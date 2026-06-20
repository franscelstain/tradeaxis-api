<?php

class WatchlistBacktestC33StaticGuardTest extends TestCase
{
    public function test_C33_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC33DataPathReplayProofCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC33DataPathReplayProofService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c33-data-path-replay-proof', $command);
        $this->assertStringContainsString('RunBacktestC33DataPathReplayProofCommand::class', $kernel);
        $this->assertStringContainsString('C33_DATA_PATH_REPLAY_PROOF', $service);
        $this->assertStringContainsString('c32-artifact', $command);
        $this->assertStringContainsString('expected-c32-hash', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c33-data-path-replay-proof", $kernel);
    }

    public function test_C33_does_not_mutate_C01_to_C32_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC33DataPathReplayProofService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC33DataPathReplayProofCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C32_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('DATA_PATH_REPLAY_PROOF_ONLY', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c33-data-path-replay-proof.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c32-data-path-and-bad-month-diagnostic', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC33ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC33ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC33ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC33ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC33ParamGridSeeder.php'));
    }

    public function test_C33_does_not_use_oos_returns_for_profile_selection_or_best_of_oos(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC33DataPathReplayProofService.php'));
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

    public function test_C33_forbidden_leak_flags_are_not_selection_or_execution_enablers(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC33DataPathReplayProofService.php'));

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

    public function test_C33_expected_C32_lock_and_production_ready_false_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC33DataPathReplayProofService.php'));

        $this->assertStringContainsString('4bd92dfcf70dd0b02398d3ecf62d08c0356292ab', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json', $service);
        $this->assertStringContainsString('C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED', $service);
        $this->assertStringContainsString('C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED', $service);
        $this->assertStringContainsString('C32_DATA_PATH_REMEDIATION_REQUIRED', $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'production_ready' => 0", $service);
        $this->assertStringNotContainsString('production_ready=true', strtolower($service));
    }

    public function test_C33_read_only_data_path_replay_boundaries_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC33DataPathReplayProofService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC33DataPathReplayProofCommand.php'));

        $this->assertStringContainsString('READ_ONLY_CURRENT_EOD_BARS_REPLAY_PROOF', $service);
        $this->assertStringContainsString('NO_SOURCE_ACQUISITION', $service);
        $this->assertStringContainsString('NO_BAR_INGEST', $service);
        $this->assertStringContainsString('NO_SOURCE_MASTER_WRITE', $service);
        $this->assertStringContainsString('NO_EOD_BARS_WRITE', $service);
        $this->assertStringContainsString("'source_acquisition_executed' => false", $service);
        $this->assertStringContainsString("'bar_ingest_executed' => false", $service);
        $this->assertStringContainsString("'source_master_write_executed' => false", $service);
        $this->assertStringContainsString("'eod_bars_write_executed' => false", $service);
        $this->assertStringContainsString("DB::table('market_calendar')", $service);
        $this->assertStringContainsString("DB::table('eod_bars as bar')", $service);
        $this->assertStringContainsString("->whereIn('bar.trade_date', \$dates)", $service);
        $this->assertStringNotContainsString('market-data:daily', $command);
        $this->assertStringNotContainsString('market-data:backfill', $command);
        $this->assertStringNotContainsString('market-data:ingest', $command);
        $this->assertStringNotContainsString('->insert(', $service);
        $this->assertStringNotContainsString('->update(', $service);
        $this->assertStringNotContainsString('->delete(', $service);
    }

    public function test_C33_preserves_execution_model_and_required_replay_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC33DataPathReplayProofService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('D1_TO_D5_RAW_OHLC_PATH', $service);
        $this->assertStringContainsString('required_path_dates', $service);
        $this->assertStringContainsString('available_path_dates', $service);
        $this->assertStringContainsString('missing_path_dates', $service);
        $this->assertStringContainsString('invalid_path_dates', $service);
        $this->assertStringContainsString('data_completeness_gate_after_replay', $service);
        $this->assertStringContainsString('C33_DATA_PATH_REPLAY_PASS', $service);
        $this->assertStringContainsString('C33_DATA_PATH_REPLAY_FAILED_MISSING_OR_INVALID_PATH', $service);
        $this->assertStringContainsString('C33_DATA_PATH_REPLAY_BLOCKED_RUNTIME_OR_CALENDAR_UNAVAILABLE', $service);
    }
}
