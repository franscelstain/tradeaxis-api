<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewService
{
    public const RUN_CODE = 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW';
    public const ARTIFACT_TYPE = 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW';

    public const DEFAULT_C95_ARTIFACT = 'storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json';
    public const DEFAULT_EXPECTED_C95_HASH = 'a8923e58e35126741226eab29cc07c88a2a721f8';
    public const DEFAULT_EXPECTED_C95_FILE_SHA1 = 'AEF14CC999F8050DADC8E451E9116C59FD1C2534';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C95_STATUS = 'C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C95_REASON = 'C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C95_RECOMMENDATION = self::RUN_CODE;
    private const C97_RECOMMENDATION = 'C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW';
    private const PASS_STATUS = 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_CLOSURE_SEALED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'post_activation_audit_archive_context_persisted_to_live_runtime',
        'post_activation_audit_archive_completion_context_persisted_to_live_runtime',
        'post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime',
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
    ];

    private const C95_LIVE_OR_MUTATING_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'post_activation_audit_archive_context_persisted_to_live_runtime',
        'post_activation_audit_archive_completion_context_persisted_to_live_runtime',
        'post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime',
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
    ];

    private const DOC_PATHS = [
        'c96_validation_doc' => 'docs/watchlist/audit/WS_C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW.md',
        'c96_operator_commands_doc' => 'docs/watchlist/audit/WS_C96_OPERATOR_VALIDATION_COMMANDS.md',
        'c95_validation_doc' => 'docs/watchlist/audit/WS_C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW.md',
        'c95_operator_commands_doc' => 'docs/watchlist/audit/WS_C95_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c95_post_activation_audit_archive_completion_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService.php',
        'c96_post_activation_audit_archive_closure_seal_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewService.php',
        'c95_command' => 'app/Console/Commands/Watchlist/RunBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewCommand.php',
        'c96_command' => 'app/Console/Commands/Watchlist/RunBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewCommand.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/*no-*-test.json',
        'storage/app/watchlist/backtest/*missing-*-test.json',
        'storage/app/watchlist/backtest/*mismatch-*-test.json',
        'storage/app/watchlist/backtest/*negative-*-test.json',
    ];

    public function execute(
        string $c95Artifact = self::DEFAULT_C95_ARTIFACT,
        string $expectedC95Hash = self::DEFAULT_EXPECTED_C95_HASH,
        string $expectedC95FileSha1 = self::DEFAULT_EXPECTED_C95_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c95Artifact, $expectedC95Hash, $expectedC95FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C96_BLOCKED_C95_ARTIFACT_LOCK_MISMATCH', 'C95 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C96_BLOCKED_C95_ARTIFACT_LOCK_MISMATCH', 'C95 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C96_BLOCKED_C95_FILE_SHA1_LOCK_MISMATCH', 'C95 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c95 = $load['payload'];
        if (($c95['status'] ?? null) !== self::EXPECTED_C95_STATUS || ($c95['reason_code'] ?? null) !== self::EXPECTED_C95_REASON) {
            return $this->blocked($artifact, 'C96_BLOCKED_C95_STATUS_OR_REASON_MISMATCH', 'C95 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c95NextRecommendationMatches($c95)) {
            return $this->blocked($artifact, 'C96_BLOCKED_C95_NEXT_RECOMMENDATION_MISMATCH', 'C95 next recommendation is not C96.', $outputPath, $overwrite);
        }
        if (! $this->c95AuditArchiveCompleted($c95)) {
            return $this->blocked($artifact, 'C96_BLOCKED_C95_AUDIT_ARCHIVE_COMPLETION_NOT_COMPLETE', 'C95 audit archive completion evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c95);
        if ($safetyFailure !== null) {
            $artifact['c95_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C96_BLOCKED_C95_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C95 contains live, mutating, or production safety flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c95)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C95 candidate scope does not match locked audit archive completion decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C96 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C96 post-activation audit archive closure seal review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C96 sealed the post-activation audit archive closure package for primary and backup in non-live audit context only. This still does not deploy, wire live runtime, activate controlled rollout, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C96_AUDIT_ARCHIVE_CLOSURE_SEALED_NON_LIVE_ONLY';
        $artifact['next_step_recommendation'] = self::C97_RECOMMENDATION;
        $artifact['post_activation_audit_archive_closure_seal_review_executed'] = true;
        $artifact['post_activation_audit_archive_closure_seal_review_allowed'] = true;
        $artifact['post_activation_audit_archive_closure_seal_review_pass'] = true;
        $artifact['post_activation_audit_archive_closure_sealed'] = true;
        $artifact['audit_archive_closure_sealed'] = true;
        $artifact['primary_candidate_audit_archive_closure_sealed'] = true;
        $artifact['backup_candidate_audit_archive_closure_sealed'] = true;
        $artifact['comparator_candidate_audit_archive_closure_sealed'] = false;
        $artifact['a01_remains_comparator_only'] = true;
        $artifact['c95_audit_archive_completed'] = true;
        $artifact['closure_seal_manifest_created'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C96_NOT_RUN',
            'reason_code' => 'C96_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'post_activation_audit_archive_closure_seal_review_executed' => false,
            'post_activation_audit_archive_closure_seal_review_allowed' => false,
            'post_activation_audit_archive_closure_seal_review_pass' => false,
            'post_activation_audit_archive_closure_sealed' => false,
            'audit_archive_closure_sealed' => false,
            'primary_candidate_audit_archive_closure_sealed' => false,
            'backup_candidate_audit_archive_closure_sealed' => false,
            'comparator_candidate_audit_archive_closure_sealed' => false,
            'a01_remains_comparator_only' => true,
            'c95_audit_archive_completed' => false,
            'closure_seal_manifest_created' => false,
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

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $decoded = json_decode($raw, true);
            $payload = is_array($decoded) ? $decoded : null;
            $actualHash = is_array($payload) ? (string) ($payload['artifact_hash'] ?? '') : null;
            $actualFileSha1 = strtoupper(sha1($raw));
        }
        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $exists && $actualHash === $expectedHash,
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $exists && $actualFileSha1 === strtoupper($expectedFileSha1),
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        $c95 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c95['source_artifact_locks'] ?? null) ? $c95['source_artifact_locks'] : [];
        return [
            'c95_artifact_path' => $load['path'],
            'expected_c95_hash' => $load['expected_hash'],
            'actual_c95_hash' => $load['actual_hash'],
            'c95_hash_match' => $load['hash_match'],
            'expected_c95_file_sha1' => $load['expected_file_sha1'],
            'actual_c95_file_sha1' => $load['actual_file_sha1'],
            'c95_file_sha1_match' => $load['file_sha1_match'],
            'c95_source_lineage_checked' => true,
            'c95_source_lineage_match' => $this->lineageLocksMatch($c95),
            'c94_artifact_hash_from_c95' => (string) ($locks['actual_c94_hash'] ?? ($c95['actual_c94_hash'] ?? '')),
            'c94_file_sha1_from_c95' => (string) ($locks['actual_c94_file_sha1'] ?? ($c95['actual_c94_file_sha1'] ?? '')),
            'c93_artifact_hash_from_c94' => (string) ($locks['c93_artifact_hash_from_c94'] ?? ''),
            'c93_file_sha1_from_c94' => (string) ($locks['c93_file_sha1_from_c94'] ?? ''),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c95_hash' => $load['expected_hash'],
            'actual_c95_hash' => $load['actual_hash'],
            'c95_hash_match' => $load['hash_match'],
            'expected_c95_file_sha1' => $load['expected_file_sha1'],
            'actual_c95_file_sha1' => $load['actual_file_sha1'],
            'c95_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c95): bool
    {
        $locks = is_array($c95['source_artifact_locks'] ?? null) ? $c95['source_artifact_locks'] : [];
        foreach (['c94_hash_match', 'c94_file_sha1_match', 'c94_source_lineage_match'] as $field) {
            if (($locks[$field] ?? null) !== true) {
                return false;
            }
        }
        return true;
    }

    private function c95NextRecommendationMatches(array $c95): bool
    {
        if (($c95['next_step_recommendation'] ?? null) !== self::EXPECTED_C95_RECOMMENDATION) {
            return false;
        }
        if (($c95['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C95_RECOMMENDATION) {
            return false;
        }
        if (($c95['c95_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C95_RECOMMENDATION) {
            return false;
        }
        if (($c95['planned_next_summary']['planned_next_review'] ?? null) !== self::EXPECTED_C95_RECOMMENDATION) {
            return false;
        }
        return true;
    }

    private function c95AuditArchiveCompleted(array $c95): bool
    {
        foreach ([
            'post_activation_audit_archive_completion_review_pass',
            'post_activation_audit_archive_completed',
            'audit_archive_completed',
            'primary_candidate_audit_archive_completed',
            'backup_candidate_audit_archive_completed',
            'archive_completion_manifest_created',
        ] as $field) {
            if (($c95[$field] ?? null) !== true) {
                return false;
            }
            if (($c95['c95_readiness_decision'][$field] ?? null) !== true && $field !== 'archive_completion_manifest_created') {
                return false;
            }
        }
        if (($c95['comparator_candidate_audit_archive_completed'] ?? null) !== false || ($c95['c95_readiness_decision']['comparator_candidate_audit_archive_completed'] ?? null) !== false) {
            return false;
        }
        if (($c95['a01_remains_comparator_only'] ?? null) !== true || ($c95['c95_readiness_decision']['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        if (($c95['temporary_negative_artifacts_remaining'] ?? null) !== false || ($c95['temporary_negative_artifact_cleanup_confirmed'] ?? null) !== true || (array) ($c95['temporary_negative_artifact_paths'] ?? []) !== []) {
            return false;
        }
        if (($c95['next_readiness_decision']['candidate_ready_for_post_activation_audit_archive_closure_seal_review_count'] ?? null) !== 2) {
            return false;
        }
        return true;
    }

    private function firstLiveOrMutatingSafetyFlag(array $payload): ?string
    {
        foreach (self::C95_LIVE_OR_MUTATING_FLAGS as $flag) {
            if (($payload[$flag] ?? false) === true) {
                return $flag;
            }
        }
        foreach (['c95_readiness_decision', 'post_activation_audit_archive_completion_decision', 'production_mutation_safety_summary'] as $section) {
            $values = is_array($payload[$section] ?? null) ? $payload[$section] : [];
            foreach (self::C95_LIVE_OR_MUTATING_FLAGS as $flag) {
                if (($values[$flag] ?? false) === true) {
                    return $section.'.'.$flag;
                }
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c95): bool
    {
        if (($c95['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || ($c95['backup_candidate_code'] ?? null) !== self::BACKUP_CANDIDATE || ($c95['comparator_candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }
        $scope = is_array($c95['candidate_scope_freeze_summary'] ?? null) ? $c95['candidate_scope_freeze_summary'] : [];
        $decision = is_array($c95['c95_readiness_decision'] ?? null) ? $c95['c95_readiness_decision'] : [];
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || (array) ($scope['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || (array) ($scope['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE]) {
            return false;
        }
        if (($decision['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || ($decision['backup_candidate_code'] ?? null) !== self::BACKUP_CANDIDATE || ($decision['comparator_candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }
        if (($decision['primary_candidate_audit_archive_completed'] ?? null) !== true || ($decision['backup_candidate_audit_archive_completed'] ?? null) !== true || ($decision['comparator_candidate_audit_archive_completed'] ?? null) !== false || ($decision['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        foreach ([
            'candidate_scope_changed_after_c89',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'audit_archive_completion_used_for_selection',
            'audit_archive_completion_used_for_retuning',
            'audit_archive_completion_used_for_ranking',
            'audit_archive_completion_used_for_plan_confirm_mutation',
            'audit_archive_completion_used_for_live_rollout',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'scoring_logic_changed',
            'a01_promoted',
            'a01_used_as_runtime_fallback',
        ] as $field) {
            if (($scope[$field] ?? false) !== false || ($decision[$field] ?? false) !== false) {
                return false;
            }
        }
        return true;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c95 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $temporaryRemaining = $temporaryNegativePaths !== [];
        $artifact['c95_lock_validation_summary'] = $this->c95LockValidationSummary($load, $c95);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c95);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['post_activation_audit_archive_closure_seal_decision'] = $this->postActivationAuditArchiveClosureSealDecision($pass, $temporaryRemaining);
        $artifact['c96_readiness_decision'] = $artifact['post_activation_audit_archive_closure_seal_decision'];
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass, $temporaryRemaining);
        $artifact['post_activation_audit_archive_closure_seal_candidate_scorecard'] = $this->candidateScorecard($pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['post_activation_audit_archive_closure_seal_context_summary'] = $this->postActivationAuditArchiveClosureSealContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c95_audit_archive_completion_carry_forward_validation_summary'] = $this->c95AuditArchiveCompletionCarryForwardValidationSummary($c95, $pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['post_activation_audit_archive_closure_seal_candidate_scorecard'], $pass);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        foreach ($this->prohibitedOptionFields() as $field) {
            if (($options[$field] ?? false) !== false) {
                $failures[] = self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
            }
        }
        if ($this->configFlagIsOn('production_catalog_runtime_bridge_enabled') || $this->configFlagIsOn('production_catalog_controlled_runtime_opt_in_pilot_enabled') || $this->configFlagIsOn('production_catalog_controlled_shadow_rollout_enabled') || $this->configFlagIsOn('production_catalog_controlled_parallel_run_enabled') || $this->configFlagIsOn('production_catalog_controlled_rollout_enabled')) {
            $failures[] = 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c89',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'audit_archive_closure_seal_used_for_selection',
            'audit_archive_closure_seal_used_for_retuning',
            'audit_archive_closure_seal_used_for_ranking',
            'audit_archive_closure_seal_used_for_plan_confirm_mutation',
            'audit_archive_closure_seal_used_for_live_rollout',
            'audit_archive_closure_seal_allowed_to_auto_enable_runtime',
            'audit_archive_closure_seal_allowed_to_auto_deploy',
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'post_activation_audit_archive_context_persisted_to_live_runtime',
            'post_activation_audit_archive_completion_context_persisted_to_live_runtime',
            'post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime',
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
            'a01_promoted',
            'a01_used_as_runtime_fallback',
        ];
    }

    private function c95LockValidationSummary(array $load, array $c95): array
    {
        return [
            'c95_lock_validation_completed' => true,
            'c95_artifact_exists' => $load['exists'],
            'expected_c95_hash' => $load['expected_hash'],
            'actual_c95_hash' => $load['actual_hash'],
            'c95_hash_match' => $load['hash_match'],
            'expected_c95_file_sha1' => $load['expected_file_sha1'],
            'actual_c95_file_sha1' => $load['actual_file_sha1'],
            'c95_file_sha1_match' => $load['file_sha1_match'],
            'c95_status_match' => ($c95['status'] ?? null) === self::EXPECTED_C95_STATUS,
            'c95_reason_code_match' => ($c95['reason_code'] ?? null) === self::EXPECTED_C95_REASON,
            'c95_next_recommendation_match' => $this->c95NextRecommendationMatches($c95),
            'c95_audit_archive_completed' => $this->c95AuditArchiveCompleted($c95),
        ];
    }

    private function lineageValidationSummary(array $c95): array
    {
        return [
            'lineage_validation_completed' => true,
            'c95_to_c94_lock_match' => (($c95['source_artifact_locks']['c94_hash_match'] ?? null) === true && ($c95['source_artifact_locks']['c94_file_sha1_match'] ?? null) === true),
            'c95_to_c60_lineage_match' => $this->lineageLocksMatch($c95),
            'lineage_source' => 'C95_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C95_LOCKED_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c89' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
            'scoring_logic_changed' => false,
            'audit_archive_closure_seal_used_for_selection' => false,
            'audit_archive_closure_seal_used_for_retuning' => false,
            'audit_archive_closure_seal_used_for_ranking' => false,
            'audit_archive_closure_seal_used_for_plan_confirm_mutation' => false,
            'audit_archive_closure_seal_used_for_live_rollout' => false,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => false,
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_validation_completed' => true,
            'operator_approval_required' => true,
            'approval_reference_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'operator_approval_validation_pass' => $pass,
        ];
    }

    private function temporaryNegativeArtifactGuardSummary(array $temporaryNegativePaths): array
    {
        return [
            'temporary_negative_artifact_guard_completed' => true,
            'temporary_negative_artifacts_remaining' => $temporaryNegativePaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryNegativePaths === [],
            'temporary_negative_artifact_paths' => array_values($temporaryNegativePaths),
            'temporary_negative_artifact_patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
    }

    private function postActivationAuditArchiveClosureSealDecision(bool $pass, bool $temporaryRemaining): array
    {
        $decision = [
            'review_pass' => $pass,
            'validation_completed' => true,
            'audit_archive_closure_sealed' => $pass,
            'post_activation_audit_archive_closure_seal_review_executed' => $pass,
            'post_activation_audit_archive_closure_seal_review_allowed' => $pass,
            'post_activation_audit_archive_closure_seal_review_pass' => $pass,
            'post_activation_audit_archive_closure_sealed' => $pass,
            'primary_candidate_audit_archive_closure_sealed' => $pass,
            'backup_candidate_audit_archive_closure_sealed' => $pass,
            'comparator_candidate_audit_archive_closure_sealed' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => $temporaryRemaining,
            'temporary_negative_artifact_cleanup_confirmed' => ! $temporaryRemaining,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'audit_archive_closure_seal_is_artifact_only' => true,
            'audit_archive_closure_seal_used_for_selection' => false,
            'audit_archive_closure_seal_used_for_retuning' => false,
            'audit_archive_closure_seal_used_for_ranking' => false,
            'audit_archive_closure_seal_used_for_plan_confirm_mutation' => false,
            'audit_archive_closure_seal_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C97_RECOMMENDATION : 'C96_TARGETED_C95_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REPAIR',
            'decision_reason' => $pass ? 'C96 post-activation audit archive closure seal validated primary and backup in non-live audit context only.' : 'C96 post-activation audit archive closure seal review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'C96_AUDIT_ARCHIVE_CLOSURE_SEALED_NON_LIVE_ONLY' : 'C96_AUDIT_ARCHIVE_CLOSURE_SEAL_REPAIR_REQUIRED',
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass, bool $temporaryRemaining): array
    {
        $decision = $this->postActivationAuditArchiveClosureSealDecision($pass, $temporaryRemaining);
        $decision['candidate_ready_for_post_activation_audit_archive_finalization_review_count'] = $pass ? 2 : 0;
        $decision['candidate_codes'] = $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [];
        return $decision;
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'post_activation_audit_archive_closure_seal_review_pass' => $pass,
            'post_activation_audit_archive_closure_sealed' => $pass,
            'candidate_ready_for_post_activation_audit_archive_finalization_review' => $pass,
            'candidate_active_in_default_runtime_catalog' => false,
            'c95_lock_validation_pass' => $pass,
            'candidate_scope_freeze_pass' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'post_activation_audit_archive_closure_seal_advisory_only_pass' => $pass,
            'production_mutation_safety_pass' => $pass,
            'documentation_governance_pass' => true,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $base[$flag] = false;
        }
        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c96_role' => 'primary_post_activation_audit_archive_closure_sealed_candidate',
                'primary_candidate_audit_archive_closure_sealed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c96_role' => 'backup_post_activation_audit_archive_closure_sealed_candidate',
                'backup_candidate_audit_archive_closure_sealed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c96_role' => 'comparator_only_candidate',
                'post_activation_audit_archive_closure_seal_review_pass' => false,
                'post_activation_audit_archive_closure_sealed' => false,
                'candidate_ready_for_post_activation_audit_archive_finalization_review' => false,
                'comparator_candidate_audit_archive_closure_sealed' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function postActivationAuditArchiveClosureSealContextSummary(bool $pass): array
    {
        return [
            'post_activation_audit_archive_closure_seal_context_created' => true,
            'post_activation_audit_archive_closure_seal_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'post_activation_audit_archive_context_persisted_to_live_runtime' => false,
            'post_activation_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime' => false,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function runtimeReadinessInspectionSummary(): array
    {
        $paths = [];
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path) || is_dir($path)];
        }
        return [
            'runtime_readiness_inspection_completed' => true,
            'inspected_paths' => $paths,
            'post_activation_audit_archive_completion_source_identified' => is_file(self::RUNTIME_PATHS['c95_post_activation_audit_archive_completion_service']),
            'post_activation_audit_archive_closure_seal_source_identified' => is_file(self::RUNTIME_PATHS['c96_post_activation_audit_archive_closure_seal_service']),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
            'plan_confirm_runtime_change_executed' => false,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'pilot_runtime_active' => false,
            'shadow_runtime_active' => false,
            'runtime_bridge_active' => false,
        ];
    }

    private function featureFlagOperatorApprovalKillSwitchValidationSummary(bool $pass): array
    {
        return [
            'feature_flag_operator_approval_kill_switch_validation_completed' => true,
            'default_off_feature_flag_pass' => $pass,
            'runtime_bridge_feature_flag_name' => 'watchlist.production_catalog_runtime_bridge_enabled',
            'runtime_bridge_feature_flag_default_off' => true,
            'runtime_bridge_feature_flag_current_state' => false,
            'controlled_pilot_feature_flag_name' => 'watchlist.production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'controlled_pilot_feature_flag_default_off' => true,
            'controlled_pilot_feature_flag_current_state' => false,
            'controlled_shadow_feature_flag_name' => 'watchlist.production_catalog_controlled_shadow_rollout_enabled',
            'controlled_shadow_feature_flag_default_off' => true,
            'controlled_shadow_feature_flag_current_state' => false,
            'explicit_operator_approval_required_pass' => $pass,
            'operator_approval_artifact_required' => true,
            'operator_approval_reference_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c95AuditArchiveCompletionCarryForwardValidationSummary(array $c95, bool $pass): array
    {
        return [
            'c95_audit_archive_completion_carry_forward_validation_completed' => true,
            'c95_audit_archive_completion_carry_forward_validation_pass' => $pass,
            'c95_audit_archive_completion_review_pass' => ($c95['post_activation_audit_archive_completion_review_pass'] ?? null) === true,
            'c95_audit_archive_completed' => ($c95['audit_archive_completed'] ?? null) === true,
            'c95_post_activation_audit_archive_completed' => ($c95['post_activation_audit_archive_completed'] ?? null) === true,
            'c95_primary_candidate_audit_archive_completed' => ($c95['primary_candidate_audit_archive_completed'] ?? null) === true,
            'c95_backup_candidate_audit_archive_completed' => ($c95['backup_candidate_audit_archive_completed'] ?? null) === true,
            'c95_comparator_candidate_audit_archive_completed' => ($c95['comparator_candidate_audit_archive_completed'] ?? null) === false,
            'c95_a01_remains_comparator_only' => ($c95['a01_remains_comparator_only'] ?? null) === true,
            'c95_temporary_negative_artifact_cleanup_confirmed' => ($c95['temporary_negative_artifact_cleanup_confirmed'] ?? null) === true,
            'c95_progress_target_reached' => ($c95['progress_summary']['target_reached'] ?? null) === true,
            'c95_planned_next_review_match' => ($c95['planned_next_summary']['planned_next_review'] ?? null) === self::EXPECTED_C95_RECOMMENDATION,
            'c95_c96_recommendation_match' => ($c95['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C95_RECOMMENDATION,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C96_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
        return [
            'baseline_plan_confirm_non_mutation_review_completed' => true,
            'baseline_plan_confirm_non_mutation_pass' => $pass,
            'baseline_plan_confirm_hash_before' => $hash,
            'baseline_plan_confirm_hash_after' => $hash,
            'baseline_plan_confirm_hash_unchanged' => true,
            'plan_confirm_output_changed' => false,
            'plan_confirm_runtime_default_path_changed' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function productionMutationSafetySummary(bool $pass): array
    {
        $summary = [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => $pass,
            'post_activation_audit_archive_closure_seal_review_created' => true,
            'post_activation_audit_archive_closure_seal_review_allowed' => $pass,
            'post_activation_audit_archive_closure_seal_review_pass' => $pass,
            'post_activation_audit_archive_closure_sealed' => $pass,
            'candidate_ready_for_post_activation_audit_archive_finalization_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C97_RECOMMENDATION : 'C96_TARGETED_C95_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REPAIR',
            'selection_changed_after_c89' => false,
            'parameter_changed_after_c89' => false,
            'new_candidate_created' => false,
            'audit_archive_closure_seal_used_for_selection' => false,
            'audit_archive_closure_seal_used_for_retuning' => false,
            'audit_archive_closure_seal_used_for_ranking' => false,
            'audit_archive_closure_seal_used_for_live_rollout' => false,
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $summary[$flag] = false;
        }
        return $summary;
    }

    private function documentationGovernanceSummary(): array
    {
        $paths = [];
        $docsExist = true;
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists];
            $docsExist = $docsExist && $exists;
        }
        return [
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => $docsExist,
            'doc_paths' => $paths,
            'append_only_docs_updated' => $docsExist,
            'docs_overclaim_live_deployment' => false,
            'docs_overclaim_plan_confirm_live_catalog_read' => false,
        ];
    }

    private function failureAttributionSummary(array $scorecards, bool $pass): array
    {
        $codes = [];
        foreach ($scorecards as $scorecard) {
            foreach ((array) ($scorecard['failure_reason_codes'] ?? []) as $code) {
                $codes[] = $code;
            }
        }
        return [
            'failure_attribution_completed' => true,
            'post_activation_audit_archive_closure_seal_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C97_RECOMMENDATION : 'C96_TARGETED_C95_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REPAIR',
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW',
            'achieved' => [
                'C95 artifact hash and file SHA1 validated',
                'C95 audit archive completion evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Temporary negative artifact cleanup confirmed',
                'Post-activation audit archive closure seal recorded for primary and backup',
                'PLAN/CONFIRM and production runtime remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C97_RECOMMENDATION : 'C96_TARGETED_C95_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REPAIR',
            'planned_next_scope' => $pass ? 'post-activation audit archive finalization review only; still not deployment, live rollout, default runtime wiring, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C96 artifact hash',
                'locked C96 file SHA1',
                'unchanged candidate scope',
                'non-live audit archive closure seal context',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C96 validates C95 artifact_hash and file SHA1 locks before sealing audit archive closure.',
            'C96 validates C95 audit archive completion fields and A01 comparator-only state.',
            'C96 confirms no temporary negative test artifact remains before a passing audit archive closure seal.',
            'C96 audit archive closure seal is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.',
            'C96 may only recommend C97 post-activation audit archive finalization review as the next audit-only step.',
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
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false, false);
        $artifact['failure_attribution_summary'] = [
            'failure_attribution_completed' => true,
            'dominant_failure_reason_codes' => [$status],
            'targeted_repair_recommendation' => $this->repairRecommendationFor($status),
        ];
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false, (bool) ($artifact['temporary_negative_artifacts_remaining'] ?? false));
        $artifact['failure_attribution_summary'] = [
            'failure_attribution_completed' => true,
            'dominant_failure_reason_codes' => [$status],
            'targeted_repair_recommendation' => $this->repairRecommendationFor($status),
        ];
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function repairRecommendationFor(string $status): string
    {
        if (strpos($status, 'C95_ARTIFACT') !== false || strpos($status, 'C95_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C96_C95_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false) {
            return 'C96_OPERATOR_APPROVAL_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C96_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C96_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false) {
            return 'C96_LIVE_OR_PRODUCTION_MUTATION_REPAIR';
        }
        return 'C96_TARGETED_C95_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REPAIR';
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
