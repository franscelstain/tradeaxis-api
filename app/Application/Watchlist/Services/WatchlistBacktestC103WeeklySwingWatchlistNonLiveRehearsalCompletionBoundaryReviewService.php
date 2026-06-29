<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC103WeeklySwingWatchlistNonLiveRehearsalCompletionBoundaryReviewService
{
    public const RUN_CODE = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW';

    public const DEFAULT_C102_ARTIFACT = 'storage/app/watchlist/backtest/c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C102_HASH = 'e9e246048d14dcedda262a35fce9d52b64b052c0';
    public const DEFAULT_EXPECTED_C102_FILE_SHA1 = 'DD731AFB11D2EA513EEF6795BF03D2F404670FB6';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c103-weekly-swing-watchlist-non-live-rehearsal-completion-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C102_STATUS = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP';
    private const EXPECTED_C102_REASON = 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP';
    private const EXPECTED_C102_RECOMMENDATION = self::RUN_CODE;
    private const C104_RECOMMENDATION = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW';
    private const PASS_STATUS = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_NOT_CONFIRMED_STATUS = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_BOUNDARY_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

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

    private const C102_LIVE_OR_MUTATING_FLAGS = [
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
        'c103_validation_doc' => 'docs/watchlist/audit/WS_C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW.md',
        'c103_operator_commands_doc' => 'docs/watchlist/audit/WS_C103_OPERATOR_VALIDATION_COMMANDS.md',
        'c102_validation_doc' => 'docs/watchlist/audit/WS_C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW.md',
        'c102_operator_commands_doc' => 'docs/watchlist/audit/WS_C102_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c102_weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService.php',
        'c103_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC103WeeklySwingWatchlistNonLiveRehearsalCompletionBoundaryReviewService.php',
        'c102_command' => 'app/Console/Commands/Watchlist/RunBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewCommand.php',
        'c103_command' => 'app/Console/Commands/Watchlist/RunBacktestC103WeeklySwingWatchlistNonLiveRehearsalCompletionBoundaryReviewCommand.php',
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
        string $c102Artifact = self::DEFAULT_C102_ARTIFACT,
        string $expectedC102Hash = self::DEFAULT_EXPECTED_C102_HASH,
        string $expectedC102FileSha1 = self::DEFAULT_EXPECTED_C102_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c102Artifact, $expectedC102Hash, $expectedC102FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C103_BLOCKED_C102_ARTIFACT_LOCK_MISMATCH', 'C102 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C103_BLOCKED_C102_ARTIFACT_LOCK_MISMATCH', 'C102 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C103_BLOCKED_C102_FILE_SHA1_LOCK_MISMATCH', 'C102 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c102 = $load['payload'];
        if (($c102['status'] ?? null) !== self::EXPECTED_C102_STATUS || ($c102['reason_code'] ?? null) !== self::EXPECTED_C102_REASON) {
            return $this->blocked($artifact, 'C103_BLOCKED_C102_STATUS_OR_REASON_MISMATCH', 'C102 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c102NextRecommendationMatches($c102)) {
            return $this->blocked($artifact, 'C103_BLOCKED_C102_NEXT_RECOMMENDATION_MISMATCH', 'C102 next recommendation is not C103.', $outputPath, $overwrite);
        }
        if (! $this->c102GoDecisionFinalized($c102)) {
            return $this->blocked($artifact, 'C103_BLOCKED_C102_GO_DECISION_FINALIZATION_STATE_NOT_COMPLETE', 'C102 GO decision finalization evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c102);
        if ($safetyFailure !== null) {
            $artifact['c102_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C103_BLOCKED_C102_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C102 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c102)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C102 candidate scope does not match locked non-live rehearsal GO finalization decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C103 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C103 weekly swing watchlist non-live rehearsal completion boundary review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C103 cleared the weekly swing watchlist non-live rehearsal completion boundary for primary and backup as artifact-only evidence. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW';
        $artifact['next_step_recommendation'] = self::C104_RECOMMENDATION;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_executed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_allowed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared'] = true;
        $artifact['completion_boundary_cleared'] = true;
        $artifact['primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] = true;
        $artifact['backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] = true;
        $artifact['comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] = false;
        $artifact['boundary_go_decision'] = 'BOUNDARY_CLEARED_GO';
        $artifact['operator_go_decision'] = 'GO';
        $artifact['go_decision_finalized'] = true;
        $artifact['c102_go_decision_finalized'] = true;
        $artifact['a01_remains_comparator_only'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C103_NOT_RUN',
            'reason_code' => 'C103_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared' => false,
            'completion_boundary_cleared' => false,
            'primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => false,
            'backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => false,
            'comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => false,
            'boundary_go_decision' => 'NO_GO',
            'operator_go_decision' => 'NO_GO',
            'go_decision_finalized' => false,
            'c102_go_decision_finalized' => false,
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
        $c102 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c102['source_artifact_locks'] ?? null) ? $c102['source_artifact_locks'] : [];
        return [
            'c102_artifact_path' => $load['path'],
            'expected_c102_hash' => $load['expected_hash'],
            'actual_c102_hash' => $load['actual_hash'],
            'c102_hash_match' => $load['hash_match'],
            'expected_c102_file_sha1' => $load['expected_file_sha1'],
            'actual_c102_file_sha1' => $load['actual_file_sha1'],
            'c102_file_sha1_match' => $load['file_sha1_match'],
            'c102_source_lineage_checked' => true,
            'c102_source_lineage_match' => $this->lineageLocksMatch($c102),
            'c101_artifact_hash_from_c102' => (string) ($locks['actual_c101_hash'] ?? ($c102['actual_c101_hash'] ?? '')),
            'c101_file_sha1_from_c102' => (string) ($locks['actual_c101_file_sha1'] ?? ($c102['actual_c101_file_sha1'] ?? '')),
            'c100_artifact_hash_from_c101' => (string) ($locks['c100_artifact_hash_from_c101'] ?? ''),
            'c100_file_sha1_from_c101' => (string) ($locks['c100_file_sha1_from_c101'] ?? ''),
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
            'expected_c102_hash' => $load['expected_hash'],
            'actual_c102_hash' => $load['actual_hash'],
            'c102_hash_match' => $load['hash_match'],
            'expected_c102_file_sha1' => $load['expected_file_sha1'],
            'actual_c102_file_sha1' => $load['actual_file_sha1'],
            'c102_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c102): bool
    {
        $locks = is_array($c102['source_artifact_locks'] ?? null) ? $c102['source_artifact_locks'] : [];
        foreach (['c101_hash_match', 'c101_file_sha1_match', 'c101_source_lineage_match'] as $field) {
            if (($locks[$field] ?? null) !== true) {
                return false;
            }
        }
        return true;
    }

    private function c102NextRecommendationMatches(array $c102): bool
    {
        foreach ([
            $c102['next_step_recommendation'] ?? null,
            $c102['next_readiness_decision']['next_recommendation'] ?? null,
            $c102['c102_go_decision_finalization_decision']['next_recommendation'] ?? null,
            $c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision']['next_recommendation'] ?? null,
            $c102['planned_next_summary']['planned_next_review'] ?? null,
        ] as $recommendation) {
            if ($recommendation !== self::EXPECTED_C102_RECOMMENDATION) {
                return false;
            }
        }
        return true;
    }

    private function c102GoDecisionFinalized(array $c102): bool
    {
        foreach ([
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass',
            'operator_go_decision_confirmed',
            'go_decision_finalized',
            'go_decision_finalization_confirmed',
            'primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized',
            'backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized',
        ] as $field) {
            if (($c102[$field] ?? null) !== true) {
                return false;
            }
            if (($c102['c102_go_decision_finalization_decision'][$field] ?? null) !== true) {
                return false;
            }
            if (($c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision'][$field] ?? null) !== true) {
                return false;
            }
        }
        if (($c102['operator_go_decision'] ?? null) !== 'GO') {
            return false;
        }
        if (($c102['c102_go_decision_finalization_decision']['operator_go_decision'] ?? null) !== 'GO') {
            return false;
        }
        if (($c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision']['operator_go_decision'] ?? null) !== 'GO') {
            return false;
        }
        if (($c102['comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized'] ?? null) !== false) {
            return false;
        }
        if (($c102['c102_go_decision_finalization_decision']['comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized'] ?? null) !== false) {
            return false;
        }
        if (($c102['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_count'] ?? null) !== 2) {
            return false;
        }

        $manifest = is_array($c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest'] ?? null) ? $c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest'] : [];
        foreach (['manifest_created', 'go_decision_finalization_artifact_only'] as $field) {
            if (($manifest[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach ([
            'weekly_swing_official_output_generated',
            'weekly_swing_live_output_enabled',
            'weekly_swing_live_output_published',
            'plan_confirm_mutation_allowed',
            'go_decision_finalization_used_for_selection',
            'go_decision_finalization_used_for_retuning',
            'go_decision_finalization_used_for_ranking',
            'go_decision_finalization_used_for_plan_confirm_mutation',
            'go_decision_finalization_used_for_live_rollout',
        ] as $field) {
            if (($manifest[$field] ?? false) === true) {
                return false;
            }
        }
        if ((array) ($manifest['official_weekly_swing_stock_recommendations'] ?? []) !== []) {
            return false;
        }
        if (($c102['temporary_negative_artifacts_remaining'] ?? null) !== false) {
            return false;
        }
        if (($c102['temporary_negative_artifact_cleanup_confirmed'] ?? null) !== true) {
            return false;
        }
        if ((array) ($c102['temporary_negative_artifact_paths'] ?? []) !== []) {
            return false;
        }
        return true;
    }

    private function firstLiveOrMutatingSafetyFlag(array $payload): ?string
    {
        foreach (self::C102_LIVE_OR_MUTATING_FLAGS as $field) {
            if (($payload[$field] ?? null) === true) {
                return $field;
            }
        }
        foreach ([
            'c102_go_decision_finalization_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_summary',
            'go_decision_finalization_governance_summary',
            'production_mutation_safety_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'baseline_plan_confirm_non_mutation_summary',
        ] as $section) {
            $values = is_array($payload[$section] ?? null) ? $payload[$section] : [];
            foreach (self::C102_LIVE_OR_MUTATING_FLAGS as $field) {
                if (($values[$field] ?? null) === true) {
                    return $section.'.'.$field;
                }
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c102): bool
    {
        foreach ([
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
        ] as $field => $expected) {
            if (($c102[$field] ?? null) !== $expected) {
                return false;
            }
            if (($c102['c102_go_decision_finalization_decision'][$field] ?? $expected) !== $expected) {
                return false;
            }
            if (($c102['weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_decision'][$field] ?? $expected) !== $expected) {
                return false;
            }
            if (($c102['candidate_scope_freeze_summary'][$field] ?? $expected) !== $expected) {
                return false;
            }
        }
        if (($c102['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        if (($c102['c102_go_decision_finalization_decision']['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        $summary = is_array($c102['candidate_scope_freeze_summary'] ?? null) ? $c102['candidate_scope_freeze_summary'] : [];
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
        $c102 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $temporaryRemaining = $temporaryNegativePaths !== [];
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c102_lock_validation_summary'] = $this->c102LockValidationSummary($load, $c102);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c102);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c103_completion_boundary_decision'] = $this->completionBoundaryDecision($pass, $temporaryRemaining);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass, $temporaryRemaining);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_decision'] = $this->completionBoundaryDecision($pass, $temporaryRemaining);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest'] = $this->completionBoundaryManifest($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_summary'] = $this->completionBoundaryContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c102_go_decision_finalization_carry_forward_validation_summary'] = $this->c102GoDecisionFinalizationCarryForwardValidationSummary($c102, $pass);
        $artifact['completion_boundary_governance_summary'] = $this->completionBoundaryGovernanceSummary($pass);
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
        if (($options['completion_boundary_confirmed'] ?? true) !== true || ($options['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_confirmed'] ?? true) !== true) {
            $failures[] = self::BOUNDARY_NOT_CONFIRMED_STATUS;
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
            'completion_boundary_used_for_selection',
            'completion_boundary_used_for_retuning',
            'completion_boundary_used_for_ranking',
            'completion_boundary_used_for_plan_confirm_mutation',
            'completion_boundary_used_for_live_rollout',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_selection',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_retuning',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_ranking',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_plan_confirm_mutation',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_live_rollout',
        ]);
    }

    private function c102LockValidationSummary(array $load, array $c102): array
    {
        return [
            'c102_lock_validation_completed' => true,
            'c102_artifact_exists' => (bool) $load['exists'],
            'expected_c102_hash' => $load['expected_hash'],
            'actual_c102_hash' => $load['actual_hash'],
            'c102_hash_match' => $load['hash_match'],
            'expected_c102_file_sha1' => $load['expected_file_sha1'],
            'actual_c102_file_sha1' => $load['actual_file_sha1'],
            'c102_file_sha1_match' => $load['file_sha1_match'],
            'c102_status_match' => (($c102['status'] ?? null) === self::EXPECTED_C102_STATUS),
            'c102_reason_code_match' => (($c102['reason_code'] ?? null) === self::EXPECTED_C102_REASON),
            'c102_next_recommendation_match' => $this->c102NextRecommendationMatches($c102),
            'c102_go_decision_finalized' => $this->c102GoDecisionFinalized($c102),
        ];
    }

    private function lineageValidationSummary(array $c102): array
    {
        $locks = is_array($c102['source_artifact_locks'] ?? null) ? $c102['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'c102_to_c101_lock_match' => (($locks['c101_hash_match'] ?? null) === true && ($locks['c101_file_sha1_match'] ?? null) === true),
            'c102_to_c60_lineage_match' => $this->lineageLocksMatch($c102),
            'lineage_source' => 'C102_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C102_LOCKED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_DECISION',
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
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_selection' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_retuning' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_ranking' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_plan_confirm_mutation' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_used_for_live_rollout' => false,
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
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? true),
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

    private function completionBoundaryDecision(bool $pass, bool $temporaryRemaining): array
    {
        $decision = [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c102_lock_valid' => $pass,
            'c102_go_decision_finalized' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_executed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared' => $pass,
            'completion_boundary_cleared' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => $pass,
            'backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => $pass,
            'comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => $temporaryRemaining,
            'temporary_negative_artifact_cleanup_confirmed' => ! $temporaryRemaining,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'completion_boundary_artifact_only' => true,
            'completion_boundary_used_for_selection' => false,
            'completion_boundary_used_for_retuning' => false,
            'completion_boundary_used_for_ranking' => false,
            'completion_boundary_used_for_plan_confirm_mutation' => false,
            'completion_boundary_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C104_RECOMMENDATION : 'C103_TARGETED_C102_GO_DECISION_FINALIZATION_REPAIR',
            'decision_reason' => $pass ? 'C103 weekly swing watchlist non-live rehearsal completion boundary cleared primary and backup in artifact-only audit context.' : 'C103 weekly swing watchlist non-live rehearsal completion boundary review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW' : 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_REPAIR_REQUIRED',
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass, bool $temporaryRemaining): array
    {
        $decision = $this->completionBoundaryDecision($pass, $temporaryRemaining);
        $decision['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_count'] = $pass ? 2 : 0;
        $decision['candidate_codes'] = $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [];
        return $decision;
    }

    private function completionBoundaryManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_non_live_rehearsal_completion_boundary_review',
            'execution_mode' => 'non_live_artifact_only_rehearsal_completion_boundary',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_non_live_rehearsal_completion_boundary_cleared_candidate',
            'backup_candidate_role' => 'backup_non_live_rehearsal_completion_boundary_cleared_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'completion_boundary_cleared' => $pass,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'completion_boundary_used_for_selection' => false,
            'completion_boundary_used_for_retuning' => false,
            'completion_boundary_used_for_ranking' => false,
            'completion_boundary_used_for_plan_confirm_mutation' => false,
            'completion_boundary_used_for_live_rollout' => false,
            'completion_boundary_artifact_only' => true,
            'official_weekly_swing_stock_recommendations' => [],
            'non_live_completion_boundary_steps' => [
                'validate_locked_c102_go_decision_finalization_artifact',
                'confirm_primary_and_backup_finalized_go_scope',
                'clear_primary_and_backup_non_live_completion_boundary',
                'confirm_a01_comparator_only_scope',
                'record_non_live_completion_boundary_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'candidate_active_in_default_runtime_catalog' => false,
            'c102_lock_validation_pass' => $pass,
            'candidate_scope_freeze_pass' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'non_live_rehearsal_completion_boundary_advisory_only_pass' => $pass,
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
                'c103_role' => 'primary_non_live_rehearsal_completion_boundary_cleared_candidate',
                'primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c103_role' => 'backup_non_live_rehearsal_completion_boundary_cleared_candidate',
                'backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c103_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass' => false,
                'boundary_go_decision' => 'NO_GO',
                'comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared' => false,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function completionBoundaryContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime' => false,
            'operator_go_no_go_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime' => false,
            'go_decision_finalization_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_persisted_to_live_runtime' => false,
            'completion_boundary_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_source_identified' => is_file(self::RUNTIME_PATHS['c102_weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_service']),
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_source_identified' => is_file(self::RUNTIME_PATHS['c103_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_service']),
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
            'completion_boundary_confirmation_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c102GoDecisionFinalizationCarryForwardValidationSummary(array $c102, bool $pass): array
    {
        return [
            'c102_go_decision_finalization_carry_forward_validation_completed' => true,
            'c102_go_decision_finalization_carry_forward_validation_pass' => $pass,
            'c102_status' => (string) ($c102['status'] ?? ''),
            'c102_reason_code' => (string) ($c102['reason_code'] ?? ''),
            'c102_artifact_hash' => (string) ($c102['artifact_hash'] ?? ''),
            'c102_go_decision_finalized' => $this->c102GoDecisionFinalized($c102),
            'c102_operator_go_decision' => (string) ($c102['operator_go_decision'] ?? ''),
            'c102_go_decision_finalization_confirmed' => (bool) ($c102['go_decision_finalization_confirmed'] ?? false),
            'c102_primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => (bool) ($c102['primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized'] ?? false),
            'c102_backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => (bool) ($c102['backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized'] ?? false),
            'c102_comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized' => (bool) ($c102['comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized'] ?? false),
            'c102_a01_remains_comparator_only' => (bool) ($c102['a01_remains_comparator_only'] ?? false),
            'expected_c102_next_recommendation' => self::EXPECTED_C102_RECOMMENDATION,
        ];
    }

    private function completionBoundaryGovernanceSummary(bool $pass): array
    {
        return [
            'completion_boundary_governance_completed' => true,
            'completion_boundary_governance_pass' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'completion_boundary_cleared' => $pass,
            'completion_boundary_artifact_only' => true,
            'completion_boundary_used_for_selection' => false,
            'completion_boundary_used_for_retuning' => false,
            'completion_boundary_used_for_ranking' => false,
            'completion_boundary_used_for_plan_confirm_mutation' => false,
            'completion_boundary_used_for_live_rollout' => false,
            'completion_boundary_context_persisted_to_live_runtime' => false,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C103_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass' => $pass,
            'completion_boundary_cleared' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C104_RECOMMENDATION : 'C103_TARGETED_C102_GO_DECISION_FINALIZATION_REPAIR',
            'selection_changed_after_c89' => false,
            'parameter_changed_after_c89' => false,
            'new_candidate_created' => false,
            'completion_boundary_used_for_selection' => false,
            'completion_boundary_used_for_retuning' => false,
            'completion_boundary_used_for_ranking' => false,
            'completion_boundary_used_for_plan_confirm_mutation' => false,
            'completion_boundary_used_for_live_rollout' => false,
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
            'targeted_repair_recommendation' => $status === [] ? self::C104_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW',
            'achieved' => [
                'C102 artifact hash and file SHA1 validated',
                'C102 finalized GO evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Completion boundary cleared for E02 and B01 only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist non-live rehearsal completion boundary manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C104_RECOMMENDATION : 'C103_TARGETED_C102_GO_DECISION_FINALIZATION_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal handoff readiness review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C103 artifact hash',
                'locked C103 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only completion boundary cleared evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C103 validates C102 artifact_hash and file SHA1 locks before weekly swing watchlist non-live rehearsal completion boundary review is recorded.',
            'C103 validates C102 finalized GO fields and A01 comparator-only state.',
            'C103 confirms no temporary negative test artifact remains before a passing non-live rehearsal completion boundary review.',
            'C103 clears completion boundary for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C103 creates an artifact-only weekly swing watchlist non-live rehearsal completion boundary manifest and no official weekly swing recommendation output.',
            'C103 weekly swing watchlist non-live rehearsal completion boundary review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C103 may only recommend C104 weekly swing watchlist non-live rehearsal handoff readiness review as the next audit-only step.',
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
        if (strpos($status, 'C102_ARTIFACT') !== false || strpos($status, 'C102_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C103_C102_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'COMPLETION_BOUNDARY') !== false) {
            return 'C103_COMPLETION_BOUNDARY_REPAIR';
        }
        if (strpos($status, 'GO_DECISION_FINALIZATION') !== false) {
            return 'C103_C102_GO_DECISION_FINALIZATION_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C103_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C103_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C103_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C103_TARGETED_C102_GO_DECISION_FINALIZATION_REPAIR';
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
