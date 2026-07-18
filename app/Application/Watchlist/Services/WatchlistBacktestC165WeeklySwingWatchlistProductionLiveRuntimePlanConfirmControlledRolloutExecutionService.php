<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutExecutionService
{
    public const RUN_CODE = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION';
    public const PHASE_LABEL = 'PR-87 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION';
    public const ARTIFACT_TYPE = self::RUN_CODE;

    public const DEFAULT_C165_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review.json';
    public const DEFAULT_EXPECTED_C165_BOUNDARY_HASH = '11eca01c5c5cc071c9d61dcf04b2004923f4772f';
    public const DEFAULT_EXPECTED_C165_BOUNDARY_FILE_SHA1 = '4391205D3732CC475FB37E518678EAB607F5CAB0';
    public const DEFAULT_ACTIVATED_CATALOG_ARTIFACT = 'storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json';
    public const DEFAULT_EXPECTED_ACTIVATED_CATALOG_HASH = '54145854758e22115e4b65a297e4c157d94c638d';
    public const DEFAULT_EXPECTED_ACTIVATED_CATALOG_FILE_SHA1 = '209E3225F37015DA348EC2DA9A0D6A3FFCC6E4F7';
    public const DEFAULT_CONTROLLED_COMPLETION_ARTIFACT = 'storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json';
    public const DEFAULT_EXPECTED_CONTROLLED_COMPLETION_HASH = 'e9862d9e7738d0558f107d978f329f97f14b3520';
    public const DEFAULT_EXPECTED_CONTROLLED_COMPLETION_FILE_SHA1 = 'AB9FC9F714339B78D68132222AC8C398BE7EE1B3';
    public const DEFAULT_RUNTIME_ACTIVATION_STATE = 'storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json';
    public const DEFAULT_EXPECTED_RUNTIME_ACTIVATION_STATE_HASH = '00cb935a8252efe340d5f6ec6ea6966d9645cff7';
    public const DEFAULT_EXPECTED_RUNTIME_ACTIVATION_STATE_FILE_SHA1 = '17E41FFC5C6EE00CCCB4DF555A22EF192F2FCCF4';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution.json';
    public const DEFAULT_ROLLOUT_STATE_OUTPUT_PATH = 'storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const WATCHLIST_FUNCTION = 'CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION';
    private const BOUNDARY_RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY';
    private const EXECUTION_RUNTIME_MODE = 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_ACTIVE';

    private const EXPECTED_BOUNDARY_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_ROLLOUT_EXECUTION_PRIMARY_AND_BACKUP';
    private const EXPECTED_BOUNDARY_PHASE = 'PR-86 / C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW';
    private const EXPECTED_C68_STATUS = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_COMPLETION_TYPE = 'C161_WEEKLY_SWING_WATCHLIST_CONTROLLED_PLAN_CONFIRM_COMPLETION';
    private const EXPECTED_RUNTIME_STATE_TYPE = 'weekly_swing_watchlist_production_live_runtime_activation_state';
    private const EXPECTED_RUNTIME_STATE_SOURCE = 'C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION';
    private const NEXT_RESULT_REVIEW = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW';

    private const PASS_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_PASSED_CONTROLLED_ROLLOUT_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const BOUNDARY_LOCK_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_C165_BOUNDARY_ARTIFACT_LOCK_MISMATCH';
    private const BOUNDARY_SHA_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_C165_BOUNDARY_FILE_SHA1_LOCK_MISMATCH';
    private const BOUNDARY_JSON_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_C165_BOUNDARY_JSON_COMPATIBILITY_VIOLATION';
    private const BOUNDARY_STATE_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_C165_BOUNDARY_STATE_INVALID';
    private const CATALOG_LOCK_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_ACTIVATED_CATALOG_ARTIFACT_LOCK_MISMATCH';
    private const CATALOG_SHA_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_ACTIVATED_CATALOG_FILE_SHA1_LOCK_MISMATCH';
    private const CATALOG_STATE_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_ACTIVATED_CATALOG_STATE_INVALID';
    private const COMPLETION_LOCK_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_CONTROLLED_COMPLETION_ARTIFACT_LOCK_MISMATCH';
    private const COMPLETION_SHA_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_CONTROLLED_COMPLETION_FILE_SHA1_LOCK_MISMATCH';
    private const COMPLETION_STATE_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_CONTROLLED_COMPLETION_STATE_INVALID';
    private const RUNTIME_LOCK_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_RUNTIME_ACTIVATION_STATE_LOCK_MISMATCH';
    private const RUNTIME_SHA_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_RUNTIME_ACTIVATION_STATE_FILE_SHA1_LOCK_MISMATCH';
    private const RUNTIME_STATE_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_RUNTIME_ACTIVATION_STATE_INVALID';
    private const APPROVAL_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const EXECUTION_CONFIRMATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_CONTROLLED_ROLLOUT_EXECUTION_CONFIRMATION_MISSING';
    private const BOUNDARY_CONFIRMATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_C165_BOUNDARY_LOCK_CONFIRMATION_MISSING';
    private const CATALOG_READ_CONFIRMATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_ACTIVATED_CATALOG_READ_CONFIRMATION_MISSING';
    private const PLAN_MUTATION_CONFIRMATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_PLAN_CONFIRM_CONTROLLED_MUTATION_CONFIRMATION_MISSING';
    private const CONTROLLED_ONLY_CONFIRMATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_CONTROLLED_ROLLOUT_ONLY_CONFIRMATION_MISSING';
    private const KILL_SWITCH_CONFIRMATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_KILL_SWITCH_CONFIRMATION_MISSING';
    private const ROLLBACK_CONFIRMATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_ROLLBACK_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_ARTIFACT_STATUS = 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c165-*controlled-rollout-execution*-negative-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-execution*-missing-*-test.json',
        'storage/app/watchlist/backtest/c165-*controlled-rollout-execution*-mismatch-*-test.json',
        'storage/app/watchlist/runtime/c165-*controlled-rollout*-negative-*-test.json',
    ];

    public function execute(
        string $c165BoundaryArtifact = self::DEFAULT_C165_BOUNDARY_ARTIFACT,
        string $expectedC165BoundaryHash = self::DEFAULT_EXPECTED_C165_BOUNDARY_HASH,
        string $expectedC165BoundaryFileSha1 = self::DEFAULT_EXPECTED_C165_BOUNDARY_FILE_SHA1,
        string $activatedCatalogArtifact = self::DEFAULT_ACTIVATED_CATALOG_ARTIFACT,
        string $expectedActivatedCatalogHash = self::DEFAULT_EXPECTED_ACTIVATED_CATALOG_HASH,
        string $expectedActivatedCatalogFileSha1 = self::DEFAULT_EXPECTED_ACTIVATED_CATALOG_FILE_SHA1,
        string $controlledCompletionArtifact = self::DEFAULT_CONTROLLED_COMPLETION_ARTIFACT,
        string $expectedControlledCompletionHash = self::DEFAULT_EXPECTED_CONTROLLED_COMPLETION_HASH,
        string $expectedControlledCompletionFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_COMPLETION_FILE_SHA1,
        string $runtimeActivationState = self::DEFAULT_RUNTIME_ACTIVATION_STATE,
        string $expectedRuntimeActivationStateHash = self::DEFAULT_EXPECTED_RUNTIME_ACTIVATION_STATE_HASH,
        string $expectedRuntimeActivationStateFileSha1 = self::DEFAULT_EXPECTED_RUNTIME_ACTIVATION_STATE_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        string $rolloutStateOutputPath = self::DEFAULT_ROLLOUT_STATE_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt);
        $loads = [
            'boundary' => $this->loadJsonLock($c165BoundaryArtifact, $expectedC165BoundaryHash, $expectedC165BoundaryFileSha1, 'artifact_hash'),
            'catalog' => $this->loadJsonLock($activatedCatalogArtifact, $expectedActivatedCatalogHash, $expectedActivatedCatalogFileSha1, 'artifact_hash'),
            'completion' => $this->loadJsonLock($controlledCompletionArtifact, $expectedControlledCompletionHash, $expectedControlledCompletionFileSha1, 'controlled_completion_hash'),
            'runtime' => $this->loadJsonLock($runtimeActivationState, $expectedRuntimeActivationStateHash, $expectedRuntimeActivationStateFileSha1, 'runtime_state_hash'),
        ];
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($loads);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($loads));

        foreach ([
            ['boundary', self::BOUNDARY_LOCK_STATUS, self::BOUNDARY_SHA_STATUS, self::BOUNDARY_JSON_STATUS],
            ['catalog', self::CATALOG_LOCK_STATUS, self::CATALOG_SHA_STATUS, self::CATALOG_STATE_STATUS],
            ['completion', self::COMPLETION_LOCK_STATUS, self::COMPLETION_SHA_STATUS, self::COMPLETION_STATE_STATUS],
            ['runtime', self::RUNTIME_LOCK_STATUS, self::RUNTIME_SHA_STATUS, self::RUNTIME_STATE_STATUS],
        ] as [$key, $lockStatus, $shaStatus, $jsonStatus]) {
            $load = $loads[$key];
            if (! $load['exists'] || ! is_array($load['payload']) || ! $load['hash_match']) {
                return $this->finish($this->completeSections($artifact, $loads, $options, false, null), $lockStatus, strtoupper($key).' source artifact is missing or its hash lock does not match.', $outputPath, $overwrite, false);
            }
            if (! $load['file_sha1_match']) {
                return $this->finish($this->completeSections($artifact, $loads, $options, false, null), $shaStatus, strtoupper($key).' source file SHA1 lock does not match.', $outputPath, $overwrite, false);
            }
            if (! $load['convert_from_json_pass']) {
                return $this->finish($this->completeSections($artifact, $loads, $options, false, null), $jsonStatus, strtoupper($key).' source is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite, false);
            }
        }

        $boundary = $loads['boundary']['payload'];
        $catalog = $loads['catalog']['payload'];
        $completion = $loads['completion']['payload'];
        $runtime = $loads['runtime']['payload'];

        if (! $this->boundaryStateValid($boundary)) {
            return $this->finish($this->completeSections($artifact, $loads, $options, false, null), self::BOUNDARY_STATE_STATUS, 'C165 boundary is not valid for same-topic controlled rollout execution.', $outputPath, $overwrite, false);
        }
        if (! $this->catalogStateValid($catalog)) {
            return $this->finish($this->completeSections($artifact, $loads, $options, false, null), self::CATALOG_STATE_STATUS, 'Activated catalog evidence is not valid for the controlled rollout scope.', $outputPath, $overwrite, false);
        }
        if (! $this->completionStateValid($completion)) {
            return $this->finish($this->completeSections($artifact, $loads, $options, false, null), self::COMPLETION_STATE_STATUS, 'Controlled completion payload is invalid or outside primary/backup scope.', $outputPath, $overwrite, false);
        }
        if (! $this->runtimeStateValid($runtime)) {
            return $this->finish($this->completeSections($artifact, $loads, $options, false, null), self::RUNTIME_STATE_STATUS, 'Runtime activation state is invalid for controlled PLAN/CONFIRM rollout.', $outputPath, $overwrite, false);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->finish($this->completeSections($artifact, $loads, $options, false, null), self::APPROVAL_STATUS, 'C165 execution requires operator approval and a non-empty approval reference.', $outputPath, $overwrite, false);
        }

        foreach ([
            'controlled_rollout_execution_confirmed' => [self::EXECUTION_CONFIRMATION_STATUS, 'Controlled rollout execution confirmation is required.'],
            'c165_boundary_locked_confirmed' => [self::BOUNDARY_CONFIRMATION_STATUS, 'C165 boundary lock confirmation is required.'],
            'activated_catalog_read_confirmed' => [self::CATALOG_READ_CONFIRMATION_STATUS, 'Activated catalog read confirmation is required.'],
            'plan_confirm_controlled_mutation_confirmed' => [self::PLAN_MUTATION_CONFIRMATION_STATUS, 'Controlled PLAN/CONFIRM mutation confirmation is required.'],
            'controlled_rollout_only_confirmed' => [self::CONTROLLED_ONLY_CONFIRMATION_STATUS, 'Controlled-rollout-only confirmation is required.'],
            'kill_switch_confirmed' => [self::KILL_SWITCH_CONFIRMATION_STATUS, 'Kill switch confirmation is required.'],
            'rollback_confirmed' => [self::ROLLBACK_CONFIRMATION_STATUS, 'Rollback confirmation is required.'],
            'free_publication_locked_confirmed' => [self::FREE_PUBLICATION_STATUS, 'Free-publication lock confirmation is required.'],
        ] as $option => [$status, $message]) {
            if (! (bool) ($options[$option] ?? false)) {
                return $this->finish($this->completeSections($artifact, $loads, $options, false, null), $status, $message, $outputPath, $overwrite, false);
            }
        }

        $temporaryPaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryPaths !== []) {
            $options['temporary_negative_artifact_paths'] = $temporaryPaths;

            return $this->finish($this->completeSections($artifact, $loads, $options, false, null), self::TEMPORARY_ARTIFACT_STATUS, 'Temporary C165 negative artifact remains.', $outputPath, $overwrite, false);
        }

        $rolloutState = $this->buildRolloutState($loads, $completion, $createdAt, $rolloutStateOutputPath, $options);
        $this->writeJson($rolloutStateOutputPath, $rolloutState, $overwrite);
        $artifact = $this->completeSections($artifact, $loads, $options, true, $rolloutState);
        $artifact = array_merge($artifact, $this->passingState($loads, $rolloutState, $options));

        return $this->finish($artifact, self::PASS_STATUS, 'C165 controlled PLAN/CONFIRM rollout executed for primary and backup with activated-catalog read, controlled mutation, kill switch, rollback, and free-publication lock.', $outputPath, $overwrite, true);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-87',
            'internal_checkpoint' => 'C165',
            'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION',
            'status' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_NOT_RUN',
            'reason_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_pass' => false,
            'production_live_runtime_plan_confirm_controlled_rollout_execution_pass' => false,
            'controlled_rollout_execution_confirmed' => false,
            'controlled_rollout_executed' => false,
            'controlled_rollout_active' => false,
            'controlled_rollout_only' => true,
            'unrestricted_rollout_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'production_config_mutated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::EXECUTION_RUNTIME_MODE,
            'watchlist_function_invoked' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'kill_switch_confirmed' => false,
            'rollback_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_NOT_RUN',
            'next_step_recommendation' => null,
            'message' => '',
        ];
    }

    private function passingState(array $loads, array $rolloutState, array $options): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_execution_pass' => true,
            'production_live_runtime_plan_confirm_controlled_rollout_execution_pass' => true,
            'controlled_rollout_execution_confirmed' => true,
            'controlled_rollout_executed' => true,
            'controlled_rollout_active' => true,
            'controlled_rollout_only' => true,
            'unrestricted_rollout_allowed' => false,
            'plan_confirm_mutation_allowed' => true,
            'plan_confirm_mutated' => true,
            'plan_confirm_runtime_reads_activated_catalog' => true,
            'live_plan_confirm_rollout_allowed' => true,
            'live_plan_confirm_rollout_executed' => true,
            'production_config_mutated' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'watchlist_function_invoked' => true,
            'watchlist_function_primary_candidate_observed' => true,
            'watchlist_function_backup_candidate_observed' => true,
            'watchlist_function_comparator_candidate_observed' => false,
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_result_review' => true,
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_result_review' => true,
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_result_review' => false,
            'kill_switch_confirmed' => true,
            'rollback_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review' => true,
            'c165_boundary_lock_valid' => $this->loadValid($loads['boundary']),
            'activated_catalog_lock_valid' => $this->loadValid($loads['catalog']),
            'controlled_completion_lock_valid' => $this->loadValid($loads['completion']),
            'runtime_activation_state_lock_valid' => $this->loadValid($loads['runtime']),
            'rollout_state_path' => $rolloutState['rollout_state_path'],
            'rollout_state_hash' => $rolloutState['rollout_state_hash'],
            'rollout_state_record_count' => count($rolloutState['rollout_rows']),
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
        ];
    }

    private function completeSections(array $artifact, array $loads, array $options, bool $pass, ?array $rolloutState): array
    {
        $boundary = is_array($loads['boundary']['payload'] ?? null) ? $loads['boundary']['payload'] : [];
        $catalog = is_array($loads['catalog']['payload'] ?? null) ? $loads['catalog']['payload'] : [];
        $completion = is_array($loads['completion']['payload'] ?? null) ? $loads['completion']['payload'] : [];
        $runtime = is_array($loads['runtime']['payload'] ?? null) ? $loads['runtime']['payload'] : [];
        $temporaryPaths = array_values((array) ($options['temporary_negative_artifact_paths'] ?? []));

        $artifact['source_lock_validation_summary'] = [
            'c165_boundary_lock_valid' => $this->loadValid($loads['boundary']),
            'activated_catalog_lock_valid' => $this->loadValid($loads['catalog']),
            'controlled_completion_lock_valid' => $this->loadValid($loads['completion']),
            'runtime_activation_state_lock_valid' => $this->loadValid($loads['runtime']),
            'all_required_source_locks_valid' => $this->allLoadsValid($loads),
        ];
        $artifact['c165_boundary_carry_forward_summary'] = [
            'boundary_status' => $boundary['status'] ?? null,
            'boundary_open' => (bool) ($boundary['controlled_rollout_boundary_open'] ?? false),
            'boundary_state_valid' => $this->boundaryStateValid($boundary),
            'next_recommendation' => $boundary['next_step_recommendation'] ?? null,
        ];
        $artifact['activated_catalog_read_summary'] = [
            'catalog_source' => 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW',
            'catalog_state_valid' => $this->catalogStateValid($catalog),
            'production_catalog_activated' => (bool) ($catalog['production_catalog_activated'] ?? false),
            'controlled_catalog_read_confirmed' => (bool) ($options['activated_catalog_read_confirmed'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => $pass,
            'catalog_read_scope' => $pass ? 'PRIMARY_AND_BACKUP_ONLY' : 'DISABLED',
        ];
        $artifact['controlled_completion_payload_summary'] = [
            'completion_state_valid' => $this->completionStateValid($completion),
            'controlled_completion_hash' => $completion['controlled_completion_hash'] ?? null,
            'controlled_completion_record_count' => count((array) ($completion['output_rows'] ?? [])),
            'primary_candidate_present' => $this->rowMatches($completion, 0, self::PRIMARY_CANDIDATE, 'primary'),
            'backup_candidate_present' => $this->rowMatches($completion, 1, self::BACKUP_CANDIDATE, 'backup'),
            'comparator_excluded_from_rollout' => $this->valueAt($completion, ['comparator_candidate', 'a01_remains_comparator_only']) === true,
        ];
        $artifact['runtime_activation_state_summary'] = [
            'runtime_state_valid' => $this->runtimeStateValid($runtime),
            'production_catalog_runtime_wired' => (bool) ($runtime['production_catalog_runtime_wired'] ?? false),
            'runtime_bridge_active' => (bool) ($runtime['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($runtime['weekly_swing_watchlist_runtime_active'] ?? false),
            'kill_switch_available' => (bool) ($runtime['kill_switch_confirmed'] ?? false),
            'rollback_available' => (bool) ($runtime['rollback_confirmed'] ?? false),
        ];
        $artifact['plan_confirm_controlled_rollout_execution_decision'] = [
            'execution_valid' => $pass,
            'execution_decision' => $pass ? 'EXECUTED_CONTROLLED' : 'NOT_EXECUTED',
            'controlled_rollout_only' => true,
            'plan_confirm_controlled_mutation_executed' => $pass,
            'activated_catalog_controlled_read_executed' => $pass,
            'live_plan_confirm_controlled_rollout_executed' => $pass,
            'production_config_mutated' => false,
            'free_publication_executed' => false,
            'unrestricted_rollout_executed' => false,
        ];
        $artifact['watchlist_function_execution_summary'] = [
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::EXECUTION_RUNTIME_MODE,
            'watchlist_function_invoked' => $pass,
            'primary_candidate_observed' => $pass,
            'backup_candidate_observed' => $pass,
            'comparator_candidate_observed' => false,
            'official_stock_recommendations_free_published' => false,
        ];
        $artifact['candidate_scope_freeze_summary'] = [
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_rolled_out' => $pass,
            'backup_rolled_out' => $pass,
            'comparator_rolled_out' => false,
            'a01_remains_comparator_only' => true,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
        ];
        $artifact['operator_control_summary'] = [
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'controlled_rollout_execution_confirmed' => (bool) ($options['controlled_rollout_execution_confirmed'] ?? false),
            'kill_switch_confirmed' => (bool) ($options['kill_switch_confirmed'] ?? false),
            'rollback_confirmed' => (bool) ($options['rollback_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
        ];
        $artifact['rollout_state_artifact_summary'] = [
            'rollout_state_created' => $rolloutState !== null,
            'rollout_state_path' => $rolloutState['rollout_state_path'] ?? null,
            'rollout_state_hash' => $rolloutState['rollout_state_hash'] ?? null,
            'rollout_state_record_count' => count((array) ($rolloutState['rollout_rows'] ?? [])),
            'rollout_state_controlled_only' => (bool) ($rolloutState['controlled_rollout_only'] ?? false),
        ];
        $artifact['publication_and_rollout_safety_summary'] = [
            'controlled_rollout_executed' => $pass,
            'plan_confirm_mutated' => $pass,
            'plan_confirm_runtime_reads_activated_catalog' => $pass,
            'live_plan_confirm_rollout_executed' => $pass,
            'unrestricted_rollout_allowed' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'production_config_mutated' => false,
            'kill_switch_confirmed' => (bool) ($options['kill_switch_confirmed'] ?? false),
            'rollback_confirmed' => (bool) ($options['rollback_confirmed'] ?? false),
        ];
        $artifact['temporary_negative_artifact_guard_summary'] = [
            'temporary_negative_artifacts_remaining' => $temporaryPaths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $temporaryPaths === [],
            'temporary_negative_artifact_paths' => $temporaryPaths,
            'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
        $artifact['next_plan_confirm_controlled_rollout_result_review_decision'] = [
            'execution_valid' => $pass,
            'next_recommendation' => $pass ? self::NEXT_RESULT_REVIEW : 'C165_TARGETED_CONTROLLED_ROLLOUT_EXECUTION_REPAIR',
            'same_topic_c165_continues' => $pass,
            'result_review_required_next' => $pass,
            'result_review_requires_locked_execution_artifact' => $pass,
            'result_review_requires_locked_rollout_state' => $pass,
        ];
        $artifact['weekly_swing_watchlist_plan_confirm_controlled_rollout_execution_manifest'] = [
            'manifest_created' => true,
            'execution_artifact_only' => false,
            'controlled_rollout_state_created' => $rolloutState !== null,
            'controlled_rollout_executed' => $pass,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'activated_catalog_read_executed' => $pass,
            'plan_confirm_controlled_mutation_executed' => $pass,
            'controlled_live_rollout_executed' => $pass,
            'production_config_mutated' => false,
            'free_publication_executed' => false,
            'unrestricted_publication_allowed' => false,
            'official_weekly_swing_stock_recommendations' => [],
        ];
        $artifact['weekly_swing_watchlist_plan_confirm_controlled_rollout_execution_checklist'] = [
            'all_source_locks_reviewed' => true,
            'all_source_locks_valid' => $this->allLoadsValid($loads),
            'boundary_state_reviewed' => true,
            'catalog_state_reviewed' => true,
            'completion_payload_reviewed' => true,
            'runtime_activation_state_reviewed' => true,
            'operator_approval_required' => true,
            'controlled_rollout_only_required' => true,
            'kill_switch_required' => true,
            'rollback_required' => true,
            'free_publication_lock_required' => true,
            'result_review_required_next' => $pass,
        ];
        $artifact['c165_candidate_plan_confirm_controlled_rollout_execution_scorecard'] = [
            ['candidate_code' => self::PRIMARY_CANDIDATE, 'role' => 'primary_controlled_rollout', 'controlled_rollout_executed' => $pass, 'ready_for_result_review' => $pass],
            ['candidate_code' => self::BACKUP_CANDIDATE, 'role' => 'backup_controlled_rollout', 'controlled_rollout_executed' => $pass, 'ready_for_result_review' => $pass],
            ['candidate_code' => self::COMPARATOR_CANDIDATE, 'role' => 'comparator_only', 'controlled_rollout_executed' => false, 'ready_for_result_review' => false],
        ];
        $artifact['progress_summary'] = [
            'progress_marker' => 'PR-87_C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION',
            'topic_code' => 'C165_PLAN_CONFIRM_CONTROLLED_ROLLOUT',
            'topic_stage' => 'PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION',
            'c165_topic_complete' => false,
            'controlled_rollout_executed' => $pass,
            'result_review_required_next' => $pass,
        ];
        $artifact['planned_next_summary'] = [
            'planned_next_review' => $pass ? self::NEXT_RESULT_REVIEW : 'C165_TARGETED_CONTROLLED_ROLLOUT_EXECUTION_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C165 result review over locked execution and rollout-state artifacts' : 'repair C165 controlled rollout execution evidence',
            'same_topic_c165_continues' => $pass,
        ];
        $artifact['diagnostics'] = [
            'C165 execution locks the boundary, activated catalog, controlled completion payload, and runtime activation state.',
            'Controlled PLAN/CONFIRM mutation, activated-catalog read, and rollout are recorded in a dedicated reversible runtime state artifact.',
            'The execution does not modify config/watchlist.php and does not free-publish stock recommendations.',
            'Primary and backup proceed to same-topic result review; A01 remains comparator-only.',
        ];

        return $artifact;
    }

    private function buildRolloutState(array $loads, array $completion, string $createdAt, string $path, array $options): array
    {
        $rows = [];
        foreach ((array) ($completion['output_rows'] ?? []) as $row) {
            $rows[] = [
                'rank' => (int) ($row['rank'] ?? 0),
                'candidate_code' => (string) ($row['candidate_code'] ?? ''),
                'role' => (string) ($row['role'] ?? ''),
                'catalog_read_state' => 'controlled_enabled',
                'plan_confirm_state' => 'controlled_rollout_active',
                'rollout_state' => 'controlled_executed',
                'publication_state' => 'not_free_published',
            ];
        }
        $state = [
            'rollout_state_type' => 'weekly_swing_watchlist_plan_confirm_controlled_rollout_state',
            'created_at' => $createdAt,
            'rollout_state_hash' => null,
            'rollout_state_hash_algorithm' => 'stable_sha1_json_payload',
            'rollout_state_path' => $path,
            'source_run_code' => self::RUN_CODE,
            'source_c165_boundary_path' => $loads['boundary']['path'],
            'source_c165_boundary_hash' => $loads['boundary']['actual_hash'],
            'source_c165_boundary_file_sha1' => $loads['boundary']['actual_file_sha1'],
            'source_activated_catalog_path' => $loads['catalog']['path'],
            'source_activated_catalog_hash' => $loads['catalog']['actual_hash'],
            'source_activated_catalog_file_sha1' => $loads['catalog']['actual_file_sha1'],
            'source_controlled_completion_path' => $loads['completion']['path'],
            'source_controlled_completion_hash' => $loads['completion']['actual_hash'],
            'source_controlled_completion_file_sha1' => $loads['completion']['actual_file_sha1'],
            'source_runtime_activation_state_path' => $loads['runtime']['path'],
            'source_runtime_activation_state_hash' => $loads['runtime']['actual_hash'],
            'source_runtime_activation_state_file_sha1' => $loads['runtime']['actual_file_sha1'],
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'controlled_rollout_scope' => 'PRIMARY_AND_BACKUP_ONLY',
            'controlled_rollout_only' => true,
            'controlled_rollout_active' => true,
            'unrestricted_rollout_allowed' => false,
            'watchlist_function_used' => self::WATCHLIST_FUNCTION,
            'watchlist_function_runtime_mode' => self::EXECUTION_RUNTIME_MODE,
            'plan_confirm_mutation_allowed' => true,
            'plan_confirm_mutated' => true,
            'plan_confirm_runtime_reads_activated_catalog' => true,
            'live_plan_confirm_rollout_allowed' => true,
            'live_plan_confirm_rollout_executed' => true,
            'production_config_mutated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'kill_switch_confirmed' => true,
            'rollback_confirmed' => true,
            'result_review_required_next' => true,
            'rollout_rows' => $rows,
            'comparator_candidate' => [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'comparator_only',
                'controlled_rollout_executed' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
        $state['rollout_state_hash'] = $this->stableStateHash($state);

        return $state;
    }

    private function boundaryStateValid(array $boundary): bool
    {
        foreach ([
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_pass',
            'controlled_rollout_boundary_confirmed',
            'controlled_rollout_boundary_open',
            'c164_finalization_lock_valid',
            'c164_finalization_state_valid',
            'controlled_rollout_only_confirmed',
            'plan_confirm_unchanged_confirmed',
            'no_rollout_executed_confirmed',
            'free_publication_locked_confirmed',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_execution',
            'controlled_plan_confirm_rollout_execution_allowed_next',
            'a01_remains_comparator_only',
        ] as $field) {
            if (($boundary[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (['plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed', 'weekly_swing_watchlist_official_output_published'] as $field) {
            if (($boundary[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($boundary['status'] ?? null) === self::EXPECTED_BOUNDARY_STATUS
            && ($boundary['reason_code'] ?? null) === self::EXPECTED_BOUNDARY_STATUS
            && ($boundary['phase_label'] ?? null) === self::EXPECTED_BOUNDARY_PHASE
            && ($boundary['next_step_recommendation'] ?? null) === self::RUN_CODE
            && ($boundary['watchlist_function_used'] ?? null) === self::WATCHLIST_FUNCTION
            && ($boundary['watchlist_function_runtime_mode'] ?? null) === self::BOUNDARY_RUNTIME_MODE
            && $this->candidateCodesMatch($boundary)
            && $this->valueAt($boundary, ['next_plan_confirm_controlled_rollout_execution_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($boundary, ['next_plan_confirm_controlled_rollout_execution_decision', 'same_topic_c165_continues']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_controlled_rollout_boundary_manifest', 'controlled_rollout_boundary_artifact_only']) === true;
    }

    private function catalogStateValid(array $catalog): bool
    {
        return ($catalog['run_code'] ?? null) === 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW'
            && ($catalog['status'] ?? null) === self::EXPECTED_C68_STATUS
            && ($catalog['reason_code'] ?? null) === self::EXPECTED_C68_STATUS
            && ($catalog['production_catalog_activation_execution_performed'] ?? null) === true
            && ($catalog['production_catalog_activated'] ?? null) === true
            && ($catalog['production_catalog_runtime_wired'] ?? null) === false
            && ($catalog['production_deployment_executed'] ?? null) === false
            && ($catalog['plan_confirm_mutated'] ?? null) === false
            && ($catalog['plan_confirm_runtime_reads_activated_catalog'] ?? null) === false
            && $this->valueAt($catalog, ['production_catalog_activation_execution_decision', 'primary_candidate_code']) === self::PRIMARY_CANDIDATE
            && $this->valueAt($catalog, ['production_catalog_activation_execution_decision', 'backup_candidate_codes']) === [self::BACKUP_CANDIDATE]
            && $this->valueAt($catalog, ['production_catalog_activation_execution_decision', 'comparator_only_candidate_codes']) === [self::COMPARATOR_CANDIDATE]
            && $this->valueAt($catalog, ['production_catalog_activation_execution_decision', 'a01_remains_comparator_only']) === true
            && $this->valueAt($catalog, ['production_activation_execution_mutation_safety_summary', 'production_activation_execution_mutation_safety_pass']) === true;
    }

    private function completionStateValid(array $completion): bool
    {
        return ($completion['artifact_type'] ?? null) === self::EXPECTED_COMPLETION_TYPE
            && ($completion['plan_confirm_completion_mode'] ?? null) === 'controlled'
            && ($completion['plan_confirm_completion_state'] ?? null) === 'controlled_completion_executed'
            && ($completion['baseline_plan_confirm_state'] ?? null) === 'closed_and_unchanged'
            && ($completion['activated_catalog_read_state'] ?? null) === 'not_enabled'
            && ($completion['live_rollout_state'] ?? null) === 'not_executed'
            && ($completion['free_publication_allowed'] ?? null) === false
            && ($completion['unrestricted_publication_allowed'] ?? null) === false
            && ($completion['plan_confirm_mutated'] ?? null) === false
            && ($completion['plan_confirm_runtime_reads_activated_catalog'] ?? null) === false
            && ($completion['live_plan_confirm_rollout_executed'] ?? null) === false
            && count((array) ($completion['output_rows'] ?? [])) === 2
            && $this->rowMatches($completion, 0, self::PRIMARY_CANDIDATE, 'primary')
            && $this->rowMatches($completion, 1, self::BACKUP_CANDIDATE, 'backup')
            && $this->valueAt($completion, ['comparator_candidate', 'candidate_code']) === self::COMPARATOR_CANDIDATE
            && $this->valueAt($completion, ['comparator_candidate', 'a01_remains_comparator_only']) === true;
    }

    private function runtimeStateValid(array $runtime): bool
    {
        foreach (['production_catalog_runtime_wired', 'production_runtime_wiring_executed', 'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_live_recommendation_generation_allowed', 'kill_switch_confirmed', 'rollback_confirmed'] as $field) {
            if (($runtime[$field] ?? null) !== true) {
                return false;
            }
        }

        return ($runtime['runtime_state_type'] ?? null) === self::EXPECTED_RUNTIME_STATE_TYPE
            && ($runtime['source_run_code'] ?? null) === self::EXPECTED_RUNTIME_STATE_SOURCE
            && ($runtime['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($runtime['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($runtime['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($runtime['plan_confirm_mutated'] ?? null) === false
            && ($runtime['plan_confirm_runtime_reads_activated_catalog'] ?? null) === false
            && ($runtime['live_plan_confirm_rollout_executed'] ?? null) === false
            && ($runtime['weekly_swing_watchlist_official_output_published'] ?? null) === false;
    }

    private function candidateCodesMatch(array $source): bool
    {
        return ($source['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($source['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($source['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($source['a01_remains_comparator_only'] ?? null) === true;
    }

    private function rowMatches(array $completion, int $index, string $candidate, string $role): bool
    {
        $row = $completion['output_rows'][$index] ?? null;

        return is_array($row)
            && ($row['rank'] ?? null) === $index + 1
            && ($row['candidate_code'] ?? null) === $candidate
            && ($row['role'] ?? null) === $role;
    }

    private function finish(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, bool $pass): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $pass
            ? 'C165_CONTROLLED_ROLLOUT_EXECUTED_READY_FOR_SAME_TOPIC_RESULT_REVIEW_FREE_PUBLICATION_LOCKED'
            : $status;
        $artifact['next_step_recommendation'] = $pass ? self::NEXT_RESULT_REVIEW : 'C165_TARGETED_CONTROLLED_ROLLOUT_EXECUTION_REPAIR';
        $artifact['failure_attribution_summary'] = [
            'failure_count' => $pass ? 0 : 1,
            'failures' => $pass ? [] : [$status],
            'attribution' => $pass ? 'NONE' : 'C165_EXECUTION_SOURCE_OR_CONFIRMATION',
        ];
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function writeJson(string $path, array $payload, bool $overwrite): void
    {
        if (! $overwrite && is_file($path)) {
            return;
        }
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
    }

    private function sourceArtifactLocks(array $loads): array
    {
        $result = [];
        foreach ($loads as $name => $load) {
            $result[] = [
                'source' => $name,
                'path' => $load['path'],
                'expected_hash' => $load['expected_hash'],
                'actual_hash' => $load['actual_hash'],
                'hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'convert_from_json_pass' => $load['convert_from_json_pass'],
            ];
        }

        return $result;
    }

    private function topLevelLockAliases(array $loads): array
    {
        $result = [];
        foreach ($loads as $name => $load) {
            $prefix = $name === 'boundary' ? 'c165_boundary' : ($name === 'catalog' ? 'activated_catalog' : ($name === 'completion' ? 'controlled_completion' : 'runtime_activation_state'));
            $result['expected_'.$prefix.'_hash'] = $load['expected_hash'];
            $result['actual_'.$prefix.'_hash'] = $load['actual_hash'];
            $result[$prefix.'_hash_match'] = $load['hash_match'];
            $result['expected_'.$prefix.'_file_sha1'] = $load['expected_file_sha1'];
            $result['actual_'.$prefix.'_file_sha1'] = $load['actual_file_sha1'];
            $result[$prefix.'_file_sha1_match'] = $load['file_sha1_match'];
            $result[$prefix.'_convert_from_json_pass'] = $load['convert_from_json_pass'];
        }

        return $result;
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1, string $hashField): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        $duplicates = [];
        $jsonError = null;
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $duplicates = $this->caseInsensitiveDuplicateTopLevelKeys($raw);
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
            'case_insensitive_duplicate_keys' => $duplicates,
            'convert_from_json_pass' => $exists && is_array($payload) && $jsonError === JSON_ERROR_NONE && $duplicates === [],
        ];
    }

    private function loadValid(array $load): bool
    {
        return $load['exists'] && $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'];
    }

    private function allLoadsValid(array $loads): bool
    {
        foreach ($loads as $load) {
            if (! $this->loadValid($load)) {
                return false;
            }
        }

        return true;
    }

    private function caseInsensitiveDuplicateTopLevelKeys(string $raw): array
    {
        $length = strlen($raw);
        $depth = 0;
        $expectKey = false;
        $seen = [];
        $duplicates = [];
        for ($i = 0; $i < $length; $i++) {
            if ($raw[$i] === '"') {
                $start = $i++;
                $escaped = false;
                while ($i < $length) {
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($raw[$i] === '\\') {
                        $escaped = true;
                    } elseif ($raw[$i] === '"') {
                        break;
                    }
                    $i++;
                }
                if ($depth === 1 && $expectKey) {
                    $token = substr($raw, $start, $i - $start + 1);
                    $j = $i + 1;
                    while ($j < $length && ctype_space($raw[$j])) {
                        $j++;
                    }
                    if ($j < $length && $raw[$j] === ':') {
                        $key = json_decode($token, true);
                        if (is_string($key)) {
                            $lower = strtolower($key);
                            if (isset($seen[$lower]) && ! in_array($key, $duplicates, true)) {
                                $duplicates[] = $key;
                            }
                            $seen[$lower] = true;
                        }
                        $expectKey = false;
                    }
                }
            } elseif ($raw[$i] === '{') {
                $depth++;
                $expectKey = $depth === 1;
            } elseif ($raw[$i] === '}') {
                $depth--;
                $expectKey = false;
            } elseif ($raw[$i] === ',' && $depth === 1) {
                $expectKey = true;
            }
        }

        return $duplicates;
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

        return array_values(array_unique($paths));
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash'], $artifact['artifact_path']);
        $this->sortRecursive($artifact);

        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function stableStateHash(array $state): string
    {
        unset($state['rollout_state_hash'], $state['rollout_state_path']);
        $this->sortRecursive($state);

        return sha1(json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function sortRecursive(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                $this->sortRecursive($item);
            }
        }
        unset($item);
        if (! array_is_list($value)) {
            ksort($value);
        }
    }

    private function valueAt(array $source, array $path)
    {
        $current = $source;
        foreach ($path as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
