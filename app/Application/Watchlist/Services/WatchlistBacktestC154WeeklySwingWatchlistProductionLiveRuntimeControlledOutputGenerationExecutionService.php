<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionService
{
    public const RUN_CODE = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION';
    public const PHASE_LABEL = 'PR-42 / C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION';
    public const ARTIFACT_TYPE = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION';

    public const DEFAULT_C153_ARTIFACT = 'storage/app/watchlist/backtest/c153-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-boundary-review.json';
    public const DEFAULT_EXPECTED_C153_HASH = '51bdfbcbb34ce49a185122f0df932451fd914a78';
    public const DEFAULT_EXPECTED_C153_FILE_SHA1 = '9B8A640C6C7C9DD1947AB4C69706C76F44793B43';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution.json';
    public const DEFAULT_CONTROLLED_OUTPUT_PATH = 'storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C153_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_GENERATION_EXECUTION_PRIMARY_AND_BACKUP';
    private const EXPECTED_C153_PHASE_LABEL = 'PR-41 / C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW';
    private const EXPECTED_C153_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C155_RECOMMENDATION = 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW';

    private const PASS_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_PASSED_CONTROLLED_OUTPUT_GENERATED_NOT_PUBLISHED_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const CONTROLLED_OUTPUT_CONFIRMATION_MISSING_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C153_LOCK_MISMATCH_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_ARTIFACT_LOCK_MISMATCH';
    private const C153_FILE_SHA1_MISMATCH_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_FILE_SHA1_LOCK_MISMATCH';
    private const C153_STATUS_MISMATCH_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_STATUS_MISMATCH';
    private const C153_PHASE_LABEL_MISMATCH_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_PHASE_LABEL_MISMATCH';
    private const C153_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_NEXT_RECOMMENDATION_MISMATCH';
    private const C153_BOUNDARY_INCOMPLETE_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_BOUNDARY_REVIEW_INCOMPLETE';
    private const OUTPUT_ALREADY_OCCURRED_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_OUTPUT_GENERATION_OR_PUBLICATION_ALREADY_OCCURRED';
    private const C153_CONVERT_FROM_JSON_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C153_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_pass',
        'production_live_runtime_controlled_output_generation_boundary_review_pass',
        'ready_for_weekly_swing_watchlist_controlled_output_generation_execution',
        'production_live_runtime_controlled_output_generation_execution_allowed_next',
        'weekly_swing_watchlist_controlled_output_generation_allowed_next',
        'production_live_runtime_activation_executed',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_executed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c152_lock_valid',
        'c152_controlled_output_generation_boundary_ready',
        'c152_convert_from_json_pass',
        'c151_lock_valid',
        'c151_post_execution_observation_review_valid',
        'runtime_state_lock_valid',
        'runtime_state_observation_valid',
        'primary_candidate_live_runtime_active',
        'backup_candidate_live_runtime_standby_active',
        'a01_remains_comparator_only',
        'c153_boundary_review_only',
        'c153_not_output_generation',
        'c153_not_publication',
        'c153_not_plan_confirm_mutation',
        'operator_approved',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C153_FALSE_FIELDS = [
        'weekly_swing_watchlist_controlled_output_generation_executed',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
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

    private const PRE_EXECUTION_OUTPUT_GUARD_FALSE_FIELDS = [
        'weekly_swing_watchlist_controlled_output_generation_executed',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
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
        string $c153Artifact = self::DEFAULT_C153_ARTIFACT,
        string $expectedC153Hash = self::DEFAULT_EXPECTED_C153_HASH,
        string $expectedC153FileSha1 = self::DEFAULT_EXPECTED_C153_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        string $controlledOutputPath = self::DEFAULT_CONTROLLED_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt, $controlledOutputPath);
        $load = $this->loadArtifactLock($c153Artifact, $expectedC153Hash, $expectedC153FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C153_LOCK_MISMATCH_STATUS, 'C153 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false, []);
            $artifact['c153_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C153_CONVERT_FROM_JSON_STATUS, 'C153 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C153_LOCK_MISMATCH_STATUS, 'C153 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C153_FILE_SHA1_MISMATCH_STATUS, 'C153 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c153 = $load['payload'];
        if (($c153['status'] ?? null) !== self::EXPECTED_C153_STATUS || ($c153['reason_code'] ?? null) !== self::EXPECTED_C153_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C153_STATUS_MISMATCH_STATUS, 'C153 status/reason is not controlled output generation execution ready.', $outputPath, $overwrite);
        }
        if (($c153['phase_label'] ?? null) !== self::EXPECTED_C153_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C153_PHASE_LABEL_MISMATCH_STATUS, 'C153 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c153NextRecommendationMatches($c153)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C153_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C153 next recommendation is not C154.', $outputPath, $overwrite);
        }
        if (! $this->preExecutionOutputGuardClean($c153)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::OUTPUT_ALREADY_OCCURRED_STATUS, 'C153 already generated output, published output, or mutated PLAN/CONFIRM.', $outputPath, $overwrite);
        }
        if (! $this->c153BoundaryReviewComplete($c153)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::C153_BOUNDARY_INCOMPLETE_STATUS, 'C153 controlled output generation boundary evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c153)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C153 candidate scope does not match controlled output generation execution scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::APPROVAL_MISSING_STATUS, 'C154 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_output_confirmed'] ?? false)
            || ! (bool) ($options['no_publication_confirmed'] ?? false)
            || ! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)
        ) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false, []), self::CONTROLLED_OUTPUT_CONFIRMATION_MISSING_STATUS, 'C154 requires controlled output, no-publication, and PLAN/CONFIRM unchanged confirmations.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false, []);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $controlledOutput = $this->writeControlledOutput($this->controlledOutputPayload($createdAt, $c153, $load), $controlledOutputPath, $overwrite);
        $artifact = $this->completeSections($artifact, $load, $options, true, $controlledOutput);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C154 generates the weekly swing watchlist controlled output artifact. The output is generated for controlled review only; it is not published, unrestricted publication remains disabled, and PLAN/CONFIRM is unchanged.';
        $artifact['diagnostic_conclusion'] = 'C154_CONTROLLED_OUTPUT_GENERATION_EXECUTED_OUTPUT_CREATED_NOT_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C155_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($controlledOutput));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $controlledOutputPath): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-42',
            'internal_checkpoint' => 'C154',
            'status' => 'C154_NOT_RUN',
            'reason_code' => 'C154_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_output_path' => $controlledOutputPath,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_pass' => false,
            'production_live_runtime_controlled_output_generation_execution_pass' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_result_review' => false,
            'production_live_runtime_controlled_output_generation_result_review_allowed_next' => false,
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
            'production_live_runtime_activation_executed' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => false,
            'c153_lock_valid' => false,
            'c153_controlled_output_generation_boundary_valid' => false,
            'c153_convert_from_json_pass' => false,
            'c152_lock_valid' => false,
            'c152_controlled_output_generation_boundary_ready' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_live_runtime_active' => false,
            'backup_candidate_live_runtime_standby_active' => false,
            'comparator_candidate_live_runtime_active' => false,
            'a01_remains_comparator_only' => true,
            'c154_controlled_output_generation_execution_only' => true,
            'c154_not_publication' => true,
            'c154_not_unrestricted_publication' => true,
            'c154_not_plan_confirm_mutation' => true,
            'operator_approved' => false,
            'approval_reference' => '',
            'controlled_output_confirmed' => false,
            'no_publication_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C154_NOT_RUN',
            'next_step_recommendation' => null,
        ];
    }

    private function passingTopLevelState(array $controlledOutput): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_pass' => true,
            'production_live_runtime_controlled_output_generation_execution_pass' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_result_review' => true,
            'production_live_runtime_controlled_output_generation_result_review_allowed_next' => true,
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
            'production_live_runtime_activation_executed' => true,
            'production_ready' => true,
            'production_catalog_runtime_wired' => true,
            'production_runtime_wiring_executed' => true,
            'runtime_bridge_active' => true,
            'weekly_swing_watchlist_runtime_active' => true,
            'weekly_swing_watchlist_live_output_enabled' => true,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => true,
            'c153_lock_valid' => true,
            'c153_controlled_output_generation_boundary_valid' => true,
            'c153_convert_from_json_pass' => true,
            'c152_lock_valid' => true,
            'c152_controlled_output_generation_boundary_ready' => true,
            'primary_candidate_live_runtime_active' => true,
            'backup_candidate_live_runtime_standby_active' => true,
            'comparator_candidate_live_runtime_active' => false,
            'a01_remains_comparator_only' => true,
            'controlled_output_hash' => $controlledOutput['controlled_output_hash'] ?? null,
            'controlled_output_file_sha1' => $controlledOutput['controlled_output_file_sha1'] ?? null,
            'controlled_output_record_count' => $controlledOutput['controlled_output_record_count'] ?? 0,
            'c154_controlled_output_generation_execution_only' => true,
            'c154_not_publication' => true,
            'c154_not_unrestricted_publication' => true,
            'c154_not_plan_confirm_mutation' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass, array $controlledOutput): array
    {
        $c153 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c153_lock_validation_summary'] = $this->c153LockValidationSummary($load, $c153);
        $artifact['c153_boundary_carry_forward_summary'] = $this->c153BoundaryCarryForwardSummary($c153, $pass);
        $artifact['controlled_output_generation_summary'] = $this->controlledOutputGenerationSummary($controlledOutput, $pass);
        $artifact['controlled_output_publication_guard_summary'] = $this->controlledOutputPublicationGuardSummary($c153, $pass);
        $artifact['controlled_output_generation_manifest'] = $this->controlledOutputGenerationManifest($controlledOutput, $pass);
        $artifact['candidate_controlled_output_generation_scorecard'] = $this->candidateScorecard($pass);
        $artifact['plan_confirm_guard_summary'] = $this->planConfirmGuardSummary($c153);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['execution_confirmation_summary'] = $this->executionConfirmationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C154_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');
        $artifact['controlled_output_confirmed'] = (bool) ($options['controlled_output_confirmed'] ?? false);
        $artifact['no_publication_confirmed'] = (bool) ($options['no_publication_confirmed'] ?? false);
        $artifact['plan_confirm_unchanged_confirmed'] = (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false);
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($pass) {
            $artifact = array_merge($artifact, $this->passingTopLevelState($controlledOutput));
        }

        return $artifact;
    }

    private function c153BoundaryReviewComplete(array $c153): bool
    {
        foreach (self::REQUIRED_C153_TRUE_FIELDS as $field) {
            if (! (bool) ($c153[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C153_FALSE_FIELDS as $field) {
            if ((bool) ($c153[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function preExecutionOutputGuardClean(array $c153): bool
    {
        foreach (self::PRE_EXECUTION_OUTPUT_GUARD_FALSE_FIELDS as $field) {
            if ((bool) ($c153[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c153NextRecommendationMatches(array $c153): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c153, $path);
            if ($value !== null && $value !== self::EXPECTED_C153_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c153['next_step_recommendation'] ?? null) === self::EXPECTED_C153_NEXT_RECOMMENDATION;
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
        if ((bool) ($source['a01_promoted'] ?? false)) {
            return false;
        }

        return (bool) ($source['a01_remains_comparator_only'] ?? true);
    }

    private function controlledOutputPayload(string $createdAt, array $c153, array $load): array
    {
        return [
            'controlled_output_type' => 'weekly_swing_watchlist_controlled_output_generation',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'created_at' => $createdAt,
            'controlled_output_hash' => null,
            'controlled_output_hash_algorithm' => 'stable_sha1_json_payload',
            'source_c153_artifact_path' => $load['path'],
            'source_c153_artifact_hash' => $load['actual_hash'],
            'source_c153_file_sha1' => $load['actual_file_sha1'],
            'runtime_bridge_active' => (bool) ($c153['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c153['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'generation_mode' => 'controlled',
            'publication_state' => 'not_published',
            'weekly_swing_watchlist_controlled_output_generation_executed' => true,
            'weekly_swing_watchlist_official_output_generated' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'output_rows' => [
                [
                    'rank' => 1,
                    'candidate_code' => self::PRIMARY_CANDIDATE,
                    'candidate_role' => 'primary_live_runtime_candidate',
                    'output_mode' => 'controlled_generation_only',
                    'publish_state' => 'not_published',
                ],
                [
                    'rank' => 2,
                    'candidate_code' => self::BACKUP_CANDIDATE,
                    'candidate_role' => 'backup_standby_candidate',
                    'output_mode' => 'controlled_generation_only',
                    'publish_state' => 'not_published',
                ],
            ],
            'comparator_candidate' => [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'candidate_role' => 'comparator_only_not_generated_for_publication',
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function writeControlledOutput(array $payload, string $path, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) {
            $raw = (string) file_get_contents($path);
            $decoded = json_decode($raw, true);

            return [
                'controlled_output_path' => $path,
                'controlled_output_hash' => is_array($decoded) ? ($decoded['controlled_output_hash'] ?? null) : null,
                'controlled_output_file_sha1' => strtoupper(sha1($raw)),
                'controlled_output_record_count' => is_array($decoded['output_rows'] ?? null) ? count($decoded['output_rows']) : 0,
                'write_skipped_existing_controlled_output' => true,
            ];
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $hashPayload = $payload;
        $hashPayload['controlled_output_hash'] = null;
        unset($hashPayload['controlled_output_path']);
        $payload['controlled_output_hash'] = sha1(json_encode($hashPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $payload['controlled_output_path'] = $path;
        $raw = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
        file_put_contents($path, $raw);

        return [
            'controlled_output_path' => $path,
            'controlled_output_hash' => $payload['controlled_output_hash'],
            'controlled_output_file_sha1' => strtoupper(sha1($raw)),
            'controlled_output_record_count' => count($payload['output_rows']),
            'write_skipped_existing_controlled_output' => false,
        ];
    }

    private function c153LockValidationSummary(array $load, array $c153): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C153',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C153_STATUS,
            'actual_status' => $c153['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C153_PHASE_LABEL,
            'actual_phase_label' => $c153['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C153_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c153NextRecommendationMatches($c153),
            'c153_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c153BoundaryCarryForwardSummary(array $c153, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c153_boundary_review_valid' => $this->c153BoundaryReviewComplete($c153),
            'controlled_output_generation_execution_allowed_next_from_c153' => (bool) ($c153['weekly_swing_watchlist_controlled_output_generation_allowed_next'] ?? false),
            'runtime_bridge_active' => (bool) ($c153['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c153['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'pre_execution_output_guard_clean' => $this->preExecutionOutputGuardClean($c153),
            'c154_execution_pass' => $pass,
        ];
    }

    private function controlledOutputGenerationSummary(array $controlledOutput, bool $pass): array
    {
        return [
            'controlled_output_generation_executed' => $pass,
            'controlled_output_artifact_created' => $pass,
            'controlled_output_path' => $controlledOutput['controlled_output_path'] ?? null,
            'controlled_output_hash' => $controlledOutput['controlled_output_hash'] ?? null,
            'controlled_output_file_sha1' => $controlledOutput['controlled_output_file_sha1'] ?? null,
            'controlled_output_record_count' => $controlledOutput['controlled_output_record_count'] ?? 0,
            'official_output_generated_for_controlled_review' => $pass,
            'official_output_published' => false,
        ];
    }

    private function controlledOutputPublicationGuardSummary(array $c153, bool $pass): array
    {
        return [
            'guard_reviewed' => true,
            'pre_execution_official_output_generated' => (bool) ($c153['weekly_swing_watchlist_official_output_generated'] ?? false),
            'controlled_output_generated_in_c154' => $pass,
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
        ];
    }

    private function controlledOutputGenerationManifest(array $controlledOutput, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'controlled_output_generation_execution',
            'controlled_output_path' => $controlledOutput['controlled_output_path'] ?? null,
            'controlled_output_hash' => $controlledOutput['controlled_output_hash'] ?? null,
            'controlled_output_file_sha1' => $controlledOutput['controlled_output_file_sha1'] ?? null,
            'controlled_output_record_count' => $controlledOutput['controlled_output_record_count'] ?? 0,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'controlled_output_generation_result_review_required_next' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c154_role' => 'primary_candidate_generated_in_controlled_output',
                'controlled_output_generated' => $pass,
                'published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c154_role' => 'backup_standby_candidate_generated_in_controlled_output',
                'controlled_output_generated' => $pass,
                'published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c154_role' => 'comparator_only_candidate',
                'controlled_output_generated' => false,
                'published' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function planConfirmGuardSummary(array $c153): array
    {
        return [
            'guard_reviewed' => true,
            'plan_confirm_mutation_allowed' => (bool) ($c153['plan_confirm_mutation_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($c153['plan_confirm_mutated'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => (bool) ($c153['plan_confirm_runtime_reads_activated_catalog'] ?? false),
            'plan_confirm_unchanged_for_c154' => true,
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

    private function executionConfirmationSummary(array $options, bool $pass): array
    {
        return [
            'controlled_output_confirmed' => (bool) ($options['controlled_output_confirmed'] ?? false),
            'no_publication_confirmed' => (bool) ($options['no_publication_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'execution_confirmation_valid' => $pass,
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

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c153_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c153_artifact_not_modified' => true,
            'c154_is_controlled_generation_not_publication' => true,
            'c154_is_not_plan_confirm_mutation' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-42_C154_CONTROLLED_OUTPUT_GENERATION_EXECUTION',
            'c153_boundary_review_carried_forward' => true,
            'controlled_output_generation_executed' => $pass,
            'controlled_output_artifact_created' => $pass,
            'official_weekly_swing_output_generated_for_controlled_review' => $pass,
            'official_weekly_swing_output_published' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C155_RECOMMENDATION : 'C154_TARGETED_C153_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'controlled output-generation result review only; no publication, unrestricted publication, or PLAN/CONFIRM mutation from C154' : 'targeted C153 lock, boundary evidence, explicit confirmation, output guard, or cleanup repair',
            'planned_next_required_inputs' => $pass ? [
                'locked C154 artifact hash',
                'locked C154 file SHA1',
                'locked controlled output artifact hash',
                'locked controlled output artifact file SHA1',
                'publication still disabled',
                'PLAN/CONFIRM unchanged',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C154 validates C153 artifact_hash and file SHA1 locks before controlled output generation.',
            'C154 requires operator approval plus controlled-output, no-publication, and PLAN/CONFIRM unchanged confirmations.',
            'C154 creates a controlled output artifact for review.',
            'C154 does not publish output or allow unrestricted publication.',
            'C154 does not mutate PLAN/CONFIRM.',
            'C154 keeps E02 primary, B01 backup standby, and A01 comparator-only.',
            'C154 may only recommend controlled output-generation result review next.',
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c153' => [
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
            'expected_c153_hash' => $load['expected_hash'],
            'actual_c153_hash' => $load['actual_hash'],
            'c153_hash_match' => $load['hash_match'],
            'expected_c153_file_sha1' => $load['expected_file_sha1'],
            'actual_c153_file_sha1' => $load['actual_file_sha1'],
            'c153_file_sha1_match' => $load['file_sha1_match'],
            'c153_convert_from_json_pass' => $load['convert_from_json_pass'],
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
        $expectedFileSha1 = strtoupper($expectedFileSha1);

        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $actualHash !== null && hash_equals($expectedHash, (string) $actualHash),
            'expected_file_sha1' => $expectedFileSha1,
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $actualFileSha1 !== null && $expectedFileSha1 === $actualFileSha1,
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

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => array_values(array_filter($status)),
            'failure_count' => count(array_values(array_filter($status))),
        ];
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
