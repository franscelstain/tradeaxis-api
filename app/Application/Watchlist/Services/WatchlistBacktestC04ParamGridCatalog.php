<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC04ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06';
    public const CATALOG_VERSION = 'C04';
    public const CATALOG_COUNT = 10;
    public const REFERENCE_ROW_CODE = '00_C03_LOW_ATR_STABILITY_REFERENCE';
    public const FIXED_STOP_ATR_MULT = 1.50;
    public const FIXED_MIN_RR = 1.50;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'Reference copy of C03 07_LOW_ATR_STABILITY_CORE; C04 guard makes it a drift reference, not best-of-failed selection.',
                2500000000, 7500000000, 1.25, 1.60, 0.010, 0.035, 0.016, 0.028,
                0.018, 0.045, 0.010, 0.006, 0.012, 0.12, 0.25, 0.18, 0.45, 0.95, 0.84, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('01_BALANCED_COMPONENT_FLOOR_CORE', 'C04 core row: balanced breakout-volume-risk quality floor before recommendation, with moderate ROC and low ATR.',
                2500000000, 7500000000, 1.20, 1.50, 0.012, 0.038, 0.018, 0.030,
                0.020, 0.050, 0.010, 0.006, 0.012, 0.10, 0.30, 0.20, 0.40, 0.92, 0.80, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('02_BREAKOUT_VOLUME_RISK_CORE', 'Prior C01/C03 evidence favored breakout, volume, and risk components; this row weights those components without chasing high momentum.',
                2500000000, 7500000000, 1.20, 1.50, 0.012, 0.040, 0.018, 0.032,
                0.020, 0.050, 0.010, 0.008, 0.014, 0.10, 0.35, 0.20, 0.35, 0.90, 0.78, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('03_MODERATE_LIQUIDITY_ROC_BAND', 'Uses C01 bucket evidence for 2.5B-5B DV20, 1.2-1.5 volume, and 2%-5% ROC while C04 guard rejects weak trend traps.',
                2500000000, 5000000000, 1.20, 1.50, 0.012, 0.045, 0.020, 0.034,
                0.020, 0.050, 0.010, 0.008, 0.014, 0.10, 0.30, 0.25, 0.35, 0.88, 0.76, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('04_PRIOR_STRENGTH_NOT_CHASE', 'Requires constructive trend confirmation through C04 guard while keeping momentum weight low because high momentum alone underperformed.',
                2500000000, 8000000000, 1.15, 1.45, 0.015, 0.042, 0.022, 0.034,
                0.015, 0.045, 0.005, 0.006, 0.012, 0.10, 0.32, 0.20, 0.38, 0.90, 0.78, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('05_LOW_ATR_DOWNSIDE_CONTROL', 'Downside control row from C03 p25 improvement: lower ATR plus risk-dominant scoring and balanced component floor.',
                5000000000, 10000000000, 1.20, 1.50, 0.010, 0.035, 0.016, 0.028,
                0.018, 0.045, 0.010, 0.006, 0.012, 0.10, 0.25, 0.20, 0.45, 0.92, 0.80, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('06_MONTHLY_STABILITY_LIQUID', 'Uses C03 liquidity row monthly-win relative strength while C04 guard removes component-imbalanced and weak-trend picks.',
                7500000000, 15000000000, 1.20, 1.60, 0.012, 0.040, 0.020, 0.032,
                0.018, 0.045, 0.010, 0.008, 0.014, 0.10, 0.28, 0.22, 0.40, 0.88, 0.76, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('07_ANTI_REVERSAL_TRAP_CONFIRM', 'Strict anti-reversal-trap probe: tight breakout band, low ATR, and C04 trend/balanced-component confirmation before TOP selection.',
                2500000000, 7500000000, 1.20, 1.50, 0.012, 0.038, 0.018, 0.030,
                0.020, 0.050, 0.010, 0.004, 0.010, 0.05, 0.35, 0.20, 0.40, 0.94, 0.82, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('08_BROAD_SAMPLE_QUALITY_FLOOR', 'Broader sample row to keep trade count meaningful after the C04 quality floor while still rejecting far/extended and weak-trend candidates.',
                1000000000, 5000000000, 1.20, 1.60, 0.012, 0.045, 0.020, 0.034,
                0.018, 0.055, 0.010, 0.008, 0.014, 0.10, 0.30, 0.25, 0.35, 0.86, 0.74, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('09_STRICT_BALANCED_FINAL_PROBE', 'Most selective C04 probe: strict score quantile plus balanced component floor, but without lowering any canonical IS gate.',
                3000000000, 8000000000, 1.20, 1.50, 0.012, 0.038, 0.018, 0.030,
                0.020, 0.050, 0.010, 0.005, 0.010, 0.05, 0.33, 0.20, 0.42, 0.94, 0.82, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
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
            'mode' => 'C04_BALANCED_COMPONENT_AND_TREND_FLOOR',
            'reason_code' => 'WATCHLIST_C04_ENTRY_QUALITY_FLOOR_FAIL',
            'score_component_min' => [
                'score_momentum' => 0.35,
                'score_breakout' => 0.80,
                'score_volume' => 0.40,
                'score_risk' => 0.70,
            ],
            'trend_metric_floor' => [
                'ma20_slope_pct' => -0.005,
                'rs_20_vs_ihsg' => -0.020,
                'close_vs_ma20_pct' => -0.030,
                'close_vs_ma50_pct' => -0.050,
            ],
            'raw_setup_guards' => [
                'roc20_between_catalog_roc_lo_and_roc_hi' => true,
                'close_to_hh20_between_negative_bo_near_below_and_bo_max_ext' => true,
            ],
        ];
    }

    public static function parameterAxes(): array
    {
        return array_merge(WatchlistBacktestC03ParamGridCatalog::parameterAxes(), [
            'c04.score_component_min.score_momentum',
            'c04.score_component_min.score_breakout',
            'c04.score_component_min.score_volume',
            'c04.score_component_min.score_risk',
            'c04.trend_metric_floor.ma20_slope_pct',
            'c04.trend_metric_floor.rs_20_vs_ihsg',
            'c04.trend_metric_floor.close_vs_ma20_pct',
            'c04.trend_metric_floor.close_vs_ma50_pct',
            'c04.raw_setup_guard.roc20_between_catalog_roc_lo_and_roc_hi',
            'c04.raw_setup_guard.close_to_hh20_between_negative_bo_near_below_and_bo_max_ext',
        ]);
    }

    public static function axisRationale(): array
    {
        return array_merge(WatchlistBacktestC03ParamGridCatalog::axisRationale(), [
            'c04.score_component_min.score_momentum' => 'Prevent dead-cat or structurally weak trend entries without overweighting momentum.',
            'c04.score_component_min.score_breakout' => 'Require constructive near-breakout quality before TOP/SECONDARY selection.',
            'c04.score_component_min.score_volume' => 'Require participation quality before recommendation can consume the plan item.',
            'c04.score_component_min.score_risk' => 'Require ATR/risk component quality before metric evaluation.',
            'c04.trend_metric_floor.ma20_slope_pct' => 'Avoid reversal traps with materially declining short trend.',
            'c04.trend_metric_floor.rs_20_vs_ihsg' => 'Avoid entries materially lagging the benchmark on prior strength.',
            'c04.trend_metric_floor.close_vs_ma20_pct' => 'Avoid entries too far below short moving average context.',
            'c04.trend_metric_floor.close_vs_ma50_pct' => 'Avoid entries too far below medium moving average context.',
            'c04.raw_setup_guard.roc20_between_catalog_roc_lo_and_roc_hi' => 'Make the C04 ROC band a hard anti-chase and weak-momentum guard.',
            'c04.raw_setup_guard.close_to_hh20_between_negative_bo_near_below_and_bo_max_ext' => 'Make the C04 breakout band a hard far/overextended-entry guard.',
        ]);
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c01_is_failure_drilldown_runtime_feature_buckets',
                'c02_operator_forensic_final_result',
                'c02_forensic_summary_csv',
                'c03_operator_forensic_final_result',
                'c03_is_run_1_json_per_row_metrics',
                'c03_is_run_2_json_per_row_metrics',
                'weekly_swing_runtime_scoring_component_contract',
                'deterministic_engineering_rationale',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY_C04_BALANCED_COMPONENT_TREND_CONFIRMATION',
            'sector_filter_used' => false,
            'sector_evidence_usage' => 'DIAGNOSTIC_REVIEW_ONLY_NO_SECTOR_FILTER',
            'c02_rejected_as_strategy_catalog' => true,
            'c03_rejected_as_strategy_catalog' => true,
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
