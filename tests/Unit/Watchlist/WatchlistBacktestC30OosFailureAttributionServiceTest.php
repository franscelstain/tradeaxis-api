<?php

use App\Application\Watchlist\Services\WatchlistBacktestC30OosFailureAttributionService;

class WatchlistBacktestC30OosFailureAttributionServiceTest extends TestCase
{
    public function test_it_blocks_when_C29_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c30-missing-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC30OosFailureAttributionService())->execute(
            sys_get_temp_dir().'/missing-c29-artifact.json',
            WatchlistBacktestC30OosFailureAttributionService::DEFAULT_EXPECTED_C29_HASH,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-19T00:00:00+00:00']
        );

        $this->assertSame('C30_BLOCKED_MISSING_C29_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C30_C29_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C30_BLOCKED_MISSING_C29_ARTIFACT', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertSame('INSUFFICIENT_DIAGNOSTIC_DATA', $artifact['attribution_verdict']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C29_hash_mismatches(): void
    {
        [$c29Path, $outputPath] = $this->tempPaths('hash-mismatch');
        $artifact = $this->c29Artifact($this->baseRows());
        file_put_contents($c29Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC30OosFailureAttributionService())->execute(
            $c29Path,
            'wrong-hash',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C30_BLOCKED_C29_HASH_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C30_C29_ARTIFACT_HASH_MISMATCH', $result['reason_code']);
        $this->assertFalse((bool) $result['c29_hash_match']);
        $this->cleanup($c29Path, $outputPath);
    }

    public function test_it_blocks_when_C29_status_is_not_failed(): void
    {
        [$c29Path, $outputPath] = $this->tempPaths('unexpected-status');
        $artifact = $this->c29Artifact($this->baseRows());
        $artifact['status'] = 'C29_OOS_PROOF_PASSED_NOT_PRODUCTION_READY';
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        file_put_contents($c29Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC30OosFailureAttributionService())->execute(
            $c29Path,
            $artifact['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C30_BLOCKED_UNEXPECTED_C29_STATUS', $result['status']);
        $this->assertSame('WS_BT_C30_UNEXPECTED_C29_STATUS', $result['reason_code']);
        $this->cleanup($c29Path, $outputPath);
    }

    public function test_it_completes_diagnostic_for_valid_failed_C29_artifact_and_writes_C30_artifact(): void
    {
        [$c29Path, $outputPath] = $this->tempPaths('completed');
        $artifact = $this->c29Artifact($this->baseRows());
        file_put_contents($c29Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC30OosFailureAttributionService())->execute(
            $c29Path,
            $artifact['artifact_hash'],
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-19T00:00:00+00:00']
        );

        $this->assertSame('C30_ATTRIBUTION_COMPLETED', $result['status']);
        $this->assertSame('MIXED_DATA_AND_STRATEGY_FAILURE', $result['attribution_verdict']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertFileExists($outputPath);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C30_OOS_FAILURE_ATTRIBUTION', $out['run_code']);
        $this->assertSame('C30_OOS_FAILURE_ATTRIBUTION', $out['artifact_type']);
        $this->assertSame('C29_OOS_PROOF_FAILED', $out['c29_status']);
        $this->assertTrue($out['c29_hash_match']);
        $this->assertFalse($out['production_ready']);
        $this->assertSame(4, $out['classification_summary']['total_oos_pick_rows']);
        $this->assertSame(1, $out['classification_summary']['reported_lookahead_violation_count']);
        $this->assertSame(0, $out['classification_summary']['actual_lookahead_violation_count']);
        $this->assertSame(0, $out['classification_summary']['selection_leak_count']);
        $this->assertSame(1, $out['classification_summary']['missing_path_count']);
        $this->assertSame(1, $out['classification_summary']['non_evaluable_pick_count']);
        $this->assertSame(3, $out['classification_summary']['clean_evaluable_pick_count']);
        $this->cleanup($c29Path, $outputPath);
    }

    public function test_missing_OHLC_path_rows_are_not_counted_as_actual_lookahead_violations(): void
    {
        [$c29Path, $outputPath] = $this->tempPaths('missing-path');
        $artifact = $this->c29Artifact([$this->missingPathRow()]);
        file_put_contents($c29Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        (new WatchlistBacktestC30OosFailureAttributionService())->execute($c29Path, $artifact['artifact_hash'], $outputPath, ['overwrite' => true]);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertCount(1, $out['missing_path_rows']);
        $this->assertCount(0, $out['actual_lookahead_violation_rows']);
        $this->assertSame('WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING', $out['missing_path_rows'][0]['missing_path_reason_code']);
        $this->cleanup($c29Path, $outputPath);
    }

    public function test_selection_leak_rows_are_detected_from_each_forbidden_flag(): void
    {
        [$c29Path, $outputPath] = $this->tempPaths('selection-leak');
        $rows = [
            $this->cleanRow('2025-06-11', 'LEAK1', 'R09', 0.01, ['future_path_price_used_for_selection' => true]),
            $this->cleanRow('2025-06-12', 'LEAK2', 'G21', 0.01, ['profile_ret_net_used_for_selection' => true]),
            $this->cleanRow('2025-06-13', 'LEAK3', 'G16', 0.01, ['derived_mfe_mae_used_for_execution' => true]),
        ];
        $artifact = $this->c29Artifact($rows);
        file_put_contents($c29Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        (new WatchlistBacktestC30OosFailureAttributionService())->execute($c29Path, $artifact['artifact_hash'], $outputPath, ['overwrite' => true]);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame(3, $out['classification_summary']['selection_leak_count']);
        $this->assertCount(3, $out['selection_leak_rows']);
        $this->assertSame('ACTUAL_LOOKAHEAD_LEAK_CONFIRMED', $out['attribution_verdict']);
        $this->cleanup($c29Path, $outputPath);
    }

    public function test_clean_metrics_only_count_clean_evaluable_rows(): void
    {
        [$c29Path, $outputPath] = $this->tempPaths('clean-metrics');
        $artifact = $this->c29Artifact($this->baseRows());
        file_put_contents($c29Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        (new WatchlistBacktestC30OosFailureAttributionService())->execute($c29Path, $artifact['artifact_hash'], $outputPath, ['overwrite' => true]);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame(3, $out['clean_metrics']['clean_evaluated_picks_count']);
        $this->assertEqualsWithDelta(-0.006666666666666666, $out['clean_metrics']['clean_avg_ret_net'], 0.0000000001);
        $this->assertSame(-0.01, $out['clean_metrics']['clean_median_ret_net']);
        $this->assertSame(-0.02, $out['clean_metrics']['clean_p25_ret_net']);
        $this->assertEqualsWithDelta(1 / 3, $out['clean_metrics']['clean_win_rate'], 0.0000000001);
        $this->cleanup($c29Path, $outputPath);
    }

    public function test_bad_month_summary_reads_trade_month_not_month(): void
    {
        [$c29Path, $outputPath] = $this->tempPaths('bad-month');
        $artifact = $this->c29Artifact($this->baseRows());
        $artifact['metrics']['month_summary'] = [[
            'month' => 'WRONG-MONTH',
            'trade_month' => '2025-08',
            'evaluated_picks_count' => 1,
            'avg_ret_net' => -0.03,
            'win_rate' => 0,
        ]];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        file_put_contents($c29Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        (new WatchlistBacktestC30OosFailureAttributionService())->execute($c29Path, $artifact['artifact_hash'], $outputPath, ['overwrite' => true]);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $months = array_column($out['bad_month_summary'], 'trade_month');
        $this->assertContains('2025-08', $months);
        $this->assertNotContains('WRONG-MONTH', $months);
        $this->cleanup($c29Path, $outputPath);
    }

    public function test_source_branch_summary_separates_R09_G21_G16(): void
    {
        [$c29Path, $outputPath] = $this->tempPaths('branch-summary');
        $artifact = $this->c29Artifact($this->baseRows());
        file_put_contents($c29Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        (new WatchlistBacktestC30OosFailureAttributionService())->execute($c29Path, $artifact['artifact_hash'], $outputPath, ['overwrite' => true]);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $branches = array_column($out['source_branch_summary'], 'selected_source_code');
        sort($branches, SORT_STRING);
        $this->assertSame(['G16', 'G21', 'R09'], $branches);
        $this->cleanup($c29Path, $outputPath);
    }

    public function test_it_does_not_create_best_of_oos_or_reselect_profile(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC30OosFailureAttributionService.php'));

        $this->assertStringContainsString('FAILURE_ATTRIBUTION_ONLY', $service);
        $this->assertStringContainsString('NO_RETUNE', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('oos_return_used_for_profile_selection', $service);
        $this->assertStringNotContainsString('best_profile_binding_allowed', $service);
        $this->assertStringNotContainsString('best_profile_code_by_avg', $service);
        $this->assertStringNotContainsString('best_profile_code_by_median', $service);
        $this->assertStringNotContainsString('best_of_oos_profile', strtolower($service));
        $this->assertStringNotContainsString('best_profile_from_oos', strtolower($service));
    }

    private function c29Artifact(array $rows): array
    {
        $artifact = [
            'run_code' => 'C29_OOS_PROOF_C28_G05',
            'status' => 'C29_OOS_PROOF_FAILED',
            'artifact_type' => 'C29_OOS_PROOF',
            'production_ready' => false,
            'metrics' => [
                'evaluated_picks_count' => count(array_filter($rows, function (array $row): bool {
                    return ($row['missing_path_data_flag'] ?? false) !== true && is_numeric($row['profile_ret_net'] ?? null);
                })),
                'avg_ret_net' => null,
                'median_ret_net' => null,
                'p25_ret_net' => null,
                'win_rate' => null,
                'month_win_rate_min' => 0,
                'month_avg_ret_net_min' => -0.03,
                'month_summary' => [[
                    'trade_month' => '2025-06',
                    'evaluated_picks_count' => 1,
                    'avg_ret_net' => -0.03,
                    'win_rate' => 0,
                ]],
            ],
            'lookahead_violation_count' => count(array_filter($rows, function (array $row): bool {
                return ($row['lookahead_safe'] ?? true) !== true;
            })),
            'oos_pick_rows' => $rows,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function baseRows(): array
    {
        return [
            $this->missingPathRow(),
            $this->cleanRow('2025-06-24', 'GWSA', 'G21', -0.03, ['bucket_code' => 'no_rule_profit_signal_before_fallback']),
            $this->cleanRow('2025-08-13', 'MDKI', 'R09', 0.02, ['bucket_code' => 'candidate_matches_or_beats_c22']),
            $this->cleanRow('2026-03-02', 'BINA', 'G16', -0.01, ['bucket_code' => 'next_open_delay_after_close_signal']),
        ];
    }

    private function missingPathRow(): array
    {
        return [
            'trade_date' => '2025-06-04',
            'trade_month' => '2025-06',
            'ticker_id' => 551,
            'ticker' => 'MICE',
            'param_id' => 151,
            'row_code' => '06_VOL_150_250_LOW_ATR_NEG_ROC20',
            'entry_date' => '2025-06-05',
            'entry_price' => null,
            'bucket_code' => null,
            'profile_code' => 'C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY',
            'selected_source_code' => 'R09',
            'profile_ret_net' => null,
            'win_flag' => false,
            'raw_ohlc_validated_flag' => false,
            'lookahead_safe' => false,
            'lookahead_violation_reason' => null,
            'missing_path_data_flag' => true,
            'missing_path_reason_code' => 'WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING',
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'production_ready' => 0,
        ];
    }

    private function cleanRow(string $date, string $ticker, string $branch, float $ret, array $extra = []): array
    {
        $row = [
            'trade_date' => $date,
            'trade_month' => substr($date, 0, 7),
            'ticker_id' => crc32($ticker) % 10000,
            'ticker' => $ticker,
            'param_id' => 145,
            'row_code' => 'FIXTURE',
            'entry_date' => $date,
            'entry_price' => 100,
            'bucket_code' => 'fixture',
            'profile_code' => 'C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY',
            'selected_source_code' => $branch,
            'profile_ret_net' => $ret,
            'win_flag' => $ret > 0,
            'raw_ohlc_validated_flag' => true,
            'lookahead_safe' => true,
            'lookahead_violation_reason' => null,
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'production_ready' => 0,
        ];
        return array_replace($row, $extra);
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash']);
        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES));
    }

    private function tempPaths(string $name): array
    {
        $c29Path = sys_get_temp_dir().'/c30-'.$name.'-c29.json';
        $outputPath = sys_get_temp_dir().'/c30-'.$name.'-output.json';
        $this->cleanup($c29Path, $outputPath);
        return [$c29Path, $outputPath];
    }

    private function cleanup(string ...$files): void
    {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
