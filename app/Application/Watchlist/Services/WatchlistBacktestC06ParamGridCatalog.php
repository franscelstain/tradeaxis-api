<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC06ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06';
    public const CATALOG_VERSION = 'C06';
    public const CATALOG_COUNT = 12;
    public const REFERENCE_ROW_CODE = '00_C04_BREAKOUT_VOLUME_RISK_REFERENCE';
    public const FIXED_STOP_ATR_MULT = 1.50;
    public const FIXED_MIN_RR = 1.50;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'Reference copy of C04 02_BREAKOUT_VOLUME_RISK_CORE; C06 changes runtime candidate-selection semantics through moderate-liquidity and moderate-volume caps.',
                2500000000, 7500000000, 1.20, 1.50, 0.012, 0.040, 0.018, 0.032,
                0.020, 0.050, 0.010, 0.008, 0.014, 0.10, 0.20, 0.35, 0.35, 0.90, 0.78, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('01_MODERATE_DV20_VOLUME_CORE', 'C06 core row from C01 drilldown: DV20 2.5B-5B, volume 1.2-1.5, ROC 2%-5%, and near-breakout-not-extended entry.',
                2500000000, 5000000000, 1.20, 1.50, 0.012, 0.040, 0.018, 0.032,
                0.020, 0.050, 0.012, 0.020, 0.008, 0.12, 0.12, 0.38, 0.38, 0.82, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('02_MODERATE_DV20_ROC_STRICT', 'Keeps the strongest C01 liquidity bucket and strict moderate ROC while allowing a slightly wider volume band for sample sufficiency.',
                2500000000, 5000000000, 1.10, 1.60, 0.010, 0.042, 0.016, 0.034,
                0.020, 0.050, 0.012, 0.018, 0.006, 0.14, 0.10, 0.38, 0.38, 0.80, 0.68, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('03_VOLUME_1_2_TO_1_5_NEAR_BELOW', 'Tests the C01 volume bucket evidence directly: participation must be present but not spike-chasing.',
                2000000000, 6000000000, 1.20, 1.50, 0.010, 0.045, 0.016, 0.034,
                0.018, 0.055, 0.010, 0.022, 0.006, 0.12, 0.16, 0.36, 0.36, 0.80, 0.68, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('04_DV20_5B_7B_EXTENSION_CONTROL', 'Broadens moderate liquidity only to 7.5B while preserving upper bounds to avoid the C01 high-liquidity underperformance bucket.',
                3000000000, 7500000000, 1.15, 1.60, 0.012, 0.045, 0.018, 0.035,
                0.018, 0.055, 0.010, 0.018, 0.006, 0.10, 0.16, 0.38, 0.36, 0.82, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('05_ROC_2_TO_5_LOW_ATR', 'Emphasizes the C01 ROC 2%-5% evidence and keeps ATR low-to-moderate for downside control.',
                2500000000, 6000000000, 1.15, 1.55, 0.008, 0.038, 0.014, 0.030,
                0.020, 0.050, 0.012, 0.020, 0.006, 0.14, 0.12, 0.36, 0.38, 0.84, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('06_RS_TREND_MODERATE_LIQUIDITY', 'Keeps moderate DV20/volume caps and lets relative-strength/trend confirmation determine whether recovery candidates survive.',
                2500000000, 6500000000, 1.10, 1.65, 0.012, 0.045, 0.018, 0.035,
                0.015, 0.055, 0.010, 0.018, 0.008, 0.18, 0.12, 0.34, 0.36, 0.82, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('07_LOW_EXTENSION_NO_BREAKOUT_CHASE', 'Allows near-breakout or just-below-HH20 entries while rejecting extension chase above the recent range.',
                2500000000, 6500000000, 1.15, 1.55, 0.010, 0.040, 0.016, 0.032,
                0.018, 0.052, 0.010, 0.025, 0.002, 0.12, 0.12, 0.40, 0.36, 0.84, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('08_VOLUME_BROAD_DV20_CAPPED', 'Broader sample probe: volume can extend to 1.8 only while DV20 remains capped and ROC remains moderate.',
                2000000000, 7500000000, 1.10, 1.80, 0.010, 0.045, 0.016, 0.036,
                0.015, 0.060, 0.008, 0.022, 0.010, 0.14, 0.14, 0.34, 0.38, 0.78, 0.66, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('09_SAMPLE_RECOVERY_WITH_CAPS', 'Sample-recovery control that keeps C06 upper caps active instead of reverting to C05 broad uncapped selection.',
                1500000000, 7500000000, 1.05, 1.75, 0.010, 0.050, 0.016, 0.038,
                0.012, 0.060, 0.006, 0.025, 0.012, 0.16, 0.14, 0.34, 0.36, 0.76, 0.64, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('10_STRICT_CORE_FINAL', 'Most selective C06 row: direct moderate-liquidity/volume/ROC bucket alignment with higher quantiles.',
                2500000000, 5000000000, 1.20, 1.50, 0.012, 0.038, 0.018, 0.030,
                0.020, 0.050, 0.014, 0.018, 0.004, 0.10, 0.12, 0.40, 0.38, 0.88, 0.76, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('11_MONTHLY_BACKFILL_CAPPED', 'Monthly stability probe: broad enough to avoid C04 sparse-month brittleness, but capped on liquidity, volume, extension, and ROC.',
                2000000000, 10000000000, 1.05, 1.80, 0.010, 0.048, 0.016, 0.036,
                0.012, 0.065, 0.006, 0.025, 0.014, 0.16, 0.14, 0.34, 0.36, 0.74, 0.62, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
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
            'mode' => 'C06_MODERATE_LIQUIDITY_VOLUME_ROC_STABILITY_FLOOR',
            'reason_code' => 'WATCHLIST_C06_ENTRY_QUALITY_FLOOR_FAIL',
            'runtime_metric_bounds' => [
                'dv20_between_catalog_min_and_strong' => true,
                'vol_ratio_between_catalog_min_and_strong' => true,
                'atr14_between_catalog_min_and_max' => true,
                'roc20_between_catalog_roc_lo_and_roc_hi' => true,
                'close_to_hh20_between_negative_bo_near_below_and_bo_max_ext' => true,
            ],
            'score_component_min' => [
                'score_momentum' => 0.30,
                'score_breakout' => 0.70,
                'score_volume' => 0.25,
                'score_risk' => 0.68,
            ],
            'score_component_required_pass_count' => 3,
            'score_component_average_min' => 0.60,
            'trend_metric_floor' => [
                'ma20_slope_pct' => -0.006,
                'rs_20_vs_ihsg' => -0.010,
                'close_vs_ma20_pct' => -0.030,
                'close_vs_ma50_pct' => -0.050,
            ],
            'trend_metric_required_pass_count' => 3,
        ];
    }

    public static function parameterAxes(): array
    {
        return array_merge(WatchlistBacktestC05ParamGridCatalog::parameterAxes(), [
            'c06.runtime_metric_bounds.dv20_between_catalog_min_and_strong',
            'c06.runtime_metric_bounds.vol_ratio_between_catalog_min_and_strong',
            'c06.runtime_metric_bounds.atr14_between_catalog_min_and_max',
            'c06.runtime_metric_bounds.roc20_between_catalog_roc_lo_and_roc_hi',
            'c06.runtime_metric_bounds.close_to_hh20_between_negative_bo_near_below_and_bo_max_ext',
            'c06.score_component_required_pass_count',
            'c06.score_component_average_min',
            'c06.trend_metric_required_pass_count',
        ]);
    }

    public static function axisRationale(): array
    {
        return array_merge(WatchlistBacktestC05ParamGridCatalog::axisRationale(), [
            'c06.runtime_metric_bounds.dv20_between_catalog_min_and_strong' => 'C01 drilldown showed moderate DV20 buckets outperformed high-liquidity chase; C06 uses the existing strong-DV20 field as an upper cap only for C06.',
            'c06.runtime_metric_bounds.vol_ratio_between_catalog_min_and_strong' => 'C01 drilldown showed volume 1.2..1.5 was healthier than broad/spike volume; C06 caps volume participation instead of rewarding spikes.',
            'c06.runtime_metric_bounds.atr14_between_catalog_min_and_max' => 'Keeps ATR regime explicit in candidate selection rather than relying only on score contribution.',
            'c06.runtime_metric_bounds.roc20_between_catalog_roc_lo_and_roc_hi' => 'Keeps the C01-supported moderate ROC bucket and avoids both weak momentum and high-momentum chase.',
            'c06.runtime_metric_bounds.close_to_hh20_between_negative_bo_near_below_and_bo_max_ext' => 'Keeps entries near the recent range while avoiding far-below reversal traps and overextended breakouts.',
            'c06.score_component_required_pass_count' => 'Retains C05 soft majority confirmation while the new metric bounds remove known weak buckets.',
            'c06.score_component_average_min' => 'Rejects imbalanced candidates that pass only by one strong component.',
            'c06.trend_metric_required_pass_count' => 'Requires broad trend health while allowing one noisy trend metric.',
        ]);
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c01_is_failure_drilldown_runtime_feature_buckets',
                'c04_operator_forensic_final_result',
                'c04_forensic_summary_csv',
                'c05_operator_forensic_final_result',
                'c05_forensic_summary_csv',
                'c05_is_run_1_json_per_row_metrics',
                'watchlist_bt_eval_aggregate_review',
                'weekly_swing_runtime_scoring_component_contract',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY_C06_MODERATE_LIQUIDITY_VOLUME_ROC_CAPS',
            'sector_filter_used' => false,
            'sector_evidence_usage' => 'DIAGNOSTIC_REVIEW_ONLY_NO_SECTOR_FILTER',
            'c02_rejected_as_strategy_catalog' => true,
            'c03_rejected_as_strategy_catalog' => true,
            'c04_rejected_as_strategy_catalog' => true,
            'c05_rejected_as_strategy_catalog' => true,
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
        float $wVolume,
        float $wBreakout,
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
