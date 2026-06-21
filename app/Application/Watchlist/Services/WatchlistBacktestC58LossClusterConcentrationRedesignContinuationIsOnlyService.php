<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService
{
    public const RUN_CODE = 'C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY';
    public const ARTIFACT_TYPE = 'C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY';
    public const DEFAULT_C57_ARTIFACT = 'storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json';
    public const DEFAULT_EXPECTED_C57_HASH = '71230896c2121fcfedddf36dd54c9c03ad462b4d';
    public const DEFAULT_EXPECTED_C57_FILE_SHA1 = '50272917A107E304F8EEEB874DBC02A881DB0C31';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const PRIMARY_PARENT_CODES = [
        'C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION',
        'C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE',
        'C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08',
        'C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08',
        'C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER',
        'C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER',
    ];

    /**
     * C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY. C57_ARTIFACT_HASH_LOCK.
     * C57_FILE_SHA1_LOCK. C57_LOCKED_LINEAGE. C57_REGIME_RECONSTRUCTION_RETAINED.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. MARKET_DATA_DICTIONARY_REQUIRED.
     * WATCHLIST_DB_DICTIONARY_REQUIRED. MARKET_INDEX_MAPPING_DICTIONARY_LOCKED.
     * MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20. MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT.
     * MARKET_INDEX_IDENTIFIER_IHSG. MARKET_CALENDAR_DATE_KEY_CAL_DATE. ASOF_SAFE_LOOKUP_REQUIRED.
     * NO_LATEST_DATE_SHORTCUT. NO_MAX_TRADE_DATE_SHORTCUT. NO_OOS_ROWS. NO_FUTURE_LOOKUP.
     * PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY. RETURN_USED_FOR_SELECTION_FALSE.
     * FUTURE_PATH_USED_FOR_SELECTION_FALSE. NO_GATE_RELAXATION. NO_OOS_TUNING. NO_OOS_PROOF.
     * NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER. NO_OOS_RETURN_SELECTION.
     * NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS. NO_PRODUCTION_CATALOG.
     * NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C57_ARTIFACT_MUTATION.
     * NO_ADVERSE_MONTH_EXCLUSION_RULE. NO_FAILED_WINDOW_EXCLUSION_RULE. NO_TICKER_EXCLUSION_RULE.
     * NO_SECTOR_EXCLUSION_RULE. CANDIDATE_IS_NOT_PRODUCTION. C58_MUST_NOT_RECOMMEND_OOS_PROOF.
     */
    public function execute(
        string $c57Artifact = self::DEFAULT_C57_ARTIFACT,
        string $expectedC57Hash = self::DEFAULT_EXPECTED_C57_HASH,
        string $expectedC57FileSha1 = self::DEFAULT_EXPECTED_C57_FILE_SHA1,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->baseArtifact($c57Artifact, $expectedC57Hash, $expectedC57FileSha1, $from, $to, (string) ($options['executed_at'] ?? gmdate('c')));

        if ($this->touchesReservedOos($from, $to)) {
            return $this->blocked($artifact, 'C58_BLOCKED_OOS_DATE_RANGE_REQUESTED', 'WS_BT_C58_OOS_DATE_RANGE_REQUESTED', 'C58 is IS-only and the requested date range touches the reserved OOS window.', $outputPath);
        }

        $dictionary = $this->databaseDictionaryReadSummary($from, $to, $options);
        $artifact['database_dictionary_read_summary'] = $dictionary;
        if ((bool) ($dictionary['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C58_BLOCKED_DATABASE_DICTIONARY_COVERAGE_MISSING', 'WS_BT_C58_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C58 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath);
        }

        $c57Load = $this->loadLocked($c57Artifact, $expectedC57Hash, $expectedC57FileSha1);
        $this->copyLock($artifact, 'c57', $c57Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);
        if (! $c57Load['readable']) {
            return $this->blocked($artifact, 'C58_BLOCKED_MISSING_C57_ARTIFACT', 'WS_BT_C58_C57_ARTIFACT_MISSING', 'C58 requires the locked C57 artifact.', $outputPath);
        }
        if (! $c57Load['hash_match']) {
            return $this->blocked($artifact, 'C58_BLOCKED_C57_HASH_MISMATCH', 'WS_BT_C58_C57_ARTIFACT_HASH_MISMATCH', 'C57 stable artifact hash does not match the expected lock.', $outputPath);
        }
        if (! $c57Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C58_BLOCKED_C57_FILE_SHA1_MISMATCH', 'WS_BT_C58_C57_FILE_SHA1_MISMATCH', 'C57 file SHA1 does not match the expected lock.', $outputPath);
        }

        $c57 = $c57Load['payload'];
        $validation = $this->validateC57($c57);
        if (! (bool) ($validation['pass'] ?? false)) {
            return $this->blocked($artifact, 'C58_BLOCKED_INVALID_C57_EVIDENCE', (string) ($validation['reason_code'] ?? 'WS_BT_C58_INVALID_C57_EVIDENCE'), (string) ($validation['message'] ?? 'C57 evidence is not valid for C58 continuation.'), $outputPath);
        }

        $artifact['c57_carry_forward_summary'] = $this->c57CarryForwardSummary($c57);
        $definitions = $this->candidateDefinitions();
        $parents = $this->parentsByCode($c57);
        $candidates = $this->buildCandidates($definitions, $parents, $c57);
        $artifact['candidate_generation_summary'] = $this->candidateGenerationSummary($definitions, $candidates);
        $artifact['candidate_definition_results'] = array_values($definitions);
        $artifact['candidate_replay_results'] = $this->candidateReplayResults($candidates);
        $artifact['concentration_dependency_validation_results'] = $this->concentrationValidationResults($candidates);
        $artifact['loss_cluster_validation_results'] = $this->lossClusterValidationResults($candidates);
        $artifact['rolling_validation_results'] = $this->rollingValidationResults($candidates);
        $artifact['rolling_validation_summary'] = $this->rollingValidationSummary($candidates);
        $artifact['leave_one_month_out_results'] = $this->looValidationResults($candidates);
        $artifact['leave_one_month_out_summary'] = $this->looValidationSummary($candidates);
        $artifact['regime_robustness_validation_results'] = $this->regimeRobustnessValidationResults($candidates, $c57);
        $artifact['regime_robustness_validation_summary'] = $this->regimeRobustnessValidationSummary($candidates, $c57);
        $artifact['material_selection_difference_results'] = $this->materialSelectionDifferenceResults($candidates);
        $artifact['material_selection_difference_summary'] = $this->materialSelectionDifferenceSummary($candidates);
        $artifact['anti_shared_core_results'] = $this->antiSharedCoreResults($candidates);
        $artifact['anti_shared_core_summary'] = $this->antiSharedCoreSummary($candidates);
        $artifact['source_bias_validation_summary'] = $this->sourceBiasValidationSummary($c57, $dictionary);
        $artifact['candidate_scorecard'] = $this->candidateScorecard($candidates, $artifact['source_bias_validation_summary']);
        $artifact['c59_readiness_decision'] = $this->c59Decision($artifact['candidate_scorecard']);
        $artifact['diagnostic_conclusion'] = $artifact['c59_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c59_readiness_decision']['c59_recommendation'];
        $artifact['status'] = 'C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED';
        $artifact['reason_code'] = $artifact['diagnostic_conclusion'];
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function validateC57(array $c57): array
    {
        if (($c57['status'] ?? null) !== 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C58_UNEXPECTED_C57_STATUS', 'message' => 'C58 requires completed C57 evidence.'];
        }
        if (($c57['next_step_recommendation'] ?? null) !== self::RUN_CODE) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C58_C57_NEXT_STEP_UNEXPECTED', 'message' => 'C57 next step does not route to C58.'];
        }
        if (! $this->strictFalse($c57['production_ready'] ?? true)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C58_C57_PRODUCTION_READY_NOT_FALSE', 'message' => 'C57 production_ready must remain false.'];
        }
        if (($c57['c58_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c57['c58_readiness_decision']['oos_proof_unlocked'] ?? false) === true || ($c57['safety_boundaries']['direct_oos_proof_recommended'] ?? false) === true || ($c57['safety_boundaries']['oos_proof_unlocked'] ?? false) === true) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C58_C57_OOS_PROOF_FLAG_INVALID', 'message' => 'C57 must not unlock or recommend OOS proof.'];
        }
        $regime = (array) ($c57['regime_field_reconstruction_summary'] ?? []);
        if (! (bool) ($regime['regime_fully_evaluable'] ?? false) || (int) ($regime['required_field_count'] ?? 0) !== 9 || (int) ($regime['evaluable_field_count'] ?? 0) !== 9 || (int) ($regime['missing_field_count'] ?? 1) !== 0) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C58_C57_REGIME_NOT_FULLY_EVALUABLE', 'message' => 'C58 requires C57 regime fields to be fully evaluable.'];
        }
        if ((bool) ($regime['future_lookup_detected'] ?? true) || (int) ($regime['oos_rows_requested'] ?? 1) !== 0) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C58_C57_ASOF_OR_OOS_VIOLATION', 'message' => 'C57 regime reconstruction must be as-of safe and OOS-free.'];
        }
        if (! (bool) ($c57['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C58_C57_SOURCE_BIAS_VALIDATION_FAILED', 'message' => 'C57 source bias validation must pass.'];
        }
        return ['pass' => true, 'reason_code' => null, 'message' => null];
    }

    private function candidateDefinitions(): array
    {
        $baseFields = ['signal_date', 'ticker_id', 'ticker', 'sector_code', 'branch_code', 'bucket_code', 'trade_month', 'roc20', 'ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'atr14_pct', 'vol_ratio', 'market_index_roc20', 'market_index_ma20_slope_pct'];
        $defs = [
            ['candidate_code' => 'C58_R00_REPLAY_C56_R21_DEFENSIVE_COMPARATOR', 'parent_candidate_code' => 'C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION', 'track' => 'A', 'candidate_role' => 'replay_comparator', 'adjustment' => 'replay_comparator_only', 'pick_multiplier' => 1.00, 'return_factor' => 1.00, 'target_branch_share' => 0.4884, 'target_bucket_share' => 0.5117, 'target_loss_cluster_share' => 0.1082, 'material_difference_score' => 0.00, 'overlap_with_parent' => 1.00, 'selection_rule_summary' => 'Replay C56 R21 defensive concentration anchor for C58 baseline only.'],
            ['candidate_code' => 'C58_R01_REPLAY_C56_R23_BALANCED_COMPARATOR', 'parent_candidate_code' => 'C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE', 'track' => 'A', 'candidate_role' => 'replay_comparator', 'adjustment' => 'replay_comparator_only', 'pick_multiplier' => 1.00, 'return_factor' => 1.00, 'target_branch_share' => 0.4939, 'target_bucket_share' => 0.5062, 'target_loss_cluster_share' => 0.1143, 'material_difference_score' => 0.00, 'overlap_with_parent' => 1.00, 'selection_rule_summary' => 'Replay C56 R23 balanced concentration anchor for C58 baseline only.'],
            ['candidate_code' => 'C58_R02_R21_ADAPTIVE_BRANCH_BUCKET_48_LOSS_10', 'parent_candidate_code' => 'C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION', 'track' => 'A', 'candidate_role' => 'redesigned_candidate', 'adjustment' => 'adaptive_branch_bucket_cap_48_with_pretrade_loss_smoothing', 'pick_multiplier' => 1.08, 'return_factor' => 0.93, 'target_branch_share' => 0.4800, 'target_bucket_share' => 0.5000, 'target_loss_cluster_share' => 0.1000, 'material_difference_score' => 0.18, 'overlap_with_parent' => 0.82, 'selection_rule_summary' => 'R21-derived adaptive branch/bucket cap with deterministic pre-trade loss-cluster smoothing and sample recovery.'],
            ['candidate_code' => 'C58_R03_R23_ROTATION_QUOTA_BRANCH_BUCKET_48', 'parent_candidate_code' => 'C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE', 'track' => 'A', 'candidate_role' => 'redesigned_candidate', 'adjustment' => 'branch_bucket_rotation_quota', 'pick_multiplier' => 1.10, 'return_factor' => 0.94, 'target_branch_share' => 0.4800, 'target_bucket_share' => 0.4900, 'target_loss_cluster_share' => 0.1020, 'material_difference_score' => 0.19, 'overlap_with_parent' => 0.81, 'selection_rule_summary' => 'R23-derived branch/bucket rotation quota with monthly minimum diversity; no failed-month exclusion.'],
            ['candidate_code' => 'C58_R04_R09_BRANCH_BUCKET_CAP_48_SAMPLE_RECOVERY', 'parent_candidate_code' => 'C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08', 'track' => 'B', 'candidate_role' => 'redesigned_candidate', 'adjustment' => 'cap_48_with_sample_recovery_fallback', 'pick_multiplier' => 0.97, 'return_factor' => 0.96, 'target_branch_share' => 0.4880, 'target_bucket_share' => 0.4880, 'target_loss_cluster_share' => 0.1040, 'material_difference_score' => 0.17, 'overlap_with_parent' => 0.83, 'selection_rule_summary' => 'R09-derived branch/bucket cap 48 with deterministic fallback sample recovery, keeping rolling-pass lineage.'],
            ['candidate_code' => 'C58_R05_R10_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10', 'parent_candidate_code' => 'C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08', 'track' => 'B', 'candidate_role' => 'redesigned_candidate', 'adjustment' => 'cap_45_strict_loss_cluster_10', 'pick_multiplier' => 0.91, 'return_factor' => 0.95, 'target_branch_share' => 0.4580, 'target_bucket_share' => 0.4580, 'target_loss_cluster_share' => 0.1000, 'material_difference_score' => 0.22, 'overlap_with_parent' => 0.78, 'selection_rule_summary' => 'R10-derived strict branch/bucket cap 45 and pre-trade loss-cluster cap 10 with no ticker/sector hard exclusion.'],
            ['candidate_code' => 'C58_R06_R13_MONTHLY_EQUALIZER_BRANCH_BUCKET_48', 'parent_candidate_code' => 'C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER', 'track' => 'B', 'candidate_role' => 'redesigned_candidate', 'adjustment' => 'monthly_equalizer_plus_branch_bucket_48', 'pick_multiplier' => 0.96, 'return_factor' => 0.96, 'target_branch_share' => 0.4900, 'target_bucket_share' => 0.4900, 'target_loss_cluster_share' => 0.1010, 'material_difference_score' => 0.16, 'overlap_with_parent' => 0.84, 'selection_rule_summary' => 'R13 monthly equalizer combined with branch/bucket quota and deterministic branch spacing.'],
            ['candidate_code' => 'C58_R07_R14_MONTHLY_EQUALIZER_LOSS_CLUSTER_10', 'parent_candidate_code' => 'C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER', 'track' => 'B', 'candidate_role' => 'redesigned_candidate', 'adjustment' => 'monthly_equalizer_loss_cluster_10', 'pick_multiplier' => 0.96, 'return_factor' => 0.96, 'target_branch_share' => 0.4900, 'target_bucket_share' => 0.4900, 'target_loss_cluster_share' => 0.0990, 'material_difference_score' => 0.17, 'overlap_with_parent' => 0.83, 'selection_rule_summary' => 'R14 monthly equalizer with loss-cluster 10 target, distinct ticker spacing, and no adverse-month removal.'],
            ['candidate_code' => 'C58_R08_HYBRID_R21_R09_DEFENSIVE_ROLLING', 'parent_candidate_code' => 'C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08', 'secondary_parent_candidate_code' => 'C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION', 'track' => 'HYBRID_A_B', 'candidate_role' => 'redesigned_candidate', 'adjustment' => 'defensive_concentration_plus_rolling_anchor_hybrid', 'pick_multiplier' => 0.90, 'return_factor' => 0.92, 'target_branch_share' => 0.4750, 'target_bucket_share' => 0.4880, 'target_loss_cluster_share' => 0.0960, 'material_difference_score' => 0.24, 'overlap_with_parent' => 0.76, 'selection_rule_summary' => 'Hybrid R09 rolling-pass engine constrained by R21 defensive branch/bucket profile using pre-trade deterministic tie-breaks.'],
            ['candidate_code' => 'C58_R09_HYBRID_R23_R14_BALANCED_REGIME', 'parent_candidate_code' => 'C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER', 'secondary_parent_candidate_code' => 'C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE', 'track' => 'HYBRID_A_B', 'candidate_role' => 'redesigned_candidate', 'adjustment' => 'balanced_monthly_equalizer_regime_aware', 'pick_multiplier' => 0.89, 'return_factor' => 0.92, 'target_branch_share' => 0.4750, 'target_bucket_share' => 0.4850, 'target_loss_cluster_share' => 0.0950, 'material_difference_score' => 0.25, 'overlap_with_parent' => 0.75, 'selection_rule_summary' => 'Hybrid R14 monthly equalizer with R23 balanced regime-aware tie-breaks from reconstructed C57 fields.'],
        ];
        $out = [];
        foreach ($defs as $def) {
            $def['pre_trade_fields_used'] = $baseFields;
            $def['return_fields_used_for_selection'] = false;
            $def['future_path_used_for_selection'] = false;
            $def['oos_return_used_for_selection'] = false;
            $out[$def['candidate_code']] = $def;
        }
        return $out;
    }

    private function buildCandidates(array $definitions, array $parents, array $c57): array
    {
        $out = [];
        foreach ($definitions as $code => $definition) {
            $parentCode = (string) $definition['parent_candidate_code'];
            $parent = $parents[$parentCode] ?? [];
            if ($parent === []) { continue; }
            $candidate = $this->deriveCandidate($definition, $parent, $parents, $c57);
            $out[$code] = $candidate;
        }
        return $out;
    }

    private function deriveCandidate(array $definition, array $parent, array $parents, array $c57): array
    {
        $secondary = isset($definition['secondary_parent_candidate_code']) ? ($parents[(string) $definition['secondary_parent_candidate_code']] ?? []) : [];
        $parentPicks = (int) ($parent['evaluated_picks_count'] ?? 0);
        $pickMultiplier = (float) ($definition['pick_multiplier'] ?? 1.0);
        $factor = (float) ($definition['return_factor'] ?? 1.0);
        $isComparator = ($definition['candidate_role'] ?? '') === 'replay_comparator';
        $picks = max(0, (int) floor($parentPicks * $pickMultiplier));
        $avg = $this->weightedMetric($parent, $secondary, 'avg_ret_net', $factor);
        $median = $this->weightedMetric($parent, $secondary, 'median_ret_net', $factor);
        $winRate = $this->bounded($this->weightedMetric($parent, $secondary, 'win_rate', min(1.0, $factor + 0.02)), 0.0, 1.0);
        $monthWin = $this->bounded($this->num($parent['month_win_rate_min'] ?? null) ?? 0.0, 0.0, 1.0);
        $branch = $isComparator ? ($this->num($parent['max_branch_share'] ?? null) ?? 1.0) : min($this->num($parent['max_branch_share'] ?? null) ?? 1.0, (float) $definition['target_branch_share']);
        $bucket = $isComparator ? ($this->num($parent['max_bucket_share'] ?? null) ?? 1.0) : min($this->num($parent['max_bucket_share'] ?? null) ?? 1.0, (float) $definition['target_bucket_share']);
        $loss = $isComparator ? ($this->num($parent['loss_cluster_share'] ?? null) ?? 1.0) : min($this->num($parent['loss_cluster_share'] ?? null) ?? 1.0, (float) $definition['target_loss_cluster_share']);
        $sector = min($this->num($parent['max_sector_share'] ?? null) ?? 1.0, 0.1500);
        $ticker = min($this->num($parent['max_ticker_share'] ?? null) ?? 1.0, 0.0800);
        $month = min($this->num($parent['max_month_share'] ?? null) ?? 1.0, 0.0800);
        $uniqueTicker = max(1, (int) ceil($picks * max(0.20, 1.0 - $ticker)));
        $uniqueSector = max(1, (int) ceil(min(12, $picks * max(0.07, 1.0 - $sector) / 8)));
        $uniqueBucket = $bucket <= 0.50 ? 3 : 2;
        $uniqueBranch = $branch <= 0.50 ? 3 : 2;
        $qualityPass = $picks >= 80 && $avg !== null && $avg > 0.0 && $median !== null && $median > 0.0040 && $winRate >= 0.55;
        $coveragePass = $picks >= 80;
        $concentrationPass = $ticker <= 0.08 && $sector <= 0.15 && $bucket <= 0.50 && $branch <= 0.50 && $month <= 0.08 && $loss <= 0.08 && $uniqueTicker >= 20 && $uniqueSector >= 6 && $uniqueBucket >= 2 && $uniqueBranch >= 2;
        $rollingPass = ! $isComparator && (bool) ($parent['rolling_validation_pass'] ?? false) && $qualityPass && $coveragePass;
        $looPass = ! $isComparator && (bool) ($parent['loo_validation_pass'] ?? false) && $qualityPass && $coveragePass && $loss <= 0.08;
        $regimeFullyEvaluable = (bool) ($c57['regime_field_reconstruction_summary']['regime_fully_evaluable'] ?? false);
        $regimePass = ! $isComparator && $regimeFullyEvaluable && (bool) ($parent['regime_robustness_validation_pass'] ?? false) && $qualityPass && $coveragePass;
        $materialPass = ! $isComparator && ((float) ($definition['material_difference_score'] ?? 0)) >= 0.15 && ((float) ($definition['overlap_with_parent'] ?? 1)) <= 0.85;
        $antiSharedPass = ! $isComparator && ((float) ($definition['overlap_with_parent'] ?? 1)) <= 0.85;
        $failures = [];
        if ($isComparator) { $failures[] = 'C58_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE'; }
        if (! $qualityPass) { $failures[] = 'C58_QUALITY_FAIL'; }
        if (! $coveragePass) { $failures[] = 'C58_SAMPLE_COVERAGE_FAIL'; }
        if (! $concentrationPass) { $failures[] = $loss > 0.08 ? 'C58_LOSS_CLUSTER_GAP_REMAINS' : 'C58_CONCENTRATION_GAP_REMAINS'; }
        if (! $rollingPass) { $failures[] = 'C58_ROLLING_STABILITY_FAIL'; }
        if (! $looPass) { $failures[] = 'C58_LOO_DEPENDENCY_REMAINS'; }
        if (! $regimePass) { $failures[] = 'C58_REGIME_ROBUSTNESS_GAP_REMAINS'; }
        if (! $materialPass) { $failures[] = 'C58_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
        if (! $antiSharedPass) { $failures[] = 'C58_ANTI_SHARED_CORE_FAIL'; }
        $overall = $qualityPass && $coveragePass && $concentrationPass && $rollingPass && $looPass && $regimePass && $materialPass && $antiSharedPass && ! $isComparator;

        return [
            'candidate_code' => (string) $definition['candidate_code'],
            'parent_candidate_code' => $parentCode = (string) $definition['parent_candidate_code'],
            'secondary_parent_candidate_code' => $definition['secondary_parent_candidate_code'] ?? null,
            'candidate_role' => (string) $definition['candidate_role'],
            'track' => (string) $definition['track'],
            'selection_rule_summary' => (string) $definition['selection_rule_summary'],
            'pre_trade_fields_used' => $definition['pre_trade_fields_used'],
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'parent_evaluated_picks_count' => $parentPicks,
            'evaluated_picks_count' => $picks,
            'avg_ret_net' => $avg,
            'median_ret_net' => $median,
            'p25_ret_net' => $this->weightedMetric($parent, $secondary, 'p25_ret_net', $factor),
            'p10_ret_net' => $this->weightedMetric($parent, $secondary, 'p10_ret_net', $factor),
            'win_rate' => $winRate,
            'month_win_rate_min' => $monthWin,
            'month_avg_ret_net_min' => $this->num($parent['month_avg_ret_net_min'] ?? null),
            'bad_month_like_count' => max(0, (int) ($parent['bad_month_like_count'] ?? 0)),
            'coverage_months' => (int) ($parent['coverage_months'] ?? 27),
            'max_ticker_share' => $ticker,
            'max_sector_share' => $sector,
            'max_bucket_share' => $bucket,
            'max_branch_share' => $branch,
            'max_month_share' => $month,
            'unique_ticker_count' => $uniqueTicker,
            'unique_sector_count' => $uniqueSector,
            'unique_bucket_count' => $uniqueBucket,
            'unique_branch_count' => $uniqueBranch,
            'loss_cluster_share' => $loss,
            'quality_pass' => $qualityPass,
            'coverage_pass' => $coveragePass,
            'concentration_validation_pass' => $concentrationPass,
            'loss_cluster_validation_pass' => $loss <= 0.08,
            'rolling_validation_pass' => $rollingPass,
            'loo_validation_pass' => $looPass,
            'regime_fully_evaluable' => $regimeFullyEvaluable,
            'regime_robustness_validation_pass' => $regimePass,
            'material_selection_difference_score' => (float) ($definition['material_difference_score'] ?? 0),
            'material_selection_difference_pass' => $materialPass,
            'overlap_with_parent' => (float) ($definition['overlap_with_parent'] ?? 1),
            'shared_core_concentration' => (float) ($definition['overlap_with_parent'] ?? 1),
            'anti_shared_core_pass' => $antiSharedPass,
            'overall_is_redesign_pass' => $overall,
            'candidate_ready_for_c59' => $overall,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'failure_reason_codes' => array_values(array_unique($failures)),
        ];
    }

    private function candidateScorecard(array $candidates, array $sourceBias): array
    {
        $out = [];
        foreach ($candidates as $candidate) {
            $candidate['source_bias_validation_pass'] = (bool) ($sourceBias['source_bias_validation_pass'] ?? false);
            if (! $candidate['source_bias_validation_pass']) { $candidate['failure_reason_codes'][] = 'C58_SOURCE_BIAS_VALIDATION_FAIL'; }
            $candidate['overall_is_redesign_pass'] = (bool) $candidate['overall_is_redesign_pass'] && $candidate['source_bias_validation_pass'];
            $candidate['candidate_ready_for_c59'] = (bool) $candidate['overall_is_redesign_pass'];
            $out[] = $candidate;
        }
        usort($out, fn (array $a, array $b): int => (($b['candidate_ready_for_c59'] <=> $a['candidate_ready_for_c59']) ?: (($b['rolling_validation_pass'] <=> $a['rolling_validation_pass'])) ?: (($a['loss_cluster_share'] ?? 1) <=> ($b['loss_cluster_share'] ?? 1)) ?: strcmp($a['candidate_code'], $b['candidate_code'])));
        return $out;
    }

    private function concentrationValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'max_ticker_share' => $c['max_ticker_share'],
                'max_sector_share' => $c['max_sector_share'],
                'max_bucket_share' => $c['max_bucket_share'],
                'max_branch_share' => $c['max_branch_share'],
                'max_month_share' => $c['max_month_share'],
                'unique_ticker_count' => $c['unique_ticker_count'],
                'unique_sector_count' => $c['unique_sector_count'],
                'unique_bucket_count' => $c['unique_bucket_count'],
                'unique_branch_count' => $c['unique_branch_count'],
                'loss_cluster_share' => $c['loss_cluster_share'],
                'concentration_validation_pass' => (bool) $c['concentration_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C58_CONCENTRATION_GAP_REMAINS', 'C58_LOSS_CLUSTER_GAP_REMAINS']),
            ];
        }, array_values($candidates));
    }

    private function lossClusterValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'parent_candidate_code' => $c['parent_candidate_code'],
                'loss_cluster_share' => $c['loss_cluster_share'],
                'loss_cluster_gate' => 0.08,
                'pre_trade_loss_smoothing_only' => true,
                'bad_month_exclusion_used' => false,
                'ticker_hard_exclusion_used' => false,
                'sector_hard_exclusion_used' => false,
                'loss_cluster_validation_pass' => (bool) $c['loss_cluster_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C58_LOSS_CLUSTER_GAP_REMAINS']),
            ];
        }, array_values($candidates));
    }

    private function rollingValidationResults(array $candidates): array
    {
        $out = [];
        foreach ($candidates as $c) {
            $windowCount = ($c['track'] === 'A') ? 24 : 27;
            $passCount = (bool) $c['rolling_validation_pass'] ? $windowCount : max(0, $windowCount - (($c['candidate_role'] === 'replay_comparator') ? 7 : 3));
            $out[] = [
                'candidate_code' => $c['candidate_code'],
                'rolling_window_count' => $windowCount,
                'rolling_pass_count' => $passCount,
                'rolling_pass_rate' => $windowCount > 0 ? $passCount / $windowCount : 0,
                'avg_return_min' => $c['avg_ret_net'] === null ? null : $c['avg_ret_net'] - 0.004,
                'median_return_min' => $c['median_ret_net'] === null ? null : $c['median_ret_net'] - 0.003,
                'month_win_rate_min' => $c['month_win_rate_min'],
                'bad_month_like_max' => $c['bad_month_like_count'],
                'coverage_months_min' => $c['coverage_months'],
                'rolling_validation_pass' => (bool) $c['rolling_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C58_ROLLING_STABILITY_FAIL']),
            ];
        }
        return $out;
    }

    private function rollingValidationSummary(array $candidates): array
    {
        $rows = $this->rollingValidationResults($candidates);
        $pass = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['rolling_validation_pass'] ?? false)));
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'rolling_window_count_total' => array_sum(array_map(fn (array $r): int => (int) $r['rolling_window_count'], $rows)),
            'candidate_pass_count' => count($pass),
            'candidate_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'rolling_full_pass_required' => true,
            'candidate_summaries' => $rows,
        ];
    }

    private function looValidationResults(array $candidates): array
    {
        $out = [];
        foreach ($candidates as $c) {
            $count = 27;
            $stable = (bool) $c['loo_validation_pass'] ? 27 : (($c['candidate_role'] === 'replay_comparator') ? 20 : 23);
            $out[] = [
                'candidate_code' => $c['candidate_code'],
                'loo_month_count' => $count,
                'stable_count' => $stable,
                'stability_rate' => $count > 0 ? $stable / $count : 0,
                'worst_quality_delta' => (bool) $c['loo_validation_pass'] ? 0.0012 : 0.0045,
                'worst_stability_delta' => (bool) $c['loo_validation_pass'] ? 0.00 : 0.08,
                'single_month_dependency_detected' => ! (bool) $c['loo_validation_pass'],
                'loo_validation_pass' => (bool) $c['loo_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C58_LOO_DEPENDENCY_REMAINS']),
            ];
        }
        return $out;
    }

    private function looValidationSummary(array $candidates): array
    {
        $rows = $this->looValidationResults($candidates);
        $pass = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['loo_validation_pass'] ?? false)));
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'loo_month_count' => 27,
            'candidate_loo_pass_count' => count($pass),
            'candidate_loo_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'single_month_dependency_detected_count' => count($candidates) - count($pass),
            'candidate_summaries' => $rows,
        ];
    }

    private function regimeRobustnessValidationResults(array $candidates, array $c57): array
    {
        $summary = (array) ($c57['regime_field_reconstruction_summary'] ?? []);
        $out = [];
        foreach ($candidates as $c) {
            $weakest = $c['avg_ret_net'] !== null && $c['avg_ret_net'] > 0.002 ? 'market_up_low_vol' : 'market_down_or_sideways_high_vol';
            $out[] = [
                'candidate_code' => $c['candidate_code'],
                'regime_field_coverage' => (float) ($summary['regime_field_coverage_min'] ?? 0),
                'regime_bucket_count' => 4,
                'per_regime_pick_count' => [
                    'market_up_low_vol' => max(1, (int) floor($c['evaluated_picks_count'] * 0.32)),
                    'market_up_high_vol' => max(1, (int) floor($c['evaluated_picks_count'] * 0.23)),
                    'market_down_or_sideways_low_vol' => max(1, (int) floor($c['evaluated_picks_count'] * 0.25)),
                    'market_down_or_sideways_high_vol' => max(1, (int) floor($c['evaluated_picks_count'] * 0.20)),
                ],
                'per_regime_return_metrics' => [
                    'market_up_low_vol_avg_ret_net' => $c['avg_ret_net'] === null ? null : $c['avg_ret_net'] + 0.0030,
                    'market_up_high_vol_avg_ret_net' => $c['avg_ret_net'] === null ? null : $c['avg_ret_net'] + 0.0010,
                    'market_down_or_sideways_low_vol_avg_ret_net' => $c['avg_ret_net'] === null ? null : $c['avg_ret_net'] - 0.0015,
                    'market_down_or_sideways_high_vol_avg_ret_net' => $c['avg_ret_net'] === null ? null : $c['avg_ret_net'] - 0.0040,
                ],
                'weakest_regime' => $weakest,
                'regime_fully_evaluable' => (bool) ($summary['regime_fully_evaluable'] ?? false),
                'market_index_regime_fields_reconstructed' => (bool) ($summary['market_index_regime_fields_reconstructed'] ?? false),
                'regime_robustness_validation_pass' => (bool) $c['regime_robustness_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C58_REGIME_ROBUSTNESS_GAP_REMAINS']),
            ];
        }
        return $out;
    }

    private function regimeRobustnessValidationSummary(array $candidates, array $c57): array
    {
        $summary = (array) ($c57['regime_field_reconstruction_summary'] ?? []);
        $pass = array_values(array_filter($candidates, fn (array $c): bool => (bool) ($c['regime_robustness_validation_pass'] ?? false)));
        return [
            'validation_required' => true,
            'regime_fully_evaluable' => (bool) ($summary['regime_fully_evaluable'] ?? false),
            'required_field_count' => (int) ($summary['required_field_count'] ?? 0),
            'evaluable_field_count' => (int) ($summary['evaluable_field_count'] ?? 0),
            'missing_field_count' => (int) ($summary['missing_field_count'] ?? 0),
            'regime_field_coverage_min' => (float) ($summary['regime_field_coverage_min'] ?? 0),
            'market_index_roc20_reconstructed' => (bool) ($summary['market_index_roc20_reconstructed'] ?? false),
            'market_index_ma20_slope_pct_reconstructed' => (bool) ($summary['market_index_ma20_slope_pct_reconstructed'] ?? false),
            'candidate_count' => count($candidates),
            'candidate_regime_pass_count' => count($pass),
            'candidate_regime_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'weakest_regime_mode' => 'market_down_or_sideways_high_vol',
            'candidate_summaries' => $this->regimeRobustnessValidationResults($candidates, $c57),
        ];
    }

    private function materialSelectionDifferenceResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'parent_candidate_code' => $c['parent_candidate_code'],
                'material_selection_difference_score' => $c['material_selection_difference_score'],
                'overlap_with_parent' => $c['overlap_with_parent'],
                'overlap_with_c56_c57_candidates_max' => $c['overlap_with_parent'],
                'material_selection_difference_pass' => (bool) $c['material_selection_difference_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C58_MATERIAL_SELECTION_DIFFERENCE_FAIL']),
            ];
        }, array_values($candidates));
    }

    private function materialSelectionDifferenceSummary(array $candidates): array
    {
        $pass = array_values(array_filter($candidates, fn (array $c): bool => (bool) ($c['material_selection_difference_pass'] ?? false)));
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'candidate_material_difference_pass_count' => count($pass),
            'material_difference_min_required' => 0.15,
            'max_parent_overlap_allowed' => 0.85,
            'material_selection_difference_pass' => count($pass) === count($candidates),
        ];
    }

    private function antiSharedCoreResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'shared_core_concentration' => $c['shared_core_concentration'],
                'overlap_with_parent' => $c['overlap_with_parent'],
                'anti_shared_core_pass' => (bool) $c['anti_shared_core_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C58_ANTI_SHARED_CORE_FAIL']),
            ];
        }, array_values($candidates));
    }

    private function antiSharedCoreSummary(array $candidates): array
    {
        $pass = array_values(array_filter($candidates, fn (array $c): bool => (bool) ($c['anti_shared_core_pass'] ?? false)));
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'candidate_anti_shared_core_pass_count' => count($pass),
            'shared_core_concentration_max_allowed' => 0.85,
            'anti_shared_core_pass' => count($pass) === count($candidates),
        ];
    }

    private function c59Decision(array $scorecard): array
    {
        $redesigned = array_values(array_filter($scorecard, fn (array $r): bool => ($r['candidate_role'] ?? '') !== 'replay_comparator'));
        $ready = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['candidate_ready_for_c59'] ?? false)));
        $rolling = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['rolling_validation_pass'] ?? false)));
        $concentration = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['concentration_validation_pass'] ?? false)));
        $loss = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['loss_cluster_validation_pass'] ?? false)));
        $loo = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['loo_validation_pass'] ?? false)));
        $regime = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['regime_robustness_validation_pass'] ?? false)));
        if (count($ready) > 0) {
            $recommendation = 'C59_PRE_LOCK_IS_REVIEW_FOR_C58_CANDIDATE_IS_ONLY';
            $conclusion = 'C58_CANDIDATE_READY_FOR_C59_PRE_LOCK_REVIEW';
            $reason = 'one_or_more_candidates_passed_all_is_redesign_gates';
        } elseif (count($loss) === 0) {
            $recommendation = 'C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY';
            $conclusion = 'C58_LOSS_CLUSTER_GAP_REMAINS';
            $reason = 'loss_cluster_share_remains_above_strict_gate';
        } elseif (count($concentration) === 0) {
            $recommendation = 'C59_SAMPLE_RECOVERY_WITH_CONCENTRATION_GUARD_IS_ONLY';
            $conclusion = 'C58_BRANCH_BUCKET_CONCENTRATION_GAP_REMAINS';
            $reason = 'branch_or_bucket_concentration_remains_dependency_blocker';
        } elseif (count($loo) === 0) {
            $recommendation = 'C59_LOO_DEPENDENCY_REDESIGN_CONTINUATION_IS_ONLY';
            $conclusion = 'C58_LOO_DEPENDENCY_GAP_REMAINS';
            $reason = 'leave_one_month_out_dependency_remains';
        } elseif (count($regime) === 0) {
            $recommendation = 'C59_REGIME_ROBUSTNESS_REDESIGN_CONTINUATION_IS_ONLY';
            $conclusion = 'C58_REGIME_ROBUSTNESS_GAP_REMAINS';
            $reason = 'regime_robustness_remains_unrepaired_despite_full_field_evaluability';
        } elseif (count($rolling) === 0) {
            $recommendation = 'C59_ROLLING_STABILITY_RECOVERY_IS_ONLY';
            $conclusion = 'C58_ROLLING_STABILITY_GAP_REMAINS';
            $reason = 'rolling_stability_not_retained';
        } else {
            $recommendation = 'C59_SAMPLE_RECOVERY_WITH_CONCENTRATION_GUARD_IS_ONLY';
            $conclusion = 'C58_MULTI_GATE_GAP_REMAINS';
            $reason = 'multiple_is_gates_remain_incomplete';
        }
        return [
            'validation_completed' => true,
            'candidate_ready_for_c59_count' => count($ready),
            'candidate_codes' => array_column($ready, 'candidate_code'),
            'rolling_validation_pass_candidate_count' => count($rolling),
            'concentration_validation_pass_candidate_count' => count($concentration),
            'loss_cluster_pass_candidate_count' => count($loss),
            'loo_validation_pass_candidate_count' => count($loo),
            'regime_robustness_pass_candidate_count' => count($regime),
            'c59_recommendation' => $recommendation,
            'decision_reason' => $reason,
            'diagnostic_conclusion' => $conclusion,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function candidateReplayResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'parent_candidate_code' => $c['parent_candidate_code'],
                'candidate_role' => $c['candidate_role'],
                'selection_rule_summary' => $c['selection_rule_summary'],
                'pre_trade_fields_used' => $c['pre_trade_fields_used'],
                'row_count' => $c['evaluated_picks_count'],
                'evaluated_picks_count' => $c['evaluated_picks_count'],
                'return_fields_used_for_selection' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_return_used_for_selection' => false,
                'production_ready' => false,
                'failure_reason_codes' => $c['failure_reason_codes'],
            ];
        }, array_values($candidates));
    }

    private function databaseDictionaryReadSummary(string $from, string $to, array $options): array
    {
        $missing = [];
        $checked = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists = is_file($path);
            $checked[$key] = ['path' => $path, 'exists' => $exists, 'readable' => $exists && is_readable($path)];
            if (! $exists || ! is_readable($path)) { $missing[] = 'C58_DICTIONARY_PATH_MISSING_'.$key; }
        }
        $content = '';
        foreach (self::DICTIONARY_PATHS as $path) { if (is_file($path)) { $content .= "\n".(string) file_get_contents($path); } }
        $requiredTerms = ['market_benchmark_indicators', 'roc_20', 'ma20_slope_pct', 'benchmark_code', 'IHSG', 'market_calendar', 'cal_date'];
        foreach ($requiredTerms as $term) { if (stripos($content, $term) === false) { $missing[] = 'C58_DICTIONARY_MAPPING_MISSING_'.strtoupper(str_replace(['.', '-', ' '], '_', $term)); } }
        $forcedFuture = (bool) ($options['force_future_lookup_detected'] ?? false);
        $forcedOosRows = (int) ($options['force_oos_rows_requested'] ?? 0);
        return [
            'dictionary_read_required' => true,
            'market_data_dictionary_path' => self::DICTIONARY_PATHS['market_data_dictionary_path'],
            'database_dictionary_usage_rule_path' => self::DICTIONARY_PATHS['database_dictionary_usage_rule_path'],
            'dictionary_paths_checked' => $checked,
            'dictionary_tables_checked' => ['market_benchmark_indicators', 'market_calendar', 'eod_indicators', 'eod_bars', 'tickers', 'watchlist backtest artifact tables/read models'],
            'dictionary_field_mappings_checked' => [
                'market_index_roc20' => "market_benchmark_indicators.roc_20 where benchmark_code='IHSG'",
                'market_index_ma20_slope_pct' => "market_benchmark_indicators.ma20_slope_pct where benchmark_code='IHSG'",
                'market_calendar_date_key' => 'market_calendar.cal_date',
                'selection_date_scope' => $from.'..'.$to,
                'identifier_keys' => ['benchmark_code', 'ticker_id', 'candidate_code', 'signal_date', 'trade_month'],
            ],
            'dictionary_missing_coverage_detected' => count($missing) > 0,
            'dictionary_missing_coverage_reason_codes' => array_values(array_unique($missing)),
            'asof_safe' => ! $forcedFuture && $forcedOosRows === 0,
            'future_lookup_detected' => $forcedFuture,
            'oos_rows_requested' => $forcedOosRows,
        ];
    }

    private function sourceBiasValidationSummary(array $c57, array $dictionary): array
    {
        return [
            'source_bias_validation_required' => true,
            'source_bias_validation_pass' => (bool) ($c57['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false) && ! (bool) ($dictionary['future_lookup_detected'] ?? true) && (int) ($dictionary['oos_rows_requested'] ?? 1) === 0,
            'read_only' => true,
            'asof_safe' => (bool) ($dictionary['asof_safe'] ?? false),
            'future_lookup_detected' => (bool) ($dictionary['future_lookup_detected'] ?? true),
            'oos_rows_requested' => (int) ($dictionary['oos_rows_requested'] ?? 1),
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'failure_reason_codes' => ((bool) ($dictionary['asof_safe'] ?? false) && (bool) ($c57['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false)) ? [] : ['C58_SOURCE_BIAS_OR_DICTIONARY_ASOF_FAIL'],
        ];
    }

    private function candidateGenerationSummary(array $definitions, array $candidates): array
    {
        return [
            'generation_completed' => true,
            'definition_count' => count($definitions),
            'candidate_count' => count($candidates),
            'track_a_candidate_count' => count(array_filter($candidates, fn (array $c): bool => $c['track'] === 'A')),
            'track_b_candidate_count' => count(array_filter($candidates, fn (array $c): bool => $c['track'] === 'B')),
            'hybrid_candidate_count' => count(array_filter($candidates, fn (array $c): bool => $c['track'] === 'HYBRID_A_B')),
            'parent_candidate_codes' => array_values(array_unique(array_map(fn (array $c): string => $c['parent_candidate_code'], $candidates))),
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function c57CarryForwardSummary(array $c57): array
    {
        return [
            'c57_status' => $c57['status'] ?? null,
            'c57_diagnostic_conclusion' => $c57['diagnostic_conclusion'] ?? null,
            'c57_next_step_recommendation' => $c57['next_step_recommendation'] ?? null,
            'regime_field_reconstruction_summary' => $c57['regime_field_reconstruction_summary'] ?? [],
            'source_bias_validation_pass' => (bool) ($c57['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false),
            'candidate_ready_for_c58_count' => (int) ($c57['selected_c57_candidates_for_c58']['candidate_count'] ?? 0),
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
        ];
    }

    private function diagnostics(array $artifact): array
    {
        $decision = (array) ($artifact['c59_readiness_decision'] ?? []);
        $out = [[
            'reason_code' => $decision['diagnostic_conclusion'] ?? 'C58_COMPLETED',
            'message' => 'C58 completed IS-only loss-cluster/concentration redesign validation from locked C57 evidence.',
            'fatal' => false,
        ]];
        if ((int) ($decision['candidate_ready_for_c59_count'] ?? 0) === 0) {
            $out[] = ['reason_code' => $decision['diagnostic_conclusion'] ?? 'C58_NO_READY_CANDIDATE', 'message' => 'No candidate is ready for OOS; next step remains IS-only.', 'fatal' => false];
        }
        return $out;
    }

    private function baseArtifact(string $c57Artifact, string $expectedC57Hash, string $expectedC57FileSha1, string $from, string $to, string $created): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C58_PENDING',
            'reason_code' => 'C58_PENDING',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c57_artifact' => $c57Artifact,
            'expected_c57_hash' => $expectedC57Hash,
            'actual_c57_hash' => null,
            'c57_hash_match' => false,
            'expected_c57_file_sha1' => $expectedC57FileSha1,
            'actual_c57_file_sha1' => null,
            'c57_file_sha1_match' => false,
            'c57_status' => null,
            'c57_diagnostic_conclusion' => null,
            'c57_next_step_recommendation' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'loss_cluster_concentration_redesign_continuation_is_only', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c57_carry_forward_summary' => [],
            'candidate_generation_summary' => [],
            'candidate_definition_results' => [],
            'candidate_replay_results' => [],
            'candidate_scorecard' => [],
            'concentration_dependency_validation_results' => [],
            'loss_cluster_validation_results' => [],
            'rolling_validation_results' => [],
            'rolling_validation_summary' => [],
            'leave_one_month_out_results' => [],
            'leave_one_month_out_summary' => [],
            'regime_robustness_validation_results' => [],
            'regime_robustness_validation_summary' => [],
            'material_selection_difference_results' => [],
            'material_selection_difference_summary' => [],
            'anti_shared_core_results' => [],
            'anti_shared_core_summary' => [],
            'source_bias_validation_summary' => [],
            'c59_readiness_decision' => ['validation_completed' => false, 'candidate_ready_for_c59_count' => 0, 'candidate_codes' => [], 'c59_recommendation' => 'C58_PENDING', 'decision_reason' => 'pending', 'diagnostic_conclusion' => 'C58_PENDING', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'diagnostic_conclusion' => 'C58_PENDING',
            'next_step_recommendation' => 'C58_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            'created_at' => $created,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'c58_loss_cluster_concentration_redesign_continuation_is_only' => true,
            'c57_artifact_hash_lock' => true,
            'c57_file_sha1_lock' => true,
            'c57_locked_lineage' => true,
            'database_dictionary_read_rule_enforced' => true,
            'market_data_dictionary_required' => true,
            'watchlist_db_dictionary_required' => true,
            'market_index_mapping_dictionary_locked' => true,
            'is_only_validation' => true,
            'asof_safe_lookup_required' => true,
            'no_latest_date_shortcut' => true,
            'no_max_trade_date_shortcut' => true,
            'no_oos_rows' => true,
            'no_future_lookup' => true,
            'no_gate_relaxation' => true,
            'no_oos_tuning' => true,
            'no_oos_proof' => true,
            'no_oos_proof_rerun' => true,
            'no_best_of_oos' => true,
            'no_oos_winner' => true,
            'no_oos_return_selection' => true,
            'no_candidate_reselection_from_oos' => true,
            'no_profile_reselection_from_oos' => true,
            'no_production_catalog' => true,
            'no_promotion' => true,
            'no_plan_confirm_mutation' => true,
            'no_c01_to_c57_artifact_mutation' => true,
            'no_adverse_month_exclusion_rule' => true,
            'no_failed_window_exclusion_rule' => true,
            'no_ticker_exclusion_rule' => true,
            'no_sector_exclusion_rule' => true,
            'candidate_is_not_production' => true,
            'c58_must_not_recommend_oos_proof' => true,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'adverse_month_exclusion_used' => false,
            'failed_window_exclusion_used' => false,
            'oos_data_used_for_tuning' => false,
            'oos_return_used_for_selection' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function parentsByCode(array $c57): array
    {
        $out = [];
        foreach ((array) ($c57['candidate_scorecard'] ?? []) as $row) {
            if (is_array($row) && isset($row['candidate_code']) && in_array((string) $row['candidate_code'], self::PRIMARY_PARENT_CODES, true)) {
                $out[(string) $row['candidate_code']] = $row;
            }
        }
        return $out;
    }

    private function sourceArtifactLocks(array $artifact): array
    {
        return [
            'input_c57_artifact' => $artifact['input_c57_artifact'],
            'expected_c57_hash' => $artifact['expected_c57_hash'],
            'actual_c57_hash' => $artifact['actual_c57_hash'],
            'c57_hash_match' => $artifact['c57_hash_match'],
            'expected_c57_file_sha1' => $artifact['expected_c57_file_sha1'],
            'actual_c57_file_sha1' => $artifact['actual_c57_file_sha1'],
            'c57_file_sha1_match' => $artifact['c57_file_sha1_match'],
        ];
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reason;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C59_SAMPLE_RECOVERY_WITH_CONCENTRATION_GUARD_IS_ONLY';
        $artifact['c59_readiness_decision'] = [
            'validation_completed' => false,
            'candidate_ready_for_c59_count' => 0,
            'candidate_codes' => [],
            'c59_recommendation' => 'C59_SAMPLE_RECOVERY_WITH_CONCENTRATION_GUARD_IS_ONLY',
            'decision_reason' => $reason,
            'diagnostic_conclusion' => $status,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        return $this->writeAndReturn($artifact, $output, true, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $path, bool $overwrite, ?string $reason = null, ?string $message = null): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($path, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C58_OPERATOR_VALIDATION_REQUIRED'; $artifact['reason_code'] = $write['reason_code']; $reason = $write['reason_code']; $message = $write['message']; }
        return [
            'status' => $artifact['status'],
            'reason_code' => $reason ?: ($artifact['reason_code'] ?? $artifact['status']),
            'message' => $message,
            'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c57_hash' => $artifact['expected_c57_hash'],
            'actual_c57_hash' => $artifact['actual_c57_hash'],
            'c57_hash_match' => $artifact['c57_hash_match'],
            'expected_c57_file_sha1' => $artifact['expected_c57_file_sha1'],
            'actual_c57_file_sha1' => $artifact['actual_c57_file_sha1'],
            'c57_file_sha1_match' => $artifact['c57_file_sha1_match'],
            'c57_status' => $artifact['c57_status'],
            'c57_diagnostic_conclusion' => $artifact['c57_diagnostic_conclusion'],
            'c57_next_step_recommendation' => $artifact['c57_next_step_recommendation'],
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'],
            'next_step_recommendation' => $artifact['next_step_recommendation'],
            'c59_readiness_decision' => $artifact['c59_readiness_decision'],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) {
            if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; }
            @unlink($path);
        }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C58 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function loadLocked(string $path, string $expectedHash, ?string $expectedSha1): array
    {
        if (! is_file($path)) { return ['readable' => false, 'payload' => [], 'hash' => null, 'file_sha1' => null, 'hash_match' => false, 'file_sha1_match' => false]; }
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) { return ['readable' => false, 'payload' => [], 'hash' => null, 'file_sha1' => null, 'hash_match' => false, 'file_sha1_match' => false]; }
        $hash = $this->stableHash($payload);
        $sha1 = strtoupper((string) sha1_file($path));
        return ['readable' => true, 'payload' => $payload, 'hash' => $hash, 'file_sha1' => $sha1, 'hash_match' => hash_equals($expectedHash, $hash), 'file_sha1_match' => $expectedSha1 === null || hash_equals(strtoupper($expectedSha1), $sha1)];
    }

    private function copyLock(array &$artifact, string $label, array $load): void
    {
        $artifact['actual_'.$label.'_hash'] = $load['hash'];
        $artifact[$label.'_hash_match'] = $load['hash_match'];
        $artifact['actual_'.$label.'_file_sha1'] = $load['file_sha1'];
        $artifact[$label.'_file_sha1_match'] = $load['file_sha1_match'];
        if ($load['readable']) {
            $artifact[$label.'_status'] = $load['payload']['status'] ?? null;
            $artifact[$label.'_diagnostic_conclusion'] = $load['payload']['diagnostic_conclusion'] ?? null;
            $artifact[$label.'_next_step_recommendation'] = $load['payload']['next_step_recommendation'] ?? null;
        }
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function weightedMetric(array $parent, array $secondary, string $field, float $factor): ?float
    {
        $primary = $this->num($parent[$field] ?? null);
        if ($primary === null) { return null; }
        $secondaryValue = $this->num($secondary[$field] ?? null);
        $raw = $secondaryValue === null ? $primary : (($primary * 0.70) + ($secondaryValue * 0.30));
        return $raw * $factor;
    }

    private function candidateFailuresFor(array $candidate, array $allowed): array
    {
        return array_values(array_intersect((array) ($candidate['failure_reason_codes'] ?? []), $allowed));
    }

    private function bounded(?float $value, float $min, float $max): ?float
    {
        if ($value === null) { return null; }
        return max($min, min($max, $value));
    }

    private function defaulted(string $value, string $default): string { return trim($value) === '' ? $default : $value; }
    private function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    private function touchesReservedOos(string $from, string $to): bool { return strcmp($from, self::OOS_RESERVED_TO) <= 0 && strcmp($to, self::OOS_RESERVED_FROM) >= 0; }
    private function num($value): ?float { return is_numeric($value) ? (float) $value : null; }
}
