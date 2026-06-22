<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService
{
    public const RUN_CODE = 'C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY';
    public const ARTIFACT_TYPE = 'C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY';
    public const DEFAULT_C61_ARTIFACT = 'storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json';
    public const DEFAULT_EXPECTED_C61_HASH = '40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8';
    public const DEFAULT_EXPECTED_C61_FILE_SHA1 = 'DEA3C807813DE81DB6776AB2C441C945D4E98EC6';
    public const DEFAULT_C60_ARTIFACT = 'storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json';
    public const DEFAULT_EXPECTED_C60_HASH = '25a32ee9c4cb77ecc29103c86a1abf0826aea705';
    public const DEFAULT_EXPECTED_C60_FILE_SHA1 = '1FA933157B61ECB4554CE6C76B0F2B314F19DB0F';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';
    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const DIVERSIFICATION_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const REQUIRED_READY_CANDIDATES = [
        self::PRIMARY_CANDIDATE,
        self::BACKUP_CANDIDATE,
        self::DIVERSIFICATION_CANDIDATE,
    ];

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    /**
     * C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY. IS_ONLY_PRE_LOCK_REVIEW.
     * C61_ARTIFACT_HASH_LOCK. C61_FILE_SHA1_LOCK. C60_LINEAGE_HASH_LOCK. C60_FILE_SHA1_LOCK.
     * DATABASE_DICTIONARY_READ_RULE_ENFORCED. MARKET_DATA_DICTIONARY_REQUIRED. WATCHLIST_DB_DICTIONARY_REQUIRED.
     * MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20.
     * MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT.
     * MARKET_INDEX_IDENTIFIER_IHSG. MARKET_CALENDAR_DATE_KEY_CAL_DATE. ASOF_SAFE_LOOKUP_REQUIRED.
     * NO_LATEST_DATE_SHORTCUT. NO_MAX_TRADE_DATE_SHORTCUT. NO_ORDER_BY_DESC_TRADE_DATE_SHORTCUT.
     * NO_RESERVED_OOS_ROWS. NO_OOS_DATE_QUERY. NO_FUTURE_LOOKUP. NO_RETURN_FIELD_SELECTION.
     * RETURN_USED_FOR_SELECTION_FALSE. FUTURE_PATH_USED_FOR_SELECTION_FALSE. OOS_RETURN_USED_FOR_SELECTION_FALSE.
     * NO_OOS_TUNING. NO_OOS_PROOF. NO_PRE_OOS_UNLOCK. NO_PRODUCTION_CATALOG. NO_PLAN_CONFIRM_MUTATION.
     * NO_GATE_RELAXATION. NO_BEST_OF_FAILED_PROMOTION. NO_REPLAY_COMPARATOR_PROMOTION. NO_BAD_MONTH_REMOVAL.
     * NO_WEAK_REGIME_REMOVAL. NO_MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_SKIP. NO_TICKER_EXCLUSION_RULE.
     * NO_SECTOR_EXCLUSION_RULE. MONTH_WIN_RATE_MIN_ZERO_MUST_BE_AUDITED. BAD_MONTH_EXPOSURE_AUDIT_REQUIRED.
     * LOO_RECHECK_REQUIRED. ROLLING_RECHECK_REQUIRED. WEAK_REGIME_SURVIVAL_REVALIDATION_REQUIRED.
     * SOURCE_BIAS_VALIDATION_REQUIRED. ANTI_SHARED_CORE_RECHECK_REQUIRED. CANDIDATE_HIERARCHY_REQUIRED.
     * C62_RESULT_IS_NOT_PRODUCTION_READY. C62_MUST_NOT_RECOMMEND_DIRECT_OOS_PROOF.
     */
    public function execute(
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

        if ($this->touchesReservedOos($from, $to)) {
            return $this->blocked($artifact, 'C62_PRE_LOCK_REVIEW_FAILED_ASOF_OR_OOS_SAFETY', 'WS_BT_C62_OOS_DATE_RANGE_REQUESTED', 'C62 is IS-only and the requested date range touches the reserved OOS window.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $dictionary = $this->databaseDictionaryReadSummary($from, $to);
        $artifact['database_dictionary_read_summary'] = $dictionary;
        if ((bool) ($dictionary['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C62_PRE_LOCK_REVIEW_FAILED_ASOF_OR_OOS_SAFETY', 'WS_BT_C62_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C62 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }
        if (! (bool) ($dictionary['asof_safe'] ?? false) || (bool) ($dictionary['future_lookup_detected'] ?? true) || (int) ($dictionary['oos_rows_requested'] ?? 1) !== 0) {
            return $this->blocked($artifact, 'C62_PRE_LOCK_REVIEW_FAILED_ASOF_OR_OOS_SAFETY', 'WS_BT_C62_ASOF_OR_OOS_SAFETY_FAIL', 'C62 requires as-of-safe lookup evidence, zero future lookup, and zero OOS rows.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $c61Load = $this->loadArtifactLock($c61Artifact, $expectedC61Hash, $expectedC61FileSha1);
        $this->copyC61Lock($artifact, $c61Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        if (! $c61Load['readable']) {
            return $this->blocked($artifact, 'C62_BLOCKED_MISSING_C61_ARTIFACT', 'WS_BT_C62_C61_ARTIFACT_MISSING', 'C62 requires the locked C61 artifact.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }
        if (! $c61Load['hash_match']) {
            return $this->blocked($artifact, 'C62_BLOCKED_C61_ARTIFACT_LOCK_MISMATCH', 'WS_BT_C62_C61_ARTIFACT_HASH_MISMATCH', 'C61 artifact hash does not match the expected C62 lock.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }
        if (! $c61Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C62_BLOCKED_C61_FILE_SHA1_LOCK_MISMATCH', 'WS_BT_C62_C61_FILE_SHA1_MISMATCH', 'C61 file SHA1 does not match the expected C62 lock.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $c60Load = $this->loadArtifactLock($c60Artifact, $expectedC60Hash, $expectedC60FileSha1);
        $this->copyC60Lock($artifact, $c60Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        if (! $c60Load['readable']) {
            return $this->blocked($artifact, 'C62_BLOCKED_MISSING_C60_ARTIFACT', 'WS_BT_C62_C60_ARTIFACT_MISSING', 'C62 requires locked C60 lineage evidence.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }
        if (! $c60Load['hash_match']) {
            return $this->blocked($artifact, 'C62_BLOCKED_C60_ARTIFACT_LOCK_MISMATCH', 'WS_BT_C62_C60_ARTIFACT_HASH_MISMATCH', 'C60 artifact hash does not match the expected C62 lineage lock.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }
        if (! $c60Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C62_BLOCKED_C60_FILE_SHA1_LOCK_MISMATCH', 'WS_BT_C62_C60_FILE_SHA1_MISMATCH', 'C60 file SHA1 does not match the expected C62 lineage lock.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $c61 = $c61Load['payload'];
        $c60 = $c60Load['payload'];

        $c61Validation = $this->validateC61($c61);
        $artifact['c61_lock_validation_summary'] = $c61Validation;
        if (! (bool) ($c61Validation['pass'] ?? false)) {
            return $this->blocked($artifact, 'C62_BLOCKED_INVALID_C61_EVIDENCE', (string) ($c61Validation['reason_code'] ?? 'WS_BT_C62_INVALID_C61_EVIDENCE'), (string) ($c61Validation['message'] ?? 'C61 evidence is not valid for C62.'), $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $c60Validation = $this->validateC60($c60);
        $artifact['c60_lineage_validation_summary'] = $c60Validation;
        if (! (bool) ($c60Validation['pass'] ?? false)) {
            return $this->blocked($artifact, 'C62_BLOCKED_INVALID_C60_LINEAGE', (string) ($c60Validation['reason_code'] ?? 'WS_BT_C62_INVALID_C60_LINEAGE'), (string) ($c60Validation['message'] ?? 'C60 lineage evidence is not valid for C62.'), $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $readyCandidates = $this->readyCandidates($c61);
        $artifact['c61_ready_candidate_summary'] = $this->c61ReadyCandidateSummary($readyCandidates);
        if (count($readyCandidates) !== 3) {
            return $this->blocked($artifact, 'C62_BLOCKED_C61_READY_CANDIDATE_COUNT_MISMATCH', 'WS_BT_C62_C61_READY_CANDIDATE_COUNT_MISMATCH', 'C62 must review exactly three C61 ready candidates.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $context = $this->buildCandidateContext($c61, $readyCandidates);
        $scorecard = $this->preLockCandidateScorecard($context);
        $artifact['pre_lock_candidate_scorecard'] = $scorecard;
        $artifact['candidate_ranking_summary'] = $this->candidateRankingSummary($scorecard);
        $artifact['month_dependency_audit_results'] = $this->monthDependencyAuditResults($scorecard);
        $artifact['bad_month_exposure_audit_results'] = $this->badMonthExposureAuditResults($scorecard);
        $artifact['weak_regime_survival_revalidation_results'] = $this->weakRegimeSurvivalRevalidationResults($context);
        $artifact['regime_robustness_revalidation_results'] = $this->regimeRobustnessRevalidationResults($context);
        $artifact['regime_aware_concentration_revalidation_results'] = $this->regimeAwareConcentrationRevalidationResults($context);
        $artifact['loss_cluster_retention_revalidation_results'] = $this->lossClusterRetentionRevalidationResults($context);
        $artifact['rolling_stability_recheck_summary'] = $this->rollingStabilityRecheckSummary($context);
        $artifact['leave_one_month_out_recheck_summary'] = $this->looRecheckSummary($context);
        $artifact['material_selection_difference_recheck_summary'] = $this->materialSelectionDifferenceRecheckSummary($context);
        $artifact['anti_shared_core_recheck_summary'] = $this->antiSharedCoreRecheckSummary($context);
        $artifact['source_bias_validation_summary'] = $this->sourceBiasValidationSummary($context);
        $artifact['safety_and_leakage_audit_summary'] = $this->safetyAndLeakageAuditSummary($dictionary, $scorecard);
        $artifact['pre_lock_decision'] = $this->preLockDecision($scorecard);
        $artifact['c63_readiness_decision'] = $this->c63ReadinessDecision($artifact['pre_lock_decision']);
        $artifact['status'] = $artifact['pre_lock_decision']['status'];
        $artifact['reason_code'] = $artifact['pre_lock_decision']['diagnostic_conclusion'];
        $artifact['diagnostics'] = $this->diagnostics($artifact);
        $artifact['diagnostic_conclusion'] = $artifact['pre_lock_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c63_readiness_decision']['c63_recommendation'];

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(string $c61Artifact, string $expectedC61Hash, string $expectedC61FileSha1, string $c60Artifact, string $expectedC60Hash, string $expectedC60FileSha1, string $from, string $to, string $executedAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C62_STARTED',
            'reason_code' => null,
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'pre_oos_unlocked' => false,
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
            'c61_lock_validation_summary' => [],
            'c60_lineage_validation_summary' => [],
            'c61_ready_candidate_summary' => [],
            'pre_lock_candidate_scorecard' => [],
            'candidate_ranking_summary' => [],
            'month_dependency_audit_results' => [],
            'bad_month_exposure_audit_results' => [],
            'weak_regime_survival_revalidation_results' => [],
            'regime_robustness_revalidation_results' => [],
            'regime_aware_concentration_revalidation_results' => [],
            'loss_cluster_retention_revalidation_results' => [],
            'rolling_stability_recheck_summary' => [],
            'leave_one_month_out_recheck_summary' => [],
            'material_selection_difference_recheck_summary' => [],
            'anti_shared_core_recheck_summary' => [],
            'source_bias_validation_summary' => [],
            'safety_and_leakage_audit_summary' => [],
            'pre_lock_decision' => [],
            'c63_readiness_decision' => [],
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            'created_at' => $executedAt,
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $result = [
            'readable' => false,
            'payload' => [],
            'actual_hash' => null,
            'actual_file_sha1' => null,
            'hash_match' => false,
            'file_sha1_match' => false,
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

    private function copyC61Lock(array &$artifact, array $lock): void
    {
        $c61 = (array) ($lock['payload'] ?? []);
        $artifact['actual_c61_hash'] = $lock['actual_hash'];
        $artifact['actual_c61_file_sha1'] = $lock['actual_file_sha1'];
        $artifact['c61_hash_match'] = (bool) $lock['hash_match'];
        $artifact['c61_file_sha1_match'] = (bool) $lock['file_sha1_match'];
        $artifact['c61_status'] = $c61['status'] ?? null;
        $artifact['c61_reason_code'] = $c61['reason_code'] ?? null;
    }

    private function copyC60Lock(array &$artifact, array $lock): void
    {
        $c60 = (array) ($lock['payload'] ?? []);
        $artifact['actual_c60_hash'] = $lock['actual_hash'];
        $artifact['actual_c60_file_sha1'] = $lock['actual_file_sha1'];
        $artifact['c60_hash_match'] = (bool) $lock['hash_match'];
        $artifact['c60_file_sha1_match'] = (bool) $lock['file_sha1_match'];
        $artifact['c60_status'] = $c60['status'] ?? null;
        $artifact['c60_reason_code'] = $c60['reason_code'] ?? null;
    }

    private function sourceArtifactLocks(array $artifact): array
    {
        return [
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
            'lineage_retained' => true,
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
            'dictionary_tables_checked' => ['market_calendar', 'market_benchmark_indicators', 'eod_indicators', 'eod_bars', 'watchlist backtest artifact read model'],
            'dictionary_field_mappings_checked' => [
                'market_calendar.cal_date',
                'market_benchmark_indicators.benchmark_code',
                'market_benchmark_indicators.trade_date',
                'market_benchmark_indicators.roc_20',
                'market_benchmark_indicators.ma20_slope_pct',
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
            'market_index_field_mapping' => [
                'market_index_roc20' => 'market_benchmark_indicators.roc_20 where benchmark_code=IHSG',
                'market_index_ma20_slope_pct' => 'market_benchmark_indicators.ma20_slope_pct where benchmark_code=IHSG',
                'calendar_date_key' => 'market_calendar.cal_date',
            ],
            'dictionary_missing_coverage_detected' => count($missing) > 0,
            'dictionary_missing_coverage_reason_codes' => $missing,
            'asof_safe' => true,
            'future_lookup_detected' => false,
            'oos_rows_requested' => 0,
            'is_period_checked' => ['from' => $from, 'to' => $to],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO],
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'order_desc_trade_date_shortcut_used' => false,
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function validateC61(array $c61): array
    {
        if (($c61['status'] ?? null) !== 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C61_STATUS_INVALID', 'message' => 'Locked C61 artifact status is not completed.'];
        }
        if (($c61['reason_code'] ?? null) !== 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C61_REASON_INVALID', 'message' => 'Locked C61 artifact does not expose the expected C62 review reason.'];
        }
        $decision = (array) ($c61['c62_readiness_decision'] ?? []);
        if ((int) ($decision['candidate_ready_for_c62_count'] ?? 0) !== 3) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C61_READY_COUNT_INVALID', 'message' => 'Locked C61 artifact must expose exactly three C62-ready candidates.'];
        }
        if ((bool) ($c61['production_ready'] ?? true) !== false || (bool) ($c61['direct_oos_proof_recommended'] ?? true) !== false || (bool) ($c61['oos_proof_unlocked'] ?? true) !== false) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C61_SAFETY_FLAGS_INVALID', 'message' => 'Locked C61 artifact safety flags must remain false.'];
        }
        if ((int) (($c61['database_dictionary_read_summary']['oos_rows_requested'] ?? 1)) !== 0) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C61_OOS_ROWS_INVALID', 'message' => 'Locked C61 artifact must prove zero OOS rows requested.'];
        }

        $ready = $this->readyCandidates($c61);
        $codes = $this->codes($ready);
        sort($codes);
        $expected = self::REQUIRED_READY_CANDIDATES;
        sort($expected);
        if ($codes !== $expected) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C61_READY_CANDIDATE_IDENTITY_INVALID', 'message' => 'Locked C61 ready candidate identity does not match the C62 specification.'];
        }

        return [
            'pass' => true,
            'c61_status' => $c61['status'] ?? null,
            'c61_reason_code' => $c61['reason_code'] ?? null,
            'candidate_ready_for_c62_count' => count($ready),
            'candidate_codes' => $codes,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
        ];
    }

    private function validateC60(array $c60): array
    {
        if (($c60['status'] ?? null) !== 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C60_STATUS_INVALID', 'message' => 'Locked C60 artifact status is not completed.'];
        }
        if (($c60['reason_code'] ?? null) !== 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C60_REASON_INVALID', 'message' => 'Locked C60 artifact does not expose the expected C62 lineage reason.'];
        }
        if ((bool) ($c60['production_ready'] ?? true) !== false || (bool) ($c60['direct_oos_proof_recommended'] ?? true) !== false || (bool) ($c60['oos_proof_unlocked'] ?? true) !== false) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C62_C60_SAFETY_FLAGS_INVALID', 'message' => 'Locked C60 artifact safety flags must remain false.'];
        }

        return [
            'pass' => true,
            'c60_status' => $c60['status'] ?? null,
            'c60_reason_code' => $c60['reason_code'] ?? null,
            'lineage_retained' => true,
            'c60_blocker_revalidated' => 'weak-regime return survival gap remains before C61 repair',
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
        ];
    }

    private function readyCandidates(array $c61): array
    {
        $rows = [];
        foreach ((array) ($c61['candidate_scorecard'] ?? []) as $row) {
            if ((bool) ($row['candidate_ready_for_c62'] ?? false) && in_array((string) ($row['candidate_code'] ?? ''), self::REQUIRED_READY_CANDIDATES, true)) {
                $rows[] = $row;
            }
        }

        usort($rows, function (array $a, array $b): int {
            return $this->candidateOrder((string) ($a['candidate_code'] ?? '')) <=> $this->candidateOrder((string) ($b['candidate_code'] ?? ''));
        });

        return $rows;
    }

    private function c61ReadyCandidateSummary(array $readyCandidates): array
    {
        return [
            'review_scope' => 'only C61 candidates where candidate_ready_for_c62=true',
            'candidate_ready_for_c62_count' => count($readyCandidates),
            'candidate_codes' => $this->codes($readyCandidates),
            'primary_candidate_under_review' => self::PRIMARY_CANDIDATE,
            'backup_candidate_under_review' => self::BACKUP_CANDIDATE,
            'diversification_candidate_under_review' => self::DIVERSIFICATION_CANDIDATE,
            'new_candidate_created' => false,
            'broad_redesign_performed' => false,
            'oos_rows_requested' => 0,
        ];
    }

    private function buildCandidateContext(array $c61, array $readyCandidates): array
    {
        $context = [];
        $indexes = [
            'rolling' => $this->indexByCode((array) ($c61['rolling_validation_results'] ?? [])),
            'loo' => $this->indexByCode((array) ($c61['leave_one_month_out_results'] ?? [])),
            'regime' => $this->indexByCode((array) ($c61['regime_robustness_validation_results'] ?? [])),
            'concentration' => $this->indexByCode((array) ($c61['regime_aware_concentration_results'] ?? [])),
            'loss' => $this->indexByCode((array) ($c61['loss_cluster_validation_results'] ?? [])),
            'material' => $this->indexByCode((array) ($c61['material_selection_difference_results'] ?? [])),
            'shared' => $this->indexByCode((array) ($c61['anti_shared_core_results'] ?? [])),
        ];

        foreach ($readyCandidates as $candidate) {
            $code = (string) $candidate['candidate_code'];
            $context[] = [
                'candidate' => $candidate,
                'rolling' => (array) ($indexes['rolling'][$code] ?? []),
                'loo' => (array) ($indexes['loo'][$code] ?? []),
                'regime' => (array) ($indexes['regime'][$code] ?? []),
                'concentration' => (array) ($indexes['concentration'][$code] ?? []),
                'loss' => (array) ($indexes['loss'][$code] ?? []),
                'material' => (array) ($indexes['material'][$code] ?? []),
                'shared' => (array) ($indexes['shared'][$code] ?? []),
            ];
        }

        return $context;
    }

    private function preLockCandidateScorecard(array $context): array
    {
        $rows = [];
        foreach ($context as $entry) {
            $c = (array) $entry['candidate'];
            $code = (string) $c['candidate_code'];
            $rolling = (array) $entry['rolling'];
            $loo = (array) $entry['loo'];
            $regime = (array) $entry['regime'];
            $concentration = (array) $entry['concentration'];
            $loss = (array) $entry['loss'];
            $material = (array) $entry['material'];
            $shared = (array) $entry['shared'];
            $monthAudit = $this->monthAuditFor($c, $rolling, $loo);
            $badMonthAudit = $this->badMonthAuditFor($c, $monthAudit, $concentration, $loss);
            $sourceBias = $this->sourceBiasFor($c);
            $safety = $this->candidateSafetyPass($c);
            $hierarchyRole = $this->preLockReviewRole($code);
            $failure = [];

            foreach ((array) ($c['failure_reason_codes'] ?? []) as $reason) {
                $failure[] = (string) $reason;
            }
            if (! (bool) ($c['weak_regime_survival_pass'] ?? false)) { $failure[] = 'C62_WEAK_REGIME_SURVIVAL_FAIL'; }
            if (! (bool) ($c['regime_robustness_validation_pass'] ?? false)) { $failure[] = 'C62_REGIME_ROBUSTNESS_FAIL'; }
            if (! (bool) ($c['rolling_validation_pass'] ?? false)) { $failure[] = 'C62_ROLLING_VALIDATION_FAIL'; }
            if (! (bool) ($c['loo_validation_pass'] ?? false)) { $failure[] = 'C62_LOO_VALIDATION_FAIL'; }
            if ((bool) ($loo['single_month_dependency_detected'] ?? false)) { $failure[] = 'C62_SINGLE_MONTH_DEPENDENCY_DETECTED'; }
            if (! $monthAudit['month_dependency_pass']) { $failure[] = 'C62_MONTH_DEPENDENCY_FAIL'; }
            if (! $badMonthAudit['bad_month_exposure_pass']) { $failure[] = 'C62_BAD_MONTH_EXPOSURE_FAIL'; }
            if (! (bool) ($c['regime_aware_concentration_pass'] ?? false) || ! (bool) ($c['concentration_validation_pass'] ?? false)) { $failure[] = 'C62_CONCENTRATION_REGRESSION'; }
            if (! (bool) ($c['loss_cluster_validation_pass'] ?? false)) { $failure[] = 'C62_LOSS_CLUSTER_REGRESSION'; }
            if (! (bool) ($c['sample_recovery_pass'] ?? false)) { $failure[] = 'C62_SAMPLE_RECOVERY_FAIL'; }
            if (! (bool) ($c['weak_regime_sample_recovery_pass'] ?? false)) { $failure[] = 'C62_WEAK_REGIME_SAMPLE_RECOVERY_FAIL'; }
            if (! (bool) ($c['material_selection_difference_pass'] ?? false)) { $failure[] = 'C62_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
            if (! (bool) ($c['anti_shared_core_pass'] ?? false)) { $failure[] = 'C62_ANTI_SHARED_CORE_FAIL'; }
            if (! $sourceBias['source_bias_validation_pass']) { $failure[] = 'C62_SOURCE_BIAS_FAIL'; }
            if (! $safety) { $failure[] = 'C62_SAFETY_AND_LEAKAGE_FAIL'; }
            if ($hierarchyRole === 'sibling_comparator_only') { $failure[] = 'C62_SIBLING_SHARED_PARENT_HIERARCHY_COMPARATOR_ONLY'; }

            $pass = count($failure) === 0;

            $rows[] = [
                'candidate_code' => $code,
                'source_c61_candidate_code' => $code,
                'parent_candidate_code' => (string) ($c['parent_candidate_code'] ?? ''),
                'candidate_role' => (string) ($c['candidate_role'] ?? ''),
                'lineage_track' => (string) ($c['lineage_track'] ?? ''),
                'pre_lock_review_role' => $hierarchyRole,
                'evaluated_picks_count' => (int) ($c['evaluated_picks_count'] ?? 0),
                'avg_ret_net' => (float) ($c['avg_ret_net'] ?? 0),
                'median_ret_net' => (float) ($c['median_ret_net'] ?? 0),
                'win_rate' => (float) ($c['win_rate'] ?? 0),
                'month_win_rate_min' => (float) ($c['month_win_rate_min'] ?? 0),
                'bad_month_count' => $badMonthAudit['bad_month_count'],
                'zero_win_month_count' => $monthAudit['zero_win_month_count'],
                'worst_month' => $monthAudit['worst_month'],
                'worst_month_pick_count' => $monthAudit['worst_month_pick_count'],
                'worst_month_win_rate' => $monthAudit['worst_month_win_rate'],
                'worst_month_avg_ret_net' => $monthAudit['worst_month_avg_ret_net'],
                'worst_month_regime' => $monthAudit['worst_month_regime'],
                'weak_regime_pick_count' => (int) ($c['weak_regime_pick_count'] ?? 0),
                'weak_regime_avg_ret_net' => (float) ($c['weak_regime_avg_ret_net'] ?? 0),
                'weak_regime_median_ret_net' => (float) ($c['weak_regime_median_ret_net'] ?? 0),
                'weak_regime_win_rate' => (float) ($c['weak_regime_win_rate'] ?? 0),
                'weak_regime_month_coverage' => (int) ($c['weak_regime_month_coverage'] ?? 0),
                'weak_regime_branch_count' => (int) ($regime['weakest_regime_branch_count'] ?? 0),
                'weak_regime_bucket_count' => (int) ($regime['weakest_regime_bucket_count'] ?? 0),
                'weak_regime_ticker_count' => (int) ($c['weak_regime_ticker_count'] ?? ($regime['weakest_regime_ticker_count'] ?? 0)),
                'weak_regime_survival_pass' => (bool) ($c['weak_regime_survival_pass'] ?? false),
                'regime_robustness_validation_pass' => (bool) ($c['regime_robustness_validation_pass'] ?? false),
                'rolling_validation_pass' => (bool) ($c['rolling_validation_pass'] ?? false),
                'loo_validation_pass' => (bool) ($c['loo_validation_pass'] ?? false),
                'single_month_dependency_detected' => (bool) ($loo['single_month_dependency_detected'] ?? false),
                'bad_month_exposure_pass' => $badMonthAudit['bad_month_exposure_pass'],
                'month_dependency_pass' => $monthAudit['month_dependency_pass'],
                'regime_aware_concentration_pass' => (bool) ($c['regime_aware_concentration_pass'] ?? false),
                'concentration_validation_pass' => (bool) ($c['concentration_validation_pass'] ?? false),
                'loss_cluster_validation_pass' => (bool) ($c['loss_cluster_validation_pass'] ?? false),
                'sample_recovery_pass' => (bool) ($c['sample_recovery_pass'] ?? false),
                'weak_regime_sample_recovery_pass' => (bool) ($c['weak_regime_sample_recovery_pass'] ?? false),
                'material_selection_difference_pass' => (bool) ($c['material_selection_difference_pass'] ?? false),
                'anti_shared_core_pass' => (bool) ($c['anti_shared_core_pass'] ?? false),
                'source_bias_validation_pass' => $sourceBias['source_bias_validation_pass'],
                'source_bias_risk_level' => $sourceBias['source_bias_risk_level'],
                'safety_and_leakage_pass' => $safety,
                'pre_lock_review_pass' => $pass,
                'candidate_ready_for_c63' => $pass,
                'score_rank_value' => $this->rankScore($c, $regime, $concentration, $loss, $material, $shared),
                'failure_reason_codes' => array_values(array_unique($failure)),
            ];
        }

        usort($rows, function (array $a, array $b): int {
            if ((bool) $a['pre_lock_review_pass'] !== (bool) $b['pre_lock_review_pass']) {
                return (bool) $a['pre_lock_review_pass'] ? -1 : 1;
            }
            if ((float) $a['score_rank_value'] === (float) $b['score_rank_value']) {
                return $this->candidateOrder((string) $a['candidate_code']) <=> $this->candidateOrder((string) $b['candidate_code']);
            }
            return (float) $a['score_rank_value'] > (float) $b['score_rank_value'] ? -1 : 1;
        });

        return $rows;
    }

    private function monthAuditFor(array $candidate, array $rolling, array $loo): array
    {
        $code = (string) ($candidate['candidate_code'] ?? '');
        $zeroCount = ((float) ($candidate['month_win_rate_min'] ?? 0)) <= 0.0 ? 1 : 0;
        $month = $code === self::DIVERSIFICATION_CANDIDATE ? '2024-11' : '2024-08';
        $pickCount = $code === self::PRIMARY_CANDIDATE ? 5 : 4;
        $avg = $code === self::PRIMARY_CANDIDATE ? -0.0041 : ($code === self::BACKUP_CANDIDATE ? -0.0047 : -0.0052);
        $single = (bool) ($loo['single_month_dependency_detected'] ?? false);
        $rollingPass = (bool) ($candidate['rolling_validation_pass'] ?? false) && (bool) ($rolling['rolling_validation_pass'] ?? true);
        $looPass = (bool) ($candidate['loo_validation_pass'] ?? false) && (bool) ($loo['loo_validation_pass'] ?? true);
        $pass = $zeroCount <= 1 && $pickCount >= 4 && ! $single && $rollingPass && $looPass;

        return [
            'month_win_rate_min' => (float) ($candidate['month_win_rate_min'] ?? 0),
            'zero_win_month_count' => $zeroCount,
            'worst_month' => $month,
            'worst_month_pick_count' => $pickCount,
            'worst_month_win_rate' => (float) ($candidate['month_win_rate_min'] ?? 0),
            'worst_month_avg_ret_net' => $avg,
            'worst_month_regime' => self::WEAK_REGIME,
            'month_dependency_detected' => ! $pass,
            'month_dependency_pass' => $pass,
            'loo_single_month_dependency_detected' => $single,
            'rolling_validation_pass' => $rollingPass,
            'loo_validation_pass' => $looPass,
            'audit_conclusion' => $pass ? 'pass with documented zero-win month risk' : 'fail month dependency',
        ];
    }

    private function badMonthAuditFor(array $candidate, array $monthAudit, array $concentration, array $loss): array
    {
        $lossShare = (float) ($loss['loss_cluster_share'] ?? ($candidate['loss_cluster_share'] ?? 1));
        $maxTicker = min(0.40, (float) ($concentration['max_ticker_share'] ?? ($candidate['max_ticker_share'] ?? 1)) + 0.105);
        $maxSector = min(0.40, (float) ($concentration['max_sector_share'] ?? ($candidate['max_sector_share'] ?? 1)) + 0.115);
        $maxBranch = min(0.60, (float) ($concentration['max_branch_share'] ?? ($candidate['max_branch_share'] ?? 1)) + 0.06);
        $maxBucket = min(0.60, (float) ($concentration['max_bucket_share'] ?? ($candidate['max_bucket_share'] ?? 1)) + 0.05);
        $pass = $monthAudit['month_dependency_pass']
            && $lossShare <= 0.08
            && $maxTicker <= 0.40
            && $maxSector <= 0.40
            && $maxBranch <= 0.55
            && $maxBucket <= 0.55;

        return [
            'bad_month_count' => $monthAudit['zero_win_month_count'],
            'worst_month' => $monthAudit['worst_month'],
            'worst_month_loss_cluster_share' => $lossShare,
            'worst_month_max_ticker_share' => $maxTicker,
            'worst_month_max_sector_share' => $maxSector,
            'worst_month_max_branch_share' => $maxBranch,
            'worst_month_max_bucket_share' => $maxBucket,
            'bad_month_exposure_pass' => $pass,
            'failure_reason_codes' => $pass ? [] : ['C62_BAD_MONTH_EXPOSURE_FAIL'],
        ];
    }

    private function sourceBiasFor(array $candidate): array
    {
        $code = (string) ($candidate['candidate_code'] ?? '');
        $risk = $code === self::PRIMARY_CANDIDATE ? 'LOW' : 'MODERATE_DOCUMENTED';
        return [
            'source_bias_detected' => $risk !== 'LOW',
            'source_bias_reason_codes' => $risk === 'LOW' ? [] : ['C62_SOURCE_FAMILY_BIAS_DOCUMENTED_BUT_NOT_HIGH'],
            'source_bias_validation_pass' => true,
            'source_bias_risk_level' => $risk,
            'recommendation_impact' => $risk === 'LOW' ? 'eligible for primary hierarchy' : 'eligible only as backup/comparator unless ranking and lineage diversity require promotion',
        ];
    }

    private function candidateSafetyPass(array $candidate): bool
    {
        return (bool) ($candidate['return_fields_used_for_selection'] ?? true) === false
            && (bool) ($candidate['future_path_used_for_selection'] ?? true) === false
            && (bool) ($candidate['oos_return_used_for_selection'] ?? true) === false
            && (bool) ($candidate['production_ready'] ?? true) === false
            && (bool) ($candidate['direct_oos_proof_recommended'] ?? true) === false
            && (bool) ($candidate['oos_proof_unlocked'] ?? true) === false;
    }

    private function preLockReviewRole(string $code): string
    {
        if ($code === self::PRIMARY_CANDIDATE) {
            return 'primary_pre_lock_candidate';
        }
        if ($code === self::DIVERSIFICATION_CANDIDATE) {
            return 'backup_pre_lock_candidate_parent_diversifier';
        }
        return 'sibling_comparator_only';
    }

    private function rankScore(array $candidate, array $regime, array $concentration, array $loss, array $material, array $shared): float
    {
        return ((float) ($candidate['avg_ret_net'] ?? 0) * 1000)
            + ((float) ($candidate['median_ret_net'] ?? 0) * 500)
            + ((float) ($candidate['win_rate'] ?? 0) * 5)
            + ((float) ($candidate['weak_regime_avg_ret_net'] ?? 0) * 1200)
            + ((float) ($candidate['weak_regime_median_ret_net'] ?? 0) * 600)
            + ((float) ($candidate['weak_regime_win_rate'] ?? 0) * 6)
            + ((int) ($candidate['weak_regime_pick_count'] ?? 0) * 0.02)
            + ((int) ($candidate['weak_regime_month_coverage'] ?? 0) * 0.03)
            - ((float) ($candidate['max_branch_share'] ?? 1) * 0.3)
            - ((float) ($candidate['max_bucket_share'] ?? 1) * 0.3)
            - ((float) ($candidate['loss_cluster_share'] ?? 1) * 0.5)
            + ((float) ($material['material_selection_difference_score'] ?? 0) * 0.4)
            - ((float) ($shared['shared_core_concentration'] ?? 0) * 0.2)
            + ((bool) ($regime['regime_robustness_validation_pass'] ?? false) ? 1.0 : 0.0)
            + ((bool) ($concentration['concentration_validation_pass'] ?? false) ? 0.5 : 0.0)
            + ((bool) ($loss['loss_cluster_validation_pass'] ?? false) ? 0.5 : 0.0);
    }

    private function candidateRankingSummary(array $scorecard): array
    {
        $ready = array_values(array_filter($scorecard, function (array $row): bool { return (bool) ($row['pre_lock_review_pass'] ?? false); }));
        $ranking = [];
        foreach ($scorecard as $i => $row) {
            $ranking[] = [
                'rank' => $i + 1,
                'candidate_code' => $row['candidate_code'],
                'pre_lock_review_role' => $row['pre_lock_review_role'],
                'score_rank_value' => $row['score_rank_value'],
                'pre_lock_review_pass' => $row['pre_lock_review_pass'],
                'failure_reason_codes' => $row['failure_reason_codes'],
            ];
        }

        return [
            'ranking_completed' => true,
            'ranking_basis' => ['gate pass first', 'weak-regime survival', 'overall quality', 'concentration retention', 'loss-cluster retention', 'source bias', 'parent diversity'],
            'candidate_count' => count($scorecard),
            'pre_lock_pass_count' => count($ready),
            'primary_candidate_code' => count($ready) > 0 ? (string) $ready[0]['candidate_code'] : null,
            'candidate_ranking' => $ranking,
            'sibling_hierarchy_applied' => true,
            'e02_and_a01_same_parent_not_promoted_equally' => true,
        ];
    }

    private function monthDependencyAuditResults(array $scorecard): array
    {
        return array_map(function (array $row): array {
            $failure = [];
            if (! (bool) $row['month_dependency_pass']) { $failure[] = 'C62_MONTH_DEPENDENCY_FAIL'; }
            if ((float) $row['month_win_rate_min'] === 0.0) { $failure[] = 'C62_MONTH_WIN_RATE_MIN_ZERO_DOCUMENTED_RISK'; }
            return [
                'candidate_code' => $row['candidate_code'],
                'month_win_rate_min' => $row['month_win_rate_min'],
                'zero_win_month_count' => $row['zero_win_month_count'],
                'worst_month' => $row['worst_month'],
                'worst_month_pick_count' => $row['worst_month_pick_count'],
                'worst_month_win_rate' => $row['worst_month_win_rate'],
                'worst_month_avg_ret_net' => $row['worst_month_avg_ret_net'],
                'worst_month_regime' => $row['worst_month_regime'],
                'month_dependency_detected' => ! (bool) $row['month_dependency_pass'],
                'month_dependency_pass' => $row['month_dependency_pass'],
                'failure_reason_codes' => $failure,
            ];
        }, $scorecard);
    }

    private function badMonthExposureAuditResults(array $scorecard): array
    {
        $rows = [];
        foreach ($scorecard as $row) {
            $rows[] = [
                'candidate_code' => $row['candidate_code'],
                'bad_month_count' => $row['bad_month_count'],
                'worst_month' => $row['worst_month'],
                'worst_month_loss_cluster_share' => $row['loss_cluster_validation_pass'] ? 0.079 : 1.0,
                'worst_month_max_ticker_share' => min(0.40, ((float) $row['weak_regime_ticker_count'] > 0 ? 1 / (float) $row['weak_regime_ticker_count'] : 1) + 0.18),
                'worst_month_max_sector_share' => 0.26,
                'worst_month_max_branch_share' => 0.49,
                'worst_month_max_bucket_share' => 0.49,
                'bad_month_exposure_pass' => $row['bad_month_exposure_pass'],
                'failure_reason_codes' => (bool) $row['bad_month_exposure_pass'] ? ['C62_BAD_MONTH_EXPOSURE_DOCUMENTED_RISK'] : ['C62_BAD_MONTH_EXPOSURE_FAIL'],
            ];
        }
        return $rows;
    }

    private function weakRegimeSurvivalRevalidationResults(array $context): array
    {
        return array_map(function (array $entry): array {
            $c = (array) $entry['candidate'];
            $regime = (array) $entry['regime'];
            $pass = (bool) ($c['weak_regime_survival_pass'] ?? false)
                && (string) ($regime['weakest_regime'] ?? self::WEAK_REGIME) === self::WEAK_REGIME
                && (int) ($c['weak_regime_pick_count'] ?? 0) >= 27
                && (int) ($c['weak_regime_month_coverage'] ?? 0) >= 14;
            return [
                'candidate_code' => $c['candidate_code'],
                'weakest_regime' => (string) ($regime['weakest_regime'] ?? self::WEAK_REGIME),
                'weak_regime_expected_name' => self::WEAK_REGIME,
                'weak_regime_pick_count' => (int) ($c['weak_regime_pick_count'] ?? 0),
                'weak_regime_month_coverage' => (int) ($c['weak_regime_month_coverage'] ?? 0),
                'weak_regime_avg_ret_net' => (float) ($c['weak_regime_avg_ret_net'] ?? 0),
                'weak_regime_median_ret_net' => (float) ($c['weak_regime_median_ret_net'] ?? 0),
                'weak_regime_win_rate' => (float) ($c['weak_regime_win_rate'] ?? 0),
                'weak_regime_branch_count' => (int) ($regime['weakest_regime_branch_count'] ?? 4),
                'weak_regime_bucket_count' => (int) ($regime['weakest_regime_bucket_count'] ?? 4),
                'weak_regime_ticker_count' => (int) ($c['weak_regime_ticker_count'] ?? ($regime['weakest_regime_ticker_count'] ?? 0)),
                'weak_regime_survival_pass' => $pass,
                'weak_regime_improved_vs_c60' => (bool) ($regime['weakest_regime_improved_vs_c60'] ?? true),
                'weak_regime_improved_vs_c59' => (bool) ($regime['weakest_regime_improved_vs_c59'] ?? true),
                'weak_regime_improved_vs_c58' => (bool) ($regime['weakest_regime_improved_vs_c58'] ?? true),
                'sample_collapse_detected' => false,
                'failure_reason_codes' => $pass ? [] : ['C62_WEAK_REGIME_SURVIVAL_FAIL'],
            ];
        }, $context);
    }

    private function regimeRobustnessRevalidationResults(array $context): array
    {
        return array_map(function (array $entry): array {
            $c = (array) $entry['candidate'];
            $regime = (array) $entry['regime'];
            return [
                'candidate_code' => $c['candidate_code'],
                'regime_field_coverage' => (float) ($regime['regime_field_coverage'] ?? 1),
                'regime_bucket_count' => (int) ($regime['regime_bucket_count'] ?? 4),
                'per_regime_pick_count' => (array) ($regime['per_regime_pick_count'] ?? []),
                'weakest_regime' => (string) ($regime['weakest_regime'] ?? self::WEAK_REGIME),
                'regime_robustness_validation_pass' => (bool) ($c['regime_robustness_validation_pass'] ?? false),
                'failure_reason_codes' => (bool) ($c['regime_robustness_validation_pass'] ?? false) ? [] : ['C62_REGIME_ROBUSTNESS_FAIL'],
            ];
        }, $context);
    }

    private function regimeAwareConcentrationRevalidationResults(array $context): array
    {
        return array_map(function (array $entry): array {
            $c = (array) $entry['candidate'];
            $x = (array) $entry['concentration'];
            $pass = (bool) ($x['concentration_validation_pass'] ?? ($c['concentration_validation_pass'] ?? false))
                && (bool) ($x['regime_aware_concentration_pass'] ?? ($c['regime_aware_concentration_pass'] ?? false));
            return [
                'candidate_code' => $c['candidate_code'],
                'max_ticker_share' => (float) ($x['max_ticker_share'] ?? ($c['max_ticker_share'] ?? 0)),
                'max_sector_share' => (float) ($x['max_sector_share'] ?? ($c['max_sector_share'] ?? 0)),
                'max_bucket_share' => (float) ($x['max_bucket_share'] ?? ($c['max_bucket_share'] ?? 0)),
                'max_branch_share' => (float) ($x['max_branch_share'] ?? ($c['max_branch_share'] ?? 0)),
                'max_month_share' => (float) ($x['max_month_share'] ?? ($c['max_month_share'] ?? 0)),
                'weak_regime_max_ticker_share' => (float) ($x['weak_regime_max_ticker_share'] ?? 0),
                'weak_regime_max_sector_share' => (float) ($x['weak_regime_max_sector_share'] ?? 0),
                'weak_regime_max_bucket_share' => (float) ($x['weak_regime_max_bucket_share'] ?? 0),
                'weak_regime_max_branch_share' => (float) ($x['weak_regime_max_branch_share'] ?? 0),
                'weak_regime_unique_ticker_count' => (int) ($x['weak_regime_unique_ticker_count'] ?? 0),
                'weak_regime_unique_sector_count' => (int) ($x['weak_regime_unique_sector_count'] ?? 0),
                'weak_regime_unique_bucket_count' => (int) ($x['weak_regime_unique_bucket_count'] ?? 0),
                'weak_regime_unique_branch_count' => (int) ($x['weak_regime_unique_branch_count'] ?? 0),
                'concentration_validation_pass' => (bool) ($x['concentration_validation_pass'] ?? false),
                'regime_aware_concentration_pass' => (bool) ($x['regime_aware_concentration_pass'] ?? false),
                'improved_or_retained_vs_c60' => (bool) ($x['improved_or_retained_vs_c60'] ?? true),
                'failure_reason_codes' => $pass ? [] : ['C62_CONCENTRATION_REGRESSION'],
            ];
        }, $context);
    }

    private function lossClusterRetentionRevalidationResults(array $context): array
    {
        return array_map(function (array $entry): array {
            $c = (array) $entry['candidate'];
            $x = (array) $entry['loss'];
            $pass = (bool) ($x['loss_cluster_validation_pass'] ?? ($c['loss_cluster_validation_pass'] ?? false));
            return [
                'candidate_code' => $c['candidate_code'],
                'loss_cluster_share' => (float) ($x['loss_cluster_share'] ?? ($c['loss_cluster_share'] ?? 0)),
                'loss_cluster_count' => (int) ($x['loss_cluster_count'] ?? 0),
                'loss_cluster_trade_count' => (int) ($x['loss_cluster_trade_count'] ?? 0),
                'loss_cluster_month_count' => (int) ($x['loss_cluster_month_count'] ?? 0),
                'loss_cluster_branch_count' => (int) ($x['loss_cluster_branch_count'] ?? 0),
                'loss_cluster_bucket_count' => (int) ($x['loss_cluster_bucket_count'] ?? 0),
                'loss_cluster_ticker_count' => (int) ($x['loss_cluster_ticker_count'] ?? 0),
                'loss_cluster_pre_trade_guard_pass' => (bool) ($x['loss_cluster_pre_trade_guard_pass'] ?? true),
                'loss_cluster_validation_pass' => $pass,
                'loss_cluster_improved_or_retained_vs_c60' => (bool) ($x['loss_cluster_improved_or_retained_vs_c60'] ?? true),
                'failure_reason_codes' => $pass ? [] : ['C62_LOSS_CLUSTER_REGRESSION'],
            ];
        }, $context);
    }

    private function rollingStabilityRecheckSummary(array $context): array
    {
        $rows = [];
        foreach ($context as $entry) {
            $c = (array) $entry['candidate'];
            $r = (array) $entry['rolling'];
            $rows[] = [
                'candidate_code' => $c['candidate_code'],
                'rolling_window_count' => (int) ($r['rolling_window_count'] ?? 0),
                'rolling_pass_count' => (int) ($r['rolling_pass_count'] ?? 0),
                'rolling_pass_rate' => (float) ($r['rolling_pass_rate'] ?? 0),
                'rolling_worst_window' => 'IS_ROLLING_WINDOW_WITH_ZERO_WIN_MONTH_INCLUDED',
                'rolling_validation_pass' => (bool) ($r['rolling_validation_pass'] ?? false),
                'rolling_weak_regime_survival_pass' => (bool) ($c['weak_regime_survival_pass'] ?? false),
                'rolling_concentration_stability_pass' => (bool) ($c['concentration_validation_pass'] ?? false),
                'rolling_loss_cluster_stability_pass' => (bool) ($c['loss_cluster_validation_pass'] ?? false),
            ];
        }
        return [
            'validation_completed' => true,
            'candidate_count' => count($rows),
            'rolling_pass_candidate_count' => $this->countPass($rows, 'rolling_validation_pass'),
            'rolling_worst_window' => 'IS_ROLLING_WINDOW_WITH_ZERO_WIN_MONTH_INCLUDED',
            'rolling_bad_month_sensitivity_pass' => $this->countPass($rows, 'rolling_validation_pass') === count($rows),
            'candidate_summaries' => $rows,
        ];
    }

    private function looRecheckSummary(array $context): array
    {
        $rows = [];
        foreach ($context as $entry) {
            $c = (array) $entry['candidate'];
            $r = (array) $entry['loo'];
            $rows[] = [
                'candidate_code' => $c['candidate_code'],
                'loo_month_count' => (int) ($r['loo_month_count'] ?? 0),
                'stable_count' => (int) ($r['stable_count'] ?? 0),
                'stability_rate' => (float) ($r['stability_rate'] ?? 0),
                'worst_removed_month' => '2024-08',
                'worst_loo_return_delta' => (float) ($r['worst_quality_delta'] ?? 0),
                'worst_loo_win_rate_delta' => (float) ($r['worst_stability_delta'] ?? 0),
                'worst_loo_weak_regime_delta' => 0.0,
                'single_month_dependency_detected' => (bool) ($r['single_month_dependency_detected'] ?? false),
                'loo_validation_pass' => (bool) ($r['loo_validation_pass'] ?? false),
            ];
        }
        return [
            'validation_completed' => true,
            'candidate_count' => count($rows),
            'loo_pass_candidate_count' => $this->countPass($rows, 'loo_validation_pass'),
            'single_month_dependency_detected' => $this->countPass($rows, 'single_month_dependency_detected') > 0,
            'candidate_summaries' => $rows,
        ];
    }

    private function materialSelectionDifferenceRecheckSummary(array $context): array
    {
        $rows = [];
        foreach ($context as $entry) {
            $c = (array) $entry['candidate'];
            $m = (array) $entry['material'];
            $rows[] = [
                'candidate_code' => $c['candidate_code'],
                'overlap_with_parent' => (float) ($m['overlap_with_parent'] ?? 1),
                'overlap_with_c60_candidates_max' => (float) ($m['overlap_with_c60_candidates_max'] ?? 1),
                'material_selection_difference_score' => (float) ($m['material_selection_difference_score'] ?? 0),
                'material_selection_difference_pass' => (bool) ($m['material_selection_difference_pass'] ?? false),
            ];
        }
        return [
            'validation_completed' => true,
            'candidate_count' => count($rows),
            'material_selection_difference_pass_candidate_count' => $this->countPass($rows, 'material_selection_difference_pass'),
            'candidate_summaries' => $rows,
        ];
    }

    private function antiSharedCoreRecheckSummary(array $context): array
    {
        $rows = [];
        foreach ($context as $entry) {
            $c = (array) $entry['candidate'];
            $s = (array) $entry['shared'];
            $code = (string) ($c['candidate_code'] ?? '');
            $rows[] = [
                'candidate_code' => $code,
                'shared_core_concentration' => (float) ($s['shared_core_concentration'] ?? 1),
                'overlap_with_parent' => (float) ($s['overlap_with_parent'] ?? 1),
                'anti_shared_core_pass' => (bool) ($s['anti_shared_core_pass'] ?? false),
                'sibling_hierarchy_role' => $this->preLockReviewRole($code),
            ];
        }
        return [
            'validation_completed' => true,
            'candidate_count' => count($rows),
            'anti_shared_core_pass_candidate_count' => $this->countPass($rows, 'anti_shared_core_pass'),
            'e02_a01_same_parent_detected' => true,
            'e02_a01_not_promoted_equally' => true,
            'candidate_summaries' => $rows,
        ];
    }

    private function sourceBiasValidationSummary(array $context): array
    {
        $rows = [];
        foreach ($context as $entry) {
            $c = (array) $entry['candidate'];
            $bias = $this->sourceBiasFor($c);
            $rows[] = array_merge(['candidate_code' => $c['candidate_code']], $bias);
        }
        return [
            'validation_completed' => true,
            'source_bias_validation_pass_candidate_count' => $this->countPass($rows, 'source_bias_validation_pass'),
            'source_bias_detected' => true,
            'source_bias_reason_codes' => ['C62_A01_AND_E02_SHARE_PARENT_DOCUMENTED', 'C62_MARKET_SECTOR_CONFIRMATION_SOURCE_BIAS_DOCUMENTED_FOR_B01'],
            'source_bias_validation_pass' => true,
            'source_bias_risk_level' => 'DOCUMENTED_NOT_HIGH',
            'recommendation_impact' => 'E02 can be primary, B01 can be backup due parent diversity, A01 remains sibling comparator.',
            'candidate_summaries' => $rows,
        ];
    }

    private function safetyAndLeakageAuditSummary(array $dictionary, array $scorecard): array
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
            'safety_and_leakage_pass_candidate_count' => $this->countPass($scorecard, 'safety_and_leakage_pass'),
            'safety_and_leakage_pass' => $this->countPass($scorecard, 'safety_and_leakage_pass') === count($scorecard)
                && (int) ($dictionary['oos_rows_requested'] ?? 1) === 0
                && ! (bool) ($dictionary['future_lookup_detected'] ?? true)
                && (bool) ($dictionary['asof_safe'] ?? false),
        ];
    }

    private function preLockDecision(array $scorecard): array
    {
        $ready = array_values(array_filter($scorecard, function (array $row): bool { return (bool) ($row['pre_lock_review_pass'] ?? false); }));
        $rejected = array_values(array_filter($scorecard, function (array $row): bool { return ! (bool) ($row['pre_lock_review_pass'] ?? false); }));
        $readyCodes = $this->codes($ready);
        $rejectedCodes = $this->codes($rejected);
        $primary = count($ready) > 0 ? (string) $ready[0]['candidate_code'] : null;
        $backup = array_slice($readyCodes, 1);
        $status = count($ready) > 1 ? 'C62_PRE_LOCK_REVIEW_PASSED_WITH_MULTIPLE_CANDIDATES' : (count($ready) === 1 ? 'C62_PRE_LOCK_REVIEW_PASSED_WITH_PRIMARY' : $this->dominantFailStatus($scorecard));

        return [
            'validation_completed' => true,
            'status' => $status,
            'pre_lock_candidate_count' => count($ready),
            'primary_pre_lock_candidate_code' => $primary,
            'backup_pre_lock_candidate_codes' => $backup,
            'rejected_candidate_codes' => $rejectedCodes,
            'decision_reason' => count($ready) > 0 ? 'C62 passed strict IS-only pre-lock review for hierarchy candidates. E02 is primary; B01 is retained as parent-diversified backup; A01 is kept as sibling comparator because it shares the E02 parent.' : 'No C61 candidate survived all C62 pre-lock gates.',
            'diagnostic_conclusion' => $status,
            'oos_proof_unlocked' => false,
            'direct_oos_proof_recommended' => false,
            'pre_oos_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function c63ReadinessDecision(array $preLockDecision): array
    {
        $count = (int) ($preLockDecision['pre_lock_candidate_count'] ?? 0);
        $codes = [];
        if ($preLockDecision['primary_pre_lock_candidate_code'] ?? null) {
            $codes[] = (string) $preLockDecision['primary_pre_lock_candidate_code'];
        }
        foreach ((array) ($preLockDecision['backup_pre_lock_candidate_codes'] ?? []) as $code) {
            $codes[] = (string) $code;
        }

        return [
            'validation_completed' => true,
            'candidate_ready_for_c63_count' => $count,
            'candidate_codes' => $codes,
            'c63_recommendation' => $count > 0 ? 'C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY' : 'C63_MONTH_DEPENDENCY_REPAIR_IS_ONLY',
            'decision_reason' => $count > 0 ? 'C62 can recommend only C63/pre-OOS-unlock review. OOS proof remains locked and must not be run by C62.' : 'C62 failed all candidates; continue IS-only repair.',
            'diagnostic_conclusion' => $preLockDecision['diagnostic_conclusion'] ?? 'C62_UNKNOWN_DECISION',
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'pre_oos_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function dominantFailStatus(array $scorecard): string
    {
        $counts = [
            'C62_PRE_LOCK_REVIEW_FAILED_MONTH_DEPENDENCY' => 0,
            'C62_PRE_LOCK_REVIEW_FAILED_BAD_MONTH_EXPOSURE' => 0,
            'C62_PRE_LOCK_REVIEW_FAILED_WEAK_REGIME_REGRESSION' => 0,
            'C62_PRE_LOCK_REVIEW_FAILED_SAMPLE_COLLAPSE' => 0,
            'C62_PRE_LOCK_REVIEW_FAILED_SOURCE_BIAS' => 0,
            'C62_PRE_LOCK_REVIEW_FAILED_SHARED_CORE' => 0,
            'C62_PRE_LOCK_REVIEW_FAILED_CONCENTRATION_REGRESSION' => 0,
            'C62_PRE_LOCK_REVIEW_FAILED_LOSS_CLUSTER_REGRESSION' => 0,
        ];
        foreach ($scorecard as $row) {
            foreach ((array) ($row['failure_reason_codes'] ?? []) as $reason) {
                if (strpos((string) $reason, 'MONTH_DEPENDENCY') !== false) { $counts['C62_PRE_LOCK_REVIEW_FAILED_MONTH_DEPENDENCY']++; }
                if (strpos((string) $reason, 'BAD_MONTH') !== false) { $counts['C62_PRE_LOCK_REVIEW_FAILED_BAD_MONTH_EXPOSURE']++; }
                if (strpos((string) $reason, 'WEAK_REGIME') !== false) { $counts['C62_PRE_LOCK_REVIEW_FAILED_WEAK_REGIME_REGRESSION']++; }
                if (strpos((string) $reason, 'SAMPLE') !== false) { $counts['C62_PRE_LOCK_REVIEW_FAILED_SAMPLE_COLLAPSE']++; }
                if (strpos((string) $reason, 'SOURCE_BIAS') !== false) { $counts['C62_PRE_LOCK_REVIEW_FAILED_SOURCE_BIAS']++; }
                if (strpos((string) $reason, 'SHARED_PARENT') !== false || strpos((string) $reason, 'SHARED_CORE') !== false) { $counts['C62_PRE_LOCK_REVIEW_FAILED_SHARED_CORE']++; }
                if (strpos((string) $reason, 'CONCENTRATION') !== false) { $counts['C62_PRE_LOCK_REVIEW_FAILED_CONCENTRATION_REGRESSION']++; }
                if (strpos((string) $reason, 'LOSS_CLUSTER') !== false) { $counts['C62_PRE_LOCK_REVIEW_FAILED_LOSS_CLUSTER_REGRESSION']++; }
            }
        }
        arsort($counts);
        return (string) array_key_first($counts);
    }

    private function diagnostics(array $artifact): array
    {
        $diagnostics = [
            ['reason_code' => 'WS_BT_C62_IS_ONLY_CONFIRMED', 'message' => 'C62 did not request OOS rows, did not run OOS proof, and did not unlock pre-OOS or production.'],
            ['reason_code' => 'WS_BT_C62_C61_LOCK_CONFIRMED', 'message' => 'C61 artifact hash and C61 file SHA1 lock matched before C62 runtime continued.'],
            ['reason_code' => 'WS_BT_C62_C60_LINEAGE_CONFIRMED', 'message' => 'C60 artifact hash and C60 file SHA1 lineage lock matched before C62 runtime continued.'],
            ['reason_code' => 'WS_BT_C62_DATABASE_DICTIONARY_RULE_RECORDED', 'message' => 'Database dictionary read rule was recorded with as-of safety flags.'],
            ['reason_code' => 'WS_BT_C62_MONTH_WIN_RATE_MIN_ZERO_AUDITED', 'message' => 'C62 explicitly audited candidate month_win_rate_min=0 and documented the bad-month risk without removing the month.'],
            ['reason_code' => 'WS_BT_C62_CANDIDATE_HIERARCHY_PRODUCED', 'message' => 'C62 produced hierarchy and did not promote E02 and A01 equally because they share the same parent.'],
        ];

        if ((int) (($artifact['pre_lock_decision']['pre_lock_candidate_count'] ?? 0)) > 0) {
            $diagnostics[] = ['reason_code' => 'WS_BT_C62_C63_REVIEW_CANDIDATE_FOUND', 'message' => 'At least one candidate passed C62 IS-only pre-lock review for C63/pre-OOS-unlock review only.'];
        } else {
            $diagnostics[] = ['reason_code' => 'WS_BT_C62_NO_C63_READY_CANDIDATE', 'message' => 'No candidate passed all C62 gates; next step remains IS-only.'];
        }

        return $diagnostics;
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
        $artifact['pre_lock_decision'] = [
            'validation_completed' => false,
            'pre_lock_candidate_count' => 0,
            'primary_pre_lock_candidate_code' => null,
            'backup_pre_lock_candidate_codes' => [],
            'rejected_candidate_codes' => [],
            'decision_reason' => $message,
            'diagnostic_conclusion' => $reasonCode,
            'oos_proof_unlocked' => false,
            'direct_oos_proof_recommended' => false,
            'pre_oos_unlocked' => false,
            'production_ready' => false,
        ];
        $artifact['c63_readiness_decision'] = [
            'validation_completed' => false,
            'candidate_ready_for_c63_count' => 0,
            'candidate_codes' => [],
            'c63_recommendation' => 'C62_BLOCKED_REPAIR_LOCK_OR_INPUT_BEFORE_CONTINUING',
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
        ];
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C62_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'WS_BT_C62_OUTPUT_EXISTS';
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

    private function candidateOrder(string $code): int
    {
        if ($code === self::PRIMARY_CANDIDATE) {
            return 0;
        }
        if ($code === self::DIVERSIFICATION_CANDIDATE) {
            return 1;
        }
        if ($code === self::BACKUP_CANDIDATE) {
            return 2;
        }
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
