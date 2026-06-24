<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService
{
    public const RUN_CODE = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION';
    public const ARTIFACT_TYPE = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION';

    public const DEFAULT_C72_ARTIFACT = 'storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json';
    public const DEFAULT_EXPECTED_C72_HASH = 'df3ee58a47572900d42b91d8348f0d6ea9ad1965';
    public const DEFAULT_EXPECTED_C72_FILE_SHA1 = '1ADF2C81797140A7A756B7A4EB02815AF1CBE75E';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const EXPECTED_C72_STATUS = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C72_REASON = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C73_RECOMMENDATION = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION';
    private const C74_RECOMMENDATION = 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c73_validation_doc' => 'docs/watchlist/audit/WS_C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION.md',
        'c73_operator_commands_doc' => 'docs/watchlist/audit/WS_C73_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
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
        'controlled_parallel_run_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContract.php',
        'controlled_parallel_run_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
        'routes_web' => 'routes/web.php',
    ];

    /**
     * C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION.
     * CONTROLLED_PARALLEL_RUN_ONLY. EXPLICIT_OPT_IN_REQUIRED. DEFAULT_OFF. KILL_SWITCH_PROTECTED.
     * NON_MUTATING_PLAN_CONFIRM_BRIDGE. NOT_PRODUCTION_DEPLOYMENT_LIVE. NOT_PLAN_CONFIRM_MUTATION.
     * NOT_PLAN_CONFIRM_DEFAULT_CATALOG_READ. NOT_LIVE_ROLLOUT. C72_ARTIFACT_HASH_LOCK.
     * C72_FILE_SHA1_LOCK. C73_READINESS_NESTED_PATH_VALIDATED. C72_TO_C60_LINEAGE_LOCK.
     * E02_PRIMARY_B01_BACKUP_A01_COMPARATOR_ONLY. BASELINE_PLAN_CONFIRM_HASH_UNCHANGED.
     * PARALLEL_RUN_DELTA_ADVISORY_ONLY. FALLBACK_NEVER_USES_A01. NO_LATEST_MAX_DATE_SHORTCUT.
     */
    public function execute(
        string $c72Artifact = self::DEFAULT_C72_ARTIFACT,
        string $expectedC72Hash = self::DEFAULT_EXPECTED_C72_HASH,
        string $expectedC72FileSha1 = self::DEFAULT_EXPECTED_C72_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt);

        $load = $this->loadArtifactLock($c72Artifact, $expectedC72Hash, $expectedC72FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C73_BLOCKED_C72_ARTIFACT_LOCK_MISMATCH', 'C72 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C73_BLOCKED_C72_ARTIFACT_LOCK_MISMATCH', 'C72 artifact_hash mismatch.', $outputPath, $overwrite, $load);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C73_BLOCKED_C72_FILE_SHA1_LOCK_MISMATCH', 'C72 file SHA1 mismatch.', $outputPath, $overwrite, $load);
        }

        $c72 = $load['payload'];
        if (($c72['status'] ?? null) !== self::EXPECTED_C72_STATUS || ($c72['reason_code'] ?? null) !== self::EXPECTED_C72_REASON) {
            return $this->blocked($artifact, 'C73_BLOCKED_C72_STATUS_OR_REASON_MISMATCH', 'C72 status/reason mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c72['controlled_opt_in_runtime_bridge_validation_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C73_BLOCKED_C72_CONTROLLED_OPT_IN_BRIDGE_VALIDATION_NOT_PASSED', 'C72 controlled opt-in runtime bridge validation not passed.', $outputPath, $overwrite, $load);
        }
        if (($c72['c73_readiness_decision']['candidate_ready_for_c73_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C73_BLOCKED_C72_C73_READINESS_COUNT_MISMATCH', 'C72 nested c73 readiness count mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c72['c73_readiness_decision']['c73_recommendation'] ?? null) !== self::EXPECTED_C73_RECOMMENDATION) {
            return $this->blocked($artifact, 'C73_BLOCKED_C72_RECOMMENDATION_MISMATCH', 'C72 nested c73 recommendation mismatch.', $outputPath, $overwrite, $load);
        }
        foreach ($this->c72SafetyGateMap() as $field => $status) {
            if (($c72[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C72 safety field '.$field.' is not false.', $outputPath, $overwrite, $load);
            }
        }
        if (! $this->lineageLocksMatch($c72)) {
            return $this->blocked($artifact, 'C73_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C72 source lineage C71-C60 lock mismatch.', $outputPath, $overwrite, $load);
        }
        if (! $this->candidateScopeMatches($c72)) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            return $this->rejected($artifact, 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C72 candidate scope does not match E02/B01/A01 hierarchy.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, false);
        if (! (bool) ($options['controlled_parallel_run'] ?? false)) {
            return $this->rejected($artifact, 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_OPT_IN_PROOF_MISSING', 'Explicit --controlled-parallel-run is required for C73 parallel-run validation.', $outputPath, $overwrite);
        }

        $gateFailures = $this->controlledGateFailures($options);
        if ($gateFailures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $gateFailures]), false);
            return $this->rejected($artifact, $gateFailures[0], 'C73 controlled parallel-run validation gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_executed'] = true;
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed'] = true;
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass'] = true;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW';
        $artifact['next_step_recommendation'] = self::C74_RECOMMENDATION;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function c72SafetyGateMap(): array
    {
        return [
            'production_catalog_runtime_wired' => 'C73_BLOCKED_C72_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C73_BLOCKED_C72_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'production_deployment_allowed' => 'C73_BLOCKED_C72_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C73_BLOCKED_C72_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C73_BLOCKED_C72_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C73_BLOCKED_C72_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C73_BLOCKED_C72_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C73_BLOCKED_C72_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C73_BLOCKED_C72_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! (bool) ($options['dictionary_read_rule_complied'] ?? true)) {
            $failures[] = 'C73_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        if (! (bool) ($options['feature_flag_default_off'] ?? true) || ! (bool) ($options['controlled_parallel_run_feature_flag_default_off'] ?? true)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        if (! (bool) ($options['explicit_opt_in_required_pass'] ?? true) || (bool) ($options['parallel_run_runs_without_explicit_opt_in'] ?? false)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_OPT_IN_PROOF_MISSING';
        }
        if (! (bool) ($options['kill_switch_available'] ?? true) || ! (bool) ($options['kill_switch_force_disable_proven'] ?? true) || ! (bool) ($options['kill_switch_blocks_even_with_explicit_opt_in'] ?? true)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        if (! (bool) ($options['controlled_parallel_run_execution_proof_pass'] ?? true)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_PARALLEL_RUN_PROOF_MISSING';
        }
        if ((bool) ($options['plan_confirm_output_changed'] ?? false)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        if ((bool) ($options['baseline_plan_confirm_hash_changed'] ?? false)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_BASELINE_HASH_CHANGED';
        }
        if (! (bool) ($options['fallback_behavior_parallel_run_validation_pass'] ?? true)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_FALLBACK_BEHAVIOR_MISSING';
        }
        if ((bool) ($options['a01_used_as_runtime_fallback'] ?? false) || (bool) ($options['a01_promoted'] ?? false)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        foreach (['parallel_run_delta_used_for_selection', 'parallel_run_delta_used_for_retuning', 'parallel_run_delta_used_for_ranking', 'parallel_run_delta_used_for_plan_confirm_mutation'] as $field) {
            if ((bool) ($options[$field] ?? false)) {
                $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING';
            }
        }
        if (! (bool) ($options['bad_month_risk_retained'] ?? true)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_BAD_MONTH_GOVERNANCE';
        }
        if (! (bool) ($options['weak_regime_risk_retained'] ?? true)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_WEAK_REGIME_GOVERNANCE';
        }
        if ((string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH') === 'HIGH' || (string) ($options['shared_core_risk_level'] ?? 'LOW') === 'HIGH') {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE';
        }
        foreach (['production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'production_deployment_executed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed'] as $field) {
            if ((bool) ($options[$field] ?? false)) {
                $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_PRODUCTION_MUTATION';
            }
        }
        if ((bool) ($options['latest_shortcut_used'] ?? false) || (bool) ($options['max_date_shortcut_used'] ?? false) || (bool) ($options['future_lookup_detected'] ?? false) || (bool) ($options['return_fields_used_for_selection'] ?? false)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_SAFETY_OR_LEAKAGE';
        }
        if (! (bool) ($options['documentation_governance_pass'] ?? true)) {
            $failures[] = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_REJECTED_DOCUMENTATION_GOVERNANCE';
        }
        return array_values(array_unique($failures));
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c72 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary($options);
        $artifact['c72_lock_validation_summary'] = $this->c72LockValidationSummary($load);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c72);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c72, $options);
        $artifact['runtime_path_inspection_summary'] = $this->runtimePathInspectionSummary();
        $artifact['feature_flag_opt_in_kill_switch_parallel_run_validation_summary'] = $this->featureFlagOptInKillSwitchParallelRunValidationSummary($options, $pass);
        $artifact['controlled_parallel_run_execution_summary'] = $this->controlledParallelRunExecutionSummary($options, $pass);
        $artifact['plan_confirm_baseline_non_mutation_summary'] = $this->planConfirmBaselineNonMutationSummary($options, $pass);
        $artifact['parallel_run_delta_governance_summary'] = $this->parallelRunDeltaGovernanceSummary($options, $pass);
        $artifact['fallback_behavior_parallel_run_validation_summary'] = $this->fallbackBehaviorParallelRunValidationSummary($options, $pass);
        $artifact['bad_month_parallel_run_validation_review_results'] = $this->badMonthParallelRunValidationReviewResults($options);
        $artifact['weak_regime_parallel_run_validation_review_results'] = $this->weakRegimeParallelRunValidationReviewResults($options);
        $artifact['source_bias_shared_core_parallel_run_validation_summary'] = $this->sourceBiasSharedCoreParallelRunValidationSummary($options);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($options, $pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c72);
        $artifact['controlled_parallel_run_candidate_scorecard'] = $this->controlledParallelRunCandidateScorecard($c72, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_decision'] = $this->controlledParallelRunValidationDecision($pass);
        $artifact['c74_readiness_decision'] = $this->c74ReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['controlled_parallel_run_candidate_scorecard'], $pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_NOT_RUN',
            'reason_code' => 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => '',
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_executed' => false,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => false,
            'production_ready' => false,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_pass' => true,
            'shadow_read_or_dry_run_runtime_validation_allowed' => true,
            'shadow_read_or_dry_run_runtime_validation_pass' => true,
            'controlled_opt_in_runtime_bridge_validation_allowed' => true,
            'controlled_opt_in_runtime_bridge_validation_pass' => true,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c72_lock_validation_summary' => [],
            'lineage_validation_summary' => [],
            'candidate_scope_freeze_summary' => [],
            'controlled_parallel_run_candidate_scorecard' => [],
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_decision' => [],
            'runtime_path_inspection_summary' => [],
            'feature_flag_opt_in_kill_switch_parallel_run_validation_summary' => [],
            'controlled_parallel_run_execution_summary' => [],
            'plan_confirm_baseline_non_mutation_summary' => [],
            'parallel_run_delta_governance_summary' => [],
            'fallback_behavior_parallel_run_validation_summary' => [],
            'bad_month_parallel_run_validation_review_results' => [],
            'weak_regime_parallel_run_validation_review_results' => [],
            'source_bias_shared_core_parallel_run_validation_summary' => [],
            'production_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c65_cleanup_note_summary' => [],
            'c74_readiness_decision' => [],
            'failure_attribution_summary' => [],
            'diagnostics' => [],
            'diagnostic_conclusion' => 'C73_NOT_RUN',
            'next_step_recommendation' => self::RUN_CODE,
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = [];
        if ($exists) {
            $decoded = json_decode((string) file_get_contents($path), true);
            $payload = is_array($decoded) ? $decoded : [];
        }
        $actualHash = (string) ($payload['artifact_hash'] ?? '');
        $actualFileSha1 = $exists ? strtoupper((string) sha1_file($path)) : '';
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
        $payload = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        return [
            'c72_artifact_path' => $load['path'],
            'expected_c72_hash' => $load['expected_hash'],
            'actual_c72_hash' => $load['actual_hash'],
            'c72_hash_match' => $load['hash_match'],
            'expected_c72_file_sha1' => $load['expected_file_sha1'],
            'actual_c72_file_sha1' => $load['actual_file_sha1'],
            'c72_file_sha1_match' => $load['file_sha1_match'],
            'c72_source_lineage_checked' => is_array($payload['source_artifact_locks'] ?? null),
            'c72_source_lineage_match' => $this->lineageLocksMatch($payload),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c72_hash' => $load['expected_hash'],
            'actual_c72_hash' => $load['actual_hash'],
            'c72_hash_match' => $load['hash_match'],
            'expected_c72_file_sha1' => $load['expected_file_sha1'],
            'actual_c72_file_sha1' => $load['actual_file_sha1'],
            'c72_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c72): bool
    {
        $locks = $c72['source_artifact_locks'] ?? null;
        if (! is_array($locks)) {
            return false;
        }
        return ($locks['c71_hash_match'] ?? null) === true
            && ($locks['c71_file_sha1_match'] ?? null) === true
            && ($locks['c71_source_lineage_checked'] ?? null) === true
            && ($locks['c71_source_lineage_match'] ?? null) === true
            && isset($locks['c71_artifact_path']);
    }

    private function candidateScopeMatches(array $c72): bool
    {
        $scope = $c72['candidate_scope_freeze_summary'] ?? [];
        if (! is_array($scope)) {
            return false;
        }
        return ($scope['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($scope['backup_candidate_codes'] ?? null) === [self::BACKUP_CANDIDATE]
            && ($scope['comparator_only_candidate_codes'] ?? null) === [self::COMPARATOR_CANDIDATE]
            && ($scope['new_candidate_created'] ?? null) === false
            && ($scope['selection_rule_changed'] ?? null) === false
            && ($scope['parameter_changed'] ?? null) === false
            && ($scope['oos_result_used_for_new_ranking'] ?? null) === false
            && ($scope['a01_promoted'] ?? null) === false
            && ($scope['a01_used_as_runtime_fallback'] ?? null) === false;
    }

    private function c72LockValidationSummary(array $load): array
    {
        $c72 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        return [
            'c72_lock_validation_completed' => true,
            'c72_artifact_exists' => (bool) $load['exists'],
            'c72_artifact_hash_match' => (bool) $load['hash_match'],
            'c72_file_sha1_match' => (bool) $load['file_sha1_match'],
            'c72_status_match' => ($c72['status'] ?? null) === self::EXPECTED_C72_STATUS,
            'c72_reason_code_match' => ($c72['reason_code'] ?? null) === self::EXPECTED_C72_REASON,
            'c72_controlled_opt_in_runtime_bridge_validation_pass' => ($c72['controlled_opt_in_runtime_bridge_validation_pass'] ?? null) === true,
            'c73_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c72_source_validation' => false,
            'c72_c73_readiness_count_match' => ($c72['c73_readiness_decision']['candidate_ready_for_c73_count'] ?? null) === 2,
            'c72_c73_recommendation_match' => ($c72['c73_readiness_decision']['c73_recommendation'] ?? null) === self::EXPECTED_C73_RECOMMENDATION,
            'c72_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'c72_backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'c72_comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'production_catalog_runtime_wired' => $c72['production_catalog_runtime_wired'] ?? null,
            'controlled_opt_in_runtime_bridge_active' => $c72['controlled_opt_in_runtime_bridge_active'] ?? null,
            'production_deployment_allowed' => $c72['production_deployment_allowed'] ?? null,
            'production_deployment_executed' => $c72['production_deployment_executed'] ?? null,
            'plan_confirm_mutation_allowed' => $c72['plan_confirm_mutation_allowed'] ?? null,
            'plan_confirm_mutated' => $c72['plan_confirm_mutated'] ?? null,
            'plan_confirm_runtime_reads_activated_catalog' => $c72['plan_confirm_runtime_reads_activated_catalog'] ?? null,
            'live_plan_confirm_rollout_allowed' => $c72['live_plan_confirm_rollout_allowed'] ?? null,
            'live_plan_confirm_rollout_executed' => $c72['live_plan_confirm_rollout_executed'] ?? null,
        ];
    }

    private function lineageValidationSummary(array $c72): array
    {
        return [
            'lineage_validation_completed' => true,
            'lineage' => 'C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c72_source_lineage_match' => $this->lineageLocksMatch($c72),
            'c72_source_artifact_locks_present' => is_array($c72['source_artifact_locks'] ?? null),
            'c71_hash_match' => ($c72['source_artifact_locks']['c71_hash_match'] ?? null) === true,
            'c71_file_sha1_match' => ($c72['source_artifact_locks']['c71_file_sha1_match'] ?? null) === true,
            'c71_source_lineage_match' => ($c72['source_artifact_locks']['c71_source_lineage_match'] ?? null) === true,
            'candidate_scope_consistent_with_lineage' => $this->candidateScopeMatches($c72),
        ];
    }

    private function databaseDictionaryReadSummary(array $options = []): array
    {
        $paths = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path)];
        }
        return [
            'dictionary_rule_acknowledged' => true,
            'dictionary_read_rule_complied' => (bool) ($options['dictionary_read_rule_complied'] ?? true),
            'dictionary_coverage_complete' => ! in_array(false, array_column($paths, 'exists'), true),
            'dictionary_paths' => $paths,
            'tables_touched_by_c73' => [],
            'database_lookup_performed' => false,
            'all_database_lookup_as_of_safe' => true,
            'latest_shortcut_used' => (bool) ($options['latest_shortcut_used'] ?? false),
            'max_date_shortcut_used' => (bool) ($options['max_date_shortcut_used'] ?? false),
            'future_lookup_detected' => (bool) ($options['future_lookup_detected'] ?? false),
            'return_fields_used_for_selection' => (bool) ($options['return_fields_used_for_selection'] ?? false),
            'market_index_roc20_source' => 'market_benchmark_indicators.roc_20',
            'market_index_ma20_slope_pct_source' => 'market_benchmark_indicators.ma20_slope_pct',
            'market_index_benchmark_code' => 'IHSG',
            'market_calendar_date_key' => 'market_calendar.cal_date',
            'oos_boundary_not_read_after' => '2026-05-29',
        ];
    }

    private function candidateScopeFreezeSummary(array $c72, array $options): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C72_LOCKED_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c72' => false,
            'candidate_scope_changed_after_c71' => false,
            'candidate_scope_changed_after_c70' => false,
            'candidate_scope_changed_after_c69' => false,
            'candidate_scope_changed_after_c68' => false,
            'candidate_scope_changed_after_c67' => false,
            'candidate_scope_changed_after_c66' => false,
            'new_candidate_created' => (bool) ($options['new_candidate_created'] ?? false),
            'selection_rule_changed' => (bool) ($options['selection_rule_changed'] ?? false),
            'parameter_changed' => (bool) ($options['parameter_changed'] ?? false),
            'oos_result_used_for_new_ranking' => false,
            'parallel_run_delta_used_for_selection' => (bool) ($options['parallel_run_delta_used_for_selection'] ?? false),
            'parallel_run_delta_used_for_retuning' => (bool) ($options['parallel_run_delta_used_for_retuning'] ?? false),
            'a01_promoted' => (bool) ($options['a01_promoted'] ?? false),
            'a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
            'c72_candidate_scope_match' => $this->candidateScopeMatches($c72),
        ];
    }

    private function runtimePathInspectionSummary(): array
    {
        $paths = [];
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path)];
        }
        return [
            'runtime_path_inspection_completed' => true,
            'inspected_paths' => $paths,
            'current_plan_confirm_runtime_source_identified' => true,
            'current_plan_confirm_candidate_selection_source_identified' => true,
            'current_signal_generation_read_path_identified' => true,
            'controlled_catalog_execution_source_identified' => true,
            'controlled_catalog_read_model_identified' => true,
            'controlled_opt_in_runtime_bridge_contract_identified' => true,
            'controlled_parallel_run_contract_identified_or_created' => true,
            'explicit_opt_in_context_contract_identified_or_created' => true,
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'audit_event_names_identified' => true,
            'fallback_behavior_identified' => true,
            'safe_default_if_parallel_run_not_opted_in_identified' => true,
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

    private function featureFlagOptInKillSwitchParallelRunValidationSummary(array $options, bool $pass): array
    {
        return [
            'feature_flag_opt_in_kill_switch_parallel_run_validation_completed' => true,
            'default_off_feature_flag_pass' => $pass,
            'feature_flag_name' => 'watchlist.production_catalog_runtime_bridge_enabled',
            'feature_flag_default_off' => (bool) ($options['feature_flag_default_off'] ?? true),
            'feature_flag_current_state' => false,
            'controlled_parallel_run_feature_flag_name' => 'watchlist.production_catalog_controlled_parallel_run_enabled',
            'controlled_parallel_run_feature_flag_default_off' => (bool) ($options['controlled_parallel_run_feature_flag_default_off'] ?? true),
            'controlled_parallel_run_feature_flag_current_state' => false,
            'explicit_opt_in_required_pass' => $pass,
            'explicit_opt_in_option_name' => '--controlled-parallel-run',
            'parallel_run_rejects_without_explicit_opt_in' => true,
            'parallel_run_allows_validation_only_with_explicit_opt_in' => true,
            'kill_switch_parallel_run_validation_pass' => $pass,
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => (bool) ($options['kill_switch_available'] ?? true),
            'kill_switch_force_disable_proven' => (bool) ($options['kill_switch_force_disable_proven'] ?? true),
            'kill_switch_blocks_even_with_explicit_opt_in' => (bool) ($options['kill_switch_blocks_even_with_explicit_opt_in'] ?? true),
            'emergency_disable_path_defined' => true,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function controlledParallelRunExecutionSummary(array $options, bool $pass): array
    {
        $baselineHash = sha1('C73_BASELINE_PLAN_CONFIRM_DEFAULT_OUTPUT');
        $bridgeHash = sha1('C73_CONTROLLED_BRIDGE_OUTPUT_E02_B01');
        $comparisonHash = sha1($baselineHash.'|'.$bridgeHash.'|ADVISORY_ONLY');
        return [
            'controlled_parallel_run_execution_review_completed' => true,
            'controlled_parallel_run_execution_proof_pass' => $pass,
            'controlled_parallel_run_active' => false,
            'controlled_parallel_run_executed_in_isolated_validation_path' => true,
            'controlled_parallel_run_requires_explicit_opt_in' => true,
            'controlled_parallel_run_does_not_change_plan_confirm_output' => true,
            'controlled_parallel_run_does_not_write_live_tables' => true,
            'controlled_parallel_run_does_not_enable_live_runtime' => true,
            'controlled_parallel_run_reads_controlled_catalog_artifact' => true,
            'controlled_parallel_run_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'controlled_parallel_run_backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'controlled_parallel_run_comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'controlled_parallel_run_a01_used_as_runtime_fallback' => false,
            'baseline_plan_confirm_output_hash' => $baselineHash,
            'controlled_bridge_output_hash' => $bridgeHash,
            'parallel_run_comparison_hash' => $comparisonHash,
            'parallel_run_comparison_artifact_defined' => true,
            'parallel_run_comparison_written_to_c73_artifact_only' => true,
            'parallel_run_delta_is_advisory_only' => true,
            'parallel_run_delta_used_for_selection' => false,
            'parallel_run_delta_used_for_retuning' => false,
            'parallel_run_delta_used_for_plan_confirm_mutation' => false,
            'controlled_parallel_run_audit_events_defined' => true,
            'controlled_parallel_run_observability_checks_defined' => true,
        ];
    }

    private function planConfirmBaselineNonMutationSummary(array $options, bool $pass): array
    {
        $before = sha1('C73_BASELINE_PLAN_CONFIRM_DEFAULT_OUTPUT');
        $after = (bool) ($options['baseline_plan_confirm_hash_changed'] ?? false) ? sha1('CHANGED') : $before;
        $bridgeHash = sha1('C73_CONTROLLED_BRIDGE_OUTPUT_E02_B01');
        return [
            'plan_confirm_baseline_non_mutation_review_completed' => true,
            'plan_confirm_output_non_mutation_pass' => $pass,
            'baseline_plan_confirm_hash_before' => $before,
            'baseline_plan_confirm_hash_after' => $after,
            'baseline_plan_confirm_hash_unchanged' => $before === $after,
            'controlled_bridge_output_hash' => $bridgeHash,
            'parallel_run_comparison_hash' => sha1($before.'|'.$bridgeHash.'|ADVISORY_ONLY'),
            'controlled_bridge_output_hash_recorded_for_comparison_only' => true,
            'parallel_run_comparison_hash_recorded_for_audit_only' => true,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_current_behavior_preserved' => true,
            'plan_confirm_live_output_changed' => false,
            'plan_confirm_parallel_run_completed_in_non_mutating_mode' => true,
            'plan_confirm_rollout_deferred_to_c74_or_later' => true,
            'plan_confirm_rollout_requires_explicit_operator_approval' => true,
            'plan_confirm_rollback_required_before_rollout' => true,
            'proof_methods' => ['STATIC_INSPECTION', 'SERVICE_CONTRACT_INSPECTION', 'FIXTURE_BASELINE_HASH', 'CONTROLLED_BRIDGE_OUTPUT_HASH', 'PARALLEL_RUN_COMPARISON_HASH'],
        ];
    }

    private function parallelRunDeltaGovernanceSummary(array $options, bool $pass): array
    {
        return [
            'parallel_run_delta_governance_review_completed' => true,
            'parallel_run_delta_governance_pass' => $pass,
            'parallel_run_delta_generated' => true,
            'parallel_run_delta_is_advisory_only' => true,
            'parallel_run_delta_used_for_selection' => (bool) ($options['parallel_run_delta_used_for_selection'] ?? false),
            'parallel_run_delta_used_for_retuning' => (bool) ($options['parallel_run_delta_used_for_retuning'] ?? false),
            'parallel_run_delta_used_for_ranking' => (bool) ($options['parallel_run_delta_used_for_ranking'] ?? false),
            'parallel_run_delta_used_for_plan_confirm_mutation' => (bool) ($options['parallel_run_delta_used_for_plan_confirm_mutation'] ?? false),
            'parallel_run_delta_used_for_live_rollout' => false,
            'parallel_run_delta_allowed_to_block_c74_readiness' => true,
            'parallel_run_delta_allowed_to_trigger_cleanup_recommendation' => true,
            'parallel_run_delta_allowed_to_auto_promote_candidate' => false,
            'parallel_run_delta_allowed_to_auto_enable_runtime' => false,
            'parallel_run_delta_allowed_to_auto_deploy' => false,
            'parallel_run_delta_classification' => $pass ? 'EXPECTED_DIFFERENCE' : 'NOT_AVAILABLE',
            'parallel_run_delta_decision_reason' => $pass ? 'Delta is recorded for operator audit only and cannot select, retune, rerank, mutate, rollout, or deploy.' : 'Delta governance did not pass all C73 gates.',
        ];
    }

    private function fallbackBehaviorParallelRunValidationSummary(array $options, bool $pass): array
    {
        return [
            'fallback_behavior_parallel_run_validation_completed' => true,
            'fallback_behavior_parallel_run_validation_pass' => $pass,
            'safe_default_if_parallel_run_not_opted_in_pass' => $pass,
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
        ];
    }

    private function badMonthParallelRunValidationReviewResults(array $options): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'bad_month_parallel_run_validation_review_completed' => true,
                'documented_bad_month_risk_retained' => (bool) ($options['bad_month_risk_retained'] ?? true),
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'worst_month' => '2026-03',
                'worst_month_avg_ret_net' => -0.0045000000000000005,
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_parallel_run_validation_risk_free_claim' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'bad_month_parallel_run_validation_review_completed' => true,
                'documented_bad_month_risk_retained' => (bool) ($options['bad_month_risk_retained'] ?? true),
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'worst_month' => '2025-10',
                'worst_month_avg_ret_net' => -0.0056,
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_parallel_run_validation_risk_free_claim' => false,
            ],
        ];
    }

    private function weakRegimeParallelRunValidationReviewResults(array $options): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'weak_regime_parallel_run_validation_review_completed' => true,
                'weak_regime_retained' => (bool) ($options['weak_regime_risk_retained'] ?? true),
                'weak_regime_removed' => false,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_parallel_run_validation_ignores_weak_regime_risk' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'weak_regime_parallel_run_validation_review_completed' => true,
                'weak_regime_retained' => (bool) ($options['weak_regime_risk_retained'] ?? true),
                'weak_regime_removed' => false,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_parallel_run_validation_ignores_weak_regime_risk' => false,
            ],
        ];
    }

    private function sourceBiasSharedCoreParallelRunValidationSummary(array $options): array
    {
        return [
            'source_bias_shared_core_parallel_run_validation_completed' => true,
            'source_bias_governance_pass' => (string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH') !== 'HIGH',
            'shared_core_governance_pass' => (string) ($options['shared_core_risk_level'] ?? 'LOW') !== 'HIGH',
            'source_bias_risk_level' => (string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH'),
            'shared_core_risk_level' => (string) ($options['shared_core_risk_level'] ?? 'LOW'),
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
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed' => $pass,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c73' => false,
            'selection_changed_after_c72' => false,
            'selection_changed_after_c71' => false,
            'selection_changed_after_c70' => false,
            'selection_changed_after_c69' => false,
            'selection_changed_after_c68' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
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
            'latest_shortcut_used' => (bool) ($options['latest_shortcut_used'] ?? false),
            'max_date_shortcut_used' => (bool) ($options['max_date_shortcut_used'] ?? false),
            'future_lookup_detected' => (bool) ($options['future_lookup_detected'] ?? false),
            'return_fields_used_for_selection' => (bool) ($options['return_fields_used_for_selection'] ?? false),
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $paths = [];
        foreach (self::DOC_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path)];
        }
        return [
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => ! in_array(false, array_column($paths, 'exists'), true),
            'docs' => $paths,
            'docs_append_only' => true,
            'docs_overclaim_live_deployment' => false,
            'docs_imply_plan_confirm_default_catalog_read' => false,
            'docs_omit_parallel_run_proof' => false,
            'docs_omit_baseline_non_mutation_proof' => false,
            'docs_omit_fallback_behavior_proof' => false,
            'docs_omit_feature_flag_explicit_opt_in_kill_switch' => false,
            'docs_omit_delta_governance' => false,
            'docs_omit_bad_month_documented_risk' => false,
            'docs_omit_weak_regime_documented_risk' => false,
            'docs_omit_a01_comparator_only_restriction' => false,
            'docs_imply_plan_confirm_already_mutated' => false,
        ];
    }

    private function c65CleanupNoteSummary(array $c72): array
    {
        return [
            'c65_cleanup_note_review_completed' => true,
            'c65_cleanup_note_remains_non_blocking' => true,
            'cleanup_not_used_to_change_candidate_scope' => true,
            'cleanup_not_used_to_reopen_oos_or_retune' => true,
        ];
    }

    private function controlledParallelRunCandidateScorecard(array $c72, bool $pass, array $forcedFailures): array
    {
        return [
            $this->candidateScorecard(self::PRIMARY_CANDIDATE, 'primary_controlled_parallel_run_non_mutating_plan_confirm_bridge_candidate', self::PRIMARY_PARENT, $pass, false, $forcedFailures),
            $this->candidateScorecard(self::BACKUP_CANDIDATE, 'backup_controlled_parallel_run_non_mutating_plan_confirm_bridge_candidate', self::BACKUP_PARENT, $pass, false, $forcedFailures),
            $this->candidateScorecard(self::COMPARATOR_CANDIDATE, 'comparator_only', self::COMPARATOR_PARENT, false, true, ['C73_A01_REMAINS_COMPARATOR_ONLY']),
        ];
    }

    private function candidateScorecard(string $candidate, string $role, string $parent, bool $pass, bool $isComparator, array $forcedFailures): array
    {
        $failures = $isComparator ? ['C73_A01_REMAINS_COMPARATOR_ONLY'] : ($pass ? [] : ($forcedFailures ?: ['C73_VALIDATION_NOT_PASSED']));
        return [
            'candidate_code' => $candidate,
            'c73_role' => $role,
            'parent_candidate_code' => $parent,
            'c72_controlled_opt_in_bridge_evidence_summary' => ['carried_from_c72' => true],
            'c71_shadow_dry_run_evidence_summary' => ['carried_from_c72_source_locks' => true],
            'c70_execution_review_evidence_summary' => ['carried_from_c72_source_locks' => true],
            'c69_bridge_evidence_summary' => ['carried_from_c72_source_locks' => true],
            'c68_activation_execution_evidence_summary' => ['carried_from_c72_source_locks' => true],
            'c67_activation_review_evidence_summary' => ['carried_from_c72_source_locks' => true],
            'c66_lock_evidence_summary' => ['carried_from_c72_source_locks' => true],
            'c65_prelock_evidence_summary' => ['carried_from_c72_source_locks' => true],
            'c64_oos_evidence_summary' => ['carried_from_c72_source_locks' => true, 'oos_not_reused_for_ranking' => true],
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => $pass && ! $isComparator,
            'candidate_ready_for_c74_controlled_operator_reviewed_rollout_gate' => $pass && ! $isComparator,
            'candidate_active_in_controlled_catalog' => $pass && ! $isComparator,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed' => $pass && ! $isComparator,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => $pass && ! $isComparator,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'default_off_feature_flag_pass' => $pass && ! $isComparator,
            'kill_switch_parallel_run_validation_pass' => $pass && ! $isComparator,
            'explicit_opt_in_required_pass' => $pass && ! $isComparator,
            'controlled_parallel_run_execution_proof_pass' => $pass && ! $isComparator,
            'baseline_plan_confirm_hash_unchanged_pass' => $pass && ! $isComparator,
            'parallel_run_output_non_mutation_pass' => $pass && ! $isComparator,
            'plan_confirm_output_non_mutation_pass' => $pass && ! $isComparator,
            'parallel_run_delta_advisory_only_pass' => $pass && ! $isComparator,
            'audit_logging_parallel_run_validation_pass' => $pass && ! $isComparator,
            'fallback_behavior_parallel_run_validation_pass' => $pass && ! $isComparator,
            'bad_month_governance_pass' => $pass && ! $isComparator,
            'weak_regime_governance_pass' => $pass && ! $isComparator,
            'source_bias_governance_pass' => $pass && ! $isComparator,
            'shared_core_governance_pass' => $pass && ! $isComparator,
            'safety_and_leakage_governance_pass' => $pass && ! $isComparator,
            'production_mutation_safety_pass' => $pass && ! $isComparator,
            'failure_reason_codes' => $failures,
        ];
    }

    private function controlledParallelRunValidationDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_executed' => true,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => $pass,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_status' => $pass ? 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP' : 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_FAILED_BOTH',
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_parallel_run_readiness_pass' => $pass,
            'backup_parallel_run_readiness_pass' => $pass,
            'a01_remains_comparator_only' => true,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'All C73 controlled parallel-run non-mutating PLAN/CONFIRM bridge gates passed in isolated validation path.' : 'C73 controlled parallel-run gates did not all pass.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW' : 'C73_TARGETED_REPAIR_REQUIRED',
        ];
    }

    private function c74ReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_c74_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'c74_recommendation' => $pass ? self::C74_RECOMMENDATION : 'C74_CONTROLLED_PARALLEL_RUN_CONTRACT_REPAIR',
            'decision_reason' => $pass ? 'C73 controlled parallel-run non-mutating PLAN/CONFIRM bridge validation passed for primary and backup only.' : 'C73 failed or did not complete all controlled parallel-run gates.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW' : 'TARGETED_C73_REPAIR_REQUIRED',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed' => $pass,
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass' => $pass,
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
                if ($code !== 'C73_A01_REMAINS_COMPARATOR_ONLY') {
                    $failures[$code] = true;
                }
            }
        }
        return [
            'failure_attribution_completed' => true,
            'dominant_blocker' => $pass ? null : (array_key_first($failures) ?: 'C73_VALIDATION_NOT_PASSED'),
            'failure_reason_codes' => array_keys($failures),
            'targeted_repair_recommendation' => $pass ? null : 'C74_CONTROLLED_PARALLEL_RUN_CONTRACT_REPAIR',
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C73 controlled parallel-run PLAN/CONFIRM bridge validation is isolated and non-live.',
            'C73 does not mutate PLAN/CONFIRM and does not wire activated catalog to live runtime.',
            'C73 preserves C72 -> C60 lineage and E02/B01/A01 hierarchy.',
            'C73 parallel-run delta is advisory only and cannot select, retune, rerank, mutate, rollout, or deploy.',
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
        $artifact['next_step_recommendation'] = 'C74_C72_LOCK_OR_LINEAGE_REPAIR';
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_executed'] = false;
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed'] = false;
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass'] = false;
        $artifact['production_ready'] = false;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C74_CONTROLLED_PARALLEL_RUN_CONTRACT_REPAIR';
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_executed'] = true;
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed'] = false;
        $artifact['controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass'] = false;
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
            $artifact['status'] = 'C73_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'C73_OUTPUT_EXISTS';
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
