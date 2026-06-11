<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC03ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06';
    public const CATALOG_VERSION = 'C03';
    public const CATALOG_COUNT = 10;
    public const REFERENCE_ROW_CODE = '00_C02_BEST_AVG_REFERENCE';
    public const FIXED_STOP_ATR_MULT = 1.50;
    public const FIXED_MIN_RR = 1.50;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'Reference copy of C02 06_BROAD_SAMPLE_NEAR_BREAKOUT, the best average-return failed row, for drift measurement only.',
                1000000000, 5000000000, 1.20, 1.80, 0.012, 0.055, 0.025, 0.040,
                0.015, 0.055, 0.005, 0.010, 0.020, 0.15, 0.30, 0.25, 0.30, 0.84, 0.68, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('01_HIGH_SCORE_LOW_ATR_MID_ROC', 'C02 had enough coverage but weak trade quality; tighten score quantile, low ATR, and mid positive ROC to reduce weak daily picks.',
                3000000000, 8000000000, 1.25, 1.60, 0.012, 0.040, 0.018, 0.032,
                0.020, 0.055, 0.010, 0.008, 0.014, 0.10, 0.25, 0.20, 0.45, 0.94, 0.82, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('02_STABILITY_PROXY_TIGHTENED', 'Tightens C02 07 stability-proxy row using only consumed axes: higher score floor, lower extension, and stronger risk weighting.',
                2500000000, 10000000000, 1.20, 1.50, 0.018, 0.045, 0.026, 0.036,
                0.020, 0.050, 0.010, 0.008, 0.014, 0.08, 0.28, 0.18, 0.46, 0.95, 0.84, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('03_DOWNSIDE_P25_LOW_ATR_STRICT_Q', 'Targets C02 p25 downside failure by combining the best p25 low-ATR idea with stricter top quantile and high liquidity.',
                5000000000, 12500000000, 1.20, 1.60, 0.010, 0.038, 0.018, 0.030,
                0.018, 0.045, 0.010, 0.006, 0.012, 0.10, 0.20, 0.20, 0.50, 0.95, 0.84, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('04_ANTI_CHASE_CLOSE_BREAKOUT', 'Reduces chase risk from C02 by requiring very near breakout while avoiding extended candidates and keeping momentum weight low.',
                2500000000, 7500000000, 1.20, 1.40, 0.015, 0.040, 0.022, 0.034,
                0.015, 0.040, 0.010, 0.004, 0.010, 0.08, 0.34, 0.18, 0.40, 0.96, 0.85, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('05_MODERATE_VOLUME_NO_SPIKE', 'C02 showed volume spike chasing did not repair quality; retain moderate participation but avoid excessive strong-volume dependence.',
                3000000000, 8000000000, 1.15, 1.45, 0.012, 0.045, 0.020, 0.035,
                0.020, 0.050, 0.010, 0.006, 0.014, 0.10, 0.22, 0.24, 0.44, 0.94, 0.82, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('06_LIQUIDITY_QUALITY_CORE', 'Raises liquidity quality to suppress illiquid weak candidates while preserving enough IS sample for canonical trade-count coverage.',
                7500000000, 15000000000, 1.20, 1.60, 0.012, 0.042, 0.020, 0.034,
                0.018, 0.050, 0.010, 0.008, 0.014, 0.08, 0.27, 0.20, 0.45, 0.94, 0.83, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('07_LOW_ATR_STABILITY_CORE', 'Stricter low-ATR stability probe intended to improve median and p25 without merely relaxing canonical IS gates.',
                2500000000, 7500000000, 1.25, 1.60, 0.010, 0.035, 0.016, 0.028,
                0.018, 0.045, 0.010, 0.006, 0.012, 0.12, 0.25, 0.18, 0.45, 0.95, 0.84, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('08_RISK_BREAKOUT_BALANCED_HIGH_Q', 'Balances breakout confirmation with risk discipline and high score floors to address negative median return and poor monthly stability.',
                5000000000, 10000000000, 1.15, 1.50, 0.015, 0.045, 0.022, 0.036,
                0.020, 0.055, 0.010, 0.006, 0.012, 0.08, 0.32, 0.18, 0.42, 0.95, 0.84, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('09_CANDIDATE_REDUCTION_EXTREME_Q', 'Aggressive candidate-reduction probe: extreme top quantile with moderate liquidity, volume, ATR, and anti-extension constraints.',
                2500000000, 10000000000, 1.20, 1.50, 0.012, 0.040, 0.020, 0.032,
                0.020, 0.050, 0.010, 0.005, 0.010, 0.05, 0.30, 0.20, 0.45, 0.97, 0.86, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
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
        return WatchlistBacktestC02ParamGridCatalog::parameterAxes();
    }

    public static function axisRationale(): array
    {
        return WatchlistBacktestC02ParamGridCatalog::axisRationale();
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c02_operator_forensic_final_result',
                'c02_forensic_summary_csv',
                'c01_is_failure_drilldown_runtime_feature_buckets',
                'weekly_swing_parameter_registry',
                'deterministic_engineering_rationale',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY_C03_CANDIDATE_REDUCTION_HIGH_SCORE_LOW_ATR_ANTI_CHASE',
            'sector_filter_used' => false,
            'sector_evidence_usage' => 'DIAGNOSTIC_REVIEW_ONLY_EXISTING_AXIS_PROXY',
            'c02_rejected_as_strategy_catalog' => true,
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
