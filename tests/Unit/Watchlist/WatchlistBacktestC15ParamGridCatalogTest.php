<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC05ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC06ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC14ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC15ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC15ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_new_deterministic_and_distinct_from_failed_prior_catalogs(): void
    {
        $first = WatchlistBacktestC15ParamGridCatalog::rows();
        $second = WatchlistBacktestC15ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06', WatchlistBacktestC15ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C15', WatchlistBacktestC15ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC15ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC15ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('cc07324262151783dc6b5583ebd91a96c0d0527d', WatchlistBacktestC15ParamGridCatalog::hash());
        foreach ([
            WatchlistBacktestParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE,
        ] as $oldCatalogCode) {
            $this->assertNotSame($oldCatalogCode, WatchlistBacktestC15ParamGridCatalog::CATALOG_CODE);
        }
    }

    public function test_rows_are_unique_valid_mid_liquidity_controlled_pullback_catalog(): void
    {
        $rowCodes = [];
        $parameterHashes = [];

        foreach (WatchlistBacktestC15ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC15ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC15ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC15ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC15ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC15ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
            $this->assertGreaterThanOrEqual(2000000000, $row['min_dv20_idr']);
            $this->assertLessThanOrEqual(10000000000, $row['dv20_strong_idr']);
            $this->assertLessThanOrEqual($row['dv20_strong_idr'], $row['min_dv20_idr']);
            $this->assertGreaterThanOrEqual(1.00, $row['min_vol_ratio']);
            $this->assertLessThanOrEqual(2.50, $row['strong_vol_ratio']);
            $this->assertLessThanOrEqual($row['strong_vol_ratio'], $row['min_vol_ratio']);
            $this->assertLessThanOrEqual(0.0, $row['roc_lo']);
            $this->assertLessThanOrEqual(0.020, $row['roc_hi']);
            $this->assertLessThan($row['roc_hi'], $row['roc_lo']);
            $this->assertEqualsWithDelta(1.0, $row['w_momentum'] + $row['w_breakout'] + $row['w_volume'] + $row['w_risk'], 0.000001);
            $this->assertLessThanOrEqual($row['top_min_score_q'], $row['secondary_min_score_q']);
            $this->assertGreaterThanOrEqual(0.80, $row['stop_atr_mult']);
            $this->assertLessThanOrEqual(1.70, $row['stop_atr_mult']);
            $this->assertGreaterThanOrEqual(0.75, $row['min_rr']);
            $this->assertLessThanOrEqual(1.20, $row['min_rr']);

            $this->assertArrayNotHasKey($row['row_code'], $rowCodes);
            $rowCodes[$row['row_code']] = true;

            $hash = $this->parameterHash($row);
            $this->assertArrayNotHasKey($hash, $parameterHashes);
            $parameterHashes[$hash] = true;
        }
    }

    public function test_axes_change_candidate_selection_not_just_c14_exit_numbers(): void
    {
        $axes = WatchlistBacktestC15ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC15ParamGridCatalog::provenance();
        $extension = WatchlistBacktestC15ParamGridCatalog::candidateSelectionExtension();

        $this->assertSame('C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION', $extension['mode']);
        $this->assertSame(['min' => -0.020, 'max' => 0.000], $extension['short_term_momentum_bounds']['roc5']);
        $this->assertSame(0.899999, $extension['score_total_max']);
        $this->assertContains('c15.short_term_momentum_bounds.roc5.min_max', $axes);
        $this->assertContains('c15.score_total_max', $axes);
        $this->assertContains('c15.runtime_metric_bounds.dv20_between_catalog_min_and_strong', $axes);
        $this->assertContains('range_position_20', $extension['disallowed_runtime_axes_until_segmented']);
        $this->assertContains('close_to_ll20', $extension['disallowed_runtime_axes_until_segmented']);
        $this->assertContains('breakout_extension', $extension['disallowed_runtime_axes_until_segmented']);
        $this->assertSame($axes, array_keys(WatchlistBacktestC15ParamGridCatalog::axisRationale()));
        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, WatchlistBacktestC15ParamGridCatalog::exitAxisPolicy()['policy']);
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertTrue($provenance['c14_rejected_as_strategy_catalog']);
        $this->assertCount(WatchlistBacktestC15ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC15ParamGridCatalog::manifestRows());
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
