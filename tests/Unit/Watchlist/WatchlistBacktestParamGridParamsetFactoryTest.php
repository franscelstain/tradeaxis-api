<?php

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;

class WatchlistBacktestParamGridParamsetFactoryTest extends TestCase
{
    public function test_every_canonical_grid_row_resolves_cross_field_valid_risk_band(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();

        foreach (WatchlistBacktestParamGridCatalog::rows() as $index => $row) {
            $row['param_id'] = $index + 1;
            $paramset = $factory->make($row);
            $risk = $paramset['risk'];

            $this->assertTrue($risk['min_atr14_pct'] <= $risk['atr_ideal_low']);
            $this->assertTrue($risk['atr_ideal_low'] <= $risk['atr_ideal_high']);
            $this->assertTrue($risk['atr_ideal_high'] <= $risk['max_atr14_pct']);
            $this->assertSame(
                WatchlistBacktestParamGridParamsetFactory::RISK_BAND_RESOLUTION_RULE,
                $paramset['bt_grid_resolution']['risk_band_rule']
            );
        }
    }

    public function test_baseline_preserves_default_ideal_band_and_strict_row_clamps_it_explicitly(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $rows = WatchlistBacktestParamGridCatalog::rows();

        $baseline = $rows[0];
        $baseline['param_id'] = 1;
        $baselineParamset = $factory->make($baseline);
        $this->assertSame(0.035, $baselineParamset['risk']['atr_ideal_low']);
        $this->assertSame(0.075, $baselineParamset['risk']['atr_ideal_high']);

        $strict = $rows[21];
        $strict['param_id'] = 22;
        $strictParamset = $factory->make($strict);
        $this->assertSame(0.03, $strictParamset['risk']['max_atr14_pct']);
        $this->assertSame(0.03, $strictParamset['risk']['atr_ideal_low']);
        $this->assertSame(0.03, $strictParamset['risk']['atr_ideal_high']);
    }
}
