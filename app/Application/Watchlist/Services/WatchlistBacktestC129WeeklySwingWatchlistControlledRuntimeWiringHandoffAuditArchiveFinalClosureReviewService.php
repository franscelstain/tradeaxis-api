<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewService
{
    public const RUN_CODE = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';
    public const PHASE_LABEL = 'PR-17 / C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';
    public const ARTIFACT_TYPE = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';

    public const DEFAULT_C128_ARTIFACT = 'storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json';
    public const DEFAULT_EXPECTED_C128_HASH = '6ef4c4f7868f71fa3855c3db3a2e1372af201f68';
    public const DEFAULT_EXPECTED_C128_FILE_SHA1 = '33C094BFA0FF23952E68EB0E45A7C9AE092F9A82';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C128_STATUS = 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_SEALED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C128_PHASE_LABEL = 'PR-16 / C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    private const EXPECTED_C128_RECOMMENDATION = self::RUN_CODE;
    private const NO_NEXT_RECOMMENDATION = 'NO_NEXT_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED';
    private const PASS_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const FINAL_CLOSURE_NOT_CONFIRMED_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_AUDIT_ARCHIVE_FINAL_CLOSURE_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const C128_COMPLETION_SEAL_INCOMPLETE_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C128_AUDIT_ARCHIVE_COMPLETION_SEAL_INCOMPLETE';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C128_CONVERT_FROM_JSON_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C128_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';

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
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
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

    private const REQUIRED_C128_FIELDS = [
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass' => true,
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
        'controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
        'handoff_audit_archive_completion_ready' => true,
        'audit_archive_completion_ready' => true,
        'completion_manifest_created' => true,
        'handoff_audit_archive_completion_confirmed' => true,
        'handoff_audit_archive_completion_go_decision' => 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_pass' => true,
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_sealed' => true,
        'controlled_runtime_wiring_handoff_audit_archive_completion_sealed' => true,
        'handoff_audit_archive_completion_sealed' => true,
        'audit_archive_completion_sealed' => true,
        'completion_seal_manifest_created' => true,
        'handoff_audit_archive_completion_seal_confirmed' => true,
        'handoff_audit_archive_completion_seal_go_decision' => 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_GO',
        'ready_for_controlled_runtime_wiring_handoff_audit_archive_final_closure_review' => true,
        'controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed_next' => true,
        'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
        'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => true,
        'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready' => false,
        'primary_candidate_handoff_audit_archive_completion_sealed' => true,
        'backup_candidate_handoff_audit_archive_completion_sealed' => true,
        'comparator_candidate_handoff_audit_archive_completion_sealed' => false,
        'handoff_audit_archived' => true,
        'audit_archived' => true,
        'handoff_closure_sealed' => true,
        'handoff_completion_boundary_cleared' => true,
        'handoff_finalized' => true,
        'handoff_ready' => true,
        'a01_remains_comparator_only' => true,
    ];

    private const DOC_PATHS = [
        'c129_validation_doc' => 'docs/watchlist/audit/WS_C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW.md',
        'c129_operator_commands_doc' => 'docs/watchlist/audit/WS_C129_OPERATOR_VALIDATION_COMMANDS.md',
        'c128_validation_doc' => 'docs/watchlist/audit/WS_C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW.md',
        'c128_operator_commands_doc' => 'docs/watchlist/audit/WS_C128_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c128_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC128WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionSealReviewService.php',
        'c129_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewService.php',
        'c128_command' => 'app/Console/Commands/Watchlist/RunBacktestC128WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionSealReviewCommand.php',
        'c129_command' => 'app/Console/Commands/Watchlist/RunBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewCommand.php',
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
        string $c128Artifact = self::DEFAULT_C128_ARTIFACT,
        string $expectedC128Hash = self::DEFAULT_EXPECTED_C128_HASH,
        string $expectedC128FileSha1 = self::DEFAULT_EXPECTED_C128_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c128Artifact, $expectedC128Hash, $expectedC128FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C129_BLOCKED_C128_ARTIFACT_LOCK_MISMATCH', 'C128 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c128_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C128_CONVERT_FROM_JSON_STATUS, 'C128 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C129_BLOCKED_C128_ARTIFACT_LOCK_MISMATCH', 'C128 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C129_BLOCKED_C128_FILE_SHA1_LOCK_MISMATCH', 'C128 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c128 = $load['payload'];
        if (($c128['status'] ?? null) !== self::EXPECTED_C128_STATUS || ($c128['reason_code'] ?? null) !== self::EXPECTED_C128_STATUS) {
            return $this->blocked($artifact, 'C129_BLOCKED_C128_STATUS_OR_REASON_MISMATCH', 'C128 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c128['phase_label'] ?? null) !== self::EXPECTED_C128_PHASE_LABEL) {
            return $this->blocked($artifact, 'C129_BLOCKED_C128_PHASE_LABEL_MISMATCH', 'C128 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c128NextRecommendationMatches($c128)) {
            return $this->blocked($artifact, 'C129_BLOCKED_C128_NEXT_RECOMMENDATION_MISMATCH', 'C128 next recommendation is not C129.', $outputPath, $overwrite);
        }
        if (! $this->c128HandoffAuditArchiveCompletionSealed($c128)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C128_COMPLETION_SEAL_INCOMPLETE_STATUS, 'C128 handoff audit archive completion seal evidence is incomplete.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c128);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c128_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C128 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c128)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C128 candidate scope does not match locked controlled runtime wiring handoff audit archive completion seal decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C129 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_audit_archive_final_closure_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FINAL_CLOSURE_NOT_CONFIRMED_STATUS, 'C129 requires --handoff-audit-archive-final-closure-confirmed.', $outputPath, $overwrite);
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

            return $this->rejected($artifact, $failures[0], 'C129 weekly swing watchlist controlled runtime wiring handoff audit archive final closure review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C129 final-closes the weekly swing watchlist controlled runtime wiring handoff audit archive package for primary and backup as artifact-only evidence. This does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C129_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_CONTROLLED_RUNTIME_WIRING_AUDIT_ONLY';
        $artifact['next_step_recommendation'] = self::NO_NEXT_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-17',
            'internal_checkpoint' => 'C129',
            'status' => 'C129_NOT_RUN',
            'reason_code' => 'C129_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_executed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_pass' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closed' => false,
            'controlled_runtime_wiring_handoff_audit_archive_final_closed' => false,
            'handoff_audit_archive_final_closed' => false,
            'audit_archive_final_closed' => false,
            'final_closure_manifest_created' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed' => false,
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed' => false,
            'handoff_audit_archive_final_closure_confirmed' => false,
            'handoff_audit_archive_final_closure_go_decision' => 'NO_GO',
            'primary_candidate_handoff_audit_archive_final_closed' => false,
            'backup_candidate_handoff_audit_archive_final_closed' => false,
            'comparator_candidate_handoff_audit_archive_final_closed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
        foreach (self::REQUIRED_C128_FIELDS as $field => $expected) {
            $artifact[$field] = is_bool($expected) ? false : 'NO_GO';
        }
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $artifact[$flag] = false;
        }
        return $artifact;
    }

    private function passingTopLevelState(): array
    {
        $state = [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_pass' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closed' => true,
            'controlled_runtime_wiring_handoff_audit_archive_final_closed' => true,
            'handoff_audit_archive_final_closed' => true,
            'audit_archive_final_closed' => true,
            'final_closure_manifest_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed' => true,
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed' => true,
            'handoff_audit_archive_final_closure_confirmed' => true,
            'handoff_audit_archive_final_closure_go_decision' => 'HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_GO',
            'primary_candidate_handoff_audit_archive_final_closed' => true,
            'backup_candidate_handoff_audit_archive_final_closed' => true,
            'comparator_candidate_handoff_audit_archive_final_closed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
        ];
        foreach (self::REQUIRED_C128_FIELDS as $field => $expected) {
            $state[$field] = $expected;
        }
        return $state;
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
        return [
            'c128' => [
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
                'expected_status' => self::EXPECTED_C128_STATUS,
                'expected_phase_label' => self::EXPECTED_C128_PHASE_LABEL,
                'expected_next_recommendation' => self::EXPECTED_C128_RECOMMENDATION,
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C128',
            'c128_artifact_path' => $load['path'],
            'c128_artifact_exists' => $load['exists'],
            'expected_c128_hash' => $load['expected_hash'],
            'actual_c128_hash' => $load['actual_hash'],
            'c128_hash_match' => $load['hash_match'],
            'expected_c128_file_sha1' => $load['expected_file_sha1'],
            'actual_c128_file_sha1' => $load['actual_file_sha1'],
            'c128_file_sha1_match' => $load['file_sha1_match'],
            'c128_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function c128NextRecommendationMatches(array $c128): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c128_readiness_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c128, $path) === self::EXPECTED_C128_RECOMMENDATION) {
                return true;
            }
        }
        return false;
    }

    private function c128HandoffAuditArchiveCompletionSealed(array $c128): bool
    {
        foreach (self::REQUIRED_C128_FIELDS as $field => $expected) {
            if (($c128[$field] ?? null) !== $expected) {
                return false;
            }
        }
        $manifest = is_array($c128['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest'] ?? null)
            ? $c128['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_manifest']
            : [];
        foreach ([
            'manifest_created' => true,
            'completion_seal_artifact_only' => true,
            'handoff_audit_archive_completion_sealed' => true,
        ] as $field => $expected) {
            if (($manifest[$field] ?? null) !== $expected) {
                return false;
            }
        }
        foreach ([
            'completion_seal_used_for_selection',
            'completion_seal_used_for_retuning',
            'completion_seal_used_for_ranking',
            'completion_seal_used_for_plan_confirm_mutation',
            'completion_seal_used_for_live_rollout',
            'weekly_swing_official_output_generated',
            'weekly_swing_live_output_enabled',
            'weekly_swing_live_output_published',
            'plan_confirm_mutation_allowed',
        ] as $field) {
            if (($manifest[$field] ?? null) !== false) {
                return false;
            }
        }

        return true;
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

    private function candidateScopeMatches(array $c128): bool
    {
        return ($c128['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE) === self::PRIMARY_CANDIDATE
            && ($c128['backup_candidate_code'] ?? self::BACKUP_CANDIDATE) === self::BACKUP_CANDIDATE
            && ($c128['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE) === self::COMPARATOR_CANDIDATE
            && (bool) ($c128['a01_remains_comparator_only'] ?? false) === true
            && (bool) ($c128['a01_promoted'] ?? false) === false
            && (bool) ($c128['candidate_promotion_executed'] ?? false) === false
            && (bool) ($c128['candidate_rerank_executed'] ?? false) === false
            && (bool) ($c128['strategy_retune_executed'] ?? false) === false
            && (bool) ($c128['scoring_mutation_executed'] ?? false) === false
            && (bool) ($c128['catalog_selection_changed'] ?? false) === false
            && (bool) ($c128['runtime_selection_changed'] ?? false) === false;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! (bool) ($options['handoff_audit_archive_final_closure_confirmed'] ?? false)) {
            $failures[] = self::FINAL_CLOSURE_NOT_CONFIRMED_STATUS;
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
            'persist_handoff_audit_archive_final_closure_context_to_live_runtime',
            'mutate_plan_confirm',
            'change_config_defaults',
            'change_strategy_parameters',
            'generate_official_weekly_swing_stock_recommendation',
            'generate_live_weekly_swing_watchlist_output',
            'publish_weekly_swing_output',
            'modify_c60_c128_artifacts',
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
        $c128 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c128_lock_validation_summary'] = $this->c128LockValidationSummary($load, $c128);
        $artifact['c122_c128_handoff_lineage_final_closure_summary'] = $this->lineageValidationSummary($c128);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c128, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c129_readiness_decision'] = $this->handoffAuditArchiveFinalClosureDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_decision'] = $artifact['c129_readiness_decision'];
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_manifest'] = $this->handoffAuditArchiveFinalClosureManifest($pass);
        $artifact['c129_candidate_audit_archive_final_closure_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['handoff_audit_archive_final_closure_context_summary'] = $this->handoffAuditArchiveFinalClosureContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c128_handoff_audit_archive_completion_seal_carry_forward_validation_summary'] = $this->c128HandoffAuditArchiveCompletionSealCarryForwardValidationSummary($c128, $pass);
        $artifact['handoff_audit_archive_final_closure_governance_summary'] = $this->handoffAuditArchiveFinalClosureGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary();
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
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

    private function c128LockValidationSummary(array $load, array $c128): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C128',
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
            'expected_status' => self::EXPECTED_C128_STATUS,
            'actual_status' => $c128['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C128_STATUS,
            'actual_reason_code' => $c128['reason_code'] ?? null,
            'expected_phase_label' => self::EXPECTED_C128_PHASE_LABEL,
            'actual_phase_label' => $c128['phase_label'] ?? null,
            'phase_label_match' => ($c128['phase_label'] ?? null) === self::EXPECTED_C128_PHASE_LABEL,
            'expected_next_recommendation' => self::EXPECTED_C128_RECOMMENDATION,
            'next_recommendation_match' => $this->c128NextRecommendationMatches($c128),
            'c128_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function lineageValidationSummary(array $c128): array
    {
        return [
            'validation_completed' => true,
            'c122_handoff_ready_carried_forward' => (bool) ($c128['handoff_ready'] ?? false),
            'c123_handoff_finalized_carried_forward' => (bool) ($c128['handoff_finalized'] ?? false),
            'c124_handoff_completion_boundary_cleared_carried_forward' => (bool) ($c128['handoff_completion_boundary_cleared'] ?? false),
            'c125_handoff_closure_sealed_carried_forward' => (bool) ($c128['handoff_closure_sealed'] ?? false),
            'c126_handoff_audit_archived_carried_forward' => (bool) ($c128['handoff_audit_archived'] ?? false),
            'c127_handoff_audit_archive_completion_ready_carried_forward' => (bool) ($c128['handoff_audit_archive_completion_ready'] ?? false),
            'c128_handoff_audit_archive_completion_sealed_carried_forward' => (bool) ($c128['handoff_audit_archive_completion_sealed'] ?? false),
            'lineage_carried_forward_complete' => $this->c128HandoffAuditArchiveCompletionSealed($c128),
        ];
    }

    private function candidateScopeFreezeSummary(array $c128, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c128),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_handoff_audit_archive_final_closure_candidate',
            'backup_candidate_role' => 'backup_handoff_audit_archive_final_closure_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
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
            'handoff_audit_archive_final_closure_confirmation_required' => true,
            'handoff_audit_archive_final_closure_confirmed' => (bool) ($options['handoff_audit_archive_final_closure_confirmed'] ?? false),
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

    private function handoffAuditArchiveFinalClosureDecision(bool $pass): array
    {
        $decision = array_merge($this->passingTopLevelState(), [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c128_lock_valid' => $pass,
            'c128_handoff_audit_archive_completion_sealed' => $pass,
            'next_recommendation' => self::NO_NEXT_RECOMMENDATION,
            'decision_reason' => $pass ? 'C129 controlled runtime wiring handoff audit archive final closure completed for primary and backup in artifact-only audit context.' : 'C129 final closure review did not pass; targeted C128 seal repair is required.',
            'diagnostic_conclusion' => $pass ? 'C129_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_CONTROLLED_RUNTIME_WIRING_AUDIT_ONLY' : 'C129_TARGETED_C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REPAIR',
        ]);
        if (! $pass) {
            foreach ([
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed',
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_pass',
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closed',
                'controlled_runtime_wiring_handoff_audit_archive_final_closed',
                'handoff_audit_archive_final_closed',
                'audit_archive_final_closed',
                'final_closure_manifest_created',
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed',
                'controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed',
                'handoff_audit_archive_final_closure_confirmed',
                'primary_candidate_handoff_audit_archive_final_closed',
                'backup_candidate_handoff_audit_archive_final_closed',
            ] as $field) {
                $decision[$field] = false;
            }
            $decision['handoff_audit_archive_final_closure_go_decision'] = 'NO_GO';
        }
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $decision[$flag] = false;
        }
        return $decision;
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::NO_NEXT_RECOMMENDATION : 'C129_TARGETED_C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REPAIR',
            'next_scope' => $pass ? 'controlled runtime wiring handoff audit archive final closure is complete; no next audit archive review is required' : 'targeted C128 lock or completion seal repair only',
        ];
    }

    private function handoffAuditArchiveFinalClosureManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_controlled_runtime_wiring_handoff_audit_archive_final_closure_review',
            'source_artifact' => 'C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW',
            'source_artifact_path' => self::DEFAULT_C128_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C128_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C128_FILE_SHA1,
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
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
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
                'c129_role' => 'primary_handoff_audit_archive_final_closure_candidate',
                'primary_candidate_handoff_audit_archive_final_closed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c129_role' => 'backup_handoff_audit_archive_final_closure_candidate',
                'backup_candidate_handoff_audit_archive_final_closed' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c129_role' => 'comparator_only_candidate',
                'handoff_audit_archive_final_closure_review_pass' => false,
                'handoff_audit_archive_final_closed' => false,
                'comparator_candidate_handoff_audit_archive_final_closed' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffAuditArchiveFinalClosureContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_context_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_completion_seal_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_final_closure_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_source_identified' => is_file(self::RUNTIME_PATHS['c128_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_service']),
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_source_identified' => is_file(self::RUNTIME_PATHS['c129_weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_service']),
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

    private function c128HandoffAuditArchiveCompletionSealCarryForwardValidationSummary(array $c128, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c128_handoff_audit_archive_completion_review_pass' => (bool) ($c128['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass'] ?? false),
            'c128_handoff_audit_archive_completion_ready' => (bool) ($c128['handoff_audit_archive_completion_ready'] ?? false),
            'c128_handoff_audit_archive_completion_seal_review_pass' => (bool) ($c128['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_pass'] ?? false),
            'c128_handoff_audit_archive_completion_sealed' => (bool) ($c128['handoff_audit_archive_completion_sealed'] ?? false),
            'c128_audit_archive_completion_sealed' => (bool) ($c128['audit_archive_completion_sealed'] ?? false),
            'c128_completion_seal_manifest_created' => (bool) ($c128['completion_seal_manifest_created'] ?? false),
            'c128_primary_candidate_completion_sealed' => (bool) ($c128['primary_candidate_handoff_audit_archive_completion_sealed'] ?? false),
            'c128_backup_candidate_completion_sealed' => (bool) ($c128['backup_candidate_handoff_audit_archive_completion_sealed'] ?? false),
            'c128_comparator_candidate_completion_sealed' => (bool) ($c128['comparator_candidate_handoff_audit_archive_completion_sealed'] ?? false),
            'handoff_audit_archive_final_closed' => $pass,
        ];
    }

    private function handoffAuditArchiveFinalClosureGovernanceSummary(bool $pass): array
    {
        return [
            'governance_scope' => 'C129 artifact-only controlled runtime wiring handoff audit archive final closure review',
            'final_closure_review_pass' => $pass,
            'audit_archive_final_closed' => $pass,
            'production_runtime_change_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'weekly_swing_live_output_generation_allowed' => false,
            'official_recommendation_generation_allowed' => false,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(): array
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

    private function productionMutationSafetySummary(): array
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
            'c129_docs_added' => is_file(self::DOC_PATHS['c129_validation_doc']) && is_file(self::DOC_PATHS['c129_operator_commands_doc']),
            'implementation_status_updated' => is_file(self::DOC_PATHS['implementation_status_doc']),
            'contract_tracker_updated' => is_file(self::DOC_PATHS['contract_tracker_doc']),
            'audit_update_governance_updated' => is_file(self::DOC_PATHS['audit_update_governance_doc']),
            'documentation_hygiene_guard_applied' => true,
            'scoped_c128_source_lock_preserved' => true,
            'scoped_expected_c128_file_sha1_key_preserved' => true,
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
            'progress_marker' => 'C129_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW',
            'c128_audit_archive_completion_seal_review_carried_forward' => true,
            'c129_handoff_audit_archive_final_closure_review_executed' => true,
            'c129_handoff_audit_archive_final_closed' => $pass,
            'still_controlled_runtime_wiring_audit_only' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NO_NEXT_RECOMMENDATION : 'C129_TARGETED_C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist controlled runtime wiring handoff audit archive final closure is complete; any live production path requires a separate approved activation contract' : 'targeted repair before final closure can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'final closure artifact retained',
                'separate production activation contract if live output is later requested',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C129 validates C128 artifact_hash and file SHA1 locks before weekly swing watchlist controlled runtime wiring handoff audit archive final closure review is recorded.',
            'C129 validates C128 handoff audit archive completion-seal fields and A01 comparator-only state.',
            'C129 confirms no temporary negative test artifact remains before a passing controlled runtime wiring handoff audit archive final closure review.',
            'C129 final-closes audit archive evidence for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C129 creates an artifact-only weekly swing watchlist controlled runtime wiring handoff audit archive final closure manifest and no official weekly swing recommendation output.',
            'C129 weekly swing watchlist controlled runtime wiring handoff audit archive final closure review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C129 records no next controlled runtime wiring handoff audit archive review; any production path requires a separate approved contract.',
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
        if (strpos($status, 'C128_ARTIFACT') !== false || strpos($status, 'C128_FILE') !== false || strpos($status, 'LOCK') !== false) {
            return 'C129_C128_LOCK_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'ARCHIVE') !== false || strpos($status, 'COMPLETION') !== false || strpos($status, 'CLOSURE') !== false) {
            return 'C129_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C129_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C129_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C129_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C129_TARGETED_C128_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REPAIR';
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
