<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-89 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_C165_RESULT_REVIEW_ARTIFACT = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-result-review.json';
    public const DEFAULT_EXPECTED_C165_RESULT_REVIEW_HASH = 'a30b5b0eeab344e0d0283cb4164fd2a27b234802';
    public const DEFAULT_EXPECTED_C165_RESULT_REVIEW_FILE_SHA1 = '664A639A2C8338F407BB0B34B9648733A0F6C94E';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';
    private const EXPECTED_RESULT_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_RESULT_PHASE = 'PR-88 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW';
    private const NEXT_FINALIZATION = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW';

    private const GO_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_CONTROLLED_ROLLOUT_PROGRESSION_STOPPED';
    private const HOLD_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_CONTROLLED_ROLLOUT_PROGRESSION_DEFERRED';
    private const RESULT_LOCK_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C165_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH';
    private const RESULT_SHA_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C165_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH';
    private const RESULT_JSON_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C165_RESULT_REVIEW_JSON_COMPATIBILITY_VIOLATION';
    private const RESULT_STATE_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C165_RESULT_REVIEW_INCOMPLETE';
    private const APPROVAL_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_CONFIRMATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMPORARY_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const CONFIRMATION_STATUSES = [
        'result_review_locked_confirmed' => 'RESULT_REVIEW_LOCK_CONFIRMATION_MISSING',
        'controlled_rollout_result_confirmed' => 'CONTROLLED_ROLLOUT_RESULT_CONFIRMATION_MISSING',
        'controlled_rollout_only_confirmed' => 'CONTROLLED_ROLLOUT_ONLY_CONFIRMATION_MISSING',
        'candidate_scope_confirmed' => 'CANDIDATE_SCOPE_CONFIRMATION_MISSING',
        'kill_switch_confirmed' => 'KILL_SWITCH_CONFIRMATION_MISSING',
        'rollback_confirmed' => 'ROLLBACK_CONFIRMATION_MISSING',
        'production_config_unchanged_confirmed' => 'PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING',
        'free_publication_locked_confirmed' => 'FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c165-*controlled-rollout-operator-go-no-go*-negative-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-operator-go-no-go*-missing-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-operator-go-no-go*-mismatch-*-test.json',
    ];

    public function execute(
        string $c165ResultReviewArtifact = self::DEFAULT_C165_RESULT_REVIEW_ARTIFACT,
        string $expectedC165ResultReviewHash = self::DEFAULT_EXPECTED_C165_RESULT_REVIEW_HASH,
        string $expectedC165ResultReviewFileSha1 = self::DEFAULT_EXPECTED_C165_RESULT_REVIEW_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $decision = $this->normalizeDecision((string) ($options['operator_decision'] ?? ''));
        $reason = trim((string) ($options['decision_reason'] ?? ''));
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($c165ResultReviewArtifact, $expectedC165ResultReviewHash, $expectedC165ResultReviewFileSha1);
        $artifact['source_artifact_locks'] = [$this->lockSummary($load)];
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload']) || ! $load['hash_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_LOCK_STATUS, 'C165 controlled rollout result-review artifact is missing, unreadable, or hash-mismatched.', $outputPath, $overwrite, false, $decision);
        }
        if (! $load['file_sha1_match']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_SHA_STATUS, 'C165 controlled rollout result-review file SHA1 mismatched.', $outputPath, $overwrite, false, $decision);
        }
        if (! $load['convert_from_json_pass']) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_JSON_STATUS, 'C165 controlled rollout result-review artifact is not ConvertFrom-Json compatible.', $outputPath, $overwrite, false, $decision);
        }

        $resultReview = $load['payload'];
        if (! $this->resultReviewValid($resultReview)) {
            return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), self::RESULT_STATE_STATUS, 'C165 result-review evidence is incomplete or unsafe for an operator decision.', $outputPath, $overwrite, false, $decision);
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
                $status = self::RUN_CODE.'_REJECTED_'.$suffix;

                return $this->finish($this->completeArtifact($artifact, $load, $options, false, $decision, $reason), $status, 'Required C165 operator confirmation is missing: '.$option.'.', $outputPath, $overwrite, false, $decision);
            }
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $artifact = $this->completeArtifact($artifact, $load, $options, false, $decision, $reason);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_paths'] = $temporaryPaths;

            return $this->finish($artifact, self::TEMPORARY_STATUS, 'Temporary C165 operator negative artifacts remain.', $outputPath, $overwrite, false, $decision);
        }

        $status = $decision === 'GO' ? self::GO_STATUS : ($decision === 'NO_GO' ? self::NO_GO_STATUS : self::HOLD_STATUS);
        $message = $decision === 'GO'
            ? 'Operator decision is GO for E02 primary and B01 backup; same-topic C165 GO decision finalization may proceed.'
            : ($decision === 'NO_GO' ? 'Operator decision is NO_GO; C165 controlled rollout progression is stopped.' : 'Operator decision is HOLD; C165 controlled rollout progression is deferred.');

        return $this->finish($this->completeArtifact($artifact, $load, $options, true, $decision, $reason), $status, $message, $outputPath, $overwrite, true, $decision);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'artifact_type' => self::ARTIFACT_TYPE,
            'created_at' => $createdAt,
            'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW',
            'c165_topic_complete' => false,
            'operator_go_no_go_review_artifact_only' => true,
            'go_decision_finalized' => false,
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

    private function completeArtifact(array $artifact, array $load, array $options, bool $completed, ?string $decision, string $reason): array
    {
        $source = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $sourceValid = $this->resultReviewValid($source);
        $go = $completed && $decision === 'GO';
        $temporaryPaths = $this->temporaryNegativeArtifactPaths();

        return array_merge($artifact, [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_operator_go_no_go_review_executed' => $completed,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_operator_go_no_go_review_allowed' => $completed,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_operator_go_no_go_review_pass' => $go,
            'production_live_runtime_plan_confirm_controlled_rollout_operator_go_no_go_review_pass' => $go,
            'operator_go_no_go_review_completed' => $completed,
            'operator_decision_recorded' => $completed,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_go_decision' => $go,
            'operator_no_go_decision' => $completed && $decision === 'NO_GO',
            'operator_hold_decision' => $completed && $decision === 'HOLD',
            'operator_decision_confirmed' => ($options['operator_decision_confirmed'] ?? false) === true,
            'operator_decision_reason' => $reason,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_review' => $go,
            'production_live_runtime_plan_confirm_controlled_rollout_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_manifest_created' => $completed,
            'controlled_rollout_stopped_no_go' => $completed && $decision === 'NO_GO',
            'controlled_rollout_deferred_hold' => $completed && $decision === 'HOLD',
            'c165_topic_number_retained_for_go_decision_finalization' => true,
            'c165_result_review_lock_valid' => $this->loadValid($load),
            'c165_controlled_rollout_result_review_valid' => $sourceValid,
            'controlled_rollout_result_valid' => ($source['controlled_rollout_result_valid'] ?? false) === true,
            'rollout_state_result_valid' => ($source['rollout_state_result_valid'] ?? false) === true,
            'execution_rollout_state_integrity_valid' => ($source['execution_rollout_state_integrity_valid'] ?? false) === true,
            'controlled_rollout_executed' => ($source['controlled_rollout_executed'] ?? false) === true,
            'controlled_rollout_active' => ($source['controlled_rollout_active'] ?? false) === true,
            'controlled_rollout_only' => ($source['controlled_rollout_only'] ?? false) === true,
            'plan_confirm_mutated' => ($source['plan_confirm_mutated'] ?? false) === true,
            'plan_confirm_runtime_reads_activated_catalog' => ($source['plan_confirm_runtime_reads_activated_catalog'] ?? false) === true,
            'live_plan_confirm_rollout_executed' => ($source['live_plan_confirm_rollout_executed'] ?? false) === true,
            'unrestricted_rollout_allowed' => false,
            'kill_switch_confirmed' => ($source['kill_switch_confirmed'] ?? false) === true,
            'rollback_confirmed' => ($source['rollback_confirmed'] ?? false) === true,
            'rollout_state_record_count' => (int) ($source['rollout_state_record_count'] ?? 0),
            'watchlist_function_invoked_during_execution' => ($source['watchlist_function_invoked_during_execution'] ?? false) === true,
            'watchlist_function_invoked_by_operator_review' => false,
            'watchlist_function_primary_candidate_observed' => ($source['watchlist_function_primary_candidate_observed'] ?? false) === true,
            'watchlist_function_backup_candidate_observed' => ($source['watchlist_function_backup_candidate_observed'] ?? false) === true,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review' => false,
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'result_review_locked_confirmed' => ($options['result_review_locked_confirmed'] ?? false) === true,
            'controlled_rollout_result_confirmed' => ($options['controlled_rollout_result_confirmed'] ?? false) === true,
            'controlled_rollout_only_confirmed' => ($options['controlled_rollout_only_confirmed'] ?? false) === true,
            'candidate_scope_confirmed' => ($options['candidate_scope_confirmed'] ?? false) === true,
            'operator_kill_switch_confirmed' => ($options['kill_switch_confirmed'] ?? false) === true,
            'operator_rollback_confirmed' => ($options['rollback_confirmed'] ?? false) === true,
            'production_config_unchanged_confirmed' => ($options['production_config_unchanged_confirmed'] ?? false) === true,
            'free_publication_locked_confirmed' => ($options['free_publication_locked_confirmed'] ?? false) === true,
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'c165_result_review_lock_validation_summary' => [
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
            'c165_controlled_rollout_result_review_carry_forward_summary' => [
                'result_review_valid' => $sourceValid,
                'source_status' => $source['status'] ?? null,
                'source_phase_label' => $source['phase_label'] ?? null,
                'source_next_recommendation' => $source['next_step_recommendation'] ?? null,
                'controlled_rollout_result_valid' => ($source['controlled_rollout_result_valid'] ?? false) === true,
                'rollout_state_record_count' => (int) ($source['rollout_state_record_count'] ?? 0),
            ],
            'watchlist_function_operator_review_summary' => [
                'watchlist_function_scope_valid' => $this->functionScopeValid($source),
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'watchlist_function_runtime_mode' => self::RUNTIME_MODE,
                'invoked_during_execution' => ($source['watchlist_function_invoked_during_execution'] ?? false) === true,
                'invoked_by_operator_review' => false,
                'function_published_output_in_operator_review' => false,
            ],
            'candidate_scope_freeze_summary' => [
                'candidate_scope_valid' => $this->candidateScopeValid($source),
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'primary_ready_for_go_decision_finalization' => $go,
                'backup_ready_for_go_decision_finalization' => $go,
                'comparator_ready_for_go_decision_finalization' => false,
                'a01_remains_comparator_only' => true,
                'candidate_rerank_executed' => false,
                'strategy_retune_executed' => false,
            ],
            'publication_and_rollout_safety_summary' => [
                'source_controlled_rollout_result_observed' => $sourceValid,
                'operator_review_artifact_only' => true,
                'new_rollout_executed' => false,
                'new_plan_confirm_mutation_executed' => false,
                'production_config_mutated' => false,
                'unrestricted_rollout_allowed' => false,
                'free_publication_allowed' => false,
                'official_output_published' => false,
                'kill_switch_confirmed' => ($source['kill_switch_confirmed'] ?? false) === true,
                'rollback_confirmed' => ($source['rollback_confirmed'] ?? false) === true,
            ],
            'operator_decision_validation_summary' => $this->operatorDecisionSummary($options, $decision, $reason, $completed),
            'temporary_negative_artifact_guard_summary' => [
                'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
                'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
                'temporary_negative_artifact_paths' => $temporaryPaths,
                'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
            ],
            'c165_controlled_rollout_operator_go_no_go_decision' => [
                'review_valid' => $completed,
                'operator_decision' => $decision ?? 'INVALID',
                'decision_reason' => $reason,
                'operator_go_decision' => $go,
                'operator_no_go_decision' => $completed && $decision === 'NO_GO',
                'operator_hold_decision' => $completed && $decision === 'HOLD',
                'result_review_artifact_locked' => $this->loadValid($load),
                'same_topic_c165_continues' => true,
                'go_decision_finalized' => false,
                'free_publication_allowed' => false,
            ],
            'next_plan_confirm_controlled_rollout_go_decision_finalization_decision' => [
                'review_valid' => $completed,
                'next_recommendation' => $this->nextRecommendation($completed, $decision),
                'same_topic_c165_continues' => true,
                'go_decision_finalization_required_next' => $go,
                'topic_number_must_not_advance_until_c165_finalization' => true,
                'free_publication_allowed_next' => false,
                'unrestricted_rollout_allowed_next' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_manifest' => [
                'manifest_created' => $completed,
                'operator_go_no_go_artifact_only' => true,
                'source_result_review_path' => $load['path'],
                'source_result_review_hash' => $load['actual_hash'],
                'source_result_review_file_sha1' => $load['actual_file_sha1'],
                'operator_decision' => $decision ?? 'INVALID',
                'operator_decision_reason' => $reason,
                'ready_for_go_decision_finalization_review' => $go,
                'watchlist_function_used' => self::WATCHLIST_FUNCTION,
                'primary_candidate_code' => self::PRIMARY_CANDIDATE,
                'backup_candidate_code' => self::BACKUP_CANDIDATE,
                'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
                'controlled_rollout_result_valid' => $sourceValid,
                'new_rollout_executed' => false,
                'official_output_published' => false,
                'free_publication_allowed' => false,
                'unrestricted_publication_allowed' => false,
                'go_decision_finalized' => false,
            ],
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_checklist' => [
                'result_review_lock_reviewed' => true,
                'result_review_state_reviewed' => true,
                'operator_approval_reviewed' => true,
                'operator_decision_reviewed' => true,
                'candidate_scope_reviewed' => true,
                'kill_switch_reviewed' => true,
                'rollback_reviewed' => true,
                'production_config_guard_reviewed' => true,
                'free_publication_guard_reviewed' => true,
                'go_decision_finalization_required_next' => $go,
            ],
            'c165_candidate_plan_confirm_controlled_rollout_operator_go_no_go_scorecard' => $this->candidateScorecard($completed, $decision),
            'progress_summary' => [
                'progress_marker' => 'PR-89_C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW',
                'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
                'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW',
                'c165_topic_complete' => false,
                'operator_review_completed' => $completed,
                'operator_decision' => $decision ?? 'INVALID',
                'go_decision_finalization_required_next' => $go,
            ],
            'planned_next_summary' => [
                'planned_next_review' => $this->nextRecommendation($completed, $decision),
                'planned_next_scope' => $go ? 'same-topic C165 GO decision finalization review over the locked operator artifact' : 'same-topic C165 operator decision stop or hold handling',
                'same_topic_c165_continues' => true,
                'topic_number_must_not_advance_until_c165_finalization' => true,
            ],
            'diagnostics' => [
                'C165 operator review locks the result-review artifact and records GO, NO_GO, or HOLD.',
                'The operator review does not invoke the watchlist function, execute another rollout, or mutate production configuration.',
                'E02 remains primary, B01 remains backup, and A01 remains comparator-only.',
                'A GO decision requires same-topic C165 GO decision finalization before the topic can close.',
            ],
        ]);
    }

    private function resultReviewValid(array $source): bool
    {
        foreach ([
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_result_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_result_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_result_review_pass',
            'controlled_rollout_result_reviewed', 'controlled_rollout_result_valid', 'rollout_state_result_valid',
            'execution_rollout_state_integrity_valid', 'c165_execution_lock_valid', 'rollout_state_lock_valid',
            'all_required_source_locks_valid', 'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_review',
            'operator_go_no_go_review_required_next', 'c165_topic_number_retained_for_operator_go_no_go_review',
            'controlled_rollout_executed', 'controlled_rollout_active', 'controlled_rollout_only',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed',
            'kill_switch_confirmed', 'rollback_confirmed', 'watchlist_function_invoked_during_execution',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed',
            'primary_candidate_controlled_rollout_result_reviewed', 'backup_candidate_controlled_rollout_result_reviewed',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review_manifest_created',
            'a01_remains_comparator_only', 'temporary_negative_artifact_cleanup_confirmed',
        ] as $field) {
            if (($source[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach ([
            'c165_topic_complete', 'new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed',
            'production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed',
            'free_publication_allowed', 'unrestricted_publication_allowed', 'watchlist_function_invoked_by_result_review',
            'watchlist_function_comparator_candidate_observed', 'comparator_candidate_controlled_rollout_result_reviewed',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review', 'temporary_negative_artifacts_remaining',
        ] as $field) {
            if (($source[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($source['run_code'] ?? null) === 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW'
            && ($source['status'] ?? null) === self::EXPECTED_RESULT_STATUS
            && ($source['reason_code'] ?? null) === self::EXPECTED_RESULT_STATUS
            && ($source['phase_label'] ?? null) === self::EXPECTED_RESULT_PHASE
            && ($source['next_step_recommendation'] ?? null) === self::RUN_CODE
            && ($source['topic_code'] ?? null) === 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT'
            && ($source['topic_stage'] ?? null) === 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW'
            && (int) ($source['rollout_state_record_count'] ?? 0) === 2
            && $this->candidateScopeValid($source)
            && $this->functionScopeValid($source)
            && $this->valueAt($source, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE
            && $this->valueAt($source, ['planned_next_summary', 'same_topic_c165_continues']) === true
            && $this->valueAt($source, ['next_plan_confirm_controlled_rollout_operator_go_no_go_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($source, ['next_plan_confirm_controlled_rollout_operator_go_no_go_decision', 'same_topic_c165_continues']) === true;
    }

    private function candidateScopeValid(array $source): bool
    {
        return ($source['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($source['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($source['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($source['a01_remains_comparator_only'] ?? null) === true;
    }

    private function functionScopeValid(array $source): bool
    {
        return ($source['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($source['watchlist_function_runtime_mode'] ?? null) === self::RUNTIME_MODE
            && ($source['watchlist_function_invoked_during_execution'] ?? null) === true
            && ($source['watchlist_function_invoked_by_result_review'] ?? null) === false;
    }

    private function operatorDecisionSummary(array $options, ?string $decision, string $reason, bool $completed): array
    {
        $summary = [
            'operator_approved' => ($options['operator_approved'] ?? false) === true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_valid' => $decision !== null,
            'operator_decision_confirmed' => ($options['operator_decision_confirmed'] ?? false) === true,
            'decision_reason' => $reason,
            'decision_reason_present' => $reason !== '',
            'operator_review_completed' => $completed,
        ];
        foreach (self::CONFIRMATION_STATUSES as $option => $unused) {
            $summary[$option] = ($options[$option] ?? false) === true;
        }

        return $summary;
    }

    private function candidateScorecard(bool $completed, ?string $decision): array
    {
        $go = $completed && $decision === 'GO';

        return [
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'role' => 'primary_controlled_rollout', 'operator_reviewed' => $completed, 'operator_decision' => $decision ?? 'INVALID', 'ready_for_go_decision_finalization' => $go],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'role' => 'backup_controlled_rollout', 'operator_reviewed' => $completed, 'operator_decision' => $decision ?? 'INVALID', 'ready_for_go_decision_finalization' => $go],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'role' => 'comparator_only', 'operator_reviewed' => false, 'operator_decision' => 'NOT_APPLICABLE', 'ready_for_go_decision_finalization' => false],
        ];
    }

    private function nextRecommendation(bool $completed, ?string $decision): string
    {
        if (! $completed || $decision === null) {
            return 'C165_TARGETED_CONTROLLED_ROLLOUT_OPERATOR_REVIEW_REPAIR';
        }
        if ($decision === 'GO') {
            return self::NEXT_FINALIZATION;
        }
        if ($decision === 'NO_GO') {
            return 'C165_CONTROLLED_ROLLOUT_PROGRESSION_STOPPED_NO_GO';
        }

        return 'C165_CONTROLLED_ROLLOUT_PROGRESSION_DEFERRED_HOLD';
    }

    private function normalizeDecision(string $decision): ?string
    {
        $normalized = strtoupper(str_replace(['-', ' '], '_', trim($decision)));

        return in_array($normalized, ['GO', 'NO_GO', 'HOLD'], true) ? $normalized : null;
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $completed, ?string $decision): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $completed
            ? 'C165_CONTROLLED_ROLLOUT_OPERATOR_DECISION_'.($decision ?? 'INVALID').'_RECORDED_FREE_PUBLICATION_LOCKED'
            : $status;
        $artifact['next_step_recommendation'] = $this->nextRecommendation($completed, $decision);
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $completed ? 0 : 1,
            'failures' => $completed ? [] : [$status],
            'attribution' => $completed ? 'NONE' : 'C165_OPERATOR_REVIEW_SOURCE_DECISION_OR_CONFIRMATION',
        ];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function lockSummary(array $load): array
    {
        return [
            'source' => 'c165_result_review',
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
            'expected_c165_result_review_hash' => $load['expected_hash'],
            'actual_c165_result_review_hash' => $load['actual_hash'],
            'c165_result_review_hash_match' => $load['hash_match'],
            'expected_c165_result_review_file_sha1' => $load['expected_file_sha1'],
            'actual_c165_result_review_file_sha1' => $load['actual_file_sha1'],
            'c165_result_review_file_sha1_match' => $load['file_sha1_match'],
            'c165_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
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
