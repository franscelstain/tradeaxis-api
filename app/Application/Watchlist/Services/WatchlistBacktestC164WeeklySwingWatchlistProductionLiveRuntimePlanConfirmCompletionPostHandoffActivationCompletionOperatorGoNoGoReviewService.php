<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-84 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C164_RESULT_REVIEW_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-result-review.json';
    public const DEFAULT_EXPECTED_C164_RESULT_REVIEW_HASH = '2cf044eb2b860bf165897585d52f5d51783066e3';
    public const DEFAULT_EXPECTED_C164_RESULT_REVIEW_FILE_SHA1 = 'B6909750A1EDD977067460ABD8D992175B9EBE42';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const WATCHLIST_FUNCTION_RUNTIME_MODE = 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT';
    private const CONTROLLED_COMPLETION_ARTIFACT = 'storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json';
    private const CONTROLLED_COMPLETION_HASH = 'e9862d9e7738d0558f107d978f329f97f14b3520';
    private const CONTROLLED_COMPLETION_FILE_SHA1 = 'AB9FC9F714339B78D68132222AC8C398BE7EE1B3';

    private const EXPECTED_C164_RESULT_REVIEW_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C164_RESULT_REVIEW_PHASE_LABEL = 'PR-83 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW';
    private const EXPECTED_C164_RESULT_REVIEW_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const GO_DECISION_FINALIZATION_RECOMMENDATION = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';

    private const GO_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_POST_HANDOFF_ACTIVATION_COMPLETION_PROGRESSION_STOPPED';
    private const HOLD_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_POST_HANDOFF_ACTIVATION_COMPLETION_PROGRESSION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const RESULT_REVIEW_LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH';
    private const RESULT_REVIEW_FILE_SHA1_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH';
    private const RESULT_REVIEW_CONVERT_FROM_JSON_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const RESULT_REVIEW_STATUS_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_STATUS_MISMATCH';
    private const RESULT_REVIEW_PHASE_LABEL_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_PHASE_LABEL_MISMATCH';
    private const RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH';
    private const RESULT_REVIEW_INCOMPLETE_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C164_RESULT_REVIEW_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CONTROLLED_COMPLETION_LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CONTROLLED_COMPLETION_LOCK_MISMATCH';

    private const REQUIRED_RESULT_REVIEW_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass',
        'post_handoff_activation_completion_result_reviewed',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest_created',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed_next',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass',
        'post_handoff_activation_completion_execution_completed',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'weekly_swing_watchlist_official_output_generated',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c164_execution_lock_valid',
        'c164_completion_execution_valid',
        'c164_execution_convert_from_json_pass',
        'controlled_completion_lock_valid',
        'controlled_completion_convert_from_json_pass',
        'controlled_completion_integrity_valid',
        'watchlist_function_primary_candidate_observed',
        'watchlist_function_backup_candidate_observed',
        'operator_approved',
        'result_review_confirmed',
        'completion_execution_result_confirmed',
        'controlled_completion_result_confirmed',
        'controlled_completion_only_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'primary_candidate_completion_result_reviewed',
        'backup_candidate_completion_result_reviewed',
        'a01_remains_comparator_only',
        'c164_is_post_handoff_activation_completion_contract',
        'c164_not_c163_activation_repeat',
        'c164_completion_result_review_only',
        'c164_controlled_completion_only',
        'c164_not_publication',
        'c164_not_unrestricted_publication',
        'c164_not_plan_confirm_mutation',
        'c164_not_live_plan_confirm_rollout',
        'c164_topic_number_retained_for_operator_go_no_go',
        'temporary_negative_artifact_cleanup_confirmed',
        'c164_execution_hash_match',
        'c164_execution_file_sha1_match',
        'controlled_completion_hash_match',
        'controlled_completion_file_sha1_match',
    ];

    private const REQUIRED_RESULT_REVIEW_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_completion_result_reviewed',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c164-*operator-go-no-go*-test.json',
        'storage/app/watchlist/backtest/c164-*operator-*-test.json',
        'storage/app/watchlist/backtest/c164-*go-no-go*-test.json',
        'storage/app/watchlist/backtest/c164-*completion-operator*-test.json',
        'storage/app/watchlist/backtest/c164-*negative-*-test.json',
        'storage/app/watchlist/backtest/c164-*missing-*-test.json',
        'storage/app/watchlist/backtest/c164-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c164-*invalid-*-test.json',
    ];

    public function execute(
        string $c164ResultReviewArtifact = self::DEFAULT_C164_RESULT_REVIEW_ARTIFACT,
        string $expectedC164ResultReviewHash = self::DEFAULT_EXPECTED_C164_RESULT_REVIEW_HASH,
        string $expectedC164ResultReviewFileSha1 = self::DEFAULT_EXPECTED_C164_RESULT_REVIEW_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $decision = $this->normalizeDecision((string) ($options['operator_decision'] ?? ''));
        $decisionReason = trim((string) ($options['decision_reason'] ?? ''));
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($c164ResultReviewArtifact, $expectedC164ResultReviewHash, $expectedC164ResultReviewFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::RESULT_REVIEW_LOCK_MISMATCH_STATUS, 'C164 post-handoff activation completion result review artifact missing or unreadable.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, $decision, $decisionReason);
            $artifact['c164_result_review_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::RESULT_REVIEW_CONVERT_FROM_JSON_STATUS, 'C164 result review artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::RESULT_REVIEW_LOCK_MISMATCH_STATUS, 'C164 result review artifact_hash mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::RESULT_REVIEW_FILE_SHA1_MISMATCH_STATUS, 'C164 result review file SHA1 mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $resultReview = $load['payload'];
        if (($resultReview['status'] ?? null) !== self::EXPECTED_C164_RESULT_REVIEW_STATUS || ($resultReview['reason_code'] ?? null) !== self::EXPECTED_C164_RESULT_REVIEW_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::RESULT_REVIEW_STATUS_MISMATCH_STATUS, 'C164 result review status/reason is not operator GO/NO-GO ready.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (($resultReview['phase_label'] ?? null) !== self::EXPECTED_C164_RESULT_REVIEW_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::RESULT_REVIEW_PHASE_LABEL_MISMATCH_STATUS, 'C164 result review phase label mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->resultReviewNextRecommendationMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C164 result review next recommendation is not C164 operator GO/NO-GO review.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->publicationAndPlanGuardClean($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C164 result review has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->resultReviewComplete($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::RESULT_REVIEW_INCOMPLETE_STATUS, 'C164 result review evidence is incomplete for operator GO/NO-GO.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->candidateScopeMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C164 result review candidate scope does not match locked operator GO/NO-GO scope.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->watchlistFunctionScopeMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS, 'C164 watchlist function scope is not the controlled weekly swing live recommendation generator.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->controlledCompletionLockMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::CONTROLLED_COMPLETION_LOCK_MISMATCH_STATUS, 'C164 result review controlled completion lock does not match the sealed controlled completion artifact.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::APPROVAL_MISSING_STATUS, 'C164 operator GO/NO-GO review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decision === null) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, null, $decisionReason), self::DECISION_INVALID_STATUS, 'C164 operator GO/NO-GO review requires --operator-decision=GO, NO_GO, or HOLD.', $outputPath, $overwrite, null, $decisionReason);
        }
        if (! (bool) ($options['operator_decision_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_NOT_CONFIRMED_STATUS, 'C164 operator GO/NO-GO review requires --operator-decision-confirmed.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decisionReason === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_REASON_MISSING_STATUS, 'C164 operator GO/NO-GO review requires a non-empty --decision-reason.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false, $decision, $decisionReason);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true, $decision, $decisionReason);
        $artifact['status'] = $this->statusForDecision($decision);
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = $this->messageForDecision($decision);
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusionForDecision($decision);
        $artifact['next_step_recommendation'] = $this->nextRecommendationForDecision($decision);
        $artifact = array_merge($artifact, $this->decisionTopLevelState($decision));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-84',
            'internal_checkpoint' => 'C164',
            'topic_code' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW',
            'status' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'reason_code' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass' => false,
            'operator_decision_recorded' => false,
            'operator_decision' => 'UNSET',
            'operator_go_decision' => false,
            'operator_no_go_decision' => false,
            'operator_hold_decision' => false,
            'operator_decision_confirmed' => false,
            'operator_decision_reason' => '',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest_created' => false,
            'post_handoff_activation_completion_stopped_no_go' => false,
            'post_handoff_activation_completion_deferred_hold' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c164_is_post_handoff_activation_completion_contract' => true,
            'c164_not_c163_activation_repeat' => true,
            'c164_operator_go_no_go_review_only' => true,
            'c164_controlled_completion_only' => true,
            'c164_not_publication' => true,
            'c164_not_unrestricted_publication' => true,
            'c164_not_plan_confirm_mutation' => true,
            'c164_not_live_plan_confirm_rollout' => true,
            'c164_topic_number_retained_for_go_decision_finalization' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function decisionTopLevelState(string $decision): array
    {
        $go = $decision === 'GO';

        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass' => $go,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass' => $go,
            'operator_decision_recorded' => true,
            'operator_decision' => $decision,
            'operator_go_decision' => $go,
            'operator_no_go_decision' => $decision === 'NO_GO',
            'operator_hold_decision' => $decision === 'HOLD',
            'operator_decision_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review' => $go,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest_created' => true,
            'post_handoff_activation_completion_stopped_no_go' => $decision === 'NO_GO',
            'post_handoff_activation_completion_deferred_hold' => $decision === 'HOLD',
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass, ?string $decision, string $decisionReason): array
    {
        $resultReview = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact = array_merge($artifact, $this->carryForwardTopLevelState($resultReview, $load));
        $artifact['c164_result_review_lock_validation_summary'] = $this->resultReviewLockValidationSummary($load);
        $artifact['c164_plan_confirm_completion_post_handoff_activation_completion_result_review_carry_forward_summary'] = $this->resultReviewCarryForwardSummary($resultReview, $pass);
        $artifact['watchlist_function_scope_summary'] = $this->watchlistFunctionScopeSummary($resultReview);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($resultReview);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($resultReview, $pass, $decision);
        $artifact['operator_decision_validation_summary'] = $this->operatorDecisionValidationSummary($options, $decision, $decisionReason, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c164_post_handoff_activation_completion_operator_go_no_go_decision'] = $this->operatorGoNoGoDecision($decision, $decisionReason, $pass);
        $artifact['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision'] = $this->nextGoDecisionFinalizationDecision($decision, $pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest'] = $this->operatorManifest($resultReview, $decision, $decisionReason, $pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_checklist'] = $this->operatorChecklist($options, $decision, $decisionReason, $pass);
        $artifact['c164_candidate_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_scorecard'] = $this->candidateScorecard($pass, $decision);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass, $decision);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass, $decision);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C164_OPERATOR_GO_NO_GO_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_go_decision'] = $pass && $decision === 'GO';
        $artifact['operator_no_go_decision'] = $pass && $decision === 'NO_GO';
        $artifact['operator_hold_decision'] = $pass && $decision === 'HOLD';
        $artifact['operator_decision_confirmed'] = (bool) ($options['operator_decision_confirmed'] ?? false);
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        return $artifact;
    }

    private function carryForwardTopLevelState(array $resultReview, array $load): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_executed' => (bool) ($resultReview['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_executed'] ?? false),
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed' => (bool) ($resultReview['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed'] ?? false),
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass' => (bool) ($resultReview['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass'] ?? false),
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass' => (bool) ($resultReview['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass'] ?? false),
            'post_handoff_activation_completion_result_reviewed' => (bool) ($resultReview['post_handoff_activation_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest_created' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest_created'] ?? false),
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed' => (bool) ($resultReview['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed'] ?? false),
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed' => (bool) ($resultReview['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed'] ?? false),
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => (bool) ($resultReview['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass'] ?? false),
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass' => (bool) ($resultReview['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass'] ?? false),
            'post_handoff_activation_completion_execution_completed' => (bool) ($resultReview['post_handoff_activation_completion_execution_completed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($resultReview['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($resultReview['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($resultReview['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($resultReview['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($resultReview['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c164_result_review_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c164_plan_confirm_completion_post_handoff_activation_completion_result_review_valid' => $this->resultReviewComplete($resultReview),
            'c164_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c164_execution_lock_valid' => (bool) ($resultReview['c164_execution_lock_valid'] ?? false),
            'c164_completion_execution_valid' => (bool) ($resultReview['c164_completion_execution_valid'] ?? false),
            'c164_execution_convert_from_json_pass' => (bool) ($resultReview['c164_execution_convert_from_json_pass'] ?? false),
            'controlled_completion_lock_valid' => (bool) ($resultReview['controlled_completion_lock_valid'] ?? false),
            'controlled_completion_integrity_valid' => (bool) ($resultReview['controlled_completion_integrity_valid'] ?? false),
            'controlled_completion_convert_from_json_pass' => (bool) ($resultReview['controlled_completion_convert_from_json_pass'] ?? false),
            'controlled_completion_path' => (string) ($resultReview['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($resultReview['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($resultReview['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($resultReview['controlled_completion_record_count'] ?? 0),
            'watchlist_function_used' => (string) ($resultReview['watchlist_function_used'] ?? ''),
            'watchlist_function_runtime_mode' => (string) ($resultReview['watchlist_function_runtime_mode'] ?? ''),
            'watchlist_function_source_artifact' => (string) ($resultReview['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => (bool) ($resultReview['watchlist_function_primary_candidate_observed'] ?? false),
            'watchlist_function_backup_candidate_observed' => (bool) ($resultReview['watchlist_function_backup_candidate_observed'] ?? false),
            'watchlist_function_comparator_candidate_observed' => false,
            'result_review_confirmed' => (bool) ($resultReview['result_review_confirmed'] ?? false),
            'completion_execution_result_confirmed' => (bool) ($resultReview['completion_execution_result_confirmed'] ?? false),
            'controlled_completion_result_confirmed' => (bool) ($resultReview['controlled_completion_result_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($resultReview['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($resultReview['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($resultReview['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($resultReview['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_completion_result_reviewed' => (bool) ($resultReview['primary_candidate_completion_result_reviewed'] ?? false),
            'backup_candidate_completion_result_reviewed' => (bool) ($resultReview['backup_candidate_completion_result_reviewed'] ?? false),
            'comparator_candidate_completion_result_reviewed' => false,
            'primary_candidate_code' => (string) ($resultReview['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($resultReview['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($resultReview['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'a01_remains_comparator_only' => (bool) ($resultReview['a01_remains_comparator_only'] ?? true),
        ];
    }

    private function resultReviewComplete(array $resultReview): bool
    {
        foreach (self::REQUIRED_RESULT_REVIEW_TRUE_FIELDS as $field) {
            if (($resultReview[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_RESULT_REVIEW_FALSE_FIELDS as $field) {
            if (($resultReview[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($resultReview['topic_code'] ?? null) !== 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION'
            || ($resultReview['topic_stage'] ?? null) !== 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW') {
            return false;
        }
        foreach ([
            ['c164_completion_result_review_decision', 'review_valid'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'review_valid'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'same_topic_c164_continues'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'topic_number_must_not_advance_until_c164_finalization'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'operator_go_no_go_required_next'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'manifest_created'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'ready_for_operator_go_no_go_review'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'result_review_artifact_only'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'controlled_completion_only'],
            ['planned_next_summary', 'same_topic_c164_continues'],
            ['planned_next_summary', 'topic_number_must_not_advance_until_c164_finalization'],
        ] as $path) {
            if ($this->valueAt($resultReview, $path) !== true) {
                return false;
            }
        }
        foreach ([
            ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'free_publication_allowed_next'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'plan_confirm_mutation_allowed_next'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'live_plan_confirm_rollout_allowed_next'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'official_output_published'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'free_publication_allowed'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'unrestricted_publication_allowed'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'plan_confirm_mutation_allowed'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'plan_confirm_mutated'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'live_plan_confirm_rollout_executed'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'result_review_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'result_review_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'result_review_used_for_live_plan_confirm_rollout'],
        ] as $path) {
            if ($this->valueAt($resultReview, $path) !== false) {
                return false;
            }
        }

        return $this->valueAt($resultReview, ['c164_completion_result_review_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($resultReview, ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($resultReview, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE
            && $this->valueAt($resultReview, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest', 'official_weekly_swing_stock_recommendations']) === [];
    }

    private function resultReviewNextRecommendationMatches(array $resultReview): bool
    {
        return ($resultReview['next_step_recommendation'] ?? null) === self::RUN_CODE
            && $this->valueAt($resultReview, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE
            && $this->valueAt($resultReview, ['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision', 'next_recommendation']) === self::RUN_CODE;
    }

    private function candidateScopeMatches(array $resultReview): bool
    {
        return ($resultReview['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($resultReview['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($resultReview['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($resultReview['primary_candidate_completion_result_reviewed'] ?? null) === true
            && ($resultReview['backup_candidate_completion_result_reviewed'] ?? null) === true
            && ($resultReview['comparator_candidate_completion_result_reviewed'] ?? null) === false
            && ($resultReview['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($resultReview['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($resultReview['watchlist_function_comparator_candidate_observed'] ?? null) === false
            && ($resultReview['a01_remains_comparator_only'] ?? null) === true
            && ($resultReview['a01_promoted'] ?? false) !== true;
    }

    private function watchlistFunctionScopeMatches(array $resultReview): bool
    {
        return ($resultReview['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($resultReview['watchlist_function_runtime_mode'] ?? null) === self::WATCHLIST_FUNCTION_RUNTIME_MODE
            && ($resultReview['watchlist_function_source_artifact'] ?? null) === self::CONTROLLED_COMPLETION_ARTIFACT
            && ($resultReview['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($resultReview['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($resultReview['watchlist_function_comparator_candidate_observed'] ?? null) === false;
    }

    private function controlledCompletionLockMatches(array $resultReview): bool
    {
        return ($resultReview['controlled_completion_path'] ?? null) === self::CONTROLLED_COMPLETION_ARTIFACT
            && ($resultReview['controlled_completion_hash'] ?? null) === self::CONTROLLED_COMPLETION_HASH
            && ($resultReview['controlled_completion_file_sha1'] ?? null) === self::CONTROLLED_COMPLETION_FILE_SHA1
            && (int) ($resultReview['controlled_completion_record_count'] ?? 0) === 2
            && ($resultReview['controlled_completion_lock_valid'] ?? null) === true
            && ($resultReview['controlled_completion_integrity_valid'] ?? null) === true
            && ($resultReview['controlled_completion_convert_from_json_pass'] ?? null) === true;
    }

    private function publicationAndPlanGuardClean(array $source): bool
    {
        foreach (self::REQUIRED_RESULT_REVIEW_FALSE_FIELDS as $field) {
            if (in_array($field, [
                'watchlist_function_comparator_candidate_observed',
                'comparator_candidate_completion_result_reviewed',
                'temporary_negative_artifacts_remaining',
            ], true)) {
                continue;
            }
            if (($source[$field] ?? null) === true) {
                return false;
            }
        }

        return true;
    }

    private function resultReviewLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'expected_status' => self::EXPECTED_C164_RESULT_REVIEW_STATUS,
            'actual_status' => is_array($load['payload']) ? ($load['payload']['status'] ?? null) : null,
            'expected_phase_label' => self::EXPECTED_C164_RESULT_REVIEW_PHASE_LABEL,
            'actual_phase_label' => is_array($load['payload']) ? ($load['payload']['phase_label'] ?? null) : null,
            'expected_next_recommendation' => self::RUN_CODE,
            'next_recommendation_match' => is_array($load['payload']) && $this->resultReviewNextRecommendationMatches($load['payload']),
            'lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function resultReviewCarryForwardSummary(array $resultReview, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c164_result_review_valid' => $this->resultReviewComplete($resultReview),
            'topic_code' => (string) ($resultReview['topic_code'] ?? ''),
            'topic_stage' => (string) ($resultReview['topic_stage'] ?? ''),
            'source_result_review_status' => (string) ($resultReview['status'] ?? ''),
            'result_review_pass' => (bool) ($resultReview['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass'] ?? false),
            'ready_for_operator_go_no_go_review' => (bool) ($resultReview['ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review'] ?? false),
            'operator_go_no_go_review_pass' => $pass,
            'controlled_completion_hash' => (string) ($resultReview['controlled_completion_hash'] ?? ''),
            'watchlist_function_used' => (string) ($resultReview['watchlist_function_used'] ?? ''),
        ];
    }

    private function watchlistFunctionScopeSummary(array $resultReview): array
    {
        return [
            'validation_completed' => true,
            'watchlist_function_scope_matches' => $this->watchlistFunctionScopeMatches($resultReview),
            'watchlist_function_used' => (string) ($resultReview['watchlist_function_used'] ?? ''),
            'expected_watchlist_function' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => (string) ($resultReview['watchlist_function_runtime_mode'] ?? ''),
            'expected_watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'source_artifact' => (string) ($resultReview['watchlist_function_source_artifact'] ?? ''),
            'primary_candidate_observed' => (bool) ($resultReview['watchlist_function_primary_candidate_observed'] ?? false),
            'backup_candidate_observed' => (bool) ($resultReview['watchlist_function_backup_candidate_observed'] ?? false),
            'comparator_candidate_observed' => (bool) ($resultReview['watchlist_function_comparator_candidate_observed'] ?? false),
            'operator_review_uses_function_for_publication' => false,
            'operator_review_uses_function_for_plan_confirm_mutation' => false,
            'operator_review_uses_function_for_live_rollout' => false,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $resultReview): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($resultReview),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $resultReview, bool $pass, ?string $decision): array
    {
        $go = $pass && $decision === 'GO';

        return [
            'validation_completed' => true,
            'candidate_scope_matches' => $this->candidateScopeMatches($resultReview),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
        ];
    }

    private function operatorDecisionValidationSummary(array $options, ?string $decision, string $decisionReason, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_decision_required' => true,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_valid' => $decision !== null,
            'operator_decision_confirmed' => (bool) ($options['operator_decision_confirmed'] ?? false),
            'decision_reason_required' => true,
            'decision_reason_present' => $decisionReason !== '',
            'operator_go_no_go_review_pass' => $pass && $decision === 'GO',
        ];
    }

    private function operatorGoNoGoDecision(?string $decision, string $decisionReason, bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'operator_decision' => $decision ?? 'INVALID',
            'decision_reason' => $decisionReason,
            'operator_go_decision' => $pass && $decision === 'GO',
            'operator_no_go_decision' => $pass && $decision === 'NO_GO',
            'operator_hold_decision' => $pass && $decision === 'HOLD',
            'result_review_artifact_locked' => $pass,
            'controlled_completion_evidence_locked' => $pass,
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function nextGoDecisionFinalizationDecision(?string $decision, bool $pass): array
    {
        $go = $pass && $decision === 'GO';

        return [
            'review_valid' => $pass,
            'next_recommendation' => $go ? self::GO_DECISION_FINALIZATION_RECOMMENDATION : $this->nextRecommendationForDecision($decision ?? 'INVALID'),
            'next_scope' => $go ? 'C164 same-topic post-handoff activation completion GO decision finalization review only' : 'C164 same-topic operator decision closure or hold',
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'go_decision_finalization_required_next' => $go,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function operatorManifest(array $resultReview, ?string $decision, string $reason, bool $pass): array
    {
        $go = $pass && $decision === 'GO';

        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review',
            'source_artifact' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW',
            'source_artifact_path' => self::DEFAULT_C164_RESULT_REVIEW_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C164_RESULT_REVIEW_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C164_RESULT_REVIEW_FILE_SHA1,
            'operator_go_no_go_artifact_only' => true,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_reason' => $reason,
            'operator_go_no_go_review_pass' => $go,
            'ready_for_go_decision_finalization_review' => $go,
            'controlled_completion_path' => (string) ($resultReview['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($resultReview['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($resultReview['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($resultReview['controlled_completion_record_count'] ?? 0),
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_operator_reviewed' => $pass,
            'backup_candidate_operator_reviewed' => $pass,
            'comparator_candidate_operator_reviewed' => false,
            'controlled_completion_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'operator_go_no_go_used_for_publication' => false,
            'operator_go_no_go_used_for_plan_confirm_mutation' => false,
            'operator_go_no_go_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function operatorChecklist(array $options, ?string $decision, string $reason, bool $pass): array
    {
        return [
            'operator_go_no_go_reviewed' => $pass,
            'artifact_only' => true,
            'operator_approval_present' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_decision_present' => $decision !== null,
            'operator_decision_confirmed' => (bool) ($options['operator_decision_confirmed'] ?? false),
            'operator_decision_reason_present' => $reason !== '',
            'source_result_review_locked' => $pass,
            'controlled_completion_only_confirmed' => $pass,
            'weekly_swing_stock_recommendation_free_published_in_c164_operator_review' => false,
            'plan_confirm_mutated_in_c164_operator_review' => false,
            'live_plan_confirm_rollout_executed_in_c164_operator_review' => false,
        ];
    }

    private function candidateScorecard(bool $pass, ?string $decision): array
    {
        $go = $pass && $decision === 'GO';

        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'operator_go_no_go_reviewed' => $pass,
                'ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review' => $go,
                'watchlist_function_enabled' => true,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'operator_go_no_go_reviewed' => $pass,
                'ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review' => $go,
                'watchlist_function_enabled' => true,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'operator_go_no_go_reviewed' => false,
                'ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review' => false,
                'watchlist_function_enabled' => false,
                'a01_remains_comparator_only' => true,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
            ],
        ];
    }

    private function progressSummary(bool $pass, ?string $decision): array
    {
        return [
            'current_stage' => self::RUN_CODE,
            'topic_code' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW',
            'source_result_review_carried_forward' => true,
            'operator_go_no_go_review_pass' => $pass && $decision === 'GO',
            'operator_decision' => $decision ?? 'INVALID',
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass, ?string $decision): array
    {
        $go = $pass && $decision === 'GO';

        return [
            'planned_next_review' => $go ? self::GO_DECISION_FINALIZATION_RECOMMENDATION : $this->nextRecommendationForDecision($decision ?? 'INVALID'),
            'planned_next_scope' => $go ? 'same-topic C164 GO decision finalization review only; controlled completion evidence remains locked and no publication or live rollout is opened' : 'same-topic C164 operator decision closure or hold with PLAN/CONFIRM unchanged',
            'same_topic_c164_continues' => true,
            'topic_number_must_not_advance_until_c164_finalization' => true,
            'planned_next_required_inputs' => $go ? [
                'locked C164 operator GO/NO-GO artifact hash',
                'locked C164 operator GO/NO-GO file SHA1',
                'GO decision finalization confirmation',
                'PLAN/CONFIRM unchanged evidence',
                'free publication still disabled',
                'live rollout still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C164 operator GO/NO-GO review validates the locked C164 result review artifact_hash and file SHA1.',
            'C164 operator GO/NO-GO review records GO, NO_GO, or HOLD without opening free publication.',
            'C164 operator GO/NO-GO review uses CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION only as controlled output evidence.',
            'C164 operator GO/NO-GO review keeps PLAN/CONFIRM unchanged, activated-catalog reads disabled, and live rollout disabled.',
            'C164 operator GO/NO-GO review keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C164 operator GO/NO-GO review may only recommend same-topic GO decision finalization after operator GO.',
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'documentation_update_required' => true,
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'same_topic_c164_retained' => true,
            'review_term_meaning' => 'operator decision gate over locked result-review evidence, not a repeated result review',
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
            'c164_plan_confirm_completion_post_handoff_activation_completion_result_review' => [
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
            'expected_c164_result_review_hash' => $load['expected_hash'],
            'actual_c164_result_review_hash' => $load['actual_hash'],
            'c164_result_review_hash_match' => $load['hash_match'],
            'expected_c164_result_review_file_sha1' => $load['expected_file_sha1'],
            'actual_c164_result_review_file_sha1' => $load['actual_file_sha1'],
            'c164_result_review_file_sha1_match' => $load['file_sha1_match'],
            'c164_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_BLOCKED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_decision_reason'] = $decisionReason;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_decision_reason'] = $decisionReason;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function failureAttributionSummary(array $failures): array
    {
        return [
            'failure_count' => count($failures),
            'failures' => $failures,
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_RESULT_REVIEW_OPERATOR_DECISION_OR_CONTROLLED_GUARD',
        ];
    }

    private function statusForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::GO_STATUS;
        }
        if ($decision === 'NO_GO') {
            return self::NO_GO_STATUS;
        }

        return self::HOLD_STATUS;
    }

    private function messageForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C164 operator GO confirms locked post-handoff activation completion result-review evidence is stable enough for same-topic GO decision finalization. No free publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout is opened.';
        }
        if ($decision === 'NO_GO') {
            return 'C164 operator NO_GO stops post-handoff activation completion progression at the operator gate while preserving controlled-output and PLAN/CONFIRM locks.';
        }

        return 'C164 operator HOLD defers post-handoff activation completion progression at the operator gate while preserving controlled-output and PLAN/CONFIRM locks.';
    }

    private function diagnosticConclusionForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_CONFIRMED_READY_FOR_GO_DECISION_FINALIZATION_CONTROLLED_ONLY';
        }
        if ($decision === 'NO_GO') {
            return 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_NO_GO_STOPPED_CONTROLLED_ONLY';
        }

        return 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_HOLD_DEFERRED_CONTROLLED_ONLY';
    }

    private function nextRecommendationForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::GO_DECISION_FINALIZATION_RECOMMENDATION;
        }
        if ($decision === 'NO_GO') {
            return 'C164_NO_GO_CLOSE_POST_HANDOFF_ACTIVATION_COMPLETION_WITH_PLAN_CONFIRM_UNCHANGED';
        }

        return 'C164_HOLD_KEEP_POST_HANDOFF_ACTIVATION_COMPLETION_LOCKED_UNTIL_OPERATOR_WINDOW';
    }

    private function normalizeDecision(string $decision): ?string
    {
        $normalized = strtoupper(trim(str_replace('-', '_', $decision)));
        if ($normalized === 'NOGO') {
            $normalized = 'NO_GO';
        }

        return in_array($normalized, ['GO', 'NO_GO', 'HOLD'], true) ? $normalized : null;
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1): array
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

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            throw new \RuntimeException('Output artifact already exists: '.$outputPath);
        }

        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $directory = dirname($outputPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $artifact;
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
