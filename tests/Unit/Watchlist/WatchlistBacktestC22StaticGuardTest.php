<?php

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;

class WatchlistBacktestC22StaticGuardTest extends TestCase
{
    public function test_c22_command_is_registered_not_scheduled_and_keeps_shadow_diagnostic_boundaries(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC22ExitCaptureShadowDiagnoseCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC22ExitCaptureShadowDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c22-exit-capture-shadow-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC22ExitCaptureShadowDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('IS-only exit capture shadow diagnostic', $command);
        $this->assertStringContainsString('C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC', $service);
        $this->assertStringContainsString('IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC', $service);
        $this->assertStringContainsString('{--shadow-profile-codes=', $command);
        $this->assertStringContainsString('{--profile-codes=', $command);
        $this->assertStringContainsString('{--param-ids=', $command);
        $this->assertStringContainsString('{--progress', $command);
        $this->assertStringContainsString('{--max-params=', $command);
        $this->assertStringContainsString('{--max-picks=', $command);
        $this->assertStringContainsString('{--max-shadow-profiles=', $command);
        $this->assertStringContainsString('c22_catalog_implementation_deferred', $command);
        $this->assertStringContainsString('oos_service_invoked', $command);
        $this->assertStringContainsString('oos_repository_invoked', $command);
        $this->assertStringContainsString('oos_executed', $command);
        $this->assertStringContainsString('production_ready', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c22-exit-capture-shadow-diagnose", $kernel);
    }

    public function test_c22_does_not_mutate_prior_catalogs_create_catalog_or_change_canonical_model(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC22ExitCaptureShadowDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertSame('d411bfbee6fb14c17d821aa92e7e0fea06925d67', WatchlistBacktestC17ParamGridCatalog::hash());
        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06', WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C17', WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT);

        $this->assertStringContainsString('DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE', $service);
        $this->assertStringContainsString("'C22_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'NO_C01_TO_C21_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_C19_REOPEN' => true", $service);
        $this->assertStringContainsString("'NO_C20_REOPEN' => true", $service);
        $this->assertStringContainsString("'NO_C21_REOPEN' => true", $service);
        $this->assertStringContainsString("'ENTRY' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'EXIT' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'HOLD' => 5", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'shadow_exit_used_for_selection' => false", $service);
        $this->assertStringContainsString("'shadow_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'mfe_mae_used_for_selection' => false", $service);

        $this->assertStringNotContainsString('WatchlistBacktestC22ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC22ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC22ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC22ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC22ParamGridSeeder.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertDoesNotMatchRegularExpression('/[\'\"]production_ready[\'\"]\s*=>\s*true\b/', $service);
        $this->assertDoesNotMatchRegularExpression('/ticker_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/month_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/sector_whitelist\s*=>\s*\[/', $service);
    }

    public function test_c22_uses_future_path_price_for_measurement_after_fixed_selection_only(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC22ExitCaptureShadowDiagnosticService.php'));

        $this->assertStringContainsString('WS_C22_FIXED_RECOMMENDATION_BEFORE_PATH_READ', $service);
        $this->assertStringContainsString('future_path_price_used_for_measurement_only', $service);
        $this->assertStringContainsString('future_path_price_used_for_selection', $service);
        $this->assertStringContainsString('shadow_ret_net_used_for_selection', $service);
        $this->assertStringContainsString('mfe_mae_used_for_selection', $service);
        $this->assertStringContainsString('C22_S00_CANONICAL_BASELINE', $service);
        $this->assertStringContainsString('C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT', $service);
        $this->assertStringNotContainsString('PROMISING_CONTINUE_TO_C22_TUNING', $service);
    }
}
