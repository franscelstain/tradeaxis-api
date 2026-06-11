<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC01ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_finite_deterministic_semantic_and_distinct_from_r_series(): void
    {
        $first = WatchlistBacktestC01ParamGridCatalog::rows();
        $second = WatchlistBacktestC01ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06', WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C01', WatchlistBacktestC01ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(8, WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('604ac98f6f193a4c317d4f25582deada84682846', WatchlistBacktestC01ParamGridCatalog::hash());
        $this->assertDoesNotMatchRegularExpression('/_R[3-9]_/', WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
    }

    public function test_rows_have_expected_hashes_unique_parameters_valid_invariants_and_fixed_exit_axes(): void
    {
        $expectedRowHashes = [
            '00_R2_DEFENSIVE_REFERENCE' => 'ce502a65e3c488a3ad41e52ab21b22a1bc957cf0',
            '01_LOW_ATR_BREADTH' => '1d9eb142fe35b9fea24e4a85a24cca92a4ed8f98',
            '02_ULTRA_LOW_ATR_BREADTH' => 'f7451083a2b63ab759901e162f017cba37083a1b',
            '03_LOW_ATR_VOLUME_STABLE' => 'fad7ad555836e46daf55df535ba0630248e3d8fa',
            '04_RISK_FIRST_NOT_CHASING' => 'af04460a896500b6c7c7a5bcb912fd4befc63b0e',
            '05_STABILITY_BREADTH_MOMENTUM' => '3e389b35206c058ec93b796d0548f178f637ded9',
            '06_HIGH_LIQ_LOW_ATR_MODERATE_Q' => '580e99963f76d42e7eae2e6a41cc114b816f4ffd',
            '07_DOWNSIDE_CAP_BALANCED_FIXED_EXIT' => 'd9e60e5cc2180498d9572f3ea851fe58a64c6d47',
        ];
        $expectedParameterHashes = [
            '00_R2_DEFENSIVE_REFERENCE' => '11a897e0974ba9d5107362e2af1b44fd32cbdf3a',
            '01_LOW_ATR_BREADTH' => 'beeea7345dca233402bf8f8113b3d87fd4c69a48',
            '02_ULTRA_LOW_ATR_BREADTH' => '8ab9abbeb1de22316028469aef216a43cda973a1',
            '03_LOW_ATR_VOLUME_STABLE' => 'c3b0a8d4d1849c511c470ffe209f7ffa446a8a30',
            '04_RISK_FIRST_NOT_CHASING' => 'bb89d9872ab7894d8be3fc82a749c4bbf2852924',
            '05_STABILITY_BREADTH_MOMENTUM' => 'b8819786508bffa340753a9fa0743e1948631c1d',
            '06_HIGH_LIQ_LOW_ATR_MODERATE_Q' => 'de207a53bf9b6dd0af51f4532c8a8228a3f743c9',
            '07_DOWNSIDE_CAP_BALANCED_FIXED_EXIT' => '8d69a98132d8fa741999af5119a272015231717b',
        ];

        $rowCodes = [];
        $parameterHashes = [];
        foreach (WatchlistBacktestC01ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC01ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame($expectedRowHashes[$row['row_code']], $row['row_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC01ParamGridCatalog::FIXED_STOP_ATR_MULT, $row['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestC01ParamGridCatalog::FIXED_MIN_RR, $row['min_rr']);
            $this->assertSame(WatchlistBacktestC01ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC01ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
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
            $this->assertSame($expectedParameterHashes[$row['row_code']], $hash);
            $this->assertArrayNotHasKey($hash, $parameterHashes);
            $parameterHashes[$hash] = true;
        }
    }

    public function test_reference_row_matches_r2_defensive_row_without_becoming_best_of_failed(): void
    {
        $reference = WatchlistBacktestC01ParamGridCatalog::rows()[0];
        $r2Defensive = WatchlistBacktestR2ParamGridCatalog::rows()[10];

        $this->assertSame(WatchlistBacktestC01ParamGridCatalog::REFERENCE_ROW_CODE, $reference['row_code']);
        foreach ([
            'min_dv20_idr', 'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio',
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr', 'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ] as $field) {
            $this->assertSame((float) $r2Defensive[$field], (float) $reference[$field], $field);
        }
        $this->assertFalse(WatchlistBacktestC01ParamGridCatalog::provenance()['best_of_failed_selection']);
    }

    public function test_catalog_axes_are_explicit_downside_stability_only(): void
    {
        $axes = WatchlistBacktestC01ParamGridCatalog::parameterAxes();

        $this->assertCount(19, $axes);
        $this->assertContains('risk.max_atr14_pct', $axes);
        $this->assertContains('risk.atr_ideal_low', $axes);
        $this->assertContains('scoring.weights.value.risk', $axes);
        $this->assertContains('grouping.secondary_min_score_q', $axes);
        $this->assertNotContains('risk.stop_atr_mult', $axes);
        $this->assertNotContains('risk.min_rr', $axes);
        $this->assertNotContains('fee', $axes);
        $this->assertNotContains('slippage', $axes);
        $this->assertNotContains('holding_days', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC01ParamGridCatalog::axisRationale()));
        $this->assertFalse(WatchlistBacktestC01ParamGridCatalog::provenance()['oos_used']);
        $this->assertSame('CURATED_DETERMINISTIC', WatchlistBacktestC01ParamGridCatalog::provenance()['search_mode']);
        $this->assertCount(WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC01ParamGridCatalog::manifestRows());
    }

    private function parameterHash(array $row): string
    {
        $columns = [
            'min_dv20_idr', 'dv20_strong_idr',
            'min_vol_ratio', 'strong_vol_ratio',
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
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
