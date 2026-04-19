<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;

class MarketDataEvidenceExportService
{
    private function field($record, $name, $default = null)
    {
        return is_object($record) && property_exists($record, $name) ? $record->{$name} : $default;
    }

    private $evidence;
    private $publications;
    private $corrections;

    public function __construct(
        EodEvidenceRepository $evidence,
        EodPublicationRepository $publications,
        EodCorrectionRepository $corrections
    ) {
        $this->evidence = $evidence;
        $this->publications = $publications;
        $this->corrections = $corrections;
    }

    public function exportRunEvidence($runId, $outputDir = null)
    {
        $run = $this->evidence->findRunById($runId);
        if (! $run) {
            throw new \RuntimeException('Run not found for evidence export.');
        }

        $publication = $this->resolvePublicationForRun($run);
        $manifest = $publication ? (array) $this->publications->buildManifestByPublicationId($publication->publication_id) : null;
        $runSummary = $this->buildRunSummary($run, $manifest);
        $sourceAttemptTelemetry = $this->buildSourceAttemptTelemetry($run, $runSummary['source_context'] ?? []);
        $runSummary['source_context'] = $this->normalizeSourceContextPaths(
            $this->mergeSourceContextFromTelemetry(
                is_array($runSummary['source_context'] ?? null) ? $runSummary['source_context'] : [],
                $sourceAttemptTelemetry
            )
        );
        $sourceAttemptTelemetry = $this->normalizeSourceAttemptTelemetryPaths($sourceAttemptTelemetry);
        $sourceSummary = $this->buildSourceSummaryString($runSummary['source_context'] ?? []);
        $eventSummary = ['run_id' => (int) $this->field($run, 'run_id'), 'trade_date_requested' => $run->trade_date_requested] + $this->evidence->summarizeRunEvents($run->run_id);
        $dominantReasonCodes = $this->evidence->dominantReasonCodes($run->run_id, $this->resolvedTradeDate($run), $publication ? $publication->publication_id : null);
        $eligibilityRows = $this->evidence->exportEligibilityRows($this->resolvedTradeDate($run), $publication ? $publication->publication_id : null);
        $invalidBarsRows = $this->evidence->exportInvalidBarsRows($run->trade_date_requested);
        $anomalyReport = $this->buildAnomalyReport($runSummary, $dominantReasonCodes, $manifest);

        $payload = [
            'run_summary' => $runSummary,
            'publication_manifest' => $manifest,
            'run_event_summary' => $eventSummary,
            'dominant_reason_codes' => $dominantReasonCodes,
            'publication_resolution' => [
                'trade_date_effective' => $runSummary['trade_date_effective'],
                'publication_version' => $manifest ? (int) $manifest['publication_version'] : null,
                'is_current_publication' => $manifest ? (bool) $manifest['is_current'] : false,
            ],
            'source_attempt_telemetry' => $sourceAttemptTelemetry,
        ];

        $dir = $outputDir ?: $this->defaultRunOutputDir($run->run_id);
        $this->ensureDirectory($dir);
        $this->writeJson($dir.'/run_summary.json', $runSummary);
        if ($manifest) {
            $this->writeJson($dir.'/publication_manifest.json', $manifest);
        }
        $this->writeJson($dir.'/run_event_summary.json', $eventSummary);
        if ($sourceAttemptTelemetry !== null) {
            $this->writeJson($dir.'/source_attempt_telemetry.json', $sourceAttemptTelemetry);
        }
        $this->writeCsv($dir.'/eligibility_export.csv', ['trade_date', 'ticker_id', 'eligible', 'reason_code'], $eligibilityRows);
        $this->writeCsv($dir.'/invalid_bars_export.csv', ['trade_date', 'ticker_id', 'source', 'source_row_ref', 'invalid_reason_code'], $invalidBarsRows);
        file_put_contents($dir.'/anomaly_report.md', $anomalyReport);
        $this->writeJson($dir.'/evidence_pack.json', $payload);

        $files = array_values(array_filter([
            'run_summary.json',
            $manifest ? 'publication_manifest.json' : null,
            'run_event_summary.json',
            $sourceAttemptTelemetry !== null ? 'source_attempt_telemetry.json' : null,
            'eligibility_export.csv',
            'invalid_bars_export.csv',
            'anomaly_report.md',
            'evidence_pack.json',
        ]));

        return [
            'selector' => ['type' => 'run', 'id' => (int) $run->run_id],
            'summary' => [
                'run_id' => (int) $this->field($run, 'run_id'),
                'trade_date_requested' => $runSummary['trade_date_requested'],
                'trade_date_effective' => $runSummary['trade_date_effective'],
                'terminal_status' => $runSummary['terminal_status'],
                'publishability_state' => $runSummary['publishability_state'],
                'source_name' => $runSummary['source_context']['source_name'] ?? null,
                'source_input_file' => isset($runSummary['source_context']['source_input_file'])
                    ? $this->normalizeOptionalPathForDisplay($runSummary['source_context']['source_input_file'])
                    : null,
                'source_summary' => $sourceSummary,
                'source_attempt_event_type' => $sourceAttemptTelemetry['event_type'] ?? null,
                'source_attempt_count' => $sourceAttemptTelemetry['attempt_count'] ?? null,
            ],
            'output_dir' => $dir,
            'file_count' => count($files),
            'files' => $files,
        ];
    }

    public function exportCorrectionEvidence($correctionId, $outputDir = null)
    {
        $correction = $this->evidence->findCorrectionById($correctionId);
        if (! $correction) {
            throw new \RuntimeException('Correction not found for evidence export.');
        }

        $priorPublication = $correction->prior_publication_id ? $this->evidence->findPublicationById($correction->prior_publication_id) : null;
        $newPublication = $correction->new_publication_id ? $this->evidence->findPublicationById($correction->new_publication_id) : null;
        $payload = [
            'correction_id' => (int) $correction->correction_id,
            'trade_date' => $correction->trade_date,
            'approval' => [
                'approved_by' => $correction->approved_by,
                'approved_at' => $correction->approved_at,
            ],
            'prior_publication' => $priorPublication ? [
                'publication_id' => (int) $priorPublication->publication_id,
                'run_id' => (int) $priorPublication->run_id,
                'publication_version' => (int) $priorPublication->publication_version,
                'is_current' => (bool) $priorPublication->is_current,
            ] : null,
            'new_publication' => $newPublication ? [
                'publication_id' => (int) $newPublication->publication_id,
                'run_id' => (int) $newPublication->run_id,
                'publication_version' => (int) $newPublication->publication_version,
                'is_current' => (bool) $newPublication->is_current,
            ] : null,
            'old_hashes' => $priorPublication ? [
                'bars_batch_hash' => $priorPublication->bars_batch_hash,
                'indicators_batch_hash' => $priorPublication->indicators_batch_hash,
                'eligibility_batch_hash' => $priorPublication->eligibility_batch_hash,
            ] : null,
            'new_hashes' => $newPublication ? [
                'bars_batch_hash' => $newPublication->bars_batch_hash,
                'indicators_batch_hash' => $newPublication->indicators_batch_hash,
                'eligibility_batch_hash' => $newPublication->eligibility_batch_hash,
            ] : null,
            'publication_switch' => $newPublication ? (bool) $newPublication->is_current : false,
            'status' => $correction->status,
            'final_outcome_note' => $correction->final_outcome_note ?? null,
            'comparison_summary' => $this->buildCorrectionComparisonSummary($priorPublication, $newPublication),
        ];

        $dir = $outputDir ?: $this->defaultCorrectionOutputDir($correction->correction_id);
        $this->ensureDirectory($dir);
        $this->writeJson($dir.'/correction_evidence.json', $payload);

        $files = ['correction_evidence.json'];

        return [
            'selector' => ['type' => 'correction', 'id' => (int) $correction->correction_id],
            'summary' => [
                'correction_id' => (int) $correction->correction_id,
                'trade_date' => $correction->trade_date,
                'status' => $correction->status,
                'publication_switch' => $payload['publication_switch'],
            ],
            'output_dir' => $dir,
            'file_count' => count($files),
            'files' => $files,
        ];
    }

    public function exportReplayEvidence($replayId, $tradeDate = null, $outputDir = null)
    {
        $metric = $this->evidence->findReplayMetric($replayId, $tradeDate);
        if (! $metric) {
            throw new \RuntimeException('Replay result not found for evidence export.');
        }

        $reasonCodes = $this->evidence->replayReasonCodeCounts($metric->replay_id, $metric->trade_date);
        $expectedReasonCodeCounts = $this->decodeExpectedReasonCodeCounts($metric->expected_reason_code_counts_json ?? null);
        $replayResult = $this->buildReplayResult($metric);
        $expectedState = $this->buildReplayExpectedState($metric, $expectedReasonCodeCounts);
        $actualState = $this->buildReplayActualState($metric, $reasonCodes);
        $summary = [
            'replay_id' => (int) $metric->replay_id,
            'trade_date' => $metric->trade_date,
            'comparison_result' => $metric->comparison_result,
            'status' => $metric->status,
            'config_identity' => $metric->config_identity,
            'reason_code_count' => count($reasonCodes),
        ];
        $payload = [
            'replay_result' => $replayResult,
            'expected_state' => $expectedState,
            'actual_state' => $actualState,
            'reason_code_counts' => $reasonCodes,
            'summary' => $summary,
        ];

        $dir = $outputDir ?: $this->defaultReplayOutputDir($metric->replay_id, $metric->trade_date);
        $this->ensureDirectory($dir);
        $this->writeJson($dir.'/replay_result.json', $replayResult);
        $this->writeJson($dir.'/replay_expected_state.json', $expectedState);
        $this->writeJson($dir.'/replay_actual_state.json', $actualState);
        $this->writeJson($dir.'/replay_reason_code_counts.json', $reasonCodes);
        $this->writeJson($dir.'/replay_evidence_pack.json', $payload);

        $files = [
            'replay_result.json',
            'replay_expected_state.json',
            'replay_actual_state.json',
            'replay_reason_code_counts.json',
            'replay_evidence_pack.json',
        ];

        return [
            'selector' => ['type' => 'replay', 'id' => (int) $metric->replay_id],
            'summary' => [
                'replay_id' => (int) $metric->replay_id,
                'trade_date' => $metric->trade_date,
                'comparison_result' => $metric->comparison_result,
                'status' => $metric->status,
            ],
            'output_dir' => $dir,
            'file_count' => count($files),
            'files' => $files,
        ];
    }

    private function resolvePublicationForRun($run)
    {
        if ($run->terminal_status !== 'SUCCESS' || $run->publishability_state !== 'READABLE') {
            throw new \RuntimeException('Run evidence export requires a SUCCESS + READABLE run; non-readable runs cannot be consumed through publication read path.');
        }

        $publication = $this->publications->findReadableCurrentPublicationForRun($run->run_id, $run->trade_date_requested);
        if (! $publication) {
            throw new \RuntimeException('Readable current publication not found for run evidence export.');
        }

        return $publication;
    }

    private function buildRunSummary($run, $manifest = null)
    {
        $sourceContext = $this->buildSourceContext($run);

        $sourceContext = $this->normalizeSourceContextPaths($sourceContext);

        return [
            'run_id' => (int) $this->field($run, 'run_id'),
            'trade_date_requested' => $this->field($run, 'trade_date_requested'),
            'trade_date_effective' => $this->field($run, 'trade_date_effective'),
            'lifecycle_state' => $this->field($run, 'lifecycle_state'),
            'terminal_status' => $this->field($run, 'terminal_status'),
            'quality_gate_state' => $this->field($run, 'quality_gate_state'),
            'publishability_state' => $this->field($run, 'publishability_state'),
            'stage' => $this->field($run, 'stage'),
            'source' => $this->field($run, 'source'),
            'final_reason_code' => $this->field($run, 'final_reason_code'),
            'source_context' => $sourceContext,
            'coverage' => $this->buildCoverageState($run),
            'coverage_ratio' => $this->field($run, 'coverage_ratio') !== null ? (float) $this->field($run, 'coverage_ratio') : null,
            'bars_rows_written' => $this->field($run, 'bars_rows_written') !== null ? (int) $this->field($run, 'bars_rows_written') : null,
            'indicators_rows_written' => $this->field($run, 'indicators_rows_written') !== null ? (int) $this->field($run, 'indicators_rows_written') : null,
            'eligibility_rows_written' => $this->field($run, 'eligibility_rows_written') !== null ? (int) $this->field($run, 'eligibility_rows_written') : null,
            'invalid_bar_count' => $this->field($run, 'invalid_bar_count') !== null ? (int) $this->field($run, 'invalid_bar_count') : null,
            'invalid_indicator_count' => $this->field($run, 'invalid_indicator_count') !== null ? (int) $this->field($run, 'invalid_indicator_count') : null,
            'warning_count' => $this->field($run, 'warning_count') !== null ? (int) $this->field($run, 'warning_count') : null,
            'hard_reject_count' => $this->field($run, 'hard_reject_count') !== null ? (int) $this->field($run, 'hard_reject_count') : null,
            'bars_batch_hash' => $this->field($run, 'bars_batch_hash'),
            'indicators_batch_hash' => $this->field($run, 'indicators_batch_hash'),
            'eligibility_batch_hash' => $this->field($run, 'eligibility_batch_hash'),
            'sealed_at' => $this->field($run, 'sealed_at'),
            'config_version' => $this->field($run, 'config_version'),
            'config_hash' => $this->field($run, 'config_hash'),
            'config_snapshot_ref' => $this->field($run, 'config_snapshot_ref'),
            'publication_id' => $this->field($run, 'publication_id') !== null ? (int) $this->field($run, 'publication_id') : ($manifest ? (int) $manifest['publication_id'] : null),
            'publication_version' => $manifest ? (int) $manifest['publication_version'] : ($this->field($run, 'publication_version') !== null ? (int) $this->field($run, 'publication_version') : null),
            'is_current_publication' => $manifest ? (bool) $manifest['is_current'] : (bool) $this->field($run, 'is_current_publication', false),
            'supersedes_run_id' => $this->field($run, 'supersedes_run_id') !== null ? (int) $this->field($run, 'supersedes_run_id') : null,
            'correction_id' => $this->field($run, 'correction_id') !== null ? (int) $this->field($run, 'correction_id') : null,
            'started_at' => $this->field($run, 'started_at'),
            'finished_at' => $this->field($run, 'finished_at'),
        ];
    }


    private function buildSourceAttemptTelemetry($run, array $sourceContext)
    {
        $telemetry = $this->evidence->exportRunSourceAttemptTelemetry($run->run_id);
        if ($telemetry === []) {
            return null;
        }

        if (($telemetry['source_name'] ?? null) === null && ($sourceContext['source_name'] ?? null) !== null) {
            $telemetry['source_name'] = $sourceContext['source_name'];
        }

        if (($telemetry['source_input_file'] ?? null) === null && ($sourceContext['source_input_file'] ?? null) !== null) {
            $telemetry['source_input_file'] = $sourceContext['source_input_file'];
        }

        if (($telemetry['provider'] ?? null) === null && ($sourceContext['provider'] ?? null) !== null) {
            $telemetry['provider'] = $sourceContext['provider'];
        }

        if (($telemetry['timeout_seconds'] ?? null) === null && array_key_exists('timeout_seconds', $sourceContext)) {
            $telemetry['timeout_seconds'] = $sourceContext['timeout_seconds'];
        }

        if (($telemetry['retry_max'] ?? null) === null && array_key_exists('retry_max', $sourceContext)) {
            $telemetry['retry_max'] = $sourceContext['retry_max'];
        }

        if (($telemetry['attempt_count'] ?? null) === null && array_key_exists('attempt_count', $sourceContext)) {
            $telemetry['attempt_count'] = $sourceContext['attempt_count'];
        }

        if (($telemetry['success_after_retry'] ?? null) === null && ($sourceContext['success_after_retry'] ?? null) !== null) {
            $telemetry['success_after_retry'] = $sourceContext['success_after_retry'];
        }

        if (($telemetry['final_http_status'] ?? null) === null && array_key_exists('final_http_status', $sourceContext)) {
            $telemetry['final_http_status'] = $sourceContext['final_http_status'];
        }

        if (($telemetry['final_reason_code'] ?? null) === null && ($sourceContext['final_reason_code'] ?? null) !== null) {
            $telemetry['final_reason_code'] = $sourceContext['final_reason_code'];
        }

        return $this->normalizeSourceAttemptTelemetryPaths($telemetry);
    }

    private function buildSourceContext($record)
    {
        $notesMap = $this->parseRunNotes((string) ($record->notes ?? ''));

        return [
            'source_name' => $record->source_name ?? ($notesMap['source_name'] ?? null),
            'source_input_file' => $record->source_input_file ?? ($notesMap['source_input_file'] ?? null),
            'provider' => $record->source_provider ?? ($notesMap['source_provider'] ?? null),
            'timeout_seconds' => $this->normalizeNullableInt($record->source_timeout_seconds ?? (isset($notesMap['source_timeout_seconds']) && $notesMap['source_timeout_seconds'] !== '' ? $notesMap['source_timeout_seconds'] : null)),
            'retry_max' => $this->normalizeNullableInt($record->source_retry_max ?? (isset($notesMap['source_retry_max']) && $notesMap['source_retry_max'] !== '' ? $notesMap['source_retry_max'] : null)),
            'attempt_count' => $this->normalizeNullableInt($record->source_attempt_count ?? (isset($notesMap['source_attempt_count']) && $notesMap['source_attempt_count'] !== '' ? $notesMap['source_attempt_count'] : null)),
            'success_after_retry' => property_exists($record, 'source_success_after_retry') && $record->source_success_after_retry !== null ? ($record->source_success_after_retry ? 'yes' : 'no') : ($notesMap['source_success_after_retry'] ?? null),
            'final_http_status' => $this->normalizeNullableInt($record->source_final_http_status ?? (isset($notesMap['source_final_http_status']) && $notesMap['source_final_http_status'] !== '' ? $notesMap['source_final_http_status'] : null)),
            'final_reason_code' => $record->source_final_reason_code ?? ($notesMap['source_final_reason_code'] ?? null),
            'retry_exhausted' => property_exists($record, 'source_retry_exhausted') && $record->source_retry_exhausted !== null ? ($record->source_retry_exhausted ? 'yes' : 'no') : ($notesMap['source_retry_exhausted'] ?? null),
        ];
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


    private function normalizeSourceContextPaths(array $sourceContext)
    {
        if (array_key_exists('source_input_file', $sourceContext) && $sourceContext['source_input_file'] !== null && $sourceContext['source_input_file'] !== '') {
            $sourceContext['source_input_file'] = $this->normalizeOptionalPathForDisplay($sourceContext['source_input_file']);
        }

        return $sourceContext;
    }

    private function normalizeSourceAttemptTelemetryPaths($telemetry)
    {
        if (! is_array($telemetry)) {
            return $telemetry;
        }

        if (array_key_exists('source_input_file', $telemetry) && $telemetry['source_input_file'] !== null && $telemetry['source_input_file'] !== '') {
            $telemetry['source_input_file'] = $this->normalizeOptionalPathForDisplay($telemetry['source_input_file']);
        }

        return $telemetry;
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

    private function normalizeNullableInt($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function buildSourceSummaryString(array $sourceContext)
    {
        $summaryParts = [];

        if (($sourceContext['provider'] ?? '') !== '') {
            $summaryParts[] = 'provider='.(string) $sourceContext['provider'];
        }

        if (array_key_exists('timeout_seconds', $sourceContext) && $sourceContext['timeout_seconds'] !== null) {
            $summaryParts[] = 'timeout_seconds='.(string) $sourceContext['timeout_seconds'];
        }

        if (array_key_exists('retry_max', $sourceContext) && $sourceContext['retry_max'] !== null) {
            $summaryParts[] = 'retry_max='.(string) $sourceContext['retry_max'];
        }

        if (array_key_exists('attempt_count', $sourceContext) && $sourceContext['attempt_count'] !== null) {
            $summaryParts[] = 'attempt_count='.(string) $sourceContext['attempt_count'];
        }

        if (isset($sourceContext['success_after_retry']) && $sourceContext['success_after_retry'] !== '') {
            $summaryParts[] = 'success_after_retry='.(string) $sourceContext['success_after_retry'];
        }

        if (array_key_exists('final_http_status', $sourceContext) && $sourceContext['final_http_status'] !== null) {
            $summaryParts[] = 'final_http_status='.(string) $sourceContext['final_http_status'];
        }

        if (($sourceContext['final_reason_code'] ?? '') !== '') {
            $summaryParts[] = 'final_reason_code='.(string) $sourceContext['final_reason_code'];
        }

        return $summaryParts === [] ? null : implode(' | ', $summaryParts);
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

    private function buildReplayResult($metric)
    {
        return [
            'replay_id' => (int) $metric->replay_id,
            'trade_date' => $metric->trade_date,
            'trade_date_effective' => $metric->trade_date_effective,
            'source' => $metric->source,
            'status' => $metric->status,
            'comparison_result' => $metric->comparison_result,
            'comparison_note' => $metric->comparison_note,
            'artifact_changed_scope' => $metric->artifact_changed_scope,
            'config_identity' => $metric->config_identity,
            'publication_version' => $metric->publication_version !== null ? (int) $metric->publication_version : null,
            'coverage' => $this->buildCoverageState($metric),
            'expected_coverage' => $this->buildExpectedCoverageState($metric),
            'coverage_ratio' => $metric->coverage_ratio !== null ? (float) $metric->coverage_ratio : null,
            'bars_rows_written' => $metric->bars_rows_written !== null ? (int) $metric->bars_rows_written : null,
            'indicators_rows_written' => $metric->indicators_rows_written !== null ? (int) $metric->indicators_rows_written : null,
            'eligibility_rows_written' => $metric->eligibility_rows_written !== null ? (int) $metric->eligibility_rows_written : null,
            'eligible_count' => $metric->eligible_count !== null ? (int) $metric->eligible_count : null,
            'invalid_bar_count' => $metric->invalid_bar_count !== null ? (int) $metric->invalid_bar_count : null,
            'invalid_indicator_count' => $metric->invalid_indicator_count !== null ? (int) $metric->invalid_indicator_count : null,
            'warning_count' => $metric->warning_count !== null ? (int) $metric->warning_count : null,
            'hard_reject_count' => $metric->hard_reject_count !== null ? (int) $metric->hard_reject_count : null,
            'bars_batch_hash' => $metric->bars_batch_hash,
            'indicators_batch_hash' => $metric->indicators_batch_hash,
            'eligibility_batch_hash' => $metric->eligibility_batch_hash,
            'seal_state' => $metric->seal_state,
            'sealed_at' => $metric->sealed_at,
            'expected_status' => $metric->expected_status,
            'expected_trade_date_effective' => $metric->expected_trade_date_effective,
            'expected_seal_state' => $metric->expected_seal_state,
            'expected_config_identity' => $metric->expected_config_identity ?? null,
            'expected_publication_version' => $metric->expected_publication_version !== null ? (int) $metric->expected_publication_version : null,
            'expected_bars_batch_hash' => $metric->expected_bars_batch_hash ?? null,
            'expected_indicators_batch_hash' => $metric->expected_indicators_batch_hash ?? null,
            'expected_eligibility_batch_hash' => $metric->expected_eligibility_batch_hash ?? null,
            'mismatch_summary' => $metric->mismatch_summary,
            'created_at' => $metric->created_at,
        ];
    }

    private function buildReplayExpectedState($metric, array $expectedReasonCodeCounts)
    {
        return [
            'status' => $metric->expected_status,
            'trade_date_effective' => $metric->expected_trade_date_effective,
            'seal_state' => $metric->expected_seal_state,
            'config_identity' => $metric->expected_config_identity ?? null,
            'publication_version' => $metric->expected_publication_version !== null ? (int) $metric->expected_publication_version : null,
            'coverage' => $this->buildExpectedCoverageState($metric),
            'bars_batch_hash' => $metric->expected_bars_batch_hash ?? null,
            'indicators_batch_hash' => $metric->expected_indicators_batch_hash ?? null,
            'eligibility_batch_hash' => $metric->expected_eligibility_batch_hash ?? null,
            'reason_code_counts' => $expectedReasonCodeCounts,
        ];
    }

    private function buildReplayActualState($metric, array $reasonCodes)
    {
        return [
            'status' => $metric->status,
            'trade_date_effective' => $metric->trade_date_effective,
            'seal_state' => $metric->seal_state,
            'config_identity' => $metric->config_identity,
            'publication_version' => $metric->publication_version !== null ? (int) $metric->publication_version : null,
            'coverage' => $this->buildCoverageState($metric),
            'bars_batch_hash' => $metric->bars_batch_hash,
            'indicators_batch_hash' => $metric->indicators_batch_hash,
            'eligibility_batch_hash' => $metric->eligibility_batch_hash,
            'reason_code_counts' => $reasonCodes,
        ];
    }


    private function buildCoverageState($record)
    {
        return [
            'coverage_universe_count' => isset($record->coverage_universe_count) && $record->coverage_universe_count !== null ? (int) $record->coverage_universe_count : null,
            'coverage_available_count' => isset($record->coverage_available_count) && $record->coverage_available_count !== null ? (int) $record->coverage_available_count : null,
            'coverage_missing_count' => isset($record->coverage_missing_count) && $record->coverage_missing_count !== null ? (int) $record->coverage_missing_count : null,
            'coverage_ratio' => isset($record->coverage_ratio) && $record->coverage_ratio !== null ? (float) $record->coverage_ratio : null,
            'coverage_min_threshold' => isset($record->coverage_min_threshold) && $record->coverage_min_threshold !== null ? (float) $record->coverage_min_threshold : null,
            'coverage_gate_state' => $record->coverage_gate_state ?? null,
            'coverage_threshold_mode' => $record->coverage_threshold_mode ?? null,
            'coverage_universe_basis' => $record->coverage_universe_basis ?? null,
            'coverage_contract_version' => $record->coverage_contract_version ?? null,
            'coverage_missing_sample' => $this->decodeJsonArray($record->coverage_missing_sample_json ?? null),
        ];
    }

    private function buildExpectedCoverageState($record)
    {
        return [
            'coverage_universe_count' => isset($record->expected_coverage_universe_count) && $record->expected_coverage_universe_count !== null ? (int) $record->expected_coverage_universe_count : null,
            'coverage_available_count' => isset($record->expected_coverage_available_count) && $record->expected_coverage_available_count !== null ? (int) $record->expected_coverage_available_count : null,
            'coverage_missing_count' => isset($record->expected_coverage_missing_count) && $record->expected_coverage_missing_count !== null ? (int) $record->expected_coverage_missing_count : null,
            'coverage_ratio' => isset($record->expected_coverage_ratio) && $record->expected_coverage_ratio !== null ? (float) $record->expected_coverage_ratio : null,
            'coverage_min_threshold' => isset($record->expected_coverage_min_threshold) && $record->expected_coverage_min_threshold !== null ? (float) $record->expected_coverage_min_threshold : null,
            'coverage_gate_state' => $record->expected_coverage_gate_state ?? null,
            'coverage_threshold_mode' => $record->expected_coverage_threshold_mode ?? null,
            'coverage_universe_basis' => $record->expected_coverage_universe_basis ?? null,
            'coverage_contract_version' => $record->expected_coverage_contract_version ?? null,
            'coverage_missing_sample' => $this->decodeJsonArray($record->expected_coverage_missing_sample_json ?? null),
        ];
    }

    private function decodeJsonArray($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values($value);
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function decodeExpectedReasonCodeCounts($json)
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function buildAnomalyReport(array $runSummary, array $dominantReasonCodes, $manifest = null)
    {
        $dominant = count($dominantReasonCodes) ? $dominantReasonCodes[0]['reason_code'] : 'NONE';
        $lines = [
            '# Anomaly Report',
            '- Requested date: '.$runSummary['trade_date_requested'],
            '- Effective date: '.($runSummary['trade_date_effective'] ?: 'null'),
            '- Status: '.$runSummary['terminal_status'],
            '- Dominant anomaly: '.$dominant,
            '- Consumer effect: '.($runSummary['publishability_state'] === 'READABLE' ? 'requested date readable' : 'fallback or no readable state'),
            '- Publication safety: '.($manifest && $manifest['seal_state'] === 'SEALED' && $manifest['is_current'] ? 'current sealed publication present' : 'requested date not proven readable as current sealed publication'),
        ];

        return implode("\n", $lines)."\n";
    }

    private function buildCorrectionComparisonSummary($priorPublication, $newPublication)
    {
        if (! $priorPublication && ! $newPublication) {
            return 'No prior or new publication found.';
        }
        if ($priorPublication && $newPublication
            && (string) $priorPublication->bars_batch_hash === (string) $newPublication->bars_batch_hash
            && (string) $priorPublication->indicators_batch_hash === (string) $newPublication->indicators_batch_hash
            && (string) $priorPublication->eligibility_batch_hash === (string) $newPublication->eligibility_batch_hash) {
            return 'No consumer-visible hash change detected.';
        }

        return 'Publication hash set changed and requires audit comparison.';
    }

    private function resolvedTradeDate($run)
    {
        return $run->trade_date_effective ?: $run->trade_date_requested;
    }

    private function writeJson($path, array $payload)
    {
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function writeCsv($path, array $headers, array $rows)
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $header) {
                $line[] = array_key_exists($header, $row) ? $row[$header] : null;
            }
            fputcsv($handle, $line);
        }
        fclose($handle);
    }

    private function ensureDirectory($dir)
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    private function defaultRunOutputDir($runId)
    {
        return rtrim((string) config('market_data.evidence.output_directory', storage_path('app/market_data/evidence')), '/').'/runs/run_'.$runId;
    }

    private function defaultCorrectionOutputDir($correctionId)
    {
        return rtrim((string) config('market_data.evidence.output_directory', storage_path('app/market_data/evidence')), '/').'/corrections/correction_'.$correctionId;
    }

    private function defaultReplayOutputDir($replayId, $tradeDate)
    {
        return rtrim((string) config('market_data.evidence.output_directory', storage_path('app/market_data/evidence')), '/').'/replays/replay_'.$replayId.'_'.$tradeDate;
    }
}
