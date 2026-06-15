<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC16ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C16_2026_06';
    public const CATALOG_VERSION = 'C16';
    public const CATALOG_COUNT = 12;
    public const REFERENCE_ROW_CODE = '00_C15_130_SHAPE_TIGHT_SCORE_VOLUME_CONTROL';
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'C16 reference shape from C15 param 130 evidence, but with C16-owned absolute score window 0.70..0.799999 and volume 1.5..2.0 runtime guard; not a C15 promotion.',
                2500000000, 5000000000, 1.50, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.040, 0.020, 0.000, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('01_STRICT_CORE_NEGATIVE_ROC20', 'Strict anchor row: DV20 2.5B..5B, vol_ratio 1.5..2.0, ROC5 controlled pullback, and ROC20 cooling -5%..0 to avoid momentum chase.',
                2500000000, 5000000000, 1.50, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.050, 0.000, -0.005, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('02_STRICT_CORE_LOW_POSITIVE_ROC20', 'Strict continuation row: keeps C16 score/volume/ROC5 guards and allows only small positive ROC20 0..2%, not high-momentum chase.',
                2500000000, 5000000000, 1.50, 2.00, 0.010, 0.045, 0.016, 0.032,
                0.000, 0.020, 0.000, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('03_LOW_ATR_MID_DV20_CONTROL', 'Low-ATR strict mid-liquidity row to test whether the C15 best-failed quality shape survives with lower volatility and the C16 score window.',
                2500000000, 5000000000, 1.50, 2.00, 0.008, 0.040, 0.014, 0.030,
                -0.040, 0.010, 0.000, 0.016, 0.014, 0.20, 0.20, 0.24, 0.36, 0.78, 0.66, 1.00, 0.80),
            self::row('04_DV20_TO_6B_STRICT_SCORE_WINDOW', 'Controlled sample recovery row: DV20 can reach 6B, but only under C16 absolute score 0.70..0.799999 and vol_ratio 1.5..2.0 guards.',
                2500000000, 6000000000, 1.50, 2.00, 0.010, 0.048, 0.016, 0.034,
                -0.040, 0.020, 0.000, 0.018, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('05_VOLUME_15_TO_22_GUARDED_TEST', 'Controlled volume recovery row: tests 1.5..2.2 only with the C16 anti-overextension score window and ROC5 pullback guard.',
                2500000000, 5000000000, 1.50, 2.20, 0.010, 0.045, 0.016, 0.032,
                -0.040, 0.020, 0.000, 0.016, 0.016, 0.20, 0.22, 0.22, 0.36, 0.78, 0.66, 0.95, 0.80),
            self::row('06_TIGHT_ATR_TIGHT_RISK', 'Tighter risk/ATR row inspired by C15 param 130 downside shape without loosening entry quality or canonical IS gates.',
                2500000000, 5000000000, 1.50, 2.00, 0.010, 0.042, 0.016, 0.030,
                -0.040, 0.020, 0.000, 0.016, 0.014, 0.20, 0.20, 0.24, 0.36, 0.78, 0.66, 0.85, 0.75),
            self::row('07_ONE_R_TARGET_MID_DV20', 'One-R target row: tests exit accessibility after entry quality is constrained by C16 score/volume/pullback runtime guards.',
                2500000000, 5000000000, 1.50, 2.00, 0.010, 0.048, 0.016, 0.034,
                -0.040, 0.020, 0.000, 0.018, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 1.10, 1.00),
            self::row('08_DV20_TO_7_5B_STRICT_RECOVERY', 'Largest allowed C16 liquidity recovery: DV20 to 7.5B but still rejects 0.8..0.9 score and low-volume 1.0..1.5 buckets.',
                2500000000, 7500000000, 1.50, 2.00, 0.010, 0.050, 0.016, 0.036,
                -0.050, 0.020, 0.000, 0.018, 0.016, 0.20, 0.20, 0.24, 0.36, 0.78, 0.66, 0.90, 0.80),
            self::row('09_VOLUME_15_TO_25_LOW_ATR_STRICT', 'Upper volume boundary test: allows 1.5..2.5 only with low ATR and C16 score window to avoid recreating broad C15 recovery failures.',
                2500000000, 5000000000, 1.50, 2.50, 0.008, 0.038, 0.014, 0.028,
                -0.040, 0.020, 0.000, 0.016, 0.014, 0.20, 0.22, 0.22, 0.36, 0.80, 0.68, 0.90, 0.80),
            self::row('10_NEGATIVE_ROC20_ONE_R_TIGHT', 'ROC20 cooling plus one-R target row; tests monthly stability recovery without chasing positive momentum or lowering volume quality.',
                2500000000, 5000000000, 1.50, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.050, 0.000, -0.005, 0.016, 0.014, 0.20, 0.20, 0.24, 0.36, 0.78, 0.66, 1.00, 1.00),
            self::row('11_DV20_TO_6B_NEG_ROC20_LOW_ATR', 'Quality-preserving sample recovery: modest DV20 expansion to 6B only with low ATR, ROC20 cooling, vol_ratio 1.5..2.0, and C16 score window.',
                2500000000, 6000000000, 1.50, 2.00, 0.008, 0.040, 0.014, 0.030,
                -0.050, 0.000, -0.005, 0.016, 0.014, 0.20, 0.20, 0.24, 0.36, 0.80, 0.68, 1.00, 0.90),
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
            'mode' => 'C16_CONTROLLED_PULLBACK_SCORE_WINDOW_VOLUME_QUALITY_RECOVERY',
            'reason_code' => 'WATCHLIST_C16_ENTRY_QUALITY_FLOOR_FAIL',
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
            'score_total_min' => 0.700000,
            'score_total_max' => 0.799999,
            'score_component_min' => [
                'score_momentum' => 0.18,
                'score_breakout' => 0.48,
                'score_volume' => 0.24,
                'score_risk' => 0.52,
            ],
            'score_component_required_pass_count' => 3,
            'score_component_average_min' => 0.46,
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
        return array_merge(WatchlistBacktestC15ParamGridCatalog::parameterAxes(), [
            'c16.runtime_metric_bounds.vol_ratio_1_5_to_catalog_strong',
            'c16.short_term_momentum_bounds.roc5.min_max',
            'c16.score_total_min',
            'c16.score_total_max',
            'c16.score_window.disallowed_score_bucket_0_8_to_0_9',
            'c16.quality_preserving_sample_recovery',
        ]);
    }

    public static function axisRationale(): array
    {
        return array_merge(WatchlistBacktestC15ParamGridCatalog::axisRationale(), [
            'c16.runtime_metric_bounds.vol_ratio_1_5_to_catalog_strong' => 'C15 evidence showed vol_ratio 1.5..2.0 was the useful bucket while 1.0..1.5 degraded quality; C16 raises the runtime lower bound instead of relying on quantile score decoration.',
            'c16.short_term_momentum_bounds.roc5.min_max' => 'C16 preserves the C15 controlled pullback ROC5 -2%..0 runtime guard because it was a useful quality signal, not a ticker/month exclusion.',
            'c16.score_total_min' => 'C16 introduces an explicit absolute score_total floor so the 0.7..0.8 diagnostic rationale is consumed at runtime rather than confused with top_min_score_q.',
            'c16.score_total_max' => 'C16 caps score_total below 0.8 to avoid the C15-observed 0.8..0.9 overextension bucket and the previously blocked 0.9..1 score chase.',
            'c16.score_window.disallowed_score_bucket_0_8_to_0_9' => 'Score bucket 0.8..0.9 is rejected as overextended unless future segmented evidence proves otherwise.',
            'c16.quality_preserving_sample_recovery' => 'Sample recovery is limited to modest DV20/volume expansion with the same score/ROC5/ROC20 quality guards; broad C15 rows 129/132 are not used as basis.',
        ]);
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c15_final_evidence_summary',
                'c15_fix4_param_summary',
                'c15_root_cause_decision_supersession_note',
                'c15_root_cause_and_diagnostic_prerequisite_note',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC_FROM_C15_FAILED_QUALITY_EVIDENCE',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'best_failed_anchor_used_as_diagnostic_only' => true,
            'focus' => 'DOWNSIDE_STABILITY_C16_CONTROLLED_PULLBACK_SCORE_07_08_VOLUME_15_20_STABILITY_RECOVERY',
            'sector_filter_used' => false,
            'ticker_blacklist_used' => false,
            'month_blacklist_used' => false,
            'c15_rejected_as_strategy_catalog' => true,
            'c15_promoted' => false,
            'candidate_selection_extension' => self::candidateSelectionExtension(),
            'exit_axis_policy' => self::exitAxisPolicy(),
            'design_anchors' => [
                'c15_param_122_diagnostic_only',
                'c15_param_130_diagnostic_only',
            ],
            'negative_recovery_samples_rejected_as_basis' => [
                'c15_param_129',
                'c15_param_132',
            ],
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
