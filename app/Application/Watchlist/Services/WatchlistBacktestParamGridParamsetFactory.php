<?php

namespace App\Application\Watchlist\Services;

use RuntimeException;

class WatchlistBacktestParamGridParamsetFactory
{
    public const RISK_BAND_RESOLUTION_RULE = 'CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR';

    public function make(array $row): array
    {
        $base = array_replace_recursive(
            WatchlistScoringService::defaultParamset(),
            WatchlistPlanGroupingService::defaultParamset(),
            WatchlistRecommendationService::defaultParamset(),
            WatchlistBacktestStrategyService::defaultParamset()
        );

        $paramId = $this->requiredInt($row, 'param_id');
        $maxAtr14Pct = $this->requiredFloat($row, 'max_atr14_pct');
        $minAtr14Pct = (float) ($base['risk']['min_atr14_pct'] ?? 0.02);
        if ($maxAtr14Pct < $minAtr14Pct) {
            throw new RuntimeException(
                'WS_BT_PARAM_GRID_INVALID: max_atr14_pct must be >= the canonical min_atr14_pct.'
            );
        }

        $defaultIdealLow = (float) ($base['risk']['atr_ideal_low'] ?? $minAtr14Pct);
        $defaultIdealHigh = (float) ($base['risk']['atr_ideal_high'] ?? $maxAtr14Pct);
        $idealHigh = max($minAtr14Pct, min($defaultIdealHigh, $maxAtr14Pct));
        $idealLow = max($minAtr14Pct, min($defaultIdealLow, $idealHigh));

        $minDv20Idr = $this->requiredFloat($row, 'min_dv20_idr');
        $dv20StrongIdr = (float) ($base['liquidity']['dv20_strong_idr'] ?? $minDv20Idr);
        if ($dv20StrongIdr < $minDv20Idr) {
            throw new RuntimeException(
                'WS_BT_PARAM_GRID_INVALID: min_dv20_idr exceeds the canonical dv20_strong_idr companion threshold.'
            );
        }

        $minVolRatio = $this->requiredFloat($row, 'min_vol_ratio');
        $strongVolRatio = (float) ($base['volume']['strong_vol_ratio'] ?? $minVolRatio);
        if ($strongVolRatio < $minVolRatio) {
            throw new RuntimeException(
                'WS_BT_PARAM_GRID_INVALID: min_vol_ratio exceeds the canonical strong_vol_ratio companion threshold.'
            );
        }

        return array_replace_recursive($base, [
            'policy_code' => (string) ($row['policy_code'] ?? 'WS'),
            'paramset_code' => 'WS_BT_PARAM_'.$paramId,
            'bt_grid' => $row,
            'bt_grid_resolution' => [
                'risk_band_rule' => self::RISK_BAND_RESOLUTION_RULE,
                'min_atr14_pct' => $minAtr14Pct,
                'max_atr14_pct' => $maxAtr14Pct,
                'atr_ideal_low' => $idealLow,
                'atr_ideal_high' => $idealHigh,
                'source' => 'canonical defaults plus explicit watchlist_bt_param_grid max_atr14_pct',
            ],
            'liquidity' => [
                'min_dv20_idr' => $minDv20Idr,
            ],
            'risk' => [
                'min_atr14_pct' => $minAtr14Pct,
                'max_atr14_pct' => $maxAtr14Pct,
                'atr_ideal_low' => $idealLow,
                'atr_ideal_high' => $idealHigh,
                'stop_atr_mult' => $this->requiredFloat($row, 'stop_atr_mult'),
                'min_rr' => $this->requiredFloat($row, 'min_rr'),
            ],
            'volume' => [
                'min_vol_ratio' => $minVolRatio,
            ],
            'scoring' => [
                'weights' => [
                    'momentum' => $this->requiredFloat($row, 'w_momentum'),
                    'volume' => $this->requiredFloat($row, 'w_volume'),
                    'breakout' => $this->requiredFloat($row, 'w_breakout'),
                    'risk' => $this->requiredFloat($row, 'w_risk'),
                ],
            ],
            'grouping' => [
                'top_min_score_q' => $this->requiredFloat($row, 'top_min_score_q'),
                'secondary_min_score_q' => $this->requiredFloat($row, 'secondary_min_score_q'),
                'top_picks' => ['max_items' => $this->requiredInt($row, 'top_picks_target')],
                'secondary' => ['max_items' => $this->requiredInt($row, 'secondary_target')],
            ],
        ]);
    }

    private function requiredFloat(array $row, string $key): float
    {
        if (! array_key_exists($key, $row) || ! is_numeric($row[$key])) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: '.$key.' must be numeric.');
        }

        return (float) $row[$key];
    }

    private function requiredInt(array $row, string $key): int
    {
        if (! array_key_exists($key, $row) || ! is_numeric($row[$key])) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: '.$key.' must be numeric.');
        }

        return (int) $row[$key];
    }
}
