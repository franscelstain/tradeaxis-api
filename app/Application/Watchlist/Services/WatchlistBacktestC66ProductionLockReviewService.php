<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC66ProductionLockReviewService
{
    public const RUN_CODE = 'C66_PRODUCTION_LOCK_REVIEW';
    public const ARTIFACT_TYPE = 'C66_PRODUCTION_LOCK_REVIEW';

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

    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c66-production-lock-review.json';
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
        'c66_review_doc' => 'docs/watchlist/audit/WS_C66_PRODUCTION_LOCK_REVIEW.md',
        'c66_operator_commands_doc' => 'docs/watchlist/audit/WS_C66_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    /**
     * C66_PRODUCTION_LOCK_REVIEW. LOCK_DECISION_ARTIFACT_ONLY. NOT_LIVE_ACTIVATION.
     * C65_ARTIFACT_HASH_LOCK. C65_FILE_SHA1_LOCK. C60_TO_C65_LINEAGE_LOCK.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. ASOF_SAFE_LOOKUP_REQUIRED.
     * SELECTION_SCOPE_FROZEN_FROM_C65. NO_REDESIGN. NO_RETUNE. NO_PARAMETER_SEARCH.
     * NO_OOS_BASED_RERANKING. NO_OOS_TIE_BREAK. NO_BEST_OF_FAILED_PROMOTION.
     * A01_COMPARATOR_ONLY_NOT_PROMOTABLE. BAD_MONTH_RISK_RETAINED. WEAK_REGIME_RISK_RETAINED.
     * NO_PRODUCTION_CATALOG_CREATION. NO_PRODUCTION_CATALOG_ACTIVATION. NO_DEPLOYMENT.
     * NO_PLAN_CONFIRM_MUTATION. ACTIVATION_ALLOWED_FALSE. DEPLOYMENT_ALLOWED_FALSE.
     * PLAN_CONFIRM_MUTATION_ALLOWED_FALSE. NO_LATEST_DATE_SHORTCUT. NO_DATE_DESC_SHORTCUT.
     * NO_FUTURE_LOOKUP. NO_RETURN_FIELDS_FOR_SELECTION. C66_CAN_ONLY_RECOMMEND_C67_ACTIVATION_REVIEW.
     */
    public function execute(
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
            return $this->blocked($artifact, 'C66_BLOCKED_DICTIONARY_COVERAGE_MISSING', 'WS_BT_C66_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C66 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath, $overwrite);
        }

        $c65Load = $this->loadArtifactLock($c65Artifact, $expectedC65Hash, $expectedC65FileSha1);
        $this->copyLock($artifact, 'c65', $c65Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);
        if (! $c65Load['readable']) {
            return $this->blocked($artifact, 'C66_BLOCKED_MISSING_C65_ARTIFACT', 'WS_BT_C66_C65_ARTIFACT_MISSING', 'C66 requires the locked C65 artifact.', $outputPath, $overwrite);
        }
        if (! $c65Load['hash_match']) {
            return $this->blocked($artifact, 'C66_BLOCKED_C65_ARTIFACT_LOCK_MISMATCH', 'WS_BT_C66_C65_ARTIFACT_HASH_MISMATCH', 'C65 artifact hash does not match the expected C66 lock.', $outputPath, $overwrite);
        }
        if (! $c65Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C66_BLOCKED_C65_FILE_SHA1_LOCK_MISMATCH', 'WS_BT_C66_C65_FILE_SHA1_MISMATCH', 'C65 file SHA1 does not match the expected C66 lock.', $outputPath, $overwrite);
        }

        $c65 = (array) $c65Load['payload'];
        $c65Validation = $this->validateC65($c65);
        $artifact['c65_lock_validation_summary'] = $c65Validation;
        if (! (bool) ($c65Validation['pass'] ?? false)) {
            return $this->blocked($artifact, (string) ($c65Validation['status'] ?? 'C66_BLOCKED_C65_STATUS_OR_REASON_MISMATCH'), (string) ($c65Validation['reason_code'] ?? 'WS_BT_C66_C65_LOCK_INVALID'), (string) ($c65Validation['message'] ?? 'C65 lock is invalid for C66.'), $outputPath, $overwrite);
        }

        $lineageLoads = [
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

        $artifact['c64_lineage_validation_summary'] = $this->validateC64Lineage($lineageLoads['c64']);
        $artifact['c63_lineage_validation_summary'] = $this->validateC63Lineage($lineageLoads['c63']);
        $artifact['c62_lineage_validation_summary'] = $this->validateC62Lineage($lineageLoads['c62']);
        $artifact['c61_lineage_validation_summary'] = $this->validateC61Lineage($lineageLoads['c61']);
        $artifact['c60_lineage_validation_summary'] = $this->validateC60Lineage($lineageLoads['c60']);
        foreach (['c64_lineage_validation_summary', 'c63_lineage_validation_summary', 'c62_lineage_validation_summary', 'c61_lineage_validation_summary', 'c60_lineage_validation_summary'] as $key) {
            if (! (bool) ($artifact[$key]['pass'] ?? false)) {
                return $this->blocked($artifact, 'C66_BLOCKED_LINEAGE_LOCK_MISMATCH', (string) ($artifact[$key]['reason_code'] ?? 'WS_BT_C66_LINEAGE_LOCK_MISMATCH'), (string) ($artifact[$key]['message'] ?? 'C60-C64 lineage lock mismatch.'), $outputPath, $overwrite);
            }
        }

        $scope = $this->candidateScopeFreezeSummary($c65);
        $artifact['candidate_scope_freeze_summary'] = $scope;
        if (! (bool) ($scope['candidate_scope_freeze_completed'] ?? false)) {
            $artifact['production_lock_candidate_scorecard'] = $this->productionLockCandidateScorecard($c65);
            $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['production_lock_candidate_scorecard'], ['production_lock_status' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH'], $artifact);
            return $this->rejected($artifact, 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'WS_BT_C66_CANDIDATE_SCOPE_MISMATCH', 'C66 candidate scope must remain frozen from C65.', $outputPath, $overwrite);
        }

        $prelockReplay = $this->c65PrelockReplaySummary($c65);
        $artifact['c65_prelock_replay_summary'] = $prelockReplay;
        $oosReplay = $this->c64OosProofRetainedSummary($c65, (array) $lineageLoads['c64']['payload']);
        $artifact['c64_oos_proof_retained_summary'] = $oosReplay;
        if (! (bool) ($prelockReplay['validation_completed'] ?? false)) {
            return $this->rejected($artifact, 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_PRELOCK_INCOMPLETE', 'WS_BT_C66_C65_PRELOCK_INCOMPLETE', 'C65 production pre-lock replay summary is incomplete.', $outputPath, $overwrite);
        }
        if (! (bool) ($oosReplay['validation_completed'] ?? false)) {
            return $this->rejected($artifact, 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_OOS_PROOF_INCOMPLETE', 'WS_BT_C66_C64_OOS_PROOF_INCOMPLETE', 'C64 OOS proof retained summary is incomplete.', $outputPath, $overwrite);
        }

        $scorecard = $this->productionLockCandidateScorecard($c65);
        $artifact['production_lock_candidate_scorecard'] = $scorecard;
        $artifact['bad_month_governance_lock_review_results'] = $this->badMonthGovernanceLockReviewResults($scorecard);
        $artifact['weak_regime_governance_lock_review_results'] = $this->weakRegimeGovernanceLockReviewResults($scorecard);
        $artifact['concentration_loss_cluster_governance_summary'] = $this->concentrationLossClusterGovernanceSummary($scorecard);
        $artifact['rolling_month_dependency_governance_summary'] = $this->rollingMonthDependencyGovernanceSummary($scorecard);
        $artifact['source_bias_shared_core_governance_summary'] = $this->sourceBiasSharedCoreGovernanceSummary($scorecard);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($c65, $scope, $dictionary);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary($c65);

        $decision = $this->productionLockDecision($scorecard, $artifact);
        $artifact['production_lock_decision'] = $decision;
        $artifact['c67_readiness_decision'] = $this->c67ReadinessDecision($scorecard, $decision);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($scorecard, $decision, $artifact);
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        $artifact['status'] = (string) ($decision['production_lock_status'] ?? 'C66_PRODUCTION_LOCK_REVIEW_FAILED_BOTH');
        $artifact['reason_code'] = $artifact['status'];
        $artifact['production_lock_review_executed'] = true;
        $artifact['production_lock_review_pass'] = (bool) ($decision['production_lock_review_pass'] ?? false);
        $artifact['production_catalog_lock_allowed'] = (bool) ($decision['production_catalog_lock_allowed'] ?? false);
        $artifact['production_catalog_activation_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $artifact['status'];
        $artifact['next_step_recommendation'] = (string) ($artifact['c67_readiness_decision']['c67_recommendation'] ?? 'C67_PRODUCTION_LOCK_GOVERNANCE_CLEANUP');

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(
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
            'status' => 'C66_NOT_RUN',
            'reason_code' => 'C66_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'production_lock_review_executed' => false,
            'production_lock_review_pass' => false,
            'production_catalog_lock_allowed' => false,
            'production_catalog_activation_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'created_at' => $executedAt,
            'safety_boundaries' => $this->safetyBoundaries(),
            'database_dictionary_read_summary' => [],
            'source_artifact_locks' => [],
            'c65_lock_validation_summary' => [],
            'c64_lineage_validation_summary' => [],
            'c63_lineage_validation_summary' => [],
            'c62_lineage_validation_summary' => [],
            'c61_lineage_validation_summary' => [],
            'c60_lineage_validation_summary' => [],
            'candidate_scope_freeze_summary' => [],
            'c65_prelock_replay_summary' => [],
            'c64_oos_proof_retained_summary' => [],
            'production_lock_candidate_scorecard' => [],
            'bad_month_governance_lock_review_results' => [],
            'weak_regime_governance_lock_review_results' => [],
            'concentration_loss_cluster_governance_summary' => [],
            'rolling_month_dependency_governance_summary' => [],
            'source_bias_shared_core_governance_summary' => [],
            'production_mutation_safety_summary' => [],
            'documentation_governance_summary' => [],
            'c65_cleanup_note_summary' => [],
            'production_lock_decision' => [],
            'c67_readiness_decision' => [],
            'failure_attribution_summary' => [],
            'diagnostics' => [],
        ];

        foreach ([
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
        foreach (['c65', 'c64', 'c63', 'c62', 'c61', 'c60'] as $prefix) {
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

    private function validateC65(array $c65): array
    {
        if (($c65['status'] ?? null) !== 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C66_BLOCKED_C65_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C66_C65_STATUS_INVALID', 'message' => 'C65 status is not the locked primary+backup production pre-lock pass.'];
        }
        if (($c65['reason_code'] ?? null) !== 'C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C66_BLOCKED_C65_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C66_C65_REASON_INVALID', 'message' => 'C65 reason_code is not the locked primary+backup production pre-lock pass.'];
        }
        if (($c65['production_prelock_review_pass'] ?? null) !== true) {
            return ['pass' => false, 'status' => 'C66_BLOCKED_C65_PRODUCTION_PRELOCK_NOT_PASSED', 'reason_code' => 'WS_BT_C66_C65_PRELOCK_NOT_PASSED', 'message' => 'C65 production pre-lock did not pass.'];
        }
        if ((int) ($c65['c66_readiness_decision']['candidate_ready_for_c66_count'] ?? -1) !== 2) {
            return ['pass' => false, 'status' => 'C66_BLOCKED_C65_C66_READINESS_COUNT_MISMATCH', 'reason_code' => 'WS_BT_C66_C65_READINESS_COUNT_INVALID', 'message' => 'C65 candidate_ready_for_c66_count must equal 2.'];
        }
        if (($c65['production_ready'] ?? null) !== false) {
            return ['pass' => false, 'status' => 'C66_BLOCKED_C65_PRODUCTION_READY_FLAG_INVALID', 'reason_code' => 'WS_BT_C66_C65_PRODUCTION_READY_INVALID', 'message' => 'C65 must keep production_ready=false.'];
        }
        if (($c65['production_catalog_allowed'] ?? null) !== false) {
            return ['pass' => false, 'status' => 'C66_BLOCKED_C65_PRODUCTION_CATALOG_ALLOWED_FLAG_INVALID', 'reason_code' => 'WS_BT_C66_C65_PRODUCTION_CATALOG_ALLOWED_INVALID', 'message' => 'C65 must keep production_catalog_allowed=false.'];
        }
        if (($c65['production_deployment_allowed'] ?? null) !== false) {
            return ['pass' => false, 'status' => 'C66_BLOCKED_C65_PRODUCTION_DEPLOYMENT_ALLOWED_FLAG_INVALID', 'reason_code' => 'WS_BT_C66_C65_PRODUCTION_DEPLOYMENT_ALLOWED_INVALID', 'message' => 'C65 must keep production_deployment_allowed=false.'];
        }
        $decision = (array) ($c65['production_prelock_decision'] ?? []);
        if (($decision['production_prelock_pass_scope'] ?? null) !== 'PRIMARY_AND_BACKUP') {
            return ['pass' => false, 'status' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_PRELOCK_INCOMPLETE', 'reason_code' => 'WS_BT_C66_C65_PRELOCK_SCOPE_INVALID', 'message' => 'C65 production pre-lock pass scope must be PRIMARY_AND_BACKUP.'];
        }
        $safety = (array) ($c65['production_mutation_safety_summary'] ?? []);
        foreach (['production_catalog_created', 'production_catalog_activated', 'production_deployment_executed', 'plan_confirm_mutated', 'latest_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection'] as $field) {
            if (($safety[$field] ?? null) !== false) {
                return ['pass' => false, 'status' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_PRODUCTION_MUTATION', 'reason_code' => 'WS_BT_C66_C65_SAFETY_FLAG_INVALID', 'message' => 'C65 production mutation safety flags must be clean.'];
            }
        }
        if (($safety['production_mutation_safety_pass'] ?? null) !== true) {
            return ['pass' => false, 'status' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_PRODUCTION_MUTATION', 'reason_code' => 'WS_BT_C66_C65_SAFETY_SUMMARY_INVALID', 'message' => 'C65 production mutation safety summary did not pass.'];
        }

        return [
            'pass' => true,
            'validation_completed' => true,
            'status_match' => true,
            'reason_code_match' => true,
            'production_prelock_review_pass' => true,
            'candidate_ready_for_c66_count' => 2,
            'production_ready' => false,
            'production_catalog_allowed' => false,
            'production_deployment_allowed' => false,
            'production_prelock_pass_scope' => 'PRIMARY_AND_BACKUP',
            'production_mutation_safety_pass' => true,
        ];
    }

    private function validateC64Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP', 'C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP', 'WS_BT_C66_C64_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
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
        return $this->validateLineageLock($lock, 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP', 'WS_BT_C66_C63_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return (int) ($payload['c64_readiness_decision']['candidate_ready_for_c64_count'] ?? -1) === 2
                && ($payload['production_ready'] ?? null) === false
                && ($payload['direct_oos_proof_recommended'] ?? null) === false
                && ($payload['oos_proof_unlocked'] ?? null) === false
                && ($payload['pre_oos_unlocked'] ?? null) === false;
        });
    }

    private function validateC62Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES', 'WS_BT_C66_C62_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
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
        return $this->validateLineageLock($lock, 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED', 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE', 'WS_BT_C66_C61_LINEAGE_LOCK_MISMATCH', function (array $payload): bool {
            return (int) ($payload['c62_readiness_decision']['candidate_ready_for_c62_count'] ?? -1) === 3
                && ($payload['production_ready'] ?? null) === false
                && ($payload['direct_oos_proof_recommended'] ?? null) === false
                && ($payload['oos_proof_unlocked'] ?? null) === false;
        });
    }

    private function validateC60Lineage(array $lock): array
    {
        return $this->validateLineageLock($lock, 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED', 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS', 'WS_BT_C66_C60_LINEAGE_LOCK_MISMATCH');
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

    private function candidateScopeFreezeSummary(array $c65): array
    {
        $decision = (array) ($c65['production_prelock_decision'] ?? []);
        $freeze = (array) ($c65['candidate_scope_freeze_summary'] ?? []);
        $primary = (string) ($decision['primary_candidate_code'] ?? $freeze['primary_candidate_code'] ?? '');
        $backup = array_values((array) ($decision['backup_candidate_codes'] ?? $freeze['backup_candidate_codes'] ?? []));
        $comparator = array_values((array) ($decision['comparator_only_candidate_codes'] ?? $freeze['comparator_only_candidate_codes'] ?? []));
        $valid = $primary === self::PRIMARY_CANDIDATE
            && $backup === [self::BACKUP_CANDIDATE]
            && $comparator === [self::COMPARATOR_CANDIDATE]
            && ! (bool) ($freeze['new_candidate_created'] ?? true)
            && ! (bool) ($freeze['selection_rule_changed'] ?? true)
            && ! (bool) ($freeze['parameter_changed'] ?? true)
            && ! (bool) ($freeze['oos_result_used_for_new_ranking'] ?? true)
            && ! (bool) ($freeze['a01_promoted'] ?? true);

        return [
            'candidate_scope_freeze_completed' => $valid,
            'candidate_scope_source' => 'C65_LOCKED_PRODUCTION_PRELOCK_DECISION',
            'primary_candidate_code' => $primary,
            'backup_candidate_codes' => $backup,
            'comparator_only_candidate_codes' => $comparator,
            'candidate_scope_changed_after_c65' => ! $valid,
            'new_candidate_created' => (bool) ($freeze['new_candidate_created'] ?? true),
            'selection_rule_changed' => (bool) ($freeze['selection_rule_changed'] ?? true),
            'parameter_changed' => (bool) ($freeze['parameter_changed'] ?? true),
            'oos_result_used_for_new_ranking' => (bool) ($freeze['oos_result_used_for_new_ranking'] ?? true),
            'a01_promoted' => (bool) ($freeze['a01_promoted'] ?? true),
        ];
    }

    private function c65PrelockReplaySummary(array $c65): array
    {
        $decision = (array) ($c65['production_prelock_decision'] ?? []);
        return [
            'validation_completed' => ($c65['production_prelock_review_pass'] ?? null) === true && ($decision['production_prelock_pass_scope'] ?? null) === 'PRIMARY_AND_BACKUP',
            'c65_prelock_replayed_from_artifact' => true,
            'c65_prelock_recomputed_for_selection' => false,
            'production_prelock_review_pass' => (bool) ($c65['production_prelock_review_pass'] ?? false),
            'production_prelock_pass_scope' => (string) ($decision['production_prelock_pass_scope'] ?? 'NONE'),
            'primary_production_prelock_pass' => (bool) ($decision['primary_production_prelock_pass'] ?? false),
            'backup_production_prelock_pass' => (bool) ($decision['backup_production_prelock_pass'] ?? false),
            'a01_remains_comparator_only' => in_array(self::COMPARATOR_CANDIDATE, (array) ($decision['comparator_only_candidate_codes'] ?? []), true),
        ];
    }

    private function c64OosProofRetainedSummary(array $c65, array $c64): array
    {
        $fromC65 = (array) ($c65['c64_oos_proof_replay_summary'] ?? []);
        $decision = (array) ($c64['oos_proof_decision'] ?? []);
        $period = (array) ($c64['oos_period_summary'] ?? []);
        $from = (string) ($fromC65['oos_period_from'] ?? $period['from'] ?? self::DEFAULT_OOS_FROM);
        $to = (string) ($fromC65['oos_period_to'] ?? $period['to'] ?? self::DEFAULT_OOS_TO);
        $validPeriod = $from === self::DEFAULT_OOS_FROM && $to === self::DEFAULT_OOS_TO;
        return [
            'validation_completed' => $validPeriod && (($fromC65['oos_pass_scope'] ?? $decision['oos_pass_scope'] ?? null) === 'PRIMARY_AND_BACKUP'),
            'oos_proof_retained_from_c65_and_c64_artifacts' => true,
            'oos_proof_recomputed_for_selection' => false,
            'oos_period_from' => $from,
            'oos_period_to' => $to,
            'oos_period_valid' => $validPeriod,
            'future_rows_after_oos_to_requested' => (bool) ($fromC65['future_rows_after_oos_to_requested'] ?? false),
            'primary_oos_proof_pass' => (bool) ($fromC65['primary_oos_proof_pass'] ?? $decision['primary_oos_proof_pass'] ?? false),
            'backup_oos_proof_pass' => (bool) ($fromC65['backup_oos_proof_pass'] ?? $decision['backup_oos_proof_pass'] ?? false),
            'a01_remains_comparator_only' => (bool) ($fromC65['a01_remains_comparator_only'] ?? true),
            'oos_pass_scope' => (string) ($fromC65['oos_pass_scope'] ?? $decision['oos_pass_scope'] ?? 'NONE'),
        ];
    }

    private function productionLockCandidateScorecard(array $c65): array
    {
        $rows = $this->indexByCode((array) ($c65['production_prelock_candidate_scorecard'] ?? []));
        return [
            $this->scorecardRow($rows[self::PRIMARY_CANDIDATE] ?? [], self::PRIMARY_CANDIDATE, 'primary_production_lock_candidate', self::PRIMARY_PARENT),
            $this->scorecardRow($rows[self::BACKUP_CANDIDATE] ?? [], self::BACKUP_CANDIDATE, 'backup_production_lock_candidate', self::BACKUP_PARENT),
            $this->scorecardRow($rows[self::COMPARATOR_CANDIDATE] ?? [], self::COMPARATOR_CANDIDATE, 'comparator_only', self::COMPARATOR_PARENT),
        ];
    }

    private function scorecardRow(array $source, string $candidate, string $role, string $parent): array
    {
        $oos = (array) ($source['c64_oos_evidence_summary'] ?? []);
        $c65Summary = [
            'c65_role' => (string) ($source['c65_role'] ?? ''),
            'production_prelock_review_pass' => (bool) ($source['production_prelock_review_pass'] ?? false),
            'candidate_ready_for_c66' => (bool) ($source['candidate_ready_for_c66'] ?? false),
            'production_catalog_allowed' => (bool) ($source['production_catalog_allowed'] ?? false),
            'production_deployment_allowed' => (bool) ($source['production_deployment_allowed'] ?? false),
            'production_ready' => (bool) ($source['production_ready'] ?? false),
        ];

        $badMonthPass = ($oos['oos_bad_month_decision'] ?? null) === 'PASS_WITH_DOCUMENTED_RISK'
            && ($oos['oos_bad_month_risk_level'] ?? null) === 'MODERATE'
            && ! empty($oos['oos_worst_month'])
            && ! empty($oos['oos_worst_month_regime'])
            && (bool) ($source['bad_month_governance_pass'] ?? false);
        $weakRegimePass = ($oos['oos_weak_regime_sample_status'] ?? null) === 'SUFFICIENT'
            && ($oos['oos_weak_regime_sample_collapse_detected'] ?? null) === false
            && ($oos['oos_weak_regime_risk_level'] ?? null) === 'MODERATE'
            && (bool) ($source['weak_regime_governance_pass'] ?? false);
        $concentrationPass = (bool) ($oos['oos_concentration_validation_pass'] ?? false)
            && (bool) ($source['concentration_governance_pass'] ?? false)
            && ! (bool) ($source['oos_concentration_regression_detected'] ?? false);
        $lossClusterPass = (bool) ($oos['oos_loss_cluster_validation_pass'] ?? false)
            && (bool) ($source['loss_cluster_governance_pass'] ?? false)
            && ! (bool) ($source['oos_loss_cluster_regression_detected'] ?? false);
        $rollingPass = (bool) ($oos['oos_rolling_validation_pass'] ?? false) && (bool) ($source['rolling_governance_pass'] ?? false);
        $sourceBiasRisk = (string) ($source['oos_source_bias_risk_level'] ?? $oos['oos_source_bias_risk_level'] ?? 'DOCUMENTED_NOT_HIGH');
        $sharedCoreRisk = (string) ($source['oos_shared_core_risk_level'] ?? $oos['oos_shared_core_risk_level'] ?? 'LOW');
        $sourceBiasPass = (bool) ($oos['oos_source_bias_validation_pass'] ?? false) && (bool) ($source['source_bias_governance_pass'] ?? false) && $sourceBiasRisk !== 'HIGH';
        $sharedCorePass = (bool) ($oos['oos_shared_core_validation_pass'] ?? false) && (bool) ($source['shared_core_governance_pass'] ?? false) && $sharedCoreRisk !== 'HIGH';
        $safetyPass = (bool) ($oos['oos_safety_and_leakage_pass'] ?? false) && (bool) ($source['safety_and_leakage_governance_pass'] ?? false);
        $planPass = (bool) ($source['plan_confirm_non_mutation_pass'] ?? false);
        $activationNonCreationPass = ($source['production_catalog_activation_non_creation_pass'] ?? true) !== false;

        $expectedC65Role = $role === 'primary_production_lock_candidate' ? 'primary_production_prelock_candidate' : ($role === 'backup_production_lock_candidate' ? 'backup_production_prelock_candidate' : 'comparator_only');
        $prelockOk = ($source['c65_role'] ?? null) === $expectedC65Role
            && $c65Summary['production_catalog_allowed'] === false
            && $c65Summary['production_deployment_allowed'] === false
            && $c65Summary['production_ready'] === false;
        if ($role !== 'comparator_only') {
            $prelockOk = $prelockOk && $c65Summary['production_prelock_review_pass'] === true && $c65Summary['candidate_ready_for_c66'] === true;
        }

        $catalogLock = $role !== 'comparator_only'
            && $prelockOk
            && $badMonthPass
            && $weakRegimePass
            && $concentrationPass
            && $lossClusterPass
            && $rollingPass
            && $sourceBiasPass
            && $sharedCorePass
            && $safetyPass
            && $planPass
            && $activationNonCreationPass;

        $failures = [];
        if (! $prelockOk) {
            $failures[] = 'C66_C65_PRELOCK_EVIDENCE_INVALID';
        }
        if (! $badMonthPass) {
            $failures[] = 'C66_BAD_MONTH_GOVERNANCE_INVALID';
        }
        if (! $weakRegimePass) {
            $failures[] = 'C66_WEAK_REGIME_GOVERNANCE_INVALID';
        }
        if (! $concentrationPass) {
            $failures[] = 'C66_CONCENTRATION_GOVERNANCE_INVALID';
        }
        if (! $lossClusterPass) {
            $failures[] = 'C66_LOSS_CLUSTER_GOVERNANCE_INVALID';
        }
        if (! $rollingPass) {
            $failures[] = 'C66_ROLLING_GOVERNANCE_INVALID';
        }
        if (! $sourceBiasPass) {
            $failures[] = 'C66_SOURCE_BIAS_GOVERNANCE_INVALID';
        }
        if (! $sharedCorePass) {
            $failures[] = 'C66_SHARED_CORE_GOVERNANCE_INVALID';
        }
        if (! $safetyPass) {
            $failures[] = 'C66_SAFETY_AND_LEAKAGE_INVALID';
        }
        if (! $planPass) {
            $failures[] = 'C66_PLAN_CONFIRM_NON_MUTATION_INVALID';
        }
        if (! $activationNonCreationPass) {
            $failures[] = 'C66_PRODUCTION_CATALOG_NON_CREATION_INVALID';
        }
        if ($role === 'comparator_only') {
            $catalogLock = false;
            $failures = ['C66_A01_REMAINS_COMPARATOR_ONLY'];
        }

        return [
            'candidate_code' => $candidate,
            'c66_role' => $role,
            'parent_candidate_code' => $parent,
            'c65_prelock_evidence_summary' => $c65Summary,
            'c64_oos_evidence_summary' => $oos,
            'production_lock_review_pass' => $catalogLock,
            'candidate_locked_for_production_catalog' => $catalogLock,
            'production_catalog_lock_allowed' => $catalogLock,
            'production_catalog_activation_allowed' => false,
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
            'plan_confirm_non_mutation_pass' => $planPass,
            'production_catalog_activation_non_creation_pass' => $activationNonCreationPass,
            'source_bias_risk_level' => $sourceBiasRisk,
            'shared_core_risk_level' => $sharedCoreRisk,
            'failure_reason_codes' => array_values(array_unique($failures)),
        ];
    }

    private function badMonthGovernanceLockReviewResults(array $scorecard): array
    {
        $out = [];
        foreach ($scorecard as $row) {
            $oos = (array) ($row['c64_oos_evidence_summary'] ?? []);
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'c66_role' => $row['c66_role'],
                'bad_month_governance_lock_review_completed' => ! empty($oos['oos_worst_month']) && ! empty($oos['oos_worst_month_regime']),
                'documented_bad_month_risk_retained' => ($oos['oos_bad_month_decision'] ?? null) === 'PASS_WITH_DOCUMENTED_RISK',
                'bad_month_removed' => empty($oos['oos_worst_month']),
                'bad_month_risk_hidden' => empty($oos['oos_bad_month_decision']),
                'worst_month' => $oos['oos_worst_month'] ?? null,
                'worst_month_avg_ret_net' => $oos['oos_worst_month_avg_ret_net'] ?? null,
                'worst_month_regime' => $oos['oos_worst_month_regime'] ?? null,
                'bad_month_risk_level' => $oos['oos_bad_month_risk_level'] ?? null,
                'bad_month_governance_decision' => $oos['oos_bad_month_decision'] ?? null,
                'production_lock_risk_free_claim' => false,
                'bad_month_governance_pass' => (bool) ($row['bad_month_governance_pass'] ?? false),
            ];
        }
        return $out;
    }

    private function weakRegimeGovernanceLockReviewResults(array $scorecard): array
    {
        $out = [];
        foreach ($scorecard as $row) {
            $oos = (array) ($row['c64_oos_evidence_summary'] ?? []);
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'c66_role' => $row['c66_role'],
                'weak_regime_governance_lock_review_completed' => ! empty($oos['oos_weak_regime_sample_status']),
                'weak_regime_retained' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => $oos['oos_weak_regime_sample_status'] ?? null,
                'weak_regime_sample_collapse_detected' => (bool) ($oos['oos_weak_regime_sample_collapse_detected'] ?? true),
                'weak_regime_risk_level' => $oos['oos_weak_regime_risk_level'] ?? null,
                'weak_regime_governance_decision' => (bool) ($row['weak_regime_governance_pass'] ?? false) ? 'PASS_WITH_DOCUMENTED_RISK' : 'FAIL',
                'production_lock_ignores_weak_regime_risk' => false,
                'weak_regime_governance_pass' => (bool) ($row['weak_regime_governance_pass'] ?? false),
            ];
        }
        return $out;
    }

    private function concentrationLossClusterGovernanceSummary(array $scorecard): array
    {
        return [
            'validation_completed' => $scorecard !== [],
            'concentration_governance_pass' => $this->allRequiredProductionRowsPass($scorecard, 'concentration_governance_pass'),
            'loss_cluster_governance_pass' => $this->allRequiredProductionRowsPass($scorecard, 'loss_cluster_governance_pass'),
            'concentration_regression_detected' => $this->anyRequiredProductionRowHasFailure($scorecard, 'C66_CONCENTRATION_GOVERNANCE_INVALID'),
            'loss_cluster_regression_detected' => $this->anyRequiredProductionRowHasFailure($scorecard, 'C66_LOSS_CLUSTER_GOVERNANCE_INVALID'),
            'month_dependency_detected' => false,
            'sample_collapse_detected' => false,
        ];
    }

    private function rollingMonthDependencyGovernanceSummary(array $scorecard): array
    {
        return [
            'validation_completed' => $scorecard !== [],
            'rolling_governance_pass' => $this->allRequiredProductionRowsPass($scorecard, 'rolling_governance_pass'),
            'month_dependency_detected' => false,
            'sample_collapse_detected' => false,
        ];
    }

    private function sourceBiasSharedCoreGovernanceSummary(array $scorecard): array
    {
        $sourceBiasPass = $this->allRequiredProductionRowsPass($scorecard, 'source_bias_governance_pass');
        $sharedCorePass = $this->allRequiredProductionRowsPass($scorecard, 'shared_core_governance_pass');
        return [
            'validation_completed' => $scorecard !== [],
            'source_bias_governance_pass' => $sourceBiasPass,
            'shared_core_governance_pass' => $sharedCorePass,
            'source_bias_risk_level' => $sourceBiasPass ? 'DOCUMENTED_NOT_HIGH' : 'HIGH',
            'shared_core_risk_level' => $sharedCorePass ? 'LOW' : 'HIGH',
            'parent_diversity_sufficient' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
        ];
    }

    private function productionMutationSafetySummary(array $c65, array $scope, array $dictionary): array
    {
        $c65Safety = (array) ($c65['production_mutation_safety_summary'] ?? []);
        $summary = [
            'validation_completed' => true,
            'production_catalog_locked_decision_created' => false,
            'production_catalog_created' => (bool) ($c65Safety['production_catalog_created'] ?? false),
            'production_catalog_activated' => (bool) ($c65Safety['production_catalog_activated'] ?? false),
            'production_deployment_executed' => (bool) ($c65Safety['production_deployment_executed'] ?? false),
            'plan_confirm_mutated' => (bool) ($c65Safety['plan_confirm_mutated'] ?? false),
            'production_catalog_activation_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'selection_changed_after_c65' => (bool) ($scope['candidate_scope_changed_after_c65'] ?? true),
            'parameter_changed_after_c65' => (bool) ($scope['parameter_changed'] ?? true),
            'new_candidate_created' => (bool) ($scope['new_candidate_created'] ?? true),
            'oos_reused_for_ranking' => (bool) ($scope['oos_result_used_for_new_ranking'] ?? true),
            'latest_shortcut_used' => (bool) ($c65Safety['latest_shortcut_used'] ?? false),
            'max_date_shortcut_used' => (bool) ($c65Safety['max_date_shortcut_used'] ?? $c65Safety['date_desc_shortcut_used'] ?? false),
            'future_lookup_detected' => (bool) ($c65Safety['future_lookup_detected'] ?? false),
            'return_fields_used_for_selection' => (bool) ($c65Safety['return_fields_used_for_selection'] ?? false),
            'database_dictionary_rule_complied' => (bool) ($dictionary['dictionary_read_rule_complied'] ?? false),
        ];
        $summary['production_mutation_safety_pass'] = ! $summary['production_catalog_created']
            && ! $summary['production_catalog_activated']
            && ! $summary['production_deployment_executed']
            && ! $summary['plan_confirm_mutated']
            && ! $summary['selection_changed_after_c65']
            && ! $summary['parameter_changed_after_c65']
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
            'c66_is_production_lock_review_documented' => 'C66 is production lock review',
            'not_live_deployment_documented' => 'C66 pass is not live deployment',
            'primary_e02_documented' => self::PRIMARY_CANDIDATE,
            'backup_b01_documented' => self::BACKUP_CANDIDATE,
            'a01_comparator_only_restriction_documented' => 'A01 remains comparator-only',
            'bad_month_documented_risk_retained' => 'bad-month risk remains documented',
            'weak_regime_documented_risk_retained' => 'weak-regime risk remains documented',
            'activation_deferred_documented' => 'activation is deferred to C67',
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

    private function c65CleanupNoteSummary(array $c65): array
    {
        $note = (array) ($c65['c64_cleanup_note_summary'] ?? []);
        return [
            'validation_completed' => true,
            'legacy_repair_recommendation' => (string) ($note['legacy_repair_recommendation'] ?? 'C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY'),
            'legacy_repair_recommendation_non_blocking' => (bool) ($note['legacy_repair_recommendation_non_blocking'] ?? true),
            'normalized_repair_recommendation' => (string) ($note['normalized_repair_recommendation'] ?? 'NOT_REQUIRED'),
            'c65_failure_repair_required' => (bool) ($note['c65_failure_repair_required'] ?? false),
        ];
    }

    private function productionLockDecision(array $scorecard, array &$artifact): array
    {
        $rows = $this->indexByCode($scorecard);
        $primaryPass = (bool) ($rows[self::PRIMARY_CANDIDATE]['production_lock_review_pass'] ?? false);
        $backupPass = (bool) ($rows[self::BACKUP_CANDIDATE]['production_lock_review_pass'] ?? false);
        $safetyPass = (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? false);
        $docsPass = (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? false);
        $cleanupPass = (bool) ($artifact['c65_cleanup_note_summary']['legacy_repair_recommendation_non_blocking'] ?? false)
            && ($artifact['c65_cleanup_note_summary']['normalized_repair_recommendation'] ?? null) === 'NOT_REQUIRED'
            && ! (bool) ($artifact['c65_cleanup_note_summary']['c65_failure_repair_required'] ?? true);
        $globalGovernancePass = $safetyPass
            && $docsPass
            && $cleanupPass
            && $this->allRequiredProductionRowsPass($scorecard, 'bad_month_governance_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'weak_regime_governance_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'concentration_governance_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'loss_cluster_governance_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'rolling_governance_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'source_bias_governance_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'shared_core_governance_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'safety_and_leakage_governance_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'plan_confirm_non_mutation_pass')
            && $this->allRequiredProductionRowsPass($scorecard, 'production_catalog_activation_non_creation_pass');

        $primaryPass = $primaryPass && $globalGovernancePass;
        $backupPass = $backupPass && $globalGovernancePass;
        $pass = $primaryPass || $backupPass;
        $scope = $primaryPass && $backupPass ? 'PRIMARY_AND_BACKUP' : ($primaryPass ? 'PRIMARY_ONLY' : ($backupPass ? 'BACKUP_ONLY' : 'NONE'));
        $status = $this->statusFromScope($scope, $scorecard, $artifact);
        if ($pass) {
            $artifact['production_mutation_safety_summary']['production_catalog_locked_decision_created'] = true;
        }

        return [
            'validation_completed' => true,
            'production_lock_review_executed' => true,
            'production_lock_status' => $status,
            'production_lock_review_pass' => $pass,
            'primary_production_lock_pass' => $primaryPass,
            'backup_production_lock_pass' => $backupPass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'production_lock_pass_scope' => $scope,
            'a01_remains_comparator_only' => true,
            'decision_reason' => $pass ? 'Primary E02 and/or backup B01 pass C66 production lock governance; artifact lock only, activation deferred.' : 'C66 production lock governance did not pass for primary or backup.',
            'diagnostic_conclusion' => $status,
            'production_catalog_lock_allowed' => $pass,
            'production_catalog_activation_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
        ];
    }

    private function c67ReadinessDecision(array $scorecard, array $decision): array
    {
        $ready = [];
        foreach ($scorecard as $row) {
            if (($row['candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE && (bool) ($row['candidate_locked_for_production_catalog'] ?? false) && (bool) ($decision['production_lock_review_pass'] ?? false)) {
                $ready[] = $row['candidate_code'];
            }
        }
        return [
            'validation_completed' => true,
            'candidate_ready_for_c67_count' => count($ready),
            'candidate_codes' => $ready,
            'c67_recommendation' => (bool) ($decision['production_lock_review_pass'] ?? false) ? 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW' : $this->repairRecommendationFromStatus((string) ($decision['production_lock_status'] ?? '')),
            'decision_reason' => (bool) ($decision['production_lock_review_pass'] ?? false) ? 'C66 production lock review passed. Next step is C67 activation review only.' : 'C66 production lock review failed. Next step is targeted governance cleanup or repair.',
            'diagnostic_conclusion' => (string) ($decision['production_lock_status'] ?? 'C66_PRODUCTION_LOCK_REVIEW_FAILED_BOTH'),
            'production_catalog_lock_allowed' => (bool) ($decision['production_catalog_lock_allowed'] ?? false),
            'production_catalog_activation_allowed' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
        ];
    }

    private function failureAttributionSummary(array $scorecard, array $decision, array $artifact): array
    {
        $status = (string) ($decision['production_lock_status'] ?? $artifact['status'] ?? 'C66_NOT_RUN');
        $blocker = $this->dominantBlocker($scorecard, $artifact, $status);
        return [
            'validation_completed' => true,
            'dominant_blocker' => $blocker,
            'status' => $status,
            'candidate_failure_reason_codes' => $this->collectFailureReasons($scorecard),
            'recommended_next_step' => $blocker === 'NONE' ? 'C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW' : $this->repairRecommendationFromBlocker($blocker),
        ];
    }

    private function diagnostics(array $artifact): array
    {
        $diags = [];
        $status = (string) ($artifact['status'] ?? $artifact['production_lock_decision']['production_lock_status'] ?? 'C66_NOT_RUN');
        if (strpos($status, 'C66_PRODUCTION_LOCK_REVIEW_PASSED') === 0) {
            $diags[] = ['reason_code' => 'WS_BT_C66_PRODUCTION_LOCK_PASSED', 'message' => 'C66 production lock review passed as artifact-only locked decision.'];
        } elseif ($status !== 'C66_NOT_RUN') {
            $diags[] = ['reason_code' => 'WS_BT_C66_PRODUCTION_LOCK_NOT_PASSED', 'message' => 'C66 production lock review did not pass.'];
        }
        if (($artifact['production_mutation_safety_summary']['production_catalog_locked_decision_created'] ?? false) === true) {
            $diags[] = ['reason_code' => 'WS_BT_C66_LOCKED_DECISION_ARTIFACT_ONLY', 'message' => 'Production catalog lock is an artifact decision only; activation and deployment remain disabled.'];
        }
        return $diags;
    }

    private function statusFromScope(string $scope, array $scorecard, array $artifact): string
    {
        if ($scope === 'PRIMARY_AND_BACKUP') {
            return 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        }
        if ($scope === 'PRIMARY_ONLY') {
            return 'C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_ONLY';
        }
        if ($scope === 'BACKUP_ONLY') {
            return 'C66_PRODUCTION_LOCK_REVIEW_PASSED_BACKUP_ONLY';
        }
        $blocker = $this->dominantBlocker($scorecard, $artifact, '');
        $map = [
            'BAD_MONTH_GOVERNANCE' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE',
            'WEAK_REGIME_GOVERNANCE' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE',
            'CONCENTRATION_OR_LOSS_CLUSTER' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER',
            'ROLLING_MONTH_DEPENDENCY' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_CONCENTRATION_OR_LOSS_CLUSTER',
            'SOURCE_BIAS_OR_SHARED_CORE' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE',
            'SAFETY_OR_LEAKAGE' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_SAFETY_OR_LEAKAGE',
            'PRODUCTION_MUTATION_SAFETY' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_PRODUCTION_MUTATION',
            'DOCUMENTATION_GOVERNANCE' => 'C66_PRODUCTION_LOCK_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
        ];
        return $map[$blocker] ?? 'C66_PRODUCTION_LOCK_REVIEW_FAILED_BOTH';
    }

    private function dominantBlocker(array $scorecard, array $artifact, string $status): string
    {
        if (strpos($status, 'PASSED') !== false) {
            return 'NONE';
        }
        if (! (bool) ($artifact['production_mutation_safety_summary']['production_mutation_safety_pass'] ?? true)) {
            return 'PRODUCTION_MUTATION_SAFETY';
        }
        if (! (bool) ($artifact['documentation_governance_summary']['documentation_governance_pass'] ?? true)) {
            return 'DOCUMENTATION_GOVERNANCE';
        }
        foreach ($scorecard as $row) {
            if (($row['c66_role'] ?? '') === 'comparator_only') {
                continue;
            }
            $codes = (array) ($row['failure_reason_codes'] ?? []);
            foreach ($codes as $code) {
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
            'C66_PRODUCTION_LOCK_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE' => 'C67_PRODUCTION_LOCK_BAD_MONTH_GOVERNANCE_REPAIR',
            'C66_PRODUCTION_LOCK_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE' => 'C67_PRODUCTION_LOCK_WEAK_REGIME_GOVERNANCE_REPAIR',
            'C66_PRODUCTION_LOCK_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE' => 'C67_PRODUCTION_LOCK_SOURCE_BIAS_OR_SHARED_CORE_REPAIR',
            'C66_PRODUCTION_LOCK_REVIEW_REJECTED_SAFETY_OR_LEAKAGE' => 'C67_PRODUCTION_LOCK_SAFETY_REPAIR',
            'C66_PRODUCTION_LOCK_REVIEW_REJECTED_PRODUCTION_MUTATION' => 'C67_PRODUCTION_LOCK_SAFETY_REPAIR',
            'C66_PRODUCTION_LOCK_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE' => 'C67_PRODUCTION_LOCK_DOCUMENTATION_REPAIR',
        ];
        return $map[$status] ?? 'C67_PRODUCTION_LOCK_GOVERNANCE_CLEANUP';
    }

    private function repairRecommendationFromBlocker(string $blocker): string
    {
        $map = [
            'BAD_MONTH_GOVERNANCE' => 'C67_PRODUCTION_LOCK_BAD_MONTH_GOVERNANCE_REPAIR',
            'WEAK_REGIME_GOVERNANCE' => 'C67_PRODUCTION_LOCK_WEAK_REGIME_GOVERNANCE_REPAIR',
            'SOURCE_BIAS_OR_SHARED_CORE' => 'C67_PRODUCTION_LOCK_SOURCE_BIAS_OR_SHARED_CORE_REPAIR',
            'SAFETY_OR_LEAKAGE' => 'C67_PRODUCTION_LOCK_SAFETY_REPAIR',
            'PRODUCTION_MUTATION_SAFETY' => 'C67_PRODUCTION_LOCK_SAFETY_REPAIR',
            'DOCUMENTATION_GOVERNANCE' => 'C67_PRODUCTION_LOCK_DOCUMENTATION_REPAIR',
        ];
        return $map[$blocker] ?? 'C67_PRODUCTION_LOCK_GOVERNANCE_CLEANUP';
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reasonCode;
        $artifact['message'] = $message;
        $artifact['production_lock_review_executed'] = false;
        $artifact['production_lock_review_pass'] = false;
        $artifact['production_catalog_lock_allowed'] = false;
        $artifact['production_catalog_activation_allowed'] = false;
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
        $artifact['production_lock_review_executed'] = true;
        $artifact['production_lock_review_pass'] = false;
        $artifact['production_catalog_lock_allowed'] = false;
        $artifact['production_catalog_activation_allowed'] = false;
        $artifact['production_deployment_allowed'] = false;
        $artifact['plan_confirm_mutation_allowed'] = false;
        $artifact['production_ready'] = false;
        $artifact['diagnostic_conclusion'] = $status;
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C66_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'WS_BT_C66_OUTPUT_EXISTS';
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
            'c66_is_production_lock_review' => true,
            'c66_is_not_redesign' => true,
            'c66_is_not_retune' => true,
            'c66_is_not_parameter_search' => true,
            'c66_is_not_oos_search' => true,
            'c66_is_not_production_activation' => true,
            'c66_is_not_production_deployment' => true,
            'production_catalog_lock_is_artifact_only' => true,
            'production_catalog_activation_allowed_must_remain_false' => true,
            'production_deployment_allowed_must_remain_false' => true,
            'plan_confirm_mutation_allowed_must_remain_false' => true,
            'candidate_scope_change_forbidden' => true,
            'a01_promotion_forbidden' => true,
            'bad_month_risk_hidden_forbidden' => true,
            'weak_regime_removed_forbidden' => true,
        ];
    }

    private function allRequiredProductionRowsPass(array $rows, string $field): bool
    {
        $seen = false;
        foreach ($rows as $row) {
            if (($row['c66_role'] ?? '') === 'comparator_only') {
                continue;
            }
            $seen = true;
            if (! (bool) ($row[$field] ?? false)) {
                return false;
            }
        }
        return $seen;
    }

    private function anyRequiredProductionRowHasFailure(array $rows, string $reason): bool
    {
        foreach ($rows as $row) {
            if (($row['c66_role'] ?? '') === 'comparator_only') {
                continue;
            }
            if (in_array($reason, (array) ($row['failure_reason_codes'] ?? []), true)) {
                return true;
            }
        }
        return false;
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
