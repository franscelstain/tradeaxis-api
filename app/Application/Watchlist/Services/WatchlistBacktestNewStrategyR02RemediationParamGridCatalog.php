<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestNewStrategyR02RemediationParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_NEW_STRATEGY_R02_REMEDIATION_2026_07';
    public const CATALOG_VERSION = 'R02M1';
    public const CATALOG_COUNT = 1;
    public const ROW_CODE = 'R02_M1_H2_SEQUENTIAL_TARGET_0P5_PROFIT_NEXT_OPEN';

    public static function rows(): array
    {
        $row = [
            'policy_code' => 'WS',
            'catalog_code' => self::CATALOG_CODE,
            'catalog_version' => self::CATALOG_VERSION,
            'catalog_hash' => '',
            'row_code' => self::ROW_CODE,
            'row_hash' => '',
            'rationale' => 'Single allowed R02 remediation: retain locked H2 selection and replace only the exit behavior with one fixed, sequential, non-lookahead profit-capture rule.',
            'min_dv20_idr' => 1000000000,
            'max_dv20_idr' => 50000000000,
            'dv20_strong_idr' => 5000000000,
            'min_vol_ratio' => 1.200000,
            'max_vol_ratio' => 5.000000,
            'strong_vol_ratio' => 2.500000,
            'min_atr14_pct' => 0.020000,
            'max_atr14_pct' => 0.060000,
            'atr_ideal_low' => 0.035000,
            'atr_ideal_high' => 0.060000,
            'max_signal_tick_risk_expansion_pct' => null,
            'roc_lo' => 0.100000,
            'roc_hi' => 0.150000,
            'mom_roc20_soft_min' => 0.000000,
            'bo_near_below_pct' => 0.020000,
            'bo_max_ext_pct' => 0.050000,
            'w_momentum' => 0.300000,
            'w_volume' => 0.100000,
            'w_breakout' => 0.200000,
            'w_risk' => 0.400000,
            'stop_atr_mult' => 1.500000,
            'min_rr' => 1.500000,
            'top_picks_target' => 5,
            'secondary_target' => 10,
            'top_min_score_q' => 0.800000,
            'top_max_score_total' => 0.999999,
            'secondary_min_score_q' => 0.650000,
            'notes' => self::CATALOG_CODE.'_'.self::ROW_CODE,
        ];
        $hashPayload = $row;
        unset($hashPayload['catalog_hash'], $hashPayload['row_hash']);
        $row['catalog_hash'] = self::hashPayload([$hashPayload]);
        $row['row_hash'] = sha1(self::CATALOG_CODE.'|'.self::ROW_CODE);

        return [$row];
    }

    public static function hash(): string
    {
        return (string) self::rows()[0]['catalog_hash'];
    }

    public static function researchSelection(): array
    {
        return WatchlistBacktestNewStrategyR02ParamGridCatalog::researchSelectionForRow(
            'R02_H2_ROC20_PERSISTENCE_10_TO_15'
        );
    }

    public static function researchExecution(): array
    {
        return [
            'schema_version' => 'WS_NEW_STRATEGY_RESEARCH_EXECUTION_V1',
            'remediation_code' => 'R02_M1_SINGLE_ALLOWED_REMEDIATION',
            'rule_code' => 'SEQUENTIAL_TARGET_0P5_PROFIT_CLOSE_NEXT_OPEN_D5_CLOSE',
            'fixed_before_entry' => true,
            'preplanned_target_pct' => 0.005000,
            'profit_close_threshold_pct' => 0,
            'profit_signal_day_offsets' => [1, 2, 3],
            'profit_signal_exit' => 'NEXT_TRADING_DAY_OPEN',
            'fallback_exit' => 'D5_CLOSE',
            'canonical_stop_used' => false,
            'raw_tradable_ohlcv_required' => true,
            'future_derived_route_used' => false,
            'oos_used' => false,
        ];
    }

    private static function hashPayload(array $payload): string
    {
        return sha1(json_encode(self::normalize($payload), JSON_UNESCAPED_SLASHES));
    }

    private static function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map([self::class, 'normalize'], $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = self::normalize($item);
        }

        return $value;
    }
}
