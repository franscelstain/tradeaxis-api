<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog
{
    public const CATALOG_CODE = 'WS_BT_GRID_FINAL_BOUNDED_REMEDIATION_C01_2026_07';
    public const CATALOG_VERSION = 'FINAL-C01';
    public const CATALOG_COUNT = 3;
    public const FIXED_TOP_PICKS_TARGET = 5;
    public const FIXED_SECONDARY_TARGET = 10;
    public const FIXED_MIN_RR = 1.5;

    public static function rows(): array
    {
        $rows = [
            self::row(
                'C171_FINAL_A_RISK_FORWARD_INTERPOLATED',
                'Final bounded score interpolation between the valid V3 control and risk-forward anchor; tests whether 35% risk weight preserves positive return while recovering downside.',
                0.300000, 0.100000, 0.250000, 0.350000,
                0.060000, 0.060000, 1.500000
            ),
            self::row(
                'C171_FINAL_B_RISK_FORWARD_ATR_055',
                'Final bounded downside candidate anchored to paramset 11; applies only a mild 5.5% ATR ceiling to reduce the high-ATR loss tail without a tick-risk or ticker blacklist.',
                0.300000, 0.100000, 0.200000, 0.400000,
                0.055000, 0.055000, 1.500000
            ),
            self::row(
                'C171_FINAL_C_RISK_FORWARD_STOP_125',
                'Final bounded execution-risk candidate anchored to paramset 11; tightens only stop ATR multiplier from 1.50 to 1.25 while preserving canonical gates and minimum RR.',
                0.300000, 0.100000, 0.200000, 0.400000,
                0.060000, 0.060000, 1.250000
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
            'source_eval_id' => 204,
            'source_param_set_id' => 11,
            'source_params_hash' => 'c93bae2b761028d6b236f368d5b19bb4f498715a',
            'source_pipeline_version' => 'WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3',
            'source_pipeline_hash' => '9e9933b363026623b7ab5629f3281fa680a53a2e',
            'comparison_eval_ids' => [199, 200, 201, 202, 203, 204],
            'decision' => 'ONE_FINAL_BOUNDED_REMEDIATION_ALLOWED',
            'closure_rule' => 'NO_PASS_CLOSE_C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION',
            'uses_decision_time_fields_only' => true,
            'uses_oos' => false,
            'ticker_blacklist_used' => false,
            'month_blacklist_used' => false,
            'return_field_used_for_selection' => false,
            'canonical_gate_lowered' => false,
            'further_remediation_after_this_catalog_allowed' => false,
            'production_ready' => false,
        ];
    }

    private static function row(
        string $rowCode,
        string $rationale,
        float $wMomentum,
        float $wVolume,
        float $wBreakout,
        float $wRisk,
        float $maxAtr14Pct,
        float $atrIdealHigh,
        float $stopAtrMult
    ): array {
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
            'min_vol_ratio' => 1.200000,
            'max_vol_ratio' => 5.000000,
            'strong_vol_ratio' => 2.500000,
            'min_atr14_pct' => 0.020000,
            'max_atr14_pct' => $maxAtr14Pct,
            'atr_ideal_low' => 0.035000,
            'atr_ideal_high' => $atrIdealHigh,
            'max_signal_tick_risk_expansion_pct' => null,
            'roc_lo' => 0.020000,
            'roc_hi' => 0.150000,
            'mom_roc20_soft_min' => 0.000000,
            'bo_near_below_pct' => 0.020000,
            'bo_max_ext_pct' => 0.050000,
            'w_momentum' => $wMomentum,
            'w_volume' => $wVolume,
            'w_breakout' => $wBreakout,
            'w_risk' => $wRisk,
            'stop_atr_mult' => $stopAtrMult,
            'min_rr' => self::FIXED_MIN_RR,
            'top_picks_target' => self::FIXED_TOP_PICKS_TARGET,
            'secondary_target' => self::FIXED_SECONDARY_TARGET,
            'top_min_score_q' => 0.800000,
            'secondary_min_score_q' => 0.650000,
            'top_max_score_total' => 0.999999,
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
