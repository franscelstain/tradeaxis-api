<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewService
{
    public const RUN_CODE = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-92 / C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_OBSERVATION_ARTIFACT = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review.json';
    public const DEFAULT_EXPECTED_OBSERVATION_HASH = '9ffec96e1a08e927c5ad14445d6e6d038528a7f2';
    public const DEFAULT_EXPECTED_OBSERVATION_FILE_SHA1 = 'D9AF66D1488F3BA14134820647E8C1A288C75525';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';
    private const OBSERVATION_BASIS = 'LOCKED_CONTROL_PLANE_RUNTIME_STATE_SNAPSHOT';

    private const EXPECTED_OBSERVATION_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_PASSED_CONTROLLED_ROLLOUT_OBSERVED_READY_FOR_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_OBSERVATION_PHASE = 'PR-91 / C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW';
    private const EXPECTED_OBSERVATION_RUN = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW';
    private const NEXT_OPERATOR_REVIEW = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const LOCK_STATUS = self::RUN_CODE.'_REJECTED_C166_POST_ROLLOUT_OBSERVATION_ARTIFACT_LOCK_MISMATCH';
    private const SHA_STATUS = self::RUN_CODE.'_REJECTED_C166_POST_ROLLOUT_OBSERVATION_FILE_SHA1_LOCK_MISMATCH';
    private const JSON_STATUS = self::RUN_CODE.'_REJECTED_C166_POST_ROLLOUT_OBSERVATION_JSON_COMPATIBILITY_VIOLATION';
    private const STATUS_MISMATCH = self::RUN_CODE.'_REJECTED_C166_POST_ROLLOUT_OBSERVATION_STATUS_MISMATCH';
    private const PHASE_MISMATCH = self::RUN_CODE.'_REJECTED_C166_POST_ROLLOUT_OBSERVATION_PHASE_LABEL_MISMATCH';
    private const NEXT_MISMATCH = self::RUN_CODE.'_REJECTED_C166_POST_ROLLOUT_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH';
    private const OBSERVATION_INCOMPLETE = self::RUN_CODE.'_REJECTED_C166_POST_ROLLOUT_OBSERVATION_INCOMPLETE';
    private const METRIC_CONTRACT_STATUS = self::RUN_CODE.'_REJECTED_UNAVAILABLE_MARKET_METRIC_INFERENCE';
    private const FUNCTION_SCOPE_STATUS = self::RUN_CODE.'_REJECTED_WATCHLIST_FUNCTION_OBSERVATION_RESULT_MISMATCH';
    private const CANDIDATE_SCOPE_STATUS = self::RUN_CODE.'_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const SAFETY_STATUS = self::RUN_CODE.'_REJECTED_PUBLICATION_ROLLOUT_OR_CONFIG_SAFETY_VIOLATION';
    private const APPROVAL_STATUS = self::RUN_CODE.'_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_STATUS = self::RUN_CODE.'_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const CONFIRMATION_STATUSES = [
        'result_review_confirmed' => 'RESULT_REVIEW_CONFIRMATION_MISSING',
        'post_rollout_observation_result_confirmed' => 'POST_ROLLOUT_OBSERVATION_RESULT_CONFIRMATION_MISSING',
        'observation_artifact_locked_confirmed' => 'OBSERVATION_ARTIFACT_LOCK_CONFIRMATION_MISSING',
        'control_plane_snapshot_confirmed' => 'CONTROL_PLANE_SNAPSHOT_CONFIRMATION_MISSING',
        'candidate_scope_confirmed' => 'CANDIDATE_SCOPE_CONFIRMATION_MISSING',
        'kill_switch_confirmed' => 'KILL_SWITCH_CONFIRMATION_MISSING',
        'rollback_confirmed' => 'ROLLBACK_CONFIRMATION_MISSING',
        'production_config_unchanged_confirmed' => 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING',
        'free_publication_locked_confirmed' => 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING',
        'market_metrics_not_inferred_confirmed' => 'MARKET_METRICS_NOT_INFERRED_CONFIRMATION_MISSING',
    ];

    private const REQUIRED_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_pass',
        'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_pass',
        'post_rollout_observation_started',
        'post_rollout_control_plane_snapshot_captured',
        'controlled_rollout_observed',
        'controlled_rollout_observation_stable',
        'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_review',
        'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_review_allowed_next',
        'c166_topic_number_retained_for_observation_result_review',
        'c165_finalization_lock_valid',
        'c165_finalization_observation_ready',
        'rollout_state_lock_valid',
        'rollout_state_observation_valid',
        'finalization_rollout_state_scope_valid',
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
        'primary_candidate_observed_in_controlled_rollout',
        'backup_candidate_observed_in_controlled_rollout',
        'primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review',
        'backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review',
        'a01_remains_comparator_only',
        'operator_approved',
        'post_rollout_observation_confirmed',
        'controlled_rollout_state_observation_confirmed',
        'observation_window_confirmed',
        'candidate_scope_confirmed',
        'observation_kill_switch_confirmed',
        'observation_rollback_confirmed',
        'production_config_unchanged_confirmed',
        'free_publication_locked_confirmed',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_FALSE_FIELDS = [
        'c166_topic_complete',
        'market_outcome_metrics_available',
        'price_performance_evaluated',
        'recommendation_quality_evaluated',
        'new_rollout_executed',
        'new_plan_confirm_mutation_executed',
        'new_catalog_read_executed',
        'watchlist_function_invoked_by_observation_review',
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_observed_in_controlled_rollout',
        'comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review',
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
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-result-review*-negative-*-test.json',
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-result-review*-missing-*-test.json',
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-result-review*-mismatch-*-test.json',
    ];

    public function execute(
        string $observationArtifact = self::DEFAULT_OBSERVATION_ARTIFACT,
        string $expectedObservationHash = self::DEFAULT_EXPECTED_OBSERVATION_HASH,
        string $expectedObservationFileSha1 = self::DEFAULT_EXPECTED_OBSERVATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($observationArtifact, $expectedObservationHash, $expectedObservationFileSha1);
        $artifact['source_artifact_locks'] = [$this->lockSummary('c166_post_rollout_observation', $load)];
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::LOCK_STATUS, 'C166 post-rollout observation artifact is missing or unreadable.', $outputPath, $overwrite, false);
        }
        if (! $load['convert_from_json_pass']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::JSON_STATUS, 'C166 post-rollout observation artifact is not ConvertFrom-Json compatible.', $outputPath, $overwrite, false);
        }
        if (! $load['hash_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::LOCK_STATUS, 'C166 post-rollout observation artifact hash mismatched.', $outputPath, $overwrite, false);
        }
        if (! $load['file_sha1_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::SHA_STATUS, 'C166 post-rollout observation file SHA1 mismatched.', $outputPath, $overwrite, false);
        }

        $observation = $load['payload'];
        if (($observation['status'] ?? null) !== self::EXPECTED_OBSERVATION_STATUS || ($observation['reason_code'] ?? null) !== self::EXPECTED_OBSERVATION_STATUS) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::STATUS_MISMATCH, 'C166 observation status is not result-review ready.', $outputPath, $overwrite, false);
        }
        if (($observation['phase_label'] ?? null) !== self::EXPECTED_OBSERVATION_PHASE) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::PHASE_MISMATCH, 'C166 observation phase label mismatched.', $outputPath, $overwrite, false);
        }
        if (! $this->observationNextRecommendationValid($observation)) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::NEXT_MISMATCH, 'C166 observation does not point to the same-topic result review.', $outputPath, $overwrite, false);
        }
        if (! $this->safetyValid($observation)) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::SAFETY_STATUS, 'C166 observation violates rollout, configuration, or publication safety.', $outputPath, $overwrite, false);
        }
        if (! $this->metricContractValid($observation)) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::METRIC_CONTRACT_STATUS, 'Unavailable market, price, or recommendation-quality metrics may not be inferred.', $outputPath, $overwrite, false);
        }
        if (! $this->watchlistFunctionScopeValid($observation)) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::FUNCTION_SCOPE_STATUS, 'C166 watchlist function observation scope is invalid.', $outputPath, $overwrite, false);
        }
        if (! $this->candidateScopeValid($observation)) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::CANDIDATE_SCOPE_STATUS, 'C166 observation candidate scope is invalid.', $outputPath, $overwrite, false);
        }
        if (! $this->observationComplete($observation)) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OBSERVATION_INCOMPLETE, 'C166 post-rollout observation evidence is incomplete.', $outputPath, $overwrite, false);
        }
        if (($options['operator_approved'] ?? false) !== true || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::APPROVAL_STATUS, 'Explicit operator approval and approval reference are required.', $outputPath, $overwrite, false);
        }
        foreach (self::CONFIRMATION_STATUSES as $option => $suffix) {
            if (($options[$option] ?? false) !== true) {
                return $this->finish(
                    $this->completeArtifact($artifact, $load, $options, false),
                    self::RUN_CODE.'_REJECTED_'.$suffix,
                    'Required C166 observation result-review confirmation is missing: '.$option.'.',
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

            return $this->finish($artifact, self::TEMPORARY_STATUS, 'Temporary C166 observation result-review artifacts remain.', $outputPath, $overwrite, false);
        }

        return $this->finish(
            $this->completeArtifact($artifact, $load, $options, true),
            self::PASS_STATUS,
            'C166 validated the locked post-rollout control-plane observation result for E02 primary and B01 backup; operator GO/NO-GO review may proceed without market-metric inference or free publication.',
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
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW',
            'c166_topic_complete' => false,
            'result_review_artifact_only' => true,
            'observation_basis' => self::OBSERVATION_BASIS,
            'market_outcome_metrics_available' => false,
            'price_performance_evaluated' => false,
            'recommendation_quality_evaluated' => false,
            'market_metrics_inferred_by_result_review' => false,
            'new_rollout_executed' => false,
            'new_plan_confirm_mutation_executed' => false,
            'new_catalog_read_executed' => false,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
            'watchlist_function_invoked_by_result_review' => false,
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
        $observation = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $lockValid = $this->loadValid($load);
        $observationValid = $this->observationValid($observation);
        $temporaryPaths = $this->temporaryNegativeArtifactPaths();

        return array_merge($artifact, [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_executed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_pass' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_pass' => $pass,
            'post_rollout_observation_result_reviewed' => $pass,
            'post_rollout_observation_result_valid' => $pass && $lockValid && $observationValid,
            'control_plane_observation_result_stable' => $pass && $observationValid,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_allowed_next' => $pass,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review_allowed_next' => $pass,
            'operator_go_no_go_review_required_next' => $pass,
            'c166_topic_number_retained_for_operator_go_no_go_review' => true,
            'c166_observation_lock_valid' => $lockValid,
            'c166_observation_result_valid' => $observationValid,
            'all_required_source_locks_valid' => $lockValid,
            'controlled_rollout_executed' => ($observation['controlled_rollout_executed'] ?? false) === true,
            'controlled_rollout_active' => ($observation['controlled_rollout_active'] ?? false) === true,
            'controlled_rollout_only' => ($observation['controlled_rollout_only'] ?? false) === true,
            'plan_confirm_mutated' => ($observation['plan_confirm_mutated'] ?? false) === true,
            'plan_confirm_runtime_reads_activated_catalog' => ($observation['plan_confirm_runtime_reads_activated_catalog'] ?? false) === true,
            'live_plan_confirm_rollout_executed' => ($observation['live_plan_confirm_rollout_executed'] ?? false) === true,
            'unrestricted_rollout_allowed' => ($observation['unrestricted_rollout_allowed'] ?? true) === true,
            'kill_switch_confirmed' => ($observation['kill_switch_confirmed'] ?? false) === true,
            'rollback_confirmed' => ($observation['rollback_confirmed'] ?? false) === true,
            'rollout_state_record_count' => (int) ($observation['rollout_state_record_count'] ?? 0),
            'watchlist_function_invoked_during_execution' => ($observation['watchlist_function_invoked_during_execution'] ?? false) === true,
            'watchlist_function_invoked_by_observation_review' => ($observation['watchlist_function_invoked_by_observation_review'] ?? true) === true,
            'watchlist_function_primary_candidate_observed' => ($observation['watchlist_function_primary_candidate_observed'] ?? false) === true,
            'watchlist_function_backup_candidate_observed' => ($observation['watchlist_function_backup_candidate_observed'] ?? false) === true,
            'watchlist_function_comparator_candidate_observed' => ($observation['watchlist_function_comparator_candidate_observed'] ?? true) === true,
            'primary_candidate_observation_result_reviewed' => $pass,
            'backup_candidate_observation_result_reviewed' => $pass,
            'comparator_candidate_observation_result_reviewed' => false,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review' => false,
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'result_review_confirmed' => ($options['result_review_confirmed'] ?? false) === true,
            'post_rollout_observation_result_confirmed' => ($options['post_rollout_observation_result_confirmed'] ?? false) === true,
            'observation_artifact_locked_confirmed' => ($options['observation_artifact_locked_confirmed'] ?? false) === true,
            'control_plane_snapshot_confirmed' => ($options['control_plane_snapshot_confirmed'] ?? false) === true,
            'candidate_scope_confirmed' => ($options['candidate_scope_confirmed'] ?? false) === true,
            'result_review_kill_switch_confirmed' => ($options['kill_switch_confirmed'] ?? false) === true,
            'result_review_rollback_confirmed' => ($options['rollback_confirmed'] ?? false) === true,
            'production_config_unchanged_confirmed' => ($options['production_config_unchanged_confirmed'] ?? false) === true,
            'free_publication_locked_confirmed' => ($options['free_publication_locked_confirmed'] ?? false) === true,
            'market_metrics_not_inferred_confirmed' => ($options['market_metrics_not_inferred_confirmed'] ?? false) === true,
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'source_lock_validation_summary' => [
                'c166_observation_lock_valid' => $lockValid,
                'all_required_source_locks_valid' => $lockValid,
            ],
            'c166_post_rollout_observation_carry_forward_summary' => [
                'observation_valid' => $observationValid,
                'source_status' => $observation['status'] ?? null,
                'source_next_recommendation' => $observation['next_step_recommendation'] ?? null,
                'control_plane_snapshot_captured' => ($observation['post_rollout_control_plane_snapshot_captured'] ?? false) === true,
                'controlled_rollout_observation_stable' => ($observation['controlled_rollout_observation_stable'] ?? false) === true,
                'observation_basis' => $observation['observation_basis'] ?? null,
            ],
            'observation_metric_result_review_contract' => [
                'control_plane_metrics_reviewed' => $pass,
                'market_outcome_metrics_available' => false,
                'price_performance_evaluated' => false,
                'recommendation_quality_evaluated' => false,
                'market_metrics_inferred_by_result_review' => false,
                'operator_confirmed_market_metrics_not_inferred' => ($options['market_metrics_not_inferred_confirmed'] ?? false) === true,
                'operator_review_must_not_infer_unavailable_market_metrics' => true,
            ],
            'watchlist_function_observation_result_summary' => [
                'watchlist_function_scope_valid' => $this->watchlistFunctionScopeValid($observation),
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
                'invoked_during_controlled_rollout_execution' => ($observation['watchlist_function_invoked_during_execution'] ?? false) === true,
                'invoked_by_observation_review' => false,
                'invoked_by_result_review' => false,
                'primary_candidate_result_reviewed' => $pass,
                'backup_candidate_result_reviewed' => $pass,
                'comparator_candidate_result_reviewed' => false,
            ],
            'candidate_scope_observation_result_summary' => [
                'candidate_scope_valid' => $this->candidateScopeValid($observation),
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'primary_result_reviewed' => $pass,
                'backup_result_reviewed' => $pass,
                'comparator_result_reviewed_as_rollout' => false,
                'a01_remains_comparator_only' => true,
                'candidate_rerank_executed' => false,
                'strategy_retune_executed' => false,
            ],
            'publication_and_rollout_result_review_safety_summary' => [
                'controlled_rollout_observation_reviewed' => $pass,
                'result_review_artifact_only' => true,
                'new_rollout_executed' => false,
                'new_plan_confirm_mutation_executed' => false,
                'new_catalog_read_executed' => false,
                'production_config_mutated' => false,
                'unrestricted_rollout_allowed' => false,
                'free_publication_allowed' => false,
                'official_output_published' => false,
                'kill_switch_confirmed' => ($observation['kill_switch_confirmed'] ?? false) === true,
                'rollback_confirmed' => ($observation['rollback_confirmed'] ?? false) === true,
            ],
            'operator_result_review_confirmation_summary' => $this->operatorConfirmationSummary($options),
            'temporary_negative_artifact_guard_summary' => [
                'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
                'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
                'temporary_negative_artifact_paths' => $temporaryPaths,
                'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
            ],
            'next_post_rollout_observation_operator_go_no_go_decision' => [
                'observation_result_valid' => $pass,
                'next_recommendation' => $pass ? self::NEXT_OPERATOR_REVIEW : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_REPAIR',
                'same_topic_c166_continues' => true,
                'operator_go_no_go_review_required_next' => $pass,
                'operator_go_no_go_review_requires_locked_result_review_artifact' => $pass,
                'free_publication_allowed_next' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_manifest' => [
                'manifest_created' => $pass,
                'result_review_artifact_only' => true,
                'source_observation_path' => $load['path'],
                'source_observation_hash' => $load['actual_hash'],
                'control_plane_observation_result_valid' => $pass && $observationValid,
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
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_checklist' => [
                'observation_artifact_lock_reviewed' => true,
                'control_plane_observation_result_reviewed' => true,
                'candidate_scope_reviewed' => true,
                'watchlist_function_scope_reviewed' => true,
                'market_metric_contract_reviewed' => true,
                'kill_switch_reviewed' => true,
                'rollback_reviewed' => true,
                'production_config_guard_reviewed' => true,
                'free_publication_guard_reviewed' => true,
                'operator_go_no_go_review_required_next' => $pass,
            ],
            'c166_candidate_post_rollout_observation_result_scorecard' => $this->candidateScorecard($pass),
            'progress_summary' => [
                'progress_marker' => 'PR-92_C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW',
                'topic_code' => 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION',
                'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW',
                'c166_topic_complete' => false,
                'observation_result_review_completed' => $pass,
                'operator_go_no_go_review_required_next' => $pass,
            ],
            'planned_next_summary' => [
                'planned_next_review' => $pass ? self::NEXT_OPERATOR_REVIEW : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_REPAIR',
                'planned_next_scope' => $pass ? 'same-topic C166 operator GO/NO-GO review over the locked control-plane observation result' : 'repair failed C166 observation result-review evidence',
                'same_topic_c166_continues' => true,
            ],
            'diagnostics' => [
                'C166 result review locks the post-rollout observation artifact and executes no runtime action.',
                'The available result is control-plane stability for E02 primary and B01 backup; A01 remains comparator-only.',
                'Market outcome, price performance, and recommendation quality remain unavailable and are not inferred.',
                'A passing result proceeds inside C166 to operator GO/NO-GO review with free publication locked.',
            ],
        ]);
    }

    private function observationValid(array $observation): bool
    {
        return ($observation['status'] ?? null) === self::EXPECTED_OBSERVATION_STATUS
            && ($observation['reason_code'] ?? null) === self::EXPECTED_OBSERVATION_STATUS
            && ($observation['phase_label'] ?? null) === self::EXPECTED_OBSERVATION_PHASE
            && $this->observationNextRecommendationValid($observation)
            && $this->safetyValid($observation)
            && $this->metricContractValid($observation)
            && $this->watchlistFunctionScopeValid($observation)
            && $this->candidateScopeValid($observation)
            && $this->observationComplete($observation);
    }

    private function observationNextRecommendationValid(array $observation): bool
    {
        return ($observation['next_step_recommendation'] ?? null) === self::RUN_CODE
            && $this->valueAt($observation, ['next_post_rollout_observation_result_review_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($observation, ['next_post_rollout_observation_result_review_decision', 'same_topic_c166_continues']) === true
            && $this->valueAt($observation, ['next_post_rollout_observation_result_review_decision', 'observation_result_review_required_next']) === true
            && $this->valueAt($observation, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE
            && $this->valueAt($observation, ['planned_next_summary', 'same_topic_c166_continues']) === true;
    }

    private function observationComplete(array $observation): bool
    {
        foreach (self::REQUIRED_TRUE_FIELDS as $field) {
            if (($observation[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_FALSE_FIELDS as $field) {
            if (($observation[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($observation['run_code'] ?? null) === self::EXPECTED_OBSERVATION_RUN
            && ($observation['topic_code'] ?? null) === 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION'
            && ($observation['topic_stage'] ?? null) === 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW'
            && ($observation['observation_basis'] ?? null) === self::OBSERVATION_BASIS
            && ($observation['rollout_state_record_count'] ?? null) === 2
            && $this->valueAt($observation, ['source_lock_validation_summary', 'all_required_source_locks_valid']) === true
            && $this->valueAt($observation, ['rollout_state_observation_snapshot', 'snapshot_captured']) === true
            && $this->valueAt($observation, ['rollout_state_observation_snapshot', 'controlled_rollout_active']) === true
            && $this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_manifest', 'manifest_created']) === true
            && $this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_checklist', 'observation_result_review_required_next']) === true;
    }

    private function metricContractValid(array $observation): bool
    {
        foreach (['market_outcome_metrics_available', 'price_performance_evaluated', 'recommendation_quality_evaluated'] as $field) {
            if (($observation[$field] ?? null) !== false || $this->valueAt($observation, ['observation_scope_and_metric_contract', $field]) !== false) {
                return false;
            }
        }

        return $this->valueAt($observation, ['observation_scope_and_metric_contract', 'result_review_must_not_infer_unavailable_market_metrics']) === true
            && $this->valueAt($observation, ['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_manifest', 'market_outcome_metrics_available']) === false;
    }

    private function watchlistFunctionScopeValid(array $observation): bool
    {
        return ($observation['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($observation['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && ($observation['watchlist_function_invoked_during_execution'] ?? null) === true
            && ($observation['watchlist_function_invoked_by_observation_review'] ?? null) === false
            && $this->valueAt($observation, ['watchlist_function_observation_summary', 'watchlist_function_scope_valid']) === true
            && $this->valueAt($observation, ['watchlist_function_observation_summary', 'watchlist_function_used']) === self::WATCHLIST_FUNCTION
            && $this->valueAt($observation, ['watchlist_function_observation_summary', 'watchlist_function_runtime_mode']) === self::RUNTIME_MODE
            && $this->valueAt($observation, ['watchlist_function_observation_summary', 'invoked_by_observation_review']) === false;
    }

    private function candidateScopeValid(array $observation): bool
    {
        $scorecard = $observation['c166_candidate_post_rollout_observation_scorecard'] ?? null;
        if (! is_array($scorecard) || count($scorecard) !== 3) {
            return false;
        }

        return ($observation['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($observation['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($observation['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($observation['a01_remains_comparator_only'] ?? null) === true
            && $this->valueAt($observation, ['candidate_scope_observation_summary', 'candidate_scope_valid']) === true
            && $this->valueAt($observation, ['candidate_scope_observation_summary', 'candidate_rerank_executed']) === false
            && $this->valueAt($observation, ['candidate_scope_observation_summary', 'strategy_retune_executed']) === false
            && $this->scorecardRowValid($scorecard[0] ?? null, self::PRIMARY_CANDIDATE, 'primary_controlled_rollout', true)
            && $this->scorecardRowValid($scorecard[1] ?? null, self::BACKUP_CANDIDATE, 'backup_controlled_rollout', true)
            && $this->scorecardRowValid($scorecard[2] ?? null, self::COMPARATOR_CANDIDATE, 'comparator_only', false);
    }

    private function scorecardRowValid($row, string $candidate, string $role, bool $observed): bool
    {
        return is_array($row)
            && ($row['candidate_code'] ?? null) === $candidate
            && ($row['role'] ?? null) === $role
            && ($row['control_plane_observed'] ?? null) === $observed
            && ($row['ready_for_observation_result_review'] ?? null) === $observed;
    }

    private function safetyValid(array $observation): bool
    {
        foreach (['new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed', 'production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed', 'free_publication_allowed', 'unrestricted_publication_allowed'] as $field) {
            if (($observation[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($observation['controlled_rollout_active'] ?? null) === true
            && ($observation['controlled_rollout_only'] ?? null) === true
            && ($observation['kill_switch_confirmed'] ?? null) === true
            && ($observation['rollback_confirmed'] ?? null) === true
            && $this->valueAt($observation, ['publication_and_rollout_safety_summary', 'new_rollout_executed']) === false
            && $this->valueAt($observation, ['publication_and_rollout_safety_summary', 'production_config_mutated']) === false
            && $this->valueAt($observation, ['publication_and_rollout_safety_summary', 'free_publication_allowed']) === false
            && $this->valueAt($observation, ['publication_and_rollout_safety_summary', 'kill_switch_confirmed']) === true
            && $this->valueAt($observation, ['publication_and_rollout_safety_summary', 'rollback_confirmed']) === true;
    }

    private function operatorConfirmationSummary(array $options): array
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
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'role' => 'primary_controlled_rollout', 'observation_result_reviewed' => $pass, 'ready_for_operator_go_no_go_review' => $pass],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'role' => 'backup_controlled_rollout', 'observation_result_reviewed' => $pass, 'ready_for_operator_go_no_go_review' => $pass],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'role' => 'comparator_only', 'observation_result_reviewed' => false, 'ready_for_operator_go_no_go_review' => false],
        ];
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $pass): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $pass
            ? 'C166_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_VALID_READY_FOR_SAME_TOPIC_OPERATOR_GO_NO_GO_REVIEW_FREE_PUBLICATION_LOCKED'
            : $status;
        $artifact['next_step_recommendation'] = $pass ? self::NEXT_OPERATOR_REVIEW : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_REPAIR';
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $pass ? 0 : 1,
            'failures' => $pass ? [] : [$status],
            'attribution' => $pass ? 'NONE' : 'C166_OBSERVATION_RESULT_REVIEW_SOURCE_OR_CONFIRMATION',
        ];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function lockSummary(string $source, array $load): array
    {
        return [
            'source' => $source,
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
            'expected_c166_observation_hash' => $load['expected_hash'],
            'actual_c166_observation_hash' => $load['actual_hash'],
            'c166_observation_hash_match' => $load['hash_match'],
            'expected_c166_observation_file_sha1' => $load['expected_file_sha1'],
            'actual_c166_observation_file_sha1' => $load['actual_file_sha1'],
            'c166_observation_file_sha1_match' => $load['file_sha1_match'],
            'c166_observation_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c166_observation_convert_from_json_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
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
