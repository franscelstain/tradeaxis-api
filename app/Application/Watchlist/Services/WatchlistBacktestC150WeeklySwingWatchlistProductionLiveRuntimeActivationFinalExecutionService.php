<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC150WeeklySwingWatchlistProductionLiveRuntimeActivationFinalExecutionService
{
    public const RUN_CODE = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION';
    public const PHASE_LABEL = 'PR-38 / C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION';
    public const ARTIFACT_TYPE = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION';

    public const DEFAULT_C149_ARTIFACT = 'storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C149_HASH = '311898597454a6a1984f4ed84473ad52ba6859fb';
    public const DEFAULT_EXPECTED_C149_FILE_SHA1 = '3B14776D36FBC922782B332BDC55CE90B50188E5';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution.json';
    public const DEFAULT_RUNTIME_STATE_PATH = 'storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C149_STATUS = 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_C150_FINAL_ACTIVATION_EXECUTION';
    private const EXPECTED_C149_PHASE_LABEL = 'PR-37 / C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    private const EXPECTED_C149_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C151_RECOMMENDATION = 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW';

    private const PASS_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_PASSED_LIVE_RUNTIME_BRIDGE_ACTIVE_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const EXPLICIT_ENABLEMENT_MISSING_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_EXPLICIT_RUNTIME_ENABLEMENT_MISSING';
    private const ROLLBACK_OR_KILL_SWITCH_MISSING_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_ROLLBACK_OR_KILL_SWITCH_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C149_LOCK_MISMATCH_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_ARTIFACT_LOCK_MISMATCH';
    private const C149_FILE_SHA1_MISMATCH_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_FILE_SHA1_LOCK_MISMATCH';
    private const C149_STATUS_MISMATCH_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_STATUS_MISMATCH';
    private const C149_PHASE_LABEL_MISMATCH_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_PHASE_LABEL_MISMATCH';
    private const C149_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_NEXT_RECOMMENDATION_MISMATCH';
    private const C149_FINAL_EXECUTION_INCOMPLETE_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_FINAL_EXECUTION_READINESS_INCOMPLETE';
    private const C149_CONVERT_FROM_JSON_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const SOURCE_ALREADY_LIVE_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_C149_ALREADY_LIVE_OR_MUTATING';
    private const CONFIG_KILL_SWITCH_ON_STATUS = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_CONFIG_KILL_SWITCH_ON';

    private const REQUIRED_C149_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed',
        'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass',
        'production_live_runtime_activation_operator_go_no_go_review_pass',
        'operator_decision_recorded',
        'operator_decision_confirmed',
        'weekly_swing_watchlist_ready_for_production_live_runtime_activation_final_execution',
        'ready_for_production_live_runtime_activation_final_execution',
        'production_live_runtime_activation_operator_go_no_go_manifest_created',
        'production_live_runtime_activation_final_execution_allowed_next',
        'c148_lock_valid',
        'c148_activation_observation_result_review_valid',
        'c148_convert_from_json_pass',
        'c147_activation_observation_review_valid',
        'c146_activation_execution_review_valid',
        'c145_activation_authorization_valid',
        'c144_pre_activation_boundary_valid',
        'c143_go_decision_finalization_valid',
        'c142_activation_operator_go_no_go_valid',
        'c141_activation_observation_result_review_valid',
        'c140_activation_observation_review_valid',
        'c139_activation_execution_review_valid',
        'c138_activation_authorization_valid',
        'c137_pre_activation_boundary_valid',
        'c136_go_decision_finalization_valid',
        'c135_activation_operator_go_no_go_valid',
        'c134_activation_observation_result_review_valid',
        'c133_activation_observation_review_valid',
        'c132_activation_execution_review_valid',
        'c131_activation_approval_valid',
        'c130_activation_readiness_valid',
        'c129_final_closure_valid',
        'activation_authorized',
        'primary_candidate_activation_authorized',
        'backup_candidate_activation_authorized',
        'c148_observation_result_review_only',
        'c148_not_live_runtime_state_change',
        'c149_operator_go_no_go_review_only',
        'c149_not_live_runtime_state_change',
        'primary_candidate_ready_for_production_live_runtime_activation_final_execution',
        'backup_candidate_ready_for_production_live_runtime_activation_final_execution',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C149_FALSE_FIELDS = [
        'operator_no_go_decision',
        'operator_hold_decision',
        'production_live_runtime_activation_stopped_no_go',
        'production_live_runtime_activation_deferred_hold',
        'production_live_runtime_activation_executed',
        'comparator_candidate_activation_authorized',
        'comparator_candidate_ready_for_production_live_runtime_activation_final_execution',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'production_deployment_allowed',
        'production_deployment_executed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'runtime_bridge_active',
        'controlled_rollout_active',
        'pilot_runtime_active',
        'shadow_runtime_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
    ];

    private const C149_PRE_EXECUTION_FALSE_GUARDS = [
        'production_live_runtime_activation_executed',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_executed',
        'production_deployment_allowed',
        'production_deployment_executed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'runtime_bridge_active',
        'controlled_rollout_active',
        'pilot_runtime_active',
        'shadow_runtime_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
        'weekly_swing_watchlist_production_live_runtime_activation_final_execution_context_persisted_to_live_runtime',
        'production_live_runtime_activation_final_execution_context_persisted_to_live_runtime',
    ];

    private const FEATURE_FLAG_KEYS = [
        'production_catalog_runtime_bridge_enabled',
        'production_catalog_controlled_opt_in_runtime_bridge_enabled',
        'production_catalog_controlled_runtime_opt_in_pilot_enabled',
        'production_catalog_controlled_shadow_rollout_enabled',
        'production_catalog_controlled_parallel_run_enabled',
        'production_catalog_controlled_rollout_enabled',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/*no-*-test.json',
        'storage/app/watchlist/backtest/*missing-*-test.json',
        'storage/app/watchlist/backtest/*mismatch-*-test.json',
        'storage/app/watchlist/backtest/*negative-*-test.json',
        'storage/app/watchlist/backtest/*invalid-*-test.json',
    ];

    public function execute(
        string $c149Artifact = self::DEFAULT_C149_ARTIFACT,
        string $expectedC149Hash = self::DEFAULT_EXPECTED_C149_HASH,
        string $expectedC149FileSha1 = self::DEFAULT_EXPECTED_C149_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        string $runtimeStatePath = self::DEFAULT_RUNTIME_STATE_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $overwriteRuntimeState = (bool) ($options['overwrite_runtime_state'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')), $runtimeStatePath);
        $load = $this->loadArtifactLock($c149Artifact, $expectedC149Hash, $expectedC149FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C149_LOCK_MISMATCH_STATUS, 'C149 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c149_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C149_CONVERT_FROM_JSON_STATUS, 'C149 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C149_LOCK_MISMATCH_STATUS, 'C149 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C149_FILE_SHA1_MISMATCH_STATUS, 'C149 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c149 = $load['payload'];
        if (($c149['status'] ?? null) !== self::EXPECTED_C149_STATUS || ($c149['reason_code'] ?? null) !== self::EXPECTED_C149_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C149_STATUS_MISMATCH_STATUS, 'C149 status/reason is not final-execution ready GO.', $outputPath, $overwrite);
        }
        if (($c149['phase_label'] ?? null) !== self::EXPECTED_C149_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C149_PHASE_LABEL_MISMATCH_STATUS, 'C149 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c149NextRecommendationMatches($c149)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C149_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C149 next recommendation is not C150.', $outputPath, $overwrite);
        }
        if (! $this->c149FinalExecutionReady($c149)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C149_FINAL_EXECUTION_INCOMPLETE_STATUS, 'C149 final execution readiness evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c149)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C149 candidate scope does not match locked final execution scope.', $outputPath, $overwrite);
        }

        $sourceLiveFailure = $this->firstPreExecutionLiveOrMutatingFlag($c149);
        if ($sourceLiveFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c149_pre_execution_live_or_mutating_flag_failure'] = $sourceLiveFailure;

            return $this->rejected($artifact, self::SOURCE_ALREADY_LIVE_STATUS, 'C149 already contains live, mutating, production, or weekly-live state before C150.', $outputPath, $overwrite);
        }
        if ($this->configFlagIsOn('production_catalog_runtime_bridge_kill_switch')) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CONFIG_KILL_SWITCH_ON_STATUS, 'C150 cannot execute while production_catalog_runtime_bridge_kill_switch is enabled.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['activation_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C150 requires --operator-approved and non-empty --activation-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['enable_runtime_bridge'] ?? false) || ! (bool) ($options['enable_live_output'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::EXPLICIT_ENABLEMENT_MISSING_STATUS, 'C150 requires explicit --enable-runtime-bridge and --enable-live-output.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['confirm_rollback'] ?? false) || ! (bool) ($options['confirm_kill_switch'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::ROLLBACK_OR_KILL_SWITCH_MISSING_STATUS, 'C150 requires --confirm-rollback and --confirm-kill-switch.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $runtimeState = $this->runtimeStatePayload($load, $options, $runtimeStatePath, (string) ($artifact['created_at'] ?? gmdate('c')));
        $runtimeState = $this->writeRuntimeStateAndReturn($runtimeState, $runtimeStatePath, $overwriteRuntimeState);
        $artifact = $this->completeSections(array_merge($artifact, [
            'runtime_state_path' => $runtimeStatePath,
            'runtime_state_hash' => $runtimeState['runtime_state_hash'] ?? null,
            'runtime_state_write_skipped_existing_output' => (bool) ($runtimeState['write_skipped_existing_output'] ?? false),
        ]), $load, $options, true);

        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C150 executes final production/live runtime activation by writing the explicit runtime activation state. Runtime bridge and weekly swing live output are active in the C150 runtime state; PLAN/CONFIRM mutation, official output generation, and publication remain separate and are not executed by C150.';
        $artifact['diagnostic_conclusion'] = 'C150_FINAL_ACTIVATION_EXECUTED_RUNTIME_BRIDGE_ACTIVE_LIVE_OUTPUT_ENABLED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::C151_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt, string $runtimeStatePath): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-38',
            'internal_checkpoint' => 'C150',
            'status' => 'C150_NOT_RUN',
            'reason_code' => 'C150_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'runtime_state_path' => $runtimeStatePath,
            'runtime_state_hash' => null,
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_pass' => false,
            'production_live_runtime_activation_final_execution_pass' => false,
            'production_live_runtime_activation_executed' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
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
            'c149_lock_valid' => false,
            'c149_operator_go_no_go_valid' => false,
            'c149_convert_from_json_pass' => false,
            'c148_activation_observation_result_review_valid' => false,
            'c147_activation_observation_review_valid' => false,
            'c146_activation_execution_review_valid' => false,
            'c145_activation_authorization_valid' => false,
            'c144_pre_activation_boundary_valid' => false,
            'c143_go_decision_finalization_valid' => false,
            'c142_activation_operator_go_no_go_valid' => false,
            'c141_activation_observation_result_review_valid' => false,
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
            'activation_reference' => '',
            'runtime_bridge_enablement_confirmed' => false,
            'live_output_enablement_confirmed' => false,
            'rollback_confirmed' => false,
            'kill_switch_confirmed' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_final_execution_pass' => true,
            'production_live_runtime_activation_final_execution_pass' => true,
            'production_live_runtime_activation_executed' => true,
            'production_ready' => true,
            'production_catalog_runtime_wired' => true,
            'production_runtime_wiring_allowed' => true,
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
            'c149_lock_valid' => true,
            'c149_operator_go_no_go_valid' => true,
            'c149_convert_from_json_pass' => true,
            'c148_activation_observation_result_review_valid' => true,
            'c147_activation_observation_review_valid' => true,
            'c146_activation_execution_review_valid' => true,
            'c145_activation_authorization_valid' => true,
            'c144_pre_activation_boundary_valid' => true,
            'c143_go_decision_finalization_valid' => true,
            'c142_activation_operator_go_no_go_valid' => true,
            'c141_activation_observation_result_review_valid' => true,
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

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c149 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c149_lock_validation_summary'] = $this->c149LockValidationSummary($load, $c149);
        $artifact['c149_operator_go_no_go_carry_forward_summary'] = $this->c149CarryForwardSummary($c149, $pass);
        $artifact['explicit_enablement_summary'] = $this->explicitEnablementSummary($options, $pass);
        $artifact['runtime_activation_execution_manifest'] = $this->runtimeActivationExecutionManifest($load, $options, $artifact, $pass);
        $artifact['weekly_swing_watchlist_runtime_state_summary'] = $this->runtimeStateSummary($artifact, $pass);
        $artifact['candidate_runtime_activation_scorecard'] = $this->candidateRuntimeActivationScorecard($pass);
        $artifact['plan_confirm_boundary_summary'] = $this->planConfirmBoundarySummary();
        $artifact['runtime_config_boundary_summary'] = $this->runtimeConfigBoundarySummary();
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['production_activation_safety_summary'] = $this->productionActivationSafetySummary($pass);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C150_PENDING')]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['activation_reference'] = (string) ($options['activation_reference'] ?? '');
        $artifact['runtime_bridge_enablement_confirmed'] = (bool) ($options['enable_runtime_bridge'] ?? false);
        $artifact['live_output_enablement_confirmed'] = (bool) ($options['enable_live_output'] ?? false);
        $artifact['rollback_confirmed'] = (bool) ($options['confirm_rollback'] ?? false);
        $artifact['kill_switch_confirmed'] = (bool) ($options['confirm_kill_switch'] ?? false);
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($pass) {
            $artifact = array_merge($artifact, $this->passingTopLevelState());
        }

        return $artifact;
    }

    private function c149FinalExecutionReady(array $c149): bool
    {
        if (($c149['operator_decision'] ?? null) !== 'GO' || ($c149['operator_go_decision'] ?? null) !== 'GO') {
            return false;
        }
        foreach (self::REQUIRED_C149_TRUE_FIELDS as $field) {
            if (! (bool) ($c149[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C149_FALSE_FIELDS as $field) {
            if ((bool) ($c149[$field] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function c149NextRecommendationMatches(array $c149): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_concrete_activation_step_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c149, $path);
            if ($value !== null && $value !== self::EXPECTED_C149_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return ($c149['next_step_recommendation'] ?? null) === self::EXPECTED_C149_NEXT_RECOMMENDATION;
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
        foreach ([
            'a01_promoted',
            'candidate_promotion_executed',
            'candidate_rerank_executed',
            'strategy_retune_executed',
            'scoring_mutation_executed',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'weekly_swing_live_recommendation_selection_executed',
        ] as $field) {
            if ((bool) ($source[$field] ?? false)) {
                return false;
            }
        }

        return (bool) ($source['a01_remains_comparator_only'] ?? false);
    }

    private function firstPreExecutionLiveOrMutatingFlag(array $source): ?string
    {
        foreach (self::C149_PRE_EXECUTION_FALSE_GUARDS as $field) {
            if ((bool) ($source[$field] ?? false)) {
                return $field;
            }
        }

        return null;
    }

    private function c149LockValidationSummary(array $load, array $c149): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C149',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C149_STATUS,
            'actual_status' => $c149['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C149_PHASE_LABEL,
            'actual_phase_label' => $c149['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C149_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c149NextRecommendationMatches($c149),
            'c149_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c149CarryForwardSummary(array $c149, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'operator_decision' => $c149['operator_decision'] ?? null,
            'operator_decision_confirmed' => (bool) ($c149['operator_decision_confirmed'] ?? false),
            'ready_for_production_live_runtime_activation_final_execution' => (bool) ($c149['ready_for_production_live_runtime_activation_final_execution'] ?? false),
            'production_live_runtime_activation_final_execution_allowed_next' => (bool) ($c149['production_live_runtime_activation_final_execution_allowed_next'] ?? false),
            'c148_activation_observation_result_review_valid' => (bool) ($c149['c148_activation_observation_result_review_valid'] ?? false),
            'c147_activation_observation_review_valid' => (bool) ($c149['c147_activation_observation_review_valid'] ?? false),
            'activation_authorized' => (bool) ($c149['activation_authorized'] ?? false),
            'primary_candidate_activation_authorized' => (bool) ($c149['primary_candidate_activation_authorized'] ?? false),
            'backup_candidate_activation_authorized' => (bool) ($c149['backup_candidate_activation_authorized'] ?? false),
            'c149_final_execution_ready' => $this->c149FinalExecutionReady($c149),
            'c150_final_execution_can_start' => $pass,
        ];
    }

    private function explicitEnablementSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'activation_reference_required' => true,
            'activation_reference' => (string) ($options['activation_reference'] ?? ''),
            'activation_reference_present' => trim((string) ($options['activation_reference'] ?? '')) !== '',
            'runtime_bridge_enablement_required' => true,
            'runtime_bridge_enablement_confirmed' => (bool) ($options['enable_runtime_bridge'] ?? false),
            'live_output_enablement_required' => true,
            'live_output_enablement_confirmed' => (bool) ($options['enable_live_output'] ?? false),
            'rollback_confirmation_required' => true,
            'rollback_confirmed' => (bool) ($options['confirm_rollback'] ?? false),
            'kill_switch_confirmation_required' => true,
            'kill_switch_confirmed' => (bool) ($options['confirm_kill_switch'] ?? false),
            'explicit_enablement_validation_pass' => $pass,
        ];
    }

    private function runtimeActivationExecutionManifest(array $load, array $options, array $artifact, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'production_live_runtime_activation_final_execution',
            'source_artifact' => 'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['expected_hash'],
            'source_file_sha1' => $load['expected_file_sha1'],
            'activation_reference' => (string) ($options['activation_reference'] ?? ''),
            'runtime_state_path' => (string) ($artifact['runtime_state_path'] ?? self::DEFAULT_RUNTIME_STATE_PATH),
            'runtime_state_hash' => $artifact['runtime_state_hash'] ?? null,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'production_live_runtime_activation_executed' => $pass,
            'production_catalog_runtime_wired' => $pass,
            'runtime_bridge_active' => $pass,
            'weekly_swing_watchlist_runtime_active' => $pass,
            'weekly_swing_watchlist_live_output_enabled' => $pass,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => $pass,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function runtimeStatePayload(array $load, array $options, string $runtimeStatePath, string $createdAt): array
    {
        $payload = [
            'runtime_state_type' => 'weekly_swing_watchlist_production_live_runtime_activation_state',
            'runtime_state_hash' => null,
            'created_at' => $createdAt,
            'source_run_code' => self::RUN_CODE,
            'source_phase_label' => self::PHASE_LABEL,
            'source_c149_artifact_path' => $load['path'],
            'source_c149_artifact_hash' => $load['expected_hash'],
            'source_c149_file_sha1' => $load['expected_file_sha1'],
            'activation_reference' => (string) ($options['activation_reference'] ?? ''),
            'runtime_state_path' => $runtimeStatePath,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
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
            'kill_switch_confirmed' => true,
            'rollback_confirmed' => true,
        ];
        $hashPayload = $payload;
        $hashPayload['runtime_state_hash'] = null;
        $payload['runtime_state_hash'] = sha1(json_encode($hashPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $payload;
    }

    private function writeRuntimeStateAndReturn(array $runtimeState, string $runtimeStatePath, bool $overwrite): array
    {
        if (is_file($runtimeStatePath) && ! $overwrite) {
            $runtimeState['write_skipped_existing_output'] = true;

            return $runtimeState;
        }
        $dir = dirname($runtimeStatePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($runtimeStatePath, json_encode($runtimeState, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);

        return $runtimeState;
    }

    private function runtimeStateSummary(array $artifact, bool $pass): array
    {
        return [
            'runtime_state_created' => $pass,
            'runtime_state_path' => (string) ($artifact['runtime_state_path'] ?? self::DEFAULT_RUNTIME_STATE_PATH),
            'runtime_state_hash' => $artifact['runtime_state_hash'] ?? null,
            'runtime_bridge_active' => $pass,
            'weekly_swing_watchlist_runtime_active' => $pass,
            'weekly_swing_watchlist_live_output_enabled' => $pass,
            'official_output_generated_by_c150' => false,
            'official_output_published_by_c150' => false,
            'live_recommendation_generated_by_c150' => false,
        ];
    }

    private function candidateRuntimeActivationScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c150_role' => 'primary_live_runtime_candidate',
                'live_runtime_active' => $pass,
                'official_output_generated_by_c150' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c150_role' => 'backup_live_runtime_standby_candidate',
                'live_runtime_standby_active' => $pass,
                'official_output_generated_by_c150' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c150_role' => 'comparator_only_candidate',
                'live_runtime_active' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function planConfirmBoundarySummary(): array
    {
        return [
            'plan_confirm_boundary_reviewed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'plan_confirm_output_changed_by_c150' => false,
        ];
    }

    private function runtimeConfigBoundarySummary(): array
    {
        $flags = [];
        foreach (self::FEATURE_FLAG_KEYS as $key) {
            $flags[$key] = $this->configFlagIsOn($key);
        }

        return [
            'runtime_config_reviewed' => true,
            'production_config_default_unchanged' => true,
            'config_feature_flags' => $flags,
            'runtime_bridge_enablement_source' => 'explicit_c150_command_flag_and_runtime_state_artifact',
            'production_catalog_runtime_bridge_kill_switch' => $this->configFlagIsOn('production_catalog_runtime_bridge_kill_switch'),
            'config_file_mutated_by_c150' => false,
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

    private function productionActivationSafetySummary(bool $pass): array
    {
        return [
            'activation_executed' => $pass,
            'runtime_bridge_active' => $pass,
            'weekly_swing_watchlist_live_output_enabled' => $pass,
            'rollback_confirmed' => $pass,
            'kill_switch_confirmed' => $pass,
            'official_output_generated_by_c150' => false,
            'official_output_published_by_c150' => false,
            'plan_confirm_mutated_by_c150' => false,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-38_C150_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION',
            'c149_operator_go_no_go_carried_forward' => true,
            'c150_final_execution_executed' => $pass,
            'runtime_bridge_active' => $pass,
            'weekly_swing_watchlist_live_output_enabled' => $pass,
            'official_weekly_swing_output_generated' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C151_RECOMMENDATION : 'C150_TARGETED_C149_FINAL_EXECUTION_READINESS_REPAIR',
            'planned_next_scope' => $pass ? 'post-execution observation review of active runtime state; not another GO/NO-GO loop' : 'targeted source lock, explicit enablement, rollback, kill-switch, or cleanup repair',
            'planned_next_required_inputs' => $pass ? [
                'locked C150 artifact hash',
                'locked C150 file SHA1',
                'locked runtime state hash',
                'runtime bridge active evidence',
                'weekly swing live output enabled evidence',
                'PLAN/CONFIRM unchanged evidence',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c149_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c149_artifact_not_modified' => true,
            'c60_c149_artifacts_not_modified' => true,
            'c150_is_final_activation_execution' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C150 validates C149 artifact_hash and file SHA1 locks before final activation execution.',
            'C150 requires operator approval, activation reference, explicit runtime bridge enablement, explicit live output enablement, rollback confirmation, and kill-switch confirmation.',
            'C150 writes the weekly swing watchlist production/live runtime activation state.',
            'C150 activates runtime bridge and weekly swing live output in the runtime state.',
            'C150 does not generate or publish the official weekly swing recommendation list.',
            'C150 does not mutate PLAN/CONFIRM and does not make PLAN/CONFIRM read the activated catalog by default.',
            'C150 keeps E02 primary, B01 backup standby, and A01 comparator-only.',
            'C150 next step is post-execution observation review, not another decision loop.',
        ];
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

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c149' => [
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
            'expected_c149_hash' => $load['expected_hash'],
            'actual_c149_hash' => $load['actual_hash'],
            'c149_hash_match' => $load['hash_match'],
            'expected_c149_file_sha1' => $load['expected_file_sha1'],
            'actual_c149_file_sha1' => $load['actual_file_sha1'],
            'c149_file_sha1_match' => $load['file_sha1_match'],
            'c149_convert_from_json_pass' => $load['convert_from_json_pass'],
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

    private function watchlistConfig(): array
    {
        $path = 'config/watchlist.php';
        if (! is_file($path)) {
            return [];
        }
        $config = require $path;

        return is_array($config) ? $config : [];
    }

    private function configFlagIsOn(string $key): bool
    {
        return (bool) ($this->watchlistConfig()[$key] ?? false);
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
