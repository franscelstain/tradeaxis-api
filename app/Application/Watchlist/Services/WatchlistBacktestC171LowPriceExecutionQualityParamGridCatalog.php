<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_LOW_PRICE_EXECUTION_QUALITY_C01_2026_07';
    public const CATALOG_VERSION = 'C01';
    public const CATALOG_COUNT = 5;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;
    public const FIXED_STOP_ATR_MULT = 1.5;
    public const FIXED_MIN_RR = 1.5;

    public static function rows(): array
    {
        $rows = [
            self::row(
                'C171_C01_A_TICK_EXPANSION_MILD',
                'Primary-focus mild guard: cap signal-date IDX tick-risk expansion at 1.50% while preserving the immutable paramset-5 anchor scoring and quality bounds.',
                0.015000,
                0.300000, 0.200000, 0.300000, 0.200000
            ),
            self::row(
                'C171_C01_B_TICK_EXPANSION_BALANCED',
                'Primary-focus balanced guard: cap signal-date IDX tick-risk expansion at 1.00%, between the anchor LT_200 average expansion and the GE_200 comparison segment.',
                0.010000,
                0.300000, 0.200000, 0.300000, 0.200000
            ),
            self::row(
                'C171_C01_C_TICK_EXPANSION_STRICT',
                'Primary-focus strict guard: cap signal-date IDX tick-risk expansion at 0.50%, above the anchor 100-199 average and below the 50-99 average expansion.',
                0.005000,
                0.300000, 0.200000, 0.300000, 0.200000
            ),
            self::row(
                'C171_C01_D_SCORE_BALANCED_RECALIBRATION',
                'Isolated score comparator: modestly reduce breakout and volume emphasis while increasing momentum and risk without applying a tick-risk guard.',
                null,
                0.350000, 0.150000, 0.250000, 0.250000
            ),
            self::row(
                'C171_C01_E_SCORE_RISK_FORWARD_RECALIBRATION',
                'Isolated score comparator: materially de-emphasize volume and breakout saturation while increasing risk quality; no tick-risk guard is combined before single-factor official IS evidence.',
                null,
                0.300000, 0.100000, 0.200000, 0.400000
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
        $rows = self::rows();

        return (string) ($rows[0]['catalog_hash'] ?? self::hashPayload([]));
    }

    public static function provenance(): array
    {
        return [
            'source_eval_id' => 192,
            'source_param_set_id' => 5,
            'source_params_hash' => 'e49b47449be1bc59659455d315bb6aaf5f4f9491',
            'comparative_diagnostic_artifact_hash' => 'f548a75e62ab954a3d35034b3b4452279693059e',
            'hypothesis_lock_artifact_hash' => '84a699996dc8ac2eeea2bd921936a2d866f216ad',
            'primary_focus' => 'LOW_PRICE_EXECUTION_QUALITY',
            'uses_decision_time_fields_only' => true,
            'uses_oos' => false,
            'ticker_blacklist_used' => false,
            'month_blacklist_used' => false,
            'return_field_used_for_selection' => false,
            'canonical_gate_lowered' => false,
            'catalog_mutation_after_release_allowed' => false,
            'production_ready' => false,
        ];
    }

    private static function row(
        string $rowCode,
        string $rationale,
        ?float $maxSignalTickRiskExpansionPct,
        float $wMomentum,
        float $wVolume,
        float $wBreakout,
        float $wRisk
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
            'max_signal_tick_risk_expansion_pct' => $maxSignalTickRiskExpansionPct,
            'roc_lo' => 0.020000,
            'roc_hi' => 0.150000,
            'mom_roc20_soft_min' => 0.000000,
            'bo_near_below_pct' => 0.020000,
            'bo_max_ext_pct' => 0.050000,
            'w_momentum' => $wMomentum,
            'w_volume' => $wVolume,
            'w_breakout' => $wBreakout,
            'w_risk' => $wRisk,
            'stop_atr_mult' => self::FIXED_STOP_ATR_MULT,
            'min_rr' => self::FIXED_MIN_RR,
            'top_picks_target' => self::FIXED_TOP_PICKS_TARGET,
            'secondary_target' => self::FIXED_SECONDARY_TARGET,
            'top_min_score_q' => 0.800000,
            'secondary_min_score_q' => 0.650000,
            'top_max_score_total' => 0.999999,
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
