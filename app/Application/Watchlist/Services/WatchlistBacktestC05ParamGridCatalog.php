<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC05ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06';
    public const CATALOG_VERSION = 'C05';
    public const CATALOG_COUNT = 12;
    public const REFERENCE_ROW_CODE = '00_C04_BEST_AVG_REFERENCE';
    public const FIXED_STOP_ATR_MULT = 1.50;
    public const FIXED_MIN_RR = 1.50;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'Reference copy of C04 02_BREAKOUT_VOLUME_RISK_CORE; C05 makes it a forensic reference, not best-of-failed selection.',
                2500000000, 7500000000, 1.20, 1.50, 0.012, 0.040, 0.018, 0.032,
                0.020, 0.050, 0.010, 0.008, 0.014, 0.10, 0.35, 0.20, 0.35, 0.90, 0.78, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('01_SOFT_BALANCED_SAMPLE_CORE', 'C05 core row: restore meaningful sample size with soft balanced component pass-count instead of C04 all-component hard floor.',
                2000000000, 7500000000, 1.15, 1.55, 0.010, 0.045, 0.016, 0.034,
                0.012, 0.060, 0.005, 0.010, 0.018, 0.12, 0.30, 0.20, 0.38, 0.86, 0.74, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('02_MONTHLY_SAMPLE_RECOVERY_CORE', 'Broader C05 row to recover monthly coverage after C04 month-win minimum collapsed to zero.',
                1500000000, 6000000000, 1.10, 1.55, 0.010, 0.050, 0.016, 0.036,
                0.010, 0.055, 0.005, 0.012, 0.020, 0.12, 0.28, 0.22, 0.38, 0.82, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('03_P25_NEAR_THRESHOLD_BALANCED', 'Retains C04 downside improvement direction while relaxing sample brittleness through softer score confirmation.',
                3000000000, 10000000000, 1.15, 1.55, 0.010, 0.040, 0.016, 0.032,
                0.014, 0.055, 0.006, 0.010, 0.016, 0.10, 0.30, 0.20, 0.40, 0.84, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('04_MODERATE_LIQUIDITY_EXPANDED', 'Uses moderate-liquidity C01/C04 evidence with broader daily eligibility to avoid C04 minimum-trade failure.',
                2500000000, 8000000000, 1.10, 1.50, 0.012, 0.048, 0.018, 0.036,
                0.015, 0.060, 0.006, 0.012, 0.020, 0.12, 0.30, 0.22, 0.36, 0.82, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('05_VOLUME_PARTICIPATION_BROAD', 'Tests whether broader volume participation can improve month coverage without lowering canonical gates.',
                1000000000, 5000000000, 1.20, 1.80, 0.012, 0.050, 0.018, 0.038,
                0.012, 0.060, 0.005, 0.012, 0.020, 0.12, 0.28, 0.25, 0.35, 0.80, 0.68, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('06_TREND_RECOVERY_WITH_RS', 'Allows earlier trend recovery only when relative-strength and component pass-count remain constructive.',
                2500000000, 10000000000, 1.10, 1.60, 0.012, 0.050, 0.018, 0.036,
                0.005, 0.050, 0.003, 0.012, 0.020, 0.18, 0.26, 0.20, 0.36, 0.82, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('07_BREAKOUT_NEAR_NOT_EXTENDED', 'Focuses near-breakout entries while C05 soft guard avoids extreme C04 sample collapse.',
                2000000000, 8000000000, 1.15, 1.60, 0.010, 0.045, 0.016, 0.034,
                0.012, 0.055, 0.005, 0.014, 0.010, 0.10, 0.34, 0.20, 0.36, 0.84, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('08_LOW_ATR_SAMPLE_RECOVERY', 'Low ATR row with broader liquidity/score quantile to test p25 control without C04 trade-count failure.',
                2000000000, 8000000000, 1.10, 1.50, 0.008, 0.038, 0.014, 0.030,
                0.010, 0.055, 0.005, 0.010, 0.018, 0.12, 0.28, 0.20, 0.40, 0.84, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('09_ANTI_CHASE_SOFT_CONFIRM', 'Anti-chase row that keeps extension guard soft enough to preserve month-to-month sample coverage.',
                2500000000, 8000000000, 1.15, 1.55, 0.010, 0.045, 0.016, 0.034,
                0.005, 0.045, 0.003, 0.016, 0.010, 0.12, 0.32, 0.20, 0.36, 0.86, 0.74, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('10_BROAD_SAMPLE_CONTROL', 'Broad C05 control row: verifies whether soft guard alone can improve C04 sample and stability without gate changes.',
                1000000000, 10000000000, 1.10, 1.60, 0.010, 0.055, 0.016, 0.038,
                0.010, 0.065, 0.005, 0.014, 0.022, 0.14, 0.28, 0.22, 0.36, 0.78, 0.68, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('11_STRICT_NOT_BRITTLE_FINAL_PROBE', 'Most selective C05 row, but still soft-count based rather than C04 all-or-nothing filtering.',
                2500000000, 9000000000, 1.15, 1.55, 0.010, 0.040, 0.016, 0.032,
                0.015, 0.055, 0.006, 0.010, 0.016, 0.10, 0.32, 0.20, 0.38, 0.88, 0.76, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
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

    public static function candidateSelectionExtension(): array
    {
        return [
            'mode' => 'C05_SOFT_BALANCED_SAMPLE_STABILITY_FLOOR',
            'reason_code' => 'WATCHLIST_C05_ENTRY_QUALITY_FLOOR_FAIL',
            'score_component_min' => [
                'score_momentum' => 0.25,
                'score_breakout' => 0.62,
                'score_volume' => 0.25,
                'score_risk' => 0.55,
            ],
            'score_component_required_pass_count' => 3,
            'score_component_average_min' => 0.58,
            'trend_metric_floor' => [
                'ma20_slope_pct' => -0.012,
                'rs_20_vs_ihsg' => -0.035,
                'close_vs_ma20_pct' => -0.045,
                'close_vs_ma50_pct' => -0.070,
            ],
            'trend_metric_required_pass_count' => 3,
            'raw_setup_guards' => [
                'roc20_between_catalog_roc_lo_and_roc_hi_with_tolerance' => true,
                'roc20_lower_tolerance' => 0.005,
                'roc20_upper_tolerance' => 0.015,
                'close_to_hh20_between_negative_bo_near_below_and_bo_max_ext_with_tolerance' => true,
                'close_to_hh20_lower_tolerance' => 0.006,
                'close_to_hh20_upper_tolerance' => 0.004,
            ],
        ];
    }

    public static function parameterAxes(): array
    {
        return array_merge(WatchlistBacktestC03ParamGridCatalog::parameterAxes(), [
            'c05.score_component_min.score_momentum',
            'c05.score_component_min.score_breakout',
            'c05.score_component_min.score_volume',
            'c05.score_component_min.score_risk',
            'c05.score_component_required_pass_count',
            'c05.score_component_average_min',
            'c05.trend_metric_floor.ma20_slope_pct',
            'c05.trend_metric_floor.rs_20_vs_ihsg',
            'c05.trend_metric_floor.close_vs_ma20_pct',
            'c05.trend_metric_floor.close_vs_ma50_pct',
            'c05.trend_metric_required_pass_count',
            'c05.raw_setup_guard.roc20_tolerance',
            'c05.raw_setup_guard.close_to_hh20_tolerance',
        ]);
    }

    public static function axisRationale(): array
    {
        return array_merge(WatchlistBacktestC03ParamGridCatalog::axisRationale(), [
            'c05.score_component_min.score_momentum' => 'Keep structurally weak trend candidates out without recreating C04 all-component brittleness.',
            'c05.score_component_min.score_breakout' => 'Require constructive breakout quality while allowing enough sample for monthly evaluation.',
            'c05.score_component_min.score_volume' => 'Avoid no-participation picks while keeping moderate volume bucket evidence available.',
            'c05.score_component_min.score_risk' => 'Keep downside-aware ATR/risk quality in candidate selection.',
            'c05.score_component_required_pass_count' => 'Require a majority of components to pass instead of all components, addressing C04 sample collapse.',
            'c05.score_component_average_min' => 'Reject imbalanced low-quality candidates even when individual pass count barely passes.',
            'c05.trend_metric_floor.ma20_slope_pct' => 'Avoid materially deteriorating trend setups.',
            'c05.trend_metric_floor.rs_20_vs_ihsg' => 'Avoid entries materially lagging the benchmark.',
            'c05.trend_metric_floor.close_vs_ma20_pct' => 'Avoid entries too far below short moving-average context.',
            'c05.trend_metric_floor.close_vs_ma50_pct' => 'Avoid entries too far below medium moving-average context.',
            'c05.trend_metric_required_pass_count' => 'Allow one noisy trend field while requiring broad trend health.',
            'c05.raw_setup_guard.roc20_tolerance' => 'Restore sample around C04 ROC bands without accepting broad momentum chase entries.',
            'c05.raw_setup_guard.close_to_hh20_tolerance' => 'Restore sample around breakout bands without accepting far or overextended entries.',
        ]);
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c01_is_failure_drilldown_runtime_feature_buckets',
                'c02_operator_forensic_final_result',
                'c03_operator_forensic_final_result',
                'c03_forensic_summary_csv',
                'c04_operator_forensic_final_result',
                'c04_forensic_summary_csv',
                'c04_is_run_1_json_per_row_metrics',
                'weekly_swing_runtime_scoring_component_contract',
                'deterministic_engineering_rationale',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY_C05_SOFT_BALANCED_SAMPLE_RECOVERY',
            'sector_filter_used' => false,
            'sector_evidence_usage' => 'DIAGNOSTIC_REVIEW_ONLY_NO_SECTOR_FILTER',
            'c02_rejected_as_strategy_catalog' => true,
            'c03_rejected_as_strategy_catalog' => true,
            'c04_rejected_as_strategy_catalog' => true,
            'candidate_selection_extension' => self::candidateSelectionExtension(),
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
