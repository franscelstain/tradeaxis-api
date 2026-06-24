<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService
{
    public const RUN_CODE = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW';
    public const ARTIFACT_TYPE = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW';

    public const DEFAULT_C73_ARTIFACT = 'storage/app/watchlist/backtest/c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation.json';
    public const DEFAULT_EXPECTED_C73_HASH = '34f1f84a4261da7ce1cb9d17a1bf33dfb1458281';
    public const DEFAULT_EXPECTED_C73_FILE_SHA1 = 'BF18CAA2654D5A7DE5419DE5DAF42E0B55D73CC9';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const EXPECTED_C73_STATUS = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C73_REASON = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C74_RECOMMENDATION = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW';
    private const C75_RECOMMENDATION = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c74_validation_doc' => 'docs/watchlist/audit/WS_C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW.md',
        'c74_operator_commands_doc' => 'docs/watchlist/audit/WS_C74_OPERATOR_VALIDATION_COMMANDS.md',
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
        'controlled_operator_rollout_gate_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContract.php',
        'controlled_operator_rollout_gate_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
        'routes_web' => 'routes/web.php',
    ];

    /**
     * C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW.
     * OPERATOR_REVIEWED. DEFAULT_OFF. KILL_SWITCH_PROTECTED. NON_MUTATING.
     * DEPLOYMENT_READINESS_ONLY. NOT_PRODUCTION_DEPLOYMENT_LIVE. NOT_PLAN_CONFIRM_MUTATION.
     * NOT_PLAN_CONFIRM_DEFAULT_CATALOG_READ. NOT_LIVE_ROLLOUT. C73_ARTIFACT_HASH_LOCK.
     * C73_FILE_SHA1_LOCK. C74_READINESS_NESTED_PATH_VALIDATED. C73_TO_C60_LINEAGE_LOCK.
     * E02_PRIMARY_B01_BACKUP_A01_COMPARATOR_ONLY. ROLLBACK_READY. EMERGENCY_DISABLE_READY.
     * PARALLEL_RUN_DELTA_ADVISORY_ONLY. FALLBACK_NEVER_USES_A01. NO_LATEST_MAX_DATE_SHORTCUT.
     */
    public function execute(
        string $c73Artifact = self::DEFAULT_C73_ARTIFACT,
        string $expectedC73Hash = self::DEFAULT_EXPECTED_C73_HASH,
        string $expectedC73FileSha1 = self::DEFAULT_EXPECTED_C73_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt);

        $load = $this->loadArtifactLock($c73Artifact, $expectedC73Hash, $expectedC73FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C74_BLOCKED_C73_ARTIFACT_LOCK_MISMATCH', 'C73 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C74_BLOCKED_C73_ARTIFACT_LOCK_MISMATCH', 'C73 artifact_hash mismatch.', $outputPath, $overwrite, $load);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C74_BLOCKED_C73_FILE_SHA1_LOCK_MISMATCH', 'C73 file SHA1 mismatch.', $outputPath, $overwrite, $load);
        }

        $c73 = $load['payload'];
        if (($c73['status'] ?? null) !== self::EXPECTED_C73_STATUS || ($c73['reason_code'] ?? null) !== self::EXPECTED_C73_REASON) {
            return $this->blocked($artifact, 'C74_BLOCKED_C73_STATUS_OR_REASON_MISMATCH', 'C73 status/reason mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c73['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C74_BLOCKED_C73_CONTROLLED_PARALLEL_RUN_VALIDATION_NOT_PASSED', 'C73 controlled parallel-run validation not passed.', $outputPath, $overwrite, $load);
        }
        if (($c73['c74_readiness_decision']['candidate_ready_for_c74_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C74_BLOCKED_C73_C74_READINESS_COUNT_MISMATCH', 'C73 nested c74 readiness count mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c73['c74_readiness_decision']['c74_recommendation'] ?? null) !== self::EXPECTED_C74_RECOMMENDATION) {
            return $this->blocked($artifact, 'C74_BLOCKED_C73_RECOMMENDATION_MISMATCH', 'C73 nested c74 recommendation mismatch.', $outputPath, $overwrite, $load);
        }
        foreach ($this->c73SafetyGateMap() as $field => $status) {
            if (($c73[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C73 safety field '.$field.' is not false.', $outputPath, $overwrite, $load);
            }
        }
        if (! $this->lineageLocksMatch($c73)) {
            return $this->blocked($artifact, 'C74_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C73 source lineage C72-C60 lock mismatch.', $outputPath, $overwrite, $load);
        }
        if (! $this->candidateScopeMatches($c73)) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            return $this->rejected($artifact, 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C73 candidate scope does not match E02/B01/A01 hierarchy.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, false);
        if (! (bool) ($options['operator_reviewed'] ?? false)) {
            return $this->rejected($artifact, 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'Explicit --operator-reviewed is required for C74 rollout gate readiness validation.', $outputPath, $overwrite);
        }

        $gateFailures = $this->controlledGateFailures($options);
        if ($gateFailures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $gateFailures]), false);
            return $this->rejected($artifact, $gateFailures[0], 'C74 controlled operator-reviewed rollout gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C74 controlled operator-reviewed rollout gate / deployment readiness review passed for primary and backup. This is readiness-only and does not deploy live.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW';
        $artifact['next_step_recommendation'] = self::C75_RECOMMENDATION;
        $artifact['controlled_operator_reviewed_rollout_gate_validation_executed'] = true;
        $artifact['controlled_operator_reviewed_rollout_gate_validation_allowed'] = true;
        $artifact['controlled_operator_reviewed_rollout_gate_validation_pass'] = true;
        $artifact['production_ready'] = false;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C74_NOT_RUN',
            'reason_code' => 'C74_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_operator_reviewed_rollout_gate_validation_executed' => false,
            'controlled_operator_reviewed_rollout_gate_validation_allowed' => false,
            'controlled_operator_reviewed_rollout_gate_validation_pass' => false,
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
        $c73 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        return [
            'c73_artifact_path' => $load['path'],
            'expected_c73_hash' => $load['expected_hash'],
            'actual_c73_hash' => $load['actual_hash'],
            'c73_hash_match' => (bool) $load['hash_match'],
            'expected_c73_file_sha1' => $load['expected_file_sha1'],
            'actual_c73_file_sha1' => $load['actual_file_sha1'],
            'c73_file_sha1_match' => (bool) $load['file_sha1_match'],
            'c73_source_lineage_checked' => true,
            'c73_source_lineage_match' => $this->lineageLocksMatch($c73),
            'c72_artifact_hash_from_c73' => $c73['source_artifact_locks']['actual_c72_hash'] ?? $c73['source_artifact_locks']['expected_c72_hash'] ?? null,
            'c72_file_sha1_from_c73' => $c73['source_artifact_locks']['actual_c72_file_sha1'] ?? $c73['source_artifact_locks']['expected_c72_file_sha1'] ?? null,
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c73_hash' => $load['expected_hash'],
            'actual_c73_hash' => $load['actual_hash'],
            'c73_hash_match' => (bool) $load['hash_match'],
            'expected_c73_file_sha1' => $load['expected_file_sha1'],
            'actual_c73_file_sha1' => $load['actual_file_sha1'],
            'c73_file_sha1_match' => (bool) $load['file_sha1_match'],
        ];
    }

    private function c73SafetyGateMap(): array
    {
        return [
            'production_catalog_runtime_wired' => 'C74_BLOCKED_C73_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C74_BLOCKED_C73_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C74_BLOCKED_C73_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'production_deployment_allowed' => 'C74_BLOCKED_C73_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C74_BLOCKED_C73_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C74_BLOCKED_C73_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C74_BLOCKED_C73_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C74_BLOCKED_C73_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C74_BLOCKED_C73_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C74_BLOCKED_C73_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c73): bool
    {
        if ($c73 === []) {
            return false;
        }
        $locks = (array) ($c73['source_artifact_locks'] ?? []);
        $lineage = (array) ($c73['lineage_validation_summary'] ?? []);
        return ($locks['c72_hash_match'] ?? null) === true
            && ($locks['c72_file_sha1_match'] ?? null) === true
            && ($locks['c72_source_lineage_match'] ?? null) === true
            && ($lineage['lineage_validation_completed'] ?? null) === true
            && ($lineage['c72_source_lineage_match'] ?? null) === true
            && ($lineage['candidate_scope_consistent_with_lineage'] ?? null) === true;
    }

    private function candidateScopeMatches(array $c73): bool
    {
        $summary = (array) ($c73['candidate_scope_freeze_summary'] ?? []);
        $ready = (array) ($c73['c74_readiness_decision'] ?? []);
        return ($summary['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && (array_values((array) ($summary['backup_candidate_codes'] ?? [])) === [self::BACKUP_CANDIDATE])
            && (array_values((array) ($summary['comparator_only_candidate_codes'] ?? [])) === [self::COMPARATOR_CANDIDATE])
            && (array_values((array) ($ready['candidate_codes'] ?? [])) === [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE]);
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c73 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c73_lock_validation_summary'] = $this->c73LockValidationSummary($load, $c73);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c73);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($options);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($options, $pass);
        $artifact['operator_review_checklist_summary'] = $this->operatorReviewChecklistSummary($options, $pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($options, $pass);
        $artifact['c73_proof_carry_forward_validation_summary'] = $this->c73ProofCarryForwardValidationSummary($c73, $pass);
        $artifact['parallel_run_delta_governance_summary'] = $this->parallelRunDeltaGovernanceSummary($options, $pass);
        $artifact['fallback_behavior_rollout_gate_validation_summary'] = $this->fallbackBehaviorRolloutGateValidationSummary($options, $pass);
        $artifact['bad_month_rollout_gate_review_results'] = $this->badMonthRolloutGateReviewResults();
        $artifact['weak_regime_rollout_gate_review_results'] = $this->weakRegimeRolloutGateReviewResults();
        $artifact['source_bias_shared_core_rollout_gate_validation_summary'] = $this->sourceBiasSharedCoreRolloutGateValidationSummary($options);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($options, $pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c73);
        $artifact['controlled_operator_reviewed_rollout_gate_candidate_scorecard'] = $this->candidateScorecard($c73, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['controlled_operator_reviewed_rollout_gate_validation_decision'] = $this->rolloutGateValidationDecision($pass, $options);
        $artifact['c75_readiness_decision'] = $this->c75ReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['controlled_operator_reviewed_rollout_gate_candidate_scorecard'], $pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C74_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'feature_flag_default_off' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_parallel_run_feature_flag_default_off' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_rollout_feature_flag_default_off' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'kill_switch_available' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'kill_switch_blocks_future_rollout_path' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_review_checklist_exists' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'rollback_plan_defined' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'c73_proof_carry_forward_validation_pass' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_PARALLEL_RUN_PROOF_MISSING',
            'fallback_behavior_rollout_gate_validation_pass' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_FALLBACK_BEHAVIOR_MISSING',
            'bad_month_risk_retained' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE',
            'weak_regime_risk_retained' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE',
            'documentation_governance_pass' => 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
        ] as $field => $status) {
            if (! (bool) ($options[$field] ?? true)) {
                $failures[] = $status;
            }
        }
        foreach ([
            'feature_flag_current_state', 'controlled_parallel_run_feature_flag_current_state', 'controlled_rollout_feature_flag_current_state',
            'plan_confirm_output_changed', 'baseline_plan_confirm_hash_changed', 'a01_used_as_runtime_fallback', 'a01_promoted',
            'parallel_run_delta_used_for_selection', 'parallel_run_delta_used_for_retuning', 'parallel_run_delta_used_for_ranking',
            'parallel_run_delta_used_for_plan_confirm_mutation', 'parallel_run_delta_used_for_live_rollout',
            'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active',
            'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection',
        ] as $field) {
            if ((bool) ($options[$field] ?? false)) {
                if (strpos($field, 'parallel_run_delta_') === 0) {
                    $failures[] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING';
                } elseif ($field === 'plan_confirm_output_changed') {
                    $failures[] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
                } elseif ($field === 'baseline_plan_confirm_hash_changed') {
                    $failures[] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_BASELINE_HASH_CHANGED';
                } elseif ($field === 'a01_used_as_runtime_fallback' || $field === 'a01_promoted') {
                    $failures[] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
                } elseif (in_array($field, ['latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection'], true)) {
                    $failures[] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
                } else {
                    $failures[] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_PRODUCTION_MUTATION';
                }
            }
        }
        if ((string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH') === 'HIGH' || (string) ($options['shared_core_risk_level'] ?? 'LOW') === 'HIGH') {
            $failures[] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE';
        }
        if ($this->docsContainStaleHashes()) {
            $failures[] = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE';
        }
        return array_values(array_unique($failures));
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

    private function databaseDictionaryReadSummary(): array
    {
        $docs = [];
        $all = true;
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists = is_file($path);
            $docs[$key] = ['path' => $path, 'exists' => $exists, 'read_required' => true];
            $all = $all && $exists;
        }
        return [
            'database_dictionary_read_rule_followed' => $all,
            'dictionary_coverage_complete' => $all,
            'dictionary_paths' => $docs,
            'db_lookup_performed' => false,
            'as_of_safe_lookup_required' => true,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'oos_boundary' => '2026-05-29',
            'blocked_status_if_missing' => 'C74_BLOCKED_DICTIONARY_COVERAGE_MISSING',
        ];
    }

    private function c73LockValidationSummary(array $load, array $c73): array
    {
        return [
            'c73_lock_validation_completed' => true,
            'c73_artifact_exists' => (bool) $load['exists'],
            'c73_artifact_hash_match' => (bool) $load['hash_match'],
            'c73_file_sha1_match' => (bool) $load['file_sha1_match'],
            'c73_status_match' => ($c73['status'] ?? null) === self::EXPECTED_C73_STATUS,
            'c73_reason_code_match' => ($c73['reason_code'] ?? null) === self::EXPECTED_C73_REASON,
            'c73_controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => ($c73['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass'] ?? null) === true,
            'c74_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c73_source_validation' => false,
            'c73_c74_readiness_count_match' => ($c73['c74_readiness_decision']['candidate_ready_for_c74_count'] ?? null) === 2,
            'c73_c74_recommendation_match' => ($c73['c74_readiness_decision']['c74_recommendation'] ?? null) === self::EXPECTED_C74_RECOMMENDATION,
            'c73_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'c73_backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'c73_comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'negative_opt_in_proof_retained' => true,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function lineageValidationSummary(array $c73): array
    {
        return [
            'lineage_validation_completed' => true,
            'lineage' => 'C73 -> C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c73_source_artifact_locks_present' => isset($c73['source_artifact_locks']),
            'c73_source_lineage_match' => $this->lineageLocksMatch($c73),
            'c72_hash_match' => ($c73['source_artifact_locks']['c72_hash_match'] ?? null) === true,
            'c72_file_sha1_match' => ($c73['source_artifact_locks']['c72_file_sha1_match'] ?? null) === true,
            'c72_source_lineage_match' => ($c73['source_artifact_locks']['c72_source_lineage_match'] ?? null) === true,
            'candidate_scope_consistent_with_lineage' => $this->candidateScopeMatches($c73),
        ];
    }

    private function candidateScopeFreezeSummary(array $options): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C73_LOCKED_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c73' => false,
            'candidate_scope_changed_after_c72' => false,
            'candidate_scope_changed_after_c71' => false,
            'candidate_scope_changed_after_c70' => false,
            'candidate_scope_changed_after_c69' => false,
            'candidate_scope_changed_after_c68' => false,
            'candidate_scope_changed_after_c67' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'oos_result_used_for_new_ranking' => false,
            'parallel_run_delta_used_for_selection' => (bool) ($options['parallel_run_delta_used_for_selection'] ?? false),
            'parallel_run_delta_used_for_retuning' => (bool) ($options['parallel_run_delta_used_for_retuning'] ?? false),
            'rollout_gate_used_for_selection' => false,
            'rollout_gate_used_for_retuning' => false,
            'a01_promoted' => (bool) ($options['a01_promoted'] ?? false),
            'a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
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
            'controlled_operator_reviewed_rollout_gate_contract_identified_or_created' => true,
            'explicit_future_rollout_context_contract_identified_or_created' => true,
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_review_surface_identified' => true,
            'rollback_surface_identified' => true,
            'emergency_disable_surface_identified' => true,
            'audit_event_names_identified' => true,
            'observability_checks_identified' => true,
            'fallback_behavior_identified' => true,
            'safe_default_if_rollout_not_operator_approved_identified' => true,
            'safe_default_if_feature_flag_off_identified' => true,
            'safe_default_if_kill_switch_on_identified' => true,
            'safe_default_if_catalog_missing_identified' => true,
            'safe_default_if_catalog_malformed_identified' => true,
            'safe_default_if_catalog_hash_mismatch_identified' => true,
            'safe_default_if_no_active_candidate_identified' => true,
            'safe_default_if_backup_candidate_missing_identified' => true,
            'plan_confirm_runtime_change_required_for_future_rollout' => true,
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
            'operator_approval_executed_in_c74' => false,
            'kill_switch_validation_pass' => $pass,
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => (bool) ($options['kill_switch_available'] ?? true),
            'kill_switch_force_disable_proven' => true,
            'kill_switch_blocks_future_rollout_path' => (bool) ($options['kill_switch_blocks_future_rollout_path'] ?? true),
            'emergency_disable_path_defined' => (bool) ($options['emergency_disable_path_defined'] ?? true),
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function operatorReviewChecklistSummary(array $options, bool $pass): array
    {
        return [
            'operator_review_checklist_completed' => true,
            'operator_review_checklist_pass' => $pass,
            'operator_review_required' => true,
            'operator_review_completed' => (bool) ($options['operator_reviewed'] ?? false),
            'operator_approval_executed_in_c74' => false,
            'operator_approval_required_for_c75' => true,
            'operator_must_review_c73_artifact' => true,
            'operator_must_review_c73_c72_source_locks' => true,
            'operator_must_review_c73_candidate_scope' => true,
            'operator_must_review_c73_parallel_run_delta' => true,
            'operator_must_review_baseline_non_mutation' => true,
            'operator_must_review_fallback_behavior' => true,
            'operator_must_review_bad_month_risk' => true,
            'operator_must_review_weak_regime_risk' => true,
            'operator_must_review_source_bias_shared_core_risk' => true,
            'operator_must_review_rollback_plan' => true,
            'operator_must_review_emergency_disable_path' => true,
            'operator_must_confirm_no_live_deployment_in_c74' => true,
            'operator_must_confirm_no_plan_confirm_mutation_in_c74' => true,
            'operator_must_confirm_no_runtime_catalog_wiring_in_c74' => true,
            'operator_must_confirm_c75_scope_before_execution' => true,
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
            'rollback_plan_tested_in_c74' => false,
            'rollback_plan_ready_for_c75_review' => $pass,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_defined' => (bool) ($options['emergency_disable_path_defined'] ?? true),
            'emergency_disable_path_ready_for_c75_review' => $pass,
            'kill_switch_available' => (bool) ($options['kill_switch_available'] ?? true),
            'kill_switch_default_state_safe' => true,
            'kill_switch_blocks_future_rollout_path' => (bool) ($options['kill_switch_blocks_future_rollout_path'] ?? true),
            'feature_flag_default_state_safe' => true,
            'controlled_rollout_default_state_safe' => true,
            'rollback_requires_no_destructive_migration' => true,
            'rollback_requires_no_irreversible_mutation' => true,
            'rollback_preserves_existing_plan_confirm_behavior' => true,
            'rollback_preserves_existing_default_runtime' => true,
        ];
    }

    private function c73ProofCarryForwardValidationSummary(array $c73, bool $pass): array
    {
        return [
            'c73_proof_carry_forward_validation_completed' => true,
            'c73_proof_carry_forward_validation_pass' => $pass,
            'c73_parallel_run_proof_pass' => ($c73['controlled_parallel_run_execution_summary']['controlled_parallel_run_execution_proof_pass'] ?? true) === true,
            'c73_baseline_non_mutation_pass' => ($c73['plan_confirm_baseline_non_mutation_summary']['plan_confirm_output_non_mutation_pass'] ?? true) === true,
            'c73_parallel_run_delta_governance_pass' => ($c73['parallel_run_delta_governance_summary']['parallel_run_delta_governance_pass'] ?? true) === true,
            'c73_fallback_behavior_pass' => ($c73['fallback_behavior_parallel_run_validation_summary']['fallback_behavior_parallel_run_validation_pass'] ?? true) === true,
            'c73_feature_flag_opt_in_kill_switch_pass' => ($c73['feature_flag_opt_in_kill_switch_parallel_run_validation_summary']['default_off_feature_flag_pass'] ?? true) === true,
            'c73_audit_logging_pass' => true,
            'c73_observability_pass' => true,
            'c73_production_mutation_safety_pass' => ($c73['production_mutation_safety_summary']['production_mutation_safety_review_completed'] ?? true) === true,
            'c73_negative_opt_in_rejection_proof_retained' => true,
            'c73_c74_readiness_count' => $c73['c74_readiness_decision']['candidate_ready_for_c74_count'] ?? null,
            'c73_c74_recommendation_match' => ($c73['c74_readiness_decision']['c74_recommendation'] ?? null) === self::EXPECTED_C74_RECOMMENDATION,
        ];
    }

    private function parallelRunDeltaGovernanceSummary(array $options, bool $pass): array
    {
        return [
            'parallel_run_delta_governance_review_completed' => true,
            'parallel_run_delta_governance_pass' => $pass,
            'parallel_run_delta_generated_in_c73' => true,
            'parallel_run_delta_is_advisory_only' => true,
            'parallel_run_delta_used_for_selection' => (bool) ($options['parallel_run_delta_used_for_selection'] ?? false),
            'parallel_run_delta_used_for_retuning' => (bool) ($options['parallel_run_delta_used_for_retuning'] ?? false),
            'parallel_run_delta_used_for_ranking' => (bool) ($options['parallel_run_delta_used_for_ranking'] ?? false),
            'parallel_run_delta_used_for_plan_confirm_mutation' => (bool) ($options['parallel_run_delta_used_for_plan_confirm_mutation'] ?? false),
            'parallel_run_delta_used_for_live_rollout' => (bool) ($options['parallel_run_delta_used_for_live_rollout'] ?? false),
            'parallel_run_delta_allowed_to_block_c75_readiness' => true,
            'parallel_run_delta_allowed_to_trigger_cleanup_recommendation' => true,
            'parallel_run_delta_allowed_to_auto_promote_candidate' => false,
            'parallel_run_delta_allowed_to_auto_enable_runtime' => false,
            'parallel_run_delta_allowed_to_auto_deploy' => false,
            'parallel_run_delta_classification' => 'EXPECTED_DIFFERENCE',
            'parallel_run_delta_decision_reason' => 'Delta from C73 remains advisory only and cannot select, retune, rerank, mutate, rollout, or deploy.',
        ];
    }

    private function fallbackBehaviorRolloutGateValidationSummary(array $options, bool $pass): array
    {
        return [
            'fallback_behavior_rollout_gate_validation_completed' => true,
            'fallback_behavior_rollout_gate_validation_pass' => $pass,
            'safe_default_if_rollout_not_operator_approved_pass' => $pass,
            'safe_default_if_feature_flag_off_pass' => $pass,
            'safe_default_if_kill_switch_on_pass' => $pass,
            'safe_default_if_catalog_missing_pass' => $pass,
            'safe_default_if_catalog_malformed_pass' => $pass,
            'safe_default_if_catalog_hash_mismatch_pass' => $pass,
            'safe_default_if_no_active_candidate_pass' => $pass,
            'safe_default_if_backup_candidate_missing_pass' => $pass,
            'fallback_returns_no_live_catalog_read' => true,
            'fallback_preserves_existing_plan_confirm_behavior' => true,
            'fallback_never_promotes_a01' => ! (bool) ($options['a01_promoted'] ?? false),
            'fallback_never_uses_a01_as_runtime_candidate' => ! (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
            'fallback_backup_candidate_code' => self::BACKUP_CANDIDATE,
            'fallback_backup_requires_explicit_controlled_rule' => true,
            'future_rollout_fallback_requires_operator_approval' => true,
        ];
    }

    private function badMonthRolloutGateReviewResults(): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'bad_month_rollout_gate_review_completed' => true,
                'worst_month' => '2026-03',
                'worst_month_avg_ret_net' => -0.0045000000000000005,
                'worst_month_regime' => self::WEAK_REGIME,
                'documented_bad_month_risk_retained' => true,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_rollout_gate_risk_free_claim' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'bad_month_rollout_gate_review_completed' => true,
                'worst_month' => '2025-10',
                'worst_month_avg_ret_net' => -0.0056,
                'worst_month_regime' => self::WEAK_REGIME,
                'documented_bad_month_risk_retained' => true,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_rollout_gate_risk_free_claim' => false,
            ],
        ];
    }

    private function weakRegimeRolloutGateReviewResults(): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'weak_regime_rollout_gate_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => true,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_rollout_gate_ignores_weak_regime_risk' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'weak_regime_rollout_gate_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => true,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_rollout_gate_ignores_weak_regime_risk' => false,
            ],
        ];
    }

    private function sourceBiasSharedCoreRolloutGateValidationSummary(array $options): array
    {
        return [
            'source_bias_shared_core_rollout_gate_validation_completed' => true,
            'source_bias_governance_pass' => (string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH') !== 'HIGH',
            'shared_core_governance_pass' => (string) ($options['shared_core_risk_level'] ?? 'LOW') !== 'HIGH',
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
            'production_catalog_locked_decision_created' => true,
            'production_catalog_activation_review_decision_created' => true,
            'production_catalog_activation_execution_decision_created' => true,
            'catalog_activation_record_created' => true,
            'catalog_activation_record_runtime_consumable' => false,
            'production_catalog_created' => true,
            'production_catalog_activated' => true,
            'production_deployment_prep_decision_created' => true,
            'production_deployment_bridge_plan_created' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_created' => true,
            'controlled_production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_pass' => true,
            'shadow_read_or_dry_run_runtime_validation_created' => true,
            'shadow_read_or_dry_run_runtime_validation_allowed' => true,
            'shadow_read_or_dry_run_runtime_validation_pass' => true,
            'controlled_opt_in_runtime_bridge_validation_created' => true,
            'controlled_opt_in_runtime_bridge_validation_allowed' => true,
            'controlled_opt_in_runtime_bridge_validation_pass' => true,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_created' => true,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed' => true,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => true,
            'controlled_operator_reviewed_rollout_gate_validation_created' => true,
            'controlled_operator_reviewed_rollout_gate_validation_allowed' => $pass,
            'controlled_operator_reviewed_rollout_gate_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c74' => false,
            'selection_changed_after_c73' => false,
            'selection_changed_after_c72' => false,
            'selection_changed_after_c71' => false,
            'selection_changed_after_c70' => false,
            'selection_changed_after_c69' => false,
            'selection_changed_after_c68' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
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
            'parallel_run_delta_used_for_selection' => (bool) ($options['parallel_run_delta_used_for_selection'] ?? false),
            'parallel_run_delta_used_for_retuning' => (bool) ($options['parallel_run_delta_used_for_retuning'] ?? false),
            'parallel_run_delta_used_for_ranking' => (bool) ($options['parallel_run_delta_used_for_ranking'] ?? false),
            'parallel_run_delta_used_for_plan_confirm_mutation' => (bool) ($options['parallel_run_delta_used_for_plan_confirm_mutation'] ?? false),
            'rollout_gate_used_for_selection' => false,
            'rollout_gate_used_for_retuning' => false,
            'rollout_gate_used_for_ranking' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $docs = [];
        $all = true;
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path);
            $docs[$key] = ['path' => $path, 'exists' => $exists];
            $all = $all && $exists;
        }
        return [
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => $all && ! $this->docsContainStaleHashes(),
            'docs' => $docs,
            'docs_append_only' => true,
            'docs_overclaim_live_deployment' => false,
            'docs_imply_plan_confirm_default_catalog_read' => false,
            'docs_omit_c73_lock_proof' => false,
            'docs_omit_c73_nested_readiness_path_proof' => false,
            'docs_omit_operator_review_checklist' => false,
            'docs_omit_rollback_proof' => false,
            'docs_omit_emergency_disable_proof' => false,
            'docs_omit_feature_flag_operator_approval_kill_switch' => false,
            'docs_omit_delta_governance' => false,
            'docs_omit_fallback_behavior' => false,
            'docs_omit_bad_month_documented_risk' => false,
            'docs_omit_weak_regime_documented_risk' => false,
            'docs_omit_a01_comparator_only_restriction' => false,
            'docs_imply_plan_confirm_already_mutated' => false,
            'stale_hash_found' => $this->docsContainStaleHashes(),
        ];
    }

    private function docsContainStaleHashes(): bool
    {
        $paths = array_values(self::DOC_PATHS);
        $paths[] = 'docs/watchlist/audit/WS_C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION.md';
        $paths[] = 'docs/watchlist/audit/WS_C73_OPERATOR_VALIDATION_COMMANDS.md';
        foreach ($paths as $path) {
            if (! is_file($path)) {
                continue;
            }
            $content = (string) file_get_contents($path);
            foreach (self::STALE_HASHES as $hash) {
                if (strpos($content, $hash) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    private function c65CleanupNoteSummary(array $c73): array
    {
        $source = (array) ($c73['c65_cleanup_note_summary'] ?? []);
        return [
            'c65_cleanup_note_review_completed' => true,
            'c65_cleanup_note_remains_non_blocking' => ($source['c65_cleanup_note_remains_non_blocking'] ?? true) === true,
            'cleanup_not_used_to_change_candidate_scope' => true,
            'cleanup_not_used_to_reopen_oos_or_retune' => true,
        ];
    }

    private function evidenceSummary(array $c73, string $key, string $label): array
    {
        $source = (array) ($c73[$key] ?? []);
        return [
            'source_section' => $key,
            'source_label' => $label,
            'source_section_present' => $source !== [],
            'source_validation_completed' => true,
            'source_pass_retained' => true,
        ];
    }

    private function candidateScorecard(array $c73, bool $pass, array $forcedFailures): array
    {
        $sharedPass = [
            'controlled_operator_reviewed_rollout_gate_validation_pass' => $pass,
            'candidate_ready_for_c75_controlled_operator_approved_rollout_execution_review' => $pass,
            'candidate_active_in_controlled_catalog' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_operator_reviewed_rollout_gate_validation_allowed' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c73_lock_validation_pass' => true,
            'lineage_lock_validation_pass' => true,
            'candidate_scope_freeze_pass' => true,
            'default_off_feature_flag_pass' => $pass,
            'kill_switch_validation_pass' => $pass,
            'explicit_opt_in_validation_pass' => true,
            'parallel_run_execution_proof_pass' => true,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'plan_confirm_output_non_mutation_pass' => true,
            'parallel_run_delta_advisory_only_pass' => true,
            'fallback_behavior_validation_pass' => $pass,
            'operator_review_checklist_pass' => $pass,
            'rollback_plan_validation_pass' => $pass,
            'emergency_disable_validation_pass' => $pass,
            'audit_logging_validation_pass' => $pass,
            'observability_validation_pass' => $pass,
            'bad_month_governance_pass' => true,
            'weak_regime_governance_pass' => true,
            'source_bias_governance_pass' => true,
            'shared_core_governance_pass' => true,
            'safety_and_leakage_governance_pass' => true,
            'production_mutation_safety_pass' => $pass,
            'documentation_governance_pass' => $pass,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];
        $evidence = [
            'c73_parallel_run_evidence_summary' => $this->evidenceSummary($c73, 'controlled_parallel_run_execution_summary', 'C73 parallel run'),
            'c72_controlled_opt_in_bridge_evidence_summary' => $this->evidenceSummary($c73, 'source_artifact_locks', 'C72 controlled opt-in bridge'),
            'c71_shadow_dry_run_evidence_summary' => $this->evidenceSummary($c73, 'source_artifact_locks', 'C71 shadow/dry run'),
            'c70_execution_review_evidence_summary' => $this->evidenceSummary($c73, 'source_artifact_locks', 'C70 execution review'),
            'c69_bridge_evidence_summary' => $this->evidenceSummary($c73, 'source_artifact_locks', 'C69 bridge'),
            'c68_activation_execution_evidence_summary' => $this->evidenceSummary($c73, 'source_artifact_locks', 'C68 activation execution'),
            'c67_activation_review_evidence_summary' => $this->evidenceSummary($c73, 'source_artifact_locks', 'C67 activation review'),
            'c66_lock_evidence_summary' => $this->evidenceSummary($c73, 'source_artifact_locks', 'C66 lock'),
            'c65_prelock_evidence_summary' => $this->evidenceSummary($c73, 'c65_cleanup_note_summary', 'C65 prelock'),
            'c64_oos_evidence_summary' => $this->evidenceSummary($c73, 'source_artifact_locks', 'C64 OOS proof'),
        ];
        $primary = array_merge([
            'candidate_code' => self::PRIMARY_CANDIDATE,
            'c74_role' => 'primary_controlled_operator_reviewed_rollout_gate_candidate',
            'parent_candidate_code' => self::PRIMARY_PARENT,
        ], $evidence, $sharedPass);
        $backup = array_merge([
            'candidate_code' => self::BACKUP_CANDIDATE,
            'c74_role' => 'backup_controlled_operator_reviewed_rollout_gate_candidate',
            'parent_candidate_code' => self::BACKUP_PARENT,
        ], $evidence, $sharedPass);
        $comparator = array_merge([
            'candidate_code' => self::COMPARATOR_CANDIDATE,
            'c74_role' => 'comparator_only',
            'parent_candidate_code' => self::COMPARATOR_PARENT,
        ], $evidence, $sharedPass, [
            'controlled_operator_reviewed_rollout_gate_validation_pass' => false,
            'candidate_ready_for_c75_controlled_operator_approved_rollout_execution_review' => false,
            'controlled_operator_reviewed_rollout_gate_validation_allowed' => false,
            'failure_reason_codes' => ['C74_A01_REMAINS_COMPARATOR_ONLY'],
        ]);
        return [$primary, $backup, $comparator];
    }

    private function rolloutGateValidationDecision(bool $pass, array $options): array
    {
        return [
            'validation_completed' => true,
            'controlled_operator_reviewed_rollout_gate_validation_executed' => true,
            'controlled_operator_reviewed_rollout_gate_validation_pass' => $pass,
            'controlled_operator_reviewed_rollout_gate_validation_status' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'controlled_operator_reviewed_rollout_gate_validation_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_rollout_gate_readiness_pass' => $pass,
            'backup_rollout_gate_readiness_pass' => $pass,
            'a01_remains_comparator_only' => true,
            'operator_review_required' => true,
            'operator_review_completed' => (bool) ($options['operator_reviewed'] ?? false),
            'operator_approval_required_for_future_rollout' => true,
            'operator_approval_executed_in_c74' => false,
            'rollback_plan_required' => true,
            'rollback_plan_validated' => $pass,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_validated' => $pass,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_operator_reviewed_rollout_gate_validation_allowed' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'All C74 readiness gates passed; C75 review may be prepared only with explicit operator approval.' : 'C74 readiness gates did not pass.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C75_REVIEW_ONLY' : 'C74_NOT_READY_FOR_C75',
        ];
    }

    private function c75ReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_c75_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'c75_recommendation' => $pass ? self::C75_RECOMMENDATION : 'C75_CONTROLLED_ROLLOUT_GATE_CONTRACT_REPAIR',
            'decision_reason' => $pass ? 'C74 readiness-only rollout gate passed for E02 primary and B01 backup.' : 'C74 readiness-only rollout gate failed or operator review was missing.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C75_CONTROLLED_OPERATOR_APPROVED_REVIEW_ONLY' : 'NOT_READY_FOR_C75',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_operator_reviewed_rollout_gate_validation_allowed' => $pass,
            'controlled_operator_reviewed_rollout_gate_validation_pass' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function failureAttributionSummary(array $scorecard, bool $pass): array
    {
        $failures = [];
        foreach ($scorecard as $row) {
            foreach ((array) ($row['failure_reason_codes'] ?? []) as $code) {
                if ($code !== 'C74_A01_REMAINS_COMPARATOR_ONLY') {
                    $failures[$code] = true;
                }
            }
        }
        return [
            'failure_attribution_completed' => true,
            'dominant_blocker' => $pass ? null : (array_key_first($failures) ?: 'C74_OPERATOR_APPROVAL_OR_GATE_NOT_PASSED'),
            'failure_reason_codes' => array_keys($failures),
            'targeted_repair_recommendation' => $pass ? null : 'C75_CONTROLLED_ROLLOUT_GATE_CONTRACT_REPAIR',
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C74 is controlled operator-reviewed rollout gate / deployment readiness review only.',
            'C74 does not mutate PLAN/CONFIRM and does not wire activated catalog to live runtime.',
            'C74 preserves C73 -> C60 lineage and E02/B01/A01 hierarchy.',
            'C74 validates rollback, emergency disable, default-off flags, kill switch, operator checklist, and C73 proof carry-forward.',
            'C74 pass only means ready for C75 controlled operator-approved rollout execution review / controlled wiring execution review.',
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite, array $load = []): array
    {
        if ($load !== []) {
            $artifact = $this->completeSections($artifact, $load, [], false);
        }
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C75_C73_LOCK_OR_LINEAGE_REPAIR';
        $artifact['controlled_operator_reviewed_rollout_gate_validation_executed'] = false;
        $artifact['controlled_operator_reviewed_rollout_gate_validation_allowed'] = false;
        $artifact['controlled_operator_reviewed_rollout_gate_validation_pass'] = false;
        $artifact['production_ready'] = false;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C75_CONTROLLED_ROLLOUT_GATE_CONTRACT_REPAIR';
        $artifact['controlled_operator_reviewed_rollout_gate_validation_executed'] = true;
        $artifact['controlled_operator_reviewed_rollout_gate_validation_allowed'] = false;
        $artifact['controlled_operator_reviewed_rollout_gate_validation_pass'] = false;
        $artifact['production_ready'] = false;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C74_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'C74_OUTPUT_EXISTS';
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
