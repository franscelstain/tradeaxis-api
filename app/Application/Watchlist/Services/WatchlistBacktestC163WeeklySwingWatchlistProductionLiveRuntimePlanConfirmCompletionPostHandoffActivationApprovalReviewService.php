<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationApprovalReviewService
{
    public const RUN_CODE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW';
    public const PHASE_LABEL = 'PR-75 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW';
    public const ARTIFACT_TYPE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW';

    public const DEFAULT_C163_POST_HANDOFF_ACTIVATION_READINESS_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-readiness-review.json';
    public const DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_HASH = '2ade4f45972d1675eb2be1c222bc688d0c454b3b';
    public const DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_FILE_SHA1 = '17BA06C16DC071B38643D8F502C2D22808725A72';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-approval-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C163_READINESS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C163_READINESS_PHASE_LABEL = 'PR-74 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW';
    private const EXPECTED_C163_READINESS_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const NEXT_RECOMMENDATION = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW';

    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const ACTIVATION_APPROVAL_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_APPROVAL_CONFIRMATION_MISSING';
    private const READINESS_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_COMPLETE_CONFIRMATION_MISSING';
    private const ACTIVATION_READINESS_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_READINESS_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C163_READINESS_LOCK_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_ARTIFACT_LOCK_MISMATCH';
    private const C163_READINESS_FILE_SHA1_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_FILE_SHA1_LOCK_MISMATCH';
    private const C163_READINESS_CONVERT_FROM_JSON_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C163_READINESS_STATUS_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_STATUS_MISMATCH';
    private const C163_READINESS_PHASE_LABEL_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_PHASE_LABEL_MISMATCH';
    private const C163_READINESS_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_NEXT_RECOMMENDATION_MISMATCH';
    private const C163_READINESS_STATE_INVALID_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_READINESS_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_pass',
        'post_handoff_activation_readiness_confirmed',
        'c163_post_handoff_boundary_complete_confirmed',
        'post_handoff_boundary_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c163_post_handoff_boundary_lock_valid',
        'c163_plan_confirm_completion_post_handoff_activation_readiness_valid',
        'c163_post_handoff_boundary_convert_from_json_pass',
        'c163_post_handoff_boundary_complete',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_review_allowed_next',
        'c163_is_same_post_handoff_contract',
        'c163_activation_readiness_review_only',
        'c163_controlled_completion_only',
        'c163_not_publication',
        'c163_not_unrestricted_publication',
        'c163_not_plan_confirm_mutation',
        'c163_not_live_plan_confirm_rollout',
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
        'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review',
        'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review',
        'a01_remains_comparator_only',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_READINESS_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review',
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
        'storage/app/watchlist/backtest/c163-*post-handoff-activation-approval*-test.json',
        'storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-approval-negative-*.json',
        'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-approval-negative-*.json',
    ];

    public function execute(
        string $c163PostHandoffActivationReadinessArtifact = self::DEFAULT_C163_POST_HANDOFF_ACTIVATION_READINESS_ARTIFACT,
        string $expectedC163PostHandoffActivationReadinessHash = self::DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_HASH,
        string $expectedC163PostHandoffActivationReadinessFileSha1 = self::DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_READINESS_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c163PostHandoffActivationReadinessArtifact, $expectedC163PostHandoffActivationReadinessHash, $expectedC163PostHandoffActivationReadinessFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->rejected($artifact, self::C163_READINESS_LOCK_MISMATCH_STATUS, 'C163 post-handoff activation readiness artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c163_post_handoff_activation_readiness_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C163_READINESS_CONVERT_FROM_JSON_STATUS, 'C163 post-handoff activation readiness artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->rejected($artifact, self::C163_READINESS_LOCK_MISMATCH_STATUS, 'C163 post-handoff activation readiness artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->rejected($artifact, self::C163_READINESS_FILE_SHA1_MISMATCH_STATUS, 'C163 post-handoff activation readiness file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $readiness = $load['payload'];
        if (($readiness['status'] ?? null) !== self::EXPECTED_C163_READINESS_STATUS || ($readiness['reason_code'] ?? null) !== self::EXPECTED_C163_READINESS_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_READINESS_STATUS_MISMATCH_STATUS, 'C163 post-handoff activation readiness status/reason is not approval-ready.', $outputPath, $overwrite);
        }
        if (($readiness['phase_label'] ?? null) !== self::EXPECTED_C163_READINESS_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_READINESS_PHASE_LABEL_MISMATCH_STATUS, 'C163 post-handoff activation readiness phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->readinessNextRecommendationMatches($readiness)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_READINESS_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C163 post-handoff activation readiness next recommendation is not activation approval review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($readiness)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C163 readiness evidence has free publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->readinessStateValid($readiness)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_READINESS_STATE_INVALID_STATUS, 'C163 activation readiness evidence is incomplete for activation approval review.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($readiness)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C163 activation readiness candidate scope does not match locked activation approval scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C163 activation approval requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_approval_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::ACTIVATION_APPROVAL_CONFIRMATION_MISSING_STATUS, 'C163 requires --post-handoff-activation-approval-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c163_post_handoff_activation_readiness_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::READINESS_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C163 requires --c163-post-handoff-activation-readiness-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_readiness_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::ACTIVATION_READINESS_CONFIRMATION_MISSING_STATUS, 'C163 requires --post-handoff-activation-readiness-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C163 post-handoff activation approval review grants approval for a later activation execution review only. It remains review-only and does not free-publish output, mutate PLAN/CONFIRM, read the activated catalog from PLAN/CONFIRM, or execute live PLAN/CONFIRM rollout.';
        $artifact['diagnostic_conclusion'] = 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_PASSED_REVIEW_ONLY_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::NEXT_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($load['payload'], $options));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-75',
            'internal_checkpoint' => 'C163',
            'topic_code' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW',
            'status' => 'C163_NOT_RUN',
            'reason_code' => 'C163_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_pass' => false,
            'post_handoff_activation_approval_confirmed' => false,
            'post_handoff_activation_approval_granted' => false,
            'c163_post_handoff_activation_readiness_complete_confirmed' => false,
            'post_handoff_activation_readiness_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'c163_post_handoff_activation_readiness_lock_valid' => false,
            'c163_plan_confirm_completion_post_handoff_activation_approval_valid' => false,
            'c163_post_handoff_activation_readiness_convert_from_json_pass' => false,
            'c163_post_handoff_activation_readiness_complete' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_review_allowed_next' => false,
            'c163_is_same_post_handoff_contract' => true,
            'c163_activation_approval_review_only' => true,
            'c163_controlled_completion_only' => true,
            'c163_not_publication' => true,
            'c163_not_unrestricted_publication' => true,
            'c163_not_plan_confirm_mutation' => true,
            'c163_not_live_plan_confirm_rollout' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => false,
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

    private function passingTopLevelState(array $readiness, array $options): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_approval_review_pass' => true,
            'post_handoff_activation_approval_confirmed' => (bool) ($options['post_handoff_activation_approval_confirmed'] ?? false),
            'post_handoff_activation_approval_granted' => true,
            'c163_post_handoff_activation_readiness_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_readiness_complete_confirmed'] ?? false),
            'post_handoff_activation_readiness_confirmed' => (bool) ($options['post_handoff_activation_readiness_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'c163_post_handoff_activation_readiness_lock_valid' => true,
            'c163_plan_confirm_completion_post_handoff_activation_approval_valid' => true,
            'c163_post_handoff_activation_readiness_convert_from_json_pass' => true,
            'c163_post_handoff_activation_readiness_complete' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_review' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_review_allowed_next' => true,
            'controlled_completion_path' => (string) ($readiness['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($readiness['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($readiness['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($readiness['controlled_completion_record_count'] ?? 0),
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($readiness['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($readiness['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($readiness['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($readiness['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($readiness['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($readiness['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($readiness['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($readiness['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($readiness['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($readiness['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $readiness = is_array($load['payload']) ? $load['payload'] : [];
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary((array) ($options['temporary_negative_artifact_paths'] ?? []));
        $artifact['c163_post_handoff_activation_readiness_lock_validation_summary'] = $this->lockValidationSummary($load);
        $artifact['c163_post_handoff_activation_readiness_carry_forward_summary'] = $this->carryForwardSummary($readiness);
        $artifact['plan_confirm_completion_post_handoff_activation_approval_guard_summary'] = $this->activationApprovalGuardSummary($readiness, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($readiness);
        $artifact['c163_post_handoff_activation_approval_decision'] = $this->activationApprovalDecision($pass, $options);
        $artifact['next_plan_confirm_completion_post_handoff_activation_decision'] = $this->nextActivationDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_manifest'] = $this->activationApprovalManifest($readiness, $pass, $options, $load);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_checklist'] = $this->activationApprovalChecklist($pass, $options);
        $artifact['c163_candidate_plan_confirm_completion_post_handoff_activation_approval_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($readiness);
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
            'post_handoff_activation_approval_confirmation_required' => true,
            'post_handoff_activation_approval_confirmed' => (bool) ($options['post_handoff_activation_approval_confirmed'] ?? false),
            'c163_post_handoff_activation_readiness_complete_confirmation_required' => true,
            'c163_post_handoff_activation_readiness_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_readiness_complete_confirmed'] ?? false),
            'post_handoff_activation_readiness_confirmation_required' => true,
            'post_handoff_activation_readiness_confirmed' => (bool) ($options['post_handoff_activation_readiness_confirmed'] ?? false),
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

    private function carryForwardSummary(array $readiness): array
    {
        return [
            'validation_completed' => true,
            'source_run_code' => (string) ($readiness['run_code'] ?? ''),
            'source_status' => (string) ($readiness['status'] ?? ''),
            'source_next_step_recommendation' => (string) ($readiness['next_step_recommendation'] ?? ''),
            'post_handoff_activation_readiness_confirmed' => (bool) ($readiness['post_handoff_activation_readiness_confirmed'] ?? false),
            'c163_post_handoff_activation_readiness_complete' => (bool) ($this->valueAt($readiness, ['next_plan_confirm_completion_post_handoff_activation_decision', 'c163_post_handoff_activation_readiness_complete']) ?? false),
            'ready_for_post_handoff_activation_approval_review' => (bool) ($readiness['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_approval_review'] ?? false),
            'controlled_completion_path' => (string) ($readiness['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($readiness['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($readiness['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($readiness['controlled_completion_record_count'] ?? 0),
        ];
    }

    private function activationApprovalGuardSummary(array $readiness, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'source_c163_readiness_next_recommendation_matches' => $this->readinessNextRecommendationMatches($readiness),
            'source_c163_readiness_state_valid' => $this->readinessStateValid($readiness),
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($readiness),
            'post_handoff_activation_approval_review_pass' => $pass,
            'post_handoff_activation_approval_artifact_only' => true,
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'post_handoff_activation_execution_allowed_next' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $readiness): array
    {
        return [
            'validation_completed' => true,
            'candidate_scope_matches' => $this->candidateScopeMatches($readiness),
            'primary_candidate_code' => (string) ($readiness['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($readiness['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($readiness['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'candidate_promotion_executed' => false,
            'a01_remains_comparator_only' => (bool) ($readiness['a01_remains_comparator_only'] ?? false),
        ];
    }

    private function activationApprovalDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'post_handoff_activation_approval_confirmed' => (bool) ($options['post_handoff_activation_approval_confirmed'] ?? false),
            'post_handoff_activation_approval_granted' => $pass,
            'c163_post_handoff_activation_readiness_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_readiness_complete_confirmed'] ?? false),
            'post_handoff_activation_readiness_confirmed' => (bool) ($options['post_handoff_activation_readiness_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'post_handoff_activation_approval_go_decision' => $pass ? 'POST_HANDOFF_ACTIVATION_APPROVAL_GO' : 'NO_GO',
            'decision_scope' => $pass
                ? 'C163 post-handoff activation approval is cleared for activation execution review only.'
                : 'C163 post-handoff activation approval did not pass.',
        ];
    }

    private function nextActivationDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'next_scope' => $pass ? 'post-handoff activation execution review only' : 'targeted C163 activation approval repair',
            'next_is_concrete' => $pass,
            'c163_post_handoff_activation_approval_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function activationApprovalManifest(array $readiness, bool $pass, array $options, array $load): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_approval_review',
            'source_artifact' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'post_handoff_activation_readiness_confirmed' => (bool) ($readiness['post_handoff_activation_readiness_confirmed'] ?? false),
            'c163_post_handoff_activation_readiness_complete' => (bool) ($this->valueAt($readiness, ['next_plan_confirm_completion_post_handoff_activation_decision', 'c163_post_handoff_activation_readiness_complete']) ?? false),
            'post_handoff_activation_approval_confirmed' => (bool) ($options['post_handoff_activation_approval_confirmed'] ?? false),
            'post_handoff_activation_approval_go_decision' => $pass ? 'POST_HANDOFF_ACTIVATION_APPROVAL_GO' : 'NO_GO',
            'ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_path' => (string) ($readiness['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($readiness['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($readiness['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($readiness['controlled_completion_record_count'] ?? 0),
            'post_handoff_activation_approval_artifact_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'activation_approval_used_for_free_publication' => false,
            'activation_approval_used_for_plan_confirm_mutation' => false,
            'activation_approval_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function activationApprovalChecklist(bool $pass, array $options): array
    {
        return [
            'post_handoff_activation_approval_reviewed' => $pass,
            'c163_post_handoff_activation_readiness_source_lock_reviewed' => $pass,
            'c163_post_handoff_activation_readiness_complete_reviewed' => $pass,
            'post_handoff_activation_approval_required' => true,
            'post_handoff_activation_approval_confirmed' => (bool) ($options['post_handoff_activation_approval_confirmed'] ?? false),
            'c163_post_handoff_activation_readiness_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_readiness_complete_confirmed'] ?? false),
            'post_handoff_activation_readiness_confirmed' => (bool) ($options['post_handoff_activation_readiness_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'activation_approval_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c163_activation_approval' => false,
            'post_handoff_activation_execution_review_required_next' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'a01_remains_comparator_only' => true,
                'ready_for_plan_confirm_completion_post_handoff_activation_execution_review' => false,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $readiness): array
    {
        return [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($readiness),
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
            'completed_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW',
            'previous_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW',
            'next_topic_number' => 'C163',
            'next_topic' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'topic_numbering_rule' => 'Keep C163 while the post-handoff activation path is still progressing.',
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'planned_next_action' => $pass ? 'Run C163 post-handoff activation execution review under controlled rules; do not publish freely or mutate PLAN/CONFIRM.' : 'Resolve C163 rejection and rerun post-handoff activation approval review.',
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'runtime_family' => 'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion',
            'review_layer' => 'post_handoff_activation_approval',
            'source_layer' => 'c163_post_handoff_activation_readiness',
            'next_layer' => 'post_handoff_activation_execution_review',
            'candidate_policy' => 'E02 primary, B01 backup, A01 comparator only',
            'publication_policy' => 'controlled output remains unpublished and unrestricted publication stays locked',
            'plan_confirm_policy' => 'PLAN/CONFIRM remains unchanged and no live rollout is authorized',
        ];
    }

    private function readinessStateValid(array $readiness): bool
    {
        foreach (self::REQUIRED_READINESS_TRUE_FIELDS as $field) {
            if (($readiness[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_READINESS_FALSE_FIELDS as $field) {
            if (($readiness[$field] ?? null) !== false) {
                return false;
            }
        }
        if ((int) ($readiness['controlled_completion_record_count'] ?? 0) !== 2) {
            return false;
        }
        if (trim((string) ($readiness['controlled_completion_hash'] ?? '')) === '' || trim((string) ($readiness['controlled_completion_file_sha1'] ?? '')) === '') {
            return false;
        }
        if ($this->valueAt($readiness, ['c163_post_handoff_activation_readiness_decision', 'review_valid']) !== true) {
            return false;
        }
        if ($this->valueAt($readiness, ['c163_post_handoff_activation_readiness_decision', 'post_handoff_activation_readiness_go_decision']) !== 'POST_HANDOFF_ACTIVATION_READINESS_GO') {
            return false;
        }
        if ($this->valueAt($readiness, ['next_plan_confirm_completion_post_handoff_activation_decision', 'c163_post_handoff_activation_readiness_complete']) !== true) {
            return false;
        }
        if ($this->valueAt($readiness, ['next_plan_confirm_completion_post_handoff_activation_decision', 'free_publication_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($readiness, ['next_plan_confirm_completion_post_handoff_activation_decision', 'plan_confirm_mutation_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($readiness, ['next_plan_confirm_completion_post_handoff_activation_decision', 'live_plan_confirm_rollout_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'post_handoff_activation_readiness_artifact_only']) !== true) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'ready_for_plan_confirm_completion_post_handoff_activation_approval_review']) !== true) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'activation_readiness_used_for_free_publication']) !== false) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'activation_readiness_used_for_plan_confirm_mutation']) !== false) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'activation_readiness_used_for_live_plan_confirm_rollout']) !== false) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'official_weekly_swing_stock_recommendations']) !== []) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_checklist', 'artifact_only']) !== true) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_checklist', 'post_handoff_activation_approval_review_required_next']) !== true) {
            return false;
        }
        if ($this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_checklist', 'weekly_swing_stock_recommendation_free_published_in_c163_activation_readiness']) !== false) {
            return false;
        }

        return true;
    }

    private function readinessNextRecommendationMatches(array $readiness): bool
    {
        return ($readiness['next_step_recommendation'] ?? null) === self::EXPECTED_C163_READINESS_NEXT_RECOMMENDATION
            && $this->valueAt($readiness, ['next_plan_confirm_completion_post_handoff_activation_decision', 'next_recommendation']) === self::EXPECTED_C163_READINESS_NEXT_RECOMMENDATION
            && $this->valueAt($readiness, ['planned_next_summary', 'planned_next_review']) === self::EXPECTED_C163_READINESS_NEXT_RECOMMENDATION;
    }

    private function publicationAndPlanGuardClean(array $readiness): bool
    {
        foreach (self::PUBLICATION_AND_PLAN_GUARD_FALSE_FIELDS as $field) {
            if (($readiness[$field] ?? null) !== false) {
                return false;
            }
        }
        foreach ([
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'activation_readiness_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'activation_readiness_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_manifest', 'activation_readiness_used_for_live_plan_confirm_rollout'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_official_output_published'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_unrestricted_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'plan_confirm_mutated'],
            ['publication_plan_confirm_safety_summary', 'live_plan_confirm_rollout_executed'],
        ] as $path) {
            if ($this->valueAt($readiness, $path) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $readiness): bool
    {
        return ($readiness['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($readiness['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($readiness['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($readiness['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review'] ?? null) === true
            && ($readiness['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review'] ?? null) === true
            && ($readiness['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_approval_review'] ?? null) === false
            && ($readiness['a01_remains_comparator_only'] ?? null) === true
            && ($readiness['a01_promoted'] ?? false) === false;
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
            'c163_plan_confirm_completion_post_handoff_activation_readiness' => [
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
            'expected_c163_post_handoff_activation_readiness_hash' => $load['expected_hash'],
            'actual_c163_post_handoff_activation_readiness_hash' => $load['actual_hash'],
            'c163_post_handoff_activation_readiness_hash_match' => $load['hash_match'],
            'expected_c163_post_handoff_activation_readiness_file_sha1' => $load['expected_file_sha1'],
            'actual_c163_post_handoff_activation_readiness_file_sha1' => $load['actual_file_sha1'],
            'c163_post_handoff_activation_readiness_file_sha1_match' => $load['file_sha1_match'],
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
        $artifact['diagnostic_conclusion'] = 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REJECTED';
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
