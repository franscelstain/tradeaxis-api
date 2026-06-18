<?php

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;

class WatchlistBacktestC21StaticGuardTest extends TestCase
{
    public function test_c21_command_is_registered_not_scheduled_and_keeps_diagnostic_boundaries(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC21EntryExitBehaviorDiagnoseCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC21EntryExitBehaviorDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c21-entry-exit-behavior-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC21EntryExitBehaviorDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('IS-only entry/exit behavior diagnostic', $command);
        $this->assertStringContainsString('C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC', $service);
        $this->assertStringContainsString('IS_ONLY_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC', $service);
        $this->assertStringContainsString('{--profile-codes=', $command);
        $this->assertStringContainsString('{--param-ids=', $command);
        $this->assertStringContainsString('{--progress', $command);
        $this->assertStringContainsString('{--max-params=', $command);
        $this->assertStringContainsString('{--max-picks=', $command);
        $this->assertStringContainsString('c21_catalog_implementation_deferred', $command);
        $this->assertStringContainsString('oos_service_invoked', $command);
        $this->assertStringContainsString('oos_repository_invoked', $command);
        $this->assertStringContainsString('oos_executed', $command);
        $this->assertStringContainsString('production_ready', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c21-entry-exit-behavior-diagnose", $kernel);
    }

    public function test_c21_does_not_mutate_prior_catalogs_create_catalog_or_change_canonical_model(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC21EntryExitBehaviorDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertSame('d411bfbee6fb14c17d821aa92e7e0fea06925d67', WatchlistBacktestC17ParamGridCatalog::hash());
        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06', WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C17', WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT);

        $this->assertStringContainsString('DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE', $service);
        $this->assertStringContainsString("'C21_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'NO_C01_TO_C20_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_C19_REOPEN' => true", $service);
        $this->assertStringContainsString("'NO_C20_REOPEN' => true", $service);
        $this->assertStringContainsString("'ENTRY' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'EXIT' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'HOLD' => 5", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'c20_g03_used_as_filter' => false", $service);

        $this->assertStringNotContainsString('WatchlistBacktestC21ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC21ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC21ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC21ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC21ParamGridSeeder.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertDoesNotMatchRegularExpression('/[\'\"]production_ready[\'\"]\s*=>\s*true\b/', $service);
        $this->assertDoesNotMatchRegularExpression('/ticker_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/month_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/sector_whitelist\s*=>\s*\[/', $service);
    }

    public function test_c21_uses_future_path_price_for_measurement_after_fixed_selection_only(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC21EntryExitBehaviorDiagnosticService.php'));

        $this->assertStringContainsString('WS_C21_FIXED_RECOMMENDATION_BEFORE_PATH_READ', $service);
        $this->assertStringContainsString('future_path_price_used_for_measurement_only', $service);
        $this->assertStringContainsString('future_path_price_used_for_selection', $service);
        $this->assertStringContainsString('C20_G03_VOLATILITY_RISK_OFF_FILTER', $service);
        $this->assertStringContainsString('segmentation_only', $service);
        $this->assertStringNotContainsString('PROMISING_CONTINUE_TO_C21_TUNING', $service);
    }
}
