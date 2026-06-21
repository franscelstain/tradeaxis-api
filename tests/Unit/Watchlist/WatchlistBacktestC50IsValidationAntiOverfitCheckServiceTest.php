<?php

use App\Application\Watchlist\Services\WatchlistBacktestC50IsValidationAntiOverfitCheckService;

class WatchlistBacktestC50IsValidationAntiOverfitCheckServiceTest extends TestCase
{
    public function test_it_blocks_missing_C49_artifact(): void
    {
        $output = $this->path('missing-output.json');
        $result = (new WatchlistBacktestC50IsValidationAntiOverfitCheckService())->execute($this->path('missing-c49.json'), 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C50_BLOCKED_MISSING_C49_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        @unlink($output);
    }

    public function test_it_blocks_expected_C49_hash_mismatch(): void
    {
        [$c49, $output] = $this->writeC49Fixture('hash-mismatch');
        $result = (new WatchlistBacktestC50IsValidationAntiOverfitCheckService())->execute($c49, 'wrong-hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'pre_trade_source_rows' => $this->preTradeRows()]);
        $this->assertSame('C50_BLOCKED_C49_HASH_MISMATCH', $result['status']);
        $this->cleanup($c49, $output);
    }

    public function test_it_blocks_invalid_C49_boundary_inputs(): void
    {
        $cases = [
            ['status', 'C49_NOT_COMPLETED', 'C50_BLOCKED_UNEXPECTED_C49_STATUS'],
            ['diagnostic_conclusion', 'C49_RANDOM_CONCLUSION', 'C50_BLOCKED_UNEXPECTED_C49_CONCLUSION'],
            ['production_ready', true, 'C50_BLOCKED_C49_PRODUCTION_READY_NOT_FALSE'],
            ['next_step_recommendation', 'C51_DIRECT_OOS_PROOF', 'C50_BLOCKED_C49_NEXT_STEP_UNEXPECTED'],
            ['c50_readiness_decision.direct_oos_proof_recommended', true, 'C50_BLOCKED_C49_OOS_PROOF_FLAG_INVALID'],
            ['c50_readiness_decision.oos_proof_unlocked', true, 'C50_BLOCKED_C49_OOS_PROOF_FLAG_INVALID'],
            ['selected_c49_candidates_for_c50.primary_candidate', '', 'C50_BLOCKED_MISSING_C49_PRIMARY_CANDIDATE'],
        ];

        foreach ($cases as $index => $case) {
            [$c49, $output] = $this->writeC49Fixture('boundary-'.$index);
            $artifact = json_decode((string) file_get_contents($c49), true);
            $this->setNested($artifact, $case[0], $case[1]);
            $artifact['artifact_hash'] = $this->stableHash($artifact);
            $this->writeJson($c49, $artifact);
            $result = (new WatchlistBacktestC50IsValidationAntiOverfitCheckService())->execute($c49, $artifact['artifact_hash'], '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'pre_trade_source_rows' => $this->preTradeRows()]);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c49, $output);
        }
    }

    public function test_it_blocks_when_validation_period_touches_OOS_reserved_window(): void
    {
        [$c49, $output] = $this->writeC49Fixture('oos-period');
        $result = (new WatchlistBacktestC50IsValidationAntiOverfitCheckService())->execute($c49, $this->hashFromFile($c49), '2025-05-22', '2025-06-30', $output, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'pre_trade_source_rows' => $this->preTradeRows()]);
        $this->assertSame('C50_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c49, $output);
    }

    public function test_valid_C49_artifact_completes_C50_IS_validation_without_OOS_tuning(): void
    {
        [$c49, $output] = $this->writeC49Fixture('completed');
        $result = (new WatchlistBacktestC50IsValidationAntiOverfitCheckService())->execute($c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, [
            'overwrite' => true,
            'executed_at' => '2026-06-21T00:00:00+00:00',
            'source_rows' => $this->sourceRows(),
            'pre_trade_source_rows' => $this->preTradeRows(),
        ]);

        $this->assertSame('C50_IS_VALIDATION_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertFileExists($output);

        $out = json_decode((string) file_get_contents($output), true);
        $this->assertSame('C50_IS_VALIDATION_ANTI_OVERFIT_CHECK', $out['run_code']);
        $this->assertSame('C50_IS_VALIDATION_ANTI_OVERFIT_CHECK', $out['artifact_type']);
        $this->assertTrue($out['c49_hash_match']);
        $this->assertSame('C49_BROADER_STRATEGY_REDESIGN_COMPLETED', $out['c49_status']);
        $this->assertSame('C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION', $out['c49_diagnostic_conclusion']);
        $this->assertSame('C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN', $out['c49_next_step_recommendation']);
        $this->assertFalse($out['production_ready']);
        $this->assertSame('is_validation_and_anti_overfit_check', $out['is_validation_period']['purpose']);
        $this->assertSame('2023-01-02', $out['is_validation_period']['from']);
        $this->assertSame('2025-05-21', $out['is_validation_period']['to']);
        $this->assertFalse($out['is_validation_period']['oos_data_used_for_tuning']);
        $this->assertFalse($out['is_validation_period']['oos_return_used_for_selection']);
        $this->assertFalse($out['is_validation_period']['oos_proof_executed']);
        $this->assertFalse($out['oos_reserved_period']['used_for_selection']);
        $this->assertFalse($out['oos_reserved_period']['used_for_tuning']);
        $this->assertFalse($out['oos_reserved_period']['used_for_proof']);
        $this->assertTrue($out['c49_carry_forward_summary']['c49_used_as_locked_candidate_source']);
        $this->assertNotEmpty($out['source_reconstruction_summary']);
        $this->assertNotEmpty($out['locked_candidate_replay_results']);
        $this->assertNotEmpty($out['rolling_validation_results']);
        $this->assertNotEmpty($out['rolling_validation_summary']);
        $this->assertNotEmpty($out['leave_one_month_out_results']);
        $this->assertNotEmpty($out['leave_one_month_out_summary']);
        $this->assertNotEmpty($out['regime_robustness_validation_results']);
        $this->assertNotEmpty($out['regime_robustness_validation_summary']);
        $this->assertNotEmpty($out['concentration_dependency_validation_results']);
        $this->assertNotEmpty($out['branch_dependency_validation_results']);
        $this->assertNotEmpty($out['material_difference_validation']);
        $this->assertNotEmpty($out['source_reconstruction_bias_check']);
        $this->assertNotEmpty($out['candidate_validation_scorecard']);
        $this->assertNotEmpty($out['selected_c50_candidates_for_c51']);
        $this->assertNotEmpty($out['c51_readiness_decision']);
        $this->assertNotEmpty($out['candidate_safety_audit']);
        $this->assertIsArray($out['not_evaluable_reasons']);
        $this->assertNotEmpty($out['diagnostic_conclusion']);
        $this->assertFalse($out['c51_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($out['c51_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($out['c51_readiness_decision']['production_ready']);
        $this->assertFalse($out['safety_boundaries']['oos_data_used_for_tuning']);
        $this->assertFalse($out['safety_boundaries']['oos_return_used_for_candidate_selection']);
        $this->assertFalse($out['safety_boundaries']['return_used_for_selection']);
        $this->assertFalse($out['safety_boundaries']['future_path_used_for_selection']);
        $this->assertTrue($out['safety_boundaries']['no_oos_proof']);
        $this->assertTrue($out['safety_boundaries']['no_production_catalog']);

        foreach ($out['candidate_validation_scorecard'] as $candidate) {
            $this->assertFalse($candidate['production_ready']);
            $this->assertFalse($candidate['direct_oos_proof_recommended']);
            $this->assertFalse($candidate['oos_proof_unlocked']);
            $this->assertNotContains('profile_ret_net', $candidate['safe_pre_trade_fields_used']);
            $this->assertNotContains('profile_exit_price', $candidate['safe_pre_trade_fields_used']);
        }
        foreach ($out['candidate_safety_audit'] as $audit) {
            $this->assertTrue($audit['passed']);
            $this->assertFalse($audit['return_used_for_selection']);
            $this->assertFalse($audit['future_path_used_for_selection']);
            $this->assertFalse($audit['oos_data_used_for_tuning']);
        }
        $this->cleanup($c49, $output);
    }

    public function test_source_universe_or_not_evaluable_reason_is_recorded_when_source_rows_are_missing(): void
    {
        [$c49, $output] = $this->writeC49Fixture('missing-source');
        $result = (new WatchlistBacktestC50IsValidationAntiOverfitCheckService())->execute($c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => [], 'pre_trade_source_rows' => []]);
        $this->assertSame('C50_SOURCE_ROWS_NOT_EVALUABLE', $result['status']);
        $out = json_decode((string) file_get_contents($output), true);
        $this->assertContains('C50_SOURCE_ROWS_NOT_EVALUABLE', array_column($out['not_evaluable_reasons'], 'reason_code'));
        $this->assertNotEmpty($out['source_reconstruction_bias_check']);
        $this->cleanup($c49, $output);
    }

    private function writeC49Fixture(string $suffix): array
    {
        $c49 = $this->path($suffix.'-c49.json');
        $output = $this->path($suffix.'-output.json');
        $artifact = $this->c49Artifact();
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($c49, $artifact);
        return [$c49, $output];
    }

    private function c49Artifact(): array
    {
        return [
            'run_code' => 'C49_BROADER_STRATEGY_REDESIGN',
            'status' => 'C49_BROADER_STRATEGY_REDESIGN_COMPLETED',
            'artifact_type' => 'C49_BROADER_STRATEGY_REDESIGN',
            'production_ready' => false,
            'diagnostic_conclusion' => 'C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION',
            'next_step_recommendation' => 'C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN',
            'source_universe_summary' => [
                'source_evidence_artifact' => 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json',
                'source_rows_available' => true,
                'source_mode' => 'C28_PICK_DIAGNOSTIC_ROWS',
                'is_rows' => 100,
                'g21_rows' => 40,
                'g16_rows' => 40,
                'g13_rows' => 20,
                'months' => 9,
                'pre_trade_source_mode' => 'DATABASE_AS_OF_SIGNAL_DATE_JOIN',
                'pre_trade_source_row_count' => 100,
                'oos_data_used_for_tuning' => false,
                'oos_return_used_for_selection' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
            ],
            'selected_c49_candidates_for_c50' => [
                'primary_candidate' => 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL',
                'primary_profile_code' => 'C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL',
                'defensive_comparator' => 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN',
                'defensive_profile_code' => 'C49_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN',
                'selected_candidate_count' => 3,
                'candidate_is_not_production' => true,
                'production_ready' => false,
            ],
            'c50_readiness_decision' => [
                'primary_candidate_code' => 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL',
                'defensive_comparator_code' => 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN',
                'c50_recommendation' => 'C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN',
                'diagnostic_conclusion' => 'C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION',
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'production_ready' => false,
            ],
            'safety_boundaries' => ['NO_OOS_TUNING' => true, 'NO_OOS_PROOF' => true, 'production_ready' => false],
        ];
    }

    private function sourceRows(): array
    {
        $rows = [];
        $months = ['2023-01', '2023-02', '2023-03', '2023-04', '2023-05', '2023-06', '2023-07', '2023-08', '2023-09'];
        $sectors = ['IDXTECHNO', 'IDXFINANCE', 'IDXENERGY'];
        foreach ($months as $monthIndex => $month) {
            for ($i = 1; $i <= 18; $i++) {
                $rows[] = $this->row($month, $i, 'G16', 'next_open_delay_after_close_signal', $this->ret($i, $monthIndex), $sectors[$i % 3]);
                $rows[] = $this->row($month, $i + 100, 'G21', 'no_rule_profit_signal_before_fallback', $this->ret($i + 100, $monthIndex), $sectors[$i % 3]);
            }
            for ($i = 1; $i <= 8; $i++) {
                $rows[] = $this->row($month, $i + 200, 'G13', 'no_rule_profit_signal_before_fallback', $this->ret($i + 200, $monthIndex), $sectors[$i % 3]);
            }
        }
        return $rows;
    }

    private function preTradeRows(): array
    {
        return array_map(function (array $row): array {
            return [
                'trade_date' => $row['trade_date'],
                'ticker_id' => $row['ticker_id'],
                'dv20_idr' => 10000000000,
                'atr14_pct' => 0.03,
                'vol_ratio' => 1.2,
                'roc20' => 0.04,
                'ma20_slope_pct' => 0.01,
                'rs_20_vs_ihsg' => 0.02,
                'rs_20_vs_sector' => 0.02,
                'sector_roc20' => 0.03,
                'sector_code' => $row['sector_code'],
                'market_index_roc20' => 0.04,
                'market_index_ma20_slope_pct' => 0.01,
            ];
        }, $this->sourceRows());
    }

    private function row(string $month, int $index, string $branch, string $bucket, float $ret, string $sector): array
    {
        $day = str_pad((string) min(20, ($index % 20) + 1), 2, '0', STR_PAD_LEFT);
        $ticker = substr($branch, 0, 1).str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        return [
            'trade_date' => $month.'-'.$day,
            'trade_month' => $month,
            'ticker_id' => abs(crc32($ticker)) % 100000,
            'ticker' => $ticker,
            'param_id' => 150 + ($index % 3),
            'row_code' => 'ROW_'.$branch.'_'.$month.'_'.$index,
            'selected_source_code' => $branch,
            'bucket_code' => $bucket,
            'profile_ret_net' => $ret,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'oos_executed' => false,
            'production_ready' => 0,
            'sector_code' => $sector,
            'market_index_roc20' => 0.04,
            'market_index_ma20_slope_pct' => 0.01,
            'sector_roc20' => 0.03,
            'rs_20_vs_ihsg' => 0.02,
            'rs_20_vs_sector' => 0.02,
            'roc20' => 0.04,
            'ma20_slope_pct' => 0.01,
            'atr14_pct' => 0.03,
            'vol_ratio' => 1.2,
            'dv20_idr' => 10000000000,
        ];
    }

    private function ret(int $i, int $monthIndex): float
    {
        if ($monthIndex === 2 && $i % 11 === 0) { return -0.006; }
        return $i % 7 === 0 ? 0.002 : 0.014;
    }

    private function hashFromFile(string $path): string { $artifact = json_decode((string) file_get_contents($path), true); return $this->stableHash($artifact); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function path(string $name): string { return sys_get_temp_dir().'/c50-'.uniqid('', true).'-'.$name; }
    private function writeJson(string $path, array $payload): void { file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
    private function setNested(array &$payload, string $path, $value): void { $cursor =& $payload; $parts = explode('.', $path); foreach ($parts as $index => $part) { if ($index === count($parts) - 1) { $cursor[$part] = $value; return; } if (! isset($cursor[$part]) || ! is_array($cursor[$part])) { $cursor[$part] = []; } $cursor =& $cursor[$part]; } }
}
