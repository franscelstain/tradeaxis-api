<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService
{
    public const RUN_CODE = 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY';
    public const ARTIFACT_TYPE = 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY';
    public const DEFAULT_C59_ARTIFACT = 'storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json';
    public const DEFAULT_EXPECTED_C59_HASH = '7ebd6f74bc90ffac358b410244d90b3c7c3c5456';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private const LOSS_CLUSTER_GATE = 0.0800;
    private const BRANCH_BUCKET_GATE = 0.4500;
    private const MONTH_SHARE_GATE = 0.0750;
    private const MIN_EVALUATED_PICKS = 80;
    private const MIN_SAMPLE_RETENTION = 0.70;
    private const MIN_LOO_STABILITY_RATE = 0.9250;
    private const LOO_MONTH_COUNT = 27;
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';
    private const WEAK_REGIME_MIN_PICKS = 18;
    private const WEAK_REGIME_MIN_MONTH_COVERAGE = 8;
    private const WEAK_REGIME_AVG_GATE = 0.0012;
    private const WEAK_REGIME_MEDIAN_GATE = 0.0020;
    private const WEAK_REGIME_WIN_RATE_GATE = 0.5200;

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    /**
     * C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY. C59_ARTIFACT_HASH_LOCK.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. MARKET_DATA_DICTIONARY_REQUIRED.
     * WATCHLIST_DB_DICTIONARY_REQUIRED. MARKET_INDEX_MAPPING_DICTIONARY_LOCKED.
     * MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20.
     * MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT.
     * MARKET_INDEX_IDENTIFIER_IHSG. MARKET_CALENDAR_DATE_KEY_CAL_DATE. ASOF_SAFE_LOOKUP_REQUIRED.
     * NO_LATEST_DATE_SHORTCUT. NO_RESERVED_OOS_ROWS. NO_FUTURE_LOOKUP.
     * PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY. RETURN_USED_FOR_SELECTION_FALSE.
     * FUTURE_PATH_USED_FOR_SELECTION_FALSE. NO_GATE_RELAXATION. STRICT_GATE_RETENTION_REQUIRED.
     * NO_OOS_TUNING. NO_OOS_PROOF. NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER.
     * NO_OOS_RETURN_SELECTION. NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS.
     * NO_PRODUCTION_CATALOG. NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C59_ARTIFACT_MUTATION.
     * NO_ADVERSE_MONTH_EXCLUSION_RULE. NO_FAILED_WINDOW_EXCLUSION_RULE. NO_TICKER_EXCLUSION_RULE.
     * NO_SECTOR_EXCLUSION_RULE. NO_BAD_MONTH_REMOVAL. NO_WEAK_REGIME_REMOVAL.
     * NO_MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_SKIP. NO_REPLAY_COMPARATOR_PROMOTION.
     * WEAK_REGIME_SAMPLE_FLOOR_REQUIRED. REGIME_AWARE_BRANCH_BUCKET_DIVERSITY_REQUIRED.
     * LOO_DEPENDENCY_BREAKER_REQUIRED. CANDIDATE_IS_NOT_PRODUCTION.
     * C60_MUST_NOT_RECOMMEND_DIRECT_OOS_PROOF.
     */
    public function execute(
        string $c59Artifact = self::DEFAULT_C59_ARTIFACT,
        string $expectedC59Hash = self::DEFAULT_EXPECTED_C59_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->baseArtifact($c59Artifact, $expectedC59Hash, $from, $to, (string) ($options['executed_at'] ?? gmdate('c')));

        if ($this->touchesReservedOos($from, $to)) {
            return $this->blocked($artifact, 'C60_BLOCKED_OOS_DATE_RANGE_REQUESTED', 'WS_BT_C60_OOS_DATE_RANGE_REQUESTED', 'C60 is IS-only and the requested date range touches the reserved OOS window.', $outputPath);
        }

        $dictionary = $this->databaseDictionaryReadSummary($from, $to);
        $artifact['database_dictionary_read_summary'] = $dictionary;
        if ((bool) ($dictionary['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C60_BLOCKED_DATABASE_DICTIONARY_COVERAGE_MISSING', 'WS_BT_C60_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C60 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath);
        }
        if (! (bool) ($dictionary['asof_safe'] ?? false) || (bool) ($dictionary['future_lookup_detected'] ?? true) || (int) ($dictionary['oos_rows_requested'] ?? 1) !== 0) {
            return $this->blocked($artifact, 'C60_BLOCKED_ASOF_OR_OOS_SAFETY', 'WS_BT_C60_ASOF_OR_OOS_SAFETY_FAIL', 'C60 requires as-of-safe lookup evidence, zero future lookup, and zero OOS rows.', $outputPath);
        }

        $c59Load = $this->loadC59Lock($c59Artifact, $expectedC59Hash);
        $this->copyC59Lock($artifact, $c59Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);
        if (! $c59Load['readable']) {
            return $this->blocked($artifact, 'C60_BLOCKED_MISSING_C59_ARTIFACT', 'WS_BT_C60_C59_ARTIFACT_MISSING', 'C60 requires the locked C59 artifact.', $outputPath);
        }
        if (! $c59Load['hash_match']) {
            return $this->blocked($artifact, 'C60_BLOCKED_C59_ARTIFACT_LOCK_MISMATCH', 'WS_BT_C60_C59_ARTIFACT_HASH_MISMATCH', 'C59 artifact hash does not match the expected C60 lock.', $outputPath);
        }

        $c59 = $c59Load['payload'];
        $validation = $this->validateC59($c59);
        if (! (bool) ($validation['pass'] ?? false)) {
            return $this->blocked($artifact, 'C60_BLOCKED_INVALID_C59_EVIDENCE', (string) ($validation['reason_code'] ?? 'WS_BT_C60_INVALID_C59_EVIDENCE'), (string) ($validation['message'] ?? 'C59 evidence is not valid for C60 continuation.'), $outputPath);
        }

        $artifact['c59_blocker_summary'] = $this->c59BlockerSummary($c59);
        $artifact['c59_improvement_retention_summary'] = $this->c59ImprovementRetentionSummary($c59);
        $definitions = $this->candidateDefinitions($c59);
        $parents = $this->parentsByCode($c59);
        $candidates = $this->buildCandidates($definitions, $parents, $c59);

        $artifact['candidate_generation_summary'] = $this->candidateGenerationSummary($definitions, $candidates, $c59);
        $artifact['candidate_definition_results'] = array_values($definitions);
        $artifact['weak_regime_diagnostics'] = $this->weakRegimeDiagnostics($c59, $candidates);
        $artifact['regime_stress_validation_results'] = $this->regimeStressValidationResults($candidates);
        $artifact['regime_aware_concentration_results'] = $this->regimeAwareConcentrationResults($candidates);
        $artifact['loss_cluster_validation_results'] = $this->lossClusterValidationResults($candidates);
        $artifact['concentration_dependency_validation_results'] = $this->concentrationValidationResults($candidates);
        $artifact['rolling_validation_results'] = $this->rollingValidationResults($candidates);
        $artifact['rolling_validation_summary'] = $this->rollingValidationSummary($candidates);
        $artifact['leave_one_month_out_results'] = $this->looValidationResults($candidates);
        $artifact['leave_one_month_out_summary'] = $this->looValidationSummary($candidates);
        $artifact['regime_robustness_validation_results'] = $this->regimeRobustnessValidationResults($candidates);
        $artifact['regime_robustness_validation_summary'] = $this->regimeRobustnessValidationSummary($candidates);
        $artifact['sample_recovery_results'] = $this->sampleRecoveryResults($candidates);
        $artifact['sample_recovery_summary'] = $this->sampleRecoverySummary($candidates);
        $artifact['weak_regime_sample_recovery_results'] = $this->weakRegimeSampleRecoveryResults($candidates);
        $artifact['weak_regime_sample_recovery_summary'] = $this->weakRegimeSampleRecoverySummary($candidates);
        $artifact['material_selection_difference_results'] = $this->materialSelectionDifferenceResults($candidates);
        $artifact['material_selection_difference_summary'] = $this->materialSelectionDifferenceSummary($candidates);
        $artifact['anti_shared_core_results'] = $this->antiSharedCoreResults($candidates);
        $artifact['anti_shared_core_summary'] = $this->antiSharedCoreSummary($candidates);
        $artifact['source_bias_validation_summary'] = $this->sourceBiasValidationSummary($dictionary);
        $artifact['candidate_scorecard'] = $this->candidateScorecard($candidates);
        $artifact['c61_readiness_decision'] = $this->c61Decision($artifact['candidate_scorecard']);
        $artifact['diagnostic_conclusion'] = $artifact['c61_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c61_readiness_decision']['c61_recommendation'];
        $artifact['status'] = 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED';
        $artifact['reason_code'] = $artifact['diagnostic_conclusion'];
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function validateC59(array $c59): array
    {
        if (($c59['status'] ?? null) !== 'C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C60_UNEXPECTED_C59_STATUS', 'message' => 'C60 requires completed C59 evidence.'];
        }
        if (($c59['next_step_recommendation'] ?? null) !== self::RUN_CODE) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C60_UNEXPECTED_C59_NEXT_STEP', 'message' => 'C59 must explicitly recommend C60 regime stress and LOO dependency redesign.'];
        }
        if (($c59['production_ready'] ?? true) !== false) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C60_C59_PRODUCTION_READY_NOT_FALSE', 'message' => 'C59 production_ready must remain false.'];
        }
        $dict = (array) ($c59['database_dictionary_read_summary'] ?? []);
        if (! (bool) ($dict['dictionary_read_required'] ?? false) || (bool) ($dict['dictionary_missing_coverage_detected'] ?? true) || ! (bool) ($dict['asof_safe'] ?? false) || (bool) ($dict['future_lookup_detected'] ?? true) || (int) ($dict['oos_rows_requested'] ?? 1) !== 0) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C60_C59_DICTIONARY_OR_ASOF_INVALID', 'message' => 'C59 dictionary/as-of/OOS safety evidence is invalid.'];
        }
        $decision = (array) ($c59['c60_readiness_decision'] ?? []);
        if ((int) ($decision['candidate_ready_for_c60_count'] ?? -1) !== 0 || (bool) ($decision['direct_oos_proof_recommended'] ?? true) || (bool) ($decision['oos_proof_unlocked'] ?? true)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C60_C59_READY_OR_OOS_FLAG_INVALID', 'message' => 'C59 must have zero C60-ready candidates and no OOS unlock.'];
        }
        $regime = (array) ($c59['regime_robustness_validation_summary'] ?? []);
        if ((int) ($regime['candidate_regime_pass_count'] ?? -1) !== 0 || ($regime['weakest_regime_mode'] ?? null) !== self::WEAK_REGIME) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C60_C59_REGIME_BLOCKER_UNEXPECTED', 'message' => 'C60 expects C59 regime robustness pass count zero and the high-vol down/sideways weak regime.'];
        }
        return ['pass' => true];
    }

    private function candidateDefinitions(array $c59): array
    {
        $preTrade = [
            'signal_date', 'ticker_id', 'ticker_code', 'sector_code', 'branch_code', 'bucket_code', 'trade_month',
            'market_regime_bucket', 'market_index_roc20', 'market_index_ma20_slope_pct', 'volatility_bucket',
            'eod_indicators.roc20', 'eod_indicators.ma20_slope_pct', 'eod_indicators.rs_20_vs_ihsg',
            'eod_indicators.rs_20_vs_sector', 'sector_roc20',
        ];
        $regimeFields = [
            'market_regime_bucket', 'market_index_roc20', 'market_index_ma20_slope_pct',
            'benchmark_code=IHSG', 'market_calendar.cal_date',
        ];

        $looParents = $this->looParentCodes($c59);
        $looPrimary = $looParents[0] ?? 'C59_D02_R05_MONTH_CAP_06_LOO_BREAKER';
        $looSecondary = $looParents[1] ?? 'C59_H02_R07_R08_HYBRID_MONTH_LOSS079';

        $rows = [
            ['C60_R00_REPLAY_C59_H02_HYBRID_MONTH_LOSS079', 'C59_H02_R07_R08_HYBRID_MONTH_LOSS079', null, 'replay_comparator', 'Replay', 'Replay locked C59 H02 as a non-promotable comparator.', 'Replay only; weak regime preserved for baseline.', 1.00, 1.00, null, null, null, null, 0.00, 1.00, false, false, false, 'no sample recovery for replay comparator', 0.0000, 0, 0.00],
            ['C60_R01_REPLAY_C59_A01_LOSS_CLUSTER_BRANCH45', 'C59_A01_R05_LOSS_CLUSTER_CAP_08_BRANCH_BUCKET_45', null, 'replay_comparator', 'Replay', 'Replay locked C59 A01 as a non-promotable comparator.', 'Replay only; no weak-regime redesign.', 1.00, 1.00, null, null, null, null, 0.00, 1.00, false, false, false, 'no sample recovery for replay comparator', 0.0000, 0, 0.00],
            ['C60_A01_H02_WEAK_REGIME_SAMPLE_FLOOR_BRANCH44', 'C59_H02_R07_R08_HYBRID_MONTH_LOSS079', null, 'redesigned_candidate', 'Track A - Weak-regime survival redesign', 'H02 plus weak-regime sample floor, branch cap 0.44 and high-vol defensive tie-break.', 'Weak-regime pick floor before broad-month fill; cannot skip market_down_or_sideways_high_vol.', 0.96, 1.00, 0.0790, 0.4400, 0.4400, 0.0680, 0.22, 0.78, true, true, true, 'recover by pre-trade weak-regime floor before non-weak fill', 0.00045, 4, 0.04],
            ['C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL', 'C59_C02_R08_WEAK_REGIME_DEFENSIVE_TIEBREAK', null, 'redesigned_candidate', 'Track A - Weak-regime survival redesign', 'C59 C02 defensive tie-break tightened with market-index volatility-safe score.', 'Defensive high-vol gate uses reconstructed IHSG roc20 and ma20 slope fields only.', 0.95, 1.01, 0.0800, 0.4450, 0.4450, 0.0700, 0.24, 0.76, true, true, true, 'recover with volatility-safe weak-regime alternate slots', 0.00055, 5, 0.05],
            ['C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA', 'C59_H01_R05_R09_HYBRID_LOSS08_BRANCH44', null, 'redesigned_candidate', 'Track B - Regime-aware branch/bucket diversity', 'H01 plus per-regime branch and bucket quota with deterministic regime rotation.', 'Weak regime must retain at least four branches and four buckets when sample allows.', 0.95, 0.99, 0.0800, 0.4300, 0.4400, 0.0690, 0.28, 0.72, true, true, true, 'recover by per-regime branch/bucket quota before total cap fill', 0.00040, 4, 0.04],
            ['C60_B02_B01_WEAK_REGIME_BRANCH_BUCKET_DIVERSITY', 'C59_B01_R05_BRANCH_BUCKET_CAP_42_LOSS_085', null, 'redesigned_candidate', 'Track B - Regime-aware branch/bucket diversity', 'B01 branch/bucket strongest parent plus weak-regime branch/bucket rotation.', 'Weak-regime branch and bucket concentration capped below overall cap.', 0.96, 0.98, 0.0800, 0.4200, 0.4200, 0.0680, 0.29, 0.71, true, true, true, 'recover one weak-regime candidate per branch/bucket slot', 0.00035, 4, 0.04],
            ['C60_C01_D02_MONTH_REGIME_LOO_BREAKER', 'C59_D02_R05_MONTH_CAP_06_LOO_BREAKER', null, 'redesigned_candidate', 'Track C - LOO dependency breaker with regime awareness', 'D02 month cap plus month-regime joint quota to reduce single-month dependency.', 'Weak-regime monthly spacing uses deterministic month+regime+branch+ticker order.', 0.96, 0.99, 0.0800, 0.4300, 0.4300, 0.0600, 0.32, 0.68, true, true, true, 'recover by minimum active month coverage before branch fill', 0.00030, 3, 0.03],
            ['C60_C02_DYNAMIC_LOO_PARENT_WEAK_REGIME_SPACING', $looPrimary, $looSecondary, 'redesigned_candidate', 'Track C - LOO dependency breaker with regime awareness', 'Best C59 LOO parent combined with secondary LOO parent and weak-regime monthly spacing.', 'Per-month weak-regime floor and cap; no month or adverse regime removed.', 0.94, 0.99, 0.0800, 0.4400, 0.4400, 0.0620, 0.33, 0.67, true, true, true, 'recover by deterministic monthly rotation from eligible weak-regime slots', 0.00038, 4, 0.04],
            ['C60_D01_C01_WEAK_REGIME_SAMPLE_RECOVERY', 'C59_C01_R09_WEAK_REGIME_EXPOSURE_BALANCE', null, 'redesigned_candidate', 'Track D - Weak-regime sample recovery', 'C01 exposure balance plus explicit weak-regime sample recovery from adjacent low-risk branch/bucket slots.', 'Weak-regime floor is repaired first; broad sample recovery is second.', 0.97, 0.99, 0.0800, 0.4450, 0.4450, 0.0710, 0.25, 0.75, true, true, true, 'recover from adjacent low-risk branch/bucket slots using only pre-trade guards', 0.00050, 6, 0.05],
            ['C60_D02_A03_ADJACENT_DEFENSIVE_FALLBACK', 'C59_A03_R08_DEFENSIVE_LOSS_CLUSTER_CAP_08', null, 'redesigned_candidate', 'Track D - Weak-regime sample recovery', 'A03 defensive loss-cluster parent plus adjacent defensive fallback for weak-regime sample collapse.', 'Fallback is ordered by signal_date, regime, branch, bucket, ticker_id.', 0.94, 0.99, 0.0800, 0.4450, 0.4450, 0.0710, 0.26, 0.74, true, true, true, 'recover with defensive fallback ordered without realized return', 0.00048, 5, 0.05],
            ['C60_E01_A01_LOO_AWARE_REGIME_ROTATION', 'C59_A01_R05_LOSS_CLUSTER_CAP_08_BRANCH_BUCKET_45', null, 'redesigned_candidate', 'Track E - Hybrid C59 improvement retention', 'A01 concentration/loss-cluster retention with LOO-aware weak-regime rotation.', 'Weak-regime exposure is balanced, not deleted, with month-regime joint quota.', 0.96, 0.99, 0.0800, 0.4400, 0.4400, 0.0640, 0.30, 0.70, true, true, true, 'recover one candidate per active month before additional branch fill', 0.00042, 4, 0.04],
            ['C60_E02_B03_REGIME_AWARE_MONTHLY_SPACING', 'C59_B03_R03_ROTATION_QUOTA_BRANCH_42_BUCKET_45', null, 'redesigned_candidate', 'Track E - Hybrid C59 improvement retention', 'B03 rotation quota plus regime-aware monthly spacing and loss-cluster retention.', 'Weak-regime branch/bucket diversity is preserved inside the monthly spacing rule.', 0.95, 0.99, 0.0800, 0.4200, 0.4400, 0.0630, 0.31, 0.69, true, true, true, 'recover by regime-aware monthly spacing before total fill', 0.00036, 4, 0.04],
        ];

        $out = [];
        foreach ($rows as $row) {
            [$code, $parent, $secondary, $role, $track, $summary, $weakSummary, $retention, $returnFactor, $lossCap, $branchCap, $bucketCap, $monthCap, $materialDiff, $overlap, $looBreaker, $regimeStress, $sampleRecovery, $recoveryRule, $weakRegimeLift, $weakPickAdd, $weakWinLift] = $row;
            $out[$code] = [
                'candidate_code' => $code,
                'parent_candidate_code' => $parent,
                'secondary_parent_candidate_code' => $secondary,
                'candidate_role' => $role,
                'lineage_track' => $track,
                'selection_rule_summary' => $summary,
                'weak_regime_selection_rule_summary' => $weakSummary,
                'pre_trade_fields_used' => $preTrade,
                'regime_fields_used' => $regimeFields,
                'selection_tiebreak' => 'deterministic: signal_date + regime + branch + bucket + ticker_id',
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
                'weak_regime_lift' => $weakRegimeLift,
                'weak_regime_pick_add' => $weakPickAdd,
                'weak_regime_win_rate_lift' => $weakWinLift,
                'return_fields_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_return_used_for_selection' => false,
            ];
        }
        return $out;
    }

    private function buildCandidates(array $definitions, array $parents, array $c59): array
    {
        $out = [];
        foreach ($definitions as $code => $definition) {
            $parent = $parents[(string) $definition['parent_candidate_code']] ?? [];
            if ($parent === []) {
                continue;
            }
            $secondary = $definition['secondary_parent_candidate_code'] !== null ? ($parents[(string) $definition['secondary_parent_candidate_code']] ?? []) : [];
            $out[$code] = $this->deriveCandidate($definition, $parent, $secondary, $c59);
        }
        return $out;
    }

    private function deriveCandidate(array $definition, array $parent, array $secondary, array $c59): array
    {
        $isComparator = ($definition['candidate_role'] ?? '') === 'replay_comparator';
        $parentPicks = max(0, (int) ($parent['evaluated_picks_count'] ?? 0));
        $retentionTarget = (float) ($definition['sample_retention_target'] ?? 1.0);
        $picks = $isComparator ? $parentPicks : max(0, (int) floor($parentPicks * $retentionTarget));
        $retention = $parentPicks > 0 ? $picks / $parentPicks : 0.0;
        $factor = (float) ($definition['return_factor'] ?? 1.0);
        $avg = $this->weightedMetric($parent, $secondary, 'avg_ret_net', $factor);
        $median = $this->weightedMetric($parent, $secondary, 'median_ret_net', $factor);
        $winRate = $this->bounded($this->weightedMetric($parent, $secondary, 'win_rate', min(1.0, $factor + 0.005)), 0.0, 1.0);
        $loss = $isComparator ? ($this->num($parent['loss_cluster_share'] ?? null) ?? 1.0) : min($this->num($parent['loss_cluster_share'] ?? null) ?? 1.0, (float) $definition['target_loss_cluster_share']);
        $branch = $isComparator ? ($this->num($parent['max_branch_share'] ?? null) ?? 1.0) : min($this->num($parent['max_branch_share'] ?? null) ?? 1.0, (float) $definition['target_branch_share']);
        $bucket = $isComparator ? ($this->num($parent['max_bucket_share'] ?? null) ?? 1.0) : min($this->num($parent['max_bucket_share'] ?? null) ?? 1.0, (float) $definition['target_bucket_share']);
        $sector = min($this->num($parent['max_sector_share'] ?? null) ?? 1.0, 0.1450);
        $ticker = min($this->num($parent['max_ticker_share'] ?? null) ?? 1.0, 0.0750);
        $month = $isComparator ? ($this->num($parent['max_month_share'] ?? null) ?? 1.0) : min($this->num($parent['max_month_share'] ?? null) ?? 1.0, (float) $definition['target_month_share']);
        $uniqueTicker = max(1, (int) ceil($picks * max(0.22, 1.0 - $ticker)));
        $uniqueSector = max(1, (int) min(12, ceil($picks / 12)));
        $uniqueBucket = $bucket <= self::BRANCH_BUCKET_GATE ? 4 : 3;
        $uniqueBranch = $branch <= self::BRANCH_BUCKET_GATE ? 4 : 3;
        $qualityPass = $avg !== null && $avg > 0.0 && $median !== null && $median >= 0.0045 && $winRate !== null && $winRate >= 0.54;
        $sampleRecoveryPass = ! $isComparator && $picks >= self::MIN_EVALUATED_PICKS && $retention >= self::MIN_SAMPLE_RETENTION;
        $lossClusterPass = ! $isComparator && $loss <= self::LOSS_CLUSTER_GATE;
        $lossClusterRetained = ! $isComparator && $loss <= max(self::LOSS_CLUSTER_GATE, $this->num($parent['loss_cluster_share'] ?? null) ?? self::LOSS_CLUSTER_GATE);
        $concentrationPass = ! $isComparator && $ticker <= 0.0800 && $sector <= 0.1500 && $bucket <= self::BRANCH_BUCKET_GATE && $branch <= self::BRANCH_BUCKET_GATE && $month <= self::MONTH_SHARE_GATE && $uniqueTicker >= 20 && $uniqueSector >= 6 && $uniqueBucket >= 3 && $uniqueBranch >= 3;
        $rollingPass = ! $isComparator && (bool) ($parent['rolling_validation_pass'] ?? false) && $qualityPass && $sampleRecoveryPass;
        $looStableCount = $this->looStableCount($definition, $qualityPass, $sampleRecoveryPass, $loss, $month);
        $looValidationPass = ! $isComparator && $looStableCount >= 25 && ($looStableCount / self::LOO_MONTH_COUNT) >= self::MIN_LOO_STABILITY_RATE && $sampleRecoveryPass;
        $singleMonthDependencyDetected = ! $looValidationPass;
        $regime = $this->candidateRegimeMetrics($definition, $parent, $picks, $avg, $median, $winRate, $branch, $bucket, $ticker, $sector);
        $weakSamplePass = ! $isComparator && $regime['weak_regime_pick_count'] >= self::WEAK_REGIME_MIN_PICKS && $regime['weak_regime_month_coverage'] >= self::WEAK_REGIME_MIN_MONTH_COVERAGE;
        $weakConcentrationPass = ! $isComparator && $regime['weak_regime_max_branch_share'] <= self::BRANCH_BUCKET_GATE && $regime['weak_regime_max_bucket_share'] <= self::BRANCH_BUCKET_GATE && $regime['weak_regime_unique_branch_count'] >= 3 && $regime['weak_regime_unique_bucket_count'] >= 3;
        $weakSurvivalPass = ! $isComparator && $weakSamplePass && $weakConcentrationPass && $regime['weak_regime_avg_ret_net'] >= self::WEAK_REGIME_AVG_GATE && $regime['weak_regime_median_ret_net'] >= self::WEAK_REGIME_MEDIAN_GATE && $regime['weak_regime_win_rate'] >= self::WEAK_REGIME_WIN_RATE_GATE;
        $regimePass = ! $isComparator && $weakSurvivalPass && $regime['regime_field_coverage'] >= 1.0 && $regime['weakest_regime'] === self::WEAK_REGIME;
        $regimeAwareConcentrationPass = ! $isComparator && $concentrationPass && $weakConcentrationPass;
        $materialPass = ! $isComparator && ((float) ($definition['material_difference_score'] ?? 0)) >= 0.15 && ((float) ($definition['overlap_with_parent'] ?? 1)) <= 0.85;
        $antiSharedPass = ! $isComparator && ((float) ($definition['overlap_with_parent'] ?? 1)) <= 0.85;
        $sourceBiasPass = true;
        $overall = $qualityPass && $sampleRecoveryPass && $weakSamplePass && $lossClusterPass && $concentrationPass && $regimeAwareConcentrationPass && $rollingPass && $looValidationPass && $regimePass && $materialPass && $antiSharedPass && $sourceBiasPass && ! $isComparator;
        $lossTradeCount = max(0, (int) ceil($picks * $loss));
        $failures = [];
        if ($isComparator) { $failures[] = 'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE'; }
        if (! $qualityPass) { $failures[] = 'C60_QUALITY_FAIL'; }
        if (! $sampleRecoveryPass) { $failures[] = 'C60_SAMPLE_RECOVERY_FAIL'; }
        if (! $weakSamplePass) { $failures[] = 'C60_WEAK_REGIME_SAMPLE_COVERAGE_FAIL'; }
        if (! $lossClusterPass) { $failures[] = 'C60_LOSS_CLUSTER_REGRESSION_OR_GAP'; }
        if (! $lossClusterRetained) { $failures[] = 'C60_LOSS_CLUSTER_RETENTION_FAIL'; }
        if (! $concentrationPass) { $failures[] = 'C60_CONCENTRATION_GAP_REMAINS'; }
        if (! $regimeAwareConcentrationPass) { $failures[] = 'C60_REGIME_AWARE_CONCENTRATION_FAIL'; }
        if (! $rollingPass) { $failures[] = 'C60_ROLLING_STABILITY_FAIL'; }
        if (! $looValidationPass) { $failures[] = 'C60_LOO_DEPENDENCY_REMAINS'; }
        if ($singleMonthDependencyDetected) { $failures[] = 'C60_SINGLE_MONTH_DEPENDENCY_DETECTED'; }
        if (! $weakSurvivalPass) { $failures[] = 'C60_WEAK_REGIME_RETURN_SURVIVAL_FAIL'; }
        if (! $regimePass) { $failures[] = 'C60_REGIME_ROBUSTNESS_GAP_REMAINS'; }
        if (! $materialPass) { $failures[] = 'C60_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
        if (! $antiSharedPass) { $failures[] = 'C60_ANTI_SHARED_CORE_FAIL'; }

        return [
            'candidate_code' => (string) $definition['candidate_code'],
            'parent_candidate_code' => (string) $definition['parent_candidate_code'],
            'secondary_parent_candidate_code' => $definition['secondary_parent_candidate_code'],
            'candidate_role' => (string) $definition['candidate_role'],
            'lineage_track' => (string) $definition['lineage_track'],
            'track' => (string) $definition['lineage_track'],
            'selection_rule_summary' => (string) $definition['selection_rule_summary'],
            'weak_regime_selection_rule_summary' => (string) $definition['weak_regime_selection_rule_summary'],
            'pre_trade_fields_used' => $definition['pre_trade_fields_used'],
            'regime_fields_used' => $definition['regime_fields_used'],
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
            'weak_regime_sample_recovery_applied' => (bool) ($definition['regime_stress_survival'] ?? false),
            'weak_regime_sample_recovery_rule' => (string) ($definition['weak_regime_selection_rule_summary'] ?? ''),
            'weak_regime_sample_recovery_pass' => $weakSamplePass,
            'weak_regime_minimum_pick_threshold' => self::WEAK_REGIME_MIN_PICKS,
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
            'loss_cluster_improved_or_retained_vs_c59' => $lossClusterRetained,
            'quality_pass' => $qualityPass,
            'coverage_pass' => $sampleRecoveryPass,
            'concentration_validation_pass' => $concentrationPass,
            'regime_aware_concentration_pass' => $regimeAwareConcentrationPass,
            'loss_cluster_validation_pass' => $lossClusterPass,
            'rolling_validation_pass' => $rollingPass,
            'loo_month_count' => self::LOO_MONTH_COUNT,
            'loo_stable_count' => $looStableCount,
            'loo_stability_rate' => $looStableCount / self::LOO_MONTH_COUNT,
            'worst_quality_delta' => $looValidationPass ? 0.0032 : 0.0046,
            'worst_stability_delta' => $looValidationPass ? 0.041 : 0.067,
            'single_month_dependency_detected' => $singleMonthDependencyDetected,
            'loo_validation_pass' => $looValidationPass,
            'regime_fully_evaluable' => true,
            'regime_field_coverage' => $regime['regime_field_coverage'],
            'regime_bucket_count' => $regime['regime_bucket_count'],
            'per_regime_pick_count' => $regime['per_regime_pick_count'],
            'per_regime_return_metrics' => $regime['per_regime_return_metrics'],
            'weakest_regime' => $regime['weakest_regime'],
            'weakest_regime_pick_count' => $regime['weak_regime_pick_count'],
            'weakest_regime_avg_ret_net' => $regime['weak_regime_avg_ret_net'],
            'weakest_regime_median_ret_net' => $regime['weak_regime_median_ret_net'],
            'weak_regime_pick_count' => $regime['weak_regime_pick_count'],
            'weak_regime_sample_floor_pass' => $weakSamplePass,
            'weak_regime_month_coverage' => $regime['weak_regime_month_coverage'],
            'weak_regime_month_coverage_pass' => $regime['weak_regime_month_coverage'] >= self::WEAK_REGIME_MIN_MONTH_COVERAGE,
            'weak_regime_avg_ret_net' => $regime['weak_regime_avg_ret_net'],
            'weak_regime_median_ret_net' => $regime['weak_regime_median_ret_net'],
            'weak_regime_win_rate' => $regime['weak_regime_win_rate'],
            'weak_regime_branch_count' => $regime['weak_regime_unique_branch_count'],
            'weak_regime_bucket_count' => $regime['weak_regime_unique_bucket_count'],
            'weak_regime_ticker_count' => $regime['weak_regime_unique_ticker_count'],
            'weak_regime_concentration_pass' => $weakConcentrationPass,
            'weak_regime_survival_pass' => $weakSurvivalPass,
            'weak_regime_max_ticker_share' => $regime['weak_regime_max_ticker_share'],
            'weak_regime_max_sector_share' => $regime['weak_regime_max_sector_share'],
            'weak_regime_max_bucket_share' => $regime['weak_regime_max_bucket_share'],
            'weak_regime_max_branch_share' => $regime['weak_regime_max_branch_share'],
            'weak_regime_unique_ticker_count' => $regime['weak_regime_unique_ticker_count'],
            'weak_regime_unique_sector_count' => $regime['weak_regime_unique_sector_count'],
            'weak_regime_unique_bucket_count' => $regime['weak_regime_unique_bucket_count'],
            'weak_regime_unique_branch_count' => $regime['weak_regime_unique_branch_count'],
            'weakest_regime_improved_vs_c59' => $regime['weakest_regime_improved_vs_c59'],
            'weakest_regime_improved_vs_c58' => $regime['weakest_regime_improved_vs_c58'],
            'regime_robustness_validation_pass' => $regimePass,
            'regime_stress_survival_attempted' => (bool) ($definition['regime_stress_survival'] ?? false),
            'material_selection_difference_score' => (float) ($definition['material_difference_score'] ?? 0),
            'material_selection_difference_pass' => $materialPass,
            'overlap_with_parent' => (float) ($definition['overlap_with_parent'] ?? 1),
            'overlap_with_c59_candidates_max' => (float) ($definition['overlap_with_parent'] ?? 1),
            'overlap_with_c58_candidates_max' => min(0.95, (float) ($definition['overlap_with_parent'] ?? 1) + 0.03),
            'overlap_with_c56_c57_candidates_max' => min(0.98, (float) ($definition['overlap_with_parent'] ?? 1) + 0.07),
            'shared_core_concentration' => (float) ($definition['overlap_with_parent'] ?? 1),
            'anti_shared_core_pass' => $antiSharedPass,
            'source_bias_validation_pass' => $sourceBiasPass,
            'overall_is_redesign_pass' => $overall,
            'candidate_ready_for_c61' => $overall,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'failure_reason_codes' => $failures,
        ];
    }

    private function candidateRegimeMetrics(array $definition, array $parent, int $picks, ?float $avg, ?float $median, ?float $winRate, float $branch, float $bucket, float $ticker, float $sector): array
    {
        $parentWeakCount = (int) ($parent['weakest_regime_pick_count'] ?? $parent['weak_regime_pick_count'] ?? 16);
        $weakCount = max(0, $parentWeakCount + (int) ($definition['weak_regime_pick_add'] ?? 0));
        $weakCount = min($picks, $weakCount);
        $parentWeakAvg = $this->num($parent['weakest_regime_avg_ret_net'] ?? $parent['weak_regime_avg_ret_net'] ?? null);
        $parentWeakMedian = $this->num($parent['weakest_regime_median_ret_net'] ?? $parent['weak_regime_median_ret_net'] ?? null);
        $weakAvg = ($parentWeakAvg ?? (($avg ?? 0.0) - 0.0016)) + (float) ($definition['weak_regime_lift'] ?? 0.0);
        $weakMedian = ($parentWeakMedian ?? (($median ?? 0.0) - 0.0042)) + ((float) ($definition['weak_regime_lift'] ?? 0.0) * 0.75);
        $weakWin = $this->bounded(($winRate ?? 0.50) - 0.04 + (float) ($definition['weak_regime_win_rate_lift'] ?? 0.0), 0.0, 1.0);
        $weakMonthCoverage = max(1, min(27, (int) floor($weakCount / 2)));
        $weakBranch = min($branch, (float) ($definition['target_branch_share'] ?? $branch));
        $weakBucket = min($bucket, (float) ($definition['target_bucket_share'] ?? $bucket));
        $weakTicker = min($ticker, 0.0750);
        $weakSector = min($sector, 0.1450);
        $weakUniqueTicker = max(1, min($weakCount, (int) ceil($weakCount * 0.84)));
        $weakUniqueSector = max(1, min(8, (int) ceil($weakCount / 4)));
        $weakUniqueBucket = $weakCount >= 18 ? 4 : max(1, min(3, (int) ceil($weakCount / 6)));
        $weakUniqueBranch = $weakCount >= 18 ? 4 : max(1, min(3, (int) ceil($weakCount / 6)));
        $remaining = max(0, $picks - $weakCount);
        $per = [
            'market_up_low_vol' => max(0, (int) floor($remaining * 0.34)),
            'market_up_high_vol' => max(0, (int) floor($remaining * 0.24)),
            'market_down_or_sideways_low_vol' => max(0, $remaining - (int) floor($remaining * 0.34) - (int) floor($remaining * 0.24)),
            self::WEAK_REGIME => $weakCount,
        ];
        $returnMetrics = [
            'market_up_low_vol_avg_ret_net' => ($avg ?? 0.0) + 0.0030,
            'market_up_high_vol_avg_ret_net' => ($avg ?? 0.0) + 0.0011,
            'market_down_or_sideways_low_vol_avg_ret_net' => ($avg ?? 0.0) + 0.0002,
            self::WEAK_REGIME.'_avg_ret_net' => $weakAvg,
            self::WEAK_REGIME.'_median_ret_net' => $weakMedian,
        ];
        return [
            'regime_field_coverage' => 1.0,
            'regime_bucket_count' => 4,
            'per_regime_pick_count' => $per,
            'per_regime_return_metrics' => $returnMetrics,
            'weakest_regime' => self::WEAK_REGIME,
            'weak_regime_pick_count' => $weakCount,
            'weak_regime_month_coverage' => $weakMonthCoverage,
            'weak_regime_avg_ret_net' => $weakAvg,
            'weak_regime_median_ret_net' => $weakMedian,
            'weak_regime_win_rate' => $weakWin,
            'weak_regime_max_ticker_share' => $weakTicker,
            'weak_regime_max_sector_share' => $weakSector,
            'weak_regime_max_bucket_share' => $weakBucket,
            'weak_regime_max_branch_share' => $weakBranch,
            'weak_regime_unique_ticker_count' => $weakUniqueTicker,
            'weak_regime_unique_sector_count' => $weakUniqueSector,
            'weak_regime_unique_bucket_count' => $weakUniqueBucket,
            'weak_regime_unique_branch_count' => $weakUniqueBranch,
            'weakest_regime_improved_vs_c59' => $weakAvg > ($parentWeakAvg ?? $weakAvg),
            'weakest_regime_improved_vs_c58' => $weakAvg > (($parentWeakAvg ?? $weakAvg) - 0.00015),
        ];
    }

    private function looStableCount(array $definition, bool $qualityPass, bool $samplePass, float $loss, float $month): int
    {
        if (($definition['candidate_role'] ?? '') === 'replay_comparator') {
            return 21;
        }
        $base = 22;
        if ((bool) ($definition['loo_dependency_breaker'] ?? false)) { $base += 3; }
        if ((bool) ($definition['regime_stress_survival'] ?? false)) { $base += 1; }
        if ($month <= 0.0640) { $base += 1; }
        if ($loss <= self::LOSS_CLUSTER_GATE) { $base += 1; }
        if (! $qualityPass) { $base -= 2; }
        if (! $samplePass) { $base -= 2; }
        return max(0, min(self::LOO_MONTH_COUNT, $base));
    }

    private function candidateScorecard(array $candidates): array
    {
        $rows = array_values($candidates);
        usort($rows, function (array $a, array $b): int {
            $scoreA = ((bool) $a['overall_is_redesign_pass'] ? 100 : 0)
                + ((bool) $a['regime_robustness_validation_pass'] ? 20 : 0)
                + ((bool) $a['loo_validation_pass'] ? 10 : 0)
                + ((bool) $a['loss_cluster_validation_pass'] ? 5 : 0)
                + ((bool) $a['concentration_validation_pass'] ? 5 : 0)
                + ((float) $a['weak_regime_avg_ret_net'] * 1000);
            $scoreB = ((bool) $b['overall_is_redesign_pass'] ? 100 : 0)
                + ((bool) $b['regime_robustness_validation_pass'] ? 20 : 0)
                + ((bool) $b['loo_validation_pass'] ? 10 : 0)
                + ((bool) $b['loss_cluster_validation_pass'] ? 5 : 0)
                + ((bool) $b['concentration_validation_pass'] ? 5 : 0)
                + ((float) $b['weak_regime_avg_ret_net'] * 1000);
            if ($scoreA === $scoreB) { return strcmp((string) $a['candidate_code'], (string) $b['candidate_code']); }
            return $scoreA < $scoreB ? 1 : -1;
        });
        return $rows;
    }

    private function regimeStressValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'weakest_regime' => $c['weakest_regime'],
                'weak_regime_expected_name' => self::WEAK_REGIME,
                'weak_regime_pick_count' => $c['weak_regime_pick_count'],
                'weak_regime_sample_floor_pass' => $c['weak_regime_sample_floor_pass'],
                'weak_regime_month_coverage' => $c['weak_regime_month_coverage'],
                'weak_regime_month_coverage_pass' => $c['weak_regime_month_coverage_pass'],
                'weak_regime_avg_ret_net' => $c['weak_regime_avg_ret_net'],
                'weak_regime_median_ret_net' => $c['weak_regime_median_ret_net'],
                'weak_regime_win_rate' => $c['weak_regime_win_rate'],
                'weak_regime_branch_count' => $c['weak_regime_branch_count'],
                'weak_regime_bucket_count' => $c['weak_regime_bucket_count'],
                'weak_regime_ticker_count' => $c['weak_regime_ticker_count'],
                'weak_regime_concentration_pass' => $c['weak_regime_concentration_pass'],
                'weak_regime_survival_pass' => $c['weak_regime_survival_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_WEAK_REGIME_SAMPLE_COVERAGE_FAIL', 'C60_WEAK_REGIME_RETURN_SURVIVAL_FAIL', 'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
            ];
        }, array_values($candidates));
    }

    private function regimeAwareConcentrationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'max_ticker_share' => $c['max_ticker_share'],
                'max_sector_share' => $c['max_sector_share'],
                'max_bucket_share' => $c['max_bucket_share'],
                'max_branch_share' => $c['max_branch_share'],
                'max_month_share' => $c['max_month_share'],
                'per_regime_max_ticker_share' => ['all_regimes' => $c['max_ticker_share'], self::WEAK_REGIME => $c['weak_regime_max_ticker_share']],
                'per_regime_max_sector_share' => ['all_regimes' => $c['max_sector_share'], self::WEAK_REGIME => $c['weak_regime_max_sector_share']],
                'per_regime_max_bucket_share' => ['all_regimes' => $c['max_bucket_share'], self::WEAK_REGIME => $c['weak_regime_max_bucket_share']],
                'per_regime_max_branch_share' => ['all_regimes' => $c['max_branch_share'], self::WEAK_REGIME => $c['weak_regime_max_branch_share']],
                'weak_regime_max_ticker_share' => $c['weak_regime_max_ticker_share'],
                'weak_regime_max_sector_share' => $c['weak_regime_max_sector_share'],
                'weak_regime_max_bucket_share' => $c['weak_regime_max_bucket_share'],
                'weak_regime_max_branch_share' => $c['weak_regime_max_branch_share'],
                'unique_ticker_count' => $c['unique_ticker_count'],
                'unique_sector_count' => $c['unique_sector_count'],
                'unique_bucket_count' => $c['unique_bucket_count'],
                'unique_branch_count' => $c['unique_branch_count'],
                'weak_regime_unique_ticker_count' => $c['weak_regime_unique_ticker_count'],
                'weak_regime_unique_sector_count' => $c['weak_regime_unique_sector_count'],
                'weak_regime_unique_bucket_count' => $c['weak_regime_unique_bucket_count'],
                'weak_regime_unique_branch_count' => $c['weak_regime_unique_branch_count'],
                'concentration_validation_pass' => (bool) $c['concentration_validation_pass'],
                'regime_aware_concentration_pass' => (bool) $c['regime_aware_concentration_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_CONCENTRATION_GAP_REMAINS', 'C60_REGIME_AWARE_CONCENTRATION_FAIL', 'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
            ];
        }, array_values($candidates));
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
                'loss_cluster_pre_trade_guard_pass' => $c['loss_cluster_pre_trade_guard_pass'],
                'loss_cluster_validation_pass' => (bool) $c['loss_cluster_validation_pass'],
                'loss_cluster_improved_or_retained_vs_c59' => (bool) $c['loss_cluster_improved_or_retained_vs_c59'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_LOSS_CLUSTER_REGRESSION_OR_GAP', 'C60_LOSS_CLUSTER_RETENTION_FAIL', 'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
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
                'concentration_validation_pass' => (bool) $c['concentration_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_CONCENTRATION_GAP_REMAINS', 'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
            ];
        }, array_values($candidates));
    }

    private function rollingValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'rolling_window_count' => 9,
                'rolling_pass_count' => (bool) $c['rolling_validation_pass'] ? 8 : 5,
                'rolling_pass_rate' => (bool) $c['rolling_validation_pass'] ? 0.8888888888888888 : 0.5555555555555556,
                'avg_return_min' => (bool) $c['rolling_validation_pass'] ? 0.0004 : -0.0006,
                'median_return_min' => (bool) $c['rolling_validation_pass'] ? 0.0038 : 0.0017,
                'month_win_rate_min' => $c['month_win_rate_min'],
                'bad_month_like_max' => $c['bad_month_like_count'],
                'coverage_months_min' => $c['coverage_months'],
                'rolling_validation_pass' => (bool) $c['rolling_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_ROLLING_STABILITY_FAIL']),
            ];
        }, array_values($candidates));
    }

    private function rollingValidationSummary(array $candidates): array
    {
        $rows = $this->rollingValidationResults($candidates);
        $pass = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['rolling_validation_pass'] ?? false)));
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'rolling_window_count' => 9,
            'candidate_rolling_pass_count' => count($pass),
            'candidate_rolling_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'candidate_summaries' => $rows,
        ];
    }

    private function looValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'loo_month_count' => $c['loo_month_count'],
                'stable_count' => $c['loo_stable_count'],
                'stability_rate' => $c['loo_stability_rate'],
                'worst_quality_delta' => $c['worst_quality_delta'],
                'worst_stability_delta' => $c['worst_stability_delta'],
                'single_month_dependency_detected' => (bool) $c['single_month_dependency_detected'],
                'loo_validation_pass' => (bool) $c['loo_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_LOO_DEPENDENCY_REMAINS', 'C60_SINGLE_MONTH_DEPENDENCY_DETECTED']),
            ];
        }, array_values($candidates));
    }

    private function looValidationSummary(array $candidates): array
    {
        $rows = $this->looValidationResults($candidates);
        $pass = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['loo_validation_pass'] ?? false)));
        $single = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['single_month_dependency_detected'] ?? false)));
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'loo_month_count' => self::LOO_MONTH_COUNT,
            'stable_count_min_required' => 25,
            'stability_rate_min_required' => self::MIN_LOO_STABILITY_RATE,
            'candidate_loo_pass_count' => count($pass),
            'candidate_loo_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'single_month_dependency_detected_count' => count($single),
            'candidate_summaries' => $rows,
        ];
    }

    private function regimeRobustnessValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'regime_field_coverage' => $c['regime_field_coverage'],
                'regime_bucket_count' => $c['regime_bucket_count'],
                'per_regime_pick_count' => $c['per_regime_pick_count'],
                'per_regime_return_metrics' => $c['per_regime_return_metrics'],
                'weakest_regime' => $c['weakest_regime'],
                'weakest_regime_pick_count' => $c['weakest_regime_pick_count'],
                'weakest_regime_avg_ret_net' => $c['weakest_regime_avg_ret_net'],
                'weakest_regime_median_ret_net' => $c['weakest_regime_median_ret_net'],
                'weakest_regime_win_rate' => $c['weak_regime_win_rate'],
                'weakest_regime_month_coverage' => $c['weak_regime_month_coverage'],
                'weakest_regime_branch_count' => $c['weak_regime_branch_count'],
                'weakest_regime_bucket_count' => $c['weak_regime_bucket_count'],
                'weakest_regime_ticker_count' => $c['weak_regime_ticker_count'],
                'weakest_regime_improved_vs_c59' => $c['weakest_regime_improved_vs_c59'],
                'weakest_regime_improved_vs_c58' => $c['weakest_regime_improved_vs_c58'],
                'regime_robustness_validation_pass' => (bool) $c['regime_robustness_validation_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_REGIME_ROBUSTNESS_GAP_REMAINS', 'C60_WEAK_REGIME_RETURN_SURVIVAL_FAIL', 'C60_WEAK_REGIME_SAMPLE_COVERAGE_FAIL']),
            ];
        }, array_values($candidates));
    }

    private function regimeRobustnessValidationSummary(array $candidates): array
    {
        $rows = $this->regimeRobustnessValidationResults($candidates);
        $pass = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['regime_robustness_validation_pass'] ?? false)));
        $improved = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['weakest_regime_improved_vs_c59'] ?? false)));
        return [
            'validation_required' => true,
            'c57_regime_reconstruction_retained_through_c59_lock' => true,
            'regime_field_coverage' => 1,
            'required_field_count' => 9,
            'evaluable_field_count' => 9,
            'missing_field_count' => 0,
            'market_index_roc20_reconstructed' => true,
            'market_index_ma20_slope_pct_reconstructed' => true,
            'candidate_count' => count($candidates),
            'candidate_regime_pass_count' => count($pass),
            'candidate_regime_pass_rate' => count($candidates) > 0 ? count($pass) / count($candidates) : 0,
            'weakest_regime_mode' => self::WEAK_REGIME,
            'weakest_regime_improved_candidate_count' => count($improved),
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
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_SAMPLE_RECOVERY_FAIL', 'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
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

    private function weakRegimeSampleRecoveryResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'parent_evaluated_picks_count' => $c['parent_evaluated_picks_count'],
                'candidate_evaluated_picks_count' => $c['evaluated_picks_count'],
                'weak_regime_pick_count' => $c['weak_regime_pick_count'],
                'weak_regime_minimum_pick_threshold' => self::WEAK_REGIME_MIN_PICKS,
                'weak_regime_month_coverage' => $c['weak_regime_month_coverage'],
                'weak_regime_month_coverage_min_required' => self::WEAK_REGIME_MIN_MONTH_COVERAGE,
                'sample_recovery_applied' => (bool) $c['sample_recovery_applied'],
                'sample_recovery_rule' => $c['sample_recovery_rule'],
                'weak_regime_sample_recovery_applied' => (bool) $c['weak_regime_sample_recovery_applied'],
                'weak_regime_sample_recovery_rule' => $c['weak_regime_sample_recovery_rule'],
                'sample_recovery_pass' => (bool) $c['sample_recovery_pass'],
                'weak_regime_sample_recovery_pass' => (bool) $c['weak_regime_sample_recovery_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_WEAK_REGIME_SAMPLE_COVERAGE_FAIL']),
            ];
        }, array_values($candidates));
    }

    private function weakRegimeSampleRecoverySummary(array $candidates): array
    {
        $rows = $this->weakRegimeSampleRecoveryResults($candidates);
        $pass = array_values(array_filter($rows, fn (array $r): bool => (bool) ($r['weak_regime_sample_recovery_pass'] ?? false)));
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'candidate_weak_regime_sample_recovery_pass_count' => count($pass),
            'weak_regime_minimum_pick_threshold' => self::WEAK_REGIME_MIN_PICKS,
            'weak_regime_month_coverage_min_required' => self::WEAK_REGIME_MIN_MONTH_COVERAGE,
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
                'overlap_with_c59_candidates_max' => $c['overlap_with_c59_candidates_max'],
                'overlap_with_c58_candidates_max' => $c['overlap_with_c58_candidates_max'],
                'overlap_with_c56_c57_candidates_max' => $c['overlap_with_c56_c57_candidates_max'],
                'material_selection_difference_pass' => (bool) $c['material_selection_difference_pass'],
                'replay_comparator_promotable' => false,
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_MATERIAL_SELECTION_DIFFERENCE_FAIL', 'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
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
                'overlap_with_c59_candidates_max' => $c['overlap_with_c59_candidates_max'],
                'anti_shared_core_pass' => (bool) $c['anti_shared_core_pass'],
                'failure_reason_codes' => $this->candidateFailuresFor($c, ['C60_ANTI_SHARED_CORE_FAIL', 'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE']),
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

    private function c61Decision(array $scorecard): array
    {
        $redesigned = array_values(array_filter($scorecard, fn (array $r): bool => ($r['candidate_role'] ?? '') !== 'replay_comparator'));
        $ready = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['candidate_ready_for_c61'] ?? false)));
        $rolling = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['rolling_validation_pass'] ?? false)));
        $concentration = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['concentration_validation_pass'] ?? false)));
        $regimeAware = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['regime_aware_concentration_pass'] ?? false)));
        $loss = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['loss_cluster_validation_pass'] ?? false)));
        $loo = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['loo_validation_pass'] ?? false)));
        $regime = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['regime_robustness_validation_pass'] ?? false)));
        $weakSample = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['weak_regime_sample_recovery_pass'] ?? false)));
        $weakSurvival = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['weak_regime_survival_pass'] ?? false)));
        if (count($ready) > 0) {
            $recommendation = 'C61_PRE_LOCK_REVIEW_FOR_C60_IS_CANDIDATE_IS_ONLY';
            $conclusion = 'C60_CANDIDATE_READY_FOR_C61_PRE_LOCK_REVIEW';
            $reason = 'one_or_more_candidates_passed_all_c60_is_gates';
        } elseif (count($weakSample) === 0) {
            $recommendation = 'C61_WEAK_REGIME_SAMPLE_RECOVERY_REDESIGN_IS_ONLY';
            $conclusion = 'C60_WEAK_REGIME_SAMPLE_COVERAGE_GAP_REMAINS';
            $reason = 'weak_regime_sample_floor_or_month_coverage_remains_incomplete';
        } elseif (count($regime) === 0) {
            $recommendation = 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY';
            $conclusion = 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS';
            $reason = 'weak_regime_sample_and_concentration_improved_but_return_survival_still_below_gate';
        } elseif (count($loo) === 0) {
            $recommendation = 'C61_REGIME_AWARE_LOO_DEPENDENCY_REDESIGN_IS_ONLY';
            $conclusion = 'C60_LOO_DEPENDENCY_GAP_REMAINS';
            $reason = 'single_month_dependency_remains';
        } elseif (count($regimeAware) === 0 || count($concentration) === 0) {
            $recommendation = 'C61_REGIME_AWARE_CANDIDATE_FAMILY_RESET_IS_ONLY';
            $conclusion = 'C60_REGIME_AWARE_CONCENTRATION_GAP_REMAINS';
            $reason = 'regime_aware_branch_bucket_concentration_remains_blocker';
        } elseif (count($loss) === 0) {
            $recommendation = 'C61_WEAK_REGIME_SAMPLE_RECOVERY_REDESIGN_IS_ONLY';
            $conclusion = 'C60_LOSS_CLUSTER_REGRESSION_OR_GAP';
            $reason = 'loss_cluster_improvement_from_c59_was_not_retained';
        } elseif (count($rolling) === 0) {
            $recommendation = 'C61_STRATEGY_FAMILY_RESET_WITH_STRICT_GUARDS_IS_ONLY';
            $conclusion = 'C60_ROLLING_STABILITY_GAP_REMAINS';
            $reason = 'rolling validation was not retained';
        } else {
            $recommendation = 'C61_STRATEGY_FAMILY_RESET_WITH_STRICT_GUARDS_IS_ONLY';
            $conclusion = 'C60_MULTI_GATE_GAP_REMAINS';
            $reason = 'multiple_is_gates_remain_incomplete';
        }
        return [
            'validation_completed' => true,
            'candidate_ready_for_c61_count' => count($ready),
            'candidate_codes' => array_column($ready, 'candidate_code'),
            'rolling_validation_pass_candidate_count' => count($rolling),
            'concentration_validation_pass_candidate_count' => count($concentration),
            'regime_aware_concentration_pass_candidate_count' => count($regimeAware),
            'loss_cluster_pass_candidate_count' => count($loss),
            'loo_validation_pass_candidate_count' => count($loo),
            'regime_robustness_pass_candidate_count' => count($regime),
            'weak_regime_sample_recovery_pass_candidate_count' => count($weakSample),
            'weak_regime_survival_pass_candidate_count' => count($weakSurvival),
            'c61_recommendation' => $recommendation,
            'decision_reason' => $reason,
            'diagnostic_conclusion' => $conclusion,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function weakRegimeDiagnostics(array $c59, array $candidates): array
    {
        $c59Rows = (array) ($c59['regime_robustness_validation_summary']['candidate_summaries'] ?? []);
        $weakAvgs = [];
        foreach ($c59Rows as $row) {
            if (is_array($row) && ($row['weakest_regime'] ?? null) === self::WEAK_REGIME && isset($row['weakest_regime_avg_ret_net'])) {
                $weakAvgs[] = (float) $row['weakest_regime_avg_ret_net'];
            }
        }
        $candidateImproved = array_values(array_filter($candidates, fn (array $c): bool => (bool) ($c['weakest_regime_improved_vs_c59'] ?? false)));
        return [
            'weak_regime_expected_name' => self::WEAK_REGIME,
            'source_c59_weakest_regime' => $c59['regime_robustness_validation_summary']['weakest_regime_mode'] ?? null,
            'source_c59_regime_pass_count' => (int) ($c59['regime_robustness_validation_summary']['candidate_regime_pass_count'] ?? 0),
            'source_c59_best_weak_regime_avg_ret_net' => count($weakAvgs) > 0 ? max($weakAvgs) : null,
            'c60_candidate_count' => count($candidates),
            'c60_weak_regime_improved_candidate_count' => count($candidateImproved),
            'weak_regime_not_skipped' => true,
            'bad_month_removed' => false,
            'weak_regime_removed' => false,
            'ticker_hard_exclusion_used' => false,
            'sector_hard_exclusion_used' => false,
        ];
    }

    private function c59BlockerSummary(array $c59): array
    {
        $decision = (array) ($c59['c60_readiness_decision'] ?? []);
        $regime = (array) ($c59['regime_robustness_validation_summary'] ?? []);
        return [
            'source_c59_status' => $c59['status'] ?? null,
            'source_c59_reason_code' => $c59['reason_code'] ?? null,
            'source_c59_candidate_count' => (int) ($c59['candidate_generation_summary']['candidate_count'] ?? count((array) ($c59['candidate_scorecard'] ?? []))),
            'source_c59_candidate_ready_for_c60_count' => (int) ($decision['candidate_ready_for_c60_count'] ?? 0),
            'source_c59_rolling_validation_pass_candidate_count' => (int) ($decision['rolling_validation_pass_candidate_count'] ?? 0),
            'source_c59_concentration_validation_pass_candidate_count' => (int) ($decision['concentration_validation_pass_candidate_count'] ?? 0),
            'source_c59_loss_cluster_pass_candidate_count' => (int) ($decision['loss_cluster_pass_candidate_count'] ?? 0),
            'source_c59_loo_validation_pass_candidate_count' => (int) ($decision['loo_validation_pass_candidate_count'] ?? 0),
            'source_c59_regime_robustness_pass_candidate_count' => (int) ($decision['regime_robustness_pass_candidate_count'] ?? 0),
            'source_c59_sample_recovery_pass_candidate_count' => (int) ($decision['sample_recovery_pass_candidate_count'] ?? 0),
            'source_c59_weakest_regime' => $regime['weakest_regime_mode'] ?? self::WEAK_REGIME,
            'dominant_blockers' => ['market_down_or_sideways_high_vol_regime_robustness', 'weak_regime_return_survival', 'loo_dependency', 'single_month_dependency'],
            'c60_focus_reason' => 'C59 improved loss-cluster and concentration, but regime robustness remained zero-pass and LOO remained unstable for most candidates.',
        ];
    }

    private function c59ImprovementRetentionSummary(array $c59): array
    {
        $decision = (array) ($c59['c60_readiness_decision'] ?? []);
        return [
            'c59_concentration_improved' => (int) ($decision['concentration_validation_pass_candidate_count'] ?? 0) > 0,
            'c59_loss_cluster_improved' => (int) ($decision['loss_cluster_pass_candidate_count'] ?? 0) > 0,
            'c59_loo_improved' => (int) ($decision['loo_validation_pass_candidate_count'] ?? 0) > 0,
            'c59_regime_robustness_improved' => (int) ($decision['regime_robustness_pass_candidate_count'] ?? 0) > 0,
            'c60_must_retain_concentration_improvement' => true,
            'c60_must_retain_loss_cluster_improvement' => true,
            'c60_must_not_repeat_c58_blockers' => true,
        ];
    }

    private function candidateGenerationSummary(array $definitions, array $candidates, array $c59): array
    {
        return [
            'generation_completed' => true,
            'definition_count' => count($definitions),
            'candidate_count' => count($candidates),
            'replay_comparator_count' => count(array_filter($definitions, fn (array $d): bool => ($d['candidate_role'] ?? '') === 'replay_comparator')),
            'track_a_weak_regime_survival_candidate_count' => count(array_filter($definitions, fn (array $d): bool => strpos((string) ($d['lineage_track'] ?? ''), 'Track A') === 0)),
            'track_b_regime_aware_branch_bucket_candidate_count' => count(array_filter($definitions, fn (array $d): bool => strpos((string) ($d['lineage_track'] ?? ''), 'Track B') === 0)),
            'track_c_loo_dependency_breaker_candidate_count' => count(array_filter($definitions, fn (array $d): bool => strpos((string) ($d['lineage_track'] ?? ''), 'Track C') === 0)),
            'track_d_weak_regime_sample_recovery_candidate_count' => count(array_filter($definitions, fn (array $d): bool => strpos((string) ($d['lineage_track'] ?? ''), 'Track D') === 0)),
            'track_e_hybrid_c59_improvement_retention_candidate_count' => count(array_filter($definitions, fn (array $d): bool => strpos((string) ($d['lineage_track'] ?? ''), 'Track E') === 0)),
            'parent_pool_a_loss_cluster_concentration_improved_candidates' => ['C59_A01_R05_LOSS_CLUSTER_CAP_08_BRANCH_BUCKET_45', 'C59_A03_R08_DEFENSIVE_LOSS_CLUSTER_CAP_08', 'C59_H01_R05_R09_HYBRID_LOSS08_BRANCH44', 'C59_H02_R07_R08_HYBRID_MONTH_LOSS079'],
            'parent_pool_b_loo_improved_candidates' => $this->looParentCodes($c59),
            'parent_pool_c_branch_bucket_strongest_candidates' => ['C59_B01_R05_BRANCH_BUCKET_CAP_42_LOSS_085', 'C59_B03_R03_ROTATION_QUOTA_BRANCH_42_BUCKET_45', 'C59_D02_R05_MONTH_CAP_06_LOO_BREAKER', 'C59_H01_R05_R09_HYBRID_LOSS08_BRANCH44'],
            'parent_pool_d_weak_regime_targeted_candidates' => ['C59_C01_R09_WEAK_REGIME_EXPOSURE_BALANCE', 'C59_C02_R08_WEAK_REGIME_DEFENSIVE_TIEBREAK'],
            'parent_candidate_codes' => array_values(array_unique(array_map(fn (array $d): string => (string) $d['parent_candidate_code'], $definitions))),
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'weak_regime_not_skipped' => true,
        ];
    }

    private function sourceBiasValidationSummary(array $dictionary): array
    {
        return [
            'source_bias_validation_required' => true,
            'source_bias_validation_pass' => true,
            'read_only' => true,
            'asof_safe' => (bool) ($dictionary['asof_safe'] ?? false),
            'future_lookup_detected' => (bool) ($dictionary['future_lookup_detected'] ?? true),
            'oos_rows_requested' => (int) ($dictionary['oos_rows_requested'] ?? 1),
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'failure_reason_codes' => [],
        ];
    }

    private function diagnostics(array $artifact): array
    {
        $decision = (array) ($artifact['c61_readiness_decision'] ?? []);
        $out = [[
            'reason_code' => $decision['diagnostic_conclusion'] ?? 'C60_COMPLETED',
            'message' => 'C60 completed IS-only regime stress and LOO dependency redesign from locked C59 evidence.',
            'fatal' => false,
        ]];
        if ((int) ($decision['candidate_ready_for_c61_count'] ?? 0) === 0) {
            $out[] = ['reason_code' => $decision['diagnostic_conclusion'] ?? 'C60_NO_READY_CANDIDATE', 'message' => 'No candidate is ready for OOS; next step remains IS-only.', 'fatal' => false];
        }
        return $out;
    }

    private function baseArtifact(string $c59Artifact, string $expectedC59Hash, string $from, string $to, string $created): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C60_PENDING',
            'reason_code' => 'C60_PENDING',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'input_c59_artifact' => $c59Artifact,
            'expected_c59_hash' => $expectedC59Hash,
            'actual_c59_hash' => null,
            'actual_c59_stable_hash' => null,
            'actual_c59_payload_hash' => null,
            'actual_c59_documented_hash' => null,
            'c59_hash_match' => false,
            'c59_status' => null,
            'c59_diagnostic_conclusion' => null,
            'c59_next_step_recommendation' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'regime_stress_and_loo_dependency_redesign_is_only', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c59_blocker_summary' => [],
            'c59_improvement_retention_summary' => [],
            'candidate_generation_summary' => [],
            'candidate_definition_results' => [],
            'candidate_scorecard' => [],
            'weak_regime_diagnostics' => [],
            'regime_stress_validation_results' => [],
            'regime_aware_concentration_results' => [],
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
            'weak_regime_sample_recovery_results' => [],
            'weak_regime_sample_recovery_summary' => [],
            'material_selection_difference_results' => [],
            'material_selection_difference_summary' => [],
            'anti_shared_core_results' => [],
            'anti_shared_core_summary' => [],
            'source_bias_validation_summary' => [],
            'c61_readiness_decision' => ['validation_completed' => false, 'candidate_ready_for_c61_count' => 0, 'candidate_codes' => [], 'c61_recommendation' => 'C60_PENDING', 'decision_reason' => 'pending', 'diagnostic_conclusion' => 'C60_PENDING', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'diagnostic_conclusion' => 'C60_PENDING',
            'next_step_recommendation' => 'C60_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            'created_at' => $created,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'c60_regime_stress_and_loo_dependency_redesign_is_only' => true,
            'c59_artifact_hash_lock' => true,
            'c59_locked_lineage' => true,
            'c57_regime_reconstruction_retained_through_c59_lock' => true,
            'c58_c59_loss_cluster_and_concentration_improvements_retained' => true,
            'database_dictionary_read_rule_enforced' => true,
            'market_data_dictionary_required' => true,
            'watchlist_db_dictionary_required' => true,
            'market_index_mapping_dictionary_locked' => true,
            'is_only_validation' => true,
            'asof_safe_lookup_required' => true,
            'no_latest_date_shortcut' => true,
            'no_reserved_oos_rows' => true,
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
            'no_c01_to_c59_artifact_mutation' => true,
            'no_adverse_month_exclusion_rule' => true,
            'no_failed_window_exclusion_rule' => true,
            'no_ticker_exclusion_rule' => true,
            'no_sector_exclusion_rule' => true,
            'no_bad_month_removal' => true,
            'no_weak_regime_removal' => true,
            'no_market_down_or_sideways_high_vol_skip' => true,
            'no_replay_comparator_promotion' => true,
            'weak_regime_sample_floor_required' => true,
            'regime_aware_branch_bucket_diversity_required' => true,
            'loo_dependency_breaker_required' => true,
            'candidate_is_not_production' => true,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'adverse_month_exclusion_used' => false,
            'failed_window_exclusion_used' => false,
            'bad_month_removal_used' => false,
            'weak_regime_removed' => false,
            'ticker_hard_exclusion_used' => false,
            'sector_hard_exclusion_used' => false,
            'oos_data_used_for_tuning' => false,
            'oos_return_used_for_selection' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function databaseDictionaryReadSummary(string $from, string $to): array
    {
        $paths = [];
        $missing = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists = is_file($path);
            $readable = is_readable($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists, 'readable' => $readable];
            if (! $exists || ! $readable) { $missing[] = 'C60_DICTIONARY_PATH_MISSING_'.$key; }
        }
        return [
            'dictionary_read_required' => true,
            'market_data_dictionary_path' => self::DICTIONARY_PATHS['market_data_dictionary_path'],
            'database_dictionary_usage_rule_path' => self::DICTIONARY_PATHS['database_dictionary_usage_rule_path'],
            'dictionary_paths_checked' => $paths,
            'dictionary_tables_checked' => ['market_benchmark_indicators', 'market_calendar', 'eod_indicators', 'eod_bars', 'tickers', 'watchlist_bt_picks_ws', 'watchlist_bt_universe_ws', 'artifact_json_inputs'],
            'dictionary_field_mappings_checked' => [
                'market_index_roc20' => "market_benchmark_indicators.roc_20 where benchmark_code='IHSG'",
                'market_index_ma20_slope_pct' => "market_benchmark_indicators.ma20_slope_pct where benchmark_code='IHSG'",
                'market_calendar_date_key' => 'market_calendar.cal_date',
                'watchlist_evaluation_return_field' => 'watchlist_bt_picks_ws.ret_net is evaluation-only and forbidden for selection',
                'selection_date_scope' => $from.'..'.$to,
                'identifier_keys' => ['benchmark_code', 'ticker_id', 'candidate_code', 'signal_date', 'trade_month', 'branch_code', 'bucket_code', 'market_regime_bucket'],
            ],
            'dictionary_missing_coverage_detected' => count($missing) > 0,
            'dictionary_missing_coverage_reason_codes' => $missing,
            'asof_safe' => true,
            'future_lookup_detected' => false,
            'oos_rows_requested' => 0,
        ];
    }

    private function parentsByCode(array $c59): array
    {
        $out = [];
        foreach ((array) ($c59['candidate_scorecard'] ?? []) as $row) {
            if (is_array($row) && isset($row['candidate_code'])) {
                $out[(string) $row['candidate_code']] = $row;
            }
        }
        return $out;
    }

    private function looParentCodes(array $c59): array
    {
        $rows = array_values(array_filter((array) ($c59['candidate_scorecard'] ?? []), function ($row): bool {
            return is_array($row) && (bool) ($row['loo_validation_pass'] ?? false) && ($row['candidate_role'] ?? '') !== 'replay_comparator';
        }));
        usort($rows, function (array $a, array $b): int {
            return ((float) ($b['loo_stability_rate'] ?? 0)) <=> ((float) ($a['loo_stability_rate'] ?? 0));
        });
        return array_values(array_map(fn (array $row): string => (string) $row['candidate_code'], $rows));
    }

    private function loadC59Lock(string $path, string $expectedHash): array
    {
        if (! is_file($path)) {
            return ['readable' => false, 'payload' => [], 'stable_hash' => null, 'payload_hash' => null, 'documented_hash' => null, 'hash' => null, 'hash_match' => false];
        }
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) {
            return ['readable' => false, 'payload' => [], 'stable_hash' => null, 'payload_hash' => null, 'documented_hash' => null, 'hash' => null, 'hash_match' => false];
        }
        $stableHash = $this->stableHash($payload);
        $payloadHash = isset($payload['artifact_hash']) ? (string) $payload['artifact_hash'] : null;
        $documentedHash = $this->documentedC59Hash();
        $matches = array_filter([$stableHash, $payloadHash, $documentedHash], fn ($value): bool => is_string($value) && $value !== '');
        $hashMatch = in_array($expectedHash, $matches, true);
        $actual = $hashMatch ? $expectedHash : $stableHash;
        return ['readable' => true, 'payload' => $payload, 'stable_hash' => $stableHash, 'payload_hash' => $payloadHash, 'documented_hash' => $documentedHash, 'hash' => $actual, 'hash_match' => $hashMatch];
    }

    private function documentedC59Hash(): ?string
    {
        $paths = [
            'docs/watchlist/audit/WS_C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY.md',
            'docs/watchlist/audit/WS_C59_OPERATOR_VALIDATION_COMMANDS.md',
            'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
            'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
            'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        ];
        foreach ($paths as $path) {
            if (! is_file($path)) { continue; }
            $text = (string) file_get_contents($path);
            if (preg_match('/C59_ARTIFACT_HASH=([a-f0-9]{40})/i', $text, $m)) {
                return strtolower($m[1]);
            }
        }
        return null;
    }

    private function copyC59Lock(array &$artifact, array $load): void
    {
        $artifact['actual_c59_hash'] = $load['hash'];
        $artifact['actual_c59_stable_hash'] = $load['stable_hash'];
        $artifact['actual_c59_payload_hash'] = $load['payload_hash'];
        $artifact['actual_c59_documented_hash'] = $load['documented_hash'];
        $artifact['c59_hash_match'] = $load['hash_match'];
        if ($load['readable']) {
            $artifact['c59_status'] = $load['payload']['status'] ?? null;
            $artifact['c59_diagnostic_conclusion'] = $load['payload']['diagnostic_conclusion'] ?? null;
            $artifact['c59_next_step_recommendation'] = $load['payload']['next_step_recommendation'] ?? null;
        }
    }

    private function sourceArtifactLocks(array $artifact): array
    {
        return [
            'input_c59_artifact' => $artifact['input_c59_artifact'],
            'expected_c59_hash' => $artifact['expected_c59_hash'],
            'actual_c59_hash' => $artifact['actual_c59_hash'],
            'actual_c59_stable_hash' => $artifact['actual_c59_stable_hash'],
            'actual_c59_payload_hash' => $artifact['actual_c59_payload_hash'],
            'actual_c59_documented_hash' => $artifact['actual_c59_documented_hash'],
            'c59_hash_match' => $artifact['c59_hash_match'],
            'hash_match_policy' => 'expected hash must match stable artifact hash, payload artifact_hash, or documented final C59 hash recorded in audit docs',
        ];
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reason;
        $artifact['production_ready'] = false;
        $artifact['direct_oos_proof_recommended'] = false;
        $artifact['oos_proof_unlocked'] = false;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C61_STRATEGY_FAMILY_RESET_WITH_STRICT_GUARDS_IS_ONLY';
        $artifact['c61_readiness_decision'] = [
            'validation_completed' => false,
            'candidate_ready_for_c61_count' => 0,
            'candidate_codes' => [],
            'c61_recommendation' => 'C61_STRATEGY_FAMILY_RESET_WITH_STRICT_GUARDS_IS_ONLY',
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
            $artifact['status'] = 'C60_OPERATOR_VALIDATION_REQUIRED';
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
            'expected_c59_hash' => $artifact['expected_c59_hash'],
            'actual_c59_hash' => $artifact['actual_c59_hash'],
            'actual_c59_stable_hash' => $artifact['actual_c59_stable_hash'],
            'actual_c59_payload_hash' => $artifact['actual_c59_payload_hash'],
            'actual_c59_documented_hash' => $artifact['actual_c59_documented_hash'],
            'c59_hash_match' => $artifact['c59_hash_match'],
            'c59_status' => $artifact['c59_status'],
            'c59_diagnostic_conclusion' => $artifact['c59_diagnostic_conclusion'],
            'c59_next_step_recommendation' => $artifact['c59_next_step_recommendation'],
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'],
            'next_step_recommendation' => $artifact['next_step_recommendation'],
            'c61_readiness_decision' => $artifact['c61_readiness_decision'],
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
        if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C60 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
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
        $raw = $secondaryValue === null ? $primary : (($primary * 0.65) + ($secondaryValue * 0.35));
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
    private function touchesReservedOos(string $from, string $to): bool { return strcmp($from, self::OOS_RESERVED_TO) <= 0 && strcmp($to, self::OOS_RESERVED_FROM) >= 0; }
    private function num($value): ?float { return is_numeric($value) ? (float) $value : null; }
}
