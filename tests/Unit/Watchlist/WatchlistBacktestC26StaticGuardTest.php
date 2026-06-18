<?php

class WatchlistBacktestC26StaticGuardTest extends TestCase
{
    public function test_C26_command_is_registered_not_scheduled_and_supports_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC26CatalogCandidateDiagnoseCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC26CatalogCandidateDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c26-catalog-candidate-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC26CatalogCandidateDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('C26_CATALOG_CANDIDATE_DIAGNOSTIC', $service);
        $this->assertStringContainsString('IS_ONLY_CATALOG_CANDIDATE_DIAGNOSTIC', $service);
        $this->assertStringContainsString('diagnostic-profile-codes', $command);
        $this->assertStringContainsString('profile-codes', $command);
        $this->assertStringContainsString('param-ids', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringContainsString('max-picks', $command);
        $this->assertStringContainsString('max-params', $command);
        $this->assertStringContainsString('max-diagnostic-profiles', $command);
        $this->assertStringContainsString('input-c21-artifact', $command);
        $this->assertStringContainsString('input-c23-artifact', $command);
        $this->assertStringContainsString('input-c24-artifact', $command);
        $this->assertStringContainsString('input-c25-artifact', $command);
        $this->assertStringContainsString('candidate-profile-code', $command);
        $this->assertStringContainsString('defensive-comparator-code', $command);
        $this->assertStringContainsString('next-open-delay-comparator-code', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c26-catalog-candidate-diagnose", $kernel);
    }

    public function test_C26_does_not_create_catalog_mutate_previous_catalogs_or_invoke_oos_paths(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC26CatalogCandidateDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString("'C26_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'C26_CATALOG_IMPLEMENTATION_DEFERRED' => true", $service);
        $this->assertStringContainsString("'NO_C01_TO_C25_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_C25_REOPEN' => true", $service);
        $this->assertStringContainsString("'catalog_allowed' => false", $service);
        $this->assertStringContainsString("'oos_allowed' => false", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'diagnostic_profiles_used_as_production_rule' => false", $service);
        $this->assertStringContainsString("'close_signal_same_day_exit_allowed' => false", $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertStringNotContainsString('WatchlistBacktestC26ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC26ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC26ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC26ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC26ParamGridSeeder.php'));
    }

    public function test_C26_preserves_canonical_model_and_realistic_execution_rules(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC26CatalogCandidateDiagnosticService.php'));

        $this->assertStringContainsString("'ENTRY' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'EXIT' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'HOLD' => 5", $service);
        $this->assertStringContainsString("'FEE' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'SLIP' => 0", $service);
        $this->assertStringContainsString("'GAP' => 'OPEN'", $service);
        $this->assertStringContainsString("'PX' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('preplanned_order_threshold_fixed_before_path_evaluation', $service);
        $this->assertStringContainsString('STOP_FIRST_IF_TARGET_AND_STOP_SAME_DAILY_CANDLE', $service);
        $this->assertStringContainsString("'d1_close_signal_min_exit_day' => 2", $service);
        $this->assertStringContainsString("'d2_close_signal_min_exit_day' => 3", $service);
        $this->assertStringContainsString("'d3_close_signal_min_exit_day' => 4", $service);
        $this->assertStringContainsString('RAW_HIGH_LOW_VALIDATION_REQUIRED=true', $service);
        $this->assertStringContainsString('C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE', $service);
        $this->assertStringContainsString('C26_G04_C25_G13_DEFENSIVE_DISTRIBUTION_COMPARATOR', $service);
        $this->assertStringContainsString('C26_G05_C25_G16_NEXT_OPEN_DELAY_COMPARATOR', $service);
        $this->assertStringContainsString('C26_G16_CATALOG_CANDIDATE_READINESS_SCORE', $service);
    }
}
