<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC02ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06';
    public const CATALOG_VERSION = 'C02';
    public const CATALOG_COUNT = 8;
    public const REFERENCE_ROW_CODE = '00_C01_NEAREST_GATE_REFERENCE';
    public const FIXED_STOP_ATR_MULT = 1.50;
    public const FIXED_MIN_RR = 1.50;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::REFERENCE_ROW_CODE, 'Reference copy of C01 01_LOW_ATR_BREADTH, the nearest avg-return gate row, for drift measurement only.',
                2500000000, 7500000000, 1.30, 2.10, 0.012, 0.055, 0.022, 0.040,
                0.018, 0.085, 0.005, 0.012, 0.020, 0.25, 0.20, 0.20, 0.35, 0.88, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('01_NEAR_BREAKOUT_MODERATE_LIQUIDITY', 'C01 drilldown favored close_to_hh20_pct -0.02..0 and moderate DV20; tighten chase while keeping enough sample.',
                2500000000, 7500000000, 1.20, 1.50, 0.015, 0.055, 0.025, 0.040,
                0.020, 0.050, 0.005, 0.010, 0.018, 0.15, 0.30, 0.25, 0.30, 0.88, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('02_MID_LIQUIDITY_VOLUME_BALANCED', 'Balances 2.5B-15B DV20 buckets and avoids volume spike chasing by using moderate strong_vol_ratio.',
                2500000000, 10000000000, 1.20, 2.00, 0.015, 0.050, 0.025, 0.040,
                0.020, 0.050, 0.005, 0.010, 0.020, 0.15, 0.25, 0.30, 0.30, 0.86, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('03_STRICT_NEAR_BREAKOUT_LOW_CHASE', 'Most anti-chase probe: tight near-breakout band, reduced momentum weight, and risk/breakout-led sorting.',
                5000000000, 10000000000, 1.20, 1.60, 0.020, 0.050, 0.030, 0.040,
                0.020, 0.050, 0.005, 0.008, 0.015, 0.10, 0.35, 0.25, 0.30, 0.90, 0.74, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('04_LOW_ATR_MID_ROC_STABILITY', 'Keeps C01 downside intent but narrows ROC to the 0.02..0.05 positive diagnostic bucket.',
                2500000000, 7500000000, 1.20, 2.00, 0.012, 0.045, 0.025, 0.038,
                0.020, 0.050, 0.005, 0.010, 0.018, 0.15, 0.25, 0.20, 0.40, 0.88, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('05_VOLUME_NOT_SPIKE_RISK_FIRST', 'Targets the 1.2..1.5 volume bucket and lowers momentum influence because high momentum component underperformed.',
                2500000000, 5000000000, 1.20, 1.50, 0.015, 0.050, 0.025, 0.040,
                0.020, 0.050, 0.005, 0.012, 0.020, 0.10, 0.25, 0.30, 0.35, 0.86, 0.70, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('06_BROAD_SAMPLE_NEAR_BREAKOUT', 'Broader sample control that preserves near-breakout and moderate volume evidence without tightening liquidity too far.',
                1000000000, 5000000000, 1.20, 1.80, 0.012, 0.055, 0.025, 0.040,
                0.015, 0.055, 0.005, 0.010, 0.020, 0.15, 0.30, 0.25, 0.30, 0.84, 0.68, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('07_STABILITY_PROXY_SECTOR_REVIEW', 'Uses only existing runtime-consumed axes while preserving sector evidence as diagnostic review, not as an unsupported filter.',
                2500000000, 10000000000, 1.20, 1.50, 0.020, 0.050, 0.030, 0.040,
                0.020, 0.050, 0.005, 0.010, 0.018, 0.10, 0.30, 0.20, 0.40, 0.90, 0.76, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
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
        return WatchlistBacktestC01ParamGridCatalog::parameterAxes();
    }

    public static function axisRationale(): array
    {
        return WatchlistBacktestC01ParamGridCatalog::axisRationale();
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'c01_is_failure_drilldown_runtime_feature_buckets',
                'c01_nearest_gate_gap_review',
                'weekly_swing_parameter_registry',
                'deterministic_engineering_rationale',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_DETERMINISTIC',
            'catalog_mutation_after_first_execution' => false,
            'best_of_failed_selection' => false,
            'focus' => 'DOWNSIDE_STABILITY_C02_ANTI_CHASE_MODERATE_LIQUIDITY_VOLUME_NEAR_BREAKOUT',
            'sector_filter_used' => false,
            'sector_evidence_usage' => 'DIAGNOSTIC_REVIEW_ONLY_EXISTING_AXIS_PROXY',
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
