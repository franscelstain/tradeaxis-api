<?php

class WatchlistBacktestOosStaticGuardTest extends TestCase
{
    public function test_oos_runtime_has_no_latest_max_date_raw_query_or_promotion_surface(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestOosSplitService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestOosProofService.php'),
            base_path('app/Console/Commands/Watchlist/RunBacktestOosProofCommand.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));

        $this->assertStringNotContainsString('MAX(trade_date)', $content);
        $this->assertStringNotContainsString('->latest(', $content);
        $this->assertStringNotContainsString('now()', $content);
        $this->assertStringNotContainsString("DB::table('eod_", $content);
        $this->assertStringNotContainsString('status = \'ACTIVE\'', $content);
        $this->assertStringNotContainsString('broker', strtolower(file_get_contents($files[0])));
    }

    public function test_selection_service_signature_cannot_receive_oos_metrics(): void
    {
        $method = new ReflectionMethod(
            App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationService::class,
            'calibrate'
        );
        $names = array_map(function (ReflectionParameter $parameter): string {
            return strtolower($parameter->getName());
        }, $method->getParameters());

        $this->assertSame(['isdates', 'options'], $names);
        $this->assertNotContains('oosmetrics', $names);
    }

    public function test_contract_drift_closures_are_present(): void
    {
        $file17 = file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md'));
        $file16 = file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md'));
        $file20 = file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/20_WS_CANONICAL_PARAMSET_PROCEDURES.md'));
        $promotion = file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/db/PROMOTE_PARAMSET.sql'));
        $fixture = json_decode(file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/fixtures/bt_oos_proof_minimal.json')), true);

        $this->assertStringContainsString('FLOOR_70_PERCENT_IS_REMAINDER_OOS', $file17);
        $this->assertStringContainsString('`param_id` paling kecil', $file16);
        $this->assertStringContainsString('canonical default `40`', $file20);
        $this->assertStringContainsString('o.picks_count_oos >= 40', $promotion);
        $this->assertSame(40, $fixture['acceptance']['min_trades_oos']);
        $this->assertArrayNotHasKey('min_test_win_rate', $fixture['acceptance']);
        $this->assertArrayNotHasKey('max_test_drawdown', $fixture['acceptance']);
    }


    public function test_oos_runtime_uses_only_official_artifacts_and_has_no_business_mutation_surface(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestOosProofService.php'),
            base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'),
            base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestEvaluationRepository.php'),
            base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestOosEvaluationRepository.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));
        $lower = strtolower($content);

        $this->assertStringContainsString("DB::table('watchlist_bt_param_grid')", $content);
        $this->assertStringContainsString("DB::table('watchlist_bt_eval')", $content);
        $this->assertStringContainsString("'catalog_code', 'catalog_version'", $content);
        $this->assertStringContainsString("'eval_model', 'paramset_hash'", $content);
        $this->assertStringContainsString("DB::table('watchlist_bt_oos_eval_ws')", $content);
        $this->assertStringContainsString('WS_BT_PARAM_GRID_PERSISTED_SET_MISMATCH', $content);
        $this->assertStringNotContainsString("DB::table('eod_bars')", $content);
        $this->assertStringNotContainsString("DB::table('eod_indicators')", $content);
        $this->assertStringNotContainsString("DB::table('market_calendar')", $content);
        $this->assertStringNotContainsString("update(['status' => 'ACTIVE'])", $content);
        $this->assertStringNotContainsString('promoteparamset', $lower);
        $this->assertStringNotContainsString('orderservice', $lower);
        $this->assertStringNotContainsString('brokerservice', $lower);
        $this->assertStringNotContainsString('portfolioservice', $lower);
    }

    public function test_oos_artifact_and_fail_closed_guards_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestOosProofService.php'));
        $ddl = file_get_contents(base_path('docs/watchlist/system/policies/weekly_swing/db/BACKTEST_SCHEMA_DDL.sql'));

        foreach ([
            "'split_manifest'",
            "'is_calibration'",
            "'best_is_binding'",
            "'oos_evaluation'",
            "'oos_acceptance'",
            "'persistence_manifest'",
        ] as $section) {
            $this->assertStringContainsString($section, $service);
        }
        $this->assertStringContainsString("'missing_metrics'", $service);
        $this->assertStringContainsString("'best_param_frozen_before_oos' => true", $service);
        $this->assertStringContainsString("'oos_not_used_for_selection' => true", $service);
        $this->assertStringContainsString('is_eval_id BIGINT NOT NULL', $ddl);
        $this->assertStringContainsString('FK_bt_oos_is_eval', $ddl);
    }

    public function test_long_window_runtime_uses_targeted_date_ticker_pairs_and_in_memory_calibration(): void
    {
        $marketDataRead = file_get_contents(base_path(
            'app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php'
        ));
        $runtime = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php'
        ));
        $calibration = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'
        ));

        $this->assertStringContainsString('readPublishedSeriesForDateTickerMap', $marketDataRead);
        $this->assertStringContainsString('TARGETED_DATE_TICKER_MAP', $runtime);
        $this->assertStringContainsString('bindRuntimeMetadataBeforeFreeze', $runtime);
        $this->assertStringContainsString('source_price_mode', $runtime);
        $this->assertStringContainsString('gap_fill_rule', $runtime);
        $this->assertStringContainsString('price_fraction_rule', $runtime);
        $this->assertStringContainsString('price_normalization_rule', $runtime);
        $this->assertStringContainsString("'skip_artifact_write' => true", $runtime);
        $this->assertStringContainsString('releaseIterationMemory', $calibration);
        $this->assertStringNotContainsString('array_fill_keys($requiredPriceDates', $runtime);
    }

    public function test_canonical_grid_seed_and_atr_rr_fallback_are_explicit(): void
    {
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $catalog = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestParamGridCatalog.php'
        ));
        $metrics = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php'
        ));
        $paramsetFactory = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'
        ));
        $calibration = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'
        ));
        $seed = file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/BACKTEST_PARAM_GRID_SEED.sql'
        ));

        $expectedGridCount = App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog::CATALOG_COUNT;

        $this->assertStringContainsString('SeedBacktestParamGridCommand::class', $kernel);
        $this->assertStringContainsString('WS_BT_GRID_BOOTSTRAP_2026_06', $catalog);
        $this->assertSame(
            $expectedGridCount,
            count(App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog::rows())
        );
        $this->assertSame($expectedGridCount, substr_count($catalog, 'self::row('));
        $this->assertStringContainsString('ATR_RR_FALLBACK', $metrics);
        $this->assertStringContainsString('WATCHLIST_BACKTEST_TARGET_STOP_ATR_RR_FALLBACK', $metrics);
        $this->assertStringContainsString('GAP_THROUGH_STOP_AT_OPEN', $metrics);
        $this->assertStringContainsString('GAP_THROUGH_TARGET_AT_OPEN', $metrics);
        $this->assertStringContainsString('CONSERVATIVE_STOP_FLOOR_TARGET_CEIL', $metrics);
        $this->assertStringContainsString('BT_SKIP_NON_EXECUTABLE_PRICE_ENTRY', $metrics);
        $this->assertStringContainsString('BT_SKIP_NON_EXECUTABLE_PRICE_EXIT', $metrics);
        $this->assertStringContainsString('CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR', $paramsetFactory);
        $this->assertStringContainsString('bt_grid_resolution', $paramsetFactory);
        $this->assertStringContainsString('$this->paramsetFactory->make($row)', $calibration);
        $this->assertSame($expectedGridCount, substr_count($seed, 'INSERT INTO watchlist_bt_param_grid'));
        $this->assertSame($expectedGridCount, substr_count($seed, 'WHERE NOT EXISTS'));
    }

    public function test_command_is_registered_without_scheduler_or_api_surface(): void
    {
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestOosProofCommand.php'));

        $this->assertStringContainsString('RunBacktestOosProofCommand::class', $kernel);
        $this->assertStringContainsString('SeedBacktestParamGridCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString('schedule->command(\'watchlist:backtest-oos-proof', $kernel);
        $this->assertStringNotContainsString('Route::', $command);
    }
}
