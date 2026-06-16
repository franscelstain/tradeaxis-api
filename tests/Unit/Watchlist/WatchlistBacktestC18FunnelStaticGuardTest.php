<?php

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;

class WatchlistBacktestC18FunnelStaticGuardTest extends TestCase
{
    public function test_c18_funnel_diagnostic_command_is_registered_not_scheduled_and_is_only(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC18FunnelDiagnoseCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c18-funnel-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC18FunnelDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('scope=IS_ONLY_DIAGNOSTIC', $command);
        $this->assertStringContainsString('oos_service_invoked=0', $command);
        $this->assertStringContainsString('oos_repository_invoked=0', $command);
        $this->assertStringContainsString('oos_executed=0', $command);
        $this->assertStringContainsString('production_ready=0', $command);
        $this->assertStringContainsString('{--deep-funnel', $command);
        $this->assertStringContainsString('{--progress-every=25', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c18-funnel-diagnose", $kernel);
    }

    public function test_c18_phase_a_diagnostic_does_not_mutate_c17_or_create_c18_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC18FunnelDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertSame('d411bfbee6fb14c17d821aa92e7e0fea06925d67', WatchlistBacktestC17ParamGridCatalog::hash());
        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06', WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C17', WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT);

        $this->assertStringContainsString('C18_PHASE_A_DIAGNOSTIC_FIRST_FUNNEL_AUDIT', $service);
        $this->assertStringContainsString('does_not_execute_oos', $service);
        $this->assertStringContainsString('does_not_lower_canonical_gates', $service);
        $this->assertStringContainsString('does_not_promote_paramset', $service);
        $this->assertStringContainsString('C18_CATALOG_IMPLEMENTATION_DEFERRED', $service);
        $this->assertStringContainsString('DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE', $service);
        $this->assertStringContainsString('plan_recommendation_confirm_boundary_unchanged', $service);
        $this->assertStringContainsString('c01_to_c17_immutable', $service);

        $this->assertStringNotContainsString('WatchlistBacktestC18ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC18ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC18ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC18ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC18ParamGridSeeder.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertDoesNotMatchRegularExpression(
            '/[\'\"]production_ready[\'\"]\s*=>\s*true\b/',
            $service
        );
        $this->assertStringContainsString("'production_ready' => false", $service);
    }

    public function test_c18_funnel_diagnostic_preserves_watchlist_pipeline_boundary(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC18FunnelDiagnosticService.php'));

        $this->assertStringContainsString('WatchlistCandidateUniverseService', $service);
        $this->assertStringContainsString('WatchlistScoringService', $service);
        $this->assertStringContainsString('WatchlistPlanGroupingService', $service);
        $this->assertStringContainsString('WatchlistRecommendationService', $service);
        $this->assertStringContainsString('WatchlistBacktestPublishedPriceRuntimeService', $service);
        $this->assertStringContainsString('plan_recommendation_confirm_boundary_unchanged', $service);
        $this->assertStringNotContainsString('Broker', $service);
        $this->assertStringNotContainsString('Order', $service);
    }
}
