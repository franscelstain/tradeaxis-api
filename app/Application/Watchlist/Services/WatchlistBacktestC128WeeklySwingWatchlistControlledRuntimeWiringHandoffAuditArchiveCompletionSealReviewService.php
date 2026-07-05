<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC128WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionSealReviewService
{
    public const RUN_CODE = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    public const PHASE_LABEL = 'PR-16 / C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    public const ARTIFACT_TYPE = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';

    public const DEFAULT_C127_ARTIFACT = 'storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json';
    public const DEFAULT_EXPECTED_C127_HASH = 'fc9d9204da55658d1416e24bd9be20381a1bbc54';
    public const DEFAULT_EXPECTED_C127_FILE_SHA1 = '6AE20CACBA644E8863FEA16FD4003BE1C775DA54';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C127_STATUS = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP';
    private const EXPECTED_C127_REASON = 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP';
    private const EXPECTED_C127_PHASE_LABEL = 'PR-15 / C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    private const EXPECTED_C127_RECOMMENDATION = self::RUN_CODE;
    private const C129_RECOMMENDATION = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';
    private const PASS_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_SEALED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const AUDIT_ARCHIVE_COMPLETION_SEAL_NOT_CONFIRMED_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_AUDIT_ARCHIVE_COMPLETION_SEAL_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const C127_AUDIT_ARCHIVE_COMPLETION_INCOMPLETE_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C127_AUDIT_ARCHIVE_COMPLETION_INCOMPLETE';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C127_CONVERT_FROM_JSON_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C127_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';

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
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
        'handoff_audit_archive_completion_seal_context_persisted_to_live_runtime',
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

    private const REQUIRED_C127_FIELDS = [
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
        'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
        'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
        'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
        'primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
        'backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
        'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
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
        'a01_remains_comparator_only' => true,
    ];

    private const DOC_PATHS = [
        'c128_validation_doc' => 'docs/watchlist/audit/WS_C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW.md',
        'c128_operator_commands_doc' => 'docs/watchlist/audit/WS_C128_OPERATOR_VALIDATION_COMMANDS.md',
        'c127_validation_doc' => 'docs/watchlist/audit/WS_C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW.md',
        'c127_operator_commands_doc' => 'docs/watchlist/audit/WS_C127_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c127_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService.php',
        'c128_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC128WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionSealReviewService.php',
        'c127_command' => 'app/Console/Commands/Watchlist/RunBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewCommand.php',
        'c128_command' => 'app/Console/Commands/Watchlist/RunBacktestC128WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionSealReviewCommand.php',
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
        string $c127Artifact = self::DEFAULT_C127_ARTIFACT,
        string $expectedC127Hash = self::DEFAULT_EXPECTED_C127_HASH,
        string $expectedC127FileSha1 = self::DEFAULT_EXPECTED_C127_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c127Artifact, $expectedC127Hash, $expectedC127FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C128_BLOCKED_C127_ARTIFACT_LOCK_MISMATCH', 'C127 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c127_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C127_CONVERT_FROM_JSON_STATUS, 'C127 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C128_BLOCKED_C127_ARTIFACT_LOCK_MISMATCH', 'C127 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C128_BLOCKED_C127_FILE_SHA1_LOCK_MISMATCH', 'C127 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c127 = $load['payload'];
        if (($c127['status'] ?? null) !== self::EXPECTED_C127_STATUS) {
            return $this->blocked($artifact, 'C128_BLOCKED_C127_STATUS_MISMATCH', 'C127 status is not passed audit archive completion ready.', $outputPath, $overwrite);
        }
        if (($c127['reason_code'] ?? null) !== self::EXPECTED_C127_REASON) {
            return $this->blocked($artifact, 'C128_BLOCKED_C127_REASON_CODE_MISMATCH', 'C127 reason_code is not passed audit archive completion ready.', $outputPath, $overwrite);
        }
        if (($c127['phase_label'] ?? null) !== self::EXPECTED_C127_PHASE_LABEL) {
            return $this->blocked($artifact, 'C128_BLOCKED_C127_PHASE_LABEL_MISMATCH', 'C127 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c127NextRecommendationMatches($c127)) {
            return $this->blocked($artifact, 'C128_BLOCKED_C127_NEXT_RECOMMENDATION_MISMATCH', 'C127 next recommendation is not C128.', $outputPath, $overwrite);
        }
        if (! $this->c127HandoffAuditArchiveCompletionReady($c127)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C127_AUDIT_ARCHIVE_COMPLETION_INCOMPLETE_STATUS, 'C127 handoff audit archive completion evidence is incomplete.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c127);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c127_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C127 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c127)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C127 candidate scope does not match locked controlled runtime wiring handoff audit archive completion decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C128 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::AUDIT_ARCHIVE_COMPLETION_SEAL_NOT_CONFIRMED_STATUS, 'C128 requires --handoff-audit-archive-completion-seal-confirmed.', $outputPath, $overwrite);
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
            return $this->rejected($artifact, $failures[0], 'C128 weekly swing watchlist controlled runtime wiring handoff audit archive completion seal review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C128 confirms the weekly swing watchlist controlled runtime wiring handoff audit archive completion package is sealed for primary and backup as artifact-only evidence. This does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_CONTROLLED_RUNTIME_WIRING_AUDIT_ONLY';
        $artifact['next_step_recommendation'] = self::C129_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-16',
            'internal_checkpoint' => 'C128',
            'status' => 'C128_NOT_RUN',
            'reason_code' => 'C128_NOT_RUN',
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
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_executed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_pass' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_sealed' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_sealed' => false,
            'handoff_audit_archive_completion_sealed' => false,
            'audit_archive_completion_sealed' => false,
            'completion_seal_manifest_created' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_confirmed' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_confirmed' => false,
            'handoff_audit_archive_completion_seal_confirmed' => false,
            'handoff_audit_archive_completion_seal_go_decision' => 'NO_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review' => false,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest_created' => false,
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed_next' => false,
            'primary_candidate_handoff_audit_archive_completion_sealed' => false,
            'backup_candidate_handoff_audit_archive_completion_sealed' => false,
            'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived' => false,
            'handoff_audit_archived' => false,
            'audit_archived' => false,
            'archive_manifest_created' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_sealed' => false,
            'handoff_closure_sealed' => false,
            'closure_sealed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared' => false,
            'handoff_completion_boundary_cleared' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => false,
            'handoff_finalized' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => false,
            'handoff_ready' => false,
            'c127_handoff_audit_archived' => false,
            'c127_handoff_audit_archive_completion_ready' => false,
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
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_pass' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_sealed' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_sealed' => true,
            'handoff_audit_archive_completion_sealed' => true,
            'audit_archive_completion_sealed' => true,
            'completion_seal_manifest_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_confirmed' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_confirmed' => true,
            'handoff_audit_archive_completion_seal_confirmed' => true,
            'handoff_audit_archive_completion_seal_go_decision' => 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review' => true,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest_created' => true,
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed_next' => true,
            'primary_candidate_handoff_audit_archive_completion_sealed' => true,
            'backup_candidate_handoff_audit_archive_completion_sealed' => true,
            'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived' => true,
            'handoff_audit_archived' => true,
            'audit_archived' => true,
            'archive_manifest_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_sealed' => true,
            'handoff_closure_sealed' => true,
            'closure_sealed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared' => true,
            'handoff_completion_boundary_cleared' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => true,
            'handoff_finalized' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => true,
            'handoff_ready' => true,
            'c127_handoff_audit_archived' => true,
            'c127_handoff_audit_archive_completion_ready' => true,
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
        $expectedFileSha1 = strtoupper($expectedFileSha1);
        return [
            'source_lock' => 'C127',
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $exists && $actualHash === $expectedHash,
            'expected_file_sha1' => $expectedFileSha1,
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $exists && $actualFileSha1 === $expectedFileSha1,
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
        return [
            'c127' => [
                'artifact_path' => $load['path'],
                'artifact_exists' => $load['exists'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'convert_from_json_pass' => $load['convert_from_json_pass'],
                'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
                'expected_status' => self::EXPECTED_C127_STATUS,
                'expected_reason_code' => self::EXPECTED_C127_REASON,
                'expected_phase_label' => self::EXPECTED_C127_PHASE_LABEL,
                'expected_next_recommendation' => self::EXPECTED_C127_RECOMMENDATION,
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C127',
            'c127_artifact_path' => $load['path'],
            'c127_artifact_exists' => $load['exists'],
            'expected_c127_hash' => $load['expected_hash'],
            'actual_c127_hash' => $load['actual_hash'],
            'c127_hash_match' => $load['hash_match'],
            'expected_c127_file_sha1' => $load['expected_file_sha1'],
            'actual_c127_file_sha1' => $load['actual_file_sha1'],
            'c127_file_sha1_match' => $load['file_sha1_match'],
            'c127_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function c127NextRecommendationMatches(array $c127): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c127_handoff_audit_archive_completion_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c127, $path) === self::EXPECTED_C127_RECOMMENDATION) {
                return true;
            }
        }
        return false;
    }

    private function c127HandoffAuditArchiveCompletionReady(array $c127): bool
    {
        foreach (self::REQUIRED_C127_FIELDS as $field => $expected) {
            if (($c127[$field] ?? null) !== $expected) {
                return false;
            }
        }
        if (($c127['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_count'] ?? null) !== 2) {
            return false;
        }

        $manifest = is_array($c127['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_manifest'] ?? null)
            ? $c127['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_manifest']
            : [];
        foreach ([
            'manifest_created' => true,
            'handoff_audit_archive_completion_artifact_only' => true,
            'handoff_audit_archive_completion_ready' => true,
            'audit_archive_completion_ready' => true,
            'completion_manifest_created' => true,
            'handoff_audit_archive_completion_confirmed' => true,
            'handoff_audit_archive_completion_go_decision' => 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review' => true,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed_next' => true,
        ] as $field => $expected) {
            if (($manifest[$field] ?? null) !== $expected) {
                return false;
            }
        }
        foreach ([
            'handoff_audit_archive_completion_used_for_selection',
            'handoff_audit_archive_completion_used_for_retuning',
            'handoff_audit_archive_completion_used_for_ranking',
            'handoff_audit_archive_completion_used_for_plan_confirm_mutation',
            'handoff_audit_archive_completion_used_for_live_rollout',
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

    private function candidateScopeMatches(array $c127): bool
    {
        return ($c127['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE) === self::PRIMARY_CANDIDATE
            && ($c127['backup_candidate_code'] ?? self::BACKUP_CANDIDATE) === self::BACKUP_CANDIDATE
            && ($c127['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE) === self::COMPARATOR_CANDIDATE
            && (bool) ($c127['a01_remains_comparator_only'] ?? false) === true
            && (bool) ($c127['a01_promoted'] ?? false) === false
            && (bool) ($c127['candidate_promotion_executed'] ?? false) === false
            && (bool) ($c127['candidate_rerank_executed'] ?? false) === false
            && (bool) ($c127['strategy_retune_executed'] ?? false) === false
            && (bool) ($c127['scoring_mutation_executed'] ?? false) === false
            && (bool) ($c127['catalog_selection_changed'] ?? false) === false
            && (bool) ($c127['runtime_selection_changed'] ?? false) === false;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false)) {
            $failures[] = self::AUDIT_ARCHIVE_COMPLETION_SEAL_NOT_CONFIRMED_STATUS;
        }
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
            'mutate_plan_confirm',
            'change_config_defaults',
            'change_strategy_parameters',
            'generate_official_weekly_swing_stock_recommendation',
            'generate_live_weekly_swing_watchlist_output',
            'publish_weekly_swing_output',
            'modify_c60_c127_artifacts',
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
        $c127 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c127_lock_validation_summary'] = $this->c127LockValidationSummary($load, $c127);
        $artifact['c122_c127_handoff_lineage_completion_summary'] = $this->lineageValidationSummary($c127);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c127, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c128_readiness_decision'] = $this->handoffAuditArchiveCompletionDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_decision'] = $artifact['c128_readiness_decision'];
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest'] = $this->handoffAuditArchiveCompletionSealManifest($pass);
        $artifact['c128_candidate_audit_archive_completion_seal_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['handoff_audit_archive_completion_seal_context_summary'] = $this->handoffAuditArchiveCompletionSealContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c127_handoff_audit_archive_completion_carry_forward_validation_summary'] = $this->c127HandoffAuditArchiveCompletionCarryForwardValidationSummary($c127, $pass);
        $artifact['handoff_audit_archive_completion_seal_governance_summary'] = $this->handoffAuditArchiveCompletionSealGovernanceSummary($pass);
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

    private function c127LockValidationSummary(array $load, array $c127): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C127',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'expected_status' => self::EXPECTED_C127_STATUS,
            'actual_status' => $c127['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C127_REASON,
            'actual_reason_code' => $c127['reason_code'] ?? null,
            'expected_phase_label' => self::EXPECTED_C127_PHASE_LABEL,
            'actual_phase_label' => $c127['phase_label'] ?? null,
            'phase_label_match' => ($c127['phase_label'] ?? null) === self::EXPECTED_C127_PHASE_LABEL,
            'expected_next_recommendation' => self::EXPECTED_C127_RECOMMENDATION,
            'next_recommendation_match' => $this->c127NextRecommendationMatches($c127),
            'c127_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function lineageValidationSummary(array $c127): array
    {
        return [
            'validation_completed' => true,
            'c122_handoff_ready_carried_forward' => (bool) ($c127['handoff_ready'] ?? false),
            'c123_handoff_finalized_carried_forward' => (bool) ($c127['handoff_finalized'] ?? false),
            'c124_handoff_completion_boundary_cleared_carried_forward' => (bool) ($c127['handoff_completion_boundary_cleared'] ?? false),
            'c125_handoff_closure_sealed_carried_forward' => (bool) ($c127['handoff_closure_sealed'] ?? false),
            'c126_handoff_audit_archived_carried_forward' => (bool) ($c127['handoff_audit_archived'] ?? false),
            'c127_handoff_audit_archive_completion_ready_carried_forward' => (bool) ($c127['handoff_audit_archive_completion_ready'] ?? false),
            'lineage_carried_forward_complete' => $this->c127HandoffAuditArchiveCompletionReady($c127),
        ];
    }

    private function candidateScopeFreezeSummary(array $c127, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c127),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_handoff_audit_archive_completion_seal_candidate',
            'backup_candidate_role' => 'backup_handoff_audit_archive_completion_seal_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_handoff_audit_archive_completion_ready' => $pass,
            'backup_candidate_handoff_audit_archive_completion_ready' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_ready' => false,
            'primary_candidate_handoff_audit_archive_completion_sealed' => $pass,
            'backup_candidate_handoff_audit_archive_completion_sealed' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
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
            'handoff_audit_archive_completion_seal_confirmation_required' => true,
            'handoff_audit_archive_completion_seal_confirmed' => (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false),
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
            'c127_lock_valid' => $pass,
            'c127_handoff_audit_archive_completion_ready' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'audit_archive_completion_ready' => $pass,
            'completion_manifest_created' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => $pass,
            'handoff_audit_archive_completion_confirmed' => $pass,
            'handoff_audit_archive_completion_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO' : 'NO_GO',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_pass' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_sealed' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_sealed' => $pass,
            'handoff_audit_archive_completion_sealed' => $pass,
            'audit_archive_completion_sealed' => $pass,
            'completion_seal_manifest_created' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_confirmed' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_confirmed' => $pass,
            'handoff_audit_archive_completion_seal_confirmed' => $pass,
            'handoff_audit_archive_completion_seal_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_GO' : 'NO_GO',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review' => $pass,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest_created' => $pass,
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed_next' => $pass,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => $pass,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
            'primary_candidate_handoff_audit_archive_completion_sealed' => $pass,
            'backup_candidate_handoff_audit_archive_completion_sealed' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
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
            'next_recommendation' => self::C129_RECOMMENDATION,
            'decision_reason' => 'C128 weekly swing watchlist controlled runtime wiring handoff audit archive completion seal review completed for primary and backup in artifact-only audit context.',
            'diagnostic_conclusion' => 'C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_CONTROLLED_RUNTIME_WIRING_AUDIT_ONLY',
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C129_RECOMMENDATION : 'C128_TARGETED_C127_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REPAIR',
            'next_scope' => $pass ? 'weekly swing watchlist controlled runtime wiring handoff audit archive final closure review only' : 'targeted C127 lock or completion readiness repair only',
        ];
    }

    private function handoffAuditArchiveCompletionSealManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review',
            'source_artifact' => 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW',
            'source_artifact_path' => self::DEFAULT_C127_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C127_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C127_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_handoff_audit_archive_completion_seal_candidate',
            'backup_candidate_role' => 'backup_handoff_audit_archive_completion_seal_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_ready_carried_forward' => $pass,
            'handoff_finalized_carried_forward' => $pass,
            'handoff_completion_boundary_cleared_carried_forward' => $pass,
            'handoff_closure_sealed_carried_forward' => $pass,
            'handoff_audit_archived_carried_forward' => $pass,
            'handoff_audit_archive_completion_ready_carried_forward' => $pass,
            'handoff_audit_archive_completion_sealed' => $pass,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'completion_seal_used_for_selection' => false,
            'completion_seal_used_for_retuning' => false,
            'completion_seal_used_for_ranking' => false,
            'completion_seal_used_for_plan_confirm_mutation' => false,
            'completion_seal_used_for_live_rollout' => false,
            'completion_seal_artifact_only' => true,
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'handoff_audit_archive_completion_seal_review_pass' => $pass,
            'handoff_audit_archive_completion_sealed' => $pass,
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
                'c128_role' => 'primary_handoff_audit_archive_completion_seal_candidate',
                'primary_candidate_handoff_audit_archive_completion_sealed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c128_role' => 'backup_handoff_audit_archive_completion_seal_candidate',
                'backup_candidate_handoff_audit_archive_completion_sealed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c128_role' => 'comparator_only_candidate',
                'handoff_audit_archive_completion_seal_review_pass' => false,
                'handoff_audit_archive_completion_sealed' => false,
                'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffAuditArchiveCompletionSealContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_completion_seal_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_source_identified' => is_file(self::RUNTIME_PATHS['c127_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_service']),
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_source_identified' => is_file(self::RUNTIME_PATHS['c128_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_service']),
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

    private function c127HandoffAuditArchiveCompletionCarryForwardValidationSummary(array $c127, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c127_handoff_audit_archive_completion_review_pass' => (bool) ($c127['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass'] ?? false),
            'c127_handoff_audit_archive_completion_ready' => (bool) ($c127['handoff_audit_archive_completion_ready'] ?? false),
            'c127_audit_archive_completion_ready' => (bool) ($c127['audit_archive_completion_ready'] ?? false),
            'c127_completion_manifest_created' => (bool) ($c127['completion_manifest_created'] ?? false),
            'c127_handoff_audit_archived' => (bool) ($c127['handoff_audit_archived'] ?? false),
            'c127_audit_archived' => (bool) ($c127['audit_archived'] ?? false),
            'c127_archive_manifest_created' => (bool) ($c127['archive_manifest_created'] ?? false),
            'c127_primary_candidate_completion_ready' => (bool) ($c127['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready'] ?? false),
            'c127_backup_candidate_completion_ready' => (bool) ($c127['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready'] ?? false),
            'c127_comparator_candidate_completion_ready' => (bool) ($c127['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready'] ?? true),
            'handoff_audit_archive_completion_sealed' => $pass,
        ];
    }

    private function handoffAuditArchiveCompletionSealGovernanceSummary(bool $pass): array
    {
        return [
            'governance_scope' => 'C128 artifact-only controlled runtime wiring handoff audit archive completion seal review',
            'completion_seal_review_pass' => $pass,
            'audit_archive_completion_sealed' => $pass,
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
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
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
            'c128_docs_added' => is_file(self::DOC_PATHS['c128_validation_doc']) && is_file(self::DOC_PATHS['c128_operator_commands_doc']),
            'implementation_status_updated' => is_file(self::DOC_PATHS['implementation_status_doc']),
            'contract_tracker_updated' => is_file(self::DOC_PATHS['contract_tracker_doc']),
            'audit_update_governance_updated' => is_file(self::DOC_PATHS['audit_update_governance_doc']),
            'documentation_hygiene_guard_applied' => true,
            'scoped_c127_source_lock_preserved' => true,
            'scoped_expected_c127_file_sha1_key_preserved' => true,
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
            'progress_marker' => 'C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW',
            'c127_audit_archive_completion_review_carried_forward' => true,
            'c128_handoff_audit_archive_completion_seal_review_executed' => true,
            'c128_handoff_audit_archive_completion_sealed' => $pass,
            'still_controlled_runtime_wiring' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C129_RECOMMENDATION : 'C128_TARGETED_C127_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist controlled runtime wiring handoff audit archive final closure review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C127 artifact hash',
                'locked C127 file SHA1',
                'unchanged candidate scope',
                'controlled runtime wiring artifact-only handoff audit archive completion seal evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C128 validates C127 artifact_hash and file SHA1 locks before weekly swing watchlist controlled runtime wiring handoff audit archive completion seal review is recorded.',
            'C128 validates C127 handoff audit archive completion fields and A01 comparator-only state.',
            'C128 confirms no temporary negative test artifact remains before a passing controlled runtime wiring handoff audit archive completion seal review.',
            'C128 seals audit archive completion for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C128 creates an artifact-only weekly swing watchlist controlled runtime wiring handoff audit archive completion seal manifest and no official weekly swing recommendation output.',
            'C128 weekly swing watchlist controlled runtime wiring handoff audit archive completion seal review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C128 may only recommend C129 weekly swing watchlist controlled runtime wiring handoff audit archive final closure review as the next audit-only step.',
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
        if (strpos($status, 'C127_ARTIFACT') !== false || strpos($status, 'C127_FILE') !== false || strpos($status, 'LOCK') !== false) {
            return 'C128_C127_LOCK_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'ARCHIVE') !== false || strpos($status, 'COMPLETION') !== false) {
            return 'C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C128_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C128_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C128_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C128_TARGETED_C127_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REPAIR';
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
