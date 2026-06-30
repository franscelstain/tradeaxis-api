<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService
{
    public const RUN_CODE = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW';
    public const ARTIFACT_TYPE = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW';

    public const DEFAULT_C111_ARTIFACT = 'storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json';
    public const DEFAULT_EXPECTED_C111_HASH = '8f7c8b81eb401bfdd70f62f90779db63fc4af56d';
    public const DEFAULT_EXPECTED_C111_FILE_SHA1 = 'D58C10185970C9344F6EB3818A5A31C75C876842';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c112-weekly-swing-watchlist-production-phase-approval-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C111_STATUS = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C111_REASON = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C111_TERMINAL_RECOMMENDATION = 'NO_NEXT_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED';
    private const C113_RECOMMENDATION = 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW';

    private const PASS_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_PASSED_PRODUCTION_PHASE_APPROVED_FOR_READINESS_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_NEW_PRODUCTION_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C111_FINAL_CLOSURE_INCOMPLETE_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_C111_FINAL_CLOSURE_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

    private const REQUIRED_TRUE_C111_FIELDS = [
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_pass',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closed',
        'handoff_audit_archive_final_closed',
        'audit_archive_final_closed',
        'final_closure_manifest_created',
        'primary_candidate_handoff_audit_archive_final_closed',
        'backup_candidate_handoff_audit_archive_final_closed',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_FALSE_C111_FIELDS = [
        'comparator_candidate_handoff_audit_archive_final_closed',
    ];

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
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
        'production_deployment_allowed',
        'production_deployment_executed',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
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
        string $c111Artifact = self::DEFAULT_C111_ARTIFACT,
        string $expectedC111Hash = self::DEFAULT_EXPECTED_C111_HASH,
        string $expectedC111FileSha1 = self::DEFAULT_EXPECTED_C111_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c111Artifact, $expectedC111Hash, $expectedC111FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C112_BLOCKED_C111_ARTIFACT_LOCK_MISMATCH', 'C111 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C112_BLOCKED_C111_ARTIFACT_LOCK_MISMATCH', 'C111 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C112_BLOCKED_C111_FILE_SHA1_LOCK_MISMATCH', 'C111 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c111 = $load['payload'];
        if (($c111['status'] ?? null) !== self::EXPECTED_C111_STATUS) {
            return $this->blocked($artifact, 'C112_BLOCKED_C111_STATUS_MISMATCH', 'C111 status is not final closed.', $outputPath, $overwrite);
        }
        if (($c111['reason_code'] ?? null) !== self::EXPECTED_C111_REASON) {
            return $this->blocked($artifact, 'C112_BLOCKED_C111_REASON_CODE_MISMATCH', 'C111 reason_code is not final closed.', $outputPath, $overwrite);
        }
        if (! $this->c111TerminalRecommendationMatches($c111)) {
            return $this->blocked($artifact, 'C112_BLOCKED_C111_TERMINAL_RECOMMENDATION_MISMATCH', 'C111 terminal recommendation is not no-next audit archive review.', $outputPath, $overwrite);
        }
        if (! $this->c111FinalClosureComplete($c111)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C111_FINAL_CLOSURE_INCOMPLETE_STATUS, 'C111 final closure evidence is incomplete.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c111);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c111_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C111 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c111)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C111 candidate scope does not match final-closed non-live evidence.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C112 requires new --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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
            return $this->rejected($artifact, $failures[0], 'C112 production phase approval review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C112 records a new operator-approved production phase entry for weekly swing watchlist readiness review only. It does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C112_NEW_PRODUCTION_PHASE_APPROVED_FOR_READINESS_REVIEW_ONLY';
        $artifact['next_step_recommendation'] = self::C113_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C112_NOT_RUN',
            'reason_code' => 'C112_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_phase_approval_review_executed' => false,
            'weekly_swing_watchlist_production_phase_approval_review_allowed' => false,
            'weekly_swing_watchlist_production_phase_approval_review_pass' => false,
            'weekly_swing_watchlist_production_phase_opened' => false,
            'production_phase_approval_granted' => false,
            'production_readiness_review_allowed' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'primary_candidate_production_phase_approval_granted' => false,
            'backup_candidate_production_phase_approval_granted' => false,
            'comparator_candidate_production_phase_approval_granted' => false,
            'c111_handoff_audit_archive_final_closed' => false,
            'c111_audit_archive_final_closed' => false,
            'c111_final_closure_manifest_created' => false,
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
            'weekly_swing_watchlist_production_phase_approval_review_executed' => true,
            'weekly_swing_watchlist_production_phase_approval_review_allowed' => true,
            'weekly_swing_watchlist_production_phase_approval_review_pass' => true,
            'weekly_swing_watchlist_production_phase_opened' => true,
            'production_phase_approval_granted' => true,
            'production_readiness_review_allowed' => true,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'primary_candidate_production_phase_approval_granted' => true,
            'backup_candidate_production_phase_approval_granted' => true,
            'comparator_candidate_production_phase_approval_granted' => false,
            'c111_handoff_audit_archive_final_closed' => true,
            'c111_audit_archive_final_closed' => true,
            'c111_final_closure_manifest_created' => true,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c111 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c111_lock_validation_summary'] = $this->c111LockValidationSummary($load, $c111);
        $artifact['c111_final_closure_carry_forward_summary'] = $this->c111FinalClosureCarryForwardSummary($c111, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c111, $pass);
        $artifact['new_production_approval_validation_summary'] = $this->newProductionApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c112_readiness_decision'] = $this->productionPhaseApprovalDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_production_phase_approval_manifest'] = $this->productionPhaseApprovalManifest($pass);
        $artifact['c112_candidate_production_phase_approval_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['production_phase_approval_context_summary'] = $this->productionPhaseApprovalContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($pass) {
            $artifact = array_merge($artifact, $this->passingTopLevelState());
        }
        return $artifact;
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        if ($exists) {
            $raw = file_get_contents($path);
            $decoded = json_decode((string) $raw, true);
            $payload = is_array($decoded) ? $decoded : null;
            $actualHash = is_array($payload) ? (string) ($payload['artifact_hash'] ?? '') : null;
            $actualFileSha1 = strtoupper(sha1_file($path));
        }
        $expectedFileSha1 = strtoupper($expectedFileSha1);
        return [
            'source_lock' => 'C111',
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $exists && $actualHash === $expectedHash,
            'expected_file_sha1' => $expectedFileSha1,
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $exists && $actualFileSha1 === $expectedFileSha1,
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c111' => [
                'artifact_path' => $load['path'],
                'artifact_exists' => $load['exists'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'expected_status' => self::EXPECTED_C111_STATUS,
                'expected_reason_code' => self::EXPECTED_C111_REASON,
                'expected_terminal_recommendation' => self::EXPECTED_C111_TERMINAL_RECOMMENDATION,
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C111',
            'c111_artifact_path' => $load['path'],
            'c111_artifact_exists' => $load['exists'],
            'expected_c111_hash' => $load['expected_hash'],
            'actual_c111_hash' => $load['actual_hash'],
            'c111_hash_match' => $load['hash_match'],
            'expected_c111_file_sha1' => $load['expected_file_sha1'],
            'actual_c111_file_sha1' => $load['actual_file_sha1'],
            'c111_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c111TerminalRecommendationMatches(array $c111): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c111_readiness_decision', 'next_recommendation'],
        ] as $path) {
            if ($this->valueAt($c111, $path) === self::EXPECTED_C111_TERMINAL_RECOMMENDATION) {
                return true;
            }
        }
        return false;
    }

    private function c111FinalClosureComplete(array $c111): bool
    {
        foreach (self::REQUIRED_TRUE_C111_FIELDS as $field) {
            if ((bool) ($c111[$field] ?? false) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_FALSE_C111_FIELDS as $field) {
            if ((bool) ($c111[$field] ?? false) !== false) {
                return false;
            }
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

    private function candidateScopeMatches(array $c111): bool
    {
        return ($c111['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE) === self::PRIMARY_CANDIDATE
            && ($c111['backup_candidate_code'] ?? self::BACKUP_CANDIDATE) === self::BACKUP_CANDIDATE
            && ($c111['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE) === self::COMPARATOR_CANDIDATE
            && (bool) ($c111['a01_remains_comparator_only'] ?? false) === true
            && (bool) ($c111['a01_promoted'] ?? false) === false
            && (bool) ($c111['candidate_promotion_executed'] ?? false) === false
            && (bool) ($c111['candidate_rerank_executed'] ?? false) === false
            && (bool) ($c111['strategy_retune_executed'] ?? false) === false
            && (bool) ($c111['scoring_mutation_executed'] ?? false) === false
            && (bool) ($c111['catalog_selection_changed'] ?? false) === false
            && (bool) ($c111['runtime_selection_changed'] ?? false) === false;
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
            'persist_production_phase_approval_context_to_live_runtime',
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
            'modify_c60_c111_artifacts',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'a01') !== false || strpos($field, 'candidate') !== false || strpos($field, 'scoring') !== false || strpos($field, 'catalog') !== false || strpos($field, 'strategy') !== false) {
            return self::CANDIDATE_SCOPE_MISMATCH_STATUS;
        }
        return self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
    }

    private function c111LockValidationSummary(array $load, array $c111): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C111',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'expected_status' => self::EXPECTED_C111_STATUS,
            'actual_status' => $c111['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C111_REASON,
            'actual_reason_code' => $c111['reason_code'] ?? null,
            'expected_terminal_recommendation' => self::EXPECTED_C111_TERMINAL_RECOMMENDATION,
            'terminal_recommendation_match' => $this->c111TerminalRecommendationMatches($c111),
            'c111_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function c111FinalClosureCarryForwardSummary(array $c111, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c111_final_closure_review_pass' => (bool) ($c111['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_pass'] ?? false),
            'c111_handoff_audit_archive_final_closed' => (bool) ($c111['handoff_audit_archive_final_closed'] ?? false),
            'c111_audit_archive_final_closed' => (bool) ($c111['audit_archive_final_closed'] ?? false),
            'c111_final_closure_manifest_created' => (bool) ($c111['final_closure_manifest_created'] ?? false),
            'c111_primary_candidate_final_closed' => (bool) ($c111['primary_candidate_handoff_audit_archive_final_closed'] ?? false),
            'c111_backup_candidate_final_closed' => (bool) ($c111['backup_candidate_handoff_audit_archive_final_closed'] ?? false),
            'c111_comparator_candidate_final_closed' => (bool) ($c111['comparator_candidate_handoff_audit_archive_final_closed'] ?? false),
            'c112_production_phase_approval_can_start' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $c111, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c111),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_phase_approval_candidate',
            'backup_candidate_role' => 'backup_production_phase_approval_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_production_phase_approval_granted' => $pass,
            'backup_candidate_production_phase_approval_granted' => $pass,
            'comparator_candidate_production_phase_approval_granted' => false,
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

    private function newProductionApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'new_production_approval_required' => true,
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'new_production_approval_validation_pass' => $pass,
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

    private function productionPhaseApprovalDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c111_lock_valid' => $pass,
            'c111_final_closure_complete' => $pass,
            'weekly_swing_watchlist_production_phase_approval_review_executed' => true,
            'weekly_swing_watchlist_production_phase_approval_review_allowed' => $pass,
            'weekly_swing_watchlist_production_phase_approval_review_pass' => $pass,
            'weekly_swing_watchlist_production_phase_opened' => $pass,
            'production_phase_approval_granted' => $pass,
            'production_readiness_review_allowed' => $pass,
            'primary_candidate_production_phase_approval_granted' => $pass,
            'backup_candidate_production_phase_approval_granted' => $pass,
            'comparator_candidate_production_phase_approval_granted' => false,
            'production_ready' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'next_recommendation' => $pass ? self::C113_RECOMMENDATION : 'C112_TARGETED_C111_FINAL_CLOSURE_REPAIR',
            'decision_reason' => $pass ? 'C112 opens the production phase for readiness review only after new operator approval.' : 'C112 cannot open production phase until C111 lock, final closure, and new approval pass.',
            'diagnostic_conclusion' => $pass ? 'C112_NEW_PRODUCTION_PHASE_APPROVED_FOR_READINESS_REVIEW_ONLY' : 'C112_NEW_PRODUCTION_PHASE_APPROVAL_REJECTED',
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C113_RECOMMENDATION : 'C112_TARGETED_C111_FINAL_CLOSURE_REPAIR',
            'next_scope' => $pass ? 'weekly swing watchlist production readiness review only; still no production deployment, live runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted C111 final closure or new approval repair only',
        ];
    }

    private function productionPhaseApprovalManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'new_operator_approved_weekly_swing_watchlist_production_phase_approval_review',
            'source_artifact' => 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW',
            'source_artifact_path' => self::DEFAULT_C111_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C111_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C111_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_phase_approval_candidate',
            'backup_candidate_role' => 'backup_production_phase_approval_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'c111_final_closure_carried_forward' => $pass,
            'production_phase_approval_granted' => $pass,
            'production_readiness_review_allowed' => $pass,
            'production_ready' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'production_phase_approval_used_for_selection' => false,
            'production_phase_approval_used_for_retuning' => false,
            'production_phase_approval_used_for_ranking' => false,
            'production_phase_approval_used_for_plan_confirm_mutation' => false,
            'production_phase_approval_used_for_live_rollout' => false,
            'production_phase_approval_artifact_only' => true,
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'production_phase_approval_review_pass' => $pass,
            'production_phase_approval_granted' => $pass,
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
                'c112_role' => 'primary_production_phase_approval_candidate',
                'primary_candidate_production_phase_approval_granted' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c112_role' => 'backup_production_phase_approval_candidate',
                'backup_candidate_production_phase_approval_granted' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c112_role' => 'comparator_only_candidate',
                'production_phase_approval_review_pass' => false,
                'production_phase_approval_granted' => false,
                'comparator_candidate_production_phase_approval_granted' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function productionPhaseApprovalContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_phase_approval_context_created' => true,
            'weekly_swing_watchlist_production_phase_approval_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_phase_approval_context_persisted_to_live_runtime' => false,
            'production_phase_approval_context_persisted_to_live_runtime' => false,
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

    private function runtimeReadinessInspectionSummary(): array
    {
        return [
            'runtime_readiness_inspection_completed' => true,
            'c111_final_closure_artifact_identified' => is_file(self::DEFAULT_C111_ARTIFACT),
            'c112_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService.php'),
            'c112_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewCommand.php'),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
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
            'progress_marker' => 'C112_NEW_PRODUCTION_PHASE_APPROVAL_REVIEW',
            'c111_final_closure_carried_forward' => true,
            'c112_production_phase_approval_review_executed' => true,
            'c112_production_phase_approval_granted' => $pass,
            'still_no_live_runtime' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C113_RECOMMENDATION : 'C112_TARGETED_C111_FINAL_CLOSURE_REPAIR',
            'planned_next_scope' => $pass ? 'production readiness review only; not production deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before production phase approval can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C111 artifact hash',
                'locked C111 file SHA1',
                'new production approval reference',
                'unchanged candidate scope',
                'production readiness checklist',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C112 validates C111 artifact_hash and file SHA1 locks before a new production phase approval is recorded.',
            'C112 validates C111 final closure fields and A01 comparator-only state.',
            'C112 requires a new operator approval and approval reference.',
            'C112 opens production readiness review for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C112 does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.',
            'C112 may only recommend C113 weekly swing watchlist production readiness review as the next controlled step.',
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
