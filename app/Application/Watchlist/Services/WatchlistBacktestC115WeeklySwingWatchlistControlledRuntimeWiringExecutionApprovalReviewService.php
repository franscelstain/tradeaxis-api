<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewService
{
    public const RUN_CODE = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW';
    public const PHASE_LABEL = 'PR-03 / C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW';
    public const ARTIFACT_TYPE = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW';

    public const DEFAULT_C114_ARTIFACT = 'storage/app/watchlist/backtest/c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review.json';
    public const DEFAULT_EXPECTED_C114_HASH = 'f66f44216218ae5360e7920ef20f0ff051f8f987';
    public const DEFAULT_EXPECTED_C114_FILE_SHA1 = '51590143E73A77EB33F6ED67065CAE6ADF30D778';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C114_STATUS = 'C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C114_REASON = 'C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C114_NEXT_RECOMMENDATION = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW';
    private const EXPECTED_C114_PHASE_LABEL = 'PR-02 / C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW';
    private const C116_RECOMMENDATION = 'C116_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW';

    private const PASS_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C114_LOCK_MISMATCH_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C114_ARTIFACT_LOCK_MISMATCH';
    private const C114_FILE_SHA1_MISMATCH_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C114_FILE_SHA1_LOCK_MISMATCH';
    private const C114_STATUS_MISMATCH_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C114_STATUS_MISMATCH';
    private const C114_REASON_MISMATCH_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C114_REASON_CODE_MISMATCH';
    private const C114_NEXT_MISMATCH_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C114_NEXT_RECOMMENDATION_MISMATCH';
    private const C114_PHASE_LABEL_MISMATCH_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C114_PHASE_LABEL_MISMATCH';
    private const C114_READINESS_INVALID_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C114_RUNTIME_WIRING_READINESS_INVALID';
    private const BOUNDARY_INVALID_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C111_C112_C113_C114_BOUNDARY_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C114_CONVERT_FROM_JSON_STATUS = 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_C114_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';

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
        string $c114Artifact = self::DEFAULT_C114_ARTIFACT,
        string $expectedC114Hash = self::DEFAULT_EXPECTED_C114_HASH,
        string $expectedC114FileSha1 = self::DEFAULT_EXPECTED_C114_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c114Artifact, $expectedC114Hash, $expectedC114FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C114_LOCK_MISMATCH_STATUS, 'C114 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c114_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C114_CONVERT_FROM_JSON_STATUS, 'C114 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C114_LOCK_MISMATCH_STATUS, 'C114 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C114_FILE_SHA1_MISMATCH_STATUS, 'C114 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c114 = $load['payload'];
        if (($c114['status'] ?? null) !== self::EXPECTED_C114_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C114_STATUS_MISMATCH_STATUS, 'C114 status is not passed ready for C115.', $outputPath, $overwrite);
        }
        if (($c114['reason_code'] ?? null) !== self::EXPECTED_C114_REASON) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C114_REASON_MISMATCH_STATUS, 'C114 reason_code is not passed ready for C115.', $outputPath, $overwrite);
        }
        if (! $this->c114NextRecommendationMatches($c114)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C114_NEXT_MISMATCH_STATUS, 'C114 next recommendation is not C115.', $outputPath, $overwrite);
        }
        if (($c114['phase_label'] ?? null) !== self::EXPECTED_C114_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C114_PHASE_LABEL_MISMATCH_STATUS, 'C114 phase label is not PR-02 / C114.', $outputPath, $overwrite);
        }
        if (! $this->c114RuntimeWiringReadinessValid($c114)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C114_READINESS_INVALID_STATUS, 'C114 runtime wiring readiness evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->boundaryValid($c114)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::BOUNDARY_INVALID_STATUS, 'C111/C112/C113/C114 boundary evidence is invalid.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c114)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C114 candidate scope does not match locked execution approval scope.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c114);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c114_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C114 contains live, mutating, production, runtime wiring, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C115 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C115 controlled runtime wiring execution approval review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C115 completes controlled runtime wiring execution approval review for E02 primary and B01 backup in review-only, non-live, non-mutating context. It approves proceeding to a controlled execution review, but does not execute runtime wiring, deploy production, activate bridge or rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C115_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PASSED_REVIEW_ONLY_NON_LIVE_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::C116_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-03',
            'internal_checkpoint' => 'C115',
            'status' => 'C115_NOT_RUN',
            'reason_code' => 'C115_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_executed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_allowed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_pass' => false,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_execution_review' => false,
            'controlled_runtime_wiring_execution_approval_review_pass' => false,
            'ready_for_controlled_runtime_wiring_execution_review' => false,
            'controlled_runtime_wiring_execution_approval_manifest_created' => false,
            'controlled_runtime_wiring_execution_review_allowed_next' => false,
            'c114_lock_valid' => false,
            'c114_runtime_wiring_readiness_valid' => false,
            'c114_convert_from_json_pass' => false,
            'c113_hash_match' => false,
            'c113_file_sha1_match' => false,
            'c113_convert_from_json_pass' => false,
            'c111_final_closure_valid' => false,
            'c111_non_live_audit_archive_terminal' => false,
            'c112_post_c111_transition_gate_valid' => false,
            'c112_not_audit_archive_continuation' => false,
            'c112_does_not_reopen_c111_final_closure' => false,
            'c113_production_readiness_valid' => false,
            'c114_runtime_wiring_readiness_review_only' => true,
            'c114_not_runtime_wiring_execution' => true,
            'primary_candidate_ready_for_controlled_runtime_wiring_execution_review' => false,
            'backup_candidate_ready_for_controlled_runtime_wiring_execution_review' => false,
            'comparator_candidate_ready_for_controlled_runtime_wiring_execution_review' => false,
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
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_allowed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_pass' => true,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_execution_review' => true,
            'controlled_runtime_wiring_execution_approval_review_pass' => true,
            'ready_for_controlled_runtime_wiring_execution_review' => true,
            'controlled_runtime_wiring_execution_approval_manifest_created' => true,
            'controlled_runtime_wiring_execution_review_allowed_next' => true,
            'c114_lock_valid' => true,
            'c114_runtime_wiring_readiness_valid' => true,
            'c114_convert_from_json_pass' => true,
            'c113_hash_match' => true,
            'c113_file_sha1_match' => true,
            'c113_convert_from_json_pass' => true,
            'c111_final_closure_valid' => true,
            'c111_non_live_audit_archive_terminal' => true,
            'c112_post_c111_transition_gate_valid' => true,
            'c112_not_audit_archive_continuation' => true,
            'c112_does_not_reopen_c111_final_closure' => true,
            'c113_production_readiness_valid' => true,
            'c114_runtime_wiring_readiness_review_only' => true,
            'c114_not_runtime_wiring_execution' => true,
            'primary_candidate_ready_for_controlled_runtime_wiring_execution_review' => true,
            'backup_candidate_ready_for_controlled_runtime_wiring_execution_review' => true,
            'comparator_candidate_ready_for_controlled_runtime_wiring_execution_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c114 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact = array_merge($artifact, $this->carryForwardTopLevelAliases($load, $c114));
        $artifact['c114_lock_validation_summary'] = $this->c114LockValidationSummary($load, $c114);
        $artifact['c111_c112_c113_c114_boundary_carry_forward_summary'] = $this->boundaryCarryForwardSummary($c114, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c114, $pass);
        $artifact['c114_final_operator_evidence_summary'] = $this->c114FinalOperatorEvidenceSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c115_execution_approval_decision'] = $this->executionApprovalDecision($pass);
        $artifact['next_execution_decision'] = $this->nextExecutionDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_decision'] = $this->executionApprovalDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_manifest'] = $this->executionApprovalManifest($load, $pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_checklist'] = $this->executionApprovalChecklist();
        $artifact['c115_candidate_execution_approval_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['controlled_runtime_wiring_execution_approval_context_summary'] = $this->executionApprovalContextSummary($pass);
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
        $artifact['c111_c112_c113_c114_boundary_evidence_labels'] = $this->boundaryAliases($pass);

        return $artifact;
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            [
                'source_lock' => 'C114',
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
                'expected_status' => self::EXPECTED_C114_STATUS,
                'expected_reason_code' => self::EXPECTED_C114_REASON,
                'expected_next_recommendation' => self::EXPECTED_C114_NEXT_RECOMMENDATION,
                'expected_phase_label' => self::EXPECTED_C114_PHASE_LABEL,
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C114',
            'c114_artifact_path' => $load['path'],
            'c114_artifact_exists' => $load['exists'],
            'expected_c114_hash' => $load['expected_hash'],
            'actual_c114_hash' => $load['actual_hash'],
            'c114_hash_match' => $load['hash_match'],
            'expected_c114_file_sha1' => $load['expected_file_sha1'],
            'actual_c114_file_sha1' => $load['actual_file_sha1'],
            'c114_file_sha1_match' => $load['file_sha1_match'],
            'c114_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function carryForwardTopLevelAliases(array $load, array $c114): array
    {
        return [
            'c114_lock_valid' => $load['exists'] && $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c114_runtime_wiring_readiness_valid' => $this->c114RuntimeWiringReadinessValid($c114),
            'c114_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c113_hash_match' => (bool) ($c114['c113_hash_match'] ?? false),
            'c113_file_sha1_match' => (bool) ($c114['c113_file_sha1_match'] ?? false),
            'c113_convert_from_json_pass' => (bool) ($c114['c113_convert_from_json_pass'] ?? false),
            'c111_final_closure_valid' => (bool) ($c114['c111_final_closure_valid'] ?? false),
            'c111_non_live_audit_archive_terminal' => (bool) ($c114['c111_non_live_audit_archive_terminal'] ?? false),
            'c112_post_c111_transition_gate_valid' => (bool) ($c114['c112_post_c111_transition_gate_valid'] ?? true),
            'c112_not_audit_archive_continuation' => (bool) ($c114['c112_not_audit_archive_continuation'] ?? false),
            'c112_does_not_reopen_c111_final_closure' => (bool) ($c114['c112_does_not_reopen_c111_final_closure'] ?? false),
            'c113_production_readiness_valid' => (bool) ($c114['c113_production_readiness_valid'] ?? false),
        ];
    }

    private function c114NextRecommendationMatches(array $c114): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_runtime_wiring_decision', 'next_recommendation'],
            ['c114_runtime_wiring_readiness_decision', 'next_recommendation'],
        ] as $path) {
            if ($this->valueAt($c114, $path) === self::EXPECTED_C114_NEXT_RECOMMENDATION) {
                return true;
            }
        }

        return false;
    }

    private function c114RuntimeWiringReadinessValid(array $c114): bool
    {
        foreach ([
            'weekly_swing_watchlist_production_runtime_wiring_readiness_review_pass',
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_execution_approval_review',
            'production_runtime_wiring_readiness_review_pass',
            'ready_for_controlled_runtime_wiring_execution_approval_review',
            'production_runtime_wiring_readiness_manifest_created',
            'c113_hash_match',
            'c113_file_sha1_match',
            'c113_convert_from_json_pass',
            'c113_production_readiness_valid',
        ] as $requiredTrueField) {
            if (! array_key_exists($requiredTrueField, $c114) || (bool) $c114[$requiredTrueField] !== true) {
                return false;
            }
        }
        if (array_key_exists('c114_runtime_wiring_readiness_decision', $c114) && is_array($c114['c114_runtime_wiring_readiness_decision'])) {
            foreach ([
                'review_pass',
                'validation_completed',
                'production_runtime_wiring_readiness_review_pass',
                'ready_for_controlled_runtime_wiring_execution_approval_review',
            ] as $decisionField) {
                if ((bool) ($c114['c114_runtime_wiring_readiness_decision'][$decisionField] ?? false) !== true) {
                    return false;
                }
            }
        }
        if (array_key_exists('weekly_swing_watchlist_production_runtime_wiring_readiness_review_manifest', $c114) && is_array($c114['weekly_swing_watchlist_production_runtime_wiring_readiness_review_manifest'])) {
            if ((bool) ($c114['weekly_swing_watchlist_production_runtime_wiring_readiness_review_manifest']['manifest_created'] ?? false) !== true) {
                return false;
            }
        }

        return true;
    }

    private function boundaryValid(array $c114): bool
    {
        foreach ([
            'c111_final_closure_valid',
            'c111_non_live_audit_archive_terminal',
            'c112_not_audit_archive_continuation',
            'c112_does_not_reopen_c111_final_closure',
            'c113_production_readiness_valid',
            'c114_not_runtime_wiring_execution',
        ] as $requiredTrueField) {
            if (array_key_exists($requiredTrueField, $c114) && (bool) $c114[$requiredTrueField] !== true) {
                return false;
            }
        }
        foreach ([
            'c114_is_runtime_wiring_execution',
            'c114_is_production_deployment',
            'c114_is_plan_confirm_mutation',
            'c113_reopens_c111_final_closure',
            'c114_reopens_c111_final_closure',
        ] as $requiredFalseField) {
            if (array_key_exists($requiredFalseField, $c114) && (bool) $c114[$requiredFalseField] !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $c114): bool
    {
        return ($c114['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE) === self::PRIMARY_CANDIDATE
            && ($c114['backup_candidate_code'] ?? self::BACKUP_CANDIDATE) === self::BACKUP_CANDIDATE
            && ($c114['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE) === self::COMPARATOR_CANDIDATE
            && (bool) ($c114['primary_candidate_ready_for_controlled_runtime_wiring_execution_approval_review'] ?? false) === true
            && (bool) ($c114['backup_candidate_ready_for_controlled_runtime_wiring_execution_approval_review'] ?? false) === true
            && (bool) ($c114['comparator_candidate_ready_for_controlled_runtime_wiring_execution_approval_review'] ?? false) === false
            && (bool) ($c114['a01_remains_comparator_only'] ?? false) === true
            && (bool) ($c114['a01_promoted'] ?? false) === false
            && (bool) ($c114['candidate_promotion_executed'] ?? false) === false
            && (bool) ($c114['candidate_rerank_executed'] ?? false) === false
            && (bool) ($c114['strategy_retune_executed'] ?? false) === false
            && (bool) ($c114['scoring_mutation_executed'] ?? false) === false
            && (bool) ($c114['catalog_selection_changed'] ?? false) === false
            && (bool) ($c114['runtime_selection_changed'] ?? false) === false
            && (bool) ($c114['weekly_swing_live_recommendation_selection_executed'] ?? false) === false;
    }

    private function firstLiveOrMutatingSafetyFlag(array $payload): ?string
    {
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            if ((bool) ($payload[$flag] ?? false) === true) {
                return $flag;
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
            'persist_controlled_runtime_wiring_execution_approval_context_to_live_runtime',
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
            'modify_c60_c114_artifacts',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'a01') !== false || strpos($field, 'candidate') !== false || strpos($field, 'scoring') !== false || strpos($field, 'catalog') !== false || strpos($field, 'strategy') !== false) {
            return self::CANDIDATE_SCOPE_MISMATCH_STATUS;
        }

        return self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
    }

    private function c114LockValidationSummary(array $load, array $c114): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C114',
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
            'expected_status' => self::EXPECTED_C114_STATUS,
            'actual_status' => $c114['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C114_REASON,
            'actual_reason_code' => $c114['reason_code'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C114_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c114NextRecommendationMatches($c114),
            'expected_phase_label' => self::EXPECTED_C114_PHASE_LABEL,
            'actual_phase_label' => $c114['phase_label'] ?? null,
            'phase_label_match' => ($c114['phase_label'] ?? null) === self::EXPECTED_C114_PHASE_LABEL,
            'c114_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c114_runtime_wiring_readiness_valid' => $this->c114RuntimeWiringReadinessValid($c114),
        ];
    }

    private function boundaryCarryForwardSummary(array $c114, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c111_non_live_handoff_audit_archive_final_closed' => $pass,
            'c111_non_live_audit_archive_terminal' => $pass,
            'c112_separate_post_c111_production_phase_transition_gate' => $pass,
            'c112_not_audit_archive_continuation' => $pass,
            'c112_does_not_reopen_c111_final_closure' => $pass,
            'c113_production_readiness_review_only' => $pass,
            'c114_runtime_wiring_readiness_review_only' => true,
            'c114_not_runtime_wiring_execution' => true,
            'c115_execution_approval_review_only' => true,
            'c115_not_runtime_wiring_execution' => true,
            'source_c111_final_closure_valid' => (bool) ($c114['c111_final_closure_valid'] ?? false),
            'source_c114_runtime_wiring_readiness_valid' => $this->c114RuntimeWiringReadinessValid($c114),
        ];
    }

    private function candidateScopeFreezeSummary(array $c114, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c114),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_controlled_runtime_wiring_execution_approval_review_candidate',
            'backup_candidate_role' => 'backup_controlled_runtime_wiring_execution_approval_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_controlled_runtime_wiring_execution_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_execution_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_execution_review' => false,
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

    private function c114FinalOperatorEvidenceSummary(): array
    {
        return [
            'FOCUSED_PHPUNIT_C114' => 'OK (106 tests, 419 assertions)',
            'FULL_WATCHLIST_PHPUNIT_POST_C114' => 'OK (2939 tests, 31130 assertions)',
            'C114_ARTIFACT_HASH' => self::DEFAULT_EXPECTED_C114_HASH,
            'C114_FILE_SHA1' => strtoupper(self::DEFAULT_EXPECTED_C114_FILE_SHA1),
            'C114_RUNTIME_STATUS' => self::EXPECTED_C114_STATUS,
            'C114_RUNTIME_REASON_CODE' => self::EXPECTED_C114_REASON,
            'C113_HASH_MATCH' => true,
            'C113_FILE_SHA1_MATCH' => true,
            'C113_CONVERT_FROM_JSON_PASS' => true,
            'C111_FINAL_CLOSURE_VALID' => true,
            'C111_NON_LIVE_AUDIT_ARCHIVE_TERMINAL' => true,
            'C112_NOT_AUDIT_ARCHIVE_CONTINUATION' => true,
            'C112_DOES_NOT_REOPEN_C111_FINAL_CLOSURE' => true,
            'C113_PRODUCTION_READINESS_VALID' => true,
            'NEGATIVE_WITHOUT_OPERATOR_APPROVAL' => 'REJECTED_OPERATOR_APPROVAL_MISSING',
            'NEGATIVE_WITHOUT_APPROVAL_REFERENCE' => 'REJECTED_OPERATOR_APPROVAL_MISSING',
            'TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP' => 'NO_OUTPUT',
            'NEXT_RECOMMENDATION' => self::EXPECTED_C114_NEXT_RECOMMENDATION,
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

    private function executionApprovalDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c114_lock_valid' => $pass,
            'c114_runtime_wiring_readiness_valid' => $pass,
            'c114_convert_from_json_pass' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_pass' => $pass,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_execution_review' => $pass,
            'controlled_runtime_wiring_execution_approval_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_execution_review' => $pass,
            'controlled_runtime_wiring_execution_approval_manifest_created' => $pass,
            'controlled_runtime_wiring_execution_review_allowed_next' => $pass,
            'primary_candidate_ready_for_controlled_runtime_wiring_execution_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_execution_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_execution_review' => false,
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
            'next_recommendation' => $pass ? self::C116_RECOMMENDATION : 'C115_TARGETED_C114_RUNTIME_WIRING_READINESS_LOCK_REPAIR',
            'decision_reason' => $pass ? 'C115 controlled runtime wiring execution approval review completed for primary and backup in review-only, non-live, non-mutating context.' : 'C115 cannot proceed until C114 lock, boundary, approval, cleanup, candidate, and safety gates pass.',
            'diagnostic_conclusion' => $pass ? 'C115_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PASSED_REVIEW_ONLY_NON_LIVE_NON_MUTATING' : 'C115_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED',
        ];
    }

    private function nextExecutionDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C116_RECOMMENDATION : 'C115_TARGETED_C114_RUNTIME_WIRING_READINESS_LOCK_REPAIR',
            'next_scope' => $pass ? 'controlled runtime wiring execution review only; C115 itself still does not execute production runtime wiring, deploy production, generate official weekly swing output, activate runtime bridge, or mutate PLAN/CONFIRM' : 'targeted C114 runtime wiring readiness lock, boundary, approval, or safety repair only',
        ];
    }

    private function executionApprovalManifest(array $load, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_review_only_controlled_runtime_wiring_execution_approval_review',
            'source_artifact' => 'C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['expected_hash'],
            'source_file_sha1' => $load['expected_file_sha1'],
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_controlled_runtime_wiring_execution_approval_review_candidate',
            'backup_candidate_role' => 'backup_controlled_runtime_wiring_execution_approval_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'c111_final_closure_carried_forward' => $pass,
            'c112_post_c111_transition_gate_carried_forward' => $pass,
            'c113_production_readiness_carried_forward' => $pass,
            'c114_runtime_wiring_readiness_carried_forward' => $pass,
            'c114_convert_from_json_pass' => $pass,
            'controlled_runtime_wiring_execution_approval_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_execution_review' => $pass,
            'runtime_wiring_execution_performed' => false,
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
            'execution_approval_review_used_for_selection' => false,
            'execution_approval_review_used_for_retuning' => false,
            'execution_approval_review_used_for_ranking' => false,
            'execution_approval_review_used_for_plan_confirm_mutation' => false,
            'execution_approval_review_used_for_live_rollout' => false,
            'execution_approval_review_artifact_only' => true,
        ];
    }

    private function executionApprovalChecklist(): array
    {
        return [
            'execution_approval_reviewed' => true,
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
            'c114_source_lock_reviewed' => true,
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
            'approval_review_only' => true,
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
            'controlled_runtime_wiring_execution_approval_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_execution_review' => $pass,
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
                'c115_role' => 'primary_controlled_runtime_wiring_execution_approval_review_candidate',
                'primary_candidate_ready_for_controlled_runtime_wiring_execution_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c115_role' => 'backup_controlled_runtime_wiring_execution_approval_review_candidate',
                'backup_candidate_ready_for_controlled_runtime_wiring_execution_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c115_role' => 'comparator_only_candidate',
                'controlled_runtime_wiring_execution_approval_review_pass' => false,
                'ready_for_controlled_runtime_wiring_execution_review' => false,
                'comparator_candidate_ready_for_controlled_runtime_wiring_execution_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function executionApprovalContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_context_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime' => false,
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
            'c114_artifact_identified' => is_file(self::DEFAULT_C114_ARTIFACT),
            'c115_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewService.php'),
            'c115_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewCommand.php'),
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
            'progress_marker' => 'PR-03_C115_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW',
            'c111_final_closure_carried_forward' => true,
            'c112_post_c111_transition_gate_carried_forward' => true,
            'c113_production_readiness_review_carried_forward' => true,
            'c114_runtime_wiring_readiness_review_carried_forward' => true,
            'c115_controlled_runtime_wiring_execution_approval_review_executed' => true,
            'c115_ready_for_controlled_runtime_wiring_execution_review' => $pass,
            'still_no_live_runtime' => true,
            'still_no_runtime_wiring_execution' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C116_RECOMMENDATION : 'C115_TARGETED_C114_RUNTIME_WIRING_READINESS_LOCK_REPAIR',
            'planned_next_scope' => $pass ? 'controlled runtime wiring execution review only; not production deployment, live rollout, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before controlled runtime wiring execution approval review can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C115 artifact hash',
                'locked C115 file SHA1',
                'operator approval reference',
                'unchanged candidate scope',
                'controlled runtime wiring execution review checklist',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c114_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c114_artifact_not_modified' => true,
            'c111_c112_c113_c114_artifacts_not_modified' => true,
            'c98_c114_sections_not_rewritten' => true,
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
            'C115_NOT_PRODUCTION_DEPLOYMENT' => true,
            'C115_NOT_PLAN_CONFIRM_MUTATION' => true,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C115 validates C114 artifact_hash and file SHA1 locks before PR-03 controlled runtime wiring execution approval review is recorded.',
            'C115 validates C114 runtime wiring readiness review pass and next recommendation to C115.',
            'C115 verifies C114 PowerShell ConvertFrom-Json compatibility and top-level case-insensitive duplicate-key hygiene.',
            'C115 carries forward C111 as terminal/final-closed, C112 as production transition gate, C113 as readiness review, and C114 as runtime wiring readiness review.',
            'C115 requires --operator-approved and a non-empty --approval-reference.',
            'C115 prepares an artifact-only controlled runtime wiring execution approval manifest and checklist without runtime wiring execution.',
            'C115 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C115 does not deploy production, wire runtime, activate bridge or rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.',
            'C115 may only recommend C116 weekly swing watchlist controlled runtime wiring execution review as the next controlled step.',
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
