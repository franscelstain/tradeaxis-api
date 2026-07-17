<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewService
{
    public const RUN_CODE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-48 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW';

    public const DEFAULT_C158_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution.json';
    public const DEFAULT_EXPECTED_C158_EXECUTION_HASH = 'fec3b624eb3e912b1302165b1def8fe0a4669a87';
    public const DEFAULT_EXPECTED_C158_EXECUTION_FILE_SHA1 = '242830E193C2D54A4C7A233A68D04F90412AEE7D';
    public const DEFAULT_CONTROLLED_PUBLICATION_ARTIFACT = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    public const DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH = 'df064c7290ff4c3bfd0c7a8412d39299049c01d5';
    public const DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1 = 'D87AB8CD1564BE8B266B8A68011470272D49EE60';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C158_EXECUTION_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PASSED_CONTROLLED_PUBLICATION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C158_EXECUTION_PHASE_LABEL = 'PR-47 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION';
    private const EXPECTED_C158_EXECUTION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C158_OPERATOR_RECOMMENDATION = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const RESULT_REVIEW_CONFIRMATION_MISSING_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C158_EXECUTION_LOCK_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_ARTIFACT_LOCK_MISMATCH';
    private const C158_EXECUTION_FILE_SHA1_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_FILE_SHA1_LOCK_MISMATCH';
    private const C158_EXECUTION_CONVERT_FROM_JSON_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C158_EXECUTION_STATUS_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_STATUS_MISMATCH';
    private const C158_EXECUTION_PHASE_LABEL_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_PHASE_LABEL_MISMATCH';
    private const C158_EXECUTION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_NEXT_RECOMMENDATION_MISMATCH';
    private const C158_EXECUTION_INCOMPLETE_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_INCOMPLETE';
    private const CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH';
    private const CONTROLLED_PUBLICATION_FILE_SHA1_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FILE_SHA1_LOCK_MISMATCH';
    private const CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CONTROLLED_PUBLICATION_INTEGRITY_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C158_EXECUTION_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_pass',
        'production_live_runtime_controlled_output_publication_execution_pass',
        'ready_for_weekly_swing_watchlist_controlled_output_publication_result_review',
        'production_live_runtime_controlled_output_publication_result_review_allowed_next',
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
        'c158_boundary_lock_valid',
        'c158_publication_boundary_valid',
        'c158_boundary_convert_from_json_pass',
        'controlled_output_lock_valid',
        'controlled_output_convert_from_json_pass',
        'controlled_output_integrity_valid',
        'operator_approved',
        'controlled_publication_execution_confirmed',
        'controlled_publication_only_confirmed',
        'plan_confirm_unchanged_confirmed',
        'primary_candidate_controlled_published',
        'backup_candidate_controlled_published',
        'a01_remains_comparator_only',
        'c158_controlled_output_publication_execution_only',
        'c158_controlled_publication_only',
        'c158_not_free_publication',
        'c158_not_unrestricted_publication',
        'c158_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C158_EXECUTION_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_controlled_published',
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
        string $c158ExecutionArtifact = self::DEFAULT_C158_EXECUTION_ARTIFACT,
        string $expectedC158ExecutionHash = self::DEFAULT_EXPECTED_C158_EXECUTION_HASH,
        string $expectedC158ExecutionFileSha1 = self::DEFAULT_EXPECTED_C158_EXECUTION_FILE_SHA1,
        string $controlledPublicationArtifact = self::DEFAULT_CONTROLLED_PUBLICATION_ARTIFACT,
        string $expectedControlledPublicationHash = self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH,
        string $expectedControlledPublicationFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')), $controlledPublicationArtifact);
        $executionLoad = $this->loadJsonLock($c158ExecutionArtifact, $expectedC158ExecutionHash, $expectedC158ExecutionFileSha1, 'artifact_hash');
        $publicationLoad = $this->loadJsonLock($controlledPublicationArtifact, $expectedControlledPublicationHash, $expectedControlledPublicationFileSha1, 'controlled_publication_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($executionLoad, $publicationLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($executionLoad, $publicationLoad));

        if (! $executionLoad['exists'] || ! is_array($executionLoad['payload'])) {
            return $this->blocked($artifact, self::C158_EXECUTION_LOCK_MISMATCH_STATUS, 'C158 execution artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $executionLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false);
            $artifact['c158_execution_convert_from_json_duplicate_keys'] = $executionLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C158_EXECUTION_CONVERT_FROM_JSON_STATUS, 'C158 execution artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $executionLoad['hash_match']) {
            return $this->blocked($artifact, self::C158_EXECUTION_LOCK_MISMATCH_STATUS, 'C158 execution artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $executionLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::C158_EXECUTION_FILE_SHA1_MISMATCH_STATUS, 'C158 execution file SHA1 mismatch.', $outputPath, $overwrite);
        }

        if (! $publicationLoad['exists'] || ! is_array($publicationLoad['payload'])) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS, 'Controlled publication artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false);
            $artifact['controlled_publication_convert_from_json_duplicate_keys'] = $publicationLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_STATUS, 'Controlled publication artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['hash_match']) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS, 'Controlled publication artifact hash mismatch.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['file_sha1_match']) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_FILE_SHA1_MISMATCH_STATUS, 'Controlled publication file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $execution = $executionLoad['payload'];
        $publication = $publicationLoad['payload'];
        if (($execution['status'] ?? null) !== self::EXPECTED_C158_EXECUTION_STATUS || ($execution['reason_code'] ?? null) !== self::EXPECTED_C158_EXECUTION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::C158_EXECUTION_STATUS_MISMATCH_STATUS, 'C158 execution status/reason is not result-review ready.', $outputPath, $overwrite);
        }
        if (($execution['phase_label'] ?? null) !== self::EXPECTED_C158_EXECUTION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::C158_EXECUTION_PHASE_LABEL_MISMATCH_STATUS, 'C158 execution phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c158ExecutionNextRecommendationMatches($execution)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::C158_EXECUTION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C158 execution next recommendation is not C158 result review.', $outputPath, $overwrite);
        }
        if (! $this->freePublicationAndPlanGuardClean($execution, $publication)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C158 execution or controlled publication has free publication, unrestricted publication, or PLAN/CONFIRM mutation already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c158ExecutionComplete($execution)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::C158_EXECUTION_INCOMPLETE_STATUS, 'C158 execution evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($execution, $publication)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C158 controlled publication candidate scope does not match the locked execution scope.', $outputPath, $overwrite);
        }
        if (! $this->controlledPublicationIntegrityValid($execution, $publication, $publicationLoad)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_INTEGRITY_STATUS, 'Controlled publication artifact does not match C158 execution manifest.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::APPROVAL_MISSING_STATUS, 'C158 result review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['result_review_confirmed'] ?? false)
            || ! (bool) ($options['controlled_publication_result_confirmed'] ?? false)
            || ! (bool) ($options['controlled_publication_only_confirmed'] ?? false)
            || ! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)
        ) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $publicationLoad, $options, false), self::RESULT_REVIEW_CONFIRMATION_MISSING_STATUS, 'C158 result review requires result-review, controlled-publication-result, controlled-only, and PLAN/CONFIRM unchanged confirmations.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $executionLoad, $publicationLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $executionLoad, $publicationLoad, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C158 reviews the controlled publication result. The controlled publication artifact is valid for operator go/no-go review; free publication remains locked, unrestricted publication remains disabled, and PLAN/CONFIRM is unchanged.';
        $artifact['diagnostic_conclusion'] = 'C158_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_PASSED_CONTROLLED_PUBLICATION_VALID_NOT_FREE_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C158_OPERATOR_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($publicationLoad));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $controlledPublicationArtifact): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-48',
            'internal_checkpoint' => 'C158',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'RESULT_REVIEW',
            'status' => 'C158_RESULT_REVIEW_NOT_RUN',
            'reason_code' => 'C158_RESULT_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_publication_path' => $controlledPublicationArtifact,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_pass' => false,
            'production_live_runtime_controlled_output_publication_result_review_pass' => false,
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => false,
            'weekly_swing_watchlist_controlled_output_publication_result_review_manifest_created' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_review' => false,
            'production_live_runtime_controlled_output_publication_operator_go_no_go_review_allowed_next' => false,
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
            'c158_execution_lock_valid' => false,
            'c158_publication_execution_valid' => false,
            'c158_execution_convert_from_json_pass' => false,
            'controlled_publication_lock_valid' => false,
            'controlled_publication_convert_from_json_pass' => false,
            'controlled_publication_integrity_valid' => false,
            'operator_approved' => false,
            'approval_reference' => '',
            'result_review_confirmed' => false,
            'controlled_publication_result_confirmed' => false,
            'controlled_publication_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'primary_candidate_controlled_publication_result_reviewed' => false,
            'backup_candidate_controlled_publication_result_reviewed' => false,
            'comparator_candidate_controlled_publication_result_reviewed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c158_controlled_output_publication_result_review_only' => true,
            'c158_controlled_publication_only' => true,
            'c158_not_free_publication' => true,
            'c158_not_unrestricted_publication' => true,
            'c158_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C158_RESULT_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function passingTopLevelState(array $publicationLoad): array
    {
        $publication = is_array($publicationLoad['payload'] ?? null) ? $publicationLoad['payload'] : [];

        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_pass' => true,
            'production_live_runtime_controlled_output_publication_result_review_pass' => true,
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => true,
            'weekly_swing_watchlist_controlled_output_publication_result_review_manifest_created' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_review' => true,
            'production_live_runtime_controlled_output_publication_operator_go_no_go_review_allowed_next' => true,
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
            'c158_execution_lock_valid' => true,
            'c158_publication_execution_valid' => true,
            'c158_execution_convert_from_json_pass' => true,
            'controlled_publication_lock_valid' => true,
            'controlled_publication_convert_from_json_pass' => true,
            'controlled_publication_integrity_valid' => true,
            'controlled_publication_hash' => $publicationLoad['actual_hash'],
            'controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'],
            'controlled_publication_record_count' => $this->controlledPublicationRecordCount($publication),
            'primary_candidate_controlled_publication_result_reviewed' => true,
            'backup_candidate_controlled_publication_result_reviewed' => true,
            'comparator_candidate_controlled_publication_result_reviewed' => false,
            'a01_remains_comparator_only' => true,
            'c158_controlled_output_publication_result_review_only' => true,
            'c158_controlled_publication_only' => true,
            'c158_not_free_publication' => true,
            'c158_not_unrestricted_publication' => true,
            'c158_not_plan_confirm_mutation' => true,
        ];
    }

    private function completeSections(array $artifact, array $executionLoad, array $publicationLoad, array $options, bool $pass): array
    {
        $execution = is_array($executionLoad['payload'] ?? null) ? $executionLoad['payload'] : [];
        $publication = is_array($publicationLoad['payload'] ?? null) ? $publicationLoad['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact = array_merge($artifact, $this->topLevelState($execution, $publication, $publicationLoad, $options, $pass));
        $artifact['c158_execution_lock_valid'] = $executionLoad['hash_match'] && $executionLoad['file_sha1_match'] && $executionLoad['convert_from_json_pass'];
        $artifact['c158_execution_convert_from_json_pass'] = $executionLoad['convert_from_json_pass'];
        $artifact['controlled_publication_lock_valid'] = $publicationLoad['hash_match'] && $publicationLoad['file_sha1_match'] && $publicationLoad['convert_from_json_pass'];
        $artifact['controlled_publication_convert_from_json_pass'] = $publicationLoad['convert_from_json_pass'];
        $artifact['controlled_publication_hash'] = $publicationLoad['actual_hash'];
        $artifact['controlled_publication_file_sha1'] = $publicationLoad['actual_file_sha1'];
        $artifact['controlled_publication_record_count'] = $this->controlledPublicationRecordCount($publication);
        $artifact['c158_execution_lock_validation_summary'] = $this->executionLockValidationSummary($executionLoad, $execution);
        $artifact['controlled_publication_lock_validation_summary'] = $this->controlledPublicationLockValidationSummary($publicationLoad, $publication);
        $artifact['c158_execution_carry_forward_summary'] = $this->executionCarryForwardSummary($execution, $pass);
        $artifact['controlled_publication_result_review_summary'] = $this->controlledPublicationResultReviewSummary($publication, $publicationLoad, $pass);
        $artifact['controlled_publication_integrity_summary'] = $this->controlledPublicationIntegritySummary($execution, $publication, $publicationLoad);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($execution, $publication);
        $artifact['candidate_controlled_publication_result_scorecard'] = $this->candidateScorecard($publication, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['result_review_confirmation_summary'] = $this->resultReviewConfirmationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($executionLoad, $publicationLoad);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C158_RESULT_REVIEW_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');
        $artifact['result_review_confirmed'] = (bool) ($options['result_review_confirmed'] ?? false);
        $artifact['controlled_publication_result_confirmed'] = (bool) ($options['controlled_publication_result_confirmed'] ?? false);
        $artifact['controlled_publication_only_confirmed'] = (bool) ($options['controlled_publication_only_confirmed'] ?? false);
        $artifact['plan_confirm_unchanged_confirmed'] = (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false);
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($pass) {
            $artifact = array_merge($artifact, $this->passingTopLevelState($publicationLoad));
        }

        return $artifact;
    }

    private function topLevelState(array $execution, array $publication, array $publicationLoad, array $options, bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_output_generation_executed' => (bool) ($execution['weekly_swing_watchlist_controlled_output_generation_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed' => (bool) ($execution['weekly_swing_watchlist_controlled_output_generation_result_reviewed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($execution['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generated' => (bool) ($execution['weekly_swing_watchlist_live_recommendation_generated'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_executed' => (bool) ($execution['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_published' => (bool) ($publication['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'weekly_swing_watchlist_controlled_publication_artifact_created' => (bool) ($execution['weekly_swing_watchlist_controlled_publication_artifact_created'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => $pass,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($execution['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($execution['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($execution['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($execution['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c158_publication_execution_valid' => $this->c158ExecutionComplete($execution),
            'controlled_publication_integrity_valid' => $this->controlledPublicationIntegrityValid($execution, $publication, $publicationLoad),
            'primary_candidate_controlled_publication_result_reviewed' => $pass,
            'backup_candidate_controlled_publication_result_reviewed' => $pass,
            'comparator_candidate_controlled_publication_result_reviewed' => false,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'controlled_publication_result_confirmed' => (bool) ($options['controlled_publication_result_confirmed'] ?? false),
            'controlled_publication_only_confirmed' => (bool) ($options['controlled_publication_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
        ];
    }

    private function c158ExecutionComplete(array $execution): bool
    {
        foreach (self::REQUIRED_C158_EXECUTION_TRUE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C158_EXECUTION_FALSE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($execution['topic_code'] ?? null) !== 'C158_CONTROLLED_OUTPUT_PUBLICATION' || ($execution['topic_stage'] ?? null) !== 'EXECUTION') {
            return false;
        }

        return true;
    }

    private function controlledPublicationIntegrityValid(array $execution, array $publication, array $publicationLoad): bool
    {
        if (($publication['controlled_publication_type'] ?? null) !== 'weekly_swing_watchlist_controlled_output_publication') {
            return false;
        }
        if (($publication['run_code'] ?? null) !== 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION') {
            return false;
        }
        if (($publication['phase_label'] ?? null) !== self::EXPECTED_C158_EXECUTION_PHASE_LABEL) {
            return false;
        }
        if (($publication['topic_code'] ?? null) !== 'C158_CONTROLLED_OUTPUT_PUBLICATION' || ($publication['topic_stage'] ?? null) !== 'EXECUTION') {
            return false;
        }
        if (($publication['controlled_publication_hash'] ?? null) !== ($publicationLoad['actual_hash'] ?? null)) {
            return false;
        }
        if (($execution['controlled_publication_hash'] ?? null) !== ($publicationLoad['actual_hash'] ?? null)) {
            return false;
        }
        if (($execution['controlled_publication_file_sha1'] ?? null) !== ($publicationLoad['actual_file_sha1'] ?? null)) {
            return false;
        }
        if (($execution['controlled_publication_record_count'] ?? null) !== 2 || $this->controlledPublicationRecordCount($publication) !== 2) {
            return false;
        }
        if (($publication['publication_mode'] ?? null) !== 'controlled') {
            return false;
        }
        if (($publication['publication_state'] ?? null) !== 'controlled_published') {
            return false;
        }
        if (($publication['public_release_state'] ?? null) !== 'not_unrestricted') {
            return false;
        }
        if (($publication['weekly_swing_watchlist_controlled_output_publication_executed'] ?? null) !== true) {
            return false;
        }
        if (($publication['weekly_swing_watchlist_controlled_output_published'] ?? null) !== true) {
            return false;
        }
        if (($publication['weekly_swing_watchlist_controlled_publication_allowed'] ?? null) !== true) {
            return false;
        }

        return $this->freePublicationAndPlanGuardClean($execution, $publication) && $this->candidateScopeMatches($execution, $publication);
    }

    private function freePublicationAndPlanGuardClean(array $execution, array $publication): bool
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
            if (($execution[$field] ?? null) !== false) {
                return false;
            }
        }
        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutated',
        ] as $field) {
            if (($publication[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($publication['publication_mode'] ?? null) === 'controlled'
            && ($publication['publication_state'] ?? null) === 'controlled_published'
            && ($publication['public_release_state'] ?? null) === 'not_unrestricted';
    }

    private function candidateScopeMatches(array $execution, array $publication): bool
    {
        if (($execution['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return false;
        }
        if (($execution['backup_candidate_code'] ?? null) !== self::BACKUP_CANDIDATE) {
            return false;
        }
        if (($execution['comparator_candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }
        if (($execution['a01_remains_comparator_only'] ?? null) !== true || ($execution['a01_promoted'] ?? false) !== false) {
            return false;
        }
        foreach ([
            'candidate_promotion_executed',
            'strategy_retune_executed',
            'scoring_mutation_executed',
            'catalog_selection_changed',
            'runtime_selection_changed',
        ] as $field) {
            if (($execution[$field] ?? false) !== false) {
                return false;
            }
        }

        $rows = is_array($publication['output_rows'] ?? null) ? array_values($publication['output_rows']) : [];
        if (count($rows) !== 2) {
            return false;
        }
        $expectedRows = [
            [1, self::PRIMARY_CANDIDATE, 'controlled_publication_execution', 'controlled_published', 'controlled_only'],
            [2, self::BACKUP_CANDIDATE, 'controlled_publication_execution', 'controlled_published', 'controlled_only'],
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
            if (($row['publication_scope'] ?? null) !== $expected[4]) {
                return false;
            }
        }

        $comparator = is_array($publication['comparator_candidate'] ?? null) ? $publication['comparator_candidate'] : [];
        if (($comparator['candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }

        return ($comparator['a01_remains_comparator_only'] ?? null) === true
            && ($comparator['candidate_role'] ?? null) === 'comparator_only_not_controlled_published';
    }

    private function c158ExecutionNextRecommendationMatches(array $execution): bool
    {
        foreach ([['next_step_recommendation'], ['planned_next_summary', 'planned_next_review']] as $path) {
            if ($this->valueAt($execution, $path) !== self::EXPECTED_C158_EXECUTION_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function controlledPublicationRecordCount(array $publication): int
    {
        return is_array($publication['output_rows'] ?? null) ? count($publication['output_rows']) : 0;
    }

    private function executionLockValidationSummary(array $load, array $execution): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C158_EXECUTION',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C158_EXECUTION_STATUS,
            'actual_status' => $execution['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C158_EXECUTION_PHASE_LABEL,
            'actual_phase_label' => $execution['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C158_EXECUTION_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c158ExecutionNextRecommendationMatches($execution),
            'c158_execution_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function controlledPublicationLockValidationSummary(array $load, array $publication): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'CONTROLLED_PUBLICATION',
            'artifact_path' => $load['path'],
            'expected_controlled_publication_hash' => $load['expected_hash'],
            'actual_controlled_publication_hash' => $load['actual_hash'],
            'controlled_publication_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'publication_mode' => $publication['publication_mode'] ?? null,
            'publication_state' => $publication['publication_state'] ?? null,
            'public_release_state' => $publication['public_release_state'] ?? null,
            'controlled_publication_record_count' => $this->controlledPublicationRecordCount($publication),
            'controlled_publication_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function executionCarryForwardSummary(array $execution, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c158_execution_valid' => $this->c158ExecutionComplete($execution),
            'topic_code' => (string) ($execution['topic_code'] ?? ''),
            'topic_stage' => (string) ($execution['topic_stage'] ?? ''),
            'controlled_publication_executed' => (bool) ($execution['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'controlled_publication_artifact_created' => (bool) ($execution['weekly_swing_watchlist_controlled_publication_artifact_created'] ?? false),
            'controlled_publication_hash' => $execution['controlled_publication_hash'] ?? null,
            'controlled_publication_file_sha1' => $execution['controlled_publication_file_sha1'] ?? null,
            'controlled_publication_record_count' => $execution['controlled_publication_record_count'] ?? null,
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'c158_result_review_pass' => $pass,
        ];
    }

    private function controlledPublicationResultReviewSummary(array $publication, array $publicationLoad, bool $pass): array
    {
        return [
            'result_review_executed' => $pass,
            'controlled_publication_path' => $publicationLoad['path'],
            'controlled_publication_hash' => $publicationLoad['actual_hash'],
            'controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'],
            'controlled_publication_record_count' => $this->controlledPublicationRecordCount($publication),
            'publication_mode' => $publication['publication_mode'] ?? null,
            'publication_state' => $publication['publication_state'] ?? null,
            'public_release_state' => $publication['public_release_state'] ?? null,
            'controlled_publication_stable_for_operator_go_no_go_review' => $pass,
        ];
    }

    private function controlledPublicationIntegritySummary(array $execution, array $publication, array $publicationLoad): array
    {
        return [
            'validation_completed' => true,
            'controlled_publication_integrity_valid' => $this->controlledPublicationIntegrityValid($execution, $publication, $publicationLoad),
            'execution_controlled_publication_hash' => $execution['controlled_publication_hash'] ?? null,
            'actual_controlled_publication_hash' => $publicationLoad['actual_hash'] ?? null,
            'execution_controlled_publication_file_sha1' => $execution['controlled_publication_file_sha1'] ?? null,
            'actual_controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'] ?? null,
            'execution_record_count' => $execution['controlled_publication_record_count'] ?? null,
            'actual_record_count' => $this->controlledPublicationRecordCount($publication),
            'candidate_scope_match' => $this->candidateScopeMatches($execution, $publication),
            'controlled_publication_only' => ($publication['publication_mode'] ?? null) === 'controlled',
            'public_release_state' => $publication['public_release_state'] ?? null,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $execution, array $publication): array
    {
        return [
            'validation_completed' => true,
            'free_publication_and_plan_guard_clean' => $this->freePublicationAndPlanGuardClean($execution, $publication),
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

    private function candidateScorecard(array $publication, bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c158_role' => 'primary_candidate_controlled_publication_result_reviewed',
                'controlled_publication_result_reviewed' => $pass,
                'controlled_published' => $pass,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c158_role' => 'backup_candidate_controlled_publication_result_reviewed',
                'controlled_publication_result_reviewed' => $pass,
                'controlled_published' => $pass,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c158_role' => 'comparator_only_candidate',
                'controlled_publication_result_reviewed' => false,
                'controlled_published' => false,
                'free_published' => false,
                'a01_remains_comparator_only' => (bool) ($publication['comparator_candidate']['a01_remains_comparator_only'] ?? false),
            ],
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'approval_valid' => $pass,
        ];
    }

    private function resultReviewConfirmationSummary(array $options, bool $pass): array
    {
        return [
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'controlled_publication_result_confirmed' => (bool) ($options['controlled_publication_result_confirmed'] ?? false),
            'controlled_publication_only_confirmed' => (bool) ($options['controlled_publication_only_confirmed'] ?? false),
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

    private function documentationHygieneGuardSummary(array $executionLoad, array $publicationLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c158_execution_convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            'controlled_publication_convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => array_values(array_unique(array_merge($executionLoad['case_insensitive_duplicate_keys'], $publicationLoad['case_insensitive_duplicate_keys']))),
            'c158_execution_artifact_not_modified' => true,
            'controlled_publication_artifact_not_modified' => true,
            'c158_result_review_is_review_only_not_free_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-48_C158_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW',
            'topic_code' => 'C158_CONTROLLED_OUTPUT_PUBLICATION',
            'topic_stage' => 'RESULT_REVIEW',
            'c158_execution_carried_forward' => true,
            'controlled_publication_result_review_pass' => $pass,
            'controlled_publication_stable_for_operator_go_no_go_review' => $pass,
            'same_topic_number_for_next_stage' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C158_OPERATOR_RECOMMENDATION : 'C158_TARGETED_EXECUTION_OR_CONTROLLED_PUBLICATION_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C158 controlled output publication operator go/no-go review only; still no unrestricted publication or PLAN/CONFIRM mutation from result review' : 'targeted execution lock, controlled publication lock, result confirmation, publication guard, or cleanup repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass ? [
                'locked C158 publication result review artifact hash',
                'locked C158 publication result review file SHA1',
                'controlled publication artifact remains locked',
                'free publication still disabled',
                'PLAN/CONFIRM unchanged',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C158 result review validates the C158 execution artifact hash and file SHA1 before reviewing results.',
            'C158 result review validates the controlled publication artifact hash and file SHA1 before recommending operator go/no-go review.',
            'C158 result review reviews controlled publication evidence only.',
            'C158 result review does not free-publish output, allow unrestricted publication, or mutate PLAN/CONFIRM.',
            'C158 result review keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C158 result review may only recommend same-topic publication operator go/no-go review next.',
        ];
    }

    private function sourceArtifactLocks(array $executionLoad, array $publicationLoad): array
    {
        return [
            'c158_execution' => [
                'artifact_path' => $executionLoad['path'],
                'expected_artifact_hash' => $executionLoad['expected_hash'],
                'actual_artifact_hash' => $executionLoad['actual_hash'],
                'artifact_hash_match' => $executionLoad['hash_match'],
                'expected_file_sha1' => $executionLoad['expected_file_sha1'],
                'actual_file_sha1' => $executionLoad['actual_file_sha1'],
                'file_sha1_match' => $executionLoad['file_sha1_match'],
                'convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
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

    private function topLevelLockAliases(array $executionLoad, array $publicationLoad): array
    {
        return [
            'expected_c158_execution_hash' => $executionLoad['expected_hash'],
            'actual_c158_execution_hash' => $executionLoad['actual_hash'],
            'c158_execution_hash_match' => $executionLoad['hash_match'],
            'expected_c158_execution_file_sha1' => $executionLoad['expected_file_sha1'],
            'actual_c158_execution_file_sha1' => $executionLoad['actual_file_sha1'],
            'c158_execution_file_sha1_match' => $executionLoad['file_sha1_match'],
            'c158_execution_convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            'expected_controlled_publication_hash' => $publicationLoad['expected_hash'],
            'actual_controlled_publication_hash' => $publicationLoad['actual_hash'],
            'controlled_publication_hash_match' => $publicationLoad['hash_match'],
            'expected_controlled_publication_file_sha1' => $publicationLoad['expected_file_sha1'],
            'actual_controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'],
            'controlled_publication_file_sha1_match' => $publicationLoad['file_sha1_match'],
            'controlled_publication_convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
        ];
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1, string $hashKey): array
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
