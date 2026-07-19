<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-94 / C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_OPERATOR_ARTIFACT = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_OPERATOR_HASH = '20b00b9c2c53e33eee4f1501e8fddc7c8c379dda';
    public const DEFAULT_EXPECTED_OPERATOR_FILE_SHA1 = '3158EDB0120527909C12A557C36C2EC28C91B209';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';
    private const OBSERVATION_BASIS = 'LOCKED_CONTROL_PLANE_RUNTIME_STATE_SNAPSHOT';

    private const EXPECTED_OPERATOR_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_OPERATOR_PHASE = 'PR-93 / C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';
    private const EXPECTED_OPERATOR_RUN = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';
    private const NEXT_COMPLETION_BOUNDARY = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW';

    private const PASS_STATUS = self::RUN_CODE.'_PASSED_GO_FINALIZED_POST_ROLLOUT_OBSERVATION_CLOSED_READY_FOR_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const OPERATOR_LOCK_STATUS = self::RUN_CODE.'_REJECTED_C166_OPERATOR_ARTIFACT_LOCK_MISMATCH';
    private const OPERATOR_SHA_STATUS = self::RUN_CODE.'_REJECTED_C166_OPERATOR_FILE_SHA1_LOCK_MISMATCH';
    private const OPERATOR_JSON_STATUS = self::RUN_CODE.'_REJECTED_C166_OPERATOR_JSON_COMPATIBILITY_VIOLATION';
    private const OPERATOR_STATE_STATUS = self::RUN_CODE.'_REJECTED_C166_OPERATOR_GO_INVALID';
    private const APPROVAL_STATUS = self::RUN_CODE.'_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_STATUS = self::RUN_CODE.'_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const CONFIRMATION_STATUSES = [
        'go_decision_finalization_confirmed' => 'GO_DECISION_FINALIZATION_CONFIRMATION_MISSING',
        'post_rollout_observation_topic_closure_confirmed' => 'POST_ROLLOUT_OBSERVATION_TOPIC_CLOSURE_CONFIRMATION_MISSING',
        'operator_go_locked_confirmed' => 'OPERATOR_GO_LOCK_CONFIRMATION_MISSING',
        'post_rollout_observation_result_confirmed' => 'POST_ROLLOUT_OBSERVATION_RESULT_CONFIRMATION_MISSING',
        'control_plane_result_confirmed' => 'CONTROL_PLANE_RESULT_CONFIRMATION_MISSING',
        'market_metrics_not_inferred_confirmed' => 'MARKET_METRICS_NOT_INFERRED_CONFIRMATION_MISSING',
        'candidate_scope_confirmed' => 'CANDIDATE_SCOPE_CONFIRMATION_MISSING',
        'kill_switch_confirmed' => 'KILL_SWITCH_CONFIRMATION_MISSING',
        'rollback_confirmed' => 'ROLLBACK_CONFIRMATION_MISSING',
        'production_config_unchanged_confirmed' => 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING',
        'free_publication_locked_confirmed' => 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING',
        'controlled_rollout_completion_boundary_required_confirmed' => 'CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REQUIREMENT_CONFIRMATION_MISSING',
    ];

    private const REQUIRED_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_pass',
        'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_pass',
        'operator_go_no_go_review_completed',
        'operator_decision_recorded',
        'operator_go_decision',
        'operator_decision_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review',
        'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_allowed_next',
        'c166_topic_number_retained_for_go_decision_finalization',
        'c166_result_review_lock_valid',
        'c166_post_rollout_observation_result_review_valid',
        'post_rollout_observation_result_valid',
        'control_plane_observation_result_stable',
        'controlled_rollout_executed',
        'controlled_rollout_active',
        'controlled_rollout_only',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_executed',
        'kill_switch_confirmed',
        'rollback_confirmed',
        'watchlist_function_invoked_during_execution',
        'watchlist_function_primary_candidate_observed',
        'watchlist_function_backup_candidate_observed',
        'primary_candidate_operator_decision_recorded',
        'backup_candidate_operator_decision_recorded',
        'primary_candidate_ready_for_go_decision_finalization',
        'backup_candidate_ready_for_go_decision_finalization',
        'a01_remains_comparator_only',
        'operator_approved',
        'result_review_locked_confirmed',
        'post_rollout_observation_result_confirmed',
        'control_plane_result_confirmed',
        'market_metrics_not_inferred_confirmed',
        'candidate_scope_confirmed',
        'operator_kill_switch_confirmed',
        'operator_rollback_confirmed',
        'production_config_unchanged_confirmed',
        'free_publication_locked_confirmed',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_FALSE_FIELDS = [
        'go_decision_finalized',
        'c166_topic_complete',
        'operator_no_go_decision',
        'operator_hold_decision',
        'controlled_rollout_post_rollout_observation_stopped_no_go',
        'controlled_rollout_post_rollout_observation_deferred_hold',
        'market_outcome_metrics_available',
        'price_performance_evaluated',
        'recommendation_quality_evaluated',
        'market_metrics_inferred_by_operator_review',
        'new_rollout_executed',
        'new_plan_confirm_mutation_executed',
        'new_catalog_read_executed',
        'watchlist_function_invoked_by_observation_review',
        'watchlist_function_invoked_by_result_review',
        'watchlist_function_invoked_by_operator_review',
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_operator_decision_recorded',
        'comparator_candidate_ready_for_go_decision_finalization',
        'production_config_mutated',
        'unrestricted_rollout_allowed',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'free_publication_allowed',
        'unrestricted_publication_allowed',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-go-decision-finalization*-negative-*-test.json',
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-go-decision-finalization*-missing-*-test.json',
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-go-decision-finalization*-mismatch-*-test.json',
    ];

    public function execute(
        string $operatorArtifact = self::DEFAULT_OPERATOR_ARTIFACT,
        string $expectedOperatorHash = self::DEFAULT_EXPECTED_OPERATOR_HASH,
        string $expectedOperatorFileSha1 = self::DEFAULT_EXPECTED_OPERATOR_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($operatorArtifact, $expectedOperatorHash, $expectedOperatorFileSha1);
        $artifact['source_artifact_locks'] = [$this->lockSummary($load)];
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_LOCK_STATUS, 'C166 operator GO artifact is missing or unreadable.', $outputPath, $overwrite, false);
        }
        if (! $load['convert_from_json_pass']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_JSON_STATUS, 'C166 operator GO artifact is not ConvertFrom-Json compatible.', $outputPath, $overwrite, false);
        }
        if (! $load['hash_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_LOCK_STATUS, 'C166 operator GO artifact hash mismatched.', $outputPath, $overwrite, false);
        }
        if (! $load['file_sha1_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_SHA_STATUS, 'C166 operator GO artifact file SHA1 mismatched.', $outputPath, $overwrite, false);
        }
        if (! $this->operatorGoValid($load['payload'])) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_STATE_STATUS, 'C166 operator evidence is not a complete GO decision ready for finalization.', $outputPath, $overwrite, false);
        }
        if (($options['operator_approved'] ?? false) !== true || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::APPROVAL_STATUS, 'Explicit operator approval and approval reference are required for C166 finalization.', $outputPath, $overwrite, false);
        }
        foreach (self::CONFIRMATION_STATUSES as $option => $suffix) {
            if (($options[$option] ?? false) !== true) {
                return $this->finish(
                    $this->completeArtifact($artifact, $load, $options, false),
                    self::RUN_CODE.'_REJECTED_'.$suffix,
                    'Required C166 finalization confirmation is missing: '.$option.'.',
                    $outputPath,
                    $overwrite,
                    false
                );
            }
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $artifact = $this->completeArtifact($artifact, $load, $options, false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_paths'] = $temporaryPaths;

            return $this->finish($artifact, self::TEMPORARY_STATUS, 'Temporary C166 finalization artifacts remain.', $outputPath, $overwrite, false);
        }

        return $this->finish(
            $this->completeArtifact($artifact, $load, $options, true),
            self::PASS_STATUS,
            'C166 post-rollout observation GO decision is finalized and the topic is closed; C167 controlled rollout completion boundary review may proceed for E02 primary and B01 backup.',
            $outputPath,
            $overwrite,
            true
        );
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'artifact_type' => self::ARTIFACT_TYPE,
            'created_at' => $createdAt,
            'topic_code' => 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW',
            'go_decision_finalization_artifact_only' => true,
            'observation_basis' => self::OBSERVATION_BASIS,
            'market_outcome_metrics_available' => false,
            'price_performance_evaluated' => false,
            'recommendation_quality_evaluated' => false,
            'market_metrics_inferred_by_finalization' => false,
            'new_rollout_executed' => false,
            'new_plan_confirm_mutation_executed' => false,
            'new_catalog_read_executed' => false,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
            'watchlist_function_invoked_by_finalization' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'production_config_mutated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
        ];
    }

    private function completeArtifact(array $artifact, array $load, array $options, bool $pass): array
    {
        $operator = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $operatorValid = $this->operatorGoValid($operator);
        $temporaryPaths = $this->temporaryNegativeArtifactPaths();

        return array_merge($artifact, [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_executed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_pass' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_pass' => $pass,
            'go_decision_finalized' => $pass,
            'post_rollout_observation_go_finalized' => $pass,
            'post_rollout_observation_topic_closed' => $pass,
            'c166_topic_complete' => $pass,
            'c166_topic_complete_after_finalization' => $pass,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_review' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_allowed_next' => $pass,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_review_allowed_next' => $pass,
            'c167_controlled_rollout_completion_boundary_required_next' => $pass,
            'c166_operator_artifact_lock_valid' => $this->loadValid($load),
            'c166_operator_go_valid' => $operatorValid,
            'operator_decision' => $operator['operator_decision'] ?? 'INVALID',
            'operator_go_decision' => ($operator['operator_go_decision'] ?? false) === true,
            'operator_decision_confirmed' => ($operator['operator_decision_confirmed'] ?? false) === true,
            'operator_decision_reason' => (string) ($operator['operator_decision_reason'] ?? ''),
            'post_rollout_observation_result_valid' => ($operator['post_rollout_observation_result_valid'] ?? false) === true,
            'control_plane_observation_result_stable' => ($operator['control_plane_observation_result_stable'] ?? false) === true,
            'controlled_rollout_executed' => ($operator['controlled_rollout_executed'] ?? false) === true,
            'controlled_rollout_active' => ($operator['controlled_rollout_active'] ?? false) === true,
            'controlled_rollout_only' => ($operator['controlled_rollout_only'] ?? false) === true,
            'plan_confirm_mutated' => ($operator['plan_confirm_mutated'] ?? false) === true,
            'plan_confirm_runtime_reads_activated_catalog' => ($operator['plan_confirm_runtime_reads_activated_catalog'] ?? false) === true,
            'live_plan_confirm_rollout_executed' => ($operator['live_plan_confirm_rollout_executed'] ?? false) === true,
            'unrestricted_rollout_allowed' => false,
            'kill_switch_confirmed' => ($operator['kill_switch_confirmed'] ?? false) === true,
            'rollback_confirmed' => ($operator['rollback_confirmed'] ?? false) === true,
            'rollout_state_record_count' => (int) ($operator['rollout_state_record_count'] ?? 0),
            'watchlist_function_invoked_during_execution' => ($operator['watchlist_function_invoked_during_execution'] ?? false) === true,
            'watchlist_function_invoked_by_observation_review' => false,
            'watchlist_function_invoked_by_result_review' => false,
            'watchlist_function_invoked_by_operator_review' => false,
            'watchlist_function_primary_candidate_observed' => ($operator['watchlist_function_primary_candidate_observed'] ?? false) === true,
            'watchlist_function_backup_candidate_observed' => ($operator['watchlist_function_backup_candidate_observed'] ?? false) === true,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review' => false,
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'go_decision_finalization_confirmed' => ($options['go_decision_finalization_confirmed'] ?? false) === true,
            'post_rollout_observation_topic_closure_confirmed' => ($options['post_rollout_observation_topic_closure_confirmed'] ?? false) === true,
            'operator_go_locked_confirmed' => ($options['operator_go_locked_confirmed'] ?? false) === true,
            'post_rollout_observation_result_confirmed' => ($options['post_rollout_observation_result_confirmed'] ?? false) === true,
            'control_plane_result_confirmed' => ($options['control_plane_result_confirmed'] ?? false) === true,
            'market_metrics_not_inferred_confirmed' => ($options['market_metrics_not_inferred_confirmed'] ?? false) === true,
            'candidate_scope_confirmed' => ($options['candidate_scope_confirmed'] ?? false) === true,
            'finalization_kill_switch_confirmed' => ($options['kill_switch_confirmed'] ?? false) === true,
            'finalization_rollback_confirmed' => ($options['rollback_confirmed'] ?? false) === true,
            'production_config_unchanged_confirmed' => ($options['production_config_unchanged_confirmed'] ?? false) === true,
            'free_publication_locked_confirmed' => ($options['free_publication_locked_confirmed'] ?? false) === true,
            'controlled_rollout_completion_boundary_required_confirmed' => ($options['controlled_rollout_completion_boundary_required_confirmed'] ?? false) === true,
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'c166_operator_lock_validation_summary' => [
                'operator_artifact_lock_valid' => $this->loadValid($load),
                'operator_go_valid' => $operatorValid,
                'all_required_source_locks_valid' => $this->loadValid($load),
            ],
            'c166_operator_go_carry_forward_summary' => [
                'operator_go_valid' => $operatorValid,
                'source_status' => $operator['status'] ?? null,
                'source_next_recommendation' => $operator['next_step_recommendation'] ?? null,
                'operator_decision' => $operator['operator_decision'] ?? null,
                'operator_decision_reason' => $operator['operator_decision_reason'] ?? null,
                'go_decision_previously_finalized' => ($operator['go_decision_finalized'] ?? true) === true,
            ],
            'post_rollout_observation_finalization_guard_summary' => [
                'operator_go_locked' => $this->loadValid($load) && $operatorValid,
                'post_rollout_observation_result_valid' => ($operator['post_rollout_observation_result_valid'] ?? false) === true,
                'control_plane_observation_result_stable' => ($operator['control_plane_observation_result_stable'] ?? false) === true,
                'go_decision_finalized' => $pass,
                'post_rollout_observation_topic_closed' => $pass,
                'c166_topic_complete' => $pass,
            ],
            'observation_metric_finalization_contract' => [
                'control_plane_result_finalized' => $pass,
                'market_outcome_metrics_available' => false,
                'price_performance_evaluated' => false,
                'recommendation_quality_evaluated' => false,
                'market_metrics_inferred_by_finalization' => false,
                'operator_confirmed_market_metrics_not_inferred' => ($options['market_metrics_not_inferred_confirmed'] ?? false) === true,
                'finalization_does_not_claim_unavailable_market_performance' => true,
            ],
            'watchlist_function_finalization_summary' => [
                'watchlist_function_scope_valid' => $this->functionScopeValid($operator),
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
                'invoked_by_observation_review' => false,
                'invoked_by_result_review' => false,
                'invoked_by_operator_review' => false,
                'invoked_by_finalization' => false,
            ],
            'candidate_scope_freeze_summary' => [
                'candidate_scope_valid' => $this->candidateScopeValid($operator),
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'a01_remains_comparator_only' => true,
                'candidate_rerank_executed' => false,
                'strategy_retune_executed' => false,
            ],
            'publication_and_rollout_safety_summary' => [
                'finalization_artifact_only' => true,
                'new_rollout_executed' => false,
                'new_plan_confirm_mutation_executed' => false,
                'new_catalog_read_executed' => false,
                'production_config_mutated' => false,
                'unrestricted_rollout_allowed' => false,
                'free_publication_allowed' => false,
                'official_output_published' => false,
                'kill_switch_confirmed' => ($operator['kill_switch_confirmed'] ?? false) === true,
                'rollback_confirmed' => ($operator['rollback_confirmed'] ?? false) === true,
            ],
            'operator_finalization_confirmation_summary' => $this->confirmationSummary($options),
            'temporary_negative_artifact_guard_summary' => [
                'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
                'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
                'temporary_negative_artifact_paths' => $temporaryPaths,
                'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
            ],
            'c166_go_decision_finalization_decision' => [
                'operator_go_locked' => $this->loadValid($load) && $operatorValid,
                'go_decision_finalized' => $pass,
                'post_rollout_observation_topic_closed' => $pass,
                'c166_topic_complete' => $pass,
                'free_publication_authorized' => false,
                'market_performance_claim_authorized' => false,
            ],
            'next_controlled_rollout_completion_boundary_decision' => [
                'c166_complete' => $pass,
                'next_topic_code' => 'C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION',
                'next_recommendation' => $pass ? self::NEXT_COMPLETION_BOUNDARY : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_FINALIZATION_REPAIR',
                'c167_may_start' => $pass,
                'controlled_rollout_completion_boundary_required_next' => $pass,
                'free_publication_allowed_next' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_manifest' => [
                'manifest_created' => $pass,
                'finalization_artifact_only' => true,
                'source_operator_path' => $load['path'],
                'source_operator_hash' => $load['actual_hash'],
                'operator_decision' => $operator['operator_decision'] ?? 'INVALID',
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'market_outcome_metrics_available' => false,
                'market_metrics_inferred' => false,
                'new_rollout_executed' => false,
                'official_output_published' => false,
                'free_publication_allowed' => false,
                'official_weekly_swing_stock_recommendations' => [],
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_checklist' => [
                'operator_go_lock_reviewed' => true,
                'post_rollout_observation_result_reviewed' => true,
                'control_plane_result_reviewed' => true,
                'candidate_scope_reviewed' => true,
                'watchlist_function_scope_reviewed' => true,
                'market_metric_contract_reviewed' => true,
                'kill_switch_reviewed' => true,
                'rollback_reviewed' => true,
                'production_config_guard_reviewed' => true,
                'free_publication_guard_reviewed' => true,
                'controlled_rollout_completion_boundary_required_next' => $pass,
            ],
            'c166_candidate_post_rollout_observation_go_decision_finalization_scorecard' => $this->candidateScorecard($pass),
            'progress_summary' => [
                'progress_marker' => 'PR-94_C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW',
                'topic_code' => 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION',
                'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW',
                'c166_topic_complete' => $pass,
                'go_decision_finalized' => $pass,
                'controlled_rollout_completion_boundary_required_next' => $pass,
            ],
            'planned_next_summary' => [
                'planned_next_review' => $pass ? self::NEXT_COMPLETION_BOUNDARY : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_FINALIZATION_REPAIR',
                'planned_next_scope' => $pass ? 'new C167 controlled rollout completion boundary review over the closed C166 evidence chain' : 'repair failed C166 GO decision finalization',
                'c166_topic_closed' => $pass,
                'c167_topic_may_start' => $pass,
            ],
            'diagnostics' => [
                'C166 finalization locks the operator GO artifact and closes the post-rollout observation topic.',
                'Finalization executes no function call, rollout, mutation, catalog read, metric inference, or publication.',
                'E02 remains primary, B01 remains backup, and A01 remains comparator-only.',
                'A passing finalization opens only the distinct C167 controlled rollout completion boundary review.',
            ],
        ]);
    }

    private function operatorGoValid(array $operator): bool
    {
        foreach (self::REQUIRED_TRUE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_FALSE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($operator['run_code'] ?? null) === self::EXPECTED_OPERATOR_RUN
            && ($operator['phase_label'] ?? null) === self::EXPECTED_OPERATOR_PHASE
            && ($operator['status'] ?? null) === self::EXPECTED_OPERATOR_STATUS
            && ($operator['reason_code'] ?? null) === self::EXPECTED_OPERATOR_STATUS
            && ($operator['topic_code'] ?? null) === 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION'
            && ($operator['topic_stage'] ?? null) === 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW'
            && ($operator['observation_basis'] ?? null) === self::OBSERVATION_BASIS
            && ($operator['operator_decision'] ?? null) === 'GO'
            && trim((string) ($operator['operator_decision_reason'] ?? '')) !== ''
            && ($operator['next_step_recommendation'] ?? null) === self::RUN_CODE
            && ($operator['rollout_state_record_count'] ?? null) === 2
            && $this->valueAt($operator, ['next_post_rollout_observation_go_decision_finalization_decision', 'operator_go_valid']) === true
            && $this->valueAt($operator, ['next_post_rollout_observation_go_decision_finalization_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($operator, ['next_post_rollout_observation_go_decision_finalization_decision', 'go_decision_finalization_required_next']) === true
            && $this->valueAt($operator, ['next_post_rollout_observation_go_decision_finalization_decision', 'go_decision_finalized']) === false
            && $this->valueAt($operator, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE
            && $this->valueAt($operator, ['planned_next_summary', 'same_topic_c166_continues']) === true
            && $this->metricContractValid($operator)
            && $this->functionScopeValid($operator)
            && $this->candidateScopeValid($operator)
            && $this->safetyValid($operator);
    }

    private function metricContractValid(array $operator): bool
    {
        return $this->valueAt($operator, ['observation_metric_operator_decision_contract', 'control_plane_result_used_for_operator_decision']) === true
            && $this->valueAt($operator, ['observation_metric_operator_decision_contract', 'market_outcome_metrics_available']) === false
            && $this->valueAt($operator, ['observation_metric_operator_decision_contract', 'price_performance_evaluated']) === false
            && $this->valueAt($operator, ['observation_metric_operator_decision_contract', 'recommendation_quality_evaluated']) === false
            && $this->valueAt($operator, ['observation_metric_operator_decision_contract', 'market_metrics_inferred_by_operator_review']) === false
            && $this->valueAt($operator, ['observation_metric_operator_decision_contract', 'operator_decision_must_not_claim_unavailable_market_performance']) === true;
    }

    private function functionScopeValid(array $operator): bool
    {
        return ($operator['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($operator['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && ($operator['watchlist_function_invoked_by_operator_review'] ?? null) === false
            && $this->valueAt($operator, ['watchlist_function_operator_review_summary', 'watchlist_function_scope_valid']) === true
            && $this->valueAt($operator, ['watchlist_function_operator_review_summary', 'watchlist_function_used']) === self::WATCHLIST_FUNCTION
            && $this->valueAt($operator, ['watchlist_function_operator_review_summary', 'watchlist_function_runtime_mode']) === self::RUNTIME_MODE
            && $this->valueAt($operator, ['watchlist_function_operator_review_summary', 'invoked_by_operator_review']) === false;
    }

    private function candidateScopeValid(array $operator): bool
    {
        $scorecard = $operator['c166_candidate_post_rollout_observation_operator_go_no_go_scorecard'] ?? null;
        if (! is_array($scorecard) || count($scorecard) !== 3) {
            return false;
        }

        return ($operator['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($operator['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($operator['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($operator['a01_remains_comparator_only'] ?? null) === true
            && $this->valueAt($operator, ['candidate_scope_freeze_summary', 'candidate_scope_valid']) === true
            && $this->valueAt($operator, ['candidate_scope_freeze_summary', 'candidate_rerank_executed']) === false
            && $this->valueAt($operator, ['candidate_scope_freeze_summary', 'strategy_retune_executed']) === false
            && $this->scorecardRowValid($scorecard[0] ?? null, self::PRIMARY_CANDIDATE, 'primary_controlled_rollout', true)
            && $this->scorecardRowValid($scorecard[1] ?? null, self::BACKUP_CANDIDATE, 'backup_controlled_rollout', true)
            && $this->scorecardRowValid($scorecard[2] ?? null, self::COMPARATOR_CANDIDATE, 'comparator_only', false);
    }

    private function scorecardRowValid($row, string $candidate, string $role, bool $ready): bool
    {
        return is_array($row)
            && ($row['candidate_code'] ?? null) === $candidate
            && ($row['role'] ?? null) === $role
            && ($row['operator_decision_recorded'] ?? null) === $ready
            && ($row['ready_for_go_decision_finalization'] ?? null) === $ready
            && ($row['operator_decision'] ?? null) === ($ready ? 'GO' : 'COMPARATOR_ONLY');
    }

    private function safetyValid(array $operator): bool
    {
        return $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'operator_review_artifact_only']) === true
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'new_rollout_executed']) === false
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'new_plan_confirm_mutation_executed']) === false
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'new_catalog_read_executed']) === false
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'production_config_mutated']) === false
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'unrestricted_rollout_allowed']) === false
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'free_publication_allowed']) === false
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'official_output_published']) === false
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'kill_switch_confirmed']) === true
            && $this->valueAt($operator, ['publication_and_rollout_safety_summary', 'rollback_confirmed']) === true;
    }

    private function confirmationSummary(array $options): array
    {
        $summary = [
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
        ];
        foreach (self::CONFIRMATION_STATUSES as $option => $unused) {
            $summary[$option] = ($options[$option] ?? false) === true;
        }

        return $summary;
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'role' => 'primary_controlled_rollout', 'go_decision_finalized' => $pass, 'ready_for_controlled_rollout_completion_boundary' => $pass],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'role' => 'backup_controlled_rollout', 'go_decision_finalized' => $pass, 'ready_for_controlled_rollout_completion_boundary' => $pass],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'role' => 'comparator_only', 'go_decision_finalized' => false, 'ready_for_controlled_rollout_completion_boundary' => false],
        ];
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $pass): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $pass
            ? 'C166_POST_ROLLOUT_OBSERVATION_GO_FINALIZED_TOPIC_CLOSED_READY_FOR_C167_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_FREE_PUBLICATION_LOCKED'
            : $status;
        $artifact['next_step_recommendation'] = $pass ? self::NEXT_COMPLETION_BOUNDARY : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_FINALIZATION_REPAIR';
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $pass ? 0 : 1,
            'failures' => $pass ? [] : [$status],
            'attribution' => $pass ? 'NONE' : 'C166_FINALIZATION_SOURCE_OR_CONFIRMATION',
        ];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function lockSummary(array $load): array
    {
        return [
            'source' => 'c166_post_rollout_observation_operator_go',
            'path' => $load['path'],
            'expected_hash' => $load['expected_hash'],
            'actual_hash' => $load['actual_hash'],
            'hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c166_operator_hash' => $load['expected_hash'],
            'actual_c166_operator_hash' => $load['actual_hash'],
            'c166_operator_hash_match' => $load['hash_match'],
            'expected_c166_operator_file_sha1' => $load['expected_file_sha1'],
            'actual_c166_operator_file_sha1' => $load['actual_file_sha1'],
            'c166_operator_file_sha1_match' => $load['file_sha1_match'],
            'c166_operator_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c166_operator_convert_from_json_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
        ];
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        $duplicates = [];
        $jsonError = null;
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $duplicates = $this->caseInsensitiveDuplicateTopLevelKeys($raw);
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
            'case_insensitive_duplicate_keys' => $duplicates,
            'convert_from_json_pass' => $exists && is_array($payload) && $jsonError === JSON_ERROR_NONE && $duplicates === [],
        ];
    }

    private function loadValid(array $load): bool
    {
        return $load['exists'] && $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'];
    }

    private function caseInsensitiveDuplicateTopLevelKeys(string $raw): array
    {
        $length = strlen($raw);
        $depth = 0;
        $expectKey = false;
        $seen = [];
        $duplicates = [];
        for ($i = 0; $i < $length; $i++) {
            if ($raw[$i] === '"') {
                $start = $i++;
                $escaped = false;
                while ($i < $length) {
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($raw[$i] === '\\') {
                        $escaped = true;
                    } elseif ($raw[$i] === '"') {
                        break;
                    }
                    $i++;
                }
                if ($depth === 1 && $expectKey) {
                    $token = substr($raw, $start, $i - $start + 1);
                    $j = $i + 1;
                    while ($j < $length && ctype_space($raw[$j])) {
                        $j++;
                    }
                    if ($j < $length && $raw[$j] === ':') {
                        $key = json_decode($token, true);
                        if (is_string($key)) {
                            $lower = strtolower($key);
                            if (isset($seen[$lower]) && ! in_array($key, $duplicates, true)) {
                                $duplicates[] = $key;
                            }
                            $seen[$lower] = true;
                        }
                        $expectKey = false;
                    }
                }
            } elseif ($raw[$i] === '{') {
                $depth++;
                $expectKey = $depth === 1;
            } elseif ($raw[$i] === '}') {
                $depth--;
                $expectKey = false;
            } elseif ($raw[$i] === ',' && $depth === 1) {
                $expectKey = true;
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

        return array_values(array_unique($paths));
    }

    private function writeJson(string $path, array $payload, bool $overwrite): void
    {
        if (! $overwrite && is_file($path)) {
            return;
        }
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
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
