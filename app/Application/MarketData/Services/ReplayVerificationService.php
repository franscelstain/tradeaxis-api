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
            'comparison_result' => $comparison['comparison_result'],
            'comparison_note' => $comparison['comparison_note'],
            'artifact_changed_scope' => $comparison['artifact_changed_scope'],
            'config_identity' => $actual['config_identity'],
            'publication_version' => $actual['publication_version'],
            'coverage_ratio' => $actual['coverage_ratio'],
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
            'expected_trade_date_effective' => $comparison['expected_trade_date_effective'],
            'expected_seal_state' => $comparison['expected_seal_state'],
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
            'config_identity' => $run->config_version,
            'publication_version' => $publication && $publication->publication_version !== null ? (int) $publication->publication_version : ($run->publication_version !== null ? (int) $run->publication_version : null),
            'coverage_ratio' => $run->coverage_ratio !== null ? (float) $run->coverage_ratio : null,
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

    private function compareExpectedAndActual(array $fixture, array $actual)
    {
        $expectedReplay = $fixture['expected_replay_result'];
        $expectedRun = $fixture['expected_run_summary'] ?: [];
        $expectedHashes = $fixture['expected_hashes'] ?: [];
        $expectedClass = $expectedReplay['comparison_result'] ?? 'MATCH';

        $mismatches = [];
        $this->compareField($mismatches, 'status', $expectedReplay['expected_status'] ?? $expectedReplay['status'] ?? null, $actual['status']);
        $this->compareField($mismatches, 'trade_date_effective', $expectedReplay['expected_trade_date_effective'] ?? $expectedReplay['trade_date_effective'] ?? null, $actual['trade_date_effective']);
        $this->compareField($mismatches, 'seal_state', $expectedReplay['expected_seal_state'] ?? $expectedReplay['seal_state'] ?? null, $actual['seal_state']);
        $this->compareField($mismatches, 'config_identity', $expectedReplay['config_identity'] ?? null, $actual['config_identity']);
        $this->compareField($mismatches, 'publication_version', $expectedReplay['publication_version'] ?? null, $actual['publication_version']);

        foreach (['bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written', 'invalid_bar_count', 'invalid_indicator_count', 'warning_count', 'hard_reject_count', 'eligible_count'] as $field) {
            $expectedValue = array_key_exists($field, $expectedRun) ? $expectedRun[$field] : (array_key_exists($field, $expectedReplay) ? $expectedReplay[$field] : null);
            $this->compareField($mismatches, $field, $expectedValue, $actual[$field]);
        }

        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $field) {
            $expectedValue = array_key_exists($field, $expectedHashes) ? $expectedHashes[$field] : (array_key_exists($field, $expectedReplay) ? $expectedReplay[$field] : null);
            $this->compareField($mismatches, $field, $expectedValue, $actual[$field]);
        }

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
            'expected_trade_date_effective' => $expectedReplay['expected_trade_date_effective'] ?? $expectedReplay['trade_date_effective'] ?? null,
            'expected_seal_state' => $expectedReplay['expected_seal_state'] ?? $expectedReplay['seal_state'] ?? null,
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

    private function resolvePublicationForRun($run)
    {
        $publication = $this->evidence->findPublicationForRun($run->run_id);
        if ($publication) {
            return $publication;
        }

        if ($run->trade_date_effective) {
            return $this->publications->findPointerResolvedPublicationForTradeDate($run->trade_date_effective);
        }

        return null;
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
