<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationObservationReviewService
{
    public const RUN_CODE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW';
    public const PHASE_LABEL = 'PR-77 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW';
    public const ARTIFACT_TYPE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW';

    public const DEFAULT_C163_POST_HANDOFF_ACTIVATION_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-execution-review.json';
    public const DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_HASH = 'e3e1656317754920f8c1248ea515ef9bce1a89aa';
    public const DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_FILE_SHA1 = '40A12B54B58D509982B7739E39905003852D225D';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-observation-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const WATCHLIST_FUNCTION_USED = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const WATCHLIST_FUNCTION_RUNTIME_MODE = 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT';

    private const EXPECTED_C163_EXECUTION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C163_EXECUTION_PHASE_LABEL = 'PR-76 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW';
    private const EXPECTED_C163_EXECUTION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const NEXT_RECOMMENDATION = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW';

    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const OBSERVATION_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_OBSERVATION_CONFIRMATION_MISSING';
    private const EXECUTION_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_COMPLETE_CONFIRMATION_MISSING';
    private const EXECUTION_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_EXECUTION_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C163_EXECUTION_LOCK_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_ARTIFACT_LOCK_MISMATCH';
    private const C163_EXECUTION_FILE_SHA1_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_FILE_SHA1_LOCK_MISMATCH';
    private const C163_EXECUTION_CONVERT_FROM_JSON_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C163_EXECUTION_STATUS_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_STATUS_MISMATCH';
    private const C163_EXECUTION_PHASE_LABEL_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_PHASE_LABEL_MISMATCH';
    private const C163_EXECUTION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_NEXT_RECOMMENDATION_MISMATCH';
    private const C163_EXECUTION_STATE_INVALID_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_STATE_INVALID';
    private const WATCHLIST_FUNCTION_OBSERVATION_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_WATCHLIST_FUNCTION_OBSERVATION_MISMATCH';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_EXECUTION_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_pass',
        'post_handoff_activation_execution_confirmed',
        'post_handoff_activation_executed',
        'controlled_post_handoff_activation_execution_executed',
        'c163_post_handoff_activation_approval_complete_confirmed',
        'post_handoff_activation_approval_confirmed',
        'post_handoff_activation_approval_granted',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c163_post_handoff_activation_approval_lock_valid',
        'c163_plan_confirm_completion_post_handoff_activation_execution_valid',
        'c163_post_handoff_activation_approval_convert_from_json_pass',
        'c163_post_handoff_activation_approval_complete',
        'controlled_completion_lock_valid',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_review_allowed_next',
        'c163_is_same_post_handoff_contract',
        'c163_activation_execution_review_only',
        'c163_controlled_completion_only',
        'c163_not_publication',
        'c163_not_unrestricted_publication',
        'c163_not_plan_confirm_mutation',
        'c163_not_live_plan_confirm_rollout',
        'watchlist_function_primary_candidate_enabled',
        'watchlist_function_backup_candidate_enabled',
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
        'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review',
        'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review',
        'a01_remains_comparator_only',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_EXECUTION_FALSE_FIELDS = [
        'watchlist_function_comparator_candidate_enabled',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review',
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
        'storage/app/watchlist/backtest/c163-*post-handoff-activation-observation*-test.json',
        'storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-observation-negative-*.json',
        'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-observation-negative-*.json',
    ];

    public function execute(
        string $c163PostHandoffActivationExecutionArtifact = self::DEFAULT_C163_POST_HANDOFF_ACTIVATION_EXECUTION_ARTIFACT,
        string $expectedC163PostHandoffActivationExecutionHash = self::DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_HASH,
        string $expectedC163PostHandoffActivationExecutionFileSha1 = self::DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_EXECUTION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c163PostHandoffActivationExecutionArtifact, $expectedC163PostHandoffActivationExecutionHash, $expectedC163PostHandoffActivationExecutionFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->rejected($artifact, self::C163_EXECUTION_LOCK_MISMATCH_STATUS, 'C163 post-handoff activation execution artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c163_post_handoff_activation_execution_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C163_EXECUTION_CONVERT_FROM_JSON_STATUS, 'C163 post-handoff activation execution artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->rejected($artifact, self::C163_EXECUTION_LOCK_MISMATCH_STATUS, 'C163 post-handoff activation execution artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->rejected($artifact, self::C163_EXECUTION_FILE_SHA1_MISMATCH_STATUS, 'C163 post-handoff activation execution file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $execution = $load['payload'];
        if (($execution['status'] ?? null) !== self::EXPECTED_C163_EXECUTION_STATUS || ($execution['reason_code'] ?? null) !== self::EXPECTED_C163_EXECUTION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_EXECUTION_STATUS_MISMATCH_STATUS, 'C163 post-handoff activation execution status/reason is not observation-ready.', $outputPath, $overwrite);
        }
        if (($execution['phase_label'] ?? null) !== self::EXPECTED_C163_EXECUTION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_EXECUTION_PHASE_LABEL_MISMATCH_STATUS, 'C163 post-handoff activation execution phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->executionNextRecommendationMatches($execution)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_EXECUTION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C163 post-handoff activation execution next recommendation is not activation observation review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($execution)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C163 execution evidence has free publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->executionStateValid($execution)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_EXECUTION_STATE_INVALID_STATUS, 'C163 activation execution evidence is incomplete for activation observation review.', $outputPath, $overwrite);
        }
        if (! $this->watchlistFunctionObserved($execution)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::WATCHLIST_FUNCTION_OBSERVATION_MISMATCH_STATUS, 'C163 controlled watchlist function observation does not match locked execution evidence.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($execution)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C163 activation execution candidate scope does not match locked activation observation scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C163 activation observation requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OBSERVATION_CONFIRMATION_MISSING_STATUS, 'C163 requires --post-handoff-activation-observation-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c163_post_handoff_activation_execution_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::EXECUTION_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C163 requires --c163-post-handoff-activation-execution-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_execution_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::EXECUTION_CONFIRMATION_MISSING_STATUS, 'C163 requires --post-handoff-activation-execution-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C163 post-handoff activation observation review observes the controlled weekly swing watchlist function as active for locked primary and backup candidates. It keeps free publication, PLAN/CONFIRM mutation, activated-catalog reads, and live PLAN/CONFIRM rollout locked off.';
        $artifact['diagnostic_conclusion'] = 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_PASSED_CONTROLLED_FUNCTION_OBSERVED_NON_PUBLISHING';
        $artifact['next_step_recommendation'] = self::NEXT_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($execution, $options));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-77',
            'internal_checkpoint' => 'C163',
            'topic_code' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW',
            'status' => 'C163_NOT_RUN',
            'reason_code' => 'C163_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_pass' => false,
            'post_handoff_activation_observation_confirmed' => false,
            'post_handoff_activation_observed' => false,
            'controlled_watchlist_function_observed' => false,
            'c163_post_handoff_activation_execution_complete_confirmed' => false,
            'post_handoff_activation_execution_confirmed' => false,
            'post_handoff_activation_executed' => false,
            'controlled_post_handoff_activation_execution_executed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'c163_post_handoff_activation_execution_lock_valid' => false,
            'c163_plan_confirm_completion_post_handoff_activation_observation_valid' => false,
            'c163_post_handoff_activation_execution_convert_from_json_pass' => false,
            'c163_post_handoff_activation_execution_complete' => false,
            'controlled_completion_lock_valid' => false,
            'controlled_completion_path' => '',
            'controlled_completion_hash' => '',
            'controlled_completion_file_sha1' => '',
            'controlled_completion_record_count' => 0,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed_next' => false,
            'c163_is_same_post_handoff_contract' => true,
            'c163_activation_observation_review_only' => true,
            'c163_controlled_completion_only' => true,
            'c163_not_publication' => true,
            'c163_not_unrestricted_publication' => true,
            'c163_not_plan_confirm_mutation' => true,
            'c163_not_live_plan_confirm_rollout' => true,
            'watchlist_function_used' => '',
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => '',
            'watchlist_function_primary_candidate_observed' => false,
            'watchlist_function_backup_candidate_observed' => false,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => false,
            'a01_remains_comparator_only' => true,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => false,
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

    private function passingTopLevelState(array $execution, array $options): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_pass' => true,
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'post_handoff_activation_observed' => true,
            'controlled_watchlist_function_observed' => true,
            'c163_post_handoff_activation_execution_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_execution_complete_confirmed'] ?? false),
            'post_handoff_activation_execution_confirmed' => (bool) ($options['post_handoff_activation_execution_confirmed'] ?? false),
            'post_handoff_activation_executed' => true,
            'controlled_post_handoff_activation_execution_executed' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'c163_post_handoff_activation_execution_lock_valid' => true,
            'c163_plan_confirm_completion_post_handoff_activation_observation_valid' => true,
            'c163_post_handoff_activation_execution_convert_from_json_pass' => true,
            'c163_post_handoff_activation_execution_complete' => true,
            'controlled_completion_lock_valid' => (bool) ($execution['controlled_completion_lock_valid'] ?? false),
            'controlled_completion_path' => (string) ($execution['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($execution['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($execution['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($execution['controlled_completion_record_count'] ?? 0),
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_review' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed_next' => true,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION_USED,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => (string) ($execution['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => true,
            'watchlist_function_backup_candidate_observed' => true,
            'watchlist_function_comparator_candidate_observed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($execution['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($execution['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($execution['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($execution['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($execution['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($execution['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $execution = is_array($load['payload']) ? $load['payload'] : [];
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary((array) ($options['temporary_negative_artifact_paths'] ?? []));
        $artifact['c163_post_handoff_activation_execution_lock_validation_summary'] = $this->lockValidationSummary($load);
        $artifact['c163_post_handoff_activation_execution_carry_forward_summary'] = $this->carryForwardSummary($execution);
        $artifact['watchlist_function_observation_summary'] = $this->watchlistFunctionObservationSummary($execution, $pass);
        $artifact['plan_confirm_completion_post_handoff_activation_observation_guard_summary'] = $this->activationObservationGuardSummary($execution, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($execution);
        $artifact['c163_post_handoff_activation_observation_decision'] = $this->activationObservationDecision($pass, $options);
        $artifact['next_plan_confirm_completion_post_handoff_activation_decision'] = $this->nextActivationDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest'] = $this->activationObservationManifest($execution, $pass, $options, $load);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_checklist'] = $this->activationObservationChecklist($pass, $options);
        $artifact['c163_candidate_plan_confirm_completion_post_handoff_activation_observation_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($execution);
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
            'post_handoff_activation_observation_confirmation_required' => true,
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'c163_post_handoff_activation_execution_complete_confirmation_required' => true,
            'c163_post_handoff_activation_execution_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_execution_complete_confirmed'] ?? false),
            'post_handoff_activation_execution_confirmation_required' => true,
            'post_handoff_activation_execution_confirmed' => (bool) ($options['post_handoff_activation_execution_confirmed'] ?? false),
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

    private function carryForwardSummary(array $execution): array
    {
        return [
            'validation_completed' => true,
            'source_run_code' => (string) ($execution['run_code'] ?? ''),
            'source_status' => (string) ($execution['status'] ?? ''),
            'source_next_step_recommendation' => (string) ($execution['next_step_recommendation'] ?? ''),
            'post_handoff_activation_executed' => (bool) ($execution['post_handoff_activation_executed'] ?? false),
            'controlled_post_handoff_activation_execution_executed' => (bool) ($execution['controlled_post_handoff_activation_execution_executed'] ?? false),
            'c163_post_handoff_activation_execution_complete' => (bool) ($this->valueAt($execution, ['next_plan_confirm_completion_post_handoff_activation_decision', 'c163_post_handoff_activation_execution_complete']) ?? false),
            'ready_for_post_handoff_activation_observation_review' => (bool) ($execution['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_review'] ?? false),
            'controlled_completion_path' => (string) ($execution['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($execution['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($execution['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($execution['controlled_completion_record_count'] ?? 0),
        ];
    }

    private function watchlistFunctionObservationSummary(array $execution, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'watchlist_function_used' => (string) ($execution['watchlist_function_used'] ?? ''),
            'expected_watchlist_function_used' => self::WATCHLIST_FUNCTION_USED,
            'watchlist_function_runtime_mode' => (string) ($execution['watchlist_function_runtime_mode'] ?? ''),
            'watchlist_function_observed' => $pass,
            'runtime_bridge_active_observed' => (bool) ($execution['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active_observed' => (bool) ($execution['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled_observed' => (bool) ($execution['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed_observed' => (bool) ($execution['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'primary_candidate_observed' => $pass,
            'backup_candidate_observed' => $pass,
            'comparator_candidate_observed' => false,
            'official_output_published' => false,
        ];
    }

    private function activationObservationGuardSummary(array $execution, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'source_c163_execution_next_recommendation_matches' => $this->executionNextRecommendationMatches($execution),
            'source_c163_execution_state_valid' => $this->executionStateValid($execution),
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($execution),
            'source_watchlist_function_observed' => $this->watchlistFunctionObserved($execution),
            'post_handoff_activation_observation_review_pass' => $pass,
            'post_handoff_activation_observation_controlled_only' => true,
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'post_handoff_activation_observation_result_allowed_next' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $execution): array
    {
        return [
            'validation_completed' => true,
            'candidate_scope_matches' => $this->candidateScopeMatches($execution),
            'primary_candidate_code' => (string) ($execution['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($execution['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($execution['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'candidate_promotion_executed' => false,
            'a01_remains_comparator_only' => (bool) ($execution['a01_remains_comparator_only'] ?? false),
        ];
    }

    private function activationObservationDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'post_handoff_activation_observed' => $pass,
            'controlled_watchlist_function_observed' => $pass,
            'c163_post_handoff_activation_execution_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_execution_complete_confirmed'] ?? false),
            'post_handoff_activation_execution_confirmed' => (bool) ($options['post_handoff_activation_execution_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'post_handoff_activation_observation_go_decision' => $pass ? 'POST_HANDOFF_ACTIVATION_OBSERVATION_GO' : 'NO_GO',
            'watchlist_function_observed' => self::WATCHLIST_FUNCTION_USED,
            'decision_scope' => $pass
                ? 'C163 controlled post-handoff activation observation is ready for observation result review.'
                : 'C163 post-handoff activation observation did not pass.',
        ];
    }

    private function nextActivationDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'next_scope' => $pass ? 'post-handoff activation observation result review only' : 'targeted C163 activation observation repair',
            'next_is_concrete' => $pass,
            'c163_post_handoff_activation_observation_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function activationObservationManifest(array $execution, bool $pass, array $options, array $load): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_observation_review',
            'source_artifact' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'post_handoff_activation_execution_confirmed' => (bool) ($execution['post_handoff_activation_execution_confirmed'] ?? false),
            'c163_post_handoff_activation_execution_complete' => (bool) ($this->valueAt($execution, ['next_plan_confirm_completion_post_handoff_activation_decision', 'c163_post_handoff_activation_execution_complete']) ?? false),
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'post_handoff_activation_observation_go_decision' => $pass ? 'POST_HANDOFF_ACTIVATION_OBSERVATION_GO' : 'NO_GO',
            'ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => $pass,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION_USED,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => (string) ($execution['watchlist_function_source_artifact'] ?? ''),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_path' => (string) ($execution['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($execution['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($execution['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($execution['controlled_completion_record_count'] ?? 0),
            'post_handoff_activation_observation_controlled_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'activation_observation_used_for_free_publication' => false,
            'activation_observation_used_for_plan_confirm_mutation' => false,
            'activation_observation_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function activationObservationChecklist(bool $pass, array $options): array
    {
        return [
            'post_handoff_activation_observation_reviewed' => $pass,
            'c163_post_handoff_activation_execution_source_lock_reviewed' => $pass,
            'c163_post_handoff_activation_execution_complete_reviewed' => $pass,
            'post_handoff_activation_observation_required' => true,
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'c163_post_handoff_activation_execution_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_execution_complete_confirmed'] ?? false),
            'post_handoff_activation_execution_confirmed' => (bool) ($options['post_handoff_activation_execution_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'controlled_watchlist_function_observation_required' => true,
            'activation_observation_review_only' => true,
            'controlled_observation_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c163_activation_observation' => false,
            'post_handoff_activation_observation_result_review_required_next' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'watchlist_function_observed' => $pass,
                'ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'watchlist_function_observed' => $pass,
                'ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'watchlist_function_observed' => false,
                'a01_remains_comparator_only' => true,
                'ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review' => false,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $execution): array
    {
        return [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($execution),
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
            'completed_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW',
            'previous_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW',
            'next_topic_number' => 'C163',
            'next_topic' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'topic_numbering_rule' => 'Keep C163 while the post-handoff activation path is still progressing.',
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NEXT_RECOMMENDATION : self::RUN_CODE,
            'planned_next_action' => $pass ? 'Review the C163 post-handoff activation observation result under controlled watchlist output; do not publish freely or mutate PLAN/CONFIRM.' : 'Resolve C163 rejection and rerun post-handoff activation observation review.',
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'runtime_family' => 'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion',
            'review_layer' => 'post_handoff_activation_observation',
            'source_layer' => 'c163_post_handoff_activation_execution',
            'next_layer' => 'post_handoff_activation_observation_result_review',
            'watchlist_function_observed' => self::WATCHLIST_FUNCTION_USED,
            'candidate_policy' => 'E02 primary, B01 backup, A01 comparator only',
            'publication_policy' => 'controlled output remains unpublished and unrestricted publication stays locked',
            'plan_confirm_policy' => 'PLAN/CONFIRM remains unchanged and no live rollout is authorized',
        ];
    }

    private function executionStateValid(array $execution): bool
    {
        foreach (self::REQUIRED_EXECUTION_TRUE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_EXECUTION_FALSE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== false) {
                return false;
            }
        }
        if ((int) ($execution['controlled_completion_record_count'] ?? 0) !== 2) {
            return false;
        }
        if (trim((string) ($execution['controlled_completion_path'] ?? '')) === '') {
            return false;
        }
        if (trim((string) ($execution['controlled_completion_hash'] ?? '')) === '' || trim((string) ($execution['controlled_completion_file_sha1'] ?? '')) === '') {
            return false;
        }
        if ($this->valueAt($execution, ['c163_post_handoff_activation_execution_decision', 'review_valid']) !== true) {
            return false;
        }
        if ($this->valueAt($execution, ['c163_post_handoff_activation_execution_decision', 'post_handoff_activation_execution_go_decision']) !== 'POST_HANDOFF_ACTIVATION_EXECUTION_GO') {
            return false;
        }
        if ($this->valueAt($execution, ['next_plan_confirm_completion_post_handoff_activation_decision', 'c163_post_handoff_activation_execution_complete']) !== true) {
            return false;
        }
        if ($this->valueAt($execution, ['next_plan_confirm_completion_post_handoff_activation_decision', 'free_publication_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($execution, ['next_plan_confirm_completion_post_handoff_activation_decision', 'plan_confirm_mutation_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($execution, ['next_plan_confirm_completion_post_handoff_activation_decision', 'live_plan_confirm_rollout_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'post_handoff_activation_execution_controlled_only']) !== true) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'ready_for_plan_confirm_completion_post_handoff_activation_observation_review']) !== true) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'activation_execution_used_for_free_publication']) !== false) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'activation_execution_used_for_plan_confirm_mutation']) !== false) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'activation_execution_used_for_live_plan_confirm_rollout']) !== false) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'official_weekly_swing_stock_recommendations']) !== []) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_checklist', 'controlled_execution_only']) !== true) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_checklist', 'post_handoff_activation_observation_review_required_next']) !== true) {
            return false;
        }
        if ($this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_checklist', 'weekly_swing_stock_recommendation_free_published_in_c163_activation_execution']) !== false) {
            return false;
        }

        return true;
    }

    private function watchlistFunctionObserved(array $execution): bool
    {
        return ($execution['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION_USED
            && ($execution['watchlist_function_runtime_mode'] ?? null) === self::WATCHLIST_FUNCTION_RUNTIME_MODE
            && ($execution['watchlist_function_primary_candidate_enabled'] ?? null) === true
            && ($execution['watchlist_function_backup_candidate_enabled'] ?? null) === true
            && ($execution['watchlist_function_comparator_candidate_enabled'] ?? null) === false
            && ($execution['runtime_bridge_active'] ?? null) === true
            && ($execution['weekly_swing_watchlist_runtime_active'] ?? null) === true
            && ($execution['weekly_swing_watchlist_live_output_enabled'] ?? null) === true
            && ($execution['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? null) === true
            && ($execution['weekly_swing_watchlist_official_output_published'] ?? null) === false;
    }

    private function executionNextRecommendationMatches(array $execution): bool
    {
        return ($execution['next_step_recommendation'] ?? null) === self::EXPECTED_C163_EXECUTION_NEXT_RECOMMENDATION
            && $this->valueAt($execution, ['next_plan_confirm_completion_post_handoff_activation_decision', 'next_recommendation']) === self::EXPECTED_C163_EXECUTION_NEXT_RECOMMENDATION
            && $this->valueAt($execution, ['planned_next_summary', 'planned_next_review']) === self::EXPECTED_C163_EXECUTION_NEXT_RECOMMENDATION;
    }

    private function publicationAndPlanGuardClean(array $execution): bool
    {
        foreach (self::PUBLICATION_AND_PLAN_GUARD_FALSE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== false) {
                return false;
            }
        }
        foreach ([
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'activation_execution_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'activation_execution_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_execution_manifest', 'activation_execution_used_for_live_plan_confirm_rollout'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_official_output_published'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_unrestricted_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'plan_confirm_mutated'],
            ['publication_plan_confirm_safety_summary', 'live_plan_confirm_rollout_executed'],
        ] as $path) {
            if ($this->valueAt($execution, $path) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $execution): bool
    {
        return ($execution['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($execution['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($execution['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($execution['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review'] ?? null) === true
            && ($execution['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review'] ?? null) === true
            && ($execution['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review'] ?? null) === false
            && ($execution['a01_remains_comparator_only'] ?? null) === true
            && ($execution['a01_promoted'] ?? false) === false;
    }

    private function failureAttributionSummary(array $failures): array
    {
        return [
            'failure_count' => count($failures),
            'failures' => $failures,
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_OPERATOR_OR_OBSERVATION_LOCK',
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
            'c163_plan_confirm_completion_post_handoff_activation_execution' => [
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
            'expected_c163_post_handoff_activation_execution_hash' => $load['expected_hash'],
            'actual_c163_post_handoff_activation_execution_hash' => $load['actual_hash'],
            'c163_post_handoff_activation_execution_hash_match' => $load['hash_match'],
            'expected_c163_post_handoff_activation_execution_file_sha1' => $load['expected_file_sha1'],
            'actual_c163_post_handoff_activation_execution_file_sha1' => $load['actual_file_sha1'],
            'c163_post_handoff_activation_execution_file_sha1_match' => $load['file_sha1_match'],
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
        $artifact['diagnostic_conclusion'] = 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REJECTED';
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
