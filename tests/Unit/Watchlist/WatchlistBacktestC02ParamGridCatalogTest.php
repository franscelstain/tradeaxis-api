<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC02ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_finite_deterministic_semantic_and_distinct_from_prior_catalogs(): void
    {
        $first = WatchlistBacktestC02ParamGridCatalog::rows();
        $second = WatchlistBacktestC02ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06', WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C02', WatchlistBacktestC02ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(8, WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('7287c438e15bd03d6beb4796e4d5159ecd8ed59a', WatchlistBacktestC02ParamGridCatalog::hash());
        $this->assertDoesNotMatchRegularExpression('/_R[3-9]_/', WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
    }

    public function test_rows_have_expected_hashes_unique_parameters_valid_invariants_and_fixed_exit_axes(): void
    {
        $expectedRowHashes = [
            '00_C01_NEAREST_GATE_REFERENCE' => 'd8671fb2122cb82b2a01d30a6c7997d8c7adffb7',
            '01_NEAR_BREAKOUT_MODERATE_LIQUIDITY' => '6b206defc39c732fcbd734a23cc7958658a3a08d',
            '02_MID_LIQUIDITY_VOLUME_BALANCED' => '77f52dee9eb2858fc52a9dd8ffc1f7dc4a04db73',
            '03_STRICT_NEAR_BREAKOUT_LOW_CHASE' => '4ed5ab3252aa5cb9c874ceb7c6b9d9059e64d36d',
            '04_LOW_ATR_MID_ROC_STABILITY' => 'de26734f4cbe16f3a46334c12bbc8f46f2538772',
            '05_VOLUME_NOT_SPIKE_RISK_FIRST' => '1a216d4f1462acea94e4b4e4c6926b15ce8abfa1',
            '06_BROAD_SAMPLE_NEAR_BREAKOUT' => 'a1b75bb807d20f73a8f649979b238d8a9106a0d6',
            '07_STABILITY_PROXY_SECTOR_REVIEW' => '704370ca4b7d5b5e50d16a44dc4af02a6e3f612c',
        ];
        $expectedParameterHashes = [
            '00_C01_NEAREST_GATE_REFERENCE' => 'beeea7345dca233402bf8f8113b3d87fd4c69a48',
            '01_NEAR_BREAKOUT_MODERATE_LIQUIDITY' => 'e6a76345e3858ba4d8a30a06377ee0e1bafbf956',
            '02_MID_LIQUIDITY_VOLUME_BALANCED' => 'e2d10c15d800b288b20d2a04ae701e20e26d192a',
            '03_STRICT_NEAR_BREAKOUT_LOW_CHASE' => '7d6fc7142305cb19ec62fb6a60209ac8ba2dea85',
            '04_LOW_ATR_MID_ROC_STABILITY' => '5066361dc2570c3bd55d42a8374d83764131ad43',
            '05_VOLUME_NOT_SPIKE_RISK_FIRST' => 'e4242668e43e20e72310a088557d009d9b4d50af',
            '06_BROAD_SAMPLE_NEAR_BREAKOUT' => '1724d250f008d6a98b94ae41223d7b86bda0ca1e',
            '07_STABILITY_PROXY_SECTOR_REVIEW' => 'aeeb9a2e69a1c6b0c41e9116e3e8ee205ad58e0f',
        ];

        $rowCodes = [];
        $parameterHashes = [];
        foreach (WatchlistBacktestC02ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC02ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC02ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame($expectedRowHashes[$row['row_code']], $row['row_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC02ParamGridCatalog::FIXED_STOP_ATR_MULT, $row['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestC02ParamGridCatalog::FIXED_MIN_RR, $row['min_rr']);
            $this->assertSame(WatchlistBacktestC02ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC02ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
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

    public function test_reference_row_matches_c01_nearest_gate_row_without_becoming_best_of_failed(): void
    {
        $reference = WatchlistBacktestC02ParamGridCatalog::rows()[0];
        $c01NearestGate = WatchlistBacktestC01ParamGridCatalog::rows()[1];

        $this->assertSame(WatchlistBacktestC02ParamGridCatalog::REFERENCE_ROW_CODE, $reference['row_code']);
        foreach ([
            'min_dv20_idr', 'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio',
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr', 'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ] as $field) {
            $this->assertSame((float) $c01NearestGate[$field], (float) $reference[$field], $field);
        }
        $this->assertFalse(WatchlistBacktestC02ParamGridCatalog::provenance()['best_of_failed_selection']);
    }

    public function test_catalog_axes_remain_existing_runtime_axes_and_sector_evidence_is_diagnostic_only(): void
    {
        $axes = WatchlistBacktestC02ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC02ParamGridCatalog::provenance();

        $this->assertCount(19, $axes);
        $this->assertContains('risk.max_atr14_pct', $axes);
        $this->assertContains('risk.atr_ideal_low', $axes);
        $this->assertContains('scoring.weights.value.risk', $axes);
        $this->assertContains('grouping.secondary_min_score_q', $axes);
        $this->assertNotContains('risk.stop_atr_mult', $axes);
        $this->assertNotContains('risk.min_rr', $axes);
        $this->assertNotContains('sector_code', $axes);
        $this->assertNotContains('sector_filter', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC02ParamGridCatalog::axisRationale()));
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertSame('DIAGNOSTIC_REVIEW_ONLY_EXISTING_AXIS_PROXY', $provenance['sector_evidence_usage']);
        $this->assertSame('CURATED_DETERMINISTIC', $provenance['search_mode']);
        $this->assertCount(WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC02ParamGridCatalog::manifestRows());
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
