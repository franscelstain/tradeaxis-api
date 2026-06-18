<?php

class WatchlistBacktestC28StaticGuardTest extends TestCase
{
    public function test_C28_command_is_registered_not_scheduled_and_supports_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC28RuleRevisionTiebreakDiagnoseCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c28-rule-revision-tiebreak-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC28RuleRevisionTiebreakDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC', $service);
        $this->assertStringContainsString('IS_ONLY_RULE_REVISION_TIEBREAK_DIAGNOSTIC', $service);
        $this->assertStringContainsString('candidate-profile-code', $command);
        $this->assertStringContainsString('diagnostic-profile-codes', $command);
        $this->assertStringContainsString('profile-codes', $command);
        $this->assertStringContainsString('param-ids', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringContainsString('max-picks', $command);
        $this->assertStringContainsString('max-params', $command);
        $this->assertStringContainsString('max-diagnostic-profiles', $command);
        $this->assertStringContainsString('input-c27-artifact', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c28-rule-revision-tiebreak-diagnose", $kernel);
    }

    public function test_C28_does_not_create_catalog_mutate_previous_catalogs_or_invoke_oos_paths(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString("'C28_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'C28_CATALOG_IMPLEMENTATION_DEFERRED' => true", $service);
        $this->assertStringContainsString("'NO_C01_TO_C27_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_C27_REOPEN' => true", $service);
        $this->assertStringContainsString("'catalog_allowed' => false", $service);
        $this->assertStringContainsString("'oos_allowed' => false", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'derived_mfe_mae_used_for_execution' => false", $service);
        $this->assertStringContainsString("'best_profile_binding_allowed' => false", $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertStringNotContainsString('WatchlistBacktestC28ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC28ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC28ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC28ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC28ParamGridSeeder.php'));
    }

    public function test_C28_preserves_canonical_model_and_explicit_bucket_tiebreak_rule(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService.php'));

        $this->assertStringContainsString("'ENTRY' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'EXIT' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'HOLD' => 5", $service);
        $this->assertStringContainsString("'FEE' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'SLIP' => 0", $service);
        $this->assertStringContainsString("'GAP' => 'OPEN'", $service);
        $this->assertStringContainsString("'PX' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY', $service);
        $this->assertStringContainsString('g21_no_signal_d3_damage_control', $service);
        $this->assertStringContainsString('g16_next_open_delay_target_component', $service);
        $this->assertStringContainsString('r09_stable_candidate_bucket', $service);
        $this->assertStringContainsString("'d1_close_signal_min_exit_day' => 2", $service);
        $this->assertStringContainsString("'d2_close_signal_min_exit_day' => 3", $service);
        $this->assertStringContainsString("'d3_close_signal_min_exit_day' => 4", $service);
    }
}
