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
            throw new \RuntimeException('Run not found for replay verification.');
        }

        if ((string) ($run->terminal_status ?? '') !== 'SUCCESS' || (string) ($run->publishability_state ?? '') !== 'READABLE') {
            throw new \RuntimeException('Replay verification requires a SUCCESS + READABLE run; non-readable runs cannot be consumed through publication read path.');
        }

        $publication = $this->resolvePublicationForRun($run);
        $correction = $this->findCorrectionForRun($run->run_id);
        $actual = $this->buildActualReplayState($run, $publication, $correction);
        $comparison = $this->compareExpectedAndActual($fixture, $actual);
        $replayId = $replayId ?: $this->replays->nextReplayId();

        $metric = [
            'replay_id' => $replayId,
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
        ];

        $this->replays->upsertMetric($metric);
        $this->replays->replaceReasonCodeCounts($replayId, $actual['trade_date'], $actual['reason_code_counts']);

        return $metric + [
            'reason_code_counts' => $actual['reason_code_counts'],
            'fixture_family' => $fixture['manifest']['fixture_family'],
            'fixture_version' => $fixture['manifest']['version'],
            'mismatches' => $comparison['mismatches'],
        ];
    }

    private function loadFixturePackage($fixturePath)
    {
        $manifestPath = rtrim($fixturePath, '/').'/manifest.json';
        if (! is_file($manifestPath)) {
            throw new \RuntimeException('Replay fixture manifest not found: '.$manifestPath);
        }

        $manifest = $this->readJsonFile($manifestPath);
        foreach (['fixture_family', 'version', 'contract_areas', 'files', 'assertion_layers'] as $field) {
            if (! array_key_exists($field, $manifest)) {
                throw new \RuntimeException('Replay fixture manifest missing required field: '.$field);
            }
        }

        if (! in_array('replay', $manifest['assertion_layers'], true)) {
            throw new \RuntimeException('Replay fixture manifest must include assertion layer: replay');
        }

        foreach ((array) $manifest['files'] as $relativePath) {
            $resolvedPath = rtrim($fixturePath, '/').'/'.$relativePath;
            if (! is_file($resolvedPath)) {
                throw new \RuntimeException('Replay fixture file missing: '.$relativePath);
            }
        }

        return [
            'manifest' => $manifest,
            'expected_replay_result' => $this->readJsonFile(rtrim($fixturePath, '/').'/expected/expected_replay_result.json'),
            'expected_run_summary' => $this->optionalJsonFile(rtrim($fixturePath, '/').'/expected/expected_run_summary.json'),
            'expected_hashes' => $this->optionalJsonFile(rtrim($fixturePath, '/').'/expected/expected_hashes.json'),
            'expected_reason_code_counts' => $this->optionalJsonFile(rtrim($fixturePath, '/').'/expected/expected_reason_code_counts.json'),
        ];
    }

    private function buildActualReplayState($run, $publication = null, $correction = null)
    {
        $resolvedTradeDate = $run->trade_date_effective ?: $run->trade_date_requested;
        $reasonCodeCounts = [];
        foreach ($this->evidence->dominantReasonCodes($run->run_id, $resolvedTradeDate, $publication ? $publication->publication_id : null) as $row) {
            $reasonCodeCounts[] = [
                'reason_code' => $row['reason_code'],
                'reason_count' => (int) $row['count'],
            ];
        }

        $eligibleCount = 0;
        foreach ($this->evidence->exportEligibilityRows($resolvedTradeDate, $publication ? $publication->publication_id : null) as $row) {
            if ((int) ($row['eligible'] ?? 0) === 1) {
                $eligibleCount++;
            }
        }

        return [
            'trade_date' => $run->trade_date_requested,
            'trade_date_effective' => $resolvedTradeDate,
            'source' => $run->source,
            'source_mode' => $run->source,
            'source_name' => $run->source_name ?? null,
            'source_provider' => $run->source_provider ?? null,
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
            'accepted_row_count' => $run->bars_rows_written !== null ? (int) $run->bars_rows_written : null,
            'rejected_row_count' => $run->invalid_bar_count !== null ? (int) $run->invalid_bar_count : null,
            'invalid_row_count' => $run->invalid_bar_count !== null ? (int) $run->invalid_bar_count : null,
            'status' => $run->terminal_status,
            'terminal_status' => $run->terminal_status,
            'publishability_state' => $run->publishability_state,
            'config_identity' => $run->config_version,
            'publication_id' => $publication && isset($publication->publication_id) && $publication->publication_id !== null ? (int) $publication->publication_id : (isset($run->publication_id) && $run->publication_id !== null ? (int) $run->publication_id : null),
            'publication_run_id' => $publication && isset($publication->run_id) && $publication->run_id !== null ? (int) $publication->run_id : (isset($run->run_id) && $run->run_id !== null ? (int) $run->run_id : null),
            'publication_version' => $publication && $publication->publication_version !== null ? (int) $publication->publication_version : ($run->publication_version !== null ? (int) $run->publication_version : null),
            'is_current_publication' => $publication && isset($publication->is_current) ? (bool) $publication->is_current : (isset($run->is_current_publication) ? (bool) $run->is_current_publication : false),
            'correction_id' => $correction && isset($correction->correction_id) ? (int) $correction->correction_id : (isset($run->correction_id) && $run->correction_id !== null ? (int) $run->correction_id : null),
            'correction_status' => $correction && isset($correction->status) ? $correction->status : null,
            'correction_outcome' => $this->resolveCorrectionOutcome($correction),
            'correction_reseal_status' => $this->resolveCorrectionResealStatus($correction),
            'correction_publication_switch' => $correction && isset($correction->new_publication_is_current) && $correction->new_publication_is_current !== null ? (bool) $correction->new_publication_is_current : null,
            'baseline_publication_id' => $correction && isset($correction->prior_publication_id) && $correction->prior_publication_id !== null ? (int) $correction->prior_publication_id : null,
            'candidate_publication_id' => $correction && isset($correction->new_publication_id) && $correction->new_publication_id !== null ? (int) $correction->new_publication_id : null,
            'coverage_universe_count' => isset($run->coverage_universe_count) && $run->coverage_universe_count !== null ? (int) $run->coverage_universe_count : null,
            'coverage_available_count' => isset($run->coverage_available_count) && $run->coverage_available_count !== null ? (int) $run->coverage_available_count : null,
            'coverage_missing_count' => isset($run->coverage_missing_count) && $run->coverage_missing_count !== null ? (int) $run->coverage_missing_count : null,
            'coverage_ratio' => $run->coverage_ratio !== null ? (float) $run->coverage_ratio : null,
            'coverage_min_threshold' => isset($run->coverage_min_threshold) && $run->coverage_min_threshold !== null ? (float) $run->coverage_min_threshold : null,
            'coverage_gate_state' => $run->coverage_gate_state ?? null,
            'coverage_threshold_mode' => $run->coverage_threshold_mode ?? null,
            'coverage_universe_basis' => $run->coverage_universe_basis ?? null,
            'coverage_contract_version' => $run->coverage_contract_version ?? null,
            'coverage_missing_sample' => $this->decodeJsonArray($run->coverage_missing_sample_json ?? null),
            'coverage_reason_code' => $this->resolveCoverageReasonCodeFromState($run->coverage_gate_state ?? null),
            'bars_rows_written' => $run->bars_rows_written !== null ? (int) $run->bars_rows_written : null,
            'indicators_rows_written' => $run->indicators_rows_written !== null ? (int) $run->indicators_rows_written : null,
            'eligibility_rows_written' => $run->eligibility_rows_written !== null ? (int) $run->eligibility_rows_written : null,
            'eligible_count' => $eligibleCount,
            'invalid_bar_count' => $run->invalid_bar_count !== null ? (int) $run->invalid_bar_count : null,
            'invalid_indicator_count' => $run->invalid_indicator_count !== null ? (int) $run->invalid_indicator_count : null,
            'warning_count' => $run->warning_count !== null ? (int) $run->warning_count : null,
            'hard_reject_count' => $run->hard_reject_count !== null ? (int) $run->hard_reject_count : null,
            'bars_batch_hash' => $run->bars_batch_hash,
            'indicators_batch_hash' => $run->indicators_batch_hash,
            'eligibility_batch_hash' => $run->eligibility_batch_hash,
            'seal_state' => $publication && isset($publication->seal_state) && $publication->seal_state ? $publication->seal_state : ($run->sealed_at ? 'SEALED' : 'UNSEALED'),
            'sealed_at' => $publication && isset($publication->sealed_at) && $publication->sealed_at ? $publication->sealed_at : $run->sealed_at,
            'reason_code_counts' => $reasonCodeCounts,
        ];
    }

    private function findCorrectionForRun($runId)
    {
        try {
            return $this->evidence->findCorrectionByRunId($runId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveCorrectionOutcome($correction)
    {
        if (! $correction || ! isset($correction->status)) {
            return null;
        }

        $status = strtoupper((string) $correction->status);
        if ($status === 'CONSUMED_CURRENT' || $status === 'CANCELLED') {
            return 'UNCHANGED';
        }

        if ($status === 'PUBLISHED') {
            return 'PUBLISHED';
        }

        if ($status === 'RESEALED' || $status === 'REPAIR_EXECUTED') {
            return 'RESEALED';
        }

        return $status;
    }

    private function resolveCorrectionResealStatus($correction)
    {
        if (! $correction || ! isset($correction->status)) {
            return null;
        }

        $status = strtoupper((string) $correction->status);
        if ($status === 'CONSUMED_CURRENT' || $status === 'CANCELLED') {
            return 'NOT_RESEALED_UNCHANGED';
        }

        if (in_array($status, ['PUBLISHED', 'RESEALED', 'REPAIR_EXECUTED'], true)) {
            return 'RESEALED';
        }

        return null;
    }

    private function resolveCoverageReasonCodeFromState($coverageGateState)
    {
        $state = strtoupper((string) $coverageGateState);

        if ($state === 'PASS') {
            return 'COVERAGE_THRESHOLD_MET';
        }

        if ($state === 'FAIL') {
            return 'COVERAGE_BELOW_THRESHOLD';
        }

        if ($state === 'NOT_EVALUABLE' || $state === 'BLOCKED') {
            return 'RUN_COVERAGE_NOT_EVALUABLE';
        }

        return null;
    }

    private function compareExpectedAndActual(array $fixture, array $actual)
    {
        $expectedReplay = $fixture['expected_replay_result'];
        $expectedRun = $fixture['expected_run_summary'] ?: [];
        $expectedHashes = $fixture['expected_hashes'] ?: [];
        $expectedReasonCodeCounts = $fixture['expected_reason_code_counts'] ?: [];
        $expectedSourceContext = $this->expectedSourceContext($expectedReplay, $expectedRun);
        $expectedClass = $expectedReplay['comparison_result'] ?? 'MATCH';

        $mismatches = [];
        // COVERAGE_FIELD_MISMATCH: coverage context mismatches must remain visible in replay results.
        $this->compareField($mismatches, 'status', $expectedReplay['expected_status'] ?? $expectedReplay['status'] ?? null, $actual['status']);
        $this->compareField($mismatches, 'terminal_status', $expectedReplay['expected_terminal_status'] ?? $expectedReplay['terminal_status'] ?? null, $actual['terminal_status']);
        $this->compareField($mismatches, 'publishability_state', $expectedReplay['expected_publishability_state'] ?? $expectedReplay['publishability_state'] ?? null, $actual['publishability_state']);
        $this->compareField($mismatches, 'trade_date_effective', $expectedReplay['expected_trade_date_effective'] ?? $expectedReplay['trade_date_effective'] ?? null, $actual['trade_date_effective']);
        $this->compareField($mismatches, 'seal_state', $expectedReplay['expected_seal_state'] ?? $expectedReplay['seal_state'] ?? null, $actual['seal_state']);
        $this->compareField($mismatches, 'config_identity', $expectedReplay['config_identity'] ?? null, $actual['config_identity']);
        $this->compareField($mismatches, 'publication_id', $expectedReplay['expected_publication_id'] ?? ($expectedReplay['publication_id'] ?? null), $actual['publication_id']);
        $this->compareField($mismatches, 'publication_run_id', $expectedReplay['expected_publication_run_id'] ?? ($expectedReplay['publication_run_id'] ?? null), $actual['publication_run_id']);
        $this->compareField($mismatches, 'publication_version', $expectedReplay['expected_publication_version'] ?? ($expectedReplay['publication_version'] ?? null), $actual['publication_version']);
        $expectedCurrentPublication = array_key_exists('expected_is_current_publication', $expectedReplay)
            ? (int) (bool) $expectedReplay['expected_is_current_publication']
            : (array_key_exists('is_current_publication', $expectedReplay) ? (int) (bool) $expectedReplay['is_current_publication'] : null);
        $this->compareField($mismatches, 'is_current_publication', $expectedCurrentPublication, (int) (bool) $actual['is_current_publication']);

        foreach (['source_mode', 'source_name', 'source_provider', 'source_final_reason_code', 'source_input_file', 'source_file_hash', 'source_file_hash_algorithm'] as $field) {
            $this->compareField($mismatches, $field, $expectedSourceContext[$field] ?? null, $actual[$field]);
        }

        foreach (['source_timeout_seconds', 'source_retry_max', 'source_attempt_count', 'source_final_http_status', 'source_file_size_bytes', 'source_file_row_count'] as $field) {
            $this->compareNumericField($mismatches, $field, $expectedSourceContext[$field] ?? null, $actual[$field]);
        }

        $this->appendManualFilePolicyMismatches($mismatches, $expectedSourceContext, $actual);

        foreach (['source_success_after_retry', 'source_retry_exhausted'] as $field) {
            $expectedBool = $this->normalizeBooleanForComparison($expectedSourceContext[$field] ?? null);
            $actualBool = $this->normalizeBooleanForComparison($actual[$field]);
            $this->compareField($mismatches, $field, $expectedBool, $actualBool);
        }

        foreach (['correction_id', 'correction_status', 'correction_outcome', 'correction_reseal_status', 'correction_publication_switch', 'baseline_publication_id', 'candidate_publication_id'] as $field) {
            $expectedCorrectionValue = array_key_exists('expected_'.$field, $expectedReplay)
                ? $expectedReplay['expected_'.$field]
                : (array_key_exists($field, $expectedReplay) ? $expectedReplay[$field] : null);
            $this->compareField($mismatches, $field, $expectedCorrectionValue, $actual[$field]);
        }

        foreach (['coverage_universe_count', 'coverage_available_count', 'coverage_missing_count', 'coverage_gate_state', 'coverage_reason_code', 'coverage_threshold_mode', 'coverage_universe_basis', 'coverage_contract_version'] as $field) {
            $this->compareField($mismatches, $field, $expectedReplay[$field] ?? null, $actual[$field]);
        }

        foreach (['coverage_ratio', 'coverage_min_threshold'] as $field) {
            $this->compareNumericField($mismatches, $field, $expectedReplay[$field] ?? null, $actual[$field]);
        }
        $this->compareListField($mismatches, 'coverage_missing_sample', $expectedReplay['coverage_missing_sample'] ?? null, $actual['coverage_missing_sample']);

        foreach (['bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written', 'invalid_bar_count', 'invalid_indicator_count', 'warning_count', 'hard_reject_count', 'eligible_count'] as $field) {
            $expectedValue = array_key_exists($field, $expectedRun) ? $expectedRun[$field] : (array_key_exists($field, $expectedReplay) ? $expectedReplay[$field] : null);
            $this->compareField($mismatches, $field, $expectedValue, $actual[$field]);
        }

        foreach (['accepted_row_count', 'rejected_row_count', 'invalid_row_count'] as $field) {
            $expectedValue = array_key_exists($field, $expectedRun) ? $expectedRun[$field] : (array_key_exists($field, $expectedReplay) ? $expectedReplay[$field] : null);
            $this->compareField($mismatches, $field, $expectedValue, $actual[$field]);
        }       

        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $field) {
            $expectedValue = array_key_exists($field, $expectedHashes) ? $expectedHashes[$field] : (array_key_exists($field, $expectedReplay) ? $expectedReplay[$field] : null);
            $this->compareField($mismatches, $field, $expectedValue, $actual[$field]);
        }

        $this->compareReasonCodeCounts($mismatches, $expectedReasonCodeCounts, $actual['reason_code_counts']);

        $artifactChangedScope = $this->resolveArtifactChangedScope($expectedHashes, $actual);
        $mismatchSummary = empty($mismatches) ? null : implode('; ', array_map(function ($item) {
            return $item['field'].': expected '.var_export($item['expected'], true).' got '.var_export($item['actual'], true);
        }, $mismatches));

        $comparisonResult = empty($mismatches)
            ? ($expectedClass === 'EXPECTED_DEGRADE' ? 'EXPECTED_DEGRADE' : 'MATCH')
            : ($expectedClass === 'EXPECTED_DEGRADE' ? 'UNEXPECTED' : 'MISMATCH');

        $comparisonNote = empty($mismatches)
            ? ($expectedReplay['comparison_note'] ?? 'Replay verification matched fixture expectation.')
            : 'Replay verification diverged from fixture expectation.';

        return [
            'expected_status' => $expectedReplay['expected_status'] ?? $expectedReplay['status'] ?? null,
            'expected_terminal_status' => $expectedReplay['expected_terminal_status'] ?? $expectedReplay['terminal_status'] ?? null,
            'expected_publishability_state' => $expectedReplay['expected_publishability_state'] ?? $expectedReplay['publishability_state'] ?? null,
            'expected_trade_date_effective' => $expectedReplay['expected_trade_date_effective'] ?? $expectedReplay['trade_date_effective'] ?? null,
            'expected_seal_state' => $expectedReplay['expected_seal_state'] ?? $expectedReplay['seal_state'] ?? null,
            'expected_source_mode' => $expectedSourceContext['source_mode'] ?? null,
            'expected_source_name' => $expectedSourceContext['source_name'] ?? null,
            'expected_source_provider' => $expectedSourceContext['source_provider'] ?? null,
            'expected_source_timeout_seconds' => $expectedSourceContext['source_timeout_seconds'] ?? null,
            'expected_source_retry_max' => $expectedSourceContext['source_retry_max'] ?? null,
            'expected_source_attempt_count' => $expectedSourceContext['source_attempt_count'] ?? null,
            'expected_source_success_after_retry' => $expectedSourceContext['source_success_after_retry'] ?? null,
            'expected_source_retry_exhausted' => $expectedSourceContext['source_retry_exhausted'] ?? null,
            'expected_source_final_http_status' => $expectedSourceContext['source_final_http_status'] ?? null,
            'expected_source_final_reason_code' => $expectedSourceContext['source_final_reason_code'] ?? null,
            'expected_source_input_file' => $expectedSourceContext['source_input_file'] ?? null,
            'expected_source_file_hash' => $expectedSourceContext['source_file_hash'] ?? null,
            'expected_source_file_hash_algorithm' => $expectedSourceContext['source_file_hash_algorithm'] ?? null,
            'expected_source_file_size_bytes' => $expectedSourceContext['source_file_size_bytes'] ?? null,
            'expected_source_file_row_count' => $expectedSourceContext['source_file_row_count'] ?? null,
            'expected_config_identity' => $expectedReplay['config_identity'] ?? null,
            'expected_publication_id' => $expectedReplay['expected_publication_id'] ?? ($expectedReplay['publication_id'] ?? null),
            'expected_publication_run_id' => $expectedReplay['expected_publication_run_id'] ?? ($expectedReplay['publication_run_id'] ?? null),
            'expected_publication_version' => $expectedReplay['expected_publication_version'] ?? ($expectedReplay['publication_version'] ?? null),
            'expected_is_current_publication' => array_key_exists('expected_is_current_publication', $expectedReplay)
                ? (bool) $expectedReplay['expected_is_current_publication']
                : (array_key_exists('is_current_publication', $expectedReplay) ? (bool) $expectedReplay['is_current_publication'] : null),
            'expected_correction_id' => $expectedReplay['expected_correction_id'] ?? ($expectedReplay['correction_id'] ?? null),
            'expected_correction_status' => $expectedReplay['expected_correction_status'] ?? ($expectedReplay['correction_status'] ?? null),
            'expected_correction_outcome' => $expectedReplay['expected_correction_outcome'] ?? ($expectedReplay['correction_outcome'] ?? null),
            'expected_correction_reseal_status' => $expectedReplay['expected_correction_reseal_status'] ?? ($expectedReplay['correction_reseal_status'] ?? null),
            'expected_correction_publication_switch' => $expectedReplay['expected_correction_publication_switch'] ?? ($expectedReplay['correction_publication_switch'] ?? null),
            'expected_baseline_publication_id' => $expectedReplay['expected_baseline_publication_id'] ?? ($expectedReplay['baseline_publication_id'] ?? null),
            'expected_candidate_publication_id' => $expectedReplay['expected_candidate_publication_id'] ?? ($expectedReplay['candidate_publication_id'] ?? null),
            'expected_coverage_universe_count' => $expectedReplay['coverage_universe_count'] ?? null,
            'expected_coverage_available_count' => $expectedReplay['coverage_available_count'] ?? null,
            'expected_coverage_missing_count' => $expectedReplay['coverage_missing_count'] ?? null,
            'expected_coverage_ratio' => $expectedReplay['coverage_ratio'] ?? null,
            'expected_coverage_min_threshold' => $expectedReplay['coverage_min_threshold'] ?? null,
            'expected_coverage_gate_state' => $expectedReplay['coverage_gate_state'] ?? null,
            'expected_coverage_reason_code' => $expectedReplay['coverage_reason_code'] ?? null,
            'expected_coverage_threshold_mode' => $expectedReplay['coverage_threshold_mode'] ?? null,
            'expected_coverage_universe_basis' => $expectedReplay['coverage_universe_basis'] ?? null,
            'expected_coverage_contract_version' => $expectedReplay['coverage_contract_version'] ?? null,
            'expected_coverage_missing_sample_json' => json_encode($this->normalizeList($expectedReplay['coverage_missing_sample'] ?? []), JSON_UNESCAPED_SLASHES),
            'expected_bars_batch_hash' => $expectedHashes['bars_batch_hash'] ?? ($expectedReplay['bars_batch_hash'] ?? null),
            'expected_indicators_batch_hash' => $expectedHashes['indicators_batch_hash'] ?? ($expectedReplay['indicators_batch_hash'] ?? null),
            'expected_eligibility_batch_hash' => $expectedHashes['eligibility_batch_hash'] ?? ($expectedReplay['eligibility_batch_hash'] ?? null),
            'expected_reason_code_counts_json' => json_encode($this->normalizeReasonCodeCounts($expectedReasonCodeCounts), JSON_UNESCAPED_SLASHES),
            'comparison_result' => $comparisonResult,
            'comparison_note' => $comparisonNote,
            'artifact_changed_scope' => $artifactChangedScope,
            'mismatch_summary' => $mismatchSummary,
            'mismatches' => $mismatches,
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
            $mismatches[] = [
                'field' => 'manual_file_source_mode_policy',
                'expected' => $expectedMode,
                'actual' => $actualMode,
            ];
        }

        if ($expectedMode !== '' && ! $expectedManual && $actualManual) {
            $mismatches[] = [
                'field' => 'api_source_mode_policy',
                'expected' => $expectedMode,
                'actual' => $actualMode,
            ];
        }

        if ($actualManual && $actualSourceName !== '' && ! in_array($actualSourceName, ['LOCAL_FILE', 'MANUAL_FILE'], true)) {
            $mismatches[] = [
                'field' => 'manual_file_source_name_policy',
                'expected' => 'LOCAL_FILE',
                'actual' => $actualSourceName,
            ];
        }

        if ($actualManual && $actualProvider !== null && $actualProvider !== '') {
            $mismatches[] = [
                'field' => 'manual_file_provider_policy',
                'expected' => null,
                'actual' => $actualProvider,
            ];
        }

        if ($actualManual
            && (string) ($actual['publishability_state'] ?? '') === 'READABLE'
            && strtoupper((string) ($actual['coverage_gate_state'] ?? '')) !== 'PASS') {
            $mismatches[] = [
                'field' => 'manual_file_readable_coverage_policy',
                'expected' => 'coverage_gate_state=PASS before READABLE',
                'actual' => $actual['coverage_gate_state'] ?? null,
            ];
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
        ];

        $resolved = [];
        foreach ($aliases as $field => $keys) {
            $resolved[$field] = null;
            foreach ($contexts as $context) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, $context) && $context[$key] !== null && $context[$key] !== '') {
                        $resolved[$field] = $context[$key];
                        break 2;
                    }
                }
            }
        }

        return $resolved;
    }

    private function normalizeBooleanForComparison($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return (int) $value;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'true', 'yes', 'y'], true)) {
            return 1;
        }
        if (in_array($normalized, ['0', 'false', 'no', 'n'], true)) {
            return 0;
        }

        return (int) (bool) $value;
    }

    private function compareField(array &$mismatches, $field, $expected, $actual)
    {
        if ($expected === null) {
            return;
        }

        if ((string) $expected !== (string) $actual) {
            $mismatches[] = [
                'field' => $field,
                'expected' => $expected,
                'actual' => $actual,
            ];
        }
    }

    private function compareNumericField(array &$mismatches, $field, $expected, $actual)
    {
        if ($expected === null) {
            return;
        }

        if ($actual === null) {
            $mismatches[] = [
                'field' => $field,
                'expected' => $expected,
                'actual' => $actual,
            ];
            return;
        }

        if ((float) $expected !== (float) $actual) {
            $mismatches[] = [
                'field' => $field,
                'expected' => $expected,
                'actual' => $actual,
            ];
        }
    }

    private function resolveArtifactChangedScope(array $expectedHashes, array $actual)
    {
        $changed = [];
        foreach ([
            'bars_batch_hash' => 'bars',
            'indicators_batch_hash' => 'indicators',
            'eligibility_batch_hash' => 'eligibility',
        ] as $field => $label) {
            if (! array_key_exists($field, $expectedHashes)) {
                continue;
            }
            if ((string) $expectedHashes[$field] !== (string) $actual[$field]) {
                $changed[] = $label;
            }
        }

        if (empty($changed)) {
            return 'none';
        }
        if (count($changed) === 1) {
            return $changed[0].'_only';
        }

        return 'multi_artifact';
    }



    private function compareListField(array &$mismatches, $field, $expected, $actual)
    {
        if ($expected === null) {
            return;
        }

        $expectedNormalized = $this->normalizeList($expected);
        $actualNormalized = $this->normalizeList($actual);

        if ($expectedNormalized !== $actualNormalized) {
            $mismatches[] = [
                'field' => $field,
                'expected' => $expectedNormalized,
                'actual' => $actualNormalized,
            ];
        }
    }

    private function normalizeList($items)
    {
        if ($items === null || $items === '') {
            return [];
        }

        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [$items];
        }

        if (! is_array($items)) {
            return [(string) $items];
        }

        $normalized = array_map(function ($item) {
            return (string) $item;
        }, array_values($items));
        sort($normalized);

        return $normalized;
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

    private function compareReasonCodeCounts(array &$mismatches, array $expectedCounts, array $actualCounts)
    {
        if ($expectedCounts === [] || $expectedCounts === null) {
            return;
        }

        $expectedNormalized = [];
        foreach ($this->normalizeReasonCodeCounts($expectedCounts) as $item) {
            $expectedNormalized[$item['reason_code']] = $item['reason_count'];
        }

        $actualNormalized = [];
        foreach ($this->normalizeReasonCodeCounts($actualCounts) as $item) {
            $actualNormalized[$item['reason_code']] = $item['reason_count'];
        }

        if ($expectedNormalized !== $actualNormalized) {
            $mismatches[] = [
                'field' => 'reason_code_counts',
                'expected' => $expectedNormalized,
                'actual' => $actualNormalized,
            ];
        }
    }

    private function normalizeReasonCodeCounts(array $items)
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! array_key_exists('reason_code', $item)) {
                continue;
            }

            $normalized[] = [
                'reason_code' => (string) $item['reason_code'],
                'reason_count' => (int) ($item['reason_count'] ?? $item['count'] ?? 0),
            ];
        }

        usort($normalized, function ($left, $right) {
            return strcmp($left['reason_code'], $right['reason_code']);
        });

        return $normalized;
    }

    private function resolvePublicationForRun($run)
    {
        if ($run->terminal_status !== 'SUCCESS' || $run->publishability_state !== 'READABLE') {
            return null;
        }

        $publication = $this->publications->findReadableCurrentPublicationForRun($run->run_id, $run->trade_date_requested);
        if (! $publication) {
            throw new \RuntimeException('Readable current publication not found for replay verification.');
        }

        return $publication;
    }

    private function readJsonFile($path)
    {
        $decoded = json_decode(file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON fixture file: '.$path);
        }

        return $decoded;
    }

    private function optionalJsonFile($path)
    {
        if (! is_file($path)) {
            return null;
        }

        return $this->readJsonFile($path);
    }
}
