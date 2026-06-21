<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService
{
    public const RUN_CODE = 'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC';
    public const ARTIFACT_TYPE = 'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC';
    public const DEFAULT_C42_ARTIFACT = 'storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json';
    public const DEFAULT_EXPECTED_C42_HASH = '939e85f179b3bf5d2511730fafb4271cf7c2ca11';
    public const DEFAULT_C42_FILE_SHA1 = 'CBB44B864DD9B2071DE5B10C426F01ED2776525D';
    public const DEFAULT_SOURCE_EVIDENCE_ARTIFACT = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C42_STATUS = 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED';
    public const TARGET_CANDIDATE_CODE = 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA';
    public const BASELINE_CANDIDATE_CODE = 'C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR';
    public const BRANCH_TOP_SHARE_LIMIT = 0.80;

    private const EXPECTED_C42_CONCLUSIONS = [
        'C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE',
        'C42_INSUFFICIENT_IS_EVIDENCE_FOR_WARNING_EXPANSION',
    ];

    public function execute(
        string $c42Artifact = self::DEFAULT_C42_ARTIFACT,
        string $expectedC42Hash = self::DEFAULT_EXPECTED_C42_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c42Artifact = trim($c42Artifact) !== '' ? trim($c42Artifact) : self::DEFAULT_C42_ARTIFACT;
        $expectedC42Hash = trim($expectedC42Hash) !== '' ? trim($expectedC42Hash) : self::DEFAULT_EXPECTED_C42_HASH;
        $from = trim($from) !== '' ? trim($from) : self::DEFAULT_FROM;
        $to = trim($to) !== '' ? trim($to) : self::DEFAULT_TO;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c42Artifact, $expectedC42Hash, null, null, null, null, $from, $to, $createdAt);
        if (! is_file($c42Artifact)) {
            return $this->blocked($artifact, 'C43_BLOCKED_MISSING_C42_ARTIFACT', 'WS_BT_C43_C42_ARTIFACT_MISSING', 'C43 requires the locked C42 artifact, but the file is missing.', $outputPath);
        }

        $c42 = json_decode((string) file_get_contents($c42Artifact), true);
        if (! is_array($c42)) {
            return $this->blocked($artifact, 'C43_BLOCKED_MISSING_C42_ARTIFACT', 'WS_BT_C43_C42_ARTIFACT_UNREADABLE', 'C42 artifact is not readable JSON.', $outputPath);
        }

        $actualC42Hash = $this->stableHash($c42);
        $artifact = $this->baseArtifact(
            $c42Artifact,
            $expectedC42Hash,
            $actualC42Hash,
            $c42['status'] ?? null,
            $c42['diagnostic_conclusion'] ?? null,
            $c42['next_step_recommendation'] ?? null,
            $from,
            $to,
            $createdAt
        );
        $artifact['source_c42_summary'] = $this->sourceC42Summary($c42);

        if ($actualC42Hash !== $expectedC42Hash) {
            return $this->blocked($artifact, 'C43_BLOCKED_C42_HASH_MISMATCH', 'WS_BT_C43_C42_ARTIFACT_HASH_MISMATCH', 'C42 artifact stable hash does not match the expected locked hash.', $outputPath);
        }
        if (($c42['status'] ?? null) !== self::EXPECTED_C42_STATUS) {
            return $this->blocked($artifact, 'C43_BLOCKED_UNEXPECTED_C42_STATUS', 'WS_BT_C43_UNEXPECTED_C42_STATUS', 'C43 requires a completed C42 artifact.', $outputPath);
        }
        if (! in_array($c42['diagnostic_conclusion'] ?? null, self::EXPECTED_C42_CONCLUSIONS, true)) {
            return $this->blocked($artifact, 'C43_BLOCKED_UNEXPECTED_C42_CONCLUSION', 'WS_BT_C43_UNEXPECTED_C42_CONCLUSION', 'C42 diagnostic conclusion does not authorize C43 field expansion.', $outputPath);
        }
        if (! $this->strictFalse($c42['production_ready'] ?? false)) {
            return $this->blocked($artifact, 'C43_BLOCKED_C42_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C43_C42_PRODUCTION_READY_NOT_FALSE', 'C43 requires C42 production_ready=false.', $outputPath);
        }
        if (! $this->strictFalse($c42['is_period']['oos_data_used_for_tuning'] ?? ($c42['safety_boundaries']['oos_data_used_for_tuning'] ?? false))) {
            return $this->blocked($artifact, 'C43_BLOCKED_C42_OOS_TUNING_FLAG_NOT_FALSE', 'WS_BT_C43_C42_OOS_TUNING_FLAG_NOT_FALSE', 'C43 requires C42 oos_data_used_for_tuning=false.', $outputPath);
        }
        if (($c42['c42_decision_summary']['direct_oos_proof_recommended'] ?? true) !== false) {
            return $this->blocked($artifact, 'C43_BLOCKED_C42_DIRECT_OOS_FLAG_INVALID', 'WS_BT_C43_C42_DIRECT_OOS_FLAG_INVALID', 'C43 requires C42 direct_oos_proof_recommended=false.', $outputPath);
        }
        if (($c42['c42_decision_summary']['oos_proof_unlocked'] ?? true) !== false) {
            return $this->blocked($artifact, 'C43_BLOCKED_C42_OOS_UNLOCK_FLAG_INVALID', 'WS_BT_C43_C42_OOS_UNLOCK_FLAG_INVALID', 'C43 requires C42 oos_proof_unlocked=false.', $outputPath);
        }
        if (($c42['c42_decision_summary']['requires_c43_pre_trade_field_expansion_diagnostic'] ?? false) !== true) {
            return $this->blocked($artifact, 'C43_BLOCKED_C42_DOES_NOT_REQUIRE_PRE_TRADE_FIELD_EXPANSION', 'WS_BT_C43_C42_FIELD_EXPANSION_NOT_REQUIRED', 'C42 does not require C43 pre-trade field expansion.', $outputPath);
        }
        if (! $this->validPeriod($from, $to)) {
            return $this->blocked($artifact, 'C43_BLOCKED_INVALID_IS_PERIOD', 'WS_BT_C43_INVALID_IS_PERIOD', 'C43 requires a valid IS period where from <= to.', $outputPath);
        }
        if ($this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C43_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C43_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C43 is IS-only and rejects periods touching the reserved OOS window.', $outputPath);
        }

        $sourcePath = trim((string) ($options['source_evidence_artifact'] ?? ($c42['source_evidence_summary']['source_evidence_artifact'] ?? self::DEFAULT_SOURCE_EVIDENCE_ARTIFACT)));
        $source = $sourcePath !== '' && is_file($sourcePath)
            ? json_decode((string) file_get_contents($sourcePath), true)
            : null;
        $rows = is_array($source) && is_array($source['pick_diagnostic_rows'] ?? null)
            ? $this->isRows($source['pick_diagnostic_rows'], $from, $to)
            : [];

        $g21Rows = $this->targetRows($rows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16Rows = $this->targetRows($rows, 'G16', 'next_open_delay_after_close_signal');
        $baselineRows = array_merge($g16Rows, $g21Rows);
        $baselineMonths = $this->uniqueMonths($baselineRows);
        $quota = $this->metadataMonthlyQuotaRows($g21Rows, $g16Rows, $baselineMonths, self::BRANCH_TOP_SHARE_LIMIT);
        $targetRows = array_merge($g16Rows, $quota['rows']);

        $sourceLoad = $this->loadPreTradeSources($baselineRows, $options);
        $baselineRows = $this->enrichRows($baselineRows, $sourceLoad['rows']);
        $targetRows = $this->enrichRows($targetRows, $sourceLoad['rows']);
        $artifact['source_evidence_summary'] = [
            'source_evidence_artifact' => $sourcePath,
            'source_evidence_available' => count($rows) > 0,
            'is_rows' => count($rows),
            'baseline_rows' => count($baselineRows),
            'g21_rows' => count($g21Rows),
            'g16_rows' => count($g16Rows),
            'target_rows' => count($targetRows),
            'metadata_monthly_g21_quota_per_month' => $quota['quota_per_month'],
            'pre_trade_source_mode' => $sourceLoad['mode'],
            'pre_trade_source_row_count' => count($sourceLoad['rows']),
            'pre_trade_source_error' => $sourceLoad['error'],
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
        ];

        $definitions = $this->fieldDefinitions();
        $artifact['field_discovery_matrix'] = $this->fieldDiscoveryMatrix($definitions, $rows, $baselineRows, $c42, $sourceLoad);
        $artifact['timing_and_leakage_audit'] = $this->timingAudit($artifact['field_discovery_matrix']);
        $artifact['join_feasibility_matrix'] = $this->joinFeasibility($artifact['field_discovery_matrix']);
        [$artifact['warning_cluster_enrichment'], $artifact['cluster_field_explanation_table']] = $this->clusterEnrichment(
            $definitions,
            $artifact['field_discovery_matrix'],
            $baselineRows,
            $targetRows,
            (string) ($artifact['source_c42_summary']['suspected_warning_month'] ?? '2024-03')
        );
        $artifact['refinement_readiness_assessment'] = $this->refinementReadiness(
            $artifact['field_discovery_matrix'],
            $artifact['cluster_field_explanation_table']
        );
        $artifact['guard_preservation_feasibility'] = $this->guardPreservationFeasibility($artifact['refinement_readiness_assessment']);
        $artifact['c43_decision_summary'] = $this->decisionSummary($artifact);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($artifact);
        $artifact['not_evaluable_reasons'] = $this->notEvaluableReasons($artifact);
        $artifact['status'] = 'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC_COMPLETED';
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusion($artifact['c43_decision_summary']);
        $artifact['next_step_recommendation'] = $this->nextStepRecommendation($artifact['c43_decision_summary']);
        $artifact['diagnostics'][] = [
            'reason_code' => $artifact['diagnostic_conclusion'],
            'message' => 'C43 completed an IS-only field availability, timing, join, and warning-cluster diagnostic. It did not form a final candidate or run OOS proof.',
            'fatal' => false,
        ];

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(string $path, string $expectedHash, ?string $actualHash, $status, $conclusion, $nextStep, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C43_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c42_artifact' => $path,
            'expected_c42_hash' => $expectedHash,
            'actual_c42_hash' => $actualHash,
            'c42_hash_match' => $actualHash !== null && $actualHash === $expectedHash,
            'expected_c42_file_sha1' => self::DEFAULT_C42_FILE_SHA1,
            'c42_status' => $status,
            'c42_diagnostic_conclusion' => $conclusion,
            'c42_next_step_recommendation' => $nextStep,
            'is_period' => [
                'from' => $from,
                'to' => $to,
                'oos_reserved_from' => self::OOS_RESERVED_FROM,
                'oos_reserved_to' => self::OOS_RESERVED_TO,
                'oos_data_used_for_tuning' => false,
            ],
            'source_c42_summary' => [],
            'source_evidence_summary' => [],
            'field_discovery_matrix' => [],
            'timing_and_leakage_audit' => [],
            'join_feasibility_matrix' => [],
            'warning_cluster_enrichment' => [],
            'cluster_field_explanation_table' => [],
            'refinement_readiness_assessment' => [],
            'guard_preservation_feasibility' => [],
            'c43_decision_summary' => [],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC_PENDING',
            'next_step_recommendation' => 'C43_PENDING',
            'diagnostics' => [[
                'reason_code' => 'WS_BT_C43_SOURCE_TRUTH_COMPATIBILITY_NOTE',
                'message' => 'C43 locks C42 and audits repository/database pre-trade fields inside IS only.',
                'fatal' => false,
            ]],
            'safety_boundaries' => [
                'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC' => true,
                'C42_ARTIFACT_HASH_LOCK' => true,
                'IS_ONLY_FIELD_EXPANSION_DIAGNOSTIC' => true,
                'C43_FROM_C42_WARNING_GAP_REQUIREMENTS' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C42_MUTATION' => true,
                'NO_C01_TO_C42_ARTIFACT_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_return_used_for_candidate_selection' => false,
            ],
            'execution_model' => ['entry' => 'NEXT_OPEN', 'exit' => 'STOP_TP_OR_TIME', 'hold' => 5, 'fee' => 'IDR_FIXED', 'slip' => 0, 'gap' => 'OPEN', 'px' => 'IDX_BANDS'],
            'created_at' => $createdAt,
        ];
    }

    private function sourceC42Summary(array $c42): array
    {
        $decision = is_array($c42['c42_decision_summary'] ?? null) ? $c42['c42_decision_summary'] : [];
        $warning = is_array($c42['warning_explanation_summary'] ?? null) ? $c42['warning_explanation_summary'] : [];
        $guard = is_array($c42['guard_preservation_audit'] ?? null) ? $c42['guard_preservation_audit'] : [];
        return [
            'target_candidate_code' => $c42['source_c41_summary']['target_candidate_code'] ?? self::TARGET_CANDIDATE_CODE,
            'warning_interpretation' => $warning['warning_interpretation'] ?? null,
            'suspected_warning_month' => $c42['warning_window_expansion'][0]['suspected_warning_month'] ?? ($c42['non_bad_month_warning_expansion']['target_bad_like_month_source'] ?? null),
            'rolling_warning_explanation_result' => $warning['rolling_warning_explanation_result'] ?? null,
            'normal_month_warning_explanation_result' => $warning['normal_month_warning_explanation_result'] ?? null,
            'c39_guard_preservation_result' => $guard['c39_guard_preservation_result'] ?? null,
            'safe_refinement_field_available' => (bool) ($decision['safe_refinement_field_available'] ?? false),
            'safe_refinement_candidate_formed' => (bool) ($decision['safe_refinement_candidate_formed'] ?? false),
            'direct_oos_proof_recommended' => (bool) ($decision['direct_oos_proof_recommended'] ?? false),
            'oos_proof_unlocked' => (bool) ($decision['oos_proof_unlocked'] ?? false),
            'production_ready' => false,
            'next_step_recommendation' => $c42['next_step_recommendation'] ?? null,
        ];
    }

    private function fieldDefinitions(): array
    {
        $directSafe = [
            ['trade_date', 'identity', 'C28 pick_diagnostic_rows', 'signal/trade date is known at selection'],
            ['trade_month', 'identity', 'C28 pick_diagnostic_rows', 'derived from trade_date'],
            ['ticker', 'identity', 'C28 pick_diagnostic_rows', 'ticker identity is known at selection'],
            ['symbol', 'identity', 'C28 pick_diagnostic_rows', 'alias of ticker when present'],
            ['selected_source_code', 'branch_metadata', 'C28 pick_diagnostic_rows', 'branch assignment precedes post-selection evaluation'],
            ['bucket_code', 'branch_metadata', 'C28 pick_diagnostic_rows', 'rule bucket is known at selection'],
            ['param_id', 'parameter_metadata', 'C28 pick_diagnostic_rows', 'locked parameter identity'],
            ['row_code', 'parameter_metadata', 'C28 pick_diagnostic_rows', 'locked parameter row identity'],
            ['ticker_concentration_metadata', 'derived_metadata', 'C28 pick_diagnostic_rows', 'derived only from selected-row ticker counts'],
            ['branch_source_quota_metadata', 'derived_metadata', 'C39/C42 metadata quota', 'derived without return or future path'],
            ['month_quota_metadata', 'derived_metadata', 'C39/C42 metadata quota', 'derived from trade_month and branch counts'],
        ];
        $definitions = [];
        foreach ($directSafe as $item) {
            $definitions[] = $this->definition($item[0], $item[1], 'artifact', $item[2], null, false, $item[3], 'SAFE_PRE_TRADE_SELECTION_FIELD', true, false, null, false);
        }

        $joinable = [
            ['signal_open', 'signal_ohlcv', 'eod_bars', 'open', false],
            ['signal_high', 'signal_ohlcv', 'eod_bars', 'high', false],
            ['signal_low', 'signal_ohlcv', 'eod_bars', 'low', false],
            ['signal_close', 'signal_ohlcv', 'eod_bars', 'close', false],
            ['signal_volume', 'signal_ohlcv', 'eod_bars', 'volume', false],
            ['dv20_idr', 'liquidity', 'eod_indicators', 'dv20_idr', true],
            ['atr14_pct', 'volatility', 'eod_indicators', 'atr14_pct', true],
            ['vol_ratio', 'volume_quality', 'eod_indicators', 'vol_ratio', true],
            ['roc20', 'momentum', 'eod_indicators', 'roc20', true],
            ['hh20', 'trend', 'eod_indicators', 'hh20', false],
            ['ma20', 'trend', 'eod_indicators', 'ma20', false],
            ['ma50', 'trend', 'eod_indicators', 'ma50', false],
            ['close_to_hh20_pct', 'trend', 'eod_indicators', 'close_to_hh20_pct', true],
            ['close_to_ma20_pct', 'trend', 'eod_indicators', 'close_vs_ma20_pct', true],
            ['close_to_ma50_pct', 'trend', 'eod_indicators', 'close_vs_ma50_pct', true],
            ['ma20_slope_pct', 'trend', 'eod_indicators', 'ma20_slope_pct', true],
            ['rs_20_vs_ihsg', 'relative_strength', 'eod_indicators', 'rs_20_vs_ihsg', true],
            ['rs_20_vs_sector', 'relative_strength', 'eod_indicators', 'rs_20_vs_sector', true],
            ['sector_roc20', 'sector_health', 'eod_indicators', 'sector_roc20', true],
            ['sector_code', 'sector_metadata', 'eod_indicators/ticker_sector_memberships', 'sector_code', true],
            ['sector_name', 'sector_metadata', 'market_data_sectors', 'sector_name', true],
            ['market_index_roc20', 'market_condition', 'market_benchmark_indicators', 'market_index_roc20', true],
            ['market_index_ma20_slope_pct', 'market_condition', 'market_benchmark_indicators', 'market_index_ma20_slope_pct', true],
            ['eligibility_status', 'eligibility', 'eod_eligibility', 'eligibility_status', true],
            ['suspension_status', 'event_risk', 'eod_indicators', 'is_suspended', true],
            ['uma_status', 'event_risk', 'eod_indicators', 'is_uma', true],
            ['corporate_action_flag', 'event_risk', 'eod_indicators', 'corporate_action_flag', true],
            ['event_risk_flag', 'event_risk', 'eod_indicators', 'event_risk_flag', true],
            ['liquidity_bucket', 'derived_pre_trade', 'eod_indicators.dv20_idr', 'dv20_idr', true],
            ['volume_bucket', 'derived_pre_trade', 'eod_indicators.vol_ratio', 'vol_ratio', true],
            ['volatility_bucket', 'derived_pre_trade', 'eod_indicators.atr14_pct', 'atr14_pct', true],
            ['trend_bucket', 'derived_pre_trade', 'eod_indicators.ma20_slope_pct', 'ma20_slope_pct', true],
            ['relative_strength_bucket', 'derived_pre_trade', 'eod_indicators.rs_20_vs_ihsg', 'rs_20_vs_ihsg', true],
        ];
        foreach ($joinable as $item) {
            $definitions[] = $this->definition($item[0], $item[1], 'database', $item[2], $item[3], true, 'source trade_date equals C28 signal trade_date and precedes next-open entry', 'SAFE_PRE_TRADE_JOINABLE_FIELD', true, false, null, $item[4]);
        }

        foreach ([
            ['profile_code', 'profile_metadata'],
            ['profile_exit_reason', 'exit_evaluation'],
        ] as $item) {
            $definitions[] = $this->definition($item[0], $item[1], 'artifact', 'C28 pick_diagnostic_rows', null, false, 'post-pick diagnostic profile context', 'DIAGNOSTIC_ONLY_EVALUATION_FIELD', false, true, 'Profile/exit context is not a pre-trade quality input.', false);
        }
        foreach (['ret_net', 'avg_ret_net', 'profile_ret_net', 'delta_vs_raw_r09', 'win_flag'] as $field) {
            $sourceKey = $field === 'ret_net' || $field === 'avg_ret_net' ? 'profile_ret_net' : $field;
            $definitions[] = $this->definition($field, 'realized_return', 'artifact', 'C28/C39/C42 evaluation rows', $sourceKey, false, 'known only after entry/future price path', 'UNSAFE_FUTURE_OR_RETURN_FIELD', false, true, 'Realized return is evaluation-only and forbidden for selection.', false);
        }
        foreach (['entry_open', 'next_open', 'gap_open_diagnostic'] as $field) {
            $definitions[] = $this->definition($field, 'execution_price', 'database', 'eod_bars on entry_date', null, true, 'known at or after NEXT_OPEN entry', 'UNSAFE_NEXT_OPEN_OR_EXECUTION_FIELD', false, true, 'Next-open/execution data is not pre-trade selection data.', false);
        }
        foreach (['mfe', 'mae', 'exit_result', 'future_path_price', 'profile_exit_price'] as $field) {
            $definitions[] = $this->definition($field, 'exit_path', 'artifact/database', 'C28 future-path evaluation', null, false, 'derived from D1-D5 or exit path', 'UNSAFE_DERIVED_FROM_EXIT_PATH', false, true, 'Future exit path is forbidden for selection.', false);
        }
        $definitions[] = $this->definition('market_calendar_session_context', 'calendar', 'database', 'market_calendar', null, true, 'cal_date must equal trade_date; source exists but is not carried to C28/C43 quality rows', 'SOURCE_EXISTS_BUT_NOT_JOINED', false, false, null, false);
        $definitions[] = $this->definition('raw_trading_status_event_notes', 'event_risk', 'database', 'market_data_trading_status_events', null, true, 'event trade_date exists, but ingestion/publication timing of free-text notes is not proven for historical selection', 'SOURCE_EXISTS_BUT_TIMING_UNCLEAR', false, true, 'Historical as-of publication timing is unclear.', false);
        foreach (['breadth_fields', 'special_monitoring_status'] as $field) {
            $definitions[] = $this->definition($field, 'unavailable_source', 'none', 'not found in repository schema/artifacts', null, true, 'no source/effective date found', 'UNAVAILABLE_FIELD', false, false, 'No repository/database/artifact source was found.', false, false);
        }
        return $definitions;
    }

    private function definition(string $name, string $group, string $sourceType, string $source, ?string $sourceKey, bool $joinRequired, string $asOf, string $classification, bool $safeSelection, bool $diagnosticOnly, ?string $unsafeReason, bool $cluster, bool $sourceFound = true): array
    {
        return [
            'field_name' => $name,
            'field_group' => $group,
            'source_type' => $sourceType,
            'source_table_or_artifact' => $source,
            'source_class_or_repository' => $this->sourceClass($source),
            'source_key' => $sourceKey ?: $name,
            'source_found' => $sourceFound,
            'join_required' => $joinRequired,
            'join_key_candidates' => $joinRequired ? ['trade_date', 'ticker_id/ticker', 'effective_date when applicable'] : ['field carried by diagnostic row'],
            'as_of_date_rule' => $asOf,
            'timing_safe' => in_array($classification, ['SAFE_PRE_TRADE_SELECTION_FIELD', 'SAFE_PRE_TRADE_JOINABLE_FIELD'], true),
            'safe_for_selection' => $safeSelection,
            'safe_for_diagnostic_only' => $diagnosticOnly,
            'unsafe_reason' => $unsafeReason,
            'field_classification' => $classification,
            'cluster' => $cluster,
        ];
    }

    private function sourceClass(string $source): ?string
    {
        if (strpos($source, 'eod_') !== false || strpos($source, 'market_') !== false || strpos($source, 'ticker_') !== false) {
            return 'App\\Infrastructure\\Persistence\\MarketData\\MarketDataWatchlistReadRepository';
        }
        return strpos($source, 'C28') !== false || strpos($source, 'C39') !== false || strpos($source, 'C42') !== false
            ? self::class
            : null;
    }

    private function loadPreTradeSources(array $rows, array $options): array
    {
        if (array_key_exists('pre_trade_source_rows', $options)) {
            $map = [];
            foreach ((array) $options['pre_trade_source_rows'] as $row) {
                if (is_array($row)) {
                    $map[$this->joinKey($row)] = $row;
                }
            }
            return ['mode' => 'INJECTED_TEST_SOURCE', 'rows' => $map, 'error' => null];
        }
        if (count($rows) === 0) {
            return ['mode' => 'NO_C28_ROWS', 'rows' => [], 'error' => 'No baseline rows were available for a pre-trade join.'];
        }
        try {
            if (! Schema::hasTable('eod_indicators') || ! Schema::hasTable('eod_bars')) {
                return ['mode' => 'REPOSITORY_SOURCE_NOT_MIGRATED', 'rows' => [], 'error' => 'Required eod_indicators/eod_bars tables are unavailable.'];
            }
            $dates = [];
            $tickerIds = [];
            $requiredKeys = [];
            foreach ($rows as $row) {
                if (($row['trade_date'] ?? '') !== '') {
                    $dates[(string) $row['trade_date']] = true;
                }
                if (isset($row['ticker_id']) && is_numeric($row['ticker_id'])) {
                    $tickerIds[(int) $row['ticker_id']] = true;
                }
                $requiredKeys[$this->joinKey($row)] = true;
            }
            if (count($dates) === 0 || count($tickerIds) === 0) {
                return ['mode' => 'JOIN_KEYS_UNAVAILABLE', 'rows' => [], 'error' => 'C28 rows do not expose trade_date+ticker_id join keys.'];
            }
            $select = [
                'i.trade_date', 'i.ticker_id', 'i.sector_code', 'i.dv20_idr', 'i.atr14_pct', 'i.vol_ratio', 'i.roc20', 'i.hh20', 'i.ma20', 'i.ma50',
                'i.close_to_hh20_pct', 'i.close_vs_ma20_pct', 'i.close_vs_ma50_pct', 'i.ma20_slope_pct', 'i.rs_20_vs_ihsg',
                'i.sector_roc20', 'i.rs_20_vs_sector', 'i.corporate_action_flag', 'i.is_suspended', 'i.is_uma', 'i.event_risk_flag',
                'b.open as signal_open', 'b.high as signal_high', 'b.low as signal_low', 'b.close as signal_close', 'b.volume as signal_volume',
                'e.eligible as eligibility_status', 's.sector_name',
            ];
            $map = [];
            foreach (array_chunk(array_keys($dates), 75) as $dateChunk) {
                $query = DB::table('eod_indicators as i')
                    ->leftJoin('eod_bars as b', function ($join): void {
                        $join->on('b.trade_date', '=', 'i.trade_date')->on('b.ticker_id', '=', 'i.ticker_id');
                    })
                    ->leftJoin('eod_eligibility as e', function ($join): void {
                        $join->on('e.trade_date', '=', 'i.trade_date')->on('e.ticker_id', '=', 'i.ticker_id');
                    })
                    ->leftJoin('market_data_sectors as s', 's.sector_code', '=', 'i.sector_code')
                    ->whereIn('i.trade_date', $dateChunk)
                    ->whereIn('i.ticker_id', array_keys($tickerIds))
                    ->select($select);
                foreach ($query->get() as $dbRow) {
                    $row = (array) $dbRow;
                    $key = $this->joinKey($row);
                    if (isset($requiredKeys[$key])) {
                        $map[$key] = $row;
                    }
                }
            }
            $benchmarkByDate = [];
            if (Schema::hasTable('market_benchmark_indicators')) {
                foreach (array_chunk(array_keys($dates), 200) as $dateChunk) {
                    $benchmarks = DB::table('market_benchmark_indicators')
                        ->where('benchmark_code', 'IHSG')
                        ->whereIn('trade_date', $dateChunk)
                        ->select(['trade_date', 'roc_20', 'ma20_slope_pct'])
                        ->get();
                    foreach ($benchmarks as $benchmark) {
                        $benchmarkByDate[(string) $benchmark->trade_date] = [
                            'market_index_roc20' => $benchmark->roc_20,
                            'market_index_ma20_slope_pct' => $benchmark->ma20_slope_pct,
                        ];
                    }
                }
            }
            foreach ($map as $key => $row) {
                $map[$key] = array_merge($row, $benchmarkByDate[(string) ($row['trade_date'] ?? '')] ?? []);
            }
            return ['mode' => 'DATABASE_AS_OF_SIGNAL_DATE_JOIN', 'rows' => $map, 'error' => null];
        } catch (Throwable $e) {
            return ['mode' => 'DATABASE_JOIN_FAILED', 'rows' => [], 'error' => get_class($e).': '.$e->getMessage()];
        }
    }

    private function enrichRows(array $rows, array $sourceRows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $source = $sourceRows[$this->joinKey($row)] ?? [];
            $out[] = array_merge($row, $source);
        }
        return $out;
    }

    private function joinKey(array $row): string
    {
        $tickerKey = isset($row['ticker_id']) && (string) $row['ticker_id'] !== ''
            ? 'ID:'.(string) $row['ticker_id']
            : 'TICKER:'.strtoupper((string) ($row['ticker'] ?? $row['ticker_code'] ?? ''));
        return (string) ($row['trade_date'] ?? '').'|'.$tickerKey;
    }

    private function fieldDiscoveryMatrix(array $definitions, array $sourceRows, array $baselineRows, array $c42, array $sourceLoad): array
    {
        $c42Fields = [];
        foreach (($c42['pre_trade_field_availability_matrix'] ?? []) as $row) {
            if (is_array($row) && isset($row['field_name'])) {
                $c42Fields[(string) $row['field_name']] = (bool) ($row['available'] ?? false);
            }
        }
        $total = count($baselineRows);
        $out = [];
        foreach ($definitions as $definition) {
            $name = $definition['field_name'];
            $directCount = $this->coverageCount($sourceRows, (string) $definition['source_key'], $name);
            $coverage = $definition['join_required']
                ? $this->coverageCount($baselineRows, (string) $definition['source_key'], $name)
                : $directCount;
            if (in_array($name, ['ticker_concentration_metadata', 'branch_source_quota_metadata', 'month_quota_metadata'], true)) {
                $coverage = count($sourceRows);
            }
            $denominator = $definition['join_required'] ? $total : count($sourceRows);
            $classification = $definition['field_classification'];
            if ($classification === 'SAFE_PRE_TRADE_JOINABLE_FIELD' && $coverage === 0) {
                $classification = $sourceLoad['mode'] === 'JOIN_KEYS_UNAVAILABLE'
                    ? 'SOURCE_EXISTS_BUT_NOT_JOINED'
                    : 'SOURCE_EXISTS_BUT_NOT_JOINED';
            }
            $missing = max(0, $denominator - $coverage);
            $row = $definition;
            unset($row['source_key'], $row['cluster']);
            $row += [
                'available_in_c28_rows' => $directCount > 0,
                'available_in_c42_artifact' => (bool) ($c42Fields[$name] ?? false),
                'available_in_database_or_repository' => $definition['source_found'],
                'required_join_keys_available' => $this->requiredJoinKeysAvailable($baselineRows, (bool) $definition['join_required']),
                'coverage_count' => $coverage,
                'coverage_pct' => $denominator > 0 ? $coverage / $denominator : 0.0,
                'missing_count' => $missing,
                'missing_pct' => $denominator > 0 ? $missing / $denominator : 1.0,
                'field_classification' => $classification,
                'reason_code' => $this->classificationReason($classification),
                'message' => $this->classificationMessage($name, $classification, $coverage, $denominator),
            ];
            if ($classification === 'SOURCE_EXISTS_BUT_NOT_JOINED') {
                $row['safe_for_selection'] = false;
                $row['timing_safe'] = $definition['timing_safe'];
            }
            $out[] = $row;
        }
        return $out;
    }

    private function coverageCount(array $rows, string $sourceKey, string $fieldName): int
    {
        $count = 0;
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $value = $this->valueForField($row, $fieldName, $sourceKey);
            if ($this->hasValue($value)) {
                $count++;
            }
        }
        return $count;
    }

    private function requiredJoinKeysAvailable(array $rows, bool $joinRequired): bool
    {
        if (! $joinRequired) {
            return true;
        }
        foreach ($rows as $row) {
            if (($row['trade_date'] ?? '') !== '' && (($row['ticker_id'] ?? '') !== '' || ($row['ticker'] ?? '') !== '')) {
                return true;
            }
        }
        return false;
    }

    private function classificationReason(string $classification): string
    {
        $map = [
            'SAFE_PRE_TRADE_SELECTION_FIELD' => 'C43_FIELD_CARRIED_AND_PRE_TRADE_SAFE',
            'SAFE_PRE_TRADE_JOINABLE_FIELD' => 'C43_FIELD_JOINED_AS_OF_SIGNAL_DATE',
            'DIAGNOSTIC_ONLY_EVALUATION_FIELD' => 'C43_FIELD_EVALUATION_ONLY',
            'UNSAFE_FUTURE_OR_RETURN_FIELD' => 'C43_FIELD_REALIZED_RETURN_FORBIDDEN_FOR_SELECTION',
            'UNSAFE_NEXT_OPEN_OR_EXECUTION_FIELD' => 'C43_FIELD_KNOWN_AT_OR_AFTER_ENTRY',
            'UNSAFE_DERIVED_FROM_EXIT_PATH' => 'C43_FIELD_DERIVED_FROM_FUTURE_EXIT_PATH',
            'UNAVAILABLE_FIELD' => 'C43_FIELD_SOURCE_NOT_FOUND',
            'SOURCE_EXISTS_BUT_NOT_JOINED' => 'C43_FIELD_SOURCE_EXISTS_NOT_JOINED',
            'SOURCE_EXISTS_BUT_TIMING_UNCLEAR' => 'C43_FIELD_HISTORICAL_AS_OF_TIMING_UNCLEAR',
        ];
        return $map[$classification] ?? 'C43_FIELD_CLASSIFICATION_UNKNOWN';
    }

    private function classificationMessage(string $field, string $classification, int $coverage, int $total): string
    {
        return $field.' classified as '.$classification.' with '.$coverage.'/'.$total.' relevant rows covered.';
    }

    private function timingAudit(array $matrix): array
    {
        $out = [];
        foreach ($matrix as $row) {
            $out[] = [
                'field_name' => $row['field_name'],
                'source_table_or_artifact' => $row['source_table_or_artifact'],
                'as_of_date_rule' => $row['as_of_date_rule'],
                'timing_safe' => (bool) $row['timing_safe'],
                'safe_for_selection' => (bool) $row['safe_for_selection'],
                'safe_for_diagnostic_only' => (bool) $row['safe_for_diagnostic_only'],
                'field_classification' => $row['field_classification'],
                'unsafe_reason' => $row['unsafe_reason'],
                'reason_code' => $row['reason_code'],
            ];
        }
        return $out;
    }

    private function joinFeasibility(array $matrix): array
    {
        $out = [];
        foreach ($matrix as $row) {
            if (! $row['join_required'] || ! $row['source_found']) {
                continue;
            }
            $out[] = [
                'field_name' => $row['field_name'],
                'source_type' => $row['source_type'],
                'source_table_or_artifact' => $row['source_table_or_artifact'],
                'source_class_or_repository' => $row['source_class_or_repository'],
                'join_key_candidates' => $row['join_key_candidates'],
                'required_join_keys_available' => $row['required_join_keys_available'],
                'as_of_date_rule' => $row['as_of_date_rule'],
                'as_of_date_safe' => (bool) $row['timing_safe'],
                'coverage_count' => $row['coverage_count'],
                'coverage_pct' => $row['coverage_pct'],
                'missing_count' => $row['missing_count'],
                'missing_pct' => $row['missing_pct'],
                'safe_for_selection' => (bool) $row['safe_for_selection'],
                'safe_for_diagnostic_only' => (bool) $row['safe_for_diagnostic_only'],
                'field_classification' => $row['field_classification'],
                'reason_code' => $row['reason_code'],
                'message' => $row['message'],
            ];
        }
        return $out;
    }

    private function clusterEnrichment(array $definitions, array $matrix, array $baselineRows, array $targetRows, string $month): array
    {
        $classification = [];
        foreach ($matrix as $row) {
            $classification[$row['field_name']] = $row;
        }
        $baselineMonth = $this->filterMonth($baselineRows, $month);
        $targetMonth = $this->filterMonth($targetRows, $month);
        $clusterRows = array_values(array_filter($targetMonth, function (array $row): bool {
            return (string) ($row['selected_source_code'] ?? '') === 'G21';
        }));
        $enrichment = [];
        $explanations = [];
        foreach ($definitions as $definition) {
            $field = $definition['field_name'];
            $matrixRow = $classification[$field] ?? null;
            if (! $definition['cluster'] || ! is_array($matrixRow) || $matrixRow['field_classification'] !== 'SAFE_PRE_TRADE_JOINABLE_FIELD') {
                continue;
            }
            $groups = [];
            foreach ($clusterRows as $row) {
                $value = $this->bucketValue($field, $this->valueForField($row, $field, $definition['source_key']));
                if ($value === null) {
                    continue;
                }
                $groups[$value][] = $row;
            }
            if (count($groups) === 0) {
                continue;
            }
            ksort($groups);
            $averages = [];
            foreach ($groups as $value => $groupRows) {
                $metrics = $this->metrics($groupRows);
                if ($metrics['avg_ret_net'] !== null) {
                    $averages[] = $metrics['avg_ret_net'];
                }
                $enrichment[] = [
                    'cluster_code' => 'C42_MARCH_2024_G21_WARNING_CLUSTER',
                    'trade_month' => $month,
                    'selected_source_code' => 'G21',
                    'field_name' => $field,
                    'field_value_or_bucket' => $value,
                    'cluster_row_count' => count($groupRows),
                    'cluster_loss_count' => $metrics['loss_count'],
                    'cluster_avg_ret_net' => $metrics['avg_ret_net'],
                    'cluster_win_rate' => $metrics['win_rate'],
                    'cluster_share' => count($clusterRows) > 0 ? count($groupRows) / count($clusterRows) : null,
                    'baseline_row_count' => $this->countBucket($baselineMonth, $field, $definition['source_key'], $value),
                    'target_row_count' => $this->countBucket($targetMonth, $field, $definition['source_key'], $value),
                    'g16_row_count' => $this->countBucket($targetMonth, $field, $definition['source_key'], $value, 'G16'),
                    'g21_row_count' => $this->countBucket($targetMonth, $field, $definition['source_key'], $value, 'G21'),
                    'safe_for_selection' => true,
                    'safe_for_diagnostic_only' => false,
                    'return_used_for_selection' => false,
                ];
            }
            $strength = $this->explanationStrength($averages, count($groups), count($clusterRows));
            $supports = in_array($strength, ['MEDIUM', 'HIGH'], true) && ($matrixRow['coverage_pct'] ?? 0.0) >= 0.80;
            $explains = $strength !== 'NONE';
            $explanations[] = [
                'field_name' => $field,
                'field_classification' => $matrixRow['field_classification'],
                'field_explains_warning_cluster' => $explains,
                'field_explanation_strength' => $strength,
                'field_can_support_future_refinement' => $supports,
                'coverage_pct' => $matrixRow['coverage_pct'],
                'bucket_count' => count($groups),
                'reason_code' => $supports ? 'C43_SAFE_FIELD_HAS_CLUSTER_DIAGNOSTIC_SEPARATION' : 'C43_SAFE_FIELD_AVAILABLE_REFINEMENT_THRESHOLD_NOT_FORMED',
                'message' => 'Return was used only after the locked metadata picks to evaluate '.$field.' buckets; C43 does not choose a threshold or final rule.',
            ];
            foreach ($enrichment as &$enrichmentRow) {
                if ($enrichmentRow['field_name'] === $field) {
                    $enrichmentRow['field_explains_warning_cluster'] = $explains;
                    $enrichmentRow['field_explanation_strength'] = $strength;
                    $enrichmentRow['field_can_support_future_refinement'] = $supports;
                }
            }
            unset($enrichmentRow);
        }
        return [$enrichment, $explanations];
    }

    private function explanationStrength(array $averages, int $groupCount, int $clusterCount): string
    {
        if ($groupCount < 2 || count($averages) < 2 || $clusterCount < 4) {
            return 'NONE';
        }
        $spread = max($averages) - min($averages);
        if ($spread >= 0.02) {
            return 'HIGH';
        }
        if ($spread >= 0.01) {
            return 'MEDIUM';
        }
        return $spread >= 0.005 ? 'LOW' : 'NONE';
    }

    private function countBucket(array $rows, string $field, string $sourceKey, string $bucket, ?string $source = null): int
    {
        $count = 0;
        foreach ($rows as $row) {
            if ($source !== null && (string) ($row['selected_source_code'] ?? '') !== $source) {
                continue;
            }
            if ($this->bucketValue($field, $this->valueForField($row, $field, $sourceKey)) === $bucket) {
                $count++;
            }
        }
        return $count;
    }

    private function refinementReadiness(array $matrix, array $explanations): array
    {
        $qualityFields = [
            'dv20_idr', 'atr14_pct', 'vol_ratio', 'roc20', 'close_to_hh20_pct', 'close_to_ma20_pct', 'close_to_ma50_pct',
            'ma20_slope_pct', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'sector_roc20', 'sector_code', 'sector_name',
            'market_index_roc20', 'market_index_ma20_slope_pct', 'eligibility_status', 'suspension_status', 'uma_status',
            'corporate_action_flag', 'event_risk_flag', 'liquidity_bucket', 'volume_bucket', 'volatility_bucket', 'trend_bucket', 'relative_strength_bucket',
        ];
        $safe = [];
        $blocked = [];
        $timingUnclear = [];
        $sourceNotJoined = [];
        foreach ($matrix as $row) {
            if ($row['field_classification'] === 'SAFE_PRE_TRADE_JOINABLE_FIELD'
                && in_array($row['field_name'], $qualityFields, true)
                && ($row['coverage_pct'] ?? 0.0) >= 0.80) {
                $safe[] = $row['field_name'];
            }
            if (in_array($row['field_classification'], ['UNSAFE_FUTURE_OR_RETURN_FIELD', 'UNSAFE_NEXT_OPEN_OR_EXECUTION_FIELD', 'UNSAFE_DERIVED_FROM_EXIT_PATH', 'UNAVAILABLE_FIELD'], true)) {
                $blocked[] = ['field_name' => $row['field_name'], 'field_classification' => $row['field_classification'], 'reason_code' => $row['reason_code']];
            }
            if ($row['field_classification'] === 'SOURCE_EXISTS_BUT_TIMING_UNCLEAR') {
                $timingUnclear[] = $row['field_name'];
            }
            if ($row['field_classification'] === 'SOURCE_EXISTS_BUT_NOT_JOINED') {
                $sourceNotJoined[] = $row['field_name'];
            }
        }
        $supporting = [];
        foreach ($explanations as $row) {
            if ($row['field_can_support_future_refinement']) {
                $supporting[] = $row['field_name'];
            }
        }
        $hypotheses = [];
        $hypothesisMap = [
            'dv20_idr' => 'Evaluate a fixed liquidity-bucket quality condition for G21 while retaining a monthly minimum G21 quota.',
            'atr14_pct' => 'Evaluate a fixed volatility-bucket condition for G21 while retaining C39 monthly coverage.',
            'rs_20_vs_ihsg' => 'Evaluate minimum relative-strength confirmation for G21 using signal-date indicators only.',
            'rs_20_vs_sector' => 'Evaluate sector-relative-strength confirmation for G21 using signal-date indicators only.',
            'sector_roc20' => 'Evaluate sector-health confirmation for G21 without suppressing the branch in full.',
            'market_index_roc20' => 'Evaluate an IS market-condition interaction for G21 without choosing a threshold from OOS.',
            'event_risk_flag' => 'Evaluate a signal-date event-risk exclusion subject to C39 coverage and branch floors.',
        ];
        foreach ($hypothesisMap as $field => $message) {
            if (in_array($field, $safe, true)) {
                $hypotheses[] = ['field_name' => $field, 'hypothesis' => $message, 'final_rule_formed' => false];
            }
        }
        $ready = count($safe) > 0 && count($explanations) > 0;
        return [
            'refinement_readiness_result' => $ready ? 'C43_SAFE_PRE_TRADE_FIELDS_READY_FOR_C44_CANDIDATE_FORMATION' : (count($sourceNotJoined) > 0 ? 'C43_DATA_PLUMBING_REQUIRED' : 'C43_EVIDENCE_EXPANSION_REQUIRED'),
            'safe_fields_for_future_refinement' => array_values(array_unique($safe)),
            'cluster_supporting_fields' => array_values(array_unique($supporting)),
            'blocked_fields' => $blocked,
            'timing_unclear_fields' => array_values(array_unique($timingUnclear)),
            'source_exists_but_not_joined_fields' => array_values(array_unique($sourceNotJoined)),
            'recommended_refinement_hypotheses' => $hypotheses,
            'requires_c44_guard_refinement_candidate_formation' => $ready,
            'requires_c44_data_plumbing' => ! $ready && count($sourceNotJoined) > 0,
            'requires_c44_field_timing_validation' => ! $ready && count($timingUnclear) > 0,
            'requires_c44_evidence_expansion' => ! $ready && count($sourceNotJoined) === 0 && count($timingUnclear) === 0,
            'production_ready' => false,
        ];
    }

    private function guardPreservationFeasibility(array $readiness): array
    {
        $out = [];
        foreach (($readiness['recommended_refinement_hypotheses'] ?? []) as $hypothesis) {
            $out[] = [
                'future_refinement_hypothesis' => $hypothesis['hypothesis'],
                'field_name' => $hypothesis['field_name'],
                'coverage_guard_feasible' => true,
                'branch_guard_feasible' => true,
                'g21_not_suppressed_total' => true,
                'months_covered_feasible' => 27,
                'zero_pick_months_risk' => 'CONTROL_REQUIRED_C44_TARGET_ZERO',
                'min_selected_rows_risk' => 'CONTROL_REQUIRED_C44_FLOOR_13',
                'reason_code' => 'C43_C39_GUARDS_FEASIBLE_WITH_MONTHLY_G21_FLOOR',
                'message' => 'Feasibility is conditional: C44 must retain all 27 months, zero zero-pick months, a minimum monthly row floor, and non-zero G21 participation.',
            ];
        }
        return $out;
    }

    private function decisionSummary(array $artifact): array
    {
        $matrix = $artifact['field_discovery_matrix'];
        $has = function (string $classification) use ($matrix): bool {
            foreach ($matrix as $row) {
                if (($row['field_classification'] ?? null) === $classification) {
                    return true;
                }
            }
            return false;
        };
        $readiness = $artifact['refinement_readiness_assessment'];
        $ready = (bool) ($readiness['requires_c44_guard_refinement_candidate_formation'] ?? false);
        return [
            'safe_pre_trade_field_found' => $has('SAFE_PRE_TRADE_SELECTION_FIELD'),
            'safe_joinable_field_found' => $has('SAFE_PRE_TRADE_JOINABLE_FIELD'),
            'source_exists_but_not_joined' => $has('SOURCE_EXISTS_BUT_NOT_JOINED'),
            'timing_unclear_field_found' => $has('SOURCE_EXISTS_BUT_TIMING_UNCLEAR'),
            'cluster_enriched' => count($artifact['warning_cluster_enrichment']) > 0,
            'refinement_ready' => $ready,
            'c43_candidate_decision' => $ready ? 'C43_SAFE_PRE_TRADE_FIELDS_FOUND_FOR_C44_REFINEMENT' : 'C43_WARNING_CLUSTER_ENRICHED_BUT_REFINEMENT_NOT_READY',
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'requires_c44_guard_refinement_candidate_formation' => $ready,
            'requires_c44_data_plumbing' => (bool) ($readiness['requires_c44_data_plumbing'] ?? false),
            'requires_c44_field_timing_validation' => (bool) ($readiness['requires_c44_field_timing_validation'] ?? false),
            'requires_c44_evidence_expansion' => (bool) ($readiness['requires_c44_evidence_expansion'] ?? false),
            'production_ready' => false,
        ];
    }

    private function candidateSafetyAudit(array $artifact): array
    {
        return [[
            'candidate_code' => self::TARGET_CANDIDATE_CODE,
            'review_layer' => 'C43_IS_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC',
            'passed' => true,
            'reason_code' => 'C43_FIELD_PROPOSAL_ONLY_NO_FINAL_CANDIDATE',
            'message' => 'C43 carries the locked C39/C42 candidate only as diagnostic context; it does not form or promote a final guard refinement candidate.',
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'no_oos_proof' => true,
            'no_production_catalog' => true,
            'no_plan_confirm_mutation' => true,
            'production_ready' => false,
        ]];
    }

    private function notEvaluableReasons(array $artifact): array
    {
        $out = [];
        if (! ($artifact['source_evidence_summary']['source_evidence_available'] ?? false)) {
            $out[] = ['validation_layer' => 'C43_SOURCE_EVIDENCE', 'validation_slice' => 'C28_PICK_DIAGNOSTIC_ROWS', 'reason_code' => 'C43_C28_ROWS_UNAVAILABLE', 'message' => 'C28 diagnostic rows were unavailable; cluster enrichment could not be evaluated.'];
        }
        foreach (($artifact['refinement_readiness_assessment']['timing_unclear_fields'] ?? []) as $field) {
            $out[] = ['validation_layer' => 'C43_FIELD_TIMING', 'validation_slice' => $field, 'reason_code' => 'C43_FIELD_TIMING_UNCLEAR', 'message' => 'Historical as-of timing is not proven; the field is excluded from selection.'];
        }
        foreach (($artifact['refinement_readiness_assessment']['source_exists_but_not_joined_fields'] ?? []) as $field) {
            $out[] = ['validation_layer' => 'C43_JOIN_FEASIBILITY', 'validation_slice' => $field, 'reason_code' => 'C43_SOURCE_EXISTS_BUT_NOT_JOINED', 'message' => 'The source exists but this field is not present in the diagnostic join.'];
        }
        if (count($artifact['warning_cluster_enrichment']) === 0) {
            $out[] = ['validation_layer' => 'C43_WARNING_CLUSTER', 'validation_slice' => '2024-03_G21', 'reason_code' => 'C43_WARNING_CLUSTER_NOT_ENRICHED', 'message' => 'No safe joined field values were available for the C42 March-2024 G21 cluster.'];
        }
        return $out;
    }

    private function diagnosticConclusion(array $decision): string
    {
        if ($decision['refinement_ready']) {
            return 'C43_SAFE_PRE_TRADE_FIELDS_FOUND_FOR_C44_REFINEMENT';
        }
        if ($decision['requires_c44_data_plumbing']) {
            return 'C43_SOURCE_EXISTS_BUT_FIELD_NOT_JOINED_REQUIRES_C44_DATA_PLUMBING';
        }
        if ($decision['requires_c44_field_timing_validation']) {
            return 'C43_FIELD_TIMING_UNCLEAR_REQUIRES_C44_TIMING_VALIDATION';
        }
        if ($decision['cluster_enriched']) {
            return 'C43_WARNING_CLUSTER_ENRICHED_BUT_REFINEMENT_NOT_READY';
        }
        return 'C43_NO_SAFE_PRE_TRADE_FIELD_AVAILABLE';
    }

    private function nextStepRecommendation(array $decision): string
    {
        if ($decision['refinement_ready']) {
            return 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION';
        }
        if ($decision['requires_c44_data_plumbing']) {
            return 'C44_PRE_TRADE_FIELD_DATA_PLUMBING';
        }
        if ($decision['requires_c44_field_timing_validation']) {
            return 'C44_PRE_TRADE_FIELD_TIMING_VALIDATION';
        }
        if ($decision['cluster_enriched']) {
            return 'C44_WARNING_CLUSTER_FIELD_EXPANSION_CONTINUATION';
        }
        return 'C44_PRE_TRADE_FIELD_EXPANSION_CONTINUATION';
    }

    private function valueForField(array $row, string $field, string $sourceKey)
    {
        if ($field === 'symbol') {
            return $row['symbol'] ?? $row['ticker'] ?? null;
        }
        if ($field === 'ticker_concentration_metadata') {
            return $row['ticker'] ?? null;
        }
        if ($field === 'branch_source_quota_metadata') {
            return $row['selected_source_code'] ?? null;
        }
        if ($field === 'month_quota_metadata') {
            return $row['trade_month'] ?? null;
        }
        if ($field === 'eligibility_status') {
            return $row['eligibility_status'] ?? $row['eligible'] ?? null;
        }
        return $row[$sourceKey] ?? $row[$field] ?? null;
    }

    private function bucketValue(string $field, $value): ?string
    {
        if (! $this->hasValue($value)) {
            return null;
        }
        if (in_array($field, ['sector_code', 'sector_name', 'eligibility_status', 'suspension_status', 'uma_status', 'corporate_action_flag', 'event_risk_flag'], true)) {
            if (is_bool($value) || $value === 0 || $value === 1 || $value === '0' || $value === '1') {
                return ((bool) $value) ? 'TRUE' : 'FALSE';
            }
            return strtoupper((string) $value);
        }
        $number = $this->num($value);
        if ($number === null) {
            return (string) $value;
        }
        if (in_array($field, ['dv20_idr', 'liquidity_bucket'], true)) {
            return $number < 1000000000 ? 'LT_1B' : ($number < 5000000000 ? '1B_TO_5B' : 'GTE_5B');
        }
        if (in_array($field, ['atr14_pct', 'volatility_bucket'], true)) {
            return $number < 0.02 ? 'LT_2PCT' : ($number < 0.05 ? '2_TO_5PCT' : ($number < 0.08 ? '5_TO_8PCT' : 'GTE_8PCT'));
        }
        if (in_array($field, ['vol_ratio', 'volume_bucket'], true)) {
            return $number < 1.0 ? 'LT_1X' : ($number < 1.5 ? '1_TO_1_5X' : ($number < 2.5 ? '1_5_TO_2_5X' : 'GTE_2_5X'));
        }
        if (in_array($field, ['roc20', 'sector_roc20', 'market_index_roc20'], true)) {
            return $number < 0.0 ? 'NEGATIVE' : ($number < 0.10 ? 'ZERO_TO_10PCT' : 'GTE_10PCT');
        }
        if (in_array($field, ['close_to_hh20_pct', 'close_to_ma20_pct', 'close_to_ma50_pct', 'ma20_slope_pct', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'market_index_ma20_slope_pct', 'trend_bucket', 'relative_strength_bucket'], true)) {
            return $number < 0.0 ? 'NEGATIVE' : ($number == 0.0 ? 'FLAT' : 'POSITIVE');
        }
        return (string) $value;
    }

    private function isRows(array $rows, string $from, string $to): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $date = (string) ($row['trade_date'] ?? '');
            if ($date === '' || strcmp($date, $from) < 0 || strcmp($date, $to) > 0) {
                continue;
            }
            if (($row['oos_executed'] ?? false) === true || (int) ($row['oos_executed'] ?? 0) === 1) {
                continue;
            }
            if (($row['production_ready'] ?? 0) === true || (int) ($row['production_ready'] ?? 0) === 1) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }

    private function targetRows(array $rows, string $source, string $bucket): array
    {
        return array_values(array_filter($rows, function (array $row) use ($source, $bucket): bool {
            return (string) ($row['selected_source_code'] ?? '') === $source
                && (string) ($row['bucket_code'] ?? '') === $bucket
                && $this->num($row['profile_ret_net'] ?? null) !== null;
        }));
    }

    private function metadataMonthlyQuotaRows(array $g21Rows, array $g16Rows, array $baselineMonths, float $topShareLimit): array
    {
        $byMonth = [];
        foreach ($g21Rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month !== '') {
                $byMonth[$month][] = $row;
            }
        }
        foreach ($byMonth as $month => $rows) {
            usort($rows, function (array $a, array $b): int {
                $ka = [(string) ($a['trade_month'] ?? ''), (string) ($a['trade_date'] ?? ''), (string) ($a['ticker'] ?? ''), sprintf('%010d', (int) ($a['param_id'] ?? 0)), (string) ($a['row_code'] ?? '')];
                $kb = [(string) ($b['trade_month'] ?? ''), (string) ($b['trade_date'] ?? ''), (string) ($b['ticker'] ?? ''), sprintf('%010d', (int) ($b['param_id'] ?? 0)), (string) ($b['row_code'] ?? '')];
                return strcmp(implode('|', $ka), implode('|', $kb));
            });
            $byMonth[$month] = $rows;
        }
        $required = count($g16Rows) > 0 ? (int) ceil((count($g16Rows) / $topShareLimit) - count($g16Rows)) : 0;
        $quota = count($baselineMonths) > 0 ? max(1, (int) ceil($required / count($baselineMonths))) : 0;
        $maxQuota = 0;
        foreach ($byMonth as $rows) {
            $maxQuota = max($maxQuota, count($rows));
        }
        $selected = [];
        while ($quota <= $maxQuota) {
            $selected = [];
            foreach ($baselineMonths as $month) {
                $selected = array_merge($selected, array_slice($byMonth[$month] ?? [], 0, $quota));
            }
            if ($this->topBranchShare(array_merge($g16Rows, $selected)) <= $topShareLimit) {
                break;
            }
            $quota++;
        }
        if ($quota > $maxQuota) {
            $quota = $maxQuota;
            $selected = [];
            foreach ($baselineMonths as $month) {
                $selected = array_merge($selected, array_slice($byMonth[$month] ?? [], 0, $quota));
            }
        }
        return ['rows' => $selected, 'quota_per_month' => $quota];
    }

    private function topBranchShare(array $rows): float
    {
        if (count($rows) === 0) {
            return 1.0;
        }
        $counts = [];
        foreach ($rows as $row) {
            $key = (string) ($row['selected_source_code'] ?? 'UNKNOWN');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
        return max($counts) / count($rows);
    }

    private function uniqueMonths(array $rows): array
    {
        $months = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month !== '') {
                $months[$month] = true;
            }
        }
        $months = array_keys($months);
        sort($months);
        return $months;
    }

    private function filterMonth(array $rows, string $month): array
    {
        return array_values(array_filter($rows, function (array $row) use ($month): bool {
            return (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)) === $month;
        }));
    }

    private function metrics(array $rows): array
    {
        $values = [];
        $losses = 0;
        foreach ($rows as $row) {
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($value === null) {
                continue;
            }
            $values[] = $value;
            if ($value < 0.0) {
                $losses++;
            }
        }
        if (count($values) === 0) {
            return ['avg_ret_net' => null, 'win_rate' => null, 'loss_count' => 0];
        }
        $wins = 0;
        foreach ($values as $value) {
            if ($value > 0.0) {
                $wins++;
            }
        }
        return ['avg_ret_net' => array_sum($values) / count($values), 'win_rate' => $wins / count($values), 'loss_count' => $losses];
    }

    private function hasValue($value): bool
    {
        return $value !== null && $value !== '';
    }

    private function num($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function validPeriod(string $from, string $to): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1
            && strcmp($from, $to) <= 0;
    }

    private function touchesOos(string $from, string $to): bool
    {
        return strcmp($to, self::OOS_RESERVED_FROM) >= 0 || strcmp($from, self::OOS_RESERVED_FROM) >= 0;
    }

    private function strictFalse($value): bool
    {
        return $value === false || $value === 0 || $value === '0';
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostics'][] = ['reason_code' => $reasonCode, 'message' => $message, 'fatal' => true];
        $artifact['diagnostic_conclusion'] = 'C43_INPUT_LOCK_OR_BOUNDARY_BLOCKED';
        $artifact['next_step_recommendation'] = 'C43_BLOCKED_UNTIL_INPUT_VALIDATED';
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($outputPath !== '') {
            $this->writeArtifact($outputPath, $artifact, true);
        }
        return $this->resultPayload($artifact, $outputPath, $reasonCode, $message);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($outputPath, $artifact, $overwrite);
        if (! $write['ok']) {
            $artifact['status'] = 'C43_OPERATOR_VALIDATION_REQUIRED';
            return $this->resultPayload($artifact, $outputPath, $write['reason_code'], $write['message']);
        }
        return $this->resultPayload($artifact, $outputPath, $artifact['status'], null);
    }

    private function resultPayload(array $artifact, string $outputPath, string $reasonCode, ?string $message): array
    {
        return [
            'status' => $artifact['status'],
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'] ?? null,
            'production_ready' => 0,
            'expected_c42_hash' => $artifact['expected_c42_hash'] ?? null,
            'actual_c42_hash' => $artifact['actual_c42_hash'] ?? null,
            'c42_hash_match' => $artifact['c42_hash_match'] ?? false,
            'c42_status' => $artifact['c42_status'] ?? null,
            'c42_diagnostic_conclusion' => $artifact['c42_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_c42_summary' => $artifact['source_c42_summary'] ?? [],
            'refinement_readiness_assessment' => $artifact['refinement_readiness_assessment'] ?? [],
            'c43_decision_summary' => $artifact['c43_decision_summary'] ?? [],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) {
            if (! $overwrite) {
                return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.'];
            }
            @unlink($path);
        }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C43 artifact.'];
        }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
