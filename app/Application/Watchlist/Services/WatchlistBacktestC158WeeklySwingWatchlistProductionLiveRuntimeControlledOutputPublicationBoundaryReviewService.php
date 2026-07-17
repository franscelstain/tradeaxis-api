<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationBoundaryReviewService
{
    public const RUN_CODE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-46 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW';

    public const DEFAULT_C157_ARTIFACT = 'storage/app/watchlist/backtest/c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C157_HASH = '36f8aadb64d1994bde030efcfec985c7fd0df411';
    public const DEFAULT_EXPECTED_C157_FILE_SHA1 = 'E3B40E1080F3C3CCE5E39E0A660E38937F25A68B';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C157_STATUS = 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C157_PHASE_LABEL = 'PR-45 / C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C157_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C158_EXECUTION_RECOMMENDATION = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION';

    private const PASS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const PUBLICATION_BOUNDARY_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_BOUNDARY_CONFIRMATION_MISSING';
    private const CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C157_LOCK_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_ARTIFACT_LOCK_MISMATCH';
    private const C157_FILE_SHA1_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_FILE_SHA1_LOCK_MISMATCH';
    private const C157_CONVERT_FROM_JSON_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C157_STATUS_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_STATUS_MISMATCH';
    private const C157_PHASE_LABEL_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_PHASE_LABEL_MISMATCH';
    private const C157_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_NEXT_RECOMMENDATION_MISMATCH';
    private const C157_GO_FINALIZATION_INCOMPLETE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_GO_FINALIZATION_INCOMPLETE';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C157_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass',
        'production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'no_publication_confirmed',
        'plan_confirm_unchanged_confirmed',
        'ready_for_weekly_swing_watchlist_controlled_output_publication_boundary_review',
        'production_live_runtime_controlled_output_publication_boundary_review_allowed_next',
        'controlled_output_generation_go_decision_finalization_manifest_created',
        'weekly_swing_watchlist_controlled_output_generation_executed',
        'weekly_swing_watchlist_controlled_output_generation_result_reviewed',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_live_recommendation_generated',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c156_lock_valid',
        'c156_operator_go_no_go_review_valid',
        'c156_convert_from_json_pass',
        'c155_lock_valid',
        'c155_controlled_output_generation_result_review_valid',
        'controlled_output_lock_valid',
        'controlled_output_integrity_valid',
        'primary_candidate_ready_for_controlled_output_publication_boundary_review',
        'backup_candidate_ready_for_controlled_output_publication_boundary_review',
        'a01_remains_comparator_only',
        'c157_go_decision_finalization_review_only',
        'c157_not_publication',
        'c157_not_unrestricted_publication',
        'c157_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C157_FALSE_FIELDS = [
        'comparator_candidate_ready_for_controlled_output_publication_boundary_review',
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
        string $c157Artifact = self::DEFAULT_C157_ARTIFACT,
        string $expectedC157Hash = self::DEFAULT_EXPECTED_C157_HASH,
        string $expectedC157FileSha1 = self::DEFAULT_EXPECTED_C157_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c157Artifact, $expectedC157Hash, $expectedC157FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C157_LOCK_MISMATCH_STATUS, 'C157 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c157_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C157_CONVERT_FROM_JSON_STATUS, 'C157 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C157_LOCK_MISMATCH_STATUS, 'C157 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C157_FILE_SHA1_MISMATCH_STATUS, 'C157 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c157 = $load['payload'];
        if (($c157['status'] ?? null) !== self::EXPECTED_C157_STATUS || ($c157['reason_code'] ?? null) !== self::EXPECTED_C157_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C157_STATUS_MISMATCH_STATUS, 'C157 status/reason is not controlled output publication boundary ready.', $outputPath, $overwrite);
        }
        if (($c157['phase_label'] ?? null) !== self::EXPECTED_C157_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C157_PHASE_LABEL_MISMATCH_STATUS, 'C157 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c157NextRecommendationMatches($c157)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C157_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C157 next recommendation is not C158 boundary review.', $outputPath, $overwrite);
        }
        if (! $this->c157GoFinalizationComplete($c157)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C157_GO_FINALIZATION_INCOMPLETE_STATUS, 'C157 GO finalization evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($c157)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C157 has already published, unlocked publication, or mutated PLAN/CONFIRM.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c157)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C157 candidate scope does not match locked C158 boundary scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C158 boundary requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['publication_boundary_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_BOUNDARY_CONFIRMATION_MISSING_STATUS, 'C158 boundary requires --publication-boundary-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_publication_only_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_MISSING_STATUS, 'C158 boundary requires --controlled-publication-only-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C158 boundary requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C158 boundary locks C157 and opens controlled output publication execution within the same C158 topic. It does not publish, unlock unrestricted publication, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C158_BOUNDARY_PASSED_READY_FOR_C158_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_NOT_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C158_EXECUTION_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-46',
            'internal_checkpoint' => 'C158',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'BOUNDARY_REVIEW',
            'status' => 'C158_BOUNDARY_NOT_RUN',
            'reason_code' => 'C158_BOUNDARY_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_pass' => false,
            'production_live_runtime_controlled_output_publication_boundary_review_pass' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_execution' => false,
            'production_live_runtime_controlled_output_publication_execution_allowed_next' => false,
            'weekly_swing_watchlist_controlled_output_publication_execution_allowed_next' => false,
            'weekly_swing_watchlist_controlled_output_generation_executed' => false,
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed_next' => false,
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
            'c157_lock_valid' => false,
            'c157_go_decision_finalization_valid' => false,
            'c157_convert_from_json_pass' => false,
            'c156_lock_valid' => false,
            'c156_operator_go_no_go_review_valid' => false,
            'controlled_output_lock_valid' => false,
            'controlled_output_integrity_valid' => false,
            'operator_approved' => false,
            'approval_reference' => '',
            'publication_boundary_confirmed' => false,
            'controlled_publication_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'primary_candidate_ready_for_controlled_output_publication_execution' => false,
            'backup_candidate_ready_for_controlled_output_publication_execution' => false,
            'comparator_candidate_ready_for_controlled_output_publication_execution' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c158_boundary_review_only' => true,
            'c158_topic_number_retained_for_execution' => true,
            'c158_not_publication' => true,
            'c158_not_unrestricted_publication' => true,
            'c158_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C158_BOUNDARY_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c157 = is_array($load['payload']) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($c157, $pass, $options));
        $artifact['c157_lock_validation_summary'] = $this->c157LockValidationSummary($load);
        $artifact['c157_go_decision_finalization_carry_forward_summary'] = $this->c157GoDecisionFinalizationCarryForwardSummary($c157);
        $artifact['controlled_output_publication_boundary_decision'] = $this->controlledOutputPublicationBoundaryDecision($pass);
        $artifact['controlled_output_publication_boundary_manifest'] = $this->controlledOutputPublicationBoundaryManifest($c157, $pass);
        $artifact['controlled_output_publication_boundary_checklist'] = $this->controlledOutputPublicationBoundaryChecklist($pass, $options);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($c157);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c157, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c158_candidate_controlled_output_publication_boundary_scorecard'] = $this->candidateScorecard($pass);
        $artifact['controlled_output_publication_boundary_context_summary'] = $this->boundaryContextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function topLevelState(array $c157, bool $pass, array $options): array
    {
        return [
            'weekly_swing_watchlist_controlled_output_generation_executed' => (bool) ($c157['weekly_swing_watchlist_controlled_output_generation_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => (bool) ($c157['weekly_swing_watchlist_controlled_output_generation_result_reviewed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($c157['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => (bool) ($c157['weekly_swing_watchlist_live_recommendation_generated'] ?? false),
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed_next' => $pass,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($c157['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c157['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c157['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c157['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c157_lock_valid' => (bool) (($c157['artifact_hash'] ?? null) === self::DEFAULT_EXPECTED_C157_HASH),
            'c157_go_decision_finalization_valid' => $this->c157GoFinalizationComplete($c157),
            'c156_lock_valid' => (bool) ($c157['c156_lock_valid'] ?? false),
            'c156_operator_go_no_go_review_valid' => (bool) ($c157['c156_operator_go_no_go_review_valid'] ?? false),
            'controlled_output_lock_valid' => (bool) ($c157['controlled_output_lock_valid'] ?? false),
            'controlled_output_integrity_valid' => (bool) ($c157['controlled_output_integrity_valid'] ?? false),
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'publication_boundary_confirmed' => (bool) ($options['publication_boundary_confirmed'] ?? false),
            'controlled_publication_only_confirmed' => (bool) ($options['controlled_publication_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'primary_candidate_ready_for_controlled_output_publication_execution' => $pass,
            'backup_candidate_ready_for_controlled_output_publication_execution' => $pass,
            'comparator_candidate_ready_for_controlled_output_publication_execution' => false,
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_pass' => true,
            'production_live_runtime_controlled_output_publication_boundary_review_pass' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_execution' => true,
            'production_live_runtime_controlled_output_publication_execution_allowed_next' => true,
            'weekly_swing_watchlist_controlled_output_publication_execution_allowed_next' => true,
            'weekly_swing_watchlist_controlled_publication_allowed_next' => true,
            'c157_lock_valid' => true,
            'c157_go_decision_finalization_valid' => true,
            'c157_convert_from_json_pass' => true,
            'primary_candidate_ready_for_controlled_output_publication_execution' => true,
            'backup_candidate_ready_for_controlled_output_publication_execution' => true,
            'comparator_candidate_ready_for_controlled_output_publication_execution' => false,
        ];
    }

    private function c157NextRecommendationMatches(array $c157): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['c157_go_decision_finalization_decision', 'next_recommendation'],
            ['next_controlled_output_publication_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c157, $path) !== self::EXPECTED_C157_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function c157GoFinalizationComplete(array $c157): bool
    {
        foreach (self::REQUIRED_C157_TRUE_FIELDS as $field) {
            if (($c157[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C157_FALSE_FIELDS as $field) {
            if (($c157[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($c157['operator_go_decision'] ?? null) !== 'GO' || ($c157['operator_decision'] ?? null) !== 'GO') {
            return false;
        }
        foreach ($this->requiredC157NestedExpectations() as $expectation) {
            if ($this->valueAt($c157, $expectation['path']) !== $expectation['value']) {
                return false;
            }
        }

        return true;
    }

    private function requiredC157NestedExpectations(): array
    {
        return [
            ['path' => ['c157_go_decision_finalization_decision', 'review_valid'], 'value' => true],
            ['path' => ['c157_go_decision_finalization_decision', 'operator_go_decision'], 'value' => 'GO'],
            ['path' => ['c157_go_decision_finalization_decision', 'go_decision_finalized'], 'value' => true],
            ['path' => ['c157_go_decision_finalization_decision', 'ready_for_controlled_output_publication_boundary_review'], 'value' => true],
            ['path' => ['next_controlled_output_publication_boundary_decision', 'next_is_concrete'], 'value' => true],
            ['path' => ['next_controlled_output_publication_boundary_decision', 'next_requires_locked_c157_artifact'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'go_decision_finalization_artifact_only'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'go_decision_finalized'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'official_output_published'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'publication_allowed'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'go_decision_finalization_used_for_publication'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'go_decision_finalization_used_for_plan_confirm_mutation'], 'value' => false],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_checklist', 'go_decision_finalization_reviewed'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_checklist', 'artifact_only'], 'value' => true],
            ['path' => ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_checklist', 'weekly_swing_stock_recommendation_published_in_c157'], 'value' => false],
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
            && ($source['a01_remains_comparator_only'] ?? null) === true
            && ($source['a01_promoted'] ?? false) === false
            && ($source['candidate_promotion_executed'] ?? false) === false
            && ($source['strategy_retune_executed'] ?? false) === false
            && ($source['scoring_mutation_executed'] ?? false) === false
            && ($source['catalog_selection_changed'] ?? false) === false
            && ($source['runtime_selection_changed'] ?? false) === false;
    }

    private function c157LockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C157',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C157_STATUS,
            'actual_status' => is_array($load['payload']) ? ($load['payload']['status'] ?? null) : null,
            'expected_phase_label' => self::EXPECTED_C157_PHASE_LABEL,
            'actual_phase_label' => is_array($load['payload']) ? ($load['payload']['phase_label'] ?? null) : null,
            'expected_next_recommendation' => self::EXPECTED_C157_NEXT_RECOMMENDATION,
            'next_recommendation_match' => is_array($load['payload']) && $this->c157NextRecommendationMatches($load['payload']),
            'c157_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c157GoDecisionFinalizationCarryForwardSummary(array $c157): array
    {
        return [
            'validation_completed' => true,
            'c157_go_decision_finalization_valid' => $this->c157GoFinalizationComplete($c157),
            'operator_go_decision' => (string) ($c157['operator_go_decision'] ?? 'UNSET'),
            'go_decision_finalized' => (bool) ($c157['go_decision_finalized'] ?? false),
            'ready_for_controlled_output_publication_boundary_review' => (bool) ($c157['ready_for_weekly_swing_watchlist_controlled_output_publication_boundary_review'] ?? false),
            'official_output_generated_for_controlled_review' => (bool) ($c157['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function controlledOutputPublicationBoundaryDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'BOUNDARY_REVIEW',
            'controlled_output_publication_execution_allowed_next' => $pass,
            'controlled_output_publication_executed_in_boundary' => false,
            'official_output_published' => false,
            'publication_allowed_in_boundary' => false,
            'controlled_publication_allowed_next' => $pass,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'next_recommendation' => $pass ? self::C158_EXECUTION_RECOMMENDATION : 'C158_TARGETED_C157_GO_FINALIZATION_REPAIR',
            'next_uses_same_topic_number' => $pass,
        ];
    }

    private function controlledOutputPublicationBoundaryManifest(array $c157, bool $pass): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'controlled_output_publication_boundary_review',
            'source_artifact_path' => self::DEFAULT_C157_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C157_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C157_FILE_SHA1,
            'controlled_output_hash' => (string) $this->valueAt($c157, ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'controlled_output_hash']),
            'controlled_output_file_sha1' => (string) $this->valueAt($c157, ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'controlled_output_file_sha1']),
            'controlled_output_record_count' => (int) $this->valueAt($c157, ['weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_manifest', 'controlled_output_record_count']),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'ready_for_controlled_output_publication_execution' => $pass,
            'controlled_output_publication_execution_required_next' => $pass,
            'controlled_output_publication_executed_in_c158_boundary' => false,
            'official_output_generated_for_controlled_review' => (bool) ($c157['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => false,
            'publication_allowed_in_boundary' => false,
            'controlled_publication_allowed_next' => $pass,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'boundary_review_only' => true,
        ];
    }

    private function controlledOutputPublicationBoundaryChecklist(bool $pass, array $options): array
    {
        return [
            'c157_artifact_locked' => true,
            'controlled_output_publication_boundary_reviewed' => true,
            'publication_boundary_confirmed' => (bool) ($options['publication_boundary_confirmed'] ?? false),
            'controlled_publication_only_confirmed' => (bool) ($options['controlled_publication_only_confirmed'] ?? false),
            'operator_approval_required' => true,
            'runtime_bridge_must_remain_active' => true,
            'weekly_swing_live_output_must_remain_enabled' => true,
            'controlled_output_publication_execution_deferred_to_same_c158_topic_execution_stage' => true,
            'official_output_publication_forbidden_in_boundary' => true,
            'unrestricted_publication_forbidden' => true,
            'plan_confirm_mutation_forbidden_in_boundary' => true,
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
            'weekly_swing_watchlist_controlled_publication_allowed_next' => true,
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
            'primary_candidate_ready_for_controlled_output_publication_execution' => $pass,
            'backup_candidate_ready_for_controlled_output_publication_execution' => $pass,
            'comparator_candidate_ready_for_controlled_output_publication_execution' => false,
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
            'publication_boundary_confirmation_required' => true,
            'publication_boundary_confirmed' => (bool) ($options['publication_boundary_confirmed'] ?? false),
            'controlled_publication_only_confirmation_required' => true,
            'controlled_publication_only_confirmed' => (bool) ($options['controlled_publication_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c158_role' => 'primary_candidate_ready_for_controlled_output_publication_execution',
                'ready_for_controlled_output_publication_execution' => $pass,
                'published' => false,
                'publication_allowed_in_boundary' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c158_role' => 'backup_standby_candidate_ready_for_controlled_output_publication_execution',
                'ready_for_controlled_output_publication_execution' => $pass,
                'published' => false,
                'publication_allowed_in_boundary' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c158_role' => 'comparator_only_candidate',
                'ready_for_controlled_output_publication_execution' => false,
                'published' => false,
                'publication_allowed_in_boundary' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function boundaryContextSummary(bool $pass): array
    {
        return [
            'controlled_output_publication_boundary_context_created' => true,
            'controlled_output_publication_boundary_context_valid' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_plan_confirm' => false,
            'context_used_for_publication' => false,
            'publication_allowed_in_boundary' => false,
            'controlled_publication_allowed_next' => $pass,
            'unrestricted_publication_allowed' => false,
            'official_output_published' => false,
            'plan_confirm_mutated' => false,
            'runtime_bridge_active' => true,
            'weekly_swing_watchlist_runtime_active' => true,
            'weekly_swing_watchlist_live_output_enabled' => true,
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c157_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c157_artifact_not_modified' => true,
            'c158_is_boundary_review_not_publication_execution' => true,
            'c158_topic_number_retained_for_next_stage' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-46_C158_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'BOUNDARY_REVIEW',
            'c157_go_finalization_carried_forward' => true,
            'controlled_output_publication_boundary_review_pass' => $pass,
            'ready_for_controlled_output_publication_execution' => $pass,
            'same_topic_number_for_next_stage' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C158_EXECUTION_RECOMMENDATION : 'C158_TARGETED_C157_GO_FINALIZATION_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C158 controlled output publication execution only; still no unrestricted publication or PLAN/CONFIRM mutation from boundary review' : 'targeted C157 lock, GO finalization, candidate scope, publication guard, or cleanup repair',
            'same_topic_number_for_next_stage' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C158 boundary artifact hash',
                'locked C158 boundary file SHA1',
                'locked C157 artifact hash',
                'operator approval reference for controlled output publication execution',
                'controlled publication only confirmation',
                'PLAN/CONFIRM unchanged confirmation',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C158 boundary validates C157 artifact_hash and file SHA1 locks before opening controlled publication execution.',
            'C158 boundary confirms the finalized GO decision from C157 remains artifact-only evidence.',
            'C158 boundary keeps the same topic number for the next execution stage.',
            'C158 boundary does not publish output, allow unrestricted publication, or mutate PLAN/CONFIRM.',
            'C158 boundary keeps E02 primary, B01 backup, and A01 comparator-only.',
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
            'c157' => [
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
            'expected_c157_hash' => $load['expected_hash'],
            'actual_c157_hash' => $load['actual_hash'],
            'c157_hash_match' => $load['hash_match'],
            'expected_c157_file_sha1' => $load['expected_file_sha1'],
            'actual_c157_file_sha1' => $load['actual_file_sha1'],
            'c157_file_sha1_match' => $load['file_sha1_match'],
            'c157_convert_from_json_pass' => $load['convert_from_json_pass'],
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
