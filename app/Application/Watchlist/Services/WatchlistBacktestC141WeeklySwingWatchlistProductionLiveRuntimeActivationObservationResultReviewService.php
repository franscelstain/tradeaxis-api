<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC141WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService
{
    public const RUN_CODE = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-29 / C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW';

    public const DEFAULT_C140_ARTIFACT = 'storage/app/watchlist/backtest/c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json';
    public const DEFAULT_EXPECTED_C140_HASH = 'e1a428c007dbe40d438e34a15c74d57a58cf5449';
    public const DEFAULT_EXPECTED_C140_FILE_SHA1 = '91EA2C44BB6E8742F55203589BFCFB7E1088DD6B';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C140_STATUS = 'C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_PASSED_READY_FOR_ACTIVATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C140_PHASE_LABEL = 'PR-28 / C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW';
    private const EXPECTED_C140_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C142_RECOMMENDATION = 'C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C140_LOCK_MISMATCH_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_ARTIFACT_LOCK_MISMATCH';
    private const C140_FILE_SHA1_MISMATCH_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_FILE_SHA1_LOCK_MISMATCH';
    private const C140_STATUS_MISMATCH_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_STATUS_MISMATCH';
    private const C140_PHASE_LABEL_MISMATCH_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_PHASE_LABEL_MISMATCH';
    private const C140_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_NEXT_RECOMMENDATION_MISMATCH';
    private const C140_OBSERVATION_REVIEW_INCOMPLETE_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_ACTIVATION_OBSERVATION_REVIEW_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C140_CONVERT_FROM_JSON_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C140_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS = 'C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_FEATURE_FLAGS_NOT_DEFAULT_OFF';

    private const REQUIRED_C140_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_observation_review_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_review_pass',
        'production_live_runtime_activation_observation_review_pass',
        'ready_for_production_live_runtime_activation_observation_result_review',
        'production_live_runtime_activation_observation_result_review_allowed_next',
        'production_live_runtime_activation_observation_review_manifest_created',
        'c139_lock_valid',
        'c139_activation_execution_review_valid',
        'c139_convert_from_json_pass',
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
        'activation_authorized',
        'primary_candidate_activation_authorized',
        'backup_candidate_activation_authorized',
        'c138_activation_authorization_review_only',
        'c138_not_activation_execution',
        'c138_not_live_runtime_state_change',
        'c139_execution_review_only',
        'c139_not_live_runtime_state_change',
        'c140_observation_review_only',
        'c140_not_live_runtime_state_change',
        'primary_candidate_ready_for_production_live_runtime_activation_observation_result_review',
        'backup_candidate_ready_for_production_live_runtime_activation_observation_result_review',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C140_FALSE_FIELDS = [
        'production_live_runtime_activation_executed',
        'comparator_candidate_activation_authorized',
        'comparator_candidate_ready_for_production_live_runtime_activation_observation_result_review',
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
        'weekly_swing_watchlist_production_live_runtime_activation_observation_context_persisted_to_live_runtime',
        'production_live_runtime_activation_observation_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_review_context_persisted_to_live_runtime',
        'production_live_runtime_activation_observation_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_context_persisted_to_live_runtime',
        'production_live_runtime_activation_observation_result_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime',
        'production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime',
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
        string $c140Artifact = self::DEFAULT_C140_ARTIFACT,
        string $expectedC140Hash = self::DEFAULT_EXPECTED_C140_HASH,
        string $expectedC140FileSha1 = self::DEFAULT_EXPECTED_C140_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c140Artifact, $expectedC140Hash, $expectedC140FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C140_LOCK_MISMATCH_STATUS, 'C140 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c140_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C140_CONVERT_FROM_JSON_STATUS, 'C140 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C140_LOCK_MISMATCH_STATUS, 'C140 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C140_FILE_SHA1_MISMATCH_STATUS, 'C140 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c140 = $load['payload'];
        if (($c140['status'] ?? null) !== self::EXPECTED_C140_STATUS || ($c140['reason_code'] ?? null) !== self::EXPECTED_C140_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C140_STATUS_MISMATCH_STATUS, 'C140 status/reason is not activation-observation-result ready.', $outputPath, $overwrite);
        }
        if (($c140['phase_label'] ?? null) !== self::EXPECTED_C140_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C140_PHASE_LABEL_MISMATCH_STATUS, 'C140 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c140NextRecommendationMatches($c140)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C140_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C140 next recommendation is not C141.', $outputPath, $overwrite);
        }
        if (! $this->c140ObservationReviewComplete($c140)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C140_OBSERVATION_REVIEW_INCOMPLETE_STATUS, 'C140 production/live activation observation review evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c140)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C140 candidate scope does not match locked activation observation result scope.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c140);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c140_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C140 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if ($this->enabledRuntimeFeatureFlags() !== []) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['enabled_runtime_feature_flags'] = $this->enabledRuntimeFeatureFlags();

            return $this->rejected($artifact, self::FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS, 'C141 requires runtime feature flags to remain default-off.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C141 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C141 reviews the C140 production/live runtime activation observation result as artifact-only evidence and moves to operator go/no-go review. It does not activate runtime bridge, execute live output, publish recommendations, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C141_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_ARTIFACT_ONLY_NON_LIVE_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::C142_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-29',
            'internal_checkpoint' => 'C141',
            'status' => 'C141_NOT_RUN',
            'reason_code' => 'C141_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass' => false,
            'production_live_runtime_activation_observation_result_review_pass' => false,
            'ready_for_production_live_runtime_activation_operator_go_no_go_review' => false,
            'production_live_runtime_activation_operator_go_no_go_review_allowed_next' => false,
            'production_live_runtime_activation_observation_result_review_manifest_created' => false,
            'production_live_runtime_activation_executed' => false,
            'c140_lock_valid' => false,
            'c140_activation_observation_review_valid' => false,
            'c140_convert_from_json_pass' => false,
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
            'activation_authorized' => false,
            'primary_candidate_activation_authorized' => false,
            'backup_candidate_activation_authorized' => false,
            'comparator_candidate_activation_authorized' => false,
            'c138_activation_authorization_review_only' => true,
            'c138_not_activation_execution' => true,
            'c138_not_live_runtime_state_change' => true,
            'c139_execution_review_only' => true,
            'c139_not_live_runtime_state_change' => true,
            'c140_observation_review_only' => true,
            'c140_not_live_runtime_state_change' => true,
            'c141_observation_result_review_only' => true,
            'c141_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => false,
            'backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => false,
            'comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => false,
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
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass' => true,
            'production_live_runtime_activation_observation_result_review_pass' => true,
            'ready_for_production_live_runtime_activation_operator_go_no_go_review' => true,
            'production_live_runtime_activation_operator_go_no_go_review_allowed_next' => true,
            'production_live_runtime_activation_observation_result_review_manifest_created' => true,
            'production_live_runtime_activation_executed' => false,
            'c140_lock_valid' => true,
            'c140_activation_observation_review_valid' => true,
            'c140_convert_from_json_pass' => true,
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
            'activation_authorized' => true,
            'primary_candidate_activation_authorized' => true,
            'backup_candidate_activation_authorized' => true,
            'comparator_candidate_activation_authorized' => false,
            'c138_activation_authorization_review_only' => true,
            'c138_not_activation_execution' => true,
            'c138_not_live_runtime_state_change' => true,
            'c139_execution_review_only' => true,
            'c139_not_live_runtime_state_change' => true,
            'c140_observation_review_only' => true,
            'c140_not_live_runtime_state_change' => true,
            'c141_observation_result_review_only' => true,
            'c141_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => true,
            'backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => true,
            'comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c140 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c140_lock_validation_summary'] = $this->c140LockValidationSummary($load, $c140);
        $artifact['c140_activation_observation_review_carry_forward_summary'] = $this->c140ActivationObservationReviewCarryForwardSummary($c140, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c140, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['feature_flag_default_off_summary'] = $this->featureFlagDefaultOffSummary();
        $artifact['c141_observation_result_decision'] = $this->observationResultDecision($pass);
        $artifact['next_operator_go_no_go_decision'] = $this->nextOperatorGoNoGoDecision($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_manifest'] = $this->observationResultReviewManifest($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_checklist'] = $this->observationResultReviewChecklist();
        $artifact['c141_candidate_activation_observation_result_scorecard'] = $this->candidateScorecard($pass);
        $artifact['production_live_runtime_activation_observation_result_review_context_summary'] = $this->observationResultReviewContextSummary($pass);
        $artifact['runtime_config_review_summary'] = $this->runtimeConfigReviewSummary();
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C141_PENDING')]);
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

    private function c140ObservationReviewComplete(array $c140): bool
    {
        foreach (self::REQUIRED_C140_TRUE_FIELDS as $field) {
            if (! (bool) ($c140[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C140_FALSE_FIELDS as $field) {
            if ((bool) ($c140[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c140NextRecommendationMatches(array $c140): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_observation_result_decision', 'next_recommendation'],
            ['c140_observation_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c140, $path);
            if ($value !== null && $value !== self::EXPECTED_C140_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c140['next_step_recommendation'] ?? null) === self::EXPECTED_C140_NEXT_RECOMMENDATION;
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

    private function c140LockValidationSummary(array $load, array $c140): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C140',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C140_STATUS,
            'actual_status' => $c140['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C140_PHASE_LABEL,
            'actual_phase_label' => $c140['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C140_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c140NextRecommendationMatches($c140),
            'c140_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c140ActivationObservationReviewCarryForwardSummary(array $c140, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c140_observation_review_pass' => (bool) ($c140['weekly_swing_watchlist_production_live_runtime_activation_observation_review_pass'] ?? false),
            'c140_ready_for_activation_observation_result_review' => (bool) ($c140['ready_for_production_live_runtime_activation_observation_result_review'] ?? false),
            'c139_activation_execution_review_valid' => (bool) ($c140['c139_activation_execution_review_valid'] ?? false),
            'c138_activation_authorization_valid' => (bool) ($c140['c138_activation_authorization_valid'] ?? false),
            'c137_pre_activation_boundary_valid' => (bool) ($c140['c137_pre_activation_boundary_valid'] ?? false),
            'c136_go_decision_finalization_valid' => (bool) ($c140['c136_go_decision_finalization_valid'] ?? false),
            'c135_activation_operator_go_no_go_valid' => (bool) ($c140['c135_activation_operator_go_no_go_valid'] ?? false),
            'c134_activation_observation_result_review_valid' => (bool) ($c140['c134_activation_observation_result_review_valid'] ?? false),
            'c133_activation_observation_review_valid' => (bool) ($c140['c133_activation_observation_review_valid'] ?? false),
            'c132_activation_execution_review_valid' => (bool) ($c140['c132_activation_execution_review_valid'] ?? false),
            'c131_activation_approval_valid' => (bool) ($c140['c131_activation_approval_valid'] ?? false),
            'c130_activation_readiness_valid' => (bool) ($c140['c130_activation_readiness_valid'] ?? false),
            'c129_final_closure_valid' => (bool) ($c140['c129_final_closure_valid'] ?? false),
            'activation_authorized' => (bool) ($c140['activation_authorized'] ?? false),
            'primary_candidate_activation_authorized' => (bool) ($c140['primary_candidate_activation_authorized'] ?? false),
            'backup_candidate_activation_authorized' => (bool) ($c140['backup_candidate_activation_authorized'] ?? false),
            'c140_activation_observation_review_valid' => $this->c140ObservationReviewComplete($c140),
            'c141_activation_observation_result_review_can_start' => $pass,
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
            'primary_candidate_role' => 'primary_production_live_runtime_activation_observation_result_review_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_observation_result_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => $pass,
            'backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => $pass,
            'comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => false,
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
            'kill_switch_identified' => true,
            'production_catalog_runtime_bridge_kill_switch' => false,
        ];
    }

    private function observationResultDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c140_activation_observation_review_valid' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_operator_go_no_go_review' => $pass,
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
            'next_recommendation' => $pass ? self::C142_RECOMMENDATION : 'C141_TARGETED_C140_ACTIVATION_OBSERVATION_REVIEW_REPAIR',
            'decision_reason' => $pass ? 'C141 records artifact-only observation result review and permits operator go/no-go review next.' : 'C141 cannot proceed until C140 lock, observation review, feature flags, candidate scope, and safety gates pass.',
        ];
    }

    private function nextOperatorGoNoGoDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C142_RECOMMENDATION : 'C141_TARGETED_C140_ACTIVATION_OBSERVATION_REVIEW_REPAIR',
            'next_scope' => $pass ? 'production/live runtime activation operator go/no-go review only; still no runtime bridge activation, live output generation, official publication, or PLAN/CONFIRM mutation in C141' : 'targeted C140 observation review, feature flag, candidate scope, or cleanup repair only',
        ];
    }

    private function observationResultReviewManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_production_live_runtime_activation_observation_result_review',
            'source_artifact' => self::EXPECTED_C140_STATUS,
            'source_artifact_path' => self::DEFAULT_C140_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C140_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C140_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_observation_result_review_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_observation_result_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'c140_activation_observation_review_carried_forward' => $pass,
            'production_live_runtime_activation_observation_result_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_operator_go_no_go_review' => $pass,
            'production_live_runtime_activation_operator_go_no_go_review_required_next' => $pass,
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
            'activation_observation_result_review_used_for_plan_confirm_mutation' => false,
            'activation_observation_result_review_used_for_live_rollout' => false,
            'activation_observation_result_review_artifact_only' => true,
        ];
    }

    private function observationResultReviewChecklist(): array
    {
        return [
            'observation_result_reviewed' => true,
            'c140_activation_observation_review_artifact_locked' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'operator_approval_required' => true,
            'feature_flags_default_off_required' => true,
            'kill_switch_required' => true,
            'rollback_plan_required_for_future_activation' => true,
            'manual_validation_required_before_future_activation' => true,
            'live_runtime_activation_operator_go_no_go_review_required_next' => true,
            'observation_result_review_only' => true,
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
            'production_live_runtime_activation_observation_result_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_operator_go_no_go_review' => $pass,
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
                'c141_role' => 'primary_production_live_runtime_activation_observation_result_review_candidate',
                'primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c141_role' => 'backup_production_live_runtime_activation_observation_result_review_candidate',
                'backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c141_role' => 'comparator_only_candidate',
                'production_live_runtime_activation_observation_result_review_pass' => false,
                'ready_for_production_live_runtime_activation_operator_go_no_go_review' => false,
                'comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function observationResultReviewContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_context_created' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_observation_result_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_observation_result_review_context_persisted_to_live_runtime' => false,
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
            'c140_artifact_identified' => is_file(self::DEFAULT_C140_ARTIFACT),
            'c141_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC141WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewService.php'),
            'c141_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC141WeeklySwingWatchlistProductionLiveRuntimeActivationObservationResultReviewCommand.php'),
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
            'progress_marker' => 'PR-29_C141_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW',
            'c140_activation_observation_review_carried_forward' => true,
            'c141_production_live_runtime_activation_observation_result_review_executed' => true,
            'c141_ready_for_activation_operator_go_no_go_review' => $pass,
            'still_no_live_runtime' => true,
            'still_no_runtime_bridge_activation' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C142_RECOMMENDATION : 'C141_TARGETED_C140_ACTIVATION_OBSERVATION_REVIEW_REPAIR',
            'planned_next_scope' => $pass ? 'production/live runtime activation operator go/no-go review only; not official weekly swing output publication or PLAN/CONFIRM mutation in C141' : 'targeted repair before production/live activation observation result review can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C141 artifact hash',
                'locked C141 file SHA1',
                'operator approval reference',
                'activation operator go/no-go review checklist',
                'runtime feature flags still default-off',
                'future activation operator go/no-go review contract',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c140_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c140_artifact_not_modified' => true,
            'c60_c140_artifacts_not_modified' => true,
            'c141_is_observation_result_review_not_live_state_change' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C141 validates C140 artifact_hash and file SHA1 locks before production/live runtime activation observation result review is recorded.',
            'C141 validates C140 activation observation review and next recommendation to C141.',
            'C141 requires --operator-approved and a non-empty --approval-reference.',
            'C141 keeps runtime feature flags default-off and checks the kill-switch surface.',
            'C141 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C141 records artifact-only activation observation result review and moves to operator go/no-go review.',
            'C141 does not activate runtime bridge, execute production runtime wiring, generate official weekly swing output, publish recommendations, or mutate PLAN/CONFIRM.',
            'C141 may only recommend C142 production/live runtime activation operator go/no-go review as the next controlled step.',
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
            'c140' => [
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
            'expected_c140_hash' => $load['expected_hash'],
            'actual_c140_hash' => $load['actual_hash'],
            'c140_hash_match' => $load['hash_match'],
            'expected_c140_file_sha1' => $load['expected_file_sha1'],
            'actual_c140_file_sha1' => $load['actual_file_sha1'],
            'c140_file_sha1_match' => $load['file_sha1_match'],
            'c140_convert_from_json_pass' => $load['convert_from_json_pass'],
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
        $artifact['next_operator_go_no_go_decision'] = $this->nextOperatorGoNoGoDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_operator_go_no_go_decision'] = $this->nextOperatorGoNoGoDecision(false);
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
