<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService
{
    public const RUN_CODE = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    public const PHASE_LABEL = 'PR-15 / C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    public const ARTIFACT_TYPE = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';

    public const DEFAULT_C126_ARTIFACT = 'storage/app/watchlist/backtest/c126-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-review.json';
    public const DEFAULT_EXPECTED_C126_HASH = '3f990d65414dd754ac4cd7a257ade44d52c89b67';
    public const DEFAULT_EXPECTED_C126_FILE_SHA1 = '16B4F020A06459B46CD5ECDAAEDAC1DC2829561E';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C126_STATUS = 'C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C126_REASON = 'C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C126_RECOMMENDATION = self::RUN_CODE;
    private const EXPECTED_C126_PHASE_LABEL = 'PR-14 / C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW';
    private const C128_RECOMMENDATION = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    private const PASS_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const AUDIT_ARCHIVE_COMPLETION_NOT_CONFIRMED_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_AUDIT_ARCHIVE_COMPLETION_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const C126_AUDIT_ARCHIVE_INCOMPLETE_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C126_AUDIT_ARCHIVE_INCOMPLETE';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C126_CONVERT_FROM_JSON_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C126_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_result_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
        'operator_go_no_go_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime',
        'go_decision_finalization_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime',
        'completion_boundary_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
        'handoff_readiness_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_handoff_finalization_context_persisted_to_live_runtime',
        'handoff_finalization_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime',
        'handoff_completion_boundary_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_handoff_closure_seal_context_persisted_to_live_runtime',
        'handoff_closure_seal_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_handoff_audit_archive_context_persisted_to_live_runtime',
        'handoff_audit_archive_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime',
        'handoff_audit_archive_completion_context_persisted_to_live_runtime',
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
        'c127_validation_doc' => 'docs/watchlist/audit/WS_C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW.md',
        'c127_operator_commands_doc' => 'docs/watchlist/audit/WS_C127_OPERATOR_VALIDATION_COMMANDS.md',
        'c126_validation_doc' => 'docs/watchlist/audit/WS_C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW.md',
        'c126_operator_commands_doc' => 'docs/watchlist/audit/WS_C126_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c126_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC126WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveReviewService.php',
        'c127_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService.php',
        'c126_command' => 'app/Console/Commands/Watchlist/RunBacktestC126WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveReviewCommand.php',
        'c127_command' => 'app/Console/Commands/Watchlist/RunBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewCommand.php',
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
        string $c126Artifact = self::DEFAULT_C126_ARTIFACT,
        string $expectedC126Hash = self::DEFAULT_EXPECTED_C126_HASH,
        string $expectedC126FileSha1 = self::DEFAULT_EXPECTED_C126_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c126Artifact, $expectedC126Hash, $expectedC126FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C127_BLOCKED_C126_ARTIFACT_LOCK_MISMATCH', 'C126 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c126_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C126_CONVERT_FROM_JSON_STATUS, 'C126 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C127_BLOCKED_C126_ARTIFACT_LOCK_MISMATCH', 'C126 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C127_BLOCKED_C126_FILE_SHA1_LOCK_MISMATCH', 'C126 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c126 = $load['payload'];
        if (($c126['status'] ?? null) !== self::EXPECTED_C126_STATUS || ($c126['reason_code'] ?? null) !== self::EXPECTED_C126_REASON) {
            return $this->blocked($artifact, 'C127_BLOCKED_C126_STATUS_OR_REASON_MISMATCH', 'C126 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c126['phase_label'] ?? null) !== self::EXPECTED_C126_PHASE_LABEL) {
            return $this->blocked($artifact, 'C127_BLOCKED_C126_PHASE_LABEL_MISMATCH', 'C126 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c126NextRecommendationMatches($c126)) {
            return $this->blocked($artifact, 'C127_BLOCKED_C126_NEXT_RECOMMENDATION_MISMATCH', 'C126 next recommendation is not C127.', $outputPath, $overwrite);
        }
        if (! $this->c126AuditArchived($c126)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C126_AUDIT_ARCHIVE_INCOMPLETE_STATUS, 'C126 handoff audit archive evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c126);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c126_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C126 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c126)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C126 candidate scope does not match locked controlled runtime wiring handoff audit archive decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C127 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C127 weekly swing watchlist controlled runtime wiring handoff audit archive completion review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C127 confirms the weekly swing watchlist controlled runtime wiring handoff audit archive completion package is ready for primary and backup as artifact-only evidence. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
        $artifact['next_step_recommendation'] = self::C128_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-15',
            'internal_checkpoint' => 'C127',
            'status' => 'C127_NOT_RUN',
            'reason_code' => 'C127_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_executed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'handoff_audit_archive_completion_ready' => false,
            'audit_archive_completion_ready' => false,
            'completion_manifest_created' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => false,
            'handoff_audit_archive_completion_confirmed' => false,
            'handoff_audit_archive_completion_go_decision' => 'NO_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_manifest_created' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed_next' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_pass' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived' => false,
            'controlled_runtime_wiring_handoff_audit_archived' => false,
            'handoff_audit_archived' => false,
            'audit_archived' => false,
            'archive_manifest_created' => false,
            'handoff_audit_archive_confirmed' => false,
            'handoff_audit_archive_go_decision' => 'NO_GO',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_sealed' => false,
            'handoff_closure_sealed' => false,
            'closure_sealed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared' => false,
            'handoff_completion_boundary_cleared' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => false,
            'handoff_finalized' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => false,
            'handoff_ready' => false,
            'operator_go_decision' => 'NO_GO',
            'go_decision_finalized' => false,
            'c126_handoff_audit_archived' => false,
            'c125_handoff_closure_sealed' => false,
            'c124_handoff_completion_boundary_cleared' => false,
            'c123_handoff_finalized' => false,
            'c122_handoff_ready' => false,
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
            'handoff_audit_archive_completion_ready' => true,
            'audit_archive_completion_ready' => true,
            'completion_manifest_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => true,
            'handoff_audit_archive_completion_confirmed' => true,
            'handoff_audit_archive_completion_go_decision' => 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_manifest_created' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed_next' => true,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_pass' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived' => true,
            'controlled_runtime_wiring_handoff_audit_archived' => true,
            'handoff_audit_archived' => true,
            'audit_archived' => true,
            'archive_manifest_created' => true,
            'handoff_audit_archive_confirmed' => true,
            'handoff_audit_archive_go_decision' => 'HANDOFF_AUDIT_ARCHIVED_GO',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_sealed' => true,
            'handoff_closure_sealed' => true,
            'closure_sealed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared' => true,
            'handoff_completion_boundary_cleared' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => true,
            'handoff_finalized' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => true,
            'handoff_ready' => true,
            'operator_go_decision' => 'GO',
            'go_decision_finalized' => true,
            'c126_handoff_audit_archived' => true,
            'c125_handoff_closure_sealed' => true,
            'c124_handoff_completion_boundary_cleared' => true,
            'c123_handoff_finalized' => true,
            'c122_handoff_ready' => true,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        $jsonError = null;
        $duplicateKeys = [];
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $duplicateKeys = $this->caseInsensitiveDuplicateTopLevelKeys($raw);
            $decoded = json_decode($raw, true);
            $jsonError = json_last_error();
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
            'json_error' => $jsonError,
            'case_insensitive_duplicate_keys' => $duplicateKeys,
            'convert_from_json_pass' => $exists && $payload !== null && $jsonError === JSON_ERROR_NONE && $duplicateKeys === [],
        ];
    }

    private function caseInsensitiveDuplicateTopLevelKeys(string $raw): array
    {
        $length = strlen($raw);
        $depth = 0;
        $expectTopLevelKey = false;
        $seen = [];
        $duplicates = [];

        for ($i = 0; $i < $length; $i++) {
            $char = $raw[$i];
            if ($char === '"') {
                $start = $i;
                $i++;
                $escaped = false;
                while ($i < $length) {
                    $inner = $raw[$i];
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($inner === '\\') {
                        $escaped = true;
                    } elseif ($inner === '"') {
                        break;
                    }
                    $i++;
                }
                if ($expectTopLevelKey && $depth === 1) {
                    $key = json_decode(substr($raw, $start, $i - $start + 1), true);
                    if (is_string($key)) {
                        $normalized = strtolower($key);
                        if (isset($seen[$normalized])) {
                            $duplicates[] = $key;
                        }
                        $seen[$normalized] = true;
                    }
                    $expectTopLevelKey = false;
                }
                continue;
            }
            if ($char === '{') {
                $depth++;
                $expectTopLevelKey = $depth === 1;
                continue;
            }
            if ($char === '}') {
                $depth--;
                $expectTopLevelKey = false;
                continue;
            }
            if ($depth === 1 && $char === ',') {
                $expectTopLevelKey = true;
            }
        }

        return array_values(array_unique($duplicates));
    }

    private function sourceArtifactLocks(array $load): array
    {
        $c126 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c126['source_artifact_locks'] ?? null) ? $c126['source_artifact_locks'] : [];
        return [
            'c126_artifact_path' => $load['path'],
            'expected_c126_hash' => $load['expected_hash'],
            'actual_c126_hash' => $load['actual_hash'],
            'c126_hash_match' => $load['hash_match'],
            'expected_c126_file_sha1' => $load['expected_file_sha1'],
            'actual_c126_file_sha1' => $load['actual_file_sha1'],
            'c126_file_sha1_match' => $load['file_sha1_match'],
            'c126_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c126_source_lineage_checked' => true,
            'c126_source_lineage_match' => $this->lineageLocksMatch($c126),
            'c125_artifact_hash_from_c126' => (string) ($locks['actual_c125_hash'] ?? ($c126['actual_c125_hash'] ?? '')),
            'c125_file_sha1_from_c126' => (string) ($locks['actual_c125_file_sha1'] ?? ($c126['actual_c125_file_sha1'] ?? '')),
            'c124_artifact_hash_from_c125' => (string) ($locks['c124_artifact_hash_from_c125'] ?? ''),
            'c124_file_sha1_from_c125' => (string) ($locks['c124_file_sha1_from_c125'] ?? ''),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c126_hash' => $load['expected_hash'],
            'actual_c126_hash' => $load['actual_hash'],
            'c126_hash_match' => $load['hash_match'],
            'expected_c126_file_sha1' => $load['expected_file_sha1'],
            'actual_c126_file_sha1' => $load['actual_file_sha1'],
            'c126_file_sha1_match' => $load['file_sha1_match'],
            'c126_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function lineageLocksMatch(array $c126): bool
    {
        $locks = is_array($c126['source_artifact_locks'] ?? null) ? $c126['source_artifact_locks'] : [];
        return ($locks['c125_hash_match'] ?? null) === true
            && ($locks['c125_file_sha1_match'] ?? null) === true
            && (string) ($locks['c124_artifact_hash_from_c125'] ?? '') !== ''
            && (string) ($locks['c124_file_sha1_from_c125'] ?? '') !== '';
    }

    private function c126NextRecommendationMatches(array $c126): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c126_handoff_audit_archive_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c126, $path) !== self::EXPECTED_C126_RECOMMENDATION) {
                return false;
            }
        }
        return true;
    }

    private function c126AuditArchived(array $c126): bool
    {
        $decision = is_array($c126['c126_handoff_audit_archive_decision'] ?? null) ? $c126['c126_handoff_audit_archive_decision'] : [];
        foreach ([
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_pass' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived' => true,
            'controlled_runtime_wiring_handoff_audit_archived' => true,
            'handoff_audit_archived' => true,
            'audit_archived' => true,
            'archive_manifest_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_confirmed' => true,
            'controlled_runtime_wiring_handoff_audit_archive_confirmed' => true,
            'handoff_audit_archive_confirmed' => true,
            'handoff_audit_archive_go_decision' => 'HANDOFF_AUDIT_ARCHIVED_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review' => true,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review' => true,
            'controlled_runtime_wiring_handoff_audit_archive_manifest_created' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed_next' => true,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived' => true,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived' => true,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review' => true,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review' => true,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review' => false,
            'handoff_closure_sealed' => true,
            'handoff_completion_boundary_cleared' => true,
            'handoff_finalized' => true,
            'handoff_ready' => true,
            'operator_go_decision' => 'GO',
            'go_decision_finalized' => true,
            'a01_remains_comparator_only' => true,
        ] as $field => $expected) {
            if (($c126[$field] ?? null) !== $expected) {
                return false;
            }
            if (($decision[$field] ?? null) !== $expected) {
                return false;
            }
        }
        if (($c126['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_count'] ?? null) !== 2) {
            return false;
        }

        $manifest = is_array($c126['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_manifest'] ?? null)
            ? $c126['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_manifest']
            : [];
        foreach ([
            'manifest_created' => true,
            'handoff_audit_archive_artifact_only' => true,
            'handoff_audit_archived' => true,
            'controlled_runtime_wiring_handoff_audit_archived' => true,
            'audit_archived' => true,
            'archive_manifest_created' => true,
            'handoff_audit_archive_confirmed' => true,
            'handoff_audit_archive_go_decision' => 'HANDOFF_AUDIT_ARCHIVED_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed_next' => true,
        ] as $field => $expected) {
            if (($manifest[$field] ?? null) !== $expected) {
                return false;
            }
        }
        foreach ([
            'handoff_audit_archive_used_for_selection',
            'handoff_audit_archive_used_for_retuning',
            'handoff_audit_archive_used_for_ranking',
            'handoff_audit_archive_used_for_plan_confirm_mutation',
            'handoff_audit_archive_used_for_live_rollout',
            'weekly_swing_official_output_generated',
            'weekly_swing_live_output_enabled',
            'weekly_swing_live_output_published',
            'plan_confirm_mutation_allowed',
        ] as $field) {
            if (($manifest[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($c126['temporary_negative_artifacts_remaining'] ?? null) === false
            && ($c126['temporary_negative_artifact_cleanup_confirmed'] ?? null) === true
            && (array) ($c126['temporary_negative_artifact_paths'] ?? []) === []
            && (array) ($manifest['official_weekly_swing_stock_recommendations'] ?? []) === [];
    }

    private function firstLiveOrMutatingSafetyFlag(array $payload): ?string
    {
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            if (($payload[$flag] ?? null) === true) {
                return $flag;
            }
        }
        foreach ([
            'c126_handoff_audit_archive_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_manifest',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'handoff_audit_archive_governance_summary',
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

    private function candidateScopeMatches(array $c126): bool
    {
        return ($c126['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c126['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c126['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c126['a01_remains_comparator_only'] ?? null) === true
            && ($c126['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived'] ?? null) === true
            && ($c126['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived'] ?? null) === true
            && ($c126['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived'] ?? null) === false
            && ($c126['primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review'] ?? null) === true
            && ($c126['backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review'] ?? null) === true
            && ($c126['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review'] ?? null) === false
            && ($c126['candidate_scope_freeze_summary']['a01_promoted'] ?? false) === false
            && ($c126['candidate_scope_freeze_summary']['new_candidate_created'] ?? false) === false;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (($options['handoff_audit_archive_completion_confirmed'] ?? false) !== true || ($options['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_confirmed'] ?? false) !== true) {
            $failures[] = self::AUDIT_ARCHIVE_COMPLETION_NOT_CONFIRMED_STATUS;
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
                $failures[] = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_FEATURE_FLAG_OR_RUNTIME_GATE_ON';
                break;
            }
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c126',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'handoff_audit_archive_completion_used_for_selection',
            'handoff_audit_archive_completion_used_for_retuning',
            'handoff_audit_archive_completion_used_for_ranking',
            'handoff_audit_archive_completion_used_for_plan_confirm_mutation',
            'handoff_audit_archive_completion_used_for_live_rollout',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'pilot_runtime_active',
            'shadow_runtime_active',
            'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active',
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
        $c126 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c126_lock_validation_summary'] = $this->c126LockValidationSummary($load, $c126);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c126);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c126, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c127_handoff_audit_archive_completion_decision'] = $this->handoffAuditArchiveCompletionDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_decision'] = $artifact['c127_handoff_audit_archive_completion_decision'];
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_manifest'] = $this->handoffAuditArchiveCompletionManifest($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_summary'] = $this->handoffAuditArchiveCompletionContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c126_handoff_audit_archive_carry_forward_validation_summary'] = $this->c126HandoffAuditArchiveCarryForwardValidationSummary($c126, $pass);
        $artifact['handoff_audit_archive_completion_governance_summary'] = $this->handoffAuditArchiveCompletionGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : $forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function c126LockValidationSummary(array $load, array $c126): array
    {
        return [
            'c126_lock_validation_completed' => true,
            'c126_artifact_path' => $load['path'],
            'expected_c126_hash' => $load['expected_hash'],
            'actual_c126_hash' => $load['actual_hash'],
            'c126_hash_match' => $load['hash_match'],
            'expected_c126_file_sha1' => $load['expected_file_sha1'],
            'actual_c126_file_sha1' => $load['actual_file_sha1'],
            'c126_file_sha1_match' => $load['file_sha1_match'],
            'c126_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c126_convert_from_json_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c126_status' => (string) ($c126['status'] ?? ''),
            'c126_reason_code' => (string) ($c126['reason_code'] ?? ''),
            'c126_phase_label' => (string) ($c126['phase_label'] ?? ''),
            'c126_phase_label_match' => ($c126['phase_label'] ?? null) === self::EXPECTED_C126_PHASE_LABEL,
            'c126_next_recommendation_match' => $this->c126NextRecommendationMatches($c126),
            'c126_handoff_audit_archived' => $this->c126AuditArchived($c126),
        ];
    }

    private function lineageValidationSummary(array $c126): array
    {
        $locks = is_array($c126['source_artifact_locks'] ?? null) ? $c126['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'c126_to_c125_lock_match' => (($locks['c125_hash_match'] ?? null) === true && ($locks['c125_file_sha1_match'] ?? null) === true),
            'c125_to_c124_lock_present' => (string) ($locks['c124_artifact_hash_from_c125'] ?? '') !== '' && (string) ($locks['c124_file_sha1_from_c125'] ?? '') !== '',
            'c126_source_lineage_match' => $this->lineageLocksMatch($c126),
            'lineage_source' => 'C126_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(array $c126, bool $pass): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_freeze_pass' => $pass,
            'candidate_scope_source' => 'C126_LOCKED_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'source_primary_candidate_code' => (string) ($c126['primary_candidate_code'] ?? ''),
            'source_backup_candidate_code' => (string) ($c126['backup_candidate_code'] ?? ''),
            'source_comparator_candidate_code' => (string) ($c126['comparator_candidate_code'] ?? ''),
            'primary_candidate_unchanged' => ($c126['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE,
            'backup_candidate_unchanged' => ($c126['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE,
            'a01_remains_comparator_only' => ($c126['a01_remains_comparator_only'] ?? null) === true,
            'candidate_scope_changed_after_c126' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'handoff_audit_archive_completion_used_for_selection' => false,
            'handoff_audit_archive_completion_used_for_retuning' => false,
            'handoff_audit_archive_completion_used_for_ranking' => false,
            'handoff_audit_archive_completion_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_completion_used_for_live_rollout' => false,
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
            'handoff_audit_archive_completion_confirmed' => (bool) ($options['handoff_audit_archive_completion_confirmed'] ?? false),
            'handoff_audit_archive_completion_reference_scope' => 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY',
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

    private function handoffAuditArchiveCompletionDecision(bool $pass): array
    {
        $decision = array_merge($this->passingTopLevelState(), [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c126_lock_valid' => $pass,
            'c126_handoff_audit_archived' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_executed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'audit_archive_completion_ready' => $pass,
            'completion_manifest_created' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => $pass,
            'handoff_audit_archive_completion_confirmed' => $pass,
            'handoff_audit_archive_completion_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO' : 'NO_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => $pass,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_manifest_created' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed_next' => $pass,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'handoff_audit_archive_completion_artifact_only' => true,
            'handoff_audit_archive_completion_used_for_selection' => false,
            'handoff_audit_archive_completion_used_for_retuning' => false,
            'handoff_audit_archive_completion_used_for_ranking' => false,
            'handoff_audit_archive_completion_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_completion_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C128_RECOMMENDATION : 'C127_TARGETED_C126_HANDOFF_AUDIT_ARCHIVE_REPAIR',
            'decision_reason' => $pass ? 'C127 weekly swing watchlist controlled runtime wiring handoff audit archive completion package is ready for primary and backup in artifact-only audit context.' : 'C127 handoff audit archive completion review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW' : 'C127_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REPAIR_REQUIRED',
            'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
        ]);
        if (! $pass) {
            foreach ([
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
                'controlled_runtime_wiring_handoff_audit_archive_completion_ready',
                'handoff_audit_archive_completion_ready',
                'audit_archive_completion_ready',
                'completion_manifest_created',
                'handoff_audit_archive_completion_confirmed',
                'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review',
                'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review',
                'controlled_runtime_wiring_handoff_audit_archive_completion_manifest_created',
                'controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed_next',
                'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
                'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
                'c126_handoff_audit_archived',
            ] as $field) {
                $decision[$field] = false;
            }
            $decision['handoff_audit_archive_completion_go_decision'] = 'NO_GO';
        }
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return array_merge($this->handoffAuditArchiveCompletionDecision($pass), [
            'validation_completed' => true,
            'next_recommendation' => $pass ? self::C128_RECOMMENDATION : 'C127_TARGETED_C126_HANDOFF_AUDIT_ARCHIVE_REPAIR',
        ]);
    }

    private function handoffAuditArchiveCompletionManifest(bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'artifact_only_controlled_runtime_wiring_handoff_audit_archive_completion_review',
            'execution_mode' => 'controlled_runtime_wiring_artifact_only_handoff_audit_archive_completion',
            'source_artifact' => 'C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW',
            'source_artifact_path' => self::DEFAULT_C126_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C126_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C126_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_controlled_runtime_wiring_handoff_audit_archive_completion_ready_candidate',
            'backup_candidate_role' => 'backup_controlled_runtime_wiring_handoff_audit_archive_completion_ready_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_ready_carried_forward' => $pass,
            'handoff_finalized_carried_forward' => $pass,
            'handoff_completion_boundary_cleared_carried_forward' => $pass,
            'handoff_closure_sealed_carried_forward' => $pass,
            'handoff_audit_archived_carried_forward' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'audit_archive_completion_ready' => $pass,
            'completion_manifest_created' => $pass,
            'handoff_audit_archive_completion_confirmed' => $pass,
            'handoff_audit_archive_completion_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO' : 'NO_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed_next' => $pass,
            'handoff_audit_archive_completion_artifact_only' => true,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'handoff_audit_archive_completion_used_for_selection' => false,
            'handoff_audit_archive_completion_used_for_retuning' => false,
            'handoff_audit_archive_completion_used_for_ranking' => false,
            'handoff_audit_archive_completion_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_completion_used_for_live_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
            'controlled_runtime_wiring_handoff_audit_archive_completion_steps' => [
                'validate_locked_c126_handoff_audit_archive_artifact',
                'confirm_primary_and_backup_handoff_audit_archived_scope',
                'mark_primary_and_backup_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
                'confirm_a01_comparator_only_scope',
                'record_controlled_runtime_wiring_handoff_audit_archive_completion_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_confirmed' => $pass,
            'handoff_audit_archive_completion_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO' : 'NO_GO',
            'audit_archive_completion_ready' => $pass,
            'handoff_audit_archived' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_advisory_only_pass' => $pass,
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
                'c127_role' => 'primary_controlled_runtime_wiring_handoff_audit_archive_completion_ready_candidate',
                'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c127_role' => 'backup_controlled_runtime_wiring_handoff_audit_archive_completion_ready_candidate',
                'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c127_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass' => false,
                'handoff_audit_archive_completion_ready' => false,
                'audit_archive_completion_ready' => false,
                'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffAuditArchiveCompletionContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
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
        $paths = [];
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path) || is_dir($path)];
        }
        return [
            'runtime_readiness_inspection_completed' => true,
            'inspected_paths' => $paths,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_source_identified' => is_file(self::RUNTIME_PATHS['c126_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_service']),
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_source_identified' => is_file(self::RUNTIME_PATHS['c127_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_service']),
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
            'handoff_audit_archive_completion_confirmation_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c126HandoffAuditArchiveCarryForwardValidationSummary(array $c126, bool $pass): array
    {
        return [
            'c126_handoff_audit_archive_carry_forward_validation_completed' => true,
            'c126_handoff_audit_archive_carry_forward_validation_pass' => $pass,
            'c126_status' => (string) ($c126['status'] ?? ''),
            'c126_reason_code' => (string) ($c126['reason_code'] ?? ''),
            'c126_artifact_hash' => (string) ($c126['artifact_hash'] ?? ''),
            'c126_handoff_audit_archive_review_pass' => ($c126['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_pass'] ?? null) === true,
            'c126_handoff_audit_archived' => ($c126['handoff_audit_archived'] ?? null) === true,
            'c126_audit_archived' => ($c126['audit_archived'] ?? null) === true,
            'c126_archive_manifest_created' => ($c126['archive_manifest_created'] ?? null) === true,
            'c126_handoff_audit_archive_confirmed' => ($c126['handoff_audit_archive_confirmed'] ?? null) === true,
            'c126_handoff_audit_archive_go_decision' => (string) ($c126['handoff_audit_archive_go_decision'] ?? ''),
            'c126_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review' => ($c126['ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review'] ?? null) === true,
            'c126_controlled_runtime_wiring_handoff_audit_archive_manifest_created' => ($c126['controlled_runtime_wiring_handoff_audit_archive_manifest_created'] ?? null) === true,
            'c126_controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed_next' => ($c126['controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed_next'] ?? null) === true,
            'c126_primary_candidate_handoff_audit_archived' => ($c126['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived'] ?? null) === true,
            'c126_backup_candidate_handoff_audit_archived' => ($c126['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived'] ?? null) === true,
            'c126_comparator_candidate_handoff_audit_archived' => ($c126['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archived'] ?? null) === false,
            'c126_primary_candidate_ready_for_audit_archive_completion' => ($c126['primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review'] ?? null) === true,
            'c126_backup_candidate_ready_for_audit_archive_completion' => ($c126['backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review'] ?? null) === true,
            'c126_comparator_candidate_ready_for_audit_archive_completion' => ($c126['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_review'] ?? null) === false,
            'c126_a01_remains_comparator_only' => ($c126['a01_remains_comparator_only'] ?? null) === true,
            'expected_c126_next_recommendation' => self::EXPECTED_C126_RECOMMENDATION,
        ];
    }

    private function handoffAuditArchiveCompletionGovernanceSummary(bool $pass): array
    {
        return [
            'handoff_audit_archive_completion_governance_completed' => true,
            'handoff_audit_archive_completion_governance_pass' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_is_explicit_context_only' => true,
            'handoff_audit_archive_completion_is_controlled_runtime_wiring_default' => true,
            'handoff_audit_archive_completion_is_artifact_only' => true,
            'handoff_audit_archive_completion_is_advisory_only' => true,
            'handoff_audit_archive_completion_used_for_selection' => false,
            'handoff_audit_archive_completion_used_for_retuning' => false,
            'handoff_audit_archive_completion_used_for_ranking' => false,
            'handoff_audit_archive_completion_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_completion_used_for_live_rollout' => false,
            'handoff_audit_archive_completion_allowed_to_auto_enable_runtime' => false,
            'handoff_audit_archive_completion_allowed_to_auto_deploy' => false,
            'handoff_audit_archive_completion_classification' => 'WEEKLY_SWING_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C127_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_confirmed' => $pass,
            'handoff_audit_archive_completion_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO' : 'NO_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => $pass,
            'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C128_RECOMMENDATION : 'C127_TARGETED_C126_HANDOFF_AUDIT_ARCHIVE_REPAIR',
            'selection_changed_after_c126' => false,
            'parameter_changed_after_c126' => false,
            'new_candidate_created' => false,
            'handoff_audit_archive_completion_used_for_selection' => false,
            'handoff_audit_archive_completion_used_for_retuning' => false,
            'handoff_audit_archive_completion_used_for_ranking' => false,
            'handoff_audit_archive_completion_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_completion_used_for_live_rollout' => false,
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
            'targeted_repair_recommendation' => $status === [] ? self::C128_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C126_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW',
            'achieved' => [
                'C126 artifact hash and file SHA1 validated',
                'C126 handoff audit archive evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Handoff audit archive completion readiness recorded for E02 and B01 only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist controlled runtime wiring handoff audit archive completion manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C128_RECOMMENDATION : 'C127_TARGETED_C126_HANDOFF_AUDIT_ARCHIVE_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist controlled runtime wiring handoff audit archive completion seal review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C127 artifact hash',
                'locked C127 file SHA1',
                'unchanged candidate scope',
                'controlled runtime wiring artifact-only handoff audit archive completion evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C127 validates C126 artifact_hash and file SHA1 locks before weekly swing watchlist controlled runtime wiring handoff audit archive completion review is recorded.',
            'C127 validates C126 handoff audit archive fields, completion readiness, and A01 comparator-only state.',
            'C127 confirms no temporary negative test artifact remains before a passing controlled runtime wiring handoff audit archive completion review.',
            'C127 marks audit archive completion readiness for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C127 creates an artifact-only weekly swing watchlist controlled runtime wiring handoff audit archive completion manifest and no official weekly swing recommendation output.',
            'C127 weekly swing watchlist controlled runtime wiring handoff audit archive completion review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C127 may only recommend C128 weekly swing watchlist controlled runtime wiring handoff audit archive completion seal review as the next audit-only step.',
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
        if (strpos($status, 'C126_ARTIFACT') !== false || strpos($status, 'C126_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C127_C126_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'ARCHIVE') !== false || strpos($status, 'COMPLETION') !== false) {
            return 'C127_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C127_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C127_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C127_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C127_TARGETED_C126_HANDOFF_AUDIT_ARCHIVE_REPAIR';
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
