<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

abstract class AbstractMarketDataCommand extends Command
{
    protected function requestedDate()
    {
        if ($this->option('requested_date')) {
            return $this->option('requested_date');
        }

        if ($this->option('latest')) {
            return Carbon::now(config('market_data.platform.timezone'))->toDateString();
        }

        return Carbon::now(config('market_data.platform.timezone'))->toDateString();
    }

    protected function sourceMode()
    {
        return $this->option('source_mode') ?: config('market_data.pipeline.default_source_mode');
    }

    protected function makeStageInput($stage)
    {
        return new MarketDataStageInput(
            $this->requestedDate(),
            $this->sourceMode(),
            $this->option('run_id') ?: null,
            $stage,
            $this->option('correction_id') ?: null
        );
    }

    protected function pipeline()
    {
        return $this->container()->make(MarketDataPipelineService::class);
    }

    protected function runRepository()
    {
        return $this->container()->make(EodRunRepository::class);
    }

    protected function evidenceRepository()
    {
        return $this->container()->make(EodEvidenceRepository::class);
    }

    protected function latestRunForRequestedDate($requestedDate = null, $sourceMode = null)
    {
        $requestedDate = $requestedDate ?: $this->requestedDate();
        $sourceMode = $sourceMode ?: $this->sourceMode();

        return $this->runRepository()->findLatestForRequestedDate($requestedDate, $sourceMode);
    }

    protected function container()
    {
        return $this->laravel ?: app();
    }

    protected function renderRecoveredFailureSummary($run, \Throwable $e, array $sourceContext = null)
    {
        if ($run) {
            $this->renderRunSummary($run, $sourceContext);
        }

        $this->error('error='.(string) $e->getMessage());
    }

    protected function renderRunSummary($run, array $sourceContext = null)
    {
        $this->info('run_id='.(string) $this->runField($run, 'run_id', ''));
        $this->line('requested_date='.(string) $this->runField($run, 'trade_date_requested', ''));
        $this->line('stage='.(string) $this->runField($run, 'stage', ''));
        $this->line('lifecycle_state='.(string) $this->runField($run, 'lifecycle_state', ''));
        $this->line('terminal_status='.(string) $this->runField($run, 'terminal_status', ''));
        $this->line('publishability_state='.(string) $this->runField($run, 'publishability_state', ''));

        $promoteMode = $this->runField($run, 'promote_mode');
        if ($promoteMode !== null && $promoteMode !== '') {
            $this->line('promote_mode='.(string) $promoteMode);
        }

        $publishTarget = $this->runField($run, 'publish_target');
        if ($publishTarget !== null && $publishTarget !== '') {
            $this->line('publish_target='.(string) $publishTarget);
        }

        $sourceContext = $sourceContext ?: $this->buildSourceContext($run);

        $this->renderCoverageSummary($run);

        $this->renderSourceSummary($run, $sourceContext);

        $reasonCode = $this->runField($run, 'final_reason_code', $this->runField($run, 'reason_code'));
        if ($reasonCode !== null && $reasonCode !== '') {
            $this->line('reason_code='.(string) $reasonCode);
        }

        $notes = $this->runField($run, 'notes');
        if ($notes !== null && $notes !== '') {
            $this->line('notes='.(string) $notes);
        }
    }



    protected function buildRunSummaryPayload($run, array $overrides = [], array $sourceContext = null)
    {
        $sourceContext = $sourceContext ?: $this->buildSourceContext($run);

        $payload = [
            'run_id' => $this->runField($run, 'run_id'),
            'requested_date' => $this->runField($run, 'trade_date_requested'),
            'stage' => $this->runField($run, 'stage'),
            'lifecycle_state' => $this->runField($run, 'lifecycle_state'),
            'terminal_status' => $this->runField($run, 'terminal_status'),
            'publishability_state' => $this->runField($run, 'publishability_state'),
            'promote_mode' => $this->runField($run, 'promote_mode'),
            'publish_target' => $this->runField($run, 'publish_target'),
            'final_reason_code' => $this->runField($run, 'final_reason_code'),
            'trade_date_effective' => $this->runField($run, 'trade_date_effective'),
            'reason_code' => $this->runField($run, 'reason_code'),
            'notes' => $this->runField($run, 'notes'),
            'coverage_gate_state' => $this->runField($run, 'coverage_gate_state'),
            'coverage_reason_code' => $this->resolveCoverageReasonCode($run, $this->runField($run, 'coverage_gate_state')),
            'coverage_available_count' => $this->runField($run, 'coverage_available_count'),
            'coverage_universe_count' => $this->runField($run, 'coverage_universe_count'),
            'coverage_missing_count' => $this->runField($run, 'coverage_missing_count'),
            'coverage_ratio' => $this->runField($run, 'coverage_ratio'),
            'coverage_min_threshold' => $this->runField($run, 'coverage_min_threshold'),
            'coverage_universe_basis' => $this->runField($run, 'coverage_universe_basis'),
            'coverage_contract_version' => $this->runField($run, 'coverage_contract_version'),
        ];

        if (($sourceContext['source_name'] ?? null) !== null && $sourceContext['source_name'] !== '') {
            $payload['source_name'] = $sourceContext['source_name'];
        }

        if (($sourceContext['source_input_file'] ?? null) !== null && $sourceContext['source_input_file'] !== '') {
            $payload['source_input_file'] = $this->normalizeOptionalPathForDisplay($sourceContext['source_input_file']);
        }

        if (($sourceContext['source_attempt_event_type'] ?? null) !== null && $sourceContext['source_attempt_event_type'] !== '') {
            $payload['source_attempt_event_type'] = $sourceContext['source_attempt_event_type'];
        }

        if (($sourceContext['source_attempt_count'] ?? null) !== null && $sourceContext['source_attempt_count'] !== '') {
            $payload['source_attempt_count'] = $sourceContext['source_attempt_count'];
        }

        $sourceSummary = $this->buildSourceSummaryString($sourceContext);
        if ($sourceSummary !== null) {
            $payload['source_summary'] = $sourceSummary;
        }

        foreach ($overrides as $key => $value) {
            $payload[$key] = $value;
        }

        return array_filter($payload, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    protected function writeRunSummaryArtifact($outputDir, $fileName, array $payload)
    {
        if ($outputDir === null || trim((string) $outputDir) === '') {
            return null;
        }

        $outputDir = rtrim((string) $outputDir, DIRECTORY_SEPARATOR.'/');
        File::ensureDirectoryExists($outputDir);
        $path = $outputDir.DIRECTORY_SEPARATOR.$fileName;
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        return $path;
    }

    protected function normalizePathForDisplay($path)
    {
        return str_replace('\\', '/', (string) $path);
    }

    protected function normalizeOptionalPathForDisplay($path)
    {
        if ($path === null || $path === '') {
            return $path;
        }

        return $this->normalizePathForDisplay($path);
    }

    protected function buildSourceSummaryString(array $sourceContext)
    {
        $summaryParts = [];

        foreach ([
            'provider' => 'provider',
            'timeout_seconds' => 'timeout_seconds',
            'retry_max' => 'retry_max',
            'attempt_count' => 'attempt_count',
            'success_after_retry' => 'success_after_retry',
            'final_http_status' => 'final_http_status',
            'final_reason_code' => 'final_reason_code',
        ] as $key => $label) {
            if (! array_key_exists($key, $sourceContext) || $sourceContext[$key] === null || $sourceContext[$key] === '') {
                continue;
            }

            $summaryParts[] = $label.'='.(string) $sourceContext[$key];
        }

        if ($summaryParts === []) {
            return null;
        }

        return implode(' | ', $summaryParts);
    }

    protected function renderSourceSummary($run, array $sourceContext = null)
    {
        $sourceContext = $sourceContext ?: $this->buildSourceContext($run);
        $sourceName = $sourceContext['source_name'] ?? null;
        $inputFile = $sourceContext['source_input_file'] ?? null;

        if ($sourceName !== null && $sourceName !== '') {
            $this->line('source_name='.(string) $sourceName);
        }

        if ($inputFile !== null && $inputFile !== '') {
            $this->line('source_input_file='.(string) $this->normalizeOptionalPathForDisplay($inputFile));
        }

        if (($sourceContext['source_attempt_event_type'] ?? null) !== null && $sourceContext['source_attempt_event_type'] !== '') {
            $this->line('source_attempt_event_type='.(string) $sourceContext['source_attempt_event_type']);
        }

        if (($sourceContext['source_attempt_count'] ?? null) !== null && $sourceContext['source_attempt_count'] !== '') {
            $this->line('source_attempt_count='.(string) $sourceContext['source_attempt_count']);
        }

        $sourceSummary = $this->buildSourceSummaryString($sourceContext);
        if ($sourceSummary !== null) {
            $this->line('source_summary='.$sourceSummary);
        }
    }

    protected function buildSourceContext($run, array $sourceAttemptTelemetry = null)
    {
        $notesMap = $this->parseRunNotes((string) $this->runField($run, 'notes', ''));
        $sourceSuccessAfterRetry = $this->runField($run, 'source_success_after_retry');
        $sourceRetryExhausted = $this->runField($run, 'source_retry_exhausted');

        $sourceContext = [
            'source_name' => $this->runField($run, 'source_name', $notesMap['source_name'] ?? null),
            'source_input_file' => $this->runField($run, 'source_input_file', $notesMap['source_input_file'] ?? null),
            'provider' => $this->runField($run, 'source_provider', $notesMap['source_provider'] ?? null),
            'timeout_seconds' => $this->runField($run, 'source_timeout_seconds', $notesMap['source_timeout_seconds'] ?? null),
            'retry_max' => $this->runField($run, 'source_retry_max', $notesMap['source_retry_max'] ?? null),
            'attempt_count' => $this->runField($run, 'source_attempt_count', $notesMap['source_attempt_count'] ?? null),
            'success_after_retry' => $sourceSuccessAfterRetry !== null ? ($sourceSuccessAfterRetry ? 'yes' : 'no') : ($notesMap['source_success_after_retry'] ?? null),
            'final_http_status' => $this->runField($run, 'source_final_http_status', $notesMap['source_final_http_status'] ?? null),
            'final_reason_code' => $this->runField($run, 'source_final_reason_code', $notesMap['source_final_reason_code'] ?? null),
            'retry_exhausted' => $sourceRetryExhausted !== null ? ($sourceRetryExhausted ? 'yes' : 'no') : ($notesMap['source_retry_exhausted'] ?? null),
            'source_attempt_event_type' => null,
            'source_attempt_count' => null,
        ];

        if (! $this->shouldRecoverSourceTelemetry($sourceContext)) {
            return $sourceContext;
        }

        return $this->mergeSourceContextFromTelemetry(
            $sourceContext,
            $sourceAttemptTelemetry !== null ? $sourceAttemptTelemetry : $this->exportRunSourceAttemptTelemetry($run)
        );
    }

    protected function exportRunSourceAttemptTelemetry($run)
    {
        $runId = $this->runField($run, 'run_id');
        if ($runId === null || $runId === '') {
            return [];
        }

        $telemetry = $this->evidenceRepository()->exportRunSourceAttemptTelemetry($runId);
        if (! is_array($telemetry)) {
            return [];
        }

        if (array_key_exists('source_input_file', $telemetry) && $telemetry['source_input_file'] !== null && $telemetry['source_input_file'] !== '') {
            $telemetry['source_input_file'] = $this->normalizeOptionalPathForDisplay($telemetry['source_input_file']);
        }

        return $telemetry;
    }

    protected function writeSourceAttemptTelemetryArtifact($outputDir, $run)
    {
        $telemetry = $this->exportRunSourceAttemptTelemetry($run);

        if ($outputDir === null || trim((string) $outputDir) === '') {
            return [null, $telemetry];
        }

        if (! is_array($telemetry) || $telemetry === [] || ! isset($telemetry['attempts']) || ! is_array($telemetry['attempts']) || $telemetry['attempts'] === []) {
            return [null, $telemetry];
        }

        $path = $this->writeRunSummaryArtifact($outputDir, 'source_attempt_telemetry.json', $telemetry);

        return [$path, $telemetry];
    }

    protected function shouldRecoverSourceTelemetry(array $sourceContext)
    {
        $sourceName = isset($sourceContext['source_name']) ? strtoupper((string) $sourceContext['source_name']) : '';
        $hasSourceInputFile = array_key_exists('source_input_file', $sourceContext)
            && $sourceContext['source_input_file'] !== null
            && $sourceContext['source_input_file'] !== '';

        $hasTelemetrySeed = $sourceName !== '' || $hasSourceInputFile;

        foreach (['provider', 'timeout_seconds', 'retry_max', 'attempt_count', 'success_after_retry', 'final_http_status', 'final_reason_code'] as $key) {
            if (array_key_exists($key, $sourceContext) && $sourceContext[$key] !== null && $sourceContext[$key] !== '') {
                $hasTelemetrySeed = true;
                break;
            }
        }

        if (! $hasTelemetrySeed) {
            return false;
        }

        if ($sourceName === 'LOCAL_FILE' || $hasSourceInputFile) {
            return false;
        }

        foreach (['provider', 'timeout_seconds', 'retry_max', 'attempt_count', 'final_reason_code', 'source_attempt_event_type', 'source_attempt_count'] as $key) {
            if (! array_key_exists($key, $sourceContext) || $sourceContext[$key] === null || $sourceContext[$key] === '') {
                return true;
            }
        }

        return false;
    }

    protected function mergeSourceContextFromTelemetry(array $sourceContext, $sourceAttemptTelemetry)
    {
        if (! is_array($sourceAttemptTelemetry)) {
            return $sourceContext;
        }

        $merged = $sourceContext;

        foreach ([
            'source_name' => 'source_name',
            'source_input_file' => 'source_input_file',
            'provider' => 'provider',
            'timeout_seconds' => 'timeout_seconds',
            'retry_max' => 'retry_max',
            'attempt_count' => 'attempt_count',
            'success_after_retry' => 'success_after_retry',
            'final_http_status' => 'final_http_status',
            'final_reason_code' => 'final_reason_code',
            'source_attempt_event_type' => 'event_type',
            'source_attempt_count' => 'attempt_count',
        ] as $contextKey => $telemetryKey) {
            $contextHasValue = array_key_exists($contextKey, $merged) && $merged[$contextKey] !== null && $merged[$contextKey] !== '';
            $telemetryHasValue = array_key_exists($telemetryKey, $sourceAttemptTelemetry) && $sourceAttemptTelemetry[$telemetryKey] !== null && $sourceAttemptTelemetry[$telemetryKey] !== '';

            if (! $contextHasValue && $telemetryHasValue) {
                $merged[$contextKey] = $sourceAttemptTelemetry[$telemetryKey];
            }
        }

        return $merged;
    }

    protected function parseRunNotes($notes)
    {
        if ($notes === '') {
            return [];
        }

        $segments = preg_split('/\s*;\s*/', $notes);
        if (! is_array($segments)) {
            return [];
        }

        $parsed = [];
        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '' || strpos($segment, '=') === false) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            $key = trim((string) $key);
            $value = trim((string) $value);

            if ($key === '') {
                continue;
            }

            $parsed[$key] = $value;
        }

        return $parsed;
    }

    protected function renderCoverageSummary($run)
    {
        $state = $this->runField($run, 'coverage_gate_state');
        $ratio = $this->runField($run, 'coverage_ratio');
        $available = $this->runField($run, 'coverage_available_count');
        $universe = $this->runField($run, 'coverage_universe_count');
        $missing = $this->runField($run, 'coverage_missing_count');
        $threshold = $this->runField($run, 'coverage_min_threshold');
        $basis = $this->runField($run, 'coverage_universe_basis');
        $contract = $this->runField($run, 'coverage_contract_version');
        $coverageReasonCode = $this->resolveCoverageReasonCode($run, $state);

        if ($state === null && $ratio === null && $available === null && $universe === null && $missing === null && $threshold === null && $coverageReasonCode === null) {
            return;
        }

        if ($state !== null && $state !== '') {
            $this->line('coverage_gate_state='.(string) $state);
        }

        if ($coverageReasonCode !== null && $coverageReasonCode !== '') {
            $this->line('coverage_reason_code='.(string) $coverageReasonCode);
        }

        $summaryParts = [];

        if ($available !== null || $universe !== null) {
            $summaryParts[] = 'available='.(string) ($available ?? 'null').'/'.(string) ($universe ?? 'null');
        }

        if ($missing !== null) {
            $summaryParts[] = 'missing='.(string) $missing;
        }

        if ($ratio !== null) {
            $summaryParts[] = 'ratio='.$this->formatCoverageDecimal($ratio);
        }

        if ($threshold !== null) {
            $summaryParts[] = 'threshold='.$this->formatCoverageDecimal($threshold);
        }

        if ($basis !== null && $basis !== '') {
            $summaryParts[] = 'basis='.(string) $basis;
        }

        if ($contract !== null && $contract !== '') {
            $summaryParts[] = 'contract='.(string) $contract;
        }

        if ($summaryParts !== []) {
            $this->line('coverage_summary='.implode(' | ', $summaryParts));
        }

        $missingSample = $this->decodeMissingSample($this->runField($run, 'coverage_missing_sample_json'));
        if ($missingSample !== []) {
            $this->line('coverage_missing_sample='.implode(',', $missingSample));
        }
    }

    protected function resolveCoverageReasonCode($run, $coverageState)
    {
        $reasonCode = $this->runField($run, 'reason_code');

        if ($reasonCode === 'RUN_COVERAGE_LOW' || $reasonCode === 'RUN_COVERAGE_NOT_EVALUABLE') {
            return $reasonCode;
        }

        if ($coverageState === 'PASS') {
            return 'COVERAGE_THRESHOLD_MET';
        }

        if ($coverageState === 'FAIL') {
            return 'COVERAGE_BELOW_THRESHOLD';
        }

        if ($coverageState === 'NOT_EVALUABLE' || $coverageState === 'BLOCKED') {
            return 'RUN_COVERAGE_NOT_EVALUABLE';
        }

        return null;
    }

    protected function decodeMissingSample($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('strval', $value), static function ($item) {
                return $item !== '';
            }));
        }

        $decoded = json_decode((string) $value, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $decoded), static function ($item) {
            return $item !== '';
        }));
    }

    protected function formatCoverageDecimal($value)
    {
        if ($value === null || $value === '') {
            return 'null';
        }

        return number_format((float) $value, 4, '.', '');
    }

    protected function runField($run, $field, $default = null)
    {
        if (is_array($run) && array_key_exists($field, $run)) {
            return $run[$field];
        }

        if (is_object($run) && isset($run->{$field})) {
            return $run->{$field};
        }

        if (is_object($run) && property_exists($run, $field)) {
            return $run->{$field};
        }

        return $default;
    }
}
