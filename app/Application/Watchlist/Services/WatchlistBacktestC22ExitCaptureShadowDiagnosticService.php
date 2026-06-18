<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketBenchmarkReadService;
use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC22ExitCaptureShadowDiagnosticService
{
    public const ARTIFACT_TYPE = 'C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-run-1.json';
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

    public static function shadowProfiles(): array
    {
        return [
            'C22_S00_CANONICAL_BASELINE' => ['family' => 'canonical', 'description' => 'Canonical ENTRY=NEXT_OPEN, EXIT=STOP_TP_OR_TIME, HOLD=5 baseline.'],
            'C22_S01_EXIT_D1_CLOSE' => ['family' => 'hold_compression', 'exit_day' => 1, 'description' => 'Shadow fixed exit at D+1 close.'],
            'C22_S02_EXIT_D2_CLOSE' => ['family' => 'hold_compression', 'exit_day' => 2, 'description' => 'Shadow fixed exit at D+2 close.'],
            'C22_S03_EXIT_D3_CLOSE' => ['family' => 'hold_compression', 'exit_day' => 3, 'description' => 'Shadow fixed exit at D+3 close.'],
            'C22_S04_EXIT_D4_CLOSE' => ['family' => 'hold_compression', 'exit_day' => 4, 'description' => 'Shadow fixed exit at D+4 close.'],
            'C22_S05_EXIT_D5_CLOSE_ONLY' => ['family' => 'hold_compression', 'exit_day' => 5, 'description' => 'Shadow fixed exit at D+5 close without intraday stop/target.'],
            'C22_S06_FIRST_PROFITABLE_CLOSE_EXIT' => ['family' => 'first_profitable_close', 'description' => 'Exit on the first D+1..D+5 close above entry, else D+5 close.'],
            'C22_S07_PROFIT_LOCK_AFTER_MFE_0_75PCT' => ['family' => 'profit_lock', 'mfe_threshold' => 0.0075, 'lock_fraction' => 0.50, 'description' => 'After MFE reaches +0.75%, protect half of the excursion.'],
            'C22_S08_PROFIT_LOCK_AFTER_MFE_1_00PCT' => ['family' => 'profit_lock', 'mfe_threshold' => 0.0100, 'lock_fraction' => 0.50, 'description' => 'After MFE reaches +1.00%, protect half of the excursion.'],
            'C22_S09_PROFIT_LOCK_AFTER_MFE_1_50PCT' => ['family' => 'profit_lock', 'mfe_threshold' => 0.0150, 'lock_fraction' => 0.50, 'description' => 'After MFE reaches +1.50%, protect half of the excursion.'],
            'C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT' => ['family' => 'breakeven', 'mfe_threshold' => 0.0100, 'description' => 'After MFE reaches +1.00%, stop is raised to breakeven.'],
            'C22_S11_TRAILING_FROM_MFE_1_50PCT_GIVEBACK_0_75PCT' => ['family' => 'trailing', 'mfe_threshold' => 0.0150, 'giveback' => 0.0075, 'description' => 'After MFE reaches +1.50%, trail by 0.75 percentage points from peak.'],
            'C22_S12_TARGET_CLOSE_1_00PCT' => ['family' => 'target_close', 'target_pct' => 0.0100, 'description' => 'Exit on the first close at or above +1.00%, else D+5 close.'],
            'C22_S13_TARGET_CLOSE_1_50PCT' => ['family' => 'target_close', 'target_pct' => 0.0150, 'description' => 'Exit on the first close at or above +1.50%, else D+5 close.'],
            'C22_S14_TARGET_CLOSE_2_00PCT' => ['family' => 'target_close', 'target_pct' => 0.0200, 'description' => 'Exit on the first close at or above +2.00%, else D+5 close.'],
            'C22_S15_STOP_LOSS_1_50PCT_SHADOW' => ['family' => 'stop_loss', 'stop_pct' => 0.0150, 'description' => 'Shadow fixed stop-loss at -1.50%, else D+5 close.'],
            'C22_S16_STOP_LOSS_2_00PCT_SHADOW' => ['family' => 'stop_loss', 'stop_pct' => 0.0200, 'description' => 'Shadow fixed stop-loss at -2.00%, else D+5 close.'],
            'C22_S17_STOP_LOSS_2_50PCT_SHADOW' => ['family' => 'stop_loss', 'stop_pct' => 0.0250, 'description' => 'Shadow fixed stop-loss at -2.50%, else D+5 close.'],
        ];
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode) !== '' ? trim($catalogCode) : self::DEFAULT_SOURCE_CATALOG_CODE;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C22_IS_ONLY_WINDOW_MISMATCH', 'C22 exit-capture shadow diagnostic requires the frozen IS window only.');
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.');
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C22_PARAM_IDS_INVALID', 'param_ids must be a comma-separated list of positive integers.');
        }
        $shadowProfiles = $this->shadowProfileCodes($options['shadow_profiles'] ?? ($options['profiles'] ?? null));
        if ($shadowProfiles === false) {
            return $this->blocked('WS_BT_C22_SHADOW_PROFILE_INVALID', 'shadow-profile-codes/profile-codes must be a comma-separated list of known C22 shadow profile codes.');
        }
        $profileScope = 'EXPLICIT';
        if ($shadowProfiles === []) {
            $shadowProfiles = [
                'C22_S00_CANONICAL_BASELINE',
                'C22_S01_EXIT_D1_CLOSE',
                'C22_S02_EXIT_D2_CLOSE',
                'C22_S03_EXIT_D3_CLOSE',
                'C22_S06_FIRST_PROFITABLE_CLOSE_EXIT',
                'C22_S08_PROFIT_LOCK_AFTER_MFE_1_00PCT',
                'C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT',
                'C22_S11_TRAILING_FROM_MFE_1_50PCT_GIVEBACK_0_75PCT',
            ];
            $profileScope = 'FAST_DEFAULT';
        }
        $maxShadowProfiles = $this->positiveIntOrNull($options['max_shadow_profiles'] ?? ($options['max_profiles'] ?? null));
        if ($maxShadowProfiles !== null) {
            $shadowProfiles = array_slice($shadowProfiles, 0, $maxShadowProfiles);
            $profileScope .= '_MAX_'.$maxShadowProfiles;
        }
        if ($shadowProfiles === []) {
            return $this->blocked('WS_BT_C22_SHADOW_PROFILE_EMPTY', 'No C22 shadow profile is selected.');
        }

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar is unavailable for C22 diagnostic.', ['calendar' => $calendar]);
        }
        $tradeDates = $this->normalizeDateList($calendar['trade_dates'] ?? []);
        $calendarDates = $this->normalizeDateList($calendar['calendar_dates'] ?? $calendar['trade_dates'] ?? []);
        if ($tradeDates === [] || $calendarDates === []) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar returned no usable dates.');
        }

        try {
            $rows = $this->paramGrid->allForCatalog($catalogCode, 'WS');
        } catch (\Throwable $e) {
            return $this->blocked('WS_BT_C22_SOURCE_CATALOG_UNAVAILABLE', $e->getMessage());
        }
        if ($paramIds !== []) {
            $allowed = array_fill_keys($paramIds, true);
            $rows = array_values(array_filter($rows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_C22_ROW_FILTER_NO_MATCH', 'No source catalog rows matched the explicit param filter.');
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
        $selectionResult = $this->selectionDiagnostic->execute($catalogCode, $fromDate, $toDate, $selectionOutput, [
            'param_ids' => $paramIds,
            'overwrite' => true,
            'executed_at' => $options['executed_at'] ?? ($toDate.'T23:59:59+07:00'),
        ]);
        if (($selectionResult['status'] ?? null) !== 'PASS') {
            return $this->blocked($selectionResult['reason_code'] ?? 'WS_BT_C22_SELECTION_SOURCE_NOT_READY', 'C19 selection diagnostic source did not produce a PASS artifact.', [
                'selection_result' => $selectionResult,
            ]);
        }
        $selectionArtifact = $this->readJson($selectionOutput);
        if ($selectionArtifact === null) {
            return $this->blocked('WS_BT_C22_SELECTION_ARTIFACT_UNREADABLE', 'C19 selection artifact could not be read.');
        }

        $executedAt = (string) ($options['executed_at'] ?? ($toDate.'T23:59:59+07:00'));
        $progress = $options['progress_callback'] ?? null;
        $maxPicks = $this->positiveIntOrNull($options['max_picks'] ?? null);
        $canonicalRows = [];
        $shadowRows = [];
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
                $progress('[C22] param '.$paramIndex.'/'.count($rowsByParamId).': '.$paramId.' profiles='.count($shadowProfiles));
            }
            $paramResult = $this->diagnoseParam(
                $selectionDiag,
                $row,
                $paramset,
                $tradeDates,
                $calendarDates,
                $fromDate,
                $toDate,
                $shadowProfiles,
                $maxPicks
            );
            foreach ($paramResult['canonical_rows'] as $canonicalRow) {
                $canonicalRows[] = $canonicalRow;
            }
            foreach ($paramResult['pick_shadow_rows'] as $shadowRow) {
                $shadowRows[] = $shadowRow;
            }
            $paramSummaries[] = $paramResult['summary'];
        }

        $canonicalSummary = $this->canonicalSummary($canonicalRows);
        $shadowProfileSummary = $this->shadowProfileSummary($shadowRows, $canonicalSummary);
        $holdDayComparisonSummary = $this->familySummary($shadowProfileSummary, 'hold_compression');
        $profitLockSummary = $this->familySummary($shadowProfileSummary, 'profit_lock');
        $breakevenSummary = $this->familySummary($shadowProfileSummary, 'breakeven');
        $trailingSummary = $this->familySummary($shadowProfileSummary, 'trailing');
        $targetDistanceSummary = $this->familySummary($shadowProfileSummary, 'target_close');
        $stopDistanceSummary = $this->familySummary($shadowProfileSummary, 'stop_loss');
        $dataAvailability = $this->dataAvailability($selectionArtifact, $canonicalRows, $calendarDates);
        $decision = $this->decision(
            $canonicalSummary,
            $shadowProfileSummary,
            $holdDayComparisonSummary,
            $profitLockSummary,
            $breakevenSummary,
            $trailingSummary,
            $targetDistanceSummary,
            $stopDistanceSummary
        );
        $status = $decision['decision_status'] === 'C22_DIAGNOSTIC_BLOCKED' ? 'BLOCKED' : 'PASS';

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => $status,
            'reason_code' => 'WS_BT_C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC',
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
                'c21_all_param_artifact_hash' => 'd6c6c72d51b40a0c852ce9bbc6a452c55920df13',
                'selection_source_artifact_path' => $selectionOutput,
                'selection_source_artifact_hash' => $selectionArtifact['artifact_hash'] ?? ($selectionResult['artifact_hash'] ?? null),
            ],
            'is_window' => [
                'from' => $fromDate,
                'to' => $toDate,
                'trade_date_count' => count($tradeDates),
                'boundary_censoring_rule' => 'EXCLUDE_LAST_HOLDING_DAYS_FROM_ENTRY_GENERATION;KEEP_ALL_PATH_PRICE_READS_WITHIN_IS;SHADOW_EXIT_MEASUREMENT_ONLY_AFTER_FIXED_PICK',
            ],
            'price_evaluation_model' => $this->canonicalEvaluationModel(),
            'data_availability' => $dataAvailability,
            'shadow_profiles' => $this->selectedShadowProfileDefinitions($shadowProfiles),
            'shadow_profile_count' => count($shadowProfiles),
            'profile_scope' => $profileScope,
            'param_summaries' => $paramSummaries,
            'canonical_rows' => $canonicalRows,
            'pick_shadow_rows' => $shadowRows,
            'canonical_summary' => $canonicalSummary,
            'shadow_profile_summary' => $shadowProfileSummary,
            'per_shadow_profile_summary' => $shadowProfileSummary,
            'hold_day_comparison_summary' => $holdDayComparisonSummary,
            'profit_lock_summary' => $profitLockSummary,
            'breakeven_summary' => $breakevenSummary,
            'trailing_summary' => $trailingSummary,
            'target_distance_summary' => $targetDistanceSummary,
            'stop_distance_summary' => $stopDistanceSummary,
            'decision' => $decision,
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message']);
        }

        $bestMedian = $this->bestByMetric($shadowProfileSummary, 'median_delta_vs_canonical');
        $bestP25 = $this->bestByMetric($shadowProfileSummary, 'p25_delta_vs_canonical');
        $bestGiveback = $this->bestByMetric($shadowProfileSummary, 'gave_back_profit_reduction_vs_canonical');

        return [
            'status' => $status,
            'reason_code' => 'WS_BT_C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'shadow_profile_count' => count($shadowProfiles),
            'profile_scope' => $profileScope,
            'evaluated_picks_count' => (int) ($canonicalSummary['evaluated_picks_count'] ?? 0),
            'path_missing_count' => (int) ($canonicalSummary['path_missing_count'] ?? 0),
            'canonical_avg_ret_net' => $canonicalSummary['canonical_avg_ret_net'] ?? null,
            'canonical_median_ret_net' => $canonicalSummary['canonical_median_ret_net'] ?? null,
            'canonical_p25_ret_net' => $canonicalSummary['canonical_p25_ret_net'] ?? null,
            'canonical_win_rate' => $canonicalSummary['canonical_win_rate'] ?? null,
            'canonical_gave_back_profit_rate' => $canonicalSummary['canonical_gave_back_profit_rate'] ?? null,
            'best_shadow_profile_code_by_median' => $bestMedian['profile_code'] ?? null,
            'best_shadow_profile_code_by_p25' => $bestP25['profile_code'] ?? null,
            'best_shadow_profile_code_by_giveback_reduction' => $bestGiveback['profile_code'] ?? null,
            'best_shadow_median_delta_vs_canonical' => $bestMedian['median_delta_vs_canonical'] ?? null,
            'best_shadow_p25_delta_vs_canonical' => $bestP25['p25_delta_vs_canonical'] ?? null,
            'best_giveback_reduction_vs_canonical' => $bestGiveback['gave_back_profit_reduction_vs_canonical'] ?? null,
            'exit_capture_signal_found' => $decision['exit_capture_signal_found'] ? 1 : 0,
            'early_exit_suspected_better' => $decision['early_exit_suspected_better'] ? 1 : 0,
            'profit_lock_suspected_better' => $decision['profit_lock_suspected_better'] ? 1 : 0,
            'breakeven_suspected_better' => $decision['breakeven_suspected_better'] ? 1 : 0,
            'trailing_suspected_better' => $decision['trailing_suspected_better'] ? 1 : 0,
            'target_distance_problem_suspected' => $decision['target_distance_problem_suspected'] ? 1 : 0,
            'stop_distance_problem_suspected' => $decision['stop_distance_problem_suspected'] ? 1 : 0,
            'hold_compression_suspected_better' => $decision['hold_compression_suspected_better'] ? 1 : 0,
            'c22_catalog_implementation_deferred' => 1,
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
        array $shadowProfiles,
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
        $shadowRows = [];
        foreach ($trades as $trade) {
            $path = $this->canonicalPath($trade, $row, $paramset, $series, $calendarDates, $holdingDays);
            $canonicalRows[] = $path['canonical_row'];
            foreach ($shadowProfiles as $profileCode) {
                $shadowRows[] = $this->shadowRow($path['canonical_row'], $path['path_bars'], $paramset, $profileCode);
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
                'shadow_ret_net_used_for_selection' => false,
                'mfe_mae_used_for_selection' => false,
            ]),
            'canonical_rows' => $canonicalRows,
            'pick_shadow_rows' => $shadowRows,
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
                'shadow_exit_used_for_selection' => false,
                'c22_diagnostic_only' => true,
            ],
        ];

        if ($ticker === '' || $tradeDate === '' || $entryDate === null || $signalBar === null || $entryBar === null || count($pathDates) < $holdingDays) {
            $base['missing_path_reason_code'] = 'WS_BT_C22_REQUIRED_ENTRY_OR_SIGNAL_PATH_MISSING';
            return ['canonical_row' => $base, 'path_bars' => []];
        }
        $signalClose = $this->num($signalBar['close'] ?? null);
        $entryPrice = $this->num($entryBar['open'] ?? null);
        if ($signalClose === null || $signalClose <= 0 || $entryPrice === null || $entryPrice <= 0) {
            $base['missing_path_reason_code'] = 'WS_BT_C22_REQUIRED_ENTRY_OR_SIGNAL_OHLC_MISSING';
            return ['canonical_row' => $base, 'path_bars' => []];
        }

        $pathBars = [];
        foreach ($pathDates as $index => $date) {
            $bar = $this->publishedBar($series, $ticker, $date);
            if ($bar === null || ! $this->tradableBar($bar, $paramset)) {
                $base['missing_path_reason_code'] = 'WS_BT_C22_D1_TO_D5_OHLC_PATH_MISSING';
                return ['canonical_row' => $base, 'path_bars' => []];
            }
            foreach (['open', 'high', 'low', 'close'] as $field) {
                $value = $this->num($bar[$field] ?? null);
                if ($value === null || $value <= 0) {
                    $base['missing_path_reason_code'] = 'WS_BT_C22_D1_TO_D5_OHLC_PATH_MISSING';
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

    private function shadowRow(array $canonical, array $pathBars, array $paramset, string $profileCode): array
    {
        $entryPrice = $this->num($canonical['entry_price'] ?? null);
        $canonicalRet = $this->num($canonical['canonical_ret_net'] ?? null);
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
            'first_profitable_day' => $canonical['first_profitable_day'] ?? null,
            'gave_back_profit_flag' => (bool) ($canonical['gave_back_profit_flag'] ?? false),
            'never_profitable_flag' => (bool) ($canonical['never_profitable_flag'] ?? false),
            'shadow_profile_code' => $profileCode,
            'shadow_profile_family' => self::shadowProfiles()[$profileCode]['family'] ?? 'unknown',
            'shadow_exit_date' => null,
            'shadow_exit_price' => null,
            'shadow_exit_day_offset' => null,
            'shadow_exit_reason' => null,
            'shadow_ret_net' => null,
            'shadow_ret_delta_vs_canonical' => null,
            'shadow_win_flag' => false,
            'canonical_win_flag' => $canonicalRet !== null && $canonicalRet > 0,
            'gave_back_profit_reduced_flag' => false,
            'loss_reduced_flag' => false,
            'profit_captured_flag' => false,
            'missing_path_data_flag' => (bool) ($canonical['missing_path_data_flag'] ?? true),
            'missing_path_reason_code' => $canonical['missing_path_reason_code'] ?? null,
            'future_path_price_used_for_selection' => false,
            'shadow_ret_net_used_for_selection' => false,
        ];
        if ($entryPrice === null || $entryPrice <= 0 || $pathBars === [] || ($canonical['missing_path_data_flag'] ?? true)) {
            return $base;
        }

        $exit = $this->shadowExit($profileCode, $entryPrice, $pathBars, $canonical);
        $retNet = $this->retNet($entryPrice, (float) $exit['price'], $paramset);
        $delta = $retNet !== null && $canonicalRet !== null ? $retNet - $canonicalRet : null;
        $shadowGaveBack = $this->shadowGaveBackFlag($canonical, $retNet);

        return array_merge($base, [
            'shadow_exit_date' => $exit['date'],
            'shadow_exit_price' => $exit['price'],
            'shadow_exit_day_offset' => $exit['offset'],
            'shadow_exit_reason' => $exit['reason'],
            'shadow_ret_net' => $retNet,
            'shadow_ret_delta_vs_canonical' => $delta,
            'shadow_win_flag' => $retNet !== null && $retNet > 0,
            'gave_back_profit_reduced_flag' => ((bool) ($canonical['gave_back_profit_flag'] ?? false)) && ! $shadowGaveBack,
            'loss_reduced_flag' => $canonicalRet !== null && $canonicalRet < 0 && $retNet !== null && $retNet > $canonicalRet,
            'profit_captured_flag' => $retNet !== null && $retNet > 0 && (($canonical['max_favorable_excursion_pct'] ?? 0) > 0),
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
        ]);
    }

    private function shadowExit(string $profileCode, float $entryPrice, array $pathBars, array $canonical): array
    {
        $profile = self::shadowProfiles()[$profileCode] ?? ['family' => 'canonical'];
        $family = (string) ($profile['family'] ?? 'canonical');
        if ($family === 'canonical') {
            return [
                'date' => (string) $canonical['canonical_exit_date'],
                'price' => (float) $canonical['canonical_exit_price'],
                'offset' => (int) $canonical['canonical_exit_day_offset'],
                'reason' => 'shadow_canonical_baseline',
            ];
        }
        if ($family === 'hold_compression') {
            $day = max(1, min(5, (int) ($profile['exit_day'] ?? 5)));
            $bar = $pathBars[$day] ?? $pathBars[count($pathBars)];
            return ['date' => $bar['date'], 'price' => $bar['close'], 'offset' => $day, 'reason' => 'shadow_exit_d'.$day.'_close'];
        }
        if ($family === 'first_profitable_close') {
            foreach ($pathBars as $day => $bar) {
                if ($bar['close'] > $entryPrice) {
                    return ['date' => $bar['date'], 'price' => $bar['close'], 'offset' => $day, 'reason' => 'shadow_first_profitable_close'];
                }
            }
            $last = $pathBars[count($pathBars)];
            return ['date' => $last['date'], 'price' => $last['close'], 'offset' => count($pathBars), 'reason' => 'shadow_no_profitable_close_hold'];
        }
        if ($family === 'profit_lock') {
            $threshold = (float) ($profile['mfe_threshold'] ?? 0.01);
            $lockFraction = (float) ($profile['lock_fraction'] ?? 0.50);
            $armed = false;
            $lockPrice = null;
            foreach ($pathBars as $day => $bar) {
                if (! $armed && (($bar['high'] - $entryPrice) / $entryPrice) >= $threshold) {
                    $armed = true;
                    $lockPrice = $this->normalizeTargetTriggerPrice($entryPrice * (1 + ($threshold * $lockFraction)));
                }
                if ($armed && $lockPrice !== null && ($bar['open'] <= $lockPrice || $bar['low'] <= $lockPrice)) {
                    return ['date' => $bar['date'], 'price' => $bar['open'] <= $lockPrice ? $bar['open'] : $lockPrice, 'offset' => $day, 'reason' => 'shadow_profit_lock'];
                }
            }
            $last = $pathBars[count($pathBars)];
            return ['date' => $last['date'], 'price' => $last['close'], 'offset' => count($pathBars), 'reason' => 'shadow_profit_lock_untriggered_hold'];
        }
        if ($family === 'breakeven') {
            $threshold = (float) ($profile['mfe_threshold'] ?? 0.01);
            $armed = false;
            foreach ($pathBars as $day => $bar) {
                if (! $armed && (($bar['high'] - $entryPrice) / $entryPrice) >= $threshold) {
                    $armed = true;
                }
                if ($armed && ($bar['open'] <= $entryPrice || $bar['low'] <= $entryPrice)) {
                    return ['date' => $bar['date'], 'price' => $bar['open'] <= $entryPrice ? $bar['open'] : $entryPrice, 'offset' => $day, 'reason' => 'shadow_breakeven_stop'];
                }
            }
            $last = $pathBars[count($pathBars)];
            return ['date' => $last['date'], 'price' => $last['close'], 'offset' => count($pathBars), 'reason' => 'shadow_breakeven_untriggered_hold'];
        }
        if ($family === 'trailing') {
            $threshold = (float) ($profile['mfe_threshold'] ?? 0.015);
            $giveback = (float) ($profile['giveback'] ?? 0.0075);
            $peakHigh = $entryPrice;
            $armed = false;
            foreach ($pathBars as $day => $bar) {
                $peakHigh = max($peakHigh, $bar['high']);
                $peakRet = ($peakHigh - $entryPrice) / $entryPrice;
                if (! $armed && $peakRet >= $threshold) {
                    $armed = true;
                }
                if ($armed) {
                    $trail = $this->normalizeTargetTriggerPrice($entryPrice * (1 + max(0, $peakRet - $giveback)));
                    if ($trail !== null && ($bar['open'] <= $trail || $bar['low'] <= $trail)) {
                        return ['date' => $bar['date'], 'price' => $bar['open'] <= $trail ? $bar['open'] : $trail, 'offset' => $day, 'reason' => 'shadow_trailing_profit_protection'];
                    }
                }
            }
            $last = $pathBars[count($pathBars)];
            return ['date' => $last['date'], 'price' => $last['close'], 'offset' => count($pathBars), 'reason' => 'shadow_trailing_untriggered_hold'];
        }
        if ($family === 'target_close') {
            $targetPct = (float) ($profile['target_pct'] ?? 0.01);
            $target = $entryPrice * (1 + $targetPct);
            foreach ($pathBars as $day => $bar) {
                if ($bar['close'] >= $target) {
                    return ['date' => $bar['date'], 'price' => $bar['close'], 'offset' => $day, 'reason' => 'shadow_target_close'];
                }
            }
            $last = $pathBars[count($pathBars)];
            return ['date' => $last['date'], 'price' => $last['close'], 'offset' => count($pathBars), 'reason' => 'shadow_target_close_unreached_hold'];
        }
        if ($family === 'stop_loss') {
            $stopPct = (float) ($profile['stop_pct'] ?? 0.02);
            $stop = $this->normalizeStopTriggerPrice($entryPrice * (1 - $stopPct));
            foreach ($pathBars as $day => $bar) {
                if ($stop !== null && ($bar['open'] <= $stop || $bar['low'] <= $stop)) {
                    return ['date' => $bar['date'], 'price' => $bar['open'] <= $stop ? $bar['open'] : $stop, 'offset' => $day, 'reason' => 'shadow_fixed_stop_loss'];
                }
            }
            $last = $pathBars[count($pathBars)];
            return ['date' => $last['date'], 'price' => $last['close'], 'offset' => count($pathBars), 'reason' => 'shadow_stop_loss_untriggered_hold'];
        }

        $last = $pathBars[count($pathBars)];
        return ['date' => $last['date'], 'price' => $last['close'], 'offset' => count($pathBars), 'reason' => 'shadow_unknown_hold'];
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
                'reason_codes' => ['WS_C22_FIXED_RECOMMENDATION_BEFORE_PATH_READ', 'WS_REC_SELECTED'],
                'contract_flags' => [
                    'recommendation_frozen_before_price_read' => true,
                    'future_path_price_used_for_measurement_only' => true,
                    'future_path_price_used_for_selection' => false,
                    'shadow_ret_net_used_for_selection' => false,
                    'mfe_mae_used_for_selection' => false,
                    'c22_diagnostic_only' => true,
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
            $notes[] = 'No complete D+1 to D+5 OHLC path could be evaluated; C22 must remain blocked or path-missing only, never inferred.';
        }
        $selectionItemsAvailable = false;
        foreach (($selectionArtifact['diagnostics'] ?? []) as $diag) {
            if (is_array($diag) && array_filter($diag['proposed_path']['selected_items'] ?? [], 'is_array') !== []) {
                $selectionItemsAvailable = true;
                break;
            }
        }
        return [
            'entry_close_available' => $evaluated !== [],
            'next_open_available' => $evaluated !== [],
            'd1_to_d5_ohlc_available' => $evaluated !== [],
            'canonical_stop_target_available' => count(array_filter($evaluated, function (array $row): bool { return $row['stop_trigger_price'] !== null && $row['target_trigger_price'] !== null; })) > 0,
            'canonical_exit_reason_available' => $evaluated !== [],
            'c21_path_recomputable' => $evaluated !== [],
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

    private function shadowProfileSummary(array $rows, array $canonicalSummary): array
    {
        $byProfile = [];
        foreach ($rows as $row) {
            $code = (string) ($row['shadow_profile_code'] ?? 'UNKNOWN');
            $byProfile[$code][] = $row;
        }
        ksort($byProfile, SORT_STRING);
        $out = [];
        $canonicalGivebackRate = $this->num($canonicalSummary['canonical_gave_back_profit_rate'] ?? null);
        foreach ($byProfile as $code => $profileRows) {
            $evaluated = $this->evaluatedShadowRows($profileRows);
            $returns = $this->values($evaluated, 'shadow_ret_net');
            $deltas = $this->values($evaluated, 'shadow_ret_delta_vs_canonical');
            $givebackCount = count(array_filter($evaluated, function (array $row): bool { return $this->shadowGaveBackFlag($row, $this->num($row['shadow_ret_net'] ?? null)); }));
            $improvedCount = count(array_filter($evaluated, function (array $row): bool { return ($row['shadow_ret_delta_vs_canonical'] ?? 0) > 0.0000001; }));
            $worsenedCount = count(array_filter($evaluated, function (array $row): bool { return ($row['shadow_ret_delta_vs_canonical'] ?? 0) < -0.0000001; }));
            $unchangedCount = max(0, count($evaluated) - $improvedCount - $worsenedCount);
            $givebackRate = count($evaluated) > 0 ? $givebackCount / count($evaluated) : null;
            $out[] = [
                'profile_code' => $code,
                'profile_family' => self::shadowProfiles()[$code]['family'] ?? 'unknown',
                'evaluated_picks_count' => count($evaluated),
                'avg_ret_net' => $this->avg($returns),
                'median_ret_net' => $this->median($returns),
                'p25_ret_net' => $this->percentile($returns, 0.25),
                'win_rate' => count($returns) > 0 ? count(array_filter($returns, function (float $v): bool { return $v > 0; })) / count($returns) : null,
                'avg_delta_vs_canonical' => $this->avg($deltas),
                'median_delta_vs_canonical' => $this->median($deltas),
                'p25_delta_vs_canonical' => $this->percentile($deltas, 0.25),
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
                'exit_day_offset_distribution' => $this->distribution($evaluated, 'shadow_exit_day_offset', 'NONE'),
                'exit_reason_distribution' => $this->distribution($evaluated, 'shadow_exit_reason', 'NONE'),
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

    private function decision(array $canonical, array $profiles, array $hold, array $profitLock, array $breakeven, array $trailing, array $target, array $stop): array
    {
        if ((int) ($canonical['evaluated_picks_count'] ?? 0) === 0) {
            return [
                'decision_status' => 'C22_DIAGNOSTIC_BLOCKED',
                'catalog_allowed' => false,
                'oos_allowed' => false,
                'next_step' => 'Fix D+1 to D+5 path data availability before deciding any exit-capture direction.',
                'exit_capture_signal_found' => false,
                'early_exit_suspected_better' => false,
                'profit_lock_suspected_better' => false,
                'breakeven_suspected_better' => false,
                'trailing_suspected_better' => false,
                'target_distance_problem_suspected' => false,
                'stop_distance_problem_suspected' => false,
                'hold_compression_suspected_better' => false,
            ];
        }

        $bestMedian = $this->bestByMetric($profiles, 'median_delta_vs_canonical');
        $bestP25 = $this->bestByMetric($profiles, 'p25_delta_vs_canonical');
        $bestGiveback = $this->bestByMetric($profiles, 'gave_back_profit_reduction_vs_canonical');
        $bestImprovedRate = $this->bestByMetric($profiles, 'improved_pick_rate');
        $bestLossReduction = $this->bestByMetric($profiles, 'loss_reduction_rate');

        $exitCapture = (($bestMedian['median_delta_vs_canonical'] ?? null) !== null && $bestMedian['median_delta_vs_canonical'] >= 0.005)
            || (($bestP25['p25_delta_vs_canonical'] ?? null) !== null && $bestP25['p25_delta_vs_canonical'] >= 0.005)
            || (($bestGiveback['gave_back_profit_reduction_vs_canonical'] ?? null) !== null && $bestGiveback['gave_back_profit_reduction_vs_canonical'] >= 0.15)
            || (($bestImprovedRate['improved_pick_rate'] ?? null) !== null && $bestImprovedRate['improved_pick_rate'] >= 0.55 && (($bestImprovedRate['p25_delta_vs_canonical'] ?? 0) > -0.005))
            || (($bestLossReduction['loss_reduction_rate'] ?? null) !== null && $bestLossReduction['loss_reduction_rate'] >= 0.20);

        $holdBest = $hold['best_by_median_delta'] ?? [];
        $earlyExit = $this->familyHasMedianP25AndGivebackImprovement($hold);
        $profitLockBetter = $this->familyHasGivebackAndDistributionImprovement($profitLock, 0.15);
        $breakevenBetter = $this->familyHasP25OrLossWithoutMedianDamage($breakeven);
        $trailingBetter = $this->familyHasGivebackAndDistributionImprovement($trailing, 0.10);
        $targetProblem = $this->familyHasMedianOrWinAndGivebackImprovement($target);
        $stopProblem = $this->familyHasP25OrLossWithoutMedianDamage($stop);
        $holdCompression = $earlyExit || (($holdBest['median_delta_vs_canonical'] ?? null) !== null && $holdBest['median_delta_vs_canonical'] >= 0.005);

        return [
            'decision_status' => $exitCapture ? 'C22_EXIT_CAPTURE_SIGNAL_FOUND' : 'C22_EXIT_CAPTURE_SIGNAL_NOT_FOUND',
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'next_step' => $exitCapture
                ? 'Continue to C23 rule-candidate design with catalog and OOS still forbidden until a non-lookahead rule is specified and tested.'
                : 'Stop exit-capture hypothesis for this catalog path unless a richer non-lookahead diagnostic is justified; do not create catalog or run OOS.',
            'exit_capture_signal_found' => $exitCapture,
            'early_exit_suspected_better' => $earlyExit,
            'profit_lock_suspected_better' => $profitLockBetter,
            'breakeven_suspected_better' => $breakevenBetter,
            'trailing_suspected_better' => $trailingBetter,
            'target_distance_problem_suspected' => $targetProblem,
            'stop_distance_problem_suspected' => $stopProblem,
            'hold_compression_suspected_better' => $holdCompression,
            'best_shadow_profile_code_by_avg' => ($this->bestByMetric($profiles, 'avg_delta_vs_canonical')['profile_code'] ?? null),
            'best_shadow_profile_code_by_median' => $bestMedian['profile_code'] ?? null,
            'best_shadow_profile_code_by_p25' => $bestP25['profile_code'] ?? null,
            'best_shadow_profile_code_by_win_rate' => ($this->bestByMetric($profiles, 'win_rate')['profile_code'] ?? null),
            'best_shadow_profile_code_by_giveback_reduction' => $bestGiveback['profile_code'] ?? null,
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

    private function shadowGaveBackFlag(array $row, ?float $retNet): bool
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

    private function evaluatedShadowRows(array $rows): array
    {
        return array_values(array_filter($rows, function (array $row): bool {
            return ($row['missing_path_data_flag'] ?? true) === false && is_numeric($row['shadow_ret_net'] ?? null);
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

    private function selectedShadowProfileDefinitions(array $profiles): array
    {
        $defs = [];
        foreach ($profiles as $code) {
            $defs[] = array_merge(['profile_code' => $code], self::shadowProfiles()[$code] ?? []);
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
            'shadow_exit_capture_analysis_only' => true,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C22_CATALOG_CODE' => 'NOT_CREATED',
            'C22_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_TICKER_BLACKLIST' => true,
            'NO_MONTH_BLACKLIST' => true,
            'NO_SECTOR_WHITELIST' => true,
            'NO_BEST_OF_FAILED_BINDING' => true,
            'NO_C01_TO_C21_MUTATION' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'NO_C19_REOPEN' => true,
            'NO_C20_REOPEN' => true,
            'NO_C21_REOPEN' => true,
            'canonical_evaluation_model_unchanged' => $this->canonicalEvaluationModel(),
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'future_path_price_used_for_measurement_only' => true,
            'future_path_price_used_for_selection' => false,
            'shadow_exit_used_for_selection' => false,
            'shadow_ret_net_used_for_selection' => false,
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

    private function shadowProfileCodes($value)
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
        $known = self::shadowProfiles();
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
            'c22_catalog_implementation_deferred' => 1,
            'c22_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }
}
