<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveCompletionReviewService
{
    public const RUN_CODE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    public const PHASE_LABEL = 'PR-70 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    public const ARTIFACT_TYPE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';

    public const DEFAULT_C162_HANDOFF_AUDIT_ARCHIVE_ARTIFACT = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-review.json';
    public const DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_HASH = 'ad53366fea95f0fe89ea1643443f1254eb1acbd8';
    public const DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_FILE_SHA1 = '6047605B700ABC36C0BB33CCD25D6087C869CE39';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-completion-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW';
    private const EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_PHASE_LABEL = 'PR-69 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW';
    private const EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_RECOMMENDATION = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';

    private const PASS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const HANDOFF_AUDIT_ARCHIVE_COMPLETION_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_COMPLETION_CONFIRMATION_MISSING';
    private const C162_HANDOFF_AUDIT_ARCHIVE_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETE_CONFIRMATION_MISSING';
    private const HANDOFF_AUDIT_ARCHIVED_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C162_HANDOFF_AUDIT_ARCHIVE_LOCK_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_ARTIFACT_LOCK_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_FILE_SHA1_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FILE_SHA1_LOCK_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_CONVERT_FROM_JSON_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C162_HANDOFF_AUDIT_ARCHIVE_STATUS_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_STATUS_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_PHASE_LABEL_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_PHASE_LABEL_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_NEXT_RECOMMENDATION_MISMATCH';
    private const C162_HANDOFF_AUDIT_ARCHIVE_STATE_INVALID_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C162_HANDOFF_AUDIT_ARCHIVE_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_pass',
        'production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_pass',
        'handoff_ready',
        'handoff_finalized',
        'handoff_completion_boundary_cleared',
        'handoff_closure_sealed',
        'handoff_audit_archived',
        'handoff_audit_archive_confirmed',
        'c162_handoff_closure_seal_complete_confirmed',
        'handoff_closure_sealed_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c162_handoff_closure_seal_lock_valid',
        'c162_plan_confirm_completion_handoff_closure_seal_valid',
        'c162_handoff_closure_seal_convert_from_json_pass',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_review',
        'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_manifest_created',
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
        'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_review',
        'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_review',
        'a01_remains_comparator_only',
        'c162_plan_confirm_completion_handoff_audit_archive_review_only',
        'c162_controlled_completion_only',
        'c162_not_publication',
        'c162_not_unrestricted_publication',
        'c162_not_plan_confirm_mutation',
        'c162_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C162_HANDOFF_AUDIT_ARCHIVE_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c162-*handoff-audit-archive-completion*-test.json',
        'storage/app/watchlist/backtest/c162-*negative-*-test.json',
        'storage/app/watchlist/backtest/c162-*missing-*-test.json',
        'storage/app/watchlist/backtest/c162-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c162-*invalid-*-test.json',
        'storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-completion-negative-*.json',
    ];

    public function execute(
        string $c162HandoffAuditArchiveArtifact = self::DEFAULT_C162_HANDOFF_AUDIT_ARCHIVE_ARTIFACT,
        string $expectedC162HandoffAuditArchiveHash = self::DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_HASH,
        string $expectedC162HandoffAuditArchiveFileSha1 = self::DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c162HandoffAuditArchiveArtifact, $expectedC162HandoffAuditArchiveHash, $expectedC162HandoffAuditArchiveFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C162_HANDOFF_AUDIT_ARCHIVE_LOCK_MISMATCH_STATUS, 'C162 handoff audit archive artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c162_handoff_audit_archive_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C162_HANDOFF_AUDIT_ARCHIVE_CONVERT_FROM_JSON_STATUS, 'C162 handoff audit archive artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C162_HANDOFF_AUDIT_ARCHIVE_LOCK_MISMATCH_STATUS, 'C162 handoff audit archive artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C162_HANDOFF_AUDIT_ARCHIVE_FILE_SHA1_MISMATCH_STATUS, 'C162 handoff audit archive file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $auditArchive = $load['payload'];
        if (($auditArchive['status'] ?? null) !== self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_STATUS || ($auditArchive['reason_code'] ?? null) !== self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_STATUS_MISMATCH_STATUS, 'C162 handoff audit archive status/reason is not audit-archive-completion-ready.', $outputPath, $overwrite);
        }
        if (($auditArchive['phase_label'] ?? null) !== self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_PHASE_LABEL_MISMATCH_STATUS, 'C162 handoff audit archive phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c162NextRecommendationMatches($auditArchive)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C162 handoff audit archive next recommendation is not C162 handoff audit archive completion.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($auditArchive)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C162 audit archive evidence has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c162HandoffAuditArchiveStateValid($auditArchive)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_STATE_INVALID_STATUS, 'C162 handoff audit archive evidence is incomplete for C162 handoff audit archive completion.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($auditArchive)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C162 handoff audit archive candidate scope does not match locked handoff audit archive completion scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C162 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_audit_archive_completion_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_AUDIT_ARCHIVE_COMPLETION_CONFIRMATION_MISSING_STATUS, 'C162 requires --handoff-audit-archive-completion-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c162_handoff_audit_archive_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C162 requires --c162-handoff-audit-archive-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_audit_archived_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_AUDIT_ARCHIVED_CONFIRMATION_MISSING_STATUS, 'C162 requires --handoff-audit-archived-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C162 confirms the PLAN/CONFIRM completion handoff audit archive completion package for E02 primary and B01 backup. This remains controlled and artifact-backed, with no PLAN/CONFIRM mutation, live rollout, free publication, or unrestricted publication.';
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_FOR_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-70',
            'internal_checkpoint' => 'C162',
            'topic_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW',
            'status' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_NOT_RUN',
            'reason_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_pass' => false,
            'handoff_ready' => false,
            'handoff_finalized' => false,
            'handoff_completion_boundary_cleared' => false,
            'handoff_closure_sealed' => false,
            'handoff_audit_archived' => false,
            'handoff_audit_archive_completion_ready' => false,
            'handoff_audit_archive_completion_confirmed' => false,
            'handoff_audit_archive_completion_go_decision' => 'NO_GO',
            'c162_handoff_audit_archive_complete_confirmed' => false,
            'handoff_audit_archived_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest_created' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c162_plan_confirm_completion_handoff_audit_archive_completion_review_only' => true,
            'c162_controlled_completion_only' => true,
            'c162_not_publication' => true,
            'c162_not_unrestricted_publication' => true,
            'c162_not_plan_confirm_mutation' => true,
            'c162_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_NOT_YET_EVALUATED',
            'next_step_recommendation' => null,
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_review_pass' => true,
            'handoff_ready' => true,
            'handoff_finalized' => true,
            'handoff_completion_boundary_cleared' => true,
            'handoff_closure_sealed' => true,
            'handoff_audit_archived' => true,
            'handoff_audit_archive_completion_ready' => true,
            'handoff_audit_archive_completion_confirmed' => true,
            'handoff_audit_archive_completion_go_decision' => 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO',
            'c162_handoff_audit_archive_complete_confirmed' => true,
            'handoff_audit_archived_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => true,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest_created' => true,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $auditArchive = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($auditArchive, $load, $pass, $options));
        $artifact['c162_handoff_audit_archive_lock_validation_summary'] = $this->c162HandoffAuditArchiveLockValidationSummary($load, $auditArchive);
        $artifact['c162_plan_confirm_completion_handoff_audit_archive_carry_forward_summary'] = $this->c162HandoffAuditArchiveCarryForwardSummary($auditArchive);
        $artifact['plan_confirm_completion_handoff_audit_archive_completion_guard_summary'] = $this->handoffAuditArchiveCompletionGuardSummary($auditArchive, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($auditArchive, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c162_handoff_audit_archive_completion_decision'] = $this->handoffAuditArchiveCompletionDecision($pass, $options);
        $artifact['next_plan_confirm_completion_handoff_audit_archive_completion_seal_decision'] = $this->nextHandoffAuditArchiveCompletionSealDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_manifest'] = $this->handoffAuditArchiveCompletionManifest($auditArchive, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_checklist'] = $this->handoffAuditArchiveCompletionChecklist($pass, $options);
        $artifact['c162_candidate_plan_confirm_completion_handoff_audit_archive_completion_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($auditArchive);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');

        return $artifact;
    }

    private function topLevelState(array $auditArchive, array $load, bool $pass, array $options): array
    {
        return [
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($auditArchive['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($auditArchive['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($auditArchive['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($auditArchive['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'controlled_completion_path' => $auditArchive['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $auditArchive['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $auditArchive['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($auditArchive['controlled_completion_record_count'] ?? 0),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($auditArchive['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($auditArchive['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($auditArchive['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($auditArchive['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($auditArchive['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($auditArchive['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c162_handoff_audit_archive_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c162_plan_confirm_completion_handoff_audit_archive_valid' => $this->c162HandoffAuditArchiveStateValid($auditArchive),
            'c162_handoff_audit_archive_convert_from_json_pass' => $load['convert_from_json_pass'],
            'handoff_ready' => (bool) ($auditArchive['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($auditArchive['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($auditArchive['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => (bool) ($auditArchive['handoff_closure_sealed'] ?? false),
            'handoff_audit_archived' => (bool) ($auditArchive['handoff_audit_archived'] ?? false),
            'handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_confirmed' => (bool) ($options['handoff_audit_archive_completion_confirmed'] ?? false),
            'handoff_audit_archive_completion_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO' : 'NO_GO',
            'c162_handoff_audit_archive_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_complete_confirmed'] ?? false),
            'handoff_audit_archived_confirmed' => (bool) ($options['handoff_audit_archived_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function c162HandoffAuditArchiveStateValid(array $auditArchive): bool
    {
        foreach (self::REQUIRED_C162_HANDOFF_AUDIT_ARCHIVE_TRUE_FIELDS as $field) {
            if (($auditArchive[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C162_HANDOFF_AUDIT_ARCHIVE_FALSE_FIELDS as $field) {
            if (($auditArchive[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($auditArchive['handoff_audit_archive_go_decision'] ?? null) === 'HANDOFF_AUDIT_ARCHIVED_GO'
            && ($auditArchive['controlled_completion_record_count'] ?? 0) === 2
            && trim((string) ($auditArchive['controlled_completion_path'] ?? '')) !== ''
            && trim((string) ($auditArchive['controlled_completion_hash'] ?? '')) !== ''
            && trim((string) ($auditArchive['controlled_completion_file_sha1'] ?? '')) !== ''
            && $this->valueAt($auditArchive, ['next_plan_confirm_completion_handoff_audit_archive_completion_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($auditArchive, ['next_plan_confirm_completion_handoff_audit_archive_completion_decision', 'topic_stage_advances_within_c162_handoff_after_audit_archive']) === true
            && $this->valueAt($auditArchive, ['next_plan_confirm_completion_handoff_audit_archive_completion_decision', 'c162_handoff_audit_archive_complete']) === true
            && $this->valueAt($auditArchive, ['c162_handoff_audit_archive_decision', 'review_valid']) === true
            && $this->valueAt($auditArchive, ['c162_handoff_audit_archive_decision', 'handoff_audit_archived']) === true
            && $this->valueAt($auditArchive, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_manifest', 'ready_for_plan_confirm_completion_handoff_audit_archive_completion_review']) === true
            && $this->valueAt($auditArchive, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_manifest', 'handoff_audit_archive_artifact_only']) === true
            && $this->valueAt($auditArchive, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_manifest', 'handoff_audit_archive_used_for_free_publication']) === false
            && $this->valueAt($auditArchive, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_manifest', 'handoff_audit_archive_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($auditArchive, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_manifest', 'handoff_audit_archive_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($auditArchive, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_checklist', 'artifact_only']) === true
            && $this->valueAt($auditArchive, ['weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_checklist', 'weekly_swing_stock_recommendation_free_published_in_c162_handoff_audit_archive']) === false;
    }

    private function c162NextRecommendationMatches(array $auditArchive): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_completion_handoff_audit_archive_completion_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($auditArchive, $path) !== self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function publicationAndPlanGuardClean(array $auditArchive): bool
    {
        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $field) {
            if (($auditArchive[$field] ?? false) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $auditArchive): bool
    {
        return ($auditArchive['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($auditArchive['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($auditArchive['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($auditArchive['primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_review'] ?? null) === true
            && ($auditArchive['backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_review'] ?? null) === true
            && ($auditArchive['comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_review'] ?? null) === false
            && ($auditArchive['a01_remains_comparator_only'] ?? null) === true
            && ($auditArchive['a01_promoted'] ?? false) === false
            && ($auditArchive['candidate_promotion_executed'] ?? false) === false
            && ($auditArchive['candidate_rerank_executed'] ?? false) === false;
    }

    private function c162HandoffAuditArchiveLockValidationSummary(array $load, array $auditArchive): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_STATUS,
            'actual_status' => $auditArchive['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_PHASE_LABEL,
            'actual_phase_label' => $auditArchive['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c162NextRecommendationMatches($auditArchive),
            'c162_handoff_audit_archive_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function c162HandoffAuditArchiveCarryForwardSummary(array $auditArchive): array
    {
        return [
            'validation_completed' => true,
            'c162_plan_confirm_completion_handoff_audit_archive_valid' => $this->c162HandoffAuditArchiveStateValid($auditArchive),
            'topic_code' => $auditArchive['topic_code'] ?? null,
            'topic_stage' => $auditArchive['topic_stage'] ?? null,
            'handoff_audit_archive_go_decision' => $auditArchive['handoff_audit_archive_go_decision'] ?? null,
            'handoff_ready' => (bool) ($auditArchive['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($auditArchive['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($auditArchive['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => (bool) ($auditArchive['handoff_closure_sealed'] ?? false),
            'handoff_audit_archived' => (bool) ($auditArchive['handoff_audit_archived'] ?? false),
            'controlled_completion_path' => $auditArchive['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $auditArchive['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $auditArchive['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($auditArchive['controlled_completion_record_count'] ?? 0),
            'official_output_published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function handoffAuditArchiveCompletionGuardSummary(array $auditArchive, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'handoff_audit_archive_completion_valid' => $pass,
            'c162_handoff_audit_archive_complete' => $this->valueAt($auditArchive, ['next_plan_confirm_completion_handoff_audit_archive_completion_decision', 'c162_handoff_audit_archive_complete']) === true,
            'handoff_audit_archived' => (bool) ($auditArchive['handoff_audit_archived'] ?? false),
            'ready_for_plan_confirm_completion_handoff_audit_archive_completion_review' => (bool) ($auditArchive['ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_review'] ?? false),
            'handoff_audit_archive_completion_ready' => $pass,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $auditArchive, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($auditArchive),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
            'scoring_mutation_executed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
        ];
    }

    private function operatorApprovalValidationSummary(array $options): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'handoff_audit_archive_completion_confirmation_required' => true,
            'handoff_audit_archive_completion_confirmed' => (bool) ($options['handoff_audit_archive_completion_confirmed'] ?? false),
            'c162_handoff_audit_archive_complete_confirmation_required' => true,
            'c162_handoff_audit_archive_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_complete_confirmed'] ?? false),
            'handoff_audit_archived_confirmation_required' => true,
            'handoff_audit_archived_confirmed' => (bool) ($options['handoff_audit_archived_confirmed'] ?? false),
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

    private function handoffAuditArchiveCompletionDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO' : 'NO_GO',
            'handoff_audit_archive_completion_confirmed' => (bool) ($options['handoff_audit_archive_completion_confirmed'] ?? false),
            'c162_handoff_audit_archive_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_complete_confirmed'] ?? false),
            'handoff_audit_archived_confirmed' => (bool) ($options['handoff_audit_archived_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'decision_scope' => $pass
                ? 'C162 PLAN/CONFIRM completion handoff audit archive completion ready; audit archive completion seal may start next'
                : 'C162 PLAN/CONFIRM completion handoff audit archive completion did not pass',
        ];
    }

    private function nextHandoffAuditArchiveCompletionSealDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_RECOMMENDATION : null,
            'next_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff audit archive completion seal review only; no PLAN/CONFIRM mutation or live rollout is authorized by C162 audit archive completion' : null,
            'next_is_concrete' => $pass,
            'next_requires_locked_c162_handoff_audit_archive_completion_artifact' => $pass,
            'topic_stage_advances_within_c162_handoff_after_audit_archive_completion' => $pass,
            'c162_handoff_audit_archive_completion_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function handoffAuditArchiveCompletionManifest(array $auditArchive, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_handoff_audit_archive_completion_review',
            'source_artifact' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW',
            'source_artifact_path' => self::DEFAULT_C162_HANDOFF_AUDIT_ARCHIVE_ARTIFACT,
            'source_artifact_hash' => $auditArchive['artifact_hash'] ?? null,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C162_HANDOFF_AUDIT_ARCHIVE_FILE_SHA1,
            'handoff_ready' => (bool) ($auditArchive['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($auditArchive['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($auditArchive['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => (bool) ($auditArchive['handoff_closure_sealed'] ?? false),
            'handoff_audit_archived' => (bool) ($auditArchive['handoff_audit_archived'] ?? false),
            'handoff_audit_archive_completion_ready' => $pass,
            'handoff_audit_archive_completion_go_decision' => $pass ? 'HANDOFF_AUDIT_ARCHIVE_COMPLETION_READY_GO' : 'NO_GO',
            'handoff_audit_archive_completion_confirmed' => (bool) ($options['handoff_audit_archive_completion_confirmed'] ?? false),
            'c162_handoff_audit_archive_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_complete_confirmed'] ?? false),
            'handoff_audit_archived_confirmed' => (bool) ($options['handoff_audit_archived_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_hash' => $auditArchive['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $auditArchive['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($auditArchive['controlled_completion_record_count'] ?? 0),
            'ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
            'handoff_audit_archive_completion_artifact_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'handoff_audit_archive_completion_used_for_free_publication' => false,
            'handoff_audit_archive_completion_used_for_plan_confirm_mutation' => false,
            'handoff_audit_archive_completion_used_for_live_plan_confirm_rollout' => false,
        ];
    }

    private function handoffAuditArchiveCompletionChecklist(bool $pass, array $options): array
    {
        return [
            'handoff_audit_archive_completion_reviewed' => $pass,
            'c162_handoff_audit_archive_source_lock_reviewed' => $pass,
            'c162_handoff_audit_archive_complete_reviewed' => $pass,
            'handoff_audit_archive_completion_required' => true,
            'handoff_audit_archive_completion_confirmed' => (bool) ($options['handoff_audit_archive_completion_confirmed'] ?? false),
            'c162_handoff_audit_archive_complete_confirmed' => (bool) ($options['c162_handoff_audit_archive_complete_confirmed'] ?? false),
            'handoff_audit_archived_confirmed' => (bool) ($options['handoff_audit_archived_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_handoff_audit_archive_completion_gate_required' => true,
            'negative_c162_handoff_audit_archive_complete_gate_required' => true,
            'negative_handoff_audit_archived_gate_required' => true,
            'negative_plan_confirm_unchanged_gate_required' => true,
            'negative_no_live_rollout_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'handoff_audit_archive_completion_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c162_handoff_audit_archive_completion' => false,
            'ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
                'ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
                'ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'a01_remains_comparator_only' => true,
                'ready_for_plan_confirm_completion_handoff_audit_archive_completion_seal_review' => false,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $auditArchive): array
    {
        return [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($auditArchive),
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
            'completed_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW',
            'next_topic_number' => 'C162',
            'next_topic' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_RECOMMENDATION : self::RUN_CODE,
            'topic_numbering_rule' => 'Keep topic number C162 until the complete HANDOFF sequence is closed.',
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_RECOMMENDATION : self::RUN_CODE,
            'planned_next_action' => $pass ? 'Lock C162 audit archive completion artifact and seal the completion package.' : 'Resolve C162 rejection and rerun audit archive completion.',
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'runtime_family' => 'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion',
            'review_layer' => 'handoff_audit_archive_completion',
            'source_layer' => 'c162_handoff_audit_archive',
            'next_layer' => 'c162_handoff_audit_archive_completion_seal',
            'candidate_policy' => 'E02 primary, B01 backup, A01 comparator only',
            'publication_policy' => 'controlled output remains generated but unpublished',
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
            'c162_plan_confirm_completion_handoff_audit_archive' => [
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
            'expected_c162_handoff_audit_archive_hash' => $load['expected_hash'],
            'actual_c162_handoff_audit_archive_hash' => $load['actual_hash'],
            'c162_handoff_audit_archive_hash_match' => $load['hash_match'],
            'expected_c162_handoff_audit_archive_file_sha1' => $load['expected_file_sha1'],
            'actual_c162_handoff_audit_archive_file_sha1' => $load['actual_file_sha1'],
            'c162_handoff_audit_archive_file_sha1_match' => $load['file_sha1_match'],
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

        return array_values(array_unique($paths));
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_BLOCKED_SOURCE_LOCK';

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REJECTED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;

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
