<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-63 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C161_RESULT_REVIEW_ARTIFACT = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-result-review.json';
    public const DEFAULT_EXPECTED_C161_RESULT_REVIEW_HASH = '1ccb2bc315cbf66c091f25310ff83f33394cd492';
    public const DEFAULT_EXPECTED_C161_RESULT_REVIEW_FILE_SHA1 = '884CFDB9AC48FF5DA0603147CAE880BF4C934B58';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C161_RESULT_REVIEW_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_PASSED_READY_FOR_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C161_RESULT_REVIEW_PHASE_LABEL = 'PR-62 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW';
    private const EXPECTED_C161_RESULT_REVIEW_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const GO_DECISION_FINALIZATION_RECOMMENDATION = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';

    private const GO_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_PLAN_CONFIRM_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_PLAN_CONFIRM_COMPLETION_PROGRESSION_STOPPED';
    private const HOLD_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_PLAN_CONFIRM_COMPLETION_PROGRESSION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C161_RESULT_REVIEW_LOCK_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C161_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH';
    private const C161_RESULT_REVIEW_FILE_SHA1_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C161_RESULT_REVIEW_FILE_SHA1_LOCK_MISMATCH';
    private const C161_RESULT_REVIEW_CONVERT_FROM_JSON_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C161_RESULT_REVIEW_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C161_RESULT_REVIEW_STATUS_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C161_RESULT_REVIEW_STATUS_MISMATCH';
    private const C161_RESULT_REVIEW_PHASE_LABEL_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C161_RESULT_REVIEW_PHASE_LABEL_MISMATCH';
    private const C161_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C161_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH';
    private const C161_RESULT_REVIEW_INCOMPLETE_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C161_RESULT_REVIEW_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_RESULT_REVIEW_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_result_review_pass',
        'production_live_runtime_plan_confirm_completion_result_review_pass',
        'weekly_swing_watchlist_plan_confirm_completion_result_reviewed',
        'weekly_swing_watchlist_plan_confirm_completion_result_review_manifest_created',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_operator_go_no_go_review',
        'production_live_runtime_plan_confirm_completion_operator_go_no_go_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_operator_go_no_go_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'weekly_swing_watchlist_official_output_generated',
        'result_review_confirmed',
        'controlled_completion_result_confirmed',
        'controlled_completion_only_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c161_execution_lock_valid',
        'c161_completion_execution_valid',
        'c161_execution_convert_from_json_pass',
        'controlled_completion_lock_valid',
        'controlled_completion_integrity_valid',
        'controlled_completion_convert_from_json_pass',
        'operator_approved',
        'primary_candidate_completion_result_reviewed',
        'backup_candidate_completion_result_reviewed',
        'a01_remains_comparator_only',
        'c161_plan_confirm_completion_result_review_only',
        'c161_controlled_completion_only',
        'c161_not_plan_confirm_mutation',
        'c161_not_live_plan_confirm_rollout',
        'c161_not_publication',
        'c161_topic_number_retained_for_operator_go_no_go',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_RESULT_REVIEW_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_completion_result_reviewed',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c161-*operator-go-no-go*-test.json',
        'storage/app/watchlist/backtest/c161-*operator-*-test.json',
        'storage/app/watchlist/backtest/c161-*go-no-go*-test.json',
        'storage/app/watchlist/backtest/c161-*plan-confirm-operator*-test.json',
        'storage/app/watchlist/backtest/c161-*negative-*-test.json',
        'storage/app/watchlist/backtest/c161-*missing-*-test.json',
        'storage/app/watchlist/backtest/c161-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c161-*invalid-*-test.json',
    ];

    public function execute(
        string $c161ResultReviewArtifact = self::DEFAULT_C161_RESULT_REVIEW_ARTIFACT,
        string $expectedC161ResultReviewHash = self::DEFAULT_EXPECTED_C161_RESULT_REVIEW_HASH,
        string $expectedC161ResultReviewFileSha1 = self::DEFAULT_EXPECTED_C161_RESULT_REVIEW_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $decision = $this->normalizeDecision((string) ($options['operator_decision'] ?? ''));
        $decisionReason = trim((string) ($options['decision_reason'] ?? ''));
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c161ResultReviewArtifact, $expectedC161ResultReviewHash, $expectedC161ResultReviewFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C161_RESULT_REVIEW_LOCK_MISMATCH_STATUS, 'C161 PLAN/CONFIRM completion result review artifact missing or unreadable.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, $decision, $decisionReason);
            $artifact['c161_result_review_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C161_RESULT_REVIEW_CONVERT_FROM_JSON_STATUS, 'C161 PLAN/CONFIRM completion result review artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C161_RESULT_REVIEW_LOCK_MISMATCH_STATUS, 'C161 PLAN/CONFIRM completion result review artifact_hash mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C161_RESULT_REVIEW_FILE_SHA1_MISMATCH_STATUS, 'C161 PLAN/CONFIRM completion result review file SHA1 mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $resultReview = $load['payload'];
        if (($resultReview['status'] ?? null) !== self::EXPECTED_C161_RESULT_REVIEW_STATUS || ($resultReview['reason_code'] ?? null) !== self::EXPECTED_C161_RESULT_REVIEW_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C161_RESULT_REVIEW_STATUS_MISMATCH_STATUS, 'C161 result review status/reason is not operator GO/NO-GO ready.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (($resultReview['phase_label'] ?? null) !== self::EXPECTED_C161_RESULT_REVIEW_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C161_RESULT_REVIEW_PHASE_LABEL_MISMATCH_STATUS, 'C161 result review phase label mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->resultReviewNextRecommendationMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C161_RESULT_REVIEW_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C161 result review next recommendation is not C161 PLAN/CONFIRM completion operator GO/NO-GO.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->publicationAndPlanGuardClean($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C161 result review has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->resultReviewComplete($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C161_RESULT_REVIEW_INCOMPLETE_STATUS, 'C161 result review evidence is incomplete.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->candidateScopeMatches($resultReview)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C161 PLAN/CONFIRM completion result review candidate scope does not match locked operator GO/NO-GO scope.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::APPROVAL_MISSING_STATUS, 'C161 operator GO/NO-GO review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decision === null) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, null, $decisionReason), self::DECISION_INVALID_STATUS, 'C161 operator GO/NO-GO review requires --operator-decision=GO, NO_GO, or HOLD.', $outputPath, $overwrite, null, $decisionReason);
        }
        if (! (bool) ($options['operator_decision_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_NOT_CONFIRMED_STATUS, 'C161 operator GO/NO-GO review requires --operator-decision-confirmed.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decisionReason === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_REASON_MISSING_STATUS, 'C161 operator GO/NO-GO review requires a non-empty --decision-reason so the decision is auditable.', $outputPath, $overwrite, $decision, $decisionReason);
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
            'phase_checkpoint' => 'PR-63',
            'internal_checkpoint' => 'C161',
            'topic_code' => 'C161_PLAN_CONFIRM_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW',
            'status' => 'C161_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'reason_code' => 'C161_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_operator_go_no_go_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_operator_go_no_go_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_operator_go_no_go_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_operator_go_no_go_review_pass' => false,
            'operator_decision_recorded' => false,
            'operator_decision' => 'UNSET',
            'operator_go_decision' => false,
            'operator_no_go_decision' => false,
            'operator_hold_decision' => false,
            'operator_decision_confirmed' => false,
            'operator_decision_reason' => '',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_review' => false,
            'production_live_runtime_plan_confirm_completion_go_decision_finalization_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_operator_go_no_go_manifest_created' => false,
            'plan_confirm_completion_stopped_no_go' => false,
            'plan_confirm_completion_deferred_hold' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
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
            'c161_result_review_lock_valid' => false,
            'c161_plan_confirm_completion_result_review_valid' => false,
            'c161_result_review_convert_from_json_pass' => false,
            'c161_execution_lock_valid' => false,
            'c161_completion_execution_valid' => false,
            'c161_execution_convert_from_json_pass' => false,
            'controlled_completion_lock_valid' => false,
            'controlled_completion_integrity_valid' => false,
            'controlled_completion_convert_from_json_pass' => false,
            'controlled_completion_path' => '',
            'controlled_completion_hash' => '',
            'controlled_completion_file_sha1' => '',
            'controlled_completion_record_count' => 0,
            'result_review_confirmed' => false,
            'controlled_completion_result_confirmed' => false,
            'controlled_completion_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_go_decision_finalization_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_go_decision_finalization_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'c161_plan_confirm_completion_result_review_only' => true,
            'c161_plan_confirm_completion_operator_go_no_go_review_only' => true,
            'c161_controlled_completion_only' => true,
            'c161_not_publication' => true,
            'c161_not_unrestricted_publication' => true,
            'c161_not_plan_confirm_mutation' => true,
            'c161_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C161_OPERATOR_GO_NO_GO_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
        ];
    }

    private function decisionTopLevelState(string $decision): array
    {
        $go = $decision === 'GO';

        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_operator_go_no_go_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_operator_go_no_go_review_pass' => $go,
            'production_live_runtime_plan_confirm_completion_operator_go_no_go_review_pass' => $go,
            'operator_decision_recorded' => true,
            'operator_decision' => $decision,
            'operator_go_decision' => $go,
            'operator_no_go_decision' => $decision === 'NO_GO',
            'operator_hold_decision' => $decision === 'HOLD',
            'operator_decision_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_review' => $go,
            'production_live_runtime_plan_confirm_completion_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_review_allowed_next' => $go,
            'weekly_swing_watchlist_plan_confirm_completion_operator_go_no_go_manifest_created' => true,
            'plan_confirm_completion_stopped_no_go' => $decision === 'NO_GO',
            'plan_confirm_completion_deferred_hold' => $decision === 'HOLD',
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => true,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => true,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => true,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
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
            'c161_result_review_lock_valid' => true,
            'c161_plan_confirm_completion_result_review_valid' => true,
            'c161_result_review_convert_from_json_pass' => true,
            'c161_execution_lock_valid' => true,
            'c161_completion_execution_valid' => true,
            'c161_execution_convert_from_json_pass' => true,
            'controlled_completion_lock_valid' => true,
            'controlled_completion_integrity_valid' => true,
            'controlled_completion_convert_from_json_pass' => true,
            'primary_candidate_ready_for_plan_confirm_completion_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_plan_confirm_completion_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_plan_confirm_completion_go_decision_finalization_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass, ?string $decision, string $decisionReason): array
    {
        $resultReview = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact = array_merge($artifact, $this->carryForwardTopLevelState($resultReview, $load));
        $artifact['c161_result_review_lock_validation_summary'] = $this->resultReviewLockValidationSummary($load);
        $artifact['c161_plan_confirm_completion_result_review_carry_forward_summary'] = $this->resultReviewCarryForwardSummary($resultReview, $pass);
        $artifact['plan_confirm_safety_guard_summary'] = $this->planConfirmSafetyGuardSummary($resultReview);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($resultReview, $pass, $decision);
        $artifact['operator_decision_validation_summary'] = $this->operatorDecisionValidationSummary($options, $decision, $decisionReason, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c161_operator_go_no_go_decision'] = $this->operatorGoNoGoDecision($decision, $decisionReason, $pass);
        $artifact['next_concrete_plan_confirm_completion_step_decision'] = $this->nextConcreteStepDecision($decision, $pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_operator_go_no_go_manifest'] = $this->operatorManifest($decision, $decisionReason, $pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_operator_go_no_go_checklist'] = $this->operatorChecklist($options, $decision, $decisionReason, $pass);
        $artifact['c161_candidate_plan_confirm_completion_operator_go_no_go_scorecard'] = $this->candidateScorecard($pass, $decision);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($resultReview);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass, $decision);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass, $decision);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C161_OPERATOR_GO_NO_GO_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');
        $artifact['operator_decision'] = $decision ?? 'INVALID';
        $artifact['operator_go_decision'] = $decision === 'GO';
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
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
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
            'c161_result_review_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c161_plan_confirm_completion_result_review_valid' => $this->resultReviewComplete($resultReview),
            'c161_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c161_execution_lock_valid' => (bool) ($resultReview['c161_execution_lock_valid'] ?? false),
            'c161_completion_execution_valid' => (bool) ($resultReview['c161_completion_execution_valid'] ?? false),
            'c161_execution_convert_from_json_pass' => (bool) ($resultReview['c161_execution_convert_from_json_pass'] ?? false),
            'controlled_completion_lock_valid' => (bool) ($resultReview['controlled_completion_lock_valid'] ?? false),
            'controlled_completion_integrity_valid' => (bool) ($resultReview['controlled_completion_integrity_valid'] ?? false),
            'controlled_completion_convert_from_json_pass' => (bool) ($resultReview['controlled_completion_convert_from_json_pass'] ?? false),
            'controlled_completion_path' => (string) ($resultReview['controlled_completion_path'] ?? ''),
            'controlled_completion_hash' => (string) ($resultReview['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($resultReview['controlled_completion_file_sha1'] ?? ''),
            'controlled_completion_record_count' => (int) ($resultReview['controlled_completion_record_count'] ?? 0),
            'result_review_confirmed' => (bool) ($resultReview['result_review_confirmed'] ?? false),
            'controlled_completion_result_confirmed' => (bool) ($resultReview['controlled_completion_result_confirmed'] ?? false),
            'controlled_completion_only_confirmed' => (bool) ($resultReview['controlled_completion_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($resultReview['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($resultReview['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($resultReview['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_completion_result_reviewed' => (bool) ($resultReview['primary_candidate_completion_result_reviewed'] ?? false),
            'backup_candidate_completion_result_reviewed' => (bool) ($resultReview['backup_candidate_completion_result_reviewed'] ?? false),
            'comparator_candidate_completion_result_reviewed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => (bool) ($resultReview['a01_remains_comparator_only'] ?? true),
        ];
    }

    private function resultReviewComplete(array $resultReview): bool
    {
        foreach (self::REQUIRED_RESULT_REVIEW_TRUE_FIELDS as $field) {
            if (($resultReview[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_RESULT_REVIEW_FALSE_FIELDS as $field) {
            if (($resultReview[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($resultReview['topic_code'] ?? null) === 'C161_PLAN_CONFIRM_COMPLETION'
            && ($resultReview['topic_stage'] ?? null) === 'PLAN_CONFIRM_COMPLETION_RESULT_REVIEW';
    }

    private function candidateScopeMatches(array $resultReview): bool
    {
        return ($resultReview['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($resultReview['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($resultReview['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($resultReview['primary_candidate_completion_result_reviewed'] ?? null) === true
            && ($resultReview['backup_candidate_completion_result_reviewed'] ?? null) === true
            && ($resultReview['comparator_candidate_completion_result_reviewed'] ?? null) === false
            && ($resultReview['a01_remains_comparator_only'] ?? null) === true
            && ($resultReview['a01_promoted'] ?? false) !== true;
    }

    private function publicationAndPlanGuardClean(array $source): bool
    {
        foreach (self::REQUIRED_RESULT_REVIEW_FALSE_FIELDS as $field) {
            if ($field === 'comparator_candidate_completion_result_reviewed' || $field === 'temporary_negative_artifacts_remaining') {
                continue;
            }
            if (($source[$field] ?? null) === true) {
                return false;
            }
        }

        return true;
    }

    private function resultReviewNextRecommendationMatches(array $resultReview): bool
    {
        return ($resultReview['next_step_recommendation'] ?? null) === self::EXPECTED_C161_RESULT_REVIEW_NEXT_RECOMMENDATION
            && $this->valueAt($resultReview, ['planned_next_summary', 'planned_next_review']) === self::EXPECTED_C161_RESULT_REVIEW_NEXT_RECOMMENDATION;
    }

    private function resultReviewLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C161_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function resultReviewCarryForwardSummary(array $resultReview, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c161_result_review_valid' => $this->resultReviewComplete($resultReview),
            'topic_code' => (string) ($resultReview['topic_code'] ?? ''),
            'topic_stage' => (string) ($resultReview['topic_stage'] ?? ''),
            'controlled_completion_result_reviewed' => (bool) ($resultReview['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'controlled_completion_hash' => (string) ($resultReview['controlled_completion_hash'] ?? ''),
            'controlled_completion_file_sha1' => (string) ($resultReview['controlled_completion_file_sha1'] ?? ''),
            'official_output_published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'operator_go_no_go_review_valid' => $pass,
        ];
    }

    private function planConfirmSafetyGuardSummary(array $resultReview): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($resultReview),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $resultReview, bool $pass, ?string $decision): array
    {
        $go = $pass && $decision === 'GO';

        return [
            'validation_completed' => true,
            'candidate_scope_match' => $this->candidateScopeMatches($resultReview),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_finalization' => $go,
            'backup_candidate_ready_for_finalization' => $go,
            'a01_remains_comparator_only' => true,
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
            'ready_for_plan_confirm_completion_go_decision_finalization_review' => $pass && $decision === 'GO',
            'plan_confirm_completion_stopped_no_go' => $pass && $decision === 'NO_GO',
            'plan_confirm_completion_deferred_hold' => $pass && $decision === 'HOLD',
        ];
    }

    private function nextConcreteStepDecision(?string $decision, bool $pass): array
    {
        return [
            'decision_valid' => $pass,
            'operator_decision' => $decision ?? 'INVALID',
            'next_recommendation' => $pass ? $this->nextRecommendationForDecision((string) $decision) : 'C161_TARGETED_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_OR_OPERATOR_DECISION_REPAIR',
            'same_topic_number_for_next_stage' => true,
            'free_publication_allowed_next' => false,
            'unrestricted_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function operatorManifest(?string $decision, string $reason, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'plan_confirm_completion_operator_go_no_go_review',
            'operator_go_no_go_artifact_only' => true,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_decision_reason' => $reason,
            'operator_go_no_go_review_pass' => $pass && $decision === 'GO',
            'ready_for_go_decision_finalization_review' => $pass && $decision === 'GO',
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'operator_go_no_go_used_for_publication' => false,
            'operator_go_no_go_used_for_plan_confirm_mutation' => false,
            'operator_go_no_go_used_for_live_plan_confirm_rollout' => false,
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
            'weekly_swing_stock_recommendation_free_published_in_c161_operator_review' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScorecard(bool $pass, ?string $decision): array
    {
        $go = $pass && $decision === 'GO';

        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c161_role' => 'primary_candidate_ready_for_plan_confirm_completion_go_finalization',
                'ready_for_plan_confirm_completion_go_decision_finalization_review' => $go,
                'plan_confirm_completion_result_reviewed' => true,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c161_role' => 'backup_candidate_ready_for_plan_confirm_completion_go_finalization',
                'ready_for_plan_confirm_completion_go_decision_finalization_review' => $go,
                'plan_confirm_completion_result_reviewed' => true,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c161_role' => 'comparator_only_candidate',
                'ready_for_plan_confirm_completion_go_decision_finalization_review' => false,
                'plan_confirm_completion_result_reviewed' => false,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $resultReview): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($resultReview),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($resultReview['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
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
            'c161_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c161_result_review_artifact_not_modified' => true,
            'c161_operator_go_no_go_review_is_artifact_only_not_plan_mutation_or_publication' => true,
            'c161_topic_number_retained_for_next_stage' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass, ?string $decision): array
    {
        return [
            'progress_marker' => 'PR-63_C161_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW',
            'topic_code' => 'C161_PLAN_CONFIRM_COMPLETION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_OPERATOR_GO_NO_GO_REVIEW',
            'c161_result_review_carried_forward' => true,
            'operator_decision' => $decision ?? 'INVALID',
            'operator_go_no_go_review_completed' => $pass,
            'go_decision_finalization_allowed_next' => $pass && $decision === 'GO',
            'same_topic_number_for_next_stage' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass, ?string $decision): array
    {
        return [
            'planned_next_review' => $pass ? $this->nextRecommendationForDecision((string) $decision) : 'C161_TARGETED_PLAN_CONFIRM_COMPLETION_RESULT_REVIEW_OR_OPERATOR_DECISION_REPAIR',
            'planned_next_scope' => $pass && $decision === 'GO'
                ? 'same-topic C161 PLAN/CONFIRM completion go decision finalization review only; still no PLAN/CONFIRM mutation, activated-catalog read, live rollout, unrestricted publication, or free publication from operator review'
                : 'operator decision closed/deferred or targeted result-review/operator-decision repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass && $decision === 'GO' ? [
                'locked C161 PLAN/CONFIRM completion operator GO/NO-GO artifact hash',
                'locked C161 PLAN/CONFIRM completion operator GO/NO-GO file SHA1',
                'operator GO decision confirmed',
                'PLAN/CONFIRM unchanged',
                'live rollout still disabled',
                'free publication still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C161 operator review validates the C161 PLAN/CONFIRM completion result review artifact hash and file SHA1 before recording an operator decision.',
            'C161 operator review records GO, NO_GO, or HOLD only.',
            'C161 operator review does not mutate PLAN/CONFIRM, read the activated catalog, execute live PLAN/CONFIRM rollout, allow unrestricted publication, or free-publish output.',
            'C161 operator review keeps E02 primary, B01 backup, and A01 comparator-only.',
            'GO may only recommend same-topic C161 PLAN/CONFIRM completion go decision finalization review next.',
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
            return 'C161 operator GO decision recorded for PLAN/CONFIRM completion. Go decision finalization review is allowed next; PLAN/CONFIRM mutation, live rollout, unrestricted publication, and free publication remain locked.';
        }
        if ($decision === 'NO_GO') {
            return 'C161 operator NO_GO decision recorded. PLAN/CONFIRM completion progression is stopped; PLAN/CONFIRM mutation, live rollout, unrestricted publication, and free publication remain locked.';
        }

        return 'C161 operator HOLD decision recorded. PLAN/CONFIRM completion progression is deferred; PLAN/CONFIRM mutation, live rollout, unrestricted publication, and free publication remain locked.';
    }

    private function diagnosticConclusionForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C161_PLAN_CONFIRM_COMPLETION_OPERATOR_GO_RECORDED_READY_FOR_GO_DECISION_FINALIZATION_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        }
        if ($decision === 'NO_GO') {
            return 'C161_PLAN_CONFIRM_COMPLETION_OPERATOR_NO_GO_RECORDED_PLAN_CONFIRM_COMPLETION_PROGRESSION_STOPPED';
        }

        return 'C161_PLAN_CONFIRM_COMPLETION_OPERATOR_HOLD_RECORDED_PLAN_CONFIRM_COMPLETION_PROGRESSION_DEFERRED';
    }

    private function nextRecommendationForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::GO_DECISION_FINALIZATION_RECOMMENDATION;
        }
        if ($decision === 'NO_GO') {
            return 'C161_NO_GO_CLOSE_PLAN_CONFIRM_COMPLETION';
        }

        return 'C161_HOLD_KEEP_PLAN_CONFIRM_COMPLETION_LOCKED_UNTIL_OPERATOR_WINDOW';
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
            'c161_plan_confirm_completion_result_review' => [
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
            'expected_c161_result_review_hash' => $load['expected_hash'],
            'actual_c161_result_review_hash' => $load['actual_hash'],
            'c161_result_review_hash_match' => $load['hash_match'],
            'expected_c161_result_review_file_sha1' => $load['expected_file_sha1'],
            'actual_c161_result_review_file_sha1' => $load['actual_file_sha1'],
            'c161_result_review_file_sha1_match' => $load['file_sha1_match'],
            'c161_result_review_convert_from_json_pass' => $load['convert_from_json_pass'],
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
