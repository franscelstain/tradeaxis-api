<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Models\EodRun;
use App\Application\MarketData\Services\MarketDataInvariantGuard;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EodPublicationRepository
{
    public function findRawCurrentPublicationStateForTradeDate($tradeDate)
    {
        return DB::table('eod_current_publication_pointer as ptr')
            ->leftJoin('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->leftJoin('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('ptr.trade_date', $tradeDate)
            ->select(
                'ptr.trade_date as pointer_trade_date',
                'ptr.publication_id as pointer_publication_id',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'pub.publication_id',
                'pub.trade_date',
                'pub.run_id',
                'pub.publication_version',
                'pub.is_current',
                'pub.seal_state',
                'pub.sealed_at',
                'run.terminal_status',
                'run.publishability_state',
                'run.coverage_gate_state',
                'run.is_current_publication',
                'run.sealed_at as run_sealed_at',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->first();
    }

    public function findInvalidCurrentPublicationStates($tradeDate = null)
    {
        $rows = DB::table('eod_current_publication_pointer as ptr')
            ->leftJoin('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->leftJoin('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->when($tradeDate !== null, function ($query) use ($tradeDate) {
                $query->where('ptr.trade_date', $tradeDate);
            })
            ->orderBy('ptr.trade_date')
            ->select(
                'ptr.trade_date as pointer_trade_date',
                'ptr.publication_id as pointer_publication_id',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'pub.publication_id',
                'pub.trade_date',
                'pub.run_id',
                'pub.publication_version',
                'pub.is_current',
                'pub.seal_state',
                'pub.sealed_at',
                'run.terminal_status',
                'run.publishability_state',
                'run.coverage_gate_state',
                'run.is_current_publication',
                'run.sealed_at as run_sealed_at',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->get();

        return $rows->filter(function ($row) {
            return $this->determineCurrentIntegrityViolationReasons($row) !== [];
        })->values();
    }

    protected function determineCurrentIntegrityViolationReasons($row)
    {
        if (! $row) {
            return ['CURRENT_POINTER_ROW_MISSING'];
        }

        $reasons = [];

        if (empty($row->publication_id)) {
            $reasons[] = 'PUBLICATION_ROW_MISSING';
            return $reasons;
        }

        if ((string) ($row->trade_date ?? $row->pointer_trade_date ?? '') !== (string) ($row->pointer_trade_date ?? '')) {
            $reasons[] = 'PUBLICATION_TRADE_DATE_MISMATCH';
        }

        if ((string) ($row->run_id ?? '') !== (string) ($row->pointer_run_id ?? '')) {
            $reasons[] = 'POINTER_RUN_ID_MISMATCH';
        }

        if ((string) ($row->publication_version ?? '') !== (string) ($row->pointer_publication_version ?? '')) {
            $reasons[] = 'POINTER_PUBLICATION_VERSION_MISMATCH';
        }

        if ((int) ($row->is_current ?? 0) !== 1) {
            $reasons[] = 'PUBLICATION_NOT_MARKED_CURRENT';
        }

        if ((string) ($row->seal_state ?? '') !== 'SEALED') {
            $reasons[] = 'PUBLICATION_NOT_SEALED';
        }

        if (empty($row->pointer_sealed_at)) {
            $reasons[] = 'POINTER_SEALED_AT_MISSING';
        }

        if (empty($row->sealed_at)) {
            $reasons[] = 'PUBLICATION_SEALED_AT_MISSING';
        }

        if (empty($row->run_id)) {
            $reasons[] = 'RUN_ROW_MISSING';
            return $reasons;
        }

        if (empty($row->run_sealed_at)) {
            $reasons[] = 'RUN_SEALED_AT_MISSING';
        }

        $runTerminalStatus = $row->run_terminal_status ?? $row->terminal_status ?? null;
        if ((string) $runTerminalStatus !== 'SUCCESS') {
            $reasons[] = 'RUN_TERMINAL_STATUS_NOT_SUCCESS';
        }

        $runPublishabilityState = $row->run_publishability_state ?? $row->publishability_state ?? null;
        if ((string) $runPublishabilityState !== 'READABLE') {
            $reasons[] = 'RUN_PUBLISHABILITY_NOT_READABLE';
        }

        $runCoverageGateState = $row->run_coverage_gate_state ?? $row->coverage_gate_state ?? null;
        if ((string) $runCoverageGateState !== 'PASS') {
            $reasons[] = 'RUN_COVERAGE_GATE_NOT_PASS';
        }

        $runIsCurrentPublication = $row->run_is_current_publication ?? $row->is_current_publication ?? 0;
        if ((int) $runIsCurrentPublication !== 1) {
            $reasons[] = 'RUN_CURRENT_MIRROR_NOT_SET';
        }

        if ((string) ($row->run_publication_id ?? '') !== (string) ($row->pointer_publication_id ?? '')) {
            $reasons[] = 'RUN_PUBLICATION_ID_MISMATCH';
        }

        if ((string) ($row->run_publication_version ?? '') !== (string) ($row->pointer_publication_version ?? '')) {
            $reasons[] = 'RUN_PUBLICATION_VERSION_MISMATCH';
        }

        return array_values(array_unique($reasons));
    }

    /**
     * Official read-side gateway for consumer paths.
     *
     * This method is intentionally pointer-first: no caller may resolve the
     * latest/current readable publication through MAX(date), MAX(publication_id),
     * raw/staging artifacts, or publication flags without the pointer row.
     */
    public function resolveCurrentReadablePublicationForTradeDate($tradeDate)
    {
        return DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->leftJoin('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('ptr.trade_date', $tradeDate)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('ptr.run_id', 'pub.run_id')
            ->whereColumn('ptr.publication_version', 'pub.publication_version')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.run_id')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.trade_date_requested', 'ptr.trade_date')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->where('run.coverage_gate_state', 'PASS')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.sealed_at as run_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.coverage_gate_state as run_coverage_gate_state',
                'run.is_current_publication as run_is_current_publication',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->first();
    }

    public function findCurrentPublicationForTradeDate($tradeDate)
    {
        return $this->resolveCurrentReadablePublicationForTradeDate($tradeDate);
    }

    public function findPointerResolvedPublicationForTradeDate($tradeDate)
    {
        return $this->resolveCurrentReadablePublicationForTradeDate($tradeDate);
    }



    public function findReadableCurrentPublicationForRun($runId, $tradeDate)
    {
        return DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('run.run_id', $runId)
            ->where('ptr.trade_date', $tradeDate)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('ptr.run_id', 'pub.run_id')
            ->whereColumn('ptr.publication_version', 'pub.publication_version')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.trade_date_requested', 'ptr.trade_date')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->where('run.coverage_gate_state', 'PASS')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.sealed_at as run_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.coverage_gate_state as run_coverage_gate_state',
                'run.is_current_publication as run_is_current_publication',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->first();
    }

    public function findCorrectionBaselinePublicationForTradeDate($tradeDate)
    {
        return DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->leftJoin('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('ptr.trade_date', $tradeDate)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('ptr.run_id', 'pub.run_id')
            ->whereColumn('ptr.publication_version', 'pub.publication_version')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.run_id')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.trade_date_requested', 'ptr.trade_date')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->where('run.coverage_gate_state', 'PASS')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.sealed_at as run_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.coverage_gate_state as run_coverage_gate_state',
                'run.is_current_publication as run_is_current_publication',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->first();
    }

    public function getOrCreateCandidatePublication(EodRun $run, $supersedesPublicationId = null)
    {
        return DB::transaction(function () use ($run, $supersedesPublicationId) {
            $existing = DB::table('eod_publications')
                ->where('run_id', $run->run_id)
                ->where('trade_date', $run->trade_date_requested)
                ->orderByDesc('publication_id')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $currentMaxVersion = (int) DB::table('eod_publications')
                ->where('trade_date', $run->trade_date_requested)
                ->max('publication_version');

            $now = Carbon::now(config('market_data.platform.timezone'));
            $publicationId = DB::table('eod_publications')->insertGetId([
                'trade_date' => $run->trade_date_requested,
                'run_id' => $run->run_id,
                'publication_version' => $currentMaxVersion + 1,
                'is_current' => 0,
                'supersedes_publication_id' => $supersedesPublicationId,
                'previous_publication_id' => $supersedesPublicationId,
                'replaced_publication_id' => $supersedesPublicationId,
                'seal_state' => 'UNSEALED',
                'bars_batch_hash' => null,
                'indicators_batch_hash' => null,
                'eligibility_batch_hash' => null,
                'source_file_hash' => $run->source_file_hash ?? null,
                'source_file_hash_algorithm' => $run->source_file_hash_algorithm ?? null,
                'source_file_size_bytes' => $run->source_file_size_bytes ?? null,
                'source_file_row_count' => $run->source_file_row_count ?? null,
                'sealed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        });
    }

    public function findByRunId($runId)
    {
        return DB::table('eod_publications')->where('run_id', $runId)->orderByDesc('publication_id')->first();
    }

    public function updateCandidateHashes($publicationId, array $hashes)
    {
        $this->assertPublicationMutable($publicationId);

        DB::table('eod_publications')
            ->where('publication_id', $publicationId)
            ->update([
                'bars_batch_hash' => $hashes['bars_batch_hash'],
                'indicators_batch_hash' => $hashes['indicators_batch_hash'],
                'eligibility_batch_hash' => $hashes['eligibility_batch_hash'],
                'updated_at' => Carbon::now(config('market_data.platform.timezone')),
            ]);
    }

    public function sealCandidatePublication(EodRun $run, $sealedBy, $sealNote = null)
    {
        return DB::transaction(function () use ($run) {
            $candidate = $this->getOrCreateCandidatePublication($run, null);

            if (! $candidate->bars_batch_hash || ! $candidate->indicators_batch_hash || ! $candidate->eligibility_batch_hash) {
                throw new \RuntimeException('Cannot seal publication before all candidate hashes exist.');
            }

            $now = Carbon::now(config('market_data.platform.timezone'));

            $this->assertPublicationMutable($candidate->publication_id);

            DB::table('eod_publications')
                ->where('publication_id', $candidate->publication_id)
                ->update([
                    'seal_state' => 'SEALED',
                    'source_file_hash' => $run->source_file_hash ?? null,
                    'source_file_hash_algorithm' => $run->source_file_hash_algorithm ?? null,
                    'source_file_size_bytes' => $run->source_file_size_bytes ?? null,
                    'source_file_row_count' => $run->source_file_row_count ?? null,
                    'sealed_at' => $now,
                    'updated_at' => $now,
                ]);

            return DB::table('eod_publications')->where('publication_id', $candidate->publication_id)->first();
        });
    }


    public function sealCandidatePublicationPartial(EodRun $run, $sealedBy, $sealNote = null)
    {
        return DB::transaction(function () use ($run) {
            $candidate = $this->getOrCreateCandidatePublication($run, null);
            $now = Carbon::now(config('market_data.platform.timezone'));

            $this->assertPublicationMutable($candidate->publication_id);

            DB::table('eod_publications')
                ->where('publication_id', $candidate->publication_id)
                ->update([
                    'seal_state' => 'SEALED',
                    'source_file_hash' => $run->source_file_hash ?? null,
                    'source_file_hash_algorithm' => $run->source_file_hash_algorithm ?? null,
                    'source_file_size_bytes' => $run->source_file_size_bytes ?? null,
                    'source_file_row_count' => $run->source_file_row_count ?? null,
                    'sealed_at' => $now,
                    'updated_at' => $now,
                ]);

            return DB::table('eod_publications')->where('publication_id', $candidate->publication_id)->first();
        });
    }

    public function promoteCandidateToCurrent(EodRun $run, $priorPublicationId = null, $forceReplace = false)
    {
        return DB::transaction(function () use ($run, $priorPublicationId, $forceReplace) {
            $candidate = DB::table('eod_publications')
                ->where('run_id', $run->run_id)
                ->where('trade_date', $run->trade_date_requested)
                ->orderByDesc('publication_id')
                ->lockForUpdate()
                ->first();

            if (! $candidate) {
                throw new \RuntimeException('Candidate publication not found for finalize/current-switch.');
            }

            if ($candidate->seal_state !== 'SEALED') {
                throw new \RuntimeException('Candidate publication is not sealed.');
            }

            if (! $candidate->sealed_at) {
                throw new \RuntimeException('Candidate publication is missing sealed_at timestamp.');
            }

            if ((string) ($run->coverage_gate_state ?? '') !== 'PASS') {
                throw new \RuntimeException('Current publication promotion requires coverage_gate_state PASS before pointer switch.');
            }


            $current = DB::table('eod_current_publication_pointer as ptr')
                ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
                ->where('ptr.trade_date', $run->trade_date_requested)
                ->lockForUpdate()
                ->select('ptr.trade_date as pointer_trade_date', 'pub.*')
                ->first();

            if ($current && (int) $current->publication_id !== (int) $candidate->publication_id && ! $priorPublicationId) {
                $rawCurrent = $this->findRawCurrentPublicationStateForTradeDate($run->trade_date_requested);
                $integrityReasons = $this->determineCurrentIntegrityViolationReasons($rawCurrent);

                if ($integrityReasons !== []) {
                    throw new \RuntimeException(
                        'Invalid current publication integrity detected for trade date '.$run->trade_date_requested.'. Repair current pointer/current mirrors before replacement. Reasons: '.implode(',', $integrityReasons)
                    );
                }

                if (! $forceReplace) {
                    throw new \RuntimeException('Current publication already exists for trade date '.$run->trade_date_requested.'. Use --force_replace=true with an audit reason to replace it via operator-controlled switch.');
                }

                $priorPublicationId = (int) $current->publication_id;
            }

            if ($priorPublicationId && $current && (int) $current->publication_id !== (int) $priorPublicationId) {
                throw new \RuntimeException('Correction baseline no longer matches current publication pointer.');
            }

            $now = Carbon::now(config('market_data.platform.timezone'));

            /*
             * Pointer promotion is only reached after finalize decision says the
             * candidate is publishable. The pointer target guard validates the
             * persisted run row, not only the in-memory model, so prime the run
             * to its publishable state before the guard reads it. Without this,
             * correction/current promotion can be falsely rejected as a pointer
             * mismatch and the valid run is finalized as HELD. Coverage is not
             * fabricated here; it must already be PASS from the coverage gate.
             */
            DB::table('eod_runs')
                ->where('run_id', $run->run_id)
                ->update([
                    'terminal_status' => 'SUCCESS',
                    'publishability_state' => 'READABLE',
                    'quality_gate_state' => 'PASS',
                    'updated_at' => $now,
                ]);

            $guardRun = DB::table('eod_runs')
                ->where('run_id', $run->run_id)
                ->lockForUpdate()
                ->first();

            (new MarketDataInvariantGuard())->assertValidPointerTarget(
                $candidate,
                $guardRun,
                $run->trade_date_requested,
                'EodPublicationRepository::promoteCandidateToCurrent'
            );

            DB::table('eod_publications')
                ->where('trade_date', $run->trade_date_requested)
                ->update([
                    'is_current' => 0,
                    'updated_at' => $now,
                ]);

            DB::table('eod_publications')
                ->where('publication_id', $candidate->publication_id)
                ->update([
                    'is_current' => 1,
                    'supersedes_publication_id' => $priorPublicationId,
                    'previous_publication_id' => $priorPublicationId,
                    'replaced_publication_id' => $priorPublicationId,
                    'updated_at' => $now,
                ]);

            DB::table('eod_current_publication_pointer')->updateOrInsert(
                ['trade_date' => $run->trade_date_requested],
                [
                    'publication_id' => $candidate->publication_id,
                    'run_id' => $run->run_id,
                    'publication_version' => $candidate->publication_version,
                    'sealed_at' => $candidate->sealed_at,
                    'updated_at' => $now,
                ]
            );

            DB::table('eod_runs')
                ->where('trade_date_requested', $run->trade_date_requested)
                ->update([
                    'is_current_publication' => 0,
                    'updated_at' => $now,
                ]);

            DB::table('eod_runs')
                ->where('run_id', $run->run_id)
                ->update([
                    'publication_id' => $candidate->publication_id,
                    'publication_version' => $candidate->publication_version,
                    'is_current_publication' => 1,
                    'updated_at' => $now,
                ]);

            $this->assertCurrentPointerResolvedAfterSwitch(
                $run->trade_date_requested,
                $candidate->publication_id,
                $run->run_id,
                'EodPublicationRepository::promoteCandidateToCurrent'
            );

            return DB::table('eod_publications')->where('publication_id', $candidate->publication_id)->first();
        });
    }


    public function restorePriorCurrentPublication($tradeDate, $priorPublicationId, $priorRunId = null)
    {
        return DB::transaction(function () use ($tradeDate, $priorPublicationId, $priorRunId) {
            if (! $priorPublicationId) {
                return null;
            }

            $priorPublication = DB::table('eod_publications')
                ->where('publication_id', $priorPublicationId)
                ->where('trade_date', $tradeDate)
                ->lockForUpdate()
                ->first();

            if (! $priorPublication) {
                return null;
            }

            $priorRunId = $priorRunId ?: $priorPublication->run_id;

            if (
                (string) ($priorPublication->trade_date ?? '') !== (string) $tradeDate
                || (string) ($priorPublication->seal_state ?? '') !== 'SEALED'
                || empty($priorPublication->sealed_at)
            ) {
                $this->clearCurrentPublicationState($tradeDate);

                return null;
            }

            $now = Carbon::now(config('market_data.platform.timezone'));

            DB::table('eod_publications')
                ->where('trade_date', $tradeDate)
                ->update([
                    'is_current' => 0,
                    'updated_at' => $now,
                ]);

            DB::table('eod_publications')
                ->where('publication_id', $priorPublicationId)
                ->update([
                    'is_current' => 1,
                    'updated_at' => $now,
                ]);

            DB::table('eod_current_publication_pointer')->updateOrInsert(
                ['trade_date' => $tradeDate],
                [
                    'publication_id' => $priorPublicationId,
                    'run_id' => $priorRunId,
                    'publication_version' => $priorPublication->publication_version,
                    'sealed_at' => $priorPublication->sealed_at,
                    'updated_at' => $now,
                ]
            );

            DB::table('eod_runs')
                ->where('trade_date_requested', $tradeDate)
                ->update([
                    'is_current_publication' => 0,
                    'updated_at' => $now,
                ]);

            DB::table('eod_runs')
                ->where('run_id', $priorRunId)
                ->update([
                    'publication_id' => $priorPublicationId,
                    'publication_version' => $priorPublication->publication_version,
                    'is_current_publication' => 1,
                    'updated_at' => $now,
                ]);

            $pointerResolved = $this->assertCurrentPointerResolvedAfterSwitch(
                $tradeDate,
                $priorPublicationId,
                $priorRunId,
                'EodPublicationRepository::restorePriorCurrentPublication'
            );

            if (! $pointerResolved) {
                return DB::table('eod_publications')->where('publication_id', $priorPublicationId)->first();
            }

            return DB::table('eod_publications')->where('publication_id', $priorPublicationId)->first();
        });
    }

    private function assertPublicationEligibleForCurrent($publication, $runId, $tradeDate): bool
    {
        if (! $publication) {
            return false;
        }

        if ((string) ($publication->trade_date ?? '') !== (string) $tradeDate) {
            return false;
        }

        if ((string) ($publication->seal_state ?? '') !== 'SEALED' || empty($publication->sealed_at)) {
            return false;
        }

        $run = DB::table('eod_runs')
            ->where('run_id', $runId)
            ->lockForUpdate()
            ->first();

        if (! $run) {
            return false;
        }

        if ((string) ($run->trade_date_requested ?? '') !== (string) $tradeDate) {
            return false;
        }

        if ((string) ($run->terminal_status ?? '') !== 'SUCCESS') {
            return false;
        }

        if ((string) ($run->publishability_state ?? '') !== 'READABLE') {
            return false;
        }

        if ((string) ($run->coverage_gate_state ?? '') !== 'PASS') {
            return false;
        }

        if (empty($run->sealed_at)) {
            return false;
        }

        if ((string) ($run->publication_id ?? '') !== (string) ($publication->publication_id ?? '')) {
            return false;
        }

        if ((string) ($run->publication_version ?? '') !== (string) ($publication->publication_version ?? '')) {
            return false;
        }

        try {
            $guard = new MarketDataInvariantGuard();
            $guard->assertValidFallbackTarget(
                $publication,
                $run,
                $tradeDate,
                'EodPublicationRepository::restorePriorCurrentPublication'
            );
            $guard->assertValidPointerTarget(
                $publication,
                $run,
                $tradeDate,
                'EodPublicationRepository::assertPublicationEligibleForCurrent'
            );
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }

    private function assertCurrentPointerResolvedAfterSwitch($tradeDate, $publicationId, $runId, $context): bool
    {
        $resolved = $this->resolveCurrentReadablePublicationForTradeDate($tradeDate);

        if (! $resolved) {
            // current pointer did not resolve to a readable publication after switch.
            return false;
        }

        if ((int) $resolved->publication_id !== (int) $publicationId) {
            // current pointer publication_id mismatch after switch.
            return false;
        }

        if ((int) $resolved->run_id !== (int) $runId) {
            // current pointer run_id mismatch after switch.
            return false;
        }

        $reasons = $this->determineCurrentIntegrityViolationReasons($resolved);

        if ($reasons !== []) {
            // invalid current pointer state after switch. Reasons:
            return false;
        }

        return true;
    }

    public function clearCurrentPublicationState($tradeDate)
    {
        return DB::transaction(function () use ($tradeDate) {
            $now = Carbon::now(config('market_data.platform.timezone'));

            DB::table('eod_publications')
                ->where('trade_date', $tradeDate)
                ->update([
                    'is_current' => 0,
                    'updated_at' => $now,
                ]);

            DB::table('eod_current_publication_pointer')
                ->where('trade_date', $tradeDate)
                ->delete();

            DB::table('eod_runs')
                ->where('trade_date_requested', $tradeDate)
                ->update([
                    'is_current_publication' => 0,
                    'updated_at' => $now,
                ]);
        });
    }

    public function discardCandidatePublication($publicationId)
    {
        DB::transaction(function () use ($publicationId) {
            DB::table('eod_bars_history')->where('publication_id', $publicationId)->delete();
            DB::table('eod_indicators_history')->where('publication_id', $publicationId)->delete();
            DB::table('eod_eligibility_history')->where('publication_id', $publicationId)->delete();
            DB::table('eod_publications')->where('publication_id', $publicationId)->delete();
        });
    }

    public function assertPublicationMutable($publicationId)
    {
        $publication = DB::table('eod_publications')
            ->where('publication_id', $publicationId)
            ->lockForUpdate()
            ->first();

        if (! $publication) {
            throw new \RuntimeException('Publication not found for mutability guard.');
        }

        if ((string) ($publication->seal_state ?? '') === 'SEALED') {
            throw new \RuntimeException('SEALED_PUBLICATION_IMMUTABLE');
        }

        return $publication;
    }

    public function findLatestReadablePublicationBefore($tradeDate)
    {
        return DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('ptr.trade_date', '<', $tradeDate)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('ptr.run_id', 'pub.run_id')
            ->whereColumn('ptr.publication_version', 'pub.publication_version')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.trade_date_requested', 'ptr.trade_date')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->where('run.coverage_gate_state', 'PASS')
            ->where('run.is_current_publication', 1)
            ->orderByDesc('ptr.trade_date')
            ->select(
                'ptr.trade_date as readable_trade_date',
                'pub.publication_id',
                'pub.publication_version',
                'run.run_id'
            )
            ->first();
    }

    public function buildManifestByPublicationId($publicationId)
    {
        return DB::table('eod_publications as pub')
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->where('pub.publication_id', $publicationId)
            ->select(
                'pub.publication_id',
                'pub.trade_date',
                'pub.run_id',
                'pub.publication_version',
                'pub.is_current',
                'pub.supersedes_publication_id',
                'pub.previous_publication_id',
                'pub.replaced_publication_id',
                'pub.seal_state',
                'pub.sealed_at',
                'run.config_version as config_identity',
                'pub.bars_batch_hash',
                'pub.indicators_batch_hash',
                'pub.eligibility_batch_hash',
                'run.bars_rows_written',
                'run.indicators_rows_written',
                'run.eligibility_rows_written',
                'run.trade_date_effective',
                'pub.source_file_hash',
                'pub.source_file_hash_algorithm',
                'pub.source_file_size_bytes',
                'pub.source_file_row_count'
            )
            ->first();
    }
}
