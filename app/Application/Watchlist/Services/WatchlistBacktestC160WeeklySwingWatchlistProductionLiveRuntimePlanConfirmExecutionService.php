<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionService
{
    public const RUN_CODE = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION';
    public const PHASE_LABEL = 'PR-56 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION';
    public const ARTIFACT_TYPE = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION';

    public const DEFAULT_C160_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-boundary-review.json';
    public const DEFAULT_EXPECTED_C160_BOUNDARY_HASH = 'b9ca7ca795c2d3a75ad2910263d5a7b3c249bab9';
    public const DEFAULT_EXPECTED_C160_BOUNDARY_FILE_SHA1 = 'D5C708775E5E6DEC644ACD54DEBBEDD370329004';
    public const DEFAULT_CONTROLLED_PUBLICATION_PATH = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    public const DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH = 'df064c7290ff4c3bfd0c7a8412d39299049c01d5';
    public const DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1 = 'D87AB8CD1564BE8B266B8A68011470272D49EE60';
    public const DEFAULT_CONTROLLED_PLAN_CONFIRM_PATH = 'storage/app/watchlist/output/c160-weekly-swing-watchlist-controlled-plan-confirm.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-execution.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C160_BOUNDARY_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_PASSED_READY_FOR_PLAN_CONFIRM_EXECUTION_PRIMARY_AND_BACKUP';
    private const EXPECTED_C160_BOUNDARY_PHASE_LABEL = 'PR-55 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW';
    private const EXPECTED_C160_BOUNDARY_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C160_RESULT_REVIEW_RECOMMENDATION = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW';

    private const PASS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_PASSED_CONTROLLED_PLAN_CONFIRM_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const PLAN_CONFIRM_EXECUTION_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_PLAN_CONFIRM_EXECUTION_CONFIRMATION_MISSING';
    private const CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C160_BOUNDARY_LOCK_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_ARTIFACT_LOCK_MISMATCH';
    private const C160_BOUNDARY_FILE_SHA1_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_FILE_SHA1_LOCK_MISMATCH';
    private const C160_BOUNDARY_CONVERT_FROM_JSON_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C160_BOUNDARY_STATUS_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_STATUS_MISMATCH';
    private const C160_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_PHASE_LABEL_MISMATCH';
    private const C160_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH';
    private const C160_BOUNDARY_INCOMPLETE_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_C160_BOUNDARY_REVIEW_INCOMPLETE';
    private const CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH';
    private const CONTROLLED_PUBLICATION_FILE_SHA1_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_FILE_SHA1_LOCK_MISMATCH';
    private const CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CONTROLLED_PUBLICATION_INCOMPLETE_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_INCOMPLETE';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C160_BOUNDARY_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_pass',
        'production_live_runtime_plan_confirm_boundary_review_pass',
        'ready_for_weekly_swing_watchlist_plan_confirm_execution',
        'production_live_runtime_plan_confirm_execution_allowed_next',
        'weekly_swing_watchlist_plan_confirm_execution_allowed_next',
        'weekly_swing_watchlist_controlled_output_publication_observed',
        'weekly_swing_watchlist_controlled_output_publication_observation_stable',
        'weekly_swing_watchlist_controlled_output_publication_executed',
        'weekly_swing_watchlist_controlled_output_published',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'weekly_swing_watchlist_official_output_generated',
        'plan_confirm_boundary_confirmed',
        'controlled_plan_confirm_only_confirmed',
        'plan_confirm_unchanged_confirmed',
        'plan_confirm_execution_allowed_next',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c159_finalization_lock_valid',
        'c159_go_decision_finalization_valid',
        'c159_finalization_convert_from_json_pass',
        'c159_topic_complete_after_finalization',
        'operator_approved',
        'primary_candidate_ready_for_plan_confirm_execution',
        'backup_candidate_ready_for_plan_confirm_execution',
        'a01_remains_comparator_only',
        'c160_boundary_review_only',
        'c160_topic_number_retained_for_execution',
        'c160_not_plan_confirm_mutation',
        'c160_not_live_plan_confirm_rollout',
        'c160_not_publication',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C160_BOUNDARY_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_execution',
        'temporary_negative_artifacts_remaining',
    ];

    private const SOURCE_FALSE_GUARDS = [
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
        'storage/app/watchlist/backtest/c160-*execution*-test.json',
        'storage/app/watchlist/backtest/c160-*plan-confirm-execution*-test.json',
        'storage/app/watchlist/backtest/c160-*negative-*-test.json',
        'storage/app/watchlist/backtest/c160-*missing-*-test.json',
        'storage/app/watchlist/backtest/c160-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c160-*invalid-*-test.json',
    ];

    public function execute(
        string $c160BoundaryArtifact = self::DEFAULT_C160_BOUNDARY_ARTIFACT,
        string $expectedC160BoundaryHash = self::DEFAULT_EXPECTED_C160_BOUNDARY_HASH,
        string $expectedC160BoundaryFileSha1 = self::DEFAULT_EXPECTED_C160_BOUNDARY_FILE_SHA1,
        string $controlledPublicationPath = self::DEFAULT_CONTROLLED_PUBLICATION_PATH,
        string $expectedControlledPublicationHash = self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH,
        string $expectedControlledPublicationFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        string $controlledPlanConfirmPath = self::DEFAULT_CONTROLLED_PLAN_CONFIRM_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt, $controlledPlanConfirmPath);
        $boundaryLoad = $this->loadArtifactLock($c160BoundaryArtifact, $expectedC160BoundaryHash, $expectedC160BoundaryFileSha1, 'artifact_hash');
        $publicationLoad = $this->loadArtifactLock($controlledPublicationPath, $expectedControlledPublicationHash, $expectedControlledPublicationFileSha1, 'controlled_publication_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($boundaryLoad, $publicationLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($boundaryLoad, $publicationLoad));

        if (! $boundaryLoad['exists'] || ! is_array($boundaryLoad['payload'])) {
            return $this->blocked($artifact, self::C160_BOUNDARY_LOCK_MISMATCH_STATUS, 'C160 boundary artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $boundaryLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []);
            $artifact['c160_boundary_convert_from_json_duplicate_keys'] = $boundaryLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C160_BOUNDARY_CONVERT_FROM_JSON_STATUS, 'C160 boundary artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $boundaryLoad['hash_match']) {
            return $this->blocked($artifact, self::C160_BOUNDARY_LOCK_MISMATCH_STATUS, 'C160 boundary artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $boundaryLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::C160_BOUNDARY_FILE_SHA1_MISMATCH_STATUS, 'C160 boundary file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $boundary = $boundaryLoad['payload'];
        if (($boundary['status'] ?? null) !== self::EXPECTED_C160_BOUNDARY_STATUS || ($boundary['reason_code'] ?? null) !== self::EXPECTED_C160_BOUNDARY_STATUS) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::C160_BOUNDARY_STATUS_MISMATCH_STATUS, 'C160 boundary status/reason is not PLAN/CONFIRM execution ready.', $outputPath, $overwrite);
        }
        if (($boundary['phase_label'] ?? null) !== self::EXPECTED_C160_BOUNDARY_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::C160_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS, 'C160 boundary phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c160BoundaryNextRecommendationMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::C160_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C160 boundary next recommendation is not C160 PLAN/CONFIRM execution.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($boundary)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C160 boundary already published, unlocked publication, mutated PLAN/CONFIRM, read activated catalog, or executed live rollout.', $outputPath, $overwrite);
        }
        if (! $this->c160BoundaryComplete($boundary)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::C160_BOUNDARY_INCOMPLETE_STATUS, 'C160 boundary evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C160 boundary candidate scope does not match PLAN/CONFIRM execution scope.', $outputPath, $overwrite);
        }

        if (! $publicationLoad['exists'] || ! is_array($publicationLoad['payload'])) {
            return $this->blocked($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS, 'Controlled publication artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []);
            $artifact['controlled_publication_convert_from_json_duplicate_keys'] = $publicationLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_STATUS, 'Controlled publication artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['hash_match']) {
            return $this->blocked($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS, 'Controlled publication artifact hash mismatch.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['file_sha1_match']) {
            return $this->blocked($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::CONTROLLED_PUBLICATION_FILE_SHA1_MISMATCH_STATUS, 'Controlled publication file SHA1 mismatch.', $outputPath, $overwrite);
        }
        if (! $this->controlledPublicationReady($publicationLoad['payload'])) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::CONTROLLED_PUBLICATION_INCOMPLETE_STATUS, 'Controlled publication evidence is incomplete, free-published, mutated, or candidate scope changed.', $outputPath, $overwrite);
        }

        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::APPROVAL_MISSING_STATUS, 'C160 PLAN/CONFIRM execution requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_execution_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::PLAN_CONFIRM_EXECUTION_CONFIRMATION_MISSING_STATUS, 'C160 execution requires --plan-confirm-execution-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING_STATUS, 'C160 execution requires --controlled-plan-confirm-only-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C160 execution requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, false, []), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C160 execution requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $boundaryLoad, $publicationLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false, []);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $controlledPlanConfirm = $this->writeControlledPlanConfirm(
            $this->controlledPlanConfirmPayload($createdAt, $boundaryLoad, $publicationLoad),
            $controlledPlanConfirmPath,
            $overwrite
        );
        $artifact = $this->completeSections($artifact, $boundaryLoad, $publicationLoad, $options, true, $controlledPlanConfirm);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C160 executes controlled PLAN/CONFIRM evidence only. PLAN/CONFIRM remains unchanged, activated-catalog reads remain disabled, live rollout remains disabled, and free publication remains locked.';
        $artifact['diagnostic_conclusion'] = 'C160_CONTROLLED_PLAN_CONFIRM_EXECUTION_COMPLETED_PLAN_CONFIRM_UNCHANGED_NO_LIVE_ROLLOUT';
        $artifact['next_step_recommendation'] = self::C160_RESULT_REVIEW_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($controlledPlanConfirm));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $controlledPlanConfirmPath): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-56',
            'internal_checkpoint' => 'C160',
            'topic_code' => 'C160_PLAN_CONFIRM',
            'topic_stage' => 'EXECUTION',
            'status' => 'C160_PLAN_CONFIRM_EXECUTION_NOT_RUN',
            'reason_code' => 'C160_PLAN_CONFIRM_EXECUTION_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_plan_confirm_path' => $controlledPlanConfirmPath,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_pass' => false,
            'production_live_runtime_plan_confirm_execution_pass' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_result_review' => false,
            'production_live_runtime_plan_confirm_result_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_result_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_only' => true,
            'weekly_swing_watchlist_controlled_output_publication_observed' => false,
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => false,
            'weekly_swing_watchlist_controlled_output_publication_executed' => false,
            'weekly_swing_watchlist_controlled_output_published' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_execution_confirmed' => false,
            'controlled_plan_confirm_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => false,
            'c160_boundary_lock_valid' => false,
            'c160_plan_confirm_boundary_valid' => false,
            'c160_boundary_convert_from_json_pass' => false,
            'controlled_publication_lock_valid' => false,
            'controlled_publication_integrity_valid' => false,
            'controlled_publication_convert_from_json_pass' => false,
            'operator_approved' => false,
            'approval_reference' => '',
            'primary_candidate_plan_confirm_controlled_executed' => false,
            'backup_candidate_plan_confirm_controlled_executed' => false,
            'comparator_candidate_plan_confirm_controlled_executed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c160_plan_confirm_execution_only' => true,
            'c160_controlled_plan_confirm_only' => true,
            'c160_not_plan_confirm_mutation' => true,
            'c160_not_live_plan_confirm_rollout' => true,
            'c160_not_publication' => true,
            'c160_topic_number_retained_for_result_review' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C160_PLAN_CONFIRM_EXECUTION_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function passingTopLevelState(array $controlledPlanConfirm): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_pass' => true,
            'production_live_runtime_plan_confirm_execution_pass' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_result_review' => true,
            'production_live_runtime_plan_confirm_result_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_result_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => true,
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => true,
            'weekly_swing_watchlist_plan_confirm_controlled_only' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c160_boundary_lock_valid' => true,
            'c160_plan_confirm_boundary_valid' => true,
            'c160_boundary_convert_from_json_pass' => true,
            'controlled_publication_lock_valid' => true,
            'controlled_publication_integrity_valid' => true,
            'controlled_publication_convert_from_json_pass' => true,
            'primary_candidate_plan_confirm_controlled_executed' => true,
            'backup_candidate_plan_confirm_controlled_executed' => true,
            'comparator_candidate_plan_confirm_controlled_executed' => false,
            'controlled_plan_confirm_path' => $controlledPlanConfirm['controlled_plan_confirm_path'] ?? null,
            'controlled_plan_confirm_hash' => $controlledPlanConfirm['controlled_plan_confirm_hash'] ?? null,
            'controlled_plan_confirm_file_sha1' => $controlledPlanConfirm['controlled_plan_confirm_file_sha1'] ?? null,
            'controlled_plan_confirm_record_count' => $controlledPlanConfirm['controlled_plan_confirm_record_count'] ?? 0,
        ];
    }

    private function completeSections(array $artifact, array $boundaryLoad, array $publicationLoad, array $options, bool $pass, array $controlledPlanConfirm): array
    {
        $boundary = is_array($boundaryLoad['payload']) ? $boundaryLoad['payload'] : [];
        $publication = is_array($publicationLoad['payload']) ? $publicationLoad['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($boundary, $publication, $options, $pass, $controlledPlanConfirm));
        $artifact['c160_boundary_lock_validation_summary'] = $this->lockValidationSummary($boundaryLoad, 'C160_PLAN_CONFIRM_BOUNDARY', self::EXPECTED_C160_BOUNDARY_STATUS, self::EXPECTED_C160_BOUNDARY_PHASE_LABEL, self::EXPECTED_C160_BOUNDARY_NEXT_RECOMMENDATION);
        $artifact['controlled_publication_lock_validation_summary'] = $this->controlledPublicationLockValidationSummary($publicationLoad);
        $artifact['c160_boundary_carry_forward_summary'] = $this->boundaryCarryForwardSummary($boundary);
        $artifact['controlled_publication_carry_forward_summary'] = $this->controlledPublicationCarryForwardSummary($publication);
        $artifact['controlled_plan_confirm_execution_summary'] = $this->controlledPlanConfirmExecutionSummary($controlledPlanConfirm, $pass);
        $artifact['controlled_plan_confirm_manifest'] = $this->controlledPlanConfirmManifest($controlledPlanConfirm, $pass);
        $artifact['controlled_plan_confirm_checklist'] = $this->controlledPlanConfirmChecklist($pass, $options);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($boundary, $publication, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($boundary, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c160_candidate_plan_confirm_execution_scorecard'] = $this->candidateScorecard($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($boundaryLoad, $publicationLoad);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function topLevelState(array $boundary, array $publication, array $options, bool $pass, array $controlledPlanConfirm): array
    {
        return [
            'weekly_swing_watchlist_controlled_output_publication_observed' => (bool) ($boundary['weekly_swing_watchlist_controlled_output_publication_observed'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => (bool) ($boundary['weekly_swing_watchlist_controlled_output_publication_observation_stable'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_executed' => (bool) ($publication['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_published' => (bool) ($publication['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($publication['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($boundary['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_execution_confirmed' => (bool) ($options['plan_confirm_execution_confirmed'] ?? false),
            'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($boundary['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($boundary['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($boundary['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($boundary['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c160_plan_confirm_boundary_valid' => $this->c160BoundaryComplete($boundary),
            'controlled_publication_integrity_valid' => $this->controlledPublicationReady($publication),
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'primary_candidate_plan_confirm_controlled_executed' => $pass,
            'backup_candidate_plan_confirm_controlled_executed' => $pass,
            'comparator_candidate_plan_confirm_controlled_executed' => false,
            'controlled_plan_confirm_path' => $controlledPlanConfirm['controlled_plan_confirm_path'] ?? null,
            'controlled_plan_confirm_hash' => $controlledPlanConfirm['controlled_plan_confirm_hash'] ?? null,
            'controlled_plan_confirm_file_sha1' => $controlledPlanConfirm['controlled_plan_confirm_file_sha1'] ?? null,
            'controlled_plan_confirm_record_count' => $controlledPlanConfirm['controlled_plan_confirm_record_count'] ?? 0,
        ];
    }

    private function controlledPlanConfirmPayload(string $createdAt, array $boundaryLoad, array $publicationLoad): array
    {
        $publication = $publicationLoad['payload'];
        $rows = [];
        foreach ((array) ($publication['output_rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $rows[] = [
                'rank' => $row['rank'] ?? null,
                'candidate_code' => $row['candidate_code'] ?? null,
                'candidate_role' => $row['candidate_role'] ?? null,
                'source_publication_scope' => $row['publication_scope'] ?? 'controlled_only',
                'plan_confirm_mode' => 'controlled_plan_confirm_execution',
                'plan_confirm_state' => 'controlled_confirmed',
                'plan_confirm_mutated' => false,
                'runtime_reads_activated_catalog' => false,
                'live_rollout_executed' => false,
            ];
        }

        return [
            'controlled_plan_confirm_type' => 'weekly_swing_watchlist_controlled_plan_confirm_execution',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'topic_code' => 'C160_PLAN_CONFIRM',
            'topic_stage' => 'EXECUTION',
            'created_at' => $createdAt,
            'controlled_plan_confirm_hash' => null,
            'controlled_plan_confirm_hash_algorithm' => 'stable_sha1_json_payload',
            'source_c160_boundary_artifact_path' => $boundaryLoad['path'],
            'source_c160_boundary_artifact_hash' => $boundaryLoad['actual_hash'],
            'source_c160_boundary_file_sha1' => $boundaryLoad['actual_file_sha1'],
            'source_controlled_publication_path' => $publicationLoad['path'],
            'source_controlled_publication_hash' => $publicationLoad['actual_hash'],
            'source_controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'],
            'plan_confirm_mode' => 'controlled',
            'plan_confirm_state' => 'controlled_executed',
            'baseline_plan_confirm_state' => 'unchanged',
            'runtime_catalog_read_state' => 'not_activated_catalog',
            'live_rollout_state' => 'not_executed',
            'publication_state' => 'controlled_publication_observed_not_free_published',
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => true,
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'output_rows' => $rows,
            'comparator_candidate' => [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'candidate_role' => 'comparator_only_not_plan_confirm_controlled_executed',
                'a01_remains_comparator_only' => true,
                'plan_confirm_state' => 'not_controlled_confirmed',
            ],
        ];
    }

    private function writeControlledPlanConfirm(array $payload, string $path, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) {
            $raw = (string) file_get_contents($path);
            $decoded = json_decode($raw, true);

            return [
                'controlled_plan_confirm_path' => $path,
                'controlled_plan_confirm_hash' => is_array($decoded) ? ($decoded['controlled_plan_confirm_hash'] ?? null) : null,
                'controlled_plan_confirm_file_sha1' => strtoupper(sha1($raw)),
                'controlled_plan_confirm_record_count' => is_array($decoded['output_rows'] ?? null) ? count($decoded['output_rows']) : 0,
                'write_skipped_existing_controlled_plan_confirm' => true,
            ];
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $hashPayload = $payload;
        $hashPayload['controlled_plan_confirm_hash'] = null;
        unset($hashPayload['controlled_plan_confirm_path']);
        $payload['controlled_plan_confirm_hash'] = sha1(json_encode($hashPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $payload['controlled_plan_confirm_path'] = $path;
        $raw = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
        file_put_contents($path, $raw);

        return [
            'controlled_plan_confirm_path' => $path,
            'controlled_plan_confirm_hash' => $payload['controlled_plan_confirm_hash'],
            'controlled_plan_confirm_file_sha1' => strtoupper(sha1($raw)),
            'controlled_plan_confirm_record_count' => count($payload['output_rows']),
            'write_skipped_existing_controlled_plan_confirm' => false,
        ];
    }

    private function c160BoundaryNextRecommendationMatches(array $boundary): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['plan_confirm_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($boundary, $path) !== self::EXPECTED_C160_BOUNDARY_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function c160BoundaryComplete(array $boundary): bool
    {
        foreach (self::REQUIRED_C160_BOUNDARY_TRUE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C160_BOUNDARY_FALSE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($boundary['topic_code'] ?? null) !== 'C160_PLAN_CONFIRM' || ($boundary['topic_stage'] ?? null) !== 'PLAN_CONFIRM_BOUNDARY_REVIEW') {
            return false;
        }

        return true;
    }

    private function controlledPublicationReady(array $publication): bool
    {
        return ($publication['controlled_publication_type'] ?? null) === 'weekly_swing_watchlist_controlled_output_publication'
            && ($publication['publication_mode'] ?? null) === 'controlled'
            && ($publication['publication_state'] ?? null) === 'controlled_published'
            && ($publication['public_release_state'] ?? null) === 'not_unrestricted'
            && ($publication['weekly_swing_watchlist_controlled_output_publication_executed'] ?? null) === true
            && ($publication['weekly_swing_watchlist_controlled_output_published'] ?? null) === true
            && ($publication['weekly_swing_watchlist_official_output_published'] ?? null) === false
            && ($publication['weekly_swing_watchlist_publication_allowed'] ?? null) === false
            && ($publication['weekly_swing_watchlist_controlled_publication_allowed'] ?? null) === true
            && ($publication['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? null) === false
            && ($publication['plan_confirm_mutated'] ?? null) === false
            && is_array($publication['output_rows'] ?? null)
            && count($publication['output_rows']) === 2
            && (($publication['output_rows'][0]['candidate_code'] ?? null) === self::PRIMARY_CANDIDATE)
            && (($publication['output_rows'][1]['candidate_code'] ?? null) === self::BACKUP_CANDIDATE)
            && (($publication['comparator_candidate']['candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE)
            && (($publication['comparator_candidate']['a01_remains_comparator_only'] ?? null) === true);
    }

    private function publicationAndPlanGuardClean(array $source): bool
    {
        foreach (self::SOURCE_FALSE_GUARDS as $field) {
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
            && ($source['primary_candidate_ready_for_plan_confirm_execution'] ?? null) === true
            && ($source['backup_candidate_ready_for_plan_confirm_execution'] ?? null) === true
            && ($source['comparator_candidate_ready_for_plan_confirm_execution'] ?? null) === false
            && ($source['a01_remains_comparator_only'] ?? null) === true
            && ($source['a01_promoted'] ?? false) === false
            && ($source['candidate_promotion_executed'] ?? false) === false
            && ($source['candidate_rerank_executed'] ?? false) === false
            && ($source['strategy_retune_executed'] ?? false) === false
            && ($source['scoring_mutation_executed'] ?? false) === false
            && ($source['catalog_selection_changed'] ?? false) === false
            && ($source['runtime_selection_changed'] ?? false) === false;
    }

    private function lockValidationSummary(array $load, string $source, string $expectedStatus, string $expectedPhaseLabel, string $expectedNext): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => $source,
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => $expectedStatus,
            'actual_status' => is_array($load['payload']) ? ($load['payload']['status'] ?? null) : null,
            'expected_phase_label' => $expectedPhaseLabel,
            'actual_phase_label' => is_array($load['payload']) ? ($load['payload']['phase_label'] ?? null) : null,
            'expected_next_recommendation' => $expectedNext,
            'next_recommendation_match' => is_array($load['payload']) && $this->c160BoundaryNextRecommendationMatches($load['payload']),
            'lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function controlledPublicationLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'CONTROLLED_PUBLICATION',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'controlled_publication_ready' => is_array($load['payload']) && $this->controlledPublicationReady($load['payload']),
        ];
    }

    private function boundaryCarryForwardSummary(array $boundary): array
    {
        return [
            'validation_completed' => true,
            'c160_boundary_valid' => $this->c160BoundaryComplete($boundary),
            'topic_code' => (string) ($boundary['topic_code'] ?? ''),
            'topic_stage' => (string) ($boundary['topic_stage'] ?? ''),
            'ready_for_plan_confirm_execution' => (bool) ($boundary['ready_for_weekly_swing_watchlist_plan_confirm_execution'] ?? false),
            'same_topic_number_for_execution' => (bool) ($boundary['c160_topic_number_retained_for_execution'] ?? false),
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function controlledPublicationCarryForwardSummary(array $publication): array
    {
        return [
            'validation_completed' => true,
            'controlled_publication_ready' => $this->controlledPublicationReady($publication),
            'controlled_publication_hash' => (string) ($publication['controlled_publication_hash'] ?? ''),
            'controlled_publication_record_count' => is_array($publication['output_rows'] ?? null) ? count($publication['output_rows']) : 0,
            'publication_state' => (string) ($publication['publication_state'] ?? ''),
            'public_release_state' => (string) ($publication['public_release_state'] ?? ''),
            'official_output_published' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function controlledPlanConfirmExecutionSummary(array $controlledPlanConfirm, bool $pass): array
    {
        return [
            'controlled_plan_confirm_execution_executed' => $pass,
            'controlled_plan_confirm_artifact_created' => $pass,
            'controlled_plan_confirm_path' => $controlledPlanConfirm['controlled_plan_confirm_path'] ?? null,
            'controlled_plan_confirm_hash' => $controlledPlanConfirm['controlled_plan_confirm_hash'] ?? null,
            'controlled_plan_confirm_file_sha1' => $controlledPlanConfirm['controlled_plan_confirm_file_sha1'] ?? null,
            'controlled_plan_confirm_record_count' => $controlledPlanConfirm['controlled_plan_confirm_record_count'] ?? 0,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
            'free_publication_executed' => false,
        ];
    }

    private function controlledPlanConfirmManifest(array $controlledPlanConfirm, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'plan_confirm_execution',
            'controlled_plan_confirm_path' => $controlledPlanConfirm['controlled_plan_confirm_path'] ?? null,
            'controlled_plan_confirm_hash' => $controlledPlanConfirm['controlled_plan_confirm_hash'] ?? null,
            'controlled_plan_confirm_file_sha1' => $controlledPlanConfirm['controlled_plan_confirm_file_sha1'] ?? null,
            'controlled_plan_confirm_record_count' => $controlledPlanConfirm['controlled_plan_confirm_record_count'] ?? 0,
            'plan_confirm_mode' => 'controlled',
            'plan_confirm_state' => $pass ? 'controlled_executed' : 'not_executed',
            'baseline_plan_confirm_state' => 'unchanged',
            'activated_catalog_read_state' => 'not_enabled',
            'live_rollout_state' => 'not_executed',
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_result_review_required_next' => $pass,
        ];
    }

    private function controlledPlanConfirmChecklist(bool $pass, array $options): array
    {
        return [
            'c160_boundary_artifact_locked' => true,
            'controlled_publication_artifact_locked' => true,
            'plan_confirm_execution_confirmed' => (bool) ($options['plan_confirm_execution_confirmed'] ?? false),
            'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'operator_approval_required' => true,
            'controlled_plan_confirm_execution_completed' => $pass,
            'plan_confirm_mutation_forbidden_in_c160_execution' => true,
            'activated_catalog_read_forbidden_in_c160_execution' => true,
            'live_plan_confirm_rollout_forbidden_in_c160_execution' => true,
            'free_publication_forbidden_in_c160_execution' => true,
            'result_review_required_next' => $pass,
            'same_topic_number_for_next_stage' => true,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $boundary, array $publication, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'boundary_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($boundary),
            'controlled_publication_ready' => $this->controlledPublicationReady($publication),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($publication['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_only' => $pass,
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
            'primary_candidate_plan_confirm_controlled_executed' => $pass,
            'backup_candidate_plan_confirm_controlled_executed' => $pass,
            'comparator_candidate_plan_confirm_controlled_executed' => false,
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
            'plan_confirm_execution_confirmation_required' => true,
            'plan_confirm_execution_confirmed' => (bool) ($options['plan_confirm_execution_confirmed'] ?? false),
            'controlled_plan_confirm_only_confirmation_required' => true,
            'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c160_role' => 'primary_candidate_plan_confirm_controlled_executed',
                'plan_confirm_controlled_executed' => $pass,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c160_role' => 'backup_candidate_plan_confirm_controlled_executed',
                'plan_confirm_controlled_executed' => $pass,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c160_role' => 'comparator_only_candidate',
                'plan_confirm_controlled_executed' => false,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function documentationHygieneGuardSummary(array $boundaryLoad, array $publicationLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c160_boundary_convert_from_json_pass' => $boundaryLoad['convert_from_json_pass'],
            'controlled_publication_convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => array_values(array_unique(array_merge($boundaryLoad['case_insensitive_duplicate_keys'], $publicationLoad['case_insensitive_duplicate_keys']))),
            'c160_boundary_artifact_not_modified' => true,
            'controlled_publication_artifact_not_modified' => true,
            'c160_execution_is_controlled_plan_confirm_not_live_rollout' => true,
            'c160_topic_number_retained_for_next_stage' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-56_C160_PLAN_CONFIRM_EXECUTION',
            'topic_code' => 'C160_PLAN_CONFIRM',
            'topic_stage' => 'EXECUTION',
            'c160_boundary_carried_forward' => true,
            'controlled_publication_carried_forward' => true,
            'controlled_plan_confirm_execution_pass' => $pass,
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
            'planned_next_review' => $pass ? self::C160_RESULT_REVIEW_RECOMMENDATION : 'C160_TARGETED_PLAN_CONFIRM_EXECUTION_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C160 PLAN/CONFIRM result review only; controlled execution evidence exists, while PLAN/CONFIRM mutation, activated-catalog reads, live rollout, and free publication remain disabled' : 'targeted boundary lock, controlled publication lock, confirmation, publication/PLAN guard, or cleanup repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass ? [
                'locked C160 PLAN/CONFIRM execution artifact hash',
                'locked C160 PLAN/CONFIRM execution file SHA1',
                'locked controlled PLAN/CONFIRM artifact hash',
                'locked controlled PLAN/CONFIRM artifact file SHA1',
                'PLAN/CONFIRM unchanged evidence',
                'live rollout still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C160 execution validates C160 boundary artifact_hash and file SHA1 locks before controlled PLAN/CONFIRM execution.',
            'C160 execution validates the C158 controlled publication artifact hash and file SHA1 before creating controlled PLAN/CONFIRM evidence.',
            'C160 execution creates a controlled PLAN/CONFIRM artifact only.',
            'C160 execution does not mutate PLAN/CONFIRM, read the activated catalog, execute live PLAN/CONFIRM rollout, or free-publish output.',
            'C160 execution keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C160 execution may only recommend same-topic PLAN/CONFIRM result review next.',
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

    private function sourceArtifactLocks(array $boundaryLoad, array $publicationLoad): array
    {
        return [
            'c160_plan_confirm_boundary' => [
                'artifact_path' => $boundaryLoad['path'],
                'expected_artifact_hash' => $boundaryLoad['expected_hash'],
                'actual_artifact_hash' => $boundaryLoad['actual_hash'],
                'artifact_hash_match' => $boundaryLoad['hash_match'],
                'expected_file_sha1' => $boundaryLoad['expected_file_sha1'],
                'actual_file_sha1' => $boundaryLoad['actual_file_sha1'],
                'file_sha1_match' => $boundaryLoad['file_sha1_match'],
                'convert_from_json_pass' => $boundaryLoad['convert_from_json_pass'],
            ],
            'controlled_publication' => [
                'artifact_path' => $publicationLoad['path'],
                'expected_artifact_hash' => $publicationLoad['expected_hash'],
                'actual_artifact_hash' => $publicationLoad['actual_hash'],
                'artifact_hash_match' => $publicationLoad['hash_match'],
                'expected_file_sha1' => $publicationLoad['expected_file_sha1'],
                'actual_file_sha1' => $publicationLoad['actual_file_sha1'],
                'file_sha1_match' => $publicationLoad['file_sha1_match'],
                'convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $boundaryLoad, array $publicationLoad): array
    {
        return [
            'expected_c160_boundary_hash' => $boundaryLoad['expected_hash'],
            'actual_c160_boundary_hash' => $boundaryLoad['actual_hash'],
            'c160_boundary_hash_match' => $boundaryLoad['hash_match'],
            'expected_c160_boundary_file_sha1' => $boundaryLoad['expected_file_sha1'],
            'actual_c160_boundary_file_sha1' => $boundaryLoad['actual_file_sha1'],
            'c160_boundary_file_sha1_match' => $boundaryLoad['file_sha1_match'],
            'c160_boundary_convert_from_json_pass' => $boundaryLoad['convert_from_json_pass'],
            'expected_controlled_publication_hash' => $publicationLoad['expected_hash'],
            'actual_controlled_publication_hash' => $publicationLoad['actual_hash'],
            'controlled_publication_hash_match' => $publicationLoad['hash_match'],
            'expected_controlled_publication_file_sha1' => $publicationLoad['expected_file_sha1'],
            'actual_controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'],
            'controlled_publication_file_sha1_match' => $publicationLoad['file_sha1_match'],
            'controlled_publication_convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1, string $hashKey): array
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
                $actualHash = $decoded[$hashKey] ?? null;
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
