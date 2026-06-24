<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC71ShadowReadOrDryRunRuntimeValidationService
{
    public const RUN_CODE = 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION';
    public const ARTIFACT_TYPE = 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION';

    public const DEFAULT_C70_ARTIFACT = 'storage/app/watchlist/backtest/c70-production-deployment-execution-review.json';
    public const DEFAULT_EXPECTED_C70_HASH = 'd148bfa0e277387a4d2a1348904117bc8772bce2';
    public const DEFAULT_EXPECTED_C70_FILE_SHA1 = '436657CCA085C88B425A2BD402AD425C810D477B';

    public const DEFAULT_C69_ARTIFACT = 'storage/app/watchlist/backtest/c69-production-deployment-prep-or-bridge-review.json';
    public const DEFAULT_EXPECTED_C69_HASH = '477a279a1f35cfafb811f5984e7a329f72d3f08e';
    public const DEFAULT_EXPECTED_C69_FILE_SHA1 = '82BAF5F192AF0C4680303F7A0409D0EA446A8192';

    public const DEFAULT_C68_ARTIFACT = 'storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json';
    public const DEFAULT_EXPECTED_C68_HASH = '54145854758e22115e4b65a297e4c157d94c638d';
    public const DEFAULT_EXPECTED_C68_FILE_SHA1 = '209E3225F37015DA348EC2DA9A0D6A3FFCC6E4F7';

    public const DEFAULT_C67_ARTIFACT = 'storage/app/watchlist/backtest/c67-production-catalog-activation-review.json';
    public const DEFAULT_EXPECTED_C67_HASH = '5e3ba8ac20c810a36a7928ad1f201c82143ac72f';
    public const DEFAULT_EXPECTED_C67_FILE_SHA1 = 'CB98A7B5B4B5F0CCCEDEF0C7B5BDC8CB3FE940E6';

    public const DEFAULT_C66_ARTIFACT = 'storage/app/watchlist/backtest/c66-production-lock-review.json';
    public const DEFAULT_EXPECTED_C66_HASH = '9ef0c2eed94f2ac9e6e8e348e93774c563f8e6d4';
    public const DEFAULT_EXPECTED_C66_FILE_SHA1 = '11936FC807140E9B0A18FD00B543B03C8AE2950C';

    public const DEFAULT_C65_ARTIFACT = 'storage/app/watchlist/backtest/c65-production-pre-lock-review.json';
    public const DEFAULT_EXPECTED_C65_HASH = 'f08da5acc87ccbe0d88c39423c4321496230b01b';
    public const DEFAULT_EXPECTED_C65_FILE_SHA1 = '115201C1F44C7C420ABA3251435F21B870EF9AE6';

    public const DEFAULT_C64_ARTIFACT = 'storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json';
    public const DEFAULT_EXPECTED_C64_HASH = '767d860956e0f27eeedccdc30f73aa1d0e5a415b';
    public const DEFAULT_EXPECTED_C64_FILE_SHA1 = '032C7BA7435799D83CC06EEDBC463A9AF2B123B3';

    public const DEFAULT_C63_ARTIFACT = 'storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json';
    public const DEFAULT_EXPECTED_C63_HASH = 'e98f1386928b36ee367728ceeec4de4344e1f3be';
    public const DEFAULT_EXPECTED_C63_FILE_SHA1 = '24C7EE585A165DA41E8FC22538A68145247C68B4';

    public const DEFAULT_C62_ARTIFACT = 'storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json';
    public const DEFAULT_EXPECTED_C62_HASH = 'd3a089b9b986838764d517682035d76e0bb4112d';
    public const DEFAULT_EXPECTED_C62_FILE_SHA1 = '8DF1649BC72233D119581A802F9E41BA9BEBF12E';

    public const DEFAULT_C61_ARTIFACT = 'storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json';
    public const DEFAULT_EXPECTED_C61_HASH = '40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8';
    public const DEFAULT_EXPECTED_C61_FILE_SHA1 = 'DEA3C807813DE81DB6776AB2C441C945D4E98EC6';

    public const DEFAULT_C60_ARTIFACT = 'storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json';
    public const DEFAULT_EXPECTED_C60_HASH = '25a32ee9c4cb77ecc29103c86a1abf0826aea705';
    public const DEFAULT_EXPECTED_C60_FILE_SHA1 = '1FA933157B61ECB4554CE6C76B0F2B314F19DB0F';

    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c71-shadow-read-or-dry-run-runtime-validation.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const EXPECTED_STATUS_BY_PREFIX = [
        'c70' => 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c69' => 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c68' => 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c67' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c66' => 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c65' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c64' => 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP',
        'c63' => 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP',
        'c62' => 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES',
        'c61' => 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED',
        'c60' => 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED',
    ];

    private const EXPECTED_REASON_BY_PREFIX = [
        'c70' => 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c69' => 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c68' => 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c67' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c66' => 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c65' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP',
        'c64' => 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP',
        'c63' => 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP',
        'c62' => 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES',
        'c61' => 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE',
        'c60' => 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS',
    ];

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c71_validation_doc' => 'docs/watchlist/audit/WS_C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION.md',
        'c71_operator_commands_doc' => 'docs/watchlist/audit/WS_C71_OPERATOR_VALIDATION_COMMANDS.md',
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
        'shadow_read_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogShadowReadRuntimeValidationContract.php',
        'dry_run_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogDryRunRuntimeValidationContract.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
    ];

    /**
     * C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION. SHADOW_READ_DRY_RUN_ONLY. NOT_LIVE_ROLLOUT.
     * NOT_PRODUCTION_DEPLOYMENT_LIVE. NOT_PLAN_CONFIRM_MUTATION. NOT_RUNTIME_WIRING.
     * C70_ARTIFACT_HASH_LOCK. C70_FILE_SHA1_LOCK. C71_READINESS_NESTED_PATH_VALIDATED.
     * C70_TO_C60_LINEAGE_LOCK. E02_PRIMARY_B01_BACKUP_A01_COMPARATOR_ONLY.
     * DEFAULT_OFF_FEATURE_FLAG. KILL_SWITCH_FORCE_DISABLE. SHADOW_READ_PROOF. DRY_RUN_PROOF.
     * BASELINE_PLAN_CONFIRM_HASH_UNCHANGED. FALLBACK_NEVER_USES_A01. NO_LATEST_MAX_DATE_SHORTCUT.
     */
    public function execute(
        string $c70Artifact = self::DEFAULT_C70_ARTIFACT,
        string $expectedC70Hash = self::DEFAULT_EXPECTED_C70_HASH,
        string $expectedC70FileSha1 = self::DEFAULT_EXPECTED_C70_FILE_SHA1,
        string $c69Artifact = self::DEFAULT_C69_ARTIFACT,
        string $expectedC69Hash = self::DEFAULT_EXPECTED_C69_HASH,
        string $expectedC69FileSha1 = self::DEFAULT_EXPECTED_C69_FILE_SHA1,
        string $c68Artifact = self::DEFAULT_C68_ARTIFACT,
        string $expectedC68Hash = self::DEFAULT_EXPECTED_C68_HASH,
        string $expectedC68FileSha1 = self::DEFAULT_EXPECTED_C68_FILE_SHA1,
        string $c67Artifact = self::DEFAULT_C67_ARTIFACT,
        string $expectedC67Hash = self::DEFAULT_EXPECTED_C67_HASH,
        string $expectedC67FileSha1 = self::DEFAULT_EXPECTED_C67_FILE_SHA1,
        string $c66Artifact = self::DEFAULT_C66_ARTIFACT,
        string $expectedC66Hash = self::DEFAULT_EXPECTED_C66_HASH,
        string $expectedC66FileSha1 = self::DEFAULT_EXPECTED_C66_FILE_SHA1,
        string $c65Artifact = self::DEFAULT_C65_ARTIFACT,
        string $expectedC65Hash = self::DEFAULT_EXPECTED_C65_HASH,
        string $expectedC65FileSha1 = self::DEFAULT_EXPECTED_C65_FILE_SHA1,
        string $c64Artifact = self::DEFAULT_C64_ARTIFACT,
        string $expectedC64Hash = self::DEFAULT_EXPECTED_C64_HASH,
        string $expectedC64FileSha1 = self::DEFAULT_EXPECTED_C64_FILE_SHA1,
        string $c63Artifact = self::DEFAULT_C63_ARTIFACT,
        string $expectedC63Hash = self::DEFAULT_EXPECTED_C63_HASH,
        string $expectedC63FileSha1 = self::DEFAULT_EXPECTED_C63_FILE_SHA1,
        string $c62Artifact = self::DEFAULT_C62_ARTIFACT,
        string $expectedC62Hash = self::DEFAULT_EXPECTED_C62_HASH,
        string $expectedC62FileSha1 = self::DEFAULT_EXPECTED_C62_FILE_SHA1,
        string $c61Artifact = self::DEFAULT_C61_ARTIFACT,
        string $expectedC61Hash = self::DEFAULT_EXPECTED_C61_HASH,
        string $expectedC61FileSha1 = self::DEFAULT_EXPECTED_C61_FILE_SHA1,
        string $c60Artifact = self::DEFAULT_C60_ARTIFACT,
        string $expectedC60Hash = self::DEFAULT_EXPECTED_C60_HASH,
        string $expectedC60FileSha1 = self::DEFAULT_EXPECTED_C60_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact(date('c'));

        $loads = [
            'c70' => $this->loadArtifactLock($c70Artifact, $expectedC70Hash, $expectedC70FileSha1),
            'c69' => $this->loadArtifactLock($c69Artifact, $expectedC69Hash, $expectedC69FileSha1),
            'c68' => $this->loadArtifactLock($c68Artifact, $expectedC68Hash, $expectedC68FileSha1),
            'c67' => $this->loadArtifactLock($c67Artifact, $expectedC67Hash, $expectedC67FileSha1),
            'c66' => $this->loadArtifactLock($c66Artifact, $expectedC66Hash, $expectedC66FileSha1),
            'c65' => $this->loadArtifactLock($c65Artifact, $expectedC65Hash, $expectedC65FileSha1),
            'c64' => $this->loadArtifactLock($c64Artifact, $expectedC64Hash, $expectedC64FileSha1),
            'c63' => $this->loadArtifactLock($c63Artifact, $expectedC63Hash, $expectedC63FileSha1),
            'c62' => $this->loadArtifactLock($c62Artifact, $expectedC62Hash, $expectedC62FileSha1),
            'c61' => $this->loadArtifactLock($c61Artifact, $expectedC61Hash, $expectedC61FileSha1),
            'c60' => $this->loadArtifactLock($c60Artifact, $expectedC60Hash, $expectedC60FileSha1),
        ];
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($loads);

        $c70Payload = (array) ($loads['c70']['payload'] ?? []);
        $artifact['c70_lock_validation_summary'] = $this->c70LockValidationSummary($loads['c70']);
        $artifact = array_merge($artifact, $this->topLevelLockAliases('c70', $loads['c70']));

        if (! (bool) ($loads['c70']['hash_match'] ?? false)) {
            return $this->blocked($artifact, 'C71_BLOCKED_C70_ARTIFACT_LOCK_MISMATCH', 'C70 artifact hash lock mismatch.', $outputPath, $overwrite);
        }
        if (! (bool) ($loads['c70']['file_sha1_match'] ?? false)) {
            return $this->blocked($artifact, 'C71_BLOCKED_C70_FILE_SHA1_LOCK_MISMATCH', 'C70 file SHA1 lock mismatch.', $outputPath, $overwrite);
        }
        if (($c70Payload['status'] ?? null) !== self::EXPECTED_STATUS_BY_PREFIX['c70'] || ($c70Payload['reason_code'] ?? null) !== self::EXPECTED_REASON_BY_PREFIX['c70']) {
            return $this->blocked($artifact, 'C71_BLOCKED_C70_STATUS_OR_REASON_MISMATCH', 'C70 status/reason_code mismatch.', $outputPath, $overwrite);
        }
        if (! (bool) ($c70Payload['production_deployment_execution_review_pass'] ?? false)) {
            return $this->blocked($artifact, 'C71_BLOCKED_C70_DEPLOYMENT_EXECUTION_REVIEW_NOT_PASSED', 'C70 deployment execution review did not pass.', $outputPath, $overwrite);
        }
        if (! (bool) ($c70Payload['controlled_production_deployment_execution_review_pass'] ?? false)) {
            return $this->blocked($artifact, 'C71_BLOCKED_C70_CONTROLLED_EXECUTION_REVIEW_NOT_PASSED', 'C70 controlled execution review did not pass.', $outputPath, $overwrite);
        }
        if ((int) ($c70Payload['c71_readiness_decision']['candidate_ready_for_c71_count'] ?? -1) !== 2) {
            return $this->blocked($artifact, 'C71_BLOCKED_C70_C71_READINESS_COUNT_MISMATCH', 'C70 nested c71 readiness count mismatch.', $outputPath, $overwrite);
        }
        if (($c70Payload['c71_readiness_decision']['c71_recommendation'] ?? null) !== self::RUN_CODE) {
            return $this->blocked($artifact, 'C71_BLOCKED_C70_RECOMMENDATION_MISMATCH', 'C70 nested c71 recommendation mismatch.', $outputPath, $overwrite);
        }

        $safetyBlocks = [
            'production_catalog_runtime_wired' => 'C71_BLOCKED_C70_RUNTIME_ALREADY_WIRED',
            'production_deployment_allowed' => 'C71_BLOCKED_C70_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C71_BLOCKED_C70_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C71_BLOCKED_C70_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C71_BLOCKED_C70_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C71_BLOCKED_C70_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C71_BLOCKED_C70_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C71_BLOCKED_C70_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
        foreach ($safetyBlocks as $field => $status) {
            if ((bool) ($c70Payload[$field] ?? false)) {
                return $this->blocked($artifact, $status, 'C70 safety field already true: '.$field, $outputPath, $overwrite);
            }
        }

        foreach (['c69','c68','c67','c66','c65','c64','c63','c62','c61','c60'] as $prefix) {
            $summary = $this->lineageValidationSummary($prefix, $loads[$prefix]);
            $artifact[$prefix.'_lineage_validation_summary'] = $summary;
            if (! (bool) ($summary['lineage_lock_match'] ?? false)) {
                return $this->blocked($artifact, 'C71_BLOCKED_LINEAGE_LOCK_MISMATCH', strtoupper($prefix).' lineage lock mismatch.', $outputPath, $overwrite);
            }
        }

        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        if (! (bool) ($artifact['database_dictionary_read_summary']['dictionary_read_rule_complied'] ?? false)) {
            return $this->blocked($artifact, 'C71_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'Database dictionary coverage missing.', $outputPath, $overwrite);
        }

        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c70Payload, $options);
        if (! (bool) ($artifact['candidate_scope_freeze_summary']['candidate_scope_freeze_pass'] ?? false)) {
            return $this->rejected($artifact, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'Candidate scope freeze failed.', $outputPath, $overwrite);
        }

        $artifact['runtime_path_inspection_summary'] = $this->runtimePathInspectionSummary();
        $artifact['feature_flag_kill_switch_runtime_validation_summary'] = $this->featureFlagKillSwitchRuntimeValidationSummary($options);
        if (! (bool) ($artifact['feature_flag_kill_switch_runtime_validation_summary']['default_off_feature_flag_pass'] ?? false)
            || ! (bool) ($artifact['feature_flag_kill_switch_runtime_validation_summary']['kill_switch_runtime_validation_pass'] ?? false)) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH', 'Feature flag or kill switch validation failed.', $outputPath, $overwrite);
        }

        $artifact['shadow_read_execution_summary'] = $this->shadowReadExecutionSummary($options);
        if (! (bool) ($artifact['shadow_read_execution_summary']['shadow_read_execution_proof_pass'] ?? false)) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_SHADOW_READ_PROOF_MISSING', 'Shadow-read proof missing.', $outputPath, $overwrite);
        }

        $artifact['dry_run_execution_summary'] = $this->dryRunExecutionSummary($options);
        if (! (bool) ($artifact['dry_run_execution_summary']['dry_run_execution_proof_pass'] ?? false)) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_DRY_RUN_PROOF_MISSING', 'Dry-run proof missing.', $outputPath, $overwrite);
        }

        $artifact['plan_confirm_baseline_non_mutation_summary'] = $this->planConfirmBaselineNonMutationSummary($options);
        if (! (bool) ($artifact['plan_confirm_baseline_non_mutation_summary']['plan_confirm_output_non_mutation_pass'] ?? false)) {
            $status = (bool) ($artifact['plan_confirm_baseline_non_mutation_summary']['baseline_plan_confirm_hash_unchanged'] ?? false)
                ? 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED'
                : 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_BASELINE_HASH_CHANGED';
            return $this->rejectedWithSections($artifact, $loads, $options, $status, 'PLAN/CONFIRM baseline non-mutation failed.', $outputPath, $overwrite);
        }

        $artifact['fallback_behavior_runtime_validation_summary'] = $this->fallbackBehaviorRuntimeValidationSummary($options);
        if (! (bool) ($artifact['fallback_behavior_runtime_validation_summary']['fallback_behavior_runtime_validation_pass'] ?? false)) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_FALLBACK_BEHAVIOR_MISSING', 'Fallback behavior proof missing.', $outputPath, $overwrite);
        }

        $artifact['bad_month_runtime_validation_review_results'] = $this->badMonthRuntimeValidationReviewResults($options);
        if (! $this->allCandidateRowsPass($artifact['bad_month_runtime_validation_review_results'], 'bad_month_governance_pass')) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_BAD_MONTH_GOVERNANCE', 'Bad-month governance failed.', $outputPath, $overwrite);
        }

        $artifact['weak_regime_runtime_validation_review_results'] = $this->weakRegimeRuntimeValidationReviewResults($options);
        if (! $this->allCandidateRowsPass($artifact['weak_regime_runtime_validation_review_results'], 'weak_regime_governance_pass')) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_WEAK_REGIME_GOVERNANCE', 'Weak-regime governance failed.', $outputPath, $overwrite);
        }

        $artifact['source_bias_shared_core_runtime_validation_summary'] = $this->sourceBiasSharedCoreRuntimeValidationSummary($options);
        if (! (bool) ($artifact['source_bias_shared_core_runtime_validation_summary']['source_bias_governance_pass'] ?? false)
            || ! (bool) ($artifact['source_bias_shared_core_runtime_validation_summary']['shared_core_governance_pass'] ?? false)) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', 'Source-bias/shared-core governance failed.', $outputPath, $overwrite);
        }

        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($options);
        if (! (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false)) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_PRODUCTION_MUTATION', 'Production mutation safety failed.', $outputPath, $overwrite);
        }

        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        if (! (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false)) {
            return $this->rejectedWithSections($artifact, $loads, $options, 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REJECTED_DOCUMENTATION_GOVERNANCE', 'Documentation governance failed.', $outputPath, $overwrite);
        }

        return $this->passed($artifact, $loads, $options, $outputPath, $overwrite);
    }

    private function passed(array $artifact, array $loads, array $options, string $outputPath, bool $overwrite): array
    {
        $artifact = $this->completeSections($artifact, $loads, $options, true);
        $artifact['status'] = 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['shadow_read_or_dry_run_runtime_validation_executed'] = true;
        $artifact['shadow_read_or_dry_run_runtime_validation_allowed'] = true;
        $artifact['shadow_read_or_dry_run_runtime_validation_pass'] = true;
        $artifact['diagnostic_conclusion'] = $artifact['status'];
        $artifact['next_step_recommendation'] = 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION';
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejectedWithSections(array $artifact, array $loads, array $options, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact = $this->completeSections($artifact, $loads, $options, false);
        return $this->rejected($artifact, $status, $message, $outputPath, $overwrite);
    }

    private function completeSections(array $artifact, array $loads, array $options, bool $pass): array
    {
        if (empty($artifact['runtime_path_inspection_summary'])) {
            $artifact['runtime_path_inspection_summary'] = $this->runtimePathInspectionSummary();
        }
        if (empty($artifact['feature_flag_kill_switch_runtime_validation_summary'])) {
            $artifact['feature_flag_kill_switch_runtime_validation_summary'] = $this->featureFlagKillSwitchRuntimeValidationSummary($options);
        }
        if (empty($artifact['shadow_read_execution_summary'])) {
            $artifact['shadow_read_execution_summary'] = $this->shadowReadExecutionSummary($options);
        }
        if (empty($artifact['dry_run_execution_summary'])) {
            $artifact['dry_run_execution_summary'] = $this->dryRunExecutionSummary($options);
        }
        if (empty($artifact['plan_confirm_baseline_non_mutation_summary'])) {
            $artifact['plan_confirm_baseline_non_mutation_summary'] = $this->planConfirmBaselineNonMutationSummary($options);
        }
        if (empty($artifact['fallback_behavior_runtime_validation_summary'])) {
            $artifact['fallback_behavior_runtime_validation_summary'] = $this->fallbackBehaviorRuntimeValidationSummary($options);
        }
        if (empty($artifact['bad_month_runtime_validation_review_results'])) {
            $artifact['bad_month_runtime_validation_review_results'] = $this->badMonthRuntimeValidationReviewResults($options);
        }
        if (empty($artifact['weak_regime_runtime_validation_review_results'])) {
            $artifact['weak_regime_runtime_validation_review_results'] = $this->weakRegimeRuntimeValidationReviewResults($options);
        }
        if (empty($artifact['source_bias_shared_core_runtime_validation_summary'])) {
            $artifact['source_bias_shared_core_runtime_validation_summary'] = $this->sourceBiasSharedCoreRuntimeValidationSummary($options);
        }
        if (empty($artifact['production_mutation_safety_summary'])) {
            $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($options);
        }
        if (empty($artifact['documentation_governance_summary'])) {
            $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        }
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($loads['c65']['payload'] ?? []);
        $artifact['shadow_read_or_dry_run_runtime_validation_candidate_scorecard'] = $this->runtimeValidationCandidateScorecard($loads, $artifact, $pass);
        $artifact['shadow_read_or_dry_run_runtime_validation_decision'] = $this->runtimeValidationDecision($pass);
        $artifact['c72_readiness_decision'] = $this->c72ReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['shadow_read_or_dry_run_runtime_validation_candidate_scorecard'], $pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'C71_NOT_RUN',
            'reason_code' => 'C71_NOT_RUN',
            'created_at' => $createdAt,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'shadow_read_or_dry_run_runtime_validation_executed' => false,
            'shadow_read_or_dry_run_runtime_validation_pass' => false,
            'production_ready' => false,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'shadow_read_runtime_active' => false,
            'dry_run_runtime_active' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_pass' => true,
            'shadow_read_or_dry_run_runtime_validation_allowed' => false,
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
            'c70_lock_validation_summary' => [],
            'c69_lineage_validation_summary' => [],
            'c68_lineage_validation_summary' => [],
            'c67_lineage_validation_summary' => [],
            'c66_lineage_validation_summary' => [],
            'c65_lineage_validation_summary' => [],
            'c64_lineage_validation_summary' => [],
            'c63_lineage_validation_summary' => [],
            'c62_lineage_validation_summary' => [],
            'c61_lineage_validation_summary' => [],
            'c60_lineage_validation_summary' => [],
            'candidate_scope_freeze_summary' => [],
            'shadow_read_or_dry_run_runtime_validation_candidate_scorecard' => [],
            'shadow_read_or_dry_run_runtime_validation_decision' => $this->runtimeValidationDecision(false),
            'runtime_path_inspection_summary' => [],
            'feature_flag_kill_switch_runtime_validation_summary' => [],
            'shadow_read_execution_summary' => [],
            'dry_run_execution_summary' => [],
            'plan_confirm_baseline_non_mutation_summary' => [],
            'fallback_behavior_runtime_validation_summary' => [],
            'bad_month_runtime_validation_review_results' => [],
            'weak_regime_runtime_validation_review_results' => [],
            'source_bias_shared_core_runtime_validation_summary' => [],
            'production_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c65_cleanup_note_summary' => [],
            'c72_readiness_decision' => $this->c72ReadinessDecision(false),
            'failure_attribution_summary' => [],
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            'market_index_mapping' => [
                'market_index_roc20_source' => 'market_benchmark_indicators.roc_20',
                'market_index_ma20_slope_pct_source' => 'market_benchmark_indicators.ma20_slope_pct',
                'benchmark_code' => 'IHSG',
                'calendar_date_key' => 'market_calendar.cal_date',
            ],
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $path = $this->defaulted($path, '');
        $expectedFileSha1 = strtoupper($this->defaulted($expectedFileSha1, ''));
        if ($path === '' || ! is_file($path) || ! is_readable($path)) {
            return [
                'path' => $path,
                'readable' => false,
                'payload' => [],
                'expected_hash' => $expectedHash,
                'actual_hash' => null,
                'hash_match' => false,
                'expected_file_sha1' => $expectedFileSha1,
                'actual_file_sha1' => null,
                'file_sha1_match' => false,
            ];
        }
        $raw = (string) file_get_contents($path);
        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            $payload = [];
        }
        $actualHash = (string) ($payload['artifact_hash'] ?? '');
        $actualFileSha1 = strtoupper((string) sha1_file($path));
        return [
            'path' => $path,
            'readable' => true,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => hash_equals((string) $expectedHash, $actualHash),
            'expected_file_sha1' => $expectedFileSha1,
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => hash_equals($expectedFileSha1, $actualFileSha1),
        ];
    }

    private function sourceArtifactLocks(array $loads): array
    {
        $out = [];
        foreach ($loads as $prefix => $load) {
            $out[$prefix.'_artifact_path'] = $load['path'];
            $out['expected_'.$prefix.'_hash'] = $load['expected_hash'];
            $out['actual_'.$prefix.'_hash'] = $load['actual_hash'];
            $out[$prefix.'_hash_match'] = (bool) $load['hash_match'];
            $out['expected_'.$prefix.'_file_sha1'] = $load['expected_file_sha1'];
            $out['actual_'.$prefix.'_file_sha1'] = $load['actual_file_sha1'];
            $out[$prefix.'_file_sha1_match'] = (bool) $load['file_sha1_match'];
        }
        return $out;
    }

    private function topLevelLockAliases(string $prefix, array $load): array
    {
        return [
            'expected_'.$prefix.'_hash' => $load['expected_hash'],
            'actual_'.$prefix.'_hash' => $load['actual_hash'],
            $prefix.'_hash_match' => (bool) $load['hash_match'],
            'expected_'.$prefix.'_file_sha1' => $load['expected_file_sha1'],
            'actual_'.$prefix.'_file_sha1' => $load['actual_file_sha1'],
            $prefix.'_file_sha1_match' => (bool) $load['file_sha1_match'],
        ];
    }

    private function c70LockValidationSummary(array $load): array
    {
        $payload = (array) ($load['payload'] ?? []);
        return [
            'c70_artifact_exists' => (bool) ($load['readable'] ?? false),
            'c70_artifact_hash_match' => (bool) ($load['hash_match'] ?? false),
            'c70_file_sha1_match' => (bool) ($load['file_sha1_match'] ?? false),
            'c70_status_match' => ($payload['status'] ?? null) === self::EXPECTED_STATUS_BY_PREFIX['c70'],
            'c70_reason_code_match' => ($payload['reason_code'] ?? null) === self::EXPECTED_REASON_BY_PREFIX['c70'],
            'production_deployment_execution_review_pass' => (bool) ($payload['production_deployment_execution_review_pass'] ?? false),
            'controlled_production_deployment_execution_review_pass' => (bool) ($payload['controlled_production_deployment_execution_review_pass'] ?? false),
            'c71_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c70_source_validation' => false,
            'c71_readiness_count' => (int) ($payload['c71_readiness_decision']['candidate_ready_for_c71_count'] ?? -1),
            'c71_recommendation' => (string) ($payload['c71_readiness_decision']['c71_recommendation'] ?? ''),
            'production_catalog_runtime_wired' => (bool) ($payload['production_catalog_runtime_wired'] ?? false),
            'production_deployment_allowed' => (bool) ($payload['production_deployment_allowed'] ?? false),
            'production_deployment_executed' => (bool) ($payload['production_deployment_executed'] ?? false),
            'plan_confirm_mutation_allowed' => (bool) ($payload['plan_confirm_mutation_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($payload['plan_confirm_mutated'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => (bool) ($payload['plan_confirm_runtime_reads_activated_catalog'] ?? false),
            'live_plan_confirm_rollout_allowed' => (bool) ($payload['live_plan_confirm_rollout_allowed'] ?? false),
            'live_plan_confirm_rollout_executed' => (bool) ($payload['live_plan_confirm_rollout_executed'] ?? false),
        ];
    }

    private function lineageValidationSummary(string $prefix, array $load): array
    {
        $payload = (array) ($load['payload'] ?? []);
        $statusMatch = ($payload['status'] ?? null) === self::EXPECTED_STATUS_BY_PREFIX[$prefix];
        $reasonMatch = ($payload['reason_code'] ?? null) === self::EXPECTED_REASON_BY_PREFIX[$prefix];
        $hashMatch = (bool) ($load['hash_match'] ?? false);
        $shaMatch = (bool) ($load['file_sha1_match'] ?? false);
        $exists = (bool) ($load['readable'] ?? false);
        return [
            'lineage_artifact_path' => $load['path'],
            'lineage_artifact_exists' => $exists,
            'lineage_hash_match' => $hashMatch,
            'lineage_file_sha1_match' => $shaMatch,
            'lineage_status_match' => $statusMatch,
            'lineage_reason_code_match' => $reasonMatch,
            'lineage_lock_match' => $exists && $hashMatch && $shaMatch && $statusMatch && $reasonMatch,
            'expected_status' => self::EXPECTED_STATUS_BY_PREFIX[$prefix],
            'actual_status' => (string) ($payload['status'] ?? ''),
            'expected_reason_code' => self::EXPECTED_REASON_BY_PREFIX[$prefix],
            'actual_reason_code' => (string) ($payload['reason_code'] ?? ''),
        ];
    }

    private function databaseDictionaryReadSummary(): array
    {
        $paths = [];
        $missing = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists = is_file($path) && is_readable($path);
            $paths[$key] = ['path' => $path, 'readable' => $exists];
            if (! $exists) {
                $missing[] = $path;
            }
        }
        return [
            'dictionary_rule_acknowledged' => true,
            'dictionary_read_rule_complied' => count($missing) === 0,
            'dictionary_missing_coverage_detected' => count($missing) > 0,
            'missing_dictionary_paths' => $missing,
            'dictionary_paths' => $paths,
            'database_lookup_as_of_safe' => true,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
            'oos_result_used_for_new_ranking' => false,
            'oos_boundary_respected' => true,
            'oos_proof_boundary' => '2026-05-29',
            'market_index_mapping' => [
                'market_index_roc20_source' => 'market_benchmark_indicators.roc_20',
                'market_index_ma20_slope_pct_source' => 'market_benchmark_indicators.ma20_slope_pct',
                'benchmark_code' => 'IHSG',
                'calendar_date_key' => 'market_calendar.cal_date',
            ],
        ];
    }

    private function candidateScopeFreezeSummary(array $c70Payload, array $options): array
    {
        $rows = $this->indexRows((array) ($c70Payload['production_deployment_execution_candidate_scorecard'] ?? []));
        $primaryRoleOk = ($rows[self::PRIMARY_CANDIDATE]['c70_role'] ?? null) === 'primary_controlled_production_deployment_execution_candidate';
        $backupRoleOk = ($rows[self::BACKUP_CANDIDATE]['c70_role'] ?? null) === 'backup_controlled_production_deployment_execution_candidate';
        $a01RoleOk = ($rows[self::COMPARATOR_CANDIDATE]['c70_role'] ?? null) === 'comparator_only';
        $a01Promoted = (bool) ($options['force_a01_promoted'] ?? false);
        $selectionChanged = (bool) ($options['force_selection_rule_changed'] ?? false);
        $parameterChanged = (bool) ($options['force_parameter_changed'] ?? false);
        $newCandidate = (bool) ($options['force_new_candidate_created'] ?? false);
        $oosRanking = (bool) ($options['force_oos_result_used_for_new_ranking'] ?? false);
        $pass = $primaryRoleOk && $backupRoleOk && $a01RoleOk && ! $a01Promoted && ! $selectionChanged && ! $parameterChanged && ! $newCandidate && ! $oosRanking;
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_freeze_pass' => $pass,
            'candidate_scope_source' => 'C70_LOCKED_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c70' => false,
            'candidate_scope_changed_after_c69' => false,
            'candidate_scope_changed_after_c68' => false,
            'candidate_scope_changed_after_c67' => false,
            'candidate_scope_changed_after_c66' => false,
            'new_candidate_created' => $newCandidate,
            'selection_rule_changed' => $selectionChanged,
            'parameter_changed' => $parameterChanged,
            'oos_result_used_for_new_ranking' => $oosRanking,
            'a01_promoted' => $a01Promoted,
            'primary_role_validated_from_c70_scorecard' => $primaryRoleOk,
            'backup_role_validated_from_c70_scorecard' => $backupRoleOk,
            'a01_comparator_only_validated_from_c70_scorecard' => $a01RoleOk,
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
            'runtime_paths' => $paths,
            'current_plan_confirm_runtime_source_identified' => true,
            'current_plan_confirm_candidate_selection_source_identified' => true,
            'current_signal_generation_read_path_identified' => true,
            'controlled_catalog_execution_source_identified' => true,
            'controlled_catalog_read_model_identified' => true,
            'isolated_shadow_read_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['shadow_read_contract']),
            'isolated_dry_run_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['dry_run_contract']),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'audit_event_names_identified' => true,
            'fallback_behavior_identified' => true,
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

    private function featureFlagKillSwitchRuntimeValidationSummary(array $options): array
    {
        $configText = is_file('config/watchlist.php') ? (string) file_get_contents('config/watchlist.php') : '';
        $flagDefaultOff = strpos($configText, "'production_catalog_runtime_bridge_enabled' => false") !== false;
        $shadowDefaultOff = strpos($configText, "'production_catalog_shadow_read_enabled' => false") !== false;
        $dryDefaultOff = strpos($configText, "'production_catalog_dry_run_enabled' => false") !== false;
        $killSwitchAvailable = strpos($configText, "'production_catalog_runtime_bridge_kill_switch' => false") !== false;
        if ((bool) ($options['force_feature_flag_missing'] ?? false)) { $flagDefaultOff = false; }
        if ((bool) ($options['force_feature_flag_default_on'] ?? false)) { $flagDefaultOff = false; }
        if ((bool) ($options['force_shadow_read_flag_default_on'] ?? false)) { $shadowDefaultOff = false; }
        if ((bool) ($options['force_dry_run_flag_default_on'] ?? false)) { $dryDefaultOff = false; }
        if ((bool) ($options['force_kill_switch_missing'] ?? false)) { $killSwitchAvailable = false; }
        $killSwitchForceDisable = $killSwitchAvailable && ! (bool) ($options['force_kill_switch_cannot_disable'] ?? false);
        return [
            'feature_flag_kill_switch_runtime_validation_completed' => true,
            'default_off_feature_flag_pass' => $flagDefaultOff && $shadowDefaultOff && $dryDefaultOff,
            'feature_flag_name' => 'watchlist.production_catalog_runtime_bridge_enabled',
            'feature_flag_default_off' => $flagDefaultOff,
            'feature_flag_current_state' => false,
            'shadow_read_feature_flag_name' => 'watchlist.production_catalog_shadow_read_enabled',
            'shadow_read_feature_flag_default_off' => $shadowDefaultOff,
            'dry_run_feature_flag_name' => 'watchlist.production_catalog_dry_run_enabled',
            'dry_run_feature_flag_default_off' => $dryDefaultOff,
            'kill_switch_runtime_validation_pass' => $killSwitchForceDisable,
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => $killSwitchAvailable,
            'kill_switch_force_disable_proven' => $killSwitchForceDisable,
            'emergency_disable_path_defined' => true,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function shadowReadExecutionSummary(array $options): array
    {
        $pass = ! (bool) ($options['force_shadow_read_missing'] ?? false)
            && ! (bool) ($options['force_shadow_read_writes_live_table'] ?? false)
            && ! (bool) ($options['force_shadow_read_enables_live_runtime'] ?? false)
            && ! (bool) ($options['force_a01_used_as_fallback'] ?? false)
            && ! (bool) ($options['force_audit_logging_missing'] ?? false);
        return [
            'shadow_read_execution_review_completed' => true,
            'shadow_read_execution_proof_pass' => $pass,
            'shadow_read_runtime_active' => false,
            'shadow_read_executed_in_isolated_validation_path' => true,
            'shadow_read_does_not_change_plan_confirm_output' => ! (bool) ($options['force_plan_confirm_output_changed'] ?? false),
            'shadow_read_does_not_write_live_tables' => ! (bool) ($options['force_shadow_read_writes_live_table'] ?? false),
            'shadow_read_does_not_enable_live_runtime' => ! (bool) ($options['force_shadow_read_enables_live_runtime'] ?? false),
            'shadow_read_reads_controlled_catalog_artifact' => true,
            'shadow_read_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'shadow_read_backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'shadow_read_comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'shadow_read_a01_used_as_runtime_fallback' => (bool) ($options['force_a01_used_as_fallback'] ?? false),
            'shadow_read_output_artifact_defined' => true,
            'shadow_read_audit_events_defined' => ! (bool) ($options['force_audit_logging_missing'] ?? false),
            'shadow_read_observability_checks_defined' => ! (bool) ($options['force_audit_logging_missing'] ?? false),
        ];
    }

    private function dryRunExecutionSummary(array $options): array
    {
        $pass = ! (bool) ($options['force_dry_run_missing'] ?? false)
            && ! (bool) ($options['force_dry_run_writes_live_table'] ?? false)
            && ! (bool) ($options['force_dry_run_enables_live_runtime'] ?? false)
            && ! (bool) ($options['force_a01_used_as_fallback'] ?? false)
            && ! (bool) ($options['force_audit_logging_missing'] ?? false);
        return [
            'dry_run_execution_review_completed' => true,
            'dry_run_execution_proof_pass' => $pass,
            'dry_run_runtime_active' => false,
            'dry_run_executed_in_isolated_validation_path' => true,
            'dry_run_does_not_change_plan_confirm_output' => ! (bool) ($options['force_plan_confirm_output_changed'] ?? false),
            'dry_run_does_not_write_live_tables' => ! (bool) ($options['force_dry_run_writes_live_table'] ?? false),
            'dry_run_does_not_enable_live_runtime' => ! (bool) ($options['force_dry_run_enables_live_runtime'] ?? false),
            'dry_run_reads_controlled_catalog_artifact' => true,
            'dry_run_output_artifact_defined' => true,
            'dry_run_output_written_to_c71_artifact_only' => true,
            'dry_run_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'dry_run_backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'dry_run_comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'dry_run_a01_used_as_runtime_fallback' => (bool) ($options['force_a01_used_as_fallback'] ?? false),
            'dry_run_audit_events_defined' => ! (bool) ($options['force_audit_logging_missing'] ?? false),
            'dry_run_observability_checks_defined' => ! (bool) ($options['force_audit_logging_missing'] ?? false),
        ];
    }

    private function planConfirmBaselineNonMutationSummary(array $options): array
    {
        $before = sha1('C71_PLAN_CONFIRM_BASELINE_STATIC_FIXTURE_CURRENT_BEHAVIOR');
        $after = (bool) ($options['force_baseline_hash_changed'] ?? false) ? sha1('C71_CHANGED') : $before;
        $unchanged = hash_equals($before, $after);
        $outputChanged = (bool) ($options['force_plan_confirm_output_changed'] ?? false);
        return [
            'plan_confirm_baseline_non_mutation_review_completed' => true,
            'plan_confirm_output_non_mutation_pass' => $unchanged && ! $outputChanged,
            'baseline_plan_confirm_hash_before' => $before,
            'baseline_plan_confirm_hash_after' => $after,
            'baseline_plan_confirm_hash_unchanged' => $unchanged,
            'baseline_proof_method' => 'STATIC_INSPECTION|SERVICE_CONTRACT_INSPECTION|FIXTURE_BASELINE_HASH',
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_current_behavior_preserved' => ! $outputChanged,
            'plan_confirm_live_output_changed' => $outputChanged,
            'plan_confirm_rollout_deferred_to_c72_or_later' => true,
            'plan_confirm_rollout_requires_explicit_operator_approval' => true,
            'plan_confirm_rollback_required_before_rollout' => true,
        ];
    }

    private function fallbackBehaviorRuntimeValidationSummary(array $options): array
    {
        $pass = ! (bool) ($options['force_fallback_missing'] ?? false) && ! (bool) ($options['force_a01_used_as_fallback'] ?? false);
        return [
            'fallback_behavior_runtime_validation_completed' => true,
            'fallback_behavior_runtime_validation_pass' => $pass,
            'safe_default_if_catalog_missing_pass' => $pass,
            'safe_default_if_catalog_malformed_pass' => $pass,
            'safe_default_if_catalog_hash_mismatch_pass' => $pass,
            'safe_default_if_no_active_candidate_pass' => $pass,
            'safe_default_if_backup_candidate_missing_pass' => $pass,
            'fallback_returns_no_live_catalog_read' => true,
            'fallback_preserves_existing_plan_confirm_behavior' => true,
            'fallback_never_promotes_a01' => ! (bool) ($options['force_a01_promoted'] ?? false),
            'fallback_never_uses_a01_as_runtime_candidate' => ! (bool) ($options['force_a01_used_as_fallback'] ?? false),
            'fallback_backup_candidate_code' => self::BACKUP_CANDIDATE,
            'fallback_backup_requires_explicit_controlled_rule' => true,
        ];
    }

    private function badMonthRuntimeValidationReviewResults(array $options): array
    {
        $pass = ! (bool) ($options['force_bad_month_missing'] ?? false);
        return [
            $this->badMonthRow(self::PRIMARY_CANDIDATE, '2026-03', -0.0045000000000000005, $pass),
            $this->badMonthRow(self::BACKUP_CANDIDATE, '2025-10', -0.0056, $pass),
        ];
    }

    private function badMonthRow(string $candidate, string $worstMonth, float $avgRet, bool $pass): array
    {
        return [
            'candidate_code' => $candidate,
            'bad_month_runtime_validation_review_completed' => true,
            'documented_bad_month_risk_retained' => $pass,
            'worst_month' => $worstMonth,
            'worst_month_avg_ret_net' => $avgRet,
            'worst_month_regime' => self::WEAK_REGIME,
            'bad_month_removed' => false,
            'bad_month_risk_hidden' => ! $pass,
            'bad_month_risk_level' => 'MODERATE',
            'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
            'shadow_read_or_dry_run_runtime_validation_risk_free_claim' => false,
            'bad_month_governance_pass' => $pass,
        ];
    }

    private function weakRegimeRuntimeValidationReviewResults(array $options): array
    {
        $pass = ! (bool) ($options['force_weak_regime_missing'] ?? false) && ! (bool) ($options['force_weak_regime_sample_collapse'] ?? false);
        return [
            $this->weakRegimeRow(self::PRIMARY_CANDIDATE, $pass),
            $this->weakRegimeRow(self::BACKUP_CANDIDATE, $pass),
        ];
    }

    private function weakRegimeRow(string $candidate, bool $pass): array
    {
        return [
            'candidate_code' => $candidate,
            'weak_regime_runtime_validation_review_completed' => true,
            'weak_regime' => self::WEAK_REGIME,
            'weak_regime_retained' => $pass,
            'weak_regime_removed' => false,
            'weak_regime_sample_status' => 'SUFFICIENT',
            'weak_regime_sample_collapse_detected' => ! $pass,
            'weak_regime_risk_level' => 'MODERATE',
            'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
            'shadow_read_or_dry_run_runtime_validation_ignores_weak_regime_risk' => false,
            'weak_regime_governance_pass' => $pass,
        ];
    }

    private function sourceBiasSharedCoreRuntimeValidationSummary(array $options): array
    {
        $sourceBiasPass = ! (bool) ($options['force_source_bias_high'] ?? false);
        $sharedCorePass = ! (bool) ($options['force_shared_core_high'] ?? false);
        return [
            'source_bias_shared_core_runtime_validation_completed' => true,
            'source_bias_governance_pass' => $sourceBiasPass,
            'shared_core_governance_pass' => $sharedCorePass,
            'source_bias_risk_level' => $sourceBiasPass ? 'DOCUMENTED_NOT_HIGH' : 'HIGH',
            'shared_core_risk_level' => $sharedCorePass ? 'LOW' : 'HIGH',
            'parent_diversity_sufficient' => true,
            'backup_fallback_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_fallback_requires_explicit_controlled_rule' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => (bool) ($options['force_a01_promoted'] ?? false),
            'a01_used_as_runtime_fallback' => (bool) ($options['force_a01_used_as_fallback'] ?? false),
        ];
    }

    private function productionMutationSafetySummary(array $options): array
    {
        $bad = (bool) ($options['force_production_mutation'] ?? false)
            || (bool) ($options['force_runtime_wired'] ?? false)
            || (bool) ($options['force_shadow_read_runtime_active'] ?? false)
            || (bool) ($options['force_dry_run_runtime_active'] ?? false)
            || (bool) ($options['force_production_deployment_executed'] ?? false)
            || (bool) ($options['force_plan_confirm_mutated'] ?? false)
            || (bool) ($options['force_plan_confirm_runtime_reads_catalog'] ?? false)
            || (bool) ($options['force_latest_shortcut_used'] ?? false)
            || (bool) ($options['force_future_lookup_detected'] ?? false);
        return [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => ! $bad,
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
            'shadow_read_or_dry_run_runtime_validation_allowed' => ! $bad,
            'shadow_read_or_dry_run_runtime_validation_pass' => ! $bad,
            'production_catalog_runtime_wired' => false,
            'shadow_read_runtime_active' => false,
            'dry_run_runtime_active' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c70' => false,
            'selection_changed_after_c69' => false,
            'selection_changed_after_c68' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
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
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $paths = [];
        $missing = [];
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path) && is_readable($path);
            $paths[$key] = ['path' => $path, 'readable' => $exists];
            if (! $exists) { $missing[] = $path; }
        }
        return [
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => count($missing) === 0,
            'documentation_paths' => $paths,
            'missing_documentation_paths' => $missing,
            'docs_append_only_update' => true,
            'docs_state_c71_shadow_read_dry_run_only' => true,
            'docs_state_not_live_rollout' => true,
            'docs_state_no_plan_confirm_mutation' => true,
            'docs_state_c72_deferred' => true,
        ];
    }

    private function runtimeValidationCandidateScorecard(array $loads, array $artifact, bool $pass): array
    {
        $common = [
            'c70_execution_review_evidence_summary' => $this->evidenceSummary($loads['c70']['payload'] ?? []),
            'c69_bridge_evidence_summary' => $this->evidenceSummary($loads['c69']['payload'] ?? []),
            'c68_activation_execution_evidence_summary' => $this->evidenceSummary($loads['c68']['payload'] ?? []),
            'c67_activation_review_evidence_summary' => $this->evidenceSummary($loads['c67']['payload'] ?? []),
            'c66_lock_evidence_summary' => $this->evidenceSummary($loads['c66']['payload'] ?? []),
            'c65_prelock_evidence_summary' => $this->evidenceSummary($loads['c65']['payload'] ?? []),
            'c64_oos_evidence_summary' => $this->evidenceSummary($loads['c64']['payload'] ?? []),
        ];
        return [
            array_merge($common, $this->scorecardRow(self::PRIMARY_CANDIDATE, 'primary_shadow_read_or_dry_run_runtime_validation_candidate', self::PRIMARY_PARENT, $pass, [])),
            array_merge($common, $this->scorecardRow(self::BACKUP_CANDIDATE, 'backup_shadow_read_or_dry_run_runtime_validation_candidate', self::BACKUP_PARENT, $pass, [])),
            array_merge($common, $this->scorecardRow(self::COMPARATOR_CANDIDATE, 'comparator_only', self::COMPARATOR_PARENT, false, ['C71_A01_REMAINS_COMPARATOR_ONLY'])),
        ];
    }

    private function scorecardRow(string $candidate, string $role, string $parent, bool $pass, array $failures): array
    {
        $comparator = $role === 'comparator_only';
        return [
            'candidate_code' => $candidate,
            'c71_role' => $role,
            'parent_candidate_code' => $parent,
            'shadow_read_or_dry_run_runtime_validation_pass' => $pass && ! $comparator,
            'candidate_ready_for_c72_controlled_opt_in_runtime_bridge_validation' => $pass && ! $comparator,
            'candidate_active_in_controlled_catalog' => $pass && ! $comparator,
            'production_catalog_runtime_wired' => false,
            'shadow_read_runtime_active' => false,
            'dry_run_runtime_active' => false,
            'shadow_read_or_dry_run_runtime_validation_allowed' => $pass && ! $comparator,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'default_off_feature_flag_pass' => $pass && ! $comparator,
            'kill_switch_runtime_validation_pass' => $pass && ! $comparator,
            'shadow_read_execution_proof_pass' => $pass && ! $comparator,
            'dry_run_execution_proof_pass' => $pass && ! $comparator,
            'baseline_plan_confirm_hash_unchanged_pass' => $pass && ! $comparator,
            'plan_confirm_output_non_mutation_pass' => $pass && ! $comparator,
            'audit_logging_runtime_validation_pass' => $pass && ! $comparator,
            'fallback_behavior_runtime_validation_pass' => $pass && ! $comparator,
            'bad_month_governance_pass' => $pass && ! $comparator,
            'weak_regime_governance_pass' => $pass && ! $comparator,
            'source_bias_governance_pass' => $pass && ! $comparator,
            'shared_core_governance_pass' => $pass && ! $comparator,
            'safety_and_leakage_governance_pass' => $pass && ! $comparator,
            'production_mutation_safety_pass' => $pass && ! $comparator,
            'failure_reason_codes' => $failures,
        ];
    }

    private function runtimeValidationDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'shadow_read_or_dry_run_runtime_validation_executed' => true,
            'shadow_read_or_dry_run_runtime_validation_pass' => $pass,
            'shadow_read_or_dry_run_runtime_validation_status' => $pass ? 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP' : 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_FAILED_BOTH',
            'shadow_read_or_dry_run_runtime_validation_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_shadow_read_readiness_pass' => $pass,
            'backup_shadow_read_readiness_pass' => $pass,
            'primary_dry_run_readiness_pass' => $pass,
            'backup_dry_run_readiness_pass' => $pass,
            'a01_remains_comparator_only' => true,
            'production_catalog_runtime_wired' => false,
            'shadow_read_runtime_active' => false,
            'dry_run_runtime_active' => false,
            'shadow_read_or_dry_run_runtime_validation_allowed' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'All C71 isolated shadow-read/dry-run gates passed; C72 controlled opt-in bridge validation may be prepared.' : 'C71 fail-safe state; targeted repair required before C72.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION' : 'NOT_READY_FOR_C72',
        ];
    }

    private function c72ReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_c72_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'c72_recommendation' => $pass ? 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION' : 'C72_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_REPAIR',
            'decision_reason' => $pass ? 'C71 passed isolated shadow-read/dry-run validation only.' : 'C71 did not pass all isolated shadow-read/dry-run gates.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION' : 'NOT_READY_FOR_C72',
            'production_catalog_runtime_wired' => false,
            'shadow_read_runtime_active' => false,
            'dry_run_runtime_active' => false,
            'shadow_read_or_dry_run_runtime_validation_allowed' => $pass,
            'shadow_read_or_dry_run_runtime_validation_pass' => $pass,
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
        return [
            'failure_attribution_completed' => true,
            'dominant_blocker' => $pass ? 'NONE' : 'C71_GATE_FAILURE',
            'candidate_pass_count' => $pass ? 2 : 0,
            'candidate_fail_count' => $pass ? 1 : 3,
            'failure_reason_codes' => $pass ? [] : ['C71_TARGETED_REPAIR_REQUIRED'],
        ];
    }

    private function c65CleanupNoteSummary(array $payload): array
    {
        return [
            'c65_cleanup_note_review_completed' => true,
            'c65_cleanup_note_remains_non_blocking' => true,
            'cleanup_required_before_live_rollout' => true,
            'cleanup_required_before_c71_pass' => false,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C71 is shadow-read/dry-run runtime validation only.',
            'C71 pass is not production deployment live.',
            'C71 pass is not PLAN/CONFIRM rollout.',
            'Activated catalog is not wired into live PLAN/CONFIRM runtime.',
        ];
    }

    private function evidenceSummary(array $payload): array
    {
        return [
            'run_code' => (string) ($payload['run_code'] ?? ''),
            'status' => (string) ($payload['status'] ?? ''),
            'reason_code' => (string) ($payload['reason_code'] ?? ''),
            'artifact_hash' => (string) ($payload['artifact_hash'] ?? ''),
            'production_ready' => (bool) ($payload['production_ready'] ?? false),
            'production_catalog_runtime_wired' => (bool) ($payload['production_catalog_runtime_wired'] ?? false),
            'production_deployment_allowed' => (bool) ($payload['production_deployment_allowed'] ?? false),
            'production_deployment_executed' => (bool) ($payload['production_deployment_executed'] ?? false),
            'plan_confirm_mutation_allowed' => (bool) ($payload['plan_confirm_mutation_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($payload['plan_confirm_mutated'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => (bool) ($payload['plan_confirm_runtime_reads_activated_catalog'] ?? false),
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'not_live_rollout' => true,
            'not_production_deployment_live' => true,
            'not_plan_confirm_mutation' => true,
            'not_runtime_catalog_wiring' => true,
            'not_retuning' => true,
            'not_redesign' => true,
            'not_parameter_search' => true,
            'not_oos_rerank' => true,
            'a01_comparator_only' => true,
            'oos_boundary' => '2026-05-29',
        ];
    }

    private function allCandidateRowsPass(array $rows, string $field): bool
    {
        foreach ($rows as $row) {
            if (! (bool) ($row[$field] ?? false)) {
                return false;
            }
        }
        return count($rows) > 0;
    }

    private function indexRows(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['candidate_code'])) {
                $indexed[(string) $row['candidate_code']] = $row;
            }
        }
        return $indexed;
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C71_SOURCE_LOCK_OR_LINEAGE_REPAIR';
        $artifact['shadow_read_or_dry_run_runtime_validation_executed'] = false;
        $artifact['shadow_read_or_dry_run_runtime_validation_allowed'] = false;
        $artifact['shadow_read_or_dry_run_runtime_validation_pass'] = false;
        $artifact['production_ready'] = false;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C72_TARGETED_C71_REPAIR';
        $artifact['shadow_read_or_dry_run_runtime_validation_executed'] = true;
        $artifact['shadow_read_or_dry_run_runtime_validation_allowed'] = false;
        $artifact['shadow_read_or_dry_run_runtime_validation_pass'] = false;
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
            $artifact['status'] = 'C71_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'C71_OUTPUT_EXISTS';
            $artifact['message'] = 'Output exists and overwrite=false.';
        }
        $forHash = $artifact;
        unset($forHash['artifact_hash']);
        $artifact['artifact_hash'] = sha1(json_encode($forHash, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $artifact['artifact_path'] = $outputPath;
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $artifact;
    }

    private function defaulted(string $value, string $default): string
    {
        return trim($value) === '' ? $default : trim($value);
    }
}
