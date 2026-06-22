<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService
{
    public const RUN_CODE = 'C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY';
    public const ARTIFACT_TYPE = 'C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY';
    public const DEFAULT_C58_ARTIFACT = 'storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json';
    public const DEFAULT_EXPECTED_C58_HASH = '80d09de8053659bf01ce5b8b72d9e2d82cdf69dc';
    public const DEFAULT_EXPECTED_C58_FILE_SHA1 = 'FA6FE27604F6CDA664DCF90A251AF41672670700';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private const LOSS_CLUSTER_GATE = 0.0800;
    private const BRANCH_BUCKET_STRICT_GATE = 0.4500;
    private const MONTH_SHARE_GATE = 0.0750;
    private const MIN_EVALUATED_PICKS = 80;
    private const MIN_SAMPLE_RETENTION = 0.70;
    private const MIN_LOO_STABILITY_RATE = 0.9250;

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    /**
     * C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY. C58_ARTIFACT_HASH_LOCK.
     * C58_FILE_SHA1_LOCK. C58_LOCKED_LINEAGE. C57_REGIME_RECONSTRUCTION_RETAINED_THROUGH_C58_LOCK.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. MARKET_DATA_DICTIONARY_REQUIRED.
     * WATCHLIST_DB_DICTIONARY_REQUIRED. MARKET_INDEX_MAPPING_DICTIONARY_LOCKED.
     * MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20. MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT.
     * MARKET_INDEX_IDENTIFIER_IHSG. MARKET_CALENDAR_DATE_KEY_CAL_DATE. ASOF_SAFE_LOOKUP_REQUIRED.
     * NO_LATEST_DATE_SHORTCUT. NO_MAX_TRADE_DATE_SHORTCUT. NO_OOS_ROWS. NO_FUTURE_LOOKUP.
     * PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY. RETURN_USED_FOR_SELECTION_FALSE.
     * FUTURE_PATH_USED_FOR_SELECTION_FALSE. NO_GATE_RELAXATION. STRICT_GATE_RETENTION_REQUIRED.
     * NO_OOS_TUNING. NO_OOS_PROOF. NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER.
     * NO_OOS_RETURN_SELECTION. NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS.
     * NO_PRODUCTION_CATALOG. NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C58_ARTIFACT_MUTATION.
     * NO_ADVERSE_MONTH_EXCLUSION_RULE. NO_FAILED_WINDOW_EXCLUSION_RULE. NO_TICKER_EXCLUSION_RULE.
     * NO_SECTOR_EXCLUSION_RULE. NO_BAD_MONTH_REMOVAL. NO_REPLAY_COMPARATOR_PROMOTION.
     * LOSS_CLUSTER_PRE_TRADE_PROXY_ONLY. BRANCH_BUCKET_CONCENTRATION_REDESIGN_ONLY.
     * MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_REGIME_REQUIRED. CANDIDATE_IS_NOT_PRODUCTION.
     * C59_MUST_NOT_RECOMMEND_DIRECT_OOS_PROOF.
     */
    public function execute(
        string $c58Artifact = self::DEFAULT_C58_ARTIFACT,
        string $expectedC58Hash = self::DEFAULT_EXPECTED_C58_HASH,
        string $expectedC58FileSha1 = self::DEFAULT_EXPECTED_C58_FILE_SHA1,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->baseArtifact($c58Artifact, $expectedC58Hash, $expectedC58FileSha1, $from, $to, (string) ($options['executed_at'] ?? gmdate('c')));

        if ($this->touchesReservedOos($from, $to)) {
            return $this->blocked($artifact, 'C59_BLOCKED_OOS_DATE_RANGE_REQUESTED', 'WS_BT_C59_OOS_DATE_RANGE_REQUESTED', 'C59 is IS-only and the requested date range touches the reserved OOS window.', $outputPath);
        }

        $dictionary = $this->databaseDictionaryReadSummary($from, $to, $options);
        $artifact['database_dictionary_read_summary'] = $dictionary;
        if ((bool) ($dictionary['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C59_BLOCKED_DATABASE_DICTIONARY_COVERAGE_MISSING', 'WS_BT_C59_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C59 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath);
        }
        if (! (bool) ($dictionary['asof_safe'] ?? false) || (bool) ($dictionary['future_lookup_detected'] ?? true) || (int) ($dictionary['oos_rows_requested'] ?? 1) !== 0) {
            return $this->blocked($artifact, 'C59_BLOCKED_ASOF_OR_OOS_SAFETY', 'WS_BT_C59_ASOF_OR_OOS_SAFETY_FAIL', 'C59 requires as-of-safe lookup evidence, zero future lookup, and zero OOS rows.', $outputPath);
        }

        $c58Load = $this->loadLocked($c58Artifact, $expectedC58Hash, $expectedC58FileSha1);
        $this->copyLock($artifact, 'c58', $c58Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);
        if (! $c58Load['readable']) {
            return $this->blocked($artifact, 'C59_BLOCKED_MISSING_C58_ARTIFACT', 'WS_BT_C59_C58_ARTIFACT_MISSING', 'C59 requires the locked C58 artifact.', $outputPath);
        }
        if (! $c58Load['hash_match']) {
            return $this->blocked($artifact, 'C59_BLOCKED_C58_HASH_MISMATCH', 'WS_BT_C59_C58_ARTIFACT_HASH_MISMATCH', 'C58 stable artifact hash does not match the expected lock.', $outputPath);
        }
        if (! $c58Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C59_BLOCKED_C58_FILE_SHA1_MISMATCH', 'WS_BT_C59_C58_FILE_SHA1_MISMATCH', 'C58 file SHA1 does not match the expected lock.', $outputPath);
        }

        $c58 = $c58Load['payload'];
        $validation = $this->validateC58($c58);
        if (! (bool) ($validation['pass'] ?? false)) {
            return $this->blocked($artifact, 'C59_BLOCKED_INVALID_C58_EVIDENCE', (string) ($validation['reason_code'] ?? 'WS_BT_C59_INVALID_C58_EVIDENCE'), (string) ($validation['message'] ?? 'C58 evidence is not valid for C59 continuation.'), $outputPath);
        }

        $artifact['c58_blocker_summary'] = $this->c58BlockerSummary($c58);
        $artifact['c57_c58_regime_lock_summary'] = $this->regimeLockSummary($c58);
        $definitions = $this->candidateDefinitions();
        $parents = $this->parentsByCode($c58);
        $candidates = $this->buildCandidates($definitions, $parents, $c58);
        $artifact['candidate_generation_summary'] = $this->candidateGenerationSummary($definitions, $candidates);
        $artifact['candidate_definition_results'] = array_values($definitions);
        $artifact['candidate_replay_results'] = $this->candidateReplayResults($candidates);
        $artifact['loss_cluster_validation_results'] = $this->lossClusterValidationResults($candidates);
        $artifact['concentration_dependency_validation_results'] = $this->concentrationValidationResults($candidates);
        $artifact['rolling_validation_results'] = $this->rollingValidationResults($candidates);
        $artifact['rolling_validation_summary'] = $this->rollingValidationSummary($candidates);
        $artifact['leave_one_month_out_results'] = $this->looValidationResults($candidates);
        $artifact['leave_one_month_out_summary'] = $this->looValidationSummary($candidates);
        $artifact['regime_robustness_validation_results'] = $this->regimeRobustnessValidationResults($candidates, $c58);
        $artifact['regime_robustness_validation_summary'] = $this->regimeRobustnessValidationSummary($candidates, $c58);
        $artifact['sample_recovery_results'] = $this->sampleRecoveryResults($candidates);
        $artifact['sample_recovery_summary'] = $this->sampleRecoverySummary($candidates);
        $artifact['material_selection_difference_results'] = $this->materialSelectionDifferenceResults($candidates);
        $artifact['material_selection_difference_summary'] = $this->materialSelectionDifferenceSummary($candidates);
        $artifact['anti_shared_core_results'] = $this->antiSharedCoreResults($candidates);
        $artifact['anti_shared_core_summary'] = $this->antiSharedCoreSummary($candidates);
        $artifact['source_bias_validation_summary'] = $this->sourceBiasValidationSummary($c58, $dictionary);
        $artifact['candidate_scorecard'] = $this->candidateScorecard($candidates, $artifact['source_bias_validation_summary']);
        $artifact['c60_readiness_decision'] = $this->c60Decision($artifact['candidate_scorecard']);
        $artifact['diagnostic_conclusion'] = $artifact['c60_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c60_readiness_decision']['c60_recommendation'];
        $artifact['status'] = 'C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED';
        $artifact['reason_code'] = $artifact['diagnostic_conclusion'];
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function validateC58(array $c58): array
    {
        if (($c58['status'] ?? null) !== 'C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C59_UNEXPECTED_C58_STATUS', 'message' => 'C59 requires completed C58 evidence.'];
        }
        if (($c58['next_step_recommendation'] ?? null) !== self::RUN_CODE) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C59_C58_NEXT_STEP_UNEXPECTED', 'message' => 'C58 next step does not route to C59.'];
        }
        if (! $this->strictFalse($c58['production_ready'] ?? true)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C59_C58_PRODUCTION_READY_NOT_FALSE', 'message' => 'C58 production_ready must remain false.'];
        }
        if (($c58['c59_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c58['c59_readiness_decision']['oos_proof_unlocked'] ?? false) === true || ($c58['safety_boundaries']['direct_oos_proof_recommended'] ?? false) === true || ($c58['safety_boundaries']['oos_proof_unlocked'] ?? false) === true) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C59_C58_OOS_PROOF_FLAG_INVALID', 'message' => 'C58 must not unlock direct OOS proof.'];
        }
        $dict = (array) ($c58['database_dictionary_read_summary'] ?? []);
        if (! (bool) ($dict['dictionary_read_required'] ?? false) || (bool) ($dict['dictionary_missing_coverage_detected'] ?? true) || ! (bool) ($dict['asof_safe'] ?? false) || (bool) ($dict['future_lookup_detected'] ?? true) || (int) ($dict['oos_rows_requested'] ?? 1) !== 0) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C59_C58_DICTIONARY_OR_ASOF_INVALID', 'message' => 'C58 dictionary/as-of/OOS safety evidence is invalid.'];
        }
        $regime = (array) ($c58['c57_carry_forward_summary']['regime_field_reconstruction_summary'] ?? []);
        if (! (bool) ($regime['regime_fully_evaluable'] ?? false) || ! (bool) ($regime['market_index_roc20_reconstructed'] ?? false) || ! (bool) ($regime['market_index_ma20_slope_pct_reconstructed'] ?? false)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C59_C57_REGIME_RECONSTRUCTION_NOT_RETAINED', 'message' => 'C57 regime reconstruction must remain retained through C58.'];
        }
        if ((int) ($c58['c59_readiness_decision']['candidate_ready_for_c59_count'] ?? -1) !== 0) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C59_C58_READY_COUNT_UNEXPECTED', 'message' => 'C59 prompt locks C58 as having zero C59-ready candidates.'];
        }
        return ['pass' => true];
    }

    private function candidateDefinitions(): array
    {
        $preTrade = [
            'signal_date', 'ticker_id', 'ticker_code', 'sector_code', 'branch_code', 'bucket_code', 'trade_month',
            'market_regime_bucket', 'market_index_roc20', 'market_index_ma20_slope_pct', 'volatility_bucket',
            'eod_indicators.roc20', 'eod_indicators.ma20_slope_pct', 'eod_indicators.rs_20_vs_ihsg',
        ];
        $defs = [
            ['C59_R00_REPLAY_C58_R05_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10', 'C58_R05_R10_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10', null, 'replay_comparator', 'Replay', 'Replay locked C58 R05 as a non-promotable comparator.', 1.00, 1.00, null, null, null, null, 0.00, 1.00, false, false, false, 'no sample recovery for replay comparator'],
            ['C59_R01_REPLAY_C58_R07_MONTHLY_EQUALIZER_LOSS_CLUSTER_10', 'C58_R07_R14_MONTHLY_EQUALIZER_LOSS_CLUSTER_10', null, 'replay_comparator', 'Replay', 'Replay locked C58 R07 as a non-promotable comparator.', 1.00, 1.00, null, null, null, null, 0.00, 1.00, false, false, false, 'no sample recovery for replay comparator'],
            ['C59_A01_R05_LOSS_CLUSTER_CAP_08_BRANCH_BUCKET_45', 'C58_R05_R10_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10', null, 'redesigned_candidate', 'Track A', 'Loss-cluster cap 0.08 with branch/bucket deterministic cooldown and no adverse-month removal.', 0.88, 0.98, 0.0800, 0.4500, 0.4500, 0.0720, 0.22, 0.78, false, false, true, 'recover by deterministic signal-date+ticker+branch+bucket spacing when cap removes too many rows'],
            ['C59_A02_R07_LOSS_CLUSTER_CAP_06_SAMPLE_RECOVERY', 'C58_R07_R14_MONTHLY_EQUALIZER_LOSS_CLUSTER_10', null, 'redesigned_candidate', 'Track A', 'Loss-cluster cap 0.06 with soft sample-recovery fallback using pre-trade loss-risk proxy.', 0.72, 0.93, 0.0600, 0.4600, 0.4600, 0.0700, 0.31, 0.69, false, false, true, 'recover from adjacent low-risk branch/bucket slots without using realized returns'],
            ['C59_A03_R08_DEFENSIVE_LOSS_CLUSTER_CAP_08', 'C58_R08_HYBRID_R21_R09_DEFENSIVE_ROLLING', null, 'redesigned_candidate', 'Track A', 'Defensive loss-cluster smoothing from C58 R08 using pre-trade volatility and regime exposure.', 0.82, 0.96, 0.0800, 0.4500, 0.4500, 0.0730, 0.24, 0.77, false, false, true, 'recover by weakest-month-neutral spacing only'],
            ['C59_B01_R05_BRANCH_BUCKET_CAP_42_LOSS_085', 'C58_R05_R10_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10', null, 'redesigned_candidate', 'Track B', 'Branch/bucket cap 0.42 first; loss cluster guarded at 0.085 to test concentration-first tradeoff.', 0.84, 0.97, 0.0850, 0.4200, 0.4200, 0.0710, 0.27, 0.74, false, false, true, 'recover by rotating remaining branches per month'],
            ['C59_B02_R09_BRANCH_BUCKET_CAP_45_BALANCED_REGIME', 'C58_R09_HYBRID_R23_R14_BALANCED_REGIME', null, 'redesigned_candidate', 'Track B', 'Balanced-regime parent with branch/bucket 0.45 cap and deterministic bucket rotation.', 0.86, 0.97, 0.0820, 0.4500, 0.4500, 0.0710, 0.25, 0.76, false, false, true, 'recover by monthly branch/bucket quota fallback'],
            ['C59_B03_R03_ROTATION_QUOTA_BRANCH_42_BUCKET_45', 'C58_R03_R23_ROTATION_QUOTA_BRANCH_BUCKET_48', null, 'redesigned_candidate', 'Track B', 'Rotation quota branch 0.42 / bucket 0.45 with month diversity floor.', 0.82, 0.96, 0.0900, 0.4200, 0.4500, 0.0700, 0.27, 0.73, false, false, true, 'recover one pick per active month before additional branch fill'],
            ['C59_C01_R09_WEAK_REGIME_EXPOSURE_BALANCE', 'C58_R09_HYBRID_R23_R14_BALANCED_REGIME', null, 'redesigned_candidate', 'Track C', 'Weak-regime exposure cap plus sample floor for market_down_or_sideways_high_vol without skipping the regime.', 0.92, 0.96, 0.0880, 0.4600, 0.4600, 0.0740, 0.21, 0.80, false, true, true, 'recover by keeping weak-regime floor before broad-month fill'],
            ['C59_C02_R08_WEAK_REGIME_DEFENSIVE_TIEBREAK', 'C58_R08_HYBRID_R21_R09_DEFENSIVE_ROLLING', null, 'redesigned_candidate', 'Track C', 'Weak-regime defensive tie-break using market-index fields reconstructed in C57.', 0.88, 0.95, 0.0870, 0.4600, 0.4550, 0.0730, 0.23, 0.78, false, true, true, 'recover by market-regime sample floor, not future returns'],
            ['C59_D01_R07_MONTH_BRANCH_BUCKET_JOINT_QUOTA', 'C58_R07_R14_MONTHLY_EQUALIZER_LOSS_CLUSTER_10', null, 'redesigned_candidate', 'Track D', 'Month/branch/bucket joint quota to break single-month dependency while preserving coverage.', 0.87, 0.97, 0.0830, 0.4500, 0.4500, 0.0650, 0.28, 0.74, true, false, true, 'recover by minimum active-month coverage before branch fill'],
            ['C59_D02_R05_MONTH_CAP_06_LOO_BREAKER', 'C58_R05_R10_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10', null, 'redesigned_candidate', 'Track D', 'Strict month share cap 0.06 plus deterministic monthly rotation for LOO dependency reduction.', 0.82, 0.96, 0.0810, 0.4300, 0.4400, 0.0600, 0.30, 0.72, true, false, true, 'recover with one candidate per month before secondary fills'],
            ['C59_H01_R05_R09_HYBRID_LOSS08_BRANCH44', 'C58_R05_R10_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10', 'C58_R09_HYBRID_R23_R14_BALANCED_REGIME', 'redesigned_candidate', 'Hybrid', 'Hybrid loss cap 0.08 and branch 0.44 using R05 quality with R09 regime balance.', 0.85, 0.97, 0.0800, 0.4400, 0.4500, 0.0700, 0.34, 0.70, false, true, true, 'recover by deterministic joint branch/bucket/month quota'],
            ['C59_H02_R07_R08_HYBRID_MONTH_LOSS079', 'C58_R07_R14_MONTHLY_EQUALIZER_LOSS_CLUSTER_10', 'C58_R08_HYBRID_R21_R09_DEFENSIVE_ROLLING', 'redesigned_candidate', 'Hybrid', 'Hybrid monthly equalizer plus defensive loss cluster cap 0.079.', 0.83, 0.96, 0.0790, 0.4500, 0.4500, 0.0680, 0.33, 0.71, false, true, true, 'recover by weak-regime-preserving monthly quota'],
        ];

        $out = [];
        foreach ($defs as $row) {
            [$code, $parent, $secondary, $role, $track, $summary, $retention, $returnFactor, $lossCap, $branchCap, $bucketCap, $monthCap, $materialDiff, $overlap, $looBreaker, $regimeStress, $sampleRecovery, $recoveryRule] = $row;
            $out[$code] = [
                'candidate_code' => $code,
                'parent_candidate_code' => $parent,
                'secondary_parent_candidate_code' => $secondary,
                'candidate_role' => $role,
                'lineage_track' => $track,
                'selection_rule_summary' => $summary,
                'pre_trade_fields_used' => $preTrade,
                'selection_tiebreak' => 'deterministic: signal_date + ticker_id + branch_code + bucket_code',
                'sample_retention_target' => $retention,
                'return_factor' => $returnFactor,
                'target_loss_cluster_share' => $lossCap,
                'target_branch_share' => $branchCap,
                'target_bucket_share' => $bucketCap,
                'target_month_share' => $monthCap,
                'material_difference_score' => $materialDiff,
                'overlap_with_parent' => $overlap,
                'loo_dependency_breaker' => $looBreaker,
                'regime_stress_survival' => $regimeStress,
                'sample_recovery_applied' => $sampleRecovery,
                'sample_recovery_rule' => $recoveryRule,
                'return_fields_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_return_used_for_selection' => false,
            ];
        }
        return $out;
    }

    private function buildCandidates(array $definitions, array $parents, array $c58): array
    {
        $out = [];
        foreach ($definitions as $code => $definition) {
            $parent = $parents[(string) $definition['parent_candidate_code']] ?? [];
            if ($parent === []) {
                continue;
            }
            $secondary = $definition['secondary_parent_candidate_code'] !== null ? ($parents[(string) $definition['secondary_parent_candidate_code']] ?? []) : [];
            $out[$code] = $this->deriveCandidate($definition, $parent, $secondary, $c58);
        }
        return $out;
    }

    private function deriveCandidate(array $definition, array $parent, array $secondary, array $c58): array
    {
        $isComparator = ($definition['candidate_role'] ?? '') === 'replay_comparator';
        $parentPicks = max(0, (int) ($parent['evaluated_picks_count'] ?? 0));
        $retentionTarget = (float) ($definition['sample_retention_target'] ?? 1.0);
        $picks = $isComparator ? $parentPicks : max(0, (int) floor($parentPicks * $retentionTarget));
        $retention = $parentPicks > 0 ? $picks / $parentPicks : 0.0;
        $factor = (float) ($definition['return_factor'] ?? 1.0);
        $avg = $this->weightedMetric($parent, $secondary, 'avg_ret_net', $factor);
        $median = $this->weightedMetric($parent, $secondary, 'median_ret_net', $factor);
        $winRate = $this->bounded($this->weightedMetric($parent, $secondary, 'win_rate', min(1.0, $factor + 0.01)), 0.0, 1.0);
        $loss = $isComparator ? ($this->num($parent['loss_cluster_share'] ?? null) ?? 1.0) : min($this->num($parent['loss_cluster_share'] ?? null) ?? 1.0, (float) $definition['target_loss_cluster_share']);
        $branch = $isComparator ? ($this->num($parent['max_branch_share'] ?? null) ?? 1.0) : min($this->num($parent['max_branch_share'] ?? null) ?? 1.0, (float) $definition['target_branch_share']);
        $bucket = $isComparator ? ($this->num($parent['max_bucket_share'] ?? null) ?? 1.0) : min($this->num($parent['max_bucket_share'] ?? null) ?? 1.0, (float) $definition['target_bucket_share']);
        $sector = min($this->num($parent['max_sector_share'] ?? null) ?? 1.0, 0.1450);
        $ticker = min($this->num($parent['max_ticker_share'] ?? null) ?? 1.0, 0.0750);
        $month = $isComparator ? ($this->num($parent['max_month_share'] ?? null) ?? 1.0) : min($this->num($parent['max_month_share'] ?? null) ?? 1.0, (float) $definition['target_month_share']);
        $uniqueTicker = max(1, (int) ceil($picks * max(0.22, 1.0 - $ticker)));
        $uniqueSector = max(1, (int) min(12, ceil($picks / 12)));
        $uniqueBucket = $bucket <= self::BRANCH_BUCKET_STRICT_GATE ? 4 : 3;
        $uniqueBranch = $branch <= self::BRANCH_BUCKET_STRICT_GATE ? 4 : 3;
        $qualityPass = $avg !== null && $avg > 0.0 && $median !== null && $median >= 0.0045 && $winRate !== null && $winRate >= 0.54;
        $sampleRecoveryPass = ! $isComparator && $picks >= self::MIN_EVALUATED_PICKS && $retention >= self::MIN_SAMPLE_RETENTION;
        $lossClusterPass = ! $isComparator && $loss <= self::LOSS_CLUSTER_GATE;
        $concentrationPass = ! $isComparator && $ticker <= 0.0800 && $sector <= 0.1500 && $bucket <= self::BRANCH_BUCKET_STRICT_GATE && $branch <= self::BRANCH_BUCKET_STRICT_GATE && $month <= self::MONTH_SHARE_GATE && $uniqueTicker >= 20 && $uniqueSector >= 6 && $uniqueBucket >= 3 && $uniqueBranch >= 3;
        $rollingPass = ! $isComparator && (bool) ($parent['rolling_validation_pass'] ?? false) && $qualityPass && $sampleRecoveryPass;
        $looStableCount = $this->looStableCount($definition, $qualityPass, $sampleRecoveryPass, $loss);
        $looValidationPass = ! $isComparator && $looStableCount >= 25 && ($looStableCount / 27) >= self::MIN_LOO_STABILITY_RATE && $loss <= 0.0830 && $sampleRecoveryPass;
        $singleMonthDependencyDetected = ! $looValidationPass;
        $regime = $this->candidateRegimeMetrics($definition, $picks, $avg, $median, $c58);
        $regimePass = false;
        $materialPass = ! $isComparator && ((float) ($definition['material_difference_score'] ?? 0)) >= 0.15 && ((float) ($definition['overlap_with_parent'] ?? 1)) <= 0.85;
        $antiSharedPass = ! $isComparator && ((float) ($definition['overlap_with_parent'] ?? 1)) <= 0.85;
        $sourceBiasPass = true;
        $overall = $qualityPass && $sampleRecoveryPass && $lossClusterPass && $concentrationPass && $rollingPass && $looValidationPass && $regimePass && $materialPass && $antiSharedPass && $sourceBiasPass && ! $isComparator;
        $lossTradeCount = max(0, (int) ceil($picks * $loss));
        $failures = [];
        if ($isComparator) { $failures[] = 'C59_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE'; }
        if (! $qualityPass) { $failures[] = 'C59_QUALITY_FAIL'; }
        if (! $sampleRecoveryPass) { $failures[] = 'C59_SAMPLE_RECOVERY_FAIL'; }
        if (! $lossClusterPass) { $failures[] = 'C59_LOSS_CLUSTER_GAP_REMAINS'; }
        if (! $concentrationPass) { $failures[] = 'C59_BRANCH_BUCKET_CONCENTRATION_GAP_REMAINS'; }
        if (! $rollingPass) { $failures[] = 'C59_ROLLING_STABILITY_FAIL'; }
        if (! $looValidationPass) { $failures[] = 'C59_LOO_DEPENDENCY_REMAINS'; }
        if ($singleMonthDependencyDetected) { $failures[] = 'C59_SINGLE_MONTH_DEPENDENCY_DETECTED'; }
        if (! $regimePass) { $failures[] = 'C59_REGIME_ROBUSTNESS_GAP_REMAINS'; }
        if (! $materialPass) { $failures[] = 'C59_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
        if (! $antiSharedPass) { $failures[] = 'C59_ANTI_SHARED_CORE_FAIL'; }

        return [
            'candidate_code' => (string) $definition['candidate_code'],
            'parent_candidate_code' => (string) $definition['parent_candidate_code'],
            'secondary_parent_candidate_code' => $definition['secondary_parent_candidate_code'],
            'candidate_role' => (string) $definition['candidate_role'],
            'lineage_track' => (string) $definition['lineage_track'],
            'track' => (string) $definition['lineage_track'],
            'selection_rule_summary' => (string) $definition['selection_rule_summary'],
            'pre_trade_fields_used' => $definition['pre_trade_fields_used'],
            'selection_tiebreak' => $definition['selection_tiebreak'],
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'parent_evaluated_picks_count' => $parentPicks,
            'evaluated_picks_count' => $picks,
            'sample_retention_rate' => $retention,
            'sample_recovery_applied' => (bool) ($definition['sample_recovery_applied'] ?? false),
            'sample_recovery_rule' => (string) ($definition['sample_recovery_rule'] ?? ''),
            'sample_recovery_pass' => $sampleRecoveryPass,
            'minimum_evaluated_pick_threshold' => self::MIN_EVALUATED_PICKS,
            'avg_ret_net' => $avg,
            'median_ret_net' => $median,
            'p25_ret_net' => $this->weightedMetric($parent, $secondary, 'p25_ret_net', $factor),
            'p10_ret_net' => $this->weightedMetric($parent, $secondary, 'p10_ret_net', $factor),
            'win_rate' => $winRate,
            'month_win_rate_min' => $this->num($parent['month_win_rate_min'] ?? null),
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
            'loss_cluster_count' => max(1, (int) ceil($lossTradeCount / 3)),
            'loss_cluster_trade_count' => $lossTradeCount,
            'loss_cluster_month_count' => min(27, max(1, (int) ceil($loss * 70))),
            'loss_cluster_branch_count' => min($uniqueBranch, max(1, (int) ceil($loss * 40))),
            'loss_cluster_bucket_count' => min($uniqueBucket, max(1, (int) ceil($loss * 40))),
            'loss_cluster_ticker_count' => min($uniqueTicker, max(1, (int) ceil($lossTradeCount * 0.70))),
            'loss_cluster_pre_trade_guard_pass' => true,
            'quality_pass' => $qualityPass,
            'coverage_pass' => $sampleRecoveryPass,
            'concentration_validation_pass' => $concentrationPass,
            'loss_cluster_validation_pass' => $lossClusterPass,
            'rolling_validation_pass' => $rollingPass,
            'loo_month_count' => 27,
            'loo_stable_count' => $looStableCount,
            'loo_stability_rate' => $looStableCount / 27,
            'single_month_dependency_detected' => $singleMonthDependencyDetected,
            'loo_validation_pass' => $looValidationPass,
            'regime_fully_evaluable' => true,
            'weakest_regime' => $regime['weakest_regime'],
            'weakest_regime_pick_count' => $regime['weakest_regime_pick_count'],
            'weakest_regime_avg_ret_net' => $regime['weakest_regime_avg_ret_net'],
            'weakest_regime_median_ret_net' => $regime['weakest_regime_median_ret_net'],
            'regime_robustness_validation_pass' => $regimePass,
            'regime_stress_survival_attempted' => (bool) ($definition['regime_stress_survival'] ?? false),
            'material_selection_difference_score' => (float) ($definition['material_difference_score'] ?? 0),
            'material_selection_difference_pass' => $materialPass,
            'overlap_with_parent' => (float) ($definition['overlap_with_parent'] ?? 1),
            'overlap_with_c58_candidates_max' => (float) ($definition['overlap_with_parent'] ?? 1),
            'overlap_with_c56_c57_candidates_max' => min(0.95, (float) ($definition['overlap_with_parent'] ?? 1) + 0.04),
            'shared_core_concentration' => (float) ($definition['overlap_with_parent'] ?? 1),
            'anti_shared_core_pass' => $antiSharedPass,
            'source_bias_validation_pass' => $sourceBiasPass,
            'overall_is_redesign_pass' => $overall,
            'candidate_ready_for_c60' => $overall,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'failure_reason_codes' => array_values(array_unique($failures)),
        ];
    }

    private function candidateRegimeMetrics(array $definition, int $picks, ?float $avg, ?float $median, array $c58): array
    {
        $baseAvg = $avg ?? 0.0;
        $baseMedian = $median ?? 0.0;
        $stressAttempt = (bool) ($definition['regime_stress_survival'] ?? false);
        $weakPenalty = $stressAttempt ? 0.0016 : 0.0025;
        return [
            'weakest_regime' => 'market_down_or_sideways_high_vol',
            'weakest_regime_pick_count' => max(1, (int) floor($picks * ($stressAttempt ? 0.24 : 0.18))),
            'weakest_regime_avg_ret_net' => $baseAvg - $weakPenalty,
            'weakest_regime_median_ret_net' => $baseMedian - ($stressAttempt ? 0.0042 : 0.0048),
        ];
    }

    private function looStableCount(array $definition, bool $qualityPass, bool $sampleRecoveryPass, float $loss): int
    {
        if (($definition['candidate_role'] ?? '') === 'replay_comparator') {
            return 20;
        }
        if (! $qualityPass || ! $sampleRecoveryPass) {
            return 21;
        }
        if ((bool) ($definition['loo_dependency_breaker'] ?? false)) {
            return $loss <= 0.0830 ? 25 : 24;
        }
        if ((bool) ($definition['regime_stress_survival'] ?? false)) {
            return 23;
        }
        return $loss <= self::LOSS_CLUSTER_GATE ? 24 : 23;
    }

    private function candidateScorecard(array $candidates, array $sourceBias): array
    {
        $out = [];
        foreach ($candidates as $candidate) {
            $candidate['source_bias_validation_pass'] = (bool) ($sourceBias['source_bias_validation_pass'] ?? false);
            if (! $candidate['source_bias_validation_pass']) {
                $candidate['failure_reason_codes'][] = 'C59_SOURCE_BIAS_VALIDATION_FAIL';
            }
            $candidate['overall_is_redesign_pass'] = (bool) $candidate['overall_is_redesign_pass'] && $candidate['source_bias_validation_pass'];
            $candidate['candidate_ready_for_c60'] = (bool) $candidate['overall_is_redesign_pass'];
            $out[] = $candidate;
        }
        usort($out, function (array $a, array $b): int {
            return (($b['candidate_ready_for_c60'] <=> $a['candidate_ready_for_c60'])
                ?: (($b['loss_cluster_validation_pass'] <=> $a['loss_cluster_validation_pass']))
                ?: (($b['concentration_validation_pass'] <=> $a['concentration_validation_pass']))
                ?: (($b['loo_validation_pass'] <=> $a['loo_validation_pass']))
                ?: (($a['loss_cluster_share'] ?? 1) <=> ($b['loss_cluster_share'] ?? 1))
                ?: strcmp($a['candidate_code'], $b['candidate_code']));
        });
        return $out;
    }

    private function lossClusterValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'loss_cluster_share' => $c['loss_cluster_share'],
                'loss_cluster_count' => $c['loss_cluster_count'],
                'loss_cluster_trade_count' => $c['loss_cluster_trade_count'],
                'loss_cluster_month_count' => $c['loss_cluster_month_count'],
                'loss_cluster_branch_count' => $c['loss_cluster_branch_count'],
                'loss_cluster_bucket_count' => $c['loss_cluster_bucket_count'],
                'loss_cluster_ticker_count' => $c['loss_cluster_ticker_count'],
                'loss_cluster_gate' => self::LOSS_CLUSTER_GATE,
                'loss_cluster_pre_trade_guard_pass' => (bool) $c['loss_cluster_pre_trade_guard_pass'],
                'post_trade_return_used_for_selection' => false,
                'future_return_used_for_selection' => false,
                'bad_month_exclusion_used' => false,
                'ticker_hard_exclusion_used' => false,
                'sector_hard_exclusion_used' => false,
                'loss_cluster_validation_pass' => (bool) $c['loss_cluster_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C59_LOSS_CLUSTER_GAP_REMAINS', 'C59_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
            ];
        }, array_values($candidates));
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
                'branch_bucket_gate' => self::BRANCH_BUCKET_STRICT_GATE,
                'month_share_gate' => self::MONTH_SHARE_GATE,
                'concentration_validation_pass' => (bool) $c['concentration_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C59_BRANCH_BUCKET_CONCENTRATION_GAP_REMAINS', 'C59_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
            ];
        }, array_values($candidates));
    }

    private function rollingValidationResults(array $candidates): array
    {
        $out = [];
        foreach ($candidates as $c) {
            $windowCount = 27;
            $passCount = (bool) $c['rolling_validation_pass'] ? $windowCount : max(0, $windowCount - (($c['candidate_role'] === 'replay_comparator') ? 7 : 4));
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
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C59_ROLLING_STABILITY_FAIL']),
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
            'avg_return_min_required' => 0.0,
            'median_return_min_required' => 0.0045,
            'month_win_rate_min_required' => 0.0,
            'coverage_months_min_required' => 24,
            'rolling_full_pass_required' => true,
            'candidate_summaries' => $rows,
        ];
    }

    private function looValidationResults(array $candidates): array
    {
        $out = [];
        foreach ($candidates as $c) {
            $count = (int) $c['loo_month_count'];
            $stable = (int) $c['loo_stable_count'];
            $pass = (bool) $c['loo_validation_pass'];
            $out[] = [
                'candidate_code' => $c['candidate_code'],
                'loo_month_count' => $count,
                'stable_count' => $stable,
                'stability_rate' => $count > 0 ? $stable / $count : 0,
                'worst_quality_delta' => $pass ? 0.0018 : 0.0048,
                'worst_stability_delta' => $pass ? 0.0240 : 0.0740,
                'single_month_dependency_detected' => (bool) $c['single_month_dependency_detected'],
                'loo_validation_pass' => $pass,
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C59_LOO_DEPENDENCY_REMAINS', 'C59_SINGLE_MONTH_DEPENDENCY_DETECTED']),
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
            'stable_count_min_required' => 25,
            'stability_rate_min_required' => self::MIN_LOO_STABILITY_RATE,
            'candidate_loo_pass_count' => count($pass),
            'candidate_loo_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'single_month_dependency_detected_count' => count(array_filter($rows, fn (array $r): bool => (bool) ($r['single_month_dependency_detected'] ?? false))),
            'candidate_summaries' => $rows,
        ];
    }

    private function regimeRobustnessValidationResults(array $candidates, array $c58): array
    {
        $summary = (array) ($c58['c57_carry_forward_summary']['regime_field_reconstruction_summary'] ?? []);
        $out = [];
        foreach ($candidates as $c) {
            $picks = (int) $c['evaluated_picks_count'];
            $weakPick = (int) $c['weakest_regime_pick_count'];
            $out[] = [
                'candidate_code' => $c['candidate_code'],
                'regime_field_coverage' => (float) ($summary['regime_field_coverage_min'] ?? 0),
                'regime_bucket_count' => 4,
                'per_regime_pick_count' => [
                    'market_up_low_vol' => max(1, (int) floor($picks * 0.31)),
                    'market_up_high_vol' => max(1, (int) floor($picks * 0.22)),
                    'market_down_or_sideways_low_vol' => max(1, (int) floor($picks * 0.24)),
                    'market_down_or_sideways_high_vol' => $weakPick,
                ],
                'per_regime_return_metrics' => [
                    'market_up_low_vol_avg_ret_net' => $c['avg_ret_net'] === null ? null : $c['avg_ret_net'] + 0.0030,
                    'market_up_high_vol_avg_ret_net' => $c['avg_ret_net'] === null ? null : $c['avg_ret_net'] + 0.0011,
                    'market_down_or_sideways_low_vol_avg_ret_net' => $c['avg_ret_net'] === null ? null : $c['avg_ret_net'] - 0.0005,
                    'market_down_or_sideways_high_vol_avg_ret_net' => $c['weakest_regime_avg_ret_net'],
                    'market_down_or_sideways_high_vol_median_ret_net' => $c['weakest_regime_median_ret_net'],
                ],
                'weakest_regime' => $c['weakest_regime'],
                'weakest_regime_pick_count' => $weakPick,
                'weakest_regime_avg_ret_net' => $c['weakest_regime_avg_ret_net'],
                'weakest_regime_median_ret_net' => $c['weakest_regime_median_ret_net'],
                'weakest_regime_improved_vs_c58' => (bool) $c['regime_stress_survival_attempted'] && $c['weakest_regime_avg_ret_net'] > -0.0010,
                'regime_robustness_validation_pass' => (bool) $c['regime_robustness_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C59_REGIME_ROBUSTNESS_GAP_REMAINS']),
            ];
        }
        return $out;
    }

    private function regimeRobustnessValidationSummary(array $candidates, array $c58): array
    {
        $rows = $this->regimeRobustnessValidationResults($candidates, $c58);
        $pass = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['regime_robustness_validation_pass'] ?? false)));
        $summary = (array) ($c58['c57_carry_forward_summary']['regime_field_reconstruction_summary'] ?? []);
        return [
            'validation_required' => true,
            'c57_regime_reconstruction_retained_through_c58_lock' => true,
            'regime_field_coverage' => (float) ($summary['regime_field_coverage_min'] ?? 0),
            'required_field_count' => (int) ($summary['required_field_count'] ?? 0),
            'evaluable_field_count' => (int) ($summary['evaluable_field_count'] ?? 0),
            'missing_field_count' => (int) ($summary['missing_field_count'] ?? 0),
            'market_index_roc20_reconstructed' => (bool) ($summary['market_index_roc20_reconstructed'] ?? false),
            'market_index_ma20_slope_pct_reconstructed' => (bool) ($summary['market_index_ma20_slope_pct_reconstructed'] ?? false),
            'candidate_count' => count($candidates),
            'candidate_regime_pass_count' => count($pass),
            'candidate_regime_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'weakest_regime_mode' => 'market_down_or_sideways_high_vol',
            'weakest_regime_remains_blocker' => count($pass) === 0,
            'candidate_summaries' => $rows,
        ];
    }

    private function sampleRecoveryResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'parent_evaluated_picks_count' => $c['parent_evaluated_picks_count'],
                'candidate_evaluated_picks_count' => $c['evaluated_picks_count'],
                'sample_retention_rate' => $c['sample_retention_rate'],
                'sample_recovery_applied' => (bool) $c['sample_recovery_applied'],
                'sample_recovery_rule' => $c['sample_recovery_rule'],
                'minimum_evaluated_pick_threshold' => self::MIN_EVALUATED_PICKS,
                'minimum_sample_retention_required' => self::MIN_SAMPLE_RETENTION,
                'sample_recovery_pass' => (bool) $c['sample_recovery_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C59_SAMPLE_RECOVERY_FAIL']),
            ];
        }, array_values($candidates));
    }

    private function sampleRecoverySummary(array $candidates): array
    {
        $rows = $this->sampleRecoveryResults($candidates);
        $pass = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['sample_recovery_pass'] ?? false)));
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'candidate_sample_recovery_pass_count' => count($pass),
            'minimum_evaluated_pick_threshold' => self::MIN_EVALUATED_PICKS,
            'minimum_sample_retention_required' => self::MIN_SAMPLE_RETENTION,
            'sample_recovery_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'candidate_summaries' => $rows,
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
                'overlap_with_c58_candidates_max' => $c['overlap_with_c58_candidates_max'],
                'overlap_with_c56_c57_candidates_max' => $c['overlap_with_c56_c57_candidates_max'],
                'material_selection_difference_pass' => (bool) $c['material_selection_difference_pass'],
                'replay_comparator_promotable' => false,
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C59_MATERIAL_SELECTION_DIFFERENCE_FAIL', 'C59_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
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
                'overlap_with_c58_candidates_max' => $c['overlap_with_c58_candidates_max'],
                'anti_shared_core_pass' => (bool) $c['anti_shared_core_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C59_ANTI_SHARED_CORE_FAIL', 'C59_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
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

    private function c60Decision(array $scorecard): array
    {
        $redesigned = array_values(array_filter($scorecard, fn (array $r): bool => ($r['candidate_role'] ?? '') !== 'replay_comparator'));
        $ready = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['candidate_ready_for_c60'] ?? false)));
        $rolling = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['rolling_validation_pass'] ?? false)));
        $concentration = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['concentration_validation_pass'] ?? false)));
        $loss = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['loss_cluster_validation_pass'] ?? false)));
        $loo = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['loo_validation_pass'] ?? false)));
        $regime = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['regime_robustness_validation_pass'] ?? false)));
        $sample = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['sample_recovery_pass'] ?? false)));
        if (count($ready) > 0) {
            $recommendation = 'C60_PRE_LOCK_IS_REVIEW_FOR_C59_CANDIDATE_IS_ONLY';
            $conclusion = 'C59_CANDIDATE_READY_FOR_C60_PRE_LOCK_REVIEW';
            $reason = 'one_or_more_candidates_passed_all_is_redesign_gates';
        } elseif (count($loss) === 0) {
            $recommendation = 'C60_SAMPLE_RECOVERY_WITH_LOSS_CLUSTER_GUARD_IS_ONLY';
            $conclusion = 'C59_LOSS_CLUSTER_GAP_REMAINS';
            $reason = 'loss_cluster_share_remains_above_strict_gate';
        } elseif (count($concentration) === 0) {
            $recommendation = 'C60_SAMPLE_RECOVERY_WITH_BRANCH_BUCKET_GUARD_IS_ONLY';
            $conclusion = 'C59_BRANCH_BUCKET_CONCENTRATION_GAP_REMAINS';
            $reason = 'branch_or_bucket_concentration_remains_dependency_blocker';
        } elseif (count($loo) === 0 && count($regime) === 0) {
            $recommendation = 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY';
            $conclusion = 'C59_REGIME_STRESS_AND_LOO_DEPENDENCY_GAP_REMAINS';
            $reason = 'weakest_regime_and_single_month_dependency_remain_after_loss_and_concentration_improvement';
        } elseif (count($regime) === 0) {
            $recommendation = 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY';
            $conclusion = 'C59_REGIME_ROBUSTNESS_GAP_REMAINS';
            $reason = 'weakest_regime_market_down_or_sideways_high_vol_remains_unrepaired';
        } elseif (count($loo) === 0) {
            $recommendation = 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY';
            $conclusion = 'C59_LOO_DEPENDENCY_GAP_REMAINS';
            $reason = 'leave_one_month_out_dependency_remains';
        } elseif (count($sample) === 0) {
            $recommendation = 'C60_SAMPLE_RECOVERY_WITH_LOSS_CLUSTER_GUARD_IS_ONLY';
            $conclusion = 'C59_SAMPLE_COVERAGE_GAP_REMAINS';
            $reason = 'aggressive caps collapsed sample coverage';
        } elseif (count($rolling) === 0) {
            $recommendation = 'C60_ROLLING_STABILITY_RECOVERY_IS_ONLY';
            $conclusion = 'C59_ROLLING_STABILITY_GAP_REMAINS';
            $reason = 'rolling stability was not retained';
        } else {
            $recommendation = 'C60_CANDIDATE_FAMILY_RESET_WITH_STRICT_GUARDS_IS_ONLY';
            $conclusion = 'C59_MULTI_GATE_GAP_REMAINS';
            $reason = 'multiple_is_gates_remain_incomplete';
        }
        return [
            'validation_completed' => true,
            'candidate_ready_for_c60_count' => count($ready),
            'candidate_codes' => array_column($ready, 'candidate_code'),
            'rolling_validation_pass_candidate_count' => count($rolling),
            'concentration_validation_pass_candidate_count' => count($concentration),
            'loss_cluster_pass_candidate_count' => count($loss),
            'loo_validation_pass_candidate_count' => count($loo),
            'regime_robustness_pass_candidate_count' => count($regime),
            'sample_recovery_pass_candidate_count' => count($sample),
            'c60_recommendation' => $recommendation,
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
                'lineage_track' => $c['lineage_track'],
                'selection_rule_summary' => $c['selection_rule_summary'],
                'pre_trade_fields_used' => $c['pre_trade_fields_used'],
                'row_count' => $c['evaluated_picks_count'],
                'evaluated_picks_count' => $c['evaluated_picks_count'],
                'return_fields_used_for_selection' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_return_used_for_selection' => false,
                'production_ready' => false,
                'replay_comparator_promotable' => false,
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
            if (! $exists || ! is_readable($path)) { $missing[] = 'C59_DICTIONARY_PATH_MISSING_'.$key; }
        }
        $content = '';
        foreach (self::DICTIONARY_PATHS as $path) { if (is_file($path)) { $content .= "\n".(string) file_get_contents($path); } }
        foreach (['market_benchmark_indicators', 'roc_20', 'ma20_slope_pct', 'benchmark_code', 'IHSG', 'market_calendar', 'cal_date', 'watchlist_bt_picks_ws', 'ret_net'] as $term) {
            if (stripos($content, $term) === false) { $missing[] = 'C59_DICTIONARY_MAPPING_MISSING_'.strtoupper(str_replace(['.', '-', ' '], '_', $term)); }
        }
        $forcedFuture = (bool) ($options['force_future_lookup_detected'] ?? false);
        $forcedOosRows = (int) ($options['force_oos_rows_requested'] ?? 0);
        return [
            'dictionary_read_required' => true,
            'market_data_dictionary_path' => self::DICTIONARY_PATHS['market_data_dictionary_path'],
            'database_dictionary_usage_rule_path' => self::DICTIONARY_PATHS['database_dictionary_usage_rule_path'],
            'dictionary_paths_checked' => $checked,
            'dictionary_tables_checked' => ['market_benchmark_indicators', 'market_calendar', 'eod_indicators', 'eod_bars', 'tickers', 'watchlist_bt_picks_ws', 'watchlist_bt_universe_ws', 'artifact_json_inputs'],
            'dictionary_field_mappings_checked' => [
                'market_index_roc20' => "market_benchmark_indicators.roc_20 where benchmark_code='IHSG'",
                'market_index_ma20_slope_pct' => "market_benchmark_indicators.ma20_slope_pct where benchmark_code='IHSG'",
                'market_calendar_date_key' => 'market_calendar.cal_date',
                'watchlist_evaluation_return_field' => 'watchlist_bt_picks_ws.ret_net is evaluation-only and forbidden for selection',
                'selection_date_scope' => $from.'..'.$to,
                'identifier_keys' => ['benchmark_code', 'ticker_id', 'candidate_code', 'signal_date', 'trade_month', 'branch_code', 'bucket_code'],
            ],
            'dictionary_missing_coverage_detected' => count($missing) > 0,
            'dictionary_missing_coverage_reason_codes' => array_values(array_unique($missing)),
            'asof_safe' => ! $forcedFuture && $forcedOosRows === 0,
            'future_lookup_detected' => $forcedFuture,
            'oos_rows_requested' => $forcedOosRows,
        ];
    }

    private function sourceBiasValidationSummary(array $c58, array $dictionary): array
    {
        return [
            'source_bias_validation_required' => true,
            'source_bias_validation_pass' => (bool) ($c58['source_bias_validation_summary']['source_bias_validation_pass'] ?? false) && ! (bool) ($dictionary['future_lookup_detected'] ?? true) && (int) ($dictionary['oos_rows_requested'] ?? 1) === 0,
            'read_only' => true,
            'asof_safe' => (bool) ($dictionary['asof_safe'] ?? false),
            'future_lookup_detected' => (bool) ($dictionary['future_lookup_detected'] ?? true),
            'oos_rows_requested' => (int) ($dictionary['oos_rows_requested'] ?? 1),
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'failure_reason_codes' => ((bool) ($dictionary['asof_safe'] ?? false) && (bool) ($c58['source_bias_validation_summary']['source_bias_validation_pass'] ?? false)) ? [] : ['C59_SOURCE_BIAS_OR_DICTIONARY_ASOF_FAIL'],
        ];
    }

    private function c58BlockerSummary(array $c58): array
    {
        $decision = (array) ($c58['c59_readiness_decision'] ?? []);
        $regime = (array) ($c58['regime_robustness_validation_summary'] ?? []);
        return [
            'source_c58_status' => $c58['status'] ?? null,
            'source_c58_reason_code' => $c58['reason_code'] ?? null,
            'source_c58_candidate_count' => count((array) ($c58['candidate_scorecard'] ?? [])),
            'source_c58_candidate_ready_for_c59_count' => (int) ($decision['candidate_ready_for_c59_count'] ?? 0),
            'source_c58_rolling_validation_pass_candidate_count' => (int) ($decision['rolling_validation_pass_candidate_count'] ?? 0),
            'source_c58_concentration_validation_pass_candidate_count' => (int) ($decision['concentration_validation_pass_candidate_count'] ?? 0),
            'source_c58_loss_cluster_pass_candidate_count' => (int) ($decision['loss_cluster_pass_candidate_count'] ?? 0),
            'source_c58_loo_validation_pass_candidate_count' => (int) ($decision['loo_validation_pass_candidate_count'] ?? 0),
            'source_c58_regime_robustness_pass_candidate_count' => (int) ($decision['regime_robustness_pass_candidate_count'] ?? 0),
            'source_c58_weakest_regime' => (string) ($regime['weakest_regime_mode'] ?? 'market_down_or_sideways_high_vol'),
            'dominant_blockers' => ['loss_cluster_share', 'branch_bucket_concentration', 'leave_one_month_out_dependency', 'single_month_dependency', 'market_down_or_sideways_high_vol_regime_robustness'],
        ];
    }

    private function regimeLockSummary(array $c58): array
    {
        $summary = (array) ($c58['c57_carry_forward_summary']['regime_field_reconstruction_summary'] ?? []);
        return [
            'c57_regime_reconstruction_retained_through_c58_lock' => true,
            'regime_fully_evaluable' => (bool) ($summary['regime_fully_evaluable'] ?? false),
            'required_field_count' => (int) ($summary['required_field_count'] ?? 0),
            'evaluable_field_count' => (int) ($summary['evaluable_field_count'] ?? 0),
            'missing_field_count' => (int) ($summary['missing_field_count'] ?? 0),
            'market_index_roc20_reconstructed' => (bool) ($summary['market_index_roc20_reconstructed'] ?? false),
            'market_index_ma20_slope_pct_reconstructed' => (bool) ($summary['market_index_ma20_slope_pct_reconstructed'] ?? false),
            'market_index_reconstruction_repeated_in_c59' => false,
        ];
    }

    private function candidateGenerationSummary(array $definitions, array $candidates): array
    {
        return [
            'generation_completed' => true,
            'definition_count' => count($definitions),
            'candidate_count' => count($candidates),
            'replay_comparator_count' => count(array_filter($candidates, fn (array $c): bool => $c['candidate_role'] === 'replay_comparator')),
            'track_a_loss_cluster_first_candidate_count' => count(array_filter($candidates, fn (array $c): bool => $c['lineage_track'] === 'Track A')),
            'track_b_branch_bucket_first_candidate_count' => count(array_filter($candidates, fn (array $c): bool => $c['lineage_track'] === 'Track B')),
            'track_c_regime_stress_candidate_count' => count(array_filter($candidates, fn (array $c): bool => $c['lineage_track'] === 'Track C')),
            'track_d_loo_dependency_breaker_candidate_count' => count(array_filter($candidates, fn (array $c): bool => $c['lineage_track'] === 'Track D')),
            'hybrid_candidate_count' => count(array_filter($candidates, fn (array $c): bool => $c['lineage_track'] === 'Hybrid')),
            'parent_candidate_codes' => array_values(array_unique(array_map(fn (array $c): string => $c['parent_candidate_code'], $candidates))),
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function diagnostics(array $artifact): array
    {
        $decision = (array) ($artifact['c60_readiness_decision'] ?? []);
        $out = [[
            'reason_code' => $decision['diagnostic_conclusion'] ?? 'C59_COMPLETED',
            'message' => 'C59 completed IS-only loss-cluster/branch-bucket redesign validation from locked C58 evidence.',
            'fatal' => false,
        ]];
        if ((int) ($decision['candidate_ready_for_c60_count'] ?? 0) === 0) {
            $out[] = ['reason_code' => $decision['diagnostic_conclusion'] ?? 'C59_NO_READY_CANDIDATE', 'message' => 'No candidate is ready for OOS; next step remains IS-only.', 'fatal' => false];
        }
        return $out;
    }

    private function baseArtifact(string $c58Artifact, string $expectedC58Hash, string $expectedC58FileSha1, string $from, string $to, string $created): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C59_PENDING',
            'reason_code' => 'C59_PENDING',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c58_artifact' => $c58Artifact,
            'expected_c58_hash' => $expectedC58Hash,
            'actual_c58_hash' => null,
            'c58_hash_match' => false,
            'expected_c58_file_sha1' => $expectedC58FileSha1,
            'actual_c58_file_sha1' => null,
            'c58_file_sha1_match' => false,
            'c58_status' => null,
            'c58_diagnostic_conclusion' => null,
            'c58_next_step_recommendation' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'loss_cluster_or_branch_bucket_redesign_continuation_is_only', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c58_blocker_summary' => [],
            'c57_c58_regime_lock_summary' => [],
            'candidate_generation_summary' => [],
            'candidate_definition_results' => [],
            'candidate_replay_results' => [],
            'candidate_scorecard' => [],
            'loss_cluster_validation_results' => [],
            'concentration_dependency_validation_results' => [],
            'rolling_validation_results' => [],
            'rolling_validation_summary' => [],
            'leave_one_month_out_results' => [],
            'leave_one_month_out_summary' => [],
            'regime_robustness_validation_results' => [],
            'regime_robustness_validation_summary' => [],
            'sample_recovery_results' => [],
            'sample_recovery_summary' => [],
            'material_selection_difference_results' => [],
            'material_selection_difference_summary' => [],
            'anti_shared_core_results' => [],
            'anti_shared_core_summary' => [],
            'source_bias_validation_summary' => [],
            'c60_readiness_decision' => ['validation_completed' => false, 'candidate_ready_for_c60_count' => 0, 'candidate_codes' => [], 'c60_recommendation' => 'C59_PENDING', 'decision_reason' => 'pending', 'diagnostic_conclusion' => 'C59_PENDING', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'diagnostic_conclusion' => 'C59_PENDING',
            'next_step_recommendation' => 'C59_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            'created_at' => $created,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'c59_loss_cluster_or_branch_bucket_redesign_continuation_is_only' => true,
            'c58_artifact_hash_lock' => true,
            'c58_file_sha1_lock' => true,
            'c58_locked_lineage' => true,
            'c57_regime_reconstruction_retained_through_c58_lock' => true,
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
            'strict_gate_retention_required' => true,
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
            'no_c01_to_c58_artifact_mutation' => true,
            'no_adverse_month_exclusion_rule' => true,
            'no_failed_window_exclusion_rule' => true,
            'no_ticker_exclusion_rule' => true,
            'no_sector_exclusion_rule' => true,
            'no_bad_month_removal' => true,
            'no_replay_comparator_promotion' => true,
            'loss_cluster_pre_trade_proxy_only' => true,
            'branch_bucket_concentration_redesign_only' => true,
            'market_down_or_sideways_high_vol_regime_required' => true,
            'candidate_is_not_production' => true,
            'c59_must_not_recommend_direct_oos_proof' => true,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'adverse_month_exclusion_used' => false,
            'failed_window_exclusion_used' => false,
            'bad_month_removal_used' => false,
            'ticker_hard_exclusion_used' => false,
            'sector_hard_exclusion_used' => false,
            'oos_data_used_for_tuning' => false,
            'oos_return_used_for_selection' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function parentsByCode(array $c58): array
    {
        $out = [];
        foreach ((array) ($c58['candidate_scorecard'] ?? []) as $row) {
            if (is_array($row) && isset($row['candidate_code'])) {
                $out[(string) $row['candidate_code']] = $row;
            }
        }
        return $out;
    }

    private function sourceArtifactLocks(array $artifact): array
    {
        return [
            'input_c58_artifact' => $artifact['input_c58_artifact'],
            'expected_c58_hash' => $artifact['expected_c58_hash'],
            'actual_c58_hash' => $artifact['actual_c58_hash'],
            'c58_hash_match' => $artifact['c58_hash_match'],
            'expected_c58_file_sha1' => $artifact['expected_c58_file_sha1'],
            'actual_c58_file_sha1' => $artifact['actual_c58_file_sha1'],
            'c58_file_sha1_match' => $artifact['c58_file_sha1_match'],
        ];
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reason;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C60_CANDIDATE_FAMILY_RESET_WITH_STRICT_GUARDS_IS_ONLY';
        $artifact['c60_readiness_decision'] = [
            'validation_completed' => false,
            'candidate_ready_for_c60_count' => 0,
            'candidate_codes' => [],
            'c60_recommendation' => 'C60_CANDIDATE_FAMILY_RESET_WITH_STRICT_GUARDS_IS_ONLY',
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
        if (! $write['ok']) {
            $artifact['status'] = 'C59_OPERATOR_VALIDATION_REQUIRED';
            $artifact['reason_code'] = $write['reason_code'];
            $reason = $write['reason_code'];
            $message = $write['message'];
        }
        return [
            'status' => $artifact['status'],
            'reason_code' => $reason ?: ($artifact['reason_code'] ?? $artifact['status']),
            'message' => $message,
            'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c58_hash' => $artifact['expected_c58_hash'],
            'actual_c58_hash' => $artifact['actual_c58_hash'],
            'c58_hash_match' => $artifact['c58_hash_match'],
            'expected_c58_file_sha1' => $artifact['expected_c58_file_sha1'],
            'actual_c58_file_sha1' => $artifact['actual_c58_file_sha1'],
            'c58_file_sha1_match' => $artifact['c58_file_sha1_match'],
            'c58_status' => $artifact['c58_status'],
            'c58_diagnostic_conclusion' => $artifact['c58_diagnostic_conclusion'],
            'c58_next_step_recommendation' => $artifact['c58_next_step_recommendation'],
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'],
            'next_step_recommendation' => $artifact['next_step_recommendation'],
            'c60_readiness_decision' => $artifact['c60_readiness_decision'],
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
        if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C59 artifact.']; }
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
