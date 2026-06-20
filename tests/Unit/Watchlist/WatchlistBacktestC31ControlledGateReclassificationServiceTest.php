<?php

use App\Application\Watchlist\Services\WatchlistBacktestC31ControlledGateReclassificationService;

class WatchlistBacktestC31ControlledGateReclassificationServiceTest extends TestCase
{
    public function test_it_blocks_when_C29_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c31-missing-c29-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC31ControlledGateReclassificationService())->execute(
            sys_get_temp_dir().'/missing-c29-artifact.json',
            WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_EXPECTED_C29_HASH,
            sys_get_temp_dir().'/unused-c30-artifact.json',
            WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_EXPECTED_C30_HASH,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C31_BLOCKED_MISSING_C29_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C31_C29_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C31_BLOCKED_MISSING_C29_ARTIFACT', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertSame('C31_RECLASSIFICATION_BLOCKED', $artifact['reclassification_conclusion']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C29_hash_mismatches(): void
    {
        [$c29Path, $c30Path, $outputPath] = $this->tempPaths('c29-hash-mismatch');
        $c29 = $this->c29Artifact();
        $this->writeJson($c29Path, $c29);

        $result = (new WatchlistBacktestC31ControlledGateReclassificationService())->execute(
            $c29Path,
            'wrong-c29-hash',
            $c30Path,
            WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_EXPECTED_C30_HASH,
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C31_BLOCKED_C29_HASH_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C31_C29_ARTIFACT_HASH_MISMATCH', $result['reason_code']);
        $this->assertFalse((bool) $result['c29_hash_match']);
        $this->cleanup($c29Path, $c30Path, $outputPath);
    }

    public function test_it_blocks_when_C29_status_is_not_failed(): void
    {
        [$c29Path, $c30Path, $outputPath] = $this->tempPaths('c29-status');
        $c29 = $this->c29Artifact();
        $c29['status'] = 'C29_OOS_PROOF_PASSED_NOT_PRODUCTION_READY';
        $c29['artifact_hash'] = $this->stableHash($c29);
        $this->writeJson($c29Path, $c29);

        $result = (new WatchlistBacktestC31ControlledGateReclassificationService())->execute(
            $c29Path,
            $c29['artifact_hash'],
            $c30Path,
            WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_EXPECTED_C30_HASH,
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C31_BLOCKED_UNEXPECTED_C29_STATUS', $result['status']);
        $this->assertSame('WS_BT_C31_UNEXPECTED_C29_STATUS', $result['reason_code']);
        $this->cleanup($c29Path, $c30Path, $outputPath);
    }

    public function test_it_blocks_when_C30_artifact_is_missing(): void
    {
        [$c29Path, $c30Path, $outputPath] = $this->tempPaths('c30-missing');
        $c29 = $this->c29Artifact();
        $this->writeJson($c29Path, $c29);

        $result = (new WatchlistBacktestC31ControlledGateReclassificationService())->execute(
            $c29Path,
            $c29['artifact_hash'],
            $c30Path,
            WatchlistBacktestC31ControlledGateReclassificationService::DEFAULT_EXPECTED_C30_HASH,
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C31_BLOCKED_MISSING_C30_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C31_C30_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertTrue((bool) $result['c29_hash_match']);
        $this->cleanup($c29Path, $c30Path, $outputPath);
    }

    public function test_it_blocks_when_expected_C30_hash_mismatches(): void
    {
        [$c29Path, $c30Path, $outputPath] = $this->tempPaths('c30-hash-mismatch');
        $c29 = $this->c29Artifact();
        $c30 = $this->c30Artifact();
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c30Path, $c30);

        $result = (new WatchlistBacktestC31ControlledGateReclassificationService())->execute(
            $c29Path,
            $c29['artifact_hash'],
            $c30Path,
            'wrong-c30-hash',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C31_BLOCKED_C30_HASH_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C31_C30_ARTIFACT_HASH_MISMATCH', $result['reason_code']);
        $this->assertFalse((bool) $result['c30_hash_match']);
        $this->cleanup($c29Path, $c30Path, $outputPath);
    }

    public function test_it_blocks_when_C30_status_is_not_completed(): void
    {
        [$c29Path, $c30Path, $outputPath] = $this->tempPaths('c30-status');
        $c29 = $this->c29Artifact();
        $c30 = $this->c30Artifact();
        $c30['status'] = 'C30_OPERATOR_VALIDATION_REQUIRED';
        $c30['artifact_hash'] = $this->stableHash($c30);
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c30Path, $c30);

        $result = (new WatchlistBacktestC31ControlledGateReclassificationService())->execute(
            $c29Path,
            $c29['artifact_hash'],
            $c30Path,
            $c30['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C31_BLOCKED_UNEXPECTED_C30_STATUS', $result['status']);
        $this->assertSame('WS_BT_C31_UNEXPECTED_C30_STATUS', $result['reason_code']);
        $this->cleanup($c29Path, $c30Path, $outputPath);
    }

    public function test_it_blocks_when_C30_verdict_is_unknown(): void
    {
        [$c29Path, $c30Path, $outputPath] = $this->tempPaths('c30-verdict');
        $c29 = $this->c29Artifact();
        $c30 = $this->c30Artifact();
        $c30['attribution_verdict'] = 'UNKNOWN_VERDICT';
        $c30['artifact_hash'] = $this->stableHash($c30);
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c30Path, $c30);

        $result = (new WatchlistBacktestC31ControlledGateReclassificationService())->execute(
            $c29Path,
            $c29['artifact_hash'],
            $c30Path,
            $c30['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C31_BLOCKED_UNEXPECTED_C30_VERDICT', $result['status']);
        $this->assertSame('WS_BT_C31_UNEXPECTED_C30_VERDICT', $result['reason_code']);
        $this->cleanup($c29Path, $c30Path, $outputPath);
    }

    public function test_it_completes_controlled_gate_reclassification_for_valid_C29_and_C30_artifacts(): void
    {
        [$c29Path, $c30Path, $outputPath] = $this->tempPaths('completed');
        $c29 = $this->c29Artifact();
        $c30 = $this->c30Artifact();
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c30Path, $c30);

        $result = (new WatchlistBacktestC31ControlledGateReclassificationService())->execute(
            $c29Path,
            $c29['artifact_hash'],
            $c30Path,
            $c30['artifact_hash'],
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertSame('C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK', $result['reclassification_conclusion']);
        $this->assertSame('C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS', $result['controlled_proof_status']);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C31_CONTROLLED_GATE_RECLASSIFICATION', $out['run_code']);
        $this->assertSame('C31_CONTROLLED_GATE_RECLASSIFICATION', $out['artifact_type']);
        $this->assertFalse($out['production_ready']);
        $this->assertSame('C29_OOS_PROOF_FAILED', $out['c29_status']);
        $this->assertSame('C30_ATTRIBUTION_COMPLETED', $out['c30_status']);
        $this->assertSame('MIXED_DATA_AND_STRATEGY_FAILURE', $out['c30_attribution_verdict']);
        $this->assertTrue($out['c29_hash_match']);
        $this->assertTrue($out['c30_hash_match']);

        $this->assertSame(128, $out['source_c29_metrics']['evaluated_picks_count']);
        $this->assertSame(4, $out['source_c29_metrics']['lookahead_violation_count']);
        $this->assertSame(132, $out['source_c30_classification_summary']['total_oos_pick_rows']);
        $this->assertSame(4, $out['source_c30_classification_summary']['reported_lookahead_violation_count']);
        $this->assertSame(0, $out['source_c30_classification_summary']['actual_lookahead_violation_count']);
        $this->assertSame(0, $out['source_c30_classification_summary']['selection_leak_count']);
        $this->assertSame(4, $out['source_c30_classification_summary']['missing_path_count']);
        $this->assertSame(4, $out['source_c30_classification_summary']['non_evaluable_pick_count']);
        $this->assertSame(128, $out['source_c30_classification_summary']['clean_evaluable_pick_count']);
        $this->assertSame(128, $out['source_c30_clean_metrics']['clean_evaluated_picks_count']);
        $this->assertSame(0, $out['source_c30_clean_metrics']['clean_month_win_rate_min']);

        $gates = $out['separated_gate_summary'];
        $this->assertSame('FAIL', $gates['reported_lookahead_gate']['status']);
        $this->assertSame('C31_REPORTED_LOOKAHEAD_GATE_FAIL_FROM_C29_COUNT', $gates['reported_lookahead_gate']['reason_code']);
        $this->assertSame('PASS', $gates['actual_lookahead_gate']['status']);
        $this->assertSame('C31_ACTUAL_LOOKAHEAD_GATE_PASS_NO_ACTUAL_LEAK', $gates['actual_lookahead_gate']['reason_code']);
        $this->assertSame('PASS', $gates['selection_leak_gate']['status']);
        $this->assertSame('FAIL', $gates['data_completeness_gate']['status']);
        $this->assertSame('C31_DATA_COMPLETENESS_GATE_FAIL_MISSING_PATH', $gates['data_completeness_gate']['reason_code']);
        $this->assertSame('FAIL', $gates['month_win_rate_gate']['status']);
        $this->assertSame('FAIL', $gates['clean_month_win_rate_gate']['status']);
        $this->assertSame('FAIL', $gates['overall_controlled_oos_gate']['status']);
        $this->assertSame('C31_CONTROLLED_OOS_GATE_FAIL_DATA_COMPLETENESS_AND_ROBUSTNESS', $gates['overall_controlled_oos_gate']['reason_code']);

        $this->assertSame(['2025-06', '2025-08', '2026-03'], array_column($out['bad_month_summary'], 'trade_month'));
        $branches = array_column($out['source_branch_summary'], 'selected_source_code');
        sort($branches, SORT_STRING);
        $this->assertSame(['G16', 'G21', 'R09'], $branches);
        $this->assertCount(4, $out['missing_path_rows']);
        $this->assertSame('WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING', $out['missing_path_rows'][0]['missing_path_reason_code']);
        $this->assertCount(0, $out['actual_lookahead_violation_rows']);
        $this->assertCount(0, $out['selection_leak_rows']);
        $this->cleanup($c29Path, $c30Path, $outputPath);
    }

    private function c29Artifact(): array
    {
        $artifact = [
            'run_code' => 'C29_OOS_PROOF_C28_G05',
            'status' => 'C29_OOS_PROOF_FAILED',
            'artifact_type' => 'C29_OOS_PROOF',
            'production_ready' => false,
            'metrics' => [
                'evaluated_picks_count' => 128,
                'avg_ret_net' => 0.004431048028767,
                'median_ret_net' => 0.0052763819095477,
                'p25_ret_net' => -0.0075615188321481,
                'win_rate' => 0.53125,
                'month_win_rate_min' => 0,
                'month_avg_ret_net_min' => -0.040489877530617,
            ],
            'lookahead_violation_count' => 4,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function c30Artifact(): array
    {
        $artifact = [
            'run_code' => 'C30_OOS_FAILURE_ATTRIBUTION',
            'status' => 'C30_ATTRIBUTION_COMPLETED',
            'artifact_type' => 'C30_OOS_FAILURE_ATTRIBUTION',
            'production_ready' => false,
            'input_c29_artifact' => 'fixture-c29.json',
            'expected_c29_hash' => 'fixture-c29-hash',
            'actual_c29_hash' => 'fixture-c29-hash',
            'c29_hash_match' => true,
            'c29_status' => 'C29_OOS_PROOF_FAILED',
            'attribution_verdict' => 'MIXED_DATA_AND_STRATEGY_FAILURE',
            'classification_summary' => [
                'total_oos_pick_rows' => 132,
                'reported_lookahead_violation_count' => 4,
                'actual_lookahead_violation_count' => 0,
                'selection_leak_count' => 0,
                'missing_path_count' => 4,
                'non_evaluable_pick_count' => 4,
                'clean_evaluable_pick_count' => 128,
            ],
            'clean_metrics' => [
                'clean_evaluated_picks_count' => 128,
                'clean_avg_ret_net' => 0.004431048028766952,
                'clean_median_ret_net' => 0.0052763819095477385,
                'clean_p25_ret_net' => -0.007561518832148093,
                'clean_win_rate' => 0.53125,
                'clean_month_win_rate_min' => 0,
                'clean_month_avg_ret_net_min' => -0.04048987753061734,
            ],
            'bad_month_summary' => [
                ['trade_month' => '2025-06', 'total_rows' => 12, 'clean_rows' => 10, 'missing_path_rows' => 2, 'win_rate' => 0, 'dominant_branch' => 'G21'],
                ['trade_month' => '2025-08', 'total_rows' => 9, 'clean_rows' => 7, 'missing_path_rows' => 2, 'win_rate' => 0, 'dominant_branch' => 'G21'],
                ['trade_month' => '2026-03', 'total_rows' => 4, 'clean_rows' => 4, 'missing_path_rows' => 0, 'win_rate' => 0, 'dominant_branch' => 'G16'],
            ],
            'source_branch_summary' => [
                ['selected_source_code' => 'G16', 'count' => 18, 'clean_count' => 18, 'missing_count' => 0, 'non_evaluable_count' => 0],
                ['selected_source_code' => 'G21', 'count' => 80, 'clean_count' => 80, 'missing_count' => 0, 'non_evaluable_count' => 0],
                ['selected_source_code' => 'R09', 'count' => 34, 'clean_count' => 30, 'missing_count' => 4, 'non_evaluable_count' => 4],
            ],
            'missing_path_rows' => [
                $this->missingPathRow('2025-06', '2025-06-04', 'MICE', 151),
                $this->missingPathRow('2025-06', '2025-06-04', 'MICE', 152),
                $this->missingPathRow('2025-08', '2025-08-15', 'BBSI', 151),
                $this->missingPathRow('2025-08', '2025-08-15', 'BBSI', 152),
            ],
            'actual_lookahead_violation_rows' => [],
            'selection_leak_rows' => [],
            'diagnostics' => [],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function missingPathRow(string $month, string $date, string $ticker, int $paramId): array
    {
        return [
            'trade_month' => $month,
            'trade_date' => $date,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'selected_source_code' => 'R09',
            'missing_path_reason_code' => 'WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING',
        ];
    }

    private function writeJson(string $path, array $payload): void
    {
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n");
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash']);
        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES));
    }

    private function tempPaths(string $name): array
    {
        $c29Path = sys_get_temp_dir().'/c31-'.$name.'-c29.json';
        $c30Path = sys_get_temp_dir().'/c31-'.$name.'-c30.json';
        $outputPath = sys_get_temp_dir().'/c31-'.$name.'-output.json';
        $this->cleanup($c29Path, $c30Path, $outputPath);
        return [$c29Path, $c30Path, $outputPath];
    }

    private function cleanup(string ...$files): void
    {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
