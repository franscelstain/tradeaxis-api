<?php

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;

class WatchlistBacktestC20StaticGuardTest extends TestCase
{
    public function test_c20_command_is_registered_not_scheduled_and_keeps_diagnostic_boundaries(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC20RegimeTradeDateDiagnoseCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC20RegimeTradeDateDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c20-regime-trade-date-diagnose', $command);
        $this->assertStringContainsString('RunBacktestC20RegimeTradeDateDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('IS-only regime and trade-date quality gate diagnostic', $command);
        $this->assertStringContainsString('C20_REGIME_TRADE_DATE_DIAGNOSTIC', $service);
        $this->assertStringContainsString('IS_ONLY_REGIME_TRADE_DATE_DIAGNOSTIC', $service);
        $this->assertStringContainsString('{--profile-codes=', $command);
        $this->assertStringContainsString('{--param-ids=', $command);
        $this->assertStringContainsString('{--progress', $command);
        $this->assertStringContainsString('{--max-profiles=', $command);
        $this->assertStringContainsString('{--max-params=', $command);
        $this->assertStringContainsString('c20_catalog_implementation_deferred', $command);
        $this->assertStringContainsString('oos_service_invoked', $command);
        $this->assertStringContainsString('oos_repository_invoked', $command);
        $this->assertStringContainsString('oos_executed', $command);
        $this->assertStringContainsString('production_ready', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c20-regime-trade-date-diagnose", $kernel);
    }

    public function test_c20_does_not_mutate_prior_catalogs_or_create_c20_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC20RegimeTradeDateDiagnosticService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertSame('d411bfbee6fb14c17d821aa92e7e0fea06925d67', WatchlistBacktestC17ParamGridCatalog::hash());
        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06', WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C17', WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT);

        $this->assertStringContainsString('DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE', $service);
        $this->assertStringContainsString("'C20_CATALOG_CODE' => 'NOT_CREATED'", $service);
        $this->assertStringContainsString("'NO_C01_TO_C19_MUTATION' => true", $service);
        $this->assertStringContainsString("'NO_TICKER_BLACKLIST' => true", $service);
        $this->assertStringContainsString("'NO_MONTH_BLACKLIST' => true", $service);
        $this->assertStringContainsString("'NO_SECTOR_WHITELIST' => true", $service);
        $this->assertStringContainsString("'date_gate_uses_price_outcome' => false", $service);

        $this->assertStringNotContainsString('WatchlistBacktestC20ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC20ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC20ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC20ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC20ParamGridSeeder.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $service);
        $this->assertDoesNotMatchRegularExpression('/[\'\"]production_ready[\'\"]\s*=>\s*true\b/', $service);
        $this->assertDoesNotMatchRegularExpression('/ticker_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/month_blacklist\s*=>\s*\[/', $service);
        $this->assertDoesNotMatchRegularExpression('/sector_whitelist\s*=>\s*\[/', $service);
    }

    public function test_c20_gate_uses_trade_date_eod_features_not_price_outcome_or_future_exit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC20RegimeTradeDateDiagnosticService.php'));

        $this->assertStringContainsString('date_gate_features_frozen_before_price_read', $service);
        $this->assertStringContainsString('future_price_used_for_trade_date_gate', $service);
        $this->assertStringContainsString('date_gate_feature_cutoff', $service);
        $this->assertStringContainsString('trade_date_eod_only', $service);
        $this->assertStringContainsString('uses_price_outcome_for_gate', $service);
        $this->assertStringContainsString('MarketBenchmarkReadService', $service);
        $this->assertStringContainsString('score_metrics', $service);
        preg_match('/private function dateGateDecision[\s\S]+?private function proposalItemsToTrades/', $service, $match);
        $dateGateBlock = $match[0] ?? '';
        $this->assertStringNotContainsString('exit_reason_code', $dateGateBlock);
        $this->assertStringNotContainsString('ret_net', $dateGateBlock);
    }
}
