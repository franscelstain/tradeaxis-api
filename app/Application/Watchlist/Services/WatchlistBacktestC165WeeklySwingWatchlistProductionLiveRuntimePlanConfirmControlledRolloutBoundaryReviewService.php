<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewService
{
    public const RUN_CODE = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-86 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_C164_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C164_FINALIZATION_HASH = '63c7512cb6d395bc6268dae385a10ae703e4aa3d';
    public const DEFAULT_EXPECTED_C164_FINALIZATION_FILE_SHA1 = '9CA9F2F36F15F17C15301E9F119C303088EDD163';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const SOURCE_RUNTIME_MODE = 'PLAN_CONFIRM_COMPLETION_POST_HANDOFF_CONTROLLED_OUTPUT';
    private const BOUNDARY_RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY';

    private const EXPECTED_C164_STATUS = 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_HANDOFF_ACTIVATION_COMPLETION_CLOSED_READY_FOR_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C164_PHASE_LABEL = 'PR-85 / C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';
    private const NEXT_EXECUTION = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION';

    private const PASS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_ROLLOUT_EXECUTION_PRIMARY_AND_BACKUP';
    private const LOCK_MISMATCH_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const SHA1_MISMATCH_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const JSON_COMPATIBILITY_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const STATUS_MISMATCH_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_STATUS_MISMATCH';
    private const PHASE_MISMATCH_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_PHASE_LABEL_MISMATCH';
    private const NEXT_MISMATCH_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C164_STATE_INVALID_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_WATCHLIST_FUNCTION_SCOPE_MISMATCH';
    private const SAFETY_STATE_INVALID_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_PUBLICATION_PLAN_CONFIRM_OR_ROLLOUT_ALREADY_OCCURRED';
    private const APPROVAL_MISSING_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_CONTROLLED_ROLLOUT_BOUNDARY_CONFIRMATION_MISSING';
    private const C164_LOCK_CONFIRMATION_MISSING_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_C164_FINALIZATION_LOCK_CONFIRMATION_MISSING';
    private const CONTROLLED_ONLY_CONFIRMATION_MISSING_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_CONTROLLED_ROLLOUT_ONLY_CONFIRMATION_MISSING';
    private const PLAN_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_NO_ROLLOUT_EXECUTED_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const REQUIRED_C164_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_pass',
        'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_review_pass',
        'operator_go_decision',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'post_handoff_activation_completion_finalization_confirmed',
        'post_handoff_activation_completion_closed',
        'c164_topic_complete_after_finalization',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_review',
        'production_live_runtime_plan_confirm_controlled_rollout_boundary_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'controlled_completion_lock_valid',
        'controlled_completion_integrity_valid',
        'controlled_completion_convert_from_json_pass',
        'watchlist_function_primary_candidate_observed',
        'watchlist_function_backup_candidate_observed',
        'primary_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review',
        'backup_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review',
        'a01_remains_comparator_only',
        'c164_not_publication',
        'c164_not_unrestricted_publication',
        'c164_not_plan_confirm_mutation',
        'c164_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C164_FALSE_FIELDS = [
        'watchlist_function_comparator_candidate_observed',
        'comparator_candidate_ready_for_plan_confirm_controlled_rollout_boundary_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c165-*controlled-rollout-boundary*-negative-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-boundary*-missing-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-boundary*-mismatch-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-boundary*-invalid-*-test.json',
    ];

    public function execute(
        string $c164FinalizationArtifact = self::DEFAULT_C164_FINALIZATION_ARTIFACT,
        string $expectedC164FinalizationHash = self::DEFAULT_EXPECTED_C164_FINALIZATION_HASH,
        string $expectedC164FinalizationFileSha1 = self::DEFAULT_EXPECTED_C164_FINALIZATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadJsonLock($c164FinalizationArtifact, $expectedC164FinalizationHash, $expectedC164FinalizationFileSha1);
        $artifact = array_merge($artifact, $this->sourceLockAliases($load));
        $artifact['source_artifact_locks'] = [$this->sourceLockSummary($load)];

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::LOCK_MISMATCH_STATUS, 'C164 finalization artifact is missing or unreadable.', $outputPath, $overwrite, false);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c164_finalization_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->finish($artifact, self::JSON_COMPATIBILITY_STATUS, 'C164 finalization artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, false);
        }
        if (! $load['hash_match']) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::LOCK_MISMATCH_STATUS, 'C164 finalization artifact_hash does not match the boundary lock.', $outputPath, $overwrite, false);
        }
        if (! $load['file_sha1_match']) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::SHA1_MISMATCH_STATUS, 'C164 finalization file SHA1 does not match the boundary lock.', $outputPath, $overwrite, false);
        }

        $c164 = $load['payload'];
        if (($c164['status'] ?? null) !== self::EXPECTED_C164_STATUS || ($c164['reason_code'] ?? null) !== self::EXPECTED_C164_STATUS) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::STATUS_MISMATCH_STATUS, 'C164 finalization status is not controlled-rollout-boundary ready.', $outputPath, $overwrite, false);
        }
        if (($c164['phase_label'] ?? null) !== self::EXPECTED_C164_PHASE_LABEL) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::PHASE_MISMATCH_STATUS, 'C164 finalization phase label does not match.', $outputPath, $overwrite, false);
        }
        if (! $this->c164NextRecommendationMatches($c164)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::NEXT_MISMATCH_STATUS, 'C164 finalization does not recommend this C165 controlled rollout boundary.', $outputPath, $overwrite, false);
        }
        if (! $this->c164StateComplete($c164)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::C164_STATE_INVALID_STATUS, 'C164 finalization state is incomplete or internally inconsistent.', $outputPath, $overwrite, false);
        }
        if (! $this->safetyStateClean($c164)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::SAFETY_STATE_INVALID_STATUS, 'Publication, PLAN/CONFIRM mutation, activated-catalog read, or rollout already occurred before the boundary.', $outputPath, $overwrite, false);
        }
        if (! $this->candidateScopeMatches($c164)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C164 candidate scope does not match the controlled rollout boundary.', $outputPath, $overwrite, false);
        }
        if (! $this->watchlistFunctionScopeMatches($c164)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::WATCHLIST_FUNCTION_SCOPE_MISMATCH_STATUS, 'C164 watchlist function scope is not the locked controlled function.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C165 boundary requires operator approval and a non-empty approval reference.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['controlled_rollout_boundary_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::BOUNDARY_CONFIRMATION_MISSING_STATUS, 'C165 boundary requires controlled rollout boundary confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['c164_finalization_locked_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::C164_LOCK_CONFIRMATION_MISSING_STATUS, 'C165 boundary requires C164 finalization lock confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['controlled_rollout_only_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::CONTROLLED_ONLY_CONFIRMATION_MISSING_STATUS, 'C165 boundary requires controlled-rollout-only confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::PLAN_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C165 boundary requires PLAN/CONFIRM unchanged confirmation.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['no_rollout_executed_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::NO_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C165 boundary requires confirmation that rollout has not executed.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->finish($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C165 boundary requires the free-publication lock confirmation.', $outputPath, $overwrite, false);
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $options['temporary_negative_artifact_paths'] = $temporaryPaths;

            return $this->finish($this->completeSections($artifact, $load, $options, false), self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary C165 negative artifact remains.', $outputPath, $overwrite, false);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact = array_merge($artifact, $this->passingState($c164, $load, $options));

        return $this->finish($artifact, self::PASS_STATUS, 'C165 controlled rollout boundary is open for same-topic controlled rollout execution.', $outputPath, $overwrite, true);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-86',
            'internal_checkpoint' => 'C165',
            'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW',
            'status' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_NOT_RUN',
            'reason_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_pass' => false,
            'production_live_runtime_plan_confirm_controlled_rollout_boundary_review_pass' => false,
            'controlled_rollout_boundary_confirmed' => false,
            'controlled_rollout_boundary_open' => false,
            'c164_finalization_locked_confirmed' => false,
            'controlled_rollout_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_rollout_executed_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_execution' => false,
            'controlled_plan_confirm_rollout_execution_allowed_next' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::BOUNDARY_RUNTIME_MODE,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c165_is_distinct_controlled_rollout_topic' => true,
            'c165_not_c164_completion_repeat' => true,
            'c165_boundary_review_only' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function passingState(array $c164, array $load, array $options): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_pass' => true,
            'production_live_runtime_plan_confirm_controlled_rollout_boundary_review_pass' => true,
            'controlled_rollout_boundary_confirmed' => true,
            'controlled_rollout_boundary_open' => true,
            'c164_finalization_locked_confirmed' => true,
            'c164_finalization_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c164_finalization_state_valid' => $this->c164StateComplete($c164),
            'controlled_rollout_only_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_rollout_executed_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_execution' => true,
            'controlled_plan_confirm_rollout_execution_allowed_next' => true,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($c164['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($c164['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c164['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c164['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c164['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'controlled_completion_path' => (string) ($c164['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($c164['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($c164['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($c164['controlled_completion_record_count'] ?? 0),
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_execution' => true,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_execution' => true,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_execution' => false,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c164 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryPaths = array_values((array) ($options['temporary_negative_artifact_paths'] ?? []));
        $artifact['c164_finalization_lock_validation_summary'] = [
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
        $artifact['c164_finalization_carry_forward_summary'] = [
            'c164_status' => $c164['status'] ?? null,
            'c164_topic_complete' => (bool) ($c164['c164_topic_complete_after_finalization'] ?? false),
            'post_handoff_activation_completion_closed' => (bool) ($c164['post_handoff_activation_completion_closed'] ?? false),
            'c164_next_recommendation_match' => $this->c164NextRecommendationMatches($c164),
            'c164_state_valid' => $this->c164StateComplete($c164),
        ];
        $artifact['plan_confirm_controlled_rollout_boundary_guard_summary'] = [
            'boundary_reviewed' => true,
            'boundary_confirmed' => (bool) ($options['controlled_rollout_boundary_confirmed'] ?? false),
            'boundary_open' => $pass,
            'controlled_rollout_only_confirmed' => (bool) ($options['controlled_rollout_only_confirmed'] ?? false),
            'boundary_executes_rollout' => false,
            'boundary_reads_activated_catalog' => false,
            'boundary_mutates_plan_confirm' => false,
            'boundary_free_publishes_output' => false,
        ];
        $artifact['watchlist_function_scope_summary'] = [
            'validation_completed' => true,
            'watchlist_function_scope_matches' => $this->watchlistFunctionScopeMatches($c164),
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'source_runtime_mode' => $c164['watchlist_function_runtime_mode'] ?? null,
            'expected_source_runtime_mode' => self::SOURCE_RUNTIME_MODE,
            'boundary_runtime_mode' => self::BOUNDARY_RUNTIME_MODE,
            'function_invoked_by_boundary' => false,
            'function_reserved_for_controlled_rollout_execution' => $pass,
        ];
        $artifact['candidate_scope_freeze_summary'] = [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c164),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_ready_for_controlled_rollout_execution' => $pass,
            'backup_ready_for_controlled_rollout_execution' => $pass,
            'comparator_ready_for_controlled_rollout_execution' => false,
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
        $artifact['c165_plan_confirm_controlled_rollout_boundary_decision'] = [
            'review_valid' => $pass,
            'boundary_decision' => $pass ? 'OPEN' : 'CLOSED',
            'controlled_rollout_boundary_confirmed' => (bool) ($options['controlled_rollout_boundary_confirmed'] ?? false),
            'c164_finalization_locked' => $load['hash_match'] && $load['file_sha1_match'],
            'c164_topic_complete' => (bool) ($c164['c164_topic_complete_after_finalization'] ?? false),
            'c165_is_distinct_topic' => true,
            'controlled_rollout_execution_performed' => false,
        ];
        $artifact['next_plan_confirm_controlled_rollout_execution_decision'] = [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::NEXT_EXECUTION : 'C165_TARGETED_C164_FINALIZATION_OR_BOUNDARY_REPAIR',
            'same_topic_c165_continues' => $pass,
            'controlled_rollout_execution_allowed_next' => $pass,
            'execution_requires_locked_c165_boundary_artifact' => $pass,
            'free_publication_allowed_next' => false,
            'unrestricted_rollout_allowed_next' => false,
        ];
        $artifact['weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_manifest'] = [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_controlled_rollout_boundary_review',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['actual_hash'],
            'source_file_sha1' => $load['actual_file_sha1'],
            'c164_completion_finalized' => (bool) ($c164['c164_topic_complete_after_finalization'] ?? false),
            'controlled_rollout_boundary_open' => $pass,
            'ready_for_controlled_rollout_execution' => $pass,
            'controlled_rollout_boundary_artifact_only' => true,
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
        $artifact['weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_checklist'] = [
            'c164_finalization_lock_reviewed' => true,
            'c164_finalization_state_reviewed' => true,
            'controlled_rollout_boundary_reviewed' => true,
            'controlled_rollout_boundary_confirmed' => (bool) ($options['controlled_rollout_boundary_confirmed'] ?? false),
            'controlled_rollout_only_confirmed' => (bool) ($options['controlled_rollout_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_rollout_executed_confirmed' => (bool) ($options['no_rollout_executed_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'artifact_only' => true,
            'controlled_rollout_execution_required_next' => $pass,
        ];
        $artifact['c165_candidate_plan_confirm_controlled_rollout_boundary_scorecard'] = [
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'c165_role' => 'primary_ready_for_controlled_rollout_execution', 'ready_for_controlled_rollout_execution' => $pass],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'c165_role' => 'backup_ready_for_controlled_rollout_execution', 'ready_for_controlled_rollout_execution' => $pass],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'c165_role' => 'comparator_only', 'ready_for_controlled_rollout_execution' => false],
        ];
        $artifact['publication_plan_confirm_rollout_safety_summary'] = [
            'validation_completed' => true,
            'source_safety_state_clean' => $this->safetyStateClean($c164),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'controlled_rollout_execution_allowed_next' => $pass,
        ];
        $artifact['documentation_hygiene_guard_summary'] = [
            'documentation_hygiene_guard_reviewed' => true,
            'c164_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c165_is_not_named_as_c164_next_boundary' => true,
            'c165_boundary_is_distinct_from_c164_completion' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
        $artifact['progress_summary'] = [
            'progress_marker' => 'PR-86_C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW',
            'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW',
            'c164_topic_complete' => (bool) ($c164['c164_topic_complete_after_finalization'] ?? false),
            'c165_topic_open' => $pass,
            'rollout_executed' => false,
        ];
        $artifact['planned_next_summary'] = [
            'planned_next_review' => $pass ? self::NEXT_EXECUTION : 'C165_TARGETED_C164_FINALIZATION_OR_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C165 controlled rollout execution with locked boundary evidence; no free or unrestricted publication' : 'repair C164 lock or C165 boundary evidence',
            'same_topic_c165_continues' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C165 controlled rollout boundary artifact hash',
                'locked C165 controlled rollout boundary file SHA1',
                'explicit controlled rollout execution approval',
                'activated catalog read limited to the controlled execution contract',
                'free publication remains disabled',
            ] : [],
        ];
        $artifact['diagnostics'] = [
            'C165 locks the corrected C164 finalization artifact before opening the controlled rollout boundary.',
            'C165 is a distinct PLAN/CONFIRM controlled rollout topic and does not repeat C164 activation completion.',
            'The boundary does not mutate PLAN/CONFIRM, read the activated catalog, execute rollout, or free-publish recommendations.',
            'The next same-topic execution may proceed only from the locked C165 boundary artifact.',
        ];

        return $artifact;
    }

    private function c164NextRecommendationMatches(array $c164): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_controlled_rollout_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c164, $path) !== self::RUN_CODE) {
                return false;
            }
        }

        return true;
    }

    private function c164StateComplete(array $c164): bool
    {
        foreach (self::REQUIRED_C164_TRUE_FIELDS as $field) {
            if (($c164[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C164_FALSE_FIELDS as $field) {
            if (($c164[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($c164['operator_decision'] ?? null) === 'GO'
            && (int) ($c164['controlled_completion_record_count'] ?? 0) > 0
            && trim((string) ($c164['controlled_completion_hash'] ?? '')) !== ''
            && $this->valueAt($c164, ['c164_go_decision_finalization_decision', 'review_valid']) === true
            && $this->valueAt($c164, ['c164_go_decision_finalization_decision', 'go_decision_finalized']) === true
            && $this->valueAt($c164, ['c164_go_decision_finalization_decision', 'post_handoff_activation_completion_closed']) === true
            && $this->valueAt($c164, ['next_plan_confirm_controlled_rollout_boundary_decision', 'review_valid']) === true
            && $this->valueAt($c164, ['next_plan_confirm_controlled_rollout_boundary_decision', 'next_is_concrete']) === true
            && $this->valueAt($c164, ['next_plan_confirm_controlled_rollout_boundary_decision', 'same_topic_c164_complete']) === true
            && $this->valueAt($c164, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest', 'go_decision_finalization_artifact_only']) === true
            && $this->valueAt($c164, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest', 'ready_for_plan_confirm_controlled_rollout_boundary_review']) === true
            && $this->valueAt($c164, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_checklist', 'artifact_only']) === true;
    }

    private function safetyStateClean(array $c164): bool
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
            if (($c164[$field] ?? null) !== false) {
                return false;
            }
        }

        return $this->valueAt($c164, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest', 'go_decision_finalization_used_for_free_publication']) === false
            && $this->valueAt($c164, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest', 'go_decision_finalization_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($c164, ['weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_go_decision_finalization_manifest', 'go_decision_finalization_used_for_live_plan_confirm_rollout']) === false;
    }

    private function candidateScopeMatches(array $c164): bool
    {
        return ($c164['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c164['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c164['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c164['a01_remains_comparator_only'] ?? null) === true;
    }

    private function watchlistFunctionScopeMatches(array $c164): bool
    {
        return ($c164['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($c164['watchlist_function_runtime_mode'] ?? null) === self::SOURCE_RUNTIME_MODE
            && ($c164['watchlist_function_primary_candidate_observed'] ?? null) === true
            && ($c164['watchlist_function_backup_candidate_observed'] ?? null) === true
            && ($c164['watchlist_function_comparator_candidate_observed'] ?? null) === false;
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $pass): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $pass
            ? 'C165_CONTROLLED_ROLLOUT_BOUNDARY_OPEN_READY_FOR_SAME_TOPIC_EXECUTION_NO_ROLLOUT_OR_FREE_PUBLICATION_EXECUTED'
            : $status;
        $artifact['next_step_recommendation'] = $pass ? self::NEXT_EXECUTION : 'C165_TARGETED_C164_FINALIZATION_OR_BOUNDARY_REPAIR';
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $pass ? 0 : 1,
            'failures' => $pass ? [] : [$status],
            'attribution' => $pass ? 'NONE' : 'C165_BOUNDARY_INPUT_OR_CONFIRMATION',
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
            'expected_c164_finalization_hash' => $load['expected_hash'],
            'actual_c164_finalization_hash' => $load['actual_hash'],
            'c164_finalization_hash_match' => $load['hash_match'],
            'expected_c164_finalization_file_sha1' => $load['expected_file_sha1'],
            'actual_c164_finalization_file_sha1' => $load['actual_file_sha1'],
            'c164_finalization_file_sha1_match' => $load['file_sha1_match'],
            'c164_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function sourceLockSummary(array $load): array
    {
        return [
            'source_artifact' => 'C164_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW',
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
