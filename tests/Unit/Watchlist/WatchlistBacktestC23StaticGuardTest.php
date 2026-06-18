<?php

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;

class WatchlistBacktestC23StaticGuardTest extends TestCase
{
    public function test_C23_command_is_registered_not_scheduled_and_keeps_rule_candidate_diagnostic_boundaries(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC23FirstProfitCaptureRuleDiagnoseCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c23-first-profit-capture-rule-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC23FirstProfitCaptureRuleDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('IS-only first profit capture rule diagnostic', $command);
        $this->assertStringContainsString('C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC', $service);
        $this->assertStringContainsString('IS_ONLY_FIRST_PROFIT_CAPTURE_RULE_CANDIDATE_DIAGNOSTIC', $service);
        $this->assertStringContainsString('{--rule-profile-codes=', $command);
        $this->assertStringContainsString('{--profile-codes=', $command);
        $this->assertStringContainsString('{--param-ids=', $command);
        $this->assertStringContainsString('{--progress', $command);
        $this->assertStringContainsString('{--max-params=', $command);
        $this->assertStringContainsString('{--max-picks=', $command);
        $this->assertStringContainsString('{--max-rule-profiles=', $command);
        $this->assertStringContainsString('{--selection-output=', $command);
        $this->assertStringContainsString('{--reuse-selection-artifact', $command);
        $this->assertStringContainsString('reuse_selection_artifact', $command);
        $this->assertStringContainsString('c23_catalog_implementation_deferred', $command);
        $this->assertStringContainsString('oos_service_invoked', $command);
        $this->assertStringContainsString('oos_repository_invoked', $command);
        $this->assertStringContainsString('oos_executed', $command);
        $this->assertStringContainsString('production_ready', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c23-first-profit-capture-rule-diagnose", $kernel);
    }

    public function test_C23_does_not_mutate_prior_catalogs_create_catalog_or_change_canonical_model(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertSame('d411bfbee6fb14c17d821aa92e7e0fea06925d67', WatchlistBacktestC17ParamGridCatalog::hash());
        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06', WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C17', WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT);

        $this->assertStringContainsString('DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE', $service);
        $this->assertStringContainsString("'C23_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'NO_C01_TO_C22_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_C19_REOPEN' => true", $service);
        $this->assertStringContainsString("'NO_C20_REOPEN' => true", $service);
        $this->assertStringContainsString("'NO_C21_REOPEN' => true", $service);
        $this->assertStringContainsString("'NO_C22_REOPEN' => true", $service);
        $this->assertStringContainsString("'ENTRY' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'EXIT' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'HOLD' => 5", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'rule_exit_used_for_selection' => false", $service);
        $this->assertStringContainsString("'rule_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'c22_shadow_s06_used_for_selection' => false", $service);
        $this->assertStringContainsString("'mfe_mae_used_for_selection' => false", $service);

        $this->assertStringNotContainsString('WatchlistBacktestC23ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC23ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC23ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC23ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC23ParamGridSeeder.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertDoesNotMatchRegularExpression('/[\'\"]production_ready[\'\"]\s*=>\s*true\b/', $service);
        $this->assertDoesNotMatchRegularExpression('/ticker_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/month_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/sector_whitelist\s*=>\s*\[/', $service);
    }

    public function test_C23_uses_future_path_price_for_measurement_after_fixed_selection_only(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService.php'));

        $this->assertStringContainsString('WS_C23_FIXED_RECOMMENDATION_BEFORE_PATH_READ', $service);
        $this->assertStringContainsString('future_path_price_used_for_measurement_only', $service);
        $this->assertStringContainsString('future_path_price_used_for_selection', $service);
        $this->assertStringContainsString('rule_ret_net_used_for_selection', $service);
        $this->assertStringContainsString('mfe_mae_used_for_selection', $service);
        $this->assertStringContainsString('C23_R00_CANONICAL_BASELINE', $service);
        $this->assertStringContainsString('C23_R18_COMBO_D1_D2_PROFIT_CAPTURE_GT_0_50PCT_OR_D3_DAMAGE_CONTROL', $service);
        $this->assertStringContainsString('lookahead_safe', $service);
        $this->assertStringContainsString('lookahead_violation_reason', $service);
        $this->assertStringContainsString('rule_signal_day_offset', $service);
        $this->assertStringContainsString('rule_exit_day_offset', $service);
        $this->assertStringNotContainsString('PROMISING_CONTINUE_TO_C23_TUNING', $service);
    }
}
