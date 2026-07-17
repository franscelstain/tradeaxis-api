<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewService
{
    public const RUN_CODE = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW';
    public const PHASE_LABEL = 'PR-41 / C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW';

    public const DEFAULT_C152_ARTIFACT = 'storage/app/watchlist/backtest/c152-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-result-review.json';
    public const DEFAULT_EXPECTED_C152_HASH = '85545acd1ea21a0efae6439ccb037b5c4ed34273';
    public const DEFAULT_EXPECTED_C152_FILE_SHA1 = 'FB866FEC13B1BE9D00E9D9CA50D494EC835EED14';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c153-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C152_STATUS = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_PASSED_RUNTIME_STABLE_READY_FOR_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_PRIMARY_AND_BACKUP';
    private const EXPECTED_C152_PHASE_LABEL = 'PR-40 / C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW';
    private const EXPECTED_C152_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C154_RECOMMENDATION = 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION';

    private const PASS_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_GENERATION_EXECUTION_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C152_LOCK_MISMATCH_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_ARTIFACT_LOCK_MISMATCH';
    private const C152_FILE_SHA1_MISMATCH_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_FILE_SHA1_LOCK_MISMATCH';
    private const C152_STATUS_MISMATCH_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_STATUS_MISMATCH';
    private const C152_PHASE_LABEL_MISMATCH_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_PHASE_LABEL_MISMATCH';
    private const C152_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_NEXT_RECOMMENDATION_MISMATCH';
    private const C152_BOUNDARY_INCOMPLETE_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_BOUNDARY_REVIEW_INCOMPLETE';
    private const OUTPUT_ALREADY_OCCURRED_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_OUTPUT_GENERATION_OR_PUBLICATION_ALREADY_OCCURRED';
    private const C152_CONVERT_FROM_JSON_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_C152_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C152_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_pass',
        'production_live_runtime_activation_post_execution_observation_result_review_pass',
        'ready_for_weekly_swing_watchlist_controlled_output_generation_boundary_review',
        'production_live_runtime_controlled_output_generation_boundary_review_allowed_next',
        'weekly_swing_watchlist_controlled_output_generation_allowed_next',
        'production_live_runtime_activation_executed',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_executed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c151_lock_valid',
        'c151_post_execution_observation_review_valid',
        'c151_convert_from_json_pass',
        'c150_lock_valid',
        'c150_final_execution_valid',
        'runtime_state_lock_valid',
        'runtime_state_observation_valid',
        'c149_operator_go_no_go_valid',
        'c148_activation_observation_result_review_valid',
        'activation_authorized',
        'primary_candidate_activation_authorized',
        'backup_candidate_activation_authorized',
        'primary_candidate_live_runtime_active',
        'backup_candidate_live_runtime_standby_active',
        'a01_remains_comparator_only',
        'c152_post_execution_observation_result_review_only',
        'c152_not_runtime_activation',
        'c152_not_output_generation',
        'c152_not_publication',
        'c152_not_plan_confirm_mutation',
        'operator_approved',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C152_FALSE_FIELDS = [
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
        'comparator_candidate_activation_authorized',
        'comparator_candidate_live_runtime_active',
        'temporary_negative_artifacts_remaining',
    ];

    private const OUTPUT_GUARD_FALSE_FIELDS = [
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
        string $c152Artifact = self::DEFAULT_C152_ARTIFACT,
        string $expectedC152Hash = self::DEFAULT_EXPECTED_C152_HASH,
        string $expectedC152FileSha1 = self::DEFAULT_EXPECTED_C152_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c152Artifact, $expectedC152Hash, $expectedC152FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C152_LOCK_MISMATCH_STATUS, 'C152 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c152_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C152_CONVERT_FROM_JSON_STATUS, 'C152 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C152_LOCK_MISMATCH_STATUS, 'C152 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C152_FILE_SHA1_MISMATCH_STATUS, 'C152 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c152 = $load['payload'];
        if (($c152['status'] ?? null) !== self::EXPECTED_C152_STATUS || ($c152['reason_code'] ?? null) !== self::EXPECTED_C152_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C152_STATUS_MISMATCH_STATUS, 'C152 status/reason is not controlled output generation boundary ready.', $outputPath, $overwrite);
        }
        if (($c152['phase_label'] ?? null) !== self::EXPECTED_C152_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C152_PHASE_LABEL_MISMATCH_STATUS, 'C152 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c152NextRecommendationMatches($c152)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C152_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C152 next recommendation is not C153.', $outputPath, $overwrite);
        }
        if (! $this->outputGuardClean($c152)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::OUTPUT_ALREADY_OCCURRED_STATUS, 'C152 already generated output, published output, or mutated PLAN/CONFIRM.', $outputPath, $overwrite);
        }
        if (! $this->c152BoundaryReviewComplete($c152)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C152_BOUNDARY_INCOMPLETE_STATUS, 'C152 controlled output generation boundary evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c152)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C152 candidate scope does not match controlled output generation boundary scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C153 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C153 locks the C152 observation-result review and opens only the next controlled output-generation execution boundary. It does not generate official output, publish output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C153_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_EXECUTION_NO_OUTPUT_CREATED';
        $artifact['next_step_recommendation'] = self::C154_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-41',
            'internal_checkpoint' => 'C153',
            'status' => 'C153_NOT_RUN',
            'reason_code' => 'C153_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_pass' => false,
            'production_live_runtime_controlled_output_generation_boundary_review_pass' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_execution' => false,
            'production_live_runtime_controlled_output_generation_execution_allowed_next' => false,
            'weekly_swing_watchlist_controlled_output_generation_allowed_next' => false,
            'weekly_swing_watchlist_controlled_output_generation_executed' => false,
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
            'c152_lock_valid' => false,
            'c152_controlled_output_generation_boundary_ready' => false,
            'c152_convert_from_json_pass' => false,
            'c151_lock_valid' => false,
            'c151_post_execution_observation_review_valid' => false,
            'runtime_state_lock_valid' => false,
            'runtime_state_observation_valid' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_live_runtime_active' => false,
            'backup_candidate_live_runtime_standby_active' => false,
            'comparator_candidate_live_runtime_active' => false,
            'a01_remains_comparator_only' => true,
            'c153_boundary_review_only' => true,
            'c153_not_output_generation' => true,
            'c153_not_publication' => true,
            'c153_not_plan_confirm_mutation' => true,
            'operator_approved' => false,
            'approval_reference' => '',
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => false,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C153_NOT_RUN',
            'next_step_recommendation' => null,
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_pass' => true,
            'production_live_runtime_controlled_output_generation_boundary_review_pass' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_generation_execution' => true,
            'production_live_runtime_controlled_output_generation_execution_allowed_next' => true,
            'weekly_swing_watchlist_controlled_output_generation_allowed_next' => true,
            'weekly_swing_watchlist_controlled_output_generation_executed' => false,
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
            'production_live_runtime_activation_executed' => true,
            'production_ready' => true,
            'production_catalog_runtime_wired' => true,
            'production_runtime_wiring_executed' => true,
            'runtime_bridge_active' => true,
            'weekly_swing_watchlist_runtime_active' => true,
            'weekly_swing_watchlist_live_output_enabled' => true,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => true,
            'c152_lock_valid' => true,
            'c152_controlled_output_generation_boundary_ready' => true,
            'c152_convert_from_json_pass' => true,
            'c151_lock_valid' => true,
            'c151_post_execution_observation_review_valid' => true,
            'runtime_state_lock_valid' => true,
            'runtime_state_observation_valid' => true,
            'primary_candidate_live_runtime_active' => true,
            'backup_candidate_live_runtime_standby_active' => true,
            'comparator_candidate_live_runtime_active' => false,
            'a01_remains_comparator_only' => true,
            'c153_boundary_review_only' => true,
            'c153_not_output_generation' => true,
            'c153_not_publication' => true,
            'c153_not_plan_confirm_mutation' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c152 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c152_lock_validation_summary'] = $this->c152LockValidationSummary($load, $c152);
        $artifact['c152_boundary_carry_forward_summary'] = $this->c152BoundaryCarryForwardSummary($c152, $pass);
        $artifact['controlled_output_generation_execution_decision'] = $this->controlledOutputGenerationExecutionDecision($pass);
        $artifact['controlled_output_generation_execution_manifest'] = $this->controlledOutputGenerationExecutionManifest($pass);
        $artifact['controlled_output_generation_boundary_checklist'] = $this->controlledOutputGenerationBoundaryChecklist();
        $artifact['candidate_controlled_output_generation_boundary_scorecard'] = $this->candidateScorecard($pass);
        $artifact['output_generation_publication_guard_summary'] = $this->outputGenerationPublicationGuardSummary($c152);
        $artifact['plan_confirm_guard_summary'] = $this->planConfirmGuardSummary($c152);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C153_PENDING')]);
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

    private function c152BoundaryReviewComplete(array $c152): bool
    {
        foreach (self::REQUIRED_C152_TRUE_FIELDS as $field) {
            if (! (bool) ($c152[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C152_FALSE_FIELDS as $field) {
            if ((bool) ($c152[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function outputGuardClean(array $c152): bool
    {
        foreach (self::OUTPUT_GUARD_FALSE_FIELDS as $field) {
            if ((bool) ($c152[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c152NextRecommendationMatches(array $c152): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c152, $path);
            if ($value !== null && $value !== self::EXPECTED_C152_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c152['next_step_recommendation'] ?? null) === self::EXPECTED_C152_NEXT_RECOMMENDATION;
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

    private function c152LockValidationSummary(array $load, array $c152): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C152',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C152_STATUS,
            'actual_status' => $c152['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C152_PHASE_LABEL,
            'actual_phase_label' => $c152['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C152_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c152NextRecommendationMatches($c152),
            'c152_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c152BoundaryCarryForwardSummary(array $c152, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c152_boundary_review_valid' => $this->c152BoundaryReviewComplete($c152),
            'runtime_bridge_active' => (bool) ($c152['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c152['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'controlled_output_generation_allowed_next_from_c152' => (bool) ($c152['weekly_swing_watchlist_controlled_output_generation_allowed_next'] ?? false),
            'official_output_generated' => (bool) ($c152['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => (bool) ($c152['weekly_swing_watchlist_official_output_published'] ?? false),
            'publication_allowed' => (bool) ($c152['weekly_swing_watchlist_publication_allowed'] ?? false),
            'unrestricted_publication_allowed' => (bool) ($c152['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($c152['plan_confirm_mutated'] ?? false),
            'c153_boundary_review_pass' => $pass,
        ];
    }

    private function controlledOutputGenerationExecutionDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'controlled_output_generation_execution_allowed_next' => $pass,
            'controlled_output_generation_executed_in_c153' => false,
            'official_output_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'next_recommendation' => $pass ? self::C154_RECOMMENDATION : 'C153_TARGETED_C152_BOUNDARY_REPAIR',
            'decision_reason' => $pass ? 'C153 records the controlled output-generation boundary and permits execution only in the next controlled step.' : 'C153 cannot proceed until C152 lock, boundary evidence, candidate scope, and output/PLAN-CONFIRM guards pass.',
        ];
    }

    private function controlledOutputGenerationExecutionManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'controlled_output_generation_boundary_review',
            'source_artifact_path' => self::DEFAULT_C152_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C152_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C152_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'ready_for_controlled_output_generation_execution' => $pass,
            'controlled_output_generation_execution_required_next' => $pass,
            'controlled_output_generation_executed_in_c153' => false,
            'official_output_generated' => false,
            'official_output_published' => false,
            'publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'boundary_review_only' => true,
        ];
    }

    private function controlledOutputGenerationBoundaryChecklist(): array
    {
        return [
            'c152_artifact_locked' => true,
            'controlled_output_generation_boundary_reviewed' => true,
            'operator_approval_required' => true,
            'runtime_bridge_must_remain_active' => true,
            'weekly_swing_live_output_must_remain_enabled' => true,
            'controlled_output_generation_execution_deferred_to_next_step' => true,
            'official_output_generation_forbidden_in_c153' => true,
            'official_output_publication_forbidden_in_c153' => true,
            'unrestricted_publication_forbidden' => true,
            'plan_confirm_mutation_forbidden_in_c153' => true,
            'artifact_only' => true,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c153_role' => 'primary_candidate_ready_for_controlled_output_generation_execution',
                'ready_for_controlled_output_generation_execution' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c153_role' => 'backup_standby_candidate_ready_for_controlled_output_generation_execution',
                'ready_for_controlled_output_generation_execution' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c153_role' => 'comparator_only_candidate',
                'ready_for_controlled_output_generation_execution' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function outputGenerationPublicationGuardSummary(array $c152): array
    {
        return [
            'guard_reviewed' => true,
            'official_output_generated' => (bool) ($c152['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => (bool) ($c152['weekly_swing_watchlist_official_output_published'] ?? false),
            'live_recommendation_generated' => (bool) ($c152['weekly_swing_watchlist_live_recommendation_generated'] ?? false),
            'publication_allowed' => (bool) ($c152['weekly_swing_watchlist_publication_allowed'] ?? false),
            'unrestricted_publication_allowed' => (bool) ($c152['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? false),
            'controlled_output_generation_executed_in_c153' => false,
            'controlled_output_generation_execution_allowed_next' => $this->outputGuardClean($c152),
        ];
    }

    private function planConfirmGuardSummary(array $c152): array
    {
        return [
            'guard_reviewed' => true,
            'plan_confirm_mutation_allowed' => (bool) ($c152['plan_confirm_mutation_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($c152['plan_confirm_mutated'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => (bool) ($c152['plan_confirm_runtime_reads_activated_catalog'] ?? false),
            'plan_confirm_unchanged_for_c153' => ! (bool) ($c152['plan_confirm_mutated'] ?? false),
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
            'c152_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c152_artifact_not_modified' => true,
            'c153_is_boundary_review_not_output_generation' => true,
            'c153_is_not_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-41_C153_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW',
            'c152_observation_result_carried_forward' => true,
            'controlled_output_generation_boundary_review_pass' => $pass,
            'ready_for_controlled_output_generation_execution' => $pass,
            'official_weekly_swing_output_generated' => false,
            'official_weekly_swing_output_published' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C154_RECOMMENDATION : 'C153_TARGETED_C152_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'controlled output-generation execution only; still no publication, unrestricted publication, or PLAN/CONFIRM mutation from C153' : 'targeted C152 lock, boundary evidence, candidate scope, output guard, or cleanup repair',
            'planned_next_required_inputs' => $pass ? [
                'locked C153 artifact hash',
                'locked C153 file SHA1',
                'locked C152 artifact hash',
                'operator approval reference for controlled generation execution',
                'no official output generated in C153',
                'no publication or PLAN/CONFIRM mutation in C153',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C153 validates C152 artifact_hash and file SHA1 locks before recording the controlled output-generation boundary review.',
            'C153 confirms runtime bridge and weekly swing live output remain active.',
            'C153 confirms official output generation and publication have not occurred.',
            'C153 confirms PLAN/CONFIRM remains unchanged.',
            'C153 keeps E02 primary, B01 backup standby, and A01 comparator-only.',
            'C153 may only recommend controlled output-generation execution next.',
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c152' => [
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
            'expected_c152_hash' => $load['expected_hash'],
            'actual_c152_hash' => $load['actual_hash'],
            'c152_hash_match' => $load['hash_match'],
            'expected_c152_file_sha1' => $load['expected_file_sha1'],
            'actual_c152_file_sha1' => $load['actual_file_sha1'],
            'c152_file_sha1_match' => $load['file_sha1_match'],
            'c152_convert_from_json_pass' => $load['convert_from_json_pass'],
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
