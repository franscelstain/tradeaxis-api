<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC17ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06';
    public const CATALOG_VERSION = 'C17';
    public const CATALOG_COUNT = 12;
    public const REFERENCE_ROW_CODE = '00_C16_140_SCORE_65_80_MID_DV20_ONE_R';
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'C17 diagnostic anchor from C16 param 140: one-R target retained, DV20 widened to 2.0B..6.0B, volume lowered only to 1.35..2.0, score window widened to 0.65..0.80 through C17 runtime guard; not a failed-param promotion.',
                2000000000, 6000000000, 1.35, 2.00, 0.010, 0.048, 0.016, 0.034,
                -0.040, 0.020, 0.000, 0.018, 0.016, 0.20, 0.20, 0.24, 0.36, 0.74, 0.62, 1.10, 1.00),
            self::row('01_NEG_ROC20_SCORE_65_80_DV20_2B_6B', 'C17 negative-ROC20 sample recovery from C16 param 134 direction: widen score to 0.65..0.80 and DV20 to 2.0B..6.0B while keeping ROC20 cooling and controlled volume.',
                2000000000, 6000000000, 1.35, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.050, 0.000, -0.005, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.74, 0.62, 0.90, 0.80),
            self::row('02_NEG_ROC20_ONE_R_SCORE_68_82', 'C17 one-R controlled recovery from C16 param 143 direction: use score 0.68..0.82 only with negative ROC20 and volume 1.35..2.0.',
                2000000000, 6000000000, 1.35, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.050, 0.000, -0.005, 0.016, 0.014, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 1.00, 1.00),
            self::row('03_SCORE_70_85_LOW_ATR_NEG_ROC20', 'Segmented score 0.70..0.85 test: permits limited 0.80..0.85 only under low ATR and negative ROC20, not a free 0.80..0.90 bucket.',
                2500000000, 7500000000, 1.35, 2.00, 0.008, 0.040, 0.014, 0.030,
                -0.050, 0.000, -0.005, 0.016, 0.014, 0.20, 0.20, 0.24, 0.36, 0.78, 0.66, 0.90, 0.80),
            self::row('04_DV20_2B_6B_CONTROLLED_PULLBACK', 'C17 controlled DV20 recovery from C16 param 137: DV20 2.0B..6.0B and score 0.65..0.80, preserving ROC5 pullback and canonical risk/exit constraints.',
                2000000000, 6000000000, 1.35, 2.00, 0.010, 0.048, 0.016, 0.034,
                -0.040, 0.020, 0.000, 0.018, 0.016, 0.20, 0.20, 0.24, 0.36, 0.74, 0.62, 0.90, 0.80),
            self::row('05_DV20_25_75_SCORE_68_82', 'C17 controlled upper-liquidity recovery from C16 param 141: DV20 to 7.5B and score 0.68..0.82, still segmented by C17 runtime quality floors.',
                2500000000, 7500000000, 1.35, 2.00, 0.010, 0.050, 0.016, 0.036,
                -0.050, 0.020, 0.000, 0.018, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('06_VOL_150_250_LOW_ATR_NEG_ROC20', 'C17 upper-volume test: volume 1.50..2.50 is allowed only with low ATR, negative ROC20, and score 0.68..0.82.',
                2500000000, 6000000000, 1.50, 2.50, 0.008, 0.038, 0.014, 0.028,
                -0.050, 0.000, -0.005, 0.016, 0.014, 0.20, 0.22, 0.22, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('07_VOL_150_250_ONE_R_LOW_ATR', 'C17 one-R upper-volume segmented test: volume 1.50..2.50 and score 0.70..0.85 only with low ATR and negative ROC20.',
                2500000000, 6000000000, 1.50, 2.50, 0.008, 0.038, 0.014, 0.028,
                -0.050, 0.000, -0.005, 0.016, 0.014, 0.20, 0.22, 0.22, 0.36, 0.78, 0.66, 1.00, 1.00),
            self::row('08_SCORE_70_85_DV20_2B_6B_ROC20_COOLING', 'C17 score 0.70..0.85 recovery is allowed only under DV20 2.0B..6.0B, ROC20 cooling, and volume 1.35..2.0; 0.85..1.00 remains rejected.',
                2000000000, 6000000000, 1.35, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.050, 0.000, -0.005, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.78, 0.66, 0.90, 0.80),
            self::row('09_MID_DV20_LOWER_VOLUME_GUARDED', 'C17 mid-DV20 sample recovery: keeps DV20 2.5B..5B but lowers volume to 1.35 under score 0.65..0.80 and full C17 quality guard.',
                2500000000, 5000000000, 1.35, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.040, 0.020, 0.000, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.74, 0.62, 0.90, 0.80),
            self::row('10_C16_134_DERIVED_NEG_ROC20_SCORE_68_82', 'C17 diagnostic branch from C16 param 134: negative ROC20 with score 0.68..0.82 and wider liquidity, not a param 134 binding.',
                2500000000, 6000000000, 1.35, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.050, 0.000, -0.005, 0.016, 0.016, 0.20, 0.20, 0.24, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('11_C16_143_DERIVED_ONE_R_SCORE_70_85', 'C17 diagnostic branch from C16 param 143: one-R tight target with score 0.70..0.85 only under ROC20 cooling and controlled sample-recovery bounds.',
                2000000000, 6000000000, 1.35, 2.00, 0.010, 0.045, 0.016, 0.032,
                -0.050, 0.000, -0.005, 0.016, 0.014, 0.20, 0.20, 0.24, 0.36, 0.78, 0.66, 1.00, 1.00),
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
            'mode' => 'C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16',
            'reason_code' => 'WATCHLIST_C17_ENTRY_QUALITY_FLOOR_FAIL',
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
            'score_windows_by_row_code' => self::scoreWindowsByRowCode(),
            'score_component_min' => [
                'score_momentum' => 0.18,
                'score_breakout' => 0.46,
                'score_volume' => 0.22,
                'score_risk' => 0.50,
            ],
            'score_component_required_pass_count' => 3,
            'score_component_average_min' => 0.44,
            'trend_metric_floor' => [
                'ma20_slope_pct' => -0.022,
                'rs_20_vs_ihsg' => -0.055,
                'close_vs_ma20_pct' => -0.065,
                'close_vs_ma50_pct' => -0.095,
            ],
            'trend_metric_required_pass_count' => 2,
            'blocked_score_chase' => [
                'score_total_min' => 0.900000,
                'score_total_max' => 1.000000,
                'reason_code' => 'WATCHLIST_C17_SCORE_CHASE_BLOCKED',
            ],
            'disallowed_runtime_axes_until_segmented' => [
                'sector_whitelist',
                'ticker_blacklist',
                'month_blacklist',
                'score_bucket_0_80_0_90_unsegmented',
            ],
        ];
    }

    public static function scoreWindowsByRowCode(): array
    {
        return [
            self::REFERENCE_ROW_CODE => ['min' => 0.650000, 'max' => 0.800000],
            '01_NEG_ROC20_SCORE_65_80_DV20_2B_6B' => ['min' => 0.650000, 'max' => 0.800000],
            '02_NEG_ROC20_ONE_R_SCORE_68_82' => ['min' => 0.680000, 'max' => 0.820000],
            '03_SCORE_70_85_LOW_ATR_NEG_ROC20' => ['min' => 0.700000, 'max' => 0.850000],
            '04_DV20_2B_6B_CONTROLLED_PULLBACK' => ['min' => 0.650000, 'max' => 0.800000],
            '05_DV20_25_75_SCORE_68_82' => ['min' => 0.680000, 'max' => 0.820000],
            '06_VOL_150_250_LOW_ATR_NEG_ROC20' => ['min' => 0.680000, 'max' => 0.820000],
            '07_VOL_150_250_ONE_R_LOW_ATR' => ['min' => 0.700000, 'max' => 0.850000],
            '08_SCORE_70_85_DV20_2B_6B_ROC20_COOLING' => ['min' => 0.700000, 'max' => 0.850000],
            '09_MID_DV20_LOWER_VOLUME_GUARDED' => ['min' => 0.650000, 'max' => 0.800000],
            '10_C16_134_DERIVED_NEG_ROC20_SCORE_68_82' => ['min' => 0.680000, 'max' => 0.820000],
            '11_C16_143_DERIVED_ONE_R_SCORE_70_85' => ['min' => 0.700000, 'max' => 0.850000],
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
        return array_merge(WatchlistBacktestC16ParamGridCatalog::parameterAxes(), [
            'c17.score_window.segmented_0_65_to_0_80',
            'c17.score_window.segmented_0_68_to_0_82',
            'c17.score_window.segmented_0_70_to_0_85',
            'c17.score_chase_0_90_to_1_00_blocked',
            'c17.runtime_metric_bounds.vol_ratio_1_35_to_catalog_strong',
            'c17.runtime_metric_bounds.dv20_2b_to_7_5b_controlled',
            'c17.volume_1_50_to_2_50_requires_low_atr_negative_roc20_segment',
            'c17.quality_preserving_sample_recovery_from_c16',
        ]);
    }

    public static function axisRationale(): array
    {
        return array_merge(WatchlistBacktestC16ParamGridCatalog::axisRationale(), [
            'c17.score_window.segmented_0_65_to_0_80' => 'C17 widens below the C16 0.70 floor only under explicit runtime score-window segmentation to recover sample without using failed-param binding.',
            'c17.score_window.segmented_0_68_to_0_82' => 'C17 tests a modest score bridge above 0.80 only where the row remains controlled by ROC20/volume/ATR guards.',
            'c17.score_window.segmented_0_70_to_0_85' => 'C17 allows 0.80..0.85 only in segmented low-ATR or negative-ROC20 setups; 0.85..1.00 remains outside the C17 design.',
            'c17.score_chase_0_90_to_1_00_blocked' => 'The prior blocked 0.90..1.00 score-chase space remains disallowed and is not used as sample recovery.',
            'c17.runtime_metric_bounds.vol_ratio_1_35_to_catalog_strong' => 'C17 lowers the volume floor from C16 only to 1.35 and only with runtime min/strong bounds, not broad low-volume recovery.',
            'c17.runtime_metric_bounds.dv20_2b_to_7_5b_controlled' => 'C17 explores DV20 2.0B..7.5B as controlled liquidity recovery while keeping row-specific quality segments.',
            'c17.volume_1_50_to_2_50_requires_low_atr_negative_roc20_segment' => 'The wider 1.50..2.50 volume bucket is limited to low-ATR and negative-ROC20 rows to avoid broad recovery that damaged earlier catalogs.',
            'c17.quality_preserving_sample_recovery_from_c16' => 'C17 is derived from C16 diagnostic anchors 140/134/143/137/141 as direction only; no C16 failed row is promoted as binding.',
        ]);
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c16_final_operator_validation_evidence',
                'c16_failure_distribution_min_trades_and_stability',
                'c16_diagnostic_anchors_140_134_143_137_141',
                'c15_final_strategy_quality_evidence',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC_FROM_C16_FAILED_IS_DIAGNOSTIC_EVIDENCE',
            'catalog_mutation_after_release_allowed' => false,
            'production_ready' => false,
            'ticker_blacklist_used' => false,
            'month_blacklist_used' => false,
            'sector_filter_used' => false,
            'sector_whitelist_used' => false,
            'best_of_failed_binding_used' => false,
            'c16_promoted' => false,
            'c16_diagnostic_anchors_only' => [
                'param_140_07_ONE_R_TARGET_MID_DV20',
                'param_134_01_STRICT_CORE_NEGATIVE_ROC20',
                'param_143_10_NEGATIVE_ROC20_ONE_R_TIGHT',
                'param_137_04_DV20_TO_6B_STRICT_SCORE_WINDOW',
                'param_141_08_DV20_TO_7_5B_STRICT_RECOVERY',
            ],
            'primary_blockers_addressed' => [
                'WS_BT_EVAL_MIN_TRADES_FAIL',
                'WS_BT_EVAL_STABILITY_FAIL',
            ],
            'canonical_gate_lowered' => false,
        ];
    }

    public static function manifestRows(): array
    {
        return array_map(function (array $row): array {
            return [
                'row_code' => $row['row_code'],
                'row_hash' => $row['row_hash'],
                'rationale' => $row['rationale'],
                'score_window' => self::scoreWindowsByRowCode()[$row['row_code']] ?? null,
            ];
        }, self::rows());
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
