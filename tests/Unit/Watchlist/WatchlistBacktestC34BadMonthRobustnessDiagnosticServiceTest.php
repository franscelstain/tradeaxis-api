<?php

use App\Application\Watchlist\Services\WatchlistBacktestC34BadMonthRobustnessDiagnosticService;

class WatchlistBacktestC34BadMonthRobustnessDiagnosticServiceTest extends TestCase
{
    public function test_it_blocks_when_C33_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c34-missing-c33-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC34BadMonthRobustnessDiagnosticService())->execute(
            sys_get_temp_dir().'/missing-c33-artifact.json',
            WatchlistBacktestC34BadMonthRobustnessDiagnosticService::DEFAULT_EXPECTED_C33_HASH,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C34_BLOCKED_MISSING_C33_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C34_C33_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C34_BLOCKED_MISSING_C33_ARTIFACT', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertSame('C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_BLOCKED', $artifact['diagnostic_conclusion']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C33_hash_mismatches(): void
    {
        [$c32Path, $c33Path, $outputPath] = $this->tempPaths('hash-mismatch');
        $c32 = $this->c32Artifact();
        $c33 = $this->c33Artifact($c32Path, $c32['artifact_hash']);
        $this->writeJson($c32Path, $c32);
        $this->writeJson($c33Path, $c33);

        $result = (new WatchlistBacktestC34BadMonthRobustnessDiagnosticService())->execute(
            $c33Path,
            'wrong-c33-hash',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C34_BLOCKED_C33_HASH_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C34_C33_ARTIFACT_HASH_MISMATCH', $result['reason_code']);
        $this->assertFalse((bool) $result['c33_hash_match']);
        $this->cleanup($c32Path, $c33Path, $outputPath);
    }

    public function test_it_blocks_when_C33_status_is_unexpected(): void
    {
        [$c32Path, $c33Path, $outputPath] = $this->tempPaths('status');
        $c32 = $this->c32Artifact();
        $c33 = $this->c33Artifact($c32Path, $c32['artifact_hash']);
        $c33['status'] = 'C33_OPERATOR_VALIDATION_REQUIRED';
        $c33['artifact_hash'] = $this->stableHash($c33);
        $this->writeJson($c32Path, $c32);
        $this->writeJson($c33Path, $c33);

        $result = (new WatchlistBacktestC34BadMonthRobustnessDiagnosticService())->execute(
            $c33Path,
            $c33['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C34_BLOCKED_UNEXPECTED_C33_STATUS', $result['status']);
        $this->assertSame('WS_BT_C34_UNEXPECTED_C33_STATUS', $result['reason_code']);
        $this->cleanup($c32Path, $c33Path, $outputPath);
    }

    public function test_it_blocks_when_C33_data_path_replay_did_not_pass(): void
    {
        [$c32Path, $c33Path, $outputPath] = $this->tempPaths('replay-fail');
        $c32 = $this->c32Artifact();
        $c33 = $this->c33Artifact($c32Path, $c32['artifact_hash']);
        $c33['data_path_replay_status'] = 'C33_DATA_PATH_REPLAY_FAILED_MISSING_OR_INVALID_PATH';
        $c33['artifact_hash'] = $this->stableHash($c33);
        $this->writeJson($c32Path, $c32);
        $this->writeJson($c33Path, $c33);

        $result = (new WatchlistBacktestC34BadMonthRobustnessDiagnosticService())->execute(
            $c33Path,
            $c33['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C34_BLOCKED_C33_DATA_PATH_REPLAY_NOT_PASS', $result['status']);
        $this->assertSame('WS_BT_C34_C33_DATA_PATH_REPLAY_NOT_PASS', $result['reason_code']);
        $this->cleanup($c32Path, $c33Path, $outputPath);
    }

    public function test_it_blocks_when_C32_source_artifact_is_missing(): void
    {
        [$c32Path, $c33Path, $outputPath] = $this->tempPaths('missing-c32');
        $c32 = $this->c32Artifact();
        $c33 = $this->c33Artifact($c32Path, $c32['artifact_hash']);
        $this->writeJson($c33Path, $c33);

        $result = (new WatchlistBacktestC34BadMonthRobustnessDiagnosticService())->execute(
            $c33Path,
            $c33['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C34_BLOCKED_MISSING_C32_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C34_C32_ARTIFACT_MISSING', $result['reason_code']);
        $this->cleanup($c32Path, $c33Path, $outputPath);
    }

    public function test_it_blocks_when_C32_hash_mismatches_C33_link(): void
    {
        [$c32Path, $c33Path, $outputPath] = $this->tempPaths('c32-hash-mismatch');
        $c32 = $this->c32Artifact();
        $c33 = $this->c33Artifact($c32Path, 'expected-from-c33');
        $this->writeJson($c32Path, $c32);
        $this->writeJson($c33Path, $c33);

        $result = (new WatchlistBacktestC34BadMonthRobustnessDiagnosticService())->execute(
            $c33Path,
            $c33['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C34_BLOCKED_C32_HASH_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C34_C32_ARTIFACT_HASH_MISMATCH', $result['reason_code']);
        $this->assertFalse((bool) $result['c32_hash_match']);
        $this->cleanup($c32Path, $c33Path, $outputPath);
    }

    public function test_it_completes_bad_month_robustness_diagnostic_after_C33_data_path_pass(): void
    {
        [$c32Path, $c33Path, $outputPath] = $this->tempPaths('completed');
        $c32 = $this->c32Artifact();
        $c33 = $this->c33Artifact($c32Path, $c32['artifact_hash']);
        $this->writeJson($c32Path, $c32);
        $this->writeJson($c33Path, $c33);

        $result = (new WatchlistBacktestC34BadMonthRobustnessDiagnosticService())->execute(
            $c33Path,
            $c33['artifact_hash'],
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertSame('C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS', $result['bad_month_robustness_status']);
        $this->assertSame(3, $result['bad_month_failure_count']);
        $this->assertSame(2, $result['branch_robustness_flag_count']);
        $this->assertTrue($result['strategy_robustness_redesign_required']);
        $this->assertSame('C35_IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC_NO_OOS_TUNING', $result['next_step']);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC', $out['run_code']);
        $this->assertSame('C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC', $out['artifact_type']);
        $this->assertFalse($out['production_ready']);
        $this->assertTrue($out['c33_hash_match']);
        $this->assertTrue($out['c32_hash_match']);
        $this->assertSame('C33_DATA_PATH_REPLAY_PASS', $out['c33_data_path_replay_status']);
        $this->assertSame('PASS', $out['c33_data_completeness_gate_after_replay']);
        $this->assertCount(3, $out['bad_month_diagnostic_rows']);
        $this->assertSame(['2025-06', '2025-08', '2026-03'], array_column($out['bad_month_diagnostic_rows'], 'trade_month'));
        $this->assertSame('CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_AFTER_DATA_PATH_CLEARED', $out['bad_month_diagnostic_rows'][0]['bad_month_failure_class']);
        $this->assertSame('CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_AFTER_DATA_PATH_CLEARED', $out['bad_month_diagnostic_rows'][1]['bad_month_failure_class']);
        $this->assertSame('CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED', $out['bad_month_diagnostic_rows'][2]['bad_month_failure_class']);
        $this->assertCount(3, $out['branch_robustness_rows']);
        $branches = array_column($out['branch_robustness_rows'], 'selected_source_code');
        $this->assertSame(['G16', 'G21', 'R09'], $branches);
        $this->assertSame('C34_BRANCH_BAD_MONTH_CONCENTRATION_REVIEW', $out['branch_robustness_rows'][0]['branch_failure_class']);
        $this->assertSame('C34_BRANCH_ROBUSTNESS_FAILURE_CONFIRMED', $out['branch_robustness_rows'][1]['branch_failure_class']);
        $this->assertSame('DATA_PATH_CLEARED_BRANCH_REVIEW_ONLY', $out['branch_robustness_rows'][2]['branch_failure_class']);
        $this->assertSame(['2025-06', '2025-08', '2026-03'], $out['robustness_decision']['bad_months_requiring_review']);
        $this->assertSame(['G16', 'G21'], $out['robustness_decision']['branches_requiring_review']);
        $this->assertFalse($out['robustness_decision']['oos_tuning_allowed']);
        $this->assertFalse($out['robustness_decision']['profile_reselection_allowed']);
        $this->assertFalse($out['robustness_decision']['production_ready']);
        $this->cleanup($c32Path, $c33Path, $outputPath);
    }

    private function c33Artifact(string $c32Path, string $c32Hash): array
    {
        $artifact = [
            'run_code' => 'C33_DATA_PATH_REPLAY_PROOF',
            'status' => 'C33_DATA_PATH_REPLAY_PROOF_COMPLETED',
            'artifact_type' => 'C33_DATA_PATH_REPLAY_PROOF',
            'production_ready' => false,
            'input_c32_artifact' => $c32Path,
            'actual_c32_hash' => $c32Hash,
            'c32_hash_match' => true,
            'c32_status' => 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED',
            'data_path_replay_status' => 'C33_DATA_PATH_REPLAY_PASS',
            'data_completeness_gate_after_replay' => [
                'status' => 'PASS',
                'can_claim_data_completeness_pass' => true,
                'can_claim_oos_pass' => false,
            ],
            'diagnostic_conclusion' => 'C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE',
            'next_step' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_AFTER_C33_NO_OOS_TUNING',
            'replay_summary' => [
                'replay_row_count' => 4,
                'replay_pass_count' => 4,
                'replay_fail_count' => 0,
                'replay_blocked_count' => 0,
                'data_path_replay_status' => 'C33_DATA_PATH_REPLAY_PASS',
            ],
            'replay_rows' => [
                $this->replayRow('2025-06', '2025-06-04', 'MICE', 151),
                $this->replayRow('2025-06', '2025-06-04', 'MICE', 152),
                $this->replayRow('2025-08', '2025-08-15', 'BBSI', 151),
                $this->replayRow('2025-08', '2025-08-15', 'BBSI', 152),
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function replayRow(string $month, string $date, string $ticker, int $paramId): array
    {
        return [
            'trade_month' => $month,
            'trade_date' => $date,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'selected_source_code' => 'R09',
            'raw_ohlc_replay_status' => 'PASS',
        ];
    }

    private function c32Artifact(): array
    {
        $artifact = [
            'run_code' => 'C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC',
            'status' => 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED',
            'artifact_type' => 'C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC',
            'production_ready' => false,
            'source_c31_clean_metrics' => [
                'clean_evaluated_picks_count' => 128,
                'clean_avg_ret_net' => 0.004431048028766952,
                'clean_win_rate' => 0.53125,
                'clean_month_win_rate_min' => 0,
            ],
            'bad_month_robustness_status' => 'C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED',
            'bad_month_robustness_summary' => [
                ['trade_month' => '2025-06', 'total_rows' => 12, 'clean_rows' => 10, 'missing_path_rows' => 2, 'avg_ret_net' => -0.04048987753061734, 'median_ret_net' => -0.04048987753061734, 'p25_ret_net' => -0.04048987753061734, 'win_rate' => 0, 'dominant_branch' => 'G21', 'dominant_ticker' => 'GWSA', 'data_path_affected' => true, 'clean_robustness_failure' => true, 'failure_class' => 'MIXED_DATA_PATH_AND_CLEAN_ROBUSTNESS_FAILURE'],
                ['trade_month' => '2025-08', 'total_rows' => 9, 'clean_rows' => 7, 'missing_path_rows' => 2, 'avg_ret_net' => -0.0064012506567370005, 'median_ret_net' => -0.004781038604343624, 'p25_ret_net' => -0.012842696966362937, 'win_rate' => 0, 'dominant_branch' => 'G21', 'dominant_ticker' => 'SMKL', 'data_path_affected' => true, 'clean_robustness_failure' => true, 'failure_class' => 'MIXED_DATA_PATH_AND_CLEAN_ROBUSTNESS_FAILURE'],
                ['trade_month' => '2026-03', 'total_rows' => 4, 'clean_rows' => 4, 'missing_path_rows' => 0, 'avg_ret_net' => -0.006991928435556013, 'median_ret_net' => -0.006991928435556013, 'p25_ret_net' => -0.006991928435556013, 'win_rate' => 0, 'dominant_branch' => 'G16', 'dominant_ticker' => 'BINA', 'data_path_affected' => false, 'clean_robustness_failure' => true, 'failure_class' => 'CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE'],
            ],
            'source_branch_robustness_summary' => [
                ['selected_source_code' => 'G16', 'count' => 18, 'clean_count' => 18, 'missing_count' => 0, 'non_evaluable_count' => 0, 'avg_ret_net' => 0.00737983091926925, 'median_ret_net' => 0.0052763819095477385, 'p25_ret_net' => -0.0004998750312421895, 'win_rate' => 0.6111111111111112, 'bad_month_contribution' => ['2025-08' => 3, '2026-03' => 4], 'bad_month_contribution_count' => 7, 'clean_bad_month_contribution_count' => 7, 'data_path_affected' => false, 'robustness_diagnostic_flag' => true, 'failure_class' => 'CLEAN_BRANCH_ROBUSTNESS_REVIEW'],
                ['selected_source_code' => 'G21', 'count' => 80, 'clean_count' => 80, 'missing_count' => 0, 'non_evaluable_count' => 0, 'avg_ret_net' => -0.007043371221106404, 'median_ret_net' => -0.0005005957088935833, 'p25_ret_net' => -0.022466611470900857, 'win_rate' => 0.3375, 'bad_month_contribution' => ['2025-06' => 10, '2025-08' => 4], 'bad_month_contribution_count' => 14, 'clean_bad_month_contribution_count' => 14, 'data_path_affected' => false, 'robustness_diagnostic_flag' => true, 'failure_class' => 'CLEAN_BRANCH_ROBUSTNESS_REVIEW'],
                ['selected_source_code' => 'R09', 'count' => 34, 'clean_count' => 30, 'missing_count' => 4, 'non_evaluable_count' => 4, 'avg_ret_net' => 0.03326022962746119, 'median_ret_net' => 0.02559142636386369, 'p25_ret_net' => 0.006607369758576874, 'win_rate' => 1, 'bad_month_contribution' => ['2025-06' => 2, '2025-08' => 2], 'bad_month_contribution_count' => 4, 'clean_bad_month_contribution_count' => 0, 'data_path_affected' => true, 'robustness_diagnostic_flag' => false, 'failure_class' => 'DATA_PATH_AFFECTED_BRANCH'],
            ],
            'diagnostic_conclusion' => 'C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
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
        $c32Path = sys_get_temp_dir().'/c34-'.$name.'-c32.json';
        $c33Path = sys_get_temp_dir().'/c34-'.$name.'-c33.json';
        $outputPath = sys_get_temp_dir().'/c34-'.$name.'-output.json';
        $this->cleanup($c32Path, $c33Path, $outputPath);
        return [$c32Path, $c33Path, $outputPath];
    }

    private function cleanup(string ...$files): void
    {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
