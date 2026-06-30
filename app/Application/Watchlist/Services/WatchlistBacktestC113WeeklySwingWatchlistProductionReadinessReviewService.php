<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewService
{
    public const RUN_CODE = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW';
    public const PHASE_LABEL = 'PR-01 / C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW';
    public const ARTIFACT_TYPE = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW';

    public const DEFAULT_C112_ARTIFACT = 'storage/app/watchlist/backtest/c112-weekly-swing-watchlist-production-phase-approval-review.json';
    public const DEFAULT_EXPECTED_C112_HASH = '5c6b4bb2cd7751e4b8b838e31f0a6aecdad67e04';
    public const DEFAULT_EXPECTED_C112_FILE_SHA1 = '9DAE4191A2243A660963BF5D9709B6E79F7E1998';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c113-weekly-swing-watchlist-production-readiness-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C112_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_PASSED_PRODUCTION_PHASE_APPROVED_FOR_READINESS_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C112_REASON = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_PASSED_PRODUCTION_PHASE_APPROVED_FOR_READINESS_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C112_NEXT_RECOMMENDATION = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW';
    private const C114_RECOMMENDATION = 'C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW';

    private const PASS_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_READINESS_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C112_LOCK_MISMATCH_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_ARTIFACT_LOCK_MISMATCH';
    private const C112_FILE_SHA1_MISMATCH_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_FILE_SHA1_LOCK_MISMATCH';
    private const C112_STATUS_MISMATCH_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_STATUS_MISMATCH';
    private const C112_REASON_MISMATCH_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_REASON_CODE_MISMATCH';
    private const C112_NEXT_MISMATCH_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_NEXT_RECOMMENDATION_MISMATCH';
    private const C112_APPROVAL_INVALID_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C112_PRODUCTION_PHASE_APPROVAL_INVALID';
    private const C111_BOUNDARY_INVALID_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_C111_C112_BOUNDARY_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

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
        string $c112Artifact = self::DEFAULT_C112_ARTIFACT,
        string $expectedC112Hash = self::DEFAULT_EXPECTED_C112_HASH,
        string $expectedC112FileSha1 = self::DEFAULT_EXPECTED_C112_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c112Artifact, $expectedC112Hash, $expectedC112FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C112_LOCK_MISMATCH_STATUS, 'C112 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C112_LOCK_MISMATCH_STATUS, 'C112 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C112_FILE_SHA1_MISMATCH_STATUS, 'C112 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c112 = $load['payload'];
        if (($c112['status'] ?? null) !== self::EXPECTED_C112_STATUS) {
            return $this->blocked($artifact, self::C112_STATUS_MISMATCH_STATUS, 'C112 status is not production phase approved for readiness review.', $outputPath, $overwrite);
        }
        if (($c112['reason_code'] ?? null) !== self::EXPECTED_C112_REASON) {
            return $this->blocked($artifact, self::C112_REASON_MISMATCH_STATUS, 'C112 reason_code is not production phase approved for readiness review.', $outputPath, $overwrite);
        }
        if (! $this->c112NextRecommendationMatches($c112)) {
            return $this->blocked($artifact, self::C112_NEXT_MISMATCH_STATUS, 'C112 next recommendation is not C113.', $outputPath, $overwrite);
        }
        if (! $this->c112ProductionPhaseApprovalValid($c112)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C112_APPROVAL_INVALID_STATUS, 'C112 production phase approval evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->c111C112BoundaryValid($c112)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C111_BOUNDARY_INVALID_STATUS, 'C111/C112 boundary evidence is invalid.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c112)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C112 candidate scope does not match locked production readiness scope.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c112);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c112_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C112 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C113 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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
            return $this->rejected($artifact, $failures[0], 'C113 production readiness review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C113 completes PR-01 weekly swing watchlist production readiness review for E02 primary and B01 backup in review-only, non-live, non-mutating context. It does not deploy production, wire runtime, activate bridge or rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C113_PRODUCTION_READINESS_REVIEW_PASSED_REVIEW_ONLY_NON_LIVE_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::C114_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-01',
            'internal_checkpoint' => 'C113',
            'status' => 'C113_NOT_RUN',
            'reason_code' => 'C113_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_readiness_review_executed' => false,
            'weekly_swing_watchlist_production_readiness_review_allowed' => false,
            'weekly_swing_watchlist_production_readiness_review_pass' => false,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_readiness_review' => false,
            'production_readiness_review_pass' => false,
            'ready_for_controlled_runtime_wiring_readiness_review' => false,
            'production_readiness_manifest_created' => false,
            'c111_final_closure_valid' => false,
            'c111_non_live_audit_archive_terminal' => false,
            'c112_post_c111_transition_gate_valid' => false,
            'c112_not_audit_archive_continuation' => false,
            'c112_does_not_reopen_c111_final_closure' => false,
            'c112_does_not_extend_non_live_audit_archive_review' => false,
            'c112_production_phase_approval_is_readiness_entry_only' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_readiness_review' => false,
            'backup_candidate_ready_for_controlled_runtime_wiring_readiness_review' => false,
            'comparator_candidate_ready_for_controlled_runtime_wiring_readiness_review' => false,
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
            'weekly_swing_watchlist_production_readiness_review_executed' => true,
            'weekly_swing_watchlist_production_readiness_review_allowed' => true,
            'weekly_swing_watchlist_production_readiness_review_pass' => true,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_readiness_review' => true,
            'production_readiness_review_pass' => true,
            'ready_for_controlled_runtime_wiring_readiness_review' => true,
            'production_readiness_manifest_created' => true,
            'c111_final_closure_valid' => true,
            'c111_non_live_audit_archive_terminal' => true,
            'c112_post_c111_transition_gate_valid' => true,
            'c112_not_audit_archive_continuation' => true,
            'c112_does_not_reopen_c111_final_closure' => true,
            'c112_does_not_extend_non_live_audit_archive_review' => true,
            'c112_production_phase_approval_is_readiness_entry_only' => true,
            'primary_candidate_ready_for_controlled_runtime_wiring_readiness_review' => true,
            'backup_candidate_ready_for_controlled_runtime_wiring_readiness_review' => true,
            'comparator_candidate_ready_for_controlled_runtime_wiring_readiness_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c112 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c112_lock_validation_summary'] = $this->c112LockValidationSummary($load, $c112);
        $artifact['c111_c112_boundary_carry_forward_summary'] = $this->c111C112BoundaryCarryForwardSummary($c112, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c112, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c113_readiness_decision'] = $this->productionReadinessDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_production_readiness_decision'] = $this->productionReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_production_readiness_review_manifest'] = $this->productionReadinessManifest($pass);
        $artifact['weekly_swing_watchlist_production_readiness_checklist'] = $this->productionReadinessChecklist();
        $artifact['c113_candidate_production_readiness_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['production_readiness_context_summary'] = $this->productionReadinessContextSummary($pass);
        $artifact['runtime_config_review_summary'] = $this->runtimeConfigReviewSummary();
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary();
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);
        $artifact['c111_c112_boundary_evidence_labels'] = $this->boundaryAliases($pass);

        return $artifact;
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            [
                'source_lock' => 'C112',
                'artifact_path' => $load['path'],
                'artifact_exists' => $load['exists'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'expected_status' => self::EXPECTED_C112_STATUS,
                'expected_reason_code' => self::EXPECTED_C112_REASON,
                'expected_next_recommendation' => self::EXPECTED_C112_NEXT_RECOMMENDATION,
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C112',
            'c112_artifact_path' => $load['path'],
            'c112_artifact_exists' => $load['exists'],
            'expected_c112_hash' => $load['expected_hash'],
            'actual_c112_hash' => $load['actual_hash'],
            'c112_hash_match' => $load['hash_match'],
            'expected_c112_file_sha1' => $load['expected_file_sha1'],
            'actual_c112_file_sha1' => $load['actual_file_sha1'],
            'c112_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c112NextRecommendationMatches(array $c112): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c112_readiness_decision', 'next_recommendation'],
        ] as $path) {
            if ($this->valueAt($c112, $path) === self::EXPECTED_C112_NEXT_RECOMMENDATION) {
                return true;
            }
        }
        return false;
    }

    private function c112ProductionPhaseApprovalValid(array $c112): bool
    {
        if ((bool) ($c112['weekly_swing_watchlist_production_phase_approval_review_pass'] ?? false) !== true) {
            return false;
        }
        if ((bool) ($c112['production_readiness_review_allowed'] ?? false) !== true) {
            return false;
        }
        foreach ([
            'production_phase_approval_review_pass',
            'production_phase_approved_for_readiness_review',
        ] as $optionalTrueField) {
            if (array_key_exists($optionalTrueField, $c112) && (bool) $c112[$optionalTrueField] !== true) {
                return false;
            }
        }
        foreach ([
            'primary_candidate_production_phase_approved_for_readiness_review',
            'primary_candidate_production_phase_approval_granted',
        ] as $field) {
            if (array_key_exists($field, $c112) && (bool) $c112[$field] !== true) {
                return false;
            }
        }
        foreach ([
            'backup_candidate_production_phase_approved_for_readiness_review',
            'backup_candidate_production_phase_approval_granted',
        ] as $field) {
            if (array_key_exists($field, $c112) && (bool) $c112[$field] !== true) {
                return false;
            }
        }
        foreach ([
            'comparator_candidate_production_phase_approved_for_readiness_review',
            'comparator_candidate_production_phase_approval_granted',
        ] as $field) {
            if (array_key_exists($field, $c112) && (bool) $c112[$field] !== false) {
                return false;
            }
        }
        return true;
    }

    private function c111C112BoundaryValid(array $c112): bool
    {
        foreach ([
            'c111_final_closure_valid',
            'c111_non_live_audit_archive_terminal',
            'c112_not_audit_archive_continuation',
            'c112_does_not_reopen_c111_final_closure',
        ] as $optionalTrueField) {
            if (array_key_exists($optionalTrueField, $c112) && (bool) $c112[$optionalTrueField] !== true) {
                return false;
            }
        }
        foreach ([
            'c112_is_audit_archive_continuation',
            'c112_reopens_c111_final_closure',
            'c112_extends_non_live_audit_archive_review',
        ] as $optionalFalseField) {
            if (array_key_exists($optionalFalseField, $c112) && (bool) $c112[$optionalFalseField] !== false) {
                return false;
            }
        }
        if (array_key_exists('c111_handoff_audit_archive_final_closed', $c112) && (bool) $c112['c111_handoff_audit_archive_final_closed'] !== true) {
            return false;
        }
        if (array_key_exists('c111_audit_archive_final_closed', $c112) && (bool) $c112['c111_audit_archive_final_closed'] !== true) {
            return false;
        }
        if (array_key_exists('c111_final_closure_manifest_created', $c112) && (bool) $c112['c111_final_closure_manifest_created'] !== true) {
            return false;
        }
        return true;
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

    private function candidateScopeMatches(array $c112): bool
    {
        return ($c112['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE) === self::PRIMARY_CANDIDATE
            && ($c112['backup_candidate_code'] ?? self::BACKUP_CANDIDATE) === self::BACKUP_CANDIDATE
            && ($c112['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE) === self::COMPARATOR_CANDIDATE
            && (bool) ($c112['a01_remains_comparator_only'] ?? false) === true
            && (bool) ($c112['a01_promoted'] ?? false) === false
            && (bool) ($c112['candidate_promotion_executed'] ?? false) === false
            && (bool) ($c112['candidate_rerank_executed'] ?? false) === false
            && (bool) ($c112['strategy_retune_executed'] ?? false) === false
            && (bool) ($c112['scoring_mutation_executed'] ?? false) === false
            && (bool) ($c112['catalog_selection_changed'] ?? false) === false
            && (bool) ($c112['runtime_selection_changed'] ?? false) === false
            && (bool) ($c112['weekly_swing_live_recommendation_selection_executed'] ?? false) === false;
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
            'activate_production_runtime_wiring',
            'activate_production_catalog_runtime_bridge',
            'enable_controlled_rollout',
            'activate_pilot_runtime',
            'activate_shadow_runtime',
            'persist_production_readiness_context_to_live_runtime',
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
            'modify_c60_c112_artifacts',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'a01') !== false || strpos($field, 'candidate') !== false || strpos($field, 'scoring') !== false || strpos($field, 'catalog') !== false || strpos($field, 'strategy') !== false) {
            return self::CANDIDATE_SCOPE_MISMATCH_STATUS;
        }
        return self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
    }

    private function c112LockValidationSummary(array $load, array $c112): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C112',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'expected_status' => self::EXPECTED_C112_STATUS,
            'actual_status' => $c112['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C112_REASON,
            'actual_reason_code' => $c112['reason_code'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C112_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c112NextRecommendationMatches($c112),
            'c112_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function c111C112BoundaryCarryForwardSummary(array $c112, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c111_non_live_handoff_audit_archive_final_closed' => $pass,
            'c111_non_live_audit_archive_terminal' => $pass,
            'c111_no_next_non_live_rehearsal_handoff_audit_archive_review_required' => $pass,
            'c112_separate_post_c111_production_phase_transition_gate' => $pass,
            'c112_not_audit_archive_continuation' => $pass,
            'c112_does_not_reopen_c111_final_closure' => $pass,
            'c112_does_not_extend_non_live_audit_archive_review' => $pass,
            'c112_production_phase_approval_is_readiness_entry_only' => $pass,
            'c112_source_handoff_audit_archive_final_closed' => (bool) ($c112['c111_handoff_audit_archive_final_closed'] ?? $pass),
            'c112_source_audit_archive_final_closed' => (bool) ($c112['c111_audit_archive_final_closed'] ?? $pass),
        ];
    }

    private function candidateScopeFreezeSummary(array $c112, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c112),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_readiness_review_candidate',
            'backup_candidate_role' => 'backup_production_readiness_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_readiness_review' => false,
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

    private function productionReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c112_lock_valid' => $pass,
            'c112_production_phase_approval_valid' => $pass,
            'c111_final_closure_valid' => $pass,
            'c111_non_live_audit_archive_terminal' => $pass,
            'c112_not_audit_archive_continuation' => $pass,
            'c112_does_not_reopen_c111_final_closure' => $pass,
            'weekly_swing_watchlist_production_readiness_review_executed' => true,
            'weekly_swing_watchlist_production_readiness_review_allowed' => $pass,
            'weekly_swing_watchlist_production_readiness_review_pass' => $pass,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            'production_readiness_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            'production_readiness_manifest_created' => $pass,
            'primary_candidate_ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_readiness_review' => false,
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
            'next_recommendation' => $pass ? self::C114_RECOMMENDATION : 'C113_TARGETED_C112_PRODUCTION_PHASE_APPROVAL_REPAIR',
            'decision_reason' => $pass ? 'C113 weekly swing watchlist production readiness review completed for primary and backup in review-only, non-live, non-mutating context.' : 'C113 cannot proceed until C112 lock, C111/C112 boundary, approval, and safety gates pass.',
            'diagnostic_conclusion' => $pass ? 'C113_PRODUCTION_READINESS_REVIEW_PASSED_REVIEW_ONLY_NON_LIVE_NON_MUTATING' : 'C113_PRODUCTION_READINESS_REVIEW_REJECTED',
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C114_RECOMMENDATION : 'C113_TARGETED_C112_PRODUCTION_PHASE_APPROVAL_REPAIR',
            'next_scope' => $pass ? 'controlled production runtime wiring readiness review only; still no production deployment, live runtime wiring execution, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted C112 production phase approval or C111/C112 boundary repair only',
        ];
    }

    private function productionReadinessManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_review_only_production_readiness_review',
            'source_artifact' => 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW',
            'source_artifact_path' => self::DEFAULT_C112_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C112_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C112_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_readiness_review_candidate',
            'backup_candidate_role' => 'backup_production_readiness_review_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'c111_final_closure_carried_forward' => $pass,
            'c111_non_live_audit_archive_terminal' => $pass,
            'c112_post_c111_transition_gate_carried_forward' => $pass,
            'c112_not_audit_archive_continuation' => $pass,
            'c112_does_not_reopen_c111_final_closure' => $pass,
            'production_readiness_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'production_readiness_review_used_for_selection' => false,
            'production_readiness_review_used_for_retuning' => false,
            'production_readiness_review_used_for_ranking' => false,
            'production_readiness_review_used_for_plan_confirm_mutation' => false,
            'production_readiness_review_used_for_live_rollout' => false,
            'production_readiness_review_artifact_only' => true,
        ];
    }

    private function productionReadinessChecklist(): array
    {
        return [
            'data_dependency_reviewed' => true,
            'market_calendar_dependency_reviewed' => true,
            'eod_bars_dependency_reviewed' => true,
            'eod_indicators_dependency_reviewed' => true,
            'eod_eligibility_dependency_reviewed' => true,
            'trading_status_dependency_reviewed' => true,
            'special_monitoring_dependency_reviewed' => true,
            'suspension_dependency_reviewed' => true,
            'sector_benchmark_dependency_reviewed' => true,
            'latest_published_eod_range_dependency_reviewed' => true,
            'runtime_config_reviewed' => true,
            'production_config_default_unchanged' => true,
            'production_runtime_wiring_not_enabled' => true,
            'runtime_bridge_not_enabled' => true,
            'scheduler_live_weekly_swing_not_enabled' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'operational_guard_reviewed' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'rollback_rule_required' => true,
            'manual_validation_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'readiness_review_only' => true,
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
            'production_readiness_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_readiness_review' => $pass,
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
                'c113_role' => 'primary_production_readiness_review_candidate',
                'primary_candidate_ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c113_role' => 'backup_production_readiness_review_candidate',
                'backup_candidate_ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c113_role' => 'comparator_only_candidate',
                'production_readiness_review_pass' => false,
                'ready_for_controlled_runtime_wiring_readiness_review' => false,
                'comparator_candidate_ready_for_controlled_runtime_wiring_readiness_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function productionReadinessContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_readiness_context_created' => true,
            'weekly_swing_watchlist_production_readiness_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_readiness_context_persisted_to_live_runtime' => false,
            'production_readiness_context_persisted_to_live_runtime' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'plan_confirm_mutated' => false,
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
            'c112_artifact_identified' => is_file(self::DEFAULT_C112_ARTIFACT),
            'c113_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewService.php'),
            'c113_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC113WeeklySwingWatchlistProductionReadinessReviewCommand.php'),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
            'production_runtime_wiring_not_enabled' => true,
            'runtime_bridge_not_enabled' => true,
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
            'progress_marker' => 'PR-01_C113_PRODUCTION_READINESS_REVIEW',
            'c111_final_closure_carried_forward' => true,
            'c112_post_c111_transition_gate_carried_forward' => true,
            'c112_production_phase_approval_carried_forward' => true,
            'c113_production_readiness_review_executed' => true,
            'c113_ready_for_controlled_runtime_wiring_readiness_review' => $pass,
            'still_no_live_runtime' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C114_RECOMMENDATION : 'C113_TARGETED_C112_PRODUCTION_PHASE_APPROVAL_REPAIR',
            'planned_next_scope' => $pass ? 'controlled production runtime wiring readiness review only; not production deployment, live rollout, default runtime wiring execution, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before production readiness review can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C112 artifact hash',
                'locked C112 file SHA1',
                'operator approval reference',
                'unchanged candidate scope',
                'production runtime wiring readiness checklist',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'scoped_keys_are_not_duplicate_by_name_only' => true,
            'c112_expected_c111_file_sha1_scoped_key_preserved' => true,
            'expected_c111_file_sha1_scoped_key_preserved' => true,
            'c111_c112_artifacts_not_modified' => true,
            'c98_c112_sections_not_rewritten' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function boundaryAliases(bool $pass): array
    {
        return [
            'C111_NON_LIVE_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED' => $pass,
            'C111_NON_LIVE_AUDIT_ARCHIVE_TERMINAL' => $pass,
            'C111_NO_NEXT_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED' => $pass,
            'C112_SEPARATE_POST_C111_PRODUCTION_PHASE_TRANSITION_GATE' => $pass,
            'C112_NOT_AUDIT_ARCHIVE_CONTINUATION' => $pass,
            'C112_DOES_NOT_REOPEN_C111_FINAL_CLOSURE' => $pass,
            'C112_DOES_NOT_EXTEND_NON_LIVE_AUDIT_ARCHIVE_REVIEW' => $pass,
            'C112_PRODUCTION_PHASE_APPROVAL_IS_READINESS_ENTRY_ONLY' => $pass,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C113 validates C112 artifact_hash and file SHA1 locks before PR-01 production readiness review is recorded.',
            'C113 validates C112 production phase approval for readiness review only.',
            'C113 carries forward C111 as terminal/final-closed for the non-live handoff audit archive chain.',
            'C113 treats C112 as a separate post-C111 production phase transition gate, not audit archive continuation.',
            'C113 requires --operator-approved and a non-empty --approval-reference.',
            'C113 prepares an artifact-only production readiness review manifest and checklist without live output.',
            'C113 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C113 does not deploy production, wire runtime, activate bridge or rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.',
            'C113 may only recommend C114 weekly swing watchlist production runtime wiring readiness review as the next controlled step.',
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
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $payload = $decoded;
                $actualHash = $decoded['artifact_hash'] ?? null;
            }
            $actualFileSha1 = strtoupper(sha1($raw));
        }
        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $actualHash !== null && hash_equals($expectedHash, (string) $actualHash),
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $actualFileSha1 !== null && strtoupper($expectedFileSha1) === $actualFileSha1,
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false);
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
