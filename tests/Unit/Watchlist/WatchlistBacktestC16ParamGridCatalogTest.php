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
use App\Application\Watchlist\Services\WatchlistBacktestC16ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC16ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_new_deterministic_and_distinct_from_prior_catalogs(): void
    {
        $first = WatchlistBacktestC16ParamGridCatalog::rows();
        $second = WatchlistBacktestC16ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C16_2026_06', WatchlistBacktestC16ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C16', WatchlistBacktestC16ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC16ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC16ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('0ad1289f79d78787cdca275f0b3f3e2ba90bf8f2', WatchlistBacktestC16ParamGridCatalog::hash());
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
            WatchlistBacktestC15ParamGridCatalog::CATALOG_CODE,
        ] as $oldCatalogCode) {
            $this->assertNotSame($oldCatalogCode, WatchlistBacktestC16ParamGridCatalog::CATALOG_CODE);
        }
    }

    public function test_rows_are_unique_valid_quality_preserving_score_window_catalog(): void
    {
        $rowCodes = [];
        $parameterHashes = [];

        foreach (WatchlistBacktestC16ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC16ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC16ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC16ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC16ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC16ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
            $this->assertGreaterThanOrEqual(2500000000, $row['min_dv20_idr']);
            $this->assertLessThanOrEqual(7500000000, $row['dv20_strong_idr']);
            $this->assertLessThanOrEqual($row['dv20_strong_idr'], $row['min_dv20_idr']);
            $this->assertGreaterThanOrEqual(1.50, $row['min_vol_ratio']);
            $this->assertLessThanOrEqual(2.50, $row['strong_vol_ratio']);
            $this->assertLessThanOrEqual($row['strong_vol_ratio'], $row['min_vol_ratio']);
            $this->assertGreaterThanOrEqual(-0.050, $row['roc_lo']);
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

    public function test_axes_add_c16_runtime_consumed_score_window_not_quantile_reinterpretation(): void
    {
        $axes = WatchlistBacktestC16ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC16ParamGridCatalog::provenance();
        $extension = WatchlistBacktestC16ParamGridCatalog::candidateSelectionExtension();

        $this->assertSame('C16_CONTROLLED_PULLBACK_SCORE_WINDOW_VOLUME_QUALITY_RECOVERY', $extension['mode']);
        $this->assertSame(['min' => -0.020, 'max' => 0.000], $extension['short_term_momentum_bounds']['roc5']);
        $this->assertSame(0.700000, $extension['score_total_min']);
        $this->assertSame(0.799999, $extension['score_total_max']);
        $this->assertSame(1.50, WatchlistBacktestC16ParamGridCatalog::rows()[1]['min_vol_ratio']);
        $this->assertContains('c16.score_total_min', $axes);
        $this->assertContains('c16.score_total_max', $axes);
        $this->assertContains('c16.runtime_metric_bounds.vol_ratio_1_5_to_catalog_strong', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC16ParamGridCatalog::axisRationale()));
        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, WatchlistBacktestC16ParamGridCatalog::exitAxisPolicy()['policy']);
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertFalse($provenance['ticker_blacklist_used']);
        $this->assertFalse($provenance['month_blacklist_used']);
        $this->assertFalse($provenance['c15_promoted']);
        $this->assertTrue($provenance['c15_rejected_as_strategy_catalog']);
        $this->assertContains('c15_param_129', $provenance['negative_recovery_samples_rejected_as_basis']);
        $this->assertContains('c15_param_132', $provenance['negative_recovery_samples_rejected_as_basis']);
        $this->assertCount(WatchlistBacktestC16ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC16ParamGridCatalog::manifestRows());
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
