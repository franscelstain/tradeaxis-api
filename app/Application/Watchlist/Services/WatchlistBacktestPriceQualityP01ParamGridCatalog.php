<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestPriceQualityP01ParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_PRICE_QUALITY_P01_2026_07';
    public const CATALOG_VERSION = 'P01';
    public const CATALOG_COUNT = 2;
    public const C1_ROW_CODE = 'P01_C1_MIN_SIGNAL_PRICE_50';
    public const C2_ROW_CODE = 'P01_C2_MIN_SIGNAL_PRICE_100';
    public const PRIMARY_HYPOTHESIS_CODE = 'P01_H1_LOW_PRICE_MICROSTRUCTURE_RISK';
    public const RULE_CODE = 'SIGNAL_ROC20_10_TO_15_IHSG_NON_WEAK_MIN_PRICE';

    public static function rows(): array
    {
        $rows = [
            self::row(
                self::C1_ROW_CODE,
                'One-idea P01 C1: retain the locked non-weak-IHSG core and sequential exit; require exact signal-date close at least 50.'
            ),
            self::row(
                self::C2_ROW_CODE,
                'One-idea P01 C2: retain the locked non-weak-IHSG core and sequential exit; require exact signal-date close at least 100.'
            ),
        ];
        $hashPayload = $rows;
        foreach ($hashPayload as &$hashRow) {
            unset($hashRow['catalog_hash'], $hashRow['row_hash']);
        }
        unset($hashRow);
        $catalogHash = self::hashPayload($hashPayload);
        foreach ($rows as &$row) {
            $row['catalog_hash'] = $catalogHash;
            $row['row_hash'] = sha1(self::CATALOG_CODE.'|'.$row['row_code']);
        }
        unset($row);

        return $rows;
    }

    public static function hash(): string
    {
        return (string) self::rows()[0]['catalog_hash'];
    }

    public static function isKnownRow(string $rowCode): bool
    {
        return in_array($rowCode, [self::C1_ROW_CODE, self::C2_ROW_CODE], true);
    }

    public static function minimumSignalClosePriceForRow(string $rowCode): int
    {
        if ($rowCode === self::C1_ROW_CODE) {
            return 50;
        }
        if ($rowCode === self::C2_ROW_CODE) {
            return 100;
        }

        throw new \RuntimeException('WS_PRICE_QUALITY_P01_UNKNOWN_CATALOG_ROW: '.$rowCode);
    }

    public static function researchSelectionForRow(string $rowCode): array
    {
        return [
            'schema_version' => 'WS_NEW_STRATEGY_RESEARCH_SELECTION_V1',
            'hypothesis_code' => $rowCode,
            'rule_code' => self::RULE_CODE,
            'signal_date_only' => true,
            'oos_used' => false,
            'thresholds' => [
                'min_roc20' => 0.10,
                'max_roc20' => 0.15,
                'benchmark_code' => 'IHSG',
                'allowed_regimes' => ['STRONG', 'MIXED'],
                'min_signal_close_price' => self::minimumSignalClosePriceForRow($rowCode),
            ],
        ];
    }

    public static function researchExecution(): array
    {
        return WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution();
    }

    private static function row(string $rowCode, string $rationale): array
    {
        return [
            'policy_code' => 'WS',
            'catalog_code' => self::CATALOG_CODE,
            'catalog_version' => self::CATALOG_VERSION,
            'catalog_hash' => '',
            'row_code' => $rowCode,
            'row_hash' => '',
            'rationale' => $rationale,
            'min_dv20_idr' => 1000000000,
            'max_dv20_idr' => 50000000000,
            'dv20_strong_idr' => 5000000000,
            'min_vol_ratio' => 1.2,
            'max_vol_ratio' => 5.0,
            'strong_vol_ratio' => 2.5,
            'min_atr14_pct' => 0.02,
            'max_atr14_pct' => 0.06,
            'atr_ideal_low' => 0.035,
            'atr_ideal_high' => 0.06,
            'max_signal_tick_risk_expansion_pct' => null,
            'roc_lo' => 0.10,
            'roc_hi' => 0.15,
            'mom_roc20_soft_min' => 0.0,
            'bo_near_below_pct' => 0.02,
            'bo_max_ext_pct' => 0.05,
            'w_momentum' => 0.30,
            'w_volume' => 0.10,
            'w_breakout' => 0.20,
            'w_risk' => 0.40,
            'stop_atr_mult' => 1.5,
            'min_rr' => 1.5,
            'top_picks_target' => 5,
            'secondary_target' => 10,
            'top_min_score_q' => 0.80,
            'top_max_score_total' => 0.999999,
            'secondary_min_score_q' => 0.65,
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
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map([self::class, 'normalize'], $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = self::normalize($item);
        }

        return $value;
    }
}
