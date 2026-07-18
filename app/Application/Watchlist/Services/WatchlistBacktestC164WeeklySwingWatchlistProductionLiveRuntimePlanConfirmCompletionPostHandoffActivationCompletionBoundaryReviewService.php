<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewService
{
    public const RUN_CODE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-81 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW';

    public const DEFAULT_C163_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C163_FINALIZATION_HASH = 'e7a4e300eea57aa5f28a87e5cceb297fd92c195a';
    public const DEFAULT_EXPECTED_C163_FINALIZATION_FILE_SHA1 = '450DC99CAC858CBE08D4E2FB32BC4D9D2F1845B9';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';

    private const EXPECTED_C163_FINALIZATION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_HANDOFF_ACTIVATION_CLOSED_READY_FOR_COMPLETION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C163_FINALIZATION_PHASE_LABEL = 'PR-80 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C163_FINALIZATION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C164_COMPLETION_EXECUTION_RECOMMENDATION = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION';

    private const PASS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_BOUNDARY_CONFIRMATION_MISSING';
    private const C163_TOPIC_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_TOPIC_COMPLETE_CONFIRMATION_MISSING';
    private const POST_HANDOFF_ACTIVATION_CLOSED_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_CLOSED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C163_FINALIZATION_LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const C163_FINALIZATION_FILE_SHA1_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const C163_FINALIZATION_CONVERT_FROM_JSON_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C163_FINALIZATION_STATUS_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_STATUS_MISMATCH';
    private const C163_FINALIZATION_PHASE_LABEL_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_PHASE_LABEL_MISMATCH';
    private const C163_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C163_FINALIZATION_STATE_INVALID_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C163_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass',
        'operator_go_decision',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'post_handoff_activation_finalization_confirmed',
        'post_handoff_activation_closed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed',
        'post_handoff_activation_observation_result_stable',
        'controlled_watchlist_function_observation_result_reviewed',
        'post_handoff_activation_observation_result_confirmed',
        'post_handoff_activation_observed',
        'controlled_watchlist_function_observed',
        'controlled_completion_lock_valid',
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
        'c163_operator_go_no_go_lock_valid',
        'c163_operator_go_no_go_review_valid',
        'c163_operator_go_no_go_convert_from_json_pass',
        'c163_observation_result_review_lock_valid',
        'c163_post_handoff_activation_observation_result_review_valid',
        'c163_observation_result_review_convert_from_json_pass',
        'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review',
        'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review',
        'a01_remains_comparator_only',
        'c163_is_same_post_handoff_contract',
        'c163_activation_go_decision_finalization_review_only',
        'c163_controlled_completion_only',
        'c163_not_publication',
        'c163_not_unrestricted_publication',
        'c163_not_plan_confirm_mutation',
        'c163_not_live_plan_confirm_rollout',
        'c163_topic_complete_after_finalization',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C163_FALSE_FIELDS = [
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c164-*completion-boundary*-test.json',
        'storage/app/watchlist/backtest/c164-*negative-*-test.json',
        'storage/app/watchlist/backtest/c164-*missing-*-test.json',
        'storage/app/watchlist/backtest/c164-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c164-*invalid-*-test.json',
        'storage/app/watchlist/backtest/.tmp-c164-*negative-*-test.json',
    ];

    public function execute(
        string $c163FinalizationArtifact = self::DEFAULT_C163_FINALIZATION_ARTIFACT,
        string $expectedC163FinalizationHash = self::DEFAULT_EXPECTED_C163_FINALIZATION_HASH,
        string $expectedC163FinalizationFileSha1 = self::DEFAULT_EXPECTED_C163_FINALIZATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c163FinalizationArtifact, $expectedC163FinalizationHash, $expectedC163FinalizationFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C163_FINALIZATION_LOCK_MISMATCH_STATUS, 'C163 GO decision finalization artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c163_go_decision_finalization_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C163_FINALIZATION_CONVERT_FROM_JSON_STATUS, 'C163 GO decision finalization artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C163_FINALIZATION_LOCK_MISMATCH_STATUS, 'C163 GO decision finalization artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C163_FINALIZATION_FILE_SHA1_MISMATCH_STATUS, 'C163 GO decision finalization file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c163 = $load['payload'];
        if (($c163['status'] ?? null) !== self::EXPECTED_C163_FINALIZATION_STATUS || ($c163['reason_code'] ?? null) !== self::EXPECTED_C163_FINALIZATION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_FINALIZATION_STATUS_MISMATCH_STATUS, 'C163 finalization status/reason is not completion-boundary ready.', $outputPath, $overwrite);
        }
        if (($c163['phase_label'] ?? null) !== self::EXPECTED_C163_FINALIZATION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_FINALIZATION_PHASE_LABEL_MISMATCH_STATUS, 'C163 finalization phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c163NextRecommendationMatches($c163)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C163 finalization next recommendation is not C164 post-handoff activation completion boundary review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($c163)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C163 finalization evidence has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c163FinalizationComplete($c163)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_FINALIZATION_STATE_INVALID_STATUS, 'C163 finalization evidence is incomplete for C164 completion boundary.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c163)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C163 finalization candidate scope does not match locked C164 completion boundary scope.', $outputPath, $overwrite);
        }
        if (! $this->watchlistFunctionScopeMatches($c163)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS, 'C163 finalization watchlist function is not the locked controlled live recommendation generation function.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C164 completion boundary requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['completion_boundary_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::BOUNDARY_CONFIRMATION_MISSING_STATUS, 'C164 requires --completion-boundary-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c163_topic_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_TOPIC_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C164 requires --c163-topic-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_closed_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::POST_HANDOFF_ACTIVATION_CLOSED_CONFIRMATION_MISSING_STATUS, 'C164 requires --post-handoff-activation-closed-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C164 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C164 requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C164 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C164 clears the post-handoff activation completion boundary from the locked C163 GO decision finalization artifact. C164 continues to completion execution only; free publication, unrestricted publication, PLAN/CONFIRM mutation, activated-catalog read, and live rollout remain locked.';
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C164_COMPLETION_EXECUTION_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-81',
            'internal_checkpoint' => 'C164',
            'topic_code' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW',
            'status' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'reason_code' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass' => false,
            'post_handoff_activation_completion_boundary_cleared' => false,
            'completion_boundary_cleared' => false,
            'completion_boundary_confirmed' => false,
            'c163_topic_complete_confirmed' => false,
            'post_handoff_activation_closed_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'boundary_go_decision' => 'NO_GO',
            'operator_decision' => 'NO_GO',
            'operator_go_decision' => false,
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'post_handoff_activation_closed' => false,
            'c163_topic_complete_after_finalization' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created' => false,
            'c163_go_decision_finalization_lock_valid' => false,
            'c163_post_handoff_activation_go_decision_finalization_valid' => false,
            'c163_go_decision_finalization_convert_from_json_pass' => false,
            'controlled_completion_lock_valid' => false,
            'controlled_completion_path' => null,
            'controlled_completion_hash' => null,
            'controlled_completion_file_sha1' => null,
            'controlled_completion_record_count' => 0,
            'watchlist_function_used' => '',
            'watchlist_function_runtime_mode' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT',
            'watchlist_function_source_artifact' => '',
            'watchlist_function_primary_candidate_observed' => false,
            'watchlist_function_backup_candidate_observed' => false,
            'watchlist_function_comparator_candidate_observed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution' => false,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution' => false,
            'a01_remains_comparator_only' => true,
            'c164_is_post_handoff_activation_completion_contract' => true,
            'c164_not_c163_activation_repeat' => true,
            'c164_completion_boundary_review_only' => true,
            'c164_controlled_completion_only' => true,
            'c164_not_publication' => true,
            'c164_not_unrestricted_publication' => true,
            'c164_not_plan_confirm_mutation' => true,
            'c164_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'next_step_recommendation' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'message' => '',
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass' => true,
            'post_handoff_activation_completion_boundary_cleared' => true,
            'completion_boundary_cleared' => true,
            'completion_boundary_confirmed' => true,
            'c163_topic_complete_confirmed' => true,
            'post_handoff_activation_closed_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'boundary_go_decision' => 'BOUNDARY_CLEARED_GO',
            'operator_decision' => 'GO',
            'operator_go_decision' => true,
            'operator_go_decision_confirmed' => true,
            'go_decision_finalized' => true,
            'post_handoff_activation_closed' => true,
            'c163_topic_complete_after_finalization' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created' => true,
            'c163_go_decision_finalization_lock_valid' => true,
            'c163_post_handoff_activation_go_decision_finalization_valid' => true,
            'c163_go_decision_finalization_convert_from_json_pass' => true,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution' => true,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c163 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryPaths = array_values((array) ($options['temporary_negative_artifact_paths'] ?? []));

        $artifact['c163_go_decision_finalization_lock_validation_summary'] = $this->c163FinalizationLockValidationSummary($load);
        $artifact['c163_go_decision_finalization_carry_forward_summary'] = $this->c163FinalizationCarryForwardSummary($c163);
        $artifact['plan_confirm_completion_post_handoff_activation_completion_boundary_guard_summary'] = $this->completionBoundaryGuardSummary($c163, $pass, $options);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c163);
        $artifact['watchlist_function_scope_summary'] = $this->watchlistFunctionScopeSummary($c163);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryPaths);
        $artifact['c164_completion_boundary_decision'] = $this->completionBoundaryDecision($pass, $options);
        $artifact['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision'] = $this->nextCompletionExecutionDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest'] = $this->completionBoundaryManifest($c163, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_checklist'] = $this->completionBoundaryChecklist($pass, $options);
        $artifact['c164_candidate_post_handoff_activation_completion_boundary_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($c163);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);

        $artifact = array_merge($artifact, [
            'operator_decision' => $pass ? 'GO' : (string) ($c163['operator_decision'] ?? 'NO_GO'),
            'operator_go_decision' => $pass ? true : (bool) ($c163['operator_go_decision'] ?? false),
            'operator_go_decision_confirmed' => $pass ? true : (bool) ($c163['operator_go_decision_confirmed'] ?? false),
            'go_decision_finalized' => $pass ? true : (bool) ($c163['go_decision_finalized'] ?? false),
            'post_handoff_activation_closed' => $pass ? true : (bool) ($c163['post_handoff_activation_closed'] ?? false),
            'c163_topic_complete_after_finalization' => $pass ? true : (bool) ($c163['c163_topic_complete_after_finalization'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created' => (bool) ($c163['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed' => (bool) ($c163['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed'] ?? false),
            'post_handoff_activation_observation_result_stable' => (bool) ($c163['post_handoff_activation_observation_result_stable'] ?? false),
            'controlled_watchlist_function_observation_result_reviewed' => (bool) ($c163['controlled_watchlist_function_observation_result_reviewed'] ?? false),
            'post_handoff_activation_observation_result_confirmed' => (bool) ($c163['post_handoff_activation_observation_result_confirmed'] ?? false),
            'post_handoff_activation_observed' => (bool) ($c163['post_handoff_activation_observed'] ?? false),
            'controlled_watchlist_function_observed' => (bool) ($c163['controlled_watchlist_function_observed'] ?? false),
            'controlled_completion_lock_valid' => (bool) ($c163['controlled_completion_lock_valid'] ?? false),
            'controlled_completion_path' => $c163['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $c163['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $c163['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($c163['controlled_completion_record_count'] ?? 0),
            'watchlist_function_used' => (string) ($c163['watchlist_function_used'] ?? ''),
            'watchlist_function_runtime_mode' => (string) ($c163['watchlist_function_runtime_mode'] ?? 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT'),
            'watchlist_function_source_artifact' => (string) ($c163['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => (bool) ($c163['watchlist_function_primary_candidate_observed'] ?? false),
            'watchlist_function_backup_candidate_observed' => (bool) ($c163['watchlist_function_backup_candidate_observed'] ?? false),
            'watchlist_function_comparator_candidate_observed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($c163['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($c163['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($c163['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($c163['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? true),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($c163['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($c163['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($c163['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c163['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c163['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c163['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c163_go_decision_finalization_lock_valid' => (bool) (($load['hash_match'] ?? false) && ($load['file_sha1_match'] ?? false) && ($load['convert_from_json_pass'] ?? false)),
            'c163_post_handoff_activation_go_decision_finalization_valid' => $this->c163FinalizationComplete($c163),
            'c163_go_decision_finalization_convert_from_json_pass' => (bool) ($load['convert_from_json_pass'] ?? false),
            'primary_candidate_code' => (string) ($c163['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($c163['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($c163['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'a01_remains_comparator_only' => (bool) ($c163['a01_remains_comparator_only'] ?? true),
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
        ]);

        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $flag) {
            $artifact[$flag] = false;
        }

        return $artifact;
    }

    private function c163NextRecommendationMatches(array $c163): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c163, $path) !== self::RUN_CODE) {
                return false;
            }
        }

        return true;
    }

    private function c163FinalizationComplete(array $c163): bool
    {
        foreach (self::REQUIRED_C163_TRUE_FIELDS as $field) {
            if (($c163[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C163_FALSE_FIELDS as $field) {
            if (($c163[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($c163['operator_decision'] ?? null) === 'GO'
            && ($c163['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && $this->valueAt($c163, ['c163_go_decision_finalization_decision', 'review_valid']) === true
            && $this->valueAt($c163, ['c163_go_decision_finalization_decision', 'go_decision_finalized']) === true
            && $this->valueAt($c163, ['c163_go_decision_finalization_decision', 'post_handoff_activation_closed']) === true
            && $this->valueAt($c163, ['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision', 'review_valid']) === true
            && $this->valueAt($c163, ['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($c163, ['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision', 'next_requires_locked_c163_finalization_artifact']) === true
            && $this->valueAt($c163, ['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision', 'topic_number_advances_after_c163_finalization']) === true
            && $this->valueAt($c163, ['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision', 'same_topic_c163_complete']) === true
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest', 'manifest_created']) === true
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest', 'ready_for_post_handoff_activation_completion_boundary_review']) === true
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest', 'go_decision_finalization_artifact_only']) === true
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest', 'go_decision_finalization_used_for_free_publication']) === false
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest', 'go_decision_finalization_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest', 'go_decision_finalization_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_checklist', 'go_decision_finalization_reviewed']) === true
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_checklist', 'artifact_only']) === true
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_checklist', 'ready_for_post_handoff_activation_completion_boundary_review']) === true
            && $this->valueAt($c163, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_checklist', 'weekly_swing_stock_recommendation_free_published_in_c163_finalization']) === false;
    }

    private function publicationAndPlanGuardClean(array $c163): bool
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
        ] as $flag) {
            if (($c163[$flag] ?? null) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $c163): bool
    {
        return ($c163['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c163['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c163['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c163['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review'] ?? null) === true
            && ($c163['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review'] ?? null) === true
            && ($c163['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review'] ?? null) === false
            && ($c163['a01_remains_comparator_only'] ?? null) === true
            && ($c163['a01_promoted'] ?? false) === false
            && ($c163['candidate_promotion_executed'] ?? false) === false
            && ($c163['candidate_rerank_executed'] ?? false) === false
            && ($c163['strategy_retune_executed'] ?? false) === false
            && ($c163['scoring_mutation_executed'] ?? false) === false
            && ($c163['catalog_selection_changed'] ?? false) === false
            && ($c163['runtime_selection_changed'] ?? false) === false;
    }

    private function watchlistFunctionScopeMatches(array $c163): bool
    {
        return ($c163['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($c163['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($c163['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($c163['watchlist_function_comparator_candidate_observed'] ?? null) === false
            && trim((string) ($c163['watchlist_function_source_artifact'] ?? '')) !== ''
            && trim((string) ($c163['controlled_completion_hash'] ?? '')) !== ''
            && (int) ($c163['controlled_completion_record_count'] ?? 0) === 2;
    }

    private function c163FinalizationLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C163_GO_DECISION_FINALIZATION',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
        ];
    }

    private function c163FinalizationCarryForwardSummary(array $c163): array
    {
        return [
            'status' => $c163['status'] ?? null,
            'phase_label' => $c163['phase_label'] ?? null,
            'operator_decision' => $c163['operator_decision'] ?? null,
            'go_decision_finalized' => (bool) ($c163['go_decision_finalized'] ?? false),
            'post_handoff_activation_closed' => (bool) ($c163['post_handoff_activation_closed'] ?? false),
            'c163_topic_complete_after_finalization' => (bool) ($c163['c163_topic_complete_after_finalization'] ?? false),
            'ready_for_post_handoff_activation_completion_boundary_review' => (bool) ($c163['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review'] ?? false),
            'source_next_recommendation' => $c163['next_step_recommendation'] ?? null,
        ];
    }

    private function completionBoundaryGuardSummary(array $c163, bool $pass, array $options): array
    {
        return [
            'validation_completed' => true,
            'c163_go_decision_finalization_valid' => $this->c163FinalizationComplete($c163),
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c163),
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? false),
            'c163_topic_complete_confirmed' => (bool) ($options['c163_topic_complete_confirmed'] ?? false),
            'post_handoff_activation_closed_confirmed' => (bool) ($options['post_handoff_activation_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'completion_boundary_cleared' => $pass,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'free_publication_allowed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $c163): array
    {
        return [
            'candidate_scope_matches' => $this->candidateScopeMatches($c163),
            'primary_candidate_code' => $c163['primary_candidate_code'] ?? null,
            'backup_candidate_code' => $c163['backup_candidate_code'] ?? null,
            'comparator_candidate_code' => $c163['comparator_candidate_code'] ?? null,
            'a01_remains_comparator_only' => (bool) ($c163['a01_remains_comparator_only'] ?? false),
            'candidate_rerank_executed' => (bool) ($c163['candidate_rerank_executed'] ?? false),
            'candidate_promotion_executed' => (bool) ($c163['candidate_promotion_executed'] ?? false),
        ];
    }

    private function watchlistFunctionScopeSummary(array $c163): array
    {
        return [
            'watchlist_function_scope_matches' => $this->watchlistFunctionScopeMatches($c163),
            'watchlist_function_used' => $c163['watchlist_function_used'] ?? null,
            'expected_watchlist_function' => self::WATCHLIST_FUNCTION,
            'primary_candidate_observed' => (bool) ($c163['watchlist_function_primary_candidate_observed'] ?? false),
            'backup_candidate_observed' => (bool) ($c163['watchlist_function_backup_candidate_observed'] ?? false),
            'comparator_candidate_observed' => (bool) ($c163['watchlist_function_comparator_candidate_observed'] ?? false),
            'controlled_completion_record_count' => (int) ($c163['controlled_completion_record_count'] ?? 0),
        ];
    }

    private function operatorApprovalValidationSummary(array $options): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
            'operator_approval_valid' => (bool) ($options['operator_approved'] ?? false) && $reference !== '',
        ];
    }

    private function completionBoundaryDecision(bool $pass, array $options): array
    {
        return [
            'review_pass' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? false),
            'c163_topic_complete_confirmed' => (bool) ($options['c163_topic_complete_confirmed'] ?? false),
            'post_handoff_activation_closed_confirmed' => (bool) ($options['post_handoff_activation_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'next_recommendation' => $pass ? self::C164_COMPLETION_EXECUTION_RECOMMENDATION : 'C164_TARGETED_COMPLETION_BOUNDARY_REPAIR',
        ];
    }

    private function nextCompletionExecutionDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C164_COMPLETION_EXECUTION_RECOMMENDATION : 'C164_TARGETED_COMPLETION_BOUNDARY_REPAIR',
            'next_scope' => 'C164 post-handoff activation completion execution only; still controlled output, no free publication, no PLAN/CONFIRM mutation, no live rollout',
            'same_topic_c164_continues' => $pass,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'requires_locked_c164_boundary_artifact' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function completionBoundaryManifest(array $c163, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_completion_boundary_review',
            'source_artifact' => 'C163_GO_DECISION_FINALIZATION_REVIEW',
            'source_artifact_path' => self::DEFAULT_C163_FINALIZATION_ARTIFACT,
            'source_artifact_hash' => $c163['artifact_hash'] ?? null,
            'source_file_sha1' => strtoupper(self::DEFAULT_EXPECTED_C163_FINALIZATION_FILE_SHA1),
            'source_operator_decision' => $c163['operator_decision'] ?? null,
            'c163_topic_complete_after_finalization' => (bool) ($c163['c163_topic_complete_after_finalization'] ?? false),
            'post_handoff_activation_closed' => (bool) ($c163['post_handoff_activation_closed'] ?? false),
            'completion_boundary_review_pass' => $pass,
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? false),
            'ready_for_post_handoff_activation_completion_execution' => $pass,
            'completion_boundary_artifact_only' => true,
            'controlled_completion_only' => true,
            'watchlist_function_used' => $c163['watchlist_function_used'] ?? null,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_completion_execution' => $pass,
            'backup_candidate_ready_for_completion_execution' => $pass,
            'comparator_candidate_ready_for_completion_execution' => false,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'completion_boundary_used_for_free_publication' => false,
            'completion_boundary_used_for_plan_confirm_mutation' => false,
            'completion_boundary_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function completionBoundaryChecklist(bool $pass, array $options): array
    {
        return [
            'completion_boundary_reviewed' => true,
            'c163_go_decision_finalization_source_lock_reviewed' => true,
            'c163_topic_complete_carried_forward' => true,
            'completion_boundary_required' => true,
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? false),
            'c163_topic_complete_confirmed' => (bool) ($options['c163_topic_complete_confirmed'] ?? false),
            'post_handoff_activation_closed_confirmed' => (bool) ($options['post_handoff_activation_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_boundary_gate_required' => true,
            'negative_c163_topic_complete_gate_required' => true,
            'negative_post_handoff_activation_closed_gate_required' => true,
            'negative_plan_confirm_unchanged_gate_required' => true,
            'negative_no_live_rollout_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'completion_boundary_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c164_boundary' => false,
            'ready_for_post_handoff_activation_completion_execution' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'post_handoff_activation_completion_boundary_review_valid' => $pass,
            'ready_for_post_handoff_activation_completion_execution' => $pass,
            'plan_confirm_mutated' => false,
            'live_rollout_executed' => false,
            'free_published' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c164_role' => 'primary_candidate_ready_for_post_handoff_activation_completion_execution',
                'primary_candidate_ready_for_post_handoff_activation_completion_execution' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c164_role' => 'backup_candidate_ready_for_post_handoff_activation_completion_execution',
                'backup_candidate_ready_for_post_handoff_activation_completion_execution' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c164_role' => 'comparator_only_candidate',
                'ready_for_post_handoff_activation_completion_execution' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $c163): array
    {
        return [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c163),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'c164_boundary_review_document_required' => true,
            'c164_operator_validation_commands_document_required' => true,
            'source_artifact_path_recorded' => (string) ($load['path'] ?? '') !== '',
            'source_artifact_hash_recorded' => (string) ($load['actual_hash'] ?? '') !== '',
            'source_file_sha1_recorded' => (string) ($load['actual_file_sha1'] ?? '') !== '',
            'no_review_name_reuse_without_new_topic' => true,
            'c164_topic_number_retained_until_completion_finalization' => true,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'current_stage' => self::RUN_CODE,
            'what_was_created' => [
                'C164 completion boundary service',
                'C164 completion boundary command',
                'C164 completion boundary artifact',
                'C164 completion boundary tests',
                'C164 completion boundary audit documentation',
            ],
            'what_this_stage_does' => 'Clears the boundary from locked C163 post-handoff activation finalization into C164 completion execution.',
            'what_this_stage_does_not_do' => [
                'does not repeat C163 activation',
                'does not publish weekly swing output freely',
                'does not unlock unrestricted publication',
                'does not mutate PLAN/CONFIRM',
                'does not make PLAN/CONFIRM read activated catalog',
                'does not execute live PLAN/CONFIRM rollout',
            ],
            'stage_passed' => $pass,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C164_COMPLETION_EXECUTION_RECOMMENDATION : 'C164_TARGETED_COMPLETION_BOUNDARY_REPAIR',
            'planned_next_scope' => 'C164 post-handoff activation completion execution, still controlled and same C164 topic.',
            'same_topic_c164_continues' => $pass,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'planned_next_required_inputs' => [
                'locked C164 completion boundary artifact hash',
                'locked C164 completion boundary file SHA1',
                'locked C163 GO decision finalization artifact hash',
                'PLAN/CONFIRM unchanged confirmation',
                'live rollout still disabled',
                'free publication still disabled',
            ],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C164 boundary validates C163 GO decision finalization artifact_hash and file SHA1 locks before boundary clearance.',
            'C164 boundary validates C163 topic completion, post-handoff activation closure, candidate scope, and controlled watchlist function scope.',
            'C164 boundary requires operator approval plus boundary, C163 complete, post-handoff activation closed, PLAN/CONFIRM unchanged, no-live-rollout, and free-publication lock confirmations.',
            'C164 boundary keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C164 boundary advances only to C164 completion execution and does not advance the topic number.',
            'C164 boundary does not mutate PLAN/CONFIRM, read activated catalog, execute live rollout, free-publish recommendations, or allow unrestricted publication.',
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

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c163_go_decision_finalization' => [
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
            'expected_c163_go_decision_finalization_hash' => $load['expected_hash'],
            'actual_c163_go_decision_finalization_hash' => $load['actual_hash'],
            'c163_go_decision_finalization_hash_match' => $load['hash_match'],
            'expected_c163_go_decision_finalization_file_sha1' => $load['expected_file_sha1'],
            'actual_c163_go_decision_finalization_file_sha1' => $load['actual_file_sha1'],
            'c163_go_decision_finalization_file_sha1_match' => $load['file_sha1_match'],
        ];
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
                $actualHash = $decoded['artifact_hash'] ?? null;
            }
            $actualFileSha1 = strtoupper(sha1($raw));
        }

        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $actualHash !== null && hash_equals($expectedHash, (string) $actualHash),
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $actualFileSha1 !== null && strtoupper($expectedFileSha1) === $actualFileSha1,
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

    private function failureAttributionSummary(array $status): array
    {
        $failures = array_values(array_filter($status));

        return [
            'failure_count' => count($failures),
            'failures' => $failures,
            'attribution' => $failures === [] ? 'NONE' : 'C164_COMPLETION_BOUNDARY_GUARD',
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
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
