<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-80 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';

    public const DEFAULT_C163_OPERATOR_ARTIFACT = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C163_OPERATOR_HASH = '8510cda284241de5118bd15aad09c4496529958e';
    public const DEFAULT_EXPECTED_C163_OPERATOR_FILE_SHA1 = 'F09E1066506CD85D3B0675504D5E27D72FA46690';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C163_OPERATOR_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C163_OPERATOR_PHASE_LABEL = 'PR-79 / C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    private const EXPECTED_C163_OPERATOR_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C164_COMPLETION_BOUNDARY_RECOMMENDATION = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW';

    private const PASS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_HANDOFF_ACTIVATION_CLOSED_READY_FOR_COMPLETION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_FINALIZATION_NOT_CONFIRMED_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING';
    private const POST_HANDOFF_ACTIVATION_FINALIZATION_NOT_CONFIRMED_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_FINALIZATION_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C163_OPERATOR_LOCK_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C163_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH';
    private const C163_OPERATOR_FILE_SHA1_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C163_OPERATOR_GO_NO_GO_FILE_SHA1_LOCK_MISMATCH';
    private const C163_OPERATOR_CONVERT_FROM_JSON_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C163_OPERATOR_GO_NO_GO_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C163_OPERATOR_STATUS_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C163_OPERATOR_GO_NO_GO_STATUS_MISMATCH';
    private const C163_OPERATOR_PHASE_LABEL_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C163_OPERATOR_GO_NO_GO_PHASE_LABEL_MISMATCH';
    private const C163_OPERATOR_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C163_OPERATOR_GO_NO_GO_NEXT_RECOMMENDATION_MISMATCH';
    private const C163_OPERATOR_GO_INVALID_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C163_OPERATOR_GO_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C163_OPERATOR_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_pass',
        'operator_decision_recorded',
        'operator_decision_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest_created',
        'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed',
        'post_handoff_activation_observation_result_stable',
        'controlled_watchlist_function_observation_result_reviewed',
        'post_handoff_activation_observation_result_confirmed',
        'post_handoff_activation_observed',
        'controlled_watchlist_function_observed',
        'controlled_completion_lock_valid',
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
        'c163_observation_result_review_lock_valid',
        'c163_post_handoff_activation_observation_result_review_valid',
        'c163_observation_result_review_convert_from_json_pass',
        'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
        'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
        'a01_remains_comparator_only',
        'c163_is_same_post_handoff_contract',
        'c163_activation_operator_go_no_go_review_only',
        'c163_controlled_completion_only',
        'c163_not_publication',
        'c163_not_unrestricted_publication',
        'c163_not_plan_confirm_mutation',
        'c163_not_live_plan_confirm_rollout',
        'operator_approved',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C163_OPERATOR_FALSE_FIELDS = [
        'operator_no_go_decision',
        'operator_hold_decision',
        'post_handoff_activation_stopped_no_go',
        'post_handoff_activation_deferred_hold',
        'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
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
        'storage/app/watchlist/backtest/c163-*post-handoff-activation-finalization*-test.json',
        'storage/app/watchlist/backtest/c163-*post-handoff-activation-go-decision*-test.json',
        'storage/app/watchlist/backtest/c163-*go-decision-finalization*-test.json',
        'storage/app/watchlist/backtest/c163-*negative-*-test.json',
        'storage/app/watchlist/backtest/c163-*missing-*-test.json',
        'storage/app/watchlist/backtest/c163-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c163-*invalid-*-test.json',
        'storage/app/watchlist/backtest/.tmp-c163-post-handoff-activation-go-decision-finalization*.json',
    ];

    public function execute(
        string $c163OperatorArtifact = self::DEFAULT_C163_OPERATOR_ARTIFACT,
        string $expectedC163OperatorHash = self::DEFAULT_EXPECTED_C163_OPERATOR_HASH,
        string $expectedC163OperatorFileSha1 = self::DEFAULT_EXPECTED_C163_OPERATOR_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c163OperatorArtifact, $expectedC163OperatorHash, $expectedC163OperatorFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C163_OPERATOR_LOCK_MISMATCH_STATUS, 'C163 operator GO/NO-GO artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c163_operator_go_no_go_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C163_OPERATOR_CONVERT_FROM_JSON_STATUS, 'C163 operator GO/NO-GO artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C163_OPERATOR_LOCK_MISMATCH_STATUS, 'C163 operator GO/NO-GO artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C163_OPERATOR_FILE_SHA1_MISMATCH_STATUS, 'C163 operator GO/NO-GO file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $operator = $load['payload'];
        if (($operator['status'] ?? null) !== self::EXPECTED_C163_OPERATOR_STATUS || ($operator['reason_code'] ?? null) !== self::EXPECTED_C163_OPERATOR_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_OPERATOR_STATUS_MISMATCH_STATUS, 'C163 operator GO/NO-GO status/reason is not GO finalization ready.', $outputPath, $overwrite);
        }
        if (($operator['phase_label'] ?? null) !== self::EXPECTED_C163_OPERATOR_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_OPERATOR_PHASE_LABEL_MISMATCH_STATUS, 'C163 operator GO/NO-GO phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->operatorNextRecommendationMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_OPERATOR_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C163 operator GO/NO-GO next recommendation is not C163 post-handoff activation GO decision finalization.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C163 operator evidence has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->operatorGoComplete($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C163_OPERATOR_GO_INVALID_STATUS, 'C163 operator GO evidence is incomplete or not valid for finalization.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C163 operator candidate scope does not match locked finalization scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C163 GO decision finalization requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['go_decision_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::GO_FINALIZATION_NOT_CONFIRMED_STATUS, 'C163 requires --go-decision-finalization-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_handoff_activation_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::POST_HANDOFF_ACTIVATION_FINALIZATION_NOT_CONFIRMED_STATUS, 'C163 requires --post-handoff-activation-finalization-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C163 finalizes the operator GO decision for PLAN/CONFIRM completion post-handoff activation. The C163 post-handoff activation topic is closed; PLAN/CONFIRM mutation, live rollout, unrestricted publication, and free publication remain locked.';
        $artifact['diagnostic_conclusion'] = 'C163_POST_HANDOFF_ACTIVATION_GO_FINALIZED_TOPIC_CLOSED_READY_FOR_C164_COMPLETION_BOUNDARY_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C164_COMPLETION_BOUNDARY_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-80',
            'internal_checkpoint' => 'C163',
            'topic_code' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW',
            'status' => 'C163_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'reason_code' => 'C163_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass' => false,
            'operator_decision' => 'NO_GO',
            'operator_go_decision' => false,
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'go_decision_finalization_confirmed' => false,
            'post_handoff_activation_finalization_confirmed' => false,
            'post_handoff_activation_closed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => false,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed' => false,
            'post_handoff_activation_observation_result_stable' => false,
            'controlled_watchlist_function_observation_result_reviewed' => false,
            'post_handoff_activation_observation_result_confirmed' => false,
            'post_handoff_activation_observed' => false,
            'controlled_watchlist_function_observed' => false,
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
            'c163_operator_go_no_go_lock_valid' => false,
            'c163_operator_go_no_go_review_valid' => false,
            'c163_operator_go_no_go_convert_from_json_pass' => false,
            'c163_observation_result_review_lock_valid' => false,
            'c163_post_handoff_activation_observation_result_review_valid' => false,
            'c163_observation_result_review_convert_from_json_pass' => false,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c163_is_same_post_handoff_contract' => true,
            'c163_activation_go_decision_finalization_review_only' => true,
            'c163_controlled_completion_only' => true,
            'c163_not_publication' => true,
            'c163_not_unrestricted_publication' => true,
            'c163_not_plan_confirm_mutation' => true,
            'c163_not_live_plan_confirm_rollout' => true,
            'c163_topic_complete_after_finalization' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C163_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'next_step_recommendation' => 'C163_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'message' => '',
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $operator = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($operator, $load, $pass, $options));
        $artifact['c163_operator_go_no_go_lock_validation_summary'] = $this->operatorLockValidationSummary($load, $operator);
        $artifact['c163_operator_go_no_go_carry_forward_summary'] = $this->operatorCarryForwardSummary($operator);
        $artifact['post_handoff_activation_finalization_guard_summary'] = $this->postHandoffActivationFinalizationGuardSummary($operator, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($operator, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c163_go_decision_finalization_decision'] = $this->goDecisionFinalizationDecision($pass, $options);
        $artifact['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision'] = $this->nextPostHandoffActivationCompletionBoundaryDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest'] = $this->goDecisionFinalizationManifest($operator, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_checklist'] = $this->goDecisionFinalizationChecklist($pass, $options);
        $artifact['c163_candidate_post_handoff_activation_go_decision_finalization_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($operator);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');

        return $artifact;
    }

    private function topLevelState(array $operator, array $load, bool $pass, array $options): array
    {
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        return [
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed'] ?? false),
            'post_handoff_activation_observation_result_stable' => (bool) ($operator['post_handoff_activation_observation_result_stable'] ?? false),
            'controlled_watchlist_function_observation_result_reviewed' => (bool) ($operator['controlled_watchlist_function_observation_result_reviewed'] ?? false),
            'post_handoff_activation_observation_result_confirmed' => (bool) ($operator['post_handoff_activation_observation_result_confirmed'] ?? false),
            'post_handoff_activation_observed' => (bool) ($operator['post_handoff_activation_observed'] ?? false),
            'controlled_watchlist_function_observed' => (bool) ($operator['controlled_watchlist_function_observed'] ?? false),
            'controlled_completion_lock_valid' => (bool) ($operator['controlled_completion_lock_valid'] ?? false),
            'controlled_completion_path' => $operator['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $operator['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $operator['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($operator['controlled_completion_record_count'] ?? 0),
            'watchlist_function_used' => (string) ($operator['watchlist_function_used'] ?? ''),
            'watchlist_function_runtime_mode' => (string) ($operator['watchlist_function_runtime_mode'] ?? 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT'),
            'watchlist_function_source_artifact' => (string) ($operator['watchlist_function_source_artifact'] ?? ''),
            'watchlist_function_primary_candidate_observed' => (bool) ($operator['watchlist_function_primary_candidate_observed'] ?? false),
            'watchlist_function_backup_candidate_observed' => (bool) ($operator['watchlist_function_backup_candidate_observed'] ?? false),
            'watchlist_function_comparator_candidate_observed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
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
            'c163_operator_go_no_go_lock_valid' => (bool) (($load['hash_match'] ?? false) && ($load['file_sha1_match'] ?? false) && ($load['convert_from_json_pass'] ?? false)),
            'c163_operator_go_no_go_review_valid' => $this->operatorGoComplete($operator),
            'c163_operator_go_no_go_convert_from_json_pass' => (bool) ($load['convert_from_json_pass'] ?? false),
            'c163_observation_result_review_lock_valid' => (bool) ($operator['c163_observation_result_review_lock_valid'] ?? false),
            'c163_post_handoff_activation_observation_result_review_valid' => (bool) ($operator['c163_post_handoff_activation_observation_result_review_valid'] ?? false),
            'c163_observation_result_review_convert_from_json_pass' => (bool) ($operator['c163_observation_result_review_convert_from_json_pass'] ?? false),
            'operator_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision' => $pass,
            'operator_go_decision_confirmed' => $pass,
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_finalization_confirmed' => (bool) ($options['post_handoff_activation_finalization_confirmed'] ?? false),
            'post_handoff_activation_closed' => $pass,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => false,
            'temporary_negative_artifacts_remaining' => $temporaryNegativePaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryNegativePaths === [],
            'temporary_negative_artifact_paths' => array_values($temporaryNegativePaths),
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass' => true,
            'operator_decision' => 'GO',
            'operator_go_decision' => true,
            'operator_go_decision_confirmed' => true,
            'go_decision_finalized' => true,
            'go_decision_finalization_confirmed' => true,
            'post_handoff_activation_finalization_confirmed' => true,
            'post_handoff_activation_closed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => true,
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created' => true,
            'c163_operator_go_no_go_lock_valid' => true,
            'c163_operator_go_no_go_review_valid' => true,
            'c163_operator_go_no_go_convert_from_json_pass' => true,
            'c163_observation_result_review_lock_valid' => true,
            'c163_post_handoff_activation_observation_result_review_valid' => true,
            'c163_observation_result_review_convert_from_json_pass' => true,
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review' => false,
            'c163_topic_complete_after_finalization' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function operatorNextRecommendationMatches(array $operator): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_completion_post_handoff_activation_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($operator, $path) !== self::EXPECTED_C163_OPERATOR_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function operatorGoComplete(array $operator): bool
    {
        foreach (self::REQUIRED_C163_OPERATOR_TRUE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C163_OPERATOR_FALSE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($operator['operator_decision'] ?? null) === 'GO'
            && ($operator['operator_go_decision'] ?? null) === true
            && trim((string) ($operator['operator_decision_reason'] ?? '')) !== ''
            && $this->valueAt($operator, ['c163_operator_go_no_go_decision', 'operator_decision']) === 'GO'
            && $this->valueAt($operator, ['c163_operator_go_no_go_decision', 'ready_for_go_decision_finalization_review']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest', 'operator_decision']) === 'GO'
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest', 'operator_go_no_go_used_for_publication']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest', 'operator_go_no_go_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest', 'operator_go_no_go_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_checklist', 'artifact_only']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_checklist', 'weekly_swing_stock_recommendation_free_published_in_c163_operator_review']) === false;
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
            && ($operator['primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review'] ?? null) === true
            && ($operator['backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review'] ?? null) === true
            && ($operator['comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review'] ?? null) === false
            && ($operator['a01_remains_comparator_only'] ?? null) === true
            && ($operator['a01_promoted'] ?? false) === false
            && ($operator['candidate_promotion_executed'] ?? false) === false
            && ($operator['candidate_rerank_executed'] ?? false) === false;
    }

    private function operatorLockValidationSummary(array $load, array $operator): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C163_OPERATOR_GO_NO_GO',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C163_OPERATOR_STATUS,
            'actual_status' => $operator['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C163_OPERATOR_PHASE_LABEL,
            'actual_phase_label' => $operator['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C163_OPERATOR_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->operatorNextRecommendationMatches($operator),
            'c163_operator_go_no_go_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function operatorCarryForwardSummary(array $operator): array
    {
        return [
            'validation_completed' => true,
            'c163_operator_go_no_go_review_valid' => $this->operatorGoComplete($operator),
            'topic_code' => $operator['topic_code'] ?? null,
            'topic_stage' => $operator['topic_stage'] ?? null,
            'operator_decision' => $operator['operator_decision'] ?? null,
            'operator_decision_reason' => $operator['operator_decision_reason'] ?? null,
            'post_handoff_activation_observation_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed'] ?? false),
            'controlled_completion_only' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'official_output_published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'go_decision_finalization_allowed' => (bool) ($operator['production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next'] ?? false),
        ];
    }

    private function postHandoffActivationFinalizationGuardSummary(array $operator, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'post_handoff_activation_finalization_valid' => $pass,
            'post_handoff_activation_observation_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed'] ?? false),
            'controlled_completion_only' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'post_handoff_activation_closed' => $pass,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
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
            'primary_candidate_ready_for_post_handoff_activation_completion_boundary_review' => $pass,
            'backup_candidate_ready_for_post_handoff_activation_completion_boundary_review' => $pass,
            'comparator_candidate_ready_for_post_handoff_activation_completion_boundary_review' => false,
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
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
            'go_decision_finalization_confirmation_required' => true,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_finalization_confirmation_required' => true,
            'post_handoff_activation_finalization_confirmed' => (bool) ($options['post_handoff_activation_finalization_confirmed'] ?? false),
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
            'post_handoff_activation_finalization_confirmed' => (bool) ($options['post_handoff_activation_finalization_confirmed'] ?? false),
            'post_handoff_activation_closed' => $pass,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'decision_scope' => $pass ? 'C163 post-handoff activation GO finalized and topic closed; completion boundary review may start next' : 'targeted repair required before C163 GO finalization can be recorded',
        ];
    }

    private function nextPostHandoffActivationCompletionBoundaryDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C164_COMPLETION_BOUNDARY_RECOMMENDATION : 'C163_TARGETED_OPERATOR_GO_NO_GO_REPAIR',
            'next_scope' => $pass ? 'C164 post-handoff activation completion boundary review only; no PLAN/CONFIRM mutation or live rollout is authorized by C163 finalization' : 'targeted repair before C163 post-handoff activation GO decision finalization can be recorded',
            'next_is_concrete' => $pass,
            'next_requires_locked_c163_finalization_artifact' => $pass,
            'topic_number_advances_after_c163_finalization' => $pass,
            'same_topic_c163_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function goDecisionFinalizationManifest(array $operator, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
            'source_artifact' => 'C163_OPERATOR_GO_NO_GO_REVIEW',
            'source_artifact_path' => self::DEFAULT_C163_OPERATOR_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C163_OPERATOR_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C163_OPERATOR_FILE_SHA1,
            'source_operator_decision' => (string) ($operator['operator_decision'] ?? 'UNSET'),
            'operator_go_decision' => $pass,
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_finalization_confirmed' => (bool) ($options['post_handoff_activation_finalization_confirmed'] ?? false),
            'post_handoff_activation_closed' => $pass,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'post_handoff_activation_go_decision_finalization_review_pass' => $pass,
            'ready_for_post_handoff_activation_completion_boundary_review' => $pass,
            'go_decision_finalization_artifact_only' => true,
            'controlled_completion_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'controlled_completion_only' => (bool) ($operator['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'go_decision_finalization_used_for_free_publication' => false,
            'go_decision_finalization_used_for_plan_confirm_mutation' => false,
            'go_decision_finalization_used_for_live_plan_confirm_rollout' => false,
        ];
    }

    private function goDecisionFinalizationChecklist(bool $pass, array $options): array
    {
        return [
            'go_decision_finalization_reviewed' => true,
            'c163_operator_go_no_go_source_lock_reviewed' => true,
            'operator_go_decision_carried_forward' => true,
            'go_decision_finalization_required' => true,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_handoff_activation_finalization_confirmed' => (bool) ($options['post_handoff_activation_finalization_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_go_finalization_gate_required' => true,
            'negative_post_handoff_activation_finalization_gate_required' => true,
            'negative_plan_confirm_unchanged_gate_required' => true,
            'negative_no_live_rollout_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'go_decision_finalization_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c163_finalization' => false,
            'ready_for_post_handoff_activation_completion_boundary_review' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'post_handoff_activation_go_decision_finalization_review_valid' => $pass,
            'operator_go_decision' => $pass,
            'go_decision_finalized' => $pass,
            'ready_for_post_handoff_activation_completion_boundary_review' => $pass,
            'plan_confirm_mutated' => false,
            'live_rollout_executed' => false,
            'free_published' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c163_role' => 'primary_candidate_ready_for_post_handoff_activation_completion_boundary_review',
                'primary_candidate_ready_for_post_handoff_activation_completion_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c163_role' => 'backup_candidate_ready_for_post_handoff_activation_completion_boundary_review',
                'backup_candidate_ready_for_post_handoff_activation_completion_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c163_role' => 'comparator_only_candidate',
                'ready_for_post_handoff_activation_completion_boundary_review' => false,
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
            'c163_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c163_operator_go_no_go_artifact_not_modified' => true,
            'c163_go_decision_finalization_review_is_artifact_only_not_free_publication_or_live_rollout' => true,
            'c163_go_decision_finalization_review_closes_c163_post_handoff_activation_topic' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-80_C163_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW',
            'topic_code' => 'C163_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW',
            'c163_operator_go_no_go_review_carried_forward' => true,
            'operator_go_decision' => $pass,
            'go_decision_finalized' => $pass,
            'post_handoff_activation_closed' => $pass,
            'topic_complete_after_finalization' => $pass,
            'topic_number_advances_after_c163_finalization' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C164_COMPLETION_BOUNDARY_RECOMMENDATION : 'C163_TARGETED_OPERATOR_GO_NO_GO_REPAIR',
            'planned_next_scope' => $pass ? 'C164 post-handoff activation completion boundary review only; C163 finalization does not mutate PLAN/CONFIRM, execute live rollout, or authorize free publication' : 'targeted repair before C163 GO decision finalization can be recorded',
            'topic_number_advances_after_c163_finalization' => $pass,
            'same_topic_c163_complete' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C163 GO decision finalization artifact hash',
                'locked C163 GO decision finalization file SHA1',
                'finalized C163 post-handoff activation GO decision',
                'PLAN/CONFIRM unchanged',
                'live rollout still disabled',
                'free publication still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C163 finalization validates C163 operator GO/NO-GO artifact_hash and file SHA1 locks before GO finalization is recorded.',
            'C163 finalization validates operator GO, confirmation, decision reason, candidate scope, and next recommendation to C163 finalization.',
            'C163 finalization requires operator approval plus GO finalization, post-handoff activation finalization, PLAN/CONFIRM unchanged, no-live-rollout, and free-publication lock confirmations.',
            'C163 finalization keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C163 finalization closes the post-handoff activation topic and recommends C164 post-handoff activation completion boundary review.',
            'C163 finalization does not mutate PLAN/CONFIRM, read activated catalog, execute live rollout, free-publish recommendations, or allow unrestricted publication.',
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
            'c163_operator_go_no_go' => [
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
            'expected_c163_operator_go_no_go_hash' => $load['expected_hash'],
            'actual_c163_operator_go_no_go_hash' => $load['actual_hash'],
            'c163_operator_go_no_go_hash_match' => $load['hash_match'],
            'expected_c163_operator_go_no_go_file_sha1' => $load['expected_file_sha1'],
            'actual_c163_operator_go_no_go_file_sha1' => $load['actual_file_sha1'],
            'c163_operator_go_no_go_file_sha1_match' => $load['file_sha1_match'],
            'c163_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
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
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_OPERATOR_OR_FINALIZATION_LOCK',
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C163_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_BLOCKED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C163_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false);
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
