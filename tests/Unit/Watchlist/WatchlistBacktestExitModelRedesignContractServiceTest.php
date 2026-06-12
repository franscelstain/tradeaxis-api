<?php

use App\Application\Watchlist\Services\WatchlistBacktestExitModelRedesignContractService;

class WatchlistBacktestExitModelRedesignContractServiceTest extends TestCase
{
    public function test_it_produces_contract_without_authorizing_catalog_creation(): void
    {
        $input = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c12-c11-input.json';
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c12-redesign-contract.json';
        @unlink($input);
        @unlink($output);
        file_put_contents($input, json_encode($this->c11Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $result = (new WatchlistBacktestExitModelRedesignContractService())->execute($input, $output, [
            'overwrite' => true,
            'generated_at' => '2026-06-12T00:00:00+07:00',
        ]);

        $this->assertTrue($result['is_ready']);
        $this->assertSame('WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_READY', $result['reason_code']);
        $this->assertFileExists($output);

        $artifact = $result['artifact'];
        $this->assertSame('EXIT_MODEL_REDESIGN_CONTRACT_READY', $artifact['decision']['status']);
        $this->assertTrue($artifact['decision']['design_contract_ready']);
        $this->assertFalse($artifact['decision']['catalog_creation_authorized']);
        $this->assertFalse($artifact['decision']['exit_model_catalog_authorized']);
        $this->assertFalse($artifact['decision']['strategy_catalog_created']);
        $this->assertSame('IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG', $artifact['decision']['next_required_step']);
        $this->assertFalse($artifact['no_oos_leakage_summary']['oos_executed']);
        $this->assertFalse($artifact['meta']['production_ready']);
        $this->assertSame('risk.min_rr', $artifact['redesign_contract']['allowed_first_phase_axis_policy'][0]['axis']);
        $this->assertSame('risk.stop_atr_mult', $artifact['redesign_contract']['allowed_first_phase_axis_policy'][1]['axis']);
        $this->assertSame('backtest.holding_days', $artifact['redesign_contract']['blocked_first_phase_axis_policy'][0]['axis']);
        $this->assertSame('backtest.target_pct|backtest.stop_pct', $artifact['redesign_contract']['blocked_first_phase_axis_policy'][1]['axis']);
        $this->assertTrue($artifact['redesign_contract']['hard_boundaries']['do_not_run_oos']);

        @unlink($input);
        @unlink($output);
    }

    public function test_it_requires_c11_contract_artifact(): void
    {
        $missing = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c12-missing-c11.json';
        @unlink($missing);

        $result = (new WatchlistBacktestExitModelRedesignContractService())->execute(
            $missing,
            sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-c12-unused.json',
            ['overwrite' => true]
        );

        $this->assertFalse($result['is_ready']);
        $this->assertSame('OPERATOR_ARTIFACT_REQUIRED', $result['reason_code']);
        $this->assertFalse($result['oos_executed']);
        $this->assertFalse($result['production_ready']);
    }

    private function c11Artifact(): array
    {
        return [
            'meta' => [
                'artifact_hash' => 'hash-c11',
            ],
            'source_catalog' => [
                'catalog_code' => 'WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06',
                'catalog_version' => 'C07',
                'catalog_count' => '12',
                'catalog_hash' => 'hash-c07',
            ],
            'c10_summary' => [
                'target_hit_share' => 0.18,
                'stop_or_timeout_share' => 0.82,
                'exit_totals' => [
                    'hit_target_total' => 2585,
                    'hit_stop_total' => 4927,
                    'timeout_hold_expired_total' => 6858,
                ],
            ],
            'strategy_quality_gate_summary' => [
                'best_median_ret_net_top' => -0.0069,
                'best_p25_ret_net_top' => -0.0342,
                'best_month_win_rate_min' => 0.25,
            ],
            'code_contract_audit' => [
                'factory_rejects_fixed_execution_snapshot_drift' => true,
                'published_runtime_forces_holding_days_5' => true,
                'param_grid_schema_exposes_target_stop_pct' => false,
                'metrics_consumes_target_stop_pct_when_present' => true,
            ],
            'decision' => [
                'status' => 'EXIT_MODEL_CATALOG_NOT_AUTHORIZED',
                'reason_code' => 'WS_BT_C11_EXIT_MODEL_CONTRACT_REQUIRED_BEFORE_CATALOG',
                'exit_model_catalog_authorized' => false,
                'next_decision' => 'NEXT_CATALOG_NOT_DESIGNED',
                'blocking_reasons' => [
                    'C01_C07_FACTORY_REJECTS_EXIT_AXIS_DRIFT',
                    'PUBLISHED_RUNTIME_FORCES_HOLD_5',
                    'PARAM_GRID_SCHEMA_LACKS_TARGET_STOP_PERCENT_FIELDS',
                    'C10_SUMMARY_REMAINS_BELOW_LOCKED_IS_GATES',
                    'C10_EXIT_OUTCOMES_STOP_OR_TIMEOUT_DOMINATE_TARGET',
                ],
            ],
        ];
    }
}
