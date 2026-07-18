<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionService
{
    public const RUN_CODE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION';
    public const PHASE_LABEL = 'PR-82 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION';
    public const ARTIFACT_TYPE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION';

    public const DEFAULT_C164_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review.json';
    public const DEFAULT_EXPECTED_C164_BOUNDARY_HASH = '997bb3cc6f5565da92438a2afaca441bb50977b4';
    public const DEFAULT_EXPECTED_C164_BOUNDARY_FILE_SHA1 = '2EBE74B5E40E53C60456A4110DF41A29B1D3E1A6';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const WATCHLIST_FUNCTION_RUNTIME_MODE = 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT';

    private const EXPECTED_C164_BOUNDARY_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP';
    private const EXPECTED_C164_BOUNDARY_PHASE_LABEL = 'PR-81 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW';
    private const EXPECTED_C164_BOUNDARY_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C164_RESULT_REVIEW_RECOMMENDATION = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW';

    private const PASS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_PASSED_COMPLETION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const COMPLETION_EXECUTION_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_COMPLETION_EXECUTION_CONFIRMATION_MISSING';
    private const C164_BOUNDARY_CLEARED_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_BOUNDARY_CLEARED_CONFIRMATION_MISSING';
    private const POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_CONFIRMATION_MISSING';
    private const CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C164_BOUNDARY_LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_ARTIFACT_LOCK_MISMATCH';
    private const C164_BOUNDARY_FILE_SHA1_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_FILE_SHA1_LOCK_MISMATCH';
    private const C164_BOUNDARY_CONVERT_FROM_JSON_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C164_BOUNDARY_STATUS_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_STATUS_MISMATCH';
    private const C164_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_PHASE_LABEL_MISMATCH';
    private const C164_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH';
    private const C164_BOUNDARY_STATE_INVALID_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_C164_COMPLETION_BOUNDARY_STATE_INVALID';
    private const CONTROLLED_COMPLETION_LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_CONTROLLED_COMPLETION_LOCK_MISMATCH';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C164_BOUNDARY_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass',
        'post_handoff_activation_completion_boundary_cleared',
        'completion_boundary_cleared',
        'completion_boundary_confirmed',
        'c163_topic_complete_confirmed',
        'post_handoff_activation_closed_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'operator_go_decision',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'post_handoff_activation_closed',
        'c163_topic_complete_after_finalization',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created',
        'c163_go_decision_finalization_lock_valid',
        'c163_post_handoff_activation_go_decision_finalization_valid',
        'c163_go_decision_finalization_convert_from_json_pass',
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
        'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution',
        'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution',
        'a01_remains_comparator_only',
        'c164_is_post_handoff_activation_completion_contract',
        'c164_not_c163_activation_repeat',
        'c164_completion_boundary_review_only',
        'c164_controlled_completion_only',
        'c164_not_publication',
        'c164_not_unrestricted_publication',
        'c164_not_plan_confirm_mutation',
        'c164_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C164_BOUNDARY_FALSE_FIELDS = [
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution',
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
        'storage/app/watchlist/backtest/c164-*completion-execution*-test.json',
        'storage/app/watchlist/backtest/c164-*completion*-execution*-test.json',
        'storage/app/watchlist/backtest/c164-*negative-*-test.json',
        'storage/app/watchlist/backtest/c164-*missing-*-test.json',
        'storage/app/watchlist/backtest/c164-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c164-*invalid-*-test.json',
        'storage/app/watchlist/backtest/.tmp-c164-*negative-*-test.json',
    ];

    public function execute(
        string $c164BoundaryArtifact = self::DEFAULT_C164_BOUNDARY_ARTIFACT,
        string $expectedC164BoundaryHash = self::DEFAULT_EXPECTED_C164_BOUNDARY_HASH,
        string $expectedC164BoundaryFileSha1 = self::DEFAULT_EXPECTED_C164_BOUNDARY_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c164BoundaryArtifact, $expectedC164BoundaryHash, $expectedC164BoundaryFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C164_BOUNDARY_LOCK_MISMATCH_STATUS, 'C164 completion boundary artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, []);
            $artifact['c164_completion_boundary_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C164_BOUNDARY_CONVERT_FROM_JSON_STATUS, 'C164 completion boundary artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C164_BOUNDARY_LOCK_MISMATCH_STATUS, 'C164 completion boundary artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C164_BOUNDARY_FILE_SHA1_MISMATCH_STATUS, 'C164 completion boundary file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $boundary = $load['payload'];
        if (($boundary['status'] ?? null) !== self::EXPECTED_C164_BOUNDARY_STATUS || ($boundary['reason_code'] ?? null) !== self::EXPECTED_C164_BOUNDARY_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C164_BOUNDARY_STATUS_MISMATCH_STATUS, 'C164 completion boundary status/reason is not completion-execution ready.', $outputPath, $overwrite);
        }
        if (($boundary['phase_label'] ?? null) !== self::EXPECTED_C164_BOUNDARY_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C164_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS, 'C164 completion boundary phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c164BoundaryNextRecommendationMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C164_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C164 completion boundary next recommendation is not C164 completion execution.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C164 boundary already published, unlocked publication, mutated PLAN/CONFIRM, read activated catalog, or executed live rollout.', $outputPath, $overwrite);
        }
        if (! $this->c164BoundaryComplete($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C164_BOUNDARY_STATE_INVALID_STATUS, 'C164 completion boundary evidence is incomplete for completion execution.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C164 completion boundary candidate scope does not match locked completion execution scope.', $outputPath, $overwrite);
        }
        if (! $this->watchlistFunctionScopeMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS, 'C164 boundary watchlist function is not the locked controlled live recommendation generation function.', $outputPath, $overwrite);
        }

        $controlledCompletion = $this->controlledCompletionValidation($boundary);
        if (! $controlledCompletion['valid']) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion);

            return $this->rejected($artifact, self::CONTROLLED_COMPLETION_LOCK_MISMATCH_STATUS, 'Controlled completion artifact lock is missing, mismatched, or outside primary/backup scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion), self::APPROVAL_MISSING_STATUS, 'C164 completion execution requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['completion_execution_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion), self::COMPLETION_EXECUTION_CONFIRMATION_MISSING_STATUS, 'C164 requires --completion-execution-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c164_boundary_cleared_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion), self::C164_BOUNDARY_CLEARED_CONFIRMATION_MISSING_STATUS, 'C164 requires --c164-boundary-cleared-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_completion_boundary_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion), self::POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_CONFIRMATION_MISSING_STATUS, 'C164 requires --post-handoff-activation-completion-boundary-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_completion_only_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion), self::CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING_STATUS, 'C164 requires --controlled-completion-only-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C164 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C164 requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), false, $controlledCompletion), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C164 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, [
                'controlled_completion_validation' => $controlledCompletion,
                'temporary_negative_artifact_paths' => $temporaryNegativePaths,
            ]), false, $controlledCompletion);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, array_merge($options, ['controlled_completion_validation' => $controlledCompletion]), true, $controlledCompletion);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C164 executes post-handoff activation completion from the locked C164 boundary artifact. Controlled weekly swing watchlist output remains limited to primary and backup candidates; free publication, unrestricted publication, PLAN/CONFIRM mutation, activated-catalog reads, and live rollout remain locked.';
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_COMPLETED_READY_FOR_RESULT_REVIEW_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C164_RESULT_REVIEW_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($boundary, $controlledCompletion, $options));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-82',
            'internal_checkpoint' => 'C164',
            'topic_code' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION',
            'status' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_NOT_RUN',
            'reason_code' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => false,
            'post_handoff_activation_completion_execution_completed' => false,
            'completion_execution_confirmed' => false,
            'c164_boundary_cleared_confirmed' => false,
            'post_handoff_activation_completion_boundary_confirmed' => false,
            'controlled_completion_only_confirmed' => false,
            'post_handoff_activation_completion_boundary_cleared' => false,
            'completion_boundary_cleared' => false,
            'completion_boundary_confirmed' => false,
            'c163_topic_complete_confirmed' => false,
            'post_handoff_activation_closed_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'operator_decision' => 'NO_GO',
            'operator_approved' => false,
            'approval_reference' => '',
            'operator_go_decision' => false,
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'post_handoff_activation_closed' => false,
            'c163_topic_complete_after_finalization' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created' => false,
            'c164_completion_boundary_lock_valid' => false,
            'c164_completion_boundary_review_valid' => false,
            'c164_completion_boundary_convert_from_json_pass' => false,
            'controlled_completion_lock_valid' => false,
            'controlled_completion_path' => null,
            'controlled_completion_hash' => null,
            'controlled_completion_file_sha1' => null,
            'controlled_completion_record_count' => 0,
            'watchlist_function_used' => '',
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
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
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => false,
            'a01_remains_comparator_only' => true,
            'c164_is_post_handoff_activation_completion_contract' => true,
            'c164_not_c163_activation_repeat' => true,
            'c164_completion_execution_only' => true,
            'c164_controlled_completion_only' => true,
            'c164_not_publication' => true,
            'c164_not_unrestricted_publication' => true,
            'c164_not_plan_confirm_mutation' => true,
            'c164_not_live_plan_confirm_rollout' => true,
            'c164_topic_number_retained_for_result_review' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_NOT_RUN',
            'next_step_recommendation' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_NOT_READY',
            'message' => '',
        ];
    }

    private function passingTopLevelState(array $boundary, array $controlledCompletion, array $options): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => true,
            'post_handoff_activation_completion_execution_completed' => true,
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'c164_boundary_cleared_confirmed' => (bool) ($options['c164_boundary_cleared_confirmed'] ?? false),
            'post_handoff_activation_completion_boundary_confirmed' => (bool) ($options['post_handoff_activation_completion_boundary_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'post_handoff_activation_completion_boundary_cleared' => true,
            'completion_boundary_cleared' => true,
            'completion_boundary_confirmed' => true,
            'c163_topic_complete_confirmed' => true,
            'post_handoff_activation_closed_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_decision' => 'GO',
            'operator_approved' => true,
            'approval_reference' => trim((string) ($options['approval_reference'] ?? '')),
            'operator_go_decision' => true,
            'operator_go_decision_confirmed' => true,
            'go_decision_finalized' => true,
            'post_handoff_activation_closed' => true,
            'c163_topic_complete_after_finalization' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created' => true,
            'c164_completion_boundary_lock_valid' => true,
            'c164_completion_boundary_review_valid' => true,
            'c164_completion_boundary_convert_from_json_pass' => true,
            'controlled_completion_lock_valid' => true,
            'controlled_completion_path' => (string) ($controlledCompletion['path'] ?? ''),
            'controlled_completion_hash' => (string) ($controlledCompletion['actual_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($controlledCompletion['actual_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($controlledCompletion['record_count'] ?? 0),
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => (string) ($boundary['watchlist_function_source_artifact'] ?? ($controlledCompletion['path'] ?? '')),
            'watchlist_function_primary_candidate_observed' => true,
            'watchlist_function_backup_candidate_observed' => true,
            'watchlist_function_comparator_candidate_observed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? true),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($boundary['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($boundary['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($boundary['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($boundary['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($boundary['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($boundary['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass, array $controlledCompletion): array
    {
        $boundary = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $controlledCompletion = $controlledCompletion !== []
            ? $controlledCompletion
            : (array) ($options['controlled_completion_validation'] ?? $this->controlledCompletionValidation($boundary));
        $temporaryPaths = array_values((array) ($options['temporary_negative_artifact_paths'] ?? []));

        $artifact['c164_completion_boundary_lock_validation_summary'] = $this->c164BoundaryLockValidationSummary($load);
        $artifact['c164_completion_boundary_carry_forward_summary'] = $this->c164BoundaryCarryForwardSummary($boundary);
        $artifact['controlled_completion_lock_validation_summary'] = $controlledCompletion;
        $artifact['plan_confirm_completion_post_handoff_activation_completion_execution_guard_summary'] = $this->completionExecutionGuardSummary($boundary, $controlledCompletion, $pass, $options);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($boundary);
        $artifact['watchlist_function_scope_summary'] = $this->watchlistFunctionScopeSummary($boundary);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryPaths);
        $artifact['c164_completion_execution_decision'] = $this->completionExecutionDecision($pass, $options);
        $artifact['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision'] = $this->nextResultReviewDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest'] = $this->completionExecutionManifest($boundary, $controlledCompletion, $pass, $options, $load);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist'] = $this->completionExecutionChecklist($pass, $options);
        $artifact['c164_candidate_post_handoff_activation_completion_execution_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($boundary);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);

        $artifact = array_merge($artifact, [
            'post_handoff_activation_completion_boundary_cleared' => (bool) ($boundary['post_handoff_activation_completion_boundary_cleared'] ?? false),
            'completion_boundary_cleared' => (bool) ($boundary['completion_boundary_cleared'] ?? false),
            'completion_boundary_confirmed' => (bool) ($boundary['completion_boundary_confirmed'] ?? false),
            'c163_topic_complete_confirmed' => (bool) ($boundary['c163_topic_complete_confirmed'] ?? false),
            'post_handoff_activation_closed_confirmed' => (bool) ($boundary['post_handoff_activation_closed_confirmed'] ?? false),
            'post_handoff_activation_closed' => (bool) ($boundary['post_handoff_activation_closed'] ?? false),
            'c163_topic_complete_after_finalization' => (bool) ($boundary['c163_topic_complete_after_finalization'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => trim((string) ($options['approval_reference'] ?? '')),
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'c164_boundary_cleared_confirmed' => (bool) ($options['c164_boundary_cleared_confirmed'] ?? false),
            'post_handoff_activation_completion_boundary_confirmed' => (bool) ($options['post_handoff_activation_completion_boundary_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created'] ?? false),
            'c164_completion_boundary_lock_valid' => (bool) (($load['hash_match'] ?? false) && ($load['file_sha1_match'] ?? false) && ($load['convert_from_json_pass'] ?? false)),
            'c164_completion_boundary_review_valid' => $this->c164BoundaryComplete($boundary),
            'c164_completion_boundary_convert_from_json_pass' => (bool) ($load['convert_from_json_pass'] ?? false),
            'controlled_completion_lock_valid' => (bool) ($controlledCompletion['valid'] ?? false),
            'controlled_completion_path' => (string) ($controlledCompletion['path'] ?? ($boundary['controlled_completion_path'] ?? '')),
            'controlled_completion_hash' => (string) ($controlledCompletion['actual_hash'] ?? ($boundary['controlled_completion_hash'] ?? '')),
            'controlled_completion_file_sha1' => (string) ($controlledCompletion['actual_file_sha1'] ?? ($boundary['controlled_completion_file_sha1'] ?? '')),
            'controlled_completion_record_count' => (int) ($controlledCompletion['record_count'] ?? ($boundary['controlled_completion_record_count'] ?? 0)),
            'watchlist_function_used' => (string) ($boundary['watchlist_function_used'] ?? ''),
            'watchlist_function_runtime_mode' => (string) ($boundary['watchlist_function_runtime_mode'] ?? self::WATCHLIST_FUNCTION_RUNTIME_MODE),
            'watchlist_function_source_artifact' => (string) ($boundary['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => (bool) ($boundary['watchlist_function_primary_candidate_observed'] ?? false),
            'watchlist_function_backup_candidate_observed' => (bool) ($boundary['watchlist_function_backup_candidate_observed'] ?? false),
            'watchlist_function_comparator_candidate_observed' => (bool) ($boundary['watchlist_function_comparator_candidate_observed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? true),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($boundary['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($boundary['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($boundary['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($boundary['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($boundary['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($boundary['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'primary_candidate_code' => (string) ($boundary['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($boundary['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($boundary['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'a01_remains_comparator_only' => (bool) ($boundary['a01_remains_comparator_only'] ?? true),
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

    private function c164BoundaryNextRecommendationMatches(array $boundary): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($boundary, $path) !== self::RUN_CODE) {
                return false;
            }
        }

        return true;
    }

    private function c164BoundaryComplete(array $boundary): bool
    {
        foreach (self::REQUIRED_C164_BOUNDARY_TRUE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C164_BOUNDARY_FALSE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($boundary['topic_code'] ?? null) === 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION'
            && ($boundary['topic_stage'] ?? null) === 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW'
            && ($boundary['boundary_go_decision'] ?? null) === 'BOUNDARY_CLEARED_GO'
            && ($boundary['operator_decision'] ?? null) === 'GO'
            && (int) ($boundary['controlled_completion_record_count'] ?? 0) === 2
            && trim((string) ($boundary['controlled_completion_path'] ?? '')) !== ''
            && trim((string) ($boundary['controlled_completion_hash'] ?? '')) !== ''
            && trim((string) ($boundary['controlled_completion_file_sha1'] ?? '')) !== ''
            && $this->valueAt($boundary, ['c164_completion_boundary_decision', 'review_pass']) === true
            && $this->valueAt($boundary, ['c164_completion_boundary_decision', 'boundary_go_decision']) === 'BOUNDARY_CLEARED_GO'
            && $this->valueAt($boundary, ['c164_completion_boundary_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($boundary, ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision', 'review_valid']) === true
            && $this->valueAt($boundary, ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($boundary, ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision', 'same_topic_c164_continues']) === true
            && $this->valueAt($boundary, ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision', 'topic_number_must_not_advance_until_c164_finalization']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'manifest_created']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'ready_for_post_handoff_activation_completion_execution']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'completion_boundary_artifact_only']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'controlled_completion_only']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'completion_boundary_used_for_free_publication']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'completion_boundary_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'completion_boundary_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'official_weekly_swing_stock_recommendations']) === []
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_checklist', 'completion_boundary_reviewed']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_checklist', 'artifact_only']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_checklist', 'ready_for_post_handoff_activation_completion_execution']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_checklist', 'weekly_swing_stock_recommendation_free_published_in_c164_boundary']) === false;
    }

    private function publicationAndPlanGuardClean(array $boundary): bool
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
            if (($boundary[$flag] ?? null) !== false) {
                return false;
            }
        }

        foreach ([
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'completion_boundary_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'completion_boundary_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest', 'completion_boundary_used_for_live_plan_confirm_rollout'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_official_output_published'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'weekly_swing_watchlist_unrestricted_publication_allowed'],
            ['publication_plan_confirm_safety_summary', 'plan_confirm_mutated'],
            ['publication_plan_confirm_safety_summary', 'live_plan_confirm_rollout_executed'],
        ] as $path) {
            if ($this->valueAt($boundary, $path) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $boundary): bool
    {
        return ($boundary['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($boundary['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($boundary['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($boundary['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution'] ?? null) === true
            && ($boundary['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution'] ?? null) === true
            && ($boundary['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution'] ?? null) === false
            && ($boundary['a01_remains_comparator_only'] ?? null) === true
            && ($boundary['a01_promoted'] ?? false) === false
            && ($boundary['candidate_promotion_executed'] ?? false) === false
            && ($boundary['candidate_rerank_executed'] ?? false) === false
            && ($boundary['strategy_retune_executed'] ?? false) === false
            && ($boundary['scoring_mutation_executed'] ?? false) === false
            && ($boundary['catalog_selection_changed'] ?? false) === false
            && ($boundary['runtime_selection_changed'] ?? false) === false;
    }

    private function watchlistFunctionScopeMatches(array $boundary): bool
    {
        return ($boundary['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($boundary['watchlist_function_runtime_mode'] ?? null) === self::WATCHLIST_FUNCTION_RUNTIME_MODE
            && ($boundary['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($boundary['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($boundary['watchlist_function_comparator_candidate_observed'] ?? null) === false
            && trim((string) ($boundary['watchlist_function_source_artifact'] ?? '')) !== ''
            && trim((string) ($boundary['controlled_completion_hash'] ?? '')) !== ''
            && (int) ($boundary['controlled_completion_record_count'] ?? 0) === 2;
    }

    private function controlledCompletionValidation(array $boundary): array
    {
        $path = (string) ($boundary['controlled_completion_path'] ?? '');
        $expectedHash = (string) ($boundary['controlled_completion_hash'] ?? '');
        $expectedFileSha1 = strtoupper((string) ($boundary['controlled_completion_file_sha1'] ?? ''));
        $exists = $path !== '' && is_file($path);
        $raw = $exists ? (string) file_get_contents($path) : '';
        $payload = $exists ? json_decode($raw, true) : null;
        $decoded = is_array($payload) && json_last_error() === JSON_ERROR_NONE;
        $actualHash = $decoded ? (string) ($payload['controlled_completion_hash'] ?? '') : '';
        $actualFileSha1 = $exists ? strtoupper(sha1($raw)) : '';
        $rows = $decoded ? (array) ($payload['output_rows'] ?? []) : [];
        $recordCount = count($rows);
        $candidateCodes = array_values(array_map(static function ($row): string {
            return is_array($row) ? (string) ($row['candidate_code'] ?? '') : '';
        }, $rows));
        $primaryBackupScope = $recordCount === 2
            && $candidateCodes === [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE]
            && ($payload['comparator_candidate']['candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($payload['comparator_candidate']['a01_remains_comparator_only'] ?? null) === true
            && ($payload['comparator_candidate']['completion_execution_state'] ?? null) === 'not_executed';
        $safetyClean = $decoded
            && ($payload['free_publication_allowed'] ?? null) === false
            && ($payload['unrestricted_publication_allowed'] ?? null) === false
            && ($payload['plan_confirm_mutated'] ?? null) === false
            && ($payload['plan_confirm_runtime_reads_activated_catalog'] ?? null) === false
            && ($payload['live_plan_confirm_rollout_executed'] ?? null) === false;
        $hashMatch = $expectedHash !== '' && hash_equals($expectedHash, $actualHash);
        $fileSha1Match = $expectedFileSha1 !== '' && $actualFileSha1 !== '' && $expectedFileSha1 === $actualFileSha1;

        return [
            'validation_completed' => true,
            'path' => $path,
            'exists' => $exists,
            'decoded' => $decoded,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $hashMatch,
            'expected_file_sha1' => $expectedFileSha1,
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $fileSha1Match,
            'record_count' => $recordCount,
            'candidate_codes' => $candidateCodes,
            'primary_backup_scope' => $primaryBackupScope,
            'safety_clean' => $safetyClean,
            'valid' => $exists && $decoded && $hashMatch && $fileSha1Match && $primaryBackupScope && $safetyClean,
        ];
    }

    private function c164BoundaryLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C164_COMPLETION_BOUNDARY',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'completion_boundary_ready' => is_array($load['payload'] ?? null) && $this->c164BoundaryComplete($load['payload']),
        ];
    }

    private function c164BoundaryCarryForwardSummary(array $boundary): array
    {
        return [
            'validation_completed' => true,
            'source_run_code' => (string) ($boundary['run_code'] ?? ''),
            'source_status' => (string) ($boundary['status'] ?? ''),
            'source_phase_label' => (string) ($boundary['phase_label'] ?? ''),
            'source_next_step_recommendation' => (string) ($boundary['next_step_recommendation'] ?? ''),
            'c164_boundary_valid' => $this->c164BoundaryComplete($boundary),
            'topic_code' => (string) ($boundary['topic_code'] ?? ''),
            'topic_stage' => (string) ($boundary['topic_stage'] ?? ''),
            'completion_boundary_cleared' => (bool) ($boundary['completion_boundary_cleared'] ?? false),
            'post_handoff_activation_completion_boundary_cleared' => (bool) ($boundary['post_handoff_activation_completion_boundary_cleared'] ?? false),
            'ready_for_completion_execution' => (bool) ($boundary['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution'] ?? false),
            'same_topic_c164_continues' => (bool) $this->valueAt($boundary, ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision', 'same_topic_c164_continues']),
            'topic_number_must_not_advance_until_c164_finalization' => (bool) $this->valueAt($boundary, ['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision', 'topic_number_must_not_advance_until_c164_finalization']),
        ];
    }

    private function completionExecutionGuardSummary(array $boundary, array $controlledCompletion, bool $pass, array $options): array
    {
        return [
            'validation_completed' => true,
            'source_c164_boundary_next_recommendation_matches' => $this->c164BoundaryNextRecommendationMatches($boundary),
            'source_c164_boundary_valid' => $this->c164BoundaryComplete($boundary),
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($boundary),
            'controlled_completion_lock_valid' => (bool) ($controlledCompletion['valid'] ?? false),
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'c164_boundary_cleared_confirmed' => (bool) ($options['c164_boundary_cleared_confirmed'] ?? false),
            'post_handoff_activation_completion_boundary_confirmed' => (bool) ($options['post_handoff_activation_completion_boundary_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'completion_execution_pass' => $pass,
            'result_review_allowed_next' => $pass,
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $boundary): array
    {
        return [
            'validation_completed' => true,
            'candidate_scope_matches' => $this->candidateScopeMatches($boundary),
            'primary_candidate_code' => (string) ($boundary['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($boundary['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($boundary['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'primary_candidate_ready_for_completion_execution' => (bool) ($boundary['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution'] ?? false),
            'backup_candidate_ready_for_completion_execution' => (bool) ($boundary['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution'] ?? false),
            'comparator_candidate_ready_for_completion_execution' => (bool) ($boundary['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution'] ?? false),
            'candidate_rerank_executed' => false,
            'candidate_promotion_executed' => false,
            'a01_remains_comparator_only' => (bool) ($boundary['a01_remains_comparator_only'] ?? false),
        ];
    }

    private function watchlistFunctionScopeSummary(array $boundary): array
    {
        return [
            'validation_completed' => true,
            'watchlist_function_scope_matches' => $this->watchlistFunctionScopeMatches($boundary),
            'watchlist_function_used' => (string) ($boundary['watchlist_function_used'] ?? ''),
            'expected_watchlist_function' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => (string) ($boundary['watchlist_function_runtime_mode'] ?? ''),
            'expected_watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'source_artifact' => (string) ($boundary['watchlist_function_source_artifact'] ?? ''),
            'primary_candidate_observed' => (bool) ($boundary['watchlist_function_primary_candidate_observed'] ?? false),
            'backup_candidate_observed' => (bool) ($boundary['watchlist_function_backup_candidate_observed'] ?? false),
            'comparator_candidate_observed' => (bool) ($boundary['watchlist_function_comparator_candidate_observed'] ?? false),
            'controlled_completion_record_count' => (int) ($boundary['controlled_completion_record_count'] ?? 0),
        ];
    }

    private function operatorApprovalValidationSummary(array $options): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
            'completion_execution_confirmation_required' => true,
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'c164_boundary_cleared_confirmation_required' => true,
            'c164_boundary_cleared_confirmed' => (bool) ($options['c164_boundary_cleared_confirmed'] ?? false),
            'post_handoff_activation_completion_boundary_confirmation_required' => true,
            'post_handoff_activation_completion_boundary_confirmed' => (bool) ($options['post_handoff_activation_completion_boundary_confirmed'] ?? false),
            'controlled_completion_only_confirmation_required' => true,
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
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
            'temporary_negative_artifact_paths' => array_values($paths),
            'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
    }

    private function completionExecutionDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'completion_execution_completed' => $pass,
            'c164_boundary_cleared_confirmed' => (bool) ($options['c164_boundary_cleared_confirmed'] ?? false),
            'post_handoff_activation_completion_boundary_confirmed' => (bool) ($options['post_handoff_activation_completion_boundary_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'completion_execution_go_decision' => $pass ? 'COMPLETION_EXECUTION_GO' : 'NO_GO',
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'decision_scope' => $pass
                ? 'C164 controlled post-handoff activation completion execution is complete for primary/backup only.'
                : 'C164 completion execution did not pass.',
        ];
    }

    private function nextResultReviewDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C164_RESULT_REVIEW_RECOMMENDATION : self::RUN_CODE,
            'next_scope' => $pass ? 'C164 post-handoff activation completion result review only' : 'targeted C164 completion execution repair',
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'result_review_required_next' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function completionExecutionManifest(array $boundary, array $controlledCompletion, bool $pass, array $options, array $load): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_completion_execution',
            'source_artifact' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'completion_boundary_cleared' => (bool) ($boundary['completion_boundary_cleared'] ?? false),
            'post_handoff_activation_completion_boundary_cleared' => (bool) ($boundary['post_handoff_activation_completion_boundary_cleared'] ?? false),
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'completion_execution_go_decision' => $pass ? 'COMPLETION_EXECUTION_GO' : 'NO_GO',
            'ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => $pass,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => (string) ($boundary['watchlist_function_source_artifact'] ?? ''),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_path' => (string) ($controlledCompletion['path'] ?? ''),
            'controlled_completion_hash' => (string) ($controlledCompletion['actual_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($controlledCompletion['actual_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($controlledCompletion['record_count'] ?? 0),
            'controlled_completion_lock_valid' => (bool) ($controlledCompletion['valid'] ?? false),
            'completion_execution_artifact_only' => true,
            'controlled_completion_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'completion_execution_used_for_free_publication' => false,
            'completion_execution_used_for_plan_confirm_mutation' => false,
            'completion_execution_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function completionExecutionChecklist(bool $pass, array $options): array
    {
        return [
            'completion_execution_reviewed' => $pass,
            'c164_completion_boundary_source_lock_reviewed' => $pass,
            'completion_boundary_cleared_reviewed' => $pass,
            'completion_execution_required' => true,
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'c164_boundary_cleared_confirmed' => (bool) ($options['c164_boundary_cleared_confirmed'] ?? false),
            'post_handoff_activation_completion_boundary_confirmed' => (bool) ($options['post_handoff_activation_completion_boundary_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_execution_gate_required' => true,
            'negative_boundary_cleared_gate_required' => true,
            'negative_controlled_completion_only_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'controlled_completion_lock_required' => true,
            'completion_execution_only' => true,
            'controlled_execution_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c164_execution' => false,
            'completion_result_review_required_next' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'completion_execution_completed' => $pass,
                'watchlist_function_enabled' => $pass,
                'ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'completion_execution_completed' => $pass,
                'watchlist_function_enabled' => $pass,
                'ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'completion_execution_completed' => false,
                'watchlist_function_enabled' => false,
                'a01_remains_comparator_only' => true,
                'ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review' => false,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $boundary): array
    {
        return [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($boundary),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
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
            'c164_execution_document_required' => true,
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
                'C164 completion execution service',
                'C164 completion execution command',
                'C164 completion execution artifact',
                'C164 completion execution tests',
                'C164 completion execution audit documentation',
            ],
            'what_this_stage_does' => 'Executes the C164 post-handoff activation completion step from the locked C164 completion boundary artifact.',
            'what_this_stage_does_not_do' => [
                'does not repeat C163 activation',
                'does not publish weekly swing output freely',
                'does not unlock unrestricted publication',
                'does not mutate PLAN/CONFIRM',
                'does not make PLAN/CONFIRM read activated catalog',
                'does not execute live PLAN/CONFIRM rollout',
                'does not advance beyond C164',
            ],
            'stage_passed' => $pass,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C164_RESULT_REVIEW_RECOMMENDATION : self::RUN_CODE,
            'planned_next_scope' => 'C164 post-handoff activation completion result review, still controlled and same C164 topic.',
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'planned_next_required_inputs' => [
                'locked C164 completion execution artifact hash',
                'locked C164 completion execution file SHA1',
                'locked C164 completion boundary artifact hash',
                'PLAN/CONFIRM unchanged confirmation',
                'live rollout still disabled',
                'free publication still disabled',
            ],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C164 execution validates C164 completion boundary artifact_hash and file SHA1 locks before execution completion.',
            'C164 execution validates the controlled completion output file lock and primary/backup-only scope.',
            'C164 execution keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C164 execution advances only to C164 completion result review and does not advance the topic number.',
            'C164 execution does not mutate PLAN/CONFIRM, read activated catalog, execute live rollout, free-publish recommendations, or allow unrestricted publication.',
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c164_plan_confirm_completion_post_handoff_activation_completion_boundary' => [
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
            'expected_c164_completion_boundary_hash' => $load['expected_hash'],
            'actual_c164_completion_boundary_hash' => $load['actual_hash'],
            'c164_completion_boundary_hash_match' => $load['hash_match'],
            'expected_c164_completion_boundary_file_sha1' => $load['expected_file_sha1'],
            'actual_c164_completion_boundary_file_sha1' => $load['actual_file_sha1'],
            'c164_completion_boundary_file_sha1_match' => $load['file_sha1_match'],
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

        return $duplicates;
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

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_BLOCKED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function failureAttributionSummary(array $failures): array
    {
        return [
            'failure_count' => count($failures),
            'failures' => $failures,
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_OPERATOR_OR_CONTROLLED_COMPLETION_LOCK',
        ];
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
