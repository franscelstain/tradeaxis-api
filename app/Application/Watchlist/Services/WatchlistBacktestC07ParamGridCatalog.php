<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC07ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06';
    public const CATALOG_VERSION = 'C07';
    public const CATALOG_COUNT = 12;
    public const REFERENCE_ROW_CODE = '00_C05_BROAD_SAMPLE_REFERENCE_WITH_C07_OVERLAY';
    public const FIXED_STOP_ATR_MULT = 1.50;
    public const FIXED_MIN_RR = 1.50;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'Reference copy of C05 10_BROAD_SAMPLE_CONTROL with C07 short-term/range/sector confirmation overlay; not best-of-failed selection.',
                1000000000, 10000000000, 1.10, 1.60, 0.010, 0.055, 0.016, 0.038,
                0.010, 0.065, 0.005, 0.014, 0.022, 0.14, 0.28, 0.22, 0.36, 0.78, 0.68, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('01_SHORT_TERM_RANGE_SECTOR_CORE', 'C07 core: keep sample broad enough while requiring short-term ROC, non-low range position, and sector-relative confirmation.',
                1500000000, 12000000000, 1.05, 1.80, 0.010, 0.052, 0.016, 0.038,
                0.006, 0.070, 0.000, 0.016, 0.022, 0.18, 0.24, 0.22, 0.36, 0.76, 0.64, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('02_RANGE_POSITION_BALANCED', 'Uses C07 range-position and close-to-LL20 evidence to avoid reversal traps without forcing C06-style low sample.',
                1500000000, 10000000000, 1.05, 1.75, 0.010, 0.050, 0.016, 0.036,
                0.005, 0.065, 0.000, 0.018, 0.018, 0.16, 0.24, 0.24, 0.36, 0.78, 0.66, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('03_SECTOR_RS_SAMPLE_RECOVERY', 'Broader monthly sample row; C07 sector-relative confirmation must remove weak sector/stock laggards before grouping.',
                1000000000, 15000000000, 1.05, 1.90, 0.010, 0.055, 0.016, 0.040,
                0.004, 0.075, -0.002, 0.018, 0.024, 0.18, 0.24, 0.20, 0.38, 0.74, 0.62, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('04_SHORT_TERM_ACCELERATION_LOW_ATR', 'Low-to-moderate ATR row that asks short-term ROC confirmation to preserve upside while controlling p25 downside.',
                1500000000, 10000000000, 1.08, 1.75, 0.008, 0.042, 0.014, 0.032,
                0.006, 0.065, 0.000, 0.016, 0.020, 0.20, 0.22, 0.22, 0.36, 0.78, 0.66, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('05_ANTI_REVERSAL_NOT_OVEREXTENDED', 'Avoids far-below-low reversal traps and breakout chase while keeping C05-level monthly sample potential.',
                1500000000, 12000000000, 1.05, 1.80, 0.010, 0.048, 0.016, 0.036,
                0.004, 0.060, -0.002, 0.020, 0.014, 0.16, 0.24, 0.24, 0.36, 0.78, 0.66, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('06_BROAD_MONTHLY_STABILITY_WITH_FEATURES', 'Monthly stability probe: broad old axes plus C07 feature overlay instead of C06 liquidity/volume hard caps.',
                1000000000, 18000000000, 1.02, 2.00, 0.010, 0.058, 0.016, 0.040,
                0.002, 0.080, -0.004, 0.022, 0.026, 0.18, 0.24, 0.20, 0.38, 0.72, 0.60, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('07_MODERATE_DV20_FEATURE_OVERLAY', 'Moderate-liquidity evidence from C01/C06 kept, but C07 feature overlay replaces C06 hard volume/ROC brittleness.',
                2000000000, 9000000000, 1.05, 1.80, 0.010, 0.050, 0.016, 0.036,
                0.006, 0.070, 0.000, 0.018, 0.020, 0.16, 0.24, 0.24, 0.36, 0.78, 0.66, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('08_VOLUME_CONTROL_FEATURE_OVERLAY', 'Moderate participation row that avoids volume-spike dependence while relying on C07 short-term and sector confirmation.',
                1000000000, 12000000000, 1.15, 1.70, 0.010, 0.052, 0.016, 0.038,
                0.004, 0.070, -0.002, 0.018, 0.020, 0.18, 0.22, 0.22, 0.38, 0.76, 0.64, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('09_LOW_ATR_RANGE_SECTOR', 'C07 range/sector overlay on low ATR entries to test whether C06 p25 improvement can survive with more trades.',
                1500000000, 12000000000, 1.05, 1.80, 0.008, 0.040, 0.014, 0.032,
                0.004, 0.065, -0.002, 0.020, 0.020, 0.18, 0.22, 0.22, 0.38, 0.78, 0.66, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('10_STRICT_FEATURE_CONFIRM', 'Most selective C07 row; confirms whether new axes can create quality without merely shrinking sample arbitrarily.',
                2000000000, 10000000000, 1.10, 1.75, 0.010, 0.045, 0.016, 0.034,
                0.008, 0.060, 0.002, 0.016, 0.016, 0.20, 0.22, 0.24, 0.34, 0.82, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('11_SAMPLE_BACKFILL_FEATURE_CONFIRM', 'Backfill control: largest sample profile allowed, but only after C07 short-term/range/sector confirmation passes.',
                1000000000, 20000000000, 1.00, 2.10, 0.010, 0.060, 0.016, 0.042,
                0.000, 0.085, -0.005, 0.024, 0.028, 0.18, 0.24, 0.20, 0.38, 0.70, 0.58, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
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
            'mode' => 'C07_SHORT_TERM_RANGE_SECTOR_CONFIRMATION',
            'reason_code' => 'WATCHLIST_C07_ENTRY_QUALITY_FLOOR_FAIL',
            'event_risk_disallow_flags' => [
                'corporate_action_flag',
                'is_suspended',
                'is_uma',
                'event_risk_flag',
            ],
            'range_position_20_pct_between' => [
                'min' => 0.35,
                'max' => 0.96,
            ],
            'confirmation_metric_floor' => [
                'roc5' => -0.015,
                'roc10' => -0.010,
                'close_to_ll20_pct' => 0.035,
                'range_position_20_pct' => 0.45,
                'sector_roc20' => -0.025,
                'rs_20_vs_sector' => -0.025,
                'sector_rs_20_vs_ihsg' => -0.020,
            ],
            'confirmation_metric_required_pass_count' => 5,
            'score_component_min' => [
                'score_momentum' => 0.22,
                'score_breakout' => 0.58,
                'score_volume' => 0.22,
                'score_risk' => 0.52,
            ],
            'score_component_required_pass_count' => 3,
            'score_component_average_min' => 0.55,
            'trend_metric_floor' => [
                'ma20_slope_pct' => -0.015,
                'rs_20_vs_ihsg' => -0.040,
                'close_vs_ma20_pct' => -0.050,
                'close_vs_ma50_pct' => -0.080,
            ],
            'trend_metric_required_pass_count' => 3,
            'raw_setup_guards' => [
                'roc20_between_catalog_roc_lo_and_roc_hi_with_tolerance' => true,
                'roc20_lower_tolerance' => 0.008,
                'roc20_upper_tolerance' => 0.020,
                'close_to_hh20_between_negative_bo_near_below_and_bo_max_ext_with_tolerance' => true,
                'close_to_hh20_lower_tolerance' => 0.008,
                'close_to_hh20_upper_tolerance' => 0.006,
            ],
        ];
    }

    public static function parameterAxes(): array
    {
        return array_merge(WatchlistBacktestC06ParamGridCatalog::parameterAxes(), [
            'c07.optional_runtime_metrics.roc5',
            'c07.optional_runtime_metrics.roc10',
            'c07.optional_runtime_metrics.close_to_ll20_pct',
            'c07.optional_runtime_metrics.range_position_20_pct',
            'c07.optional_runtime_metrics.sector_roc20',
            'c07.optional_runtime_metrics.rs_20_vs_sector',
            'c07.optional_runtime_metrics.sector_rs_20_vs_ihsg',
            'c07.event_risk_disallow_flags',
            'c07.confirmation_metric_required_pass_count',
        ]);
    }

    public static function axisRationale(): array
    {
        return array_merge(WatchlistBacktestC06ParamGridCatalog::axisRationale(), [
            'c07.optional_runtime_metrics.roc5' => 'Short-term momentum confirmation was available in sealed market-data indicators but not previously propagated into watchlist scoring payloads.',
            'c07.optional_runtime_metrics.roc10' => 'Adds entry-timing confirmation between single-month ROC20 and very short-term ROC5.',
            'c07.optional_runtime_metrics.close_to_ll20_pct' => 'Avoids reversal traps close to the 20-day low without adding a sector filter or loosening gates.',
            'c07.optional_runtime_metrics.range_position_20_pct' => 'Uses close position inside the 20-day range to avoid both low-range traps and extreme chase.',
            'c07.optional_runtime_metrics.sector_roc20' => 'Uses sector momentum as confirmation only; it is not a sector whitelist or unsupported sector filter.',
            'c07.optional_runtime_metrics.rs_20_vs_sector' => 'Requires the stock not to materially lag its own sector.',
            'c07.optional_runtime_metrics.sector_rs_20_vs_ihsg' => 'Requires sector context not to materially lag the benchmark.',
            'c07.event_risk_disallow_flags' => 'Rejects available event-risk flags when present instead of pretending they are absent.',
            'c07.confirmation_metric_required_pass_count' => 'Uses soft feature confirmation to avoid C06 sample collapse while filtering C05 weak trades before evaluation.',
        ]);
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c01_is_failure_drilldown_runtime_feature_buckets',
                'c05_operator_forensic_final_result',
                'c05_forensic_summary_csv',
                'c06_operator_forensic_final_result',
                'c06_forensic_summary_csv',
                'market_data_watchlist_read_repository_optional_indicator_surface',
                'eod_indicators_optional_feature_coverage_audit',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY_C07_SHORT_TERM_RANGE_SECTOR_CONFIRMATION',
            'sector_filter_used' => false,
            'sector_evidence_usage' => 'CONFIRMATION_METRICS_ONLY_NO_SECTOR_WHITELIST',
            'c02_rejected_as_strategy_catalog' => true,
            'c03_rejected_as_strategy_catalog' => true,
            'c04_rejected_as_strategy_catalog' => true,
            'c05_rejected_as_strategy_catalog' => true,
            'c06_rejected_as_strategy_catalog' => true,
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
