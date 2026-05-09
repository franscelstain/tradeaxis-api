<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;

class ReplayVerificationService
{
    private $evidence;
    private $publications;
    private $replays;

    private $deterministicFieldsChecked = [];

    private $ignoredVolatileFields = [
        'exported_at',
        'replay_started_at',
        'replay_completed_at',
        'duration_ms',
        'runtime_memory',
        'temporary_output_path',
        'created_at',
        'updated_at',
    ];

    public function __construct(
        EodEvidenceRepository $evidence,
        EodPublicationRepository $publications,
        ReplayResultRepository $replays
    ) {
        $this->evidence = $evidence;
        $this->publications = $publications;
        $this->replays = $replays;
    }

    public function verifyRunAgainstFixture($runId, $fixturePath, $replayId = null)
    {
        $fixture = $this->loadFixturePackage($fixturePath);
        $run = $this->evidence->findRunById($runId);
        if (! $run) {
            throw new \RuntimeException('REPLAY_ACTUAL_PROOF_INCOMPLETE: Run not found for replay verification.');
        }

        $publication = $this->resolvePublicationForRun($run);
        $correction = $this->findCorrectionForRun($run->run_id);
        $actual = $this->buildActualReplayState($run, $publication, $correction);
        $comparison = $this->compareExpectedAndActual($fixture, $actual);
        $replayId = $replayId ?: $this->replays->nextReplayId();

        $manifest = $fixture['manifest'];
        $metric = [
            'replay_id' => $replayId,
            'replay_suite' => $manifest['fixture_family'] ?? null,
            'replay_case' => $manifest['fixture_id'] ?? null,
            'fixture_id' => $manifest['fixture_id'] ?? null,
            'fixture_version' => $manifest['fixture_version'] ?? ($manifest['version'] ?? null),
            'fixture_schema_version' => $manifest['fixture_schema_version'] ?? null,
            'fixture_source' => $manifest['fixture_source'] ?? null,
            'fixture_created_at' => $manifest['fixture_created_at'] ?? null,
            'trade_date' => $actual['trade_date'],
            'trade_date_effective' => $actual['trade_date_effective'],
            'source' => $actual['source'],
            'source_mode' => $actual['source_mode'],
            'source_name' => $actual['source_name'],
            'source_provider' => $actual['source_provider'],
            'source_timeout_seconds' => $actual['source_timeout_seconds'],
            'source_retry_max' => $actual['source_retry_max'],
            'source_attempt_count' => $actual['source_attempt_count'],
            'source_success_after_retry' => $actual['source_success_after_retry'],
            'source_retry_exhausted' => $actual['source_retry_exhausted'],
            'source_final_http_status' => $actual['source_final_http_status'],
            'source_final_reason_code' => $actual['source_final_reason_code'],
            'source_input_file' => $actual['source_input_file'],
            'source_file_hash' => $actual['source_file_hash'],
            'source_file_hash_algorithm' => $actual['source_file_hash_algorithm'],
            'source_file_size_bytes' => $actual['source_file_size_bytes'],
            'source_file_row_count' => $actual['source_file_row_count'],
            'status' => $actual['status'],
            'publishability_state' => $actual['publishability_state'],
            'publication_id' => $actual['publication_id'],
            'current_publication_id' => $actual['current_publication_id'],
            'publication_run_id' => $actual['publication_run_id'],
            'comparison_result' => $comparison['comparison_result'],
            'comparison_note' => $comparison['comparison_note'],
            'artifact_changed_scope' => $comparison['artifact_changed_scope'],
            'config_identity' => $actual['config_identity'],
            'publication_version' => $actual['publication_version'],
            'is_current_publication' => $actual['is_current_publication'],
            'correction_id' => $actual['correction_id'],
            'correction_status' => $actual['correction_status'],
            'correction_outcome' => $actual['correction_outcome'],
            'correction_reseal_status' => $actual['correction_reseal_status'],
            'correction_publication_switch' => $actual['correction_publication_switch'],
            'baseline_publication_id' => $actual['baseline_publication_id'],
            'candidate_publication_id' => $actual['candidate_publication_id'],
            'expected_correction_id' => $comparison['expected_correction_id'],
            'expected_correction_status' => $comparison['expected_correction_status'],
            'expected_correction_outcome' => $comparison['expected_correction_outcome'],
            'expected_correction_reseal_status' => $comparison['expected_correction_reseal_status'],
            'expected_correction_publication_switch' => $comparison['expected_correction_publication_switch'],
            'expected_baseline_publication_id' => $comparison['expected_baseline_publication_id'],
            'expected_candidate_publication_id' => $comparison['expected_candidate_publication_id'],
            'coverage_universe_count' => $actual['coverage_universe_count'],
            'coverage_available_count' => $actual['coverage_available_count'],
            'coverage_missing_count' => $actual['coverage_missing_count'],
            'coverage_ratio' => $actual['coverage_ratio'],
            'coverage_min_threshold' => $actual['coverage_min_threshold'],
            'coverage_gate_state' => $actual['coverage_gate_state'],
            'coverage_threshold_mode' => $actual['coverage_threshold_mode'],
            'coverage_universe_basis' => $actual['coverage_universe_basis'],
            'coverage_contract_version' => $actual['coverage_contract_version'],
            'coverage_missing_sample_json' => json_encode($actual['coverage_missing_sample'], JSON_UNESCAPED_SLASHES),
            'bars_rows_written' => $actual['bars_rows_written'],
            'indicators_rows_written' => $actual['indicators_rows_written'],
            'eligibility_rows_written' => $actual['eligibility_rows_written'],
            'eligible_count' => $actual['eligible_count'],
            'invalid_bar_count' => $actual['invalid_bar_count'],
            'invalid_indicator_count' => $actual['invalid_indicator_count'],
            'warning_count' => $actual['warning_count'],
            'hard_reject_count' => $actual['hard_reject_count'],
            'bars_batch_hash' => $actual['bars_batch_hash'],
            'indicators_batch_hash' => $actual['indicators_batch_hash'],
            'eligibility_batch_hash' => $actual['eligibility_batch_hash'],
            'seal_state' => $actual['seal_state'],
            'sealed_at' => $actual['sealed_at'],
            'expected_status' => $comparison['expected_status'],
            'expected_terminal_status' => $comparison['expected_terminal_status'],
            'expected_publishability_state' => $comparison['expected_publishability_state'],
            'expected_trade_date_effective' => $comparison['expected_trade_date_effective'],
            'expected_seal_state' => $comparison['expected_seal_state'],
            'expected_source_mode' => $comparison['expected_source_mode'],
            'expected_source_name' => $comparison['expected_source_name'],
            'expected_source_provider' => $comparison['expected_source_provider'],
            'expected_source_timeout_seconds' => $comparison['expected_source_timeout_seconds'],
            'expected_source_retry_max' => $comparison['expected_source_retry_max'],
            'expected_source_attempt_count' => $comparison['expected_source_attempt_count'],
            'expected_source_success_after_retry' => $comparison['expected_source_success_after_retry'],
            'expected_source_retry_exhausted' => $comparison['expected_source_retry_exhausted'],
            'expected_source_final_http_status' => $comparison['expected_source_final_http_status'],
            'expected_source_final_reason_code' => $comparison['expected_source_final_reason_code'],
            'expected_source_input_file' => $comparison['expected_source_input_file'],
            'expected_source_file_hash' => $comparison['expected_source_file_hash'],
            'expected_source_file_hash_algorithm' => $comparison['expected_source_file_hash_algorithm'],
            'expected_source_file_size_bytes' => $comparison['expected_source_file_size_bytes'],
            'expected_source_file_row_count' => $comparison['expected_source_file_row_count'],
            'expected_config_identity' => $comparison['expected_config_identity'],
            'expected_publication_id' => $comparison['expected_publication_id'],
            'expected_publication_run_id' => $comparison['expected_publication_run_id'],
            'expected_publication_version' => $comparison['expected_publication_version'],
            'expected_is_current_publication' => $comparison['expected_is_current_publication'],
            'expected_coverage_universe_count' => $comparison['expected_coverage_universe_count'],
            'expected_coverage_available_count' => $comparison['expected_coverage_available_count'],
            'expected_coverage_missing_count' => $comparison['expected_coverage_missing_count'],
            'expected_coverage_ratio' => $comparison['expected_coverage_ratio'],
            'expected_coverage_min_threshold' => $comparison['expected_coverage_min_threshold'],
            'expected_coverage_gate_state' => $comparison['expected_coverage_gate_state'],
            'expected_coverage_reason_code' => $comparison['expected_coverage_reason_code'],
            'expected_coverage_threshold_mode' => $comparison['expected_coverage_threshold_mode'],
            'expected_coverage_universe_basis' => $comparison['expected_coverage_universe_basis'],
            'expected_coverage_contract_version' => $comparison['expected_coverage_contract_version'],
            'expected_coverage_missing_sample_json' => $comparison['expected_coverage_missing_sample_json'],
            'expected_bars_batch_hash' => $comparison['expected_bars_batch_hash'],
            'expected_indicators_batch_hash' => $comparison['expected_indicators_batch_hash'],
            'expected_eligibility_batch_hash' => $comparison['expected_eligibility_batch_hash'],
            'expected_reason_code_counts_json' => $comparison['expected_reason_code_counts_json'],
            'mismatch_summary' => $comparison['mismatch_summary'],
            'mismatch_count' => $comparison['mismatch_count'],
            'mismatch_reason_codes_json' => json_encode($comparison['mismatch_reason_codes'], JSON_UNESCAPED_SLASHES),
            'mismatches_json' => json_encode($comparison['mismatches'], JSON_UNESCAPED_SLASHES),
            'expected_context_json' => json_encode($comparison['expected_context'], JSON_UNESCAPED_SLASHES),
            'actual_context_json' => json_encode($actual['context'], JSON_UNESCAPED_SLASHES),
            'ignored_volatile_fields_json' => json_encode($this->ignoredVolatileFields, JSON_UNESCAPED_SLASHES),
            'deterministic_fields_checked_json' => json_encode($comparison['deterministic_fields_checked'], JSON_UNESCAPED_SLASHES),
            'final_reason_code' => $comparison['final_reason_code'],
        ];

        $this->replays->upsertMetric($metric);
        $this->replays->replaceReasonCodeCounts($replayId, $actual['trade_date'], $actual['reason_code_counts']);

        return $metric + [
            'reason_code_counts' => $actual['reason_code_counts'],
            'fixture_family' => $manifest['fixture_family'],
            'fixture_version' => $manifest['fixture_version'] ?? ($manifest['version'] ?? null),
            'fixture_schema_version' => $manifest['fixture_schema_version'] ?? null,
            'mismatches' => $comparison['mismatches'],
            'mismatch_reason_codes' => $comparison['mismatch_reason_codes'],
            'expected_context' => $comparison['expected_context'],
            'actual_context' => $actual['context'],
            'ignored_volatile_fields' => $this->ignoredVolatileFields,
            'deterministic_fields_checked' => $comparison['deterministic_fields_checked'],
        ];
    }

    public function generateFixtureFromRun($runId, $fixturePath, $caseName = 'valid_case')
    {
        $run = $this->evidence->findRunById($runId);
        if (! $run) {
            throw new \RuntimeException('REPLAY_ACTUAL_PROOF_INCOMPLETE: Run not found for replay fixture generation.');
        }

        $publication = $this->resolvePublicationForRun($run);
        $correction = $this->findCorrectionForRun($run->run_id);
        $actual = $this->buildActualReplayState($run, $publication, $correction);
        $fixturePath = rtrim((string) $fixturePath, '/\\');
        if ($fixturePath === '') {
            throw new \RuntimeException('COMMAND_MISSING_REQUIRED_INPUT: output_dir must be provided for replay fixture generation.');
        }

        $expectedDir = $fixturePath.'/expected';
        if (! is_dir($expectedDir) && ! mkdir($expectedDir, 0777, true) && ! is_dir($expectedDir)) {
            throw new \RuntimeException('COMMAND_EXECUTION_FAILED: Unable to create replay fixture directory: '.$expectedDir);
        }

        $manifest = [
            'fixture_id' => $caseName,
            'fixture_family' => 'runtime_generated_valid_case',
            'fixture_version' => 'generated-v1',
            'fixture_schema_version' => 'replay_fixture_v2',
            'fixture_created_at' => date(DATE_ATOM),
            'fixture_source' => 'generated_from_run_'.$runId,
            'version' => 'generated-v1',
            'contract_areas' => [
                'replay_verification',
                'replay_determinism',
                'evidence_export_completeness',
                'production_validation_runtime_proof',
            ],
            'files' => [
                'expected/expected_replay_result.json',
                'expected/expected_reason_code_counts.json',
            ],
            'assertion_layers' => [
                'run',
                'source',
                'coverage',
                'hash',
                'seal',
                'publication',
                'pointer',
                'fallback',
                'correction',
                'lineage',
                'replay',
            ],
        ];

        $expectedReplay = $this->buildExpectedReplayResultFromActual($actual, $runId);
        $reasonCodeCounts = $this->normalizeReasonCodeCounts($actual['reason_code_counts']);

        $this->writeJsonFile($fixturePath.'/manifest.json', $manifest);
        $this->writeJsonFile($expectedDir.'/expected_replay_result.json', $expectedReplay);
        $this->writeJsonFile($expectedDir.'/expected_reason_code_counts.json', $reasonCodeCounts);

        return [
            'run_id' => (int) $runId,
            'fixture_id' => $caseName,
            'fixture_family' => $manifest['fixture_family'],
            'fixture_path' => $fixturePath,
            'manifest_path' => $fixturePath.'/manifest.json',
            'expected_replay_result_path' => $expectedDir.'/expected_replay_result.json',
            'expected_reason_code_counts_path' => $expectedDir.'/expected_reason_code_counts.json',
            'trade_date' => $actual['trade_date'],
            'trade_date_effective' => $actual['trade_date_effective'],
            'expected_result' => 'MATCH',
            'source_mode' => $actual['source_mode'],
            'publication_id' => $actual['publication_id'],
            'publication_run_id' => $actual['publication_run_id'],
            'pointer_publication_id' => $actual['context']['actual_pointer_context']['pointer_publication_id'] ?? null,
            'pointer_run_id' => $actual['context']['actual_pointer_context']['pointer_run_id'] ?? null,
            'coverage_gate_state' => $actual['coverage_gate_state'],
            'coverage_ratio' => $actual['coverage_ratio'],
            'bars_batch_hash' => $actual['bars_batch_hash'],
            'indicators_batch_hash' => $actual['indicators_batch_hash'],
            'eligibility_batch_hash' => $actual['eligibility_batch_hash'],
        ];
    }

    private function buildExpectedReplayResultFromActual(array $actual, $runId)
    {
        $runContext = $actual['context']['actual_run_context'];
        $sourceContext = $actual['context']['actual_source_context'];
        $coverageContext = $actual['context']['actual_coverage_context'];
        $artifactContext = $actual['context']['actual_artifact_context'];
        $sealContext = $actual['context']['actual_seal_context'];
        $publicationContext = $actual['context']['actual_publication_context'];
        $pointerContext = $actual['context']['actual_pointer_context'];
        $fallbackContext = $actual['context']['actual_fallback_context'];
        $correctionContext = $actual['context']['actual_correction_context'];
        $finalState = $actual['context']['actual_final_state'];
        $lineage = $actual['context']['actual_lineage'];

        return [
            'comparison_result' => 'MATCH',
            'comparison_note' => 'Runtime-generated replay fixture expectation for run_id='.$runId.'.',
            'expected_config_identity' => $actual['config_identity'],
            'expected_run_context' => [
                'trade_date_requested' => $runContext['trade_date_requested'],
                'trade_date_effective' => $runContext['trade_date_effective'],
                'request_mode' => $runContext['request_mode'],
                'import_status' => $runContext['import_status'],
                'promote_status' => $runContext['promote_status'],
                'promoted' => $runContext['promoted'],
                'pointer_switched' => $runContext['pointer_switched'],
                'promote_mode' => $runContext['promote_mode'],
                'publish_target' => $runContext['publish_target'],
                'terminal_status' => $runContext['terminal_status'],
                'publishability_state' => $runContext['publishability_state'],
                'final_reason_code' => $runContext['final_reason_code'],
            ],
            'expected_source_context' => $sourceContext,
            'expected_coverage_context' => $coverageContext,
            'expected_artifact_context' => $artifactContext,
            'expected_seal_context' => [
                'seal_state' => $sealContext['seal_state'],
            ],
            'expected_publication_context' => [
                'publication_id' => $publicationContext['publication_id'],
                'publication_run_id' => $publicationContext['publication_run_id'],
                'publication_version' => $publicationContext['publication_version'],
                'publication_terminal_status' => $publicationContext['publication_terminal_status'],
                'publication_publishability_state' => $publicationContext['publication_publishability_state'],
                'publication_is_current' => $publicationContext['publication_is_current'],
                'publication_seal_state' => $publicationContext['publication_seal_state'],
            ],
            'expected_pointer_context' => $pointerContext,
            'expected_fallback_context' => $fallbackContext,
            'expected_correction_context' => $correctionContext,
            'expected_final_state' => $finalState,
            'expected_reason_code' => $finalState['final_reason_code'],
            'expected_lineage' => $lineage,
        ];
    }

    private function writeJsonFile($path, array $payload)
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('COMMAND_EXECUTION_FAILED: Unable to encode replay fixture JSON.');
        }
        file_put_contents($path, $json."\n");
    }

    private function loadFixturePackage($fixturePath)
    {
        $manifestPath = rtrim($fixturePath, '/').'/manifest.json';
        if (! is_file($manifestPath)) {
            throw new \RuntimeException('REPLAY_FIXTURE_SCHEMA_MISMATCH: Replay fixture manifest not found: '.$manifestPath);
        }

        $manifest = $this->readJsonFile($manifestPath, 'REPLAY_FIXTURE_SCHEMA_MISMATCH');
        if (isset($manifest['fixture_version']) && ! isset($manifest['version'])) {
            $manifest['version'] = $manifest['fixture_version'];
        }

        foreach (['fixture_id', 'fixture_family', 'fixture_version', 'fixture_schema_version', 'fixture_created_at', 'fixture_source', 'version', 'contract_areas', 'files', 'assertion_layers'] as $field) {
            if (! array_key_exists($field, $manifest)) {
                throw new \RuntimeException('REPLAY_FIXTURE_SCHEMA_MISMATCH: Replay fixture manifest missing required field: '.$field);
            }
        }

        if (! is_array($manifest['assertion_layers']) || ! in_array('replay', $manifest['assertion_layers'], true)) {
            throw new \RuntimeException('REPLAY_FIXTURE_SCHEMA_MISMATCH: Replay fixture manifest must include assertion layer: replay');
        }

        if (! in_array('expected/expected_replay_result.json', (array) $manifest['files'], true)) {
            throw new \RuntimeException('REPLAY_EXPECTED_PROOF_INCOMPLETE: Replay fixture manifest must declare expected/expected_replay_result.json');
        }

        foreach ((array) $manifest['files'] as $relativePath) {
            $resolvedPath = rtrim($fixturePath, '/').'/'.$relativePath;
            if (! is_file($resolvedPath)) {
                throw new \RuntimeException('REPLAY_EXPECTED_PROOF_INCOMPLETE: Replay fixture file missing: '.$relativePath);
            }
        }

        $expectedReplay = $this->readJsonFile(rtrim($fixturePath, '/').'/expected/expected_replay_result.json', 'REPLAY_EXPECTED_PROOF_INCOMPLETE');
        $expectedRun = $this->optionalJsonFile(rtrim($fixturePath, '/').'/expected/expected_run_summary.json', 'REPLAY_EXPECTED_PROOF_INCOMPLETE');
        $expectedHashes = $this->optionalJsonFile(rtrim($fixturePath, '/').'/expected/expected_hashes.json', 'REPLAY_EXPECTED_PROOF_INCOMPLETE');
        $expectedReasonCodeCounts = $this->optionalJsonFile(rtrim($fixturePath, '/').'/expected/expected_reason_code_counts.json', 'REPLAY_EXPECTED_PROOF_INCOMPLETE');

        return [
            'manifest' => $manifest,
            'expected_replay_result' => $expectedReplay,
            'expected_run_summary' => $expectedRun,
            'expected_hashes' => $expectedHashes,
            'expected_reason_code_counts' => $expectedReasonCodeCounts,
            'expected_reason_code_counts_present' => $expectedReasonCodeCounts !== null,
            'expected_proof_missing' => $this->validateExpectedProofCompleteness($expectedReplay, $expectedReasonCodeCounts !== null),
        ];
    }

    private function validateExpectedProofCompleteness(array $expectedReplay, $hasReasonCountsFile)
    {
        $required = [
            'expected_run_context',
            'expected_run_context.trade_date_requested',
            'expected_run_context.trade_date_effective',
            'expected_run_context.terminal_status',
            'expected_run_context.publishability_state',
            'expected_run_context.final_reason_code',
            'expected_source_context',
            'expected_source_context.source_mode',
            'expected_source_context.source_identity',
            'expected_coverage_context',
            'expected_coverage_context.coverage_universe_count',
            'expected_coverage_context.coverage_available_count',
            'expected_coverage_context.coverage_missing_count',
            'expected_coverage_context.coverage_ratio',
            'expected_coverage_context.coverage_min_threshold',
            'expected_coverage_context.coverage_gate_state',
            'expected_coverage_context.coverage_reason_code',
            'expected_artifact_context',
            'expected_artifact_context.bars_batch_hash',
            'expected_artifact_context.indicators_batch_hash',
            'expected_artifact_context.eligibility_batch_hash',
            'expected_seal_context',
            'expected_seal_context.seal_state',
            'expected_publication_context',
            'expected_publication_context.publication_id',
            'expected_publication_context.publication_version',
            'expected_publication_context.publication_terminal_status',
            'expected_publication_context.publication_publishability_state',
            'expected_pointer_context',
            'expected_pointer_context.pointer_publication_id',
            'expected_pointer_context.pointer_resolve_status',
            'expected_fallback_context',
            'expected_fallback_context.fallback_used',
            'expected_correction_context',
            'expected_final_state',
            'expected_final_state.terminal_status',
            'expected_final_state.publishability_state',
            'expected_final_state.final_reason_code',
            'expected_reason_code',
            'expected_lineage',
        ];

        $missing = [];
        foreach ($required as $path) {
            if (! $this->hasPath($expectedReplay, $path)) {
                $missing[] = $path;
            }
        }

        if (! $hasReasonCountsFile) {
            $missing[] = 'expected/expected_reason_code_counts.json';
        }

        return $missing;
    }

    private function buildActualReplayState($run, $publication = null, $correction = null)
    {
        $notes = $this->parseNotes((string) ($run->notes ?? ''));
        $resolvedTradeDate = $run->trade_date_effective ?: $run->trade_date_requested;
        $reasonCodeCounts = $this->actualReasonCodeCounts($run, $resolvedTradeDate, $publication);
        $eligibleCount = 0;
        if ($publication) {
            foreach ($this->evidence->exportEligibilityRows($resolvedTradeDate, $publication->publication_id) as $row) {
                if ((int) ($row['eligible'] ?? 0) === 1) {
                    $eligibleCount++;
                }
            }
        }

        $coverageReasonCode = $this->resolveCoverageReasonCodeFromState($run->coverage_gate_state ?? null);
        $finalReasonCode = $run->final_reason_code ?? ($run->source_final_reason_code ?? $coverageReasonCode);
        $sourceIdentity = $this->buildSourceIdentity($run);
        $publicationId = $publication && isset($publication->publication_id) && $publication->publication_id !== null ? (int) $publication->publication_id : (isset($run->publication_id) && $run->publication_id !== null ? (int) $run->publication_id : null);
        $publicationRunId = $publication && isset($publication->run_id) && $publication->run_id !== null ? (int) $publication->run_id : (isset($run->publication_run_id) && $run->publication_run_id !== null ? (int) $run->publication_run_id : null);
        $publicationVersion = $publication && isset($publication->publication_version) && $publication->publication_version !== null ? (int) $publication->publication_version : (isset($run->publication_version) && $run->publication_version !== null ? (int) $run->publication_version : null);
        $isCurrentPublication = $publication && isset($publication->is_current) ? (bool) $publication->is_current : (isset($run->is_current_publication) ? (bool) $run->is_current_publication : false);
        $sealState = $publication && isset($publication->seal_state) && $publication->seal_state ? $publication->seal_state : ($run->sealed_at ? 'SEALED' : 'UNSEALED');
        $requestMode = $run->request_mode ?? ($notes['request_mode'] ?? null);
        $promoted = (string) ($run->terminal_status ?? '') === 'SUCCESS'
            && (string) ($run->publishability_state ?? '') === 'READABLE'
            && $isCurrentPublication;
        $importStatus = (string) $requestMode === 'import_only'
            ? (isset($run->bars_rows_written) && $run->bars_rows_written !== null ? 'COMPLETED' : 'PENDING')
            : (isset($run->bars_rows_written) && $run->bars_rows_written !== null ? 'COMPLETED' : 'NOT_APPLICABLE');
        $promoteStatus = $promoted
            ? 'PROMOTED'
            : ((string) $requestMode === 'import_only'
                ? 'NOT_PROMOTED'
                : (in_array((string) ($run->terminal_status ?? ''), ['HELD', 'FAILED', 'BLOCKED'], true) ? (string) $run->terminal_status : 'NOT_PROMOTED'));
        $sourceMode = $run->source ?? ($notes['source_mode'] ?? null);
        $sourceName = $run->source_name ?? ($notes['source_name'] ?? null);
        $sourceProvider = $run->source_provider ?? ($notes['source_provider'] ?? null);

        $runContext = [
            'run_id' => isset($run->run_id) ? (int) $run->run_id : null,
            'trade_date_requested' => $run->trade_date_requested ?? null,
            'trade_date_effective' => $resolvedTradeDate,
            'request_mode' => $requestMode,
            'import_status' => $importStatus,
            'promote_status' => $promoteStatus,
            'promoted' => $promoted,
            'pointer_switched' => $isCurrentPublication,
            'promote_mode' => $run->promote_mode ?? ($notes['promote_mode'] ?? null),
            'publish_target' => $run->publish_target ?? ($notes['publish_target'] ?? null),
            'terminal_status' => $run->terminal_status ?? null,
            'publishability_state' => $run->publishability_state ?? null,
            'final_reason_code' => $finalReasonCode,
        ];
        $sourceContext = [
            'source_mode' => $sourceMode,
            'source_name' => $sourceName,
            'source_identity' => $sourceIdentity,
            'source_provider' => $sourceProvider,
            'provider' => $sourceProvider,
            'source_timeout_seconds' => isset($run->source_timeout_seconds) && $run->source_timeout_seconds !== null ? (int) $run->source_timeout_seconds : null,
            'source_retry_max' => isset($run->source_retry_max) && $run->source_retry_max !== null ? (int) $run->source_retry_max : null,
            'source_attempt_count' => isset($run->source_attempt_count) && $run->source_attempt_count !== null ? (int) $run->source_attempt_count : null,
            'source_success_after_retry' => isset($run->source_success_after_retry) && $run->source_success_after_retry !== null ? (bool) $run->source_success_after_retry : null,
            'source_retry_exhausted' => isset($run->source_retry_exhausted) && $run->source_retry_exhausted !== null ? (bool) $run->source_retry_exhausted : null,
            'source_final_http_status' => isset($run->source_final_http_status) && $run->source_final_http_status !== null ? (int) $run->source_final_http_status : null,
            'source_final_reason_code' => $run->source_final_reason_code ?? null,
            'source_input_file' => $run->source_input_file ?? null,
            'source_file_hash' => $run->source_file_hash ?? null,
            'source_file_hash_algorithm' => $run->source_file_hash_algorithm ?? null,
            'source_file_size_bytes' => isset($run->source_file_size_bytes) && $run->source_file_size_bytes !== null ? (int) $run->source_file_size_bytes : null,
            'source_file_row_count' => isset($run->source_file_row_count) && $run->source_file_row_count !== null ? (int) $run->source_file_row_count : null,
            'accepted_row_count' => isset($run->bars_rows_written) && $run->bars_rows_written !== null ? (int) $run->bars_rows_written : null,
            'rejected_row_count' => isset($run->invalid_bar_count) && $run->invalid_bar_count !== null ? (int) $run->invalid_bar_count : null,
            'invalid_row_count' => isset($run->invalid_bar_count) && $run->invalid_bar_count !== null ? (int) $run->invalid_bar_count : null,
        ];
        $coverageContext = [
            'coverage_universe_count' => isset($run->coverage_universe_count) && $run->coverage_universe_count !== null ? (int) $run->coverage_universe_count : null,
            'coverage_expected_count' => isset($run->coverage_universe_count) && $run->coverage_universe_count !== null ? (int) $run->coverage_universe_count : null,
            'coverage_available_count' => isset($run->coverage_available_count) && $run->coverage_available_count !== null ? (int) $run->coverage_available_count : null,
            'coverage_missing_count' => isset($run->coverage_missing_count) && $run->coverage_missing_count !== null ? (int) $run->coverage_missing_count : null,
            'coverage_ratio' => isset($run->coverage_ratio) && $run->coverage_ratio !== null ? (float) $run->coverage_ratio : null,
            'coverage_min_threshold' => isset($run->coverage_min_threshold) && $run->coverage_min_threshold !== null ? (float) $run->coverage_min_threshold : null,
            'coverage_gate_state' => $run->coverage_gate_state ?? null,
            'coverage_reason_code' => $coverageReasonCode,
            'coverage_threshold_mode' => $run->coverage_threshold_mode ?? null,
            'coverage_universe_basis' => $run->coverage_universe_basis ?? null,
            'coverage_contract_version' => $run->coverage_contract_version ?? null,
            'coverage_missing_sample' => $this->decodeJsonArray($run->coverage_missing_sample_json ?? null),
        ];
        $artifactContext = [
            'bars_rows_written' => isset($run->bars_rows_written) && $run->bars_rows_written !== null ? (int) $run->bars_rows_written : null,
            'indicators_rows_written' => isset($run->indicators_rows_written) && $run->indicators_rows_written !== null ? (int) $run->indicators_rows_written : null,
            'eligibility_rows_written' => isset($run->eligibility_rows_written) && $run->eligibility_rows_written !== null ? (int) $run->eligibility_rows_written : null,
            'eligible_count' => $eligibleCount,
            'invalid_bar_count' => isset($run->invalid_bar_count) && $run->invalid_bar_count !== null ? (int) $run->invalid_bar_count : null,
            'invalid_indicator_count' => isset($run->invalid_indicator_count) && $run->invalid_indicator_count !== null ? (int) $run->invalid_indicator_count : null,
            'warning_count' => isset($run->warning_count) && $run->warning_count !== null ? (int) $run->warning_count : null,
            'hard_reject_count' => isset($run->hard_reject_count) && $run->hard_reject_count !== null ? (int) $run->hard_reject_count : null,
            'bars_batch_hash' => $run->bars_batch_hash ?? null,
            'indicators_batch_hash' => $run->indicators_batch_hash ?? null,
            'eligibility_batch_hash' => $run->eligibility_batch_hash ?? null,
        ];
        $sealContext = [
            'seal_state' => $sealState,
            'sealed_at' => $publication && isset($publication->sealed_at) && $publication->sealed_at ? $publication->sealed_at : ($run->sealed_at ?? null),
        ];
        $publicationContext = [
            'publication_id' => $publicationId,
            'current_publication_id' => $isCurrentPublication ? $publicationId : null,
            'publication_run_id' => $publicationRunId,
            'publication_version' => $publicationVersion,
            'publication_terminal_status' => $run->terminal_status ?? null,
            'publication_publishability_state' => $run->publishability_state ?? null,
            'publication_is_current' => $isCurrentPublication,
            'publication_seal_state' => $sealState,
        ];
        $pointerContext = [
            'pointer_publication_id' => $publicationId,
            'pointer_run_id' => $publicationRunId,
            'pointer_publication_version' => $publicationVersion,
            'pointer_resolve_status' => ((string) ($run->terminal_status ?? '') === 'SUCCESS' && (string) ($run->publishability_state ?? '') === 'READABLE' && $isCurrentPublication) ? 'RESOLVED_READABLE_CURRENT' : 'NOT_RESOLVED_READABLE_CURRENT',
            'pointer_switched' => $isCurrentPublication,
        ];
        $fallbackContext = [
            'fallback_used' => $this->normalizeBoolean($run->fallback_used ?? ($notes['fallback_used'] ?? false)),
            'fallback_publication_id' => isset($run->fallback_publication_id) && $run->fallback_publication_id !== null ? (int) $run->fallback_publication_id : (isset($notes['fallback_publication_id']) && $notes['fallback_publication_id'] !== '' ? (int) $notes['fallback_publication_id'] : null),
            'fallback_run_id' => isset($run->fallback_run_id) && $run->fallback_run_id !== null ? (int) $run->fallback_run_id : (isset($notes['fallback_run_id']) && $notes['fallback_run_id'] !== '' ? (int) $notes['fallback_run_id'] : null),
        ];
        $correctionContext = [
            'correction_id' => $correction && isset($correction->correction_id) ? (int) $correction->correction_id : (isset($run->correction_id) && $run->correction_id !== null ? (int) $run->correction_id : null),
            'correction_status' => $correction && isset($correction->status) ? $correction->status : null,
            'correction_outcome' => $this->resolveCorrectionOutcome($correction),
            'correction_reseal_status' => $this->resolveCorrectionResealStatus($correction),
            'correction_publication_switch' => $correction && isset($correction->new_publication_is_current) && $correction->new_publication_is_current !== null ? (bool) $correction->new_publication_is_current : null,
            'baseline_publication_id' => $correction && isset($correction->baseline_publication_id) && $correction->baseline_publication_id !== null ? (int) $correction->baseline_publication_id : ($correction && isset($correction->prior_publication_id) && $correction->prior_publication_id !== null ? (int) $correction->prior_publication_id : null),
            'candidate_publication_id' => $correction && isset($correction->replacement_publication_id) && $correction->replacement_publication_id !== null ? (int) $correction->replacement_publication_id : ($correction && isset($correction->new_publication_id) && $correction->new_publication_id !== null ? (int) $correction->new_publication_id : null),
        ];
        $lineage = [
            'run_id' => $runContext['run_id'],
            'publication_id' => $publicationId,
            'current_publication_id' => $isCurrentPublication ? $publicationId : null,
            'publication_run_id' => $publicationRunId,
            'correction_id' => $correctionContext['correction_id'],
            'source_file_hash' => $sourceContext['source_file_hash'],
            'bars_batch_hash' => $artifactContext['bars_batch_hash'],
            'indicators_batch_hash' => $artifactContext['indicators_batch_hash'],
            'eligibility_batch_hash' => $artifactContext['eligibility_batch_hash'],
            'final_reason_code' => $finalReasonCode,
        ];

        $actual = [
            'trade_date' => $run->trade_date_requested,
            'trade_date_effective' => $resolvedTradeDate,
            'source' => $sourceMode,
            'source_mode' => $sourceMode,
            'source_name' => $sourceName,
            'source_provider' => $sourceProvider,
            'source_timeout_seconds' => $sourceContext['source_timeout_seconds'],
            'source_retry_max' => $sourceContext['source_retry_max'],
            'source_attempt_count' => $sourceContext['source_attempt_count'],
            'source_success_after_retry' => $sourceContext['source_success_after_retry'],
            'source_retry_exhausted' => $sourceContext['source_retry_exhausted'],
            'source_final_http_status' => $sourceContext['source_final_http_status'],
            'source_final_reason_code' => $sourceContext['source_final_reason_code'],
            'source_input_file' => $sourceContext['source_input_file'],
            'source_file_hash' => $sourceContext['source_file_hash'],
            'source_file_hash_algorithm' => $sourceContext['source_file_hash_algorithm'],
            'source_file_size_bytes' => $sourceContext['source_file_size_bytes'],
            'source_file_row_count' => $sourceContext['source_file_row_count'],
            'accepted_row_count' => $sourceContext['accepted_row_count'],
            'rejected_row_count' => $sourceContext['rejected_row_count'],
            'invalid_row_count' => $sourceContext['invalid_row_count'],
            'status' => $runContext['terminal_status'],
            'terminal_status' => $runContext['terminal_status'],
            'publishability_state' => $runContext['publishability_state'],
            'request_mode' => $requestMode,
            'import_status' => $importStatus,
            'promote_status' => $promoteStatus,
            'promoted' => $promoted,
            'pointer_switched' => $isCurrentPublication,
            'config_identity' => $run->config_version ?? null,
            'publication_id' => $publicationId,
            'current_publication_id' => $isCurrentPublication ? $publicationId : null,
            'publication_run_id' => $publicationRunId,
            'publication_version' => $publicationVersion,
            'is_current_publication' => $isCurrentPublication,
            'correction_id' => $correctionContext['correction_id'],
            'correction_status' => $correctionContext['correction_status'],
            'correction_outcome' => $correctionContext['correction_outcome'],
            'correction_reseal_status' => $correctionContext['correction_reseal_status'],
            'correction_publication_switch' => $correctionContext['correction_publication_switch'],
            'baseline_publication_id' => $correctionContext['baseline_publication_id'],
            'candidate_publication_id' => $correctionContext['candidate_publication_id'],
            'coverage_universe_count' => $coverageContext['coverage_universe_count'],
            'coverage_available_count' => $coverageContext['coverage_available_count'],
            'coverage_missing_count' => $coverageContext['coverage_missing_count'],
            'coverage_ratio' => $coverageContext['coverage_ratio'],
            'coverage_min_threshold' => $coverageContext['coverage_min_threshold'],
            'coverage_gate_state' => $coverageContext['coverage_gate_state'],
            'coverage_threshold_mode' => $coverageContext['coverage_threshold_mode'],
            'coverage_universe_basis' => $coverageContext['coverage_universe_basis'],
            'coverage_contract_version' => $coverageContext['coverage_contract_version'],
            'coverage_missing_sample' => $coverageContext['coverage_missing_sample'],
            'coverage_reason_code' => $coverageContext['coverage_reason_code'],
            'bars_rows_written' => $artifactContext['bars_rows_written'],
            'indicators_rows_written' => $artifactContext['indicators_rows_written'],
            'eligibility_rows_written' => $artifactContext['eligibility_rows_written'],
            'eligible_count' => $artifactContext['eligible_count'],
            'invalid_bar_count' => $artifactContext['invalid_bar_count'],
            'invalid_indicator_count' => $artifactContext['invalid_indicator_count'],
            'warning_count' => $artifactContext['warning_count'],
            'hard_reject_count' => $artifactContext['hard_reject_count'],
            'bars_batch_hash' => $artifactContext['bars_batch_hash'],
            'indicators_batch_hash' => $artifactContext['indicators_batch_hash'],
            'eligibility_batch_hash' => $artifactContext['eligibility_batch_hash'],
            'seal_state' => $sealContext['seal_state'],
            'sealed_at' => $sealContext['sealed_at'],
            'reason_code_counts' => $reasonCodeCounts,
        ];
        $actual['context'] = [
            'actual_run_context' => $runContext,
            'actual_import_promote_context' => [
                'request_mode' => $requestMode,
                'source_mode' => $sourceMode,
                'import_status' => $importStatus,
                'promote_status' => $promoteStatus,
                'promoted' => $promoted,
                'pointer_switched' => $isCurrentPublication,
                'current_publication_id' => $isCurrentPublication ? $publicationId : null,
            ],
            'actual_source_context' => $sourceContext,
            'actual_coverage_context' => $coverageContext,
            'actual_artifact_context' => $artifactContext,
            'actual_seal_context' => $sealContext,
            'actual_publication_context' => $publicationContext,
            'actual_pointer_context' => $pointerContext,
            'actual_fallback_context' => $fallbackContext,
            'actual_correction_context' => $correctionContext,
            'actual_final_state' => [
                'terminal_status' => $runContext['terminal_status'],
                'publishability_state' => $runContext['publishability_state'],
                'final_reason_code' => $finalReasonCode,
            ],
            'actual_lineage' => $lineage,
        ];

        return $actual;
    }

    private function actualReasonCodeCounts($run, $resolvedTradeDate, $publication)
    {
        $reasonCodeCounts = [];
        if ($publication) {
            foreach ($this->evidence->dominantReasonCodes($run->run_id, $resolvedTradeDate, $publication->publication_id) as $row) {
                $reasonCodeCounts[] = [
                    'reason_code' => $row['reason_code'],
                    'reason_count' => (int) $row['count'],
                ];
            }
            return $reasonCodeCounts;
        }

        try {
            $eventSummary = $this->evidence->summarizeRunEvents($run->run_id);
            foreach (($eventSummary['reason_code_counts'] ?? []) as $reasonCode => $count) {
                $reasonCodeCounts[] = [
                    'reason_code' => (string) $reasonCode,
                    'reason_count' => (int) $count,
                ];
            }
        } catch (\Throwable $e) {
            if (($run->final_reason_code ?? null) !== null) {
                $reasonCodeCounts[] = ['reason_code' => $run->final_reason_code, 'reason_count' => 1];
            }
        }

        return $this->normalizeReasonCodeCounts($reasonCodeCounts);
    }

    private function compareExpectedAndActual(array $fixture, array $actual)
    {
        $this->deterministicFieldsChecked = [];
        $expectedReplay = $fixture['expected_replay_result'];
        $expectedRun = $fixture['expected_run_summary'] ?: [];
        $expectedHashes = $fixture['expected_hashes'] ?: [];
        $expectedReasonCodeCounts = $fixture['expected_reason_code_counts'];
        $expectedClass = $expectedReplay['comparison_result'] ?? 'MATCH';
        $expectedContext = $this->buildExpectedContext($fixture);
        $mismatches = [];

        foreach ($fixture['expected_proof_missing'] as $missingPath) {
            $this->appendMismatch($mismatches, 'expected_proof.'.$missingPath, 'present', 'missing', 'REPLAY_EXPECTED_PROOF_INCOMPLETE');
        }

        $this->compareField($mismatches, 'trade_date_requested', $this->ctx($expectedContext, 'expected_run_context.trade_date_requested'), $actual['trade_date']);
        $this->compareField($mismatches, 'trade_date_effective', $this->ctx($expectedContext, 'expected_run_context.trade_date_effective'), $actual['trade_date_effective']);
        $this->compareField($mismatches, 'request_mode', $this->ctx($expectedContext, 'expected_run_context.request_mode'), $actual['context']['actual_run_context']['request_mode']);
        $this->compareField($mismatches, 'import_status', $this->ctx($expectedContext, 'expected_run_context.import_status'), $actual['context']['actual_import_promote_context']['import_status']);
        $this->compareField($mismatches, 'promote_status', $this->ctx($expectedContext, 'expected_run_context.promote_status'), $actual['context']['actual_import_promote_context']['promote_status']);
        $this->compareField($mismatches, 'promoted', $this->ctx($expectedContext, 'expected_run_context.promoted'), $actual['context']['actual_import_promote_context']['promoted']);
        $this->compareField($mismatches, 'pointer_switched', $this->ctx($expectedContext, 'expected_run_context.pointer_switched'), $actual['context']['actual_import_promote_context']['pointer_switched']);
        $this->compareField($mismatches, 'promote_mode', $this->ctx($expectedContext, 'expected_run_context.promote_mode'), $actual['context']['actual_run_context']['promote_mode']);
        $this->compareField($mismatches, 'publish_target', $this->ctx($expectedContext, 'expected_run_context.publish_target'), $actual['context']['actual_run_context']['publish_target']);
        $this->compareField($mismatches, 'terminal_status', $this->ctx($expectedContext, 'expected_final_state.terminal_status'), $actual['terminal_status']);
        $this->compareField($mismatches, 'status', $this->ctx($expectedContext, 'expected_final_state.terminal_status'), $actual['status']);
        $this->compareField($mismatches, 'publishability_state', $this->ctx($expectedContext, 'expected_final_state.publishability_state'), $actual['publishability_state']);
        $this->compareField($mismatches, 'final_reason_code', $this->ctx($expectedContext, 'expected_final_state.final_reason_code'), $actual['context']['actual_final_state']['final_reason_code']);
        $this->compareField($mismatches, 'expected_reason_code', $this->ctx($expectedContext, 'expected_reason_code'), $actual['context']['actual_final_state']['final_reason_code']);
        $this->compareField($mismatches, 'seal_state', $this->ctx($expectedContext, 'expected_seal_context.seal_state'), $actual['seal_state']);
        $this->compareField($mismatches, 'config_identity', $this->valueFromContexts([$expectedReplay], ['config_identity', 'expected_config_identity']), $actual['config_identity']);

        foreach (['source_mode', 'source_name', 'source_identity', 'source_provider', 'provider', 'source_final_reason_code', 'source_input_file', 'source_file_hash', 'source_file_hash_algorithm'] as $field) {
            $actualField = $field === 'provider' ? 'source_provider' : $field;
            $this->compareFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_source_context.'.$field), $actual['context']['actual_source_context'][$actualField] ?? null);
        }
        foreach (['source_timeout_seconds', 'source_retry_max', 'source_attempt_count', 'source_final_http_status', 'source_file_size_bytes', 'source_file_row_count', 'accepted_row_count', 'rejected_row_count', 'invalid_row_count'] as $field) {
            $this->compareNumericFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_source_context.'.$field), $actual['context']['actual_source_context'][$field] ?? null);
        }
        foreach (['source_success_after_retry', 'source_retry_exhausted'] as $field) {
            $expectedBool = $this->normalizeBooleanForComparison($this->ctx($expectedContext, 'expected_source_context.'.$field));
            $actualBool = $this->normalizeBooleanForComparison($actual['context']['actual_source_context'][$field] ?? null);
            $this->compareFieldAllowNull($mismatches, $field, $expectedBool, $actualBool);
        }
        $this->appendManualFilePolicyMismatches($mismatches, $expectedContext['expected_source_context'], $actual);

        foreach (['coverage_universe_count', 'coverage_expected_count', 'coverage_available_count', 'coverage_missing_count', 'coverage_gate_state', 'coverage_reason_code', 'coverage_threshold_mode', 'coverage_universe_basis', 'coverage_contract_version'] as $field) {
            $this->compareFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_coverage_context.'.$field), $actual['context']['actual_coverage_context'][$field] ?? null);
        }
        foreach (['coverage_ratio', 'coverage_min_threshold'] as $field) {
            $this->compareNumericFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_coverage_context.'.$field), $actual['context']['actual_coverage_context'][$field] ?? null);
        }
        $this->compareListField($mismatches, 'coverage_missing_sample', $this->ctx($expectedContext, 'expected_coverage_context.coverage_missing_sample'), $actual['context']['actual_coverage_context']['coverage_missing_sample']);

        foreach (['bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written', 'invalid_bar_count', 'invalid_indicator_count', 'warning_count', 'hard_reject_count', 'eligible_count'] as $field) {
            $expectedValue = $this->ctx($expectedContext, 'expected_artifact_context.'.$field);
            if ($expectedValue === null && array_key_exists($field, $expectedRun)) {
                $expectedValue = $expectedRun[$field];
            }
            $this->compareFieldAllowNull($mismatches, $field, $expectedValue, $actual[$field]);
        }
        foreach (['accepted_row_count', 'rejected_row_count', 'invalid_row_count'] as $field) {
            $this->compareFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_source_context.'.$field), $actual[$field]);
        }
        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $field) {
            $expectedValue = $this->ctx($expectedContext, 'expected_artifact_context.'.$field);
            if ($expectedValue === null && array_key_exists($field, $expectedHashes)) {
                $expectedValue = $expectedHashes[$field];
            }
            $this->compareFieldAllowNull($mismatches, $field, $expectedValue, $actual[$field]);
        }

        foreach (['publication_id', 'publication_run_id', 'publication_version', 'publication_terminal_status', 'publication_publishability_state', 'publication_is_current', 'publication_seal_state'] as $field) {
            $this->compareFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_publication_context.'.$field), $actual['context']['actual_publication_context'][$field] ?? null);
        }
        foreach (['pointer_publication_id', 'pointer_run_id', 'pointer_publication_version', 'pointer_resolve_status', 'pointer_switched'] as $field) {
            $this->compareFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_pointer_context.'.$field), $actual['context']['actual_pointer_context'][$field] ?? null);
        }
        foreach (['fallback_used', 'fallback_publication_id', 'fallback_run_id'] as $field) {
            $this->compareFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_fallback_context.'.$field), $actual['context']['actual_fallback_context'][$field] ?? null);
        }
        foreach (['correction_id', 'correction_status', 'correction_outcome', 'correction_reseal_status', 'correction_publication_switch', 'baseline_publication_id', 'candidate_publication_id'] as $field) {
            $this->compareFieldAllowNull($mismatches, $field, $this->ctx($expectedContext, 'expected_correction_context.'.$field), $actual['context']['actual_correction_context'][$field] ?? null);
        }

        $this->compareArrayField($mismatches, 'lineage', $this->ctx($expectedContext, 'expected_lineage'), $actual['context']['actual_lineage']);
        if ($fixture['expected_reason_code_counts_present']) {
            $this->compareReasonCodeCounts($mismatches, $expectedReasonCodeCounts ?: [], $actual['reason_code_counts']);
        }

        $artifactChangedScope = $this->resolveArtifactChangedScope($expectedContext['expected_artifact_context'], $actual);
        $mismatchReasonCodes = array_values(array_unique(array_map(function ($item) {
            return $item['reason_code'];
        }, $mismatches)));
        sort($mismatchReasonCodes);
        $mismatchSummary = $this->buildOperatorMismatchSummary($mismatches, $mismatchReasonCodes);

        $comparisonResult = empty($mismatches)
            ? ($expectedClass === 'EXPECTED_DEGRADE' ? 'EXPECTED_DEGRADE' : 'MATCH')
            : ($expectedClass === 'EXPECTED_DEGRADE' ? 'UNEXPECTED' : 'MISMATCH');
        $comparisonNote = empty($mismatches)
            ? ($expectedReplay['comparison_note'] ?? 'Replay verification matched fixture expectation.')
            : 'Replay verification diverged from fixture expectation.';
        $finalReasonCode = empty($mismatchReasonCodes)
            ? ($actual['context']['actual_final_state']['final_reason_code'] ?: ($this->ctx($expectedContext, 'expected_reason_code') ?: 'REPLAY_MATCH'))
            : $mismatchReasonCodes[0];

        return [
            'expected_status' => $this->ctx($expectedContext, 'expected_final_state.terminal_status'),
            'expected_terminal_status' => $this->ctx($expectedContext, 'expected_final_state.terminal_status'),
            'expected_publishability_state' => $this->ctx($expectedContext, 'expected_final_state.publishability_state'),
            'expected_trade_date_effective' => $this->ctx($expectedContext, 'expected_run_context.trade_date_effective'),
            'expected_seal_state' => $this->ctx($expectedContext, 'expected_seal_context.seal_state'),
            'expected_source_mode' => $this->ctx($expectedContext, 'expected_source_context.source_mode'),
            'expected_source_name' => $this->ctx($expectedContext, 'expected_source_context.source_name'),
            'expected_source_provider' => $this->ctx($expectedContext, 'expected_source_context.source_provider') ?: $this->ctx($expectedContext, 'expected_source_context.provider'),
            'expected_source_timeout_seconds' => $this->ctx($expectedContext, 'expected_source_context.source_timeout_seconds'),
            'expected_source_retry_max' => $this->ctx($expectedContext, 'expected_source_context.source_retry_max'),
            'expected_source_attempt_count' => $this->ctx($expectedContext, 'expected_source_context.source_attempt_count'),
            'expected_source_success_after_retry' => $this->ctx($expectedContext, 'expected_source_context.source_success_after_retry'),
            'expected_source_retry_exhausted' => $this->ctx($expectedContext, 'expected_source_context.source_retry_exhausted'),
            'expected_source_final_http_status' => $this->ctx($expectedContext, 'expected_source_context.source_final_http_status'),
            'expected_source_final_reason_code' => $this->ctx($expectedContext, 'expected_source_context.source_final_reason_code'),
            'expected_source_input_file' => $this->ctx($expectedContext, 'expected_source_context.source_input_file'),
            'expected_source_file_hash' => $this->ctx($expectedContext, 'expected_source_context.source_file_hash'),
            'expected_source_file_hash_algorithm' => $this->ctx($expectedContext, 'expected_source_context.source_file_hash_algorithm'),
            'expected_source_file_size_bytes' => $this->ctx($expectedContext, 'expected_source_context.source_file_size_bytes'),
            'expected_source_file_row_count' => $this->ctx($expectedContext, 'expected_source_context.source_file_row_count'),
            'expected_config_identity' => $expectedReplay['config_identity'] ?? ($expectedReplay['expected_config_identity'] ?? null),
            'expected_publication_id' => $this->ctx($expectedContext, 'expected_publication_context.publication_id'),
            'expected_publication_run_id' => $this->ctx($expectedContext, 'expected_publication_context.publication_run_id'),
            'expected_publication_version' => $this->ctx($expectedContext, 'expected_publication_context.publication_version'),
            'expected_is_current_publication' => $this->ctx($expectedContext, 'expected_publication_context.publication_is_current'),
            'expected_correction_id' => $this->ctx($expectedContext, 'expected_correction_context.correction_id'),
            'expected_correction_status' => $this->ctx($expectedContext, 'expected_correction_context.correction_status'),
            'expected_correction_outcome' => $this->ctx($expectedContext, 'expected_correction_context.correction_outcome'),
            'expected_correction_reseal_status' => $this->ctx($expectedContext, 'expected_correction_context.correction_reseal_status'),
            'expected_correction_publication_switch' => $this->ctx($expectedContext, 'expected_correction_context.correction_publication_switch'),
            'expected_baseline_publication_id' => $this->ctx($expectedContext, 'expected_correction_context.baseline_publication_id'),
            'expected_candidate_publication_id' => $this->ctx($expectedContext, 'expected_correction_context.candidate_publication_id'),
            'expected_coverage_universe_count' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_universe_count'),
            'expected_coverage_available_count' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_available_count'),
            'expected_coverage_missing_count' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_missing_count'),
            'expected_coverage_ratio' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_ratio'),
            'expected_coverage_min_threshold' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_min_threshold'),
            'expected_coverage_gate_state' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_gate_state'),
            'expected_coverage_reason_code' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_reason_code'),
            'expected_coverage_threshold_mode' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_threshold_mode'),
            'expected_coverage_universe_basis' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_universe_basis'),
            'expected_coverage_contract_version' => $this->ctx($expectedContext, 'expected_coverage_context.coverage_contract_version'),
            'expected_coverage_missing_sample_json' => json_encode($this->normalizeList($this->ctx($expectedContext, 'expected_coverage_context.coverage_missing_sample')), JSON_UNESCAPED_SLASHES),
            'expected_bars_batch_hash' => $this->ctx($expectedContext, 'expected_artifact_context.bars_batch_hash'),
            'expected_indicators_batch_hash' => $this->ctx($expectedContext, 'expected_artifact_context.indicators_batch_hash'),
            'expected_eligibility_batch_hash' => $this->ctx($expectedContext, 'expected_artifact_context.eligibility_batch_hash'),
            'expected_reason_code_counts_json' => json_encode($this->normalizeReasonCodeCounts($expectedReasonCodeCounts ?: []), JSON_UNESCAPED_SLASHES),
            'comparison_result' => $comparisonResult,
            'comparison_note' => $comparisonNote,
            'artifact_changed_scope' => $artifactChangedScope,
            'mismatch_summary' => $mismatchSummary,
            'mismatch_count' => count($mismatches),
            'mismatch_reason_codes' => $mismatchReasonCodes,
            'mismatches' => $mismatches,
            'expected_context' => $expectedContext,
            'deterministic_fields_checked' => array_values(array_unique($this->deterministicFieldsChecked)),
            'final_reason_code' => $finalReasonCode,
        ];
    }

    private function buildOperatorMismatchSummary(array $mismatches, array $mismatchReasonCodes)
    {
        if (empty($mismatches)) {
            return null;
        }

        $reasonCounts = [];
        foreach ($mismatches as $mismatch) {
            $reasonCode = (string) ($mismatch['reason_code'] ?? 'REPLAY_MISMATCH');
            $reasonCounts[$reasonCode] = ($reasonCounts[$reasonCode] ?? 0) + 1;
        }
        ksort($reasonCounts);

        $parts = [];
        foreach ($reasonCounts as $reasonCode => $count) {
            $parts[] = $reasonCode.':'.$count;
        }

        $firstFields = array_slice(array_map(function ($mismatch) {
            return (string) ($mismatch['field'] ?? 'unknown');
        }, $mismatches), 0, 5);

        $summary = 'mismatch_count='.count($mismatches)
            .' | reason_codes='.implode(',', $parts)
            .' | first_fields='.implode(',', $firstFields)
            .' | details=mismatches_json';

        return strlen($summary) > 1000 ? substr($summary, 0, 997).'...' : $summary;
    }

    private function buildExpectedContext(array $fixture)
    {
        $r = $fixture['expected_replay_result'];
        $run = $fixture['expected_run_summary'] ?: [];
        $hashes = $fixture['expected_hashes'] ?: [];
        $expectedRun = $r['expected_run_context'] ?? [];
        $expectedSource = $r['expected_source_context'] ?? [];
        $expectedCoverage = $r['expected_coverage_context'] ?? [];
        $expectedArtifact = $r['expected_artifact_context'] ?? [];
        $expectedSeal = $r['expected_seal_context'] ?? [];
        $expectedPublication = $r['expected_publication_context'] ?? [];
        $expectedPointer = $r['expected_pointer_context'] ?? [];
        $expectedFallback = $r['expected_fallback_context'] ?? [];
        $expectedCorrection = $r['expected_correction_context'] ?? [];
        $expectedFinal = $r['expected_final_state'] ?? [];
        $expectedLineage = $r['expected_lineage'] ?? [];

        $expectedRun = $this->mergeMissing($expectedRun, [
            'trade_date_requested' => $r['expected_trade_date_requested'] ?? ($r['trade_date_requested'] ?? null),
            'trade_date_effective' => $r['expected_trade_date_effective'] ?? ($r['trade_date_effective'] ?? null),
            'request_mode' => $r['expected_request_mode'] ?? ($r['request_mode'] ?? null),
            'import_status' => $r['expected_import_status'] ?? ($r['import_status'] ?? null),
            'promote_status' => $r['expected_promote_status'] ?? ($r['promote_status'] ?? null),
            'promoted' => $r['expected_promoted'] ?? ($r['promoted'] ?? null),
            'pointer_switched' => $r['expected_pointer_switched'] ?? ($r['pointer_switched'] ?? null),
            'promote_mode' => $r['expected_promote_mode'] ?? ($r['promote_mode'] ?? null),
            'publish_target' => $r['expected_publish_target'] ?? ($r['publish_target'] ?? null),
            'terminal_status' => $r['expected_terminal_status'] ?? ($r['expected_status'] ?? ($r['status'] ?? null)),
            'publishability_state' => $r['expected_publishability_state'] ?? ($r['publishability_state'] ?? null),
            'final_reason_code' => $r['expected_reason_code'] ?? ($r['final_reason_code'] ?? null),
        ]);
        $expectedFinal = $this->mergeMissing($expectedFinal, [
            'terminal_status' => $expectedRun['terminal_status'] ?? null,
            'publishability_state' => $expectedRun['publishability_state'] ?? null,
            'final_reason_code' => $expectedRun['final_reason_code'] ?? null,
        ]);
        $expectedCoverage = $this->mergeMissing($expectedCoverage, [
            'coverage_universe_count' => $r['coverage_universe_count'] ?? null,
            'coverage_expected_count' => $r['coverage_expected_count'] ?? ($r['coverage_universe_count'] ?? null),
            'coverage_available_count' => $r['coverage_available_count'] ?? null,
            'coverage_missing_count' => $r['coverage_missing_count'] ?? null,
            'coverage_ratio' => $r['coverage_ratio'] ?? null,
            'coverage_min_threshold' => $r['coverage_min_threshold'] ?? null,
            'coverage_gate_state' => $r['coverage_gate_state'] ?? null,
            'coverage_reason_code' => $r['coverage_reason_code'] ?? null,
            'coverage_threshold_mode' => $r['coverage_threshold_mode'] ?? null,
            'coverage_universe_basis' => $r['coverage_universe_basis'] ?? null,
            'coverage_contract_version' => $r['coverage_contract_version'] ?? null,
            'coverage_missing_sample' => $r['coverage_missing_sample'] ?? [],
        ]);
        $expectedArtifact = $this->mergeMissing($expectedArtifact, [
            'bars_rows_written' => $run['bars_rows_written'] ?? ($r['bars_rows_written'] ?? null),
            'indicators_rows_written' => $run['indicators_rows_written'] ?? ($r['indicators_rows_written'] ?? null),
            'eligibility_rows_written' => $run['eligibility_rows_written'] ?? ($r['eligibility_rows_written'] ?? null),
            'eligible_count' => $run['eligible_count'] ?? ($r['eligible_count'] ?? null),
            'invalid_bar_count' => $run['invalid_bar_count'] ?? ($r['invalid_bar_count'] ?? null),
            'invalid_indicator_count' => $run['invalid_indicator_count'] ?? ($r['invalid_indicator_count'] ?? null),
            'warning_count' => $run['warning_count'] ?? ($r['warning_count'] ?? null),
            'hard_reject_count' => $run['hard_reject_count'] ?? ($r['hard_reject_count'] ?? null),
            'bars_batch_hash' => $hashes['bars_batch_hash'] ?? ($r['bars_batch_hash'] ?? null),
            'indicators_batch_hash' => $hashes['indicators_batch_hash'] ?? ($r['indicators_batch_hash'] ?? null),
            'eligibility_batch_hash' => $hashes['eligibility_batch_hash'] ?? ($r['eligibility_batch_hash'] ?? null),
        ]);
        $expectedSeal = $this->mergeMissing($expectedSeal, [
            'seal_state' => $r['expected_seal_state'] ?? ($r['seal_state'] ?? null),
        ]);
        $expectedPublication = $this->mergeMissing($expectedPublication, [
            'publication_id' => $r['expected_publication_id'] ?? ($r['publication_id'] ?? null),
            'publication_run_id' => $r['expected_publication_run_id'] ?? ($r['publication_run_id'] ?? null),
            'publication_version' => $r['expected_publication_version'] ?? ($r['publication_version'] ?? null),
            'publication_terminal_status' => $r['expected_terminal_status'] ?? ($r['expected_status'] ?? null),
            'publication_publishability_state' => $r['expected_publishability_state'] ?? null,
            'publication_is_current' => array_key_exists('expected_is_current_publication', $r) ? $r['expected_is_current_publication'] : ($r['is_current_publication'] ?? null),
            'publication_seal_state' => $r['expected_seal_state'] ?? ($r['seal_state'] ?? null),
        ]);
        $expectedPointer = $this->mergeMissing($expectedPointer, [
            'pointer_publication_id' => $expectedPublication['publication_id'] ?? null,
            'pointer_run_id' => $expectedPublication['publication_run_id'] ?? null,
            'pointer_publication_version' => $expectedPublication['publication_version'] ?? null,
            'pointer_resolve_status' => (($expectedPublication['publication_publishability_state'] ?? null) === 'READABLE' && ($expectedPublication['publication_is_current'] ?? null)) ? 'RESOLVED_READABLE_CURRENT' : 'NOT_RESOLVED_READABLE_CURRENT',
            'pointer_switched' => $expectedPublication['publication_is_current'] ?? null,
        ]);
        $expectedSource = $this->mergeMissing($expectedSource, $this->expectedSourceContext($r, $run));
        $expectedSource = $this->mergeMissing($expectedSource, [
            'source_identity' => $this->buildExpectedSourceIdentity($expectedSource),
        ]);
        $expectedFallback = $this->mergeMissing($expectedFallback, [
            'fallback_used' => $r['expected_fallback_used'] ?? ($r['fallback_used'] ?? false),
            'fallback_publication_id' => $r['expected_fallback_publication_id'] ?? ($r['fallback_publication_id'] ?? null),
            'fallback_run_id' => $r['expected_fallback_run_id'] ?? ($r['fallback_run_id'] ?? null),
        ]);
        $expectedCorrection = $this->mergeMissing($expectedCorrection, [
            'correction_id' => $r['expected_correction_id'] ?? ($r['correction_id'] ?? null),
            'correction_status' => $r['expected_correction_status'] ?? ($r['correction_status'] ?? null),
            'correction_outcome' => $r['expected_correction_outcome'] ?? ($r['correction_outcome'] ?? null),
            'correction_reseal_status' => $r['expected_correction_reseal_status'] ?? ($r['correction_reseal_status'] ?? null),
            'correction_publication_switch' => $r['expected_correction_publication_switch'] ?? ($r['correction_publication_switch'] ?? null),
            'baseline_publication_id' => $r['expected_baseline_publication_id'] ?? ($r['baseline_publication_id'] ?? null),
            'candidate_publication_id' => $r['expected_candidate_publication_id'] ?? ($r['candidate_publication_id'] ?? null),
        ]);

        return [
            'fixture_metadata' => [
                'fixture_id' => $fixture['manifest']['fixture_id'] ?? null,
                'fixture_version' => $fixture['manifest']['fixture_version'] ?? ($fixture['manifest']['version'] ?? null),
                'fixture_schema_version' => $fixture['manifest']['fixture_schema_version'] ?? null,
                'fixture_created_at' => $fixture['manifest']['fixture_created_at'] ?? null,
                'fixture_source' => $fixture['manifest']['fixture_source'] ?? null,
            ],
            'expected_run_context' => $expectedRun,
            'expected_source_context' => $expectedSource,
            'expected_coverage_context' => $expectedCoverage,
            'expected_artifact_context' => $expectedArtifact,
            'expected_seal_context' => $expectedSeal,
            'expected_publication_context' => $expectedPublication,
            'expected_pointer_context' => $expectedPointer,
            'expected_fallback_context' => $expectedFallback,
            'expected_correction_context' => $expectedCorrection,
            'expected_final_state' => $expectedFinal,
            'expected_reason_code' => $r['expected_reason_code'] ?? ($expectedFinal['final_reason_code'] ?? null),
            'expected_lineage' => $expectedLineage,
        ];
    }

    private function appendManualFilePolicyMismatches(array &$mismatches, array $expectedSourceContext, array $actual)
    {
        $expectedMode = strtolower((string) ($expectedSourceContext['source_mode'] ?? ''));
        $actualMode = strtolower((string) ($actual['source_mode'] ?? ''));
        $actualSourceName = strtoupper((string) ($actual['source_name'] ?? ''));
        $actualProvider = $actual['source_provider'] ?? null;
        $expectedManual = in_array($expectedMode, ['manual_file', 'manual_entry'], true);
        $actualManual = in_array($actualMode, ['manual_file', 'manual_entry'], true);

        if ($expectedManual && ! $actualManual) {
            $this->appendMismatch($mismatches, 'manual_file_source_mode_policy', $expectedMode, $actualMode, 'REPLAY_SOURCE_MODE_MISMATCH');
        }
        if ($expectedMode !== '' && ! $expectedManual && $actualManual) {
            $this->appendMismatch($mismatches, 'api_source_mode_policy', $expectedMode, $actualMode, 'REPLAY_SOURCE_MODE_MISMATCH');
        }
        if ($actualManual && $actualSourceName !== '' && ! in_array($actualSourceName, ['LOCAL_FILE', 'MANUAL_FILE'], true)) {
            $this->appendMismatch($mismatches, 'manual_file_source_name_policy', 'LOCAL_FILE', $actualSourceName, 'REPLAY_SOURCE_IDENTITY_MISMATCH');
        }
        if ($actualManual && $actualProvider !== null && $actualProvider !== '') {
            $this->appendMismatch($mismatches, 'manual_file_provider_policy', null, $actualProvider, 'REPLAY_PROVIDER_CONTEXT_MISMATCH');
        }
        if ($actualManual && (string) ($actual['publishability_state'] ?? '') === 'READABLE' && strtoupper((string) ($actual['coverage_gate_state'] ?? '')) !== 'PASS') {
            $this->appendMismatch($mismatches, 'manual_file_readable_coverage_policy', 'coverage_gate_state=PASS before READABLE', $actual['coverage_gate_state'] ?? null, 'REPLAY_COVERAGE_STATE_MISMATCH');
        }
    }

    private function expectedSourceContext(array $expectedReplay, array $expectedRun)
    {
        $contexts = [];
        foreach (['expected_source_context', 'source_context'] as $key) {
            if (isset($expectedReplay[$key]) && is_array($expectedReplay[$key])) {
                $contexts[] = $expectedReplay[$key];
            }
        }
        if (isset($expectedRun['source_context']) && is_array($expectedRun['source_context'])) {
            $contexts[] = $expectedRun['source_context'];
        }
        $contexts[] = $expectedReplay;
        $contexts[] = $expectedRun;

        $aliases = [
            'source_mode' => ['expected_source_mode', 'source_mode', 'source'],
            'source_name' => ['expected_source_name', 'source_name'],
            'source_provider' => ['expected_source_provider', 'source_provider', 'provider'],
            'provider' => ['expected_source_provider', 'source_provider', 'provider'],
            'source_timeout_seconds' => ['expected_source_timeout_seconds', 'source_timeout_seconds', 'timeout_seconds'],
            'source_retry_max' => ['expected_source_retry_max', 'source_retry_max', 'retry_max'],
            'source_attempt_count' => ['expected_source_attempt_count', 'source_attempt_count', 'attempt_count'],
            'source_success_after_retry' => ['expected_source_success_after_retry', 'source_success_after_retry', 'success_after_retry'],
            'source_retry_exhausted' => ['expected_source_retry_exhausted', 'source_retry_exhausted', 'retry_exhausted'],
            'source_final_http_status' => ['expected_source_final_http_status', 'source_final_http_status', 'final_http_status'],
            'source_final_reason_code' => ['expected_source_final_reason_code', 'source_final_reason_code', 'final_reason_code'],
            'source_input_file' => ['expected_source_input_file', 'source_input_file'],
            'source_file_hash' => ['expected_source_file_hash', 'source_file_hash'],
            'source_file_hash_algorithm' => ['expected_source_file_hash_algorithm', 'source_file_hash_algorithm'],
            'source_file_size_bytes' => ['expected_source_file_size_bytes', 'source_file_size_bytes'],
            'source_file_row_count' => ['expected_source_file_row_count', 'source_file_row_count'],
            'accepted_row_count' => ['accepted_row_count'],
            'rejected_row_count' => ['rejected_row_count'],
            'invalid_row_count' => ['invalid_row_count'],
        ];

        $resolved = [];
        foreach ($aliases as $field => $keys) {
            $resolved[$field] = $this->valueFromContexts($contexts, $keys);
        }

        return $resolved;
    }

    private function compareField(array &$mismatches, $field, $expected, $actual)
    {
        if ($expected === null) {
            return;
        }
        $this->deterministicFieldsChecked[] = $field;
        if ($this->normalizeScalar($expected) !== $this->normalizeScalar($actual)) {
            $this->appendMismatch($mismatches, $field, $expected, $actual, $this->reasonCodeForField($field));
        }
    }


    private function compareFieldAllowNull(array &$mismatches, $field, $expected, $actual)
    {
        $this->deterministicFieldsChecked[] = $field;
        if ($this->normalizeScalar($expected) !== $this->normalizeScalar($actual)) {
            $this->appendMismatch($mismatches, $field, $expected, $actual, $this->reasonCodeForField($field));
        }
    }

    private function compareNumericFieldAllowNull(array &$mismatches, $field, $expected, $actual)
    {
        $this->deterministicFieldsChecked[] = $field;
        if ($expected === null || $actual === null) {
            if ($expected !== $actual) {
                $this->appendMismatch($mismatches, $field, $expected, $actual, $this->reasonCodeForField($field));
            }
            return;
        }
        if ((float) $expected !== (float) $actual) {
            $this->appendMismatch($mismatches, $field, $expected, $actual, $this->reasonCodeForField($field));
        }
    }

    private function compareNumericField(array &$mismatches, $field, $expected, $actual)
    {
        if ($expected === null) {
            return;
        }
        $this->deterministicFieldsChecked[] = $field;
        if ($actual === null || (float) $expected !== (float) $actual) {
            $this->appendMismatch($mismatches, $field, $expected, $actual, $this->reasonCodeForField($field));
        }
    }

    private function compareListField(array &$mismatches, $field, $expected, $actual)
    {
        if ($expected === null) {
            return;
        }
        $this->deterministicFieldsChecked[] = $field;
        $expectedNormalized = $this->normalizeList($expected);
        $actualNormalized = $this->normalizeList($actual);
        if ($expectedNormalized !== $actualNormalized) {
            $this->appendMismatch($mismatches, $field, $expectedNormalized, $actualNormalized, $this->reasonCodeForField($field));
        }
    }

    private function compareArrayField(array &$mismatches, $field, $expected, $actual)
    {
        if ($expected === null) {
            return;
        }
        $this->deterministicFieldsChecked[] = $field;
        $expectedNormalized = $this->normalizeArrayForComparison($expected);
        $actualNormalized = $this->normalizeArrayForComparison($actual);
        if ($expectedNormalized !== $actualNormalized) {
            $this->appendMismatch($mismatches, $field, $expectedNormalized, $actualNormalized, $this->reasonCodeForField($field));
        }
    }

    private function compareReasonCodeCounts(array &$mismatches, array $expectedCounts, array $actualCounts)
    {
        $this->deterministicFieldsChecked[] = 'reason_code_counts';
        $expectedNormalized = [];
        foreach ($this->normalizeReasonCodeCounts($expectedCounts) as $item) {
            $expectedNormalized[$item['reason_code']] = $item['reason_count'];
        }
        $actualNormalized = [];
        foreach ($this->normalizeReasonCodeCounts($actualCounts) as $item) {
            $actualNormalized[$item['reason_code']] = $item['reason_count'];
        }
        if ($expectedNormalized !== $actualNormalized) {
            $this->appendMismatch($mismatches, 'reason_code_counts', $expectedNormalized, $actualNormalized, 'REPLAY_FINAL_REASON_CODE_MISMATCH');
        }
    }

    private function appendMismatch(array &$mismatches, $field, $expected, $actual, $reasonCode)
    {
        $mismatches[] = [
            'field' => $field,
            'expected' => $expected,
            'actual' => $actual,
            'reason_code' => $reasonCode,
        ];
    }

    private function reasonCodeForField($field)
    {
        if (strpos($field, 'expected_proof.') === 0) return 'REPLAY_EXPECTED_PROOF_INCOMPLETE';
        if (strpos($field, 'actual_proof.') === 0) return 'REPLAY_ACTUAL_PROOF_INCOMPLETE';
        if ($field === 'trade_date_requested') return 'REPLAY_REQUESTED_DATE_MISMATCH';
        if ($field === 'trade_date_effective') return 'REPLAY_EFFECTIVE_DATE_MISMATCH';
        if ($field === 'request_mode' || $field === 'promote_mode' || $field === 'publish_target') return 'REPLAY_REQUEST_MODE_MISMATCH';
        if ($field === 'import_status') return 'REPLAY_IMPORT_STATUS_MISMATCH';
        if ($field === 'promote_status' || $field === 'promoted') return 'REPLAY_PROMOTE_STATUS_MISMATCH';
        if ($field === 'pointer_switched') return 'REPLAY_UNEXPECTED_PUBLICATION_PROMOTION';
        if (strpos($field, 'source_file_hash') !== false) return 'REPLAY_SOURCE_FILE_HASH_MISMATCH';
        if (strpos($field, 'source_mode') !== false) return 'REPLAY_SOURCE_MODE_MISMATCH';
        if (strpos($field, 'source_provider') !== false || $field === 'provider' || strpos($field, 'http') !== false || strpos($field, 'retry') !== false || strpos($field, 'timeout') !== false || strpos($field, 'attempt') !== false) return 'REPLAY_PROVIDER_CONTEXT_MISMATCH';
        if (strpos($field, 'source_') !== false || strpos($field, 'row_count') !== false) return 'REPLAY_SOURCE_IDENTITY_MISMATCH';
        if (strpos($field, 'rows_written') !== false || strpos($field, 'invalid_') !== false || $field === 'eligible_count' || $field === 'warning_count' || $field === 'hard_reject_count') return 'REPLAY_ARTIFACT_HASH_MISMATCH';
        if ($field === 'coverage_gate_state') return 'REPLAY_COVERAGE_STATE_MISMATCH';
        if ($field === 'coverage_ratio' || $field === 'coverage_min_threshold') return 'REPLAY_COVERAGE_RATIO_MISMATCH';
        if ($field === 'coverage_reason_code') return 'REPLAY_COVERAGE_REASON_MISMATCH';
        if (strpos($field, 'coverage_') !== false) return 'REPLAY_COVERAGE_STATE_MISMATCH';
        if (strpos($field, 'batch_hash') !== false) return 'REPLAY_ARTIFACT_HASH_MISMATCH';
        if ($field === 'seal_state') return 'REPLAY_SEAL_STATE_MISMATCH';
        if (strpos($field, 'publication_version') !== false) return 'REPLAY_PUBLICATION_VERSION_MISMATCH';
        if (strpos($field, 'publication_') !== false && strpos($field, 'pointer_') === false) return 'REPLAY_PUBLICATION_STATE_MISMATCH';
        if ($field === 'pointer_resolve_status') return 'REPLAY_POINTER_RESOLUTION_MISMATCH';
        if (strpos($field, 'pointer_') !== false) return 'REPLAY_POINTER_TARGET_MISMATCH';
        if (strpos($field, 'fallback_') !== false) return 'REPLAY_FALLBACK_CONTEXT_MISMATCH';
        if (strpos($field, 'correction_') !== false || strpos($field, 'baseline_') !== false || strpos($field, 'candidate_') !== false) return 'REPLAY_CORRECTION_BASELINE_MISMATCH';
        if ($field === 'terminal_status' || $field === 'status' || $field === 'publishability_state') return 'REPLAY_FINAL_STATUS_MISMATCH';
        if ($field === 'final_reason_code' || $field === 'expected_reason_code' || $field === 'reason_code_counts') return 'REPLAY_FINAL_REASON_CODE_MISMATCH';
        if ($field === 'lineage') return 'REPLAY_LINEAGE_MISMATCH';
        return 'REPLAY_NON_DETERMINISTIC_OUTPUT';
    }

    private function resolveArtifactChangedScope(array $expectedArtifact, array $actual)
    {
        $changed = [];
        foreach (['bars_batch_hash' => 'bars', 'indicators_batch_hash' => 'indicators', 'eligibility_batch_hash' => 'eligibility'] as $field => $label) {
            if (! array_key_exists($field, $expectedArtifact) || $expectedArtifact[$field] === null) {
                continue;
            }
            if ((string) $expectedArtifact[$field] !== (string) $actual[$field]) {
                $changed[] = $label;
            }
        }
        if (empty($changed)) return 'none';
        if (count($changed) === 1) return $changed[0].'_only';
        return 'multi_artifact';
    }

    private function resolvePublicationForRun($run)
    {
        if ((string) ($run->terminal_status ?? '') !== 'SUCCESS' || (string) ($run->publishability_state ?? '') !== 'READABLE') {
            return null;
        }
        return $this->publications->findReadableCurrentPublicationForRun($run->run_id, $run->trade_date_requested);
    }

    private function findCorrectionForRun($runId)
    {
        try { return $this->evidence->findCorrectionByRunId($runId); } catch (\Throwable $e) { return null; }
    }

    private function resolveCorrectionOutcome($correction)
    {
        if (! $correction || ! isset($correction->status)) return null;
        $status = strtoupper((string) $correction->status);
        if ($status === 'CONSUMED_CURRENT' || $status === 'CANCELLED') return 'UNCHANGED';
        if ($status === 'PUBLISHED') return 'PUBLISHED';
        if ($status === 'RESEALED' || $status === 'REPAIR_EXECUTED') return 'RESEALED';
        return $status;
    }

    private function resolveCorrectionResealStatus($correction)
    {
        if (! $correction || ! isset($correction->status)) return null;
        $status = strtoupper((string) $correction->status);
        if ($status === 'CONSUMED_CURRENT' || $status === 'CANCELLED') return 'NOT_RESEALED_UNCHANGED';
        if (in_array($status, ['PUBLISHED', 'RESEALED', 'REPAIR_EXECUTED'], true)) return 'RESEALED';
        return null;
    }

    private function resolveCoverageReasonCodeFromState($coverageGateState)
    {
        $state = strtoupper((string) $coverageGateState);
        if ($state === 'PASS') return 'COVERAGE_THRESHOLD_MET';
        if ($state === 'FAIL') return 'COVERAGE_BELOW_THRESHOLD';
        if ($state === 'NOT_EVALUABLE' || $state === 'BLOCKED') return 'RUN_COVERAGE_NOT_EVALUABLE';
        return null;
    }

    private function readJsonFile($path, $reasonCode = 'REPLAY_FIXTURE_SCHEMA_MISMATCH')
    {
        $decoded = json_decode(file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new \RuntimeException($reasonCode.': Invalid JSON fixture file: '.$path);
        }
        return $decoded;
    }

    private function optionalJsonFile($path, $reasonCode = 'REPLAY_EXPECTED_PROOF_INCOMPLETE')
    {
        if (! is_file($path)) return null;
        return $this->readJsonFile($path, $reasonCode);
    }

    private function hasPath(array $payload, $path)
    {
        $cursor = $payload;
        foreach (explode('.', $path) as $part) {
            if (! is_array($cursor) || ! array_key_exists($part, $cursor)) return false;
            $cursor = $cursor[$part];
        }
        return true;
    }

    private function ctx(array $payload, $path)
    {
        $cursor = $payload;
        foreach (explode('.', $path) as $part) {
            if (! is_array($cursor) || ! array_key_exists($part, $cursor)) return null;
            $cursor = $cursor[$part];
        }
        return $cursor;
    }

    private function valueFromContexts(array $contexts, array $keys)
    {
        foreach ($contexts as $context) {
            if (! is_array($context)) continue;
            foreach ($keys as $key) {
                if (array_key_exists($key, $context) && $context[$key] !== null && $context[$key] !== '') return $context[$key];
            }
        }
        return null;
    }

    private function mergeMissing(array $base, array $fallback)
    {
        foreach ($fallback as $key => $value) {
            if (! array_key_exists($key, $base)) $base[$key] = $value;
        }
        return $base;
    }

    private function normalizeBooleanForComparison($value)
    {
        if ($value === null || $value === '') return null;
        return $this->normalizeBoolean($value) ? 1 : 0;
    }

    private function normalizeBoolean($value)
    {
        if (is_bool($value)) return $value;
        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'true', 'yes', 'y'], true)) return true;
        if (in_array($normalized, ['0', 'false', 'no', 'n', ''], true)) return false;
        return (bool) $value;
    }

    private function normalizeScalar($value)
    {
        if (is_bool($value)) return $value ? '1' : '0';
        if ($value === null) return null;
        if (is_array($value)) return json_encode($this->normalizeArrayForComparison($value), JSON_UNESCAPED_SLASHES);
        return (string) $value;
    }

    private function normalizeList($items)
    {
        if ($items === null || $items === '') return [];
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [$items];
        }
        if (! is_array($items)) return [(string) $items];
        $normalized = array_map(function ($item) { return (string) $item; }, array_values($items));
        sort($normalized);
        return $normalized;
    }

    private function normalizeArrayForComparison($items)
    {
        if ($items === null) return null;
        if (! is_array($items)) return $items;
        $normalized = [];
        foreach ($items as $key => $value) {
            if (in_array($key, $this->ignoredVolatileFields, true)) continue;
            $normalized[$key] = is_array($value) ? $this->normalizeArrayForComparison($value) : $value;
        }
        ksort($normalized);
        return $normalized;
    }

    private function decodeJsonArray($value)
    {
        if ($value === null || $value === '') return [];
        if (is_array($value)) return array_values($value);
        $decoded = json_decode($value, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function normalizeReasonCodeCounts(array $items)
    {
        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! array_key_exists('reason_code', $item)) continue;
            $normalized[] = [
                'reason_code' => (string) $item['reason_code'],
                'reason_count' => (int) ($item['reason_count'] ?? $item['count'] ?? 0),
            ];
        }
        usort($normalized, function ($left, $right) { return strcmp($left['reason_code'], $right['reason_code']); });
        return $normalized;
    }

    private function parseNotes($notes)
    {
        $parsed = [];
        foreach (preg_split('/[|;]/', (string) $notes) as $part) {
            if (strpos($part, '=') === false) continue;
            list($key, $value) = array_map('trim', explode('=', $part, 2));
            if ($key !== '') $parsed[$key] = $value;
        }
        return $parsed;
    }

    private function buildSourceIdentity($record)
    {
        return $this->buildExpectedSourceIdentity([
            'source_mode' => $record->source ?? null,
            'source_name' => $record->source_name ?? null,
            'source_provider' => $record->source_provider ?? null,
            'source_input_file' => $record->source_input_file ?? null,
            'source_file_hash' => $record->source_file_hash ?? null,
        ]);
    }

    private function buildExpectedSourceIdentity(array $source)
    {
        $parts = [];
        if (($source['source_mode'] ?? null) !== null) $parts[] = 'mode='.$source['source_mode'];
        if (($source['source_name'] ?? null) !== null) $parts[] = 'name='.$source['source_name'];
        if (($source['source_provider'] ?? ($source['provider'] ?? null)) !== null) $parts[] = 'provider='.($source['source_provider'] ?? $source['provider']);
        if (($source['source_input_file'] ?? null) !== null) $parts[] = 'input='.basename(str_replace('\\', '/', (string) $source['source_input_file']));
        if (($source['source_file_hash'] ?? null) !== null) $parts[] = 'hash='.$source['source_file_hash'];
        return empty($parts) ? null : implode('|', $parts);
    }
}
