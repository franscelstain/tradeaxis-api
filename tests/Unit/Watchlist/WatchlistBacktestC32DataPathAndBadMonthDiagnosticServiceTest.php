<?php

use App\Application\Watchlist\Services\WatchlistBacktestC32DataPathAndBadMonthDiagnosticService;

class WatchlistBacktestC32DataPathAndBadMonthDiagnosticServiceTest extends TestCase
{
    public function test_it_blocks_when_C31_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c32-missing-c31-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC32DataPathAndBadMonthDiagnosticService())->execute(
            sys_get_temp_dir().'/missing-c31-artifact.json',
            WatchlistBacktestC32DataPathAndBadMonthDiagnosticService::DEFAULT_EXPECTED_C31_HASH,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C32_BLOCKED_MISSING_C31_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C32_C31_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C32_BLOCKED_MISSING_C31_ARTIFACT', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertSame('C32_DIAGNOSTIC_BLOCKED', $artifact['diagnostic_conclusion']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C31_hash_mismatches(): void
    {
        [$c29Path, $c31Path, $outputPath] = $this->tempPaths('hash-mismatch');
        $c29 = $this->c29Artifact();
        $c31 = $this->c31Artifact($c29Path, $c29['artifact_hash']);
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c31Path, $c31);

        $result = (new WatchlistBacktestC32DataPathAndBadMonthDiagnosticService())->execute(
            $c31Path,
            'wrong-c31-hash',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C32_BLOCKED_C31_HASH_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C32_C31_ARTIFACT_HASH_MISMATCH', $result['reason_code']);
        $this->assertFalse((bool) $result['c31_hash_match']);
        $this->cleanup($c29Path, $c31Path, $outputPath);
    }

    public function test_it_blocks_when_C31_status_is_unexpected(): void
    {
        [$c29Path, $c31Path, $outputPath] = $this->tempPaths('status');
        $c29 = $this->c29Artifact();
        $c31 = $this->c31Artifact($c29Path, $c29['artifact_hash']);
        $c31['status'] = 'C31_OPERATOR_VALIDATION_REQUIRED';
        $c31['artifact_hash'] = $this->stableHash($c31);
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c31Path, $c31);

        $result = (new WatchlistBacktestC32DataPathAndBadMonthDiagnosticService())->execute(
            $c31Path,
            $c31['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C32_BLOCKED_UNEXPECTED_C31_STATUS', $result['status']);
        $this->assertSame('WS_BT_C32_UNEXPECTED_C31_STATUS', $result['reason_code']);
        $this->cleanup($c29Path, $c31Path, $outputPath);
    }

    public function test_it_blocks_when_C31_conclusion_is_unexpected(): void
    {
        [$c29Path, $c31Path, $outputPath] = $this->tempPaths('conclusion');
        $c29 = $this->c29Artifact();
        $c31 = $this->c31Artifact($c29Path, $c29['artifact_hash']);
        $c31['reclassification_conclusion'] = 'C31_RECLASSIFICATION_ACTUAL_LOOKAHEAD_LEAK_CONFIRMED';
        $c31['artifact_hash'] = $this->stableHash($c31);
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c31Path, $c31);

        $result = (new WatchlistBacktestC32DataPathAndBadMonthDiagnosticService())->execute(
            $c31Path,
            $c31['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C32_BLOCKED_UNEXPECTED_C31_CONCLUSION', $result['status']);
        $this->assertSame('WS_BT_C32_UNEXPECTED_C31_CONCLUSION', $result['reason_code']);
        $this->cleanup($c29Path, $c31Path, $outputPath);
    }

    public function test_it_blocks_when_C31_proof_status_is_unexpected(): void
    {
        [$c29Path, $c31Path, $outputPath] = $this->tempPaths('proof-status');
        $c29 = $this->c29Artifact();
        $c31 = $this->c31Artifact($c29Path, $c29['artifact_hash']);
        $c31['controlled_proof_status'] = 'C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS';
        $c31['artifact_hash'] = $this->stableHash($c31);
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c31Path, $c31);

        $result = (new WatchlistBacktestC32DataPathAndBadMonthDiagnosticService())->execute(
            $c31Path,
            $c31['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C32_BLOCKED_UNEXPECTED_C31_PROOF_STATUS', $result['status']);
        $this->assertSame('WS_BT_C32_UNEXPECTED_C31_PROOF_STATUS', $result['reason_code']);
        $this->cleanup($c29Path, $c31Path, $outputPath);
    }

    public function test_it_completes_diagnostic_split_for_valid_C31_artifact(): void
    {
        [$c29Path, $c31Path, $outputPath] = $this->tempPaths('completed');
        $c29 = $this->c29Artifact();
        $c31 = $this->c31Artifact($c29Path, $c29['artifact_hash']);
        $this->writeJson($c29Path, $c29);
        $this->writeJson($c31Path, $c31);

        $result = (new WatchlistBacktestC32DataPathAndBadMonthDiagnosticService())->execute(
            $c31Path,
            $c31['artifact_hash'],
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertSame('C32_DATA_PATH_REMEDIATION_REQUIRED', $result['data_path_remediation_status']);
        $this->assertSame('C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED', $result['bad_month_robustness_status']);
        $this->assertSame('C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED', $result['diagnostic_conclusion']);
        $this->assertSame('C33_DATA_PATH_REPLAY_PROOF_THEN_C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_NO_OOS_TUNING', $result['next_step']);
        $this->assertTrue($result['split_decision']['data_path_remediation_required']);
        $this->assertTrue($result['split_decision']['bad_month_robustness_diagnostic_required']);
        $this->assertFalse($result['split_decision']['actual_lookahead_fix_required']);
        $this->assertFalse($result['split_decision']['selection_leak_fix_required']);
        $this->assertFalse($result['split_decision']['oos_tuning_allowed']);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC', $out['run_code']);
        $this->assertSame('C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC', $out['artifact_type']);
        $this->assertFalse($out['production_ready']);
        $this->assertSame(4, $out['data_path_remediation_scope']['missing_path_count']);
        $this->assertSame(['2025-06-04', '2025-08-15'], $out['data_path_remediation_scope']['affected_trade_dates']);
        $this->assertSame(['2025-06-05', '2025-08-19'], $out['data_path_remediation_scope']['affected_entry_dates']);
        $this->assertSame(['BBSI', 'MICE'], $out['data_path_remediation_scope']['affected_tickers']);
        $this->assertSame([151, 152], $out['data_path_remediation_scope']['affected_param_ids']);
        $this->assertSame(['R09'], $out['data_path_remediation_scope']['affected_source_codes']);
        $this->assertSame(4, $out['data_path_remediation_scope']['missing_path_reason_counts']['WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING']);
        $this->assertCount(4, $out['missing_path_replay_rows']);
        $this->assertSame('2025-06-05', $out['missing_path_replay_rows'][0]['entry_date']);
        $this->assertSame('D1_TO_D5_RAW_OHLC_PATH', $out['missing_path_replay_rows'][0]['required_path_scope']);
        $this->assertSame(['2025-06', '2025-08', '2026-03'], array_column($out['bad_month_robustness_summary'], 'trade_month'));
        $this->assertContains('MIXED_DATA_PATH_AND_CLEAN_ROBUSTNESS_FAILURE', array_column($out['bad_month_robustness_summary'], 'failure_class'));
        $this->assertContains('CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE', array_column($out['bad_month_robustness_summary'], 'failure_class'));
        $branches = array_column($out['source_branch_robustness_summary'], 'selected_source_code');
        $this->assertSame(['G16', 'G21', 'R09'], $branches);
        $this->assertContains('DATA_PATH_AFFECTED_BRANCH', array_column($out['source_branch_robustness_summary'], 'failure_class'));
        $this->cleanup($c29Path, $c31Path, $outputPath);
    }

    private function c31Artifact(string $c29Path, string $c29Hash): array
    {
        $artifact = [
            'run_code' => 'C31_CONTROLLED_GATE_RECLASSIFICATION',
            'status' => 'C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED',
            'artifact_type' => 'C31_CONTROLLED_GATE_RECLASSIFICATION',
            'production_ready' => false,
            'input_c29_artifact' => $c29Path,
            'actual_c29_hash' => $c29Hash,
            'reclassification_conclusion' => 'C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK',
            'controlled_proof_status' => 'C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS',
            'source_c30_classification_summary' => [
                'total_oos_pick_rows' => 132,
                'reported_lookahead_violation_count' => 4,
                'actual_lookahead_violation_count' => 0,
                'selection_leak_count' => 0,
                'missing_path_count' => 4,
                'non_evaluable_pick_count' => 4,
                'clean_evaluable_pick_count' => 128,
            ],
            'source_c30_clean_metrics' => [
                'clean_evaluated_picks_count' => 128,
                'clean_avg_ret_net' => 0.004431048028766952,
                'clean_median_ret_net' => 0.0052763819095477385,
                'clean_p25_ret_net' => -0.007561518832148093,
                'clean_win_rate' => 0.53125,
                'clean_month_win_rate_min' => 0,
                'clean_month_avg_ret_net_min' => -0.04048987753061734,
            ],
            'separated_gate_summary' => [
                'actual_lookahead_gate' => ['status' => 'PASS'],
                'selection_leak_gate' => ['status' => 'PASS'],
                'data_completeness_gate' => ['status' => 'FAIL'],
                'month_win_rate_gate' => ['status' => 'FAIL'],
                'clean_month_win_rate_gate' => ['status' => 'FAIL'],
            ],
            'bad_month_summary' => [
                ['trade_month' => '2025-06', 'total_rows' => 12, 'clean_rows' => 10, 'missing_path_rows' => 2, 'avg_ret_net' => -0.04048987753061734, 'median_ret_net' => -0.04048987753061734, 'p25_ret_net' => -0.04048987753061734, 'win_rate' => 0, 'dominant_branch' => 'G21', 'dominant_ticker' => 'GWSA'],
                ['trade_month' => '2025-08', 'total_rows' => 9, 'clean_rows' => 7, 'missing_path_rows' => 2, 'avg_ret_net' => -0.0064012506567370005, 'median_ret_net' => -0.004781038604343624, 'p25_ret_net' => -0.012842696966362937, 'win_rate' => 0, 'dominant_branch' => 'G21', 'dominant_ticker' => 'SMKL'],
                ['trade_month' => '2026-03', 'total_rows' => 4, 'clean_rows' => 4, 'missing_path_rows' => 0, 'avg_ret_net' => -0.006991928435556013, 'median_ret_net' => -0.006991928435556013, 'p25_ret_net' => -0.006991928435556013, 'win_rate' => 0, 'dominant_branch' => 'G16', 'dominant_ticker' => 'BINA'],
            ],
            'source_branch_summary' => [
                ['selected_source_code' => 'G16', 'count' => 18, 'clean_count' => 18, 'missing_count' => 0, 'non_evaluable_count' => 0, 'avg_ret_net' => 0.00737983091926925, 'median_ret_net' => 0.0052763819095477385, 'p25_ret_net' => -0.0004998750312421895, 'win_rate' => 0.6111111111111112, 'bad_month_contribution' => ['2025-08' => 3, '2026-03' => 4]],
                ['selected_source_code' => 'G21', 'count' => 80, 'clean_count' => 80, 'missing_count' => 0, 'non_evaluable_count' => 0, 'avg_ret_net' => -0.007043371221106404, 'median_ret_net' => -0.0005005957088935833, 'p25_ret_net' => -0.022466611470900857, 'win_rate' => 0.3375, 'bad_month_contribution' => ['2025-06' => 10, '2025-08' => 4]],
                ['selected_source_code' => 'R09', 'count' => 34, 'clean_count' => 30, 'missing_count' => 4, 'non_evaluable_count' => 4, 'avg_ret_net' => 0.03326022962746119, 'median_ret_net' => 0.02559142636386369, 'p25_ret_net' => 0.006607369758576874, 'win_rate' => 1, 'bad_month_contribution' => ['2025-06' => 2, '2025-08' => 2]],
            ],
            'missing_path_rows' => [
                $this->missingPathRow('2025-06', '2025-06-04', 'MICE', 151, '06_VOL_150_250_LOW_ATR_NEG_ROC20'),
                $this->missingPathRow('2025-06', '2025-06-04', 'MICE', 152, '07_VOL_150_250_ONE_R_LOW_ATR'),
                $this->missingPathRow('2025-08', '2025-08-15', 'BBSI', 151, '06_VOL_150_250_LOW_ATR_NEG_ROC20'),
                $this->missingPathRow('2025-08', '2025-08-15', 'BBSI', 152, '07_VOL_150_250_ONE_R_LOW_ATR'),
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function c29Artifact(): array
    {
        $rows = [
            $this->c29MissingRow('2025-06', '2025-06-04', '2025-06-05', 'MICE', 551, 151, '06_VOL_150_250_LOW_ATR_NEG_ROC20'),
            $this->c29MissingRow('2025-06', '2025-06-04', '2025-06-05', 'MICE', 551, 152, '07_VOL_150_250_ONE_R_LOW_ATR'),
            $this->c29MissingRow('2025-08', '2025-08-15', '2025-08-19', 'BBSI', 100, 151, '06_VOL_150_250_LOW_ATR_NEG_ROC20'),
            $this->c29MissingRow('2025-08', '2025-08-15', '2025-08-19', 'BBSI', 100, 152, '07_VOL_150_250_ONE_R_LOW_ATR'),
        ];
        $artifact = [
            'run_code' => 'C29_OOS_PROOF_C28_G05',
            'status' => 'C29_OOS_PROOF_FAILED',
            'artifact_type' => 'C29_OOS_PROOF',
            'production_ready' => false,
            'oos_pick_rows' => $rows,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function missingPathRow(string $month, string $date, string $ticker, int $paramId, string $rowCode): array
    {
        return [
            'trade_month' => $month,
            'trade_date' => $date,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'row_code' => $rowCode,
            'selected_source_code' => 'R09',
            'missing_path_reason_code' => 'WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING',
            'raw_ohlc_validated_flag' => false,
            'missing_path_data_flag' => true,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function c29MissingRow(string $month, string $date, string $entryDate, string $ticker, int $tickerId, int $paramId, string $rowCode): array
    {
        return array_replace($this->missingPathRow($month, $date, $ticker, $paramId, $rowCode), [
            'ticker_id' => $tickerId,
            'entry_date' => $entryDate,
            'selected_source_reason' => 'r09_default_non_primary_bucket',
        ]);
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
        $c29Path = sys_get_temp_dir().'/c32-'.$name.'-c29.json';
        $c31Path = sys_get_temp_dir().'/c32-'.$name.'-c31.json';
        $outputPath = sys_get_temp_dir().'/c32-'.$name.'-output.json';
        $this->cleanup($c29Path, $c31Path, $outputPath);
        return [$c29Path, $c31Path, $outputPath];
    }

    private function cleanup(string ...$files): void
    {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
