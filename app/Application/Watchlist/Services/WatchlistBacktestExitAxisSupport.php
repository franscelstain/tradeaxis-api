<?php

namespace App\Application\Watchlist\Services;

use RuntimeException;

class WatchlistBacktestExitAxisSupport
{
    public const POLICY_FIXED_EXECUTION = 'FIXED_EXECUTION_SNAPSHOT';
    public const POLICY_VARIABLE_RISK_EXIT_AXIS = 'VARIABLE_RISK_EXIT_AXIS_V1';

    public const AXIS_RISK_STOP_ATR_MULT = 'risk.stop_atr_mult';
    public const AXIS_RISK_MIN_RR = 'risk.min_rr';
    public const AXIS_HOLDING_DAYS = 'backtest.holding_days';
    public const AXIS_TARGET_PCT = 'backtest.target_pct';
    public const AXIS_STOP_PCT = 'backtest.stop_pct';

    public const CONTRACT_SOURCE = 'C12_EXIT_MODEL_REDESIGN_CONTRACT';

    /**
     * @return array<string, mixed>
     */
    public static function fixedExecutionDefinition(
        float $stopAtrMult,
        float $minRr,
        int $topPicksTarget,
        int $secondaryTarget
    ): array {
        return [
            'policy' => self::POLICY_FIXED_EXECUTION,
            'fixed_stop_atr_mult' => $stopAtrMult,
            'fixed_min_rr' => $minRr,
            'fixed_top_picks_target' => $topPicksTarget,
            'fixed_secondary_target' => $secondaryTarget,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function variableRiskExitAxisDefinition(
        int $fixedTopPicksTarget,
        int $fixedSecondaryTarget,
        array $bounds = []
    ): array {
        return [
            'policy' => self::POLICY_VARIABLE_RISK_EXIT_AXIS,
            'runtime_axes' => [
                self::AXIS_RISK_STOP_ATR_MULT,
                self::AXIS_RISK_MIN_RR,
            ],
            'blocked_first_phase_axes' => [
                self::AXIS_HOLDING_DAYS,
                self::AXIS_TARGET_PCT,
                self::AXIS_STOP_PCT,
            ],
            'fixed_top_picks_target' => $fixedTopPicksTarget,
            'fixed_secondary_target' => $fixedSecondaryTarget,
            'bounds' => array_replace([
                'stop_atr_mult_min' => 0.50,
                'stop_atr_mult_max' => 3.00,
                'min_rr_min' => 0.50,
                'min_rr_max' => 3.00,
            ], $bounds),
            'contract_source' => self::CONTRACT_SOURCE,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolve(array $row, array $definition): array
    {
        $policy = (string) ($definition['policy'] ?? self::POLICY_FIXED_EXECUTION);

        if ($policy === self::POLICY_FIXED_EXECUTION) {
            return self::resolveFixedExecution($row, $definition);
        }

        if ($policy === self::POLICY_VARIABLE_RISK_EXIT_AXIS) {
            return self::resolveVariableRiskExitAxis($row, $definition);
        }

        throw new RuntimeException('WS_BT_EXIT_AXIS_INVALID: unsupported execution axis policy.');
    }

    /**
     * @return array<string, mixed>
     */
    private static function resolveFixedExecution(array $row, array $definition): array
    {
        $stopAtrMult = self::requiredFloat($row, 'stop_atr_mult');
        $minRr = self::requiredFloat($row, 'min_rr');
        $topPicksTarget = self::requiredInt($row, 'top_picks_target');
        $secondaryTarget = self::requiredInt($row, 'secondary_target');

        if (abs($stopAtrMult - (float) $definition['fixed_stop_atr_mult']) > 0.000001
            || abs($minRr - (float) $definition['fixed_min_rr']) > 0.000001
            || $topPicksTarget !== (int) $definition['fixed_top_picks_target']
            || $secondaryTarget !== (int) $definition['fixed_secondary_target']) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: fixed execution/grouping snapshot drifted.');
        }

        return [
            'policy' => self::POLICY_FIXED_EXECUTION,
            'stop_atr_mult' => $stopAtrMult,
            'min_rr' => $minRr,
            'top_picks_target' => $topPicksTarget,
            'secondary_target' => $secondaryTarget,
            'bt_grid_resolution' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function resolveVariableRiskExitAxis(array $row, array $definition): array
    {
        foreach (['holding_days', 'target_pct', 'stop_pct'] as $field) {
            if (array_key_exists($field, $row) && trim((string) $row[$field]) !== '') {
                throw new RuntimeException('WS_BT_EXIT_AXIS_INVALID: first-phase exit-axis support blocks holding_days and fixed percent exits.');
            }
        }

        $topPicksTarget = self::requiredInt($row, 'top_picks_target');
        $secondaryTarget = self::requiredInt($row, 'secondary_target');
        if ($topPicksTarget !== (int) $definition['fixed_top_picks_target']
            || $secondaryTarget !== (int) $definition['fixed_secondary_target']) {
            throw new RuntimeException('WS_BT_EXIT_AXIS_INVALID: grouping target drifted under variable risk-exit policy.');
        }

        $stopAtrMult = self::requiredFloat($row, 'stop_atr_mult');
        $minRr = self::requiredFloat($row, 'min_rr');
        $bounds = is_array($definition['bounds'] ?? null) ? $definition['bounds'] : [];

        self::assertWithinBounds($stopAtrMult, 'stop_atr_mult', $bounds, 'stop_atr_mult_min', 'stop_atr_mult_max');
        self::assertWithinBounds($minRr, 'min_rr', $bounds, 'min_rr_min', 'min_rr_max');

        return [
            'policy' => self::POLICY_VARIABLE_RISK_EXIT_AXIS,
            'stop_atr_mult' => $stopAtrMult,
            'min_rr' => $minRr,
            'top_picks_target' => $topPicksTarget,
            'secondary_target' => $secondaryTarget,
            'bt_grid_resolution' => [
                'exit_axis_policy' => self::POLICY_VARIABLE_RISK_EXIT_AXIS,
                'exit_axis_runtime_axes' => [
                    self::AXIS_RISK_STOP_ATR_MULT,
                    self::AXIS_RISK_MIN_RR,
                ],
                'blocked_first_phase_axes' => [
                    self::AXIS_HOLDING_DAYS,
                    self::AXIS_TARGET_PCT,
                    self::AXIS_STOP_PCT,
                ],
                'contract_source' => (string) ($definition['contract_source'] ?? self::CONTRACT_SOURCE),
            ],
        ];
    }

    private static function assertWithinBounds(
        float $value,
        string $field,
        array $bounds,
        string $minKey,
        string $maxKey
    ): void {
        $min = (float) ($bounds[$minKey] ?? 0);
        $max = (float) ($bounds[$maxKey] ?? 0);
        if ($min <= 0 || $max <= 0 || $min > $max || $value < $min || $value > $max) {
            throw new RuntimeException('WS_BT_EXIT_AXIS_INVALID: '.$field.' is outside authorized first-phase bounds.');
        }
    }

    private static function requiredFloat(array $row, string $key): float
    {
        if (! array_key_exists($key, $row) || ! is_numeric($row[$key])) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: '.$key.' must be numeric.');
        }

        return (float) $row[$key];
    }

    private static function requiredInt(array $row, string $key): int
    {
        if (! array_key_exists($key, $row) || ! is_numeric($row[$key])) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: '.$key.' must be numeric.');
        }

        return (int) $row[$key];
    }
}
