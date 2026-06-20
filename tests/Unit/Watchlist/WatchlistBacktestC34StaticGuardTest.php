<?php

class WatchlistBacktestC34StaticGuardTest extends TestCase
{
    public function test_C34_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC34BadMonthRobustnessDiagnosticCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC34BadMonthRobustnessDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c34-bad-month-robustness-diagnostic', $command);
        $this->assertStringContainsString('RunBacktestC34BadMonthRobustnessDiagnosticCommand::class', $kernel);
        $this->assertStringContainsString('C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC', $service);
        $this->assertStringContainsString('c33-artifact', $command);
        $this->assertStringContainsString('expected-c33-hash', $command);
        $this->assertStringContainsString('c32-artifact', $command);
        $this->assertStringContainsString('expected-c32-hash', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c34-bad-month-robustness-diagnostic", $kernel);
    }

    public function test_C34_does_not_mutate_C01_to_C33_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC34BadMonthRobustnessDiagnosticService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC34BadMonthRobustnessDiagnosticCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C33_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_ONLY', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c33-data-path-replay-proof', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC34ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC34ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC34ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC34ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC34ParamGridSeeder.php'));
    }

    public function test_C34_is_file_artifact_only_and_does_not_query_or_mutate_DB(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC34BadMonthRobustnessDiagnosticService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC34BadMonthRobustnessDiagnosticCommand.php'));

        $this->assertStringContainsString('FILE_ARTIFACT_DIAGNOSTIC_ONLY', $service);
        $this->assertStringContainsString('NO_MARKET_DATA_REPLAY', $service);
        $this->assertStringContainsString('NO_DB_READ', $service);
        $this->assertStringContainsString('NO_DB_WRITE', $service);
        $this->assertStringNotContainsString('DB::table', $service.$command);
        $this->assertStringNotContainsString('market-data:daily', $command);
        $this->assertStringNotContainsString('market-data:backfill', $command);
        $this->assertStringNotContainsString('market-data:ingest', $command);
        $this->assertStringNotContainsString('->insert(', $service);
        $this->assertStringNotContainsString('->update(', $service);
        $this->assertStringNotContainsString('->delete(', $service);
    }

    public function test_C34_does_not_use_oos_returns_for_profile_selection_or_best_of_oos(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC34BadMonthRobustnessDiagnosticService.php'));
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

    public function test_C34_expected_hashes_and_production_ready_false_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC34BadMonthRobustnessDiagnosticService.php'));

        $this->assertStringContainsString('84bb77871515643b203de644fd34b4c748d1b2af', $service);
        $this->assertStringContainsString('4bd92dfcf70dd0b02398d3ecf62d08c0356292ab', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c33-data-path-replay-proof.json', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json', $service);
        $this->assertStringContainsString('C33_DATA_PATH_REPLAY_PROOF_COMPLETED', $service);
        $this->assertStringContainsString('C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE', $service);
        $this->assertStringContainsString('C33_DATA_PATH_REPLAY_PASS', $service);
        $this->assertStringContainsString('C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED', $service);
        $this->assertStringContainsString('C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED', $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'production_ready' => 0", $service);
        $this->assertStringNotContainsString('production_ready=true', strtolower($service));
    }

    public function test_C34_preserves_execution_model_and_required_diagnostic_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC34BadMonthRobustnessDiagnosticService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('bad_month_diagnostic_rows', $service);
        $this->assertStringContainsString('branch_robustness_rows', $service);
        $this->assertStringContainsString('robustness_decision', $service);
        $this->assertStringContainsString('CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_AFTER_DATA_PATH_CLEARED', $service);
        $this->assertStringContainsString('C34_BRANCH_ROBUSTNESS_FAILURE_CONFIRMED', $service);
        $this->assertStringContainsString('C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS', $service);
        $this->assertStringContainsString('C35_IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC_NO_OOS_TUNING', $service);
    }
}
