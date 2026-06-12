<?php

use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC14ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;

class WatchlistBacktestC14ParamGridParamsetFactoryTest extends TestCase
{
    public function test_factory_maps_c14_variable_exit_axes_and_c07_candidate_extension(): void
    {
        $row = WatchlistBacktestC14ParamGridCatalog::rows()[2];
        $row['param_id'] = 14002;
        $snapshot = (new WatchlistBacktestParamGridParamsetFactory())->make($row);

        $this->assertSame($row['catalog_code'], $snapshot['bt_catalog']['catalog_code']);
        $this->assertSame($row['catalog_version'], $snapshot['bt_catalog']['catalog_version']);
        $this->assertSame($row['catalog_hash'], $snapshot['bt_catalog']['catalog_hash']);
        $this->assertSame($row['row_code'], $snapshot['bt_catalog']['row_code']);
        $this->assertSame($row['stop_atr_mult'], $snapshot['risk']['stop_atr_mult']);
        $this->assertSame($row['min_rr'], $snapshot['risk']['min_rr']);
        $this->assertSame(WatchlistBacktestC14ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $snapshot['grouping']['top_picks']['max_items']);
        $this->assertSame(WatchlistBacktestC14ParamGridCatalog::FIXED_SECONDARY_TARGET, $snapshot['grouping']['secondary']['max_items']);
        $this->assertSame(
            WatchlistBacktestC07ParamGridCatalog::candidateSelectionExtension(),
            $snapshot['bt_grid_resolution']['candidate_selection_extension']
        );
        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, $snapshot['bt_grid_resolution']['exit_axis_policy']);
        $this->assertSame([
            WatchlistBacktestExitAxisSupport::AXIS_RISK_STOP_ATR_MULT,
            WatchlistBacktestExitAxisSupport::AXIS_RISK_MIN_RR,
        ], $snapshot['bt_grid_resolution']['exit_axis_runtime_axes']);
        $this->assertContains(WatchlistBacktestExitAxisSupport::AXIS_HOLDING_DAYS, $snapshot['bt_grid_resolution']['blocked_first_phase_axes']);
        $this->assertContains(WatchlistBacktestExitAxisSupport::AXIS_TARGET_PCT, $snapshot['bt_grid_resolution']['blocked_first_phase_axes']);
        $this->assertContains(WatchlistBacktestExitAxisSupport::AXIS_STOP_PCT, $snapshot['bt_grid_resolution']['blocked_first_phase_axes']);
    }

    public function test_c14_extension_is_entry_equivalent_to_c07_but_exit_axis_manifest_is_new(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $c07 = WatchlistBacktestC07ParamGridCatalog::rows()[1];
        $c14 = WatchlistBacktestC14ParamGridCatalog::rows()[0];
        $c07['param_id'] = 12001;
        $c14['param_id'] = 14000;

        $c07Snapshot = $factory->make($c07);
        $c14Snapshot = $factory->make($c14);

        $this->assertSame(
            $c07Snapshot['bt_grid_resolution']['candidate_selection_extension'],
            $c14Snapshot['bt_grid_resolution']['candidate_selection_extension']
        );
        $this->assertArrayNotHasKey('exit_axis_policy', $c07Snapshot['bt_grid_resolution']);
        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, $c14Snapshot['bt_grid_resolution']['exit_axis_policy']);
        $this->assertNotSame(
            sha1(json_encode($this->normalize($c07Snapshot), JSON_UNESCAPED_SLASHES)),
            sha1(json_encode($this->normalize($c14Snapshot), JSON_UNESCAPED_SLASHES))
        );
    }

    public function test_c14_paramset_hash_does_not_depend_on_database_surrogate_param_id(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $left = WatchlistBacktestC14ParamGridCatalog::rows()[4];
        $right = $left;
        $left['param_id'] = 140;
        $right['param_id'] = 140140;

        $leftSnapshot = $factory->make($left);
        $rightSnapshot = $factory->make($right);

        $this->assertArrayNotHasKey('param_id', $leftSnapshot['bt_grid']);
        $this->assertSame($leftSnapshot, $rightSnapshot);
    }

    public function test_c14_rejects_non_contract_exit_axis_fields(): void
    {
        $row = WatchlistBacktestC14ParamGridCatalog::rows()[0];
        $row['param_id'] = 14000;
        $row['holding_days'] = 3;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('WS_BT_EXIT_AXIS_INVALID: first-phase exit-axis support blocks');

        (new WatchlistBacktestParamGridParamsetFactory())->make($row);
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
