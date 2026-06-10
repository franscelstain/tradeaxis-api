<?php

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;

class WatchlistBacktestParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_non_empty_deterministic_unique_and_within_canonical_units(): void
    {
        $first = WatchlistBacktestParamGridCatalog::rows();
        $second = WatchlistBacktestParamGridCatalog::rows();

        $this->assertSame($first, $second);
        $this->assertSame(WatchlistBacktestParamGridCatalog::hash(), sha1(json_encode($first, JSON_UNESCAPED_SLASHES)));
        $this->assertCount(WatchlistBacktestParamGridCatalog::CATALOG_COUNT, $first);

        $payloadHashes = [];
        foreach ($first as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertGreaterThanOrEqual(1000000000, $row['min_dv20_idr']);
            $this->assertGreaterThan(0, $row['max_atr14_pct']);
            $this->assertLessThanOrEqual(1, $row['max_atr14_pct']);
            $this->assertGreaterThanOrEqual(0, $row['min_vol_ratio']);
            $this->assertEqualsWithDelta(
                1.0,
                $row['w_momentum'] + $row['w_volume'] + $row['w_breakout'] + $row['w_risk'],
                0.000001
            );
            $this->assertGreaterThan(0, $row['stop_atr_mult']);
            $this->assertGreaterThan(0, $row['min_rr']);
            $this->assertGreaterThan(0, $row['top_picks_target']);
            $this->assertGreaterThan(0, $row['secondary_target']);
            $this->assertGreaterThanOrEqual(0, $row['secondary_min_score_q']);
            $this->assertLessThanOrEqual(1, $row['top_min_score_q']);
            $this->assertGreaterThanOrEqual($row['secondary_min_score_q'], $row['top_min_score_q']);
            $this->assertStringStartsWith(WatchlistBacktestParamGridCatalog::CATALOG_CODE.'_', $row['notes']);

            $payload = $row;
            unset($payload['notes']);
            $hash = sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
            $this->assertArrayNotHasKey($hash, $payloadHashes);
            $payloadHashes[$hash] = true;
        }
    }

    public function test_sql_seed_contains_every_catalog_row_and_idempotent_guard(): void
    {
        $sql = file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/BACKTEST_PARAM_GRID_SEED.sql'
        ));

        $this->assertIsString($sql);
        $this->assertStringContainsString('WHERE NOT EXISTS', $sql);
        $this->assertStringContainsString('START TRANSACTION', $sql);
        $this->assertStringContainsString('COMMIT', $sql);
        foreach (WatchlistBacktestParamGridCatalog::rows() as $row) {
            $this->assertStringContainsString($row['notes'], $sql);
        }
    }
}
