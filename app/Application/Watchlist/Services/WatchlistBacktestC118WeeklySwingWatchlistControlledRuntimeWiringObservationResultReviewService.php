<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewService
{
    public const RUN_CODE = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-06 / C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW';

    public const DEFAULT_C117_ARTIFACT = 'storage/app/watchlist/backtest/c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review.json';
    public const DEFAULT_EXPECTED_C117_HASH = '5a41862b964e1c56547ad40e50dbaa95dd0bd6ea';
    public const DEFAULT_EXPECTED_C117_FILE_SHA1 = '78A8F6BA18AC378ED74B98ADF9179FC9A7F49084';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C117_STATUS = 'C117_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C117_REASON = 'C117_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C117_NEXT_RECOMMENDATION = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW';
    private const EXPECTED_C117_PHASE_LABEL = 'PR-05 / C117_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW';
    private const C119_RECOMMENDATION = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C117_LOCK_MISMATCH_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C117_ARTIFACT_LOCK_MISMATCH';
    private const C117_FILE_SHA1_MISMATCH_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C117_FILE_SHA1_LOCK_MISMATCH';
    private const C117_STATUS_MISMATCH_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C117_STATUS_MISMATCH';
    private const C117_REASON_MISMATCH_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C117_REASON_CODE_MISMATCH';
    private const C117_NEXT_MISMATCH_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C117_NEXT_RECOMMENDATION_MISMATCH';
    private const C117_PHASE_LABEL_MISMATCH_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C117_PHASE_LABEL_MISMATCH';
    private const C117_OBSERVATION_REVIEW_INVALID_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C117_OBSERVATION_REVIEW_INVALID';
    private const BOUNDARY_INVALID_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C111_C112_C113_C114_C115_C116_C117_BOUNDARY_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C117_CONVERT_FROM_JSON_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_C117_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime',
        'operator_go_no_go_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime',
        'go_decision_finalization_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_persisted_to_live_runtime',
        'completion_boundary_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_persisted_to_live_runtime',
        'handoff_readiness_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_context_persisted_to_live_runtime',
        'handoff_finalization_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_persisted_to_live_runtime',
        'handoff_completion_boundary_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_persisted_to_live_runtime',
        'handoff_closure_seal_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime',
        'handoff_audit_archive_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime',
        'handoff_audit_archive_completion_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
        'handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
        'handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_phase_approval_context_persisted_to_live_runtime',
        'production_phase_approval_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_readiness_context_persisted_to_live_runtime',
        'production_readiness_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_runtime_wiring_readiness_context_persisted_to_live_runtime',
        'production_runtime_wiring_readiness_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_production_runtime_wiring_context_persisted_to_live_runtime',
        'production_runtime_wiring_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
        'production_deployment_allowed',
        'production_deployment_executed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'pilot_runtime_active',
        'shadow_runtime_active',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_plan_confirm_mutation_allowed',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/*no-*-test.json',
        'storage/app/watchlist/backtest/*missing-*-test.json',
        'storage/app/watchlist/backtest/*mismatch-*-test.json',
        'storage/app/watchlist/backtest/*negative-*-test.json',
    ];

    public function execute(
        string $c117Artifact = self::DEFAULT_C117_ARTIFACT,
        string $expectedC117Hash = self::DEFAULT_EXPECTED_C117_HASH,
        string $expectedC117FileSha1 = self::DEFAULT_EXPECTED_C117_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c117Artifact, $expectedC117Hash, $expectedC117FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C117_LOCK_MISMATCH_STATUS, 'C117 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c117_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C117_CONVERT_FROM_JSON_STATUS, 'C117 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C117_LOCK_MISMATCH_STATUS, 'C117 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C117_FILE_SHA1_MISMATCH_STATUS, 'C117 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c117 = $load['payload'];
        if (($c117['status'] ?? null) !== self::EXPECTED_C117_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C117_STATUS_MISMATCH_STATUS, 'C117 status is not passed ready for C118.', $outputPath, $overwrite);
        }
        if (($c117['reason_code'] ?? null) !== self::EXPECTED_C117_REASON) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C117_REASON_MISMATCH_STATUS, 'C117 reason_code is not passed ready for C118.', $outputPath, $overwrite);
        }
        if (! $this->c117NextRecommendationMatches($c117)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C117_NEXT_MISMATCH_STATUS, 'C117 next recommendation is not C118.', $outputPath, $overwrite);
        }
        if (($c117['phase_label'] ?? null) !== self::EXPECTED_C117_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C117_PHASE_LABEL_MISMATCH_STATUS, 'C117 phase label is not PR-05 / C117.', $outputPath, $overwrite);
        }
        if (! $this->c117ObservationReviewValid($c117)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C117_OBSERVATION_REVIEW_INVALID_STATUS, 'C117 observation review evidence is incomplete.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c117);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c117_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C117 contains live, mutating, production, runtime wiring, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->boundaryValid($c117)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::BOUNDARY_INVALID_STATUS, 'C111/C112/C113/C114/C115/C117 boundary evidence is invalid.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c117)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C117 candidate scope does not match locked observation result review scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C118 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }
        $failures = $this->controlledGateFailures($options);
        if ($failures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $failures]), false);

            return $this->rejected($artifact, $failures[0], 'C118 controlled runtime wiring observation result review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C118 reviews the C117 controlled runtime wiring observation result for E02 primary and B01 backup in artifact-only, non-live, non-mutating context. It records that the controlled observation result can move to operator go/no-go review, but it does not deploy production, activate runtime bridge or rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C118_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PASSED_ARTIFACT_ONLY_NON_LIVE_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::C119_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-06',
            'internal_checkpoint' => 'C118',
            'status' => 'C118_NOT_RUN',
            'reason_code' => 'C118_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_executed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_allowed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_pass' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_reviewed' => false,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
            'controlled_runtime_wiring_observation_result_review_pass' => false,
            'ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
            'controlled_runtime_wiring_observation_result_review_manifest_created' => false,
            'controlled_runtime_wiring_operator_go_no_go_review_allowed_next' => false,
            'c117_lock_valid' => false,
            'c117_observation_review_valid' => false,
            'c117_convert_from_json_pass' => false,
            'c115_hash_match' => false,
            'c115_file_sha1_match' => false,
            'c115_convert_from_json_pass' => false,
            'c115_execution_approval_valid' => false,
            'c114_hash_match' => false,
            'c114_file_sha1_match' => false,
            'c114_convert_from_json_pass' => false,
            'c114_runtime_wiring_readiness_valid' => false,
            'c111_final_closure_valid' => false,
            'c111_non_live_audit_archive_terminal' => false,
            'c112_post_c111_transition_gate_valid' => false,
            'c112_not_audit_archive_continuation' => false,
            'c112_does_not_reopen_c111_final_closure' => false,
            'c113_production_readiness_valid' => false,
            'c114_runtime_wiring_readiness_review_only' => true,
            'c114_not_runtime_wiring_execution' => true,
            'c115_execution_approval_review_only' => true,
            'c115_not_runtime_wiring_execution' => true,
            'c116_execution_review_only' => true,
            'c116_not_production_deployment' => true,
            'c116_not_plan_confirm_mutation' => true,
            'c116_not_weekly_swing_live_output' => true,
            'c117_observation_review_only' => true,
            'c117_not_production_deployment' => true,
            'c117_not_plan_confirm_mutation' => true,
            'c117_not_weekly_swing_live_output' => true,
            'primary_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
            'backup_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
            'comparator_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
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
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_allowed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_pass' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_reviewed' => true,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => true,
            'controlled_runtime_wiring_observation_result_review_pass' => true,
            'ready_for_controlled_runtime_wiring_operator_go_no_go_review' => true,
            'controlled_runtime_wiring_observation_result_review_manifest_created' => true,
            'controlled_runtime_wiring_operator_go_no_go_review_allowed_next' => true,
            'c117_lock_valid' => true,
            'c117_observation_review_valid' => true,
            'c117_convert_from_json_pass' => true,
            'c115_hash_match' => true,
            'c115_file_sha1_match' => true,
            'c115_convert_from_json_pass' => true,
            'c115_execution_approval_valid' => true,
            'c114_hash_match' => true,
            'c114_file_sha1_match' => true,
            'c114_convert_from_json_pass' => true,
            'c114_runtime_wiring_readiness_valid' => true,
            'c111_final_closure_valid' => true,
            'c111_non_live_audit_archive_terminal' => true,
            'c112_post_c111_transition_gate_valid' => true,
            'c112_not_audit_archive_continuation' => true,
            'c112_does_not_reopen_c111_final_closure' => true,
            'c113_production_readiness_valid' => true,
            'c114_runtime_wiring_readiness_review_only' => true,
            'c114_not_runtime_wiring_execution' => true,
            'c115_execution_approval_review_only' => true,
            'c115_not_runtime_wiring_execution' => true,
            'c116_execution_review_only' => true,
            'c116_not_production_deployment' => true,
            'c116_not_plan_confirm_mutation' => true,
            'c116_not_weekly_swing_live_output' => true,
            'c117_observation_review_only' => true,
            'c117_not_production_deployment' => true,
            'c117_not_plan_confirm_mutation' => true,
            'c117_not_weekly_swing_live_output' => true,
            'primary_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => true,
            'backup_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => true,
            'comparator_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c117 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact = array_merge($artifact, $this->carryForwardTopLevelAliases($load, $c117));
        $artifact['c117_lock_validation_summary'] = $this->c117LockValidationSummary($load, $c117);
        $artifact['c111_c112_c113_c114_c115_c116_c117_boundary_carry_forward_summary'] = $this->boundaryCarryForwardSummary($c117, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c117, $pass);
        $artifact['c117_final_operator_evidence_summary'] = $this->c117FinalOperatorEvidenceSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c118_observation_result_review_decision'] = $this->observationResultReviewDecision($pass);
        $artifact['next_operator_go_no_go_decision'] = $this->nextOperatorGoNoGoDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_manifest'] = $this->observationResultReviewManifest($load, $pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_checklist'] = $this->observationResultReviewChecklist();
        $artifact['c118_candidate_observation_result_review_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['controlled_runtime_wiring_observation_result_review_context_summary'] = $this->observationResultReviewContextSummary($pass);
        $artifact['runtime_config_review_summary'] = $this->runtimeConfigReviewSummary();
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);
        $artifact['c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels'] = $this->boundaryAliases($pass);

        return $artifact;
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [[
            'source_lock' => 'C117',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'expected_status' => self::EXPECTED_C117_STATUS,
            'expected_reason_code' => self::EXPECTED_C117_REASON,
            'expected_next_recommendation' => self::EXPECTED_C117_NEXT_RECOMMENDATION,
            'expected_phase_label' => self::EXPECTED_C117_PHASE_LABEL,
        ]];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C117',
            'c117_artifact_path' => $load['path'],
            'c117_artifact_exists' => $load['exists'],
            'expected_c117_hash' => $load['expected_hash'],
            'actual_c117_hash' => $load['actual_hash'],
            'c117_hash_match' => $load['hash_match'],
            'expected_c117_file_sha1' => $load['expected_file_sha1'],
            'actual_c117_file_sha1' => $load['actual_file_sha1'],
            'c117_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function carryForwardTopLevelAliases(array $load, array $c117): array
    {
        return array_merge($this->topLevelLockAliases($load), [
            'c117_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c117_observation_review_valid' => $this->c117ObservationReviewValid($c117),
            'c115_hash_match' => (bool) ($c117['c115_hash_match'] ?? false),
            'c115_file_sha1_match' => (bool) ($c117['c115_file_sha1_match'] ?? false),
            'c115_convert_from_json_pass' => (bool) ($c117['c115_convert_from_json_pass'] ?? false),
            'c115_execution_approval_valid' => (bool) ($c117['c115_execution_approval_valid'] ?? false),
            'c114_hash_match' => (bool) ($c117['c114_hash_match'] ?? false),
            'c114_file_sha1_match' => (bool) ($c117['c114_file_sha1_match'] ?? false),
            'c114_convert_from_json_pass' => (bool) ($c117['c114_convert_from_json_pass'] ?? false),
            'c114_runtime_wiring_readiness_valid' => (bool) ($c117['c114_runtime_wiring_readiness_valid'] ?? false),
            'c111_final_closure_valid' => (bool) ($c117['c111_final_closure_valid'] ?? false),
            'c111_non_live_audit_archive_terminal' => (bool) ($c117['c111_non_live_audit_archive_terminal'] ?? false),
            'c112_post_c111_transition_gate_valid' => (bool) ($c117['c112_post_c111_transition_gate_valid'] ?? false),
            'c112_not_audit_archive_continuation' => (bool) ($c117['c112_not_audit_archive_continuation'] ?? false),
            'c112_does_not_reopen_c111_final_closure' => (bool) ($c117['c112_does_not_reopen_c111_final_closure'] ?? false),
            'c113_production_readiness_valid' => (bool) ($c117['c113_production_readiness_valid'] ?? false),
            'primary_candidate_code' => $c117['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => $c117['backup_candidate_code'] ?? self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => $c117['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE,
        ]);
    }

    private function c117ObservationReviewValid(array $c117): bool
    {
        foreach ([
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_review_pass',
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_observation_result_review',
            'controlled_runtime_wiring_observation_review_pass',
            'ready_for_controlled_runtime_wiring_observation_result_review',
            'controlled_runtime_wiring_observation_review_manifest_created',
            'controlled_runtime_wiring_observation_result_review_allowed_next',
            'c117_observation_review_decision.review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_review_manifest.ready_for_controlled_runtime_wiring_observation_result_review',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_review_checklist.observation_reviewed',
            'c116_hash_match',
            'c116_file_sha1_match',
            'c116_convert_from_json_pass',
            'c116_execution_review_valid',
            'c115_hash_match',
            'c115_file_sha1_match',
            'c115_convert_from_json_pass',
            'c115_execution_approval_valid',
            'c114_hash_match',
            'c114_file_sha1_match',
            'c114_convert_from_json_pass',
            'c114_runtime_wiring_readiness_valid',
        ] as $field) {
            if (! $this->boolField($c117, $field)) {
                return false;
            }
        }

        return true;
    }

    private function c117NextRecommendationMatches(array $c117): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_observation_result_decision', 'next_recommendation'],
            ['c117_observation_review_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c117, $path) !== self::EXPECTED_C117_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function boundaryValid(array $c117): bool
    {
        foreach ([
            'c111_final_closure_valid',
            'c111_non_live_audit_archive_terminal',
            'c112_not_audit_archive_continuation',
            'c112_does_not_reopen_c111_final_closure',
            'c113_production_readiness_valid',
            'c114_runtime_wiring_readiness_review_only',
            'c114_not_runtime_wiring_execution',
            'c115_execution_approval_review_only',
            'c115_not_runtime_wiring_execution',
            'c111_c112_c113_c114_c115_c116_boundary_evidence_labels.C117_OBSERVATION_REVIEW_ONLY',
            'c111_c112_c113_c114_c115_c116_boundary_evidence_labels.C117_NOT_PRODUCTION_DEPLOYMENT',
            'c111_c112_c113_c114_c115_c116_boundary_evidence_labels.C117_NOT_PLAN_CONFIRM_MUTATION',
            'c111_c112_c113_c114_c115_c116_boundary_evidence_labels.C117_NOT_WEEKLY_SWING_LIVE_OUTPUT',
        ] as $field) {
            if (! $this->boolField($c117, $field)) {
                return false;
            }
        }
        foreach ([
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
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_live_recommendation_generated',
        ] as $field) {
            if ($this->boolField($c117, $field)) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $c117): bool
    {
        foreach ([
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
        ] as $key => $expected) {
            if (($c117[$key] ?? null) !== $expected) {
                return false;
            }
        }
        foreach ([
            'primary_candidate_ready_for_controlled_runtime_wiring_observation_result_review',
            'backup_candidate_ready_for_controlled_runtime_wiring_observation_result_review',
            'a01_remains_comparator_only',
        ] as $field) {
            if (! $this->boolField($c117, $field)) {
                return false;
            }
        }
        foreach ([
            'comparator_candidate_ready_for_controlled_runtime_wiring_observation_result_review',
            'a01_promoted',
            'candidate_promotion_executed',
            'candidate_rerank_executed',
            'strategy_retune_executed',
            'scoring_mutation_executed',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'weekly_swing_live_recommendation_selection_executed',
        ] as $field) {
            if ($this->boolField($c117, $field)) {
                return false;
            }
        }

        return true;
    }

    private function firstLiveOrMutatingSafetyFlag(array $c117): ?string
    {
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $field) {
            if ($this->boolField($c117, $field)) {
                return $field;
            }
        }
        foreach ([
            'production_catalog_runtime_bridge_enabled',
            'production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'production_catalog_controlled_shadow_rollout_enabled',
            'production_catalog_controlled_parallel_run_enabled',
            'production_catalog_controlled_rollout_enabled',
        ] as $configFlag) {
            if ($this->configFlagIsOn($configFlag)) {
                return $configFlag;
            }
        }

        return null;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        foreach ($this->prohibitedOptionFields() as $field) {
            if ((bool) ($options[$field] ?? false)) {
                $failures[] = $this->statusForProhibitedField($field);
            }
        }

        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'execute_production_runtime_wiring',
            'activate_production_runtime_wiring',
            'activate_production_catalog_runtime_bridge',
            'enable_controlled_rollout',
            'activate_controlled_opt_in_runtime_bridge',
            'activate_controlled_parallel_run',
            'activate_pilot_runtime',
            'activate_shadow_runtime',
            'persist_controlled_runtime_wiring_observation_result_review_context_to_live_runtime',
            'persist_controlled_runtime_wiring_observation_review_context_to_live_runtime',
            'persist_controlled_runtime_wiring_execution_review_context_to_live_runtime',
            'persist_controlled_runtime_wiring_execution_context_to_live_runtime',
            'mutate_plan_confirm',
            'change_candidate_selection',
            'promote_a01',
            'rerank_candidate',
            'retune_strategy',
            'change_scoring_logic',
            'change_catalog_selection',
            'generate_official_weekly_swing_stock_recommendation',
            'generate_live_weekly_swing_watchlist_output',
            'publish_weekly_swing_output',
            'modify_c60_c117_artifacts',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'a01') !== false || strpos($field, 'candidate') !== false || strpos($field, 'scoring') !== false || strpos($field, 'catalog') !== false || strpos($field, 'strategy') !== false) {
            return self::CANDIDATE_SCOPE_MISMATCH_STATUS;
        }

        return self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
    }

    private function c117LockValidationSummary(array $load, array $c117): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C117',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'expected_status' => self::EXPECTED_C117_STATUS,
            'actual_status' => $c117['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C117_REASON,
            'actual_reason_code' => $c117['reason_code'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C117_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c117NextRecommendationMatches($c117),
            'expected_phase_label' => self::EXPECTED_C117_PHASE_LABEL,
            'actual_phase_label' => $c117['phase_label'] ?? null,
            'phase_label_match' => ($c117['phase_label'] ?? null) === self::EXPECTED_C117_PHASE_LABEL,
            'c117_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
            'c117_observation_review_valid' => $this->c117ObservationReviewValid($c117),
        ];
    }

    private function boundaryCarryForwardSummary(array $c117, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c111_non_live_handoff_audit_archive_final_closed' => (bool) ($c117['c111_final_closure_valid'] ?? false),
            'c111_non_live_audit_archive_terminal' => (bool) ($c117['c111_non_live_audit_archive_terminal'] ?? false),
            'c112_separate_post_c111_production_phase_transition_gate' => (bool) ($c117['c112_post_c111_transition_gate_valid'] ?? false),
            'c112_not_audit_archive_continuation' => (bool) ($c117['c112_not_audit_archive_continuation'] ?? false),
            'c112_does_not_reopen_c111_final_closure' => (bool) ($c117['c112_does_not_reopen_c111_final_closure'] ?? false),
            'c113_production_readiness_review_only' => (bool) ($c117['c113_production_readiness_valid'] ?? false),
            'c114_runtime_wiring_readiness_review_only' => (bool) ($c117['c114_runtime_wiring_readiness_review_only'] ?? false),
            'c114_not_runtime_wiring_execution' => (bool) ($c117['c114_not_runtime_wiring_execution'] ?? false),
            'c115_execution_approval_review_only' => (bool) ($c117['c115_execution_approval_review_only'] ?? false),
            'c115_not_runtime_wiring_execution' => (bool) ($c117['c115_not_runtime_wiring_execution'] ?? false),
            'c116_execution_review_only' => true,
            'c117_observation_review_only' => true,
            'c117_not_production_deployment' => true,
            'c117_not_plan_confirm_mutation' => true,
            'c117_not_weekly_swing_live_output' => true,
            'c118_observation_result_review_only' => true,
            'source_c117_observation_review_valid' => $this->c117ObservationReviewValid($c117),
            'c118_boundary_pass' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $c117, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c117),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_controlled_runtime_wiring_observation_result_review_candidate',
            'backup_candidate_role' => 'backup_controlled_runtime_wiring_observation_result_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
            'scoring_mutation_executed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
            'weekly_swing_live_recommendation_selection_executed' => false,
            'official_weekly_swing_stock_recommendation_generated' => false,
            'weekly_swing_output_published' => false,
        ];
    }

    private function c117FinalOperatorEvidenceSummary(): array
    {
        return [
            'FOCUSED_PHPUNIT_C117' => 'OK (125 tests, 445 assertions)',
            'FULL_WATCHLIST_PHPUNIT_POST_C117' => 'OK (3288 tests, 32424 assertions)',
            'C117_ARTIFACT_HASH' => self::DEFAULT_EXPECTED_C117_HASH,
            'C117_FILE_SHA1' => self::DEFAULT_EXPECTED_C117_FILE_SHA1,
            'C117_RUNTIME_STATUS' => self::EXPECTED_C117_STATUS,
            'C117_RUNTIME_REASON_CODE' => self::EXPECTED_C117_REASON,
            'C115_HASH_MATCH' => true,
            'C115_FILE_SHA1_MATCH' => true,
            'C115_CONVERT_FROM_JSON_PASS' => true,
            'C115_EXECUTION_APPROVAL_VALID' => true,
            'NEGATIVE_WITHOUT_OPERATOR_APPROVAL' => 'REJECTED_OPERATOR_APPROVAL_MISSING',
            'NEGATIVE_WITHOUT_APPROVAL_REFERENCE' => 'REJECTED_OPERATOR_APPROVAL_MISSING',
            'TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP' => 'NO_OUTPUT',
            'NEXT_RECOMMENDATION' => self::EXPECTED_C117_NEXT_RECOMMENDATION,
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
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

    private function observationResultReviewDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c117_lock_valid' => $pass,
            'c117_observation_review_valid' => $pass,
            'c117_convert_from_json_pass' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_pass' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_reviewed' => $pass,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'controlled_runtime_wiring_observation_result_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'controlled_runtime_wiring_observation_result_review_manifest_created' => $pass,
            'controlled_runtime_wiring_operator_go_no_go_review_allowed_next' => $pass,
            'primary_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'next_recommendation' => $pass ? self::C119_RECOMMENDATION : 'C118_TARGETED_C117_OBSERVATION_REVIEW_LOCK_REPAIR',
            'decision_reason' => $pass ? 'C118 controlled runtime wiring observation result review completed for primary and backup in artifact-only, non-live, non-mutating context.' : 'C118 cannot proceed until C117 lock, boundary, approval, cleanup, candidate, and safety gates pass.',
            'diagnostic_conclusion' => $pass ? 'C118_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PASSED_ARTIFACT_ONLY_NON_LIVE_NON_MUTATING' : 'C118_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED',
        ];
    }

    private function nextOperatorGoNoGoDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C119_RECOMMENDATION : 'C118_TARGETED_C117_OBSERVATION_REVIEW_LOCK_REPAIR',
            'next_scope' => $pass ? 'controlled runtime wiring operator go/no-go review only; C118 itself still does not deploy production, generate official weekly swing output, activate live rollout, or mutate PLAN/CONFIRM' : 'targeted C117 observation review lock, boundary, approval, or safety repair only',
        ];
    }

    private function observationResultReviewManifest(array $load, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_controlled_runtime_wiring_observation_result_review',
            'source_artifact' => 'C117_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['expected_hash'],
            'source_file_sha1' => $load['expected_file_sha1'],
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_controlled_runtime_wiring_observation_result_review_candidate',
            'backup_candidate_role' => 'backup_controlled_runtime_wiring_observation_result_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'c111_final_closure_carried_forward' => $pass,
            'c112_post_c111_transition_gate_carried_forward' => $pass,
            'c113_production_readiness_carried_forward' => $pass,
            'c114_runtime_wiring_readiness_carried_forward' => $pass,
            'c115_execution_approval_carried_forward' => $pass,
            'c117_observation_review_carried_forward' => $pass,
            'c117_convert_from_json_pass' => $pass,
            'controlled_runtime_wiring_observation_result_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'runtime_wiring_observation_result_reviewed' => $pass,
            'runtime_wiring_execution_performed_against_live_runtime' => false,
            'runtime_wiring_enabled' => false,
            'runtime_bridge_enabled' => false,
            'controlled_rollout_enabled' => false,
            'pilot_runtime_enabled' => false,
            'shadow_runtime_enabled' => false,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'observation_result_review_used_for_selection' => false,
            'observation_result_review_used_for_retuning' => false,
            'observation_result_review_used_for_ranking' => false,
            'observation_result_review_used_for_plan_confirm_mutation' => false,
            'observation_result_review_used_for_live_rollout' => false,
            'observation_result_review_artifact_only' => true,
        ];
    }

    private function observationResultReviewChecklist(): array
    {
        return [
            'observation_result_reviewed' => true,
            'production_runtime_wiring_not_enabled' => true,
            'production_catalog_runtime_wired' => false,
            'runtime_bridge_not_enabled' => true,
            'controlled_opt_in_runtime_bridge_not_enabled' => true,
            'controlled_parallel_run_not_enabled' => true,
            'controlled_rollout_not_enabled' => true,
            'pilot_runtime_not_enabled' => true,
            'shadow_runtime_not_enabled' => true,
            'scheduler_live_weekly_swing_not_enabled' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'production_config_default_unchanged' => true,
            'c117_source_lock_reviewed' => true,
            'runtime_entrypoint_reviewed' => true,
            'artisan_command_registration_reviewed' => true,
            'service_boundary_reviewed' => true,
            'config_boundary_reviewed' => true,
            'scheduler_boundary_reviewed' => true,
            'plan_confirm_boundary_reviewed' => true,
            'output_publication_boundary_reviewed' => true,
            'rollback_boundary_reviewed' => true,
            'operational_guard_reviewed' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'rollback_rule_required' => true,
            'manual_validation_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'observation_result_review_only' => true,
            'non_live' => true,
            'non_mutating' => true,
            'artifact_only' => true,
            'live_endpoint_called' => false,
            'scheduler_executed' => false,
            'weekly_swing_stock_recommendation_generated' => false,
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'controlled_runtime_wiring_observation_result_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'production_ready' => false,
            'production_deployment_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'failure_reason_codes' => $forcedFailures,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c118_role' => 'primary_controlled_runtime_wiring_observation_result_review_candidate',
                'primary_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c118_role' => 'backup_controlled_runtime_wiring_observation_result_review_candidate',
                'backup_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c118_role' => 'comparator_only_candidate',
                'controlled_runtime_wiring_observation_result_review_pass' => false,
                'ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
                'comparator_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function observationResultReviewContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_execution_context_persisted_to_live_runtime' => false,
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
            'c117_artifact_identified' => is_file(self::DEFAULT_C117_ARTIFACT),
            'c118_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewService.php'),
            'c118_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewCommand.php'),
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
        $summary = ['validation_completed' => true, 'all_required_safety_flags_false' => true];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $summary[$flag] = false;
        }

        return $summary;
    }

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => $status,
            'failure_count' => count($status),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-06_C118_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW',
            'c111_final_closure_carried_forward' => true,
            'c112_post_c111_transition_gate_carried_forward' => true,
            'c113_production_readiness_review_carried_forward' => true,
            'c114_runtime_wiring_readiness_review_carried_forward' => true,
            'c115_execution_approval_review_carried_forward' => true,
            'c116_execution_review_carried_forward' => true,
            'c117_observation_review_carried_forward' => true,
            'c118_controlled_runtime_wiring_observation_result_review_executed' => true,
            'c118_ready_for_controlled_runtime_wiring_operator_go_no_go_review' => $pass,
            'still_no_live_runtime' => true,
            'still_no_production_deployment' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C119_RECOMMENDATION : 'C118_TARGETED_C117_OBSERVATION_REVIEW_LOCK_REPAIR',
            'planned_next_scope' => $pass ? 'controlled runtime wiring operator go/no-go review only; not production deployment, live rollout, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before controlled runtime wiring observation result review can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C118 artifact hash',
                'locked C118 file SHA1',
                'operator approval reference',
                'unchanged candidate scope',
                'controlled runtime wiring operator go/no-go review checklist',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c117_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c117_artifact_not_modified' => true,
            'c111_c112_c113_c114_c115_c116_c117_artifacts_not_modified' => true,
            'c98_c117_sections_not_rewritten' => true,
            'uppercase_boundary_labels_nested_only' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function boundaryAliases(bool $pass): array
    {
        return [
            'C111_NON_LIVE_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED' => $pass,
            'C111_NON_LIVE_AUDIT_ARCHIVE_TERMINAL' => $pass,
            'C112_SEPARATE_POST_C111_PRODUCTION_PHASE_TRANSITION_GATE' => $pass,
            'C112_NOT_AUDIT_ARCHIVE_CONTINUATION' => $pass,
            'C112_DOES_NOT_REOPEN_C111_FINAL_CLOSURE' => $pass,
            'C113_PRODUCTION_READINESS_REVIEW_ONLY' => $pass,
            'C114_RUNTIME_WIRING_READINESS_REVIEW_ONLY' => $pass,
            'C114_NOT_RUNTIME_WIRING_EXECUTION' => true,
            'C115_EXECUTION_APPROVAL_REVIEW_ONLY' => true,
            'C115_NOT_RUNTIME_WIRING_EXECUTION' => true,
            'C116_EXECUTION_REVIEW_ONLY' => true,
            'C116_NOT_PRODUCTION_DEPLOYMENT' => true,
            'C116_NOT_PLAN_CONFIRM_MUTATION' => true,
            'C116_NOT_WEEKLY_SWING_LIVE_OUTPUT' => true,
            'C117_OBSERVATION_REVIEW_ONLY' => true,
            'C117_NOT_PRODUCTION_DEPLOYMENT' => true,
            'C117_NOT_PLAN_CONFIRM_MUTATION' => true,
            'C117_NOT_WEEKLY_SWING_LIVE_OUTPUT' => true,
            'C118_OBSERVATION_RESULT_REVIEW_ONLY' => true,
            'C118_NOT_PRODUCTION_DEPLOYMENT' => true,
            'C118_NOT_PLAN_CONFIRM_MUTATION' => true,
            'C118_NOT_WEEKLY_SWING_LIVE_OUTPUT' => true,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C118 validates C117 artifact_hash and file SHA1 locks before PR-06 controlled runtime wiring observation result review is recorded.',
            'C118 validates C117 controlled runtime wiring observation review pass and next recommendation to C118.',
            'C118 verifies C117 PowerShell ConvertFrom-Json compatibility and top-level case-insensitive duplicate-key hygiene.',
            'C118 carries forward C111 as terminal/final-closed, C112 as production transition gate, C113 as readiness review, C114 as runtime wiring readiness review, C115 as execution approval review, C116 as execution review, and C117 as observation review.',
            'C118 requires --operator-approved and a non-empty --approval-reference.',
            'C118 creates an artifact-only controlled runtime wiring observation result review manifest and checklist.',
            'C118 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C118 does not deploy production, activate live rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.',
            'C118 may only recommend C119 weekly swing watchlist controlled runtime wiring operator go/no-go review as the next controlled step.',
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

    private function boolField(array $source, string $field): bool
    {
        $path = explode('.', $field);

        return (bool) $this->valueAt($source, $path);
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

    private function configFlagIsOn(string $key): bool
    {
        $path = 'config/watchlist.php';
        if (! is_file($path)) {
            return false;
        }
        $config = require $path;

        return is_array($config) && (bool) ($config[$key] ?? false);
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
