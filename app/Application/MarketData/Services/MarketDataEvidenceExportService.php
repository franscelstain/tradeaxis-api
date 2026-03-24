<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;

class MarketDataEvidenceExportService
{
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
        $eventSummary = ['run_id' => (int) $run->run_id, 'trade_date_requested' => $run->trade_date_requested] + $this->evidence->summarizeRunEvents($run->run_id);
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
        ];

        $dir = $outputDir ?: $this->defaultRunOutputDir($run->run_id);
        $this->ensureDirectory($dir);
        $this->writeJson($dir.'/run_summary.json', $runSummary);
        if ($manifest) {
            $this->writeJson($dir.'/publication_manifest.json', $manifest);
        }
        $this->writeJson($dir.'/run_event_summary.json', $eventSummary);
        $this->writeCsv($dir.'/eligibility_export.csv', ['trade_date', 'ticker_id', 'eligible', 'reason_code'], $eligibilityRows);
        $this->writeCsv($dir.'/invalid_bars_export.csv', ['trade_date', 'ticker_id', 'source', 'source_row_ref', 'invalid_reason_code'], $invalidBarsRows);
        file_put_contents($dir.'/anomaly_report.md', $anomalyReport);
        $this->writeJson($dir.'/evidence_pack.json', $payload);

        return [
            'output_dir' => $dir,
            'files' => array_values(array_filter([
                'run_summary.json',
                $manifest ? 'publication_manifest.json' : null,
                'run_event_summary.json',
                'eligibility_export.csv',
                'invalid_bars_export.csv',
                'anomaly_report.md',
                'evidence_pack.json',
            ])),
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

        return [
            'output_dir' => $dir,
            'files' => ['correction_evidence.json'],
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

        return [
            'output_dir' => $dir,
            'files' => [
                'replay_result.json',
                'replay_expected_state.json',
                'replay_actual_state.json',
                'replay_reason_code_counts.json',
                'replay_evidence_pack.json',
            ],
        ];
    }

    private function resolvePublicationForRun($run)
    {
        $publication = $this->evidence->findPublicationForRun($run->run_id);
        if ($run->terminal_status === 'SUCCESS' && $run->publishability_state === 'READABLE') {
            $current = $this->publications->findCurrentPublicationForTradeDate($run->trade_date_requested);
            if ($current && (int) $current->run_id === (int) $run->run_id) {
                return $current;
            }
            if ($run->trade_date_effective && $run->trade_date_effective !== $run->trade_date_requested) {
                $fallback = $this->publications->findCurrentPublicationForTradeDate($run->trade_date_effective);
                if ($fallback) {
                    return $fallback;
                }
            }
        }

        return $publication;
    }

    private function buildRunSummary($run, $manifest = null)
    {
        return [
            'run_id' => (int) $run->run_id,
            'trade_date_requested' => $run->trade_date_requested,
            'trade_date_effective' => $run->trade_date_effective,
            'lifecycle_state' => $run->lifecycle_state,
            'terminal_status' => $run->terminal_status,
            'quality_gate_state' => $run->quality_gate_state,
            'publishability_state' => $run->publishability_state,
            'stage' => $run->stage,
            'source' => $run->source,
            'coverage_ratio' => $run->coverage_ratio !== null ? (float) $run->coverage_ratio : null,
            'bars_rows_written' => $run->bars_rows_written !== null ? (int) $run->bars_rows_written : null,
            'indicators_rows_written' => $run->indicators_rows_written !== null ? (int) $run->indicators_rows_written : null,
            'eligibility_rows_written' => $run->eligibility_rows_written !== null ? (int) $run->eligibility_rows_written : null,
            'invalid_bar_count' => $run->invalid_bar_count !== null ? (int) $run->invalid_bar_count : null,
            'invalid_indicator_count' => $run->invalid_indicator_count !== null ? (int) $run->invalid_indicator_count : null,
            'warning_count' => $run->warning_count !== null ? (int) $run->warning_count : null,
            'hard_reject_count' => $run->hard_reject_count !== null ? (int) $run->hard_reject_count : null,
            'bars_batch_hash' => $run->bars_batch_hash,
            'indicators_batch_hash' => $run->indicators_batch_hash,
            'eligibility_batch_hash' => $run->eligibility_batch_hash,
            'sealed_at' => $run->sealed_at,
            'config_version' => $run->config_version,
            'config_hash' => $run->config_hash,
            'config_snapshot_ref' => $run->config_snapshot_ref,
            'publication_version' => $manifest ? (int) $manifest['publication_version'] : ($run->publication_version !== null ? (int) $run->publication_version : null),
            'is_current_publication' => $manifest ? (bool) $manifest['is_current'] : (bool) $run->is_current_publication,
            'supersedes_run_id' => $run->supersedes_run_id !== null ? (int) $run->supersedes_run_id : null,
            'started_at' => $run->started_at,
            'finished_at' => $run->finished_at,
        ];
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
            'bars_batch_hash' => $metric->bars_batch_hash,
            'indicators_batch_hash' => $metric->indicators_batch_hash,
            'eligibility_batch_hash' => $metric->eligibility_batch_hash,
            'reason_code_counts' => $reasonCodes,
        ];
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
