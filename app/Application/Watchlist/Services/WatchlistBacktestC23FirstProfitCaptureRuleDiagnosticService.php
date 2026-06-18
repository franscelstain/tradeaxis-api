<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketBenchmarkReadService;
use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService
{
    public const ARTIFACT_TYPE = 'C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-run-1.json';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;

    private MarketDataTradingCalendarReadService $calendar;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;
    private WatchlistBacktestC19SelectionModelRedesignAnalysisService $selectionDiagnostic;
    private MarketDataPublishedEodSeriesReadService $priceSeries;
    private MarketBenchmarkReadService $benchmarkRead;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null,
        WatchlistBacktestC19SelectionModelRedesignAnalysisService $selectionDiagnostic = null,
        MarketDataPublishedEodSeriesReadService $priceSeries = null,
        MarketBenchmarkReadService $benchmarkRead = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->paramGrid = $paramGrid ?: new WatchlistBacktestParamGridRepository();
        $this->paramsetFactory = $paramsetFactory ?: new WatchlistBacktestParamGridParamsetFactory();
        $this->selectionDiagnostic = $selectionDiagnostic ?: new WatchlistBacktestC19SelectionModelRedesignAnalysisService(
            $this->calendar,
            $this->paramGrid,
            $this->paramsetFactory
        );
        $this->priceSeries = $priceSeries ?: new MarketDataPublishedEodSeriesReadService();
        $this->benchmarkRead = $benchmarkRead ?: new MarketBenchmarkReadService();
    }

    public static function ruleProfiles(): array
    {
        return [
            'C23_R00_CANONICAL_BASELINE' => ['family' => 'canonical', 'threshold_pct' => null, 'signal_days' => [], 'description' => 'Canonical ENTRY=NEXT_OPEN, EXIT=STOP_TP_OR_TIME, HOLD=5 baseline.'],
            'C23_R01_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0, 'signal_days' => [1], 'fallback' => 'canonical'],
            'C23_R02_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0_25PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0025, 'signal_days' => [1], 'fallback' => 'canonical'],
            'C23_R03_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0_50PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0050, 'signal_days' => [1], 'fallback' => 'canonical'],
            'C23_R04_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_1_00PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0100, 'signal_days' => [1], 'fallback' => 'canonical'],
            'C23_R05_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0, 'signal_days' => [1, 2], 'fallback' => 'canonical'],
            'C23_R06_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0_25PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0025, 'signal_days' => [1, 2], 'fallback' => 'canonical'],
            'C23_R07_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0_50PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0050, 'signal_days' => [1, 2], 'fallback' => 'canonical'],
            'C23_R08_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_1_00PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0100, 'signal_days' => [1, 2], 'fallback' => 'canonical'],
            'C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0, 'signal_days' => [1, 2, 3], 'fallback' => 'canonical'],
            'C23_R10_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0_25PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0025, 'signal_days' => [1, 2, 3], 'fallback' => 'canonical'],
            'C23_R11_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0_50PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0050, 'signal_days' => [1, 2, 3], 'fallback' => 'canonical'],
            'C23_R12_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_1_00PCT' => ['family' => 'first_profit_capture', 'threshold_pct' => 0.0100, 'signal_days' => [1, 2, 3], 'fallback' => 'canonical'],
            'C23_R13_COMPRESS_HOLD_TO_D3_IF_NO_PROFIT_BY_D2' => ['family' => 'damage_control', 'threshold_pct' => null, 'no_profit_by_day' => 2, 'damage_exit_offset' => 3, 'fallback' => 'canonical'],
            'C23_R14_COMPRESS_HOLD_TO_D4_IF_NO_PROFIT_BY_D3' => ['family' => 'damage_control', 'threshold_pct' => null, 'no_profit_by_day' => 3, 'damage_exit_offset' => 4, 'fallback' => 'canonical'],
            'C23_R15_COMBO_D1_D2_FIRST_PROFIT_CAPTURE_OR_D3_NO_PROFIT_EXIT' => ['family' => 'combo_profit_capture_damage_control', 'threshold_pct' => 0.0, 'signal_days' => [1, 2], 'no_profit_by_day' => 2, 'damage_exit_offset' => 3, 'fallback' => 'canonical'],
            'C23_R16_COMBO_D1_D2_D3_FIRST_PROFIT_CAPTURE_OR_D4_NO_PROFIT_EXIT' => ['family' => 'combo_profit_capture_damage_control', 'threshold_pct' => 0.0, 'signal_days' => [1, 2, 3], 'no_profit_by_day' => 3, 'damage_exit_offset' => 4, 'fallback' => 'canonical'],
            'C23_R17_COMBO_D1_PROFIT_CAPTURE_GT_0_50PCT_OR_D3_DAMAGE_CONTROL' => ['family' => 'combo_profit_capture_damage_control', 'threshold_pct' => 0.0050, 'signal_days' => [1], 'no_profit_by_day' => 2, 'damage_exit_offset' => 3, 'fallback' => 'canonical'],
            'C23_R18_COMBO_D1_D2_PROFIT_CAPTURE_GT_0_50PCT_OR_D3_DAMAGE_CONTROL' => ['family' => 'combo_profit_capture_damage_control', 'threshold_pct' => 0.0050, 'signal_days' => [1, 2], 'no_profit_by_day' => 2, 'damage_exit_offset' => 3, 'fallback' => 'canonical'],
        ];
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode) !== '' ? trim($catalogCode) : self::DEFAULT_SOURCE_CATALOG_CODE;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C23_IS_ONLY_WINDOW_MISMATCH', 'C23 first-profit-capture rule diagnostic requires the frozen IS window only.');
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.');
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C23_PARAM_IDS_INVALID', 'param_ids must be a comma-separated list of positive integers.');
        }
        $ruleProfiles = $this->ruleProfileCodes($options['rule_profiles'] ?? ($options['profile_codes'] ?? ($options['profiles'] ?? null)));
        if ($ruleProfiles === false) {
            return $this->blocked('WS_BT_C23_RULE_PROFILE_INVALID', 'rule-profile-codes/profile-codes must be a comma-separated list of known C23 rule profile codes.');
        }
        $profileScope = 'EXPLICIT';
        if ($ruleProfiles === []) {
            $ruleProfiles = [
                'C23_R00_CANONICAL_BASELINE',
                'C23_R01_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0',
                'C23_R03_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0_50PCT',
                'C23_R05_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0',
                'C23_R07_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0_50PCT',
                'C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0',
                'C23_R13_COMPRESS_HOLD_TO_D3_IF_NO_PROFIT_BY_D2',
                'C23_R15_COMBO_D1_D2_FIRST_PROFIT_CAPTURE_OR_D3_NO_PROFIT_EXIT',
            ];
            $profileScope = 'FAST_DEFAULT';
        }
        $maxRuleProfiles = $this->positiveIntOrNull($options['max_rule_profiles'] ?? ($options['max_profiles'] ?? null));
        if ($maxRuleProfiles !== null) {
            $ruleProfiles = array_slice($ruleProfiles, 0, $maxRuleProfiles);
            $profileScope .= '_MAX_'.$maxRuleProfiles;
        }
        if ($ruleProfiles === []) {
            return $this->blocked('WS_BT_C23_RULE_PROFILE_EMPTY', 'No C23 rule profile is selected.');
        }

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar is unavailable for C23 diagnostic.', ['calendar' => $calendar]);
        }
        $tradeDates = $this->normalizeDateList($calendar['trade_dates'] ?? []);
        $calendarDates = $this->normalizeDateList($calendar['calendar_dates'] ?? $calendar['trade_dates'] ?? []);
        if ($tradeDates === [] || $calendarDates === []) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar returned no usable dates.');
        }

        try {
            $rows = $this->paramGrid->allForCatalog($catalogCode, 'WS');
        } catch (\Throwable $e) {
            return $this->blocked('WS_BT_C23_SOURCE_CATALOG_UNAVAILABLE', $e->getMessage());
        }
        if ($paramIds !== []) {
            $allowed = array_fill_keys($paramIds, true);
            $rows = array_values(array_filter($rows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_C23_ROW_FILTER_NO_MATCH', 'No source catalog rows matched the explicit param filter.');
            }
        }
        usort($rows, function (array $left, array $right): int {
            $cmp = strcmp((string) ($left['row_code'] ?? ''), (string) ($right['row_code'] ?? ''));
            return $cmp !== 0 ? $cmp : (((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0)));
        });
        $maxParams = $this->positiveIntOrNull($options['max_params'] ?? null);
        if ($maxParams !== null) {
            $rows = array_slice(array_values($rows), 0, $maxParams);
        }
        $rowsByParamId = [];
        foreach ($rows as $row) {
            $rowsByParamId[(int) ($row['param_id'] ?? 0)] = $row;
        }

        $selectionOutput = (string) ($options['selection_output_path'] ?? ($outputPath.'.c19-selection-analysis.json'));
        $selectionResult = null;
        $reuseSelectionArtifact = ! empty($options['reuse_selection_artifact']);
        if ($reuseSelectionArtifact) {
            if (! is_file($selectionOutput)) {
                return $this->blocked('WS_BT_C23_SELECTION_ARTIFACT_REUSE_MISSING', 'Requested C19 selection artifact reuse, but the artifact path does not exist.');
            }
            $selectionArtifact = $this->readJson($selectionOutput);
            if ($selectionArtifact === null) {
                return $this->blocked('WS_BT_C23_SELECTION_ARTIFACT_UNREADABLE', 'Requested C19 selection artifact reuse, but the artifact could not be read.');
            }
            $selectionResult = [
                'status' => 'PASS',
                'reason_code' => 'WS_BT_C23_SELECTION_ARTIFACT_REUSED',
                'artifact_path' => $selectionOutput,
                'artifact_hash' => $selectionArtifact['artifact_hash'] ?? null,
            ];
        } else {
            $selectionResult = $this->selectionDiagnostic->execute($catalogCode, $fromDate, $toDate, $selectionOutput, [
                'param_ids' => $paramIds,
                'overwrite' => true,
                'executed_at' => $options['executed_at'] ?? ($toDate.'T23:59:59+07:00'),
            ]);
            if (($selectionResult['status'] ?? null) !== 'PASS') {
                return $this->blocked($selectionResult['reason_code'] ?? 'WS_BT_C23_SELECTION_SOURCE_NOT_READY', 'C19 selection diagnostic source did not produce a PASS artifact.', [
                    'selection_result' => $selectionResult,
                ]);
            }
            $selectionArtifact = $this->readJson($selectionOutput);
            if ($selectionArtifact === null) {
                return $this->blocked('WS_BT_C23_SELECTION_ARTIFACT_UNREADABLE', 'C19 selection artifact could not be read.');
            }
        }

        $executedAt = (string) ($options['executed_at'] ?? ($toDate.'T23:59:59+07:00'));
        $progress = $options['progress_callback'] ?? null;
        $maxPicks = $this->positiveIntOrNull($options['max_picks'] ?? null);
        $canonicalRows = [];
        $ruleRows = [];
        $paramSummaries = [];
        $paramIndex = 0;

        foreach (($selectionArtifact['diagnostics'] ?? []) as $selectionDiag) {
            if (! is_array($selectionDiag)) {
                continue;
            }
            $paramId = (int) ($selectionDiag['param_id'] ?? 0);
            if (! isset($rowsByParamId[$paramId])) {
                continue;
            }
            $row = $rowsByParamId[$paramId];
            $paramset = $this->paramsetFactory->make($row);
            $paramIndex++;
            if (is_callable($progress)) {
                $progress('[C23] param '.$paramIndex.'/'.count($rowsByParamId).': '.$paramId.' profiles='.count($ruleProfiles));
            }
            $paramResult = $this->diagnoseParam(
                $selectionDiag,
                $row,
                $paramset,
                $tradeDates,
                $calendarDates,
                $fromDate,
                $toDate,
                $ruleProfiles,
                $maxPicks
            );
            foreach ($paramResult['canonical_rows'] as $canonicalRow) {
                $canonicalRows[] = $canonicalRow;
            }
            foreach ($paramResult['pick_rule_rows'] as $ruleRow) {
                $ruleRows[] = $ruleRow;
            }
            $paramSummaries[] = $paramResult['summary'];
        }

        $canonicalSummary = $this->canonicalSummary($canonicalRows);
        $ruleProfileSummary = $this->ruleProfileSummary($ruleRows, $canonicalSummary);
        $c22ShadowS06Summary = $this->c22ShadowS06Summary($ruleRows);
        $firstProfitCaptureSummary = $this->familySummary($ruleProfileSummary, 'first_profit_capture');
        $damageControlSummary = $this->familySummary($ruleProfileSummary, 'damage_control');
        $comboRuleSummary = $this->familySummary($ruleProfileSummary, 'combo_profit_capture_damage_control');
        $paramConsistencySummary = $this->paramConsistencySummary($ruleRows, $ruleProfileSummary);
        $profileConsistencySummary = $this->profileConsistencySummary($ruleRows, $ruleProfileSummary);
        $monthStabilitySummary = $this->monthStabilitySummary($ruleRows, $ruleProfileSummary);
        $lookaheadSafetySummary = $this->lookaheadSafetySummary($ruleRows);
        $dataAvailability = $this->dataAvailability($selectionArtifact, $canonicalRows, $calendarDates);
        $decision = $this->decision(
            $canonicalSummary,
            $ruleProfileSummary,
            $c22ShadowS06Summary,
            $firstProfitCaptureSummary,
            $damageControlSummary,
            $comboRuleSummary,
            $paramConsistencySummary,
            $monthStabilitySummary,
            $lookaheadSafetySummary
        );
        $status = $decision['decision_status'] === 'C23_DIAGNOSTIC_BLOCKED' ? 'BLOCKED' : 'PASS';

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => $status,
            'reason_code' => 'WS_BT_C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_FIRST_PROFIT_CAPTURE_RULE_CANDIDATE_DIAGNOSTIC',
            'generated_at' => $executedAt,
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'policy_code' => 'WS',
                'row_count' => count($rows),
                'max_params' => $maxParams,
                'max_picks' => $maxPicks,
                'source_selection' => 'C19_FIXED_PROPOSED_SELECTION_ITEMS',
                'source_catalog_anchor' => 'C17_IMMUTABLE_CATALOG',
            ],
            'source_evidence' => [
                'c19_final_status' => 'C19_CATALOG_CANDIDATE_FAILED',
                'c20_final_status' => 'C20_DATE_GATE_NOT_ENOUGH',
                'c21_final_status' => 'C21_EXECUTION_SIGNAL_FOUND',
                'c22_final_status' => 'C22_EXIT_CAPTURE_SIGNAL_FOUND',
                'c22_all_param_artifact_hash' => '4e939d091a03ed49bbf460c0424ff1a018f98e72',
                'selection_source_artifact_path' => $selectionOutput,
                'selection_source_artifact_hash' => $selectionArtifact['artifact_hash'] ?? ($selectionResult['artifact_hash'] ?? null),
                'selection_source_artifact_reused' => $reuseSelectionArtifact,
            ],
            'is_window' => [
                'from' => $fromDate,
                'to' => $toDate,
                'trade_date_count' => count($tradeDates),
                'boundary_censoring_rule' => 'EXCLUDE_LAST_HOLDING_DAYS_FROM_ENTRY_GENERATION;KEEP_ALL_PATH_PRICE_READS_WITHIN_IS;RULE_CANDIDATE_MEASUREMENT_ONLY_AFTER_FIXED_PICK',
            ],
            'price_evaluation_model' => $this->canonicalEvaluationModel(),
            'data_availability' => $dataAvailability,
            'rule_profiles' => $this->selectedRuleProfileDefinitions($ruleProfiles),
            'rule_profile_count' => count($ruleProfiles),
            'profile_scope' => $profileScope,
            'param_summaries' => $paramSummaries,
            'canonical_rows' => $canonicalRows,
            'pick_rule_rows' => $ruleRows,
            'canonical_summary' => $canonicalSummary,
            'c22_shadow_s06_summary' => $c22ShadowS06Summary,
            'rule_profile_summary' => $ruleProfileSummary,
            'per_rule_profile_summary' => $ruleProfileSummary,
            'first_profit_capture_summary' => $firstProfitCaptureSummary,
            'damage_control_summary' => $damageControlSummary,
            'combo_rule_summary' => $comboRuleSummary,
            'param_consistency_summary' => $paramConsistencySummary,
            'profile_consistency_summary' => $profileConsistencySummary,
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

        $bestMedian = $this->bestByMetric($ruleProfileSummary, 'median_delta_vs_canonical');
        $bestP25 = $this->bestByMetric($ruleProfileSummary, 'p25_delta_vs_canonical');
        $bestGiveback = $this->bestByMetric($ruleProfileSummary, 'gave_back_profit_reduction_vs_canonical');

        return [
            'status' => $status,
            'reason_code' => 'WS_BT_C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_FIRST_PROFIT_CAPTURE_RULE_CANDIDATE_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'rule_profile_count' => count($ruleProfiles),
            'profile_scope' => $profileScope,
            'evaluated_picks_count' => (int) ($canonicalSummary['evaluated_picks_count'] ?? 0),
            'path_missing_count' => (int) ($canonicalSummary['path_missing_count'] ?? 0),
            'canonical_avg_ret_net' => $canonicalSummary['canonical_avg_ret_net'] ?? null,
            'canonical_median_ret_net' => $canonicalSummary['canonical_median_ret_net'] ?? null,
            'canonical_p25_ret_net' => $canonicalSummary['canonical_p25_ret_net'] ?? null,
            'canonical_win_rate' => $canonicalSummary['canonical_win_rate'] ?? null,
            'canonical_gave_back_profit_rate' => $canonicalSummary['canonical_gave_back_profit_rate'] ?? null,
            'c22_shadow_s06_avg_ret_net' => $c22ShadowS06Summary['c22_shadow_s06_avg_ret_net'] ?? null,
            'c22_shadow_s06_median_ret_net' => $c22ShadowS06Summary['c22_shadow_s06_median_ret_net'] ?? null,
            'c22_shadow_s06_p25_ret_net' => $c22ShadowS06Summary['c22_shadow_s06_p25_ret_net'] ?? null,
            'c22_shadow_s06_win_rate' => $c22ShadowS06Summary['c22_shadow_s06_win_rate'] ?? null,
            'best_rule_profile_code_by_avg' => ($this->bestByMetric($ruleProfileSummary, 'avg_delta_vs_canonical')['profile_code'] ?? null),
            'best_rule_profile_code_by_median' => $bestMedian['profile_code'] ?? null,
            'best_rule_profile_code_by_p25' => $bestP25['profile_code'] ?? null,
            'best_rule_profile_code_by_win_rate' => ($this->bestByMetric($ruleProfileSummary, 'win_rate')['profile_code'] ?? null),
            'best_rule_profile_code_by_giveback_reduction' => $bestGiveback['profile_code'] ?? null,
            'best_rule_profile_code_by_closest_to_c22_s06' => ($this->bestByClosestToC22($ruleProfileSummary)['profile_code'] ?? null),
            'best_rule_median_delta_vs_canonical' => $bestMedian['median_delta_vs_canonical'] ?? null,
            'best_rule_p25_delta_vs_canonical' => $bestP25['p25_delta_vs_canonical'] ?? null,
            'best_rule_giveback_reduction_vs_canonical' => $bestGiveback['gave_back_profit_reduction_vs_canonical'] ?? null,
            'best_rule_profit_capture_gap_vs_c22_s06' => ($this->bestByClosestToC22($ruleProfileSummary)['profit_capture_gap_vs_c22_s06_median'] ?? null),
            'first_profit_capture_rule_signal_found' => $decision['first_profit_capture_rule_signal_found'] ? 1 : 0,
            'c22_shadow_gap_acceptable' => $decision['c22_shadow_gap_acceptable'] ? 1 : 0,
            'non_lookahead_rule_candidate_found' => $decision['non_lookahead_rule_candidate_found'] ? 1 : 0,
            'damage_control_candidate_found' => $decision['damage_control_candidate_found'] ? 1 : 0,
            'combo_rule_candidate_found' => $decision['combo_rule_candidate_found'] ? 1 : 0,
            'param_consistency_found' => $decision['param_consistency_found'] ? 1 : 0,
            'month_stability_sufficient' => $decision['month_stability_sufficient'] ? 1 : 0,
            'c23_catalog_implementation_deferred' => 1,
            'c23_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function diagnoseParam(
        array $selectionDiagnostic,
        array $row,
        array $paramset,
        array $tradeDates,
        array $calendarDates,
        string $fromDate,
        string $toDate,
        array $ruleProfiles,
        ?int $maxPicks
    ): array {
        $baseItems = array_values(array_filter($selectionDiagnostic['proposed_path']['selected_items'] ?? [], 'is_array'));
        $holdingDays = max(1, (int) ($paramset['backtest']['holding_days'] ?? 5));
        $holdingDays = min(5, $holdingDays);
        $strategyTradeDates = count($tradeDates) > $holdingDays ? array_slice($tradeDates, 0, count($tradeDates) - $holdingDays) : [];
        $items = array_values(array_filter($baseItems, function (array $item) use ($strategyTradeDates): bool {
            return in_array((string) ($item['trade_date'] ?? ''), $strategyTradeDates, true);
        }));
        $items = $this->sortItems($items);
        if ($maxPicks !== null) {
            $items = array_slice($items, 0, $maxPicks);
        }

        $trades = $this->proposalItemsToTrades($items, $paramset);
        $requiredMap = $this->requiredPriceTickerMap($trades, $calendarDates, $holdingDays, true);
        $requiredDates = array_keys($requiredMap);
        $priceFromDate = $requiredDates[0] ?? $fromDate;
        $priceToDate = $requiredDates === [] ? $toDate : $requiredDates[count($requiredDates) - 1];
        $priceRead = $requiredMap === []
            ? $this->emptyPriceRead($priceFromDate, $priceToDate)
            : $this->priceSeries->readPublishedSeriesForDateTickerMap($priceFromDate, $priceToDate, $requiredMap);
        $series = is_array($priceRead['series_by_ticker'] ?? null) ? $priceRead['series_by_ticker'] : [];

        $canonicalRows = [];
        $ruleRows = [];
        foreach ($trades as $trade) {
            $path = $this->canonicalPath($trade, $row, $paramset, $series, $calendarDates, $holdingDays);
            $canonicalRows[] = $path['canonical_row'];
            foreach ($ruleProfiles as $profileCode) {
                $ruleRows[] = $this->ruleRow($path['canonical_row'], $path['path_bars'], $paramset, $profileCode);
            }
        }

        return [
            'summary' => array_merge($this->canonicalSummary($canonicalRows), [
                'param_id' => (int) ($row['param_id'] ?? 0),
                'row_code' => (string) ($row['row_code'] ?? ''),
                'baseline_proposed_recommended_count' => count($baseItems),
                'diagnostic_selected_count' => count($items),
                'required_price_date_count' => count($requiredDates),
                'requested_ticker_date_pair_count' => array_sum(array_map('count', $requiredMap)),
                'price_readiness' => [
                    'is_ready' => (bool) ($priceRead['is_ready'] ?? false),
                    'reason_code' => $priceRead['reason_code'] ?? null,
                    'missing_price_dates' => $priceRead['price_series_manifest']['missing_price_dates'] ?? [],
                    'missing_price_rows_count' => count($priceRead['price_series_manifest']['missing_price_rows'] ?? []),
                ],
                'future_path_price_used_for_selection' => false,
                'rule_ret_net_used_for_selection' => false,
                'mfe_mae_used_for_selection' => false,
            ]),
            'canonical_rows' => $canonicalRows,
            'pick_rule_rows' => $ruleRows,
        ];
    }

    private function canonicalPath(array $trade, array $row, array $paramset, array $series, array $calendarDates, int $holdingDays): array
    {
        $ticker = (string) ($trade['ticker'] ?? '');
        $tradeDate = (string) ($trade['trade_date'] ?? '');
        $entryDate = $this->nextTradingDate($tradeDate, $calendarDates);
        $signalBar = $this->publishedBar($series, $ticker, $tradeDate);
        $entryBar = $entryDate !== null ? $this->publishedBar($series, $ticker, $entryDate) : null;
        $pathDates = $entryDate !== null ? $this->tradingWindowFrom($entryDate, $calendarDates, $holdingDays) : [];
        $base = [
            'trade_date' => $tradeDate,
            'ticker_id' => $trade['ticker_id'] ?? null,
            'ticker' => $ticker,
            'param_id' => (int) ($row['param_id'] ?? 0),
            'row_code' => (string) ($row['row_code'] ?? ''),
            'entry_date' => $entryDate,
            'entry_price' => null,
            'signal_close_price' => null,
            'canonical_exit_date' => null,
            'canonical_exit_price' => null,
            'canonical_exit_reason' => null,
            'canonical_exit_day_offset' => null,
            'canonical_ret_net' => null,
            'd1_close_ret' => null,
            'd2_close_ret' => null,
            'd3_close_ret' => null,
            'd4_close_ret' => null,
            'd5_close_ret' => null,
            'max_favorable_excursion_pct' => null,
            'max_adverse_excursion_pct' => null,
            'first_profitable_day' => null,
            'first_profitable_close_day' => null,
            'gave_back_profit_flag' => false,
            'never_profitable_flag' => false,
            'missing_path_data_flag' => true,
            'missing_path_reason_code' => null,
            'stop_price' => null,
            'target_price' => null,
            'stop_trigger_price' => null,
            'target_trigger_price' => null,
            'contract_flags' => [
                'recommendation_frozen_before_price_read' => true,
                'future_path_price_used_for_measurement_only' => true,
                'future_path_price_used_for_selection' => false,
                'rule_exit_used_for_selection' => false,
                'C23_diagnostic_only' => true,
            ],
        ];

        if ($ticker === '' || $tradeDate === '' || $entryDate === null || $signalBar === null || $entryBar === null || count($pathDates) < $holdingDays) {
            $base['missing_path_reason_code'] = 'WS_BT_C23_REQUIRED_ENTRY_OR_SIGNAL_PATH_MISSING';
            return ['canonical_row' => $base, 'path_bars' => []];
        }
        $signalClose = $this->num($signalBar['close'] ?? null);
        $entryPrice = $this->num($entryBar['open'] ?? null);
        if ($signalClose === null || $signalClose <= 0 || $entryPrice === null || $entryPrice <= 0) {
            $base['missing_path_reason_code'] = 'WS_BT_C23_REQUIRED_ENTRY_OR_SIGNAL_OHLC_MISSING';
            return ['canonical_row' => $base, 'path_bars' => []];
        }

        $pathBars = [];
        foreach ($pathDates as $index => $date) {
            $bar = $this->publishedBar($series, $ticker, $date);
            if ($bar === null || ! $this->tradableBar($bar, $paramset)) {
                $base['missing_path_reason_code'] = 'WS_BT_C23_D1_TO_D5_OHLC_PATH_MISSING';
                return ['canonical_row' => $base, 'path_bars' => []];
            }
            foreach (['open', 'high', 'low', 'close'] as $field) {
                $value = $this->num($bar[$field] ?? null);
                if ($value === null || $value <= 0) {
                    $base['missing_path_reason_code'] = 'WS_BT_C23_D1_TO_D5_OHLC_PATH_MISSING';
                    return ['canonical_row' => $base, 'path_bars' => []];
                }
            }
            $pathBars[$index + 1] = [
                'date' => $date,
                'open' => (float) $bar['open'],
                'high' => (float) $bar['high'],
                'low' => (float) $bar['low'],
                'close' => (float) $bar['close'],
            ];
        }

        $levels = $this->targetStopLevels($entryPrice, $trade, $paramset);
        $firstProfitable = null;
        $maxHigh = null;
        $minLow = null;
        $exitDate = null;
        $exitPrice = null;
        $exitReason = null;
        $exitOffset = null;
        $dailyCloseReturns = [];
        $mfe = [];
        $mae = [];

        foreach ($pathBars as $offset => $bar) {
            $maxHigh = $maxHigh === null ? $bar['high'] : max($maxHigh, $bar['high']);
            $minLow = $minLow === null ? $bar['low'] : min($minLow, $bar['low']);
            $dailyCloseReturns[$offset] = ($bar['close'] - $entryPrice) / $entryPrice;
            $mfe[$offset] = ($maxHigh - $entryPrice) / $entryPrice;
            $mae[$offset] = ($minLow - $entryPrice) / $entryPrice;
            if ($firstProfitable === null && $bar['close'] > $entryPrice) {
                $firstProfitable = $offset;
            }
            if (($levels['has_target_stop'] ?? false) === true && $exitReason === null) {
                $stop = (float) $levels['stop_trigger_price'];
                $target = (float) $levels['target_trigger_price'];
                if ($bar['open'] <= $stop) {
                    $exitPrice = $bar['open'];
                    $exitReason = 'exit_stop';
                    $exitDate = $bar['date'];
                    $exitOffset = $offset;
                } elseif ($bar['open'] >= $target) {
                    $exitPrice = $bar['open'];
                    $exitReason = 'exit_target';
                    $exitDate = $bar['date'];
                    $exitOffset = $offset;
                } elseif ($bar['low'] <= $stop) {
                    $exitPrice = $stop;
                    $exitReason = 'exit_stop';
                    $exitDate = $bar['date'];
                    $exitOffset = $offset;
                } elseif ($bar['high'] >= $target) {
                    $exitPrice = $target;
                    $exitReason = 'exit_target';
                    $exitDate = $bar['date'];
                    $exitOffset = $offset;
                }
            }
        }

        if ($exitReason === null) {
            $last = $pathBars[count($pathBars)];
            $exitDate = $last['date'];
            $exitPrice = $last['close'];
            $exitReason = 'exit_hold';
            $exitOffset = count($pathBars);
        }

        $retNet = $this->retNet($entryPrice, (float) $exitPrice, $paramset);
        $maxMfe = $mfe === [] ? null : max($mfe);
        $maxMae = $mae === [] ? null : min($mae);
        $gaveBack = $maxMfe !== null && $retNet !== null && $maxMfe > 0.005 && ($retNet <= 0 || ($maxMfe - $retNet) >= 0.010);
        $neverProfitable = $maxMfe !== null && $maxMfe <= 0;

        return ['canonical_row' => array_merge($base, [
            'entry_price' => $entryPrice,
            'signal_close_price' => $signalClose,
            'canonical_exit_date' => $exitDate,
            'canonical_exit_price' => $exitPrice,
            'canonical_exit_reason' => $exitReason,
            'canonical_exit_day_offset' => $exitOffset,
            'canonical_ret_net' => $retNet,
            'd1_close_ret' => $dailyCloseReturns[1] ?? null,
            'd2_close_ret' => $dailyCloseReturns[2] ?? null,
            'd3_close_ret' => $dailyCloseReturns[3] ?? null,
            'd4_close_ret' => $dailyCloseReturns[4] ?? null,
            'd5_close_ret' => $dailyCloseReturns[5] ?? null,
            'max_favorable_excursion_pct' => $maxMfe,
            'max_adverse_excursion_pct' => $maxMae,
            'first_profitable_day' => $firstProfitable,
            'first_profitable_close_day' => $firstProfitable,
            'gave_back_profit_flag' => $gaveBack,
            'never_profitable_flag' => $neverProfitable,
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
            'stop_price' => $levels['stop_price'] ?? null,
            'target_price' => $levels['target_price'] ?? null,
            'stop_trigger_price' => $levels['stop_trigger_price'] ?? null,
            'target_trigger_price' => $levels['target_trigger_price'] ?? null,
        ]), 'path_bars' => $pathBars];
    }

    private function ruleRow(array $canonical, array $pathBars, array $paramset, string $profileCode): array
    {
        $entryPrice = $this->num($canonical['entry_price'] ?? null);
        $canonicalRet = $this->num($canonical['canonical_ret_net'] ?? null);
        $profile = self::ruleProfiles()[$profileCode] ?? ['family' => 'unknown', 'threshold_pct' => null];
        $base = [
            'trade_date' => $canonical['trade_date'] ?? null,
            'ticker_id' => $canonical['ticker_id'] ?? null,
            'ticker' => $canonical['ticker'] ?? null,
            'param_id' => $canonical['param_id'] ?? null,
            'row_code' => $canonical['row_code'] ?? null,
            'entry_date' => $canonical['entry_date'] ?? null,
            'entry_price' => $entryPrice,
            'signal_close_price' => $canonical['signal_close_price'] ?? null,
            'canonical_exit_date' => $canonical['canonical_exit_date'] ?? null,
            'canonical_exit_price' => $canonical['canonical_exit_price'] ?? null,
            'canonical_exit_reason' => $canonical['canonical_exit_reason'] ?? null,
            'canonical_exit_day_offset' => $canonical['canonical_exit_day_offset'] ?? null,
            'canonical_ret_net' => $canonicalRet,
            'max_favorable_excursion_pct' => $canonical['max_favorable_excursion_pct'] ?? null,
            'max_adverse_excursion_pct' => $canonical['max_adverse_excursion_pct'] ?? null,
            'first_profitable_close_day' => $canonical['first_profitable_close_day'] ?? ($canonical['first_profitable_day'] ?? null),
            'gave_back_profit_flag' => (bool) ($canonical['gave_back_profit_flag'] ?? false),
            'never_profitable_flag' => (bool) ($canonical['never_profitable_flag'] ?? false),
            'c22_shadow_s06_exit_date' => null,
            'c22_shadow_s06_exit_price' => null,
            'c22_shadow_s06_exit_day_offset' => null,
            'c22_shadow_s06_ret_net' => null,
            'c22_shadow_s06_delta_vs_canonical' => null,
            'c22_shadow_s06_win_flag' => false,
            'rule_profile_code' => $profileCode,
            'rule_family' => $profile['family'] ?? 'unknown',
            'rule_threshold_pct' => $profile['threshold_pct'] ?? null,
            'rule_signal_date' => null,
            'rule_signal_day_offset' => null,
            'rule_signal_type' => null,
            'rule_signal_close_price' => null,
            'rule_signal_return_pct' => null,
            'rule_exit_date' => null,
            'rule_exit_price' => null,
            'rule_exit_day_offset' => null,
            'rule_exit_reason' => null,
            'rule_ret_net' => null,
            'rule_ret_delta_vs_canonical' => null,
            'rule_ret_delta_vs_c22_shadow_s06' => null,
            'profit_capture_gap_vs_c22_s06' => null,
            'rule_win_flag' => false,
            'canonical_win_flag' => $canonicalRet !== null && $canonicalRet > 0,
            'c22_shadow_s06_win_flag' => false,
            'gave_back_profit_reduced_flag' => false,
            'loss_reduced_flag' => false,
            'profit_captured_flag' => false,
            'lookahead_safe' => false,
            'lookahead_violation_reason' => null,
            'missing_path_data_flag' => (bool) ($canonical['missing_path_data_flag'] ?? true),
            'missing_path_reason_code' => $canonical['missing_path_reason_code'] ?? null,
            'future_path_price_used_for_selection' => false,
            'rule_ret_net_used_for_selection' => false,
        ];
        if ($entryPrice === null || $entryPrice <= 0 || $pathBars === [] || ($canonical['missing_path_data_flag'] ?? true)) {
            return $base;
        }

        $c22Exit = $this->c22ShadowS06Exit($entryPrice, $pathBars);
        $c22Ret = $this->retNet($entryPrice, (float) $c22Exit['price'], $paramset);
        $c22Delta = $c22Ret !== null && $canonicalRet !== null ? $c22Ret - $canonicalRet : null;

        $exit = $this->ruleExit($profileCode, $entryPrice, $pathBars, $canonical);
        if (($exit['missing_path_data_flag'] ?? false) === true) {
            return array_merge($base, [
                'c22_shadow_s06_exit_date' => $c22Exit['date'],
                'c22_shadow_s06_exit_price' => $c22Exit['price'],
                'c22_shadow_s06_exit_day_offset' => $c22Exit['offset'],
                'c22_shadow_s06_ret_net' => $c22Ret,
                'c22_shadow_s06_delta_vs_canonical' => $c22Delta,
                'c22_shadow_s06_win_flag' => $c22Ret !== null && $c22Ret > 0,
                'missing_path_data_flag' => true,
                'missing_path_reason_code' => $exit['missing_path_reason_code'] ?? 'WS_BT_C23_RULE_EXIT_PATH_MISSING',
            ]);
        }

        $retNet = $this->retNet($entryPrice, (float) $exit['price'], $paramset);
        $delta = $retNet !== null && $canonicalRet !== null ? $retNet - $canonicalRet : null;
        $ruleGaveBack = $this->exitGaveBackFlag($canonical, $retNet);
        $deltaVsC22 = $retNet !== null && $c22Ret !== null ? $retNet - $c22Ret : null;
        $gapVsC22 = $retNet !== null && $c22Ret !== null ? $c22Ret - $retNet : null;

        return array_merge($base, [
            'c22_shadow_s06_exit_date' => $c22Exit['date'],
            'c22_shadow_s06_exit_price' => $c22Exit['price'],
            'c22_shadow_s06_exit_day_offset' => $c22Exit['offset'],
            'c22_shadow_s06_ret_net' => $c22Ret,
            'c22_shadow_s06_delta_vs_canonical' => $c22Delta,
            'c22_shadow_s06_win_flag' => $c22Ret !== null && $c22Ret > 0,
            'rule_signal_date' => $exit['signal_date'],
            'rule_signal_day_offset' => $exit['signal_offset'],
            'rule_signal_type' => $exit['signal_type'],
            'rule_signal_close_price' => $exit['signal_close_price'],
            'rule_signal_return_pct' => $exit['signal_return_pct'],
            'rule_exit_date' => $exit['date'],
            'rule_exit_price' => $exit['price'],
            'rule_exit_day_offset' => $exit['offset'],
            'rule_exit_reason' => $exit['reason'],
            'rule_ret_net' => $retNet,
            'rule_ret_delta_vs_canonical' => $delta,
            'rule_ret_delta_vs_c22_shadow_s06' => $deltaVsC22,
            'profit_capture_gap_vs_c22_s06' => $gapVsC22,
            'rule_win_flag' => $retNet !== null && $retNet > 0,
            'gave_back_profit_reduced_flag' => ((bool) ($canonical['gave_back_profit_flag'] ?? false)) && ! $ruleGaveBack,
            'loss_reduced_flag' => $canonicalRet !== null && $canonicalRet < 0 && $retNet !== null && $retNet > $canonicalRet,
            'profit_captured_flag' => $retNet !== null && $retNet > 0 && (($canonical['max_favorable_excursion_pct'] ?? 0) > 0),
            'lookahead_safe' => (bool) ($exit['lookahead_safe'] ?? false),
            'lookahead_violation_reason' => $exit['lookahead_violation_reason'] ?? null,
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
        ]);
    }

    private function ruleExit(string $profileCode, float $entryPrice, array $pathBars, array $canonical): array
    {
        $profile = self::ruleProfiles()[$profileCode] ?? ['family' => 'canonical'];
        $family = (string) ($profile['family'] ?? 'canonical');
        if ($family === 'canonical') {
            return $this->exitPayload(
                null,
                null,
                null,
                null,
                null,
                (string) $canonical['canonical_exit_date'],
                (float) $canonical['canonical_exit_price'],
                (int) $canonical['canonical_exit_day_offset'],
                'rule_canonical_baseline'
            );
        }

        if ($family === 'first_profit_capture' || $family === 'combo_profit_capture_damage_control') {
            $capture = $this->firstProfitCaptureExit($entryPrice, $pathBars, $profile);
            if ($capture !== null) {
                return $capture;
            }
        }

        if ($family === 'damage_control' || $family === 'combo_profit_capture_damage_control') {
            $damage = $this->damageControlExit($entryPrice, $pathBars, $profile);
            if ($damage !== null) {
                return $damage;
            }
        }

        return $this->exitPayload(
            null,
            null,
            null,
            null,
            null,
            (string) $canonical['canonical_exit_date'],
            (float) $canonical['canonical_exit_price'],
            (int) $canonical['canonical_exit_day_offset'],
            'rule_fallback_canonical'
        );
    }

    private function firstProfitCaptureExit(float $entryPrice, array $pathBars, array $profile): ?array
    {
        $threshold = $this->num($profile['threshold_pct'] ?? null);
        $threshold = $threshold === null ? 0.0 : $threshold;
        $signalDays = is_array($profile['signal_days'] ?? null) ? $profile['signal_days'] : [];

        foreach ($signalDays as $rawDay) {
            $day = (int) $rawDay;
            if ($day < 1 || $day > 3 || ! isset($pathBars[$day])) {
                continue;
            }

            $bar = $pathBars[$day];
            $signalClose = $this->num($bar['close'] ?? null);
            if ($signalClose === null || $signalClose <= 0) {
                return [
                    'missing_path_data_flag' => true,
                    'missing_path_reason_code' => 'WS_BT_C23_RULE_SIGNAL_CLOSE_MISSING',
                ];
            }

            $signalReturn = ($signalClose - $entryPrice) / $entryPrice;
            if ($signalReturn > $threshold) {
                $exitOffset = $day + 1;
                if (! isset($pathBars[$exitOffset])) {
                    return [
                        'missing_path_data_flag' => true,
                        'missing_path_reason_code' => 'WS_BT_C23_NEXT_OPEN_AFTER_SIGNAL_MISSING',
                    ];
                }
                $exitBar = $pathBars[$exitOffset];
                $exitOpen = $this->num($exitBar['open'] ?? null);
                if ($exitOpen === null || $exitOpen <= 0) {
                    return [
                        'missing_path_data_flag' => true,
                        'missing_path_reason_code' => 'WS_BT_C23_NEXT_OPEN_AFTER_SIGNAL_MISSING',
                    ];
                }

                return $this->exitPayload(
                    $day,
                    (string) $bar['date'],
                    'close_profit_gt_threshold',
                    $signalClose,
                    $signalReturn,
                    (string) $exitBar['date'],
                    $exitOpen,
                    $exitOffset,
                    'rule_first_profit_capture_next_open'
                );
            }
        }

        return null;
    }

    private function damageControlExit(float $entryPrice, array $pathBars, array $profile): ?array
    {
        $noProfitByDay = (int) ($profile['no_profit_by_day'] ?? 0);
        $exitOffset = (int) ($profile['damage_exit_offset'] ?? 0);
        if ($noProfitByDay < 1 || $exitOffset <= $noProfitByDay) {
            return null;
        }

        for ($day = 1; $day <= $noProfitByDay; $day++) {
            if (! isset($pathBars[$day])) {
                return [
                    'missing_path_data_flag' => true,
                    'missing_path_reason_code' => 'WS_BT_C23_DAMAGE_CONTROL_SIGNAL_PATH_MISSING',
                ];
            }
            $close = $this->num($pathBars[$day]['close'] ?? null);
            if ($close === null || $close <= 0) {
                return [
                    'missing_path_data_flag' => true,
                    'missing_path_reason_code' => 'WS_BT_C23_DAMAGE_CONTROL_SIGNAL_CLOSE_MISSING',
                ];
            }
            if ($close > $entryPrice) {
                return null;
            }
        }

        if (! isset($pathBars[$noProfitByDay], $pathBars[$exitOffset])) {
            return [
                'missing_path_data_flag' => true,
                'missing_path_reason_code' => 'WS_BT_C23_DAMAGE_CONTROL_NEXT_OPEN_MISSING',
            ];
        }

        $signalBar = $pathBars[$noProfitByDay];
        $exitBar = $pathBars[$exitOffset];
        $signalClose = $this->num($signalBar['close'] ?? null);
        $exitOpen = $this->num($exitBar['open'] ?? null);
        if ($signalClose === null || $signalClose <= 0 || $exitOpen === null || $exitOpen <= 0) {
            return [
                'missing_path_data_flag' => true,
                'missing_path_reason_code' => 'WS_BT_C23_DAMAGE_CONTROL_OHLC_MISSING',
            ];
        }

        return $this->exitPayload(
            $noProfitByDay,
            (string) $signalBar['date'],
            'no_profit_by_close',
            $signalClose,
            ($signalClose - $entryPrice) / $entryPrice,
            (string) $exitBar['date'],
            $exitOpen,
            $exitOffset,
            'rule_damage_control_no_profit_next_open'
        );
    }

    private function c22ShadowS06Exit(float $entryPrice, array $pathBars): array
    {
        foreach ($pathBars as $offset => $bar) {
            $close = $this->num($bar['close'] ?? null);
            if ($close !== null && $close > $entryPrice) {
                return [
                    'date' => (string) $bar['date'],
                    'price' => $close,
                    'offset' => (int) $offset,
                    'reason' => 'c22_s06_first_profitable_close',
                ];
            }
        }

        $lastOffset = count($pathBars);
        $last = $pathBars[$lastOffset];
        return [
            'date' => (string) $last['date'],
            'price' => (float) $last['close'],
            'offset' => $lastOffset,
            'reason' => 'c22_s06_hold_fallback',
        ];
    }

    private function exitPayload(
        ?int $signalOffset,
        ?string $signalDate,
        ?string $signalType,
        ?float $signalClosePrice,
        ?float $signalReturnPct,
        string $exitDate,
        float $exitPrice,
        int $exitOffset,
        string $reason
    ): array {
        $lookaheadSafe = $signalOffset === null || $exitOffset > $signalOffset;

        return [
            'signal_offset' => $signalOffset,
            'signal_date' => $signalDate,
            'signal_type' => $signalType,
            'signal_close_price' => $signalClosePrice,
            'signal_return_pct' => $signalReturnPct,
            'date' => $exitDate,
            'price' => $exitPrice,
            'offset' => $exitOffset,
            'reason' => $reason,
            'lookahead_safe' => $lookaheadSafe,
            'lookahead_violation_reason' => $lookaheadSafe ? null : 'WS_BT_C23_RULE_EXIT_NOT_AFTER_SIGNAL_CLOSE',
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
        ];
    }

    private function proposalItemsToTrades(array $items, array $paramset): array
    {
        $trades = [];
        $rank = 1;
        foreach ($items as $item) {
            $ticker = strtoupper(trim((string) ($item['ticker_code'] ?? $item['ticker'] ?? '')));
            $tradeDate = (string) ($item['trade_date'] ?? '');
            if ($ticker === '' || ! $this->validDate($tradeDate)) {
                continue;
            }
            $metrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
            $trades[] = [
                'trade_date' => $tradeDate,
                'ticker_id' => (int) ($item['ticker_id'] ?? 0),
                'ticker' => $ticker,
                'recommendation_rank' => $rank,
                'recommendation_score' => $this->num($item['quality_score'] ?? null),
                'score_total' => $this->num($item['score_total'] ?? null),
                'score_metrics' => $metrics,
                'dv20_idr' => $this->num($metrics['dv20_idr'] ?? null),
                'vol_ratio' => $this->num($metrics['vol_ratio'] ?? null),
                'roc20' => $this->num($metrics['roc20'] ?? null),
                'atr14_pct' => $this->num($metrics['atr14_pct'] ?? null),
                'stop_atr_mult' => $this->num($paramset['risk']['stop_atr_mult'] ?? null),
                'min_rr' => $this->num($paramset['risk']['min_rr'] ?? null),
                'reason_codes' => ['WS_C23_FIXED_RECOMMENDATION_BEFORE_PATH_READ', 'WS_REC_SELECTED'],
                'contract_flags' => [
                    'recommendation_frozen_before_price_read' => true,
                    'future_path_price_used_for_measurement_only' => true,
                    'future_path_price_used_for_selection' => false,
                    'rule_ret_net_used_for_selection' => false,
                    'mfe_mae_used_for_selection' => false,
                    'C23_diagnostic_only' => true,
                ],
            ];
            $rank++;
        }
        return $trades;
    }

    private function dataAvailability(array $selectionArtifact, array $rows, array $calendarDates): array
    {
        $evaluated = $this->evaluatedCanonicalRows($rows);
        $notes = [];
        if ($rows === []) {
            $notes[] = 'No fixed recommendation rows were available from the C19 selection source for the selected filters.';
        }
        if ($evaluated === []) {
            $notes[] = 'No complete D+1 to D+5 OHLC path could be evaluated; C23 must remain blocked or path-missing only, never inferred.';
        }
        $selectionItemsAvailable = false;
        foreach (($selectionArtifact['diagnostics'] ?? []) as $diag) {
            if (is_array($diag) && array_filter($diag['proposed_path']['selected_items'] ?? [], 'is_array') !== []) {
                $selectionItemsAvailable = true;
                break;
            }
        }
        return [
            'signal_close_available' => $evaluated !== [],
            'next_open_available' => $evaluated !== [],
            'next_open_after_signal_available' => $evaluated !== [],
            'd1_to_d5_ohlc_available' => $evaluated !== [],
            'canonical_stop_target_available' => count(array_filter($evaluated, function (array $row): bool { return $row['stop_trigger_price'] !== null && $row['target_trigger_price'] !== null; })) > 0,
            'canonical_exit_reason_available' => $evaluated !== [],
            'c22_shadow_s06_recomputable' => $evaluated !== [],
            'lookahead_safe_exit_dates_resolvable' => $evaluated !== [],
            'fixed_selection_items_available' => $selectionItemsAvailable,
            'calendar_dates_available' => $calendarDates !== [],
            'notes' => $notes,
        ];
    }

    private function canonicalSummary(array $rows): array
    {
        $evaluated = $this->evaluatedCanonicalRows($rows);
        $returns = $this->values($evaluated, 'canonical_ret_net');
        $gaveBack = count(array_filter($evaluated, function (array $row): bool { return ($row['gave_back_profit_flag'] ?? false) === true; }));
        $never = count(array_filter($evaluated, function (array $row): bool { return ($row['never_profitable_flag'] ?? false) === true; }));
        return [
            'evaluated_picks_count' => count($evaluated),
            'price_missing_count' => count($rows) - count($evaluated),
            'path_missing_count' => count($rows) - count($evaluated),
            'canonical_avg_ret_net' => $this->avg($returns),
            'canonical_median_ret_net' => $this->median($returns),
            'canonical_p25_ret_net' => $this->percentile($returns, 0.25),
            'canonical_win_rate' => count($returns) > 0 ? count(array_filter($returns, function (float $v): bool { return $v > 0; })) / count($returns) : null,
            'canonical_gave_back_profit_count' => $gaveBack,
            'canonical_gave_back_profit_rate' => count($evaluated) > 0 ? $gaveBack / count($evaluated) : null,
            'canonical_never_profitable_count' => $never,
            'canonical_never_profitable_rate' => count($evaluated) > 0 ? $never / count($evaluated) : null,
            'canonical_exit_stop_count' => count(array_filter($evaluated, function (array $row): bool { return ($row['canonical_exit_reason'] ?? '') === 'exit_stop'; })),
            'canonical_exit_target_count' => count(array_filter($evaluated, function (array $row): bool { return ($row['canonical_exit_reason'] ?? '') === 'exit_target'; })),
            'canonical_exit_hold_count' => count(array_filter($evaluated, function (array $row): bool { return ($row['canonical_exit_reason'] ?? '') === 'exit_hold'; })),
            'canonical_exit_day_offset_distribution' => $this->distribution($evaluated, 'canonical_exit_day_offset', 'NONE'),
            'canonical_exit_reason_distribution' => $this->distribution($evaluated, 'canonical_exit_reason', 'NONE'),
        ];
    }

    private function ruleProfileSummary(array $rows, array $canonicalSummary): array
    {
        $byProfile = [];
        foreach ($rows as $row) {
            $code = (string) ($row['rule_profile_code'] ?? 'UNKNOWN');
            $byProfile[$code][] = $row;
        }
        ksort($byProfile, SORT_STRING);
        $out = [];
        $canonicalGivebackRate = $this->num($canonicalSummary['canonical_gave_back_profit_rate'] ?? null);
        foreach ($byProfile as $code => $profileRows) {
            $evaluated = $this->evaluatedRuleRows($profileRows);
            $returns = $this->values($evaluated, 'rule_ret_net');
            $deltas = $this->values($evaluated, 'rule_ret_delta_vs_canonical');
            $deltasVsC22 = $this->values($evaluated, 'rule_ret_delta_vs_c22_shadow_s06');
            $gapsVsC22 = $this->values($evaluated, 'profit_capture_gap_vs_c22_s06');
            $givebackCount = count(array_filter($evaluated, function (array $row): bool { return $this->exitGaveBackFlag($row, $this->num($row['rule_ret_net'] ?? null)); }));
            $improvedCount = count(array_filter($evaluated, function (array $row): bool { return ($row['rule_ret_delta_vs_canonical'] ?? 0) > 0.0000001; }));
            $worsenedCount = count(array_filter($evaluated, function (array $row): bool { return ($row['rule_ret_delta_vs_canonical'] ?? 0) < -0.0000001; }));
            $unchangedCount = max(0, count($evaluated) - $improvedCount - $worsenedCount);
            $givebackRate = count($evaluated) > 0 ? $givebackCount / count($evaluated) : null;
            $lookaheadViolationCount = count(array_filter($evaluated, function (array $row): bool { return ($row['lookahead_safe'] ?? false) !== true; }));
            $family = self::ruleProfiles()[$code]['family'] ?? 'unknown';
            $out[] = [
                'profile_code' => $code,
                'profile_family' => $family,
                'rule_family' => $family,
                'evaluated_picks_count' => count($evaluated),
                'avg_ret_net' => $this->avg($returns),
                'median_ret_net' => $this->median($returns),
                'p25_ret_net' => $this->percentile($returns, 0.25),
                'win_rate' => count($returns) > 0 ? count(array_filter($returns, function (float $v): bool { return $v > 0; })) / count($returns) : null,
                'avg_delta_vs_canonical' => $this->avg($deltas),
                'median_delta_vs_canonical' => $this->median($deltas),
                'p25_delta_vs_canonical' => $this->percentile($deltas, 0.25),
                'avg_delta_vs_c22_shadow_s06' => $this->avg($deltasVsC22),
                'median_delta_vs_c22_shadow_s06' => $this->median($deltasVsC22),
                'p25_delta_vs_c22_shadow_s06' => $this->percentile($deltasVsC22, 0.25),
                'profit_capture_gap_vs_c22_s06_avg' => $this->avg($gapsVsC22),
                'profit_capture_gap_vs_c22_s06_median' => $this->median($gapsVsC22),
                'profit_capture_gap_vs_c22_s06_p25' => $this->percentile($gapsVsC22, 0.25),
                'improved_pick_count' => $improvedCount,
                'worsened_pick_count' => $worsenedCount,
                'unchanged_pick_count' => $unchangedCount,
                'improved_pick_rate' => count($evaluated) > 0 ? $improvedCount / count($evaluated) : null,
                'gave_back_profit_count' => $givebackCount,
                'gave_back_profit_rate' => $givebackRate,
                'gave_back_profit_reduction_vs_canonical' => $canonicalGivebackRate !== null && $givebackRate !== null ? $canonicalGivebackRate - $givebackRate : null,
                'loss_reduction_count' => count(array_filter($evaluated, function (array $row): bool { return ($row['loss_reduced_flag'] ?? false) === true; })),
                'loss_reduction_rate' => count($evaluated) > 0 ? count(array_filter($evaluated, function (array $row): bool { return ($row['loss_reduced_flag'] ?? false) === true; })) / count($evaluated) : null,
                'profit_capture_count' => count(array_filter($evaluated, function (array $row): bool { return ($row['profit_captured_flag'] ?? false) === true; })),
                'profit_capture_rate' => count($evaluated) > 0 ? count(array_filter($evaluated, function (array $row): bool { return ($row['profit_captured_flag'] ?? false) === true; })) / count($evaluated) : null,
                'lookahead_safe_count' => count($evaluated) - $lookaheadViolationCount,
                'lookahead_violation_count' => $lookaheadViolationCount,
                'exit_day_offset_distribution' => $this->distribution($evaluated, 'rule_exit_day_offset', 'NONE'),
                'exit_reason_distribution' => $this->distribution($evaluated, 'rule_exit_reason', 'NONE'),
            ];
        }
        return $out;
    }

    private function familySummary(array $profileSummaries, string $family): array
    {
        $rows = array_values(array_filter($profileSummaries, function (array $row) use ($family): bool {
            return ($row['profile_family'] ?? null) === $family;
        }));
        return [
            'profile_family' => $family,
            'profile_count' => count($rows),
            'profiles' => $rows,
            'best_by_median_delta' => $this->bestByMetric($rows, 'median_delta_vs_canonical'),
            'best_by_p25_delta' => $this->bestByMetric($rows, 'p25_delta_vs_canonical'),
            'best_by_giveback_reduction' => $this->bestByMetric($rows, 'gave_back_profit_reduction_vs_canonical'),
            'best_by_loss_reduction_rate' => $this->bestByMetric($rows, 'loss_reduction_rate'),
        ];
    }

    private function c22ShadowS06Summary(array $rows): array
    {
        $unique = [];
        foreach ($this->evaluatedRuleRows($rows) as $row) {
            if (! is_numeric($row['c22_shadow_s06_ret_net'] ?? null)) {
                continue;
            }
            $key = implode('|', [
                (string) ($row['trade_date'] ?? ''),
                (string) ($row['ticker'] ?? ''),
                (string) ($row['param_id'] ?? ''),
                (string) ($row['row_code'] ?? ''),
            ]);
            if (! isset($unique[$key])) {
                $unique[$key] = $row;
            }
        }

        $evaluated = array_values($unique);
        $returns = $this->values($evaluated, 'c22_shadow_s06_ret_net');
        $deltas = $this->values($evaluated, 'c22_shadow_s06_delta_vs_canonical');
        $gaveBackCount = count(array_filter($evaluated, function (array $row): bool {
            return $this->exitGaveBackFlag($row, $this->num($row['c22_shadow_s06_ret_net'] ?? null));
        }));

        return [
            'c22_shadow_s06_evaluated_picks_count' => count($evaluated),
            'c22_shadow_s06_avg_ret_net' => $this->avg($returns),
            'c22_shadow_s06_median_ret_net' => $this->median($returns),
            'c22_shadow_s06_p25_ret_net' => $this->percentile($returns, 0.25),
            'c22_shadow_s06_win_rate' => count($returns) > 0 ? count(array_filter($returns, function (float $v): bool { return $v > 0; })) / count($returns) : null,
            'c22_shadow_s06_avg_delta_vs_canonical' => $this->avg($deltas),
            'c22_shadow_s06_median_delta_vs_canonical' => $this->median($deltas),
            'c22_shadow_s06_p25_delta_vs_canonical' => $this->percentile($deltas, 0.25),
            'c22_shadow_s06_gave_back_profit_count' => $gaveBackCount,
            'c22_shadow_s06_gave_back_profit_rate' => count($evaluated) > 0 ? $gaveBackCount / count($evaluated) : null,
            'c22_shadow_s06_exit_day_offset_distribution' => $this->distribution($evaluated, 'c22_shadow_s06_exit_day_offset', 'NONE'),
        ];
    }

    private function paramConsistencySummary(array $rows, array $profileSummaries): array
    {
        $best = $this->bestRuleCandidateSummary($profileSummaries);
        $bestCode = (string) ($best['profile_code'] ?? '');
        $bestRows = array_values(array_filter($this->evaluatedRuleRows($rows), function (array $row) use ($bestCode): bool {
            return $bestCode !== '' && ($row['rule_profile_code'] ?? null) === $bestCode;
        }));

        $byParam = [];
        foreach ($bestRows as $row) {
            $key = (string) ($row['param_id'] ?? 'UNKNOWN');
            $byParam[$key][] = $row;
        }
        ksort($byParam, SORT_STRING);

        $perParam = [];
        $benefited = [];
        foreach ($byParam as $paramId => $paramRows) {
            $deltas = $this->values($paramRows, 'rule_ret_delta_vs_canonical');
            $improved = count(array_filter($paramRows, function (array $row): bool {
                return ($row['rule_ret_delta_vs_canonical'] ?? 0) > 0.0000001;
            }));
            $summary = [
                'param_id' => is_numeric($paramId) ? (int) $paramId : $paramId,
                'evaluated_picks_count' => count($paramRows),
                'avg_delta_vs_canonical' => $this->avg($deltas),
                'median_delta_vs_canonical' => $this->median($deltas),
                'p25_delta_vs_canonical' => $this->percentile($deltas, 0.25),
                'improved_pick_rate' => count($paramRows) > 0 ? $improved / count($paramRows) : null,
            ];
            $perParam[] = $summary;
            if ((($summary['avg_delta_vs_canonical'] ?? 0) > 0.001)
                || (($summary['median_delta_vs_canonical'] ?? 0) > 0)
                || (($summary['p25_delta_vs_canonical'] ?? 0) > 0)
                || (($summary['improved_pick_rate'] ?? 0) >= 0.50 && (($summary['p25_delta_vs_canonical'] ?? 0) > -0.003))) {
                $benefited[] = $summary['param_id'];
            }
        }

        return [
            'best_profile_code' => $bestCode !== '' ? $bestCode : null,
            'evaluated_param_count' => count($byParam),
            'benefited_param_count' => count($benefited),
            'benefited_param_ids' => $benefited,
            'param_consistency_found' => count($benefited) >= 2,
            'per_param_summary' => $perParam,
        ];
    }

    private function profileConsistencySummary(array $rows, array $profileSummaries): array
    {
        $stableProfiles = [];
        foreach ($profileSummaries as $summary) {
            if ((($summary['median_delta_vs_canonical'] ?? 0) > 0)
                && (($summary['p25_delta_vs_canonical'] ?? 0) > -0.003)
                && (($summary['lookahead_violation_count'] ?? 1) === 0)) {
                $stableProfiles[] = $summary['profile_code'] ?? null;
            }
        }

        return [
            'profile_count' => count($profileSummaries),
            'stable_profile_count' => count(array_filter($stableProfiles)),
            'stable_profile_codes' => array_values(array_filter($stableProfiles)),
            'profile_consistency_found' => count(array_filter($stableProfiles)) >= 2,
            'evaluated_rule_row_count' => count($this->evaluatedRuleRows($rows)),
        ];
    }

    private function monthStabilitySummary(array $rows, array $profileSummaries): array
    {
        $best = $this->bestRuleCandidateSummary($profileSummaries);
        $bestCode = (string) ($best['profile_code'] ?? '');
        $bestRows = array_values(array_filter($this->evaluatedRuleRows($rows), function (array $row) use ($bestCode): bool {
            return $bestCode !== '' && ($row['rule_profile_code'] ?? null) === $bestCode;
        }));

        $byMonth = [];
        foreach ($bestRows as $row) {
            $month = substr((string) ($row['trade_date'] ?? ''), 0, 7);
            if ($month !== '') {
                $byMonth[$month][] = $row;
            }
        }
        ksort($byMonth, SORT_STRING);

        $perMonth = [];
        $positive = 0;
        foreach ($byMonth as $month => $monthRows) {
            $deltas = $this->values($monthRows, 'rule_ret_delta_vs_canonical');
            $avgDelta = $this->avg($deltas);
            if ($avgDelta !== null && $avgDelta > 0) {
                $positive++;
            }
            $perMonth[] = [
                'month' => $month,
                'evaluated_picks_count' => count($monthRows),
                'avg_delta_vs_canonical' => $avgDelta,
                'median_delta_vs_canonical' => $this->median($deltas),
            ];
        }

        $monthCount = count($byMonth);
        return [
            'best_profile_code' => $bestCode !== '' ? $bestCode : null,
            'month_count' => $monthCount,
            'positive_month_count' => $positive,
            'positive_month_rate' => $monthCount > 0 ? $positive / $monthCount : null,
            'month_stability_sufficient' => $monthCount >= 2 && ($positive / max(1, $monthCount)) >= 0.50,
            'per_month_summary' => $perMonth,
        ];
    }

    private function lookaheadSafetySummary(array $rows): array
    {
        $evaluated = $this->evaluatedRuleRows($rows);
        $violations = array_values(array_filter($evaluated, function (array $row): bool {
            return ($row['lookahead_safe'] ?? false) !== true;
        }));

        return [
            'evaluated_rule_row_count' => count($evaluated),
            'lookahead_safe_count' => count($evaluated) - count($violations),
            'lookahead_violation_count' => count($violations),
            'lookahead_violation_reason_distribution' => $this->distribution($violations, 'lookahead_violation_reason', 'NONE'),
            'non_lookahead_all_profiles' => count($evaluated) > 0 && count($violations) === 0,
        ];
    }

    private function decision(
        array $canonical,
        array $profiles,
        array $c22Shadow,
        array $firstProfit,
        array $damage,
        array $combo,
        array $paramConsistency,
        array $monthStability,
        array $lookahead
    ): array
    {
        if ((int) ($canonical['evaluated_picks_count'] ?? 0) === 0) {
            return [
                'decision_status' => 'C23_DIAGNOSTIC_BLOCKED',
                'catalog_allowed' => false,
                'oos_allowed' => false,
                'next_step' => 'Fix D+1 to D+5 path data availability before deciding any exit-capture direction.',
                'first_profit_capture_rule_signal_found' => false,
                'c22_shadow_gap_acceptable' => false,
                'non_lookahead_rule_candidate_found' => false,
                'damage_control_candidate_found' => false,
                'combo_rule_candidate_found' => false,
                'param_consistency_found' => false,
                'month_stability_sufficient' => false,
                'lookahead_violation_count' => 0,
                'C23_CATALOG_CODE' => 'NOT_CREATED',
                'C23_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            ];
        }

        $bestMedian = $this->bestByMetric($profiles, 'median_delta_vs_canonical');
        $bestP25 = $this->bestByMetric($profiles, 'p25_delta_vs_canonical');
        $bestGiveback = $this->bestByMetric($profiles, 'gave_back_profit_reduction_vs_canonical');
        $bestImprovedRate = $this->bestByMetric($profiles, 'improved_pick_rate');
        $bestWinRate = $this->bestByMetric($profiles, 'win_rate');
        $closestToC22 = $this->bestByClosestToC22($profiles);

        $firstProfitSignal = (($bestMedian['median_delta_vs_canonical'] ?? null) !== null && $bestMedian['median_delta_vs_canonical'] >= 0.003)
            || (($bestP25['p25_delta_vs_canonical'] ?? null) !== null && $bestP25['p25_delta_vs_canonical'] >= 0.003)
            || (($bestGiveback['gave_back_profit_reduction_vs_canonical'] ?? null) !== null && $bestGiveback['gave_back_profit_reduction_vs_canonical'] >= 0.05)
            || (($bestImprovedRate['improved_pick_rate'] ?? null) !== null && $bestImprovedRate['improved_pick_rate'] >= 0.53 && (($bestImprovedRate['p25_delta_vs_canonical'] ?? 0) > -0.003))
            || (($bestWinRate['win_rate'] ?? null) !== null
                && ($canonical['canonical_win_rate'] ?? null) !== null
                && ((float) $bestWinRate['win_rate'] - (float) $canonical['canonical_win_rate']) >= 0.05
                && (($bestWinRate['p25_delta_vs_canonical'] ?? 0) > -0.003));

        $canonicalMedian = $this->num($canonical['canonical_median_ret_net'] ?? null);
        $c22Median = $this->num($c22Shadow['c22_shadow_s06_median_ret_net'] ?? null);
        $closestMedian = $this->num($closestToC22['median_ret_net'] ?? null);
        $gapAcceptable = false;
        if ($canonicalMedian !== null && $c22Median !== null && $closestMedian !== null) {
            $gap = $c22Median - $canonicalMedian;
            $gapAcceptable = $gap <= 0 || $closestMedian >= ($canonicalMedian + ($gap * 0.50));
        }

        $lookaheadViolationCount = (int) ($lookahead['lookahead_violation_count'] ?? 0);
        $lookaheadSafe = (bool) ($lookahead['non_lookahead_all_profiles'] ?? false);
        $paramConsistencyFound = (bool) ($paramConsistency['param_consistency_found'] ?? false);
        $monthStabilitySufficient = (bool) ($monthStability['month_stability_sufficient'] ?? false);
        $damageCandidate = $this->familyHasP25OrLossWithoutMedianDamage($damage)
            || $this->familyHasGivebackAndDistributionImprovement($damage, 0.05);
        $comboCandidate = $this->familyHasP25OrLossWithoutMedianDamage($combo)
            || $this->familyHasGivebackAndDistributionImprovement($combo, 0.05)
            || (($combo['best_by_median_delta']['median_delta_vs_canonical'] ?? 0) >= 0.003);
        $nonLookaheadRuleCandidate = $firstProfitSignal && $lookaheadSafe && $paramConsistencyFound;
        $readyForNextDiagnosticDesign = $nonLookaheadRuleCandidate && $gapAcceptable && $monthStabilitySufficient;

        return [
            'decision_status' => $firstProfitSignal ? 'C23_FIRST_PROFIT_CAPTURE_RULE_SIGNAL_FOUND' : 'C23_FIRST_PROFIT_CAPTURE_RULE_SIGNAL_NOT_FOUND',
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'next_step' => $readyForNextDiagnosticDesign
                ? 'Use this as C24 design input only; catalog creation and OOS remain forbidden until a separate approved stage.'
                : 'Keep C23 as IS-only diagnostic evidence; do not create catalog, run OOS, or promote production behavior.',
            'first_profit_capture_rule_signal_found' => $firstProfitSignal,
            'c22_shadow_gap_acceptable' => $gapAcceptable,
            'non_lookahead_rule_candidate_found' => $nonLookaheadRuleCandidate,
            'damage_control_candidate_found' => $damageCandidate,
            'combo_rule_candidate_found' => $comboCandidate,
            'param_consistency_found' => $paramConsistencyFound,
            'month_stability_sufficient' => $monthStabilitySufficient,
            'lookahead_violation_count' => $lookaheadViolationCount,
            'C23_CATALOG_CODE' => 'NOT_CREATED',
            'C23_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'c24_design_input_only_candidate' => $readyForNextDiagnosticDesign,
            'first_profit_capture_best_by_median_delta' => $firstProfit['best_by_median_delta'] ?? [],
            'best_rule_profile_code_by_avg' => ($this->bestByMetric($profiles, 'avg_delta_vs_canonical')['profile_code'] ?? null),
            'best_rule_profile_code_by_median' => $bestMedian['profile_code'] ?? null,
            'best_rule_profile_code_by_p25' => $bestP25['profile_code'] ?? null,
            'best_rule_profile_code_by_win_rate' => ($this->bestByMetric($profiles, 'win_rate')['profile_code'] ?? null),
            'best_rule_profile_code_by_giveback_reduction' => $bestGiveback['profile_code'] ?? null,
            'best_rule_profile_code_by_closest_to_c22_s06' => $closestToC22['profile_code'] ?? null,
        ];
    }

    private function familyHasMedianP25AndGivebackImprovement(array $summary): bool
    {
        foreach (($summary['profiles'] ?? []) as $row) {
            if (($row['median_delta_vs_canonical'] ?? null) !== null
                && ($row['p25_delta_vs_canonical'] ?? null) !== null
                && $row['median_delta_vs_canonical'] > 0
                && $row['p25_delta_vs_canonical'] > 0
                && (($row['gave_back_profit_reduction_vs_canonical'] ?? 0) > 0)) {
                return true;
            }
        }
        return false;
    }

    private function familyHasGivebackAndDistributionImprovement(array $summary, float $minGivebackReduction): bool
    {
        foreach (($summary['profiles'] ?? []) as $row) {
            if (($row['gave_back_profit_reduction_vs_canonical'] ?? null) !== null
                && $row['gave_back_profit_reduction_vs_canonical'] >= $minGivebackReduction
                && ((($row['median_delta_vs_canonical'] ?? 0) > 0) || (($row['p25_delta_vs_canonical'] ?? 0) > 0))) {
                return true;
            }
        }
        return false;
    }

    private function familyHasP25OrLossWithoutMedianDamage(array $summary): bool
    {
        foreach (($summary['profiles'] ?? []) as $row) {
            if (((($row['p25_delta_vs_canonical'] ?? 0) > 0.0025) || (($row['loss_reduction_rate'] ?? 0) >= 0.20))
                && (($row['median_delta_vs_canonical'] ?? 0) > -0.0030)) {
                return true;
            }
        }
        return false;
    }

    private function familyHasMedianOrWinAndGivebackImprovement(array $summary): bool
    {
        foreach (($summary['profiles'] ?? []) as $row) {
            if (((($row['median_delta_vs_canonical'] ?? 0) > 0) || (($row['win_rate'] ?? 0) > 0.50))
                && (($row['gave_back_profit_reduction_vs_canonical'] ?? 0) > 0.05)) {
                return true;
            }
        }
        return false;
    }

    private function exitGaveBackFlag(array $row, ?float $retNet): bool
    {
        $mfe = $this->num($row['max_favorable_excursion_pct'] ?? null);
        return $mfe !== null && $retNet !== null && $mfe > 0.005 && ($retNet <= 0 || ($mfe - $retNet) >= 0.010);
    }

    private function targetStopLevels(float $entryPrice, array $trade, array $paramset): array
    {
        $risk = is_array($paramset['risk'] ?? null) ? $paramset['risk'] : [];
        $atr14Pct = $this->num($trade['atr14_pct'] ?? null);
        $stopAtrMult = $this->num($trade['stop_atr_mult'] ?? $risk['stop_atr_mult'] ?? null);
        $minRr = $this->num($trade['min_rr'] ?? $risk['min_rr'] ?? null);
        $stopPrice = null;
        $targetPrice = null;
        if ($atr14Pct !== null && $atr14Pct > 0 && $atr14Pct <= 1 && $stopAtrMult !== null && $stopAtrMult > 0 && $minRr !== null && $minRr > 0) {
            $stopPrice = $entryPrice * (1 - ($stopAtrMult * $atr14Pct));
            $targetPrice = $entryPrice + ($minRr * ($entryPrice - $stopPrice));
        }
        $stopTrigger = $stopPrice !== null ? $this->normalizeStopTriggerPrice($stopPrice) : null;
        $targetTrigger = $targetPrice !== null ? $this->normalizeTargetTriggerPrice($targetPrice) : null;
        $has = $stopTrigger !== null && $targetTrigger !== null && $stopTrigger < $entryPrice && $targetTrigger > $entryPrice;
        return [
            'has_target_stop' => $has,
            'stop_price' => $has ? $stopPrice : null,
            'target_price' => $has ? $targetPrice : null,
            'stop_trigger_price' => $has ? $stopTrigger : null,
            'target_trigger_price' => $has ? $targetTrigger : null,
        ];
    }

    private function requiredPriceTickerMap(array $trades, array $calendarDates, int $holdingDays, bool $includeSignalDate): array
    {
        $map = [];
        foreach ($trades as $trade) {
            $ticker = strtoupper(trim((string) ($trade['ticker'] ?? $trade['ticker_code'] ?? '')));
            $tradeDate = (string) ($trade['trade_date'] ?? '');
            $entryDate = $this->nextTradingDate($tradeDate, $calendarDates);
            if ($ticker === '' || $entryDate === null) {
                continue;
            }
            if ($includeSignalDate) {
                $map[$tradeDate][$ticker] = $ticker;
            }
            foreach ($this->tradingWindowFrom($entryDate, $calendarDates, $holdingDays) as $date) {
                $map[$date][$ticker] = $ticker;
            }
        }
        ksort($map, SORT_STRING);
        foreach ($map as $date => $codes) {
            ksort($codes, SORT_STRING);
            $map[$date] = array_values($codes);
        }
        return $map;
    }

    private function retNet(float $entryPrice, float $exitPrice, array $paramset): ?float
    {
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $notionalIdr = $this->num($backtest['notional_idr'] ?? null) ?? 10000000.0;
        $lotSize = max(1, (int) ($backtest['lot_size'] ?? 100));
        $feeBuy = $this->num($backtest['fee_buy_idr'] ?? null) ?? 2500.0;
        $feeSell = $this->num($backtest['fee_sell_idr'] ?? null) ?? 2500.0;
        $lots = (int) floor($notionalIdr / ($entryPrice * $lotSize));
        if ($lots < 1) {
            return null;
        }
        $quantity = $lots * $lotSize;
        $grossBuy = $entryPrice * $quantity;
        $grossSell = $exitPrice * $quantity;
        return ($grossSell - $grossBuy - $feeBuy - $feeSell) / ($grossBuy + $feeBuy);
    }

    private function tradableBar(array $bar, array $paramset): bool
    {
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $minimum = max(1, (int) ($backtest['min_tradable_volume'] ?? 1));
        return is_numeric($bar['volume'] ?? null) && (int) $bar['volume'] >= $minimum;
    }

    private function publishedBar(array $priceSeries, string $ticker, string $tradeDate): ?array
    {
        $ticker = strtoupper($ticker);
        $bar = null;
        if (isset($priceSeries[$ticker][$tradeDate]) && is_array($priceSeries[$ticker][$tradeDate])) {
            $bar = $priceSeries[$ticker][$tradeDate];
        } elseif (isset($priceSeries[$ticker.'.JK'][$tradeDate]) && is_array($priceSeries[$ticker.'.JK'][$tradeDate])) {
            $bar = $priceSeries[$ticker.'.JK'][$tradeDate];
        }
        if ($bar === null) {
            return null;
        }
        if (($bar['published'] ?? true) !== true || ($bar['readable'] ?? true) !== true) {
            return null;
        }
        return $bar;
    }

    private function nextTradingDate(string $tradeDate, array $calendarDates): ?string
    {
        foreach ($calendarDates as $date) {
            if (strcmp($date, $tradeDate) > 0) {
                return $date;
            }
        }
        return null;
    }

    private function tradingWindowFrom(string $entryDate, array $calendarDates, int $holdingDays): array
    {
        $window = [];
        $started = false;
        foreach ($calendarDates as $date) {
            if (! $started && $date === $entryDate) {
                $started = true;
            }
            if ($started) {
                $window[] = $date;
                if (count($window) >= $holdingDays) {
                    break;
                }
            }
        }
        return $window;
    }

    private function evaluatedCanonicalRows(array $rows): array
    {
        return array_values(array_filter($rows, function (array $row): bool {
            return ($row['missing_path_data_flag'] ?? true) === false && is_numeric($row['canonical_ret_net'] ?? null);
        }));
    }

    private function evaluatedRuleRows(array $rows): array
    {
        return array_values(array_filter($rows, function (array $row): bool {
            return ($row['missing_path_data_flag'] ?? true) === false && is_numeric($row['rule_ret_net'] ?? null);
        }));
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

    private function distribution(array $rows, string $key, string $nullLabel): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $value = $row[$key] ?? null;
            $label = $value === null || $value === '' ? $nullLabel : (string) $value;
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }
        ksort($counts, SORT_STRING);
        return $counts;
    }

    private function normalizeStopTriggerPrice(float $price): ?float
    {
        if ($price <= 0) {
            return null;
        }
        $tick = $this->priceTick($price);
        $normalized = floor(($price + 0.000000001) / $tick) * $tick;
        return $normalized > 0 ? (float) $normalized : null;
    }

    private function normalizeTargetTriggerPrice(float $price): ?float
    {
        if ($price <= 0) {
            return null;
        }
        $tick = $this->priceTick($price);
        $normalized = ceil(($price - 0.000000001) / $tick) * $tick;
        return $normalized > 0 ? (float) $normalized : null;
    }

    private function priceTick(float $price): float
    {
        if ($price < 200) {
            return 1.0;
        }
        if ($price < 500) {
            return 2.0;
        }
        if ($price < 2000) {
            return 5.0;
        }
        if ($price < 5000) {
            return 10.0;
        }
        return 25.0;
    }

    private function sortItems(array $items): array
    {
        usort($items, function (array $left, array $right): int {
            foreach (['trade_date', 'proposed_rank', 'ticker_id', 'ticker_code'] as $key) {
                $cmp = strcmp((string) ($left[$key] ?? ''), (string) ($right[$key] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            return 0;
        });
        return $items;
    }

    private function selectedRuleProfileDefinitions(array $profiles): array
    {
        $defs = [];
        foreach ($profiles as $code) {
            $defs[] = array_merge(['profile_code' => $code], self::ruleProfiles()[$code] ?? []);
        }
        return $defs;
    }

    private function canonicalEvaluationModel(): array
    {
        return [
            'ENTRY' => 'NEXT_OPEN',
            'EXIT' => 'STOP_TP_OR_TIME',
            'HOLD' => 5,
            'FEE' => 'IDR_FIXED',
            'SLIP' => 0,
            'GAP' => 'OPEN',
            'PX' => 'IDX_BANDS',
            'canonical_model_changed' => false,
            'rule_exit_capture_analysis_only' => true,
            'rule_candidate_diagnostic_only' => true,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C23_CATALOG_CODE' => 'NOT_CREATED',
            'C23_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_TICKER_BLACKLIST' => true,
            'NO_MONTH_BLACKLIST' => true,
            'NO_SECTOR_WHITELIST' => true,
            'NO_BEST_OF_FAILED_BINDING' => true,
            'NO_C01_TO_C22_MUTATION' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'NO_C19_REOPEN' => true,
            'NO_C20_REOPEN' => true,
            'NO_C21_REOPEN' => true,
            'NO_C22_REOPEN' => true,
            'canonical_evaluation_model_unchanged' => $this->canonicalEvaluationModel(),
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'future_path_price_used_for_measurement_only' => true,
            'future_path_price_used_for_selection' => false,
            'rule_exit_used_for_selection' => false,
            'rule_ret_net_used_for_selection' => false,
            'c22_shadow_s06_used_for_selection' => false,
            'mfe_mae_used_for_selection' => false,
        ];
    }

    private function emptyPriceRead(string $fromDate, string $toDate): array
    {
        return [
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_PRICE_SERIES_EMPTY_REQUEST',
            'series_by_ticker' => [],
            'price_series_manifest' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'required_price_date_count' => 0,
                'requested_ticker_date_pair_count' => 0,
                'missing_price_dates' => [],
                'missing_price_rows' => [],
            ],
            'publication_manifest' => [],
        ];
    }

    private function ruleProfileCodes($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (! is_array($value)) {
            return false;
        }
        $known = self::ruleProfiles();
        $codes = [];
        foreach ($value as $item) {
            $code = strtoupper(trim((string) $item));
            if ($code === '' || ! isset($known[$code])) {
                return false;
            }
            $codes[] = $code;
        }
        return array_values(array_unique($codes));
    }

    private function paramIds($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (! is_array($value)) {
            return false;
        }
        $ids = [];
        foreach ($value as $item) {
            if (! is_numeric($item) || (int) $item <= 0) {
                return false;
            }
            $ids[] = (int) $item;
        }
        $ids = array_values(array_unique($ids));
        sort($ids);
        return $ids;
    }

    private function normalizeDateList(array $dates): array
    {
        $out = [];
        foreach ($dates as $date) {
            $date = (string) $date;
            if ($this->validDate($date)) {
                $out[] = $date;
            }
        }
        $out = array_values(array_unique($out));
        sort($out);
        return $out;
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

    private function bestByClosestToC22(array $rows): array
    {
        $best = [];
        $bestDistance = null;
        foreach ($rows as $row) {
            if (is_numeric($row['profit_capture_gap_vs_c22_s06_median'] ?? null)) {
                $distance = abs((float) $row['profit_capture_gap_vs_c22_s06_median']);
            } elseif (is_numeric($row['median_delta_vs_c22_shadow_s06'] ?? null)) {
                $distance = abs((float) $row['median_delta_vs_c22_shadow_s06']);
            } else {
                continue;
            }
            if ($best === [] || $bestDistance === null || $distance < $bestDistance) {
                $best = $row;
                $bestDistance = $distance;
            }
        }
        return $best;
    }

    private function bestRuleCandidateSummary(array $profileSummaries): array
    {
        $candidates = array_values(array_filter($profileSummaries, function (array $row): bool {
            return ($row['profile_family'] ?? null) !== 'canonical'
                && (int) ($row['evaluated_picks_count'] ?? 0) > 0
                && (int) ($row['lookahead_violation_count'] ?? 0) === 0;
        }));
        if ($candidates === []) {
            return $this->bestByMetric($profileSummaries, 'median_delta_vs_canonical');
        }

        $best = [];
        foreach ($candidates as $row) {
            if (! is_numeric($row['avg_delta_vs_canonical'] ?? null)) {
                continue;
            }
            if ($best === []
                || (float) $row['avg_delta_vs_canonical'] > (float) $best['avg_delta_vs_canonical']
                || ((float) $row['avg_delta_vs_canonical'] === (float) $best['avg_delta_vs_canonical']
                    && (float) ($row['win_rate'] ?? 0) > (float) ($best['win_rate'] ?? 0))) {
                $best = $row;
            }
        }

        return $best !== [] ? $best : $this->bestByMetric($profileSummaries, 'median_delta_vs_canonical');
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
        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }
        return ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
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

    private function positiveIntOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            return null;
        }
        $int = (int) $value;
        return $int > 0 ? $int : null;
    }

    private function num($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function validDate(string $date): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_replace_recursive([
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'c23_catalog_implementation_deferred' => 1,
            'c23_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }
}
