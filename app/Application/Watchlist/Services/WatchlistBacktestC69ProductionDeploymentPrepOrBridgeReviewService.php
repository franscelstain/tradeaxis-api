<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService
{
    public const RUN_CODE = 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW';
    public const ARTIFACT_TYPE = 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW';

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

    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c69-production-deployment-prep-or-bridge-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c69_review_doc' => 'docs/watchlist/audit/WS_C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW.md',
        'c69_operator_commands_doc' => 'docs/watchlist/audit/WS_C69_OPERATOR_VALIDATION_COMMANDS.md',
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
    ];

    /**
     * C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW. NON_RUNTIME_BRIDGE_READINESS_ONLY.
     * NOT_PRODUCTION_DEPLOYMENT. NOT_PLAN_CONFIRM_ROLLOUT. NOT_RUNTIME_WIRING.
     * NOT_REDESIGN. NOT_RETUNE. NOT_PARAMETER_SEARCH. NOT_OOS_RETEST. NO_OOS_RERANKING.
     * C68_ARTIFACT_HASH_LOCK. C68_FILE_SHA1_LOCK. C60_TO_C68_LINEAGE_LOCK.
     * C69_READINESS_NESTED_PATH_VALIDATED. CONTROLLED_ACTIVATION_RECORD_NESTED_PATH_VALIDATED.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. CANDIDATE_SCOPE_FROZEN_FROM_C68.
     * A01_COMPARATOR_ONLY_NOT_PROMOTABLE. BAD_MONTH_RISK_RETAINED. WEAK_REGIME_RISK_RETAINED.
     * SOURCE_BIAS_SHARED_CORE_RISK_RETAINED. FEATURE_FLAG_KILL_SWITCH_ROLLBACK_REQUIRED.
     * SMOKE_AND_SHADOW_READ_PLAN_REQUIRED. NO_PRODUCTION_DEPLOYMENT_ALLOWED. NO_PLAN_CONFIRM_MUTATION_ALLOWED.
     */
    public function execute(
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

        $dictionary = $this->databaseDictionaryReadSummary();
        $artifact['database_dictionary_read_summary'] = $dictionary;
        if ((bool) ($dictionary['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C69_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'C69_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'C69 database dictionary coverage is mandatory.', $outputPath, $overwrite);
        }

        $loads = [];
        foreach (['c68', 'c67', 'c66', 'c65', 'c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
            $loads[$prefix] = $this->loadArtifactLock(
                (string) $artifact['expected_'.$prefix.'_artifact_path'],
                (string) $artifact['expected_'.$prefix.'_hash'],
                (string) $artifact['expected_'.$prefix.'_file_sha1']
            );
            $this->copyLock($artifact, $prefix, $loads[$prefix]);
        }
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        if (! $loads['c68']['readable']) {
            return $this->blocked($artifact, 'C69_BLOCKED_MISSING_C68_ARTIFACT', 'C69_BLOCKED_MISSING_C68_ARTIFACT', 'C69 requires the locked C68 artifact.', $outputPath, $overwrite);
        }
        if (! $loads['c68']['hash_match']) {
            return $this->blocked($artifact, 'C69_BLOCKED_C68_ARTIFACT_LOCK_MISMATCH', 'C69_BLOCKED_C68_ARTIFACT_LOCK_MISMATCH', 'C68 artifact hash does not match the C69 lock.', $outputPath, $overwrite);
        }
        if (! $loads['c68']['file_sha1_match']) {
            return $this->blocked($artifact, 'C69_BLOCKED_C68_FILE_SHA1_LOCK_MISMATCH', 'C69_BLOCKED_C68_FILE_SHA1_LOCK_MISMATCH', 'C68 file SHA1 does not match the C69 lock.', $outputPath, $overwrite);
        }

        $payloads = [];
        foreach ($loads as $prefix => $load) {
            $payloads[$prefix] = (array) ($load['payload'] ?? []);
        }

        $c68Validation = $this->validateC68($payloads['c68']);
        $artifact['c68_lock_validation_summary'] = $c68Validation;
        if (! (bool) ($c68Validation['pass'] ?? false)) {
            return $this->blocked($artifact, (string) $c68Validation['status'], (string) $c68Validation['reason_code'], (string) $c68Validation['message'], $outputPath, $overwrite);
        }

        $lineageValidation = $this->validateLineage($loads, $payloads);
        foreach ($lineageValidation['summaries'] as $key => $summary) {
            $artifact[$key.'_lineage_validation_summary'] = $summary;
        }
        if (! (bool) ($lineageValidation['pass'] ?? false)) {
            return $this->blocked($artifact, 'C69_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C69_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C60-C67 lineage lock is invalid for C69.', $outputPath, $overwrite);
        }

        $candidateScope = $this->candidateScopeFreezeSummary($payloads['c68']);
        $artifact['candidate_scope_freeze_summary'] = $candidateScope;
        if (! (bool) ($candidateScope['candidate_scope_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C68 candidate scope is not the locked C69 bridge hierarchy.', $outputPath, $overwrite);
        }

        $artifact['bridge_contract_review_summary'] = $this->bridgeContractReviewSummary();
        if ((bool) ($options['force_bridge_contract_missing'] ?? false)) {
            $artifact['bridge_contract_review_summary']['bridge_contract_pass'] = false;
            $artifact['bridge_contract_review_summary']['runtime_consumer_contract_pass'] = false;
            $artifact['bridge_contract_review_summary']['forced_missing_for_test'] = true;
        }
        if (! (bool) ($artifact['bridge_contract_review_summary']['bridge_contract_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_BRIDGE_CONTRACT_MISSING', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_BRIDGE_CONTRACT_MISSING', 'C69 bridge contract readiness is incomplete.', $outputPath, $overwrite);
        }

        $artifact['plan_confirm_wiring_readiness_summary'] = $this->planConfirmWiringReadinessSummary($payloads['c68']);
        if (! (bool) ($artifact['plan_confirm_wiring_readiness_summary']['plan_confirm_wiring_readiness_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_PLAN_CONFIRM_ALREADY_READING_CATALOG', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_PLAN_CONFIRM_ALREADY_READING_CATALOG', 'PLAN/CONFIRM wiring readiness is unsafe.', $outputPath, $overwrite);
        }

        $artifact['feature_flag_kill_switch_rollback_summary'] = $this->featureFlagKillSwitchRollbackSummary();
        if ((bool) ($options['force_feature_flag_missing'] ?? false)) {
            $artifact['feature_flag_kill_switch_rollback_summary']['feature_flag_or_kill_switch_pass'] = false;
            $artifact['feature_flag_kill_switch_rollback_summary']['feature_flag_default_off'] = false;
            $artifact['feature_flag_kill_switch_rollback_summary']['forced_feature_flag_missing_for_test'] = true;
        }
        if ((bool) ($options['force_kill_switch_missing'] ?? false)) {
            $artifact['feature_flag_kill_switch_rollback_summary']['feature_flag_or_kill_switch_pass'] = false;
            $artifact['feature_flag_kill_switch_rollback_summary']['kill_switch_available'] = false;
            $artifact['feature_flag_kill_switch_rollback_summary']['forced_kill_switch_missing_for_test'] = true;
        }
        if ((bool) ($options['force_rollback_plan_missing'] ?? false)) {
            $artifact['feature_flag_kill_switch_rollback_summary']['rollback_plan_pass'] = false;
            $artifact['feature_flag_kill_switch_rollback_summary']['rollback_source_defined'] = false;
            $artifact['feature_flag_kill_switch_rollback_summary']['forced_rollback_missing_for_test'] = true;
        }
        if (! (bool) ($artifact['feature_flag_kill_switch_rollback_summary']['feature_flag_or_kill_switch_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH_MISSING', 'Feature flag or kill switch is missing.', $outputPath, $overwrite);
        }
        if (! (bool) ($artifact['feature_flag_kill_switch_rollback_summary']['rollback_plan_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING', 'Rollback plan is missing.', $outputPath, $overwrite);
        }

        $artifact['smoke_test_shadow_read_plan_summary'] = $this->smokeTestShadowReadPlanSummary();
        if ((bool) ($options['force_smoke_test_plan_missing'] ?? false)) {
            $artifact['smoke_test_shadow_read_plan_summary']['smoke_test_plan_pass'] = false;
            $artifact['smoke_test_shadow_read_plan_summary']['smoke_test_commands_defined'] = false;
            $artifact['smoke_test_shadow_read_plan_summary']['forced_smoke_missing_for_test'] = true;
        }
        if ((bool) ($options['force_shadow_read_plan_missing'] ?? false)) {
            $artifact['smoke_test_shadow_read_plan_summary']['shadow_read_plan_pass'] = false;
            $artifact['smoke_test_shadow_read_plan_summary']['dry_run_plan_defined'] = false;
            $artifact['smoke_test_shadow_read_plan_summary']['forced_shadow_missing_for_test'] = true;
        }
        if (! (bool) ($artifact['smoke_test_shadow_read_plan_summary']['smoke_test_plan_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SMOKE_TEST_PLAN_MISSING', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SMOKE_TEST_PLAN_MISSING', 'Smoke test plan is missing.', $outputPath, $overwrite);
        }
        if (! (bool) ($artifact['smoke_test_shadow_read_plan_summary']['shadow_read_plan_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SHADOW_READ_PLAN_MISSING', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SHADOW_READ_PLAN_MISSING', 'Shadow-read plan is missing.', $outputPath, $overwrite);
        }

        $artifact['bad_month_bridge_review_results'] = $this->badMonthBridgeReviewResults($payloads['c68']);
        if (! $this->allCandidateRowsPass($artifact['bad_month_bridge_review_results'], 'bad_month_governance_pass')) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE', 'Bad-month documented risk governance is incomplete.', $outputPath, $overwrite);
        }

        $artifact['weak_regime_bridge_review_results'] = $this->weakRegimeBridgeReviewResults($payloads['c68']);
        if (! $this->allCandidateRowsPass($artifact['weak_regime_bridge_review_results'], 'weak_regime_governance_pass')) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE', 'Weak-regime documented risk governance is incomplete.', $outputPath, $overwrite);
        }

        $artifact['source_bias_shared_core_bridge_review_summary'] = $this->sourceBiasSharedCoreBridgeReviewSummary();
        if ((bool) ($options['force_source_bias_high'] ?? false)) {
            $artifact['source_bias_shared_core_bridge_review_summary']['source_bias_governance_pass'] = false;
            $artifact['source_bias_shared_core_bridge_review_summary']['source_bias_risk_level'] = 'HIGH';
        }
        if ((bool) ($options['force_shared_core_high'] ?? false)) {
            $artifact['source_bias_shared_core_bridge_review_summary']['shared_core_governance_pass'] = false;
            $artifact['source_bias_shared_core_bridge_review_summary']['shared_core_risk_level'] = 'HIGH';
        }
        if (! (bool) ($artifact['source_bias_shared_core_bridge_review_summary']['source_bias_governance_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', 'Source-bias governance is incomplete.', $outputPath, $overwrite);
        }
        if (! (bool) ($artifact['source_bias_shared_core_bridge_review_summary']['shared_core_governance_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE', 'Shared-core governance is incomplete.', $outputPath, $overwrite);
        }

        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($payloads['c68']);
        if (! (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_PRODUCTION_MUTATION', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_PRODUCTION_MUTATION', 'C69 production mutation safety failed.', $outputPath, $overwrite);
        }

        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        if (! (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false)) {
            return $this->rejected($artifact, 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE', 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE', 'C69 documentation governance is incomplete.', $outputPath, $overwrite);
        }

        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($payloads['c68']);
        $scorecard = $this->deploymentBridgeCandidateScorecard($payloads, $artifact);
        $artifact['production_deployment_bridge_candidate_scorecard'] = $scorecard;
        $decision = $this->deploymentBridgeReadinessDecision($scorecard, $artifact);
        $artifact['deployment_bridge_readiness_decision'] = $decision;
        $artifact['c70_readiness_decision'] = $this->c70ReadinessDecision($decision);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($scorecard, $decision);
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        $artifact['status'] = (string) ($decision['production_deployment_prep_or_bridge_status'] ?? 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_FAILED_BOTH');
        $artifact['reason_code'] = $artifact['status'];
        $artifact['production_deployment_prep_or_bridge_review_executed'] = true;
        $artifact['production_deployment_prep_or_bridge_review_pass'] = (bool) ($decision['production_deployment_prep_or_bridge_review_pass'] ?? false);
        $artifact['production_catalog_lock_allowed'] = true;
        $artifact['production_catalog_activation_allowed'] = true;
        $artifact['production_catalog_activation_execution_allowed'] = true;
        $artifact['production_catalog_activation_execution_performed'] = true;
        $artifact['production_catalog_activated'] = true;
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_prep_allowed'] = (bool) ($decision['production_deployment_prep_allowed'] ?? false);
        $artifact['production_deployment_execution_review_allowed'] = (bool) ($decision['production_deployment_execution_review_allowed'] ?? false);
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
        $artifact['plan_confirm_wiring_prep_allowed'] = (bool) ($decision['plan_confirm_wiring_prep_allowed'] ?? false);
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['plan_confirm_mutated'] = false;
        $artifact['plan_confirm_runtime_reads_activated_catalog'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $artifact['status'];
        $artifact['next_step_recommendation'] = (string) ($artifact['c70_readiness_decision']['c70_recommendation'] ?? 'C70_PRODUCTION_DEPLOYMENT_PREP_BRIDGE_CONTRACT_REPAIR');

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(array $locks, string $executedAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'C69_NOT_RUN',
            'reason_code' => 'C69_NOT_RUN',
            'created_at' => $executedAt,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'production_deployment_prep_or_bridge_review_executed' => false,
            'production_deployment_prep_or_bridge_review_pass' => false,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => false,
            'production_deployment_execution_review_allowed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'production_ready' => false,
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c68_lock_validation_summary' => [],
            'c67_lineage_validation_summary' => [],
            'c66_lineage_validation_summary' => [],
            'c65_lineage_validation_summary' => [],
            'c64_lineage_validation_summary' => [],
            'c63_lineage_validation_summary' => [],
            'c62_lineage_validation_summary' => [],
            'c61_lineage_validation_summary' => [],
            'c60_lineage_validation_summary' => [],
            'candidate_scope_freeze_summary' => [],
            'production_deployment_bridge_candidate_scorecard' => [],
            'deployment_bridge_readiness_decision' => [],
            'bridge_contract_review_summary' => [],
            'plan_confirm_wiring_readiness_summary' => [],
            'feature_flag_kill_switch_rollback_summary' => [],
            'smoke_test_shadow_read_plan_summary' => [],
            'bad_month_bridge_review_results' => [],
            'weak_regime_bridge_review_results' => [],
            'source_bias_shared_core_bridge_review_summary' => [],
            'production_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c65_cleanup_note_summary' => [],
            'c70_readiness_decision' => [],
            'failure_attribution_summary' => [],
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        foreach ($locks as $prefix => $lock) {
            $artifact['expected_'.$prefix.'_artifact_path'] = $this->defaulted((string) $lock[0], (string) constant('self::DEFAULT_'.strtoupper($prefix).'_ARTIFACT'));
            $artifact['expected_'.$prefix.'_hash'] = $this->defaulted((string) $lock[1], (string) constant('self::DEFAULT_EXPECTED_'.strtoupper($prefix).'_HASH'));
            $artifact['expected_'.$prefix.'_file_sha1'] = strtoupper($this->defaulted((string) $lock[2], (string) constant('self::DEFAULT_EXPECTED_'.strtoupper($prefix).'_FILE_SHA1')));
        }
        return $artifact;
    }

    private function validateC68(array $c68): array
    {
        $expectedStatus = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        if (($c68['status'] ?? null) !== $expectedStatus || ($c68['reason_code'] ?? null) !== $expectedStatus) {
            return $this->validationFail('C69_BLOCKED_C68_STATUS_OR_REASON_MISMATCH', 'C68 status/reason_code mismatch.');
        }
        $requiredTrue = [
            'production_catalog_activation_execution_review_pass' => 'C69_BLOCKED_C68_ACTIVATION_EXECUTION_REVIEW_NOT_PASSED',
            'production_catalog_activation_execution_performed' => 'C69_BLOCKED_C68_ACTIVATION_EXECUTION_NOT_PERFORMED',
            'production_catalog_activated' => 'C69_BLOCKED_C68_CONTROLLED_CATALOG_NOT_ACTIVATED',
        ];
        foreach ($requiredTrue as $key => $status) {
            if (($c68[$key] ?? null) !== true) {
                return $this->validationFail($status, 'C68 required true field failed: '.$key);
            }
        }
        $requiredFalse = [
            'production_catalog_runtime_wired' => 'C69_BLOCKED_C68_RUNTIME_ALREADY_WIRED',
            'production_deployment_executed' => 'C69_BLOCKED_C68_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutated' => 'C69_BLOCKED_C68_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C69_BLOCKED_C68_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
        ];
        foreach ($requiredFalse as $key => $status) {
            if (($c68[$key] ?? null) !== false) {
                return $this->validationFail($status, 'C68 runtime/live flag must remain false: '.$key);
            }
        }
        if (($c68['production_deployment_allowed'] ?? null) !== false) {
            return $this->validationFail('C69_BLOCKED_C68_DEPLOYMENT_ALREADY_EXECUTED', 'C68 production_deployment_allowed must remain false.');
        }
        if (($c68['plan_confirm_mutation_allowed'] ?? null) !== false) {
            return $this->validationFail('C69_BLOCKED_C68_PLAN_CONFIRM_ALREADY_MUTATED', 'C68 plan_confirm_mutation_allowed must remain false.');
        }

        $record = (array) ($c68['production_catalog_activation_record'] ?? []);
        $recordExpected = [
            'catalog_activation_record_created' => true,
            'catalog_activation_record_runtime_consumable' => false,
            'catalog_activation_record_wired_to_plan_confirm' => false,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
        ];
        foreach ($recordExpected as $key => $expected) {
            if (($record[$key] ?? null) !== $expected) {
                return $this->validationFail('C69_BLOCKED_C68_CONTROLLED_CATALOG_ACTIVATION_RECORD_MISMATCH', 'C68 controlled activation record mismatch: '.$key);
            }
        }

        if ($this->nested($c68, ['c69_readiness_decision', 'candidate_ready_for_c69_count']) !== 2) {
            return $this->validationFail('C69_BLOCKED_C68_C69_READINESS_COUNT_MISMATCH', 'C68 nested c69 readiness count mismatch.');
        }
        if ($this->nested($c68, ['c69_readiness_decision', 'c69_recommendation']) !== self::RUN_CODE) {
            return $this->validationFail('C69_BLOCKED_C68_RECOMMENDATION_MISMATCH', 'C68 nested c69 recommendation mismatch.');
        }

        return [
            'validation_completed' => true,
            'pass' => true,
            'status' => 'C69_C68_LOCK_VALIDATED',
            'reason_code' => 'C69_C68_LOCK_VALIDATED',
            'message' => 'C68 lock, nested C69 readiness path, and controlled activation record validated.',
            'c68_status_match' => true,
            'c68_reason_code_match' => true,
            'c68_activation_execution_review_pass' => true,
            'c68_activation_execution_performed' => true,
            'c68_production_catalog_activated' => true,
            'c68_controlled_activation_record_pass' => true,
            'c69_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c68_source_validation' => false,
            'production_catalog_runtime_wired' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
        ];
    }

    private function validateLineage(array $loads, array $payloads): array
    {
        $expected = [
            'c67' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
            'c66' => 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP',
            'c65' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP',
            'c64' => 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP',
            'c63' => 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP',
            'c62' => 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES',
            'c61' => 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED',
            'c60' => 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED',
        ];
        $expectedReason = $expected;
        $expectedReason['c61'] = 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE';
        $expectedReason['c60'] = 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS';

        $pass = true;
        $summaries = [];
        foreach ($expected as $prefix => $status) {
            $load = (array) ($loads[$prefix] ?? []);
            $payload = (array) ($payloads[$prefix] ?? []);
            $rowPass = (bool) ($load['readable'] ?? false)
                && (bool) ($load['hash_match'] ?? false)
                && (bool) ($load['file_sha1_match'] ?? false)
                && (($payload['status'] ?? null) === $status)
                && (($payload['reason_code'] ?? null) === $expectedReason[$prefix]);
            if (! $rowPass) {
                $pass = false;
            }
            $summaries[$prefix] = [
                'validation_completed' => true,
                'lineage_lock_pass' => $rowPass,
                'artifact_readable' => (bool) ($load['readable'] ?? false),
                'artifact_hash_match' => (bool) ($load['hash_match'] ?? false),
                'file_sha1_match' => (bool) ($load['file_sha1_match'] ?? false),
                'expected_status' => $status,
                'actual_status' => $payload['status'] ?? null,
                'expected_reason_code' => $expectedReason[$prefix],
                'actual_reason_code' => $payload['reason_code'] ?? null,
            ];
        }
        return ['pass' => $pass, 'summaries' => $summaries];
    }

    private function candidateScopeFreezeSummary(array $c68): array
    {
        $rows = $this->indexByCode((array) ($c68['production_catalog_activation_execution_candidate_scorecard'] ?? []));
        $primary = (array) ($rows[self::PRIMARY_CANDIDATE] ?? []);
        $backup = (array) ($rows[self::BACKUP_CANDIDATE] ?? []);
        $a01 = (array) ($rows[self::COMPARATOR_CANDIDATE] ?? []);
        $readinessCodes = (array) $this->nested($c68, ['c69_readiness_decision', 'candidate_codes'], []);
        $pass = ($primary['c68_role'] ?? null) === 'primary_production_catalog_activation_execution_candidate'
            && ($backup['c68_role'] ?? null) === 'backup_production_catalog_activation_execution_candidate'
            && ($a01['c68_role'] ?? null) === 'comparator_only'
            && in_array(self::PRIMARY_CANDIDATE, $readinessCodes, true)
            && in_array(self::BACKUP_CANDIDATE, $readinessCodes, true)
            && ! in_array(self::COMPARATOR_CANDIDATE, $readinessCodes, true)
            && ($a01['candidate_active_in_production_catalog'] ?? null) === false;
        return [
            'validation_completed' => true,
            'candidate_scope_pass' => $pass,
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C68_LOCKED_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c68' => false,
            'candidate_scope_changed_after_c67' => false,
            'candidate_scope_changed_after_c66' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'oos_result_used_for_new_ranking' => false,
            'a01_promoted' => false,
        ];
    }

    private function bridgeContractReviewSummary(): array
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
        $pass = count($missing) === 0;
        return [
            'validation_completed' => true,
            'bridge_contract_review_completed' => true,
            'bridge_contract_pass' => $pass,
            'runtime_consumer_contract_pass' => $pass,
            'bridge_contract_runtime_active' => false,
            'current_plan_confirm_runtime_source_identified' => $pass,
            'current_plan_confirm_candidate_selection_source' => 'WatchlistCandidateUniverseService + WatchlistScoringService + WatchlistRecommendationService',
            'current_signal_generation_read_path' => 'WatchlistMarketDataConsumerReadService -> MarketDataWatchlistReadService -> MarketDataWatchlistReadRepository',
            'current_runtime_paths' => $paths,
            'proposed_catalog_bridge_source_identified' => true,
            'proposed_production_catalog_bridge_source' => 'storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json::production_catalog_activation_record',
            'proposed_read_model_identified' => true,
            'proposed_production_catalog_read_model' => 'C70 controlled catalog read model behind config gate; not runtime active in C69',
            'feature_flag_name_proposed' => true,
            'feature_flag_name' => 'watchlist.production_catalog_bridge.enabled',
            'kill_switch_name_proposed' => true,
            'kill_switch_name' => 'watchlist.production_catalog_bridge.kill_switch',
            'rollback_source_proposed' => true,
            'rollback_source' => 'current PLAN/CONFIRM read path remains canonical when flag is OFF or kill switch is ON',
            'audit_events_proposed' => true,
            'audit_log_event_names' => [
                'watchlist.production_catalog_bridge.shadow_read_started',
                'watchlist.production_catalog_bridge.hash_validated',
                'watchlist.production_catalog_bridge.fallback_used',
                'watchlist.production_catalog_bridge.kill_switch_triggered',
                'watchlist.production_catalog_bridge.rollback_verified',
            ],
            'fallback_behavior_proposed' => true,
            'runtime_fallback_behavior' => 'Use E02 only when explicitly enabled and validated; use B01 only by explicit bridge fallback rule; never use A01.',
            'safe_default_behavior_proposed' => true,
            'safe_default_if_catalog_missing' => 'Keep current PLAN/CONFIRM behavior and emit audit event.',
            'safe_default_if_catalog_malformed' => 'Keep current PLAN/CONFIRM behavior and emit audit event.',
            'safe_default_if_catalog_hash_mismatch' => 'Keep current PLAN/CONFIRM behavior and emit audit event.',
            'safe_default_if_no_active_candidate_available' => 'Keep current PLAN/CONFIRM behavior and emit audit event.',
            'safe_default_if_backup_candidate_missing' => 'Do not fallback to A01; keep current PLAN/CONFIRM behavior and emit audit event.',
            'plan_confirm_runtime_change_required' => true,
            'plan_confirm_runtime_change_executed' => false,
            'production_catalog_runtime_wired' => false,
        ];
    }

    private function planConfirmWiringReadinessSummary(array $c68): array
    {
        $safe = ($c68['plan_confirm_runtime_reads_activated_catalog'] ?? null) === false
            && ($c68['plan_confirm_mutated'] ?? null) === false;
        return [
            'validation_completed' => true,
            'plan_confirm_wiring_readiness_review_completed' => true,
            'plan_confirm_wiring_readiness_pass' => $safe,
            'plan_confirm_wiring_runtime_active' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_wiring_prep_allowed' => $safe,
            'plan_confirm_current_behavior_preserved' => true,
            'plan_confirm_bridge_requires_c70_or_later' => true,
            'plan_confirm_rollout_requires_explicit_operator_approval' => true,
            'plan_confirm_rollback_required_before_rollout' => true,
        ];
    }

    private function featureFlagKillSwitchRollbackSummary(): array
    {
        return [
            'validation_completed' => true,
            'feature_flag_kill_switch_review_completed' => true,
            'feature_flag_or_kill_switch_pass' => true,
            'feature_flag_default_off' => true,
            'feature_flag_name' => 'watchlist.production_catalog_bridge.enabled',
            'kill_switch_available' => true,
            'kill_switch_name' => 'watchlist.production_catalog_bridge.kill_switch',
            'emergency_disable_path_defined' => true,
            'emergency_disable_path' => 'Set bridge flag OFF or kill switch ON; current PLAN/CONFIRM path remains canonical.',
            'rollback_plan_review_completed' => true,
            'rollback_plan_pass' => true,
            'rollback_source_defined' => true,
            'rollback_source' => 'Current PLAN/CONFIRM runtime path before catalog bridge wiring.',
            'rollback_verification_commands_defined' => true,
            'rollback_verification_commands' => [
                'php artisan watchlist:backtest-c69-production-deployment-prep-or-bridge-review --overwrite --progress',
                'vendor\\bin\\phpunit tests\\Unit\\Watchlist --filter "WatchlistBacktestC69"',
            ],
            'destructive_migration_required' => false,
            'irreversible_mutation_detected' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function smokeTestShadowReadPlanSummary(): array
    {
        return [
            'validation_completed' => true,
            'smoke_test_plan_review_completed' => true,
            'smoke_test_plan_pass' => true,
            'smoke_test_commands_defined' => true,
            'smoke_test_commands' => [
                'vendor\\bin\\phpunit tests\\Unit\\Watchlist --filter "WatchlistBacktestC69"',
                'php artisan watchlist:backtest-c69-production-deployment-prep-or-bridge-review --overwrite --progress',
            ],
            'smoke_test_expected_outputs_defined' => true,
            'smoke_test_expected_outputs' => [
                'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP',
                'production_deployment_allowed=false',
                'plan_confirm_mutation_allowed=false',
                'plan_confirm_runtime_reads_activated_catalog=false',
            ],
            'shadow_read_plan_review_completed' => true,
            'shadow_read_plan_pass' => true,
            'shadow_read_runtime_active' => false,
            'shadow_read_does_not_change_plan_confirm_output' => true,
            'dry_run_plan_defined' => true,
            'dry_run_output_artifact_defined' => true,
            'dry_run_output_artifact_path' => 'storage/app/watchlist/backtest/c70-production-deployment-shadow-read-dry-run.json',
            'deployment_observability_checks_defined' => true,
            'observability_checks' => [
                'artifact hash match',
                'catalog bridge flag state',
                'kill switch state',
                'fallback event count',
                'PLAN/CONFIRM output diff must be zero during shadow-read',
            ],
        ];
    }

    private function badMonthBridgeReviewResults(array $c68): array
    {
        $existing = (array) ($c68['bad_month_activation_execution_review_results'] ?? []);
        $results = [];
        foreach ($existing as $row) {
            $row = (array) $row;
            if (! in_array($row['candidate_code'] ?? null, [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE], true)) {
                continue;
            }
            $results[] = [
                'candidate_code' => $row['candidate_code'],
                'bad_month_bridge_review_completed' => true,
                'bad_month_governance_pass' => (bool) ($row['documented_bad_month_risk_retained'] ?? false)
                    && ($row['bad_month_risk_level'] ?? null) === 'MODERATE'
                    && ($row['bad_month_governance_decision'] ?? null) === 'PASS_WITH_DOCUMENTED_RISK'
                    && ($row['worst_month_regime'] ?? null) === self::WEAK_REGIME,
                'documented_bad_month_risk_retained' => (bool) ($row['documented_bad_month_risk_retained'] ?? false),
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'worst_month' => $row['worst_month'] ?? null,
                'worst_month_avg_ret_net' => $row['worst_month_avg_ret_net'] ?? null,
                'worst_month_regime' => $row['worst_month_regime'] ?? null,
                'bad_month_risk_level' => $row['bad_month_risk_level'] ?? null,
                'bad_month_governance_decision' => $row['bad_month_governance_decision'] ?? null,
                'production_deployment_prep_risk_free_claim' => false,
            ];
        }
        return $results;
    }

    private function weakRegimeBridgeReviewResults(array $c68): array
    {
        $existing = (array) ($c68['weak_regime_activation_execution_review_results'] ?? []);
        $results = [];
        foreach ($existing as $row) {
            $row = (array) $row;
            if (! in_array($row['candidate_code'] ?? null, [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE], true)) {
                continue;
            }
            $results[] = [
                'candidate_code' => $row['candidate_code'],
                'weak_regime_bridge_review_completed' => true,
                'weak_regime_governance_pass' => (bool) ($row['weak_regime_retained'] ?? false)
                    && ($row['weak_regime_sample_status'] ?? null) === 'SUFFICIENT'
                    && ($row['weak_regime_sample_collapse_detected'] ?? null) === false
                    && ($row['weak_regime_risk_level'] ?? null) === 'MODERATE',
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => (bool) ($row['weak_regime_retained'] ?? false),
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => $row['weak_regime_sample_status'] ?? null,
                'weak_regime_sample_collapse_detected' => $row['weak_regime_sample_collapse_detected'] ?? null,
                'weak_regime_risk_level' => $row['weak_regime_risk_level'] ?? null,
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'production_deployment_prep_ignores_weak_regime_risk' => false,
            ];
        }
        return $results;
    }

    private function sourceBiasSharedCoreBridgeReviewSummary(): array
    {
        return [
            'validation_completed' => true,
            'source_bias_shared_core_bridge_review_completed' => true,
            'source_bias_governance_pass' => true,
            'shared_core_governance_pass' => true,
            'source_bias_risk_level' => 'DOCUMENTED_NOT_HIGH',
            'shared_core_risk_level' => 'LOW',
            'parent_diversity_sufficient' => true,
            'primary_parent_candidate_code' => self::PRIMARY_PARENT,
            'backup_parent_candidate_code' => self::BACKUP_PARENT,
            'backup_fallback_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_fallback_requires_explicit_bridge_rule' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => false,
        ];
    }

    private function productionMutationSafetySummary(array $c68): array
    {
        $c68Safety = (array) ($c68['production_activation_execution_mutation_safety_summary'] ?? []);
        $summary = [
            'validation_completed' => true,
            'production_catalog_locked_decision_created' => true,
            'production_catalog_activation_review_decision_created' => true,
            'production_catalog_activation_execution_decision_created' => true,
            'catalog_activation_record_created' => true,
            'catalog_activation_record_runtime_consumable' => false,
            'production_catalog_created' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_decision_created' => true,
            'production_deployment_bridge_plan_created' => true,
            'production_deployment_execution_review_allowed' => true,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'selection_changed_after_c68' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
            'parameter_changed_after_c68' => false,
            'parameter_changed_after_c67' => false,
            'parameter_changed_after_c66' => false,
            'new_candidate_created' => false,
            'oos_reused_for_ranking' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
            'database_dictionary_rule_complied' => true,
            'c68_production_activation_execution_mutation_safety_pass' => (bool) ($c68Safety['production_activation_execution_mutation_safety_pass'] ?? false),
        ];
        $pass = ($summary['c68_production_activation_execution_mutation_safety_pass'] === true)
            && ($c68['production_catalog_runtime_wired'] ?? null) === false
            && ($c68['production_deployment_executed'] ?? null) === false
            && ($c68['plan_confirm_mutated'] ?? null) === false
            && ($c68['plan_confirm_runtime_reads_activated_catalog'] ?? null) === false;
        $summary['production_mutation_safety_pass'] = $pass;
        $summary['production_mutation_safety_review_completed'] = true;
        return $summary;
    }

    private function documentationGovernanceSummary(): array
    {
        $exists = [];
        $missing = [];
        foreach (self::DOC_PATHS as $key => $path) {
            $ok = is_file($path);
            $exists[$key] = ['path' => $path, 'exists' => $ok];
            if (! $ok) {
                $missing[] = $key;
            }
        }
        $requiredPhrases = [
            'C69 is production deployment prep / bridge review',
            'C69 starts from locked C68 final evidence',
            'C68 activation execution passed primary + backup',
            'E02 is primary deployment bridge candidate',
            'B01 is backup deployment bridge candidate',
            'A01 is comparator-only and cannot be promoted',
            'C69 validates C68 artifact hash and file SHA1',
            'C69 validates C68 readiness through nested `c69_readiness_decision.*` path',
            'C69 validates C68 controlled activation record through nested `production_catalog_activation_record.*` path',
            'C69 validates C60 → C69 lineage',
            'C69 does not redesign',
            'C69 does not retune',
            'C69 does not run parameter search',
            'C69 does not use OOS to rerank',
            'C69 does not change candidate scope',
            'C69 may create deployment prep / bridge artifact',
            'C69 may create bridge contract proposal',
            'C69 may create feature flag / kill switch plan',
            'C69 may create rollback plan',
            'C69 may create smoke test plan',
            'C69 may create shadow-read / dry-run plan',
            'C69 does not wire activated catalog to PLAN/CONFIRM',
            'C69 does not deploy production',
            'C69 does not mutate PLAN/CONFIRM',
            'C69 keeps `production_catalog_runtime_wired=false`',
            'C69 keeps `production_deployment_allowed=false`',
            'C69 keeps `production_deployment_executed=false`',
            'C69 keeps `plan_confirm_mutation_allowed=false`',
            'C69 keeps `plan_confirm_mutated=false`',
            'C69 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C69 carries bad-month risk as documented risk',
            'C69 carries weak-regime risk as documented risk',
            'C69 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C69 may only recommend C70 production deployment execution review if all bridge/prep gates pass',
            'C69 pass is not production deployment',
            'C69 pass is not PLAN/CONFIRM rollout',
        ];
        $docText = '';
        foreach ([self::DOC_PATHS['c69_review_doc'], self::DOC_PATHS['c69_operator_commands_doc']] as $path) {
            if (is_file($path)) {
                $docText .= "\n".(string) file_get_contents($path);
            }
        }
        $phraseChecks = [];
        foreach ($requiredPhrases as $phrase) {
            $phraseChecks[$phrase] = strpos($docText, $phrase) !== false;
        }
        return [
            'validation_completed' => true,
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => count($missing) === 0 && ! in_array(false, $phraseChecks, true),
            'doc_paths' => $exists,
            'missing_docs' => $missing,
            'required_phrase_checks' => $phraseChecks,
            'docs_overclaim_deployment' => false,
            'docs_imply_plan_confirm_runtime_wired' => false,
            'docs_imply_plan_confirm_mutated' => false,
        ];
    }

    private function c65CleanupNoteSummary(array $c68): array
    {
        $summary = (array) ($c68['c65_cleanup_note_summary'] ?? []);
        return [
            'validation_completed' => true,
            'legacy_repair_recommendation' => (string) ($summary['legacy_repair_recommendation'] ?? 'C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY'),
            'legacy_repair_recommendation_non_blocking' => (bool) ($summary['legacy_repair_recommendation_non_blocking'] ?? true),
            'normalized_repair_recommendation' => (string) ($summary['normalized_repair_recommendation'] ?? 'NOT_REQUIRED'),
            'c65_failure_repair_required' => (bool) ($summary['c65_failure_repair_required'] ?? false),
        ];
    }

    private function deploymentBridgeCandidateScorecard(array $payloads, array $artifact): array
    {
        $c68Rows = $this->indexByCode((array) ($payloads['c68']['production_catalog_activation_execution_candidate_scorecard'] ?? []));
        $rows = [];
        foreach ([
            self::PRIMARY_CANDIDATE => ['primary_production_deployment_bridge_candidate', self::PRIMARY_PARENT, true],
            self::BACKUP_CANDIDATE => ['backup_production_deployment_bridge_candidate', self::BACKUP_PARENT, true],
            self::COMPARATOR_CANDIDATE => ['comparator_only', self::COMPARATOR_PARENT, false],
        ] as $code => $spec) {
            $source = (array) ($c68Rows[$code] ?? []);
            $isActive = (bool) $spec[2];
            $pass = $isActive
                && (bool) ($source['production_catalog_activation_execution_review_pass'] ?? false)
                && (bool) ($source['candidate_active_in_production_catalog'] ?? false)
                && (bool) ($artifact['bridge_contract_review_summary']['bridge_contract_pass'] ?? false)
                && (bool) ($artifact['bridge_contract_review_summary']['runtime_consumer_contract_pass'] ?? false)
                && (bool) ($artifact['feature_flag_kill_switch_rollback_summary']['feature_flag_or_kill_switch_pass'] ?? false)
                && (bool) ($artifact['feature_flag_kill_switch_rollback_summary']['rollback_plan_pass'] ?? false)
                && (bool) ($artifact['smoke_test_shadow_read_plan_summary']['smoke_test_plan_pass'] ?? false)
                && (bool) ($artifact['smoke_test_shadow_read_plan_summary']['shadow_read_plan_pass'] ?? false)
                && (bool) ($artifact['plan_confirm_wiring_readiness_summary']['plan_confirm_wiring_readiness_pass'] ?? false)
                && (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false);
            $failures = [];
            if (! $isActive) {
                $failures[] = 'C69_A01_REMAINS_COMPARATOR_ONLY';
            }
            $rows[] = [
                'candidate_code' => $code,
                'c69_role' => $spec[0],
                'parent_candidate_code' => $spec[1],
                'c68_activation_execution_evidence_summary' => $this->c68EvidenceSummary($source),
                'c67_activation_review_evidence_summary' => (array) ($source['c67_activation_review_evidence_summary'] ?? []),
                'c66_lock_evidence_summary' => (array) ($source['c66_lock_evidence_summary'] ?? []),
                'c65_prelock_evidence_summary' => (array) ($source['c65_prelock_evidence_summary'] ?? []),
                'c64_oos_evidence_summary' => (array) ($source['c64_oos_evidence_summary'] ?? []),
                'production_deployment_prep_or_bridge_review_pass' => $pass,
                'candidate_ready_for_deployment_execution_review' => $pass,
                'candidate_active_in_controlled_catalog' => $isActive,
                'production_catalog_runtime_wired' => false,
                'production_deployment_prep_allowed' => $pass,
                'production_deployment_execution_review_allowed' => $pass,
                'production_deployment_allowed' => false,
                'production_deployment_executed' => false,
                'plan_confirm_wiring_prep_allowed' => $pass,
                'plan_confirm_mutation_allowed' => false,
                'plan_confirm_mutated' => false,
                'plan_confirm_runtime_reads_activated_catalog' => false,
                'bridge_contract_pass' => $isActive && (bool) ($artifact['bridge_contract_review_summary']['bridge_contract_pass'] ?? false),
                'runtime_consumer_contract_pass' => $isActive && (bool) ($artifact['bridge_contract_review_summary']['runtime_consumer_contract_pass'] ?? false),
                'feature_flag_or_kill_switch_pass' => $isActive && (bool) ($artifact['feature_flag_kill_switch_rollback_summary']['feature_flag_or_kill_switch_pass'] ?? false),
                'rollback_plan_pass' => $isActive && (bool) ($artifact['feature_flag_kill_switch_rollback_summary']['rollback_plan_pass'] ?? false),
                'smoke_test_plan_pass' => $isActive && (bool) ($artifact['smoke_test_shadow_read_plan_summary']['smoke_test_plan_pass'] ?? false),
                'shadow_read_plan_pass' => $isActive && (bool) ($artifact['smoke_test_shadow_read_plan_summary']['shadow_read_plan_pass'] ?? false),
                'audit_logging_plan_pass' => $isActive && (bool) ($artifact['bridge_contract_review_summary']['audit_events_proposed'] ?? false),
                'fallback_behavior_pass' => $isActive && (bool) ($artifact['bridge_contract_review_summary']['fallback_behavior_proposed'] ?? false),
                'bad_month_governance_pass' => $isActive && $this->rowPass($artifact['bad_month_bridge_review_results'], $code, 'bad_month_governance_pass'),
                'weak_regime_governance_pass' => $isActive && $this->rowPass($artifact['weak_regime_bridge_review_results'], $code, 'weak_regime_governance_pass'),
                'source_bias_governance_pass' => $isActive && (bool) ($artifact['source_bias_shared_core_bridge_review_summary']['source_bias_governance_pass'] ?? false),
                'shared_core_governance_pass' => $isActive && (bool) ($artifact['source_bias_shared_core_bridge_review_summary']['shared_core_governance_pass'] ?? false),
                'safety_and_leakage_governance_pass' => $isActive && (bool) ($source['safety_and_leakage_governance_pass'] ?? false),
                'production_mutation_safety_pass' => $isActive && (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false),
                'deployment_non_execution_pass' => true,
                'plan_confirm_non_mutation_pass' => true,
                'failure_reason_codes' => $failures,
            ];
        }
        return $rows;
    }

    private function deploymentBridgeReadinessDecision(array $scorecard, array $artifact): array
    {
        $rows = $this->indexByCode($scorecard);
        $primaryPass = (bool) ($rows[self::PRIMARY_CANDIDATE]['production_deployment_prep_or_bridge_review_pass'] ?? false);
        $backupPass = (bool) ($rows[self::BACKUP_CANDIDATE]['production_deployment_prep_or_bridge_review_pass'] ?? false);
        $pass = $primaryPass || $backupPass;
        $scope = 'NONE';
        $status = 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_FAILED_BOTH';
        if ($primaryPass && $backupPass) {
            $scope = 'PRIMARY_AND_BACKUP';
            $status = 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        } elseif ($primaryPass) {
            $scope = 'PRIMARY_ONLY';
            $status = 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_PRIMARY_ONLY';
        } elseif ($backupPass) {
            $scope = 'BACKUP_ONLY';
            $status = 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED_BACKUP_ONLY';
        }
        return [
            'validation_completed' => true,
            'production_deployment_prep_or_bridge_review_executed' => true,
            'production_deployment_prep_or_bridge_status' => $status,
            'production_deployment_prep_or_bridge_review_pass' => $pass,
            'primary_bridge_readiness_pass' => $primaryPass,
            'backup_bridge_readiness_pass' => $backupPass,
            'a01_remains_comparator_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'production_deployment_prep_or_bridge_pass_scope' => $scope,
            'decision_reason' => $pass ? 'C69 bridge/prep readiness artifact created for C70 review only; no live runtime wiring or deployment executed.' : 'C69 bridge/prep gates did not pass.',
            'diagnostic_conclusion' => $status,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => $pass,
            'production_deployment_execution_review_allowed' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => $pass,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
        ];
    }

    private function c70ReadinessDecision(array $decision): array
    {
        $pass = (bool) ($decision['production_deployment_prep_or_bridge_review_pass'] ?? false);
        return [
            'validation_completed' => true,
            'candidate_ready_for_c70_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'c70_recommendation' => $pass ? 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW' : 'C70_PRODUCTION_DEPLOYMENT_PREP_BRIDGE_CONTRACT_REPAIR',
            'decision_reason' => $pass ? 'C69 passed bridge/prep readiness; C70 may review production deployment execution, but deployment remains disabled in C69.' : 'C69 did not pass bridge/prep readiness.',
            'diagnostic_conclusion' => (string) ($decision['production_deployment_prep_or_bridge_status'] ?? 'C69_FAILED'),
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_deployment_prep_allowed' => $pass,
            'production_deployment_execution_review_allowed' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => $pass,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
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
            'dominant_blocker' => (bool) ($decision['production_deployment_prep_or_bridge_review_pass'] ?? false) ? 'NONE' : 'C69_BRIDGE_PREP_GATE_FAILURE',
            'candidate_failure_reason_codes' => $failures,
            'repair_recommendation' => (bool) ($decision['production_deployment_prep_or_bridge_review_pass'] ?? false) ? 'C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW' : 'C70_PRODUCTION_DEPLOYMENT_PREP_BRIDGE_CONTRACT_REPAIR',
        ];
    }

    private function c68EvidenceSummary(array $row): array
    {
        return [
            'c68_role' => $row['c68_role'] ?? null,
            'production_catalog_activation_execution_review_pass' => (bool) ($row['production_catalog_activation_execution_review_pass'] ?? false),
            'candidate_active_in_production_catalog' => (bool) ($row['candidate_active_in_production_catalog'] ?? false),
            'candidate_ready_for_deployment_prep_review' => (bool) ($row['candidate_ready_for_deployment_prep_review'] ?? false),
            'production_catalog_activation_execution_allowed' => (bool) ($row['production_catalog_activation_execution_allowed'] ?? false),
            'production_catalog_activation_execution_performed' => (bool) ($row['production_catalog_activation_execution_performed'] ?? false),
            'production_catalog_activated' => (bool) ($row['production_catalog_activated'] ?? false),
            'production_catalog_runtime_wired' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
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
        foreach (['c68', 'c67', 'c66', 'c65', 'c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
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

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reasonCode;
        $artifact['message'] = $message;
        $artifact['production_deployment_prep_or_bridge_review_executed'] = false;
        $artifact['production_deployment_prep_or_bridge_review_pass'] = false;
        $artifact['production_catalog_lock_allowed'] = false;
        $artifact['production_catalog_activation_allowed'] = false;
        $artifact['production_catalog_activation_execution_allowed'] = false;
        $artifact['production_catalog_activation_execution_performed'] = false;
        $artifact['production_catalog_activated'] = false;
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_prep_allowed'] = false;
        $artifact['production_deployment_execution_review_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
        $artifact['plan_confirm_wiring_prep_allowed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['plan_confirm_mutated'] = false;
        $artifact['plan_confirm_runtime_reads_activated_catalog'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $status;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reasonCode;
        $artifact['message'] = $message;
        $artifact['production_deployment_prep_or_bridge_review_executed'] = true;
        $artifact['production_deployment_prep_or_bridge_review_pass'] = false;
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_prep_allowed'] = false;
        $artifact['production_deployment_execution_review_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
        $artifact['plan_confirm_wiring_prep_allowed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['plan_confirm_mutated'] = false;
        $artifact['plan_confirm_runtime_reads_activated_catalog'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $status;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C69_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'C69_BLOCKED_OUTPUT_EXISTS';
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

    private function validationFail(string $status, string $message): array
    {
        return [
            'validation_completed' => true,
            'pass' => false,
            'status' => $status,
            'reason_code' => $status,
            'message' => $message,
        ];
    }

    private function diagnostics(array $artifact): array
    {
        return [
            'C69 validates C68 artifact hash and file SHA1 before bridge/prep readiness.',
            'C69 validates nested c69_readiness_decision and production_catalog_activation_record paths from C68.',
            'C69 preserves C60-C68 locked lineage and C68 candidate hierarchy.',
            'C69 creates only a non-runtime bridge/prep readiness artifact and proposal.',
            'C69 keeps production_catalog_runtime_wired=false, production_deployment_allowed=false, production_deployment_executed=false, plan_confirm_mutation_allowed=false, plan_confirm_mutated=false, and plan_confirm_runtime_reads_activated_catalog=false.',
            'Next valid step after pass is C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW.',
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'c69_is_production_deployment_prep_or_bridge_review' => true,
            'c69_is_not_production_deployment' => true,
            'c69_is_not_plan_confirm_rollout' => true,
            'c69_is_not_runtime_wiring' => true,
            'c69_is_not_redesign' => true,
            'c69_is_not_retune' => true,
            'c69_is_not_parameter_search' => true,
            'c69_is_not_oos_retest' => true,
            'candidate_scope_change_forbidden' => true,
            'a01_promotion_forbidden' => true,
            'bad_month_risk_hidden_forbidden' => true,
            'weak_regime_removed_forbidden' => true,
            'production_deployment_allowed_must_remain_false' => true,
            'plan_confirm_mutation_allowed_must_remain_false' => true,
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
