<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Application\MarketData\Services\CoverageGateStateNormalizer;
use App\Application\MarketData\Services\MarketDataInvariantGuard;
use App\Models\EodRun;
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
                'pub.price_product_code as publication_price_product_code',
                'pub.price_product_version as publication_price_product_version',
                'pub.factor_set_hash as publication_factor_set_hash',
                'run.terminal_status',
                'run.publishability_state',
                'run.coverage_gate_state',
                'run.coverage_universe_count',
                'run.coverage_available_count',
                'run.coverage_missing_count',
                'run.coverage_ratio',
                'run.coverage_min_threshold',
                'run.coverage_threshold_mode',
                'run.coverage_universe_basis',
                'run.coverage_contract_version',
                'run.is_current_publication',
                'run.sealed_at as run_sealed_at',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version',
                'run.price_product_code as run_price_product_code',
                'run.price_product_version as run_price_product_version',
                'run.factor_set_hash as run_factor_set_hash'
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
                'pub.price_product_code as publication_price_product_code',
                'pub.price_product_version as publication_price_product_version',
                'pub.factor_set_hash as publication_factor_set_hash',
                'run.terminal_status',
                'run.publishability_state',
                'run.coverage_gate_state',
                'run.coverage_universe_count',
                'run.coverage_available_count',
                'run.coverage_missing_count',
                'run.coverage_ratio',
                'run.coverage_min_threshold',
                'run.coverage_threshold_mode',
                'run.coverage_universe_basis',
                'run.coverage_contract_version',
                'run.is_current_publication',
                'run.sealed_at as run_sealed_at',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version',
                'run.price_product_code as run_price_product_code',
                'run.price_product_version as run_price_product_version',
                'run.factor_set_hash as run_factor_set_hash'
            )
            ->get();

        return $rows->filter(function ($row) {
            return $this->determineCurrentIntegrityViolationReasons($row) !== [];
        })->values();
    }

    /**
     * Migration-only baseline selector for governed analytical recomputation.
     *
     * Consumers must use the strict readable resolver. This selector permits exactly the three
     * legacy analytical-identity faults that the recompute will replace; every seal, pointer,
     * run, coverage, and publication invariant remains mandatory.
     */
    public function findCurrentPublicationForAnalyticalRemediation($tradeDate)
    {
        $raw = $this->findRawCurrentPublicationStateForTradeDate($tradeDate);
        if (! $raw) {
            return null;
        }

        $allowedLegacyFaults = [
            'PUBLICATION_ANALYTICAL_IDENTITY_INVALID',
            'RUN_ANALYTICAL_IDENTITY_MISMATCH',
            'ANALYTICAL_ROW_IDENTITY_MISMATCH',
        ];
        $blocking = array_values(array_diff(
            $this->determineCurrentIntegrityViolationReasons($raw),
            $allowedLegacyFaults
        ));

        if ($blocking !== []) {
            return null;
        }

        return DB::table('eod_publications')
            ->where('publication_id', $raw->publication_id)
            ->where('trade_date', $tradeDate)
            ->first();
    }

    /**
     * Canonical derivation of why a current-pointer state is invalid.
     *
     * Public because operator tooling must report the same reasons the scan filters on.
     * RepairCurrentPublicationIntegrityCommand previously kept its own copy of this logic and
     * it had drifted: five of the reasons below were missing there, so a pointer broken only by
     * a coverage or run-mirror fault was correctly detected as invalid and then displayed with
     * an empty integrity_reasons list.
     */
    public function determineCurrentIntegrityViolationReasons($row)
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

        $expectedProduct = (string) config('market_data.indicators.price_product_default', 'STRUCTURAL_ADJUSTED');
        $publicationProduct = (string) ($row->publication_price_product_code ?? $row->price_product_code ?? '');
        $publicationVersion = (string) ($row->publication_price_product_version ?? $row->price_product_version ?? '');
        $publicationFactorHash = (string) ($row->publication_factor_set_hash ?? $row->factor_set_hash ?? '');

        if ($publicationProduct !== $expectedProduct || $publicationVersion === '' || ! preg_match('/^[a-f0-9]{64}$/', $publicationFactorHash)) {
            $reasons[] = 'PUBLICATION_ANALYTICAL_IDENTITY_INVALID';
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

        $runCoverageGateState = CoverageGateStateNormalizer::normalize($row->run_coverage_gate_state ?? $row->coverage_gate_state ?? null);
        if ((string) $runCoverageGateState !== 'PASS') {
            $reasons[] = 'RUN_COVERAGE_GATE_NOT_PASS';
        } else {
            try {
                (new MarketDataInvariantGuard())->assertNoBypassState([
                    'terminal_status' => $runTerminalStatus,
                    'publishability_state' => $runPublishabilityState,
                    'coverage_gate_state' => $runCoverageGateState,
                    'expected_universe_count' => $row->run_coverage_universe_count ?? $row->coverage_universe_count ?? null,
                    'available_eod_count' => $row->run_coverage_available_count ?? $row->coverage_available_count ?? null,
                    'missing_eod_count' => $row->run_coverage_missing_count ?? $row->coverage_missing_count ?? null,
                    'coverage_ratio' => $row->run_coverage_ratio ?? $row->coverage_ratio ?? null,
                    'coverage_threshold_value' => $row->run_coverage_min_threshold ?? $row->coverage_min_threshold ?? null,
                    'coverage_threshold_mode' => $row->run_coverage_threshold_mode ?? $row->coverage_threshold_mode ?? null,
                    'coverage_universe_basis' => $row->run_coverage_universe_basis ?? $row->coverage_universe_basis ?? null,
                    'coverage_contract_version' => $row->run_coverage_contract_version ?? $row->coverage_contract_version ?? null,
                ], 'EodPublicationRepository::currentPointerIntegrity');
            } catch (\Throwable $e) {
                $reasons[] = 'RUN_COVERAGE_TELEMETRY_INVALID';
            }
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

        if ((string) ($row->run_price_product_code ?? '') !== $publicationProduct
            || (string) ($row->run_price_product_version ?? '') !== $publicationVersion
            || (string) ($row->run_factor_set_hash ?? '') !== $publicationFactorHash) {
            $reasons[] = 'RUN_ANALYTICAL_IDENTITY_MISMATCH';
        }

        if ($publicationProduct === $expectedProduct
            && $publicationVersion !== ''
            && preg_match('/^[a-f0-9]{64}$/', $publicationFactorHash)
            && ! $this->analyticalRowsMatchPublicationIdentity((object) [
                'publication_id' => $row->publication_id,
                'price_product_code' => $publicationProduct,
                'price_product_version' => $publicationVersion,
                'factor_set_hash' => $publicationFactorHash,
            ])) {
            $reasons[] = 'ANALYTICAL_ROW_IDENTITY_MISMATCH';
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
        $row = DB::table('eod_current_publication_pointer as ptr')
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
            ->whereNotNull('run.coverage_universe_count')
            ->where('run.coverage_universe_count', '>', 0)
            ->whereNotNull('run.coverage_available_count')
            ->whereNotNull('run.coverage_missing_count')
            ->whereNotNull('run.coverage_ratio')
            ->whereNotNull('run.coverage_min_threshold')
            ->whereNotNull('run.coverage_threshold_mode')
            ->whereNotNull('run.coverage_universe_basis')
            ->whereNotNull('run.coverage_contract_version')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
            ->whereColumn('run.price_product_code', 'pub.price_product_code')
            ->whereColumn('run.price_product_version', 'pub.price_product_version')
            ->whereColumn('run.factor_set_hash', 'pub.factor_set_hash')
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.publication_id as pointer_publication_id',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.sealed_at as run_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.coverage_gate_state as run_coverage_gate_state',
                'run.coverage_universe_count as run_coverage_universe_count',
                'run.coverage_available_count as run_coverage_available_count',
                'run.coverage_missing_count as run_coverage_missing_count',
                'run.coverage_ratio as run_coverage_ratio',
                'run.coverage_min_threshold as run_coverage_min_threshold',
                'run.coverage_threshold_mode as run_coverage_threshold_mode',
                'run.coverage_universe_basis as run_coverage_universe_basis',
                'run.coverage_contract_version as run_coverage_contract_version',
                'run.is_current_publication as run_is_current_publication',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->first();

        return $this->readablePublicationRowOrNull(
            $row,
            $tradeDate,
            'EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate'
        );
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
        $row = DB::table('eod_current_publication_pointer as ptr')
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
            ->whereNotNull('run.coverage_universe_count')
            ->where('run.coverage_universe_count', '>', 0)
            ->whereNotNull('run.coverage_available_count')
            ->whereNotNull('run.coverage_missing_count')
            ->whereNotNull('run.coverage_ratio')
            ->whereNotNull('run.coverage_min_threshold')
            ->whereNotNull('run.coverage_threshold_mode')
            ->whereNotNull('run.coverage_universe_basis')
            ->whereNotNull('run.coverage_contract_version')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
            ->whereColumn('run.price_product_code', 'pub.price_product_code')
            ->whereColumn('run.price_product_version', 'pub.price_product_version')
            ->whereColumn('run.factor_set_hash', 'pub.factor_set_hash')
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.publication_id as pointer_publication_id',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.sealed_at as run_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.coverage_gate_state as run_coverage_gate_state',
                'run.coverage_universe_count as run_coverage_universe_count',
                'run.coverage_available_count as run_coverage_available_count',
                'run.coverage_missing_count as run_coverage_missing_count',
                'run.coverage_ratio as run_coverage_ratio',
                'run.coverage_min_threshold as run_coverage_min_threshold',
                'run.coverage_threshold_mode as run_coverage_threshold_mode',
                'run.coverage_universe_basis as run_coverage_universe_basis',
                'run.coverage_contract_version as run_coverage_contract_version',
                'run.is_current_publication as run_is_current_publication',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->first();

        return $this->readablePublicationRowOrNull(
            $row,
            $tradeDate,
            'EodPublicationRepository::findReadableCurrentPublicationForRun'
        );
    }

    public function findCorrectionBaselinePublicationForTradeDate($tradeDate)
    {
        $row = DB::table('eod_current_publication_pointer as ptr')
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
            ->whereNotNull('run.coverage_universe_count')
            ->where('run.coverage_universe_count', '>', 0)
            ->whereNotNull('run.coverage_available_count')
            ->whereNotNull('run.coverage_missing_count')
            ->whereNotNull('run.coverage_ratio')
            ->whereNotNull('run.coverage_min_threshold')
            ->whereNotNull('run.coverage_threshold_mode')
            ->whereNotNull('run.coverage_universe_basis')
            ->whereNotNull('run.coverage_contract_version')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
            ->whereColumn('run.price_product_code', 'pub.price_product_code')
            ->whereColumn('run.price_product_version', 'pub.price_product_version')
            ->whereColumn('run.factor_set_hash', 'pub.factor_set_hash')
            ->select(
                'pub.*',
                'ptr.trade_date as pointer_trade_date',
                'ptr.publication_id as pointer_publication_id',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.sealed_at as run_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.coverage_gate_state as run_coverage_gate_state',
                'run.coverage_universe_count as run_coverage_universe_count',
                'run.coverage_available_count as run_coverage_available_count',
                'run.coverage_missing_count as run_coverage_missing_count',
                'run.coverage_ratio as run_coverage_ratio',
                'run.coverage_min_threshold as run_coverage_min_threshold',
                'run.coverage_threshold_mode as run_coverage_threshold_mode',
                'run.coverage_universe_basis as run_coverage_universe_basis',
                'run.coverage_contract_version as run_coverage_contract_version',
                'run.is_current_publication as run_is_current_publication',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->first();

        return $this->readablePublicationRowOrNull(
            $row,
            $tradeDate,
            'EodPublicationRepository::findCorrectionBaselinePublicationForTradeDate'
        );
    }

    public function getOrCreateCandidatePublication(EodRun $run, $supersedesPublicationId = null, $allowSealed = false)
    {
        return DB::transaction(function () use ($run, $supersedesPublicationId, $allowSealed) {
            $existing = DB::table('eod_publications')
                ->where('run_id', $run->run_id)
                ->where('trade_date', $run->trade_date_requested)
                ->orderByDesc('publication_id')
                ->lockForUpdate()
                ->first();

            if ($existing && $this->candidatePublicationIsReusable($existing, $supersedesPublicationId, $allowSealed)) {
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
                'config_snapshot_id' => $run->config_snapshot_id ?? null,
                'factor_set_id' => null,
                'factor_set_hash' => null,
                'observation_manifest_hash' => $run->observation_manifest_hash ?? null,
                'publication_manifest_hash' => null,
                'price_product_code' => (string) config('market_data.indicators.price_product_default', 'STRUCTURAL_ADJUSTED'),
                'price_product_version' => (string) config('market_data.indicators.price_product_version', 'structural_adjusted_v1'),
                'read_model_version' => 'market_data_read_product_v1',
                'readiness_state' => 'NOT_READY',
                'sealed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        });
    }

    private function candidatePublicationIsReusable($publication, $supersedesPublicationId, $allowSealed = false): bool
    {
        if (! $publication) {
            return false;
        }

        if ((string) ($publication->seal_state ?? '') === 'SEALED' && ! $allowSealed) {
            return false;
        }

        if ($supersedesPublicationId === null || $supersedesPublicationId === '') {
            return true;
        }

        $supersedesPublicationId = (int) $supersedesPublicationId;

        return (int) ($publication->supersedes_publication_id ?? 0) === $supersedesPublicationId
            || (int) ($publication->previous_publication_id ?? 0) === $supersedesPublicationId
            || (int) ($publication->replaced_publication_id ?? 0) === $supersedesPublicationId;
    }

    public function findByRunId($runId)
    {
        return DB::table('eod_publications')->where('run_id', $runId)->orderByDesc('publication_id')->first();
    }

    public function updateCandidateHashes($publicationId, array $hashes)
    {
        $this->assertPublicationMutable($publicationId);

        $now = Carbon::now(config('market_data.platform.timezone'));
        $publication = DB::table('eod_publications')
            ->where('publication_id', $publicationId)
            ->first();

        DB::table('eod_publications')
            ->where('publication_id', $publicationId)
            ->update([
                'bars_batch_hash' => $hashes['bars_batch_hash'],
                'indicators_batch_hash' => $hashes['indicators_batch_hash'],
                'eligibility_batch_hash' => $hashes['eligibility_batch_hash'],
                'updated_at' => $now,
            ]);

        if ($publication && ! empty($publication->run_id)) {
            DB::table('eod_runs')
                ->where('run_id', $publication->run_id)
                ->update([
                    'bars_batch_hash' => $hashes['bars_batch_hash'],
                    'indicators_batch_hash' => $hashes['indicators_batch_hash'],
                    'eligibility_batch_hash' => $hashes['eligibility_batch_hash'],
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * Bind a candidate to the acquisition set and configuration that produced it.
     *
     * A null `$configSnapshotId` is written as null on purpose: that is the `CONFIG_UNBOUND` state
     * from `Platform_Config_Registry_LOCKED.md:31`, and recording it is what lets a later seal
     * refuse the candidate. The observation manifest is bound regardless, because which
     * observations produced a candidate is knowable even when the configuration is not.
     */
    public function bindCandidateAcquisitionProvenance($publicationId, $runId, $observationManifestHash, $configSnapshotId)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        DB::table('eod_runs')->where('run_id', $runId)->update([
            'observation_manifest_hash' => $observationManifestHash,
            'updated_at' => $now,
        ]);

        DB::table('eod_publications')->where('publication_id', $publicationId)->update([
            'config_snapshot_id' => $configSnapshotId,
            'observation_manifest_hash' => $observationManifestHash,
            'read_model_version' => 'market_data_read_product_v1',
            'readiness_state' => 'NOT_READY',
            'updated_at' => $now,
        ]);
    }

    /**
     * Persist the one-basis-per-run identity even when the run produces zero analytical rows.
     */
    public function bindCandidateAnalyticalProduct($publicationId, $runId, $priceProductCode, $priceProductVersion, $factorSetHash)
    {
        $this->assertPublicationMutable($publicationId);
        $priceProductCode = strtoupper(trim((string) $priceProductCode));
        $priceProductVersion = trim((string) $priceProductVersion);
        $factorSetHash = strtolower(trim((string) $factorSetHash));

        if ($priceProductCode !== 'STRUCTURAL_ADJUSTED') {
            throw new \RuntimeException('ANALYTICAL_PRICE_PRODUCT_INVALID: publication must bind STRUCTURAL_ADJUSTED.');
        }
        if ($priceProductVersion === '' || ! preg_match('/^[a-f0-9]{64}$/', $factorSetHash)) {
            throw new \RuntimeException('ANALYTICAL_PRODUCT_IDENTITY_INCOMPLETE.');
        }

        $now = Carbon::now(config('market_data.platform.timezone'));
        DB::transaction(function () use ($publicationId, $runId, $priceProductCode, $priceProductVersion, $factorSetHash, $now) {
            $publication = DB::table('eod_publications')
                ->where('publication_id', $publicationId)
                ->where('run_id', $runId)
                ->lockForUpdate()
                ->first();

            if (! $publication) {
                throw new \RuntimeException('ANALYTICAL_PUBLICATION_RUN_MISMATCH.');
            }

            $identity = [
                'price_product_code' => $priceProductCode,
                'price_product_version' => $priceProductVersion,
                'factor_set_hash' => $factorSetHash,
                'updated_at' => $now,
            ];

            DB::table('eod_publications')->where('publication_id', $publicationId)->update($identity);
            DB::table('eod_runs')->where('run_id', $runId)->update($identity);
        });
    }

    public function sealCandidatePublication(EodRun $run, $sealedBy, $sealNote = null)
    {
        return DB::transaction(function () use ($run) {
            $candidate = $this->getOrCreateCandidatePublication($run, null);

            if (! $candidate->bars_batch_hash || ! $candidate->indicators_batch_hash || ! $candidate->eligibility_batch_hash) {
                throw new \RuntimeException('DATASET_HASH_MISSING: Cannot seal publication before all candidate hashes exist.');
            }

            $this->assertPublicationIntegrityContextComplete($candidate, $run, false);

            $now = Carbon::now(config('market_data.platform.timezone'));

            $this->assertPublicationMutable($candidate->publication_id);

            DB::table('eod_publications')
                ->where('publication_id', $candidate->publication_id)
                ->update([
                    'seal_state' => 'SEALED',
                    // Recorded on the publication, not inferred later: a consumer must be able to
                    // see what this seal covers without reconstructing which run produced it.
                    'seal_provenance_scope' => $this->sealProvenanceScope($candidate, $run),
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
            $this->assertPublicationIntegrityContextComplete($candidate, $run, true);
            $now = Carbon::now(config('market_data.platform.timezone'));

            $this->assertPublicationMutable($candidate->publication_id);

            DB::table('eod_publications')
                ->where('publication_id', $candidate->publication_id)
                ->update([
                    'seal_state' => 'SEALED',
                    'seal_provenance_scope' => $this->sealProvenanceScope($candidate, $run),
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
                throw new \RuntimeException('RUN_PUBLICATION_LINK_MISSING: Candidate publication not found for finalize/current-switch.');
            }

            $freshRun = DB::table('eod_runs')
                ->where('run_id', $run->run_id)
                ->lockForUpdate()
                ->first();

            if (! $freshRun) {
                throw new \RuntimeException('PUBLICATION_RUN_NOT_FOUND: Candidate run not found for finalize/current-switch.');
            }

            if ($candidate->seal_state !== 'SEALED') {
                throw new \RuntimeException('FINALIZE_SEAL_INVALID: POINTER_PUBLICATION_SEAL_INVALID: Candidate publication is not sealed.');
            }

            if (! $candidate->sealed_at) {
                throw new \RuntimeException('FINALIZE_SEAL_MISSING: POINTER_PUBLICATION_SEAL_INVALID: Candidate publication is missing sealed_at timestamp.');
            }

            if ((string) ($freshRun->coverage_gate_state ?? '') !== 'PASS') {
                throw new \RuntimeException('POINTER_PUBLICATION_STATE_INVALID: Current publication promotion requires coverage_gate_state PASS before pointer switch.');
            }

            if ((string) ($freshRun->terminal_status ?? '') !== 'SUCCESS'
                || (string) ($freshRun->publishability_state ?? '') !== 'READABLE'
            ) {
                throw new \RuntimeException('PUBLICATION_RUN_STATE_INVALID: Current publication promotion requires pre-approved SUCCESS + READABLE run before pointer switch.');
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
                        'POINTER_PUBLICATION_LINK_INVALID: Invalid current publication integrity detected for trade date '.$run->trade_date_requested.'. Repair current pointer/current mirrors before replacement. Reasons: '.implode(',', $integrityReasons)
                    );
                }

                if (! $forceReplace) {
                    throw new \RuntimeException('CURRENT_PUBLICATION_REPLACE_BLOCKED: Current publication already exists for trade date '.$run->trade_date_requested.'. Use --force_replace=true with an audit reason to replace it via operator-controlled switch.');
                }

                $priorPublicationId = (int) $current->publication_id;
            }

            if ($priorPublicationId && $current && (int) $current->publication_id !== (int) $priorPublicationId) {
                throw new \RuntimeException('CORRECTION_BASELINE_LINK_INVALID: Correction baseline no longer matches current publication pointer.');
            }

            $this->assertSealedPublicationMatchesRunHashes($candidate, $freshRun);

            $now = Carbon::now(config('market_data.platform.timezone'));

            /*
             * Pointer promotion is only reached after finalize decision says the
             * candidate is publishable. Validate the intended final run state
             * without persisting READABLE before the pointer/current mirrors are
             * switched; otherwise a failed switch could briefly create an invalid
             * run/publication combination inside the transaction.
             */
            $guardRun = (array) DB::table('eod_runs')
                ->where('run_id', $run->run_id)
                ->lockForUpdate()
                ->first();

            $guardRun['terminal_status'] = 'SUCCESS';
            $guardRun['publishability_state'] = 'READABLE';
            $guardRun['quality_gate_state'] = 'PASS';
            $guardRun['publication_id'] = $candidate->publication_id;
            $guardRun['publication_version'] = $candidate->publication_version;
            $guardRun['is_current_publication'] = 1;

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

            $priorRun = DB::table('eod_runs')
                ->where('run_id', $priorRunId)
                ->lockForUpdate()
                ->first();

            if (! $priorRun) {
                throw new \RuntimeException('POINTER_ORPHAN_DETECTED: Current publication integrity violation: current pointer requires existing run row.');
            }

            if ((string) ($priorRun->trade_date_requested ?? '') !== (string) $tradeDate) {
                throw new \RuntimeException('POINTER_PUBLICATION_TRADE_DATE_MISMATCH: Current publication integrity violation: current pointer requires run trade_date_requested to match pointer trade_date.');
            }

            if ((string) ($priorRun->terminal_status ?? '') !== 'SUCCESS') {
                throw new \RuntimeException('POINTER_PUBLICATION_STATE_INVALID: Current publication integrity violation: current pointer requires run terminal_status SUCCESS.');
            }

            if ((string) ($priorRun->publishability_state ?? '') !== 'READABLE') {
                throw new \RuntimeException('POINTER_PUBLICATION_STATE_INVALID: Current publication integrity violation: current pointer requires run publishability_state READABLE.');
            }

            if ((string) ($priorRun->coverage_gate_state ?? '') !== 'PASS') {
                throw new \RuntimeException('POINTER_PUBLICATION_STATE_INVALID: Current publication integrity violation: current pointer requires run coverage_gate_state PASS.');
            }

            if (empty($priorRun->sealed_at)) {
                throw new \RuntimeException('POINTER_PUBLICATION_SEAL_INVALID: Current publication integrity violation: current pointer requires run sealed_at.');
            }

            if ((string) ($priorRun->publication_id ?? '') !== (string) $priorPublicationId) {
                throw new \RuntimeException('RUN_PUBLICATION_MIRROR_MISMATCH: Current publication integrity violation: current pointer requires run publication_id to match publication.');
            }

            if ((string) ($priorRun->publication_version ?? '') !== (string) ($priorPublication->publication_version ?? '')) {
                throw new \RuntimeException('RUN_PUBLICATION_MIRROR_MISMATCH: Current publication integrity violation: current pointer requires run publication_version to match publication.');
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

            $this->assertCurrentPointerResolvedAfterSwitch(
                $tradeDate,
                $priorPublicationId,
                $priorRunId,
                'EodPublicationRepository::restorePriorCurrentPublication'
            );

            return DB::table('eod_publications')->where('publication_id', $priorPublicationId)->first();
        });
    }

    /**
     * What a seal on this candidate actually covers.
     *
     * `FULL` — config identity and acquisition provenance both. `ANALYTICAL_ONLY` — the run
     * recomputed analytics over existing bars and there was no acquisition manifest to carry
     * forward, so the seal covers the analytical identity and states nothing about acquisition.
     *
     * A carried-forward manifest outranks the mode: a recompute that inherits real acquisition
     * provenance is fully sealed, because the provenance is present and true regardless of which
     * run first recorded it.
     */
    private function sealProvenanceScope($publication, EodRun $run): string
    {
        if (trim((string) ($publication->observation_manifest_hash ?? '')) !== '') {
            return 'FULL';
        }

        return (string) ($run->promote_mode ?? '') === 'analytical_remediation_current'
            ? 'ANALYTICAL_ONLY'
            : 'FULL';
    }

    private function assertPublicationIntegrityContextComplete($publication, EodRun $run, $allowPartial = false): void
    {
        $missing = [];

        if (empty($publication->publication_id)) {
            $missing[] = 'publication_id';
        }
        if (empty($publication->run_id) || (int) $publication->run_id !== (int) $run->run_id) {
            $missing[] = 'run_id';
        }
        if (empty($publication->trade_date) || (string) $publication->trade_date !== (string) $run->trade_date_requested) {
            $missing[] = 'trade_date';
        }

        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $hashField) {
            if (! $allowPartial && empty($publication->{$hashField})) {
                $missing[] = $hashField;
            }
        }

        foreach (['bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written'] as $rowCountField) {
            if (! $allowPartial && (empty($run->{$rowCountField}) && (int) ($run->{$rowCountField} ?? 0) <= 0)) {
                $missing[] = $rowCountField;
            }
        }

        if ((string) ($publication->price_product_code ?? '') !== (string) config('market_data.indicators.price_product_default', 'STRUCTURAL_ADJUSTED')) {
            $missing[] = 'price_product_code';
        }
        if (empty($publication->price_product_version)) {
            $missing[] = 'price_product_version';
        }
        if (empty($publication->factor_set_hash) || ! preg_match('/^[a-f0-9]{64}$/', (string) $publication->factor_set_hash)) {
            $missing[] = 'factor_set_hash';
        }
        if (! $this->analyticalRowsMatchPublicationIdentity($publication)) {
            $missing[] = 'analytical_row_identity';
        }

        foreach (['price_product_code', 'price_product_version', 'factor_set_hash'] as $identityField) {
            if ((string) ($run->{$identityField} ?? '') !== (string) ($publication->{$identityField} ?? '')) {
                $missing[] = 'run_'.$identityField;
            }
        }

        if (! empty($run->config_snapshot_id)) {
            if (empty($publication->config_snapshot_id) || (int) $publication->config_snapshot_id !== (int) $run->config_snapshot_id) {
                $missing[] = 'config_snapshot_id';
            }
            // Only a run that acquired observations can present an acquisition manifest. A
            // recompute run recomputes analytics over bars that already exist; demanding a manifest
            // from it asks it to attest to work it did not do, and that demand is what stopped
            // every promote run the moment config binding woke this gate. A run that did acquire
            // still fails here exactly as before.
            if ($this->sealProvenanceScope($publication, $run) === 'FULL') {
                if (empty($publication->observation_manifest_hash) || (string) $publication->observation_manifest_hash !== (string) $run->observation_manifest_hash) {
                    $missing[] = 'observation_manifest_hash';
                }
            }
        }

        if ($missing !== []) {
            throw new \RuntimeException('DATASET_MANIFEST_INVALID: Candidate publication cannot be sealed; missing integrity context: '.implode(',', array_unique($missing)));
        }
    }

    private function assertSealedPublicationMatchesRunHashes($publication, $run): void
    {
        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $hashField) {
            $publicationHash = (string) ($publication->{$hashField} ?? '');
            $runHash = (string) ($run->{$hashField} ?? '');

            if ($publicationHash === '' || $runHash === '') {
                throw new \RuntimeException('FINALIZE_HASH_MISSING: '.$hashField.' is required before readable publication promotion.');
            }

            if (! hash_equals($publicationHash, $runHash)) {
                throw new \RuntimeException('FINALIZE_HASH_MISMATCH: '.$hashField.' differs between run and publication candidate.');
            }
        }
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

    private function assertCurrentPointerResolvedAfterSwitch($tradeDate, $publicationId, $runId, $context): void
    {
        $raw = $this->findRawCurrentPublicationStateForTradeDate($tradeDate);
        $reasons = $this->determineCurrentIntegrityViolationReasons($raw);

        if ($reasons !== []) {
            throw new \RuntimeException($context.': invalid current pointer state after switch. Reasons: '.implode(',', $reasons));
        }

        if ((int) ($raw->pointer_publication_id ?? 0) !== (int) $publicationId) {
            throw new \RuntimeException($context.': current pointer publication_id mismatch after switch.');
        }

        if ((int) ($raw->pointer_run_id ?? 0) !== (int) $runId) {
            throw new \RuntimeException($context.': current pointer run_id mismatch after switch.');
        }

        $resolved = $this->resolveCurrentReadablePublicationForTradeDate($tradeDate);

        if (! $resolved) {
            throw new \RuntimeException($context.': current pointer did not resolve to a readable publication after switch.');
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

    /**
     * Discard a candidate that will never be published.
     *
     * The seal check is the whole safety of this method. Its name says candidate, but nothing
     * enforced that, and it deletes the snapshot sets *and* the publication row — so called with a
     * sealed id it would perform the most complete violation rule 9 of
     * `Canonical_Row_History_and_Versioning_Policy_LOCKED.md` describes: sealed snapshot content
     * deleted by an operator path, leaving no record that it ever existed.
     */
    public function discardCandidatePublication($publicationId)
    {
        DB::transaction(function () use ($publicationId) {
            $this->assertPublicationMutable($publicationId);

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
        $row = DB::table('eod_current_publication_pointer as ptr')
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
            ->whereNotNull('run.coverage_universe_count')
            ->where('run.coverage_universe_count', '>', 0)
            ->whereNotNull('run.coverage_available_count')
            ->whereNotNull('run.coverage_missing_count')
            ->whereNotNull('run.coverage_ratio')
            ->whereNotNull('run.coverage_min_threshold')
            ->whereNotNull('run.coverage_threshold_mode')
            ->whereNotNull('run.coverage_universe_basis')
            ->whereNotNull('run.coverage_contract_version')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
            ->whereColumn('run.price_product_code', 'pub.price_product_code')
            ->whereColumn('run.price_product_version', 'pub.price_product_version')
            ->whereColumn('run.factor_set_hash', 'pub.factor_set_hash')
            ->orderByDesc('ptr.trade_date')
            ->select(
                'pub.*',
                'ptr.trade_date as readable_trade_date',
                'ptr.publication_id as pointer_publication_id',
                'ptr.run_id as pointer_run_id',
                'ptr.publication_version as pointer_publication_version',
                'ptr.sealed_at as pointer_sealed_at',
                'run.sealed_at as run_sealed_at',
                'run.terminal_status as run_terminal_status',
                'run.publishability_state as run_publishability_state',
                'run.coverage_gate_state as run_coverage_gate_state',
                'run.coverage_universe_count as run_coverage_universe_count',
                'run.coverage_available_count as run_coverage_available_count',
                'run.coverage_missing_count as run_coverage_missing_count',
                'run.coverage_ratio as run_coverage_ratio',
                'run.coverage_min_threshold as run_coverage_min_threshold',
                'run.coverage_threshold_mode as run_coverage_threshold_mode',
                'run.coverage_universe_basis as run_coverage_universe_basis',
                'run.coverage_contract_version as run_coverage_contract_version',
                'run.is_current_publication as run_is_current_publication',
                'run.publication_id as run_publication_id',
                'run.publication_version as run_publication_version'
            )
            ->first();

        return $this->readablePublicationRowOrNull(
            $row,
            $row ? $row->readable_trade_date : null,
            'EodPublicationRepository::findLatestReadablePublicationBefore'
        );
    }


    private function readablePublicationRowOrNull($row, $tradeDate, string $context)
    {
        if (! $row) {
            return null;
        }

        if ((string) ($row->price_product_code ?? '') !== (string) config('market_data.indicators.price_product_default', 'STRUCTURAL_ADJUSTED')
            || empty($row->price_product_version)
            || ! preg_match('/^[a-f0-9]{64}$/', (string) ($row->factor_set_hash ?? ''))) {
            return null;
        }

        if (! $this->analyticalRowsMatchPublicationIdentity($row)) {
            return null;
        }

        $runState = [
            'terminal_status' => $row->run_terminal_status ?? $row->terminal_status ?? null,
            'publishability_state' => $row->run_publishability_state ?? $row->publishability_state ?? null,
            'coverage_gate_state' => CoverageGateStateNormalizer::normalize($row->run_coverage_gate_state ?? $row->coverage_gate_state ?? null),
            'expected_universe_count' => $row->run_coverage_universe_count ?? $row->coverage_universe_count ?? null,
            'available_eod_count' => $row->run_coverage_available_count ?? $row->coverage_available_count ?? null,
            'missing_eod_count' => $row->run_coverage_missing_count ?? $row->coverage_missing_count ?? null,
            'coverage_ratio' => $row->run_coverage_ratio ?? $row->coverage_ratio ?? null,
            'coverage_threshold_value' => $row->run_coverage_min_threshold ?? $row->coverage_min_threshold ?? null,
            'coverage_threshold_mode' => $row->run_coverage_threshold_mode ?? $row->coverage_threshold_mode ?? null,
            'coverage_universe_basis' => $row->run_coverage_universe_basis ?? $row->coverage_universe_basis ?? null,
            'coverage_contract_version' => $row->run_coverage_contract_version ?? $row->coverage_contract_version ?? null,
        ];

        try {
            (new MarketDataInvariantGuard())->assertValidPointerTarget($row, $runState, $tradeDate, $context);
        } catch (\Throwable $e) {
            return null;
        }

        return $row;
    }

    private function analyticalRowsMatchPublicationIdentity($publication)
    {
        foreach (['eod_indicators', 'eod_indicators_history'] as $table) {
            $query = DB::table($table)->where('publication_id', $publication->publication_id);
            if (! $query->exists()) {
                continue;
            }

            if ((clone $query)->where(function ($row) use ($publication) {
                $row->whereNull('price_product_code')
                    ->orWhere('price_product_code', '<>', $publication->price_product_code)
                    ->orWhereNull('price_product_version')
                    ->orWhere('price_product_version', '<>', $publication->price_product_version)
                    ->orWhereNull('factor_set_hash')
                    ->orWhere('factor_set_hash', '<>', $publication->factor_set_hash);
            })->exists()) {
                return false;
            }
        }

        return true;
    }

    public function buildManifestByPublicationId($publicationId)
    {
        $row = DB::table('eod_publications as pub')
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
                'pub.price_product_code',
                'pub.price_product_version',
                'pub.factor_set_id',
                'pub.factor_set_hash',
                'pub.config_snapshot_id',
                'run.config_version as config_identity',
                'pub.bars_batch_hash',
                'pub.indicators_batch_hash',
                'pub.eligibility_batch_hash',
                'run.bars_rows_written',
                'run.indicators_rows_written',
                'run.eligibility_rows_written',
                'run.trade_date_requested',
                'run.trade_date_effective',
                'run.source as source_mode',
                'run.source_name',
                'run.source_provider',
                'run.promote_mode',
                'run.publish_target',
                'run.coverage_universe_count',
                'run.coverage_available_count',
                'run.coverage_missing_count',
                'run.coverage_ratio',
                'run.coverage_min_threshold',
                'run.coverage_gate_state',
                'run.coverage_threshold_mode',
                'run.coverage_universe_basis',
                'run.coverage_contract_version',
                'pub.source_file_hash',
                'pub.source_file_hash_algorithm',
                'pub.source_file_size_bytes',
                'pub.source_file_row_count'
            )
            ->first();

        if (! $row) {
            return null;
        }

        $manifest = (array) $row;
        $manifest['manifest_schema_version'] = 'market_data_dataset_integrity_manifest_v1';
        $manifest['artifact_type'] = 'market_data_eod_publication';
        $manifest['artifact_version'] = (int) $row->publication_version;
        $manifest['dataset_scope'] = [
            'bars' => 'eod_bars/eod_bars_history',
            'indicators' => 'eod_indicators/eod_indicators_history',
            'eligibility' => 'eod_eligibility/eod_eligibility_history',
        ];
        $manifest['hash_algorithm'] = config('market_data.hash.algorithm', 'SHA-256');
        $manifest['hash_delimiter'] = config('market_data.hash.delimiter', '|');
        $manifest['hash_line_separator'] = config('market_data.hash.line_separator', "\n");
        $manifest['hash_null_token'] = config('market_data.hash.null_token', '[empty]');
        $manifest['canonical_ordering_rule'] = 'trade_date ASC, ticker_id ASC; DeterministicHashService sorts canonical serialized rows before hashing';
        $manifest['component_hashes'] = [
            'bars_batch_hash' => $row->bars_batch_hash,
            'indicators_batch_hash' => $row->indicators_batch_hash,
            'eligibility_batch_hash' => $row->eligibility_batch_hash,
        ];
        $manifest['component_row_counts'] = [
            'bars_rows_written' => $row->bars_rows_written,
            'indicators_rows_written' => $row->indicators_rows_written,
            'eligibility_rows_written' => $row->eligibility_rows_written,
        ];
        $manifest['component_column_contract'] = [
            'bars' => \App\Application\MarketData\Services\MarketDataPipelineService::BARS_HASH_COLUMNS,
            'indicators' => \App\Application\MarketData\Services\MarketDataPipelineService::INDICATORS_HASH_COLUMNS,
            'eligibility' => \App\Application\MarketData\Services\MarketDataPipelineService::ELIGIBILITY_HASH_COLUMNS,
        ];
        $manifest['coverage_context'] = [
            'coverage_universe_count' => $row->coverage_universe_count,
            'coverage_available_count' => $row->coverage_available_count,
            'coverage_missing_count' => $row->coverage_missing_count,
            'coverage_ratio' => $row->coverage_ratio,
            'coverage_min_threshold' => $row->coverage_min_threshold,
            'coverage_gate_state' => CoverageGateStateNormalizer::normalize($row->coverage_gate_state),
            'legacy_coverage_gate_state_raw' => CoverageGateStateNormalizer::legacyRaw($row->coverage_gate_state),
            'coverage_threshold_mode' => $row->coverage_threshold_mode,
            'coverage_universe_basis' => $row->coverage_universe_basis,
            'coverage_contract_version' => $row->coverage_contract_version,
        ];
        $manifest['source_context'] = [
            'source_mode' => $row->source_mode,
            'source_name' => $row->source_name,
            'source_provider' => $row->source_provider,
            'source_file_hash' => $row->source_file_hash,
            'source_file_hash_algorithm' => $row->source_file_hash_algorithm,
            'source_file_size_bytes' => $row->source_file_size_bytes,
            'source_file_row_count' => $row->source_file_row_count,
        ];
        $manifest['seal_verification_status'] = $row->seal_state === 'SEALED' && $row->sealed_at ? 'VERIFIED_BY_STORED_HASH_CONTEXT' : 'NOT_VERIFIED_UNSEALED';
        $manifest['seal_verification_reason_code'] = $row->seal_state === 'SEALED' && $row->sealed_at ? 'DATASET_HASH_VERIFIED' : 'DATASET_SEAL_INVALID';

        return (object) $manifest;
    }

}
