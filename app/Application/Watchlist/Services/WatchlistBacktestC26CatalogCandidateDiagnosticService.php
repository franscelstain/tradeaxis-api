<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC26CatalogCandidateDiagnosticService
{
    public const ARTIFACT_TYPE = 'C26_CATALOG_CANDIDATE_DIAGNOSTIC';
    public const DEFAULT_C21_INPUT_PATH = 'storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json';
    public const DEFAULT_C23_INPUT_PATH = 'storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json';
    public const DEFAULT_C24_INPUT_PATH = 'storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json';
    public const DEFAULT_C25_INPUT_PATH = 'storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-all-param.json';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;

    public const C25_R09 = 'C25_G02_C23_R09_BASELINE_BRIDGE';
    public const C25_R15 = 'C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR';
    public const C25_R16 = 'C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR';
    public const C25_G13 = 'C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT';
    public const C25_G16 = 'C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT';
    public const C25_G21 = 'C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT';

    public static function diagnosticProfiles(): array
    {
        return [
            'C26_G00_CANONICAL_BASELINE' => ['family' => 'baseline', 'candidate_role' => 'canonical_baseline', 'source_profile_code' => 'C25_G00_CANONICAL_BASELINE', 'candidate_rule_code' => 'CANONICAL'],
            'C26_G01_C22_S06_SHADOW_BENCHMARK' => ['family' => 'shadow_benchmark', 'candidate_role' => 'shadow_benchmark', 'source_profile_code' => 'C25_G01_C22_S06_SHADOW_BENCHMARK', 'candidate_rule_code' => 'C22_S06_FIRST_PROFITABLE_CLOSE_EXIT'],
            'C26_G02_C23_R09_BASELINE_BRIDGE' => ['family' => 'r09_bridge', 'candidate_role' => 'baseline_bridge', 'source_profile_code' => self::C25_R09, 'candidate_rule_code' => 'C23_R09'],
            'C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE' => ['family' => 'primary_candidate', 'candidate_role' => 'primary_balanced_candidate', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G04_C25_G13_DEFENSIVE_DISTRIBUTION_COMPARATOR' => ['family' => 'defensive_comparator', 'candidate_role' => 'defensive_distribution_comparator', 'source_profile_code' => self::C25_G13, 'candidate_rule_code' => self::C25_G13],
            'C26_G05_C25_G16_NEXT_OPEN_DELAY_COMPARATOR' => ['family' => 'next_open_delay_comparator', 'candidate_role' => 'next_open_delay_comparator', 'source_profile_code' => self::C25_G16, 'candidate_rule_code' => self::C25_G16],
            'C26_G06_C23_R15_DOWNSIDE_COMPARATOR' => ['family' => 'downside_comparator', 'candidate_role' => 'downside_comparator', 'source_profile_code' => self::C25_R15, 'candidate_rule_code' => 'C23_R15'],
            'C26_G07_C23_R16_DOWNSIDE_COMPARATOR' => ['family' => 'downside_comparator', 'candidate_role' => 'downside_comparator', 'source_profile_code' => self::C25_R16, 'candidate_rule_code' => 'C23_R16'],
            'C26_G08_G21_WITH_PARAM_CONSISTENCY_GATE' => ['family' => 'readiness_gate', 'candidate_role' => 'g21_param_consistency_gate', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G09_G21_WITH_MONTH_STABILITY_GATE' => ['family' => 'readiness_gate', 'candidate_role' => 'g21_month_stability_gate', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G10_G21_WITH_BUCKET_STABILITY_GATE' => ['family' => 'readiness_gate', 'candidate_role' => 'g21_bucket_stability_gate', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G11_G21_WITH_RAW_OHLC_REQUIRED_GATE' => ['family' => 'readiness_gate', 'candidate_role' => 'g21_raw_ohlc_required_gate', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G12_G21_WITH_NO_DERIVED_MFE_MAE_DEPENDENCY_GATE' => ['family' => 'readiness_gate', 'candidate_role' => 'g21_no_derived_mfe_mae_dependency_gate', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G13_G21_VS_G13_DEFENSIVE_TIEBREAK' => ['family' => 'candidate_tiebreak', 'candidate_role' => 'g21_vs_g13_defensive_tiebreak', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G14_G21_VS_G16_DELAY_TIEBREAK' => ['family' => 'candidate_tiebreak', 'candidate_role' => 'g21_vs_g16_delay_tiebreak', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G15_G21_VS_R15_R16_DOWNSIDE_TIEBREAK' => ['family' => 'candidate_tiebreak', 'candidate_role' => 'g21_vs_r15_r16_downside_tiebreak', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
            'C26_G16_CATALOG_CANDIDATE_READINESS_SCORE' => ['family' => 'readiness_score', 'candidate_role' => 'catalog_candidate_readiness_score', 'source_profile_code' => self::C25_G21, 'candidate_rule_code' => self::C25_G21],
        ];
    }

    public function execute(string $c25InputPath = '', string $outputPath = '', array $options = []): array
    {
        $catalogCode = (string) ($options['catalog_code'] ?? self::DEFAULT_SOURCE_CATALOG_CODE);
        $fromDate = (string) ($options['from'] ?? WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $toDate = (string) ($options['to'] ?? WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $c21InputPath = trim((string) ($options['input_c21_artifact'] ?? self::DEFAULT_C21_INPUT_PATH));
        $c23InputPath = trim((string) ($options['input_c23_artifact'] ?? self::DEFAULT_C23_INPUT_PATH));
        $c24InputPath = trim((string) ($options['input_c24_artifact'] ?? self::DEFAULT_C24_INPUT_PATH));
        $c25InputPath = trim($c25InputPath) !== '' ? trim($c25InputPath) : self::DEFAULT_C25_INPUT_PATH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C26_IS_ONLY_WINDOW_MISMATCH', 'C26 catalog-candidate diagnostic requires the frozen IS window only.', $outputPath, $options, [
                'catalog_code' => $catalogCode,
                'from' => $fromDate,
                'to' => $toDate,
            ]);
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.', '', $options);
        }

        $c25 = $this->readJson($c25InputPath);
        if ($c25 === null) {
            return $this->blocked('WS_BT_C26_C25_ARTIFACT_UNREADABLE', 'C26 requires a readable C25 no-signal fallback/delay diagnostic artifact.', $outputPath, $options, [
                'c25_all_param_artifact_path' => $c25InputPath,
            ]);
        }
        if (($c25['artifact_type'] ?? null) !== 'C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC' || ($c25['status'] ?? null) !== 'PASS') {
            return $this->blocked('WS_BT_C26_C25_ARTIFACT_INVALID', 'C26 requires a PASS C25 no-signal fallback/delay diagnostic artifact.', $outputPath, $options, [
                'c25_all_param_artifact_path' => $c25InputPath,
                'c25_artifact_type' => $c25['artifact_type'] ?? null,
                'c25_status' => $c25['status'] ?? null,
            ]);
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C26_PARAM_IDS_INVALID', 'param-ids must be a comma-separated list of positive integers.', $outputPath, $options);
        }
        $profileCodes = $this->profileCodes($options['diagnostic_profile_codes'] ?? ($options['profile_codes'] ?? null));
        if ($profileCodes === false) {
            return $this->blocked('WS_BT_C26_DIAGNOSTIC_PROFILE_INVALID', 'diagnostic-profile-codes/profile-codes must contain known C26 diagnostic profile codes only.', $outputPath, $options);
        }
        $profileScope = 'EXPLICIT';
        if ($profileCodes === []) {
            $profileCodes = array_keys(self::diagnosticProfiles());
            $profileScope = 'ALL_DEFAULT';
        }
        $maxProfiles = $this->positiveIntOrNull($options['max_diagnostic_profiles'] ?? ($options['max_profiles'] ?? null));
        if ($maxProfiles !== null) {
            $profileCodes = array_slice($profileCodes, 0, $maxProfiles);
            $profileScope .= '_MAX_'.$maxProfiles;
        }
        if ($profileCodes === []) {
            return $this->blocked('WS_BT_C26_DIAGNOSTIC_PROFILE_EMPTY', 'No C26 diagnostic profiles selected.', $outputPath, $options);
        }

        $c21 = $this->readJson($c21InputPath);
        $c23 = $this->readJson($c23InputPath);
        $c24 = $this->readJson($c24InputPath);
        $rowsByProfile = $this->c25RowsByProfileAndPick($c25, $paramIds);
        $maxParams = $this->positiveIntOrNull($options['max_params'] ?? null);
        if ($maxParams !== null && $paramIds === []) {
            $allowedParams = $this->firstParamIds($rowsByProfile[self::C25_R09] ?? [], $maxParams);
            $rowsByProfile = $this->filterRowsByParams($rowsByProfile, $allowedParams);
            $profileScope .= '_MAX_PARAMS_'.$maxParams;
        }

        foreach ($this->requiredC25Profiles() as $required) {
            if (! isset($rowsByProfile[$required]) || $rowsByProfile[$required] === []) {
                return $this->blocked('WS_BT_C26_REQUIRED_C25_PROFILE_MISSING', 'C26 requires G21, G13, G16, R09, R15, and R16 C25 rows as comparators.', $outputPath, $options, [
                    'missing_profile_code' => $required,
                    'c25_all_param_artifact_path' => $c25InputPath,
                ]);
            }
        }

        $baseRows = $this->baseRows($rowsByProfile[self::C25_R09]);
        $maxPicks = $this->positiveIntOrNull($options['max_picks'] ?? null);
        if ($maxPicks !== null) {
            $baseRows = array_slice($baseRows, 0, $maxPicks);
            $profileScope .= '_MAX_PICKS_'.$maxPicks;
        }
        if ($baseRows === []) {
            return $this->blocked('WS_BT_C26_R09_ROWS_EMPTY', 'C26 requires evaluated C25 R09 rows after filtering.', $outputPath, $options);
        }

        $sourceProfileSummary = $this->profileSummaryByCode($c25);
        $baselineSummaries = $this->baselineSummaries($c25, $sourceProfileSummary);
        $rawOhlcAvailable = $this->rawOhlcAvailable($c25, $c21);
        $paramConsistencySummary = $this->paramConsistencySummary($rowsByProfile[self::C25_G21], $rowsByProfile[self::C25_R09]);
        $monthStabilitySummary = $this->monthStabilitySummary($rowsByProfile[self::C25_G21], $rowsByProfile[self::C25_R09]);
        $bucketStabilitySummary = $this->bucketStabilitySummary($rowsByProfile);
        $dataAvailability = $this->dataAvailability($c25, $c21, $c23, $c24, $rowsByProfile, $rawOhlcAvailable);
        $dataQualitySummary = $this->dataQualitySummary($c25, $rowsByProfile, $rawOhlcAvailable, count($baseRows));
        $lookaheadSafetySummary = $this->lookaheadSafetySummary($rowsByProfile);
        $candidateSummary = $this->candidateSummary($sourceProfileSummary, $rowsByProfile, $rawOhlcAvailable);
        $candidateReadinessSummary = $this->candidateReadinessSummary(
            $sourceProfileSummary,
            $candidateSummary,
            $paramConsistencySummary,
            $monthStabilitySummary,
            $bucketStabilitySummary,
            $dataQualitySummary,
            $lookaheadSafetySummary
        );
        $decision = $this->decision($candidateReadinessSummary);

        $pickRows = [];
        $profiles = self::diagnosticProfiles();
        $total = count($baseRows);
        foreach ($baseRows as $index => $r09) {
            if (! empty($options['progress_callback']) && is_callable($options['progress_callback']) && ($index % 250 === 0)) {
                ($options['progress_callback'])('[C26] pick '.($index + 1).'/'.$total.' profiles='.count($profileCodes));
            }
            $key = $this->pickKey($r09);
            $companions = $this->companions($rowsByProfile, $key);
            foreach ($profileCodes as $profileCode) {
                $profile = $profiles[$profileCode] ?? null;
                if ($profile === null) {
                    continue;
                }
                $sourceCode = (string) ($profile['source_profile_code'] ?? self::C25_G21);
                $source = $companions[$sourceCode] ?? null;
                if (! is_array($source)) {
                    continue;
                }
                $pickRows[] = $this->pickDiagnosticRow(
                    $profileCode,
                    $profile,
                    $source,
                    $companions,
                    $candidateReadinessSummary,
                    $paramConsistencySummary,
                    $monthStabilitySummary,
                    $bucketStabilitySummary,
                    $rawOhlcAvailable
                );
            }
        }

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C26_CATALOG_CANDIDATE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_CATALOG_CANDIDATE_DIAGNOSTIC',
            'generated_at' => (string) ($options['executed_at'] ?? gmdate('c')),
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'policy_code' => 'WS',
                'mutated' => false,
            ],
            'source_evidence' => $this->sourceEvidence($c21, $c23, $c24, $c25, $c21InputPath, $c23InputPath, $c24InputPath, $c25InputPath),
            'is_window' => ['from' => $fromDate, 'to' => $toDate],
            'evaluated_picks_count' => count($baseRows),
            'price_missing_count' => 0,
            'path_missing_count' => (int) ($dataQualitySummary['path_missing_count'] ?? 0),
            'data_availability' => $dataAvailability,
            'diagnostic_profiles' => $this->profileDefinitions($profileCodes),
            'profile_scope' => $profileScope,
            'pick_diagnostic_rows' => $pickRows,
            'baseline_summaries' => $baselineSummaries,
            'candidate_summary' => $candidateSummary,
            'param_consistency_summary' => $paramConsistencySummary,
            'month_stability_summary' => $monthStabilitySummary,
            'bucket_stability_summary' => $bucketStabilitySummary,
            'data_quality_summary' => $dataQualitySummary,
            'lookahead_safety_summary' => $lookaheadSafetySummary,
            'candidate_readiness_summary' => $candidateReadinessSummary,
            'decision' => $decision,
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message'], '', $options);
        }

        $r09 = $baselineSummaries['c23_r09'] ?? [];
        $g21 = $baselineSummaries['c25_g21'] ?? [];
        $g13 = $baselineSummaries['c25_g13'] ?? [];
        $g16 = $baselineSummaries['c25_g16'] ?? [];

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C26_CATALOG_CANDIDATE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_CATALOG_CANDIDATE_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'diagnostic_profile_count' => count($profileCodes),
            'profile_scope' => $profileScope,
            'evaluated_picks_count' => count($baseRows),
            'path_missing_count' => (int) ($dataQualitySummary['path_missing_count'] ?? 0),
            'c21_input_artifact_hash' => $c21['artifact_hash'] ?? null,
            'c23_input_artifact_hash' => $c23['artifact_hash'] ?? ($c25['source_evidence']['c23_all_param_artifact_hash'] ?? null),
            'c24_input_artifact_hash' => $c24['artifact_hash'] ?? ($c25['source_evidence']['c24_all_param_artifact_hash'] ?? null),
            'c25_input_artifact_hash' => $c25['artifact_hash'] ?? null,
            'primary_candidate' => self::C25_G21,
            'defensive_comparator' => self::C25_G13,
            'next_open_delay_comparator' => self::C25_G16,
            'r09_avg_ret_net' => $r09['avg_ret_net'] ?? null,
            'r09_median_ret_net' => $r09['median_ret_net'] ?? null,
            'r09_p25_ret_net' => $r09['p25_ret_net'] ?? null,
            'r09_win_rate' => $r09['win_rate'] ?? null,
            'g21_avg_ret_net' => $g21['avg_ret_net'] ?? null,
            'g21_median_ret_net' => $g21['median_ret_net'] ?? null,
            'g21_p25_ret_net' => $g21['p25_ret_net'] ?? null,
            'g21_win_rate' => $g21['win_rate'] ?? null,
            'g13_avg_ret_net' => $g13['avg_ret_net'] ?? null,
            'g13_median_ret_net' => $g13['median_ret_net'] ?? null,
            'g13_p25_ret_net' => $g13['p25_ret_net'] ?? null,
            'g13_win_rate' => $g13['win_rate'] ?? null,
            'g16_avg_ret_net' => $g16['avg_ret_net'] ?? null,
            'g16_median_ret_net' => $g16['median_ret_net'] ?? null,
            'g16_p25_ret_net' => $g16['p25_ret_net'] ?? null,
            'g16_win_rate' => $g16['win_rate'] ?? null,
            'g21_param_pass_count' => $paramConsistencySummary['candidate_pass_count'] ?? 0,
            'g21_param_fail_count' => $paramConsistencySummary['candidate_fail_count'] ?? 0,
            'g21_month_pass_count' => $monthStabilitySummary['candidate_pass_count'] ?? 0,
            'g21_month_fail_count' => $monthStabilitySummary['candidate_fail_count'] ?? 0,
            'g21_bucket_pass_count' => $bucketStabilitySummary['candidate_pass_count'] ?? 0,
            'g21_bucket_fail_count' => $bucketStabilitySummary['candidate_fail_count'] ?? 0,
            'raw_ohlc_validation_required' => $candidateReadinessSummary['raw_ohlc_validation_required'] ? 1 : 0,
            'derived_mfe_mae_dependency_detected' => $candidateReadinessSummary['derived_mfe_mae_dependency_detected'] ? 1 : 0,
            'lookahead_violation_count' => $lookaheadSafetySummary['lookahead_violation_count'] ?? 0,
            'ambiguous_intraday_sequence_count' => $candidateSummary['ambiguous_intraday_sequence_count'] ?? 0,
            'g21_primary_candidate_ready' => $candidateReadinessSummary['g21_primary_candidate_ready'] ? 1 : 0,
            'g13_defensive_candidate_ready' => $candidateReadinessSummary['g13_defensive_candidate_ready'] ? 1 : 0,
            'g16_next_open_delay_component_ready' => $candidateReadinessSummary['g16_next_open_delay_component_ready'] ? 1 : 0,
            'c27_catalog_candidate_implementation_recommended' => $candidateReadinessSummary['c27_catalog_candidate_implementation_recommended'] ? 1 : 0,
            'c27_requires_raw_ohlc_validation_first' => $candidateReadinessSummary['c27_requires_raw_ohlc_validation_first'] ? 1 : 0,
            'exit_rule_path_still_viable' => $candidateReadinessSummary['exit_rule_path_still_viable'] ? 1 : 0,
            'selection_quality_revisit_needed' => $candidateReadinessSummary['selection_quality_revisit_needed'] ? 1 : 0,
            'c26_catalog_implementation_deferred' => 1,
            'c26_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function requiredC25Profiles(): array
    {
        return [
            'C25_G00_CANONICAL_BASELINE',
            'C25_G01_C22_S06_SHADOW_BENCHMARK',
            self::C25_R09,
            self::C25_R15,
            self::C25_R16,
            self::C25_G13,
            self::C25_G16,
            self::C25_G21,
        ];
    }

    private function baseRows(array $rows): array
    {
        $rows = array_values(array_filter($rows, function (array $row): bool {
            return ($row['missing_path_data_flag'] ?? true) === false && is_numeric($row['profile_ret_net'] ?? null);
        }));
        usort($rows, function (array $left, array $right): int {
            return strcmp($this->pickKey($left), $this->pickKey($right));
        });
        return $rows;
    }

    private function companions(array $rowsByProfile, string $key): array
    {
        $out = [];
        foreach ($this->requiredC25Profiles() as $profileCode) {
            $out[$profileCode] = $rowsByProfile[$profileCode][$key] ?? null;
        }
        return $out;
    }

    private function pickDiagnosticRow(
        string $profileCode,
        array $profile,
        array $source,
        array $companions,
        array $readiness,
        array $paramConsistency,
        array $monthStability,
        array $bucketStability,
        bool $rawOhlcAvailable
    ): array {
        $canonical = $companions['C25_G00_CANONICAL_BASELINE'] ?? $source;
        $c22 = $companions['C25_G01_C22_S06_SHADOW_BENCHMARK'] ?? $source;
        $r09 = $companions[self::C25_R09] ?? $source;
        $r15 = $companions[self::C25_R15] ?? null;
        $r16 = $companions[self::C25_R16] ?? null;
        $g13 = $companions[self::C25_G13] ?? null;
        $g16 = $companions[self::C25_G16] ?? null;
        $g21 = $companions[self::C25_G21] ?? null;
        $profileRet = $this->num($source['profile_ret_net'] ?? null);
        $bucket = (string) ($source['bucket_code'] ?? 'other_gap_rows');
        $bucketContext = $this->bucketContext($bucketStability, $bucket);
        $paramId = (string) ($source['param_id'] ?? 'UNKNOWN');
        $month = substr((string) ($source['trade_date'] ?? ''), 0, 7);
        $derivedDependency = ! $rawOhlcAvailable && (bool) ($source['uses_intraday_high_low'] ?? false);

        return [
            'trade_date' => $source['trade_date'] ?? null,
            'ticker_id' => $source['ticker_id'] ?? null,
            'ticker' => $source['ticker'] ?? null,
            'param_id' => $source['param_id'] ?? null,
            'row_code' => $source['row_code'] ?? null,
            'entry_date' => $source['entry_date'] ?? null,
            'entry_price' => $source['entry_price'] ?? null,
            'signal_close_price' => $source['signal_close_price'] ?? null,
            'bucket_code' => $bucket,
            'bucket_reason' => $source['bucket_reason'] ?? null,
            'canonical_exit_date' => $source['canonical_exit_date'] ?? null,
            'canonical_exit_price' => $source['canonical_exit_price'] ?? null,
            'canonical_exit_reason' => $source['canonical_exit_reason'] ?? null,
            'canonical_ret_net' => $this->num($canonical['profile_ret_net'] ?? ($source['canonical_ret_net'] ?? null)),
            'c22_s06_exit_date' => $source['c22_s06_exit_date'] ?? null,
            'c22_s06_exit_price' => $source['c22_s06_exit_price'] ?? null,
            'c22_s06_ret_net' => $this->num($c22['profile_ret_net'] ?? ($source['c22_s06_ret_net'] ?? null)),
            'c23_r09_ret_net' => $this->num($r09['profile_ret_net'] ?? ($source['c23_r09_ret_net'] ?? null)),
            'c23_r15_ret_net' => is_array($r15) ? $this->num($r15['profile_ret_net'] ?? null) : null,
            'c23_r16_ret_net' => is_array($r16) ? $this->num($r16['profile_ret_net'] ?? null) : null,
            'c25_g13_ret_net' => is_array($g13) ? $this->num($g13['profile_ret_net'] ?? null) : null,
            'c25_g16_ret_net' => is_array($g16) ? $this->num($g16['profile_ret_net'] ?? null) : null,
            'c25_g21_ret_net' => is_array($g21) ? $this->num($g21['profile_ret_net'] ?? null) : null,
            'profile_code' => $profileCode,
            'profile_family' => $profile['family'] ?? null,
            'candidate_role' => $profile['candidate_role'] ?? null,
            'candidate_rule_code' => $profile['candidate_rule_code'] ?? null,
            'profile_exit_date' => $source['profile_exit_date'] ?? null,
            'profile_exit_price' => $source['profile_exit_price'] ?? null,
            'profile_exit_reason' => $source['profile_exit_reason'] ?? null,
            'profile_ret_net' => $profileRet,
            'delta_vs_canonical' => $this->delta($profileRet, $this->num($canonical['profile_ret_net'] ?? ($source['canonical_ret_net'] ?? null))),
            'delta_vs_c22_s06' => $this->delta($profileRet, $this->num($c22['profile_ret_net'] ?? ($source['c22_s06_ret_net'] ?? null))),
            'delta_vs_c23_r09' => $this->delta($profileRet, $this->num($r09['profile_ret_net'] ?? ($source['c23_r09_ret_net'] ?? null))),
            'delta_vs_c23_r15' => $this->delta($profileRet, is_array($r15) ? $this->num($r15['profile_ret_net'] ?? null) : null),
            'delta_vs_c23_r16' => $this->delta($profileRet, is_array($r16) ? $this->num($r16['profile_ret_net'] ?? null) : null),
            'delta_vs_c25_g13' => $this->delta($profileRet, is_array($g13) ? $this->num($g13['profile_ret_net'] ?? null) : null),
            'delta_vs_c25_g16' => $this->delta($profileRet, is_array($g16) ? $this->num($g16['profile_ret_net'] ?? null) : null),
            'delta_vs_c25_g21' => $this->delta($profileRet, is_array($g21) ? $this->num($g21['profile_ret_net'] ?? null) : null),
            'absolute_avg_bucket_context' => $bucketContext['g21_avg_ret_net'] ?? null,
            'absolute_median_bucket_context' => $bucketContext['g21_median_ret_net'] ?? null,
            'absolute_p25_bucket_context' => $bucketContext['g21_p25_ret_net'] ?? null,
            'max_favorable_excursion_pct' => $source['max_favorable_excursion_pct'] ?? null,
            'max_adverse_excursion_pct' => $source['max_adverse_excursion_pct'] ?? null,
            'first_profitable_close_day' => $source['first_profitable_close_day'] ?? null,
            'first_intraday_target_hit_day' => $source['first_intraday_target_hit_day'] ?? null,
            'close_signal_day' => $source['close_signal_day'] ?? null,
            'next_open_exit_day' => $source['next_open_exit_day'] ?? null,
            'next_open_delay_return_impact' => $source['next_open_delay_return_impact'] ?? null,
            'no_signal_before_fallback_flag' => $bucket === 'no_rule_profit_signal_before_fallback',
            'next_open_delay_gap_flag' => $bucket === 'next_open_delay_after_close_signal',
            'param_consistency_flag' => (bool) ($paramConsistency['param_pass_map'][$paramId] ?? false),
            'month_stability_flag' => (bool) ($monthStability['month_pass_map'][$month !== '' ? $month : 'UNKNOWN'] ?? false),
            'bucket_stability_flag' => (bool) ($bucketContext['candidate_pass_flag'] ?? false),
            'raw_ohlc_validated_flag' => $rawOhlcAvailable && (bool) ($source['uses_intraday_high_low'] ?? false),
            'derived_mfe_mae_dependency_flag' => $derivedDependency,
            'candidate_readiness_flag' => (bool) ($readiness['g21_primary_candidate_ready'] ?? false),
            'lookahead_safe' => (bool) ($source['lookahead_safe'] ?? true),
            'lookahead_violation_reason' => ((bool) ($source['lookahead_safe'] ?? true)) ? null : 'SOURCE_PROFILE_LOOKAHEAD_UNSAFE',
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'uses_intraday_high_low' => (bool) ($source['uses_intraday_high_low'] ?? false),
            'preplanned_order' => (bool) ($source['preplanned_order'] ?? false),
            'preplanned_threshold_pct' => $this->preplannedThreshold($profile['source_profile_code'] ?? ''),
            'intraday_sequence_known' => (bool) ($source['intraday_sequence_known'] ?? false),
            'ambiguous_intraday_sequence_flag' => (bool) ($source['ambiguous_intraday_sequence_flag'] ?? false),
            'conservative_fill_policy' => $source['conservative_fill_policy'] ?? 'STOP_FIRST_IF_TARGET_AND_STOP_SAME_DAILY_CANDLE',
            'missing_path_data_flag' => (bool) ($source['missing_path_data_flag'] ?? false),
        ];
    }

    private function baselineSummaries(array $c25, array $profileSummary): array
    {
        $baselines = is_array($c25['baseline_summaries'] ?? null) ? $c25['baseline_summaries'] : [];
        return [
            'canonical' => $this->normalizeSummary('canonical', $baselines['canonical_summary'] ?? [], 'canonical'),
            'c22_s06' => $this->normalizeSummary('c22_s06', $baselines['c22_s06_summary'] ?? [], 'c22_s06'),
            'c23_r09' => $this->normalizeSummary('c23_r09', $profileSummary[self::C25_R09] ?? []),
            'c23_r15' => $this->normalizeSummary('c23_r15', $profileSummary[self::C25_R15] ?? []),
            'c23_r16' => $this->normalizeSummary('c23_r16', $profileSummary[self::C25_R16] ?? []),
            'c25_g13' => $this->normalizeSummary('c25_g13', $profileSummary[self::C25_G13] ?? []),
            'c25_g16' => $this->normalizeSummary('c25_g16', $profileSummary[self::C25_G16] ?? []),
            'c25_g21' => $this->normalizeSummary('c25_g21', $profileSummary[self::C25_G21] ?? []),
        ];
    }

    private function candidateSummary(array $profileSummary, array $rowsByProfile, bool $rawOhlcAvailable): array
    {
        $r09 = $profileSummary[self::C25_R09] ?? [];
        $g21 = $profileSummary[self::C25_G21] ?? [];
        $g13 = $profileSummary[self::C25_G13] ?? [];
        $g16 = $profileSummary[self::C25_G16] ?? [];
        $derivedDependencyCount = $rawOhlcAvailable ? 0 : (int) ($g21['uses_intraday_high_low_count'] ?? 0);
        return [
            'primary_candidate_code' => self::C25_G21,
            'defensive_comparator_code' => self::C25_G13,
            'next_open_delay_comparator_code' => self::C25_G16,
            'downside_comparator_codes' => ['C23_R15', 'C23_R16'],
            'avg_ret_net' => $this->num($g21['avg_ret_net'] ?? null),
            'median_ret_net' => $this->num($g21['median_ret_net'] ?? null),
            'p25_ret_net' => $this->num($g21['p25_ret_net'] ?? null),
            'win_rate' => $this->num($g21['win_rate'] ?? null),
            'avg_delta_vs_r09' => $this->delta($this->num($g21['avg_ret_net'] ?? null), $this->num($r09['avg_ret_net'] ?? null)),
            'median_delta_vs_r09' => $this->delta($this->num($g21['median_ret_net'] ?? null), $this->num($r09['median_ret_net'] ?? null)),
            'p25_absolute_improvement_vs_r09' => $this->delta($this->num($g21['p25_ret_net'] ?? null), $this->num($r09['p25_ret_net'] ?? null)),
            'win_rate_delta_vs_r09' => $this->delta($this->num($g21['win_rate'] ?? null), $this->num($r09['win_rate'] ?? null)),
            'lookahead_violation_count' => (int) ($g21['lookahead_violation_count'] ?? 0),
            'ambiguous_intraday_sequence_count' => (int) ($g21['ambiguous_intraday_sequence_count'] ?? 0),
            'preplanned_order_count' => (int) ($g21['preplanned_order_count'] ?? 0),
            'raw_ohlc_validated_count' => $rawOhlcAvailable ? (int) ($g21['uses_intraday_high_low_count'] ?? 0) : 0,
            'derived_mfe_mae_dependency_count' => $derivedDependencyCount,
            'g21_profile_summary' => $this->normalizeSummary('c25_g21', $g21),
            'g13_profile_summary' => $this->normalizeSummary('c25_g13', $g13),
            'g16_profile_summary' => $this->normalizeSummary('c25_g16', $g16),
            'r09_profile_summary' => $this->normalizeSummary('c23_r09', $r09),
            'g21_rows_available' => ($rowsByProfile[self::C25_G21] ?? []) !== [],
            'g13_rows_available' => ($rowsByProfile[self::C25_G13] ?? []) !== [],
            'g16_rows_available' => ($rowsByProfile[self::C25_G16] ?? []) !== [],
        ];
    }

    private function paramConsistencySummary(array $g21Rows, array $r09Rows): array
    {
        $g21ByParam = $this->groupRows($g21Rows, 'param_id');
        $r09ByParam = $this->groupRows($r09Rows, 'param_id');
        $rows = [];
        $passMap = [];
        $improvementCount = 0;
        foreach ($g21ByParam as $param => $group) {
            $summary = $this->comparisonSummary($group, $r09ByParam[$param] ?? []);
            $reasons = $this->g21GateFailures($summary);
            $pass = $reasons === [];
            if (($summary['avg_delta_vs_r09'] ?? 0) > 0 || ($summary['median_delta_vs_r09'] ?? 0) >= 0.005 || ($summary['p25_improvement_vs_r09'] ?? 0) >= 0.010) {
                $improvementCount++;
            }
            $passMap[(string) $param] = $pass;
            $rows[] = array_merge(['param_id' => is_numeric($param) ? (int) $param : $param], $summary, [
                'candidate_pass_flag' => $pass,
                'failure_reason_codes' => $reasons,
            ]);
        }
        usort($rows, function (array $left, array $right): int {
            return ((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0));
        });
        $passCount = count(array_filter($rows, function (array $row): bool { return ($row['candidate_pass_flag'] ?? false) === true; }));
        return [
            'params' => $rows,
            'param_pass_map' => $passMap,
            'param_count' => count($rows),
            'candidate_pass_count' => $passCount,
            'candidate_fail_count' => count($rows) - $passCount,
            'candidate_improvement_param_count' => $improvementCount,
            'not_limited_to_one_param' => $improvementCount > 1,
        ];
    }

    private function monthStabilitySummary(array $g21Rows, array $r09Rows): array
    {
        $g21ByMonth = $this->groupRows($g21Rows, 'trade_month');
        $r09ByMonth = $this->groupRows($r09Rows, 'trade_month');
        $rows = [];
        $passMap = [];
        $improvementCount = 0;
        $positiveAvgCount = 0;
        foreach ($g21ByMonth as $month => $group) {
            $summary = $this->comparisonSummary($group, $r09ByMonth[$month] ?? []);
            if (($summary['avg_ret_net'] ?? 0) > 0) {
                $positiveAvgCount++;
            }
            $improved = (($summary['avg_delta_vs_r09'] ?? 0) > 0)
                || (($summary['median_delta_vs_r09'] ?? 0) >= 0.005)
                || (($summary['p25_improvement_vs_r09'] ?? 0) >= 0.010)
                || (($summary['win_rate_delta_vs_r09'] ?? 0) >= 0.10);
            if ($improved) {
                $improvementCount++;
            }
            $reasons = $improved ? [] : ['G21_MONTH_NO_MATERIAL_IMPROVEMENT_VS_R09'];
            $passMap[(string) $month] = $improved;
            $rows[] = array_merge(['month' => $month], $summary, [
                'candidate_pass_flag' => $improved,
                'failure_reason_codes' => $reasons,
            ]);
        }
        ksort($passMap, SORT_STRING);
        usort($rows, function (array $left, array $right): int {
            return strcmp((string) ($left['month'] ?? ''), (string) ($right['month'] ?? ''));
        });
        $passCount = count(array_filter($rows, function (array $row): bool { return ($row['candidate_pass_flag'] ?? false) === true; }));
        return [
            'months' => $rows,
            'month_pass_map' => $passMap,
            'month_count' => count($rows),
            'candidate_pass_count' => $passCount,
            'candidate_fail_count' => count($rows) - $passCount,
            'candidate_improvement_month_count' => $improvementCount,
            'positive_avg_month_count' => $positiveAvgCount,
            'one_month_only_dependency' => $improvementCount <= 1,
            'month_stability_hard_fail' => count($rows) <= 1 || $improvementCount <= 1,
        ];
    }

    private function bucketStabilitySummary(array $rowsByProfile): array
    {
        $buckets = [
            'candidate_matches_or_beats_c22',
            'next_open_delay_after_close_signal',
            'no_rule_profit_signal_before_fallback',
            'other_gap_rows',
        ];
        $out = [];
        $passCount = 0;
        $failCount = 0;
        foreach ($buckets as $bucket) {
            $g21 = $this->rowsForBucket($rowsByProfile[self::C25_G21] ?? [], $bucket);
            $r09 = $this->rowsForBucket($rowsByProfile[self::C25_R09] ?? [], $bucket);
            if ($bucket === 'other_gap_rows') {
                $g21 = array_merge($g21, $this->rowsForBucket($rowsByProfile[self::C25_G21] ?? [], 'late_rule_signal_after_c22_s06'));
                $r09 = array_merge($r09, $this->rowsForBucket($rowsByProfile[self::C25_R09] ?? [], 'late_rule_signal_after_c22_s06'));
            }
            $summary = $this->comparisonSummary($g21, $r09);
            $pass = count($g21) === 0 ? true : (($summary['avg_delta_vs_r09'] ?? 0) > 0 || ($summary['p25_improvement_vs_r09'] ?? 0) >= 0 || ($summary['win_rate_delta_vs_r09'] ?? 0) > 0);
            if ($bucket === 'candidate_matches_or_beats_c22') {
                $pass = count($g21) > 0;
            }
            $out[$bucket] = array_merge(['bucket_code' => $bucket], $summary, [
                'candidate_pass_flag' => $pass,
                'failure_reason_codes' => $pass ? [] : ['G21_BUCKET_NOT_IMPROVED_VS_R09'],
            ]);
            if ($pass) {
                $passCount++;
            } else {
                $failCount++;
            }
        }
        $nextOpenG16 = $this->comparisonSummary(
            $this->rowsForBucket($rowsByProfile[self::C25_G16] ?? [], 'next_open_delay_after_close_signal'),
            $this->rowsForBucket($rowsByProfile[self::C25_R09] ?? [], 'next_open_delay_after_close_signal')
        );
        $out['next_open_delay_g16_component'] = array_merge($nextOpenG16, [
            'materially_improves_bucket' => (($nextOpenG16['avg_delta_vs_r09'] ?? 0) >= 0.005)
                || (($nextOpenG16['median_delta_vs_r09'] ?? 0) >= 0.003)
                || (($nextOpenG16['win_rate_delta_vs_r09'] ?? 0) >= 0.10),
        ]);
        $out['candidate_pass_count'] = $passCount;
        $out['candidate_fail_count'] = $failCount;
        $out['no_signal_fallback_improved'] = (bool) ($out['no_rule_profit_signal_before_fallback']['candidate_pass_flag'] ?? false);
        $out['next_open_delay_improved'] = (bool) ($out['next_open_delay_after_close_signal']['candidate_pass_flag'] ?? false);
        $out['bucket_stability_pass'] = $out['no_signal_fallback_improved'] && $out['next_open_delay_improved'];
        return $out;
    }

    private function dataAvailability(array $c25, ?array $c21, ?array $c23, ?array $c24, array $rowsByProfile, bool $rawOhlcAvailable): array
    {
        $c25Data = is_array($c25['data_availability'] ?? null) ? $c25['data_availability'] : [];
        $notes = [];
        if ($c21 === null) {
            $notes[] = 'C21 path artifact NOT_FOUND_IN_SOURCE; raw OHLC and derived path dependency cannot be independently inspected from C21.';
        }
        if ($c23 === null) {
            $notes[] = 'C23 all-param artifact NOT_FOUND_IN_SOURCE; C26 uses C25 carried summaries for C23 comparators.';
        }
        if ($c24 === null) {
            $notes[] = 'C24 gap bridge artifact NOT_FOUND_IN_SOURCE; C26 uses C25 carried bucket evidence.';
        }
        if (! $rawOhlcAvailable) {
            $notes[] = 'RAW_HIGH_LOW_VALIDATION_REQUIRED=true because available C25/C21 artifacts do not prove raw D1-D5 open/high/low/close execution validation.';
        }
        if (! empty($c25Data['notes']) && is_array($c25Data['notes'])) {
            foreach ($c25Data['notes'] as $note) {
                $notes[] = (string) $note;
            }
        }
        return [
            'c25_all_param_artifact_available' => true,
            'c23_all_param_artifact_available' => $c23 !== null || (bool) ($c25Data['c23_all_param_artifact_available'] ?? false),
            'c24_all_param_artifact_available' => $c24 !== null || (bool) ($c25Data['c24_all_param_artifact_available'] ?? false),
            'c21_path_artifact_available' => $c21 !== null || (bool) ($c25Data['c21_path_artifact_available'] ?? false),
            'g21_rows_available' => ($rowsByProfile[self::C25_G21] ?? []) !== [],
            'g13_rows_available' => ($rowsByProfile[self::C25_G13] ?? []) !== [],
            'g16_rows_available' => ($rowsByProfile[self::C25_G16] ?? []) !== [],
            'r09_rows_available' => ($rowsByProfile[self::C25_R09] ?? []) !== [],
            'r15_rows_available' => ($rowsByProfile[self::C25_R15] ?? []) !== [],
            'r16_rows_available' => ($rowsByProfile[self::C25_R16] ?? []) !== [],
            'c22_shadow_s06_available_or_recomputable' => (bool) ($c25Data['c22_shadow_s06_available_or_recomputable'] ?? true),
            'canonical_baseline_available' => (bool) ($c25Data['canonical_baseline_available'] ?? true),
            'd1_to_d5_ohlc_available' => $rawOhlcAvailable,
            'raw_high_low_available' => $rawOhlcAvailable,
            'derived_mfe_mae_available' => (bool) ($c25Data['derived_mfe_mae_available'] ?? false),
            'next_open_after_close_signal_available' => (bool) ($c25Data['next_open_after_close_signal_available'] ?? true),
            'market_calendar_continuity_available' => (bool) ($c25Data['market_calendar_continuity_available'] ?? true),
            'published_price_availability_available' => (bool) ($c25Data['published_price_availability_available'] ?? true),
            'raw_ohlc_validation_required' => ! $rawOhlcAvailable,
            'notes' => array_values(array_unique($notes)),
        ];
    }

    private function dataQualitySummary(array $c25, array $rowsByProfile, bool $rawOhlcAvailable, int $evaluatedPicks): array
    {
        $pathMissing = (int) ($c25['baseline_summaries']['canonical_summary']['path_missing_count'] ?? 0);
        $g21 = $rowsByProfile[self::C25_G21] ?? [];
        $derived = count(array_filter($g21, function (array $row): bool {
            return (bool) ($row['uses_intraday_high_low'] ?? false);
        }));
        $ambiguous = count(array_filter($g21, function (array $row): bool {
            return (bool) ($row['ambiguous_intraday_sequence_flag'] ?? false);
        }));
        return [
            'evaluated_picks_count' => $evaluatedPicks,
            'price_missing_count' => 0,
            'path_missing_count' => $pathMissing,
            'raw_ohlc_available_rate' => $rawOhlcAvailable ? 1.0 : 0.0,
            'derived_mfe_mae_dependency_rate' => $evaluatedPicks > 0 ? $derived / $evaluatedPicks : null,
            'missing_path_rate' => $evaluatedPicks > 0 ? $pathMissing / ($evaluatedPicks + $pathMissing) : null,
            'ambiguous_intraday_sequence_rate' => $evaluatedPicks > 0 ? $ambiguous / $evaluatedPicks : null,
            'market_calendar_continuity_status' => 'AVAILABLE_FROM_C25_SOURCE_EVIDENCE',
        ];
    }

    private function lookaheadSafetySummary(array $rowsByProfile): array
    {
        $rows = [];
        foreach ([self::C25_G21, self::C25_G13, self::C25_G16, self::C25_R09, self::C25_R15, self::C25_R16] as $profile) {
            foreach (($rowsByProfile[$profile] ?? []) as $row) {
                $rows[] = $row;
            }
        }
        $violations = 0;
        $ambiguous = 0;
        $futureSelection = false;
        $profileRetSelection = false;
        foreach ($rows as $row) {
            if (($row['lookahead_safe'] ?? true) !== true) {
                $violations++;
            }
            if (($row['ambiguous_intraday_sequence_flag'] ?? false) === true) {
                $ambiguous++;
            }
            $futureSelection = $futureSelection || (bool) ($row['future_path_price_used_for_selection'] ?? false);
            $profileRetSelection = $profileRetSelection || (bool) ($row['profile_ret_net_used_for_selection'] ?? false);
        }
        $closeSignal = $this->closeSignalNextOpenChecks($rowsByProfile[self::C25_R09] ?? []);
        return [
            'lookahead_violation_count' => $violations + (int) ($closeSignal['same_day_close_signal_exit_violation_count'] ?? 0),
            'lookahead_safe' => $violations === 0 && (int) ($closeSignal['same_day_close_signal_exit_violation_count'] ?? 0) === 0,
            'future_path_price_used_for_selection' => $futureSelection,
            'profile_ret_net_used_for_selection' => $profileRetSelection,
            'preplanned_order_threshold_fixed_before_path_evaluation' => true,
            'close_signal_same_day_exit_forbidden' => true,
            'ambiguous_intraday_sequence_count' => $ambiguous,
            'close_signal_next_open_rule_checks' => $closeSignal,
        ];
    }

    private function closeSignalNextOpenChecks(array $r09Rows): array
    {
        $violations = 0;
        $checks = [
            'd1_close_signal_min_exit_day' => 2,
            'd2_close_signal_min_exit_day' => 3,
            'd3_close_signal_min_exit_day' => 4,
            'd1_checked_count' => 0,
            'd2_checked_count' => 0,
            'd3_checked_count' => 0,
        ];
        foreach ($r09Rows as $row) {
            $signal = $this->num($row['close_signal_day'] ?? null);
            $exit = $this->num($row['next_open_exit_day'] ?? null);
            if ($signal === null || $exit === null) {
                continue;
            }
            if ((int) $signal === 1) {
                $checks['d1_checked_count']++;
            } elseif ((int) $signal === 2) {
                $checks['d2_checked_count']++;
            } elseif ((int) $signal === 3) {
                $checks['d3_checked_count']++;
            }
            if (in_array((int) $signal, [1, 2, 3], true) && (int) $exit <= (int) $signal) {
                $violations++;
            }
        }
        $checks['same_day_close_signal_exit_violation_count'] = $violations;
        return $checks;
    }

    private function candidateReadinessSummary(array $profileSummary, array $candidateSummary, array $param, array $month, array $bucket, array $quality, array $lookahead): array
    {
        $r09 = $profileSummary[self::C25_R09] ?? [];
        $g21 = $profileSummary[self::C25_G21] ?? [];
        $g13 = $profileSummary[self::C25_G13] ?? [];
        $g16 = $profileSummary[self::C25_G16] ?? [];
        $g21Failures = $this->g21GateFailures([
            'avg_delta_vs_r09' => $this->delta($this->num($g21['avg_ret_net'] ?? null), $this->num($r09['avg_ret_net'] ?? null)),
            'median_delta_vs_r09' => $this->delta($this->num($g21['median_ret_net'] ?? null), $this->num($r09['median_ret_net'] ?? null)),
            'p25_improvement_vs_r09' => $this->delta($this->num($g21['p25_ret_net'] ?? null), $this->num($r09['p25_ret_net'] ?? null)),
            'win_rate_delta_vs_r09' => $this->delta($this->num($g21['win_rate'] ?? null), $this->num($r09['win_rate'] ?? null)),
            'lookahead_violation_count' => (int) ($g21['lookahead_violation_count'] ?? 0),
            'ambiguous_intraday_sequence_count' => (int) ($g21['ambiguous_intraday_sequence_count'] ?? 0),
        ]);
        if (! (bool) ($param['not_limited_to_one_param'] ?? false)) {
            $g21Failures[] = 'G21_IMPROVEMENT_LIMITED_TO_ONE_PARAM';
        }
        if ((bool) ($month['one_month_only_dependency'] ?? true)) {
            $g21Failures[] = 'G21_MONTH_STABILITY_ONE_MONTH_ONLY';
        }

        $g13Failures = [];
        if (($this->delta($this->num($g13['p25_ret_net'] ?? null), $this->num($r09['p25_ret_net'] ?? null)) ?? -999) < 0.015) {
            $g13Failures[] = 'G13_P25_IMPROVEMENT_LT_1_5PCT';
        }
        if (($this->delta($this->num($g13['win_rate'] ?? null), $this->num($r09['win_rate'] ?? null)) ?? -999) < 0.15) {
            $g13Failures[] = 'G13_WIN_RATE_DELTA_LT_15PCT';
        }
        if ((int) ($g13['lookahead_violation_count'] ?? 0) !== 0 || (int) ($g13['ambiguous_intraday_sequence_count'] ?? 0) !== 0) {
            $g13Failures[] = 'G13_LOOKAHEAD_OR_SEQUENCE_UNSAFE';
        }
        if (!(($this->num($g13['avg_ret_net'] ?? null) ?? 0.0) < ($this->num($r09['avg_ret_net'] ?? null) ?? 0.0) || ($this->num($g13['avg_ret_net'] ?? null) ?? 0.0) < 0.0)) {
            $g13Failures[] = 'G13_NOT_DEFENSIVE_AVG_NOT_BELOW_R09_OR_ZERO';
        }

        $g16Failures = [];
        if (! (bool) ($bucket['next_open_delay_g16_component']['materially_improves_bucket'] ?? false)) {
            $g16Failures[] = 'G16_NEXT_OPEN_DELAY_BUCKET_NOT_MATERIAL';
        }
        if (($this->delta($this->num($g16['p25_ret_net'] ?? null), $this->num($r09['p25_ret_net'] ?? null)) ?? -999) < -0.005) {
            $g16Failures[] = 'G16_GLOBAL_P25_WORSE_THAN_R09_BY_GT_0_5PCT';
        }
        if ((int) ($g16['lookahead_violation_count'] ?? 0) !== 0 || (int) ($g16['ambiguous_intraday_sequence_count'] ?? 0) !== 0) {
            $g16Failures[] = 'G16_LOOKAHEAD_OR_SEQUENCE_UNSAFE';
        }

        $rawRequired = (($quality['raw_ohlc_available_rate'] ?? 0.0) < 1.0) || (($candidateSummary['derived_mfe_mae_dependency_count'] ?? 0) > 0);
        $g21Ready = $g21Failures === [];
        $bucketPass = (bool) ($bucket['bucket_stability_pass'] ?? false);
        $lookaheadPass = (bool) ($lookahead['lookahead_safe'] ?? false) && (int) ($candidateSummary['ambiguous_intraday_sequence_count'] ?? 0) === 0;
        $c27Recommended = $g21Ready
            && (bool) ($param['not_limited_to_one_param'] ?? false)
            && ! (bool) ($month['month_stability_hard_fail'] ?? true)
            && $bucketPass
            && $lookaheadPass;

        return [
            'g21_primary_candidate_ready' => $g21Ready,
            'g21_failure_reason_codes' => array_values(array_unique($g21Failures)),
            'g13_defensive_candidate_ready' => $g13Failures === [],
            'g13_failure_reason_codes' => $g13Failures,
            'g13_defensive_only' => $g13Failures === [],
            'g16_next_open_delay_component_ready' => $g16Failures === [],
            'g16_failure_reason_codes' => $g16Failures,
            'r15_r16_downside_comparator_kept' => true,
            'raw_ohlc_validation_required' => $rawRequired,
            'derived_mfe_mae_dependency_detected' => ($candidateSummary['derived_mfe_mae_dependency_count'] ?? 0) > 0,
            'c27_catalog_candidate_implementation_recommended' => $c27Recommended,
            'c27_requires_raw_ohlc_validation_first' => $c27Recommended && $rawRequired,
            'exit_rule_path_still_viable' => $c27Recommended || $g21Ready,
            'selection_quality_revisit_needed' => ! $g21Ready,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'production_ready' => 0,
        ];
    }

    private function decision(array $readiness): array
    {
        $recommended = (bool) ($readiness['c27_catalog_candidate_implementation_recommended'] ?? false);
        $rawRequired = (bool) ($readiness['raw_ohlc_validation_required'] ?? true);
        $status = $recommended
            ? ($rawRequired ? 'C26_RAW_OHLC_VALIDATION_REQUIRED' : 'C26_CATALOG_CANDIDATE_READY')
            : 'C26_CANDIDATE_NOT_READY';
        return [
            'decision_status' => $status,
            'g21_primary_candidate_ready' => (bool) ($readiness['g21_primary_candidate_ready'] ?? false),
            'g13_defensive_candidate_ready' => (bool) ($readiness['g13_defensive_candidate_ready'] ?? false),
            'g16_next_open_delay_component_ready' => (bool) ($readiness['g16_next_open_delay_component_ready'] ?? false),
            'raw_ohlc_validation_required' => $rawRequired,
            'c27_catalog_candidate_implementation_recommended' => $recommended,
            'c27_requires_raw_ohlc_validation_first' => (bool) ($readiness['c27_requires_raw_ohlc_validation_first'] ?? false),
            'exit_rule_path_still_viable' => (bool) ($readiness['exit_rule_path_still_viable'] ?? false),
            'selection_quality_revisit_needed' => (bool) ($readiness['selection_quality_revisit_needed'] ?? true),
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'next_step' => $recommended
                ? ($rawRequired
                    ? 'C27 catalog-candidate implementation may proceed only after raw D1-D5 OHLC/high-low validation is added first; OOS and production catalog remain forbidden in C26.'
                    : 'C27 catalog-candidate implementation may proceed as IS-only source work; OOS and production catalog remain forbidden in C26.')
                : 'Hold the C27 catalog-candidate path; revisit exit-rule evidence or selection/entry quality based on C26 failure reasons.',
        ];
    }

    private function g21GateFailures(array $summary): array
    {
        $reasons = [];
        if (($summary['avg_delta_vs_r09'] ?? null) === null || (float) $summary['avg_delta_vs_r09'] < 0.0) {
            $reasons[] = 'G21_AVG_BELOW_R09';
        }
        if (($summary['median_delta_vs_r09'] ?? null) === null || (float) $summary['median_delta_vs_r09'] < 0.005) {
            $reasons[] = 'G21_MEDIAN_DELTA_LT_0_5PCT';
        }
        if (($summary['p25_improvement_vs_r09'] ?? null) === null || (float) $summary['p25_improvement_vs_r09'] < 0.010) {
            $reasons[] = 'G21_P25_IMPROVEMENT_LT_1PCT';
        }
        if (($summary['win_rate_delta_vs_r09'] ?? null) === null || (float) $summary['win_rate_delta_vs_r09'] < 0.10) {
            $reasons[] = 'G21_WIN_RATE_DELTA_LT_10PCT';
        }
        if ((int) ($summary['lookahead_violation_count'] ?? 0) !== 0) {
            $reasons[] = 'G21_LOOKAHEAD_VIOLATION';
        }
        if ((int) ($summary['ambiguous_intraday_sequence_count'] ?? 0) !== 0) {
            $reasons[] = 'G21_AMBIGUOUS_INTRADAY_SEQUENCE';
        }
        return $reasons;
    }

    private function comparisonSummary(array $candidateRows, array $r09Rows): array
    {
        $candidateReturns = $this->values($candidateRows, 'profile_ret_net');
        $r09Returns = $this->values($r09Rows, 'profile_ret_net');
        $wins = count(array_filter($candidateReturns, function (float $value): bool { return $value > 0; }));
        $r09Wins = count(array_filter($r09Returns, function (float $value): bool { return $value > 0; }));
        $candidateAvg = $this->avg($candidateReturns);
        $r09Avg = $this->avg($r09Returns);
        $candidateMedian = $this->median($candidateReturns);
        $r09Median = $this->median($r09Returns);
        $candidateP25 = $this->percentile($candidateReturns, 0.25);
        $r09P25 = $this->percentile($r09Returns, 0.25);
        $candidateWin = count($candidateRows) > 0 ? $wins / count($candidateRows) : null;
        $r09Win = count($r09Rows) > 0 ? $r09Wins / count($r09Rows) : null;
        return [
            'count' => count($candidateRows),
            'avg_ret_net' => $candidateAvg,
            'median_ret_net' => $candidateMedian,
            'p25_ret_net' => $candidateP25,
            'win_rate' => $candidateWin,
            'r09_avg_ret_net' => $r09Avg,
            'r09_median_ret_net' => $r09Median,
            'r09_p25_ret_net' => $r09P25,
            'r09_win_rate' => $r09Win,
            'avg_delta_vs_r09' => $this->delta($candidateAvg, $r09Avg),
            'median_delta_vs_r09' => $this->delta($candidateMedian, $r09Median),
            'p25_improvement_vs_r09' => $this->delta($candidateP25, $r09P25),
            'win_rate_delta_vs_r09' => $this->delta($candidateWin, $r09Win),
            'lookahead_violation_count' => count(array_filter($candidateRows, function (array $row): bool { return ($row['lookahead_safe'] ?? true) !== true; })),
            'ambiguous_intraday_sequence_count' => count(array_filter($candidateRows, function (array $row): bool { return (bool) ($row['ambiguous_intraday_sequence_flag'] ?? false); })),
        ];
    }

    private function sourceEvidence(?array $c21, ?array $c23, ?array $c24, array $c25, string $c21Path, string $c23Path, string $c24Path, string $c25Path): array
    {
        return [
            'c19_final_status' => 'C19_CATALOG_CANDIDATE_FAILED',
            'c20_final_status' => 'C20_DATE_GATE_NOT_ENOUGH',
            'c21_final_status' => 'C21_EXECUTION_SIGNAL_FOUND',
            'c22_final_status' => 'C22_EXIT_CAPTURE_SIGNAL_FOUND',
            'c23_final_status' => 'C23_NON_LOOKAHEAD_RULE_CANDIDATE_FOUND_BUT_SHADOW_GAP_NOT_ACCEPTABLE',
            'c24_final_status' => 'C24_GAP_BRIDGE_EXPLAINED',
            'c25_final_status' => 'C25_C26_CATALOG_CANDIDATE_DIAGNOSTIC_RECOMMENDED',
            'c21_path_artifact_path' => $c21 !== null ? $c21Path : null,
            'c21_path_artifact_hash' => $c21['artifact_hash'] ?? null,
            'c23_all_param_artifact_path' => $c23 !== null ? $c23Path : ($c25['source_evidence']['c23_all_param_artifact_path'] ?? null),
            'c23_all_param_artifact_hash' => $c23['artifact_hash'] ?? ($c25['source_evidence']['c23_all_param_artifact_hash'] ?? null),
            'c24_all_param_artifact_path' => $c24 !== null ? $c24Path : ($c25['source_evidence']['c24_all_param_artifact_path'] ?? null),
            'c24_all_param_artifact_hash' => $c24['artifact_hash'] ?? ($c25['source_evidence']['c24_all_param_artifact_hash'] ?? null),
            'c25_all_param_artifact_path' => $c25Path,
            'c25_all_param_artifact_hash' => $c25['artifact_hash'] ?? null,
        ];
    }

    private function profileDefinitions(array $codes): array
    {
        $profiles = self::diagnosticProfiles();
        $out = [];
        foreach ($codes as $code) {
            $out[] = array_merge([
                'profile_code' => $code,
                'diagnostic_only' => true,
                'production_rule' => false,
                'used_for_ticker_selection' => false,
                'used_for_trade_date_selection' => false,
            ], $profiles[$code] ?? []);
        }
        return $out;
    }

    private function safetyBoundaries(): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C26_CATALOG_CODE' => 'NOT_CREATED',
            'C26_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_TICKER_BLACKLIST' => true,
            'NO_MONTH_BLACKLIST' => true,
            'NO_SECTOR_WHITELIST' => true,
            'NO_BEST_OF_FAILED_BINDING' => true,
            'NO_C01_TO_C25_MUTATION' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'NO_C19_REOPEN' => true,
            'NO_C20_REOPEN' => true,
            'NO_C21_REOPEN' => true,
            'NO_C22_REOPEN' => true,
            'NO_C23_REOPEN' => true,
            'NO_C24_REOPEN' => true,
            'NO_C25_REOPEN' => true,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'canonical_model_unchanged' => [
                'ENTRY' => 'NEXT_OPEN',
                'EXIT' => 'STOP_TP_OR_TIME',
                'HOLD' => 5,
                'FEE' => 'IDR_FIXED',
                'SLIP' => 0,
                'GAP' => 'OPEN',
                'PX' => 'IDX_BANDS',
            ],
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'c22_shadow_s06_used_for_selection' => false,
            'diagnostic_profiles_used_as_production_rule' => false,
            'c22_shadow_used_as_production_rule' => false,
            'close_signal_same_day_exit_allowed' => false,
        ];
    }

    private function rawOhlcAvailable(array $c25, ?array $c21): bool
    {
        if (($c25['data_availability']['d1_to_d5_ohlc_available'] ?? false) !== true
            && ($c25['data_availability']['raw_high_low_available'] ?? false) !== true) {
            return false;
        }
        $rows = is_array($c21['pick_path_rows'] ?? null) ? $c21['pick_path_rows'] : [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $hasAll = true;
            for ($day = 1; $day <= 5; $day++) {
                foreach (['open', 'high', 'low', 'close'] as $field) {
                    if (! is_numeric($row['d'.$day.'_'.$field] ?? null)) {
                        $hasAll = false;
                        break 2;
                    }
                }
            }
            if ($hasAll) {
                return true;
            }
        }
        return false;
    }

    private function normalizeSummary(string $code, array $summary, string $type = 'profile'): array
    {
        if ($type === 'canonical') {
            return [
                'summary_code' => $code,
                'avg_ret_net' => $this->num($summary['canonical_avg_ret_net'] ?? null),
                'median_ret_net' => $this->num($summary['canonical_median_ret_net'] ?? null),
                'p25_ret_net' => $this->num($summary['canonical_p25_ret_net'] ?? null),
                'win_rate' => $this->num($summary['canonical_win_rate'] ?? null),
                'evaluated_picks_count' => (int) ($summary['evaluated_picks_count'] ?? 0),
                'path_missing_count' => (int) ($summary['path_missing_count'] ?? 0),
            ];
        }
        if ($type === 'c22_s06') {
            return [
                'summary_code' => $code,
                'avg_ret_net' => $this->num($summary['c22_shadow_s06_avg_ret_net'] ?? null),
                'median_ret_net' => $this->num($summary['c22_shadow_s06_median_ret_net'] ?? null),
                'p25_ret_net' => $this->num($summary['c22_shadow_s06_p25_ret_net'] ?? null),
                'win_rate' => $this->num($summary['c22_shadow_s06_win_rate'] ?? null),
                'evaluated_picks_count' => (int) ($summary['evaluated_picks_count'] ?? 0),
            ];
        }
        return array_merge(['summary_code' => $code], $summary);
    }

    private function profileSummaryByCode(array $c25): array
    {
        $out = [];
        foreach (($c25['profile_summary'] ?? []) as $summary) {
            if (is_array($summary) && isset($summary['profile_code'])) {
                $out[(string) $summary['profile_code']] = $summary;
            }
        }
        return $out;
    }

    private function c25RowsByProfileAndPick(array $c25, array $paramIds): array
    {
        $allowed = $paramIds === [] ? null : array_fill_keys($paramIds, true);
        $rows = [];
        foreach (($c25['pick_diagnostic_rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            if ($allowed !== null && ! isset($allowed[(int) ($row['param_id'] ?? 0)])) {
                continue;
            }
            $profile = (string) ($row['profile_code'] ?? '');
            if ($profile === '') {
                continue;
            }
            $row['trade_month'] = substr((string) ($row['trade_date'] ?? ''), 0, 7) ?: 'UNKNOWN';
            $rows[$profile][$this->pickKey($row)] = $row;
        }
        return $rows;
    }

    private function filterRowsByParams(array $rowsByProfile, array $paramIds): array
    {
        if ($paramIds === []) {
            return $rowsByProfile;
        }
        $allowed = array_fill_keys($paramIds, true);
        $out = [];
        foreach ($rowsByProfile as $profile => $rows) {
            foreach ($rows as $key => $row) {
                if (isset($allowed[(int) ($row['param_id'] ?? 0)])) {
                    $out[$profile][$key] = $row;
                }
            }
        }
        return $out;
    }

    private function firstParamIds(array $rows, int $maxParams): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['param_id'] ?? 0);
            if ($id > 0) {
                $ids[$id] = true;
            }
        }
        $ids = array_keys($ids);
        sort($ids, SORT_NUMERIC);
        return array_slice($ids, 0, $maxParams);
    }

    private function groupRows(array $rows, string $key): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $value = $key === 'trade_month'
                ? (substr((string) ($row['trade_date'] ?? ''), 0, 7) ?: 'UNKNOWN')
                : (string) ($row[$key] ?? 'UNKNOWN');
            $groups[$value][] = $row;
        }
        ksort($groups, SORT_STRING);
        return $groups;
    }

    private function rowsForBucket(array $rows, string $bucket): array
    {
        return array_values(array_filter($rows, function (array $row) use ($bucket): bool {
            return ($row['bucket_code'] ?? null) === $bucket;
        }));
    }

    private function bucketContext(array $bucketStability, string $bucket): array
    {
        if (isset($bucketStability[$bucket]) && is_array($bucketStability[$bucket])) {
            return [
                'g21_avg_ret_net' => $bucketStability[$bucket]['avg_ret_net'] ?? null,
                'g21_median_ret_net' => $bucketStability[$bucket]['median_ret_net'] ?? null,
                'g21_p25_ret_net' => $bucketStability[$bucket]['p25_ret_net'] ?? null,
                'candidate_pass_flag' => $bucketStability[$bucket]['candidate_pass_flag'] ?? false,
            ];
        }
        return [];
    }

    private function preplannedThreshold(string $sourceProfile): ?float
    {
        if ($sourceProfile === self::C25_G13) {
            return 0.0050;
        }
        if ($sourceProfile === self::C25_G16) {
            return 0.0150;
        }
        if ($sourceProfile === self::C25_G21) {
            return 0.0100;
        }
        return null;
    }

    private function pickKey(array $row): string
    {
        return implode('|', [
            (string) ($row['trade_date'] ?? ''),
            (string) ($row['ticker_id'] ?? ''),
            strtoupper((string) ($row['ticker'] ?? '')),
            (string) ($row['param_id'] ?? ''),
        ]);
    }

    private function readJson(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function writeArtifact(string $path, array $artifact): array
    {
        $dir = dirname($path);
        if ($dir !== '' && ! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write artifact file.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash'], $payload['generated_at']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function profileCodes($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return [];
        }
        $known = self::diagnosticProfiles();
        $codes = [];
        foreach (explode(',', (string) $value) as $raw) {
            $code = strtoupper(trim($raw));
            if ($code === '') {
                continue;
            }
            if (! isset($known[$code])) {
                return false;
            }
            $codes[] = $code;
        }
        return array_values(array_unique($codes));
    }

    private function paramIds($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return [];
        }
        $ids = [];
        foreach (explode(',', (string) $value) as $raw) {
            $id = trim($raw);
            if ($id === '' || ! ctype_digit($id) || (int) $id <= 0) {
                return false;
            }
            $ids[] = (int) $id;
        }
        return array_values(array_unique($ids));
    }

    private function positiveIntOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value) || (int) $value <= 0) {
            return null;
        }
        return (int) $value;
    }

    private function values(array $rows, string $key): array
    {
        $values = [];
        foreach ($rows as $row) {
            if (is_numeric($row[$key] ?? null)) {
                $values[] = (float) $row[$key];
            }
        }
        return $values;
    }

    private function avg(array $values): ?float
    {
        $values = array_values(array_filter($values, 'is_numeric'));
        return $values === [] ? null : array_sum($values) / count($values);
    }

    private function median(array $values): ?float
    {
        $values = array_values(array_filter($values, 'is_numeric'));
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);
        return $count % 2 === 1 ? (float) $values[$middle] : (((float) $values[$middle - 1] + (float) $values[$middle]) / 2);
    }

    private function percentile(array $values, float $percentile): ?float
    {
        $values = array_values(array_filter($values, 'is_numeric'));
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $index = (count($values) - 1) * $percentile;
        $lower = (int) floor($index);
        $upper = (int) ceil($index);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }
        $weight = $index - $lower;
        return (float) $values[$lower] + (((float) $values[$upper] - (float) $values[$lower]) * $weight);
    }

    private function num($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function delta(?float $left, ?float $right): ?float
    {
        return $left === null || $right === null ? null : $left - $right;
    }

    private function blocked(string $reasonCode, string $message, string $outputPath = '', array $options = [], array $extra = []): array
    {
        $result = array_replace_recursive([
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'c26_catalog_implementation_deferred' => 1,
            'c26_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);

        if ($outputPath !== '' && (! is_file($outputPath) || ! empty($options['overwrite']))) {
            $artifact = [
                'artifact_type' => self::ARTIFACT_TYPE,
                'status' => 'BLOCKED',
                'reason_code' => $reasonCode,
                'scope' => 'IS_ONLY_CATALOG_CANDIDATE_DIAGNOSTIC',
                'generated_at' => (string) ($options['executed_at'] ?? gmdate('c')),
                'source_catalog' => [
                    'catalog_code' => $extra['catalog_code'] ?? self::DEFAULT_SOURCE_CATALOG_CODE,
                    'policy_code' => 'WS',
                    'mutated' => false,
                ],
                'source_evidence' => $extra,
                'data_availability' => [
                    'c25_all_param_artifact_available' => false,
                    'notes' => ['NOT_FOUND_IN_SOURCE or invalid required C25 artifact; C26 does not reconstruct runtime evidence.'],
                ],
                'diagnostic_profiles' => [],
                'pick_diagnostic_rows' => [],
                'baseline_summaries' => [],
                'candidate_summary' => [],
                'param_consistency_summary' => [],
                'month_stability_summary' => [],
                'bucket_stability_summary' => [],
                'data_quality_summary' => [],
                'lookahead_safety_summary' => [],
                'candidate_readiness_summary' => [],
                'decision' => [
                    'decision_status' => 'C26_DIAGNOSTIC_BLOCKED',
                    'catalog_allowed' => false,
                    'oos_allowed' => false,
                    'next_step' => 'Provide a readable PASS C25 all-param artifact and rerun C26; do not run OOS or create catalog.',
                ],
                'safety_boundaries' => $this->safetyBoundaries(),
            ];
            $artifact['artifact_hash'] = $this->stableHash($artifact);
            if (($this->writeArtifact($outputPath, $artifact)['ok'] ?? false) === true) {
                $result['artifact_path'] = $outputPath;
                $result['artifact_hash'] = $artifact['artifact_hash'];
            }
        }

        return $result;
    }
}
