<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Carbon\Carbon;

class MarketDataBackfillService
{
    private $calendar;
    private $pipeline;
    private $runs;
    private $evidence;
    private $sourceAttemptTelemetryCache = [];

    public function __construct(MarketCalendarRepository $calendar, MarketDataPipelineService $pipeline, EodRunRepository $runs = null, EodEvidenceRepository $evidence = null)
    {
        $this->calendar = $calendar;
        $this->pipeline = $pipeline;
        $this->runs = $runs;
        $this->evidence = $evidence;
    }

    public function execute($startDate, $endDate, $sourceMode = null, $outputDir = null, $continueOnError = false)
    {
        $this->guardDateRange($startDate, $endDate);

        $sourceMode = $sourceMode ?: config('market_data.pipeline.default_source_mode');
        $dates = $this->calendar->tradingDatesBetween($startDate, $endDate);
        if ($dates === []) {
            throw new \RuntimeException('Backfill requires at least one trading date in market_calendar for the requested range.');
        }

        $requestMode = $this->resolveRequestMode($startDate, $endDate, $dates);

        $outputDir = $outputDir ?: storage_path('app/market_data/evidence/backfills/backfill_'.$startDate.'_to_'.$endDate.'_'.Carbon::now(config('market_data.platform.timezone'))->format('Ymd_His'));
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $cases = [];
        $telemetryCases = [];
        $allImported = true;

        foreach ($dates as $requestedDate) {
            try {
                $run = $requestMode === 'single_day'
                    ? $this->pipeline->importSingleDay($requestedDate, $sourceMode, null)
                    : $this->pipeline->importDaily($requestedDate, $sourceMode, null);

                $case = [
                    'requested_date' => $requestedDate,
                    'run_id' => (int) $run->run_id,
                ] + $this->buildSourceContextFromRun($run);

                $case = array_merge($case, $this->buildImportContext($run));
                if (($case['import_status'] ?? null) !== 'IMPORTED' || $this->failedRunCountsAsDeterministicFail($run)) {
                    $case = array_merge($case, $this->buildDeterministicFailureContext($run));
                }
                $case['status'] = $this->resolveImportCaseStatus($case);
                if ($case['status'] === 'FAIL') {
                    unset($case['error_class'], $case['error_message']);
                }

                if (($case['import_status'] ?? null) !== 'IMPORTED') {
                    $allImported = false;
                }

                $cases[] = $case;

                $telemetryCase = $this->buildSourceAttemptTelemetryCase($requestedDate, $run);
                if ($telemetryCase !== null) {
                    $telemetryCases[] = $telemetryCase;
                }

                if (($case['import_status'] ?? null) !== 'IMPORTED' && ! $continueOnError) {
                    break;
                }

                if ($this->shouldStopAfterSingleManualFileCase($requestMode, $sourceMode, $case, $continueOnError)) {
                    break;
                }
            } catch (\Throwable $e) {
                $allImported = false;
                $failedRun = $this->runs ? $this->runs->findLatestForRequestedDate($requestedDate, $sourceMode) : null;
                $case = [
                    'requested_date' => $requestedDate,
                    'status' => 'ERROR',
                    'error_class' => get_class($e),
                    'error_message' => $e->getMessage(),
                    'import_status' => 'IMPORT_FAILED',
                ];

                if ($failedRun) {
                    $case = [
                        'requested_date' => $requestedDate,
                        'run_id' => (int) $failedRun->run_id,
                    ] + $this->buildSourceContextFromRun($failedRun);

                    $case = array_merge($case, $this->buildImportContext($failedRun));
                    $case = array_merge($case, $this->buildDeterministicFailureContext($failedRun));
                    $case['status'] = $this->resolveImportCaseStatus($case, true);
                    if ($case['status'] === 'ERROR') {
                        $case['error_class'] = get_class($e);
                        $case['error_message'] = $e->getMessage();
                    }

                    $telemetryCase = $this->buildSourceAttemptTelemetryCase($requestedDate, $failedRun);
                    if ($telemetryCase !== null) {
                        $telemetryCases[] = $telemetryCase;
                    }
                }

                if (($case['import_status'] ?? null) !== 'IMPORTED') {
                    $allImported = false;
                }

                $cases[] = $case;

                if (! $continueOnError) {
                    break;
                }
            }
        }

        $telemetryArtifactPath = $this->writeSourceAttemptTelemetryArtifact($outputDir, $startDate, $endDate, $sourceMode, $telemetryCases);

        $summary = [
            'suite' => 'market_data_backfill_minimum',
            'range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'source_mode' => $sourceMode,
            'request_mode' => $requestMode,
            'trading_dates' => $dates,
            'all_imported' => $allImported,
            'all_passed' => $allImported && count($cases) === count($dates) && ! $this->didStopEarlyBeforeImportFailure($cases, $dates),
            'cases' => $cases,
            'output_dir' => $outputDir,
            'source_attempt_telemetry_artifact' => $telemetryArtifactPath,
        ];

        file_put_contents(
            $outputDir.'/market_data_backfill_summary.json',
            json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $summary;
    }


    private function buildSourceAttemptTelemetryCase($requestedDate, $run)
    {
        $sourceAttemptTelemetry = $this->buildSourceAttemptTelemetryForRun($run);
        if (! is_array($sourceAttemptTelemetry) || $sourceAttemptTelemetry === []) {
            return null;
        }

        if (! isset($sourceAttemptTelemetry['attempts']) || ! is_array($sourceAttemptTelemetry['attempts']) || $sourceAttemptTelemetry['attempts'] === []) {
            return null;
        }

        return [
            'requested_date' => (string) $requestedDate,
            'run_id' => isset($run->run_id) ? (int) $run->run_id : null,
            'terminal_status' => isset($run->terminal_status) ? (string) $run->terminal_status : null,
            'publishability_state' => isset($run->publishability_state) ? (string) $run->publishability_state : null,
            'telemetry' => $sourceAttemptTelemetry,
        ];
    }

    private function writeSourceAttemptTelemetryArtifact($outputDir, $startDate, $endDate, $sourceMode, array $telemetryCases)
    {
        if ($outputDir === null || trim((string) $outputDir) === '' || $telemetryCases === []) {
            return null;
        }

        $artifact = [
            'suite' => 'market_data_backfill_minimum',
            'range' => [
                'start_date' => (string) $startDate,
                'end_date' => (string) $endDate,
            ],
            'source_mode' => $sourceMode,
            'request_mode' => $this->resolveRequestMode($startDate, $endDate, array_column($telemetryCases, 'requested_date')),
            'cases' => $telemetryCases,
        ];

        $path = $outputDir.'/source_attempt_telemetry.json';

        file_put_contents(
            $path,
            json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $this->normalizeOptionalPathForDisplay($path);
    }

    private function buildSourceContextFromRun($run)
    {
        $notesMap = $this->parseRunNotes((string) ($run->notes ?? ''));
        $sourceAttemptTelemetry = $this->buildSourceAttemptTelemetryForRun($run);
        $sourceContext = $this->mergeSourceContextFromTelemetry([
            'source_name' => $run->source_name ?? (($notesMap['source_name'] ?? '') !== '' ? (string) $notesMap['source_name'] : null),
            'source_input_file' => $run->source_input_file ?? (($notesMap['source_input_file'] ?? '') !== '' ? (string) $notesMap['source_input_file'] : null),
            'provider' => $run->source_provider ?? (($notesMap['source_provider'] ?? '') !== '' ? (string) $notesMap['source_provider'] : null),
            'timeout_seconds' => $run->source_timeout_seconds ?? (isset($notesMap['source_timeout_seconds']) && $notesMap['source_timeout_seconds'] != '' ? (int) $notesMap['source_timeout_seconds'] : null),
            'retry_max' => $run->source_retry_max ?? (isset($notesMap['source_retry_max']) && $notesMap['source_retry_max'] != '' ? (int) $notesMap['source_retry_max'] : null),
            'attempt_count' => $run->source_attempt_count ?? (isset($notesMap['source_attempt_count']) && $notesMap['source_attempt_count'] != '' ? (int) $notesMap['source_attempt_count'] : null),
            'success_after_retry' => array_key_exists('source_success_after_retry', (array) $run) && $run->source_success_after_retry !== null ? ($run->source_success_after_retry ? 'yes' : 'no') : (($notesMap['source_success_after_retry'] ?? '') !== '' ? (string) $notesMap['source_success_after_retry'] : null),
            'final_http_status' => $run->source_final_http_status ?? (isset($notesMap['source_final_http_status']) && $notesMap['source_final_http_status'] != '' ? (int) $notesMap['source_final_http_status'] : null),
            'final_reason_code' => $run->source_final_reason_code ?? (($notesMap['source_final_reason_code'] ?? '') !== '' ? (string) $notesMap['source_final_reason_code'] : null),
            'retry_exhausted' => array_key_exists('source_retry_exhausted', (array) $run) && $run->source_retry_exhausted !== null ? ($run->source_retry_exhausted ? 'yes' : 'no') : (($notesMap['source_retry_exhausted'] ?? '') !== '' ? (string) $notesMap['source_retry_exhausted'] : null),
        ], $sourceAttemptTelemetry);

        $result = [];

        if (($notesMap['final_outcome_note'] ?? null) !== null && $notesMap['final_outcome_note'] !== '') {
            $result['final_outcome_note'] = (string) $notesMap['final_outcome_note'];
        }

        if (($sourceContext['source_name'] ?? null) !== null) {
            $result['source_name'] = $sourceContext['source_name'];
        }

        if (($sourceContext['source_input_file'] ?? null) !== null) {
            $result['source_input_file'] = $this->normalizeOptionalPathForDisplay($sourceContext['source_input_file']);
        }

        if (is_array($sourceAttemptTelemetry)) {
            if (($sourceAttemptTelemetry['event_type'] ?? null) !== null && $sourceAttemptTelemetry['event_type'] !== '') {
                $result['source_attempt_event_type'] = (string) $sourceAttemptTelemetry['event_type'];
            }

            if (array_key_exists('attempt_count', $sourceAttemptTelemetry) && $sourceAttemptTelemetry['attempt_count'] !== null) {
                $result['source_attempt_count'] = (int) $sourceAttemptTelemetry['attempt_count'];
            }
        }

        $sourceSummary = $this->buildSourceSummaryString($sourceContext);
        if ($sourceSummary !== null) {
            $result['source_summary'] = $sourceSummary;
        }

        return $result;
    }

    private function buildSourceAttemptTelemetryForRun($run)
    {
        if ($this->evidence === null || ! isset($run->run_id)) {
            return null;
        }

        $runId = (int) $run->run_id;

        if (! array_key_exists($runId, $this->sourceAttemptTelemetryCache)) {
            $telemetry = $this->evidence->exportRunSourceAttemptTelemetry($runId);
            $this->sourceAttemptTelemetryCache[$runId] = $telemetry === [] ? null : $telemetry;
        }

        return $this->sourceAttemptTelemetryCache[$runId];
    }

    private function mergeSourceContextFromTelemetry(array $sourceContext, $sourceAttemptTelemetry)
    {
        if (! is_array($sourceAttemptTelemetry)) {
            return $sourceContext;
        }

        $merged = $sourceContext;
        $fieldMap = [
            'source_name' => 'source_name',
            'source_input_file' => 'source_input_file',
            'provider' => 'provider',
            'timeout_seconds' => 'timeout_seconds',
            'retry_max' => 'retry_max',
            'attempt_count' => 'attempt_count',
            'success_after_retry' => 'success_after_retry',
            'final_http_status' => 'final_http_status',
            'final_reason_code' => 'final_reason_code',
            'retry_exhausted' => 'retry_exhausted',
        ];

        foreach ($fieldMap as $contextKey => $telemetryKey) {
            $contextHasValue = array_key_exists($contextKey, $merged) && $merged[$contextKey] !== null && $merged[$contextKey] !== '';
            $telemetryHasValue = array_key_exists($telemetryKey, $sourceAttemptTelemetry) && $sourceAttemptTelemetry[$telemetryKey] !== null && $sourceAttemptTelemetry[$telemetryKey] !== '';

            if (! $contextHasValue && $telemetryHasValue) {
                $merged[$contextKey] = $sourceAttemptTelemetry[$telemetryKey];
            }
        }

        return $merged;
    }



    private function resolveRequestMode($startDate, $endDate, array $dates)
    {
        if ((string) $startDate !== (string) $endDate) {
            return 'range';
        }

        if (count($dates) !== 1 || (string) $dates[0] !== (string) $startDate) {
            throw new \RuntimeException('Single-day backfill requires exactly one trading date equal to requested start_date/end_date.');
        }

        return 'single_day';
    }

    private function normalizeOptionalPathForDisplay($path)
    {
        if ($path === null || $path === '') {
            return $path;
        }

        $normalized = str_replace('\\', '/', (string) $path);

        if ($this->looksLikeRelativeProjectPath($normalized)) {
            return basename($normalized);
        }

        return $normalized;
    }

    private function looksLikeRelativeProjectPath($path)
    {
        if ($path === null || $path === '') {
            return false;
        }

        $path = (string) $path;

        if (preg_match('~^[A-Za-z]:/~', $path) === 1) {
            return false;
        }

        if (strpos($path, '//') === 0 || strpos($path, '/') === 0 || strpos($path, '\\') === 0) {
            return false;
        }

        return true;
    }

    private function buildSourceSummaryString(array $sourceContext)
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
            'retry_exhausted' => 'retry_exhausted',
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

    private function parseRunNotes($notes)
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


    private function buildImportContext($run)
    {
        $context = [
            'import_status' => $this->resolveImportStatus($run),
        ];

        if (isset($run->bars_rows_written) && $run->bars_rows_written !== null) {
            $context['import_bars_rows_written'] = (int) $run->bars_rows_written;
        }

        if (isset($run->invalid_bar_count) && $run->invalid_bar_count !== null) {
            $context['import_invalid_bar_count'] = (int) $run->invalid_bar_count;
        }

        if (isset($run->stage) && $run->stage !== null && $run->stage !== '') {
            $context['import_stage_reached'] = (string) $run->stage;
        }

        return $context;
    }

    private function resolveImportStatus($run)
    {
        if ($run === null) {
            return 'IMPORT_FAILED';
        }

        foreach (['bars_rows_written', 'invalid_bar_count', 'publication_version'] as $field) {
            if (isset($run->{$field}) && $run->{$field} !== null && $run->{$field} !== '') {
                return 'IMPORTED';
            }
        }

        $stage = (string) ($run->stage ?? '');

        if (in_array($stage, ['INGEST_BARS', 'COMPUTE_INDICATORS', 'BUILD_ELIGIBILITY', 'HASH', 'SEAL', 'FINALIZE'], true)) {
            return 'IMPORTED';
        }

        return 'IMPORT_FAILED';
    }

    private function buildDeterministicFailureContext($run)
    {
        $context = [];
        $notesMap = $this->parseRunNotes((string) ($run->notes ?? ''));

        foreach ([
            'quality_gate_state' => ['quality_gate_state'],
            'coverage_gate_state' => ['coverage_gate_state'],
            'coverage_ratio' => ['coverage_ratio'],
            'coverage_universe_count' => ['coverage_universe_count'],
            'coverage_available_count' => ['coverage_available_count'],
            'coverage_missing_count' => ['coverage_missing_count'],
            'coverage_min_threshold' => ['coverage_min_threshold'],
            'final_reason_code' => ['final_reason_code', 'source_final_reason_code'],
            'final_outcome_note' => ['final_outcome_note'],
        ] as $outputKey => $fields) {
            foreach ($fields as $field) {
                if (isset($run->{$field}) && $run->{$field} !== null && $run->{$field} !== '') {
                    $context[$outputKey] = $run->{$field};
                    break;
                }

                if (isset($notesMap[$field]) && $notesMap[$field] !== '') {
                    $context[$outputKey] = $notesMap[$field];
                    break;
                }
            }
        }

        if ($this->runs !== null && isset($run->run_id) && method_exists($this->runs, 'findLatestTerminalEventForRun')) {
            try {
                $event = $this->runs->findLatestTerminalEventForRun((int) $run->run_id);
            } catch (\Throwable $e) {
                $event = null;
            }
            if ($event) {
                if ((! isset($context['final_reason_code']) || $context['final_reason_code'] === '') && isset($event->reason_code) && $event->reason_code !== null && $event->reason_code !== '') {
                    $context['final_reason_code'] = (string) $event->reason_code;
                }

                if (isset($event->message) && $event->message !== null && $event->message !== '') {
                    $context['final_reason_message'] = (string) $event->message;
                }
            }
        }

        return $context;
    }


    private function resolveImportCaseStatus(array $case, $encounteredException = false)
    {
        if (($case['import_status'] ?? null) === 'IMPORTED') {
            return 'IMPORTED';
        }

        if (array_key_exists('final_outcome_note', $case) && $case['final_outcome_note'] !== null && $case['final_outcome_note'] !== '') {
            return 'FAIL';
        }

        if (array_key_exists('final_reason_code', $case) && $case['final_reason_code'] !== null && $case['final_reason_code'] !== '') {
            return 'FAIL';
        }

        return $encounteredException ? 'ERROR' : 'FAIL';
    }

    private function didStopEarlyBeforeImportFailure(array $cases, array $dates)
    {
        return count($cases) < count($dates)
            && ($cases === [] || (($cases[count($cases) - 1]['import_status'] ?? null) === 'IMPORTED'));
    }

    private function shouldStopAfterSingleManualFileCase($requestMode, $sourceMode, array $case, $continueOnError)
    {
        if ($continueOnError || $requestMode !== 'range' || (string) $sourceMode !== 'manual_file') {
            return false;
        }

        if (($case['import_status'] ?? null) !== 'IMPORTED') {
            return false;
        }

        return (string) ($case['source_name'] ?? '') === 'LOCAL_FILE'
            && ((string) ($case['source_input_file'] ?? '') !== '');
    }

    private function runCountsAsPass($run)
    {
        return (string) $run->terminal_status === 'SUCCESS'
            && (string) $run->publishability_state === 'READABLE';
    }

    private function failedRunCountsAsDeterministicFail($run)
    {
        $terminalStatus = (string) ($run->terminal_status ?? '');
        $publishabilityState = (string) ($run->publishability_state ?? '');

        return in_array($terminalStatus, ['FAILED', 'HELD'], true)
            && $publishabilityState === 'NOT_READABLE';
    }

    private function guardDateRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate, config('market_data.platform.timezone'))->startOfDay();
        $end = Carbon::parse($endDate, config('market_data.platform.timezone'))->startOfDay();

        if ($end->lt($start)) {
            throw new \RuntimeException('Backfill requires end_date >= start_date.');
        }
    }
}
