<?php

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;

class WatchlistBacktestC17ParamGridParamsetFactoryTest extends TestCase
{
    public function test_factory_maps_c17_candidate_extension_segmented_score_windows_and_variable_exit_axes(): void
    {
        $row = WatchlistBacktestC17ParamGridCatalog::rows()[2];
        $row['param_id'] = 17002;
        $snapshot = (new WatchlistBacktestParamGridParamsetFactory())->make($row);

        $this->assertSame($row['catalog_code'], $snapshot['bt_catalog']['catalog_code']);
        $this->assertSame($row['catalog_version'], $snapshot['bt_catalog']['catalog_version']);
        $this->assertSame($row['catalog_hash'], $snapshot['bt_catalog']['catalog_hash']);
        $this->assertSame($row['row_code'], $snapshot['bt_catalog']['row_code']);
        $this->assertSame($row['stop_atr_mult'], $snapshot['risk']['stop_atr_mult']);
        $this->assertSame($row['min_rr'], $snapshot['risk']['min_rr']);
        $this->assertSame(WatchlistBacktestC17ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $snapshot['grouping']['top_picks']['max_items']);
        $this->assertSame(WatchlistBacktestC17ParamGridCatalog::FIXED_SECONDARY_TARGET, $snapshot['grouping']['secondary']['max_items']);
        $this->assertSame(
            WatchlistBacktestC17ParamGridCatalog::candidateSelectionExtension(),
            $snapshot['bt_grid_resolution']['candidate_selection_extension']
        );
        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, $snapshot['bt_grid_resolution']['exit_axis_policy']);
        $this->assertSame(['min' => 0.680000, 'max' => 0.820000], $snapshot['bt_grid_resolution']['candidate_selection_extension']['score_windows_by_row_code'][$row['row_code']]);
    }

    public function test_c17_paramset_hash_does_not_depend_on_database_surrogate_param_id(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $left = WatchlistBacktestC17ParamGridCatalog::rows()[8];
        $right = $left;
        $left['param_id'] = 17008;
        $right['param_id'] = 999999;

        $leftSnapshot = $factory->make($left);
        $rightSnapshot = $factory->make($right);

        $this->assertSame($leftSnapshot['bt_catalog'], $rightSnapshot['bt_catalog']);
        $this->assertSame($leftSnapshot['bt_grid'], $rightSnapshot['bt_grid']);
        $this->assertSame($leftSnapshot['bt_grid_resolution'], $rightSnapshot['bt_grid_resolution']);
        $this->assertArrayNotHasKey('param_id', $leftSnapshot['bt_grid']);
        $this->assertArrayNotHasKey('param_id', $rightSnapshot['bt_grid']);
    }
}
