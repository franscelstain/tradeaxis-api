<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC131WeeklySwingWatchlistProductionLiveRuntimeActivationApprovalReviewService
{
    public const RUN_CODE = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW';
    public const PHASE_LABEL = 'PR-19 / C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW';
    public const ARTIFACT_TYPE = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW';

    public const DEFAULT_C130_ARTIFACT = 'storage/app/watchlist/backtest/c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review.json';
    public const DEFAULT_EXPECTED_C130_HASH = 'b4c4d48a672a953fee5fc5e79459817c34863775';
    public const DEFAULT_EXPECTED_C130_FILE_SHA1 = 'B244D23169FA9B01B473382398BE7C847A0C2794';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C130_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_PASSED_READY_FOR_ACTIVATION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C130_PHASE_LABEL = 'PR-18 / C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW';
    private const EXPECTED_C130_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C132_RECOMMENDATION = 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW';

    private const PASS_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_PASSED_READY_FOR_ACTIVATION_EXECUTION_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const ACTIVATION_APPROVAL_NOT_CONFIRMED_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_ACTIVATION_APPROVAL_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C130_LOCK_MISMATCH_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_C130_ARTIFACT_LOCK_MISMATCH';
    private const C130_FILE_SHA1_MISMATCH_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_C130_FILE_SHA1_LOCK_MISMATCH';
    private const C130_STATUS_MISMATCH_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_C130_STATUS_MISMATCH';
    private const C130_PHASE_LABEL_MISMATCH_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_C130_PHASE_LABEL_MISMATCH';
    private const C130_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_C130_NEXT_RECOMMENDATION_MISMATCH';
    private const C130_READINESS_INCOMPLETE_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_C130_ACTIVATION_READINESS_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C130_CONVERT_FROM_JSON_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_C130_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_FEATURE_FLAGS_NOT_DEFAULT_OFF';

    private const REQUIRED_C130_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_pass',
        'production_live_runtime_activation_readiness_review_pass',
        'ready_for_production_live_runtime_activation_approval_review',
        'production_live_runtime_activation_readiness_manifest_created',
        'c129_final_closure_valid',
        'c129_handoff_audit_archive_final_closed',
        'c129_audit_archive_terminal',
        'c130_is_new_production_live_activation_phase',
        'c130_not_handoff_audit_archive_continuation',
        'primary_candidate_ready_for_production_live_runtime_activation_approval_review',
        'backup_candidate_ready_for_production_live_runtime_activation_approval_review',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C130_FALSE_FIELDS = [
        'comparator_candidate_ready_for_production_live_runtime_activation_approval_review',
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
        string $c130Artifact = self::DEFAULT_C130_ARTIFACT,
        string $expectedC130Hash = self::DEFAULT_EXPECTED_C130_HASH,
        string $expectedC130FileSha1 = self::DEFAULT_EXPECTED_C130_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c130Artifact, $expectedC130Hash, $expectedC130FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C130_LOCK_MISMATCH_STATUS, 'C130 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c130_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C130_CONVERT_FROM_JSON_STATUS, 'C130 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C130_LOCK_MISMATCH_STATUS, 'C130 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C130_FILE_SHA1_MISMATCH_STATUS, 'C130 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c130 = $load['payload'];
        if (($c130['status'] ?? null) !== self::EXPECTED_C130_STATUS || ($c130['reason_code'] ?? null) !== self::EXPECTED_C130_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C130_STATUS_MISMATCH_STATUS, 'C130 status/reason is not activation-readiness passed.', $outputPath, $overwrite);
        }
        if (($c130['phase_label'] ?? null) !== self::EXPECTED_C130_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C130_PHASE_LABEL_MISMATCH_STATUS, 'C130 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c130NextRecommendationMatches($c130)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C130_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C130 next recommendation is not C131.', $outputPath, $overwrite);
        }
        if (! $this->c130ActivationReadinessComplete($c130)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C130_READINESS_INCOMPLETE_STATUS, 'C130 production/live activation readiness evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c130)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C130 candidate scope does not match locked activation approval scope.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c130);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c130_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C130 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if ($this->enabledRuntimeFeatureFlags() !== []) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['enabled_runtime_feature_flags'] = $this->enabledRuntimeFeatureFlags();

            return $this->rejected($artifact, self::FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS, 'C131 requires runtime feature flags to remain default-off.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C131 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['production_live_runtime_activation_approval_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::ACTIVATION_APPROVAL_NOT_CONFIRMED_STATUS, 'C131 requires --production-live-runtime-activation-approval-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C131 records production/live runtime activation approval readiness for a future execution review. It does not activate runtime bridge, execute live output, publish recommendations, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C131_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_PASSED_REVIEW_ONLY_NON_LIVE_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::C132_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-19',
            'internal_checkpoint' => 'C131',
            'status' => 'C131_NOT_RUN',
            'reason_code' => 'C131_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_pass' => false,
            'production_live_runtime_activation_approval_review_pass' => false,
            'production_live_runtime_activation_approval_granted' => false,
            'ready_for_production_live_runtime_activation_execution_review' => false,
            'production_live_runtime_activation_execution_review_allowed_next' => false,
            'production_live_runtime_activation_approval_manifest_created' => false,
            'c130_lock_valid' => false,
            'c130_activation_readiness_valid' => false,
            'c130_convert_from_json_pass' => false,
            'c129_final_closure_valid' => false,
            'c129_audit_archive_terminal' => false,
            'c130_activation_readiness_review_only' => true,
            'c130_not_live_runtime_activation_execution' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
            'backup_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
            'comparator_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
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
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_pass' => true,
            'production_live_runtime_activation_approval_review_pass' => true,
            'production_live_runtime_activation_approval_granted' => true,
            'ready_for_production_live_runtime_activation_execution_review' => true,
            'production_live_runtime_activation_execution_review_allowed_next' => true,
            'production_live_runtime_activation_approval_manifest_created' => true,
            'c130_lock_valid' => true,
            'c130_activation_readiness_valid' => true,
            'c130_convert_from_json_pass' => true,
            'c129_final_closure_valid' => true,
            'c129_audit_archive_terminal' => true,
            'c130_activation_readiness_review_only' => true,
            'c130_not_live_runtime_activation_execution' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_execution_review' => true,
            'backup_candidate_ready_for_production_live_runtime_activation_execution_review' => true,
            'comparator_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c130 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c130_lock_validation_summary'] = $this->c130LockValidationSummary($load, $c130);
        $artifact['c130_activation_readiness_carry_forward_summary'] = $this->c130ActivationReadinessCarryForwardSummary($c130, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c130, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['activation_approval_confirmation_summary'] = $this->activationApprovalConfirmationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['feature_flag_default_off_summary'] = $this->featureFlagDefaultOffSummary();
        $artifact['c131_approval_decision'] = $this->activationApprovalDecision($pass);
        $artifact['next_execution_decision'] = $this->nextExecutionDecision($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_approval_manifest'] = $this->activationApprovalManifest($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_approval_checklist'] = $this->activationApprovalChecklist();
        $artifact['c131_candidate_activation_approval_scorecard'] = $this->candidateScorecard($pass);
        $artifact['production_live_runtime_activation_approval_context_summary'] = $this->activationApprovalContextSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C131_PENDING')]);
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

    private function c130ActivationReadinessComplete(array $c130): bool
    {
        foreach (self::REQUIRED_C130_TRUE_FIELDS as $field) {
            if (! (bool) ($c130[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C130_FALSE_FIELDS as $field) {
            if ((bool) ($c130[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c130NextRecommendationMatches(array $c130): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c130_readiness_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c130, $path);
            if ($value !== null && $value !== self::EXPECTED_C130_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c130['next_step_recommendation'] ?? null) === self::EXPECTED_C130_NEXT_RECOMMENDATION;
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

    private function c130LockValidationSummary(array $load, array $c130): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C130',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C130_STATUS,
            'actual_status' => $c130['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C130_PHASE_LABEL,
            'actual_phase_label' => $c130['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C130_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c130NextRecommendationMatches($c130),
            'c130_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c130ActivationReadinessCarryForwardSummary(array $c130, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c130_activation_readiness_review_pass' => (bool) ($c130['weekly_swing_watchlist_production_live_runtime_activation_readiness_review_pass'] ?? false),
            'c130_ready_for_activation_approval_review' => (bool) ($c130['ready_for_production_live_runtime_activation_approval_review'] ?? false),
            'c130_activation_readiness_manifest_created' => (bool) ($c130['production_live_runtime_activation_readiness_manifest_created'] ?? false),
            'c129_final_closure_valid' => (bool) ($c130['c129_final_closure_valid'] ?? false),
            'c129_audit_archive_terminal' => (bool) ($c130['c129_audit_archive_terminal'] ?? false),
            'c130_activation_readiness_valid' => $this->c130ActivationReadinessComplete($c130),
            'c131_activation_approval_can_start' => $pass,
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
            'primary_candidate_role' => 'primary_production_live_runtime_activation_approval_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_approval_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_production_live_runtime_activation_execution_review' => $pass,
            'backup_candidate_ready_for_production_live_runtime_activation_execution_review' => $pass,
            'comparator_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
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

    private function activationApprovalConfirmationSummary(array $options, bool $pass): array
    {
        return [
            'production_live_runtime_activation_approval_confirmation_required' => true,
            'production_live_runtime_activation_approval_confirmed' => (bool) ($options['production_live_runtime_activation_approval_confirmed'] ?? false),
            'activation_approval_confirmation_pass' => $pass,
            'activation_execution_requested' => false,
            'live_runtime_mutation_requested' => false,
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

    private function activationApprovalDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c130_activation_readiness_valid' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_pass' => $pass,
            'production_live_runtime_activation_approval_granted' => $pass,
            'ready_for_production_live_runtime_activation_execution_review' => $pass,
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
            'next_recommendation' => $pass ? self::C132_RECOMMENDATION : 'C131_TARGETED_C130_ACTIVATION_READINESS_OR_APPROVAL_REPAIR',
            'decision_reason' => $pass ? 'C131 grants approval to proceed to production/live activation execution review only.' : 'C131 cannot proceed until C130 lock, readiness, approval, confirmation, feature flags, and safety gates pass.',
        ];
    }

    private function nextExecutionDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C132_RECOMMENDATION : 'C131_TARGETED_C130_ACTIVATION_READINESS_OR_APPROVAL_REPAIR',
            'next_scope' => $pass ? 'production/live runtime activation execution review only; still no runtime bridge activation, live output generation, official publication, or PLAN/CONFIRM mutation in C131' : 'targeted C130 readiness, approval, feature flag, or cleanup repair only',
        ];
    }

    private function activationApprovalManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_production_live_runtime_activation_approval_review',
            'source_artifact' => self::EXPECTED_C130_STATUS,
            'source_artifact_path' => self::DEFAULT_C130_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C130_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C130_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_approval_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_approval_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'c130_activation_readiness_carried_forward' => $pass,
            'production_live_runtime_activation_approval_granted' => $pass,
            'ready_for_production_live_runtime_activation_execution_review' => $pass,
            'production_live_runtime_activation_execution_review_required_next' => $pass,
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
            'activation_approval_used_for_plan_confirm_mutation' => false,
            'activation_approval_used_for_live_rollout' => false,
            'activation_approval_artifact_only' => true,
        ];
    }

    private function activationApprovalChecklist(): array
    {
        return [
            'c130_activation_readiness_artifact_locked' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'operator_approval_required' => true,
            'activation_approval_confirmation_required' => true,
            'feature_flags_default_off_required' => true,
            'kill_switch_required' => true,
            'rollback_plan_required_for_future_activation' => true,
            'manual_validation_required_before_future_activation' => true,
            'live_runtime_activation_execution_review_required_next' => true,
            'approval_review_only' => true,
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
            'production_live_runtime_activation_approval_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_execution_review' => $pass,
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
                'c131_role' => 'primary_production_live_runtime_activation_approval_candidate',
                'primary_candidate_ready_for_production_live_runtime_activation_execution_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c131_role' => 'backup_production_live_runtime_activation_approval_candidate',
                'backup_candidate_ready_for_production_live_runtime_activation_execution_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c131_role' => 'comparator_only_candidate',
                'production_live_runtime_activation_approval_review_pass' => false,
                'ready_for_production_live_runtime_activation_execution_review' => false,
                'comparator_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function activationApprovalContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_approval_context_created' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_approval_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_approval_context_persisted_to_live_runtime' => false,
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
            'progress_marker' => 'PR-19_C131_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW',
            'c130_activation_readiness_carried_forward' => true,
            'c131_production_live_runtime_activation_approval_review_executed' => true,
            'c131_ready_for_activation_execution_review' => $pass,
            'still_no_live_runtime' => true,
            'still_no_runtime_bridge_activation' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C132_RECOMMENDATION : 'C131_TARGETED_C130_ACTIVATION_READINESS_OR_APPROVAL_REPAIR',
            'planned_next_scope' => $pass ? 'production/live runtime activation execution review only; not official weekly swing output publication or PLAN/CONFIRM mutation in C131' : 'targeted repair before production/live activation approval can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C130 artifact hash',
                'locked C130 file SHA1',
                'operator approval reference',
                'activation approval confirmation',
                'runtime feature flags still default-off',
                'future activation execution review contract',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c130_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c130_artifact_not_modified' => true,
            'c60_c130_artifacts_not_modified' => true,
            'c131_is_approval_review_not_execution' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C131 validates C130 artifact_hash and file SHA1 locks before production/live runtime activation approval is recorded.',
            'C131 validates C130 activation readiness and next recommendation to C131.',
            'C131 requires --operator-approved, a non-empty --approval-reference, and --production-live-runtime-activation-approval-confirmed.',
            'C131 keeps runtime feature flags default-off and checks the kill-switch surface.',
            'C131 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C131 grants approval to proceed to a future activation execution review only.',
            'C131 does not activate runtime bridge, execute production runtime wiring, generate official weekly swing output, publish recommendations, or mutate PLAN/CONFIRM.',
            'C131 may only recommend C132 production/live runtime activation execution review as the next controlled step.',
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
            'c130' => [
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
            'expected_c130_hash' => $load['expected_hash'],
            'actual_c130_hash' => $load['actual_hash'],
            'c130_hash_match' => $load['hash_match'],
            'expected_c130_file_sha1' => $load['expected_file_sha1'],
            'actual_c130_file_sha1' => $load['actual_file_sha1'],
            'c130_file_sha1_match' => $load['file_sha1_match'],
            'c130_convert_from_json_pass' => $load['convert_from_json_pass'],
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
        $artifact['next_execution_decision'] = $this->nextExecutionDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_execution_decision'] = $this->nextExecutionDecision(false);
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
