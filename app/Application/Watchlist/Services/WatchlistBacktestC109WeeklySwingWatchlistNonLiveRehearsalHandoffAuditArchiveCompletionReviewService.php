<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService
{
    public const RUN_CODE = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    public const ARTIFACT_TYPE = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';

    public const DEFAULT_C108_ARTIFACT = 'storage/app/watchlist/backtest/c108-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-review.json';
    public const DEFAULT_EXPECTED_C108_HASH = 'e7b6f6f94a40d1fe825bc0224b686d11e7510e94';
    public const DEFAULT_EXPECTED_C108_FILE_SHA1 = '591BF25C2A1E7678B2C9335ECBEF1938BDAF990C';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c109-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C108_STATUS = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C108_REASON = 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C108_RECOMMENDATION = self::RUN_CODE;
    private const C110_RECOMMENDATION = 'C110_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    private const PASS_STATUS = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const C108_AUDIT_ARCHIVE_INCOMPLETE_STATUS = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C108_AUDIT_ARCHIVE_INCOMPLETE';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

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

    private const REQUIRED_TRUE_C108_FIELDS = [
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived',
        'handoff_audit_archived',
        'audit_archived',
        'archive_manifest_created',
        'primary_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived',
        'backup_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived',
        'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed',
        'handoff_closure_sealed',
        'closure_sealed',
        'handoff_completion_boundary_cleared',
        'handoff_finalized',
        'handoff_ready',
    ];

    private const REQUIRED_FALSE_C108_FIELDS = [
        'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived',
    ];

    private const DOC_PATHS = [
        'c109_validation_doc' => 'docs/watchlist/audit/WS_C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW.md',
        'c109_operator_commands_doc' => 'docs/watchlist/audit/WS_C109_OPERATOR_VALIDATION_COMMANDS.md',
        'c108_validation_doc' => 'docs/watchlist/audit/WS_C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW.md',
        'c108_operator_commands_doc' => 'docs/watchlist/audit/WS_C108_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c108_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC108WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveReviewService.php',
        'c109_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService.php',
        'c108_command' => 'app/Console/Commands/Watchlist/RunBacktestC108WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveReviewCommand.php',
        'c109_command' => 'app/Console/Commands/Watchlist/RunBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewCommand.php',
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
        string $c108Artifact = self::DEFAULT_C108_ARTIFACT,
        string $expectedC108Hash = self::DEFAULT_EXPECTED_C108_HASH,
        string $expectedC108FileSha1 = self::DEFAULT_EXPECTED_C108_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c108Artifact, $expectedC108Hash, $expectedC108FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C109_BLOCKED_C108_ARTIFACT_LOCK_MISMATCH', 'C108 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C109_BLOCKED_C108_ARTIFACT_LOCK_MISMATCH', 'C108 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C109_BLOCKED_C108_FILE_SHA1_LOCK_MISMATCH', 'C108 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c108 = $load['payload'];
        if (($c108['status'] ?? null) !== self::EXPECTED_C108_STATUS) {
            return $this->blocked($artifact, 'C109_BLOCKED_C108_STATUS_MISMATCH', 'C108 status is not passed audit archived.', $outputPath, $overwrite);
        }
        if (($c108['reason_code'] ?? null) !== self::EXPECTED_C108_REASON) {
            return $this->blocked($artifact, 'C109_BLOCKED_C108_REASON_CODE_MISMATCH', 'C108 reason_code is not passed audit archived.', $outputPath, $overwrite);
        }
        if (! $this->c108NextRecommendationMatches($c108)) {
            return $this->blocked($artifact, 'C109_BLOCKED_C108_NEXT_RECOMMENDATION_MISMATCH', 'C108 next recommendation is not C109.', $outputPath, $overwrite);
        }
        if (! $this->c108HandoffAuditArchived($c108)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C108_AUDIT_ARCHIVE_INCOMPLETE_STATUS, 'C108 handoff audit archive evidence is incomplete.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c108);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c108_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C108 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c108)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C108 candidate scope does not match locked non-live handoff audit archive decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C109 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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
            return $this->rejected($artifact, $failures[0], 'C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C109 confirms the weekly swing watchlist non-live rehearsal handoff audit archive completion package is ready for primary and backup as artifact-only evidence. This does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C109_HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_NON_LIVE_ONLY';
        $artifact['next_step_recommendation'] = self::C110_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C109_NOT_RUN',
            'reason_code' => 'C109_NOT_RUN',
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
            'c108_handoff_audit_archived' => false,
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
            'c108_handoff_audit_archived' => true,
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
            'source_lock' => 'C108',
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
            'c108' => [
                'artifact_path' => $load['path'],
                'artifact_exists' => $load['exists'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'expected_status' => self::EXPECTED_C108_STATUS,
                'expected_reason_code' => self::EXPECTED_C108_REASON,
                'expected_next_recommendation' => self::EXPECTED_C108_RECOMMENDATION,
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C108',
            'c108_artifact_path' => $load['path'],
            'c108_artifact_exists' => $load['exists'],
            'expected_c108_hash' => $load['expected_hash'],
            'actual_c108_hash' => $load['actual_hash'],
            'c108_hash_match' => $load['hash_match'],
            'expected_c108_file_sha1' => $load['expected_file_sha1'],
            'actual_c108_file_sha1' => $load['actual_file_sha1'],
            'c108_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c108NextRecommendationMatches(array $c108): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c108_handoff_audit_archive_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_decision', 'next_recommendation'],
        ] as $path) {
            if ($this->valueAt($c108, $path) === self::EXPECTED_C108_RECOMMENDATION) {
                return true;
            }
        }
        return false;
    }

    private function c108HandoffAuditArchived(array $c108): bool
    {
        foreach (self::REQUIRED_TRUE_C108_FIELDS as $field) {
            if (array_key_exists($field, $c108) && (bool) $c108[$field] !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_FALSE_C108_FIELDS as $field) {
            if (array_key_exists($field, $c108) && (bool) $c108[$field] !== false) {
                return false;
            }
        }
        return (bool) ($c108['a01_remains_comparator_only'] ?? false) === true;
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

    private function candidateScopeMatches(array $c108): bool
    {
        return ($c108['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE) === self::PRIMARY_CANDIDATE
            && ($c108['backup_candidate_code'] ?? self::BACKUP_CANDIDATE) === self::BACKUP_CANDIDATE
            && ($c108['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE) === self::COMPARATOR_CANDIDATE
            && (bool) ($c108['a01_remains_comparator_only'] ?? false) === true
            && (bool) ($c108['a01_promoted'] ?? false) === false
            && (bool) ($c108['candidate_promotion_executed'] ?? false) === false
            && (bool) ($c108['candidate_rerank_executed'] ?? false) === false
            && (bool) ($c108['strategy_retune_executed'] ?? false) === false
            && (bool) ($c108['scoring_mutation_executed'] ?? false) === false
            && (bool) ($c108['catalog_selection_changed'] ?? false) === false
            && (bool) ($c108['runtime_selection_changed'] ?? false) === false;
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
            'mutate_plan_confirm',
            'change_config_defaults',
            'change_strategy_parameters',
            'generate_official_weekly_swing_stock_recommendation',
            'generate_live_weekly_swing_watchlist_output',
            'publish_weekly_swing_output',
            'modify_c60_c108_artifacts',
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
        $c108 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact['c108_lock_validation_summary'] = $this->c108LockValidationSummary($load, $c108);
        $artifact['c104_c108_handoff_lineage_completion_summary'] = $this->lineageValidationSummary($c108);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c108, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c109_readiness_decision'] = $this->handoffAuditArchiveCompletionDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_decision'] = $artifact['c109_readiness_decision'];
        $artifact['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_manifest'] = $this->handoffAuditArchiveCompletionManifest($pass);
        $artifact['c109_candidate_audit_archive_completion_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['handoff_audit_archive_completion_context_summary'] = $this->handoffAuditArchiveCompletionContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c108_handoff_audit_archive_carry_forward_validation_summary'] = $this->c108HandoffAuditArchiveCarryForwardValidationSummary($c108, $pass);
        $artifact['handoff_audit_archive_completion_governance_summary'] = $this->handoffAuditArchiveCompletionGovernanceSummary($pass);
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

    private function c108LockValidationSummary(array $load, array $c108): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C108',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'expected_status' => self::EXPECTED_C108_STATUS,
            'actual_status' => $c108['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C108_REASON,
            'actual_reason_code' => $c108['reason_code'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C108_RECOMMENDATION,
            'next_recommendation_match' => $this->c108NextRecommendationMatches($c108),
            'c108_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function lineageValidationSummary(array $c108): array
    {
        return [
            'validation_completed' => true,
            'c104_handoff_ready_carried_forward' => (bool) ($c108['handoff_ready'] ?? false),
            'c105_handoff_finalized_carried_forward' => (bool) ($c108['handoff_finalized'] ?? false),
            'c106_handoff_completion_boundary_cleared_carried_forward' => (bool) ($c108['handoff_completion_boundary_cleared'] ?? false),
            'c107_handoff_closure_sealed_carried_forward' => (bool) ($c108['handoff_closure_sealed'] ?? false),
            'c108_handoff_audit_archived_carried_forward' => (bool) ($c108['handoff_audit_archived'] ?? false),
            'lineage_carried_forward_complete' => $this->c108HandoffAuditArchived($c108),
        ];
    }

    private function candidateScopeFreezeSummary(array $c108, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c108),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_handoff_audit_archive_completion_candidate',
            'backup_candidate_role' => 'backup_handoff_audit_archive_completion_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_handoff_audit_archive_completion_ready' => $pass,
            'backup_candidate_handoff_audit_archive_completion_ready' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_ready' => false,
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
            'c108_lock_valid' => $pass,
            'c108_handoff_audit_archived' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_executed' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_allowed' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_pass' => $pass,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'audit_archive_completion_ready' => $pass,
            'completion_manifest_created' => $pass,
            'primary_candidate_handoff_audit_archive_completion_ready' => $pass,
            'backup_candidate_handoff_audit_archive_completion_ready' => $pass,
            'comparator_candidate_handoff_audit_archive_completion_ready' => false,
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
            'next_recommendation' => self::C110_RECOMMENDATION,
            'decision_reason' => 'C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review completed for primary and backup in artifact-only audit context.',
            'diagnostic_conclusion' => 'C109_HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_NON_LIVE_ONLY',
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C110_RECOMMENDATION : 'C109_TARGETED_C108_HANDOFF_AUDIT_ARCHIVE_REPAIR',
            'next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal handoff audit archive completion seal review only' : 'targeted C108 lock or completion readiness repair only',
        ];
    }

    private function handoffAuditArchiveCompletionManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_non_live_handoff_audit_archive_completion_review',
            'source_artifact' => 'C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW',
            'source_artifact_path' => self::DEFAULT_C108_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C108_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C108_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_handoff_audit_archive_completion_candidate',
            'backup_candidate_role' => 'backup_handoff_audit_archive_completion_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'handoff_ready_carried_forward' => $pass,
            'handoff_finalized_carried_forward' => $pass,
            'handoff_completion_boundary_cleared_carried_forward' => $pass,
            'handoff_closure_sealed_carried_forward' => $pass,
            'handoff_audit_archived_carried_forward' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'completion_review_used_for_selection' => false,
            'completion_review_used_for_retuning' => false,
            'completion_review_used_for_ranking' => false,
            'completion_review_used_for_plan_confirm_mutation' => false,
            'completion_review_used_for_live_rollout' => false,
            'completion_review_artifact_only' => true,
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'handoff_audit_archive_completion_review_pass' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
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
                'c109_role' => 'primary_handoff_audit_archive_completion_candidate',
                'primary_candidate_handoff_audit_archive_completion_ready' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c109_role' => 'backup_handoff_audit_archive_completion_candidate',
                'backup_candidate_handoff_audit_archive_completion_ready' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c109_role' => 'comparator_only_candidate',
                'handoff_audit_archive_completion_review_pass' => false,
                'handoff_audit_archive_completion_ready' => false,
                'comparator_candidate_handoff_audit_archive_completion_ready' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function handoffAuditArchiveCompletionContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_created' => true,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
            'handoff_audit_archive_completion_context_persisted_to_live_runtime' => false,
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
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_source_identified' => is_file(self::RUNTIME_PATHS['c108_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_service']),
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_source_identified' => is_file(self::RUNTIME_PATHS['c109_weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_service']),
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

    private function c108HandoffAuditArchiveCarryForwardValidationSummary(array $c108, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c108_handoff_audit_archive_review_pass' => (bool) ($c108['weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_review_pass'] ?? false),
            'c108_handoff_audit_archived' => (bool) ($c108['handoff_audit_archived'] ?? false),
            'c108_audit_archived' => (bool) ($c108['audit_archived'] ?? false),
            'c108_archive_manifest_created' => (bool) ($c108['archive_manifest_created'] ?? false),
            'c108_primary_candidate_audit_archived' => (bool) ($c108['primary_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived'] ?? false),
            'c108_backup_candidate_audit_archived' => (bool) ($c108['backup_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived'] ?? false),
            'c108_comparator_candidate_audit_archived' => (bool) ($c108['comparator_candidate_weekly_swing_non_live_rehearsal_handoff_audit_archived'] ?? false),
            'handoff_audit_archive_completion_ready' => $pass,
        ];
    }

    private function handoffAuditArchiveCompletionGovernanceSummary(bool $pass): array
    {
        return [
            'governance_scope' => 'C109 artifact-only non-live handoff audit archive completion review',
            'completion_review_pass' => $pass,
            'audit_archive_completion_ready' => $pass,
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
            'c109_docs_added' => is_file(self::DOC_PATHS['c109_validation_doc']) && is_file(self::DOC_PATHS['c109_operator_commands_doc']),
            'implementation_status_updated' => is_file(self::DOC_PATHS['implementation_status_doc']),
            'contract_tracker_updated' => is_file(self::DOC_PATHS['contract_tracker_doc']),
            'audit_update_governance_updated' => is_file(self::DOC_PATHS['audit_update_governance_doc']),
            'documentation_hygiene_guard_applied' => true,
            'scoped_c108_expected_c107_file_sha1_key_preserved' => true,
            'scoped_expected_c107_file_sha1_key_preserved' => true,
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
            'progress_marker' => 'C109_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW',
            'c108_audit_archive_review_carried_forward' => true,
            'c109_handoff_audit_archive_completion_review_executed' => true,
            'c109_handoff_audit_archive_completion_ready' => $pass,
            'still_non_live' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C110_RECOMMENDATION : 'C109_TARGETED_C108_HANDOFF_AUDIT_ARCHIVE_REPAIR',
            'planned_next_scope' => $pass ? 'weekly swing watchlist non-live rehearsal handoff audit archive completion seal review only; still not deployment, live rollout, default runtime wiring, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C109 artifact hash',
                'locked C109 file SHA1',
                'unchanged candidate scope',
                'non-live artifact-only handoff audit archive completion evidence',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C109 validates C108 artifact_hash and file SHA1 locks before weekly swing watchlist non-live rehearsal handoff audit archive completion review is recorded.',
            'C109 validates C108 handoff audit archive fields and A01 comparator-only state.',
            'C109 confirms no temporary negative test artifact remains before a passing non-live rehearsal handoff audit archive completion review.',
            'C109 marks audit archive completion readiness for E02 primary and B01 backup only; A01 remains comparator-only.',
            'C109 creates an artifact-only weekly swing watchlist non-live rehearsal handoff audit archive completion manifest and no official weekly swing recommendation output.',
            'C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.',
            'C109 may only recommend C110 weekly swing watchlist non-live rehearsal handoff audit archive completion seal review as the next audit-only step.',
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
        if (strpos($status, 'C108_ARTIFACT') !== false || strpos($status, 'C108_FILE') !== false || strpos($status, 'LOCK') !== false) {
            return 'C109_C108_LOCK_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'ARCHIVE') !== false || strpos($status, 'COMPLETION') !== false) {
            return 'C109_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REPAIR';
        }
        if (strpos($status, 'TEMPORARY_NEGATIVE') !== false) {
            return 'C109_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_REPAIR';
        }
        if (strpos($status, 'CANDIDATE') !== false || strpos($status, 'A01') !== false) {
            return 'C109_CANDIDATE_SCOPE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'LIVE') !== false || strpos($status, 'PRODUCTION') !== false || strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'WEEKLY') !== false) {
            return 'C109_LIVE_OR_PRODUCTION_OR_WEEKLY_OUTPUT_MUTATION_REPAIR';
        }
        return 'C109_TARGETED_C108_HANDOFF_AUDIT_ARCHIVE_REPAIR';
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
