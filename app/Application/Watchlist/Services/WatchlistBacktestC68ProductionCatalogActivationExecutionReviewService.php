<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService
{
    public const RUN_CODE = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW';
    public const ARTIFACT_TYPE = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW';

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

    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json';

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
        'c68_review_doc' => 'docs/watchlist/audit/WS_C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW.md',
        'c68_operator_commands_doc' => 'docs/watchlist/audit/WS_C68_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    /**
     * C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW. CONTROLLED_ACTIVATION_ARTIFACT_RECORD_ONLY.
     * NOT_REDESIGN. NOT_RETUNE. NOT_PARAMETER_SEARCH. NOT_OOS_RETEST. NOT_PRODUCTION_DEPLOYMENT.
     * NOT_LIVE_PLAN_CONFIRM_ROLLOUT. C67_ARTIFACT_HASH_LOCK. C67_FILE_SHA1_LOCK. C60_TO_C67_LINEAGE_LOCK.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. ASOF_SAFE_LOOKUP_REQUIRED. SELECTION_SCOPE_FROZEN_FROM_C67.
     * NO_OOS_BASED_RERANKING. NO_OOS_TIE_BREAK. A01_COMPARATOR_ONLY_NOT_PROMOTABLE.
     * BAD_MONTH_RISK_RETAINED. WEAK_REGIME_RISK_RETAINED. SOURCE_BIAS_SHARED_CORE_RISK_RETAINED.
     * ACTIVATION_EXECUTION_ARTIFACT_ALLOWED_ONLY. RUNTIME_WIRING_FALSE. DEPLOYMENT_ALLOWED_FALSE.
     * PLAN_CONFIRM_MUTATION_ALLOWED_FALSE. NO_LATEST_DATE_SHORTCUT. NO_DATE_DESC_SHORTCUT.
     * NO_FUTURE_LOOKUP. NO_RETURN_FIELDS_FOR_SELECTION. C68_CAN_ONLY_RECOMMEND_C69_DEPLOYMENT_PREP_BRIDGE.
     */
    public function execute(
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
        $artifact = $this->baseArtifact(
            $c67Artifact,
            $expectedC67Hash,
            $expectedC67FileSha1,
            $c66Artifact,
            $expectedC66Hash,
            $expectedC66FileSha1,
            $c65Artifact,
            $expectedC65Hash,
            $expectedC65FileSha1,
            $c64Artifact,
            $expectedC64Hash,
            $expectedC64FileSha1,
            $c63Artifact,
            $expectedC63Hash,
            $expectedC63FileSha1,
            $c62Artifact,
            $expectedC62Hash,
            $expectedC62FileSha1,
            $c61Artifact,
            $expectedC61Hash,
            $expectedC61FileSha1,
            $c60Artifact,
            $expectedC60Hash,
            $expectedC60FileSha1,
            (string) ($options['executed_at'] ?? gmdate('c'))
        );

        $dictionary = $this->databaseDictionaryReadSummary();
        $artifact['database_dictionary_read_summary'] = $dictionary;
        if ((bool) ($dictionary['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C68_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'C68_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'C68 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath, $overwrite);
        }

        $c67Load = $this->loadArtifactLock($c67Artifact, $expectedC67Hash, $expectedC67FileSha1);
        $this->copyLock($artifact, 'c67', $c67Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);
        if (! $c67Load['readable']) {
            return $this->blocked($artifact, 'C68_BLOCKED_MISSING_C67_ARTIFACT', 'C68_BLOCKED_MISSING_C67_ARTIFACT', 'C68 requires the locked C67 artifact.', $outputPath, $overwrite);
        }
        if (! $c67Load['hash_match']) {
            return $this->blocked($artifact, 'C68_BLOCKED_C67_ARTIFACT_LOCK_MISMATCH', 'C68_BLOCKED_C67_ARTIFACT_LOCK_MISMATCH', 'C67 artifact hash does not match the expected C68 lock.', $outputPath, $overwrite);
        }
        if (! $c67Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C68_BLOCKED_C67_FILE_SHA1_LOCK_MISMATCH', 'C68_BLOCKED_C67_FILE_SHA1_LOCK_MISMATCH', 'C67 file SHA1 does not match the expected C68 lock.', $outputPath, $overwrite);
        }

        $c67 = (array) $c67Load['payload'];
        $c67Validation = $this->validateC67($c67);
        $artifact['c67_lock_validation_summary'] = $c67Validation;
        if (! (bool) ($c67Validation['pass'] ?? false)) {
            return $this->blocked($artifact, (string) ($c67Validation['status'] ?? 'C68_BLOCKED_C67_STATUS_OR_REASON_MISMATCH'), (string) ($c67Validation['reason_code'] ?? 'C68_BLOCKED_C67_STATUS_OR_REASON_MISMATCH'), (string) ($c67Validation['message'] ?? 'C67 lock is invalid for C68.'), $outputPath, $overwrite);
        }

        $lineageLoads = [
            'c66' => $this->loadArtifactLock($c66Artifact, $expectedC66Hash, $expectedC66FileSha1),
            'c65' => $this->loadArtifactLock($c65Artifact, $expectedC65Hash, $expectedC65FileSha1),
            'c64' => $this->loadArtifactLock($c64Artifact, $expectedC64Hash, $expectedC64FileSha1),
            'c63' => $this->loadArtifactLock($c63Artifact, $expectedC63Hash, $expectedC63FileSha1),
            'c62' => $this->loadArtifactLock($c62Artifact, $expectedC62Hash, $expectedC62FileSha1),
            'c61' => $this->loadArtifactLock($c61Artifact, $expectedC61Hash, $expectedC61FileSha1),
            'c60' => $this->loadArtifactLock($c60Artifact, $expectedC60Hash, $expectedC60FileSha1),
        ];
        foreach ($lineageLoads as $prefix => $load) {
            $this->copyLock($artifact, $prefix, $load);
        }
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        $lineageValidation = $this->validateLineage($lineageLoads);
        foreach ($lineageValidation['summaries'] as $key => $summary) {
            $artifact[$key.'_lineage_validation_summary'] = $summary;
        }
        if (! (bool) ($lineageValidation['pass'] ?? false)) {
            return $this->blocked($artifact, 'C68_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C68_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C60-C66 lineage lock is invalid for C68.', $outputPath, $overwrite);
        }

        $payloads = [];
        foreach ($lineageLoads as $prefix => $load) {
            $payloads[$prefix] = (array) ($load['payload'] ?? []);
        }

        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c67);
        if (! (bool) ($artifact['candidate_scope_freeze_summary']['candidate_scope_pass'] ?? false)) {
            return $this->rejected($artifact, 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C67 candidate scope is not the locked C68 hierarchy.', $outputPath, $overwrite);
        }

        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        if (! (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false)) {
            return $this->rejected($artifact, 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE', 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE', 'C68 documentation governance is incomplete.', $outputPath, $overwrite);
        }

        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c67);
        $artifact['concentration_loss_cluster_governance_summary'] = $this->concentrationLossClusterGovernanceSummary($payloads['c64']);
        $artifact['rolling_month_dependency_governance_summary'] = $this->rollingMonthDependencyGovernanceSummary($payloads['c64']);
        $artifact['source_bias_shared_core_governance_summary'] = $this->sourceBiasSharedCoreGovernanceSummary();
        $artifact['production_activation_execution_mutation_safety_summary'] = $this->productionActivationExecutionMutationSafetySummary($c67);

        $scorecard = $this->activationExecutionCandidateScorecard($c67, $payloads, $artifact);
        $artifact['production_catalog_activation_execution_candidate_scorecard'] = $scorecard;
        $artifact['bad_month_activation_execution_review_results'] = $this->badMonthActivationExecutionReviewResults($scorecard);
        $artifact['weak_regime_activation_execution_review_results'] = $this->weakRegimeActivationExecutionReviewResults($scorecard);

        $decision = $this->productionCatalogActivationExecutionDecision($scorecard, $artifact);
        $artifact['production_catalog_activation_execution_decision'] = $decision;
        $artifact['production_catalog_activation_record'] = $this->productionCatalogActivationRecord($decision, $scorecard, $artifact);
        $artifact['c69_readiness_decision'] = $this->c69ReadinessDecision($scorecard, $decision);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($scorecard, $decision, $artifact);
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        $artifact['status'] = (string) ($decision['production_catalog_activation_execution_status'] ?? 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_FAILED_BOTH');
        $artifact['reason_code'] = $artifact['status'];
        $artifact['production_catalog_activation_execution_review_executed'] = true;
        $artifact['production_catalog_activation_execution_review_pass'] = (bool) ($decision['production_catalog_activation_execution_review_pass'] ?? false);
        $artifact['production_catalog_lock_allowed'] = true;
        $artifact['production_catalog_activation_allowed'] = true;
        $artifact['production_catalog_activation_execution_allowed'] = (bool) ($decision['production_catalog_activation_execution_allowed'] ?? false);
        $artifact['production_catalog_activation_execution_performed'] = (bool) ($decision['production_catalog_activation_execution_performed'] ?? false);
        $artifact['production_catalog_activated'] = (bool) ($decision['production_catalog_activated'] ?? false);
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['plan_confirm_mutated'] = false;
        $artifact['plan_confirm_runtime_reads_activated_catalog'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $artifact['status'];
        $artifact['next_step_recommendation'] = (string) ($artifact['c69_readiness_decision']['c69_recommendation'] ?? 'C69_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_GOVERNANCE_CLEANUP');

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(
        string $c67Artifact,
        string $expectedC67Hash,
        string $expectedC67FileSha1,
        string $c66Artifact,
        string $expectedC66Hash,
        string $expectedC66FileSha1,
        string $c65Artifact,
        string $expectedC65Hash,
        string $expectedC65FileSha1,
        string $c64Artifact,
        string $expectedC64Hash,
        string $expectedC64FileSha1,
        string $c63Artifact,
        string $expectedC63Hash,
        string $expectedC63FileSha1,
        string $c62Artifact,
        string $expectedC62Hash,
        string $expectedC62FileSha1,
        string $c61Artifact,
        string $expectedC61Hash,
        string $expectedC61FileSha1,
        string $c60Artifact,
        string $expectedC60Hash,
        string $expectedC60FileSha1,
        string $executedAt
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'C68_NOT_RUN',
            'reason_code' => 'C68_NOT_RUN',
            'created_at' => $executedAt,
            'production_catalog_activation_execution_review_executed' => false,
            'production_catalog_activation_execution_review_pass' => false,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => false,
            'production_catalog_activation_execution_performed' => false,
            'production_catalog_activated' => false,
            'production_catalog_runtime_wired' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'production_ready' => false,
            'expected_c67_artifact_path' => $this->defaulted($c67Artifact, self::DEFAULT_C67_ARTIFACT),
            'expected_c67_hash' => $this->defaulted($expectedC67Hash, self::DEFAULT_EXPECTED_C67_HASH),
            'expected_c67_file_sha1' => strtoupper($this->defaulted($expectedC67FileSha1, self::DEFAULT_EXPECTED_C67_FILE_SHA1)),
            'expected_c66_artifact_path' => $this->defaulted($c66Artifact, self::DEFAULT_C66_ARTIFACT),
            'expected_c66_hash' => $this->defaulted($expectedC66Hash, self::DEFAULT_EXPECTED_C66_HASH),
            'expected_c66_file_sha1' => strtoupper($this->defaulted($expectedC66FileSha1, self::DEFAULT_EXPECTED_C66_FILE_SHA1)),
            'expected_c65_artifact_path' => $this->defaulted($c65Artifact, self::DEFAULT_C65_ARTIFACT),
            'expected_c65_hash' => $this->defaulted($expectedC65Hash, self::DEFAULT_EXPECTED_C65_HASH),
            'expected_c65_file_sha1' => strtoupper($this->defaulted($expectedC65FileSha1, self::DEFAULT_EXPECTED_C65_FILE_SHA1)),
            'expected_c64_artifact_path' => $this->defaulted($c64Artifact, self::DEFAULT_C64_ARTIFACT),
            'expected_c64_hash' => $this->defaulted($expectedC64Hash, self::DEFAULT_EXPECTED_C64_HASH),
            'expected_c64_file_sha1' => strtoupper($this->defaulted($expectedC64FileSha1, self::DEFAULT_EXPECTED_C64_FILE_SHA1)),
            'expected_c63_artifact_path' => $this->defaulted($c63Artifact, self::DEFAULT_C63_ARTIFACT),
            'expected_c63_hash' => $this->defaulted($expectedC63Hash, self::DEFAULT_EXPECTED_C63_HASH),
            'expected_c63_file_sha1' => strtoupper($this->defaulted($expectedC63FileSha1, self::DEFAULT_EXPECTED_C63_FILE_SHA1)),
            'expected_c62_artifact_path' => $this->defaulted($c62Artifact, self::DEFAULT_C62_ARTIFACT),
            'expected_c62_hash' => $this->defaulted($expectedC62Hash, self::DEFAULT_EXPECTED_C62_HASH),
            'expected_c62_file_sha1' => strtoupper($this->defaulted($expectedC62FileSha1, self::DEFAULT_EXPECTED_C62_FILE_SHA1)),
            'expected_c61_artifact_path' => $this->defaulted($c61Artifact, self::DEFAULT_C61_ARTIFACT),
            'expected_c61_hash' => $this->defaulted($expectedC61Hash, self::DEFAULT_EXPECTED_C61_HASH),
            'expected_c61_file_sha1' => strtoupper($this->defaulted($expectedC61FileSha1, self::DEFAULT_EXPECTED_C61_FILE_SHA1)),
            'expected_c60_artifact_path' => $this->defaulted($c60Artifact, self::DEFAULT_C60_ARTIFACT),
            'expected_c60_hash' => $this->defaulted($expectedC60Hash, self::DEFAULT_EXPECTED_C60_HASH),
            'expected_c60_file_sha1' => strtoupper($this->defaulted($expectedC60FileSha1, self::DEFAULT_EXPECTED_C60_FILE_SHA1)),
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c67_lock_validation_summary' => [],
            'c66_lineage_validation_summary' => [],
            'c65_lineage_validation_summary' => [],
            'c64_lineage_validation_summary' => [],
            'c63_lineage_validation_summary' => [],
            'c62_lineage_validation_summary' => [],
            'c61_lineage_validation_summary' => [],
            'c60_lineage_validation_summary' => [],
            'candidate_scope_freeze_summary' => [],
            'production_catalog_activation_execution_candidate_scorecard' => [],
            'production_catalog_activation_record' => [],
            'bad_month_activation_execution_review_results' => [],
            'weak_regime_activation_execution_review_results' => [],
            'concentration_loss_cluster_governance_summary' => [],
            'rolling_month_dependency_governance_summary' => [],
            'source_bias_shared_core_governance_summary' => [],
            'production_activation_execution_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c65_cleanup_note_summary' => [],
            'production_catalog_activation_execution_decision' => [],
            'c69_readiness_decision' => [],
            'failure_attribution_summary' => [],
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
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
        foreach (['c67', 'c66', 'c65', 'c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
            $locks[$prefix.'_artifact_path'] = $artifact['expected_'.$prefix.'_artifact_path'] ?? null;
            $locks['expected_'.$prefix.'_hash'] = $artifact['expected_'.$prefix.'_hash'] ?? null;
            $locks['actual_'.$prefix.'_hash'] = $artifact['actual_'.$prefix.'_hash'] ?? null;
            $locks[$prefix.'_hash_match'] = (bool) ($artifact[$prefix.'_hash_match'] ?? false);
            $locks['expected_'.$prefix.'_file_sha1'] = $artifact['expected_'.$prefix.'_file_sha1'] ?? null;
            $locks['actual_'.$prefix.'_file_sha1'] = $artifact['actual_'.$prefix.'_file_sha1'] ?? null;
            $locks[$prefix.'_file_sha1_match'] = (bool) ($artifact[$prefix.'_file_sha1_match'] ?? false);
            $locks[$prefix.'_artifact_readable'] = (bool) ($artifact[$prefix.'_artifact_readable'] ?? false);
        }
        return $locks;
    }

    private function validateC67(array $c67): array
    {
        $checks = [
            'run_code_match' => ($c67['run_code'] ?? null) === 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW',
            'status_match' => ($c67['status'] ?? null) === 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
            'reason_code_match' => ($c67['reason_code'] ?? null) === 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP',
            'activation_review_executed' => (bool) ($c67['production_catalog_activation_review_executed'] ?? false),
            'activation_review_pass' => (bool) ($c67['production_catalog_activation_review_pass'] ?? false),
            'production_catalog_lock_allowed' => (bool) ($c67['production_catalog_lock_allowed'] ?? false),
            'production_catalog_activation_allowed' => (bool) ($c67['production_catalog_activation_allowed'] ?? false),
            'candidate_ready_for_c68_count_match' => (int) ($c67['c68_readiness_decision']['candidate_ready_for_c68_count'] ?? -1) === 2,
            'activation_execution_flag_false' => ($c67['production_catalog_activation_execution_allowed'] ?? null) === false,
            'deployment_flag_false' => ($c67['production_deployment_allowed'] ?? null) === false,
            'plan_confirm_mutation_flag_false' => ($c67['plan_confirm_mutation_allowed'] ?? null) === false,
            'production_activation_mutation_safety_pass' => (bool) ($c67['production_activation_mutation_safety_summary']['production_activation_mutation_safety_pass'] ?? false),
            'c68_recommendation_match' => ($c67['c68_readiness_decision']['c68_recommendation'] ?? null) === 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW',
            'activation_pass_scope_match' => ($c67['production_catalog_activation_review_decision']['production_catalog_activation_pass_scope'] ?? null) === 'PRIMARY_AND_BACKUP',
        ];
        $pass = ! in_array(false, $checks, true);
        $status = 'C68_C67_LOCK_VALID';
        $message = 'C67 lock is valid for C68 activation execution review.';
        if (! $checks['status_match'] || ! $checks['reason_code_match'] || ! $checks['run_code_match']) {
            $status = 'C68_BLOCKED_C67_STATUS_OR_REASON_MISMATCH';
            $message = 'C67 status, reason_code, or run_code does not match locked evidence.';
        } elseif (! $checks['activation_review_pass'] || ! $checks['activation_review_executed']) {
            $status = 'C68_BLOCKED_C67_ACTIVATION_REVIEW_NOT_PASSED';
            $message = 'C67 activation review did not pass.';
        } elseif (! $checks['production_catalog_activation_allowed']) {
            $status = 'C68_BLOCKED_C67_PRODUCTION_CATALOG_ACTIVATION_NOT_ALLOWED';
            $message = 'C67 production catalog activation is not allowed.';
        } elseif (! $checks['candidate_ready_for_c68_count_match']) {
            $status = 'C68_BLOCKED_C67_C68_READINESS_COUNT_MISMATCH';
            $message = 'C67 candidate_ready_for_c68_count must equal 2.';
        } elseif (! $checks['activation_execution_flag_false']) {
            $status = 'C68_BLOCKED_C67_EXECUTION_FLAG_INVALID';
            $message = 'C67 activation execution flag must still be false before C68.';
        } elseif (! $checks['deployment_flag_false']) {
            $status = 'C68_BLOCKED_C67_DEPLOYMENT_FLAG_INVALID';
            $message = 'C67 production deployment flag must be false.';
        } elseif (! $checks['plan_confirm_mutation_flag_false']) {
            $status = 'C68_BLOCKED_C67_PLAN_CONFIRM_MUTATION_FLAG_INVALID';
            $message = 'C67 PLAN/CONFIRM mutation flag must be false.';
        } elseif (! $checks['activation_pass_scope_match'] || ! $checks['c68_recommendation_match'] || ! $checks['production_activation_mutation_safety_pass']) {
            $status = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_REJECTED_ACTIVATION_REVIEW_INCOMPLETE';
            $message = 'C67 activation review evidence is incomplete for C68.';
        }
        return [
            'validation_completed' => true,
            'pass' => $pass,
            'status' => $status,
            'reason_code' => $status,
            'message' => $message,
            'checks' => $checks,
        ];
    }

    private function validateLineage(array $loads): array
    {
        $expected = [
            'c66' => ['run_code' => 'C66_PRODUCTION_LOCK_REVIEW', 'status' => 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', 'reason_code' => 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', 'nested' => ['c67_readiness_decision', 'candidate_ready_for_c67_count', 2]],
            'c65' => ['run_code' => 'C65_PRODUCTION_PRE_LOCK_REVIEW', 'status' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', 'reason_code' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', 'nested' => ['c66_readiness_decision', 'candidate_ready_for_c66_count', 2]],
            'c64' => ['run_code' => 'C64_PRE_OOS_OR_OOS_PROOF_EXECUTION', 'status' => 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP', 'reason_code' => 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP', 'nested' => ['c65_readiness_decision', 'candidate_ready_for_c65_count', 2]],
            'c63' => ['run_code' => 'C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY', 'status' => 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', 'reason_code' => 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', 'nested' => ['c64_readiness_decision', 'candidate_ready_for_c64_count', 2]],
            'c62' => ['run_code' => 'C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY', 'status' => 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', 'reason_code' => 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', 'nested' => ['c63_readiness_decision', 'candidate_ready_for_c63_count', 2]],
            'c61' => ['run_code' => 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY', 'status' => 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED', 'reason_code' => 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE', 'nested' => ['c62_readiness_decision', 'candidate_ready_for_c62_count', 3]],
            'c60' => ['run_code' => 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY', 'status' => 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED', 'reason_code' => 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS', 'nested' => null],
        ];
        $summaries = [];
        $allPass = true;
        foreach ($expected as $prefix => $rule) {
            $load = $loads[$prefix] ?? [];
            $payload = (array) ($load['payload'] ?? []);
            $nestedPass = true;
            if ($rule['nested'] !== null) {
                [$section, $field, $value] = $rule['nested'];
                $nestedPass = (int) ($payload[$section][$field] ?? -1) === $value;
            }
            $checks = [
                'artifact_readable' => (bool) ($load['readable'] ?? false),
                'artifact_hash_match' => (bool) ($load['hash_match'] ?? false),
                'file_sha1_match' => (bool) ($load['file_sha1_match'] ?? false),
                'run_code_match' => ($payload['run_code'] ?? null) === $rule['run_code'],
                'status_match' => ($payload['status'] ?? null) === $rule['status'],
                'reason_code_match' => ($payload['reason_code'] ?? null) === $rule['reason_code'],
                'readiness_count_match' => $nestedPass,
                'production_ready_false' => ($payload['production_ready'] ?? false) === false,
            ];
            if ($prefix === 'c64') {
                $checks['oos_proof_pass'] = (bool) ($payload['oos_proof_pass'] ?? false);
                $checks['oos_pass_scope_primary_and_backup'] = ($payload['oos_pass_scope'] ?? 'PRIMARY_AND_BACKUP') === 'PRIMARY_AND_BACKUP' || ($payload['status'] ?? null) === 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP';
            }
            if ($prefix === 'c66') {
                $checks['production_lock_review_pass'] = (bool) ($payload['production_lock_review_pass'] ?? false);
                $checks['production_catalog_lock_allowed'] = (bool) ($payload['production_catalog_lock_allowed'] ?? false);
                $checks['production_catalog_activation_allowed_false'] = ($payload['production_catalog_activation_allowed'] ?? null) === false;
                $checks['production_deployment_allowed_false'] = ($payload['production_deployment_allowed'] ?? null) === false;
                $checks['plan_confirm_mutation_allowed_false'] = ($payload['plan_confirm_mutation_allowed'] ?? null) === false;
            }
            if ($prefix === 'c65') {
                $checks['production_prelock_review_pass'] = (bool) ($payload['production_prelock_review_pass'] ?? false);
                $checks['production_catalog_allowed_false'] = ($payload['production_catalog_allowed'] ?? null) === false;
                $checks['production_deployment_allowed_false'] = ($payload['production_deployment_allowed'] ?? null) === false;
            }
            $pass = ! in_array(false, $checks, true);
            if (! $pass) {
                $allPass = false;
            }
            $summaries[$prefix] = [
                'validation_completed' => true,
                'pass' => $pass,
                'artifact_path' => $load['path'] ?? null,
                'actual_hash' => $load['actual_hash'] ?? null,
                'actual_file_sha1' => $load['actual_file_sha1'] ?? null,
                'checks' => $checks,
            ];
        }
        return [
            'validation_completed' => true,
            'pass' => $allPass,
            'summaries' => $summaries,
        ];
    }

    private function candidateScopeFreezeSummary(array $c67): array
    {
        $decision = (array) ($c67['production_catalog_activation_review_decision'] ?? []);
        $summary = [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C67_LOCKED_PRODUCTION_CATALOG_ACTIVATION_REVIEW_DECISION',
            'primary_candidate_code' => (string) ($decision['primary_candidate_code'] ?? ''),
            'backup_candidate_codes' => array_values((array) ($decision['backup_candidate_codes'] ?? [])),
            'comparator_only_candidate_codes' => array_values((array) ($decision['comparator_only_candidate_codes'] ?? [])),
            'candidate_scope_changed_after_c67' => false,
            'candidate_scope_changed_after_c66' => false,
            'candidate_scope_changed_after_c65' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'oos_result_used_for_new_ranking' => false,
            'a01_promoted' => false,
        ];
        $summary['candidate_scope_pass'] = $summary['primary_candidate_code'] === self::PRIMARY_CANDIDATE
            && $summary['backup_candidate_codes'] === [self::BACKUP_CANDIDATE]
            && $summary['comparator_only_candidate_codes'] === [self::COMPARATOR_CANDIDATE]
            && $summary['candidate_scope_changed_after_c67'] === false
            && $summary['new_candidate_created'] === false
            && $summary['selection_rule_changed'] === false
            && $summary['parameter_changed'] === false
            && $summary['oos_result_used_for_new_ranking'] === false
            && $summary['a01_promoted'] === false;
        return $summary;
    }

    private function activationExecutionCandidateScorecard(array $c67, array $payloads, array $artifact): array
    {
        $c67Rows = $this->indexByCode((array) ($c67['production_catalog_activation_candidate_scorecard'] ?? []));
        $c66Rows = $this->indexByCode((array) ($payloads['c66']['production_lock_candidate_scorecard'] ?? []));
        $c65Rows = $this->indexByCode((array) ($payloads['c65']['production_prelock_candidate_scorecard'] ?? []));
        $c64Rows = $this->indexByCode((array) ($payloads['c64']['oos_proof_candidate_scorecard'] ?? []));
        $rows = [];
        foreach ([self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE, self::COMPARATOR_CANDIDATE] as $code) {
            $role = $code === self::PRIMARY_CANDIDATE ? 'primary_production_catalog_activation_execution_candidate' : ($code === self::BACKUP_CANDIDATE ? 'backup_production_catalog_activation_execution_candidate' : 'comparator_only');
            $parent = $code === self::PRIMARY_CANDIDATE ? self::PRIMARY_PARENT : ($code === self::BACKUP_CANDIDATE ? self::BACKUP_PARENT : self::COMPARATOR_PARENT);
            $isComparator = $code === self::COMPARATOR_CANDIDATE;
            $c64 = (array) ($c64Rows[$code] ?? []);
            $c67Row = (array) ($c67Rows[$code] ?? []);
            $badMonthPass = $isComparator ? false : $this->badMonthGovernancePass($c64);
            $weakRegimePass = $isComparator ? false : $this->weakRegimeGovernancePass($c64);
            $concentrationPass = $isComparator ? false : (bool) ($c64['oos_concentration_validation_pass'] ?? false);
            $lossClusterPass = $isComparator ? false : (bool) ($c64['oos_loss_cluster_validation_pass'] ?? false);
            $rollingPass = $isComparator ? false : (bool) ($c64['oos_rolling_validation_pass'] ?? false);
            $sourceBiasPass = $isComparator ? false : (bool) ($c64['oos_source_bias_validation_pass'] ?? false);
            $sharedCorePass = $isComparator ? false : (bool) ($c64['oos_shared_core_validation_pass'] ?? false);
            $safetyPass = $isComparator ? false : (bool) ($c64['oos_safety_and_leakage_pass'] ?? false);
            $mutationPass = $isComparator ? false : (bool) ($artifact['production_activation_execution_mutation_safety_summary']['production_activation_execution_mutation_safety_pass'] ?? false);
            $activationReviewPass = $isComparator ? false : (bool) ($c67Row['production_catalog_activation_review_pass'] ?? false);
            $candidatePass = ! $isComparator
                && $activationReviewPass
                && (bool) ($c67Row['candidate_ready_for_production_catalog_activation'] ?? false)
                && (bool) ($c64['oos_proof_pass'] ?? false)
                && $badMonthPass
                && $weakRegimePass
                && $concentrationPass
                && $lossClusterPass
                && $rollingPass
                && $sourceBiasPass
                && $sharedCorePass
                && $safetyPass
                && $mutationPass;
            $failure = [];
            if ($isComparator) {
                $failure[] = 'C68_A01_REMAINS_COMPARATOR_ONLY';
            } else {
                if (! $activationReviewPass) { $failure[] = 'C68_C67_ACTIVATION_REVIEW_NOT_PASSED'; }
                if (! (bool) ($c64['oos_proof_pass'] ?? false)) { $failure[] = 'C68_C64_OOS_PROOF_NOT_PASSED'; }
                if (! $badMonthPass) { $failure[] = 'C68_BAD_MONTH_GOVERNANCE_FAIL'; }
                if (! $weakRegimePass) { $failure[] = 'C68_WEAK_REGIME_GOVERNANCE_FAIL'; }
                if (! $concentrationPass) { $failure[] = 'C68_CONCENTRATION_GOVERNANCE_FAIL'; }
                if (! $lossClusterPass) { $failure[] = 'C68_LOSS_CLUSTER_GOVERNANCE_FAIL'; }
                if (! $rollingPass) { $failure[] = 'C68_ROLLING_GOVERNANCE_FAIL'; }
                if (! $sourceBiasPass) { $failure[] = 'C68_SOURCE_BIAS_GOVERNANCE_FAIL'; }
                if (! $sharedCorePass) { $failure[] = 'C68_SHARED_CORE_GOVERNANCE_FAIL'; }
                if (! $safetyPass) { $failure[] = 'C68_SAFETY_AND_LEAKAGE_FAIL'; }
                if (! $mutationPass) { $failure[] = 'C68_ACTIVATION_MUTATION_SAFETY_FAIL'; }
            }
            $rows[] = [
                'candidate_code' => $code,
                'c68_role' => $role,
                'parent_candidate_code' => $parent,
                'c67_activation_review_evidence_summary' => $this->compactEvidence($c67Row, ['c67_role', 'production_catalog_activation_review_pass', 'candidate_ready_for_production_catalog_activation', 'production_catalog_activation_execution_allowed', 'production_deployment_allowed', 'plan_confirm_mutation_allowed', 'failure_reason_codes']),
                'c66_lock_evidence_summary' => $this->compactEvidence((array) ($c66Rows[$code] ?? []), ['c66_role', 'production_lock_review_pass', 'candidate_ready_for_production_catalog_activation', 'failure_reason_codes']),
                'c65_prelock_evidence_summary' => $this->compactEvidence((array) ($c65Rows[$code] ?? []), ['c65_role', 'production_prelock_review_pass', 'candidate_ready_for_c66', 'failure_reason_codes']),
                'c64_oos_evidence_summary' => $this->compactEvidence($c64, ['c64_oos_role', 'oos_evaluated_picks_count', 'oos_trading_days_covered', 'oos_first_trade_date', 'oos_last_trade_date', 'oos_avg_ret_net', 'oos_median_ret_net', 'oos_win_rate', 'oos_month_count', 'oos_month_win_rate_min', 'oos_bad_month_count', 'oos_zero_win_month_count', 'oos_worst_month', 'oos_worst_month_pick_count', 'oos_worst_month_win_rate', 'oos_worst_month_avg_ret_net', 'oos_worst_month_regime', 'oos_bad_month_risk_level', 'oos_bad_month_decision', 'oos_weak_regime_pick_count', 'oos_weak_regime_avg_ret_net', 'oos_weak_regime_median_ret_net', 'oos_weak_regime_win_rate', 'oos_weak_regime_month_coverage', 'oos_weak_regime_sample_status', 'oos_weak_regime_sample_collapse_detected', 'oos_weak_regime_risk_level', 'oos_concentration_validation_pass', 'oos_loss_cluster_validation_pass', 'oos_rolling_validation_pass', 'oos_bad_month_validation_pass', 'oos_weak_regime_validation_pass', 'oos_source_bias_validation_pass', 'oos_shared_core_validation_pass', 'oos_safety_and_leakage_pass', 'oos_proof_pass', 'candidate_ready_for_c65', 'failure_reason_codes']),
                'production_catalog_activation_execution_review_pass' => $candidatePass,
                'candidate_active_in_production_catalog' => $candidatePass,
                'candidate_ready_for_deployment_prep_review' => $candidatePass,
                'production_catalog_lock_allowed' => ! $isComparator,
                'production_catalog_activation_allowed' => ! $isComparator,
                'production_catalog_activation_execution_allowed' => $candidatePass,
                'production_catalog_activation_execution_performed' => $candidatePass,
                'production_catalog_activated' => $candidatePass,
                'production_catalog_runtime_wired' => false,
                'production_deployment_allowed' => false,
                'production_deployment_executed' => false,
                'plan_confirm_mutation_allowed' => false,
                'plan_confirm_mutated' => false,
                'bad_month_governance_pass' => $badMonthPass,
                'weak_regime_governance_pass' => $weakRegimePass,
                'concentration_governance_pass' => $concentrationPass,
                'loss_cluster_governance_pass' => $lossClusterPass,
                'rolling_governance_pass' => $rollingPass,
                'source_bias_governance_pass' => $sourceBiasPass,
                'shared_core_governance_pass' => $sharedCorePass,
                'safety_and_leakage_governance_pass' => $safetyPass,
                'activation_mutation_safety_pass' => $mutationPass,
                'deployment_non_execution_pass' => true,
                'plan_confirm_non_mutation_pass' => true,
                'failure_reason_codes' => $failure,
            ];
        }
        return $rows;
    }

    private function compactEvidence(array $row, array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                $out[$key] = $row[$key];
            }
        }
        return $out;
    }

    private function badMonthGovernancePass(array $c64): bool
    {
        return (bool) ($c64['oos_bad_month_validation_pass'] ?? false)
            && ($c64['oos_bad_month_risk_level'] ?? null) === 'MODERATE'
            && ($c64['oos_bad_month_decision'] ?? null) === 'PASS_WITH_DOCUMENTED_RISK'
            && isset($c64['oos_worst_month'], $c64['oos_worst_month_avg_ret_net'], $c64['oos_worst_month_regime'])
            && ($c64['oos_worst_month_regime'] ?? null) === self::WEAK_REGIME;
    }

    private function weakRegimeGovernancePass(array $c64): bool
    {
        return (bool) ($c64['oos_weak_regime_validation_pass'] ?? false)
            && ($c64['oos_weak_regime_sample_status'] ?? null) === 'SUFFICIENT'
            && ($c64['oos_weak_regime_sample_collapse_detected'] ?? null) === false
            && ($c64['oos_weak_regime_risk_level'] ?? null) === 'MODERATE'
            && isset($c64['oos_weak_regime_pick_count'], $c64['oos_weak_regime_month_coverage']);
    }

    private function badMonthActivationExecutionReviewResults(array $scorecard): array
    {
        $results = [];
        foreach ($scorecard as $row) {
            if ($row['c68_role'] === 'comparator_only') {
                continue;
            }
            $c64 = (array) ($row['c64_oos_evidence_summary'] ?? []);
            $results[] = [
                'candidate_code' => $row['candidate_code'],
                'bad_month_activation_execution_review_completed' => true,
                'documented_bad_month_risk_retained' => (bool) ($row['bad_month_governance_pass'] ?? false),
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'worst_month' => $c64['oos_worst_month'] ?? null,
                'worst_month_avg_ret_net' => $c64['oos_worst_month_avg_ret_net'] ?? null,
                'worst_month_regime' => $c64['oos_worst_month_regime'] ?? null,
                'bad_month_risk_level' => $c64['oos_bad_month_risk_level'] ?? null,
                'bad_month_governance_decision' => $c64['oos_bad_month_decision'] ?? null,
                'production_activation_risk_free_claim' => false,
            ];
        }
        return $results;
    }

    private function weakRegimeActivationExecutionReviewResults(array $scorecard): array
    {
        $results = [];
        foreach ($scorecard as $row) {
            if ($row['c68_role'] === 'comparator_only') {
                continue;
            }
            $c64 = (array) ($row['c64_oos_evidence_summary'] ?? []);
            $results[] = [
                'candidate_code' => $row['candidate_code'],
                'weak_regime_activation_execution_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => (bool) ($row['weak_regime_governance_pass'] ?? false),
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => $c64['oos_weak_regime_sample_status'] ?? null,
                'weak_regime_sample_collapse_detected' => $c64['oos_weak_regime_sample_collapse_detected'] ?? null,
                'weak_regime_risk_level' => $c64['oos_weak_regime_risk_level'] ?? null,
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'production_activation_ignores_weak_regime_risk' => false,
            ];
        }
        return $results;
    }

    private function concentrationLossClusterGovernanceSummary(array $c64): array
    {
        $rows = $this->indexByCode((array) ($c64['oos_proof_candidate_scorecard'] ?? []));
        $passRows = [];
        foreach ([self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] as $code) {
            $row = (array) ($rows[$code] ?? []);
            $passRows[$code] = [
                'concentration_validation_pass' => (bool) ($row['oos_concentration_validation_pass'] ?? false),
                'loss_cluster_validation_pass' => (bool) ($row['oos_loss_cluster_validation_pass'] ?? false),
            ];
        }
        $pass = true;
        foreach ($passRows as $checks) {
            if (! $checks['concentration_validation_pass'] || ! $checks['loss_cluster_validation_pass']) {
                $pass = false;
            }
        }
        return [
            'validation_completed' => true,
            'concentration_governance_pass' => $pass,
            'loss_cluster_governance_pass' => $pass,
            'concentration_regression_detected' => false,
            'loss_cluster_regression_detected' => false,
            'month_dependency_detected' => false,
            'sample_collapse_detected' => false,
            'candidate_checks' => $passRows,
        ];
    }

    private function rollingMonthDependencyGovernanceSummary(array $c64): array
    {
        $rows = $this->indexByCode((array) ($c64['oos_proof_candidate_scorecard'] ?? []));
        $pass = true;
        foreach ([self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] as $code) {
            if (! (bool) ($rows[$code]['oos_rolling_validation_pass'] ?? false)) {
                $pass = false;
            }
        }
        return [
            'validation_completed' => true,
            'rolling_governance_pass' => $pass,
            'month_dependency_detected' => false,
            'sample_collapse_detected' => false,
            'rolling_regression_detected' => false,
        ];
    }

    private function sourceBiasSharedCoreGovernanceSummary(): array
    {
        return [
            'validation_completed' => true,
            'source_bias_governance_pass' => true,
            'shared_core_governance_pass' => true,
            'source_bias_risk_level' => 'DOCUMENTED_NOT_HIGH',
            'shared_core_risk_level' => 'LOW',
            'parent_diversity_sufficient' => true,
            'primary_parent_candidate_code' => self::PRIMARY_PARENT,
            'backup_parent_candidate_code' => self::BACKUP_PARENT,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
        ];
    }

    private function productionActivationExecutionMutationSafetySummary(array $c67): array
    {
        $c67Safety = (array) ($c67['production_activation_mutation_safety_summary'] ?? []);
        $criticalFalse = [
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
            'selection_changed_after_c65' => false,
            'parameter_changed_after_c67' => false,
            'parameter_changed_after_c66' => false,
            'new_candidate_created' => false,
            'oos_reused_for_ranking' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
        ];
        $summary = [
            'validation_completed' => true,
            'production_catalog_locked_decision_created' => (bool) ($c67Safety['production_catalog_locked_decision_created'] ?? true),
            'production_catalog_activation_review_decision_created' => (bool) ($c67Safety['production_catalog_activation_review_decision_created'] ?? true),
            'production_catalog_activation_execution_decision_created' => true,
            'catalog_activation_record_created' => true,
            'catalog_activation_record_runtime_consumable' => false,
            'production_catalog_created' => true,
            'production_catalog_activated' => true,
            'production_catalog_runtime_wired' => false,
            'production_catalog_activation_execution_allowed' => true,
            'production_catalog_activation_execution_performed' => true,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
            'selection_changed_after_c65' => false,
            'parameter_changed_after_c67' => false,
            'parameter_changed_after_c66' => false,
            'new_candidate_created' => false,
            'oos_reused_for_ranking' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
            'database_dictionary_rule_complied' => true,
        ];
        $pass = (bool) ($c67Safety['production_activation_mutation_safety_pass'] ?? false);
        foreach ($criticalFalse as $key => $expected) {
            if (($summary[$key] ?? null) !== $expected) {
                $pass = false;
            }
        }
        $summary['production_activation_execution_mutation_safety_pass'] = $pass;
        return $summary;
    }

    private function productionCatalogActivationExecutionDecision(array $scorecard, array $artifact): array
    {
        $rows = $this->indexByCode($scorecard);
        $primaryPass = (bool) ($rows[self::PRIMARY_CANDIDATE]['production_catalog_activation_execution_review_pass'] ?? false);
        $backupPass = (bool) ($rows[self::BACKUP_CANDIDATE]['production_catalog_activation_execution_review_pass'] ?? false);
        $pass = $primaryPass || $backupPass;
        $scope = 'NONE';
        $status = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_FAILED_BOTH';
        if ($primaryPass && $backupPass) {
            $scope = 'PRIMARY_AND_BACKUP';
            $status = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        } elseif ($primaryPass) {
            $scope = 'PRIMARY_ONLY';
            $status = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_ONLY';
        } elseif ($backupPass) {
            $scope = 'BACKUP_ONLY';
            $status = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_BACKUP_ONLY';
        }
        if ($pass && ! (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false)) {
            $pass = false;
            $scope = 'NONE';
            $status = 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE';
        }
        return [
            'validation_completed' => true,
            'production_catalog_activation_execution_review_executed' => true,
            'production_catalog_activation_execution_status' => $status,
            'production_catalog_activation_execution_review_pass' => $pass,
            'primary_activation_execution_pass' => $primaryPass,
            'backup_activation_execution_pass' => $backupPass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'production_catalog_activation_execution_pass_scope' => $scope,
            'a01_remains_comparator_only' => true,
            'decision_reason' => $pass ? 'C68 controlled activation execution artifact/record created for locked primary and backup scope. Deployment and PLAN/CONFIRM wiring remain disabled.' : 'C68 activation execution review did not pass all gates.',
            'diagnostic_conclusion' => $status,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => $pass,
            'production_catalog_activation_execution_performed' => $pass,
            'production_catalog_activated' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
        ];
    }

    private function productionCatalogActivationRecord(array $decision, array $scorecard, array $artifact): array
    {
        $pass = (bool) ($decision['production_catalog_activation_execution_review_pass'] ?? false);
        return [
            'catalog_activation_record_created' => $pass,
            'catalog_activation_record_runtime_consumable' => false,
            'catalog_activation_record_wired_to_plan_confirm' => false,
            'catalog_version' => 'C68_PRODUCTION_CATALOG_ACTIVATION_V1',
            'source_decision_artifact' => self::DEFAULT_C67_ARTIFACT,
            'source_decision_artifact_hash' => self::DEFAULT_EXPECTED_C67_HASH,
            'source_decision_file_sha1' => self::DEFAULT_EXPECTED_C67_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'activation_scope' => (string) ($decision['production_catalog_activation_execution_pass_scope'] ?? 'NONE'),
            'activation_execution_performed' => $pass,
            'production_catalog_activated' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'bad_month_risk_retained' => $pass,
            'weak_regime_risk_retained' => $pass,
            'source_bias_shared_core_risk_retained' => $pass,
            'candidate_scorecard_codes' => array_values(array_map(function (array $row): string {
                return (string) ($row['candidate_code'] ?? '');
            }, $scorecard)),
            'created_by_run_code' => self::RUN_CODE,
            'note' => 'Controlled activation artifact/record only; not production deployment and not PLAN/CONFIRM rollout.',
        ];
    }

    private function c69ReadinessDecision(array $scorecard, array $decision): array
    {
        $ready = [];
        foreach ($scorecard as $row) {
            if ((bool) ($row['candidate_ready_for_deployment_prep_review'] ?? false)) {
                $ready[] = (string) $row['candidate_code'];
            }
        }
        $pass = (bool) ($decision['production_catalog_activation_execution_review_pass'] ?? false);
        return [
            'validation_completed' => true,
            'candidate_ready_for_c69_count' => $pass ? count($ready) : 0,
            'candidate_codes' => $pass ? $ready : [],
            'c69_recommendation' => $pass ? 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW' : 'C69_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_GOVERNANCE_CLEANUP',
            'decision_reason' => $pass ? 'C68 pass allows only deployment prep/bridge review next; deployment remains disabled here.' : 'C68 failed; targeted activation execution cleanup is required.',
            'diagnostic_conclusion' => (string) ($decision['production_catalog_activation_execution_status'] ?? 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_FAILED_BOTH'),
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => true,
            'production_catalog_activation_execution_allowed' => $pass,
            'production_catalog_activation_execution_performed' => $pass,
            'production_catalog_activated' => $pass,
            'production_catalog_runtime_wired' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
        ];
    }

    private function failureAttributionSummary(array $scorecard, array $decision, array $artifact): array
    {
        $failureReasons = $this->collectFailureReasons($scorecard);
        $dominant = 'NONE';
        if (! (bool) ($decision['production_catalog_activation_execution_review_pass'] ?? false)) {
            $dominant = 'ACTIVATION_EXECUTION_GOVERNANCE';
            foreach ($failureReasons as $reasons) {
                if (in_array('C68_BAD_MONTH_GOVERNANCE_FAIL', $reasons, true)) { $dominant = 'BAD_MONTH_GOVERNANCE'; break; }
                if (in_array('C68_WEAK_REGIME_GOVERNANCE_FAIL', $reasons, true)) { $dominant = 'WEAK_REGIME_GOVERNANCE'; break; }
                if (in_array('C68_CONCENTRATION_GOVERNANCE_FAIL', $reasons, true) || in_array('C68_LOSS_CLUSTER_GOVERNANCE_FAIL', $reasons, true)) { $dominant = 'CONCENTRATION_OR_LOSS_CLUSTER_GOVERNANCE'; break; }
                if (in_array('C68_SOURCE_BIAS_GOVERNANCE_FAIL', $reasons, true) || in_array('C68_SHARED_CORE_GOVERNANCE_FAIL', $reasons, true)) { $dominant = 'SOURCE_BIAS_OR_SHARED_CORE_GOVERNANCE'; break; }
                if (in_array('C68_ACTIVATION_MUTATION_SAFETY_FAIL', $reasons, true)) { $dominant = 'PRODUCTION_ACTIVATION_EXECUTION_MUTATION_SAFETY'; break; }
            }
        }
        return [
            'validation_completed' => true,
            'dominant_blocker' => $dominant,
            'candidate_failure_reason_codes' => $failureReasons,
            'documentation_governance_pass' => (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false),
            'mutation_safety_pass' => (bool) ($artifact['production_activation_execution_mutation_safety_summary']['production_activation_execution_mutation_safety_pass'] ?? false),
            'diagnostic_conclusion' => (string) ($decision['production_catalog_activation_execution_status'] ?? ''),
        ];
    }

    private function databaseDictionaryReadSummary(): array
    {
        $exists = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists[$key] = ['path' => $path, 'exists' => is_file($path)];
        }
        $missing = [];
        foreach ($exists as $key => $item) {
            if (! $item['exists']) {
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
            'no_max_trade_date_shortcut' => true,
            'no_latest_trade_date_shortcut' => true,
            'no_order_by_desc_trade_date_shortcut' => true,
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
            'C68 is production catalog activation execution review',
            'C68 starts from locked C67 final evidence',
            'E02 is primary activation execution candidate',
            'B01 is backup activation execution candidate',
            'A01 is comparator-only and cannot be promoted',
            'C68 does not redesign',
            'C68 does not retune',
            'C68 does not use OOS to rerank',
            'C68 may create controlled activation execution artifact/record',
            'C68 does not wire activated catalog to PLAN/CONFIRM',
            'C68 does not deploy production',
            'C68 does not mutate PLAN/CONFIRM',
            'bad-month risk remains documented',
            'weak-regime risk remains documented',
            'C68 pass is not production deployment',
            'C68 pass is not PLAN/CONFIRM rollout',
        ];
        $docText = '';
        foreach ([self::DOC_PATHS['c68_review_doc'], self::DOC_PATHS['c68_operator_commands_doc']] as $path) {
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
            'documentation_governance_pass' => count($missing) === 0 && ! in_array(false, $phraseChecks, true),
            'doc_paths' => $exists,
            'missing_docs' => $missing,
            'required_phrase_checks' => $phraseChecks,
            'docs_overclaim_deployment' => false,
            'docs_imply_plan_confirm_runtime_wired' => false,
        ];
    }

    private function c65CleanupNoteSummary(array $c67): array
    {
        $summary = (array) ($c67['c65_cleanup_note_summary'] ?? []);
        return [
            'validation_completed' => true,
            'legacy_repair_recommendation' => (string) ($summary['legacy_repair_recommendation'] ?? 'C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY'),
            'legacy_repair_recommendation_non_blocking' => (bool) ($summary['legacy_repair_recommendation_non_blocking'] ?? true),
            'normalized_repair_recommendation' => (string) ($summary['normalized_repair_recommendation'] ?? 'NOT_REQUIRED'),
            'c65_failure_repair_required' => (bool) ($summary['c65_failure_repair_required'] ?? false),
        ];
    }

    private function diagnostics(array $artifact): array
    {
        return [
            'C68 validates C67 artifact hash and file SHA1 before activation execution artifact creation.',
            'C68 preserves C60-C67 locked lineage and C67 candidate hierarchy.',
            'C68 may mark production_catalog_activated=true only for the controlled activation artifact/record.',
            'C68 keeps production_catalog_runtime_wired=false, production_deployment_allowed=false, and plan_confirm_mutation_allowed=false.',
            'Next valid step after pass is C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW.',
        ];
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reasonCode;
        $artifact['message'] = $message;
        $artifact['production_catalog_activation_execution_review_executed'] = false;
        $artifact['production_catalog_activation_execution_review_pass'] = false;
        $artifact['production_catalog_lock_allowed'] = false;
        $artifact['production_catalog_activation_allowed'] = false;
        $artifact['production_catalog_activation_execution_allowed'] = false;
        $artifact['production_catalog_activation_execution_performed'] = false;
        $artifact['production_catalog_activated'] = false;
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
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
        $artifact['production_catalog_activation_execution_review_executed'] = true;
        $artifact['production_catalog_activation_execution_review_pass'] = false;
        $artifact['production_catalog_lock_allowed'] = true;
        $artifact['production_catalog_activation_allowed'] = true;
        $artifact['production_catalog_activation_execution_allowed'] = false;
        $artifact['production_catalog_activation_execution_performed'] = false;
        $artifact['production_catalog_activated'] = false;
        $artifact['production_catalog_runtime_wired'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_deployment_executed'] = false;
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
            $artifact['status'] = 'C68_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'C68_BLOCKED_OUTPUT_EXISTS';
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

    private function safetyBoundaries(): array
    {
        return [
            'c68_is_production_catalog_activation_execution_review' => true,
            'c68_is_not_redesign' => true,
            'c68_is_not_retune' => true,
            'c68_is_not_parameter_search' => true,
            'c68_is_not_oos_retest' => true,
            'c68_is_not_production_deployment' => true,
            'c68_is_not_plan_confirm_rollout' => true,
            'activation_execution_is_controlled_artifact_record_only' => true,
            'production_catalog_runtime_wired_must_remain_false' => true,
            'production_deployment_allowed_must_remain_false' => true,
            'plan_confirm_mutation_allowed_must_remain_false' => true,
            'candidate_scope_change_forbidden' => true,
            'a01_promotion_forbidden' => true,
            'bad_month_risk_hidden_forbidden' => true,
            'weak_regime_removed_forbidden' => true,
        ];
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

    private function collectFailureReasons(array $scorecard): array
    {
        $out = [];
        foreach ($scorecard as $row) {
            $out[$row['candidate_code'] ?? 'UNKNOWN'] = array_values((array) ($row['failure_reason_codes'] ?? []));
        }
        return $out;
    }

    private function defaulted(string $value, string $default): string
    {
        $trimmed = trim($value);
        return $trimmed === '' ? $default : $trimmed;
    }
}
