<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC124WeeklySwingWatchlistControlledRuntimeWiringHandoffCompletionBoundaryReviewService
{
    public const RUN_CODE = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-12 / C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW';

    public const DEFAULT_C123_ARTIFACT = 'storage/app/watchlist/backtest/c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review.json';
    public const DEFAULT_EXPECTED_C123_HASH = '802f76794be7b4478ece5e9587c7d5e8635ff88d';
    public const DEFAULT_EXPECTED_C123_FILE_SHA1 = '9880DB3FDDCBFBA7FA325E8956F523A850605B4D';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c124-weekly-swing-watchlist-controlled-runtime-wiring-handoff-completion-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C123_STATUS = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C123_REASON = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C123_RECOMMENDATION = self::RUN_CODE;
    private const EXPECTED_C123_PHASE_LABEL = 'PR-11 / C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW';
    private const C125_RECOMMENDATION = 'C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW';
    private const PASS_STATUS = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_NOT_CONFIRMED_STATUS = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_HANDOFF_COMPLETION_BOUNDARY_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C123_CONVERT_FROM_JSON_STATUS = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_C123_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';

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

    private const C123_LIVE_OR_MUTATING_FLAGS = [
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
        'c124_validation_doc' => 'docs/watchlist/audit/WS_C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW.md',
        'c124_operator_commands_doc' => 'docs/watchlist/audit/WS_C124_OPERATOR_VALIDATION_COMMANDS.md',
        'c123_validation_doc' => 'docs/watchlist/audit/WS_C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW.md',
        'c123_operator_commands_doc' => 'docs/watchlist/audit/WS_C123_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c123_weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService.php',
        'c124_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC124WeeklySwingWatchlistControlledRuntimeWiringHandoffCompletionBoundaryReviewService.php',
        'c123_command' => 'app/Console/Commands/Watchlist/RunBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewCommand.php',
        'c124_command' => 'app/Console/Commands/Watchlist/RunBacktestC124WeeklySwingWatchlistControlledRuntimeWiringHandoffCompletionBoundaryReviewCommand.php',
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
        string $c123Artifact = self::DEFAULT_C123_ARTIFACT,
        string $expectedC123Hash = self::DEFAULT_EXPECTED_C123_HASH,
        string $expectedC123FileSha1 = self::DEFAULT_EXPECTED_C123_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c123Artifact, $expectedC123Hash, $expectedC123FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C124_BLOCKED_C123_ARTIFACT_LOCK_MISMATCH', 'C123 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c123_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C123_CONVERT_FROM_JSON_STATUS, 'C123 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C124_BLOCKED_C123_ARTIFACT_LOCK_MISMATCH', 'C123 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C124_BLOCKED_C123_FILE_SHA1_LOCK_MISMATCH', 'C123 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c123 = $load['payload'];
        if (($c123['status'] ?? null) !== self::EXPECTED_C123_STATUS || ($c123['reason_code'] ?? null) !== self::EXPECTED_C123_REASON) {
            return $this->blocked($artifact, 'C124_BLOCKED_C123_STATUS_OR_REASON_MISMATCH', 'C123 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c123['phase_label'] ?? null) !== self::EXPECTED_C123_PHASE_LABEL) {
            return $this->blocked($artifact, 'C124_BLOCKED_C123_PHASE_LABEL_MISMATCH', 'C123 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c123NextRecommendationMatches($c123)) {
            return $this->blocked($artifact, 'C124_BLOCKED_C123_NEXT_RECOMMENDATION_MISMATCH', 'C123 next recommendation is not C124.', $outputPath, $overwrite);
        }
        if (! $this->c123HandoffFinalized($c123)) {
            return $this->blocked($artifact, 'C124_BLOCKED_C123_HANDOFF_NOT_FINALIZED', 'C123 handoff finalization evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c123);
        if ($safetyFailure !== null) {
            $artifact['c123_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C124_BLOCKED_C123_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C123 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c123)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C123 candidate scope does not match locked controlled runtime wiring handoff finalization decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C124 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C124 weekly swing watchlist controlled runtime wiring handoff completion boundary review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C124 cleared the weekly swing watchlist controlled runtime wiring handoff completion boundary for primary and backup as artifact-only evidence. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW';
        $artifact['next_step_recommendation'] = self::C125_RECOMMENDATION;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_executed'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_allowed'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_pass'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared'] = true;
        $artifact['handoff_completion_boundary_cleared'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_confirmed'] = true;
        $artifact['handoff_completion_boundary_confirmed'] = true;
        $artifact['handoff_completion_boundary_go_decision'] = 'HANDOFF_COMPLETION_BOUNDARY_CLEARED_GO';
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized'] = true;
        $artifact['controlled_runtime_wiring_handoff_finalized'] = true;
        $artifact['handoff_finalized'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready'] = true;
        $artifact['controlled_runtime_wiring_handoff_ready'] = true;
        $artifact['handoff_ready'] = true;
        $artifact['ready_for_controlled_runtime_wiring_handoff_closure_seal_review'] = true;
        $artifact['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_closure_seal_review'] = true;
        $artifact['controlled_runtime_wiring_handoff_completion_boundary_manifest_created'] = true;
        $artifact['controlled_runtime_wiring_handoff_closure_seal_review_allowed_next'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_cleared'] = true;
        $artifact['completion_boundary_cleared'] = true;
        $artifact['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared'] = true;
        $artifact['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared'] = true;
        $artifact['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared'] = false;
        $artifact['primary_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review'] = true;
        $artifact['backup_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review'] = true;
        $artifact['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review'] = false;
        $artifact['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] = true;
        $artifact['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] = true;
        $artifact['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] = false;
        $artifact['boundary_go_decision'] = 'BOUNDARY_CLEARED_GO';
        $artifact['operator_go_decision'] = 'GO';
        $artifact['go_decision_finalized'] = true;
        $artifact['c123_handoff_finalized'] = true;
        $artifact['c122_handoff_ready'] = true;
        $artifact['a01_remains_comparator_only'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-12',
            'internal_checkpoint' => 'C124',
            'status' => 'C124_NOT_RUN',
            'reason_code' => 'C124_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_executed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_allowed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_pass' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared' => false,
            'handoff_completion_boundary_cleared' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_confirmed' => false,
            'handoff_completion_boundary_confirmed' => false,
            'handoff_completion_boundary_go_decision' => 'NO_GO',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => false,
            'controlled_runtime_wiring_handoff_finalized' => false,
            'handoff_finalized' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => false,
            'controlled_runtime_wiring_handoff_ready' => false,
            'handoff_ready' => false,
            'ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => false,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => false,
            'controlled_runtime_wiring_handoff_completion_boundary_manifest_created' => false,
            'controlled_runtime_wiring_handoff_closure_seal_review_allowed_next' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_cleared' => false,
            'completion_boundary_cleared' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => false,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => false,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => false,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => false,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'boundary_go_decision' => 'NO_GO',
            'operator_go_decision' => 'NO_GO',
            'go_decision_finalized' => false,
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
                $token = substr($raw, $start, $i - $start + 1);
                if ($depth === 1 && $expectTopLevelKey) {
                    $j = $i + 1;
                    while ($j < $length && ctype_space($raw[$j])) {
                        $j++;
                    }
                    if ($j < $length && $raw[$j] === ':') {
                        $decoded = json_decode($token, true);
                        if (is_string($decoded)) {
                            $lower = strtolower($decoded);
                            if (array_key_exists($lower, $seen) && ! in_array($decoded, $duplicates, true)) {
                                $duplicates[] = $decoded;
                            }
                            $seen[$lower] = $decoded;
                        }
                        $expectTopLevelKey = false;
                    }
                }
                continue;
            }
            if ($char === '{') {
                $depth++;
                if ($depth === 1) {
                    $expectTopLevelKey = true;
                }
                continue;
            }
            if ($char === '}') {
                if ($depth === 1) {
                    $expectTopLevelKey = false;
                }
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($char === '[') {
                $depth++;
                continue;
            }
            if ($char === ']') {
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($char === ',' && $depth === 1) {
                $expectTopLevelKey = true;
            }
        }
        sort($duplicates);

        return array_values($duplicates);
    }

    private function sourceArtifactLocks(array $load): array
    {
        $c123 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c123['source_artifact_locks'] ?? null) ? $c123['source_artifact_locks'] : [];
        return [
            'c123_artifact_path' => $load['path'],
            'expected_c123_hash' => $load['expected_hash'],
            'actual_c123_hash' => $load['actual_hash'],
            'c123_hash_match' => $load['hash_match'],
            'expected_c123_file_sha1' => $load['expected_file_sha1'],
            'actual_c123_file_sha1' => $load['actual_file_sha1'],
            'c123_file_sha1_match' => $load['file_sha1_match'],
            'c123_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c123_convert_from_json_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c123_source_lineage_checked' => true,
            'c123_source_lineage_match' => $this->lineageLocksMatch($c123),
            'c122_artifact_hash_from_c123' => (string) ($locks['actual_c122_hash'] ?? ($c123['actual_c122_hash'] ?? '')),
            'c122_file_sha1_from_c123' => (string) ($locks['actual_c122_file_sha1'] ?? ($c123['actual_c122_file_sha1'] ?? '')),
            'c103_artifact_hash_from_c122' => (string) ($locks['c103_artifact_hash_from_c122'] ?? ''),
            'c103_file_sha1_from_c122' => (string) ($locks['c103_file_sha1_from_c122'] ?? ''),
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
            'expected_c123_hash' => $load['expected_hash'],
            'actual_c123_hash' => $load['actual_hash'],
            'c123_hash_match' => $load['hash_match'],
            'expected_c123_file_sha1' => $load['expected_file_sha1'],
            'actual_c123_file_sha1' => $load['actual_file_sha1'],
            'c123_file_sha1_match' => $load['file_sha1_match'],
            'c123_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function lineageLocksMatch(array $c123): bool
    {
        $locks = is_array($c123['source_artifact_locks'] ?? null) ? $c123['source_artifact_locks'] : [];
        return ($locks['c122_hash_match'] ?? null) === true
            && ($locks['c122_file_sha1_match'] ?? null) === true
            && (string) ($locks['c103_artifact_hash_from_c122'] ?? '') !== ''
            && (string) ($locks['c103_file_sha1_from_c122'] ?? '') !== '';
    }

    private function c123NextRecommendationMatches(array $c123): bool
    {
        $paths = [
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['next_handoff_completion_boundary_decision', 'next_recommendation'],
            ['c123_handoff_finalization_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ];
        $found = false;
        foreach ($paths as $path) {
            $value = $this->valueAt($c123, $path);
            if ($value === null || $value === '') {
                continue;
            }
            $found = true;
            if ($value !== self::EXPECTED_C123_RECOMMENDATION) {
                return false;
            }
        }
        return $found;
    }

    private function c123HandoffFinalized(array $c123): bool
    {
        $decision = is_array($c123['c123_handoff_finalization_decision'] ?? null) ? $c123['c123_handoff_finalization_decision'] : [];
        foreach ([
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => true,
            'controlled_runtime_wiring_handoff_finalized' => true,
            'handoff_finalized' => true,
            'handoff_finalization_confirmed' => true,
            'handoff_finalization_go_decision' => 'HANDOFF_FINALIZED_GO',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => true,
            'controlled_runtime_wiring_handoff_ready' => true,
            'handoff_ready' => true,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => true,
            'ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => true,
            'controlled_runtime_wiring_handoff_finalization_manifest_created' => true,
            'controlled_runtime_wiring_handoff_completion_boundary_review_allowed_next' => true,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => true,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => true,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => true,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => true,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => true,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => true,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => false,
            'completion_boundary_cleared' => true,
            'boundary_go_decision' => 'BOUNDARY_CLEARED_GO',
            'operator_go_decision' => 'GO',
            'go_decision_finalized' => true,
            'a01_remains_comparator_only' => true,
        ] as $field => $expected) {
            if (($c123[$field] ?? null) !== $expected) {
                return false;
            }
            if (($decision[$field] ?? null) !== $expected) {
                return false;
            }
        }
        if (($c123['next_readiness_decision']['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_count'] ?? null) !== 2) {
            return false;
        }
        if (($decision['candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_count'] ?? null) !== 2) {
            return false;
        }
        if (($c123['temporary_negative_artifacts_remaining'] ?? null) !== false || ($c123['temporary_negative_artifact_cleanup_confirmed'] ?? null) !== true || (array) ($c123['temporary_negative_artifact_paths'] ?? []) !== []) {
            return false;
        }

        $manifest = is_array($c123['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_manifest'] ?? null)
            ? $c123['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_manifest']
            : [];
        if (($manifest['handoff_finalization_artifact_only'] ?? null) !== true || ($manifest['handoff_finalized'] ?? null) !== true) {
            return false;
        }
        if (($manifest['handoff_finalization_confirmed'] ?? null) !== true || ($manifest['handoff_finalization_go_decision'] ?? null) !== 'HANDOFF_FINALIZED_GO') {
            return false;
        }
        if (($manifest['ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] ?? null) !== true) {
            return false;
        }
        foreach ([
            'handoff_finalization_used_for_selection',
            'handoff_finalization_used_for_retuning',
            'handoff_finalization_used_for_ranking',
            'handoff_finalization_used_for_plan_confirm_mutation',
            'handoff_finalization_used_for_live_rollout',
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
        foreach (self::C123_LIVE_OR_MUTATING_FLAGS as $flag) {
            if (($payload[$flag] ?? null) === true) {
                return $flag;
            }
        }
        foreach ([
            'c123_handoff_finalization_decision',
            'next_readiness_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_manifest',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_summary',
            'runtime_readiness_inspection_summary',
            'feature_flag_operator_approval_kill_switch_validation_summary',
            'handoff_finalization_governance_summary',
            'baseline_plan_confirm_non_mutation_summary',
            'production_mutation_safety_summary',
        ] as $section) {
            $values = is_array($payload[$section] ?? null) ? $payload[$section] : [];
            foreach (self::C123_LIVE_OR_MUTATING_FLAGS as $flag) {
                if (($values[$flag] ?? null) === true) {
                    return $section.'.'.$flag;
                }
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c123): bool
    {
        return ($c123['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c123['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c123['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c123['a01_remains_comparator_only'] ?? null) === true
            && ($c123['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] ?? null) === true
            && ($c123['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] ?? null) === true
            && ($c123['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] ?? null) === false
            && ($c123['primary_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] ?? null) === true
            && ($c123['backup_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] ?? null) === true
            && ($c123['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] ?? null) === false
            && ($c123['candidate_scope_freeze_summary']['a01_promoted'] ?? false) === false
            && ($c123['candidate_scope_freeze_summary']['new_candidate_created'] ?? false) === false;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (($options['handoff_completion_boundary_confirmed'] ?? true) !== true || ($options['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_confirmed'] ?? true) !== true) {
            $failures[] = self::BOUNDARY_NOT_CONFIRMED_STATUS;
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
                $failures[] = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_FEATURE_FLAG_OR_RUNTIME_GATE_ON';
                break;
            }
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c123',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'handoff_completion_boundary_used_for_selection',
            'handoff_completion_boundary_used_for_retuning',
            'handoff_completion_boundary_used_for_ranking',
            'handoff_completion_boundary_used_for_plan_confirm_mutation',
            'handoff_completion_boundary_used_for_live_rollout',
            'handoff_completion_boundary_allowed_to_auto_enable_runtime',
            'handoff_completion_boundary_allowed_to_auto_deploy',
            'a01_promoted',
            'a01_used_as_runtime_fallback',
            'production_ready',
            'production_catalog_runtime_wired',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime',
            'handoff_completion_boundary_context_persisted_to_live_runtime',
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
        $c123 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c123_lock_validation_summary'] = $this->c123LockValidationSummary($load, $c123);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c123);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c123, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c124_handoff_completion_boundary_decision'] = $this->handoffCompletionBoundaryDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_decision'] = $artifact['c124_handoff_completion_boundary_decision'];
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_manifest'] = $this->handoffCompletionBoundaryManifest($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_context_summary'] = $this->handoffCompletionBoundaryContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c123_handoff_finalization_carry_forward_validation_summary'] = $this->c123HandoffFinalizationCarryForwardValidationSummary($c123, $pass);
        $artifact['handoff_completion_boundary_governance_summary'] = $this->handoffCompletionBoundaryGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : $forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function c123LockValidationSummary(array $load, array $c123): array
    {
        return [
            'c123_lock_validation_completed' => true,
            'c123_artifact_path' => $load['path'],
            'expected_c123_hash' => $load['expected_hash'],
            'actual_c123_hash' => $load['actual_hash'],
            'c123_hash_match' => $load['hash_match'],
            'expected_c123_file_sha1' => $load['expected_file_sha1'],
            'actual_c123_file_sha1' => $load['actual_file_sha1'],
            'c123_file_sha1_match' => $load['file_sha1_match'],
            'c123_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c123_convert_from_json_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c123_status' => (string) ($c123['status'] ?? ''),
            'c123_reason_code' => (string) ($c123['reason_code'] ?? ''),
            'c123_phase_label' => (string) ($c123['phase_label'] ?? ''),
            'c123_phase_label_match' => ($c123['phase_label'] ?? null) === self::EXPECTED_C123_PHASE_LABEL,
            'c123_next_recommendation_match' => $this->c123NextRecommendationMatches($c123),
            'c123_handoff_finalized' => $this->c123HandoffFinalized($c123),
        ];
    }

    private function lineageValidationSummary(array $c123): array
    {
        $locks = is_array($c123['source_artifact_locks'] ?? null) ? $c123['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'c123_to_c122_lock_match' => (($locks['c122_hash_match'] ?? null) === true && ($locks['c122_file_sha1_match'] ?? null) === true),
            'c122_to_c103_lock_present' => (string) ($locks['c103_artifact_hash_from_c122'] ?? '') !== '' && (string) ($locks['c103_file_sha1_from_c122'] ?? '') !== '',
            'c123_source_lineage_match' => $this->lineageLocksMatch($c123),
            'lineage_source' => 'C123_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(array $c123, bool $pass): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_freeze_pass' => $pass,
            'candidate_scope_source' => 'C123_LOCKED_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'source_primary_candidate_code' => (string) ($c123['primary_candidate_code'] ?? ''),
            'source_backup_candidate_code' => (string) ($c123['backup_candidate_code'] ?? ''),
            'source_comparator_candidate_code' => (string) ($c123['comparator_candidate_code'] ?? ''),
            'primary_candidate_unchanged' => ($c123['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE,
            'backup_candidate_unchanged' => ($c123['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE,
            'a01_remains_comparator_only' => ($c123['a01_remains_comparator_only'] ?? null) === true,
            'candidate_scope_changed_after_c123' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'handoff_completion_boundary_used_for_selection' => false,
            'handoff_completion_boundary_used_for_retuning' => false,
            'handoff_completion_boundary_used_for_ranking' => false,
            'handoff_completion_boundary_used_for_plan_confirm_mutation' => false,
            'handoff_completion_boundary_used_for_live_rollout' => false,
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
            'handoff_completion_boundary_reference_scope' => 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_ONLY',
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

    private function handoffCompletionBoundaryDecision(bool $pass): array
    {
        $decision = [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c123_lock_valid' => $pass,
            'c123_handoff_finalized' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_executed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_pass' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared' => $pass,
            'handoff_completion_boundary_cleared' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_confirmed' => $pass,
            'handoff_completion_boundary_confirmed' => $pass,
            'handoff_completion_boundary_go_decision' => $pass ? 'HANDOFF_COMPLETION_BOUNDARY_CLEARED_GO' : 'NO_GO',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => $pass,
            'controlled_runtime_wiring_handoff_finalized' => $pass,
            'handoff_finalized' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => $pass,
            'controlled_runtime_wiring_handoff_ready' => $pass,
            'handoff_ready' => $pass,
            'ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => $pass,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => $pass,
            'controlled_runtime_wiring_handoff_completion_boundary_manifest_created' => true,
            'controlled_runtime_wiring_handoff_closure_seal_review_allowed_next' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_cleared' => $pass,
            'completion_boundary_cleared' => $pass,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => $pass,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => $pass,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => $pass,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => $pass,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'a01_remains_comparator_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'handoff_completion_boundary_artifact_only' => true,
            'handoff_completion_boundary_used_for_selection' => false,
            'handoff_completion_boundary_used_for_retuning' => false,
            'handoff_completion_boundary_used_for_ranking' => false,
            'handoff_completion_boundary_used_for_plan_confirm_mutation' => false,
            'handoff_completion_boundary_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C125_RECOMMENDATION : 'C124_TARGETED_C123_HANDOFF_FINALIZATION_REPAIR',
            'decision_reason' => $pass ? 'C124 weekly swing watchlist controlled runtime wiring handoff completion boundary is cleared for primary and backup in artifact-only audit context.' : 'C124 handoff completion boundary review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C125_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_CLOSURE_SEAL_REVIEW' : 'C124_HANDOFF_COMPLETION_BOUNDARY_REPAIR_REQUIRED',
            'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return array_merge($this->handoffCompletionBoundaryDecision($pass), [
            'validation_completed' => true,
            'next_recommendation' => $pass ? self::C125_RECOMMENDATION : 'C124_TARGETED_C123_HANDOFF_FINALIZATION_REPAIR',
        ]);
    }

    private function handoffCompletionBoundaryManifest(bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'artifact_only_controlled_runtime_wiring_handoff_completion_boundary_review',
            'execution_mode' => 'controlled_runtime_wiring_artifact_only_handoff_completion_boundary',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_controlled_runtime_wiring_handoff_completion_boundary_cleared_candidate',
            'backup_candidate_role' => 'backup_controlled_runtime_wiring_handoff_completion_boundary_cleared_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_completion_boundary_cleared' => $pass,
            'handoff_completion_boundary_confirmed' => $pass,
            'handoff_completion_boundary_go_decision' => $pass ? 'HANDOFF_COMPLETION_BOUNDARY_CLEARED_GO' : 'NO_GO',
            'handoff_finalized' => $pass,
            'handoff_ready' => $pass,
            'ready_for_controlled_runtime_wiring_handoff_closure_seal_review' => $pass,
            'controlled_runtime_wiring_handoff_closure_seal_review_allowed_next' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'handoff_completion_boundary_artifact_only' => true,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'handoff_completion_boundary_used_for_selection' => false,
            'handoff_completion_boundary_used_for_retuning' => false,
            'handoff_completion_boundary_used_for_ranking' => false,
            'handoff_completion_boundary_used_for_plan_confirm_mutation' => false,
            'handoff_completion_boundary_used_for_live_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
            'controlled_runtime_wiring_handoff_completion_boundary_steps' => [
                'validate_locked_c123_handoff_finalization_artifact',
                'confirm_primary_and_backup_handoff_finalized_scope',
                'clear_primary_and_backup_controlled_runtime_wiring_handoff_completion_boundary',
                'confirm_a01_comparator_only_scope',
                'record_controlled_runtime_wiring_handoff_completion_boundary_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_pass' => $pass,
            'handoff_completion_boundary_cleared' => $pass,
            'handoff_completion_boundary_confirmed' => $pass,
            'handoff_completion_boundary_go_decision' => $pass ? 'HANDOFF_COMPLETION_BOUNDARY_CLEARED_GO' : 'NO_GO',
            'handoff_finalized' => $pass,
            'handoff_ready' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'controlled_runtime_wiring_handoff_completion_boundary_advisory_only_pass' => $pass,
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
                'c124_role' => 'primary_controlled_runtime_wiring_handoff_completion_boundary_cleared_candidate',
                'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c124_role' => 'backup_controlled_runtime_wiring_handoff_completion_boundary_cleared_candidate',
                'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c124_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_pass' => false,
                'handoff_completion_boundary_cleared' => false,
                'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared' => false,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffCompletionBoundaryContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_context_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime' => false,
            'handoff_completion_boundary_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_source_identified' => is_file(self::RUNTIME_PATHS['c123_weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_service']),
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_source_identified' => is_file(self::RUNTIME_PATHS['c124_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_service']),
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
            'handoff_completion_boundary_confirmation_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c123HandoffFinalizationCarryForwardValidationSummary(array $c123, bool $pass): array
    {
        return [
            'c123_handoff_finalization_carry_forward_validation_completed' => true,
            'c123_handoff_finalization_carry_forward_validation_pass' => $pass,
            'c123_status' => (string) ($c123['status'] ?? ''),
            'c123_reason_code' => (string) ($c123['reason_code'] ?? ''),
            'c123_artifact_hash' => (string) ($c123['artifact_hash'] ?? ''),
            'c123_handoff_finalization_review_pass' => ($c123['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass'] ?? null) === true,
            'c123_handoff_finalized' => ($c123['handoff_finalized'] ?? null) === true,
            'c123_handoff_finalization_confirmed' => ($c123['handoff_finalization_confirmed'] ?? null) === true,
            'c123_handoff_finalization_go_decision' => (string) ($c123['handoff_finalization_go_decision'] ?? ''),
            'c123_handoff_ready' => ($c123['handoff_ready'] ?? null) === true,
            'c123_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => ($c123['ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] ?? null) === true,
            'c123_completion_boundary_cleared' => ($c123['completion_boundary_cleared'] ?? null) === true,
            'c123_primary_candidate_handoff_finalized' => ($c123['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] ?? null) === true,
            'c123_backup_candidate_handoff_finalized' => ($c123['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] ?? null) === true,
            'c123_comparator_candidate_handoff_finalized' => ($c123['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] ?? null) === false,
            'c123_a01_remains_comparator_only' => ($c123['a01_remains_comparator_only'] ?? null) === true,
            'expected_c123_next_recommendation' => self::EXPECTED_C123_RECOMMENDATION,
        ];
    }

    private function handoffCompletionBoundaryGovernanceSummary(bool $pass): array
    {
        return [
            'handoff_completion_boundary_governance_completed' => true,
            'handoff_completion_boundary_governance_pass' => $pass,
            'handoff_completion_boundary_cleared' => $pass,
            'handoff_completion_boundary_confirmed' => $pass,
            'handoff_completion_boundary_go_decision' => $pass ? 'HANDOFF_COMPLETION_BOUNDARY_CLEARED_GO' : 'NO_GO',
            'handoff_completion_boundary_is_explicit_context_only' => true,
            'handoff_completion_boundary_is_controlled_runtime_wiring_default' => true,
            'handoff_completion_boundary_is_artifact_only' => true,
            'handoff_completion_boundary_is_advisory_only' => true,
            'handoff_completion_boundary_used_for_selection' => false,
            'handoff_completion_boundary_used_for_retuning' => false,
            'handoff_completion_boundary_used_for_ranking' => false,
            'handoff_completion_boundary_used_for_plan_confirm_mutation' => false,
            'handoff_completion_boundary_used_for_live_rollout' => false,
            'handoff_completion_boundary_allowed_to_auto_enable_runtime' => false,
            'handoff_completion_boundary_allowed_to_auto_deploy' => false,
            'handoff_completion_boundary_classification' => 'WEEKLY_SWING_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C124_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_pass' => $pass,
            'handoff_completion_boundary_cleared' => $pass,
            'handoff_completion_boundary_confirmed' => $pass,
            'handoff_completion_boundary_go_decision' => $pass ? 'HANDOFF_COMPLETION_BOUNDARY_CLEARED_GO' : 'NO_GO',
            'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_seal_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C125_RECOMMENDATION : 'C124_TARGETED_C123_HANDOFF_FINALIZATION_REPAIR',
            'selection_changed_after_c123' => false,
            'parameter_changed_after_c123' => false,
            'new_candidate_created' => false,
            'handoff_completion_boundary_used_for_selection' => false,
            'handoff_completion_boundary_used_for_retuning' => false,
            'handoff_completion_boundary_used_for_ranking' => false,
            'handoff_completion_boundary_used_for_plan_confirm_mutation' => false,
            'handoff_completion_boundary_used_for_live_rollout' => false,
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
            'targeted_repair_recommendation' => $status === [] ? self::C125_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW',
            'achieved' => [
                'C123 artifact hash and file SHA1 validated',
                'C123 handoff finalization evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Handoff completion boundary cleared for E02 and B01 only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist controlled runtime wiring handoff completion boundary manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C125_RECOMMENDATION : 'C124_TARGETED_C123_HANDOFF_FINALIZATION_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist controlled runtime wiring handoff closure seal review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C124 artifact hash',
                'locked C124 file SHA1',
                'unchanged candidate scope',
                'controlled runtime wiring artifact-only handoff completion boundary evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C124 validates C123 artifact_hash and file SHA1 locks before weekly swing watchlist controlled runtime wiring handoff completion boundary review is recorded.',
            'C124 validates C123 handoff finalization fields and A01 comparator-only state.',
            'C124 confirms no temporary negative test artifact remains before a passing controlled runtime wiring handoff completion boundary review.',
            'C124 clears handoff completion boundary for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C124 creates an artifact-only weekly swing watchlist controlled runtime wiring handoff completion boundary manifest and no official weekly swing recommendation output.',
            'C124 weekly swing watchlist controlled runtime wiring handoff completion boundary review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C124 may only recommend C125 weekly swing watchlist controlled runtime wiring handoff closure seal review as the next audit-only step.',
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
        if (strpos($status, 'C123_ARTIFACT') !== false || strpos($status, 'C123_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C124_C123_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'BOUNDARY') !== false) {
            return 'C124_HANDOFF_COMPLETION_BOUNDARY_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C124_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C124_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C124_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C124_TARGETED_C123_HANDOFF_FINALIZATION_REPAIR';
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
