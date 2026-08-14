<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_BREAKOUT_INTEGRITY_B01_2026_07';
    public const CATALOG_VERSION = 'B01';
    public const CATALOG_COUNT = 1;
    public const ROW_CODE = 'B01_C1_CLOSE_TO_HH20_FLOOR_NEG5';
    public const PRIMARY_HYPOTHESIS_CODE = 'B01_H1_BREAKOUT_DISTANCE_INTEGRITY';
    public const RULE_CODE =
        'SIGNAL_ROC20_10_TO_15_IHSG_NON_WEAK_MIN_PRICE_BREAKOUT_FLOOR';

    public static function rows(): array
    {
        $rows = [[
            'policy_code' => 'WS',
            'catalog_code' => self::CATALOG_CODE,
            'catalog_version' => self::CATALOG_VERSION,
            'catalog_hash' => '',
            'row_code' => self::ROW_CODE,
            'row_hash' => '',
            'rationale' => 'One-idea B01: retain exact P01 C1 core and execution; reject signals more than 5% below HH20 using exact signal-date evidence.',
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
            'max_signal_tick_risk_expansion_pct' => null,
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
            'notes' => self::CATALOG_CODE.'_'.self::ROW_CODE,
        ]];
        $hashPayload = $rows;
        unset($hashPayload[0]['catalog_hash'], $hashPayload[0]['row_hash']);
        $catalogHash = self::hashPayload($hashPayload);
        $rows[0]['catalog_hash'] = $catalogHash;
        $rows[0]['row_hash'] = sha1(self::CATALOG_CODE.'|'.self::ROW_CODE);

        return $rows;
    }

    public static function hash(): string
    {
        return (string) self::rows()[0]['catalog_hash'];
    }

    public static function isKnownRow(string $rowCode): bool
    {
        return $rowCode === self::ROW_CODE;
    }

    public static function researchSelection(): array
    {
        return [
            'schema_version' => 'WS_NEW_STRATEGY_RESEARCH_SELECTION_V1',
            'hypothesis_code' => self::ROW_CODE,
            'rule_code' => self::RULE_CODE,
            'signal_date_only' => true,
            'oos_used' => false,
            'thresholds' => [
                'min_roc20' => 0.10,
                'max_roc20' => 0.15,
                'benchmark_code' => 'IHSG',
                'allowed_regimes' => ['STRONG', 'MIXED'],
                'min_signal_close_price' => 50,
                'min_close_to_hh20_pct' => -0.05,
            ],
        ];
    }

    public static function researchExecution(): array
    {
        return WatchlistBacktestPriceQualityP01ParamGridCatalog::researchExecution();
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
