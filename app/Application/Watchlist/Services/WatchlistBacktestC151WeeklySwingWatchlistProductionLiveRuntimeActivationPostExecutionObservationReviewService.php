<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewService
{
    public const RUN_CODE = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW';
    public const PHASE_LABEL = 'PR-39 / C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW';
    public const ARTIFACT_TYPE = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW';

    public const DEFAULT_C150_ARTIFACT = 'storage/app/watchlist/backtest/c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution.json';
    public const DEFAULT_EXPECTED_C150_HASH = '0b3b5e57011d8d98fcd38c004fb8d94fb33ca9ad';
    public const DEFAULT_EXPECTED_C150_FILE_SHA1 = 'E25A4E0DF40F9E01E6B3270F2AE2C5FF1CF0A500';
    public const DEFAULT_RUNTIME_STATE = 'storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json';
    public const DEFAULT_EXPECTED_RUNTIME_STATE_HASH = '00cb935a8252efe340d5f6ec6ea6966d9645cff7';
    public const DEFAULT_EXPECTED_RUNTIME_STATE_FILE_SHA1 = '17E41FFC5C6EE00CCCB4DF555A22EF192F2FCCF4';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c151-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C150_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_PASSED_LIVE_RUNTIME_BRIDGE_ACTIVE_PRIMARY_AND_BACKUP';
    private const EXPECTED_C150_PHASE_LABEL = 'PR-38 / C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION';
    private const EXPECTED_C150_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C152_RECOMMENDATION = 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW';

    private const PASS_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_PASSED_RUNTIME_ACTIVE_READY_FOR_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C150_LOCK_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_ARTIFACT_LOCK_MISMATCH';
    private const C150_FILE_SHA1_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_FILE_SHA1_LOCK_MISMATCH';
    private const RUNTIME_STATE_LOCK_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_LOCK_MISMATCH';
    private const RUNTIME_STATE_FILE_SHA1_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_FILE_SHA1_LOCK_MISMATCH';
    private const C150_STATUS_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_STATUS_MISMATCH';
    private const C150_PHASE_LABEL_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_PHASE_LABEL_MISMATCH';
    private const C150_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_NEXT_RECOMMENDATION_MISMATCH';
    private const C150_FINAL_EXECUTION_INCOMPLETE_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_FINAL_EXECUTION_INCOMPLETE';
    private const RUNTIME_STATE_OBSERVATION_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_OBSERVATION_MISMATCH';
    private const RUNTIME_STATE_LINK_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_LINK_MISMATCH';
    private const C150_CONVERT_FROM_JSON_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_C150_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const RUNTIME_STATE_CONVERT_FROM_JSON_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_RUNTIME_STATE_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';

    private const REQUIRED_C150_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_final_execution_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_final_execution_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_final_execution_pass',
        'production_live_runtime_activation_final_execution_pass',
        'production_live_runtime_activation_executed',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c149_lock_valid',
        'c149_operator_go_no_go_valid',
        'c149_convert_from_json_pass',
        'c148_activation_observation_result_review_valid',
        'c147_activation_observation_review_valid',
        'c146_activation_execution_review_valid',
        'c145_activation_authorization_valid',
        'c144_pre_activation_boundary_valid',
        'c143_go_decision_finalization_valid',
        'c142_activation_operator_go_no_go_valid',
        'c141_activation_observation_result_review_valid',
        'activation_authorized',
        'primary_candidate_activation_authorized',
        'backup_candidate_activation_authorized',
        'primary_candidate_live_runtime_active',
        'backup_candidate_live_runtime_standby_active',
        'a01_remains_comparator_only',
        'operator_approved',
        'runtime_bridge_enablement_confirmed',
        'live_output_enablement_confirmed',
        'rollback_confirmed',
        'kill_switch_confirmed',
    ];

    private const REQUIRED_C150_FALSE_FIELDS = [
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
    ];

    private const REQUIRED_RUNTIME_TRUE_FIELDS = [
        'production_live_runtime_activation_executed',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_executed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'kill_switch_confirmed',
        'rollback_confirmed',
    ];

    private const REQUIRED_RUNTIME_FALSE_FIELDS = [
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
        string $c150Artifact = self::DEFAULT_C150_ARTIFACT,
        string $expectedC150Hash = self::DEFAULT_EXPECTED_C150_HASH,
        string $expectedC150FileSha1 = self::DEFAULT_EXPECTED_C150_FILE_SHA1,
        string $runtimeState = self::DEFAULT_RUNTIME_STATE,
        string $expectedRuntimeStateHash = self::DEFAULT_EXPECTED_RUNTIME_STATE_HASH,
        string $expectedRuntimeStateFileSha1 = self::DEFAULT_EXPECTED_RUNTIME_STATE_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')), $runtimeState);
        $c150Load = $this->loadJsonLock($c150Artifact, $expectedC150Hash, $expectedC150FileSha1, 'artifact_hash');
        $runtimeLoad = $this->loadJsonLock($runtimeState, $expectedRuntimeStateHash, $expectedRuntimeStateFileSha1, 'runtime_state_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($c150Load, $runtimeLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($c150Load, $runtimeLoad));

        if (! $c150Load['exists'] || ! is_array($c150Load['payload'])) {
            return $this->blocked($artifact, self::C150_LOCK_MISMATCH_STATUS, 'C150 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $c150Load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false);
            $artifact['c150_convert_from_json_duplicate_keys'] = $c150Load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C150_CONVERT_FROM_JSON_STATUS, 'C150 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $c150Load['hash_match']) {
            return $this->blocked($artifact, self::C150_LOCK_MISMATCH_STATUS, 'C150 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $c150Load['file_sha1_match']) {
            return $this->blocked($artifact, self::C150_FILE_SHA1_MISMATCH_STATUS, 'C150 file SHA1 mismatch.', $outputPath, $overwrite);
        }
        if (! $runtimeLoad['exists'] || ! is_array($runtimeLoad['payload'])) {
            return $this->blocked($artifact, self::RUNTIME_STATE_LOCK_MISMATCH_STATUS, 'C150 runtime state missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $runtimeLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false);
            $artifact['runtime_state_convert_from_json_duplicate_keys'] = $runtimeLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::RUNTIME_STATE_CONVERT_FROM_JSON_STATUS, 'C150 runtime state is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $runtimeLoad['hash_match']) {
            return $this->blocked($artifact, self::RUNTIME_STATE_LOCK_MISMATCH_STATUS, 'Runtime state hash mismatch.', $outputPath, $overwrite);
        }
        if (! $runtimeLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::RUNTIME_STATE_FILE_SHA1_MISMATCH_STATUS, 'Runtime state file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c150 = $c150Load['payload'];
        $state = $runtimeLoad['payload'];
        if (($c150['status'] ?? null) !== self::EXPECTED_C150_STATUS || ($c150['reason_code'] ?? null) !== self::EXPECTED_C150_STATUS) {
            return $this->rejected($this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false), self::C150_STATUS_MISMATCH_STATUS, 'C150 status/reason is not post-execution observation ready.', $outputPath, $overwrite);
        }
        if (($c150['phase_label'] ?? null) !== self::EXPECTED_C150_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false), self::C150_PHASE_LABEL_MISMATCH_STATUS, 'C150 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c150NextRecommendationMatches($c150)) {
            return $this->rejected($this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false), self::C150_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C150 next recommendation is not C151.', $outputPath, $overwrite);
        }
        if (! $this->c150FinalExecutionComplete($c150)) {
            return $this->rejected($this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false), self::C150_FINAL_EXECUTION_INCOMPLETE_STATUS, 'C150 final execution evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->runtimeStateObservedClean($state)) {
            return $this->rejected($this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false), self::RUNTIME_STATE_OBSERVATION_MISMATCH_STATUS, 'Runtime state is not active-and-clean for post-execution observation.', $outputPath, $overwrite);
        }
        if (! $this->runtimeStateLinkedToC150($c150, $state, $runtimeLoad)) {
            return $this->rejected($this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false), self::RUNTIME_STATE_LINK_MISMATCH_STATUS, 'Runtime state is not linked to locked C150 execution evidence.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c150) || ! $this->candidateScopeMatches($state)) {
            return $this->rejected($this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'Candidate scope does not match locked post-execution observation scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $c150Load, $runtimeLoad, $options, false), self::APPROVAL_MISSING_STATUS, 'C151 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $c150Load, $runtimeLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $c150Load, $runtimeLoad, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C151 observes the active C150 runtime state. Runtime bridge and weekly swing live output are active; official recommendation generation, output publication, and PLAN/CONFIRM mutation remain off.';
        $artifact['diagnostic_conclusion'] = 'C151_POST_EXECUTION_OBSERVATION_PASSED_RUNTIME_ACTIVE_OUTPUT_NOT_GENERATED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C152_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $runtimeStatePath): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-39',
            'internal_checkpoint' => 'C151',
            'status' => 'C151_NOT_RUN',
            'reason_code' => 'C151_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'runtime_state_path' => $runtimeStatePath,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_pass' => false,
            'production_live_runtime_activation_post_execution_observation_review_pass' => false,
            'ready_for_production_live_runtime_activation_post_execution_observation_result_review' => false,
            'production_live_runtime_activation_post_execution_observation_result_review_allowed_next' => false,
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
            'c150_lock_valid' => false,
            'c150_final_execution_valid' => false,
            'c150_convert_from_json_pass' => false,
            'runtime_state_lock_valid' => false,
            'runtime_state_observation_valid' => false,
            'runtime_state_convert_from_json_pass' => false,
            'c149_operator_go_no_go_valid' => false,
            'c148_activation_observation_result_review_valid' => false,
            'activation_authorized' => false,
            'primary_candidate_activation_authorized' => false,
            'backup_candidate_activation_authorized' => false,
            'comparator_candidate_activation_authorized' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_live_runtime_active' => false,
            'backup_candidate_live_runtime_standby_active' => false,
            'comparator_candidate_live_runtime_active' => false,
            'a01_remains_comparator_only' => true,
            'operator_approved' => false,
            'approval_reference' => '',
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_pass' => true,
            'production_live_runtime_activation_post_execution_observation_review_pass' => true,
            'ready_for_production_live_runtime_activation_post_execution_observation_result_review' => true,
            'production_live_runtime_activation_post_execution_observation_result_review_allowed_next' => true,
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
            'c150_lock_valid' => true,
            'c150_final_execution_valid' => true,
            'c150_convert_from_json_pass' => true,
            'runtime_state_lock_valid' => true,
            'runtime_state_observation_valid' => true,
            'runtime_state_convert_from_json_pass' => true,
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
        ];
    }

    private function completeSections(array $artifact, array $c150Load, array $runtimeLoad, array $options, bool $pass): array
    {
        $c150 = is_array($c150Load['payload'] ?? null) ? $c150Load['payload'] : [];
        $state = is_array($runtimeLoad['payload'] ?? null) ? $runtimeLoad['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c150_lock_validation_summary'] = $this->c150LockValidationSummary($c150Load, $c150);
        $artifact['runtime_state_lock_validation_summary'] = $this->runtimeStateLockValidationSummary($runtimeLoad, $state);
        $artifact['post_execution_observation_summary'] = $this->postExecutionObservationSummary($c150, $state, $pass);
        $artifact['runtime_state_observation_summary'] = $this->runtimeStateObservationSummary($state, $pass);
        $artifact['candidate_runtime_observation_scorecard'] = $this->candidateRuntimeObservationScorecard($pass);
        $artifact['output_generation_guard_summary'] = $this->outputGenerationGuardSummary($c150, $state);
        $artifact['plan_confirm_observation_summary'] = $this->planConfirmObservationSummary($c150, $state);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($c150Load, $runtimeLoad);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C151_PENDING')]);
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

    private function c150FinalExecutionComplete(array $c150): bool
    {
        foreach (self::REQUIRED_C150_TRUE_FIELDS as $field) {
            if (! (bool) ($c150[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C150_FALSE_FIELDS as $field) {
            if ((bool) ($c150[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function runtimeStateObservedClean(array $state): bool
    {
        foreach (self::REQUIRED_RUNTIME_TRUE_FIELDS as $field) {
            if (! (bool) ($state[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_RUNTIME_FALSE_FIELDS as $field) {
            if ((bool) ($state[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function runtimeStateLinkedToC150(array $c150, array $state, array $runtimeLoad): bool
    {
        if (($c150['runtime_state_path'] ?? null) !== $runtimeLoad['path']) {
            return false;
        }
        if (($c150['runtime_state_hash'] ?? null) !== ($state['runtime_state_hash'] ?? null)) {
            return false;
        }
        if (($state['source_run_code'] ?? null) !== 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION') {
            return false;
        }
        if (($state['source_phase_label'] ?? null) !== self::EXPECTED_C150_PHASE_LABEL) {
            return false;
        }
        if (($state['activation_reference'] ?? null) !== ($c150['activation_reference'] ?? null)) {
            return false;
        }

        return true;
    }

    private function c150NextRecommendationMatches(array $c150): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c150, $path);
            if ($value !== null && $value !== self::EXPECTED_C150_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c150['next_step_recommendation'] ?? null) === self::EXPECTED_C150_NEXT_RECOMMENDATION;
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

        return (bool) ($source['a01_remains_comparator_only'] ?? true);
    }

    private function c150LockValidationSummary(array $load, array $c150): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C150',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C150_STATUS,
            'actual_status' => $c150['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C150_PHASE_LABEL,
            'actual_phase_label' => $c150['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C150_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c150NextRecommendationMatches($c150),
            'c150_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function runtimeStateLockValidationSummary(array $load, array $state): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C150_RUNTIME_STATE',
            'runtime_state_path' => $load['path'],
            'runtime_state_exists' => $load['exists'],
            'expected_runtime_state_hash' => $load['expected_hash'],
            'actual_runtime_state_hash' => $load['actual_hash'],
            'runtime_state_hash_match' => $load['hash_match'],
            'expected_runtime_state_file_sha1' => $load['expected_file_sha1'],
            'actual_runtime_state_file_sha1' => $load['actual_file_sha1'],
            'runtime_state_file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'runtime_state_observation_valid' => $this->runtimeStateObservedClean($state),
        ];
    }

    private function postExecutionObservationSummary(array $c150, array $state, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c150_final_execution_valid' => $this->c150FinalExecutionComplete($c150),
            'runtime_state_observation_valid' => $this->runtimeStateObservedClean($state),
            'runtime_state_linked_to_c150' => $this->runtimeStateLinkedToC150($c150, $state, [
                'path' => (string) ($c150['runtime_state_path'] ?? self::DEFAULT_RUNTIME_STATE),
            ]),
            'runtime_bridge_active' => (bool) ($state['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($state['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'official_output_generated' => (bool) ($state['weekly_swing_watchlist_official_output_generated'] ?? false),
            'official_output_published' => (bool) ($state['weekly_swing_watchlist_official_output_published'] ?? false),
            'plan_confirm_mutated' => (bool) ($state['plan_confirm_mutated'] ?? false),
            'post_execution_observation_pass' => $pass,
        ];
    }

    private function runtimeStateObservationSummary(array $state, bool $pass): array
    {
        return [
            'runtime_state_observed' => true,
            'runtime_state_type' => $state['runtime_state_type'] ?? null,
            'runtime_state_hash' => $state['runtime_state_hash'] ?? null,
            'production_live_runtime_activation_executed' => (bool) ($state['production_live_runtime_activation_executed'] ?? false),
            'runtime_bridge_active' => (bool) ($state['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($state['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($state['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($state['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($state['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => (bool) ($state['weekly_swing_watchlist_official_output_published'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generated' => (bool) ($state['weekly_swing_watchlist_live_recommendation_generated'] ?? false),
            'plan_confirm_mutated' => (bool) ($state['plan_confirm_mutated'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => (bool) ($state['plan_confirm_runtime_reads_activated_catalog'] ?? false),
            'observation_pass' => $pass,
        ];
    }

    private function candidateRuntimeObservationScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c151_role' => 'primary_live_runtime_candidate_observed_active',
                'live_runtime_active' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c151_role' => 'backup_live_runtime_standby_candidate_observed_active',
                'live_runtime_standby_active' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c151_role' => 'comparator_only_candidate',
                'live_runtime_active' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function outputGenerationGuardSummary(array $c150, array $state): array
    {
        return [
            'official_output_generation_guard_reviewed' => true,
            'c150_official_output_generated' => (bool) ($c150['weekly_swing_watchlist_official_output_generated'] ?? false),
            'runtime_state_official_output_generated' => (bool) ($state['weekly_swing_watchlist_official_output_generated'] ?? false),
            'runtime_state_official_output_published' => (bool) ($state['weekly_swing_watchlist_official_output_published'] ?? false),
            'runtime_state_live_recommendation_generated' => (bool) ($state['weekly_swing_watchlist_live_recommendation_generated'] ?? false),
            'official_generation_still_deferred' => ! (bool) ($state['weekly_swing_watchlist_official_output_generated'] ?? false),
            'publication_still_deferred' => ! (bool) ($state['weekly_swing_watchlist_official_output_published'] ?? false),
        ];
    }

    private function planConfirmObservationSummary(array $c150, array $state): array
    {
        return [
            'plan_confirm_observed' => true,
            'c150_plan_confirm_mutated' => (bool) ($c150['plan_confirm_mutated'] ?? false),
            'runtime_state_plan_confirm_mutated' => (bool) ($state['plan_confirm_mutated'] ?? false),
            'runtime_state_plan_confirm_reads_activated_catalog' => (bool) ($state['plan_confirm_runtime_reads_activated_catalog'] ?? false),
            'plan_confirm_runtime_default_path_changed' => false,
            'plan_confirm_output_changed_by_c151' => false,
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

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-39_C151_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW',
            'c150_final_execution_carried_forward' => true,
            'runtime_state_observed_active' => $pass,
            'runtime_bridge_active' => $pass,
            'weekly_swing_watchlist_live_output_enabled' => $pass,
            'official_weekly_swing_output_generated' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C152_RECOMMENDATION : 'C151_TARGETED_C150_RUNTIME_STATE_OBSERVATION_REPAIR',
            'planned_next_scope' => $pass ? 'post-execution observation result review; decide whether active runtime is stable enough for the next controlled output-generation boundary' : 'targeted C150/runtime-state lock, active-state, output guard, or PLAN/CONFIRM repair',
            'planned_next_required_inputs' => $pass ? [
                'locked C151 artifact hash',
                'locked C151 file SHA1',
                'locked C150 artifact hash',
                'locked runtime state hash',
                'runtime bridge active observation',
                'weekly swing live output enabled observation',
                'official output still not generated or published',
                'PLAN/CONFIRM unchanged observation',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $c150Load, array $runtimeLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c150_convert_from_json_pass' => $c150Load['convert_from_json_pass'],
            'runtime_state_convert_from_json_pass' => $runtimeLoad['convert_from_json_pass'],
            'c150_top_level_case_insensitive_duplicate_keys' => $c150Load['case_insensitive_duplicate_keys'],
            'runtime_state_top_level_case_insensitive_duplicate_keys' => $runtimeLoad['case_insensitive_duplicate_keys'],
            'c150_artifact_not_modified' => true,
            'runtime_state_not_modified_by_c151' => true,
            'c151_is_observation_only_not_output_generation' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C151 validates C150 artifact_hash and file SHA1 locks before observation is recorded.',
            'C151 validates the runtime_state_hash and runtime state file SHA1.',
            'C151 observes runtime bridge active and weekly swing live output enabled.',
            'C151 confirms official output generation, official publication, and live recommendation generation remain false.',
            'C151 confirms PLAN/CONFIRM is not mutated and does not read the activated catalog by default.',
            'C151 does not modify C150 artifact, runtime state, config defaults, or PLAN/CONFIRM.',
            'C151 keeps E02 primary, B01 backup standby, and A01 comparator-only.',
        ];
    }

    private function sourceArtifactLocks(array $c150Load, array $runtimeLoad): array
    {
        return [
            'c150' => [
                'artifact_path' => $c150Load['path'],
                'expected_artifact_hash' => $c150Load['expected_hash'],
                'actual_artifact_hash' => $c150Load['actual_hash'],
                'artifact_hash_match' => $c150Load['hash_match'],
                'expected_file_sha1' => $c150Load['expected_file_sha1'],
                'actual_file_sha1' => $c150Load['actual_file_sha1'],
                'file_sha1_match' => $c150Load['file_sha1_match'],
                'convert_from_json_pass' => $c150Load['convert_from_json_pass'],
            ],
            'runtime_state' => [
                'runtime_state_path' => $runtimeLoad['path'],
                'expected_runtime_state_hash' => $runtimeLoad['expected_hash'],
                'actual_runtime_state_hash' => $runtimeLoad['actual_hash'],
                'runtime_state_hash_match' => $runtimeLoad['hash_match'],
                'expected_runtime_state_file_sha1' => $runtimeLoad['expected_file_sha1'],
                'actual_runtime_state_file_sha1' => $runtimeLoad['actual_file_sha1'],
                'runtime_state_file_sha1_match' => $runtimeLoad['file_sha1_match'],
                'convert_from_json_pass' => $runtimeLoad['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $c150Load, array $runtimeLoad): array
    {
        return [
            'expected_c150_hash' => $c150Load['expected_hash'],
            'actual_c150_hash' => $c150Load['actual_hash'],
            'c150_hash_match' => $c150Load['hash_match'],
            'expected_c150_file_sha1' => $c150Load['expected_file_sha1'],
            'actual_c150_file_sha1' => $c150Load['actual_file_sha1'],
            'c150_file_sha1_match' => $c150Load['file_sha1_match'],
            'c150_convert_from_json_pass' => $c150Load['convert_from_json_pass'],
            'expected_runtime_state_hash' => $runtimeLoad['expected_hash'],
            'actual_runtime_state_hash' => $runtimeLoad['actual_hash'],
            'runtime_state_hash_match' => $runtimeLoad['hash_match'],
            'expected_runtime_state_file_sha1' => $runtimeLoad['expected_file_sha1'],
            'actual_runtime_state_file_sha1' => $runtimeLoad['actual_file_sha1'],
            'runtime_state_file_sha1_match' => $runtimeLoad['file_sha1_match'],
            'runtime_state_convert_from_json_pass' => $runtimeLoad['convert_from_json_pass'],
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
