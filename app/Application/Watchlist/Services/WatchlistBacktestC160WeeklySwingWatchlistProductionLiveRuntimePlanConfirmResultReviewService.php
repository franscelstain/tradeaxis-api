<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmResultReviewService
{
    public const RUN_CODE = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-57 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW';

    public const DEFAULT_C160_EXECUTION_ARTIFACT = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-execution.json';
    public const DEFAULT_EXPECTED_C160_EXECUTION_HASH = '8937d98bf09e440ab527b812051779a2eda8a89c';
    public const DEFAULT_EXPECTED_C160_EXECUTION_FILE_SHA1 = 'B7388BB99473BB12725AEE345E97C774E9D2618A';
    public const DEFAULT_CONTROLLED_PLAN_CONFIRM_ARTIFACT = 'storage/app/watchlist/output/c160-weekly-swing-watchlist-controlled-plan-confirm.json';
    public const DEFAULT_EXPECTED_CONTROLLED_PLAN_CONFIRM_HASH = '10164115c468c66c1d8cced1e29985698c66f056';
    public const DEFAULT_EXPECTED_CONTROLLED_PLAN_CONFIRM_FILE_SHA1 = 'A696DDD288CAAD469CA02B61D155EB4EE3A8F71B';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C160_EXECUTION_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_PASSED_CONTROLLED_PLAN_CONFIRM_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C160_EXECUTION_PHASE_LABEL = 'PR-56 / C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION';
    private const EXPECTED_C160_EXECUTION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C160_OPERATOR_GO_NO_GO_RECOMMENDATION = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_PASSED_READY_FOR_PLAN_CONFIRM_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const RESULT_REVIEW_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING';
    private const CONTROLLED_PLAN_CONFIRM_RESULT_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_RESULT_CONFIRMATION_MISSING';
    private const CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C160_EXECUTION_LOCK_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_ARTIFACT_LOCK_MISMATCH';
    private const C160_EXECUTION_FILE_SHA1_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_FILE_SHA1_LOCK_MISMATCH';
    private const C160_EXECUTION_CONVERT_FROM_JSON_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C160_EXECUTION_STATUS_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_STATUS_MISMATCH';
    private const C160_EXECUTION_PHASE_LABEL_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_PHASE_LABEL_MISMATCH';
    private const C160_EXECUTION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_NEXT_RECOMMENDATION_MISMATCH';
    private const C160_EXECUTION_INCOMPLETE_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_C160_EXECUTION_INCOMPLETE';
    private const CONTROLLED_PLAN_CONFIRM_LOCK_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_ARTIFACT_LOCK_MISMATCH';
    private const CONTROLLED_PLAN_CONFIRM_FILE_SHA1_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_FILE_SHA1_LOCK_MISMATCH';
    private const CONTROLLED_PLAN_CONFIRM_CONVERT_FROM_JSON_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CONTROLLED_PLAN_CONFIRM_INTEGRITY_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CONTROLLED_PLAN_CONFIRM_INTEGRITY_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C160_EXECUTION_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_pass',
        'production_live_runtime_plan_confirm_execution_pass',
        'ready_for_weekly_swing_watchlist_plan_confirm_result_review',
        'production_live_runtime_plan_confirm_result_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_result_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_controlled_execution_executed',
        'weekly_swing_watchlist_plan_confirm_controlled_artifact_created',
        'weekly_swing_watchlist_plan_confirm_controlled_only',
        'weekly_swing_watchlist_controlled_output_publication_observed',
        'weekly_swing_watchlist_controlled_output_publication_observation_stable',
        'weekly_swing_watchlist_controlled_output_publication_executed',
        'weekly_swing_watchlist_controlled_output_published',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'weekly_swing_watchlist_official_output_generated',
        'plan_confirm_execution_confirmed',
        'controlled_plan_confirm_only_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c160_boundary_lock_valid',
        'c160_plan_confirm_boundary_valid',
        'c160_boundary_convert_from_json_pass',
        'controlled_publication_lock_valid',
        'controlled_publication_integrity_valid',
        'controlled_publication_convert_from_json_pass',
        'operator_approved',
        'primary_candidate_plan_confirm_controlled_executed',
        'backup_candidate_plan_confirm_controlled_executed',
        'a01_remains_comparator_only',
        'c160_plan_confirm_execution_only',
        'c160_controlled_plan_confirm_only',
        'c160_not_plan_confirm_mutation',
        'c160_not_live_plan_confirm_rollout',
        'c160_not_publication',
        'c160_topic_number_retained_for_result_review',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C160_EXECUTION_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_plan_confirm_controlled_executed',
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
        'storage/app/watchlist/backtest/c160-*result-review*-test.json',
        'storage/app/watchlist/backtest/c160-*plan-confirm-result*-test.json',
        'storage/app/watchlist/backtest/c160-*negative-*-test.json',
        'storage/app/watchlist/backtest/c160-*missing-*-test.json',
        'storage/app/watchlist/backtest/c160-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c160-*invalid-*-test.json',
    ];

    public function execute(
        string $c160ExecutionArtifact = self::DEFAULT_C160_EXECUTION_ARTIFACT,
        string $expectedC160ExecutionHash = self::DEFAULT_EXPECTED_C160_EXECUTION_HASH,
        string $expectedC160ExecutionFileSha1 = self::DEFAULT_EXPECTED_C160_EXECUTION_FILE_SHA1,
        string $controlledPlanConfirmArtifact = self::DEFAULT_CONTROLLED_PLAN_CONFIRM_ARTIFACT,
        string $expectedControlledPlanConfirmHash = self::DEFAULT_EXPECTED_CONTROLLED_PLAN_CONFIRM_HASH,
        string $expectedControlledPlanConfirmFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_PLAN_CONFIRM_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')), $controlledPlanConfirmArtifact);
        $executionLoad = $this->loadJsonLock($c160ExecutionArtifact, $expectedC160ExecutionHash, $expectedC160ExecutionFileSha1, 'artifact_hash');
        $planConfirmLoad = $this->loadJsonLock($controlledPlanConfirmArtifact, $expectedControlledPlanConfirmHash, $expectedControlledPlanConfirmFileSha1, 'controlled_plan_confirm_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($executionLoad, $planConfirmLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($executionLoad, $planConfirmLoad));

        if (! $executionLoad['exists'] || ! is_array($executionLoad['payload'])) {
            return $this->blocked($artifact, self::C160_EXECUTION_LOCK_MISMATCH_STATUS, 'C160 execution artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $executionLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false);
            $artifact['c160_execution_convert_from_json_duplicate_keys'] = $executionLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C160_EXECUTION_CONVERT_FROM_JSON_STATUS, 'C160 execution artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $executionLoad['hash_match']) {
            return $this->blocked($artifact, self::C160_EXECUTION_LOCK_MISMATCH_STATUS, 'C160 execution artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $executionLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::C160_EXECUTION_FILE_SHA1_MISMATCH_STATUS, 'C160 execution file SHA1 mismatch.', $outputPath, $overwrite);
        }

        if (! $planConfirmLoad['exists'] || ! is_array($planConfirmLoad['payload'])) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::CONTROLLED_PLAN_CONFIRM_LOCK_MISMATCH_STATUS, 'Controlled PLAN/CONFIRM artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $planConfirmLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false);
            $artifact['controlled_plan_confirm_convert_from_json_duplicate_keys'] = $planConfirmLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::CONTROLLED_PLAN_CONFIRM_CONVERT_FROM_JSON_STATUS, 'Controlled PLAN/CONFIRM artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $planConfirmLoad['hash_match']) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::CONTROLLED_PLAN_CONFIRM_LOCK_MISMATCH_STATUS, 'Controlled PLAN/CONFIRM artifact hash mismatch.', $outputPath, $overwrite);
        }
        if (! $planConfirmLoad['file_sha1_match']) {
            return $this->blocked($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::CONTROLLED_PLAN_CONFIRM_FILE_SHA1_MISMATCH_STATUS, 'Controlled PLAN/CONFIRM file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $execution = $executionLoad['payload'];
        $planConfirm = $planConfirmLoad['payload'];
        if (($execution['status'] ?? null) !== self::EXPECTED_C160_EXECUTION_STATUS || ($execution['reason_code'] ?? null) !== self::EXPECTED_C160_EXECUTION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::C160_EXECUTION_STATUS_MISMATCH_STATUS, 'C160 execution status/reason is not result-review ready.', $outputPath, $overwrite);
        }
        if (($execution['phase_label'] ?? null) !== self::EXPECTED_C160_EXECUTION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::C160_EXECUTION_PHASE_LABEL_MISMATCH_STATUS, 'C160 execution phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c160ExecutionNextRecommendationMatches($execution)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::C160_EXECUTION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C160 execution next recommendation is not C160 result review.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($execution, $planConfirm)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C160 execution or controlled PLAN/CONFIRM already published, unlocked publication, mutated PLAN/CONFIRM, read activated catalog, or executed live rollout.', $outputPath, $overwrite);
        }
        if (! $this->c160ExecutionComplete($execution)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::C160_EXECUTION_INCOMPLETE_STATUS, 'C160 execution evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($execution, $planConfirm)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C160 controlled PLAN/CONFIRM candidate scope does not match locked execution scope.', $outputPath, $overwrite);
        }
        if (! $this->controlledPlanConfirmIntegrityValid($execution, $planConfirm, $planConfirmLoad)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::CONTROLLED_PLAN_CONFIRM_INTEGRITY_STATUS, 'Controlled PLAN/CONFIRM artifact does not match C160 execution manifest.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::APPROVAL_MISSING_STATUS, 'C160 result review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['result_review_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::RESULT_REVIEW_CONFIRMATION_MISSING_STATUS, 'C160 result review requires --result-review-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_plan_confirm_result_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::CONTROLLED_PLAN_CONFIRM_RESULT_CONFIRMATION_MISSING_STATUS, 'C160 result review requires --controlled-plan-confirm-result-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::CONTROLLED_PLAN_CONFIRM_ONLY_CONFIRMATION_MISSING_STATUS, 'C160 result review requires --controlled-plan-confirm-only-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C160 result review requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, false), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C160 result review requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $executionLoad, $planConfirmLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $executionLoad, $planConfirmLoad, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C160 reviews controlled PLAN/CONFIRM execution result. Controlled PLAN/CONFIRM evidence is valid for operator GO/NO-GO review; PLAN/CONFIRM remains unchanged, activated-catalog reads remain disabled, live rollout remains disabled, and free publication remains locked.';
        $artifact['diagnostic_conclusion'] = 'C160_PLAN_CONFIRM_RESULT_REVIEW_PASSED_CONTROLLED_EVIDENCE_VALID_PLAN_CONFIRM_UNCHANGED_NO_LIVE_ROLLOUT';
        $artifact['next_step_recommendation'] = self::C160_OPERATOR_GO_NO_GO_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($planConfirmLoad));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $controlledPlanConfirmArtifact): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-57',
            'internal_checkpoint' => 'C160',
            'topic_code' => 'C160_PLAN_CONFIRM',
            'topic_stage' => 'RESULT_REVIEW',
            'status' => 'C160_PLAN_CONFIRM_RESULT_REVIEW_NOT_RUN',
            'reason_code' => 'C160_PLAN_CONFIRM_RESULT_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_plan_confirm_path' => $controlledPlanConfirmArtifact,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_result_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_result_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_result_review_pass' => false,
            'production_live_runtime_plan_confirm_result_review_pass' => false,
            'weekly_swing_watchlist_plan_confirm_result_reviewed' => false,
            'weekly_swing_watchlist_plan_confirm_result_review_manifest_created' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_operator_go_no_go_review' => false,
            'production_live_runtime_plan_confirm_operator_go_no_go_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_operator_go_no_go_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_only' => true,
            'weekly_swing_watchlist_controlled_output_publication_executed' => false,
            'weekly_swing_watchlist_controlled_output_published' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
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
            'c160_execution_lock_valid' => false,
            'c160_plan_confirm_execution_valid' => false,
            'c160_execution_convert_from_json_pass' => false,
            'controlled_plan_confirm_lock_valid' => false,
            'controlled_plan_confirm_convert_from_json_pass' => false,
            'controlled_plan_confirm_integrity_valid' => false,
            'operator_approved' => false,
            'approval_reference' => '',
            'result_review_confirmed' => false,
            'controlled_plan_confirm_result_confirmed' => false,
            'controlled_plan_confirm_only_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'primary_candidate_plan_confirm_result_reviewed' => false,
            'backup_candidate_plan_confirm_result_reviewed' => false,
            'comparator_candidate_plan_confirm_result_reviewed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c160_plan_confirm_result_review_only' => true,
            'c160_controlled_plan_confirm_only' => true,
            'c160_not_plan_confirm_mutation' => true,
            'c160_not_live_plan_confirm_rollout' => true,
            'c160_not_publication' => true,
            'c160_topic_number_retained_for_operator_go_no_go' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C160_PLAN_CONFIRM_RESULT_REVIEW_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function passingTopLevelState(array $planConfirmLoad): array
    {
        $planConfirm = is_array($planConfirmLoad['payload']) ? $planConfirmLoad['payload'] : [];

        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_result_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_result_review_pass' => true,
            'production_live_runtime_plan_confirm_result_review_pass' => true,
            'weekly_swing_watchlist_plan_confirm_result_reviewed' => true,
            'weekly_swing_watchlist_plan_confirm_result_review_manifest_created' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_operator_go_no_go_review' => true,
            'production_live_runtime_plan_confirm_operator_go_no_go_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_operator_go_no_go_review_allowed_next' => true,
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
            'c160_execution_lock_valid' => true,
            'c160_plan_confirm_execution_valid' => true,
            'c160_execution_convert_from_json_pass' => true,
            'controlled_plan_confirm_lock_valid' => true,
            'controlled_plan_confirm_convert_from_json_pass' => true,
            'controlled_plan_confirm_integrity_valid' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'primary_candidate_plan_confirm_result_reviewed' => true,
            'backup_candidate_plan_confirm_result_reviewed' => true,
            'comparator_candidate_plan_confirm_result_reviewed' => false,
            'controlled_plan_confirm_path' => $planConfirmLoad['path'],
            'controlled_plan_confirm_hash' => $planConfirmLoad['actual_hash'],
            'controlled_plan_confirm_file_sha1' => $planConfirmLoad['actual_file_sha1'],
            'controlled_plan_confirm_record_count' => is_array($planConfirm['output_rows'] ?? null) ? count($planConfirm['output_rows']) : 0,
        ];
    }

    private function completeSections(array $artifact, array $executionLoad, array $planConfirmLoad, array $options, bool $pass): array
    {
        $execution = is_array($executionLoad['payload']) ? $executionLoad['payload'] : [];
        $planConfirm = is_array($planConfirmLoad['payload']) ? $planConfirmLoad['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($execution, $planConfirm, $options, $pass, $planConfirmLoad));
        $artifact['c160_execution_lock_validation_summary'] = $this->lockValidationSummary($executionLoad, 'C160_PLAN_CONFIRM_EXECUTION', self::EXPECTED_C160_EXECUTION_STATUS, self::EXPECTED_C160_EXECUTION_PHASE_LABEL, self::EXPECTED_C160_EXECUTION_NEXT_RECOMMENDATION);
        $artifact['controlled_plan_confirm_lock_validation_summary'] = $this->controlledPlanConfirmLockValidationSummary($planConfirmLoad);
        $artifact['c160_execution_carry_forward_summary'] = $this->executionCarryForwardSummary($execution);
        $artifact['controlled_plan_confirm_result_review_summary'] = $this->controlledPlanConfirmResultReviewSummary($planConfirmLoad, $pass);
        $artifact['controlled_plan_confirm_integrity_summary'] = $this->controlledPlanConfirmIntegritySummary($execution, $planConfirm, $planConfirmLoad);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($execution, $planConfirm);
        $artifact['candidate_plan_confirm_result_scorecard'] = $this->candidateScorecard($pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['result_review_confirmation_summary'] = $this->resultReviewConfirmationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($executionLoad, $planConfirmLoad);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();

        return $artifact;
    }

    private function topLevelState(array $execution, array $planConfirm, array $options, bool $pass, array $planConfirmLoad): array
    {
        return [
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created' => (bool) ($execution['weekly_swing_watchlist_plan_confirm_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_controlled_only' => true,
            'weekly_swing_watchlist_controlled_output_publication_executed' => (bool) ($execution['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_published' => (bool) ($execution['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($execution['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($execution['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
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
            'c160_plan_confirm_execution_valid' => $this->c160ExecutionComplete($execution),
            'controlled_plan_confirm_integrity_valid' => $this->controlledPlanConfirmIntegrityValid($execution, $planConfirm, $planConfirmLoad),
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'controlled_plan_confirm_result_confirmed' => (bool) ($options['controlled_plan_confirm_result_confirmed'] ?? false),
            'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'primary_candidate_plan_confirm_result_reviewed' => $pass,
            'backup_candidate_plan_confirm_result_reviewed' => $pass,
            'comparator_candidate_plan_confirm_result_reviewed' => false,
            'controlled_plan_confirm_path' => $planConfirmLoad['path'],
            'controlled_plan_confirm_hash' => $planConfirmLoad['actual_hash'],
            'controlled_plan_confirm_file_sha1' => $planConfirmLoad['actual_file_sha1'],
            'controlled_plan_confirm_record_count' => is_array($planConfirm['output_rows'] ?? null) ? count($planConfirm['output_rows']) : 0,
        ];
    }

    private function c160ExecutionNextRecommendationMatches(array $execution): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($execution, $path) !== self::EXPECTED_C160_EXECUTION_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function c160ExecutionComplete(array $execution): bool
    {
        foreach (self::REQUIRED_C160_EXECUTION_TRUE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C160_EXECUTION_FALSE_FIELDS as $field) {
            if (($execution[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($execution['topic_code'] ?? null) !== 'C160_PLAN_CONFIRM' || ($execution['topic_stage'] ?? null) !== 'EXECUTION') {
            return false;
        }

        return true;
    }

    private function controlledPlanConfirmReady(array $planConfirm): bool
    {
        return ($planConfirm['controlled_plan_confirm_type'] ?? null) === 'weekly_swing_watchlist_controlled_plan_confirm_execution'
            && ($planConfirm['run_code'] ?? null) === 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION'
            && ($planConfirm['topic_code'] ?? null) === 'C160_PLAN_CONFIRM'
            && ($planConfirm['topic_stage'] ?? null) === 'EXECUTION'
            && ($planConfirm['plan_confirm_mode'] ?? null) === 'controlled'
            && ($planConfirm['plan_confirm_state'] ?? null) === 'controlled_executed'
            && ($planConfirm['baseline_plan_confirm_state'] ?? null) === 'unchanged'
            && ($planConfirm['runtime_catalog_read_state'] ?? null) === 'not_activated_catalog'
            && ($planConfirm['live_rollout_state'] ?? null) === 'not_executed'
            && ($planConfirm['weekly_swing_watchlist_plan_confirm_controlled_execution_executed'] ?? null) === true
            && ($planConfirm['weekly_swing_watchlist_plan_confirm_controlled_artifact_created'] ?? null) === true
            && ($planConfirm['weekly_swing_watchlist_official_output_published'] ?? null) === false
            && ($planConfirm['weekly_swing_watchlist_publication_allowed'] ?? null) === false
            && ($planConfirm['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? null) === false
            && ($planConfirm['plan_confirm_mutation_allowed'] ?? null) === false
            && ($planConfirm['plan_confirm_mutated'] ?? null) === false
            && ($planConfirm['plan_confirm_runtime_reads_activated_catalog'] ?? null) === false
            && ($planConfirm['live_plan_confirm_rollout_allowed'] ?? null) === false
            && ($planConfirm['live_plan_confirm_rollout_executed'] ?? null) === false
            && is_array($planConfirm['output_rows'] ?? null)
            && count($planConfirm['output_rows']) === 2
            && (($planConfirm['output_rows'][0]['candidate_code'] ?? null) === self::PRIMARY_CANDIDATE)
            && (($planConfirm['output_rows'][1]['candidate_code'] ?? null) === self::BACKUP_CANDIDATE)
            && (($planConfirm['comparator_candidate']['candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE)
            && (($planConfirm['comparator_candidate']['a01_remains_comparator_only'] ?? null) === true);
    }

    private function controlledPlanConfirmIntegrityValid(array $execution, array $planConfirm, array $planConfirmLoad): bool
    {
        return $this->controlledPlanConfirmReady($planConfirm)
            && ($execution['controlled_plan_confirm_hash'] ?? null) === $planConfirmLoad['actual_hash']
            && ($execution['controlled_plan_confirm_file_sha1'] ?? null) === $planConfirmLoad['actual_file_sha1']
            && (int) ($execution['controlled_plan_confirm_record_count'] ?? 0) === 2
            && ($planConfirm['controlled_plan_confirm_hash'] ?? null) === $planConfirmLoad['actual_hash'];
    }

    private function publicationAndPlanGuardClean(array $execution, array $planConfirm): bool
    {
        foreach (self::SOURCE_FALSE_GUARDS as $field) {
            if (($execution[$field] ?? null) !== false || ($planConfirm[$field] ?? null) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $execution, array $planConfirm): bool
    {
        return ($execution['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($execution['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($execution['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($execution['primary_candidate_plan_confirm_controlled_executed'] ?? null) === true
            && ($execution['backup_candidate_plan_confirm_controlled_executed'] ?? null) === true
            && ($execution['comparator_candidate_plan_confirm_controlled_executed'] ?? null) === false
            && ($execution['a01_remains_comparator_only'] ?? null) === true
            && (($planConfirm['output_rows'][0]['candidate_code'] ?? null) === self::PRIMARY_CANDIDATE)
            && (($planConfirm['output_rows'][1]['candidate_code'] ?? null) === self::BACKUP_CANDIDATE)
            && (($planConfirm['comparator_candidate']['candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE)
            && (($planConfirm['comparator_candidate']['a01_remains_comparator_only'] ?? null) === true)
            && ($execution['a01_promoted'] ?? false) === false
            && ($execution['candidate_promotion_executed'] ?? false) === false
            && ($execution['candidate_rerank_executed'] ?? false) === false
            && ($execution['strategy_retune_executed'] ?? false) === false
            && ($execution['scoring_mutation_executed'] ?? false) === false
            && ($execution['catalog_selection_changed'] ?? false) === false
            && ($execution['runtime_selection_changed'] ?? false) === false;
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
            'next_recommendation_match' => is_array($load['payload']) && $this->c160ExecutionNextRecommendationMatches($load['payload']),
            'lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function controlledPlanConfirmLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'CONTROLLED_PLAN_CONFIRM',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'controlled_plan_confirm_ready' => is_array($load['payload']) && $this->controlledPlanConfirmReady($load['payload']),
        ];
    }

    private function executionCarryForwardSummary(array $execution): array
    {
        return [
            'validation_completed' => true,
            'c160_execution_valid' => $this->c160ExecutionComplete($execution),
            'topic_code' => (string) ($execution['topic_code'] ?? ''),
            'topic_stage' => (string) ($execution['topic_stage'] ?? ''),
            'ready_for_plan_confirm_result_review' => (bool) ($execution['ready_for_weekly_swing_watchlist_plan_confirm_result_review'] ?? false),
            'controlled_plan_confirm_hash' => (string) ($execution['controlled_plan_confirm_hash'] ?? ''),
            'official_output_published' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function controlledPlanConfirmResultReviewSummary(array $planConfirmLoad, bool $pass): array
    {
        $planConfirm = is_array($planConfirmLoad['payload']) ? $planConfirmLoad['payload'] : [];

        return [
            'validation_completed' => true,
            'controlled_plan_confirm_result_reviewed' => $pass,
            'controlled_plan_confirm_ready' => $this->controlledPlanConfirmReady($planConfirm),
            'controlled_plan_confirm_hash' => $planConfirmLoad['actual_hash'],
            'controlled_plan_confirm_file_sha1' => $planConfirmLoad['actual_file_sha1'],
            'controlled_plan_confirm_record_count' => is_array($planConfirm['output_rows'] ?? null) ? count($planConfirm['output_rows']) : 0,
            'plan_confirm_mode' => (string) ($planConfirm['plan_confirm_mode'] ?? ''),
            'plan_confirm_state' => (string) ($planConfirm['plan_confirm_state'] ?? ''),
            'baseline_plan_confirm_state' => (string) ($planConfirm['baseline_plan_confirm_state'] ?? ''),
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function controlledPlanConfirmIntegritySummary(array $execution, array $planConfirm, array $planConfirmLoad): array
    {
        return [
            'validation_completed' => true,
            'controlled_plan_confirm_integrity_valid' => $this->controlledPlanConfirmIntegrityValid($execution, $planConfirm, $planConfirmLoad),
            'execution_controlled_plan_confirm_hash' => $execution['controlled_plan_confirm_hash'] ?? null,
            'actual_controlled_plan_confirm_hash' => $planConfirmLoad['actual_hash'],
            'execution_controlled_plan_confirm_file_sha1' => $execution['controlled_plan_confirm_file_sha1'] ?? null,
            'actual_controlled_plan_confirm_file_sha1' => $planConfirmLoad['actual_file_sha1'],
            'controlled_plan_confirm_ready' => $this->controlledPlanConfirmReady($planConfirm),
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $execution, array $planConfirm): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($execution, $planConfirm),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'weekly_swing_watchlist_plan_confirm_controlled_only' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c160_role' => 'primary_candidate_plan_confirm_result_reviewed',
                'plan_confirm_result_reviewed' => $pass,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c160_role' => 'backup_candidate_plan_confirm_result_reviewed',
                'plan_confirm_result_reviewed' => $pass,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c160_role' => 'comparator_only_candidate',
                'plan_confirm_result_reviewed' => false,
                'plan_confirm_mutated' => false,
                'live_rollout_executed' => false,
                'a01_remains_comparator_only' => true,
            ],
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
        ];
    }

    private function resultReviewConfirmationSummary(array $options): array
    {
        return [
            'result_review_confirmation_required' => true,
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'controlled_plan_confirm_result_confirmation_required' => true,
            'controlled_plan_confirm_result_confirmed' => (bool) ($options['controlled_plan_confirm_result_confirmed'] ?? false),
            'controlled_plan_confirm_only_confirmation_required' => true,
            'controlled_plan_confirm_only_confirmed' => (bool) ($options['controlled_plan_confirm_only_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
        ];
    }

    private function documentationHygieneGuardSummary(array $executionLoad, array $planConfirmLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c160_execution_convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            'controlled_plan_confirm_convert_from_json_pass' => $planConfirmLoad['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => array_values(array_unique(array_merge($executionLoad['case_insensitive_duplicate_keys'], $planConfirmLoad['case_insensitive_duplicate_keys']))),
            'c160_execution_artifact_not_modified' => true,
            'controlled_plan_confirm_artifact_not_modified' => true,
            'c160_result_review_is_not_operator_decision' => true,
            'c160_topic_number_retained_for_next_stage' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-57_C160_PLAN_CONFIRM_RESULT_REVIEW',
            'topic_code' => 'C160_PLAN_CONFIRM',
            'topic_stage' => 'RESULT_REVIEW',
            'c160_execution_carried_forward' => true,
            'controlled_plan_confirm_carried_forward' => true,
            'controlled_plan_confirm_result_review_pass' => $pass,
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
            'planned_next_review' => $pass ? self::C160_OPERATOR_GO_NO_GO_RECOMMENDATION : 'C160_TARGETED_PLAN_CONFIRM_RESULT_REVIEW_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C160 PLAN/CONFIRM operator GO/NO-GO review only; controlled PLAN/CONFIRM evidence is reviewed while mutation, activated-catalog reads, live rollout, and free publication remain disabled' : 'targeted C160 execution lock, controlled PLAN/CONFIRM lock, confirmation, guard, or cleanup repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass ? [
                'locked C160 PLAN/CONFIRM result review artifact hash',
                'locked C160 PLAN/CONFIRM result review file SHA1',
                'operator decision GO or NO_GO',
                'PLAN/CONFIRM unchanged evidence',
                'live rollout still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C160 result review validates C160 execution artifact_hash and file SHA1 locks.',
            'C160 result review validates controlled PLAN/CONFIRM artifact hash, file SHA1, and row integrity.',
            'C160 result review confirms controlled PLAN/CONFIRM evidence is reviewed, not live-rolled out.',
            'C160 result review does not mutate PLAN/CONFIRM, read the activated catalog, execute live PLAN/CONFIRM rollout, or free-publish output.',
            'C160 result review keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C160 result review may only recommend same-topic PLAN/CONFIRM operator GO/NO-GO review next.',
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

    private function sourceArtifactLocks(array $executionLoad, array $planConfirmLoad): array
    {
        return [
            'c160_plan_confirm_execution' => [
                'artifact_path' => $executionLoad['path'],
                'expected_artifact_hash' => $executionLoad['expected_hash'],
                'actual_artifact_hash' => $executionLoad['actual_hash'],
                'artifact_hash_match' => $executionLoad['hash_match'],
                'expected_file_sha1' => $executionLoad['expected_file_sha1'],
                'actual_file_sha1' => $executionLoad['actual_file_sha1'],
                'file_sha1_match' => $executionLoad['file_sha1_match'],
                'convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            ],
            'controlled_plan_confirm' => [
                'artifact_path' => $planConfirmLoad['path'],
                'expected_artifact_hash' => $planConfirmLoad['expected_hash'],
                'actual_artifact_hash' => $planConfirmLoad['actual_hash'],
                'artifact_hash_match' => $planConfirmLoad['hash_match'],
                'expected_file_sha1' => $planConfirmLoad['expected_file_sha1'],
                'actual_file_sha1' => $planConfirmLoad['actual_file_sha1'],
                'file_sha1_match' => $planConfirmLoad['file_sha1_match'],
                'convert_from_json_pass' => $planConfirmLoad['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $executionLoad, array $planConfirmLoad): array
    {
        return [
            'expected_c160_execution_hash' => $executionLoad['expected_hash'],
            'actual_c160_execution_hash' => $executionLoad['actual_hash'],
            'c160_execution_hash_match' => $executionLoad['hash_match'],
            'expected_c160_execution_file_sha1' => $executionLoad['expected_file_sha1'],
            'actual_c160_execution_file_sha1' => $executionLoad['actual_file_sha1'],
            'c160_execution_file_sha1_match' => $executionLoad['file_sha1_match'],
            'c160_execution_convert_from_json_pass' => $executionLoad['convert_from_json_pass'],
            'expected_controlled_plan_confirm_hash' => $planConfirmLoad['expected_hash'],
            'actual_controlled_plan_confirm_hash' => $planConfirmLoad['actual_hash'],
            'controlled_plan_confirm_hash_match' => $planConfirmLoad['hash_match'],
            'expected_controlled_plan_confirm_file_sha1' => $planConfirmLoad['expected_file_sha1'],
            'actual_controlled_plan_confirm_file_sha1' => $planConfirmLoad['actual_file_sha1'],
            'controlled_plan_confirm_file_sha1_match' => $planConfirmLoad['file_sha1_match'],
            'controlled_plan_confirm_convert_from_json_pass' => $planConfirmLoad['convert_from_json_pass'],
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
