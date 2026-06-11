<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC01ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06';
    public const CATALOG_VERSION = 'C01';
    public const CATALOG_COUNT = 8;
    public const REFERENCE_ROW_CODE = '00_R2_DEFENSIVE_REFERENCE';
    public const FIXED_STOP_ATR_MULT = 1.50;
    public const FIXED_MIN_RR = 1.50;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'Reference copy of R2 defensive row 10 for drift measurement only.',
                7500000000, 15000000000, 1.40, 2.30, 0.015, 0.065, 0.025, 0.045,
                0.020, 0.090, 0.005, 0.010, 0.025, 0.25, 0.30, 0.15, 0.30, 0.95, 0.82, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('01_LOW_ATR_BREADTH', 'Lower ATR ceiling with moderate quantiles to test downside without starving coverage.',
                2500000000, 7500000000, 1.30, 2.10, 0.012, 0.055, 0.022, 0.040,
                0.018, 0.085, 0.005, 0.012, 0.020, 0.25, 0.20, 0.20, 0.35, 0.88, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('02_ULTRA_LOW_ATR_BREADTH', 'Ultra-low ATR breadth probe derived from R1 downside-near-pass evidence.',
                1000000000, 5000000000, 1.25, 2.00, 0.010, 0.040, 0.018, 0.032,
                0.015, 0.075, 0.000, 0.015, 0.020, 0.25, 0.10, 0.25, 0.40, 0.85, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('03_LOW_ATR_VOLUME_STABLE', 'Low ATR plus stronger participation while keeping broad enough quantiles.',
                2500000000, 7500000000, 1.50, 2.40, 0.012, 0.050, 0.020, 0.038,
                0.015, 0.080, 0.005, 0.012, 0.018, 0.25, 0.10, 0.30, 0.35, 0.90, 0.75, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('04_RISK_FIRST_NOT_CHASING', 'Risk-first ranking with a small breakout weight and tight extension cap.',
                5000000000, 10000000000, 1.35, 2.20, 0.015, 0.055, 0.022, 0.040,
                0.015, 0.075, 0.005, 0.010, 0.015, 0.25, 0.10, 0.20, 0.45, 0.90, 0.76, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('05_STABILITY_BREADTH_MOMENTUM', 'Broader candidate coverage with momentum and risk emphasis for monthly stability.',
                2500000000, 7500000000, 1.25, 2.10, 0.015, 0.060, 0.025, 0.045,
                0.010, 0.080, 0.005, 0.015, 0.025, 0.35, 0.10, 0.25, 0.30, 0.86, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('06_HIGH_LIQ_LOW_ATR_MODERATE_Q', 'High liquidity and low ATR with moderate grouping cutoffs.',
                5000000000, 12500000000, 1.30, 2.20, 0.012, 0.050, 0.020, 0.038,
                0.015, 0.080, 0.005, 0.010, 0.020, 0.25, 0.15, 0.25, 0.35, 0.90, 0.74, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('07_DOWNSIDE_CAP_BALANCED_FIXED_EXIT', 'Separates ATR downside effect from exit-axis drift under fixed stop/RR.',
                2500000000, 7500000000, 1.40, 2.20, 0.010, 0.045, 0.018, 0.035,
                0.015, 0.075, 0.005, 0.012, 0.018, 0.25, 0.10, 0.25, 0.40, 0.92, 0.78, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
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

    public static function parameterAxes(): array
    {
        return WatchlistBacktestR2ParamGridCatalog::parameterAxes();
    }

    public static function axisRationale(): array
    {
        return WatchlistBacktestR2ParamGridCatalog::axisRationale();
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'r2_failed_is_diagnostic',
                'r1_low_atr_is_comparison',
                'weekly_swing_parameter_registry',
                'deterministic_engineering_rationale',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY',
        ];
    }

    public static function manifestRows(): array
    {
        return array_values(array_map(function (array $row): array {
            unset($row['policy_code'], $row['catalog_code'], $row['catalog_version'], $row['catalog_hash']);

            return $row;
        }, self::rows()));
    }

    private static function row(
        string $rowCode,
        string $rationale,
        int $minDv20Idr,
        int $dv20StrongIdr,
        float $minVolRatio,
        float $strongVolRatio,
        float $minAtr14Pct,
        float $maxAtr14Pct,
        float $atrIdealLow,
        float $atrIdealHigh,
        float $rocLo,
        float $rocHi,
        float $momRoc20SoftMin,
        float $boNearBelowPct,
        float $boMaxExtPct,
        float $wMomentum,
        float $wBreakout,
        float $wVolume,
        float $wRisk,
        float $topMinScoreQ,
        float $secondaryMinScoreQ,
        int $topPicksTarget,
        int $secondaryTarget
    ): array {
        return [
            'policy_code' => 'WS',
            'catalog_code' => self::CATALOG_CODE,
            'catalog_version' => self::CATALOG_VERSION,
            'catalog_hash' => '',
            'row_code' => $rowCode,
            'row_hash' => '',
            'rationale' => $rationale,
            'min_dv20_idr' => $minDv20Idr,
            'dv20_strong_idr' => $dv20StrongIdr,
            'min_vol_ratio' => $minVolRatio,
            'strong_vol_ratio' => $strongVolRatio,
            'min_atr14_pct' => $minAtr14Pct,
            'max_atr14_pct' => $maxAtr14Pct,
            'atr_ideal_low' => $atrIdealLow,
            'atr_ideal_high' => $atrIdealHigh,
            'roc_lo' => $rocLo,
            'roc_hi' => $rocHi,
            'mom_roc20_soft_min' => $momRoc20SoftMin,
            'bo_near_below_pct' => $boNearBelowPct,
            'bo_max_ext_pct' => $boMaxExtPct,
            'w_momentum' => $wMomentum,
            'w_volume' => $wVolume,
            'w_breakout' => $wBreakout,
            'w_risk' => $wRisk,
            'stop_atr_mult' => self::FIXED_STOP_ATR_MULT,
            'min_rr' => self::FIXED_MIN_RR,
            'top_picks_target' => $topPicksTarget,
            'secondary_target' => $secondaryTarget,
            'top_min_score_q' => $topMinScoreQ,
            'secondary_min_score_q' => $secondaryMinScoreQ,
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
        if (array_keys($value) === range(0, count($value) - 1)) {
            return array_map([self::class, 'normalize'], $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = self::normalize($item);
        }

        return $value;
    }
}
