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
use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC17ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_new_deterministic_and_distinct_from_prior_catalogs(): void
    {
        $first = WatchlistBacktestC17ParamGridCatalog::rows();
        $second = WatchlistBacktestC17ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06', WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C17', WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('d411bfbee6fb14c17d821aa92e7e0fea06925d67', WatchlistBacktestC17ParamGridCatalog::hash());
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
            WatchlistBacktestC16ParamGridCatalog::CATALOG_CODE,
        ] as $oldCatalogCode) {
            $this->assertNotSame($oldCatalogCode, WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE);
        }
    }

    public function test_rows_are_unique_valid_quality_preserving_sample_recovery_catalog(): void
    {
        $rowCodes = [];
        $parameterHashes = [];
        $scoreWindows = WatchlistBacktestC17ParamGridCatalog::scoreWindowsByRowCode();

        foreach (WatchlistBacktestC17ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC17ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC17ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC17ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
            $this->assertGreaterThanOrEqual(2000000000, $row['min_dv20_idr']);
            $this->assertLessThanOrEqual(7500000000, $row['dv20_strong_idr']);
            $this->assertLessThanOrEqual($row['dv20_strong_idr'], $row['min_dv20_idr']);
            $this->assertGreaterThanOrEqual(1.35, $row['min_vol_ratio']);
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
            $this->assertArrayHasKey($row['row_code'], $scoreWindows);
            $this->assertLessThan(0.90, $scoreWindows[$row['row_code']]['max']);

            $this->assertArrayNotHasKey($row['row_code'], $rowCodes);
            $rowCodes[$row['row_code']] = true;

            $hash = $this->parameterHash($row);
            $this->assertArrayNotHasKey($hash, $parameterHashes);
            $parameterHashes[$hash] = true;
        }
    }

    public function test_axes_add_c17_segmented_score_windows_and_no_best_of_failed_binding(): void
    {
        $axes = WatchlistBacktestC17ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC17ParamGridCatalog::provenance();
        $extension = WatchlistBacktestC17ParamGridCatalog::candidateSelectionExtension();

        $this->assertSame('C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16', $extension['mode']);
        $this->assertSame(['min' => -0.020, 'max' => 0.000], $extension['short_term_momentum_bounds']['roc5']);
        $this->assertSame(['min' => 0.650000, 'max' => 0.800000], $extension['score_windows_by_row_code'][WatchlistBacktestC17ParamGridCatalog::REFERENCE_ROW_CODE]);
        $this->assertSame(['min' => 0.700000, 'max' => 0.850000], $extension['score_windows_by_row_code']['08_SCORE_70_85_DV20_2B_6B_ROC20_COOLING']);
        $this->assertSame(0.900000, $extension['blocked_score_chase']['score_total_min']);
        $this->assertContains('c17.score_window.segmented_0_65_to_0_80', $axes);
        $this->assertContains('c17.score_window.segmented_0_68_to_0_82', $axes);
        $this->assertContains('c17.score_window.segmented_0_70_to_0_85', $axes);
        $this->assertContains('c17.score_chase_0_90_to_1_00_blocked', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC17ParamGridCatalog::axisRationale()));
        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, WatchlistBacktestC17ParamGridCatalog::exitAxisPolicy()['policy']);
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertFalse($provenance['sector_whitelist_used']);
        $this->assertFalse($provenance['ticker_blacklist_used']);
        $this->assertFalse($provenance['month_blacklist_used']);
        $this->assertFalse($provenance['best_of_failed_binding_used']);
        $this->assertFalse($provenance['c16_promoted']);
        $this->assertFalse($provenance['canonical_gate_lowered']);
        $this->assertContains('param_140_07_ONE_R_TARGET_MID_DV20', $provenance['c16_diagnostic_anchors_only']);
        $this->assertCount(WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC17ParamGridCatalog::manifestRows());
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
