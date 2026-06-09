<?php

class WatchlistBacktestPublishedPriceRuntimeStaticGuardTest extends TestCase
{
    public function test_watchlist_runtime_uses_market_data_application_services_without_direct_database_reads(): void
    {
        $runtime = $this->read('app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php');
        $command = $this->read('app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php');
        $combined = $runtime."\n".$command;

        $this->assertStringContainsString('MarketDataTradingCalendarReadService', $runtime);
        $this->assertStringContainsString('MarketDataPublishedEodSeriesReadService', $runtime);
        $this->assertStringNotContainsString('DB::table', $combined);
        $this->assertStringNotContainsString("'eod_bars'", $combined);
        $this->assertStringNotContainsString("'market_calendar'", $combined);
        $this->assertStringNotContainsString('MAX(trade_date)', $combined);
        $this->assertStringNotContainsString('latest()', $combined);
    }

    public function test_runtime_freezes_trade_candidates_before_future_price_evaluation_and_keeps_execution_boundary_closed(): void
    {
        $runtime = $this->read('app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php');
        $command = $this->read('app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php');
        $combined = strtolower($runtime."\n".$command);

        $this->assertStringContainsString('trade_candidates_frozen_before_price_read', $runtime);
        $this->assertStringContainsString('future_price_used_for_evaluation_only', $runtime);
        $this->assertStringContainsString('strategy_payload_immutable', $runtime);
        $this->assertStringNotContainsString('createorder', $combined);
        $this->assertStringNotContainsString('broker_instruction', $combined);
        $this->assertStringNotContainsString('portfolio_allocation', $combined);
        $this->assertStringNotContainsString('scheduler', $combined);
    }

    public function test_command_requires_explicit_from_to_and_output_and_is_registered_without_scheduler(): void
    {
        $command = $this->read('app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php');
        $kernel = $this->read('app/Console/Kernel.php');

        $this->assertStringContainsString('{--from=', $command);
        $this->assertStringContainsString('{--to=', $command);
        $this->assertStringContainsString('{--output=', $command);
        $this->assertStringContainsString('RunBacktestPublishedPriceProofCommand::class', $kernel);
        $this->assertSame(1, substr_count($kernel, 'RunBacktestPublishedPriceProofCommand::class'));
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-published-price-proof", $kernel);
    }

    public function test_backtest_runtime_requires_positive_volume_and_keeps_reason_codes_in_owner_docs_and_seed(): void
    {
        $metrics = $this->read('app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php');
        $strategy = $this->read('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php');
        $runtime = $this->read('app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php');
        $owners = implode("\n", [
            $this->read('docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md'),
            $this->read('docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md'),
            $this->read('docs/watchlist/system/policies/weekly_swing/13_WS_CONTRACT_TEST_CHECKLIST.md'),
            $this->read('docs/watchlist/system/policies/weekly_swing/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md'),
            $this->read('docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md'),
            $this->read('docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql'),
        ]);

        $this->assertStringContainsString('POSITIVE_VOLUME_REQUIRED', $strategy.$runtime.$owners);
        $this->assertStringContainsString('min_tradable_volume', $strategy.$runtime.$metrics);
        foreach (['BT_SKIP_NO_TRADABLE_ENTRY', 'BT_SKIP_NO_TRADABLE_EXIT'] as $reasonCode) {
            $this->assertStringContainsString($reasonCode, $metrics);
            $this->assertGreaterThanOrEqual(5, substr_count($owners, $reasonCode));
        }
        $this->assertStringContainsString('zero_volume_bar_is_published_but_non_executable', $metrics);
    }

    public function test_runtime_paramset_binds_eval_thresholds_and_blocks_unresolved_threshold_export(): void
    {
        $strategy = $this->read('app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php');
        $runtime = $this->read('app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php');
        $contract = $this->read('docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md');
        $registry = $this->read('docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md');

        foreach ([
            'min_trades',
            'min_days_covered',
            'min_p25_ret_net_top',
            'min_month_win_rate_min',
            'min_month_avg_ret_net_min',
        ] as $threshold) {
            $this->assertStringContainsString("'{$threshold}'", $strategy);
            $this->assertStringContainsString($threshold, $contract.$registry);
        }
        $this->assertStringContainsString("'WS_BT_EVAL_METRICS_MISSING'", $runtime);
        $this->assertStringContainsString('thresholds_resolved', $runtime);
        $this->assertStringContainsString('runtime artifact export is blocked', $runtime);
        $this->assertStringContainsString("'value' => 120", $strategy);
        $this->assertStringContainsString("'value' => 40", $strategy);
        $this->assertStringContainsString('CEIL_70_PERCENT_OF_TOTAL_TRADING_DAYS', $this->read('app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php'));

        $metricsOwner = $this->read('docs/watchlist/system/policies/weekly_swing/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md');
        $this->assertStringContainsString('rule-based exit', strtolower($metricsOwner));
        $this->assertStringContainsString('batas maksimum lima trading day', strtolower($metricsOwner));
        $this->assertStringNotContainsString('**Fixed holding** dengan parameter', $metricsOwner);
    }

    public function test_active_audit_session_is_synchronized_and_historical_markers_remain(): void
    {
        $status = $this->read('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $active = 'WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION';

        $this->assertSame(1, substr_count($status, '## ACTIVE SESSION'));
        $this->assertSame(1, substr_count($tracker, '## ACTIVE SESSION'));
        $this->assertStringContainsString($active, $status);
        $this->assertStringContainsString($active, $tracker);
        $this->assertStringContainsString('WATCHLIST — BACKTEST STRATEGY ENGINE FOUNDATION EXECUTION SESSION', $status.$tracker);
        $this->assertStringContainsString('PHASE_6_CONFIRM_OVERLAY_FOUNDATION_DONE / NOT_PRODUCTION_READY', $status.$tracker);
        $this->assertStringContainsString('PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY', $status.$tracker);
    }

    public function test_runtime_artifact_references_only_official_manifest_names(): void
    {
        $artifact = $this->read('app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php');

        foreach ([
            'watchlist_bt_param_grid',
            'watchlist_bt_eval',
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_bt_cutoffs_ws',
            'watchlist_bt_oos_eval_ws',
        ] as $officialName) {
            $this->assertStringContainsString($officialName, $artifact);
        }
        $this->assertStringNotContainsString('watchlist_bt_runtime_result', $artifact);
    }

    private function read(string $relativePath): string
    {
        $contents = file_get_contents(base_path($relativePath));
        $this->assertNotFalse($contents);

        return $contents;
    }
}
