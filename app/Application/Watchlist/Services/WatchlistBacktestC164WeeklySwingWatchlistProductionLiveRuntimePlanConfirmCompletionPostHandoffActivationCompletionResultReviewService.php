<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewService
{
    public const RUN_CODE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-83 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW';

    public const DEFAULT_C164_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution.json';
    public const DEFAULT_EXPECTED_C164_EXECUTION_HASH = '78066e88b917b317ba6af5777b0ddc98b04bc29a';
    public const DEFAULT_EXPECTED_C164_EXECUTION_FILE_SHA1 = 'EEBF3B6A4D12203FB1860CFC1E60DF72C057E815';
    public const DEFAULT_CONTROLLED_COMPLETION_ARTIFACT = 'storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json';
    public const DEFAULT_EXPECTED_CONTROLLED_COMPLETION_HASH = 'e9862d9e7738d0558f107d978f329f97f14b3520';
    public const DEFAULT_EXPECTED_CONTROLLED_COMPLETION_FILE_SHA1 = 'AB9FC9F714339B78D68132222AC8C398BE7EE1B3';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const WATCHLIST_FUNCTION_RUNTIME_MODE = 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT';

    private const EXPECTED_C164_EXECUTION_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_PASSED_COMPLETION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C164_EXECUTION_PHASE_LABEL = 'PR-82 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION';
    private const EXPECTED_C164_EXECUTION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C164_OPERATOR_GO_NO_GO_RECOMMENDATION = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const RESULT_REVIEW_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING';
    private const COMPLETION_EXECUTION_RESULT_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_COMPLETION_EXECUTION_RESULT_CONFIRMATION_MISSING';
    private const CONTROLLED_COMPLETION_RESULT_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_RESULT_CONFIRMATION_MISSING';
    private const CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C164_EXECUTION_LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_ARTIFACT_LOCK_MISMATCH';
    private const C164_EXECUTION_FILE_SHA1_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_FILE_SHA1_LOCK_MISMATCH';
    private const C164_EXECUTION_CONVERT_FROM_JSON_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C164_EXECUTION_STATUS_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_STATUS_MISMATCH';
    private const C164_EXECUTION_PHASE_LABEL_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_PHASE_LABEL_MISMATCH';
    private const C164_EXECUTION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_NEXT_RECOMMENDATION_MISMATCH';
    private const C164_EXECUTION_STATE_INVALID_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_C164_COMPLETION_EXECUTION_STATE_INVALID';
    private const CONTROLLED_COMPLETION_LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_ARTIFACT_LOCK_MISMATCH';
    private const CONTROLLED_COMPLETION_FILE_SHA1_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_FILE_SHA1_LOCK_MISMATCH';
    private const CONTROLLED_COMPLETION_CONVERT_FROM_JSON_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CONTROLLED_COMPLETION_INTEGRITY_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CONTROLLED_COMPLETION_INTEGRITY_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH';

    private const REQUIRED_C164_EXECUTION_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass',
        'post_handoff_activation_completion_execution_completed',
        'completion_execution_confirmed',
        'c164_boundary_cleared_confirmed',
        'post_handoff_activation_completion_boundary_confirmed',
        'controlled_completion_only_confirmed',
        'post_handoff_activation_completion_boundary_cleared',
        'completion_boundary_cleared',
        'completion_boundary_confirmed',
        'c163_topic_complete_confirmed',
        'post_handoff_activation_closed_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'operator_approved',
        'operator_go_decision',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'post_handoff_activation_closed',
        'c163_topic_complete_after_finalization',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created',
        'c164_completion_boundary_lock_valid',
        'c164_completion_boundary_review_valid',
        'c164_completion_boundary_convert_from_json_pass',
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
        'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review',
        'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review',
        'a01_remains_comparator_only',
        'c164_is_post_handoff_activation_completion_contract',
        'c164_not_c163_activation_repeat',
        'c164_completion_execution_only',
        'c164_controlled_completion_only',
        'c164_not_publication',
        'c164_not_unrestricted_publication',
        'c164_not_plan_confirm_mutation',
        'c164_not_live_plan_confirm_rollout',
        'c164_topic_number_retained_for_result_review',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C164_EXECUTION_FALSE_FIELDS = [
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review',
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

    private const SOURCE_FALSE_GUARDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
    ];

    private const CONTROLLED_COMPLETION_FALSE_GUARDS = [
        'free_publication_allowed',
        'unrestricted_publication_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_executed',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c164-*completion-result-review*-test.json',
        'storage/app/watchlist/backtest/c164-*completion-result*-test.json',
        'storage/app/watchlist/backtest/c164-*result-review*-negative-*.json',
        'storage/app/watchlist/backtest/c164-*negative-*-test.json',
        'storage/app/watchlist/backtest/c164-*missing-*-test.json',
        'storage/app/watchlist/backtest/c164-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c164-*invalid-*-test.json',
        'storage/app/watchlist/backtest/.tmp-c164-*negative-*-test.json',
    ];

    public function execute(
        string $c164ExecutionArtifact = self::DEFAULT_C164_EXECUTION_ARTIFACT,
        string $expectedC164ExecutionHash = self::DEFAULT_EXPECTED_C164_EXECUTION_HASH,
        string $expectedC164ExecutionFileSha1 = self::DEFAULT_EXPECTED_C164_EXECUTION_FILE_SHA1,
        string $controlledCompletionArtifact = self::DEFAULT_CONTROLLED_COMPLETION_ARTIFACT,
        string $expectedControlledCompletionHash = self::DEFAULT_EXPECTED_CONTROLLED_COMPLETION_HASH,
        string $expectedControlledCompletionFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_COMPLETION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')), $controlledCompletionArtifact);
        $executionLoad = $this->loadJsonLock($c164ExecutionArtifact, $expectedC164ExecutionHash, $expectedC164ExecutionFileSha1, 'artifact_hash');
        $completionLoad = $this->loadJsonLock($controlledCompletionArtifact, $expectedControlledCompletionHash, $expectedControlledCompletionFileSha1, 'controlled_completion_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($executionLoad, $completionLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($executionLoad, $completionLoad));

        if (! $executionLoad['exists'] || ! is_array($executionLoad['payload'])) {
            return $this->blocked($artifact, self::C164_EXECUTION_LOCK_MISMATCH_STATUS, 'C164 completion execution artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $executionLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $executionLoad, $completionLoad, $options, false);
            $artifact['c164_execution_convert_from_json_duplicate_keys'] = $executionLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C164_EXECUTION_CONVERT_FROM_JSON_STATUS, 'C164 completion execution artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $executionLoad['hash_match']) {
            return $this->blocked($artifact, self::C164_EXECUTION_LOCK_MISMATCH_STATUS, 'C164 completion execution artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $executionLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::C164_EXECUTION_FILE_SHA1_MISMATCH_STATUS, 'C164 completion execution file SHA1 mismatch.', $outputPath, $overwrite);
        }

        if (! $completionLoad['exists'] || ! is_array($completionLoad['payload'])) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::CONTROLLED_COMPLETION_LOCK_MISMATCH_STATUS, 'Controlled completion artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $completionLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $executionLoad, $completionLoad, $options, false);
            $artifact['controlled_completion_convert_from_json_duplicate_keys'] = $completionLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::CONTROLLED_COMPLETION_CONVERT_FROM_JSON_STATUS, 'Controlled completion artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $completionLoad['hash_match']) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::CONTROLLED_COMPLETION_LOCK_MISMATCH_STATUS, 'Controlled completion artifact hash mismatch.', $outputPath, $overwrite);
        }
        if (! $completionLoad['file_sha1_match']) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::CONTROLLED_COMPLETION_FILE_SHA1_MISMATCH_STATUS, 'Controlled completion file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $execution = $executionLoad['payload'];
        $completion = $completionLoad['payload'];
        if (($execution['status'] ?? null) !== self::EXPECTED_C164_EXECUTION_STATUS || ($execution['reason_code'] ?? null) !== self::EXPECTED_C164_EXECUTION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::C164_EXECUTION_STATUS_MISMATCH_STATUS, 'C164 completion execution status/reason is not result-review ready.', $outputPath, $overwrite);
        }
        if (($execution['phase_label'] ?? null) !== self::EXPECTED_C164_EXECUTION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::C164_EXECUTION_PHASE_LABEL_MISMATCH_STATUS, 'C164 completion execution phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c164ExecutionNextRecommendationMatches($execution)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::C164_EXECUTION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C164 completion execution next recommendation is not C164 result review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($execution, $completion)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C164 execution or controlled completion already published, unlocked publication, mutated PLAN/CONFIRM, read activated catalog, or executed live rollout.', $outputPath, $overwrite);
        }
        if (! $this->c164ExecutionComplete($execution)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::C164_EXECUTION_STATE_INVALID_STATUS, 'C164 completion execution evidence is incomplete for result review.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($execution, $completion)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C164 completion execution candidate scope does not match locked result review scope.', $outputPath, $overwrite);
        }
        if (! $this->watchlistFunctionScopeMatches($execution)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS, 'C164 execution watchlist function is not the locked controlled live recommendation generation function.', $outputPath, $overwrite);
        }
        if (! $this->controlledCompletionIntegrityValid($execution, $completion, $completionLoad)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::CONTROLLED_COMPLETION_INTEGRITY_STATUS, 'Controlled completion artifact does not match C164 execution manifest.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::APPROVAL_MISSING_STATUS, 'C164 result review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['result_review_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::RESULT_REVIEW_CONFIRMATION_MISSING_STATUS, 'C164 result review requires --result-review-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['completion_execution_result_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::COMPLETION_EXECUTION_RESULT_CONFIRMATION_MISSING_STATUS, 'C164 result review requires --completion-execution-result-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_completion_result_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::CONTROLLED_COMPLETION_RESULT_CONFIRMATION_MISSING_STATUS, 'C164 result review requires --controlled-completion-result-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_completion_only_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING_STATUS, 'C164 result review requires --controlled-completion-only-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C164 result review requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C164 result review requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $completionLoad, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C164 result review requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $executionLoad, $completionLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $executionLoad, $completionLoad, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C164 reviews the post-handoff activation completion execution result. Controlled completion evidence is valid for C164 operator GO/NO-GO review; PLAN/CONFIRM remains unchanged, activated-catalog reads remain disabled, live rollout remains disabled, and free publication remains locked.';
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_PASSED_CONTROLLED_EVIDENCE_VALID_PLAN_UNCHANGED_NO_LIVE_ROLLOUT';
        $artifact['next_step_recommendation'] = self::C164_OPERATOR_GO_NO_GO_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($completionLoad, $options));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $controlledCompletionArtifact): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-83',
            'internal_checkpoint' => 'C164',
            'topic_code' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW',
            'status' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_NOT_RUN',
            'reason_code' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_completion_path' => $controlledCompletionArtifact,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass' => false,
            'post_handoff_activation_completion_result_reviewed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest_created' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed_next' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => false,
            'post_handoff_activation_completion_execution_completed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'weekly_swing_watchlist_controlled_publication_allowed' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
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
            'c164_execution_lock_valid' => false,
            'c164_completion_execution_valid' => false,
            'c164_execution_convert_from_json_pass' => false,
            'controlled_completion_lock_valid' => false,
            'controlled_completion_convert_from_json_pass' => false,
            'controlled_completion_integrity_valid' => false,
            'watchlist_function_used' => '',
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'watchlist_function_source_artifact' => '',
            'watchlist_function_primary_candidate_observed' => false,
            'watchlist_function_backup_candidate_observed' => false,
            'watchlist_function_comparator_candidate_observed' => false,
            'operator_approved' => false,
            'approval_reference' => '',
            'result_review_confirmed' => false,
            'completion_execution_result_confirmed' => false,
            'controlled_completion_result_confirmed' => false,
            'controlled_completion_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'primary_candidate_completion_result_reviewed' => false,
            'backup_candidate_completion_result_reviewed' => false,
            'comparator_candidate_completion_result_reviewed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c164_is_post_handoff_activation_completion_contract' => true,
            'c164_not_c163_activation_repeat' => true,
            'c164_completion_result_review_only' => true,
            'c164_controlled_completion_only' => true,
            'c164_not_publication' => true,
            'c164_not_unrestricted_publication' => true,
            'c164_not_plan_confirm_mutation' => true,
            'c164_not_live_plan_confirm_rollout' => true,
            'c164_topic_number_retained_for_operator_go_no_go' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function passingTopLevelState(array $completionLoad, array $options): array
    {
        $completion = is_array($completionLoad['payload']) ? $completionLoad['payload'] : [];

        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass' => true,
            'post_handoff_activation_completion_result_reviewed' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest_created' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed_next' => true,
            'c164_execution_lock_valid' => true,
            'c164_completion_execution_valid' => true,
            'c164_execution_convert_from_json_pass' => true,
            'controlled_completion_lock_valid' => true,
            'controlled_completion_convert_from_json_pass' => true,
            'controlled_completion_integrity_valid' => true,
            'operator_approved' => true,
            'approval_reference' => trim((string) ($options['approval_reference'] ?? '')),
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'completion_execution_result_confirmed' => (bool) ($options['completion_execution_result_confirmed'] ?? false),
            'controlled_completion_result_confirmed' => (bool) ($options['controlled_completion_result_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_completion_result_reviewed' => true,
            'backup_candidate_completion_result_reviewed' => true,
            'comparator_candidate_completion_result_reviewed' => false,
            'controlled_completion_path' => $completionLoad['path'],
            'controlled_completion_hash' => $completionLoad['actual_hash'],
            'controlled_completion_file_sha1' => $completionLoad['actual_file_sha1'],
            'controlled_completion_record_count' => is_array($completion['output_rows'] ?? null) ? count($completion['output_rows']) : 0,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $executionLoad, array $completionLoad, array $options, bool $pass): array
    {
        $execution = is_array($executionLoad['payload']) ? $executionLoad['payload'] : [];
        $completion = is_array($completionLoad['payload']) ? $completionLoad['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($execution, $completion, $options, $pass, $completionLoad));
        $artifact['c164_execution_lock_validation_summary'] = $this->lockValidationSummary($executionLoad);
        $artifact['controlled_completion_lock_validation_summary'] = $this->controlledCompletionLockValidationSummary($completionLoad);
        $artifact['c164_execution_carry_forward_summary'] = $this->executionCarryForwardSummary($execution);
        $artifact['controlled_completion_result_review_summary'] = $this->controlledCompletionResultReviewSummary($completionLoad, $pass);
        $artifact['controlled_completion_integrity_summary'] = $this->controlledCompletionIntegritySummary($execution, $completion, $completionLoad);
        $artifact['watchlist_function_scope_summary'] = $this->watchlistFunctionScopeSummary($execution);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($execution, $completion);
        $artifact['candidate_completion_result_scorecard'] = $this->candidateScorecard($pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['result_review_confirmation_summary'] = $this->resultReviewConfirmationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c164_completion_result_review_decision'] = $this->resultReviewDecision($pass, $options);
        $artifact['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision'] = $this->nextOperatorGoNoGoDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest'] = $this->resultReviewManifest($execution, $completionLoad, $pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_checklist'] = $this->resultReviewChecklist($pass, $options);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($executionLoad, $completionLoad);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);

        return $artifact;
    }

    private function topLevelState(array $execution, array $completion, array $options, bool $pass, array $completionLoad): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed' => (bool) ($execution['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed'] ?? false),
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed' => (bool) ($execution['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed'] ?? false),
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => (bool) ($execution['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass'] ?? false),
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => (bool) ($execution['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass'] ?? false),
            'post_handoff_activation_completion_execution_completed' => (bool) ($execution['post_handoff_activation_completion_execution_completed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($execution['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($execution['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($execution['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($execution['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($execution['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($execution['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'watchlist_function_used' => (string) ($execution['watchlist_function_used'] ?? ''),
            'watchlist_function_runtime_mode' => (string) ($execution['watchlist_function_runtime_mode'] ?? self::WATCHLIST_FUNCTION_RUNTIME_MODE),
            'watchlist_function_source_artifact' => (string) ($execution['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => (bool) ($execution['watchlist_function_primary_candidate_observed'] ?? false),
            'watchlist_function_backup_candidate_observed' => (bool) ($execution['watchlist_function_backup_candidate_observed'] ?? false),
            'watchlist_function_comparator_candidate_observed' => (bool) ($execution['watchlist_function_comparator_candidate_observed'] ?? false),
            'c164_completion_execution_valid' => $this->c164ExecutionComplete($execution),
            'controlled_completion_integrity_valid' => $this->controlledCompletionIntegrityValid($execution, $completion, $completionLoad),
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => trim((string) ($options['approval_reference'] ?? '')),
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'completion_execution_result_confirmed' => (bool) ($options['completion_execution_result_confirmed'] ?? false),
            'controlled_completion_result_confirmed' => (bool) ($options['controlled_completion_result_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_completion_result_reviewed' => $pass,
            'backup_candidate_completion_result_reviewed' => $pass,
            'comparator_candidate_completion_result_reviewed' => false,
            'controlled_completion_path' => $completionLoad['path'],
            'controlled_completion_hash' => $completionLoad['actual_hash'],
            'controlled_completion_file_sha1' => $completionLoad['actual_file_sha1'],
            'controlled_completion_record_count' => is_array($completion['output_rows'] ?? null) ? count($completion['output_rows']) : 0,
            'primary_candidate_code' => (string) ($execution['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($execution['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($execution['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'a01_remains_comparator_only' => (bool) ($execution['a01_remains_comparator_only'] ?? true),
        ];
    }

    private function c164ExecutionNextRecommendationMatches(array $execution): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'next_recommendation'],
        ] as $path) {
            if ($this->valueAt($execution, $path) !== self::EXPECTED_C164_EXECUTION_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function c164ExecutionComplete(array $execution): bool
    {
        foreach (self::REQUIRED_C164_EXECUTION_TRUE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C164_EXECUTION_FALSE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($execution['topic_code'] ?? null) !== 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION'
            || ($execution['topic_stage'] ?? null) !== 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION') {
            return false;
        }
        foreach ([
            ['c164_completion_execution_decision', 'review_valid'],
            ['c164_completion_execution_decision', 'completion_execution_completed'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'review_valid'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'same_topic_c164_continues'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'topic_number_must_not_advance_until_c164_finalization'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'result_review_required_next'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'manifest_created'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'completion_execution_artifact_only'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'controlled_completion_only'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist', 'completion_execution_reviewed'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist', 'completion_result_review_required_next'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist', 'completion_execution_only'],
        ] as $path) {
            if ($this->valueAt($execution, $path) !== true) {
                return false;
            }
        }
        foreach ([
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'free_publication_allowed_next'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'plan_confirm_mutation_allowed_next'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'live_plan_confirm_rollout_allowed_next'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'completion_execution_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'completion_execution_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'completion_execution_used_for_live_plan_confirm_rollout'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_checklist', 'weekly_swing_stock_recommendation_free_published_in_c164_execution'],
        ] as $path) {
            if ($this->valueAt($execution, $path) !== false) {
                return false;
            }
        }

        return $this->valueAt($execution, ['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($execution, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'official_weekly_swing_stock_recommendations']) === [];
    }

    private function controlledCompletionReady(array $completion): bool
    {
        return ($completion['artifact_type'] ?? null) === 'C161_WEEKLY_SWING_WATCHLIST_CONTROLLED_PLAN_CONFIRM_COMPLETION'
            && ($completion['plan_confirm_completion_mode'] ?? null) === 'controlled'
            && ($completion['plan_confirm_completion_state'] ?? null) === 'controlled_completion_executed'
            && ($completion['baseline_plan_confirm_state'] ?? null) === 'closed_and_unchanged'
            && ($completion['activated_catalog_read_state'] ?? null) === 'not_enabled'
            && ($completion['live_rollout_state'] ?? null) === 'not_executed'
            && ($completion['free_publication_allowed'] ?? null) === false
            && ($completion['unrestricted_publication_allowed'] ?? null) === false
            && ($completion['plan_confirm_mutated'] ?? null) === false
            && ($completion['plan_confirm_runtime_reads_activated_catalog'] ?? null) === false
            && ($completion['live_plan_confirm_rollout_executed'] ?? null) === false
            && ($completion['result_review_required_next'] ?? null) === true
            && is_array($completion['output_rows'] ?? null)
            && count($completion['output_rows']) === 2
            && (($completion['output_rows'][0]['candidate_code'] ?? null) === self::PRIMARY_CANDIDATE)
            && (($completion['output_rows'][0]['role'] ?? null) === 'primary')
            && (($completion['output_rows'][1]['candidate_code'] ?? null) === self::BACKUP_CANDIDATE)
            && (($completion['output_rows'][1]['role'] ?? null) === 'backup')
            && (($completion['comparator_candidate']['candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE)
            && (($completion['comparator_candidate']['role'] ?? null) === 'comparator_only')
            && (($completion['comparator_candidate']['a01_remains_comparator_only'] ?? null) === true)
            && (($completion['comparator_candidate']['completion_execution_state'] ?? null) === 'not_executed');
    }

    private function controlledCompletionIntegrityValid(array $execution, array $completion, array $completionLoad): bool
    {
        return $this->controlledCompletionReady($completion)
            && ($execution['controlled_completion_path'] ?? null) === $completionLoad['path']
            && ($execution['controlled_completion_hash'] ?? null) === $completionLoad['actual_hash']
            && ($execution['controlled_completion_file_sha1'] ?? null) === $completionLoad['actual_file_sha1']
            && (int) ($execution['controlled_completion_record_count'] ?? 0) === 2
            && ($completion['controlled_completion_hash'] ?? null) === $completionLoad['actual_hash'];
    }

    private function publicationAndPlanGuardClean(array $execution, array $completion): bool
    {
        foreach (self::SOURCE_FALSE_GUARDS as $field) {
            if (($execution[$field] ?? null) !== false) {
                return false;
            }
        }
        foreach (self::CONTROLLED_COMPLETION_FALSE_GUARDS as $field) {
            if (($completion[$field] ?? null) !== false) {
                return false;
            }
        }
        foreach ([
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'completion_execution_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'completion_execution_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest', 'completion_execution_used_for_live_plan_confirm_rollout'],
        ] as $path) {
            if ($this->valueAt($execution, $path) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $execution, array $completion): bool
    {
        return ($execution['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($execution['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($execution['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($execution['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review'] ?? null) === true
            && ($execution['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review'] ?? null) === true
            && ($execution['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review'] ?? null) === false
            && ($execution['a01_remains_comparator_only'] ?? null) === true
            && (($completion['output_rows'][0]['candidate_code'] ?? null) === self::PRIMARY_CANDIDATE)
            && (($completion['output_rows'][1]['candidate_code'] ?? null) === self::BACKUP_CANDIDATE)
            && (($completion['comparator_candidate']['candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE)
            && (($completion['comparator_candidate']['a01_remains_comparator_only'] ?? null) === true)
            && ($execution['a01_promoted'] ?? false) === false
            && ($execution['candidate_promotion_executed'] ?? false) === false
            && ($execution['candidate_rerank_executed'] ?? false) === false
            && ($execution['strategy_retune_executed'] ?? false) === false
            && ($execution['scoring_mutation_executed'] ?? false) === false
            && ($execution['catalog_selection_changed'] ?? false) === false
            && ($execution['runtime_selection_changed'] ?? false) === false;
    }

    private function watchlistFunctionScopeMatches(array $execution): bool
    {
        return ($execution['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($execution['watchlist_function_runtime_mode'] ?? null) === self::WATCHLIST_FUNCTION_RUNTIME_MODE
            && ($execution['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($execution['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($execution['watchlist_function_comparator_candidate_observed'] ?? null) === false
            && trim((string) ($execution['watchlist_function_source_artifact'] ?? '')) !== ''
            && (int) ($execution['controlled_completion_record_count'] ?? 0) === 2;
    }

    private function lockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'expected_status' => self::EXPECTED_C164_EXECUTION_STATUS,
            'actual_status' => is_array($load['payload']) ? ($load['payload']['status'] ?? null) : null,
            'expected_phase_label' => self::EXPECTED_C164_EXECUTION_PHASE_LABEL,
            'actual_phase_label' => is_array($load['payload']) ? ($load['payload']['phase_label'] ?? null) : null,
            'expected_next_recommendation' => self::EXPECTED_C164_EXECUTION_NEXT_RECOMMENDATION,
            'next_recommendation_match' => is_array($load['payload']) && $this->c164ExecutionNextRecommendationMatches($load['payload']),
            'lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function controlledCompletionLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'CONTROLLED_PLAN_CONFIRM_COMPLETION',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'controlled_completion_ready' => is_array($load['payload']) && $this->controlledCompletionReady($load['payload']),
        ];
    }

    private function executionCarryForwardSummary(array $execution): array
    {
        return [
            'validation_completed' => true,
            'c164_execution_valid' => $this->c164ExecutionComplete($execution),
            'topic_code' => (string) ($execution['topic_code'] ?? ''),
            'topic_stage' => (string) ($execution['topic_stage'] ?? ''),
            'ready_for_completion_result_review' => (bool) ($execution['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review'] ?? false),
            'controlled_completion_hash' => (string) ($execution['controlled_completion_hash'] ?? ''),
            'watchlist_function_used' => (string) ($execution['watchlist_function_used'] ?? ''),
            'official_output_published' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function controlledCompletionResultReviewSummary(array $completionLoad, bool $pass): array
    {
        $completion = is_array($completionLoad['payload']) ? $completionLoad['payload'] : [];

        return [
            'validation_completed' => true,
            'controlled_completion_result_reviewed' => $pass,
            'controlled_completion_ready' => $this->controlledCompletionReady($completion),
            'controlled_completion_hash' => $completionLoad['actual_hash'],
            'controlled_completion_file_sha1' => $completionLoad['actual_file_sha1'],
            'controlled_completion_record_count' => is_array($completion['output_rows'] ?? null) ? count($completion['output_rows']) : 0,
            'plan_confirm_completion_mode' => (string) ($completion['plan_confirm_completion_mode'] ?? ''),
            'plan_confirm_completion_state' => (string) ($completion['plan_confirm_completion_state'] ?? ''),
            'baseline_plan_confirm_state' => (string) ($completion['baseline_plan_confirm_state'] ?? ''),
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function controlledCompletionIntegritySummary(array $execution, array $completion, array $completionLoad): array
    {
        return [
            'validation_completed' => true,
            'controlled_completion_integrity_valid' => $this->controlledCompletionIntegrityValid($execution, $completion, $completionLoad),
            'execution_controlled_completion_path' => $execution['controlled_completion_path'] ?? null,
            'actual_controlled_completion_path' => $completionLoad['path'],
            'execution_controlled_completion_hash' => $execution['controlled_completion_hash'] ?? null,
            'actual_controlled_completion_hash' => $completionLoad['actual_hash'],
            'execution_controlled_completion_file_sha1' => $execution['controlled_completion_file_sha1'] ?? null,
            'actual_controlled_completion_file_sha1' => $completionLoad['actual_file_sha1'],
            'controlled_completion_ready' => $this->controlledCompletionReady($completion),
        ];
    }

    private function watchlistFunctionScopeSummary(array $execution): array
    {
        return [
            'validation_completed' => true,
            'watchlist_function_scope_matches' => $this->watchlistFunctionScopeMatches($execution),
            'watchlist_function_used' => (string) ($execution['watchlist_function_used'] ?? ''),
            'expected_watchlist_function' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => (string) ($execution['watchlist_function_runtime_mode'] ?? ''),
            'expected_watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'source_artifact' => (string) ($execution['watchlist_function_source_artifact'] ?? ''),
            'primary_candidate_observed' => (bool) ($execution['watchlist_function_primary_candidate_observed'] ?? false),
            'backup_candidate_observed' => (bool) ($execution['watchlist_function_backup_candidate_observed'] ?? false),
            'comparator_candidate_observed' => (bool) ($execution['watchlist_function_comparator_candidate_observed'] ?? false),
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $execution, array $completion): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($execution, $completion),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'plan_confirm_completion_post_handoff_activation_completion_result_reviewed' => $pass,
                'watchlist_function_enabled' => $pass,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'plan_confirm_completion_post_handoff_activation_completion_result_reviewed' => $pass,
                'watchlist_function_enabled' => $pass,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'plan_confirm_completion_post_handoff_activation_completion_result_reviewed' => false,
                'watchlist_function_enabled' => false,
                'a01_remains_comparator_only' => true,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
            ],
        ];
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

    private function resultReviewConfirmationSummary(array $options): array
    {
        return [
            'result_review_confirmation_required' => true,
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'completion_execution_result_confirmation_required' => true,
            'completion_execution_result_confirmed' => (bool) ($options['completion_execution_result_confirmed'] ?? false),
            'controlled_completion_result_confirmation_required' => true,
            'controlled_completion_result_confirmed' => (bool) ($options['controlled_completion_result_confirmed'] ?? false),
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

    private function resultReviewDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'completion_execution_result_confirmed' => (bool) ($options['completion_execution_result_confirmed'] ?? false),
            'controlled_completion_result_confirmed' => (bool) ($options['controlled_completion_result_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'result_review_go_decision' => $pass ? 'RESULT_REVIEW_GO' : 'NO_GO',
            'next_recommendation' => $pass ? self::C164_OPERATOR_GO_NO_GO_RECOMMENDATION : self::RUN_CODE,
        ];
    }

    private function nextOperatorGoNoGoDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C164_OPERATOR_GO_NO_GO_RECOMMENDATION : self::RUN_CODE,
            'next_scope' => $pass ? 'C164 post-handoff activation completion operator GO/NO-GO review only' : 'targeted C164 result review repair',
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'operator_go_no_go_required_next' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function resultReviewManifest(array $execution, array $completionLoad, bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_completion_result_review',
            'source_artifact' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION',
            'source_artifact_path' => self::DEFAULT_C164_EXECUTION_ARTIFACT,
            'source_artifact_hash' => $execution['artifact_hash'] ?? null,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C164_EXECUTION_FILE_SHA1,
            'completion_execution_completed' => (bool) ($execution['post_handoff_activation_completion_execution_completed'] ?? false),
            'completion_execution_result_reviewed' => $pass,
            'ready_for_operator_go_no_go_review' => $pass,
            'controlled_completion_path' => $completionLoad['path'],
            'controlled_completion_hash' => $completionLoad['actual_hash'],
            'controlled_completion_file_sha1' => $completionLoad['actual_file_sha1'],
            'controlled_completion_record_count' => is_array($completionLoad['payload']['output_rows'] ?? null) ? count($completionLoad['payload']['output_rows']) : 0,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_result_reviewed' => $pass,
            'backup_candidate_result_reviewed' => $pass,
            'comparator_candidate_result_reviewed' => false,
            'result_review_artifact_only' => true,
            'controlled_completion_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'result_review_used_for_free_publication' => false,
            'result_review_used_for_plan_confirm_mutation' => false,
            'result_review_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function resultReviewChecklist(bool $pass, array $options): array
    {
        return [
            'result_reviewed' => $pass,
            'c164_completion_execution_source_lock_reviewed' => $pass,
            'controlled_completion_lock_reviewed' => $pass,
            'result_review_required' => true,
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'completion_execution_result_confirmed' => (bool) ($options['completion_execution_result_confirmed'] ?? false),
            'controlled_completion_result_confirmed' => (bool) ($options['controlled_completion_result_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_result_review_gate_required' => true,
            'negative_controlled_completion_result_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'controlled_completion_lock_required' => true,
            'result_review_only' => true,
            'controlled_completion_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c164_result_review' => false,
            'operator_go_no_go_review_required_next' => $pass,
        ];
    }

    private function documentationHygieneGuardSummary(array $executionLoad, array $completionLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c164_execution_convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            'controlled_completion_convert_from_json_pass' => $completionLoad['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => array_values(array_unique(array_merge($executionLoad['case_insensitive_duplicate_keys'], $completionLoad['case_insensitive_duplicate_keys']))),
            'c164_execution_artifact_not_modified' => true,
            'controlled_completion_artifact_not_modified' => true,
            'c164_result_review_is_not_operator_decision' => true,
            'c164_topic_number_retained_for_next_stage' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'current_stage' => self::RUN_CODE,
            'topic_code' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW',
            'c164_execution_carried_forward' => true,
            'controlled_completion_carried_forward' => true,
            'completion_result_review_pass' => $pass,
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C164_OPERATOR_GO_NO_GO_RECOMMENDATION : self::RUN_CODE,
            'planned_next_scope' => $pass ? 'same-topic C164 completion operator GO/NO-GO review only; controlled completion evidence is reviewed while mutation, activated-catalog reads, live rollout, and free publication remain disabled' : 'targeted C164 execution lock, controlled completion lock, confirmation, guard, or cleanup repair',
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'planned_next_required_inputs' => $pass ? [
                'locked C164 completion result review artifact hash',
                'locked C164 completion result review file SHA1',
                'operator decision GO or NO_GO',
                'PLAN/CONFIRM unchanged evidence',
                'live rollout still disabled',
                'free publication still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C164 result review validates C164 completion execution artifact_hash and file SHA1 locks.',
            'C164 result review validates controlled completion artifact hash, file SHA1, and row integrity.',
            'C164 result review confirms controlled completion evidence is reviewed, not live-rolled out.',
            'C164 result review does not mutate PLAN/CONFIRM, read the activated catalog, execute live PLAN/CONFIRM rollout, or free-publish output.',
            'C164 result review keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C164 result review may only recommend same-topic operator GO/NO-GO review next.',
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

    private function sourceArtifactLocks(array $executionLoad, array $completionLoad): array
    {
        return [
            'c164_plan_confirm_completion_post_handoff_activation_completion_execution' => [
                'artifact_path' => $executionLoad['path'],
                'expected_artifact_hash' => $executionLoad['expected_hash'],
                'actual_artifact_hash' => $executionLoad['actual_hash'],
                'artifact_hash_match' => $executionLoad['hash_match'],
                'expected_file_sha1' => $executionLoad['expected_file_sha1'],
                'actual_file_sha1' => $executionLoad['actual_file_sha1'],
                'file_sha1_match' => $executionLoad['file_sha1_match'],
                'convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            ],
            'controlled_completion' => [
                'artifact_path' => $completionLoad['path'],
                'expected_artifact_hash' => $completionLoad['expected_hash'],
                'actual_artifact_hash' => $completionLoad['actual_hash'],
                'artifact_hash_match' => $completionLoad['hash_match'],
                'expected_file_sha1' => $completionLoad['expected_file_sha1'],
                'actual_file_sha1' => $completionLoad['actual_file_sha1'],
                'file_sha1_match' => $completionLoad['file_sha1_match'],
                'convert_from_json_pass' => $completionLoad['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $executionLoad, array $completionLoad): array
    {
        return [
            'expected_c164_execution_hash' => $executionLoad['expected_hash'],
            'actual_c164_execution_hash' => $executionLoad['actual_hash'],
            'c164_execution_hash_match' => $executionLoad['hash_match'],
            'expected_c164_execution_file_sha1' => $executionLoad['expected_file_sha1'],
            'actual_c164_execution_file_sha1' => $executionLoad['actual_file_sha1'],
            'c164_execution_file_sha1_match' => $executionLoad['file_sha1_match'],
            'c164_execution_convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            'expected_controlled_completion_hash' => $completionLoad['expected_hash'],
            'actual_controlled_completion_hash' => $completionLoad['actual_hash'],
            'controlled_completion_hash_match' => $completionLoad['hash_match'],
            'expected_controlled_completion_file_sha1' => $completionLoad['expected_file_sha1'],
            'actual_controlled_completion_file_sha1' => $completionLoad['actual_file_sha1'],
            'controlled_completion_file_sha1_match' => $completionLoad['file_sha1_match'],
            'controlled_completion_convert_from_json_pass' => $completionLoad['convert_from_json_pass'],
        ];
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1, string $hashKey): array
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
                $actualHash = $decoded[$hashKey] ?? null;
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
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_BLOCKED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_REJECTED';
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
