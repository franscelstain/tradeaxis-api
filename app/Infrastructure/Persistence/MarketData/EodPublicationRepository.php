<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EodPublicationRepository
{
    public function findRawCurrentPublicationStateForTradeDate($tradeDate)
    {
        return DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
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
                'run.is_current_publication',
                'run.sealed_at as run_sealed_at'
            )
            ->first();
    }

    public function findInvalidCurrentPublicationStates($tradeDate = null)
    {
        $rows = DB::table('eod_current_publication_pointer as ptr')
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'ptr.publication_id')
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
                'run.is_current_publication',
                'run.sealed_at as run_sealed_at'
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

        if ((string) ($row->terminal_status ?? '') !== 'SUCCESS') {
            $reasons[] = 'RUN_TERMINAL_STATUS_NOT_SUCCESS';
        }

        if ((string) ($row->publishability_state ?? '') !== 'READABLE') {
            $reasons[] = 'RUN_PUBLISHABILITY_NOT_READABLE';
        }

        if ((int) ($row->is_current_publication ?? 0) !== 1) {
            $reasons[] = 'RUN_CURRENT_MIRROR_NOT_SET';
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
            ->where('run.is_current_publication', 1)
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.is_current_publication as run_is_current_publication'
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
            ->where('run.is_current_publication', 1)
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.is_current_publication as run_is_current_publication'
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
            ->where('run.is_current_publication', 1)
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.is_current_publication as run_is_current_publication'
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
            $this->assertPublicationEligibleForCurrent($priorPublication, $priorRunId, $tradeDate);

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

            return DB::table('eod_publications')->where('publication_id', $priorPublicationId)->first();
        });
    }

    private function assertPublicationEligibleForCurrent($publication, $runId, $tradeDate): void
    {
        if (! $publication) {
            throw new \RuntimeException('Current publication integrity violation: publication row is missing.');
        }

        if ((string) ($publication->trade_date ?? '') !== (string) $tradeDate) {
            throw new \RuntimeException('Current publication integrity violation: publication trade_date does not match pointer trade_date.');
        }

        if ((string) ($publication->seal_state ?? '') !== 'SEALED' || empty($publication->sealed_at)) {
            throw new \RuntimeException('Current publication integrity violation: publication must be SEALED with sealed_at before it can become current.');
        }

        $run = DB::table('eod_runs')
            ->where('run_id', $runId)
            ->lockForUpdate()
            ->first();

        if (! $run) {
            throw new \RuntimeException('Current publication integrity violation: publication run row is missing.');
        }

        if ((string) ($run->trade_date_requested ?? '') !== (string) $tradeDate) {
            throw new \RuntimeException('Current publication integrity violation: run trade_date_requested does not match pointer trade_date.');
        }

        if ((string) ($run->terminal_status ?? '') !== 'SUCCESS') {
            throw new \RuntimeException('Current publication integrity violation: current pointer requires run terminal_status SUCCESS.');
        }

        if ((string) ($run->publishability_state ?? '') !== 'READABLE') {
            throw new \RuntimeException('Current publication integrity violation: current pointer requires run publishability_state READABLE.');
        }

        if (empty($run->sealed_at)) {
            throw new \RuntimeException('Current publication integrity violation: current pointer requires sealed run.');
        }
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
