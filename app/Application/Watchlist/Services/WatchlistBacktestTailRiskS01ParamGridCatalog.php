<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestTailRiskS01ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_TAIL_RISK_S01_2026_07';
    public const CATALOG_VERSION = 'S01';
    public const CATALOG_COUNT = 3;
    public const H1_ROW_CODE = 'S01_H1_IHSG_NON_WEAK_GUARD';
    public const H2_ROW_CODE = 'S01_H2_TICK_RISK_LT_1P5_GUARD';
    public const H3_ROW_CODE = 'S01_H3_DAILY_CLOSE_LOSS_CONTAINMENT';

    public static function rows(): array
    {
        $rows = [
            self::row(
                self::H1_ROW_CODE,
                'One-idea S01 H1: retain the locked ROC20 baseline and sequential exit; exclude only exact signal-date IHSG WEAK regime.',
                null
            ),
            self::row(
                self::H2_ROW_CODE,
                'One-idea S01 H2: retain the locked ROC20 baseline and sequential exit; add only signal tick-risk expansion below 1.5%.',
                0.015
            ),
            self::row(
                self::H3_ROW_CODE,
                'One-idea S01 H3: retain selection; add only a D1-D3 close loss signal at -3% with next-trading-day open execution.',
                null
            ),
        ];
        $hashPayload = $rows;
        foreach ($hashPayload as &$hashRow) {
            unset($hashRow['catalog_hash'], $hashRow['row_hash']);
        }
        unset($hashRow);
        $catalogHash = self::hashPayload($hashPayload);
        foreach ($rows as &$row) {
            $row['catalog_hash'] = $catalogHash;
            $row['row_hash'] = sha1(self::CATALOG_CODE.'|'.$row['row_code']);
        }
        unset($row);

        return $rows;
    }

    public static function hash(): string
    {
        return (string) self::rows()[0]['catalog_hash'];
    }

    public static function researchSelectionForRow(string $rowCode): array
    {
        $base = [
            'schema_version' => 'WS_NEW_STRATEGY_RESEARCH_SELECTION_V1',
            'signal_date_only' => true,
            'oos_used' => false,
        ];
        if ($rowCode === self::H1_ROW_CODE) {
            return array_merge($base, [
                'hypothesis_code' => self::H1_ROW_CODE,
                'rule_code' => 'SIGNAL_ROC20_10_TO_15_AND_IHSG_NON_WEAK',
                'thresholds' => [
                    'min_roc20' => 0.10,
                    'max_roc20' => 0.15,
                    'benchmark_code' => 'IHSG',
                    'allowed_regimes' => ['STRONG', 'MIXED'],
                ],
            ]);
        }
        if ($rowCode === self::H2_ROW_CODE) {
            return array_merge($base, [
                'hypothesis_code' => self::H2_ROW_CODE,
                'rule_code' => 'SIGNAL_ROC20_10_TO_15_AND_TICK_RISK_LT_1P5',
                'thresholds' => [
                    'min_roc20' => 0.10,
                    'max_roc20' => 0.15,
                    'max_signal_tick_risk_expansion_pct' => 0.015,
                ],
            ]);
        }
        if ($rowCode === self::H3_ROW_CODE) {
            return array_merge($base, [
                'hypothesis_code' => self::H3_ROW_CODE,
                'rule_code' => 'SIGNAL_ROC20_10_TO_15_BASELINE_FOR_LOSS_CONTAINMENT',
                'thresholds' => [
                    'min_roc20' => 0.10,
                    'max_roc20' => 0.15,
                ],
            ]);
        }

        throw new \RuntimeException('WS_TAIL_RISK_S01_UNKNOWN_CATALOG_ROW: '.$rowCode);
    }

    public static function researchExecutionForRow(string $rowCode): array
    {
        if ($rowCode === self::H1_ROW_CODE || $rowCode === self::H2_ROW_CODE) {
            return WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution();
        }
        if ($rowCode === self::H3_ROW_CODE) {
            return self::lossContainmentExecution();
        }

        throw new \RuntimeException('WS_TAIL_RISK_S01_UNKNOWN_CATALOG_ROW: '.$rowCode);
    }

    public static function lossContainmentExecution(): array
    {
        return [
            'schema_version' => 'WS_NEW_STRATEGY_RESEARCH_EXECUTION_V1',
            'remediation_code' => 'S01_H3_DAILY_CLOSE_LOSS_CONTAINMENT',
            'rule_code' => 'SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_CLOSE_NEXT_OPEN_D5_CLOSE',
            'fixed_before_entry' => true,
            'preplanned_target_pct' => 0.005,
            'profit_close_threshold_pct' => 0,
            'profit_signal_day_offsets' => [1, 2, 3],
            'profit_signal_exit' => 'NEXT_TRADING_DAY_OPEN',
            'loss_close_threshold_pct' => -0.03,
            'loss_signal_day_offsets' => [1, 2, 3],
            'loss_signal_exit' => 'NEXT_TRADING_DAY_OPEN',
            'fallback_exit' => 'D5_CLOSE',
            'canonical_stop_used' => false,
            'raw_tradable_ohlcv_required' => true,
            'future_derived_route_used' => false,
            'oos_used' => false,
        ];
    }

    private static function row(
        string $rowCode,
        string $rationale,
        ?float $maxTickRisk
    ): array {
        return [
            'policy_code' => 'WS',
            'catalog_code' => self::CATALOG_CODE,
            'catalog_version' => self::CATALOG_VERSION,
            'catalog_hash' => '',
            'row_code' => $rowCode,
            'row_hash' => '',
            'rationale' => $rationale,
            'min_dv20_idr' => 1000000000,
            'max_dv20_idr' => 50000000000,
            'dv20_strong_idr' => 5000000000,
            'min_vol_ratio' => 1.2,
            'max_vol_ratio' => 5.0,
            'strong_vol_ratio' => 2.5,
            'min_atr14_pct' => 0.02,
            'max_atr14_pct' => 0.06,
            'atr_ideal_low' => 0.035,
            'atr_ideal_high' => 0.06,
            'max_signal_tick_risk_expansion_pct' => $maxTickRisk,
            'roc_lo' => 0.10,
            'roc_hi' => 0.15,
            'mom_roc20_soft_min' => 0.0,
            'bo_near_below_pct' => 0.02,
            'bo_max_ext_pct' => 0.05,
            'w_momentum' => 0.30,
            'w_volume' => 0.10,
            'w_breakout' => 0.20,
            'w_risk' => 0.40,
            'stop_atr_mult' => 1.5,
            'min_rr' => 1.5,
            'top_picks_target' => 5,
            'secondary_target' => 10,
            'top_min_score_q' => 0.80,
            'top_max_score_total' => 0.999999,
            'secondary_min_score_q' => 0.65,
            'notes' => self::CATALOG_CODE.'_'.$rowCode,
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
