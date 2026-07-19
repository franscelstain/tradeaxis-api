<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewService
{
    public const RUN_CODE = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-95 / C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_C166_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C166_FINALIZATION_HASH = '299eb7f2978b8755351d28bb299249f0cb0d818f';
    public const DEFAULT_EXPECTED_C166_FINALIZATION_FILE_SHA1 = '3E2CF7C226756EFD9F3AADBDDCAE3BD133D174BA';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c167-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-completion-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const SOURCE_RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';
    private const BOUNDARY_RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY';

    private const EXPECTED_C166_STATUS = 'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_ROLLOUT_OBSERVATION_CLOSED_READY_FOR_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C166_PHASE_LABEL = 'PR-94 / C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';
    private const NEXT_EXECUTION = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_EXECUTION';

    private const PASS_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_ROLLOUT_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP';
    private const LOCK_MISMATCH_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const JSON_COMPATIBILITY_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const STATUS_MISMATCH_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_STATUS_MISMATCH';
    private const PHASE_MISMATCH_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_PHASE_LABEL_MISMATCH';
    private const NEXT_MISMATCH_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C166_STATE_INVALID_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH';
    private const SAFETY_STATE_INVALID_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_NEW_MUTATION_OR_PUBLICATION_SAFETY_STATE_INVALID';
    private const APPROVAL_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_CONFIRMATION_MISSING';
    private const C166_LOCK_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_C166_FINALIZATION_LOCK_CONFIRMATION_MISSING';
    private const EVIDENCE_CHAIN_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_CONTROLLED_ROLLOUT_EVIDENCE_CHAIN_CONFIRMATION_MISSING';
    private const COMPLETION_EXECUTION_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_EXECUTION_REQUIRED_CONFIRMATION_MISSING';
    private const MARKET_METRIC_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_MARKET_METRICS_NOT_INFERRED_CONFIRMATION_MISSING';
    private const CANDIDATE_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_CONFIRMATION_MISSING';
    private const KILL_SWITCH_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_KILL_SWITCH_CONFIRMATION_MISSING';
    private const ROLLBACK_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_ROLLBACK_CONFIRMATION_MISSING';
    private const PRODUCTION_CONFIG_CONFIRMATION_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_PRODUCTION_CONFIG_UNCHANGED_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const REQUIRED_C166_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_pass',
        'production_live_runtime_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review_pass',
        'operator_go_decision',
        'operator_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'post_rollout_observation_go_finalized',
        'post_rollout_observation_topic_closed',
        'post_rollout_observation_topic_closure_confirmed',
        'c166_topic_complete',
        'c166_topic_complete_after_finalization',
        'free_publication_locked_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_review',
        'production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_review_allowed_next',
        'c167_controlled_rollout_completion_boundary_required_next',
        'c166_operator_artifact_lock_valid',
        'c166_operator_go_valid',
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
        'primary_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review',
        'backup_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review',
        'a01_remains_comparator_only',
        'market_metrics_not_inferred_confirmed',
        'candidate_scope_confirmed',
        'production_config_unchanged_confirmed',
        'controlled_rollout_completion_boundary_required_confirmed',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C166_FALSE_FIELDS = [
        'market_outcome_metrics_available',
        'market_metrics_inferred_by_finalization',
        'new_rollout_executed',
        'new_plan_confirm_mutation_executed',
        'new_catalog_read_executed',
        'watchlist_function_invoked_by_finalization',
        'production_config_mutated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'free_publication_allowed',
        'unrestricted_publication_allowed',
        'unrestricted_rollout_allowed',
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c167-*controlled-rollout-completion-boundary*-negative-*-test.json',
        'storage/app/watchlist/backtest/c167-*controlled-rollout-completion-boundary*-missing-*-test.json',
        'storage/app/watchlist/backtest/c167-*controlled-rollout-completion-boundary*-mismatch-*-test.json',
        'storage/app/watchlist/backtest/c167-*controlled-rollout-completion-boundary*-invalid-*-test.json',
    ];

    public function execute(
        string $c166FinalizationArtifact = self::DEFAULT_C166_FINALIZATION_ARTIFACT,
        string $expectedC166FinalizationHash = self::DEFAULT_EXPECTED_C166_FINALIZATION_HASH,
        string $expectedC166FinalizationFileSha1 = self::DEFAULT_EXPECTED_C166_FINALIZATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($c166FinalizationArtifact, $expectedC166FinalizationHash, $expectedC166FinalizationFileSha1);
        $artifact = array_merge($artifact, $this->sourceLockAliases($load));
        $artifact['source_artifact_locks'] = [$this->sourceLockSummary($load)];

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::LOCK_MISMATCH_STATUS, 'C166 finalization artifact is missing or unreadable.', $outputPath, $overwrite, false);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c166_finalization_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->finish($artifact, self::JSON_COMPATIBILITY_STATUS, 'C166 finalization artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, false);
        }
        if (! $load['hash_match']) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::LOCK_MISMATCH_STATUS, 'C166 finalization artifact_hash does not match the boundary lock.', $outputPath, $overwrite, false);
        }
        if (! $load['file_sha1_match']) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::SHA1_MISMATCH_STATUS, 'C166 finalization file SHA1 does not match the boundary lock.', $outputPath, $overwrite, false);
        }

        $c166 = $load['payload'];
        if (($c166['status'] ?? null) !== self::EXPECTED_C166_STATUS || ($c166['reason_code'] ?? null) !== self::EXPECTED_C166_STATUS) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::STATUS_MISMATCH_STATUS, 'C166 finalization status is not controlled-rollout-completion-boundary ready.', $outputPath, $overwrite, false);
        }
        if (($c166['phase_label'] ?? null) !== self::EXPECTED_C166_PHASE_LABEL) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::PHASE_MISMATCH_STATUS, 'C166 finalization phase label does not match.', $outputPath, $overwrite, false);
        }
        if (! $this->c166NextRecommendationMatches($c166)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::NEXT_MISMATCH_STATUS, 'C166 finalization does not recommend this C167 controlled rollout completion boundary.', $outputPath, $overwrite, false);
        }
        if (! $this->c166StateComplete($c166)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::C166_STATE_INVALID_STATUS, 'C166 finalization state is incomplete or internally inconsistent.', $outputPath, $overwrite, false);
        }
        if (! $this->safetyStateClean($c166)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::SAFETY_STATE_INVALID_STATUS, 'C166 contains a new finalization mutation, publication authorization, production-config mutation, or unrestricted rollout state.', $outputPath, $overwrite, false);
        }
        if (! $this->candidateScopeMatches($c166)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C166 candidate scope does not match the controlled rollout completion boundary.', $outputPath, $overwrite, false);
        }
        if (! $this->watchlistFunctionScopeMatches($c166)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS, 'C166 watchlist function scope is not the locked controlled function.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C167 boundary requires operator approval and a non-empty approval reference.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['controlled_rollout_completion_boundary_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::BOUNDARY_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires controlled rollout completion boundary confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['c166_finalization_locked_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::C166_LOCK_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires C166 finalization lock confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['controlled_rollout_evidence_chain_complete_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::EVIDENCE_CHAIN_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires confirmation that the controlled rollout evidence chain is complete.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['completion_execution_required_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::COMPLETION_EXECUTION_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires controlled rollout completion execution next.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['market_metrics_not_inferred_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::MARKET_METRIC_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires confirmation that unavailable market metrics were not inferred.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['candidate_scope_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires the E02/B01/A01 candidate scope confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['kill_switch_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::KILL_SWITCH_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires kill-switch confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['rollback_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::ROLLBACK_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires rollback confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['production_config_unchanged_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::PRODUCTION_CONFIG_CONFIRMATION_MISSING_STATUS, 'C167 boundary requires production configuration unchanged confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C167 boundary requires the free-publication lock confirmation.', $outputPath, $overwrite, false);
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $options['temporary_negative_artifact_paths'] = $temporaryPaths;

            return $this->finish($this->completeSections($artifact, $load, $options, false), self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary C167 negative artifact remains.', $outputPath, $overwrite, false);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact = array_merge($artifact, $this->passingState($c166, $load, $options));

        return $this->finish($artifact, self::PASS_STATUS, 'C167 controlled rollout completion boundary is open for same-topic completion execution.', $outputPath, $overwrite, true);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-95',
            'internal_checkpoint' => 'C167',
            'topic_code' => 'C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW',
            'status' => 'C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'reason_code' => 'C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_pass' => false,
            'production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_pass' => false,
            'controlled_rollout_completion_boundary_confirmed' => false,
            'controlled_rollout_completion_boundary_open' => false,
            'c166_finalization_locked_confirmed' => false,
            'controlled_rollout_evidence_chain_complete_confirmed' => false,
            'completion_execution_required_confirmed' => false,
            'market_metrics_not_inferred_confirmed' => false,
            'candidate_scope_confirmed' => false,
            'kill_switch_confirmed' => false,
            'rollback_confirmed' => false,
            'production_config_unchanged_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_execution' => false,
            'controlled_plan_confirm_rollout_completion_execution_allowed_next' => false,
            'controlled_rollout_executed' => true,
            'controlled_rollout_active' => true,
            'new_rollout_executed' => false,
            'new_plan_confirm_mutation_executed' => false,
            'new_catalog_read_executed' => false,
            'watchlist_function_invoked_by_boundary' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => true,
            'plan_confirm_runtime_reads_activated_catalog' => true,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => true,
            'production_config_mutated' => false,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::BOUNDARY_RUNTIME_MODE,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c167_is_distinct_controlled_rollout_completion_topic' => true,
            'c167_not_c166_observation_repeat' => true,
            'c167_boundary_review_only' => true,
            'c167_topic_open' => false,
            'c167_topic_complete' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function passingState(array $c166, array $load, array $options): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_pass' => true,
            'production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_pass' => true,
            'controlled_rollout_completion_boundary_confirmed' => true,
            'controlled_rollout_completion_boundary_open' => true,
            'c166_finalization_locked_confirmed' => true,
            'c166_finalization_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c166_finalization_state_valid' => $this->c166StateComplete($c166),
            'c167_topic_open' => true,
            'c167_topic_complete' => false,
            'controlled_rollout_evidence_chain_complete_confirmed' => true,
            'completion_execution_required_confirmed' => true,
            'market_metrics_not_inferred_confirmed' => true,
            'candidate_scope_confirmed' => true,
            'kill_switch_confirmed' => true,
            'rollback_confirmed' => true,
            'production_config_unchanged_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_execution' => true,
            'controlled_plan_confirm_rollout_completion_execution_allowed_next' => true,
            'controlled_rollout_executed' => true,
            'controlled_rollout_active' => true,
            'new_rollout_executed' => false,
            'new_plan_confirm_mutation_executed' => false,
            'new_catalog_read_executed' => false,
            'watchlist_function_invoked_by_boundary' => false,
            'plan_confirm_mutated' => true,
            'plan_confirm_runtime_reads_activated_catalog' => true,
            'live_plan_confirm_rollout_executed' => true,
            'production_config_mutated' => false,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_completion_execution' => true,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_completion_execution' => true,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_completion_execution' => false,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c166 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryPaths = array_values((array) ($options['temporary_negative_artifact_paths'] ?? []));
        $artifact['c166_finalization_lock_validation_summary'] = [
            'validation_completed' => true,
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
        ];
        $artifact['c166_finalization_carry_forward_summary'] = [
            'c166_status' => $c166['status'] ?? null,
            'c166_topic_complete' => (bool) ($c166['c166_topic_complete_after_finalization'] ?? false),
            'post_rollout_observation_topic_closed' => (bool) ($c166['post_rollout_observation_topic_closed'] ?? false),
            'controlled_rollout_active' => (bool) ($c166['controlled_rollout_active'] ?? false),
            'post_rollout_observation_result_valid' => (bool) ($c166['post_rollout_observation_result_valid'] ?? false),
            'c166_next_recommendation_match' => $this->c166NextRecommendationMatches($c166),
            'c166_state_valid' => $this->c166StateComplete($c166),
        ];
        $artifact['plan_confirm_controlled_rollout_completion_boundary_guard_summary'] = [
            'boundary_reviewed' => true,
            'boundary_confirmed' => (bool) ($options['controlled_rollout_completion_boundary_confirmed'] ?? false),
            'boundary_open' => $pass,
            'controlled_rollout_evidence_chain_complete_confirmed' => (bool) ($options['controlled_rollout_evidence_chain_complete_confirmed'] ?? false),
            'boundary_executes_rollout' => false,
            'boundary_reads_activated_catalog' => false,
            'boundary_mutates_plan_confirm' => false,
            'boundary_invokes_watchlist_function' => false,
            'boundary_free_publishes_output' => false,
        ];
        $artifact['watchlist_function_scope_summary'] = [
            'validation_completed' => true,
            'watchlist_function_scope_matches' => $this->watchlistFunctionScopeMatches($c166),
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'source_runtime_mode' => $c166['watchlist_function_runtime_mode'] ?? null,
            'expected_source_runtime_mode' => self::SOURCE_RUNTIME_MODE,
            'boundary_runtime_mode' => self::BOUNDARY_RUNTIME_MODE,
            'function_invoked_by_boundary' => false,
            'function_invoked_during_locked_controlled_rollout_execution' => (bool) ($c166['watchlist_function_invoked_during_execution'] ?? false),
            'function_reserved_for_controlled_rollout_completion_execution' => false,
        ];
        $artifact['candidate_scope_freeze_summary'] = [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c166),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_ready_for_controlled_rollout_completion_execution' => $pass,
            'backup_ready_for_controlled_rollout_completion_execution' => $pass,
            'comparator_ready_for_controlled_rollout_completion_execution' => false,
            'a01_remains_comparator_only' => true,
        ];
        $artifact['operator_approval_validation_summary'] = [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
        ];
        $artifact['temporary_negative_artifact_guard_summary'] = [
            'validation_completed' => true,
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
        $artifact['c167_plan_confirm_controlled_rollout_completion_boundary_decision'] = [
            'review_valid' => $pass,
            'boundary_decision' => $pass ? 'OPEN' : 'CLOSED',
            'controlled_rollout_completion_boundary_confirmed' => (bool) ($options['controlled_rollout_completion_boundary_confirmed'] ?? false),
            'c166_finalization_locked' => $load['hash_match'] && $load['file_sha1_match'],
            'c166_topic_complete' => (bool) ($c166['c166_topic_complete_after_finalization'] ?? false),
            'c167_is_distinct_topic' => true,
            'controlled_rollout_already_executed' => (bool) ($c166['controlled_rollout_executed'] ?? false),
            'completion_execution_performed' => false,
        ];
        $artifact['next_plan_confirm_controlled_rollout_completion_execution_decision'] = [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::NEXT_EXECUTION : 'C167_TARGETED_C166_FINALIZATION_OR_BOUNDARY_REPAIR',
            'same_topic_c167_continues' => $pass,
            'controlled_rollout_completion_execution_allowed_next' => $pass,
            'execution_requires_locked_c167_boundary_artifact' => $pass,
            'free_publication_allowed_next' => false,
            'unrestricted_rollout_allowed_next' => false,
        ];
        $artifact['weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_manifest'] = [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_controlled_rollout_completion_boundary_review',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'c166_post_rollout_observation_finalized' => (bool) ($c166['c166_topic_complete_after_finalization'] ?? false),
            'controlled_rollout_completion_boundary_open' => $pass,
            'ready_for_controlled_rollout_completion_execution' => $pass,
            'controlled_rollout_completion_boundary_artifact_only' => true,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'boundary_used_for_free_publication' => false,
            'boundary_used_for_plan_confirm_mutation' => false,
            'boundary_used_for_activated_catalog_read' => false,
            'boundary_used_for_rollout_execution' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
        $artifact['weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_checklist'] = [
            'c166_finalization_lock_reviewed' => true,
            'c166_finalization_state_reviewed' => true,
            'controlled_rollout_completion_boundary_reviewed' => true,
            'controlled_rollout_completion_boundary_confirmed' => (bool) ($options['controlled_rollout_completion_boundary_confirmed'] ?? false),
            'controlled_rollout_evidence_chain_complete_confirmed' => (bool) ($options['controlled_rollout_evidence_chain_complete_confirmed'] ?? false),
            'completion_execution_required_confirmed' => (bool) ($options['completion_execution_required_confirmed'] ?? false),
            'market_metrics_not_inferred_confirmed' => (bool) ($options['market_metrics_not_inferred_confirmed'] ?? false),
            'candidate_scope_confirmed' => (bool) ($options['candidate_scope_confirmed'] ?? false),
            'kill_switch_confirmed' => (bool) ($options['kill_switch_confirmed'] ?? false),
            'rollback_confirmed' => (bool) ($options['rollback_confirmed'] ?? false),
            'production_config_unchanged_confirmed' => (bool) ($options['production_config_unchanged_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'artifact_only' => true,
            'controlled_rollout_execution_required_next' => $pass,
        ];
        $artifact['c167_candidate_plan_confirm_controlled_rollout_completion_boundary_scorecard'] = [
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'c167_role' => 'primary_ready_for_controlled_rollout_completion_execution', 'ready_for_controlled_rollout_completion_execution' => $pass],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'c167_role' => 'backup_ready_for_controlled_rollout_completion_execution', 'ready_for_controlled_rollout_completion_execution' => $pass],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'c167_role' => 'comparator_only', 'ready_for_controlled_rollout_completion_execution' => false],
        ];
        $artifact['publication_plan_confirm_rollout_safety_summary'] = [
            'validation_completed' => true,
            'source_safety_state_clean' => $this->safetyStateClean($c166),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'controlled_rollout_already_executed' => (bool) ($c166['controlled_rollout_executed'] ?? false),
            'new_rollout_executed' => false,
            'plan_confirm_mutated_by_locked_execution' => (bool) ($c166['plan_confirm_mutated'] ?? false),
            'activated_catalog_read_by_locked_execution' => (bool) ($c166['plan_confirm_runtime_reads_activated_catalog'] ?? false),
            'new_plan_confirm_mutation_executed' => false,
            'new_catalog_read_executed' => false,
            'production_config_mutated' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'controlled_rollout_completion_execution_allowed_next' => $pass,
        ];
        $artifact['documentation_hygiene_guard_summary'] = [
            'documentation_hygiene_guard_reviewed' => true,
            'c166_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c167_uses_the_c166_declared_next_boundary' => $this->c166NextRecommendationMatches($c166),
            'c167_boundary_is_distinct_from_c166_observation' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
        $artifact['progress_summary'] = [
            'progress_marker' => 'PR-95_C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW',
            'topic_code' => 'C167_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW',
            'c166_topic_complete' => (bool) ($c166['c166_topic_complete_after_finalization'] ?? false),
            'c167_topic_open' => $pass,
            'controlled_rollout_already_executed' => (bool) ($c166['controlled_rollout_executed'] ?? false),
            'completion_execution_executed' => false,
        ];
        $artifact['planned_next_summary'] = [
            'planned_next_review' => $pass ? self::NEXT_EXECUTION : 'C167_TARGETED_C166_FINALIZATION_OR_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C167 controlled rollout completion execution with locked boundary evidence; no new rollout and no free publication' : 'repair C166 lock or C167 boundary evidence',
            'same_topic_c167_continues' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C167 controlled rollout completion boundary artifact hash',
                'locked C167 controlled rollout completion boundary file SHA1',
                'explicit controlled rollout completion execution approval',
                'closed C166 post-rollout observation evidence chain',
                'no new watchlist function invocation or PLAN/CONFIRM mutation',
                'free publication remains disabled',
            ] : [],
        ];
        $artifact['diagnostics'] = [
            'C167 locks the C166 post-rollout observation finalization artifact before opening the controlled rollout completion boundary.',
            'C167 is a distinct PLAN/CONFIRM controlled rollout completion topic and does not repeat C166 observation.',
            'The boundary recognizes the existing controlled rollout but does not mutate PLAN/CONFIRM, read the activated catalog, execute a new rollout, invoke the watchlist function, or free-publish recommendations.',
            'The next same-topic execution may proceed only from the locked C167 boundary artifact.',
        ];

        return $artifact;
    }

    private function c166NextRecommendationMatches(array $c166): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_controlled_rollout_completion_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c166, $path) !== self::RUN_CODE) {
                return false;
            }
        }

        return true;
    }

    private function c166StateComplete(array $c166): bool
    {
        foreach (self::REQUIRED_C166_TRUE_FIELDS as $field) {
            if (($c166[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C166_FALSE_FIELDS as $field) {
            if (($c166[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($c166['operator_decision'] ?? null) === 'GO'
            && $this->valueAt($c166, ['c166_go_decision_finalization_decision', 'operator_go_locked']) === true
            && $this->valueAt($c166, ['c166_go_decision_finalization_decision', 'go_decision_finalized']) === true
            && $this->valueAt($c166, ['c166_go_decision_finalization_decision', 'post_rollout_observation_topic_closed']) === true
            && $this->valueAt($c166, ['c166_go_decision_finalization_decision', 'c166_topic_complete']) === true
            && $this->valueAt($c166, ['next_controlled_rollout_completion_boundary_decision', 'c166_complete']) === true
            && $this->valueAt($c166, ['next_controlled_rollout_completion_boundary_decision', 'c167_may_start']) === true
            && $this->valueAt($c166, ['next_controlled_rollout_completion_boundary_decision', 'controlled_rollout_completion_boundary_required_next']) === true
            && $this->valueAt($c166, ['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_manifest', 'finalization_artifact_only']) === true
            && $this->valueAt($c166, ['weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_checklist', 'controlled_rollout_completion_boundary_required_next']) === true;
    }

    private function safetyStateClean(array $c166): bool
    {
        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'free_publication_allowed',
            'unrestricted_publication_allowed',
            'unrestricted_rollout_allowed',
            'new_rollout_executed',
            'new_plan_confirm_mutation_executed',
            'new_catalog_read_executed',
            'watchlist_function_invoked_by_finalization',
            'production_config_mutated',
            'market_metrics_inferred_by_finalization',
        ] as $field) {
            if (($c166[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($c166['controlled_rollout_executed'] ?? null) === true
            && ($c166['controlled_rollout_active'] ?? null) === true
            && ($c166['plan_confirm_mutated'] ?? null) === true
            && ($c166['plan_confirm_runtime_reads_activated_catalog'] ?? null) === true
            && $this->valueAt($c166, ['publication_and_rollout_safety_summary', 'finalization_artifact_only']) === true
            && $this->valueAt($c166, ['publication_and_rollout_safety_summary', 'new_rollout_executed']) === false
            && $this->valueAt($c166, ['publication_and_rollout_safety_summary', 'new_plan_confirm_mutation_executed']) === false
            && $this->valueAt($c166, ['publication_and_rollout_safety_summary', 'new_catalog_read_executed']) === false
            && $this->valueAt($c166, ['publication_and_rollout_safety_summary', 'production_config_mutated']) === false
            && $this->valueAt($c166, ['publication_and_rollout_safety_summary', 'free_publication_allowed']) === false;
    }

    private function candidateScopeMatches(array $c166): bool
    {
        return ($c166['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c166['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c166['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c166['a01_remains_comparator_only'] ?? null) === true;
    }

    private function watchlistFunctionScopeMatches(array $c166): bool
    {
        return ($c166['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($c166['watchlist_function_runtime_mode'] ?? null) === self::SOURCE_RUNTIME_MODE
            && ($c166['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($c166['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($c166['watchlist_function_comparator_candidate_observed'] ?? null) === false;
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $pass): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $pass
            ? 'C167_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_OPEN_READY_FOR_SAME_TOPIC_COMPLETION_EXECUTION_NO_NEW_ROLLOUT_OR_FREE_PUBLICATION'
            : $status;
        $artifact['next_step_recommendation'] = $pass ? self::NEXT_EXECUTION : 'C167_TARGETED_C166_FINALIZATION_OR_BOUNDARY_REPAIR';
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $pass ? 0 : 1,
            'failures' => $pass ? [] : [$status],
            'attribution' => $pass ? 'NONE' : 'C167_BOUNDARY_INPUT_OR_CONFIRMATION',
        ];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        if ($overwrite || ! is_file($outputPath)) {
            $directory = dirname($outputPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
            file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        }

        return $artifact;
    }

    private function sourceLockAliases(array $load): array
    {
        return [
            'expected_c166_finalization_hash' => $load['expected_hash'],
            'actual_c166_finalization_hash' => $load['actual_hash'],
            'c166_finalization_hash_match' => $load['hash_match'],
            'expected_c166_finalization_file_sha1' => $load['expected_file_sha1'],
            'actual_c166_finalization_file_sha1' => $load['actual_file_sha1'],
            'c166_finalization_file_sha1_match' => $load['file_sha1_match'],
            'c166_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function sourceLockSummary(array $load): array
    {
        return [
            'source_artifact' => 'C166_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW',
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
        $expectKey = false;
        $seen = [];
        $duplicates = [];
        for ($i = 0; $i < $length; $i++) {
            $char = $raw[$i];
            if ($char === '"') {
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
                            if (array_key_exists($lower, $seen) && ! in_array($key, $duplicates, true)) {
                                $duplicates[] = $key;
                            }
                            $seen[$lower] = $key;
                        }
                        $expectKey = false;
                    }
                }
                continue;
            }
            if ($char === '{') {
                $depth++;
                $expectKey = $depth === 1;
            } elseif ($char === '}') {
                $depth--;
                $expectKey = false;
            } elseif ($char === ',' && $depth === 1) {
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
