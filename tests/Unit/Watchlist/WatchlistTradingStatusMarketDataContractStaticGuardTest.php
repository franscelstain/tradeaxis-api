<?php

class WatchlistTradingStatusMarketDataContractStaticGuardTest extends TestCase
{
    public function test_watchlist_runtime_boundaries_normalize_market_data_trading_status_snapshots(): void
    {
        foreach ([
            'app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php',
            'app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php',
            'app/Application/Watchlist/Services/WatchlistScoringService.php',
            'app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php',
            'app/Application/Watchlist/Services/WatchlistBacktestIsFailureDrilldownService.php',
            'app/Application/Watchlist/Services/WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService.php',
        ] as $relativePath) {
            $source = file_get_contents(base_path($relativePath));

            $this->assertStringContainsString('WatchlistTradingStatusSnapshotNormalizer::normalize', $source, $relativePath);
        }
    }

    public function test_watchlist_consumer_contract_uses_eod_indicator_snapshot_not_raw_trading_status_table(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php'));

        $this->assertStringContainsString("'event_risk_snapshot_source' => 'published_indicator_snapshot'", $source);
        $this->assertStringContainsString("'trading_status_code_semantics' => 'market_data_trading_status_event_types.event_type_code_snapshot'", $source);
        $this->assertStringContainsString("'forbids_raw_trading_status_event_join' => true", $source);
    }
}
