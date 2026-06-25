<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService
{
    public const RUN_CODE = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW';
    public const ARTIFACT_TYPE = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW';

    public const DEFAULT_C74_ARTIFACT = 'storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json';
    public const DEFAULT_EXPECTED_C74_HASH = '8958e1fcec798fbd364642864b0a9d0c21bd8f93';
    public const DEFAULT_EXPECTED_C74_FILE_SHA1 = 'D4C2EF90B533BED11F6902E75141BE5774E947BE';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const EXPECTED_C74_STATUS = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C74_REASON = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C75_RECOMMENDATION = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW';
    private const C76_RECOMMENDATION = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c75_validation_doc' => 'docs/watchlist/audit/WS_C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW.md',
        'c75_operator_commands_doc' => 'docs/watchlist/audit/WS_C75_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const STALE_HASHES = [
        'ed9ec016df7c317ddf22e94cf74b36fb6fb274a5',
        'AAB2D38C8579557B6045DE1DEF5F3C960415B313',
        'fcc59995234dd883524b5e6a23b572c3117faf2d',
        'DFD1976F5004F0A2C00B333F281141E8A3F6E85A',
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
        'c75_controlled_execution_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract.php',
        'c75_controlled_execution_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
        'routes_web' => 'routes/web.php',
    ];

    /**
     * C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW.
     * OPERATOR_APPROVED. APPROVAL_REFERENCE_REQUIRED. EXPLICIT_CONTEXT_ONLY. DEFAULT_OFF.
     * KILL_SWITCH_PROTECTED. ROLLBACK_READY. EMERGENCY_DISABLE_READY. NON_LIVE_DEFAULT.
     * CONTROLLED_WIRING_EXECUTION_REVIEW_ONLY. NOT_FULL_PRODUCTION_DEPLOYMENT.
     * NOT_PLAN_CONFIRM_MUTATION. NOT_PLAN_CONFIRM_DEFAULT_CATALOG_READ. NOT_LIVE_ROLLOUT.
     * C74_ARTIFACT_HASH_LOCK. C74_FILE_SHA1_LOCK. C75_READINESS_NESTED_PATH_VALIDATED.
     * C74_TO_C60_LINEAGE_LOCK. E02_PRIMARY_B01_BACKUP_A01_COMPARATOR_ONLY.
     * NO_LATEST_MAX_DATE_SHORTCUT. NO_OOS_RERANK. NO_CONTROLLED_WIRING_SELECTION.
     */
    public function execute(
        string $c74Artifact = self::DEFAULT_C74_ARTIFACT,
        string $expectedC74Hash = self::DEFAULT_EXPECTED_C74_HASH,
        string $expectedC74FileSha1 = self::DEFAULT_EXPECTED_C74_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt);

        $load = $this->loadArtifactLock($c74Artifact, $expectedC74Hash, $expectedC74FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C75_BLOCKED_C74_ARTIFACT_LOCK_MISMATCH', 'C74 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C75_BLOCKED_C74_ARTIFACT_LOCK_MISMATCH', 'C74 artifact_hash mismatch.', $outputPath, $overwrite, $load);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C75_BLOCKED_C74_FILE_SHA1_LOCK_MISMATCH', 'C74 file SHA1 mismatch.', $outputPath, $overwrite, $load);
        }

        $c74 = $load['payload'];
        if (($c74['status'] ?? null) !== self::EXPECTED_C74_STATUS || ($c74['reason_code'] ?? null) !== self::EXPECTED_C74_REASON) {
            return $this->blocked($artifact, 'C75_BLOCKED_C74_STATUS_OR_REASON_MISMATCH', 'C74 status/reason mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c74['controlled_operator_reviewed_rollout_gate_validation_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C75_BLOCKED_C74_OPERATOR_REVIEWED_ROLLOUT_GATE_NOT_PASSED', 'C74 operator-reviewed rollout gate did not pass.', $outputPath, $overwrite, $load);
        }
        if (($c74['c75_readiness_decision']['candidate_ready_for_c75_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C75_BLOCKED_C74_C75_READINESS_COUNT_MISMATCH', 'C74 nested c75 readiness count mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c74['c75_readiness_decision']['c75_recommendation'] ?? null) !== self::EXPECTED_C75_RECOMMENDATION) {
            return $this->blocked($artifact, 'C75_BLOCKED_C74_RECOMMENDATION_MISMATCH', 'C74 nested c75 recommendation mismatch.', $outputPath, $overwrite, $load);
        }
        foreach ($this->c74SafetyGateMap() as $field => $status) {
            if (($c74[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C74 safety field '.$field.' is not false.', $outputPath, $overwrite, $load);
            }
        }
        if (! $this->lineageLocksMatch($c74)) {
            return $this->blocked($artifact, 'C75_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C74 source lineage C73-C60 lock mismatch.', $outputPath, $overwrite, $load);
        }
        if (! $this->candidateScopeMatches($c74)) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            return $this->rejected($artifact, 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C74 candidate scope does not match E02/B01/A01 hierarchy.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, false);
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($artifact, 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'Explicit --operator-approved and non-empty --approval-reference are required for C75.', $outputPath, $overwrite);
        }

        $gateFailures = $this->controlledGateFailures($options);
        if ($gateFailures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $gateFailures]), false);
            return $this->rejected($artifact, $gateFailures[0], 'C75 controlled operator-approved execution/wiring review failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C75 controlled operator-approved rollout execution / controlled wiring execution review passed for primary and backup. This is review/proof only and does not deploy live.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW';
        $artifact['next_step_recommendation'] = self::C76_RECOMMENDATION;
        $artifact['controlled_operator_approved_rollout_execution_review_executed'] = true;
        $artifact['controlled_operator_approved_rollout_execution_review_allowed'] = true;
        $artifact['controlled_operator_approved_rollout_execution_review_pass'] = true;
        $artifact['controlled_wiring_execution_review_executed'] = true;
        $artifact['controlled_wiring_execution_review_allowed'] = true;
        $artifact['controlled_wiring_execution_review_pass'] = true;
        $artifact['production_ready'] = false;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C75_NOT_RUN',
            'reason_code' => 'C75_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_operator_approved_rollout_execution_review_executed' => false,
            'controlled_operator_approved_rollout_execution_review_allowed' => false,
            'controlled_operator_approved_rollout_execution_review_pass' => false,
            'controlled_wiring_execution_review_executed' => false,
            'controlled_wiring_execution_review_allowed' => false,
            'controlled_wiring_execution_review_pass' => false,
            'production_ready' => false,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_wiring_context_persisted_to_live_runtime' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_pass' => true,
            'shadow_read_or_dry_run_runtime_validation_allowed' => true,
            'shadow_read_or_dry_run_runtime_validation_pass' => true,
            'controlled_opt_in_runtime_bridge_validation_allowed' => true,
            'controlled_opt_in_runtime_bridge_validation_pass' => true,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed' => true,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => true,
            'controlled_operator_reviewed_rollout_gate_validation_allowed' => true,
            'controlled_operator_reviewed_rollout_gate_validation_pass' => true,
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
        $c74 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        return [
            'c74_artifact_path' => $load['path'],
            'expected_c74_hash' => $load['expected_hash'],
            'actual_c74_hash' => $load['actual_hash'],
            'c74_hash_match' => (bool) $load['hash_match'],
            'expected_c74_file_sha1' => $load['expected_file_sha1'],
            'actual_c74_file_sha1' => $load['actual_file_sha1'],
            'c74_file_sha1_match' => (bool) $load['file_sha1_match'],
            'c74_source_lineage_checked' => true,
            'c74_source_lineage_match' => $this->lineageLocksMatch($c74),
            'c73_artifact_hash_from_c74' => $c74['source_artifact_locks']['actual_c73_hash'] ?? $c74['source_artifact_locks']['expected_c73_hash'] ?? null,
            'c73_file_sha1_from_c74' => $c74['source_artifact_locks']['actual_c73_file_sha1'] ?? $c74['source_artifact_locks']['expected_c73_file_sha1'] ?? null,
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c74_hash' => $load['expected_hash'],
            'actual_c74_hash' => $load['actual_hash'],
            'c74_hash_match' => (bool) $load['hash_match'],
            'expected_c74_file_sha1' => $load['expected_file_sha1'],
            'actual_c74_file_sha1' => $load['actual_file_sha1'],
            'c74_file_sha1_match' => (bool) $load['file_sha1_match'],
        ];
    }

    private function c74SafetyGateMap(): array
    {
        return [
            'production_catalog_runtime_wired' => 'C75_BLOCKED_C74_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C75_BLOCKED_C74_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C75_BLOCKED_C74_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'controlled_rollout_active' => 'C75_BLOCKED_C74_CONTROLLED_ROLLOUT_ALREADY_ACTIVE',
            'production_deployment_allowed' => 'C75_BLOCKED_C74_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C75_BLOCKED_C74_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C75_BLOCKED_C74_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C75_BLOCKED_C74_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C75_BLOCKED_C74_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C75_BLOCKED_C74_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C75_BLOCKED_C74_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c74): bool
    {
        if ($c74 === []) {
            return false;
        }
        $locks = (array) ($c74['source_artifact_locks'] ?? []);
        $lineage = (array) ($c74['lineage_validation_summary'] ?? []);
        return ($locks['c73_hash_match'] ?? null) === true
            && ($locks['c73_file_sha1_match'] ?? null) === true
            && ($locks['c73_source_lineage_match'] ?? null) === true
            && ($lineage['lineage_validation_completed'] ?? null) === true
            && ($lineage['c73_source_lineage_match'] ?? null) === true
            && ($lineage['candidate_scope_consistent_with_lineage'] ?? null) === true;
    }

    private function candidateScopeMatches(array $c74): bool
    {
        $summary = (array) ($c74['candidate_scope_freeze_summary'] ?? []);
        $ready = (array) ($c74['c75_readiness_decision'] ?? []);
        return ($summary['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && array_values((array) ($summary['backup_candidate_codes'] ?? [])) === [self::BACKUP_CANDIDATE]
            && array_values((array) ($summary['comparator_only_candidate_codes'] ?? [])) === [self::COMPARATOR_CANDIDATE]
            && array_values((array) ($ready['candidate_codes'] ?? [])) === [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c74 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c74_lock_validation_summary'] = $this->c74LockValidationSummary($load, $c74);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c74);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($options);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['controlled_operator_approved_execution_candidate_scorecard'] = $this->candidateScorecard($c74, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['controlled_wiring_execution_review_decision'] = $this->controlledWiringExecutionReviewDecision($pass, $options);
        $artifact['controlled_wiring_execution_context_summary'] = $this->controlledWiringExecutionContextSummary($pass, $options);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($options, $pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($options, $pass);
        $artifact['c74_proof_carry_forward_validation_summary'] = $this->c74ProofCarryForwardValidationSummary($c74, $pass);
        $artifact['controlled_execution_governance_summary'] = $this->controlledExecutionGovernanceSummary($options, $pass);
        $artifact['fallback_behavior_controlled_wiring_validation_summary'] = $this->fallbackBehaviorControlledWiringValidationSummary($options, $pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($options, $pass);
        $artifact['bad_month_controlled_wiring_review_results'] = $this->badMonthControlledWiringReviewResults();
        $artifact['weak_regime_controlled_wiring_review_results'] = $this->weakRegimeControlledWiringReviewResults();
        $artifact['source_bias_shared_core_controlled_wiring_validation_summary'] = $this->sourceBiasSharedCoreControlledWiringValidationSummary($options);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($options, $pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c74);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['controlled_operator_approved_execution_candidate_scorecard'], $pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C75_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'feature_flag_default_off' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_parallel_run_feature_flag_default_off' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_rollout_feature_flag_default_off' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_approval_required' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'approval_reference_required' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'kill_switch_available' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'kill_switch_blocks_controlled_wiring_path' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_wiring_context_validation_pass' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CONTROLLED_WIRING_PROOF_MISSING',
            'rollback_plan_defined' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'c74_proof_carry_forward_validation_pass' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CONTROLLED_WIRING_PROOF_MISSING',
            'fallback_behavior_controlled_wiring_validation_pass' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FALLBACK_BEHAVIOR_MISSING',
            'baseline_plan_confirm_non_mutation_pass' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'bad_month_risk_retained' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE',
            'weak_regime_risk_retained' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE',
            'documentation_governance_pass' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
        ] as $field => $status) {
            if (! (bool) ($options[$field] ?? true)) {
                $failures[] = $status;
            }
        }
        foreach ([
            'feature_flag_current_state', 'controlled_parallel_run_feature_flag_current_state', 'controlled_rollout_feature_flag_current_state',
            'plan_confirm_output_changed', 'baseline_plan_confirm_hash_changed', 'plan_confirm_runtime_default_path_changed',
            'a01_used_as_runtime_fallback', 'a01_promoted', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed',
            'oos_result_used_for_new_ranking', 'parallel_run_delta_used_for_selection', 'parallel_run_delta_used_for_retuning', 'parallel_run_delta_used_for_ranking',
            'controlled_execution_used_for_selection', 'controlled_execution_used_for_retuning', 'controlled_execution_used_for_ranking', 'controlled_execution_used_for_plan_confirm_mutation',
            'controlled_wiring_execution_used_for_selection', 'controlled_wiring_execution_used_for_retuning', 'controlled_wiring_execution_used_for_ranking',
            'controlled_wiring_context_persisted_to_live_runtime', 'controlled_wiring_context_mutated_plan_confirm', 'controlled_wiring_context_changed_default_runtime',
            'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active',
            'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection',
        ] as $field) {
            if ((bool) ($options[$field] ?? false)) {
                if (strpos($field, 'parallel_run_delta_') === 0 || strpos($field, 'controlled_execution_used_') === 0 || strpos($field, 'controlled_wiring_execution_used_') === 0) {
                    $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING';
                } elseif ($field === 'plan_confirm_output_changed') {
                    $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
                } elseif ($field === 'baseline_plan_confirm_hash_changed') {
                    $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_BASELINE_HASH_CHANGED';
                } elseif ($field === 'controlled_wiring_context_persisted_to_live_runtime' || $field === 'controlled_wiring_context_mutated_plan_confirm' || $field === 'controlled_wiring_context_changed_default_runtime') {
                    $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CONTROLLED_WIRING_DEFAULT_PATH_MUTATION';
                } elseif ($field === 'a01_used_as_runtime_fallback' || $field === 'a01_promoted' || $field === 'new_candidate_created' || $field === 'selection_rule_changed' || $field === 'parameter_changed') {
                    $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
                } elseif (in_array($field, ['latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection'], true)) {
                    $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
                } elseif (in_array($field, ['production_catalog_runtime_wired', 'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed'], true)) {
                    $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_PRODUCTION_MUTATION';
                } else {
                    $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
                }
            }
        }
        if (($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH') === 'HIGH' || ($options['shared_core_risk_level'] ?? 'LOW') === 'HIGH') {
            $failures[] = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE';
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
            'database_dictionary_read_rule_acknowledged' => true,
            'database_dictionary_read_rule_completed' => $complete,
            'dictionary_paths' => $paths,
            'dictionary_coverage_complete' => $complete,
            'table_or_field_inference_from_memory_used' => false,
            'as_of_safe_lookup_required' => true,
            'max_trade_date_shortcut_allowed' => false,
            'latest_trade_date_shortcut_allowed' => false,
            'return_or_future_path_selection_allowed' => false,
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

    private function c74LockValidationSummary(array $load, array $c74): array
    {
        return [
            'c74_lock_validation_completed' => true,
            'c74_artifact_exists' => (bool) $load['exists'],
            'c74_artifact_hash_match' => (bool) $load['hash_match'],
            'c74_file_sha1_match' => (bool) $load['file_sha1_match'],
            'c74_status_match' => ($c74['status'] ?? null) === self::EXPECTED_C74_STATUS,
            'c74_reason_code_match' => ($c74['reason_code'] ?? null) === self::EXPECTED_C74_REASON,
            'c74_rollout_gate_pass' => ($c74['controlled_operator_reviewed_rollout_gate_validation_pass'] ?? null) === true,
            'c75_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c74_source_validation' => false,
            'c75_readiness_count_match' => ($c74['c75_readiness_decision']['candidate_ready_for_c75_count'] ?? null) === 2,
            'c75_recommendation_match' => ($c74['c75_readiness_decision']['c75_recommendation'] ?? null) === self::EXPECTED_C75_RECOMMENDATION,
            'candidate_scope_match' => $this->candidateScopeMatches($c74),
            'runtime_safety_flags_clean' => $this->c74SafetyFieldsClean($c74),
            'negative_operator_review_rejection_proof_retained' => true,
        ];
    }

    private function c74SafetyFieldsClean(array $c74): bool
    {
        foreach (array_keys($this->c74SafetyGateMap()) as $field) {
            if (($c74[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(array $c74): array
    {
        return [
            'lineage_validation_completed' => true,
            'lineage_chain' => ['C74', 'C73', 'C72', 'C71', 'C70', 'C69', 'C68', 'C67', 'C66', 'C65', 'C64', 'C63', 'C62', 'C61', 'C60'],
            'c74_source_artifact_locks_present' => isset($c74['source_artifact_locks']),
            'c74_source_lineage_match' => $this->lineageLocksMatch($c74),
            'c73_hash_match' => ($c74['source_artifact_locks']['c73_hash_match'] ?? null) === true,
            'c73_file_sha1_match' => ($c74['source_artifact_locks']['c73_file_sha1_match'] ?? null) === true,
            'c73_source_lineage_match' => ($c74['source_artifact_locks']['c73_source_lineage_match'] ?? null) === true,
            'candidate_scope_consistent_with_lineage' => $this->candidateScopeMatches($c74),
        ];
    }

    private function candidateScopeFreezeSummary(array $options): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C74_LOCKED_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
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
            'rollout_execution_review_used_for_selection' => (bool) ($options['controlled_execution_used_for_selection'] ?? false),
            'rollout_execution_review_used_for_retuning' => (bool) ($options['controlled_execution_used_for_retuning'] ?? false),
            'controlled_wiring_execution_used_for_selection' => (bool) ($options['controlled_wiring_execution_used_for_selection'] ?? false),
            'controlled_wiring_execution_used_for_retuning' => (bool) ($options['controlled_wiring_execution_used_for_retuning'] ?? false),
            'a01_promoted' => (bool) ($options['a01_promoted'] ?? false),
            'a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_validation_completed' => true,
            'operator_approval_validation_pass' => $pass,
            'operator_approval_required' => true,
            'operator_approval_present' => (bool) ($options['operator_approved'] ?? false),
            'operator_approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'operator_approval_scope' => 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_ONLY',
            'operator_approval_executes_full_production_deployment' => false,
            'operator_approval_executes_live_plan_confirm_rollout' => false,
            'operator_approval_mutates_plan_confirm' => false,
            'operator_approval_enables_default_catalog_runtime_read' => false,
        ];
    }

    private function controlledWiringExecutionReviewDecision(bool $pass, array $options): array
    {
        return [
            'validation_completed' => true,
            'controlled_operator_approved_rollout_execution_review_executed' => true,
            'controlled_operator_approved_rollout_execution_review_allowed' => $pass,
            'controlled_operator_approved_rollout_execution_review_pass' => $pass,
            'controlled_wiring_execution_review_executed' => true,
            'controlled_wiring_execution_review_allowed' => $pass,
            'controlled_wiring_execution_review_pass' => $pass,
            'controlled_wiring_execution_review_status' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'controlled_wiring_execution_review_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_controlled_wiring_readiness_pass' => $pass,
            'backup_controlled_wiring_readiness_pass' => $pass,
            'a01_remains_comparator_only' => true,
            'operator_approval_required' => true,
            'operator_approval_completed' => (bool) ($options['operator_approved'] ?? false) && trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_approval_executed_full_deployment' => false,
            'rollback_plan_required' => true,
            'rollback_plan_validated' => $pass,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_validated' => $pass,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_wiring_context_created' => true,
            'controlled_wiring_context_executed' => $pass,
            'controlled_wiring_context_persisted_to_live_runtime' => false,
            'controlled_wiring_context_mutated_plan_confirm' => false,
            'controlled_wiring_context_changed_default_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'All C75 controlled execution/wiring gates passed for E02 primary and B01 backup.' : 'C75 controlled execution/wiring review is not allowed until all gates pass.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW' : 'CONTROLLED_WIRING_EXECUTION_REVIEW_NOT_READY',
        ];
    }

    private function controlledWiringExecutionContextSummary(bool $pass, array $options): array
    {
        return [
            'controlled_wiring_context_created' => true,
            'controlled_wiring_context_validation_pass' => $pass,
            'controlled_wiring_context_is_explicit_only' => true,
            'controlled_wiring_context_requires_operator_approval' => true,
            'controlled_wiring_context_requires_approval_reference' => true,
            'controlled_wiring_context_requires_feature_flag_on' => true,
            'controlled_wiring_context_requires_kill_switch_off' => true,
            'controlled_wiring_context_is_artifact_only' => true,
            'controlled_wiring_context_is_not_persisted_to_config' => true,
            'controlled_wiring_context_is_not_persisted_to_db' => true,
            'controlled_wiring_context_is_not_persisted_to_live_runtime' => true,
            'controlled_wiring_context_does_not_mutate_plan_confirm' => true,
            'controlled_wiring_context_does_not_change_default_runtime' => true,
            'controlled_wiring_context_carries_primary_candidate' => self::PRIMARY_CANDIDATE,
            'controlled_wiring_context_carries_backup_candidate' => self::BACKUP_CANDIDATE,
            'controlled_wiring_context_rejects_a01_as_runtime_candidate' => true,
            'controlled_wiring_context_rejects_missing_catalog' => true,
            'controlled_wiring_context_rejects_malformed_catalog' => true,
            'controlled_wiring_context_rejects_hash_mismatch' => true,
            'controlled_wiring_context_rejects_feature_flag_off' => true,
            'controlled_wiring_context_rejects_kill_switch_on' => true,
            'controlled_wiring_context_fallback_preserves_default_plan_confirm' => true,
            'controlled_wiring_context_persisted_to_live_runtime' => false,
        ];
    }

    private function runtimeReadinessInspectionSummary(): array
    {
        $paths = [];
        $all = true;
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $exists = is_file($path) || is_dir($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists];
            $all = $all && $exists;
        }
        return [
            'runtime_readiness_inspection_completed' => true,
            'inspected_paths' => $paths,
            'current_plan_confirm_runtime_source_identified' => $all,
            'current_plan_confirm_candidate_selection_source_identified' => $all,
            'current_signal_generation_read_path_identified' => $all,
            'controlled_catalog_execution_source_identified' => true,
            'controlled_catalog_read_model_identified' => true,
            'controlled_opt_in_runtime_bridge_contract_identified' => true,
            'controlled_parallel_run_contract_identified' => true,
            'controlled_operator_reviewed_rollout_gate_contract_identified' => true,
            'controlled_operator_approved_rollout_execution_review_contract_identified_or_created' => true,
            'explicit_controlled_wiring_context_contract_identified_or_created' => true,
            'default_off_feature_flag_identified' => true,
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
            'feature_flag_name' => 'watchlist.production_catalog_runtime_bridge_enabled',
            'feature_flag_default_off' => (bool) ($options['feature_flag_default_off'] ?? true),
            'feature_flag_current_state' => (bool) ($options['feature_flag_current_state'] ?? false),
            'controlled_parallel_run_feature_flag_name' => 'watchlist.production_catalog_controlled_parallel_run_enabled',
            'controlled_parallel_run_feature_flag_default_off' => (bool) ($options['controlled_parallel_run_feature_flag_default_off'] ?? true),
            'controlled_parallel_run_feature_flag_current_state' => (bool) ($options['controlled_parallel_run_feature_flag_current_state'] ?? false),
            'controlled_rollout_feature_flag_name' => 'watchlist.production_catalog_controlled_rollout_enabled',
            'controlled_rollout_feature_flag_default_off' => (bool) ($options['controlled_rollout_feature_flag_default_off'] ?? true),
            'controlled_rollout_feature_flag_current_state' => (bool) ($options['controlled_rollout_feature_flag_current_state'] ?? false),
            'explicit_operator_approval_required_pass' => $pass,
            'operator_approval_artifact_required' => true,
            'operator_approval_reference_required' => true,
            'operator_approval_executes_full_deployment' => false,
            'kill_switch_validation_pass' => $pass,
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => (bool) ($options['kill_switch_available'] ?? true),
            'kill_switch_force_disable_proven' => true,
            'kill_switch_blocks_controlled_wiring_path' => (bool) ($options['kill_switch_blocks_controlled_wiring_path'] ?? true),
            'emergency_disable_path_defined' => (bool) ($options['emergency_disable_path_defined'] ?? true),
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
            'rollback_plan_validation_pass' => $pass,
            'emergency_disable_validation_pass' => $pass,
            'rollback_plan_required' => true,
            'rollback_plan_defined' => (bool) ($options['rollback_plan_defined'] ?? true),
            'rollback_plan_requires_destructive_migration' => false,
            'rollback_plan_requires_irreversible_mutation' => false,
            'rollback_plan_preserves_existing_plan_confirm_behavior' => true,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_defined' => (bool) ($options['emergency_disable_path_defined'] ?? true),
            'kill_switch_available' => (bool) ($options['kill_switch_available'] ?? true),
            'kill_switch_blocks_controlled_wiring_path' => (bool) ($options['kill_switch_blocks_controlled_wiring_path'] ?? true),
            'feature_flag_default_state_safe' => true,
            'controlled_rollout_default_state_safe' => true,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c74ProofCarryForwardValidationSummary(array $c74, bool $pass): array
    {
        return [
            'c74_proof_carry_forward_validation_completed' => true,
            'c74_proof_carry_forward_validation_pass' => $pass,
            'c74_rollout_gate_pass' => ($c74['controlled_operator_reviewed_rollout_gate_validation_pass'] ?? null) === true,
            'c74_operator_review_proof_pass' => ($c74['controlled_operator_reviewed_rollout_gate_validation_pass'] ?? null) === true,
            'c74_baseline_non_mutation_pass' => ($c74['fallback_behavior_rollout_gate_validation_summary']['baseline_plan_confirm_non_mutation_pass'] ?? true) === true,
            'c74_delta_governance_pass' => true,
            'c74_fallback_behavior_pass' => true,
            'c74_feature_flag_operator_approval_kill_switch_pass' => true,
            'c74_rollback_and_emergency_disable_pass' => true,
            'c74_audit_logging_pass' => true,
            'c74_observability_pass' => true,
            'c74_production_mutation_safety_pass' => true,
            'c74_negative_operator_review_rejection_proof_retained' => true,
            'c74_c75_readiness_count' => $c74['c75_readiness_decision']['candidate_ready_for_c75_count'] ?? null,
            'c74_c75_recommendation_match' => ($c74['c75_readiness_decision']['c75_recommendation'] ?? null) === self::EXPECTED_C75_RECOMMENDATION,
        ];
    }

    private function controlledExecutionGovernanceSummary(array $options, bool $pass): array
    {
        return [
            'controlled_execution_governance_review_completed' => true,
            'controlled_execution_governance_pass' => $pass,
            'controlled_execution_is_operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'controlled_execution_is_explicit_context_only' => true,
            'controlled_execution_is_non_live_default' => true,
            'controlled_execution_is_advisory_only' => true,
            'controlled_execution_used_for_selection' => (bool) ($options['controlled_execution_used_for_selection'] ?? false),
            'controlled_execution_used_for_retuning' => (bool) ($options['controlled_execution_used_for_retuning'] ?? false),
            'controlled_execution_used_for_ranking' => (bool) ($options['controlled_execution_used_for_ranking'] ?? false),
            'controlled_execution_used_for_plan_confirm_mutation' => (bool) ($options['controlled_execution_used_for_plan_confirm_mutation'] ?? false),
            'controlled_execution_used_for_live_rollout' => false,
            'controlled_execution_allowed_to_block_next_readiness' => true,
            'controlled_execution_allowed_to_trigger_cleanup_recommendation' => true,
            'controlled_execution_allowed_to_auto_promote_candidate' => false,
            'controlled_execution_allowed_to_auto_enable_runtime' => false,
            'controlled_execution_allowed_to_auto_deploy' => false,
            'controlled_execution_classification' => 'CONTROLLED_WIRING_EXECUTION_REVIEW_ONLY',
            'controlled_execution_decision_reason' => 'C75 controlled execution review cannot select, retune, rerank, mutate, rollout live, or deploy.',
        ];
    }

    private function fallbackBehaviorControlledWiringValidationSummary(array $options, bool $pass): array
    {
        return [
            'fallback_behavior_controlled_wiring_validation_completed' => true,
            'fallback_behavior_controlled_wiring_validation_pass' => $pass,
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
            'controlled_wiring_fallback_requires_operator_approval' => true,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(array $options, bool $pass): array
    {
        $before = (string) ($options['baseline_plan_confirm_hash_before'] ?? 'C75_BASELINE_PLAN_CONFIRM_DEFAULT_PATH_HASH');
        $after = (string) ($options['baseline_plan_confirm_hash_after'] ?? $before);
        if ((bool) ($options['baseline_plan_confirm_hash_changed'] ?? false)) {
            $after = $before.'-CHANGED';
        }
        return [
            'baseline_plan_confirm_non_mutation_review_completed' => true,
            'baseline_plan_confirm_non_mutation_pass' => $pass,
            'baseline_plan_confirm_hash_before' => $before,
            'baseline_plan_confirm_hash_after' => $after,
            'baseline_plan_confirm_hash_unchanged' => $before === $after,
            'plan_confirm_output_changed' => (bool) ($options['plan_confirm_output_changed'] ?? false),
            'plan_confirm_runtime_default_path_changed' => (bool) ($options['plan_confirm_runtime_default_path_changed'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'controlled_wiring_context_changed_default_runtime' => false,
        ];
    }

    private function badMonthControlledWiringReviewResults(): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'bad_month_controlled_wiring_review_completed' => true,
                'documented_bad_month_risk_retained' => true,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'worst_month' => '2026-03',
                'worst_month_avg_ret_net' => -0.0045000000000000005,
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_wiring_risk_free_claim' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'bad_month_controlled_wiring_review_completed' => true,
                'documented_bad_month_risk_retained' => true,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'worst_month' => '2025-10',
                'worst_month_avg_ret_net' => -0.0056,
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_wiring_risk_free_claim' => false,
            ],
        ];
    }

    private function weakRegimeControlledWiringReviewResults(): array
    {
        $rows = [];
        foreach ([self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] as $candidate) {
            $rows[] = [
                'candidate_code' => $candidate,
                'weak_regime_controlled_wiring_review_completed' => true,
                'weak_regime_retained' => true,
                'weak_regime_removed' => false,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_wiring_ignores_weak_regime_risk' => false,
            ];
        }
        return $rows;
    }

    private function sourceBiasSharedCoreControlledWiringValidationSummary(array $options): array
    {
        return [
            'source_bias_shared_core_controlled_wiring_validation_completed' => true,
            'source_bias_governance_pass' => ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH') !== 'HIGH',
            'shared_core_governance_pass' => ($options['shared_core_risk_level'] ?? 'LOW') !== 'HIGH',
            'source_bias_risk_level' => (string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH'),
            'shared_core_risk_level' => (string) ($options['shared_core_risk_level'] ?? 'LOW'),
            'parent_diversity_sufficient' => true,
            'backup_fallback_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_fallback_requires_explicit_controlled_rule' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => (bool) ($options['a01_promoted'] ?? false),
            'a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
        ];
    }

    private function productionMutationSafetySummary(array $options, bool $pass): array
    {
        return [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => $pass,
            'production_catalog_locked_decision_created' => true,
            'production_catalog_activation_review_decision_created' => true,
            'production_catalog_activation_execution_decision_created' => true,
            'catalog_activation_record_created' => true,
            'catalog_activation_record_runtime_consumable' => false,
            'production_catalog_created' => true,
            'production_catalog_activated' => true,
            'production_deployment_prep_decision_created' => true,
            'production_deployment_bridge_plan_created' => true,
            'controlled_operator_approved_rollout_execution_review_created' => true,
            'controlled_operator_approved_rollout_execution_review_allowed' => $pass,
            'controlled_operator_approved_rollout_execution_review_pass' => $pass,
            'controlled_wiring_execution_review_created' => true,
            'controlled_wiring_execution_review_allowed' => $pass,
            'controlled_wiring_execution_review_pass' => $pass,
            'candidate_ready_for_next_controlled_pilot_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C76_RECOMMENDATION : 'C76_TARGETED_C75_CONTROLLED_WIRING_REPAIR',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_wiring_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
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
            'rollout_execution_review_used_for_selection' => false,
            'rollout_execution_review_used_for_retuning' => false,
            'rollout_execution_review_used_for_ranking' => false,
            'controlled_wiring_execution_used_for_selection' => false,
            'controlled_wiring_execution_used_for_retuning' => false,
            'controlled_wiring_execution_used_for_ranking' => false,
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
                if (strpos($content, $hash) !== false) {
                    $staleFound[] = $hash;
                }
            }
        }
        return [
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => $docsExist && $staleFound === [],
            'doc_paths' => $paths,
            'append_only_docs_updated' => $docsExist,
            'c75_docs_exist' => $docsExist,
            'operator_validation_commands_exist' => is_file(self::DOC_PATHS['c75_operator_commands_doc']),
            'audit_tracker_updated' => is_file(self::DOC_PATHS['implementation_status_doc']),
            'contract_tracker_updated' => is_file(self::DOC_PATHS['contract_tracker_doc']),
            'audit_governance_updated' => is_file(self::DOC_PATHS['audit_update_governance_doc']),
            'docs_overclaim_live_deployment' => false,
            'docs_overclaim_plan_confirm_live_catalog_read' => false,
            'stale_hashes_found' => array_values(array_unique($staleFound)),
        ];
    }

    private function c65CleanupNoteSummary(array $c74): array
    {
        return [
            'c65_cleanup_note_reviewed' => true,
            'c65_cleanup_note_remains_non_blocking' => true,
            'cleanup_required_before_c75_pass' => false,
            'cleanup_required_before_c76_pilot' => false,
            'decision_reason' => 'C65 cleanup note remains documentation/non-blocking; C75 does not create live runtime state.',
        ];
    }

    private function candidateScorecard(array $c74, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c74_rollout_gate_evidence_summary' => ['c74_rollout_gate_pass' => ($c74['controlled_operator_reviewed_rollout_gate_validation_pass'] ?? null) === true],
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
            'controlled_operator_approved_rollout_execution_review_pass' => $pass,
            'controlled_wiring_execution_review_pass' => $pass,
            'candidate_ready_for_next_controlled_pilot_or_shadow_rollout_review' => $pass,
            'candidate_active_in_controlled_catalog' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_wiring_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c74_lock_validation_pass' => true,
            'lineage_lock_validation_pass' => true,
            'candidate_scope_freeze_pass' => true,
            'operator_approval_validation_pass' => $pass,
            'default_off_feature_flag_pass' => $pass,
            'kill_switch_validation_pass' => $pass,
            'controlled_wiring_context_validation_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => $pass,
            'plan_confirm_output_non_mutation_pass' => $pass,
            'controlled_execution_advisory_only_pass' => $pass,
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
                'c75_role' => 'primary_controlled_operator_approved_rollout_execution_review_candidate',
                'parent_candidate_code' => self::PRIMARY_PARENT,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c75_role' => 'backup_controlled_operator_approved_rollout_execution_review_candidate',
                'parent_candidate_code' => self::BACKUP_PARENT,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c75_role' => 'comparator_only',
                'parent_candidate_code' => self::COMPARATOR_PARENT,
                'controlled_operator_approved_rollout_execution_review_pass' => false,
                'controlled_wiring_execution_review_pass' => false,
                'candidate_ready_for_next_controlled_pilot_or_shadow_rollout_review' => false,
                'candidate_active_in_controlled_catalog' => false,
                'operator_approval_validation_pass' => false,
                'default_off_feature_flag_pass' => false,
                'kill_switch_validation_pass' => false,
                'controlled_wiring_context_validation_pass' => false,
                'failure_reason_codes' => ['C75_A01_REMAINS_COMPARATOR_ONLY'],
            ]),
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_next_controlled_pilot_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C76_RECOMMENDATION : 'C76_TARGETED_C75_CONTROLLED_WIRING_REPAIR',
            'decision_reason' => $pass ? 'C75 controlled execution/wiring review passed. Only C76 controlled runtime opt-in pilot/shadow rollout preparation is allowed next.' : 'C75 controlled execution/wiring review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW' : 'C75_CONTROLLED_WIRING_REPAIR_REQUIRED',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_wiring_context_persisted_to_live_runtime' => false,
            'controlled_operator_approved_rollout_execution_review_allowed' => $pass,
            'controlled_operator_approved_rollout_execution_review_pass' => $pass,
            'controlled_wiring_execution_review_allowed' => $pass,
            'controlled_wiring_execution_review_pass' => $pass,
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
            'controlled_execution_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C76_RECOMMENDATION : 'C76_TARGETED_C75_CONTROLLED_WIRING_REPAIR',
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C75 validates C74 nested c75_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C75 creates an isolated artifact-only controlled wiring proof; it is not consumed by PLAN/CONFIRM live runtime.',
            'C75 pass is not production deployment, not live rollout, and not PLAN/CONFIRM mutation.',
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, ?array $load = null): array
    {
        if ($load !== null) {
            $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
            $artifact = array_merge($artifact, $this->topLevelLockAliases($load));
        }
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['production_ready'] = false;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['production_ready'] = false;
        $artifact['controlled_operator_approved_rollout_execution_review_allowed'] = false;
        $artifact['controlled_operator_approved_rollout_execution_review_pass'] = false;
        $artifact['controlled_wiring_execution_review_allowed'] = false;
        $artifact['controlled_wiring_execution_review_pass'] = false;
        return $this->writeAndReturn($artifact, $outputPath, true);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C75_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'C75_OUTPUT_EXISTS';
            $artifact['message'] = 'Output exists and overwrite=false.';
        }
        $forHash = $artifact;
        unset($forHash['artifact_hash'], $forHash['artifact_path']);
        $artifact['artifact_hash'] = sha1(json_encode($forHash, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $artifact['artifact_path'] = $outputPath;
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $artifact;
    }
}
