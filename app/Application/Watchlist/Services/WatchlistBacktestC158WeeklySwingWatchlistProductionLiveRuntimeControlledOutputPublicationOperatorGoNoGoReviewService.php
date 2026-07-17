<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-49 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C158_RESULT_REVIEW_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review.json';
    public const DEFAULT_EXPECTED_C158_RESULT_REVIEW_HASH = '2912bf54b34ee23b4413a179072d3e670f92e719';
    public const DEFAULT_EXPECTED_C158_RESULT_REVIEW_FILE_SHA1 = 'C601A8598D83D61FB84F0AAB3DED9AD8E36AD59B';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C158_RESULT_REVIEW_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C158_RESULT_REVIEW_PHASE_LABEL = 'PR-48 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW';
    private const EXPECTED_C158_RESULT_REVIEW_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C158_GO_DECISION_FINALIZATION_RECOMMENDATION = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';

    private const GO_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_CONTROLLED_OUTPUT_PUBLICATION_STOPPED';
    private const HOLD_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_CONTROLLED_OUTPUT_PUBLICATION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C158_RESULT_REVIEW_LOCK_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH';
    private const C158_RESULT_REVIEW_FILE_SHA1_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH';
    private const C158_RESULT_REVIEW_CONVERT_FROM_JSON_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C158_RESULT_REVIEW_STATUS_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_STATUS_MISMATCH';
    private const C158_RESULT_REVIEW_PHASE_LABEL_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_PHASE_LABEL_MISMATCH';
    private const C158_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH';
    private const C158_RESULT_REVIEW_INCOMPLETE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C158_RESULT_REVIEW_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_pass',
        'production_live_runtime_controlled_output_publication_result_review_pass',
        'weekly_swing_watchlist_controlled_output_publication_result_reviewed',
        'weekly_swing_watchlist_controlled_output_publication_result_review_manifest_created',
        'ready_for_weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_review',
        'production_live_runtime_controlled_output_publication_operator_go_no_go_review_allowed_next',
        'weekly_swing_watchlist_controlled_output_generation_executed',
        'weekly_swing_watchlist_controlled_output_generation_result_reviewed',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_live_recommendation_generated',
        'weekly_swing_watchlist_controlled_output_publication_executed',
        'weekly_swing_watchlist_controlled_output_published',
        'weekly_swing_watchlist_controlled_publication_artifact_created',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c158_execution_lock_valid',
        'c158_publication_execution_valid',
        'c158_execution_convert_from_json_pass',
        'controlled_publication_lock_valid',
        'controlled_publication_convert_from_json_pass',
        'controlled_publication_integrity_valid',
        'operator_approved',
        'result_review_confirmed',
        'controlled_publication_result_confirmed',
        'controlled_publication_only_confirmed',
        'plan_confirm_unchanged_confirmed',
        'primary_candidate_controlled_publication_result_reviewed',
        'backup_candidate_controlled_publication_result_reviewed',
        'a01_remains_comparator_only',
        'c158_controlled_output_publication_result_review_only',
        'c158_controlled_publication_only',
        'c158_not_free_publication',
        'c158_not_unrestricted_publication',
        'c158_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C158_RESULT_REVIEW_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_controlled_publication_result_reviewed',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/*no-*-test.json',
        'storage/app/watchlist/backtest/*missing-*-test.json',
        'storage/app/watchlist/backtest/*mismatch-*-test.json',
        'storage/app/watchlist/backtest/*negative-*-test.json',
        'storage/app/watchlist/backtest/*invalid-*-test.json',
    ];

    public function execute(
        string $c158ResultReviewArtifact = self::DEFAULT_C158_RESULT_REVIEW_ARTIFACT,
        string $expectedC158ResultReviewHash = self::DEFAULT_EXPECTED_C158_RESULT_REVIEW_HASH,
        string $expectedC158ResultReviewFileSha1 = self::DEFAULT_EXPECTED_C158_RESULT_REVIEW_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $decision = $this->normalizeDecision((string) ($options['operator_decision'] ?? ''));
        $decisionReason = trim((string) ($options['decision_reason'] ?? ''));
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c158ResultReviewArtifact, $expectedC158ResultReviewHash, $expectedC158ResultReviewFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C158_RESULT_REVIEW_LOCK_MISMATCH_STATUS, 'C158 result review artifact missing or unreadable.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, $decision, $decisionReason);
            $artifact['c158_result_review_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C158_RESULT_REVIEW_CONVERT_FROM_JSON_STATUS, 'C158 result review artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C158_RESULT_REVIEW_LOCK_MISMATCH_STATUS, 'C158 result review artifact_hash mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C158_RESULT_REVIEW_FILE_SHA1_MISMATCH_STATUS, 'C158 result review file SHA1 mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $resultReview = $load['payload'];
        if (($resultReview['status'] ?? null) !== self::EXPECTED_C158_RESULT_REVIEW_STATUS || ($resultReview['reason_code'] ?? null) !== self::EXPECTED_C158_RESULT_REVIEW_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C158_RESULT_REVIEW_STATUS_MISMATCH_STATUS, 'C158 result review status/reason is not operator GO/NO-GO ready.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (($resultReview['phase_label'] ?? null) !== self::EXPECTED_C158_RESULT_REVIEW_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C158_RESULT_REVIEW_PHASE_LABEL_MISMATCH_STATUS, 'C158 result review phase label mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->c158ResultReviewNextRecommendationMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C158_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C158 result review next recommendation is not C158 operator GO/NO-GO.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->freePublicationAndPlanGuardClean($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C158 result review has free publication, unrestricted publication, or PLAN/CONFIRM mutation already enabled.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->c158ResultReviewComplete($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C158_RESULT_REVIEW_INCOMPLETE_STATUS, 'C158 result review evidence is incomplete.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->candidateScopeMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C158 result review candidate scope does not match locked operator GO/NO-GO scope.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::APPROVAL_MISSING_STATUS, 'C158 operator GO/NO-GO review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decision === null) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, null, $decisionReason), self::DECISION_INVALID_STATUS, 'C158 operator GO/NO-GO review requires --operator-decision=GO, NO_GO, or HOLD.', $outputPath, $overwrite, null, $decisionReason);
        }
        if (! (bool) ($options['operator_decision_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_NOT_CONFIRMED_STATUS, 'C158 operator GO/NO-GO review requires --operator-decision-confirmed.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decisionReason === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_REASON_MISSING_STATUS, 'C158 operator GO/NO-GO review requires a non-empty --decision-reason so the decision is auditable.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false, $decision, $decisionReason);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true, $decision, $decisionReason);
        $artifact['status'] = $this->statusForDecision($decision);
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = $this->messageForDecision($decision);
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusionForDecision($decision);
        $artifact['next_step_recommendation'] = $this->nextRecommendationForDecision($decision);
        $artifact = array_merge($artifact, $this->decisionTopLevelState($decision));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-49',
            'internal_checkpoint' => 'C158',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'OPERATOR_GO_NO_GO_REVIEW',
            'status' => 'C158_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'reason_code' => 'C158_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_pass' => false,
            'production_live_runtime_controlled_output_publication_operator_go_no_go_review_pass' => false,
            'operator_decision_recorded' => false,
            'operator_decision' => 'UNSET',
            'operator_go_decision' => 'UNSET',
            'operator_no_go_decision' => false,
            'operator_hold_decision' => false,
            'operator_decision_confirmed' => false,
            'operator_decision_reason' => '',
            'ready_for_weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_review' => false,
            'production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed_next' => false,
            'controlled_output_publication_operator_go_no_go_manifest_created' => false,
            'controlled_output_publication_stopped_no_go' => false,
            'controlled_output_publication_deferred_hold' => false,
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
            'c158_result_review_lock_valid' => false,
            'c158_controlled_output_publication_result_review_valid' => false,
            'c158_result_review_convert_from_json_pass' => false,
            'controlled_publication_lock_valid' => false,
            'controlled_publication_integrity_valid' => false,
            'primary_candidate_ready_for_controlled_output_publication_go_decision_finalization_review' => false,
            'backup_candidate_ready_for_controlled_output_publication_go_decision_finalization_review' => false,
            'comparator_candidate_ready_for_controlled_output_publication_go_decision_finalization_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c158_controlled_output_publication_operator_go_no_go_review_only' => true,
            'c158_controlled_publication_only' => true,
            'c158_not_free_publication' => true,
            'c158_not_unrestricted_publication' => true,
            'c158_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C158_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
        ];
    }

    private function decisionTopLevelState(string $decision): array
    {
        $go = $decision === 'GO';

        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_pass' => $go,
            'production_live_runtime_controlled_output_publication_operator_go_no_go_review_pass' => $go,
            'operator_decision_recorded' => true,
            'operator_decision' => $decision,
            'operator_go_decision' => $decision,
            'operator_no_go_decision' => $decision === 'NO_GO',
            'operator_hold_decision' => $decision === 'HOLD',
            'operator_decision_confirmed' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_review' => $go,
            'production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed_next' => $go,
            'controlled_output_publication_operator_go_no_go_manifest_created' => true,
            'controlled_output_publication_stopped_no_go' => $decision === 'NO_GO',
            'controlled_output_publication_deferred_hold' => $decision === 'HOLD',
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => true,
            'weekly_swing_watchlist_controlled_output_publication_executed' => true,
            'weekly_swing_watchlist_controlled_output_published' => true,
            'weekly_swing_watchlist_controlled_publication_artifact_created' => true,
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
            'c158_result_review_lock_valid' => true,
            'c158_controlled_output_publication_result_review_valid' => true,
            'c158_result_review_convert_from_json_pass' => true,
            'controlled_publication_lock_valid' => true,
            'controlled_publication_integrity_valid' => true,
            'primary_candidate_ready_for_controlled_output_publication_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_controlled_output_publication_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_controlled_output_publication_go_decision_finalization_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass, ?string $decision, string $decisionReason): array
    {
        $resultReview = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact = array_merge($artifact, $this->carryForwardTopLevelState($resultReview, $load));
        $artifact['c158_result_review_lock_validation_summary'] = $this->resultReviewLockValidationSummary($load, $resultReview);
        $artifact['c158_controlled_output_publication_result_review_carry_forward_summary'] = $this->resultReviewCarryForwardSummary($resultReview, $pass);
        $artifact['controlled_publication_publication_guard_summary'] = $this->publicationGuardSummary($resultReview);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($resultReview, $pass, $decision);
        $artifact['operator_decision_validation_summary'] = $this->operatorDecisionValidationSummary($options, $decision, $decisionReason, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c158_operator_go_no_go_decision'] = $this->operatorGoNoGoDecision($decision, $decisionReason, $pass);
        $artifact['next_concrete_controlled_output_publication_step_decision'] = $this->nextConcreteStepDecision($decision, $pass);
        $artifact['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_manifest'] = $this->operatorManifest($decision, $decisionReason, $pass);
        $artifact['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_checklist'] = $this->operatorChecklist($options, $decision, $decisionReason, $pass);
        $artifact['c158_candidate_controlled_publication_operator_go_no_go_scorecard'] = $this->candidateScorecard($pass, $decision);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($resultReview);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass, $decision);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass, $decision);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C158_OPERATOR_GO_NO_GO_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_go_decision'] = $decision ?? 'INVALID';
        $artifact['operator_decision_confirmed'] = (bool) ($options['operator_decision_confirmed'] ?? false);
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        return $artifact;
    }

    private function carryForwardTopLevelState(array $resultReview, array $load): array
    {
        return [
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_output_publication_result_reviewed'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_executed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_published' => (bool) ($resultReview['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'weekly_swing_watchlist_controlled_publication_artifact_created' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_artifact_created'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($resultReview['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($resultReview['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($resultReview['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($resultReview['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($resultReview['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c158_result_review_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c158_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c158_controlled_output_publication_result_review_valid' => $this->c158ResultReviewComplete($resultReview),
            'controlled_publication_lock_valid' => (bool) ($resultReview['controlled_publication_lock_valid'] ?? false),
            'controlled_publication_integrity_valid' => (bool) ($resultReview['controlled_publication_integrity_valid'] ?? false),
        ];
    }

    private function c158ResultReviewComplete(array $resultReview): bool
    {
        foreach (self::REQUIRED_C158_RESULT_REVIEW_TRUE_FIELDS as $field) {
            if (($resultReview[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C158_RESULT_REVIEW_FALSE_FIELDS as $field) {
            if (($resultReview[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($resultReview['topic_code'] ?? null) !== 'C158_CONTROLLED_OUTPUT_PUBLICATION' || ($resultReview['topic_stage'] ?? null) !== 'RESULT_REVIEW') {
            return false;
        }

        return true;
    }

    private function freePublicationAndPlanGuardClean(array $resultReview): bool
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
            if (($resultReview[$field] ?? null) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $resultReview): bool
    {
        return ($resultReview['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($resultReview['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($resultReview['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($resultReview['primary_candidate_controlled_publication_result_reviewed'] ?? null) === true
            && ($resultReview['backup_candidate_controlled_publication_result_reviewed'] ?? null) === true
            && ($resultReview['comparator_candidate_controlled_publication_result_reviewed'] ?? null) === false
            && ($resultReview['a01_remains_comparator_only'] ?? null) === true
            && ($resultReview['a01_promoted'] ?? false) === false
            && ($resultReview['candidate_promotion_executed'] ?? false) === false
            && ($resultReview['strategy_retune_executed'] ?? false) === false
            && ($resultReview['scoring_mutation_executed'] ?? false) === false
            && ($resultReview['catalog_selection_changed'] ?? false) === false
            && ($resultReview['runtime_selection_changed'] ?? false) === false;
    }

    private function c158ResultReviewNextRecommendationMatches(array $resultReview): bool
    {
        foreach ([['next_step_recommendation'], ['planned_next_summary', 'planned_next_review']] as $path) {
            if ($this->valueAt($resultReview, $path) !== self::EXPECTED_C158_RESULT_REVIEW_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function resultReviewLockValidationSummary(array $load, array $resultReview): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C158_RESULT_REVIEW',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C158_RESULT_REVIEW_STATUS,
            'actual_status' => $resultReview['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C158_RESULT_REVIEW_PHASE_LABEL,
            'actual_phase_label' => $resultReview['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C158_RESULT_REVIEW_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c158ResultReviewNextRecommendationMatches($resultReview),
            'c158_result_review_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function resultReviewCarryForwardSummary(array $resultReview, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c158_result_review_valid' => $this->c158ResultReviewComplete($resultReview),
            'topic_code' => (string) ($resultReview['topic_code'] ?? ''),
            'topic_stage' => (string) ($resultReview['topic_stage'] ?? ''),
            'controlled_publication_result_reviewed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_output_publication_result_reviewed'] ?? false),
            'controlled_publication_hash' => $resultReview['controlled_publication_hash'] ?? null,
            'controlled_publication_file_sha1' => $resultReview['controlled_publication_file_sha1'] ?? null,
            'controlled_publication_record_count' => $resultReview['controlled_publication_record_count'] ?? null,
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'operator_go_no_go_review_completed' => $pass,
        ];
    }

    private function publicationGuardSummary(array $resultReview): array
    {
        return [
            'guard_reviewed' => true,
            'free_publication_and_plan_guard_clean' => $this->freePublicationAndPlanGuardClean($resultReview),
            'official_output_published' => false,
            'publication_allowed' => false,
            'controlled_publication_allowed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $resultReview, bool $pass, ?string $decision): array
    {
        $go = $decision === 'GO' && $pass;

        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($resultReview),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
            'scoring_mutation_executed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
        ];
    }

    private function operatorDecisionValidationSummary(array $options, ?string $decision, string $reason, bool $pass): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_decision_required' => true,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_valid' => $decision !== null,
            'operator_decision_confirmed' => (bool) ($options['operator_decision_confirmed'] ?? false),
            'decision_reason_required' => true,
            'decision_reason_present' => $reason !== '',
            'operator_go_no_go_review_valid' => $pass,
        ];
    }

    private function operatorGoNoGoDecision(?string $decision, string $reason, bool $pass): array
    {
        return [
            'decision_recorded' => $pass,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_reason' => $reason,
            'operator_go_decision' => $decision === 'GO',
            'operator_no_go_decision' => $decision === 'NO_GO',
            'operator_hold_decision' => $decision === 'HOLD',
            'ready_for_go_decision_finalization_review' => $pass && $decision === 'GO',
            'controlled_publication_stopped_no_go' => $pass && $decision === 'NO_GO',
            'controlled_publication_deferred_hold' => $pass && $decision === 'HOLD',
        ];
    }

    private function nextConcreteStepDecision(?string $decision, bool $pass): array
    {
        return [
            'decision_valid' => $pass,
            'operator_decision' => $decision ?? 'INVALID',
            'next_recommendation' => $pass ? $this->nextRecommendationForDecision((string) $decision) : 'C158_TARGETED_RESULT_REVIEW_OR_OPERATOR_DECISION_REPAIR',
            'same_topic_number_for_next_stage' => true,
            'free_publication_allowed_next' => false,
            'unrestricted_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
        ];
    }

    private function operatorManifest(?string $decision, string $reason, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'controlled_output_publication_operator_go_no_go_review',
            'operator_go_no_go_artifact_only' => true,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_reason' => $reason,
            'operator_go_no_go_review_pass' => $pass && $decision === 'GO',
            'ready_for_go_decision_finalization_review' => $pass && $decision === 'GO',
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'operator_go_no_go_used_for_publication' => false,
            'operator_go_no_go_used_for_plan_confirm_mutation' => false,
        ];
    }

    private function operatorChecklist(array $options, ?string $decision, string $reason, bool $pass): array
    {
        return [
            'operator_go_no_go_reviewed' => $pass,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_confirmed' => (bool) ($options['operator_decision_confirmed'] ?? false),
            'decision_reason_present' => $reason !== '',
            'artifact_only' => true,
            'same_topic_number_for_next_stage' => true,
            'weekly_swing_stock_recommendation_free_published_in_c158_operator_review' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function candidateScorecard(bool $pass, ?string $decision): array
    {
        $go = $pass && $decision === 'GO';

        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c158_role' => 'primary_candidate_ready_for_controlled_publication_go_finalization',
                'ready_for_go_decision_finalization_review' => $go,
                'controlled_published' => true,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c158_role' => 'backup_candidate_ready_for_controlled_publication_go_finalization',
                'ready_for_go_decision_finalization_review' => $go,
                'controlled_published' => true,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c158_role' => 'comparator_only_candidate',
                'ready_for_go_decision_finalization_review' => false,
                'controlled_published' => false,
                'free_published' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $resultReview): array
    {
        return [
            'validation_completed' => true,
            'free_publication_and_plan_guard_clean' => $this->freePublicationAndPlanGuardClean($resultReview),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c158_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c158_result_review_artifact_not_modified' => true,
            'c158_operator_go_no_go_review_is_artifact_only_not_free_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass, ?string $decision): array
    {
        return [
            'progress_marker' => 'PR-49_C158_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'OPERATOR_GO_NO_GO_REVIEW',
            'c158_result_review_carried_forward' => true,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_go_no_go_review_completed' => $pass,
            'go_decision_finalization_allowed_next' => $pass && $decision === 'GO',
            'same_topic_number_for_next_stage' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass, ?string $decision): array
    {
        return [
            'planned_next_review' => $pass ? $this->nextRecommendationForDecision((string) $decision) : 'C158_TARGETED_RESULT_REVIEW_OR_OPERATOR_DECISION_REPAIR',
            'planned_next_scope' => $pass && $decision === 'GO'
                ? 'same-topic C158 controlled output publication go decision finalization review only; still no unrestricted publication or PLAN/CONFIRM mutation from operator review'
                : 'operator decision closed or targeted result-review/operator-decision repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass && $decision === 'GO' ? [
                'locked C158 operator go/no-go artifact hash',
                'locked C158 operator go/no-go file SHA1',
                'operator GO decision confirmed',
                'free publication still disabled',
                'PLAN/CONFIRM unchanged',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C158 operator review validates the C158 result review artifact hash and file SHA1 before recording an operator decision.',
            'C158 operator review records GO, NO_GO, or HOLD only.',
            'C158 operator review does not free-publish output, allow unrestricted publication, or mutate PLAN/CONFIRM.',
            'C158 operator review keeps E02 primary, B01 backup, and A01 comparator-only.',
            'GO may only recommend same-topic go decision finalization review next.',
        ];
    }

    private function statusForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::GO_STATUS;
        }
        if ($decision === 'NO_GO') {
            return self::NO_GO_STATUS;
        }

        return self::HOLD_STATUS;
    }

    private function messageForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C158 operator GO decision recorded for controlled publication. Go decision finalization review is allowed next; free publication, unrestricted publication, and PLAN/CONFIRM mutation remain locked.';
        }
        if ($decision === 'NO_GO') {
            return 'C158 operator NO_GO decision recorded. Controlled publication progression is stopped; free publication, unrestricted publication, and PLAN/CONFIRM mutation remain locked.';
        }

        return 'C158 operator HOLD decision recorded. Controlled publication progression is deferred; free publication, unrestricted publication, and PLAN/CONFIRM mutation remain locked.';
    }

    private function diagnosticConclusionForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C158_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_RECORDED_READY_FOR_GO_DECISION_FINALIZATION_NOT_FREE_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        }
        if ($decision === 'NO_GO') {
            return 'C158_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_NO_GO_RECORDED_PUBLICATION_PROGRESSION_STOPPED';
        }

        return 'C158_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_HOLD_RECORDED_PUBLICATION_PROGRESSION_DEFERRED';
    }

    private function nextRecommendationForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::C158_GO_DECISION_FINALIZATION_RECOMMENDATION;
        }
        if ($decision === 'NO_GO') {
            return 'C158_NO_GO_CLOSE_CONTROLLED_OUTPUT_PUBLICATION';
        }

        return 'C158_HOLD_KEEP_CONTROLLED_PUBLICATION_LOCKED_UNTIL_OPERATOR_WINDOW';
    }

    private function normalizeDecision(string $decision): ?string
    {
        $normalized = strtoupper(trim(str_replace('-', '_', $decision)));
        if (in_array($normalized, ['GO', 'NO_GO', 'HOLD'], true)) {
            return $normalized;
        }

        return null;
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c158_result_review' => [
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
            'expected_c158_result_review_hash' => $load['expected_hash'],
            'actual_c158_result_review_hash' => $load['actual_hash'],
            'c158_result_review_hash_match' => $load['hash_match'],
            'expected_c158_result_review_file_sha1' => $load['expected_file_sha1'],
            'actual_c158_result_review_file_sha1' => $load['actual_file_sha1'],
            'c158_result_review_file_sha1_match' => $load['file_sha1_match'],
            'c158_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
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
            'hash_match' => $exists && $actualHash === $expectedHash,
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $exists && $actualFileSha1 === strtoupper($expectedFileSha1),
            'json_error' => $jsonError,
            'case_insensitive_duplicate_keys' => $duplicateKeys,
            'convert_from_json_pass' => $exists && $jsonError === JSON_ERROR_NONE && $duplicateKeys === [],
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

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => array_values(array_filter($status)),
            'failure_count' => count(array_values(array_filter($status))),
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false, $decision);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false, $decision);
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
