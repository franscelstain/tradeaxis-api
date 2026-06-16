<?php

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;

class WatchlistBacktestC19StaticGuardTest extends TestCase
{
    public function test_c19_selection_diagnostic_command_is_registered_not_scheduled_and_is_only(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC19SelectionDiagnoseCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c19-selection-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC19SelectionDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('IS-only selection model redesign diagnostic/prototype', $command);
        $this->assertStringContainsString('c19_catalog_implementation_deferred', $command);
        $this->assertStringContainsString('oos_service_invoked', $command);
        $this->assertStringContainsString('oos_repository_invoked', $command);
        $this->assertStringContainsString('oos_executed', $command);
        $this->assertStringContainsString('production_ready', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c19-selection-diagnose", $kernel);
    }

    public function test_c19_phase_a_b_diagnostic_does_not_mutate_prior_catalogs_or_create_c19_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC19SelectionModelRedesignAnalysisService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertSame('d411bfbee6fb14c17d821aa92e7e0fea06925d67', WatchlistBacktestC17ParamGridCatalog::hash());
        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06', WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C17', WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT);

        $this->assertStringContainsString('C19_SELECTION_MODEL_REDESIGN_ANALYSIS', $service);
        $this->assertStringContainsString('C19_STRATEGY_MODEL_REDESIGN', $service);
        $this->assertStringContainsString('C19_NOT_CATALOG_CHURN', $service);
        $this->assertStringContainsString('C19_CATALOG_IMPLEMENTATION_DEFERRED', $service);
        $this->assertStringContainsString("'C19_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString('DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE', $service);
        $this->assertStringContainsString('PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED', $service);
        $this->assertStringContainsString('C01_TO_C18_IMMUTABLE', $service);

        $this->assertStringNotContainsString('WatchlistBacktestC19ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC19ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC19ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC19ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC19ParamGridSeeder.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertDoesNotMatchRegularExpression('/[\'\"]production_ready[\'\"]\s*=>\s*true\b/', $service);
        $this->assertDoesNotMatchRegularExpression('/ticker_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/month_blacklist\s*=>\s*\[/', $service);
    }

    public function test_c19_selection_diagnostic_preserves_watchlist_pipeline_boundary(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC19SelectionModelRedesignAnalysisService.php'));

        $this->assertStringContainsString('WatchlistCandidateUniverseService', $service);
        $this->assertStringContainsString('WatchlistScoringService', $service);
        $this->assertStringContainsString('WatchlistPlanGroupingService', $service);
        $this->assertStringContainsString('WatchlistRecommendationService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestPublishedPriceRuntimeService', $service);
        $this->assertStringContainsString('ENTRY', $service);
        $this->assertStringContainsString('NEXT_OPEN', $service);
        $this->assertStringContainsString('STOP_TP_OR_TIME', $service);
        $this->assertStringNotContainsString('Broker', $service);
        $this->assertStringNotContainsString('Order', $service);
    }
}
