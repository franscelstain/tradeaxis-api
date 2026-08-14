<?php

namespace App\Application\Watchlist\Services;

class WeeklySwingParamsetValidator
{
    private const ROOT_KEYS = [
        'policy_code', 'policy_version', 'schema_version', 'paramset_code',
        'data_contract', 'data_readiness', 'liquidity', 'risk', 'setup',
        'scoring', 'grouping', 'plan_levels', 'no_trade', 'confirm_overlay',
        'eval', 'hash_contract', 'volume',
    ];

    private const OPTIONAL_ROOT_KEYS = [
        'research_selection',
        'research_execution',
    ];

    private const SECTION_KEYS = [
        'data_contract' => ['required_sources', 'required_fields', 'disabled_fields'],
        'data_readiness' => [
            'min_coverage_ratio', 'min_history_days', 'max_missing_bar_days_60d',
            'reject_if_eod_incomplete', 'outlier_ruleset',
        ],
        'liquidity' => ['min_dv20_idr', 'dv20_strong_idr', 'exclude_tickers'],
        'volume' => ['min_vol_ratio'],
        'risk' => [
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'stop_mode', 'stop_atr_mult', 'min_rr',
        ],
        'setup' => [
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_trigger_mode',
            'bo_near_below_pct', 'bo_max_ext_pct',
        ],
        'scoring' => ['combine_mode', 'weights'],
        'grouping' => [
            'secondary_target', 'top_picks_target', 'secondary_min_score_q',
            'top_min_score_q', 'grouping_mode', 'sort_keys', 'rounding_mode',
            'min_count_overrides', 'display_caps',
        ],
        'plan_levels' => ['entry_mode', 'entry_band_pct'],
        'no_trade' => ['min_eligible_count', 'no_trade_hides_all'],
        'confirm_overlay' => ['snapshot_max_age_sec', 'max_drift_from_entry_pct'],
        'eval' => [
            'min_trades_oos', 'min_trades', 'min_days_covered',
            'min_p25_ret_net_top', 'min_month_win_rate_min',
            'min_month_avg_ret_net_min',
        ],
        'hash_contract' => ['version', 'order_by', 'scales', 'null_handling'],
    ];

    private const OPTIONAL_SECTION_KEYS = [
        'liquidity' => ['max_dv20_idr'],
        'volume' => ['max_vol_ratio'],
        'grouping' => ['top_max_score_total'],
        'risk' => ['max_signal_tick_risk_expansion_pct'],
    ];

    private const AUDIT_KEYS = [
        'value', 'origin', 'status', 'bt_target', 'rationale', 'change_triggers',
    ];

    private const ARRAY_VALUES = [
        'data_contract.required_sources',
        'data_contract.required_fields',
        'data_contract.disabled_fields',
        'liquidity.exclude_tickers',
        'grouping.sort_keys',
    ];

    private const OBJECT_VALUES = [
        'data_readiness.outlier_ruleset',
        'scoring.weights',
        'grouping.min_count_overrides',
        'grouping.display_caps',
        'hash_contract.scales',
    ];

    private const BOOL_VALUES = [
        'data_readiness.reject_if_eod_incomplete',
        'no_trade.no_trade_hides_all',
    ];

    private const STRING_VALUES = [
        'risk.stop_mode',
        'setup.bo_trigger_mode',
        'scoring.combine_mode',
        'grouping.grouping_mode',
        'grouping.rounding_mode',
        'plan_levels.entry_mode',
        'hash_contract.order_by',
        'hash_contract.null_handling',
    ];

    private const ORIGINS = ['DET', 'MAN', 'BT', 'DET+MAN'];
    private const STATUSES = ['ACTIVE', 'TEMP', 'DEPRECATED'];

    private const SORT_KEYS = [
        'score_total_desc',
        'score_breakout_desc',
        'score_momentum_desc',
        'dv20_idr_desc',
        'atr14_pct_asc',
        'ticker_id_asc',
    ];

    private const HASH_SCALES = [
        'close_price_dp' => 4,
        'hh20_dp' => 4,
        'roc20_dp' => 6,
        'atr14_pct_dp' => 4,
        'dv20_idr_dp' => 0,
    ];

    public function validate(array $payload): array
    {
        $errors = [];
        $this->validateRoot($payload, $errors);
        $this->validateSections($payload, $errors);
        $this->validateFixedContracts($payload, $errors);
        $this->validateCrossFieldContracts($payload, $errors);
        $this->validateResearchSelection($payload, $errors);
        $this->validateResearchExecution($payload, $errors);

        $canonical = $this->normalizeForHash($payload);

        return [
            'valid' => $errors === [],
            'errors' => array_values($errors),
            'canonical_hash' => sha1(json_encode($canonical, JSON_UNESCAPED_SLASHES)),
            'canonical_payload' => $canonical,
        ];
    }

    private function validateRoot(array $payload, array &$errors): void
    {
        $actual = array_keys($payload);
        foreach (self::ROOT_KEYS as $key) {
            if (! array_key_exists($key, $payload)) {
                $this->error($errors, 'WS_PARAMSET_REQUIRED_KEY_MISSING', $key);
            }
        }
        foreach (array_diff($actual, array_merge(self::ROOT_KEYS, self::OPTIONAL_ROOT_KEYS)) as $key) {
            $this->error($errors, 'WS_PARAMSET_UNKNOWN_KEY', (string) $key);
        }

        if (($payload['policy_code'] ?? null) !== 'WS') {
            $this->error($errors, 'WS_PARAMSET_FIXED_VALUE_INVALID', 'policy_code');
        }
        if (($payload['schema_version'] ?? null) !== 'PARAMSET_JSON') {
            $this->error($errors, 'WS_PARAMSET_FIXED_VALUE_INVALID', 'schema_version');
        }
        foreach (['policy_version', 'paramset_code'] as $key) {
            if (! is_string($payload[$key] ?? null) || trim((string) $payload[$key]) === '') {
                $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $key);
            }
        }
    }

    private function validateSections(array $payload, array &$errors): void
    {
        foreach (self::SECTION_KEYS as $section => $requiredKeys) {
            $value = $payload[$section] ?? null;
            if (! is_array($value)) {
                $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $section);
                continue;
            }

            $optionalKeys = self::OPTIONAL_SECTION_KEYS[$section] ?? [];
            $allowedKeys = array_values(array_unique(array_merge($requiredKeys, $optionalKeys)));
            foreach ($requiredKeys as $key) {
                if (! array_key_exists($key, $value)) {
                    $this->error($errors, 'WS_PARAMSET_REQUIRED_KEY_MISSING', $section.'.'.$key);
                }
            }
            foreach (array_diff(array_keys($value), $allowedKeys) as $key) {
                $this->error($errors, 'WS_PARAMSET_UNKNOWN_KEY', $section.'.'.$key);
            }

            foreach ($allowedKeys as $key) {
                if (! array_key_exists($key, $value)) {
                    continue;
                }
                $this->validateAuditObject($section.'.'.$key, $value[$key], $errors);
            }
        }
    }

    private function validateAuditObject(string $path, $audit, array &$errors): void
    {
        if (! is_array($audit)) {
            $this->error($errors, 'WS_PARAMSET_AUDIT_OBJECT_INVALID', $path);
            return;
        }

        foreach (self::AUDIT_KEYS as $key) {
            if (! array_key_exists($key, $audit)) {
                $this->error($errors, 'WS_PARAMSET_AUDIT_FIELD_MISSING', $path.'.'.$key);
            }
        }
        foreach (array_diff(array_keys($audit), self::AUDIT_KEYS) as $key) {
            $this->error($errors, 'WS_PARAMSET_UNKNOWN_KEY', $path.'.'.$key);
        }

        if (! in_array($audit['origin'] ?? null, self::ORIGINS, true)) {
            $this->error($errors, 'WS_PARAMSET_ORIGIN_INVALID', $path.'.origin');
        }
        if (! in_array($audit['status'] ?? null, self::STATUSES, true)) {
            $this->error($errors, 'WS_PARAMSET_STATUS_INVALID', $path.'.status');
        }
        if (! is_bool($audit['bt_target'] ?? null)) {
            $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $path.'.bt_target');
        }
        if (! is_string($audit['rationale'] ?? null)) {
            $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $path.'.rationale');
        }
        if (! is_array($audit['change_triggers'] ?? null)) {
            $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $path.'.change_triggers');
        }

        if (! array_key_exists('value', $audit)) {
            return;
        }
        $value = $audit['value'];
        if (in_array($path, self::ARRAY_VALUES, true) || in_array($path, self::OBJECT_VALUES, true)) {
            if (! is_array($value)) {
                $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $path.'.value');
            }
            return;
        }
        if (in_array($path, self::BOOL_VALUES, true)) {
            if (! is_bool($value)) {
                $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $path.'.value');
            }
            return;
        }
        if (in_array($path, self::STRING_VALUES, true)) {
            if (! is_string($value)) {
                $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $path.'.value');
            }
            return;
        }
        if (! is_int($value) && ! is_float($value)) {
            $this->error($errors, 'WS_PARAMSET_TYPE_INVALID', $path.'.value');
        }
    }

    private function validateFixedContracts(array $payload, array &$errors): void
    {
        if ($this->value($payload, 'hash_contract.order_by') !== 'ticker_id_asc') {
            $this->error($errors, 'WS_PARAMSET_HASH_CONTRACT_INVALID', 'hash_contract.order_by.value');
        }
        if ($this->value($payload, 'hash_contract.null_handling') !== 'EXCLUDE_FROM_HASH_PAYLOAD') {
            $this->error($errors, 'WS_PARAMSET_HASH_CONTRACT_INVALID', 'hash_contract.null_handling.value');
        }
        if ($this->value($payload, 'hash_contract.scales') != self::HASH_SCALES) {
            $this->error($errors, 'WS_PARAMSET_HASH_CONTRACT_INVALID', 'hash_contract.scales.value');
        }
        if ($this->value($payload, 'grouping.sort_keys') !== self::SORT_KEYS) {
            $this->error($errors, 'WS_PARAMSET_SORT_KEYS_INVALID', 'grouping.sort_keys.value');
        }

        $fixed = [
            'risk.stop_mode' => 'ATR',
            'scoring.combine_mode' => 'NORM_WEIGHTED_SUM_CLAMP01',
            'grouping.grouping_mode' => 'QUALIFIED_POOLS_QUANTILE_CUTOFF',
            'grouping.rounding_mode' => 'FLOOR',
            'plan_levels.entry_mode' => 'BREAKOUT',
        ];
        foreach ($fixed as $path => $expected) {
            if ($this->value($payload, $path) !== $expected) {
                $this->error($errors, 'WS_PARAMSET_FIXED_VALUE_INVALID', $path.'.value');
            }
        }
        if (! in_array($this->value($payload, 'setup.bo_trigger_mode'), ['CLOSE_GT_HH20', 'HH20', 'NEAR_HH20', 'OFF'], true)) {
            $this->error($errors, 'WS_PARAMSET_ENUM_INVALID', 'setup.bo_trigger_mode.value');
        }
    }

    private function validateCrossFieldContracts(array $payload, array &$errors): void
    {
        $minDv = $this->numericValue($payload, 'liquidity.min_dv20_idr');
        $strongDv = $this->numericValue($payload, 'liquidity.dv20_strong_idr');
        if ($minDv !== null && $strongDv !== null && ($minDv < 0 || $strongDv < $minDv)) {
            $this->error($errors, 'WS_PARAMSET_RANGE_INVALID', 'liquidity.dv20_strong_idr.value');
        }
        $maxDv = $this->numericValue($payload, 'liquidity.max_dv20_idr');
        if ($maxDv !== null && ($minDv === null || $maxDv < $minDv || ($strongDv !== null && $maxDv < $strongDv))) {
            $this->error($errors, 'WS_PARAMSET_RANGE_INVALID', 'liquidity.max_dv20_idr.value');
        }

        $minVol = $this->numericValue($payload, 'volume.min_vol_ratio');
        $maxVol = $this->numericValue($payload, 'volume.max_vol_ratio');
        if ($maxVol !== null && ($minVol === null || $maxVol < $minVol)) {
            $this->error($errors, 'WS_PARAMSET_RANGE_INVALID', 'volume.max_vol_ratio.value');
        }

        $atr = [
            $this->numericValue($payload, 'risk.min_atr14_pct'),
            $this->numericValue($payload, 'risk.atr_ideal_low'),
            $this->numericValue($payload, 'risk.atr_ideal_high'),
            $this->numericValue($payload, 'risk.max_atr14_pct'),
        ];
        if (! in_array(null, $atr, true) && ! ($atr[0] <= $atr[1] && $atr[1] <= $atr[2] && $atr[2] <= $atr[3])) {
            $this->error($errors, 'WS_PARAMSET_RANGE_INVALID', 'risk.atr_band');
        }

        $rocLo = $this->numericValue($payload, 'setup.roc_lo');
        $rocHi = $this->numericValue($payload, 'setup.roc_hi');
        if ($rocLo !== null && $rocHi !== null && $rocLo >= $rocHi) {
            $this->error($errors, 'WS_PARAMSET_RANGE_INVALID', 'setup.roc_lo/roc_hi');
        }

        $secondaryQ = $this->numericValue($payload, 'grouping.secondary_min_score_q');
        $topQ = $this->numericValue($payload, 'grouping.top_min_score_q');
        if ($secondaryQ !== null && $topQ !== null && ! (0 <= $secondaryQ && $secondaryQ <= $topQ && $topQ <= 1)) {
            $this->error($errors, 'WS_PARAMSET_RANGE_INVALID', 'grouping.score_quantiles');
        }
        $topMaxScore = $this->numericValue($payload, 'grouping.top_max_score_total');
        if ($topMaxScore !== null && ($topMaxScore < 0 || $topMaxScore > 1)) {
            $this->error($errors, 'WS_PARAMSET_RANGE_INVALID', 'grouping.top_max_score_total.value');
        }

        $maxSignalTickExpansion = $this->numericValue($payload, 'risk.max_signal_tick_risk_expansion_pct');
        if ($maxSignalTickExpansion !== null && ($maxSignalTickExpansion < 0 || $maxSignalTickExpansion > 1)) {
            $this->error($errors, 'WS_PARAMSET_RANGE_INVALID', 'risk.max_signal_tick_risk_expansion_pct.value');
        }

        $weights = $this->value($payload, 'scoring.weights');
        $expectedWeightKeys = ['momentum', 'breakout', 'volume', 'risk'];
        $actualWeightKeys = is_array($weights) ? array_keys($weights) : [];
        sort($actualWeightKeys, SORT_STRING);
        $sortedExpectedWeightKeys = $expectedWeightKeys;
        sort($sortedExpectedWeightKeys, SORT_STRING);
        if (! is_array($weights) || $actualWeightKeys !== $sortedExpectedWeightKeys) {
            $this->error($errors, 'WS_PARAMSET_WEIGHTS_INVALID', 'scoring.weights.value');
        } else {
            $sum = 0.0;
            foreach ($weights as $key => $weight) {
                if ((! is_int($weight) && ! is_float($weight)) || $weight < 0) {
                    $this->error($errors, 'WS_PARAMSET_WEIGHTS_INVALID', 'scoring.weights.value.'.$key);
                    continue;
                }
                $sum += (float) $weight;
            }
            if (abs($sum - 1.0) > 0.000001) {
                $this->error($errors, 'WS_PARAMSET_WEIGHTS_INVALID', 'scoring.weights.value.sum');
            }
        }

        $minTrades = $this->numericValue($payload, 'eval.min_trades');
        $minTradesOos = $this->numericValue($payload, 'eval.min_trades_oos');
        $minMonthWinRate = $this->numericValue($payload, 'eval.min_month_win_rate_min');
        if ($minTrades !== null && $minTrades < 120) {
            $this->error($errors, 'WS_PARAMSET_EVAL_GATE_INVALID', 'eval.min_trades.value');
        }
        if ($minTradesOos !== null && $minTradesOos < 40) {
            $this->error($errors, 'WS_PARAMSET_EVAL_GATE_INVALID', 'eval.min_trades_oos.value');
        }
        if ($minMonthWinRate !== null && ($minMonthWinRate < 0 || $minMonthWinRate > 1)) {
            $this->error($errors, 'WS_PARAMSET_EVAL_GATE_INVALID', 'eval.min_month_win_rate_min.value');
        }
    }

    private function validateResearchSelection(array $payload, array &$errors): void
    {
        if (! array_key_exists('research_selection', $payload)) {
            return;
        }
        $selection = $payload['research_selection'];
        if (! is_array($selection)) {
            $this->error($errors, 'WS_PARAMSET_RESEARCH_SELECTION_INVALID', 'research_selection');
            return;
        }
        $required = [
            'schema_version', 'hypothesis_code', 'rule_code',
            'signal_date_only', 'oos_used', 'thresholds',
        ];
        if (! $this->sameKeys($selection, $required)) {
            $this->error($errors, 'WS_PARAMSET_RESEARCH_SELECTION_INVALID', 'research_selection.keys');
        }
        if (($selection['schema_version'] ?? null) !== 'WS_NEW_STRATEGY_RESEARCH_SELECTION_V1'
            || ($selection['signal_date_only'] ?? null) !== true
            || ($selection['oos_used'] ?? null) !== false
            || ! is_array($selection['thresholds'] ?? null)) {
            $this->error($errors, 'WS_PARAMSET_RESEARCH_SELECTION_INVALID', 'research_selection.contract');
            return;
        }

        $hypothesis = (string) ($selection['hypothesis_code'] ?? '');
        $rule = (string) ($selection['rule_code'] ?? '');
        $thresholds = $selection['thresholds'];
        if ($hypothesis === 'H1_BREAKOUT_QUALITY_CONFIRMATION'
            && $rule === 'SIGNAL_CLOSE_TO_HH20_0_TO_2_PCT'
            && $this->sameKeys($thresholds, ['min_close_to_hh20_pct', 'max_close_to_hh20_pct'])
            && $this->exactNumber($thresholds['min_close_to_hh20_pct'] ?? null, 0.0)
            && $this->exactNumber($thresholds['max_close_to_hh20_pct'] ?? null, 0.02)) {
            return;
        }
        if ($hypothesis === 'H2_MOMENTUM_PERSISTENCE'
            && $rule === 'SIGNAL_ROC20_10_TO_15_PCT'
            && $this->sameKeys($thresholds, ['min_roc20', 'max_roc20'])
            && $this->exactNumber($thresholds['min_roc20'] ?? null, 0.10)
            && $this->exactNumber($thresholds['max_roc20'] ?? null, 0.15)) {
            return;
        }
        if ($hypothesis === 'H3_MARKET_REGIME_COMPATIBILITY'
            && $rule === 'SIGNAL_IHSG_MIXED_REGIME_ONLY'
            && $this->sameKeys($thresholds, ['benchmark_code', 'allowed_regimes'])
            && ($thresholds['benchmark_code'] ?? null) === 'IHSG'
            && ($thresholds['allowed_regimes'] ?? null) === ['MIXED']) {
            return;
        }
        if ($hypothesis === WatchlistBacktestTailRiskS01ParamGridCatalog::H1_ROW_CODE
            && $rule === 'SIGNAL_ROC20_10_TO_15_AND_IHSG_NON_WEAK'
            && $this->sameKeys($thresholds, [
                'min_roc20', 'max_roc20', 'benchmark_code', 'allowed_regimes',
            ])
            && $this->exactNumber($thresholds['min_roc20'] ?? null, 0.10)
            && $this->exactNumber($thresholds['max_roc20'] ?? null, 0.15)
            && ($thresholds['benchmark_code'] ?? null) === 'IHSG'
            && ($thresholds['allowed_regimes'] ?? null) === ['STRONG', 'MIXED']) {
            return;
        }
        if ($hypothesis === WatchlistBacktestTailRiskS01ParamGridCatalog::H2_ROW_CODE
            && $rule === 'SIGNAL_ROC20_10_TO_15_AND_TICK_RISK_LT_1P5'
            && $this->sameKeys($thresholds, [
                'min_roc20', 'max_roc20', 'max_signal_tick_risk_expansion_pct',
            ])
            && $this->exactNumber($thresholds['min_roc20'] ?? null, 0.10)
            && $this->exactNumber($thresholds['max_roc20'] ?? null, 0.15)
            && $this->exactNumber(
                $thresholds['max_signal_tick_risk_expansion_pct'] ?? null,
                0.015
            )) {
            return;
        }
        if ($hypothesis === WatchlistBacktestTailRiskS01ParamGridCatalog::H3_ROW_CODE
            && $rule === 'SIGNAL_ROC20_10_TO_15_BASELINE_FOR_LOSS_CONTAINMENT'
            && $this->sameKeys($thresholds, ['min_roc20', 'max_roc20'])
            && $this->exactNumber($thresholds['min_roc20'] ?? null, 0.10)
            && $this->exactNumber($thresholds['max_roc20'] ?? null, 0.15)) {
            return;
        }
        if (WatchlistBacktestPriceQualityP01ParamGridCatalog::isKnownRow($hypothesis)
            && $rule === WatchlistBacktestPriceQualityP01ParamGridCatalog::RULE_CODE
            && $this->sameKeys($thresholds, [
                'min_roc20', 'max_roc20', 'benchmark_code', 'allowed_regimes',
                'min_signal_close_price',
            ])
            && $this->exactNumber($thresholds['min_roc20'] ?? null, 0.10)
            && $this->exactNumber($thresholds['max_roc20'] ?? null, 0.15)
            && ($thresholds['benchmark_code'] ?? null) === 'IHSG'
            && ($thresholds['allowed_regimes'] ?? null) === ['STRONG', 'MIXED']
            && $this->exactNumber(
                $thresholds['min_signal_close_price'] ?? null,
                (float) WatchlistBacktestPriceQualityP01ParamGridCatalog
                    ::minimumSignalClosePriceForRow($hypothesis)
            )) {
            return;
        }
        if (WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::isKnownRow(
            $hypothesis
        )
            && $rule
                === WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::RULE_CODE
            && $this->sameKeys($thresholds, [
                'min_roc20', 'max_roc20', 'benchmark_code', 'allowed_regimes',
                'min_signal_close_price', 'min_close_to_hh20_pct',
            ])
            && $this->exactNumber($thresholds['min_roc20'] ?? null, 0.10)
            && $this->exactNumber($thresholds['max_roc20'] ?? null, 0.15)
            && ($thresholds['benchmark_code'] ?? null) === 'IHSG'
            && ($thresholds['allowed_regimes'] ?? null) === ['STRONG', 'MIXED']
            && $this->exactNumber(
                $thresholds['min_signal_close_price'] ?? null,
                50.0
            )
            && $this->exactNumber(
                $thresholds['min_close_to_hh20_pct'] ?? null,
                -0.05
            )) {
            return;
        }

        $this->error($errors, 'WS_PARAMSET_RESEARCH_SELECTION_INVALID', 'research_selection.thresholds');
    }

    private function validateResearchExecution(array $payload, array &$errors): void
    {
        if (! array_key_exists('research_execution', $payload)) {
            return;
        }
        $execution = $payload['research_execution'];
        $required = [
            'schema_version',
            'remediation_code',
            'rule_code',
            'fixed_before_entry',
            'preplanned_target_pct',
            'profit_close_threshold_pct',
            'profit_signal_day_offsets',
            'profit_signal_exit',
            'fallback_exit',
            'canonical_stop_used',
            'raw_tradable_ohlcv_required',
            'future_derived_route_used',
            'oos_used',
        ];
        if (! is_array($execution)) {
            $this->error($errors, 'WS_PARAMSET_RESEARCH_EXECUTION_INVALID', 'research_execution');
            return;
        }
        $r02Execution = WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution();
        if ($execution == $r02Execution) {
            return;
        }
        $s01Execution = WatchlistBacktestTailRiskS01ParamGridCatalog::lossContainmentExecution();
        if ($execution == $s01Execution) {
            return;
        }
        $s01RemediationExecution =
            WatchlistBacktestTailRiskS01RemediationParamGridCatalog::researchExecution();
        if ($execution == $s01RemediationExecution) {
            return;
        }
        $p01RemediationExecution =
            WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::researchExecution();
        if ($execution == $p01RemediationExecution) {
            return;
        }
        if (! $this->sameKeys($execution, $required)
            || ($execution['schema_version'] ?? null) !== 'WS_NEW_STRATEGY_RESEARCH_EXECUTION_V1'
            || ($execution['remediation_code'] ?? null) !== 'R02_M1_SINGLE_ALLOWED_REMEDIATION'
            || ($execution['rule_code'] ?? null)
                !== 'SEQUENTIAL_TARGET_0P5_PROFIT_CLOSE_NEXT_OPEN_D5_CLOSE'
            || ($execution['fixed_before_entry'] ?? null) !== true
            || ! $this->exactNumber($execution['preplanned_target_pct'] ?? null, 0.005)
            || ! $this->exactNumber($execution['profit_close_threshold_pct'] ?? null, 0.0)
            || ($execution['profit_signal_day_offsets'] ?? null) !== [1, 2, 3]
            || ($execution['profit_signal_exit'] ?? null) !== 'NEXT_TRADING_DAY_OPEN'
            || ($execution['fallback_exit'] ?? null) !== 'D5_CLOSE'
            || ($execution['canonical_stop_used'] ?? null) !== false
            || ($execution['raw_tradable_ohlcv_required'] ?? null) !== true
            || ($execution['future_derived_route_used'] ?? null) !== false
            || ($execution['oos_used'] ?? null) !== false) {
            $this->error($errors, 'WS_PARAMSET_RESEARCH_EXECUTION_INVALID', 'research_execution');
        }
    }

    private function exactNumber($actual, float $expected): bool
    {
        return (is_int($actual) || is_float($actual))
            && abs((float) $actual - $expected) <= 0.000000001;
    }

    private function sameKeys(array $payload, array $expected): bool
    {
        $actual = array_keys($payload);
        sort($actual, SORT_STRING);
        sort($expected, SORT_STRING);

        return $actual === $expected;
    }

    private function value(array $payload, string $path)
    {
        $parts = explode('.', $path);
        if (count($parts) !== 2) {
            return null;
        }

        return $payload[$parts[0]][$parts[1]]['value'] ?? null;
    }

    private function numericValue(array $payload, string $path): ?float
    {
        $value = $this->value($payload, $path);
        return is_int($value) || is_float($value) ? (float) $value : null;
    }

    private function error(array &$errors, string $reasonCode, string $path): void
    {
        $key = $reasonCode.'|'.$path;
        $errors[$key] = ['reason_code' => $reasonCode, 'path' => $path];
    }

    private function normalizeForHash($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) {
                return $this->normalizeForHash($item);
            }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeForHash($item);
        }

        return $value;
    }
}
