<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-54 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';

    public const DEFAULT_C159_OPERATOR_ARTIFACT = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C159_OPERATOR_HASH = 'e6c1daae25cfd45950c9c7849b1277cc2099e557';
    public const DEFAULT_EXPECTED_C159_OPERATOR_FILE_SHA1 = 'DEA4167C95413F45DA8E7F6F16816BD178987F78';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C159_OPERATOR_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C159_OPERATOR_PHASE_LABEL = 'PR-53 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';
    private const EXPECTED_C159_OPERATOR_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C160_RECOMMENDATION = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW';

    private const PASS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_PUBLICATION_OBSERVATION_CLOSED_READY_FOR_PLAN_CONFIRM_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_FINALIZATION_NOT_CONFIRMED_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING';
    private const OBSERVATION_FINALIZATION_NOT_CONFIRMED_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_POST_PUBLICATION_OBSERVATION_FINALIZATION_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C159_OPERATOR_LOCK_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH';
    private const C159_OPERATOR_FILE_SHA1_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_FILE_SHA1_LOCK_MISMATCH';
    private const C159_OPERATOR_CONVERT_FROM_JSON_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C159_OPERATOR_STATUS_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_STATUS_MISMATCH';
    private const C159_OPERATOR_PHASE_LABEL_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_PHASE_LABEL_MISMATCH';
    private const C159_OPERATOR_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_NO_GO_NEXT_RECOMMENDATION_MISMATCH';
    private const C159_OPERATOR_GO_INVALID_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C159_OPERATOR_GO_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C159_OPERATOR_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_pass',
        'production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_pass',
        'operator_decision_recorded',
        'operator_decision_confirmed',
        'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_review',
        'production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed_next',
        'controlled_output_publication_post_publication_observation_operator_go_no_go_manifest_created',
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
        'c159_result_review_lock_valid',
        'c159_post_publication_observation_result_review_valid',
        'c159_result_review_convert_from_json_pass',
        'controlled_publication_lock_valid',
        'controlled_publication_integrity_valid',
        'primary_candidate_ready_for_post_publication_observation_go_decision_finalization_review',
        'backup_candidate_ready_for_post_publication_observation_go_decision_finalization_review',
        'a01_remains_comparator_only',
        'c159_post_publication_observation_operator_go_no_go_review_only',
        'c159_controlled_publication_observation_only',
        'c159_not_free_publication',
        'c159_not_unrestricted_publication',
        'c159_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C159_OPERATOR_FALSE_FIELDS = [
        'operator_no_go_decision',
        'operator_hold_decision',
        'post_publication_observation_stopped_no_go',
        'post_publication_observation_deferred_hold',
        'comparator_candidate_ready_for_post_publication_observation_go_decision_finalization_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c159-*finalization*-test.json',
        'storage/app/watchlist/backtest/c159-*go-decision*-test.json',
        'storage/app/watchlist/backtest/c159-*negative-*-test.json',
        'storage/app/watchlist/backtest/c159-*missing-*-test.json',
        'storage/app/watchlist/backtest/c159-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c159-*invalid-*-test.json',
    ];

    public function execute(
        string $c159OperatorArtifact = self::DEFAULT_C159_OPERATOR_ARTIFACT,
        string $expectedC159OperatorHash = self::DEFAULT_EXPECTED_C159_OPERATOR_HASH,
        string $expectedC159OperatorFileSha1 = self::DEFAULT_EXPECTED_C159_OPERATOR_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c159OperatorArtifact, $expectedC159OperatorHash, $expectedC159OperatorFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C159_OPERATOR_LOCK_MISMATCH_STATUS, 'C159 operator GO/NO-GO artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c159_operator_go_no_go_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C159_OPERATOR_CONVERT_FROM_JSON_STATUS, 'C159 operator GO/NO-GO artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C159_OPERATOR_LOCK_MISMATCH_STATUS, 'C159 operator GO/NO-GO artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C159_OPERATOR_FILE_SHA1_MISMATCH_STATUS, 'C159 operator GO/NO-GO file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $operator = $load['payload'];
        if (($operator['status'] ?? null) !== self::EXPECTED_C159_OPERATOR_STATUS || ($operator['reason_code'] ?? null) !== self::EXPECTED_C159_OPERATOR_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C159_OPERATOR_STATUS_MISMATCH_STATUS, 'C159 operator GO/NO-GO status/reason is not GO finalization ready.', $outputPath, $overwrite);
        }
        if (($operator['phase_label'] ?? null) !== self::EXPECTED_C159_OPERATOR_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C159_OPERATOR_PHASE_LABEL_MISMATCH_STATUS, 'C159 operator GO/NO-GO phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->operatorNextRecommendationMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C159_OPERATOR_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C159 operator GO/NO-GO next recommendation is not C159 post-publication observation GO decision finalization.', $outputPath, $overwrite);
        }
        if (! $this->operatorGoComplete($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C159_OPERATOR_GO_INVALID_STATUS, 'C159 operator GO evidence is incomplete or not valid for finalization.', $outputPath, $overwrite);
        }
        if (! $this->freePublicationAndPlanGuardClean($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C159 operator evidence has free publication, unrestricted publication, or PLAN/CONFIRM mutation already enabled.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C159 operator candidate scope does not match locked finalization scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C159 GO decision finalization requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['go_decision_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::GO_FINALIZATION_NOT_CONFIRMED_STATUS, 'C159 requires --go-decision-finalization-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_publication_observation_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OBSERVATION_FINALIZATION_NOT_CONFIRMED_STATUS, 'C159 requires --post-publication-observation-finalization-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C159 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C159 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C159 finalizes the operator GO decision for controlled output publication post-publication observation. The observation topic is closed; free publication, unrestricted publication, and PLAN/CONFIRM mutation remain locked.';
        $artifact['diagnostic_conclusion'] = 'C159_POST_PUBLICATION_OBSERVATION_GO_FINALIZED_TOPIC_CLOSED_READY_FOR_C160_PLAN_CONFIRM_BOUNDARY_NOT_MUTATED';
        $artifact['next_step_recommendation'] = self::C160_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-54',
            'internal_checkpoint' => 'C159',
            'topic_code' => 'C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION',
            'topic_stage' => 'POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW',
            'status' => 'C159_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'reason_code' => 'C159_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass' => false,
            'production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass' => false,
            'operator_decision' => 'NO_GO',
            'operator_go_decision' => 'NO_GO',
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'go_decision_finalization_confirmed' => false,
            'post_publication_observation_finalization_confirmed' => false,
            'post_publication_observation_closed' => false,
            'free_publication_locked_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_boundary_review' => false,
            'production_live_runtime_plan_confirm_boundary_review_allowed_next' => false,
            'controlled_output_publication_post_publication_observation_go_decision_finalization_manifest_created' => false,
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed' => false,
            'weekly_swing_watchlist_controlled_output_publication_observed' => false,
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => false,
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => false,
            'weekly_swing_watchlist_controlled_output_publication_executed' => false,
            'weekly_swing_watchlist_controlled_output_published' => false,
            'weekly_swing_watchlist_controlled_publication_artifact_created' => false,
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
            'c159_operator_go_no_go_lock_valid' => false,
            'c159_operator_go_no_go_review_valid' => false,
            'c159_operator_go_no_go_convert_from_json_pass' => false,
            'c159_result_review_lock_valid' => false,
            'c159_post_publication_observation_result_review_valid' => false,
            'controlled_publication_lock_valid' => false,
            'controlled_publication_integrity_valid' => false,
            'primary_candidate_ready_for_plan_confirm_boundary_review' => false,
            'backup_candidate_ready_for_plan_confirm_boundary_review' => false,
            'comparator_candidate_ready_for_plan_confirm_boundary_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c159_post_publication_observation_go_decision_finalization_review_only' => true,
            'c159_controlled_publication_observation_only' => true,
            'c159_not_free_publication' => true,
            'c159_not_unrestricted_publication' => true,
            'c159_not_plan_confirm_mutation' => true,
            'c159_topic_complete_after_finalization' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C159_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'next_step_recommendation' => 'C159_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'message' => '',
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $operator = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($operator, $load, $pass, $options));
        $artifact['c159_operator_go_no_go_lock_validation_summary'] = $this->operatorLockValidationSummary($load, $operator);
        $artifact['c159_operator_go_no_go_carry_forward_summary'] = $this->operatorCarryForwardSummary($operator);
        $artifact['post_publication_observation_finalization_guard_summary'] = $this->postPublicationObservationFinalizationGuardSummary($operator, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($operator, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c159_go_decision_finalization_decision'] = $this->goDecisionFinalizationDecision($pass, $options);
        $artifact['next_plan_confirm_boundary_decision'] = $this->nextPlanConfirmBoundaryDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_manifest'] = $this->goDecisionFinalizationManifest($operator, $pass, $options);
        $artifact['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_checklist'] = $this->goDecisionFinalizationChecklist($pass, $options);
        $artifact['c159_candidate_post_publication_observation_go_decision_finalization_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($operator);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');

        return $artifact;
    }

    private function topLevelState(array $operator, array $load, bool $pass, array $options): array
    {
        return [
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_observed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_observed'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_observation_stable'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_result_reviewed'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_executed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_published' => (bool) ($operator['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'weekly_swing_watchlist_controlled_publication_artifact_created' => (bool) ($operator['weekly_swing_watchlist_controlled_publication_artifact_created'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($operator['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($operator['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($operator['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($operator['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($operator['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($operator['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c159_operator_go_no_go_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c159_operator_go_no_go_review_valid' => $this->operatorGoComplete($operator),
            'c159_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c159_result_review_lock_valid' => (bool) ($operator['c159_result_review_lock_valid'] ?? false),
            'c159_post_publication_observation_result_review_valid' => (bool) ($operator['c159_post_publication_observation_result_review_valid'] ?? false),
            'controlled_publication_lock_valid' => (bool) ($operator['controlled_publication_lock_valid'] ?? false),
            'controlled_publication_integrity_valid' => (bool) ($operator['controlled_publication_integrity_valid'] ?? false),
            'operator_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision_confirmed' => $pass,
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_publication_observation_finalization_confirmed' => (bool) ($options['post_publication_observation_finalization_confirmed'] ?? false),
            'post_publication_observation_closed' => $pass,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_boundary_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_boundary_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_boundary_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass' => true,
            'production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass' => true,
            'operator_decision' => 'GO',
            'operator_go_decision' => 'GO',
            'operator_go_decision_confirmed' => true,
            'go_decision_finalized' => true,
            'go_decision_finalization_confirmed' => true,
            'post_publication_observation_finalization_confirmed' => true,
            'post_publication_observation_closed' => true,
            'free_publication_locked_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_boundary_review' => true,
            'production_live_runtime_plan_confirm_boundary_review_allowed_next' => true,
            'controlled_output_publication_post_publication_observation_go_decision_finalization_manifest_created' => true,
            'c159_operator_go_no_go_lock_valid' => true,
            'c159_operator_go_no_go_review_valid' => true,
            'c159_operator_go_no_go_convert_from_json_pass' => true,
            'primary_candidate_ready_for_plan_confirm_boundary_review' => true,
            'backup_candidate_ready_for_plan_confirm_boundary_review' => true,
            'comparator_candidate_ready_for_plan_confirm_boundary_review' => false,
            'c159_topic_complete_after_finalization' => true,
        ];
    }

    private function operatorNextRecommendationMatches(array $operator): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_concrete_post_publication_observation_step_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($operator, $path) !== self::EXPECTED_C159_OPERATOR_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function operatorGoComplete(array $operator): bool
    {
        foreach (self::REQUIRED_C159_OPERATOR_TRUE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C159_OPERATOR_FALSE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($operator['operator_decision'] ?? null) === 'GO'
            && ($operator['operator_go_decision'] ?? null) === 'GO'
            && trim((string) ($operator['operator_decision_reason'] ?? '')) !== ''
            && $this->valueAt($operator, ['c159_operator_go_no_go_decision', 'operator_decision']) === 'GO'
            && $this->valueAt($operator, ['c159_operator_go_no_go_decision', 'ready_for_go_decision_finalization_review']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_manifest', 'operator_decision']) === 'GO'
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_manifest', 'operator_go_no_go_used_for_publication']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_manifest', 'operator_go_no_go_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_checklist', 'artifact_only']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_checklist', 'weekly_swing_stock_recommendation_free_published_in_c159_operator_review']) === false;
    }

    private function freePublicationAndPlanGuardClean(array $operator): bool
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
            if (($operator[$field] ?? false) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $operator): bool
    {
        return ($operator['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($operator['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($operator['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($operator['primary_candidate_ready_for_post_publication_observation_go_decision_finalization_review'] ?? null) === true
            && ($operator['backup_candidate_ready_for_post_publication_observation_go_decision_finalization_review'] ?? null) === true
            && ($operator['comparator_candidate_ready_for_post_publication_observation_go_decision_finalization_review'] ?? null) === false
            && ($operator['a01_remains_comparator_only'] ?? null) === true
            && ($operator['a01_promoted'] ?? false) === false
            && ($operator['candidate_promotion_executed'] ?? false) === false
            && ($operator['candidate_rerank_executed'] ?? false) === false;
    }

    private function operatorLockValidationSummary(array $load, array $operator): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C159_OPERATOR_GO_NO_GO',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C159_OPERATOR_STATUS,
            'actual_status' => $operator['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C159_OPERATOR_PHASE_LABEL,
            'actual_phase_label' => $operator['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C159_OPERATOR_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->operatorNextRecommendationMatches($operator),
            'c159_operator_go_no_go_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function operatorCarryForwardSummary(array $operator): array
    {
        return [
            'validation_completed' => true,
            'c159_operator_go_no_go_review_valid' => $this->operatorGoComplete($operator),
            'topic_code' => $operator['topic_code'] ?? null,
            'topic_stage' => $operator['topic_stage'] ?? null,
            'operator_decision' => $operator['operator_decision'] ?? null,
            'operator_decision_reason' => $operator['operator_decision_reason'] ?? null,
            'controlled_publication_observed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_observed'] ?? false),
            'controlled_publication_observation_stable' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_observation_stable'] ?? false),
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'go_decision_finalization_allowed' => (bool) ($operator['production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed_next'] ?? false),
        ];
    }

    private function postPublicationObservationFinalizationGuardSummary(array $operator, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'post_publication_observation_finalization_valid' => $pass,
            'controlled_publication_observed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_observed'] ?? false),
            'controlled_publication_observation_stable' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_observation_stable'] ?? false),
            'controlled_publication_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_result_reviewed'] ?? false),
            'post_publication_observation_closed' => $pass,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $operator, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($operator),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_boundary_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_boundary_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_boundary_review' => false,
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
            'go_decision_finalization_confirmation_required' => true,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_publication_observation_finalization_confirmation_required' => true,
            'post_publication_observation_finalization_confirmed' => (bool) ($options['post_publication_observation_finalization_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
        ];
    }

    private function goDecisionFinalizationDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'operator_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_publication_observation_finalization_confirmed' => (bool) ($options['post_publication_observation_finalization_confirmed'] ?? false),
            'post_publication_observation_closed' => $pass,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'decision_scope' => $pass ? 'C159 post-publication observation GO finalized and topic closed; PLAN/CONFIRM boundary review may start next' : 'targeted repair required before C159 GO finalization can be recorded',
        ];
    }

    private function nextPlanConfirmBoundaryDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C160_RECOMMENDATION : 'C159_TARGETED_OPERATOR_GO_NO_GO_REPAIR',
            'next_scope' => $pass ? 'C160 PLAN/CONFIRM boundary review only; no PLAN/CONFIRM mutation is authorized by C159 finalization' : 'targeted repair before C159 post-publication observation GO decision finalization can be recorded',
            'next_is_concrete' => $pass,
            'next_requires_locked_c159_finalization_artifact' => $pass,
            'topic_number_advances_after_c159_finalization' => $pass,
            'same_topic_c159_complete' => $pass,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function goDecisionFinalizationManifest(array $operator, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'controlled_output_publication_post_publication_observation_go_decision_finalization_review',
            'source_artifact' => 'C159_OPERATOR_GO_NO_GO_REVIEW',
            'source_artifact_path' => self::DEFAULT_C159_OPERATOR_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C159_OPERATOR_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C159_OPERATOR_FILE_SHA1,
            'source_operator_decision' => (string) ($operator['operator_decision'] ?? 'UNSET'),
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_publication_observation_finalization_confirmed' => (bool) ($options['post_publication_observation_finalization_confirmed'] ?? false),
            'post_publication_observation_closed' => $pass,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass' => $pass,
            'ready_for_plan_confirm_boundary_review' => $pass,
            'go_decision_finalization_artifact_only' => true,
            'controlled_publication_observed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_observed'] ?? false),
            'controlled_publication_observation_stable' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_observation_stable'] ?? false),
            'controlled_publication_published' => (bool) ($operator['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'go_decision_finalization_used_for_free_publication' => false,
            'go_decision_finalization_used_for_plan_confirm_mutation' => false,
        ];
    }

    private function goDecisionFinalizationChecklist(bool $pass, array $options): array
    {
        return [
            'go_decision_finalization_reviewed' => true,
            'c159_operator_go_no_go_source_lock_reviewed' => true,
            'operator_go_decision_carried_forward' => true,
            'go_decision_finalization_required' => true,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'post_publication_observation_finalization_confirmed' => (bool) ($options['post_publication_observation_finalization_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_go_finalization_gate_required' => true,
            'negative_post_publication_observation_finalization_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'negative_plan_confirm_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'go_decision_finalization_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c159_finalization' => false,
            'ready_for_plan_confirm_boundary_review' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'post_publication_observation_go_decision_finalization_review_valid' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'ready_for_plan_confirm_boundary_review' => $pass,
            'free_published' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c159_role' => 'primary_candidate_ready_for_plan_confirm_boundary_review',
                'primary_candidate_ready_for_plan_confirm_boundary_review' => $pass,
                'controlled_published' => true,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c159_role' => 'backup_candidate_ready_for_plan_confirm_boundary_review',
                'backup_candidate_ready_for_plan_confirm_boundary_review' => $pass,
                'controlled_published' => true,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c159_role' => 'comparator_only_candidate',
                'ready_for_plan_confirm_boundary_review' => false,
                'controlled_published' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $operator): array
    {
        return [
            'validation_completed' => true,
            'free_publication_and_plan_guard_clean' => $this->freePublicationAndPlanGuardClean($operator),
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
            'c159_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c159_operator_go_no_go_artifact_not_modified' => true,
            'c159_go_decision_finalization_review_is_artifact_only_not_free_publication' => true,
            'c159_go_decision_finalization_review_closes_c159_topic' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-54_C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW',
            'topic_code' => 'C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION',
            'topic_stage' => 'POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW',
            'c159_operator_go_no_go_review_carried_forward' => true,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'post_publication_observation_closed' => $pass,
            'topic_complete_after_finalization' => $pass,
            'topic_number_advances_after_c159_finalization' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C160_RECOMMENDATION : 'C159_TARGETED_OPERATOR_GO_NO_GO_REPAIR',
            'planned_next_scope' => $pass ? 'C160 PLAN/CONFIRM boundary review only; C159 finalization does not mutate PLAN/CONFIRM or authorize live rollout' : 'targeted repair before C159 GO decision finalization can be recorded',
            'topic_number_advances_after_c159_finalization' => $pass,
            'same_topic_c159_complete' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C159 GO decision finalization artifact hash',
                'locked C159 GO decision finalization file SHA1',
                'finalized C159 post-publication observation GO decision',
                'free publication still disabled',
                'PLAN/CONFIRM unchanged',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C159 finalization validates C159 operator GO/NO-GO artifact_hash and file SHA1 locks before GO finalization is recorded.',
            'C159 finalization validates operator GO, confirmation, decision reason, candidate scope, and next recommendation to C159 finalization.',
            'C159 finalization requires operator approval plus GO finalization, post-publication observation finalization, free publication lock, and PLAN/CONFIRM unchanged confirmations.',
            'C159 finalization keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C159 finalization closes the controlled output publication post-publication observation topic and recommends C160 PLAN/CONFIRM boundary review.',
            'C159 finalization does not free-publish recommendations, allow unrestricted publication, or mutate PLAN/CONFIRM.',
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
            'c159_operator_go_no_go' => [
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
            'expected_c159_operator_go_no_go_hash' => $load['expected_hash'],
            'actual_c159_operator_go_no_go_hash' => $load['actual_hash'],
            'c159_operator_go_no_go_hash_match' => $load['hash_match'],
            'expected_c159_operator_go_no_go_file_sha1' => $load['expected_file_sha1'],
            'actual_c159_operator_go_no_go_file_sha1' => $load['actual_file_sha1'],
            'c159_operator_go_no_go_file_sha1_match' => $load['file_sha1_match'],
            'c159_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
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
        $keys = [];
        $duplicates = [];
        $depth = 0;
        $inString = false;
        $escaping = false;
        $buffer = '';
        $collectingKey = false;
        $lastString = null;
        $length = strlen($raw);

        for ($i = 0; $i < $length; $i++) {
            $char = $raw[$i];
            if ($inString) {
                if ($escaping) {
                    $buffer .= $char;
                    $escaping = false;
                    continue;
                }
                if ($char === '\\') {
                    $escaping = true;
                    continue;
                }
                if ($char === '"') {
                    $inString = false;
                    $lastString = $collectingKey ? $buffer : null;
                    $collectingKey = false;
                    $buffer = '';
                    continue;
                }
                $buffer .= $char;
                continue;
            }
            if ($char === '"') {
                $inString = true;
                $collectingKey = $depth === 1;
                $buffer = '';
                continue;
            }
            if ($char === '{') {
                $depth++;
                $lastString = null;
                continue;
            }
            if ($char === '}') {
                $depth--;
                $lastString = null;
                continue;
            }
            if ($depth === 1 && $char === ':' && $lastString !== null) {
                $normalized = strtolower($lastString);
                if (isset($keys[$normalized]) && ! in_array($lastString, $duplicates, true)) {
                    $duplicates[] = $lastString;
                }
                $keys[$normalized] = true;
                $lastString = null;
            }
        }

        sort($duplicates);

        return $duplicates;
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
