<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestNewStrategyR02ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_NEW_STRATEGY_R02_2026_07';
    public const CATALOG_VERSION = 'R02';
    public const CATALOG_COUNT = 3;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;
    public const FIXED_STOP_ATR_MULT = 1.5;
    public const FIXED_MIN_RR = 1.5;

    public static function rows(): array
    {
        $rows = [
            self::row(
                'R02_H1_BREAKOUT_QUALITY_0_TO_2',
                'One-idea H1 candidate: require signal-date close-to-HH20 from 0% through +2%; no volume, exit, market-regime, or OOS routing.',
                0.020000
            ),
            self::row(
                'R02_H2_ROC20_PERSISTENCE_10_TO_15',
                'One-idea H2 candidate: require signal-date ROC20 from +10% through +15%; no breakout, exit, market-regime, or OOS routing.',
                0.050000,
                0.100000
            ),
            self::row(
                'R02_H3_IHSG_MIXED_REGIME_ONLY',
                'One-idea H3 candidate: accept only signal-date IHSG MIXED regime where ROC20 and MA20 slope have opposite signs; no equity or OOS outcome routing.',
                0.050000,
                0.020000
            ),
        ];

        $catalogPayload = $rows;
        foreach ($catalogPayload as &$catalogRow) {
            unset($catalogRow['catalog_hash'], $catalogRow['row_hash']);
        }
        unset($catalogRow);
        $catalogHash = self::hashPayload($catalogPayload);
        foreach ($rows as &$row) {
            $row['catalog_hash'] = $catalogHash;
            $row['row_hash'] = sha1(self::CATALOG_CODE.'|'.$row['row_code']);
        }
        unset($row);

        return $rows;
    }

    public static function hash(): string
    {
        return (string) (self::rows()[0]['catalog_hash'] ?? self::hashPayload([]));
    }

    public static function researchSelectionForRow(string $rowCode): array
    {
        $base = [
            'schema_version' => 'WS_NEW_STRATEGY_RESEARCH_SELECTION_V1',
            'signal_date_only' => true,
            'oos_used' => false,
        ];

        if ($rowCode === 'R02_H1_BREAKOUT_QUALITY_0_TO_2') {
            return array_merge($base, [
                'hypothesis_code' => 'H1_BREAKOUT_QUALITY_CONFIRMATION',
                'rule_code' => 'SIGNAL_CLOSE_TO_HH20_0_TO_2_PCT',
                'thresholds' => [
                    'min_close_to_hh20_pct' => 0.000000,
                    'max_close_to_hh20_pct' => 0.020000,
                ],
            ]);
        }
        if ($rowCode === 'R02_H2_ROC20_PERSISTENCE_10_TO_15') {
            return array_merge($base, [
                'hypothesis_code' => 'H2_MOMENTUM_PERSISTENCE',
                'rule_code' => 'SIGNAL_ROC20_10_TO_15_PCT',
                'thresholds' => [
                    'min_roc20' => 0.100000,
                    'max_roc20' => 0.150000,
                ],
            ]);
        }
        if ($rowCode === 'R02_H3_IHSG_MIXED_REGIME_ONLY') {
            return array_merge($base, [
                'hypothesis_code' => 'H3_MARKET_REGIME_COMPATIBILITY',
                'rule_code' => 'SIGNAL_IHSG_MIXED_REGIME_ONLY',
                'thresholds' => [
                    'benchmark_code' => 'IHSG',
                    'allowed_regimes' => ['MIXED'],
                ],
            ]);
        }

        throw new \RuntimeException('WS_NEW_STRATEGY_R02_UNKNOWN_CATALOG_ROW: '.$rowCode);
    }

    public static function provenance(): array
    {
        return [
            'scope' => 'WS_NEW_STRATEGY_R02',
            'source_eval_id' => 204,
            'source_param_set_id' => 11,
            'source_params_hash' => 'c93bae2b761028d6b236f368d5b19bb4f498715a',
            'r01_artifact_hash' => 'a38e59f6d1422b7823a428ca4f6b724a3fa1a0e7',
            'candidate_count' => self::CATALOG_COUNT,
            'one_primary_idea_per_candidate' => true,
            'decision_time_fields_only' => true,
            'oos_used' => false,
            'canonical_gate_lowered' => false,
            'ticker_blacklist_used' => false,
            'month_blacklist_used' => false,
            'production_ready' => false,
        ];
    }

    private static function row(
        string $rowCode,
        string $rationale,
        float $boMaxExtPct,
        float $rocLo = 0.020000
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
            'min_vol_ratio' => 1.200000,
            'max_vol_ratio' => 5.000000,
            'strong_vol_ratio' => 2.500000,
            'min_atr14_pct' => 0.020000,
            'max_atr14_pct' => 0.060000,
            'atr_ideal_low' => 0.035000,
            'atr_ideal_high' => 0.060000,
            'max_signal_tick_risk_expansion_pct' => null,
            'roc_lo' => $rocLo,
            'roc_hi' => 0.150000,
            'mom_roc20_soft_min' => 0.000000,
            'bo_near_below_pct' => 0.020000,
            'bo_max_ext_pct' => $boMaxExtPct,
            'w_momentum' => 0.300000,
            'w_volume' => 0.100000,
            'w_breakout' => 0.200000,
            'w_risk' => 0.400000,
            'stop_atr_mult' => self::FIXED_STOP_ATR_MULT,
            'min_rr' => self::FIXED_MIN_RR,
            'top_picks_target' => self::FIXED_TOP_PICKS_TARGET,
            'secondary_target' => self::FIXED_SECONDARY_TARGET,
            'top_min_score_q' => 0.800000,
            'top_max_score_total' => 0.999999,
            'secondary_min_score_q' => 0.650000,
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
