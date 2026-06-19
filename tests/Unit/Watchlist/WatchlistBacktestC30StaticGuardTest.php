<?php

class WatchlistBacktestC30StaticGuardTest extends TestCase
{
    public function test_C30_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC30OosFailureAttributionCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC30OosFailureAttributionService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c30-oos-failure-attribution', $command);
        $this->assertStringContainsString('RunBacktestC30OosFailureAttributionCommand::class', $kernel);
        $this->assertStringContainsString('C30_OOS_FAILURE_ATTRIBUTION', $service);
        $this->assertStringContainsString('c29-artifact', $command);
        $this->assertStringContainsString('expected-c29-hash', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c30-oos-failure-attribution", $kernel);
    }

    public function test_C30_does_not_mutate_C01_to_C29_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC30OosFailureAttributionService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C29_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('FAILURE_ATTRIBUTION_ONLY', $service);
        $this->assertStringNotContainsString('WatchlistBacktestC30ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC30ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC30ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC30ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC30ParamGridSeeder.php'));
    }

    public function test_C30_does_not_use_oos_returns_for_profile_selection_or_best_of_oos(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC30OosFailureAttributionService.php'));

        $this->assertStringContainsString('NO_RETUNE', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('oos_return_used_for_profile_selection', $service);
        $this->assertStringNotContainsString('best_profile_binding_allowed', $service);
        $this->assertStringNotContainsString('best_profile_code_by_avg', $service);
        $this->assertStringNotContainsString('best_profile_code_by_median', $service);
        $this->assertStringNotContainsString('best_profile_code_by_p25', $service);
        $this->assertStringNotContainsString('best_of_oos_profile', strtolower($service));
        $this->assertStringNotContainsString('best_profile_from_oos', strtolower($service));
    }

    public function test_C30_forbidden_leak_flags_are_detection_inputs_not_selection_enablers(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC30OosFailureAttributionService.php'));

        $this->assertStringContainsString('future_path_price_used_for_selection', $service);
        $this->assertStringContainsString('profile_ret_net_used_for_selection', $service);
        $this->assertStringContainsString('derived_mfe_mae_used_for_execution', $service);
        $this->assertStringContainsString('isSelectionLeakRow', $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'derived_mfe_mae_used_for_execution' => false", $service);
        $this->assertStringNotContainsString('selection_enabler', $service);
        $this->assertStringNotContainsString('enable_selection_from_future_path', $service);
    }

    public function test_C30_expected_C29_hash_and_production_ready_false_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC30OosFailureAttributionService.php'));

        $this->assertStringContainsString('c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json', $service);
        $this->assertStringContainsString('C29_OOS_PROOF_FAILED', $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'production_ready' => 0", $service);
    }

    public function test_C30_preserves_canonical_execution_model_and_classification_boundary(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC30OosFailureAttributionService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('isMissingPathRow', $service);
        $this->assertStringContainsString('isActualLookaheadViolationRow', $service);
        $this->assertStringContainsString('missing_path_rows', $service);
        $this->assertStringContainsString('actual_lookahead_violation_rows', $service);
    }
}
