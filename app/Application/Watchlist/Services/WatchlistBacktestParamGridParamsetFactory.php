<?php

namespace App\Application\Watchlist\Services;

use RuntimeException;

class WatchlistBacktestParamGridParamsetFactory
{
    public const RISK_BAND_RESOLUTION_RULE = 'CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR';
    public const EXPLICIT_CATALOG_RISK_BAND_RULE = 'EXPLICIT_CATALOG_ENTRY_QUALITY_ATR_BAND';
    public const EXPLICIT_R2_RISK_BAND_RULE = self::EXPLICIT_CATALOG_RISK_BAND_RULE;

    public function make(array $row): array
    {
        $catalogCode = trim((string) ($row['catalog_code'] ?? WatchlistBacktestParamGridCatalog::CATALOG_CODE));
        if ($catalogCode === WatchlistBacktestParamGridCatalog::CATALOG_CODE) {
            return $this->makeLegacyR1($row);
        }

        return $this->makeCuratedCatalog($row, $catalogCode);
    }

    private function makeLegacyR1(array $row): array
    {
        $row = WatchlistBacktestParamGridCatalog::legacyRuntimeRow($row);
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

    private function makeCuratedCatalog(array $row, string $catalogCode): array
    {
        $definition = $this->catalogDefinition($catalogCode);
        $base = array_replace_recursive(
            WatchlistScoringService::defaultParamset(),
            WatchlistPlanGroupingService::defaultParamset(),
            WatchlistRecommendationService::defaultParamset(),
            WatchlistBacktestStrategyService::defaultParamset()
        );

        $paramId = $this->requiredInt($row, 'param_id');
        $catalogVersion = trim((string) ($row['catalog_version'] ?? ''));
        $rowCode = trim((string) ($row['row_code'] ?? ''));
        $catalogHash = trim((string) ($row['catalog_hash'] ?? ''));
        $rowHash = trim((string) ($row['row_hash'] ?? ''));
        $rationale = trim((string) ($row['rationale'] ?? ''));
        if ($catalogVersion !== $definition['version']
            || $catalogHash !== $definition['hash']
            || $rowCode === ''
            || $rowHash !== sha1($catalogCode.'|'.$rowCode)
            || $rationale === '') {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: approved catalog metadata must be explicit and exact.');
        }

        $minDv20Idr = $this->requiredFloat($row, 'min_dv20_idr');
        $dv20StrongIdr = $this->requiredFloat($row, 'dv20_strong_idr');
        $minVolRatio = $this->requiredFloat($row, 'min_vol_ratio');
        $strongVolRatio = $this->requiredFloat($row, 'strong_vol_ratio');
        $minAtr14Pct = $this->requiredFloat($row, 'min_atr14_pct');
        $maxAtr14Pct = $this->requiredFloat($row, 'max_atr14_pct');
        $idealLow = $this->requiredFloat($row, 'atr_ideal_low');
        $idealHigh = $this->requiredFloat($row, 'atr_ideal_high');
        $rocLo = $this->requiredFloat($row, 'roc_lo');
        $rocHi = $this->requiredFloat($row, 'roc_hi');
        $momRoc20SoftMin = $this->requiredFloat($row, 'mom_roc20_soft_min');
        $boNearBelowPct = $this->requiredFloat($row, 'bo_near_below_pct');
        $boMaxExtPct = $this->requiredFloat($row, 'bo_max_ext_pct');

        $weights = [
            'momentum' => $this->requiredFloat($row, 'w_momentum'),
            'volume' => $this->requiredFloat($row, 'w_volume'),
            'breakout' => $this->requiredFloat($row, 'w_breakout'),
            'risk' => $this->requiredFloat($row, 'w_risk'),
        ];
        $topMinScoreQ = $this->requiredFloat($row, 'top_min_score_q');
        $secondaryMinScoreQ = $this->requiredFloat($row, 'secondary_min_score_q');

        $gridSnapshot = $row;
        unset($gridSnapshot['param_id']);

        $this->assertInvariants(
            $minDv20Idr,
            $dv20StrongIdr,
            $minVolRatio,
            $strongVolRatio,
            $minAtr14Pct,
            $idealLow,
            $idealHigh,
            $maxAtr14Pct,
            $rocLo,
            $rocHi,
            $boNearBelowPct,
            $boMaxExtPct,
            $weights,
            $topMinScoreQ,
            $secondaryMinScoreQ
        );

        $executionAxes = WatchlistBacktestExitAxisSupport::resolve(
            $row,
            $definition['execution_axis_policy'] ?? WatchlistBacktestExitAxisSupport::fixedExecutionDefinition(
                (float) $definition['fixed_stop_atr_mult'],
                (float) $definition['fixed_min_rr'],
                (int) $definition['fixed_top_picks_target'],
                (int) $definition['fixed_secondary_target']
            )
        );
        $stopAtrMult = (float) $executionAxes['stop_atr_mult'];
        $minRr = (float) $executionAxes['min_rr'];
        $topPicksTarget = (int) $executionAxes['top_picks_target'];
        $secondaryTarget = (int) $executionAxes['secondary_target'];

        $btGridResolution = [
            'risk_band_rule' => self::EXPLICIT_CATALOG_RISK_BAND_RULE,
            'min_atr14_pct' => $minAtr14Pct,
            'max_atr14_pct' => $maxAtr14Pct,
            'atr_ideal_low' => $idealLow,
            'atr_ideal_high' => $idealHigh,
            'source' => 'explicit immutable catalog values',
            'explicit_catalog_values_preserved' => true,
        ];
        if (isset($definition['candidate_selection_extension'])) {
            $btGridResolution['candidate_selection_extension'] = $definition['candidate_selection_extension'];
        }
        if (! empty($executionAxes['bt_grid_resolution']) && is_array($executionAxes['bt_grid_resolution'])) {
            $btGridResolution = array_replace_recursive($btGridResolution, $executionAxes['bt_grid_resolution']);
        }

        return array_replace_recursive($base, [
            'policy_code' => (string) ($row['policy_code'] ?? 'WS'),
            'policy_version' => 'WS_EOD_RUNTIME',
            'paramset_code' => $catalogCode.'_'.$rowCode,
            'bt_catalog' => [
                'catalog_code' => $catalogCode,
                'catalog_version' => $catalogVersion,
                'catalog_hash' => $catalogHash,
                'row_code' => $rowCode,
                'row_hash' => $rowHash,
                'rationale' => $rationale,
            ],
            'bt_grid' => $gridSnapshot,
            'bt_grid_resolution' => $btGridResolution,
            'setup' => [
                'roc_lo' => $rocLo,
                'roc_hi' => $rocHi,
                'mom_roc20_soft_min' => $momRoc20SoftMin,
                'bo_near_below_pct' => $boNearBelowPct,
                'bo_max_ext_pct' => $boMaxExtPct,
            ],
            'liquidity' => [
                'min_dv20_idr' => $minDv20Idr,
                'dv20_strong_idr' => $dv20StrongIdr,
            ],
            'risk' => [
                'min_atr14_pct' => $minAtr14Pct,
                'max_atr14_pct' => $maxAtr14Pct,
                'atr_ideal_low' => $idealLow,
                'atr_ideal_high' => $idealHigh,
                'stop_atr_mult' => $stopAtrMult,
                'min_rr' => $minRr,
            ],
            'volume' => [
                'min_vol_ratio' => $minVolRatio,
                'strong_vol_ratio' => $strongVolRatio,
            ],
            'scoring' => [
                'weights' => $weights,
            ],
            'grouping' => [
                'top_min_score_q' => $topMinScoreQ,
                'secondary_min_score_q' => $secondaryMinScoreQ,
                'top_picks' => ['max_items' => $topPicksTarget],
                'secondary' => ['max_items' => $secondaryTarget],
            ],
        ]);
    }

    private function catalogDefinition(string $catalogCode): array
    {
        $definitions = [
            WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestR2ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestR2ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => WatchlistBacktestR2ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestR2ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestR2ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestR2ParamGridCatalog::FIXED_SECONDARY_TARGET,
            ],
            WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC01ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC01ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => WatchlistBacktestC01ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC01ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC01ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC01ParamGridCatalog::FIXED_SECONDARY_TARGET,
            ],
            WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC02ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC02ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => WatchlistBacktestC02ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC02ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC02ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC02ParamGridCatalog::FIXED_SECONDARY_TARGET,
            ],
            WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC03ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC03ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => WatchlistBacktestC03ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC03ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC03ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC03ParamGridCatalog::FIXED_SECONDARY_TARGET,
            ],
            WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC04ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC04ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => WatchlistBacktestC04ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC04ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC04ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC04ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'candidate_selection_extension' => WatchlistBacktestC04ParamGridCatalog::candidateSelectionExtension(),
            ],
            WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC05ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC05ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => WatchlistBacktestC05ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC05ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC05ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC05ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'candidate_selection_extension' => WatchlistBacktestC05ParamGridCatalog::candidateSelectionExtension(),
            ],
            WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC06ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC06ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => WatchlistBacktestC06ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC06ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC06ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC06ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'candidate_selection_extension' => WatchlistBacktestC06ParamGridCatalog::candidateSelectionExtension(),
            ],
            WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC07ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC07ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => WatchlistBacktestC07ParamGridCatalog::FIXED_STOP_ATR_MULT,
                'fixed_min_rr' => WatchlistBacktestC07ParamGridCatalog::FIXED_MIN_RR,
                'fixed_top_picks_target' => WatchlistBacktestC07ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC07ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'candidate_selection_extension' => WatchlistBacktestC07ParamGridCatalog::candidateSelectionExtension(),
            ],
            WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC14ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC14ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => 0.0,
                'fixed_min_rr' => 0.0,
                'fixed_top_picks_target' => WatchlistBacktestC14ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC14ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'candidate_selection_extension' => WatchlistBacktestC14ParamGridCatalog::candidateSelectionExtension(),
                'execution_axis_policy' => WatchlistBacktestC14ParamGridCatalog::exitAxisPolicy(),
            ],
            WatchlistBacktestC15ParamGridCatalog::CATALOG_CODE => [
                'version' => WatchlistBacktestC15ParamGridCatalog::CATALOG_VERSION,
                'hash' => WatchlistBacktestC15ParamGridCatalog::hash(),
                'fixed_stop_atr_mult' => 0.0,
                'fixed_min_rr' => 0.0,
                'fixed_top_picks_target' => WatchlistBacktestC15ParamGridCatalog::FIXED_TOP_PICKS_TARGET,
                'fixed_secondary_target' => WatchlistBacktestC15ParamGridCatalog::FIXED_SECONDARY_TARGET,
                'candidate_selection_extension' => WatchlistBacktestC15ParamGridCatalog::candidateSelectionExtension(),
                'execution_axis_policy' => WatchlistBacktestC15ParamGridCatalog::exitAxisPolicy(),
            ],
        ];

        if (! isset($definitions[$catalogCode])) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: catalog_code is not an approved immutable catalog.');
        }

        return $definitions[$catalogCode];
    }

    private function assertInvariants(
        float $minDv20Idr,
        float $dv20StrongIdr,
        float $minVolRatio,
        float $strongVolRatio,
        float $minAtr14Pct,
        float $idealLow,
        float $idealHigh,
        float $maxAtr14Pct,
        float $rocLo,
        float $rocHi,
        float $boNearBelowPct,
        float $boMaxExtPct,
        array $weights,
        float $topMinScoreQ,
        float $secondaryMinScoreQ
    ): void {
        if ($minDv20Idr < 0 || $dv20StrongIdr < $minDv20Idr) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: dv20_strong_idr must be >= min_dv20_idr and both liquidity thresholds must be non-negative.');
        }
        if ($minVolRatio < 0 || $strongVolRatio < $minVolRatio) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: strong_vol_ratio must be >= min_vol_ratio and both volume thresholds must be non-negative.');
        }
        if ($minAtr14Pct < 0 || $maxAtr14Pct > 1
            || ! ($minAtr14Pct <= $idealLow && $idealLow <= $idealHigh && $idealHigh <= $maxAtr14Pct)) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: ATR band invariant or fractional range is invalid.');
        }
        if ($rocLo >= $rocHi) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: roc_lo must be lower than roc_hi.');
        }
        if ($boNearBelowPct < 0 || $boNearBelowPct > 1 || $boMaxExtPct < 0 || $boMaxExtPct > 1) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: breakout fractional ranges are invalid.');
        }
        if (abs(array_sum($weights) - 1.0) > 0.000001
            || count(array_filter($weights, function (float $weight): bool {
                return $weight < 0 || $weight > 1;
            })) > 0) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: scoring weights must sum to 1.0 and each weight must be within 0..1.');
        }
        if ($secondaryMinScoreQ < 0 || $secondaryMinScoreQ > 1
            || $topMinScoreQ < 0 || $topMinScoreQ > 1
            || $secondaryMinScoreQ > $topMinScoreQ) {
            throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: grouping quantiles are invalid.');
        }
    }

    private function floatOrDefault(array $row, string $key, $default): float
    {
        if (! array_key_exists($key, $row)) {
            return (float) $default;
        }

        return $this->requiredFloat($row, $key);
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
