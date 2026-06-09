<?php

use App\Application\Watchlist\Services\WatchlistBacktestRuntimeArtifactService;

class WatchlistBacktestRuntimeArtifactServiceTest extends TestCase
{
    public function test_runtime_artifact_contains_official_manifest_shape_and_deterministic_hash(): void
    {
        $service = new WatchlistBacktestRuntimeArtifactService();
        $payload = $this->backtestPayload();

        $first = $service->buildArtifact($payload, [], [], ['generated_at' => '2026-06-08T00:00:00+07:00']);
        $second = $service->buildArtifact($payload, [], [], ['generated_at' => '2026-06-08T00:00:00+07:00']);

        $this->assertSame($first, $second);
        $this->assertTrue($first['ready']);
        $this->assertSame('WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY_WITH_EVALUATION_SKIPPED', $first['reason_code']);

        foreach ([
            'meta',
            'source_contract',
            'backtest_contract',
            'paramset_snapshot',
            'replay_window',
            'input_manifest',
            'items',
            'trades',
            'evaluations',
            'metrics',
            'summary',
            'diagnostics',
            'artifact_manifest',
            'validation',
        ] as $requiredKey) {
            $this->assertArrayHasKey($requiredKey, $first);
        }

        $this->assertSame('WatchlistBacktestRuntimeArtifactService', $first['meta']['artifact_service']);
        $this->assertSame('watchlist_bt_eval', $first['artifact_manifest']['official_backtest_tables'][1]);
        $this->assertTrue($first['artifact_manifest']['runtime_artifact_created']);
        $this->assertFalse($first['artifact_manifest']['runtime_persistence_created']);
        $this->assertTrue($first['validation']['required_sections_present']);
        $this->assertTrue($first['validation']['official_manifest_referenced']);
        $this->assertSame($first['meta']['artifact_hash'], $first['validation']['artifact_hash']);
        $this->assertSame(40, strlen($first['validation']['artifact_hash']));
    }

    public function test_runtime_artifact_preserves_source_payload_and_boundary_contracts(): void
    {
        $service = new WatchlistBacktestRuntimeArtifactService();
        $payload = $this->backtestPayload();
        $before = $payload;

        $artifact = $service->buildArtifact($payload);

        $this->assertSame($before, $payload);
        $this->assertTrue($artifact['source_contract']['no_raw_market_data']);
        $this->assertTrue($artifact['source_contract']['no_latest_shortcut']);
        $this->assertTrue($artifact['source_contract']['no_max_trade_date_shortcut']);
        $this->assertTrue($artifact['source_contract']['no_plan_mutation']);
        $this->assertTrue($artifact['source_contract']['no_recommendation_mutation']);
        $this->assertTrue($artifact['source_contract']['no_confirm_mutation']);
        $this->assertTrue($artifact['backtest_contract']['runtime_artifact_created']);
        $this->assertFalse($artifact['summary']['production_ready']);
    }

    public function test_runtime_artifact_records_fail_safe_metric_diagnostics_when_inputs_are_unavailable(): void
    {
        $artifact = (new WatchlistBacktestRuntimeArtifactService())->buildArtifact($this->backtestPayload());

        $this->assertFalse($artifact['metrics']['ready']);
        $this->assertSame('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $artifact['metrics']['reason_code']);
        $this->assertSame('UNAVAILABLE', $artifact['input_manifest']['price_series_contract']);
        $this->assertSame('UNAVAILABLE', $artifact['input_manifest']['calendar_contract']);
        $this->assertFalse($artifact['validation']['metrics_ready']);
        $this->assertContains('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', array_column($artifact['diagnostics'], 'reason_code'));
        $this->assertContains('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', array_column($artifact['diagnostics'], 'reason_code'));
        $this->assertContains('WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', array_column($artifact['diagnostics'], 'reason_code'));
    }

    public function test_runtime_artifact_can_encode_and_write_deterministic_json_export_foundation(): void
    {
        $service = new WatchlistBacktestRuntimeArtifactService();
        $artifact = $service->buildArtifact($this->backtestPayload(), [], [], ['generated_at' => '2026-06-08T00:00:00+07:00']);
        $encoded = $service->encodeArtifact($artifact);
        $targetPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'watchlist-bt-artifact-test-'.uniqid('', true).'.json';

        $write = $service->writeJsonArtifact($artifact, $targetPath);

        $this->assertTrue($write['ready']);
        $this->assertFileExists($targetPath);
        $this->assertSame($encoded, file_get_contents($targetPath));
        $this->assertSame(sha1($encoded), $write['sha1']);

        @unlink($targetPath);
    }

    private function backtestPayload(): array
    {
        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_FOUNDATION_READY',
            'meta' => [
                'strategy_code' => 'WS',
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
                'engine' => 'WatchlistBacktestStrategyService',
            ],
            'source_contract' => [
                'consumer' => 'WatchlistBacktestStrategyService',
                'recommendation_layer_only' => true,
                'confirm_overlay_diagnostic_only' => true,
                'no_raw_market_data' => true,
                'no_latest_shortcut' => true,
                'no_max_trade_date_shortcut' => true,
                'no_plan_mutation' => true,
                'no_recommendation_mutation' => true,
                'no_confirm_mutation' => true,
            ],
            'backtest_contract' => [
                'no_lookahead' => true,
                'deterministic_replay' => true,
                'publication_aware_replay' => true,
                'explicit_replay_window_only' => true,
                'not_production_ready' => true,
            ],
            'paramset_snapshot' => [
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
                'backtest' => [
                    'entry_model' => 'D_PLUS_1_OPEN_DOCUMENTED',
                    'exit_model' => 'WEEKLY_SWING_MAX_5_TRADING_DAYS_DOCUMENTED',
                    'pricing_model' => 'FOUNDATION_ONLY_PRICE_SERIES_NOT_CONSUMED',
                    'fee_model' => 'IDR_FIXED',
                    'fee_buy_idr' => 2500,
                    'fee_sell_idr' => 2500,
                    'notional_idr' => 10000000,
                    'lot_size' => 100,
                    'slippage_entry_pct' => 0,
                    'slippage_exit_pct' => 0,
                ],
            ],
            'replay_window' => [
                'from_trade_date' => '2026-05-19',
                'to_trade_date' => '2026-05-19',
                'trade_dates' => ['2026-05-19'],
                'explicit_window' => true,
            ],
            'items' => [
                [
                    'trade_date' => '2026-05-19',
                    'ticker_id' => 1,
                    'ticker' => 'AAA',
                    'recommended_flag' => true,
                    'active_trade_evaluation' => true,
                    'reason_codes' => ['WS_REC_SELECTED'],
                ],
            ],
            'trades' => [
                [
                    'trade_date' => '2026-05-19',
                    'ticker_id' => 1,
                    'ticker' => 'AAA',
                    'bucket_code' => 'TOP_PICKS',
                    'plan_rank' => 1,
                    'recommendation_rank' => 1,
                    'trade_state' => 'EVALUATION_CANDIDATE',
                    'reason_codes' => ['WS_REC_SELECTED'],
                ],
            ],
            'evaluations' => [
                [
                    'trade_date' => '2026-05-19',
                    'ticker_id' => 1,
                    'ticker' => 'AAA',
                    'metrics_ready' => false,
                    'reason_codes' => ['WS_BT_EVAL_METRICS_MISSING'],
                ],
            ],
            'summary' => [
                'days_requested' => 1,
                'days_evaluated' => 1,
                'items_count' => 1,
                'picks_count' => 1,
                'evaluations_count' => 1,
                'empty_recommendation_days' => 0,
                'metrics_ready' => false,
                'production_ready' => false,
                'reason_codes' => ['WS_BT_EVAL_METRICS_MISSING', 'WS_BT_ARTIFACT_MISSING'],
            ],
            'diagnostics' => [],
            'artifact_manifest' => [
                'official_backtest_tables' => [
                    'watchlist_bt_param_grid',
                    'watchlist_bt_eval',
                    'watchlist_bt_picks_ws',
                    'watchlist_bt_universe_ws',
                    'watchlist_bt_cutoffs_ws',
                    'watchlist_bt_oos_eval_ws',
                ],
                'runtime_artifact_created' => false,
                'runtime_persistence_created' => false,
            ],
        ];
    }
}
