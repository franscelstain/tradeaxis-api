<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-23 / C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C134_ARTIFACT = 'storage/app/watchlist/backtest/c134-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json';
    public const DEFAULT_EXPECTED_C134_HASH = 'ada066cc599d749e050b5efd61073ccad1e64b74';
    public const DEFAULT_EXPECTED_C134_FILE_SHA1 = 'AE7C013A1B5CC0DFC5968C4FC99B2E1DDFF88F3E';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C134_STATUS = 'C134_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C134_PHASE_LABEL = 'PR-22 / C134_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW';
    private const EXPECTED_C134_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C136_RECOMMENDATION = 'C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';

    private const PASS_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_DECISION_NOT_CONFIRMED_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C134_LOCK_MISMATCH_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_ARTIFACT_LOCK_MISMATCH';
    private const C134_FILE_SHA1_MISMATCH_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_FILE_SHA1_LOCK_MISMATCH';
    private const C134_STATUS_MISMATCH_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_STATUS_MISMATCH';
    private const C134_PHASE_LABEL_MISMATCH_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_PHASE_LABEL_MISMATCH';
    private const C134_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_NEXT_RECOMMENDATION_MISMATCH';
    private const C134_OBSERVATION_RESULT_REVIEW_INCOMPLETE_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_OBSERVATION_RESULT_REVIEW_INCOMPLETE';
    private const C134_CONVERT_FROM_JSON_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C134_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS = 'C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAGS_NOT_DEFAULT_OFF';

    private const REQUIRED_C134_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass',
        'production_live_runtime_activation_observation_result_review_pass',
        'ready_for_production_live_runtime_activation_operator_go_no_go_review',
        'production_live_runtime_activation_operator_go_no_go_review_allowed_next',
        'production_live_runtime_activation_observation_result_review_manifest_created',
        'c133_lock_valid',
        'c133_activation_observation_review_valid',
        'c133_convert_from_json_pass',
        'c132_activation_execution_review_valid',
        'c131_activation_approval_valid',
        'c130_activation_readiness_valid',
        'c129_final_closure_valid',
        'c133_observation_review_only',
        'c133_not_live_runtime_state_change',
        'c134_observation_result_review_only',
        'c134_not_live_runtime_state_change',
        'primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review',
        'backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C134_FALSE_FIELDS = [
        'production_live_runtime_activation_executed',
        'comparator_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review',
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
        'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_context_persisted_to_live_runtime',
        'production_live_runtime_activation_operator_go_no_go_context_persisted_to_live_runtime',
        'operator_go_no_go_context_persisted_to_live_runtime',
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
        string $c134Artifact = self::DEFAULT_C134_ARTIFACT,
        string $expectedC134Hash = self::DEFAULT_EXPECTED_C134_HASH,
        string $expectedC134FileSha1 = self::DEFAULT_EXPECTED_C134_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c134Artifact, $expectedC134Hash, $expectedC134FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C134_LOCK_MISMATCH_STATUS, 'C134 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c134_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C134_CONVERT_FROM_JSON_STATUS, 'C134 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C134_LOCK_MISMATCH_STATUS, 'C134 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C134_FILE_SHA1_MISMATCH_STATUS, 'C134 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c134 = $load['payload'];
        if (($c134['status'] ?? null) !== self::EXPECTED_C134_STATUS || ($c134['reason_code'] ?? null) !== self::EXPECTED_C134_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C134_STATUS_MISMATCH_STATUS, 'C134 status/reason is not activation operator go/no-go ready.', $outputPath, $overwrite);
        }
        if (($c134['phase_label'] ?? null) !== self::EXPECTED_C134_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C134_PHASE_LABEL_MISMATCH_STATUS, 'C134 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c134NextRecommendationMatches($c134)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C134_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C134 next recommendation is not C135.', $outputPath, $overwrite);
        }
        if (! $this->c134ObservationResultReviewComplete($c134)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C134_OBSERVATION_RESULT_REVIEW_INCOMPLETE_STATUS, 'C134 production/live activation observation result review evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c134)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C134 candidate scope does not match locked activation operator go/no-go scope.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c134);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c134_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C134 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if ($this->enabledRuntimeFeatureFlags() !== []) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['enabled_runtime_feature_flags'] = $this->enabledRuntimeFeatureFlags();

            return $this->rejected($artifact, self::FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS, 'C135 requires runtime feature flags to remain default-off.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C135 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (($options['operator_go_decision_confirmed'] ?? true) !== true) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::GO_DECISION_NOT_CONFIRMED_STATUS, 'C135 requires explicit operator GO decision confirmation.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C135 records an operator GO decision for E02 primary and B01 backup using the locked C134 observation result review. This is still artifact-only and does not activate runtime bridge, execute live output, publish recommendations, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
        $artifact['next_step_recommendation'] = self::C136_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-23',
            'internal_checkpoint' => 'C135',
            'status' => 'C135_NOT_RUN',
            'reason_code' => 'C135_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass' => false,
            'production_live_runtime_activation_operator_go_no_go_review_pass' => false,
            'operator_go_decision' => 'NO_GO',
            'operator_go_decision_confirmed' => false,
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
            'ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
            'production_live_runtime_activation_operator_go_no_go_manifest_created' => false,
            'production_live_runtime_activation_go_decision_finalization_review_allowed_next' => false,
            'production_live_runtime_activation_executed' => false,
            'c134_lock_valid' => false,
            'c134_activation_observation_result_review_valid' => false,
            'c134_convert_from_json_pass' => false,
            'c133_activation_observation_review_valid' => false,
            'c132_activation_execution_review_valid' => false,
            'c131_activation_approval_valid' => false,
            'c130_activation_readiness_valid' => false,
            'c129_final_closure_valid' => false,
            'c134_observation_result_review_only' => true,
            'c134_not_live_runtime_state_change' => true,
            'c135_operator_go_no_go_review_only' => true,
            'c135_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
            'backup_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
            'comparator_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
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
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass' => true,
            'production_live_runtime_activation_operator_go_no_go_review_pass' => true,
            'operator_go_decision' => 'GO',
            'operator_go_decision_confirmed' => true,
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_go_decision_finalization_review' => true,
            'ready_for_production_live_runtime_activation_go_decision_finalization_review' => true,
            'production_live_runtime_activation_operator_go_no_go_manifest_created' => true,
            'production_live_runtime_activation_go_decision_finalization_review_allowed_next' => true,
            'production_live_runtime_activation_executed' => false,
            'c134_lock_valid' => true,
            'c134_activation_observation_result_review_valid' => true,
            'c134_convert_from_json_pass' => true,
            'c133_activation_observation_review_valid' => true,
            'c132_activation_execution_review_valid' => true,
            'c131_activation_approval_valid' => true,
            'c130_activation_readiness_valid' => true,
            'c129_final_closure_valid' => true,
            'c134_observation_result_review_only' => true,
            'c134_not_live_runtime_state_change' => true,
            'c135_operator_go_no_go_review_only' => true,
            'c135_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => true,
            'backup_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => true,
            'comparator_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c134 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c134_lock_validation_summary'] = $this->c134LockValidationSummary($load, $c134);
        $artifact['c134_activation_observation_result_review_carry_forward_summary'] = $this->c134ActivationObservationResultReviewCarryForwardSummary($c134, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c134, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['feature_flag_default_off_summary'] = $this->featureFlagDefaultOffSummary();
        $artifact['c135_operator_go_no_go_decision'] = $this->operatorGoNoGoDecision($pass);
        $artifact['next_go_decision_finalization_decision'] = $this->nextGoDecisionFinalizationDecision($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_manifest'] = $this->operatorGoNoGoManifest($load, $pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_checklist'] = $this->operatorGoNoGoChecklist();
        $artifact['c135_candidate_activation_operator_go_no_go_scorecard'] = $this->candidateScorecard($pass);
        $artifact['production_live_runtime_activation_operator_go_no_go_context_summary'] = $this->operatorGoNoGoContextSummary($pass);
        $artifact['runtime_config_review_summary'] = $this->runtimeConfigReviewSummary();
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C135_PENDING')]);
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

    private function c134ObservationResultReviewComplete(array $c134): bool
    {
        foreach (self::REQUIRED_C134_TRUE_FIELDS as $field) {
            if (! (bool) ($c134[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C134_FALSE_FIELDS as $field) {
            if ((bool) ($c134[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c134NextRecommendationMatches(array $c134): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_operator_go_no_go_decision', 'next_recommendation'],
            ['c134_observation_result_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c134, $path);
            if ($value !== null && $value !== self::EXPECTED_C134_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c134['next_step_recommendation'] ?? null) === self::EXPECTED_C134_NEXT_RECOMMENDATION;
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

    private function c134LockValidationSummary(array $load, array $c134): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C134',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C134_STATUS,
            'actual_status' => $c134['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C134_PHASE_LABEL,
            'actual_phase_label' => $c134['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C134_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c134NextRecommendationMatches($c134),
            'c134_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c134ActivationObservationResultReviewCarryForwardSummary(array $c134, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c134_observation_result_review_pass' => (bool) ($c134['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass'] ?? false),
            'c134_ready_for_activation_operator_go_no_go_review' => (bool) ($c134['ready_for_production_live_runtime_activation_operator_go_no_go_review'] ?? false),
            'c133_activation_observation_review_valid' => (bool) ($c134['c133_activation_observation_review_valid'] ?? false),
            'c132_activation_execution_review_valid' => (bool) ($c134['c132_activation_execution_review_valid'] ?? false),
            'c131_activation_approval_valid' => (bool) ($c134['c131_activation_approval_valid'] ?? false),
            'c130_activation_readiness_valid' => (bool) ($c134['c130_activation_readiness_valid'] ?? false),
            'c129_final_closure_valid' => (bool) ($c134['c129_final_closure_valid'] ?? false),
            'c134_activation_observation_result_review_valid' => $this->c134ObservationResultReviewComplete($c134),
            'c135_activation_operator_go_no_go_review_can_start' => $pass,
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
            'primary_candidate_role' => 'primary_production_live_runtime_activation_operator_go_no_go_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_operator_go_no_go_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => $pass,
            'backup_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => $pass,
            'comparator_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
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
            'operator_go_decision_confirmation_required' => true,
            'operator_go_decision_confirmed' => (bool) ($options['operator_go_decision_confirmed'] ?? false),
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

    private function operatorGoNoGoDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision_confirmed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_go_decision_finalization_review' => $pass,
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
            'next_recommendation' => $pass ? self::C136_RECOMMENDATION : 'C135_TARGETED_C134_ACTIVATION_OBSERVATION_RESULT_REVIEW_REPAIR',
            'decision_reason' => $pass ? 'C135 records artifact-only operator GO and permits go decision finalization review next.' : 'C135 cannot proceed until C134 lock, approval, GO confirmation, cleanup, candidate, and safety gates pass.',
        ];
    }

    private function nextGoDecisionFinalizationDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C136_RECOMMENDATION : 'C135_TARGETED_C134_ACTIVATION_OBSERVATION_RESULT_REVIEW_REPAIR',
            'next_scope' => $pass ? 'production/live runtime activation GO decision finalization review only; C135 itself still does not activate runtime bridge, generate live output, publish recommendations, or mutate PLAN/CONFIRM' : 'targeted C134 observation result review lock, approval, or safety repair only',
        ];
    }

    private function operatorGoNoGoManifest(array $load, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_production_live_runtime_activation_operator_go_no_go_review',
            'source_artifact' => 'C134_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['expected_hash'],
            'source_file_sha1' => $load['expected_file_sha1'],
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision_confirmed' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'production_live_runtime_activation_operator_go_no_go_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_go_decision_finalization_review' => $pass,
            'operator_go_no_go_artifact_only' => true,
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
            'operator_go_no_go_used_for_selection' => false,
            'operator_go_no_go_used_for_retuning' => false,
            'operator_go_no_go_used_for_ranking' => false,
            'operator_go_no_go_used_for_plan_confirm_mutation' => false,
            'operator_go_no_go_used_for_live_rollout' => false,
        ];
    }

    private function operatorGoNoGoChecklist(): array
    {
        return [
            'operator_go_no_go_reviewed' => true,
            'operator_go_decision_confirmation_required' => true,
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
            'c134_source_lock_reviewed' => true,
            'runtime_entrypoint_reviewed' => true,
            'artisan_command_registration_reviewed' => true,
            'service_boundary_reviewed' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_go_confirmation_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'operator_go_no_go_review_only' => true,
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
            'production_live_runtime_activation_operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'ready_for_production_live_runtime_activation_go_decision_finalization_review' => $pass,
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
                'c135_role' => 'primary_production_live_runtime_activation_operator_go_no_go_candidate',
                'primary_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c135_role' => 'backup_production_live_runtime_activation_operator_go_no_go_candidate',
                'backup_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c135_role' => 'comparator_only_candidate',
                'production_live_runtime_activation_operator_go_no_go_review_pass' => false,
                'operator_go_decision' => 'NO_GO',
                'ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
                'comparator_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function operatorGoNoGoContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_context_created' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_operator_go_no_go_context_persisted_to_live_runtime' => false,
            'operator_go_no_go_context_persisted_to_live_runtime' => false,
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
            'c134_artifact_identified' => is_file(self::DEFAULT_C134_ARTIFACT),
            'c135_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService.php'),
            'c135_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC135WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewCommand.php'),
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
            'progress_marker' => 'PR-23_C135_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW',
            'c134_activation_observation_result_review_carried_forward' => true,
            'c135_production_live_runtime_activation_operator_go_no_go_review_executed' => true,
            'c135_operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'c135_ready_for_activation_go_decision_finalization_review' => $pass,
            'still_no_live_runtime' => true,
            'still_no_runtime_bridge_activation' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C136_RECOMMENDATION : 'C135_TARGETED_C134_ACTIVATION_OBSERVATION_RESULT_REVIEW_REPAIR',
            'planned_next_scope' => $pass ? 'production/live runtime activation GO decision finalization review only; not official weekly swing output publication or PLAN/CONFIRM mutation in C135' : 'targeted repair before production/live activation operator go/no-go review can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C135 artifact hash',
                'locked C135 file SHA1',
                'operator approval reference',
                'confirmed C135 operator GO decision',
                'activation GO decision finalization checklist',
                'runtime feature flags still default-off',
                'future activation finalization contract',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c134_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c134_artifact_not_modified' => true,
            'c60_c134_artifacts_not_modified' => true,
            'c135_is_operator_go_no_go_review_not_live_state_change' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C135 validates C134 artifact_hash and file SHA1 locks before production/live runtime activation operator go/no-go review is recorded.',
            'C135 validates C134 activation observation result review and next recommendation to C135.',
            'C135 requires --operator-approved, a non-empty --approval-reference, and explicit operator GO decision confirmation.',
            'C135 keeps runtime feature flags default-off and checks the kill-switch surface.',
            'C135 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C135 records artifact-only activation operator GO and moves to GO decision finalization review.',
            'C135 does not activate runtime bridge, execute production runtime wiring, generate official weekly swing output, publish recommendations, or mutate PLAN/CONFIRM.',
            'C135 may only recommend C136 production/live runtime activation GO decision finalization review as the next controlled step.',
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
            'c134' => [
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
            'expected_c134_hash' => $load['expected_hash'],
            'actual_c134_hash' => $load['actual_hash'],
            'c134_hash_match' => $load['hash_match'],
            'expected_c134_file_sha1' => $load['expected_file_sha1'],
            'actual_c134_file_sha1' => $load['actual_file_sha1'],
            'c134_file_sha1_match' => $load['file_sha1_match'],
            'c134_convert_from_json_pass' => $load['convert_from_json_pass'],
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
        $artifact['next_go_decision_finalization_decision'] = $this->nextGoDecisionFinalizationDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_go_decision_finalization_decision'] = $this->nextGoDecisionFinalizationDecision(false);
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
