<?php

class WatchlistBacktestC01StaticGuardTest extends TestCase
{
    public function test_c01_seed_command_is_registered_explicit_and_not_scheduled(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/SeedBacktestC01ParamGridCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c01-param-grid-seed', $command);
        $this->assertStringContainsString('SeedBacktestC01ParamGridCommand::class', $kernel);
        $this->assertStringContainsString('WatchlistBacktestC01ParamGridCatalog::rows()', $command);
        $this->assertStringContainsString('r1_immutable=1', $command);
        $this->assertStringContainsString('r2_immutable=1', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString('latest(', strtolower($command));
        $this->assertStringNotContainsString('max(trade_date)', strtolower($command));
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c01-param-grid-seed", $kernel);
    }

    public function test_c01_calibration_path_is_explicit_is_only_and_has_no_oos_dependency(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestC01ParamGridCatalog.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'),
            base_path('app/Console/Commands/Watchlist/RunBacktestIsCalibrationCommand.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));
        $lower = strtolower($content);

        $this->assertStringContainsString('WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06', $content);
        $this->assertStringContainsString('C01_GRID_FAILED_IS_QUALITY', $content);
        $this->assertStringContainsString('WATCHLIST_C01_IS_CALIBRATION_V1', $content);
        $this->assertStringContainsString('WEEKLY_SWING_DOWNSIDE_STABILITY_C01_IS_ONLY', $content);
        $this->assertStringContainsString("public const R2_MIN_IS_DATE = '2023-01-02'", $content);
        $this->assertStringContainsString("public const R2_MAX_IS_DATE = '2025-05-21'", $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $content);
        $this->assertStringNotContainsString("DB::table('eod_bars')", $content);
        $this->assertStringNotContainsString("DB::table('eod_indicators')", $content);
        $this->assertStringNotContainsString('promoteparamset', $lower);
        $this->assertStringNotContainsString('orderservice', $lower);
        $this->assertStringNotContainsString('brokerservice', $lower);
        $this->assertStringNotContainsString('portfolioservice', $lower);
    }

    public function test_c01_does_not_continue_r_series_catalog_naming(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestC01ParamGridCatalog.php'),
            base_path('app/Console/Commands/Watchlist/SeedBacktestC01ParamGridCommand.php'),
            base_path('database/seeders/Watchlist/WatchlistBacktestC01ParamGridSeeder.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));

        $this->assertStringContainsString('WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06', $content);
        $this->assertDoesNotMatchRegularExpression('/WS_BT_GRID_[A-Z0-9_]*_R[3-9]_/', $content);
        $this->assertStringNotContainsString('WS_BT_GRID_ENTRY_QUALITY_R3', $content);
        $this->assertStringNotContainsString('WS_BT_GRID_ENTRY_QUALITY_R4', $content);
        $this->assertStringNotContainsString('WS_BT_GRID_ENTRY_QUALITY_R5', $content);
    }

    public function test_every_c01_axis_has_an_explicit_runtime_consumer(): void
    {
        $universe = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php'));
        $scoring = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistScoringService.php'));
        $grouping = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php'));

        foreach (['min_dv20_idr', 'min_atr14_pct', 'max_atr14_pct'] as $field) {
            $this->assertStringContainsString($field, $universe);
        }
        foreach ([
            'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            "'momentum'", "'breakout'", "'volume'", "'risk'",
        ] as $field) {
            $this->assertStringContainsString($field, $scoring);
        }
        foreach (['top_min_score_q', 'secondary_min_score_q'] as $field) {
            $this->assertStringContainsString($field, $grouping);
        }
    }

    public function test_locked_gate_and_oos_contract_files_were_not_modified(): void
    {
        $file16 = base_path('docs/watchlist/system/policies/weekly_swing/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md');
        $file17 = base_path('docs/watchlist/system/policies/weekly_swing/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md');

        $this->assertSame('31299d858b68ee351ae898f4c9380d8753a65d8a', sha1_file($file16));
        $this->assertSame('39519a391158a7b2dcf7b6e989079788d61669be', sha1_file($file17));
    }
}
