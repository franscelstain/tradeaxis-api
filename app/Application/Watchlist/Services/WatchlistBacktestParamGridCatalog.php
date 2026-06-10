<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_BOOTSTRAP_2026_06';
    public const CATALOG_COUNT = 24;

    public static function rows(): array
    {
        return [
            self::row('01_BASELINE', 1000000000, 0.12, 1.20, 0.30, 0.20, 0.30, 0.20, 1.50, 1.50, 5, 10, 0.80, 0.65),
            self::row('02_BALANCED_QUALITY', 2500000000, 0.10, 1.50, 0.30, 0.20, 0.35, 0.15, 1.25, 1.50, 3, 5, 0.85, 0.70),
            self::row('03_BREAKOUT_QUALITY', 2500000000, 0.08, 1.50, 0.25, 0.15, 0.45, 0.15, 1.00, 2.00, 3, 5, 0.90, 0.75),
            self::row('04_VOLUME_BREAKOUT', 2500000000, 0.08, 2.00, 0.20, 0.30, 0.35, 0.15, 1.00, 1.50, 3, 5, 0.90, 0.75),
            self::row('05_HIGH_LIQ_BREAKOUT', 5000000000, 0.08, 1.50, 0.25, 0.20, 0.40, 0.15, 1.25, 2.00, 3, 5, 0.90, 0.75),
            self::row('06_LOW_ATR_BALANCED', 2500000000, 0.05, 1.50, 0.30, 0.20, 0.35, 0.15, 1.00, 1.50, 3, 5, 0.90, 0.75),
            self::row('07_LOW_ATR_HIGH_VOLUME', 5000000000, 0.04, 2.00, 0.20, 0.30, 0.35, 0.15, 1.00, 2.00, 2, 3, 0.95, 0.80),
            self::row('08_STRICT_QUALITY', 5000000000, 0.05, 2.00, 0.25, 0.25, 0.35, 0.15, 1.00, 1.50, 2, 3, 0.95, 0.80),
            self::row('09_MOMENTUM_BREAKOUT', 2500000000, 0.06, 1.50, 0.35, 0.15, 0.35, 0.15, 1.25, 2.00, 3, 5, 0.90, 0.75),
            self::row('10_RISK_TILT', 2500000000, 0.06, 1.50, 0.25, 0.15, 0.30, 0.30, 1.00, 1.50, 3, 5, 0.90, 0.75),
            self::row('11_VOLUME_TILT', 1000000000, 0.06, 2.00, 0.20, 0.35, 0.30, 0.15, 1.00, 2.00, 3, 5, 0.90, 0.75),
            self::row('12_BROAD_LOW_ATR', 1000000000, 0.05, 1.50, 0.30, 0.20, 0.30, 0.20, 1.00, 1.50, 3, 5, 0.85, 0.70),
            self::row('13_HIGH_LIQ_MODERATE', 5000000000, 0.07, 1.20, 0.30, 0.15, 0.35, 0.20, 1.25, 1.50, 3, 5, 0.85, 0.70),
            self::row('14_VERY_LOW_ATR', 2500000000, 0.04, 1.50, 0.30, 0.20, 0.35, 0.15, 1.00, 2.00, 2, 3, 0.95, 0.80),
            self::row('15_ULTRA_STRICT_VOLUME', 5000000000, 0.035, 2.00, 0.20, 0.30, 0.35, 0.15, 1.00, 2.00, 1, 2, 0.95, 0.85),
            self::row('16_ULTRA_STRICT_BREAKOUT', 5000000000, 0.04, 1.50, 0.30, 0.15, 0.40, 0.15, 1.00, 2.50, 1, 2, 0.95, 0.85),
            self::row('17_BREAKOUT_MAX', 2500000000, 0.07, 1.50, 0.20, 0.15, 0.50, 0.15, 1.25, 2.00, 2, 3, 0.95, 0.80),
            self::row('18_RISK_QUALITY_MAX', 5000000000, 0.05, 1.50, 0.25, 0.15, 0.30, 0.30, 1.00, 1.50, 1, 2, 0.95, 0.85),
            self::row('19_DOWNSIDE_CAP_BALANCED', 2500000000, 0.04, 1.50, 0.30, 0.20, 0.35, 0.15, 0.75, 1.50, 2, 3, 0.95, 0.80),
            self::row('20_DOWNSIDE_CAP_BREAKOUT', 5000000000, 0.04, 1.50, 0.25, 0.15, 0.45, 0.15, 0.75, 2.00, 1, 2, 0.95, 0.85),
            self::row('21_DOWNSIDE_CAP_VOLUME', 5000000000, 0.04, 2.00, 0.20, 0.30, 0.35, 0.15, 0.75, 1.50, 1, 2, 0.95, 0.85),
            self::row('22_ULTRA_LOW_ATR_BALANCED', 2500000000, 0.03, 1.50, 0.30, 0.20, 0.35, 0.15, 1.00, 1.50, 2, 3, 0.95, 0.80),
            self::row('23_ULTRA_LOW_ATR_BREAKOUT', 5000000000, 0.03, 1.50, 0.25, 0.15, 0.45, 0.15, 1.00, 2.00, 1, 2, 0.95, 0.85),
            self::row('24_ULTRA_LOW_ATR_RISK', 5000000000, 0.03, 1.50, 0.25, 0.15, 0.30, 0.30, 0.75, 2.00, 1, 2, 0.95, 0.85),
        ];
    }

    public static function hash(): string
    {
        return sha1(json_encode(self::rows(), JSON_UNESCAPED_SLASHES));
    }

    private static function row(
        string $code,
        int $minDv20Idr,
        float $maxAtr14Pct,
        float $minVolRatio,
        float $wMomentum,
        float $wVolume,
        float $wBreakout,
        float $wRisk,
        float $stopAtrMult,
        float $minRr,
        int $topPicksTarget,
        int $secondaryTarget,
        float $topMinScoreQ,
        float $secondaryMinScoreQ
    ): array {
        return [
            'policy_code' => 'WS',
            'min_dv20_idr' => $minDv20Idr,
            'max_atr14_pct' => $maxAtr14Pct,
            'min_vol_ratio' => $minVolRatio,
            'w_momentum' => $wMomentum,
            'w_volume' => $wVolume,
            'w_breakout' => $wBreakout,
            'w_risk' => $wRisk,
            'stop_atr_mult' => $stopAtrMult,
            'min_rr' => $minRr,
            'top_picks_target' => $topPicksTarget,
            'secondary_target' => $secondaryTarget,
            'top_min_score_q' => $topMinScoreQ,
            'secondary_min_score_q' => $secondaryMinScoreQ,
            'notes' => self::CATALOG_CODE.'_'.$code,
        ];
    }
}
