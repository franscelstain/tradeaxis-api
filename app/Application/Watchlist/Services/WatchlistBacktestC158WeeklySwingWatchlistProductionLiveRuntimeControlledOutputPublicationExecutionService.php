<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionService
{
    public const RUN_CODE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION';
    public const PHASE_LABEL = 'PR-47 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION';
    public const ARTIFACT_TYPE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION';

    public const DEFAULT_C158_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review.json';
    public const DEFAULT_EXPECTED_C158_BOUNDARY_HASH = 'f17826dd8eb388491be7ef94d18600647dbccc85';
    public const DEFAULT_EXPECTED_C158_BOUNDARY_FILE_SHA1 = 'B61A0522835494811E3306ABDFE37639D5ED56C8';
    public const DEFAULT_CONTROLLED_OUTPUT_PATH = 'storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json';
    public const DEFAULT_EXPECTED_CONTROLLED_OUTPUT_HASH = 'a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e';
    public const DEFAULT_EXPECTED_CONTROLLED_OUTPUT_FILE_SHA1 = 'AFCA465B7567AFA37034388B257F5F5808B17E5F';
    public const DEFAULT_CONTROLLED_PUBLICATION_PATH = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C158_BOUNDARY_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PRIMARY_AND_BACKUP';
    private const EXPECTED_C158_BOUNDARY_PHASE_LABEL = 'PR-46 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW';
    private const EXPECTED_C158_BOUNDARY_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C158_RESULT_REVIEW_RECOMMENDATION = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW';

    private const PASS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PASSED_CONTROLLED_PUBLICATION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const EXECUTION_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_EXECUTION_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C158_BOUNDARY_LOCK_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_ARTIFACT_LOCK_MISMATCH';
    private const C158_BOUNDARY_FILE_SHA1_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_FILE_SHA1_LOCK_MISMATCH';
    private const C158_BOUNDARY_CONVERT_FROM_JSON_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C158_BOUNDARY_STATUS_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_STATUS_MISMATCH';
    private const C158_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_PHASE_LABEL_MISMATCH';
    private const C158_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH';
    private const C158_BOUNDARY_INCOMPLETE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_REVIEW_INCOMPLETE';
    private const CONTROLLED_OUTPUT_LOCK_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH';
    private const CONTROLLED_OUTPUT_FILE_SHA1_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_FILE_SHA1_LOCK_MISMATCH';
    private const CONTROLLED_OUTPUT_CONVERT_FROM_JSON_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CONTROLLED_OUTPUT_INCOMPLETE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_INCOMPLETE';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C158_BOUNDARY_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_boundary_review_pass',
        'production_live_runtime_controlled_output_publication_boundary_review_pass',
        'ready_for_weekly_swing_watchlist_controlled_output_publication_execution',
        'production_live_runtime_controlled_output_publication_execution_allowed_next',
        'weekly_swing_watchlist_controlled_output_publication_execution_allowed_next',
        'weekly_swing_watchlist_controlled_output_generation_executed',
        'weekly_swing_watchlist_controlled_output_generation_result_reviewed',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_live_recommendation_generated',
        'weekly_swing_watchlist_controlled_publication_allowed_next',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c157_lock_valid',
        'c157_go_decision_finalization_valid',
        'c157_convert_from_json_pass',
        'c156_lock_valid',
        'c156_operator_go_no_go_review_valid',
        'controlled_output_lock_valid',
        'controlled_output_integrity_valid',
        'operator_approved',
        'publication_boundary_confirmed',
        'controlled_publication_only_confirmed',
        'plan_confirm_unchanged_confirmed',
        'primary_candidate_ready_for_controlled_output_publication_execution',
        'backup_candidate_ready_for_controlled_output_publication_execution',
        'a01_remains_comparator_only',
        'c158_boundary_review_only',
        'c158_topic_number_retained_for_execution',
        'c158_not_publication',
        'c158_not_unrestricted_publication',
        'c158_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C158_BOUNDARY_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_controlled_output_publication_execution',
        'temporary_negative_artifacts_remaining',
    ];

    private const SOURCE_FREE_PUBLICATION_FALSE_FIELDS = [
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
        string $c158BoundaryArtifact = self::DEFAULT_C158_BOUNDARY_ARTIFACT,
        string $expectedC158BoundaryHash = self::DEFAULT_EXPECTED_C158_BOUNDARY_HASH,
        string $expectedC158BoundaryFileSha1 = self::DEFAULT_EXPECTED_C158_BOUNDARY_FILE_SHA1,
        string $controlledOutputPath = self::DEFAULT_CONTROLLED_OUTPUT_PATH,
        string $expectedControlledOutputHash = self::DEFAULT_EXPECTED_CONTROLLED_OUTPUT_HASH,
        string $expectedControlledOutputFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_OUTPUT_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        string $controlledPublicationPath = self::DEFAULT_CONTROLLED_PUBLICATION_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt, $controlledPublicationPath);
        $boundaryLoad = $this->loadArtifactLock($c158BoundaryArtifact, $expectedC158BoundaryHash, $expectedC158BoundaryFileSha1, 'artifact_hash');
        $outputLoad = $this->loadArtifactLock($controlledOutputPath, $expectedControlledOutputHash, $expectedControlledOutputFileSha1, 'controlled_output_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($boundaryLoad, $outputLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($boundaryLoad, $outputLoad));

        if (! $boundaryLoad['exists'] || ! is_array($boundaryLoad['payload'])) {
            return $this->blocked($artifact, self::C158_BOUNDARY_LOCK_MISMATCH_STATUS, 'C158 boundary artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $boundaryLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []);
            $artifact['c158_boundary_convert_from_json_duplicate_keys'] = $boundaryLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C158_BOUNDARY_CONVERT_FROM_JSON_STATUS, 'C158 boundary artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $boundaryLoad['hash_match']) {
            return $this->blocked($artifact, self::C158_BOUNDARY_LOCK_MISMATCH_STATUS, 'C158 boundary artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $boundaryLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::C158_BOUNDARY_FILE_SHA1_MISMATCH_STATUS, 'C158 boundary file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c158Boundary = $boundaryLoad['payload'];
        if (($c158Boundary['status'] ?? null) !== self::EXPECTED_C158_BOUNDARY_STATUS || ($c158Boundary['reason_code'] ?? null) !== self::EXPECTED_C158_BOUNDARY_STATUS) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::C158_BOUNDARY_STATUS_MISMATCH_STATUS, 'C158 boundary status/reason is not controlled publication execution ready.', $outputPath, $overwrite);
        }
        if (($c158Boundary['phase_label'] ?? null) !== self::EXPECTED_C158_BOUNDARY_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::C158_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS, 'C158 boundary phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c158BoundaryNextRecommendationMatches($c158Boundary)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::C158_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C158 boundary next recommendation is not C158 publication execution.', $outputPath, $overwrite);
        }
        if (! $this->freePublicationAndPlanGuardClean($c158Boundary)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C158 boundary has free publication, unrestricted publication, or PLAN/CONFIRM mutation already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c158BoundaryComplete($c158Boundary)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::C158_BOUNDARY_INCOMPLETE_STATUS, 'C158 boundary evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c158Boundary)) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C158 boundary candidate scope does not match controlled publication execution scope.', $outputPath, $overwrite);
        }

        if (! $outputLoad['exists'] || ! is_array($outputLoad['payload'])) {
            return $this->blocked($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::CONTROLLED_OUTPUT_LOCK_MISMATCH_STATUS, 'Controlled output artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $outputLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []);
            $artifact['controlled_output_convert_from_json_duplicate_keys'] = $outputLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::CONTROLLED_OUTPUT_CONVERT_FROM_JSON_STATUS, 'Controlled output artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $outputLoad['hash_match']) {
            return $this->blocked($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::CONTROLLED_OUTPUT_LOCK_MISMATCH_STATUS, 'Controlled output artifact hash mismatch.', $outputPath, $overwrite);
        }
        if (! $outputLoad['file_sha1_match']) {
            return $this->blocked($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::CONTROLLED_OUTPUT_FILE_SHA1_MISMATCH_STATUS, 'Controlled output file SHA1 mismatch.', $outputPath, $overwrite);
        }
        if (! $this->controlledOutputReady($outputLoad['payload'])) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::CONTROLLED_OUTPUT_INCOMPLETE_STATUS, 'Controlled output evidence is incomplete or already published.', $outputPath, $overwrite);
        }

        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::APPROVAL_MISSING_STATUS, 'C158 execution requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_publication_execution_confirmed'] ?? false)
            || ! (bool) ($options['controlled_publication_only_confirmed'] ?? false)
            || ! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)
        ) {
            return $this->rejected($this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, false, []), self::EXECUTION_CONFIRMATION_MISSING_STATUS, 'C158 execution requires controlled-publication execution, controlled-only, and PLAN/CONFIRM unchanged confirmations.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $boundaryLoad, $outputLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false, []);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $publication = $this->writeControlledPublication(
            $this->controlledPublicationPayload($createdAt, $boundaryLoad, $outputLoad),
            $controlledPublicationPath,
            $overwrite
        );
        $artifact = $this->completeSections($artifact, $boundaryLoad, $outputLoad, $options, true, $publication);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C158 executes controlled output publication into a controlled publication artifact. Free publication remains locked, unrestricted publication remains disabled, and PLAN/CONFIRM is unchanged.';
        $artifact['diagnostic_conclusion'] = 'C158_CONTROLLED_OUTPUT_PUBLICATION_EXECUTED_CONTROLLED_ONLY_NOT_FREE_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C158_RESULT_REVIEW_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($publication));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $controlledPublicationPath): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-47',
            'internal_checkpoint' => 'C158',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'EXECUTION',
            'status' => 'C158_EXECUTION_NOT_RUN',
            'reason_code' => 'C158_EXECUTION_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_publication_path' => $controlledPublicationPath,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_pass' => false,
            'production_live_runtime_controlled_output_publication_execution_pass' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_result_review' => false,
            'production_live_runtime_controlled_output_publication_result_review_allowed_next' => false,
            'weekly_swing_watchlist_controlled_output_generation_executed' => false,
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'weekly_swing_watchlist_controlled_output_publication_executed' => false,
            'weekly_swing_watchlist_controlled_output_published' => false,
            'weekly_swing_watchlist_controlled_publication_artifact_created' => false,
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
            'c158_boundary_lock_valid' => false,
            'c158_publication_boundary_valid' => false,
            'c158_boundary_convert_from_json_pass' => false,
            'controlled_output_lock_valid' => false,
            'controlled_output_convert_from_json_pass' => false,
            'controlled_output_integrity_valid' => false,
            'operator_approved' => false,
            'approval_reference' => '',
            'controlled_publication_execution_confirmed' => false,
            'controlled_publication_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'primary_candidate_controlled_published' => false,
            'backup_candidate_controlled_published' => false,
            'comparator_candidate_controlled_published' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c158_controlled_output_publication_execution_only' => true,
            'c158_controlled_publication_only' => true,
            'c158_not_free_publication' => true,
            'c158_not_unrestricted_publication' => true,
            'c158_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C158_EXECUTION_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function passingTopLevelState(array $publication): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_pass' => true,
            'production_live_runtime_controlled_output_publication_execution_pass' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_result_review' => true,
            'production_live_runtime_controlled_output_publication_result_review_allowed_next' => true,
            'weekly_swing_watchlist_controlled_output_publication_executed' => true,
            'weekly_swing_watchlist_controlled_output_published' => true,
            'weekly_swing_watchlist_controlled_publication_artifact_created' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c158_boundary_lock_valid' => true,
            'c158_publication_boundary_valid' => true,
            'c158_boundary_convert_from_json_pass' => true,
            'controlled_output_lock_valid' => true,
            'controlled_output_convert_from_json_pass' => true,
            'controlled_output_integrity_valid' => true,
            'primary_candidate_controlled_published' => true,
            'backup_candidate_controlled_published' => true,
            'comparator_candidate_controlled_published' => false,
            'controlled_publication_path' => $publication['controlled_publication_path'] ?? null,
            'controlled_publication_hash' => $publication['controlled_publication_hash'] ?? null,
            'controlled_publication_file_sha1' => $publication['controlled_publication_file_sha1'] ?? null,
            'controlled_publication_record_count' => $publication['controlled_publication_record_count'] ?? 0,
        ];
    }

    private function completeSections(array $artifact, array $boundaryLoad, array $outputLoad, array $options, bool $pass, array $publication): array
    {
        $boundary = is_array($boundaryLoad['payload']) ? $boundaryLoad['payload'] : [];
        $controlledOutput = is_array($outputLoad['payload']) ? $outputLoad['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($boundary, $controlledOutput, $options, $pass, $publication));
        $artifact['c158_boundary_lock_validation_summary'] = $this->lockValidationSummary($boundaryLoad, 'C158_BOUNDARY', self::EXPECTED_C158_BOUNDARY_STATUS, self::EXPECTED_C158_BOUNDARY_PHASE_LABEL, self::EXPECTED_C158_BOUNDARY_NEXT_RECOMMENDATION);
        $artifact['controlled_output_lock_validation_summary'] = $this->controlledOutputLockValidationSummary($outputLoad);
        $artifact['c158_boundary_carry_forward_summary'] = $this->boundaryCarryForwardSummary($boundary);
        $artifact['controlled_output_carry_forward_summary'] = $this->controlledOutputCarryForwardSummary($controlledOutput);
        $artifact['controlled_publication_execution_summary'] = $this->controlledPublicationExecutionSummary($publication, $pass);
        $artifact['controlled_publication_manifest'] = $this->controlledPublicationManifest($publication, $pass);
        $artifact['controlled_publication_checklist'] = $this->controlledPublicationChecklist($pass, $options);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($boundary, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($boundary, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c158_candidate_controlled_publication_execution_scorecard'] = $this->candidateScorecard($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($boundaryLoad, $outputLoad);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function topLevelState(array $boundary, array $controlledOutput, array $options, bool $pass, array $publication): array
    {
        return [
            'weekly_swing_watchlist_controlled_output_generation_executed' => (bool) ($boundary['weekly_swing_watchlist_controlled_output_generation_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => (bool) ($boundary['weekly_swing_watchlist_controlled_output_generation_result_reviewed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($boundary['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generated' => (bool) ($boundary['weekly_swing_watchlist_live_recommendation_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => $pass,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($boundary['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($boundary['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($boundary['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($boundary['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c158_publication_boundary_valid' => $this->c158BoundaryComplete($boundary),
            'controlled_output_integrity_valid' => $this->controlledOutputReady($controlledOutput),
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'controlled_publication_execution_confirmed' => (bool) ($options['controlled_publication_execution_confirmed'] ?? false),
            'controlled_publication_only_confirmed' => (bool) ($options['controlled_publication_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'primary_candidate_controlled_published' => $pass,
            'backup_candidate_controlled_published' => $pass,
            'comparator_candidate_controlled_published' => false,
            'controlled_publication_path' => $publication['controlled_publication_path'] ?? null,
            'controlled_publication_hash' => $publication['controlled_publication_hash'] ?? null,
            'controlled_publication_file_sha1' => $publication['controlled_publication_file_sha1'] ?? null,
            'controlled_publication_record_count' => $publication['controlled_publication_record_count'] ?? 0,
        ];
    }

    private function controlledPublicationPayload(string $createdAt, array $boundaryLoad, array $outputLoad): array
    {
        $controlledOutput = $outputLoad['payload'];
        $rows = [];
        foreach ((array) ($controlledOutput['output_rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $rows[] = array_merge($row, [
                'output_mode' => 'controlled_publication_execution',
                'publish_state' => 'controlled_published',
                'publication_scope' => 'controlled_only',
            ]);
        }

        return [
            'controlled_publication_type' => 'weekly_swing_watchlist_controlled_output_publication',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'EXECUTION',
            'created_at' => $createdAt,
            'controlled_publication_hash' => null,
            'controlled_publication_hash_algorithm' => 'stable_sha1_json_payload',
            'source_c158_boundary_artifact_path' => $boundaryLoad['path'],
            'source_c158_boundary_artifact_hash' => $boundaryLoad['actual_hash'],
            'source_c158_boundary_file_sha1' => $boundaryLoad['actual_file_sha1'],
            'source_controlled_output_path' => $outputLoad['path'],
            'source_controlled_output_hash' => $outputLoad['actual_hash'],
            'source_controlled_output_file_sha1' => $outputLoad['actual_file_sha1'],
            'publication_mode' => 'controlled',
            'publication_state' => 'controlled_published',
            'public_release_state' => 'not_unrestricted',
            'weekly_swing_watchlist_controlled_output_publication_executed' => true,
            'weekly_swing_watchlist_controlled_output_published' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'output_rows' => $rows,
            'comparator_candidate' => [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'candidate_role' => 'comparator_only_not_controlled_published',
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function writeControlledPublication(array $payload, string $path, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) {
            $raw = (string) file_get_contents($path);
            $decoded = json_decode($raw, true);

            return [
                'controlled_publication_path' => $path,
                'controlled_publication_hash' => is_array($decoded) ? ($decoded['controlled_publication_hash'] ?? null) : null,
                'controlled_publication_file_sha1' => strtoupper(sha1($raw)),
                'controlled_publication_record_count' => is_array($decoded['output_rows'] ?? null) ? count($decoded['output_rows']) : 0,
                'write_skipped_existing_controlled_publication' => true,
            ];
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $hashPayload = $payload;
        $hashPayload['controlled_publication_hash'] = null;
        unset($hashPayload['controlled_publication_path']);
        $payload['controlled_publication_hash'] = sha1(json_encode($hashPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $payload['controlled_publication_path'] = $path;
        $raw = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
        file_put_contents($path, $raw);

        return [
            'controlled_publication_path' => $path,
            'controlled_publication_hash' => $payload['controlled_publication_hash'],
            'controlled_publication_file_sha1' => strtoupper(sha1($raw)),
            'controlled_publication_record_count' => count($payload['output_rows']),
            'write_skipped_existing_controlled_publication' => false,
        ];
    }

    private function c158BoundaryNextRecommendationMatches(array $boundary): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['controlled_output_publication_boundary_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($boundary, $path) !== self::EXPECTED_C158_BOUNDARY_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function c158BoundaryComplete(array $boundary): bool
    {
        foreach (self::REQUIRED_C158_BOUNDARY_TRUE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C158_BOUNDARY_FALSE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($boundary['topic_code'] ?? null) !== 'C158_CONTROLLED_OUTPUT_PUBLICATION' || ($boundary['topic_stage'] ?? null) !== 'BOUNDARY_REVIEW') {
            return false;
        }

        return true;
    }

    private function controlledOutputReady(array $output): bool
    {
        return ($output['controlled_output_type'] ?? null) === 'weekly_swing_watchlist_controlled_output_generation'
            && ($output['generation_mode'] ?? null) === 'controlled'
            && ($output['publication_state'] ?? null) === 'not_published'
            && ($output['weekly_swing_watchlist_controlled_output_generation_executed'] ?? null) === true
            && ($output['weekly_swing_watchlist_official_output_generated'] ?? null) === true
            && ($output['weekly_swing_watchlist_official_output_published'] ?? null) === false
            && ($output['weekly_swing_watchlist_publication_allowed'] ?? null) === false
            && ($output['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? null) === false
            && ($output['plan_confirm_mutated'] ?? null) === false
            && is_array($output['output_rows'] ?? null)
            && count($output['output_rows']) === 2
            && (($output['output_rows'][0]['candidate_code'] ?? null) === self::PRIMARY_CANDIDATE)
            && (($output['output_rows'][1]['candidate_code'] ?? null) === self::BACKUP_CANDIDATE)
            && (($output['comparator_candidate']['candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE)
            && (($output['comparator_candidate']['a01_remains_comparator_only'] ?? null) === true);
    }

    private function freePublicationAndPlanGuardClean(array $source): bool
    {
        foreach (self::SOURCE_FREE_PUBLICATION_FALSE_FIELDS as $field) {
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
            'next_recommendation_match' => is_array($load['payload']) && $this->c158BoundaryNextRecommendationMatches($load['payload']),
            'lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function controlledOutputLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'CONTROLLED_OUTPUT',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'controlled_output_ready' => is_array($load['payload']) && $this->controlledOutputReady($load['payload']),
        ];
    }

    private function boundaryCarryForwardSummary(array $boundary): array
    {
        return [
            'validation_completed' => true,
            'c158_boundary_valid' => $this->c158BoundaryComplete($boundary),
            'topic_code' => (string) ($boundary['topic_code'] ?? ''),
            'topic_stage' => (string) ($boundary['topic_stage'] ?? ''),
            'controlled_publication_allowed_next' => (bool) ($boundary['weekly_swing_watchlist_controlled_publication_allowed_next'] ?? false),
            'ready_for_controlled_output_publication_execution' => (bool) ($boundary['ready_for_weekly_swing_watchlist_controlled_output_publication_execution'] ?? false),
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function controlledOutputCarryForwardSummary(array $output): array
    {
        return [
            'validation_completed' => true,
            'controlled_output_ready' => $this->controlledOutputReady($output),
            'controlled_output_hash' => (string) ($output['controlled_output_hash'] ?? ''),
            'controlled_output_record_count' => is_array($output['output_rows'] ?? null) ? count($output['output_rows']) : 0,
            'publication_state' => (string) ($output['publication_state'] ?? ''),
            'official_output_published' => false,
        ];
    }

    private function controlledPublicationExecutionSummary(array $publication, bool $pass): array
    {
        return [
            'controlled_publication_execution_executed' => $pass,
            'controlled_publication_artifact_created' => $pass,
            'controlled_publication_path' => $publication['controlled_publication_path'] ?? null,
            'controlled_publication_hash' => $publication['controlled_publication_hash'] ?? null,
            'controlled_publication_file_sha1' => $publication['controlled_publication_file_sha1'] ?? null,
            'controlled_publication_record_count' => $publication['controlled_publication_record_count'] ?? 0,
            'free_publication_executed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function controlledPublicationManifest(array $publication, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'controlled_output_publication_execution',
            'controlled_publication_path' => $publication['controlled_publication_path'] ?? null,
            'controlled_publication_hash' => $publication['controlled_publication_hash'] ?? null,
            'controlled_publication_file_sha1' => $publication['controlled_publication_file_sha1'] ?? null,
            'controlled_publication_record_count' => $publication['controlled_publication_record_count'] ?? 0,
            'publication_mode' => 'controlled',
            'publication_state' => $pass ? 'controlled_published' : 'not_published',
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'controlled_output_publication_result_review_required_next' => $pass,
        ];
    }

    private function controlledPublicationChecklist(bool $pass, array $options): array
    {
        return [
            'c158_boundary_artifact_locked' => true,
            'controlled_output_artifact_locked' => true,
            'controlled_publication_execution_confirmed' => (bool) ($options['controlled_publication_execution_confirmed'] ?? false),
            'controlled_publication_only_confirmed' => (bool) ($options['controlled_publication_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'operator_approval_required' => true,
            'controlled_publication_execution_completed' => $pass,
            'free_publication_forbidden_in_c158_execution' => true,
            'unrestricted_publication_forbidden' => true,
            'plan_confirm_mutation_forbidden_in_c158_execution' => true,
            'result_review_required_next' => $pass,
            'same_topic_number_for_next_stage' => true,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $boundary, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'free_publication_and_plan_guard_clean' => $this->freePublicationAndPlanGuardClean($boundary),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => $pass,
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
            'primary_candidate_controlled_published' => $pass,
            'backup_candidate_controlled_published' => $pass,
            'comparator_candidate_controlled_published' => false,
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
            'controlled_publication_execution_confirmation_required' => true,
            'controlled_publication_execution_confirmed' => (bool) ($options['controlled_publication_execution_confirmed'] ?? false),
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
                'c158_role' => 'primary_candidate_controlled_published',
                'controlled_published' => $pass,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c158_role' => 'backup_candidate_controlled_published',
                'controlled_published' => $pass,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c158_role' => 'comparator_only_candidate',
                'controlled_published' => false,
                'free_published' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function documentationHygieneGuardSummary(array $boundaryLoad, array $outputLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c158_boundary_convert_from_json_pass' => $boundaryLoad['convert_from_json_pass'],
            'controlled_output_convert_from_json_pass' => $outputLoad['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => array_values(array_unique(array_merge($boundaryLoad['case_insensitive_duplicate_keys'], $outputLoad['case_insensitive_duplicate_keys']))),
            'c158_boundary_artifact_not_modified' => true,
            'controlled_output_artifact_not_modified' => true,
            'c158_execution_is_controlled_publication_not_free_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-47_C158_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'EXECUTION',
            'c158_boundary_carried_forward' => true,
            'controlled_publication_execution_pass' => $pass,
            'controlled_output_published_to_controlled_scope' => $pass,
            'same_topic_number_for_next_stage' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C158_RESULT_REVIEW_RECOMMENDATION : 'C158_TARGETED_BOUNDARY_OR_CONTROLLED_OUTPUT_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C158 controlled output publication result review only; still no unrestricted publication or PLAN/CONFIRM mutation from execution' : 'targeted boundary lock, controlled output lock, confirmation, publication guard, or cleanup repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass ? [
                'locked C158 publication execution artifact hash',
                'locked C158 publication execution file SHA1',
                'locked controlled publication artifact hash',
                'locked controlled publication file SHA1',
                'free publication still disabled',
                'PLAN/CONFIRM unchanged',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C158 execution validates C158 boundary artifact_hash and file SHA1 locks before controlled publication execution.',
            'C158 execution validates the controlled output artifact hash and file SHA1 before creating controlled publication evidence.',
            'C158 execution creates a controlled publication artifact only.',
            'C158 execution does not free-publish output, allow unrestricted publication, or mutate PLAN/CONFIRM.',
            'C158 execution keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C158 execution may only recommend same-topic publication result review next.',
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

    private function sourceArtifactLocks(array $boundaryLoad, array $outputLoad): array
    {
        return [
            'c158_boundary' => [
                'artifact_path' => $boundaryLoad['path'],
                'expected_artifact_hash' => $boundaryLoad['expected_hash'],
                'actual_artifact_hash' => $boundaryLoad['actual_hash'],
                'artifact_hash_match' => $boundaryLoad['hash_match'],
                'expected_file_sha1' => $boundaryLoad['expected_file_sha1'],
                'actual_file_sha1' => $boundaryLoad['actual_file_sha1'],
                'file_sha1_match' => $boundaryLoad['file_sha1_match'],
                'convert_from_json_pass' => $boundaryLoad['convert_from_json_pass'],
            ],
            'controlled_output' => [
                'artifact_path' => $outputLoad['path'],
                'expected_artifact_hash' => $outputLoad['expected_hash'],
                'actual_artifact_hash' => $outputLoad['actual_hash'],
                'artifact_hash_match' => $outputLoad['hash_match'],
                'expected_file_sha1' => $outputLoad['expected_file_sha1'],
                'actual_file_sha1' => $outputLoad['actual_file_sha1'],
                'file_sha1_match' => $outputLoad['file_sha1_match'],
                'convert_from_json_pass' => $outputLoad['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $boundaryLoad, array $outputLoad): array
    {
        return [
            'expected_c158_boundary_hash' => $boundaryLoad['expected_hash'],
            'actual_c158_boundary_hash' => $boundaryLoad['actual_hash'],
            'c158_boundary_hash_match' => $boundaryLoad['hash_match'],
            'expected_c158_boundary_file_sha1' => $boundaryLoad['expected_file_sha1'],
            'actual_c158_boundary_file_sha1' => $boundaryLoad['actual_file_sha1'],
            'c158_boundary_file_sha1_match' => $boundaryLoad['file_sha1_match'],
            'c158_boundary_convert_from_json_pass' => $boundaryLoad['convert_from_json_pass'],
            'expected_controlled_output_hash' => $outputLoad['expected_hash'],
            'actual_controlled_output_hash' => $outputLoad['actual_hash'],
            'controlled_output_hash_match' => $outputLoad['hash_match'],
            'expected_controlled_output_file_sha1' => $outputLoad['expected_file_sha1'],
            'actual_controlled_output_file_sha1' => $outputLoad['actual_file_sha1'],
            'controlled_output_file_sha1_match' => $outputLoad['file_sha1_match'],
            'controlled_output_convert_from_json_pass' => $outputLoad['convert_from_json_pass'],
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
