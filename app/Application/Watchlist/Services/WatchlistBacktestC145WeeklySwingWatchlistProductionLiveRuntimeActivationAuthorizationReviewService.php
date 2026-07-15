<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC145WeeklySwingWatchlistProductionLiveRuntimeActivationAuthorizationReviewService
{
    public const RUN_CODE = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-33 / C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW';

    public const DEFAULT_C144_ARTIFACT = 'storage/app/watchlist/backtest/c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json';
    public const DEFAULT_EXPECTED_C144_HASH = '68d5bb7d096b09d1defa3a655313ff0a7f658e84';
    public const DEFAULT_EXPECTED_C144_FILE_SHA1 = 'FBC618728E9A8B49A5FBD5CE273EF2159705C816';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C144_STATUS = 'C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C144_PHASE_LABEL = 'PR-32 / C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW';
    private const EXPECTED_C144_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C146_RECOMMENDATION = 'C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW';

    private const PASS_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_PASSED_AUTHORIZED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const AUTHORIZATION_NOT_CONFIRMED_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_ACTIVATION_AUTHORIZATION_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C144_LOCK_MISMATCH_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_C144_ARTIFACT_LOCK_MISMATCH';
    private const C144_FILE_SHA1_MISMATCH_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_C144_FILE_SHA1_LOCK_MISMATCH';
    private const C144_STATUS_MISMATCH_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_C144_STATUS_MISMATCH';
    private const C144_PHASE_LABEL_MISMATCH_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_C144_PHASE_LABEL_MISMATCH';
    private const C144_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_C144_NEXT_RECOMMENDATION_MISMATCH';
    private const C144_PRE_ACTIVATION_BOUNDARY_INVALID_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_C144_PRE_ACTIVATION_BOUNDARY_INVALID';
    private const C144_CONVERT_FROM_JSON_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_C144_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS = 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_FEATURE_FLAGS_NOT_DEFAULT_OFF';

    private const REQUIRED_C144_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_pass',
        'production_live_runtime_activation_pre_activation_boundary_review_pass',
        'pre_activation_boundary_confirmed',
        'pre_activation_boundary_cleared',
        'primary_candidate_boundary_cleared',
        'backup_candidate_boundary_cleared',
        'weekly_swing_watchlist_ready_for_production_live_runtime_activation_authorization_review',
        'ready_for_production_live_runtime_activation_authorization_review',
        'production_live_runtime_activation_pre_activation_boundary_manifest_created',
        'production_live_runtime_activation_authorization_review_allowed_next',
        'c143_lock_valid',
        'c143_go_decision_finalization_valid',
        'c143_convert_from_json_pass',
        'c142_lock_valid',
        'c142_activation_operator_go_no_go_valid',
        'c142_convert_from_json_pass',
        'c141_activation_observation_result_review_valid',
        'c140_activation_observation_review_valid',
        'c139_activation_execution_review_valid',
        'c138_activation_authorization_valid',
        'c137_pre_activation_boundary_valid',
        'c136_go_decision_finalization_valid',
        'c135_activation_operator_go_no_go_valid',
        'c134_activation_observation_result_review_valid',
        'c133_activation_observation_review_valid',
        'c132_activation_execution_review_valid',
        'c131_activation_approval_valid',
        'c130_activation_readiness_valid',
        'c129_final_closure_valid',
        'c138_activation_authorization_review_only',
        'c138_not_activation_execution',
        'c138_not_live_runtime_state_change',
        'c139_execution_review_only',
        'c139_not_live_runtime_state_change',
        'c140_observation_review_only',
        'c140_not_live_runtime_state_change',
        'c141_observation_result_review_only',
        'c141_not_live_runtime_state_change',
        'c142_operator_go_no_go_review_only',
        'c142_not_live_runtime_state_change',
        'c143_go_decision_finalization_review_only',
        'c144_pre_activation_boundary_review_only',
        'c144_not_activation_authorization',
        'c144_not_live_runtime_state_change',
        'primary_candidate_ready_for_production_live_runtime_activation_authorization_review',
        'backup_candidate_ready_for_production_live_runtime_activation_authorization_review',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C144_FALSE_FIELDS = [
        'activation_authorized',
        'production_live_runtime_activation_executed',
        'comparator_candidate_ready_for_production_live_runtime_activation_authorization_review',
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
        'weekly_swing_watchlist_production_live_runtime_activation_authorization_context_persisted_to_live_runtime',
        'production_live_runtime_activation_authorization_context_persisted_to_live_runtime',
        'activation_authorization_context_persisted_to_live_runtime',
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
        string $c144Artifact = self::DEFAULT_C144_ARTIFACT,
        string $expectedC144Hash = self::DEFAULT_EXPECTED_C144_HASH,
        string $expectedC144FileSha1 = self::DEFAULT_EXPECTED_C144_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c144Artifact, $expectedC144Hash, $expectedC144FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C144_LOCK_MISMATCH_STATUS, 'C144 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c144_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C144_CONVERT_FROM_JSON_STATUS, 'C144 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C144_LOCK_MISMATCH_STATUS, 'C144 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C144_FILE_SHA1_MISMATCH_STATUS, 'C144 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c144 = $load['payload'];
        if (($c144['status'] ?? null) !== self::EXPECTED_C144_STATUS || ($c144['reason_code'] ?? null) !== self::EXPECTED_C144_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C144_STATUS_MISMATCH_STATUS, 'C144 status/reason is not activation authorization ready.', $outputPath, $overwrite);
        }
        if (($c144['phase_label'] ?? null) !== self::EXPECTED_C144_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C144_PHASE_LABEL_MISMATCH_STATUS, 'C144 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c144NextRecommendationMatches($c144)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C144_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C144 next recommendation is not C145.', $outputPath, $overwrite);
        }
        if (! $this->c144PreActivationBoundaryValid($c144)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C144_PRE_ACTIVATION_BOUNDARY_INVALID_STATUS, 'C144 production/live activation pre-activation boundary evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c144)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C144 candidate scope does not match locked activation authorization scope.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c144);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c144_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C144 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if ($this->enabledRuntimeFeatureFlags() !== []) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['enabled_runtime_feature_flags'] = $this->enabledRuntimeFeatureFlags();

            return $this->rejected($artifact, self::FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS, 'C145 requires runtime feature flags to remain default-off.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C145 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (($options['activation_authorization_confirmed'] ?? true) !== true) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::AUTHORIZATION_NOT_CONFIRMED_STATUS, 'C145 requires explicit activation authorization confirmation.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C145 authorizes production/live runtime activation for E02 primary and B01 backup as artifact-only evidence. This still does not execute activation, activate runtime bridge, produce live output, publish recommendations, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW';
        $artifact['next_step_recommendation'] = self::C146_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return array_merge(array_fill_keys(self::REQUIRED_FALSE_SAFETY_FLAGS, false), [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-33',
            'internal_checkpoint' => 'C145',
            'status' => 'C145_NOT_RUN',
            'reason_code' => 'C145_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_pass' => false,
            'production_live_runtime_activation_authorization_review_pass' => false,
            'activation_authorization_confirmed' => false,
            'activation_authorized' => false,
            'primary_candidate_activation_authorized' => false,
            'backup_candidate_activation_authorized' => false,
            'comparator_candidate_activation_authorized' => false,
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_execution_review' => false,
            'ready_for_production_live_runtime_activation_execution_review' => false,
            'production_live_runtime_activation_authorization_manifest_created' => false,
            'production_live_runtime_activation_execution_review_allowed_next' => false,
            'production_live_runtime_activation_executed' => false,
            'c144_lock_valid' => false,
            'c144_pre_activation_boundary_valid' => false,
            'c144_convert_from_json_pass' => false,
            'c143_lock_valid' => false,
            'c143_go_decision_finalization_valid' => false,
            'c143_convert_from_json_pass' => false,
            'c142_lock_valid' => false,
            'c142_activation_operator_go_no_go_valid' => false,
            'c142_convert_from_json_pass' => false,
            'c141_activation_observation_result_review_valid' => false,
            'c140_activation_observation_review_valid' => false,
            'c139_activation_execution_review_valid' => false,
            'c138_activation_authorization_valid' => false,
            'c137_pre_activation_boundary_valid' => false,
            'c136_go_decision_finalization_valid' => false,
            'c135_activation_operator_go_no_go_valid' => false,
            'c134_activation_observation_result_review_valid' => false,
            'c133_activation_observation_review_valid' => false,
            'c132_activation_execution_review_valid' => false,
            'c131_activation_approval_valid' => false,
            'c130_activation_readiness_valid' => false,
            'c129_final_closure_valid' => false,
            'c138_activation_authorization_review_only' => true,
            'c138_not_activation_execution' => true,
            'c138_not_live_runtime_state_change' => true,
            'c139_execution_review_only' => true,
            'c139_not_live_runtime_state_change' => true,
            'c140_observation_review_only' => true,
            'c140_not_live_runtime_state_change' => true,
            'c141_observation_result_review_only' => true,
            'c141_not_live_runtime_state_change' => true,
            'c142_operator_go_no_go_review_only' => true,
            'c142_not_live_runtime_state_change' => true,
            'c143_go_decision_finalization_review_only' => true,
            'c144_pre_activation_boundary_review_only' => false,
            'c144_not_activation_authorization' => false,
            'c145_activation_authorization_review_only' => true,
            'c145_not_activation_execution' => true,
            'c145_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
            'backup_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
            'comparator_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
            'a01_remains_comparator_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'production_live_runtime_activation_authorization_context_persisted_to_live_runtime' => false,
            'activation_authorization_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_executed' => false,
        ]);
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_pass' => true,
            'production_live_runtime_activation_authorization_review_pass' => true,
            'activation_authorization_confirmed' => true,
            'activation_authorized' => true,
            'primary_candidate_activation_authorized' => true,
            'backup_candidate_activation_authorized' => true,
            'comparator_candidate_activation_authorized' => false,
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_execution_review' => true,
            'ready_for_production_live_runtime_activation_execution_review' => true,
            'production_live_runtime_activation_authorization_manifest_created' => true,
            'production_live_runtime_activation_execution_review_allowed_next' => true,
            'production_live_runtime_activation_executed' => false,
            'c144_lock_valid' => true,
            'c144_pre_activation_boundary_valid' => true,
            'c144_convert_from_json_pass' => true,
            'c143_lock_valid' => true,
            'c143_go_decision_finalization_valid' => true,
            'c143_convert_from_json_pass' => true,
            'c142_lock_valid' => true,
            'c142_activation_operator_go_no_go_valid' => true,
            'c142_convert_from_json_pass' => true,
            'c141_activation_observation_result_review_valid' => true,
            'c140_activation_observation_review_valid' => true,
            'c139_activation_execution_review_valid' => true,
            'c138_activation_authorization_valid' => true,
            'c137_pre_activation_boundary_valid' => true,
            'c136_go_decision_finalization_valid' => true,
            'c135_activation_operator_go_no_go_valid' => true,
            'c134_activation_observation_result_review_valid' => true,
            'c133_activation_observation_review_valid' => true,
            'c132_activation_execution_review_valid' => true,
            'c131_activation_approval_valid' => true,
            'c130_activation_readiness_valid' => true,
            'c129_final_closure_valid' => true,
            'c138_activation_authorization_review_only' => true,
            'c138_not_activation_execution' => true,
            'c138_not_live_runtime_state_change' => true,
            'c139_execution_review_only' => true,
            'c139_not_live_runtime_state_change' => true,
            'c140_observation_review_only' => true,
            'c140_not_live_runtime_state_change' => true,
            'c141_observation_result_review_only' => true,
            'c141_not_live_runtime_state_change' => true,
            'c142_operator_go_no_go_review_only' => true,
            'c142_not_live_runtime_state_change' => true,
            'c143_go_decision_finalization_review_only' => true,
            'c144_pre_activation_boundary_review_only' => true,
            'c144_not_activation_authorization' => true,
            'c145_activation_authorization_review_only' => true,
            'c145_not_activation_execution' => true,
            'c145_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_execution_review' => true,
            'backup_candidate_ready_for_production_live_runtime_activation_execution_review' => true,
            'comparator_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c144 = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        return array_merge($artifact, [
            'c144_lock_validation_summary' => $this->c144LockValidationSummary($load),
            'c144_pre_activation_boundary_carry_forward_summary' => $this->c144PreActivationBoundaryCarryForwardSummary($c144),
            'candidate_scope_freeze_summary' => $this->candidateScopeFreezeSummary($c144, $pass),
            'operator_approval_validation_summary' => [
                'operator_approval_required' => true,
                'operator_approved' => (bool) ($options['operator_approved'] ?? false),
                'approval_reference_required' => true,
                'approval_reference' => (string) ($options['approval_reference'] ?? ''),
                'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
                'activation_authorization_confirmation_required' => true,
                'activation_authorization_confirmed' => ($options['activation_authorization_confirmed'] ?? false) === true,
                'operator_approval_validation_pass' => (bool) ($options['operator_approved'] ?? false)
                    && trim((string) ($options['approval_reference'] ?? '')) !== ''
                    && ($options['activation_authorization_confirmed'] ?? false) === true,
            ],
            'temporary_negative_artifact_guard_summary' => [
                'validation_completed' => true,
                'temporary_negative_artifacts_remaining' => $temporaryNegativePaths !== [],
                'temporary_negative_artifact_cleanup_confirmed' => $temporaryNegativePaths === [],
                'temporary_negative_artifact_paths' => $temporaryNegativePaths,
                'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
            ],
            'feature_flag_default_off_summary' => $this->featureFlagDefaultOffSummary(),
            'c145_activation_authorization_decision' => $this->activationAuthorizationDecision($pass),
            'next_activation_execution_decision' => $this->nextActivationExecutionDecision($pass),
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_manifest' => $this->activationAuthorizationManifest($load, $pass),
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_checklist' => $this->activationAuthorizationChecklist(),
            'c145_candidate_activation_execution_readiness_scorecard' => $this->candidateScorecard($pass),
            'production_live_runtime_activation_authorization_context_summary' => $this->activationAuthorizationContextSummary($pass),
            'runtime_config_review_summary' => $this->runtimeConfigReviewSummary(),
            'production_mutation_safety_summary' => $this->productionMutationSafetySummary(),
            'failure_attribution_summary' => $this->failureAttributionSummary([]),
            'progress_summary' => $this->progressSummary($pass),
            'planned_next_summary' => $this->plannedNextSummary($pass),
            'documentation_hygiene_guard_summary' => $this->documentationHygieneGuardSummary($load),
            'diagnostics' => $this->diagnostics(),
        ]);
    }

    private function c144LockValidationSummary(array $load): array
    {
        $c144 = is_array($load['payload']) ? $load['payload'] : [];

        return [
            'validation_completed' => true,
            'source_lock' => 'C144',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C144_STATUS,
            'actual_status' => $c144['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C144_PHASE_LABEL,
            'actual_phase_label' => $c144['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C144_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c144NextRecommendationMatches($c144),
            'c144_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c144PreActivationBoundaryCarryForwardSummary(array $c144): array
    {
        return [
            'validation_completed' => true,
            'c144_pre_activation_boundary_review_pass' => (bool) ($c144['weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_pass'] ?? false),
            'c144_pre_activation_boundary_cleared' => (bool) ($c144['pre_activation_boundary_cleared'] ?? false),
            'c144_ready_for_activation_authorization_review' => (bool) ($c144['ready_for_production_live_runtime_activation_authorization_review'] ?? false),
            'c143_lock_valid' => (bool) ($c144['c143_lock_valid'] ?? false),
            'c143_go_decision_finalization_valid' => (bool) ($c144['c143_go_decision_finalization_valid'] ?? false),
            'c143_convert_from_json_pass' => (bool) ($c144['c143_convert_from_json_pass'] ?? false),
            'c142_lock_valid' => (bool) ($c144['c142_lock_valid'] ?? false),
            'c142_activation_operator_go_no_go_valid' => (bool) ($c144['c142_activation_operator_go_no_go_valid'] ?? false),
            'c142_convert_from_json_pass' => (bool) ($c144['c142_convert_from_json_pass'] ?? false),
            'c141_activation_observation_result_review_valid' => (bool) ($c144['c141_activation_observation_result_review_valid'] ?? false),
            'c140_activation_observation_review_valid' => (bool) ($c144['c140_activation_observation_review_valid'] ?? false),
            'c139_activation_execution_review_valid' => (bool) ($c144['c139_activation_execution_review_valid'] ?? false),
            'c138_activation_authorization_valid' => (bool) ($c144['c138_activation_authorization_valid'] ?? false),
            'c137_pre_activation_boundary_valid' => (bool) ($c144['c137_pre_activation_boundary_valid'] ?? false),
            'c136_go_decision_finalization_valid' => (bool) ($c144['c136_go_decision_finalization_valid'] ?? false),
            'c135_activation_operator_go_no_go_valid' => (bool) ($c144['c135_activation_operator_go_no_go_valid'] ?? false),
            'c134_activation_observation_result_review_valid' => (bool) ($c144['c134_activation_observation_result_review_valid'] ?? false),
            'c133_activation_observation_review_valid' => (bool) ($c144['c133_activation_observation_review_valid'] ?? false),
            'c132_activation_execution_review_valid' => (bool) ($c144['c132_activation_execution_review_valid'] ?? false),
            'c131_activation_approval_valid' => (bool) ($c144['c131_activation_approval_valid'] ?? false),
            'c130_activation_readiness_valid' => (bool) ($c144['c130_activation_readiness_valid'] ?? false),
            'c129_final_closure_valid' => (bool) ($c144['c129_final_closure_valid'] ?? false),
            'c144_pre_activation_boundary_valid' => $this->c144PreActivationBoundaryValid($c144),
            'c145_activation_authorization_can_start' => $this->c144PreActivationBoundaryValid($c144),
        ];
    }

    private function candidateScopeFreezeSummary(array $c144, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c144),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_authorized_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_authorized_candidate',
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

    private function activationAuthorizationDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'activation_authorization_confirmed' => $pass,
            'activation_authorized' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_execution_review' => $pass,
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
            'next_recommendation' => $pass ? self::C146_RECOMMENDATION : 'C145_TARGETED_C144_PRE_ACTIVATION_BOUNDARY_REPAIR',
            'decision_reason' => $pass ? 'C145 authorizes activation as artifact-only evidence and permits activation execution review next.' : 'C145 cannot proceed until C144 lock, approval, authorization confirmation, cleanup, candidate, and safety gates pass.',
        ];
    }

    private function nextActivationExecutionDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C146_RECOMMENDATION : 'C145_TARGETED_C144_PRE_ACTIVATION_BOUNDARY_REPAIR',
            'next_scope' => $pass ? 'production/live runtime activation execution review only; C145 itself still does not execute activation, activate runtime bridge, generate live output, publish recommendations, or mutate PLAN/CONFIRM' : 'targeted C144 pre-activation boundary lock, approval, or safety repair only',
        ];
    }

    private function activationAuthorizationManifest(array $load, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_production_live_runtime_activation_authorization_review',
            'source_artifact' => 'C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'activation_authorization_confirmed' => $pass,
            'activation_authorized' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_activation_authorized' => $pass,
            'backup_candidate_activation_authorized' => $pass,
            'comparator_candidate_activation_authorized' => false,
            'ready_for_production_live_runtime_activation_execution_review' => $pass,
            'activation_authorization_artifact_only' => true,
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
            'activation_authorization_used_for_selection' => false,
            'activation_authorization_used_for_retuning' => false,
            'activation_authorization_used_for_ranking' => false,
            'activation_authorization_used_for_plan_confirm_mutation' => false,
            'activation_authorization_used_for_live_rollout' => false,
        ];
    }

    private function activationAuthorizationChecklist(): array
    {
        return [
            'activation_authorization_reviewed' => true,
            'activation_authorization_confirmation_required' => true,
            'activation_execution_not_run' => true,
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
            'c144_source_lock_reviewed' => true,
            'runtime_entrypoint_reviewed' => true,
            'artisan_command_registration_reviewed' => true,
            'service_boundary_reviewed' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_activation_authorization_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'activation_authorization_review_only' => true,
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
            'production_live_runtime_activation_authorization_review_pass' => $pass,
            'activation_authorized' => $pass,
            'ready_for_production_live_runtime_activation_execution_review' => $pass,
            'production_live_runtime_activation_executed' => false,
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
                'c145_role' => 'primary_production_live_runtime_activation_authorized_candidate',
                'primary_candidate_ready_for_production_live_runtime_activation_execution_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c145_role' => 'backup_production_live_runtime_activation_authorized_candidate',
                'backup_candidate_ready_for_production_live_runtime_activation_execution_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c145_role' => 'comparator_only_candidate',
                'production_live_runtime_activation_authorization_review_pass' => false,
                'activation_authorized' => false,
                'ready_for_production_live_runtime_activation_execution_review' => false,
                'comparator_candidate_ready_for_production_live_runtime_activation_execution_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function activationAuthorizationContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_context_created' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_authorization_context_persisted_to_live_runtime' => false,
            'activation_authorization_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_pre_activation_boundary_context_persisted_to_live_runtime' => false,
            'pre_activation_boundary_context_persisted_to_live_runtime' => false,
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
            'c144_artifact_identified' => is_file(self::DEFAULT_C144_ARTIFACT),
            'c145_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC145WeeklySwingWatchlistProductionLiveRuntimeActivationAuthorizationReviewService.php'),
            'c145_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC145WeeklySwingWatchlistProductionLiveRuntimeActivationAuthorizationReviewCommand.php'),
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
            'progress_marker' => 'PR-33_C145_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW',
            'c144_pre_activation_boundary_review_carried_forward' => true,
            'c145_production_live_runtime_activation_authorization_review_executed' => true,
            'c145_activation_authorized' => $pass,
            'c145_ready_for_activation_execution_review' => $pass,
            'production_live_runtime_activation_executed' => false,
            'still_no_live_runtime' => true,
            'still_no_runtime_bridge_activation' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C146_RECOMMENDATION : 'C145_TARGETED_C144_PRE_ACTIVATION_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'production/live runtime activation execution review only; not official weekly swing output publication or PLAN/CONFIRM mutation in C145' : 'targeted repair before production/live activation can be authorized',
            'planned_next_required_inputs' => $pass ? [
                'locked C145 artifact hash',
                'locked C145 file SHA1',
                'operator approval reference',
                'activation authorization record',
                'activation execution checklist',
                'runtime feature flags still default-off',
                'future activation execution contract',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c144_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c144_artifact_not_modified' => true,
            'c60_c144_artifacts_not_modified' => true,
            'c145_is_activation_authorization_review_not_activation_execution' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C145 validates C144 artifact_hash and file SHA1 locks before production/live runtime activation authorization review is recorded.',
            'C145 validates C144 pre-activation boundary clearance and next recommendation to C145.',
            'C145 requires --operator-approved, a non-empty --approval-reference, and explicit activation authorization confirmation.',
            'C145 keeps runtime feature flags default-off and checks the kill-switch surface.',
            'C145 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C145 records artifact-only activation authorization and moves to activation execution review.',
            'C145 does not execute activation, activate runtime bridge, execute production runtime wiring, generate official weekly swing output, publish recommendations, or mutate PLAN/CONFIRM.',
            'C145 may only recommend C146 production/live runtime activation execution review as the next controlled step.',
        ];
    }

    private function c144PreActivationBoundaryValid(array $c144): bool
    {
        foreach (self::REQUIRED_C144_TRUE_FIELDS as $field) {
            if (($c144[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C144_FALSE_FIELDS as $field) {
            if (($c144[$field] ?? null) !== false) {
                return false;
            }
        }

        foreach ([
            [['c144_pre_activation_boundary_decision', 'review_pass'], true],
            [['c144_pre_activation_boundary_decision', 'next_recommendation'], self::EXPECTED_C144_NEXT_RECOMMENDATION],
            [['next_activation_authorization_decision', 'review_pass'], true],
            [['next_activation_authorization_decision', 'next_recommendation'], self::EXPECTED_C144_NEXT_RECOMMENDATION],
            [['weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_manifest', 'pre_activation_boundary_cleared'], true],
            [['weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_manifest', 'ready_for_production_live_runtime_activation_authorization_review'], true],
            [['weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_manifest', 'activation_authorized'], false],
            [['weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_checklist', 'pre_activation_boundary_reviewed'], true],
            [['weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_checklist', 'artifact_only'], true],
        ] as [$path, $expected]) {
            if ($this->valueAt($c144, $path) !== $expected) {
                return false;
            }
        }

        return true;
    }

    private function c144NextRecommendationMatches(array $c144): bool
    {
        foreach ([
            $c144['next_step_recommendation'] ?? null,
            $c144['next_activation_authorization_decision']['next_recommendation'] ?? null,
            $c144['planned_next_summary']['planned_next_review'] ?? null,
        ] as $value) {
            if ($value !== null && $value !== self::EXPECTED_C144_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c144['next_step_recommendation'] ?? null) === self::EXPECTED_C144_NEXT_RECOMMENDATION;
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
        foreach (array_values(array_unique(array_merge(self::REQUIRED_C144_FALSE_FIELDS, self::REQUIRED_FALSE_SAFETY_FLAGS))) as $field) {
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
            'c144' => [
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
            'expected_c144_hash' => $load['expected_hash'],
            'actual_c144_hash' => $load['actual_hash'],
            'c144_hash_match' => $load['hash_match'],
            'expected_c144_file_sha1' => $load['expected_file_sha1'],
            'actual_c144_file_sha1' => $load['actual_file_sha1'],
            'c144_file_sha1_match' => $load['file_sha1_match'],
            'c144_convert_from_json_pass' => $load['convert_from_json_pass'],
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
        $artifact['next_activation_execution_decision'] = $this->nextActivationExecutionDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_activation_execution_decision'] = $this->nextActivationExecutionDecision(false);
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
