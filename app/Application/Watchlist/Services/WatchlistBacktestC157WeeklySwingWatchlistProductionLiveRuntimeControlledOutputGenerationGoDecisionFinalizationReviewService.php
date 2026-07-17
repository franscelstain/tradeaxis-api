<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-45 / C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';

    public const DEFAULT_C156_ARTIFACT = 'storage/app/watchlist/backtest/c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C156_HASH = 'f36edcf84b291dd58119caf4e003c00ced404311';
    public const DEFAULT_EXPECTED_C156_FILE_SHA1 = 'A7165F0FB30111B313783A1FD3DE77992BD39E99';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C156_STATUS = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';
    private const C156_RUN_CODE = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW';
    private const EXPECTED_C156_PHASE_LABEL = 'PR-44 / C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW';
    private const EXPECTED_C156_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C158_RECOMMENDATION = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW';

    private const PASS_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_FINALIZATION_NOT_CONFIRMED_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING';
    private const NO_PUBLICATION_CONFIRMATION_MISSING_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_NO_PUBLICATION_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C156_LOCK_MISMATCH_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_ARTIFACT_LOCK_MISMATCH';
    private const C156_FILE_SHA1_MISMATCH_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_FILE_SHA1_LOCK_MISMATCH';
    private const C156_CONVERT_FROM_JSON_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C156_STATUS_MISMATCH_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_STATUS_MISMATCH';
    private const C156_PHASE_LABEL_MISMATCH_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_PHASE_LABEL_MISMATCH';
    private const C156_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_NEXT_RECOMMENDATION_MISMATCH';
    private const C156_OPERATOR_GO_INVALID_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_OPERATOR_GO_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C156_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass',
        'production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass',
        'operator_decision_recorded',
        'operator_decision_confirmed',
        'ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review',
        'production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next',
        'controlled_output_generation_operator_go_no_go_manifest_created',
        'weekly_swing_watchlist_controlled_output_generation_executed',
        'weekly_swing_watchlist_controlled_output_generation_result_reviewed',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_live_recommendation_generated',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c155_lock_valid',
        'c155_controlled_output_generation_result_review_valid',
        'c155_convert_from_json_pass',
        'controlled_output_lock_valid',
        'controlled_output_integrity_valid',
        'primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review',
        'backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review',
        'a01_remains_comparator_only',
        'c156_operator_go_no_go_review_only',
        'c156_not_publication',
        'c156_not_unrestricted_publication',
        'c156_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C156_FALSE_FIELDS = [
        'operator_no_go_decision',
        'operator_hold_decision',
        'controlled_output_generation_stopped_no_go',
        'controlled_output_generation_deferred_hold',
        'comparator_candidate_ready_for_controlled_output_generation_go_decision_finalization_review',
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
        'storage/app/watchlist/backtest/*no-*-test.json',
        'storage/app/watchlist/backtest/*missing-*-test.json',
        'storage/app/watchlist/backtest/*mismatch-*-test.json',
        'storage/app/watchlist/backtest/*negative-*-test.json',
        'storage/app/watchlist/backtest/*invalid-*-test.json',
    ];

    public function execute(
        string $c156Artifact = self::DEFAULT_C156_ARTIFACT,
        string $expectedC156Hash = self::DEFAULT_EXPECTED_C156_HASH,
        string $expectedC156FileSha1 = self::DEFAULT_EXPECTED_C156_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c156Artifact, $expectedC156Hash, $expectedC156FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C156_LOCK_MISMATCH_STATUS, 'C156 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c156_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C156_CONVERT_FROM_JSON_STATUS, 'C156 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C156_LOCK_MISMATCH_STATUS, 'C156 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C156_FILE_SHA1_MISMATCH_STATUS, 'C156 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c156 = $load['payload'];
        if (($c156['status'] ?? null) !== self::EXPECTED_C156_STATUS || ($c156['reason_code'] ?? null) !== self::EXPECTED_C156_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C156_STATUS_MISMATCH_STATUS, 'C156 status/reason is not controlled output-generation GO finalization ready.', $outputPath, $overwrite);
        }
        if (($c156['phase_label'] ?? null) !== self::EXPECTED_C156_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C156_PHASE_LABEL_MISMATCH_STATUS, 'C156 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c156NextRecommendationMatches($c156)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C156_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C156 next recommendation is not C157.', $outputPath, $overwrite);
        }
        if (! $this->c156OperatorGoComplete($c156)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C156_OPERATOR_GO_INVALID_STATUS, 'C156 operator GO evidence is incomplete or not finalized for C157.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($c156)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C156 has already published, unlocked publication, or mutated PLAN/CONFIRM.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c156)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C156 candidate scope does not match locked C157 GO finalization scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C157 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['go_decision_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::GO_FINALIZATION_NOT_CONFIRMED_STATUS, 'C157 requires --go-decision-finalization-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_publication_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::NO_PUBLICATION_CONFIRMATION_MISSING_STATUS, 'C157 requires --no-publication-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C157 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C157 finalizes the operator GO decision from locked C156 evidence for controlled output generation. It remains controlled-output artifact only: no publication, unrestricted publication, or PLAN/CONFIRM mutation is executed.';
        $artifact['diagnostic_conclusion'] = 'C157_GO_DECISION_FINALIZED_READY_FOR_C158_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_NOT_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C158_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-45',
            'internal_checkpoint' => 'C157',
            'status' => 'C157_NOT_RUN',
            'reason_code' => 'C157_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass' => false,
            'production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass' => false,
            'operator_go_decision' => 'NO_GO',
            'operator_decision' => 'NO_GO',
            'operator_go_decision_confirmed' => false,
            'go_decision_finalized' => false,
            'go_decision_finalization_confirmed' => false,
            'no_publication_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_boundary_review' => false,
            'production_live_runtime_controlled_output_publication_boundary_review_allowed_next' => false,
            'controlled_output_generation_go_decision_finalization_manifest_created' => false,
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
            'c156_lock_valid' => false,
            'c156_operator_go_no_go_review_valid' => false,
            'c156_convert_from_json_pass' => false,
            'c155_lock_valid' => false,
            'c155_controlled_output_generation_result_review_valid' => false,
            'controlled_output_lock_valid' => false,
            'controlled_output_integrity_valid' => false,
            'primary_candidate_ready_for_controlled_output_publication_boundary_review' => false,
            'backup_candidate_ready_for_controlled_output_publication_boundary_review' => false,
            'comparator_candidate_ready_for_controlled_output_publication_boundary_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c157_go_decision_finalization_review_only' => true,
            'c157_not_publication' => true,
            'c157_not_unrestricted_publication' => true,
            'c157_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C157_NOT_RUN',
            'next_step_recommendation' => 'C157_NOT_RUN',
            'message' => '',
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c156 = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($c156, $pass, $options));
        $artifact['c156_lock_validation_summary'] = $this->c156LockValidationSummary($load);
        $artifact['c156_operator_go_no_go_carry_forward_summary'] = $this->c156OperatorGoNoGoCarryForwardSummary($c156);
        $artifact['controlled_output_publication_guard_summary'] = $this->controlledOutputPublicationGuardSummary($c156);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c156, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c157_go_decision_finalization_decision'] = $this->goDecisionFinalizationDecision($c156, $pass, $options);
        $artifact['next_controlled_output_publication_boundary_decision'] = $this->nextControlledOutputPublicationBoundaryDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest'] = $this->goDecisionFinalizationManifest($c156, $pass, $options);
        $artifact['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_checklist'] = $this->goDecisionFinalizationChecklist($pass, $options);
        $artifact['c157_candidate_controlled_output_go_decision_finalization_scorecard'] = $this->candidateScorecard($pass);
        $artifact['controlled_output_generation_go_decision_finalization_context_summary'] = $this->goDecisionFinalizationContextSummary($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($c156);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function topLevelState(array $c156, bool $pass, array $options): array
    {
        return [
            'weekly_swing_watchlist_controlled_output_generation_executed' => (bool) ($c156['weekly_swing_watchlist_controlled_output_generation_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => (bool) ($c156['weekly_swing_watchlist_controlled_output_generation_result_reviewed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($c156['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => (bool) ($c156['weekly_swing_watchlist_live_recommendation_generated'] ?? false),
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($c156['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c156['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c156['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c156['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c156_lock_valid' => (bool) (($c156['artifact_hash'] ?? null) === self::DEFAULT_EXPECTED_C156_HASH),
            'c156_operator_go_no_go_review_valid' => $this->c156OperatorGoComplete($c156),
            'c155_lock_valid' => (bool) ($c156['c155_lock_valid'] ?? false),
            'c155_controlled_output_generation_result_review_valid' => (bool) ($c156['c155_controlled_output_generation_result_review_valid'] ?? false),
            'controlled_output_lock_valid' => (bool) ($c156['controlled_output_lock_valid'] ?? false),
            'controlled_output_integrity_valid' => (bool) ($c156['controlled_output_integrity_valid'] ?? false),
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision_confirmed' => $pass,
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'no_publication_confirmed' => (bool) ($options['no_publication_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'primary_candidate_ready_for_controlled_output_publication_boundary_review' => $pass,
            'backup_candidate_ready_for_controlled_output_publication_boundary_review' => $pass,
            'comparator_candidate_ready_for_controlled_output_publication_boundary_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass' => true,
            'production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass' => true,
            'operator_go_decision' => 'GO',
            'operator_decision' => 'GO',
            'operator_go_decision_confirmed' => true,
            'go_decision_finalized' => true,
            'go_decision_finalization_confirmed' => true,
            'no_publication_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_boundary_review' => true,
            'production_live_runtime_controlled_output_publication_boundary_review_allowed_next' => true,
            'controlled_output_generation_go_decision_finalization_manifest_created' => true,
            'c156_lock_valid' => true,
            'c156_operator_go_no_go_review_valid' => true,
            'c156_convert_from_json_pass' => true,
            'primary_candidate_ready_for_controlled_output_publication_boundary_review' => true,
            'backup_candidate_ready_for_controlled_output_publication_boundary_review' => true,
            'comparator_candidate_ready_for_controlled_output_publication_boundary_review' => false,
        ];
    }

    private function c156NextRecommendationMatches(array $c156): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['c156_operator_go_no_go_decision', 'next_recommendation'],
            ['next_concrete_controlled_output_step_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c156, $path) !== self::EXPECTED_C156_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function c156OperatorGoComplete(array $c156): bool
    {
        foreach (self::REQUIRED_C156_TRUE_FIELDS as $field) {
            if (($c156[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C156_FALSE_FIELDS as $field) {
            if (($c156[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($c156['operator_decision'] ?? null) !== 'GO' || ($c156['operator_go_decision'] ?? null) !== 'GO') {
            return false;
        }
        if (trim((string) ($c156['operator_decision_reason'] ?? '')) === '') {
            return false;
        }

        foreach ($this->requiredC156NestedExpectations() as $expectation) {
            if ($this->valueAt($c156, $expectation['path']) !== $expectation['value']) {
                return false;
            }
        }

        return true;
    }

    private function requiredC156NestedExpectations(): array
    {
        return [
            ['path' => ['c156_operator_go_no_go_decision', 'review_valid'], 'value' => true],
            ['path' => ['c156_operator_go_no_go_decision', 'operator_decision'], 'value' => 'GO'],
            ['path' => ['c156_operator_go_no_go_decision', 'operator_decision_confirmed'], 'value' => true],
            ['path' => ['c156_operator_go_no_go_decision', 'operator_go_no_go_review_pass'], 'value' => true],
            ['path' => ['c156_operator_go_no_go_decision', 'ready_for_go_decision_finalization_review'], 'value' => true],
            ['path' => ['c156_operator_go_no_go_decision', 'controlled_output_generation_stopped_no_go'], 'value' => false],
            ['path' => ['c156_operator_go_no_go_decision', 'controlled_output_generation_deferred_hold'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'operator_decision'], 'value' => 'GO'],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'operator_decision_confirmed'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'operator_go_no_go_review_pass'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'ready_for_go_decision_finalization_review'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'operator_go_no_go_artifact_only'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'operator_go_no_go_used_for_publication'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'operator_go_no_go_used_for_plan_confirm_mutation'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_checklist', 'operator_go_no_go_reviewed'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_checklist', 'artifact_only'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_checklist', 'weekly_swing_stock_recommendation_published_in_c156'], 'value' => false],
        ];
    }

    private function publicationAndPlanGuardClean(array $c156): bool
    {
        foreach (self::PUBLICATION_AND_PLAN_FALSE_FIELDS as $field) {
            if (($c156[$field] ?? null) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $c156): bool
    {
        return ($c156['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c156['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c156['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c156['a01_remains_comparator_only'] ?? null) === true
            && ($c156['a01_promoted'] ?? false) === false
            && ($c156['candidate_promotion_executed'] ?? false) === false
            && ($c156['strategy_retune_executed'] ?? false) === false
            && ($c156['scoring_mutation_executed'] ?? false) === false
            && ($c156['catalog_selection_changed'] ?? false) === false
            && ($c156['runtime_selection_changed'] ?? false) === false;
    }

    private function c156LockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C156',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C156_STATUS,
            'actual_status' => is_array($load['payload']) ? ($load['payload']['status'] ?? null) : null,
            'expected_phase_label' => self::EXPECTED_C156_PHASE_LABEL,
            'actual_phase_label' => is_array($load['payload']) ? ($load['payload']['phase_label'] ?? null) : null,
            'expected_next_recommendation' => self::EXPECTED_C156_NEXT_RECOMMENDATION,
            'next_recommendation_match' => is_array($load['payload']) && $this->c156NextRecommendationMatches($load['payload']),
            'c156_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c156OperatorGoNoGoCarryForwardSummary(array $c156): array
    {
        return [
            'validation_completed' => true,
            'c156_operator_go_no_go_review_valid' => $this->c156OperatorGoComplete($c156),
            'operator_decision' => (string) ($c156['operator_decision'] ?? 'UNSET'),
            'operator_decision_confirmed' => (bool) ($c156['operator_decision_confirmed'] ?? false),
            'operator_decision_reason_present' => trim((string) ($c156['operator_decision_reason'] ?? '')) !== '',
            'ready_for_go_decision_finalization_review' => (bool) ($c156['ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review'] ?? false),
            'go_decision_finalization_review_allowed_next' => (bool) ($c156['production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next'] ?? false),
            'controlled_output_generation_stopped_no_go' => (bool) ($c156['controlled_output_generation_stopped_no_go'] ?? false),
            'controlled_output_generation_deferred_hold' => (bool) ($c156['controlled_output_generation_deferred_hold'] ?? false),
        ];
    }

    private function controlledOutputPublicationGuardSummary(array $c156): array
    {
        return [
            'guard_reviewed' => true,
            'official_output_generated_for_controlled_review' => (bool) ($c156['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c156),
        ];
    }

    private function candidateScopeFreezeSummary(array $c156, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c156),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_controlled_output_publication_boundary_review' => $pass,
            'backup_candidate_ready_for_controlled_output_publication_boundary_review' => $pass,
            'comparator_candidate_ready_for_controlled_output_publication_boundary_review' => false,
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
            'no_publication_confirmation_required' => true,
            'no_publication_confirmed' => (bool) ($options['no_publication_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
        ];
    }

    private function goDecisionFinalizationDecision(array $c156, bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'source_operator_decision' => (string) ($c156['operator_decision'] ?? 'UNSET'),
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'ready_for_controlled_output_publication_boundary_review' => $pass,
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'next_recommendation' => $pass ? self::C158_RECOMMENDATION : 'C157_TARGETED_C156_OPERATOR_GO_NO_GO_REPAIR',
            'decision_reason' => $pass ? 'Operator GO from locked C156 is finalized for controlled output publication boundary review only.' : 'C157 GO decision finalization could not be recorded because a validation gate failed.',
        ];
    }

    private function nextControlledOutputPublicationBoundaryDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C158_RECOMMENDATION : 'C157_TARGETED_C156_OPERATOR_GO_NO_GO_REPAIR',
            'next_scope' => $pass ? 'controlled output publication boundary review only; no publication, unrestricted publication, or PLAN/CONFIRM mutation from C157' : 'targeted repair before controlled output-generation GO decision finalization can be recorded',
            'next_is_concrete' => $pass,
            'next_requires_locked_c157_artifact' => $pass,
        ];
    }

    private function goDecisionFinalizationManifest(array $c156, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'controlled_output_generation_go_decision_finalization_review',
            'source_artifact' => self::C156_RUN_CODE,
            'source_artifact_path' => self::DEFAULT_C156_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C156_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C156_FILE_SHA1,
            'controlled_output_hash' => (string) $this->valueAt($c156, ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'controlled_output_hash']),
            'controlled_output_file_sha1' => (string) $this->valueAt($c156, ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'controlled_output_file_sha1']),
            'controlled_output_record_count' => (int) $this->valueAt($c156, ['weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_manifest', 'controlled_output_record_count']),
            'source_operator_decision' => (string) ($c156['operator_decision'] ?? 'UNSET'),
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_output_generation_go_decision_finalization_review_pass' => $pass,
            'ready_for_controlled_output_publication_boundary_review' => $pass,
            'go_decision_finalization_artifact_only' => true,
            'official_output_generated_for_controlled_review' => (bool) ($c156['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'go_decision_finalization_used_for_plan_confirm_mutation' => false,
            'go_decision_finalization_used_for_publication' => false,
        ];
    }

    private function goDecisionFinalizationChecklist(bool $pass, array $options): array
    {
        return [
            'go_decision_finalization_reviewed' => true,
            'c156_source_lock_reviewed' => true,
            'c156_operator_go_decision_carried_forward' => true,
            'go_decision_finalization_required' => true,
            'go_decision_finalization_confirmed' => (bool) ($options['go_decision_finalization_confirmed'] ?? false),
            'publication_not_executed_in_c157' => true,
            'unrestricted_publication_not_enabled_in_c157' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'no_publication_confirmed' => (bool) ($options['no_publication_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_go_finalization_gate_required' => true,
            'negative_no_publication_gate_required' => true,
            'negative_plan_confirm_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'go_decision_finalization_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_generated_in_c157' => false,
            'weekly_swing_stock_recommendation_published_in_c157' => false,
            'ready_for_next_boundary' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'controlled_output_generation_go_decision_finalization_review_valid' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'ready_for_controlled_output_publication_boundary_review' => $pass,
            'published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c157_role' => 'primary_controlled_output_publication_boundary_candidate',
                'primary_candidate_ready_for_controlled_output_publication_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c157_role' => 'backup_controlled_output_publication_boundary_candidate',
                'backup_candidate_ready_for_controlled_output_publication_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c157_role' => 'comparator_only_candidate',
                'controlled_output_generation_go_decision_finalization_review_valid' => false,
                'operator_go_decision' => 'NO_GO',
                'go_decision_finalized' => false,
                'ready_for_controlled_output_publication_boundary_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function goDecisionFinalizationContextSummary(bool $pass): array
    {
        return [
            'controlled_output_generation_go_decision_finalization_context_created' => true,
            'controlled_output_generation_go_decision_finalization_context_valid' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
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

    private function publicationPlanConfirmSafetySummary(array $c156): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c156),
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

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-45_C157_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW',
            'c156_operator_go_no_go_review_carried_forward' => true,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'go_decision_finalized' => $pass,
            'ready_for_controlled_output_publication_boundary_review' => $pass,
            'still_no_publication' => true,
            'still_no_unrestricted_publication' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C158_RECOMMENDATION : 'C157_TARGETED_C156_OPERATOR_GO_NO_GO_REPAIR',
            'planned_next_scope' => $pass ? 'controlled output publication boundary review only; no publication, unrestricted publication, or PLAN/CONFIRM mutation in C157' : 'targeted repair before controlled output-generation GO decision finalization can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C157 artifact hash',
                'locked C157 file SHA1',
                'finalized C157 GO decision',
                'publication still disabled',
                'PLAN/CONFIRM unchanged',
                'controlled output publication boundary checklist',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c156_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c156_artifact_not_modified' => true,
            'c157_is_go_decision_finalization_review_not_publication' => true,
            'c157_is_not_plan_confirm_mutation' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C157 validates C156 artifact_hash and file SHA1 locks before controlled output-generation GO decision finalization is recorded.',
            'C157 validates that C156 recorded operator GO, confirmation, decision reason, and next recommendation to C157.',
            'C157 requires --operator-approved, a non-empty --approval-reference, and explicit GO finalization confirmation.',
            'C157 also requires explicit confirmations that publication is not executed and PLAN/CONFIRM remains unchanged.',
            'C157 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C157 records artifact-only finalized GO and moves only to a controlled output publication boundary review.',
            'C157 does not publish recommendations, allow unrestricted publication, or mutate PLAN/CONFIRM.',
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
            'c156' => [
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
            'expected_c156_hash' => $load['expected_hash'],
            'actual_c156_hash' => $load['actual_hash'],
            'c156_hash_match' => $load['hash_match'],
            'expected_c156_file_sha1' => $load['expected_file_sha1'],
            'actual_c156_file_sha1' => $load['actual_file_sha1'],
            'c156_file_sha1_match' => $load['file_sha1_match'],
            'c156_convert_from_json_pass' => $load['convert_from_json_pass'],
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
