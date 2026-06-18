<?php

class WatchlistBacktestC24StaticGuardTest extends TestCase
{
    public function test_C24_command_is_registered_not_scheduled_and_keeps_gap_bridge_boundaries(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC24C22ShadowGapBridgeDiagnoseCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC24C22ShadowGapBridgeDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC', $service);
        $this->assertStringContainsString('IS_ONLY_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC', $service);
        $this->assertStringContainsString('{--input=', $command);
        $this->assertStringContainsString('{--candidate-profile-code=', $command);
        $this->assertStringContainsString('{--overwrite', $command);
        $this->assertStringContainsString('c24_catalog_implementation_deferred', $command);
        $this->assertStringContainsString('oos_service_invoked', $command);
        $this->assertStringContainsString('oos_repository_invoked', $command);
        $this->assertStringContainsString('oos_executed', $command);
        $this->assertStringContainsString('production_ready', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose", $kernel);
    }

    public function test_C24_does_not_create_catalog_or_invoke_oos_paths(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString("'C24_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'C24_CATALOG_IMPLEMENTATION_DEFERRED' => true", $service);
        $this->assertStringContainsString("'NO_C01_TO_C23_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_C23_REOPEN' => true", $service);
        $this->assertStringContainsString("'reads_c23_artifact_only' => true", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'candidate_ret_used_for_selection' => false", $service);
        $this->assertStringContainsString("'c22_shadow_s06_used_for_selection' => false", $service);

        $this->assertStringNotContainsString('WatchlistBacktestC24ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC24ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC24ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC24ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC24ParamGridSeeder.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertDoesNotMatchRegularExpression('/[\'\"]production_ready[\'\"]\s*=>\s*true\b/', $service);
        $this->assertDoesNotMatchRegularExpression('/ticker_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/month_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/sector_whitelist\s*=>\s*\[/', $service);
    }
}
