<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC15ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06';
    public const CATALOG_VERSION = 'C15';
    public const CATALOG_COUNT = 12;
    public const REFERENCE_ROW_CODE = '00_C14_REFERENCE_CONTROLLED_PULLBACK_MID_DV20';
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'C15 reference: C14 variable risk-exit support with controlled ROC5 pullback and mid-liquidity guard from C14 drilldown evidence; not a best-failed-row promotion.',
                2500000000, 5000000000, 1.00, 2.50, 0.010, 0.050, 0.016, 0.036,
                -0.050, 0.020, 0.000, 0.018, 0.018, 0.18, 0.22, 0.24, 0.36, 0.74, 0.62, 1.00, 0.80),
            self::row('01_CORE_MID_DV20_NEUTRAL_ROC20', 'Core controlled-pullback row: ROC5 must be -2%..0%, DV20 2.5B..5B, and ROC20 neutral to avoid high-momentum chase.',
                2500000000, 5000000000, 1.00, 2.20, 0.010, 0.048, 0.016, 0.034,
                -0.040, 0.020, 0.000, 0.018, 0.018, 0.18, 0.22, 0.24, 0.36, 0.74, 0.62, 0.90, 0.80),
            self::row('02_CORE_MID_DV20_LOW_ATR', 'Low-ATR version of the controlled-pullback row to test whether mid-liquidity ROC5 pullbacks survive with tighter volatility.',
                2500000000, 5000000000, 1.00, 2.20, 0.008, 0.040, 0.014, 0.030,
                -0.040, 0.020, 0.000, 0.018, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 1.00, 0.90),
            self::row('03_SAMPLE_RECOVERY_DV20_TO_7_5B', 'Sample recovery row: allow DV20 up to 7.5B while keeping ROC5 pullback and score cap active; avoids broad >20B liquidity chase.',
                2500000000, 7500000000, 1.00, 2.20, 0.010, 0.050, 0.016, 0.036,
                -0.050, 0.020, 0.000, 0.018, 0.018, 0.18, 0.22, 0.24, 0.36, 0.72, 0.60, 1.10, 0.90),
            self::row('04_STRICT_DV20_2_5B_TO_5B_SCORE_CAP', 'Strict mid-liquidity row: direct 2.5B..5B DV20 bucket with lower score quantile and C15 max-score cap to reject 0.9..1 late-entry names.',
                2500000000, 5000000000, 1.00, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.030, 0.020, 0.000, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.72, 0.60, 1.10, 0.90),
            self::row('05_WIDER_VOLUME_CONTROLLED_PULLBACK', 'Tests whether volume can reach 2.5 without becoming a spike-chase when ROC5 and score cap reject overextension.',
                2500000000, 6000000000, 1.00, 2.50, 0.010, 0.050, 0.016, 0.036,
                -0.050, 0.020, 0.000, 0.018, 0.018, 0.18, 0.22, 0.24, 0.36, 0.74, 0.62, 1.20, 1.00),
            self::row('06_NARROW_ROC20_NEGATIVE_TO_FLAT', 'Requires mild ROC20 cooling together with ROC5 pullback; designed to avoid positive-momentum crowding without selecting breakdowns.',
                2500000000, 6000000000, 1.00, 2.20, 0.010, 0.048, 0.016, 0.034,
                -0.050, 0.000, -0.005, 0.018, 0.016, 0.20, 0.20, 0.24, 0.36, 0.74, 0.62, 1.20, 1.00),
            self::row('07_LIGHTLY_POSITIVE_ROC20_WITH_PULLBACK', 'Allows small positive ROC20 only when the latest ROC5 is cooling; tests controlled continuation instead of breakout chase.',
                2500000000, 7500000000, 1.00, 2.20, 0.010, 0.050, 0.016, 0.036,
                -0.020, 0.020, 0.000, 0.018, 0.018, 0.18, 0.22, 0.24, 0.36, 0.74, 0.62, 1.35, 1.00),
            self::row('08_LOW_ATR_SAMPLE_RECOVERY', 'Low-ATR sample recovery with DV20 up to 7.5B and the C15 pullback/score/volume caps active.',
                2000000000, 7500000000, 1.00, 2.20, 0.008, 0.042, 0.014, 0.032,
                -0.050, 0.020, 0.000, 0.018, 0.018, 0.18, 0.22, 0.22, 0.38, 0.72, 0.60, 1.25, 1.00),
            self::row('09_TIGHT_RISK_MID_DV20', 'Tighter execution-risk row for mid-DV20 controlled pullbacks; tests whether nearer stops help without changing C15 entry semantics.',
                2500000000, 5000000000, 1.00, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.040, 0.020, 0.000, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('10_MODERATE_RR_MID_DV20', 'Moderate RR row: keeps C15 candidate universe fixed while testing whether 1.10R target improves expectancy.',
                2500000000, 6000000000, 1.00, 2.20, 0.010, 0.048, 0.016, 0.034,
                -0.040, 0.020, 0.000, 0.018, 0.018, 0.18, 0.22, 0.24, 0.36, 0.74, 0.62, 1.35, 1.10),
            self::row('11_BACKFILL_WITH_C15_GUARDS', 'Broadest allowed C15 backfill: still rejects high-score, high-volume, high-momentum chase; used to test monthly stability without whitening failures.',
                2000000000, 10000000000, 1.00, 2.50, 0.010, 0.052, 0.016, 0.038,
                -0.050, 0.020, 0.000, 0.020, 0.020, 0.18, 0.22, 0.22, 0.38, 0.70, 0.58, 1.20, 0.90),
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
            'mode' => 'C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION',
            'reason_code' => 'WATCHLIST_C15_ENTRY_QUALITY_FLOOR_FAIL',
            'runtime_metric_bounds' => [
                'dv20_between_catalog_min_and_strong' => true,
                'vol_ratio_between_catalog_min_and_strong' => true,
                'atr14_between_catalog_min_and_max' => true,
                'roc20_between_catalog_roc_lo_and_roc_hi' => true,
            ],
            'short_term_momentum_bounds' => [
                'roc5' => [
                    'min' => -0.020,
                    'max' => 0.000,
                ],
            ],
            'score_total_max' => 0.899999,
            'score_component_min' => [
                'score_momentum' => 0.18,
                'score_breakout' => 0.48,
                'score_volume' => 0.20,
                'score_risk' => 0.52,
            ],
            'score_component_required_pass_count' => 3,
            'score_component_average_min' => 0.45,
            'trend_metric_floor' => [
                'ma20_slope_pct' => -0.020,
                'rs_20_vs_ihsg' => -0.050,
                'close_vs_ma20_pct' => -0.060,
                'close_vs_ma50_pct' => -0.090,
            ],
            'trend_metric_required_pass_count' => 2,
            'disallowed_runtime_axes_until_segmented' => [
                'range_position_20',
                'close_to_ll20',
                'breakout_extension',
            ],
        ];
    }

    public static function exitAxisPolicy(): array
    {
        return WatchlistBacktestExitAxisSupport::variableRiskExitAxisDefinition(
            self::FIXED_TOP_PICKS_TARGET,
            self::FIXED_SECONDARY_TARGET,
            [
                'stop_atr_mult_min' => 0.80,
                'stop_atr_mult_max' => 1.70,
                'min_rr_min' => 0.75,
                'min_rr_max' => 1.20,
            ]
        );
    }

    public static function parameterAxes(): array
    {
        return array_merge(WatchlistBacktestC14ParamGridCatalog::parameterAxes(), [
            'c15.runtime_metric_bounds.dv20_between_catalog_min_and_strong',
            'c15.runtime_metric_bounds.vol_ratio_between_catalog_min_and_strong',
            'c15.runtime_metric_bounds.roc20_between_catalog_roc_lo_and_roc_hi',
            'c15.short_term_momentum_bounds.roc5.min_max',
            'c15.score_total_max',
            'c15.anti_overextension.disallowed_high_score_bucket_0_9_to_1',
            'c15.anti_overextension.disallowed_high_momentum_chase',
            'c15.disallowed_runtime_axes_until_segmented',
        ]);
    }

    public static function axisRationale(): array
    {
        return array_merge(WatchlistBacktestC14ParamGridCatalog::axisRationale(), [
            'c15.runtime_metric_bounds.dv20_between_catalog_min_and_strong' => 'C14 drilldown aggregate showed DV20 2.5B..5B had positive weighted average and healthier win rate; C15 keeps this as candidate-selection range, not as score-only decoration.',
            'c15.runtime_metric_bounds.vol_ratio_between_catalog_min_and_strong' => 'C14 drilldown showed high volume buckets underperformed; C15 caps participation to avoid spike/crowded entries.',
            'c15.runtime_metric_bounds.roc20_between_catalog_roc_lo_and_roc_hi' => 'C15 avoids high momentum chase by keeping ROC20 neutral or cooling while still rejecting breakdowns.',
            'c15.short_term_momentum_bounds.roc5.min_max' => 'C14 all-row drilldown showed ROC5 -2%..0 was the most consistent positive candidate-selection bucket; C15 implements it as a hard runtime guard.',
            'c15.score_total_max' => 'C14 drilldown showed score_bucket 0.9..1 underperformed; C15 caps score_total instead of only raising lower score quantiles.',
            'c15.anti_overextension.disallowed_high_score_bucket_0_9_to_1' => 'Explicitly prevents late-entry score-chase behavior identified by C14 root-cause analysis.',
            'c15.anti_overextension.disallowed_high_momentum_chase' => 'Rejects high-momentum chase because roc5/roc10/roc20 high buckets were negative in aggregate.',
            'c15.disallowed_runtime_axes_until_segmented' => 'range_position_20, close_to_ll20, and breakout_extension collapsed into a single diagnostic bucket and are excluded until segmentation is proven.',
        ]);
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c15_root_cause_cross_catalog_summary',
                'c15_prereq_c14_drilldown_summary',
                'c15_prereq_c14_axis_aggregate',
                'c14_variable_risk_exit_final_result',
                'watchlist_runtime_roc5_dv20_score_support_search',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC_FROM_C14_ALL_ROW_DRILLDOWN',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY_C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION',
            'sector_filter_used' => false,
            'sector_evidence_usage' => 'NO_SECTOR_WHITELIST_AXIS_UNUSED_FOR_C15',
            'c01_rejected_as_strategy_catalog' => true,
            'c02_rejected_as_strategy_catalog' => true,
            'c03_rejected_as_strategy_catalog' => true,
            'c04_rejected_as_strategy_catalog' => true,
            'c05_rejected_as_strategy_catalog' => true,
            'c06_rejected_as_strategy_catalog' => true,
            'c07_rejected_as_strategy_catalog' => true,
            'c14_rejected_as_strategy_catalog' => true,
            'candidate_selection_extension' => self::candidateSelectionExtension(),
            'exit_axis_policy' => self::exitAxisPolicy(),
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
        float $stopAtrMult,
        float $minRr
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
            'stop_atr_mult' => $stopAtrMult,
            'min_rr' => $minRr,
            'top_picks_target' => self::FIXED_TOP_PICKS_TARGET,
            'secondary_target' => self::FIXED_SECONDARY_TARGET,
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
