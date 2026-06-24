<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC67ProductionCatalogActivationReviewService
{
    public const RUN_CODE = 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW';
    public const ARTIFACT_TYPE = 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW';

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

    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c67-production-catalog-activation-review.json';
    public const DEFAULT_OOS_FROM = '2025-05-22';
    public const DEFAULT_OOS_TO = '2026-05-29';

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
        'c67_review_doc' => 'docs/watchlist/audit/WS_C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW.md',
        'c67_operator_commands_doc' => 'docs/watchlist/audit/WS_C67_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    /**
     * C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW. ACTIVATION_REVIEW_DECISION_ARTIFACT_ONLY.
     * NOT_REDESIGN. NOT_RETUNE. NOT_PARAMETER_SEARCH. NOT_OOS_RETEST. NOT_LIVE_ACTIVATION.
     * C66_ARTIFACT_HASH_LOCK. C66_FILE_SHA1_LOCK. C60_TO_C66_LINEAGE_LOCK.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. ASOF_SAFE_LOOKUP_REQUIRED.
     * SELECTION_SCOPE_FROZEN_FROM_C66. NO_OOS_BASED_RERANKING. NO_OOS_TIE_BREAK.
     * A01_COMPARATOR_ONLY_NOT_PROMOTABLE. BAD_MONTH_RISK_RETAINED. WEAK_REGIME_RISK_RETAINED.
     * NO_PRODUCTION_CATALOG_CREATION. NO_PRODUCTION_CATALOG_ACTIVATION_EXECUTION. NO_DEPLOYMENT.
     * NO_PLAN_CONFIRM_MUTATION. ACTIVATION_EXECUTION_ALLOWED_FALSE. DEPLOYMENT_ALLOWED_FALSE.
     * PLAN_CONFIRM_MUTATION_ALLOWED_FALSE. NO_LATEST_DATE_SHORTCUT. NO_DATE_DESC_SHORTCUT.
     * NO_FUTURE_LOOKUP. NO_RETURN_FIELDS_FOR_SELECTION. C67_CAN_ONLY_RECOMMEND_C68_EXECUTION_REVIEW.
     */
    public function execute(
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
            return $this->blocked($artifact, 'C67_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'WS_BT_C67_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C67 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath, $overwrite);
        }

        $c66Load = $this->loadArtifactLock($c66Artifact, $expectedC66Hash, $expectedC66FileSha1);
        $this->copyLock($artifact, 'c66', $c66Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);
        if (! $c66Load['readable']) {
            return $this->blocked($artifact, 'C67_BLOCKED_MISSING_C66_ARTIFACT', 'WS_BT_C67_C66_ARTIFACT_MISSING', 'C67 requires the locked C66 artifact.', $outputPath, $overwrite);
        }
        if (! $c66Load['hash_match']) {
            return $this->blocked($artifact, 'C67_BLOCKED_C66_ARTIFACT_LOCK_MISMATCH', 'WS_BT_C67_C66_ARTIFACT_HASH_MISMATCH', 'C66 artifact hash does not match the expected C67 lock.', $outputPath, $overwrite);
        }
        if (! $c66Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C67_BLOCKED_C66_FILE_SHA1_LOCK_MISMATCH', 'WS_BT_C67_C66_FILE_SHA1_MISMATCH', 'C66 file SHA1 does not match the expected C67 lock.', $outputPath, $overwrite);
        }

        $c66 = (array) $c66Load['payload'];
        $c66Validation = $this->validateC66($c66);
        $artifact['c66_lock_validation_summary'] = $c66Validation;
        if (! (bool) ($c66Validation['pass'] ?? false)) {
            return $this->blocked($artifact, (string) ($c66Validation['status'] ?? 'C67_BLOCKED_C66_STATUS_OR_REASON_MISMATCH'), (string) ($c66Validation['reason_code'] ?? 'WS_BT_C67_C66_LOCK_INVALID'), (string) ($c66Validation['message'] ?? 'C66 lock is invalid for C67.'), $outputPath, $overwrite);
        }

        $lineageLoads = [
            'c65' => $this->loadArtifactLock($c65Artifact, $expectedC65Hash, $expectedC65FileSha1),
            'c64' => $this->loadArtifactLock($c64Artifact, $expectedC64Hash, $expectedC64FileSha1),
            'c63' => $this->loadArtifactLock($c63Artifact, $expectedC63Hash, $expectedC63FileSha1),
            'c62' => $this->loadArtifactLock($c62Artifact, $expectedC62Hash, $expectedC62FileSha1),
            'c61' => $this->loadArtifactLock($c61Artifact, $expectedC61Hash, $expectedC61FileSha1),
            'c60' => $this->loadArtifactLock($c60Artifact, $expectedC60Hash, $expectedC60FileSha1),
        ];
        foreach ($lineageLoads as $prefix => $lock) {
            $this->copyLock($artifact, $prefix, $lock);
        }
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        $artifact['c65_lineage_validation_summary'] = $this->validateC65Lineage($lineageLoads['c65']);
        $artifact['c64_lineage_validation_summary'] = $this->validateC64Lineage($lineageLoads['c64']);
        $artifact['c63_lineage_validation_summary'] = $this->validateC63Lineage($lineageLoads['c63']);
        $artifact['c62_lineage_validation_summary'] = $this->validateC62Lineage($lineageLoads['c62']);
        $artifact['c61_lineage_validation_summary'] = $this->validateC61Lineage($lineageLoads['c61']);
        $artifact['c60_lineage_validation_summary'] = $this->validateC60Lineage($lineageLoads['c60']);

        foreach (['c65', 'c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
            if (! (bool) ($artifact[$prefix.'_lineage_validation_summary']['pass'] ?? false)) {
                return $this->blocked($artifact, 'C67_BLOCKED_LINEAGE_LOCK_MISMATCH', (string) ($artifact[$prefix.'_lineage_validation_summary']['reason_code'] ?? 'WS_BT_C67_LINEAGE_LOCK_MISMATCH'), strtoupper($prefix).' lineage lock is invalid for C67.', $outputPath, $overwrite);
            }
        }

        $scope = $this->candidateScopeFreezeSummary($c66);
        $artifact['candidate_scope_freeze_summary'] = $scope;
        if (! (bool) ($scope['candidate_scope_freeze_completed'] ?? false)) {
            return $this->rejected($artifact, 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'WS_BT_C67_CANDIDATE_SCOPE_MISMATCH', 'C67 candidate scope does not match locked C66 hierarchy.', $outputPath, $overwrite);
        }

        $artifact['concentration_loss_cluster_governance_summary'] = $this->concentrationLossClusterGovernanceSummary($c66);
        $artifact['rolling_month_dependency_governance_summary'] = $this->rollingMonthDependencyGovernanceSummary($c66);
        $artifact['source_bias_shared_core_governance_summary'] = $this->sourceBiasSharedCoreGovernanceSummary($c66);
        $artifact['production_activation_mutation_safety_summary'] = $this->productionActivationMutationSafetySummary($c66, $scope, $dictionary);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c66);

        $scorecard = $this->productionCatalogActivationCandidateScorecard($c66, $artifact);
        $artifact['production_catalog_activation_candidate_scorecard'] = $scorecard;
        $artifact['bad_month_activation_review_results'] = $this->badMonthActivationReviewResults($scorecard);
        $artifact['weak_regime_activation_review_results'] = $this->weakRegimeActivationReviewResults($scorecard);

        $decision = $this->productionCatalogActivationReviewDecision($scorecard, $artifact);
        $artifact['production_catalog_activation_review_decision'] = $decision;
        $artifact['c68_readiness_decision'] = $this->c68ReadinessDecision($scorecard, $decision);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($scorecard, $decision, $artifact);
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        $artifact['status'] = (string) ($decision['production_catalog_activation_review_status'] ?? 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_FAILED_BOTH');
        $artifact['reason_code'] = $artifact['status'];
        $artifact['production_catalog_activation_review_executed'] = true;
        $artifact['production_catalog_activation_review_pass'] = (bool) ($decision['production_catalog_activation_review_pass'] ?? false);
        $artifact['production_catalog_lock_allowed'] = true;
        $artifact['production_catalog_activation_allowed'] = (bool) ($decision['production_catalog_activation_allowed'] ?? false);
        $artifact['production_catalog_activation_execution_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $artifact['status'];
        $artifact['next_step_recommendation'] = (string) ($artifact['c68_readiness_decision']['c68_recommendation'] ?? 'C68_PRODUCTION_CATALOG_ACTIVATION_GOVERNANCE_CLEANUP');

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(
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
        $artifact = [
            'run_code' => self::RUN_CODE,
            'status' => 'C67_NOT_RUN',
            'reason_code' => 'C67_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'production_catalog_activation_review_executed' => false,
            'production_catalog_activation_review_pass' => false,
            'production_catalog_lock_allowed' => false,
            'production_catalog_activation_allowed' => false,
            'production_catalog_activation_execution_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'created_at' => $executedAt,
            'safety_boundaries' => $this->safetyBoundaries(),
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c66_lock_validation_summary' => [],
            'c65_lineage_validation_summary' => [],
            'c64_lineage_validation_summary' => [],
            'c63_lineage_validation_summary' => [],
            'c62_lineage_validation_summary' => [],
            'c61_lineage_validation_summary' => [],
            'c60_lineage_validation_summary' => [],
            'candidate_scope_freeze_summary' => [],
            'production_catalog_activation_candidate_scorecard' => [],
            'bad_month_activation_review_results' => [],
            'weak_regime_activation_review_results' => [],
            'concentration_loss_cluster_governance_summary' => [],
            'rolling_month_dependency_governance_summary' => [],
            'source_bias_shared_core_governance_summary' => [],
            'production_activation_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c65_cleanup_note_summary' => [],
            'production_catalog_activation_review_decision' => [],
            'c68_readiness_decision' => [],
            'failure_attribution_summary' => [],
            'diagnostics' => [],
        ];

        foreach ([
            'c66' => [$c66Artifact, $expectedC66Hash, $expectedC66FileSha1],
            'c65' => [$c65Artifact, $expectedC65Hash, $expectedC65FileSha1],
            'c64' => [$c64Artifact, $expectedC64Hash, $expectedC64FileSha1],
            'c63' => [$c63Artifact, $expectedC63Hash, $expectedC63FileSha1],
            'c62' => [$c62Artifact, $expectedC62Hash, $expectedC62FileSha1],
            'c61' => [$c61Artifact, $expectedC61Hash, $expectedC61FileSha1],
            'c60' => [$c60Artifact, $expectedC60Hash, $expectedC60FileSha1],
        ] as $prefix => $values) {
            $artifact['input_'.$prefix.'_artifact'] = $values[0];
            $artifact['expected_'.$prefix.'_hash'] = $values[1];
            $artifact['expected_'.$prefix.'_file_sha1'] = strtoupper($values[2]);
            $artifact['actual_'.$prefix.'_hash'] = null;
            $artifact['actual_'.$prefix.'_file_sha1'] = null;
            $artifact[$prefix.'_hash_match'] = false;
            $artifact[$prefix.'_file_sha1_match'] = false;
            $artifact[$prefix.'_status'] = null;
            $artifact[$prefix.'_reason_code'] = null;
        }

        return $artifact;
    }

    private function loadArtifactLock(string $artifactPath, string $expectedHash, string $expectedFileSha1): array
    {
        $result = [
            'path' => $artifactPath,
            'expected_hash' => $expectedHash,
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_hash' => null,
            'actual_file_sha1' => null,
            'hash_match' => false,
            'file_sha1_match' => false,
            'readable' => false,
            'payload' => [],
        ];

        if (! is_file($artifactPath)) {
            return $result;
        }

        $raw = file_get_contents($artifactPath);
        $payload = json_decode((string) $raw, true);
        if (! is_array($payload)) {
            return $result;
        }

        $actualHash = (string) ($payload['artifact_hash'] ?? '');
        $actualFileSha1 = strtoupper(sha1_file($artifactPath) ?: '');
        $result['readable'] = true;
        $result['payload'] = $payload;
        $result['actual_hash'] = $actualHash;
        $result['actual_file_sha1'] = $actualFileSha1;
        $result['hash_match'] = strtolower($actualHash) === strtolower($expectedHash);
        $result['file_sha1_match'] = strtoupper($actualFileSha1) === strtoupper($expectedFileSha1);

        return $result;
    }

    private function copyLock(array &$artifact, string $prefix, array $lock): void
    {
        $artifact['actual_'.$prefix.'_hash'] = $lock['actual_hash'];
        $artifact['actual_'.$prefix.'_file_sha1'] = $lock['actual_file_sha1'];
        $artifact[$prefix.'_hash_match'] = (bool) $lock['hash_match'];
        $artifact[$prefix.'_file_sha1_match'] = (bool) $lock['file_sha1_match'];
        if ($lock['readable']) {
            $artifact[$prefix.'_status'] = $lock['payload']['status'] ?? null;
            $artifact[$prefix.'_reason_code'] = $lock['payload']['reason_code'] ?? null;
        }
    }

    private function sourceArtifactLocks(array $artifact): array
    {
        $out = [];
        foreach (['c66', 'c65', 'c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
            $out[$prefix.'_artifact_path'] = $artifact['input_'.$prefix.'_artifact'];
            $out['expected_'.$prefix.'_hash'] = $artifact['expected_'.$prefix.'_hash'];
            $out['actual_'.$prefix.'_hash'] = $artifact['actual_'.$prefix.'_hash'];
            $out[$prefix.'_hash_match'] = (bool) $artifact[$prefix.'_hash_match'];
            $out['expected_'.$prefix.'_file_sha1'] = $artifact['expected_'.$prefix.'_file_sha1'];
            $out['actual_'.$prefix.'_file_sha1'] = $artifact['actual_'.$prefix.'_file_sha1'];
            $out[$prefix.'_file_sha1_match'] = (bool) $artifact[$prefix.'_file_sha1_match'];
        }
        return $out;
    }

    private function databaseDictionaryReadSummary(): array
    {
        $paths = [];
        $missing = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists, 'bytes' => $exists ? filesize($path) : 0];
            if (! $exists) {
                $missing[] = $path;
            }
        }

        return [
            'validation_completed' => true,
            'dictionary_rule_acknowledged' => true,
            'dictionary_read_rule_complied' => $missing === [],
            'dictionary_paths' => $paths,
            'missing_dictionary_paths' => $missing,
            'dictionary_missing_coverage_detected' => $missing !== [],
            'asof_safe' => true,
            'latest_shortcut_forbidden' => true,
            'max_date_shortcut_forbidden' => true,
            'future_lookup_forbidden' => true,
            'return_path_selection_forbidden' => true,
            'market_index_mapping' => [
                'market_index_roc20_source' => 'market_benchmark_indicators.roc_20',
                'market_index_ma20_slope_pct_source' => 'market_benchmark_indicators.ma20_slope_pct',
                'market_index_benchmark_code' => 'IHSG',
                'calendar_date_key' => 'market_calendar.cal_date',
            ],
        ];
    }

    private function validateC66(array $c66): array
    {
        if (($c66['status'] ?? null) !== 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C67_BLOCKED_C66_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C67_C66_STATUS_INVALID', 'message' => 'C66 status is not the locked primary+backup production lock pass.'];
        }
        if (($c66['reason_code'] ?? null) !== 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C67_BLOCKED_C66_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C67_C66_REASON_INVALID', 'message' => 'C66 reason_code is not the locked primary+backup production lock pass.'];
        }
        if (($c66['production_lock_review_pass'] ?? null) !== true) {
            return ['pass' => false, 'status' => 'C67_BLOCKED_C66_PRODUCTION_LOCK_NOT_PASSED', 'reason_code' => 'WS_BT_C67_C66_LOCK_NOT_PASSED', 'message' => 'C66 production lock did not pass.'];
        }
        if (($c66['production_catalog_lock_allowed'] ?? null) !== true) {
            return ['pass' => false, 'status' => 'C67_BLOCKED_C66_PRODUCTION_CATALOG_LOCK_NOT_ALLOWED', 'reason_code' => 'WS_BT_C67_C66_CATALOG_LOCK_NOT_ALLOWED', 'message' => 'C66 production catalog lock is not allowed.'];
        }
        if ((int) ($c66['c67_readiness_decision']['candidate_ready_for_c67_count'] ?? -1) !== 2) {
            return ['pass' => false, 'status' => 'C67_BLOCKED_C66_C67_READINESS_COUNT_MISMATCH', 'reason_code' => 'WS_BT_C67_C66_READINESS_COUNT_INVALID', 'message' => 'C66 candidate_ready_for_c67_count must equal 2.'];
        }
        if (($c66['production_catalog_activation_allowed'] ?? null) !== false) {
            return ['pass' => false, 'status' => 'C67_BLOCKED_C66_ACTIVATION_FLAG_INVALID', 'reason_code' => 'WS_BT_C67_C66_ACTIVATION_FLAG_INVALID', 'message' => 'C66 must keep production_catalog_activation_allowed=false.'];
        }
        if (($c66['production_deployment_allowed'] ?? null) !== false) {
            return ['pass' => false, 'status' => 'C67_BLOCKED_C66_DEPLOYMENT_FLAG_INVALID', 'reason_code' => 'WS_BT_C67_C66_DEPLOYMENT_FLAG_INVALID', 'message' => 'C66 must keep production_deployment_allowed=false.'];
        }
        if (($c66['plan_confirm_mutation_allowed'] ?? null) !== false) {
            return ['pass' => false, 'status' => 'C67_BLOCKED_C66_PLAN_CONFIRM_MUTATION_FLAG_INVALID', 'reason_code' => 'WS_BT_C67_C66_PLAN_CONFIRM_FLAG_INVALID', 'message' => 'C66 must keep plan_confirm_mutation_allowed=false.'];
        }
        $decision = (array) ($c66['production_lock_decision'] ?? []);
        if (($decision['production_lock_pass_scope'] ?? null) !== 'PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_LOCK_INCOMPLETE', 'reason_code' => 'WS_BT_C67_C66_LOCK_SCOPE_INVALID', 'message' => 'C66 production lock pass scope must be PRIMARY_AND_BACKUP.'];
        }
        $safety = (array) ($c66['production_mutation_safety_summary'] ?? []);
        foreach (['production_catalog_created', 'production_catalog_activated', 'production_deployment_executed', 'plan_confirm_mutated', 'latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection'] as $field) {
            if (($safety[$field] ?? null) !== false) {
                return ['pass' => false, 'status' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_MUTATION', 'reason_code' => 'WS_BT_C67_C66_SAFETY_FLAG_INVALID', 'message' => 'C66 production mutation safety flags must be clean.'];
            }
        }
        if (($safety['production_mutation_safety_pass'] ?? null) !== true) {
            return ['pass' => false, 'status' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_MUTATION', 'reason_code' => 'WS_BT_C67_C66_SAFETY_SUMMARY_INVALID', 'message' => 'C66 production mutation safety summary did not pass.'];
        }

        return [
            'pass' => true,
            'validation_completed' => true,
            'status_match' => true,
            'reason_code_match' => true,
            'production_lock_review_pass' => true,
            'production_catalog_lock_allowed' => true,
            'candidate_ready_for_c67_count' => 2,
            'production_catalog_activation_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'production_lock_pass_scope' => 'PRIMARY_AND_BACKUP',
            'production_mutation_safety_pass' => true,
        ];
    }

    private function validateC65Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP', 'WS_BT_C67_C65_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return ($payload['production_prelock_review_pass'] ?? null) === true
                && (int) ($payload['c66_readiness_decision']['candidate_ready_for_c66_count'] ?? -1) === 2
                && ($payload['production_ready'] ?? null) === false
                && ($payload['production_catalog_allowed'] ?? null) === false
                && ($payload['production_deployment_allowed'] ?? null) === false;
        });
    }

    private function validateC64Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP', 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP', 'WS_BT_C67_C64_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return ($payload['oos_proof_pass'] ?? null) === true
                && (int) ($payload['c65_readiness_decision']['candidate_ready_for_c65_count'] ?? -1) === 2
                && ($payload['oos_proof_decision']['oos_pass_scope'] ?? null) === 'PRIMARY_AND_BACKUP'
                && ($payload['production_ready'] ?? null) === false
                && ($payload['direct_oos_proof_recommended'] ?? null) === false
                && ($payload['oos_proof_unlocked'] ?? null) === false
                && ($payload['pre_oos_unlocked'] ?? null) === false;
        });
    }

    private function validateC63Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', 'WS_BT_C67_C63_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return (int) ($payload['c64_readiness_decision']['candidate_ready_for_c64_count'] ?? -1) === 2
                && ($payload['production_ready'] ?? null) === false
                && ($payload['direct_oos_proof_recommended'] ?? null) === false
                && ($payload['oos_proof_unlocked'] ?? null) === false
                && ($payload['pre_oos_unlocked'] ?? null) === false;
        });
    }

    private function validateC62Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', 'WS_BT_C67_C62_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return (int) ($payload['c63_readiness_decision']['candidate_ready_for_c63_count'] ?? -1) === 2
                && ($payload['c63_readiness_decision']['c63_recommendation'] ?? null) === 'C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY'
                && ($payload['production_ready'] ?? null) === false
                && ($payload['direct_oos_proof_recommended'] ?? null) === false
                && ($payload['oos_proof_unlocked'] ?? null) === false
                && ($payload['pre_oos_unlocked'] ?? null) === false;
        });
    }

    private function validateC61Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED', 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE', 'WS_BT_C67_C61_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return (int) ($payload['c62_readiness_decision']['candidate_ready_for_c62_count'] ?? -1) === 3
                && ($payload['production_ready'] ?? null) === false
                && ($payload['direct_oos_proof_recommended'] ?? null) === false
                && ($payload['oos_proof_unlocked'] ?? null) === false;
        });
    }

    private function validateC60Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED', 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS', 'WS_BT_C67_C60_LINEAGE_LOCK_MISMATCH');
    }

    private function validateLineageLock(array $lock, string $expectedStatus, string $expectedReason, string $reasonCode, ?callable $extra = null): array
    {
        if (! (bool) ($lock['readable'] ?? false) || ! (bool) ($lock['hash_match'] ?? false) || ! (bool) ($lock['file_sha1_match'] ?? false)) {
            return ['pass' => false, 'reason_code' => $reasonCode, 'message' => 'Lineage artifact lock hash or file SHA1 mismatch.'];
        }
        $payload = (array) ($lock['payload'] ?? []);
        if (($payload['status'] ?? null) !== $expectedStatus || ($payload['reason_code'] ?? null) !== $expectedReason) {
            return ['pass' => false, 'reason_code' => $reasonCode, 'message' => 'Lineage artifact status/reason_code mismatch.'];
        }
        if ($extra !== null && ! $extra($payload)) {
            return ['pass' => false, 'reason_code' => $reasonCode, 'message' => 'Lineage artifact readiness/safety fields mismatch.'];
        }
        return [
            'pass' => true,
            'validation_completed' => true,
            'artifact_path' => $lock['path'],
            'artifact_hash_match' => true,
            'file_sha1_match' => true,
            'status_match' => true,
            'reason_code_match' => true,
        ];
    }

    private function candidateScopeFreezeSummary(array $c66): array
    {
        $decision = (array) ($c66['production_lock_decision'] ?? []);
        $freeze = (array) ($c66['candidate_scope_freeze_summary'] ?? []);
        $primary = (string) ($decision['primary_candidate_code'] ?? $freeze['primary_candidate_code'] ?? '');
        $backup = array_values((array) ($decision['backup_candidate_codes'] ?? $freeze['backup_candidate_codes'] ?? []));
        $comparator = array_values((array) ($decision['comparator_only_candidate_codes'] ?? $freeze['comparator_only_candidate_codes'] ?? []));
        $valid = $primary === self::PRIMARY_CANDIDATE
            && $backup === [self::BACKUP_CANDIDATE]
            && $comparator === [self::COMPARATOR_CANDIDATE]
            && ! (bool) ($freeze['candidate_scope_changed_after_c65'] ?? false)
            && ! (bool) ($freeze['new_candidate_created'] ?? true)
            && ! (bool) ($freeze['selection_rule_changed'] ?? true)
            && ! (bool) ($freeze['parameter_changed'] ?? true)
            && ! (bool) ($freeze['oos_result_used_for_new_ranking'] ?? true)
            && ! (bool) ($freeze['a01_promoted'] ?? true);

        return [
            'candidate_scope_freeze_completed' => $valid,
            'candidate_scope_source' => 'C66_LOCKED_PRODUCTION_CATALOG_DECISION',
            'primary_candidate_code' => $primary,
            'backup_candidate_codes' => $backup,
            'comparator_only_candidate_codes' => $comparator,
            'candidate_scope_changed_after_c66' => false,
            'candidate_scope_changed_after_c65' => (bool) ($freeze['candidate_scope_changed_after_c65'] ?? false),
            'new_candidate_created' => (bool) ($freeze['new_candidate_created'] ?? true),
            'selection_rule_changed' => (bool) ($freeze['selection_rule_changed'] ?? true),
            'parameter_changed' => (bool) ($freeze['parameter_changed'] ?? true),
            'oos_result_used_for_new_ranking' => (bool) ($freeze['oos_result_used_for_new_ranking'] ?? true),
            'a01_promoted' => (bool) ($freeze['a01_promoted'] ?? true),
        ];
    }

    private function productionCatalogActivationCandidateScorecard(array $c66, array $artifact): array
    {
        $rows = $this->indexByCode((array) ($c66['production_lock_candidate_scorecard'] ?? []));
        return [
            $this->scorecardRow($rows[self::PRIMARY_CANDIDATE] ?? [], self::PRIMARY_CANDIDATE, 'primary_production_catalog_activation_review_candidate', self::PRIMARY_PARENT, $artifact),
            $this->scorecardRow($rows[self::BACKUP_CANDIDATE] ?? [], self::BACKUP_CANDIDATE, 'backup_production_catalog_activation_review_candidate', self::BACKUP_PARENT, $artifact),
            $this->scorecardRow($rows[self::COMPARATOR_CANDIDATE] ?? [], self::COMPARATOR_CANDIDATE, 'comparator_only', self::COMPARATOR_PARENT, $artifact),
        ];
    }

    private function scorecardRow(array $source, string $candidate, string $role, string $parent, array $artifact): array
    {
        $oos = (array) ($source['c64_oos_evidence_summary'] ?? []);
        $c66Summary = [
            'c66_role' => (string) ($source['c66_role'] ?? ''),
            'production_lock_review_pass' => (bool) ($source['production_lock_review_pass'] ?? false),
            'candidate_locked_for_production_catalog' => (bool) ($source['candidate_locked_for_production_catalog'] ?? false),
            'production_catalog_lock_allowed' => (bool) ($source['production_catalog_lock_allowed'] ?? false),
            'production_catalog_activation_allowed' => (bool) ($source['production_catalog_activation_allowed'] ?? false),
            'production_deployment_allowed' => (bool) ($source['production_deployment_allowed'] ?? false),
            'plan_confirm_mutation_allowed' => (bool) ($source['plan_confirm_mutation_allowed'] ?? false),
        ];
        $c65Summary = (array) ($source['c65_prelock_evidence_summary'] ?? []);
        $badMonthPass = ($oos['oos_bad_month_decision'] ?? null) === 'PASS_WITH_DOCUMENTED_RISK'
            && ($oos['oos_bad_month_risk_level'] ?? null) === 'MODERATE'
            && ! empty($oos['oos_worst_month'])
            && ! empty($oos['oos_worst_month_regime'])
            && (bool) ($source['bad_month_governance_pass'] ?? false);
        $weakRegimePass = ($oos['oos_weak_regime_sample_status'] ?? null) === 'SUFFICIENT'
            && ($oos['oos_weak_regime_sample_collapse_detected'] ?? null) === false
            && ($oos['oos_weak_regime_risk_level'] ?? null) === 'MODERATE'
            && (bool) ($source['weak_regime_governance_pass'] ?? false);
        $concentrationPass = (bool) ($oos['oos_concentration_validation_pass'] ?? false) && (bool) ($source['concentration_governance_pass'] ?? false);
        $lossClusterPass = (bool) ($oos['oos_loss_cluster_validation_pass'] ?? false) && (bool) ($source['loss_cluster_governance_pass'] ?? false);
        $rollingPass = (bool) ($oos['oos_rolling_validation_pass'] ?? false) && (bool) ($source['rolling_governance_pass'] ?? false);
        $sourceBiasRisk = (string) ($source['source_bias_risk_level'] ?? $oos['oos_source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH');
        $sharedCoreRisk = (string) ($source['shared_core_risk_level'] ?? $oos['oos_shared_core_risk_level'] ?? 'LOW');
        $sourceBiasPass = (bool) ($oos['oos_source_bias_validation_pass'] ?? false) && (bool) ($source['source_bias_governance_pass'] ?? false) && $sourceBiasRisk !== 'HIGH';
        $sharedCorePass = (bool) ($oos['oos_shared_core_validation_pass'] ?? false) && (bool) ($source['shared_core_governance_pass'] ?? false) && $sharedCoreRisk !== 'HIGH';
        $safetyPass = (bool) ($oos['oos_safety_and_leakage_pass'] ?? false) && (bool) ($source['safety_and_leakage_governance_pass'] ?? false);
        $productionMutationSafetyPass = (bool) ($artifact['production_activation_mutation_safety_summary']['production_activation_mutation_safety_pass'] ?? false);
        $planPass = (bool) ($source['plan_confirm_non_mutation_pass'] ?? false) && $c66Summary['plan_confirm_mutation_allowed'] === false;
        $nonExecutionPass = ! (bool) ($artifact['production_activation_mutation_safety_summary']['production_catalog_activated'] ?? true)
            && ! (bool) ($artifact['production_activation_mutation_safety_summary']['production_catalog_activation_execution_allowed'] ?? true);
        $c66LockOk = $role !== 'comparator_only'
            && $c66Summary['production_lock_review_pass'] === true
            && $c66Summary['candidate_locked_for_production_catalog'] === true
            && $c66Summary['production_catalog_lock_allowed'] === true
            && $c66Summary['production_catalog_activation_allowed'] === false
            && $c66Summary['production_deployment_allowed'] === false
            && $c66Summary['plan_confirm_mutation_allowed'] === false;

        $ready = $c66LockOk
            && $badMonthPass
            && $weakRegimePass
            && $concentrationPass
            && $lossClusterPass
            && $rollingPass
            && $sourceBiasPass
            && $sharedCorePass
            && $safetyPass
            && $productionMutationSafetyPass
            && $planPass
            && $nonExecutionPass;

        $failures = [];
        if (! $c66LockOk) {
            $failures[] = 'C67_C66_LOCK_EVIDENCE_INVALID';
        }
        if (! $badMonthPass) {
            $failures[] = 'C67_BAD_MONTH_GOVERNANCE_INVALID';
        }
        if (! $weakRegimePass) {
            $failures[] = 'C67_WEAK_REGIME_GOVERNANCE_INVALID';
        }
        if (! $concentrationPass) {
            $failures[] = 'C67_CONCENTRATION_GOVERNANCE_INVALID';
        }
        if (! $lossClusterPass) {
            $failures[] = 'C67_LOSS_CLUSTER_GOVERNANCE_INVALID';
        }
        if (! $rollingPass) {
            $failures[] = 'C67_ROLLING_GOVERNANCE_INVALID';
        }
        if (! $sourceBiasPass) {
            $failures[] = 'C67_SOURCE_BIAS_GOVERNANCE_INVALID';
        }
        if (! $sharedCorePass) {
            $failures[] = 'C67_SHARED_CORE_GOVERNANCE_INVALID';
        }
        if (! $safetyPass) {
            $failures[] = 'C67_SAFETY_AND_LEAKAGE_INVALID';
        }
        if (! $productionMutationSafetyPass) {
            $failures[] = 'C67_PRODUCTION_MUTATION_SAFETY_INVALID';
        }
        if (! $planPass) {
            $failures[] = 'C67_PLAN_CONFIRM_NON_MUTATION_INVALID';
        }
        if (! $nonExecutionPass) {
            $failures[] = 'C67_PRODUCTION_CATALOG_ACTIVATION_NON_EXECUTION_INVALID';
        }
        if ($role === 'comparator_only') {
            $ready = false;
            $failures = ['C67_A01_REMAINS_COMPARATOR_ONLY'];
        }

        return [
            'candidate_code' => $candidate,
            'c67_role' => $role,
            'parent_candidate_code' => $parent,
            'c66_lock_evidence_summary' => $c66Summary,
            'c65_prelock_evidence_summary' => $c65Summary,
            'c64_oos_evidence_summary' => $oos,
            'production_catalog_activation_review_pass' => $ready,
            'candidate_ready_for_production_catalog_activation' => $ready,
            'production_catalog_lock_allowed' => $role !== 'comparator_only' && (bool) ($source['production_catalog_lock_allowed'] ?? false),
            'production_catalog_activation_allowed' => $ready,
            'production_catalog_activation_execution_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'bad_month_governance_pass' => $badMonthPass,
            'weak_regime_governance_pass' => $weakRegimePass,
            'concentration_governance_pass' => $concentrationPass,
            'loss_cluster_governance_pass' => $lossClusterPass,
            'rolling_governance_pass' => $rollingPass,
            'source_bias_governance_pass' => $sourceBiasPass,
            'shared_core_governance_pass' => $sharedCorePass,
            'safety_and_leakage_governance_pass' => $safetyPass,
            'production_mutation_safety_pass' => $productionMutationSafetyPass,
            'plan_confirm_non_mutation_pass' => $planPass,
            'production_catalog_activation_non_execution_pass' => $nonExecutionPass,
            'source_bias_risk_level' => $sourceBiasRisk,
            'shared_core_risk_level' => $sharedCoreRisk,
            'failure_reason_codes' => array_values(array_unique($failures)),
        ];
    }

    private function badMonthActivationReviewResults(array $scorecard): array
    {
        $out = [];
        foreach ($scorecard as $row) {
            $oos = (array) ($row['c64_oos_evidence_summary'] ?? []);
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'c67_role' => $row['c67_role'],
                'bad_month_activation_review_completed' => ! empty($oos['oos_worst_month']) && ! empty($oos['oos_worst_month_regime']),
                'documented_bad_month_risk_retained' => ($oos['oos_bad_month_decision'] ?? null) === 'PASS_WITH_DOCUMENTED_RISK',
                'bad_month_removed' => empty($oos['oos_worst_month']),
                'bad_month_risk_hidden' => empty($oos['oos_bad_month_decision']),
                'worst_month' => $oos['oos_worst_month'] ?? null,
                'worst_month_avg_ret_net' => $oos['oos_worst_month_avg_ret_net'] ?? null,
                'worst_month_regime' => $oos['oos_worst_month_regime'] ?? null,
                'bad_month_risk_level' => $oos['oos_bad_month_risk_level'] ?? null,
                'bad_month_governance_decision' => $oos['oos_bad_month_decision'] ?? null,
                'production_activation_risk_free_claim' => false,
                'bad_month_governance_pass' => (bool) ($row['bad_month_governance_pass'] ?? false),
            ];
        }
        return $out;
    }

    private function weakRegimeActivationReviewResults(array $scorecard): array
    {
        $out = [];
        foreach ($scorecard as $row) {
            $oos = (array) ($row['c64_oos_evidence_summary'] ?? []);
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'c67_role' => $row['c67_role'],
                'weak_regime_activation_review_completed' => ! empty($oos['oos_weak_regime_sample_status']),
                'weak_regime_retained' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => $oos['oos_weak_regime_sample_status'] ?? null,
                'weak_regime_sample_collapse_detected' => (bool) ($oos['oos_weak_regime_sample_collapse_detected'] ?? true),
                'weak_regime_risk_level' => $oos['oos_weak_regime_risk_level'] ?? null,
                'weak_regime_governance_decision' => (bool) ($row['weak_regime_governance_pass'] ?? false) ? 'PASS_WITH_DOCUMENTED_RISK' : 'FAIL',
                'production_activation_ignores_weak_regime_risk' => false,
                'weak_regime_governance_pass' => (bool) ($row['weak_regime_governance_pass'] ?? false),
            ];
        }
        return $out;
    }

    private function concentrationLossClusterGovernanceSummary(array $c66): array
    {
        $src = (array) ($c66['concentration_loss_cluster_governance_summary'] ?? []);
        return [
            'validation_completed' => true,
            'concentration_governance_pass' => (bool) ($src['concentration_governance_pass'] ?? false),
            'loss_cluster_governance_pass' => (bool) ($src['loss_cluster_governance_pass'] ?? false),
            'concentration_regression_detected' => (bool) ($src['concentration_regression_detected'] ?? true),
            'loss_cluster_regression_detected' => (bool) ($src['loss_cluster_regression_detected'] ?? true),
            'month_dependency_detected' => (bool) ($src['month_dependency_detected'] ?? false),
            'sample_collapse_detected' => (bool) ($src['sample_collapse_detected'] ?? false),
        ];
    }

    private function rollingMonthDependencyGovernanceSummary(array $c66): array
    {
        $src = (array) ($c66['rolling_month_dependency_governance_summary'] ?? []);
        return [
            'validation_completed' => true,
            'rolling_governance_pass' => (bool) ($src['rolling_governance_pass'] ?? false),
            'month_dependency_detected' => (bool) ($src['month_dependency_detected'] ?? false),
            'sample_collapse_detected' => (bool) ($src['sample_collapse_detected'] ?? false),
        ];
    }

    private function sourceBiasSharedCoreGovernanceSummary(array $c66): array
    {
        $src = (array) ($c66['source_bias_shared_core_governance_summary'] ?? []);
        $sourceBiasPass = (bool) ($src['source_bias_governance_pass'] ?? false) && ($src['source_bias_risk_level'] ?? 'HIGH') !== 'HIGH';
        $sharedCorePass = (bool) ($src['shared_core_governance_pass'] ?? false) && ($src['shared_core_risk_level'] ?? 'HIGH') !== 'HIGH';
        return [
            'validation_completed' => true,
            'source_bias_governance_pass' => $sourceBiasPass,
            'shared_core_governance_pass' => $sharedCorePass,
            'source_bias_risk_level' => $sourceBiasPass ? 'DOCUMENTED_NOT_HIGH' : 'HIGH',
            'shared_core_risk_level' => $sharedCorePass ? 'LOW' : 'HIGH',
            'parent_diversity_sufficient' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
        ];
    }

    private function productionActivationMutationSafetySummary(array $c66, array $scope, array $dictionary): array
    {
        $c66Safety = (array) ($c66['production_mutation_safety_summary'] ?? []);
        $summary = [
            'validation_completed' => true,
            'production_catalog_locked_decision_created' => (bool) ($c66Safety['production_catalog_locked_decision_created'] ?? false),
            'production_catalog_activation_review_decision_created' => false,
            'production_catalog_created' => (bool) ($c66Safety['production_catalog_created'] ?? false),
            'production_catalog_activated' => (bool) ($c66Safety['production_catalog_activated'] ?? false),
            'production_catalog_activation_execution_allowed' => false,
            'production_deployment_executed' => (bool) ($c66Safety['production_deployment_executed'] ?? false),
            'plan_confirm_mutated' => (bool) ($c66Safety['plan_confirm_mutated'] ?? false),
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'selection_changed_after_c66' => (bool) ($scope['candidate_scope_changed_after_c66'] ?? true),
            'selection_changed_after_c65' => (bool) ($scope['candidate_scope_changed_after_c65'] ?? true),
            'parameter_changed_after_c66' => (bool) ($scope['parameter_changed'] ?? true),
            'new_candidate_created' => (bool) ($scope['new_candidate_created'] ?? true),
            'oos_reused_for_ranking' => (bool) ($scope['oos_result_used_for_new_ranking'] ?? true),
            'latest_shortcut_used' => (bool) ($c66Safety['latest_shortcut_used'] ?? false),
            'max_date_shortcut_used' => (bool) ($c66Safety['max_date_shortcut_used'] ?? false),
            'future_lookup_detected' => (bool) ($c66Safety['future_lookup_detected'] ?? false),
            'return_fields_used_for_selection' => (bool) ($c66Safety['return_fields_used_for_selection'] ?? false),
            'database_dictionary_rule_complied' => (bool) ($dictionary['dictionary_read_rule_complied'] ?? false),
        ];
        $summary['production_activation_mutation_safety_pass'] = $summary['production_catalog_locked_decision_created']
            && ! $summary['production_catalog_created']
            && ! $summary['production_catalog_activated']
            && ! $summary['production_catalog_activation_execution_allowed']
            && ! $summary['production_deployment_executed']
            && ! $summary['plan_confirm_mutated']
            && ! $summary['production_deployment_allowed']
            && ! $summary['plan_confirm_mutation_allowed']
            && ! $summary['selection_changed_after_c66']
            && ! $summary['selection_changed_after_c65']
            && ! $summary['parameter_changed_after_c66']
            && ! $summary['new_candidate_created']
            && ! $summary['oos_reused_for_ranking']
            && ! $summary['latest_shortcut_used']
            && ! $summary['max_date_shortcut_used']
            && ! $summary['future_lookup_detected']
            && ! $summary['return_fields_used_for_selection']
            && $summary['database_dictionary_rule_complied'];
        return $summary;
    }

    private function documentationGovernanceSummary(): array
    {
        $docs = [];
        $missing = [];
        $combined = '';
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path);
            $content = $exists ? (string) file_get_contents($path) : '';
            $combined .= "\n".$content;
            $docs[$key] = ['path' => $path, 'exists' => $exists, 'bytes' => $exists ? filesize($path) : 0];
            if (! $exists) {
                $missing[] = $path;
            }
        }
        $requirements = [
            'c67_is_activation_review_documented' => 'C67 is production catalog activation review',
            'not_live_activation_documented' => 'C67 pass is not live activation',
            'not_live_deployment_documented' => 'C67 pass is not live deployment',
            'primary_e02_documented' => self::PRIMARY_CANDIDATE,
            'backup_b01_documented' => self::BACKUP_CANDIDATE,
            'a01_comparator_only_restriction_documented' => 'A01 remains comparator-only',
            'bad_month_documented_risk_retained' => 'bad-month risk remains documented',
            'weak_regime_documented_risk_retained' => 'weak-regime risk remains documented',
            'activation_execution_deferred_documented' => 'activation execution is deferred to C68',
            'plan_confirm_non_mutation_documented' => 'does not mutate PLAN/CONFIRM',
        ];
        $flags = [];
        foreach ($requirements as $key => $needle) {
            $flags[$key] = strpos($combined, $needle) !== false;
        }
        return array_merge([
            'validation_completed' => true,
            'docs' => $docs,
            'missing_docs' => $missing,
            'documentation_governance_pass' => $missing === [] && ! in_array(false, $flags, true),
        ], $flags);
    }

    private function c65CleanupNoteSummary(array $c66): array
    {
        $note = (array) ($c66['c65_cleanup_note_summary'] ?? []);
        return [
            'validation_completed' => true,
            'legacy_repair_recommendation' => (string) ($note['legacy_repair_recommendation'] ?? 'C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY'),
            'legacy_repair_recommendation_non_blocking' => (bool) ($note['legacy_repair_recommendation_non_blocking'] ?? true),
            'normalized_repair_recommendation' => (string) ($note['normalized_repair_recommendation'] ?? 'NOT_REQUIRED'),
            'c65_failure_repair_required' => (bool) ($note['c65_failure_repair_required'] ?? false),
        ];
    }

    private function productionCatalogActivationReviewDecision(array $scorecard, array &$artifact): array
    {
        $rows = $this->indexByCode($scorecard);
        $primaryPass = (bool) ($rows[self::PRIMARY_CANDIDATE]['production_catalog_activation_review_pass'] ?? false);
        $backupPass = (bool) ($rows[self::BACKUP_CANDIDATE]['production_catalog_activation_review_pass'] ?? false);
        $safetyPass = (bool) ($artifact['production_activation_mutation_safety_summary']['production_activation_mutation_safety_pass'] ?? false);
        $docsPass = (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false);
        $cleanupPass = (bool) ($artifact['c65_cleanup_note_summary']['legacy_repair_recommendation_non_blocking'] ?? false)
            && ($artifact['c65_cleanup_note_summary']['normalized_repair_recommendation'] ?? null) === 'NOT_REQUIRED'
            && ! (bool) ($artifact['c65_cleanup_note_summary']['c65_failure_repair_required'] ?? true);
        $globalGovernancePass = $safetyPass
            && $docsPass
            && $cleanupPass
            && (bool) ($artifact['concentration_loss_cluster_governance_summary']['concentration_governance_pass'] ?? false)
            && (bool) ($artifact['concentration_loss_cluster_governance_summary']['loss_cluster_governance_pass'] ?? false)
            && ! (bool) ($artifact['concentration_loss_cluster_governance_summary']['concentration_regression_detected'] ?? true)
            && ! (bool) ($artifact['concentration_loss_cluster_governance_summary']['loss_cluster_regression_detected'] ?? true)
            && (bool) ($artifact['rolling_month_dependency_governance_summary']['rolling_governance_pass'] ?? false)
            && ! (bool) ($artifact['rolling_month_dependency_governance_summary']['month_dependency_detected'] ?? true)
            && ! (bool) ($artifact['rolling_month_dependency_governance_summary']['sample_collapse_detected'] ?? true)
            && (bool) ($artifact['source_bias_shared_core_governance_summary']['source_bias_governance_pass'] ?? false)
            && (bool) ($artifact['source_bias_shared_core_governance_summary']['shared_core_governance_pass'] ?? false);

        $primaryPass = $primaryPass && $globalGovernancePass;
        $backupPass = $backupPass && $globalGovernancePass;
        $pass = $primaryPass || $backupPass;
        $scope = $primaryPass && $backupPass ? 'PRIMARY_AND_BACKUP' : ($primaryPass ? 'PRIMARY_ONLY' : ($backupPass ? 'BACKUP_ONLY' : 'NONE'));
        $status = $this->statusFromScope($scope, $scorecard, $artifact);
        if ($pass) {
            $artifact['production_activation_mutation_safety_summary']['production_catalog_activation_review_decision_created'] = true;
        }

        return [
            'validation_completed' => true,
            'production_catalog_activation_review_executed' => true,
            'production_catalog_activation_review_status' => $status,
            'production_catalog_activation_review_pass' => $pass,
            'primary_activation_review_pass' => $primaryPass,
            'backup_activation_review_pass' => $backupPass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'production_catalog_activation_pass_scope' => $scope,
            'a01_remains_comparator_only' => true,
            'decision_reason' => $pass ? 'Primary E02 and/or backup B01 pass C67 production catalog activation review; execution is deferred to C68.' : 'C67 production catalog activation review did not pass for primary or backup.',
            'diagnostic_conclusion' => $status,
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => $pass,
            'production_catalog_activation_execution_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
        ];
    }

    private function c68ReadinessDecision(array $scorecard, array $decision): array
    {
        $ready = [];
        foreach ($scorecard as $row) {
            if (($row['candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE && (bool) ($row['candidate_ready_for_production_catalog_activation'] ?? false) && (bool) ($decision['production_catalog_activation_review_pass'] ?? false)) {
                $ready[] = $row['candidate_code'];
            }
        }
        return [
            'validation_completed' => true,
            'candidate_ready_for_c68_count' => count($ready),
            'candidate_codes' => $ready,
            'c68_recommendation' => (bool) ($decision['production_catalog_activation_review_pass'] ?? false) ? 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW' : $this->repairRecommendationFromStatus((string) ($decision['production_catalog_activation_review_status'] ?? '')),
            'decision_reason' => (bool) ($decision['production_catalog_activation_review_pass'] ?? false) ? 'C67 activation review passed. Next step is C68 activation execution review only.' : 'C67 activation review failed. Next step is targeted governance cleanup or repair.',
            'diagnostic_conclusion' => (string) ($decision['production_catalog_activation_review_status'] ?? 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_FAILED_BOTH'),
            'production_catalog_lock_allowed' => true,
            'production_catalog_activation_allowed' => (bool) ($decision['production_catalog_activation_allowed'] ?? false),
            'production_catalog_activation_execution_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
        ];
    }

    private function failureAttributionSummary(array $scorecard, array $decision, array $artifact): array
    {
        $status = (string) ($decision['production_catalog_activation_review_status'] ?? $artifact['status'] ?? 'C67_NOT_RUN');
        $blocker = $this->dominantBlocker($scorecard, $artifact, $status);
        return [
            'validation_completed' => true,
            'dominant_blocker' => $blocker,
            'status' => $status,
            'candidate_failure_reason_codes' => $this->collectFailureReasons($scorecard),
            'recommended_next_step' => $blocker === 'NONE' ? 'C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW' : $this->repairRecommendationFromBlocker($blocker),
        ];
    }

    private function diagnostics(array $artifact): array
    {
        $diags = [];
        $status = (string) ($artifact['status'] ?? $artifact['production_catalog_activation_review_decision']['production_catalog_activation_review_status'] ?? 'C67_NOT_RUN');
        if (strpos($status, 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED') === 0) {
            $diags[] = ['reason_code' => 'WS_BT_C67_ACTIVATION_REVIEW_PASSED', 'message' => 'C67 production catalog activation review passed as artifact-only review decision.'];
        } elseif ($status !== 'C67_NOT_RUN') {
            $diags[] = ['reason_code' => 'WS_BT_C67_ACTIVATION_REVIEW_NOT_PASSED', 'message' => 'C67 production catalog activation review did not pass.'];
        }
        if (($artifact['production_activation_mutation_safety_summary']['production_catalog_activation_review_decision_created'] ?? false) === true) {
            $diags[] = ['reason_code' => 'WS_BT_C67_REVIEW_DECISION_ARTIFACT_ONLY', 'message' => 'Activation review decision is artifact-only; live activation, deployment, and PLAN/CONFIRM mutation remain disabled.'];
        }
        return $diags;
    }

    private function statusFromScope(string $scope, array $scorecard, array $artifact): string
    {
        if ($scope === 'PRIMARY_AND_BACKUP') {
            return 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        }
        if ($scope === 'PRIMARY_ONLY') {
            return 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_PRIMARY_ONLY';
        }
        if ($scope === 'BACKUP_ONLY') {
            return 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_PASSED_BACKUP_ONLY';
        }
        $blocker = $this->dominantBlocker($scorecard, $artifact, '');
        $map = [
            'PRODUCTION_LOCK_INCOMPLETE' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_LOCK_INCOMPLETE',
            'BAD_MONTH_GOVERNANCE' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE',
            'WEAK_REGIME_GOVERNANCE' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE',
            'CONCENTRATION_OR_LOSS_CLUSTER' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER',
            'ROLLING_MONTH_DEPENDENCY' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER',
            'SOURCE_BIAS_OR_SHARED_CORE' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE',
            'SAFETY_OR_LEAKAGE' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE',
            'PRODUCTION_MUTATION_SAFETY' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_MUTATION',
            'DOCUMENTATION_GOVERNANCE' => 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
        ];
        return $map[$blocker] ?? 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_FAILED_BOTH';
    }

    private function dominantBlocker(array $scorecard, array $artifact, string $status): string
    {
        if (strpos($status, 'PASSED') !== false) {
            return 'NONE';
        }
        if (! (bool) ($artifact['production_activation_mutation_safety_summary']['production_activation_mutation_safety_pass'] ?? true)) {
            return 'PRODUCTION_MUTATION_SAFETY';
        }
        if (! (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? true)) {
            return 'DOCUMENTATION_GOVERNANCE';
        }
        if (! (bool) ($artifact['concentration_loss_cluster_governance_summary']['concentration_governance_pass'] ?? true)
            || ! (bool) ($artifact['concentration_loss_cluster_governance_summary']['loss_cluster_governance_pass'] ?? true)
            || (bool) ($artifact['concentration_loss_cluster_governance_summary']['concentration_regression_detected'] ?? false)
            || (bool) ($artifact['concentration_loss_cluster_governance_summary']['loss_cluster_regression_detected'] ?? false)) {
            return 'CONCENTRATION_OR_LOSS_CLUSTER';
        }
        if (! (bool) ($artifact['rolling_month_dependency_governance_summary']['rolling_governance_pass'] ?? true)
            || (bool) ($artifact['rolling_month_dependency_governance_summary']['month_dependency_detected'] ?? false)
            || (bool) ($artifact['rolling_month_dependency_governance_summary']['sample_collapse_detected'] ?? false)) {
            return 'ROLLING_MONTH_DEPENDENCY';
        }
        if (! (bool) ($artifact['source_bias_shared_core_governance_summary']['source_bias_governance_pass'] ?? true)
            || ! (bool) ($artifact['source_bias_shared_core_governance_summary']['shared_core_governance_pass'] ?? true)
            || (bool) ($artifact['source_bias_shared_core_governance_summary']['a01_promoted'] ?? false)) {
            return 'SOURCE_BIAS_OR_SHARED_CORE';
        }
        foreach ($scorecard as $row) {
            if (($row['c67_role'] ?? '') === 'comparator_only') {
                continue;
            }
            $codes = (array) ($row['failure_reason_codes'] ?? []);
            foreach ($codes as $code) {
                if (strpos($code, 'C66_LOCK') !== false) {
                    return 'PRODUCTION_LOCK_INCOMPLETE';
                }
                if (strpos($code, 'BAD_MONTH') !== false) {
                    return 'BAD_MONTH_GOVERNANCE';
                }
                if (strpos($code, 'WEAK_REGIME') !== false) {
                    return 'WEAK_REGIME_GOVERNANCE';
                }
                if (strpos($code, 'CONCENTRATION') !== false || strpos($code, 'LOSS_CLUSTER') !== false) {
                    return 'CONCENTRATION_OR_LOSS_CLUSTER';
                }
                if (strpos($code, 'ROLLING') !== false) {
                    return 'ROLLING_MONTH_DEPENDENCY';
                }
                if (strpos($code, 'SOURCE_BIAS') !== false || strpos($code, 'SHARED_CORE') !== false) {
                    return 'SOURCE_BIAS_OR_SHARED_CORE';
                }
                if (strpos($code, 'SAFETY') !== false) {
                    return 'SAFETY_OR_LEAKAGE';
                }
            }
        }
        return 'NONE';
    }

    private function repairRecommendationFromStatus(string $status): string
    {
        $map = [
            'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_BAD_MONTH_GOVERNANCE_REPAIR',
            'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_WEAK_REGIME_GOVERNANCE_REPAIR',
            'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_SOURCE_BIAS_OR_SHARED_CORE_REPAIR',
            'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_SAFETY_REPAIR',
            'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_PRODUCTION_MUTATION' => 'C68_PRODUCTION_CATALOG_ACTIVATION_SAFETY_REPAIR',
            'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_DOCUMENTATION_REPAIR',
        ];
        return $map[$status] ?? 'C68_PRODUCTION_CATALOG_ACTIVATION_GOVERNANCE_CLEANUP';
    }

    private function repairRecommendationFromBlocker(string $blocker): string
    {
        $map = [
            'BAD_MONTH_GOVERNANCE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_BAD_MONTH_GOVERNANCE_REPAIR',
            'WEAK_REGIME_GOVERNANCE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_WEAK_REGIME_GOVERNANCE_REPAIR',
            'SOURCE_BIAS_OR_SHARED_CORE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_SOURCE_BIAS_OR_SHARED_CORE_REPAIR',
            'SAFETY_OR_LEAKAGE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_SAFETY_REPAIR',
            'PRODUCTION_MUTATION_SAFETY' => 'C68_PRODUCTION_CATALOG_ACTIVATION_SAFETY_REPAIR',
            'DOCUMENTATION_GOVERNANCE' => 'C68_PRODUCTION_CATALOG_ACTIVATION_DOCUMENTATION_REPAIR',
        ];
        return $map[$blocker] ?? 'C68_PRODUCTION_CATALOG_ACTIVATION_GOVERNANCE_CLEANUP';
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reasonCode;
        $artifact['message'] = $message;
        $artifact['production_catalog_activation_review_executed'] = false;
        $artifact['production_catalog_activation_review_pass'] = false;
        $artifact['production_catalog_lock_allowed'] = false;
        $artifact['production_catalog_activation_allowed'] = false;
        $artifact['production_catalog_activation_execution_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $status;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reasonCode;
        $artifact['message'] = $message;
        $artifact['production_catalog_activation_review_executed'] = true;
        $artifact['production_catalog_activation_review_pass'] = false;
        $artifact['production_catalog_lock_allowed'] = true;
        $artifact['production_catalog_activation_allowed'] = false;
        $artifact['production_catalog_activation_execution_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $status;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C67_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'WS_BT_C67_OUTPUT_EXISTS';
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
            'c67_is_production_catalog_activation_review' => true,
            'c67_is_not_redesign' => true,
            'c67_is_not_retune' => true,
            'c67_is_not_parameter_search' => true,
            'c67_is_not_oos_retest' => true,
            'c67_is_not_production_activation_execution' => true,
            'c67_is_not_production_deployment' => true,
            'production_catalog_activation_review_is_artifact_only' => true,
            'production_catalog_activation_execution_allowed_must_remain_false' => true,
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
            if (isset($row['candidate_code'])) {
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
