<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewService
{
    public const RUN_CODE = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-55 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW';

    public const DEFAULT_C159_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C159_FINALIZATION_HASH = '1c497836fc6932909c06e62e324f806b07676ab1';
    public const DEFAULT_EXPECTED_C159_FINALIZATION_FILE_SHA1 = '97D00F48AA0D68853BAA46C36DCC571CFF3CB01F';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C159_FINALIZATION_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_PUBLICATION_OBSERVATION_CLOSED_READY_FOR_PLAN_CONFIRM_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C159_FINALIZATION_PHASE_LABEL = 'PR-54 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C159_FINALIZATION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C160_EXECUTION_RECOMMENDATION = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION';

    private const PASS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_PASSED_READY_FOR_PLAN_CONFIRM_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const PLAN_CONFIRM_BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_BOUNDARY_CONFIRMATION_MISSING';
    private const CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C159_LOCK_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const C159_FILE_SHA1_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const C159_CONVERT_FROM_JSON_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C159_STATUS_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_STATUS_MISMATCH';
    private const C159_PHASE_LABEL_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_PHASE_LABEL_MISMATCH';
    private const C159_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C159_FINALIZATION_INCOMPLETE_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_C159_FINALIZATION_INCOMPLETE';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C159_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass',
        'production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'post_publication_observation_finalization_confirmed',
        'post_publication_observation_closed',
        'free_publication_locked_confirmed',
        'plan_confirm_unchanged_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_boundary_review',
        'production_live_runtime_plan_confirm_boundary_review_allowed_next',
        'controlled_output_publication_post_publication_observation_go_decision_finalization_manifest_created',
        'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed',
        'weekly_swing_watchlist_controlled_output_publication_observed',
        'weekly_swing_watchlist_controlled_output_publication_observation_stable',
        'weekly_swing_watchlist_controlled_output_publication_result_reviewed',
        'weekly_swing_watchlist_controlled_output_publication_executed',
        'weekly_swing_watchlist_controlled_output_published',
        'weekly_swing_watchlist_controlled_publication_artifact_created',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c159_operator_go_no_go_lock_valid',
        'c159_operator_go_no_go_review_valid',
        'c159_operator_go_no_go_convert_from_json_pass',
        'c159_result_review_lock_valid',
        'c159_post_publication_observation_result_review_valid',
        'controlled_publication_lock_valid',
        'controlled_publication_integrity_valid',
        'primary_candidate_ready_for_plan_confirm_boundary_review',
        'backup_candidate_ready_for_plan_confirm_boundary_review',
        'a01_remains_comparator_only',
        'c159_post_publication_observation_go_decision_finalization_review_only',
        'c159_controlled_publication_observation_only',
        'c159_not_free_publication',
        'c159_not_unrestricted_publication',
        'c159_not_plan_confirm_mutation',
        'c159_topic_complete_after_finalization',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C159_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_boundary_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const PUBLICATION_AND_PLAN_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c160-*boundary*-test.json',
        'storage/app/watchlist/backtest/c160-*plan-confirm*-test.json',
        'storage/app/watchlist/backtest/c160-*negative-*-test.json',
        'storage/app/watchlist/backtest/c160-*missing-*-test.json',
        'storage/app/watchlist/backtest/c160-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c160-*invalid-*-test.json',
    ];

    public function execute(
        string $c159FinalizationArtifact = self::DEFAULT_C159_FINALIZATION_ARTIFACT,
        string $expectedC159FinalizationHash = self::DEFAULT_EXPECTED_C159_FINALIZATION_HASH,
        string $expectedC159FinalizationFileSha1 = self::DEFAULT_EXPECTED_C159_FINALIZATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c159FinalizationArtifact, $expectedC159FinalizationHash, $expectedC159FinalizationFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C159_LOCK_MISMATCH_STATUS, 'C159 finalization artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c159_finalization_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C159_CONVERT_FROM_JSON_STATUS, 'C159 finalization artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C159_LOCK_MISMATCH_STATUS, 'C159 finalization artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C159_FILE_SHA1_MISMATCH_STATUS, 'C159 finalization file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c159 = $load['payload'];
        if (($c159['status'] ?? null) !== self::EXPECTED_C159_FINALIZATION_STATUS || ($c159['reason_code'] ?? null) !== self::EXPECTED_C159_FINALIZATION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C159_STATUS_MISMATCH_STATUS, 'C159 finalization status/reason is not PLAN/CONFIRM boundary ready.', $outputPath, $overwrite);
        }
        if (($c159['phase_label'] ?? null) !== self::EXPECTED_C159_FINALIZATION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C159_PHASE_LABEL_MISMATCH_STATUS, 'C159 finalization phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c159NextRecommendationMatches($c159)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C159_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C159 finalization next recommendation is not C160 PLAN/CONFIRM boundary review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($c159)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C159 finalization already published, unlocked publication, mutated PLAN/CONFIRM, or executed live rollout.', $outputPath, $overwrite);
        }
        if (! $this->c159FinalizationComplete($c159)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C159_FINALIZATION_INCOMPLETE_STATUS, 'C159 finalization evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c159)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C159 candidate scope does not match locked C160 boundary scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C160 boundary requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_boundary_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_BOUNDARY_CONFIRMATION_MISSING_STATUS, 'C160 boundary requires --plan-confirm-boundary-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING_STATUS, 'C160 boundary requires --controlled-plan-confirm-only-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C160 boundary requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C160 locks C159 finalization and opens only the same-topic PLAN/CONFIRM execution boundary. It does not mutate PLAN/CONFIRM, publish output, or enable live rollout.';
        $artifact['diagnostic_conclusion'] = 'C160_PLAN_CONFIRM_BOUNDARY_PASSED_READY_FOR_CONTROLLED_EXECUTION_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C160_EXECUTION_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-55',
            'internal_checkpoint' => 'C160',
            'topic_code' => 'C160_PLAN_CONFIRM',
            'topic_stage' => 'PLAN_CONFIRM_BOUNDARY_REVIEW',
            'status' => 'C160_PLAN_CONFIRM_BOUNDARY_NOT_RUN',
            'reason_code' => 'C160_PLAN_CONFIRM_BOUNDARY_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_pass' => false,
            'production_live_runtime_plan_confirm_boundary_review_pass' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_execution' => false,
            'production_live_runtime_plan_confirm_execution_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_execution_allowed_next' => false,
            'weekly_swing_watchlist_controlled_output_publication_observed' => false,
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => false,
            'weekly_swing_watchlist_controlled_output_publication_executed' => false,
            'weekly_swing_watchlist_controlled_output_published' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_boundary_confirmed' => false,
            'controlled_plan_confirm_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'plan_confirm_execution_allowed_next' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => false,
            'c159_finalization_lock_valid' => false,
            'c159_go_decision_finalization_valid' => false,
            'c159_finalization_convert_from_json_pass' => false,
            'c159_topic_complete_after_finalization' => false,
            'operator_approved' => false,
            'approval_reference' => '',
            'primary_candidate_ready_for_plan_confirm_execution' => false,
            'backup_candidate_ready_for_plan_confirm_execution' => false,
            'comparator_candidate_ready_for_plan_confirm_execution' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c160_boundary_review_only' => true,
            'c160_topic_number_retained_for_execution' => true,
            'c160_not_plan_confirm_mutation' => true,
            'c160_not_live_plan_confirm_rollout' => true,
            'c160_not_publication' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C160_PLAN_CONFIRM_BOUNDARY_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c159 = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($c159, $pass, $options));
        $artifact['c159_finalization_lock_validation_summary'] = $this->c159FinalizationLockValidationSummary($load);
        $artifact['c159_go_decision_finalization_carry_forward_summary'] = $this->c159GoDecisionFinalizationCarryForwardSummary($c159);
        $artifact['plan_confirm_boundary_decision'] = $this->planConfirmBoundaryDecision($pass);
        $artifact['plan_confirm_boundary_manifest'] = $this->planConfirmBoundaryManifest($c159, $pass);
        $artifact['plan_confirm_boundary_checklist'] = $this->planConfirmBoundaryChecklist($pass, $options);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($c159);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c159, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c160_candidate_plan_confirm_boundary_scorecard'] = $this->candidateScorecard($pass);
        $artifact['plan_confirm_boundary_context_summary'] = $this->boundaryContextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function topLevelState(array $c159, bool $pass, array $options): array
    {
        return [
            'weekly_swing_watchlist_controlled_output_publication_observed' => (bool) ($c159['weekly_swing_watchlist_controlled_output_publication_observed'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => (bool) ($c159['weekly_swing_watchlist_controlled_output_publication_observation_stable'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_executed' => (bool) ($c159['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_published' => (bool) ($c159['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($c159['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($c159['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_boundary_confirmed' => (bool) ($options['plan_confirm_boundary_confirmed'] ?? false),
            'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'plan_confirm_execution_allowed_next' => $pass,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($c159['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c159['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c159['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c159['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c159_finalization_lock_valid' => (bool) (($c159['artifact_hash'] ?? null) === self::DEFAULT_EXPECTED_C159_FINALIZATION_HASH),
            'c159_go_decision_finalization_valid' => $this->c159FinalizationComplete($c159),
            'c159_topic_complete_after_finalization' => (bool) ($c159['c159_topic_complete_after_finalization'] ?? false),
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'primary_candidate_ready_for_plan_confirm_execution' => $pass,
            'backup_candidate_ready_for_plan_confirm_execution' => $pass,
            'comparator_candidate_ready_for_plan_confirm_execution' => false,
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_pass' => true,
            'production_live_runtime_plan_confirm_boundary_review_pass' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_execution' => true,
            'production_live_runtime_plan_confirm_execution_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_execution_allowed_next' => true,
            'plan_confirm_execution_allowed_next' => true,
            'c159_finalization_lock_valid' => true,
            'c159_go_decision_finalization_valid' => true,
            'c159_finalization_convert_from_json_pass' => true,
            'c159_topic_complete_after_finalization' => true,
            'primary_candidate_ready_for_plan_confirm_execution' => true,
            'backup_candidate_ready_for_plan_confirm_execution' => true,
            'comparator_candidate_ready_for_plan_confirm_execution' => false,
        ];
    }

    private function c159NextRecommendationMatches(array $c159): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c159, $path) !== self::EXPECTED_C159_FINALIZATION_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function c159FinalizationComplete(array $c159): bool
    {
        foreach (self::REQUIRED_C159_TRUE_FIELDS as $field) {
            if (($c159[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C159_FALSE_FIELDS as $field) {
            if (($c159[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($c159['operator_go_decision'] ?? null) !== 'GO' || ($c159['operator_decision'] ?? null) !== 'GO') {
            return false;
        }
        foreach ($this->requiredC159NestedExpectations() as $expectation) {
            if ($this->valueAt($c159, $expectation['path']) !== $expectation['value']) {
                return false;
            }
        }

        return true;
    }

    private function requiredC159NestedExpectations(): array
    {
        return [
            ['path' => ['c159_go_decision_finalization_decision', 'review_valid'], 'value' => true],
            ['path' => ['c159_go_decision_finalization_decision', 'operator_decision'], 'value' => 'GO'],
            ['path' => ['c159_go_decision_finalization_decision', 'go_decision_finalized'], 'value' => true],
            ['path' => ['c159_go_decision_finalization_decision', 'post_publication_observation_closed'], 'value' => true],
            ['path' => ['next_plan_confirm_boundary_decision', 'next_is_concrete'], 'value' => true],
            ['path' => ['next_plan_confirm_boundary_decision', 'next_requires_locked_c159_finalization_artifact'], 'value' => true],
            ['path' => ['next_plan_confirm_boundary_decision', 'topic_number_advances_after_c159_finalization'], 'value' => true],
            ['path' => ['next_plan_confirm_boundary_decision', 'same_topic_c159_complete'], 'value' => true],
            ['path' => ['next_plan_confirm_boundary_decision', 'plan_confirm_mutation_allowed_next'], 'value' => false],
            ['path' => ['next_plan_confirm_boundary_decision', 'live_plan_confirm_rollout_allowed_next'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest', 'go_decision_finalization_artifact_only'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest', 'go_decision_finalized'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest', 'ready_for_plan_confirm_boundary_review'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest', 'official_output_published'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest', 'free_publication_allowed'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest', 'go_decision_finalization_used_for_plan_confirm_mutation'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_checklist', 'go_decision_finalization_reviewed'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_checklist', 'artifact_only'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_checklist', 'weekly_swing_stock_recommendation_free_published_in_c159_finalization'], 'value' => false],
        ];
    }

    private function publicationAndPlanGuardClean(array $source): bool
    {
        foreach (self::PUBLICATION_AND_PLAN_FALSE_FIELDS as $field) {
            if (($source[$field] ?? null) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $source): bool
    {
        return ($source['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($source['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($source['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($source['primary_candidate_ready_for_plan_confirm_boundary_review'] ?? null) === true
            && ($source['backup_candidate_ready_for_plan_confirm_boundary_review'] ?? null) === true
            && ($source['comparator_candidate_ready_for_plan_confirm_boundary_review'] ?? null) === false
            && ($source['a01_remains_comparator_only'] ?? null) === true
            && ($source['a01_promoted'] ?? false) === false
            && ($source['candidate_promotion_executed'] ?? false) === false
            && ($source['candidate_rerank_executed'] ?? false) === false
            && ($source['strategy_retune_executed'] ?? false) === false
            && ($source['scoring_mutation_executed'] ?? false) === false
            && ($source['catalog_selection_changed'] ?? false) === false
            && ($source['runtime_selection_changed'] ?? false) === false;
    }

    private function c159FinalizationLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C159_GO_DECISION_FINALIZATION',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C159_FINALIZATION_STATUS,
            'actual_status' => is_array($load['payload']) ? ($load['payload']['status'] ?? null) : null,
            'expected_phase_label' => self::EXPECTED_C159_FINALIZATION_PHASE_LABEL,
            'actual_phase_label' => is_array($load['payload']) ? ($load['payload']['phase_label'] ?? null) : null,
            'expected_next_recommendation' => self::EXPECTED_C159_FINALIZATION_NEXT_RECOMMENDATION,
            'next_recommendation_match' => is_array($load['payload']) && $this->c159NextRecommendationMatches($load['payload']),
            'c159_finalization_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c159GoDecisionFinalizationCarryForwardSummary(array $c159): array
    {
        return [
            'validation_completed' => true,
            'c159_go_decision_finalization_valid' => $this->c159FinalizationComplete($c159),
            'operator_go_decision' => (string) ($c159['operator_go_decision'] ?? 'UNSET'),
            'go_decision_finalized' => (bool) ($c159['go_decision_finalized'] ?? false),
            'post_publication_observation_closed' => (bool) ($c159['post_publication_observation_closed'] ?? false),
            'ready_for_plan_confirm_boundary_review' => (bool) ($c159['ready_for_weekly_swing_watchlist_plan_confirm_boundary_review'] ?? false),
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function planConfirmBoundaryDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'topic_code' => 'C160_PLAN_CONFIRM',
            'topic_stage' => 'PLAN_CONFIRM_BOUNDARY_REVIEW',
            'plan_confirm_execution_allowed_next' => $pass,
            'plan_confirm_mutated_in_boundary' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'official_output_published' => false,
            'publication_allowed_in_boundary' => false,
            'unrestricted_publication_allowed' => false,
            'next_recommendation' => $pass ? self::C160_EXECUTION_RECOMMENDATION : 'C160_TARGETED_C159_FINALIZATION_REPAIR',
            'next_uses_same_topic_number' => $pass,
        ];
    }

    private function planConfirmBoundaryManifest(array $c159, bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_boundary_review',
            'source_artifact_path' => self::DEFAULT_C159_FINALIZATION_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C159_FINALIZATION_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C159_FINALIZATION_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'ready_for_plan_confirm_execution' => $pass,
            'plan_confirm_execution_required_next' => $pass,
            'plan_confirm_executed_in_c160_boundary' => false,
            'plan_confirm_mutated_in_c160_boundary' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'official_output_generated_for_controlled_review' => (bool) ($c159['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => false,
            'publication_allowed_in_boundary' => false,
            'unrestricted_publication_allowed' => false,
            'boundary_review_only' => true,
        ];
    }

    private function planConfirmBoundaryChecklist(bool $pass, array $options): array
    {
        return [
            'c159_finalization_artifact_locked' => true,
            'plan_confirm_boundary_reviewed' => true,
            'plan_confirm_boundary_confirmed' => (bool) ($options['plan_confirm_boundary_confirmed'] ?? false),
            'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'operator_approval_required' => true,
            'runtime_bridge_must_remain_active' => true,
            'weekly_swing_live_output_must_remain_enabled' => true,
            'plan_confirm_execution_deferred_to_same_c160_topic_execution_stage' => true,
            'plan_confirm_mutation_forbidden_in_boundary' => true,
            'live_plan_confirm_rollout_forbidden_in_boundary' => true,
            'official_output_publication_forbidden_in_boundary' => true,
            'unrestricted_publication_forbidden' => true,
            'artifact_only' => true,
            'same_topic_number_for_next_stage' => $pass,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $source): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($source),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
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
            'primary_candidate_ready_for_plan_confirm_execution' => $pass,
            'backup_candidate_ready_for_plan_confirm_execution' => $pass,
            'comparator_candidate_ready_for_plan_confirm_execution' => false,
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
            'plan_confirm_boundary_confirmation_required' => true,
            'plan_confirm_boundary_confirmed' => (bool) ($options['plan_confirm_boundary_confirmed'] ?? false),
            'controlled_plan_confirm_only_confirmation_required' => true,
            'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c160_role' => 'primary_candidate_ready_for_plan_confirm_execution',
                'ready_for_plan_confirm_execution' => $pass,
                'plan_confirm_mutated_in_boundary' => false,
                'live_rollout_allowed_in_boundary' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c160_role' => 'backup_standby_candidate_ready_for_plan_confirm_execution',
                'ready_for_plan_confirm_execution' => $pass,
                'plan_confirm_mutated_in_boundary' => false,
                'live_rollout_allowed_in_boundary' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c160_role' => 'comparator_only_candidate',
                'ready_for_plan_confirm_execution' => false,
                'plan_confirm_mutated_in_boundary' => false,
                'live_rollout_allowed_in_boundary' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function boundaryContextSummary(bool $pass): array
    {
        return [
            'plan_confirm_boundary_context_created' => true,
            'plan_confirm_boundary_context_valid' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_plan_confirm' => false,
            'context_used_for_plan_confirm_mutation' => false,
            'context_used_for_live_rollout' => false,
            'plan_confirm_execution_allowed_next' => $pass,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'official_output_published' => false,
            'runtime_bridge_active' => true,
            'weekly_swing_watchlist_runtime_active' => true,
            'weekly_swing_watchlist_live_output_enabled' => true,
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c159_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c159_finalization_artifact_not_modified' => true,
            'c160_is_boundary_review_not_plan_confirm_execution' => true,
            'c160_topic_number_retained_for_next_stage' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-55_C160_PLAN_CONFIRM_BOUNDARY_REVIEW',
            'topic_code' => 'C160_PLAN_CONFIRM',
            'topic_stage' => 'PLAN_CONFIRM_BOUNDARY_REVIEW',
            'c159_go_finalization_carried_forward' => true,
            'plan_confirm_boundary_review_pass' => $pass,
            'ready_for_plan_confirm_execution' => $pass,
            'same_topic_number_for_next_stage' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C160_EXECUTION_RECOMMENDATION : 'C160_TARGETED_C159_FINALIZATION_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C160 controlled PLAN/CONFIRM execution only; boundary review does not mutate PLAN/CONFIRM or enable live rollout' : 'targeted C159 lock, finalization, candidate scope, publication/PLAN guard, or cleanup repair',
            'same_topic_number_for_next_stage' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C160 boundary artifact hash',
                'locked C160 boundary file SHA1',
                'locked C159 finalization artifact hash',
                'operator approval reference for controlled PLAN/CONFIRM execution',
                'controlled PLAN/CONFIRM only confirmation',
                'PLAN/CONFIRM unchanged baseline confirmation',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C160 boundary validates C159 finalization artifact_hash and file SHA1 locks before opening PLAN/CONFIRM execution.',
            'C160 boundary confirms the finalized C159 GO decision remains artifact-only evidence.',
            'C160 boundary keeps the same topic number for the next execution stage.',
            'C160 boundary does not mutate PLAN/CONFIRM, allow live PLAN/CONFIRM rollout, publish output, or unlock unrestricted publication.',
            'C160 boundary keeps E02 primary, B01 backup, and A01 comparator-only.',
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
            'c159_go_decision_finalization' => [
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
            'expected_c159_finalization_hash' => $load['expected_hash'],
            'actual_c159_finalization_hash' => $load['actual_hash'],
            'c159_finalization_hash_match' => $load['hash_match'],
            'expected_c159_finalization_file_sha1' => $load['expected_file_sha1'],
            'actual_c159_finalization_file_sha1' => $load['actual_file_sha1'],
            'c159_finalization_file_sha1_match' => $load['file_sha1_match'],
            'c159_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
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
