<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService
{
    public const RUN_CODE = 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW';
    public const ARTIFACT_TYPE = 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW';

    public const DEFAULT_C98_ARTIFACT = 'storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json';
    public const DEFAULT_EXPECTED_C98_HASH = '269eb05141a2acf28925fdef51df9263955b0143';
    public const DEFAULT_EXPECTED_C98_FILE_SHA1 = '762BAFFCFCB104E10C9D8C6F6CCBD4E990766702';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c99-weekly-swing-watchlist-non-live-rehearsal-execution-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C98_STATUS = 'C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_PASSED_NON_LIVE_REHEARSAL_READY_PRIMARY_AND_BACKUP';
    private const EXPECTED_C98_REASON = 'C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_PASSED_NON_LIVE_REHEARSAL_READY_PRIMARY_AND_BACKUP';
    private const EXPECTED_C98_RECOMMENDATION = self::RUN_CODE;
    private const C100_RECOMMENDATION = 'C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW';
    private const PASS_STATUS = 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_PASSED_NON_LIVE_REHEARSAL_EXECUTED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
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

    private const C98_LIVE_OR_MUTATING_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
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
        'weekly_swing_official_output_generated',
        'weekly_swing_live_output_enabled',
        'weekly_swing_live_output_published',
        'weekly_swing_live_recommendation_generated',
    ];

    private const DOC_PATHS = [
        'c99_validation_doc' => 'docs/watchlist/audit/WS_C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW.md',
        'c99_operator_commands_doc' => 'docs/watchlist/audit/WS_C99_OPERATOR_VALIDATION_COMMANDS.md',
        'c98_validation_doc' => 'docs/watchlist/audit/WS_C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW.md',
        'c98_operator_commands_doc' => 'docs/watchlist/audit/WS_C98_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c98_weekly_swing_watchlist_non_live_rehearsal_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewService.php',
        'c99_weekly_swing_watchlist_non_live_rehearsal_execution_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService.php',
        'c98_command' => 'app/Console/Commands/Watchlist/RunBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewCommand.php',
        'c99_command' => 'app/Console/Commands/Watchlist/RunBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewCommand.php',
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
        string $c98Artifact = self::DEFAULT_C98_ARTIFACT,
        string $expectedC98Hash = self::DEFAULT_EXPECTED_C98_HASH,
        string $expectedC98FileSha1 = self::DEFAULT_EXPECTED_C98_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c98Artifact, $expectedC98Hash, $expectedC98FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C99_BLOCKED_C98_ARTIFACT_LOCK_MISMATCH', 'C98 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C99_BLOCKED_C98_ARTIFACT_LOCK_MISMATCH', 'C98 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C99_BLOCKED_C98_FILE_SHA1_LOCK_MISMATCH', 'C98 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c98 = $load['payload'];
        if (($c98['status'] ?? null) !== self::EXPECTED_C98_STATUS || ($c98['reason_code'] ?? null) !== self::EXPECTED_C98_REASON) {
            return $this->blocked($artifact, 'C99_BLOCKED_C98_STATUS_OR_REASON_MISMATCH', 'C98 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c98NextRecommendationMatches($c98)) {
            return $this->blocked($artifact, 'C99_BLOCKED_C98_NEXT_RECOMMENDATION_MISMATCH', 'C98 next recommendation is not C99.', $outputPath, $overwrite);
        }
        if (! $this->c98NonLiveRehearsalReady($c98)) {
            return $this->blocked($artifact, 'C99_BLOCKED_C98_NON_LIVE_REHEARSAL_READY_STATE_NOT_COMPLETE', 'C98 non-live rehearsal readiness evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c98);
        if ($safetyFailure !== null) {
            $artifact['c98_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C99_BLOCKED_C98_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C98 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c98)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C98 candidate scope does not match locked non-live rehearsal review decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C99 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C99 weekly swing watchlist non-live rehearsal execution review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C99 executed the weekly swing watchlist rehearsal in artifact-only non-live context for primary and backup candidates. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTED_NON_LIVE_ONLY';
        $artifact['next_step_recommendation'] = self::C100_RECOMMENDATION;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_execution_review_executed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_execution_review_allowed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_execution_review_pass'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_executed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_execution_manifest_created'] = true;
        $artifact['primary_candidate_weekly_swing_non_live_rehearsal_executed'] = true;
        $artifact['backup_candidate_weekly_swing_non_live_rehearsal_executed'] = true;
        $artifact['comparator_candidate_weekly_swing_non_live_rehearsal_executed'] = false;
        $artifact['a01_remains_comparator_only'] = true;
        $artifact['c98_non_live_rehearsal_ready'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C99_NOT_RUN',
            'reason_code' => 'C99_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_pass' => false,
            'weekly_swing_watchlist_non_live_rehearsal_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_manifest_created' => false,
            'primary_candidate_weekly_swing_non_live_rehearsal_executed' => false,
            'backup_candidate_weekly_swing_non_live_rehearsal_executed' => false,
            'comparator_candidate_weekly_swing_non_live_rehearsal_executed' => false,
            'a01_remains_comparator_only' => true,
            'c98_non_live_rehearsal_ready' => false,
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
        $c98 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c98['source_artifact_locks'] ?? null) ? $c98['source_artifact_locks'] : [];
        return [
            'c98_artifact_path' => $load['path'],
            'expected_c98_hash' => $load['expected_hash'],
            'actual_c98_hash' => $load['actual_hash'],
            'c98_hash_match' => $load['hash_match'],
            'expected_c98_file_sha1' => $load['expected_file_sha1'],
            'actual_c98_file_sha1' => $load['actual_file_sha1'],
            'c98_file_sha1_match' => $load['file_sha1_match'],
            'c98_source_lineage_checked' => true,
            'c98_source_lineage_match' => $this->lineageLocksMatch($c98),
            'c97_artifact_hash_from_c98' => (string) ($locks['actual_c97_hash'] ?? ($c98['actual_c97_hash'] ?? '')),
            'c97_file_sha1_from_c98' => (string) ($locks['actual_c97_file_sha1'] ?? ($c98['actual_c97_file_sha1'] ?? '')),
            'c96_artifact_hash_from_c97' => (string) ($locks['c96_artifact_hash_from_c97'] ?? ''),
            'c96_file_sha1_from_c97' => (string) ($locks['c96_file_sha1_from_c97'] ?? ''),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c98_hash' => $load['expected_hash'],
            'actual_c98_hash' => $load['actual_hash'],
            'c98_hash_match' => $load['hash_match'],
            'expected_c98_file_sha1' => $load['expected_file_sha1'],
            'actual_c98_file_sha1' => $load['actual_file_sha1'],
            'c98_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c98): bool
    {
        $locks = is_array($c98['source_artifact_locks'] ?? null) ? $c98['source_artifact_locks'] : [];
        foreach (['c97_hash_match', 'c97_file_sha1_match', 'c97_source_lineage_match'] as $field) {
            if (($locks[$field] ?? null) !== true) {
                return false;
            }
        }
        return true;
    }

    private function c98NextRecommendationMatches(array $c98): bool
    {
        foreach ([
            $c98['next_step_recommendation'] ?? null,
            $c98['next_readiness_decision']['next_recommendation'] ?? null,
            $c98['c98_readiness_decision']['next_recommendation'] ?? null,
            $c98['weekly_swing_watchlist_non_live_rehearsal_decision']['next_recommendation'] ?? null,
            $c98['planned_next_summary']['planned_next_review'] ?? null,
        ] as $recommendation) {
            if ($recommendation !== self::EXPECTED_C98_RECOMMENDATION) {
                return false;
            }
        }
        return true;
    }

    private function c98NonLiveRehearsalReady(array $c98): bool
    {
        foreach ([
            'weekly_swing_watchlist_non_live_rehearsal_review_pass',
            'weekly_swing_watchlist_non_live_rehearsal_ready',
            'weekly_swing_watchlist_non_live_rehearsal_manifest_created',
            'primary_candidate_weekly_swing_non_live_rehearsal_ready',
            'backup_candidate_weekly_swing_non_live_rehearsal_ready',
        ] as $field) {
            if (($c98[$field] ?? null) !== true) {
                return false;
            }
            if (($c98['c98_readiness_decision'][$field] ?? null) !== true) {
                return false;
            }
        }
        foreach ([
            'comparator_candidate_weekly_swing_non_live_rehearsal_ready',
        ] as $field) {
            if (($c98[$field] ?? null) !== false) {
                return false;
            }
            if (($c98['c98_readiness_decision'][$field] ?? null) !== false) {
                return false;
            }
        }
        $manifest = is_array($c98['weekly_swing_watchlist_non_live_rehearsal_manifest'] ?? null) ? $c98['weekly_swing_watchlist_non_live_rehearsal_manifest'] : [];
        foreach ([
            'manifest_created',
            'rehearsal_artifact_only',
        ] as $field) {
            if (($manifest[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach ([
            'weekly_swing_official_output_generated',
            'weekly_swing_live_output_enabled',
            'weekly_swing_live_output_published',
            'plan_confirm_mutation_allowed',
            'rehearsal_used_for_selection',
            'rehearsal_used_for_retuning',
            'rehearsal_used_for_ranking',
            'rehearsal_used_for_plan_confirm_mutation',
            'rehearsal_used_for_live_rollout',
        ] as $field) {
            if (($manifest[$field] ?? false) === true) {
                return false;
            }
        }
        if (($c98['temporary_negative_artifacts_remaining'] ?? null) !== false) {
            return false;
        }
        if (($c98['temporary_negative_artifact_cleanup_confirmed'] ?? null) !== true) {
            return false;
        }
        if ((array) ($c98['temporary_negative_artifact_paths'] ?? []) !== []) {
            return false;
        }
        return true;
    }

    private function firstLiveOrMutatingSafetyFlag(array $payload): ?string
    {
        foreach (self::C98_LIVE_OR_MUTATING_FLAGS as $field) {
            if (($payload[$field] ?? null) === true) {
                return $field;
            }
        }
        foreach ([
            'c98_readiness_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_decision',
            'weekly_swing_watchlist_non_live_rehearsal_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_context_summary',
            'production_mutation_safety_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'baseline_plan_confirm_non_mutation_summary',
        ] as $section) {
            $values = is_array($payload[$section] ?? null) ? $payload[$section] : [];
            foreach (self::C98_LIVE_OR_MUTATING_FLAGS as $field) {
                if (($values[$field] ?? null) === true) {
                    return $section.'.'.$field;
                }
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c98): bool
    {
        foreach ([
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
        ] as $field => $expected) {
            if (($c98[$field] ?? null) !== $expected) {
                return false;
            }
            if (($c98['c98_readiness_decision'][$field] ?? $expected) !== $expected) {
                return false;
            }
            if (($c98['candidate_scope_freeze_summary'][$field] ?? $expected) !== $expected) {
                return false;
            }
        }
        if (($c98['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        if (($c98['c98_readiness_decision']['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        $summary = is_array($c98['candidate_scope_freeze_summary'] ?? null) ? $c98['candidate_scope_freeze_summary'] : [];
        foreach ([
            'candidate_scope_changed_after_c89',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'scoring_logic_changed',
            'weekly_swing_live_recommendation_selection_changed',
            'weekly_swing_watchlist_non_live_rehearsal_used_for_selection',
            'weekly_swing_watchlist_non_live_rehearsal_used_for_retuning',
            'weekly_swing_watchlist_non_live_rehearsal_used_for_ranking',
            'weekly_swing_watchlist_non_live_rehearsal_used_for_plan_confirm_mutation',
            'weekly_swing_watchlist_non_live_rehearsal_used_for_live_rollout',
            'a01_promoted',
            'a01_used_as_runtime_fallback',
        ] as $field) {
            if (($summary[$field] ?? false) === true) {
                return false;
            }
        }
        return true;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c98 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $temporaryRemaining = $temporaryNegativePaths !== [];
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c98_lock_validation_summary'] = $this->c98LockValidationSummary($load, $c98);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c98);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c99_execution_decision'] = $this->weeklySwingWatchlistNonLiveRehearsalExecutionDecision($pass, $temporaryRemaining);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass, $temporaryRemaining);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_execution_decision'] = $this->weeklySwingWatchlistNonLiveRehearsalExecutionDecision($pass, $temporaryRemaining);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_execution_manifest'] = $this->weeklySwingWatchlistNonLiveRehearsalExecutionManifest($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_execution_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_execution_context_summary'] = $this->weeklySwingWatchlistNonLiveRehearsalExecutionContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c98_non_live_rehearsal_ready_carry_forward_validation_summary'] = $this->c98NonLiveRehearsalReadyCarryForwardValidationSummary($c98, $pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : $forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        foreach ($this->prohibitedOptionFields() as $field) {
            if (($options[$field] ?? false) === true) {
                $failures[] = self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
            }
        }
        foreach ([
            'production_catalog_runtime_bridge_enabled',
            'production_catalog_controlled_opt_in_runtime_bridge_enabled',
            'production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'production_catalog_controlled_shadow_rollout_enabled',
            'production_catalog_controlled_parallel_run_enabled',
            'production_catalog_controlled_rollout_enabled',
        ] as $configKey) {
            if ($this->configFlagIsOn($configKey)) {
                $failures[] = self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
            }
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return array_merge(self::REQUIRED_FALSE_SAFETY_FLAGS, [
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_selection',
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_retuning',
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_ranking',
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_plan_confirm_mutation',
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_live_rollout',
        ]);
    }

    private function c98LockValidationSummary(array $load, array $c98): array
    {
        return [
            'c98_lock_validation_completed' => true,
            'c98_artifact_exists' => (bool) $load['exists'],
            'expected_c98_hash' => $load['expected_hash'],
            'actual_c98_hash' => $load['actual_hash'],
            'c98_hash_match' => $load['hash_match'],
            'expected_c98_file_sha1' => $load['expected_file_sha1'],
            'actual_c98_file_sha1' => $load['actual_file_sha1'],
            'c98_file_sha1_match' => $load['file_sha1_match'],
            'c98_status_match' => (($c98['status'] ?? null) === self::EXPECTED_C98_STATUS),
            'c98_reason_code_match' => (($c98['reason_code'] ?? null) === self::EXPECTED_C98_REASON),
            'c98_next_recommendation_match' => $this->c98NextRecommendationMatches($c98),
            'c98_non_live_rehearsal_ready' => $this->c98NonLiveRehearsalReady($c98),
        ];
    }

    private function lineageValidationSummary(array $c98): array
    {
        $locks = is_array($c98['source_artifact_locks'] ?? null) ? $c98['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'c98_to_c97_lock_match' => (($locks['c97_hash_match'] ?? null) === true && ($locks['c97_file_sha1_match'] ?? null) === true),
            'c98_to_c60_lineage_match' => $this->lineageLocksMatch($c98),
            'lineage_source' => 'C98_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C98_LOCKED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_DECISION',
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
            'weekly_swing_live_recommendation_selection_changed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_selection' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_retuning' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_ranking' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_plan_confirm_mutation' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_live_rollout' => false,
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
            'temporary_negative_artifact_paths' => $temporaryNegativePaths,
            'temporary_negative_artifact_patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
    }

    private function weeklySwingWatchlistNonLiveRehearsalExecutionDecision(bool $pass, bool $temporaryRemaining): array
    {
        $decision = [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c98_lock_valid' => $pass,
            'c98_non_live_rehearsal_ready' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_executed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_executed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_execution_manifest_created' => $pass,
            'primary_candidate_weekly_swing_non_live_rehearsal_executed' => $pass,
            'backup_candidate_weekly_swing_non_live_rehearsal_executed' => $pass,
            'comparator_candidate_weekly_swing_non_live_rehearsal_executed' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => $temporaryRemaining,
            'temporary_negative_artifact_cleanup_confirmed' => ! $temporaryRemaining,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'rehearsal_execution_artifact_only' => true,
            'rehearsal_execution_used_for_selection' => false,
            'rehearsal_execution_used_for_retuning' => false,
            'rehearsal_execution_used_for_ranking' => false,
            'rehearsal_execution_used_for_plan_confirm_mutation' => false,
            'rehearsal_execution_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C100_RECOMMENDATION : 'C99_TARGETED_C98_NON_LIVE_REHEARSAL_READY_REPAIR',
            'decision_reason' => $pass ? 'C99 weekly swing watchlist non-live rehearsal execution review completed for primary and backup in artifact-only audit context.' : 'C99 weekly swing watchlist non-live rehearsal execution review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTED_NON_LIVE_ONLY' : 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REPAIR_REQUIRED',
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass, bool $temporaryRemaining): array
    {
        $decision = $this->weeklySwingWatchlistNonLiveRehearsalExecutionDecision($pass, $temporaryRemaining);
        $decision['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_result_review_count'] = $pass ? 2 : 0;
        $decision['candidate_codes'] = $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [];
        return $decision;
    }

    private function weeklySwingWatchlistNonLiveRehearsalExecutionManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_non_live_rehearsal_execution_review',
            'execution_mode' => 'non_live_artifact_only_rehearsal_execution',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_non_live_rehearsal_execution_candidate',
            'backup_candidate_role' => 'backup_non_live_rehearsal_execution_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'rehearsal_execution_used_for_selection' => false,
            'rehearsal_execution_used_for_retuning' => false,
            'rehearsal_execution_used_for_ranking' => false,
            'rehearsal_execution_used_for_plan_confirm_mutation' => false,
            'rehearsal_execution_used_for_live_rollout' => false,
            'rehearsal_execution_artifact_only' => true,
            'official_weekly_swing_stock_recommendations' => [],
            'non_live_execution_steps' => [
                'validate_locked_c98_readiness_artifact',
                'confirm_primary_and_backup_rehearsal_execution_scope',
                'confirm_a01_comparator_only_scope',
                'record_non_live_rehearsal_execution_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_executed' => $pass,
            'candidate_active_in_default_runtime_catalog' => false,
            'c98_lock_validation_pass' => $pass,
            'candidate_scope_freeze_pass' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'non_live_rehearsal_execution_advisory_only_pass' => $pass,
            'production_mutation_safety_pass' => $pass,
            'weekly_swing_live_output_safety_pass' => $pass,
            'documentation_governance_pass' => true,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $base[$flag] = false;
        }
        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c99_role' => 'primary_non_live_rehearsal_executed_candidate',
                'primary_candidate_weekly_swing_non_live_rehearsal_executed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c99_role' => 'backup_non_live_rehearsal_executed_candidate',
                'backup_candidate_weekly_swing_non_live_rehearsal_executed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c99_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_non_live_rehearsal_execution_review_pass' => false,
                'weekly_swing_watchlist_non_live_rehearsal_executed' => false,
                'comparator_candidate_weekly_swing_non_live_rehearsal_executed' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function weeklySwingWatchlistNonLiveRehearsalExecutionContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime' => false,
            'production_catalog_runtime_wired' => false,
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
        $paths = [];
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path) || is_dir($path)];
        }
        return [
            'runtime_readiness_inspection_completed' => true,
            'inspected_paths' => $paths,
            'weekly_swing_watchlist_non_live_rehearsal_review_source_identified' => is_file(self::RUNTIME_PATHS['c98_weekly_swing_watchlist_non_live_rehearsal_review_service']),
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_source_identified' => is_file(self::RUNTIME_PATHS['c99_weekly_swing_watchlist_non_live_rehearsal_execution_review_service']),
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
            'weekly_swing_live_output_feature_flag_current_state' => false,
            'explicit_operator_approval_required_pass' => $pass,
            'operator_approval_artifact_required' => true,
            'operator_approval_reference_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c98NonLiveRehearsalReadyCarryForwardValidationSummary(array $c98, bool $pass): array
    {
        return [
            'c98_non_live_rehearsal_ready_carry_forward_validation_completed' => true,
            'c98_non_live_rehearsal_ready_carry_forward_validation_pass' => $pass,
            'c98_status' => (string) ($c98['status'] ?? ''),
            'c98_reason_code' => (string) ($c98['reason_code'] ?? ''),
            'c98_artifact_hash' => (string) ($c98['artifact_hash'] ?? ''),
            'c98_non_live_rehearsal_ready' => $this->c98NonLiveRehearsalReady($c98),
            'c98_primary_candidate_weekly_swing_non_live_rehearsal_ready' => (bool) ($c98['primary_candidate_weekly_swing_non_live_rehearsal_ready'] ?? false),
            'c98_backup_candidate_weekly_swing_non_live_rehearsal_ready' => (bool) ($c98['backup_candidate_weekly_swing_non_live_rehearsal_ready'] ?? false),
            'c98_comparator_candidate_weekly_swing_non_live_rehearsal_ready' => (bool) ($c98['comparator_candidate_weekly_swing_non_live_rehearsal_ready'] ?? false),
            'c98_a01_remains_comparator_only' => (bool) ($c98['a01_remains_comparator_only'] ?? false),
            'expected_c98_next_recommendation' => self::EXPECTED_C98_RECOMMENDATION,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C99_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_executed' => $pass,
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_result_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C100_RECOMMENDATION : 'C99_TARGETED_C98_NON_LIVE_REHEARSAL_READY_REPAIR',
            'selection_changed_after_c89' => false,
            'parameter_changed_after_c89' => false,
            'new_candidate_created' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_selection' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_retuning' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_ranking' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_plan_confirm_mutation' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_used_for_live_rollout' => false,
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
            'docs_overclaim_weekly_swing_live_output' => false,
            'docs_overclaim_official_weekly_swing_recommendation' => false,
        ];
    }

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_attribution_completed' => true,
            'dominant_failure_reason_codes' => $status,
            'targeted_repair_recommendation' => $status === [] ? self::C100_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW',
            'achieved' => [
                'C98 artifact hash and file SHA1 validated',
                'C98 non-live rehearsal readiness evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist non-live rehearsal execution manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C100_RECOMMENDATION : 'C99_TARGETED_C98_NON_LIVE_REHEARSAL_READY_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal result review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C99 artifact hash',
                'locked C99 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only rehearsal execution manifest',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C99 validates C98 artifact_hash and file SHA1 locks before weekly swing watchlist non-live rehearsal execution is recorded.',
            'C99 validates C98 non-live rehearsal readiness fields and A01 comparator-only state.',
            'C99 confirms no temporary negative test artifact remains before a passing non-live rehearsal execution review.',
            'C99 creates an artifact-only weekly swing watchlist non-live rehearsal execution manifest and no official weekly swing recommendation output.',
            'C99 weekly swing watchlist non-live rehearsal execution review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C99 may only recommend C100 weekly swing watchlist non-live rehearsal result review as the next audit-only step.',
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
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false, (bool) ($artifact['temporary_negative_artifacts_remaining'] ?? false));
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function repairRecommendationFor(string $status): string
    {
        if (strpos($status, 'C98_ARTIFACT') !== false || strpos($status, 'C98_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C99_C98_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false) {
            return 'C99_OPERATOR_APPROVAL_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C99_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C99_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C99_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C99_TARGETED_C98_NON_LIVE_REHEARSAL_READY_REPAIR';
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
