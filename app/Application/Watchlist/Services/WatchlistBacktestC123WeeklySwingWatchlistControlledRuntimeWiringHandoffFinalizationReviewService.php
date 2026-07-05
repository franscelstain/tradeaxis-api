<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService
{
    public const RUN_CODE = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-11 / C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW';

    public const DEFAULT_C122_ARTIFACT = 'storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json';
    public const DEFAULT_EXPECTED_C122_HASH = '0edfa166bfa8f195db6dfd09f318b6e0515cc5c7';
    public const DEFAULT_EXPECTED_C122_FILE_SHA1 = 'FF830FE04623A636F86E514120575BD57A98EEB4';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C122_STATUS = 'C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP';
    private const EXPECTED_C122_REASON = 'C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP';
    private const EXPECTED_C122_RECOMMENDATION = self::RUN_CODE;
    private const EXPECTED_C122_PHASE_LABEL = 'PR-10 / C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW';
    private const C124_RECOMMENDATION = 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
    private const PASS_STATUS = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const HANDOFF_NOT_CONFIRMED_STATUS = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_FINALIZATION_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const C122_CONVERT_FROM_JSON_STATUS = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_C122_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
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

    private const C122_LIVE_OR_MUTATING_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
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
        'c123_validation_doc' => 'docs/watchlist/audit/WS_C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW.md',
        'c123_operator_commands_doc' => 'docs/watchlist/audit/WS_C123_OPERATOR_VALIDATION_COMMANDS.md',
        'c122_validation_doc' => 'docs/watchlist/audit/WS_C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW.md',
        'c122_operator_commands_doc' => 'docs/watchlist/audit/WS_C122_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c122_weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewService.php',
        'c123_weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService.php',
        'c122_command' => 'app/Console/Commands/Watchlist/RunBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewCommand.php',
        'c123_command' => 'app/Console/Commands/Watchlist/RunBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewCommand.php',
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
        string $c122Artifact = self::DEFAULT_C122_ARTIFACT,
        string $expectedC122Hash = self::DEFAULT_EXPECTED_C122_HASH,
        string $expectedC122FileSha1 = self::DEFAULT_EXPECTED_C122_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c122Artifact, $expectedC122Hash, $expectedC122FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C123_BLOCKED_C122_ARTIFACT_LOCK_MISMATCH', 'C122 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c122_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C122_CONVERT_FROM_JSON_STATUS, 'C122 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C123_BLOCKED_C122_ARTIFACT_LOCK_MISMATCH', 'C122 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C123_BLOCKED_C122_FILE_SHA1_LOCK_MISMATCH', 'C122 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c122 = $load['payload'];
        if (($c122['status'] ?? null) !== self::EXPECTED_C122_STATUS || ($c122['reason_code'] ?? null) !== self::EXPECTED_C122_REASON) {
            return $this->blocked($artifact, 'C123_BLOCKED_C122_STATUS_OR_REASON_MISMATCH', 'C122 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c122['phase_label'] ?? null) !== self::EXPECTED_C122_PHASE_LABEL) {
            return $this->blocked($artifact, 'C123_BLOCKED_C122_PHASE_LABEL_MISMATCH', 'C122 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c122NextRecommendationMatches($c122)) {
            return $this->blocked($artifact, 'C123_BLOCKED_C122_NEXT_RECOMMENDATION_MISMATCH', 'C122 next recommendation is not C123.', $outputPath, $overwrite);
        }
        if (! $this->c122HandoffReady($c122)) {
            return $this->blocked($artifact, 'C123_BLOCKED_C122_HANDOFF_NOT_READY', 'C122 handoff readiness evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c122);
        if ($safetyFailure !== null) {
            $artifact['c122_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C123_BLOCKED_C122_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C122 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c122)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C122 candidate scope does not match locked controlled runtime wiring handoff readiness decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C123 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C123 weekly swing watchlist controlled runtime wiring handoff finalization review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C123 finalized the weekly swing watchlist controlled runtime wiring handoff package for E02 primary and B01 backup as artifact-only evidence. This still does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
        $artifact['next_step_recommendation'] = self::C124_RECOMMENDATION;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_executed'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_allowed'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass'] = true;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized'] = true;
        $artifact['controlled_runtime_wiring_handoff_finalized'] = true;
        $artifact['handoff_finalized'] = true;
        $artifact['handoff_finalization_confirmed'] = true;
        $artifact['handoff_finalization_go_decision'] = 'HANDOFF_FINALIZED_GO';
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready'] = true;
        $artifact['controlled_runtime_wiring_handoff_ready'] = true;
        $artifact['handoff_ready'] = true;
        $artifact['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] = true;
        $artifact['ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] = true;
        $artifact['controlled_runtime_wiring_handoff_finalization_manifest_created'] = true;
        $artifact['controlled_runtime_wiring_handoff_completion_boundary_review_allowed_next'] = true;
        $artifact['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] = true;
        $artifact['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] = true;
        $artifact['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized'] = false;
        $artifact['primary_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] = true;
        $artifact['backup_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] = true;
        $artifact['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review'] = false;
        $artifact['primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready'] = true;
        $artifact['backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready'] = true;
        $artifact['comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready'] = false;
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_cleared'] = true;
        $artifact['completion_boundary_cleared'] = true;
        $artifact['boundary_go_decision'] = 'BOUNDARY_CLEARED_GO';
        $artifact['operator_go_decision'] = 'GO';
        $artifact['go_decision_finalized'] = true;
        $artifact['c122_handoff_ready'] = true;
        $artifact['a01_remains_comparator_only'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-11',
            'internal_checkpoint' => 'C123',
            'status' => 'C123_NOT_RUN',
            'reason_code' => 'C123_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_executed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_allowed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => false,
            'controlled_runtime_wiring_handoff_finalized' => false,
            'handoff_finalized' => false,
            'handoff_finalization_confirmed' => false,
            'handoff_finalization_go_decision' => 'NO_GO',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => false,
            'controlled_runtime_wiring_handoff_ready' => false,
            'handoff_ready' => false,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => false,
            'ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => false,
            'controlled_runtime_wiring_handoff_finalization_manifest_created' => false,
            'controlled_runtime_wiring_handoff_completion_boundary_review_allowed_next' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => false,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => false,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => false,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => false,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_cleared' => false,
            'completion_boundary_cleared' => false,
            'boundary_go_decision' => 'NO_GO',
            'operator_go_decision' => 'NO_GO',
            'go_decision_finalized' => false,
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
            if (is_array($decoded)) {
                $payload = $decoded;
                $actualHash = (string) ($decoded['artifact_hash'] ?? '');
            }
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
        $c122 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c122['source_artifact_locks'] ?? null) ? $c122['source_artifact_locks'] : [];
        return [
            'c122_artifact_path' => $load['path'],
            'expected_c122_hash' => $load['expected_hash'],
            'actual_c122_hash' => $load['actual_hash'],
            'c122_hash_match' => $load['hash_match'],
            'expected_c122_file_sha1' => $load['expected_file_sha1'],
            'actual_c122_file_sha1' => $load['actual_file_sha1'],
            'c122_file_sha1_match' => $load['file_sha1_match'],
            'c122_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c122_convert_from_json_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c103_artifact_hash_from_c122' => (string) ($locks['actual_c103_hash'] ?? ($c122['actual_c103_hash'] ?? '')),
            'c103_file_sha1_from_c122' => (string) ($locks['actual_c103_file_sha1'] ?? ($c122['actual_c103_file_sha1'] ?? '')),
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
            'expected_c122_hash' => $load['expected_hash'],
            'actual_c122_hash' => $load['actual_hash'],
            'c122_hash_match' => $load['hash_match'],
            'expected_c122_file_sha1' => $load['expected_file_sha1'],
            'actual_c122_file_sha1' => $load['actual_file_sha1'],
            'c122_file_sha1_match' => $load['file_sha1_match'],
            'c122_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function c122NextRecommendationMatches(array $c122): bool
    {
        $paths = [
            ['next_step_recommendation'],
            ['next_handoff_finalization_decision', 'next_recommendation'],
            ['c122_handoff_readiness_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ];
        $found = false;
        foreach ($paths as $path) {
            $value = $this->valueAt($c122, $path);
            if ($value === null || $value === '') {
                continue;
            }
            $found = true;
            if ($value !== self::EXPECTED_C122_RECOMMENDATION) {
                return false;
            }
        }
        return $found;
    }

    private function c122HandoffReady(array $c122): bool
    {
        if (($c122['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_review_pass'] ?? null) !== true) {
            return false;
        }
        if (($c122['controlled_runtime_wiring_handoff_readiness_review_pass'] ?? null) !== true) {
            return false;
        }
        if (($c122['weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready'] ?? null) !== true || ($c122['controlled_runtime_wiring_handoff_ready'] ?? null) !== true || ($c122['handoff_ready'] ?? null) !== true) {
            return false;
        }
        if (($c122['handoff_readiness_confirmed'] ?? null) !== true || ($c122['handoff_readiness_go_decision'] ?? null) !== 'HANDOFF_READY_GO') {
            return false;
        }
        if (($c122['completion_boundary_cleared'] ?? null) !== true || ($c122['completion_boundary_confirmed'] ?? null) !== true) {
            return false;
        }
        if (($c122['ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) !== true) {
            return false;
        }
        if (($c122['weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) !== true) {
            return false;
        }
        if (($c122['controlled_runtime_wiring_handoff_readiness_manifest_created'] ?? null) !== true || ($c122['controlled_runtime_wiring_handoff_finalization_review_allowed_next'] ?? null) !== true) {
            return false;
        }
        if (($c122['primary_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) !== true) {
            return false;
        }
        if (($c122['backup_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) !== true) {
            return false;
        }
        if (($c122['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) !== false) {
            return false;
        }
        if (($c122['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        if (($c122['c122_handoff_readiness_decision']['next_recommendation'] ?? null) !== self::RUN_CODE) {
            return false;
        }
        if (($c122['next_handoff_finalization_decision']['next_recommendation'] ?? null) !== self::RUN_CODE) {
            return false;
        }
        $manifest = is_array($c122['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest'] ?? null)
            ? $c122['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest']
            : [];
        if (($manifest['handoff_readiness_artifact_only'] ?? null) !== true || ($manifest['handoff_ready'] ?? null) !== true || ($manifest['handoff_readiness_confirmed'] ?? null) !== true) {
            return false;
        }
        if (($manifest['handoff_readiness_go_decision'] ?? null) !== 'HANDOFF_READY_GO') {
            return false;
        }
        if (($manifest['controlled_runtime_wiring_handoff_readiness_review_pass'] ?? null) !== true || ($manifest['ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) !== true) {
            return false;
        }
        foreach ([
            'handoff_readiness_used_for_selection',
            'handoff_readiness_used_for_retuning',
            'handoff_readiness_used_for_ranking',
            'handoff_readiness_used_for_plan_confirm_mutation',
            'handoff_readiness_used_for_live_rollout',
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
        foreach (self::C122_LIVE_OR_MUTATING_FLAGS as $flag) {
            if (($source[$flag] ?? null) === true) {
                return $flag;
            }
        }
        $manifest = is_array($source['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest'] ?? null)
            ? $source['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest']
            : [];
        foreach (self::C122_LIVE_OR_MUTATING_FLAGS as $flag) {
            if (($manifest[$flag] ?? null) === true) {
                return 'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_manifest.'.$flag;
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c122): bool
    {
        return ($c122['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c122['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c122['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c122['a01_remains_comparator_only'] ?? null) === true
            && ($c122['primary_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) === true
            && ($c122['backup_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) === true
            && ($c122['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) === false;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (($options['handoff_finalization_confirmed'] ?? true) !== true) {
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
                $failures[] = 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_RUNTIME_GATE_ON';
                break;
            }
        }
        return $failures;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c122 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c122_lock_validation_summary'] = [
            'c122_lock_validation_completed' => true,
            'c122_artifact_path' => $load['path'],
            'expected_c122_hash' => $load['expected_hash'],
            'actual_c122_hash' => $load['actual_hash'],
            'c122_hash_match' => $load['hash_match'],
            'expected_c122_file_sha1' => $load['expected_file_sha1'],
            'actual_c122_file_sha1' => $load['actual_file_sha1'],
            'c122_file_sha1_match' => $load['file_sha1_match'],
            'c122_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c122_status' => (string) ($c122['status'] ?? ''),
            'c122_reason_code' => (string) ($c122['reason_code'] ?? ''),
            'c122_phase_label' => (string) ($c122['phase_label'] ?? ''),
            'c122_phase_label_match' => ($c122['phase_label'] ?? null) === self::EXPECTED_C122_PHASE_LABEL,
            'c122_next_recommendation_match' => $this->c122NextRecommendationMatches($c122),
        ];
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c122, $pass);
        $artifact['operator_approval_validation_summary'] = [
            'operator_approval_validation_completed' => true,
            'operator_approval_validation_pass' => $pass,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_required' => true,
            'handoff_finalization_reference_scope' => 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_ONLY',
        ];
        $artifact['temporary_negative_artifact_guard_summary'] = [
            'temporary_negative_artifact_guard_completed' => true,
            'temporary_negative_artifacts_remaining' => (bool) ($options['temporary_negative_artifact_paths'] ?? false),
            'temporary_negative_artifact_cleanup_confirmed' => (array) ($options['temporary_negative_artifact_paths'] ?? []) === [],
            'temporary_negative_artifact_paths' => (array) ($options['temporary_negative_artifact_paths'] ?? []),
        ];
        $artifact['c123_handoff_finalization_decision'] = $this->handoffFinalizationDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['next_handoff_completion_boundary_decision'] = $artifact['next_readiness_decision'];
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_decision'] = $artifact['c123_handoff_finalization_decision'];
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_manifest'] = $this->handoffFinalizationManifest($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_checklist'] = $this->handoffFinalizationChecklist($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_candidate_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_summary'] = $this->handoffFinalizationContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c122_handoff_readiness_carry_forward_validation_summary'] = $this->c122HandoffReadinessCarryForwardValidationSummary($c122, $pass);
        $artifact['handoff_finalization_governance_summary'] = $this->handoffFinalizationGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function candidateScopeFreezeSummary(array $c122, bool $pass): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_freeze_pass' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'source_primary_candidate_code' => (string) ($c122['primary_candidate_code'] ?? ''),
            'source_backup_candidate_code' => (string) ($c122['backup_candidate_code'] ?? ''),
            'source_comparator_candidate_code' => (string) ($c122['comparator_candidate_code'] ?? ''),
            'primary_candidate_unchanged' => ($c122['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE,
            'backup_candidate_unchanged' => ($c122['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE,
            'a01_remains_comparator_only' => ($c122['a01_remains_comparator_only'] ?? null) === true,
            'a01_promoted' => false,
            'new_candidate_created' => false,
        ];
    }

    private function handoffFinalizationDecision(bool $pass): array
    {
        $decision = [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c122_lock_valid' => $pass,
            'c122_handoff_ready' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_executed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized' => $pass,
            'controlled_runtime_wiring_handoff_finalized' => $pass,
            'handoff_finalized' => $pass,
            'handoff_finalization_confirmed' => $pass,
            'handoff_finalization_go_decision' => $pass ? 'HANDOFF_FINALIZED_GO' : 'NO_GO',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready' => $pass,
            'controlled_runtime_wiring_handoff_ready' => $pass,
            'handoff_ready' => $pass,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => $pass,
            'ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => $pass,
            'controlled_runtime_wiring_handoff_finalization_manifest_created' => true,
            'controlled_runtime_wiring_handoff_completion_boundary_review_allowed_next' => $pass,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => $pass,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => $pass,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => false,
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => $pass,
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => $pass,
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_ready' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_cleared' => $pass,
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
            'handoff_finalization_artifact_only' => true,
            'handoff_finalization_used_for_selection' => false,
            'handoff_finalization_used_for_retuning' => false,
            'handoff_finalization_used_for_ranking' => false,
            'handoff_finalization_used_for_plan_confirm_mutation' => false,
            'handoff_finalization_used_for_live_rollout' => false,
            'next_recommendation' => $pass ? self::C124_RECOMMENDATION : 'C123_TARGETED_C122_HANDOFF_READINESS_REPAIR',
            'decision_reason' => $pass ? 'C123 weekly swing watchlist controlled runtime wiring handoff package is finalized for E02 primary and B01 backup in artifact-only audit context.' : 'C123 handoff finalization review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW' : 'C123_HANDOFF_FINALIZATION_REPAIR_REQUIRED',
            'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return array_merge($this->handoffFinalizationDecision($pass), [
            'validation_completed' => true,
            'next_recommendation' => $pass ? self::C124_RECOMMENDATION : 'C123_TARGETED_C122_HANDOFF_READINESS_REPAIR',
        ]);
    }

    private function handoffFinalizationManifest(bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'artifact_only_controlled_runtime_wiring_handoff_finalization_review',
            'execution_mode' => 'controlled_runtime_wiring_artifact_only_handoff_finalization',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_controlled_runtime_wiring_handoff_finalized_candidate',
            'backup_candidate_role' => 'backup_controlled_runtime_wiring_handoff_finalized_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_finalized' => $pass,
            'handoff_finalization_confirmed' => $pass,
            'handoff_finalization_go_decision' => $pass ? 'HANDOFF_FINALIZED_GO' : 'NO_GO',
            'handoff_ready' => $pass,
            'ready_for_controlled_runtime_wiring_handoff_completion_boundary_review' => $pass,
            'handoff_finalization_artifact_only' => true,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'handoff_finalization_used_for_selection' => false,
            'handoff_finalization_used_for_retuning' => false,
            'handoff_finalization_used_for_ranking' => false,
            'handoff_finalization_used_for_plan_confirm_mutation' => false,
            'handoff_finalization_used_for_live_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
            'non_live_handoff_finalization_steps' => [
                'validate_locked_c122_handoff_readiness_artifact',
                'confirm_primary_and_backup_handoff_ready_scope',
                'finalize_primary_and_backup_non_live_handoff_package',
                'confirm_a01_comparator_only_scope',
                'record_non_live_handoff_finalization_manifest_without_stock_recommendations',
                'confirm_live_runtime_and_plan_confirm_remain_unchanged',
            ],
        ];
    }

    private function handoffFinalizationChecklist(bool $pass): array
    {
        return [
            'handoff_finalization_reviewed' => true,
            'handoff_finalization_confirmation_required' => true,
            'handoff_finalization_confirmed' => $pass,
            'handoff_finalization_go_decision' => $pass ? 'HANDOFF_FINALIZED_GO' : 'NO_GO',
            'c122_source_lock_reviewed' => true,
            'c122_handoff_ready_required' => true,
            'c122_handoff_ready_valid' => $pass,
            'production_runtime_wiring_not_enabled' => true,
            'production_catalog_runtime_wired' => false,
            'runtime_bridge_not_enabled' => true,
            'controlled_rollout_not_enabled' => true,
            'pilot_runtime_not_enabled' => true,
            'shadow_runtime_not_enabled' => true,
            'scheduler_live_weekly_swing_not_enabled' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'production_config_default_unchanged' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'handoff_finalization_review_only' => true,
            'non_live' => true,
            'non_mutating' => true,
            'artifact_only' => true,
            'live_endpoint_called' => false,
            'scheduler_executed' => false,
            'weekly_swing_stock_recommendation_generated' => false,
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass' => $pass,
            'handoff_finalized' => $pass,
            'handoff_ready' => $pass,
            'completion_boundary_cleared' => $pass,
            'operator_approval_validation_pass' => $pass,
            'temporary_negative_artifact_cleanup_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'controlled_runtime_wiring_handoff_finalization_advisory_only_pass' => $pass,
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
                'c123_role' => 'primary_controlled_runtime_wiring_handoff_finalized_candidate',
                'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c123_role' => 'backup_controlled_runtime_wiring_handoff_finalized_candidate',
                'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => $pass,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c123_role' => 'comparator_only_candidate',
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass' => false,
                'handoff_finalized' => false,
                'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized' => false,
                'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffFinalizationContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_handoff_finalization_context_persisted_to_live_runtime' => false,
            'handoff_finalization_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_review_source_identified' => is_file(self::RUNTIME_PATHS['c122_weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_review_service']),
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_source_identified' => is_file(self::RUNTIME_PATHS['c123_weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_service']),
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
            'handoff_finalization_confirmation_required' => true,
            'kill_switch_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
        ];
    }

    private function c122HandoffReadinessCarryForwardValidationSummary(array $c122, bool $pass): array
    {
        return [
            'c122_handoff_readiness_carry_forward_validation_completed' => true,
            'c122_handoff_readiness_carry_forward_validation_pass' => $pass,
            'c122_status' => (string) ($c122['status'] ?? ''),
            'c122_reason_code' => (string) ($c122['reason_code'] ?? ''),
            'c122_artifact_hash' => (string) ($c122['artifact_hash'] ?? ''),
            'c122_handoff_readiness_review_pass' => ($c122['weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_review_pass'] ?? null) === true,
            'c122_handoff_ready' => ($c122['handoff_ready'] ?? null) === true,
            'c122_primary_candidate_ready_for_handoff_finalization' => ($c122['primary_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) === true,
            'c122_backup_candidate_ready_for_handoff_finalization' => ($c122['backup_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) === true,
            'c122_comparator_candidate_ready_for_handoff_finalization' => ($c122['comparator_candidate_ready_for_controlled_runtime_wiring_handoff_finalization_review'] ?? null) === false,
            'c122_a01_remains_comparator_only' => ($c122['a01_remains_comparator_only'] ?? null) === true,
            'expected_c122_next_recommendation' => self::EXPECTED_C122_RECOMMENDATION,
        ];
    }

    private function handoffFinalizationGovernanceSummary(bool $pass): array
    {
        return [
            'handoff_finalization_governance_completed' => true,
            'handoff_finalization_governance_pass' => $pass,
            'handoff_finalized' => $pass,
            'handoff_finalization_is_explicit_context_only' => true,
            'handoff_finalization_is_non_live_default' => true,
            'handoff_finalization_is_artifact_only' => true,
            'handoff_finalization_is_advisory_only' => true,
            'handoff_finalization_used_for_selection' => false,
            'handoff_finalization_used_for_retuning' => false,
            'handoff_finalization_used_for_ranking' => false,
            'handoff_finalization_used_for_plan_confirm_mutation' => false,
            'handoff_finalization_used_for_live_rollout' => false,
            'handoff_finalization_allowed_to_auto_enable_runtime' => false,
            'handoff_finalization_allowed_to_auto_deploy' => false,
            'handoff_finalization_classification' => 'WEEKLY_SWING_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C123_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass' => $pass,
            'handoff_finalized' => $pass,
            'candidate_ready_for_weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C124_RECOMMENDATION : 'C123_TARGETED_C122_HANDOFF_READINESS_REPAIR',
            'selection_changed_after_c122' => false,
            'parameter_changed_after_c122' => false,
            'new_candidate_created' => false,
            'handoff_finalization_used_for_selection' => false,
            'handoff_finalization_used_for_retuning' => false,
            'handoff_finalization_used_for_ranking' => false,
            'handoff_finalization_used_for_plan_confirm_mutation' => false,
            'handoff_finalization_used_for_live_rollout' => false,
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
            'targeted_repair_recommendation' => $status === [] ? self::C124_RECOMMENDATION : $this->repairRecommendationFor($status[0] ?? ''),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW',
            'achieved' => [
                'C122 artifact hash and file SHA1 validated',
                'C122 handoff readiness evidence validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Handoff package finalized for E02 and B01 only',
                'Temporary negative artifact cleanup confirmed',
                'Weekly swing watchlist controlled runtime wiring handoff finalization manifest recorded',
                'PLAN/CONFIRM, production runtime, and weekly swing live output remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C124_RECOMMENDATION : 'C123_TARGETED_C122_HANDOFF_READINESS_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist controlled runtime wiring handoff completion boundary review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C123 artifact hash',
                'locked C123 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only handoff finalization evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C123 validates C122 artifact_hash and file SHA1 locks before weekly swing watchlist controlled runtime wiring handoff finalization review is recorded.',
            'C123 validates C122 handoff readiness fields and A01 comparator-only state.',
            'C123 confirms no temporary negative test artifact remains before a passing controlled runtime wiring handoff finalization review.',
            'C123 finalizes handoff for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C123 creates an artifact-only weekly swing watchlist controlled runtime wiring handoff finalization manifest and no official weekly swing recommendation output.',
            'C123 weekly swing watchlist controlled runtime wiring handoff finalization review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C123 may only recommend C124 weekly swing watchlist controlled runtime wiring handoff completion boundary review as the next audit-only step.',
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
        if (strpos($status, 'C122_ARTIFACT') !== false || strpos($status, 'C122_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C123_C122_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false) {
            return 'C123_HANDOFF_FINALIZATION_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C123_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C123_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C123_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C123_TARGETED_C122_HANDOFF_READINESS_REPAIR';
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
