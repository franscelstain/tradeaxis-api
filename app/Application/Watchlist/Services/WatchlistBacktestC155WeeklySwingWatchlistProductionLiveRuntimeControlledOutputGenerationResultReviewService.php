<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewService
{
    public const RUN_CODE = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-43 / C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW';

    public const DEFAULT_C154_ARTIFACT = 'storage/app/watchlist/backtest/c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution.json';
    public const DEFAULT_EXPECTED_C154_HASH = 'cd321cbbbbc1fa3902da5928a61741e80c8bd437';
    public const DEFAULT_EXPECTED_C154_FILE_SHA1 = '82C8C90E04A7B7C5208BC37E40CAC8B02673CACB';
    public const DEFAULT_CONTROLLED_OUTPUT_ARTIFACT = 'storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json';
    public const DEFAULT_EXPECTED_CONTROLLED_OUTPUT_HASH = 'a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e';
    public const DEFAULT_EXPECTED_CONTROLLED_OUTPUT_FILE_SHA1 = 'AFCA465B7567AFA37034388B257F5F5808B17E5F';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C154_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_PASSED_CONTROLLED_OUTPUT_GENERATED_NOT_PUBLISHED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C154_PHASE_LABEL = 'PR-42 / C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION';
    private const EXPECTED_C154_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C156_RECOMMENDATION = 'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const RESULT_REVIEW_CONFIRMATION_MISSING_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C154_LOCK_MISMATCH_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_ARTIFACT_LOCK_MISMATCH';
    private const C154_FILE_SHA1_MISMATCH_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_FILE_SHA1_LOCK_MISMATCH';
    private const C154_CONVERT_FROM_JSON_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C154_STATUS_MISMATCH_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_STATUS_MISMATCH';
    private const C154_PHASE_LABEL_MISMATCH_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_PHASE_LABEL_MISMATCH';
    private const C154_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_NEXT_RECOMMENDATION_MISMATCH';
    private const C154_EXECUTION_INCOMPLETE_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_CONTROLLED_OUTPUT_GENERATION_EXECUTION_INCOMPLETE';
    private const CONTROLLED_OUTPUT_LOCK_MISMATCH_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH';
    private const CONTROLLED_OUTPUT_FILE_SHA1_MISMATCH_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_FILE_SHA1_LOCK_MISMATCH';
    private const CONTROLLED_OUTPUT_CONVERT_FROM_JSON_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CONTROLLED_OUTPUT_INTEGRITY_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_INTEGRITY_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C154_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_pass',
        'production_live_runtime_controlled_output_generation_execution_pass',
        'ready_for_weekly_swing_watchlist_controlled_output_generation_result_review',
        'production_live_runtime_controlled_output_generation_result_review_allowed_next',
        'weekly_swing_watchlist_controlled_output_generation_executed',
        'weekly_swing_watchlist_controlled_output_generated',
        'weekly_swing_watchlist_controlled_output_artifact_created',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_live_recommendation_generated',
        'production_live_runtime_activation_executed',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_executed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c153_lock_valid',
        'c153_controlled_output_generation_boundary_valid',
        'c153_convert_from_json_pass',
        'c152_lock_valid',
        'c152_controlled_output_generation_boundary_ready',
        'primary_candidate_live_runtime_active',
        'backup_candidate_live_runtime_standby_active',
        'a01_remains_comparator_only',
        'c154_controlled_output_generation_execution_only',
        'c154_not_publication',
        'c154_not_unrestricted_publication',
        'c154_not_plan_confirm_mutation',
        'operator_approved',
        'controlled_output_confirmed',
        'no_publication_confirmed',
        'plan_confirm_unchanged_confirmed',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C154_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_live_runtime_active',
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
        string $c154Artifact = self::DEFAULT_C154_ARTIFACT,
        string $expectedC154Hash = self::DEFAULT_EXPECTED_C154_HASH,
        string $expectedC154FileSha1 = self::DEFAULT_EXPECTED_C154_FILE_SHA1,
        string $controlledOutputArtifact = self::DEFAULT_CONTROLLED_OUTPUT_ARTIFACT,
        string $expectedControlledOutputHash = self::DEFAULT_EXPECTED_CONTROLLED_OUTPUT_HASH,
        string $expectedControlledOutputFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_OUTPUT_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')), $controlledOutputArtifact);
        $c154Load = $this->loadJsonLock($c154Artifact, $expectedC154Hash, $expectedC154FileSha1, 'artifact_hash');
        $controlledLoad = $this->loadJsonLock($controlledOutputArtifact, $expectedControlledOutputHash, $expectedControlledOutputFileSha1, 'controlled_output_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($c154Load, $controlledLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($c154Load, $controlledLoad));

        if (! $c154Load['exists'] || ! is_array($c154Load['payload'])) {
            return $this->blocked($artifact, self::C154_LOCK_MISMATCH_STATUS, 'C154 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $c154Load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $c154Load, $controlledLoad, $options, false);
            $artifact['c154_convert_from_json_duplicate_keys'] = $c154Load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C154_CONVERT_FROM_JSON_STATUS, 'C154 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $c154Load['hash_match']) {
            return $this->blocked($artifact, self::C154_LOCK_MISMATCH_STATUS, 'C154 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $c154Load['file_sha1_match']) {
            return $this->blocked($artifact, self::C154_FILE_SHA1_MISMATCH_STATUS, 'C154 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        if (! $controlledLoad['exists'] || ! is_array($controlledLoad['payload'])) {
            return $this->blocked($artifact, self::CONTROLLED_OUTPUT_LOCK_MISMATCH_STATUS, 'Controlled output artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $controlledLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $c154Load, $controlledLoad, $options, false);
            $artifact['controlled_output_convert_from_json_duplicate_keys'] = $controlledLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::CONTROLLED_OUTPUT_CONVERT_FROM_JSON_STATUS, 'Controlled output artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $controlledLoad['hash_match']) {
            return $this->blocked($artifact, self::CONTROLLED_OUTPUT_LOCK_MISMATCH_STATUS, 'Controlled output hash mismatch.', $outputPath, $overwrite);
        }
        if (! $controlledLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::CONTROLLED_OUTPUT_FILE_SHA1_MISMATCH_STATUS, 'Controlled output file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c154 = $c154Load['payload'];
        $controlledOutput = $controlledLoad['payload'];
        if (($c154['status'] ?? null) !== self::EXPECTED_C154_STATUS || ($c154['reason_code'] ?? null) !== self::EXPECTED_C154_STATUS) {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::C154_STATUS_MISMATCH_STATUS, 'C154 status/reason is not controlled output result-review ready.', $outputPath, $overwrite);
        }
        if (($c154['phase_label'] ?? null) !== self::EXPECTED_C154_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::C154_PHASE_LABEL_MISMATCH_STATUS, 'C154 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c154NextRecommendationMatches($c154)) {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::C154_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C154 next recommendation is not C155.', $outputPath, $overwrite);
        }
        if (! $this->c154ExecutionComplete($c154)) {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::C154_EXECUTION_INCOMPLETE_STATUS, 'C154 controlled output-generation execution evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($c154, $controlledOutput)) {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C154 or controlled output has already published, unlocked publication, or mutated PLAN/CONFIRM.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c154, $controlledOutput)) {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'Controlled output candidate scope does not match the locked C154 scope.', $outputPath, $overwrite);
        }
        if (! $this->controlledOutputIntegrityValid($c154, $controlledOutput, $controlledLoad)) {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::CONTROLLED_OUTPUT_INTEGRITY_STATUS, 'Controlled output artifact does not match C154 execution manifest.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::APPROVAL_MISSING_STATUS, 'C155 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['result_review_confirmed'] ?? false)
            || ! (bool) ($options['no_publication_confirmed'] ?? false)
            || ! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)
        ) {
            return $this->rejected($this->completeSections($artifact, $c154Load, $controlledLoad, $options, false), self::RESULT_REVIEW_CONFIRMATION_MISSING_STATUS, 'C155 requires result-review, no-publication, and PLAN/CONFIRM unchanged confirmations.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $c154Load, $controlledLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $c154Load, $controlledLoad, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C155 reviews the C154 controlled output-generation result. The controlled output is valid for operator go/no-go review; it is still not published, unrestricted publication remains disabled, and PLAN/CONFIRM is unchanged.';
        $artifact['diagnostic_conclusion'] = 'C155_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_PASSED_OUTPUT_VALID_NOT_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C156_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($controlledLoad));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $controlledOutputArtifact): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-43',
            'internal_checkpoint' => 'C155',
            'status' => 'C155_NOT_RUN',
            'reason_code' => 'C155_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_output_path' => $controlledOutputArtifact,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_pass' => false,
            'production_live_runtime_controlled_output_generation_result_review_pass' => false,
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => false,
            'weekly_swing_watchlist_controlled_output_generation_result_review_manifest_created' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_review' => false,
            'production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed_next' => false,
            'weekly_swing_watchlist_controlled_output_generation_executed' => false,
            'weekly_swing_watchlist_controlled_output_generated' => false,
            'weekly_swing_watchlist_controlled_output_artifact_created' => false,
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
            'c154_lock_valid' => false,
            'c154_controlled_output_generation_execution_valid' => false,
            'c154_convert_from_json_pass' => false,
            'controlled_output_lock_valid' => false,
            'controlled_output_convert_from_json_pass' => false,
            'controlled_output_integrity_valid' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_controlled_output_result_reviewed' => false,
            'backup_candidate_controlled_output_result_reviewed' => false,
            'comparator_candidate_controlled_output_result_reviewed' => false,
            'a01_remains_comparator_only' => true,
            'c155_controlled_output_generation_result_review_only' => true,
            'c155_not_publication' => true,
            'c155_not_unrestricted_publication' => true,
            'c155_not_plan_confirm_mutation' => true,
            'operator_approved' => false,
            'approval_reference' => '',
            'result_review_confirmed' => false,
            'no_publication_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C155_NOT_RUN',
            'next_step_recommendation' => null,
        ];
    }

    private function passingTopLevelState(array $controlledLoad): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_pass' => true,
            'production_live_runtime_controlled_output_generation_result_review_pass' => true,
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => true,
            'weekly_swing_watchlist_controlled_output_generation_result_review_manifest_created' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_review' => true,
            'production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed_next' => true,
            'weekly_swing_watchlist_controlled_output_generation_executed' => true,
            'weekly_swing_watchlist_controlled_output_generated' => true,
            'weekly_swing_watchlist_controlled_output_artifact_created' => true,
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
            'c154_lock_valid' => true,
            'c154_controlled_output_generation_execution_valid' => true,
            'c154_convert_from_json_pass' => true,
            'controlled_output_lock_valid' => true,
            'controlled_output_convert_from_json_pass' => true,
            'controlled_output_integrity_valid' => true,
            'controlled_output_hash' => $controlledLoad['actual_hash'],
            'controlled_output_file_sha1' => $controlledLoad['actual_file_sha1'],
            'controlled_output_record_count' => $this->controlledOutputRecordCount((array) ($controlledLoad['payload'] ?? [])),
            'primary_candidate_controlled_output_result_reviewed' => true,
            'backup_candidate_controlled_output_result_reviewed' => true,
            'comparator_candidate_controlled_output_result_reviewed' => false,
            'a01_remains_comparator_only' => true,
            'c155_controlled_output_generation_result_review_only' => true,
            'c155_not_publication' => true,
            'c155_not_unrestricted_publication' => true,
            'c155_not_plan_confirm_mutation' => true,
        ];
    }

    private function completeSections(array $artifact, array $c154Load, array $controlledLoad, array $options, bool $pass): array
    {
        $c154 = is_array($c154Load['payload'] ?? null) ? $c154Load['payload'] : [];
        $controlledOutput = is_array($controlledLoad['payload'] ?? null) ? $controlledLoad['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c154_lock_validation_summary'] = $this->c154LockValidationSummary($c154Load, $c154);
        $artifact['controlled_output_lock_validation_summary'] = $this->controlledOutputLockValidationSummary($controlledLoad, $controlledOutput);
        $artifact['c154_execution_carry_forward_summary'] = $this->c154ExecutionCarryForwardSummary($c154, $pass);
        $artifact['controlled_output_result_review_summary'] = $this->controlledOutputResultReviewSummary($controlledOutput, $controlledLoad, $pass);
        $artifact['controlled_output_integrity_summary'] = $this->controlledOutputIntegritySummary($c154, $controlledOutput, $controlledLoad);
        $artifact['controlled_output_publication_guard_summary'] = $this->controlledOutputPublicationGuardSummary($c154, $controlledOutput);
        $artifact['candidate_controlled_output_result_scorecard'] = $this->candidateScorecard($controlledOutput, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['result_review_confirmation_summary'] = $this->resultReviewConfirmationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($c154Load, $controlledLoad);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C155_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');
        $artifact['result_review_confirmed'] = (bool) ($options['result_review_confirmed'] ?? false);
        $artifact['no_publication_confirmed'] = (bool) ($options['no_publication_confirmed'] ?? false);
        $artifact['plan_confirm_unchanged_confirmed'] = (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false);
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($pass) {
            $artifact = array_merge($artifact, $this->passingTopLevelState($controlledLoad));
        }

        return $artifact;
    }

    private function c154ExecutionComplete(array $c154): bool
    {
        foreach (self::REQUIRED_C154_TRUE_FIELDS as $field) {
            if (! (bool) ($c154[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C154_FALSE_FIELDS as $field) {
            if ((bool) ($c154[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function publicationAndPlanGuardClean(array $c154, array $controlledOutput): bool
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
            if ((bool) ($c154[$field] ?? false)) {
                return false;
            }
        }
        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutated',
        ] as $field) {
            if ((bool) ($controlledOutput[$field] ?? false)) {
                return false;
            }
        }

        return ($controlledOutput['publication_state'] ?? null) === 'not_published';
    }

    private function controlledOutputIntegrityValid(array $c154, array $controlledOutput, array $controlledLoad): bool
    {
        if (($controlledOutput['controlled_output_type'] ?? null) !== 'weekly_swing_watchlist_controlled_output_generation') {
            return false;
        }
        if (($controlledOutput['run_code'] ?? null) !== 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION') {
            return false;
        }
        if (($controlledOutput['phase_label'] ?? null) !== self::EXPECTED_C154_PHASE_LABEL) {
            return false;
        }
        if (($controlledOutput['controlled_output_hash'] ?? null) !== $controlledLoad['actual_hash']) {
            return false;
        }
        if (($c154['controlled_output_hash'] ?? null) !== $controlledLoad['actual_hash']) {
            return false;
        }
        if (($c154['controlled_output_file_sha1'] ?? null) !== $controlledLoad['actual_file_sha1']) {
            return false;
        }
        if (($c154['controlled_output_record_count'] ?? null) !== 2 || $this->controlledOutputRecordCount($controlledOutput) !== 2) {
            return false;
        }
        if (($controlledOutput['generation_mode'] ?? null) !== 'controlled') {
            return false;
        }
        if (! (bool) ($controlledOutput['runtime_bridge_active'] ?? false) || ! (bool) ($controlledOutput['weekly_swing_watchlist_live_output_enabled'] ?? false)) {
            return false;
        }
        if (! (bool) ($controlledOutput['weekly_swing_watchlist_controlled_output_generation_executed'] ?? false)) {
            return false;
        }
        if (! (bool) ($controlledOutput['weekly_swing_watchlist_official_output_generated'] ?? false)) {
            return false;
        }

        return $this->candidateScopeMatches($c154, $controlledOutput);
    }

    private function candidateScopeMatches(array $c154, array $controlledOutput): bool
    {
        if (($c154['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return false;
        }
        if (($c154['backup_candidate_code'] ?? null) !== self::BACKUP_CANDIDATE) {
            return false;
        }
        if (($c154['comparator_candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }
        if ((bool) ($c154['a01_promoted'] ?? false) || ! (bool) ($c154['a01_remains_comparator_only'] ?? true)) {
            return false;
        }

        $rows = is_array($controlledOutput['output_rows'] ?? null) ? array_values($controlledOutput['output_rows']) : [];
        if (count($rows) !== 2) {
            return false;
        }
        $expectedRows = [
            [1, self::PRIMARY_CANDIDATE, 'controlled_generation_only', 'not_published'],
            [2, self::BACKUP_CANDIDATE, 'controlled_generation_only', 'not_published'],
        ];
        foreach ($expectedRows as $index => $expected) {
            $row = is_array($rows[$index] ?? null) ? $rows[$index] : [];
            if (($row['rank'] ?? null) !== $expected[0]) {
                return false;
            }
            if (($row['candidate_code'] ?? null) !== $expected[1]) {
                return false;
            }
            if (($row['output_mode'] ?? null) !== $expected[2]) {
                return false;
            }
            if (($row['publish_state'] ?? null) !== $expected[3]) {
                return false;
            }
        }

        $comparator = is_array($controlledOutput['comparator_candidate'] ?? null) ? $controlledOutput['comparator_candidate'] : [];
        if (($comparator['candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }

        return (bool) ($comparator['a01_remains_comparator_only'] ?? false);
    }

    private function c154NextRecommendationMatches(array $c154): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c154, $path);
            if ($value !== null && $value !== self::EXPECTED_C154_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c154['next_step_recommendation'] ?? null) === self::EXPECTED_C154_NEXT_RECOMMENDATION;
    }

    private function controlledOutputRecordCount(array $controlledOutput): int
    {
        return is_array($controlledOutput['output_rows'] ?? null) ? count($controlledOutput['output_rows']) : 0;
    }

    private function c154LockValidationSummary(array $load, array $c154): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C154',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C154_STATUS,
            'actual_status' => $c154['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C154_PHASE_LABEL,
            'actual_phase_label' => $c154['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C154_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c154NextRecommendationMatches($c154),
            'c154_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function controlledOutputLockValidationSummary(array $load, array $controlledOutput): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'controlled_output',
            'artifact_path' => $load['path'],
            'expected_controlled_output_hash' => $load['expected_hash'],
            'actual_controlled_output_hash' => $load['actual_hash'],
            'controlled_output_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'generation_mode' => $controlledOutput['generation_mode'] ?? null,
            'publication_state' => $controlledOutput['publication_state'] ?? null,
            'controlled_output_record_count' => $this->controlledOutputRecordCount($controlledOutput),
            'controlled_output_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c154ExecutionCarryForwardSummary(array $c154, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c154_execution_valid' => $this->c154ExecutionComplete($c154),
            'controlled_output_generation_executed' => (bool) ($c154['weekly_swing_watchlist_controlled_output_generation_executed'] ?? false),
            'controlled_output_generated' => (bool) ($c154['weekly_swing_watchlist_controlled_output_generated'] ?? false),
            'controlled_output_artifact_created' => (bool) ($c154['weekly_swing_watchlist_controlled_output_artifact_created'] ?? false),
            'official_output_generated_for_controlled_review' => (bool) ($c154['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => (bool) ($c154['weekly_swing_watchlist_official_output_published'] ?? false),
            'publication_allowed' => (bool) ($c154['weekly_swing_watchlist_publication_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($c154['plan_confirm_mutated'] ?? false),
            'c155_result_review_pass' => $pass,
        ];
    }

    private function controlledOutputResultReviewSummary(array $controlledOutput, array $controlledLoad, bool $pass): array
    {
        return [
            'result_review_executed' => $pass,
            'controlled_output_path' => $controlledLoad['path'],
            'controlled_output_hash' => $controlledLoad['actual_hash'],
            'controlled_output_file_sha1' => $controlledLoad['actual_file_sha1'],
            'controlled_output_record_count' => $this->controlledOutputRecordCount($controlledOutput),
            'generation_mode' => $controlledOutput['generation_mode'] ?? null,
            'publication_state' => $controlledOutput['publication_state'] ?? null,
            'controlled_output_stable_for_operator_go_no_go_review' => $pass,
        ];
    }

    private function controlledOutputIntegritySummary(array $c154, array $controlledOutput, array $controlledLoad): array
    {
        return [
            'validation_completed' => true,
            'controlled_output_integrity_valid' => $this->controlledOutputIntegrityValid($c154, $controlledOutput, $controlledLoad),
            'c154_controlled_output_hash' => $c154['controlled_output_hash'] ?? null,
            'actual_controlled_output_hash' => $controlledLoad['actual_hash'],
            'c154_controlled_output_file_sha1' => $c154['controlled_output_file_sha1'] ?? null,
            'actual_controlled_output_file_sha1' => $controlledLoad['actual_file_sha1'],
            'c154_record_count' => $c154['controlled_output_record_count'] ?? null,
            'actual_record_count' => $this->controlledOutputRecordCount($controlledOutput),
            'candidate_scope_match' => $this->candidateScopeMatches($c154, $controlledOutput),
        ];
    }

    private function controlledOutputPublicationGuardSummary(array $c154, array $controlledOutput): array
    {
        return [
            'guard_reviewed' => true,
            'official_output_generated_for_controlled_review' => (bool) ($c154['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => (bool) (($c154['weekly_swing_watchlist_official_output_published'] ?? false) || ($controlledOutput['weekly_swing_watchlist_official_output_published'] ?? false)),
            'publication_allowed' => (bool) (($c154['weekly_swing_watchlist_publication_allowed'] ?? false) || ($controlledOutput['weekly_swing_watchlist_publication_allowed'] ?? false)),
            'unrestricted_publication_allowed' => (bool) (($c154['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? false) || ($controlledOutput['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? false)),
            'plan_confirm_mutation_allowed' => (bool) ($c154['plan_confirm_mutation_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) (($c154['plan_confirm_mutated'] ?? false) || ($controlledOutput['plan_confirm_mutated'] ?? false)),
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($c154, $controlledOutput),
        ];
    }

    private function candidateScorecard(array $controlledOutput, bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c155_role' => 'primary_candidate_controlled_output_result_reviewed',
                'controlled_output_result_reviewed' => $pass,
                'published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c155_role' => 'backup_standby_candidate_controlled_output_result_reviewed',
                'controlled_output_result_reviewed' => $pass,
                'published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c155_role' => 'comparator_only_candidate',
                'controlled_output_result_reviewed' => false,
                'published' => false,
                'a01_remains_comparator_only' => (bool) ($controlledOutput['comparator_candidate']['a01_remains_comparator_only'] ?? false),
            ],
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'approval_valid' => $pass,
        ];
    }

    private function resultReviewConfirmationSummary(array $options, bool $pass): array
    {
        return [
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'no_publication_confirmed' => (bool) ($options['no_publication_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'result_review_confirmation_valid' => $pass,
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

    private function documentationHygieneGuardSummary(array $c154Load, array $controlledLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c154_convert_from_json_pass' => $c154Load['convert_from_json_pass'],
            'controlled_output_convert_from_json_pass' => $controlledLoad['convert_from_json_pass'],
            'c154_top_level_case_insensitive_duplicate_keys' => $c154Load['case_insensitive_duplicate_keys'],
            'controlled_output_top_level_case_insensitive_duplicate_keys' => $controlledLoad['case_insensitive_duplicate_keys'],
            'c154_artifact_not_modified' => true,
            'controlled_output_artifact_not_modified' => true,
            'c155_is_result_review_not_publication' => true,
            'c155_is_not_plan_confirm_mutation' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-43_C155_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW',
            'c154_controlled_output_generation_execution_carried_forward' => true,
            'controlled_output_generation_result_reviewed' => $pass,
            'controlled_output_stable_for_operator_go_no_go_review' => $pass,
            'official_weekly_swing_output_published' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C156_RECOMMENDATION : 'C155_TARGETED_C154_OR_CONTROLLED_OUTPUT_REPAIR',
            'planned_next_scope' => $pass ? 'controlled output-generation operator go/no-go review only; no publication, unrestricted publication, or PLAN/CONFIRM mutation from C155' : 'targeted C154 lock, controlled output artifact lock, integrity, confirmation, or cleanup repair',
            'planned_next_required_inputs' => $pass ? [
                'locked C155 artifact hash',
                'locked C155 file SHA1',
                'publication still disabled',
                'PLAN/CONFIRM unchanged',
                'operator go/no-go approval decision',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C155 validates C154 artifact_hash and file SHA1 locks.',
            'C155 validates the controlled output artifact hash and file SHA1 locks.',
            'C155 confirms the controlled output rows match the primary and backup candidates.',
            'C155 does not publish output or allow unrestricted publication.',
            'C155 does not mutate PLAN/CONFIRM.',
            'C155 keeps A01 comparator-only.',
            'C155 may only recommend controlled output-generation operator go/no-go review next.',
        ];
    }

    private function sourceArtifactLocks(array $c154Load, array $controlledLoad): array
    {
        return [
            'c154' => [
                'artifact_path' => $c154Load['path'],
                'expected_artifact_hash' => $c154Load['expected_hash'],
                'actual_artifact_hash' => $c154Load['actual_hash'],
                'artifact_hash_match' => $c154Load['hash_match'],
                'expected_file_sha1' => $c154Load['expected_file_sha1'],
                'actual_file_sha1' => $c154Load['actual_file_sha1'],
                'file_sha1_match' => $c154Load['file_sha1_match'],
                'convert_from_json_pass' => $c154Load['convert_from_json_pass'],
            ],
            'controlled_output' => [
                'artifact_path' => $controlledLoad['path'],
                'expected_controlled_output_hash' => $controlledLoad['expected_hash'],
                'actual_controlled_output_hash' => $controlledLoad['actual_hash'],
                'controlled_output_hash_match' => $controlledLoad['hash_match'],
                'expected_file_sha1' => $controlledLoad['expected_file_sha1'],
                'actual_file_sha1' => $controlledLoad['actual_file_sha1'],
                'file_sha1_match' => $controlledLoad['file_sha1_match'],
                'convert_from_json_pass' => $controlledLoad['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $c154Load, array $controlledLoad): array
    {
        return [
            'expected_c154_hash' => $c154Load['expected_hash'],
            'actual_c154_hash' => $c154Load['actual_hash'],
            'c154_hash_match' => $c154Load['hash_match'],
            'expected_c154_file_sha1' => $c154Load['expected_file_sha1'],
            'actual_c154_file_sha1' => $c154Load['actual_file_sha1'],
            'c154_file_sha1_match' => $c154Load['file_sha1_match'],
            'c154_convert_from_json_pass' => $c154Load['convert_from_json_pass'],
            'expected_controlled_output_hash' => $controlledLoad['expected_hash'],
            'actual_controlled_output_hash' => $controlledLoad['actual_hash'],
            'controlled_output_hash_match' => $controlledLoad['hash_match'],
            'expected_controlled_output_file_sha1' => $controlledLoad['expected_file_sha1'],
            'actual_controlled_output_file_sha1' => $controlledLoad['actual_file_sha1'],
            'controlled_output_file_sha1_match' => $controlledLoad['file_sha1_match'],
            'controlled_output_convert_from_json_pass' => $controlledLoad['convert_from_json_pass'],
        ];
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1, string $hashField): array
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
                $actualHash = $decoded[$hashField] ?? null;
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
