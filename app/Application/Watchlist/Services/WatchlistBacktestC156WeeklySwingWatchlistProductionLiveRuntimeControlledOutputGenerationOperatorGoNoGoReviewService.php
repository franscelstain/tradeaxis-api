<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-44 / C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C155_ARTIFACT = 'storage/app/watchlist/backtest/c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review.json';
    public const DEFAULT_EXPECTED_C155_HASH = '6fa40eafa588299db84b465202ea060a310d0d12';
    public const DEFAULT_EXPECTED_C155_FILE_SHA1 = '637A4D7EAE383CDCD8804040384367439847B16D';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C155_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C155_PHASE_LABEL = 'PR-43 / C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW';
    private const EXPECTED_C155_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C157_GO_RECOMMENDATION = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';

    private const GO_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';
    private const NO_GO_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_CONTROLLED_OUTPUT_GENERATION_STOPPED';
    private const HOLD_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_CONTROLLED_OUTPUT_GENERATION_DEFERRED';
    private const APPROVAL_MISSING_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const DECISION_INVALID_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID';
    private const DECISION_NOT_CONFIRMED_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED';
    private const DECISION_REASON_MISSING_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C155_LOCK_MISMATCH_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_ARTIFACT_LOCK_MISMATCH';
    private const C155_FILE_SHA1_MISMATCH_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_FILE_SHA1_LOCK_MISMATCH';
    private const C155_CONVERT_FROM_JSON_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C155_STATUS_MISMATCH_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_STATUS_MISMATCH';
    private const C155_PHASE_LABEL_MISMATCH_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_PHASE_LABEL_MISMATCH';
    private const C155_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_NEXT_RECOMMENDATION_MISMATCH';
    private const C155_RESULT_REVIEW_INCOMPLETE_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_RESULT_REVIEW_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C155_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_pass',
        'production_live_runtime_controlled_output_generation_result_review_pass',
        'weekly_swing_watchlist_controlled_output_generation_result_reviewed',
        'weekly_swing_watchlist_controlled_output_generation_result_review_manifest_created',
        'ready_for_weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_review',
        'production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed_next',
        'weekly_swing_watchlist_controlled_output_generation_executed',
        'weekly_swing_watchlist_controlled_output_generated',
        'weekly_swing_watchlist_controlled_output_artifact_created',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_live_recommendation_generated',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c154_lock_valid',
        'c154_controlled_output_generation_execution_valid',
        'c154_convert_from_json_pass',
        'controlled_output_lock_valid',
        'controlled_output_convert_from_json_pass',
        'controlled_output_integrity_valid',
        'primary_candidate_controlled_output_result_reviewed',
        'backup_candidate_controlled_output_result_reviewed',
        'a01_remains_comparator_only',
        'c155_controlled_output_generation_result_review_only',
        'c155_not_publication',
        'c155_not_unrestricted_publication',
        'c155_not_plan_confirm_mutation',
        'operator_approved',
        'result_review_confirmed',
        'no_publication_confirmed',
        'plan_confirm_unchanged_confirmed',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C155_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_controlled_output_result_reviewed',
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
        string $c155Artifact = self::DEFAULT_C155_ARTIFACT,
        string $expectedC155Hash = self::DEFAULT_EXPECTED_C155_HASH,
        string $expectedC155FileSha1 = self::DEFAULT_EXPECTED_C155_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $decision = $this->normalizeDecision((string) ($options['operator_decision'] ?? ''));
        $decisionReason = trim((string) ($options['decision_reason'] ?? ''));
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c155Artifact, $expectedC155Hash, $expectedC155FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C155_LOCK_MISMATCH_STATUS, 'C155 artifact missing or unreadable.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, $decision, $decisionReason);
            $artifact['c155_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C155_CONVERT_FROM_JSON_STATUS, 'C155 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C155_LOCK_MISMATCH_STATUS, 'C155 artifact_hash mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C155_FILE_SHA1_MISMATCH_STATUS, 'C155 file SHA1 mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }

        $c155 = $load['payload'];
        if (($c155['status'] ?? null) !== self::EXPECTED_C155_STATUS || ($c155['reason_code'] ?? null) !== self::EXPECTED_C155_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C155_STATUS_MISMATCH_STATUS, 'C155 status/reason is not operator GO/NO-GO ready.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (($c155['phase_label'] ?? null) !== self::EXPECTED_C155_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C155_PHASE_LABEL_MISMATCH_STATUS, 'C155 phase label mismatch.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->c155NextRecommendationMatches($c155)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C155_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C155 next recommendation is not C156.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->c155ResultReviewComplete($c155)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::C155_RESULT_REVIEW_INCOMPLETE_STATUS, 'C155 controlled output-generation result review evidence is incomplete.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->publicationAndPlanGuardClean($c155)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C155 has already published, unlocked publication, or mutated PLAN/CONFIRM.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! $this->candidateScopeMatches($c155)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C155 candidate scope does not match locked operator GO/NO-GO scope.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::APPROVAL_MISSING_STATUS, 'C156 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decision === null) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, null, $decisionReason), self::DECISION_INVALID_STATUS, 'C156 requires --operator-decision=GO, NO_GO, or HOLD.', $outputPath, $overwrite, null, $decisionReason);
        }
        if (! (bool) ($options['operator_decision_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_NOT_CONFIRMED_STATUS, 'C156 requires --operator-decision-confirmed.', $outputPath, $overwrite, $decision, $decisionReason);
        }
        if ($decisionReason === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, $decision, $decisionReason), self::DECISION_REASON_MISSING_STATUS, 'C156 requires a non-empty --decision-reason so the decision is auditable.', $outputPath, $overwrite, $decision, $decisionReason);
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
            'phase_checkpoint' => 'PR-44',
            'internal_checkpoint' => 'C156',
            'status' => 'C156_NOT_RUN',
            'reason_code' => 'C156_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass' => false,
            'production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass' => false,
            'operator_decision_recorded' => false,
            'operator_decision' => 'UNSET',
            'operator_go_decision' => 'UNSET',
            'operator_no_go_decision' => false,
            'operator_hold_decision' => false,
            'operator_decision_confirmed' => false,
            'operator_decision_reason' => '',
            'ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review' => false,
            'production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next' => false,
            'controlled_output_generation_operator_go_no_go_manifest_created' => false,
            'controlled_output_generation_stopped_no_go' => false,
            'controlled_output_generation_deferred_hold' => false,
            'weekly_swing_watchlist_controlled_output_generation_executed' => false,
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
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
            'c155_lock_valid' => false,
            'c155_controlled_output_generation_result_review_valid' => false,
            'c155_convert_from_json_pass' => false,
            'controlled_output_lock_valid' => false,
            'controlled_output_integrity_valid' => false,
            'primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => false,
            'backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => false,
            'comparator_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c156_operator_go_no_go_review_only' => true,
            'c156_not_publication' => true,
            'c156_not_unrestricted_publication' => true,
            'c156_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C156_NOT_RUN',
            'next_step_recommendation' => null,
        ];
    }

    private function decisionTopLevelState(string $decision): array
    {
        $go = $decision === 'GO';

        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass' => $go,
            'production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass' => $go,
            'operator_decision_recorded' => true,
            'operator_decision' => $decision,
            'operator_go_decision' => $decision,
            'operator_no_go_decision' => $decision === 'NO_GO',
            'operator_hold_decision' => $decision === 'HOLD',
            'operator_decision_confirmed' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review' => $go,
            'production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next' => $go,
            'controlled_output_generation_operator_go_no_go_manifest_created' => true,
            'controlled_output_generation_stopped_no_go' => $decision === 'NO_GO',
            'controlled_output_generation_deferred_hold' => $decision === 'HOLD',
            'weekly_swing_watchlist_controlled_output_generation_executed' => true,
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => true,
            'weekly_swing_watchlist_official_output_generated' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => true,
            'weekly_swing_watchlist_publication_allowed' => false,
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
            'c155_lock_valid' => true,
            'c155_controlled_output_generation_result_review_valid' => true,
            'c155_convert_from_json_pass' => true,
            'controlled_output_lock_valid' => true,
            'controlled_output_integrity_valid' => true,
            'primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'c156_operator_go_no_go_review_only' => true,
            'c156_not_publication' => true,
            'c156_not_unrestricted_publication' => true,
            'c156_not_plan_confirm_mutation' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $valid, ?string $decision, string $decisionReason): array
    {
        $c155 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $decisionForSummary = $decision ?: 'UNSET';

        $artifact['c155_lock_validation_summary'] = $this->c155LockValidationSummary($load, $c155);
        $artifact['c155_controlled_output_generation_result_review_carry_forward_summary'] = $this->c155CarryForwardSummary($c155, $valid);
        $artifact['controlled_output_publication_guard_summary'] = $this->controlledOutputPublicationGuardSummary($c155);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c155, $decisionForSummary, $valid);
        $artifact['operator_decision_validation_summary'] = $this->operatorDecisionValidationSummary($options, $decisionForSummary, $decisionReason, $valid);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c156_operator_go_no_go_decision'] = $this->operatorGoNoGoDecision($decisionForSummary, $decisionReason, $valid);
        $artifact['next_concrete_controlled_output_step_decision'] = $this->nextConcreteControlledOutputStepDecision($decisionForSummary, $valid);
        $artifact['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest'] = $this->operatorGoNoGoManifest($load, $c155, $decisionForSummary, $decisionReason, $valid);
        $artifact['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_checklist'] = $this->operatorGoNoGoChecklist($decisionForSummary);
        $artifact['c156_candidate_controlled_output_operator_go_no_go_scorecard'] = $this->candidateScorecard($decisionForSummary, $valid);
        $artifact['controlled_output_generation_operator_go_no_go_context_summary'] = $this->operatorGoNoGoContextSummary($decisionForSummary, $valid);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($c155);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($valid ? [] : [(string) ($artifact['status'] ?? 'C156_PENDING')]);
        $artifact['progress_summary'] = $this->progressSummary($decisionForSummary, $valid);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($decisionForSummary, $valid);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['operator_decision_confirmed'] = (bool) ($options['operator_decision_confirmed'] ?? false);
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($valid && in_array($decisionForSummary, ['GO', 'NO_GO', 'HOLD'], true)) {
            $artifact = array_merge($artifact, $this->decisionTopLevelState($decisionForSummary));
        }

        return $artifact;
    }

    private function c155ResultReviewComplete(array $c155): bool
    {
        foreach (self::REQUIRED_C155_TRUE_FIELDS as $field) {
            if (! (bool) ($c155[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C155_FALSE_FIELDS as $field) {
            if ((bool) ($c155[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function publicationAndPlanGuardClean(array $c155): bool
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
            if ((bool) ($c155[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c155NextRecommendationMatches(array $c155): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c155, $path);
            if ($value !== null && $value !== self::EXPECTED_C155_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c155['next_step_recommendation'] ?? null) === self::EXPECTED_C155_NEXT_RECOMMENDATION;
    }

    private function candidateScopeMatches(array $source): bool
    {
        if (($source['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return false;
        }
        if (($source['backup_candidate_code'] ?? null) !== self::BACKUP_CANDIDATE) {
            return false;
        }
        if (($source['comparator_candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }
        foreach ([
            'a01_promoted',
            'candidate_promotion_executed',
            'candidate_rerank_executed',
            'strategy_retune_executed',
            'scoring_mutation_executed',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'weekly_swing_live_recommendation_selection_executed',
        ] as $field) {
            if ((bool) ($source[$field] ?? false)) {
                return false;
            }
        }

        return (bool) ($source['a01_remains_comparator_only'] ?? false);
    }

    private function c155LockValidationSummary(array $load, array $c155): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C155',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C155_STATUS,
            'actual_status' => $c155['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C155_PHASE_LABEL,
            'actual_phase_label' => $c155['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C155_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c155NextRecommendationMatches($c155),
            'c155_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c155CarryForwardSummary(array $c155, bool $valid): array
    {
        return [
            'validation_completed' => true,
            'c155_result_review_valid' => $this->c155ResultReviewComplete($c155),
            'controlled_output_generation_result_reviewed' => (bool) ($c155['weekly_swing_watchlist_controlled_output_generation_result_reviewed'] ?? false),
            'controlled_output_lock_valid' => (bool) ($c155['controlled_output_lock_valid'] ?? false),
            'controlled_output_integrity_valid' => (bool) ($c155['controlled_output_integrity_valid'] ?? false),
            'controlled_output_record_count' => (int) ($c155['controlled_output_record_count'] ?? 0),
            'ready_for_operator_go_no_go_review' => (bool) ($c155['ready_for_weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_review'] ?? false),
            'c156_operator_go_no_go_review_can_start' => $valid,
        ];
    }

    private function controlledOutputPublicationGuardSummary(array $c155): array
    {
        return [
            'guard_reviewed' => true,
            'official_output_generated_for_controlled_review' => (bool) ($c155['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => (bool) ($c155['weekly_swing_watchlist_official_output_published'] ?? false),
            'publication_allowed' => (bool) ($c155['weekly_swing_watchlist_publication_allowed'] ?? false),
            'unrestricted_publication_allowed' => (bool) ($c155['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? false),
            'plan_confirm_mutation_allowed' => (bool) ($c155['plan_confirm_mutation_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($c155['plan_confirm_mutated'] ?? false),
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c155),
        ];
    }

    private function candidateScopeFreezeSummary(array $source, string $decision, bool $valid): array
    {
        $go = $valid && $decision === 'GO';

        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($source),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => $go,
            'backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => $go,
            'comparator_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
            'scoring_mutation_executed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
        ];
    }

    private function operatorDecisionValidationSummary(array $options, string $decision, string $decisionReason, bool $valid): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_decision_required' => true,
            'allowed_operator_decisions' => ['GO', 'NO_GO', 'HOLD'],
            'operator_decision' => $decision,
            'operator_decision_valid' => in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
            'operator_decision_confirmation_required' => true,
            'operator_decision_confirmed' => (bool) ($options['operator_decision_confirmed'] ?? false),
            'decision_reason_required' => true,
            'decision_reason' => $decisionReason,
            'decision_reason_present' => $decisionReason !== '',
            'operator_decision_validation_pass' => $valid && in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
        ];
    }

    private function operatorGoNoGoDecision(string $decision, string $decisionReason, bool $valid): array
    {
        $go = $valid && $decision === 'GO';

        return [
            'review_valid' => $valid,
            'operator_decision_recorded' => $valid && in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
            'operator_decision' => $decision,
            'operator_decision_reason' => $decisionReason,
            'operator_decision_confirmed' => $valid && in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
            'operator_go_no_go_review_pass' => $go,
            'ready_for_go_decision_finalization_review' => $go,
            'go_decision_finalization_review_allowed_next' => $go,
            'controlled_output_generation_stopped_no_go' => $valid && $decision === 'NO_GO',
            'controlled_output_generation_deferred_hold' => $valid && $decision === 'HOLD',
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'next_recommendation' => $valid ? $this->nextRecommendationForDecision($decision) : 'C156_TARGETED_C155_RESULT_REVIEW_REPAIR',
            'decision_reason' => $this->decisionExplanation($decision, $valid),
        ];
    }

    private function nextConcreteControlledOutputStepDecision(string $decision, bool $valid): array
    {
        return [
            'review_valid' => $valid,
            'operator_decision' => $decision,
            'next_recommendation' => $valid ? $this->nextRecommendationForDecision($decision) : 'C156_TARGETED_C155_RESULT_REVIEW_REPAIR',
            'next_scope' => $valid ? $this->nextScopeForDecision($decision) : 'targeted C155 lock, approval, operator decision, or guard repair only',
            'next_is_concrete' => $valid && $decision === 'GO',
            'next_requires_locked_c156_artifact' => $valid && $decision === 'GO',
        ];
    }

    private function operatorGoNoGoManifest(array $load, array $c155, string $decision, string $decisionReason, bool $valid): array
    {
        $go = $valid && $decision === 'GO';

        return [
            'manifest_created' => $valid,
            'manifest_context' => 'controlled_output_generation_operator_go_no_go_review',
            'source_artifact' => self::EXPECTED_C155_NEXT_RECOMMENDATION === self::RUN_CODE ? 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW' : 'C155',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['expected_hash'],
            'source_file_sha1' => $load['expected_file_sha1'],
            'controlled_output_hash' => $c155['controlled_output_hash'] ?? null,
            'controlled_output_file_sha1' => $c155['controlled_output_file_sha1'] ?? null,
            'controlled_output_record_count' => $c155['controlled_output_record_count'] ?? 0,
            'operator_decision' => $decision,
            'operator_decision_reason' => $decisionReason,
            'operator_decision_confirmed' => $valid,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'operator_go_no_go_review_pass' => $go,
            'ready_for_go_decision_finalization_review' => $go,
            'go_decision_finalization_review_allowed_next' => $go,
            'operator_go_no_go_artifact_only' => true,
            'official_output_generated_for_controlled_review' => true,
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'operator_go_no_go_used_for_plan_confirm_mutation' => false,
            'operator_go_no_go_used_for_publication' => false,
        ];
    }

    private function operatorGoNoGoChecklist(string $decision): array
    {
        return [
            'operator_go_no_go_reviewed' => true,
            'operator_decision_required' => true,
            'operator_decision_allowed_values' => ['GO', 'NO_GO', 'HOLD'],
            'operator_decision_recorded' => in_array($decision, ['GO', 'NO_GO', 'HOLD'], true),
            'c155_source_lock_reviewed' => true,
            'controlled_output_artifact_lock_carried_from_c155' => true,
            'publication_not_executed_in_c156' => true,
            'unrestricted_publication_not_enabled_in_c156' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_decision_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'operator_go_no_go_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_generated_in_c156' => false,
            'weekly_swing_stock_recommendation_published_in_c156' => false,
        ];
    }

    private function candidateScorecard(string $decision, bool $valid): array
    {
        $go = $valid && $decision === 'GO';
        $base = [
            'controlled_output_generation_operator_go_no_go_review_valid' => $valid,
            'operator_decision' => $decision,
            'ready_for_go_decision_finalization_review' => $go,
            'published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c156_role' => 'primary_controlled_output_generation_go_decision_finalization_candidate',
                'primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => $go,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c156_role' => 'backup_controlled_output_generation_go_decision_finalization_candidate',
                'backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review' => $go,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c156_role' => 'comparator_only_candidate',
                'controlled_output_generation_operator_go_no_go_review_valid' => false,
                'ready_for_go_decision_finalization_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function operatorGoNoGoContextSummary(string $decision, bool $valid): array
    {
        return [
            'controlled_output_generation_operator_go_no_go_context_created' => true,
            'controlled_output_generation_operator_go_no_go_context_valid' => $valid,
            'operator_decision' => $decision,
            'context_is_artifact_only' => true,
            'context_persisted_to_plan_confirm' => false,
            'context_used_for_publication' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'official_output_published' => false,
            'plan_confirm_mutated' => false,
            'runtime_bridge_active' => true,
            'weekly_swing_watchlist_runtime_active' => true,
            'weekly_swing_watchlist_live_output_enabled' => true,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $c155): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c155),
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

    private function progressSummary(string $decision, bool $valid): array
    {
        return [
            'progress_marker' => 'PR-44_C156_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW',
            'c155_controlled_output_generation_result_review_carried_forward' => true,
            'operator_decision' => $decision,
            'ready_for_go_decision_finalization_review' => $valid && $decision === 'GO',
            'controlled_output_generation_stopped_no_go' => $valid && $decision === 'NO_GO',
            'controlled_output_generation_deferred_hold' => $valid && $decision === 'HOLD',
            'still_no_publication' => true,
            'still_no_unrestricted_publication' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(string $decision, bool $valid): array
    {
        return [
            'planned_next_review' => $valid ? $this->nextRecommendationForDecision($decision) : 'C156_TARGETED_C155_RESULT_REVIEW_REPAIR',
            'planned_next_scope' => $valid ? $this->nextScopeForDecision($decision) : 'targeted repair before controlled output-generation operator GO/NO-GO decision can be recorded',
            'planned_next_required_inputs' => $valid && $decision === 'GO' ? [
                'locked C156 artifact hash',
                'locked C156 file SHA1',
                'operator GO decision reference',
                'publication still disabled',
                'PLAN/CONFIRM unchanged',
                'go decision finalization review boundary',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c155_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c155_artifact_not_modified' => true,
            'c156_is_operator_go_no_go_review_not_publication' => true,
            'c156_is_not_plan_confirm_mutation' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C156 validates C155 artifact_hash and file SHA1 locks before operator GO/NO-GO is recorded.',
            'C156 requires --operator-decision=GO, NO_GO, or HOLD plus explicit confirmation and reason.',
            'GO opens only the C157 go decision finalization review target; C156 still does not publish output.',
            'NO_GO stops controlled output generation progression without mutating the controlled output artifact.',
            'HOLD defers controlled output generation progression while preserving the locked C155 evidence.',
            'C156 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C156 does not publish recommendations, allow unrestricted publication, or mutate PLAN/CONFIRM.',
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
            return 'C156 records operator GO for the controlled output-generation result and points to C157 go decision finalization review. C156 does not publish output, allow unrestricted publication, or mutate PLAN/CONFIRM.';
        }
        if ($decision === 'NO_GO') {
            return 'C156 records operator NO_GO and stops controlled output-generation progression. Evidence remains locked and no output publication, unrestricted publication, or PLAN/CONFIRM mutation is executed.';
        }

        return 'C156 records operator HOLD and defers controlled output-generation progression. Evidence remains locked and no output publication, unrestricted publication, or PLAN/CONFIRM mutation is executed.';
    }

    private function diagnosticConclusionForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'C156_OPERATOR_GO_RECORDED_READY_FOR_C157_GO_DECISION_FINALIZATION_REVIEW_NOT_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        }
        if ($decision === 'NO_GO') {
            return 'C156_OPERATOR_NO_GO_RECORDED_CONTROLLED_OUTPUT_GENERATION_STOPPED_NOT_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        }

        return 'C156_OPERATOR_HOLD_RECORDED_CONTROLLED_OUTPUT_GENERATION_DEFERRED_NOT_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
    }

    private function nextRecommendationForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return self::C157_GO_RECOMMENDATION;
        }
        if ($decision === 'NO_GO') {
            return 'C156_NO_GO_CLOSE_CONTROLLED_OUTPUT_GENERATION';
        }

        return 'C156_HOLD_KEEP_C155_LOCKED_UNTIL_OPERATOR_WINDOW';
    }

    private function nextScopeForDecision(string $decision): string
    {
        if ($decision === 'GO') {
            return 'controlled output-generation go decision finalization review only; no publication, unrestricted publication, or PLAN/CONFIRM mutation from C156';
        }
        if ($decision === 'NO_GO') {
            return 'controlled output-generation progression stopped by operator decision; no publication';
        }

        return 'controlled output-generation progression held pending a later operator window; no publication';
    }

    private function decisionExplanation(string $decision, bool $valid): string
    {
        if (! $valid) {
            return 'C156 operator decision could not be recorded because a validation gate failed.';
        }
        if ($decision === 'GO') {
            return 'Operator accepts the locked C155 controlled output result for go decision finalization review.';
        }
        if ($decision === 'NO_GO') {
            return 'Operator rejects progression from the controlled output result and stops the current controlled output-generation path.';
        }

        return 'Operator defers progression from the controlled output result while preserving the locked C155 evidence.';
    }

    private function normalizeDecision(string $decision): ?string
    {
        $decision = strtoupper(trim(str_replace('-', '_', $decision)));
        if (in_array($decision, ['GO', 'NO_GO', 'HOLD'], true)) {
            return $decision;
        }

        return null;
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c155' => [
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
            'expected_c155_hash' => $load['expected_hash'],
            'actual_c155_hash' => $load['actual_hash'],
            'c155_hash_match' => $load['hash_match'],
            'expected_c155_file_sha1' => $load['expected_file_sha1'],
            'actual_c155_file_sha1' => $load['actual_file_sha1'],
            'c155_file_sha1_match' => $load['file_sha1_match'],
            'c155_convert_from_json_pass' => $load['convert_from_json_pass'],
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

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['planned_next_summary'] = $this->plannedNextSummary($decision ?: 'UNSET', false);
        $artifact['operator_decision_reason'] = $decisionReason;
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?string $decision, string $decisionReason): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['planned_next_summary'] = $this->plannedNextSummary($decision ?: 'UNSET', false);
        $artifact['operator_decision_reason'] = $decisionReason;
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
