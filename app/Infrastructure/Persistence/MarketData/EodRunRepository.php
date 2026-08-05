<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Application\MarketData\Services\CoverageGateStateNormalizer;
use App\Domain\MarketData\MarketDataScope;
use App\Models\EodRun;
use App\Models\EodRunEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EodRunRepository
{
    private $configSnapshots;

    public function __construct(MarketDataConfigSnapshotRepository $configSnapshots = null)
    {
        $this->configSnapshots = $configSnapshots ?: new MarketDataConfigSnapshotRepository();
    }

    public function getOrCreateOwningRun($requestedDate, $sourceMode, $stage, $supersedesRunId = null, $requestMode = null)
    {
        $scope = MarketDataScope::fromConfig();
        $requestedDate = $scope->assertRequestedDate($requestedDate);
        $snapshot = $this->configSnapshots->resolveForRun($requestedDate);

        return DB::transaction(function () use ($requestedDate, $sourceMode, $stage, $supersedesRunId, $requestMode, $scope, $snapshot) {
            $this->cancelStaleActiveRuns($requestedDate, $sourceMode, $requestMode);

            $activeRun = EodRun::query()
                ->where('trade_date_requested', $requestedDate)
                ->where('source', $sourceMode)
                ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
                ->where(function ($query) use ($requestMode) {
                    if ($requestMode === null || trim((string) $requestMode) === '') {
                        $query->whereNull('request_mode')->orWhere('request_mode', '');
                        return;
                    }

                    $query->where('request_mode', $requestMode);
                })
                ->orderByDesc('run_id')
                ->lockForUpdate()
                ->first();

            if ($activeRun) {
                if (empty($activeRun->config_snapshot_id)) {
                    $activeRun->config_snapshot_id = $snapshot['config_snapshot_id'];
                    $activeRun->config_hash = $snapshot['config_hash'];
                    $activeRun->config_snapshot_ref = $snapshot['snapshot_uid'];
                    $activeRun->operational_start_date = $scope->operationalStartDate();
                    $activeRun->freshness_state = $scope->operationalStartDate() ? 'NOT_EVALUATED' : 'DEVELOPMENT_NOT_OPERATIONAL';
                    $activeRun->save();
                }

                return $activeRun;
            }

            $now = Carbon::now(config('market_data.platform.timezone'));

            $run = EodRun::query()->create([
                'trade_date_requested' => $requestedDate,
                'trade_date_effective' => null,
                'lifecycle_state' => 'PENDING',
                'terminal_status' => null,
                'quality_gate_state' => 'PENDING',
                'publishability_state' => 'NOT_READABLE',
                'stage' => $stage,
                'source' => $sourceMode,
                'request_mode' => $requestMode,
                'source_name' => null,
                'source_provider' => null,
                'source_input_file' => null,
                'source_timeout_seconds' => null,
                'source_retry_max' => null,
                'source_attempt_count' => null,
                'source_success_after_retry' => null,
                'source_retry_exhausted' => null,
                'source_final_http_status' => null,
                'source_final_reason_code' => null,
                'source_file_hash' => null,
                'source_file_hash_algorithm' => null,
                'source_file_size_bytes' => null,
                'source_file_row_count' => null,
                'coverage_universe_count' => null,
                'coverage_available_count' => null,
                'coverage_missing_count' => null,
                'coverage_ratio' => null,
                'coverage_min_threshold' => null,
                'coverage_gate_state' => null,
                'coverage_threshold_mode' => null,
                'coverage_universe_basis' => null,
                'coverage_contract_version' => null,
                'coverage_missing_sample_json' => null,
                'bars_rows_written' => null,
                'indicators_rows_written' => null,
                'eligibility_rows_written' => null,
                'invalid_bar_count' => null,
                'invalid_indicator_count' => null,
                'hard_reject_count' => null,
                'warning_count' => null,
                'notes' => null,
                'bars_batch_hash' => null,
                'indicators_batch_hash' => null,
                'eligibility_batch_hash' => null,
                'config_version' => config('market_data.indicators.set_version'),
                'config_hash' => $snapshot['config_hash'],
                'config_snapshot_ref' => $snapshot['snapshot_uid'],
                'config_snapshot_id' => $snapshot['config_snapshot_id'],
                'observation_manifest_hash' => null,
                'operational_start_date' => $scope->operationalStartDate(),
                'freshness_state' => $scope->operationalStartDate() ? 'NOT_EVALUATED' : 'DEVELOPMENT_NOT_OPERATIONAL',
                'supersedes_run_id' => $supersedesRunId,
                'publication_id' => null,
                'publication_version' => null,
                'is_current_publication' => 0,
                'correction_id' => null,
                'promote_mode' => null,
                'publish_target' => null,
                'final_reason_code' => null,
                'sealed_at' => null,
                'sealed_by' => null,
                'seal_note' => null,
                'started_at' => $now,
                'finished_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->appendEvent(
                $run,
                $stage,
                'RUN_CREATED',
                'INFO',
                'Market-data run created with owning run context.',
                null,
                [
                    'run_id' => (int) $run->run_id,
                    'trade_date_requested' => $requestedDate,
                    'trade_date_effective' => null,
                    'source_mode' => $sourceMode,
                    'request_mode' => $requestMode,
                    'supersedes_run_id' => $supersedesRunId,
                    'lifecycle_state' => 'PENDING',
                    'publishability_state' => 'NOT_READABLE',
                    'config_snapshot_id' => (int) $snapshot['config_snapshot_id'],
                    'config_hash' => $snapshot['config_hash'],
                ]
            );

            return $run;
        });
    }


    public function createPromoteRunFromSeed(EodRun $seedRun, $stage, array $overrides = [])
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        $payload = [
            'trade_date_requested' => $seedRun->trade_date_requested,
            'trade_date_effective' => null,
            'lifecycle_state' => 'PENDING',
            'terminal_status' => null,
            'quality_gate_state' => 'PENDING',
            'publishability_state' => 'NOT_READABLE',
            'stage' => $stage,
            'source' => $seedRun->source,
            'request_mode' => $overrides['request_mode'] ?? 'promote',
            'source_name' => $seedRun->source_name,
            'source_provider' => $seedRun->source_provider,
            'source_input_file' => $seedRun->source_input_file,
            'source_timeout_seconds' => $seedRun->source_timeout_seconds,
            'source_retry_max' => $seedRun->source_retry_max,
            'source_attempt_count' => $seedRun->source_attempt_count,
            'source_success_after_retry' => $seedRun->source_success_after_retry,
            'source_retry_exhausted' => $seedRun->source_retry_exhausted,
            'source_final_http_status' => $seedRun->source_final_http_status,
            'source_final_reason_code' => $seedRun->source_final_reason_code,
            'source_file_hash' => $seedRun->source_file_hash,
            'source_file_hash_algorithm' => $seedRun->source_file_hash_algorithm,
            'source_file_size_bytes' => $seedRun->source_file_size_bytes,
            'source_file_row_count' => $seedRun->source_file_row_count,
            'coverage_universe_count' => null,
            'coverage_available_count' => null,
            'coverage_missing_count' => null,
            'coverage_ratio' => null,
            'coverage_min_threshold' => null,
            'coverage_gate_state' => null,
            'coverage_threshold_mode' => null,
            'coverage_universe_basis' => null,
            'coverage_contract_version' => null,
            'coverage_missing_sample_json' => null,
            'bars_rows_written' => $seedRun->bars_rows_written,
            'indicators_rows_written' => null,
            'eligibility_rows_written' => null,
            'invalid_bar_count' => $seedRun->invalid_bar_count,
            'invalid_indicator_count' => null,
            'hard_reject_count' => null,
            'warning_count' => null,
            'notes' => $seedRun->notes,
            'bars_batch_hash' => null,
            'indicators_batch_hash' => null,
            'eligibility_batch_hash' => null,
            'config_version' => $seedRun->config_version ?: config('market_data.indicators.set_version'),
            'config_hash' => $seedRun->config_hash,
            'config_snapshot_ref' => $seedRun->config_snapshot_ref,
            'config_snapshot_id' => $seedRun->config_snapshot_id,
            'observation_manifest_hash' => $seedRun->observation_manifest_hash,
            'operational_start_date' => $seedRun->operational_start_date,
            'freshness_state' => $seedRun->freshness_state,
            'supersedes_run_id' => $seedRun->supersedes_run_id,
            'publication_id' => null,
            'publication_version' => null,
            'is_current_publication' => 0,
            'correction_id' => null,
            'promote_mode' => null,
            'publish_target' => null,
            'final_reason_code' => null,
            'sealed_at' => null,
            'sealed_by' => null,
            'seal_note' => null,
            'started_at' => $now,
            'finished_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        foreach ($overrides as $key => $value) {
            $payload[$key] = $value;
        }

        $run = EodRun::query()->create($payload);

        $this->appendEvent(
            $run,
            $stage,
            'RUN_CREATED',
            'INFO',
            'Market-data promote run created from seed run context.',
            null,
            [
                'run_id' => (int) $run->run_id,
                'seed_run_id' => (int) $seedRun->run_id,
                'trade_date_requested' => $run->trade_date_requested,
                'trade_date_effective' => null,
                'source_mode' => $run->source,
                'request_mode' => $run->request_mode ?? ($overrides['request_mode'] ?? 'promote'),
                'promote_mode' => $run->promote_mode,
                'publish_target' => $run->publish_target,
                'lifecycle_state' => 'PENDING',
                'publishability_state' => 'NOT_READABLE',
            ]
        );

        return $run;
    }

    public function findByRunId($runId)
    {
        return EodRun::query()->where('run_id', $runId)->first();
    }


    public function findLatestForRequestedDate($requestedDate, $sourceMode = null)
    {
        $query = EodRun::query()
            ->where('trade_date_requested', $requestedDate)
            ->orderByDesc('run_id');

        if ($sourceMode !== null && trim((string) $sourceMode) !== '') {
            $query->where('source', $sourceMode);
        }

        return $query->first();
    }


    public function findLatestTerminalEventForRun($runId)
    {
        return EodRunEvent::query()
            ->where('run_id', $runId)
            ->whereIn('event_type', ['RUN_FINALIZED', 'STAGE_FAILED'])
            ->orderByDesc('event_id')
            ->first();
    }

    public function touchStage(EodRun $run, $stage, array $attributes = [])
    {
        $run->stage = $stage;

        if ($stage === 'FINALIZE') {
            $run->lifecycle_state = 'FINALIZING';
        } elseif ($run->lifecycle_state === 'PENDING') {
            $run->lifecycle_state = 'RUNNING';
        }

        foreach ($attributes as $key => $value) {
            $run->{$key} = $value;
        }

        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    public function appendEvent(EodRun $run, $stage, $eventType, $severity, $message, $reasonCode = null, array $payload = [])
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $severity = $this->normalizeSeverity($severity);
        $message = $this->truncateMessage($message);

        return EodRunEvent::query()->create([
            'run_id' => $run->run_id,
            'trade_date_requested' => $run->trade_date_requested,
            'event_time' => $now,
            'stage' => $stage,
            'event_type' => $eventType,
            'severity' => $severity,
            'reason_code' => $reasonCode,
            'message' => $message,
            'event_payload_json' => empty($payload) ? null : json_encode($payload),
            'created_at' => $now,
        ]);
    }

    public function failStage(EodRun $run, $stage, $reasonCode, $message, array $payload = [])
    {
        $safePayload = array_merge([
            'run_id' => (int) $run->run_id,
            'stage' => $stage,
            'reason_code' => $reasonCode,
        ], $payload);

        $this->appendEvent(
            $run,
            $stage,
            'STAGE_FAILED',
            'ERROR',
            $message,
            $reasonCode,
            $safePayload
        );

        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->lifecycle_state = 'FAILED';
        $run->terminal_status = 'FAILED';
        $run->quality_gate_state = 'FAIL';
        $run->publishability_state = 'NOT_READABLE';
        $run->final_reason_code = $reasonCode;
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function holdStage(EodRun $run, $stage, $reasonCode, $message, $tradeDateEffective = null, array $payload = [])
    {
        $safePayload = array_merge([
            'run_id' => (int) $run->run_id,
            'stage' => $stage,
            'reason_code' => $reasonCode,
            'trade_date_effective' => $tradeDateEffective,
        ], $payload);

        $this->appendEvent(
            $run,
            $stage,
            'STAGE_FAILED',
            'WARN',
            $message,
            $reasonCode,
            $safePayload
        );

        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->lifecycle_state = 'COMPLETED';
        $run->terminal_status = 'HELD';
        $run->quality_gate_state = 'BLOCKED';
        $run->publishability_state = 'NOT_READABLE';
        $run->final_reason_code = $reasonCode;
        $run->trade_date_effective = $tradeDateEffective;
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function completeImportOnly(EodRun $run, $stage, $reasonCode = 'IMPORT_ONLY_COMPLETED_NOT_PROMOTED', array $payload = [])
    {
        $safePayload = array_merge([
            'run_id' => (int) $run->run_id,
            'stage' => $stage,
            'reason_code' => $reasonCode,
            'request_mode' => 'import_only',
            'import_status' => 'COMPLETED',
            'promote_status' => 'NOT_PROMOTED',
            'promoted' => false,
            'pointer_switched' => false,
        ], $payload);

        $this->appendEvent(
            $run,
            $stage,
            'IMPORT_ONLY_COMPLETED_NOT_PROMOTED',
            'INFO',
            'Import-only run closed as completed non-readable candidate; promote remains explicit.',
            $reasonCode,
            $safePayload
        );

        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->stage = $stage;
        $run->lifecycle_state = 'COMPLETED';
        $run->terminal_status = null;
        $run->publishability_state = 'NOT_READABLE';
        $run->is_current_publication = 0;
        $run->final_reason_code = $reasonCode;
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function updateTelemetry(EodRun $run, array $telemetry)
    {
        $telemetry = $this->normalizeTelemetry($telemetry);

        foreach ($telemetry as $key => $value) {
            $run->{$key} = $value;
        }

        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    public function storeHashes(EodRun $run, array $hashes)
    {
        $run->bars_batch_hash = $hashes['bars_batch_hash'];
        $run->indicators_batch_hash = $hashes['indicators_batch_hash'];
        $run->eligibility_batch_hash = $hashes['eligibility_batch_hash'];
        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    public function markSealed(EodRun $run, $sealedBy, $sealNote)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->sealed_at = $now;
        $run->sealed_by = $sealedBy;
        $run->seal_note = $sealNote;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function syncCurrentPublicationMirror($tradeDate, $currentRunId)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        EodRun::query()
            ->where('trade_date_requested', $tradeDate)
            ->update([
                'is_current_publication' => 0,
                'updated_at' => $now,
            ]);

        EodRun::query()
            ->where('run_id', $currentRunId)
            ->update([
                'is_current_publication' => 1,
                'updated_at' => $now,
            ]);
    }


    private function cancelStaleActiveRuns($requestedDate, $sourceMode, $requestMode = null): void
    {
        $staleMinutes = (int) config('market_data.pipeline.active_run_stale_minutes', 1440);

        if ($staleMinutes <= 0) {
            return;
        }

        $now = Carbon::now(config('market_data.platform.timezone'));
        $cutoff = $now->copy()->subMinutes($staleMinutes);

        $query = EodRun::query()
            ->where('trade_date_requested', $requestedDate)
            ->where('source', $sourceMode)
            ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
            ->whereNull('finished_at')
            ->where('updated_at', '<', $cutoff)
            ->where(function ($query) use ($requestMode) {
                if ($requestMode === null || trim((string) $requestMode) === '') {
                    $query->whereNull('request_mode')->orWhere('request_mode', '');
                    return;
                }

                $query->where('request_mode', $requestMode)
                    ->orWhereNull('request_mode')
                    ->orWhere('request_mode', '');
            })
            ->orderBy('run_id')
            ->lockForUpdate();

        foreach ($query->get() as $staleRun) {
            $previousState = (string) ($staleRun->lifecycle_state ?? '');
            $previousUpdatedAt = $staleRun->updated_at ? (string) $staleRun->updated_at : null;

            $this->appendEvent(
                $staleRun,
                $staleRun->stage ?: 'INGEST_BARS',
                'STALE_ACTIVE_RUN_CANCELLED',
                'WARN',
                'Stale active run cancelled before creating or reusing an owning run.',
                'STALE_ACTIVE_RUN_CANCELLED',
                [
                    'run_id' => (int) $staleRun->run_id,
                    'trade_date_requested' => $requestedDate,
                    'source_mode' => $sourceMode,
                    'request_mode' => $requestMode,
                    'previous_lifecycle_state' => $previousState,
                    'previous_updated_at' => $previousUpdatedAt,
                    'stale_after_minutes' => $staleMinutes,
                    'cutoff_at' => (string) $cutoff,
                ]
            );

            $staleRun->lifecycle_state = 'CANCELLED';
            $staleRun->terminal_status = null;
            $staleRun->publishability_state = 'NOT_READABLE';
            $staleRun->final_reason_code = 'STALE_ACTIVE_RUN_CANCELLED';
            $staleRun->finished_at = $now;
            $staleRun->updated_at = $now;
            $staleRun->save();
        }
    }

    private function normalizeTelemetry(array $telemetry)
    {
        if (array_key_exists('expected_universe_count', $telemetry) && ! array_key_exists('coverage_universe_count', $telemetry)) {
            $telemetry['coverage_universe_count'] = $telemetry['expected_universe_count'];
        }

        if (array_key_exists('available_eod_count', $telemetry) && ! array_key_exists('coverage_available_count', $telemetry)) {
            $telemetry['coverage_available_count'] = $telemetry['available_eod_count'];
        }

        if (array_key_exists('missing_eod_count', $telemetry) && ! array_key_exists('coverage_missing_count', $telemetry)) {
            $telemetry['coverage_missing_count'] = $telemetry['missing_eod_count'];
        }

        if (array_key_exists('coverage_gate_status', $telemetry) && ! array_key_exists('coverage_gate_state', $telemetry)) {
            $telemetry['coverage_gate_state'] = $telemetry['coverage_gate_status'];
        }
        unset($telemetry['coverage_gate_status']);

        if (array_key_exists('coverage_gate_state', $telemetry)) {
            $telemetry['coverage_gate_state'] = CoverageGateStateNormalizer::normalize($telemetry['coverage_gate_state']);
        }

        if (array_key_exists('coverage_threshold_value', $telemetry) && ! array_key_exists('coverage_min_threshold', $telemetry)) {
            $telemetry['coverage_min_threshold'] = $telemetry['coverage_threshold_value'];
        }

        if (array_key_exists('coverage_calibration_version', $telemetry) && ! array_key_exists('coverage_contract_version', $telemetry)) {
            $telemetry['coverage_contract_version'] = $telemetry['coverage_calibration_version'];
        }

        if (array_key_exists('missing_ticker_codes', $telemetry) && ! array_key_exists('coverage_missing_sample_json', $telemetry)) {
            $telemetry['coverage_missing_sample_json'] = $telemetry['missing_ticker_codes'];
        }

        if (array_key_exists('coverage_missing_sample_json', $telemetry) && is_array($telemetry['coverage_missing_sample_json'])) {
            $telemetry['coverage_missing_sample_json'] = json_encode(array_values($telemetry['coverage_missing_sample_json']));
        }

        foreach (['source_success_after_retry', 'source_retry_exhausted'] as $booleanField) {
            if (array_key_exists($booleanField, $telemetry) && $telemetry[$booleanField] !== null) {
                $telemetry[$booleanField] = $telemetry[$booleanField] ? 1 : 0;
            }
        }

        return $telemetry;
    }

    private function normalizeSeverity($severity)
    {
        if ($severity === 'WARNING') {
            return 'WARN';
        }

        return in_array($severity, ['INFO', 'WARN', 'ERROR'], true) ? $severity : 'ERROR';
    }

    private function truncateMessage($message)
    {
        $message = trim((string) $message);
        if ($message === '') {
            return null;
        }

        return mb_strlen($message) <= 255 ? $message : mb_substr($message, 0, 252).'...';
    }

    public function finalize(EodRun $run, array $finalState)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        foreach ($finalState as $key => $value) {
            $run->{$key} = $value;
        }

        $run->lifecycle_state = $finalState['lifecycle_state'];
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }
}
