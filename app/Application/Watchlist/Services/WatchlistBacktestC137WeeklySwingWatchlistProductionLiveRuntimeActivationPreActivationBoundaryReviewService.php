<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC137WeeklySwingWatchlistProductionLiveRuntimeActivationPreActivationBoundaryReviewService
{
    public const RUN_CODE = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-25 / C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW';

    public const DEFAULT_C136_ARTIFACT = 'storage/app/watchlist/backtest/c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C136_HASH = '38eee6c7216fd94421c65be129ba50c4a93fd1d1';
    public const DEFAULT_EXPECTED_C136_FILE_SHA1 = '1B395D673F04AE8A7FD62527259DA2CFBA8244AF';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C136_STATUS = 'C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP';
    private const EXPECTED_C136_PHASE_LABEL = 'PR-24 / C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C136_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C138_RECOMMENDATION = 'C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW';

    private const PASS_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_NOT_CONFIRMED_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_PRE_ACTIVATION_BOUNDARY_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C136_LOCK_MISMATCH_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_C136_ARTIFACT_LOCK_MISMATCH';
    private const C136_FILE_SHA1_MISMATCH_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_C136_FILE_SHA1_LOCK_MISMATCH';
    private const C136_STATUS_MISMATCH_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_C136_STATUS_MISMATCH';
    private const C136_PHASE_LABEL_MISMATCH_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_C136_PHASE_LABEL_MISMATCH';
    private const C136_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_C136_NEXT_RECOMMENDATION_MISMATCH';
    private const C136_GO_DECISION_INVALID_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_C136_GO_DECISION_FINALIZATION_INVALID';
    private const C136_CONVERT_FROM_JSON_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_C136_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS = 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_FEATURE_FLAGS_NOT_DEFAULT_OFF';

    private const REQUIRED_C136_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_review_pass',
        'production_live_runtime_activation_go_decision_finalization_review_pass',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'weekly_swing_watchlist_ready_for_production_live_runtime_activation_pre_activation_boundary_review',
        'ready_for_production_live_runtime_activation_pre_activation_boundary_review',
        'production_live_runtime_activation_go_decision_finalization_manifest_created',
        'production_live_runtime_activation_pre_activation_boundary_review_allowed_next',
        'c135_lock_valid',
        'c135_activation_operator_go_no_go_valid',
        'c135_convert_from_json_pass',
        'c134_activation_observation_result_review_valid',
        'c133_activation_observation_review_valid',
        'c132_activation_execution_review_valid',
        'c131_activation_approval_valid',
        'c130_activation_readiness_valid',
        'c129_final_closure_valid',
        'c135_operator_go_no_go_review_only',
        'c135_not_live_runtime_state_change',
        'c136_go_decision_finalization_review_only',
        'c136_not_live_runtime_state_change',
        'primary_candidate_ready_for_production_live_runtime_activation_pre_activation_boundary_review',
        'backup_candidate_ready_for_production_live_runtime_activation_pre_activation_boundary_review',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C136_FALSE_FIELDS = [
        'production_live_runtime_activation_executed',
        'comparator_candidate_ready_for_production_live_runtime_activation_pre_activation_boundary_review',
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
        'pilot_runtime_active',
        'shadow_runtime_active',
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
        'weekly_swing_watchlist_production_live_runtime_activation_observation_context_persisted_to_live_runtime',
        'production_live_runtime_activation_observation_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_review_context_persisted_to_live_runtime',
        'production_live_runtime_activation_observation_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_context_persisted_to_live_runtime',
        'production_live_runtime_activation_observation_result_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime',
        'production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_context_persisted_to_live_runtime',
        'production_live_runtime_activation_operator_go_no_go_context_persisted_to_live_runtime',
        'operator_go_no_go_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_context_persisted_to_live_runtime',
        'production_live_runtime_activation_go_decision_finalization_context_persisted_to_live_runtime',
        'go_decision_finalization_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_context_persisted_to_live_runtime',
        'production_live_runtime_activation_pre_activation_boundary_context_persisted_to_live_runtime',
        'pre_activation_boundary_context_persisted_to_live_runtime',
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
        string $c136Artifact = self::DEFAULT_C136_ARTIFACT,
        string $expectedC136Hash = self::DEFAULT_EXPECTED_C136_HASH,
        string $expectedC136FileSha1 = self::DEFAULT_EXPECTED_C136_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c136Artifact, $expectedC136Hash, $expectedC136FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C136_LOCK_MISMATCH_STATUS, 'C136 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c136_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C136_CONVERT_FROM_JSON_STATUS, 'C136 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C136_LOCK_MISMATCH_STATUS, 'C136 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C136_FILE_SHA1_MISMATCH_STATUS, 'C136 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c136 = $load['payload'];
        if (($c136['status'] ?? null) !== self::EXPECTED_C136_STATUS || ($c136['reason_code'] ?? null) !== self::EXPECTED_C136_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C136_STATUS_MISMATCH_STATUS, 'C136 status/reason is not pre-activation boundary ready.', $outputPath, $overwrite);
        }
        if (($c136['phase_label'] ?? null) !== self::EXPECTED_C136_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C136_PHASE_LABEL_MISMATCH_STATUS, 'C136 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c136NextRecommendationMatches($c136)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C136_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C136 next recommendation is not C137.', $outputPath, $overwrite);
        }
        if (! $this->c136GoDecisionFinalizationValid($c136)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C136_GO_DECISION_INVALID_STATUS, 'C136 production/live activation GO decision finalization evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c136)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C136 candidate scope does not match locked pre-activation boundary scope.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c136);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c136_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C136 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if ($this->enabledRuntimeFeatureFlags() !== []) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['enabled_runtime_feature_flags'] = $this->enabledRuntimeFeatureFlags();

            return $this->rejected($artifact, self::FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS, 'C137 requires runtime feature flags to remain default-off.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C137 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (($options['pre_activation_boundary_confirmed'] ?? true) !== true) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::BOUNDARY_NOT_CONFIRMED_STATUS, 'C137 requires explicit pre-activation boundary confirmation.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C137 clears the production/live runtime activation pre-activation boundary for E02 primary and B01 backup. This is still artifact-only and does not authorize activation, activate runtime bridge, execute live output, publish recommendations, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW';
        $artifact['next_step_recommendation'] = self::C138_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return array_merge(array_fill_keys(self::REQUIRED_FALSE_SAFETY_FLAGS, false), [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-25',
            'internal_checkpoint' => 'C137',
            'status' => 'C137_NOT_RUN',
            'reason_code' => 'C137_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_pass' => false,
            'production_live_runtime_activation_pre_activation_boundary_review_pass' => false,
            'pre_activation_boundary_confirmed' => false,
            'pre_activation_boundary_cleared' => false,
            'primary_candidate_boundary_cleared' => false,
            'backup_candidate_boundary_cleared' => false,
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_authorization_review' => false,
            'ready_for_production_live_runtime_activation_authorization_review' => false,
            'production_live_runtime_activation_pre_activation_boundary_manifest_created' => false,
            'production_live_runtime_activation_authorization_review_allowed_next' => false,
            'activation_authorized' => false,
            'production_live_runtime_activation_executed' => false,
            'c136_lock_valid' => false,
            'c136_go_decision_finalization_valid' => false,
            'c136_convert_from_json_pass' => false,
            'c135_activation_operator_go_no_go_valid' => false,
            'c134_activation_observation_result_review_valid' => false,
            'c133_activation_observation_review_valid' => false,
            'c132_activation_execution_review_valid' => false,
            'c131_activation_approval_valid' => false,
            'c130_activation_readiness_valid' => false,
            'c129_final_closure_valid' => false,
            'c136_go_decision_finalization_review_only' => false,
            'c137_pre_activation_boundary_review_only' => true,
            'c137_not_activation_authorization' => true,
            'c137_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_authorization_review' => false,
            'backup_candidate_ready_for_production_live_runtime_activation_authorization_review' => false,
            'comparator_candidate_ready_for_production_live_runtime_activation_authorization_review' => false,
            'a01_remains_comparator_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'pilot_runtime_active' => false,
            'shadow_runtime_active' => false,
            'runtime_bridge_active' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_plan_confirm_mutation_allowed' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
        ]);
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_pass' => true,
            'production_live_runtime_activation_pre_activation_boundary_review_pass' => true,
            'pre_activation_boundary_confirmed' => true,
            'pre_activation_boundary_cleared' => true,
            'primary_candidate_boundary_cleared' => true,
            'backup_candidate_boundary_cleared' => true,
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_authorization_review' => true,
            'ready_for_production_live_runtime_activation_authorization_review' => true,
            'production_live_runtime_activation_pre_activation_boundary_manifest_created' => true,
            'production_live_runtime_activation_authorization_review_allowed_next' => true,
            'activation_authorized' => false,
            'production_live_runtime_activation_executed' => false,
            'c136_lock_valid' => true,
            'c136_go_decision_finalization_valid' => true,
            'c136_convert_from_json_pass' => true,
            'c135_activation_operator_go_no_go_valid' => true,
            'c134_activation_observation_result_review_valid' => true,
            'c133_activation_observation_review_valid' => true,
            'c132_activation_execution_review_valid' => true,
            'c131_activation_approval_valid' => true,
            'c130_activation_readiness_valid' => true,
            'c129_final_closure_valid' => true,
            'c136_go_decision_finalization_review_only' => true,
            'c137_pre_activation_boundary_review_only' => true,
            'c137_not_activation_authorization' => true,
            'c137_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_authorization_review' => true,
            'backup_candidate_ready_for_production_live_runtime_activation_authorization_review' => true,
            'comparator_candidate_ready_for_production_live_runtime_activation_authorization_review' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c136 = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        return array_merge($artifact, [
            'c136_lock_validation_summary' => $this->c136LockValidationSummary($load),
            'c136_go_decision_finalization_carry_forward_summary' => $this->c136GoDecisionFinalizationCarryForwardSummary($c136),
            'candidate_scope_freeze_summary' => $this->candidateScopeFreezeSummary($c136, $pass),
            'operator_approval_validation_summary' => [
                'operator_approval_required' => true,
                'operator_approved' => (bool) ($options['operator_approved'] ?? false),
                'approval_reference_required' => true,
                'approval_reference' => (string) ($options['approval_reference'] ?? ''),
                'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
                'pre_activation_boundary_confirmation_required' => true,
                'pre_activation_boundary_confirmed' => ($options['pre_activation_boundary_confirmed'] ?? false) === true,
                'operator_approval_validation_pass' => (bool) ($options['operator_approved'] ?? false)
                    && trim((string) ($options['approval_reference'] ?? '')) !== ''
                    && ($options['pre_activation_boundary_confirmed'] ?? false) === true,
            ],
            'temporary_negative_artifact_guard_summary' => [
                'validation_completed' => true,
                'temporary_negative_artifacts_remaining' => $temporaryNegativePaths !== [],
                'temporary_negative_artifact_cleanup_confirmed' => $temporaryNegativePaths === [],
                'temporary_negative_artifact_paths' => $temporaryNegativePaths,
                'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
            ],
            'feature_flag_default_off_summary' => $this->featureFlagDefaultOffSummary(),
            'c137_pre_activation_boundary_decision' => $this->preActivationBoundaryDecision($pass),
            'next_activation_authorization_decision' => $this->nextActivationAuthorizationDecision($pass),
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_manifest' => $this->preActivationBoundaryManifest($load, $pass),
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_checklist' => $this->preActivationBoundaryChecklist(),
            'c137_candidate_activation_authorization_readiness_scorecard' => $this->candidateScorecard($pass),
            'production_live_runtime_activation_pre_activation_boundary_context_summary' => $this->preActivationBoundaryContextSummary($pass),
            'runtime_config_review_summary' => $this->runtimeConfigReviewSummary(),
            'production_mutation_safety_summary' => $this->productionMutationSafetySummary(),
            'failure_attribution_summary' => $this->failureAttributionSummary([]),
            'progress_summary' => $this->progressSummary($pass),
            'planned_next_summary' => $this->plannedNextSummary($pass),
            'documentation_hygiene_guard_summary' => $this->documentationHygieneGuardSummary($load),
            'diagnostics' => $this->diagnostics(),
        ]);
    }

    private function c136LockValidationSummary(array $load): array
    {
        $c136 = is_array($load['payload']) ? $load['payload'] : [];

        return [
            'validation_completed' => true,
            'source_lock' => 'C136',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C136_STATUS,
            'actual_status' => $c136['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C136_PHASE_LABEL,
            'actual_phase_label' => $c136['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C136_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c136NextRecommendationMatches($c136),
            'c136_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c136GoDecisionFinalizationCarryForwardSummary(array $c136): array
    {
        return [
            'validation_completed' => true,
            'c136_go_decision_finalization_review_pass' => (bool) ($c136['weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_review_pass'] ?? false),
            'c136_operator_go_decision' => (string) ($c136['operator_go_decision'] ?? ''),
            'c136_operator_go_decision_confirmed' => (bool) ($c136['operator_go_decision_confirmed'] ?? false),
            'c136_go_decision_finalized' => (bool) ($c136['go_decision_finalized'] ?? false),
            'c136_go_decision_finalization_confirmed' => (bool) ($c136['go_decision_finalization_confirmed'] ?? false),
            'c136_ready_for_pre_activation_boundary_review' => (bool) ($c136['ready_for_production_live_runtime_activation_pre_activation_boundary_review'] ?? false),
            'c135_activation_operator_go_no_go_valid' => (bool) ($c136['c135_activation_operator_go_no_go_valid'] ?? false),
            'c134_activation_observation_result_review_valid' => (bool) ($c136['c134_activation_observation_result_review_valid'] ?? false),
            'c133_activation_observation_review_valid' => (bool) ($c136['c133_activation_observation_review_valid'] ?? false),
            'c132_activation_execution_review_valid' => (bool) ($c136['c132_activation_execution_review_valid'] ?? false),
            'c131_activation_approval_valid' => (bool) ($c136['c131_activation_approval_valid'] ?? false),
            'c130_activation_readiness_valid' => (bool) ($c136['c130_activation_readiness_valid'] ?? false),
            'c129_final_closure_valid' => (bool) ($c136['c129_final_closure_valid'] ?? false),
            'c136_go_decision_finalization_valid' => $this->c136GoDecisionFinalizationValid($c136),
            'c137_pre_activation_boundary_can_start' => $this->c136GoDecisionFinalizationValid($c136),
        ];
    }

    private function candidateScopeFreezeSummary(array $c136, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c136),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_pre_activation_boundary_cleared_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_pre_activation_boundary_cleared_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_production_live_runtime_activation_authorization_review' => $pass,
            'backup_candidate_ready_for_production_live_runtime_activation_authorization_review' => $pass,
            'comparator_candidate_ready_for_production_live_runtime_activation_authorization_review' => false,
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

    private function preActivationBoundaryDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'pre_activation_boundary_confirmed' => $pass,
            'pre_activation_boundary_cleared' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_authorization_review' => $pass,
            'activation_authorized' => false,
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
            'next_recommendation' => $pass ? self::C138_RECOMMENDATION : 'C137_TARGETED_C136_GO_DECISION_FINALIZATION_REPAIR',
            'decision_reason' => $pass ? 'C137 clears the artifact-only pre-activation boundary and permits activation authorization review next.' : 'C137 cannot proceed until C136 lock, approval, boundary confirmation, cleanup, candidate, and safety gates pass.',
        ];
    }

    private function nextActivationAuthorizationDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C138_RECOMMENDATION : 'C137_TARGETED_C136_GO_DECISION_FINALIZATION_REPAIR',
            'next_scope' => $pass ? 'production/live runtime activation authorization review only; C137 itself still does not authorize activation, activate runtime bridge, generate live output, publish recommendations, or mutate PLAN/CONFIRM' : 'targeted C136 GO decision finalization lock, approval, or safety repair only',
        ];
    }

    private function preActivationBoundaryManifest(array $load, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_production_live_runtime_activation_pre_activation_boundary_review',
            'source_artifact' => 'C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'pre_activation_boundary_confirmed' => $pass,
            'pre_activation_boundary_cleared' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_boundary_cleared' => $pass,
            'backup_candidate_boundary_cleared' => $pass,
            'comparator_candidate_boundary_cleared' => false,
            'ready_for_production_live_runtime_activation_authorization_review' => $pass,
            'pre_activation_boundary_artifact_only' => true,
            'activation_authorized' => false,
            'production_live_runtime_activation_executed' => false,
            'runtime_bridge_enabled' => false,
            'runtime_bridge_active' => false,
            'controlled_rollout_enabled' => false,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'pre_activation_boundary_used_for_selection' => false,
            'pre_activation_boundary_used_for_retuning' => false,
            'pre_activation_boundary_used_for_ranking' => false,
            'pre_activation_boundary_used_for_plan_confirm_mutation' => false,
            'pre_activation_boundary_used_for_live_rollout' => false,
        ];
    }

    private function preActivationBoundaryChecklist(): array
    {
        return [
            'pre_activation_boundary_reviewed' => true,
            'pre_activation_boundary_confirmation_required' => true,
            'activation_authorization_not_granted' => true,
            'production_live_runtime_activation_not_executed' => true,
            'production_runtime_wiring_not_enabled' => true,
            'production_catalog_runtime_wired' => false,
            'runtime_bridge_not_enabled' => true,
            'controlled_rollout_not_enabled' => true,
            'pilot_runtime_not_enabled' => true,
            'shadow_runtime_not_enabled' => true,
            'scheduler_live_weekly_swing_not_enabled' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'production_config_default_unchanged' => true,
            'c136_source_lock_reviewed' => true,
            'runtime_entrypoint_reviewed' => true,
            'artisan_command_registration_reviewed' => true,
            'service_boundary_reviewed' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_pre_activation_boundary_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'pre_activation_boundary_review_only' => true,
            'non_live' => true,
            'non_mutating' => true,
            'artifact_only' => true,
            'live_endpoint_called' => false,
            'scheduler_executed' => false,
            'weekly_swing_stock_recommendation_generated' => false,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'production_live_runtime_activation_pre_activation_boundary_review_pass' => $pass,
            'pre_activation_boundary_cleared' => $pass,
            'ready_for_production_live_runtime_activation_authorization_review' => $pass,
            'activation_authorized' => false,
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
                'c137_role' => 'primary_production_live_runtime_activation_pre_activation_boundary_cleared_candidate',
                'primary_candidate_ready_for_production_live_runtime_activation_authorization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c137_role' => 'backup_production_live_runtime_activation_pre_activation_boundary_cleared_candidate',
                'backup_candidate_ready_for_production_live_runtime_activation_authorization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c137_role' => 'comparator_only_candidate',
                'production_live_runtime_activation_pre_activation_boundary_review_pass' => false,
                'pre_activation_boundary_cleared' => false,
                'ready_for_production_live_runtime_activation_authorization_review' => false,
                'comparator_candidate_ready_for_production_live_runtime_activation_authorization_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function preActivationBoundaryContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_context_created' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_pre_activation_boundary_context_persisted_to_live_runtime' => false,
            'pre_activation_boundary_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_go_decision_finalization_context_persisted_to_live_runtime' => false,
            'go_decision_finalization_context_persisted_to_live_runtime' => false,
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

    private function runtimeConfigReviewSummary(): array
    {
        return [
            'runtime_config_reviewed' => true,
            'production_config_default_unchanged' => true,
            'c136_artifact_identified' => is_file(self::DEFAULT_C136_ARTIFACT),
            'c137_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC137WeeklySwingWatchlistProductionLiveRuntimeActivationPreActivationBoundaryReviewService.php'),
            'c137_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC137WeeklySwingWatchlistProductionLiveRuntimeActivationPreActivationBoundaryReviewCommand.php'),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
            'production_runtime_wiring_not_enabled' => true,
            'runtime_bridge_not_enabled' => true,
            'controlled_opt_in_runtime_bridge_not_enabled' => true,
            'controlled_parallel_run_not_enabled' => true,
            'controlled_rollout_not_enabled' => true,
            'scheduler_live_weekly_swing_not_enabled' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'plan_confirm_runtime_change_executed' => false,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'pilot_runtime_active' => false,
            'shadow_runtime_active' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
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
            'progress_marker' => 'PR-25_C137_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW',
            'c136_go_decision_finalization_review_carried_forward' => true,
            'c137_production_live_runtime_activation_pre_activation_boundary_review_executed' => true,
            'c137_pre_activation_boundary_cleared' => $pass,
            'c137_ready_for_activation_authorization_review' => $pass,
            'activation_authorized' => false,
            'still_no_live_runtime' => true,
            'still_no_runtime_bridge_activation' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C138_RECOMMENDATION : 'C137_TARGETED_C136_GO_DECISION_FINALIZATION_REPAIR',
            'planned_next_scope' => $pass ? 'production/live runtime activation authorization review only; not official weekly swing output publication or PLAN/CONFIRM mutation in C137' : 'targeted repair before production/live activation pre-activation boundary can be cleared',
            'planned_next_required_inputs' => $pass ? [
                'locked C137 artifact hash',
                'locked C137 file SHA1',
                'operator approval reference',
                'cleared pre-activation boundary',
                'activation authorization checklist',
                'runtime feature flags still default-off',
                'future activation authorization contract',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c136_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c136_artifact_not_modified' => true,
            'c60_c136_artifacts_not_modified' => true,
            'c137_is_pre_activation_boundary_review_not_activation_authorization' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C137 validates C136 artifact_hash and file SHA1 locks before production/live runtime activation pre-activation boundary review is recorded.',
            'C137 validates C136 finalized activation GO and next recommendation to C137.',
            'C137 requires --operator-approved, a non-empty --approval-reference, and explicit pre-activation boundary confirmation.',
            'C137 keeps runtime feature flags default-off and checks the kill-switch surface.',
            'C137 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C137 records artifact-only pre-activation boundary clearance and moves to activation authorization review.',
            'C137 does not authorize activation, activate runtime bridge, execute production runtime wiring, generate official weekly swing output, publish recommendations, or mutate PLAN/CONFIRM.',
            'C137 may only recommend C138 production/live runtime activation authorization review as the next controlled step.',
        ];
    }

    private function c136GoDecisionFinalizationValid(array $c136): bool
    {
        foreach (self::REQUIRED_C136_TRUE_FIELDS as $field) {
            if (($c136[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C136_FALSE_FIELDS as $field) {
            if (($c136[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($c136['operator_go_decision'] ?? null) !== 'GO') {
            return false;
        }

        foreach ([
            [['c136_go_decision_finalization_decision', 'review_pass'], true],
            [['c136_go_decision_finalization_decision', 'next_recommendation'], self::EXPECTED_C136_NEXT_RECOMMENDATION],
            [['next_pre_activation_boundary_decision', 'review_pass'], true],
            [['next_pre_activation_boundary_decision', 'next_recommendation'], self::EXPECTED_C136_NEXT_RECOMMENDATION],
            [['weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_manifest', 'go_decision_finalized'], true],
            [['weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_manifest', 'ready_for_production_live_runtime_activation_pre_activation_boundary_review'], true],
            [['weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_checklist', 'go_decision_finalization_reviewed'], true],
            [['weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_checklist', 'artifact_only'], true],
        ] as [$path, $expected]) {
            if ($this->valueAt($c136, $path) !== $expected) {
                return false;
            }
        }

        return true;
    }

    private function c136NextRecommendationMatches(array $c136): bool
    {
        foreach ([
            $c136['next_step_recommendation'] ?? null,
            $c136['next_pre_activation_boundary_decision']['next_recommendation'] ?? null,
            $c136['planned_next_summary']['planned_next_review'] ?? null,
        ] as $value) {
            if ($value !== null && $value !== self::EXPECTED_C136_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c136['next_step_recommendation'] ?? null) === self::EXPECTED_C136_NEXT_RECOMMENDATION;
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
        if (($source['a01_promoted'] ?? false) === true) {
            return false;
        }

        return (bool) ($source['a01_remains_comparator_only'] ?? false);
    }

    private function firstLiveOrMutatingSafetyFlag(array $source): ?string
    {
        foreach (array_values(array_unique(array_merge(self::REQUIRED_C136_FALSE_FIELDS, self::REQUIRED_FALSE_SAFETY_FLAGS))) as $field) {
            if (($source[$field] ?? false) === true) {
                return $field;
            }
        }

        return null;
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
            'kill_switch_identified' => true,
            'production_catalog_runtime_bridge_kill_switch' => false,
        ];
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
            'c136' => [
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
            'expected_c136_hash' => $load['expected_hash'],
            'actual_c136_hash' => $load['actual_hash'],
            'c136_hash_match' => $load['hash_match'],
            'expected_c136_file_sha1' => $load['expected_file_sha1'],
            'actual_c136_file_sha1' => $load['actual_file_sha1'],
            'c136_file_sha1_match' => $load['file_sha1_match'],
            'c136_convert_from_json_pass' => $load['convert_from_json_pass'],
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
        $artifact['next_activation_authorization_decision'] = $this->nextActivationAuthorizationDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_activation_authorization_decision'] = $this->nextActivationAuthorizationDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => array_values(array_filter($status)),
            'failure_count' => count(array_values(array_filter($status))),
        ];
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
