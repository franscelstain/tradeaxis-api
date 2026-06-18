<?php

class WatchlistBacktestC27StaticGuardTest extends TestCase
{
    public function test_C27_command_is_registered_not_scheduled_and_supports_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC27CatalogCandidateRawOhlcValidateCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC27CatalogCandidateRawOhlcValidationService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c27-catalog-candidate-raw-ohlc-validate', $command);
        $this->assertStringContainsString('RunBacktestC27CatalogCandidateRawOhlcValidateCommand::class', $kernel);
        $this->assertStringContainsString('C27_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION', $service);
        $this->assertStringContainsString('IS_ONLY_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION', $service);
        $this->assertStringContainsString('validation-profile-codes', $command);
        $this->assertStringContainsString('profile-codes', $command);
        $this->assertStringContainsString('param-ids', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringContainsString('max-picks', $command);
        $this->assertStringContainsString('max-params', $command);
        $this->assertStringContainsString('max-validation-profiles', $command);
        $this->assertStringContainsString('input-c26-artifact', $command);
        $this->assertStringContainsString('input-c21-artifact', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c27-catalog-candidate-raw-ohlc-validate", $kernel);
    }

    public function test_C27_does_not_create_catalog_mutate_previous_catalogs_or_invoke_oos_paths(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC27CatalogCandidateRawOhlcValidationService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString("'C27_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'C27_CATALOG_IMPLEMENTATION_DEFERRED' => true", $service);
        $this->assertStringContainsString("'NO_C01_TO_C26_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_C26_REOPEN' => true", $service);
        $this->assertStringContainsString("'catalog_allowed' => false", $service);
        $this->assertStringContainsString("'oos_allowed' => false", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'derived_mfe_mae_used_for_execution' => false", $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertStringNotContainsString('WatchlistBacktestC27ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC27ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC27ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC27ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC27ParamGridSeeder.php'));
    }

    public function test_C27_preserves_canonical_model_and_uses_raw_ohlc_before_any_next_step(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC27CatalogCandidateRawOhlcValidationService.php'));

        $this->assertStringContainsString("'ENTRY' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'EXIT' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'HOLD' => 5", $service);
        $this->assertStringContainsString("'FEE' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'SLIP' => 0", $service);
        $this->assertStringContainsString("'GAP' => 'OPEN'", $service);
        $this->assertStringContainsString("'PX' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('raw_ohlc_validation_first', $service);
        $this->assertStringContainsString('raw_high_low_used_for_execution', $service);
        $this->assertStringContainsString('preplanned_order_threshold_fixed_before_path_evaluation', $service);
        $this->assertStringContainsString('OPEN_GAP_THEN_HIGH_LOW_STOP_FIRST_IF_BOTH_SAME_DAILY_CANDLE', $service);
        $this->assertStringContainsString("'d1_close_signal_min_exit_day' => 2", $service);
        $this->assertStringContainsString("'d2_close_signal_min_exit_day' => 3", $service);
        $this->assertStringContainsString("'d3_close_signal_min_exit_day' => 4", $service);
        $this->assertStringContainsString('C27_G05_RAW_C25_G21_PRIMARY_COMBO', $service);
        $this->assertStringContainsString('C27_G03_RAW_C25_G13_TARGET_0_50PCT', $service);
        $this->assertStringContainsString('C27_G04_RAW_C25_G16_TARGET_1_50PCT', $service);
        $this->assertStringContainsString('C27_G08_CATALOG_CANDIDATE_READINESS_SCORE', $service);
    }
}
