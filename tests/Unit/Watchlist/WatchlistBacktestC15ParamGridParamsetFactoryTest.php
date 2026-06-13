<?php

use App\Application\Watchlist\Services\WatchlistBacktestC15ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;

class WatchlistBacktestC15ParamGridParamsetFactoryTest extends TestCase
{
    public function test_factory_maps_c15_candidate_extension_and_variable_exit_axes(): void
    {
        $row = WatchlistBacktestC15ParamGridCatalog::rows()[1];
        $row['param_id'] = 15001;
        $snapshot = (new WatchlistBacktestParamGridParamsetFactory())->make($row);

        $this->assertSame($row['catalog_code'], $snapshot['bt_catalog']['catalog_code']);
        $this->assertSame($row['catalog_version'], $snapshot['bt_catalog']['catalog_version']);
        $this->assertSame($row['catalog_hash'], $snapshot['bt_catalog']['catalog_hash']);
        $this->assertSame($row['row_code'], $snapshot['bt_catalog']['row_code']);
        $this->assertSame($row['stop_atr_mult'], $snapshot['risk']['stop_atr_mult']);
        $this->assertSame($row['min_rr'], $snapshot['risk']['min_rr']);
        $this->assertSame(WatchlistBacktestC15ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $snapshot['grouping']['top_picks']['max_items']);
        $this->assertSame(WatchlistBacktestC15ParamGridCatalog::FIXED_SECONDARY_TARGET, $snapshot['grouping']['secondary']['max_items']);
        $this->assertSame(
            WatchlistBacktestC15ParamGridCatalog::candidateSelectionExtension(),
            $snapshot['bt_grid_resolution']['candidate_selection_extension']
        );
        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, $snapshot['bt_grid_resolution']['exit_axis_policy']);
        $this->assertSame([
            WatchlistBacktestExitAxisSupport::AXIS_RISK_STOP_ATR_MULT,
            WatchlistBacktestExitAxisSupport::AXIS_RISK_MIN_RR,
        ], $snapshot['bt_grid_resolution']['exit_axis_runtime_axes']);
    }

    public function test_c15_paramset_hash_does_not_depend_on_database_surrogate_param_id(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $left = WatchlistBacktestC15ParamGridCatalog::rows()[4];
        $right = $left;
        $left['param_id'] = 150;
        $right['param_id'] = 150150;

        $leftSnapshot = $factory->make($left);
        $rightSnapshot = $factory->make($right);

        $this->assertArrayNotHasKey('param_id', $leftSnapshot['bt_grid']);
        $this->assertSame($leftSnapshot, $rightSnapshot);
    }
}
