<?php

use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;

class WatchlistBacktestExitAxisSupportTest extends TestCase
{
    public function test_fixed_execution_policy_preserves_legacy_drift_guard(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('WS_BT_R2_CATALOG_INVALID: fixed execution/grouping snapshot drifted.');

        WatchlistBacktestExitAxisSupport::resolve([
            'stop_atr_mult' => 2.0,
            'min_rr' => 1.5,
            'top_picks_target' => 5,
            'secondary_target' => 10,
        ], WatchlistBacktestExitAxisSupport::fixedExecutionDefinition(1.5, 1.5, 5, 10));
    }

    public function test_variable_risk_exit_policy_allows_only_contracted_risk_axes(): void
    {
        $resolved = WatchlistBacktestExitAxisSupport::resolve([
            'stop_atr_mult' => 1.25,
            'min_rr' => 1.10,
            'top_picks_target' => 5,
            'secondary_target' => 10,
        ], WatchlistBacktestExitAxisSupport::variableRiskExitAxisDefinition(5, 10));

        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, $resolved['policy']);
        $this->assertSame(1.25, $resolved['stop_atr_mult']);
        $this->assertSame(1.10, $resolved['min_rr']);
        $this->assertSame([
            WatchlistBacktestExitAxisSupport::AXIS_RISK_STOP_ATR_MULT,
            WatchlistBacktestExitAxisSupport::AXIS_RISK_MIN_RR,
        ], $resolved['bt_grid_resolution']['exit_axis_runtime_axes']);
        $this->assertSame([
            WatchlistBacktestExitAxisSupport::AXIS_HOLDING_DAYS,
            WatchlistBacktestExitAxisSupport::AXIS_TARGET_PCT,
            WatchlistBacktestExitAxisSupport::AXIS_STOP_PCT,
        ], $resolved['bt_grid_resolution']['blocked_first_phase_axes']);
    }

    /** @dataProvider blockedVariableAxisRows */
    public function test_variable_risk_exit_policy_blocks_non_contracted_first_phase_axes(array $row): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('WS_BT_EXIT_AXIS_INVALID: first-phase exit-axis support blocks');

        WatchlistBacktestExitAxisSupport::resolve(
            $row,
            WatchlistBacktestExitAxisSupport::variableRiskExitAxisDefinition(5, 10)
        );
    }

    public function blockedVariableAxisRows(): array
    {
        $base = [
            'stop_atr_mult' => 1.25,
            'min_rr' => 1.10,
            'top_picks_target' => 5,
            'secondary_target' => 10,
        ];

        return [
            'holding_days' => [array_merge($base, ['holding_days' => 3])],
            'target_pct' => [array_merge($base, ['target_pct' => 0.05])],
            'stop_pct' => [array_merge($base, ['stop_pct' => 0.03])],
        ];
    }

    public function test_variable_risk_exit_policy_rejects_out_of_bounds_risk_axis_values(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('WS_BT_EXIT_AXIS_INVALID: stop_atr_mult is outside authorized first-phase bounds.');

        WatchlistBacktestExitAxisSupport::resolve([
            'stop_atr_mult' => 3.50,
            'min_rr' => 1.10,
            'top_picks_target' => 5,
            'secondary_target' => 10,
        ], WatchlistBacktestExitAxisSupport::variableRiskExitAxisDefinition(5, 10));
    }

    public function test_existing_c07_factory_output_remains_fixed_and_has_no_variable_exit_axis_manifest(): void
    {
        $row = WatchlistBacktestC07ParamGridCatalog::rows()[0];
        $row['param_id'] = 7001;
        $snapshot = (new WatchlistBacktestParamGridParamsetFactory())->make($row);

        $this->assertSame(WatchlistBacktestC07ParamGridCatalog::FIXED_STOP_ATR_MULT, $snapshot['risk']['stop_atr_mult']);
        $this->assertSame(WatchlistBacktestC07ParamGridCatalog::FIXED_MIN_RR, $snapshot['risk']['min_rr']);
        $this->assertArrayNotHasKey('exit_axis_policy', $snapshot['bt_grid_resolution']);
        $this->assertArrayNotHasKey('exit_axis_runtime_axes', $snapshot['bt_grid_resolution']);
    }
}
