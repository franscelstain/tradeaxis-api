<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-93 / C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_RESULT_REVIEW_ARTIFACT = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review.json';
    public const DEFAULT_EXPECTED_RESULT_REVIEW_HASH = '1dbd61b08afb2d45918cc66a16c782983cfd6666';
    public const DEFAULT_EXPECTED_RESULT_REVIEW_FILE_SHA1 = '2555E1C7612C066FBF60342D0235AE399CB23253';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';
    private const OBSERVATION_BASIS = 'LOCKED_CONTROL_PLANE_RUNTIME_STATE_SNAPSHOT';

    private const EXPECTED_RESULT_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_RESULT_PHASE = 'PR-92 / C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW';
    private const EXPECTED_RESULT_RUN = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW';
    private const NEXT_FINALIZATION = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';

    private const GO_STATUS = self::RUN_CODE.'_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = self::RUN_CODE.'_COMPLETED_NO_GO_POST_ROLLOUT_OBSERVATION_PROGRESSION_STOPPED';
    private const HOLD_STATUS = self::RUN_CODE.'_COMPLETED_HOLD_POST_ROLLOUT_OBSERVATION_PROGRESSION_DEFERRED';
    private const RESULT_LOCK_STATUS = self::RUN_CODE.'_REJECTED_C166_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH';
    private const RESULT_SHA_STATUS = self::RUN_CODE.'_REJECTED_C166_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH';
    private const RESULT_JSON_STATUS = self::RUN_CODE.'_REJECTED_C166_RESULT_REVIEW_JSON_COMPATIBILITY_VIOLATION';
    private const RESULT_STATE_STATUS = self::RUN_CODE.'_REJECTED_C166_RESULT_REVIEW_INCOMPLETE';
    private const APPROVAL_STATUS = self::RUN_CODE.'_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_STATUS = self::RUN_CODE.'_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_CONFIRMATION_STATUS = self::RUN_CODE.'_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_STATUS = self::RUN_CODE.'_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMPORARY_STATUS = self::RUN_CODE.'_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const CONFIRMATION_STATUSES = [
        'result_review_locked_confirmed' => 'RESULT_REVIEW_LOCK_CONFIRMATION_MISSING',
        'post_rollout_observation_result_confirmed' => 'POST_ROLLOUT_OBSERVATION_RESULT_CONFIRMATION_MISSING',
        'control_plane_result_confirmed' => 'CONTROL_PLANE_RESULT_CONFIRMATION_MISSING',
        'market_metrics_not_inferred_confirmed' => 'MARKET_METRICS_NOT_INFERRED_CONFIRMATION_MISSING',
        'candidate_scope_confirmed' => 'CANDIDATE_SCOPE_CONFIRMATION_MISSING',
        'kill_switch_confirmed' => 'KILL_SWITCH_CONFIRMATION_MISSING',
        'rollback_confirmed' => 'ROLLBACK_CONFIRMATION_MISSING',
        'production_config_unchanged_confirmed' => 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING',
        'free_publication_locked_confirmed' => 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING',
    ];

    private const REQUIRED_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_pass',
        'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_pass',
        'post_rollout_observation_result_reviewed',
        'post_rollout_observation_result_valid',
        'control_plane_observation_result_stable',
        'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review',
        'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_allowed_next',
        'operator_go_no_go_review_required_next',
        'c166_topic_number_retained_for_operator_go_no_go_review',
        'c166_observation_lock_valid',
        'c166_observation_result_valid',
        'all_required_source_locks_valid',
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
        'primary_candidate_observation_result_reviewed',
        'backup_candidate_observation_result_reviewed',
        'primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review',
        'backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review',
        'a01_remains_comparator_only',
        'operator_approved',
        'result_review_confirmed',
        'post_rollout_observation_result_confirmed',
        'observation_artifact_locked_confirmed',
        'control_plane_snapshot_confirmed',
        'candidate_scope_confirmed',
        'result_review_kill_switch_confirmed',
        'result_review_rollback_confirmed',
        'production_config_unchanged_confirmed',
        'free_publication_locked_confirmed',
        'market_metrics_not_inferred_confirmed',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_FALSE_FIELDS = [
        'c166_topic_complete',
        'market_outcome_metrics_available',
        'price_performance_evaluated',
        'recommendation_quality_evaluated',
        'market_metrics_inferred_by_result_review',
        'new_rollout_executed',
        'new_plan_confirm_mutation_executed',
        'new_catalog_read_executed',
        'watchlist_function_invoked_by_observation_review',
        'watchlist_function_invoked_by_result_review',
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_observation_result_reviewed',
        'comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review',
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
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-operator-go-no-go*-negative-*-test.json',
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-operator-go-no-go*-missing-*-test.json',
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-operator-go-no-go*-mismatch-*-test.json',
    ];

    public function execute(
        string $resultReviewArtifact = self::DEFAULT_RESULT_REVIEW_ARTIFACT,
        string $expectedResultReviewHash = self::DEFAULT_EXPECTED_RESULT_REVIEW_HASH,
        string $expectedResultReviewFileSha1 = self::DEFAULT_EXPECTED_RESULT_REVIEW_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $decision = $this->normalizeDecision((string) ($options['operator_decision'] ?? ''));
        $reason = trim((string) ($options['decision_reason'] ?? ''));
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($resultReviewArtifact, $expectedResultReviewHash, $expectedResultReviewFileSha1);
        $artifact['source_artifact_locks'] = [$this->lockSummary($load)];
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_LOCK_STATUS, 'C166 observation result-review artifact is missing or unreadable.', $outputPath, $overwrite, false, $decision);
        }
        if (! $load['convert_from_json_pass']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_JSON_STATUS, 'C166 observation result-review artifact is not ConvertFrom-Json compatible.', $outputPath, $overwrite, false, $decision);
        }
        if (! $load['hash_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_LOCK_STATUS, 'C166 observation result-review artifact hash mismatched.', $outputPath, $overwrite, false, $decision);
        }
        if (! $load['file_sha1_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_SHA_STATUS, 'C166 observation result-review file SHA1 mismatched.', $outputPath, $overwrite, false, $decision);
        }
        if (! $this->resultReviewValid($load['payload'])) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_STATE_STATUS, 'C166 observation result-review evidence is incomplete or unsafe for an operator decision.', $outputPath, $overwrite, false, $decision);
        }
        if (($options['operator_approved'] ?? false) !== true || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::APPROVAL_STATUS, 'Explicit operator approval and approval reference are required.', $outputPath, $overwrite, false, $decision);
        }
        if ($decision === null) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, null, $reason), self::DECISION_STATUS, 'Operator decision must be GO, NO_GO, or HOLD.', $outputPath, $overwrite, false, null);
        }
        if (($options['operator_decision_confirmed'] ?? false) !== true) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::DECISION_CONFIRMATION_STATUS, 'The operator decision must be explicitly confirmed.', $outputPath, $overwrite, false, $decision);
        }
        if ($reason === '') {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::DECISION_REASON_STATUS, 'A non-empty operator decision reason is required.', $outputPath, $overwrite, false, $decision);
        }
        foreach (self::CONFIRMATION_STATUSES as $option => $suffix) {
            if (($options[$option] ?? false) !== true) {
                return $this->finish(
                    $this->completeArtifact($artifact, $load, $options, false, $decision, $reason),
                    self::RUN_CODE.'_REJECTED_'.$suffix,
                    'Required C166 operator confirmation is missing: '.$option.'.',
                    $outputPath,
                    $overwrite,
                    false,
                    $decision
                );
            }
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $artifact = $this->completeArtifact($artifact, $load, $options, false, $decision, $reason);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_paths'] = $temporaryPaths;

            return $this->finish($artifact, self::TEMPORARY_STATUS, 'Temporary C166 operator-review artifacts remain.', $outputPath, $overwrite, false, $decision);
        }

        $status = $decision === 'GO' ? self::GO_STATUS : ($decision === 'NO_GO' ? self::NO_GO_STATUS : self::HOLD_STATUS);
        $message = $decision === 'GO'
            ? 'Operator decision is GO for the locked C166 control-plane observation result; same-topic GO decision finalization may proceed.'
            : ($decision === 'NO_GO'
                ? 'Operator decision is NO_GO; C166 post-rollout observation progression is stopped.'
                : 'Operator decision is HOLD; C166 post-rollout observation progression is deferred.');

        return $this->finish($this->completeArtifact($artifact, $load, $options, true, $decision, $reason), $status, $message, $outputPath, $overwrite, true, $decision);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'artifact_type' => self::ARTIFACT_TYPE,
            'created_at' => $createdAt,
            'topic_code' => 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW',
            'c166_topic_complete' => false,
            'operator_go_no_go_review_artifact_only' => true,
            'go_decision_finalized' => false,
            'observation_basis' => self::OBSERVATION_BASIS,
            'market_outcome_metrics_available' => false,
            'price_performance_evaluated' => false,
            'recommendation_quality_evaluated' => false,
            'market_metrics_inferred_by_operator_review' => false,
            'new_rollout_executed' => false,
            'new_plan_confirm_mutation_executed' => false,
            'new_catalog_read_executed' => false,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
            'watchlist_function_invoked_by_operator_review' => false,
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

    private function completeArtifact(array $artifact, array $load, array $options, bool $completed, ?string $decision, string $reason): array
    {
        $source = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $sourceValid = $this->resultReviewValid($source);
        $go = $completed && $decision === 'GO';
        $noGo = $completed && $decision === 'NO_GO';
        $hold = $completed && $decision === 'HOLD';
        $temporaryPaths = $this->temporaryNegativeArtifactPaths();

        return array_merge($artifact, [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_executed' => $completed,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_allowed' => $completed,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_pass' => $go,
            'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_pass' => $go,
            'operator_go_no_go_review_completed' => $completed,
            'operator_decision_recorded' => $completed,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_go_decision' => $go,
            'operator_no_go_decision' => $noGo,
            'operator_hold_decision' => $hold,
            'operator_decision_confirmed' => ($options['operator_decision_confirmed'] ?? false) === true,
            'operator_decision_reason' => $reason,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review' => $go,
            'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_allowed_next' => $go,
            'controlled_rollout_post_rollout_observation_stopped_no_go' => $noGo,
            'controlled_rollout_post_rollout_observation_deferred_hold' => $hold,
            'c166_topic_number_retained_for_go_decision_finalization' => true,
            'c166_result_review_lock_valid' => $this->loadValid($load),
            'c166_post_rollout_observation_result_review_valid' => $sourceValid,
            'post_rollout_observation_result_valid' => ($source['post_rollout_observation_result_valid'] ?? false) === true,
            'control_plane_observation_result_stable' => ($source['control_plane_observation_result_stable'] ?? false) === true,
            'controlled_rollout_executed' => ($source['controlled_rollout_executed'] ?? false) === true,
            'controlled_rollout_active' => ($source['controlled_rollout_active'] ?? false) === true,
            'controlled_rollout_only' => ($source['controlled_rollout_only'] ?? false) === true,
            'plan_confirm_mutated' => ($source['plan_confirm_mutated'] ?? false) === true,
            'plan_confirm_runtime_reads_activated_catalog' => ($source['plan_confirm_runtime_reads_activated_catalog'] ?? false) === true,
            'live_plan_confirm_rollout_executed' => ($source['live_plan_confirm_rollout_executed'] ?? false) === true,
            'unrestricted_rollout_allowed' => ($source['unrestricted_rollout_allowed'] ?? true) === true,
            'kill_switch_confirmed' => ($source['kill_switch_confirmed'] ?? false) === true,
            'rollback_confirmed' => ($source['rollback_confirmed'] ?? false) === true,
            'rollout_state_record_count' => (int) ($source['rollout_state_record_count'] ?? 0),
            'watchlist_function_invoked_during_execution' => ($source['watchlist_function_invoked_during_execution'] ?? false) === true,
            'watchlist_function_invoked_by_observation_review' => ($source['watchlist_function_invoked_by_observation_review'] ?? true) === true,
            'watchlist_function_invoked_by_result_review' => ($source['watchlist_function_invoked_by_result_review'] ?? true) === true,
            'watchlist_function_primary_candidate_observed' => ($source['watchlist_function_primary_candidate_observed'] ?? false) === true,
            'watchlist_function_backup_candidate_observed' => ($source['watchlist_function_backup_candidate_observed'] ?? false) === true,
            'watchlist_function_comparator_candidate_observed' => ($source['watchlist_function_comparator_candidate_observed'] ?? true) === true,
            'primary_candidate_operator_decision_recorded' => $completed,
            'backup_candidate_operator_decision_recorded' => $completed,
            'comparator_candidate_operator_decision_recorded' => false,
            'primary_candidate_ready_for_go_decision_finalization' => $go,
            'backup_candidate_ready_for_go_decision_finalization' => $go,
            'comparator_candidate_ready_for_go_decision_finalization' => false,
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'result_review_locked_confirmed' => ($options['result_review_locked_confirmed'] ?? false) === true,
            'post_rollout_observation_result_confirmed' => ($options['post_rollout_observation_result_confirmed'] ?? false) === true,
            'control_plane_result_confirmed' => ($options['control_plane_result_confirmed'] ?? false) === true,
            'market_metrics_not_inferred_confirmed' => ($options['market_metrics_not_inferred_confirmed'] ?? false) === true,
            'candidate_scope_confirmed' => ($options['candidate_scope_confirmed'] ?? false) === true,
            'operator_kill_switch_confirmed' => ($options['kill_switch_confirmed'] ?? false) === true,
            'operator_rollback_confirmed' => ($options['rollback_confirmed'] ?? false) === true,
            'production_config_unchanged_confirmed' => ($options['production_config_unchanged_confirmed'] ?? false) === true,
            'free_publication_locked_confirmed' => ($options['free_publication_locked_confirmed'] ?? false) === true,
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'c166_result_review_lock_validation_summary' => [
                'result_review_lock_valid' => $this->loadValid($load),
                'result_review_state_valid' => $sourceValid,
                'all_required_source_locks_valid' => $this->loadValid($load),
            ],
            'c166_post_rollout_observation_result_review_carry_forward_summary' => [
                'result_review_valid' => $sourceValid,
                'source_status' => $source['status'] ?? null,
                'source_next_recommendation' => $source['next_step_recommendation'] ?? null,
                'post_rollout_observation_result_valid' => ($source['post_rollout_observation_result_valid'] ?? false) === true,
                'control_plane_observation_result_stable' => ($source['control_plane_observation_result_stable'] ?? false) === true,
                'observation_basis' => $source['observation_basis'] ?? null,
            ],
            'observation_metric_operator_decision_contract' => [
                'control_plane_result_used_for_operator_decision' => $completed,
                'market_outcome_metrics_available' => false,
                'price_performance_evaluated' => false,
                'recommendation_quality_evaluated' => false,
                'market_metrics_inferred_by_operator_review' => false,
                'operator_confirmed_market_metrics_not_inferred' => ($options['market_metrics_not_inferred_confirmed'] ?? false) === true,
                'operator_decision_must_not_claim_unavailable_market_performance' => true,
            ],
            'watchlist_function_operator_review_summary' => [
                'watchlist_function_scope_valid' => $this->functionScopeValid($source),
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
                'invoked_by_observation_review' => false,
                'invoked_by_result_review' => false,
                'invoked_by_operator_review' => false,
                'primary_candidate_decision_recorded' => $completed,
                'backup_candidate_decision_recorded' => $completed,
                'comparator_candidate_decision_recorded' => false,
            ],
            'candidate_scope_freeze_summary' => [
                'candidate_scope_valid' => $this->candidateScopeValid($source),
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'a01_remains_comparator_only' => true,
                'candidate_rerank_executed' => false,
                'strategy_retune_executed' => false,
            ],
            'publication_and_rollout_safety_summary' => [
                'operator_review_artifact_only' => true,
                'new_rollout_executed' => false,
                'new_plan_confirm_mutation_executed' => false,
                'new_catalog_read_executed' => false,
                'production_config_mutated' => false,
                'unrestricted_rollout_allowed' => false,
                'free_publication_allowed' => false,
                'official_output_published' => false,
                'kill_switch_confirmed' => ($source['kill_switch_confirmed'] ?? false) === true,
                'rollback_confirmed' => ($source['rollback_confirmed'] ?? false) === true,
            ],
            'operator_decision_validation_summary' => [
                'operator_approved' => ($options['operator_approved'] ?? false) === true,
                'approval_reference' => (string) ($options['approval_reference'] ?? ''),
                'operator_decision' => $decision ?? 'INVALID',
                'operator_decision_confirmed' => ($options['operator_decision_confirmed'] ?? false) === true,
                'operator_decision_reason' => $reason,
                'decision_completed' => $completed,
                'decision_is_go' => $go,
                'decision_is_no_go' => $noGo,
                'decision_is_hold' => $hold,
                'required_confirmation_summary' => $this->confirmationSummary($options),
            ],
            'temporary_negative_artifact_guard_summary' => [
                'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
                'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
                'temporary_negative_artifact_paths' => $temporaryPaths,
                'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
            ],
            'c166_post_rollout_observation_operator_go_no_go_decision' => [
                'decision_recorded' => $completed,
                'decision' => $decision ?? 'INVALID',
                'decision_reason' => $reason,
                'go_selected' => $go,
                'no_go_selected' => $noGo,
                'hold_selected' => $hold,
                'free_publication_authorized' => false,
                'market_performance_claim_authorized' => false,
            ],
            'next_post_rollout_observation_go_decision_finalization_decision' => [
                'operator_go_valid' => $go,
                'next_recommendation' => $go ? self::NEXT_FINALIZATION : ($noGo ? 'C166_POST_ROLLOUT_OBSERVATION_PROGRESSION_STOPPED_NO_GO' : ($hold ? 'C166_POST_ROLLOUT_OBSERVATION_PROGRESSION_DEFERRED_HOLD' : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_OPERATOR_REVIEW_REPAIR')),
                'same_topic_c166_continues' => true,
                'go_decision_finalization_required_next' => $go,
                'go_decision_finalization_requires_locked_operator_artifact' => $go,
                'go_decision_finalized' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_manifest' => [
                'manifest_created' => $completed,
                'operator_review_artifact_only' => true,
                'source_result_review_path' => $load['path'],
                'source_result_review_hash' => $load['actual_hash'],
                'operator_decision' => $decision ?? 'INVALID',
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
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_checklist' => [
                'result_review_lock_reviewed' => true,
                'control_plane_observation_result_reviewed' => true,
                'candidate_scope_reviewed' => true,
                'watchlist_function_scope_reviewed' => true,
                'market_metric_contract_reviewed' => true,
                'kill_switch_reviewed' => true,
                'rollback_reviewed' => true,
                'production_config_guard_reviewed' => true,
                'free_publication_guard_reviewed' => true,
                'go_decision_finalization_required_next' => $go,
            ],
            'c166_candidate_post_rollout_observation_operator_go_no_go_scorecard' => $this->candidateScorecard($completed, $go, $decision),
            'progress_summary' => [
                'progress_marker' => 'PR-93_C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW',
                'topic_code' => 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION',
                'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW',
                'c166_topic_complete' => false,
                'operator_review_completed' => $completed,
                'operator_decision' => $decision ?? 'INVALID',
                'go_decision_finalization_required_next' => $go,
            ],
            'planned_next_summary' => [
                'planned_next_review' => $go ? self::NEXT_FINALIZATION : ($noGo ? 'C166_POST_ROLLOUT_OBSERVATION_PROGRESSION_STOPPED_NO_GO' : ($hold ? 'C166_POST_ROLLOUT_OBSERVATION_PROGRESSION_DEFERRED_HOLD' : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_OPERATOR_REVIEW_REPAIR')),
                'planned_next_scope' => $go ? 'same-topic C166 GO decision finalization over the locked operator artifact' : ($noGo ? 'stop C166 progression' : ($hold ? 'defer C166 progression' : 'repair failed C166 operator review')),
                'same_topic_c166_continues' => true,
            ],
            'diagnostics' => [
                'C166 operator review locks the control-plane result-review artifact and executes no runtime action.',
                'GO, NO_GO, and HOLD are explicit completed decisions with distinct progression behavior.',
                'No decision infers unavailable market performance or authorizes free publication.',
                'Only GO may proceed inside C166 to GO decision finalization; GO is not finalization itself.',
            ],
        ]);
    }

    private function resultReviewValid(array $source): bool
    {
        foreach (self::REQUIRED_TRUE_FIELDS as $field) {
            if (($source[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_FALSE_FIELDS as $field) {
            if (($source[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($source['run_code'] ?? null) === self::EXPECTED_RESULT_RUN
            && ($source['phase_label'] ?? null) === self::EXPECTED_RESULT_PHASE
            && ($source['status'] ?? null) === self::EXPECTED_RESULT_STATUS
            && ($source['reason_code'] ?? null) === self::EXPECTED_RESULT_STATUS
            && ($source['topic_code'] ?? null) === 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION'
            && ($source['topic_stage'] ?? null) === 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW'
            && ($source['observation_basis'] ?? null) === self::OBSERVATION_BASIS
            && ($source['next_step_recommendation'] ?? null) === self::RUN_CODE
            && ($source['rollout_state_record_count'] ?? null) === 2
            && $this->valueAt($source, ['next_post_rollout_observation_operator_go_no_go_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($source, ['next_post_rollout_observation_operator_go_no_go_decision', 'same_topic_c166_continues']) === true
            && $this->valueAt($source, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE
            && $this->valueAt($source, ['planned_next_summary', 'same_topic_c166_continues']) === true
            && $this->metricContractValid($source)
            && $this->functionScopeValid($source)
            && $this->candidateScopeValid($source)
            && $this->safetyValid($source);
    }

    private function metricContractValid(array $source): bool
    {
        return $this->valueAt($source, ['observation_metric_result_review_contract', 'control_plane_metrics_reviewed']) === true
            && $this->valueAt($source, ['observation_metric_result_review_contract', 'market_outcome_metrics_available']) === false
            && $this->valueAt($source, ['observation_metric_result_review_contract', 'price_performance_evaluated']) === false
            && $this->valueAt($source, ['observation_metric_result_review_contract', 'recommendation_quality_evaluated']) === false
            && $this->valueAt($source, ['observation_metric_result_review_contract', 'market_metrics_inferred_by_result_review']) === false
            && $this->valueAt($source, ['observation_metric_result_review_contract', 'operator_review_must_not_infer_unavailable_market_metrics']) === true;
    }

    private function functionScopeValid(array $source): bool
    {
        return ($source['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($source['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && ($source['watchlist_function_invoked_by_observation_review'] ?? null) === false
            && ($source['watchlist_function_invoked_by_result_review'] ?? null) === false
            && $this->valueAt($source, ['watchlist_function_observation_result_summary', 'watchlist_function_scope_valid']) === true
            && $this->valueAt($source, ['watchlist_function_observation_result_summary', 'watchlist_function_used']) === self::WATCHLIST_FUNCTION
            && $this->valueAt($source, ['watchlist_function_observation_result_summary', 'watchlist_function_runtime_mode']) === self::RUNTIME_MODE
            && $this->valueAt($source, ['watchlist_function_observation_result_summary', 'invoked_by_result_review']) === false;
    }

    private function candidateScopeValid(array $source): bool
    {
        $scorecard = $source['c166_candidate_post_rollout_observation_result_scorecard'] ?? null;
        if (! is_array($scorecard) || count($scorecard) !== 3) {
            return false;
        }

        return ($source['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($source['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($source['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($source['a01_remains_comparator_only'] ?? null) === true
            && $this->valueAt($source, ['candidate_scope_observation_result_summary', 'candidate_scope_valid']) === true
            && $this->valueAt($source, ['candidate_scope_observation_result_summary', 'candidate_rerank_executed']) === false
            && $this->valueAt($source, ['candidate_scope_observation_result_summary', 'strategy_retune_executed']) === false
            && $this->scorecardRowValid($scorecard[0] ?? null, self::PRIMARY_CANDIDATE, 'primary_controlled_rollout', true)
            && $this->scorecardRowValid($scorecard[1] ?? null, self::BACKUP_CANDIDATE, 'backup_controlled_rollout', true)
            && $this->scorecardRowValid($scorecard[2] ?? null, self::COMPARATOR_CANDIDATE, 'comparator_only', false);
    }

    private function scorecardRowValid($row, string $candidate, string $role, bool $ready): bool
    {
        return is_array($row)
            && ($row['candidate_code'] ?? null) === $candidate
            && ($row['role'] ?? null) === $role
            && ($row['observation_result_reviewed'] ?? null) === $ready
            && ($row['ready_for_operator_go_no_go_review'] ?? null) === $ready;
    }

    private function safetyValid(array $source): bool
    {
        return $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'result_review_artifact_only']) === true
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'new_rollout_executed']) === false
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'new_plan_confirm_mutation_executed']) === false
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'new_catalog_read_executed']) === false
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'production_config_mutated']) === false
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'unrestricted_rollout_allowed']) === false
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'free_publication_allowed']) === false
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'official_output_published']) === false
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'kill_switch_confirmed']) === true
            && $this->valueAt($source, ['publication_and_rollout_result_review_safety_summary', 'rollback_confirmed']) === true;
    }

    private function confirmationSummary(array $options): array
    {
        $summary = [];
        foreach (self::CONFIRMATION_STATUSES as $option => $unused) {
            $summary[$option] = ($options[$option] ?? false) === true;
        }

        return $summary;
    }

    private function candidateScorecard(bool $completed, bool $go, ?string $decision): array
    {
        return [
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'role' => 'primary_controlled_rollout', 'operator_decision_recorded' => $completed, 'operator_decision' => $decision ?? 'INVALID', 'ready_for_go_decision_finalization' => $go],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'role' => 'backup_controlled_rollout', 'operator_decision_recorded' => $completed, 'operator_decision' => $decision ?? 'INVALID', 'ready_for_go_decision_finalization' => $go],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'role' => 'comparator_only', 'operator_decision_recorded' => false, 'operator_decision' => 'COMPARATOR_ONLY', 'ready_for_go_decision_finalization' => false],
        ];
    }

    private function normalizeDecision(string $decision): ?string
    {
        $normalized = strtoupper(str_replace(['-', ' '], '_', trim($decision)));

        return in_array($normalized, ['GO', 'NO_GO', 'HOLD'], true) ? $normalized : null;
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $completed, ?string $decision): array
    {
        $go = $completed && $decision === 'GO';
        $noGo = $completed && $decision === 'NO_GO';
        $hold = $completed && $decision === 'HOLD';
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $go
            ? 'C166_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_RECORDED_READY_FOR_SAME_TOPIC_FINALIZATION_FREE_PUBLICATION_LOCKED'
            : ($noGo ? 'C166_POST_ROLLOUT_OBSERVATION_OPERATOR_NO_GO_RECORDED_PROGRESSION_STOPPED' : ($hold ? 'C166_POST_ROLLOUT_OBSERVATION_OPERATOR_HOLD_RECORDED_PROGRESSION_DEFERRED' : $status));
        $artifact['next_step_recommendation'] = $go
            ? self::NEXT_FINALIZATION
            : ($noGo ? 'C166_POST_ROLLOUT_OBSERVATION_PROGRESSION_STOPPED_NO_GO' : ($hold ? 'C166_POST_ROLLOUT_OBSERVATION_PROGRESSION_DEFERRED_HOLD' : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_OPERATOR_REVIEW_REPAIR'));
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $completed ? 0 : 1,
            'failures' => $completed ? [] : [$status],
            'attribution' => $completed ? 'NONE' : 'C166_OPERATOR_REVIEW_SOURCE_DECISION_OR_CONFIRMATION',
        ];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function lockSummary(array $load): array
    {
        return [
            'source' => 'c166_post_rollout_observation_result_review',
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
            'expected_c166_result_review_hash' => $load['expected_hash'],
            'actual_c166_result_review_hash' => $load['actual_hash'],
            'c166_result_review_hash_match' => $load['hash_match'],
            'expected_c166_result_review_file_sha1' => $load['expected_file_sha1'],
            'actual_c166_result_review_file_sha1' => $load['actual_file_sha1'],
            'c166_result_review_file_sha1_match' => $load['file_sha1_match'],
            'c166_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c166_result_review_convert_from_json_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
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
