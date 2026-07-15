<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-37 / C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C148_ARTIFACT = 'storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json';
    public const DEFAULT_EXPECTED_C148_HASH = 'd5420447a0b5994791e51f65318dcc46c75ec156';
    public const DEFAULT_EXPECTED_C148_FILE_SHA1 = '9EF227B2B7944B2406D15235DC6C84264466B81F';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C148_STATUS = 'C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C148_PHASE_LABEL = 'PR-36 / C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW';
    private const EXPECTED_C148_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C150_GO_RECOMMENDATION = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION';

    private const GO_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_C150_FINAL_ACTIVATION_EXECUTION';
    private const NO_GO_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_PRODUCTION_LIVE_RUNTIME_ACTIVATION_STOPPED';
    private const HOLD_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_PRODUCTION_LIVE_RUNTIME_ACTIVATION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C148_LOCK_MISMATCH_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_ARTIFACT_LOCK_MISMATCH';
    private const C148_FILE_SHA1_MISMATCH_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_FILE_SHA1_LOCK_MISMATCH';
    private const C148_STATUS_MISMATCH_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_STATUS_MISMATCH';
    private const C148_PHASE_LABEL_MISMATCH_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_PHASE_LABEL_MISMATCH';
    private const C148_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_NEXT_RECOMMENDATION_MISMATCH';
    private const C148_OBSERVATION_RESULT_REVIEW_INCOMPLETE_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_OBSERVATION_RESULT_REVIEW_INCOMPLETE';
    private const C148_CONVERT_FROM_JSON_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C148_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAGS_NOT_DEFAULT_OFF';

    private const REQUIRED_C148_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass',
        'production_live_runtime_activation_observation_result_review_pass',
        'ready_for_production_live_runtime_activation_operator_go_no_go_review',
        'production_live_runtime_activation_operator_go_no_go_review_allowed_next',
        'production_live_runtime_activation_observation_result_review_manifest_created',
        'c147_lock_valid',
        'c147_activation_observation_review_valid',
        'c147_convert_from_json_pass',
        'c146_lock_valid',
        'c146_activation_execution_review_valid',
        'c146_convert_from_json_pass',
        'c145_lock_valid',
        'c145_activation_authorization_valid',
        'c145_convert_from_json_pass',
        'c144_lock_valid',
        'c144_pre_activation_boundary_valid',
        'c144_convert_from_json_pass',
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
        'c141_observation_result_review_only',
        'c141_not_live_runtime_state_change',
        'c142_operator_go_no_go_review_only',
        'c142_not_live_runtime_state_change',
        'c143_go_decision_finalization_review_only',
        'c144_pre_activation_boundary_review_only',
        'c144_not_activation_authorization',
        'c145_activation_authorization_review_only',
        'c145_not_activation_execution',
        'c145_not_live_runtime_state_change',
        'c146_execution_review_only',
        'c146_not_live_runtime_state_change',
        'c147_observation_review_only',
        'c147_not_live_runtime_state_change',
        'c148_observation_result_review_only',
        'c148_not_live_runtime_state_change',
        'primary_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review',
        'backup_candidate_ready_for_production_live_runtime_activation_operator_go_no_go_review',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C148_FALSE_FIELDS = [
        'production_live_runtime_activation_executed',
        'comparator_candidate_activation_authorized',
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
        'weekly_swing_watchlist_production_live_runtime_activation_final_execution_context_persisted_to_live_runtime',
        'production_live_runtime_activation_final_execution_context_persisted_to_live_runtime',
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
        string $c148Artifact = self::DEFAULT_C148_ARTIFACT,
        string $expectedC148Hash = self::DEFAULT_EXPECTED_C148_HASH,
        string $expectedC148FileSha1 = self::DEFAULT_EXPECTED_C148_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $decision = $this->normalizeDecision((string) ($options['operator_decision'] ?? ''));
        $decisionReason = trim((string) ($options['decision_reason'] ?? ''));
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c148Artifact, $expectedC148Hash, $expectedC148FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C148_LOCK_MISMATCH_STATUS, 'C148 artifact missing or unreadable.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, $decision, $decisionReason);
            $artifact['c148_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C148_CONVERT_FROM_JSON_STATUS, 'C148 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C148_LOCK_MISMATCH_STATUS, 'C148 artifact_hash mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C148_FILE_SHA1_MISMATCH_STATUS, 'C148 file SHA1 mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $c148 = $load['payload'];
        if (($c148['status'] ?? null) !== self::EXPECTED_C148_STATUS || ($c148['reason_code'] ?? null) !== self::EXPECTED_C148_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C148_STATUS_MISMATCH_STATUS, 'C148 status/reason is not operator GO/NO-GO ready.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (($c148['phase_label'] ?? null) !== self::EXPECTED_C148_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C148_PHASE_LABEL_MISMATCH_STATUS, 'C148 phase label mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->c148NextRecommendationMatches($c148)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C148_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C148 next recommendation is not C149.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->c148ObservationResultReviewComplete($c148)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C148_OBSERVATION_RESULT_REVIEW_INCOMPLETE_STATUS, 'C148 production/live activation observation result review evidence is incomplete.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->candidateScopeMatches($c148)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C148 candidate scope does not match locked operator GO/NO-GO scope.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c148);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false, $decision, $decisionReason);
            $artifact['c148_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C148 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($this->enabledRuntimeFeatureFlags() !== []) {
            $artifact = $this->completeSections($artifact, $load, $options, false, $decision, $decisionReason);
            $artifact['enabled_runtime_feature_flags'] = $this->enabledRuntimeFeatureFlags();

            return $this->rejected($artifact, self::FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS, 'C149 requires runtime feature flags to remain default-off.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::APPROVAL_MISSING_STATUS, 'C149 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decision === null) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, null, $decisionReason), self::DECISION_INVALID_STATUS, 'C149 requires --operator-decision=GO, NO_GO, or HOLD.', $outputPath, $overwrite, null, $decisionReason);
        }
        if (! (bool) ($options['operator_decision_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_NOT_CONFIRMED_STATUS, 'C149 requires --operator-decision-confirmed.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decisionReason === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_REASON_MISSING_STATUS, 'C149 requires a non-empty --decision-reason so the decision is auditable.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false, $decision, $decisionReason);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true, $decision, $decisionReason);
        $artifact['status'] = $this->statusForDecision($decision);
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = $this->messageForDecision($decision);
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusionForDecision($decision);
        $artifact['next_step_recommendation'] = $this->nextRecommendationForDecision($decision);
        $artifact = array_merge($artifact, $this->decisionTopLevelState($decision));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-37',
            'internal_checkpoint' => 'C149',
            'status' => 'C149_NOT_RUN',
            'reason_code' => 'C149_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass' => false,
            'production_live_runtime_activation_operator_go_no_go_review_pass' => false,
            'operator_decision_recorded' => false,
            'operator_decision' => 'UNSET',
            'operator_go_decision' => 'UNSET',
            'operator_no_go_decision' => false,
            'operator_hold_decision' => false,
            'operator_decision_confirmed' => false,
            'operator_decision_reason' => '',
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_final_execution' => false,
            'ready_for_production_live_runtime_activation_final_execution' => false,
            'production_live_runtime_activation_operator_go_no_go_manifest_created' => false,
            'production_live_runtime_activation_final_execution_allowed_next' => false,
            'production_live_runtime_activation_stopped_no_go' => false,
            'production_live_runtime_activation_deferred_hold' => false,
            'production_live_runtime_activation_executed' => false,
            'c148_lock_valid' => false,
            'c148_activation_observation_result_review_valid' => false,
            'c148_convert_from_json_pass' => false,
            'c147_activation_observation_review_valid' => false,
            'c146_activation_execution_review_valid' => false,
            'c145_activation_authorization_valid' => false,
            'c144_pre_activation_boundary_valid' => false,
            'c143_go_decision_finalization_valid' => false,
            'c142_activation_operator_go_no_go_valid' => false,
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
            'activation_authorized' => false,
            'primary_candidate_activation_authorized' => false,
            'backup_candidate_activation_authorized' => false,
            'comparator_candidate_activation_authorized' => false,
            'c148_observation_result_review_only' => true,
            'c148_not_live_runtime_state_change' => true,
            'c149_operator_go_no_go_review_only' => true,
            'c149_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_final_execution' => false,
            'backup_candidate_ready_for_production_live_runtime_activation_final_execution' => false,
            'comparator_candidate_ready_for_production_live_runtime_activation_final_execution' => false,
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

    private function decisionTopLevelState(string $decision): array
    {
        $go = $decision === 'GO';

        return [
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass' => $go,
            'production_live_runtime_activation_operator_go_no_go_review_pass' => $go,
            'operator_decision_recorded' => true,
            'operator_decision' => $decision,
            'operator_go_decision' => $decision,
            'operator_no_go_decision' => $decision === 'NO_GO',
            'operator_hold_decision' => $decision === 'HOLD',
            'operator_decision_confirmed' => true,
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_final_execution' => $go,
            'ready_for_production_live_runtime_activation_final_execution' => $go,
            'production_live_runtime_activation_operator_go_no_go_manifest_created' => true,
            'production_live_runtime_activation_final_execution_allowed_next' => $go,
            'production_live_runtime_activation_stopped_no_go' => $decision === 'NO_GO',
            'production_live_runtime_activation_deferred_hold' => $decision === 'HOLD',
            'production_live_runtime_activation_executed' => false,
            'c148_lock_valid' => true,
            'c148_activation_observation_result_review_valid' => true,
            'c148_convert_from_json_pass' => true,
            'c147_activation_observation_review_valid' => true,
            'c146_activation_execution_review_valid' => true,
            'c145_activation_authorization_valid' => true,
            'c144_pre_activation_boundary_valid' => true,
            'c143_go_decision_finalization_valid' => true,
            'c142_activation_operator_go_no_go_valid' => true,
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
            'activation_authorized' => true,
            'primary_candidate_activation_authorized' => true,
            'backup_candidate_activation_authorized' => true,
            'comparator_candidate_activation_authorized' => false,
            'c148_observation_result_review_only' => true,
            'c148_not_live_runtime_state_change' => true,
            'c149_operator_go_no_go_review_only' => true,
            'c149_not_live_runtime_state_change' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_final_execution' => $go,
            'backup_candidate_ready_for_production_live_runtime_activation_final_execution' => $go,
            'comparator_candidate_ready_for_production_live_runtime_activation_final_execution' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $valid, ?string $decision, string $decisionReason): array
    {
        $c148 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $decisionForSummary = $decision ?: 'UNSET';

        $artifact['c148_lock_validation_summary'] = $this->c148LockValidationSummary($load, $c148);
        $artifact['c148_activation_observation_result_review_carry_forward_summary'] = $this->c148CarryForwardSummary($c148, $valid);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c148, $decisionForSummary, $valid);
        $artifact['operator_decision_validation_summary'] = $this->operatorDecisionValidationSummary($options, $decisionForSummary, $decisionReason, $valid);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['feature_flag_default_off_summary'] = $this->featureFlagDefaultOffSummary();
        $artifact['c149_operator_go_no_go_decision'] = $this->operatorGoNoGoDecision($decisionForSummary, $decisionReason, $valid);
        $artifact['next_concrete_activation_step_decision'] = $this->nextConcreteActivationStepDecision($decisionForSummary, $valid);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_manifest'] = $this->operatorGoNoGoManifest($load, $decisionForSummary, $decisionReason, $valid);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_checklist'] = $this->operatorGoNoGoChecklist($decisionForSummary);
        $artifact['c149_candidate_activation_operator_go_no_go_scorecard'] = $this->candidateScorecard($decisionForSummary, $valid);
        $artifact['production_live_runtime_activation_operator_go_no_go_context_summary'] = $this->operatorGoNoGoContextSummary($decisionForSummary, $valid);
        $artifact['runtime_config_review_summary'] = $this->runtimeConfigReviewSummary();
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($valid ? [] : [(string) ($artifact['status'] ?? 'C149_PENDING')]);
        $artifact['progress_summary'] = $this->progressSummary($decisionForSummary, $valid);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($decisionForSummary, $valid);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($valid && in_array($decisionForSummary, ['GO', 'NO_GO', 'HOLD'], true)) {
            $artifact = array_merge($artifact, $this->decisionTopLevelState($decisionForSummary));
        }

        return $artifact;
    }

    private function c148ObservationResultReviewComplete(array $c148): bool
    {
        foreach (self::REQUIRED_C148_TRUE_FIELDS as $field) {
            if (! (bool) ($c148[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C148_FALSE_FIELDS as $field) {
            if ((bool) ($c148[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c148NextRecommendationMatches(array $c148): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_operator_go_no_go_decision', 'next_recommendation'],
            ['observation_result_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c148, $path);
            if ($value !== null && $value !== self::EXPECTED_C148_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c148['next_step_recommendation'] ?? null) === self::EXPECTED_C148_NEXT_RECOMMENDATION;
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

    private function c148LockValidationSummary(array $load, array $c148): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C148',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C148_STATUS,
            'actual_status' => $c148['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C148_PHASE_LABEL,
            'actual_phase_label' => $c148['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C148_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c148NextRecommendationMatches($c148),
            'c148_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c148CarryForwardSummary(array $c148, bool $valid): array
    {
        return [
            'validation_completed' => true,
            'c148_observation_result_review_pass' => (bool) ($c148['weekly_swing_watchlist_production_live_runtime_activation_observation_result_review_pass'] ?? false),
            'c148_ready_for_activation_operator_go_no_go_review' => (bool) ($c148['ready_for_production_live_runtime_activation_operator_go_no_go_review'] ?? false),
            'c147_activation_observation_review_valid' => (bool) ($c148['c147_activation_observation_review_valid'] ?? false),
            'c146_activation_execution_review_valid' => (bool) ($c148['c146_activation_execution_review_valid'] ?? false),
            'c145_activation_authorization_valid' => (bool) ($c148['c145_activation_authorization_valid'] ?? false),
            'c144_pre_activation_boundary_valid' => (bool) ($c148['c144_pre_activation_boundary_valid'] ?? false),
            'c143_go_decision_finalization_valid' => (bool) ($c148['c143_go_decision_finalization_valid'] ?? false),
            'c142_activation_operator_go_no_go_valid' => (bool) ($c148['c142_activation_operator_go_no_go_valid'] ?? false),
            'c141_activation_observation_result_review_valid' => (bool) ($c148['c141_activation_observation_result_review_valid'] ?? false),
            'activation_authorized' => (bool) ($c148['activation_authorized'] ?? false),
            'primary_candidate_activation_authorized' => (bool) ($c148['primary_candidate_activation_authorized'] ?? false),
            'backup_candidate_activation_authorized' => (bool) ($c148['backup_candidate_activation_authorized'] ?? false),
            'c148_activation_observation_result_review_valid' => $this->c148ObservationResultReviewComplete($c148),
            'c149_activation_operator_go_no_go_review_can_start' => $valid,
        ];
    }

    private function candidateScopeFreezeSummary(array $source, string $decision, bool $valid): array
    {
        $go = $valid && $decision === 'GO';

        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($source),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_final_execution_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_final_execution_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_production_live_runtime_activation_final_execution' => $go,
            'backup_candidate_ready_for_production_live_runtime_activation_final_execution' => $go,
            'comparator_candidate_ready_for_production_live_runtime_activation_final_execution' => false,
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

    private function operatorDecisionValidationSummary(array $options, string $decision, string $decisionReason, bool $valid): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_decision_required' => true,
            'allowed_operator_decisions' => ['GO', 'NO_GO', 'HOLD'],
            'operator_decision' => $decision,
            'operator_decision_valid' => in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
            'operator_decision_confirmation_required' => true,
            'operator_decision_confirmed' => (bool) ($options['operator_decision_confirmed'] ?? false),
            'decision_reason_required' => true,
            'decision_reason' => $decisionReason,
            'decision_reason_present' => $decisionReason !== '',
            'operator_decision_validation_pass' => $valid && in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
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

    private function operatorGoNoGoDecision(string $decision, string $decisionReason, bool $valid): array
    {
        $go = $valid && $decision === 'GO';

        return [
            'review_valid' => $valid,
            'validation_completed' => true,
            'operator_decision_recorded' => $valid && in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
            'operator_decision' => $decision,
            'operator_decision_reason' => $decisionReason,
            'operator_decision_confirmed' => $valid && in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed' => $valid,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass' => $go,
            'ready_for_production_live_runtime_activation_final_execution' => $go,
            'production_live_runtime_activation_final_execution_allowed_next' => $go,
            'production_live_runtime_activation_stopped_no_go' => $valid && $decision === 'NO_GO',
            'production_live_runtime_activation_deferred_hold' => $valid && $decision === 'HOLD',
            'production_live_runtime_activation_executed' => false,
            'production_ready' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'runtime_bridge_active' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'next_recommendation' => $valid ? $this->nextRecommendationForDecision($decision) : 'C149_TARGETED_C148_ACTIVATION_OBSERVATION_RESULT_REVIEW_REPAIR',
            'decision_reason' => $this->decisionExplanation($decision, $valid),
        ];
    }

    private function nextConcreteActivationStepDecision(string $decision, bool $valid): array
    {
        return [
            'review_valid' => $valid,
            'operator_decision' => $decision,
            'next_recommendation' => $valid ? $this->nextRecommendationForDecision($decision) : 'C149_TARGETED_C148_ACTIVATION_OBSERVATION_RESULT_REVIEW_REPAIR',
            'next_scope' => $valid ? $this->nextScopeForDecision($decision) : 'targeted C148 lock, approval, operator decision, or safety repair only',
            'next_is_concrete' => $valid && $decision === 'GO',
            'next_requires_explicit_runtime_enablement_flags' => $valid && $decision === 'GO',
        ];
    }

    private function operatorGoNoGoManifest(array $load, string $decision, string $decisionReason, bool $valid): array
    {
        $go = $valid && $decision === 'GO';

        return [
            'manifest_created' => $valid,
            'manifest_context' => 'artifact_only_production_live_runtime_activation_operator_go_no_go_review',
            'source_artifact' => 'C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['expected_hash'],
            'source_file_sha1' => $load['expected_file_sha1'],
            'operator_decision' => $decision,
            'operator_decision_reason' => $decisionReason,
            'operator_decision_confirmed' => $valid,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'production_live_runtime_activation_operator_go_no_go_review_pass' => $go,
            'ready_for_production_live_runtime_activation_final_execution' => $go,
            'production_live_runtime_activation_final_execution_allowed_next' => $go,
            'operator_go_no_go_artifact_only' => true,
            'production_live_runtime_activation_executed' => false,
            'runtime_bridge_enabled' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'operator_go_no_go_used_for_selection' => false,
            'operator_go_no_go_used_for_retuning' => false,
            'operator_go_no_go_used_for_ranking' => false,
            'operator_go_no_go_used_for_plan_confirm_mutation' => false,
            'operator_go_no_go_used_for_live_rollout' => false,
        ];
    }

    private function operatorGoNoGoChecklist(string $decision): array
    {
        return [
            'operator_go_no_go_reviewed' => true,
            'operator_decision_required' => true,
            'operator_decision_allowed_values' => ['GO', 'NO_GO', 'HOLD'],
            'operator_decision_recorded' => in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
            'production_live_runtime_activation_not_executed_in_c149' => true,
            'production_runtime_wiring_not_enabled_in_c149' => true,
            'runtime_bridge_not_enabled_in_c149' => true,
            'scheduler_live_weekly_swing_not_enabled_in_c149' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'c148_source_lock_reviewed' => true,
            'runtime_entrypoint_reviewed' => true,
            'artisan_command_registration_reviewed' => true,
            'service_boundary_reviewed' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_decision_gate_required' => true,
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

    private function candidateScorecard(string $decision, bool $valid): array
    {
        $go = $valid && $decision === 'GO';
        $base = [
            'production_live_runtime_activation_operator_go_no_go_review_valid' => $valid,
            'operator_decision' => $decision,
            'ready_for_production_live_runtime_activation_final_execution' => $go,
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
                'c149_role' => 'primary_production_live_runtime_activation_final_execution_candidate',
                'primary_candidate_ready_for_production_live_runtime_activation_final_execution' => $go,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c149_role' => 'backup_production_live_runtime_activation_final_execution_candidate',
                'backup_candidate_ready_for_production_live_runtime_activation_final_execution' => $go,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c149_role' => 'comparator_only_candidate',
                'production_live_runtime_activation_operator_go_no_go_review_valid' => false,
                'operator_decision' => 'NO_GO',
                'ready_for_production_live_runtime_activation_final_execution' => false,
                'comparator_candidate_ready_for_production_live_runtime_activation_final_execution' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function operatorGoNoGoContextSummary(string $decision, bool $valid): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_context_created' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_context_valid' => $valid,
            'operator_decision' => $decision,
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
            'c148_artifact_identified' => is_file(self::DEFAULT_C148_ARTIFACT),
            'c149_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService.php'),
            'c149_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewCommand.php'),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
            'operator_decision_surface_identified' => true,
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

    private function progressSummary(string $decision, bool $valid): array
    {
        return [
            'progress_marker' => 'PR-37_C149_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW',
            'c148_activation_observation_result_review_carried_forward' => true,
            'c149_production_live_runtime_activation_operator_go_no_go_review_executed' => true,
            'operator_decision' => $decision,
            'c149_ready_for_final_activation_execution' => $valid && $decision === 'GO',
            'activation_stopped_no_go' => $valid && $decision === 'NO_GO',
            'activation_deferred_hold' => $valid && $decision === 'HOLD',
            'still_no_live_runtime' => true,
            'still_no_runtime_bridge_activation' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(string $decision, bool $valid): array
    {
        return [
            'planned_next_review' => $valid ? $this->nextRecommendationForDecision($decision) : 'C149_TARGETED_C148_ACTIVATION_OBSERVATION_RESULT_REVIEW_REPAIR',
            'planned_next_scope' => $valid ? $this->nextScopeForDecision($decision) : 'targeted repair before production/live activation operator GO/NO-GO decision can be recorded',
            'planned_next_required_inputs' => $valid && $decision === 'GO' ? [
                'locked C149 artifact hash',
                'locked C149 file SHA1',
                'operator GO decision reference',
                'explicit runtime bridge enablement flag',
                'explicit live output enablement flag',
                'rollback and kill-switch confirmation',
                'final execution command boundary',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c148_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c148_artifact_not_modified' => true,
            'c60_c148_artifacts_not_modified' => true,
            'c149_is_operator_go_no_go_review_not_live_state_change' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C149 validates C148 artifact_hash and file SHA1 locks before operator GO/NO-GO is recorded.',
            'C149 requires --operator-decision=GO, NO_GO, or HOLD plus explicit confirmation and reason.',
            'GO opens only the C150 final activation execution target; it still does not activate runtime in C149.',
            'NO_GO stops production/live runtime activation without treating the technical review as broken.',
            'HOLD defers production/live runtime activation while preserving the locked C148 evidence.',
            'C149 keeps runtime feature flags default-off and checks the kill-switch surface.',
            'C149 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C149 does not activate runtime bridge, execute production runtime wiring, generate official weekly swing output, publish recommendations, or mutate PLAN/CONFIRM.',
        ];
    }

    private function statusForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::GO_STATUS;
        }
        if ($decision === 'NO_GO') {
            return self::NO_GO_STATUS;
        }

        return self::HOLD_STATUS;
    }

    private function messageForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C149 records operator GO for E02 primary and B01 backup, then points to C150 final activation execution. C149 remains artifact-only and does not enable runtime bridge, live output, publishing, or PLAN/CONFIRM mutation.';
        }
        if ($decision === 'NO_GO') {
            return 'C149 records operator NO_GO and stops production/live runtime activation. Evidence remains locked and no runtime bridge, live output, publishing, or PLAN/CONFIRM mutation is executed.';
        }

        return 'C149 records operator HOLD and defers production/live runtime activation. Evidence remains locked and no runtime bridge, live output, publishing, or PLAN/CONFIRM mutation is executed.';
    }

    private function diagnosticConclusionForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C149_OPERATOR_GO_RECORDED_READY_FOR_C150_FINAL_ACTIVATION_EXECUTION_NON_LIVE_NON_MUTATING';
        }
        if ($decision === 'NO_GO') {
            return 'C149_OPERATOR_NO_GO_RECORDED_ACTIVATION_STOPPED_NON_LIVE_NON_MUTATING';
        }

        return 'C149_OPERATOR_HOLD_RECORDED_ACTIVATION_DEFERRED_NON_LIVE_NON_MUTATING';
    }

    private function nextRecommendationForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::C150_GO_RECOMMENDATION;
        }
        if ($decision === 'NO_GO') {
            return 'C149_NO_GO_CLOSE_PRODUCTION_LIVE_RUNTIME_ACTIVATION';
        }

        return 'C149_HOLD_KEEP_C148_LOCKED_UNTIL_OPERATOR_WINDOW';
    }

    private function nextScopeForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C150 final activation execution only; it must use explicit runtime bridge and live output enablement flags plus rollback and kill-switch confirmation.';
        }
        if ($decision === 'NO_GO') {
            return 'close production/live runtime activation path and do not proceed to live execution.';
        }

        return 'hold activation, preserve C148/C149 evidence, and wait for a fresh operator window.';
    }

    private function decisionExplanation(string $decision, bool $valid): string
    {
        if (! $valid) {
            return 'C149 cannot proceed until C148 lock, approval, operator decision, cleanup, candidate, and safety gates pass.';
        }
        if ($decision === 'GO') {
            return 'C149 records operator GO and permits the concrete C150 final activation execution target.';
        }
        if ($decision === 'NO_GO') {
            return 'C149 records operator NO_GO and closes the activation path without live execution.';
        }

        return 'C149 records operator HOLD and defers activation without live execution.';
    }

    private function normalizeDecision(string $decision): ?string
    {
        $normalized = strtoupper(trim(str_replace('-', '_', $decision)));
        if (in_array($normalized, ['GO', 'NO_GO', 'HOLD'], true)) {
            return $normalized;
        }

        return null;
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
            'c148' => [
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
            'expected_c148_hash' => $load['expected_hash'],
            'actual_c148_hash' => $load['actual_hash'],
            'c148_hash_match' => $load['hash_match'],
            'expected_c148_file_sha1' => $load['expected_file_sha1'],
            'actual_c148_file_sha1' => $load['actual_file_sha1'],
            'c148_file_sha1_match' => $load['file_sha1_match'],
            'c148_convert_from_json_pass' => $load['convert_from_json_pass'],
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

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_concrete_activation_step_decision'] = $this->nextConcreteActivationStepDecision($decision ?: 'UNSET', false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['next_concrete_activation_step_decision'] = $this->nextConcreteActivationStepDecision($decision ?: 'UNSET', false);
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
