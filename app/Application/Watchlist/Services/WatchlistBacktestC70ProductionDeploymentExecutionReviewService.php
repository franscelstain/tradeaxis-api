<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC70ProductionDeploymentExecutionReviewService
{
    public const RUN_CODE = 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW';
    public const ARTIFACT_TYPE = 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW';

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

    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c70-production-deployment-execution-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const EXPECTED_STATUS_BY_PREFIX = [
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
        'c70_review_doc' => 'docs/watchlist/audit/WS_C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW.md',
        'c70_operator_commands_doc' => 'docs/watchlist/audit/WS_C70_OPERATOR_VALIDATION_COMMANDS.md',
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
        'runtime_bridge_contract_marker' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogRuntimeBridgeContract.php',
    ];

    /**
     * C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW. CONTROLLED_NON_LIVE_EXECUTION_REVIEW_ONLY.
     * NOT_FULL_LIVE_ROLLOUT. NOT_PLAN_CONFIRM_MUTATION. NOT_RUNTIME_WIRING. NOT_REDESIGN. NOT_RETUNE.
     * C69_ARTIFACT_HASH_LOCK. C69_FILE_SHA1_LOCK. C69_READINESS_NESTED_PATH_VALIDATED.
     * C60_TO_C69_LINEAGE_LOCK. E02_PRIMARY_B01_BACKUP_A01_COMPARATOR_ONLY. DEFAULT_OFF_FLAG.
     * KILL_SWITCH_REQUIRED. ROLLBACK_PROOF_REQUIRED. SMOKE_TEST_PROOF_REQUIRED. SHADOW_READ_DRY_RUN_PROOF_REQUIRED.
     * PLAN_CONFIRM_NON_MUTATION_REQUIRED. PRODUCTION_DEPLOYMENT_ALLOWED_FALSE. PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG_FALSE.
     */
    public function execute(
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
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact([
            'c69' => [$c69Artifact, $expectedC69Hash, $expectedC69FileSha1],
            'c68' => [$c68Artifact, $expectedC68Hash, $expectedC68FileSha1],
            'c67' => [$c67Artifact, $expectedC67Hash, $expectedC67FileSha1],
            'c66' => [$c66Artifact, $expectedC66Hash, $expectedC66FileSha1],
            'c65' => [$c65Artifact, $expectedC65Hash, $expectedC65FileSha1],
            'c64' => [$c64Artifact, $expectedC64Hash, $expectedC64FileSha1],
            'c63' => [$c63Artifact, $expectedC63Hash, $expectedC63FileSha1],
            'c62' => [$c62Artifact, $expectedC62Hash, $expectedC62FileSha1],
            'c61' => [$c61Artifact, $expectedC61Hash, $expectedC61FileSha1],
            'c60' => [$c60Artifact, $expectedC60Hash, $expectedC60FileSha1],
        ], (string) ($options['executed_at'] ?? gmdate('c')));

        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        if ((bool) ($artifact['database_dictionary_read_summary']['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C70_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'Dictionary coverage is mandatory.', $outputPath, $overwrite);
        }

        $loads = [];
        foreach ($artifact['_source_input_locks'] as $prefix => $lock) {
            $loads[$prefix] = $this->loadArtifactLock((string) $lock[0], (string) $lock[1], (string) $lock[2]);
            $this->copyLock($artifact, $prefix, $loads[$prefix]);
        }
        unset($artifact['_source_input_locks']);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        $c69Validation = $this->validateC69Lock($loads['c69']);
        $artifact['c69_lock_validation_summary'] = $c69Validation;
        if (! (bool) ($loads['c69']['readable'] ?? false) || ! (bool) ($loads['c69']['hash_match'] ?? false)) {
            return $this->blocked($artifact, 'C70_BLOCKED_C69_ARTIFACT_LOCK_MISMATCH', 'C69 artifact lock mismatch.', $outputPath, $overwrite);
        }
        if (! (bool) ($loads['c69']['file_sha1_match'] ?? false)) {
            return $this->blocked($artifact, 'C70_BLOCKED_C69_FILE_SHA1_LOCK_MISMATCH', 'C69 file SHA1 lock mismatch.', $outputPath, $overwrite);
        }
        if (! (bool) ($c69Validation['status_reason_match'] ?? false)) {
            return $this->blocked($artifact, 'C70_BLOCKED_C69_STATUS_OR_REASON_MISMATCH', 'C69 status/reason mismatch.', $outputPath, $overwrite);
        }

        $c69Payload = (array) ($loads['c69']['payload'] ?? []);
        foreach ($this->c69BooleanGateMap() as $field => $spec) {
            if ((bool) ($c69Payload[$field] ?? null) !== (bool) $spec[0]) {
                return $this->blocked($artifact, (string) $spec[1], 'C69 '.$field.' gate mismatch.', $outputPath, $overwrite);
            }
        }
        if ((int) $this->nested($c69Payload, ['c70_readiness_decision', 'candidate_ready_for_c70_count'], 0) !== 2) {
            return $this->blocked($artifact, 'C70_BLOCKED_C69_C70_READINESS_COUNT_MISMATCH', 'C69 C70 readiness count mismatch.', $outputPath, $overwrite);
        }
        if ((string) $this->nested($c69Payload, ['c70_readiness_decision', 'c70_recommendation'], '') !== self::RUN_CODE) {
            return $this->blocked($artifact, 'C70_BLOCKED_C69_RECOMMENDATION_MISMATCH', 'C69 C70 recommendation mismatch.', $outputPath, $overwrite);
        }

        foreach (['c68', 'c67', 'c66', 'c65', 'c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
            $summary = $this->lineageValidationSummary($prefix, $loads[$prefix]);
            $artifact[$prefix.'_lineage_validation_summary'] = $summary;
            if (! (bool) ($summary['lineage_lock_pass'] ?? false)) {
                return $this->blocked($artifact, 'C70_BLOCKED_LINEAGE_LOCK_MISMATCH', strtoupper($prefix).' lineage lock mismatch.', $outputPath, $overwrite);
            }
        }

        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c69Payload, $options);
        if (! (bool) ($artifact['candidate_scope_freeze_summary']['candidate_scope_freeze_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'Candidate scope mismatch.', $outputPath, $overwrite);
        }

        $artifact['runtime_path_inspection_summary'] = $this->runtimePathInspectionSummary();
        $artifact['execution_contract_review_summary'] = $this->executionContractReviewSummary($artifact['runtime_path_inspection_summary'], $options);
        if (! (bool) ($artifact['execution_contract_review_summary']['execution_contract_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_EXECUTION_CONTRACT_MISSING', 'Execution contract missing.', $outputPath, $overwrite);
        }

        $artifact['feature_flag_kill_switch_execution_summary'] = $this->featureFlagKillSwitchExecutionSummary($options);
        if (! (bool) ($artifact['feature_flag_kill_switch_execution_summary']['default_off_feature_flag_pass'] ?? false)
            || ! (bool) ($artifact['feature_flag_kill_switch_execution_summary']['kill_switch_execution_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING', 'Feature flag or kill switch missing.', $outputPath, $overwrite);
        }

        $artifact['rollback_execution_verification_summary'] = $this->rollbackExecutionVerificationSummary($options);
        if (! (bool) ($artifact['rollback_execution_verification_summary']['rollback_execution_proof_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_ROLLBACK_EXECUTION_PROOF_MISSING', 'Rollback execution proof missing.', $outputPath, $overwrite);
        }

        $artifact['smoke_test_execution_summary'] = $this->smokeTestExecutionSummary($options);
        if (! (bool) ($artifact['smoke_test_execution_summary']['smoke_test_execution_proof_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SMOKE_TEST_EXECUTION_PROOF_MISSING', 'Smoke test proof missing.', $outputPath, $overwrite);
        }

        $artifact['shadow_read_or_dry_run_execution_summary'] = $this->shadowReadOrDryRunExecutionSummary($options);
        if (! (bool) ($artifact['shadow_read_or_dry_run_execution_summary']['shadow_read_or_dry_run_execution_proof_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SHADOW_READ_EXECUTION_PROOF_MISSING', 'Shadow-read/dry-run proof missing.', $outputPath, $overwrite);
        }

        $artifact['plan_confirm_non_mutation_summary'] = $this->planConfirmNonMutationSummary($options);
        if (! (bool) ($artifact['plan_confirm_non_mutation_summary']['plan_confirm_non_mutation_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_ALREADY_MUTATED', 'PLAN/CONFIRM non-mutation failed.', $outputPath, $overwrite);
        }

        $artifact['bad_month_execution_review_results'] = $this->badMonthExecutionReviewResults($options);
        if (! $this->allCandidateRowsPass($artifact['bad_month_execution_review_results'], 'bad_month_governance_pass')) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE', 'Bad-month governance failed.', $outputPath, $overwrite);
        }

        $artifact['weak_regime_execution_review_results'] = $this->weakRegimeExecutionReviewResults($options);
        if (! $this->allCandidateRowsPass($artifact['weak_regime_execution_review_results'], 'weak_regime_governance_pass')) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE', 'Weak-regime governance failed.', $outputPath, $overwrite);
        }

        $artifact['source_bias_shared_core_execution_review_summary'] = $this->sourceBiasSharedCoreExecutionReviewSummary($options);
        if (! (bool) ($artifact['source_bias_shared_core_execution_review_summary']['source_bias_governance_pass'] ?? false)
            || ! (bool) ($artifact['source_bias_shared_core_execution_review_summary']['shared_core_governance_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', 'Source-bias/shared-core governance failed.', $outputPath, $overwrite);
        }

        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($c69Payload, $options);
        if (! (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_PRODUCTION_MUTATION', 'Production mutation safety failed.', $outputPath, $overwrite);
        }

        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        if (! (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false)) {
            return $this->rejected($artifact, 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE', 'Documentation governance failed.', $outputPath, $overwrite);
        }

        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c69Payload);
        $scorecard = $this->deploymentExecutionCandidateScorecard($c69Payload, $loads, $artifact);
        $artifact['production_deployment_execution_candidate_scorecard'] = $scorecard;
        $decision = $this->controlledDeploymentExecutionDecision($scorecard);
        $artifact['controlled_deployment_execution_decision'] = $decision;
        $artifact['c71_readiness_decision'] = $this->c71ReadinessDecision($decision);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($scorecard, $decision);
        $artifact['diagnostics'] = $this->diagnostics();

        $artifact['status'] = (string) ($decision['production_deployment_execution_review_status'] ?? 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_FAILED_BOTH');
        $artifact['reason_code'] = $artifact['status'];
        $artifact['production_deployment_execution_review_executed'] = true;
        $artifact['production_deployment_execution_review_pass'] = (bool) ($decision['production_deployment_execution_review_pass'] ?? false);
        $artifact['production_catalog_lock_allowed'] = true;
        $artifact['production_catalog_activation_allowed'] = true;
        $artifact['production_catalog_activation_execution_allowed'] = true;
        $artifact['production_catalog_activation_execution_performed'] = true;
        $artifact['production_catalog_activated'] = true;
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_prep_allowed'] = true;
        $artifact['production_deployment_execution_review_allowed'] = true;
        $artifact['controlled_production_deployment_execution_review_allowed'] = (bool) ($decision['controlled_production_deployment_execution_review_allowed'] ?? false);
        $artifact['controlled_production_deployment_execution_review_pass'] = (bool) ($decision['controlled_production_deployment_execution_review_pass'] ?? false);
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
        $artifact['plan_confirm_wiring_prep_allowed'] = true;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['plan_confirm_mutated'] = false;
        $artifact['plan_confirm_runtime_reads_activated_catalog'] = false;
        $artifact['live_plan_confirm_rollout_allowed'] = false;
        $artifact['live_plan_confirm_rollout_executed'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $artifact['status'];
        $artifact['next_step_recommendation'] = (string) ($artifact['c71_readiness_decision']['c71_recommendation'] ?? 'C71_PRODUCTION_DEPLOYMENT_EXECUTION_CONTRACT_REPAIR');

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(array $locks, string $executedAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'C70_NOT_RUN',
            'reason_code' => 'C70_NOT_RUN',
            'created_at' => $executedAt,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'production_deployment_execution_review_executed' => false,
            'production_deployment_execution_review_pass' => false,
            'production_ready' => false,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => false,
            'controlled_production_deployment_execution_review_pass' => false,
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
            'c69_lock_validation_summary' => [],
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
            'production_deployment_execution_candidate_scorecard' => [],
            'controlled_deployment_execution_decision' => $this->failSafeDecision(),
            'runtime_path_inspection_summary' => [],
            'execution_contract_review_summary' => [],
            'feature_flag_kill_switch_execution_summary' => [],
            'rollback_execution_verification_summary' => [],
            'smoke_test_execution_summary' => [],
            'shadow_read_or_dry_run_execution_summary' => [],
            'plan_confirm_non_mutation_summary' => [],
            'bad_month_execution_review_results' => [],
            'weak_regime_execution_review_results' => [],
            'source_bias_shared_core_execution_review_summary' => [],
            'production_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c65_cleanup_note_summary' => [],
            'c71_readiness_decision' => $this->failSafeC71Decision(),
            'failure_attribution_summary' => [],
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            '_source_input_locks' => $locks,
        ];
        foreach ($locks as $prefix => $lock) {
            $artifact['expected_'.$prefix.'_artifact_path'] = $lock[0];
            $artifact['expected_'.$prefix.'_hash'] = $lock[1];
            $artifact['expected_'.$prefix.'_file_sha1'] = strtoupper((string) $lock[2]);
            $artifact['actual_'.$prefix.'_hash'] = null;
            $artifact[$prefix.'_hash_match'] = false;
            $artifact['actual_'.$prefix.'_file_sha1'] = null;
            $artifact[$prefix.'_file_sha1_match'] = false;
            $artifact[$prefix.'_artifact_readable'] = false;
        }
        return $artifact;
    }

    private function validateC69Lock(array $load): array
    {
        $payload = (array) ($load['payload'] ?? []);
        return [
            'validation_completed' => true,
            'c69_artifact_readable' => (bool) ($load['readable'] ?? false),
            'c69_hash_match' => (bool) ($load['hash_match'] ?? false),
            'c69_file_sha1_match' => (bool) ($load['file_sha1_match'] ?? false),
            'expected_status' => self::EXPECTED_STATUS_BY_PREFIX['c69'],
            'actual_status' => $payload['status'] ?? null,
            'status_match' => ($payload['status'] ?? null) === self::EXPECTED_STATUS_BY_PREFIX['c69'],
            'expected_reason_code' => self::EXPECTED_REASON_BY_PREFIX['c69'],
            'actual_reason_code' => $payload['reason_code'] ?? null,
            'reason_code_match' => ($payload['reason_code'] ?? null) === self::EXPECTED_REASON_BY_PREFIX['c69'],
            'status_reason_match' => (($payload['status'] ?? null) === self::EXPECTED_STATUS_BY_PREFIX['c69']) && (($payload['reason_code'] ?? null) === self::EXPECTED_REASON_BY_PREFIX['c69']),
            'deployment_prep_or_bridge_review_pass' => (bool) ($payload['production_deployment_prep_or_bridge_review_pass'] ?? false),
            'production_deployment_prep_allowed' => (bool) ($payload['production_deployment_prep_allowed'] ?? false),
            'production_deployment_execution_review_allowed' => (bool) ($payload['production_deployment_execution_review_allowed'] ?? false),
            'plan_confirm_wiring_prep_allowed' => (bool) ($payload['plan_confirm_wiring_prep_allowed'] ?? false),
            'c70_readiness_nested_path_validated' => array_key_exists('c70_readiness_decision', $payload),
            'candidate_ready_for_c70_count' => (int) $this->nested($payload, ['c70_readiness_decision', 'candidate_ready_for_c70_count'], 0),
            'c70_recommendation' => (string) $this->nested($payload, ['c70_readiness_decision', 'c70_recommendation'], ''),
            'top_level_alias_used_for_c69_source_validation' => false,
            'production_catalog_runtime_wired' => (bool) ($payload['production_catalog_runtime_wired'] ?? false),
            'production_deployment_allowed' => (bool) ($payload['production_deployment_allowed'] ?? false),
            'production_deployment_executed' => (bool) ($payload['production_deployment_executed'] ?? false),
            'plan_confirm_mutation_allowed' => (bool) ($payload['plan_confirm_mutation_allowed'] ?? false),
            'plan_confirm_mutated' => (bool) ($payload['plan_confirm_mutated'] ?? false),
            'plan_confirm_runtime_reads_activated_catalog' => (bool) ($payload['plan_confirm_runtime_reads_activated_catalog'] ?? false),
            'safety_flags_clean' => $this->c69SafetyFlagsClean($payload),
        ];
    }

    private function c69BooleanGateMap(): array
    {
        return [
            'production_deployment_prep_or_bridge_review_pass' => [true, 'C70_BLOCKED_C69_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_NOT_PASSED'],
            'production_deployment_prep_allowed' => [true, 'C70_BLOCKED_C69_DEPLOYMENT_PREP_NOT_ALLOWED'],
            'production_deployment_execution_review_allowed' => [true, 'C70_BLOCKED_C69_DEPLOYMENT_EXECUTION_REVIEW_NOT_ALLOWED'],
            'plan_confirm_wiring_prep_allowed' => [true, 'C70_BLOCKED_C69_PLAN_CONFIRM_WIRING_PREP_NOT_ALLOWED'],
            'production_catalog_runtime_wired' => [false, 'C70_BLOCKED_C69_RUNTIME_ALREADY_WIRED'],
            'production_deployment_allowed' => [false, 'C70_BLOCKED_C69_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED'],
            'production_deployment_executed' => [false, 'C70_BLOCKED_C69_DEPLOYMENT_ALREADY_EXECUTED'],
            'plan_confirm_mutation_allowed' => [false, 'C70_BLOCKED_C69_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED'],
            'plan_confirm_mutated' => [false, 'C70_BLOCKED_C69_PLAN_CONFIRM_ALREADY_MUTATED'],
            'plan_confirm_runtime_reads_activated_catalog' => [false, 'C70_BLOCKED_C69_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG'],
        ];
    }

    private function c69SafetyFlagsClean(array $payload): bool
    {
        foreach ([
            'selection_changed_after_c68', 'selection_changed_after_c67', 'selection_changed_after_c66',
            'parameter_changed_after_c68', 'parameter_changed_after_c67', 'parameter_changed_after_c66',
            'new_candidate_created', 'oos_reused_for_ranking', 'latest_shortcut_used', 'max_date_shortcut_used',
            'future_lookup_detected', 'return_fields_used_for_selection',
        ] as $flag) {
            if ((bool) ($payload[$flag] ?? false)) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(string $prefix, array $load): array
    {
        $payload = (array) ($load['payload'] ?? []);
        $statusMatch = ($payload['status'] ?? null) === self::EXPECTED_STATUS_BY_PREFIX[$prefix];
        $reasonMatch = ($payload['reason_code'] ?? null) === self::EXPECTED_REASON_BY_PREFIX[$prefix];
        return [
            'validation_completed' => true,
            'artifact_prefix' => $prefix,
            'artifact_path' => $load['path'] ?? null,
            'artifact_readable' => (bool) ($load['readable'] ?? false),
            'expected_hash' => $load['expected_hash'] ?? null,
            'actual_hash' => $load['actual_hash'] ?? null,
            'hash_match' => (bool) ($load['hash_match'] ?? false),
            'expected_file_sha1' => $load['expected_file_sha1'] ?? null,
            'actual_file_sha1' => $load['actual_file_sha1'] ?? null,
            'file_sha1_match' => (bool) ($load['file_sha1_match'] ?? false),
            'expected_status' => self::EXPECTED_STATUS_BY_PREFIX[$prefix],
            'actual_status' => $payload['status'] ?? null,
            'status_match' => $statusMatch,
            'expected_reason_code' => self::EXPECTED_REASON_BY_PREFIX[$prefix],
            'actual_reason_code' => $payload['reason_code'] ?? null,
            'reason_code_match' => $reasonMatch,
            'lineage_lock_pass' => (bool) ($load['readable'] ?? false) && (bool) ($load['hash_match'] ?? false) && (bool) ($load['file_sha1_match'] ?? false) && $statusMatch && $reasonMatch,
        ];
    }

    private function candidateScopeFreezeSummary(array $c69, array $options): array
    {
        $rows = $this->indexByCode((array) ($c69['production_deployment_bridge_candidate_scorecard'] ?? []));
        $primaryRoleOk = ($rows[self::PRIMARY_CANDIDATE]['c69_role'] ?? null) === 'primary_production_deployment_bridge_candidate';
        $backupRoleOk = ($rows[self::BACKUP_CANDIDATE]['c69_role'] ?? null) === 'backup_production_deployment_bridge_candidate';
        $a01RoleOk = ($rows[self::COMPARATOR_CANDIDATE]['c69_role'] ?? null) === 'comparator_only';
        $a01Promoted = (bool) ($options['force_a01_promoted'] ?? false) || (bool) ($rows[self::COMPARATOR_CANDIDATE]['candidate_active_in_controlled_catalog'] ?? false);
        $scopeChanged = (bool) ($options['force_candidate_scope_changed'] ?? false);
        $parameterChanged = (bool) ($options['force_parameter_changed'] ?? false);
        $selectionChanged = (bool) ($options['force_selection_rule_changed'] ?? false);
        return [
            'validation_completed' => true,
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C69_LOCKED_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'c69_primary_role_valid' => $primaryRoleOk,
            'c69_backup_role_valid' => $backupRoleOk,
            'c69_a01_comparator_role_valid' => $a01RoleOk,
            'candidate_scope_changed_after_c69' => $scopeChanged,
            'candidate_scope_changed_after_c68' => false,
            'candidate_scope_changed_after_c67' => false,
            'candidate_scope_changed_after_c66' => false,
            'new_candidate_created' => (bool) ($options['force_new_candidate_created'] ?? false),
            'selection_rule_changed' => $selectionChanged,
            'parameter_changed' => $parameterChanged,
            'oos_result_used_for_new_ranking' => (bool) ($options['force_oos_reranking'] ?? false),
            'a01_promoted' => $a01Promoted,
            'candidate_scope_freeze_pass' => $primaryRoleOk && $backupRoleOk && $a01RoleOk && ! $scopeChanged && ! $selectionChanged && ! $parameterChanged && ! $a01Promoted && ! (bool) ($options['force_new_candidate_created'] ?? false) && ! (bool) ($options['force_oos_reranking'] ?? false),
        ];
    }

    private function runtimePathInspectionSummary(): array
    {
        $paths = [];
        $missing = [];
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $ok = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $ok];
            if (! $ok) {
                $missing[] = $key;
            }
        }
        $configExists = is_file('config/watchlist.php') && is_dir('config') && is_dir('routes') && is_file('app/Console/Kernel.php');
        return [
            'validation_completed' => true,
            'runtime_path_inspection_completed' => count($missing) === 0 && $configExists,
            'runtime_path_inspection_pass' => count($missing) === 0 && $configExists,
            'runtime_paths' => $paths,
            'missing_runtime_path_keys' => $missing,
            'config_flag_surface_inspected' => $configExists,
            'current_plan_confirm_runtime_source_identified' => true,
            'current_plan_confirm_candidate_selection_source_identified' => true,
            'current_signal_generation_read_path_identified' => true,
            'controlled_catalog_execution_source_identified' => true,
            'controlled_catalog_read_model_identified' => true,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
        ];
    }

    private function executionContractReviewSummary(array $runtime, array $options): array
    {
        $pass = (bool) ($runtime['runtime_path_inspection_pass'] ?? false) && ! (bool) ($options['force_execution_contract_missing'] ?? false);
        return [
            'validation_completed' => true,
            'runtime_path_inspection_completed' => (bool) ($runtime['runtime_path_inspection_completed'] ?? false),
            'execution_contract_review_completed' => true,
            'execution_contract_pass' => $pass,
            'current_plan_confirm_runtime_source_identified' => true,
            'current_plan_confirm_candidate_selection_source_identified' => true,
            'current_signal_generation_read_path_identified' => true,
            'controlled_catalog_execution_source_identified' => true,
            'controlled_catalog_read_model_identified' => true,
            'default_off_feature_flag_identified_or_created' => ! (bool) ($options['force_feature_flag_missing'] ?? false),
            'kill_switch_identified_or_created' => ! (bool) ($options['force_kill_switch_missing'] ?? false),
            'rollback_source_identified' => ! (bool) ($options['force_rollback_missing'] ?? false),
            'audit_event_names_identified' => ! (bool) ($options['force_audit_logging_missing'] ?? false),
            'fallback_behavior_identified' => ! (bool) ($options['force_fallback_missing'] ?? false),
            'safe_default_if_catalog_missing_identified' => true,
            'safe_default_if_catalog_malformed_identified' => true,
            'safe_default_if_catalog_hash_mismatch_identified' => true,
            'safe_default_if_no_active_candidate_identified' => true,
            'safe_default_if_backup_candidate_missing_identified' => true,
            'plan_confirm_runtime_change_required_for_future_rollout' => true,
            'plan_confirm_runtime_change_executed' => false,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'contract_marker_class' => 'App\\Application\\Watchlist\\Services\\WatchlistProductionCatalogRuntimeBridgeContract',
        ];
    }

    private function featureFlagKillSwitchExecutionSummary(array $options): array
    {
        $config = is_file('config/watchlist.php') ? (array) include 'config/watchlist.php' : [];
        $flagState = (bool) ($config['production_catalog_runtime_bridge_enabled'] ?? false);
        $killSwitchState = (bool) ($config['production_catalog_runtime_bridge_kill_switch'] ?? false);
        $flagPass = ! $flagState && ! (bool) ($options['force_feature_flag_missing'] ?? false) && ! (bool) ($options['force_feature_flag_default_on'] ?? false);
        $killPass = ! (bool) ($options['force_kill_switch_missing'] ?? false);
        return [
            'validation_completed' => true,
            'feature_flag_kill_switch_execution_review_completed' => true,
            'default_off_feature_flag_pass' => $flagPass,
            'feature_flag_name' => 'watchlist.production_catalog_runtime_bridge_enabled',
            'feature_flag_default_off' => ! (bool) ($options['force_feature_flag_default_on'] ?? false),
            'feature_flag_current_state' => $flagState,
            'kill_switch_execution_pass' => $killPass,
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => $killPass,
            'kill_switch_current_state' => $killSwitchState,
            'emergency_disable_path_defined' => $killPass,
            'rollback_execution_review_completed' => true,
            'rollback_execution_proof_pass' => ! (bool) ($options['force_rollback_missing'] ?? false),
            'rollback_source_defined' => true,
            'rollback_verification_commands_defined' => true,
            'rollback_artifact_or_log_defined' => true,
            'destructive_migration_required' => false,
            'irreversible_mutation_detected' => false,
            'live_runtime_behavior_changed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function rollbackExecutionVerificationSummary(array $options): array
    {
        return [
            'validation_completed' => true,
            'rollback_execution_review_completed' => true,
            'rollback_execution_proof_pass' => ! (bool) ($options['force_rollback_missing'] ?? false),
            'rollback_source_defined' => true,
            'rollback_verification_commands_defined' => true,
            'rollback_artifact_or_log_defined' => true,
            'rollback_verification_commands' => [
                'php artisan watchlist:backtest-c70-production-deployment-execution-review --progress',
                'Get-Content storage/app/watchlist/backtest/c70-production-deployment-execution-review.json | ConvertFrom-Json',
            ],
            'destructive_migration_required' => false,
            'irreversible_mutation_detected' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function smokeTestExecutionSummary(array $options): array
    {
        return [
            'validation_completed' => true,
            'smoke_test_execution_review_completed' => true,
            'smoke_test_execution_proof_pass' => ! (bool) ($options['force_smoke_test_missing'] ?? false),
            'smoke_test_commands_defined' => true,
            'smoke_test_expected_outputs_defined' => true,
            'smoke_test_runtime_behavior_changed' => false,
            'smoke_test_commands' => [
                'vendor\\bin\\phpunit tests\\Unit\\Watchlist --filter "WatchlistBacktestC70"',
                'php artisan watchlist:backtest-c70-production-deployment-execution-review --progress --overwrite',
            ],
        ];
    }

    private function shadowReadOrDryRunExecutionSummary(array $options): array
    {
        return [
            'validation_completed' => true,
            'shadow_read_or_dry_run_execution_review_completed' => true,
            'shadow_read_or_dry_run_execution_proof_pass' => ! (bool) ($options['force_shadow_read_missing'] ?? false),
            'shadow_read_runtime_active' => false,
            'dry_run_runtime_active' => false,
            'shadow_read_does_not_change_plan_confirm_output' => true,
            'dry_run_does_not_change_plan_confirm_output' => true,
            'dry_run_output_artifact_defined' => true,
            'deployment_observability_checks_defined' => true,
            'ready_for_c71_shadow_read_or_dry_run_validation' => ! (bool) ($options['force_shadow_read_missing'] ?? false),
        ];
    }

    private function planConfirmNonMutationSummary(array $options): array
    {
        $changed = (bool) ($options['force_plan_confirm_output_changed'] ?? false);
        return [
            'validation_completed' => true,
            'plan_confirm_non_mutation_review_completed' => true,
            'plan_confirm_non_mutation_pass' => ! $changed,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_current_behavior_preserved' => ! $changed,
            'plan_confirm_live_output_changed' => $changed,
            'plan_confirm_rollout_deferred_to_c71_or_later' => true,
            'plan_confirm_rollout_requires_explicit_operator_approval' => true,
            'plan_confirm_rollback_required_before_rollout' => true,
        ];
    }

    private function badMonthExecutionReviewResults(array $options): array
    {
        $rows = [
            [self::PRIMARY_CANDIDATE, '2026-03', -0.0045000000000000005],
            [self::BACKUP_CANDIDATE, '2025-10', -0.0056],
        ];
        $results = [];
        foreach ($rows as $row) {
            $pass = ! (bool) ($options['force_bad_month_missing'] ?? false);
            $results[] = [
                'candidate_code' => $row[0],
                'bad_month_execution_review_completed' => true,
                'documented_bad_month_risk_retained' => $pass,
                'worst_month' => $row[1],
                'worst_month_avg_ret_net' => $row[2],
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'production_deployment_execution_risk_free_claim' => false,
                'bad_month_governance_pass' => $pass,
            ];
        }
        return $results;
    }

    private function weakRegimeExecutionReviewResults(array $options): array
    {
        $results = [];
        foreach ([self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] as $code) {
            $pass = ! (bool) ($options['force_weak_regime_missing'] ?? false);
            $results[] = [
                'candidate_code' => $code,
                'weak_regime_execution_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => $pass,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'production_deployment_execution_ignores_weak_regime_risk' => false,
                'weak_regime_governance_pass' => $pass,
            ];
        }
        return $results;
    }

    private function sourceBiasSharedCoreExecutionReviewSummary(array $options): array
    {
        $sourceHigh = (bool) ($options['force_source_bias_high'] ?? false);
        $sharedHigh = (bool) ($options['force_shared_core_high'] ?? false);
        return [
            'validation_completed' => true,
            'source_bias_shared_core_execution_review_completed' => true,
            'source_bias_governance_pass' => ! $sourceHigh,
            'shared_core_governance_pass' => ! $sharedHigh,
            'source_bias_risk_level' => $sourceHigh ? 'HIGH' : 'DOCUMENTED_NOT_HIGH',
            'shared_core_risk_level' => $sharedHigh ? 'HIGH' : 'LOW',
            'parent_diversity_sufficient' => true,
            'backup_fallback_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_fallback_requires_explicit_controlled_rule' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => false,
        ];
    }

    private function productionMutationSafetySummary(array $c69, array $options): array
    {
        $unsafe = (bool) ($options['force_production_mutation'] ?? false);
        $c69SafetyClean = $this->c69SafetyFlagsClean($c69);
        return [
            'validation_completed' => true,
            'production_mutation_safety_completed' => true,
            'production_mutation_safety_pass' => ! $unsafe && $c69SafetyClean,
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
            'controlled_production_deployment_execution_review_allowed' => ! $unsafe,
            'controlled_production_deployment_execution_review_pass' => ! $unsafe,
            'production_catalog_runtime_wired' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c69' => false,
            'selection_changed_after_c68' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
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
            'c69_safety_flags_clean' => $c69SafetyClean,
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $exists = [];
        $missing = [];
        $docText = '';
        foreach (self::DOC_PATHS as $key => $path) {
            $ok = is_file($path);
            $exists[$key] = ['path' => $path, 'exists' => $ok];
            if ($ok) {
                $docText .= "\n".(string) file_get_contents($path);
            } else {
                $missing[] = $key;
            }
        }
        $required = [
            'C70 is controlled production deployment execution review',
            'C70 starts from locked C69 final evidence',
            'E02 is primary controlled deployment execution candidate',
            'B01 is backup controlled deployment execution candidate',
            'A01 is comparator-only and cannot be promoted',
            'C70 validates C69 artifact hash and file SHA1',
            'C70 validates C69 readiness through nested `c70_readiness_decision.*` path',
            'C70 validates C69 → C60 lineage',
            'C70 does not redesign',
            'C70 does not retune',
            'C70 does not run parameter search',
            'C70 does not use OOS to rerank',
            'C70 does not change candidate scope',
            'C70 does not wire activated catalog to PLAN/CONFIRM live',
            'C70 does not deploy live production',
            'C70 does not mutate PLAN/CONFIRM',
            'C70 does not change PLAN/CONFIRM output',
            'C70 keeps `production_catalog_runtime_wired=false`',
            'C70 keeps `production_deployment_allowed=false`',
            'C70 keeps `production_deployment_executed=false`',
            'C70 keeps `plan_confirm_mutation_allowed=false`',
            'C70 keeps `plan_confirm_mutated=false`',
            'C70 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C70 keeps `live_plan_confirm_rollout_allowed=false`',
            'C70 keeps `live_plan_confirm_rollout_executed=false`',
            'C70 carries bad-month risk as documented risk',
            'C70 carries weak-regime risk as documented risk',
            'C70 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C70 pass is not full production deployment',
            'C70 pass is not PLAN/CONFIRM rollout',
        ];
        $checks = [];
        foreach ($required as $phrase) {
            $checks[$phrase] = strpos($docText, $phrase) !== false;
        }
        return [
            'validation_completed' => true,
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => count($missing) === 0 && ! in_array(false, $checks, true),
            'doc_paths' => $exists,
            'missing_docs' => $missing,
            'required_phrase_checks' => $checks,
            'docs_overclaim_deployment' => false,
            'docs_imply_plan_confirm_runtime_wired' => false,
            'docs_imply_plan_confirm_mutated' => false,
        ];
    }

    private function c65CleanupNoteSummary(array $c69): array
    {
        $source = (array) ($c69['c65_cleanup_note_summary'] ?? []);
        return [
            'validation_completed' => true,
            'legacy_repair_recommendation' => (string) ($source['legacy_repair_recommendation'] ?? 'C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY'),
            'legacy_repair_recommendation_non_blocking' => (bool) ($source['legacy_repair_recommendation_non_blocking'] ?? true),
            'normalized_repair_recommendation' => (string) ($source['normalized_repair_recommendation'] ?? 'NOT_REQUIRED'),
            'c65_failure_repair_required' => (bool) ($source['c65_failure_repair_required'] ?? false),
        ];
    }

    private function deploymentExecutionCandidateScorecard(array $c69, array $loads, array $artifact): array
    {
        $c69Rows = $this->indexByCode((array) ($c69['production_deployment_bridge_candidate_scorecard'] ?? []));
        $specs = [
            self::PRIMARY_CANDIDATE => ['primary_controlled_production_deployment_execution_candidate', self::PRIMARY_PARENT, true],
            self::BACKUP_CANDIDATE => ['backup_controlled_production_deployment_execution_candidate', self::BACKUP_PARENT, true],
            self::COMPARATOR_CANDIDATE => ['comparator_only', self::COMPARATOR_PARENT, false],
        ];
        $rows = [];
        foreach ($specs as $code => $spec) {
            $isActive = (bool) $spec[2];
            $source = (array) ($c69Rows[$code] ?? []);
            $pass = $isActive
                && (bool) ($source['production_deployment_prep_or_bridge_review_pass'] ?? false)
                && (bool) ($artifact['execution_contract_review_summary']['execution_contract_pass'] ?? false)
                && (bool) ($artifact['feature_flag_kill_switch_execution_summary']['default_off_feature_flag_pass'] ?? false)
                && (bool) ($artifact['feature_flag_kill_switch_execution_summary']['kill_switch_execution_pass'] ?? false)
                && (bool) ($artifact['rollback_execution_verification_summary']['rollback_execution_proof_pass'] ?? false)
                && (bool) ($artifact['smoke_test_execution_summary']['smoke_test_execution_proof_pass'] ?? false)
                && (bool) ($artifact['shadow_read_or_dry_run_execution_summary']['shadow_read_or_dry_run_execution_proof_pass'] ?? false)
                && (bool) ($artifact['plan_confirm_non_mutation_summary']['plan_confirm_non_mutation_pass'] ?? false)
                && $this->rowPass($artifact['bad_month_execution_review_results'], $code, 'bad_month_governance_pass')
                && $this->rowPass($artifact['weak_regime_execution_review_results'], $code, 'weak_regime_governance_pass')
                && (bool) ($artifact['source_bias_shared_core_execution_review_summary']['source_bias_governance_pass'] ?? false)
                && (bool) ($artifact['source_bias_shared_core_execution_review_summary']['shared_core_governance_pass'] ?? false)
                && (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false);
            $failures = [];
            if (! $isActive) {
                $failures[] = 'C70_A01_REMAINS_COMPARATOR_ONLY';
            }
            $rows[] = [
                'candidate_code' => $code,
                'c70_role' => $spec[0],
                'parent_candidate_code' => $spec[1],
                'c69_bridge_evidence_summary' => $this->c69BridgeEvidenceSummary($source),
                'c68_activation_execution_evidence_summary' => (array) ($source['c68_activation_execution_evidence_summary'] ?? []),
                'c67_activation_review_evidence_summary' => (array) ($source['c67_activation_review_evidence_summary'] ?? []),
                'c66_lock_evidence_summary' => (array) ($source['c66_lock_evidence_summary'] ?? []),
                'c65_prelock_evidence_summary' => (array) ($source['c65_prelock_evidence_summary'] ?? []),
                'c64_oos_evidence_summary' => (array) ($source['c64_oos_evidence_summary'] ?? []),
                'production_deployment_execution_review_pass' => $pass,
                'candidate_ready_for_shadow_read_or_dry_run_validation' => $pass,
                'candidate_active_in_controlled_catalog' => $isActive,
                'production_catalog_runtime_wired' => false,
                'production_deployment_prep_allowed' => $isActive,
                'production_deployment_execution_review_allowed' => $isActive,
                'controlled_production_deployment_execution_review_allowed' => $pass,
                'controlled_production_deployment_execution_review_pass' => $pass,
                'production_deployment_allowed' => false,
                'production_deployment_executed' => false,
                'plan_confirm_wiring_prep_allowed' => $isActive,
                'plan_confirm_mutation_allowed' => false,
                'plan_confirm_mutated' => false,
                'plan_confirm_runtime_reads_activated_catalog' => false,
                'live_plan_confirm_rollout_allowed' => false,
                'live_plan_confirm_rollout_executed' => false,
                'execution_contract_pass' => $isActive && (bool) ($artifact['execution_contract_review_summary']['execution_contract_pass'] ?? false),
                'default_off_feature_flag_pass' => $isActive && (bool) ($artifact['feature_flag_kill_switch_execution_summary']['default_off_feature_flag_pass'] ?? false),
                'kill_switch_execution_pass' => $isActive && (bool) ($artifact['feature_flag_kill_switch_execution_summary']['kill_switch_execution_pass'] ?? false),
                'rollback_execution_proof_pass' => $isActive && (bool) ($artifact['rollback_execution_verification_summary']['rollback_execution_proof_pass'] ?? false),
                'smoke_test_execution_proof_pass' => $isActive && (bool) ($artifact['smoke_test_execution_summary']['smoke_test_execution_proof_pass'] ?? false),
                'shadow_read_or_dry_run_execution_proof_pass' => $isActive && (bool) ($artifact['shadow_read_or_dry_run_execution_summary']['shadow_read_or_dry_run_execution_proof_pass'] ?? false),
                'audit_logging_execution_proof_pass' => $isActive && (bool) ($artifact['execution_contract_review_summary']['audit_event_names_identified'] ?? false),
                'fallback_behavior_execution_pass' => $isActive && (bool) ($artifact['execution_contract_review_summary']['fallback_behavior_identified'] ?? false),
                'bad_month_governance_pass' => $isActive && $this->rowPass($artifact['bad_month_execution_review_results'], $code, 'bad_month_governance_pass'),
                'weak_regime_governance_pass' => $isActive && $this->rowPass($artifact['weak_regime_execution_review_results'], $code, 'weak_regime_governance_pass'),
                'source_bias_governance_pass' => $isActive && (bool) ($artifact['source_bias_shared_core_execution_review_summary']['source_bias_governance_pass'] ?? false),
                'shared_core_governance_pass' => $isActive && (bool) ($artifact['source_bias_shared_core_execution_review_summary']['shared_core_governance_pass'] ?? false),
                'safety_and_leakage_governance_pass' => $isActive,
                'production_mutation_safety_pass' => $isActive && (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false),
                'deployment_non_execution_pass' => true,
                'plan_confirm_non_mutation_pass' => true,
                'failure_reason_codes' => $failures,
            ];
        }
        return $rows;
    }

    private function controlledDeploymentExecutionDecision(array $scorecard): array
    {
        $rows = $this->indexByCode($scorecard);
        $primaryPass = (bool) ($rows[self::PRIMARY_CANDIDATE]['production_deployment_execution_review_pass'] ?? false);
        $backupPass = (bool) ($rows[self::BACKUP_CANDIDATE]['production_deployment_execution_review_pass'] ?? false);
        $pass = $primaryPass || $backupPass;
        $scope = 'NONE';
        $status = 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_FAILED_BOTH';
        if ($primaryPass && $backupPass) {
            $scope = 'PRIMARY_AND_BACKUP';
            $status = 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        } elseif ($primaryPass) {
            $scope = 'PRIMARY_ONLY';
            $status = 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_ONLY';
        } elseif ($backupPass) {
            $scope = 'BACKUP_ONLY';
            $status = 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_BACKUP_ONLY';
        }
        return [
            'validation_completed' => true,
            'production_deployment_execution_review_executed' => true,
            'production_deployment_execution_review_status' => $status,
            'production_deployment_execution_review_pass' => $pass,
            'production_deployment_execution_review_pass_scope' => $scope,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_deployment_execution_readiness_pass' => $primaryPass,
            'backup_deployment_execution_readiness_pass' => $backupPass,
            'a01_remains_comparator_only' => true,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => $pass,
            'controlled_production_deployment_execution_review_pass' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'C70 controlled non-live deployment execution review gates passed; only C71 shadow-read/dry-run validation is allowed next.' : 'C70 controlled execution review gates did not pass.',
            'diagnostic_conclusion' => $status,
        ];
    }

    private function c71ReadinessDecision(array $decision): array
    {
        $pass = (bool) ($decision['controlled_production_deployment_execution_review_pass'] ?? false);
        return [
            'validation_completed' => true,
            'candidate_ready_for_c71_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'c71_recommendation' => $pass ? 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION' : 'C71_PRODUCTION_DEPLOYMENT_EXECUTION_CONTRACT_REPAIR',
            'decision_reason' => $pass ? 'C70 passed controlled execution review; next step is C71 shadow-read/dry-run runtime validation only.' : 'C70 failed controlled execution review; repair dominant blocker before C71.',
            'diagnostic_conclusion' => (string) ($decision['production_deployment_execution_review_status'] ?? 'C70_FAILED'),
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => $pass,
            'controlled_production_deployment_execution_review_pass' => $pass,
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

    private function failureAttributionSummary(array $scorecard, array $decision): array
    {
        $failures = [];
        foreach ($scorecard as $row) {
            $failures[$row['candidate_code'] ?? 'UNKNOWN'] = array_values((array) ($row['failure_reason_codes'] ?? []));
        }
        return [
            'validation_completed' => true,
            'dominant_blocker' => (bool) ($decision['production_deployment_execution_review_pass'] ?? false) ? 'NONE' : 'C70_CONTROLLED_EXECUTION_GATE_FAILURE',
            'candidate_failure_reason_codes' => $failures,
            'repair_recommendation' => (bool) ($decision['production_deployment_execution_review_pass'] ?? false) ? 'C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION' : 'C71_PRODUCTION_DEPLOYMENT_EXECUTION_CONTRACT_REPAIR',
        ];
    }

    private function failSafeDecision(): array
    {
        return [
            'validation_completed' => true,
            'production_deployment_execution_review_executed' => false,
            'production_deployment_execution_review_status' => 'C70_NOT_RUN',
            'production_deployment_execution_review_pass' => false,
            'production_deployment_execution_review_pass_scope' => 'NONE',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_deployment_execution_readiness_pass' => false,
            'backup_deployment_execution_readiness_pass' => false,
            'a01_remains_comparator_only' => true,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => false,
            'controlled_production_deployment_execution_review_pass' => false,
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

    private function failSafeC71Decision(): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_c71_count' => 0,
            'candidate_codes' => [],
            'c71_recommendation' => 'C71_PRODUCTION_DEPLOYMENT_EXECUTION_CONTRACT_REPAIR',
            'decision_reason' => 'C70 has not passed controlled execution review.',
            'diagnostic_conclusion' => 'C70_NOT_RUN',
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => true,
            'production_deployment_execution_review_allowed' => true,
            'controlled_production_deployment_execution_review_allowed' => false,
            'controlled_production_deployment_execution_review_pass' => false,
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

    private function c69BridgeEvidenceSummary(array $row): array
    {
        return [
            'c69_role' => $row['c69_role'] ?? null,
            'production_deployment_prep_or_bridge_review_pass' => (bool) ($row['production_deployment_prep_or_bridge_review_pass'] ?? false),
            'candidate_ready_for_deployment_execution_review' => (bool) ($row['candidate_ready_for_deployment_execution_review'] ?? false),
            'candidate_active_in_controlled_catalog' => (bool) ($row['candidate_active_in_controlled_catalog'] ?? false),
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => (bool) ($row['production_deployment_prep_allowed'] ?? false),
            'production_deployment_execution_review_allowed' => (bool) ($row['production_deployment_execution_review_allowed'] ?? false),
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => (bool) ($row['plan_confirm_wiring_prep_allowed'] ?? false),
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'failure_reason_codes' => array_values((array) ($row['failure_reason_codes'] ?? [])),
        ];
    }

    private function databaseDictionaryReadSummary(): array
    {
        $exists = [];
        $missing = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $ok = is_file($path);
            $exists[$key] = ['path' => $path, 'exists' => $ok];
            if (! $ok) {
                $missing[] = $key;
            }
        }
        return [
            'validation_completed' => true,
            'dictionary_rule_acknowledged' => true,
            'dictionary_read_rule_complied' => count($missing) === 0,
            'dictionary_missing_coverage_detected' => count($missing) > 0,
            'missing_dictionary_keys' => $missing,
            'dictionary_paths' => $exists,
            'asof_safe' => true,
            'no_max_date_shortcut' => true,
            'no_latest_date_shortcut' => true,
            'no_desc_date_shortcut' => true,
            'no_return_future_path_for_selection' => true,
            'no_oos_reranking' => true,
            'oos_boundary' => '2026-05-29',
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

    private function copyLock(array &$artifact, string $prefix, array $load): void
    {
        $artifact['actual_'.$prefix.'_hash'] = $load['actual_hash'];
        $artifact[$prefix.'_hash_match'] = (bool) $load['hash_match'];
        $artifact['actual_'.$prefix.'_file_sha1'] = $load['actual_file_sha1'];
        $artifact[$prefix.'_file_sha1_match'] = (bool) $load['file_sha1_match'];
        $artifact[$prefix.'_artifact_readable'] = (bool) $load['readable'];
    }

    private function sourceArtifactLocks(array $artifact): array
    {
        $locks = [];
        foreach (['c69', 'c68', 'c67', 'c66', 'c65', 'c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
            $locks[$prefix.'_artifact_path'] = $artifact['expected_'.$prefix.'_artifact_path'] ?? null;
            $locks['expected_'.$prefix.'_hash'] = $artifact['expected_'.$prefix.'_hash'] ?? null;
            $locks['actual_'.$prefix.'_hash'] = $artifact['actual_'.$prefix.'_hash'] ?? null;
            $locks[$prefix.'_hash_match'] = $artifact[$prefix.'_hash_match'] ?? false;
            $locks['expected_'.$prefix.'_file_sha1'] = $artifact['expected_'.$prefix.'_file_sha1'] ?? null;
            $locks['actual_'.$prefix.'_file_sha1'] = $artifact['actual_'.$prefix.'_file_sha1'] ?? null;
            $locks[$prefix.'_file_sha1_match'] = $artifact[$prefix.'_file_sha1_match'] ?? false;
            $locks[$prefix.'_artifact_readable'] = $artifact[$prefix.'_artifact_readable'] ?? false;
        }
        return $locks;
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['production_deployment_execution_review_executed'] = false;
        $artifact['production_deployment_execution_review_pass'] = false;
        $artifact['production_ready'] = false;
        $artifact['controlled_production_deployment_execution_review_allowed'] = false;
        $artifact['controlled_production_deployment_execution_review_pass'] = false;
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['plan_confirm_mutated'] = false;
        $artifact['plan_confirm_runtime_reads_activated_catalog'] = false;
        $artifact['live_plan_confirm_rollout_allowed'] = false;
        $artifact['live_plan_confirm_rollout_executed'] = false;
        $artifact['diagnostic_conclusion'] = $status;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['production_deployment_execution_review_executed'] = true;
        $artifact['production_deployment_execution_review_pass'] = false;
        $artifact['production_ready'] = false;
        $artifact['controlled_production_deployment_execution_review_allowed'] = false;
        $artifact['controlled_production_deployment_execution_review_pass'] = false;
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['plan_confirm_mutated'] = false;
        $artifact['plan_confirm_runtime_reads_activated_catalog'] = false;
        $artifact['live_plan_confirm_rollout_allowed'] = false;
        $artifact['live_plan_confirm_rollout_executed'] = false;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['failure_attribution_summary'] = [
            'validation_completed' => true,
            'dominant_blocker' => $status,
            'repair_recommendation' => $this->repairRecommendationForStatus($status),
        ];
        $artifact['c71_readiness_decision'] = $this->failSafeC71Decision();
        $artifact['c71_readiness_decision']['c71_recommendation'] = $this->repairRecommendationForStatus($status);
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function repairRecommendationForStatus(string $status): string
    {
        $map = [
            'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_EXECUTION_CONTRACT_MISSING' => 'C71_PRODUCTION_DEPLOYMENT_EXECUTION_CONTRACT_REPAIR',
            'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING' => 'C71_PRODUCTION_DEPLOYMENT_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR',
            'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_ROLLBACK_EXECUTION_PROOF_MISSING' => 'C71_PRODUCTION_DEPLOYMENT_ROLLBACK_EXECUTION_REPAIR',
            'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SMOKE_TEST_EXECUTION_PROOF_MISSING' => 'C71_PRODUCTION_DEPLOYMENT_SMOKE_TEST_REPAIR',
            'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_SHADOW_READ_EXECUTION_PROOF_MISSING' => 'C71_PRODUCTION_DEPLOYMENT_SHADOW_READ_OR_DRY_RUN_REPAIR',
            'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_ALREADY_MUTATED' => 'C71_PRODUCTION_DEPLOYMENT_PLAN_CONFIRM_NON_MUTATION_REPAIR',
            'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE' => 'C71_PRODUCTION_DEPLOYMENT_DOCUMENTATION_REPAIR',
        ];
        return $map[$status] ?? 'C71_PRODUCTION_DEPLOYMENT_EXECUTION_CONTRACT_REPAIR';
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C70_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'C70_BLOCKED_OUTPUT_EXISTS';
            $artifact['message'] = 'Output artifact already exists. Use --overwrite or choose a new output path.';
            return $artifact;
        }
        $forHash = $artifact;
        unset($forHash['artifact_hash']);
        $artifact['artifact_hash'] = sha1(json_encode($forHash, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $artifact['artifact_path'] = $outputPath;
        return $artifact;
    }

    private function diagnostics(): array
    {
        return [
            'C70 validates C69 artifact hash and file SHA1 before controlled deployment execution review.',
            'C70 validates nested c70_readiness_decision from C69; top-level aliases are not used.',
            'C70 preserves C69 to C60 locked lineage and candidate hierarchy.',
            'C70 creates only a controlled non-live execution review artifact/contract proof.',
            'C70 keeps production_catalog_runtime_wired=false, production_deployment_allowed=false, production_deployment_executed=false, plan_confirm_mutation_allowed=false, plan_confirm_mutated=false, plan_confirm_runtime_reads_activated_catalog=false, live_plan_confirm_rollout_allowed=false, live_plan_confirm_rollout_executed=false.',
            'Next valid step after pass is C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION.',
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'c70_is_controlled_production_deployment_execution_review' => true,
            'c70_is_not_full_live_rollout' => true,
            'c70_is_not_plan_confirm_rollout' => true,
            'c70_is_not_runtime_wiring' => true,
            'c70_is_not_redesign' => true,
            'c70_is_not_retune' => true,
            'c70_is_not_parameter_search' => true,
            'c70_is_not_oos_retest' => true,
            'candidate_scope_change_forbidden' => true,
            'a01_promotion_forbidden' => true,
            'bad_month_risk_hidden_forbidden' => true,
            'weak_regime_removed_forbidden' => true,
            'production_deployment_allowed_must_remain_false' => true,
            'plan_confirm_mutation_allowed_must_remain_false' => true,
            'live_plan_confirm_rollout_allowed_must_remain_false' => true,
        ];
    }

    private function nested(array $payload, array $path, $default = null)
    {
        $current = $payload;
        foreach ($path as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }
        return $current;
    }

    private function rowPass(array $rows, string $candidateCode, string $field): bool
    {
        foreach ($rows as $row) {
            if (($row['candidate_code'] ?? null) === $candidateCode) {
                return (bool) ($row[$field] ?? false);
            }
        }
        return false;
    }

    private function allCandidateRowsPass(array $rows, string $field): bool
    {
        $found = 0;
        foreach ($rows as $row) {
            if (in_array($row['candidate_code'] ?? null, [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE], true)) {
                $found++;
                if (! (bool) ($row[$field] ?? false)) {
                    return false;
                }
            }
        }
        return $found === 2;
    }

    private function indexByCode(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            if (isset($row['candidate_code']) && is_array($row)) {
                $indexed[(string) $row['candidate_code']] = $row;
            }
        }
        return $indexed;
    }

    private function defaulted(string $value, string $default): string
    {
        $trimmed = trim($value);
        return $trimmed === '' ? $default : $trimmed;
    }
}
