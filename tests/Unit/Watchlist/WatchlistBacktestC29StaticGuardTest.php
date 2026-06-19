<?php

class WatchlistBacktestC29StaticGuardTest extends TestCase
{
    public function test_C29_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC29OosProofCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC29OosProofService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c29-oos-proof', $command);
        $this->assertStringContainsString('RunBacktestC29OosProofCommand::class', $kernel);
        $this->assertStringContainsString('C29_OOS_PROOF_C28_G05', $service);
        $this->assertStringContainsString('c28-artifact', $command);
        $this->assertStringContainsString('expected-c28-hash', $command);
        $this->assertStringContainsString('candidate-profile-code', $command);
        $this->assertStringContainsString('from', $command);
        $this->assertStringContainsString('to', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c29-oos-proof", $kernel);
    }

    public function test_C29_does_not_mutate_previous_catalogs_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC29OosProofService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C28_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('production_ready', $service);
        $this->assertStringNotContainsString('WatchlistBacktestC29ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC29ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC29ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC29ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC29ParamGridSeeder.php'));
    }

    public function test_C29_does_not_use_oos_returns_for_selection_or_best_of_oos(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC29OosProofService.php'));

        $this->assertStringContainsString('NO_RETUNE', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringNotContainsString('best_profile_binding_allowed', $service);
        $this->assertStringContainsString('future_path_price_used_for_selection', $service);
        $this->assertStringContainsString('profile_ret_net_used_for_selection', $service);
        $this->assertStringContainsString('derived_mfe_mae_used_for_execution', $service);
        $this->assertStringContainsString('oos_return_used_for_profile_selection', $service);
        $this->assertStringNotContainsString('best_profile_code_by_avg', $service);
        $this->assertStringNotContainsString('best_profile_code_by_median', $service);
        $this->assertStringNotContainsString('best_profile_code_by_p25', $service);
    }

    public function test_C29_reserved_window_and_C28_hash_are_fixed_explicitly(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC29OosProofService.php'));

        $this->assertStringContainsString("public const OOS_FROM = '2025-05-22'", $service);
        $this->assertStringContainsString("public const OOS_TO = '2026-05-29'", $service);
        $this->assertStringContainsString('64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd', $service);
        $this->assertStringContainsString('C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY', $service);
        $this->assertStringContainsString("'candidate_matches_or_beats_c22' => 'RAW_R09'", $service);
        $this->assertStringContainsString("'no_rule_profit_signal_before_fallback' => 'RAW_G21'", $service);
        $this->assertStringContainsString("'next_open_delay_after_close_signal' => 'RAW_G16'", $service);
    }

    public function test_C29_preserves_canonical_execution_model_and_production_ready_false(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC29OosProofService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'production_ready' => 0", $service);
    }
}
