<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-90 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_C165_OPERATOR_ARTIFACT = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C165_OPERATOR_HASH = '48cd9784bb9df5ceef8b47ca970996398d104f54';
    public const DEFAULT_EXPECTED_C165_OPERATOR_FILE_SHA1 = '5457B6DDA328EF4FD1B0157E5857968D01965381';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';
    private const EXPECTED_OPERATOR_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_OPERATOR_PHASE = 'PR-89 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW';
    private const NEXT_OBSERVATION = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW';

    private const PASS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_CONTROLLED_ROLLOUT_CLOSED_READY_FOR_POST_ROLLOUT_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP';
    private const OPERATOR_LOCK_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C165_OPERATOR_ARTIFACT_LOCK_MISMATCH';
    private const OPERATOR_SHA_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C165_OPERATOR_FILE_SHA1_LOCK_MISMATCH';
    private const OPERATOR_JSON_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C165_OPERATOR_JSON_COMPATIBILITY_VIOLATION';
    private const OPERATOR_STATE_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C165_OPERATOR_GO_INVALID';
    private const APPROVAL_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const CONFIRMATION_STATUSES = [
        'go_decision_finalization_confirmed' => 'GO_DECISION_FINALIZATION_CONFIRMATION_MISSING',
        'controlled_rollout_topic_closure_confirmed' => 'CONTROLLED_ROLLOUT_TOPIC_CLOSURE_CONFIRMATION_MISSING',
        'operator_go_locked_confirmed' => 'OPERATOR_GO_LOCK_CONFIRMATION_MISSING',
        'controlled_rollout_result_confirmed' => 'CONTROLLED_ROLLOUT_RESULT_CONFIRMATION_MISSING',
        'kill_switch_confirmed' => 'KILL_SWITCH_CONFIRMATION_MISSING',
        'rollback_confirmed' => 'ROLLBACK_CONFIRMATION_MISSING',
        'production_config_unchanged_confirmed' => 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING',
        'free_publication_locked_confirmed' => 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING',
        'post_rollout_observation_required_confirmed' => 'POST_ROLLOUT_OBSERVATION_REQUIREMENT_CONFIRMATION_MISSING',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c165-*controlled-rollout-go-decision-finalization*-negative-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-go-decision-finalization*-missing-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-go-decision-finalization*-mismatch-*-test.json',
    ];

    public function execute(
        string $c165OperatorArtifact = self::DEFAULT_C165_OPERATOR_ARTIFACT,
        string $expectedC165OperatorHash = self::DEFAULT_EXPECTED_C165_OPERATOR_HASH,
        string $expectedC165OperatorFileSha1 = self::DEFAULT_EXPECTED_C165_OPERATOR_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($c165OperatorArtifact, $expectedC165OperatorHash, $expectedC165OperatorFileSha1);
        $artifact['source_artifact_locks'] = [$this->lockSummary($load)];
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload']) || ! $load['hash_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_LOCK_STATUS, 'C165 operator GO artifact is missing, unreadable, or hash-mismatched.', $outputPath, $overwrite, false);
        }
        if (! $load['file_sha1_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_SHA_STATUS, 'C165 operator GO artifact file SHA1 mismatched.', $outputPath, $overwrite, false);
        }
        if (! $load['convert_from_json_pass']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_JSON_STATUS, 'C165 operator GO artifact is not ConvertFrom-Json compatible.', $outputPath, $overwrite, false);
        }
        if (! $this->operatorGoValid($load['payload'])) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::OPERATOR_STATE_STATUS, 'C165 operator evidence is not a complete GO decision ready for finalization.', $outputPath, $overwrite, false);
        }
        if (($options['operator_approved'] ?? false) !== true || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false), self::APPROVAL_STATUS, 'Explicit operator approval and approval reference are required for finalization.', $outputPath, $overwrite, false);
        }
        foreach (self::CONFIRMATION_STATUSES as $option => $suffix) {
            if (($options[$option] ?? false) !== true) {
                $status = self::RUN_CODE.'_REJECTED_'.$suffix;

                return $this->finish($this->completeArtifact($artifact, $load, $options, false), $status, 'Required C165 finalization confirmation is missing: '.$option.'.', $outputPath, $overwrite, false);
            }
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $artifact = $this->completeArtifact($artifact, $load, $options, false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_paths'] = $temporaryPaths;

            return $this->finish($artifact, self::TEMPORARY_STATUS, 'Temporary C165 finalization negative artifacts remain.', $outputPath, $overwrite, false);
        }

        return $this->finish(
            $this->completeArtifact($artifact, $load, $options, true),
            self::PASS_STATUS,
            'C165 controlled rollout GO decision is finalized and the topic is closed; C166 post-rollout observation may proceed for E02 primary and B01 backup.',
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
            'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW',
            'go_decision_finalization_artifact_only' => true,
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

    private function completeArtifact(array $artifact, array $load, array $options, bool $pass): array
    {
        $operator = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $operatorValid = $this->operatorGoValid($operator);
        $temporaryPaths = $this->temporaryNegativeArtifactPaths();

        return array_merge($artifact, [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_go_decision_finalization_review_executed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_go_decision_finalization_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_go_decision_finalization_review_pass' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_go_decision_finalization_review_pass' => $pass,
            'go_decision_finalized' => $pass,
            'controlled_rollout_go_finalized' => $pass,
            'controlled_rollout_topic_closed' => $pass,
            'c165_topic_complete' => $pass,
            'c165_topic_complete_after_finalization' => $pass,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_review' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_review_allowed_next' => $pass,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_review_allowed_next' => $pass,
            'c166_post_rollout_observation_required_next' => $pass,
            'c165_operator_artifact_lock_valid' => $this->loadValid($load),
            'c165_operator_go_valid' => $operatorValid,
            'operator_decision' => $operator['operator_decision'] ?? 'INVALID',
            'operator_go_decision' => ($operator['operator_go_decision'] ?? false) === true,
            'operator_decision_confirmed' => ($operator['operator_decision_confirmed'] ?? false) === true,
            'operator_decision_reason' => (string) ($operator['operator_decision_reason'] ?? ''),
            'controlled_rollout_result_valid' => ($operator['controlled_rollout_result_valid'] ?? false) === true,
            'rollout_state_result_valid' => ($operator['rollout_state_result_valid'] ?? false) === true,
            'execution_rollout_state_integrity_valid' => ($operator['execution_rollout_state_integrity_valid'] ?? false) === true,
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
            'watchlist_function_invoked_by_finalization' => false,
            'watchlist_function_primary_candidate_observed' => ($operator['watchlist_function_primary_candidate_observed'] ?? false) === true,
            'watchlist_function_backup_candidate_observed' => ($operator['watchlist_function_backup_candidate_observed'] ?? false) === true,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review' => false,
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'go_decision_finalization_confirmed' => ($options['go_decision_finalization_confirmed'] ?? false) === true,
            'controlled_rollout_topic_closure_confirmed' => ($options['controlled_rollout_topic_closure_confirmed'] ?? false) === true,
            'operator_go_locked_confirmed' => ($options['operator_go_locked_confirmed'] ?? false) === true,
            'controlled_rollout_result_confirmed' => ($options['controlled_rollout_result_confirmed'] ?? false) === true,
            'finalization_kill_switch_confirmed' => ($options['kill_switch_confirmed'] ?? false) === true,
            'finalization_rollback_confirmed' => ($options['rollback_confirmed'] ?? false) === true,
            'production_config_unchanged_confirmed' => ($options['production_config_unchanged_confirmed'] ?? false) === true,
            'free_publication_locked_confirmed' => ($options['free_publication_locked_confirmed'] ?? false) === true,
            'post_rollout_observation_required_confirmed' => ($options['post_rollout_observation_required_confirmed'] ?? false) === true,
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'c165_operator_lock_validation_summary' => [
                'validation_completed' => true,
                'artifact_path' => $load['path'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'convert_from_json_pass' => $load['convert_from_json_pass'],
                'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
                'lock_valid' => $this->loadValid($load),
            ],
            'c165_operator_go_carry_forward_summary' => [
                'operator_go_valid' => $operatorValid,
                'source_status' => $operator['status'] ?? null,
                'source_phase_label' => $operator['phase_label'] ?? null,
                'source_next_recommendation' => $operator['next_step_recommendation'] ?? null,
                'operator_decision' => $operator['operator_decision'] ?? 'INVALID',
                'operator_go_decision' => ($operator['operator_go_decision'] ?? false) === true,
                'go_decision_finalized_in_source' => ($operator['go_decision_finalized'] ?? true) === true,
            ],
            'controlled_rollout_finalization_guard_summary' => [
                'operator_go_locked' => $this->loadValid($load),
                'controlled_rollout_result_valid' => ($operator['controlled_rollout_result_valid'] ?? false) === true,
                'controlled_rollout_remains_active_for_observation' => ($operator['controlled_rollout_active'] ?? false) === true,
                'kill_switch_confirmed' => ($operator['kill_switch_confirmed'] ?? false) === true,
                'rollback_confirmed' => ($operator['rollback_confirmed'] ?? false) === true,
                'production_config_unchanged' => ($operator['production_config_mutated'] ?? true) === false,
                'free_publication_locked' => ($operator['free_publication_allowed'] ?? true) === false,
            ],
            'watchlist_function_finalization_summary' => [
                'watchlist_function_scope_valid' => $this->functionScopeValid($operator),
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
                'invoked_during_execution' => ($operator['watchlist_function_invoked_during_execution'] ?? false) === true,
                'invoked_by_finalization' => false,
                'function_published_output_in_finalization' => false,
            ],
            'candidate_scope_freeze_summary' => [
                'candidate_scope_valid' => $this->candidateScopeValid($operator),
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'primary_ready_for_post_rollout_observation' => $pass,
                'backup_ready_for_post_rollout_observation' => $pass,
                'comparator_ready_for_post_rollout_observation' => false,
                'a01_remains_comparator_only' => true,
                'candidate_rerank_executed' => false,
                'strategy_retune_executed' => false,
            ],
            'publication_and_rollout_safety_summary' => [
                'controlled_rollout_active_for_next_observation' => ($operator['controlled_rollout_active'] ?? false) === true,
                'finalization_artifact_only' => true,
                'new_rollout_executed' => false,
                'new_plan_confirm_mutation_executed' => false,
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
            'c165_go_decision_finalization_decision' => [
                'finalization_valid' => $pass,
                'operator_decision' => 'GO',
                'go_decision_finalized' => $pass,
                'controlled_rollout_topic_closed' => $pass,
                'c165_topic_complete' => $pass,
                'controlled_rollout_remains_active_for_observation' => ($operator['controlled_rollout_active'] ?? false) === true,
                'free_publication_allowed' => false,
            ],
            'next_post_rollout_observation_decision' => [
                'finalization_valid' => $pass,
                'next_recommendation' => $pass ? self::NEXT_OBSERVATION : 'C165_TARGETED_CONTROLLED_ROLLOUT_FINALIZATION_REPAIR',
                'c165_topic_complete' => $pass,
                'next_topic_code' => $pass ? 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION' : null,
                'c166_may_start' => $pass,
                'post_rollout_observation_required_next' => $pass,
                'free_publication_allowed_next' => false,
                'unrestricted_rollout_allowed_next' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_manifest' => [
                'manifest_created' => $pass,
                'go_decision_finalization_artifact_only' => true,
                'source_operator_path' => $load['path'],
                'source_operator_hash' => $load['actual_hash'],
                'source_operator_file_sha1' => $load['actual_file_sha1'],
                'operator_decision' => 'GO',
                'go_decision_finalized' => $pass,
                'controlled_rollout_topic_closed' => $pass,
                'c165_topic_complete' => $pass,
                'ready_for_post_rollout_observation_review' => $pass,
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'controlled_rollout_active' => ($operator['controlled_rollout_active'] ?? false) === true,
                'new_rollout_executed' => false,
                'official_output_published' => false,
                'free_publication_allowed' => false,
                'unrestricted_publication_allowed' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_checklist' => [
                'operator_artifact_lock_reviewed' => true,
                'operator_go_reviewed' => true,
                'go_decision_finalization_reviewed' => true,
                'controlled_rollout_topic_closure_reviewed' => true,
                'candidate_scope_reviewed' => true,
                'kill_switch_reviewed' => true,
                'rollback_reviewed' => true,
                'production_config_guard_reviewed' => true,
                'free_publication_guard_reviewed' => true,
                'post_rollout_observation_required_next' => $pass,
            ],
            'c165_candidate_plan_confirm_controlled_rollout_go_decision_finalization_scorecard' => $this->candidateScorecard($pass),
            'progress_summary' => [
                'progress_marker' => 'PR-90_C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW',
                'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
                'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW',
                'go_decision_finalized' => $pass,
                'c165_topic_complete' => $pass,
                'next_topic_code' => $pass ? 'C166_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION' : null,
            ],
            'planned_next_summary' => [
                'planned_next_review' => $pass ? self::NEXT_OBSERVATION : 'C165_TARGETED_CONTROLLED_ROLLOUT_FINALIZATION_REPAIR',
                'planned_next_scope' => $pass ? 'new C166 post-rollout observation topic over the active controlled rollout' : 'repair C165 controlled rollout GO finalization evidence',
                'c165_topic_complete' => $pass,
                'c166_is_distinct_post_rollout_observation_topic' => $pass,
            ],
            'diagnostics' => [
                'C165 finalization locks the operator GO artifact and closes the controlled rollout governance topic.',
                'The active controlled rollout remains available for C166 observation; finalization does not run it again.',
                'E02 remains primary, B01 remains backup, and A01 remains comparator-only.',
                'Free publication and unrestricted rollout remain disabled while C166 post-rollout observation becomes the next distinct topic.',
            ],
        ]);
    }

    private function operatorGoValid(array $operator): bool
    {
        foreach ([
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_operator_go_no_go_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_operator_go_no_go_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_operator_go_no_go_review_pass',
            'operator_go_no_go_review_completed', 'operator_decision_recorded', 'operator_go_decision', 'operator_decision_confirmed',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_review',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_manifest_created',
            'c165_result_review_lock_valid', 'c165_controlled_rollout_result_review_valid', 'controlled_rollout_result_valid',
            'rollout_state_result_valid', 'execution_rollout_state_integrity_valid', 'controlled_rollout_executed',
            'controlled_rollout_active', 'controlled_rollout_only', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed', 'kill_switch_confirmed',
            'rollback_confirmed', 'watchlist_function_invoked_during_execution', 'watchlist_function_primary_candidate_observed',
            'watchlist_function_backup_candidate_observed', 'primary_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review',
            'operator_approved', 'result_review_locked_confirmed', 'controlled_rollout_result_confirmed',
            'controlled_rollout_only_confirmed', 'candidate_scope_confirmed', 'operator_kill_switch_confirmed',
            'operator_rollback_confirmed', 'production_config_unchanged_confirmed', 'free_publication_locked_confirmed',
            'a01_remains_comparator_only', 'temporary_negative_artifact_cleanup_confirmed',
        ] as $field) {
            if (($operator[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach ([
            'c165_topic_complete', 'go_decision_finalized', 'operator_no_go_decision', 'operator_hold_decision',
            'controlled_rollout_stopped_no_go', 'controlled_rollout_deferred_hold', 'new_rollout_executed',
            'new_plan_confirm_mutation_executed', 'new_catalog_read_executed', 'production_config_mutated',
            'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed',
            'free_publication_allowed', 'unrestricted_publication_allowed', 'watchlist_function_invoked_by_operator_review',
            'watchlist_function_comparator_candidate_observed', 'comparator_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review',
            'temporary_negative_artifacts_remaining',
        ] as $field) {
            if (($operator[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($operator['run_code'] ?? null) === 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW'
            && ($operator['status'] ?? null) === self::EXPECTED_OPERATOR_STATUS
            && ($operator['reason_code'] ?? null) === self::EXPECTED_OPERATOR_STATUS
            && ($operator['phase_label'] ?? null) === self::EXPECTED_OPERATOR_PHASE
            && ($operator['next_step_recommendation'] ?? null) === self::RUN_CODE
            && ($operator['operator_decision'] ?? null) === 'GO'
            && trim((string) ($operator['operator_decision_reason'] ?? '')) !== ''
            && (int) ($operator['rollout_state_record_count'] ?? 0) === 2
            && $this->candidateScopeValid($operator)
            && $this->functionScopeValid($operator)
            && $this->valueAt($operator, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE
            && $this->valueAt($operator, ['planned_next_summary', 'same_topic_c165_continues']) === true
            && $this->valueAt($operator, ['next_plan_confirm_controlled_rollout_go_decision_finalization_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($operator, ['next_plan_confirm_controlled_rollout_go_decision_finalization_decision', 'go_decision_finalization_required_next']) === true;
    }

    private function candidateScopeValid(array $operator): bool
    {
        return ($operator['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($operator['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($operator['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($operator['a01_remains_comparator_only'] ?? null) === true;
    }

    private function functionScopeValid(array $operator): bool
    {
        return ($operator['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($operator['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && ($operator['watchlist_function_invoked_during_execution'] ?? null) === true
            && ($operator['watchlist_function_invoked_by_operator_review'] ?? null) === false;
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
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'role' => 'primary_controlled_rollout', 'go_decision_finalized' => $pass, 'ready_for_post_rollout_observation' => $pass],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'role' => 'backup_controlled_rollout', 'go_decision_finalized' => $pass, 'ready_for_post_rollout_observation' => $pass],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'role' => 'comparator_only', 'go_decision_finalized' => false, 'ready_for_post_rollout_observation' => false],
        ];
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $pass): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $pass
            ? 'C165_CONTROLLED_ROLLOUT_GO_FINALIZED_TOPIC_CLOSED_C166_POST_ROLLOUT_OBSERVATION_READY_FREE_PUBLICATION_LOCKED'
            : $status;
        $artifact['next_step_recommendation'] = $pass ? self::NEXT_OBSERVATION : 'C165_TARGETED_CONTROLLED_ROLLOUT_FINALIZATION_REPAIR';
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $pass ? 0 : 1,
            'failures' => $pass ? [] : [$status],
            'attribution' => $pass ? 'NONE' : 'C165_FINALIZATION_SOURCE_OR_CONFIRMATION',
        ];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function lockSummary(array $load): array
    {
        return [
            'source' => 'c165_operator_go_no_go',
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
            'expected_c165_operator_hash' => $load['expected_hash'],
            'actual_c165_operator_hash' => $load['actual_hash'],
            'c165_operator_hash_match' => $load['hash_match'],
            'expected_c165_operator_file_sha1' => $load['expected_file_sha1'],
            'actual_c165_operator_file_sha1' => $load['actual_file_sha1'],
            'c165_operator_file_sha1_match' => $load['file_sha1_match'],
            'c165_operator_convert_from_json_pass' => $load['convert_from_json_pass'],
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
