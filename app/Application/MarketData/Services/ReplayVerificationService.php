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

        $publication = $this->resolvePublicationForRun($run);
        $actual = $this->buildActualReplayState($run, $publication);
        $comparison = $this->compareExpectedAndActual($fixture, $actual);
        $replayId = $replayId ?: $this->replays->nextReplayId();

        $metric = [
            'replay_id' => $replayId,
            'trade_date' => $actual['trade_date'],
            'trade_date_effective' => $actual['trade_date_effective'],
            'source' => $actual['source'],
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

    private function buildActualReplayState($run, $publication = null)
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
            'status' => $run->terminal_status,
            'terminal_status' => $run->terminal_status,
            'publishability_state' => $run->publishability_state,
            'config_identity' => $run->config_version,
            'publication_id' => $publication && isset($publication->publication_id) && $publication->publication_id !== null ? (int) $publication->publication_id : (isset($run->publication_id) && $run->publication_id !== null ? (int) $run->publication_id : null),
            'publication_run_id' => $publication && isset($publication->run_id) && $publication->run_id !== null ? (int) $publication->run_id : (isset($run->run_id) && $run->run_id !== null ? (int) $run->run_id : null),
            'publication_version' => $publication && $publication->publication_version !== null ? (int) $publication->publication_version : ($run->publication_version !== null ? (int) $run->publication_version : null),
            'is_current_publication' => $publication && isset($publication->is_current) ? (bool) $publication->is_current : (isset($run->is_current_publication) ? (bool) $run->is_current_publication : false),
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
            'seal_state' => $publication && $publication->seal_state ? $publication->seal_state : ($run->sealed_at ? 'SEALED' : 'UNSEALED'),
            'sealed_at' => $publication && $publication->sealed_at ? $publication->sealed_at : $run->sealed_at,
            'reason_code_counts' => $reasonCodeCounts,
        ];
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
            'expected_config_identity' => $expectedReplay['config_identity'] ?? null,
            'expected_publication_id' => $expectedReplay['expected_publication_id'] ?? ($expectedReplay['publication_id'] ?? null),
            'expected_publication_run_id' => $expectedReplay['expected_publication_run_id'] ?? ($expectedReplay['publication_run_id'] ?? null),
            'expected_publication_version' => $expectedReplay['expected_publication_version'] ?? ($expectedReplay['publication_version'] ?? null),
            'expected_is_current_publication' => array_key_exists('expected_is_current_publication', $expectedReplay)
                ? (bool) $expectedReplay['expected_is_current_publication']
                : (array_key_exists('is_current_publication', $expectedReplay) ? (bool) $expectedReplay['is_current_publication'] : null),
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
            throw new \RuntimeException('Replay verification requires a SUCCESS + READABLE run; non-readable runs cannot be consumed through publication read path.');
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
