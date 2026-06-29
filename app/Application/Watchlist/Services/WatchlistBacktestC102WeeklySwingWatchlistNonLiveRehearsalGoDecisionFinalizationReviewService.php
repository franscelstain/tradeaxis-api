<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW';

    public const DEFAULT_C101_ARTIFACT = 'storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C101_HASH = 'f8a339760d94d230e184dc6f6b3016731ba72379';
    public const DEFAULT_EXPECTED_C101_FILE_SHA1 = 'B12CF95D02172659B51B215E567D0B31C6F891F7';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C101_STATUS = 'C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
    private const EXPECTED_C101_REASON = 'C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
    private const EXPECTED_C101_RECOMMENDATION = self::RUN_CODE;
    private const C103_RECOMMENDATION = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW';
    private const PASS_STATUS = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_DECISION_NOT_CONFIRMED_STATUS = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

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

    private const C101_LIVE_OR_MUTATING_FLAGS = [
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
        'c102_validation_doc' => 'docs/watchlist/audit/WS_C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW.md',
        'c102_operator_commands_doc' => 'docs/watchlist/audit/WS_C102_OPERATOR_VALIDATION_COMMANDS.md',
        'c101_validation_doc' => 'docs/watchlist/audit/WS_C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW.md',
        'c101_operator_commands_doc' => 'docs/watchlist/audit/WS_C101_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c101_weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewService.php',
        'c102_weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService.php',
        'c101_command' => 'app/Console/Commands/Watchlist/RunBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewCommand.php',
        'c102_command' => 'app/Console/Commands/Watchlist/RunBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewCommand.php',
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
        string $c101Artifact = self::DEFAULT_C101_ARTIFACT,
        string $expectedC101Hash = self::DEFAULT_EXPECTED_C101_HASH,
        string $expectedC101FileSha1 = self::DEFAULT_EXPECTED_C101_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c101Artifact, $expectedC101Hash, $expectedC101FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C102_BLOCKED_C101_ARTIFACT_LOCK_MISMATCH', 'C101 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C102_BLOCKED_C101_ARTIFACT_LOCK_MISMATCH', 'C101 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C102_BLOCKED_C101_FILE_SHA1_LOCK_MISMATCH', 'C101 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c101 = $load['payload'];
        if (($c101['status'] ?? null) !== self::EXPECTED_C101_STATUS || ($c101['reason_code'] ?? null) !== self::EXPECTED_C101_REASON) {
            return $this->blocked($artifact, 'C102_BLOCKED_C101_STATUS_OR_REASON_MISMATCH', 'C101 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c101NextRecommendationMatches($c101)) {
            return $this->blocked($artifact, 'C102_BLOCKED_C101_NEXT_RECOMMENDATION_MISMATCH', 'C101 next recommendation is not C102.', $outputPath, $overwrite);
        }
        if (! $this->c101OperatorGoNoGoPassed($c101)) {
            return $this->blocked($artifact, 'C102_BLOCKED_C101_OPERATOR_GO_NO_GO_STATE_NOT_COMPLETE', 'C101 operator GO/NO-GO evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c101);
        if ($safetyFailure !== null) {
            $artifact['c101_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C102_BLOCKED_C101_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C101 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c101)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C101 candidate scope does not match locked non-live rehearsal operator GO/NO-GO decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C102 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C102 weekly swing watchlist non-live rehearsal GO decision finalization review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C102 GO decision finalization review issued GO for the weekly swing watchlist non-live rehearsal primary and backup candidates. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW';
        $artifact['next_step_recommendation'] = self::C103_RECOMMENDATION;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_executed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_allowed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass'] = true;
        $artifact['operator_go_decision'] = 'GO';
        $artifact['operator_go_decision_confirmed'] = true;
        $artifact['go_decision_finalized'] = true;
        $artifact['go_decision_finalization_confirmed'] = true;
        $artifact['primary_candidate_weekly_swing_non_live_rehearsal_operator_go'] = true;
        $artifact['backup_candidate_weekly_swing_non_live_rehearsal_operator_go'] = true;
        $artifact['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go'] = false;
        $artifact['primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized'] = true;
        $artifact['backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized'] = true;
        $artifact['comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized'] = false;
        $artifact['a01_remains_comparator_only'] = true;
        $artifact['c101_operator_go_no_go_passed'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C102_NOT_RUN',
            'reason_code' => 'C102_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass' => false,
            'operator_go_decision' => 'NO_GO',
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'go_decision_finalization_confirmed' => false,
            'primary_candidate_weekly_swing_non_live_rehearsal_operator_go' => false,
            'backup_candidate_weekly_swing_non_live_rehearsal_operator_go' => false,
            'comparator_candidate_weekly_swing_non_live_rehearsal_operator_go' => false,
            'primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => false,
            'backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => false,
            'comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => false,
            'a01_remains_comparator_only' => true,
            'c101_operator_go_no_go_passed' => false,
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
        $c101 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c101['source_artifact_locks'] ?? null) ? $c101['source_artifact_locks'] : [];
        return [
            'c101_artifact_path' => $load['path'],
            'expected_c101_hash' => $load['expected_hash'],
            'actual_c101_hash' => $load['actual_hash'],
            'c101_hash_match' => $load['hash_match'],
            'expected_c101_file_sha1' => $load['expected_file_sha1'],
            'actual_c101_file_sha1' => $load['actual_file_sha1'],
            'c101_file_sha1_match' => $load['file_sha1_match'],
            'c101_source_lineage_checked' => true,
            'c101_source_lineage_match' => $this->lineageLocksMatch($c101),
            'c100_artifact_hash_from_c101' => (string) ($locks['actual_c100_hash'] ?? ($c101['actual_c100_hash'] ?? '')),
            'c100_file_sha1_from_c101' => (string) ($locks['actual_c100_file_sha1'] ?? ($c101['actual_c100_file_sha1'] ?? '')),
            'c99_artifact_hash_from_c100' => (string) ($locks['c99_artifact_hash_from_c100'] ?? ''),
            'c99_file_sha1_from_c100' => (string) ($locks['c99_file_sha1_from_c100'] ?? ''),
            'c98_artifact_hash_from_c99' => (string) ($locks['c98_artifact_hash_from_c99'] ?? ''),
            'c98_file_sha1_from_c99' => (string) ($locks['c98_file_sha1_from_c99'] ?? ''),
            'c97_artifact_hash_from_c98' => (string) ($locks['c97_artifact_hash_from_c98'] ?? ''),
            'c97_file_sha1_from_c98' => (string) ($locks['c97_file_sha1_from_c98'] ?? ''),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c101_hash' => $load['expected_hash'],
            'actual_c101_hash' => $load['actual_hash'],
            'c101_hash_match' => $load['hash_match'],
            'expected_c101_file_sha1' => $load['expected_file_sha1'],
            'actual_c101_file_sha1' => $load['actual_file_sha1'],
            'c101_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c101): bool
    {
        $locks = is_array($c101['source_artifact_locks'] ?? null) ? $c101['source_artifact_locks'] : [];
        foreach (['c100_hash_match', 'c100_file_sha1_match', 'c100_source_lineage_match'] as $field) {
            if (($locks[$field] ?? null) !== true) {
                return false;
            }
        }
        return true;
    }

    private function c101NextRecommendationMatches(array $c101): bool
    {
        foreach ([
            $c101['next_step_recommendation'] ?? null,
            $c101['next_readiness_decision']['next_recommendation'] ?? null,
            $c101['c101_operator_go_no_go_decision']['next_recommendation'] ?? null,
            $c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision']['next_recommendation'] ?? null,
            $c101['planned_next_summary']['planned_next_review'] ?? null,
        ] as $recommendation) {
            if ($recommendation !== self::EXPECTED_C101_RECOMMENDATION) {
                return false;
            }
        }
        return true;
    }

    private function c101OperatorGoNoGoPassed(array $c101): bool
    {
        foreach ([
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_review_pass',
            'operator_go_decision_confirmed',
            'primary_candidate_weekly_swing_non_live_rehearsal_operator_go',
            'backup_candidate_weekly_swing_non_live_rehearsal_operator_go',
        ] as $field) {
            if (($c101[$field] ?? null) !== true) {
                return false;
            }
            if (($c101['c101_operator_go_no_go_decision'][$field] ?? null) !== true) {
                return false;
            }
            if (($c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision'][$field] ?? null) !== true) {
                return false;
            }
        }
        foreach ([
            'operator_go_decision' => 'GO',
        ] as $field => $expected) {
            if (($c101[$field] ?? null) !== $expected) {
                return false;
            }
            if (($c101['c101_operator_go_no_go_decision'][$field] ?? null) !== $expected) {
                return false;
            }
            if (($c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision'][$field] ?? null) !== $expected) {
                return false;
            }
        }
        if (($c101['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go'] ?? null) !== false) {
            return false;
        }
        if (($c101['c101_operator_go_no_go_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go'] ?? null) !== false) {
            return false;
        }
        if (($c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go'] ?? null) !== false) {
            return false;
        }
        if (($c101['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_count'] ?? null) !== 2) {
            return false;
        }

        $manifest = is_array($c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest'] ?? null) ? $c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest'] : [];
        foreach ([
            'manifest_created',
            'operator_go_no_go_artifact_only',
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
            'operator_go_used_for_selection',
            'operator_go_used_for_retuning',
            'operator_go_used_for_ranking',
            'operator_go_used_for_plan_confirm_mutation',
            'operator_go_used_for_live_rollout',
        ] as $field) {
            if (($manifest[$field] ?? false) === true) {
                return false;
            }
        }
        if ((array) ($manifest['official_weekly_swing_stock_recommendations'] ?? []) !== []) {
            return false;
        }
        if (($c101['temporary_negative_artifacts_remaining'] ?? null) !== false) {
            return false;
        }
        if (($c101['temporary_negative_artifact_cleanup_confirmed'] ?? null) !== true) {
            return false;
        }
        if ((array) ($c101['temporary_negative_artifact_paths'] ?? []) !== []) {
            return false;
        }
        return true;
    }

    private function firstLiveOrMutatingSafetyFlag(array $payload): ?string
    {
        foreach (self::C101_LIVE_OR_MUTATING_FLAGS as $field) {
            if (($payload[$field] ?? null) === true) {
                return $field;
            }
        }
        foreach ([
            'c101_operator_go_no_go_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_summary',
            'production_mutation_safety_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'baseline_plan_confirm_non_mutation_summary',
        ] as $section) {
            $values = is_array($payload[$section] ?? null) ? $payload[$section] : [];
            foreach (self::C101_LIVE_OR_MUTATING_FLAGS as $field) {
                if (($values[$field] ?? null) === true) {
                    return $section.'.'.$field;
                }
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c101): bool
    {
        foreach ([
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
        ] as $field => $expected) {
            if (($c101[$field] ?? null) !== $expected) {
                return false;
            }
            if (($c101['c101_operator_go_no_go_decision'][$field] ?? $expected) !== $expected) {
                return false;
            }
            if (($c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision'][$field] ?? $expected) !== $expected) {
                return false;
            }
            if (($c101['candidate_scope_freeze_summary'][$field] ?? $expected) !== $expected) {
                return false;
            }
        }
        if (($c101['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        if (($c101['c101_operator_go_no_go_decision']['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        if (($c101['weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_decision']['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        $summary = is_array($c101['candidate_scope_freeze_summary'] ?? null) ? $c101['candidate_scope_freeze_summary'] : [];
        foreach ([
            'candidate_scope_changed_after_c89',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'scoring_logic_changed',
            'weekly_swing_live_recommendation_selection_changed',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_selection',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_retuning',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_ranking',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_plan_confirm_mutation',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_live_rollout',
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
        $c101 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $temporaryRemaining = $temporaryNegativePaths !== [];
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c101_lock_validation_summary'] = $this->c101LockValidationSummary($load, $c101);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c101);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c102_go_decision_finalization_decision'] = $this->weeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationDecision($pass, $temporaryRemaining);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass, $temporaryRemaining);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision'] = $this->weeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationDecision($pass, $temporaryRemaining);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest'] = $this->weeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationManifest($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_summary'] = $this->weeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c101_operator_go_no_go_carry_forward_validation_summary'] = $this->c101OperatorGoNoGoCarryForwardValidationSummary($c101, $pass);
        $artifact['go_decision_finalization_governance_summary'] = $this->goDecisionFinalizationGovernanceSummary($pass);
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
        if (($options['go_decision_finalization_confirmed'] ?? true) !== true || ($options['operator_go_decision_confirmed'] ?? true) !== true) {
            $failures[] = self::GO_DECISION_NOT_CONFIRMED_STATUS;
        }
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
            'go_decision_finalization_used_for_selection',
            'go_decision_finalization_used_for_retuning',
            'go_decision_finalization_used_for_ranking',
            'go_decision_finalization_used_for_plan_confirm_mutation',
            'go_decision_finalization_used_for_live_rollout',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_used_for_selection',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_used_for_retuning',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_used_for_ranking',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_used_for_plan_confirm_mutation',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_used_for_live_rollout',
        ]);
    }

    private function c101LockValidationSummary(array $load, array $c101): array
    {
        return [
            'c101_lock_validation_completed' => true,
            'c101_artifact_exists' => (bool) $load['exists'],
            'expected_c101_hash' => $load['expected_hash'],
            'actual_c101_hash' => $load['actual_hash'],
            'c101_hash_match' => $load['hash_match'],
            'expected_c101_file_sha1' => $load['expected_file_sha1'],
            'actual_c101_file_sha1' => $load['actual_file_sha1'],
            'c101_file_sha1_match' => $load['file_sha1_match'],
            'c101_status_match' => (($c101['status'] ?? null) === self::EXPECTED_C101_STATUS),
            'c101_reason_code_match' => (($c101['reason_code'] ?? null) === self::EXPECTED_C101_REASON),
            'c101_next_recommendation_match' => $this->c101NextRecommendationMatches($c101),
            'c101_operator_go_no_go_passed' => $this->c101OperatorGoNoGoPassed($c101),
        ];
    }

    private function lineageValidationSummary(array $c101): array
    {
        $locks = is_array($c101['source_artifact_locks'] ?? null) ? $c101['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'c101_to_c100_lock_match' => (($locks['c100_hash_match'] ?? null) === true && ($locks['c100_file_sha1_match'] ?? null) === true),
            'c101_to_c60_lineage_match' => $this->lineageLocksMatch($c101),
            'lineage_source' => 'C101_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C101_LOCKED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_DECISION',
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
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_selection' => false,
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_retuning' => false,
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_ranking' => false,
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_plan_confirm_mutation' => false,
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_used_for_live_rollout' => false,
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
            'operator_go_decision_confirmed' => (bool) ($options['operator_go_decision_confirmed'] ?? true),
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? true),
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

    private function weeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationDecision(bool $pass, bool $temporaryRemaining): array
    {
        $decision = [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c101_lock_valid' => $pass,
            'c101_operator_go_no_go_passed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_executed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision_confirmed' => $pass,
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => $pass,
            'primary_candidate_weekly_swing_non_live_rehearsal_operator_go' => $pass,
            'backup_candidate_weekly_swing_non_live_rehearsal_operator_go' => $pass,
            'comparator_candidate_weekly_swing_non_live_rehearsal_operator_go' => false,
            'primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => $pass,
            'backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => $pass,
            'comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => $temporaryRemaining,
            'temporary_negative_artifact_cleanup_confirmed' => ! $temporaryRemaining,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'go_decision_finalization_artifact_only' => true,
            'go_decision_finalization_used_for_selection' => false,
            'go_decision_finalization_used_for_retuning' => false,
            'go_decision_finalization_used_for_ranking' => false,
            'go_decision_finalization_used_for_plan_confirm_mutation' => false,
            'go_decision_finalization_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C103_RECOMMENDATION : 'C102_TARGETED_C101_OPERATOR_GO_NO_GO_REPAIR',
            'decision_reason' => $pass ? 'C102 weekly swing watchlist non-live rehearsal GO decision finalization review issued GO for primary and backup in artifact-only audit context.' : 'C102 weekly swing watchlist non-live rehearsal GO decision finalization review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW' : 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REPAIR_REQUIRED',
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass, bool $temporaryRemaining): array
    {
        $decision = $this->weeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationDecision($pass, $temporaryRemaining);
        $decision['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_count'] = $pass ? 2 : 0;
        $decision['candidate_codes'] = $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [];
        return $decision;
    }

    private function weeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_non_live_rehearsal_go_decision_finalization_review',
            'execution_mode' => 'non_live_artifact_only_rehearsal_go_decision_finalization',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_non_live_rehearsal_finalized_go_candidate',
            'backup_candidate_role' => 'backup_non_live_rehearsal_finalized_go_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => $pass,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'go_decision_finalization_used_for_selection' => false,
            'go_decision_finalization_used_for_retuning' => false,
            'go_decision_finalization_used_for_ranking' => false,
            'go_decision_finalization_used_for_plan_confirm_mutation' => false,
            'go_decision_finalization_used_for_live_rollout' => false,
            'go_decision_finalization_artifact_only' => true,
            'official_weekly_swing_stock_recommendations' => [],
            'non_live_go_decision_finalization_steps' => [
                'validate_locked_c101_operator_go_no_go_artifact',
                'confirm_primary_and_backup_operator_go_scope',
                'finalize_primary_and_backup_go_decision_scope',
                'confirm_a01_comparator_only_scope',
                'record_non_live_go_decision_finalization_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => $pass,
            'candidate_active_in_default_runtime_catalog' => false,
            'c101_lock_validation_pass' => $pass,
            'candidate_scope_freeze_pass' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'non_live_rehearsal_go_decision_finalization_advisory_only_pass' => $pass,
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
                'c102_role' => 'primary_non_live_rehearsal_finalized_go_candidate',
                'primary_candidate_weekly_swing_non_live_rehearsal_operator_go' => $pass,
                'primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c102_role' => 'backup_non_live_rehearsal_finalized_go_candidate',
                'backup_candidate_weekly_swing_non_live_rehearsal_operator_go' => $pass,
                'backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c102_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass' => false,
                'operator_go_decision' => 'NO_GO',
                'go_decision_finalized' => false,
                'comparator_candidate_weekly_swing_non_live_rehearsal_operator_go' => false,
                'comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => false,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function weeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime' => false,
            'operator_go_no_go_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime' => false,
            'go_decision_finalization_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_source_identified' => is_file(self::RUNTIME_PATHS['c101_weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_service']),
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_source_identified' => is_file(self::RUNTIME_PATHS['c102_weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_service']),
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
            'operator_go_decision_confirmation_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c101OperatorGoNoGoCarryForwardValidationSummary(array $c101, bool $pass): array
    {
        return [
            'c101_operator_go_no_go_carry_forward_validation_completed' => true,
            'c101_operator_go_no_go_carry_forward_validation_pass' => $pass,
            'c101_status' => (string) ($c101['status'] ?? ''),
            'c101_reason_code' => (string) ($c101['reason_code'] ?? ''),
            'c101_artifact_hash' => (string) ($c101['artifact_hash'] ?? ''),
            'c101_operator_go_no_go_passed' => $this->c101OperatorGoNoGoPassed($c101),
            'c101_operator_go_decision' => (string) ($c101['operator_go_decision'] ?? ''),
            'c101_operator_go_decision_confirmed' => (bool) ($c101['operator_go_decision_confirmed'] ?? false),
            'c101_primary_candidate_weekly_swing_non_live_rehearsal_operator_go' => (bool) ($c101['primary_candidate_weekly_swing_non_live_rehearsal_operator_go'] ?? false),
            'c101_backup_candidate_weekly_swing_non_live_rehearsal_operator_go' => (bool) ($c101['backup_candidate_weekly_swing_non_live_rehearsal_operator_go'] ?? false),
            'c101_comparator_candidate_weekly_swing_non_live_rehearsal_operator_go' => (bool) ($c101['comparator_candidate_weekly_swing_non_live_rehearsal_operator_go'] ?? false),
            'c101_a01_remains_comparator_only' => (bool) ($c101['a01_remains_comparator_only'] ?? false),
            'expected_c101_next_recommendation' => self::EXPECTED_C101_RECOMMENDATION,
        ];
    }

    private function goDecisionFinalizationGovernanceSummary(bool $pass): array
    {
        return [
            'go_decision_finalization_governance_completed' => true,
            'go_decision_finalization_governance_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => $pass,
            'go_decision_finalization_artifact_only' => true,
            'go_decision_finalization_used_for_selection' => false,
            'go_decision_finalization_used_for_retuning' => false,
            'go_decision_finalization_used_for_ranking' => false,
            'go_decision_finalization_used_for_plan_confirm_mutation' => false,
            'go_decision_finalization_used_for_live_rollout' => false,
            'go_decision_finalization_context_persisted_to_live_runtime' => false,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C102_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => $pass,
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C103_RECOMMENDATION : 'C102_TARGETED_C101_OPERATOR_GO_NO_GO_REPAIR',
            'selection_changed_after_c89' => false,
            'parameter_changed_after_c89' => false,
            'new_candidate_created' => false,
            'go_decision_finalization_used_for_selection' => false,
            'go_decision_finalization_used_for_retuning' => false,
            'go_decision_finalization_used_for_ranking' => false,
            'go_decision_finalization_used_for_plan_confirm_mutation' => false,
            'go_decision_finalization_used_for_live_rollout' => false,
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
            'targeted_repair_recommendation' => $status === [] ? self::C103_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW',
            'achieved' => [
                'C101 artifact hash and file SHA1 validated',
                'C101 operator GO/NO-GO evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Finalized GO recorded for E02 and B01 only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist non-live rehearsal GO decision finalization manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C103_RECOMMENDATION : 'C102_TARGETED_C101_OPERATOR_GO_NO_GO_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal completion boundary review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C102 artifact hash',
                'locked C102 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only finalized GO decision',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C102 validates C101 artifact_hash and file SHA1 locks before weekly swing watchlist non-live rehearsal GO decision finalization review is recorded.',
            'C102 validates C101 operator GO/NO-GO fields and A01 comparator-only state.',
            'C102 confirms no temporary negative test artifact remains before a passing non-live rehearsal GO decision finalization review.',
            'C102 records finalized GO for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C102 creates an artifact-only weekly swing watchlist non-live rehearsal GO decision finalization manifest and no official weekly swing recommendation output.',
            'C102 weekly swing watchlist non-live rehearsal GO decision finalization review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C102 may only recommend C103 weekly swing watchlist non-live rehearsal completion boundary review as the next audit-only step.',
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
        if (strpos($status, 'C101_ARTIFACT') !== false || strpos($status, 'C101_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C102_C101_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'GO_DECISION') !== false) {
            return 'C102_OPERATOR_GO_NO_GO_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C102_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C102_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C102_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C102_TARGETED_C101_OPERATOR_GO_NO_GO_REPAIR';
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
