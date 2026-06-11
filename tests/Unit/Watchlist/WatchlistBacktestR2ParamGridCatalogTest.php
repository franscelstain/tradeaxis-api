<?php

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestR2ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_finite_deterministic_versioned_and_distinct_from_r1(): void
    {
        $first = WatchlistBacktestR2ParamGridCatalog::rows();
        $second = WatchlistBacktestR2ParamGridCatalog::rows();

        $this->assertSame(12, WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('0f2eaadaa446980a3d5e48cd498df2a8157c01a5', WatchlistBacktestR2ParamGridCatalog::hash());
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::hash(), WatchlistBacktestR2ParamGridCatalog::hash());
    }

    public function test_rows_have_unique_identity_valid_invariants_and_fixed_exit_axes(): void
    {
        $rowCodes = [];
        $rowHashes = [];
        $parameterHashes = [];

        foreach (WatchlistBacktestR2ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::FIXED_STOP_ATR_MULT, $row['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::FIXED_MIN_RR, $row['min_rr']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
            $this->assertLessThanOrEqual($row['dv20_strong_idr'], $row['min_dv20_idr']);
            $this->assertLessThanOrEqual($row['strong_vol_ratio'], $row['min_vol_ratio']);
            $this->assertLessThanOrEqual($row['atr_ideal_low'], $row['min_atr14_pct']);
            $this->assertLessThanOrEqual($row['atr_ideal_high'], $row['atr_ideal_low']);
            $this->assertLessThanOrEqual($row['max_atr14_pct'], $row['atr_ideal_high']);
            $this->assertLessThan($row['roc_hi'], $row['roc_lo']);
            $this->assertEqualsWithDelta(1.0, $row['w_momentum'] + $row['w_breakout'] + $row['w_volume'] + $row['w_risk'], 0.000001);
            $this->assertLessThanOrEqual($row['top_min_score_q'], $row['secondary_min_score_q']);

            $this->assertArrayNotHasKey($row['row_code'], $rowCodes);
            $this->assertArrayNotHasKey($row['row_hash'], $rowHashes);
            $rowCodes[$row['row_code']] = true;
            $rowHashes[$row['row_hash']] = true;

            $payload = $row;
            foreach (['catalog_hash', 'row_hash', 'row_code', 'rationale', 'notes'] as $field) {
                unset($payload[$field]);
            }
            $hash = sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
            $this->assertArrayNotHasKey($hash, $parameterHashes);
            $parameterHashes[$hash] = true;
        }
    }

    public function test_control_row_preserves_r1_baseline_entry_and_exit_semantics(): void
    {
        $r1 = WatchlistBacktestParamGridCatalog::rows()[0];
        $control = WatchlistBacktestR2ParamGridCatalog::rows()[0];

        $this->assertSame(WatchlistBacktestR2ParamGridCatalog::R1_CONTROL_ROW_CODE, $control['row_code']);
        foreach ([
            'min_dv20_idr', 'max_atr14_pct', 'min_vol_ratio',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr', 'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ] as $field) {
            $this->assertSame((float) $r1[$field], (float) $control[$field], $field);
        }
    }

    public function test_catalog_axes_are_explicit_entry_quality_only(): void
    {
        $axes = WatchlistBacktestR2ParamGridCatalog::parameterAxes();

        $this->assertCount(19, $axes);
        $this->assertContains('liquidity.min_dv20_idr', $axes);
        $this->assertContains('volume.strong_vol_ratio', $axes);
        $this->assertContains('risk.atr_ideal_high', $axes);
        $this->assertContains('setup.bo_max_ext_pct', $axes);
        $this->assertContains('scoring.weights.value.breakout', $axes);
        $this->assertContains('grouping.top_min_score_q', $axes);
        $this->assertNotContains('risk.stop_atr_mult', $axes);
        $this->assertNotContains('risk.min_rr', $axes);
        $this->assertNotContains('grouping.top_picks_target', $axes);
        $this->assertNotContains('grouping.secondary_target', $axes);
        $this->assertNotContains('fee', $axes);
        $this->assertNotContains('holding_days', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestR2ParamGridCatalog::axisRationale()));
        $this->assertFalse(WatchlistBacktestR2ParamGridCatalog::provenance()['oos_used']);
        $this->assertCount(WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestR2ParamGridCatalog::manifestRows());
    }
}
