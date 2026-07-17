<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionService
{
    public const RUN_CODE = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION';
    public const PHASE_LABEL = 'PR-61 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION';
    public const ARTIFACT_TYPE = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION';

    public const DEFAULT_C161_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-boundary-review.json';
    public const DEFAULT_EXPECTED_C161_BOUNDARY_HASH = 'fe92324430bbad2f9caa74538976a9225a4a2807';
    public const DEFAULT_EXPECTED_C161_BOUNDARY_FILE_SHA1 = '8BEEA9838E6C22646331A151A38404A7FE2E4CC5';
    public const DEFAULT_CONTROLLED_COMPLETION_PATH = 'storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-execution.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C161_BOUNDARY_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP';
    private const EXPECTED_C161_BOUNDARY_PHASE_LABEL = 'PR-60 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW';
    private const EXPECTED_C161_BOUNDARY_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C161_RESULT_REVIEW_RECOMMENDATION = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW';

    private const PASS_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_PASSED_CONTROLLED_COMPLETION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const COMPLETION_EXECUTION_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_COMPLETION_EXECUTION_CONFIRMATION_MISSING';
    private const CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C161_BOUNDARY_LOCK_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_ARTIFACT_LOCK_MISMATCH';
    private const C161_BOUNDARY_FILE_SHA1_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_FILE_SHA1_LOCK_MISMATCH';
    private const C161_BOUNDARY_CONVERT_FROM_JSON_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C161_BOUNDARY_STATUS_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_STATUS_MISMATCH';
    private const C161_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_PHASE_LABEL_MISMATCH';
    private const C161_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH';
    private const C161_BOUNDARY_INCOMPLETE_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_C161_COMPLETION_BOUNDARY_INCOMPLETE';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C161_BOUNDARY_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_pass',
        'production_live_runtime_plan_confirm_completion_boundary_review_pass',
        'plan_confirm_completion_boundary_cleared',
        'completion_boundary_cleared',
        'completion_boundary_confirmed',
        'c160_topic_complete_confirmed',
        'plan_confirm_closed_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'operator_go_decision',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'plan_confirm_closed',
        'c160_topic_complete_after_finalization',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_execution',
        'production_live_runtime_plan_confirm_completion_execution_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_execution_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_boundary_manifest_created',
        'weekly_swing_watchlist_plan_confirm_result_reviewed',
        'weekly_swing_watchlist_plan_confirm_controlled_execution_executed',
        'weekly_swing_watchlist_plan_confirm_controlled_artifact_created',
        'weekly_swing_watchlist_plan_confirm_controlled_only',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c160_finalization_lock_valid',
        'c160_go_decision_finalization_valid',
        'c160_finalization_convert_from_json_pass',
        'primary_candidate_ready_for_plan_confirm_completion_execution',
        'backup_candidate_ready_for_plan_confirm_completion_execution',
        'a01_remains_comparator_only',
        'c161_plan_confirm_completion_boundary_review_only',
        'c161_controlled_plan_confirm_completion_only',
        'c161_not_publication',
        'c161_not_unrestricted_publication',
        'c161_not_plan_confirm_mutation',
        'c161_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C161_BOUNDARY_FALSE_FIELDS = [
        'comparator_candidate_ready_for_plan_confirm_completion_execution',
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
        'storage/app/watchlist/backtest/c161-*completion-execution*-test.json',
        'storage/app/watchlist/backtest/c161-*completion*-execution*-test.json',
        'storage/app/watchlist/backtest/c161-*negative-*-test.json',
        'storage/app/watchlist/backtest/c161-*missing-*-test.json',
        'storage/app/watchlist/backtest/c161-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c161-*invalid-*-test.json',
    ];

    public function execute(
        string $c161BoundaryArtifact = self::DEFAULT_C161_BOUNDARY_ARTIFACT,
        string $expectedC161BoundaryHash = self::DEFAULT_EXPECTED_C161_BOUNDARY_HASH,
        string $expectedC161BoundaryFileSha1 = self::DEFAULT_EXPECTED_C161_BOUNDARY_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        string $controlledCompletionPath = self::DEFAULT_CONTROLLED_COMPLETION_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt, $controlledCompletionPath);
        $load = $this->loadArtifactLock($c161BoundaryArtifact, $expectedC161BoundaryHash, $expectedC161BoundaryFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C161_BOUNDARY_LOCK_MISMATCH_STATUS, 'C161 completion boundary artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, []);
            $artifact['c161_boundary_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C161_BOUNDARY_CONVERT_FROM_JSON_STATUS, 'C161 completion boundary artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C161_BOUNDARY_LOCK_MISMATCH_STATUS, 'C161 completion boundary artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C161_BOUNDARY_FILE_SHA1_MISMATCH_STATUS, 'C161 completion boundary file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $boundary = $load['payload'];
        if (($boundary['status'] ?? null) !== self::EXPECTED_C161_BOUNDARY_STATUS || ($boundary['reason_code'] ?? null) !== self::EXPECTED_C161_BOUNDARY_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C161_BOUNDARY_STATUS_MISMATCH_STATUS, 'C161 completion boundary status/reason is not completion execution ready.', $outputPath, $overwrite);
        }
        if (($boundary['phase_label'] ?? null) !== self::EXPECTED_C161_BOUNDARY_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C161_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS, 'C161 completion boundary phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c161BoundaryNextRecommendationMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C161_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C161 completion boundary next recommendation is not C161 completion execution.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C161 boundary already published, unlocked publication, mutated PLAN/CONFIRM, read activated catalog, or executed live rollout.', $outputPath, $overwrite);
        }
        if (! $this->c161BoundaryComplete($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C161_BOUNDARY_INCOMPLETE_STATUS, 'C161 completion boundary evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C161 completion boundary candidate scope does not match completion execution scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::APPROVAL_MISSING_STATUS, 'C161 completion execution requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['completion_execution_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::COMPLETION_EXECUTION_CONFIRMATION_MISSING_STATUS, 'C161 requires --completion-execution-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_completion_only_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::CONTROLLED_COMPLETION_ONLY_CONFIRMATION_MISSING_STATUS, 'C161 requires --controlled-completion-only-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C161 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C161 requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C161 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false, []);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $controlledCompletion = $this->writeControlledCompletion(
            $this->controlledCompletionPayload($createdAt, $load),
            $controlledCompletionPath,
            $overwrite
        );
        $artifact = $this->completeSections($artifact, $load, $options, true, $controlledCompletion);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C161 executes controlled PLAN/CONFIRM completion evidence only. PLAN/CONFIRM remains unchanged, activated-catalog reads remain disabled, live rollout remains disabled, and free publication remains locked.';
        $artifact['diagnostic_conclusion'] = 'C161_CONTROLLED_PLAN_CONFIRM_COMPLETION_EXECUTION_COMPLETED_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C161_RESULT_REVIEW_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($controlledCompletion));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $controlledCompletionPath): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-61',
            'internal_checkpoint' => 'C161',
            'topic_code' => 'C161_PLAN_CONFIRM_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_EXECUTION',
            'status' => 'C161_PLAN_CONFIRM_COMPLETION_EXECUTION_NOT_RUN',
            'reason_code' => 'C161_PLAN_CONFIRM_COMPLETION_EXECUTION_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_completion_path' => $controlledCompletionPath,
            'controlled_completion_hash' => null,
            'controlled_completion_file_sha1' => null,
            'controlled_completion_record_count' => 0,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_pass' => false,
            'production_live_runtime_plan_confirm_completion_execution_pass' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_result_review' => false,
            'production_live_runtime_plan_confirm_completion_result_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'completion_execution_confirmed' => false,
            'controlled_completion_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'completion_boundary_cleared' => false,
            'plan_confirm_closed' => false,
            'weekly_swing_watchlist_plan_confirm_result_reviewed' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_only' => true,
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
            'c161_boundary_lock_valid' => false,
            'c161_completion_boundary_valid' => false,
            'c161_boundary_convert_from_json_pass' => false,
            'operator_approved' => false,
            'approval_reference' => '',
            'primary_candidate_completion_controlled_executed' => false,
            'backup_candidate_completion_controlled_executed' => false,
            'comparator_candidate_completion_controlled_executed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c161_plan_confirm_completion_execution_only' => true,
            'c161_controlled_completion_only' => true,
            'c161_not_plan_confirm_mutation' => true,
            'c161_not_live_plan_confirm_rollout' => true,
            'c161_not_publication' => true,
            'c161_topic_number_retained_for_result_review' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'next_step_recommendation' => 'C161_PLAN_CONFIRM_COMPLETION_EXECUTION_NOT_READY',
            'diagnostic_conclusion' => 'C161_PLAN_CONFIRM_COMPLETION_EXECUTION_NOT_RUN',
        ];
    }

    private function passingTopLevelState(array $controlledCompletion): array
    {
        return [
            'controlled_completion_path' => $controlledCompletion['controlled_completion_path'],
            'controlled_completion_hash' => $controlledCompletion['controlled_completion_hash'],
            'controlled_completion_file_sha1' => $controlledCompletion['controlled_completion_file_sha1'],
            'controlled_completion_record_count' => $controlledCompletion['controlled_completion_record_count'],
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_pass' => true,
            'production_live_runtime_plan_confirm_completion_execution_pass' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_result_review' => true,
            'production_live_runtime_plan_confirm_completion_result_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_result_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => true,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => true,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'completion_execution_confirmed' => true,
            'controlled_completion_only_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'completion_boundary_cleared' => true,
            'plan_confirm_closed' => true,
            'weekly_swing_watchlist_plan_confirm_result_reviewed' => true,
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => true,
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => true,
            'weekly_swing_watchlist_plan_confirm_controlled_only' => true,
            'weekly_swing_watchlist_official_output_generated' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => true,
            'weekly_swing_watchlist_runtime_active' => true,
            'weekly_swing_watchlist_live_output_enabled' => true,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => true,
            'c161_boundary_lock_valid' => true,
            'c161_completion_boundary_valid' => true,
            'c161_boundary_convert_from_json_pass' => true,
            'operator_approved' => true,
            'primary_candidate_completion_controlled_executed' => true,
            'backup_candidate_completion_controlled_executed' => true,
            'comparator_candidate_completion_controlled_executed' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass, array $controlledCompletion): array
    {
        $boundary = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryPaths = array_values((array) ($options['temporary_negative_artifact_paths'] ?? []));
        $reference = trim((string) ($options['approval_reference'] ?? ''));
        $artifact['c161_boundary_lock_validation_summary'] = $this->c161BoundaryLockValidationSummary($load);
        $artifact['c161_boundary_carry_forward_summary'] = $this->c161BoundaryCarryForwardSummary($boundary);
        $artifact['controlled_completion_execution_summary'] = $this->controlledCompletionExecutionSummary($controlledCompletion, $pass);
        $artifact['controlled_completion_manifest'] = $this->controlledCompletionManifest($controlledCompletion, $pass);
        $artifact['controlled_completion_checklist'] = $this->controlledCompletionChecklist($pass, $options);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($boundary, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($boundary, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryPaths);
        $artifact['c161_candidate_completion_execution_scorecard'] = $this->candidateScorecard($pass);
        $artifact['next_plan_confirm_completion_result_review_decision'] = $this->nextResultReviewDecision($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact = array_merge($artifact, [
            'completion_boundary_cleared' => $pass ? true : (bool) ($boundary['completion_boundary_cleared'] ?? false),
            'plan_confirm_closed' => $pass ? true : (bool) ($boundary['plan_confirm_closed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_result_reviewed' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_controlled_only' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_controlled_only'] ?? true),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($boundary['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($boundary['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($boundary['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($boundary['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($boundary['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($boundary['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c161_boundary_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
            'c161_completion_boundary_valid' => $this->c161BoundaryComplete($boundary),
            'c161_boundary_convert_from_json_pass' => $load['convert_from_json_pass'],
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => $reference,
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => (string) ($boundary['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($boundary['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($boundary['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'a01_remains_comparator_only' => (bool) ($boundary['a01_remains_comparator_only'] ?? true),
        ]);
        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $flag) {
            $artifact[$flag] = false;
        }

        return $artifact;
    }

    private function controlledCompletionPayload(string $createdAt, array $load): array
    {
        $boundary = is_array($load['payload']) ? $load['payload'] : [];
        return [
            'artifact_type' => 'C161_WEEKLY_SWING_WATCHLIST_CONTROLLED_PLAN_CONFIRM_COMPLETION',
            'created_at' => $createdAt,
            'controlled_completion_hash' => null,
            'source_c161_boundary_artifact' => $load['path'],
            'source_c161_boundary_hash' => $load['actual_hash'],
            'source_c161_boundary_file_sha1' => $load['actual_file_sha1'],
            'plan_confirm_completion_mode' => 'controlled',
            'plan_confirm_completion_state' => 'controlled_completion_executed',
            'baseline_plan_confirm_state' => 'closed_and_unchanged',
            'activated_catalog_read_state' => 'not_enabled',
            'live_rollout_state' => 'not_executed',
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
            'result_review_required_next' => true,
            'output_rows' => [
                [
                    'rank' => 1,
                    'candidate_code' => self::PRIMARY_CANDIDATE,
                    'role' => 'primary',
                    'completion_execution_state' => 'controlled_executed',
                    'plan_confirm_state' => 'closed_and_unchanged',
                    'publication_state' => 'not_free_published',
                    'live_rollout_state' => 'not_executed',
                ],
                [
                    'rank' => 2,
                    'candidate_code' => self::BACKUP_CANDIDATE,
                    'role' => 'backup',
                    'completion_execution_state' => 'controlled_executed',
                    'plan_confirm_state' => 'closed_and_unchanged',
                    'publication_state' => 'not_free_published',
                    'live_rollout_state' => 'not_executed',
                ],
            ],
            'comparator_candidate' => [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'comparator_only',
                'a01_remains_comparator_only' => (bool) ($boundary['a01_remains_comparator_only'] ?? true),
                'completion_execution_state' => 'not_executed',
            ],
        ];
    }

    private function writeControlledCompletion(array $payload, string $path, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) {
            $raw = (string) file_get_contents($path);
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return [
                    'controlled_completion_path' => $path,
                    'controlled_completion_hash' => (string) ($decoded['controlled_completion_hash'] ?? ''),
                    'controlled_completion_file_sha1' => strtoupper(sha1($raw)),
                    'controlled_completion_record_count' => is_array($decoded['output_rows'] ?? null) ? count($decoded['output_rows']) : 0,
                    'write_skipped_existing_controlled_completion' => true,
                ];
            }
        }
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $hashPayload = $payload;
        $hashPayload['controlled_completion_hash'] = null;
        $payload['controlled_completion_hash'] = sha1(json_encode($hashPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        $raw = (string) file_get_contents($path);

        return [
            'controlled_completion_path' => $path,
            'controlled_completion_hash' => $payload['controlled_completion_hash'],
            'controlled_completion_file_sha1' => strtoupper(sha1($raw)),
            'controlled_completion_record_count' => is_array($payload['output_rows'] ?? null) ? count($payload['output_rows']) : 0,
            'write_skipped_existing_controlled_completion' => false,
        ];
    }

    private function c161BoundaryNextRecommendationMatches(array $boundary): bool
    {
        return ($boundary['next_step_recommendation'] ?? null) === self::RUN_CODE
            && $this->valueAt($boundary, ['next_plan_confirm_completion_execution_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($boundary, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE;
    }

    private function c161BoundaryComplete(array $boundary): bool
    {
        foreach (self::REQUIRED_C161_BOUNDARY_TRUE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C161_BOUNDARY_FALSE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($boundary['topic_code'] ?? null) === 'C161_PLAN_CONFIRM_COMPLETION'
            && ($boundary['topic_stage'] ?? null) === 'PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW'
            && ($boundary['boundary_go_decision'] ?? null) === 'BOUNDARY_CLEARED_GO'
            && ($boundary['operator_decision'] ?? null) === 'GO'
            && $this->valueAt($boundary, ['c161_completion_boundary_decision', 'review_pass']) === true
            && $this->valueAt($boundary, ['c161_completion_boundary_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest', 'manifest_created']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest', 'boundary_artifact_only']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest', 'ready_for_plan_confirm_completion_execution']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest', 'completion_boundary_used_for_free_publication']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest', 'completion_boundary_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest', 'completion_boundary_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_boundary_checklist', 'completion_boundary_reviewed']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_boundary_checklist', 'artifact_only']) === true
            && $this->valueAt($boundary, ['next_plan_confirm_completion_execution_decision', 'review_valid']) === true
            && $this->valueAt($boundary, ['next_plan_confirm_completion_execution_decision', 'same_topic_c161_continues']) === true;
    }

    private function publicationAndPlanGuardClean(array $boundary): bool
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
        ] as $flag) {
            if (($boundary[$flag] ?? null) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $boundary): bool
    {
        return ($boundary['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($boundary['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($boundary['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($boundary['primary_candidate_ready_for_plan_confirm_completion_execution'] ?? null) === true
            && ($boundary['backup_candidate_ready_for_plan_confirm_completion_execution'] ?? null) === true
            && ($boundary['comparator_candidate_ready_for_plan_confirm_completion_execution'] ?? null) === false
            && ($boundary['a01_remains_comparator_only'] ?? null) === true
            && ($boundary['a01_promoted'] ?? false) === false
            && ($boundary['candidate_promotion_executed'] ?? false) === false
            && ($boundary['candidate_rerank_executed'] ?? false) === false
            && ($boundary['strategy_retune_executed'] ?? false) === false
            && ($boundary['scoring_mutation_executed'] ?? false) === false
            && ($boundary['catalog_selection_changed'] ?? false) === false
            && ($boundary['runtime_selection_changed'] ?? false) === false;
    }

    private function c161BoundaryLockValidationSummary(array $load): array
    {
        return [
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
            'completion_boundary_ready' => is_array($load['payload']) && $this->c161BoundaryComplete($load['payload']),
        ];
    }

    private function c161BoundaryCarryForwardSummary(array $boundary): array
    {
        return [
            'validation_completed' => true,
            'c161_boundary_valid' => $this->c161BoundaryComplete($boundary),
            'topic_code' => (string) ($boundary['topic_code'] ?? ''),
            'topic_stage' => (string) ($boundary['topic_stage'] ?? ''),
            'completion_boundary_cleared' => (bool) ($boundary['completion_boundary_cleared'] ?? false),
            'ready_for_plan_confirm_completion_execution' => (bool) ($boundary['ready_for_weekly_swing_watchlist_plan_confirm_completion_execution'] ?? false),
            'same_topic_c161_continues' => (bool) $this->valueAt($boundary, ['next_plan_confirm_completion_execution_decision', 'same_topic_c161_continues']),
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function controlledCompletionExecutionSummary(array $controlledCompletion, bool $pass): array
    {
        return [
            'controlled_completion_execution_executed' => $pass,
            'controlled_completion_artifact_created' => $pass,
            'controlled_completion_path' => $controlledCompletion['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $controlledCompletion['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $controlledCompletion['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => $controlledCompletion['controlled_completion_record_count'] ?? 0,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
            'free_publication_executed' => false,
        ];
    }

    private function controlledCompletionManifest(array $controlledCompletion, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'plan_confirm_completion_execution',
            'controlled_completion_path' => $controlledCompletion['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $controlledCompletion['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $controlledCompletion['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => $controlledCompletion['controlled_completion_record_count'] ?? 0,
            'plan_confirm_completion_mode' => 'controlled',
            'plan_confirm_completion_state' => $pass ? 'controlled_completion_executed' : 'not_executed',
            'baseline_plan_confirm_state' => 'closed_and_unchanged',
            'activated_catalog_read_state' => 'not_enabled',
            'live_rollout_state' => 'not_executed',
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'completion_result_review_required_next' => $pass,
        ];
    }

    private function controlledCompletionChecklist(bool $pass, array $options): array
    {
        return [
            'c161_boundary_artifact_locked' => true,
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'controlled_completion_execution_completed' => $pass,
            'plan_confirm_mutation_forbidden_in_c161_execution' => true,
            'activated_catalog_read_forbidden_in_c161_execution' => true,
            'live_plan_confirm_rollout_forbidden_in_c161_execution' => true,
            'free_publication_forbidden_in_c161_execution' => true,
            'result_review_required_next' => $pass,
            'same_topic_number_for_next_stage' => true,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $boundary, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'boundary_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($boundary),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($boundary['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => $pass,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $source, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($source),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_completion_controlled_executed' => $pass,
            'backup_candidate_completion_controlled_executed' => $pass,
            'comparator_candidate_completion_controlled_executed' => false,
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
            'completion_execution_confirmation_required' => true,
            'completion_execution_confirmed' => (bool) ($options['completion_execution_confirmed'] ?? false),
            'controlled_completion_only_confirmation_required' => true,
            'controlled_completion_only_confirmed' => (bool) ($options['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c161_role' => 'primary_candidate_completion_controlled_executed',
                'completion_controlled_executed' => $pass,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c161_role' => 'backup_candidate_completion_controlled_executed',
                'completion_controlled_executed' => $pass,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c161_role' => 'comparator_only_candidate',
                'completion_controlled_executed' => false,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'free_published' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function nextResultReviewDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C161_RESULT_REVIEW_RECOMMENDATION : 'C161_TARGETED_COMPLETION_EXECUTION_REPAIR',
            'next_scope' => $pass ? 'same-topic C161 PLAN/CONFIRM completion result review only; controlled completion evidence exists, while PLAN/CONFIRM mutation, activated-catalog reads, live rollout, and free publication remain disabled' : 'targeted boundary lock, confirmation, publication/PLAN guard, or cleanup repair',
            'same_topic_c161_continues' => true,
            'next_requires_locked_c161_completion_execution_artifact' => $pass,
            'next_requires_locked_controlled_completion_artifact' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c161_boundary_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c161_boundary_artifact_not_modified' => true,
            'c161_execution_is_controlled_completion_not_live_rollout' => true,
            'c161_topic_number_retained_for_next_stage' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-61_C161_PLAN_CONFIRM_COMPLETION_EXECUTION',
            'topic_code' => 'C161_PLAN_CONFIRM_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_EXECUTION',
            'c161_boundary_carried_forward' => true,
            'controlled_completion_execution_pass' => $pass,
            'same_topic_number_for_next_stage' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C161_RESULT_REVIEW_RECOMMENDATION : 'C161_TARGETED_COMPLETION_EXECUTION_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C161 PLAN/CONFIRM completion result review only; controlled completion execution evidence exists, while PLAN/CONFIRM mutation, activated-catalog reads, live rollout, and free publication remain disabled' : 'targeted boundary lock, confirmation, publication/PLAN guard, or cleanup repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass ? [
                'locked C161 PLAN/CONFIRM completion execution artifact hash',
                'locked C161 PLAN/CONFIRM completion execution file SHA1',
                'locked controlled completion artifact hash',
                'locked controlled completion artifact file SHA1',
                'PLAN/CONFIRM unchanged evidence',
                'live rollout still disabled',
                'free publication still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C161 completion execution validates C161 boundary artifact_hash and file SHA1 locks before controlled completion execution.',
            'C161 completion execution creates controlled PLAN/CONFIRM completion evidence only.',
            'C161 completion execution does not mutate PLAN/CONFIRM, read the activated catalog, execute live PLAN/CONFIRM rollout, or free-publish output.',
            'C161 completion execution keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C161 completion execution may only recommend same-topic PLAN/CONFIRM completion result review next.',
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
            'c161_plan_confirm_completion_boundary' => [
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
            'expected_c161_boundary_hash' => $load['expected_hash'],
            'actual_c161_boundary_hash' => $load['actual_hash'],
            'c161_boundary_hash_match' => $load['hash_match'],
            'expected_c161_boundary_file_sha1' => $load['expected_file_sha1'],
            'actual_c161_boundary_file_sha1' => $load['actual_file_sha1'],
            'c161_boundary_file_sha1_match' => $load['file_sha1_match'],
            'c161_boundary_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
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
        $expectTopLevelKey = false;
        $seen = [];
        $duplicates = [];

        for ($i = 0; $i < $length; $i++) {
            $char = $raw[$i];
            if ($char === '"') {
                $start = $i;
                $i++;
                $escaped = false;
                while ($i < $length) {
                    $inner = $raw[$i];
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($inner === '\\') {
                        $escaped = true;
                    } elseif ($inner === '"') {
                        break;
                    }
                    $i++;
                }
                $token = substr($raw, $start, $i - $start + 1);
                if ($depth === 1 && $expectTopLevelKey) {
                    $j = $i + 1;
                    while ($j < $length && ctype_space($raw[$j])) {
                        $j++;
                    }
                    if ($j < $length && $raw[$j] === ':') {
                        $decoded = json_decode($token, true);
                        if (is_string($decoded)) {
                            $lower = strtolower($decoded);
                            if (array_key_exists($lower, $seen) && ! in_array($decoded, $duplicates, true)) {
                                $duplicates[] = $decoded;
                            }
                            $seen[$lower] = $decoded;
                        }
                        $expectTopLevelKey = false;
                    }
                }
                continue;
            }
            if ($char === '{') {
                $depth++;
                if ($depth === 1) {
                    $expectTopLevelKey = true;
                }
                continue;
            }
            if ($char === '}') {
                if ($depth === 1) {
                    $expectTopLevelKey = false;
                }
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($char === '[') {
                $depth++;
                continue;
            }
            if ($char === ']') {
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($char === ',' && $depth === 1) {
                $expectTopLevelKey = true;
            }
        }
        sort($duplicates);

        return array_values($duplicates);
    }

    private function valueAt(array $source, array $path)
    {
        $value = $source;
        foreach ($path as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
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

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => array_values(array_filter($status)),
            'failure_count' => count(array_values(array_filter($status))),
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['artifact_path'] = $outputPath;
            $artifact['write_skipped_existing_output'] = true;

            return $artifact;
        }
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $hashPayload = $artifact;
        $hashPayload['artifact_hash'] = null;
        unset($hashPayload['artifact_path']);
        $artifact['artifact_hash'] = sha1(json_encode($hashPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $artifact['artifact_path'] = $outputPath;
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $artifact;
    }
}
