<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationObservationResultReviewService
{
    public const RUN_CODE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-78 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW';

    public const DEFAULT_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-observation-review.json';
    public const DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_HASH = '2c150f14fca84692db091b8b5137ed1e68855ffa';
    public const DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_FILE_SHA1 = '94ACF854DAF2DF1669B89D487F13496D0019F576';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-observation-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const WATCHLIST_FUNCTION_USED = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const WATCHLIST_FUNCTION_RUNTIME_MODE = 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT';

    private const EXPECTED_C163_OBSERVATION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C163_OBSERVATION_PHASE_LABEL = 'PR-77 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW';
    private const EXPECTED_C163_OBSERVATION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const NEXT_OPERATOR_RECOMMENDATION = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const RESULT_REVIEW_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING';
    private const OBSERVATION_RESULT_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_CONFIRMATION_MISSING';
    private const OBSERVATION_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_COMPLETE_CONFIRMATION_MISSING';
    private const OBSERVATION_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_OBSERVATION_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C163_OBSERVATION_LOCK_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_ARTIFACT_LOCK_MISMATCH';
    private const C163_OBSERVATION_FILE_SHA1_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_FILE_SHA1_LOCK_MISMATCH';
    private const C163_OBSERVATION_CONVERT_FROM_JSON_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C163_OBSERVATION_STATUS_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_STATUS_MISMATCH';
    private const C163_OBSERVATION_PHASE_LABEL_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_PHASE_LABEL_MISMATCH';
    private const C163_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C163_OBSERVATION_INCOMPLETE_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_INCOMPLETE';
    private const WATCHLIST_FUNCTION_RESULT_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_WATCHLIST_FUNCTION_OBSERVATION_RESULT_MISMATCH';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_OBSERVATION_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_pass',
        'post_handoff_activation_observation_confirmed',
        'post_handoff_activation_observed',
        'controlled_watchlist_function_observed',
        'c163_post_handoff_activation_execution_complete_confirmed',
        'post_handoff_activation_execution_confirmed',
        'post_handoff_activation_executed',
        'controlled_post_handoff_activation_execution_executed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c163_post_handoff_activation_execution_lock_valid',
        'c163_plan_confirm_completion_post_handoff_activation_observation_valid',
        'c163_post_handoff_activation_execution_convert_from_json_pass',
        'c163_post_handoff_activation_execution_complete',
        'controlled_completion_lock_valid',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed_next',
        'c163_is_same_post_handoff_contract',
        'c163_activation_observation_review_only',
        'c163_controlled_completion_only',
        'c163_not_publication',
        'c163_not_unrestricted_publication',
        'c163_not_plan_confirm_mutation',
        'c163_not_live_plan_confirm_rollout',
        'watchlist_function_primary_candidate_observed',
        'watchlist_function_backup_candidate_observed',
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
        'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review',
        'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review',
        'a01_remains_comparator_only',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_OBSERVATION_FALSE_FIELDS = [
        'watchlist_function_comparator_candidate_observed',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review',
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
        'storage/app/watchlist/backtest/c163-*post-handoff-activation-observation-result-review*-test.json',
        'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-observation-result-review*.json',
        'storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-observation-result-negative-*.json',
    ];

    public function execute(
        string $c163PostHandoffActivationObservationArtifact = self::DEFAULT_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_ARTIFACT,
        string $expectedC163PostHandoffActivationObservationHash = self::DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_HASH,
        string $expectedC163PostHandoffActivationObservationFileSha1 = self::DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c163PostHandoffActivationObservationArtifact, $expectedC163PostHandoffActivationObservationHash, $expectedC163PostHandoffActivationObservationFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->rejected($artifact, self::C163_OBSERVATION_LOCK_MISMATCH_STATUS, 'C163 post-handoff activation observation artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c163_post_handoff_activation_observation_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C163_OBSERVATION_CONVERT_FROM_JSON_STATUS, 'C163 post-handoff activation observation artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->rejected($artifact, self::C163_OBSERVATION_LOCK_MISMATCH_STATUS, 'C163 post-handoff activation observation artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->rejected($artifact, self::C163_OBSERVATION_FILE_SHA1_MISMATCH_STATUS, 'C163 post-handoff activation observation file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $observation = $load['payload'];
        if (($observation['status'] ?? null) !== self::EXPECTED_C163_OBSERVATION_STATUS || ($observation['reason_code'] ?? null) !== self::EXPECTED_C163_OBSERVATION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_OBSERVATION_STATUS_MISMATCH_STATUS, 'C163 post-handoff activation observation status/reason is not result-review ready.', $outputPath, $overwrite);
        }
        if (($observation['phase_label'] ?? null) !== self::EXPECTED_C163_OBSERVATION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_OBSERVATION_PHASE_LABEL_MISMATCH_STATUS, 'C163 post-handoff activation observation phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->observationNextRecommendationMatches($observation)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C163 post-handoff activation observation next recommendation is not observation result review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($observation)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'Free publication, unrestricted publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already occurred.', $outputPath, $overwrite);
        }
        if (! $this->observationComplete($observation)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_OBSERVATION_INCOMPLETE_STATUS, 'C163 post-handoff activation observation evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->watchlistFunctionResultStable($observation)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::WATCHLIST_FUNCTION_RESULT_MISMATCH_STATUS, 'C163 controlled watchlist function observation result is not stable.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($observation)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C163 activation observation candidate scope does not match locked result-review scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C163 observation result review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['result_review_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::RESULT_REVIEW_CONFIRMATION_MISSING_STATUS, 'C163 observation result review requires --result-review-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_observation_result_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OBSERVATION_RESULT_CONFIRMATION_MISSING_STATUS, 'C163 observation result review requires --post-handoff-activation-observation-result-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c163_post_handoff_activation_observation_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OBSERVATION_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C163 observation result review requires --c163-post-handoff-activation-observation-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OBSERVATION_CONFIRMATION_MISSING_STATUS, 'C163 observation result review requires --post-handoff-activation-observation-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C163 observation result review requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C163 observation result review requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C163 observation result review requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C163 post-handoff activation observation result review confirms the controlled weekly swing watchlist function is stable for locked primary and backup candidates. It keeps publication, PLAN/CONFIRM mutation, activated-catalog reads, and live PLAN/CONFIRM rollout locked off.';
        $artifact['diagnostic_conclusion'] = 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_CONTROLLED_FUNCTION_STABLE_READY_FOR_OPERATOR_GO_NO_GO';
        $artifact['next_step_recommendation'] = self::NEXT_OPERATOR_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($observation, $options));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-78',
            'internal_checkpoint' => 'C163',
            'topic_code' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW',
            'status' => 'C163_OBSERVATION_RESULT_REVIEW_NOT_RUN',
            'reason_code' => 'C163_OBSERVATION_RESULT_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_pass' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed' => false,
            'post_handoff_activation_observation_result_confirmed' => false,
            'post_handoff_activation_observation_result_review_confirmed' => false,
            'post_handoff_activation_observation_result_stable' => false,
            'controlled_watchlist_function_observation_result_reviewed' => false,
            'c163_post_handoff_activation_observation_complete_confirmed' => false,
            'post_handoff_activation_observation_confirmed' => false,
            'post_handoff_activation_observed' => false,
            'controlled_watchlist_function_observed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'c163_post_handoff_activation_observation_lock_valid' => false,
            'c163_plan_confirm_completion_post_handoff_activation_observation_result_review_valid' => false,
            'c163_post_handoff_activation_observation_convert_from_json_pass' => false,
            'c163_post_handoff_activation_observation_complete' => false,
            'controlled_completion_lock_valid' => false,
            'controlled_completion_path' => '',
            'controlled_completion_hash' => '',
            'controlled_completion_file_sha1' => '',
            'controlled_completion_record_count' => 0,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed_next' => false,
            'watchlist_function_used' => '',
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => '',
            'watchlist_function_primary_candidate_observed' => false,
            'watchlist_function_backup_candidate_observed' => false,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_observation_result_reviewed' => false,
            'backup_candidate_observation_result_reviewed' => false,
            'comparator_candidate_observation_result_reviewed' => false,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => false,
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
            'c163_is_same_post_handoff_contract' => true,
            'c163_activation_observation_result_review_only' => true,
            'c163_controlled_completion_only' => true,
            'c163_not_publication' => true,
            'c163_not_unrestricted_publication' => true,
            'c163_not_plan_confirm_mutation' => true,
            'c163_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function passingTopLevelState(array $observation, array $options): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_pass' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed' => true,
            'post_handoff_activation_observation_result_confirmed' => (bool) ($options['post_handoff_activation_observation_result_confirmed'] ?? false),
            'post_handoff_activation_observation_result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'post_handoff_activation_observation_result_stable' => true,
            'controlled_watchlist_function_observation_result_reviewed' => true,
            'c163_post_handoff_activation_observation_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_observation_complete_confirmed'] ?? false),
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'post_handoff_activation_observed' => true,
            'controlled_watchlist_function_observed' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'c163_post_handoff_activation_observation_lock_valid' => true,
            'c163_plan_confirm_completion_post_handoff_activation_observation_result_review_valid' => true,
            'c163_post_handoff_activation_observation_convert_from_json_pass' => true,
            'c163_post_handoff_activation_observation_complete' => true,
            'controlled_completion_lock_valid' => (bool) ($observation['controlled_completion_lock_valid'] ?? false),
            'controlled_completion_path' => (string) ($observation['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($observation['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($observation['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($observation['controlled_completion_record_count'] ?? 0),
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed_next' => true,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION_USED,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => (string) ($observation['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => true,
            'watchlist_function_backup_candidate_observed' => true,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_observation_result_reviewed' => true,
            'backup_candidate_observation_result_reviewed' => true,
            'comparator_candidate_observation_result_reviewed' => false,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => false,
            'a01_remains_comparator_only' => true,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($observation['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($observation['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($observation['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($observation['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($observation['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($observation['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($observation['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($observation['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($observation['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($observation['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c163_is_same_post_handoff_contract' => true,
            'c163_activation_observation_result_review_only' => true,
            'c163_controlled_completion_only' => true,
            'c163_not_publication' => true,
            'c163_not_unrestricted_publication' => true,
            'c163_not_plan_confirm_mutation' => true,
            'c163_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $observation = is_array($load['payload']) ? $load['payload'] : [];

        return array_merge($artifact, [
            'operator_approval_validation_summary' => $this->operatorApprovalValidationSummary($options),
            'result_review_confirmation_summary' => $this->resultReviewConfirmationSummary($options, $pass),
            'temporary_negative_artifact_guard_summary' => $this->temporaryNegativeArtifactGuardSummary((array) ($options['temporary_negative_artifact_paths'] ?? [])),
            'c163_post_handoff_activation_observation_lock_validation_summary' => $this->lockValidationSummary($load),
            'c163_post_handoff_activation_observation_carry_forward_summary' => $this->carryForwardSummary($observation),
            'watchlist_function_observation_result_summary' => $this->watchlistFunctionObservationResultSummary($observation, $pass),
            'plan_confirm_completion_post_handoff_activation_observation_result_guard_summary' => $this->observationResultGuardSummary($observation, $pass),
            'candidate_observation_result_scorecard' => $this->candidateObservationResultScorecard($pass),
            'c163_post_handoff_activation_observation_result_decision' => $this->observationResultDecision($pass, $options),
            'next_plan_confirm_completion_post_handoff_activation_decision' => $this->nextActivationDecision($pass),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_manifest' => $this->observationResultManifest($observation, $pass, $options, $load),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_checklist' => $this->observationResultChecklist($pass, $options),
            'publication_plan_confirm_safety_summary' => $this->publicationPlanConfirmSafetySummary($observation),
            'documentation_hygiene_guard_summary' => $this->documentationHygieneGuardSummary($load),
            'progress_summary' => $this->progressSummary($pass),
            'planned_next_summary' => $this->plannedNextSummary($pass),
            'diagnostics' => $this->diagnostics(),
        ]);
    }

    private function operatorApprovalValidationSummary(array $options): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
        ];
    }

    private function resultReviewConfirmationSummary(array $options, bool $pass): array
    {
        return [
            'result_review_confirmation_required' => true,
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'post_handoff_activation_observation_result_confirmation_required' => true,
            'post_handoff_activation_observation_result_confirmed' => (bool) ($options['post_handoff_activation_observation_result_confirmed'] ?? false),
            'c163_post_handoff_activation_observation_complete_confirmation_required' => true,
            'c163_post_handoff_activation_observation_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_observation_complete_confirmed'] ?? false),
            'post_handoff_activation_observation_confirmation_required' => true,
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'result_review_confirmation_valid' => $pass,
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

    private function carryForwardSummary(array $observation): array
    {
        return [
            'validation_completed' => true,
            'source_run_code' => (string) ($observation['run_code'] ?? ''),
            'source_status' => (string) ($observation['status'] ?? ''),
            'source_next_step_recommendation' => (string) ($observation['next_step_recommendation'] ?? ''),
            'source_observation_complete' => $this->observationComplete($observation),
            'post_handoff_activation_observed' => (bool) ($observation['post_handoff_activation_observed'] ?? false),
            'controlled_watchlist_function_observed' => (bool) ($observation['controlled_watchlist_function_observed'] ?? false),
            'controlled_completion_path' => (string) ($observation['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($observation['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($observation['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($observation['controlled_completion_record_count'] ?? 0),
        ];
    }

    private function watchlistFunctionObservationResultSummary(array $observation, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'watchlist_function_used' => (string) ($observation['watchlist_function_used'] ?? ''),
            'expected_watchlist_function_used' => self::WATCHLIST_FUNCTION_USED,
            'watchlist_function_runtime_mode' => (string) ($observation['watchlist_function_runtime_mode'] ?? ''),
            'watchlist_function_observation_result_stable' => $this->watchlistFunctionResultStable($observation),
            'watchlist_function_observation_result_reviewed' => $pass,
            'runtime_bridge_active_observed' => (bool) ($observation['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active_observed' => (bool) ($observation['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled_observed' => (bool) ($observation['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed_observed' => (bool) ($observation['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'primary_candidate_result_reviewed' => $pass,
            'backup_candidate_result_reviewed' => $pass,
            'comparator_candidate_result_reviewed' => false,
            'official_output_published' => false,
        ];
    }

    private function observationResultGuardSummary(array $observation, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'source_c163_observation_next_recommendation_matches' => $this->observationNextRecommendationMatches($observation),
            'source_c163_observation_complete' => $this->observationComplete($observation),
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($observation),
            'source_watchlist_function_result_stable' => $this->watchlistFunctionResultStable($observation),
            'post_handoff_activation_observation_result_review_pass' => $pass,
            'post_handoff_activation_observation_result_controlled_only' => true,
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'operator_go_no_go_review_allowed_next' => $pass,
        ];
    }

    private function candidateObservationResultScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'watchlist_function_observed' => $pass,
                'observation_result_reviewed' => $pass,
                'ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'watchlist_function_observed' => $pass,
                'observation_result_reviewed' => $pass,
                'ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'watchlist_function_observed' => false,
                'observation_result_reviewed' => false,
                'a01_remains_comparator_only' => true,
                'ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => false,
            ],
        ];
    }

    private function observationResultDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'post_handoff_activation_observation_result_confirmed' => (bool) ($options['post_handoff_activation_observation_result_confirmed'] ?? false),
            'c163_post_handoff_activation_observation_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_observation_complete_confirmed'] ?? false),
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'controlled_watchlist_function_result_reviewed' => $pass,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'post_handoff_activation_observation_result_go_decision' => $pass ? 'POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_GO' : 'NO_GO',
            'decision_scope' => $pass
                ? 'C163 controlled post-handoff activation observation result is ready for operator go/no-go review.'
                : 'C163 post-handoff activation observation result did not pass.',
        ];
    }

    private function nextActivationDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::NEXT_OPERATOR_RECOMMENDATION : self::RUN_CODE,
            'next_scope' => $pass ? 'post-handoff activation operator go/no-go review only' : 'targeted C163 observation result repair',
            'next_is_concrete' => $pass,
            'c163_post_handoff_activation_observation_result_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function observationResultManifest(array $observation, bool $pass, array $options, array $load): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_observation_result_review',
            'source_artifact' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'post_handoff_activation_observation_result_confirmed' => (bool) ($options['post_handoff_activation_observation_result_confirmed'] ?? false),
            'c163_post_handoff_activation_observation_complete' => $this->observationComplete($observation),
            'post_handoff_activation_observation_result_go_decision' => $pass ? 'POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_GO' : 'NO_GO',
            'ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review' => $pass,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION_USED,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => (string) ($observation['watchlist_function_source_artifact'] ?? ''),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_path' => (string) ($observation['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($observation['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($observation['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($observation['controlled_completion_record_count'] ?? 0),
            'post_handoff_activation_observation_result_controlled_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'activation_observation_result_used_for_free_publication' => false,
            'activation_observation_result_used_for_plan_confirm_mutation' => false,
            'activation_observation_result_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function observationResultChecklist(bool $pass, array $options): array
    {
        return [
            'post_handoff_activation_observation_result_reviewed' => $pass,
            'c163_post_handoff_activation_observation_source_lock_reviewed' => $pass,
            'c163_post_handoff_activation_observation_complete_reviewed' => $pass,
            'result_review_required' => true,
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'post_handoff_activation_observation_result_confirmed' => (bool) ($options['post_handoff_activation_observation_result_confirmed'] ?? false),
            'c163_post_handoff_activation_observation_complete_confirmed' => (bool) ($options['c163_post_handoff_activation_observation_complete_confirmed'] ?? false),
            'post_handoff_activation_observation_confirmed' => (bool) ($options['post_handoff_activation_observation_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'controlled_watchlist_function_result_required' => true,
            'activation_observation_result_review_only' => true,
            'controlled_observation_result_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c163_activation_observation_result' => false,
            'post_handoff_activation_operator_go_no_go_review_required_next' => $pass,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $observation): array
    {
        return [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($observation),
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
            'observation_artifact_not_modified' => true,
            'observation_result_review_is_not_free_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'current' => self::RUN_CODE,
            'current_topic_number' => 'C163',
            'current_topic_complete' => $pass,
            'completed_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW',
            'previous_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW',
            'next_topic_number' => 'C163',
            'next_topic' => $pass ? self::NEXT_OPERATOR_RECOMMENDATION : self::RUN_CODE,
            'topic_numbering_rule' => 'Keep C163 while the post-handoff activation path is still progressing.',
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NEXT_OPERATOR_RECOMMENDATION : self::RUN_CODE,
            'planned_next_action' => $pass ? 'Run same-topic C163 operator go/no-go review for the controlled post-handoff activation path; do not publish freely or mutate PLAN/CONFIRM.' : 'Resolve C163 observation result rejection and rerun this result review.',
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'runtime_family' => 'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion',
            'review_layer' => 'post_handoff_activation_observation_result',
            'source_layer' => 'c163_post_handoff_activation_observation',
            'next_layer' => 'post_handoff_activation_operator_go_no_go_review',
            'watchlist_function_reviewed' => self::WATCHLIST_FUNCTION_USED,
            'candidate_policy' => 'E02 primary, B01 backup, A01 comparator only',
            'publication_policy' => 'free publication and unrestricted publication stay locked',
            'plan_confirm_policy' => 'PLAN/CONFIRM remains unchanged and no live rollout is authorized',
        ];
    }

    private function observationComplete(array $observation): bool
    {
        foreach (self::REQUIRED_OBSERVATION_TRUE_FIELDS as $field) {
            if (($observation[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_OBSERVATION_FALSE_FIELDS as $field) {
            if (($observation[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($observation['topic_code'] ?? null) !== 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION') {
            return false;
        }
        if (($observation['topic_stage'] ?? null) !== 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_REVIEW') {
            return false;
        }
        if ((int) ($observation['controlled_completion_record_count'] ?? 0) !== 2) {
            return false;
        }
        if (trim((string) ($observation['controlled_completion_hash'] ?? '')) === '' || trim((string) ($observation['controlled_completion_file_sha1'] ?? '')) === '') {
            return false;
        }
        if ($this->valueAt($observation, ['c163_post_handoff_activation_observation_decision', 'review_valid']) !== true) {
            return false;
        }
        if ($this->valueAt($observation, ['c163_post_handoff_activation_observation_decision', 'post_handoff_activation_observation_go_decision']) !== 'POST_HANDOFF_ACTIVATION_OBSERVATION_GO') {
            return false;
        }
        if ($this->valueAt($observation, ['next_plan_confirm_completion_post_handoff_activation_decision', 'c163_post_handoff_activation_observation_complete']) !== true) {
            return false;
        }
        if ($this->valueAt($observation, ['next_plan_confirm_completion_post_handoff_activation_decision', 'free_publication_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($observation, ['next_plan_confirm_completion_post_handoff_activation_decision', 'plan_confirm_mutation_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($observation, ['next_plan_confirm_completion_post_handoff_activation_decision', 'live_plan_confirm_rollout_allowed_next']) !== false) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'post_handoff_activation_observation_controlled_only']) !== true) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review']) !== true) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'activation_observation_used_for_free_publication']) !== false) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'activation_observation_used_for_plan_confirm_mutation']) !== false) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'activation_observation_used_for_live_plan_confirm_rollout']) !== false) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'official_weekly_swing_stock_recommendations']) !== []) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_checklist', 'controlled_observation_only']) !== true) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_checklist', 'post_handoff_activation_observation_result_review_required_next']) !== true) {
            return false;
        }
        if ($this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_checklist', 'weekly_swing_stock_recommendation_free_published_in_c163_activation_observation']) !== false) {
            return false;
        }

        return true;
    }

    private function watchlistFunctionResultStable(array $observation): bool
    {
        return ($observation['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION_USED
            && ($observation['watchlist_function_runtime_mode'] ?? null) === self::WATCHLIST_FUNCTION_RUNTIME_MODE
            && ($observation['controlled_watchlist_function_observed'] ?? null) === true
            && ($observation['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($observation['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($observation['watchlist_function_comparator_candidate_observed'] ?? null) === false
            && ($observation['runtime_bridge_active'] ?? null) === true
            && ($observation['weekly_swing_watchlist_runtime_active'] ?? null) === true
            && ($observation['weekly_swing_watchlist_live_output_enabled'] ?? null) === true
            && ($observation['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? null) === true
            && ($observation['weekly_swing_watchlist_official_output_published'] ?? null) === false;
    }

    private function observationNextRecommendationMatches(array $observation): bool
    {
        return ($observation['next_step_recommendation'] ?? null) === self::EXPECTED_C163_OBSERVATION_NEXT_RECOMMENDATION
            && $this->valueAt($observation, ['next_plan_confirm_completion_post_handoff_activation_decision', 'next_recommendation']) === self::EXPECTED_C163_OBSERVATION_NEXT_RECOMMENDATION
            && $this->valueAt($observation, ['planned_next_summary', 'planned_next_review']) === self::EXPECTED_C163_OBSERVATION_NEXT_RECOMMENDATION;
    }

    private function publicationAndPlanGuardClean(array $observation): bool
    {
        foreach (self::PUBLICATION_AND_PLAN_GUARD_FALSE_FIELDS as $field) {
            if (($observation[$field] ?? null) !== false) {
                return false;
            }
        }
        foreach ([
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'activation_observation_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'activation_observation_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_manifest', 'activation_observation_used_for_live_plan_confirm_rollout'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_official_output_published'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_unrestricted_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'plan_confirm_mutated'],
            ['publication_plan_confirm_safety_summary', 'live_plan_confirm_rollout_executed'],
            ['next_plan_confirm_completion_post_handoff_activation_decision', 'free_publication_allowed_next'],
            ['next_plan_confirm_completion_post_handoff_activation_decision', 'plan_confirm_mutation_allowed_next'],
            ['next_plan_confirm_completion_post_handoff_activation_decision', 'live_plan_confirm_rollout_allowed_next'],
        ] as $path) {
            if ($this->valueAt($observation, $path) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $observation): bool
    {
        return ($observation['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($observation['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($observation['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($observation['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review'] ?? null) === true
            && ($observation['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review'] ?? null) === true
            && ($observation['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_result_review'] ?? null) === false
            && ($observation['a01_remains_comparator_only'] ?? null) === true
            && ($observation['a01_promoted'] ?? false) === false;
    }

    private function failureAttributionSummary(array $failures): array
    {
        return [
            'failure_count' => count($failures),
            'failures' => $failures,
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_OPERATOR_OR_OBSERVATION_RESULT_LOCK',
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
            'c163_plan_confirm_completion_post_handoff_activation_observation' => [
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
            'expected_c163_post_handoff_activation_observation_hash' => $load['expected_hash'],
            'actual_c163_post_handoff_activation_observation_hash' => $load['actual_hash'],
            'c163_post_handoff_activation_observation_hash_match' => $load['hash_match'],
            'expected_c163_post_handoff_activation_observation_file_sha1' => $load['expected_file_sha1'],
            'actual_c163_post_handoff_activation_observation_file_sha1' => $load['actual_file_sha1'],
            'c163_post_handoff_activation_observation_file_sha1_match' => $load['file_sha1_match'],
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
        $artifact['diagnostic_conclusion'] = 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED';
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
