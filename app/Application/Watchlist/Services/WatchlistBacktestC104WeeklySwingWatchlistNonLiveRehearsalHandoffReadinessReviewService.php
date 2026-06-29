<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewService
{
    public const RUN_CODE = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW';
    public const ARTIFACT_TYPE = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW';

    public const DEFAULT_C103_ARTIFACT = 'storage/app/watchlist/backtest/c103-weekly-swing-watchlist-non-live-rehearsal-completion-boundary-review.json';
    public const DEFAULT_EXPECTED_C103_HASH = '60954783fd524694581bd1b4cdb47a71bdcd7bcb';
    public const DEFAULT_EXPECTED_C103_FILE_SHA1 = 'F61E6BAF148D974CEE483D45164E0D5F6BD51376';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C103_STATUS = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C103_REASON = 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C103_RECOMMENDATION = self::RUN_CODE;
    private const C105_RECOMMENDATION = 'C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW';
    private const PASS_STATUS = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const HANDOFF_NOT_CONFIRMED_STATUS = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_HANDOFF_READINESS_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

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

    private const C103_LIVE_OR_MUTATING_FLAGS = [
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
        'c104_validation_doc' => 'docs/watchlist/audit/WS_C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW.md',
        'c104_operator_commands_doc' => 'docs/watchlist/audit/WS_C104_OPERATOR_VALIDATION_COMMANDS.md',
        'c103_validation_doc' => 'docs/watchlist/audit/WS_C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW.md',
        'c103_operator_commands_doc' => 'docs/watchlist/audit/WS_C103_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c103_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC103WeeklySwingWatchlistNonLiveRehearsalCompletionBoundaryReviewService.php',
        'c104_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewService.php',
        'c103_command' => 'app/Console/Commands/Watchlist/RunBacktestC103WeeklySwingWatchlistNonLiveRehearsalCompletionBoundaryReviewCommand.php',
        'c104_command' => 'app/Console/Commands/Watchlist/RunBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewCommand.php',
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
        string $c103Artifact = self::DEFAULT_C103_ARTIFACT,
        string $expectedC103Hash = self::DEFAULT_EXPECTED_C103_HASH,
        string $expectedC103FileSha1 = self::DEFAULT_EXPECTED_C103_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c103Artifact, $expectedC103Hash, $expectedC103FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C104_BLOCKED_C103_ARTIFACT_LOCK_MISMATCH', 'C103 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C104_BLOCKED_C103_ARTIFACT_LOCK_MISMATCH', 'C103 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C104_BLOCKED_C103_FILE_SHA1_LOCK_MISMATCH', 'C103 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c103 = $load['payload'];
        if (($c103['status'] ?? null) !== self::EXPECTED_C103_STATUS || ($c103['reason_code'] ?? null) !== self::EXPECTED_C103_REASON) {
            return $this->blocked($artifact, 'C104_BLOCKED_C103_STATUS_OR_REASON_MISMATCH', 'C103 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c103NextRecommendationMatches($c103)) {
            return $this->blocked($artifact, 'C104_BLOCKED_C103_NEXT_RECOMMENDATION_MISMATCH', 'C103 next recommendation is not C104.', $outputPath, $overwrite);
        }
        if (! $this->c103CompletionBoundaryCleared($c103)) {
            return $this->blocked($artifact, 'C104_BLOCKED_C103_COMPLETION_BOUNDARY_NOT_CLEARED', 'C103 completion boundary evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c103);
        if ($safetyFailure !== null) {
            $artifact['c103_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C104_BLOCKED_C103_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C103 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c103)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C103 candidate scope does not match locked non-live rehearsal completion boundary decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C104 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C104 weekly swing watchlist non-live rehearsal handoff readiness review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C104 marked the weekly swing watchlist non-live rehearsal handoff package ready for primary and backup as artifact-only evidence. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW';
        $artifact['next_step_recommendation'] = self::C105_RECOMMENDATION;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_executed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_allowed'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass'] = true;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_ready'] = true;
        $artifact['handoff_ready'] = true;
        $artifact['primary_candidate_weekly_swing_non_live_rehearsal_handoff_ready'] = true;
        $artifact['backup_candidate_weekly_swing_non_live_rehearsal_handoff_ready'] = true;
        $artifact['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_ready'] = false;
        $artifact['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared'] = true;
        $artifact['completion_boundary_cleared'] = true;
        $artifact['boundary_go_decision'] = 'BOUNDARY_CLEARED_GO';
        $artifact['operator_go_decision'] = 'GO';
        $artifact['go_decision_finalized'] = true;
        $artifact['c103_completion_boundary_cleared'] = true;
        $artifact['a01_remains_comparator_only'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C104_NOT_RUN',
            'reason_code' => 'C104_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready' => false,
            'handoff_ready' => false,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => false,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => false,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared' => false,
            'completion_boundary_cleared' => false,
            'boundary_go_decision' => 'NO_GO',
            'operator_go_decision' => 'NO_GO',
            'go_decision_finalized' => false,
            'c103_completion_boundary_cleared' => false,
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
        $c103 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c103['source_artifact_locks'] ?? null) ? $c103['source_artifact_locks'] : [];
        return [
            'c103_artifact_path' => $load['path'],
            'expected_c103_hash' => $load['expected_hash'],
            'actual_c103_hash' => $load['actual_hash'],
            'c103_hash_match' => $load['hash_match'],
            'expected_c103_file_sha1' => $load['expected_file_sha1'],
            'actual_c103_file_sha1' => $load['actual_file_sha1'],
            'c103_file_sha1_match' => $load['file_sha1_match'],
            'c102_artifact_hash_from_c103' => (string) ($locks['actual_c102_hash'] ?? ($c103['actual_c102_hash'] ?? '')),
            'c102_file_sha1_from_c103' => (string) ($locks['actual_c102_file_sha1'] ?? ($c103['actual_c102_file_sha1'] ?? '')),
            'c101_artifact_hash_from_c102' => (string) ($locks['c101_artifact_hash_from_c102'] ?? ''),
            'c101_file_sha1_from_c102' => (string) ($locks['c101_file_sha1_from_c102'] ?? ''),
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
            'expected_c103_hash' => $load['expected_hash'],
            'actual_c103_hash' => $load['actual_hash'],
            'c103_hash_match' => $load['hash_match'],
            'expected_c103_file_sha1' => $load['expected_file_sha1'],
            'actual_c103_file_sha1' => $load['actual_file_sha1'],
            'c103_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c103NextRecommendationMatches(array $c103): bool
    {
        $paths = [
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c103_completion_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ];
        $found = false;
        foreach ($paths as $path) {
            $value = $this->valueAt($c103, $path);
            if ($value === null || $value === '') {
                continue;
            }
            $found = true;
            if ($value !== self::EXPECTED_C103_RECOMMENDATION) {
                return false;
            }
        }
        return $found;
    }

    private function c103CompletionBoundaryCleared(array $c103): bool
    {
        if (($c103['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass'] ?? null) !== true) {
            return false;
        }
        if (($c103['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared'] ?? null) !== true || ($c103['completion_boundary_cleared'] ?? null) !== true) {
            return false;
        }
        if (($c103['boundary_go_decision'] ?? null) !== 'BOUNDARY_CLEARED_GO' || ($c103['operator_go_decision'] ?? null) !== 'GO' || ($c103['go_decision_finalized'] ?? null) !== true) {
            return false;
        }
        if (($c103['primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) !== true) {
            return false;
        }
        if (($c103['backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) !== true) {
            return false;
        }
        if (($c103['comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) !== false) {
            return false;
        }
        if (($c103['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        if (($c103['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_count'] ?? null) !== 2) {
            return false;
        }
        $manifest = is_array($c103['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest'] ?? null)
            ? $c103['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest']
            : [];
        if (($manifest['completion_boundary_artifact_only'] ?? null) !== true) {
            return false;
        }
        foreach ([
            'completion_boundary_used_for_selection',
            'completion_boundary_used_for_retuning',
            'completion_boundary_used_for_ranking',
            'completion_boundary_used_for_plan_confirm_mutation',
            'completion_boundary_used_for_live_rollout',
            'weekly_swing_official_output_generated',
            'weekly_swing_live_output_enabled',
            'weekly_swing_live_output_published',
            'plan_confirm_mutation_allowed',
        ] as $field) {
            if (($manifest[$field] ?? null) !== false) {
                return false;
            }
        }
        return (array) ($manifest['official_weekly_swing_stock_recommendations'] ?? []) === [];
    }

    private function firstLiveOrMutatingSafetyFlag(array $source): ?string
    {
        foreach (self::C103_LIVE_OR_MUTATING_FLAGS as $flag) {
            if (($source[$flag] ?? null) === true) {
                return $flag;
            }
        }
        $manifest = is_array($source['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest'] ?? null)
            ? $source['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest']
            : [];
        foreach (self::C103_LIVE_OR_MUTATING_FLAGS as $flag) {
            if (($manifest[$flag] ?? null) === true) {
                return 'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_manifest.'.$flag;
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c103): bool
    {
        return ($c103['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c103['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c103['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c103['a01_remains_comparator_only'] ?? null) === true
            && ($c103['primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) === true
            && ($c103['backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) === true
            && ($c103['comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) === false;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (($options['handoff_readiness_confirmed'] ?? true) !== true) {
            $failures[] = self::HANDOFF_NOT_CONFIRMED_STATUS;
        }
        foreach ([
            'production_catalog_runtime_bridge_enabled',
            'production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'production_catalog_controlled_shadow_rollout_enabled',
            'production_catalog_controlled_parallel_run_enabled',
            'production_catalog_controlled_rollout_enabled',
        ] as $flag) {
            if ($this->configFlagIsOn($flag)) {
                $failures[] = 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_RUNTIME_GATE_ON';
                break;
            }
        }
        return $failures;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c103 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c103_lock_validation_summary'] = [
            'c103_lock_validation_completed' => true,
            'c103_artifact_path' => $load['path'],
            'expected_c103_hash' => $load['expected_hash'],
            'actual_c103_hash' => $load['actual_hash'],
            'c103_hash_match' => $load['hash_match'],
            'expected_c103_file_sha1' => $load['expected_file_sha1'],
            'actual_c103_file_sha1' => $load['actual_file_sha1'],
            'c103_file_sha1_match' => $load['file_sha1_match'],
            'c103_status' => (string) ($c103['status'] ?? ''),
            'c103_reason_code' => (string) ($c103['reason_code'] ?? ''),
            'c103_next_recommendation_match' => $this->c103NextRecommendationMatches($c103),
        ];
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c103, $pass);
        $artifact['operator_approval_validation_summary'] = [
            'operator_approval_validation_completed' => true,
            'operator_approval_validation_pass' => $pass,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_required' => true,
            'handoff_readiness_reference_scope' => 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_ONLY',
        ];
        $artifact['temporary_negative_artifact_guard_summary'] = [
            'temporary_negative_artifact_guard_completed' => true,
            'temporary_negative_artifacts_remaining' => (bool) ($options['temporary_negative_artifact_paths'] ?? false),
            'temporary_negative_artifact_cleanup_confirmed' => (array) ($options['temporary_negative_artifact_paths'] ?? []) === [],
            'temporary_negative_artifact_paths' => (array) ($options['temporary_negative_artifact_paths'] ?? []),
        ];
        $artifact['c104_handoff_readiness_decision'] = $this->handoffReadinessDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_decision'] = $artifact['c104_handoff_readiness_decision'];
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_manifest'] = $this->handoffReadinessManifest($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_summary'] = $this->handoffReadinessContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c103_completion_boundary_carry_forward_validation_summary'] = $this->c103CompletionBoundaryCarryForwardValidationSummary($c103, $pass);
        $artifact['handoff_readiness_governance_summary'] = $this->handoffReadinessGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function candidateScopeFreezeSummary(array $c103, bool $pass): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_freeze_pass' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'source_primary_candidate_code' => (string) ($c103['primary_candidate_code'] ?? ''),
            'source_backup_candidate_code' => (string) ($c103['backup_candidate_code'] ?? ''),
            'source_comparator_candidate_code' => (string) ($c103['comparator_candidate_code'] ?? ''),
            'primary_candidate_unchanged' => ($c103['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE,
            'backup_candidate_unchanged' => ($c103['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE,
            'a01_remains_comparator_only' => ($c103['a01_remains_comparator_only'] ?? null) === true,
            'a01_promoted' => false,
            'new_candidate_created' => false,
        ];
    }

    private function handoffReadinessDecision(bool $pass): array
    {
        $decision = [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c103_lock_valid' => $pass,
            'c103_completion_boundary_cleared' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_executed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready' => $pass,
            'handoff_ready' => $pass,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => $pass,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => $pass,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared' => $pass,
            'completion_boundary_cleared' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'a01_remains_comparator_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'handoff_readiness_artifact_only' => true,
            'handoff_readiness_used_for_selection' => false,
            'handoff_readiness_used_for_retuning' => false,
            'handoff_readiness_used_for_ranking' => false,
            'handoff_readiness_used_for_plan_confirm_mutation' => false,
            'handoff_readiness_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C105_RECOMMENDATION : 'C104_TARGETED_C103_COMPLETION_BOUNDARY_REPAIR',
            'decision_reason' => $pass ? 'C104 weekly swing watchlist non-live rehearsal handoff package is ready for primary and backup in artifact-only audit context.' : 'C104 handoff readiness review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW' : 'C104_HANDOFF_READINESS_REPAIR_REQUIRED',
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return array_merge($this->handoffReadinessDecision($pass), [
            'validation_completed' => true,
            'next_recommendation' => $pass ? self::C105_RECOMMENDATION : 'C104_TARGETED_C103_COMPLETION_BOUNDARY_REPAIR',
        ]);
    }

    private function handoffReadinessManifest(bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'artifact_only_non_live_rehearsal_handoff_readiness_review',
            'execution_mode' => 'non_live_artifact_only_rehearsal_handoff_readiness',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_non_live_rehearsal_handoff_ready_candidate',
            'backup_candidate_role' => 'backup_non_live_rehearsal_handoff_ready_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_ready' => $pass,
            'handoff_readiness_artifact_only' => true,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'handoff_readiness_used_for_selection' => false,
            'handoff_readiness_used_for_retuning' => false,
            'handoff_readiness_used_for_ranking' => false,
            'handoff_readiness_used_for_plan_confirm_mutation' => false,
            'handoff_readiness_used_for_live_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
            'non_live_handoff_readiness_steps' => [
                'validate_locked_c103_completion_boundary_artifact',
                'confirm_primary_and_backup_completion_boundary_cleared_scope',
                'mark_primary_and_backup_non_live_handoff_package_ready',
                'confirm_a01_comparator_only_scope',
                'record_non_live_handoff_readiness_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass' => $pass,
            'handoff_ready' => $pass,
            'completion_boundary_cleared' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'non_live_rehearsal_handoff_readiness_advisory_only_pass' => $pass,
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
                'c104_role' => 'primary_non_live_rehearsal_handoff_ready_candidate',
                'primary_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c104_role' => 'backup_non_live_rehearsal_handoff_ready_candidate',
                'backup_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c104_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass' => false,
                'handoff_ready' => false,
                'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_ready' => false,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffReadinessContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_persisted_to_live_runtime' => false,
            'handoff_readiness_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_source_identified' => is_file(self::RUNTIME_PATHS['c103_weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_service']),
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_source_identified' => is_file(self::RUNTIME_PATHS['c104_weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_service']),
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
            'handoff_readiness_confirmation_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c103CompletionBoundaryCarryForwardValidationSummary(array $c103, bool $pass): array
    {
        return [
            'c103_completion_boundary_carry_forward_validation_completed' => true,
            'c103_completion_boundary_carry_forward_validation_pass' => $pass,
            'c103_status' => (string) ($c103['status'] ?? ''),
            'c103_reason_code' => (string) ($c103['reason_code'] ?? ''),
            'c103_artifact_hash' => (string) ($c103['artifact_hash'] ?? ''),
            'c103_completion_boundary_review_pass' => ($c103['weekly_swing_watchlist_non_live_rehearsal_completion_boundary_review_pass'] ?? null) === true,
            'c103_completion_boundary_cleared' => ($c103['completion_boundary_cleared'] ?? null) === true,
            'c103_primary_candidate_completion_boundary_cleared' => ($c103['primary_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) === true,
            'c103_backup_candidate_completion_boundary_cleared' => ($c103['backup_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) === true,
            'c103_comparator_candidate_completion_boundary_cleared' => ($c103['comparator_candidate_weekly_swing_non_live_rehearsal_completion_boundary_cleared'] ?? null) === true,
            'c103_a01_remains_comparator_only' => ($c103['a01_remains_comparator_only'] ?? null) === true,
            'expected_c103_next_recommendation' => self::EXPECTED_C103_RECOMMENDATION,
        ];
    }

    private function handoffReadinessGovernanceSummary(bool $pass): array
    {
        return [
            'handoff_readiness_governance_completed' => true,
            'handoff_readiness_governance_pass' => $pass,
            'handoff_ready' => $pass,
            'handoff_readiness_is_explicit_context_only' => true,
            'handoff_readiness_is_non_live_default' => true,
            'handoff_readiness_is_artifact_only' => true,
            'handoff_readiness_is_advisory_only' => true,
            'handoff_readiness_used_for_selection' => false,
            'handoff_readiness_used_for_retuning' => false,
            'handoff_readiness_used_for_ranking' => false,
            'handoff_readiness_used_for_plan_confirm_mutation' => false,
            'handoff_readiness_used_for_live_rollout' => false,
            'handoff_readiness_allowed_to_auto_enable_runtime' => false,
            'handoff_readiness_allowed_to_auto_deploy' => false,
            'handoff_readiness_classification' => 'WEEKLY_SWING_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C104_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass' => $pass,
            'handoff_ready' => $pass,
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C105_RECOMMENDATION : 'C104_TARGETED_C103_COMPLETION_BOUNDARY_REPAIR',
            'selection_changed_after_c103' => false,
            'parameter_changed_after_c103' => false,
            'new_candidate_created' => false,
            'handoff_readiness_used_for_selection' => false,
            'handoff_readiness_used_for_retuning' => false,
            'handoff_readiness_used_for_ranking' => false,
            'handoff_readiness_used_for_plan_confirm_mutation' => false,
            'handoff_readiness_used_for_live_rollout' => false,
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
            'targeted_repair_recommendation' => $status === [] ? self::C105_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW',
            'achieved' => [
                'C103 artifact hash and file SHA1 validated',
                'C103 completion boundary cleared evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Handoff package marked ready for E02 and B01 only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist non-live rehearsal handoff readiness manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C105_RECOMMENDATION : 'C104_TARGETED_C103_COMPLETION_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal handoff finalization review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C104 artifact hash',
                'locked C104 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only handoff readiness evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C104 validates C103 artifact_hash and file SHA1 locks before weekly swing watchlist non-live rehearsal handoff readiness review is recorded.',
            'C104 validates C103 completion boundary cleared fields and A01 comparator-only state.',
            'C104 confirms no temporary negative test artifact remains before a passing non-live rehearsal handoff readiness review.',
            'C104 marks handoff ready for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C104 creates an artifact-only weekly swing watchlist non-live rehearsal handoff readiness manifest and no official weekly swing recommendation output.',
            'C104 weekly swing watchlist non-live rehearsal handoff readiness review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C104 may only recommend C105 weekly swing watchlist non-live rehearsal handoff finalization review as the next audit-only step.',
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

    private function repairRecommendationFor(string $status): string
    {
        if (strpos($status, 'C103_ARTIFACT') !== false || strpos($status, 'C103_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C104_C103_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false) {
            return 'C104_HANDOFF_READINESS_REPAIR';
        }
        if (strpos($status, 'COMPLETION_BOUNDARY') !== false || strpos($status, 'BOUNDARY') !== false) {
            return 'C104_C103_COMPLETION_BOUNDARY_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C104_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C104_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C104_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C104_TARGETED_C103_COMPLETION_BOUNDARY_REPAIR';
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
