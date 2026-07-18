<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewService
{
    public const RUN_CODE = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-88 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_C165_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution.json';
    public const DEFAULT_EXPECTED_C165_EXECUTION_HASH = '73dc9758d1baad52e7a8e56f6e0058e99b9f71f7';
    public const DEFAULT_EXPECTED_C165_EXECUTION_FILE_SHA1 = '10B76E055119D1A9049F2D9EBA858E1B71A552BE';
    public const DEFAULT_ROLLOUT_STATE_ARTIFACT = 'storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json';
    public const DEFAULT_EXPECTED_ROLLOUT_STATE_HASH = '3a8350955f6a1396f5225af3fddcfa31fa622904';
    public const DEFAULT_EXPECTED_ROLLOUT_STATE_FILE_SHA1 = '4B58D3A17B56136CF02BE1635FB2F16F12831722';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';
    private const EXPECTED_EXECUTION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_PASSED_CONTROLLED_ROLLOUT_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_EXECUTION_PHASE = 'PR-87 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION';
    private const EXPECTED_STATE_TYPE = 'weekly_swing_watchlist_plan_confirm_controlled_rollout_state';
    private const EXPECTED_STATE_SOURCE = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION';
    private const NEXT_OPERATOR_REVIEW = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXECUTION_LOCK_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_C165_EXECUTION_ARTIFACT_LOCK_MISMATCH';
    private const EXECUTION_SHA_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_C165_EXECUTION_FILE_SHA1_LOCK_MISMATCH';
    private const EXECUTION_JSON_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_C165_EXECUTION_JSON_COMPATIBILITY_VIOLATION';
    private const EXECUTION_STATE_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_C165_EXECUTION_RESULT_INVALID';
    private const ROLLOUT_STATE_LOCK_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_ROLLOUT_STATE_ARTIFACT_LOCK_MISMATCH';
    private const ROLLOUT_STATE_SHA_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_ROLLOUT_STATE_FILE_SHA1_LOCK_MISMATCH';
    private const ROLLOUT_STATE_JSON_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_ROLLOUT_STATE_JSON_COMPATIBILITY_VIOLATION';
    private const ROLLOUT_STATE_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_ROLLOUT_STATE_RESULT_INVALID';
    private const CROSS_ARTIFACT_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_EXECUTION_ROLLOUT_STATE_INTEGRITY_MISMATCH';
    private const APPROVAL_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const CONFIRMATION_STATUSES = [
        'result_review_confirmed' => 'RESULT_REVIEW_CONFIRMATION_MISSING',
        'controlled_rollout_execution_result_confirmed' => 'CONTROLLED_ROLLOUT_EXECUTION_RESULT_CONFIRMATION_MISSING',
        'rollout_state_locked_confirmed' => 'ROLLOUT_STATE_LOCK_CONFIRMATION_MISSING',
        'controlled_rollout_only_confirmed' => 'CONTROLLED_ROLLOUT_ONLY_CONFIRMATION_MISSING',
        'candidate_scope_confirmed' => 'CANDIDATE_SCOPE_CONFIRMATION_MISSING',
        'kill_switch_confirmed' => 'KILL_SWITCH_CONFIRMATION_MISSING',
        'rollback_confirmed' => 'ROLLBACK_CONFIRMATION_MISSING',
        'production_config_unchanged_confirmed' => 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING',
        'free_publication_locked_confirmed' => 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c165-*controlled-rollout-result-review*-negative-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-result-review*-missing-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-result-review*-mismatch-*-test.json',
    ];

    public function execute(
        string $c165ExecutionArtifact = self::DEFAULT_C165_EXECUTION_ARTIFACT,
        string $expectedC165ExecutionHash = self::DEFAULT_EXPECTED_C165_EXECUTION_HASH,
        string $expectedC165ExecutionFileSha1 = self::DEFAULT_EXPECTED_C165_EXECUTION_FILE_SHA1,
        string $rolloutStateArtifact = self::DEFAULT_ROLLOUT_STATE_ARTIFACT,
        string $expectedRolloutStateHash = self::DEFAULT_EXPECTED_ROLLOUT_STATE_HASH,
        string $expectedRolloutStateFileSha1 = self::DEFAULT_EXPECTED_ROLLOUT_STATE_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $executionLoad = $this->loadJsonLock($c165ExecutionArtifact, $expectedC165ExecutionHash, $expectedC165ExecutionFileSha1, 'artifact_hash');
        $stateLoad = $this->loadJsonLock($rolloutStateArtifact, $expectedRolloutStateHash, $expectedRolloutStateFileSha1, 'rollout_state_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($executionLoad, $stateLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($executionLoad, $stateLoad));

        foreach ([
            [$executionLoad, self::EXECUTION_LOCK_STATUS, self::EXECUTION_SHA_STATUS, self::EXECUTION_JSON_STATUS],
            [$stateLoad, self::ROLLOUT_STATE_LOCK_STATUS, self::ROLLOUT_STATE_SHA_STATUS, self::ROLLOUT_STATE_JSON_STATUS],
        ] as [$load, $hashStatus, $shaStatus, $jsonStatus]) {
            if (! $load['exists'] || ! is_array($load['payload']) || ! $load['hash_match']) {
                return $this->finish($this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false), $hashStatus, 'Required C165 result-review source artifact is missing, unreadable, or hash-mismatched.', $outputPath, $overwrite, false);
            }
            if (! $load['file_sha1_match']) {
                return $this->finish($this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false), $shaStatus, 'Required C165 result-review source file SHA1 mismatched.', $outputPath, $overwrite, false);
            }
            if (! $load['convert_from_json_pass']) {
                return $this->finish($this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false), $jsonStatus, 'Required C165 result-review source is not ConvertFrom-Json compatible.', $outputPath, $overwrite, false);
            }
        }

        $execution = $executionLoad['payload'];
        $state = $stateLoad['payload'];
        if (! $this->executionResultValid($execution)) {
            return $this->finish($this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false), self::EXECUTION_STATE_STATUS, 'C165 execution artifact does not represent a valid controlled rollout result.', $outputPath, $overwrite, false);
        }
        if (! $this->rolloutStateValid($state)) {
            return $this->finish($this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false), self::ROLLOUT_STATE_STATUS, 'C165 rollout state does not satisfy controlled result-review invariants.', $outputPath, $overwrite, false);
        }
        if (! $this->crossArtifactIntegrityValid($execution, $state, $stateLoad)) {
            return $this->finish($this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false), self::CROSS_ARTIFACT_STATUS, 'C165 execution and rollout state do not describe the same controlled rollout.', $outputPath, $overwrite, false);
        }

        if (($options['operator_approved'] ?? false) !== true || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false), self::APPROVAL_STATUS, 'Explicit operator approval and approval reference are required for C165 result review.', $outputPath, $overwrite, false);
        }
        foreach (self::CONFIRMATION_STATUSES as $option => $suffix) {
            if (($options[$option] ?? false) !== true) {
                $status = self::RUN_CODE.'_REJECTED_'.$suffix;

                return $this->finish($this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false), $status, 'Required C165 controlled rollout result-review confirmation is missing: '.$option.'.', $outputPath, $overwrite, false);
            }
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $artifact = $this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, false);
            $artifact['temporary_negative_artifact_paths'] = $temporaryPaths;
            $artifact['temporary_negative_artifacts_remaining'] = true;

            return $this->finish($artifact, self::TEMPORARY_STATUS, 'Temporary C165 negative result-review artifacts remain.', $outputPath, $overwrite, false);
        }

        return $this->finish(
            $this->completeArtifact($artifact, $executionLoad, $stateLoad, $options, true),
            self::PASS_STATUS,
            'C165 controlled rollout results are valid for E02 primary and B01 backup; operator GO/NO-GO review may proceed while free publication stays locked.',
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
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW',
            'c165_topic_complete' => false,
            'result_review_artifact_only' => true,
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

    private function completeArtifact(array $artifact, array $executionLoad, array $stateLoad, array $options, bool $pass): array
    {
        $execution = is_array($executionLoad['payload']) ? $executionLoad['payload'] : [];
        $state = is_array($stateLoad['payload']) ? $stateLoad['payload'] : [];
        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        $executionValid = $this->executionResultValid($execution);
        $stateValid = $this->rolloutStateValid($state);
        $integrityValid = $this->crossArtifactIntegrityValid($execution, $state, $stateLoad);
        $sourceLocksValid = $this->loadValid($executionLoad) && $this->loadValid($stateLoad);

        return array_merge($artifact, [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_result_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_result_review_pass' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_result_review_pass' => $pass,
            'controlled_rollout_result_reviewed' => $pass,
            'controlled_rollout_result_valid' => $pass && $executionValid,
            'rollout_state_result_valid' => $pass && $stateValid,
            'execution_rollout_state_integrity_valid' => $pass && $integrityValid,
            'c165_execution_lock_valid' => $this->loadValid($executionLoad),
            'c165_execution_result_valid' => $executionValid,
            'rollout_state_lock_valid' => $this->loadValid($stateLoad),
            'rollout_state_integrity_valid' => $stateValid && $integrityValid,
            'all_required_source_locks_valid' => $sourceLocksValid,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_review' => $pass,
            'production_live_runtime_plan_confirm_controlled_rollout_operator_go_no_go_review_allowed_next' => $pass,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_review_allowed_next' => $pass,
            'operator_go_no_go_review_required_next' => $pass,
            'c165_topic_number_retained_for_operator_go_no_go_review' => true,
            'controlled_rollout_executed' => ($state['live_plan_confirm_rollout_executed'] ?? false) === true,
            'controlled_rollout_active' => ($state['controlled_rollout_active'] ?? false) === true,
            'controlled_rollout_only' => ($state['controlled_rollout_only'] ?? false) === true,
            'plan_confirm_mutated' => ($state['plan_confirm_mutated'] ?? false) === true,
            'plan_confirm_runtime_reads_activated_catalog' => ($state['plan_confirm_runtime_reads_activated_catalog'] ?? false) === true,
            'live_plan_confirm_rollout_executed' => ($state['live_plan_confirm_rollout_executed'] ?? false) === true,
            'unrestricted_rollout_allowed' => ($state['unrestricted_rollout_allowed'] ?? true) === true,
            'kill_switch_confirmed' => ($state['kill_switch_confirmed'] ?? false) === true,
            'rollback_confirmed' => ($state['rollback_confirmed'] ?? false) === true,
            'rollout_state_record_count' => count((array) ($state['rollout_rows'] ?? [])),
            'watchlist_function_invoked_during_execution' => ($execution['watchlist_function_invoked'] ?? false) === true,
            'watchlist_function_invoked_by_result_review' => false,
            'watchlist_function_primary_candidate_observed' => $pass,
            'watchlist_function_backup_candidate_observed' => $pass,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_controlled_rollout_result_reviewed' => $pass,
            'backup_candidate_controlled_rollout_result_reviewed' => $pass,
            'comparator_candidate_controlled_rollout_result_reviewed' => false,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review_manifest_created' => $pass,
            'weekly_swing_watchlist_controlled_publication_allowed' => ($execution['weekly_swing_watchlist_controlled_publication_allowed'] ?? false) === true,
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'result_review_confirmed' => ($options['result_review_confirmed'] ?? false) === true,
            'controlled_rollout_execution_result_confirmed' => ($options['controlled_rollout_execution_result_confirmed'] ?? false) === true,
            'rollout_state_locked_confirmed' => ($options['rollout_state_locked_confirmed'] ?? false) === true,
            'controlled_rollout_only_confirmed' => ($options['controlled_rollout_only_confirmed'] ?? false) === true,
            'candidate_scope_confirmed' => ($options['candidate_scope_confirmed'] ?? false) === true,
            'production_config_unchanged_confirmed' => ($options['production_config_unchanged_confirmed'] ?? false) === true,
            'free_publication_locked_confirmed' => ($options['free_publication_locked_confirmed'] ?? false) === true,
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'source_lock_validation_summary' => [
                'c165_execution_lock_valid' => $this->loadValid($executionLoad),
                'rollout_state_lock_valid' => $this->loadValid($stateLoad),
                'all_required_source_locks_valid' => $sourceLocksValid,
            ],
            'c165_execution_result_summary' => [
                'execution_result_valid' => $executionValid,
                'execution_status' => $execution['status'] ?? null,
                'execution_phase_label' => $execution['phase_label'] ?? null,
                'execution_next_recommendation' => $execution['next_step_recommendation'] ?? null,
                'controlled_rollout_executed' => ($execution['controlled_rollout_executed'] ?? false) === true,
                'new_execution_triggered_by_review' => false,
            ],
            'rollout_state_result_summary' => [
                'rollout_state_result_valid' => $stateValid,
                'rollout_state_type' => $state['rollout_state_type'] ?? null,
                'rollout_state_hash' => $state['rollout_state_hash'] ?? null,
                'rollout_state_record_count' => count((array) ($state['rollout_rows'] ?? [])),
                'controlled_rollout_scope' => $state['controlled_rollout_scope'] ?? null,
                'controlled_rollout_only' => ($state['controlled_rollout_only'] ?? false) === true,
            ],
            'execution_rollout_state_integrity_summary' => [
                'integrity_valid' => $integrityValid,
                'execution_rollout_state_hash' => $execution['rollout_state_hash'] ?? null,
                'locked_rollout_state_hash' => $stateLoad['actual_hash'],
                'candidate_scope_matches' => $this->candidateScopeValid($state),
                'function_scope_matches' => $this->functionScopeValid($execution, $state),
            ],
            'watchlist_function_result_review_summary' => [
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
                'watchlist_function_invoked_during_execution' => ($execution['watchlist_function_invoked'] ?? false) === true,
                'watchlist_function_invoked_by_result_review' => false,
                'primary_candidate_observed' => $pass,
                'backup_candidate_observed' => $pass,
                'comparator_candidate_observed' => false,
            ],
            'candidate_scope_result_review_summary' => [
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'primary_result_reviewed' => $pass,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'backup_result_reviewed' => $pass,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'comparator_result_reviewed_as_rollout' => false,
                'a01_remains_comparator_only' => true,
                'candidate_rerank_executed' => false,
                'strategy_retune_executed' => false,
            ],
            'publication_and_rollout_safety_summary' => [
                'controlled_rollout_result_observed' => $pass,
                'review_artifact_only' => true,
                'new_rollout_executed' => false,
                'production_config_mutated' => false,
                'unrestricted_rollout_allowed' => false,
                'free_publication_allowed' => false,
                'official_output_published' => false,
                'kill_switch_confirmed' => ($state['kill_switch_confirmed'] ?? false) === true,
                'rollback_confirmed' => ($state['rollback_confirmed'] ?? false) === true,
            ],
            'operator_confirmation_summary' => $this->operatorConfirmationSummary($options),
            'temporary_negative_artifact_guard_summary' => [
                'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
                'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
                'temporary_negative_artifact_paths' => $temporaryPaths,
                'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
            ],
            'next_plan_confirm_controlled_rollout_operator_go_no_go_decision' => [
                'result_review_valid' => $pass,
                'next_recommendation' => $pass ? self::NEXT_OPERATOR_REVIEW : 'C165_TARGETED_CONTROLLED_ROLLOUT_RESULT_REVIEW_REPAIR',
                'same_topic_c165_continues' => true,
                'operator_go_no_go_review_required_next' => $pass,
                'operator_go_no_go_review_requires_locked_result_review_artifact' => $pass,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review_manifest' => [
                'manifest_created' => $pass,
                'result_review_artifact_only' => true,
                'execution_artifact_locked' => $this->loadValid($executionLoad),
                'rollout_state_locked' => $this->loadValid($stateLoad),
                'controlled_rollout_result_valid' => $pass && $executionValid && $stateValid && $integrityValid,
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'free_publication_executed' => false,
                'unrestricted_publication_allowed' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review_checklist' => [
                'execution_lock_reviewed' => true,
                'rollout_state_lock_reviewed' => true,
                'execution_result_reviewed' => true,
                'rollout_state_result_reviewed' => true,
                'candidate_scope_reviewed' => true,
                'watchlist_function_scope_reviewed' => true,
                'kill_switch_reviewed' => true,
                'rollback_reviewed' => true,
                'production_config_guard_reviewed' => true,
                'free_publication_guard_reviewed' => true,
                'operator_go_no_go_review_required_next' => $pass,
            ],
            'c165_candidate_plan_confirm_controlled_rollout_result_review_scorecard' => $this->candidateScorecard($pass),
            'progress_summary' => [
                'progress_marker' => 'PR-88_C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW',
                'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
                'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW',
                'c165_topic_complete' => false,
                'result_review_completed' => $pass,
                'operator_go_no_go_review_required_next' => $pass,
            ],
            'planned_next_summary' => [
                'planned_next_review' => $pass ? self::NEXT_OPERATOR_REVIEW : 'C165_TARGETED_CONTROLLED_ROLLOUT_RESULT_REVIEW_REPAIR',
                'planned_next_scope' => $pass ? 'same-topic C165 operator GO/NO-GO review over the locked controlled rollout result-review artifact' : 'repair failed C165 controlled rollout result-review evidence',
                'same_topic_c165_continues' => true,
            ],
            'diagnostics' => [
                'C165 result review reads the locked execution and rollout-state artifacts without running rollout again.',
                'The observed controlled PLAN/CONFIRM mutation, activated-catalog read, and rollout remain limited to E02 primary and B01 backup.',
                'Production config, free publication, unrestricted publication, candidate ranking, and strategy parameters remain unchanged by this review.',
                'A passing result proceeds inside C165 to operator GO/NO-GO review.',
            ],
        ]);
    }

    private function executionResultValid(array $execution): bool
    {
        foreach (['controlled_rollout_executed', 'controlled_rollout_active', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed', 'kill_switch_confirmed', 'rollback_confirmed', 'watchlist_function_invoked', 'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed', 'a01_remains_comparator_only'] as $field) {
            if (($execution[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (['production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed', 'watchlist_function_comparator_candidate_observed'] as $field) {
            if (($execution[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($execution['run_code'] ?? null) === self::EXPECTED_STATE_SOURCE
            && ($execution['status'] ?? null) === self::EXPECTED_EXECUTION_STATUS
            && ($execution['reason_code'] ?? null) === self::EXPECTED_EXECUTION_STATUS
            && ($execution['phase_label'] ?? null) === self::EXPECTED_EXECUTION_PHASE
            && ($execution['next_step_recommendation'] ?? null) === self::RUN_CODE
            && ($execution['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($execution['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && ($execution['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($execution['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($execution['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($execution['rollout_state_record_count'] ?? null) === 2;
    }

    private function rolloutStateValid(array $state): bool
    {
        foreach (['controlled_rollout_only', 'controlled_rollout_active', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'kill_switch_confirmed', 'rollback_confirmed', 'result_review_required_next'] as $field) {
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
            && ($state['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($state['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && $this->candidateScopeValid($state);
    }

    private function candidateScopeValid(array $state): bool
    {
        $rows = $state['rollout_rows'] ?? null;
        if (! is_array($rows) || count($rows) !== 2) {
            return false;
        }

        return $this->rolloutRowValid($rows[0] ?? null, 1, self::PRIMARY_CANDIDATE, 'primary')
            && $this->rolloutRowValid($rows[1] ?? null, 2, self::BACKUP_CANDIDATE, 'backup')
            && $this->valueAt($state, ['comparator_candidate', 'candidate_code']) === self::COMPARATOR_CANDIDATE
            && $this->valueAt($state, ['comparator_candidate', 'role']) === 'comparator_only'
            && $this->valueAt($state, ['comparator_candidate', 'controlled_rollout_executed']) === false
            && $this->valueAt($state, ['comparator_candidate', 'a01_remains_comparator_only']) === true;
    }

    private function rolloutRowValid($row, int $rank, string $candidate, string $role): bool
    {
        return is_array($row)
            && ($row['rank'] ?? null) === $rank
            && ($row['candidate_code'] ?? null) === $candidate
            && ($row['role'] ?? null) === $role
            && ($row['catalog_read_state'] ?? null) === 'controlled_enabled'
            && ($row['plan_confirm_state'] ?? null) === 'controlled_rollout_active'
            && ($row['rollout_state'] ?? null) === 'controlled_executed'
            && ($row['publication_state'] ?? null) === 'not_free_published';
    }

    private function functionScopeValid(array $execution, array $state): bool
    {
        return ($execution['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($execution['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && ($state['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($state['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE;
    }

    private function crossArtifactIntegrityValid(array $execution, array $state, array $stateLoad): bool
    {
        return ($execution['rollout_state_hash'] ?? null) === ($state['rollout_state_hash'] ?? null)
            && ($state['rollout_state_hash'] ?? null) === $stateLoad['actual_hash']
            && ($execution['rollout_state_record_count'] ?? null) === count((array) ($state['rollout_rows'] ?? []))
            && ($execution['approval_reference'] ?? null) === ($state['approval_reference'] ?? null)
            && $this->candidateScopeValid($state)
            && $this->functionScopeValid($execution, $state);
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
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'role' => 'primary_controlled_rollout', 'result_reviewed' => $pass, 'ready_for_operator_go_no_go_review' => $pass],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'role' => 'backup_controlled_rollout', 'result_reviewed' => $pass, 'ready_for_operator_go_no_go_review' => $pass],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'role' => 'comparator_only', 'result_reviewed' => false, 'ready_for_operator_go_no_go_review' => false],
        ];
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $pass): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $pass
            ? 'C165_CONTROLLED_ROLLOUT_RESULT_VALID_READY_FOR_SAME_TOPIC_OPERATOR_GO_NO_GO_REVIEW_FREE_PUBLICATION_LOCKED'
            : $status;
        $artifact['next_step_recommendation'] = $pass ? self::NEXT_OPERATOR_REVIEW : 'C165_TARGETED_CONTROLLED_ROLLOUT_RESULT_REVIEW_REPAIR';
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $pass ? 0 : 1,
            'failures' => $pass ? [] : [$status],
            'attribution' => $pass ? 'NONE' : 'C165_RESULT_REVIEW_SOURCE_OR_CONFIRMATION',
        ];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function sourceArtifactLocks(array $executionLoad, array $stateLoad): array
    {
        return [
            $this->lockSummary('c165_execution', $executionLoad),
            $this->lockSummary('rollout_state', $stateLoad),
        ];
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

    private function topLevelLockAliases(array $executionLoad, array $stateLoad): array
    {
        return [
            'expected_c165_execution_hash' => $executionLoad['expected_hash'],
            'actual_c165_execution_hash' => $executionLoad['actual_hash'],
            'c165_execution_hash_match' => $executionLoad['hash_match'],
            'expected_c165_execution_file_sha1' => $executionLoad['expected_file_sha1'],
            'actual_c165_execution_file_sha1' => $executionLoad['actual_file_sha1'],
            'c165_execution_file_sha1_match' => $executionLoad['file_sha1_match'],
            'c165_execution_convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            'expected_rollout_state_hash' => $stateLoad['expected_hash'],
            'actual_rollout_state_hash' => $stateLoad['actual_hash'],
            'rollout_state_hash_match' => $stateLoad['hash_match'],
            'expected_rollout_state_file_sha1' => $stateLoad['expected_file_sha1'],
            'actual_rollout_state_file_sha1' => $stateLoad['actual_file_sha1'],
            'rollout_state_file_sha1_match' => $stateLoad['file_sha1_match'],
            'rollout_state_convert_from_json_pass' => $stateLoad['convert_from_json_pass'],
        ];
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1, string $hashField): array
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
                $actualHash = $decoded[$hashField] ?? null;
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
