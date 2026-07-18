<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-85 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';

    public const DEFAULT_C164_OPERATOR_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C164_OPERATOR_HASH = 'df6957364fb3090d64ce767990fdab3964e2573d';
    public const DEFAULT_EXPECTED_C164_OPERATOR_FILE_SHA1 = '3F6C5BCD92864B89CDF2A974FD0C9F9367EDCD2C';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const WATCHLIST_FUNCTION_RUNTIME_MODE = 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT';

    private const EXPECTED_C164_OPERATOR_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C164_OPERATOR_PHASE_LABEL = 'PR-84 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';
    private const EXPECTED_C164_OPERATOR_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const NEXT_BOUNDARY_RECOMMENDATION = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW';

    private const PASS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_HANDOFF_ACTIVATION_COMPLETION_CLOSED_READY_FOR_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_FINALIZATION_NOT_CONFIRMED_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING';
    private const COMPLETION_FINALIZATION_NOT_CONFIRMED_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_COMPLETION_FINALIZATION_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const OPERATOR_LOCK_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH';
    private const OPERATOR_FILE_SHA1_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_FILE_SHA1_LOCK_MISMATCH';
    private const OPERATOR_CONVERT_FROM_JSON_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const OPERATOR_STATUS_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_STATUS_MISMATCH';
    private const OPERATOR_PHASE_LABEL_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_PHASE_LABEL_MISMATCH';
    private const OPERATOR_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_NO_GO_NEXT_RECOMMENDATION_MISMATCH';
    private const OPERATOR_GO_INVALID_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C164_OPERATOR_GO_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C164_OPERATOR_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass',
        'operator_decision_recorded',
        'operator_go_decision',
        'operator_decision_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest_created',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass',
        'post_handoff_activation_completion_result_reviewed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass',
        'post_handoff_activation_completion_execution_completed',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'weekly_swing_watchlist_official_output_generated',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c164_result_review_lock_valid',
        'c164_plan_confirm_completion_post_handoff_activation_completion_result_review_valid',
        'c164_result_review_convert_from_json_pass',
        'c164_execution_lock_valid',
        'c164_completion_execution_valid',
        'c164_execution_convert_from_json_pass',
        'controlled_completion_lock_valid',
        'controlled_completion_integrity_valid',
        'controlled_completion_convert_from_json_pass',
        'watchlist_function_primary_candidate_observed',
        'watchlist_function_backup_candidate_observed',
        'result_review_confirmed',
        'completion_execution_result_confirmed',
        'controlled_completion_result_confirmed',
        'controlled_completion_only_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'a01_remains_comparator_only',
        'c164_is_post_handoff_activation_completion_contract',
        'c164_not_c163_activation_repeat',
        'c164_operator_go_no_go_review_only',
        'c164_controlled_completion_only',
        'c164_not_publication',
        'c164_not_unrestricted_publication',
        'c164_not_plan_confirm_mutation',
        'c164_not_live_plan_confirm_rollout',
        'c164_topic_number_retained_for_go_decision_finalization',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C164_OPERATOR_FALSE_FIELDS = [
        'operator_no_go_decision',
        'operator_hold_decision',
        'post_handoff_activation_completion_stopped_no_go',
        'post_handoff_activation_completion_deferred_hold',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'watchlist_function_comparator_candidate_observed',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c164-*completion-finalization*-test.json',
        'storage/app/watchlist/backtest/c164-*completion-go-decision*-test.json',
        'storage/app/watchlist/backtest/c164-*go-decision-finalization*-test.json',
        'storage/app/watchlist/backtest/c164-*negative-*-test.json',
        'storage/app/watchlist/backtest/c164-*missing-*-test.json',
        'storage/app/watchlist/backtest/c164-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c164-*invalid-*-test.json',
    ];

    public function execute(
        string $c164OperatorArtifact = self::DEFAULT_C164_OPERATOR_ARTIFACT,
        string $expectedC164OperatorHash = self::DEFAULT_EXPECTED_C164_OPERATOR_HASH,
        string $expectedC164OperatorFileSha1 = self::DEFAULT_EXPECTED_C164_OPERATOR_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($c164OperatorArtifact, $expectedC164OperatorHash, $expectedC164OperatorFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::OPERATOR_LOCK_MISMATCH_STATUS, 'C164 operator GO/NO-GO artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c164_operator_go_no_go_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::OPERATOR_CONVERT_FROM_JSON_STATUS, 'C164 operator GO/NO-GO artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::OPERATOR_LOCK_MISMATCH_STATUS, 'C164 operator GO/NO-GO artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::OPERATOR_FILE_SHA1_MISMATCH_STATUS, 'C164 operator GO/NO-GO file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $operator = $load['payload'];
        if (($operator['status'] ?? null) !== self::EXPECTED_C164_OPERATOR_STATUS || ($operator['reason_code'] ?? null) !== self::EXPECTED_C164_OPERATOR_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OPERATOR_STATUS_MISMATCH_STATUS, 'C164 operator GO/NO-GO status/reason is not GO finalization ready.', $outputPath, $overwrite);
        }
        if (($operator['phase_label'] ?? null) !== self::EXPECTED_C164_OPERATOR_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OPERATOR_PHASE_LABEL_MISMATCH_STATUS, 'C164 operator GO/NO-GO phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->operatorNextRecommendationMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OPERATOR_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C164 operator GO/NO-GO next recommendation is not C164 GO decision finalization.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C164 operator evidence has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->operatorGoComplete($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OPERATOR_GO_INVALID_STATUS, 'C164 operator GO evidence is incomplete or not valid for finalization.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C164 operator candidate scope does not match locked finalization scope.', $outputPath, $overwrite);
        }
        if (! $this->watchlistFunctionScopeMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS, 'C164 watchlist function scope is not controlled primary/backup-only evidence.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C164 GO decision finalization requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['go_decision_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::GO_FINALIZATION_NOT_CONFIRMED_STATUS, 'C164 requires --go-decision-finalization-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_completion_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::COMPLETION_FINALIZATION_NOT_CONFIRMED_STATUS, 'C164 requires --post-handoff-activation-completion-finalization-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C164 finalizes the operator GO decision for post-handoff activation completion. The C164 completion topic is closed; PLAN/CONFIRM mutation, activated-catalog reads, live rollout, unrestricted publication, and free publication remain locked.';
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_GO_FINALIZED_TOPIC_CLOSED_READY_FOR_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::NEXT_BOUNDARY_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-85',
            'internal_checkpoint' => 'C164',
            'topic_code' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW',
            'status' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'reason_code' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_pass' => false,
            'operator_decision' => 'NO_GO',
            'operator_go_decision' => false,
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'go_decision_finalization_confirmed' => false,
            'post_handoff_activation_completion_finalization_confirmed' => false,
            'post_handoff_activation_completion_closed' => false,
            'c164_topic_complete_after_finalization' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_review' => false,
            'production_live_runtime_plan_confirm_controlled_rollout_boundary_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest_created' => false,
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
            'c164_completion_go_decision_finalization_review_only' => true,
            'c164_controlled_completion_only' => true,
            'c164_not_publication' => true,
            'c164_not_unrestricted_publication' => true,
            'c164_not_plan_confirm_mutation' => true,
            'c164_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $operator = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact = array_merge($artifact, $this->carryForwardTopLevelState($operator, $load, $options, $pass, $temporaryNegativePaths));
        $artifact['c164_operator_go_no_go_lock_validation_summary'] = $this->operatorLockValidationSummary($load, $operator);
        $artifact['c164_operator_go_no_go_carry_forward_summary'] = $this->operatorCarryForwardSummary($operator);
        $artifact['post_handoff_activation_completion_finalization_guard_summary'] = $this->completionFinalizationGuardSummary($operator, $pass);
        $artifact['watchlist_function_scope_summary'] = $this->watchlistFunctionScopeSummary($operator);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($operator, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c164_go_decision_finalization_decision'] = $this->goDecisionFinalizationDecision($pass, $options);
        $artifact['next_plan_confirm_controlled_rollout_boundary_decision'] = $this->nextBoundaryDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest'] = $this->goDecisionFinalizationManifest($operator, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_checklist'] = $this->goDecisionFinalizationChecklist($pass, $options);
        $artifact['c164_candidate_post_handoff_activation_completion_go_decision_finalization_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($operator);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C164_FINALIZATION_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');

        return $artifact;
    }

    private function carryForwardTopLevelState(array $operator, array $load, array $options, bool $pass, array $temporaryNegativePaths): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_executed' => (bool) ($operator['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_executed'] ?? false),
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass' => (bool) ($operator['weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_pass'] ?? false),
            'post_handoff_activation_completion_result_reviewed' => (bool) ($operator['post_handoff_activation_completion_result_reviewed'] ?? false),
            'post_handoff_activation_completion_execution_completed' => (bool) ($operator['post_handoff_activation_completion_execution_completed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'weekly_swing_watchlist_official_output_generated' => (bool) ($operator['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($operator['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($operator['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($operator['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($operator['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($operator['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c164_operator_go_no_go_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c164_operator_go_no_go_review_valid' => $this->operatorGoComplete($operator),
            'c164_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c164_result_review_lock_valid' => (bool) ($operator['c164_result_review_lock_valid'] ?? false),
            'c164_plan_confirm_completion_post_handoff_activation_completion_result_review_valid' => (bool) ($operator['c164_plan_confirm_completion_post_handoff_activation_completion_result_review_valid'] ?? false),
            'c164_result_review_convert_from_json_pass' => (bool) ($operator['c164_result_review_convert_from_json_pass'] ?? false),
            'c164_execution_lock_valid' => (bool) ($operator['c164_execution_lock_valid'] ?? false),
            'c164_completion_execution_valid' => (bool) ($operator['c164_completion_execution_valid'] ?? false),
            'controlled_completion_lock_valid' => (bool) ($operator['controlled_completion_lock_valid'] ?? false),
            'controlled_completion_integrity_valid' => (bool) ($operator['controlled_completion_integrity_valid'] ?? false),
            'controlled_completion_convert_from_json_pass' => (bool) ($operator['controlled_completion_convert_from_json_pass'] ?? false),
            'controlled_completion_path' => (string) ($operator['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($operator['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($operator['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($operator['controlled_completion_record_count'] ?? 0),
            'watchlist_function_used' => (string) ($operator['watchlist_function_used'] ?? ''),
            'watchlist_function_runtime_mode' => (string) ($operator['watchlist_function_runtime_mode'] ?? ''),
            'watchlist_function_source_artifact' => (string) ($operator['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => (bool) ($operator['watchlist_function_primary_candidate_observed'] ?? false),
            'watchlist_function_backup_candidate_observed' => (bool) ($operator['watchlist_function_backup_candidate_observed'] ?? false),
            'watchlist_function_comparator_candidate_observed' => false,
            'operator_decision' => $pass ? 'GO' : (string) ($operator['operator_decision'] ?? 'NO_GO'),
            'operator_go_decision' => $pass,
            'operator_go_decision_confirmed' => $pass,
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_completion_finalization_confirmed' => (bool) ($options['post_handoff_activation_completion_finalization_confirmed'] ?? false),
            'post_handoff_activation_completion_closed' => $pass,
            'c164_topic_complete_after_finalization' => $pass,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => $temporaryNegativePaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryNegativePaths === [],
            'temporary_negative_artifact_paths' => array_values($temporaryNegativePaths),
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_pass' => true,
            'operator_decision' => 'GO',
            'operator_go_decision' => true,
            'operator_go_decision_confirmed' => true,
            'go_decision_finalized' => true,
            'go_decision_finalization_confirmed' => true,
            'post_handoff_activation_completion_finalization_confirmed' => true,
            'post_handoff_activation_completion_closed' => true,
            'c164_topic_complete_after_finalization' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_review' => true,
            'production_live_runtime_plan_confirm_controlled_rollout_boundary_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest_created' => true,
            'c164_operator_go_no_go_lock_valid' => true,
            'c164_operator_go_no_go_review_valid' => true,
            'c164_operator_go_no_go_convert_from_json_pass' => true,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => true,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => true,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function operatorNextRecommendationMatches(array $operator): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($operator, $path) !== self::RUN_CODE) {
                return false;
            }
        }

        return true;
    }

    private function operatorGoComplete(array $operator): bool
    {
        foreach (self::REQUIRED_C164_OPERATOR_TRUE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C164_OPERATOR_FALSE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($operator['operator_decision'] ?? null) === 'GO'
            && trim((string) ($operator['operator_decision_reason'] ?? '')) !== ''
            && $this->valueAt($operator, ['c164_post_handoff_activation_completion_operator_go_no_go_decision', 'operator_decision']) === 'GO'
            && $this->valueAt($operator, ['c164_post_handoff_activation_completion_operator_go_no_go_decision', 'operator_go_decision']) === true
            && $this->valueAt($operator, ['c164_post_handoff_activation_completion_operator_go_no_go_decision', 'result_review_artifact_locked']) === true
            && $this->valueAt($operator, ['c164_post_handoff_activation_completion_operator_go_no_go_decision', 'controlled_completion_evidence_locked']) === true
            && $this->valueAt($operator, ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision', 'review_valid']) === true
            && $this->valueAt($operator, ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision', 'go_decision_finalization_required_next']) === true
            && $this->valueAt($operator, ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision', 'same_topic_c164_continues']) === true
            && $this->valueAt($operator, ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision', 'topic_number_must_not_advance_until_c164_finalization']) === true
            && $this->valueAt($operator, ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision', 'free_publication_allowed_next']) === false
            && $this->valueAt($operator, ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision', 'plan_confirm_mutation_allowed_next']) === false
            && $this->valueAt($operator, ['next_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_decision', 'live_plan_confirm_rollout_allowed_next']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest', 'operator_decision']) === 'GO'
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest', 'operator_go_no_go_artifact_only']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest', 'ready_for_go_decision_finalization_review']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest', 'operator_go_no_go_used_for_publication']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest', 'operator_go_no_go_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_manifest', 'operator_go_no_go_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_checklist', 'artifact_only']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_checklist', 'weekly_swing_stock_recommendation_free_published_in_c164_operator_review']) === false;
    }

    private function publicationAndPlanGuardClean(array $operator): bool
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
            if (($operator[$field] ?? false) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $operator): bool
    {
        return ($operator['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($operator['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($operator['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($operator['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review'] ?? null) === true
            && ($operator['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review'] ?? null) === true
            && ($operator['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review'] ?? null) === false
            && ($operator['a01_remains_comparator_only'] ?? null) === true
            && ($operator['a01_promoted'] ?? false) === false
            && ($operator['candidate_promotion_executed'] ?? false) === false
            && ($operator['candidate_rerank_executed'] ?? false) === false;
    }

    private function watchlistFunctionScopeMatches(array $operator): bool
    {
        return ($operator['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($operator['watchlist_function_runtime_mode'] ?? null) === self::WATCHLIST_FUNCTION_RUNTIME_MODE
            && ($operator['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($operator['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($operator['watchlist_function_comparator_candidate_observed'] ?? null) === false
            && trim((string) ($operator['watchlist_function_source_artifact'] ?? '')) !== '';
    }

    private function operatorLockValidationSummary(array $load, array $operator): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C164_OPERATOR_GO_NO_GO',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'expected_status' => self::EXPECTED_C164_OPERATOR_STATUS,
            'actual_status' => $operator['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C164_OPERATOR_PHASE_LABEL,
            'actual_phase_label' => $operator['phase_label'] ?? null,
            'expected_next_recommendation' => self::RUN_CODE,
            'next_recommendation_match' => $this->operatorNextRecommendationMatches($operator),
            'c164_operator_go_no_go_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function operatorCarryForwardSummary(array $operator): array
    {
        return [
            'validation_completed' => true,
            'c164_operator_go_no_go_review_valid' => $this->operatorGoComplete($operator),
            'topic_code' => $operator['topic_code'] ?? null,
            'topic_stage' => $operator['topic_stage'] ?? null,
            'operator_decision' => $operator['operator_decision'] ?? null,
            'operator_decision_reason' => $operator['operator_decision_reason'] ?? null,
            'controlled_completion_only' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'watchlist_function_used' => (string) ($operator['watchlist_function_used'] ?? ''),
            'official_output_published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'go_decision_finalization_allowed' => (bool) ($operator['production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_allowed_next'] ?? false),
        ];
    }

    private function completionFinalizationGuardSummary(array $operator, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'post_handoff_activation_completion_finalization_valid' => $pass,
            'operator_go_decision_carried_forward' => (bool) ($operator['operator_go_decision'] ?? false),
            'controlled_completion_only' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'post_handoff_activation_completion_closed' => $pass,
            'c164_topic_complete_after_finalization' => $pass,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function watchlistFunctionScopeSummary(array $operator): array
    {
        return [
            'validation_completed' => true,
            'watchlist_function_scope_matches' => $this->watchlistFunctionScopeMatches($operator),
            'watchlist_function_used' => (string) ($operator['watchlist_function_used'] ?? ''),
            'expected_watchlist_function' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => (string) ($operator['watchlist_function_runtime_mode'] ?? ''),
            'expected_watchlist_function_runtime_mode' => self::WATCHLIST_FUNCTION_RUNTIME_MODE,
            'primary_candidate_observed' => (bool) ($operator['watchlist_function_primary_candidate_observed'] ?? false),
            'backup_candidate_observed' => (bool) ($operator['watchlist_function_backup_candidate_observed'] ?? false),
            'comparator_candidate_observed' => false,
            'finalization_uses_function_for_publication' => false,
            'finalization_uses_function_for_plan_confirm_mutation' => false,
            'finalization_uses_function_for_live_rollout' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $operator, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($operator),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
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
            'go_decision_finalization_confirmation_required' => true,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_completion_finalization_confirmation_required' => true,
            'post_handoff_activation_completion_finalization_confirmed' => (bool) ($options['post_handoff_activation_completion_finalization_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
        ];
    }

    private function goDecisionFinalizationDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'operator_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_completion_finalization_confirmed' => (bool) ($options['post_handoff_activation_completion_finalization_confirmed'] ?? false),
            'post_handoff_activation_completion_closed' => $pass,
            'c164_topic_complete_after_finalization' => $pass,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'decision_scope' => $pass ? 'C164 post-handoff activation completion GO finalized and topic closed; C165 PLAN/CONFIRM controlled rollout boundary review may lock this artifact' : 'targeted repair required before C164 GO finalization can be recorded',
        ];
    }

    private function nextBoundaryDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::NEXT_BOUNDARY_RECOMMENDATION : 'C164_TARGETED_OPERATOR_GO_NO_GO_REPAIR',
            'next_scope' => $pass ? 'C165 PLAN/CONFIRM controlled rollout boundary review only; C164 finalization does not mutate PLAN/CONFIRM, read the activated catalog, execute rollout, or authorize free publication' : 'targeted repair before C164 GO decision finalization can be recorded',
            'next_is_concrete' => $pass,
            'next_requires_locked_c164_finalization_artifact' => $pass,
            'topic_number_advances_after_c164_finalization' => $pass,
            'same_topic_c164_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function goDecisionFinalizationManifest(array $operator, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review',
            'source_artifact' => 'C164_OPERATOR_GO_NO_GO_REVIEW',
            'source_artifact_path' => self::DEFAULT_C164_OPERATOR_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C164_OPERATOR_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C164_OPERATOR_FILE_SHA1,
            'source_operator_decision' => (string) ($operator['operator_decision'] ?? 'UNSET'),
            'operator_go_decision' => $pass,
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_completion_finalization_confirmed' => (bool) ($options['post_handoff_activation_completion_finalization_confirmed'] ?? false),
            'post_handoff_activation_completion_closed' => $pass,
            'c164_topic_complete_after_finalization' => $pass,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'go_decision_finalization_artifact_only' => true,
            'ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
            'controlled_completion_only' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'go_decision_finalization_used_for_free_publication' => false,
            'go_decision_finalization_used_for_plan_confirm_mutation' => false,
            'go_decision_finalization_used_for_live_plan_confirm_rollout' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
    }

    private function goDecisionFinalizationChecklist(bool $pass, array $options): array
    {
        return [
            'go_decision_finalization_reviewed' => true,
            'c164_operator_go_no_go_source_lock_reviewed' => true,
            'operator_go_decision_carried_forward' => true,
            'go_decision_finalization_required' => true,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_completion_finalization_confirmed' => (bool) ($options['post_handoff_activation_completion_finalization_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_go_finalization_gate_required' => true,
            'negative_completion_finalization_gate_required' => true,
            'negative_plan_confirm_unchanged_gate_required' => true,
            'negative_no_live_rollout_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'go_decision_finalization_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c164_finalization' => false,
            'ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'post_handoff_activation_completion_go_decision_finalization_review_valid' => $pass,
            'operator_go_decision' => $pass,
            'go_decision_finalized' => $pass,
            'ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
            'plan_confirm_mutated' => false,
            'live_rollout_executed' => false,
            'free_published' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c164_role' => 'primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review',
                'primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c164_role' => 'backup_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review',
                'backup_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c164_role' => 'comparator_only_candidate',
                'ready_for_plan_confirm_controlled_rollout_boundary_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $operator): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($operator),
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
            'documentation_hygiene_guard_reviewed' => true,
            'c164_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c164_operator_go_no_go_artifact_not_modified' => true,
            'c164_go_decision_finalization_review_is_artifact_only_not_free_publication_or_live_rollout' => true,
            'c164_go_decision_finalization_review_closes_c164_post_handoff_activation_completion_topic' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-85_C164_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW',
            'topic_code' => 'C164_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW',
            'c164_operator_go_no_go_review_carried_forward' => true,
            'operator_go_decision' => $pass,
            'go_decision_finalized' => $pass,
            'post_handoff_activation_completion_closed' => $pass,
            'topic_complete_after_finalization' => $pass,
            'topic_number_advances_after_c164_finalization' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NEXT_BOUNDARY_RECOMMENDATION : 'C164_TARGETED_OPERATOR_GO_NO_GO_REPAIR',
            'planned_next_scope' => $pass ? 'C165 PLAN/CONFIRM controlled rollout boundary review only; C164 finalization does not mutate PLAN/CONFIRM, read the activated catalog, execute rollout, or authorize free publication' : 'targeted repair before C164 GO decision finalization can be recorded',
            'topic_number_advances_after_c164_finalization' => $pass,
            'same_topic_c164_complete' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C164 GO decision finalization artifact hash',
                'locked C164 GO decision finalization file SHA1',
                'finalized C164 post-handoff activation completion GO decision',
                'PLAN/CONFIRM unchanged',
                'live rollout still disabled',
                'free publication still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C164 finalization validates C164 operator GO/NO-GO artifact_hash and file SHA1 locks before GO finalization is recorded.',
            'C164 finalization validates operator GO, confirmation, decision reason, candidate scope, watchlist function scope, and next recommendation to C164 finalization.',
            'C164 finalization requires operator approval plus GO finalization, post-handoff activation completion finalization, PLAN/CONFIRM unchanged, no-live-rollout, and free-publication lock confirmations.',
            'C164 finalization keeps CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION controlled-only for E02 primary and B01 backup.',
            'C164 finalization closes the C164 post-handoff activation completion topic and recommends the distinct C165 PLAN/CONFIRM controlled rollout boundary review.',
            'C164 finalization does not mutate PLAN/CONFIRM, read activated catalog, execute live rollout, free-publish recommendations, or allow unrestricted publication.',
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
            'c164_operator_go_no_go' => [
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
            'expected_c164_operator_go_no_go_hash' => $load['expected_hash'],
            'actual_c164_operator_go_no_go_hash' => $load['actual_hash'],
            'c164_operator_go_no_go_hash_match' => $load['hash_match'],
            'expected_c164_operator_go_no_go_file_sha1' => $load['expected_file_sha1'],
            'actual_c164_operator_go_no_go_file_sha1' => $load['actual_file_sha1'],
            'c164_operator_go_no_go_file_sha1_match' => $load['file_sha1_match'],
            'c164_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function failureAttributionSummary(array $failures): array
    {
        $failures = array_values(array_filter($failures));

        return [
            'failure_count' => count($failures),
            'failures' => $failures,
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_OPERATOR_OR_FINALIZATION_LOCK',
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_BLOCKED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
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
