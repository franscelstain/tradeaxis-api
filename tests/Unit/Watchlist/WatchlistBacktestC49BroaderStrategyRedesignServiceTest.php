<?php

use App\Application\Watchlist\Services\WatchlistBacktestC49BroaderStrategyRedesignService;

class WatchlistBacktestC49BroaderStrategyRedesignServiceTest extends TestCase
{
    public function test_it_blocks_missing_C48_artifact(): void
    {
        $output = $this->path('missing-output.json');
        $result = (new WatchlistBacktestC49BroaderStrategyRedesignService())->execute($this->path('missing-c48.json'), 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C49_BLOCKED_MISSING_C48_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        @unlink($output);
    }

    public function test_it_blocks_expected_C48_hash_mismatch(): void
    {
        [$c48, $output] = $this->writeC48Fixture('hash-mismatch');
        $result = (new WatchlistBacktestC49BroaderStrategyRedesignService())->execute($c48, 'wrong-hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'pre_trade_source_rows' => $this->preTradeRows()]);
        $this->assertSame('C49_BLOCKED_C48_HASH_MISMATCH', $result['status']);
        $this->cleanup($c48, $output);
    }

    public function test_it_blocks_invalid_C48_boundary_inputs(): void
    {
        $cases = [
            ['status', 'C48_NOT_COMPLETED', 'C49_BLOCKED_UNEXPECTED_C48_STATUS'],
            ['diagnostic_conclusion', 'C48_RANDOM_CONCLUSION', 'C49_BLOCKED_UNEXPECTED_C48_CONCLUSION'],
            ['production_ready', true, 'C49_BLOCKED_C48_PRODUCTION_READY_NOT_FALSE'],
            ['next_step_recommendation', 'C49_SOMETHING_ELSE', 'C49_BLOCKED_C48_NEXT_STEP_UNEXPECTED'],
            ['c49_readiness_decision.direct_oos_proof_recommended', true, 'C49_BLOCKED_C48_OOS_PROOF_FLAG_INVALID'],
            ['c49_readiness_decision.oos_proof_unlocked', true, 'C49_BLOCKED_C48_OOS_PROOF_FLAG_INVALID'],
        ];

        foreach ($cases as $index => $case) {
            [$c48, $output] = $this->writeC48Fixture('boundary-'.$index);
            $artifact = json_decode((string) file_get_contents($c48), true);
            $this->setNested($artifact, $case[0], $case[1]);
            $artifact['artifact_hash'] = $this->stableHash($artifact);
            $this->writeJson($c48, $artifact);
            $result = (new WatchlistBacktestC49BroaderStrategyRedesignService())->execute($c48, $artifact['artifact_hash'], '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'pre_trade_source_rows' => $this->preTradeRows()]);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c48, $output);
        }
    }

    public function test_it_blocks_when_IS_period_touches_OOS_reserved_window(): void
    {
        [$c48, $output] = $this->writeC48Fixture('oos-period');
        $hash = $this->hashFromFile($c48);
        $result = (new WatchlistBacktestC49BroaderStrategyRedesignService())->execute($c48, $hash, '2025-05-22', '2025-06-30', $output, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'pre_trade_source_rows' => $this->preTradeRows()]);
        $this->assertSame('C49_BLOCKED_ATTRIBUTION_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c48, $output);
    }

    public function test_valid_C48_artifact_completes_C49_IS_broader_redesign_without_OOS_tuning(): void
    {
        [$c48, $output] = $this->writeC48Fixture('completed');
        $result = (new WatchlistBacktestC49BroaderStrategyRedesignService())->execute($c48, $this->hashFromFile($c48), '2023-01-02', '2025-05-21', $output, [
            'overwrite' => true,
            'executed_at' => '2026-06-21T00:00:00+00:00',
            'source_rows' => $this->sourceRows(),
            'pre_trade_source_rows' => $this->preTradeRows(),
        ]);

        $this->assertSame('C49_BROADER_STRATEGY_REDESIGN_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertFileExists($output);

        $out = json_decode((string) file_get_contents($output), true);
        $this->assertSame('C49_BROADER_STRATEGY_REDESIGN', $out['run_code']);
        $this->assertSame('C49_BROADER_STRATEGY_REDESIGN', $out['artifact_type']);
        $this->assertTrue($out['c48_hash_match']);
        $this->assertSame('C48_OOS_FAILURE_ATTRIBUTION_COMPLETED', $out['c48_status']);
        $this->assertSame('C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED', $out['c48_diagnostic_conclusion']);
        $this->assertFalse($out['production_ready']);
        $this->assertSame('broader_strategy_redesign_is_only', $out['is_redesign_period']['purpose']);
        $this->assertFalse($out['is_redesign_period']['oos_data_used_for_tuning']);
        $this->assertFalse($out['is_redesign_period']['oos_return_used_for_selection']);
        $this->assertFalse($out['oos_reserved_period']['used_for_selection']);
        $this->assertFalse($out['oos_reserved_period']['used_for_tuning']);
        $this->assertFalse($out['oos_reserved_period']['used_for_proof']);
        $this->assertTrue($out['c48_carry_forward_summary']['c48_used_for_hypothesis_only']);
        $this->assertNotEmpty($out['source_universe_summary']);
        $this->assertNotEmpty($out['baseline_c44_comparator_summary']);
        $this->assertNotEmpty($out['redesign_profile_results']);
        $this->assertNotEmpty($out['shared_core_escape_attribution']);
        $this->assertNotEmpty($out['branch_quota_fragility_is_diagnostic']);
        $this->assertNotEmpty($out['regime_aware_is_diagnostic']);
        $this->assertNotEmpty($out['concentration_guard_is_diagnostic']);
        $this->assertNotEmpty($out['post_entry_path_is_diagnostic']);
        $this->assertNotEmpty($out['candidate_scorecard']);
        $this->assertNotEmpty($out['selected_c49_candidates_for_c50']);
        $this->assertNotEmpty($out['c50_readiness_decision']);
        $this->assertNotEmpty($out['candidate_safety_audit']);
        $this->assertIsArray($out['not_evaluable_reasons']);
        $this->assertContains($out['diagnostic_conclusion'], ['C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION', 'C49_EVIDENCE_EXPANSION_REQUIRED']);
        $this->assertFalse($out['c50_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($out['c50_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($out['c50_readiness_decision']['production_ready']);
        $this->assertFalse($out['safety_boundaries']['oos_data_used_for_tuning']);
        $this->assertFalse($out['safety_boundaries']['oos_return_used_for_candidate_selection']);
        $this->assertFalse($out['safety_boundaries']['return_used_for_selection']);
        $this->assertFalse($out['safety_boundaries']['future_path_used_for_selection']);
        $this->cleanup($c48, $output);
    }

    public function test_source_universe_or_not_evaluable_reason_is_recorded_when_source_rows_are_missing(): void
    {
        [$c48, $output] = $this->writeC48Fixture('missing-source');
        $result = (new WatchlistBacktestC49BroaderStrategyRedesignService())->execute($c48, $this->hashFromFile($c48), '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => [], 'pre_trade_source_rows' => []]);
        $this->assertSame('C49_SOURCE_ROWS_NOT_EVALUABLE', $result['status']);
        $out = json_decode((string) file_get_contents($output), true);
        $this->assertContains('C49_SOURCE_ROWS_NOT_EVALUABLE', array_column($out['not_evaluable_reasons'], 'reason_code'));
        $this->cleanup($c48, $output);
    }

    public function test_return_and_future_path_are_diagnostic_only_not_selection_inputs(): void
    {
        [$c48, $output] = $this->writeC48Fixture('safety');
        (new WatchlistBacktestC49BroaderStrategyRedesignService())->execute($c48, $this->hashFromFile($c48), '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'pre_trade_source_rows' => $this->preTradeRows()]);
        $out = json_decode((string) file_get_contents($output), true);
        foreach ($out['candidate_scorecard'] as $candidate) {
            $this->assertFalse($candidate['production_ready']);
            $this->assertNotContains('profile_ret_net', $candidate['safe_pre_trade_fields_used']);
            $this->assertNotContains('profile_exit_price', $candidate['safe_pre_trade_fields_used']);
        }
        foreach ($out['post_entry_path_is_diagnostic'] as $row) {
            $this->assertFalse($row['safe_for_selection']);
            $this->assertTrue($row['diagnostic_only']);
        }
        foreach ($out['candidate_safety_audit'] as $audit) {
            $this->assertTrue($audit['passed']);
            $this->assertFalse($audit['return_used_for_selection']);
            $this->assertFalse($audit['future_path_used_for_selection']);
            $this->assertFalse($audit['oos_data_used_for_tuning']);
        }
        $this->cleanup($c48, $output);
    }

    private function writeC48Fixture(string $suffix): array
    {
        $c48 = $this->path($suffix.'-c48.json');
        $output = $this->path($suffix.'-output.json');
        $this->writeJson($c48, $this->c48Artifact());
        return [$c48, $output];
    }

    private function c48Artifact(): array
    {
        $artifact = [
            'run_code' => 'C48_OOS_FAILURE_ATTRIBUTION',
            'status' => 'C48_OOS_FAILURE_ATTRIBUTION_COMPLETED',
            'artifact_type' => 'C48_OOS_FAILURE_ATTRIBUTION',
            'production_ready' => false,
            'diagnostic_conclusion' => 'C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED',
            'next_step_recommendation' => 'C49_BROADER_STRATEGY_REDESIGN',
            'failure_attribution_summary' => [
                'dominant_failure_source' => 'shared_core_selection_and_oos_month_cluster',
                'dominant_failure_branch' => 'G21',
                'g21_quota_fragility' => true,
                'market_extension_control_insufficient' => true,
                'market_regime_failure' => true,
                'ticker_concentration_failure' => true,
                'sector_bucket_failure' => true,
                'post_entry_path_failure' => true,
                'selection_overlap_failure' => true,
                'is_oos_generalization_failure' => true,
            ],
            'c49_readiness_decision' => ['c49_recommendation' => 'C49_BROADER_STRATEGY_REDESIGN', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'safety_boundaries' => ['NO_OOS_TUNING' => true, 'NO_OOS_PROOF_RERUN' => true, 'NO_BEST_OF_OOS' => true, 'production_ready' => false, 'oos_data_used_for_tuning' => false],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function sourceRows(): array
    {
        $rows = [];
        $months = ['2023-01', '2023-02', '2023-03', '2023-04'];
        foreach ($months as $monthIndex => $month) {
            for ($i = 1; $i <= 30; $i++) { $rows[] = $this->row($month, $i, 'G16', 'next_open_delay_after_close_signal', $this->ret($i, $monthIndex), 'TECH'); }
            for ($i = 1; $i <= 16; $i++) { $rows[] = $this->row($month, $i, 'G21', 'no_rule_profit_signal_before_fallback', $this->ret($i + 2, $monthIndex), $i % 2 === 0 ? 'FIN' : 'TECH'); }
            for ($i = 1; $i <= 8; $i++) { $rows[] = $this->row($month, $i, 'G13', 'no_rule_profit_signal_before_fallback', $this->ret($i + 4, $monthIndex), 'MISC'); }
        }
        return $rows;
    }

    private function preTradeRows(): array
    {
        $rows = [];
        foreach ($this->sourceRows() as $row) {
            $n = (int) substr((string) $row['ticker'], 1);
            $rows[] = [
                'trade_date' => $row['trade_date'],
                'ticker_id' => $row['ticker_id'],
                'ticker' => $row['ticker'],
                'dv20_idr' => 1000000000 + ($n * 1000),
                'atr14_pct' => ($n % 4) * 0.015 + 0.015,
                'vol_ratio' => 1.0 + ($n / 100),
                'roc20' => $n % 5 === 0 ? -0.01 : 0.03,
                'ma20_slope_pct' => $n % 6 === 0 ? -0.005 : 0.01,
                'rs_20_vs_ihsg' => $n % 7 === 0 ? -0.01 : 0.02,
                'rs_20_vs_sector' => $n % 8 === 0 ? -0.01 : 0.02,
                'sector_roc20' => $n % 9 === 0 ? -0.01 : 0.025,
                'sector_code' => $row['sector_code'],
                'market_index_roc20' => $n % 10 === 0 ? -0.02 : 0.04,
            ];
        }
        return $rows;
    }

    private function row(string $month, int $index, string $branch, string $bucket, float $ret, string $sector): array
    {
        $day = str_pad((string) min(20, $index), 2, '0', STR_PAD_LEFT);
        $ticker = substr($branch, 0, 1).str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        return [
            'trade_date' => $month.'-'.$day,
            'trade_month' => $month,
            'ticker_id' => crc32($ticker) % 100000,
            'ticker' => $ticker,
            'param_id' => 150 + ($index % 3),
            'row_code' => 'ROW_'.$branch.'_'.$index,
            'selected_source_code' => $branch,
            'bucket_code' => $bucket,
            'profile_ret_net' => $ret,
            'profile_exit_reason' => $ret < 0 ? 'time_exit' : 'raw_preplanned_intraday_target_hit',
            'profile_exit_day_offset' => $ret < 0 ? 5 : 2,
            'missing_path_data_flag' => false,
            'lookahead_safe' => true,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'oos_executed' => false,
            'production_ready' => 0,
            'sector_code' => $sector,
        ];
    }

    private function ret(int $i, int $monthIndex): float
    {
        if ($monthIndex === 2 && $i % 6 === 0) { return -0.02; }
        return $i % 5 === 0 ? -0.006 : 0.014;
    }

    private function hashFromFile(string $path): string { $artifact = json_decode((string) file_get_contents($path), true); return $this->stableHash($artifact); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function path(string $name): string { return sys_get_temp_dir().'/c49-'.uniqid('', true).'-'.$name; }
    private function writeJson(string $path, array $payload): void { file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
    private function setNested(array &$payload, string $path, $value): void { $cursor =& $payload; $parts = explode('.', $path); foreach ($parts as $index => $part) { if ($index === count($parts) - 1) { $cursor[$part] = $value; return; } if (! isset($cursor[$part]) || ! is_array($cursor[$part])) { $cursor[$part] = []; } $cursor =& $cursor[$part]; } }
}
