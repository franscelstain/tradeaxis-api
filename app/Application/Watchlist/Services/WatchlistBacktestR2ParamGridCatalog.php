<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestR2ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_ENTRY_QUALITY_R2_2026_06';
    public const CATALOG_VERSION = 'R2';
    public const CATALOG_COUNT = 12;
    public const R1_CONTROL_ROW_CODE = '00_R1_BASELINE_CONTROL';
    public const FIXED_STOP_ATR_MULT = 1.50;
    public const FIXED_MIN_RR = 1.50;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;

    public static function rows(): array
    {
        $rows = [
            self::row(self::R1_CONTROL_ROW_CODE, 'Exact entry-quality control equivalent to R1 01_BASELINE; extended fields use canonical runtime defaults.',
                1000000000, 5000000000, 1.20, 2.50, 0.020, 0.120, 0.035, 0.075,
                0.020, 0.150, 0.000, 0.020, 0.050, 0.30, 0.30, 0.20, 0.20, 0.80, 0.65, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('01_BALANCED_ENTRY_FILTER', 'Moderate liquidity, participation, ATR, momentum, breakout and quantile tightening without exit-axis changes.',
                2500000000, 7500000000, 1.30, 2.20, 0.020, 0.090, 0.035, 0.065,
                0.025, 0.120, 0.005, 0.015, 0.040, 0.30, 0.35, 0.20, 0.15, 0.88, 0.72, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('02_LIQUID_MOMENTUM', 'Higher liquidity and positive momentum floor to reduce weak entries.',
                5000000000, 10000000000, 1.25, 2.00, 0.020, 0.085, 0.035, 0.060,
                0.030, 0.110, 0.010, 0.015, 0.035, 0.40, 0.30, 0.15, 0.15, 0.90, 0.75, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('03_VOLUME_BREAKOUT_CONFIRM', 'Stronger volume confirmation with breakout-heavy score and limited extension.',
                2500000000, 7500000000, 1.50, 2.30, 0.020, 0.090, 0.035, 0.065,
                0.020, 0.120, 0.005, 0.010, 0.030, 0.20, 0.45, 0.25, 0.10, 0.90, 0.75, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('04_LOW_VOLATILITY_QUALITY', 'Narrower ATR eligibility and ideal band to avoid unstable entries.',
                2500000000, 7500000000, 1.30, 2.20, 0.015, 0.070, 0.025, 0.050,
                0.020, 0.100, 0.005, 0.015, 0.035, 0.30, 0.30, 0.20, 0.20, 0.90, 0.75, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('05_RISK_WEIGHTED_ENTRY', 'Narrow ATR band plus higher risk score weight while execution risk remains fixed.',
                5000000000, 10000000000, 1.30, 2.20, 0.015, 0.075, 0.025, 0.050,
                0.020, 0.105, 0.005, 0.015, 0.035, 0.25, 0.30, 0.15, 0.30, 0.92, 0.78, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('06_NEAR_BREAKOUT_ONLY', 'Tighter near-breakout and extension tolerances with breakout emphasis.',
                2500000000, 7500000000, 1.35, 2.20, 0.020, 0.085, 0.035, 0.060,
                0.025, 0.110, 0.005, 0.008, 0.025, 0.25, 0.50, 0.15, 0.10, 0.93, 0.80, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('07_STRONG_PARTICIPATION', 'High minimum and strong volume ratios to require broad participation.',
                5000000000, 10000000000, 1.60, 2.60, 0.020, 0.090, 0.035, 0.060,
                0.020, 0.110, 0.005, 0.012, 0.030, 0.20, 0.35, 0.35, 0.10, 0.92, 0.78, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('08_MOMENTUM_NOT_EXTENDED', 'Positive ROC floor, lower ROC ceiling, and strict breakout extension cap.',
                2500000000, 7500000000, 1.35, 2.20, 0.020, 0.085, 0.035, 0.060,
                0.030, 0.090, 0.010, 0.010, 0.025, 0.40, 0.35, 0.15, 0.10, 0.93, 0.80, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('09_HIGH_LIQ_BALANCED_STRICT', 'High-liquidity balanced candidate with strict grouping cutoffs.',
                7500000000, 15000000000, 1.35, 2.20, 0.020, 0.080, 0.030, 0.055,
                0.025, 0.100, 0.005, 0.012, 0.030, 0.30, 0.35, 0.20, 0.15, 0.95, 0.82, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('10_DEFENSIVE_ENTRY', 'Defensive entry selection combining high liquidity, narrow ATR, and risk-weighted scoring.',
                7500000000, 15000000000, 1.40, 2.30, 0.015, 0.065, 0.025, 0.045,
                0.020, 0.090, 0.005, 0.010, 0.025, 0.25, 0.30, 0.15, 0.30, 0.95, 0.82, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
            self::row('11_CONCENTRATED_ENTRY_QUALITY', 'Most concentrated curated row; strict liquidity, participation, momentum, breakout and quantile filters.',
                10000000000, 20000000000, 1.50, 2.50, 0.015, 0.070, 0.025, 0.050,
                0.030, 0.095, 0.010, 0.008, 0.020, 0.35, 0.40, 0.15, 0.10, 0.97, 0.85, self::FIXED_TOP_PICKS_TARGET, self::FIXED_SECONDARY_TARGET),
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
        return [
            'liquidity.min_dv20_idr', 'liquidity.dv20_strong_idr',
            'volume.min_vol_ratio', 'volume.strong_vol_ratio',
            'risk.min_atr14_pct', 'risk.max_atr14_pct', 'risk.atr_ideal_low', 'risk.atr_ideal_high',
            'setup.roc_lo', 'setup.roc_hi', 'setup.mom_roc20_soft_min',
            'setup.bo_near_below_pct', 'setup.bo_max_ext_pct',
            'scoring.weights.value.momentum', 'scoring.weights.value.breakout',
            'scoring.weights.value.volume', 'scoring.weights.value.risk',
            'grouping.top_min_score_q', 'grouping.secondary_min_score_q',
        ];
    }

    public static function axisRationale(): array
    {
        return [
            'liquidity.min_dv20_idr' => 'Reject illiquid entries before ranking.',
            'liquidity.dv20_strong_idr' => 'Reward distinctly strong liquidity without changing the minimum gate.',
            'volume.min_vol_ratio' => 'Require basic participation confirmation.',
            'volume.strong_vol_ratio' => 'Separate merely tradable volume from strong participation.',
            'risk.min_atr14_pct' => 'Reject inactive names unlikely to complete a weekly swing.',
            'risk.max_atr14_pct' => 'Reject excessively unstable entry candidates.',
            'risk.atr_ideal_low' => 'Define the lower edge of the preferred entry-volatility band.',
            'risk.atr_ideal_high' => 'Define the upper edge of the preferred entry-volatility band.',
            'setup.roc_lo' => 'Require a minimum momentum floor.',
            'setup.roc_hi' => 'Avoid chasing overextended momentum.',
            'setup.mom_roc20_soft_min' => 'Shape momentum score quality without adding a new hard exit rule.',
            'setup.bo_near_below_pct' => 'Control how far below breakout remains a valid near-breakout entry.',
            'setup.bo_max_ext_pct' => 'Cap breakout extension to avoid late entries.',
            'scoring.weights.value.momentum' => 'Tune momentum contribution to entry ranking.',
            'scoring.weights.value.breakout' => 'Tune breakout contribution to entry ranking.',
            'scoring.weights.value.volume' => 'Tune participation contribution to entry ranking.',
            'scoring.weights.value.risk' => 'Tune volatility-quality contribution to entry ranking.',
            'grouping.top_min_score_q' => 'Tighten or loosen the adaptive TOP_PICKS cutoff.',
            'grouping.secondary_min_score_q' => 'Tighten or loosen the adaptive SECONDARY cutoff.',
        ];
    }

    public static function provenance(): array
    {
        return [
            'sources' => [
                'weekly_swing_parameter_registry',
                'r1_in_sample_failure_evidence',
                'deterministic_engineering_rationale',
            ],
            'oos_used' => false,
            'search_mode' => 'CURATED_LIMITED_COMBINATIONS',
            'catalog_mutation_after_first_execution' => false,
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
