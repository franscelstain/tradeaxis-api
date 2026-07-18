<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewService
{
    public const RUN_CODE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-73 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW';

    public const DEFAULT_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review.json';
    public const DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_HASH = '4de6d670e5e6d6990dd618e0e818e57a7f79716e';
    public const DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_FILE_SHA1 = '97E9057EE0E7A71BC7F74B019F16FE1D251A3157';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C162_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP_HANDOFF_AUDIT_ARCHIVE_CHAIN_CLOSED';
    private const EXPECTED_C162_PHASE_LABEL = 'PR-72 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';
    private const EXPECTED_C162_TERMINAL_RECOMMENDATION = 'NO_NEXT_C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED';
    private const NEXT_RECOMMENDATION = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW';

    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_PASSED_C162_HANDOFF_CLOSED_READY_FOR_POST_HANDOFF_ACTIVATION_READINESS_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const POST_HANDOFF_BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_POST_HANDOFF_BOUNDARY_CONFIRMATION_MISSING';
    private const C162_CHAIN_CLOSED_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_CHAIN_CLOSED_CONFIRMATION_MISSING';
    private const C162_TERMINAL_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_TERMINAL_NO_NEXT_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C162_LOCK_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT_LOCK_MISMATCH';
    private const C162_FILE_SHA1_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_FILE_SHA1_LOCK_MISMATCH';
    private const C162_CONVERT_FROM_JSON_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C162_STATUS_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_STATUS_MISMATCH';
    private const C162_PHASE_LABEL_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_PHASE_LABEL_MISMATCH';
    private const C162_TERMINAL_RECOMMENDATION_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_TERMINAL_RECOMMENDATION_MISMATCH';
    private const C162_FINAL_CLOSURE_STATE_INVALID_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C162_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_final_closure_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_final_closure_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_final_closure_review_pass',
        'production_live_runtime_plan_confirm_completion_handoff_audit_archive_final_closure_review_pass',
        'handoff_ready',
        'handoff_finalized',
        'handoff_completion_boundary_cleared',
        'handoff_closure_sealed',
        'handoff_audit_archived',
        'handoff_audit_archive_completion_ready',
        'handoff_audit_archive_completion_sealed',
        'handoff_audit_archive_final_closed',
        'handoff_audit_archive_final_closure_confirmed',
        'c162_handoff_audit_archive_completion_seal_complete_confirmed',
        'handoff_audit_archive_completion_sealed_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c162_handoff_audit_archive_completion_seal_lock_valid',
        'c162_plan_confirm_completion_handoff_audit_archive_completion_seal_valid',
        'c162_handoff_audit_archive_completion_seal_convert_from_json_pass',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest_created',
        'c162_handoff_audit_archive_final_closure_complete',
        'no_next_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_required',
        'weekly_swing_watchlist_plan_confirm_completion_result_reviewed',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'primary_candidate_handoff_audit_archive_final_closed',
        'backup_candidate_handoff_audit_archive_final_closed',
        'a01_remains_comparator_only',
        'c162_plan_confirm_completion_handoff_audit_archive_final_closure_review_only',
        'c162_controlled_completion_only',
        'c162_not_publication',
        'c162_not_unrestricted_publication',
        'c162_not_plan_confirm_mutation',
        'c162_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C162_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_handoff_audit_archive_final_closed',
        'temporary_negative_artifacts_remaining',
    ];

    private const PUBLICATION_AND_PLAN_GUARD_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c163-*post-handoff-boundary*-test.json',
        'storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-boundary-negative-*.json',
        'storage/app/watchlist/backtest/.tmp-c163-post-handoff-boundary-negative-*.json',
    ];

    public function execute(
        string $c162HandoffAuditArchiveFinalClosureArtifact = self::DEFAULT_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT,
        string $expectedC162HandoffAuditArchiveFinalClosureHash = self::DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_HASH,
        string $expectedC162HandoffAuditArchiveFinalClosureFileSha1 = self::DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c162HandoffAuditArchiveFinalClosureArtifact, $expectedC162HandoffAuditArchiveFinalClosureHash, $expectedC162HandoffAuditArchiveFinalClosureFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->rejected($artifact, self::C162_LOCK_MISMATCH_STATUS, 'C162 handoff audit archive final closure artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c162_handoff_audit_archive_final_closure_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C162_CONVERT_FROM_JSON_STATUS, 'C162 handoff audit archive final closure artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->rejected($artifact, self::C162_LOCK_MISMATCH_STATUS, 'C162 handoff audit archive final closure artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->rejected($artifact, self::C162_FILE_SHA1_MISMATCH_STATUS, 'C162 handoff audit archive final closure file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c162 = $load['payload'];
        if (($c162['status'] ?? null) !== self::EXPECTED_C162_STATUS || ($c162['reason_code'] ?? null) !== self::EXPECTED_C162_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_STATUS_MISMATCH_STATUS, 'C162 handoff audit archive final closure status/reason is not final closed.', $outputPath, $overwrite);
        }
        if (($c162['phase_label'] ?? null) !== self::EXPECTED_C162_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_PHASE_LABEL_MISMATCH_STATUS, 'C162 handoff audit archive final closure phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c162TerminalRecommendationMatches($c162)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_TERMINAL_RECOMMENDATION_MISMATCH_STATUS, 'C162 terminal no-next handoff audit archive recommendation mismatch.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($c162)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C162 final closure evidence has free publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c162FinalClosureStateValid($c162)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_FINAL_CLOSURE_STATE_INVALID_STATUS, 'C162 handoff audit archive final closure evidence is incomplete for post-handoff boundary review.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c162)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C162 handoff audit archive final closure candidate scope does not match locked post-handoff boundary scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C163 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_boundary_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::POST_HANDOFF_BOUNDARY_CONFIRMATION_MISSING_STATUS, 'C163 requires --post-handoff-boundary-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c162_handoff_audit_archive_chain_closed_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_CHAIN_CLOSED_CONFIRMATION_MISSING_STATUS, 'C163 requires --c162-handoff-audit-archive-chain-closed-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c162_terminal_no_next_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_TERMINAL_CONFIRMATION_MISSING_STATUS, 'C163 requires --c162-terminal-no-next-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C163 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C163 requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C163 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C163 opens a new post-handoff boundary after C162 handoff audit archive final closure. It confirms the closed handoff boundary and permits only a later post-handoff activation readiness review; it does not free-publish output, mutate PLAN/CONFIRM, or execute live PLAN/CONFIRM rollout.';
        $artifact['diagnostic_conclusion'] = 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_PASSED_REVIEW_ONLY_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::NEXT_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($load['payload'], $options));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-73',
            'internal_checkpoint' => 'C163',
            'topic_code' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW',
            'status' => 'C163_NOT_RUN',
            'reason_code' => 'C163_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass' => false,
            'post_handoff_boundary_confirmed' => false,
            'c162_handoff_audit_archive_chain_closed_confirmed' => false,
            'c162_terminal_no_next_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'c162_handoff_audit_archive_final_closure_lock_valid' => false,
            'c162_plan_confirm_completion_post_handoff_boundary_valid' => false,
            'c162_handoff_audit_archive_final_closure_convert_from_json_pass' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_review_allowed_next' => false,
            'c163_is_new_post_handoff_contract' => true,
            'c163_not_c162_handoff_audit_archive_continuation' => true,
            'c163_post_handoff_boundary_review_only' => true,
            'c163_controlled_completion_only' => true,
            'c163_not_publication' => true,
            'c163_not_unrestricted_publication' => true,
            'c163_not_plan_confirm_mutation' => true,
            'c163_not_live_plan_confirm_rollout' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => false,
            'a01_remains_comparator_only' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function passingTopLevelState(array $c162, array $options): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass' => true,
            'post_handoff_boundary_confirmed' => (bool) ($options['post_handoff_boundary_confirmed'] ?? false),
            'c162_handoff_audit_archive_chain_closed_confirmed' => (bool) ($options['c162_handoff_audit_archive_chain_closed_confirmed'] ?? false),
            'c162_terminal_no_next_confirmed' => (bool) ($options['c162_terminal_no_next_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'c162_handoff_audit_archive_final_closure_lock_valid' => true,
            'c162_plan_confirm_completion_post_handoff_boundary_valid' => true,
            'c162_handoff_audit_archive_final_closure_convert_from_json_pass' => true,
            'c162_handoff_audit_archive_final_closure_complete' => true,
            'no_next_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_required' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_review' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_review_allowed_next' => true,
            'controlled_completion_path' => (string) ($c162['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($c162['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($c162['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($c162['controlled_completion_record_count'] ?? 0),
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($c162['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($c162['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($c162['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($c162['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($c162['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($c162['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($c162['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c162['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c162['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c162['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c162 = is_array($load['payload']) ? $load['payload'] : [];
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary((array) ($options['temporary_negative_artifact_paths'] ?? []));
        $artifact['c162_handoff_audit_archive_final_closure_lock_validation_summary'] = $this->lockValidationSummary($load);
        $artifact['c162_handoff_audit_archive_final_closure_carry_forward_summary'] = $this->carryForwardSummary($c162);
        $artifact['plan_confirm_completion_post_handoff_boundary_guard_summary'] = $this->postHandoffBoundaryGuardSummary($c162, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c162);
        $artifact['c163_post_handoff_boundary_decision'] = $this->postHandoffBoundaryDecision($pass, $options);
        $artifact['next_plan_confirm_completion_post_handoff_decision'] = $this->nextPostHandoffDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_manifest'] = $this->postHandoffBoundaryManifest($c162, $pass, $options, $load);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_boundary_checklist'] = $this->postHandoffBoundaryChecklist($pass, $options);
        $artifact['c163_candidate_plan_confirm_completion_post_handoff_boundary_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($c162);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function operatorApprovalValidationSummary(array $options): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'post_handoff_boundary_confirmation_required' => true,
            'post_handoff_boundary_confirmed' => (bool) ($options['post_handoff_boundary_confirmed'] ?? false),
            'c162_handoff_audit_archive_chain_closed_confirmation_required' => true,
            'c162_handoff_audit_archive_chain_closed_confirmed' => (bool) ($options['c162_handoff_audit_archive_chain_closed_confirmed'] ?? false),
            'c162_terminal_no_next_confirmation_required' => true,
            'c162_terminal_no_next_confirmed' => (bool) ($options['c162_terminal_no_next_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
        ];
    }

    private function temporaryNegativeArtifactGuardSummary(array $paths): array
    {
        return [
            'validation_completed' => true,
            'temporary_negative_artifacts_remaining' => $paths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $paths === [],
            'temporary_negative_artifact_paths' => $paths,
            'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
    }

    private function lockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function carryForwardSummary(array $c162): array
    {
        return [
            'validation_completed' => true,
            'source_run_code' => (string) ($c162['run_code'] ?? ''),
            'source_status' => (string) ($c162['status'] ?? ''),
            'source_next_step_recommendation' => (string) ($c162['next_step_recommendation'] ?? ''),
            'handoff_ready' => (bool) ($c162['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($c162['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($c162['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => (bool) ($c162['handoff_closure_sealed'] ?? false),
            'handoff_audit_archived' => (bool) ($c162['handoff_audit_archived'] ?? false),
            'handoff_audit_archive_final_closed' => (bool) ($c162['handoff_audit_archive_final_closed'] ?? false),
            'c162_handoff_audit_archive_final_closure_complete' => (bool) ($c162['c162_handoff_audit_archive_final_closure_complete'] ?? false),
            'no_next_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_required' => (bool) ($c162['no_next_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_required'] ?? false),
            'controlled_completion_path' => (string) ($c162['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($c162['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($c162['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($c162['controlled_completion_record_count'] ?? 0),
        ];
    }

    private function postHandoffBoundaryGuardSummary(array $c162, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'source_c162_terminal_recommendation_matches' => $this->c162TerminalRecommendationMatches($c162),
            'source_c162_final_closure_state_valid' => $this->c162FinalClosureStateValid($c162),
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c162),
            'post_handoff_boundary_review_pass' => $pass,
            'post_handoff_boundary_artifact_only' => true,
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'post_handoff_activation_readiness_allowed_next' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $c162): array
    {
        return [
            'validation_completed' => true,
            'candidate_scope_matches' => $this->candidateScopeMatches($c162),
            'primary_candidate_code' => (string) ($c162['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($c162['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($c162['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'candidate_promotion_executed' => false,
            'a01_remains_comparator_only' => (bool) ($c162['a01_remains_comparator_only'] ?? false),
        ];
    }

    private function postHandoffBoundaryDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'post_handoff_boundary_confirmed' => (bool) ($options['post_handoff_boundary_confirmed'] ?? false),
            'c162_handoff_audit_archive_chain_closed_confirmed' => (bool) ($options['c162_handoff_audit_archive_chain_closed_confirmed'] ?? false),
            'c162_terminal_no_next_confirmed' => (bool) ($options['c162_terminal_no_next_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'post_handoff_boundary_go_decision' => $pass ? 'POST_HANDOFF_BOUNDARY_GO' : 'NO_GO',
            'decision_scope' => $pass
                ? 'C162 handoff audit archive chain is closed; C163 may proceed only to post-handoff activation readiness review.'
                : 'C163 post-handoff boundary did not pass.',
        ];
    }

    private function nextPostHandoffDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'next_scope' => $pass ? 'post-handoff activation readiness review only' : 'targeted C163 post-handoff boundary repair',
            'next_is_concrete' => $pass,
            'c163_post_handoff_boundary_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function postHandoffBoundaryManifest(array $c162, bool $pass, array $options, array $load): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_boundary_review',
            'source_artifact' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'c162_handoff_audit_archive_final_closure_complete' => (bool) ($c162['c162_handoff_audit_archive_final_closure_complete'] ?? false),
            'c162_terminal_recommendation' => (string) ($c162['next_step_recommendation'] ?? ''),
            'c162_terminal_no_next_confirmed' => (bool) ($options['c162_terminal_no_next_confirmed'] ?? false),
            'post_handoff_boundary_confirmed' => (bool) ($options['post_handoff_boundary_confirmed'] ?? false),
            'post_handoff_boundary_go_decision' => $pass ? 'POST_HANDOFF_BOUNDARY_GO' : 'NO_GO',
            'ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_path' => (string) ($c162['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($c162['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($c162['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($c162['controlled_completion_record_count'] ?? 0),
            'post_handoff_boundary_artifact_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'post_handoff_boundary_used_for_free_publication' => false,
            'post_handoff_boundary_used_for_plan_confirm_mutation' => false,
            'post_handoff_boundary_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function postHandoffBoundaryChecklist(bool $pass, array $options): array
    {
        return [
            'post_handoff_boundary_reviewed' => $pass,
            'c162_handoff_audit_archive_final_closure_source_lock_reviewed' => $pass,
            'c162_handoff_audit_archive_chain_closed_reviewed' => $pass,
            'c162_terminal_no_next_reviewed' => $pass,
            'post_handoff_boundary_required' => true,
            'post_handoff_boundary_confirmed' => (bool) ($options['post_handoff_boundary_confirmed'] ?? false),
            'c162_handoff_audit_archive_chain_closed_confirmed' => (bool) ($options['c162_handoff_audit_archive_chain_closed_confirmed'] ?? false),
            'c162_terminal_no_next_confirmed' => (bool) ($options['c162_terminal_no_next_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'post_handoff_boundary_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c163_post_handoff_boundary' => false,
            'post_handoff_activation_readiness_review_required_next' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'a01_remains_comparator_only' => true,
                'ready_for_plan_confirm_completion_post_handoff_activation_readiness_review' => false,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $c162): array
    {
        return [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c162),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'free_publication_allowed' => false,
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_artifact_path' => $load['path'],
            'source_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
            'documentation_update_required' => true,
            'operator_validation_commands_required' => true,
            'audit_tracker_update_required' => true,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'current' => self::RUN_CODE,
            'current_topic_number' => 'C163',
            'current_topic_complete' => $pass,
            'completed_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW',
            'source_topic_number' => 'C162',
            'source_topic_terminal' => self::EXPECTED_C162_TERMINAL_RECOMMENDATION,
            'next_topic_number' => $pass ? 'C163' : 'C163',
            'next_topic' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'topic_numbering_rule' => 'C162 stayed active until the handoff audit archive chain closed. C163 is now a new post-handoff contract.',
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'planned_next_action' => $pass ? 'Run C163 post-handoff activation readiness review under the same C163 topic; do not publish freely or mutate PLAN/CONFIRM.' : 'Resolve C163 rejection and rerun post-handoff boundary review.',
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'runtime_family' => 'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion',
            'review_layer' => 'post_handoff_boundary',
            'source_layer' => 'c162_handoff_audit_archive_final_closure',
            'next_layer' => 'post_handoff_activation_readiness_review',
            'candidate_policy' => 'E02 primary, B01 backup, A01 comparator only',
            'publication_policy' => 'controlled output remains unpublished and unrestricted publication stays locked',
            'plan_confirm_policy' => 'PLAN/CONFIRM remains unchanged and no live rollout is authorized',
        ];
    }

    private function c162FinalClosureStateValid(array $c162): bool
    {
        foreach (self::REQUIRED_C162_TRUE_FIELDS as $field) {
            if (($c162[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C162_FALSE_FIELDS as $field) {
            if (($c162[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($c162['handoff_audit_archive_final_closure_go_decision'] ?? null) !== 'HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_GO') {
            return false;
        }
        if ((int) ($c162['controlled_completion_record_count'] ?? 0) !== 2) {
            return false;
        }
        if (trim((string) ($c162['controlled_completion_hash'] ?? '')) === '' || trim((string) ($c162['controlled_completion_file_sha1'] ?? '')) === '') {
            return false;
        }
        if ($this->valueAt($c162, ['c162_handoff_audit_archive_final_closure_decision', 'review_valid']) !== true) {
            return false;
        }
        if ($this->valueAt($c162, ['c162_handoff_audit_archive_final_closure_decision', 'handoff_audit_archive_final_closed']) !== true) {
            return false;
        }
        if ($this->valueAt($c162, ['c162_handoff_audit_archive_final_closure_decision', 'handoff_audit_archive_final_closure_go_decision']) !== 'HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_GO') {
            return false;
        }
        if ($this->valueAt($c162, ['next_plan_confirm_completion_handoff_audit_archive_decision', 'c162_handoff_audit_archive_final_closure_complete']) !== true) {
            return false;
        }
        if ($this->valueAt($c162, ['next_plan_confirm_completion_handoff_audit_archive_decision', 'free_publication_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($c162, ['next_plan_confirm_completion_handoff_audit_archive_decision', 'plan_confirm_mutation_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($c162, ['next_plan_confirm_completion_handoff_audit_archive_decision', 'live_plan_confirm_rollout_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'handoff_audit_archive_chain_closed']) !== true) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'final_closure_artifact_only']) !== true) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'final_closure_used_for_free_publication']) !== false) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'final_closure_used_for_plan_confirm_mutation']) !== false) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'final_closure_used_for_live_plan_confirm_rollout']) !== false) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'official_weekly_swing_stock_recommendations']) !== []) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_checklist', 'artifact_only']) !== true) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_checklist', 'no_next_c162_handoff_audit_archive_review_required']) !== true) {
            return false;
        }
        if ($this->valueAt($c162, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_checklist', 'weekly_swing_stock_recommendation_free_published_in_c162_handoff_audit_archive_final_closure']) !== false) {
            return false;
        }

        return true;
    }

    private function c162TerminalRecommendationMatches(array $c162): bool
    {
        return ($c162['next_step_recommendation'] ?? null) === self::EXPECTED_C162_TERMINAL_RECOMMENDATION
            && $this->valueAt($c162, ['next_plan_confirm_completion_handoff_audit_archive_decision', 'next_recommendation']) === self::EXPECTED_C162_TERMINAL_RECOMMENDATION
            && $this->valueAt($c162, ['planned_next_summary', 'planned_next_review']) === self::EXPECTED_C162_TERMINAL_RECOMMENDATION;
    }

    private function publicationAndPlanGuardClean(array $c162): bool
    {
        foreach (self::PUBLICATION_AND_PLAN_GUARD_FALSE_FIELDS as $field) {
            if (($c162[$field] ?? null) !== false) {
                return false;
            }
        }
        foreach ([
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'final_closure_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'final_closure_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_manifest', 'final_closure_used_for_live_plan_confirm_rollout'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_official_output_published'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_unrestricted_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'plan_confirm_mutated'],
            ['publication_plan_confirm_safety_summary', 'live_plan_confirm_rollout_executed'],
        ] as $path) {
            if ($this->valueAt($c162, $path) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $c162): bool
    {
        return ($c162['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c162['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c162['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c162['primary_candidate_handoff_audit_archive_final_closed'] ?? null) === true
            && ($c162['backup_candidate_handoff_audit_archive_final_closed'] ?? null) === true
            && ($c162['comparator_candidate_handoff_audit_archive_final_closed'] ?? null) === false
            && ($c162['a01_remains_comparator_only'] ?? null) === true
            && ($c162['a01_promoted'] ?? false) === false;
    }

    private function failureAttributionSummary(array $failures): array
    {
        return [
            'failure_count' => count($failures),
            'failures' => $failures,
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_OR_OPERATOR_CONFIRMATION',
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $raw = $exists ? (string) file_get_contents($path) : '';
        $payload = $exists ? json_decode($raw, true) : null;
        $decoded = is_array($payload) && json_last_error() === JSON_ERROR_NONE;
        $duplicateKeys = $decoded ? $this->caseInsensitiveDuplicateKeys($payload) : [];
        $actualHash = is_array($payload) ? (string) ($payload['artifact_hash'] ?? '') : '';
        $actualFileSha1 = $exists ? strtoupper(sha1($raw)) : '';

        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $actualHash !== '' && hash_equals($expectedHash, $actualHash),
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $actualFileSha1 !== '' && strtoupper($expectedFileSha1) === $actualFileSha1,
            'convert_from_json_pass' => $decoded && $duplicateKeys === [],
            'case_insensitive_duplicate_keys' => $duplicateKeys,
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c162_plan_confirm_completion_handoff_audit_archive_final_closure' => [
                'artifact_path' => $load['path'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'convert_from_json_pass' => $load['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c162_handoff_audit_archive_final_closure_hash' => $load['expected_hash'],
            'actual_c162_handoff_audit_archive_final_closure_hash' => $load['actual_hash'],
            'c162_handoff_audit_archive_final_closure_hash_match' => $load['hash_match'],
            'expected_c162_handoff_audit_archive_final_closure_file_sha1' => $load['expected_file_sha1'],
            'actual_c162_handoff_audit_archive_final_closure_file_sha1' => $load['actual_file_sha1'],
            'c162_handoff_audit_archive_final_closure_file_sha1_match' => $load['file_sha1_match'],
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

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REJECTED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeArtifact($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): void
    {
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException('Output artifact already exists: '.$path);
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash'], $artifact['artifact_path']);
        $this->sortRecursive($artifact);

        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function sortRecursive(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                $this->sortRecursive($item);
            }
        }
        unset($item);

        if (! array_is_list($value)) {
            ksort($value);
        }
    }

    private function caseInsensitiveDuplicateKeys(array $payload, string $prefix = ''): array
    {
        $duplicates = [];
        if (! array_is_list($payload)) {
            $seen = [];
            foreach (array_keys($payload) as $key) {
                $lower = strtolower((string) $key);
                $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                if (isset($seen[$lower])) {
                    $duplicates[] = $seen[$lower].' / '.$path;
                } else {
                    $seen[$lower] = $path;
                }
            }
        }

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $childPrefix = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                $duplicates = array_merge($duplicates, $this->caseInsensitiveDuplicateKeys($value, $childPrefix));
            }
        }

        return $duplicates;
    }

    private function valueAt(array $source, array $path)
    {
        $current = $source;
        foreach ($path as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
