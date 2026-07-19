<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewService
{
    public const RUN_CODE = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW';
    public const PHASE_LABEL = 'PR-91 / C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_C165_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C165_FINALIZATION_HASH = '618a09a64ba295aee023edc8131452782e184a9f';
    public const DEFAULT_EXPECTED_C165_FINALIZATION_FILE_SHA1 = '8EBDA0F4267597ED04F7AB798A1B1A227ACE4B9A';
    public const DEFAULT_ROLLOUT_STATE_ARTIFACT = 'storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json';
    public const DEFAULT_EXPECTED_ROLLOUT_STATE_HASH = '3a8350955f6a1396f5225af3fddcfa31fa622904';
    public const DEFAULT_EXPECTED_ROLLOUT_STATE_FILE_SHA1 = '4B58D3A17B56136CF02BE1635FB2F16F12831722';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';
    private const EXPECTED_FINALIZATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_CONTROLLED_ROLLOUT_CLOSED_READY_FOR_POST_ROLLOUT_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_FINALIZATION_PHASE = 'PR-90 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_STATE_TYPE = 'weekly_swing_watchlist_plan_confirm_controlled_rollout_state';
    private const EXPECTED_STATE_SOURCE = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION';
    private const NEXT_RESULT_REVIEW = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW';

    private const PASS_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_PASSED_CONTROLLED_ROLLOUT_OBSERVED_READY_FOR_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const FINALIZATION_LOCK_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_C165_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const FINALIZATION_SHA_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_C165_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const FINALIZATION_JSON_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_C165_FINALIZATION_JSON_COMPATIBILITY_VIOLATION';
    private const FINALIZATION_STATE_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_C165_FINALIZATION_INCOMPLETE';
    private const STATE_LOCK_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_ROLLOUT_STATE_ARTIFACT_LOCK_MISMATCH';
    private const STATE_SHA_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_ROLLOUT_STATE_FILE_SHA1_LOCK_MISMATCH';
    private const STATE_JSON_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_ROLLOUT_STATE_JSON_COMPATIBILITY_VIOLATION';
    private const STATE_RESULT_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_ROLLOUT_STATE_OBSERVATION_INVALID';
    private const CROSS_SOURCE_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_FINALIZATION_ROLLOUT_STATE_SCOPE_MISMATCH';
    private const APPROVAL_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const CONFIRMATION_STATUSES = [
        'post_rollout_observation_confirmed' => 'POST_ROLLOUT_OBSERVATION_CONFIRMATION_MISSING',
        'controlled_rollout_state_observation_confirmed' => 'CONTROLLED_ROLLOUT_STATE_OBSERVATION_CONFIRMATION_MISSING',
        'observation_window_confirmed' => 'OBSERVATION_WINDOW_CONFIRMATION_MISSING',
        'candidate_scope_confirmed' => 'CANDIDATE_SCOPE_CONFIRMATION_MISSING',
        'kill_switch_confirmed' => 'KILL_SWITCH_CONFIRMATION_MISSING',
        'rollback_confirmed' => 'ROLLBACK_CONFIRMATION_MISSING',
        'production_config_unchanged_confirmed' => 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING',
        'free_publication_locked_confirmed' => 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-review*-negative-*-test.json',
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-review*-missing-*-test.json',
        'storage/app/watchlist/backtest/c166-*post-rollout-observation-review*-mismatch-*-test.json',
    ];

    public function execute(
        string $c165FinalizationArtifact = self::DEFAULT_C165_FINALIZATION_ARTIFACT,
        string $expectedC165FinalizationHash = self::DEFAULT_EXPECTED_C165_FINALIZATION_HASH,
        string $expectedC165FinalizationFileSha1 = self::DEFAULT_EXPECTED_C165_FINALIZATION_FILE_SHA1,
        string $rolloutStateArtifact = self::DEFAULT_ROLLOUT_STATE_ARTIFACT,
        string $expectedRolloutStateHash = self::DEFAULT_EXPECTED_ROLLOUT_STATE_HASH,
        string $expectedRolloutStateFileSha1 = self::DEFAULT_EXPECTED_ROLLOUT_STATE_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $finalLoad = $this->loadJsonLock($c165FinalizationArtifact, $expectedC165FinalizationHash, $expectedC165FinalizationFileSha1, 'artifact_hash');
        $stateLoad = $this->loadJsonLock($rolloutStateArtifact, $expectedRolloutStateHash, $expectedRolloutStateFileSha1, 'rollout_state_hash');
        $artifact['source_artifact_locks'] = [$this->lockSummary('c165_finalization', $finalLoad), $this->lockSummary('rollout_state', $stateLoad)];
        $artifact = array_merge($artifact, $this->topLevelLockAliases($finalLoad, $stateLoad));

        foreach ([
            [$finalLoad, self::FINALIZATION_LOCK_STATUS, self::FINALIZATION_SHA_STATUS, self::FINALIZATION_JSON_STATUS],
            [$stateLoad, self::STATE_LOCK_STATUS, self::STATE_SHA_STATUS, self::STATE_JSON_STATUS],
        ] as [$load, $hashStatus, $shaStatus, $jsonStatus]) {
            if (! $load['exists'] || ! is_array($load['payload']) || ! $load['hash_match']) {
                return $this->finish($this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false), $hashStatus, 'Required C166 observation source is missing, unreadable, or hash-mismatched.', $outputPath, $overwrite, false);
            }
            if (! $load['file_sha1_match']) {
                return $this->finish($this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false), $shaStatus, 'Required C166 observation source file SHA1 mismatched.', $outputPath, $overwrite, false);
            }
            if (! $load['convert_from_json_pass']) {
                return $this->finish($this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false), $jsonStatus, 'Required C166 observation source is not ConvertFrom-Json compatible.', $outputPath, $overwrite, false);
            }
        }

        $finalization = $finalLoad['payload'];
        $state = $stateLoad['payload'];
        if (! $this->finalizationValid($finalization)) {
            return $this->finish($this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false), self::FINALIZATION_STATE_STATUS, 'C165 finalization is not complete or observation-ready.', $outputPath, $overwrite, false);
        }
        if (! $this->rolloutStateValid($state)) {
            return $this->finish($this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false), self::STATE_RESULT_STATUS, 'Controlled rollout state is not valid for C166 observation.', $outputPath, $overwrite, false);
        }
        if (! $this->crossSourceScopeValid($finalization, $state)) {
            return $this->finish($this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false), self::CROSS_SOURCE_STATUS, 'C165 finalization and rollout state do not describe the same controlled scope.', $outputPath, $overwrite, false);
        }
        if (($options['operator_approved'] ?? false) !== true || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false), self::APPROVAL_STATUS, 'Explicit operator approval and approval reference are required for C166 observation.', $outputPath, $overwrite, false);
        }
        foreach (self::CONFIRMATION_STATUSES as $option => $suffix) {
            if (($options[$option] ?? false) !== true) {
                $status = self::RUN_CODE.'_REJECTED_'.$suffix;

                return $this->finish($this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false), $status, 'Required C166 observation confirmation is missing: '.$option.'.', $outputPath, $overwrite, false);
            }
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $artifact = $this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_paths'] = $temporaryPaths;

            return $this->finish($artifact, self::TEMPORARY_STATUS, 'Temporary C166 observation negative artifacts remain.', $outputPath, $overwrite, false);
        }

        return $this->finish(
            $this->completeArtifact($artifact, $finalLoad, $stateLoad, $options, true),
            self::PASS_STATUS,
            'C166 captured a controlled post-rollout control-plane snapshot for E02 primary and B01 backup; result review may proceed without free publication.',
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
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW',
            'c166_topic_complete' => false,
            'observation_review_artifact_only' => true,
            'observation_basis' => 'LOCKED_CONTROL_PLANE_RUNTIME_STATE_SNAPSHOT',
            'market_outcome_metrics_available' => false,
            'price_performance_evaluated' => false,
            'recommendation_quality_evaluated' => false,
            'new_rollout_executed' => false,
            'new_plan_confirm_mutation_executed' => false,
            'new_catalog_read_executed' => false,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
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

    private function completeArtifact(array $artifact, array $finalLoad, array $stateLoad, array $options, bool $pass): array
    {
        $finalization = is_array($finalLoad['payload'] ?? null) ? $finalLoad['payload'] : [];
        $state = is_array($stateLoad['payload'] ?? null) ? $stateLoad['payload'] : [];
        $finalValid = $this->finalizationValid($finalization);
        $stateValid = $this->rolloutStateValid($state);
        $scopeValid = $this->crossSourceScopeValid($finalization, $state);
        $temporaryPaths = $this->temporaryNegativeArtifactPaths();

        return array_merge($artifact, [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_executed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_pass' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_pass' => $pass,
            'post_rollout_observation_started' => $pass,
            'post_rollout_control_plane_snapshot_captured' => $pass,
            'controlled_rollout_observed' => $pass,
            'controlled_rollout_observation_stable' => $pass && $finalValid && $stateValid && $scopeValid,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_review' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_result_review_allowed_next' => $pass,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_review_allowed_next' => $pass,
            'c166_topic_number_retained_for_observation_result_review' => true,
            'c165_finalization_lock_valid' => $this->loadValid($finalLoad),
            'c165_finalization_observation_ready' => $finalValid,
            'rollout_state_lock_valid' => $this->loadValid($stateLoad),
            'rollout_state_observation_valid' => $stateValid,
            'finalization_rollout_state_scope_valid' => $scopeValid,
            'all_required_source_locks_valid' => $this->loadValid($finalLoad) && $this->loadValid($stateLoad),
            'controlled_rollout_executed' => ($state['live_plan_confirm_rollout_executed'] ?? false) === true,
            'controlled_rollout_active' => ($state['controlled_rollout_active'] ?? false) === true,
            'controlled_rollout_only' => ($state['controlled_rollout_only'] ?? false) === true,
            'plan_confirm_mutated' => ($state['plan_confirm_mutated'] ?? false) === true,
            'plan_confirm_runtime_reads_activated_catalog' => ($state['plan_confirm_runtime_reads_activated_catalog'] ?? false) === true,
            'live_plan_confirm_rollout_executed' => ($state['live_plan_confirm_rollout_executed'] ?? false) === true,
            'unrestricted_rollout_allowed' => false,
            'kill_switch_confirmed' => ($state['kill_switch_confirmed'] ?? false) === true,
            'rollback_confirmed' => ($state['rollback_confirmed'] ?? false) === true,
            'rollout_state_record_count' => count((array) ($state['rollout_rows'] ?? [])),
            'watchlist_function_invoked_during_execution' => true,
            'watchlist_function_invoked_by_observation_review' => false,
            'watchlist_function_primary_candidate_observed' => $pass,
            'watchlist_function_backup_candidate_observed' => $pass,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_observed_in_controlled_rollout' => $pass,
            'backup_candidate_observed_in_controlled_rollout' => $pass,
            'comparator_candidate_observed_in_controlled_rollout' => false,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review' => false,
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'post_rollout_observation_confirmed' => ($options['post_rollout_observation_confirmed'] ?? false) === true,
            'controlled_rollout_state_observation_confirmed' => ($options['controlled_rollout_state_observation_confirmed'] ?? false) === true,
            'observation_window_confirmed' => ($options['observation_window_confirmed'] ?? false) === true,
            'candidate_scope_confirmed' => ($options['candidate_scope_confirmed'] ?? false) === true,
            'observation_kill_switch_confirmed' => ($options['kill_switch_confirmed'] ?? false) === true,
            'observation_rollback_confirmed' => ($options['rollback_confirmed'] ?? false) === true,
            'production_config_unchanged_confirmed' => ($options['production_config_unchanged_confirmed'] ?? false) === true,
            'free_publication_locked_confirmed' => ($options['free_publication_locked_confirmed'] ?? false) === true,
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'source_lock_validation_summary' => [
                'c165_finalization_lock_valid' => $this->loadValid($finalLoad),
                'rollout_state_lock_valid' => $this->loadValid($stateLoad),
                'all_required_source_locks_valid' => $this->loadValid($finalLoad) && $this->loadValid($stateLoad),
            ],
            'c165_finalization_carry_forward_summary' => [
                'finalization_valid' => $finalValid,
                'source_status' => $finalization['status'] ?? null,
                'source_next_recommendation' => $finalization['next_step_recommendation'] ?? null,
                'go_decision_finalized' => ($finalization['go_decision_finalized'] ?? false) === true,
                'controlled_rollout_topic_closed' => ($finalization['controlled_rollout_topic_closed'] ?? false) === true,
                'c165_topic_complete' => ($finalization['c165_topic_complete'] ?? false) === true,
            ],
            'rollout_state_observation_snapshot' => [
                'snapshot_captured' => $pass,
                'observation_basis' => 'LOCKED_CONTROL_PLANE_RUNTIME_STATE_SNAPSHOT',
                'rollout_state_type' => $state['rollout_state_type'] ?? null,
                'rollout_state_hash' => $state['rollout_state_hash'] ?? null,
                'controlled_rollout_scope' => $state['controlled_rollout_scope'] ?? null,
                'controlled_rollout_active' => ($state['controlled_rollout_active'] ?? false) === true,
                'controlled_rollout_only' => ($state['controlled_rollout_only'] ?? false) === true,
                'rollout_state_record_count' => count((array) ($state['rollout_rows'] ?? [])),
                'kill_switch_confirmed' => ($state['kill_switch_confirmed'] ?? false) === true,
                'rollback_confirmed' => ($state['rollback_confirmed'] ?? false) === true,
                'production_config_mutated' => ($state['production_config_mutated'] ?? true) === true,
                'free_publication_allowed' => ($state['free_publication_allowed'] ?? true) === true,
                'unrestricted_publication_allowed' => ($state['unrestricted_publication_allowed'] ?? true) === true,
            ],
            'observation_scope_and_metric_contract' => [
                'control_plane_state_observed' => $pass,
                'candidate_scope_observed' => $pass,
                'function_runtime_mode_observed' => $pass,
                'kill_switch_observed' => $pass,
                'rollback_observed' => $pass,
                'publication_guard_observed' => $pass,
                'production_config_guard_observed' => $pass,
                'market_outcome_metrics_available' => false,
                'price_performance_evaluated' => false,
                'recommendation_quality_evaluated' => false,
                'result_review_must_not_infer_unavailable_market_metrics' => true,
            ],
            'watchlist_function_observation_summary' => [
                'watchlist_function_scope_valid' => $this->functionScopeValid($state),
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
                'invoked_during_controlled_rollout_execution' => true,
                'invoked_by_observation_review' => false,
                'primary_candidate_observed' => $pass,
                'backup_candidate_observed' => $pass,
                'comparator_candidate_observed' => false,
            ],
            'candidate_scope_observation_summary' => [
                'candidate_scope_valid' => $this->candidateScopeValid($state),
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'primary_observed' => $pass,
                'backup_observed' => $pass,
                'comparator_observed_as_rollout' => false,
                'a01_remains_comparator_only' => true,
                'candidate_rerank_executed' => false,
                'strategy_retune_executed' => false,
            ],
            'publication_and_rollout_safety_summary' => [
                'controlled_rollout_observed_active' => ($state['controlled_rollout_active'] ?? false) === true,
                'observation_artifact_only' => true,
                'new_rollout_executed' => false,
                'new_plan_confirm_mutation_executed' => false,
                'production_config_mutated' => false,
                'unrestricted_rollout_allowed' => false,
                'free_publication_allowed' => false,
                'official_output_published' => false,
                'kill_switch_confirmed' => ($state['kill_switch_confirmed'] ?? false) === true,
                'rollback_confirmed' => ($state['rollback_confirmed'] ?? false) === true,
            ],
            'operator_observation_confirmation_summary' => $this->confirmationSummary($options),
            'temporary_negative_artifact_guard_summary' => [
                'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
                'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
                'temporary_negative_artifact_paths' => $temporaryPaths,
                'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
            ],
            'next_post_rollout_observation_result_review_decision' => [
                'observation_valid' => $pass,
                'next_recommendation' => $pass ? self::NEXT_RESULT_REVIEW : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_REPAIR',
                'same_topic_c166_continues' => true,
                'observation_result_review_required_next' => $pass,
                'observation_result_review_requires_locked_observation_artifact' => $pass,
                'free_publication_allowed_next' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_manifest' => [
                'manifest_created' => $pass,
                'observation_artifact_only' => true,
                'source_finalization_path' => $finalLoad['path'],
                'source_finalization_hash' => $finalLoad['actual_hash'],
                'source_rollout_state_path' => $stateLoad['path'],
                'source_rollout_state_hash' => $stateLoad['actual_hash'],
                'control_plane_snapshot_captured' => $pass,
                'controlled_rollout_active' => ($state['controlled_rollout_active'] ?? false) === true,
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'market_outcome_metrics_available' => false,
                'new_rollout_executed' => false,
                'official_output_published' => false,
                'free_publication_allowed' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_checklist' => [
                'finalization_lock_reviewed' => true,
                'rollout_state_lock_reviewed' => true,
                'control_plane_state_reviewed' => true,
                'candidate_scope_reviewed' => true,
                'watchlist_function_scope_reviewed' => true,
                'kill_switch_reviewed' => true,
                'rollback_reviewed' => true,
                'production_config_guard_reviewed' => true,
                'free_publication_guard_reviewed' => true,
                'observation_result_review_required_next' => $pass,
            ],
            'c166_candidate_post_rollout_observation_scorecard' => $this->candidateScorecard($pass),
            'progress_summary' => [
                'progress_marker' => 'PR-91_C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW',
                'topic_code' => 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION',
                'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW',
                'c166_topic_complete' => false,
                'observation_snapshot_captured' => $pass,
                'observation_result_review_required_next' => $pass,
            ],
            'planned_next_summary' => [
                'planned_next_review' => $pass ? self::NEXT_RESULT_REVIEW : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_REPAIR',
                'planned_next_scope' => $pass ? 'same-topic C166 result review over the locked control-plane observation snapshot' : 'repair C166 observation evidence',
                'same_topic_c166_continues' => true,
            ],
            'diagnostics' => [
                'C166 observation locks the C165 finalization and active rollout state.',
                'The snapshot evaluates control-plane stability and safety guards, not unavailable market performance.',
                'The observation does not invoke the function, execute another rollout, or publish output.',
                'E02 remains primary, B01 remains backup, and A01 remains comparator-only for same-topic result review.',
            ],
        ]);
    }

    private function finalizationValid(array $finalization): bool
    {
        foreach (['go_decision_finalized', 'controlled_rollout_go_finalized', 'controlled_rollout_topic_closed', 'c165_topic_complete', 'c165_topic_complete_after_finalization', 'c166_post_rollout_observation_required_next', 'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_review', 'controlled_rollout_active', 'kill_switch_confirmed', 'rollback_confirmed', 'a01_remains_comparator_only'] as $field) {
            if (($finalization[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (['new_rollout_executed', 'new_plan_confirm_mutation_executed', 'production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed', 'watchlist_function_invoked_by_finalization'] as $field) {
            if (($finalization[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($finalization['run_code'] ?? null) === 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW'
            && ($finalization['status'] ?? null) === self::EXPECTED_FINALIZATION_STATUS
            && ($finalization['reason_code'] ?? null) === self::EXPECTED_FINALIZATION_STATUS
            && ($finalization['phase_label'] ?? null) === self::EXPECTED_FINALIZATION_PHASE
            && ($finalization['next_step_recommendation'] ?? null) === self::RUN_CODE
            && ($finalization['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($finalization['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && $this->candidateCodesValid($finalization)
            && $this->valueAt($finalization, ['next_post_rollout_observation_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($finalization, ['next_post_rollout_observation_decision', 'c166_may_start']) === true;
    }

    private function rolloutStateValid(array $state): bool
    {
        foreach (['controlled_rollout_only', 'controlled_rollout_active', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed', 'kill_switch_confirmed', 'rollback_confirmed'] as $field) {
            if (($state[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (['unrestricted_rollout_allowed', 'production_config_mutated', 'weekly_swing_watchlist_official_output_published', 'free_publication_allowed', 'unrestricted_publication_allowed'] as $field) {
            if (($state[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($state['rollout_state_type'] ?? null) === self::EXPECTED_STATE_TYPE
            && ($state['source_run_code'] ?? null) === self::EXPECTED_STATE_SOURCE
            && ($state['controlled_rollout_scope'] ?? null) === 'PRIMARY_AND_BACKUP_ONLY'
            && $this->functionScopeValid($state)
            && $this->candidateScopeValid($state);
    }

    private function crossSourceScopeValid(array $finalization, array $state): bool
    {
        return $this->candidateCodesValid($finalization)
            && $this->candidateScopeValid($state)
            && ($finalization['watchlist_function_used'] ?? null) === ($state['watchlist_function_used'] ?? null)
            && ($finalization['watchlist_function_runtime_mode'] ?? null) === ($state['watchlist_function_runtime_mode'] ?? null)
            && (int) ($finalization['rollout_state_record_count'] ?? 0) === count((array) ($state['rollout_rows'] ?? []));
    }

    private function candidateCodesValid(array $source): bool
    {
        return ($source['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($source['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($source['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($source['a01_remains_comparator_only'] ?? null) === true;
    }

    private function functionScopeValid(array $state): bool
    {
        return ($state['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($state['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE;
    }

    private function candidateScopeValid(array $state): bool
    {
        $rows = $state['rollout_rows'] ?? null;
        if (! is_array($rows) || count($rows) !== 2) {
            return false;
        }

        return $this->rowValid($rows[0] ?? null, 1, self::PRIMARY_CANDIDATE, 'primary')
            && $this->rowValid($rows[1] ?? null, 2, self::BACKUP_CANDIDATE, 'backup')
            && $this->valueAt($state, ['comparator_candidate', 'candidate_code']) === self::COMPARATOR_CANDIDATE
            && $this->valueAt($state, ['comparator_candidate', 'controlled_rollout_executed']) === false
            && $this->valueAt($state, ['comparator_candidate', 'a01_remains_comparator_only']) === true;
    }

    private function rowValid($row, int $rank, string $candidate, string $role): bool
    {
        return is_array($row)
            && ($row['rank'] ?? null) === $rank
            && ($row['candidate_code'] ?? null) === $candidate
            && ($row['role'] ?? null) === $role
            && ($row['rollout_state'] ?? null) === 'controlled_executed'
            && ($row['publication_state'] ?? null) === 'not_free_published';
    }

    private function confirmationSummary(array $options): array
    {
        $summary = ['operator_approved' => ($options['operator_approved'] ?? false) === true, 'approval_reference' => (string) ($options['approval_reference'] ?? '')];
        foreach (self::CONFIRMATION_STATUSES as $option => $unused) {
            $summary[$option] = ($options[$option] ?? false) === true;
        }

        return $summary;
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'role' => 'primary_controlled_rollout', 'control_plane_observed' => $pass, 'ready_for_observation_result_review' => $pass],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'role' => 'backup_controlled_rollout', 'control_plane_observed' => $pass, 'ready_for_observation_result_review' => $pass],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'role' => 'comparator_only', 'control_plane_observed' => false, 'ready_for_observation_result_review' => false],
        ];
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $pass): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $pass ? 'C166_CONTROLLED_ROLLOUT_CONTROL_PLANE_OBSERVED_READY_FOR_SAME_TOPIC_RESULT_REVIEW_FREE_PUBLICATION_LOCKED' : $status;
        $artifact['next_step_recommendation'] = $pass ? self::NEXT_RESULT_REVIEW : 'C166_TARGETED_POST_ROLLOUT_OBSERVATION_REPAIR';
        $artifact['failure_attribution_summary'] = ['failure_count' => $pass ? 0 : 1, 'failures' => $pass ? [] : [$status], 'attribution' => $pass ? 'NONE' : 'C166_OBSERVATION_SOURCE_OR_CONFIRMATION'];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function lockSummary(string $source, array $load): array
    {
        return ['source' => $source, 'path' => $load['path'], 'expected_hash' => $load['expected_hash'], 'actual_hash' => $load['actual_hash'], 'hash_match' => $load['hash_match'], 'expected_file_sha1' => $load['expected_file_sha1'], 'actual_file_sha1' => $load['actual_file_sha1'], 'file_sha1_match' => $load['file_sha1_match'], 'convert_from_json_pass' => $load['convert_from_json_pass']];
    }

    private function topLevelLockAliases(array $finalLoad, array $stateLoad): array
    {
        return [
            'expected_c165_finalization_hash' => $finalLoad['expected_hash'], 'actual_c165_finalization_hash' => $finalLoad['actual_hash'], 'c165_finalization_hash_match' => $finalLoad['hash_match'],
            'expected_c165_finalization_file_sha1' => $finalLoad['expected_file_sha1'], 'actual_c165_finalization_file_sha1' => $finalLoad['actual_file_sha1'], 'c165_finalization_file_sha1_match' => $finalLoad['file_sha1_match'], 'c165_finalization_convert_from_json_pass' => $finalLoad['convert_from_json_pass'],
            'expected_rollout_state_hash' => $stateLoad['expected_hash'], 'actual_rollout_state_hash' => $stateLoad['actual_hash'], 'rollout_state_hash_match' => $stateLoad['hash_match'],
            'expected_rollout_state_file_sha1' => $stateLoad['expected_file_sha1'], 'actual_rollout_state_file_sha1' => $stateLoad['actual_file_sha1'], 'rollout_state_file_sha1_match' => $stateLoad['file_sha1_match'], 'rollout_state_convert_from_json_pass' => $stateLoad['convert_from_json_pass'],
        ];
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1, string $hashField): array
    {
        $exists = is_file($path); $payload = null; $actualHash = null; $actualFileSha1 = null; $duplicates = []; $jsonError = null;
        if ($exists) {
            $raw = (string) file_get_contents($path); $duplicates = $this->caseInsensitiveDuplicateTopLevelKeys($raw); $decoded = json_decode($raw, true); $jsonError = json_last_error();
            if (is_array($decoded)) { $payload = $decoded; $actualHash = $decoded[$hashField] ?? null; }
            $actualFileSha1 = strtoupper(sha1($raw));
        }

        return ['path' => $path, 'exists' => $exists, 'payload' => $payload, 'expected_hash' => $expectedHash, 'actual_hash' => $actualHash, 'hash_match' => $actualHash !== null && hash_equals($expectedHash, (string) $actualHash), 'expected_file_sha1' => strtoupper($expectedFileSha1), 'actual_file_sha1' => $actualFileSha1, 'file_sha1_match' => $actualFileSha1 !== null && strtoupper($expectedFileSha1) === $actualFileSha1, 'case_insensitive_duplicate_keys' => $duplicates, 'convert_from_json_pass' => $exists && is_array($payload) && $jsonError === JSON_ERROR_NONE && $duplicates === []];
    }

    private function loadValid(array $load): bool
    {
        return $load['exists'] && $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'];
    }

    private function caseInsensitiveDuplicateTopLevelKeys(string $raw): array
    {
        $length = strlen($raw); $depth = 0; $expectKey = false; $seen = []; $duplicates = [];
        for ($i = 0; $i < $length; $i++) {
            if ($raw[$i] === '"') {
                $start = $i++; $escaped = false;
                while ($i < $length) { if ($escaped) { $escaped = false; } elseif ($raw[$i] === '\\') { $escaped = true; } elseif ($raw[$i] === '"') { break; } $i++; }
                if ($depth === 1 && $expectKey) {
                    $token = substr($raw, $start, $i - $start + 1); $j = $i + 1; while ($j < $length && ctype_space($raw[$j])) { $j++; }
                    if ($j < $length && $raw[$j] === ':') { $key = json_decode($token, true); if (is_string($key)) { $lower = strtolower($key); if (isset($seen[$lower]) && ! in_array($key, $duplicates, true)) { $duplicates[] = $key; } $seen[$lower] = true; } $expectKey = false; }
                }
            } elseif ($raw[$i] === '{') { $depth++; $expectKey = $depth === 1; } elseif ($raw[$i] === '}') { $depth--; $expectKey = false; } elseif ($raw[$i] === ',' && $depth === 1) { $expectKey = true; }
        }

        return $duplicates;
    }

    private function temporaryNegativeArtifactPaths(): array
    {
        $paths = [];
        foreach (self::TEMPORARY_NEGATIVE_PATTERNS as $pattern) { foreach ((array) glob($pattern) as $path) { if (is_file($path)) { $paths[] = str_replace('\\', '/', $path); } } }

        return array_values(array_unique($paths));
    }

    private function writeJson(string $path, array $payload, bool $overwrite): void
    {
        if (! $overwrite && is_file($path)) { return; }
        $directory = dirname($path); if (! is_dir($directory)) { mkdir($directory, 0777, true); }
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash'], $artifact['artifact_path']); $this->sortRecursive($artifact);

        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function sortRecursive(array &$value): void
    {
        foreach ($value as &$item) { if (is_array($item)) { $this->sortRecursive($item); } } unset($item); if (! array_is_list($value)) { ksort($value); }
    }

    private function valueAt(array $source, array $path)
    {
        $current = $source;
        foreach ($path as $segment) { if (! is_array($current) || ! array_key_exists($segment, $current)) { return null; } $current = $current[$segment]; }

        return $current;
    }
}
