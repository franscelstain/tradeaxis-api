<?php

namespace App\Infrastructure\Persistence\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SectorClassificationRepository
{
    private const AUTHORITATIVE_CLASSES = ['EXCHANGE_AUTHORITATIVE', 'OPERATOR_ENTERED'];

    /**
     * Session-scoped advisory lock serialising whole membership imports.
     *
     * appendMembership() already takes lockForUpdate() over one listing's revisions, so two
     * concurrent appends for the same listing cannot interleave. That is not the exposure. The
     * import command validates a batch, then applies it, and those are two separate reads of the
     * world: validateAuthoritativeImportBatch() runs preflight outside any transaction, and the
     * postflight check inside the transaction uses plain SELECTs, which under REPEATABLE READ answer
     * from the snapshot taken at the transaction's first read. lockForUpdate(), by contrast, reads
     * the latest committed version. So an import that blocks on another importer's row lock resumes
     * planning against rows its own postflight cannot see, and postflight can then certify a state
     * that never existed.
     *
     * Narrowing the isolation mismatch would mean making postflight lock every row it inspects
     * across every listing in the batch. Serialising the import is both smaller and honest about
     * what the operation is: importing a classification revision is not a concurrent activity.
     */
    public const IMPORT_LOCK_NAME = 'tradeaxis.sector_membership_import';

    public function activeSectorCodes($classificationSystem = null)
    {
        $classificationSystem = $this->classificationSystem($classificationSystem);

        return DB::table($this->sectorsTable())
            ->where('classification_system', $classificationSystem)
            ->where('is_active', 1)
            ->pluck('sector_code')
            ->map(function ($code) {
                return strtoupper(trim((string) $code));
            })
            ->values()
            ->all();
    }

    /**
     * Returns true when the caller holds the import lock and may proceed.
     *
     * GET_LOCK is scoped to the connection, not the transaction, which is what this needs: the lock
     * has to span preflight, the apply transaction, and postflight. A dropped connection releases it
     * server-side, so a crashed importer cannot strand the lock.
     *
     * The SQLite test corpus has no equivalent and runs single-process, so there is nothing to
     * serialise. Returning true there states that plainly rather than pretending a lock was taken —
     * concurrency behaviour is proven against MariaDB or not proven at all.
     */
    public function acquireImportLock($timeoutSeconds = 10)
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return true;
        }

        $row = DB::selectOne(
            'SELECT GET_LOCK(?, ?) AS acquired',
            [self::IMPORT_LOCK_NAME, max(0, (int) $timeoutSeconds)]
        );

        // GET_LOCK answers 1 acquired, 0 timed out, NULL on error. Only 1 is permission to write.
        return $row !== null && $row->acquired !== null && (int) $row->acquired === 1;
    }

    public function releaseImportLock()
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [self::IMPORT_LOCK_NAME]);
    }

    /**
     * Validate an authoritative import as a complete effective-time/known-time batch without
     * writing transient rows. The simulation uses the same append plan as the write path and
     * checks every known-time checkpoint for overlapping active intervals.
     */
    public function validateAuthoritativeImportBatch(array $candidates, $classificationSystem = null)
    {
        $classificationSystem = $this->classificationSystem($classificationSystem);
        $errors = [];
        $plannedRevisionCount = 0;
        $knownKeys = [];

        usort($candidates, function ($left, $right) {
            foreach (['recorded_at', 'listing_id', 'effective_from', 'line'] as $field) {
                $comparison = strcmp((string) ($left[$field] ?? ''), (string) ($right[$field] ?? ''));
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        });

        $listingIds = $this->normalizeIds(array_map(function ($candidate) {
            return (int) ($candidate['listing_id'] ?? 0);
        }, $candidates));

        $rowsByListing = [];
        if ($listingIds) {
            $existingRows = DB::table($this->membershipsTable())
                ->whereIn('listing_id', $listingIds)
                ->where('classification_system', $classificationSystem)
                ->orderBy('recorded_at')
                ->orderBy('membership_id')
                ->get()
                ->all();

            foreach ($existingRows as $row) {
                if (in_array((string) $row->source_authority_class, self::AUTHORITATIVE_CLASSES, true)) {
                    $rowsByListing[(int) $row->listing_id][] = $row;
                }
            }

            foreach ($existingRows as $row) {
                $knownKeys[$this->knownRowKey($row)] = $this->membershipFingerprint($row);
            }
        }

        $virtualId = -1;
        foreach ($candidates as $candidate) {
            $line = (int) ($candidate['line'] ?? 0);

            try {
                $candidate = $this->normalizeCandidate($candidate, $classificationSystem);
                $this->assertMembershipInput(
                    $candidate['listing_id'],
                    $candidate['ticker_id'],
                    $candidate['sector_code'],
                    $candidate['effective_from'],
                    $candidate['effective_to'],
                    $candidate['source_name'],
                    $candidate['source_ref'],
                    $classificationSystem,
                    $candidate['source_authority_class'],
                    $candidate['recorded_at'],
                    $candidate['operator_name'],
                    $candidate['reason_code']
                );

                $candidateObject = (object) $candidate;
                $knownKey = $this->knownRowKey($candidateObject);
                $fingerprint = $this->membershipFingerprint($candidateObject);
                if (isset($knownKeys[$knownKey])) {
                    if ($knownKeys[$knownKey] !== $fingerprint) {
                        throw new \RuntimeException('SECTOR_MEMBERSHIP_KNOWN_TIME_CONFLICT: effective_from and recorded_at identify a different revision.');
                    }

                    continue;
                }

                $listingRows = $rowsByListing[$candidate['listing_id']] ?? [];
                $knownRows = array_values(array_filter($listingRows, function ($row) use ($candidate) {
                    return (string) $row->recorded_at <= (string) $candidate['recorded_at'];
                }));
                $plan = $this->membershipAppendPlan($this->activeRevisions($knownRows), $candidate);

                if ($plan['existing_id'] !== null) {
                    continue;
                }

                $pendingRows = [];
                if ($plan['prior'] !== null) {
                    $closure = clone $plan['prior'];
                    $closure->membership_id = $virtualId--;
                    $closure->effective_to = $plan['closed_through'];
                    $closure->source_name = $candidate['source_name'];
                    $closure->source_ref = $candidate['source_ref'];
                    $closure->source_authority_class = $candidate['source_authority_class'];
                    $closure->recorded_at = $candidate['recorded_at'];
                    $closure->supersedes_membership_id = (int) $plan['prior']->membership_id;
                    $closure->operator_name = $candidate['operator_name'];
                    $closure->reason_code = $candidate['reason_code'];
                    $pendingRows[] = $closure;
                }

                $candidateObject->membership_id = $virtualId--;
                $candidateObject->supersedes_membership_id = $plan['supersedes'];
                $pendingRows[] = $candidateObject;

                foreach ($pendingRows as $pendingRow) {
                    $pendingKey = $this->knownRowKey($pendingRow);
                    $pendingFingerprint = $this->membershipFingerprint($pendingRow);
                    if (isset($knownKeys[$pendingKey]) && $knownKeys[$pendingKey] !== $pendingFingerprint) {
                        throw new \RuntimeException('SECTOR_MEMBERSHIP_KNOWN_TIME_CONFLICT: planned revision collides with an existing effective/known key.');
                    }
                }

                foreach ($pendingRows as $pendingRow) {
                    $rowsByListing[$candidate['listing_id']][] = $pendingRow;
                    $knownKeys[$this->knownRowKey($pendingRow)] = $this->membershipFingerprint($pendingRow);
                    $plannedRevisionCount++;
                }
            } catch (\Throwable $e) {
                $errors[] = 'line '.$line.': '.$e->getMessage();
            }
        }

        if (! $errors) {
            foreach ($rowsByListing as $listingId => $rows) {
                try {
                    $this->assertNoOverlapAcrossKnownTimeline($rows, $listingId);
                } catch (\Throwable $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return [
            'errors' => $errors,
            'planned_revision_count' => $errors ? 0 : $plannedRevisionCount,
        ];
    }

    public function resolveSectorCodesForTickerIds(array $tickerIds, $tradeDate, $classificationSystem = null, $knownAt = null)
    {
        $contexts = $this->resolveSectorContextForTickerIds($tickerIds, $tradeDate, $classificationSystem, $knownAt);
        $resolved = [];

        foreach ($contexts as $tickerId => $context) {
            $resolved[$tickerId] = $context['sector_code'];
        }

        return $resolved;
    }

    /**
     * Resolve an authoritative membership using both effective-time and known-time.
     *
     * Superseded revisions remain stored. A revision only hides the row it supersedes once that
     * revision itself was known, which preserves an honest as-known reconstruction.
     */
    public function resolveSectorContextForTickerIds(array $tickerIds, $tradeDate, $classificationSystem = null, $knownAt = null)
    {
        $tickerIds = $this->normalizeIds($tickerIds);
        if (empty($tickerIds)) {
            return [];
        }

        $classificationSystem = $this->classificationSystem($classificationSystem);
        $knownAt = $knownAt ?: Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $listingIdsByTicker = $this->listingIdsByTickerIds($tickerIds);
        $contexts = [];

        foreach ($tickerIds as $tickerId) {
            if (! isset($listingIdsByTicker[$tickerId])) {
                $contexts[$tickerId] = $this->unknownContext('SECTOR_LISTING_IDENTITY_UNKNOWN');
            }
        }

        if (empty($listingIdsByTicker)) {
            return $contexts;
        }

        $rows = DB::table($this->membershipsTable().' as member')
            ->leftJoin($this->sectorsTable().' as sector', function ($join) use ($classificationSystem) {
                $join->on('sector.sector_code', '=', 'member.sector_code')
                    ->where('sector.classification_system', '=', $classificationSystem);
            })
            ->select([
                'member.membership_id', 'member.supersedes_membership_id', 'member.ticker_id',
                'member.listing_id', 'member.sector_code', 'member.effective_from',
                'member.effective_to', 'member.source_authority_class', 'member.source_name',
                'member.source_ref', 'member.recorded_at', 'member.operator_name',
                'member.reason_code', 'sector.sector_index_code',
            ])
            ->whereIn('member.listing_id', array_values($listingIdsByTicker))
            ->where('member.classification_system', $classificationSystem)
            ->where('member.recorded_at', '<=', $knownAt)
            ->whereIn('member.source_authority_class', self::AUTHORITATIVE_CLASSES)
            ->orderBy('member.recorded_at')
            ->orderBy('member.membership_id')
            ->get();

        $rowsByListing = [];
        foreach ($rows as $row) {
            $rowsByListing[(int) $row->listing_id][] = $row;
        }

        foreach ($listingIdsByTicker as $tickerId => $listingId) {
            $stored = $rowsByListing[$listingId] ?? [];
            $governed = array_values(array_filter($stored, function ($row) {
                return $this->operatorGovernanceSatisfied($row);
            }));
            $refused = array_values(array_filter($stored, function ($row) use ($tradeDate) {
                return ! $this->operatorGovernanceSatisfied($row) && $this->covers($row, $tradeDate);
            }));

            $activeRevisions = $this->activeRevisions($governed);
            $covering = array_values(array_filter($activeRevisions, function ($row) use ($tradeDate) {
                return $this->covers($row, $tradeDate);
            }));

            if (count($covering) !== 1) {
                $contexts[$tickerId] = $this->unknownContext(
                    count($covering) > 1
                        ? 'SECTOR_MEMBERSHIP_OVERLAP_INVALID'
                        : ($refused === [] ? 'SECTOR_MEMBERSHIP_UNKNOWN' : 'SECTOR_OPERATOR_GOVERNANCE_INCOMPLETE')
                );
                continue;
            }

            $row = $covering[0];
            $sectorIndexCode = $row->sector_index_code !== null
                ? strtoupper(trim((string) $row->sector_index_code))
                : null;

            $contexts[$tickerId] = [
                'sector_code' => strtoupper(trim((string) $row->sector_code)),
                'sector_index_code' => $sectorIndexCode !== '' ? $sectorIndexCode : null,
                'sector_membership_id' => (int) $row->membership_id,
                'listing_id' => (int) $row->listing_id,
                'source_authority_class' => (string) $row->source_authority_class,
                'membership_recorded_at' => (string) $row->recorded_at,
                'resolution_state' => 'RESOLVED_AUTHORITATIVE',
                'resolution_reason_code' => null,
            ];
        }

        ksort($contexts);

        return $contexts;
    }

    /**
     * Compatibility entry point. Despite the historical name, this method never updates a row.
     */
    public function upsertMembership($tickerId, $sectorCode, $effectiveFrom, $effectiveTo = null, $sourceName = null, $sourceRef = null, $classificationSystem = null, $sourceAuthorityClass = 'OPERATOR_ENTERED', $recordedAt = null, $operatorName = null, $reasonCode = null)
    {
        $listingId = $this->listingIdForTickerId((int) $tickerId);

        if ($listingId === null) {
            throw new \RuntimeException('SECTOR_LISTING_IDENTITY_MISSING: ticker_id '.(int) $tickerId.'.');
        }

        return $this->appendMembership(
            $listingId,
            (int) $tickerId,
            $sectorCode,
            $effectiveFrom,
            $effectiveTo,
            $sourceName,
            $sourceRef,
            $classificationSystem,
            $sourceAuthorityClass,
            $recordedAt,
            $operatorName,
            $reasonCode
        );
    }

    public function appendMembership($listingId, $tickerId, $sectorCode, $effectiveFrom, $effectiveTo, $sourceName, $sourceRef, $classificationSystem = null, $sourceAuthorityClass = 'OPERATOR_ENTERED', $recordedAt = null, $operatorName = null, $reasonCode = null)
    {
        $classificationSystem = $this->classificationSystem($classificationSystem);
        $sectorCode = strtoupper(trim((string) $sectorCode));
        $authorityClass = strtoupper(trim((string) $sourceAuthorityClass));
        $sourceName = trim((string) $sourceName);
        $sourceRef = $this->nullableString($sourceRef);
        $operatorName = $this->nullableString($operatorName);
        $reasonCode = $this->nullableString($reasonCode);
        $recordedAt = $this->nullableString($recordedAt);
        $effectiveTo = $this->nullableString($effectiveTo);

        $this->assertMembershipInput(
            $listingId,
            $tickerId,
            $sectorCode,
            $effectiveFrom,
            $effectiveTo,
            $sourceName,
            $sourceRef,
            $classificationSystem,
            $authorityClass,
            $recordedAt,
            $operatorName,
            $reasonCode
        );

        return DB::transaction(function () use ($listingId, $tickerId, $sectorCode, $effectiveFrom, $effectiveTo, $sourceName, $sourceRef, $classificationSystem, $authorityClass, $recordedAt, $operatorName, $reasonCode) {
            $knownRows = DB::table($this->membershipsTable())
                ->where('listing_id', (int) $listingId)
                ->where('classification_system', $classificationSystem)
                ->where('recorded_at', '<=', $recordedAt)
                ->whereIn('source_authority_class', self::AUTHORITATIVE_CLASSES)
                ->orderBy('recorded_at')
                ->orderBy('membership_id')
                ->lockForUpdate()
                ->get()
                ->all();
            $active = $this->activeRevisions($knownRows);

            $candidate = [
                'sector_code' => $sectorCode,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'source_name' => $sourceName,
                'source_ref' => $sourceRef,
                'source_authority_class' => $authorityClass,
                'operator_name' => $operatorName,
                'reason_code' => $reasonCode,
            ];
            $plan = $this->membershipAppendPlan($active, $candidate);

            if ($plan['existing_id'] !== null) {
                return $plan['existing_id'];
            }

            $supersedes = $plan['supersedes'];
            $activeAfterClosure = $active;

            if ($plan['prior'] !== null) {
                $prior = $plan['prior'];
                $closedThrough = $plan['closed_through'];
                $closureId = $this->insertRevision([
                    'ticker_id' => (int) $prior->ticker_id,
                    'listing_id' => (int) $prior->listing_id,
                    'sector_code' => (string) $prior->sector_code,
                    'classification_system' => $classificationSystem,
                    'effective_from' => (string) $prior->effective_from,
                    'effective_to' => $closedThrough,
                    'source_name' => $sourceName,
                    'source_ref' => $sourceRef,
                    'source_authority_class' => $authorityClass,
                    'recorded_at' => $recordedAt,
                    'supersedes_membership_id' => (int) $prior->membership_id,
                    'operator_name' => $operatorName,
                    'reason_code' => $reasonCode,
                ]);

                $activeAfterClosure = array_values(array_filter($activeAfterClosure, function ($row) use ($prior) {
                    return (int) $row->membership_id !== (int) $prior->membership_id;
                }));
                $closed = clone $prior;
                $closed->membership_id = $closureId;
                $closed->effective_to = $closedThrough;
                $activeAfterClosure[] = $closed;
            } else {
                $activeAfterClosure = array_values(array_filter($activeAfterClosure, function ($row) use ($supersedes) {
                    return $supersedes === null || (int) $row->membership_id !== $supersedes;
                }));
            }

            foreach ($activeAfterClosure as $row) {
                if ($this->intervalsOverlap($effectiveFrom, $effectiveTo, $row->effective_from, $row->effective_to)) {
                    throw new \RuntimeException('SECTOR_MEMBERSHIP_OVERLAP_INVALID: proposed interval overlaps membership_id '.(int) $row->membership_id.'.');
                }
            }

            return $this->insertRevision([
                'ticker_id' => (int) $tickerId,
                'listing_id' => (int) $listingId,
                'sector_code' => $sectorCode,
                'classification_system' => $classificationSystem,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'source_name' => $sourceName,
                'source_ref' => $sourceRef,
                'source_authority_class' => $authorityClass,
                'recorded_at' => $recordedAt,
                'supersedes_membership_id' => $supersedes,
                'operator_name' => $operatorName,
                'reason_code' => $reasonCode,
            ]);
        });
    }

    private function insertRevision(array $row)
    {
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        return (int) DB::table($this->membershipsTable())->insertGetId($row + [
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * `OPERATOR_ENTERED` is a conditional authority class, and the condition is part of resolution.
     *
     * Sector_Classification_Contract_LOCKED.md permits an operator-entered row to establish
     * membership "only with an explicit authoritative reference, named operator, and governed reason
     * code". appendMembership() enforces that triple, but `operator_name` and `reason_code` are
     * nullable with no database constraint binding them to the class, so a row written by any other
     * path arrived here naming a conditional class without carrying its condition — and resolved as
     * authoritative membership. Checking the condition only where it is already satisfied is not
     * checking it.
     *
     * A row that fails this is not authoritative for any purpose: it cannot resolve a sector and it
     * cannot supersede a row that can.
     */
    private function operatorGovernanceSatisfied($row)
    {
        if ((string) $row->source_authority_class !== 'OPERATOR_ENTERED') {
            return true;
        }

        foreach (['operator_name', 'reason_code', 'source_ref'] as $field) {
            if (trim((string) ($row->{$field} ?? '')) === '') {
                return false;
            }
        }

        return true;
    }

    private function covers($row, $tradeDate)
    {
        return (string) $row->effective_from <= (string) $tradeDate
            && ($row->effective_to === null || (string) $row->effective_to >= (string) $tradeDate);
    }

    private function activeRevisions(array $rows)
    {
        $superseded = [];
        foreach ($rows as $row) {
            if (! empty($row->supersedes_membership_id)) {
                $superseded[(int) $row->supersedes_membership_id] = true;
            }
        }

        return array_values(array_filter($rows, function ($row) use ($superseded) {
            return ! isset($superseded[(int) $row->membership_id]);
        }));
    }

    private function membershipAppendPlan(array $active, array $candidate)
    {
        foreach ($active as $row) {
            if ($this->sameMembership(
                $row,
                $candidate['sector_code'],
                $candidate['effective_from'],
                $candidate['effective_to'],
                $candidate['source_name'],
                $candidate['source_ref'],
                $candidate['source_authority_class'],
                $candidate['operator_name'],
                $candidate['reason_code']
            )) {
                return [
                    'existing_id' => (int) $row->membership_id,
                    'supersedes' => null,
                    'prior' => null,
                    'closed_through' => null,
                ];
            }
        }

        $sameStart = array_values(array_filter($active, function ($row) use ($candidate) {
            return (string) $row->effective_from === (string) $candidate['effective_from'];
        }));
        if (count($sameStart) > 1) {
            throw new \RuntimeException('SECTOR_MEMBERSHIP_OVERLAP_INVALID: multiple active revisions share effective_from.');
        }

        $supersedes = $sameStart ? (int) $sameStart[0]->membership_id : null;
        $prior = null;
        $closedThrough = null;
        $activeAfterClosure = $active;

        if ($supersedes === null) {
            $priorCovering = array_values(array_filter($active, function ($row) use ($candidate) {
                return (string) $row->effective_from < (string) $candidate['effective_from']
                    && ($row->effective_to === null || (string) $row->effective_to >= (string) $candidate['effective_from']);
            }));

            if (count($priorCovering) > 1) {
                throw new \RuntimeException('SECTOR_MEMBERSHIP_OVERLAP_INVALID: prior membership intervals overlap.');
            }

            if ($priorCovering) {
                $prior = $priorCovering[0];
                $closedThrough = date('Y-m-d', strtotime((string) $candidate['effective_from'].' -1 day'));
                $activeAfterClosure = array_values(array_filter($activeAfterClosure, function ($row) use ($prior) {
                    return (int) $row->membership_id !== (int) $prior->membership_id;
                }));
                $closed = clone $prior;
                $closed->effective_to = $closedThrough;
                $activeAfterClosure[] = $closed;
            }
        } else {
            $activeAfterClosure = array_values(array_filter($activeAfterClosure, function ($row) use ($supersedes) {
                return (int) $row->membership_id !== $supersedes;
            }));
        }

        foreach ($activeAfterClosure as $row) {
            if ($this->intervalsOverlap(
                $candidate['effective_from'],
                $candidate['effective_to'],
                $row->effective_from,
                $row->effective_to
            )) {
                throw new \RuntimeException('SECTOR_MEMBERSHIP_OVERLAP_INVALID: proposed interval overlaps membership_id '.(int) $row->membership_id.'.');
            }
        }

        return [
            'existing_id' => null,
            'supersedes' => $supersedes,
            'prior' => $prior,
            'closed_through' => $closedThrough,
        ];
    }

    private function normalizeCandidate(array $candidate, $classificationSystem)
    {
        return [
            'line' => (int) ($candidate['line'] ?? 0),
            'listing_id' => (int) ($candidate['listing_id'] ?? 0),
            'ticker_id' => (int) ($candidate['ticker_id'] ?? 0),
            'sector_code' => strtoupper(trim((string) ($candidate['sector_code'] ?? ''))),
            'classification_system' => $classificationSystem,
            'effective_from' => trim((string) ($candidate['effective_from'] ?? '')),
            'effective_to' => $this->nullableString($candidate['effective_to'] ?? null),
            'source_name' => trim((string) ($candidate['source_name'] ?? '')),
            'source_ref' => $this->nullableString($candidate['source_ref'] ?? null),
            'source_authority_class' => strtoupper(trim((string) ($candidate['source_authority_class'] ?? ''))),
            'recorded_at' => $this->nullableString($candidate['recorded_at'] ?? null),
            'operator_name' => $this->nullableString($candidate['operator_name'] ?? null),
            'reason_code' => $this->nullableString($candidate['reason_code'] ?? null),
        ];
    }

    private function knownRowKey($row)
    {
        return implode('|', [
            (int) $row->listing_id,
            (string) $row->effective_from,
            (string) $row->recorded_at,
        ]);
    }

    private function membershipFingerprint($row)
    {
        return json_encode([
            (int) $row->ticker_id,
            strtoupper(trim((string) $row->sector_code)),
            (string) $row->effective_from,
            $this->nullableString($row->effective_to),
            trim((string) $row->source_name),
            $this->nullableString($row->source_ref),
            strtoupper(trim((string) $row->source_authority_class)),
            $this->nullableString($row->operator_name ?? null),
            $this->nullableString($row->reason_code ?? null),
        ]);
    }

    private function assertNoOverlapAcrossKnownTimeline(array $rows, $listingId)
    {
        $checkpoints = array_values(array_unique(array_map(function ($row) {
            return (string) $row->recorded_at;
        }, $rows)));
        sort($checkpoints, SORT_STRING);

        foreach ($checkpoints as $knownAt) {
            $knownRows = array_values(array_filter($rows, function ($row) use ($knownAt) {
                return (string) $row->recorded_at <= $knownAt;
            }));
            $active = $this->activeRevisions($knownRows);

            for ($left = 0; $left < count($active); $left++) {
                for ($right = $left + 1; $right < count($active); $right++) {
                    if ($this->intervalsOverlap(
                        $active[$left]->effective_from,
                        $active[$left]->effective_to,
                        $active[$right]->effective_from,
                        $active[$right]->effective_to
                    )) {
                        throw new \RuntimeException(
                            'SECTOR_MEMBERSHIP_OVERLAP_INVALID: listing_id '.(int) $listingId
                            .' has overlapping active intervals as known at '.$knownAt.'.'
                        );
                    }
                }
            }
        }
    }

    private function listingIdsByTickerIds(array $tickerIds)
    {
        return DB::table('md_listings')
            ->whereIn('legacy_ticker_id', $tickerIds)
            ->pluck('listing_id', 'legacy_ticker_id')
            ->map(function ($listingId) {
                return (int) $listingId;
            })
            ->all();
    }

    private function listingIdForTickerId($tickerId)
    {
        $listingId = DB::table('md_listings')->where('legacy_ticker_id', $tickerId)->value('listing_id');

        if ($listingId === null) {
            (new TemporalIdentityRepository())->ensureLegacyProjection();
            $listingId = DB::table('md_listings')->where('legacy_ticker_id', $tickerId)->value('listing_id');
        }

        return $listingId === null ? null : (int) $listingId;
    }

    private function assertMembershipInput($listingId, $tickerId, $sectorCode, $effectiveFrom, $effectiveTo, $sourceName, $sourceRef, $classificationSystem, $authorityClass, $recordedAt, $operatorName, $reasonCode)
    {
        $listing = (int) $listingId > 0
            ? DB::table('md_listings')->where('listing_id', (int) $listingId)->first()
            : null;
        if ($listing === null) {
            throw new \InvalidArgumentException('SECTOR_LISTING_IDENTITY_MISSING: a stable listing_id is required.');
        }
        if ((int) $tickerId <= 0 || (int) $listing->legacy_ticker_id !== (int) $tickerId) {
            throw new \InvalidArgumentException('SECTOR_LISTING_TICKER_MISMATCH: ticker_id is not bound to the stable listing.');
        }
        if (strtoupper(trim((string) $listing->exchange_code)) !== 'IDX') {
            throw new \InvalidArgumentException('SECTOR_LISTING_EXCHANGE_UNSUPPORTED: IDX-IC membership requires an IDX listing.');
        }
        if ($classificationSystem !== 'IDX-IC') {
            throw new \InvalidArgumentException('SECTOR_CLASSIFICATION_SYSTEM_UNSUPPORTED: '.$classificationSystem.'.');
        }
        if (! DB::table($this->sectorsTable())->where('classification_system', $classificationSystem)->where('sector_code', $sectorCode)->exists()) {
            throw new \InvalidArgumentException('SECTOR_CODE_UNKNOWN: '.$sectorCode.'.');
        }
        if (! $this->isIsoDate($effectiveFrom) || ($effectiveTo !== null && ! $this->isIsoDate($effectiveTo))) {
            throw new \InvalidArgumentException('SECTOR_MEMBERSHIP_EFFECTIVE_DATE_INVALID.');
        }
        if ($effectiveTo !== null && (string) $effectiveTo < (string) $effectiveFrom) {
            throw new \InvalidArgumentException('SECTOR_MEMBERSHIP_INTERVAL_INVALID.');
        }
        if (! in_array($authorityClass, self::AUTHORITATIVE_CLASSES, true)) {
            throw new \InvalidArgumentException('SECTOR_SOURCE_AUTHORITY_CLASS_INVALID: '.$authorityClass.'.');
        }
        if ($sourceName === '' || $sourceRef === null) {
            throw new \InvalidArgumentException('SECTOR_SOURCE_PROVENANCE_INCOMPLETE: source_name and source_ref are required.');
        }
        if ($authorityClass === 'OPERATOR_ENTERED' && ($operatorName === null || $reasonCode === null)) {
            throw new \InvalidArgumentException('SECTOR_OPERATOR_GOVERNANCE_INCOMPLETE: operator_name and reason_code are required.');
        }
        if ($recordedAt === null || ! $this->isDateTime($recordedAt)) {
            throw new \InvalidArgumentException('SECTOR_MEMBERSHIP_RECORDED_AT_INVALID: recorded_at must use YYYY-MM-DD HH:MM:SS.');
        }
    }

    private function sameMembership($row, $sectorCode, $effectiveFrom, $effectiveTo, $sourceName, $sourceRef, $authorityClass, $operatorName, $reasonCode)
    {
        return (string) $row->sector_code === (string) $sectorCode
            && (string) $row->effective_from === (string) $effectiveFrom
            && $this->nullableString($row->effective_to) === $effectiveTo
            && (string) $row->source_name === (string) $sourceName
            && $this->nullableString($row->source_ref) === $sourceRef
            && (string) $row->source_authority_class === (string) $authorityClass
            && $this->nullableString($row->operator_name ?? null) === $operatorName
            && $this->nullableString($row->reason_code ?? null) === $reasonCode;
    }

    private function intervalsOverlap($leftFrom, $leftTo, $rightFrom, $rightTo)
    {
        $leftEnd = $leftTo ?: '9999-12-31';
        $rightEnd = $rightTo ?: '9999-12-31';

        return (string) $leftFrom <= (string) $rightEnd && (string) $rightFrom <= (string) $leftEnd;
    }

    private function unknownContext($reasonCode)
    {
        return [
            'sector_code' => 'UNKNOWN',
            'sector_index_code' => null,
            'sector_membership_id' => null,
            'listing_id' => null,
            'source_authority_class' => null,
            'membership_recorded_at' => null,
            'resolution_state' => 'UNKNOWN',
            'resolution_reason_code' => $reasonCode,
        ];
    }

    private function normalizeIds(array $ids)
    {
        return array_values(array_filter(array_unique(array_map('intval', $ids)), function ($id) {
            return $id > 0;
        }));
    }

    private function isIsoDate($value)
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);

        return $date !== false && $date->format('Y-m-d') === (string) $value;
    }

    private function isDateTime($value)
    {
        $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', (string) $value);

        return $dateTime !== false && $dateTime->format('Y-m-d H:i:s') === (string) $value;
    }

    private function nullableString($value)
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function classificationSystem($classificationSystem)
    {
        $value = $classificationSystem ?: config('market_data.sectors.classification_system', 'IDX-IC');

        return strtoupper(trim((string) $value));
    }

    private function sectorsTable()
    {
        return config('market_data.sectors.table', 'market_data_sectors');
    }

    private function membershipsTable()
    {
        return config('market_data.sectors.membership_table', 'ticker_sector_memberships');
    }
}
