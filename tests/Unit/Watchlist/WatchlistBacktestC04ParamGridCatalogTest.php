<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC04ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_finite_deterministic_semantic_and_distinct_from_prior_catalogs(): void
    {
        $first = WatchlistBacktestC04ParamGridCatalog::rows();
        $second = WatchlistBacktestC04ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06', WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C04', WatchlistBacktestC04ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(10, WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('0ce3a313c45432c5a4d607def12b3f774988f324', WatchlistBacktestC04ParamGridCatalog::hash());
        $this->assertDoesNotMatchRegularExpression('/_R[3-9]_/', WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
    }

    public function test_rows_have_expected_hashes_unique_parameters_valid_invariants_and_fixed_exit_axes(): void
    {
        $expectedRowHashes = [
            '00_C03_LOW_ATR_STABILITY_REFERENCE' => '8506385fad91010e0a070f42c987cae7254e12a6',
            '01_BALANCED_COMPONENT_FLOOR_CORE' => 'ccd0d1ec8a3cd850013a7452b2c320e0eeb81710',
            '02_BREAKOUT_VOLUME_RISK_CORE' => '5d54992ca18228b7f3e99192f6504f662ab8e7e2',
            '03_MODERATE_LIQUIDITY_ROC_BAND' => '6bcfabff4c48e872c213cc6be93d1a23a688c5b9',
            '04_PRIOR_STRENGTH_NOT_CHASE' => '91175107e7f3e60d678176fa60b2b1b6d388b698',
            '05_LOW_ATR_DOWNSIDE_CONTROL' => '8d2fc26f6fd0bf15451b3d4364112dc9ed366ed4',
            '06_MONTHLY_STABILITY_LIQUID' => '05a74cc14339b878cd0672e743d96fd9ba1caf1b',
            '07_ANTI_REVERSAL_TRAP_CONFIRM' => '6839ab704c7f129d57b4871057dc2485b43c001c',
            '08_BROAD_SAMPLE_QUALITY_FLOOR' => '20d67a175a91d3da723c6a95f51e406d27d32169',
            '09_STRICT_BALANCED_FINAL_PROBE' => '73d7f955c0e40c962e78edd62f3444804f523144',
        ];
        $expectedParameterHashes = [
            '00_C03_LOW_ATR_STABILITY_REFERENCE' => '84a203de56ab3a7189512c0f0ebc35a61f48f6e9',
            '01_BALANCED_COMPONENT_FLOOR_CORE' => 'f1e625b53e46e1e59e460780252f782316948a65',
            '02_BREAKOUT_VOLUME_RISK_CORE' => '38942ba5794726c4ac39f9c66b3ec7a76d1a69e1',
            '03_MODERATE_LIQUIDITY_ROC_BAND' => '29f37a8625cdb495a62ac439db8040e30e5f866a',
            '04_PRIOR_STRENGTH_NOT_CHASE' => '3bb1c6a0f8873a56d4fd562f15958876a6a0179f',
            '05_LOW_ATR_DOWNSIDE_CONTROL' => '370abd2a70aececa513f3a96a4f9b92143b34fe7',
            '06_MONTHLY_STABILITY_LIQUID' => '661081dc3fe3abe1ed01ba0851711069747dc4cd',
            '07_ANTI_REVERSAL_TRAP_CONFIRM' => '2a2046c72cd42ed7b382ef50b5e60f19b783d838',
            '08_BROAD_SAMPLE_QUALITY_FLOOR' => '32ee8e707f875e08e0e1b61e6494b1871b1224fb',
            '09_STRICT_BALANCED_FINAL_PROBE' => '80841a41178f6e2fe51cb71e6bc4b0b32a84ad34',
        ];

        $rowCodes = [];
        $parameterHashes = [];
        foreach (WatchlistBacktestC04ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame($expectedRowHashes[$row['row_code']], $row['row_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::FIXED_STOP_ATR_MULT, $row['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::FIXED_MIN_RR, $row['min_rr']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
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

    public function test_reference_row_matches_c03_low_atr_stability_failed_row_without_becoming_best_of_failed(): void
    {
        $reference = WatchlistBacktestC04ParamGridCatalog::rows()[0];
        $c03LowAtrStability = WatchlistBacktestC03ParamGridCatalog::rows()[7];

        $this->assertSame(WatchlistBacktestC04ParamGridCatalog::REFERENCE_ROW_CODE, $reference['row_code']);
        foreach ([
            'min_dv20_idr', 'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio',
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr', 'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ] as $field) {
            $this->assertSame((float) $c03LowAtrStability[$field], (float) $reference[$field], $field);
        }
        $this->assertFalse(WatchlistBacktestC04ParamGridCatalog::provenance()['best_of_failed_selection']);
        $this->assertTrue(WatchlistBacktestC04ParamGridCatalog::provenance()['c02_rejected_as_strategy_catalog']);
        $this->assertTrue(WatchlistBacktestC04ParamGridCatalog::provenance()['c03_rejected_as_strategy_catalog']);
    }

    public function test_catalog_axes_are_runtime_supported_and_sector_evidence_is_diagnostic_only(): void
    {
        $axes = WatchlistBacktestC04ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC04ParamGridCatalog::provenance();
        $extension = WatchlistBacktestC04ParamGridCatalog::candidateSelectionExtension();

        $this->assertCount(29, $axes);
        $this->assertContains('c04.score_component_min.score_breakout', $axes);
        $this->assertContains('c04.trend_metric_floor.rs_20_vs_ihsg', $axes);
        $this->assertContains('c04.raw_setup_guard.close_to_hh20_between_negative_bo_near_below_and_bo_max_ext', $axes);
        $this->assertNotContains('risk.stop_atr_mult', $axes);
        $this->assertNotContains('risk.min_rr', $axes);
        $this->assertNotContains('sector_code', $axes);
        $this->assertNotContains('sector_filter', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC04ParamGridCatalog::axisRationale()));
        $this->assertSame('C04_BALANCED_COMPONENT_AND_TREND_FLOOR', $extension['mode']);
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertSame('DIAGNOSTIC_REVIEW_ONLY_NO_SECTOR_FILTER', $provenance['sector_evidence_usage']);
        $this->assertSame('CURATED_DETERMINISTIC', $provenance['search_mode']);
        $this->assertCount(WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC04ParamGridCatalog::manifestRows());
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
