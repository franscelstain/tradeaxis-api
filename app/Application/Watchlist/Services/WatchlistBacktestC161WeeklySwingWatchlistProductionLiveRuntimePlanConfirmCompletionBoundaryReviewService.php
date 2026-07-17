<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewService
{
    public const RUN_CODE = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-60 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW';

    public const DEFAULT_C160_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C160_FINALIZATION_HASH = 'f6d2ca065099a5f07d7e6f53a3263b7b75293b2c';
    public const DEFAULT_EXPECTED_C160_FINALIZATION_FILE_SHA1 = 'B7F94670FC798F62B129AF76D87C1EAE9813B241';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C160_FINALIZATION_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_PLAN_CONFIRM_CLOSED_READY_FOR_COMPLETION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C160_FINALIZATION_PHASE_LABEL = 'PR-59 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C160_FINALIZATION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C161_COMPLETION_EXECUTION_RECOMMENDATION = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION';

    private const PASS_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_BOUNDARY_CONFIRMATION_MISSING';
    private const C160_TOPIC_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_TOPIC_COMPLETE_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_CLOSED_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_CLOSED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C160_FINALIZATION_LOCK_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const C160_FINALIZATION_FILE_SHA1_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const C160_FINALIZATION_CONVERT_FROM_JSON_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C160_FINALIZATION_STATUS_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_STATUS_MISMATCH';
    private const C160_FINALIZATION_PHASE_LABEL_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_PHASE_LABEL_MISMATCH';
    private const C160_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C160_FINALIZATION_STATE_INVALID_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_C160_FINALIZATION_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C160_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_go_decision_finalization_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_go_decision_finalization_review_pass',
        'production_live_runtime_plan_confirm_go_decision_finalization_review_pass',
        'operator_go_decision',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'plan_confirm_finalization_confirmed',
        'plan_confirm_closed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_boundary_review',
        'production_live_runtime_plan_confirm_completion_boundary_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_boundary_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest_created',
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
        'c160_operator_go_no_go_lock_valid',
        'c160_operator_go_no_go_review_valid',
        'c160_operator_go_no_go_convert_from_json_pass',
        'c160_result_review_lock_valid',
        'c160_plan_confirm_result_review_valid',
        'controlled_plan_confirm_lock_valid',
        'controlled_plan_confirm_integrity_valid',
        'primary_candidate_ready_for_plan_confirm_completion_boundary_review',
        'backup_candidate_ready_for_plan_confirm_completion_boundary_review',
        'a01_remains_comparator_only',
        'c160_plan_confirm_go_decision_finalization_review_only',
        'c160_controlled_plan_confirm_only',
        'c160_not_publication',
        'c160_not_unrestricted_publication',
        'c160_not_plan_confirm_mutation',
        'c160_not_live_plan_confirm_rollout',
        'c160_topic_complete_after_finalization',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C160_FALSE_FIELDS = [
        'comparator_candidate_ready_for_plan_confirm_completion_boundary_review',
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
        'storage/app/watchlist/backtest/c161-*completion-boundary*-test.json',
        'storage/app/watchlist/backtest/c161-*negative-*-test.json',
        'storage/app/watchlist/backtest/c161-*missing-*-test.json',
        'storage/app/watchlist/backtest/c161-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c161-*invalid-*-test.json',
    ];

    public function execute(
        string $c160FinalizationArtifact = self::DEFAULT_C160_FINALIZATION_ARTIFACT,
        string $expectedC160FinalizationHash = self::DEFAULT_EXPECTED_C160_FINALIZATION_HASH,
        string $expectedC160FinalizationFileSha1 = self::DEFAULT_EXPECTED_C160_FINALIZATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c160FinalizationArtifact, $expectedC160FinalizationHash, $expectedC160FinalizationFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C160_FINALIZATION_LOCK_MISMATCH_STATUS, 'C160 GO decision finalization artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c160_finalization_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C160_FINALIZATION_CONVERT_FROM_JSON_STATUS, 'C160 GO decision finalization artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C160_FINALIZATION_LOCK_MISMATCH_STATUS, 'C160 GO decision finalization artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C160_FINALIZATION_FILE_SHA1_MISMATCH_STATUS, 'C160 GO decision finalization file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c160 = $load['payload'];
        if (($c160['status'] ?? null) !== self::EXPECTED_C160_FINALIZATION_STATUS || ($c160['reason_code'] ?? null) !== self::EXPECTED_C160_FINALIZATION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C160_FINALIZATION_STATUS_MISMATCH_STATUS, 'C160 finalization status/reason is not completion-boundary ready.', $outputPath, $overwrite);
        }
        if (($c160['phase_label'] ?? null) !== self::EXPECTED_C160_FINALIZATION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C160_FINALIZATION_PHASE_LABEL_MISMATCH_STATUS, 'C160 finalization phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c160NextRecommendationMatches($c160)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C160_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C160 finalization next recommendation is not C161 PLAN/CONFIRM completion boundary review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($c160)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C160 finalization evidence has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c160FinalizationComplete($c160)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C160_FINALIZATION_STATE_INVALID_STATUS, 'C160 finalization evidence is incomplete for C161 completion boundary.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c160)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C160 finalization candidate scope does not match locked C161 completion boundary scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C161 completion boundary requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['completion_boundary_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::BOUNDARY_CONFIRMATION_MISSING_STATUS, 'C161 requires --completion-boundary-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c160_topic_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C160_TOPIC_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C161 requires --c160-topic-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_closed_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_CLOSED_CONFIRMATION_MISSING_STATUS, 'C161 requires --plan-confirm-closed-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C161 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C161 requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C161 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C161 clears the PLAN/CONFIRM completion boundary after locked C160 GO finalization. This is still artifact-only; PLAN/CONFIRM mutation, live rollout, unrestricted publication, and free publication remain locked.';
        $artifact['diagnostic_conclusion'] = 'C161_PLAN_CONFIRM_COMPLETION_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C161_COMPLETION_EXECUTION_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-60',
            'internal_checkpoint' => 'C161',
            'topic_code' => 'C161_PLAN_CONFIRM_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW',
            'status' => 'C161_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'reason_code' => 'C161_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_boundary_review_pass' => false,
            'plan_confirm_completion_boundary_cleared' => false,
            'completion_boundary_cleared' => false,
            'completion_boundary_confirmed' => false,
            'c160_topic_complete_confirmed' => false,
            'plan_confirm_closed_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'boundary_go_decision' => 'NO_GO',
            'operator_decision' => 'NO_GO',
            'operator_go_decision' => false,
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'plan_confirm_closed' => false,
            'c160_topic_complete_after_finalization' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_execution' => false,
            'production_live_runtime_plan_confirm_completion_execution_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_execution_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_boundary_manifest_created' => false,
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
            'c160_finalization_lock_valid' => false,
            'c160_go_decision_finalization_valid' => false,
            'c160_finalization_convert_from_json_pass' => false,
            'primary_candidate_ready_for_plan_confirm_completion_execution' => false,
            'backup_candidate_ready_for_plan_confirm_completion_execution' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_execution' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c161_plan_confirm_completion_boundary_review_only' => true,
            'c161_controlled_plan_confirm_completion_only' => true,
            'c161_not_publication' => true,
            'c161_not_unrestricted_publication' => true,
            'c161_not_plan_confirm_mutation' => true,
            'c161_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'next_step_recommendation' => 'C161_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_NOT_READY',
            'diagnostic_conclusion' => 'C161_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_NOT_RUN',
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_boundary_review_pass' => true,
            'plan_confirm_completion_boundary_cleared' => true,
            'completion_boundary_cleared' => true,
            'completion_boundary_confirmed' => true,
            'c160_topic_complete_confirmed' => true,
            'plan_confirm_closed_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'boundary_go_decision' => 'BOUNDARY_CLEARED_GO',
            'operator_decision' => 'GO',
            'operator_go_decision' => true,
            'operator_go_decision_confirmed' => true,
            'go_decision_finalized' => true,
            'plan_confirm_closed' => true,
            'c160_topic_complete_after_finalization' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_execution' => true,
            'production_live_runtime_plan_confirm_completion_execution_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_execution_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_boundary_manifest_created' => true,
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
            'c160_finalization_lock_valid' => true,
            'c160_go_decision_finalization_valid' => true,
            'c160_finalization_convert_from_json_pass' => true,
            'primary_candidate_ready_for_plan_confirm_completion_execution' => true,
            'backup_candidate_ready_for_plan_confirm_completion_execution' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_execution' => false,
            'a01_remains_comparator_only' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c160 = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryPaths = array_values((array) ($options['temporary_negative_artifact_paths'] ?? []));
        $artifact['c160_finalization_lock_validation_summary'] = $this->c160FinalizationLockValidationSummary($load);
        $artifact['c160_finalization_carry_forward_summary'] = $this->c160FinalizationCarryForwardSummary($c160);
        $artifact['plan_confirm_completion_boundary_guard_summary'] = $this->planConfirmCompletionBoundaryGuardSummary($c160, $pass, $options);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c160);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryPaths);
        $artifact['c161_completion_boundary_decision'] = $this->completionBoundaryDecision($pass, $options);
        $artifact['next_plan_confirm_completion_execution_decision'] = $this->nextPlanConfirmCompletionExecutionDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_boundary_manifest'] = $this->completionBoundaryManifest($c160, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_boundary_checklist'] = $this->completionBoundaryChecklist($pass, $options);
        $artifact['c161_candidate_plan_confirm_completion_boundary_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($c160);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        $artifact = array_merge($artifact, [
            'operator_decision' => $pass ? 'GO' : (string) ($c160['operator_decision'] ?? 'NO_GO'),
            'operator_go_decision' => $pass ? true : (bool) ($c160['operator_go_decision'] ?? false),
            'operator_go_decision_confirmed' => $pass ? true : (bool) ($c160['operator_go_decision_confirmed'] ?? false),
            'go_decision_finalized' => $pass ? true : (bool) ($c160['go_decision_finalized'] ?? false),
            'plan_confirm_closed' => $pass ? true : (bool) ($c160['plan_confirm_closed'] ?? false),
            'c160_topic_complete_after_finalization' => $pass ? true : (bool) ($c160['c160_topic_complete_after_finalization'] ?? false),
            'weekly_swing_watchlist_plan_confirm_result_reviewed' => (bool) ($c160['weekly_swing_watchlist_plan_confirm_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => (bool) ($c160['weekly_swing_watchlist_plan_confirm_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => (bool) ($c160['weekly_swing_watchlist_plan_confirm_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_controlled_only' => (bool) ($c160['weekly_swing_watchlist_plan_confirm_controlled_only'] ?? true),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($c160['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($c160['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'runtime_bridge_active' => (bool) ($c160['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c160['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c160['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c160['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c160_finalization_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
            'c160_go_decision_finalization_valid' => $this->c160FinalizationComplete($c160),
            'c160_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
            'primary_candidate_code' => (string) ($c160['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE),
            'backup_candidate_code' => (string) ($c160['backup_candidate_code'] ?? self::BACKUP_CANDIDATE),
            'comparator_candidate_code' => (string) ($c160['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE),
            'a01_remains_comparator_only' => (bool) ($c160['a01_remains_comparator_only'] ?? true),
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

    private function c160NextRecommendationMatches(array $c160): bool
    {
        return ($c160['next_step_recommendation'] ?? null) === self::RUN_CODE
            && $this->valueAt($c160, ['next_plan_confirm_completion_boundary_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($c160, ['planned_next_summary', 'planned_next_review']) === self::RUN_CODE;
    }

    private function c160FinalizationComplete(array $c160): bool
    {
        foreach (self::REQUIRED_C160_TRUE_FIELDS as $field) {
            if (($c160[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C160_FALSE_FIELDS as $field) {
            if (($c160[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($c160['operator_decision'] ?? null) === 'GO'
            && $this->valueAt($c160, ['c160_go_decision_finalization_decision', 'review_valid']) === true
            && $this->valueAt($c160, ['c160_go_decision_finalization_decision', 'go_decision_finalized']) === true
            && $this->valueAt($c160, ['c160_go_decision_finalization_decision', 'plan_confirm_closed']) === true
            && $this->valueAt($c160, ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest', 'manifest_created']) === true
            && $this->valueAt($c160, ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest', 'go_decision_finalization_artifact_only']) === true
            && $this->valueAt($c160, ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest', 'ready_for_plan_confirm_completion_boundary_review']) === true
            && $this->valueAt($c160, ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest', 'go_decision_finalization_used_for_free_publication']) === false
            && $this->valueAt($c160, ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest', 'go_decision_finalization_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($c160, ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest', 'go_decision_finalization_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($c160, ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_checklist', 'go_decision_finalization_reviewed']) === true
            && $this->valueAt($c160, ['weekly_swing_watchlist_plan_confirm_go_decision_finalization_checklist', 'artifact_only']) === true
            && $this->valueAt($c160, ['next_plan_confirm_completion_boundary_decision', 'review_valid']) === true
            && $this->valueAt($c160, ['next_plan_confirm_completion_boundary_decision', 'same_topic_c160_complete']) === true
            && $this->valueAt($c160, ['next_plan_confirm_completion_boundary_decision', 'topic_number_advances_after_c160_finalization']) === true;
    }

    private function publicationAndPlanGuardClean(array $c160): bool
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
            if (($c160[$flag] ?? null) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $c160): bool
    {
        return ($c160['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c160['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c160['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c160['primary_candidate_ready_for_plan_confirm_completion_boundary_review'] ?? null) === true
            && ($c160['backup_candidate_ready_for_plan_confirm_completion_boundary_review'] ?? null) === true
            && ($c160['comparator_candidate_ready_for_plan_confirm_completion_boundary_review'] ?? null) === false
            && ($c160['a01_remains_comparator_only'] ?? null) === true
            && ($c160['a01_promoted'] ?? false) === false
            && ($c160['candidate_promotion_executed'] ?? false) === false
            && ($c160['candidate_rerank_executed'] ?? false) === false
            && ($c160['strategy_retune_executed'] ?? false) === false
            && ($c160['scoring_mutation_executed'] ?? false) === false
            && ($c160['catalog_selection_changed'] ?? false) === false
            && ($c160['runtime_selection_changed'] ?? false) === false;
    }

    private function c160FinalizationLockValidationSummary(array $load): array
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
        ];
    }

    private function c160FinalizationCarryForwardSummary(array $c160): array
    {
        return [
            'status' => $c160['status'] ?? null,
            'phase_label' => $c160['phase_label'] ?? null,
            'operator_decision' => $c160['operator_decision'] ?? null,
            'go_decision_finalized' => (bool) ($c160['go_decision_finalized'] ?? false),
            'plan_confirm_closed' => (bool) ($c160['plan_confirm_closed'] ?? false),
            'c160_topic_complete_after_finalization' => (bool) ($c160['c160_topic_complete_after_finalization'] ?? false),
            'ready_for_plan_confirm_completion_boundary_review' => (bool) ($c160['ready_for_weekly_swing_watchlist_plan_confirm_completion_boundary_review'] ?? false),
            'source_next_recommendation' => $c160['next_step_recommendation'] ?? null,
        ];
    }

    private function planConfirmCompletionBoundaryGuardSummary(array $c160, bool $pass, array $options): array
    {
        return [
            'validation_completed' => true,
            'c160_go_decision_finalization_valid' => $this->c160FinalizationComplete($c160),
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c160),
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? false),
            'c160_topic_complete_confirmed' => (bool) ($options['c160_topic_complete_confirmed'] ?? false),
            'plan_confirm_closed_confirmed' => (bool) ($options['plan_confirm_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'completion_boundary_cleared' => $pass,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'free_publication_allowed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $c160): array
    {
        return [
            'candidate_scope_matches' => $this->candidateScopeMatches($c160),
            'primary_candidate_code' => $c160['primary_candidate_code'] ?? null,
            'backup_candidate_code' => $c160['backup_candidate_code'] ?? null,
            'comparator_candidate_code' => $c160['comparator_candidate_code'] ?? null,
            'a01_remains_comparator_only' => (bool) ($c160['a01_remains_comparator_only'] ?? false),
            'candidate_rerank_executed' => (bool) ($c160['candidate_rerank_executed'] ?? false),
            'candidate_promotion_executed' => (bool) ($c160['candidate_promotion_executed'] ?? false),
        ];
    }

    private function operatorApprovalValidationSummary(array $options): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
            'operator_approval_valid' => (bool) ($options['operator_approved'] ?? false) && $reference !== '',
        ];
    }

    private function completionBoundaryDecision(bool $pass, array $options): array
    {
        return [
            'review_pass' => $pass,
            'boundary_go_decision' => $pass ? 'BOUNDARY_CLEARED_GO' : 'NO_GO',
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? false),
            'c160_topic_complete_confirmed' => (bool) ($options['c160_topic_complete_confirmed'] ?? false),
            'plan_confirm_closed_confirmed' => (bool) ($options['plan_confirm_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'next_recommendation' => $pass ? self::C161_COMPLETION_EXECUTION_RECOMMENDATION : 'C161_TARGETED_COMPLETION_BOUNDARY_REPAIR',
        ];
    }

    private function nextPlanConfirmCompletionExecutionDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C161_COMPLETION_EXECUTION_RECOMMENDATION : 'C161_TARGETED_COMPLETION_BOUNDARY_REPAIR',
            'next_scope' => $pass ? 'C161 PLAN/CONFIRM completion execution only; no PLAN/CONFIRM mutation, live rollout, unrestricted publication, or free publication is authorized by the boundary' : 'targeted repair before C161 completion execution can be opened',
            'same_topic_c161_continues' => $pass,
            'next_is_concrete' => $pass,
            'next_requires_locked_c161_completion_boundary_artifact' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function completionBoundaryManifest(array $c160, bool $pass, array $options): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'plan_confirm_completion_boundary_review',
            'source_artifact' => 'C160_PLAN_CONFIRM_GO_DECISION_FINALIZATION_REVIEW',
            'source_artifact_path' => self::DEFAULT_C160_FINALIZATION_ARTIFACT,
            'source_artifact_hash' => $c160['artifact_hash'] ?? null,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C160_FINALIZATION_FILE_SHA1,
            'source_status' => $c160['status'] ?? null,
            'source_next_recommendation' => $c160['next_step_recommendation'] ?? null,
            'operator_decision' => $c160['operator_decision'] ?? null,
            'go_decision_finalized' => (bool) ($c160['go_decision_finalized'] ?? false),
            'plan_confirm_closed' => (bool) ($c160['plan_confirm_closed'] ?? false),
            'c160_topic_complete_after_finalization' => (bool) ($c160['c160_topic_complete_after_finalization'] ?? false),
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? false),
            'completion_boundary_cleared' => $pass,
            'ready_for_plan_confirm_completion_execution' => $pass,
            'boundary_artifact_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'completion_boundary_used_for_free_publication' => false,
            'completion_boundary_used_for_plan_confirm_mutation' => false,
            'completion_boundary_used_for_live_plan_confirm_rollout' => false,
        ];
    }

    private function completionBoundaryChecklist(bool $pass, array $options): array
    {
        return [
            'completion_boundary_reviewed' => true,
            'c160_finalization_source_lock_reviewed' => true,
            'c160_go_finalization_carried_forward' => true,
            'completion_boundary_confirmation_required' => true,
            'completion_boundary_confirmed' => (bool) ($options['completion_boundary_confirmed'] ?? false),
            'c160_topic_complete_confirmation_required' => true,
            'c160_topic_complete_confirmed' => (bool) ($options['c160_topic_complete_confirmed'] ?? false),
            'plan_confirm_closed_confirmation_required' => true,
            'plan_confirm_closed_confirmed' => (bool) ($options['plan_confirm_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_completion_boundary_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'completion_boundary_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c161_boundary' => false,
            'ready_for_plan_confirm_completion_execution' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'plan_confirm_completion_boundary_review_valid' => $pass,
            'completion_boundary_cleared' => $pass,
            'ready_for_plan_confirm_completion_execution' => $pass,
            'plan_confirm_mutated' => false,
            'live_rollout_executed' => false,
            'free_published' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c161_role' => 'primary_candidate_ready_for_plan_confirm_completion_execution',
                'primary_candidate_ready_for_plan_confirm_completion_execution' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c161_role' => 'backup_candidate_ready_for_plan_confirm_completion_execution',
                'backup_candidate_ready_for_plan_confirm_completion_execution' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c161_role' => 'comparator_only_candidate',
                'ready_for_plan_confirm_completion_execution' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $c160): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c160),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c160_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c160_finalization_artifact_not_modified' => true,
            'c161_completion_boundary_review_is_artifact_only_not_free_publication_or_live_rollout' => true,
            'c161_completion_boundary_keeps_same_topic_number_for_execution' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-60_C161_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW',
            'topic_code' => 'C161_PLAN_CONFIRM_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW',
            'c160_go_decision_finalization_carried_forward' => true,
            'c160_topic_complete_after_finalization' => $pass,
            'completion_boundary_cleared' => $pass,
            'same_topic_c161_continues_to_completion_execution' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C161_COMPLETION_EXECUTION_RECOMMENDATION : 'C161_TARGETED_COMPLETION_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'C161 PLAN/CONFIRM completion execution only; boundary does not mutate PLAN/CONFIRM, execute live rollout, or authorize free publication' : 'targeted repair before C161 completion execution can be opened',
            'same_topic_c161_continues' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C161 PLAN/CONFIRM completion boundary artifact hash',
                'locked C161 PLAN/CONFIRM completion boundary file SHA1',
                'C160 GO decision finalization carried forward',
                'PLAN/CONFIRM closed and unchanged',
                'live rollout still disabled',
                'free publication still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C161 completion boundary validates C160 GO decision finalization artifact_hash and file SHA1 locks before the boundary is cleared.',
            'C161 completion boundary validates C160 topic completion, PLAN/CONFIRM closed state, candidate scope, and next recommendation to C161 completion boundary.',
            'C161 completion boundary requires operator approval plus boundary, C160-topic-complete, PLAN/CONFIRM-closed, PLAN/CONFIRM-unchanged, no-live-rollout, and free-publication lock confirmations.',
            'C161 completion boundary keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C161 completion boundary recommends same-topic C161 PLAN/CONFIRM completion execution next.',
            'C161 completion boundary does not mutate PLAN/CONFIRM, read activated catalog, execute live rollout, free-publish recommendations, or allow unrestricted publication.',
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
            'c160_plan_confirm_go_decision_finalization' => [
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
            'expected_c160_finalization_hash' => $load['expected_hash'],
            'actual_c160_finalization_hash' => $load['actual_hash'],
            'c160_finalization_hash_match' => $load['hash_match'],
            'expected_c160_finalization_file_sha1' => $load['expected_file_sha1'],
            'actual_c160_finalization_file_sha1' => $load['actual_file_sha1'],
            'c160_finalization_file_sha1_match' => $load['file_sha1_match'],
            'c160_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
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
