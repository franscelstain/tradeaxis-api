<?php

class WatchlistBacktestC14StaticGuardTest extends TestCase
{
    public function test_c14_seed_command_is_registered_explicit_and_not_scheduled(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/SeedBacktestC14ParamGridCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $seeder = file_get_contents(base_path('database/seeders/Watchlist/WatchlistBacktestC14ParamGridSeeder.php'));

        $this->assertStringContainsString('watchlist:backtest-c14-param-grid-seed', $command);
        $this->assertStringContainsString('SeedBacktestC14ParamGridCommand::class', $kernel);
        $this->assertStringContainsString('WatchlistBacktestC14ParamGridCatalog::rows()', $command);
        $this->assertStringContainsString('WatchlistBacktestC14ParamGridCatalog::rows()', $seeder);
        foreach (['r1_immutable=1', 'r2_immutable=1', 'c01_immutable=1', 'c02_immutable=1', 'c03_immutable=1', 'c04_immutable=1', 'c05_immutable=1', 'c06_immutable=1', 'c07_immutable=1'] as $marker) {
            $this->assertStringContainsString($marker, $command);
        }
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c14-param-grid-seed", $kernel);
    }

    public function test_c14_calibration_path_is_explicit_is_only_and_has_no_oos_dependency(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestC14ParamGridCatalog.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestExitAxisSupport.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'),
            base_path('app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistScoringService.php'),
            base_path('app/Console/Commands/Watchlist/RunBacktestIsCalibrationCommand.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));
        $lower = strtolower($content);

        $this->assertStringContainsString('WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06', $content);
        $this->assertStringContainsString('C14_GRID_FAILED_IS_QUALITY', $content);
        $this->assertStringContainsString('WATCHLIST_C14_IS_CALIBRATION_V1', $content);
        $this->assertStringContainsString('WEEKLY_SWING_DOWNSIDE_STABILITY_C14_VARIABLE_RISK_EXIT_IS_ONLY', $content);
        $this->assertStringContainsString('VARIABLE_RISK_EXIT_AXIS_V1', $content);
        $this->assertStringContainsString("['C07', 'C14']", $content);
        $this->assertStringContainsString('C07_SHORT_TERM_RANGE_SECTOR_CONFIRMATION', $content);
        $this->assertStringContainsString('requires_c07_catalog', $content);
        $this->assertStringContainsString('c07_immutability_proof', $content);
        $this->assertStringContainsString('WS_BT_C14_NO_VALID_IS_CANDIDATE', $content);
        $this->assertStringContainsString('backtest.holding_days', $content);
        $this->assertStringContainsString('backtest.target_pct', $content);
        $this->assertStringContainsString('backtest.stop_pct', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $content);
        $this->assertStringNotContainsString("DB::table('eod_indicators')", $content);
        $this->assertStringNotContainsString('promoteparamset', $lower);
    }

    public function test_locked_gate_and_oos_contract_files_were_not_modified(): void
    {
        $file16 = base_path('docs/watchlist/system/policies/weekly_swing/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md');
        $file17 = base_path('docs/watchlist/system/policies/weekly_swing/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md');

        $this->assertSame('31299d858b68ee351ae898f4c9380d8753a65d8a', sha1_file($file16));
        $this->assertSame('39519a391158a7b2dcf7b6e989079788d61669be', sha1_file($file17));
    }
}
