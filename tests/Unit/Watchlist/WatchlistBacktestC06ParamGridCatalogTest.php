<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC05ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC06ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC06ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_new_deterministic_and_distinct_from_failed_prior_catalogs(): void
    {
        $first = WatchlistBacktestC06ParamGridCatalog::rows();
        $second = WatchlistBacktestC06ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06', WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C06', WatchlistBacktestC06ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC06ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC06ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('6c93d67fb77319a02cecc3d96fd99bb0e139a1ac', WatchlistBacktestC06ParamGridCatalog::hash());
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
    }

    public function test_rows_are_unique_valid_and_preserve_fixed_execution_axes(): void
    {
        $rowCodes = [];
        $parameterHashes = [];
        foreach (WatchlistBacktestC06ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC06ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC06ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC06ParamGridCatalog::FIXED_STOP_ATR_MULT, $row['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestC06ParamGridCatalog::FIXED_MIN_RR, $row['min_rr']);
            $this->assertSame(WatchlistBacktestC06ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC06ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
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

    public function test_reference_row_preserves_c04_control_without_best_of_failed_promotion(): void
    {
        $reference = WatchlistBacktestC06ParamGridCatalog::rows()[0];
        $c04Reference = WatchlistBacktestC04ParamGridCatalog::rows()[2];

        $this->assertSame(WatchlistBacktestC06ParamGridCatalog::REFERENCE_ROW_CODE, $reference['row_code']);
        foreach ([
            'min_dv20_idr', 'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio',
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr', 'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ] as $field) {
            $this->assertSame((float) $c04Reference[$field], (float) $reference[$field], $field);
        }
        $this->assertFalse(WatchlistBacktestC06ParamGridCatalog::provenance()['best_of_failed_selection']);
        $this->assertTrue(WatchlistBacktestC06ParamGridCatalog::provenance()['c05_rejected_as_strategy_catalog']);
    }

    public function test_axes_are_runtime_supported_and_sector_remains_diagnostic_only(): void
    {
        $axes = WatchlistBacktestC06ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC06ParamGridCatalog::provenance();
        $extension = WatchlistBacktestC06ParamGridCatalog::candidateSelectionExtension();

        $this->assertContains('c06.runtime_metric_bounds.dv20_between_catalog_min_and_strong', $axes);
        $this->assertContains('c06.runtime_metric_bounds.vol_ratio_between_catalog_min_and_strong', $axes);
        $this->assertContains('c06.runtime_metric_bounds.roc20_between_catalog_roc_lo_and_roc_hi', $axes);
        $this->assertNotContains('sector_code', $axes);
        $this->assertNotContains('sector_filter', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC06ParamGridCatalog::axisRationale()));
        $this->assertSame('C06_MODERATE_LIQUIDITY_VOLUME_ROC_STABILITY_FLOOR', $extension['mode']);
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertSame('DIAGNOSTIC_REVIEW_ONLY_NO_SECTOR_FILTER', $provenance['sector_evidence_usage']);
        $this->assertCount(WatchlistBacktestC06ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC06ParamGridCatalog::manifestRows());
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
