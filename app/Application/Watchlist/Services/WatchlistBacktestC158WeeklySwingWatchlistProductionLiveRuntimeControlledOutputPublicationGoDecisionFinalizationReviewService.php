<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-50 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';

    public const DEFAULT_C158_OPERATOR_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C158_OPERATOR_HASH = '14fc284651d7d5f07d1941300b382c2d7071fea3';
    public const DEFAULT_EXPECTED_C158_OPERATOR_FILE_SHA1 = '66EDD8CC51F5C5F9C29889354A94A01FC0501B21';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C158_OPERATOR_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C158_OPERATOR_PHASE_LABEL = 'PR-49 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW';
    private const EXPECTED_C158_OPERATOR_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C159_RECOMMENDATION = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW';

    private const PASS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_FINALIZATION_NOT_CONFIRMED_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING';
    private const CONTROLLED_PUBLICATION_FINALIZATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FINALIZATION_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C158_OPERATOR_LOCK_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH';
    private const C158_OPERATOR_FILE_SHA1_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_NO_GO_FILE_SHA1_LOCK_MISMATCH';
    private const C158_OPERATOR_CONVERT_FROM_JSON_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_NO_GO_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C158_OPERATOR_STATUS_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_NO_GO_STATUS_MISMATCH';
    private const C158_OPERATOR_PHASE_LABEL_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_NO_GO_PHASE_LABEL_MISMATCH';
    private const C158_OPERATOR_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_NO_GO_NEXT_RECOMMENDATION_MISMATCH';
    private const C158_OPERATOR_GO_INVALID_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C158_OPERATOR_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_operator_go_no_go_review_pass',
        'production_live_runtime_controlled_output_publication_operator_go_no_go_review_pass',
        'operator_decision_recorded',
        'operator_decision_confirmed',
        'ready_for_weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_review',
        'production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed_next',
        'controlled_output_publication_operator_go_no_go_manifest_created',
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
        'c158_result_review_lock_valid',
        'c158_controlled_output_publication_result_review_valid',
        'c158_result_review_convert_from_json_pass',
        'controlled_publication_lock_valid',
        'controlled_publication_integrity_valid',
        'primary_candidate_ready_for_controlled_output_publication_go_decision_finalization_review',
        'backup_candidate_ready_for_controlled_output_publication_go_decision_finalization_review',
        'a01_remains_comparator_only',
        'c158_controlled_output_publication_operator_go_no_go_review_only',
        'c158_controlled_publication_only',
        'c158_not_free_publication',
        'c158_not_unrestricted_publication',
        'c158_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C158_OPERATOR_FALSE_FIELDS = [
        'operator_no_go_decision',
        'operator_hold_decision',
        'controlled_output_publication_stopped_no_go',
        'controlled_output_publication_deferred_hold',
        'comparator_candidate_ready_for_controlled_output_publication_go_decision_finalization_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c158-*no-*-test.json',
        'storage/app/watchlist/backtest/c158-*missing-*-test.json',
        'storage/app/watchlist/backtest/c158-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c158-*negative-*-test.json',
        'storage/app/watchlist/backtest/c158-*invalid-*-test.json',
    ];

    public function execute(
        string $c158OperatorArtifact = self::DEFAULT_C158_OPERATOR_ARTIFACT,
        string $expectedC158OperatorHash = self::DEFAULT_EXPECTED_C158_OPERATOR_HASH,
        string $expectedC158OperatorFileSha1 = self::DEFAULT_EXPECTED_C158_OPERATOR_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c158OperatorArtifact, $expectedC158OperatorHash, $expectedC158OperatorFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C158_OPERATOR_LOCK_MISMATCH_STATUS, 'C158 operator GO/NO-GO artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c158_operator_go_no_go_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C158_OPERATOR_CONVERT_FROM_JSON_STATUS, 'C158 operator GO/NO-GO artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C158_OPERATOR_LOCK_MISMATCH_STATUS, 'C158 operator GO/NO-GO artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C158_OPERATOR_FILE_SHA1_MISMATCH_STATUS, 'C158 operator GO/NO-GO file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $operator = $load['payload'];
        if (($operator['status'] ?? null) !== self::EXPECTED_C158_OPERATOR_STATUS || ($operator['reason_code'] ?? null) !== self::EXPECTED_C158_OPERATOR_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C158_OPERATOR_STATUS_MISMATCH_STATUS, 'C158 operator GO/NO-GO status/reason is not GO finalization ready.', $outputPath, $overwrite);
        }
        if (($operator['phase_label'] ?? null) !== self::EXPECTED_C158_OPERATOR_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C158_OPERATOR_PHASE_LABEL_MISMATCH_STATUS, 'C158 operator GO/NO-GO phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->operatorNextRecommendationMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C158_OPERATOR_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C158 operator GO/NO-GO next recommendation is not C158 GO decision finalization.', $outputPath, $overwrite);
        }
        if (! $this->operatorGoComplete($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C158_OPERATOR_GO_INVALID_STATUS, 'C158 operator GO evidence is incomplete or not valid for finalization.', $outputPath, $overwrite);
        }
        if (! $this->freePublicationAndPlanGuardClean($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C158 operator evidence has free publication, unrestricted publication, or PLAN/CONFIRM mutation already enabled.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($operator)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C158 operator candidate scope does not match locked finalization scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C158 GO decision finalization requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['go_decision_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::GO_FINALIZATION_NOT_CONFIRMED_STATUS, 'C158 requires --go-decision-finalization-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_publication_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CONTROLLED_PUBLICATION_FINALIZATION_MISSING_STATUS, 'C158 requires --controlled-publication-finalization-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C158 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C158 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C158 finalizes the operator GO decision for controlled output publication. The controlled publication path is finalized for observation; free publication, unrestricted publication, and PLAN/CONFIRM mutation remain locked.';
        $artifact['diagnostic_conclusion'] = 'C158_CONTROLLED_OUTPUT_PUBLICATION_GO_FINALIZED_READY_FOR_C159_POST_PUBLICATION_OBSERVATION_NOT_FREE_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C159_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-50',
            'internal_checkpoint' => 'C158',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'GO_DECISION_FINALIZATION_REVIEW',
            'status' => 'C158_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'reason_code' => 'C158_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass' => false,
            'production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass' => false,
            'operator_decision' => 'NO_GO',
            'operator_go_decision' => 'NO_GO',
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'go_decision_finalization_confirmed' => false,
            'controlled_publication_finalization_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_review' => false,
            'production_live_runtime_controlled_output_publication_post_publication_observation_review_allowed_next' => false,
            'controlled_output_publication_go_decision_finalization_manifest_created' => false,
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
            'c158_operator_go_no_go_lock_valid' => false,
            'c158_operator_go_no_go_review_valid' => false,
            'c158_operator_go_no_go_convert_from_json_pass' => false,
            'c158_result_review_lock_valid' => false,
            'c158_controlled_output_publication_result_review_valid' => false,
            'controlled_publication_lock_valid' => false,
            'controlled_publication_integrity_valid' => false,
            'primary_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => false,
            'backup_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => false,
            'comparator_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c158_controlled_output_publication_go_decision_finalization_review_only' => true,
            'c158_controlled_publication_only' => true,
            'c158_not_free_publication' => true,
            'c158_not_unrestricted_publication' => true,
            'c158_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C158_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'next_step_recommendation' => 'C158_GO_DECISION_FINALIZATION_REVIEW_NOT_RUN',
            'message' => '',
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $operator = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($operator, $pass, $options));
        $artifact['c158_operator_go_no_go_lock_validation_summary'] = $this->operatorLockValidationSummary($load, $operator);
        $artifact['c158_operator_go_no_go_carry_forward_summary'] = $this->operatorCarryForwardSummary($operator);
        $artifact['controlled_publication_finalization_guard_summary'] = $this->controlledPublicationFinalizationGuardSummary($operator, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($operator, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c158_go_decision_finalization_decision'] = $this->goDecisionFinalizationDecision($pass, $options);
        $artifact['next_controlled_output_publication_post_publication_observation_decision'] = $this->nextPostPublicationObservationDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_manifest'] = $this->goDecisionFinalizationManifest($operator, $pass, $options);
        $artifact['weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_checklist'] = $this->goDecisionFinalizationChecklist($pass, $options);
        $artifact['c158_candidate_controlled_publication_go_decision_finalization_scorecard'] = $this->candidateScorecard($pass);
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

    private function topLevelState(array $operator, bool $pass, array $options): array
    {
        return [
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
            'c158_operator_go_no_go_lock_valid' => (bool) (($operator['artifact_hash'] ?? null) === self::DEFAULT_EXPECTED_C158_OPERATOR_HASH),
            'c158_operator_go_no_go_review_valid' => $this->operatorGoComplete($operator),
            'c158_result_review_lock_valid' => (bool) ($operator['c158_result_review_lock_valid'] ?? false),
            'c158_controlled_output_publication_result_review_valid' => (bool) ($operator['c158_controlled_output_publication_result_review_valid'] ?? false),
            'controlled_publication_lock_valid' => (bool) ($operator['controlled_publication_lock_valid'] ?? false),
            'controlled_publication_integrity_valid' => (bool) ($operator['controlled_publication_integrity_valid'] ?? false),
            'operator_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision_confirmed' => $pass,
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'controlled_publication_finalization_confirmed' => (bool) ($options['controlled_publication_finalization_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'primary_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => $pass,
            'backup_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => $pass,
            'comparator_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass' => true,
            'production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass' => true,
            'operator_decision' => 'GO',
            'operator_go_decision' => 'GO',
            'operator_go_decision_confirmed' => true,
            'go_decision_finalized' => true,
            'go_decision_finalization_confirmed' => true,
            'controlled_publication_finalization_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_review' => true,
            'production_live_runtime_controlled_output_publication_post_publication_observation_review_allowed_next' => true,
            'controlled_output_publication_go_decision_finalization_manifest_created' => true,
            'c158_operator_go_no_go_lock_valid' => true,
            'c158_operator_go_no_go_review_valid' => true,
            'c158_operator_go_no_go_convert_from_json_pass' => true,
            'primary_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => true,
            'backup_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => true,
            'comparator_candidate_ready_for_controlled_output_publication_post_publication_observation_review' => false,
        ];
    }

    private function operatorNextRecommendationMatches(array $operator): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_concrete_controlled_output_publication_step_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($operator, $path) !== self::EXPECTED_C158_OPERATOR_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function operatorGoComplete(array $operator): bool
    {
        foreach (self::REQUIRED_C158_OPERATOR_TRUE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C158_OPERATOR_FALSE_FIELDS as $field) {
            if (($operator[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($operator['operator_decision'] ?? null) === 'GO'
            && ($operator['operator_go_decision'] ?? null) === 'GO'
            && trim((string) ($operator['operator_decision_reason'] ?? '')) !== ''
            && $this->valueAt($operator, ['c158_operator_go_no_go_decision', 'operator_decision']) === 'GO'
            && $this->valueAt($operator, ['c158_operator_go_no_go_decision', 'ready_for_go_decision_finalization_review']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_manifest', 'operator_decision']) === 'GO'
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_manifest', 'operator_go_no_go_used_for_publication']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_manifest', 'operator_go_no_go_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_checklist', 'artifact_only']) === true
            && $this->valueAt($operator, ['weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_checklist', 'weekly_swing_stock_recommendation_free_published_in_c158_operator_review']) === false;
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
            && ($operator['primary_candidate_ready_for_controlled_output_publication_go_decision_finalization_review'] ?? null) === true
            && ($operator['backup_candidate_ready_for_controlled_output_publication_go_decision_finalization_review'] ?? null) === true
            && ($operator['comparator_candidate_ready_for_controlled_output_publication_go_decision_finalization_review'] ?? null) === false
            && ($operator['a01_remains_comparator_only'] ?? null) === true
            && ($operator['a01_promoted'] ?? false) === false
            && ($operator['candidate_promotion_executed'] ?? false) === false
            && ($operator['candidate_rerank_executed'] ?? false) === false;
    }

    private function operatorLockValidationSummary(array $load, array $operator): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C158_OPERATOR_GO_NO_GO',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C158_OPERATOR_STATUS,
            'actual_status' => $operator['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C158_OPERATOR_PHASE_LABEL,
            'actual_phase_label' => $operator['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C158_OPERATOR_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->operatorNextRecommendationMatches($operator),
            'c158_operator_go_no_go_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function operatorCarryForwardSummary(array $operator): array
    {
        return [
            'validation_completed' => true,
            'c158_operator_go_no_go_review_valid' => $this->operatorGoComplete($operator),
            'topic_code' => $operator['topic_code'] ?? null,
            'topic_stage' => $operator['topic_stage'] ?? null,
            'operator_decision' => $operator['operator_decision'] ?? null,
            'operator_decision_reason' => $operator['operator_decision_reason'] ?? null,
            'controlled_publication_result_reviewed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_result_reviewed'] ?? false),
            'controlled_publication_executed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'controlled_publication_published' => (bool) ($operator['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'go_decision_finalization_allowed' => (bool) ($operator['production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed_next'] ?? false),
        ];
    }

    private function controlledPublicationFinalizationGuardSummary(array $operator, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'controlled_publication_finalization_valid' => $pass,
            'controlled_publication_already_executed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'controlled_publication_artifact_created' => (bool) ($operator['weekly_swing_watchlist_controlled_publication_artifact_created'] ?? false),
            'controlled_publication_allowed' => (bool) ($operator['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
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
            'primary_candidate_ready_for_post_publication_observation_review' => $pass,
            'backup_candidate_ready_for_post_publication_observation_review' => $pass,
            'comparator_candidate_ready_for_post_publication_observation_review' => false,
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
            'controlled_publication_finalization_confirmation_required' => true,
            'controlled_publication_finalization_confirmed' => (bool) ($options['controlled_publication_finalization_confirmed'] ?? false),
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
            'controlled_publication_finalization_confirmed' => (bool) ($options['controlled_publication_finalization_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'decision_scope' => $pass ? 'controlled publication GO finalized for post-publication observation only' : 'targeted repair required before C158 GO finalization can be recorded',
        ];
    }

    private function nextPostPublicationObservationDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C159_RECOMMENDATION : 'C158_TARGETED_OPERATOR_GO_NO_GO_REPAIR',
            'next_scope' => $pass ? 'C159 post-publication observation review for controlled publication only; free publication and PLAN/CONFIRM remain locked' : 'targeted repair before controlled output publication GO decision finalization can be recorded',
            'next_is_concrete' => $pass,
            'next_requires_locked_c158_finalization_artifact' => $pass,
            'topic_number_advances_after_c158_finalization' => $pass,
        ];
    }

    private function goDecisionFinalizationManifest(array $operator, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'controlled_output_publication_go_decision_finalization_review',
            'source_artifact' => 'C158_OPERATOR_GO_NO_GO_REVIEW',
            'source_artifact_path' => self::DEFAULT_C158_OPERATOR_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C158_OPERATOR_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C158_OPERATOR_FILE_SHA1,
            'source_operator_decision' => (string) ($operator['operator_decision'] ?? 'UNSET'),
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'controlled_publication_finalization_confirmed' => (bool) ($options['controlled_publication_finalization_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_output_publication_go_decision_finalization_review_pass' => $pass,
            'ready_for_post_publication_observation_review' => $pass,
            'go_decision_finalization_artifact_only' => true,
            'controlled_publication_already_executed' => (bool) ($operator['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
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
            'c158_operator_go_no_go_source_lock_reviewed' => true,
            'operator_go_decision_carried_forward' => true,
            'go_decision_finalization_required' => true,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'controlled_publication_finalization_confirmed' => (bool) ($options['controlled_publication_finalization_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_go_finalization_gate_required' => true,
            'negative_controlled_publication_finalization_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'negative_plan_confirm_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'go_decision_finalization_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c158_finalization' => false,
            'ready_for_next_observation' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'controlled_output_publication_go_decision_finalization_review_valid' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'ready_for_post_publication_observation_review' => $pass,
            'free_published' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c158_role' => 'primary_candidate_ready_for_post_publication_observation',
                'primary_candidate_ready_for_post_publication_observation_review' => $pass,
                'controlled_published' => true,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c158_role' => 'backup_candidate_ready_for_post_publication_observation',
                'backup_candidate_ready_for_post_publication_observation_review' => $pass,
                'controlled_published' => true,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c158_role' => 'comparator_only_candidate',
                'ready_for_post_publication_observation_review' => false,
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
            'c158_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c158_operator_go_no_go_artifact_not_modified' => true,
            'c158_go_decision_finalization_review_is_artifact_only_not_free_publication' => true,
            'c158_go_decision_finalization_review_closes_c158_topic' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-50_C158_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'GO_DECISION_FINALIZATION_REVIEW',
            'c158_operator_go_no_go_review_carried_forward' => true,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'ready_for_post_publication_observation_review' => $pass,
            'topic_complete_after_finalization' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C159_RECOMMENDATION : 'C158_TARGETED_OPERATOR_GO_NO_GO_REPAIR',
            'planned_next_scope' => $pass ? 'C159 post-publication observation review of controlled publication only; still no free publication or PLAN/CONFIRM mutation from C158 finalization' : 'targeted repair before C158 GO decision finalization can be recorded',
            'topic_number_advances_after_c158_finalization' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C158 GO decision finalization artifact hash',
                'locked C158 GO decision finalization file SHA1',
                'finalized C158 controlled publication GO decision',
                'controlled publication evidence remains intact',
                'free publication still disabled',
                'PLAN/CONFIRM unchanged',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C158 finalization validates C158 operator GO/NO-GO artifact_hash and file SHA1 locks before GO finalization is recorded.',
            'C158 finalization validates operator GO, confirmation, decision reason, candidate scope, and next recommendation to C158 finalization.',
            'C158 finalization requires operator approval plus GO finalization, controlled publication finalization, free publication lock, and PLAN/CONFIRM unchanged confirmations.',
            'C158 finalization keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C158 finalization closes the controlled output publication topic and recommends C159 post-publication observation review.',
            'C158 finalization does not free-publish recommendations, allow unrestricted publication, or mutate PLAN/CONFIRM.',
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
            'c158_operator_go_no_go' => [
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
            'expected_c158_operator_go_no_go_hash' => $load['expected_hash'],
            'actual_c158_operator_go_no_go_hash' => $load['actual_hash'],
            'c158_operator_go_no_go_hash_match' => $load['hash_match'],
            'expected_c158_operator_go_no_go_file_sha1' => $load['expected_file_sha1'],
            'actual_c158_operator_go_no_go_file_sha1' => $load['actual_file_sha1'],
            'c158_operator_go_no_go_file_sha1_match' => $load['file_sha1_match'],
            'c158_operator_go_no_go_convert_from_json_pass' => $load['convert_from_json_pass'],
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
