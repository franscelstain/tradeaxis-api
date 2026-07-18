<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-79 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-observation-result-review.json';
    public const DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_HASH = '59783060cce101a3c7faa39558ebaef62fcb72c9';
    public const DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_FILE_SHA1 = 'F0A2B58E19E72FEBC5CEF9843B59B628EE3CBD64';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const WATCHLIST_FUNCTION_USED = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const WATCHLIST_FUNCTION_RUNTIME_MODE = 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT';

    private const EXPECTED_C163_RESULT_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C163_RESULT_PHASE_LABEL = 'PR-78 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW';
    private const EXPECTED_C163_RESULT_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const GO_DECISION_FINALIZATION_RECOMMENDATION = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';

    private const GO_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_POST_HANDOFF_ACTIVATION_PROGRESSION_STOPPED';
    private const HOLD_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_POST_HANDOFF_ACTIVATION_PROGRESSION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C163_RESULT_LOCK_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_ARTIFACT_LOCK_MISMATCH';
    private const C163_RESULT_FILE_SHA1_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_FILE_SHA1_LOCK_MISMATCH';
    private const C163_RESULT_CONVERT_FROM_JSON_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C163_RESULT_STATUS_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_STATUS_MISMATCH';
    private const C163_RESULT_PHASE_LABEL_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_PHASE_LABEL_MISMATCH';
    private const C163_RESULT_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_NEXT_RECOMMENDATION_MISMATCH';
    private const C163_RESULT_INCOMPLETE_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_INCOMPLETE';
    private const WATCHLIST_FUNCTION_RESULT_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_WATCHLIST_FUNCTION_OBSERVATION_RESULT_MISMATCH';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_RESULT_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_result_review_pass',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed',
        'post_handoff_activation_observation_result_confirmed',
        'post_handoff_activation_observation_result_review_confirmed',
        'post_handoff_activation_observation_result_stable',
        'controlled_watchlist_function_observation_result_reviewed',
        'c163_post_handoff_activation_observation_complete_confirmed',
        'post_handoff_activation_observation_confirmed',
        'post_handoff_activation_observed',
        'controlled_watchlist_function_observed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c163_post_handoff_activation_observation_lock_valid',
        'c163_plan_confirm_completion_post_handoff_activation_observation_result_review_valid',
        'c163_post_handoff_activation_observation_convert_from_json_pass',
        'c163_post_handoff_activation_observation_complete',
        'controlled_completion_lock_valid',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed_next',
        'watchlist_function_primary_candidate_observed',
        'watchlist_function_backup_candidate_observed',
        'primary_candidate_observation_result_reviewed',
        'backup_candidate_observation_result_reviewed',
        'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review',
        'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review',
        'a01_remains_comparator_only',
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
        'c163_is_same_post_handoff_contract',
        'c163_activation_observation_result_review_only',
        'c163_controlled_completion_only',
        'c163_not_publication',
        'c163_not_unrestricted_publication',
        'c163_not_plan_confirm_mutation',
        'c163_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_RESULT_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_observation_result_reviewed',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c163-*post-handoff-activation-operator-go-no-go*-test.json',
        'storage/app/watchlist/backtest/c163-*activation-operator-*-test.json',
        'storage/app/watchlist/backtest/c163-*go-no-go*-test.json',
        'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-operator-go-no-go*.json',
        'storage/app/watchlist/backtest/.tmp-runtime-c163-post-handoff-activation-operator-negative-*.json',
    ];

    public function execute(
        string $c163PostHandoffActivationObservationResultArtifact = self::DEFAULT_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_ARTIFACT,
        string $expectedC163PostHandoffActivationObservationResultHash = self::DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_HASH,
        string $expectedC163PostHandoffActivationObservationResultFileSha1 = self::DEFAULT_EXPECTED_C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $decision = $this->normalizeDecision((string) ($options['operator_decision'] ?? ''));
        $decisionReason = trim((string) ($options['decision_reason'] ?? ''));
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c163PostHandoffActivationObservationResultArtifact, $expectedC163PostHandoffActivationObservationResultHash, $expectedC163PostHandoffActivationObservationResultFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->rejected($artifact, self::C163_RESULT_LOCK_MISMATCH_STATUS, 'C163 post-handoff activation observation result artifact missing or unreadable.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, $decision, $decisionReason);
            $artifact['c163_post_handoff_activation_observation_result_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C163_RESULT_CONVERT_FROM_JSON_STATUS, 'C163 post-handoff activation observation result artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['hash_match']) {
            return $this->rejected($artifact, self::C163_RESULT_LOCK_MISMATCH_STATUS, 'C163 post-handoff activation observation result artifact_hash mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['file_sha1_match']) {
            return $this->rejected($artifact, self::C163_RESULT_FILE_SHA1_MISMATCH_STATUS, 'C163 post-handoff activation observation result file SHA1 mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $resultReview = $load['payload'];
        if (($resultReview['status'] ?? null) !== self::EXPECTED_C163_RESULT_STATUS || ($resultReview['reason_code'] ?? null) !== self::EXPECTED_C163_RESULT_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C163_RESULT_STATUS_MISMATCH_STATUS, 'C163 observation result status/reason is not operator GO/NO-GO ready.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (($resultReview['phase_label'] ?? null) !== self::EXPECTED_C163_RESULT_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C163_RESULT_PHASE_LABEL_MISMATCH_STATUS, 'C163 observation result phase label mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->resultReviewNextRecommendationMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C163_RESULT_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C163 observation result next recommendation is not C163 operator GO/NO-GO.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->publicationAndPlanGuardClean($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C163 observation result has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->resultReviewComplete($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C163_RESULT_INCOMPLETE_STATUS, 'C163 observation result evidence is incomplete.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->watchlistFunctionResultStable($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::WATCHLIST_FUNCTION_RESULT_MISMATCH_STATUS, 'C163 watchlist function observation result does not match locked operator scope.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->candidateScopeMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C163 observation result candidate scope does not match locked operator GO/NO-GO scope.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::APPROVAL_MISSING_STATUS, 'C163 operator GO/NO-GO review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decision === null) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, null, $decisionReason), self::DECISION_INVALID_STATUS, 'C163 operator GO/NO-GO review requires --operator-decision=GO, NO_GO, or HOLD.', $outputPath, $overwrite, null, $decisionReason);
        }
        if (! (bool) ($options['operator_decision_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_NOT_CONFIRMED_STATUS, 'C163 operator GO/NO-GO review requires --operator-decision-confirmed.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decisionReason === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_REASON_MISSING_STATUS, 'C163 operator GO/NO-GO review requires a non-empty --decision-reason so the decision is auditable.', $outputPath, $overwrite, $decision, $decisionReason);
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
        $artifact = array_merge($artifact, $this->decisionTopLevelState($resultReview, $decision));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-79',
            'internal_checkpoint' => 'C163',
            'topic_code' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW',
            'status' => 'C163_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'reason_code' => 'C163_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_pass' => false,
            'operator_decision_recorded' => false,
            'operator_decision' => 'UNSET',
            'operator_go_decision' => false,
            'operator_no_go_decision' => false,
            'operator_hold_decision' => false,
            'operator_decision_confirmed' => false,
            'operator_decision_reason' => '',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest_created' => false,
            'post_handoff_activation_stopped_no_go' => false,
            'post_handoff_activation_deferred_hold' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed' => false,
            'post_handoff_activation_observation_result_stable' => false,
            'controlled_watchlist_function_observation_result_reviewed' => false,
            'post_handoff_activation_observation_result_confirmed' => false,
            'post_handoff_activation_observed' => false,
            'controlled_watchlist_function_observed' => false,
            'controlled_completion_lock_valid' => false,
            'controlled_completion_path' => '',
            'controlled_completion_hash' => '',
            'controlled_completion_file_sha1' => '',
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
            'c163_observation_result_review_lock_valid' => false,
            'c163_post_handoff_activation_observation_result_review_valid' => false,
            'c163_observation_result_review_convert_from_json_pass' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'c163_is_same_post_handoff_contract' => true,
            'c163_activation_operator_go_no_go_review_only' => true,
            'c163_controlled_completion_only' => true,
            'c163_not_publication' => true,
            'c163_not_unrestricted_publication' => true,
            'c163_not_plan_confirm_mutation' => true,
            'c163_not_live_plan_confirm_rollout' => true,
            'operator_approved' => false,
            'approval_reference' => '',
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C163_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
        ];
    }

    private function decisionTopLevelState(array $resultReview, string $decision): array
    {
        $go = $decision === 'GO';

        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_pass' => $go,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_pass' => $go,
            'operator_decision_recorded' => true,
            'operator_decision' => $decision,
            'operator_go_decision' => $go,
            'operator_no_go_decision' => $decision === 'NO_GO',
            'operator_hold_decision' => $decision === 'HOLD',
            'operator_decision_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review' => $go,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest_created' => true,
            'post_handoff_activation_stopped_no_go' => $decision === 'NO_GO',
            'post_handoff_activation_deferred_hold' => $decision === 'HOLD',
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ] + $this->carryForwardTopLevelState($resultReview, [
            'hash_match' => true,
            'file_sha1_match' => true,
            'convert_from_json_pass' => true,
        ]);
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass, ?string $decision, string $decisionReason): array
    {
        $resultReview = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact = array_merge($artifact, $this->carryForwardTopLevelState($resultReview, $load));
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_go_decision'] = $decision === 'GO';
        $artifact['operator_no_go_decision'] = $decision === 'NO_GO';
        $artifact['operator_hold_decision'] = $decision === 'HOLD';
        $artifact['operator_decision_confirmed'] = (bool) ($options['operator_decision_confirmed'] ?? false);
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        return array_merge($artifact, [
            'c163_observation_result_review_lock_validation_summary' => $this->resultReviewLockValidationSummary($load),
            'c163_post_handoff_activation_observation_result_review_carry_forward_summary' => $this->resultReviewCarryForwardSummary($resultReview, $pass),
            'watchlist_function_operator_go_no_go_summary' => $this->watchlistFunctionOperatorSummary($resultReview, $pass, $decision),
            'candidate_scope_freeze_summary' => $this->candidateScopeFreezeSummary($resultReview, $pass, $decision),
            'operator_decision_validation_summary' => $this->operatorDecisionValidationSummary($options, $decision, $decisionReason, $pass),
            'temporary_negative_artifact_guard_summary' => $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths),
            'c163_operator_go_no_go_decision' => $this->operatorGoNoGoDecision($decision, $decisionReason, $pass),
            'next_plan_confirm_completion_post_handoff_activation_decision' => $this->nextActivationDecision($decision, $pass),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest' => $this->operatorManifest($resultReview, $decision, $decisionReason, $pass),
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_checklist' => $this->operatorChecklist($options, $decision, $decisionReason, $pass),
            'c163_candidate_post_handoff_activation_operator_go_no_go_scorecard' => $this->candidateScorecard($pass, $decision),
            'publication_plan_confirm_safety_summary' => $this->publicationPlanConfirmSafetySummary($resultReview),
            'documentation_hygiene_guard_summary' => $this->documentationHygieneGuardSummary($load),
            'progress_summary' => $this->progressSummary($pass, $decision),
            'planned_next_summary' => $this->plannedNextSummary($pass, $decision),
            'diagnostics' => $this->diagnostics(),
            'failure_attribution_summary' => $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C163_OPERATOR_GO_NO_GO_PENDING')]),
        ]);
    }

    private function carryForwardTopLevelState(array $resultReview, array $load): array
    {
        return [
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed'] ?? false),
            'post_handoff_activation_observation_result_stable' => (bool) ($resultReview['post_handoff_activation_observation_result_stable'] ?? false),
            'controlled_watchlist_function_observation_result_reviewed' => (bool) ($resultReview['controlled_watchlist_function_observation_result_reviewed'] ?? false),
            'post_handoff_activation_observation_result_confirmed' => (bool) ($resultReview['post_handoff_activation_observation_result_confirmed'] ?? false),
            'post_handoff_activation_observed' => (bool) ($resultReview['post_handoff_activation_observed'] ?? false),
            'controlled_watchlist_function_observed' => (bool) ($resultReview['controlled_watchlist_function_observed'] ?? false),
            'controlled_completion_lock_valid' => (bool) ($resultReview['controlled_completion_lock_valid'] ?? false),
            'controlled_completion_path' => (string) ($resultReview['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($resultReview['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($resultReview['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($resultReview['controlled_completion_record_count'] ?? 0),
            'watchlist_function_used' => (string) ($resultReview['watchlist_function_used'] ?? ''),
            'watchlist_function_runtime_mode' => (string) ($resultReview['watchlist_function_runtime_mode'] ?? self::WATCHLIST_FUNCTION_RUNTIME_MODE),
            'watchlist_function_source_artifact' => (string) ($resultReview['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => (bool) ($resultReview['watchlist_function_primary_candidate_observed'] ?? false),
            'watchlist_function_backup_candidate_observed' => (bool) ($resultReview['watchlist_function_backup_candidate_observed'] ?? false),
            'watchlist_function_comparator_candidate_observed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($resultReview['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
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
            'c163_observation_result_review_lock_valid' => (bool) (($load['hash_match'] ?? false) && ($load['file_sha1_match'] ?? false) && ($load['convert_from_json_pass'] ?? false)),
            'c163_post_handoff_activation_observation_result_review_valid' => $this->resultReviewComplete($resultReview),
            'c163_observation_result_review_convert_from_json_pass' => (bool) ($load['convert_from_json_pass'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => (bool) ($resultReview['a01_remains_comparator_only'] ?? true),
            'c163_is_same_post_handoff_contract' => true,
            'c163_activation_operator_go_no_go_review_only' => true,
            'c163_controlled_completion_only' => true,
            'c163_not_publication' => true,
            'c163_not_unrestricted_publication' => true,
            'c163_not_plan_confirm_mutation' => true,
            'c163_not_live_plan_confirm_rollout' => true,
        ];
    }

    private function resultReviewComplete(array $resultReview): bool
    {
        foreach (self::REQUIRED_RESULT_TRUE_FIELDS as $field) {
            if (($resultReview[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_RESULT_FALSE_FIELDS as $field) {
            if (($resultReview[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($resultReview['topic_code'] ?? null) === 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION'
            && ($resultReview['topic_stage'] ?? null) === 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW'
            && (int) ($resultReview['controlled_completion_record_count'] ?? 0) === 2;
    }

    private function watchlistFunctionResultStable(array $resultReview): bool
    {
        return ($resultReview['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION_USED
            && ($resultReview['watchlist_function_runtime_mode'] ?? null) === self::WATCHLIST_FUNCTION_RUNTIME_MODE
            && ($resultReview['controlled_watchlist_function_observed'] ?? null) === true
            && ($resultReview['controlled_watchlist_function_observation_result_reviewed'] ?? null) === true
            && ($resultReview['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($resultReview['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($resultReview['watchlist_function_comparator_candidate_observed'] ?? null) === false
            && ($resultReview['runtime_bridge_active'] ?? null) === true
            && ($resultReview['weekly_swing_watchlist_runtime_active'] ?? null) === true
            && ($resultReview['weekly_swing_watchlist_live_output_enabled'] ?? null) === true
            && ($resultReview['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? null) === true
            && ($resultReview['weekly_swing_watchlist_official_output_published'] ?? null) === false;
    }

    private function candidateScopeMatches(array $resultReview): bool
    {
        return ($resultReview['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($resultReview['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($resultReview['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($resultReview['primary_candidate_observation_result_reviewed'] ?? null) === true
            && ($resultReview['backup_candidate_observation_result_reviewed'] ?? null) === true
            && ($resultReview['comparator_candidate_observation_result_reviewed'] ?? null) === false
            && ($resultReview['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review'] ?? null) === true
            && ($resultReview['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review'] ?? null) === true
            && ($resultReview['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review'] ?? null) === false
            && ($resultReview['a01_remains_comparator_only'] ?? null) === true
            && ($resultReview['a01_promoted'] ?? false) !== true;
    }

    private function publicationAndPlanGuardClean(array $source): bool
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
            if (($source[$field] ?? null) === true) {
                return false;
            }
        }
        foreach ([
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_manifest', 'activation_observation_result_used_for_free_publication'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_manifest', 'activation_observation_result_used_for_plan_confirm_mutation'],
            ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_manifest', 'activation_observation_result_used_for_live_plan_confirm_rollout'],
            ['next_plan_confirm_completion_post_handoff_activation_decision', 'free_publication_allowed_next'],
            ['next_plan_confirm_completion_post_handoff_activation_decision', 'plan_confirm_mutation_allowed_next'],
            ['next_plan_confirm_completion_post_handoff_activation_decision', 'live_plan_confirm_rollout_allowed_next'],
        ] as $path) {
            if ($this->valueAt($source, $path) !== false) {
                return false;
            }
        }

        return true;
    }

    private function resultReviewNextRecommendationMatches(array $resultReview): bool
    {
        return ($resultReview['next_step_recommendation'] ?? null) === self::EXPECTED_C163_RESULT_NEXT_RECOMMENDATION
            && $this->valueAt($resultReview, ['next_plan_confirm_completion_post_handoff_activation_decision', 'next_recommendation']) === self::EXPECTED_C163_RESULT_NEXT_RECOMMENDATION
            && $this->valueAt($resultReview, ['planned_next_summary', 'planned_next_review']) === self::EXPECTED_C163_RESULT_NEXT_RECOMMENDATION;
    }

    private function resultReviewLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C163_POST_HANDOFF_ACTIVATION_OBSERVATION_RESULT_REVIEW',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function resultReviewCarryForwardSummary(array $resultReview, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c163_result_review_valid' => $this->resultReviewComplete($resultReview),
            'topic_code' => (string) ($resultReview['topic_code'] ?? ''),
            'topic_stage' => (string) ($resultReview['topic_stage'] ?? ''),
            'observation_result_stable' => (bool) ($resultReview['post_handoff_activation_observation_result_stable'] ?? false),
            'controlled_watchlist_function_result_reviewed' => (bool) ($resultReview['controlled_watchlist_function_observation_result_reviewed'] ?? false),
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'operator_go_no_go_review_valid' => $pass,
        ];
    }

    private function watchlistFunctionOperatorSummary(array $resultReview, bool $pass, ?string $decision): array
    {
        return [
            'validation_completed' => true,
            'watchlist_function_used' => (string) ($resultReview['watchlist_function_used'] ?? ''),
            'expected_watchlist_function_used' => self::WATCHLIST_FUNCTION_USED,
            'watchlist_function_runtime_mode' => (string) ($resultReview['watchlist_function_runtime_mode'] ?? ''),
            'watchlist_function_result_stable' => $this->watchlistFunctionResultStable($resultReview),
            'operator_decision' => $decision ?? 'INVALID',
            'operator_go_no_go_reviewed' => $pass,
            'primary_candidate_reviewed' => $pass,
            'backup_candidate_reviewed' => $pass,
            'comparator_candidate_reviewed' => false,
            'official_output_published' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $resultReview, bool $pass, ?string $decision): array
    {
        $go = $pass && $decision === 'GO';

        return [
            'validation_completed' => true,
            'candidate_scope_match' => $this->candidateScopeMatches($resultReview),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_finalization' => $go,
            'backup_candidate_ready_for_finalization' => $go,
            'comparator_candidate_ready_for_finalization' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function operatorDecisionValidationSummary(array $options, ?string $decision, string $reason, bool $pass): array
    {
        return [
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
            'decision_reason_present' => $reason !== '',
            'operator_go_no_go_review_valid' => $pass,
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

    private function operatorGoNoGoDecision(?string $decision, string $reason, bool $pass): array
    {
        return [
            'decision_recorded' => $pass,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_reason' => $reason,
            'operator_go_decision' => $decision === 'GO',
            'operator_no_go_decision' => $decision === 'NO_GO',
            'operator_hold_decision' => $decision === 'HOLD',
            'ready_for_go_decision_finalization_review' => $pass && $decision === 'GO',
            'post_handoff_activation_stopped_no_go' => $pass && $decision === 'NO_GO',
            'post_handoff_activation_deferred_hold' => $pass && $decision === 'HOLD',
        ];
    }

    private function nextActivationDecision(?string $decision, bool $pass): array
    {
        return [
            'decision_valid' => $pass,
            'operator_decision' => $decision ?? 'INVALID',
            'next_recommendation' => $pass ? $this->nextRecommendationForDecision((string) $decision) : 'C163_TARGETED_OBSERVATION_RESULT_OR_OPERATOR_DECISION_REPAIR',
            'same_topic_number_for_next_stage' => true,
            'free_publication_allowed_next' => false,
            'unrestricted_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function operatorManifest(array $resultReview, ?string $decision, string $reason, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_operator_go_no_go_review',
            'operator_go_no_go_artifact_only' => true,
            'source_result_review_artifact_hash' => (string) ($resultReview['artifact_hash'] ?? ''),
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_reason' => $reason,
            'operator_go_no_go_review_pass' => $pass && $decision === 'GO',
            'ready_for_go_decision_finalization_review' => $pass && $decision === 'GO',
            'watchlist_function_used' => self::WATCHLIST_FUNCTION_USED,
            'watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
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
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_confirmed' => (bool) ($options['operator_decision_confirmed'] ?? false),
            'decision_reason_present' => $reason !== '',
            'artifact_only' => true,
            'same_topic_number_for_next_stage' => true,
            'source_observation_result_review_lock_required' => true,
            'controlled_watchlist_function_result_required' => true,
            'weekly_swing_stock_recommendation_free_published_in_c163_operator_review' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScorecard(bool $pass, ?string $decision): array
    {
        $go = $pass && $decision === 'GO';

        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'ready_for_go_decision_finalization_review' => $go,
                'watchlist_function_observed' => true,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'ready_for_go_decision_finalization_review' => $go,
                'watchlist_function_observed' => true,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'ready_for_go_decision_finalization_review' => false,
                'watchlist_function_observed' => false,
                'free_published' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $resultReview): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($resultReview),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
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
            'documentation_hygiene_guard_reviewed' => true,
            'c163_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c163_result_review_artifact_not_modified' => true,
            'c163_operator_go_no_go_review_is_artifact_only_not_free_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass, ?string $decision): array
    {
        return [
            'progress_marker' => 'PR-79_C163_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW',
            'topic_code' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW',
            'c163_result_review_carried_forward' => true,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_go_no_go_review_completed' => $pass,
            'go_decision_finalization_allowed_next' => $pass && $decision === 'GO',
            'same_topic_number_for_next_stage' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass, ?string $decision): array
    {
        return [
            'planned_next_review' => $pass ? $this->nextRecommendationForDecision((string) $decision) : 'C163_TARGETED_OBSERVATION_RESULT_OR_OPERATOR_DECISION_REPAIR',
            'planned_next_scope' => $pass && $decision === 'GO'
                ? 'same-topic C163 post-handoff activation go decision finalization review only; still no free publication or PLAN/CONFIRM mutation from operator review'
                : 'operator decision closed or targeted C163 observation-result/operator-decision repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass && $decision === 'GO' ? [
                'locked C163 operator go/no-go artifact hash',
                'locked C163 operator go/no-go file SHA1',
                'operator GO decision confirmed',
                'free publication still disabled',
                'PLAN/CONFIRM unchanged',
                'live PLAN/CONFIRM rollout still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C163 operator review validates the C163 observation result artifact hash and file SHA1 before recording an operator decision.',
            'C163 operator review records GO, NO_GO, or HOLD only.',
            'C163 operator review does not free-publish output, allow unrestricted publication, mutate PLAN/CONFIRM, or execute live PLAN/CONFIRM rollout.',
            'C163 operator review keeps E02 primary, B01 backup, and A01 comparator-only.',
            'GO may only recommend same-topic post-handoff activation go decision finalization review next.',
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
            return 'C163 operator GO decision recorded for post-handoff activation. Go decision finalization review is allowed next; free publication, unrestricted publication, PLAN/CONFIRM mutation, and live PLAN/CONFIRM rollout remain locked.';
        }
        if ($decision === 'NO_GO') {
            return 'C163 operator NO_GO decision recorded. Post-handoff activation progression is stopped; free publication, unrestricted publication, PLAN/CONFIRM mutation, and live PLAN/CONFIRM rollout remain locked.';
        }

        return 'C163 operator HOLD decision recorded. Post-handoff activation progression is deferred; free publication, unrestricted publication, PLAN/CONFIRM mutation, and live PLAN/CONFIRM rollout remain locked.';
    }

    private function diagnosticConclusionForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C163_POST_HANDOFF_ACTIVATION_OPERATOR_GO_RECORDED_READY_FOR_GO_DECISION_FINALIZATION_NOT_FREE_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        }
        if ($decision === 'NO_GO') {
            return 'C163_POST_HANDOFF_ACTIVATION_OPERATOR_NO_GO_RECORDED_ACTIVATION_PROGRESSION_STOPPED';
        }

        return 'C163_POST_HANDOFF_ACTIVATION_OPERATOR_HOLD_RECORDED_ACTIVATION_PROGRESSION_DEFERRED';
    }

    private function nextRecommendationForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::GO_DECISION_FINALIZATION_RECOMMENDATION;
        }
        if ($decision === 'NO_GO') {
            return 'C163_NO_GO_CLOSE_POST_HANDOFF_ACTIVATION';
        }

        return 'C163_HOLD_KEEP_POST_HANDOFF_ACTIVATION_LOCKED_UNTIL_OPERATOR_WINDOW';
    }

    private function normalizeDecision(string $decision): ?string
    {
        $normalized = strtoupper(trim(str_replace('-', '_', $decision)));
        if (in_array($normalized, ['GO', 'NO_GO', 'HOLD'], true)) {
            return $normalized;
        }

        return null;
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c163_plan_confirm_completion_post_handoff_activation_observation_result_review' => [
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
            'expected_c163_post_handoff_activation_observation_result_hash' => $load['expected_hash'],
            'actual_c163_post_handoff_activation_observation_result_hash' => $load['actual_hash'],
            'c163_post_handoff_activation_observation_result_hash_match' => $load['hash_match'],
            'expected_c163_post_handoff_activation_observation_result_file_sha1' => $load['expected_file_sha1'],
            'actual_c163_post_handoff_activation_observation_result_file_sha1' => $load['actual_file_sha1'],
            'c163_post_handoff_activation_observation_result_file_sha1_match' => $load['file_sha1_match'],
            'c163_post_handoff_activation_observation_result_convert_from_json_pass' => $load['convert_from_json_pass'],
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

    private function failureAttributionSummary(array $failures): array
    {
        return [
            'failure_count' => count(array_values(array_filter($failures))),
            'failures' => array_values(array_filter($failures)),
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_OPERATOR_OR_GO_NO_GO_LOCK',
        ];
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_go_decision'] = $decision === 'GO';
        $artifact['operator_no_go_decision'] = $decision === 'NO_GO';
        $artifact['operator_hold_decision'] = $decision === 'HOLD';
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['diagnostic_conclusion'] = 'C163_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false, $decision);
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
