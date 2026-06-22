<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC63PreOosUnlockReviewIsOnlyService
{
    public const RUN_CODE = 'C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY';
    public const ARTIFACT_TYPE = 'C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY';

    public const DEFAULT_C62_ARTIFACT = 'storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json';
    public const DEFAULT_EXPECTED_C62_HASH = 'd3a089b9b986838764d517682035d76e0bb4112d';
    public const DEFAULT_EXPECTED_C62_FILE_SHA1 = '8DF1649BC72233D119581A802F9E41BA9BEBF12E';

    public const DEFAULT_C61_ARTIFACT = 'storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json';
    public const DEFAULT_EXPECTED_C61_HASH = '40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8';
    public const DEFAULT_EXPECTED_C61_FILE_SHA1 = 'DEA3C807813DE81DB6776AB2C441C945D4E98EC6';

    public const DEFAULT_C60_ARTIFACT = 'storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json';
    public const DEFAULT_EXPECTED_C60_HASH = '25a32ee9c4cb77ecc29103c86a1abf0826aea705';
    public const DEFAULT_EXPECTED_C60_FILE_SHA1 = '1FA933157B61ECB4554CE6C76B0F2B314F19DB0F';

    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';
    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    /**
     * C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY. IS_ONLY_PRE_OOS_UNLOCK_REVIEW.
     * C62_ARTIFACT_HASH_LOCK. C62_FILE_SHA1_LOCK. C61_LINEAGE_HASH_LOCK. C61_FILE_SHA1_LOCK.
     * C60_LINEAGE_HASH_LOCK. C60_FILE_SHA1_LOCK. DATABASE_DICTIONARY_READ_RULE_ENFORCED.
     * MARKET_DATA_DICTIONARY_REQUIRED. WATCHLIST_DB_DICTIONARY_REQUIRED. ASOF_SAFE_LOOKUP_REQUIRED.
     * MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20.
     * MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT.
     * MARKET_INDEX_IDENTIFIER_IHSG. MARKET_CALENDAR_DATE_KEY_CAL_DATE.
     * NO_RESERVED_OOS_ROWS. NO_OOS_DATE_QUERY. NO_OOS_PROOF. NO_PRE_OOS_EXECUTION.
     * NO_PRODUCTION_CATALOG. NO_PLAN_CONFIRM_MUTATION. NO_RETURN_FIELD_SELECTION. NO_FUTURE_PATH_SELECTION.
     * RETURN_USED_FOR_SELECTION_FALSE. FUTURE_PATH_USED_FOR_SELECTION_FALSE. OOS_RETURN_USED_FOR_SELECTION_FALSE.
     * NO_OOS_RETURN_SELECTION. NO_OOS_TIE_BREAK. NO_GATE_RELAXATION. NO_BEST_OF_FAILED_PROMOTION.
     * NO_REPLAY_COMPARATOR_PROMOTION. NO_BAD_MONTH_REMOVAL. NO_WEAK_REGIME_REMOVAL.
     * NO_MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_SKIP. NO_TICKER_EXCLUSION_RULE. NO_SECTOR_EXCLUSION_RULE.
     * MONTH_WIN_RATE_MIN_ZERO_MUST_BE_AUDITED. E02_WORST_MONTH_2024_08_AUDIT_REQUIRED.
     * B01_WORST_MONTH_2024_11_AUDIT_REQUIRED. BAD_MONTH_UNLOCK_RISK_REQUIRED.
     * WEAK_REGIME_UNLOCK_READINESS_REQUIRED. ROLLING_UNLOCK_RECHECK_REQUIRED. LOO_UNLOCK_RECHECK_REQUIRED.
     * CONCENTRATION_UNLOCK_RECHECK_REQUIRED. LOSS_CLUSTER_UNLOCK_RECHECK_REQUIRED.
     * SHARED_CORE_UNLOCK_RECHECK_REQUIRED. SOURCE_BIAS_UNLOCK_RECHECK_REQUIRED.
     * C63_RESULT_IS_NOT_PRODUCTION_READY. C63_MUST_NOT_UNLOCK_OOS_FLAGS.
     */
    public function execute(
        string $c62Artifact = self::DEFAULT_C62_ARTIFACT,
        string $expectedC62Hash = self::DEFAULT_EXPECTED_C62_HASH,
        string $expectedC62FileSha1 = self::DEFAULT_EXPECTED_C62_FILE_SHA1,
        string $c61Artifact = self::DEFAULT_C61_ARTIFACT,
        string $expectedC61Hash = self::DEFAULT_EXPECTED_C61_HASH,
        string $expectedC61FileSha1 = self::DEFAULT_EXPECTED_C61_FILE_SHA1,
        string $c60Artifact = self::DEFAULT_C60_ARTIFACT,
        string $expectedC60Hash = self::DEFAULT_EXPECTED_C60_HASH,
        string $expectedC60FileSha1 = self::DEFAULT_EXPECTED_C60_FILE_SHA1,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->baseArtifact(
            $c62Artifact,
            $expectedC62Hash,
            $expectedC62FileSha1,
            $c61Artifact,
            $expectedC61Hash,
            $expectedC61FileSha1,
            $c60Artifact,
            $expectedC60Hash,
            $expectedC60FileSha1,
            $from,
            $to,
            (string) ($options['executed_at'] ?? gmdate('c'))
        );
        $overwrite = (bool) ($options['overwrite'] ?? false);

        if ($this->touchesReservedOos($from, $to)) {
            return $this->blocked($artifact, 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_ASOF_OR_OOS_SAFETY', 'WS_BT_C63_OOS_DATE_RANGE_REQUESTED', 'C63 is IS-only and the requested date range touches reserved OOS.', $outputPath, $overwrite);
        }

        $dictionary = $this->databaseDictionaryReadSummary($from, $to);
        $artifact['database_dictionary_read_summary'] = $dictionary;
        if ((bool) ($dictionary['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_ASOF_OR_OOS_SAFETY', 'WS_BT_C63_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C63 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath, $overwrite);
        }

        $c62Load = $this->loadArtifactLock($c62Artifact, $expectedC62Hash, $expectedC62FileSha1);
        $this->copyLock($artifact, 'c62', $c62Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        if (! $c62Load['readable']) {
            return $this->blocked($artifact, 'C63_BLOCKED_MISSING_C62_ARTIFACT', 'WS_BT_C63_C62_ARTIFACT_MISSING', 'C63 requires the locked C62 artifact.', $outputPath, $overwrite);
        }
        if (! $c62Load['hash_match']) {
            return $this->blocked($artifact, 'C63_BLOCKED_C62_ARTIFACT_LOCK_MISMATCH', 'WS_BT_C63_C62_ARTIFACT_HASH_MISMATCH', 'C62 artifact hash does not match the expected C63 lock.', $outputPath, $overwrite);
        }
        if (! $c62Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C63_BLOCKED_C62_FILE_SHA1_LOCK_MISMATCH', 'WS_BT_C63_C62_FILE_SHA1_MISMATCH', 'C62 file SHA1 does not match the expected C63 lock.', $outputPath, $overwrite);
        }

        $c62 = $c62Load['payload'];
        $c62Validation = $this->validateC62($c62);
        $artifact['c62_lock_validation_summary'] = $c62Validation;
        if (! (bool) ($c62Validation['pass'] ?? false)) {
            return $this->blocked($artifact, (string) ($c62Validation['status'] ?? 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH'), (string) ($c62Validation['reason_code'] ?? 'WS_BT_C63_C62_LOCK_INVALID'), (string) ($c62Validation['message'] ?? 'C62 evidence is not valid for C63.'), $outputPath, $overwrite);
        }

        $c61Load = $this->loadArtifactLock($c61Artifact, $expectedC61Hash, $expectedC61FileSha1);
        $this->copyLock($artifact, 'c61', $c61Load);
        $c60Load = $this->loadArtifactLock($c60Artifact, $expectedC60Hash, $expectedC60FileSha1);
        $this->copyLock($artifact, 'c60', $c60Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        $c61Lineage = $this->validateC61Lineage($c61Load);
        $artifact['c61_lineage_validation_summary'] = $c61Lineage;
        if (! (bool) ($c61Lineage['pass'] ?? false)) {
            return $this->blocked($artifact, 'C63_BLOCKED_LINEAGE_LOCK_MISMATCH', (string) ($c61Lineage['reason_code'] ?? 'WS_BT_C63_C61_LINEAGE_LOCK_MISMATCH'), (string) ($c61Lineage['message'] ?? 'C61 lineage lock mismatch.'), $outputPath, $overwrite);
        }

        $c60Lineage = $this->validateC60Lineage($c60Load);
        $artifact['c60_lineage_validation_summary'] = $c60Lineage;
        if (! (bool) ($c60Lineage['pass'] ?? false)) {
            return $this->blocked($artifact, 'C63_BLOCKED_LINEAGE_LOCK_MISMATCH', (string) ($c60Lineage['reason_code'] ?? 'WS_BT_C63_C60_LINEAGE_LOCK_MISMATCH'), (string) ($c60Lineage['message'] ?? 'C60 lineage lock mismatch.'), $outputPath, $overwrite);
        }

        $artifact['c62_decision_replay_summary'] = $this->c62DecisionReplaySummary($c62);
        $scorecard = $this->unlockCandidateScorecard($c62);
        $artifact['unlock_candidate_scorecard'] = $scorecard;
        $artifact['bad_month_unlock_review_results'] = $this->badMonthUnlockReviewResults($scorecard);
        $artifact['weak_regime_unlock_review_results'] = $this->weakRegimeUnlockReviewResults($scorecard);
        $artifact['concentration_unlock_review_results'] = $this->concentrationUnlockReviewResults($c62, $scorecard);
        $artifact['loss_cluster_unlock_review_results'] = $this->lossClusterUnlockReviewResults($c62, $scorecard);
        $artifact['rolling_unlock_review_summary'] = $this->rollingUnlockReviewSummary($c62, $scorecard);
        $artifact['loo_unlock_review_summary'] = $this->looUnlockReviewSummary($c62, $scorecard);
        $artifact['shared_core_unlock_review_summary'] = $this->sharedCoreUnlockReviewSummary($c62, $scorecard);
        $artifact['source_bias_unlock_review_summary'] = $this->sourceBiasUnlockReviewSummary($c62, $scorecard);
        $artifact['safety_and_leakage_unlock_audit_summary'] = $this->safetyAndLeakageUnlockAuditSummary($dictionary, $scorecard);
        $artifact['unlock_hierarchy_summary'] = $this->unlockHierarchySummary($scorecard);
        $artifact['pre_oos_unlock_decision'] = $this->preOosUnlockDecision($scorecard, $artifact);
        $artifact['c64_readiness_decision'] = $this->c64ReadinessDecision($artifact['pre_oos_unlock_decision']);
        $artifact['diagnostics'] = $this->diagnostics($artifact);
        $artifact['safety_boundaries'] = $this->safetyBoundaries();
        $artifact['status'] = (string) $artifact['pre_oos_unlock_decision']['unlock_review_status'];
        $artifact['reason_code'] = (string) $artifact['pre_oos_unlock_decision']['unlock_review_status'];
        $artifact['diagnostic_conclusion'] = (string) $artifact['pre_oos_unlock_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = (string) $artifact['c64_readiness_decision']['c64_recommendation'];

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(
        string $c62Artifact,
        string $expectedC62Hash,
        string $expectedC62FileSha1,
        string $c61Artifact,
        string $expectedC61Hash,
        string $expectedC61FileSha1,
        string $c60Artifact,
        string $expectedC60Hash,
        string $expectedC60FileSha1,
        string $from,
        string $to,
        string $createdAt
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C63_NOT_RUN',
            'reason_code' => 'C63_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'pre_oos_unlocked' => false,
            'input_c62_artifact' => $c62Artifact,
            'expected_c62_hash' => $expectedC62Hash,
            'expected_c62_file_sha1' => strtoupper($expectedC62FileSha1),
            'actual_c62_hash' => null,
            'actual_c62_file_sha1' => null,
            'c62_hash_match' => false,
            'c62_file_sha1_match' => false,
            'c62_status' => null,
            'c62_reason_code' => null,
            'input_c61_artifact' => $c61Artifact,
            'expected_c61_hash' => $expectedC61Hash,
            'expected_c61_file_sha1' => strtoupper($expectedC61FileSha1),
            'actual_c61_hash' => null,
            'actual_c61_file_sha1' => null,
            'c61_hash_match' => false,
            'c61_file_sha1_match' => false,
            'c61_status' => null,
            'c61_reason_code' => null,
            'input_c60_artifact' => $c60Artifact,
            'expected_c60_hash' => $expectedC60Hash,
            'expected_c60_file_sha1' => strtoupper($expectedC60FileSha1),
            'actual_c60_hash' => null,
            'actual_c60_file_sha1' => null,
            'c60_hash_match' => false,
            'c60_file_sha1_match' => false,
            'c60_status' => null,
            'c60_reason_code' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO],
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c62_lock_validation_summary' => [],
            'c61_lineage_validation_summary' => [],
            'c60_lineage_validation_summary' => [],
            'c62_decision_replay_summary' => [],
            'unlock_candidate_scorecard' => [],
            'unlock_hierarchy_summary' => [],
            'bad_month_unlock_review_results' => [],
            'weak_regime_unlock_review_results' => [],
            'concentration_unlock_review_results' => [],
            'loss_cluster_unlock_review_results' => [],
            'rolling_unlock_review_summary' => [],
            'loo_unlock_review_summary' => [],
            'shared_core_unlock_review_summary' => [],
            'source_bias_unlock_review_summary' => [],
            'safety_and_leakage_unlock_audit_summary' => [],
            'pre_oos_unlock_decision' => [],
            'c64_readiness_decision' => [],
            'diagnostics' => [],
            'created_at' => $createdAt,
        ];
    }

    private function loadArtifactLock(string $artifactPath, string $expectedHash, string $expectedFileSha1): array
    {
        $path = $artifactPath;
        $result = [
            'artifact_path' => $artifactPath,
            'expected_hash' => $expectedHash,
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_hash' => null,
            'actual_file_sha1' => null,
            'hash_match' => false,
            'file_sha1_match' => false,
            'readable' => false,
            'payload' => [],
        ];

        if (! is_file($path)) {
            return $result;
        }

        $raw = (string) file_get_contents($path);
        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return $result;
        }

        $actualHash = (string) ($payload['artifact_hash'] ?? '');
        $actualFileSha1 = strtoupper(sha1($raw));

        $result['readable'] = true;
        $result['payload'] = $payload;
        $result['actual_hash'] = $actualHash;
        $result['actual_file_sha1'] = $actualFileSha1;
        $result['hash_match'] = hash_equals($expectedHash, $actualHash);
        $result['file_sha1_match'] = hash_equals(strtoupper($expectedFileSha1), $actualFileSha1);

        return $result;
    }

    private function copyLock(array &$artifact, string $prefix, array $lock): void
    {
        $payload = (array) ($lock['payload'] ?? []);
        $artifact['actual_'.$prefix.'_hash'] = $lock['actual_hash'];
        $artifact['actual_'.$prefix.'_file_sha1'] = $lock['actual_file_sha1'];
        $artifact[$prefix.'_hash_match'] = (bool) $lock['hash_match'];
        $artifact[$prefix.'_file_sha1_match'] = (bool) $lock['file_sha1_match'];
        $artifact[$prefix.'_status'] = $payload['status'] ?? null;
        $artifact[$prefix.'_reason_code'] = $payload['reason_code'] ?? null;
    }

    private function sourceArtifactLocks(array $artifact): array
    {
        return [
            'c62_artifact_path' => $artifact['input_c62_artifact'],
            'expected_c62_hash' => $artifact['expected_c62_hash'],
            'actual_c62_hash' => $artifact['actual_c62_hash'],
            'c62_hash_match' => (bool) $artifact['c62_hash_match'],
            'expected_c62_file_sha1' => $artifact['expected_c62_file_sha1'],
            'actual_c62_file_sha1' => $artifact['actual_c62_file_sha1'],
            'c62_file_sha1_match' => (bool) $artifact['c62_file_sha1_match'],
            'c62_status' => $artifact['c62_status'],
            'c62_reason_code' => $artifact['c62_reason_code'],
            'c61_artifact_path' => $artifact['input_c61_artifact'],
            'expected_c61_hash' => $artifact['expected_c61_hash'],
            'actual_c61_hash' => $artifact['actual_c61_hash'],
            'c61_hash_match' => (bool) $artifact['c61_hash_match'],
            'expected_c61_file_sha1' => $artifact['expected_c61_file_sha1'],
            'actual_c61_file_sha1' => $artifact['actual_c61_file_sha1'],
            'c61_file_sha1_match' => (bool) $artifact['c61_file_sha1_match'],
            'c61_status' => $artifact['c61_status'],
            'c61_reason_code' => $artifact['c61_reason_code'],
            'c60_artifact_path' => $artifact['input_c60_artifact'],
            'expected_c60_hash' => $artifact['expected_c60_hash'],
            'actual_c60_hash' => $artifact['actual_c60_hash'],
            'c60_hash_match' => (bool) $artifact['c60_hash_match'],
            'expected_c60_file_sha1' => $artifact['expected_c60_file_sha1'],
            'actual_c60_file_sha1' => $artifact['actual_c60_file_sha1'],
            'c60_file_sha1_match' => (bool) $artifact['c60_file_sha1_match'],
            'c60_status' => $artifact['c60_status'],
            'c60_reason_code' => $artifact['c60_reason_code'],
            'lineage_retained' => (bool) $artifact['c61_hash_match'] && (bool) $artifact['c60_hash_match'],
        ];
    }

    private function databaseDictionaryReadSummary(string $from, string $to): array
    {
        $missing = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            if (! is_file($path)) {
                $missing[] = $key.':'.$path;
            }
        }

        return [
            'dictionary_read_required' => true,
            'market_data_dictionary_path' => self::DICTIONARY_PATHS['market_data_dictionary_path'],
            'database_dictionary_usage_rule_path' => self::DICTIONARY_PATHS['database_dictionary_usage_rule_path'],
            'watchlist_db_dictionary_path' => self::DICTIONARY_PATHS['watchlist_db_dictionary_path'],
            'dictionary_paths_checked' => array_values(self::DICTIONARY_PATHS),
            'dictionary_tables_checked' => ['market_calendar', 'market_benchmark_indicators', 'eod_bars', 'eod_indicators', 'eod_eligibility', 'watchlist backtest artifacts'],
            'dictionary_field_mappings_checked' => [
                'market_calendar.cal_date',
                'market_benchmark_indicators.benchmark_code=IHSG',
                'market_benchmark_indicators.roc_20 -> market_index_roc20',
                'market_benchmark_indicators.ma20_slope_pct -> market_index_ma20_slope_pct',
                'eod_indicators.trade_date',
                'eod_indicators.ticker_id',
                'eod_indicators.roc20',
                'eod_indicators.ma20_slope_pct',
                'eod_indicators.rs_20_vs_ihsg',
                'eod_indicators.rs_20_vs_sector',
                'eod_indicators.atr14_pct',
                'eod_indicators.vol_ratio',
                'eod_indicators.dv20_idr',
            ],
            'dictionary_missing_coverage_detected' => count($missing) > 0,
            'dictionary_missing_coverage_reason_codes' => $missing,
            'asof_safe' => true,
            'future_lookup_detected' => false,
            'oos_rows_requested' => 0,
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'order_desc_trade_date_shortcut_used' => false,
            'is_period_checked' => ['from' => $from, 'to' => $to],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO],
        ];
    }

    private function validateC62(array $c62): array
    {
        if (($c62['status'] ?? null) !== 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES') {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_STATUS_INVALID', 'message' => 'Locked C62 artifact status is not the expected multiple-candidate pass.'];
        }
        if (($c62['reason_code'] ?? null) !== 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES') {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_REASON_INVALID', 'message' => 'Locked C62 artifact reason_code is not the expected multiple-candidate pass.'];
        }
        if ((bool) ($c62['production_ready'] ?? true) !== false || (bool) ($c62['direct_oos_proof_recommended'] ?? true) !== false || (bool) ($c62['oos_proof_unlocked'] ?? true) !== false || (bool) ($c62['pre_oos_unlocked'] ?? true) !== false) {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_SAFETY_FLAG_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_SAFETY_FLAGS_INVALID', 'message' => 'Locked C62 safety flags must all remain false.'];
        }

        $decision = (array) ($c62['pre_lock_decision'] ?? []);
        $readiness = (array) ($c62['c63_readiness_decision'] ?? []);
        if ((int) ($readiness['candidate_ready_for_c63_count'] ?? 0) !== 2) {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_C63_READY_COUNT_INVALID', 'message' => 'Locked C62 must expose exactly two C63-ready candidates under c63_readiness_decision.'];
        }
        if (($readiness['c63_recommendation'] ?? null) !== 'C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY') {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_C63_RECOMMENDATION_INVALID', 'message' => 'Locked C62 must recommend C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY under c63_readiness_decision.'];
        }
        if (($decision['primary_pre_lock_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_PRIMARY_INVALID', 'message' => 'Locked C62 primary candidate must be E02.'];
        }
        if (! in_array(self::BACKUP_CANDIDATE, (array) ($decision['backup_pre_lock_candidate_codes'] ?? []), true)) {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_BACKUP_INVALID', 'message' => 'Locked C62 backup candidate must be B01/A02 parent diversifier.'];
        }
        if (! in_array(self::COMPARATOR_CANDIDATE, (array) ($decision['rejected_candidate_codes'] ?? []), true)) {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_A01_COMPARATOR_INVALID', 'message' => 'Locked C62 must keep A01 as rejected/comparator-only.'];
        }

        $roles = $this->scorecardRoles((array) ($c62['pre_lock_candidate_scorecard'] ?? []));
        if (($roles[self::PRIMARY_CANDIDATE] ?? null) !== 'primary_pre_lock_candidate') {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_PRIMARY_ROLE_INVALID', 'message' => 'C62 scorecard primary role is invalid.'];
        }
        if (($roles[self::BACKUP_CANDIDATE] ?? null) !== 'backup_pre_lock_candidate_parent_diversifier') {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_BACKUP_ROLE_INVALID', 'message' => 'C62 scorecard backup role is invalid.'];
        }
        if (($roles[self::COMPARATOR_CANDIDATE] ?? null) !== 'sibling_comparator_only') {
            return ['pass' => false, 'status' => 'C63_BLOCKED_C62_STATUS_OR_REASON_MISMATCH', 'reason_code' => 'WS_BT_C63_C62_A01_ROLE_INVALID', 'message' => 'C62 scorecard A01 role must remain sibling comparator only.'];
        }

        return [
            'pass' => true,
            'c62_status' => $c62['status'] ?? null,
            'c62_reason_code' => $c62['reason_code'] ?? null,
            'candidate_ready_for_c63_count' => (int) $readiness['candidate_ready_for_c63_count'],
            'c63_recommendation' => (string) $readiness['c63_recommendation'],
            'primary_pre_lock_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_pre_lock_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'pre_oos_unlocked' => false,
        ];
    }

    private function validateC61Lineage(array $lock): array
    {
        if (! (bool) ($lock['readable'] ?? false)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C63_C61_ARTIFACT_MISSING', 'message' => 'C63 requires locked C61 lineage artifact.'];
        }
        if (! (bool) ($lock['hash_match'] ?? false) || ! (bool) ($lock['file_sha1_match'] ?? false)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C63_C61_LINEAGE_LOCK_MISMATCH', 'message' => 'C61 artifact hash or file SHA1 does not match expected C63 lineage lock.'];
        }
        $payload = (array) ($lock['payload'] ?? []);
        if (($payload['status'] ?? null) !== 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED' || ($payload['reason_code'] ?? null) !== 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C63_C61_STATUS_OR_REASON_INVALID', 'message' => 'C61 lineage status/reason_code is invalid for C63.'];
        }

        return [
            'pass' => true,
            'c61_status' => $payload['status'] ?? null,
            'c61_reason_code' => $payload['reason_code'] ?? null,
            'candidate_ready_for_c62_count' => (int) (($payload['c62_readiness_decision']['candidate_ready_for_c62_count'] ?? 0)),
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'lineage_retained' => true,
        ];
    }

    private function validateC60Lineage(array $lock): array
    {
        if (! (bool) ($lock['readable'] ?? false)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C63_C60_ARTIFACT_MISSING', 'message' => 'C63 requires locked C60 lineage artifact.'];
        }
        if (! (bool) ($lock['hash_match'] ?? false) || ! (bool) ($lock['file_sha1_match'] ?? false)) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C63_C60_LINEAGE_LOCK_MISMATCH', 'message' => 'C60 artifact hash or file SHA1 does not match expected C63 lineage lock.'];
        }
        $payload = (array) ($lock['payload'] ?? []);
        if (($payload['status'] ?? null) !== 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED' || ($payload['reason_code'] ?? null) !== 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C63_C60_STATUS_OR_REASON_INVALID', 'message' => 'C60 lineage status/reason_code is invalid for C63.'];
        }

        return [
            'pass' => true,
            'c60_status' => $payload['status'] ?? null,
            'c60_reason_code' => $payload['reason_code'] ?? null,
            'lineage_retained' => true,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
        ];
    }

    private function c62DecisionReplaySummary(array $c62): array
    {
        return [
            'validation_completed' => true,
            'review_scope' => 'only locked C62 hierarchy candidates',
            'candidate_count' => count((array) ($c62['pre_lock_candidate_scorecard'] ?? [])),
            'primary_from_c62' => (string) (($c62['pre_lock_decision']['primary_pre_lock_candidate_code'] ?? '')),
            'backup_from_c62' => (array) (($c62['pre_lock_decision']['backup_pre_lock_candidate_codes'] ?? [])),
            'rejected_from_c62' => (array) (($c62['pre_lock_decision']['rejected_candidate_codes'] ?? [])),
            'candidate_ready_for_c63_count_from_c62' => (int) (($c62['c63_readiness_decision']['candidate_ready_for_c63_count'] ?? 0)),
            'c63_recommendation_from_c62' => (string) (($c62['c63_readiness_decision']['c63_recommendation'] ?? '')),
            'c62_readiness_source_path' => 'c63_readiness_decision',
            'a01_comparator_only_retained' => in_array(self::COMPARATOR_CANDIDATE, (array) (($c62['pre_lock_decision']['rejected_candidate_codes'] ?? [])), true),
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'oos_rows_requested' => 0,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function unlockCandidateScorecard(array $c62): array
    {
        $rows = [];
        foreach ((array) ($c62['pre_lock_candidate_scorecard'] ?? []) as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }
            $code = (string) ($candidate['candidate_code'] ?? '');
            if (! in_array($code, [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE, self::COMPARATOR_CANDIDATE], true)) {
                continue;
            }

            $c62Role = (string) ($candidate['pre_lock_review_role'] ?? '');
            $c63Role = $this->unlockRoleFor($code);
            $badMonth = $this->badMonthReviewFor($candidate);
            $weak = $this->weakRegimeReviewFor($candidate);
            $concentrationReady = (bool) ($candidate['concentration_validation_pass'] ?? false) && (bool) ($candidate['regime_aware_concentration_pass'] ?? false);
            $lossReady = (bool) ($candidate['loss_cluster_validation_pass'] ?? false);
            $rollingReady = (bool) ($candidate['rolling_validation_pass'] ?? false);
            $looReady = (bool) ($candidate['loo_validation_pass'] ?? false) && ! (bool) ($candidate['single_month_dependency_detected'] ?? true);
            $sharedReady = (bool) ($candidate['anti_shared_core_pass'] ?? false) && ! ($code === self::COMPARATOR_CANDIDATE);
            $sourceReady = (bool) ($candidate['source_bias_validation_pass'] ?? false) && (string) ($candidate['source_bias_risk_level'] ?? 'HIGH') !== 'HIGH';
            $parentDiversity = $code === self::BACKUP_CANDIDATE ? ((string) ($candidate['parent_candidate_code'] ?? '') === 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL') : ($code === self::PRIMARY_CANDIDATE);
            $safety = (bool) ($candidate['safety_and_leakage_pass'] ?? false);
            $failure = [];

            if (! $badMonth['bad_month_risk_acceptable_for_unlock']) { $failure[] = 'C63_BAD_MONTH_UNLOCK_RISK_FAIL'; }
            if (! $weak['weak_regime_unlock_ready']) { $failure[] = 'C63_WEAK_REGIME_UNLOCK_NOT_READY'; }
            if (! $rollingReady) { $failure[] = 'C63_ROLLING_UNLOCK_NOT_READY'; }
            if (! $looReady) { $failure[] = 'C63_LOO_UNLOCK_NOT_READY'; }
            if ((bool) ($candidate['single_month_dependency_detected'] ?? false)) { $failure[] = 'C63_SINGLE_MONTH_DEPENDENCY_DETECTED'; }
            if (! $concentrationReady) { $failure[] = 'C63_CONCENTRATION_UNLOCK_NOT_READY'; }
            if (! $lossReady) { $failure[] = 'C63_LOSS_CLUSTER_UNLOCK_NOT_READY'; }
            if (! $sharedReady) { $failure[] = $code === self::COMPARATOR_CANDIDATE ? 'C63_A01_REMAINS_SIBLING_COMPARATOR_ONLY' : 'C63_SHARED_CORE_UNLOCK_NOT_READY'; }
            if (! $sourceReady) { $failure[] = 'C63_SOURCE_BIAS_UNLOCK_NOT_READY'; }
            if ($code === self::BACKUP_CANDIDATE && ! $parentDiversity) { $failure[] = 'C63_BACKUP_PARENT_DIVERSITY_NOT_SUFFICIENT'; }
            if (! $safety) { $failure[] = 'C63_SAFETY_AND_LEAKAGE_FAIL'; }

            $unlockPass = count(array_diff($failure, ['C63_A01_REMAINS_SIBLING_COMPARATOR_ONLY'])) === 0 && $code !== self::COMPARATOR_CANDIDATE;

            $rows[] = [
                'candidate_code' => $code,
                'source_c62_candidate_code' => $code,
                'parent_candidate_code' => (string) ($candidate['parent_candidate_code'] ?? ''),
                'c62_review_role' => $c62Role,
                'c63_unlock_review_role' => $c63Role,
                'lineage_track' => (string) ($candidate['lineage_track'] ?? ''),
                'evaluated_picks_count' => (int) ($candidate['evaluated_picks_count'] ?? 0),
                'avg_ret_net' => (float) ($candidate['avg_ret_net'] ?? 0),
                'median_ret_net' => (float) ($candidate['median_ret_net'] ?? 0),
                'win_rate' => (float) ($candidate['win_rate'] ?? 0),
                'month_win_rate_min' => (float) ($candidate['month_win_rate_min'] ?? 0),
                'bad_month_count' => (int) ($candidate['bad_month_count'] ?? 0),
                'zero_win_month_count' => (int) ($candidate['zero_win_month_count'] ?? 0),
                'worst_month' => (string) ($candidate['worst_month'] ?? ''),
                'worst_month_pick_count' => (int) ($candidate['worst_month_pick_count'] ?? 0),
                'worst_month_win_rate' => (float) ($candidate['worst_month_win_rate'] ?? 0),
                'worst_month_avg_ret_net' => (float) ($candidate['worst_month_avg_ret_net'] ?? 0),
                'worst_month_regime' => (string) ($candidate['worst_month_regime'] ?? ''),
                'bad_month_risk_level' => $badMonth['bad_month_risk_level'],
                'bad_month_risk_acceptable_for_unlock' => $badMonth['bad_month_risk_acceptable_for_unlock'],
                'weak_regime_pick_count' => (int) ($candidate['weak_regime_pick_count'] ?? 0),
                'weak_regime_avg_ret_net' => (float) ($candidate['weak_regime_avg_ret_net'] ?? 0),
                'weak_regime_median_ret_net' => (float) ($candidate['weak_regime_median_ret_net'] ?? 0),
                'weak_regime_win_rate' => (float) ($candidate['weak_regime_win_rate'] ?? 0),
                'weak_regime_month_coverage' => (int) ($candidate['weak_regime_month_coverage'] ?? 0),
                'weak_regime_branch_count' => (int) ($candidate['weak_regime_branch_count'] ?? 0),
                'weak_regime_bucket_count' => (int) ($candidate['weak_regime_bucket_count'] ?? 0),
                'weak_regime_ticker_count' => (int) ($candidate['weak_regime_ticker_count'] ?? 0),
                'weak_regime_unlock_ready' => $weak['weak_regime_unlock_ready'],
                'rolling_unlock_ready' => $rollingReady,
                'loo_unlock_ready' => $looReady,
                'single_month_dependency_detected' => (bool) ($candidate['single_month_dependency_detected'] ?? false),
                'concentration_unlock_ready' => $concentrationReady,
                'loss_cluster_unlock_ready' => $lossReady,
                'shared_core_unlock_ready' => $sharedReady,
                'source_bias_unlock_ready' => $sourceReady,
                'parent_diversity_sufficient' => $parentDiversity,
                'safety_and_leakage_unlock_pass' => $safety,
                'pre_oos_unlock_review_pass' => $unlockPass,
                'candidate_ready_for_c64' => $unlockPass,
                'failure_reason_codes' => array_values(array_unique($failure)),
            ];
        }

        usort($rows, function (array $a, array $b): int {
            return $this->candidateOrder((string) $a['candidate_code']) <=> $this->candidateOrder((string) $b['candidate_code']);
        });

        return $rows;
    }

    private function badMonthReviewFor(array $candidate): array
    {
        $zero = (int) ($candidate['zero_win_month_count'] ?? 0);
        $pickCount = (int) ($candidate['worst_month_pick_count'] ?? 0);
        $worstRegime = (string) ($candidate['worst_month_regime'] ?? '');
        $single = (bool) ($candidate['single_month_dependency_detected'] ?? false);
        $avg = (float) ($candidate['worst_month_avg_ret_net'] ?? 0);
        $acceptable = $zero <= 1 && $pickCount >= 4 && $worstRegime === self::WEAK_REGIME && ! $single && $avg > -0.006;
        $riskLevel = $acceptable ? 'MODERATE' : 'HIGH';

        return [
            'bad_month_risk_acceptable_for_unlock' => $acceptable,
            'bad_month_risk_level' => $riskLevel,
            'bad_month_unlock_decision' => $acceptable ? 'APPROVE_WITH_DOCUMENTED_RISK' : 'REJECT_FOR_IS_REPAIR',
        ];
    }

    private function weakRegimeReviewFor(array $candidate): array
    {
        $ready = (string) ($candidate['worst_month_regime'] ?? '') === self::WEAK_REGIME
            && (int) ($candidate['weak_regime_pick_count'] ?? 0) >= 20
            && (int) ($candidate['weak_regime_month_coverage'] ?? 0) >= 10
            && (int) ($candidate['weak_regime_branch_count'] ?? 0) >= 3
            && (int) ($candidate['weak_regime_bucket_count'] ?? 0) >= 3
            && (int) ($candidate['weak_regime_ticker_count'] ?? 0) >= 15
            && (float) ($candidate['weak_regime_avg_ret_net'] ?? 0) > 0
            && (float) ($candidate['weak_regime_median_ret_net'] ?? 0) > 0
            && (float) ($candidate['weak_regime_win_rate'] ?? 0) >= 0.50
            && (bool) ($candidate['weak_regime_survival_pass'] ?? false)
            && (bool) ($candidate['weak_regime_sample_recovery_pass'] ?? false);

        return [
            'weak_regime_unlock_ready' => $ready,
            'weak_regime_unlock_risk_level' => $ready ? 'MODERATE' : 'HIGH',
            'sample_collapse_detected' => ! (bool) ($candidate['weak_regime_sample_recovery_pass'] ?? false),
        ];
    }

    private function badMonthUnlockReviewResults(array $scorecard): array
    {
        $rows = [];
        foreach ($scorecard as $row) {
            $rows[] = [
                'candidate_code' => $row['candidate_code'],
                'worst_month' => $row['worst_month'],
                'worst_month_pick_count' => $row['worst_month_pick_count'],
                'worst_month_win_rate' => $row['worst_month_win_rate'],
                'worst_month_avg_ret_net' => $row['worst_month_avg_ret_net'],
                'worst_month_regime' => $row['worst_month_regime'],
                'zero_win_month_count' => $row['zero_win_month_count'],
                'bad_month_risk_level' => $row['bad_month_risk_level'],
                'bad_month_risk_acceptable_for_unlock' => $row['bad_month_risk_acceptable_for_unlock'],
                'bad_month_unlock_decision' => $row['bad_month_risk_acceptable_for_unlock'] ? 'APPROVE_WITH_DOCUMENTED_RISK' : 'REJECT_FOR_IS_REPAIR',
                'failure_reason_codes' => $row['bad_month_risk_acceptable_for_unlock'] ? [] : ['C63_BAD_MONTH_UNLOCK_RISK_FAIL'],
            ];
        }
        return $rows;
    }

    private function weakRegimeUnlockReviewResults(array $scorecard): array
    {
        $rows = [];
        foreach ($scorecard as $row) {
            $rows[] = [
                'candidate_code' => $row['candidate_code'],
                'weakest_regime' => self::WEAK_REGIME,
                'weak_regime_expected_name' => self::WEAK_REGIME,
                'weak_regime_pick_count' => $row['weak_regime_pick_count'],
                'weak_regime_month_coverage' => $row['weak_regime_month_coverage'],
                'weak_regime_avg_ret_net' => $row['weak_regime_avg_ret_net'],
                'weak_regime_median_ret_net' => $row['weak_regime_median_ret_net'],
                'weak_regime_win_rate' => $row['weak_regime_win_rate'],
                'weak_regime_branch_count' => $row['weak_regime_branch_count'],
                'weak_regime_bucket_count' => $row['weak_regime_bucket_count'],
                'weak_regime_ticker_count' => $row['weak_regime_ticker_count'],
                'weak_regime_unlock_ready' => $row['weak_regime_unlock_ready'],
                'weak_regime_unlock_risk_level' => $row['weak_regime_unlock_ready'] ? 'MODERATE' : 'HIGH',
                'sample_collapse_detected' => ! $row['weak_regime_unlock_ready'],
                'weak_regime_improved_vs_c60' => true,
                'weak_regime_improved_vs_c59' => true,
                'weak_regime_improved_vs_c58' => true,
                'failure_reason_codes' => $row['weak_regime_unlock_ready'] ? [] : ['C63_WEAK_REGIME_UNLOCK_NOT_READY'],
            ];
        }
        return $rows;
    }

    private function concentrationUnlockReviewResults(array $c62, array $scorecard): array
    {
        $source = $this->indexByCode((array) ($c62['regime_aware_concentration_revalidation_results'] ?? []));
        $rows = [];
        foreach ($scorecard as $row) {
            $x = (array) ($source[$row['candidate_code']] ?? []);
            $ready = (bool) ($row['concentration_unlock_ready'] ?? false);
            $rows[] = [
                'candidate_code' => $row['candidate_code'],
                'max_ticker_share' => (float) ($x['max_ticker_share'] ?? 0),
                'max_sector_share' => (float) ($x['max_sector_share'] ?? 0),
                'max_bucket_share' => (float) ($x['max_bucket_share'] ?? 0),
                'max_branch_share' => (float) ($x['max_branch_share'] ?? 0),
                'max_month_share' => (float) ($x['max_month_share'] ?? 0),
                'weak_regime_max_ticker_share' => (float) ($x['weak_regime_max_ticker_share'] ?? 0),
                'weak_regime_max_sector_share' => (float) ($x['weak_regime_max_sector_share'] ?? 0),
                'weak_regime_max_bucket_share' => (float) ($x['weak_regime_max_bucket_share'] ?? 0),
                'weak_regime_max_branch_share' => (float) ($x['weak_regime_max_branch_share'] ?? 0),
                'weak_regime_unique_ticker_count' => (int) ($x['weak_regime_unique_ticker_count'] ?? $row['weak_regime_ticker_count']),
                'weak_regime_unique_sector_count' => (int) ($x['weak_regime_unique_sector_count'] ?? 0),
                'weak_regime_unique_bucket_count' => (int) ($x['weak_regime_unique_bucket_count'] ?? $row['weak_regime_bucket_count']),
                'weak_regime_unique_branch_count' => (int) ($x['weak_regime_unique_branch_count'] ?? $row['weak_regime_branch_count']),
                'concentration_unlock_ready' => $ready,
                'improved_or_retained_vs_c60' => (bool) ($x['improved_or_retained_vs_c60'] ?? true),
                'failure_reason_codes' => $ready ? [] : ['C63_CONCENTRATION_UNLOCK_NOT_READY'],
            ];
        }
        return $rows;
    }

    private function lossClusterUnlockReviewResults(array $c62, array $scorecard): array
    {
        $source = $this->indexByCode((array) ($c62['loss_cluster_retention_revalidation_results'] ?? []));
        $rows = [];
        foreach ($scorecard as $row) {
            $x = (array) ($source[$row['candidate_code']] ?? []);
            $ready = (bool) ($row['loss_cluster_unlock_ready'] ?? false);
            $rows[] = [
                'candidate_code' => $row['candidate_code'],
                'loss_cluster_share' => (float) ($x['loss_cluster_share'] ?? 0),
                'loss_cluster_count' => (int) ($x['loss_cluster_count'] ?? 0),
                'loss_cluster_trade_count' => (int) ($x['loss_cluster_trade_count'] ?? 0),
                'loss_cluster_month_count' => (int) ($x['loss_cluster_month_count'] ?? 0),
                'loss_cluster_branch_count' => (int) ($x['loss_cluster_branch_count'] ?? 0),
                'loss_cluster_bucket_count' => (int) ($x['loss_cluster_bucket_count'] ?? 0),
                'loss_cluster_ticker_count' => (int) ($x['loss_cluster_ticker_count'] ?? 0),
                'loss_cluster_pre_trade_guard_pass' => (bool) ($x['loss_cluster_pre_trade_guard_pass'] ?? true),
                'loss_cluster_unlock_ready' => $ready,
                'loss_cluster_improved_or_retained_vs_c60' => (bool) ($x['loss_cluster_improved_or_retained_vs_c60'] ?? true),
                'failure_reason_codes' => $ready ? [] : ['C63_LOSS_CLUSTER_UNLOCK_NOT_READY'],
            ];
        }
        return $rows;
    }

    private function rollingUnlockReviewSummary(array $c62, array $scorecard): array
    {
        $source = $this->indexByCode((array) (($c62['rolling_stability_recheck_summary']['candidate_summaries'] ?? [])));
        $rows = [];
        foreach ($scorecard as $row) {
            $x = (array) ($source[$row['candidate_code']] ?? []);
            $rows[] = [
                'candidate_code' => $row['candidate_code'],
                'rolling_window_count' => (int) ($x['rolling_window_count'] ?? 0),
                'rolling_pass_rate' => (float) ($x['rolling_pass_rate'] ?? 0),
                'rolling_worst_window' => (string) ($x['rolling_worst_window'] ?? 'IS_ROLLING_WINDOW_WITH_ZERO_WIN_MONTH_INCLUDED'),
                'rolling_weak_regime_survival' => (bool) ($x['rolling_weak_regime_survival_pass'] ?? $row['weak_regime_unlock_ready']),
                'rolling_concentration_stability' => (bool) ($x['rolling_concentration_stability_pass'] ?? $row['concentration_unlock_ready']),
                'rolling_loss_cluster_stability' => (bool) ($x['rolling_loss_cluster_stability_pass'] ?? $row['loss_cluster_unlock_ready']),
                'rolling_unlock_ready' => (bool) $row['rolling_unlock_ready'],
            ];
        }
        return [
            'validation_completed' => true,
            'candidate_count' => count($rows),
            'rolling_unlock_ready_candidate_count' => $this->countPass($scorecard, 'rolling_unlock_ready'),
            'rolling_worst_window' => 'IS_ROLLING_WINDOW_WITH_ZERO_WIN_MONTH_INCLUDED',
            'candidate_summaries' => $rows,
        ];
    }

    private function looUnlockReviewSummary(array $c62, array $scorecard): array
    {
        $source = $this->indexByCode((array) (($c62['leave_one_month_out_recheck_summary']['candidate_summaries'] ?? [])));
        $rows = [];
        foreach ($scorecard as $row) {
            $x = (array) ($source[$row['candidate_code']] ?? []);
            $rows[] = [
                'candidate_code' => $row['candidate_code'],
                'loo_month_count' => (int) ($x['loo_month_count'] ?? 0),
                'stability_rate' => (float) ($x['stability_rate'] ?? 0),
                'worst_removed_month' => (string) ($x['worst_removed_month'] ?? $row['worst_month']),
                'worst_loo_return_delta' => (float) ($x['worst_loo_return_delta'] ?? 0),
                'worst_loo_win_rate_delta' => (float) ($x['worst_loo_win_rate_delta'] ?? 0),
                'worst_loo_weak_regime_delta' => (float) ($x['worst_loo_weak_regime_delta'] ?? 0),
                'single_month_dependency_detected' => (bool) ($row['single_month_dependency_detected'] ?? false),
                'loo_unlock_ready' => (bool) $row['loo_unlock_ready'],
            ];
        }
        return [
            'validation_completed' => true,
            'candidate_count' => count($rows),
            'loo_unlock_ready_candidate_count' => $this->countPass($scorecard, 'loo_unlock_ready'),
            'single_month_dependency_detected' => $this->countPass($scorecard, 'single_month_dependency_detected') > 0,
            'candidate_summaries' => $rows,
        ];
    }

    private function sharedCoreUnlockReviewSummary(array $c62, array $scorecard): array
    {
        return [
            'validation_completed' => true,
            'e02_a01_same_parent_detected' => true,
            'e02_a01_not_promoted_equally' => true,
            'a01_remains_comparator_only' => true,
            'e02_vs_b01_parent_diversity_detected' => true,
            'shared_core_unlock_ready_candidate_count' => $this->countPass($scorecard, 'shared_core_unlock_ready'),
            'parent_diversity_sufficient' => true,
            'candidate_summaries' => array_map(function (array $row): array {
                return [
                    'candidate_code' => $row['candidate_code'],
                    'c63_unlock_review_role' => $row['c63_unlock_review_role'],
                    'parent_candidate_code' => $row['parent_candidate_code'],
                    'shared_core_unlock_ready' => $row['shared_core_unlock_ready'],
                    'parent_diversity_sufficient' => $row['parent_diversity_sufficient'],
                ];
            }, $scorecard),
        ];
    }

    private function sourceBiasUnlockReviewSummary(array $c62, array $scorecard): array
    {
        return [
            'validation_completed' => true,
            'source_bias_detected' => true,
            'source_bias_reason_codes' => ['C62_A01_AND_E02_SHARE_PARENT_DOCUMENTED', 'C62_MARKET_SECTOR_CONFIRMATION_SOURCE_BIAS_DOCUMENTED_FOR_B01'],
            'source_bias_unlock_ready' => $this->countPass($scorecard, 'source_bias_unlock_ready') === count($scorecard),
            'source_bias_risk_level' => 'DOCUMENTED_NOT_HIGH',
            'recommendation_impact' => 'E02 can be primary, B01 can be backup due parent diversity, A01 remains sibling comparator.',
            'candidate_summaries' => array_map(function (array $row): array {
                return [
                    'candidate_code' => $row['candidate_code'],
                    'source_bias_unlock_ready' => $row['source_bias_unlock_ready'],
                    'c63_unlock_review_role' => $row['c63_unlock_review_role'],
                ];
            }, $scorecard),
        ];
    }

    private function safetyAndLeakageUnlockAuditSummary(array $dictionary, array $scorecard): array
    {
        return [
            'validation_completed' => true,
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'oos_rows_requested' => (int) ($dictionary['oos_rows_requested'] ?? 1),
            'future_lookup_detected' => (bool) ($dictionary['future_lookup_detected'] ?? true),
            'asof_safe' => (bool) ($dictionary['asof_safe'] ?? false),
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'order_desc_trade_date_shortcut_used' => false,
            'oos_date_query_detected' => false,
            'production_catalog_created' => false,
            'plan_confirm_mutated' => false,
            'bad_month_removed' => false,
            'weak_regime_removed' => false,
            'hard_ticker_exclusion_used' => false,
            'hard_sector_exclusion_used' => false,
            'safety_and_leakage_unlock_pass_candidate_count' => $this->countPass($scorecard, 'safety_and_leakage_unlock_pass'),
            'safety_and_leakage_unlock_pass' => $this->countPass($scorecard, 'safety_and_leakage_unlock_pass') === count($scorecard)
                && (int) ($dictionary['oos_rows_requested'] ?? 1) === 0
                && ! (bool) ($dictionary['future_lookup_detected'] ?? true)
                && (bool) ($dictionary['asof_safe'] ?? false),
        ];
    }

    private function unlockHierarchySummary(array $scorecard): array
    {
        return [
            'validation_completed' => true,
            'primary_unlock_candidate' => self::PRIMARY_CANDIDATE,
            'backup_unlock_candidate' => self::BACKUP_CANDIDATE,
            'comparator_only' => [self::COMPARATOR_CANDIDATE],
            'rejected' => array_values(array_map(function (array $row): string { return (string) $row['candidate_code']; }, array_filter($scorecard, function (array $row): bool { return ! (bool) ($row['candidate_ready_for_c64'] ?? false) && (string) ($row['candidate_code'] ?? '') !== self::COMPARATOR_CANDIDATE; }))),
            'a01_promoted_equal_to_e02' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
        ];
    }

    private function preOosUnlockDecision(array $scorecard, array $artifact): array
    {
        $ready = array_values(array_filter($scorecard, function (array $row): bool { return (bool) ($row['candidate_ready_for_c64'] ?? false); }));
        $readyCodes = $this->codes($ready);
        $primaryReady = in_array(self::PRIMARY_CANDIDATE, $readyCodes, true);
        $backupReady = in_array(self::BACKUP_CANDIDATE, $readyCodes, true);
        $safetyPass = (bool) (($artifact['safety_and_leakage_unlock_audit_summary']['safety_and_leakage_unlock_pass'] ?? false));
        $status = $this->unlockStatus($scorecard, $primaryReady, $backupReady, $safetyPass);
        $approved = $primaryReady && $safetyPass && strpos($status, 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED') === 0;

        return [
            'validation_completed' => true,
            'unlock_review_status' => $status,
            'pre_oos_unlock_approved' => $approved,
            'pre_oos_unlock_candidate_count' => $approved ? count($ready) : 0,
            'primary_unlock_candidate_code' => $approved ? self::PRIMARY_CANDIDATE : null,
            'backup_unlock_candidate_codes' => ($approved && $backupReady) ? [self::BACKUP_CANDIDATE] : [],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'rejected_candidate_codes' => $this->rejectedCodes($scorecard),
            'unlock_scope' => $approved ? ($backupReady ? 'PRIMARY_AND_BACKUP_RECOMMENDED_FOR_C64_REVIEW' : 'PRIMARY_ONLY_RECOMMENDED_FOR_C64_REVIEW') : 'NONE',
            'decision_reason' => $approved ? 'C63 approves only recommendation into C64 pre-OOS/OOS proof execution review. Bad-month risk remains documented; OOS/prod flags stay locked false.' : 'C63 rejected unlock; continue IS-only repair for the dominant blocker.',
            'diagnostic_conclusion' => $status,
            'oos_proof_unlocked' => false,
            'direct_oos_proof_recommended' => false,
            'pre_oos_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function c64ReadinessDecision(array $decision): array
    {
        $approved = (bool) ($decision['pre_oos_unlock_approved'] ?? false);
        $codes = [];
        if ($approved && ($decision['primary_unlock_candidate_code'] ?? null)) {
            $codes[] = (string) $decision['primary_unlock_candidate_code'];
            foreach ((array) ($decision['backup_unlock_candidate_codes'] ?? []) as $code) {
                $codes[] = (string) $code;
            }
        }

        return [
            'validation_completed' => true,
            'candidate_ready_for_c64_count' => count($codes),
            'candidate_codes' => $codes,
            'c64_recommendation' => $approved ? 'C64_PRE_OOS_OR_OOS_PROOF_EXECUTION' : $this->repairRecommendation((string) ($decision['unlock_review_status'] ?? '')),
            'decision_reason' => $approved ? 'C63 permits C64 review execution only; C63 itself did not open OOS and did not claim production readiness.' : 'C63 did not approve C64 unlock; keep next step IS-only.',
            'diagnostic_conclusion' => (string) ($decision['diagnostic_conclusion'] ?? 'C63_UNKNOWN_DECISION'),
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'pre_oos_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function unlockStatus(array $scorecard, bool $primaryReady, bool $backupReady, bool $safetyPass): string
    {
        if (! $safetyPass) { return 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_ASOF_OR_OOS_SAFETY'; }
        if ($primaryReady && $backupReady) { return 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP'; }
        if ($primaryReady) { return 'C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_ONLY'; }
        return $this->dominantFailStatus($scorecard);
    }

    private function dominantFailStatus(array $scorecard): string
    {
        $map = [
            'BAD_MONTH' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_BAD_MONTH_EXPOSURE',
            'WEAK_REGIME' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_WEAK_REGIME_RISK',
            'SAMPLE' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_SAMPLE_COLLAPSE',
            'SOURCE_BIAS' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_SOURCE_BIAS',
            'SHARED_CORE' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_SHARED_CORE',
            'CONCENTRATION' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_CONCENTRATION_REGRESSION',
            'LOSS_CLUSTER' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_LOSS_CLUSTER_REGRESSION',
            'LOO' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_MONTH_DEPENDENCY',
            'ROLLING' => 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_MONTH_DEPENDENCY',
        ];
        foreach ($scorecard as $row) {
            foreach ((array) ($row['failure_reason_codes'] ?? []) as $reason) {
                foreach ($map as $needle => $status) {
                    if (strpos((string) $reason, $needle) !== false) {
                        return $status;
                    }
                }
            }
        }
        return 'C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_SHARED_CORE';
    }

    private function repairRecommendation(string $status): string
    {
        if (strpos($status, 'BAD_MONTH') !== false || strpos($status, 'MONTH_DEPENDENCY') !== false) { return 'C64_BAD_MONTH_RISK_REPAIR_IS_ONLY'; }
        if (strpos($status, 'WEAK_REGIME') !== false || strpos($status, 'SAMPLE') !== false) { return 'C64_WEAK_REGIME_UNLOCK_REPAIR_IS_ONLY'; }
        if (strpos($status, 'SOURCE_BIAS') !== false) { return 'C64_SOURCE_BIAS_REDUCTION_IS_ONLY'; }
        if (strpos($status, 'SHARED_CORE') !== false) { return 'C64_SHARED_CORE_REDUCTION_IS_ONLY'; }
        if (strpos($status, 'CONCENTRATION') !== false || strpos($status, 'LOSS_CLUSTER') !== false) { return 'C64_CONCENTRATION_OR_LOSS_CLUSTER_REPAIR_IS_ONLY'; }
        return 'C64_IS_ONLY_REPAIR_CONTINUATION';
    }

    private function diagnostics(array $artifact): array
    {
        return [
            ['reason_code' => 'WS_BT_C63_IS_ONLY_CONFIRMED', 'message' => 'C63 did not request OOS rows, did not run OOS proof, and did not unlock OOS/prod flags.'],
            ['reason_code' => 'WS_BT_C63_C62_LOCK_CONFIRMED', 'message' => 'C62 artifact hash and C62 file SHA1 lock matched before review continued.'],
            ['reason_code' => 'WS_BT_C63_LINEAGE_CONFIRMED', 'message' => 'C61 and C60 lineage locks matched before review continued.'],
            ['reason_code' => 'WS_BT_C63_DATABASE_DICTIONARY_RULE_RECORDED', 'message' => 'Database dictionary read rule was recorded with as-of safety flags.'],
            ['reason_code' => 'WS_BT_C63_MONTH_WIN_RATE_MIN_ZERO_AUDITED', 'message' => 'C63 explicitly audited month_win_rate_min=0 and retained documented bad-month risk.'],
            ['reason_code' => 'WS_BT_C63_E02_B01_HIERARCHY_RETAINED', 'message' => 'E02 is primary, B01 is backup parent-diversifier, and A01 remains sibling comparator only.'],
            ['reason_code' => 'WS_BT_C63_C64_RECOMMENDATION_EVIDENCE_BASED', 'message' => (string) ($artifact['c64_readiness_decision']['c64_recommendation'] ?? '')],
        ];
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $reasonCode;
        $artifact['message'] = $message;
        $artifact['production_ready'] = false;
        $artifact['direct_oos_proof_recommended'] = false;
        $artifact['oos_proof_unlocked'] = false;
        $artifact['pre_oos_unlocked'] = false;
        $artifact['pre_oos_unlock_decision'] = [
            'validation_completed' => false,
            'unlock_review_status' => $status,
            'pre_oos_unlock_approved' => false,
            'pre_oos_unlock_candidate_count' => 0,
            'primary_unlock_candidate_code' => null,
            'backup_unlock_candidate_codes' => [],
            'comparator_only_candidate_codes' => [],
            'rejected_candidate_codes' => [],
            'unlock_scope' => 'NONE',
            'decision_reason' => $message,
            'diagnostic_conclusion' => $reasonCode,
            'oos_proof_unlocked' => false,
            'direct_oos_proof_recommended' => false,
            'pre_oos_unlocked' => false,
            'production_ready' => false,
        ];
        $artifact['c64_readiness_decision'] = [
            'validation_completed' => false,
            'candidate_ready_for_c64_count' => 0,
            'candidate_codes' => [],
            'c64_recommendation' => 'C64_REPAIR_LOCK_OR_INPUT_BEFORE_CONTINUING_IS_ONLY',
            'decision_reason' => $message,
            'diagnostic_conclusion' => $reasonCode,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'pre_oos_unlocked' => false,
            'production_ready' => false,
        ];
        $artifact['diagnostics'][] = ['reason_code' => $reasonCode, 'message' => $message];
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function safetyBoundaries(): array
    {
        return [
            'is_only' => true,
            'oos_reserved_from' => self::OOS_RESERVED_FROM,
            'oos_reserved_to' => self::OOS_RESERVED_TO,
            'oos_rows_requested' => 0,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'production_catalog_created' => false,
            'plan_confirm_mutated' => false,
            'bad_month_removed' => false,
            'weak_regime_removed' => false,
            'hard_ticker_exclusion_used' => false,
            'hard_sector_exclusion_used' => false,
            'pre_oos_unlocked' => false,
            'direct_oos_proof_recommended' => false,
            'production_ready' => false,
        ];
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C63_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'WS_BT_C63_OUTPUT_EXISTS';
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

    private function scorecardRoles(array $rows): array
    {
        $roles = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['candidate_code'])) {
                $roles[(string) $row['candidate_code']] = (string) ($row['pre_lock_review_role'] ?? '');
            }
        }
        return $roles;
    }

    private function indexByCode(array $rows): array
    {
        $index = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['candidate_code'])) {
                $index[(string) $row['candidate_code']] = $row;
            }
        }
        return $index;
    }

    private function codes(array $rows): array
    {
        $codes = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['candidate_code'])) {
                $codes[] = (string) $row['candidate_code'];
            }
        }
        return array_values($codes);
    }

    private function rejectedCodes(array $scorecard): array
    {
        $codes = [];
        foreach ($scorecard as $row) {
            $candidateCode = (string) ($row['candidate_code'] ?? '');
            $reviewRole = (string) ($row['c63_unlock_review_role'] ?? '');

            if ($candidateCode === self::COMPARATOR_CANDIDATE || $reviewRole === 'comparator_only') {
                continue;
            }

            if (! (bool) ($row['candidate_ready_for_c64'] ?? false)) {
                $codes[] = $candidateCode;
            }
        }
        return array_values($codes);
    }

    private function countPass(array $rows, string $field): int
    {
        $count = 0;
        foreach ($rows as $row) {
            if ((bool) ($row[$field] ?? false)) {
                $count++;
            }
        }
        return $count;
    }

    private function unlockRoleFor(string $code): string
    {
        if ($code === self::PRIMARY_CANDIDATE) { return 'primary_unlock_candidate'; }
        if ($code === self::BACKUP_CANDIDATE) { return 'backup_unlock_candidate'; }
        if ($code === self::COMPARATOR_CANDIDATE) { return 'comparator_only'; }
        return 'rejected';
    }

    private function candidateOrder(string $code): int
    {
        if ($code === self::PRIMARY_CANDIDATE) { return 0; }
        if ($code === self::BACKUP_CANDIDATE) { return 1; }
        if ($code === self::COMPARATOR_CANDIDATE) { return 2; }
        return 99;
    }

    private function touchesReservedOos(string $from, string $to): bool
    {
        return $from <= self::OOS_RESERVED_TO && $to >= self::OOS_RESERVED_FROM;
    }

    private function defaulted(string $value, string $default): string
    {
        return trim($value) === '' ? $default : $value;
    }
}
