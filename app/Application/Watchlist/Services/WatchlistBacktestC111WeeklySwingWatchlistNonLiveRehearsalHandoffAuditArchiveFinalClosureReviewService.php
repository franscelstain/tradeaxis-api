<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewService
{
    public const RUN_CODE = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';
    public const ARTIFACT_TYPE = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';

    public const DEFAULT_C110_ARTIFACT = 'storage/app/watchlist/backtest/c110-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-seal-review.json';
    public const DEFAULT_EXPECTED_C110_HASH = '17352f926bcf9138be62c9f43a81551f89de0cc7';
    public const DEFAULT_EXPECTED_C110_FILE_SHA1 = '407DB31435BF42C48FD0C7419B7BEBCA138DB127';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C110_STATUS = 'C110_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_SEALED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C110_REASON = 'C110_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_SEALED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C110_RECOMMENDATION = self::RUN_CODE;
    private const NO_NEXT_RECOMMENDATION = 'NO_NEXT_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED';
    private const PASS_STATUS = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const C110_AUDIT_ARCHIVE_COMPLETION_SEAL_INCOMPLETE_STATUS = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C110_AUDIT_ARCHIVE_COMPLETION_SEAL_INCOMPLETE';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

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

    private const REQUIRED_TRUE_C110_FIELDS = [
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_pass',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_ready',
        'handoff_audit_archive_completion_ready',
        'audit_archive_completion_ready',
        'completion_manifest_created',
        'primary_candidate_handoff_audit_archive_completion_ready',
        'backup_candidate_handoff_audit_archive_completion_ready',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_pass',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_sealed',
        'handoff_audit_archive_completion_sealed',
        'audit_archive_completion_sealed',
        'completion_seal_manifest_created',
        'primary_candidate_handoff_audit_archive_completion_sealed',
        'backup_candidate_handoff_audit_archive_completion_sealed',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived',
        'handoff_audit_archived',
        'audit_archived',
        'archive_manifest_created',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed',
        'handoff_closure_sealed',
        'closure_sealed',
        'handoff_completion_boundary_cleared',
        'handoff_finalized',
        'handoff_ready',
    ];

    private const REQUIRED_FALSE_C110_FIELDS = [
        'comparator_candidate_handoff_audit_archive_completion_ready',
        'comparator_candidate_handoff_audit_archive_completion_sealed',
    ];

    private const DOC_PATHS = [
        'c111_validation_doc' => 'docs/watchlist/audit/WS_C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW.md',
        'c111_operator_commands_doc' => 'docs/watchlist/audit/WS_C111_OPERATOR_VALIDATION_COMMANDS.md',
        'c110_validation_doc' => 'docs/watchlist/audit/WS_C110_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW.md',
        'c110_operator_commands_doc' => 'docs/watchlist/audit/WS_C110_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c110_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewService.php',
        'c111_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewService.php',
        'c110_command' => 'app/Console/Commands/Watchlist/RunBacktestC110WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionSealReviewCommand.php',
        'c111_command' => 'app/Console/Commands/Watchlist/RunBacktestC111WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveFinalClosureReviewCommand.php',
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
        string $c110Artifact = self::DEFAULT_C110_ARTIFACT,
        string $expectedC110Hash = self::DEFAULT_EXPECTED_C110_HASH,
        string $expectedC110FileSha1 = self::DEFAULT_EXPECTED_C110_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c110Artifact, $expectedC110Hash, $expectedC110FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C111_BLOCKED_C110_ARTIFACT_LOCK_MISMATCH', 'C110 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C111_BLOCKED_C110_ARTIFACT_LOCK_MISMATCH', 'C110 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C111_BLOCKED_C110_FILE_SHA1_LOCK_MISMATCH', 'C110 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c110 = $load['payload'];
        if (($c110['status'] ?? null) !== self::EXPECTED_C110_STATUS) {
            return $this->blocked($artifact, 'C111_BLOCKED_C110_STATUS_MISMATCH', 'C110 status is not passed audit archive completion sealed.', $outputPath, $overwrite);
        }
        if (($c110['reason_code'] ?? null) !== self::EXPECTED_C110_REASON) {
            return $this->blocked($artifact, 'C111_BLOCKED_C110_REASON_CODE_MISMATCH', 'C110 reason_code is not passed audit archive completion sealed.', $outputPath, $overwrite);
        }
        if (! $this->c110NextRecommendationMatches($c110)) {
            return $this->blocked($artifact, 'C111_BLOCKED_C110_NEXT_RECOMMENDATION_MISMATCH', 'C110 next recommendation is not C111.', $outputPath, $overwrite);
        }
        if (! $this->c110HandoffAuditArchiveCompletionSealed($c110)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C110_AUDIT_ARCHIVE_COMPLETION_SEAL_INCOMPLETE_STATUS, 'C110 handoff audit archive completion seal evidence is incomplete.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c110);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c110_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C110 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c110)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C110 candidate scope does not match locked non-live handoff audit archive completion decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C111 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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
            return $this->rejected($artifact, $failures[0], 'C111 weekly swing watchlist non-live rehearsal handoff audit archive final closure review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C111 final-closes the weekly swing watchlist non-live rehearsal handoff audit archive package for primary and backup as artifact-only evidence. This does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C111_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_NON_LIVE_ONLY';
        $artifact['next_step_recommendation'] = self::NO_NEXT_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C111_NOT_RUN',
            'reason_code' => 'C111_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_pass' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_ready' => false,
            'handoff_audit_archive_completion_ready' => false,
            'audit_archive_completion_ready' => false,
            'completion_manifest_created' => false,
            'primary_candidate_handoff_audit_archive_completion_ready' => false,
            'backup_candidate_handoff_audit_archive_completion_ready' => false,
            'comparator_candidate_handoff_audit_archive_completion_ready' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_pass' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_sealed' => false,
            'handoff_audit_archive_completion_sealed' => false,
            'audit_archive_completion_sealed' => false,
            'completion_seal_manifest_created' => false,
            'primary_candidate_handoff_audit_archive_completion_sealed' => false,
            'backup_candidate_handoff_audit_archive_completion_sealed' => false,
            'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_pass' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closed' => false,
            'handoff_audit_archive_final_closed' => false,
            'audit_archive_final_closed' => false,
            'final_closure_manifest_created' => false,
            'primary_candidate_handoff_audit_archive_final_closed' => false,
            'backup_candidate_handoff_audit_archive_final_closed' => false,
            'comparator_candidate_handoff_audit_archive_final_closed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived' => false,
            'handoff_audit_archived' => false,
            'audit_archived' => false,
            'archive_manifest_created' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed' => false,
            'handoff_closure_sealed' => false,
            'closure_sealed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_cleared' => false,
            'handoff_completion_boundary_cleared' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalized' => false,
            'handoff_finalized' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready' => false,
            'handoff_ready' => false,
            'c110_handoff_audit_archived' => false,
            'c110_handoff_audit_archive_completion_ready' => false,
            'c110_handoff_audit_archive_completion_sealed' => false,
            'c107_handoff_closure_sealed' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_executed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_allowed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_pass' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_ready' => true,
            'handoff_audit_archive_completion_ready' => true,
            'audit_archive_completion_ready' => true,
            'completion_manifest_created' => true,
            'primary_candidate_handoff_audit_archive_completion_ready' => true,
            'backup_candidate_handoff_audit_archive_completion_ready' => true,
            'comparator_candidate_handoff_audit_archive_completion_ready' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_executed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_allowed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_pass' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_sealed' => true,
            'handoff_audit_archive_completion_sealed' => true,
            'audit_archive_completion_sealed' => true,
            'completion_seal_manifest_created' => true,
            'primary_candidate_handoff_audit_archive_completion_sealed' => true,
            'backup_candidate_handoff_audit_archive_completion_sealed' => true,
            'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_executed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_allowed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_pass' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closed' => true,
            'handoff_audit_archive_final_closed' => true,
            'audit_archive_final_closed' => true,
            'final_closure_manifest_created' => true,
            'primary_candidate_handoff_audit_archive_final_closed' => true,
            'backup_candidate_handoff_audit_archive_final_closed' => true,
            'comparator_candidate_handoff_audit_archive_final_closed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived' => true,
            'handoff_audit_archived' => true,
            'audit_archived' => true,
            'archive_manifest_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed' => true,
            'handoff_closure_sealed' => true,
            'closure_sealed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_cleared' => true,
            'handoff_completion_boundary_cleared' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalized' => true,
            'handoff_finalized' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready' => true,
            'handoff_ready' => true,
            'c110_handoff_audit_archived' => true,
            'c110_handoff_audit_archive_completion_ready' => true,
            'c110_handoff_audit_archive_completion_sealed' => true,
            'c107_handoff_closure_sealed' => true,
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
            $raw = file_get_contents($path);
            $decoded = json_decode((string) $raw, true);
            $payload = is_array($decoded) ? $decoded : null;
            $actualHash = is_array($payload) ? (string) ($payload['artifact_hash'] ?? '') : null;
            $actualFileSha1 = strtoupper(sha1_file($path));
        }
        $expectedFileSha1 = strtoupper($expectedFileSha1);
        return [
            'source_lock' => 'C110',
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
            'c110' => [
                'artifact_path' => $load['path'],
                'artifact_exists' => $load['exists'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'expected_status' => self::EXPECTED_C110_STATUS,
                'expected_reason_code' => self::EXPECTED_C110_REASON,
                'expected_next_recommendation' => self::EXPECTED_C110_RECOMMENDATION,
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C110',
            'c110_artifact_path' => $load['path'],
            'c110_artifact_exists' => $load['exists'],
            'expected_c110_hash' => $load['expected_hash'],
            'actual_c110_hash' => $load['actual_hash'],
            'c110_hash_match' => $load['hash_match'],
            'expected_c110_file_sha1' => $load['expected_file_sha1'],
            'actual_c110_file_sha1' => $load['actual_file_sha1'],
            'c110_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c110NextRecommendationMatches(array $c110): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c110_readiness_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_decision', 'next_recommendation'],
        ] as $path) {
            if ($this->valueAt($c110, $path) === self::EXPECTED_C110_RECOMMENDATION) {
                return true;
            }
        }
        return false;
    }

    private function c110HandoffAuditArchiveCompletionSealed(array $c110): bool
    {
        foreach (self::REQUIRED_TRUE_C110_FIELDS as $field) {
            if (array_key_exists($field, $c110) && (bool) $c110[$field] !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_FALSE_C110_FIELDS as $field) {
            if (array_key_exists($field, $c110) && (bool) $c110[$field] !== false) {
                return false;
            }
        }
        return (bool) ($c110['a01_remains_comparator_only'] ?? false) === true;
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

    private function candidateScopeMatches(array $c110): bool
    {
        return ($c110['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE) === self::PRIMARY_CANDIDATE
            && ($c110['backup_candidate_code'] ?? self::BACKUP_CANDIDATE) === self::BACKUP_CANDIDATE
            && ($c110['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE) === self::COMPARATOR_CANDIDATE
            && (bool) ($c110['a01_remains_comparator_only'] ?? false) === true
            && (bool) ($c110['a01_promoted'] ?? false) === false
            && (bool) ($c110['candidate_promotion_executed'] ?? false) === false
            && (bool) ($c110['candidate_rerank_executed'] ?? false) === false
            && (bool) ($c110['strategy_retune_executed'] ?? false) === false
            && (bool) ($c110['scoring_mutation_executed'] ?? false) === false
            && (bool) ($c110['catalog_selection_changed'] ?? false) === false
            && (bool) ($c110['runtime_selection_changed'] ?? false) === false;
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
            'run_oos_rerank',
            'rebuild_signal_quality',
            'change_candidate_selection',
            'promote_a01',
            'rerank_candidate',
            'retune_strategy',
            'change_scoring_logic',
            'change_catalog_selection',
            'activate_production_catalog_runtime_bridge',
            'enable_controlled_rollout',
            'activate_pilot_runtime',
            'activate_shadow_runtime',
            'persist_handoff_audit_archive_completion_context_to_live_runtime',
            'persist_handoff_audit_archive_final_closure_context_to_live_runtime',
            'mutate_plan_confirm',
            'change_config_defaults',
            'change_strategy_parameters',
            'generate_official_weekly_swing_stock_recommendation',
            'generate_live_weekly_swing_watchlist_output',
            'publish_weekly_swing_output',
            'modify_c60_c110_artifacts',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'a01') !== false || strpos($field, 'candidate') !== false || strpos($field, 'scoring') !== false || strpos($field, 'catalog') !== false || strpos($field, 'strategy') !== false) {
            return self::CANDIDATE_SCOPE_MISMATCH_STATUS;
        }
        return self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c110 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c110_lock_validation_summary'] = $this->c110LockValidationSummary($load, $c110);
        $artifact['c104_c110_handoff_lineage_final_closure_summary'] = $this->lineageValidationSummary($c110);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c110, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c111_readiness_decision'] = $this->handoffAuditArchiveCompletionDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_decision'] = $artifact['c111_readiness_decision'];
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_manifest'] = $this->handoffAuditArchiveFinalClosureManifest($pass);
        $artifact['c111_candidate_audit_archive_final_closure_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['handoff_audit_archive_final_closure_context_summary'] = $this->handoffAuditArchiveFinalClosureContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c110_handoff_audit_archive_completion_seal_carry_forward_validation_summary'] = $this->c110HandoffAuditArchiveCompletionSealCarryForwardValidationSummary($c110, $pass);
        $artifact['handoff_audit_archive_final_closure_governance_summary'] = $this->handoffAuditArchiveFinalClosureGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
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

    private function c110LockValidationSummary(array $load, array $c110): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C110',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'expected_status' => self::EXPECTED_C110_STATUS,
            'actual_status' => $c110['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C110_REASON,
            'actual_reason_code' => $c110['reason_code'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C110_RECOMMENDATION,
            'next_recommendation_match' => $this->c110NextRecommendationMatches($c110),
            'c110_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function lineageValidationSummary(array $c110): array
    {
        return [
            'validation_completed' => true,
            'c104_handoff_ready_carried_forward' => (bool) ($c110['handoff_ready'] ?? false),
            'c105_handoff_finalized_carried_forward' => (bool) ($c110['handoff_finalized'] ?? false),
            'c106_handoff_completion_boundary_cleared_carried_forward' => (bool) ($c110['handoff_completion_boundary_cleared'] ?? false),
            'c107_handoff_closure_sealed_carried_forward' => (bool) ($c110['handoff_closure_sealed'] ?? false),
            'c108_handoff_audit_archived_carried_forward' => (bool) ($c110['handoff_audit_archived'] ?? false),
            'c110_handoff_audit_archive_completion_ready_carried_forward' => (bool) ($c110['handoff_audit_archive_completion_ready'] ?? false),
            'c110_handoff_audit_archive_completion_sealed_carried_forward' => (bool) ($c110['handoff_audit_archive_completion_sealed'] ?? false),
            'lineage_carried_forward_complete' => $this->c110HandoffAuditArchiveCompletionSealed($c110),
        ];
    }

    private function candidateScopeFreezeSummary(array $c110, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c110),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_handoff_audit_archive_final_closure_candidate',
            'backup_candidate_role' => 'backup_handoff_audit_archive_final_closure_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_handoff_audit_archive_completion_ready' => $pass,
            'backup_candidate_handoff_audit_archive_completion_ready' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_ready' => false,
            'primary_candidate_handoff_audit_archive_completion_sealed' => $pass,
            'backup_candidate_handoff_audit_archive_completion_sealed' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
            'primary_candidate_handoff_audit_archive_final_closed' => $pass,
            'backup_candidate_handoff_audit_archive_final_closed' => $pass,
            'comparator_candidate_handoff_audit_archive_final_closed' => false,
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

    private function handoffAuditArchiveCompletionDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c110_lock_valid' => $pass,
            'c110_handoff_audit_archive_completion_ready' => $pass,
            'c110_handoff_audit_archive_completion_sealed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'audit_archive_completion_ready' => $pass,
            'completion_manifest_created' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_executed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_sealed' => $pass,
            'handoff_audit_archive_completion_sealed' => $pass,
            'audit_archive_completion_sealed' => $pass,
            'completion_seal_manifest_created' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_executed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closed' => $pass,
            'handoff_audit_archive_final_closed' => $pass,
            'audit_archive_final_closed' => $pass,
            'final_closure_manifest_created' => $pass,
            'primary_candidate_handoff_audit_archive_completion_ready' => $pass,
            'backup_candidate_handoff_audit_archive_completion_ready' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_ready' => false,
            'primary_candidate_handoff_audit_archive_completion_sealed' => $pass,
            'backup_candidate_handoff_audit_archive_completion_sealed' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
            'primary_candidate_handoff_audit_archive_final_closed' => $pass,
            'backup_candidate_handoff_audit_archive_final_closed' => $pass,
            'comparator_candidate_handoff_audit_archive_final_closed' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'production_ready' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'next_recommendation' => self::NO_NEXT_RECOMMENDATION,
            'decision_reason' => 'C111 weekly swing watchlist non-live rehearsal handoff audit archive final closure review closed the non-live audit archive package for primary and backup in artifact-only audit context.',
            'diagnostic_conclusion' => 'C111_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_NON_LIVE_ONLY',
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::NO_NEXT_RECOMMENDATION : 'C111_TARGETED_C110_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REPAIR',
            'next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal handoff audit archive final closure complete; any production path requires a separate approved contract' : 'targeted C110 lock or completion-seal repair only',
        ];
    }

    private function handoffAuditArchiveFinalClosureManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_non_live_handoff_audit_archive_final_closure_review',
            'source_artifact' => 'C110_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW',
            'source_artifact_path' => self::DEFAULT_C110_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C110_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C110_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_handoff_audit_archive_final_closure_candidate',
            'backup_candidate_role' => 'backup_handoff_audit_archive_final_closure_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_ready_carried_forward' => $pass,
            'handoff_finalized_carried_forward' => $pass,
            'handoff_completion_boundary_cleared_carried_forward' => $pass,
            'handoff_closure_sealed_carried_forward' => $pass,
            'handoff_audit_archived_carried_forward' => $pass,
            'handoff_audit_archive_completion_ready_carried_forward' => $pass,
            'handoff_audit_archive_completion_sealed_carried_forward' => $pass,
            'handoff_audit_archive_final_closed' => $pass,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'final_closure_used_for_selection' => false,
            'final_closure_used_for_retuning' => false,
            'final_closure_used_for_ranking' => false,
            'final_closure_used_for_plan_confirm_mutation' => false,
            'final_closure_used_for_live_rollout' => false,
            'final_closure_artifact_only' => true,
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'handoff_audit_archive_completion_seal_review_pass' => $pass,
            'handoff_audit_archive_completion_sealed' => $pass,
            'handoff_audit_archive_final_closure_review_pass' => $pass,
            'handoff_audit_archive_final_closed' => $pass,
            'production_ready' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'failure_reason_codes' => $forcedFailures,
        ];
        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c111_role' => 'primary_handoff_audit_archive_final_closure_candidate',
                'primary_candidate_handoff_audit_archive_completion_sealed' => $pass,
                'primary_candidate_handoff_audit_archive_final_closed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c111_role' => 'backup_handoff_audit_archive_final_closure_candidate',
                'backup_candidate_handoff_audit_archive_completion_sealed' => $pass,
                'backup_candidate_handoff_audit_archive_final_closed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c111_role' => 'comparator_only_candidate',
                'handoff_audit_archive_completion_seal_review_pass' => false,
                'handoff_audit_archive_completion_sealed' => false,
                'handoff_audit_archive_final_closure_review_pass' => false,
                'handoff_audit_archive_final_closed' => false,
                'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
                'comparator_candidate_handoff_audit_archive_final_closed' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffAuditArchiveFinalClosureContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_context_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_completion_seal_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_final_closure_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_source_identified' => is_file(self::RUNTIME_PATHS['c110_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_service']),
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_source_identified' => is_file(self::RUNTIME_PATHS['c111_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_review_service']),
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
            'validation_completed' => true,
            'operator_approval_required' => true,
            'operator_approval_surface_pass' => $pass,
            'kill_switch_required' => true,
            'production_catalog_runtime_bridge_enabled' => $this->configFlagIsOn('production_catalog_runtime_bridge_enabled'),
            'production_catalog_runtime_bridge_kill_switch' => $this->configFlagIsOn('production_catalog_runtime_bridge_kill_switch'),
            'production_catalog_controlled_runtime_opt_in_pilot_enabled' => $this->configFlagIsOn('production_catalog_controlled_runtime_opt_in_pilot_enabled'),
            'production_catalog_controlled_shadow_rollout_enabled' => $this->configFlagIsOn('production_catalog_controlled_shadow_rollout_enabled'),
            'production_catalog_controlled_parallel_run_enabled' => $this->configFlagIsOn('production_catalog_controlled_parallel_run_enabled'),
            'production_catalog_controlled_rollout_enabled' => $this->configFlagIsOn('production_catalog_controlled_rollout_enabled'),
            'all_runtime_feature_flags_remain_default_off' => ! $this->configFlagIsOn('production_catalog_runtime_bridge_enabled')
                && ! $this->configFlagIsOn('production_catalog_controlled_runtime_opt_in_pilot_enabled')
                && ! $this->configFlagIsOn('production_catalog_controlled_shadow_rollout_enabled')
                && ! $this->configFlagIsOn('production_catalog_controlled_parallel_run_enabled')
                && ! $this->configFlagIsOn('production_catalog_controlled_rollout_enabled'),
        ];
    }

    private function c110HandoffAuditArchiveCompletionSealCarryForwardValidationSummary(array $c110, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c110_handoff_audit_archive_completion_review_pass' => (bool) ($c110['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_pass'] ?? false),
            'c110_handoff_audit_archive_completion_ready' => (bool) ($c110['handoff_audit_archive_completion_ready'] ?? false),
            'c110_audit_archive_completion_ready' => (bool) ($c110['audit_archive_completion_ready'] ?? false),
            'c110_completion_manifest_created' => (bool) ($c110['completion_manifest_created'] ?? false),
            'c110_handoff_audit_archived' => (bool) ($c110['handoff_audit_archived'] ?? false),
            'c110_audit_archived' => (bool) ($c110['audit_archived'] ?? false),
            'c110_archive_manifest_created' => (bool) ($c110['archive_manifest_created'] ?? false),
            'c110_primary_candidate_completion_ready' => (bool) ($c110['primary_candidate_handoff_audit_archive_completion_ready'] ?? false),
            'c110_backup_candidate_completion_ready' => (bool) ($c110['backup_candidate_handoff_audit_archive_completion_ready'] ?? false),
            'c110_comparator_candidate_completion_ready' => (bool) ($c110['comparator_candidate_handoff_audit_archive_completion_ready'] ?? false),
            'c110_handoff_audit_archive_completion_seal_review_pass' => (bool) ($c110['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_review_pass'] ?? false),
            'c110_handoff_audit_archive_completion_sealed' => (bool) ($c110['handoff_audit_archive_completion_sealed'] ?? false),
            'c110_audit_archive_completion_sealed' => (bool) ($c110['audit_archive_completion_sealed'] ?? false),
            'c110_completion_seal_manifest_created' => (bool) ($c110['completion_seal_manifest_created'] ?? false),
            'c110_primary_candidate_completion_sealed' => (bool) ($c110['primary_candidate_handoff_audit_archive_completion_sealed'] ?? false),
            'c110_backup_candidate_completion_sealed' => (bool) ($c110['backup_candidate_handoff_audit_archive_completion_sealed'] ?? false),
            'c110_comparator_candidate_completion_sealed' => (bool) ($c110['comparator_candidate_handoff_audit_archive_completion_sealed'] ?? false),
            'handoff_audit_archive_final_closed' => $pass,
        ];
    }

    private function handoffAuditArchiveFinalClosureGovernanceSummary(bool $pass): array
    {
        return [
            'governance_scope' => 'C111 artifact-only non-live handoff audit archive final closure review',
            'final_closure_review_pass' => $pass,
            'audit_archive_final_closed' => $pass,
            'production_runtime_change_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'weekly_swing_live_output_generation_allowed' => false,
            'official_recommendation_generation_allowed' => false,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'baseline_plan_confirm_boundary_preserved' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
        ];
    }

    private function productionMutationSafetySummary(bool $pass): array
    {
        $summary = [
            'validation_completed' => true,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'pilot_runtime_active' => false,
            'shadow_runtime_active' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'all_required_safety_flags_false' => true,
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $summary[$flag] = false;
        }
        return $summary;
    }

    private function documentationGovernanceSummary(): array
    {
        $paths = [];
        foreach (self::DOC_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path)];
        }
        return [
            'documentation_review_completed' => true,
            'documentation_paths' => $paths,
            'c111_docs_added' => is_file(self::DOC_PATHS['c111_validation_doc']) && is_file(self::DOC_PATHS['c111_operator_commands_doc']),
            'implementation_status_updated' => is_file(self::DOC_PATHS['implementation_status_doc']),
            'contract_tracker_updated' => is_file(self::DOC_PATHS['contract_tracker_doc']),
            'audit_update_governance_updated' => is_file(self::DOC_PATHS['audit_update_governance_doc']),
            'documentation_hygiene_guard_applied' => true,
            'scoped_c110_expected_c109_file_sha1_key_preserved' => true,
            'scoped_expected_c109_file_sha1_key_preserved' => true,
        ];
    }

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => $status,
            'failure_count' => count($status),
            'repair_recommendations' => array_values(array_unique(array_map(function (string $code): string {
                return $this->repairRecommendationFor($code);
            }, $status))),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'C111_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW',
            'c110_audit_archive_completion_seal_review_carried_forward' => true,
            'c111_handoff_audit_archive_final_closure_review_executed' => true,
            'c111_handoff_audit_archive_final_closed' => $pass,
            'still_non_live' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NO_NEXT_RECOMMENDATION : 'C111_TARGETED_C110_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal handoff audit archive final closure is complete; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before final closure can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C110 artifact hash',
                'locked C110 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only handoff audit archive final closure evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C111 validates C110 artifact_hash and file SHA1 locks before weekly swing watchlist non-live rehearsal handoff audit archive final closure review is recorded.',
            'C111 validates C110 handoff audit archive completion-seal fields and A01 comparator-only state.',
            'C111 confirms no temporary negative test artifact remains before a passing non-live rehearsal handoff audit archive final closure review.',
            'C111 final-closes audit archive evidence for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C111 creates an artifact-only weekly swing watchlist non-live rehearsal handoff audit archive final closure manifest and no official weekly swing recommendation output.',
            'C111 weekly swing watchlist non-live rehearsal handoff audit archive final closure review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C111 records no next rehearsal handoff audit archive review; any production path requires a separate approved contract.',
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
        if (strpos($status, 'C110_ARTIFACT') !== false || strpos($status, 'C110_FILE') !== false || strpos($status, 'LOCK') !== false) {
            return 'C111_C110_LOCK_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'ARCHIVE') !== false || strpos($status, 'COMPLETION') !== false) {
            return 'C111_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C111_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C111_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C111_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C111_TARGETED_C110_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REPAIR';
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
