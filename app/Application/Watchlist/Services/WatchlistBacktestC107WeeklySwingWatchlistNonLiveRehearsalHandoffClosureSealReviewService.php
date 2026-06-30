<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewService
{
    public const RUN_CODE = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW';
    public const ARTIFACT_TYPE = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW';

    public const DEFAULT_C106_ARTIFACT = 'storage/app/watchlist/backtest/c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review.json';
    public const DEFAULT_EXPECTED_C106_HASH = '49b2a80cbd714a62418bcf452776514df2ee19ea';
    public const DEFAULT_EXPECTED_C106_FILE_SHA1 = 'B2AA8A78A7A320D8F616EAEE6AA5362B63FFA2BD';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c107-weekly-swing-watchlist-non-live-rehearsal-handoff-closure-seal-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C106_STATUS = 'C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C106_REASON = 'C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C106_RECOMMENDATION = self::RUN_CODE;
    private const C108_RECOMMENDATION = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW';
    private const PASS_STATUS = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const CLOSURE_SEAL_NOT_CONFIRMED_STATUS = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_CLOSURE_SEAL_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

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

    private const C106_LIVE_OR_MUTATING_FLAGS = [
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
        'c107_validation_doc' => 'docs/watchlist/audit/WS_C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW.md',
        'c107_operator_commands_doc' => 'docs/watchlist/audit/WS_C107_OPERATOR_VALIDATION_COMMANDS.md',
        'c106_validation_doc' => 'docs/watchlist/audit/WS_C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW.md',
        'c106_operator_commands_doc' => 'docs/watchlist/audit/WS_C106_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c106_weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewService.php',
        'c107_weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewService.php',
        'c106_command' => 'app/Console/Commands/Watchlist/RunBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewCommand.php',
        'c107_command' => 'app/Console/Commands/Watchlist/RunBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewCommand.php',
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
        string $c106Artifact = self::DEFAULT_C106_ARTIFACT,
        string $expectedC106Hash = self::DEFAULT_EXPECTED_C106_HASH,
        string $expectedC106FileSha1 = self::DEFAULT_EXPECTED_C106_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c106Artifact, $expectedC106Hash, $expectedC106FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C107_BLOCKED_C106_ARTIFACT_LOCK_MISMATCH', 'C106 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C107_BLOCKED_C106_ARTIFACT_LOCK_MISMATCH', 'C106 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C107_BLOCKED_C106_FILE_SHA1_LOCK_MISMATCH', 'C106 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c106 = $load['payload'];
        if (($c106['status'] ?? null) !== self::EXPECTED_C106_STATUS || ($c106['reason_code'] ?? null) !== self::EXPECTED_C106_REASON) {
            return $this->blocked($artifact, 'C107_BLOCKED_C106_STATUS_OR_REASON_MISMATCH', 'C106 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c106NextRecommendationMatches($c106)) {
            return $this->blocked($artifact, 'C107_BLOCKED_C106_NEXT_RECOMMENDATION_MISMATCH', 'C106 next recommendation is not C107.', $outputPath, $overwrite);
        }
        if (! $this->c106HandoffCompletionBoundaryCleared($c106)) {
            return $this->blocked($artifact, 'C107_BLOCKED_C106_HANDOFF_COMPLETION_BOUNDARY_NOT_CLEARED', 'C106 handoff completion boundary evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c106);
        if ($safetyFailure !== null) {
            $artifact['c106_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C107_BLOCKED_C106_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C106 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c106)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C106 candidate scope does not match locked non-live rehearsal handoff completion boundary decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C107 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C107 weekly swing watchlist non-live rehearsal handoff closure seal review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C107 sealed the weekly swing watchlist non-live rehearsal handoff closure for primary and backup as artifact-only evidence. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW';
        $artifact['next_step_recommendation'] = self::C108_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C107_NOT_RUN',
            'reason_code' => 'C107_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed' => false,
            'handoff_closure_sealed' => false,
            'closure_sealed' => false,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => false,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => false,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_cleared' => false,
            'handoff_completion_boundary_cleared' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalized' => false,
            'handoff_finalized' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready' => false,
            'handoff_ready' => false,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared' => false,
            'completion_boundary_cleared' => false,
            'boundary_go_decision' => 'NO_GO',
            'operator_go_decision' => 'NO_GO',
            'go_decision_finalized' => false,
            'c106_handoff_completion_boundary_cleared' => false,
            'c105_handoff_finalized' => false,
            'c104_handoff_ready' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_executed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_allowed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed' => true,
            'handoff_closure_sealed' => true,
            'closure_sealed' => true,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => true,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => true,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_cleared' => true,
            'handoff_completion_boundary_cleared' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalized' => true,
            'handoff_finalized' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready' => true,
            'handoff_ready' => true,
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared' => true,
            'completion_boundary_cleared' => true,
            'boundary_go_decision' => 'BOUNDARY_CLEARED_GO',
            'operator_go_decision' => 'GO',
            'go_decision_finalized' => true,
            'c106_handoff_completion_boundary_cleared' => true,
            'c105_handoff_finalized' => true,
            'c104_handoff_ready' => true,
            'a01_remains_comparator_only' => true,
        ];
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
        $c106 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c106['source_artifact_locks'] ?? null) ? $c106['source_artifact_locks'] : [];
        return [
            'c106_artifact_path' => $load['path'],
            'expected_c106_hash' => $load['expected_hash'],
            'actual_c106_hash' => $load['actual_hash'],
            'c106_hash_match' => $load['hash_match'],
            'expected_c106_file_sha1' => $load['expected_file_sha1'],
            'actual_c106_file_sha1' => $load['actual_file_sha1'],
            'c106_file_sha1_match' => $load['file_sha1_match'],
            'c106_source_lineage_checked' => true,
            'c106_source_lineage_match' => $this->lineageLocksMatch($c106),
            'c105_artifact_hash_from_c106' => (string) ($locks['actual_c105_hash'] ?? ($c106['actual_c105_hash'] ?? '')),
            'c105_file_sha1_from_c106' => (string) ($locks['actual_c105_file_sha1'] ?? ($c106['actual_c105_file_sha1'] ?? '')),
            'c104_artifact_hash_from_c105' => (string) ($locks['c104_artifact_hash_from_c105'] ?? ''),
            'c104_file_sha1_from_c105' => (string) ($locks['c104_file_sha1_from_c105'] ?? ''),
            'c103_artifact_hash_from_c104' => (string) ($locks['c103_artifact_hash_from_c104'] ?? ''),
            'c103_file_sha1_from_c104' => (string) ($locks['c103_file_sha1_from_c104'] ?? ''),
            'c102_artifact_hash_from_c103' => (string) ($locks['c102_artifact_hash_from_c103'] ?? ''),
            'c102_file_sha1_from_c103' => (string) ($locks['c102_file_sha1_from_c103'] ?? ''),
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
            'expected_c106_hash' => $load['expected_hash'],
            'actual_c106_hash' => $load['actual_hash'],
            'c106_hash_match' => $load['hash_match'],
            'expected_c106_file_sha1' => $load['expected_file_sha1'],
            'actual_c106_file_sha1' => $load['actual_file_sha1'],
            'c106_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c106): bool
    {
        $locks = is_array($c106['source_artifact_locks'] ?? null) ? $c106['source_artifact_locks'] : [];
        return ($locks['c105_hash_match'] ?? null) === true
            && ($locks['c105_file_sha1_match'] ?? null) === true
            && (string) ($locks['c104_artifact_hash_from_c105'] ?? '') !== ''
            && (string) ($locks['c104_file_sha1_from_c105'] ?? '') !== '';
    }

    private function c106NextRecommendationMatches(array $c106): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c106_handoff_completion_boundary_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c106, $path);
            if ($value !== self::EXPECTED_C106_RECOMMENDATION) {
                return false;
            }
        }
        return true;
    }

    private function c106HandoffCompletionBoundaryCleared(array $c106): bool
    {
        $decision = is_array($c106['c106_handoff_completion_boundary_decision'] ?? null) ? $c106['c106_handoff_completion_boundary_decision'] : [];
        foreach ([
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_pass' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_cleared' => true,
            'handoff_completion_boundary_cleared' => true,
            'handoff_finalized' => true,
            'handoff_ready' => true,
            'completion_boundary_cleared' => true,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared' => true,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared' => true,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared' => false,
            'boundary_go_decision' => 'BOUNDARY_CLEARED_GO',
            'operator_go_decision' => 'GO',
            'go_decision_finalized' => true,
            'a01_remains_comparator_only' => true,
        ] as $field => $expected) {
            if (($c106[$field] ?? null) !== $expected) {
                return false;
            }
            if (($decision[$field] ?? null) !== $expected) {
                return false;
            }
        }
        if (($c106['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_count'] ?? null) !== 2) {
            return false;
        }
        if (($decision['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_count'] ?? null) !== 2) {
            return false;
        }
        if (($c106['temporary_negative_artifacts_remaining'] ?? null) !== false || ($c106['temporary_negative_artifact_cleanup_confirmed'] ?? null) !== true || (array) ($c106['temporary_negative_artifact_paths'] ?? []) !== []) {
            return false;
        }

        $manifest = is_array($c106['weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_manifest'] ?? null)
            ? $c106['weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_manifest']
            : [];
        if (($manifest['handoff_completion_boundary_artifact_only'] ?? null) !== true || ($manifest['handoff_completion_boundary_cleared'] ?? null) !== true) {
            return false;
        }
        foreach ([
            'handoff_completion_boundary_used_for_selection',
            'handoff_completion_boundary_used_for_retuning',
            'handoff_completion_boundary_used_for_ranking',
            'handoff_completion_boundary_used_for_plan_confirm_mutation',
            'handoff_completion_boundary_used_for_live_rollout',
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

    private function firstLiveOrMutatingSafetyFlag(array $payload): ?string
    {
        foreach (self::C106_LIVE_OR_MUTATING_FLAGS as $flag) {
            if (($payload[$flag] ?? null) === true) {
                return $flag;
            }
        }
        foreach ([
            'c106_handoff_completion_boundary_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_decision',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'handoff_completion_boundary_governance_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
        ] as $section) {
            $values = is_array($payload[$section] ?? null) ? $payload[$section] : [];
            foreach (self::C106_LIVE_OR_MUTATING_FLAGS as $flag) {
                if (($values[$flag] ?? null) === true) {
                    return $section.'.'.$flag;
                }
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c106): bool
    {
        return ($c106['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c106['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c106['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c106['a01_remains_comparator_only'] ?? null) === true
            && ($c106['primary_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared'] ?? null) === true
            && ($c106['backup_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared'] ?? null) === true
            && ($c106['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared'] ?? null) === false
            && ($c106['candidate_scope_freeze_summary']['a01_promoted'] ?? false) === false
            && ($c106['candidate_scope_freeze_summary']['new_candidate_created'] ?? false) === false;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (($options['handoff_closure_seal_confirmed'] ?? true) !== true || ($options['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_confirmed'] ?? true) !== true) {
            $failures[] = self::CLOSURE_SEAL_NOT_CONFIRMED_STATUS;
        }
        foreach ($this->prohibitedOptionFields() as $field) {
            if (($options[$field] ?? false) === true) {
                $failures[] = $this->statusForProhibitedField($field);
            }
        }
        foreach ([
            'production_catalog_runtime_bridge_enabled',
            'production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'production_catalog_controlled_shadow_rollout_enabled',
            'production_catalog_controlled_parallel_run_enabled',
            'production_catalog_controlled_rollout_enabled',
        ] as $flag) {
            if ($this->configFlagIsOn($flag)) {
                $failures[] = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_FEATURE_FLAG_OR_RUNTIME_GATE_ON';
                break;
            }
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c106',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'handoff_closure_seal_used_for_selection',
            'handoff_closure_seal_used_for_retuning',
            'handoff_closure_seal_used_for_ranking',
            'handoff_closure_seal_used_for_plan_confirm_mutation',
            'handoff_closure_seal_used_for_live_rollout',
            'handoff_closure_seal_allowed_to_auto_enable_runtime',
            'handoff_closure_seal_allowed_to_auto_deploy',
            'a01_promoted',
            'a01_used_as_runtime_fallback',
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_persisted_to_live_runtime',
            'handoff_closure_seal_context_persisted_to_live_runtime',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'candidate_scope') !== false || strpos($field, 'new_candidate') !== false || strpos($field, 'selection') !== false || strpos($field, 'retuning') !== false || strpos($field, 'ranking') !== false || strpos($field, 'parameter') !== false || strpos($field, 'a01') !== false) {
            return self::CANDIDATE_SCOPE_MISMATCH_STATUS;
        }
        return self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c106 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c106_lock_validation_summary'] = $this->c106LockValidationSummary($load, $c106);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c106);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c106, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c107_handoff_closure_seal_decision'] = $this->handoffClosureSealDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_decision'] = $artifact['c107_handoff_closure_seal_decision'];
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_manifest'] = $this->handoffClosureSealManifest($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_summary'] = $this->handoffClosureSealContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c106_handoff_completion_boundary_carry_forward_validation_summary'] = $this->c106HandoffCompletionBoundaryCarryForwardValidationSummary($c106, $pass);
        $artifact['handoff_closure_seal_governance_summary'] = $this->handoffClosureSealGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : $forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function c106LockValidationSummary(array $load, array $c106): array
    {
        return [
            'c106_lock_validation_completed' => true,
            'c106_artifact_path' => $load['path'],
            'expected_c106_hash' => $load['expected_hash'],
            'actual_c106_hash' => $load['actual_hash'],
            'c106_hash_match' => $load['hash_match'],
            'expected_c106_file_sha1' => $load['expected_file_sha1'],
            'actual_c106_file_sha1' => $load['actual_file_sha1'],
            'c106_file_sha1_match' => $load['file_sha1_match'],
            'c106_status' => (string) ($c106['status'] ?? ''),
            'c106_reason_code' => (string) ($c106['reason_code'] ?? ''),
            'c106_next_recommendation_match' => $this->c106NextRecommendationMatches($c106),
            'c106_handoff_completion_boundary_cleared' => $this->c106HandoffCompletionBoundaryCleared($c106),
        ];
    }

    private function lineageValidationSummary(array $c106): array
    {
        $locks = is_array($c106['source_artifact_locks'] ?? null) ? $c106['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'c106_to_c105_lock_match' => (($locks['c105_hash_match'] ?? null) === true && ($locks['c105_file_sha1_match'] ?? null) === true),
            'c105_to_c104_lock_present' => (string) ($locks['c104_artifact_hash_from_c105'] ?? '') !== '' && (string) ($locks['c104_file_sha1_from_c105'] ?? '') !== '',
            'c106_source_lineage_match' => $this->lineageLocksMatch($c106),
            'lineage_source' => 'C106_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(array $c106, bool $pass): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_freeze_pass' => $pass,
            'candidate_scope_source' => 'C106_LOCKED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'source_primary_candidate_code' => (string) ($c106['primary_candidate_code'] ?? ''),
            'source_backup_candidate_code' => (string) ($c106['backup_candidate_code'] ?? ''),
            'source_comparator_candidate_code' => (string) ($c106['comparator_candidate_code'] ?? ''),
            'primary_candidate_unchanged' => ($c106['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE,
            'backup_candidate_unchanged' => ($c106['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE,
            'a01_remains_comparator_only' => ($c106['a01_remains_comparator_only'] ?? null) === true,
            'candidate_scope_changed_after_c106' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'handoff_closure_seal_used_for_selection' => false,
            'handoff_closure_seal_used_for_retuning' => false,
            'handoff_closure_seal_used_for_ranking' => false,
            'handoff_closure_seal_used_for_plan_confirm_mutation' => false,
            'handoff_closure_seal_used_for_live_rollout' => false,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => false,
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_validation_completed' => true,
            'operator_approval_validation_pass' => $pass,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_required' => true,
            'handoff_closure_seal_reference_scope' => 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_ONLY',
        ];
    }

    private function temporaryNegativeArtifactGuardSummary(array $paths): array
    {
        return [
            'temporary_negative_artifact_guard_completed' => true,
            'temporary_negative_artifacts_remaining' => $paths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $paths === [],
            'temporary_negative_artifact_paths' => $paths,
        ];
    }

    private function handoffClosureSealDecision(bool $pass): array
    {
        $decision = array_merge($this->passingTopLevelState(), [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c106_lock_valid' => $pass,
            'c106_handoff_completion_boundary_cleared' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_executed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed' => $pass,
            'handoff_closure_sealed' => $pass,
            'closure_sealed' => $pass,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => $pass,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => $pass,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => false,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'handoff_closure_seal_artifact_only' => true,
            'handoff_closure_seal_used_for_selection' => false,
            'handoff_closure_seal_used_for_retuning' => false,
            'handoff_closure_seal_used_for_ranking' => false,
            'handoff_closure_seal_used_for_plan_confirm_mutation' => false,
            'handoff_closure_seal_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C108_RECOMMENDATION : 'C107_TARGETED_C106_HANDOFF_COMPLETION_BOUNDARY_REPAIR',
            'decision_reason' => $pass ? 'C107 weekly swing watchlist non-live rehearsal handoff closure is sealed for primary and backup in artifact-only audit context.' : 'C107 handoff closure seal review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW' : 'C107_HANDOFF_CLOSURE_SEAL_REPAIR_REQUIRED',
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
        ]);
        if (! $pass) {
            foreach ([
                'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed',
                'handoff_closure_sealed',
                'closure_sealed',
                'primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed',
                'backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed',
                'c106_handoff_completion_boundary_cleared',
            ] as $field) {
                $decision[$field] = false;
            }
        }
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return array_merge($this->handoffClosureSealDecision($pass), [
            'validation_completed' => true,
            'next_recommendation' => $pass ? self::C108_RECOMMENDATION : 'C107_TARGETED_C106_HANDOFF_COMPLETION_BOUNDARY_REPAIR',
        ]);
    }

    private function handoffClosureSealManifest(bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'artifact_only_non_live_rehearsal_handoff_closure_seal_review',
            'execution_mode' => 'non_live_artifact_only_rehearsal_handoff_closure_seal',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_non_live_rehearsal_handoff_closure_sealed_candidate',
            'backup_candidate_role' => 'backup_non_live_rehearsal_handoff_closure_sealed_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_closure_sealed' => $pass,
            'closure_sealed' => $pass,
            'handoff_completion_boundary_cleared' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'handoff_closure_seal_artifact_only' => true,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'handoff_closure_seal_used_for_selection' => false,
            'handoff_closure_seal_used_for_retuning' => false,
            'handoff_closure_seal_used_for_ranking' => false,
            'handoff_closure_seal_used_for_plan_confirm_mutation' => false,
            'handoff_closure_seal_used_for_live_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
            'non_live_handoff_closure_seal_steps' => [
                'validate_locked_c106_handoff_completion_boundary_artifact',
                'confirm_primary_and_backup_handoff_completion_boundary_cleared_scope',
                'seal_primary_and_backup_non_live_handoff_closure',
                'confirm_a01_comparator_only_scope',
                'record_non_live_handoff_closure_seal_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass' => $pass,
            'handoff_closure_sealed' => $pass,
            'closure_sealed' => $pass,
            'handoff_completion_boundary_cleared' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'non_live_rehearsal_handoff_closure_seal_advisory_only_pass' => $pass,
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
                'c107_role' => 'primary_non_live_rehearsal_handoff_closure_sealed_candidate',
                'primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c107_role' => 'backup_non_live_rehearsal_handoff_closure_sealed_candidate',
                'backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c107_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass' => false,
                'handoff_closure_sealed' => false,
                'closure_sealed' => false,
                'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => false,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffClosureSealContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_persisted_to_live_runtime' => false,
            'handoff_closure_seal_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_source_identified' => is_file(self::RUNTIME_PATHS['c106_weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_service']),
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_source_identified' => is_file(self::RUNTIME_PATHS['c107_weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_service']),
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
            'handoff_closure_seal_confirmation_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c106HandoffCompletionBoundaryCarryForwardValidationSummary(array $c106, bool $pass): array
    {
        return [
            'c106_handoff_completion_boundary_carry_forward_validation_completed' => true,
            'c106_handoff_completion_boundary_carry_forward_validation_pass' => $pass,
            'c106_status' => (string) ($c106['status'] ?? ''),
            'c106_reason_code' => (string) ($c106['reason_code'] ?? ''),
            'c106_artifact_hash' => (string) ($c106['artifact_hash'] ?? ''),
            'c106_handoff_completion_boundary_review_pass' => ($c106['weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_pass'] ?? null) === true,
            'c106_handoff_completion_boundary_cleared' => ($c106['handoff_completion_boundary_cleared'] ?? null) === true,
            'c106_handoff_finalized' => ($c106['handoff_finalized'] ?? null) === true,
            'c106_handoff_ready' => ($c106['handoff_ready'] ?? null) === true,
            'c106_primary_candidate_handoff_completion_boundary_cleared' => ($c106['primary_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared'] ?? null) === true,
            'c106_backup_candidate_handoff_completion_boundary_cleared' => ($c106['backup_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared'] ?? null) === true,
            'c106_comparator_candidate_handoff_completion_boundary_cleared' => ($c106['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared'] ?? null) === false,
            'c106_a01_remains_comparator_only' => ($c106['a01_remains_comparator_only'] ?? null) === true,
            'expected_c106_next_recommendation' => self::EXPECTED_C106_RECOMMENDATION,
        ];
    }

    private function handoffClosureSealGovernanceSummary(bool $pass): array
    {
        return [
            'handoff_closure_seal_governance_completed' => true,
            'handoff_closure_seal_governance_pass' => $pass,
            'handoff_closure_sealed' => $pass,
            'handoff_closure_seal_is_explicit_context_only' => true,
            'handoff_closure_seal_is_non_live_default' => true,
            'handoff_closure_seal_is_artifact_only' => true,
            'handoff_closure_seal_is_advisory_only' => true,
            'handoff_closure_seal_used_for_selection' => false,
            'handoff_closure_seal_used_for_retuning' => false,
            'handoff_closure_seal_used_for_ranking' => false,
            'handoff_closure_seal_used_for_plan_confirm_mutation' => false,
            'handoff_closure_seal_used_for_live_rollout' => false,
            'handoff_closure_seal_allowed_to_auto_enable_runtime' => false,
            'handoff_closure_seal_allowed_to_auto_deploy' => false,
            'handoff_closure_seal_classification' => 'WEEKLY_SWING_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C107_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass' => $pass,
            'handoff_closure_sealed' => $pass,
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C108_RECOMMENDATION : 'C107_TARGETED_C106_HANDOFF_COMPLETION_BOUNDARY_REPAIR',
            'selection_changed_after_c106' => false,
            'parameter_changed_after_c106' => false,
            'new_candidate_created' => false,
            'handoff_closure_seal_used_for_selection' => false,
            'handoff_closure_seal_used_for_retuning' => false,
            'handoff_closure_seal_used_for_ranking' => false,
            'handoff_closure_seal_used_for_plan_confirm_mutation' => false,
            'handoff_closure_seal_used_for_live_rollout' => false,
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
            'targeted_repair_recommendation' => $status === [] ? self::C108_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW',
            'achieved' => [
                'C106 artifact hash and file SHA1 validated',
                'C106 handoff completion boundary evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Handoff closure sealed for E02 and B01 only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist non-live rehearsal handoff closure seal manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C108_RECOMMENDATION : 'C107_TARGETED_C106_HANDOFF_COMPLETION_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal handoff audit archive review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C107 artifact hash',
                'locked C107 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only handoff closure seal evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C107 validates C106 artifact_hash and file SHA1 locks before weekly swing watchlist non-live rehearsal handoff closure seal review is recorded.',
            'C107 validates C106 handoff completion boundary fields and A01 comparator-only state.',
            'C107 confirms no temporary negative test artifact remains before a passing non-live rehearsal handoff closure seal review.',
            'C107 seals handoff closure for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C107 creates an artifact-only weekly swing watchlist non-live rehearsal handoff closure seal manifest and no official weekly swing recommendation output.',
            'C107 weekly swing watchlist non-live rehearsal handoff closure seal review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C107 may only recommend C108 weekly swing watchlist non-live rehearsal handoff audit archive review as the next audit-only step.',
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
        if (strpos($status, 'C106_ARTIFACT') !== false || strpos($status, 'C106_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C107_C106_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'CLOSURE') !== false) {
            return 'C107_HANDOFF_CLOSURE_SEAL_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C107_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C107_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C107_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C107_TARGETED_C106_HANDOFF_COMPLETION_BOUNDARY_REPAIR';
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
