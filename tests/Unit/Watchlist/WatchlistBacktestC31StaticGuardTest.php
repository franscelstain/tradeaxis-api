<?php

class WatchlistBacktestC31StaticGuardTest extends TestCase
{
    public function test_C31_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC31ControlledGateReclassificationCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC31ControlledGateReclassificationService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c31-controlled-gate-reclassification', $command);
        $this->assertStringContainsString('RunBacktestC31ControlledGateReclassificationCommand::class', $kernel);
        $this->assertStringContainsString('C31_CONTROLLED_GATE_RECLASSIFICATION', $service);
        $this->assertStringContainsString('c29-artifact', $command);
        $this->assertStringContainsString('expected-c29-hash', $command);
        $this->assertStringContainsString('c30-artifact', $command);
        $this->assertStringContainsString('expected-c30-hash', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c31-controlled-gate-reclassification", $kernel);
    }

    public function test_C31_does_not_mutate_C01_to_C30_artifacts_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC31ControlledGateReclassificationService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC31ControlledGateReclassificationCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C30_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('CONTROLLED_GATE_RECLASSIFICATION_ONLY', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $command);
        $this->assertStringNotContainsString('watchlist:backtest-c30-oos-failure-attribution', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC31ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC31ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC31ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC31ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC31ParamGridSeeder.php'));
    }

    public function test_C31_does_not_use_oos_returns_for_profile_selection_or_best_of_oos(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC31ControlledGateReclassificationService.php'));
        $normalized = str_replace(['NO_BEST_OF_OOS', 'NO_PROFILE_RESELECTION'], '', $service);

        $this->assertStringContainsString('NO_RETUNE', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('oos_return_used_for_profile_selection', $service);
        $this->assertStringNotContainsString('best_profile_binding_allowed', $service);
        $this->assertStringNotContainsString('best_profile_code_by_avg', $service);
        $this->assertStringNotContainsString('best_profile_code_by_median', $service);
        $this->assertStringNotContainsString('best_profile_code_by_p25', $service);
        $this->assertStringNotContainsString('best_of_oos', strtolower($normalized));
        $this->assertStringNotContainsString('candidate_reselection', strtolower($service));
        $this->assertStringNotContainsString('profile_reselection', strtolower($normalized));
    }

    public function test_C31_forbidden_leak_flags_are_not_selection_or_execution_enablers(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC31ControlledGateReclassificationService.php'));

        $this->assertStringContainsString('future_path_price_used_for_selection', $service);
        $this->assertStringContainsString('profile_ret_net_used_for_selection', $service);
        $this->assertStringContainsString('derived_mfe_mae_used_for_execution', $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'derived_mfe_mae_used_for_execution' => false", $service);
        $this->assertStringNotContainsString('selection_enabler', $service);
        $this->assertStringNotContainsString('enable_selection_from_future_path', $service);
        $this->assertStringNotContainsString('execution_enabler', $service);
        $this->assertStringNotContainsString('enable_execution_from_mfe_mae', $service);
    }

    public function test_C31_expected_hashes_and_production_ready_false_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC31ControlledGateReclassificationService.php'));

        $this->assertStringContainsString('c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9', $service);
        $this->assertStringContainsString('667b639951d6b566cc9b0fa6cf7dc278db92a8f0', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c30-oos-failure-attribution.json', $service);
        $this->assertStringContainsString('C29_OOS_PROOF_FAILED', $service);
        $this->assertStringContainsString('C30_ATTRIBUTION_COMPLETED', $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'production_ready' => 0", $service);
        $this->assertStringNotContainsString('production_ready=true', strtolower($service));
    }

    public function test_C31_preserves_execution_model_and_separates_actual_lookahead_from_data_completeness(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC31ControlledGateReclassificationService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('actual_lookahead_gate', $service);
        $this->assertStringContainsString('data_completeness_gate', $service);
        $this->assertStringContainsString('C31_ACTUAL_LOOKAHEAD_GATE_PASS_NO_ACTUAL_LEAK', $service);
        $this->assertStringContainsString('C31_DATA_COMPLETENESS_GATE_FAIL_MISSING_PATH', $service);
        $this->assertStringContainsString('actual_lookahead_gate_separated_from_data_completeness_gate', $service);
    }
}
