<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService
{
    public const ARTIFACT_TYPE = 'C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC';
    public const DEFAULT_C23_INPUT_PATH = 'storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json';
    public const DEFAULT_C24_INPUT_PATH = 'storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json';
    public const DEFAULT_C21_INPUT_PATH = 'storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-all-param.json';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;

    public const C23_R09 = 'C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0';
    public const C23_R13 = 'C23_R13_COMPRESS_HOLD_TO_D3_IF_NO_PROFIT_BY_D2';
    public const C23_R14 = 'C23_R14_COMPRESS_HOLD_TO_D4_IF_NO_PROFIT_BY_D3';
    public const C23_R15 = 'C23_R15_COMBO_D1_D2_FIRST_PROFIT_CAPTURE_OR_D3_NO_PROFIT_EXIT';
    public const C23_R16 = 'C23_R16_COMBO_D1_D2_D3_FIRST_PROFIT_CAPTURE_OR_D4_NO_PROFIT_EXIT';
    public const C23_R00 = 'C23_R00_CANONICAL_BASELINE';

    public static function diagnosticProfiles(): array
    {
        return [
            'C25_G00_CANONICAL_BASELINE' => ['family' => 'baseline', 'source' => 'canonical'],
            'C25_G01_C22_S06_SHADOW_BENCHMARK' => ['family' => 'shadow_benchmark', 'source' => 'c22_s06'],
            'C25_G02_C23_R09_BASELINE_BRIDGE' => ['family' => 'r09_bridge', 'source' => 'r09'],
            'C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR' => ['family' => 'downside_comparator', 'source' => 'r15'],
            'C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR' => ['family' => 'downside_comparator', 'source' => 'r16'],
            'C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN' => ['family' => 'no_signal_fallback', 'source' => 'r13_if_no_signal_else_r09'],
            'C25_G06_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN' => ['family' => 'no_signal_fallback', 'source' => 'r14_if_no_signal_else_r09'],
            'C25_G07_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN_IF_MAE_LT_2PCT' => ['family' => 'no_signal_fallback', 'source' => 'r13_if_no_signal_and_mae_lt_2pct_else_r09'],
            'C25_G08_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN_IF_MAE_LT_2PCT' => ['family' => 'no_signal_fallback', 'source' => 'r14_if_no_signal_and_mae_lt_2pct_else_r09'],
            'C25_G09_R09_PLUS_NO_SIGNAL_D3_DAMAGE_CONTROL' => ['family' => 'combo_no_signal_damage_control', 'source' => 'r13_if_no_signal_else_r09'],
            'C25_G10_R09_PLUS_NO_SIGNAL_D4_DAMAGE_CONTROL' => ['family' => 'combo_no_signal_damage_control', 'source' => 'r14_if_no_signal_else_r09'],
            'C25_G11_R09_PLUS_R15_STYLE_DOWNSIDE_CONTROL' => ['family' => 'combo_downside_control', 'source' => 'r15'],
            'C25_G12_R09_PLUS_R16_STYLE_DOWNSIDE_CONTROL' => ['family' => 'combo_downside_control', 'source' => 'r16'],
            'C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT' => ['family' => 'preplanned_intraday_target', 'target_pct' => 0.0050, 'source' => 'preplanned_target_or_r09'],
            'C25_G14_PREPLANNED_INTRADAY_TARGET_0_75PCT' => ['family' => 'preplanned_intraday_target', 'target_pct' => 0.0075, 'source' => 'preplanned_target_or_r09'],
            'C25_G15_PREPLANNED_INTRADAY_TARGET_1_00PCT' => ['family' => 'preplanned_intraday_target', 'target_pct' => 0.0100, 'source' => 'preplanned_target_or_r09'],
            'C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT' => ['family' => 'preplanned_intraday_target', 'target_pct' => 0.0150, 'source' => 'preplanned_target_or_r09'],
            'C25_G17_PREPLANNED_TARGET_0_75PCT_WITH_STOP_1_50PCT' => ['family' => 'preplanned_intraday_target_stop', 'target_pct' => 0.0075, 'stop_pct' => 0.0150, 'source' => 'preplanned_target_stop_or_r09'],
            'C25_G18_PREPLANNED_TARGET_1_00PCT_WITH_STOP_2_00PCT' => ['family' => 'preplanned_intraday_target_stop', 'target_pct' => 0.0100, 'stop_pct' => 0.0200, 'source' => 'preplanned_target_stop_or_r09'],
            'C25_G19_NEXT_OPEN_DELAY_ROWS_ONLY_R09' => ['family' => 'bucket_rows_only', 'source' => 'r09', 'bucket' => 'next_open_delay_after_close_signal'],
            'C25_G20_NO_SIGNAL_FALLBACK_ROWS_ONLY_R09' => ['family' => 'bucket_rows_only', 'source' => 'r09', 'bucket' => 'no_rule_profit_signal_before_fallback'],
            'C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT' => ['family' => 'combo_intraday_no_signal', 'target_pct' => 0.0100, 'source' => 'preplanned_target_or_no_signal_r13_or_r09'],
        ];
    }

    public function execute(string $c23InputPath = '', string $c24InputPath = '', string $outputPath = '', array $options = []): array
    {
        $catalogCode = (string) ($options['catalog_code'] ?? self::DEFAULT_SOURCE_CATALOG_CODE);
        $fromDate = (string) ($options['from'] ?? WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $toDate = (string) ($options['to'] ?? WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $c23InputPath = trim($c23InputPath) !== '' ? trim($c23InputPath) : self::DEFAULT_C23_INPUT_PATH;
        $c24InputPath = trim($c24InputPath) !== '' ? trim($c24InputPath) : self::DEFAULT_C24_INPUT_PATH;
        $c21InputPath = trim((string) ($options['input_c21_artifact'] ?? self::DEFAULT_C21_INPUT_PATH));
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C25_IS_ONLY_WINDOW_MISMATCH', 'C25 no-signal fallback and next-open delay diagnostic requires the frozen IS window only.');
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.');
        }

        $c23 = $this->readJson($c23InputPath);
        if ($c23 === null) {
            return $this->blocked('WS_BT_C25_C23_ARTIFACT_UNREADABLE', 'C25 requires a readable C23 all-param first-profit-capture diagnostic artifact.');
        }
        if (($c23['artifact_type'] ?? null) !== 'C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC' || ($c23['status'] ?? null) !== 'PASS') {
            return $this->blocked('WS_BT_C25_C23_ARTIFACT_INVALID', 'C25 requires a PASS C23 first-profit-capture diagnostic artifact.');
        }

        $c24 = $this->readJson($c24InputPath);
        if ($c24 === null) {
            return $this->blocked('WS_BT_C25_C24_ARTIFACT_UNREADABLE', 'C25 requires a readable C24 gap bridge diagnostic artifact.');
        }
        if (($c24['artifact_type'] ?? null) !== 'C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC' || ($c24['status'] ?? null) !== 'PASS') {
            return $this->blocked('WS_BT_C25_C24_ARTIFACT_INVALID', 'C25 requires a PASS C24 C22-shadow gap bridge diagnostic artifact.');
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C25_PARAM_IDS_INVALID', 'param-ids must be a comma-separated list of positive integers.');
        }
        $profileCodes = $this->profileCodes($options['diagnostic_profile_codes'] ?? ($options['profile_codes'] ?? null));
        if ($profileCodes === false) {
            return $this->blocked('WS_BT_C25_DIAGNOSTIC_PROFILE_INVALID', 'diagnostic-profile-codes/profile-codes must contain known C25 diagnostic profile codes only.');
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
            return $this->blocked('WS_BT_C25_DIAGNOSTIC_PROFILE_EMPTY', 'No C25 diagnostic profiles selected.');
        }

        $c21 = $this->readJson($c21InputPath);
        $c21Rows = $this->c21RowsByPickKey($c21);
        $rowsByProfile = $this->c23RowsByProfileAndPick($c23, $paramIds);
        $requiredProfiles = [self::C23_R00, self::C23_R09, self::C23_R13, self::C23_R14, self::C23_R15, self::C23_R16];
        foreach ($requiredProfiles as $required) {
            if (! isset($rowsByProfile[$required]) || $rowsByProfile[$required] === []) {
                return $this->blocked('WS_BT_C25_REQUIRED_C23_PROFILE_MISSING', 'C25 requires canonical, R09, R13, R14, R15, and R16 rows in the C23 artifact.', ['missing_profile_code' => $required]);
            }
        }

        $baseRows = array_values($rowsByProfile[self::C23_R09]);
        $baseRows = array_values(array_filter($baseRows, function (array $row): bool {
            return ($row['missing_path_data_flag'] ?? true) === false && is_numeric($row['rule_ret_net'] ?? null) && is_numeric($row['c22_shadow_s06_ret_net'] ?? null);
        }));
        $baseRows = $this->sortRows($baseRows);
        $maxPicks = $this->positiveIntOrNull($options['max_picks'] ?? null);
        if ($maxPicks !== null) {
            $baseRows = array_slice($baseRows, 0, $maxPicks);
        }
        if ($baseRows === []) {
            return $this->blocked('WS_BT_C25_R09_ROWS_EMPTY', 'C25 requires evaluated C23 R09 rows after filtering.');
        }

        $pickRows = [];
        $missingCompanionRows = 0;
        foreach ($baseRows as $index => $r09) {
            if (! empty($options['progress_callback']) && is_callable($options['progress_callback']) && ($index % 250 === 0)) {
                ($options['progress_callback'])('[C25] pick '.($index + 1).'/'.count($baseRows).' profiles='.count($profileCodes));
            }
            $key = $this->pickKey($r09);
            $companions = [
                'canonical' => $rowsByProfile[self::C23_R00][$key] ?? null,
                'r09' => $r09,
                'r13' => $rowsByProfile[self::C23_R13][$key] ?? null,
                'r14' => $rowsByProfile[self::C23_R14][$key] ?? null,
                'r15' => $rowsByProfile[self::C23_R15][$key] ?? null,
                'r16' => $rowsByProfile[self::C23_R16][$key] ?? null,
                'c21' => $c21Rows[$key] ?? null,
            ];
            foreach (['canonical', 'r13', 'r14', 'r15', 'r16'] as $requiredCompanion) {
                if (! is_array($companions[$requiredCompanion])) {
                    $missingCompanionRows++;
                }
            }
            $bucket = $this->gapComponent($r09);
            foreach ($profileCodes as $profileCode) {
                $profileRow = $this->profileRow($profileCode, $companions, $bucket);
                if ($profileRow !== null) {
                    $pickRows[] = $profileRow;
                }
            }
        }

        $baselineSummaries = $this->baselineSummaries($c23);
        $bucketSummary = $this->bucketSummary($baseRows);
        $profileSummary = $this->profileSummary($pickRows);
        $noSignalSummary = $this->bucketProfileComparison($pickRows, 'no_rule_profit_signal_before_fallback');
        $nextOpenSummary = $this->bucketProfileComparison($pickRows, 'next_open_delay_after_close_signal');
        $r09VsR15R16Summary = $this->r09VsR15R16Summary($baselineSummaries, $profileSummary);
        $paramConsistencySummary = $this->paramConsistencySummary($pickRows, $profileSummary);
        $monthStabilitySummary = $this->monthStabilitySummary($pickRows);
        $lookaheadSafetySummary = $this->lookaheadSafetySummary($pickRows);
        $decision = $this->decision($profileSummary, $noSignalSummary, $nextOpenSummary, $paramConsistencySummary, $lookaheadSafetySummary);
        $dataAvailability = $this->dataAvailability($c23, $c24, $c21, $rowsByProfile, $c21Rows, $missingCompanionRows);

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_NO_SIGNAL_FALLBACK_AND_NEXT_OPEN_DELAY_DIAGNOSTIC',
            'generated_at' => (string) ($options['executed_at'] ?? gmdate('c')),
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'policy_code' => 'WS',
                'mutated' => false,
            ],
            'source_evidence' => [
                'c19_final_status' => 'C19_CATALOG_CANDIDATE_FAILED',
                'c20_final_status' => 'C20_DATE_GATE_NOT_ENOUGH',
                'c21_final_status' => 'C21_EXECUTION_SIGNAL_FOUND',
                'c22_final_status' => 'C22_EXIT_CAPTURE_SIGNAL_FOUND',
                'c23_final_status' => 'C23_NON_LOOKAHEAD_RULE_CANDIDATE_FOUND_BUT_SHADOW_GAP_NOT_ACCEPTABLE',
                'c24_final_status' => 'C24_GAP_BRIDGE_EXPLAINED',
                'c23_all_param_artifact_path' => $c23InputPath,
                'c23_all_param_artifact_hash' => $c23['artifact_hash'] ?? null,
                'c24_all_param_artifact_path' => $c24InputPath,
                'c24_all_param_artifact_hash' => $c24['artifact_hash'] ?? null,
                'c21_path_artifact_path' => is_array($c21) ? $c21InputPath : null,
                'c21_path_artifact_hash' => is_array($c21) ? ($c21['artifact_hash'] ?? null) : null,
            ],
            'is_window' => ['from' => $fromDate, 'to' => $toDate],
            'data_availability' => $dataAvailability,
            'diagnostic_profiles' => $this->profileDefinitions($profileCodes),
            'profile_scope' => $profileScope,
            'pick_diagnostic_rows' => $pickRows,
            'baseline_summaries' => $baselineSummaries,
            'bucket_summary' => $bucketSummary,
            'no_signal_fallback_summary' => $noSignalSummary,
            'next_open_delay_summary' => $nextOpenSummary,
            'r09_vs_r15_r16_summary' => $r09VsR15R16Summary,
            'profile_summary' => $profileSummary,
            'param_consistency_summary' => $paramConsistencySummary,
            'month_stability_summary' => $monthStabilitySummary,
            'lookahead_safety_summary' => $lookaheadSafetySummary,
            'decision' => $decision,
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message']);
        }

        $bestAvg = $this->bestByMetric($profileSummary, 'avg_ret_net');
        $bestMedian = $this->bestByMetric($profileSummary, 'median_ret_net');
        $bestP25 = $this->bestByMetric($profileSummary, 'p25_ret_net');
        $bestDistribution = $this->bestDistributionBalance($profileSummary);

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_NO_SIGNAL_FALLBACK_AND_NEXT_OPEN_DELAY_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'diagnostic_profile_count' => count($profileCodes),
            'profile_scope' => $profileScope,
            'evaluated_picks_count' => count($baseRows),
            'path_missing_count' => (int) ($baselineSummaries['canonical_summary']['path_missing_count'] ?? 0),
            'c23_input_artifact_hash' => $c23['artifact_hash'] ?? null,
            'c24_input_artifact_hash' => $c24['artifact_hash'] ?? null,
            'canonical_avg_ret_net' => $baselineSummaries['canonical_summary']['canonical_avg_ret_net'] ?? null,
            'c22_s06_avg_ret_net' => $baselineSummaries['c22_s06_summary']['c22_shadow_s06_avg_ret_net'] ?? null,
            'c23_r09_avg_ret_net' => $baselineSummaries['c23_r09_summary']['avg_ret_net'] ?? null,
            'c23_r15_p25_ret_net' => $baselineSummaries['c23_r15_summary']['p25_ret_net'] ?? null,
            'c23_r16_p25_ret_net' => $baselineSummaries['c23_r16_summary']['p25_ret_net'] ?? null,
            'no_signal_fallback_count' => $bucketSummary['no_rule_profit_signal_before_fallback']['count'] ?? 0,
            'next_open_delay_count' => $bucketSummary['next_open_delay_after_close_signal']['count'] ?? 0,
            'best_profile_code_by_avg' => $bestAvg['profile_code'] ?? null,
            'best_profile_code_by_median' => $bestMedian['profile_code'] ?? null,
            'best_profile_code_by_p25' => $bestP25['profile_code'] ?? null,
            'best_profile_code_by_distribution_balance' => $bestDistribution['profile_code'] ?? null,
            'best_no_signal_fallback_profile' => $noSignalSummary['best_profile_code_by_avg_delta_vs_c23_r09'] ?? null,
            'best_next_open_delay_profile' => $nextOpenSummary['best_profile_code_by_avg_delta_vs_c23_r09'] ?? null,
            'no_signal_fallback_fix_found' => $decision['no_signal_fallback_fix_found'] ? 1 : 0,
            'next_open_delay_fix_found' => $decision['next_open_delay_fix_found'] ? 1 : 0,
            'distribution_balance_candidate_found' => $decision['distribution_balance_candidate_found'] ? 1 : 0,
            'intraday_preplanned_order_candidate_found' => $decision['intraday_preplanned_order_candidate_found'] ? 1 : 0,
            'exit_rule_path_still_viable' => $decision['exit_rule_path_still_viable'] ? 1 : 0,
            'selection_quality_revisit_needed' => $decision['selection_quality_revisit_needed'] ? 1 : 0,
            'c26_catalog_candidate_diagnostic_recommended' => $decision['c26_catalog_candidate_diagnostic_recommended'] ? 1 : 0,
            'c25_catalog_implementation_deferred' => 1,
            'c25_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function profileRow(string $profileCode, array $companions, string $bucket): ?array
    {
        $profiles = self::diagnosticProfiles();
        $profile = $profiles[$profileCode] ?? null;
        if ($profile === null) {
            return null;
        }
        if (($profile['family'] ?? null) === 'bucket_rows_only' && ($profile['bucket'] ?? null) !== $bucket) {
            return null;
        }
        $r09 = $companions['r09'];
        $source = $this->resolveProfileReturn($profileCode, $profile, $companions, $bucket);
        if ($source === null) {
            $source = $this->rowSource($r09, 'rule', 'source_unavailable_fallback_r09');
            $source['missing_path_data_flag'] = true;
            $source['missing_path_reason_code'] = 'WS_BT_C25_PROFILE_SOURCE_UNAVAILABLE';
        }
        $c21 = is_array($companions['c21']) ? $companions['c21'] : [];
        $c22 = $this->num($r09['c22_shadow_s06_ret_net'] ?? null);
        $canonical = $this->num($r09['canonical_ret_net'] ?? null);
        $r09Ret = $this->num($r09['rule_ret_net'] ?? null);
        $r15 = is_array($companions['r15'] ?? null) ? $this->num($companions['r15']['rule_ret_net'] ?? null) : null;
        $r16 = is_array($companions['r16'] ?? null) ? $this->num($companions['r16']['rule_ret_net'] ?? null) : null;
        $profileRet = $this->num($source['ret_net'] ?? null);
        $firstIntradayTargetHitDay = $this->firstTargetHitDay($c21, (float) ($profile['target_pct'] ?? 0));
        $nextOpenImpact = $bucket === 'next_open_delay_after_close_signal' && $c22 !== null && $r09Ret !== null ? $c22 - $r09Ret : null;

        return [
            'trade_date' => $r09['trade_date'] ?? null,
            'ticker_id' => $r09['ticker_id'] ?? null,
            'ticker' => $r09['ticker'] ?? null,
            'param_id' => $r09['param_id'] ?? null,
            'row_code' => $r09['row_code'] ?? null,
            'entry_date' => $r09['entry_date'] ?? null,
            'entry_price' => $r09['entry_price'] ?? null,
            'signal_close_price' => $r09['signal_close_price'] ?? null,
            'bucket_code' => $bucket,
            'bucket_reason' => $this->bucketReason($bucket),
            'canonical_exit_date' => $r09['canonical_exit_date'] ?? null,
            'canonical_exit_price' => $r09['canonical_exit_price'] ?? null,
            'canonical_exit_reason' => $r09['canonical_exit_reason'] ?? null,
            'canonical_ret_net' => $canonical,
            'c22_s06_exit_date' => $r09['c22_shadow_s06_exit_date'] ?? null,
            'c22_s06_exit_price' => $r09['c22_shadow_s06_exit_price'] ?? null,
            'c22_s06_ret_net' => $c22,
            'c23_r09_exit_date' => $r09['rule_exit_date'] ?? null,
            'c23_r09_exit_price' => $r09['rule_exit_price'] ?? null,
            'c23_r09_ret_net' => $r09Ret,
            'c23_r15_ret_net' => $r15,
            'c23_r16_ret_net' => $r16,
            'd1_open' => null,
            'd1_high' => null,
            'd1_low' => null,
            'd1_close' => null,
            'd2_open' => null,
            'd2_high' => null,
            'd2_low' => null,
            'd2_close' => null,
            'd3_open' => null,
            'd3_high' => null,
            'd3_low' => null,
            'd3_close' => null,
            'd4_open' => null,
            'd4_high' => null,
            'd4_low' => null,
            'd4_close' => null,
            'd5_open' => null,
            'd5_high' => null,
            'd5_low' => null,
            'd5_close' => null,
            'd1_close_ret' => $c21['d1_close_ret'] ?? null,
            'd2_close_ret' => $c21['d2_close_ret'] ?? null,
            'd3_close_ret' => $c21['d3_close_ret'] ?? null,
            'd4_close_ret' => $c21['d4_close_ret'] ?? null,
            'd5_close_ret' => $c21['d5_close_ret'] ?? null,
            'mfe_1d' => $c21['mfe_1d'] ?? null,
            'mfe_2d' => $c21['mfe_2d'] ?? null,
            'mfe_3d' => $c21['mfe_3d'] ?? null,
            'mfe_4d' => $c21['mfe_4d'] ?? null,
            'mfe_5d' => $c21['mfe_5d'] ?? null,
            'mae_1d' => $c21['mae_1d'] ?? null,
            'mae_2d' => $c21['mae_2d'] ?? null,
            'mae_3d' => $c21['mae_3d'] ?? null,
            'mae_4d' => $c21['mae_4d'] ?? null,
            'mae_5d' => $c21['mae_5d'] ?? null,
            'max_favorable_excursion_pct' => $r09['max_favorable_excursion_pct'] ?? null,
            'max_adverse_excursion_pct' => $r09['max_adverse_excursion_pct'] ?? null,
            'first_profitable_close_day' => $r09['first_profitable_close_day'] ?? null,
            'first_intraday_target_hit_day' => $firstIntradayTargetHitDay,
            'close_signal_day' => $r09['rule_signal_day_offset'] ?? null,
            'next_open_exit_day' => $r09['rule_exit_day_offset'] ?? null,
            'next_open_delay_return_impact' => $nextOpenImpact,
            'no_signal_before_fallback_flag' => $bucket === 'no_rule_profit_signal_before_fallback',
            'next_open_delay_gap_flag' => $bucket === 'next_open_delay_after_close_signal',
            'profile_code' => $profileCode,
            'profile_family' => $profile['family'] ?? null,
            'profile_exit_date' => $source['exit_date'] ?? null,
            'profile_exit_price' => $source['exit_price'] ?? null,
            'profile_exit_reason' => $source['exit_reason'] ?? null,
            'profile_ret_net' => $profileRet,
            'delta_vs_canonical' => $this->delta($profileRet, $canonical),
            'delta_vs_c22_s06' => $this->delta($profileRet, $c22),
            'delta_vs_c23_r09' => $this->delta($profileRet, $r09Ret),
            'delta_vs_c23_r15' => $this->delta($profileRet, $r15),
            'delta_vs_c23_r16' => $this->delta($profileRet, $r16),
            'profit_capture_gap_vs_c22_s06' => $profileRet !== null && $c22 !== null ? $c22 - $profileRet : null,
            'loss_reduced_flag' => $canonical !== null && $canonical < 0 && $profileRet !== null && $profileRet > $canonical,
            'p25_protection_flag' => $r09Ret !== null && $profileRet !== null && $profileRet > $r09Ret,
            'win_flag' => $profileRet !== null && $profileRet > 0,
            'lookahead_safe' => (bool) ($source['lookahead_safe'] ?? true),
            'uses_intraday_high_low' => (bool) ($source['uses_intraday_high_low'] ?? false),
            'preplanned_order' => (bool) ($source['preplanned_order'] ?? false),
            'intraday_sequence_known' => (bool) ($source['intraday_sequence_known'] ?? false),
            'ambiguous_intraday_sequence_flag' => (bool) ($source['ambiguous_intraday_sequence_flag'] ?? false),
            'conservative_fill_policy' => $source['conservative_fill_policy'] ?? null,
            'missing_path_data_flag' => (bool) ($source['missing_path_data_flag'] ?? false),
            'missing_path_reason_code' => $source['missing_path_reason_code'] ?? null,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
        ];
    }

    private function resolveProfileReturn(string $profileCode, array $profile, array $companions, string $bucket): ?array
    {
        $source = (string) ($profile['source'] ?? 'r09');
        if ($source === 'canonical') {
            return $this->rowSource($companions['r09'], 'canonical', 'canonical_baseline');
        }
        if ($source === 'c22_s06') {
            return $this->rowSource($companions['r09'], 'c22_s06', 'c22_s06_shadow_benchmark');
        }
        if ($source === 'r09') {
            return $this->rowSource($companions['r09'], 'rule', 'c23_r09_baseline_bridge');
        }
        if ($source === 'r15') {
            return $this->rowSource($companions['r15'] ?? null, 'rule', 'c23_r15_downside_comparator');
        }
        if ($source === 'r16') {
            return $this->rowSource($companions['r16'] ?? null, 'rule', 'c23_r16_downside_comparator');
        }
        if ($source === 'r13_if_no_signal_else_r09') {
            return $this->rowSource($bucket === 'no_rule_profit_signal_before_fallback' ? ($companions['r13'] ?? null) : $companions['r09'], 'rule', $bucket === 'no_rule_profit_signal_before_fallback' ? 'c23_r13_no_signal_d3_damage_control' : 'c23_r09_non_no_signal_fallback');
        }
        if ($source === 'r14_if_no_signal_else_r09') {
            return $this->rowSource($bucket === 'no_rule_profit_signal_before_fallback' ? ($companions['r14'] ?? null) : $companions['r09'], 'rule', $bucket === 'no_rule_profit_signal_before_fallback' ? 'c23_r14_no_signal_d4_damage_control' : 'c23_r09_non_no_signal_fallback');
        }
        if ($source === 'r13_if_no_signal_and_mae_lt_2pct_else_r09') {
            $mae = $this->num($companions['r09']['max_adverse_excursion_pct'] ?? null);
            $useDamage = $bucket === 'no_rule_profit_signal_before_fallback' && $mae !== null && $mae > -0.0200;
            return $this->rowSource($useDamage ? ($companions['r13'] ?? null) : $companions['r09'], 'rule', $useDamage ? 'c23_r13_no_signal_d3_if_mae_lt_2pct' : 'c23_r09_fallback_mae_gate_not_met');
        }
        if ($source === 'r14_if_no_signal_and_mae_lt_2pct_else_r09') {
            $mae = $this->num($companions['r09']['max_adverse_excursion_pct'] ?? null);
            $useDamage = $bucket === 'no_rule_profit_signal_before_fallback' && $mae !== null && $mae > -0.0200;
            return $this->rowSource($useDamage ? ($companions['r14'] ?? null) : $companions['r09'], 'rule', $useDamage ? 'c23_r14_no_signal_d4_if_mae_lt_2pct' : 'c23_r09_fallback_mae_gate_not_met');
        }
        if ($source === 'preplanned_target_or_r09') {
            return $this->preplannedTargetSource($companions, (float) ($profile['target_pct'] ?? 0), null);
        }
        if ($source === 'preplanned_target_stop_or_r09') {
            return $this->preplannedTargetSource($companions, (float) ($profile['target_pct'] ?? 0), (float) ($profile['stop_pct'] ?? 0));
        }
        if ($source === 'preplanned_target_or_no_signal_r13_or_r09') {
            $target = $this->preplannedTargetSource($companions, (float) ($profile['target_pct'] ?? 0), null);
            if ($target !== null && ($target['exit_reason'] ?? null) === 'preplanned_intraday_target_hit') {
                return $target;
            }
            if ($bucket === 'no_rule_profit_signal_before_fallback') {
                return $this->rowSource($companions['r13'] ?? null, 'rule', 'c23_r13_no_signal_d3_damage_control_after_target_not_hit');
            }
            return $this->rowSource($companions['r09'], 'rule', 'c23_r09_target_not_hit');
        }
        return null;
    }

    private function rowSource(?array $row, string $type, string $reason): ?array
    {
        if (! is_array($row)) {
            return null;
        }
        if ($type === 'canonical') {
            return [
                'exit_date' => $row['canonical_exit_date'] ?? null,
                'exit_price' => $row['canonical_exit_price'] ?? null,
                'exit_reason' => $reason,
                'ret_net' => $this->num($row['canonical_ret_net'] ?? null),
                'lookahead_safe' => true,
                'uses_intraday_high_low' => false,
                'preplanned_order' => false,
                'intraday_sequence_known' => false,
                'ambiguous_intraday_sequence_flag' => false,
                'missing_path_data_flag' => (bool) ($row['missing_path_data_flag'] ?? false),
                'missing_path_reason_code' => $row['missing_path_reason_code'] ?? null,
            ];
        }
        if ($type === 'c22_s06') {
            return [
                'exit_date' => $row['c22_shadow_s06_exit_date'] ?? null,
                'exit_price' => $row['c22_shadow_s06_exit_price'] ?? null,
                'exit_reason' => $reason,
                'ret_net' => $this->num($row['c22_shadow_s06_ret_net'] ?? null),
                'lookahead_safe' => true,
                'uses_intraday_high_low' => false,
                'preplanned_order' => false,
                'intraday_sequence_known' => false,
                'ambiguous_intraday_sequence_flag' => false,
                'missing_path_data_flag' => (bool) ($row['missing_path_data_flag'] ?? false),
                'missing_path_reason_code' => $row['missing_path_reason_code'] ?? null,
            ];
        }
        return [
            'exit_date' => $row['rule_exit_date'] ?? null,
            'exit_price' => $row['rule_exit_price'] ?? null,
            'exit_reason' => $reason,
            'ret_net' => $this->num($row['rule_ret_net'] ?? null),
            'lookahead_safe' => (bool) ($row['lookahead_safe'] ?? true),
            'uses_intraday_high_low' => false,
            'preplanned_order' => false,
            'intraday_sequence_known' => false,
            'ambiguous_intraday_sequence_flag' => false,
            'missing_path_data_flag' => (bool) ($row['missing_path_data_flag'] ?? false),
            'missing_path_reason_code' => $row['missing_path_reason_code'] ?? null,
        ];
    }

    private function preplannedTargetSource(array $companions, float $targetPct, ?float $stopPct): ?array
    {
        $r09 = $companions['r09'];
        $c21 = is_array($companions['c21'] ?? null) ? $companions['c21'] : [];
        if ($targetPct <= 0 || $c21 === []) {
            $fallback = $this->rowSource($r09, 'rule', 'preplanned_intraday_source_unavailable_fallback_r09');
            if ($fallback !== null) {
                $fallback['missing_path_data_flag'] = true;
                $fallback['missing_path_reason_code'] = 'WS_BT_C25_C21_DERIVED_MFE_MAE_UNAVAILABLE';
            }
            return $fallback;
        }
        $targetDay = $this->firstTargetHitDay($c21, $targetPct);
        $stopDay = $stopPct !== null && $stopPct > 0 ? $this->firstStopHitDay($c21, $stopPct) : null;
        if ($targetDay === null && $stopDay === null) {
            return $this->rowSource($r09, 'rule', 'preplanned_intraday_order_not_hit_fallback_r09');
        }
        $hitStop = false;
        $ambiguous = false;
        if ($stopDay !== null && ($targetDay === null || $stopDay < $targetDay)) {
            $hitStop = true;
        } elseif ($stopDay !== null && $targetDay !== null && $stopDay === $targetDay) {
            $hitStop = true;
            $ambiguous = true;
        }
        $entryPrice = $this->num($r09['entry_price'] ?? null);
        if ($entryPrice === null || $entryPrice <= 0) {
            return $this->rowSource($r09, 'rule', 'preplanned_intraday_entry_price_missing_fallback_r09');
        }
        $grossPct = $hitStop ? -1.0 * (float) $stopPct : $targetPct;
        $exitPrice = $entryPrice * (1.0 + $grossPct);
        $retNet = $this->netReturnFromGrossPct($r09, $grossPct);
        $day = $hitStop ? $stopDay : $targetDay;
        return [
            'exit_date' => $c21['entry_date'] ?? ($r09['entry_date'] ?? null),
            'exit_price' => $exitPrice,
            'exit_reason' => $hitStop ? 'preplanned_intraday_stop_hit_conservative' : 'preplanned_intraday_target_hit',
            'ret_net' => $retNet,
            'lookahead_safe' => true,
            'uses_intraday_high_low' => true,
            'preplanned_order' => true,
            'intraday_sequence_known' => false,
            'ambiguous_intraday_sequence_flag' => $ambiguous,
            'conservative_fill_policy' => $ambiguous ? 'TARGET_AND_STOP_SAME_DAILY_CANDLE_STOP_FIRST' : 'STOP_FIRST_IF_TARGET_AND_STOP_SAME_DAILY_CANDLE',
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
            'hit_day' => $day,
        ];
    }

    private function netReturnFromGrossPct(array $row, float $grossPct): float
    {
        $entryPrice = $this->num($row['entry_price'] ?? null);
        $exitPrice = $this->num($row['rule_exit_price'] ?? null);
        $ruleRet = $this->num($row['rule_ret_net'] ?? null);
        if ($entryPrice !== null && $entryPrice > 0 && $exitPrice !== null && $exitPrice > 0 && $ruleRet !== null) {
            $knownGross = ($exitPrice - $entryPrice) / $entryPrice;
            $feeDrag = $knownGross - $ruleRet;
            return $grossPct - $feeDrag;
        }
        return $grossPct - 0.0005;
    }

    private function baselineSummaries(array $c23): array
    {
        return [
            'canonical_summary' => is_array($c23['canonical_summary'] ?? null) ? $c23['canonical_summary'] : [],
            'c22_s06_summary' => is_array($c23['c22_shadow_s06_summary'] ?? null) ? $c23['c22_shadow_s06_summary'] : [],
            'c23_r09_summary' => $this->c23ProfileSummary($c23, self::C23_R09),
            'c23_r15_summary' => $this->c23ProfileSummary($c23, self::C23_R15),
            'c23_r16_summary' => $this->c23ProfileSummary($c23, self::C23_R16),
        ];
    }

    private function bucketSummary(array $r09Rows): array
    {
        $groups = [];
        foreach ($r09Rows as $row) {
            $groups[$this->gapComponent($row)][] = $row;
        }
        $out = [];
        foreach (['candidate_matches_or_beats_c22', 'next_open_delay_after_close_signal', 'no_rule_profit_signal_before_fallback', 'late_rule_signal_after_c22_s06', 'other_gap_rows'] as $bucket) {
            $out[$bucket] = $this->r09RowsSummary($groups[$bucket] ?? [], ['bucket_code' => $bucket]);
        }
        return $out;
    }

    private function r09RowsSummary(array $rows, array $prefix = []): array
    {
        $r09 = $this->values($rows, 'rule_ret_net');
        $c22 = $this->values($rows, 'c22_shadow_s06_ret_net');
        $canonical = $this->values($rows, 'canonical_ret_net');
        $mfe = $this->values($rows, 'max_favorable_excursion_pct');
        $mae = $this->values($rows, 'max_adverse_excursion_pct');
        $gaps = [];
        $beats = 0;
        foreach ($rows as $row) {
            if (is_numeric($row['c22_shadow_s06_ret_net'] ?? null) && is_numeric($row['rule_ret_net'] ?? null)) {
                $gap = (float) $row['c22_shadow_s06_ret_net'] - (float) $row['rule_ret_net'];
                $gaps[] = $gap;
                if ($gap > 0.0000001) {
                    $beats++;
                }
            }
        }
        return array_merge($prefix, [
            'count' => count($rows),
            'c23_r09_avg_ret_net' => $this->avg($r09),
            'c22_s06_avg_ret_net' => $this->avg($c22),
            'canonical_avg_ret_net' => $this->avg($canonical),
            'median_gap_c22_minus_r09' => $this->median($gaps),
            'avg_gap_c22_minus_r09' => $this->avg($gaps),
            'c22_beats_r09_rate' => count($rows) > 0 ? $beats / count($rows) : null,
            'mfe_avg' => $this->avg($mfe),
            'mae_avg' => $this->avg($mae),
        ]);
    }

    private function profileSummary(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $groups[(string) ($row['profile_code'] ?? 'UNKNOWN')][] = $row;
        }
        ksort($groups, SORT_STRING);
        $out = [];
        foreach ($groups as $profileCode => $profileRows) {
            $out[] = $this->profileRowsSummary($profileRows, $profileCode);
        }
        return $out;
    }

    private function profileRowsSummary(array $rows, string $profileCode): array
    {
        $returns = $this->values($rows, 'profile_ret_net');
        $deltaCanonical = $this->values($rows, 'delta_vs_canonical');
        $deltaC22 = $this->values($rows, 'delta_vs_c22_s06');
        $deltaR09 = $this->values($rows, 'delta_vs_c23_r09');
        $gapC22 = $this->values($rows, 'profit_capture_gap_vs_c22_s06');
        $wins = 0;
        $lossReduced = 0;
        $p25Protected = 0;
        $lookaheadViolations = 0;
        $ambiguous = 0;
        $intraday = 0;
        $preplanned = 0;
        $exitReasons = [];
        $exitDays = [];
        $params = [];
        foreach ($rows as $row) {
            if (($row['win_flag'] ?? false) === true) {
                $wins++;
            }
            if (($row['loss_reduced_flag'] ?? false) === true) {
                $lossReduced++;
            }
            if (($row['p25_protection_flag'] ?? false) === true) {
                $p25Protected++;
            }
            if (($row['lookahead_safe'] ?? true) !== true) {
                $lookaheadViolations++;
            }
            if (($row['ambiguous_intraday_sequence_flag'] ?? false) === true) {
                $ambiguous++;
            }
            if (($row['uses_intraday_high_low'] ?? false) === true) {
                $intraday++;
            }
            if (($row['preplanned_order'] ?? false) === true) {
                $preplanned++;
            }
            $reason = (string) ($row['profile_exit_reason'] ?? 'NONE');
            $exitReasons[$reason] = ($exitReasons[$reason] ?? 0) + 1;
            $day = (string) ($row['next_open_exit_day'] ?? 'NONE');
            $exitDays[$day] = ($exitDays[$day] ?? 0) + 1;
            if (isset($row['param_id'])) {
                $params[(string) $row['param_id']] = true;
            }
        }
        ksort($exitReasons, SORT_STRING);
        ksort($exitDays, SORT_STRING);
        $profile = self::diagnosticProfiles()[$profileCode] ?? [];
        return [
            'profile_code' => $profileCode,
            'profile_family' => $profile['family'] ?? null,
            'evaluated_picks_count' => count($rows),
            'avg_ret_net' => $this->avg($returns),
            'median_ret_net' => $this->median($returns),
            'p25_ret_net' => $this->percentile($returns, 0.25),
            'win_rate' => count($rows) > 0 ? $wins / count($rows) : null,
            'avg_delta_vs_canonical' => $this->avg($deltaCanonical),
            'median_delta_vs_canonical' => $this->median($deltaCanonical),
            'p25_delta_vs_canonical' => $this->percentile($deltaCanonical, 0.25),
            'avg_delta_vs_c22_s06' => $this->avg($deltaC22),
            'median_delta_vs_c22_s06' => $this->median($deltaC22),
            'p25_delta_vs_c22_s06' => $this->percentile($deltaC22, 0.25),
            'avg_delta_vs_c23_r09' => $this->avg($deltaR09),
            'median_delta_vs_c23_r09' => $this->median($deltaR09),
            'p25_delta_vs_c23_r09' => $this->percentile($deltaR09, 0.25),
            'profit_capture_gap_vs_c22_s06_avg' => $this->avg($gapC22),
            'profit_capture_gap_vs_c22_s06_median' => $this->median($gapC22),
            'loss_reduction_rate' => count($rows) > 0 ? $lossReduced / count($rows) : null,
            'p25_protection_rate' => count($rows) > 0 ? $p25Protected / count($rows) : null,
            'lookahead_violation_count' => $lookaheadViolations,
            'ambiguous_intraday_sequence_count' => $ambiguous,
            'uses_intraday_high_low_count' => $intraday,
            'preplanned_order_count' => $preplanned,
            'distinct_param_count' => count($params),
            'exit_reason_distribution' => $exitReasons,
            'exit_day_distribution' => $exitDays,
        ];
    }

    private function bucketProfileComparison(array $rows, string $bucket): array
    {
        $bucketRows = array_values(array_filter($rows, function (array $row) use ($bucket): bool {
            return ($row['bucket_code'] ?? null) === $bucket;
        }));
        $profiles = $this->profileSummary($bucketRows);
        $candidateProfiles = $this->bucketCandidateProfiles($profiles, $bucket);
        $best = $this->bestByMetric($candidateProfiles, 'avg_delta_vs_c23_r09');
        $bestMedian = $this->bestByMetric($candidateProfiles, 'median_ret_net');
        $bestP25 = $this->bestByMetric($candidateProfiles, 'p25_ret_net');
        $targetHitRows = 0;
        $profitLostRows = 0;
        $delayImpacts = [];
        foreach ($bucketRows as $row) {
            if (($row['first_intraday_target_hit_day'] ?? null) !== null) {
                $targetHitRows++;
            }
            if (is_numeric($row['next_open_delay_return_impact'] ?? null)) {
                $delayImpacts[] = (float) $row['next_open_delay_return_impact'];
                if ((float) $row['next_open_delay_return_impact'] > 0) {
                    $profitLostRows++;
                }
            }
        }
        return [
            'bucket_code' => $bucket,
            'count' => count(array_unique(array_map(function (array $row): string { return $this->pickKey($row); }, $bucketRows))),
            'profile_summary' => $profiles,
            'best_profile_code_by_avg_delta_vs_c23_r09' => $best['profile_code'] ?? null,
            'best_profile_code_by_median' => $bestMedian['profile_code'] ?? null,
            'best_profile_code_by_p25' => $bestP25['profile_code'] ?? null,
            'avg_signal_close_to_next_open_gap' => $this->avg($delayImpacts),
            'median_signal_close_to_next_open_gap' => $this->median($delayImpacts),
            'p25_signal_close_to_next_open_gap' => $this->percentile($delayImpacts, 0.25),
            'profit_lost_to_next_open_rate' => $delayImpacts === [] ? null : $profitLostRows / count($delayImpacts),
            'preplanned_target_hit_rate' => count($bucketRows) > 0 ? $targetHitRows / count($bucketRows) : null,
            'preplanned_target_effectiveness' => $this->preplannedEffectiveness($profiles),
            'damage_control_effectiveness' => $this->damageControlEffectiveness($profiles),
            'mfe_mae_distribution' => $this->mfeMaeDistribution($bucketRows),
        ];
    }


    private function bucketCandidateProfiles(array $profiles, string $bucket): array
    {
        if ($bucket === 'no_rule_profit_signal_before_fallback') {
            return array_values(array_filter($profiles, function (array $profile): bool {
                return $this->isNoSignalCandidate($profile);
            }));
        }
        if ($bucket === 'next_open_delay_after_close_signal') {
            return array_values(array_filter($profiles, function (array $profile): bool {
                return (int) ($profile['preplanned_order_count'] ?? 0) > 0;
            }));
        }
        return $profiles;
    }

    private function r09VsR15R16Summary(array $baselineSummaries, array $profileSummary): array
    {
        $r09 = $baselineSummaries['c23_r09_summary'] ?? [];
        $r15 = $baselineSummaries['c23_r15_summary'] ?? [];
        $r16 = $baselineSummaries['c23_r16_summary'] ?? [];
        return [
            'r09' => $r09,
            'r15' => $r15,
            'r16' => $r16,
            'r15_p25_delta_vs_r09' => $this->delta($this->num($r15['p25_ret_net'] ?? null), $this->num($r09['p25_ret_net'] ?? null)),
            'r16_p25_delta_vs_r09' => $this->delta($this->num($r16['p25_ret_net'] ?? null), $this->num($r09['p25_ret_net'] ?? null)),
            'best_by_distribution_balance' => $this->bestDistributionBalance($profileSummary),
        ];
    }

    private function decision(array $profileSummary, array $noSignalSummary, array $nextOpenSummary, array $paramConsistencySummary, array $lookaheadSafetySummary): array
    {
        $noSignalFix = false;
        foreach ($noSignalSummary['profile_summary'] ?? [] as $profile) {
            if (! $this->isNoSignalCandidate($profile)) {
                continue;
            }
            $avgDelta = $this->num($profile['avg_delta_vs_c23_r09'] ?? null) ?? 0.0;
            $p25Delta = $this->num($profile['p25_delta_vs_c23_r09'] ?? null) ?? 0.0;
            $lossReduction = $this->num($profile['loss_reduction_rate'] ?? null) ?? 0.0;
            $medianDelta = $this->medianDeltaVsR09($noSignalSummary, (string) $profile['profile_code']);
            if (($p25Delta >= 0.005 || $avgDelta >= 0.005 || $lossReduction >= 0.20) && $medianDelta >= -0.003) {
                $noSignalFix = true;
                break;
            }
        }

        $nextOpenFix = false;
        $intradayCandidate = false;
        foreach ($nextOpenSummary['profile_summary'] ?? [] as $profile) {
            if ((int) ($profile['preplanned_order_count'] ?? 0) <= 0) {
                continue;
            }
            $intradayCandidate = true;
            $avgDelta = $this->num($profile['avg_delta_vs_c23_r09'] ?? null) ?? 0.0;
            $medianDelta = $this->medianDeltaVsR09($nextOpenSummary, (string) $profile['profile_code']);
            $gap = abs($this->num($profile['profit_capture_gap_vs_c22_s06_avg'] ?? null) ?? 0.0);
            $r09Gap = abs($this->bucketProfileMetric($nextOpenSummary, 'C25_G02_C23_R09_BASELINE_BRIDGE', 'profit_capture_gap_vs_c22_s06_avg') ?? 0.0);
            $gapReduced = $r09Gap > 0.0 && $gap <= ($r09Gap * 0.75);
            if (((int) ($profile['lookahead_violation_count'] ?? 0)) === 0 && ($avgDelta >= 0.005 || $medianDelta >= 0.003 || $gapReduced)) {
                $nextOpenFix = true;
                break;
            }
        }

        $distribution = $this->bestDistributionBalance($profileSummary);
        $distributionFound = is_array($distribution) && ($distribution['distribution_balance_gate_pass'] ?? false) === true;
        $lookaheadOk = (int) ($lookaheadSafetySummary['lookahead_violation_count'] ?? 0) === 0;
        $notOneParam = (int) ($paramConsistencySummary['candidate_distinct_param_count_max'] ?? 0) > 1;
        $exitPathViable = ($noSignalFix || $nextOpenFix || $distributionFound) && $notOneParam && $lookaheadOk;
        $candidateImprovesCanonical = false;
        foreach ($profileSummary as $profile) {
            if (in_array(($profile['profile_code'] ?? null), ['C25_G00_CANONICAL_BASELINE', 'C25_G01_C22_S06_SHADOW_BENCHMARK'], true)) {
                continue;
            }
            if (((int) ($profile['lookahead_violation_count'] ?? 0)) === 0
                && ((($this->num($profile['median_delta_vs_canonical'] ?? null) ?? 0.0) > 0.0)
                    || (($this->num($profile['p25_delta_vs_canonical'] ?? null) ?? 0.0) > 0.0))) {
                $candidateImprovesCanonical = true;
                break;
            }
        }
        $c26 = $exitPathViable && $candidateImprovesCanonical;
        return [
            'decision_status' => $exitPathViable ? 'C25_GAP_FIX_CANDIDATE_FOUND' : 'C25_GAP_FIX_CANDIDATE_NOT_FOUND',
            'no_signal_fallback_fix_found' => $noSignalFix,
            'next_open_delay_fix_found' => $nextOpenFix,
            'distribution_balance_candidate_found' => $distributionFound,
            'intraday_preplanned_order_candidate_found' => $intradayCandidate && $nextOpenFix,
            'exit_rule_path_still_viable' => $exitPathViable,
            'selection_quality_revisit_needed' => ! $exitPathViable,
            'c26_catalog_candidate_diagnostic_recommended' => $c26,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'next_step' => $c26
                ? 'Run C26 as catalog-candidate diagnostic only, still no OOS and no catalog promotion in C25.'
                : 'Do not create catalog or run OOS; use C25 evidence to decide whether to revisit selection quality/entry quality or narrow exit-rule diagnostics.',
        ];
    }

    private function paramConsistencySummary(array $rows, array $profileSummary): array
    {
        $profileParams = [];
        foreach ($rows as $row) {
            $code = (string) ($row['profile_code'] ?? 'UNKNOWN');
            $param = (string) ($row['param_id'] ?? 'UNKNOWN');
            $profileParams[$code][$param] = true;
        }
        $max = 0;
        $out = [];
        foreach ($profileSummary as $profile) {
            $code = (string) ($profile['profile_code'] ?? 'UNKNOWN');
            $count = count($profileParams[$code] ?? []);
            $max = max($max, $count);
            $out[] = [
                'profile_code' => $code,
                'distinct_param_count' => $count,
                'not_limited_to_one_param' => $count > 1,
            ];
        }
        return [
            'profile_param_consistency' => $out,
            'candidate_distinct_param_count_max' => $max,
            'not_limited_to_one_param' => $max > 1,
        ];
    }

    private function monthStabilitySummary(array $rows): array
    {
        $months = [];
        foreach ($rows as $row) {
            $code = (string) ($row['profile_code'] ?? 'UNKNOWN');
            $month = substr((string) ($row['trade_date'] ?? ''), 0, 7);
            $months[$code][$month !== '' ? $month : 'UNKNOWN'][] = $row;
        }
        $out = [];
        foreach ($months as $code => $byMonth) {
            $monthRows = [];
            foreach ($byMonth as $month => $group) {
                $monthRows[] = [
                    'month' => $month,
                    'count' => count($group),
                    'avg_ret_net' => $this->avg($this->values($group, 'profile_ret_net')),
                    'win_rate' => $this->rate($group, 'win_flag'),
                ];
            }
            $out[] = [
                'profile_code' => $code,
                'month_count' => count($byMonth),
                'months' => $monthRows,
            ];
        }
        return $out;
    }

    private function lookaheadSafetySummary(array $rows): array
    {
        $violations = 0;
        $preplanned = 0;
        $intraday = 0;
        foreach ($rows as $row) {
            if (($row['lookahead_safe'] ?? true) !== true) {
                $violations++;
            }
            if (($row['preplanned_order'] ?? false) === true) {
                $preplanned++;
            }
            if (($row['uses_intraday_high_low'] ?? false) === true) {
                $intraday++;
            }
        }
        return [
            'lookahead_violation_count' => $violations,
            'lookahead_safe' => $violations === 0,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'preplanned_order_count' => $preplanned,
            'uses_intraday_high_low_count' => $intraday,
            'preplanned_order_threshold_fixed_before_path_evaluation' => true,
            'close_signal_same_day_exit_forbidden' => true,
        ];
    }

    private function dataAvailability(array $c23, array $c24, ?array $c21, array $rowsByProfile, array $c21Rows, int $missingCompanionRows): array
    {
        $notes = [];
        if ($c21 === null) {
            $notes[] = 'C21 path artifact not found; intraday target/protection profiles fall back or mark source limitation.';
        }
        $notes[] = 'C23 artifact does not carry raw D1-D5 open/high/low/close fields; C25 keeps OHLC fields null and uses C21 derived MFE/MAE when available.';
        if ($missingCompanionRows > 0) {
            $notes[] = 'Some companion C23 profile rows were missing after filters; affected profile rows use explicit source-unavailable markers.';
        }
        return [
            'c23_all_param_artifact_available' => true,
            'c24_all_param_artifact_available' => true,
            'c21_path_artifact_available' => $c21 !== null,
            'c23_r09_rows_available' => isset($rowsByProfile[self::C23_R09]) && $rowsByProfile[self::C23_R09] !== [],
            'c23_r13_rows_available' => isset($rowsByProfile[self::C23_R13]) && $rowsByProfile[self::C23_R13] !== [],
            'c23_r14_rows_available' => isset($rowsByProfile[self::C23_R14]) && $rowsByProfile[self::C23_R14] !== [],
            'c23_r15_rows_available' => isset($rowsByProfile[self::C23_R15]) && $rowsByProfile[self::C23_R15] !== [],
            'c23_r16_rows_available' => isset($rowsByProfile[self::C23_R16]) && $rowsByProfile[self::C23_R16] !== [],
            'c22_shadow_s06_available_or_recomputable' => true,
            'canonical_baseline_available' => true,
            'd1_to_d5_ohlc_available' => false,
            'd1_to_d5_close_return_available' => $c21 !== null && $c21Rows !== [],
            'derived_mfe_mae_available' => $c21 !== null && $c21Rows !== [],
            'next_open_after_close_signal_available' => true,
            'intraday_high_low_available' => false,
            'intraday_high_low_derived_from_c21_mfe_mae_available' => $c21 !== null && $c21Rows !== [],
            'market_calendar_continuity_available' => true,
            'published_price_availability_available' => true,
            'missing_companion_row_count' => $missingCompanionRows,
            'notes' => $notes,
        ];
    }

    private function c23RowsByProfileAndPick(array $c23, array $paramIds)
    {
        $allowed = $paramIds === [] ? null : array_fill_keys($paramIds, true);
        $rows = [];
        foreach (($c23['pick_rule_rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            if ($allowed !== null && ! isset($allowed[(int) ($row['param_id'] ?? 0)])) {
                continue;
            }
            $profile = (string) ($row['rule_profile_code'] ?? '');
            if ($profile === '') {
                continue;
            }
            $rows[$profile][$this->pickKey($row)] = $row;
        }
        return $rows;
    }

    private function c21RowsByPickKey(?array $c21): array
    {
        if (! is_array($c21)) {
            return [];
        }
        $out = [];
        foreach (($c21['pick_path_rows'] ?? []) as $row) {
            if (! is_array($row) || ($row['diagnostic_profile_code'] ?? null) !== 'C21_P00_CANONICAL_PATH_BASELINE') {
                continue;
            }
            $out[$this->pickKey($row)] = $row;
        }
        return $out;
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

    private function gapComponent(array $row): string
    {
        $signalOffset = $row['rule_signal_day_offset'] ?? null;
        $ruleExitOffset = $row['rule_exit_day_offset'] ?? null;
        $c22ExitOffset = $row['c22_shadow_s06_exit_day_offset'] ?? null;
        $c22 = $this->num($row['c22_shadow_s06_ret_net'] ?? null);
        $rule = $this->num($row['rule_ret_net'] ?? null);
        $gap = $c22 !== null && $rule !== null ? $c22 - $rule : null;
        if ($gap !== null && $gap <= 0.0000001) {
            return 'candidate_matches_or_beats_c22';
        }
        if ($signalOffset === null) {
            return 'no_rule_profit_signal_before_fallback';
        }
        if (is_numeric($signalOffset) && is_numeric($c22ExitOffset) && (int) $signalOffset > (int) $c22ExitOffset) {
            return 'late_rule_signal_after_c22_s06';
        }
        if (is_numeric($signalOffset) && is_numeric($c22ExitOffset) && (int) $signalOffset === (int) $c22ExitOffset
            && is_numeric($ruleExitOffset) && (int) $ruleExitOffset > (int) $c22ExitOffset) {
            return 'next_open_delay_after_close_signal';
        }
        return 'other_gap_rows';
    }

    private function bucketReason(string $bucket): string
    {
        $map = [
            'candidate_matches_or_beats_c22' => 'C23 R09 return matches or beats C22 S06 shadow row.',
            'next_open_delay_after_close_signal' => 'Close signal occurs on the same day as C22 S06, but realistic C23 R09 exits next open.',
            'no_rule_profit_signal_before_fallback' => 'No close-profit rule signal occurs before fallback, so C23 R09 falls back to canonical exit behavior.',
            'late_rule_signal_after_c22_s06' => 'Realistic close signal appears later than the C22 S06 first profitable close shadow.',
            'other_gap_rows' => 'Remaining C22-vs-R09 gap row not covered by primary C24 buckets.',
        ];
        return $map[$bucket] ?? 'Unknown C25 bucket.';
    }

    private function firstTargetHitDay(array $c21, float $targetPct): ?int
    {
        if ($targetPct <= 0) {
            return null;
        }
        for ($day = 1; $day <= 5; $day++) {
            $mfe = $this->num($c21['mfe_'.$day.'d'] ?? null);
            if ($mfe !== null && $mfe >= $targetPct) {
                return $day;
            }
        }
        return null;
    }

    private function firstStopHitDay(array $c21, float $stopPct): ?int
    {
        if ($stopPct <= 0) {
            return null;
        }
        for ($day = 1; $day <= 5; $day++) {
            $mae = $this->num($c21['mae_'.$day.'d'] ?? null);
            if ($mae !== null && $mae <= -1.0 * $stopPct) {
                return $day;
            }
        }
        return null;
    }

    private function c23ProfileSummary(array $c23, string $profileCode): array
    {
        foreach (($c23['rule_profile_summary'] ?? []) as $summary) {
            if (is_array($summary) && ($summary['profile_code'] ?? null) === $profileCode) {
                return $summary;
            }
        }
        return [];
    }

    private function profileDefinitions(array $codes): array
    {
        $profiles = self::diagnosticProfiles();
        $out = [];
        foreach ($codes as $code) {
            $out[] = array_merge(['profile_code' => $code, 'diagnostic_only' => true, 'production_rule' => false], $profiles[$code] ?? []);
        }
        return $out;
    }

    private function safetyBoundaries(): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C25_CATALOG_CODE' => 'NOT_CREATED',
            'C25_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_TICKER_BLACKLIST' => true,
            'NO_MONTH_BLACKLIST' => true,
            'NO_SECTOR_WHITELIST' => true,
            'NO_BEST_OF_FAILED_BINDING' => true,
            'NO_C01_TO_C24_MUTATION' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'NO_C19_REOPEN' => true,
            'NO_C20_REOPEN' => true,
            'NO_C21_REOPEN' => true,
            'NO_C22_REOPEN' => true,
            'NO_C23_REOPEN' => true,
            'NO_C24_REOPEN' => true,
            'catalog_allowed' => false,
            'oos_allowed' => false,
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

    private function preplannedEffectiveness(array $profiles): array
    {
        $out = [];
        foreach ($profiles as $profile) {
            if ((int) ($profile['preplanned_order_count'] ?? 0) > 0) {
                $out[] = [
                    'profile_code' => $profile['profile_code'] ?? null,
                    'avg_delta_vs_c23_r09' => $profile['avg_delta_vs_c23_r09'] ?? null,
                    'profit_capture_gap_vs_c22_s06_avg' => $profile['profit_capture_gap_vs_c22_s06_avg'] ?? null,
                    'preplanned_order_count' => $profile['preplanned_order_count'] ?? 0,
                    'ambiguous_intraday_sequence_count' => $profile['ambiguous_intraday_sequence_count'] ?? 0,
                ];
            }
        }
        return $out;
    }

    private function damageControlEffectiveness(array $profiles): array
    {
        $out = [];
        foreach ($profiles as $profile) {
            if (in_array($profile['profile_family'] ?? null, ['no_signal_fallback', 'combo_no_signal_damage_control', 'combo_downside_control'], true)) {
                $out[] = [
                    'profile_code' => $profile['profile_code'] ?? null,
                    'avg_delta_vs_c23_r09' => $profile['avg_delta_vs_c23_r09'] ?? null,
                    'p25_delta_vs_c23_r09' => $profile['p25_delta_vs_c23_r09'] ?? null,
                    'loss_reduction_rate' => $profile['loss_reduction_rate'] ?? null,
                ];
            }
        }
        return $out;
    }

    private function mfeMaeDistribution(array $rows): array
    {
        return [
            'mfe_avg' => $this->avg($this->values($rows, 'max_favorable_excursion_pct')),
            'mfe_median' => $this->median($this->values($rows, 'max_favorable_excursion_pct')),
            'mae_avg' => $this->avg($this->values($rows, 'max_adverse_excursion_pct')),
            'mae_median' => $this->median($this->values($rows, 'max_adverse_excursion_pct')),
        ];
    }

    private function medianDeltaVsR09(array $bucketSummary, string $profileCode): float
    {
        foreach (($bucketSummary['profile_summary'] ?? []) as $profile) {
            if (($profile['profile_code'] ?? null) === $profileCode) {
                return $this->num($profile['median_delta_vs_c23_r09'] ?? null) ?? 0.0;
            }
        }
        return 0.0;
    }

    private function bucketProfileMetric(array $bucketSummary, string $profileCode, string $metric): ?float
    {
        foreach (($bucketSummary['profile_summary'] ?? []) as $profile) {
            if (($profile['profile_code'] ?? null) === $profileCode) {
                return $this->num($profile[$metric] ?? null);
            }
        }
        return null;
    }

    private function isNoSignalCandidate(array $profile): bool
    {
        return in_array($profile['profile_family'] ?? null, ['no_signal_fallback', 'combo_no_signal_damage_control', 'combo_downside_control', 'combo_intraday_no_signal'], true);
    }

    private function bestByMetric(array $rows, string $metric): array
    {
        $best = [];
        foreach ($rows as $row) {
            if (! is_numeric($row[$metric] ?? null)) {
                continue;
            }
            if ($best === [] || (float) $row[$metric] > (float) $best[$metric]) {
                $best = $row;
            }
        }
        return $best;
    }

    private function bestDistributionBalance(array $profileSummary): array
    {
        $r09 = null;
        foreach ($profileSummary as $profile) {
            if (($profile['profile_code'] ?? null) === 'C25_G02_C23_R09_BASELINE_BRIDGE') {
                $r09 = $profile;
                break;
            }
        }
        if ($r09 === null) {
            return [];
        }
        $best = [];
        foreach ($profileSummary as $profile) {
            if (in_array(($profile['profile_code'] ?? null), ['C25_G00_CANONICAL_BASELINE', 'C25_G01_C22_S06_SHADOW_BENCHMARK', 'C25_G02_C23_R09_BASELINE_BRIDGE'], true)) {
                continue;
            }
            $p25Delta = $this->delta($this->num($profile['p25_ret_net'] ?? null), $this->num($r09['p25_ret_net'] ?? null));
            $avgDelta = $this->delta($this->num($profile['avg_ret_net'] ?? null), $this->num($r09['avg_ret_net'] ?? null));
            $winDelta = $this->delta($this->num($profile['win_rate'] ?? null), $this->num($r09['win_rate'] ?? null));
            $pass = $p25Delta !== null && $avgDelta !== null && $winDelta !== null
                && $p25Delta >= 0.005 && $avgDelta >= -0.003 && $winDelta >= -0.05;
            $candidate = array_merge($profile, [
                'p25_delta_vs_r09_summary' => $p25Delta,
                'avg_delta_vs_r09_summary' => $avgDelta,
                'win_rate_delta_vs_r09_summary' => $winDelta,
                'distribution_balance_gate_pass' => $pass,
            ]);
            if ($pass && ($best === [] || (float) $candidate['p25_delta_vs_r09_summary'] > (float) ($best['p25_delta_vs_r09_summary'] ?? -999))) {
                $best = $candidate;
            }
        }
        return $best;
    }

    private function sortRows(array $rows): array
    {
        usort($rows, function (array $left, array $right): int {
            return strcmp($this->pickKey($left), $this->pickKey($right));
        });
        return $rows;
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
        $json = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write artifact file.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        $normalized = $payload;
        unset($normalized['artifact_hash'], $normalized['generated_at']);
        return sha1(json_encode($this->normalize($normalized), JSON_UNESCAPED_SLASHES));
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        $keys = array_keys($value);
        if ($keys !== range(0, count($value) - 1)) {
            ksort($value);
        }
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }
        return $value;
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

    private function rate(array $rows, string $flag): ?float
    {
        if ($rows === []) {
            return null;
        }
        $count = 0;
        foreach ($rows as $row) {
            if (($row[$flag] ?? false) === true) {
                $count++;
            }
        }
        return $count / count($rows);
    }

    private function num($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function delta(?float $left, ?float $right): ?float
    {
        return $left === null || $right === null ? null : $left - $right;
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_replace_recursive([
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'c25_catalog_implementation_deferred' => 1,
            'c25_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }
}
