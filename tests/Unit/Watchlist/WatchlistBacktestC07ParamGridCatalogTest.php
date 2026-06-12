<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC05ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC06ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC07ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_new_deterministic_and_distinct_from_failed_prior_catalogs(): void
    {
        $first = WatchlistBacktestC07ParamGridCatalog::rows();
        $second = WatchlistBacktestC07ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06', WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C07', WatchlistBacktestC07ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC07ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC07ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('233b45b06cbf34da221d5d7de2d9725fdf4d3441', WatchlistBacktestC07ParamGridCatalog::hash());
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
    }

    public function test_rows_are_unique_valid_and_preserve_fixed_execution_axes(): void
    {
        $rowCodes = [];
        $parameterHashes = [];
        foreach (WatchlistBacktestC07ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC07ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC07ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC07ParamGridCatalog::FIXED_STOP_ATR_MULT, $row['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestC07ParamGridCatalog::FIXED_MIN_RR, $row['min_rr']);
            $this->assertSame(WatchlistBacktestC07ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC07ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
            $this->assertLessThanOrEqual($row['dv20_strong_idr'], $row['min_dv20_idr']);
            $this->assertLessThanOrEqual($row['strong_vol_ratio'], $row['min_vol_ratio']);
            $this->assertLessThanOrEqual($row['atr_ideal_low'], $row['min_atr14_pct']);
            $this->assertLessThanOrEqual($row['atr_ideal_high'], $row['atr_ideal_low']);
            $this->assertLessThanOrEqual($row['max_atr14_pct'], $row['atr_ideal_high']);
            $this->assertLessThan($row['roc_hi'], $row['roc_lo']);
            $this->assertEqualsWithDelta(1.0, $row['w_momentum'] + $row['w_breakout'] + $row['w_volume'] + $row['w_risk'], 0.000001);
            $this->assertLessThanOrEqual($row['top_min_score_q'], $row['secondary_min_score_q']);

            $this->assertArrayNotHasKey($row['row_code'], $rowCodes);
            $rowCodes[$row['row_code']] = true;

            $hash = $this->parameterHash($row);
            $this->assertArrayNotHasKey($hash, $parameterHashes);
            $parameterHashes[$hash] = true;
        }
    }

    public function test_axes_are_runtime_supported_and_sector_remains_confirmation_only(): void
    {
        $axes = WatchlistBacktestC07ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC07ParamGridCatalog::provenance();
        $extension = WatchlistBacktestC07ParamGridCatalog::candidateSelectionExtension();

        $this->assertContains('c07.optional_runtime_metrics.roc5', $axes);
        $this->assertContains('c07.optional_runtime_metrics.range_position_20_pct', $axes);
        $this->assertContains('c07.optional_runtime_metrics.rs_20_vs_sector', $axes);
        $this->assertContains('c07.event_risk_disallow_flags', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC07ParamGridCatalog::axisRationale()));
        $this->assertSame('C07_SHORT_TERM_RANGE_SECTOR_CONFIRMATION', $extension['mode']);
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertSame('CONFIRMATION_METRICS_ONLY_NO_SECTOR_WHITELIST', $provenance['sector_evidence_usage']);
        $this->assertCount(WatchlistBacktestC07ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC07ParamGridCatalog::manifestRows());
    }

    private function parameterHash(array $row): string
    {
        $columns = [
            'min_dv20_idr', 'dv20_strong_idr',
            'min_vol_ratio', 'strong_vol_ratio', 'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr',
            'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ];
        $payload = [];
        foreach ($columns as $column) {
            $payload[$column] = $row[$column];
        }

        return sha1(json_encode($this->normalize($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_keys($value) === range(0, count($value) - 1)) {
            return array_map([$this, 'normalize'], $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }
}
