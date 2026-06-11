<?php

use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestR2ParamGridParamsetFactoryTest extends TestCase
{
    public function test_factory_maps_every_explicit_r2_field_without_default_override(): void
    {
        $row = WatchlistBacktestR2ParamGridCatalog::rows()[5];
        $row['param_id'] = 2005;
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
        $this->assertSame(WatchlistBacktestParamGridParamsetFactory::EXPLICIT_R2_RISK_BAND_RULE, $snapshot['bt_grid_resolution']['risk_band_rule']);
        $this->assertTrue($snapshot['bt_grid_resolution']['explicit_catalog_values_preserved']);
    }

    public function test_all_r2_rows_keep_exit_semantics_fixed_and_have_unique_paramset_hashes(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $hashes = [];

        foreach (WatchlistBacktestR2ParamGridCatalog::rows() as $index => $row) {
            $row['param_id'] = 3000 + $index;
            $snapshot = $factory->make($row);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::FIXED_STOP_ATR_MULT, $snapshot['risk']['stop_atr_mult']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::FIXED_MIN_RR, $snapshot['risk']['min_rr']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $snapshot['grouping']['top_picks']['max_items']);
            $this->assertSame(WatchlistBacktestR2ParamGridCatalog::FIXED_SECONDARY_TARGET, $snapshot['grouping']['secondary']['max_items']);
            $hash = sha1(json_encode($this->normalize($snapshot), JSON_UNESCAPED_SLASHES));
            $this->assertArrayNotHasKey($hash, $hashes);
            $hashes[$hash] = true;
        }
    }

    public function test_r2_paramset_hash_does_not_depend_on_database_surrogate_param_id(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $left = WatchlistBacktestR2ParamGridCatalog::rows()[1];
        $right = $left;
        $left['param_id'] = 25;
        $right['param_id'] = 900025;

        $leftSnapshot = $factory->make($left);
        $rightSnapshot = $factory->make($right);

        $this->assertArrayNotHasKey('param_id', $leftSnapshot['bt_grid']);
        $this->assertSame($leftSnapshot, $rightSnapshot);
        $this->assertSame(
            sha1(json_encode($this->normalize($leftSnapshot), JSON_UNESCAPED_SLASHES)),
            sha1(json_encode($this->normalize($rightSnapshot), JSON_UNESCAPED_SLASHES))
        );
    }

    /** @dataProvider invalidRows */
    public function test_factory_rejects_cross_field_invariant_violations(callable $mutator, string $message): void
    {
        $row = WatchlistBacktestR2ParamGridCatalog::rows()[1];
        $row['param_id'] = 1;
        $row = $mutator($row);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        (new WatchlistBacktestParamGridParamsetFactory())->make($row);
    }

    public function invalidRows(): array
    {
        return [
            'liquidity' => [function (array $row): array { $row['dv20_strong_idr'] = $row['min_dv20_idr'] - 1; return $row; }, 'dv20_strong_idr'],
            'volume' => [function (array $row): array { $row['strong_vol_ratio'] = $row['min_vol_ratio'] - 0.1; return $row; }, 'strong_vol_ratio'],
            'atr' => [function (array $row): array { $row['atr_ideal_low'] = $row['max_atr14_pct'] + 0.1; return $row; }, 'ATR band'],
            'roc' => [function (array $row): array { $row['roc_lo'] = $row['roc_hi']; return $row; }, 'roc_lo'],
            'weights' => [function (array $row): array { $row['w_risk'] += 0.1; return $row; }, 'weights must sum'],
            'breakout range' => [function (array $row): array { $row['bo_max_ext_pct'] = 1.1; return $row; }, 'breakout fractional ranges'],
            'quantiles' => [function (array $row): array { $row['secondary_min_score_q'] = $row['top_min_score_q'] + 0.01; return $row; }, 'quantiles'],
            'fixed execution' => [function (array $row): array { $row['stop_atr_mult'] = 2.0; return $row; }, 'fixed execution/grouping'],
            'catalog hash' => [function (array $row): array { $row['catalog_hash'] = str_repeat('0', 40); return $row; }, 'catalog metadata'],
        ];
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
