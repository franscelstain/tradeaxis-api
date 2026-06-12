<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC05ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC05ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_finite_deterministic_semantic_and_distinct_from_prior_catalogs(): void
    {
        $first = WatchlistBacktestC05ParamGridCatalog::rows();
        $second = WatchlistBacktestC05ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06', WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C05', WatchlistBacktestC05ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC05ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC05ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('476af5dde18079b1270556bc44bbc632edd46e27', WatchlistBacktestC05ParamGridCatalog::hash());
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
    }

    public function test_rows_have_expected_hashes_unique_parameters_valid_invariants_and_fixed_exit_axes(): void
    {
        $expectedRowHashes = [
            '00_C04_BEST_AVG_REFERENCE' => '8e7b933690479cfa9a5ef07fb53a97869d06c7eb',
            '01_SOFT_BALANCED_SAMPLE_CORE' => '5390e5aaae6f98243c9d1a86f4d020e68eb74568',
            '02_MONTHLY_SAMPLE_RECOVERY_CORE' => '067b6a7e74e594881af7d9994f2932109714ada6',
            '03_P25_NEAR_THRESHOLD_BALANCED' => '5b6cc72dce2248c53886b5ccda8e79eb9790f212',
            '04_MODERATE_LIQUIDITY_EXPANDED' => 'a953bc48f2c5781bcf06a8800bdb9553497aba14',
            '05_VOLUME_PARTICIPATION_BROAD' => '68e835ce0e33893b6778e48e4f384a16858e35dd',
            '06_TREND_RECOVERY_WITH_RS' => '8f99eddf458337332d856f251325ad4c99336bbe',
            '07_BREAKOUT_NEAR_NOT_EXTENDED' => 'a725357819fb68b39318c40ced81b01475620ffa',
            '08_LOW_ATR_SAMPLE_RECOVERY' => 'fd825a1f1673cab6d7543b5ebaf7bea36517e6cf',
            '09_ANTI_CHASE_SOFT_CONFIRM' => '2b0a7de5c880844fc57486a34764c50a4dfadcdd',
            '10_BROAD_SAMPLE_CONTROL' => 'eee634b0e93cc8c4501f3f67157b1a5fafdd71ba',
            '11_STRICT_NOT_BRITTLE_FINAL_PROBE' => '6ec5c52a60ff909da31808c96896f33585b66047',
        ];
        $expectedParameterHashes = [
            '00_C04_BEST_AVG_REFERENCE' => '38942ba5794726c4ac39f9c66b3ec7a76d1a69e1',
            '01_SOFT_BALANCED_SAMPLE_CORE' => '8df5ad69a8394253f7d59810ae09b0d53a0043e5',
            '02_MONTHLY_SAMPLE_RECOVERY_CORE' => 'b381bf903707812767c6055c16529902f4ae423e',
            '03_P25_NEAR_THRESHOLD_BALANCED' => '9f43c71374c5ba248216c710847ce5f6dfc194ce',
            '04_MODERATE_LIQUIDITY_EXPANDED' => 'fd63e73c4e745f1811efa76249d6cbb42e2de1d5',
            '05_VOLUME_PARTICIPATION_BROAD' => '9f1e4306f1743751488498add5b62d1beb932609',
            '06_TREND_RECOVERY_WITH_RS' => '8f66367c9e887eba3639d3a3728a8c0f3bef028d',
            '07_BREAKOUT_NEAR_NOT_EXTENDED' => 'dc5a51ef4f10dda9ac612aaaa24b37c12aaf2acc',
            '08_LOW_ATR_SAMPLE_RECOVERY' => '0c121e39987f7377563bbfbe1f281e3d1ef68822',
            '09_ANTI_CHASE_SOFT_CONFIRM' => '859aedbf865821ac23e0125c3662ab29a5078225',
            '10_BROAD_SAMPLE_CONTROL' => 'c93fd8d0d1777856b00fc84da4b124ece71fb4c1',
            '11_STRICT_NOT_BRITTLE_FINAL_PROBE' => '59673e38e54e4c5f0294c24df6117f6981718e51',
        ];

        $rowCodes = [];
        $parameterHashes = [];
        foreach (WatchlistBacktestC05ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC05ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC05ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame($expectedRowHashes[$row['row_code']], $row['row_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC05ParamGridCatalog::FIXED_STOP_ATR_MULT, $row['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestC05ParamGridCatalog::FIXED_MIN_RR, $row['min_rr']);
            $this->assertSame(WatchlistBacktestC05ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC05ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
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

    public function test_reference_row_matches_c04_best_average_failed_row_without_becoming_best_of_failed(): void
    {
        $reference = WatchlistBacktestC05ParamGridCatalog::rows()[0];
        $c04BestAverage = WatchlistBacktestC04ParamGridCatalog::rows()[2];

        $this->assertSame(WatchlistBacktestC05ParamGridCatalog::REFERENCE_ROW_CODE, $reference['row_code']);
        foreach ([
            'min_dv20_idr', 'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio',
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr', 'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ] as $field) {
            $this->assertSame((float) $c04BestAverage[$field], (float) $reference[$field], $field);
        }
        $this->assertFalse(WatchlistBacktestC05ParamGridCatalog::provenance()['best_of_failed_selection']);
        $this->assertTrue(WatchlistBacktestC05ParamGridCatalog::provenance()['c04_rejected_as_strategy_catalog']);
    }

    public function test_catalog_axes_are_runtime_supported_and_sector_evidence_is_diagnostic_only(): void
    {
        $axes = WatchlistBacktestC05ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC05ParamGridCatalog::provenance();
        $extension = WatchlistBacktestC05ParamGridCatalog::candidateSelectionExtension();

        $this->assertCount(32, $axes);
        $this->assertContains('c05.score_component_required_pass_count', $axes);
        $this->assertContains('c05.trend_metric_required_pass_count', $axes);
        $this->assertContains('c05.raw_setup_guard.roc20_tolerance', $axes);
        $this->assertNotContains('risk.stop_atr_mult', $axes);
        $this->assertNotContains('risk.min_rr', $axes);
        $this->assertNotContains('sector_code', $axes);
        $this->assertNotContains('sector_filter', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC05ParamGridCatalog::axisRationale()));
        $this->assertSame('C05_SOFT_BALANCED_SAMPLE_STABILITY_FLOOR', $extension['mode']);
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertSame('DIAGNOSTIC_REVIEW_ONLY_NO_SECTOR_FILTER', $provenance['sector_evidence_usage']);
        $this->assertCount(WatchlistBacktestC05ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC05ParamGridCatalog::manifestRows());
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
