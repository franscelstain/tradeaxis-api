<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC03ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_finite_deterministic_semantic_and_distinct_from_prior_catalogs(): void
    {
        $first = WatchlistBacktestC03ParamGridCatalog::rows();
        $second = WatchlistBacktestC03ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06', WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C03', WatchlistBacktestC03ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(10, WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('29e15ceab1b3f7dc31a21f339ac6ab7483e14800', WatchlistBacktestC03ParamGridCatalog::hash());
        $this->assertDoesNotMatchRegularExpression('/_R[3-9]_/', WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
    }

    public function test_rows_have_expected_hashes_unique_parameters_valid_invariants_and_fixed_exit_axes(): void
    {
        $expectedRowHashes = [
            '00_C02_BEST_AVG_REFERENCE' => '2aea621dbe634e5b8be97406e2f2a2d3d36f06bf',
            '01_HIGH_SCORE_LOW_ATR_MID_ROC' => 'dd0e432462b71fcc0326dc0486fa4fd984245090',
            '02_STABILITY_PROXY_TIGHTENED' => '081524dbc2c1fe638012b644029ede3cbd9dd804',
            '03_DOWNSIDE_P25_LOW_ATR_STRICT_Q' => 'bfcf7b5583a6f3126ebca43f80fdfe464eb6176f',
            '04_ANTI_CHASE_CLOSE_BREAKOUT' => 'd2bc0e7e10d6080ca4c7bcf8e1b6f632bc818435',
            '05_MODERATE_VOLUME_NO_SPIKE' => 'b8e73388c756cebb9d292a8f895c0896fff04d27',
            '06_LIQUIDITY_QUALITY_CORE' => '7ab38440346315698d7970264bc9eb27f577220b',
            '07_LOW_ATR_STABILITY_CORE' => '6079d1fb3ef386752bb6601cfdc444ace8ced9ac',
            '08_RISK_BREAKOUT_BALANCED_HIGH_Q' => '714b8f6e7668fca420dd06da0808589059f4ae08',
            '09_CANDIDATE_REDUCTION_EXTREME_Q' => '6d4b9d82bc771b1fa1bfc402c1d584a8b826f05f',
        ];
        $expectedParameterHashes = [
            '00_C02_BEST_AVG_REFERENCE' => '1724d250f008d6a98b94ae41223d7b86bda0ca1e',
            '01_HIGH_SCORE_LOW_ATR_MID_ROC' => 'eea81429fa0b3bba7179f92999a9b87d06e803cd',
            '02_STABILITY_PROXY_TIGHTENED' => '4927790ebf393fd279515eb890181e1192fae00e',
            '03_DOWNSIDE_P25_LOW_ATR_STRICT_Q' => 'cab5489e21fc8515c4a1ce2a8955b61a2ee9d10f',
            '04_ANTI_CHASE_CLOSE_BREAKOUT' => '5d8a7aa33c6a4f2233faae8219f6125c4ded2e6e',
            '05_MODERATE_VOLUME_NO_SPIKE' => 'e25f2547e971f8be9f27bbbb8bf78aec2ad82cc2',
            '06_LIQUIDITY_QUALITY_CORE' => '4a5aa83c9fcae5feddd7579ed205206383ebb06b',
            '07_LOW_ATR_STABILITY_CORE' => '84a203de56ab3a7189512c0f0ebc35a61f48f6e9',
            '08_RISK_BREAKOUT_BALANCED_HIGH_Q' => 'aba6fd485809be432d2e3fecf4d360672c419872',
            '09_CANDIDATE_REDUCTION_EXTREME_Q' => 'f0e3793d2efb677233578e2c824e1de55e065850',
        ];

        $rowCodes = [];
        $parameterHashes = [];
        foreach (WatchlistBacktestC03ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC03ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC03ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame($expectedRowHashes[$row['row_code']], $row['row_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC03ParamGridCatalog::FIXED_STOP_ATR_MULT, $row['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestC03ParamGridCatalog::FIXED_MIN_RR, $row['min_rr']);
            $this->assertSame(WatchlistBacktestC03ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC03ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
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

    public function test_reference_row_matches_c02_best_average_return_failed_row_without_becoming_best_of_failed(): void
    {
        $reference = WatchlistBacktestC03ParamGridCatalog::rows()[0];
        $c02BestAverageReturn = WatchlistBacktestC02ParamGridCatalog::rows()[6];

        $this->assertSame(WatchlistBacktestC03ParamGridCatalog::REFERENCE_ROW_CODE, $reference['row_code']);
        foreach ([
            'min_dv20_idr', 'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio',
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr', 'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ] as $field) {
            $this->assertSame((float) $c02BestAverageReturn[$field], (float) $reference[$field], $field);
        }
        $this->assertFalse(WatchlistBacktestC03ParamGridCatalog::provenance()['best_of_failed_selection']);
        $this->assertTrue(WatchlistBacktestC03ParamGridCatalog::provenance()['c02_rejected_as_strategy_catalog']);
    }

    public function test_catalog_axes_remain_existing_runtime_axes_and_sector_evidence_is_diagnostic_only(): void
    {
        $axes = WatchlistBacktestC03ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC03ParamGridCatalog::provenance();

        $this->assertCount(19, $axes);
        $this->assertContains('risk.max_atr14_pct', $axes);
        $this->assertContains('risk.atr_ideal_low', $axes);
        $this->assertContains('scoring.weights.value.risk', $axes);
        $this->assertContains('grouping.secondary_min_score_q', $axes);
        $this->assertNotContains('risk.stop_atr_mult', $axes);
        $this->assertNotContains('risk.min_rr', $axes);
        $this->assertNotContains('sector_code', $axes);
        $this->assertNotContains('sector_filter', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC03ParamGridCatalog::axisRationale()));
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertSame('DIAGNOSTIC_REVIEW_ONLY_EXISTING_AXIS_PROXY', $provenance['sector_evidence_usage']);
        $this->assertSame('CURATED_DETERMINISTIC', $provenance['search_mode']);
        $this->assertCount(WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC03ParamGridCatalog::manifestRows());
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
