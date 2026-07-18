<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveCompletionSealReviewService
{
    public const RUN_CODE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    public const PHASE_LABEL = 'PR-71 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    public const ARTIFACT_TYPE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';

    public const DEFAULT_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_ARTIFACT = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-completion-review.json';
    public const DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_HASH = '77f23211f2c59c9d23d13e5231b56a3869a0dd00';
    public const DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_FILE_SHA1 = '5A9CF8A070E19747E6BEB885D7E5057D5900E8EC';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-completion-seal-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    private const EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_PHASE_LABEL = 'PR-70 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    private const EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_RECOMMENDATION = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';

    private const PASS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_SEALED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_CONFIRMATION_MISSING';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_COMPLETE_CONFIRMATION_MISSING';
    private const HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_LOCK_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_ARTIFACT_LOCK_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_FILE_SHA1_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_FILE_SHA1_LOCK_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_CONVERT_FROM_JSON_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATUS_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATUS_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_PHASE_LABEL_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_PHASE_LABEL_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_NEXT_RECOMMENDATION_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATE_INVALID_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_pass',
        'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_pass',
        'handoff_ready',
        'handoff_finalized',
        'handoff_completion_boundary_cleared',
        'handoff_closure_sealed',
        'handoff_audit_archived',
        'handoff_audit_archive_completion_ready',
        'handoff_audit_archive_completion_confirmed',
        'c162_handoff_audit_archive_complete_confirmed',
        'handoff_audit_archived_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c162_handoff_audit_archive_lock_valid',
        'c162_plan_confirm_completion_handoff_audit_archive_valid',
        'c162_handoff_audit_archive_convert_from_json_pass',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_review',
        'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest_created',
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
        'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review',
        'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review',
        'a01_remains_comparator_only',
        'c162_plan_confirm_completion_handoff_audit_archive_completion_review_only',
        'c162_controlled_completion_only',
        'c162_not_publication',
        'c162_not_unrestricted_publication',
        'c162_not_plan_confirm_mutation',
        'c162_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review',
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
        'storage/app/watchlist/backtest/c162-*handoff-audit-archive-completion-seal*-test.json',
        'storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-completion-seal-negative-*.json',
        'storage/app/watchlist/backtest/.tmp-c162-handoff-audit-archive-completion-seal-negative-*.json',
    ];

    public function execute(
        string $c162HandoffAuditArchiveCompletionArtifact = self::DEFAULT_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_ARTIFACT,
        string $expectedC162HandoffAuditArchiveCompletionHash = self::DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_HASH,
        string $expectedC162HandoffAuditArchiveCompletionFileSha1 = self::DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c162HandoffAuditArchiveCompletionArtifact, $expectedC162HandoffAuditArchiveCompletionHash, $expectedC162HandoffAuditArchiveCompletionFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->rejected($artifact, self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_LOCK_MISMATCH_STATUS, 'C162 handoff audit archive completion artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c162_handoff_audit_archive_completion_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_CONVERT_FROM_JSON_STATUS, 'C162 handoff audit archive completion artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->rejected($artifact, self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_LOCK_MISMATCH_STATUS, 'C162 handoff audit archive completion artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->rejected($artifact, self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_FILE_SHA1_MISMATCH_STATUS, 'C162 handoff audit archive completion file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $completion = $load['payload'];
        if (($completion['status'] ?? null) !== self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATUS || ($completion['reason_code'] ?? null) !== self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATUS_MISMATCH_STATUS, 'C162 handoff audit archive completion status/reason is not completion-seal-ready.', $outputPath, $overwrite);
        }
        if (($completion['phase_label'] ?? null) !== self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_PHASE_LABEL_MISMATCH_STATUS, 'C162 handoff audit archive completion phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c162NextRecommendationMatches($completion)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C162 handoff audit archive completion next recommendation is not completion seal review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($completion)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C162 audit archive completion evidence has free publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c162HandoffAuditArchiveCompletionStateValid($completion)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_STATE_INVALID_STATUS, 'C162 handoff audit archive completion evidence is incomplete for completion seal review.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($completion)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C162 handoff audit archive completion candidate scope does not match locked completion seal scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C162 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_CONFIRMATION_MISSING_STATUS, 'C162 requires --handoff-audit-archive-completion-seal-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c162_handoff_audit_archive_completion_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C162 requires --c162-handoff-audit-archive-completion-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_audit_archive_completion_ready_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_CONFIRMATION_MISSING_STATUS, 'C162 requires --handoff-audit-archive-completion-ready-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C162 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C162 requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C162 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C162 seals the PLAN/CONFIRM completion handoff audit archive completion package for E02 primary and B01 backup. This remains artifact-only, with no PLAN/CONFIRM mutation, free publication, unrestricted publication, or live plan-confirm rollout.';
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_READY_FOR_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-71',
            'internal_checkpoint' => 'C162',
            'topic_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW',
            'status' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_NOT_RUN',
            'reason_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_pass' => false,
            'next_step_recommendation' => self::RUN_CODE,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $completion = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryNegativePaths = array_values((array) ($options['temporary_negative_artifact_paths'] ?? []));
        $artifact = array_merge($artifact, [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_pass' => $pass,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_pass' => $pass,
            'handoff_ready' => (bool) ($completion['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($completion['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($completion['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => (bool) ($completion['handoff_closure_sealed'] ?? false),
            'handoff_audit_archived' => (bool) ($completion['handoff_audit_archived'] ?? false),
            'handoff_audit_archive_completion_ready' => (bool) ($completion['handoff_audit_archive_completion_ready'] ?? false),
            'handoff_audit_archive_completion_sealed' => $pass,
            'handoff_audit_archive_completion_seal_confirmed' => (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false),
            'handoff_audit_archive_completion_seal_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_GO' : 'NO_GO',
            'c162_handoff_audit_archive_completion_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_completion_complete_confirmed'] ?? false),
            'handoff_audit_archive_completion_ready_confirmed' => (bool) ($options['handoff_audit_archive_completion_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'c162_handoff_audit_archive_completion_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
            'c162_plan_confirm_completion_handoff_audit_archive_completion_valid' => $this->c162HandoffAuditArchiveCompletionStateValid($completion),
            'c162_handoff_audit_archive_completion_convert_from_json_pass' => $load['convert_from_json_pass'],
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_review' => $pass,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_final_closure_review_allowed_next' => $pass,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_review_allowed_next' => $pass,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_manifest_created' => true,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($completion['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($completion['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($completion['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($completion['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'controlled_completion_path' => (string) ($completion['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($completion['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($completion['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($completion['controlled_completion_record_count'] ?? 0),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($completion['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($completion['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($completion['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($completion['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($completion['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($completion['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'primary_candidate_code' => (string) ($completion['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($completion['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($completion['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => (bool) ($completion['primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review'] ?? false),
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => (bool) ($completion['backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review'] ?? false),
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review' => false,
            'a01_remains_comparator_only' => (bool) ($completion['a01_remains_comparator_only'] ?? false),
            'c162_plan_confirm_completion_handoff_audit_archive_completion_seal_review_only' => true,
            'c162_controlled_completion_only' => true,
            'c162_not_publication' => true,
            'c162_not_unrestricted_publication' => true,
            'c162_not_plan_confirm_mutation' => true,
            'c162_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => $temporaryNegativePaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryNegativePaths === [],
            'temporary_negative_artifact_paths' => $temporaryNegativePaths,
        ]);

        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c162_handoff_audit_archive_completion_lock_validation_summary'] = $this->lockValidationSummary($load);
        $artifact['c162_plan_confirm_completion_handoff_audit_archive_completion_carry_forward_summary'] = $this->carryForwardSummary($completion);
        $artifact['plan_confirm_completion_handoff_audit_archive_completion_seal_guard_summary'] = $this->completionSealGuardSummary($completion, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($completion);
        $artifact['c162_handoff_audit_archive_completion_seal_decision'] = $this->handoffAuditArchiveCompletionSealDecision($pass, $options);
        $artifact['next_plan_confirm_completion_handoff_audit_archive_final_closure_decision'] = $this->nextHandoffAuditArchiveFinalClosureDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_manifest'] = $this->handoffAuditArchiveCompletionSealManifest($completion, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_checklist'] = $this->handoffAuditArchiveCompletionSealChecklist($pass, $options);
        $artifact['c162_candidate_plan_confirm_completion_handoff_audit_archive_completion_seal_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($completion);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : ['C162_COMPLETION_SEAL_REVIEW_NOT_PASSED']);

        return $artifact;
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_pass' => true,
            'handoff_audit_archive_completion_sealed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_review' => true,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_final_closure_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_manifest_created' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
        ];
    }

    private function c162HandoffAuditArchiveCompletionStateValid(array $completion): bool
    {
        if (($completion['handoff_audit_archive_completion_go_decision'] ?? null) !== 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO') {
            return false;
        }
        if ((int) ($completion['controlled_completion_record_count'] ?? 0) !== 2) {
            return false;
        }
        foreach (['controlled_completion_path', 'controlled_completion_hash', 'controlled_completion_file_sha1'] as $field) {
            if (trim((string) ($completion[$field] ?? '')) === '') {
                return false;
            }
        }
        foreach (self::REQUIRED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_TRUE_FIELDS as $field) {
            if (! (bool) ($completion[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_FALSE_FIELDS as $field) {
            if ((bool) ($completion[$field] ?? false)) {
                return false;
            }
        }

        return $this->nestedCompletionDecisionValid($completion)
            && $this->nestedCompletionManifestValid($completion)
            && $this->nestedCompletionChecklistValid($completion);
    }

    private function nestedCompletionDecisionValid(array $completion): bool
    {
        return (bool) $this->valueAt($completion, ['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision', 'review_valid'])
            && $this->valueAt($completion, ['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision', 'next_recommendation']) === self::RUN_CODE
            && (bool) $this->valueAt($completion, ['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision', 'topic_stage_advances_within_c162_handoff_after_audit_archive_completion'])
            && (bool) $this->valueAt($completion, ['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision', 'c162_handoff_audit_archive_completion_complete'])
            && ! (bool) $this->valueAt($completion, ['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision', 'free_publication_allowed_next'])
            && ! (bool) $this->valueAt($completion, ['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision', 'plan_confirm_mutation_allowed_next'])
            && ! (bool) $this->valueAt($completion, ['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision', 'live_plan_confirm_rollout_allowed_next'])
            && (bool) $this->valueAt($completion, ['c162_handoff_audit_archive_completion_decision', 'review_valid'])
            && (bool) $this->valueAt($completion, ['c162_handoff_audit_archive_completion_decision', 'handoff_audit_archive_completion_ready'])
            && $this->valueAt($completion, ['c162_handoff_audit_archive_completion_decision', 'handoff_audit_archive_completion_go_decision']) === 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO';
    }

    private function nestedCompletionManifestValid(array $completion): bool
    {
        return (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest', 'ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review'])
            && (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest', 'handoff_audit_archive_completion_artifact_only'])
            && ! (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest', 'handoff_audit_archive_completion_used_for_free_publication'])
            && ! (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest', 'handoff_audit_archive_completion_used_for_plan_confirm_mutation'])
            && ! (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest', 'handoff_audit_archive_completion_used_for_live_plan_confirm_rollout']);
    }

    private function nestedCompletionChecklistValid(array $completion): bool
    {
        return (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_checklist', 'artifact_only'])
            && ! (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_checklist', 'weekly_swing_stock_recommendation_free_published_in_c162_handoff_audit_archive_completion']);
    }

    private function c162NextRecommendationMatches(array $completion): bool
    {
        return ($completion['next_step_recommendation'] ?? null) === self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_NEXT_RECOMMENDATION
            && $this->valueAt($completion, ['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision', 'next_recommendation']) === self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_NEXT_RECOMMENDATION
            && $this->valueAt($completion, ['planned_next_summary', 'planned_next_review']) === self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_NEXT_RECOMMENDATION;
    }

    private function publicationAndPlanGuardClean(array $completion): bool
    {
        foreach (self::PUBLICATION_AND_PLAN_GUARD_FALSE_FIELDS as $field) {
            if ((bool) ($completion[$field] ?? false)) {
                return false;
            }
        }

        return ! (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest', 'handoff_audit_archive_completion_used_for_free_publication'])
            && ! (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest', 'handoff_audit_archive_completion_used_for_plan_confirm_mutation'])
            && ! (bool) $this->valueAt($completion, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest', 'handoff_audit_archive_completion_used_for_live_plan_confirm_rollout']);
    }

    private function candidateScopeMatches(array $completion): bool
    {
        return ($completion['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE) === self::PRIMARY_CANDIDATE
            && ($completion['backup_candidate_code'] ?? self::BACKUP_CANDIDATE) === self::BACKUP_CANDIDATE
            && ($completion['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE) === self::COMPARATOR_CANDIDATE
            && ! (bool) ($completion['a01_promoted'] ?? false)
            && (bool) ($completion['a01_remains_comparator_only'] ?? false)
            && (bool) ($completion['primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review'] ?? false)
            && (bool) ($completion['backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review'] ?? false)
            && ! (bool) ($completion['comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review'] ?? false);
    }

    private function operatorApprovalValidationSummary(array $options): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'handoff_audit_archive_completion_seal_confirmation_required' => true,
            'handoff_audit_archive_completion_seal_confirmed' => (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false),
            'c162_handoff_audit_archive_completion_complete_confirmation_required' => true,
            'c162_handoff_audit_archive_completion_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_completion_complete_confirmed'] ?? false),
            'handoff_audit_archive_completion_ready_confirmation_required' => true,
            'handoff_audit_archive_completion_ready_confirmed' => (bool) ($options['handoff_audit_archive_completion_ready_confirmed'] ?? false),
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

    private function carryForwardSummary(array $completion): array
    {
        return [
            'validation_completed' => true,
            'source_run_code' => (string) ($completion['run_code'] ?? ''),
            'source_status' => (string) ($completion['status'] ?? ''),
            'handoff_ready' => (bool) ($completion['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($completion['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($completion['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => (bool) ($completion['handoff_closure_sealed'] ?? false),
            'handoff_audit_archived' => (bool) ($completion['handoff_audit_archived'] ?? false),
            'handoff_audit_archive_completion_ready' => (bool) ($completion['handoff_audit_archive_completion_ready'] ?? false),
            'controlled_completion_path' => (string) ($completion['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($completion['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($completion['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($completion['controlled_completion_record_count'] ?? 0),
        ];
    }

    private function completionSealGuardSummary(array $completion, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'source_completion_state_valid' => $this->c162HandoffAuditArchiveCompletionStateValid($completion),
            'completion_seal_review_pass' => $pass,
            'completion_seal_artifact_only' => true,
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'ready_for_handoff_audit_archive_final_closure_review' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $completion): array
    {
        return [
            'validation_completed' => true,
            'candidate_scope_matches' => $this->candidateScopeMatches($completion),
            'primary_candidate_code' => (string) ($completion['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($completion['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($completion['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'candidate_promotion_executed' => false,
            'a01_remains_comparator_only' => (bool) ($completion['a01_remains_comparator_only'] ?? false),
        ];
    }

    private function handoffAuditArchiveCompletionSealDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'handoff_audit_archive_completion_sealed' => $pass,
            'handoff_audit_archive_completion_seal_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_GO' : 'NO_GO',
            'handoff_audit_archive_completion_seal_confirmed' => (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false),
            'c162_handoff_audit_archive_completion_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_completion_complete_confirmed'] ?? false),
            'handoff_audit_archive_completion_ready_confirmed' => (bool) ($options['handoff_audit_archive_completion_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'decision_scope' => $pass
                ? 'C162 PLAN/CONFIRM completion handoff audit archive completion sealed; final closure review may start next within C162'
                : 'C162 PLAN/CONFIRM completion handoff audit archive completion seal did not pass',
        ];
    }

    private function nextHandoffAuditArchiveFinalClosureDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_RECOMMENDATION : null,
            'next_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff audit archive final closure review only; no PLAN/CONFIRM mutation, free publication, unrestricted publication, or live rollout is authorized by completion seal' : null,
            'next_is_concrete' => $pass,
            'next_requires_locked_c162_handoff_audit_archive_completion_seal_artifact' => $pass,
            'topic_stage_advances_within_c162_handoff_after_audit_archive_completion_seal' => $pass,
            'c162_handoff_audit_archive_completion_seal_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function handoffAuditArchiveCompletionSealManifest(array $completion, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_handoff_audit_archive_completion_seal_review',
            'source_artifact' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW',
            'source_artifact_path' => self::DEFAULT_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_ARTIFACT,
            'source_artifact_hash' => $completion['artifact_hash'] ?? null,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_FILE_SHA1,
            'handoff_ready' => (bool) ($completion['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($completion['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($completion['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => (bool) ($completion['handoff_closure_sealed'] ?? false),
            'handoff_audit_archived' => (bool) ($completion['handoff_audit_archived'] ?? false),
            'handoff_audit_archive_completion_ready' => (bool) ($completion['handoff_audit_archive_completion_ready'] ?? false),
            'handoff_audit_archive_completion_sealed' => $pass,
            'handoff_audit_archive_completion_seal_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_GO' : 'NO_GO',
            'handoff_audit_archive_completion_seal_confirmed' => (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false),
            'c162_handoff_audit_archive_completion_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_completion_complete_confirmed'] ?? false),
            'handoff_audit_archive_completion_ready_confirmed' => (bool) ($options['handoff_audit_archive_completion_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_path' => (string) ($completion['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($completion['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($completion['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($completion['controlled_completion_record_count'] ?? 0),
            'ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review' => $pass,
            'handoff_audit_archive_completion_seal_artifact_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'handoff_audit_archive_completion_seal_used_for_free_publication' => false,
            'handoff_audit_archive_completion_seal_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_completion_seal_used_for_live_plan_confirm_rollout' => false,
        ];
    }

    private function handoffAuditArchiveCompletionSealChecklist(bool $pass, array $options): array
    {
        return [
            'handoff_audit_archive_completion_seal_reviewed' => $pass,
            'c162_handoff_audit_archive_completion_source_lock_reviewed' => $pass,
            'c162_handoff_audit_archive_completion_complete_reviewed' => $pass,
            'handoff_audit_archive_completion_seal_required' => true,
            'handoff_audit_archive_completion_seal_confirmed' => (bool) ($options['handoff_audit_archive_completion_seal_confirmed'] ?? false),
            'c162_handoff_audit_archive_completion_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_completion_complete_confirmed'] ?? false),
            'handoff_audit_archive_completion_ready_confirmed' => (bool) ($options['handoff_audit_archive_completion_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'handoff_audit_archive_completion_seal_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c162_handoff_audit_archive_completion_seal' => false,
            'ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review' => $pass,
                'handoff_audit_archive_completion_sealed' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review' => $pass,
                'handoff_audit_archive_completion_sealed' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'a01_remains_comparator_only' => true,
                'ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review' => false,
                'handoff_audit_archive_completion_sealed' => false,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $completion): array
    {
        return [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($completion),
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
            'current_topic_number' => 'C162',
            'current_topic_complete' => $pass,
            'completed_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW',
            'next_topic_number' => 'C162',
            'next_topic' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_RECOMMENDATION : self::RUN_CODE,
            'topic_numbering_rule' => 'Keep topic number C162 until the complete HANDOFF sequence is closed.',
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_RECOMMENDATION : self::RUN_CODE,
            'planned_next_action' => $pass ? 'Lock C162 audit archive completion seal artifact and perform final closure review.' : 'Resolve C162 rejection and rerun audit archive completion seal.',
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'runtime_family' => 'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion',
            'review_layer' => 'handoff_audit_archive_completion_seal',
            'source_layer' => 'c162_handoff_audit_archive_completion',
            'next_layer' => 'c162_handoff_audit_archive_final_closure',
            'candidate_policy' => 'E02 primary, B01 backup, A01 comparator only',
            'publication_policy' => 'controlled output remains unpublished and unrestricted publication stays locked',
            'plan_confirm_policy' => 'PLAN/CONFIRM remains unchanged and no live rollout is authorized',
        ];
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
            'c162_plan_confirm_completion_handoff_audit_archive_completion' => [
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
            'expected_c162_handoff_audit_archive_completion_hash' => $load['expected_hash'],
            'actual_c162_handoff_audit_archive_completion_hash' => $load['actual_hash'],
            'c162_handoff_audit_archive_completion_hash_match' => $load['hash_match'],
            'expected_c162_handoff_audit_archive_completion_file_sha1' => $load['expected_file_sha1'],
            'actual_c162_handoff_audit_archive_completion_file_sha1' => $load['actual_file_sha1'],
            'c162_handoff_audit_archive_completion_file_sha1_match' => $load['file_sha1_match'],
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
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REJECTED';
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
