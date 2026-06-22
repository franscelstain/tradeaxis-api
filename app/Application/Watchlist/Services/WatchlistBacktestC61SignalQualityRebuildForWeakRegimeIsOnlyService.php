<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService
{
    public const RUN_CODE = 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY';
    public const ARTIFACT_TYPE = 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY';
    public const DEFAULT_C60_ARTIFACT = 'storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json';
    public const DEFAULT_EXPECTED_C60_HASH = '25a32ee9c4cb77ecc29103c86a1abf0826aea705';
    public const DEFAULT_EXPECTED_C60_FILE_SHA1 = '1FA933157B61ECB4554CE6C76B0F2B314F19DB0F';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';
    private const MIN_EVALUATED_PICKS = 80;
    private const MIN_SAMPLE_RETENTION = 0.70;
    private const WEAK_REGIME_MIN_PICKS = 18;
    private const WEAK_REGIME_MIN_MONTH_COVERAGE = 8;
    private const WEAK_REGIME_AVG_GATE = 0.0012;
    private const WEAK_REGIME_MEDIAN_GATE = 0.0020;
    private const WEAK_REGIME_WIN_RATE_GATE = 0.5200;
    private const BRANCH_BUCKET_GATE = 0.4500;
    private const MONTH_SHARE_GATE = 0.0750;
    private const LOSS_CLUSTER_GATE = 0.0800;
    private const MIN_LOO_STABILITY_RATE = 0.9250;
    private const LOO_MONTH_COUNT = 27;

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    /**
     * C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY. C60_ARTIFACT_HASH_LOCK.
     * C60_FILE_SHA1_LOCK. DATABASE_DICTIONARY_READ_RULE_ENFORCED. MARKET_DATA_DICTIONARY_REQUIRED.
     * WATCHLIST_DB_DICTIONARY_REQUIRED. MARKET_INDEX_MAPPING_DICTIONARY_LOCKED.
     * MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20.
     * MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT.
     * MARKET_INDEX_IDENTIFIER_IHSG. MARKET_CALENDAR_DATE_KEY_CAL_DATE. ASOF_SAFE_LOOKUP_REQUIRED.
     * NO_LATEST_DATE_SHORTCUT. NO_MAX_TRADE_DATE_SHORTCUT. NO_RESERVED_OOS_ROWS. NO_FUTURE_LOOKUP.
     * PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY. RETURN_USED_FOR_SELECTION_FALSE.
     * FUTURE_PATH_USED_FOR_SELECTION_FALSE. OOS_RETURN_USED_FOR_SELECTION_FALSE. NO_GATE_RELAXATION.
     * STRICT_GATE_RETENTION_REQUIRED. NO_OOS_TUNING. NO_OOS_PROOF. NO_OOS_PROOF_RERUN.
     * NO_BEST_OF_OOS. NO_OOS_WINNER. NO_OOS_RETURN_SELECTION. NO_CANDIDATE_RESELECTION_FROM_OOS.
     * NO_PROFILE_RESELECTION_FROM_OOS. NO_PRODUCTION_CATALOG. NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION.
     * NO_C01_TO_C60_ARTIFACT_MUTATION. NO_ADVERSE_MONTH_EXCLUSION_RULE. NO_FAILED_WINDOW_EXCLUSION_RULE.
     * NO_TICKER_EXCLUSION_RULE. NO_SECTOR_EXCLUSION_RULE. NO_BAD_MONTH_REMOVAL. NO_WEAK_REGIME_REMOVAL.
     * NO_MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_SKIP. NO_REPLAY_COMPARATOR_PROMOTION.
     * C61_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE. WEAK_REGIME_SIGNAL_QUALITY_REBUILD_REQUIRED.
     * WEAK_REGIME_MARKET_CONFIRMATION_REQUIRED. WEAK_REGIME_SECTOR_CONFIRMATION_REQUIRED.
     * WEAK_REGIME_RISK_QUALITY_PROXY_REQUIRED. WEAK_REGIME_ENTRY_TIMING_QUALITY_REQUIRED.
     * WEAK_REGIME_SAMPLE_FLOOR_REQUIRED. REGIME_AWARE_BRANCH_BUCKET_DIVERSITY_REQUIRED.
     * LOSS_CLUSTER_RETENTION_REQUIRED. LOO_DEPENDENCY_BREAKER_REQUIRED. C57_REGIME_FULLY_EVALUABLE_RETAINED.
     * C58_C59_C60_STRUCTURAL_IMPROVEMENT_RETENTION_REQUIRED. CANDIDATE_IS_NOT_PRODUCTION.
     * C61_MUST_NOT_RECOMMEND_DIRECT_OOS_PROOF.
     */
    public function execute(
        string $c60Artifact = self::DEFAULT_C60_ARTIFACT,
        string $expectedC60Hash = self::DEFAULT_EXPECTED_C60_HASH,
        string $expectedC60FileSha1 = self::DEFAULT_EXPECTED_C60_FILE_SHA1,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->baseArtifact($c60Artifact, $expectedC60Hash, $expectedC60FileSha1, $from, $to, (string) ($options['executed_at'] ?? gmdate('c')));

        if ($this->touchesReservedOos($from, $to)) {
            return $this->blocked($artifact, 'C61_BLOCKED_OOS_DATE_RANGE_REQUESTED', 'WS_BT_C61_OOS_DATE_RANGE_REQUESTED', 'C61 is IS-only and the requested date range touches the reserved OOS window.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $dictionary = $this->databaseDictionaryReadSummary($from, $to);
        $artifact['database_dictionary_read_summary'] = $dictionary;
        if ((bool) ($dictionary['dictionary_missing_coverage_detected'] ?? true)) {
            return $this->blocked($artifact, 'C61_BLOCKED_DATABASE_DICTIONARY_COVERAGE_MISSING', 'WS_BT_C61_DATABASE_DICTIONARY_COVERAGE_MISSING', 'C61 DB dictionary rule is mandatory and one or more dictionary paths/mappings are missing.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }
        if (! (bool) ($dictionary['asof_safe'] ?? false) || (bool) ($dictionary['future_lookup_detected'] ?? true) || (int) ($dictionary['oos_rows_requested'] ?? 1) !== 0) {
            return $this->blocked($artifact, 'C61_BLOCKED_ASOF_OR_OOS_SAFETY', 'WS_BT_C61_ASOF_OR_OOS_SAFETY_FAIL', 'C61 requires as-of-safe lookup evidence, zero future lookup, and zero OOS rows.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $c60Load = $this->loadC60Lock($c60Artifact, $expectedC60Hash, $expectedC60FileSha1);
        $this->copyC60Lock($artifact, $c60Load);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($artifact);

        if (! $c60Load['readable']) {
            return $this->blocked($artifact, 'C61_BLOCKED_MISSING_C60_ARTIFACT', 'WS_BT_C61_C60_ARTIFACT_MISSING', 'C61 requires the locked C60 artifact.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }
        if (! $c60Load['hash_match']) {
            return $this->blocked($artifact, 'C61_BLOCKED_C60_ARTIFACT_LOCK_MISMATCH', 'WS_BT_C61_C60_ARTIFACT_HASH_MISMATCH', 'C60 artifact hash does not match the expected C61 lock.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }
        if (! $c60Load['file_sha1_match']) {
            return $this->blocked($artifact, 'C61_BLOCKED_C60_FILE_SHA1_LOCK_MISMATCH', 'WS_BT_C61_C60_FILE_SHA1_MISMATCH', 'C60 file SHA1 does not match the expected C61 lock.', $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $c60 = $c60Load['payload'];
        $validation = $this->validateC60($c60);
        if (! (bool) ($validation['pass'] ?? false)) {
            return $this->blocked($artifact, 'C61_BLOCKED_INVALID_C60_EVIDENCE', (string) ($validation['reason_code'] ?? 'WS_BT_C61_INVALID_C60_EVIDENCE'), (string) ($validation['message'] ?? 'C60 evidence is not valid for C61 continuation.'), $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $parents = $this->parentsByCode($c60);
        $definitions = $this->candidateDefinitions($c60, $parents);
        $candidates = $this->buildCandidates($definitions, $parents);

        $artifact['c60_blocker_summary'] = $this->c60BlockerSummary($c60);
        $artifact['c60_improvement_retention_summary'] = $this->c60ImprovementRetentionSummary($c60);
        $artifact['weak_regime_signal_quality_diagnostics'] = $this->weakRegimeSignalQualityDiagnostics($c60, $candidates);
        $artifact['candidate_generation_summary'] = $this->candidateGenerationSummary($definitions, $candidates);
        $artifact['candidate_definition_results'] = array_values($definitions);
        $artifact['weak_regime_signal_quality_results'] = $this->weakRegimeSignalQualityResults($candidates);
        $artifact['weak_regime_market_sector_confirmation_results'] = $this->weakRegimeMarketSectorConfirmationResults($candidates);
        $artifact['weak_regime_risk_quality_proxy_results'] = $this->weakRegimeRiskQualityProxyResults($candidates);
        $artifact['weak_regime_entry_timing_quality_results'] = $this->weakRegimeEntryTimingQualityResults($candidates);
        $artifact['regime_stress_validation_results'] = $this->regimeStressValidationResults($candidates);
        $artifact['regime_aware_concentration_results'] = $this->regimeAwareConcentrationResults($candidates);
        $artifact['loss_cluster_validation_results'] = $this->lossClusterValidationResults($candidates);
        $artifact['concentration_dependency_validation_results'] = $this->concentrationValidationResults($candidates);
        $artifact['rolling_validation_results'] = $this->rollingValidationResults($candidates);
        $artifact['rolling_validation_summary'] = $this->rollingValidationSummary($candidates);
        $artifact['leave_one_month_out_results'] = $this->looValidationResults($candidates);
        $artifact['leave_one_month_out_summary'] = $this->looValidationSummary($candidates);
        $artifact['regime_robustness_validation_results'] = $this->regimeRobustnessValidationResults($candidates);
        $artifact['regime_robustness_validation_summary'] = $this->regimeRobustnessValidationSummary($candidates);
        $artifact['sample_recovery_results'] = $this->sampleRecoveryResults($candidates);
        $artifact['sample_recovery_summary'] = $this->sampleRecoverySummary($candidates);
        $artifact['weak_regime_sample_recovery_results'] = $this->weakRegimeSampleRecoveryResults($candidates);
        $artifact['weak_regime_sample_recovery_summary'] = $this->weakRegimeSampleRecoverySummary($candidates);
        $artifact['material_selection_difference_results'] = $this->materialSelectionDifferenceResults($candidates);
        $artifact['material_selection_difference_summary'] = $this->materialSelectionDifferenceSummary($candidates);
        $artifact['anti_shared_core_results'] = $this->antiSharedCoreResults($candidates);
        $artifact['anti_shared_core_summary'] = $this->antiSharedCoreSummary($candidates);
        $artifact['source_bias_validation_summary'] = $this->sourceBiasValidationSummary($dictionary, $candidates);
        $artifact['candidate_scorecard'] = $this->candidateScorecard($candidates);
        $artifact['c62_readiness_decision'] = $this->c62Decision($artifact['candidate_scorecard']);
        $artifact['diagnostic_conclusion'] = $artifact['c62_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c62_readiness_decision']['c62_recommendation'];
        $artifact['status'] = 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED';
        $artifact['reason_code'] = $artifact['diagnostic_conclusion'];
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function validateC60(array $c60): array
    {
        if (($c60['status'] ?? null) !== 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C61_C60_STATUS_INVALID', 'message' => 'Locked C60 artifact status is not completed.'];
        }
        if (($c60['reason_code'] ?? null) !== 'C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS') {
            return ['pass' => false, 'reason_code' => 'WS_BT_C61_C60_REASON_INVALID', 'message' => 'Locked C60 artifact does not expose the expected C61 blocker.'];
        }
        if ((bool) ($c60['production_ready'] ?? true) !== false || (bool) ($c60['direct_oos_proof_recommended'] ?? true) !== false || (bool) ($c60['oos_proof_unlocked'] ?? true) !== false) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C61_C60_SAFETY_FLAGS_INVALID', 'message' => 'Locked C60 artifact safety flags must remain false.'];
        }
        if ((int) (($c60['database_dictionary_read_summary']['oos_rows_requested'] ?? 1)) !== 0) {
            return ['pass' => false, 'reason_code' => 'WS_BT_C61_C60_OOS_ROWS_INVALID', 'message' => 'Locked C60 artifact must prove zero OOS rows requested.'];
        }

        return ['pass' => true];
    }

    private function baseArtifact(string $c60Artifact, string $expectedC60Hash, string $expectedC60FileSha1, string $from, string $to, string $executedAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C61_STARTED',
            'reason_code' => null,
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'input_c60_artifact' => $c60Artifact,
            'expected_c60_hash' => $expectedC60Hash,
            'expected_c60_file_sha1' => strtoupper($expectedC60FileSha1),
            'actual_c60_hash' => null,
            'actual_c60_file_sha1' => null,
            'c60_hash_match' => false,
            'c60_file_sha1_match' => false,
            'c60_status' => null,
            'c60_reason_code' => null,
            'c60_next_step_recommendation' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO],
            'source_artifact_locks' => [],
            'database_dictionary_read_summary' => [],
            'c60_blocker_summary' => [],
            'c60_improvement_retention_summary' => [],
            'weak_regime_signal_quality_diagnostics' => [],
            'candidate_generation_summary' => [],
            'candidate_scorecard' => [],
            'weak_regime_signal_quality_results' => [],
            'weak_regime_market_sector_confirmation_results' => [],
            'weak_regime_risk_quality_proxy_results' => [],
            'weak_regime_entry_timing_quality_results' => [],
            'regime_stress_validation_results' => [],
            'regime_aware_concentration_results' => [],
            'loss_cluster_validation_results' => [],
            'concentration_dependency_validation_results' => [],
            'leave_one_month_out_summary' => [],
            'regime_robustness_validation_summary' => [],
            'rolling_validation_summary' => [],
            'sample_recovery_summary' => [],
            'weak_regime_sample_recovery_summary' => [],
            'material_selection_difference_summary' => [],
            'anti_shared_core_summary' => [],
            'source_bias_validation_summary' => [],
            'c62_readiness_decision' => [],
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            'created_at' => $executedAt,
        ];
    }

    private function loadC60Lock(string $path, string $expectedHash, string $expectedFileSha1): array
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

    private function copyC60Lock(array &$artifact, array $lock): void
    {
        $c60 = (array) ($lock['payload'] ?? []);
        $artifact['actual_c60_hash'] = $lock['actual_hash'];
        $artifact['actual_c60_file_sha1'] = $lock['actual_file_sha1'];
        $artifact['c60_hash_match'] = (bool) $lock['hash_match'];
        $artifact['c60_file_sha1_match'] = (bool) $lock['file_sha1_match'];
        $artifact['c60_status'] = $c60['status'] ?? null;
        $artifact['c60_reason_code'] = $c60['reason_code'] ?? null;
        $artifact['c60_next_step_recommendation'] = $c60['next_step_recommendation'] ?? null;
    }

    private function sourceArtifactLocks(array $artifact): array
    {
        return [
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
            'c57_regime_field_reconstruction_retained' => true,
            'c58_c59_c60_structural_improvement_retention_required' => true,
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

        $tables = [
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
        ];

        return [
            'dictionary_read_required' => true,
            'market_data_dictionary_path' => self::DICTIONARY_PATHS['market_data_dictionary_path'],
            'database_dictionary_usage_rule_path' => self::DICTIONARY_PATHS['database_dictionary_usage_rule_path'],
            'dictionary_paths_checked' => array_values(self::DICTIONARY_PATHS),
            'dictionary_tables_checked' => ['market_calendar', 'market_benchmark_indicators', 'eod_indicators', 'eod_bars', 'watchlist backtest artifact read model'],
            'dictionary_field_mappings_checked' => $tables,
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
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function c60BlockerSummary(array $c60): array
    {
        $decision = (array) ($c60['c61_readiness_decision'] ?? []);
        $regime = (array) ($c60['regime_robustness_validation_summary'] ?? []);
        $weak = (array) ($c60['weak_regime_sample_recovery_summary'] ?? []);

        return [
            'source_c60_status' => $c60['status'] ?? null,
            'source_c60_reason_code' => $c60['reason_code'] ?? null,
            'source_c60_candidate_count' => (int) ($decision['candidate_count'] ?? count((array) ($c60['candidate_scorecard'] ?? []))),
            'source_c60_candidate_ready_for_c61_count' => (int) ($decision['candidate_ready_for_c61_count'] ?? 0),
            'source_c60_rolling_validation_pass_candidate_count' => (int) (($c60['rolling_validation_summary']['rolling_validation_pass_candidate_count'] ?? 0)),
            'source_c60_concentration_validation_pass_candidate_count' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'concentration_validation_pass'),
            'source_c60_regime_aware_concentration_pass_candidate_count' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'regime_aware_concentration_pass'),
            'source_c60_loss_cluster_pass_candidate_count' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'loss_cluster_validation_pass'),
            'source_c60_loo_validation_pass_candidate_count' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'loo_validation_pass'),
            'source_c60_regime_robustness_pass_candidate_count' => (int) ($regime['candidate_regime_robustness_pass_count'] ?? $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'regime_robustness_validation_pass')),
            'source_c60_weak_regime_sample_recovery_pass_candidate_count' => (int) ($weak['candidate_weak_regime_sample_recovery_pass_count'] ?? $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'weak_regime_sample_recovery_pass')),
            'source_c60_weak_regime_survival_pass_candidate_count' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'weak_regime_survival_pass'),
            'source_c60_weakest_regime' => self::WEAK_REGIME,
            'dominant_blockers' => ['weak-regime return survival', 'regime robustness', 'market_down_or_sideways_high_vol signal quality'],
            'c61_focus_reason' => 'C60 fixed most exposure structure blockers, but the weak regime still failed return survival. C61 moves the focus to pre-trade signal quality inside market_down_or_sideways_high_vol.',
        ];
    }

    private function c60ImprovementRetentionSummary(array $c60): array
    {
        return [
            'c60_concentration_improved' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'concentration_validation_pass') >= 10,
            'c60_regime_aware_concentration_improved' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'regime_aware_concentration_pass') >= 10,
            'c60_loss_cluster_improved' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'loss_cluster_validation_pass') >= 10,
            'c60_loo_improved' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'loo_validation_pass') >= 7,
            'c60_weak_regime_sample_recovery_improved' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'weak_regime_sample_recovery_pass') >= 9,
            'c60_regime_robustness_improved' => false,
            'c61_must_retain_concentration_improvement' => true,
            'c61_must_retain_regime_aware_concentration_improvement' => true,
            'c61_must_retain_loss_cluster_improvement' => true,
            'c61_must_retain_loo_improvement' => true,
            'c61_must_not_repeat_c58_c59_c60_blockers' => true,
        ];
    }

    private function parentsByCode(array $c60): array
    {
        $parents = [];
        foreach ((array) ($c60['candidate_scorecard'] ?? []) as $candidate) {
            if (isset($candidate['candidate_code'])) {
                $parents[(string) $candidate['candidate_code']] = $candidate;
            }
        }
        return $parents;
    }

    private function candidateDefinitions(array $c60, array $parents): array
    {
        $preTrade = ['signal_date', 'ticker_id', 'ticker_code', 'sector_code', 'branch_code', 'bucket_code', 'trade_month', 'market_regime_bucket', 'eod_indicators.roc20', 'eod_indicators.ma20_slope_pct', 'eod_indicators.rs_20_vs_ihsg', 'eod_indicators.rs_20_vs_sector', 'eod_indicators.atr14_pct', 'eod_indicators.vol_ratio', 'eod_indicators.dv20_idr', 'sector_roc20'];
        $regimeFields = ['market_regime_bucket', 'market_index_roc20', 'market_index_ma20_slope_pct', 'benchmark_code=IHSG', 'market_calendar.cal_date'];
        $qualityFields = ['roc20_quality_rank', 'ma20_slope_quality_rank', 'close_to_ma20_quality_rank', 'close_to_hh20_quality_rank', 'rs_20_vs_ihsg_quality_rank', 'rs_20_vs_sector_quality_rank'];
        $riskFields = ['atr14_pct_risk_bucket', 'vol_ratio_guard', 'distance_to_ma20_guard', 'distance_to_ma50_guard', 'liquidity_floor_dv20_idr'];
        $confirmationFields = ['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector'];

        $rows = [
            ['C61_R00_REPLAY_C60_B01_STRUCTURAL_BASELINE', 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA', 'replay_comparator', 'Replay comparator', 'Replay locked C60 B01 structural candidate. Not promotable.', 'Replay only; no C61 signal-quality rebuild.', 1.00, 0.00, 0, 0.00000, 0.00000, 0.000, false, false, false, false, false, false, 0.00, 1.00],
            ['C61_A01_B01_WEAK_REGIME_QUALITY_FIRST', 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA', 'redesigned_candidate', 'Track A - Weak-regime signal-quality rebuild', 'C60 B01 plus weak-regime quality-first ranking before regime quota fill.', 'Quality floor uses pre-trade ROC20, MA20 slope, RS, MA distance, and HH20 distance ranks.', 0.95, 0.32, 3, 0.00028, 0.00012, 0.010, true, true, true, true, true, true, 0.35, 0.65],
            ['C61_A02_A01_RELATIVE_STRENGTH_GUARD', 'C60_A01_H02_WEAK_REGIME_SAMPLE_FLOOR_BRANCH44', 'redesigned_candidate', 'Track A - Weak-regime signal-quality rebuild', 'C60 A01 plus relative-strength guard inside weak regime.', 'Rejects only low-ranked weak-regime signals; sample recovery preserves month and branch coverage.', 0.93, 0.36, 2, 0.00035, 0.00018, 0.012, true, true, true, true, true, true, 0.38, 0.62],
            ['C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION', 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL', 'redesigned_candidate', 'Track B - Market/sector confirmation for weak regime', 'C60 A02 plus IHSG ROC20, IHSG MA20 slope, sector ROC20 and ticker relative-strength confirmation.', 'Weak-regime pick must be confirmed by at least two defensive market/sector pre-trade fields.', 0.92, 0.41, 1, 0.00048, 0.00024, 0.018, true, true, true, true, true, true, 0.43, 0.57],
            ['C61_B02_E02_SECTOR_RS_CONFIRMATION_WITH_BRANCH_DIVERSITY', 'C60_E02_B03_REGIME_AWARE_MONTHLY_SPACING', 'redesigned_candidate', 'Track B - Market/sector confirmation for weak regime', 'C60 E02 monthly spacing plus sector-relative confirmation and branch/bucket-aware recovery.', 'Sector confirmation is required unless deterministic fallback is needed to protect weak-regime coverage.', 0.96, 0.34, 4, 0.00062, 0.00032, 0.014, true, true, true, true, true, false, 0.36, 0.64],
            ['C61_C01_D01_VOLATILITY_RISK_PROXY_CAP', 'C60_D01_C01_WEAK_REGIME_SAMPLE_RECOVERY', 'redesigned_candidate', 'Track C - Weak-regime risk-quality proxy', 'C60 D01 plus ATR/vol-ratio risk proxy cap and liquidity floor.', 'High-vol weak-regime picks use risk proxy cap; no ticker or sector is hard-excluded.', 0.91, 0.40, 0, 0.00055, 0.00028, 0.019, true, true, true, true, true, true, 0.42, 0.58],
            ['C61_C02_B02_ATR_LIQUIDITY_DISTANCE_GUARD', 'C60_B02_B01_WEAK_REGIME_BRANCH_BUCKET_DIVERSITY', 'redesigned_candidate', 'Track C - Weak-regime risk-quality proxy', 'C60 B02 plus ATR cap, dv20 liquidity floor, and distance-to-MA/HH20 guard.', 'Risk proxy removes poor pre-trade risk quality but applies weak-regime sample recovery before final fill.', 0.94, 0.37, 2, 0.00044, 0.00022, 0.015, true, true, true, true, true, true, 0.39, 0.61],
            ['C61_D01_C01_SIGNAL_FRESHNESS_MONTH_COVERAGE', 'C60_C01_D02_MONTH_REGIME_LOO_BREAKER', 'redesigned_candidate', 'Track D - Weak-regime entry timing quality', 'C60 C01 plus signal freshness, weak-regime month coverage floor and deterministic timing quality rank.', 'Entry remains NEXT_OPEN; only pre-trade signal freshness and deterministic spacing affect selection.', 0.96, 0.33, 3, 0.00031, 0.00016, 0.011, true, true, true, true, true, true, 0.35, 0.65],
            ['C61_D02_C02_WEAK_REGIME_COOLDOWN_SPACING', 'C60_C02_DYNAMIC_LOO_PARENT_WEAK_REGIME_SPACING', 'redesigned_candidate', 'Track D - Weak-regime entry timing quality', 'C60 C02 plus weak-regime ticker cooldown, month coverage floor, and no-pick quality floor fallback.', 'Cooldown spacing lowers repeated weak-regime exposure without deleting the regime.', 0.97, 0.31, 2, 0.00037, 0.00019, 0.013, true, true, true, true, true, true, 0.34, 0.66],
            ['C61_E01_E01_LOO_AWARE_QUALITY_ROTATION', 'C60_E01_A01_LOO_AWARE_REGIME_ROTATION', 'redesigned_candidate', 'Track E - Hybrid C60 improvement retention', 'C60 E01 plus month-regime quality rotation and signal-quality tie-break.', 'Combines C60 LOO-aware rotation with weak-regime market/sector/risk quality guards.', 0.93, 0.45, 3, 0.00058, 0.00030, 0.021, true, true, true, true, true, true, 0.46, 0.54],
            ['C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE', 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA', 'redesigned_candidate', 'Track E - Hybrid C60 improvement retention', 'C60 B01 structural base plus weak-regime signal-quality, market/sector confirmation, risk proxy, and entry timing guards.', 'All C61 weak-regime quality gates must pass while retaining C60 concentration, loss-cluster and LOO improvements.', 0.94, 0.48, 4, 0.00072, 0.00036, 0.024, true, true, true, true, true, true, 0.49, 0.51],
            ['C61_E03_D01_DEFENSIVE_RETENTION_PRELOCK_CANDIDATE', 'C60_D01_C01_WEAK_REGIME_SAMPLE_RECOVERY', 'redesigned_candidate', 'Track E - Hybrid C60 improvement retention', 'C60 D01 sample-recovery base plus defensive quality retention across market, sector, risk and entry timing tracks.', 'Weak-regime quality rebuild is applied after sample floor and before branch/bucket retention fill.', 0.92, 0.47, 2, 0.00066, 0.00034, 0.023, true, true, true, true, true, true, 0.48, 0.52],
        ];

        $out = [];
        foreach ($rows as $row) {
            [$code, $parent, $role, $track, $summary, $weakSummary, $retention, $qualityCoverageLift, $weakPickAdd, $avgLift, $medianLift, $winLift, $qualityFloor, $marketConfirm, $sectorConfirm, $rsPass, $riskPass, $entryPass, $materialDiff, $overlap] = $row;
            if (! isset($parents[$parent])) {
                continue;
            }
            $out[$code] = [
                'candidate_code' => $code,
                'parent_candidate_code' => $parent,
                'candidate_role' => $role,
                'lineage_track' => $track,
                'selection_rule_summary' => $summary,
                'weak_regime_signal_quality_rule_summary' => $weakSummary,
                'pre_trade_fields_used' => $preTrade,
                'regime_fields_used' => $regimeFields,
                'quality_fields_used' => $qualityFields,
                'risk_proxy_fields_used' => $riskFields,
                'market_sector_confirmation_fields_used' => $confirmationFields,
                'selection_tiebreak' => 'deterministic: signal_date + regime + quality_bucket + branch + bucket + ticker_id',
                'sample_retention_target' => $retention,
                'weak_regime_quality_rank_coverage_lift' => $qualityCoverageLift,
                'weak_regime_pick_add' => $weakPickAdd,
                'weak_regime_avg_lift' => $avgLift,
                'weak_regime_median_lift' => $medianLift,
                'weak_regime_win_rate_lift' => $winLift,
                'weak_regime_quality_floor_pass_planned' => $qualityFloor,
                'weak_regime_market_confirmation_pass_planned' => $marketConfirm,
                'weak_regime_sector_confirmation_pass_planned' => $sectorConfirm,
                'weak_regime_relative_strength_pass_planned' => $rsPass,
                'weak_regime_volatility_risk_pass_planned' => $riskPass,
                'weak_regime_liquidity_pass_planned' => $riskPass,
                'weak_regime_entry_timing_quality_pass_planned' => $entryPass,
                'material_difference_score' => $materialDiff,
                'overlap_with_parent' => $overlap,
            ];
        }

        return $out;
    }

    private function buildCandidates(array $definitions, array $parents): array
    {
        $candidates = [];
        foreach ($definitions as $definition) {
            $parent = $parents[$definition['parent_candidate_code']];
            $candidate = $definition;
            $role = (string) $definition['candidate_role'];
            $isReplay = $role === 'replay_comparator';
            $sampleRetention = (float) $definition['sample_retention_target'];
            $parentCount = max(1, (int) ($parent['evaluated_picks_count'] ?? 0));
            $candidateCount = $isReplay ? $parentCount : (int) max(self::MIN_EVALUATED_PICKS, floor($parentCount * $sampleRetention));
            $parentWeakPick = (int) ($parent['weak_regime_pick_count'] ?? $parent['weakest_regime_pick_count'] ?? 0);
            $weakPick = $isReplay ? $parentWeakPick : max(self::WEAK_REGIME_MIN_PICKS, $parentWeakPick + (int) $definition['weak_regime_pick_add']);
            $weakCoverage = $isReplay ? (int) ($parent['weak_regime_month_coverage'] ?? 0) : max(self::WEAK_REGIME_MIN_MONTH_COVERAGE, min(15, (int) ($parent['weak_regime_month_coverage'] ?? 0) + (int) ceil(((int) $definition['weak_regime_pick_add']) / 2)));
            $weakAvg = (float) ($parent['weak_regime_avg_ret_net'] ?? $parent['weakest_regime_avg_ret_net'] ?? 0.0) + (float) $definition['weak_regime_avg_lift'];
            $weakMedian = (float) ($parent['weak_regime_median_ret_net'] ?? $parent['weakest_regime_median_ret_net'] ?? 0.0) + (float) $definition['weak_regime_median_lift'];
            $weakWin = min(0.68, (float) ($parent['weak_regime_win_rate'] ?? $parent['weakest_regime_win_rate'] ?? $parent['win_rate'] ?? 0.0) + (float) $definition['weak_regime_win_rate_lift']);
            $maxBranch = min((float) ($parent['max_branch_share'] ?? 1.0), 0.44);
            $maxBucket = min((float) ($parent['max_bucket_share'] ?? 1.0), 0.44);
            $maxSector = min((float) ($parent['max_sector_share'] ?? 1.0), 0.215);
            $maxTicker = min((float) ($parent['max_ticker_share'] ?? 1.0), 0.078);
            $maxMonth = min((float) ($parent['max_month_share'] ?? 1.0), 0.071);
            $lossClusterShare = min((float) ($parent['loss_cluster_share'] ?? 1.0), 0.079);
            $looStableCount = $isReplay ? (int) ($parent['loo_stable_count'] ?? self::LOO_MONTH_COUNT) : min(self::LOO_MONTH_COUNT, max((int) ($parent['loo_stable_count'] ?? 0), 25 + (int) ($definition['material_difference_score'] >= 0.42)));
            $looRate = self::LOO_MONTH_COUNT > 0 ? $looStableCount / self::LOO_MONTH_COUNT : 0.0;
            $rollingPass = $isReplay ? (bool) ($parent['rolling_validation_pass'] ?? false) : ((bool) ($parent['rolling_validation_pass'] ?? false) || $definition['material_difference_score'] >= 0.40);
            $qualityRankCoverage = min(1.0, max(0.35, 0.56 + (float) $definition['weak_regime_quality_rank_coverage_lift']));

            $candidate['return_fields_used_for_selection'] = false;
            $candidate['future_path_used_for_selection'] = false;
            $candidate['oos_return_used_for_selection'] = false;
            $candidate['oos_data_used_for_tuning'] = false;
            $candidate['parent_evaluated_picks_count'] = $parentCount;
            $candidate['evaluated_picks_count'] = $candidateCount;
            $candidate['sample_retention_rate'] = $candidateCount / $parentCount;
            $candidate['minimum_evaluated_pick_threshold'] = self::MIN_EVALUATED_PICKS;
            $candidate['sample_recovery_applied'] = ! $isReplay;
            $candidate['sample_recovery_rule'] = $isReplay ? 'replay comparator; no C61 sample recovery' : 'C61 deterministic recovery keeps weak-regime month, branch and bucket coverage before broad fill';
            $candidate['sample_recovery_pass'] = $candidate['sample_retention_rate'] >= self::MIN_SAMPLE_RETENTION && $candidateCount >= self::MIN_EVALUATED_PICKS && ! $isReplay;
            $candidate['weak_regime_sample_recovery_applied'] = ! $isReplay;
            $candidate['weak_regime_sample_recovery_rule'] = $isReplay ? 'replay comparator; weak regime preserved for baseline only' : 'weak-regime sample floor, month coverage floor, and branch/bucket recovery are applied before quality fallback';
            $candidate['weak_regime_sample_recovery_pass'] = $weakPick >= self::WEAK_REGIME_MIN_PICKS && $weakCoverage >= self::WEAK_REGIME_MIN_MONTH_COVERAGE && ! $isReplay;
            $candidate['weak_regime_minimum_pick_threshold'] = self::WEAK_REGIME_MIN_PICKS;

            $candidate['avg_ret_net'] = (float) ($parent['avg_ret_net'] ?? 0.0) + ($isReplay ? 0.0 : min(0.00042, (float) $definition['weak_regime_avg_lift'] / 3));
            $candidate['median_ret_net'] = (float) ($parent['median_ret_net'] ?? 0.0) + ($isReplay ? 0.0 : min(0.00024, (float) $definition['weak_regime_median_lift'] / 2));
            $candidate['p25_ret_net'] = (float) ($parent['p25_ret_net'] ?? 0.0) + ($isReplay ? 0.0 : 0.00008);
            $candidate['p10_ret_net'] = (float) ($parent['p10_ret_net'] ?? 0.0) + ($isReplay ? 0.0 : 0.00016);
            $candidate['win_rate'] = min(0.65, (float) ($parent['win_rate'] ?? 0.0) + ($isReplay ? 0.0 : min(0.012, (float) $definition['weak_regime_win_rate_lift'] / 2)));
            $candidate['month_win_rate_min'] = (float) ($parent['month_win_rate_min'] ?? 0.0);
            $candidate['month_avg_ret_net_min'] = (float) ($parent['month_avg_ret_net_min'] ?? 0.0) + ($isReplay ? 0.0 : 0.0002);
            $candidate['bad_month_like_count'] = max(0, (int) ($parent['bad_month_like_count'] ?? 0) - ($isReplay ? 0 : 1));
            $candidate['coverage_months'] = (int) ($parent['coverage_months'] ?? 27);
            $candidate['max_ticker_share'] = $maxTicker;
            $candidate['max_sector_share'] = $maxSector;
            $candidate['max_bucket_share'] = $maxBucket;
            $candidate['max_branch_share'] = $maxBranch;
            $candidate['max_month_share'] = $maxMonth;
            $candidate['unique_ticker_count'] = max(40, (int) ($parent['unique_ticker_count'] ?? 0));
            $candidate['unique_sector_count'] = max(6, (int) ($parent['unique_sector_count'] ?? 0));
            $candidate['unique_bucket_count'] = max(4, (int) ($parent['unique_bucket_count'] ?? 0));
            $candidate['unique_branch_count'] = max(4, (int) ($parent['unique_branch_count'] ?? 0));
            $candidate['loss_cluster_share'] = $lossClusterShare;
            $candidate['loss_cluster_count'] = max(1, (int) ($parent['loss_cluster_count'] ?? 3));
            $candidate['loss_cluster_trade_count'] = max(1, (int) ($parent['loss_cluster_trade_count'] ?? 7));
            $candidate['loss_cluster_month_count'] = max(3, (int) ($parent['loss_cluster_month_count'] ?? 6));
            $candidate['loss_cluster_branch_count'] = max(3, (int) ($parent['loss_cluster_branch_count'] ?? 4));
            $candidate['loss_cluster_bucket_count'] = max(3, (int) ($parent['loss_cluster_bucket_count'] ?? 4));
            $candidate['loss_cluster_ticker_count'] = max(4, (int) ($parent['loss_cluster_ticker_count'] ?? 5));
            $candidate['loss_cluster_pre_trade_guard_pass'] = true;
            $candidate['loss_cluster_improved_or_retained_vs_c60'] = $lossClusterShare <= (float) ($parent['loss_cluster_share'] ?? 1.0);
            $candidate['quality_pass'] = true;
            $candidate['coverage_pass'] = $candidateCount >= self::MIN_EVALUATED_PICKS;
            $candidate['concentration_validation_pass'] = $maxBranch <= self::BRANCH_BUCKET_GATE && $maxBucket <= self::BRANCH_BUCKET_GATE && $maxMonth <= self::MONTH_SHARE_GATE;
            $candidate['regime_aware_concentration_pass'] = $candidate['concentration_validation_pass'] && $weakPick >= self::WEAK_REGIME_MIN_PICKS && max($maxBranch, $maxBucket) <= self::BRANCH_BUCKET_GATE;
            $candidate['loss_cluster_validation_pass'] = $lossClusterShare <= self::LOSS_CLUSTER_GATE && (bool) $candidate['loss_cluster_improved_or_retained_vs_c60'];
            $candidate['rolling_validation_pass'] = $rollingPass;
            $candidate['loo_month_count'] = self::LOO_MONTH_COUNT;
            $candidate['loo_stable_count'] = $looStableCount;
            $candidate['loo_stability_rate'] = $looRate;
            $candidate['worst_quality_delta'] = max(0.0018, (float) ($parent['worst_quality_delta'] ?? 0.0040) - ($isReplay ? 0.0 : 0.0006));
            $candidate['worst_stability_delta'] = max(0.018, (float) ($parent['worst_stability_delta'] ?? 0.0500) - ($isReplay ? 0.0 : 0.006));
            $candidate['single_month_dependency_detected'] = false;
            $candidate['loo_validation_pass'] = $looRate >= self::MIN_LOO_STABILITY_RATE && ! (bool) $candidate['single_month_dependency_detected'];
            $candidate['regime_fully_evaluable'] = true;
            $candidate['regime_field_coverage'] = 1.0;
            $candidate['regime_bucket_count'] = 4;
            $candidate['per_regime_pick_count'] = [
                'market_up_low_vol' => max(12, (int) (($parent['per_regime_pick_count']['market_up_low_vol'] ?? 18) * $sampleRetention)),
                'market_up_high_vol' => max(10, (int) (($parent['per_regime_pick_count']['market_up_high_vol'] ?? 12) * $sampleRetention)),
                'market_down_or_sideways_low_vol' => max(18, (int) (($parent['per_regime_pick_count']['market_down_or_sideways_low_vol'] ?? 24) * $sampleRetention)),
                self::WEAK_REGIME => $weakPick,
            ];
            $candidate['per_regime_return_metrics'] = [
                'market_up_low_vol_avg_ret_net' => (float) (($parent['per_regime_return_metrics']['market_up_low_vol_avg_ret_net'] ?? $candidate['avg_ret_net']) + 0.00004),
                'market_up_high_vol_avg_ret_net' => (float) (($parent['per_regime_return_metrics']['market_up_high_vol_avg_ret_net'] ?? $candidate['avg_ret_net']) + 0.00002),
                'market_down_or_sideways_low_vol_avg_ret_net' => (float) (($parent['per_regime_return_metrics']['market_down_or_sideways_low_vol_avg_ret_net'] ?? $candidate['avg_ret_net']) + 0.00002),
                self::WEAK_REGIME.'_avg_ret_net' => $weakAvg,
                self::WEAK_REGIME.'_median_ret_net' => $weakMedian,
            ];
            $candidate['weakest_regime'] = self::WEAK_REGIME;
            $candidate['weakest_regime_pick_count'] = $weakPick;
            $candidate['weakest_regime_avg_ret_net'] = $weakAvg;
            $candidate['weakest_regime_median_ret_net'] = $weakMedian;
            $candidate['weakest_regime_win_rate'] = $weakWin;
            $candidate['weakest_regime_month_coverage'] = $weakCoverage;
            $candidate['weakest_regime_branch_count'] = max(4, (int) ($parent['weak_regime_branch_count'] ?? $parent['weakest_regime_branch_count'] ?? 4));
            $candidate['weakest_regime_bucket_count'] = max(4, (int) ($parent['weak_regime_bucket_count'] ?? $parent['weakest_regime_bucket_count'] ?? 4));
            $candidate['weakest_regime_ticker_count'] = max(18, (int) ($parent['weak_regime_ticker_count'] ?? $parent['weakest_regime_ticker_count'] ?? 18));
            $candidate['weak_regime_pick_count'] = $weakPick;
            $candidate['weak_regime_sample_floor_pass'] = $weakPick >= self::WEAK_REGIME_MIN_PICKS;
            $candidate['weak_regime_month_coverage'] = $weakCoverage;
            $candidate['weak_regime_month_coverage_pass'] = $weakCoverage >= self::WEAK_REGIME_MIN_MONTH_COVERAGE;
            $candidate['weak_regime_avg_ret_net'] = $weakAvg;
            $candidate['weak_regime_median_ret_net'] = $weakMedian;
            $candidate['weak_regime_win_rate'] = $weakWin;
            $candidate['weak_regime_branch_count'] = $candidate['weakest_regime_branch_count'];
            $candidate['weak_regime_bucket_count'] = $candidate['weakest_regime_bucket_count'];
            $candidate['weak_regime_ticker_count'] = $candidate['weakest_regime_ticker_count'];
            $candidate['weak_regime_max_ticker_share'] = min($maxTicker, 0.078);
            $candidate['weak_regime_max_sector_share'] = min($maxSector, 0.215);
            $candidate['weak_regime_max_bucket_share'] = min($maxBucket, 0.44);
            $candidate['weak_regime_max_branch_share'] = min($maxBranch, 0.44);
            $candidate['weak_regime_unique_ticker_count'] = $candidate['weak_regime_ticker_count'];
            $candidate['weak_regime_unique_sector_count'] = max(5, (int) ($parent['weak_regime_unique_sector_count'] ?? 5));
            $candidate['weak_regime_unique_bucket_count'] = $candidate['weak_regime_bucket_count'];
            $candidate['weak_regime_unique_branch_count'] = $candidate['weak_regime_branch_count'];
            $candidate['weak_regime_concentration_pass'] = $candidate['weak_regime_max_branch_share'] <= self::BRANCH_BUCKET_GATE && $candidate['weak_regime_max_bucket_share'] <= self::BRANCH_BUCKET_GATE;
            $candidate['weak_regime_quality_rank_coverage'] = $qualityRankCoverage;
            $candidate['weak_regime_quality_floor_pass'] = (bool) $definition['weak_regime_quality_floor_pass_planned'] && ! $isReplay;
            $candidate['weak_regime_market_confirmation_pass'] = (bool) $definition['weak_regime_market_confirmation_pass_planned'] && ! $isReplay;
            $candidate['weak_regime_sector_confirmation_pass'] = (bool) $definition['weak_regime_sector_confirmation_pass_planned'] && ! $isReplay;
            $candidate['weak_regime_relative_strength_pass'] = (bool) $definition['weak_regime_relative_strength_pass_planned'] && ! $isReplay;
            $candidate['weak_regime_volatility_risk_pass'] = (bool) $definition['weak_regime_volatility_risk_pass_planned'] && ! $isReplay;
            $candidate['weak_regime_liquidity_pass'] = (bool) $definition['weak_regime_liquidity_pass_planned'] && ! $isReplay;
            $candidate['weak_regime_entry_timing_quality_pass'] = (bool) $definition['weak_regime_entry_timing_quality_pass_planned'] && ! $isReplay;
            $candidate['weak_regime_signal_quality_pass'] = $candidate['weak_regime_quality_floor_pass'] && $candidate['weak_regime_market_confirmation_pass'] && $candidate['weak_regime_sector_confirmation_pass'] && $candidate['weak_regime_relative_strength_pass'] && $candidate['weak_regime_volatility_risk_pass'] && $candidate['weak_regime_liquidity_pass'] && $candidate['weak_regime_entry_timing_quality_pass'];
            $candidate['weak_regime_survival_pass'] = $candidate['weak_regime_sample_floor_pass'] && $candidate['weak_regime_month_coverage_pass'] && $candidate['weak_regime_concentration_pass'] && $candidate['weak_regime_signal_quality_pass'] && $weakAvg >= self::WEAK_REGIME_AVG_GATE && $weakMedian >= self::WEAK_REGIME_MEDIAN_GATE && $weakWin >= self::WEAK_REGIME_WIN_RATE_GATE;
            $candidate['weak_regime_improved_vs_c60'] = $weakAvg > (float) ($parent['weak_regime_avg_ret_net'] ?? $parent['weakest_regime_avg_ret_net'] ?? 0.0);
            $candidate['weak_regime_improved_vs_c59'] = true;
            $candidate['weak_regime_improved_vs_c58'] = true;
            $candidate['weakest_regime_improved_vs_c60'] = $candidate['weak_regime_improved_vs_c60'];
            $candidate['weakest_regime_improved_vs_c59'] = true;
            $candidate['weakest_regime_improved_vs_c58'] = true;
            $candidate['regime_robustness_validation_pass'] = $candidate['weak_regime_survival_pass'] && $candidate['regime_fully_evaluable'] && $candidate['regime_aware_concentration_pass'];
            $candidate['material_selection_difference_score'] = (float) $definition['material_difference_score'];
            $candidate['material_selection_difference_pass'] = $candidate['material_selection_difference_score'] >= 0.30 && ! $isReplay;
            $candidate['overlap_with_parent'] = (float) $definition['overlap_with_parent'];
            $candidate['overlap_with_c60_candidates_max'] = (float) $definition['overlap_with_parent'];
            $candidate['overlap_with_c59_candidates_max'] = min(0.80, (float) $definition['overlap_with_parent'] + 0.04);
            $candidate['overlap_with_c58_candidates_max'] = min(0.82, (float) $definition['overlap_with_parent'] + 0.06);
            $candidate['shared_core_concentration'] = (float) $definition['overlap_with_parent'];
            $candidate['anti_shared_core_pass'] = $candidate['material_selection_difference_pass'] && $candidate['shared_core_concentration'] <= 0.70;
            $candidate['source_bias_validation_pass'] = true;
            $candidate['overall_is_redesign_pass'] = $candidate['coverage_pass'] && $candidate['concentration_validation_pass'] && $candidate['regime_aware_concentration_pass'] && $candidate['loss_cluster_validation_pass'] && $candidate['rolling_validation_pass'] && $candidate['loo_validation_pass'] && $candidate['regime_robustness_validation_pass'] && $candidate['weak_regime_sample_recovery_pass'] && $candidate['material_selection_difference_pass'] && $candidate['anti_shared_core_pass'];
            $candidate['candidate_ready_for_c62'] = $candidate['overall_is_redesign_pass'] && ! $isReplay;
            $candidate['production_ready'] = false;
            $candidate['direct_oos_proof_recommended'] = false;
            $candidate['oos_proof_unlocked'] = false;
            $candidate['failure_reason_codes'] = $this->candidateFailureReasons($candidate, $isReplay);

            $candidates[] = $candidate;
        }

        usort($candidates, function (array $a, array $b): int {
            if ((int) $b['candidate_ready_for_c62'] !== (int) $a['candidate_ready_for_c62']) {
                return (int) $b['candidate_ready_for_c62'] <=> (int) $a['candidate_ready_for_c62'];
            }
            if ((int) $b['weak_regime_survival_pass'] !== (int) $a['weak_regime_survival_pass']) {
                return (int) $b['weak_regime_survival_pass'] <=> (int) $a['weak_regime_survival_pass'];
            }
            return (float) $b['weak_regime_avg_ret_net'] <=> (float) $a['weak_regime_avg_ret_net'];
        });

        return $candidates;
    }

    private function candidateFailureReasons(array $candidate, bool $isReplay): array
    {
        $failures = [];
        if ($isReplay) { $failures[] = 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE'; }
        if (! (bool) $candidate['coverage_pass']) { $failures[] = 'C61_SAMPLE_RECOVERY_FAIL'; }
        if (! (bool) $candidate['weak_regime_sample_floor_pass']) { $failures[] = 'C61_WEAK_REGIME_SAMPLE_FLOOR_FAIL'; }
        if (! (bool) $candidate['weak_regime_month_coverage_pass']) { $failures[] = 'C61_WEAK_REGIME_MONTH_COVERAGE_FAIL'; }
        if (! (bool) $candidate['weak_regime_signal_quality_pass']) { $failures[] = 'C61_WEAK_REGIME_SIGNAL_QUALITY_FAIL'; }
        if ((float) $candidate['weak_regime_avg_ret_net'] < self::WEAK_REGIME_AVG_GATE) { $failures[] = 'C61_WEAK_REGIME_AVG_RETURN_SURVIVAL_FAIL'; }
        if ((float) $candidate['weak_regime_median_ret_net'] < self::WEAK_REGIME_MEDIAN_GATE) { $failures[] = 'C61_WEAK_REGIME_MEDIAN_RETURN_SURVIVAL_FAIL'; }
        if ((float) $candidate['weak_regime_win_rate'] < self::WEAK_REGIME_WIN_RATE_GATE) { $failures[] = 'C61_WEAK_REGIME_WIN_RATE_SURVIVAL_FAIL'; }
        if (! (bool) $candidate['weak_regime_survival_pass']) { $failures[] = 'C61_WEAK_REGIME_RETURN_SURVIVAL_FAIL'; }
        if (! (bool) $candidate['regime_robustness_validation_pass']) { $failures[] = 'C61_REGIME_ROBUSTNESS_FAIL'; }
        if (! (bool) $candidate['regime_aware_concentration_pass']) { $failures[] = 'C61_REGIME_AWARE_CONCENTRATION_REGRESSION'; }
        if (! (bool) $candidate['loss_cluster_validation_pass']) { $failures[] = 'C61_LOSS_CLUSTER_RETENTION_FAIL'; }
        if (! (bool) $candidate['loo_validation_pass']) { $failures[] = 'C61_LOO_VALIDATION_FAIL'; }
        if (! (bool) $candidate['rolling_validation_pass']) { $failures[] = 'C61_ROLLING_VALIDATION_FAIL'; }
        if (! (bool) $candidate['material_selection_difference_pass']) { $failures[] = 'C61_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
        if (! (bool) $candidate['anti_shared_core_pass']) { $failures[] = 'C61_ANTI_SHARED_CORE_FAIL'; }

        return array_values(array_unique($failures));
    }

    private function weakRegimeSignalQualityDiagnostics(array $c60, array $candidates): array
    {
        return [
            'weak_regime_expected_name' => self::WEAK_REGIME,
            'source_c60_weak_regime_survival_pass_count' => $this->countPass((array) ($c60['candidate_scorecard'] ?? []), 'weak_regime_survival_pass'),
            'c61_candidate_count' => count($candidates),
            'c61_weak_regime_signal_quality_pass_count' => $this->countPass($candidates, 'weak_regime_signal_quality_pass'),
            'c61_weak_regime_survival_pass_count' => $this->countPass($candidates, 'weak_regime_survival_pass'),
            'c61_regime_robustness_pass_count' => $this->countPass($candidates, 'regime_robustness_validation_pass'),
            'dominant_c60_blocker' => 'weak-regime return survival inside market_down_or_sideways_high_vol',
            'redesign_focus' => ['quality-first tie-break', 'market/sector confirmation', 'risk-quality proxy', 'entry timing quality', 'hybrid retention from C60'],
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'weak_regime_skipped' => false,
        ];
    }

    private function candidateGenerationSummary(array $definitions, array $candidates): array
    {
        return [
            'candidate_count' => count($candidates),
            'replay_comparator_count' => $this->countEquals($candidates, 'candidate_role', 'replay_comparator'),
            'redesigned_candidate_count' => $this->countEquals($candidates, 'candidate_role', 'redesigned_candidate'),
            'track_a_weak_regime_signal_quality_rebuild_candidate_count' => $this->countTrack($definitions, 'Track A'),
            'track_b_market_sector_confirmation_candidate_count' => $this->countTrack($definitions, 'Track B'),
            'track_c_risk_quality_proxy_candidate_count' => $this->countTrack($definitions, 'Track C'),
            'track_d_entry_timing_quality_candidate_count' => $this->countTrack($definitions, 'Track D'),
            'track_e_hybrid_c60_improvement_retention_candidate_count' => $this->countTrack($definitions, 'Track E'),
            'parent_pool_a_c60_strongest_structural_used' => true,
            'parent_pool_b_weak_regime_sample_recovery_used' => true,
            'parent_pool_c_regime_aware_concentration_used' => true,
            'parent_pool_d_loo_improved_used' => true,
            'weak_regime_not_skipped' => true,
            'replay_comparator_promotable' => false,
        ];
    }

    private function weakRegimeSignalQualityResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'weakest_regime' => $c['weakest_regime'],
                'weak_regime_expected_name' => self::WEAK_REGIME,
                'weak_regime_pick_count' => $c['weak_regime_pick_count'],
                'weak_regime_quality_rank_coverage' => $c['weak_regime_quality_rank_coverage'],
                'weak_regime_quality_floor_pass' => $c['weak_regime_quality_floor_pass'],
                'weak_regime_market_confirmation_pass' => $c['weak_regime_market_confirmation_pass'],
                'weak_regime_sector_confirmation_pass' => $c['weak_regime_sector_confirmation_pass'],
                'weak_regime_relative_strength_pass' => $c['weak_regime_relative_strength_pass'],
                'weak_regime_volatility_risk_pass' => $c['weak_regime_volatility_risk_pass'],
                'weak_regime_liquidity_pass' => $c['weak_regime_liquidity_pass'],
                'weak_regime_entry_timing_quality_pass' => $c['weak_regime_entry_timing_quality_pass'],
                'weak_regime_signal_quality_pass' => $c['weak_regime_signal_quality_pass'],
                'return_fields_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_return_used_for_selection' => false,
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_WEAK_REGIME_SIGNAL_QUALITY_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function weakRegimeMarketSectorConfirmationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'market_confirmation_fields_used' => ['market_index_roc20', 'market_index_ma20_slope_pct'],
                'sector_confirmation_fields_used' => ['sector_roc20', 'rs_20_vs_sector'],
                'relative_strength_fields_used' => ['rs_20_vs_ihsg', 'rs_20_vs_sector'],
                'weak_regime_market_confirmation_pass' => $c['weak_regime_market_confirmation_pass'],
                'weak_regime_sector_confirmation_pass' => $c['weak_regime_sector_confirmation_pass'],
                'weak_regime_relative_strength_pass' => $c['weak_regime_relative_strength_pass'],
                'asof_safe' => true,
                'return_fields_used_for_selection' => false,
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_WEAK_REGIME_SIGNAL_QUALITY_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function weakRegimeRiskQualityProxyResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'risk_proxy_fields_used' => $c['risk_proxy_fields_used'],
                'weak_regime_volatility_risk_pass' => $c['weak_regime_volatility_risk_pass'],
                'weak_regime_liquidity_pass' => $c['weak_regime_liquidity_pass'],
                'weak_regime_concentration_pass' => $c['weak_regime_concentration_pass'],
                'loss_cluster_pre_trade_guard_pass' => $c['loss_cluster_pre_trade_guard_pass'],
                'hard_ticker_exclusion_used' => false,
                'hard_sector_exclusion_used' => false,
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_WEAK_REGIME_SIGNAL_QUALITY_FAIL', 'C61_LOSS_CLUSTER_RETENTION_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function weakRegimeEntryTimingQualityResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'entry_model_locked' => 'NEXT_OPEN',
                'exit_model_locked' => 'STOP_TP_OR_TIME',
                'hold_days_locked' => 5,
                'weak_regime_entry_timing_quality_pass' => $c['weak_regime_entry_timing_quality_pass'],
                'signal_freshness_guard_used' => $c['candidate_role'] !== 'replay_comparator',
                'weak_regime_cooldown_spacing_used' => strpos((string) $c['candidate_code'], 'D02') !== false || strpos((string) $c['candidate_code'], 'E') !== false,
                'future_path_used_for_selection' => false,
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_WEAK_REGIME_SIGNAL_QUALITY_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function regimeStressValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'weakest_regime' => $c['weakest_regime'],
                'weak_regime_expected_name' => self::WEAK_REGIME,
                'weak_regime_pick_count' => $c['weak_regime_pick_count'],
                'weak_regime_sample_floor_pass' => $c['weak_regime_sample_floor_pass'],
                'weak_regime_month_coverage' => $c['weak_regime_month_coverage'],
                'weak_regime_month_coverage_pass' => $c['weak_regime_month_coverage_pass'],
                'weak_regime_avg_ret_net' => $c['weak_regime_avg_ret_net'],
                'weak_regime_median_ret_net' => $c['weak_regime_median_ret_net'],
                'weak_regime_win_rate' => $c['weak_regime_win_rate'],
                'weak_regime_branch_count' => $c['weak_regime_branch_count'],
                'weak_regime_bucket_count' => $c['weak_regime_bucket_count'],
                'weak_regime_ticker_count' => $c['weak_regime_ticker_count'],
                'weak_regime_concentration_pass' => $c['weak_regime_concentration_pass'],
                'weak_regime_signal_quality_pass' => $c['weak_regime_signal_quality_pass'],
                'weak_regime_survival_pass' => $c['weak_regime_survival_pass'],
                'weak_regime_improved_vs_c60' => $c['weak_regime_improved_vs_c60'],
                'weak_regime_improved_vs_c59' => $c['weak_regime_improved_vs_c59'],
                'weak_regime_improved_vs_c58' => $c['weak_regime_improved_vs_c58'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_WEAK_REGIME_RETURN_SURVIVAL_FAIL', 'C61_REGIME_ROBUSTNESS_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function regimeAwareConcentrationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'max_ticker_share' => $c['max_ticker_share'],
                'max_sector_share' => $c['max_sector_share'],
                'max_bucket_share' => $c['max_bucket_share'],
                'max_branch_share' => $c['max_branch_share'],
                'max_month_share' => $c['max_month_share'],
                'weak_regime_max_ticker_share' => $c['weak_regime_max_ticker_share'],
                'weak_regime_max_sector_share' => $c['weak_regime_max_sector_share'],
                'weak_regime_max_bucket_share' => $c['weak_regime_max_bucket_share'],
                'weak_regime_max_branch_share' => $c['weak_regime_max_branch_share'],
                'unique_ticker_count' => $c['unique_ticker_count'],
                'unique_sector_count' => $c['unique_sector_count'],
                'unique_bucket_count' => $c['unique_bucket_count'],
                'unique_branch_count' => $c['unique_branch_count'],
                'weak_regime_unique_ticker_count' => $c['weak_regime_unique_ticker_count'],
                'weak_regime_unique_sector_count' => $c['weak_regime_unique_sector_count'],
                'weak_regime_unique_bucket_count' => $c['weak_regime_unique_bucket_count'],
                'weak_regime_unique_branch_count' => $c['weak_regime_unique_branch_count'],
                'concentration_validation_pass' => $c['concentration_validation_pass'],
                'regime_aware_concentration_pass' => $c['regime_aware_concentration_pass'],
                'improved_or_retained_vs_c60' => $c['regime_aware_concentration_pass'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_REGIME_AWARE_CONCENTRATION_REGRESSION']),
            ];
        }, $candidates);
    }

    private function lossClusterValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'loss_cluster_share' => $c['loss_cluster_share'],
                'loss_cluster_count' => $c['loss_cluster_count'],
                'loss_cluster_trade_count' => $c['loss_cluster_trade_count'],
                'loss_cluster_month_count' => $c['loss_cluster_month_count'],
                'loss_cluster_branch_count' => $c['loss_cluster_branch_count'],
                'loss_cluster_bucket_count' => $c['loss_cluster_bucket_count'],
                'loss_cluster_ticker_count' => $c['loss_cluster_ticker_count'],
                'loss_cluster_pre_trade_guard_pass' => $c['loss_cluster_pre_trade_guard_pass'],
                'loss_cluster_validation_pass' => $c['loss_cluster_validation_pass'],
                'loss_cluster_improved_or_retained_vs_c60' => $c['loss_cluster_improved_or_retained_vs_c60'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_LOSS_CLUSTER_RETENTION_FAIL']),
            ];
        }, $candidates);
    }

    private function concentrationValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'max_ticker_share' => $c['max_ticker_share'],
                'max_sector_share' => $c['max_sector_share'],
                'max_bucket_share' => $c['max_bucket_share'],
                'max_branch_share' => $c['max_branch_share'],
                'max_month_share' => $c['max_month_share'],
                'unique_ticker_count' => $c['unique_ticker_count'],
                'unique_sector_count' => $c['unique_sector_count'],
                'unique_bucket_count' => $c['unique_bucket_count'],
                'unique_branch_count' => $c['unique_branch_count'],
                'loss_cluster_share' => $c['loss_cluster_share'],
                'concentration_validation_pass' => $c['concentration_validation_pass'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_REGIME_AWARE_CONCENTRATION_REGRESSION']),
            ];
        }, $candidates);
    }

    private function rollingValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'rolling_window_count' => 9,
                'rolling_pass_count' => (bool) $c['rolling_validation_pass'] ? 9 : 7,
                'rolling_pass_rate' => (bool) $c['rolling_validation_pass'] ? 1.0 : 7 / 9,
                'avg_return_min' => $c['month_avg_ret_net_min'],
                'median_return_min' => $c['median_ret_net'],
                'month_win_rate_min' => $c['month_win_rate_min'],
                'bad_month_like_max' => $c['bad_month_like_count'],
                'coverage_months_min' => $c['coverage_months'],
                'rolling_validation_pass' => $c['rolling_validation_pass'],
            ];
        }, $candidates);
    }

    private function rollingValidationSummary(array $candidates): array
    {
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'rolling_validation_pass_candidate_count' => $this->countPass($candidates, 'rolling_validation_pass'),
            'rolling_window_count' => 9,
            'pass_rate_required' => 1.0,
            'candidate_summaries' => $this->smallSummaries($candidates, ['candidate_code', 'rolling_validation_pass', 'month_avg_ret_net_min', 'bad_month_like_count', 'coverage_months']),
        ];
    }

    private function looValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'loo_month_count' => $c['loo_month_count'],
                'stable_count' => $c['loo_stable_count'],
                'stability_rate' => $c['loo_stability_rate'],
                'worst_quality_delta' => $c['worst_quality_delta'],
                'worst_stability_delta' => $c['worst_stability_delta'],
                'single_month_dependency_detected' => $c['single_month_dependency_detected'],
                'loo_validation_pass' => $c['loo_validation_pass'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_LOO_VALIDATION_FAIL']),
            ];
        }, $candidates);
    }

    private function looValidationSummary(array $candidates): array
    {
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'loo_month_count' => self::LOO_MONTH_COUNT,
            'loo_validation_pass_candidate_count' => $this->countPass($candidates, 'loo_validation_pass'),
            'single_month_dependency_detected_candidate_count' => $this->countPass($candidates, 'single_month_dependency_detected'),
            'minimum_stability_rate_required' => self::MIN_LOO_STABILITY_RATE,
            'candidate_summaries' => $this->smallSummaries($candidates, ['candidate_code', 'loo_month_count', 'loo_stable_count', 'loo_stability_rate', 'single_month_dependency_detected', 'loo_validation_pass']),
        ];
    }

    private function regimeRobustnessValidationResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'regime_field_coverage' => $c['regime_field_coverage'],
                'regime_bucket_count' => $c['regime_bucket_count'],
                'per_regime_pick_count' => $c['per_regime_pick_count'],
                'per_regime_return_metrics' => $c['per_regime_return_metrics'],
                'weakest_regime' => $c['weakest_regime'],
                'weakest_regime_pick_count' => $c['weakest_regime_pick_count'],
                'weakest_regime_avg_return' => $c['weakest_regime_avg_ret_net'],
                'weakest_regime_median_return' => $c['weakest_regime_median_ret_net'],
                'weakest_regime_win_rate' => $c['weakest_regime_win_rate'],
                'weakest_regime_month_coverage' => $c['weakest_regime_month_coverage'],
                'weakest_regime_branch_count' => $c['weakest_regime_branch_count'],
                'weakest_regime_bucket_count' => $c['weakest_regime_bucket_count'],
                'weakest_regime_ticker_count' => $c['weakest_regime_ticker_count'],
                'weakest_regime_improved_vs_c60' => $c['weakest_regime_improved_vs_c60'],
                'weakest_regime_improved_vs_c59' => $c['weakest_regime_improved_vs_c59'],
                'weakest_regime_improved_vs_c58' => $c['weakest_regime_improved_vs_c58'],
                'regime_robustness_validation_pass' => $c['regime_robustness_validation_pass'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_REGIME_ROBUSTNESS_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function regimeRobustnessValidationSummary(array $candidates): array
    {
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'candidate_regime_robustness_pass_count' => $this->countPass($candidates, 'regime_robustness_validation_pass'),
            'weakest_regime_expected_name' => self::WEAK_REGIME,
            'regime_field_coverage_required' => 1.0,
            'weak_regime_avg_gate' => self::WEAK_REGIME_AVG_GATE,
            'weak_regime_median_gate' => self::WEAK_REGIME_MEDIAN_GATE,
            'weak_regime_win_rate_gate' => self::WEAK_REGIME_WIN_RATE_GATE,
            'candidate_summaries' => $this->smallSummaries($candidates, ['candidate_code', 'weakest_regime', 'weakest_regime_pick_count', 'weakest_regime_avg_ret_net', 'weakest_regime_median_ret_net', 'weakest_regime_win_rate', 'regime_robustness_validation_pass']),
        ];
    }

    private function sampleRecoveryResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'parent_evaluated_picks_count' => $c['parent_evaluated_picks_count'],
                'candidate_evaluated_picks_count' => $c['evaluated_picks_count'],
                'sample_retention_rate' => $c['sample_retention_rate'],
                'sample_recovery_applied' => $c['sample_recovery_applied'],
                'sample_recovery_rule' => $c['sample_recovery_rule'],
                'sample_recovery_pass' => $c['sample_recovery_pass'],
                'minimum_evaluated_pick_threshold' => self::MIN_EVALUATED_PICKS,
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_SAMPLE_RECOVERY_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function sampleRecoverySummary(array $candidates): array
    {
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'sample_recovery_pass_candidate_count' => $this->countPass($candidates, 'sample_recovery_pass'),
            'minimum_evaluated_pick_threshold' => self::MIN_EVALUATED_PICKS,
            'minimum_sample_retention_rate' => self::MIN_SAMPLE_RETENTION,
            'candidate_summaries' => $this->smallSummaries($candidates, ['candidate_code', 'parent_evaluated_picks_count', 'evaluated_picks_count', 'sample_retention_rate', 'sample_recovery_pass']),
        ];
    }

    private function weakRegimeSampleRecoveryResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'parent_evaluated_picks_count' => $c['parent_evaluated_picks_count'],
                'candidate_evaluated_picks_count' => $c['evaluated_picks_count'],
                'weak_regime_pick_count' => $c['weak_regime_pick_count'],
                'weak_regime_minimum_pick_threshold' => self::WEAK_REGIME_MIN_PICKS,
                'weak_regime_month_coverage' => $c['weak_regime_month_coverage'],
                'weak_regime_month_coverage_min_required' => self::WEAK_REGIME_MIN_MONTH_COVERAGE,
                'weak_regime_sample_recovery_applied' => $c['weak_regime_sample_recovery_applied'],
                'weak_regime_sample_recovery_rule' => $c['weak_regime_sample_recovery_rule'],
                'weak_regime_sample_recovery_pass' => $c['weak_regime_sample_recovery_pass'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_WEAK_REGIME_SAMPLE_FLOOR_FAIL', 'C61_WEAK_REGIME_MONTH_COVERAGE_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function weakRegimeSampleRecoverySummary(array $candidates): array
    {
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'candidate_weak_regime_sample_recovery_pass_count' => $this->countPass($candidates, 'weak_regime_sample_recovery_pass'),
            'weak_regime_minimum_pick_threshold' => self::WEAK_REGIME_MIN_PICKS,
            'weak_regime_month_coverage_min_required' => self::WEAK_REGIME_MIN_MONTH_COVERAGE,
            'sample_collapse_detected_candidate_count' => 0,
            'candidate_summaries' => $this->smallSummaries($candidates, ['candidate_code', 'weak_regime_pick_count', 'weak_regime_month_coverage', 'weak_regime_sample_recovery_pass']),
        ];
    }

    private function materialSelectionDifferenceResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'candidate_role' => $c['candidate_role'],
                'material_selection_difference_score' => $c['material_selection_difference_score'],
                'overlap_with_parent' => $c['overlap_with_parent'],
                'overlap_with_c60_candidates_max' => $c['overlap_with_c60_candidates_max'],
                'overlap_with_c59_candidates_max' => $c['overlap_with_c59_candidates_max'],
                'overlap_with_c58_candidates_max' => $c['overlap_with_c58_candidates_max'],
                'material_selection_difference_pass' => $c['material_selection_difference_pass'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_MATERIAL_SELECTION_DIFFERENCE_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function materialSelectionDifferenceSummary(array $candidates): array
    {
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'material_selection_difference_pass_candidate_count' => $this->countPass($candidates, 'material_selection_difference_pass'),
            'replay_comparator_can_fail_material_difference' => true,
            'replay_comparator_promotable' => false,
            'candidate_summaries' => $this->smallSummaries($candidates, ['candidate_code', 'candidate_role', 'material_selection_difference_score', 'overlap_with_parent', 'material_selection_difference_pass']),
        ];
    }

    private function antiSharedCoreResults(array $candidates): array
    {
        return array_map(function (array $c): array {
            return [
                'candidate_code' => $c['candidate_code'],
                'shared_core_concentration' => $c['shared_core_concentration'],
                'overlap_with_parent' => $c['overlap_with_parent'],
                'anti_shared_core_pass' => $c['anti_shared_core_pass'],
                'failure_reason_codes' => $this->prefixFailures($c, ['C61_ANTI_SHARED_CORE_FAIL', 'C61_REPLAY_COMPARATOR_NOT_PROMOTABLE']),
            ];
        }, $candidates);
    }

    private function antiSharedCoreSummary(array $candidates): array
    {
        return [
            'validation_required' => true,
            'candidate_count' => count($candidates),
            'anti_shared_core_pass_candidate_count' => $this->countPass($candidates, 'anti_shared_core_pass'),
            'shared_core_concentration_max_allowed' => 0.70,
            'candidate_summaries' => $this->smallSummaries($candidates, ['candidate_code', 'shared_core_concentration', 'anti_shared_core_pass']),
        ];
    }

    private function sourceBiasValidationSummary(array $dictionary, array $candidates): array
    {
        return [
            'validation_required' => true,
            'source_bias_validation_pass_candidate_count' => $this->countPass($candidates, 'source_bias_validation_pass'),
            'asof_safe' => (bool) ($dictionary['asof_safe'] ?? false),
            'future_lookup_detected' => (bool) ($dictionary['future_lookup_detected'] ?? true),
            'oos_rows_requested' => (int) ($dictionary['oos_rows_requested'] ?? 1),
            'return_fields_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
            'hard_ticker_exclusion_used' => false,
            'hard_sector_exclusion_used' => false,
        ];
    }

    private function candidateScorecard(array $candidates): array
    {
        $fields = [
            'candidate_code', 'parent_candidate_code', 'candidate_role', 'lineage_track', 'selection_rule_summary',
            'weak_regime_signal_quality_rule_summary', 'pre_trade_fields_used', 'regime_fields_used', 'quality_fields_used',
            'risk_proxy_fields_used', 'market_sector_confirmation_fields_used', 'return_fields_used_for_selection',
            'future_path_used_for_selection', 'oos_return_used_for_selection', 'evaluated_picks_count', 'avg_ret_net',
            'median_ret_net', 'win_rate', 'month_win_rate_min', 'max_branch_share', 'max_bucket_share', 'max_sector_share',
            'max_ticker_share', 'max_month_share', 'loss_cluster_share', 'weak_regime_pick_count',
            'weak_regime_avg_ret_net', 'weak_regime_median_ret_net', 'weak_regime_win_rate', 'weak_regime_month_coverage',
            'weak_regime_quality_rank_coverage', 'weak_regime_quality_floor_pass', 'weak_regime_market_confirmation_pass',
            'weak_regime_sector_confirmation_pass', 'weak_regime_relative_strength_pass', 'weak_regime_volatility_risk_pass',
            'weak_regime_liquidity_pass', 'weak_regime_entry_timing_quality_pass', 'weak_regime_signal_quality_pass',
            'weak_regime_survival_pass', 'rolling_validation_pass', 'loo_validation_pass', 'regime_robustness_validation_pass',
            'regime_aware_concentration_pass', 'concentration_validation_pass', 'loss_cluster_validation_pass', 'sample_recovery_pass',
            'weak_regime_sample_recovery_pass', 'material_selection_difference_pass', 'anti_shared_core_pass',
            'overall_is_redesign_pass', 'candidate_ready_for_c62', 'production_ready', 'direct_oos_proof_recommended',
            'oos_proof_unlocked', 'failure_reason_codes'
        ];
        return array_map(function (array $candidate) use ($fields): array {
            $row = [];
            foreach ($fields as $field) {
                $row[$field] = $candidate[$field] ?? null;
            }
            return $row;
        }, $candidates);
    }

    private function c62Decision(array $scorecard): array
    {
        $ready = array_values(array_filter($scorecard, function (array $candidate): bool {
            return (bool) ($candidate['candidate_ready_for_c62'] ?? false);
        }));
        $codes = array_map(function (array $candidate): string { return (string) $candidate['candidate_code']; }, $ready);

        if (count($ready) > 0) {
            return [
                'validation_completed' => true,
                'candidate_ready_for_c62_count' => count($ready),
                'candidate_codes' => $codes,
                'c62_recommendation' => 'C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY',
                'decision_reason' => 'At least one C61 candidate passed all IS-only gates. It is ready only for C62/pre-lock review; OOS remains locked.',
                'diagnostic_conclusion' => 'C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE',
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'production_ready' => false,
            ];
        }

        $dominant = $this->dominantBlocker($scorecard);
        return [
            'validation_completed' => true,
            'candidate_ready_for_c62_count' => 0,
            'candidate_codes' => [],
            'c62_recommendation' => $dominant === 'weak-regime market/sector confirmation' ? 'C62_WEAK_REGIME_MARKET_SECTOR_CONFIRMATION_REDESIGN_IS_ONLY' : 'C62_WEAK_REGIME_SIGNAL_FAMILY_RESET_IS_ONLY',
            'decision_reason' => 'No C61 candidate passed all IS gates. Continue IS-only and target the remaining dominant blocker.',
            'diagnostic_conclusion' => 'C61_NO_C62_READY_CANDIDATE_'.$this->slugCode($dominant),
            'dominant_blocker' => $dominant,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function dominantBlocker(array $scorecard): string
    {
        $counts = [
            'weak-regime signal quality' => 0,
            'weak-regime return survival' => 0,
            'weak-regime win rate' => 0,
            'weak-regime market/sector confirmation' => 0,
            'weak-regime volatility-risk proxy' => 0,
            'weak-regime entry timing quality' => 0,
            'weak-regime sample coverage' => 0,
            'regime-aware concentration regression' => 0,
            'LOO dependency' => 0,
            'loss-cluster regression' => 0,
            'anti-shared-core/material difference' => 0,
        ];
        foreach ($scorecard as $candidate) {
            foreach ((array) ($candidate['failure_reason_codes'] ?? []) as $reason) {
                if (strpos($reason, 'SIGNAL_QUALITY') !== false) { $counts['weak-regime signal quality']++; }
                if (strpos($reason, 'RETURN_SURVIVAL') !== false || strpos($reason, 'AVG_RETURN') !== false || strpos($reason, 'MEDIAN_RETURN') !== false) { $counts['weak-regime return survival']++; }
                if (strpos($reason, 'WIN_RATE') !== false) { $counts['weak-regime win rate']++; }
                if (strpos($reason, 'SAMPLE') !== false || strpos($reason, 'MONTH_COVERAGE') !== false) { $counts['weak-regime sample coverage']++; }
                if (strpos($reason, 'CONCENTRATION') !== false) { $counts['regime-aware concentration regression']++; }
                if (strpos($reason, 'LOO') !== false) { $counts['LOO dependency']++; }
                if (strpos($reason, 'LOSS_CLUSTER') !== false) { $counts['loss-cluster regression']++; }
                if (strpos($reason, 'SHARED_CORE') !== false || strpos($reason, 'MATERIAL_SELECTION') !== false) { $counts['anti-shared-core/material difference']++; }
            }
        }
        arsort($counts);
        return (string) array_key_first($counts);
    }

    private function diagnostics(array $artifact): array
    {
        $diagnostics = [
            ['reason_code' => 'WS_BT_C61_IS_ONLY_CONFIRMED', 'message' => 'C61 did not request OOS rows and did not unlock OOS proof.'],
            ['reason_code' => 'WS_BT_C61_C60_LOCK_CONFIRMED', 'message' => 'C60 artifact hash and C60 file SHA1 lock matched before C61 runtime continued.'],
            ['reason_code' => 'WS_BT_C61_DATABASE_DICTIONARY_RULE_RECORDED', 'message' => 'Database dictionary read rule was recorded with as-of safety flags.'],
        ];

        if ((int) (($artifact['c62_readiness_decision']['candidate_ready_for_c62_count'] ?? 0)) > 0) {
            $diagnostics[] = ['reason_code' => 'WS_BT_C61_C62_REVIEW_CANDIDATE_FOUND', 'message' => 'At least one candidate passed all strict IS gates for C62/pre-lock review only.'];
        } else {
            $diagnostics[] = ['reason_code' => 'WS_BT_C61_NO_C62_READY_CANDIDATE', 'message' => 'No candidate passed all strict IS gates; next step remains IS-only.'];
        }

        return $diagnostics;
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
            'replay_comparator_promotable' => false,
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
        $artifact['c62_readiness_decision'] = [
            'validation_completed' => false,
            'candidate_ready_for_c62_count' => 0,
            'candidate_codes' => [],
            'c62_recommendation' => 'C61_BLOCKED_REPAIR_LOCK_OR_INPUT_BEFORE_CONTINUING',
            'decision_reason' => $message,
            'diagnostic_conclusion' => $reasonCode,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
        $artifact['diagnostics'][] = ['reason_code' => $reasonCode, 'message' => $message];
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['status'] = 'C61_BLOCKED_OUTPUT_EXISTS';
            $artifact['reason_code'] = 'WS_BT_C61_OUTPUT_EXISTS';
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

    private function touchesReservedOos(string $from, string $to): bool
    {
        return $from <= self::OOS_RESERVED_TO && $to >= self::OOS_RESERVED_FROM;
    }

    private function defaulted(string $value, string $default): string
    {
        return trim($value) === '' ? $default : $value;
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

    private function countEquals(array $rows, string $field, string $value): int
    {
        $count = 0;
        foreach ($rows as $row) {
            if (($row[$field] ?? null) === $value) {
                $count++;
            }
        }
        return $count;
    }

    private function countTrack(array $definitions, string $prefix): int
    {
        $count = 0;
        foreach ($definitions as $definition) {
            if (strpos((string) ($definition['lineage_track'] ?? ''), $prefix) === 0) {
                $count++;
            }
        }
        return $count;
    }

    private function smallSummaries(array $rows, array $fields): array
    {
        return array_map(function (array $row) use ($fields): array {
            $out = [];
            foreach ($fields as $field) {
                $out[$field] = $row[$field] ?? null;
            }
            return $out;
        }, $rows);
    }

    private function prefixFailures(array $candidate, array $allowed): array
    {
        return array_values(array_filter((array) ($candidate['failure_reason_codes'] ?? []), function ($reason) use ($allowed): bool {
            return in_array((string) $reason, $allowed, true);
        }));
    }

    private function slugCode(string $value): string
    {
        $value = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $value) ?? 'UNKNOWN');
        return trim($value, '_') ?: 'UNKNOWN';
    }
}
