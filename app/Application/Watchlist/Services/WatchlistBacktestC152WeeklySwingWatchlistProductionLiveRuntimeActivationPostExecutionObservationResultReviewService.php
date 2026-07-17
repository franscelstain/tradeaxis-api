<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewService
{
    public const RUN_CODE = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-40 / C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW';

    public const DEFAULT_C151_ARTIFACT = 'storage/app/watchlist/backtest/c151-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-review.json';
    public const DEFAULT_EXPECTED_C151_HASH = '55f06c57436ead483bea22626552b7e500d53120';
    public const DEFAULT_EXPECTED_C151_FILE_SHA1 = '198B10144A6ADC5447478E36347CD8DAD6136E16';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c152-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C151_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_PASSED_RUNTIME_ACTIVE_READY_FOR_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C151_PHASE_LABEL = 'PR-39 / C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW';
    private const EXPECTED_C151_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C153_RECOMMENDATION = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW';

    private const PASS_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_PASSED_RUNTIME_STABLE_READY_FOR_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C151_LOCK_MISMATCH_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_ARTIFACT_LOCK_MISMATCH';
    private const C151_FILE_SHA1_MISMATCH_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_FILE_SHA1_LOCK_MISMATCH';
    private const C151_STATUS_MISMATCH_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_STATUS_MISMATCH';
    private const C151_PHASE_LABEL_MISMATCH_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_PHASE_LABEL_MISMATCH';
    private const C151_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_NEXT_RECOMMENDATION_MISMATCH';
    private const C151_OBSERVATION_REVIEW_INCOMPLETE_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_POST_EXECUTION_OBSERVATION_REVIEW_INCOMPLETE';
    private const C151_CONTROLLED_OUTPUT_GUARD_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_OUTPUT_GENERATION_OR_PUBLICATION_ALREADY_OCCURRED';
    private const C151_CONVERT_FROM_JSON_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_C151_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C151_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_pass',
        'production_live_runtime_activation_post_execution_observation_review_pass',
        'ready_for_production_live_runtime_activation_post_execution_observation_result_review',
        'production_live_runtime_activation_post_execution_observation_result_review_allowed_next',
        'production_live_runtime_activation_executed',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_executed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c150_lock_valid',
        'c150_final_execution_valid',
        'c150_convert_from_json_pass',
        'runtime_state_lock_valid',
        'runtime_state_observation_valid',
        'runtime_state_convert_from_json_pass',
        'c149_operator_go_no_go_valid',
        'c148_activation_observation_result_review_valid',
        'activation_authorized',
        'primary_candidate_activation_authorized',
        'backup_candidate_activation_authorized',
        'primary_candidate_live_runtime_active',
        'backup_candidate_live_runtime_standby_active',
        'a01_remains_comparator_only',
        'operator_approved',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C151_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_activation_authorized',
        'comparator_candidate_live_runtime_active',
        'temporary_negative_artifacts_remaining',
    ];

    private const CONTROLLED_OUTPUT_GUARD_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
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
        string $c151Artifact = self::DEFAULT_C151_ARTIFACT,
        string $expectedC151Hash = self::DEFAULT_EXPECTED_C151_HASH,
        string $expectedC151FileSha1 = self::DEFAULT_EXPECTED_C151_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c151Artifact, $expectedC151Hash, $expectedC151FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C151_LOCK_MISMATCH_STATUS, 'C151 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c151_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C151_CONVERT_FROM_JSON_STATUS, 'C151 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C151_LOCK_MISMATCH_STATUS, 'C151 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C151_FILE_SHA1_MISMATCH_STATUS, 'C151 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c151 = $load['payload'];
        if (($c151['status'] ?? null) !== self::EXPECTED_C151_STATUS || ($c151['reason_code'] ?? null) !== self::EXPECTED_C151_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C151_STATUS_MISMATCH_STATUS, 'C151 status/reason is not post-execution observation-result ready.', $outputPath, $overwrite);
        }
        if (($c151['phase_label'] ?? null) !== self::EXPECTED_C151_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C151_PHASE_LABEL_MISMATCH_STATUS, 'C151 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c151NextRecommendationMatches($c151)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C151_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C151 next recommendation is not C152.', $outputPath, $overwrite);
        }
        if (! $this->controlledOutputGuardClean($c151)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C151_CONTROLLED_OUTPUT_GUARD_STATUS, 'C151 already generated output, published output, or mutated PLAN/CONFIRM.', $outputPath, $overwrite);
        }
        if (! $this->c151PostExecutionObservationReviewComplete($c151)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C151_OBSERVATION_REVIEW_INCOMPLETE_STATUS, 'C151 post-execution observation review evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c151)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C151 candidate scope does not match locked post-execution observation result scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C152 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C152 reviews the C151 post-execution observation result and permits only the next controlled output-generation boundary review. It does not generate official weekly swing output, publish output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C152_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_PASSED_RUNTIME_STABLE_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_READY';
        $artifact['next_step_recommendation'] = self::C153_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-40',
            'internal_checkpoint' => 'C152',
            'status' => 'C152_NOT_RUN',
            'reason_code' => 'C152_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_pass' => false,
            'production_live_runtime_activation_post_execution_observation_result_review_pass' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_boundary_review' => false,
            'production_live_runtime_controlled_output_generation_boundary_review_allowed_next' => false,
            'weekly_swing_watchlist_controlled_output_generation_allowed_next' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'production_live_runtime_activation_executed' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c151_lock_valid' => false,
            'c151_post_execution_observation_review_valid' => false,
            'c151_convert_from_json_pass' => false,
            'c150_lock_valid' => false,
            'c150_final_execution_valid' => false,
            'runtime_state_lock_valid' => false,
            'runtime_state_observation_valid' => false,
            'c149_operator_go_no_go_valid' => false,
            'c148_activation_observation_result_review_valid' => false,
            'activation_authorized' => false,
            'primary_candidate_activation_authorized' => false,
            'backup_candidate_activation_authorized' => false,
            'comparator_candidate_activation_authorized' => false,
            'primary_candidate_live_runtime_active' => false,
            'backup_candidate_live_runtime_standby_active' => false,
            'comparator_candidate_live_runtime_active' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c152_post_execution_observation_result_review_only' => true,
            'c152_not_runtime_activation' => true,
            'c152_not_output_generation' => true,
            'c152_not_publication' => true,
            'c152_not_plan_confirm_mutation' => true,
            'operator_approved' => false,
            'approval_reference' => '',
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C152_NOT_RUN',
            'next_step_recommendation' => null,
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_pass' => true,
            'production_live_runtime_activation_post_execution_observation_result_review_pass' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_boundary_review' => true,
            'production_live_runtime_controlled_output_generation_boundary_review_allowed_next' => true,
            'weekly_swing_watchlist_controlled_output_generation_allowed_next' => true,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'production_live_runtime_activation_executed' => true,
            'production_ready' => true,
            'production_catalog_runtime_wired' => true,
            'production_runtime_wiring_executed' => true,
            'runtime_bridge_active' => true,
            'weekly_swing_watchlist_runtime_active' => true,
            'weekly_swing_watchlist_live_output_enabled' => true,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => true,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c151_lock_valid' => true,
            'c151_post_execution_observation_review_valid' => true,
            'c151_convert_from_json_pass' => true,
            'c150_lock_valid' => true,
            'c150_final_execution_valid' => true,
            'runtime_state_lock_valid' => true,
            'runtime_state_observation_valid' => true,
            'c149_operator_go_no_go_valid' => true,
            'c148_activation_observation_result_review_valid' => true,
            'activation_authorized' => true,
            'primary_candidate_activation_authorized' => true,
            'backup_candidate_activation_authorized' => true,
            'comparator_candidate_activation_authorized' => false,
            'primary_candidate_live_runtime_active' => true,
            'backup_candidate_live_runtime_standby_active' => true,
            'comparator_candidate_live_runtime_active' => false,
            'a01_remains_comparator_only' => true,
            'c152_post_execution_observation_result_review_only' => true,
            'c152_not_runtime_activation' => true,
            'c152_not_output_generation' => true,
            'c152_not_publication' => true,
            'c152_not_plan_confirm_mutation' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c151 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c151_lock_validation_summary'] = $this->c151LockValidationSummary($load, $c151);
        $artifact['c151_post_execution_observation_carry_forward_summary'] = $this->c151PostExecutionObservationCarryForwardSummary($c151, $pass);
        $artifact['runtime_stability_result_summary'] = $this->runtimeStabilityResultSummary($c151, $pass);
        $artifact['controlled_output_generation_boundary_decision'] = $this->controlledOutputGenerationBoundaryDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_output_generation_boundary_manifest'] = $this->controlledOutputGenerationBoundaryManifest($pass);
        $artifact['weekly_swing_watchlist_controlled_output_generation_boundary_checklist'] = $this->controlledOutputGenerationBoundaryChecklist();
        $artifact['candidate_runtime_stability_scorecard'] = $this->candidateRuntimeStabilityScorecard($pass);
        $artifact['output_generation_publication_guard_summary'] = $this->outputGenerationPublicationGuardSummary($c151);
        $artifact['plan_confirm_guard_summary'] = $this->planConfirmGuardSummary($c151);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C152_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($pass) {
            $artifact = array_merge($artifact, $this->passingTopLevelState());
        }

        return $artifact;
    }

    private function c151PostExecutionObservationReviewComplete(array $c151): bool
    {
        foreach (self::REQUIRED_C151_TRUE_FIELDS as $field) {
            if (! (bool) ($c151[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C151_FALSE_FIELDS as $field) {
            if ((bool) ($c151[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function controlledOutputGuardClean(array $c151): bool
    {
        foreach (self::CONTROLLED_OUTPUT_GUARD_FALSE_FIELDS as $field) {
            if ((bool) ($c151[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c151NextRecommendationMatches(array $c151): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c151, $path);
            if ($value !== null && $value !== self::EXPECTED_C151_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c151['next_step_recommendation'] ?? null) === self::EXPECTED_C151_NEXT_RECOMMENDATION;
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

        return ($source['a01_promoted'] ?? false) === false
            && (bool) ($source['a01_remains_comparator_only'] ?? true);
    }

    private function c151LockValidationSummary(array $load, array $c151): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C151',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C151_STATUS,
            'actual_status' => $c151['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C151_PHASE_LABEL,
            'actual_phase_label' => $c151['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C151_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c151NextRecommendationMatches($c151),
            'c151_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c151PostExecutionObservationCarryForwardSummary(array $c151, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c151_post_execution_observation_review_valid' => $this->c151PostExecutionObservationReviewComplete($c151),
            'c151_runtime_state_observation_valid' => (bool) ($c151['runtime_state_observation_valid'] ?? false),
            'c151_c150_final_execution_valid' => (bool) ($c151['c150_final_execution_valid'] ?? false),
            'runtime_bridge_active' => (bool) ($c151['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c151['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c151['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'official_output_generated' => (bool) ($c151['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => (bool) ($c151['weekly_swing_watchlist_official_output_published'] ?? false),
            'plan_confirm_mutated' => (bool) ($c151['plan_confirm_mutated'] ?? false),
            'post_execution_observation_result_review_pass' => $pass,
        ];
    }

    private function runtimeStabilityResultSummary(array $c151, bool $pass): array
    {
        return [
            'runtime_stability_reviewed' => true,
            'runtime_bridge_active' => (bool) ($c151['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c151['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c151['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'runtime_state_lock_valid' => (bool) ($c151['runtime_state_lock_valid'] ?? false),
            'runtime_state_observation_valid' => (bool) ($c151['runtime_state_observation_valid'] ?? false),
            'official_output_still_deferred' => ! (bool) ($c151['weekly_swing_watchlist_official_output_generated'] ?? false),
            'publication_still_deferred' => ! (bool) ($c151['weekly_swing_watchlist_official_output_published'] ?? false),
            'plan_confirm_unchanged' => ! (bool) ($c151['plan_confirm_mutated'] ?? false),
            'runtime_stable_enough_for_controlled_output_generation_boundary' => $pass,
        ];
    }

    private function controlledOutputGenerationBoundaryDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'controlled_output_generation_boundary_allowed_next' => $pass,
            'controlled_output_generation_allowed_now' => false,
            'official_output_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'next_recommendation' => $pass ? self::C153_RECOMMENDATION : 'C152_TARGETED_C151_POST_EXECUTION_OBSERVATION_REPAIR',
            'decision_reason' => $pass ? 'C152 records the C151 observation result as stable enough to review controlled output generation next, without generating or publishing output in C152.' : 'C152 cannot proceed until C151 lock, observation result, candidate scope, and output/PLAN-CONFIRM guards pass.',
        ];
    }

    private function controlledOutputGenerationBoundaryManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'post_execution_observation_result_review_for_controlled_output_generation_boundary',
            'source_artifact' => self::EXPECTED_C151_STATUS,
            'source_artifact_path' => self::DEFAULT_C151_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C151_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C151_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_runtime_active_candidate_ready_for_controlled_output_generation_boundary_review',
            'backup_candidate_role' => 'backup_runtime_standby_candidate_ready_for_controlled_output_generation_boundary_review',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'production_live_runtime_activation_post_execution_observation_result_review_pass' => $pass,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_boundary_review' => $pass,
            'controlled_output_generation_boundary_review_required_next' => $pass,
            'runtime_bridge_active' => $pass,
            'weekly_swing_live_output_enabled' => $pass,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_official_output_published' => false,
            'weekly_swing_live_recommendation_generated' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'unrestricted_publication_allowed' => false,
            'post_execution_observation_result_review_artifact_only' => true,
        ];
    }

    private function controlledOutputGenerationBoundaryChecklist(): array
    {
        return [
            'post_execution_observation_result_reviewed' => true,
            'c151_post_execution_observation_artifact_locked' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'operator_approval_required' => true,
            'runtime_bridge_must_remain_active' => true,
            'weekly_swing_live_output_must_remain_enabled' => true,
            'official_output_generation_must_remain_deferred_in_c152' => true,
            'official_output_publication_must_remain_deferred_in_c152' => true,
            'plan_confirm_mutation_forbidden_in_c152' => true,
            'controlled_output_generation_boundary_review_required_next' => true,
            'unrestricted_publication_forbidden' => true,
            'artifact_only' => true,
        ];
    }

    private function candidateRuntimeStabilityScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c152_role' => 'primary_runtime_active_candidate',
                'runtime_stability_pass' => $pass,
                'ready_for_controlled_output_generation_boundary_review' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c152_role' => 'backup_runtime_standby_candidate',
                'runtime_stability_pass' => $pass,
                'ready_for_controlled_output_generation_boundary_review' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c152_role' => 'comparator_only_candidate',
                'runtime_stability_pass' => false,
                'ready_for_controlled_output_generation_boundary_review' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function outputGenerationPublicationGuardSummary(array $c151): array
    {
        return [
            'guard_reviewed' => true,
            'official_output_generated' => (bool) ($c151['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => (bool) ($c151['weekly_swing_watchlist_official_output_published'] ?? false),
            'live_recommendation_generated' => (bool) ($c151['weekly_swing_watchlist_live_recommendation_generated'] ?? false),
            'controlled_output_generation_allowed_next' => $this->controlledOutputGuardClean($c151),
            'controlled_output_generation_executed_in_c152' => false,
            'publication_allowed_in_c152' => false,
            'unrestricted_publication_allowed' => false,
        ];
    }

    private function planConfirmGuardSummary(array $c151): array
    {
        return [
            'guard_reviewed' => true,
            'plan_confirm_mutation_allowed' => (bool) ($c151['plan_confirm_mutation_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($c151['plan_confirm_mutated'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => (bool) ($c151['plan_confirm_runtime_reads_activated_catalog'] ?? false),
            'live_plan_confirm_rollout_allowed' => (bool) ($c151['live_plan_confirm_rollout_allowed'] ?? false),
            'live_plan_confirm_rollout_executed' => (bool) ($c151['live_plan_confirm_rollout_executed'] ?? false),
            'plan_confirm_unchanged_for_c152' => ! (bool) ($c151['plan_confirm_mutated'] ?? false),
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
            'c151_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c151_artifact_not_modified' => true,
            'c152_is_observation_result_review_not_output_generation' => true,
            'c152_is_not_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-40_C152_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW',
            'c151_post_execution_observation_carried_forward' => true,
            'runtime_observation_result_stable' => $pass,
            'controlled_output_generation_boundary_ready' => $pass,
            'official_weekly_swing_output_generated' => false,
            'official_weekly_swing_output_published' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C153_RECOMMENDATION : 'C152_TARGETED_C151_POST_EXECUTION_OBSERVATION_REPAIR',
            'planned_next_scope' => $pass ? 'controlled output-generation boundary review only; C152 still does not generate official output, publish output, or mutate PLAN/CONFIRM' : 'targeted C151 lock, observation result, candidate scope, output guard, or cleanup repair',
            'planned_next_required_inputs' => $pass ? [
                'locked C152 artifact hash',
                'locked C152 file SHA1',
                'locked C151 artifact hash',
                'runtime bridge active observation carried forward',
                'weekly swing live output enabled observation carried forward',
                'official output still not generated or published',
                'PLAN/CONFIRM unchanged observation',
                'controlled output-generation boundary checklist',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C152 validates C151 artifact_hash and file SHA1 locks before recording the observation result review.',
            'C152 summarizes C151 post-execution observation evidence instead of running another activation or output command.',
            'C152 confirms the runtime bridge and weekly swing live output are active.',
            'C152 confirms official output generation, official publication, and live recommendation generation remain false.',
            'C152 confirms PLAN/CONFIRM remains unchanged.',
            'C152 keeps E02 primary, B01 backup standby, and A01 comparator-only.',
            'C152 may only recommend a controlled output-generation boundary review next.',
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c151' => [
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
            'expected_c151_hash' => $load['expected_hash'],
            'actual_c151_hash' => $load['actual_hash'],
            'c151_hash_match' => $load['hash_match'],
            'expected_c151_file_sha1' => $load['expected_file_sha1'],
            'actual_c151_file_sha1' => $load['actual_file_sha1'],
            'c151_file_sha1_match' => $load['file_sha1_match'],
            'c151_convert_from_json_pass' => $load['convert_from_json_pass'],
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
