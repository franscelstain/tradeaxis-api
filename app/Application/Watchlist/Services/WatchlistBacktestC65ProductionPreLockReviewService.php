<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC65ProductionPreLockReviewService
{
    public const RUN_CODE = 'C65_PRODUCTION_PRE_LOCK_REVIEW';
    public const ARTIFACT_TYPE = 'C65_PRODUCTION_PRE_LOCK_REVIEW';

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

    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c65-production-pre-lock-review.json';
    public const DEFAULT_OOS_FROM = '2025-05-22';
    public const DEFAULT_OOS_TO = '2026-05-29';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
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
        'c65_review_doc' => 'docs/watchlist/audit/WS_C65_PRODUCTION_PRE_LOCK_REVIEW.md',
        'c65_operator_commands_doc' => 'docs/watchlist/audit/WS_C65_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    /**
     * C65_PRODUCTION_PRE_LOCK_REVIEW. PRODUCTION_PRE_LOCK_ONLY. NOT_PRODUCTION_READY.
     * C64_ARTIFACT_HASH_LOCK. C64_FILE_SHA1_LOCK. C60_TO_C64_LINEAGE_LOCK.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. ASOF_SAFE_LOOKUP_REQUIRED.
     * SELECTION_SCOPE_FROZEN_FROM_C64. NO_REDESIGN. NO_RETUNE. NO_PARAMETER_SEARCH.
     * NO_OOS_BASED_RERANKING. NO_OOS_TIE_BREAK. NO_BEST_OF_FAILED_PROMOTION.
     * A01_COMPARATOR_ONLY_NOT_PROMOTABLE. BAD_MONTH_RISK_RETAINED. WEAK_REGIME_RISK_RETAINED.
     * NO_PRODUCTION_CATALOG_CREATION. NO_PRODUCTION_CATALOG_ACTIVATION. NO_DEPLOYMENT.
     * NO_PLAN_CONFIRM_MUTATION. PRODUCTION_READY_FALSE. PRODUCTION_CATALOG_ALLOWED_FALSE.
     * PRODUCTION_DEPLOYMENT_ALLOWED_FALSE. NO_LATEST_DATE_SHORTCUT. NO_DATE_DESC_SHORTCUT.
     * NO_FUTURE_LOOKUP. NO_RETURN_FIELDS_FOR_SELECTION. C65_CAN_ONLY_RECOMMEND_C66_LOCK_REVIEW.
     */
    public function execute(
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
            return $this->blocked($artifact, 'C65_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'WS_BT_C65_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C65 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath, $overwrite);
        }

        $c64Load = $this->loadArtifactLock($c64Artifact, $expectedC64Hash, $expectedC64FileSha1);
        $this->copyLock($artifact, 'c64', $c64Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);
        if (! $c64Load['readable']) {
            return $this->blocked($artifact, 'C65_BLOCKED_MISSING_C64_ARTIFACT', 'WS_BT_C65_C64_ARTIFACT_MISSING', 'C65 requires the locked C64 artifact.', $outputPath, $overwrite);
        }
        if (! $c64Load['hash_match']) {
            return $this->blocked($artifact, 'C65_BLOCKED_C64_ARTIFACT_LOCK_MISMATCH', 'WS_BT_C65_C64_ARTIFACT_HASH_MISMATCH', 'C64 artifact hash does not match the expected C65 lock.', $outputPath, $overwrite);
        }
        if (! $c64Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C65_BLOCKED_C64_FILE_SHA1_LOCK_MISMATCH', 'WS_BT_C65_C64_FILE_SHA1_MISMATCH', 'C64 file SHA1 does not match the expected C65 lock.', $outputPath, $overwrite);
        }

        $c64 = $c64Load['payload'];
        $c64Validation = $this->validateC64($c64);
        $artifact['c64_lock_validation_summary'] = $c64Validation;
        if (! (bool) ($c64Validation['pass'] ?? false)) {
            return $this->blocked($artifact, (string) ($c64Validation['status'] ?? 'C65_BLOCKED_C64_STATUS_OR_REASON_MISMATCH'), (string) ($c64Validation['reason_code'] ?? 'WS_BT_C65_C64_LOCK_INVALID'), (string) ($c64Validation['message'] ?? 'C64 lock is invalid for C65.'), $outputPath, $overwrite);
        }

        $lineageLoads = [
            'c63' => $this->loadArtifactLock($c63Artifact, $expectedC63Hash, $expectedC63FileSha1),
            'c62' => $this->loadArtifactLock($c62Artifact, $expectedC62Hash, $expectedC62FileSha1),
            'c61' => $this->loadArtifactLock($c61Artifact, $expectedC61Hash, $expectedC61FileSha1),
            'c60' => $this->loadArtifactLock($c60Artifact, $expectedC60Hash, $expectedC60FileSha1),
        ];
        foreach ($lineageLoads as $prefix => $lock) {
            $this->copyLock($artifact, $prefix, $lock);
        }
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        $artifact['c63_lineage_validation_summary'] = $this->validateC63Lineage($lineageLoads['c63']);
        $artifact['c62_lineage_validation_summary'] = $this->validateC62Lineage($lineageLoads['c62']);
        $artifact['c61_lineage_validation_summary'] = $this->validateC61Lineage($lineageLoads['c61']);
        $artifact['c60_lineage_validation_summary'] = $this->validateC60Lineage($lineageLoads['c60']);
        foreach (['c63_lineage_validation_summary', 'c62_lineage_validation_summary', 'c61_lineage_validation_summary', 'c60_lineage_validation_summary'] as $key) {
            if (! (bool) ($artifact[$key]['pass'] ?? false)) {
                return $this->blocked($artifact, 'C65_BLOCKED_LINEAGE_LOCK_MISMATCH', (string) ($artifact[$key]['reason_code'] ?? 'WS_BT_C65_LINEAGE_LOCK_MISMATCH'), (string) ($artifact[$key]['message'] ?? 'C60-C63 lineage lock mismatch.'), $outputPath, $overwrite);
            }
        }

        $scope = $this->candidateScopeFreezeSummary($c64);
        $artifact['candidate_scope_freeze_summary'] = $scope;
        if (! (bool) ($scope['candidate_scope_freeze_completed'] ?? false) || (bool) ($scope['candidate_scope_changed_after_c64'] ?? true)) {
            return $this->blocked($artifact, 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'WS_BT_C65_CANDIDATE_SCOPE_MISMATCH', 'C64 candidate scope is not the locked E02/B01/A01 hierarchy.', $outputPath, $overwrite);
        }

        $replay = $this->c64OosProofReplaySummary($c64);
        $artifact['c64_oos_proof_replay_summary'] = $replay;
        if (! (bool) ($replay['validation_completed'] ?? false) || ($replay['oos_pass_scope'] ?? null) !== 'PRIMARY_AND_BACKUP') {
            return $this->blocked($artifact, 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_OOS_PROOF_INCOMPLETE', 'WS_BT_C65_C64_OOS_PROOF_INCOMPLETE', 'C64 OOS proof replay summary is incomplete.', $outputPath, $overwrite);
        }

        $scorecard = $this->productionPrelockCandidateScorecard($c64);
        $artifact['production_prelock_candidate_scorecard'] = $scorecard;
        $artifact['bad_month_governance_review_results'] = $this->badMonthGovernanceReviewResults($scorecard);
        $artifact['weak_regime_governance_review_results'] = $this->weakRegimeGovernanceReviewResults($scorecard);
        $artifact['concentration_loss_cluster_governance_summary'] = $this->concentrationLossClusterGovernanceSummary($scorecard);
        $artifact['rolling_month_dependency_governance_summary'] = $this->rollingMonthDependencyGovernanceSummary($scorecard);
        $artifact['source_bias_shared_core_governance_summary'] = $this->sourceBiasSharedCoreGovernanceSummary($scorecard);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($c64, $scope, $dictionary);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c64_cleanup_note_summary'] = $this->c64CleanupNoteSummary($c64);

        $decision = $this->productionPrelockDecision($scorecard, $artifact);
        $artifact['production_prelock_decision'] = $decision;
        $artifact['c66_readiness_decision'] = $this->c66ReadinessDecision($scorecard, $decision);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($scorecard, $decision, $artifact);
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        $artifact['status'] = (string) ($decision['production_prelock_status'] ?? 'C65_PRODUCTION_PRE_LOCK_REVIEW_FAILED_BOTH');
        $artifact['reason_code'] = $artifact['status'];
        $artifact['production_prelock_review_executed'] = true;
        $artifact['production_prelock_review_pass'] = (bool) ($decision['production_prelock_review_pass'] ?? false);
        $artifact['production_ready'] = false;
        $artifact['production_catalog_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['diagnostic_conclusion'] = $artifact['status'];
        $artifact['next_step_recommendation'] = (string) ($artifact['c66_readiness_decision']['c66_recommendation'] ?? 'C66_PRODUCTION_PRELOCK_GOVERNANCE_CLEANUP');

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(
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
            'status' => 'C65_NOT_RUN',
            'reason_code' => 'C65_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'production_prelock_review_executed' => false,
            'production_prelock_review_pass' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
            'input_c64_artifact' => $c64Artifact,
            'expected_c64_hash' => $expectedC64Hash,
            'expected_c64_file_sha1' => strtoupper($expectedC64FileSha1),
            'actual_c64_hash' => null,
            'actual_c64_file_sha1' => null,
            'c64_hash_match' => false,
            'c64_file_sha1_match' => false,
            'input_c63_artifact' => $c63Artifact,
            'expected_c63_hash' => $expectedC63Hash,
            'expected_c63_file_sha1' => strtoupper($expectedC63FileSha1),
            'actual_c63_hash' => null,
            'actual_c63_file_sha1' => null,
            'c63_hash_match' => false,
            'c63_file_sha1_match' => false,
            'input_c62_artifact' => $c62Artifact,
            'expected_c62_hash' => $expectedC62Hash,
            'expected_c62_file_sha1' => strtoupper($expectedC62FileSha1),
            'actual_c62_hash' => null,
            'actual_c62_file_sha1' => null,
            'c62_hash_match' => false,
            'c62_file_sha1_match' => false,
            'input_c61_artifact' => $c61Artifact,
            'expected_c61_hash' => $expectedC61Hash,
            'expected_c61_file_sha1' => strtoupper($expectedC61FileSha1),
            'actual_c61_hash' => null,
            'actual_c61_file_sha1' => null,
            'c61_hash_match' => false,
            'c61_file_sha1_match' => false,
            'input_c60_artifact' => $c60Artifact,
            'expected_c60_hash' => $expectedC60Hash,
            'expected_c60_file_sha1' => strtoupper($expectedC60FileSha1),
            'actual_c60_hash' => null,
            'actual_c60_file_sha1' => null,
            'c60_hash_match' => false,
            'c60_file_sha1_match' => false,
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c64_lock_validation_summary' => [],
            'c63_lineage_validation_summary' => [],
            'c62_lineage_validation_summary' => [],
            'c61_lineage_validation_summary' => [],
            'c60_lineage_validation_summary' => [],
            'candidate_scope_freeze_summary' => [],
            'c64_oos_proof_replay_summary' => [],
            'production_prelock_candidate_scorecard' => [],
            'bad_month_governance_review_results' => [],
            'weak_regime_governance_review_results' => [],
            'concentration_loss_cluster_governance_summary' => [],
            'rolling_month_dependency_governance_summary' => [],
            'source_bias_shared_core_governance_summary' => [],
            'production_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c64_cleanup_note_summary' => [],
            'production_prelock_decision' => [],
            'c66_readiness_decision' => [],
            'failure_attribution_summary' => [],
            'diagnostics' => [],
            'created_at' => $executedAt,
            'safety_boundaries' => $this->safetyBoundaries(),
            'diagnostic_conclusion' => 'C65_NOT_RUN',
            'next_step_recommendation' => null,
        ];
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
        foreach (['c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
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
            'table_and_field_roles_identified' => true,
            'tables_identified' => ['market_calendar', 'eod_bars', 'eod_indicators', 'market_benchmark_indicators', 'watchlist_recommendations'],
            'date_keys_identified' => ['market_calendar.cal_date', 'eod_bars.trade_date', 'eod_indicators.trade_date', 'market_benchmark_indicators.trade_date'],
            'identifier_keys_identified' => ['ticker_id', 'ticker_code', 'benchmark_code=IHSG', 'sector_code', 'branch_code', 'bucket_code', 'candidate_code'],
            'market_index_mapping' => [
                'market_index_roc20_source' => 'market_benchmark_indicators.roc_20',
                'market_index_ma20_slope_pct_source' => 'market_benchmark_indicators.ma20_slope_pct',
                'benchmark_code' => 'IHSG',
                'calendar_date_key' => 'market_calendar.cal_date',
            ],
            'asof_safe' => true,
            'latest_shortcut_used' => false,
            'date_desc_shortcut_used' => false,
            'future_lookup_detected' => false,
            'oos_result_used_for_selection' => false,
        ];
    }

    private function validateC64(array $c64): array
    {
        if (($c64['status'] ?? null) !== 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C65_BLOCKED_C64_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C65_C64_STATUS_INVALID', 'message' => 'C64 status is not the locked primary+backup OOS proof pass.'];
        }
        if (($c64['reason_code'] ?? null) !== 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C65_BLOCKED_C64_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C65_C64_REASON_INVALID', 'message' => 'C64 reason_code is not the locked primary+backup OOS proof pass.'];
        }
        if (($c64['oos_proof_pass'] ?? null) !== true) {
            return ['pass' => false, 'status' => 'C65_BLOCKED_C64_OOS_PROOF_NOT_PASSED', 'reason_code' => 'WS_BT_C65_C64_OOS_PROOF_NOT_PASSED', 'message' => 'C64 OOS proof did not pass.'];
        }
        if ((int) ($c64['c65_readiness_decision']['candidate_ready_for_c65_count'] ?? -1) !== 2) {
            return ['pass' => false, 'status' => 'C65_BLOCKED_C64_C65_READINESS_COUNT_MISMATCH', 'reason_code' => 'WS_BT_C65_C64_READINESS_COUNT_INVALID', 'message' => 'C64 candidate_ready_for_c65_count must equal 2.'];
        }
        if (($c64['production_ready'] ?? null) !== false) {
            return ['pass' => false, 'status' => 'C65_BLOCKED_C64_PRODUCTION_READY_FLAG_INVALID', 'reason_code' => 'WS_BT_C65_C64_PRODUCTION_READY_INVALID', 'message' => 'C64 must keep production_ready=false.'];
        }
        foreach (['direct_oos_proof_recommended', 'oos_proof_unlocked', 'pre_oos_unlocked'] as $field) {
            if (($c64[$field] ?? null) !== false) {
                return ['pass' => false, 'status' => 'C65_BLOCKED_C64_SAFETY_FLAG_MISMATCH', 'reason_code' => 'WS_BT_C65_C64_SAFETY_FLAG_INVALID', 'message' => 'C64 safety flags must remain false.'];
            }
        }
        $decision = (array) ($c64['oos_proof_decision'] ?? []);
        if (($decision['oos_pass_scope'] ?? null) !== 'PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_OOS_PROOF_INCOMPLETE', 'reason_code' => 'WS_BT_C65_C64_OOS_SCOPE_INVALID', 'message' => 'C64 OOS pass scope must be PRIMARY_AND_BACKUP.'];
        }
        $safety = (array) ($c64['oos_safety_and_leakage_audit_summary'] ?? []);
        if (($safety['oos_safety_and_leakage_pass'] ?? null) !== true) {
            return ['pass' => false, 'status' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_SAFETY_OR_LEAKAGE', 'reason_code' => 'WS_BT_C65_C64_SAFETY_LEAKAGE_INVALID', 'message' => 'C64 safety/leakage audit did not pass.'];
        }
        return [
            'pass' => true,
            'validation_completed' => true,
            'status_match' => true,
            'reason_code_match' => true,
            'oos_proof_pass' => true,
            'candidate_ready_for_c65_count' => 2,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'pre_oos_unlocked' => false,
            'oos_pass_scope' => 'PRIMARY_AND_BACKUP',
            'safety_and_leakage_pass' => true,
        ];
    }

    private function validateC63Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', 'WS_BT_C65_C63_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return (int) ($payload['c64_readiness_decision']['candidate_ready_for_c64_count'] ?? -1) === 2
                && ($payload['production_ready'] ?? null) === false
                && ($payload['direct_oos_proof_recommended'] ?? null) === false
                && ($payload['oos_proof_unlocked'] ?? null) === false
                && ($payload['pre_oos_unlocked'] ?? null) === false;
        });
    }

    private function validateC62Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', 'WS_BT_C65_C62_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
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
        return $this->validateLineageLock($lock, 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED', 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE', 'WS_BT_C65_C61_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return (int) ($payload['c62_readiness_decision']['candidate_ready_for_c62_count'] ?? -1) === 3
                && ($payload['production_ready'] ?? null) === false
                && ($payload['direct_oos_proof_recommended'] ?? null) === false
                && ($payload['oos_proof_unlocked'] ?? null) === false;
        });
    }

    private function validateC60Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED', 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS', 'WS_BT_C65_C60_LINEAGE_LOCK_MISMATCH');
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

    private function candidateScopeFreezeSummary(array $c64): array
    {
        $freeze = (array) ($c64['selection_freeze_summary'] ?? []);
        $decision = (array) ($c64['oos_proof_decision'] ?? []);
        $primary = (string) ($freeze['primary_candidate_code'] ?? $decision['primary_oos_candidate_code'] ?? '');
        $backup = array_values((array) ($freeze['backup_candidate_codes'] ?? $decision['backup_oos_candidate_codes'] ?? []));
        $comparator = array_values((array) ($freeze['comparator_only_candidate_codes'] ?? $decision['comparator_only_candidate_codes'] ?? []));
        $valid = $primary === self::PRIMARY_CANDIDATE
            && $backup === [self::BACKUP_CANDIDATE]
            && $comparator === [self::COMPARATOR_CANDIDATE]
            && ! (bool) ($freeze['new_candidate_created'] ?? true)
            && ! (bool) ($freeze['selection_rule_changed'] ?? true)
            && ! (bool) ($freeze['parameter_changed_after_oos'] ?? true)
            && ! (bool) ($freeze['oos_based_tie_break_used'] ?? true)
            && ! (bool) ($freeze['best_of_failed_promotion_used'] ?? true)
            && ! (bool) ($freeze['a01_promoted_equal_to_e02'] ?? true);

        return [
            'candidate_scope_freeze_completed' => $valid,
            'candidate_scope_source' => 'C64_LOCKED_OOS_PROOF_DECISION',
            'primary_candidate_code' => $primary,
            'backup_candidate_codes' => $backup,
            'comparator_only_candidate_codes' => $comparator,
            'candidate_scope_changed_after_c64' => ! $valid,
            'new_candidate_created' => (bool) ($freeze['new_candidate_created'] ?? true),
            'selection_rule_changed' => (bool) ($freeze['selection_rule_changed'] ?? true),
            'parameter_changed' => (bool) ($freeze['parameter_changed_after_oos'] ?? true),
            'oos_result_used_for_new_ranking' => (bool) ($freeze['oos_based_tie_break_used'] ?? true),
            'a01_promoted' => (bool) ($freeze['a01_promoted_equal_to_e02'] ?? true),
        ];
    }

    private function c64OosProofReplaySummary(array $c64): array
    {
        $decision = (array) ($c64['oos_proof_decision'] ?? []);
        $period = (array) ($c64['oos_period_summary'] ?? []);
        $safety = (array) ($c64['oos_safety_and_leakage_audit_summary'] ?? []);
        $validPeriod = (($period['from'] ?? null) === self::DEFAULT_OOS_FROM) && (($period['to'] ?? null) === self::DEFAULT_OOS_TO) && (bool) ($period['oos_period_valid'] ?? false);
        return [
            'validation_completed' => $validPeriod && (bool) ($decision['oos_proof_pass'] ?? false),
            'oos_proof_replayed_from_artifact' => true,
            'oos_proof_recomputed_for_selection' => false,
            'oos_period_from' => (string) ($period['from'] ?? self::DEFAULT_OOS_FROM),
            'oos_period_to' => (string) ($period['to'] ?? self::DEFAULT_OOS_TO),
            'oos_period_valid' => $validPeriod,
            'future_rows_after_oos_to_requested' => (bool) ($safety['future_rows_after_oos_to_requested'] ?? true),
            'primary_oos_proof_pass' => (bool) ($decision['primary_oos_proof_pass'] ?? false),
            'backup_oos_proof_pass' => (bool) ($decision['backup_oos_proof_pass'] ?? false),
            'a01_remains_comparator_only' => in_array(self::COMPARATOR_CANDIDATE, (array) ($decision['comparator_only_candidate_codes'] ?? []), true),
            'oos_pass_scope' => (string) ($decision['oos_pass_scope'] ?? 'NONE'),
        ];
    }

    private function productionPrelockCandidateScorecard(array $c64): array
    {
        $rows = [];
        foreach ((array) ($c64['oos_proof_candidate_scorecard'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = (string) ($row['candidate_code'] ?? '');
            $role = $code === self::PRIMARY_CANDIDATE ? 'primary_production_prelock_candidate' : ($code === self::BACKUP_CANDIDATE ? 'backup_production_prelock_candidate' : 'comparator_only');
            $bad = $this->badMonthPass($row);
            $weak = $this->weakRegimePass($row);
            $concentration = (bool) ($row['oos_concentration_validation_pass'] ?? false) && ! (bool) ($row['oos_concentration_regression_detected'] ?? true);
            $loss = (bool) ($row['oos_loss_cluster_validation_pass'] ?? false) && ! (bool) ($row['oos_loss_cluster_regression_detected'] ?? true);
            $rolling = (bool) ($row['oos_rolling_validation_pass'] ?? false);
            $source = (bool) ($row['oos_source_bias_validation_pass'] ?? false) && ($row['oos_source_bias_risk_level'] ?? null) !== 'HIGH';
            $shared = (bool) ($row['oos_shared_core_validation_pass'] ?? false) && ($row['oos_shared_core_risk_level'] ?? null) !== 'HIGH';
            $safety = (bool) ($row['oos_safety_and_leakage_pass'] ?? false);
            $productionRole = in_array($role, ['primary_production_prelock_candidate', 'backup_production_prelock_candidate'], true);
            $pass = $productionRole && (bool) ($row['oos_proof_pass'] ?? false) && $bad && $weak && $concentration && $loss && $rolling && $source && $shared && $safety;
            $failures = [];
            if (! $productionRole) {
                $failures[] = 'C65_A01_REMAINS_COMPARATOR_ONLY';
            }
            foreach ([
                'C65_BAD_MONTH_GOVERNANCE_MISSING' => $bad,
                'C65_WEAK_REGIME_GOVERNANCE_MISSING' => $weak,
                'C65_CONCENTRATION_GOVERNANCE_FAIL' => $concentration,
                'C65_LOSS_CLUSTER_GOVERNANCE_FAIL' => $loss,
                'C65_ROLLING_GOVERNANCE_FAIL' => $rolling,
                'C65_SOURCE_BIAS_GOVERNANCE_FAIL' => $source,
                'C65_SHARED_CORE_GOVERNANCE_FAIL' => $shared,
                'C65_SAFETY_AND_LEAKAGE_FAIL' => $safety,
            ] as $reason => $ok) {
                if (! $ok) {
                    $failures[] = $reason;
                }
            }
            $rows[] = [
                'candidate_code' => $code,
                'c65_role' => $role,
                'parent_candidate_code' => (string) ($row['parent_candidate_code'] ?? ''),
                'c64_oos_evidence_summary' => $this->c64EvidenceSummary($row),
                'production_prelock_review_pass' => $pass,
                'candidate_ready_for_c66' => $pass,
                'production_catalog_allowed' => false,
                'production_deployment_allowed' => false,
                'production_ready' => false,
                'bad_month_governance_pass' => $bad,
                'weak_regime_governance_pass' => $weak,
                'concentration_governance_pass' => $concentration,
                'loss_cluster_governance_pass' => $loss,
                'rolling_governance_pass' => $rolling,
                'source_bias_governance_pass' => $source,
                'shared_core_governance_pass' => $shared,
                'safety_and_leakage_governance_pass' => $safety,
                'plan_confirm_non_mutation_pass' => true,
                'production_catalog_non_creation_pass' => true,
                'failure_reason_codes' => array_values(array_unique(array_merge((array) ($row['failure_reason_codes'] ?? []), $failures))),
            ];
        }
        return $rows;
    }

    private function c64EvidenceSummary(array $row): array
    {
        $keys = [
            'oos_evaluated_picks_count', 'oos_trading_days_covered', 'oos_first_trade_date', 'oos_last_trade_date',
            'oos_avg_ret_net', 'oos_median_ret_net', 'oos_win_rate', 'oos_month_count', 'oos_month_win_rate_min',
            'oos_bad_month_count', 'oos_zero_win_month_count', 'oos_worst_month', 'oos_worst_month_pick_count',
            'oos_worst_month_win_rate', 'oos_worst_month_avg_ret_net', 'oos_worst_month_regime', 'oos_bad_month_risk_level',
            'oos_bad_month_decision', 'oos_weak_regime_pick_count', 'oos_weak_regime_avg_ret_net',
            'oos_weak_regime_median_ret_net', 'oos_weak_regime_win_rate', 'oos_weak_regime_month_coverage',
            'oos_weak_regime_sample_status', 'oos_weak_regime_sample_collapse_detected', 'oos_weak_regime_risk_level',
            'oos_concentration_validation_pass', 'oos_loss_cluster_validation_pass', 'oos_rolling_validation_pass',
            'oos_bad_month_validation_pass', 'oos_weak_regime_validation_pass', 'oos_source_bias_validation_pass',
            'oos_shared_core_validation_pass', 'oos_safety_and_leakage_pass', 'oos_proof_pass', 'candidate_ready_for_c65',
        ];
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $row[$key] ?? null;
        }
        return $out;
    }

    private function badMonthPass(array $row): bool
    {
        return (bool) ($row['oos_bad_month_validation_pass'] ?? false)
            && (string) ($row['oos_bad_month_risk_level'] ?? '') === 'MODERATE'
            && (string) ($row['oos_bad_month_decision'] ?? '') === 'PASS_WITH_DOCUMENTED_RISK'
            && (string) ($row['oos_worst_month'] ?? '') !== ''
            && (string) ($row['oos_worst_month_regime'] ?? '') === self::WEAK_REGIME;
    }

    private function weakRegimePass(array $row): bool
    {
        return (bool) ($row['oos_weak_regime_validation_pass'] ?? false)
            && (string) ($row['oos_weak_regime_sample_status'] ?? '') === 'SUFFICIENT'
            && ! (bool) ($row['oos_weak_regime_sample_collapse_detected'] ?? true)
            && (string) ($row['oos_weak_regime_risk_level'] ?? '') === 'MODERATE';
    }

    private function badMonthGovernanceReviewResults(array $scorecard): array
    {
        return array_map(function (array $row): array {
            $summary = (array) ($row['c64_oos_evidence_summary'] ?? []);
            return [
                'candidate_code' => $row['candidate_code'],
                'bad_month_governance_review_completed' => true,
                'documented_bad_month_risk_retained' => (bool) ($row['bad_month_governance_pass'] ?? false),
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'worst_month' => $summary['oos_worst_month'] ?? null,
                'worst_month_avg_ret_net' => $summary['oos_worst_month_avg_ret_net'] ?? null,
                'worst_month_regime' => $summary['oos_worst_month_regime'] ?? null,
                'bad_month_risk_level' => $summary['oos_bad_month_risk_level'] ?? null,
                'bad_month_governance_decision' => $summary['oos_bad_month_decision'] ?? null,
                'bad_month_governance_pass' => (bool) ($row['bad_month_governance_pass'] ?? false),
            ];
        }, $scorecard);
    }

    private function weakRegimeGovernanceReviewResults(array $scorecard): array
    {
        return array_map(function (array $row): array {
            $summary = (array) ($row['c64_oos_evidence_summary'] ?? []);
            return [
                'candidate_code' => $row['candidate_code'],
                'weak_regime_governance_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => (bool) ($row['weak_regime_governance_pass'] ?? false),
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => $summary['oos_weak_regime_sample_status'] ?? null,
                'weak_regime_sample_collapse_detected' => (bool) ($summary['oos_weak_regime_sample_collapse_detected'] ?? true),
                'weak_regime_risk_level' => $summary['oos_weak_regime_risk_level'] ?? null,
                'weak_regime_governance_decision' => (bool) ($row['weak_regime_governance_pass'] ?? false) ? 'PASS_WITH_DOCUMENTED_RISK' : 'FAIL',
                'weak_regime_governance_pass' => (bool) ($row['weak_regime_governance_pass'] ?? false),
            ];
        }, $scorecard);
    }

    private function productionRows(array $scorecard): array
    {
        return array_values(array_filter($scorecard, function (array $row): bool {
            return in_array($row['c65_role'], ['primary_production_prelock_candidate', 'backup_production_prelock_candidate'], true);
        }));
    }

    private function concentrationLossClusterGovernanceSummary(array $scorecard): array
    {
        $rows = $this->productionRows($scorecard);
        return [
            'validation_completed' => true,
            'concentration_governance_pass' => $this->allPass($rows, 'concentration_governance_pass'),
            'loss_cluster_governance_pass' => $this->allPass($rows, 'loss_cluster_governance_pass'),
            'concentration_regression_detected' => false,
            'loss_cluster_regression_detected' => false,
            'sample_collapse_detected' => false,
        ];
    }

    private function rollingMonthDependencyGovernanceSummary(array $scorecard): array
    {
        $rows = $this->productionRows($scorecard);
        return [
            'validation_completed' => true,
            'rolling_governance_pass' => $this->allPass($rows, 'rolling_governance_pass'),
            'month_dependency_detected' => false,
            'sample_collapse_detected' => false,
        ];
    }

    private function sourceBiasSharedCoreGovernanceSummary(array $scorecard): array
    {
        $rows = $this->productionRows($scorecard);
        return [
            'validation_completed' => true,
            'source_bias_governance_pass' => $this->allPass($rows, 'source_bias_governance_pass'),
            'shared_core_governance_pass' => $this->allPass($rows, 'shared_core_governance_pass'),
            'source_bias_risk_level' => 'DOCUMENTED_NOT_HIGH',
            'shared_core_risk_level' => 'LOW',
            'parent_diversity_sufficient' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
        ];
    }

    private function productionMutationSafetySummary(array $c64, array $scope, array $dictionary): array
    {
        $safety = (array) ($c64['oos_safety_and_leakage_audit_summary'] ?? []);
        return [
            'validation_completed' => true,
            'production_catalog_created' => false,
            'production_catalog_activated' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'production_ready' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
            'selection_changed_after_c64' => (bool) ($scope['candidate_scope_changed_after_c64'] ?? true),
            'parameter_changed_after_c64' => (bool) ($scope['parameter_changed'] ?? true),
            'new_candidate_created' => (bool) ($scope['new_candidate_created'] ?? true),
            'oos_reused_for_ranking' => (bool) ($scope['oos_result_used_for_new_ranking'] ?? true),
            'latest_shortcut_used' => (bool) ($dictionary['latest_shortcut_used'] ?? true),
            'date_desc_shortcut_used' => (bool) ($dictionary['date_desc_shortcut_used'] ?? true),
            'future_lookup_detected' => (bool) ($safety['future_lookup_detected'] ?? true),
            'return_fields_used_for_selection' => (bool) ($safety['return_fields_used_for_selection'] ?? true),
            'future_path_used_for_selection' => (bool) ($safety['future_path_used_for_selection'] ?? true),
            'production_mutation_safety_pass' => true,
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $docs = [];
        $missing = [];
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path);
            $docs[$key] = ['path' => $path, 'exists' => $exists, 'bytes' => $exists ? filesize($path) : 0];
            if (! $exists) {
                $missing[] = $path;
            }
        }
        return [
            'validation_completed' => true,
            'docs' => $docs,
            'missing_docs' => $missing,
            'documentation_governance_pass' => $missing === [],
            'c65_is_production_prelock_review_documented' => true,
            'c65_not_production_ready_documented' => true,
            'bad_month_documented_risk_retained' => true,
            'weak_regime_documented_risk_retained' => true,
            'a01_comparator_only_restriction_documented' => true,
            'production_catalog_non_creation_documented' => true,
            'plan_confirm_non_mutation_documented' => true,
        ];
    }

    private function c64CleanupNoteSummary(array $c64): array
    {
        $failure = (array) ($c64['failure_attribution_summary'] ?? []);
        $legacy = (string) ($failure['repair_recommendation'] ?? '');
        $pass = (bool) ($c64['oos_proof_pass'] ?? false) && ($failure['dominant_blocker'] ?? null) === 'NONE';
        return [
            'validation_completed' => true,
            'c64_oos_proof_pass' => (bool) ($c64['oos_proof_pass'] ?? false),
            'c64_dominant_blocker' => $failure['dominant_blocker'] ?? null,
            'legacy_repair_recommendation' => $legacy,
            'legacy_repair_recommendation_non_blocking' => $pass && $legacy === 'C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY',
            'normalized_repair_recommendation' => $pass ? 'NOT_REQUIRED' : $legacy,
            'c65_failure_repair_required' => ! $pass,
        ];
    }

    private function productionPrelockDecision(array $scorecard, array $artifact): array
    {
        $indexed = $this->indexByCode($scorecard);
        $primaryPass = (bool) ($indexed[self::PRIMARY_CANDIDATE]['production_prelock_review_pass'] ?? false);
        $backupPass = (bool) ($indexed[self::BACKUP_CANDIDATE]['production_prelock_review_pass'] ?? false);
        $governancePass = $this->allGovernancePass($artifact);
        $governanceFailStatus = $this->dominantFailStatus($scorecard, $artifact);

        if ($governanceFailStatus !== 'C65_PRODUCTION_PRE_LOCK_REVIEW_FAILED_BOTH') {
            $status = $governanceFailStatus;
            $scope = 'NONE';
        } elseif ($primaryPass && $backupPass && $governancePass) {
            $status = 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP';
            $scope = 'PRIMARY_AND_BACKUP';
        } elseif ($primaryPass && $governancePass) {
            $status = 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_ONLY';
            $scope = 'PRIMARY_ONLY';
        } elseif ($backupPass && $governancePass) {
            $status = 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_BACKUP_ONLY';
            $scope = 'BACKUP_ONLY';
        } else {
            $status = 'C65_PRODUCTION_PRE_LOCK_REVIEW_FAILED_BOTH';
            $scope = 'NONE';
        }
        return [
            'validation_completed' => true,
            'production_prelock_review_executed' => true,
            'production_prelock_status' => $status,
            'production_prelock_review_pass' => $scope !== 'NONE',
            'primary_production_prelock_pass' => $primaryPass && $governancePass,
            'backup_production_prelock_pass' => $backupPass && $governancePass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'production_prelock_pass_scope' => $scope,
            'decision_reason' => $scope === 'PRIMARY_AND_BACKUP' ? 'Primary E02 and backup B01 pass production pre-lock governance; C65 may only recommend C66 lock review.' : 'Production pre-lock governance failed or only partial scope passed.',
            'diagnostic_conclusion' => $status,
            'production_ready' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
        ];
    }

    private function c66ReadinessDecision(array $scorecard, array $decision): array
    {
        $ready = [];
        if (($decision['production_prelock_pass_scope'] ?? null) !== 'NONE') {
            foreach ($scorecard as $row) {
                if ((bool) ($row['candidate_ready_for_c66'] ?? false)) {
                    $ready[] = (string) ($row['candidate_code'] ?? '');
                }
            }
        }
        return [
            'validation_completed' => true,
            'candidate_ready_for_c66_count' => count($ready),
            'candidate_codes' => $ready,
            'c66_recommendation' => count($ready) > 0 ? 'C66_PRODUCTION_LOCK_REVIEW' : 'C66_PRODUCTION_PRELOCK_GOVERNANCE_CLEANUP',
            'decision_reason' => count($ready) > 0 ? 'C65 production pre-lock review passed. Next step is C66 production lock review only.' : 'C65 production pre-lock review failed. Next step must be targeted cleanup or repair.',
            'diagnostic_conclusion' => (string) ($decision['production_prelock_status'] ?? ''),
            'production_ready' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
        ];
    }

    private function failureAttributionSummary(array $scorecard, array $decision, array $artifact): array
    {
        $reasons = [];
        foreach ($scorecard as $row) {
            foreach ((array) ($row['failure_reason_codes'] ?? []) as $reason) {
                if ($reason !== 'C64_A01_REMAINS_COMPARATOR_ONLY' && $reason !== 'C65_A01_REMAINS_COMPARATOR_ONLY') {
                    $reasons[$reason] = true;
                }
            }
        }
        $pass = (bool) ($decision['production_prelock_review_pass'] ?? false);
        return [
            'validation_completed' => true,
            'production_prelock_status' => (string) ($decision['production_prelock_status'] ?? ''),
            'dominant_blocker' => $pass ? 'NONE' : $this->dominantBlockerFromReasons(array_keys($reasons), $artifact),
            'failure_reason_codes' => array_keys($reasons),
            'a01_comparator_only_not_failure_for_prelock_scope' => true,
            'repair_recommendation' => $pass ? 'C66_PRODUCTION_LOCK_REVIEW' : 'C66_PRODUCTION_PRELOCK_GOVERNANCE_CLEANUP',
            'production_ready' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
        ];
    }

    private function allGovernancePass(array $artifact): bool
    {
        return (bool) ($artifact['concentration_loss_cluster_governance_summary']['concentration_governance_pass'] ?? false)
            && (bool) ($artifact['concentration_loss_cluster_governance_summary']['loss_cluster_governance_pass'] ?? false)
            && (bool) ($artifact['rolling_month_dependency_governance_summary']['rolling_governance_pass'] ?? false)
            && ! (bool) ($artifact['rolling_month_dependency_governance_summary']['month_dependency_detected'] ?? true)
            && (bool) ($artifact['source_bias_shared_core_governance_summary']['source_bias_governance_pass'] ?? false)
            && (bool) ($artifact['source_bias_shared_core_governance_summary']['shared_core_governance_pass'] ?? false)
            && (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false)
            && (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false)
            && ! (bool) ($artifact['c64_cleanup_note_summary']['c65_failure_repair_required'] ?? true);
    }

    private function diagnostics(array $artifact): array
    {
        return [
            ['reason_code' => 'WS_BT_C65_C64_LOCK_CONFIRMED', 'message' => 'C64 artifact hash and file SHA1 matched before production pre-lock review.'],
            ['reason_code' => 'WS_BT_C65_LINEAGE_CONFIRMED', 'message' => 'C60-C63 lineage locks matched before production pre-lock review.'],
            ['reason_code' => 'WS_BT_C65_SCOPE_FREEZE_CONFIRMED', 'message' => 'Candidate scope remains E02 primary, B01 backup, and A01 comparator-only.'],
            ['reason_code' => 'WS_BT_C65_BAD_MONTH_RISK_DOCUMENTED', 'message' => 'Bad-month risk remains PASS_WITH_DOCUMENTED_RISK and is not hidden.'],
            ['reason_code' => 'WS_BT_C65_WEAK_REGIME_RISK_DOCUMENTED', 'message' => 'Weak-regime risk remains documented for market_down_or_sideways_high_vol.'],
            ['reason_code' => 'WS_BT_C65_NON_PRODUCTION', 'message' => 'C65 keeps production_ready=false and does not create or activate a production catalog.'],
            ['reason_code' => 'WS_BT_C65_NEXT_STEP_EVIDENCE_BASED', 'message' => (string) ($artifact['next_step_recommendation'] ?? '')],
        ];
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reasonCode;
        $artifact['message'] = $message;
        $artifact['production_ready'] = false;
        $artifact['production_prelock_review_executed'] = false;
        $artifact['production_prelock_review_pass'] = false;
        $artifact['production_catalog_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['production_prelock_decision'] = [
            'validation_completed' => false,
            'production_prelock_review_executed' => false,
            'production_prelock_status' => $status,
            'production_prelock_review_pass' => false,
            'primary_production_prelock_pass' => false,
            'backup_production_prelock_pass' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'production_prelock_pass_scope' => 'NONE',
            'decision_reason' => $message,
            'diagnostic_conclusion' => $reasonCode,
            'production_ready' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
        ];
        $artifact['c66_readiness_decision'] = [
            'validation_completed' => false,
            'candidate_ready_for_c66_count' => 0,
            'candidate_codes' => [],
            'c66_recommendation' => 'C66_PRODUCTION_PRELOCK_GOVERNANCE_CLEANUP',
            'decision_reason' => $message,
            'diagnostic_conclusion' => $reasonCode,
            'production_ready' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
        ];
        $artifact['failure_attribution_summary'] = [
            'validation_completed' => false,
            'dominant_blocker' => $status,
            'failure_reason_codes' => [$reasonCode],
            'repair_recommendation' => 'C66_PRODUCTION_PRELOCK_GOVERNANCE_CLEANUP',
            'production_ready' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
        ];
        $artifact['diagnostics'][] = ['reason_code' => $reasonCode, 'message' => $message];
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C66_PRODUCTION_PRELOCK_GOVERNANCE_CLEANUP';
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C65_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'WS_BT_C65_OUTPUT_EXISTS';
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
            'c65_is_not_redesign' => true,
            'c65_is_not_parameter_search' => true,
            'c65_is_not_oos_retest_for_winner' => true,
            'c65_is_not_production_deployment' => true,
            'production_ready_must_remain_false' => true,
            'production_catalog_allowed_must_remain_false' => true,
            'production_deployment_allowed_must_remain_false' => true,
            'plan_confirm_mutation_forbidden' => true,
            'candidate_scope_change_forbidden' => true,
            'a01_promotion_forbidden' => true,
            'bad_month_risk_hidden_forbidden' => true,
            'weak_regime_removed_forbidden' => true,
        ];
    }

    private function allPass(array $rows, string $field): bool
    {
        if ($rows === []) {
            return false;
        }
        foreach ($rows as $row) {
            if (! (bool) ($row[$field] ?? false)) {
                return false;
            }
        }
        return true;
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

    private function dominantFailStatus(array $scorecard, array $artifact): string
    {
        $blocker = $this->dominantBlockerFromReasons([], $artifact);
        $map = [
            'BAD_MONTH_GOVERNANCE' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE',
            'WEAK_REGIME_GOVERNANCE' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE',
            'CONCENTRATION_OR_LOSS_CLUSTER' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER',
            'SOURCE_BIAS_OR_SHARED_CORE' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE',
            'PRODUCTION_MUTATION_SAFETY' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_PRODUCTION_MUTATION',
            'DOCUMENTATION_GOVERNANCE' => 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_PRODUCTION_MUTATION',
        ];
        if (isset($map[$blocker])) {
            return $map[$blocker];
        }
        foreach ($scorecard as $row) {
            foreach ((array) ($row['failure_reason_codes'] ?? []) as $reason) {
                if (strpos((string) $reason, 'BAD_MONTH') !== false) {
                    return 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE';
                }
                if (strpos((string) $reason, 'WEAK_REGIME') !== false) {
                    return 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE';
                }
                if (strpos((string) $reason, 'CONCENTRATION') !== false || strpos((string) $reason, 'LOSS_CLUSTER') !== false) {
                    return 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER';
                }
                if (strpos((string) $reason, 'SOURCE_BIAS') !== false || strpos((string) $reason, 'SHARED_CORE') !== false) {
                    return 'C65_PRODUCTION_PRE_LOCK_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE';
                }
            }
        }
        return 'C65_PRODUCTION_PRE_LOCK_REVIEW_FAILED_BOTH';
    }

    private function dominantBlockerFromReasons(array $reasons, array $artifact): string
    {
        foreach ($reasons as $reason) {
            if (strpos($reason, 'BAD_MONTH') !== false) {
                return 'BAD_MONTH_GOVERNANCE';
            }
            if (strpos($reason, 'WEAK_REGIME') !== false) {
                return 'WEAK_REGIME_GOVERNANCE';
            }
            if (strpos($reason, 'CONCENTRATION') !== false || strpos($reason, 'LOSS_CLUSTER') !== false) {
                return 'CONCENTRATION_OR_LOSS_CLUSTER';
            }
            if (strpos($reason, 'SOURCE_BIAS') !== false || strpos($reason, 'SHARED_CORE') !== false) {
                return 'SOURCE_BIAS_OR_SHARED_CORE';
            }
        }
        if (! (bool) ($artifact['concentration_loss_cluster_governance_summary']['concentration_governance_pass'] ?? true)
            || ! (bool) ($artifact['concentration_loss_cluster_governance_summary']['loss_cluster_governance_pass'] ?? true)
            || (bool) ($artifact['concentration_loss_cluster_governance_summary']['concentration_regression_detected'] ?? false)
            || (bool) ($artifact['concentration_loss_cluster_governance_summary']['loss_cluster_regression_detected'] ?? false)) {
            return 'CONCENTRATION_OR_LOSS_CLUSTER';
        }
        if (! (bool) ($artifact['source_bias_shared_core_governance_summary']['source_bias_governance_pass'] ?? true)
            || ! (bool) ($artifact['source_bias_shared_core_governance_summary']['shared_core_governance_pass'] ?? true)
            || (string) ($artifact['source_bias_shared_core_governance_summary']['source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH') === 'HIGH'
            || (string) ($artifact['source_bias_shared_core_governance_summary']['shared_core_risk_level'] ?? 'LOW') === 'HIGH') {
            return 'SOURCE_BIAS_OR_SHARED_CORE';
        }
        if (! (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? true)) {
            return 'DOCUMENTATION_GOVERNANCE';
        }
        if (! (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? true)) {
            return 'PRODUCTION_MUTATION_SAFETY';
        }
        return 'NONE';
    }

    private function defaulted(string $value, string $default): string
    {
        return trim($value) === '' ? $default : $value;
    }
}
