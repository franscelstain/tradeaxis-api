<?php

class WatchlistBacktestC25StaticGuardTest extends TestCase
{
    public function test_C25_command_is_registered_not_scheduled_and_keeps_diagnostic_boundaries(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC25NoSignalFallbackDelayDiagnoseCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c25-no-signal-fallback-delay-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC25NoSignalFallbackDelayDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC', $service);
        $this->assertStringContainsString('IS_ONLY_NO_SIGNAL_FALLBACK_AND_NEXT_OPEN_DELAY_DIAGNOSTIC', $service);
        $this->assertStringContainsString('c25_catalog_implementation_deferred', $command);
        $this->assertStringContainsString('oos_service_invoked', $command);
        $this->assertStringContainsString('oos_repository_invoked', $command);
        $this->assertStringContainsString('oos_executed', $command);
        $this->assertStringContainsString('production_ready', $command);
        $this->assertStringContainsString('diagnostic-profile-codes', $command);
        $this->assertStringContainsString('profile-codes', $command);
        $this->assertStringContainsString('input-c23-artifact', $command);
        $this->assertStringContainsString('input-c24-artifact', $command);
        $this->assertStringContainsString('max-picks', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c25-no-signal-fallback-delay-diagnose", $kernel);
    }

    public function test_C25_does_not_create_catalog_mutate_previous_catalogs_or_invoke_oos_paths(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString("'C25_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'C25_CATALOG_IMPLEMENTATION_DEFERRED' => true", $service);
        $this->assertStringContainsString("'NO_C01_TO_C24_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_C24_REOPEN' => true", $service);
        $this->assertStringContainsString("'catalog_allowed' => false", $service);
        $this->assertStringContainsString("'oos_allowed' => false", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'diagnostic_profiles_used_as_production_rule' => false", $service);
        $this->assertStringContainsString("'close_signal_same_day_exit_allowed' => false", $service);

        $this->assertStringNotContainsString('WatchlistBacktestC25ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC25ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC25ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC25ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC25ParamGridSeeder.php'));
    }

    public function test_C25_preserves_canonical_model_and_realistic_execution_rules(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService.php'));

        $this->assertStringContainsString("'ENTRY' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'EXIT' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'HOLD' => 5", $service);
        $this->assertStringContainsString("'FEE' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'SLIP' => 0", $service);
        $this->assertStringContainsString("'PX' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('preplanned_order_threshold_fixed_before_path_evaluation', $service);
        $this->assertStringContainsString('STOP_FIRST_IF_TARGET_AND_STOP_SAME_DAILY_CANDLE', $service);
        $this->assertStringContainsString('C25_G19_NEXT_OPEN_DELAY_ROWS_ONLY_R09', $service);
        $this->assertStringContainsString('C25_G20_NO_SIGNAL_FALLBACK_ROWS_ONLY_R09', $service);
        $this->assertStringContainsString('C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT', $service);
    }
}
