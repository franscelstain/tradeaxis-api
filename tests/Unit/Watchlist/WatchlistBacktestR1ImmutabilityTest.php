<?php

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestR1ImmutabilityTest extends TestCase
{
    public function test_historical_r1_rows_count_and_hash_remain_exact(): void
    {
        $this->assertSame('WS_BT_GRID_BOOTSTRAP_2026_06', WatchlistBacktestParamGridCatalog::CATALOG_CODE);
        $this->assertSame(24, WatchlistBacktestParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(24, WatchlistBacktestParamGridCatalog::rows());
        $this->assertSame('9da8b0983c57bde1ce0a1fbf1c119756f8af431c', WatchlistBacktestParamGridCatalog::hash());
    }

    public function test_r1_persistence_enrichment_does_not_change_historical_payload(): void
    {
        $historical = WatchlistBacktestParamGridCatalog::rows();
        $persisted = WatchlistBacktestParamGridCatalog::persistenceRows();

        $this->assertCount(count($historical), $persisted);
        foreach ($historical as $index => $row) {
            foreach ($row as $field => $value) {
                $this->assertSame($value, $persisted[$index][$field], $index.':'.$field);
            }
            $this->assertSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, $persisted[$index]['catalog_code']);
            $this->assertSame(WatchlistBacktestParamGridCatalog::hash(), $persisted[$index]['catalog_hash']);
        }
    }

    public function test_r1_factory_snapshot_is_identical_before_and_after_catalog_enrichment(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        foreach (WatchlistBacktestParamGridCatalog::rows() as $index => $historicalRow) {
            $historicalRuntimeRow = ['param_id' => $index + 1] + $historicalRow;
            $enrichedRuntimeRow = ['param_id' => $index + 1]
                + WatchlistBacktestParamGridCatalog::persistenceRows()[$index];

            $this->assertSame(
                $factory->make($historicalRuntimeRow),
                $factory->make($enrichedRuntimeRow),
                'R1 runtime paramset drift at row '.($index + 1)
            );
        }
    }

    public function test_r2_catalog_does_not_reuse_r1_identity(): void
    {
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_VERSION, WatchlistBacktestR2ParamGridCatalog::CATALOG_VERSION);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::hash(), WatchlistBacktestR2ParamGridCatalog::hash());
    }

    public function test_c01_catalog_does_not_reuse_r1_or_r2_identity(): void
    {
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_VERSION, WatchlistBacktestC01ParamGridCatalog::CATALOG_VERSION);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_VERSION, WatchlistBacktestC01ParamGridCatalog::CATALOG_VERSION);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::hash(), WatchlistBacktestC01ParamGridCatalog::hash());
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::hash(), WatchlistBacktestC01ParamGridCatalog::hash());
    }
}
