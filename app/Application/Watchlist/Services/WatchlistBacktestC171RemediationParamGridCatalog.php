<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC171RemediationParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07';
    public const CATALOG_VERSION = 'C171-R1';
    public const CATALOG_COUNT = 5;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;
    public const FIXED_STOP_ATR_MULT = 1.5;
    public const FIXED_MIN_RR = 1.5;

    public static function rows(): array
    {
        $rows = [
            self::row(
                'C171_DRAFT_A_BROAD_MODERATE_SCORE_CAP',
                'Primary quality candidate: broad replacement pool with explicit upper bounds for DV20, volume participation, ATR, and TOP score saturation.',
                1000000000, 50000000000, 1.2, 5.0, 0.020000, 0.075000, 0.980000
            ),
            self::row(
                'C171_DRAFT_B_BROAD_SAMPLE_RECOVERY',
                'Coverage recovery control: preserve the broad bounded universe without excluding score saturation, while keeping all canonical gates unchanged.',
                1000000000, 50000000000, 1.2, 5.0, 0.020000, 0.075000, 1.000000
            ),
            self::row(
                'C171_DRAFT_C_MID_LIQ_LOW_ATR_SCORE_CAP',
                'Mid-liquidity quality candidate: stronger DV20 floor, low ATR ceiling, moderate volume participation, and exact score-saturation exclusion.',
                2500000000, 50000000000, 1.5, 5.0, 0.020000, 0.060000, 0.999999
            ),
            self::row(
                'C171_DRAFT_D_LOW_ATR_BALANCED',
                'Low-ATR balanced candidate: broad liquidity floor with low ATR ceiling and exact score-saturation exclusion to balance quality and replacement coverage.',
                1000000000, 50000000000, 1.2, 5.0, 0.020000, 0.060000, 0.999999
            ),
            self::row(
                'C171_DRAFT_E_LOWER_VOLUME_BALANCED',
                'Volume-spike avoidance candidate: lower maximum volume participation with a moderate TOP score cap while preserving the broader ATR range.',
                1000000000, 50000000000, 1.2, 3.0, 0.020000, 0.075000, 0.980000
            ),
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

    public static function provenance(): array
    {
        return [
            'source_eval_id' => 188,
            'source_param_set_id' => 1,
            'source_params_hash' => 'b7f3c207b989c55c93f8f61b1fcceea2c343a151',
            'diagnostic_artifact_hash' => '768b4e47d4a9e497fda29ca6541be9a8f3a63c9d',
            'candidate_design_artifact_hash' => '2a1345857e2ecf62b2d64fcaa46ed06f6015e9a6',
            'uses_decision_time_fields_only' => true,
            'uses_oos' => false,
            'ticker_blacklist_used' => false,
            'sector_whitelist_used' => false,
            'month_blacklist_used' => false,
            'canonical_gate_lowered' => false,
            'catalog_mutation_after_release_allowed' => false,
            'production_ready' => false,
        ];
    }

    private static function row(
        string $rowCode,
        string $rationale,
        int $minDv20Idr,
        int $maxDv20Idr,
        float $minVolRatio,
        float $maxVolRatio,
        float $minAtr14Pct,
        float $maxAtr14Pct,
        float $topMaxScoreTotal
    ): array {
        $idealLow = 0.035000;
        $idealHigh = min(0.075000, $maxAtr14Pct);

        return [
            'policy_code' => 'WS',
            'catalog_code' => self::CATALOG_CODE,
            'catalog_version' => self::CATALOG_VERSION,
            'catalog_hash' => '',
            'row_code' => $rowCode,
            'row_hash' => '',
            'rationale' => $rationale,
            'min_dv20_idr' => $minDv20Idr,
            'max_dv20_idr' => $maxDv20Idr,
            'dv20_strong_idr' => 5000000000,
            'min_vol_ratio' => $minVolRatio,
            'max_vol_ratio' => $maxVolRatio,
            'strong_vol_ratio' => 2.500000,
            'min_atr14_pct' => $minAtr14Pct,
            'max_atr14_pct' => $maxAtr14Pct,
            'atr_ideal_low' => $idealLow,
            'atr_ideal_high' => $idealHigh,
            'roc_lo' => 0.020000,
            'roc_hi' => 0.150000,
            'mom_roc20_soft_min' => 0.000000,
            'bo_near_below_pct' => 0.020000,
            'bo_max_ext_pct' => 0.050000,
            'w_momentum' => 0.300000,
            'w_volume' => 0.200000,
            'w_breakout' => 0.300000,
            'w_risk' => 0.200000,
            'stop_atr_mult' => self::FIXED_STOP_ATR_MULT,
            'min_rr' => self::FIXED_MIN_RR,
            'top_picks_target' => self::FIXED_TOP_PICKS_TARGET,
            'secondary_target' => self::FIXED_SECONDARY_TARGET,
            'top_min_score_q' => 0.800000,
            'secondary_min_score_q' => 0.650000,
            'top_max_score_total' => $topMaxScoreTotal,
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
