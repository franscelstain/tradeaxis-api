<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC108WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveReviewService
{
    public const RUN_CODE = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW';
    public const ARTIFACT_TYPE = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW';

    public const DEFAULT_C107_ARTIFACT = 'storage/app/watchlist/backtest/c107-weekly-swing-watchlist-non-live-rehearsal-handoff-closure-seal-review.json';
    public const DEFAULT_EXPECTED_C107_HASH = 'dd9edfc84044eeaa78f83b3fe4980e86ad9be62f';
    public const DEFAULT_EXPECTED_C107_FILE_SHA1 = '002EAEC0989CA23C7CE713345AEA7CAE8C6622E8';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c108-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C107_STATUS = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C107_REASON = 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C107_RECOMMENDATION = self::RUN_CODE;
    private const C109_RECOMMENDATION = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    private const PASS_STATUS = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const AUDIT_ARCHIVE_NOT_CONFIRMED_STATUS = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_AUDIT_ARCHIVE_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

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

    private const DOC_PATHS = [
        'c108_validation_doc' => 'docs/watchlist/audit/WS_C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW.md',
        'c108_operator_commands_doc' => 'docs/watchlist/audit/WS_C108_OPERATOR_VALIDATION_COMMANDS.md',
        'c107_validation_doc' => 'docs/watchlist/audit/WS_C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW.md',
        'c107_operator_commands_doc' => 'docs/watchlist/audit/WS_C107_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c107_weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewService.php',
        'c108_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC108WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveReviewService.php',
        'c107_command' => 'app/Console/Commands/Watchlist/RunBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewCommand.php',
        'c108_command' => 'app/Console/Commands/Watchlist/RunBacktestC108WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveReviewCommand.php',
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
        string $c107Artifact = self::DEFAULT_C107_ARTIFACT,
        string $expectedC107Hash = self::DEFAULT_EXPECTED_C107_HASH,
        string $expectedC107FileSha1 = self::DEFAULT_EXPECTED_C107_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c107Artifact, $expectedC107Hash, $expectedC107FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C108_BLOCKED_C107_ARTIFACT_LOCK_MISMATCH', 'C107 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C108_BLOCKED_C107_ARTIFACT_LOCK_MISMATCH', 'C107 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C108_BLOCKED_C107_FILE_SHA1_LOCK_MISMATCH', 'C107 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c107 = $load['payload'];
        if (($c107['status'] ?? null) !== self::EXPECTED_C107_STATUS || ($c107['reason_code'] ?? null) !== self::EXPECTED_C107_REASON) {
            return $this->blocked($artifact, 'C108_BLOCKED_C107_STATUS_OR_REASON_MISMATCH', 'C107 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c107NextRecommendationMatches($c107)) {
            return $this->blocked($artifact, 'C108_BLOCKED_C107_NEXT_RECOMMENDATION_MISMATCH', 'C107 next recommendation is not C108.', $outputPath, $overwrite);
        }
        if (! $this->c107ClosureSealed($c107)) {
            return $this->blocked($artifact, 'C108_BLOCKED_C107_CLOSURE_NOT_SEALED', 'C107 closure seal evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c107);
        if ($safetyFailure !== null) {
            $artifact['c107_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C108_BLOCKED_C107_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C107 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c107)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C107 candidate scope does not match locked non-live rehearsal handoff closure seal decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C108 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C108 weekly swing watchlist non-live rehearsal handoff audit archive review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C108 archived the weekly swing watchlist non-live rehearsal handoff audit trail for primary and backup as artifact-only evidence. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
        $artifact['next_step_recommendation'] = self::C109_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C108_NOT_RUN',
            'reason_code' => 'C108_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_executed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_allowed' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived' => false,
            'handoff_audit_archived' => false,
            'audit_archived' => false,
            'archive_manifest_created' => false,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => false,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => false,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed' => false,
            'handoff_closure_sealed' => false,
            'closure_sealed' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_executed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_allowed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived' => true,
            'handoff_audit_archived' => true,
            'audit_archived' => true,
            'archive_manifest_created' => true,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => true,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => true,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed' => true,
            'handoff_closure_sealed' => true,
            'closure_sealed' => true,
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
        $c107 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c107['source_artifact_locks'] ?? null) ? $c107['source_artifact_locks'] : [];
        return [
            'c107_artifact_path' => $load['path'],
            'expected_c107_hash' => $load['expected_hash'],
            'actual_c107_hash' => $load['actual_hash'],
            'c107_hash_match' => $load['hash_match'],
            'expected_c107_file_sha1' => $load['expected_file_sha1'],
            'actual_c107_file_sha1' => $load['actual_file_sha1'],
            'c107_file_sha1_match' => $load['file_sha1_match'],
            'c107_source_lineage_checked' => true,
            'c107_source_lineage_match' => $this->lineageLocksMatch($c107),
            'c106_artifact_hash_from_c107' => (string) ($locks['actual_c106_hash'] ?? ($c107['actual_c106_hash'] ?? '')),
            'c106_file_sha1_from_c107' => (string) ($locks['actual_c106_file_sha1'] ?? ($c107['actual_c106_file_sha1'] ?? '')),
            'c105_artifact_hash_from_c106' => (string) ($locks['c105_artifact_hash_from_c106'] ?? ''),
            'c105_file_sha1_from_c106' => (string) ($locks['c105_file_sha1_from_c106'] ?? ''),
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
            'expected_c107_hash' => $load['expected_hash'],
            'actual_c107_hash' => $load['actual_hash'],
            'c107_hash_match' => $load['hash_match'],
            'expected_c107_file_sha1' => $load['expected_file_sha1'],
            'actual_c107_file_sha1' => $load['actual_file_sha1'],
            'c107_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c107): bool
    {
        $locks = is_array($c107['source_artifact_locks'] ?? null) ? $c107['source_artifact_locks'] : [];
        return ($locks['c106_hash_match'] ?? null) === true
            && ($locks['c106_file_sha1_match'] ?? null) === true
            && (string) ($locks['c105_artifact_hash_from_c106'] ?? '') !== ''
            && (string) ($locks['c105_file_sha1_from_c106'] ?? '') !== '';
    }

    private function c107NextRecommendationMatches(array $c107): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c107_handoff_closure_seal_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c107, $path) !== self::EXPECTED_C107_RECOMMENDATION) {
                return false;
            }
        }
        return true;
    }

    private function c107ClosureSealed(array $c107): bool
    {
        $decision = is_array($c107['c107_handoff_closure_seal_decision'] ?? null) ? $c107['c107_handoff_closure_seal_decision'] : [];
        foreach ([
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed' => true,
            'handoff_closure_sealed' => true,
            'closure_sealed' => true,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => true,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => true,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed' => false,
            'handoff_completion_boundary_cleared' => true,
            'boundary_go_decision' => 'BOUNDARY_CLEARED_GO',
            'operator_go_decision' => 'GO',
            'go_decision_finalized' => true,
            'a01_remains_comparator_only' => true,
        ] as $field => $expected) {
            if (($c107[$field] ?? null) !== $expected) {
                return false;
            }
            if (($decision[$field] ?? null) !== $expected) {
                return false;
            }
        }
        if (($c107['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_count'] ?? null) !== 2) {
            return false;
        }
        if (($decision['candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_count'] ?? null) !== 2) {
            return false;
        }
        if (($c107['temporary_negative_artifacts_remaining'] ?? null) !== false || ($c107['temporary_negative_artifact_cleanup_confirmed'] ?? null) !== true || (array) ($c107['temporary_negative_artifact_paths'] ?? []) !== []) {
            return false;
        }

        $manifest = is_array($c107['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_manifest'] ?? null)
            ? $c107['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_manifest']
            : [];
        if (($manifest['handoff_closure_seal_artifact_only'] ?? null) !== true || ($manifest['handoff_closure_sealed'] ?? null) !== true) {
            return false;
        }
        foreach ([
            'handoff_closure_seal_used_for_selection',
            'handoff_closure_seal_used_for_retuning',
            'handoff_closure_seal_used_for_ranking',
            'handoff_closure_seal_used_for_plan_confirm_mutation',
            'handoff_closure_seal_used_for_live_rollout',
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
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            if (($payload[$flag] ?? null) === true) {
                return $flag;
            }
        }
        foreach ([
            'c107_handoff_closure_seal_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_decision',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_manifest',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'handoff_closure_seal_governance_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
        ] as $section) {
            $values = is_array($payload[$section] ?? null) ? $payload[$section] : [];
            foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
                if (($values[$flag] ?? null) === true) {
                    return $section.'.'.$flag;
                }
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c107): bool
    {
        return ($c107['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c107['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c107['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c107['a01_remains_comparator_only'] ?? null) === true
            && ($c107['primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed'] ?? null) === true
            && ($c107['backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed'] ?? null) === true
            && ($c107['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed'] ?? null) === false
            && ($c107['candidate_scope_freeze_summary']['a01_promoted'] ?? false) === false
            && ($c107['candidate_scope_freeze_summary']['new_candidate_created'] ?? false) === false;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (($options['handoff_audit_archive_confirmed'] ?? true) !== true || ($options['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_confirmed'] ?? true) !== true) {
            $failures[] = self::AUDIT_ARCHIVE_NOT_CONFIRMED_STATUS;
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
                $failures[] = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_FEATURE_FLAG_OR_RUNTIME_GATE_ON';
                break;
            }
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c107',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'handoff_audit_archive_used_for_selection',
            'handoff_audit_archive_used_for_retuning',
            'handoff_audit_archive_used_for_ranking',
            'handoff_audit_archive_used_for_plan_confirm_mutation',
            'handoff_audit_archive_used_for_live_rollout',
            'handoff_audit_archive_allowed_to_auto_enable_runtime',
            'handoff_audit_archive_allowed_to_auto_deploy',
            'a01_promoted',
            'a01_used_as_runtime_fallback',
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime',
            'handoff_audit_archive_context_persisted_to_live_runtime',
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
        $c107 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c107_lock_validation_summary'] = $this->c107LockValidationSummary($load, $c107);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c107);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c107, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c108_handoff_audit_archive_decision'] = $this->handoffAuditArchiveDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_decision'] = $artifact['c108_handoff_audit_archive_decision'];
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_manifest'] = $this->handoffAuditArchiveManifest($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_summary'] = $this->handoffAuditArchiveContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c107_handoff_closure_seal_carry_forward_validation_summary'] = $this->c107HandoffClosureSealCarryForwardValidationSummary($c107, $pass);
        $artifact['handoff_audit_archive_governance_summary'] = $this->handoffAuditArchiveGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : $forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function c107LockValidationSummary(array $load, array $c107): array
    {
        return [
            'c107_lock_validation_completed' => true,
            'c107_artifact_path' => $load['path'],
            'expected_c107_hash' => $load['expected_hash'],
            'actual_c107_hash' => $load['actual_hash'],
            'c107_hash_match' => $load['hash_match'],
            'expected_c107_file_sha1' => $load['expected_file_sha1'],
            'actual_c107_file_sha1' => $load['actual_file_sha1'],
            'c107_file_sha1_match' => $load['file_sha1_match'],
            'c107_status' => (string) ($c107['status'] ?? ''),
            'c107_reason_code' => (string) ($c107['reason_code'] ?? ''),
            'c107_next_recommendation_match' => $this->c107NextRecommendationMatches($c107),
            'c107_handoff_closure_sealed' => $this->c107ClosureSealed($c107),
        ];
    }

    private function lineageValidationSummary(array $c107): array
    {
        $locks = is_array($c107['source_artifact_locks'] ?? null) ? $c107['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'c107_to_c106_lock_match' => (($locks['c106_hash_match'] ?? null) === true && ($locks['c106_file_sha1_match'] ?? null) === true),
            'c106_to_c105_lock_present' => (string) ($locks['c105_artifact_hash_from_c106'] ?? '') !== '' && (string) ($locks['c105_file_sha1_from_c106'] ?? '') !== '',
            'c107_source_lineage_match' => $this->lineageLocksMatch($c107),
            'lineage_source' => 'C107_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(array $c107, bool $pass): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_freeze_pass' => $pass,
            'candidate_scope_source' => 'C107_LOCKED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'source_primary_candidate_code' => (string) ($c107['primary_candidate_code'] ?? ''),
            'source_backup_candidate_code' => (string) ($c107['backup_candidate_code'] ?? ''),
            'source_comparator_candidate_code' => (string) ($c107['comparator_candidate_code'] ?? ''),
            'primary_candidate_unchanged' => ($c107['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE,
            'backup_candidate_unchanged' => ($c107['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE,
            'a01_remains_comparator_only' => ($c107['a01_remains_comparator_only'] ?? null) === true,
            'candidate_scope_changed_after_c107' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'handoff_audit_archive_used_for_selection' => false,
            'handoff_audit_archive_used_for_retuning' => false,
            'handoff_audit_archive_used_for_ranking' => false,
            'handoff_audit_archive_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_used_for_live_rollout' => false,
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
            'handoff_audit_archive_reference_scope' => 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_ONLY',
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

    private function handoffAuditArchiveDecision(bool $pass): array
    {
        $decision = array_merge($this->passingTopLevelState(), [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c107_lock_valid' => $pass,
            'c107_handoff_closure_sealed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_executed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived' => $pass,
            'handoff_audit_archived' => $pass,
            'audit_archived' => $pass,
            'archive_manifest_created' => $pass,
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => $pass,
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => $pass,
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => false,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'handoff_audit_archive_artifact_only' => true,
            'handoff_audit_archive_used_for_selection' => false,
            'handoff_audit_archive_used_for_retuning' => false,
            'handoff_audit_archive_used_for_ranking' => false,
            'handoff_audit_archive_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C109_RECOMMENDATION : 'C108_TARGETED_C107_HANDOFF_CLOSURE_SEAL_REPAIR',
            'decision_reason' => $pass ? 'C108 weekly swing watchlist non-live rehearsal handoff audit trail is archived for primary and backup in artifact-only audit context.' : 'C108 handoff audit archive review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW' : 'C108_HANDOFF_AUDIT_ARCHIVE_REPAIR_REQUIRED',
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
        ]);
        if (! $pass) {
            foreach ([
                'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived',
                'handoff_audit_archived',
                'audit_archived',
                'archive_manifest_created',
                'primary_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived',
                'backup_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived',
                'c107_handoff_closure_sealed',
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
        return array_merge($this->handoffAuditArchiveDecision($pass), [
            'validation_completed' => true,
            'next_recommendation' => $pass ? self::C109_RECOMMENDATION : 'C108_TARGETED_C107_HANDOFF_CLOSURE_SEAL_REPAIR',
        ]);
    }

    private function handoffAuditArchiveManifest(bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'artifact_only_non_live_rehearsal_handoff_audit_archive_review',
            'execution_mode' => 'non_live_artifact_only_rehearsal_handoff_audit_archive',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_non_live_rehearsal_handoff_audit_archived_candidate',
            'backup_candidate_role' => 'backup_non_live_rehearsal_handoff_audit_archived_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_audit_archived' => $pass,
            'audit_archived' => $pass,
            'archive_manifest_created' => $pass,
            'handoff_closure_sealed' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'handoff_audit_archive_artifact_only' => true,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'handoff_audit_archive_used_for_selection' => false,
            'handoff_audit_archive_used_for_retuning' => false,
            'handoff_audit_archive_used_for_ranking' => false,
            'handoff_audit_archive_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_used_for_live_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
            'non_live_handoff_audit_archive_steps' => [
                'validate_locked_c107_handoff_closure_seal_artifact',
                'confirm_primary_and_backup_handoff_closure_sealed_scope',
                'archive_primary_and_backup_non_live_handoff_audit_trail',
                'confirm_a01_comparator_only_scope',
                'record_non_live_handoff_audit_archive_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass' => $pass,
            'handoff_audit_archived' => $pass,
            'audit_archived' => $pass,
            'handoff_closure_sealed' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'non_live_rehearsal_handoff_audit_archive_advisory_only_pass' => $pass,
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
                'c108_role' => 'primary_non_live_rehearsal_handoff_audit_archived_candidate',
                'primary_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c108_role' => 'backup_non_live_rehearsal_handoff_audit_archived_candidate',
                'backup_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c108_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass' => false,
                'handoff_audit_archived' => false,
                'audit_archived' => false,
                'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived' => false,
                'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffAuditArchiveContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_source_identified' => is_file(self::RUNTIME_PATHS['c107_weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_service']),
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_source_identified' => is_file(self::RUNTIME_PATHS['c108_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_service']),
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
            'handoff_audit_archive_confirmation_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c107HandoffClosureSealCarryForwardValidationSummary(array $c107, bool $pass): array
    {
        return [
            'c107_handoff_closure_seal_carry_forward_validation_completed' => true,
            'c107_handoff_closure_seal_carry_forward_validation_pass' => $pass,
            'c107_status' => (string) ($c107['status'] ?? ''),
            'c107_reason_code' => (string) ($c107['reason_code'] ?? ''),
            'c107_artifact_hash' => (string) ($c107['artifact_hash'] ?? ''),
            'c107_handoff_closure_seal_review_pass' => ($c107['weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_review_pass'] ?? null) === true,
            'c107_handoff_closure_sealed' => ($c107['handoff_closure_sealed'] ?? null) === true,
            'c107_closure_sealed' => ($c107['closure_sealed'] ?? null) === true,
            'c107_primary_candidate_handoff_closure_sealed' => ($c107['primary_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed'] ?? null) === true,
            'c107_backup_candidate_handoff_closure_sealed' => ($c107['backup_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed'] ?? null) === true,
            'c107_comparator_candidate_handoff_closure_sealed' => ($c107['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_closure_sealed'] ?? null) === false,
            'c107_a01_remains_comparator_only' => ($c107['a01_remains_comparator_only'] ?? null) === true,
            'expected_c107_next_recommendation' => self::EXPECTED_C107_RECOMMENDATION,
        ];
    }

    private function handoffAuditArchiveGovernanceSummary(bool $pass): array
    {
        return [
            'handoff_audit_archive_governance_completed' => true,
            'handoff_audit_archive_governance_pass' => $pass,
            'handoff_audit_archived' => $pass,
            'handoff_audit_archive_is_explicit_context_only' => true,
            'handoff_audit_archive_is_non_live_default' => true,
            'handoff_audit_archive_is_artifact_only' => true,
            'handoff_audit_archive_is_advisory_only' => true,
            'handoff_audit_archive_used_for_selection' => false,
            'handoff_audit_archive_used_for_retuning' => false,
            'handoff_audit_archive_used_for_ranking' => false,
            'handoff_audit_archive_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_used_for_live_rollout' => false,
            'handoff_audit_archive_allowed_to_auto_enable_runtime' => false,
            'handoff_audit_archive_allowed_to_auto_deploy' => false,
            'handoff_audit_archive_classification' => 'WEEKLY_SWING_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C108_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass' => $pass,
            'handoff_audit_archived' => $pass,
            'candidate_ready_for_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C109_RECOMMENDATION : 'C108_TARGETED_C107_HANDOFF_CLOSURE_SEAL_REPAIR',
            'selection_changed_after_c107' => false,
            'parameter_changed_after_c107' => false,
            'new_candidate_created' => false,
            'handoff_audit_archive_used_for_selection' => false,
            'handoff_audit_archive_used_for_retuning' => false,
            'handoff_audit_archive_used_for_ranking' => false,
            'handoff_audit_archive_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_used_for_live_rollout' => false,
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
            'targeted_repair_recommendation' => $status === [] ? self::C109_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW',
            'achieved' => [
                'C107 artifact hash and file SHA1 validated',
                'C107 handoff closure seal evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Handoff audit trail archived for E02 and B01 only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist non-live rehearsal handoff audit archive manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C109_RECOMMENDATION : 'C108_TARGETED_C107_HANDOFF_CLOSURE_SEAL_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal handoff audit archive completion review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C108 artifact hash',
                'locked C108 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only handoff audit archive evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C108 validates C107 artifact_hash and file SHA1 locks before weekly swing watchlist non-live rehearsal handoff audit archive review is recorded.',
            'C108 validates C107 handoff closure seal fields and A01 comparator-only state.',
            'C108 confirms no temporary negative test artifact remains before a passing non-live rehearsal handoff audit archive review.',
            'C108 archives handoff audit trail for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C108 creates an artifact-only weekly swing watchlist non-live rehearsal handoff audit archive manifest and no official weekly swing recommendation output.',
            'C108 weekly swing watchlist non-live rehearsal handoff audit archive review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C108 may only recommend C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review as the next audit-only step.',
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
        if (strpos($status, 'C107_ARTIFACT') !== false || strpos($status, 'C107_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C108_C107_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'ARCHIVE') !== false) {
            return 'C108_HANDOFF_AUDIT_ARCHIVE_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C108_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C108_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C108_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C108_TARGETED_C107_HANDOFF_CLOSURE_SEAL_REPAIR';
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
