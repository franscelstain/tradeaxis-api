<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC14ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06';
    public const CATALOG_VERSION = 'C14';
    public const CATALOG_COUNT = 12;
    public const REFERENCE_ROW_CODE = '00_C07_CORE_EXIT_NEARER_TARGET_REFERENCE';
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'C14 reference: C07 core candidate selection with a nearer 0.80R target and 1.00 ATR stop to test C10 timeout/target-hit evidence without changing gates.',
                1500000000, 12000000000, 1.05, 1.80, 0.010, 0.052, 0.016, 0.038,
                0.006, 0.070, 0.000, 0.016, 0.022, 0.18, 0.24, 0.22, 0.36, 0.76, 0.64, 1.00, 0.80),
            self::row('01_TIGHT_STOP_NEAR_TARGET_CORE', 'Tighter stop and nearer target to reduce C07 downside and timeout pressure while preserving the C07 confirmation filter.',
                1500000000, 12000000000, 1.05, 1.80, 0.010, 0.052, 0.016, 0.038,
                0.006, 0.070, 0.000, 0.016, 0.022, 0.18, 0.24, 0.22, 0.36, 0.76, 0.64, 0.90, 0.80),
            self::row('02_BALANCED_STOP_NEAR_TARGET_RANGE', 'C07 range-position row with near target and moderate stop to test p25 recovery without sample collapse.',
                1500000000, 10000000000, 1.05, 1.75, 0.010, 0.050, 0.016, 0.036,
                0.005, 0.065, 0.000, 0.018, 0.018, 0.16, 0.24, 0.24, 0.36, 0.78, 0.66, 1.10, 0.90),
            self::row('03_LOW_ATR_NEAR_TARGET', 'Low-ATR C07 profile with 1.10 ATR stop and 0.90R target to favor realized exits over time-expiry.',
                1500000000, 10000000000, 1.08, 1.75, 0.008, 0.042, 0.014, 0.032,
                0.006, 0.065, 0.000, 0.016, 0.020, 0.20, 0.22, 0.22, 0.36, 0.78, 0.66, 1.10, 0.90),
            self::row('04_ANTI_REVERSAL_TIGHT_RISK', 'Anti-reversal C07 row with tighter risk to test whether weak trades are cut before p25 downside breaches.',
                1500000000, 12000000000, 1.05, 1.80, 0.010, 0.048, 0.016, 0.036,
                0.004, 0.060, -0.002, 0.020, 0.014, 0.16, 0.24, 0.24, 0.36, 0.78, 0.66, 1.00, 0.90),
            self::row('05_CORE_ONE_R_TARGET', 'C07 core with one-risk-unit target to measure target-hit improvement against the old 1.50R fixed exit.',
                1500000000, 12000000000, 1.05, 1.80, 0.010, 0.052, 0.016, 0.038,
                0.006, 0.070, 0.000, 0.016, 0.022, 0.18, 0.24, 0.22, 0.36, 0.76, 0.64, 1.20, 1.00),
            self::row('06_RANGE_ONE_R_TARGET', 'Range-position row with a slightly wider stop and one-R target for median-return recovery testing.',
                1500000000, 10000000000, 1.05, 1.75, 0.010, 0.050, 0.016, 0.036,
                0.005, 0.065, 0.000, 0.018, 0.018, 0.16, 0.24, 0.24, 0.36, 0.78, 0.66, 1.25, 1.00),
            self::row('07_LOW_ATR_ONE_R_TARGET', 'Low-ATR entry profile with one-R target and 1.25 ATR stop to balance target accessibility and stop noise.',
                1500000000, 12000000000, 1.05, 1.80, 0.008, 0.040, 0.014, 0.032,
                0.004, 0.065, -0.002, 0.020, 0.020, 0.18, 0.22, 0.22, 0.38, 0.78, 0.66, 1.25, 1.00),
            self::row('08_STRICT_FEATURE_ONE_R_TARGET', 'Strict C07 feature confirmation with one-R target; tests quality-over-sample without using best-of-failed selection.',
                2000000000, 10000000000, 1.10, 1.75, 0.010, 0.045, 0.016, 0.034,
                0.008, 0.060, 0.002, 0.016, 0.016, 0.20, 0.22, 0.24, 0.34, 0.82, 0.70, 1.35, 1.00),
            self::row('09_CORE_MODERATE_RR', 'Moderate 1.10R target on C07 core to test whether a little upside requirement can still improve median return.',
                1500000000, 12000000000, 1.05, 1.80, 0.010, 0.052, 0.016, 0.038,
                0.006, 0.070, 0.000, 0.016, 0.022, 0.18, 0.24, 0.22, 0.36, 0.76, 0.64, 1.35, 1.10),
            self::row('10_RANGE_MODERATE_RR', 'Range-position row with 1.10R target and 1.50 ATR stop as a controlled bridge from C07 fixed execution.',
                1500000000, 10000000000, 1.05, 1.75, 0.010, 0.050, 0.016, 0.036,
                0.005, 0.065, 0.000, 0.018, 0.018, 0.16, 0.24, 0.24, 0.36, 0.78, 0.66, 1.50, 1.10),
            self::row('11_SAMPLE_BACKFILL_NEAR_TARGET', 'Broad C07 sample-backfill profile with nearer target; checks monthly stability without loosening IS gates or OOS.',
                1000000000, 20000000000, 1.00, 2.10, 0.010, 0.060, 0.016, 0.042,
                0.000, 0.085, -0.005, 0.024, 0.028, 0.18, 0.24, 0.20, 0.38, 0.70, 0.58, 1.20, 0.80),
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
        return WatchlistBacktestC07ParamGridCatalog::candidateSelectionExtension();
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
        return array_merge(WatchlistBacktestC07ParamGridCatalog::parameterAxes(), [
            'c14.variable_exit_axis.risk.stop_atr_mult',
            'c14.variable_exit_axis.risk.min_rr',
        ]);
    }

    public static function axisRationale(): array
    {
        return array_merge(WatchlistBacktestC07ParamGridCatalog::axisRationale(), [
            'c14.variable_exit_axis.risk.stop_atr_mult' => 'C10 showed downside and stop-or-timeout dominance under fixed 1.50 ATR stop; C14 varies stop distance inside C13-authorized bounds only.',
            'c14.variable_exit_axis.risk.min_rr' => 'C10 showed low target-hit share and many time-expired trades under fixed 1.50R; C14 tests nearer targets without changing locked IS gates.',
        ]);
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c07_operator_forensic_final_result',
                'c10_exit_model_diagnostic_summary',
                'c11_exit_model_contract_audit',
                'c12_exit_model_redesign_contract',
                'c13_exit_axis_support_audit',
                'watchlist_runtime_risk_stop_atr_mult_min_rr_support',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY_C14_VARIABLE_RISK_EXIT_AXIS_WITH_C07_CONFIRMATION',
            'sector_filter_used' => false,
            'sector_evidence_usage' => 'C07_CONFIRMATION_ONLY_NO_SECTOR_WHITELIST',
            'c02_rejected_as_strategy_catalog' => true,
            'c03_rejected_as_strategy_catalog' => true,
            'c04_rejected_as_strategy_catalog' => true,
            'c05_rejected_as_strategy_catalog' => true,
            'c06_rejected_as_strategy_catalog' => true,
            'c07_rejected_as_strategy_catalog' => true,
            'c13_support_artifact_hash' => '73ba035edfa22f19b4b3525ee3f522241fbae291',
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
