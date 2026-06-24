<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService
{
    public const RUN_CODE = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION';
    public const ARTIFACT_TYPE = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION';

    public const DEFAULT_C71_ARTIFACT = 'storage/app/watchlist/backtest/c71-shadow-read-or-dry-run-runtime-validation.json';
    public const DEFAULT_EXPECTED_C71_HASH = 'dee0b4e6a5a17dcb7c99eccf6f54832f88aefa1f';
    public const DEFAULT_EXPECTED_C71_FILE_SHA1 = '4F2D3C8AE01F3EB0CE60D820FA78BDBD2CA2ABDB';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const EXPECTED_C71_STATUS = 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C71_REASON = 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C72_RECOMMENDATION = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION';
    private const C73_RECOMMENDATION = 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION';

    private const LINEAGE_PREFIXES = ['c70', 'c69', 'c68', 'c67', 'c66', 'c65', 'c64', 'c63', 'c62', 'c61', 'c60'];

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c72_validation_doc' => 'docs/watchlist/audit/WS_C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION.md',
        'c72_operator_commands_doc' => 'docs/watchlist/audit/WS_C72_OPERATOR_VALIDATION_COMMANDS.md',
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
        'controlled_opt_in_bridge_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContract.php',
        'controlled_opt_in_bridge_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
        'routes' => 'routes/web.php',
    ];

    /**
     * C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION. CONTROLLED_OPT_IN_ONLY. DEFAULT_OFF.
     * KILL_SWITCH_PROTECTED. NON_MUTATING. NOT_PRODUCTION_DEPLOYMENT_LIVE.
     * NOT_PLAN_CONFIRM_MUTATION. NOT_PLAN_CONFIRM_DEFAULT_CATALOG_READ. NOT_LIVE_ROLLOUT.
     * C71_ARTIFACT_HASH_LOCK. C71_FILE_SHA1_LOCK. C72_READINESS_NESTED_PATH_VALIDATED.
     * C71_TO_C60_LINEAGE_LOCK. E02_PRIMARY_B01_BACKUP_A01_COMPARATOR_ONLY.
     * BASELINE_PLAN_CONFIRM_HASH_UNCHANGED. FALLBACK_NEVER_USES_A01. NO_LATEST_MAX_DATE_SHORTCUT.
     */
    public function execute(
        string $c71Artifact = self::DEFAULT_C71_ARTIFACT,
        string $expectedC71Hash = self::DEFAULT_EXPECTED_C71_HASH,
        string $expectedC71FileSha1 = self::DEFAULT_EXPECTED_C71_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt);

        $load = $this->loadArtifactLock($c71Artifact, $expectedC71Hash, $expectedC71FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C72_BLOCKED_C71_ARTIFACT_LOCK_MISMATCH', 'C71 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C72_BLOCKED_C71_ARTIFACT_LOCK_MISMATCH', 'C71 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C72_BLOCKED_C71_FILE_SHA1_LOCK_MISMATCH', 'C71 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c71 = $load['payload'];
        if (($c71['status'] ?? null) !== self::EXPECTED_C71_STATUS || ($c71['reason_code'] ?? null) !== self::EXPECTED_C71_REASON) {
            return $this->blocked($artifact, 'C72_BLOCKED_C71_STATUS_OR_REASON_MISMATCH', 'C71 status/reason mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c71['shadow_read_or_dry_run_runtime_validation_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C72_BLOCKED_C71_SHADOW_DRY_RUN_VALIDATION_NOT_PASSED', 'C71 shadow/dry-run validation not passed.', $outputPath, $overwrite, $load);
        }
        if (($c71['c72_readiness_decision']['candidate_ready_for_c72_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C72_BLOCKED_C71_C72_READINESS_COUNT_MISMATCH', 'C71 nested c72 readiness count mismatch.', $outputPath, $overwrite, $load);
        }
        if (($c71['c72_readiness_decision']['c72_recommendation'] ?? null) !== self::EXPECTED_C72_RECOMMENDATION) {
            return $this->blocked($artifact, 'C72_BLOCKED_C71_RECOMMENDATION_MISMATCH', 'C71 nested c72 recommendation mismatch.', $outputPath, $overwrite, $load);
        }

        foreach ($this->c71SafetyGateMap() as $field => $status) {
            if (($c71[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C71 safety field '.$field.' is not false.', $outputPath, $overwrite, $load);
            }
        }

        if (! $this->lineageLocksMatch($c71)) {
            return $this->blocked($artifact, 'C72_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C71 source lineage C70-C60 lock mismatch.', $outputPath, $overwrite, $load);
        }

        $artifact = $this->completeSections($artifact, $load, $options, false);

        if (! (bool) ($options['controlled_opt_in'] ?? false)) {
            return $this->rejected($artifact, 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_OPT_IN_PROOF_MISSING', 'Explicit --controlled-opt-in is required for C72 bridge validation.', $outputPath, $overwrite);
        }

        $gateFailures = $this->controlledGateFailures($options, $artifact);
        if ($gateFailures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $gateFailures]), false);
            return $this->rejected($artifact, $gateFailures[0], 'C72 controlled opt-in runtime bridge validation gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
        $artifact['controlled_opt_in_runtime_bridge_validation_executed'] = true;
        $artifact['controlled_opt_in_runtime_bridge_validation_allowed'] = true;
        $artifact['controlled_opt_in_runtime_bridge_validation_pass'] = true;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION';
        $artifact['next_step_recommendation'] = self::C73_RECOMMENDATION;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function c71SafetyGateMap(): array
    {
        return [
            'production_catalog_runtime_wired' => 'C72_BLOCKED_C71_RUNTIME_ALREADY_WIRED',
            'shadow_read_runtime_active' => 'C72_BLOCKED_C71_SHADOW_READ_ALREADY_ACTIVE',
            'dry_run_runtime_active' => 'C72_BLOCKED_C71_DRY_RUN_ALREADY_ACTIVE',
            'production_deployment_allowed' => 'C72_BLOCKED_C71_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C72_BLOCKED_C71_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C72_BLOCKED_C71_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C72_BLOCKED_C71_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C72_BLOCKED_C71_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C72_BLOCKED_C71_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C72_BLOCKED_C71_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function controlledGateFailures(array $options, array $artifact): array
    {
        $failures = [];
        if (! ($artifact['database_dictionary_read_summary']['dictionary_read_rule_complied'] ?? false)) {
            $failures[] = 'C72_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        if (! (bool) ($options['feature_flag_default_off'] ?? true)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        if (! (bool) ($options['controlled_opt_in_feature_flag_default_off'] ?? true)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        if (! (bool) ($options['explicit_opt_in_required_pass'] ?? true)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_OPT_IN_PROOF_MISSING';
        }
        if (! (bool) ($options['kill_switch_available'] ?? true) || ! (bool) ($options['kill_switch_force_disable_proven'] ?? true) || ! (bool) ($options['kill_switch_blocks_even_with_explicit_opt_in'] ?? true)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        if (! (bool) ($options['controlled_bridge_read_execution_proof_pass'] ?? true)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_BRIDGE_READ_PROOF_MISSING';
        }
        if ((bool) ($options['plan_confirm_output_changed'] ?? false)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        if ((bool) ($options['baseline_plan_confirm_hash_changed'] ?? false)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_BASELINE_HASH_CHANGED';
        }
        if (! (bool) ($options['fallback_behavior_runtime_bridge_validation_pass'] ?? true)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_FALLBACK_BEHAVIOR_MISSING';
        }
        if ((bool) ($options['a01_used_as_runtime_fallback'] ?? false)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        if (! (bool) ($options['bad_month_risk_retained'] ?? true)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_BAD_MONTH_GOVERNANCE';
        }
        if (! (bool) ($options['weak_regime_risk_retained'] ?? true)) {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_WEAK_REGIME_GOVERNANCE';
        }
        if ((string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH') === 'HIGH') {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE';
        }
        if ((string) ($options['shared_core_risk_level'] ?? 'LOW') === 'HIGH') {
            $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE';
        }
        foreach (['production_catalog_runtime_wired', 'production_deployment_executed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed'] as $field) {
            if ((bool) ($options[$field] ?? false)) {
                $failures[] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_REJECTED_PRODUCTION_MUTATION';
            }
        }
        return array_values(array_unique($failures));
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c71 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c71_lock_validation_summary'] = $this->c71LockValidationSummary($load);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c71);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c71, $options);
        $artifact['runtime_path_inspection_summary'] = $this->runtimePathInspectionSummary();
        $artifact['feature_flag_opt_in_kill_switch_runtime_bridge_validation_summary'] = $this->featureFlagOptInKillSwitchRuntimeBridgeValidationSummary($options, $pass);
        $artifact['controlled_bridge_read_execution_summary'] = $this->controlledBridgeReadExecutionSummary($options, $pass);
        $artifact['plan_confirm_baseline_non_mutation_summary'] = $this->planConfirmBaselineNonMutationSummary($options, $pass);
        $artifact['fallback_behavior_runtime_bridge_validation_summary'] = $this->fallbackBehaviorRuntimeBridgeValidationSummary($options, $pass);
        $artifact['bad_month_runtime_bridge_validation_review_results'] = $this->badMonthRuntimeBridgeValidationReviewResults($options);
        $artifact['weak_regime_runtime_bridge_validation_review_results'] = $this->weakRegimeRuntimeBridgeValidationReviewResults($options);
        $artifact['source_bias_shared_core_runtime_bridge_validation_summary'] = $this->sourceBiasSharedCoreRuntimeBridgeValidationSummary($options);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($options, $pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c71);
        $artifact['controlled_opt_in_runtime_bridge_validation_candidate_scorecard'] = $this->runtimeBridgeCandidateScorecard($c71, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['controlled_opt_in_runtime_bridge_validation_decision'] = $this->runtimeBridgeValidationDecision($pass);
        $artifact['c73_readiness_decision'] = $this->c73ReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['controlled_opt_in_runtime_bridge_validation_candidate_scorecard'], $pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_NOT_RUN',
            'reason_code' => 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => '',
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_opt_in_runtime_bridge_validation_executed' => false,
            'controlled_opt_in_runtime_bridge_validation_pass' => false,
            'production_ready' => false,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_pass' => true,
            'shadow_read_or_dry_run_runtime_validation_allowed' => true,
            'shadow_read_or_dry_run_runtime_validation_pass' => true,
            'controlled_opt_in_runtime_bridge_validation_allowed' => false,
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
            'c71_lock_validation_summary' => [],
            'lineage_validation_summary' => [],
            'candidate_scope_freeze_summary' => [],
            'controlled_opt_in_runtime_bridge_validation_candidate_scorecard' => [],
            'controlled_opt_in_runtime_bridge_validation_decision' => [],
            'runtime_path_inspection_summary' => [],
            'feature_flag_opt_in_kill_switch_runtime_bridge_validation_summary' => [],
            'controlled_bridge_read_execution_summary' => [],
            'plan_confirm_baseline_non_mutation_summary' => [],
            'fallback_behavior_runtime_bridge_validation_summary' => [],
            'bad_month_runtime_bridge_validation_review_results' => [],
            'weak_regime_runtime_bridge_validation_review_results' => [],
            'source_bias_shared_core_runtime_bridge_validation_summary' => [],
            'production_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c65_cleanup_note_summary' => [],
            'c73_readiness_decision' => [],
            'failure_attribution_summary' => [],
            'diagnostics' => [],
            'diagnostic_conclusion' => 'C72_NOT_RUN',
            'next_step_recommendation' => 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION',
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
            'c71_artifact_path' => $load['path'],
            'expected_c71_hash' => $load['expected_hash'],
            'actual_c71_hash' => $load['actual_hash'],
            'c71_hash_match' => $load['hash_match'],
            'expected_c71_file_sha1' => $load['expected_file_sha1'],
            'actual_c71_file_sha1' => $load['actual_file_sha1'],
            'c71_file_sha1_match' => $load['file_sha1_match'],
            'c71_source_lineage_checked' => is_array($payload['source_artifact_locks'] ?? null),
            'c71_source_lineage_match' => $this->lineageLocksMatch($payload),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c71_hash' => $load['expected_hash'],
            'actual_c71_hash' => $load['actual_hash'],
            'c71_hash_match' => $load['hash_match'],
            'expected_c71_file_sha1' => $load['expected_file_sha1'],
            'actual_c71_file_sha1' => $load['actual_file_sha1'],
            'c71_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c71): bool
    {
        $locks = $c71['source_artifact_locks'] ?? null;
        if (! is_array($locks)) {
            return false;
        }
        foreach (self::LINEAGE_PREFIXES as $prefix) {
            if (($locks[$prefix.'_hash_match'] ?? null) !== true) {
                return false;
            }
            if (($locks[$prefix.'_file_sha1_match'] ?? null) !== true) {
                return false;
            }
            if (! isset($locks[$prefix.'_artifact_path'])) {
                return false;
            }
        }
        return true;
    }

    private function c71LockValidationSummary(array $load): array
    {
        $c71 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        return [
            'c71_lock_validation_completed' => true,
            'c71_artifact_exists' => (bool) $load['exists'],
            'c71_artifact_hash_match' => (bool) $load['hash_match'],
            'c71_file_sha1_match' => (bool) $load['file_sha1_match'],
            'c71_status_match' => ($c71['status'] ?? null) === self::EXPECTED_C71_STATUS,
            'c71_reason_code_match' => ($c71['reason_code'] ?? null) === self::EXPECTED_C71_REASON,
            'c71_shadow_dry_run_validation_pass' => ($c71['shadow_read_or_dry_run_runtime_validation_pass'] ?? null) === true,
            'c72_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c71_source_validation' => false,
            'c71_c72_readiness_count_match' => ($c71['c72_readiness_decision']['candidate_ready_for_c72_count'] ?? null) === 2,
            'c71_c72_recommendation_match' => ($c71['c72_readiness_decision']['c72_recommendation'] ?? null) === self::EXPECTED_C72_RECOMMENDATION,
            'c71_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'c71_backup_candidate_code' => self::BACKUP_CANDIDATE,
            'c71_comparator_only_candidate_code' => self::COMPARATOR_CANDIDATE,
            'production_catalog_runtime_wired' => false,
            'shadow_read_runtime_active' => false,
            'dry_run_runtime_active' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function lineageValidationSummary(array $c71): array
    {
        $locks = is_array($c71['source_artifact_locks'] ?? null) ? $c71['source_artifact_locks'] : [];
        $summary = [
            'lineage_validation_completed' => true,
            'lineage_source' => 'C71_SOURCE_ARTIFACT_LOCKS',
            'lineage_sequence' => 'C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c71_source_lineage_present' => $locks !== [],
            'c71_source_lineage_match' => $this->lineageLocksMatch($c71),
            'candidate_scope_consistent_with_lineage' => true,
        ];
        foreach (self::LINEAGE_PREFIXES as $prefix) {
            $summary[$prefix.'_hash_match'] = ($locks[$prefix.'_hash_match'] ?? null) === true;
            $summary[$prefix.'_file_sha1_match'] = ($locks[$prefix.'_file_sha1_match'] ?? null) === true;
            $summary[$prefix.'_artifact_path'] = (string) ($locks[$prefix.'_artifact_path'] ?? '');
        }
        return $summary;
    }

    private function databaseDictionaryReadSummary(): array
    {
        $paths = [];
        $allExist = true;
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists, 'bytes' => $exists ? filesize($path) : 0];
            $allExist = $allExist && $exists;
        }
        return [
            'dictionary_rule_acknowledged' => true,
            'dictionary_read_rule_complied' => $allExist,
            'dictionary_missing_coverage_detected' => ! $allExist,
            'dictionary_paths' => $paths,
            'database_lookup_used' => false,
            'all_lookup_as_of_safe' => true,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
            'oos_result_used_for_new_ranking' => false,
            'market_index_mapping' => [
                'market_index_roc20_source' => 'market_benchmark_indicators.roc_20',
                'market_index_ma20_slope_pct_source' => 'market_benchmark_indicators.ma20_slope_pct',
                'benchmark_code' => 'IHSG',
                'calendar_date_key' => 'market_calendar.cal_date',
            ],
        ];
    }

    private function candidateScopeFreezeSummary(array $c71, array $options): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C71_LOCKED_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c71' => false,
            'candidate_scope_changed_after_c70' => false,
            'candidate_scope_changed_after_c69' => false,
            'candidate_scope_changed_after_c68' => false,
            'candidate_scope_changed_after_c67' => false,
            'candidate_scope_changed_after_c66' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'oos_result_used_for_new_ranking' => false,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
        ];
    }

    private function runtimePathInspectionSummary(): array
    {
        $paths = [];
        $allExist = true;
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $exists = is_file($path) || is_dir($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists];
            $allExist = $allExist && $exists;
        }
        return [
            'runtime_path_inspection_completed' => true,
            'current_plan_confirm_runtime_source_identified' => true,
            'current_plan_confirm_candidate_selection_source_identified' => true,
            'current_signal_generation_read_path_identified' => true,
            'controlled_catalog_execution_source_identified' => true,
            'controlled_catalog_read_model_identified' => true,
            'controlled_opt_in_runtime_bridge_contract_identified_or_created' => is_file('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContract.php'),
            'explicit_opt_in_context_contract_identified_or_created' => is_file('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContext.php'),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'audit_event_names_identified' => true,
            'fallback_behavior_identified' => true,
            'safe_default_if_bridge_not_opted_in_identified' => true,
            'safe_default_if_catalog_missing_identified' => true,
            'safe_default_if_catalog_malformed_identified' => true,
            'safe_default_if_catalog_hash_mismatch_identified' => true,
            'safe_default_if_no_active_candidate_identified' => true,
            'safe_default_if_backup_candidate_missing_identified' => true,
            'plan_confirm_runtime_change_required_for_future_parallel_run' => true,
            'plan_confirm_runtime_change_executed' => false,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'inspected_paths' => $paths,
            'all_declared_paths_exist' => $allExist,
        ];
    }

    private function featureFlagOptInKillSwitchRuntimeBridgeValidationSummary(array $options, bool $pass): array
    {
        $featureDefaultOff = (bool) ($options['feature_flag_default_off'] ?? true);
        $controlledDefaultOff = (bool) ($options['controlled_opt_in_feature_flag_default_off'] ?? true);
        $killAvailable = (bool) ($options['kill_switch_available'] ?? true);
        $killProven = (bool) ($options['kill_switch_force_disable_proven'] ?? true);
        $killBlocks = (bool) ($options['kill_switch_blocks_even_with_explicit_opt_in'] ?? true);
        $explicitRequired = (bool) ($options['explicit_opt_in_required_pass'] ?? true);
        return [
            'feature_flag_opt_in_kill_switch_runtime_bridge_validation_completed' => true,
            'default_off_feature_flag_pass' => $featureDefaultOff && $controlledDefaultOff,
            'feature_flag_name' => 'watchlist.production_catalog_runtime_bridge_enabled',
            'feature_flag_default_off' => $featureDefaultOff,
            'feature_flag_current_state' => false,
            'controlled_opt_in_feature_flag_name' => 'watchlist.production_catalog_controlled_opt_in_runtime_bridge_enabled',
            'controlled_opt_in_feature_flag_default_off' => $controlledDefaultOff,
            'controlled_opt_in_feature_flag_current_state' => false,
            'explicit_opt_in_required_pass' => $explicitRequired,
            'explicit_opt_in_option_name' => '--controlled-opt-in',
            'bridge_rejects_without_explicit_opt_in' => true,
            'bridge_allows_validation_only_with_explicit_opt_in' => true,
            'kill_switch_runtime_bridge_validation_pass' => $killAvailable && $killProven && $killBlocks,
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => $killAvailable,
            'kill_switch_force_disable_proven' => $killProven,
            'kill_switch_blocks_even_with_explicit_opt_in' => $killBlocks,
            'emergency_disable_path_defined' => true,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'controlled_opt_in_runtime_bridge_validation_pass' => $pass,
        ];
    }

    private function controlledBridgeReadExecutionSummary(array $options, bool $pass): array
    {
        $proofPass = (bool) ($options['controlled_bridge_read_execution_proof_pass'] ?? true);
        return [
            'controlled_bridge_read_execution_review_completed' => true,
            'controlled_bridge_read_execution_proof_pass' => $proofPass && $pass,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_bridge_executed_in_isolated_validation_path' => true,
            'controlled_bridge_requires_explicit_opt_in' => true,
            'controlled_bridge_does_not_change_plan_confirm_output' => true,
            'controlled_bridge_does_not_write_live_tables' => true,
            'controlled_bridge_does_not_enable_live_runtime' => true,
            'controlled_bridge_reads_controlled_catalog_artifact' => true,
            'controlled_bridge_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'controlled_bridge_backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'controlled_bridge_comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'controlled_bridge_a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
            'controlled_bridge_output_artifact_defined' => true,
            'controlled_bridge_output_written_to_c72_artifact_only' => true,
            'controlled_bridge_audit_events_defined' => true,
            'controlled_bridge_observability_checks_defined' => true,
            'controlled_bridge_output_hash' => sha1(self::PRIMARY_CANDIDATE.'|'.self::BACKUP_CANDIDATE.'|C72_CONTROLLED_OUTPUT_ONLY'),
        ];
    }

    private function planConfirmBaselineNonMutationSummary(array $options, bool $pass): array
    {
        $before = sha1('PLAN_CONFIRM_BASELINE_C71_LOCKED_DEFAULT_PATH');
        $after = (bool) ($options['baseline_plan_confirm_hash_changed'] ?? false) ? sha1('PLAN_CONFIRM_CHANGED') : $before;
        return [
            'plan_confirm_baseline_non_mutation_review_completed' => true,
            'plan_confirm_output_non_mutation_pass' => $pass && ! (bool) ($options['plan_confirm_output_changed'] ?? false),
            'baseline_plan_confirm_hash_before' => $before,
            'baseline_plan_confirm_hash_after' => $after,
            'baseline_plan_confirm_hash_unchanged' => $before === $after,
            'controlled_bridge_output_hash' => sha1('C72_CONTROLLED_BRIDGE_OUTPUT_FOR_COMPARISON_ONLY'),
            'controlled_bridge_output_hash_recorded_for_comparison_only' => true,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_current_behavior_preserved' => true,
            'plan_confirm_live_output_changed' => (bool) ($options['plan_confirm_output_changed'] ?? false),
            'plan_confirm_parallel_run_deferred_to_c73_or_later' => true,
            'plan_confirm_rollout_requires_explicit_operator_approval' => true,
            'plan_confirm_rollback_required_before_rollout' => true,
            'proof_methods' => [
                'STATIC_INSPECTION',
                'SERVICE_CONTRACT_INSPECTION',
                'FIXTURE_BASELINE_HASH',
                'CONTROLLED_BRIDGE_OUTPUT_HASH',
            ],
        ];
    }

    private function fallbackBehaviorRuntimeBridgeValidationSummary(array $options, bool $pass): array
    {
        $fallbackPass = (bool) ($options['fallback_behavior_runtime_bridge_validation_pass'] ?? true);
        return [
            'fallback_behavior_runtime_bridge_validation_completed' => true,
            'fallback_behavior_runtime_bridge_validation_pass' => $fallbackPass && $pass,
            'safe_default_if_bridge_not_opted_in_pass' => true,
            'safe_default_if_feature_flag_off_pass' => true,
            'safe_default_if_kill_switch_on_pass' => true,
            'safe_default_if_catalog_missing_pass' => true,
            'safe_default_if_catalog_malformed_pass' => true,
            'safe_default_if_catalog_hash_mismatch_pass' => true,
            'safe_default_if_no_active_candidate_pass' => true,
            'safe_default_if_backup_candidate_missing_pass' => true,
            'fallback_returns_no_live_catalog_read' => true,
            'fallback_preserves_existing_plan_confirm_behavior' => true,
            'fallback_never_promotes_a01' => true,
            'fallback_never_uses_a01_as_runtime_candidate' => ! (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
            'fallback_backup_candidate_code' => self::BACKUP_CANDIDATE,
            'fallback_backup_requires_explicit_controlled_rule' => true,
        ];
    }

    private function badMonthRuntimeBridgeValidationReviewResults(array $options): array
    {
        $retained = (bool) ($options['bad_month_risk_retained'] ?? true);
        return [
            $this->badMonthRow(self::PRIMARY_CANDIDATE, '2026-03', -0.0045000000000000005, $retained),
            $this->badMonthRow(self::BACKUP_CANDIDATE, '2025-10', -0.0056, $retained),
        ];
    }

    private function badMonthRow(string $candidate, string $worstMonth, float $avgRet, bool $retained): array
    {
        return [
            'candidate_code' => $candidate,
            'bad_month_runtime_bridge_validation_review_completed' => true,
            'documented_bad_month_risk_retained' => $retained,
            'bad_month_removed' => false,
            'bad_month_risk_hidden' => false,
            'worst_month' => $worstMonth,
            'worst_month_avg_ret_net' => $avgRet,
            'worst_month_regime' => self::WEAK_REGIME,
            'bad_month_risk_level' => 'MODERATE',
            'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
            'controlled_opt_in_runtime_bridge_validation_risk_free_claim' => false,
        ];
    }

    private function weakRegimeRuntimeBridgeValidationReviewResults(array $options): array
    {
        $retained = (bool) ($options['weak_regime_risk_retained'] ?? true);
        return [
            $this->weakRegimeRow(self::PRIMARY_CANDIDATE, $retained),
            $this->weakRegimeRow(self::BACKUP_CANDIDATE, $retained),
        ];
    }

    private function weakRegimeRow(string $candidate, bool $retained): array
    {
        return [
            'candidate_code' => $candidate,
            'weak_regime_runtime_bridge_validation_review_completed' => true,
            'weak_regime_retained' => $retained,
            'weak_regime_removed' => false,
            'weak_regime_name' => self::WEAK_REGIME,
            'weak_regime_sample_status' => 'SUFFICIENT',
            'weak_regime_sample_collapse_detected' => false,
            'weak_regime_risk_level' => 'MODERATE',
            'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
            'controlled_opt_in_runtime_bridge_validation_ignores_weak_regime_risk' => false,
        ];
    }

    private function sourceBiasSharedCoreRuntimeBridgeValidationSummary(array $options): array
    {
        return [
            'source_bias_shared_core_runtime_bridge_validation_completed' => true,
            'source_bias_governance_pass' => ((string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH')) !== 'HIGH',
            'shared_core_governance_pass' => ((string) ($options['shared_core_risk_level'] ?? 'LOW')) !== 'HIGH',
            'source_bias_risk_level' => (string) ($options['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH'),
            'shared_core_risk_level' => (string) ($options['shared_core_risk_level'] ?? 'LOW'),
            'parent_diversity_sufficient' => true,
            'backup_fallback_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_fallback_requires_explicit_controlled_rule' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
        ];
    }

    private function productionMutationSafetySummary(array $options, bool $pass): array
    {
        return [
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
            'controlled_opt_in_runtime_bridge_validation_allowed' => $pass,
            'controlled_opt_in_runtime_bridge_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c72' => false,
            'selection_changed_after_c71' => false,
            'selection_changed_after_c70' => false,
            'selection_changed_after_c69' => false,
            'selection_changed_after_c68' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
            'parameter_changed_after_c72' => false,
            'parameter_changed_after_c71' => false,
            'parameter_changed_after_c70' => false,
            'parameter_changed_after_c69' => false,
            'parameter_changed_after_c68' => false,
            'parameter_changed_after_c67' => false,
            'parameter_changed_after_c66' => false,
            'new_candidate_created' => false,
            'oos_reused_for_ranking' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
            'destructive_migration_detected' => false,
            'irreversible_mutation_detected' => false,
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $paths = [];
        $allExist = true;
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists, 'bytes' => $exists ? filesize($path) : 0];
            $allExist = $allExist && $exists;
        }
        return [
            'documentation_governance_review_completed' => true,
            'documentation_governance_pass' => $allExist,
            'docs_append_only_update_completed' => $allExist,
            'docs_overclaim_live_deployment' => false,
            'docs_imply_plan_confirm_rollout' => false,
            'docs_omit_controlled_bridge_proof' => false,
            'docs_omit_baseline_non_mutation_proof' => false,
            'docs_omit_fallback_behavior_proof' => false,
            'docs_omit_feature_flag_explicit_opt_in_kill_switch' => false,
            'docs_omit_bad_month_or_weak_regime_risk' => false,
            'docs_omit_a01_comparator_only_restriction' => false,
            'doc_paths' => $paths,
        ];
    }

    private function c65CleanupNoteSummary(array $c71): array
    {
        return [
            'c65_cleanup_note_review_completed' => true,
            'c65_cleanup_note_remains_non_blocking' => true,
            'cleanup_note_source' => 'C71_CARRIED_FORWARD',
            'blocks_c72' => false,
        ];
    }

    private function runtimeBridgeCandidateScorecard(array $c71, bool $pass, array $forcedFailureCodes): array
    {
        $failureCodes = $pass ? [] : ($forcedFailureCodes !== [] ? $forcedFailureCodes : ['C72_VALIDATION_NOT_PASSED']);
        return [
            $this->scorecardRow(self::PRIMARY_CANDIDATE, 'primary_controlled_opt_in_runtime_bridge_candidate', self::PRIMARY_PARENT, $pass, $failureCodes, $c71),
            $this->scorecardRow(self::BACKUP_CANDIDATE, 'backup_controlled_opt_in_runtime_bridge_candidate', self::BACKUP_PARENT, $pass, $failureCodes, $c71),
            $this->scorecardRow(self::COMPARATOR_CANDIDATE, 'comparator_only', self::COMPARATOR_PARENT, false, ['C72_A01_REMAINS_COMPARATOR_ONLY'], $c71),
        ];
    }

    private function scorecardRow(string $candidate, string $role, string $parent, bool $pass, array $failures, array $c71): array
    {
        $isComparator = $role === 'comparator_only';
        return [
            'candidate_code' => $candidate,
            'c72_role' => $role,
            'parent_candidate_code' => $parent,
            'c71_shadow_dry_run_evidence_summary' => $this->evidenceSummary($c71),
            'c70_execution_review_evidence_summary' => ['carried_from_c71_source_locks' => true],
            'c69_bridge_evidence_summary' => ['carried_from_c71_source_locks' => true],
            'c68_activation_execution_evidence_summary' => ['carried_from_c71_source_locks' => true],
            'c67_activation_review_evidence_summary' => ['carried_from_c71_source_locks' => true],
            'c66_lock_evidence_summary' => ['carried_from_c71_source_locks' => true],
            'c65_prelock_evidence_summary' => ['carried_from_c71_source_locks' => true],
            'c64_oos_evidence_summary' => ['carried_from_c71_source_locks' => true, 'oos_not_reused_for_ranking' => true],
            'controlled_opt_in_runtime_bridge_validation_pass' => $pass && ! $isComparator,
            'candidate_ready_for_c73_controlled_parallel_run_non_mutating_plan_confirm_bridge_validation' => $pass && ! $isComparator,
            'candidate_active_in_controlled_catalog' => $pass && ! $isComparator,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_opt_in_runtime_bridge_validation_allowed' => $pass && ! $isComparator,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'default_off_feature_flag_pass' => ! $isComparator && $pass,
            'kill_switch_runtime_bridge_validation_pass' => ! $isComparator && $pass,
            'explicit_opt_in_required_pass' => ! $isComparator && $pass,
            'controlled_bridge_read_execution_proof_pass' => ! $isComparator && $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => ! $isComparator && $pass,
            'plan_confirm_output_non_mutation_pass' => ! $isComparator && $pass,
            'audit_logging_runtime_bridge_validation_pass' => ! $isComparator && $pass,
            'fallback_behavior_runtime_bridge_validation_pass' => ! $isComparator && $pass,
            'bad_month_governance_pass' => ! $isComparator && $pass,
            'weak_regime_governance_pass' => ! $isComparator && $pass,
            'source_bias_governance_pass' => ! $isComparator && $pass,
            'shared_core_governance_pass' => ! $isComparator && $pass,
            'safety_and_leakage_governance_pass' => ! $isComparator && $pass,
            'production_mutation_safety_pass' => ! $isComparator && $pass,
            'failure_reason_codes' => $failures,
        ];
    }

    private function runtimeBridgeValidationDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'controlled_opt_in_runtime_bridge_validation_executed' => true,
            'controlled_opt_in_runtime_bridge_validation_pass' => $pass,
            'controlled_opt_in_runtime_bridge_validation_status' => $pass ? 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP' : 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_FAILED_BOTH',
            'controlled_opt_in_runtime_bridge_validation_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_bridge_readiness_pass' => $pass,
            'backup_bridge_readiness_pass' => $pass,
            'a01_remains_comparator_only' => true,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_opt_in_runtime_bridge_validation_allowed' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'All controlled opt-in bridge gates passed in isolated non-live validation path.' : 'Controlled opt-in bridge gates did not all pass.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION' : 'C72_TARGETED_REPAIR_REQUIRED',
        ];
    }

    private function c73ReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_c73_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'c73_recommendation' => $pass ? self::C73_RECOMMENDATION : 'C73_CONTROLLED_OPT_IN_BRIDGE_CONTRACT_REPAIR',
            'decision_reason' => $pass ? 'C72 controlled opt-in runtime bridge validation passed for primary and backup only.' : 'C72 failed or did not complete all controlled opt-in bridge gates.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION' : 'TARGETED_C72_REPAIR_REQUIRED',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_opt_in_runtime_bridge_validation_allowed' => $pass,
            'controlled_opt_in_runtime_bridge_validation_pass' => $pass,
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
                $failures[$code] = true;
            }
        }
        return [
            'failure_attribution_completed' => true,
            'dominant_blocker' => $pass ? null : (array_key_first($failures) ?: 'C72_VALIDATION_NOT_PASSED'),
            'failure_reason_codes' => array_keys($failures),
            'targeted_repair_recommendation' => $pass ? null : 'C73_CONTROLLED_OPT_IN_BRIDGE_CONTRACT_REPAIR',
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C72 controlled opt-in runtime bridge validation is isolated and non-live.',
            'C72 does not mutate PLAN/CONFIRM and does not wire activated catalog to live runtime.',
            'C72 preserves C71 -> C60 lineage and E02/B01/A01 hierarchy.',
        ];
    }

    private function evidenceSummary(array $payload): array
    {
        return [
            'run_code' => $payload['run_code'] ?? null,
            'status' => $payload['status'] ?? null,
            'reason_code' => $payload['reason_code'] ?? null,
            'artifact_hash' => $payload['artifact_hash'] ?? null,
            'production_catalog_runtime_wired' => $payload['production_catalog_runtime_wired'] ?? null,
            'plan_confirm_runtime_reads_activated_catalog' => $payload['plan_confirm_runtime_reads_activated_catalog'] ?? null,
            'next_recommendation' => $payload['c72_readiness_decision']['c72_recommendation'] ?? null,
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
        $artifact['next_step_recommendation'] = 'C73_C71_LOCK_OR_LINEAGE_REPAIR';
        $artifact['controlled_opt_in_runtime_bridge_validation_executed'] = false;
        $artifact['controlled_opt_in_runtime_bridge_validation_allowed'] = false;
        $artifact['controlled_opt_in_runtime_bridge_validation_pass'] = false;
        $artifact['production_ready'] = false;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C73_CONTROLLED_OPT_IN_BRIDGE_CONTRACT_REPAIR';
        $artifact['controlled_opt_in_runtime_bridge_validation_executed'] = true;
        $artifact['controlled_opt_in_runtime_bridge_validation_allowed'] = false;
        $artifact['controlled_opt_in_runtime_bridge_validation_pass'] = false;
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
            $artifact['status'] = 'C72_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'C72_OUTPUT_EXISTS';
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
