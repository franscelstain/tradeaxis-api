<?php

class WatchlistBacktestC32StaticGuardTest extends TestCase
{
    public function test_C32_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC32DataPathAndBadMonthDiagnosticCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC32DataPathAndBadMonthDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c32-data-path-and-bad-month-diagnostic', $command);
        $this->assertStringContainsString('RunBacktestC32DataPathAndBadMonthDiagnosticCommand::class', $kernel);
        $this->assertStringContainsString('C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC', $service);
        $this->assertStringContainsString('c31-artifact', $command);
        $this->assertStringContainsString('expected-c31-hash', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c32-data-path-and-bad-month-diagnostic", $kernel);
    }

    public function test_C32_does_not_mutate_C01_to_C31_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC32DataPathAndBadMonthDiagnosticService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC32DataPathAndBadMonthDiagnosticCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C31_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_ONLY', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c31-controlled-gate-reclassification', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC32ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC32ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC32ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC32ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC32ParamGridSeeder.php'));
    }

    public function test_C32_does_not_use_oos_returns_for_profile_selection_or_best_of_oos(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC32DataPathAndBadMonthDiagnosticService.php'));
        $normalized = str_replace(['NO_BEST_OF_OOS', 'NO_PROFILE_RESELECTION', 'profile_reselection_allowed'], '', $service);

        $this->assertStringContainsString('NO_RETUNE', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('oos_return_used_for_profile_selection', $service);
        $this->assertStringContainsString("'oos_tuning_allowed' => false", $service);
        $this->assertStringContainsString("'profile_reselection_allowed' => false", $service);
        $this->assertStringContainsString("'production_promotion_allowed' => false", $service);
        $this->assertStringNotContainsString('best_profile_binding_allowed', $service);
        $this->assertStringNotContainsString('best_profile_code_by_avg', $service);
        $this->assertStringNotContainsString('best_profile_code_by_median', $service);
        $this->assertStringNotContainsString('best_of_oos', strtolower($normalized));
        $this->assertStringNotContainsString('candidate_reselection', strtolower($service));
        $this->assertStringNotContainsString('profile_reselection', strtolower($normalized));
    }

    public function test_C32_forbidden_leak_flags_are_not_selection_or_execution_enablers(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC32DataPathAndBadMonthDiagnosticService.php'));

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

    public function test_C32_expected_C31_hash_and_production_ready_false_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC32DataPathAndBadMonthDiagnosticService.php'));

        $this->assertStringContainsString('4c6203621ed53ade368328a3aad567cbfc12f3a0', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json', $service);
        $this->assertStringContainsString('C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED', $service);
        $this->assertStringContainsString('C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK', $service);
        $this->assertStringContainsString('C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS', $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'production_ready' => 0", $service);
        $this->assertStringNotContainsString('production_ready=true', strtolower($service));
    }

    public function test_C32_preserves_execution_model_and_required_split_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC32DataPathAndBadMonthDiagnosticService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('data_path_remediation_scope', $service);
        $this->assertStringContainsString('missing_path_replay_rows', $service);
        $this->assertStringContainsString('bad_month_robustness_summary', $service);
        $this->assertStringContainsString('source_branch_robustness_summary', $service);
        $this->assertStringContainsString('C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED', $service);
    }
}
