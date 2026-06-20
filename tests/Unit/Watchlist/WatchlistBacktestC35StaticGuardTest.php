<?php

class WatchlistBacktestC35StaticGuardTest extends TestCase
{
    public function test_C35_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC35IsRobustnessRedesignDiagnosticCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC35IsRobustnessRedesignDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c35-is-robustness-redesign-diagnostic', $command);
        $this->assertStringContainsString('RunBacktestC35IsRobustnessRedesignDiagnosticCommand::class', $kernel);
        $this->assertStringContainsString('C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC', $service);
        $this->assertStringContainsString('c34-artifact', $command);
        $this->assertStringContainsString('expected-c34-hash', $command);
        $this->assertStringContainsString('from', $command);
        $this->assertStringContainsString('to', $command);
        $this->assertStringContainsString('is-evidence-artifact', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c35-is-robustness-redesign-diagnostic", $kernel);
    }

    public function test_C35_does_not_mutate_C01_to_C34_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC35IsRobustnessRedesignDiagnosticService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC35IsRobustnessRedesignDiagnosticCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C34_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c34-bad-month-robustness-diagnostic', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC35ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC35ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC35ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC35ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC35ParamGridSeeder.php'));
    }

    public function test_C35_does_not_run_OOS_proof_or_use_best_of_OOS_or_profile_reselection(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC35IsRobustnessRedesignDiagnosticService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC35IsRobustnessRedesignDiagnosticCommand.php'));
        $normalized = str_replace(['NO_BEST_OF_OOS', 'NO_PROFILE_RESELECTION', 'NO_CANDIDATE_RESELECTION', 'profile_reselection_allowed'], '', $service);

        $this->assertStringContainsString('NO_OOS_TUNING', $service);
        $this->assertStringContainsString('NO_OOS_PROOF', $service);
        $this->assertStringContainsString('NO_RETUNE', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('NO_CANDIDATE_RESELECTION', $service);
        $this->assertStringContainsString('oos_return_used_for_profile_selection', $service);
        $this->assertStringContainsString("'oos_data_used_for_tuning' => false", $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $command);
        $this->assertStringNotContainsString('best_profile_binding_allowed', $service);
        $this->assertStringNotContainsString('best_of_oos', strtolower($normalized));
        $this->assertStringNotContainsString('candidate_reselection', strtolower($normalized));
        $this->assertStringNotContainsString('profile_reselection', strtolower($normalized));
    }

    public function test_C35_expected_C34_hash_and_IS_only_boundaries_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC35IsRobustnessRedesignDiagnosticService.php'));

        $this->assertStringContainsString('1dcf355095334796c2f4558823a1882e71e3ed30', $service);
        $this->assertStringContainsString('C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED', $service);
        $this->assertStringContainsString('C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS', $service);
        $this->assertStringContainsString('2023-01-02', $service);
        $this->assertStringContainsString('2025-05-21', $service);
        $this->assertStringContainsString('2025-05-22', $service);
        $this->assertStringContainsString('C34_BAD_MONTHS_CONTEXT_ONLY', $service);
        $this->assertStringContainsString('oos_data_used_for_tuning', $service);
        $this->assertStringNotContainsString('production_ready=true', strtolower($service));
    }

    public function test_C35_preserves_execution_model_and_required_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC35IsRobustnessRedesignDiagnosticService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('g21_is_summary', $service);
        $this->assertStringContainsString('g16_is_summary', $service);
        $this->assertStringContainsString('is_bad_month_like_summary', $service);
        $this->assertStringContainsString('is_branch_month_matrix', $service);
        $this->assertStringContainsString('is_ticker_failure_cluster', $service);
        $this->assertStringContainsString('redesign_hypotheses', $service);
        $this->assertStringContainsString('C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK', $service);
        $this->assertStringContainsString('C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE', $service);
        $this->assertStringContainsString('C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED', $service);
    }
}
