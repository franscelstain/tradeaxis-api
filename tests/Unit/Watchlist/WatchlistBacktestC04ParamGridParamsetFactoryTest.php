<?php

use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;

class WatchlistBacktestC04ParamGridParamsetFactoryTest extends TestCase
{
    public function test_factory_maps_every_explicit_c04_field_and_embeds_candidate_selection_extension(): void
    {
        $row = WatchlistBacktestC04ParamGridCatalog::rows()[1];
        $row['param_id'] = 9001;
        $snapshot = (new WatchlistBacktestParamGridParamsetFactory())->make($row);

        $this->assertSame($row['catalog_code'], $snapshot['bt_catalog']['catalog_code']);
        $this->assertSame($row['catalog_version'], $snapshot['bt_catalog']['catalog_version']);
        $this->assertSame($row['catalog_hash'], $snapshot['bt_catalog']['catalog_hash']);
        $this->assertSame($row['row_code'], $snapshot['bt_catalog']['row_code']);
        $this->assertSame((float) $row['min_dv20_idr'], $snapshot['liquidity']['min_dv20_idr']);
        $this->assertSame((float) $row['dv20_strong_idr'], $snapshot['liquidity']['dv20_strong_idr']);
        $this->assertSame($row['min_vol_ratio'], $snapshot['volume']['min_vol_ratio']);
        $this->assertSame($row['strong_vol_ratio'], $snapshot['volume']['strong_vol_ratio']);
        $this->assertSame($row['min_atr14_pct'], $snapshot['risk']['min_atr14_pct']);
        $this->assertSame($row['max_atr14_pct'], $snapshot['risk']['max_atr14_pct']);
        $this->assertSame($row['atr_ideal_low'], $snapshot['risk']['atr_ideal_low']);
        $this->assertSame($row['atr_ideal_high'], $snapshot['risk']['atr_ideal_high']);
        $this->assertSame($row['roc_lo'], $snapshot['setup']['roc_lo']);
        $this->assertSame($row['roc_hi'], $snapshot['setup']['roc_hi']);
        $this->assertSame($row['mom_roc20_soft_min'], $snapshot['setup']['mom_roc20_soft_min']);
        $this->assertSame($row['bo_near_below_pct'], $snapshot['setup']['bo_near_below_pct']);
        $this->assertSame($row['bo_max_ext_pct'], $snapshot['setup']['bo_max_ext_pct']);
        $this->assertSame($row['w_momentum'], $snapshot['scoring']['weights']['momentum']);
        $this->assertSame($row['w_breakout'], $snapshot['scoring']['weights']['breakout']);
        $this->assertSame($row['w_volume'], $snapshot['scoring']['weights']['volume']);
        $this->assertSame($row['w_risk'], $snapshot['scoring']['weights']['risk']);
        $this->assertSame($row['top_min_score_q'], $snapshot['grouping']['top_min_score_q']);
        $this->assertSame($row['secondary_min_score_q'], $snapshot['grouping']['secondary_min_score_q']);
        $this->assertSame(WatchlistBacktestParamGridParamsetFactory::EXPLICIT_CATALOG_RISK_BAND_RULE, $snapshot['bt_grid_resolution']['risk_band_rule']);
        $this->assertTrue($snapshot['bt_grid_resolution']['explicit_catalog_values_preserved']);
        $this->assertSame(
            WatchlistBacktestC04ParamGridCatalog::candidateSelectionExtension(),
            $snapshot['bt_grid_resolution']['candidate_selection_extension']
        );
    }

    public function test_c04_extension_does_not_leak_to_c03_paramsets(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $c03 = WatchlistBacktestC03ParamGridCatalog::rows()[7];
        $c04 = WatchlistBacktestC04ParamGridCatalog::rows()[0];
        $c03['param_id'] = 8007;
        $c04['param_id'] = 9000;

        $c03Snapshot = $factory->make($c03);
        $c04Snapshot = $factory->make($c04);

        $this->assertArrayNotHasKey('candidate_selection_extension', $c03Snapshot['bt_grid_resolution']);
        $this->assertArrayHasKey('candidate_selection_extension', $c04Snapshot['bt_grid_resolution']);
        $this->assertNotSame(
            sha1(json_encode($this->normalize($c03Snapshot), JSON_UNESCAPED_SLASHES)),
            sha1(json_encode($this->normalize($c04Snapshot), JSON_UNESCAPED_SLASHES))
        );
    }

    public function test_all_c04_rows_keep_exit_semantics_fixed_and_have_unique_paramset_hashes(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $hashes = [];

        foreach (WatchlistBacktestC04ParamGridCatalog::rows() as $index => $row) {
            $row['param_id'] = 9000 + $index;
            $snapshot = $factory->make($row);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::FIXED_STOP_ATR_MULT, $snapshot['risk']['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::FIXED_MIN_RR, $snapshot['risk']['min_rr']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $snapshot['grouping']['top_picks']['max_items']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::FIXED_SECONDARY_TARGET, $snapshot['grouping']['secondary']['max_items']);
            $this->assertSame(WatchlistBacktestC04ParamGridCatalog::candidateSelectionExtension(), $snapshot['bt_grid_resolution']['candidate_selection_extension']);
            $hash = sha1(json_encode($this->normalize($snapshot), JSON_UNESCAPED_SLASHES));
            $this->assertArrayNotHasKey($hash, $hashes);
            $hashes[$hash] = true;
        }
    }

    public function test_c04_paramset_hash_does_not_depend_on_database_surrogate_param_id(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $left = WatchlistBacktestC04ParamGridCatalog::rows()[2];
        $right = $left;
        $left['param_id'] = 102;
        $right['param_id'] = 900102;

        $leftSnapshot = $factory->make($left);
        $rightSnapshot = $factory->make($right);

        $this->assertArrayNotHasKey('param_id', $leftSnapshot['bt_grid']);
        $this->assertSame($leftSnapshot, $rightSnapshot);
        $this->assertSame(
            sha1(json_encode($this->normalize($leftSnapshot), JSON_UNESCAPED_SLASHES)),
            sha1(json_encode($this->normalize($rightSnapshot), JSON_UNESCAPED_SLASHES))
        );
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
