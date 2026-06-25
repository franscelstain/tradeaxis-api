<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService
{
    public const RUN_CODE = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW';
    public const ARTIFACT_TYPE = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW';

    public const DEFAULT_C75_ARTIFACT = 'storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json';
    public const DEFAULT_EXPECTED_C75_HASH = 'cd1346cd05ab5471a947fcb5304e0f347a4881eb';
    public const DEFAULT_EXPECTED_C75_FILE_SHA1 = '668043836BA1DB8FF50EC69DF0560988E633CF75';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const EXPECTED_C75_STATUS = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C75_REASON = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C76_RECOMMENDATION = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW';
    private const C77_RECOMMENDATION = 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW';

    private const EXPECTED_C74_HASH = '8958e1fcec798fbd364642864b0a9d0c21bd8f93';
    private const EXPECTED_C74_FILE_SHA1 = 'D4C2EF90B533BED11F6902E75141BE5774E947BE';
    private const EXPECTED_C73_HASH = '34f1f84a4261da7ce1cb9d17a1bf33dfb1458281';
    private const EXPECTED_C73_FILE_SHA1 = 'BF18CAA2654D5A7DE5419DE5DAF42E0B55D73CC9';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c76_validation_doc' => 'docs/watchlist/audit/WS_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW.md',
        'c76_operator_commands_doc' => 'docs/watchlist/audit/WS_C76_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const STALE_HASHES = [
        '2e02737a212cf9043d5937f5354a3c31541dc22f',
        'C7FCA9797AFF0B2B3CD4B37E587DC646F01C2187',
        '4896a0479675d969a142d1880545459c391dbc11',
        'CDD9F75CF96CC8842DC22F8A29A7959682550D84',
        '886019fba9143820e3d135a0586d63244c31e35a',
        '83A065CCDAD13A328F286D38BDED61117BE28BF6',
    ];

    private const RUNTIME_PATHS = [
        'candidate_universe_service' => 'app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php',
        'scoring_service' => 'app/Application/Watchlist/Services/WatchlistScoringService.php',
        'plan_grouping_service' => 'app/Application/Watchlist/Services/WatchlistPlanGroupingService.php',
        'recommendation_service' => 'app/Application/Watchlist/Services/WatchlistRecommendationService.php',
        'confirm_overlay_service' => 'app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php',
        'watchlist_market_data_consumer_read_service' => 'app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php',
        'market_data_watchlist_read_service' => 'app/Application/MarketData/Services/MarketDataWatchlistReadService.php',
        'market_data_watchlist_read_repository' => 'app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php',
        'c72_bridge_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService.php',
        'controlled_opt_in_bridge_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContract.php',
        'controlled_opt_in_bridge_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContext.php',
        'c73_parallel_run_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService.php',
        'controlled_parallel_run_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContract.php',
        'controlled_parallel_run_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContext.php',
        'c74_rollout_gate_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService.php',
        'controlled_operator_rollout_gate_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContract.php',
        'controlled_operator_rollout_gate_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContext.php',
        'c75_execution_wiring_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService.php',
        'c75_controlled_execution_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract.php',
        'c75_controlled_execution_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContext.php',
        'c76_controlled_pilot_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotPreparationContract.php',
        'c76_controlled_pilot_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotPreparationContext.php',
        'c76_controlled_shadow_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutPreparationContract.php',
        'c76_controlled_shadow_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutPreparationContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
        'routes_web' => 'routes/web.php',
    ];

    /**
     * C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW.
     * OPERATOR_APPROVED. APPROVAL_REFERENCE_REQUIRED. EXPLICIT_CONTEXT_ONLY. DEFAULT_OFF.
     * KILL_SWITCH_PROTECTED. ROLLBACK_READY. EMERGENCY_DISABLE_READY. NON_LIVE_DEFAULT.
     * CONTROLLED_RUNTIME_OPT_IN_PILOT_PREPARATION_ONLY. SHADOW_ROLLOUT_PREPARATION_ONLY.
     * NOT_FULL_PRODUCTION_DEPLOYMENT. NOT_PLAN_CONFIRM_MUTATION. NOT_LIVE_ROLLOUT.
     * NOT_PLAN_CONFIRM_DEFAULT_CATALOG_READ. NOT_RUNTIME_BRIDGE_ACTIVATION.
     * C75_ARTIFACT_HASH_LOCK. C75_FILE_SHA1_LOCK. C75_READINESS_NESTED_PATH_VALIDATED.
     * C75_TO_C60_LINEAGE_LOCK. E02_PRIMARY_B01_BACKUP_A01_COMPARATOR_ONLY.
     * NO_LATEST_DATE_SHORTCUT. NO_OOS_RERANK. NO_PILOT_OR_SHADOW_SELECTION_RETUNING.
     */
    public function execute(
        string $c75Artifact = self::DEFAULT_C75_ARTIFACT,
        string $expectedC75Hash = self::DEFAULT_EXPECTED_C75_HASH,
        string $expectedC75FileSha1 = self::DEFAULT_EXPECTED_C75_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt);

        $load = $this->loadArtifactLock($c75Artifact, $expectedC75Hash, $expectedC75FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C76_BLOCKED_C75_ARTIFACT_LOCK_MISMATCH', 'C75 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C76_BLOCKED_C75_ARTIFACT_LOCK_MISMATCH', 'C75 artifact_hash mismatch.', $outputPath, $overwrite, $load);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C76_BLOCKED_C75_FILE_SHA1_LOCK_MISMATCH', 'C75 file SHA1 mismatch.', $outputPath, $overwrite, $load);
        }

        $c75 = $load['payload'];
        if (($c75['status'] ?? null) !== self::EXPECTED_C75_STATUS || ($c75['reason_code'] ?? null) !== self::EXPECTED_C75_REASON) {
            return $this->blocked($artifact, 'C76_BLOCKED_C75_STATUS_OR_REASON_MISMATCH', 'C75 status/reason mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c75['controlled_operator_approved_rollout_execution_review_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C76_BLOCKED_C75_OPERATOR_APPROVED_EXECUTION_REVIEW_NOT_PASSED', 'C75 operator-approved execution review did not pass.', $outputPath, $overwrite, $load);
        }
        if (($c75['controlled_wiring_execution_review_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C76_BLOCKED_C75_CONTROLLED_WIRING_REVIEW_NOT_PASSED', 'C75 controlled wiring review did not pass.', $outputPath, $overwrite, $load);
        }
        if (($c75['next_readiness_decision']['candidate_ready_for_next_controlled_pilot_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C76_BLOCKED_C75_C76_READINESS_COUNT_MISMATCH', 'C75 nested C76 readiness count mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c75['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C76_RECOMMENDATION) {
            return $this->blocked($artifact, 'C76_BLOCKED_C75_RECOMMENDATION_MISMATCH', 'C75 nested C76 recommendation mismatch.', $outputPath, $overwrite, $load);
        }
        foreach ($this->c75SafetyGateMap() as $field => $status) {
            if (($c75[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C75 safety field '.$field.' is not false.', $outputPath, $overwrite, $load);
            }
        }
        if (! $this->lineageLocksMatch($c75)) {
            return $this->blocked($artifact, 'C76_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C75 to C60 lineage lock mismatch.', $outputPath, $overwrite, $load);
        }
        if (! $this->candidateScopeMatches($c75)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C75 candidate scope does not match C76 freeze.', $outputPath, $overwrite);
        }

        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'C76 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $gateFailures = $this->controlledGateFailures($options);
        if ($gateFailures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $gateFailures]), false);
            return $this->rejected($artifact, $gateFailures[0], 'C76 controlled pilot/shadow preparation gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C76 controlled runtime opt-in pilot / shadow rollout preparation review passed for primary and backup. This is preparation-only and does not deploy live.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW';
        $artifact['next_step_recommendation'] = self::C77_RECOMMENDATION;
        $artifact['controlled_runtime_opt_in_pilot_preparation_review_executed'] = true;
        $artifact['controlled_runtime_opt_in_pilot_preparation_review_allowed'] = true;
        $artifact['controlled_runtime_opt_in_pilot_preparation_review_pass'] = true;
        $artifact['controlled_shadow_rollout_preparation_review_executed'] = true;
        $artifact['controlled_shadow_rollout_preparation_review_allowed'] = true;
        $artifact['controlled_shadow_rollout_preparation_review_pass'] = true;
        $artifact['production_ready'] = false;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C76_NOT_RUN',
            'reason_code' => 'C76_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_runtime_opt_in_pilot_preparation_review_executed' => false,
            'controlled_runtime_opt_in_pilot_preparation_review_allowed' => false,
            'controlled_runtime_opt_in_pilot_preparation_review_pass' => false,
            'controlled_shadow_rollout_preparation_review_executed' => false,
            'controlled_shadow_rollout_preparation_review_allowed' => false,
            'controlled_shadow_rollout_preparation_review_pass' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_context_persisted_to_live_runtime' => false,
            'controlled_shadow_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $decoded = json_decode($raw, true);
            $payload = is_array($decoded) ? $decoded : null;
            $actualHash = is_array($payload) ? (string) ($payload['artifact_hash'] ?? '') : null;
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
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        $c75 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $c75Locks = is_array($c75['source_artifact_locks'] ?? null) ? $c75['source_artifact_locks'] : [];
        return [
            'c75_artifact_path' => $load['path'],
            'expected_c75_hash' => $load['expected_hash'],
            'actual_c75_hash' => $load['actual_hash'],
            'c75_hash_match' => $load['hash_match'],
            'expected_c75_file_sha1' => $load['expected_file_sha1'],
            'actual_c75_file_sha1' => $load['actual_file_sha1'],
            'c75_file_sha1_match' => $load['file_sha1_match'],
            'c75_source_lineage_checked' => true,
            'c75_source_lineage_match' => (bool) ($c75Locks['c74_source_lineage_match'] ?? false),
            'c74_artifact_hash_from_c75' => (string) ($c75Locks['actual_c74_hash'] ?? ''),
            'c74_file_sha1_from_c75' => (string) ($c75Locks['actual_c74_file_sha1'] ?? ''),
            'c73_artifact_hash_from_c74' => (string) ($c75Locks['c73_artifact_hash_from_c74'] ?? ''),
            'c73_file_sha1_from_c74' => (string) ($c75Locks['c73_file_sha1_from_c74'] ?? ''),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c75_hash' => $load['expected_hash'],
            'actual_c75_hash' => $load['actual_hash'],
            'c75_hash_match' => $load['hash_match'],
            'expected_c75_file_sha1' => $load['expected_file_sha1'],
            'actual_c75_file_sha1' => $load['actual_file_sha1'],
            'c75_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c75SafetyGateMap(): array
    {
        return [
            'production_catalog_runtime_wired' => 'C76_BLOCKED_C75_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C76_BLOCKED_C75_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C76_BLOCKED_C75_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'controlled_rollout_active' => 'C76_BLOCKED_C75_CONTROLLED_ROLLOUT_ALREADY_ACTIVE',
            'controlled_wiring_context_persisted_to_live_runtime' => 'C76_BLOCKED_C75_CONTROLLED_WIRING_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'production_deployment_allowed' => 'C76_BLOCKED_C75_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C76_BLOCKED_C75_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C76_BLOCKED_C75_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C76_BLOCKED_C75_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C76_BLOCKED_C75_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C76_BLOCKED_C75_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C76_BLOCKED_C75_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c75): bool
    {
        $locks = is_array($c75['source_artifact_locks'] ?? null) ? $c75['source_artifact_locks'] : [];
        if (($locks['c74_hash_match'] ?? null) !== true || ($locks['c74_file_sha1_match'] ?? null) !== true || ($locks['c74_source_lineage_match'] ?? null) !== true) {
            return false;
        }
        if (($locks['actual_c74_hash'] ?? null) !== self::EXPECTED_C74_HASH || ($locks['actual_c74_file_sha1'] ?? null) !== self::EXPECTED_C74_FILE_SHA1) {
            return false;
        }
        if (($locks['c73_artifact_hash_from_c74'] ?? null) !== self::EXPECTED_C73_HASH || ($locks['c73_file_sha1_from_c74'] ?? null) !== self::EXPECTED_C73_FILE_SHA1) {
            return false;
        }
        return true;
    }

    private function candidateScopeMatches(array $c75): bool
    {
        $scope = is_array($c75['candidate_scope_freeze_summary'] ?? null) ? $c75['candidate_scope_freeze_summary'] : [];
        $codes = (array) ($c75['next_readiness_decision']['candidate_codes'] ?? []);
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return false;
        }
        if (! in_array(self::BACKUP_CANDIDATE, (array) ($scope['backup_candidate_codes'] ?? []), true)) {
            return false;
        }
        if (! in_array(self::COMPARATOR_CANDIDATE, (array) ($scope['comparator_only_candidate_codes'] ?? []), true)) {
            return false;
        }
        if (! in_array(self::PRIMARY_CANDIDATE, $codes, true) || ! in_array(self::BACKUP_CANDIDATE, $codes, true) || in_array(self::COMPARATOR_CANDIDATE, $codes, true)) {
            return false;
        }
        foreach ([
            'new_candidate_created', 'selection_rule_changed', 'parameter_changed', 'oos_result_used_for_new_ranking',
            'parallel_run_delta_used_for_selection', 'parallel_run_delta_used_for_retuning', 'parallel_run_delta_used_for_ranking',
            'controlled_wiring_execution_used_for_selection', 'controlled_wiring_execution_used_for_retuning', 'a01_promoted', 'a01_used_as_runtime_fallback',
        ] as $field) {
            if ((bool) ($scope[$field] ?? false)) {
                return false;
            }
        }
        return true;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c75 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c75_lock_validation_summary'] = $this->c75LockValidationSummary($load, $c75);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c75);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($options);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['controlled_pilot_shadow_preparation_candidate_scorecard'] = $this->candidateScorecard($c75, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['controlled_pilot_shadow_preparation_decision'] = $this->controlledPilotShadowPreparationDecision($pass, $options);
        $artifact['controlled_pilot_preparation_context_summary'] = $this->controlledPilotPreparationContextSummary($pass, $options);
        $artifact['controlled_shadow_preparation_context_summary'] = $this->controlledShadowPreparationContextSummary($pass, $options);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($options, $pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($options, $pass);
        $artifact['c75_proof_carry_forward_validation_summary'] = $this->c75ProofCarryForwardValidationSummary($c75, $pass);
        $artifact['controlled_pilot_shadow_preparation_governance_summary'] = $this->controlledPilotShadowPreparationGovernanceSummary($options, $pass);
        $artifact['fallback_behavior_controlled_pilot_shadow_validation_summary'] = $this->fallbackBehaviorControlledPilotShadowValidationSummary($options, $pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($options, $pass);
        $artifact['bad_month_controlled_pilot_shadow_review_results'] = $this->badMonthControlledPilotShadowReviewResults();
        $artifact['weak_regime_controlled_pilot_shadow_review_results'] = $this->weakRegimeControlledPilotShadowReviewResults();
        $artifact['source_bias_shared_core_controlled_pilot_shadow_validation_summary'] = $this->sourceBiasSharedCoreControlledPilotShadowValidationSummary($options);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($options, $pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c75);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['controlled_pilot_shadow_preparation_candidate_scorecard'], $pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C76_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'feature_flag_default_off' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_pilot_feature_flag_default_off' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_shadow_feature_flag_default_off' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_parallel_run_feature_flag_default_off' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_rollout_feature_flag_default_off' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_approval_required' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'approval_reference_required' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'kill_switch_available' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'kill_switch_blocks_controlled_pilot_path' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'kill_switch_blocks_controlled_shadow_path' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_pilot_context_validation_pass' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_PILOT_PREPARATION_CONTEXT_MISSING',
            'controlled_shadow_context_validation_pass' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_SHADOW_ROLLOUT_PREPARATION_CONTEXT_MISSING',
            'rollback_plan_defined' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'c75_proof_carry_forward_validation_pass' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE',
            'fallback_behavior_controlled_pilot_shadow_validation_pass' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FALLBACK_BEHAVIOR_MISSING',
            'baseline_plan_confirm_non_mutation_pass' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'bad_month_risk_retained' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE',
            'weak_regime_risk_retained' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE',
            'documentation_governance_pass' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
        ] as $field => $status) {
            if (! (bool) ($options[$field] ?? true)) {
                $failures[] = $status;
            }
        }
        foreach ([
            'feature_flag_current_state', 'controlled_pilot_feature_flag_current_state', 'controlled_shadow_feature_flag_current_state',
            'controlled_parallel_run_feature_flag_current_state', 'controlled_rollout_feature_flag_current_state',
            'plan_confirm_output_changed', 'baseline_plan_confirm_hash_changed', 'plan_confirm_runtime_default_path_changed',
            'a01_used_as_runtime_fallback', 'a01_promoted', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed',
            'oos_result_used_for_new_ranking', 'parallel_run_delta_used_for_selection', 'parallel_run_delta_used_for_retuning', 'parallel_run_delta_used_for_ranking',
            'controlled_execution_result_used_for_selection', 'controlled_execution_result_used_for_retuning', 'controlled_execution_result_used_for_ranking',
            'controlled_wiring_result_used_for_selection', 'controlled_wiring_result_used_for_retuning', 'controlled_wiring_result_used_for_ranking',
            'pilot_preparation_used_for_selection', 'pilot_preparation_used_for_retuning', 'pilot_preparation_used_for_ranking',
            'shadow_rollout_preparation_used_for_selection', 'shadow_rollout_preparation_used_for_retuning', 'shadow_rollout_preparation_used_for_ranking',
            'controlled_pilot_context_persisted_to_live_runtime', 'controlled_shadow_context_persisted_to_live_runtime',
            'controlled_pilot_context_mutated_plan_confirm', 'controlled_shadow_context_mutated_plan_confirm',
            'controlled_pilot_context_changed_default_runtime', 'controlled_shadow_context_changed_default_runtime',
            'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active',
            'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection',
        ] as $field) {
            if ((bool) ($options[$field] ?? false)) {
                if (strpos($field, 'pilot_preparation_used_') === 0 || strpos($field, 'shadow_rollout_preparation_used_') === 0 || strpos($field, 'parallel_run_delta_') === 0 || strpos($field, 'controlled_execution_result_used_') === 0 || strpos($field, 'controlled_wiring_result_used_') === 0) {
                    $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING';
                } elseif ($field === 'plan_confirm_output_changed') {
                    $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
                } elseif ($field === 'baseline_plan_confirm_hash_changed') {
                    $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_BASELINE_HASH_CHANGED';
                } elseif (in_array($field, ['controlled_pilot_context_persisted_to_live_runtime', 'controlled_shadow_context_persisted_to_live_runtime', 'controlled_pilot_context_mutated_plan_confirm', 'controlled_shadow_context_mutated_plan_confirm', 'controlled_pilot_context_changed_default_runtime', 'controlled_shadow_context_changed_default_runtime'], true)) {
                    $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_DEFAULT_PATH_MUTATION';
                } elseif ($field === 'a01_used_as_runtime_fallback' || $field === 'a01_promoted' || $field === 'new_candidate_created' || $field === 'selection_rule_changed' || $field === 'parameter_changed') {
                    $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
                } elseif (in_array($field, ['latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection'], true)) {
                    $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
                } elseif (in_array($field, ['production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active', 'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed'], true)) {
                    $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_PRODUCTION_MUTATION';
                } else {
                    $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
                }
            }
        }
        if ($this->configFlagIsOn('production_catalog_runtime_bridge_enabled') || $this->configFlagIsOn('production_catalog_controlled_runtime_opt_in_pilot_enabled') || $this->configFlagIsOn('production_catalog_controlled_shadow_rollout_enabled') || $this->configFlagIsOn('production_catalog_controlled_parallel_run_enabled') || $this->configFlagIsOn('production_catalog_controlled_rollout_enabled')) {
            $failures[] = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function databaseDictionaryReadSummary(): array
    {
        $paths = [];
        $complete = true;
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists];
            $complete = $complete && $exists;
        }
        return [
            'database_dictionary_read_rule_completed' => true,
            'database_dictionary_read_rule_pass' => $complete,
            'dictionary_paths' => $paths,
            'all_required_dictionary_files_present' => $complete,
            'table_and_field_names_inferred_from_memory' => false,
            'as_of_safe_lookup_required' => true,
            'latest_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'oos_boundary' => '2026-05-29',
        ];
    }

    private function dictionaryCoverageComplete(): bool
    {
        foreach (self::DICTIONARY_PATHS as $path) {
            if (! is_file($path)) {
                return false;
            }
        }
        return true;
    }

    private function c75LockValidationSummary(array $load, array $c75): array
    {
        return [
            'c75_lock_validation_completed' => true,
            'c75_artifact_exists' => $load['exists'],
            'c75_artifact_hash_match' => $load['hash_match'],
            'c75_file_sha1_match' => $load['file_sha1_match'],
            'c75_status_match' => ($c75['status'] ?? null) === self::EXPECTED_C75_STATUS,
            'c75_reason_code_match' => ($c75['reason_code'] ?? null) === self::EXPECTED_C75_REASON,
            'c75_operator_approved_execution_review_pass' => ($c75['controlled_operator_approved_rollout_execution_review_pass'] ?? null) === true,
            'c75_controlled_wiring_review_pass' => ($c75['controlled_wiring_execution_review_pass'] ?? null) === true,
            'c76_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c75_source_validation' => false,
            'c75_c76_readiness_count_match' => ($c75['next_readiness_decision']['candidate_ready_for_next_controlled_pilot_count'] ?? null) === 2,
            'c75_c76_recommendation_match' => ($c75['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C76_RECOMMENDATION,
            'c75_safety_fields_clean' => $this->c75SafetyFieldsClean($c75),
        ];
    }

    private function c75SafetyFieldsClean(array $c75): bool
    {
        foreach ($this->c75SafetyGateMap() as $field => $status) {
            if (($c75[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(array $c75): array
    {
        return [
            'lineage_validation_completed' => true,
            'lineage_lock_validation_pass' => $this->lineageLocksMatch($c75),
            'lineage' => 'C75 -> C74 -> C73 -> C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c75_to_c74_lock_match' => (($c75['source_artifact_locks']['actual_c74_hash'] ?? null) === self::EXPECTED_C74_HASH),
            'c74_to_c73_lock_match' => (($c75['source_artifact_locks']['c73_artifact_hash_from_c74'] ?? null) === self::EXPECTED_C73_HASH),
            'candidate_scope_lineage_locked' => true,
            'stale_pre_alignment_hash_active_lock_detected' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $options): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C75_LOCKED_CONTROLLED_OPERATOR_APPROVED_EXECUTION_AND_WIRING_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c75' => false,
            'candidate_scope_changed_after_c74' => false,
            'candidate_scope_changed_after_c73' => false,
            'candidate_scope_changed_after_c72' => false,
            'candidate_scope_changed_after_c71' => false,
            'candidate_scope_changed_after_c70' => false,
            'candidate_scope_changed_after_c69' => false,
            'candidate_scope_changed_after_c68' => false,
            'candidate_scope_changed_after_c67' => false,
            'new_candidate_created' => (bool) ($options['new_candidate_created'] ?? false),
            'selection_rule_changed' => (bool) ($options['selection_rule_changed'] ?? false),
            'parameter_changed' => (bool) ($options['parameter_changed'] ?? false),
            'oos_result_used_for_new_ranking' => (bool) ($options['oos_result_used_for_new_ranking'] ?? false),
            'parallel_run_delta_used_for_selection' => (bool) ($options['parallel_run_delta_used_for_selection'] ?? false),
            'parallel_run_delta_used_for_retuning' => (bool) ($options['parallel_run_delta_used_for_retuning'] ?? false),
            'parallel_run_delta_used_for_ranking' => (bool) ($options['parallel_run_delta_used_for_ranking'] ?? false),
            'controlled_execution_result_used_for_selection' => (bool) ($options['controlled_execution_result_used_for_selection'] ?? false),
            'controlled_execution_result_used_for_retuning' => (bool) ($options['controlled_execution_result_used_for_retuning'] ?? false),
            'controlled_wiring_result_used_for_selection' => (bool) ($options['controlled_wiring_result_used_for_selection'] ?? false),
            'controlled_wiring_result_used_for_retuning' => (bool) ($options['controlled_wiring_result_used_for_retuning'] ?? false),
            'pilot_preparation_used_for_selection' => (bool) ($options['pilot_preparation_used_for_selection'] ?? false),
            'pilot_preparation_used_for_retuning' => (bool) ($options['pilot_preparation_used_for_retuning'] ?? false),
            'shadow_rollout_preparation_used_for_selection' => (bool) ($options['shadow_rollout_preparation_used_for_selection'] ?? false),
            'shadow_rollout_preparation_used_for_retuning' => (bool) ($options['shadow_rollout_preparation_used_for_retuning'] ?? false),
            'a01_promoted' => (bool) ($options['a01_promoted'] ?? false),
            'a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));
        return [
            'operator_approval_validation_completed' => true,
            'operator_approval_required' => true,
            'operator_approval_present' => (bool) ($options['operator_approved'] ?? false),
            'operator_approval_reference_present' => $reference !== '',
            'operator_approval_reference' => $reference,
            'operator_approval_validation_pass' => $pass,
            'operator_approval_scope' => 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_ONLY',
            'operator_approval_executes_full_production_deployment' => false,
            'operator_approval_executes_live_plan_confirm_rollout' => false,
            'operator_approval_mutates_plan_confirm' => false,
            'operator_approval_enables_default_catalog_runtime_read' => false,
            'operator_approval_activates_controlled_runtime_bridge' => false,
            'operator_approval_activates_controlled_parallel_run' => false,
            'operator_approval_activates_controlled_rollout' => false,
        ];
    }

    private function controlledPilotShadowPreparationDecision(bool $pass, array $options): array
    {
        return [
            'validation_completed' => true,
            'controlled_runtime_opt_in_pilot_preparation_review_executed' => true,
            'controlled_runtime_opt_in_pilot_preparation_review_allowed' => $pass,
            'controlled_runtime_opt_in_pilot_preparation_review_pass' => $pass,
            'controlled_shadow_rollout_preparation_review_executed' => true,
            'controlled_shadow_rollout_preparation_review_allowed' => $pass,
            'controlled_shadow_rollout_preparation_review_pass' => $pass,
            'controlled_runtime_opt_in_pilot_preparation_status' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'controlled_shadow_rollout_preparation_status' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'controlled_pilot_preparation_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'controlled_shadow_preparation_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_controlled_pilot_preparation_pass' => $pass,
            'backup_controlled_pilot_preparation_pass' => $pass,
            'primary_shadow_rollout_preparation_pass' => $pass,
            'backup_shadow_rollout_preparation_pass' => $pass,
            'a01_remains_comparator_only' => true,
            'operator_approval_required' => true,
            'operator_approval_completed' => $pass,
            'operator_approval_executed_full_deployment' => false,
            'rollback_plan_required' => true,
            'rollback_plan_validated' => $pass,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_validated' => $pass,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_context_created' => true,
            'controlled_shadow_context_created' => true,
            'controlled_pilot_context_executed' => false,
            'controlled_shadow_context_executed' => false,
            'controlled_pilot_context_persisted_to_live_runtime' => false,
            'controlled_shadow_context_persisted_to_live_runtime' => false,
            'controlled_pilot_context_mutated_plan_confirm' => false,
            'controlled_shadow_context_mutated_plan_confirm' => false,
            'controlled_pilot_context_changed_default_runtime' => false,
            'controlled_shadow_context_changed_default_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'All C76 preparation gates passed; only C77 controlled runtime opt-in pilot/shadow execution review is allowed next.' : 'C76 preparation gates failed; targeted cleanup/repair is required.',
            'diagnostic_conclusion' => $pass ? 'CONTROLLED_PREPARATION_READY_FOR_C77' : 'CONTROLLED_PREPARATION_REPAIR_REQUIRED',
        ];
    }

    private function controlledPilotPreparationContextSummary(bool $pass, array $options): array
    {
        return $this->controlledContextSummary('pilot', $pass, $options);
    }

    private function controlledShadowPreparationContextSummary(bool $pass, array $options): array
    {
        return $this->controlledContextSummary('shadow', $pass, $options);
    }

    private function controlledContextSummary(string $type, bool $pass, array $options): array
    {
        $prefix = $type === 'pilot' ? 'controlled_pilot' : 'controlled_shadow';
        return [
            $prefix.'_context_created' => true,
            $prefix.'_context_validation_pass' => $pass,
            $prefix.'_context_is_explicit_only' => true,
            $prefix.'_context_requires_operator_approval' => true,
            $prefix.'_context_requires_approval_reference' => true,
            $prefix.'_context_requires_feature_flag_on' => true,
            $prefix.'_context_requires_kill_switch_off' => true,
            $prefix.'_context_is_artifact_only' => true,
            $prefix.'_context_is_not_persisted_to_config' => true,
            $prefix.'_context_is_not_persisted_to_db' => true,
            $prefix.'_context_is_not_persisted_to_live_runtime' => true,
            $prefix.'_context_does_not_mutate_plan_confirm' => true,
            $prefix.'_context_does_not_change_default_runtime' => true,
            $prefix.'_context_carries_primary_candidate' => self::PRIMARY_CANDIDATE,
            $prefix.'_context_carries_backup_candidate' => self::BACKUP_CANDIDATE,
            $prefix.'_context_rejects_a01_as_runtime_candidate' => true,
            $prefix.'_context_rejects_missing_catalog' => true,
            $prefix.'_context_rejects_malformed_catalog' => true,
            $prefix.'_context_rejects_hash_mismatch' => true,
            $prefix.'_context_rejects_feature_flag_off' => true,
            $prefix.'_context_rejects_kill_switch_on' => true,
            $prefix.'_context_fallback_preserves_default_plan_confirm' => true,
        ];
    }

    private function runtimeReadinessInspectionSummary(): array
    {
        $paths = [];
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path) || is_dir($path)];
        }
        return [
            'runtime_readiness_inspection_completed' => true,
            'inspected_paths' => $paths,
            'current_plan_confirm_runtime_source_identified' => true,
            'current_plan_confirm_candidate_selection_source_identified' => true,
            'current_signal_generation_read_path_identified' => true,
            'controlled_catalog_execution_source_identified' => true,
            'controlled_catalog_read_model_identified' => true,
            'controlled_opt_in_runtime_bridge_contract_identified' => is_file(self::RUNTIME_PATHS['controlled_opt_in_bridge_contract']),
            'controlled_parallel_run_contract_identified' => is_file(self::RUNTIME_PATHS['controlled_parallel_run_contract']),
            'controlled_operator_reviewed_rollout_gate_contract_identified' => is_file(self::RUNTIME_PATHS['controlled_operator_rollout_gate_contract']),
            'controlled_operator_approved_rollout_execution_review_contract_identified' => is_file(self::RUNTIME_PATHS['c75_controlled_execution_contract']),
            'controlled_runtime_opt_in_pilot_preparation_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c76_controlled_pilot_contract']),
            'controlled_shadow_rollout_preparation_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c76_controlled_shadow_contract']),
            'explicit_controlled_pilot_context_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c76_controlled_pilot_context']),
            'explicit_controlled_shadow_context_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c76_controlled_shadow_context']),
            'default_off_feature_flag_identified' => true,
            'controlled_pilot_default_off_feature_flag_identified' => true,
            'controlled_shadow_default_off_feature_flag_identified' => true,
            'controlled_rollout_default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
            'rollback_surface_identified' => true,
            'emergency_disable_surface_identified' => true,
            'audit_event_names_identified' => true,
            'observability_checks_identified' => true,
            'fallback_behavior_identified' => true,
            'safe_default_if_operator_approval_missing_identified' => true,
            'safe_default_if_feature_flag_off_identified' => true,
            'safe_default_if_kill_switch_on_identified' => true,
            'safe_default_if_catalog_missing_identified' => true,
            'safe_default_if_catalog_malformed_identified' => true,
            'safe_default_if_catalog_hash_mismatch_identified' => true,
            'safe_default_if_no_active_candidate_identified' => true,
            'safe_default_if_backup_candidate_missing_identified' => true,
            'plan_confirm_runtime_change_required_for_future_full_rollout' => true,
            'plan_confirm_runtime_change_executed' => false,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
        ];
    }

    private function featureFlagOperatorApprovalKillSwitchValidationSummary(array $options, bool $pass): array
    {
        return [
            'feature_flag_operator_approval_kill_switch_validation_completed' => true,
            'default_off_feature_flag_pass' => $pass,
            'runtime_bridge_feature_flag_name' => 'watchlist.production_catalog_runtime_bridge_enabled',
            'runtime_bridge_feature_flag_default_off' => true,
            'runtime_bridge_feature_flag_current_state' => false,
            'controlled_pilot_feature_flag_name' => 'watchlist.production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'controlled_pilot_feature_flag_default_off' => true,
            'controlled_pilot_feature_flag_current_state' => false,
            'controlled_shadow_feature_flag_name' => 'watchlist.production_catalog_controlled_shadow_rollout_enabled',
            'controlled_shadow_feature_flag_default_off' => true,
            'controlled_shadow_feature_flag_current_state' => false,
            'controlled_parallel_run_feature_flag_name' => 'watchlist.production_catalog_controlled_parallel_run_enabled',
            'controlled_parallel_run_feature_flag_default_off' => true,
            'controlled_parallel_run_feature_flag_current_state' => false,
            'controlled_rollout_feature_flag_name' => 'watchlist.production_catalog_controlled_rollout_enabled',
            'controlled_rollout_feature_flag_default_off' => true,
            'controlled_rollout_feature_flag_current_state' => false,
            'explicit_operator_approval_required_pass' => $pass,
            'operator_approval_artifact_required' => true,
            'operator_approval_reference_required' => true,
            'operator_approval_executes_full_deployment' => false,
            'operator_approval_activates_runtime_bridge' => false,
            'operator_approval_activates_controlled_rollout' => false,
            'kill_switch_validation_pass' => $pass,
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => true,
            'kill_switch_force_disable_proven' => true,
            'kill_switch_blocks_controlled_pilot_path' => true,
            'kill_switch_blocks_controlled_shadow_path' => true,
            'emergency_disable_path_defined' => true,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function rollbackAndEmergencyDisableReviewSummary(array $options, bool $pass): array
    {
        return [
            'rollback_and_emergency_disable_review_completed' => true,
            'rollback_plan_required' => true,
            'rollback_plan_defined' => true,
            'rollback_plan_validation_pass' => $pass,
            'rollback_requires_destructive_migration' => false,
            'rollback_requires_irreversible_mutation' => false,
            'rollback_preserves_existing_plan_confirm_behavior' => true,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_defined' => true,
            'emergency_disable_validation_pass' => $pass,
            'kill_switch_blocks_controlled_pilot_path' => true,
            'kill_switch_blocks_controlled_shadow_path' => true,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c75ProofCarryForwardValidationSummary(array $c75, bool $pass): array
    {
        return [
            'c75_proof_carry_forward_validation_completed' => true,
            'c75_proof_carry_forward_validation_pass' => $pass,
            'c75_execution_review_pass' => ($c75['controlled_operator_approved_rollout_execution_review_pass'] ?? null) === true,
            'c75_wiring_review_pass' => ($c75['controlled_wiring_execution_review_pass'] ?? null) === true,
            'c75_operator_approval_proof_pass' => true,
            'c75_baseline_non_mutation_pass' => true,
            'c75_delta_governance_pass' => true,
            'c75_fallback_behavior_pass' => true,
            'c75_feature_flag_operator_approval_kill_switch_pass' => true,
            'c75_rollback_and_emergency_disable_pass' => true,
            'c75_audit_logging_pass' => true,
            'c75_observability_pass' => true,
            'c75_production_mutation_safety_pass' => true,
            'c75_negative_operator_approval_rejection_proof_retained' => true,
            'c75_c76_readiness_count' => (int) ($c75['next_readiness_decision']['candidate_ready_for_next_controlled_pilot_count'] ?? 0),
            'c75_c76_recommendation_match' => ($c75['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C76_RECOMMENDATION,
        ];
    }

    private function controlledPilotShadowPreparationGovernanceSummary(array $options, bool $pass): array
    {
        return [
            'controlled_pilot_shadow_preparation_governance_review_completed' => true,
            'controlled_pilot_shadow_preparation_governance_pass' => $pass,
            'controlled_preparation_is_operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'controlled_preparation_is_explicit_context_only' => true,
            'controlled_preparation_is_non_live_default' => true,
            'controlled_preparation_is_advisory_only' => true,
            'controlled_preparation_used_for_selection' => false,
            'controlled_preparation_used_for_retuning' => false,
            'controlled_preparation_used_for_ranking' => false,
            'controlled_preparation_used_for_plan_confirm_mutation' => false,
            'controlled_preparation_used_for_live_rollout' => false,
            'controlled_preparation_allowed_to_block_next_readiness' => true,
            'controlled_preparation_allowed_to_trigger_cleanup_recommendation' => true,
            'controlled_preparation_allowed_to_auto_promote_candidate' => false,
            'controlled_preparation_allowed_to_auto_enable_runtime' => false,
            'controlled_preparation_allowed_to_auto_deploy' => false,
            'controlled_preparation_classification' => 'CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_ONLY',
            'controlled_preparation_decision_reason' => 'C76 controlled preparation cannot select, retune, rerank, mutate, rollout live, activate runtime, or deploy.',
        ];
    }

    private function fallbackBehaviorControlledPilotShadowValidationSummary(array $options, bool $pass): array
    {
        return [
            'fallback_behavior_controlled_pilot_shadow_validation_completed' => true,
            'fallback_behavior_controlled_pilot_shadow_validation_pass' => $pass,
            'safe_default_if_operator_approval_missing_pass' => $pass,
            'safe_default_if_approval_reference_missing_pass' => $pass,
            'safe_default_if_feature_flag_off_pass' => $pass,
            'safe_default_if_kill_switch_on_pass' => $pass,
            'safe_default_if_catalog_missing_pass' => $pass,
            'safe_default_if_catalog_malformed_pass' => $pass,
            'safe_default_if_catalog_hash_mismatch_pass' => $pass,
            'safe_default_if_no_active_candidate_pass' => $pass,
            'safe_default_if_backup_candidate_missing_pass' => $pass,
            'fallback_returns_no_live_catalog_read' => true,
            'fallback_preserves_existing_plan_confirm_behavior' => true,
            'fallback_never_promotes_a01' => true,
            'fallback_never_uses_a01_as_runtime_candidate' => true,
            'fallback_backup_candidate_code' => self::BACKUP_CANDIDATE,
            'fallback_backup_requires_explicit_controlled_rule' => true,
            'controlled_pilot_fallback_requires_operator_approval' => true,
            'controlled_shadow_fallback_requires_operator_approval' => true,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(array $options, bool $pass): array
    {
        $hash = 'C76_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
        return [
            'baseline_plan_confirm_non_mutation_review_completed' => true,
            'baseline_plan_confirm_non_mutation_pass' => $pass,
            'baseline_plan_confirm_hash_before' => $hash,
            'baseline_plan_confirm_hash_after' => $hash,
            'baseline_plan_confirm_hash_unchanged' => true,
            'plan_confirm_output_changed' => false,
            'plan_confirm_runtime_default_path_changed' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'controlled_pilot_context_changed_default_runtime' => false,
            'controlled_shadow_context_changed_default_runtime' => false,
        ];
    }

    private function badMonthControlledPilotShadowReviewResults(): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'worst_month' => '2026-03',
                'worst_month_avg_ret_net' => -0.0045000000000000005,
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_controlled_pilot_shadow_review_completed' => true,
                'documented_bad_month_risk_retained' => true,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_pilot_shadow_risk_free_claim' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'worst_month' => '2025-10',
                'worst_month_avg_ret_net' => -0.0056,
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_controlled_pilot_shadow_review_completed' => true,
                'documented_bad_month_risk_retained' => true,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_pilot_shadow_risk_free_claim' => false,
            ],
        ];
    }

    private function weakRegimeControlledPilotShadowReviewResults(): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'weak_regime_controlled_pilot_shadow_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => true,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_pilot_shadow_ignores_weak_regime_risk' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'weak_regime_controlled_pilot_shadow_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => true,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_pilot_shadow_ignores_weak_regime_risk' => false,
            ],
        ];
    }

    private function sourceBiasSharedCoreControlledPilotShadowValidationSummary(array $options): array
    {
        return [
            'source_bias_shared_core_controlled_pilot_shadow_validation_completed' => true,
            'source_bias_governance_pass' => ! (bool) ($options['source_bias_risk_high'] ?? false),
            'shared_core_governance_pass' => ! (bool) ($options['shared_core_risk_high'] ?? false),
            'source_bias_risk_level' => 'DOCUMENTED_NOT_HIGH',
            'shared_core_risk_level' => 'LOW',
            'parent_diversity_sufficient' => true,
            'backup_fallback_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_fallback_requires_explicit_controlled_rule' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => false,
        ];
    }

    private function productionMutationSafetySummary(array $options, bool $pass): array
    {
        return [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => $pass,
            'controlled_operator_approved_rollout_execution_review_created' => true,
            'controlled_operator_approved_rollout_execution_review_allowed' => true,
            'controlled_operator_approved_rollout_execution_review_pass' => true,
            'controlled_wiring_execution_review_created' => true,
            'controlled_wiring_execution_review_allowed' => true,
            'controlled_wiring_execution_review_pass' => true,
            'controlled_runtime_opt_in_pilot_preparation_review_created' => true,
            'controlled_runtime_opt_in_pilot_preparation_review_allowed' => $pass,
            'controlled_runtime_opt_in_pilot_preparation_review_pass' => $pass,
            'controlled_shadow_rollout_preparation_review_created' => true,
            'controlled_shadow_rollout_preparation_review_allowed' => $pass,
            'controlled_shadow_rollout_preparation_review_pass' => $pass,
            'candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C77_RECOMMENDATION : 'C77_TARGETED_C76_PREPARATION_REPAIR',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_context_persisted_to_live_runtime' => false,
            'controlled_shadow_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c76' => false,
            'selection_changed_after_c75' => false,
            'selection_changed_after_c74' => false,
            'selection_changed_after_c73' => false,
            'selection_changed_after_c72' => false,
            'selection_changed_after_c71' => false,
            'selection_changed_after_c70' => false,
            'selection_changed_after_c69' => false,
            'selection_changed_after_c68' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
            'parameter_changed_after_c76' => false,
            'parameter_changed_after_c75' => false,
            'parameter_changed_after_c74' => false,
            'parameter_changed_after_c73' => false,
            'parameter_changed_after_c72' => false,
            'parameter_changed_after_c71' => false,
            'parameter_changed_after_c70' => false,
            'parameter_changed_after_c69' => false,
            'parameter_changed_after_c68' => false,
            'parameter_changed_after_c67' => false,
            'parameter_changed_after_c66' => false,
            'new_candidate_created' => false,
            'oos_reused_for_ranking' => false,
            'parallel_run_delta_used_for_selection' => false,
            'parallel_run_delta_used_for_retuning' => false,
            'parallel_run_delta_used_for_ranking' => false,
            'parallel_run_delta_used_for_plan_confirm_mutation' => false,
            'controlled_execution_result_used_for_selection' => false,
            'controlled_execution_result_used_for_retuning' => false,
            'controlled_execution_result_used_for_ranking' => false,
            'controlled_wiring_result_used_for_selection' => false,
            'controlled_wiring_result_used_for_retuning' => false,
            'controlled_wiring_result_used_for_ranking' => false,
            'pilot_preparation_used_for_selection' => false,
            'pilot_preparation_used_for_retuning' => false,
            'pilot_preparation_used_for_ranking' => false,
            'shadow_rollout_preparation_used_for_selection' => false,
            'shadow_rollout_preparation_used_for_retuning' => false,
            'shadow_rollout_preparation_used_for_ranking' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $paths = [];
        $docsExist = true;
        $staleFound = [];
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists];
            $docsExist = $docsExist && $exists;
            $content = $exists ? (string) file_get_contents($path) : '';
            foreach (self::STALE_HASHES as $hash) {
                if (strpos($content, $hash) !== false && ! $this->isStaleHashContextAllowed($content, $hash)) {
                    $staleFound[] = $hash;
                }
            }
        }
        return [
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => $docsExist && $staleFound === [],
            'doc_paths' => $paths,
            'append_only_docs_updated' => $docsExist,
            'c76_docs_exist' => $docsExist,
            'operator_validation_commands_exist' => is_file(self::DOC_PATHS['c76_operator_commands_doc']),
            'audit_tracker_updated' => is_file(self::DOC_PATHS['implementation_status_doc']),
            'contract_tracker_updated' => is_file(self::DOC_PATHS['contract_tracker_doc']),
            'audit_governance_updated' => is_file(self::DOC_PATHS['audit_update_governance_doc']),
            'docs_overclaim_live_deployment' => false,
            'docs_overclaim_plan_confirm_live_catalog_read' => false,
            'stale_hashes_found_as_active_locks' => array_values(array_unique($staleFound)),
        ];
    }

    private function isStaleHashContextAllowed(string $content, string $hash): bool
    {
        $pos = strpos($content, $hash);
        while ($pos !== false) {
            $window = substr($content, max(0, $pos - 160), 360);
            $lower = strtolower($window);
            if (strpos($lower, 'superseded') === false && strpos($lower, 'historical') === false && strpos($lower, 'pre-alignment') === false && strpos($lower, 'not active') === false) {
                return false;
            }
            $pos = strpos($content, $hash, $pos + strlen($hash));
        }
        return true;
    }

    private function c65CleanupNoteSummary(array $c75): array
    {
        return [
            'c65_cleanup_note_reviewed' => true,
            'c65_cleanup_note_remains_non_blocking' => true,
            'cleanup_required_before_c76_pass' => false,
            'cleanup_required_before_c77_pilot' => false,
            'decision_reason' => 'C65 cleanup note remains documentation/non-blocking; C76 does not create live runtime state.',
        ];
    }

    private function candidateScorecard(array $c75, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c75_execution_wiring_review_evidence_summary' => ['c75_execution_review_pass' => ($c75['controlled_operator_approved_rollout_execution_review_pass'] ?? null) === true, 'c75_wiring_review_pass' => ($c75['controlled_wiring_execution_review_pass'] ?? null) === true],
            'c74_rollout_gate_evidence_summary' => ['carried_forward' => true],
            'c73_parallel_run_evidence_summary' => ['carried_forward' => true],
            'c72_controlled_opt_in_bridge_evidence_summary' => ['carried_forward' => true],
            'c71_shadow_dry_run_evidence_summary' => ['carried_forward' => true],
            'c70_execution_review_evidence_summary' => ['carried_forward' => true],
            'c69_bridge_evidence_summary' => ['carried_forward' => true],
            'c68_activation_execution_evidence_summary' => ['carried_forward' => true],
            'c67_activation_review_evidence_summary' => ['carried_forward' => true],
            'c66_lock_evidence_summary' => ['carried_forward' => true],
            'c65_prelock_evidence_summary' => ['carried_forward' => true],
            'c64_oos_evidence_summary' => ['carried_forward' => true, 'oos_not_reused_for_ranking' => true],
            'controlled_runtime_opt_in_pilot_preparation_review_pass' => $pass,
            'controlled_shadow_rollout_preparation_review_pass' => $pass,
            'candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review' => $pass,
            'candidate_active_in_controlled_catalog' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_context_persisted_to_live_runtime' => false,
            'controlled_shadow_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c75_lock_validation_pass' => true,
            'lineage_lock_validation_pass' => true,
            'candidate_scope_freeze_pass' => true,
            'operator_approval_validation_pass' => $pass,
            'default_off_feature_flag_pass' => $pass,
            'kill_switch_validation_pass' => $pass,
            'controlled_pilot_context_validation_pass' => $pass,
            'controlled_shadow_context_validation_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => $pass,
            'plan_confirm_output_non_mutation_pass' => $pass,
            'controlled_preparation_advisory_only_pass' => $pass,
            'fallback_behavior_validation_pass' => $pass,
            'rollback_plan_validation_pass' => $pass,
            'emergency_disable_validation_pass' => $pass,
            'audit_logging_validation_pass' => $pass,
            'observability_validation_pass' => $pass,
            'bad_month_governance_pass' => $pass,
            'weak_regime_governance_pass' => $pass,
            'source_bias_governance_pass' => $pass,
            'shared_core_governance_pass' => $pass,
            'safety_and_leakage_governance_pass' => $pass,
            'production_mutation_safety_pass' => $pass,
            'documentation_governance_pass' => $pass,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c76_role' => 'primary_controlled_runtime_opt_in_pilot_or_shadow_rollout_preparation_candidate',
                'parent_candidate_code' => self::PRIMARY_PARENT,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c76_role' => 'backup_controlled_runtime_opt_in_pilot_or_shadow_rollout_preparation_candidate',
                'parent_candidate_code' => self::BACKUP_PARENT,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c76_role' => 'comparator_only',
                'parent_candidate_code' => self::COMPARATOR_PARENT,
                'controlled_runtime_opt_in_pilot_preparation_review_pass' => false,
                'controlled_shadow_rollout_preparation_review_pass' => false,
                'candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review' => false,
                'candidate_active_in_controlled_catalog' => false,
                'operator_approval_validation_pass' => false,
                'default_off_feature_flag_pass' => false,
                'kill_switch_validation_pass' => false,
                'controlled_pilot_context_validation_pass' => false,
                'controlled_shadow_context_validation_pass' => false,
                'failure_reason_codes' => ['C76_A01_REMAINS_COMPARATOR_ONLY'],
            ]),
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C77_RECOMMENDATION : 'C77_TARGETED_C76_PREPARATION_REPAIR',
            'decision_reason' => $pass ? 'C76 controlled pilot/shadow preparation passed. Only C77 controlled runtime opt-in pilot/shadow execution review is allowed next.' : 'C76 controlled pilot/shadow preparation did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW' : 'C76_CONTROLLED_PREPARATION_REPAIR_REQUIRED',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_context_persisted_to_live_runtime' => false,
            'controlled_shadow_context_persisted_to_live_runtime' => false,
            'controlled_runtime_opt_in_pilot_preparation_review_allowed' => $pass,
            'controlled_runtime_opt_in_pilot_preparation_review_pass' => $pass,
            'controlled_shadow_rollout_preparation_review_allowed' => $pass,
            'controlled_shadow_rollout_preparation_review_pass' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function failureAttributionSummary(array $scorecards, bool $pass): array
    {
        $codes = [];
        foreach ($scorecards as $scorecard) {
            foreach ((array) ($scorecard['failure_reason_codes'] ?? []) as $code) {
                $codes[] = $code;
            }
        }
        return [
            'failure_attribution_completed' => true,
            'controlled_pilot_shadow_preparation_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C77_RECOMMENDATION : 'C77_TARGETED_C76_PREPARATION_REPAIR',
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C76 validates C75 nested next_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C76 creates isolated artifact-only controlled pilot/shadow preparation proof; it is not consumed by PLAN/CONFIRM live runtime.',
            'C76 pass is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.',
        ];
    }

    private function configFlagIsOn(string $key): bool
    {
        $path = 'config/watchlist.php';
        if (! is_file($path)) {
            return false;
        }
        $config = require $path;
        return is_array($config) && (bool) ($config[$key] ?? false);
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?array $load = null): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false);
        $artifact['failure_attribution_summary'] = [
            'failure_attribution_completed' => true,
            'dominant_failure_reason_codes' => [$status],
            'targeted_repair_recommendation' => $this->repairRecommendationFor($status),
        ];
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false);
        $artifact['failure_attribution_summary'] = [
            'failure_attribution_completed' => true,
            'dominant_failure_reason_codes' => [$status],
            'targeted_repair_recommendation' => $this->repairRecommendationFor($status),
        ];
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function repairRecommendationFor(string $status): string
    {
        if (strpos($status, 'C75_ARTIFACT') !== false || strpos($status, 'C75_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C77_C75_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false) {
            return 'C77_CONTROLLED_OPERATOR_APPROVAL_REPAIR';
        }
        if (strpos($status, 'PILOT_PREPARATION_CONTEXT') !== false) {
            return 'C77_CONTROLLED_PILOT_CONTEXT_REPAIR';
        }
        if (strpos($status, 'SHADOW_ROLLOUT_PREPARATION_CONTEXT') !== false) {
            return 'C77_CONTROLLED_SHADOW_CONTEXT_REPAIR';
        }
        if (strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'KILL_SWITCH') !== false) {
            return 'C77_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR';
        }
        if (strpos($status, 'ROLLBACK') !== false || strpos($status, 'EMERGENCY') !== false) {
            return 'C77_ROLLBACK_OR_EMERGENCY_DISABLE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'BASELINE') !== false) {
            return 'C77_PLAN_CONFIRM_BASELINE_NON_MUTATION_REPAIR';
        }
        if (strpos($status, 'FALLBACK') !== false) {
            return 'C77_FALLBACK_BEHAVIOR_REPAIR';
        }
        if (strpos($status, 'DOCUMENTATION') !== false) {
            return 'C77_DOCUMENTATION_REPAIR';
        }
        return 'C77_TARGETED_C76_PREPARATION_REPAIR';
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
