<?php

use App\Application\Watchlist\Services\WatchlistTradingStatusSnapshotNormalizer;

class WatchlistTradingStatusSnapshotNormalizerTest extends TestCase
{
    public function test_it_normalizes_legacy_trading_status_values_to_canonical_event_type_codes(): void
    {
        $this->assertNull(WatchlistTradingStatusSnapshotNormalizer::normalize(null));
        $this->assertNull(WatchlistTradingStatusSnapshotNormalizer::normalize(''));
        $this->assertSame('UNSUSPENDED', WatchlistTradingStatusSnapshotNormalizer::normalize('ACTIVE'));
        $this->assertSame('UNSUSPENDED', WatchlistTradingStatusSnapshotNormalizer::normalize('resume trading'));
        $this->assertSame('SUSPENSION_OBSERVED', WatchlistTradingStatusSnapshotNormalizer::normalize('long-suspension-gt-6m'));
        $this->assertSame('SUSPENDED', WatchlistTradingStatusSnapshotNormalizer::normalize('trading halt'));
        $this->assertSame('SPECIAL_MONITORING_START', WatchlistTradingStatusSnapshotNormalizer::normalize('notasi khusus'));
        $this->assertSame('SPECIAL_MONITORING_END', WatchlistTradingStatusSnapshotNormalizer::normalize('removed from special monitoring'));
    }

    public function test_it_uses_market_data_primary_status_priority_for_legacy_multi_status_snapshots(): void
    {
        $this->assertSame('SUSPENDED', WatchlistTradingStatusSnapshotNormalizer::normalize('UMA,SUSPENDED'));
        $this->assertSame('UNSUSPENDED', WatchlistTradingStatusSnapshotNormalizer::normalize('ACTIVE,UMA'));
        $this->assertSame('SPECIAL_MONITORING_START', WatchlistTradingStatusSnapshotNormalizer::normalize('UMA,SPECIAL_MONITORING'));
    }
}
