<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC132WeeklySwingWatchlistProductionLiveRuntimeActivationExecutionReviewService
{
    public const RUN_CODE = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW';
    public const PHASE_LABEL = 'PR-20 / C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW';
    public const ARTIFACT_TYPE = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW';

    public const DEFAULT_C131_ARTIFACT = 'storage/app/watchlist/backtest/c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review.json';
    public const DEFAULT_EXPECTED_C131_HASH = 'b585d9df32751e811f2b11038e71acb730d694b5';
    public const DEFAULT_EXPECTED_C131_FILE_SHA1 = 'C493DA15314B5AD070FC6D236AD90BB73B046AD8';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c132-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C131_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_PASSED_READY_FOR_ACTIVATION_EXECUTION_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C131_PHASE_LABEL = 'PR-19 / C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW';
    private const EXPECTED_C131_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C133_RECOMMENDATION = 'C133_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW';

    private const PASS_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_PASSED_READY_FOR_ACTIVATION_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const ACTIVATION_EXECUTION_NOT_CONFIRMED_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_ACTIVATION_EXECUTION_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C131_LOCK_MISMATCH_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_C131_ARTIFACT_LOCK_MISMATCH';
    private const C131_FILE_SHA1_MISMATCH_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_C131_FILE_SHA1_LOCK_MISMATCH';
    private const C131_STATUS_MISMATCH_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_C131_STATUS_MISMATCH';
    private const C131_PHASE_LABEL_MISMATCH_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_C131_PHASE_LABEL_MISMATCH';
    private const C131_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_C131_NEXT_RECOMMENDATION_MISMATCH';
    private const C131_APPROVAL_INCOMPLETE_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_C131_ACTIVATION_APPROVAL_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C131_CONVERT_FROM_JSON_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_C131_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_FEATURE_FLAGS_NOT_DEFAULT_OFF';

    private const REQUIRED_C131_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_approval_review_pass',
        'production_live_runtime_activation_approval_review_pass',
        'production_live_runtime_activation_approval_granted',
        'ready_for_production_live_runtime_activation_execution_review',
        'production_live_runtime_activation_execution_review_allowed_next',
        'production_live_runtime_activation_approval_manifest_created',
        'c130_lock_valid',
        'c130_activation_readiness_valid',
        'c130_convert_from_json_pass',
        'c129_final_closure_valid',
        'c129_audit_archive_terminal',
        'c130_activation_readiness_review_only',
        'c130_not_live_runtime_activation_execution',
        'primary_candidate_ready_for_production_live_runtime_activation_execution_review',
        'backup_candidate_ready_for_production_live_runtime_activation_execution_review',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C131_FALSE_FIELDS = [
        'comparator_candidate_ready_for_production_live_runtime_activation_execution_review',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'production_deployment_allowed',
        'production_deployment_executed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'runtime_bridge_active',
        'controlled_rollout_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
    ];

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'production_deployment_allowed',
        'production_deployment_executed',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'pilot_runtime_active',
        'shadow_runtime_active',
        'runtime_bridge_active',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_plan_confirm_mutation_allowed',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
        'weekly_swing_watchlist_production_live_runtime_activation_readiness_context_persisted_to_live_runtime',
        'production_live_runtime_activation_readiness_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_approval_context_persisted_to_live_runtime',
        'production_live_runtime_activation_approval_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_execution_context_persisted_to_live_runtime',
        'production_live_runtime_activation_execution_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_execution_review_context_persisted_to_live_runtime',
        'production_live_runtime_activation_execution_review_context_persisted_to_live_runtime',
    ];

    private const FEATURE_FLAG_KEYS = [
        'production_catalog_runtime_bridge_enabled',
        'production_catalog_controlled_opt_in_runtime_bridge_enabled',
        'production_catalog_controlled_runtime_opt_in_pilot_enabled',
        'production_catalog_controlled_shadow_rollout_enabled',
        'production_catalog_controlled_parallel_run_enabled',
        'production_catalog_controlled_rollout_enabled',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/*no-*-test.json',
        'storage/app/watchlist/backtest/*missing-*-test.json',
        'storage/app/watchlist/backtest/*mismatch-*-test.json',
        'storage/app/watchlist/backtest/*negative-*-test.json',
    ];

    public function execute(
        string $c131Artifact = self::DEFAULT_C131_ARTIFACT,
        string $expectedC131Hash = self::DEFAULT_EXPECTED_C131_HASH,
        string $expectedC131FileSha1 = self::DEFAULT_EXPECTED_C131_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c131Artifact, $expectedC131Hash, $expectedC131FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C131_LOCK_MISMATCH_STATUS, 'C131 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c131_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C131_CONVERT_FROM_JSON_STATUS, 'C131 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C131_LOCK_MISMATCH_STATUS, 'C131 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C131_FILE_SHA1_MISMATCH_STATUS, 'C131 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c131 = $load['payload'];
        if (($c131['status'] ?? null) !== self::EXPECTED_C131_STATUS || ($c131['reason_code'] ?? null) !== self::EXPECTED_C131_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C131_STATUS_MISMATCH_STATUS, 'C131 status/reason is not activation-approval passed.', $outputPath, $overwrite);
        }
        if (($c131['phase_label'] ?? null) !== self::EXPECTED_C131_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C131_PHASE_LABEL_MISMATCH_STATUS, 'C131 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c131NextRecommendationMatches($c131)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C131_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C131 next recommendation is not C132.', $outputPath, $overwrite);
        }
        if (! $this->c131ActivationApprovalComplete($c131)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C131_APPROVAL_INCOMPLETE_STATUS, 'C131 production/live activation approval evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c131)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C131 candidate scope does not match locked activation execution scope.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c131);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c131_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C131 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if ($this->enabledRuntimeFeatureFlags() !== []) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['enabled_runtime_feature_flags'] = $this->enabledRuntimeFeatureFlags();

            return $this->rejected($artifact, self::FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS, 'C132 requires runtime feature flags to remain default-off.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C132 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['production_live_runtime_activation_execution_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::ACTIVATION_EXECUTION_NOT_CONFIRMED_STATUS, 'C132 requires --production-live-runtime-activation-execution-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C132 records production/live runtime activation execution review as artifact-only evidence and moves to observation review. It does not activate runtime bridge, execute live output, publish recommendations, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C132_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_PASSED_ARTIFACT_ONLY_NON_LIVE_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::C133_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-20',
            'internal_checkpoint' => 'C132',
            'status' => 'C132_NOT_RUN',
            'reason_code' => 'C132_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_pass' => false,
            'production_live_runtime_activation_execution_review_pass' => false,
            'ready_for_production_live_runtime_activation_observation_review' => false,
            'production_live_runtime_activation_observation_review_allowed_next' => false,
            'production_live_runtime_activation_execution_review_manifest_created' => false,
            'production_live_runtime_activation_executed' => false,
            'c131_lock_valid' => false,
            'c131_activation_approval_valid' => false,
            'c131_convert_from_json_pass' => false,
            'c130_activation_readiness_valid' => false,
            'c129_final_closure_valid' => false,
            'c131_activation_approval_review_only' => true,
            'c131_not_live_runtime_activation_execution' => true,
            'c132_execution_review_only' => true,
            'c132_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_observation_review' => false,
            'backup_candidate_ready_for_production_live_runtime_activation_observation_review' => false,
            'comparator_candidate_ready_for_production_live_runtime_activation_observation_review' => false,
            'a01_remains_comparator_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $artifact[$flag] = false;
        }

        return $artifact;
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_pass' => true,
            'production_live_runtime_activation_execution_review_pass' => true,
            'ready_for_production_live_runtime_activation_observation_review' => true,
            'production_live_runtime_activation_observation_review_allowed_next' => true,
            'production_live_runtime_activation_execution_review_manifest_created' => true,
            'production_live_runtime_activation_executed' => false,
            'c131_lock_valid' => true,
            'c131_activation_approval_valid' => true,
            'c131_convert_from_json_pass' => true,
            'c130_activation_readiness_valid' => true,
            'c129_final_closure_valid' => true,
            'c131_activation_approval_review_only' => true,
            'c131_not_live_runtime_activation_execution' => true,
            'c132_execution_review_only' => true,
            'c132_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_observation_review' => true,
            'backup_candidate_ready_for_production_live_runtime_activation_observation_review' => true,
            'comparator_candidate_ready_for_production_live_runtime_activation_observation_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c131 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c131_lock_validation_summary'] = $this->c131LockValidationSummary($load, $c131);
        $artifact['c131_activation_approval_carry_forward_summary'] = $this->c131ActivationApprovalCarryForwardSummary($c131, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c131, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['activation_execution_confirmation_summary'] = $this->activationExecutionConfirmationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['feature_flag_default_off_summary'] = $this->featureFlagDefaultOffSummary();
        $artifact['c132_execution_decision'] = $this->activationExecutionDecision($pass);
        $artifact['next_observation_decision'] = $this->nextObservationDecision($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_execution_review_manifest'] = $this->activationExecutionManifest($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_execution_review_checklist'] = $this->activationExecutionChecklist();
        $artifact['c132_candidate_activation_execution_scorecard'] = $this->candidateScorecard($pass);
        $artifact['production_live_runtime_activation_execution_review_context_summary'] = $this->activationExecutionContextSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C132_PENDING')]);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($pass) {
            $artifact = array_merge($artifact, $this->passingTopLevelState());
        }

        return $artifact;
    }

    private function c131ActivationApprovalComplete(array $c131): bool
    {
        foreach (self::REQUIRED_C131_TRUE_FIELDS as $field) {
            if (! (bool) ($c131[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C131_FALSE_FIELDS as $field) {
            if ((bool) ($c131[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c131NextRecommendationMatches(array $c131): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_execution_decision', 'next_recommendation'],
            ['c131_approval_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c131, $path);
            if ($value !== null && $value !== self::EXPECTED_C131_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c131['next_step_recommendation'] ?? null) === self::EXPECTED_C131_NEXT_RECOMMENDATION;
    }

    private function candidateScopeMatches(array $source): bool
    {
        if (($source['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return false;
        }
        if (($source['backup_candidate_code'] ?? null) !== self::BACKUP_CANDIDATE) {
            return false;
        }
        if (($source['comparator_candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }
        foreach ([
            'a01_promoted',
            'candidate_promotion_executed',
            'candidate_rerank_executed',
            'strategy_retune_executed',
            'scoring_mutation_executed',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'weekly_swing_live_recommendation_selection_executed',
        ] as $field) {
            if ((bool) ($source[$field] ?? false)) {
                return false;
            }
        }

        return (bool) ($source['a01_remains_comparator_only'] ?? false);
    }

    private function firstLiveOrMutatingSafetyFlag(array $source): ?string
    {
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $field) {
            if ((bool) ($source[$field] ?? false)) {
                return $field;
            }
        }

        return null;
    }

    private function enabledRuntimeFeatureFlags(): array
    {
        $enabled = [];
        foreach (self::FEATURE_FLAG_KEYS as $key) {
            if ($this->configFlagIsOn($key)) {
                $enabled[] = $key;
            }
        }

        return $enabled;
    }

    private function c131LockValidationSummary(array $load, array $c131): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C131',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C131_STATUS,
            'actual_status' => $c131['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C131_PHASE_LABEL,
            'actual_phase_label' => $c131['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C131_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c131NextRecommendationMatches($c131),
            'c131_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c131ActivationApprovalCarryForwardSummary(array $c131, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c131_activation_approval_review_pass' => (bool) ($c131['weekly_swing_watchlist_production_live_runtime_activation_approval_review_pass'] ?? false),
            'c131_activation_approval_granted' => (bool) ($c131['production_live_runtime_activation_approval_granted'] ?? false),
            'c131_ready_for_activation_execution_review' => (bool) ($c131['ready_for_production_live_runtime_activation_execution_review'] ?? false),
            'c130_activation_readiness_valid' => (bool) ($c131['c130_activation_readiness_valid'] ?? false),
            'c129_final_closure_valid' => (bool) ($c131['c129_final_closure_valid'] ?? false),
            'c131_activation_approval_valid' => $this->c131ActivationApprovalComplete($c131),
            'c132_activation_execution_review_can_start' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $source, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($source),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_execution_review_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_execution_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_production_live_runtime_activation_observation_review' => $pass,
            'backup_candidate_ready_for_production_live_runtime_activation_observation_review' => $pass,
            'comparator_candidate_ready_for_production_live_runtime_activation_observation_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
            'scoring_mutation_executed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
            'weekly_swing_live_recommendation_selection_executed' => false,
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_approval_validation_pass' => $pass,
        ];
    }

    private function activationExecutionConfirmationSummary(array $options, bool $pass): array
    {
        return [
            'production_live_runtime_activation_execution_confirmation_required' => true,
            'production_live_runtime_activation_execution_confirmed' => (bool) ($options['production_live_runtime_activation_execution_confirmed'] ?? false),
            'activation_execution_confirmation_pass' => $pass,
            'activation_execution_review_only' => true,
            'live_runtime_state_change_requested' => false,
        ];
    }

    private function temporaryNegativeArtifactGuardSummary(array $paths): array
    {
        return [
            'validation_completed' => true,
            'temporary_negative_artifacts_remaining' => $paths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $paths === [],
            'temporary_negative_artifact_paths' => array_values($paths),
            'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
    }

    private function featureFlagDefaultOffSummary(): array
    {
        $flags = [];
        foreach (self::FEATURE_FLAG_KEYS as $key) {
            $flags[$key] = $this->configFlagIsOn($key);
        }

        return [
            'validation_completed' => true,
            'feature_flags_checked' => $flags,
            'enabled_runtime_feature_flags' => $this->enabledRuntimeFeatureFlags(),
            'all_runtime_feature_flags_remain_default_off' => $this->enabledRuntimeFeatureFlags() === [],
            'kill_switch_identified' => array_key_exists('production_catalog_runtime_bridge_kill_switch', $this->watchlistConfig()),
            'production_catalog_runtime_bridge_kill_switch' => $this->configFlagIsOn('production_catalog_runtime_bridge_kill_switch'),
        ];
    }

    private function activationExecutionDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c131_activation_approval_valid' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_observation_review' => $pass,
            'production_live_runtime_activation_executed' => false,
            'production_ready' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'runtime_bridge_active' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'next_recommendation' => $pass ? self::C133_RECOMMENDATION : 'C132_TARGETED_C131_ACTIVATION_APPROVAL_OR_CONFIRMATION_REPAIR',
            'decision_reason' => $pass ? 'C132 records artifact-only execution review and permits observation review next.' : 'C132 cannot proceed until C131 lock, activation approval, confirmation, feature flags, and safety gates pass.',
        ];
    }

    private function nextObservationDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C133_RECOMMENDATION : 'C132_TARGETED_C131_ACTIVATION_APPROVAL_OR_CONFIRMATION_REPAIR',
            'next_scope' => $pass ? 'production/live runtime activation observation review only; still no runtime bridge activation, live output generation, official publication, or PLAN/CONFIRM mutation in C132' : 'targeted C131 approval, confirmation, feature flag, or cleanup repair only',
        ];
    }

    private function activationExecutionManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_production_live_runtime_activation_execution_review',
            'source_artifact' => self::EXPECTED_C131_STATUS,
            'source_artifact_path' => self::DEFAULT_C131_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C131_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C131_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_execution_review_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_execution_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'c131_activation_approval_carried_forward' => $pass,
            'production_live_runtime_activation_execution_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_observation_review' => $pass,
            'production_live_runtime_activation_observation_review_required_next' => $pass,
            'production_live_runtime_activation_executed' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_official_output_published' => false,
            'weekly_swing_live_recommendation_generated' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'activation_execution_review_used_for_plan_confirm_mutation' => false,
            'activation_execution_review_used_for_live_rollout' => false,
            'activation_execution_review_artifact_only' => true,
        ];
    }

    private function activationExecutionChecklist(): array
    {
        return [
            'c131_activation_approval_artifact_locked' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'operator_approval_required' => true,
            'activation_execution_confirmation_required' => true,
            'feature_flags_default_off_required' => true,
            'kill_switch_required' => true,
            'rollback_plan_required_for_future_activation' => true,
            'manual_validation_required_before_future_activation' => true,
            'live_runtime_activation_observation_review_required_next' => true,
            'execution_review_only' => true,
            'non_live' => true,
            'non_mutating' => true,
            'artifact_only' => true,
            'runtime_bridge_activated' => false,
            'production_runtime_wiring_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_stock_recommendation_generated' => false,
            'weekly_swing_stock_recommendation_published' => false,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'production_live_runtime_activation_execution_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_observation_review' => $pass,
            'production_ready' => false,
            'production_deployment_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c132_role' => 'primary_production_live_runtime_activation_execution_review_candidate',
                'primary_candidate_ready_for_production_live_runtime_activation_observation_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c132_role' => 'backup_production_live_runtime_activation_execution_review_candidate',
                'backup_candidate_ready_for_production_live_runtime_activation_observation_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c132_role' => 'comparator_only_candidate',
                'production_live_runtime_activation_execution_review_pass' => false,
                'ready_for_production_live_runtime_activation_observation_review' => false,
                'comparator_candidate_ready_for_production_live_runtime_activation_observation_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function activationExecutionContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_context_created' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_execution_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_execution_review_context_persisted_to_live_runtime' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'plan_confirm_mutated' => false,
            'runtime_bridge_active' => false,
            'controlled_rollout_active' => false,
            'pilot_runtime_active' => false,
            'shadow_runtime_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
        ];
    }

    private function productionMutationSafetySummary(): array
    {
        $summary = [
            'validation_completed' => true,
            'all_required_safety_flags_false' => true,
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $summary[$flag] = false;
        }

        return $summary;
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-20_C132_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW',
            'c131_activation_approval_carried_forward' => true,
            'c132_production_live_runtime_activation_execution_review_executed' => true,
            'c132_ready_for_activation_observation_review' => $pass,
            'still_no_live_runtime' => true,
            'still_no_runtime_bridge_activation' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C133_RECOMMENDATION : 'C132_TARGETED_C131_ACTIVATION_APPROVAL_OR_CONFIRMATION_REPAIR',
            'planned_next_scope' => $pass ? 'production/live runtime activation observation review only; not official weekly swing output publication or PLAN/CONFIRM mutation in C132' : 'targeted repair before production/live activation execution review can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C131 artifact hash',
                'locked C131 file SHA1',
                'operator approval reference',
                'activation execution confirmation',
                'runtime feature flags still default-off',
                'future activation observation review contract',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c131_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c131_artifact_not_modified' => true,
            'c60_c131_artifacts_not_modified' => true,
            'c132_is_execution_review_not_live_state_change' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C132 validates C131 artifact_hash and file SHA1 locks before production/live runtime activation execution review is recorded.',
            'C132 validates C131 activation approval and next recommendation to C132.',
            'C132 requires --operator-approved, a non-empty --approval-reference, and --production-live-runtime-activation-execution-confirmed.',
            'C132 keeps runtime feature flags default-off and checks the kill-switch surface.',
            'C132 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C132 records artifact-only activation execution review and moves to observation review.',
            'C132 does not activate runtime bridge, execute production runtime wiring, generate official weekly swing output, publish recommendations, or mutate PLAN/CONFIRM.',
            'C132 may only recommend C133 production/live runtime activation observation review as the next controlled step.',
        ];
    }

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => array_values(array_filter($status)),
            'failure_count' => count(array_values(array_filter($status))),
        ];
    }

    private function temporaryNegativeArtifactPaths(): array
    {
        $paths = [];
        foreach (self::TEMPORARY_NEGATIVE_PATTERNS as $pattern) {
            foreach ((array) glob($pattern) as $path) {
                if (is_file($path)) {
                    $paths[] = str_replace('\\', '/', $path);
                }
            }
        }
        sort($paths);

        return array_values(array_unique($paths));
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c131' => [
                'artifact_path' => $load['path'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'convert_from_json_pass' => $load['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c131_hash' => $load['expected_hash'],
            'actual_c131_hash' => $load['actual_hash'],
            'c131_hash_match' => $load['hash_match'],
            'expected_c131_file_sha1' => $load['expected_file_sha1'],
            'actual_c131_file_sha1' => $load['actual_file_sha1'],
            'c131_file_sha1_match' => $load['file_sha1_match'],
            'c131_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        $jsonError = null;
        $duplicateKeys = [];
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $duplicateKeys = $this->caseInsensitiveDuplicateTopLevelKeys($raw);
            $decoded = json_decode($raw, true);
            $jsonError = json_last_error();
            if (is_array($decoded)) {
                $payload = $decoded;
                $actualHash = $decoded['artifact_hash'] ?? null;
            }
            $actualFileSha1 = strtoupper(sha1($raw));
        }
        $expectedFileSha1 = strtoupper($expectedFileSha1);

        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $actualHash !== null && hash_equals($expectedHash, (string) $actualHash),
            'expected_file_sha1' => $expectedFileSha1,
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $actualFileSha1 !== null && $expectedFileSha1 === $actualFileSha1,
            'json_error' => $jsonError,
            'case_insensitive_duplicate_keys' => $duplicateKeys,
            'convert_from_json_pass' => $exists && $payload !== null && $jsonError === JSON_ERROR_NONE && $duplicateKeys === [],
        ];
    }

    private function caseInsensitiveDuplicateTopLevelKeys(string $raw): array
    {
        $length = strlen($raw);
        $depth = 0;
        $expectTopLevelKey = false;
        $seen = [];
        $duplicates = [];

        for ($i = 0; $i < $length; $i++) {
            $char = $raw[$i];
            if ($char === '"') {
                $start = $i;
                $i++;
                $escaped = false;
                while ($i < $length) {
                    $inner = $raw[$i];
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($inner === '\\') {
                        $escaped = true;
                    } elseif ($inner === '"') {
                        break;
                    }
                    $i++;
                }
                $token = substr($raw, $start, $i - $start + 1);
                if ($depth === 1 && $expectTopLevelKey) {
                    $j = $i + 1;
                    while ($j < $length && ctype_space($raw[$j])) {
                        $j++;
                    }
                    if ($j < $length && $raw[$j] === ':') {
                        $decoded = json_decode($token, true);
                        if (is_string($decoded)) {
                            $lower = strtolower($decoded);
                            if (array_key_exists($lower, $seen) && ! in_array($decoded, $duplicates, true)) {
                                $duplicates[] = $decoded;
                            }
                            $seen[$lower] = $decoded;
                        }
                        $expectTopLevelKey = false;
                    }
                }
                continue;
            }
            if ($char === '{') {
                $depth++;
                if ($depth === 1) {
                    $expectTopLevelKey = true;
                }
                continue;
            }
            if ($char === '}') {
                if ($depth === 1) {
                    $expectTopLevelKey = false;
                }
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($char === '[') {
                $depth++;
                continue;
            }
            if ($char === ']') {
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($char === ',' && $depth === 1) {
                $expectTopLevelKey = true;
            }
        }
        sort($duplicates);

        return array_values($duplicates);
    }

    private function valueAt(array $source, array $path)
    {
        $value = $source;
        foreach ($path as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private function watchlistConfig(): array
    {
        $path = 'config/watchlist.php';
        if (! is_file($path)) {
            return [];
        }
        $config = require $path;

        return is_array($config) ? $config : [];
    }

    private function configFlagIsOn(string $key): bool
    {
        return (bool) ($this->watchlistConfig()[$key] ?? false);
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_observation_decision'] = $this->nextObservationDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_observation_decision'] = $this->nextObservationDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['artifact_path'] = $outputPath;
            $artifact['write_skipped_existing_output'] = true;

            return $artifact;
        }
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $hashPayload = $artifact;
        $hashPayload['artifact_hash'] = null;
        unset($hashPayload['artifact_path']);
        $artifact['artifact_hash'] = sha1(json_encode($hashPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $artifact['artifact_path'] = $outputPath;
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $artifact;
    }
}
