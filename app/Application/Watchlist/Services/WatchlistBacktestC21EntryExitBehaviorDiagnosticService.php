<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketBenchmarkReadService;
use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC21EntryExitBehaviorDiagnosticService
{
    public const ARTIFACT_TYPE = 'C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-run-1.json';
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

    public static function diagnosticProfiles(): array
    {
        return [
            'C21_P00_CANONICAL_PATH_BASELINE' => [
                'description' => 'Canonical ENTRY=NEXT_OPEN, EXIT=STOP_TP_OR_TIME, HOLD=5 path baseline over fixed C19 recommendations.',
                'analysis_family' => 'canonical_path',
            ],
            'C21_P01_ENTRY_GAP_BUCKET_ANALYSIS' => [
                'description' => 'Entry gap buckets from signal close to next open; diagnostic only, never a selection filter.',
                'analysis_family' => 'entry_gap',
            ],
            'C21_P02_MFE_MAE_PATH_ANALYSIS' => [
                'description' => 'MFE/MAE D+1 through D+5 path behavior after fixed recommendation freeze.',
                'analysis_family' => 'mfe_mae',
            ],
            'C21_P03_EXIT_REASON_DISTRIBUTION' => [
                'description' => 'Canonical stop/target/hold exit reason distribution and concentration.',
                'analysis_family' => 'exit_reason',
            ],
            'C21_P04_HOLD_DAY_RETURN_PATH' => [
                'description' => 'D+1 through D+5 close-return path distribution to test hold-period fit.',
                'analysis_family' => 'hold_path',
            ],
            'C21_P05_GAVE_BACK_PROFIT_ANALYSIS' => [
                'description' => 'Trades with positive excursion before weak final canonical return.',
                'analysis_family' => 'gave_back_profit',
            ],
            'C21_P06_NEVER_PROFITABLE_ANALYSIS' => [
                'description' => 'Trades that never show positive favorable excursion during the canonical holding window.',
                'analysis_family' => 'never_profitable',
            ],
            'C21_P07_C20_G03_SEGMENTED_PATH_ANALYSIS' => [
                'description' => 'C20 G03 volatility/risk-off context as explanation-only segmentation, not a decision filter.',
                'analysis_family' => 'c20_g03_segmentation',
            ],
        ];
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode) !== '' ? trim($catalogCode) : self::DEFAULT_SOURCE_CATALOG_CODE;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C21_IS_ONLY_WINDOW_MISMATCH', 'C21 entry/exit behavior diagnostic requires the frozen IS window only.');
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.');
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C21_PARAM_IDS_INVALID', 'param_ids must be a comma-separated list of positive integers.');
        }
        $profiles = $this->profileCodes($options['profiles'] ?? null);
        if ($profiles === false) {
            return $this->blocked('WS_BT_C21_PROFILE_INVALID', 'profile-codes/profiles must be a comma-separated list of known C21 profile codes.');
        }
        $profileScope = 'EXPLICIT';
        if ($profiles === []) {
            $profiles = [
                'C21_P00_CANONICAL_PATH_BASELINE',
                'C21_P01_ENTRY_GAP_BUCKET_ANALYSIS',
                'C21_P02_MFE_MAE_PATH_ANALYSIS',
                'C21_P03_EXIT_REASON_DISTRIBUTION',
            ];
            $profileScope = 'FAST_DEFAULT';
        }
        $maxProfiles = $this->positiveIntOrNull($options['max_profiles'] ?? null);
        if ($maxProfiles !== null) {
            $profiles = array_slice($profiles, 0, $maxProfiles);
            $profileScope .= '_MAX_'.$maxProfiles;
        }
        if ($profiles === []) {
            return $this->blocked('WS_BT_C21_PROFILE_EMPTY', 'No C21 diagnostic profile is selected.');
        }

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar is unavailable for C21 diagnostic.', [
                'calendar' => $calendar,
            ]);
        }
        $tradeDates = $this->normalizeDateList($calendar['trade_dates'] ?? []);
        $calendarDates = $this->normalizeDateList($calendar['calendar_dates'] ?? $calendar['trade_dates'] ?? []);
        if ($tradeDates === [] || $calendarDates === []) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar returned no usable dates.');
        }

        try {
            $rows = $this->paramGrid->allForCatalog($catalogCode, 'WS');
        } catch (\Throwable $e) {
            return $this->blocked('WS_BT_C21_SOURCE_CATALOG_UNAVAILABLE', $e->getMessage());
        }
        if ($paramIds !== []) {
            $allowed = array_fill_keys($paramIds, true);
            $rows = array_values(array_filter($rows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_C21_ROW_FILTER_NO_MATCH', 'No source catalog rows matched the explicit param filter.');
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
            return $this->blocked($selectionResult['reason_code'] ?? 'WS_BT_C21_SELECTION_SOURCE_NOT_READY', 'C19 selection diagnostic source did not produce a PASS artifact.', [
                'selection_result' => $selectionResult,
            ]);
        }
        $selectionArtifact = $this->readJson($selectionOutput);
        if ($selectionArtifact === null) {
            return $this->blocked('WS_BT_C21_SELECTION_ARTIFACT_UNREADABLE', 'C19 selection artifact could not be read.');
        }

        $executedAt = (string) ($options['executed_at'] ?? ($toDate.'T23:59:59+07:00'));
        $progress = $options['progress_callback'] ?? null;
        $maxPicks = $this->positiveIntOrNull($options['max_picks'] ?? null);
        $profileRows = [];
        $allPickRows = [];
        $profileIndex = 0;

        foreach ($profiles as $profileCode) {
            $profileIndex++;
            if (is_callable($progress)) {
                $progress('[C21] profile '.$profileIndex.'/'.count($profiles).': '.$profileCode);
            }
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
                $profileResult = $this->diagnoseProfileParam(
                    $profileCode,
                    $selectionDiag,
                    $row,
                    $paramset,
                    $tradeDates,
                    $calendarDates,
                    $fromDate,
                    $toDate,
                    $maxPicks
                );
                $profileRows[] = $profileResult['summary'];
                foreach ($profileResult['pick_path_rows'] as $pickRow) {
                    $allPickRows[] = $pickRow;
                }
            }
        }

        $pathSummary = $this->pathSummary($allPickRows);
        $entryGapSummary = $this->entryGapSummary($allPickRows);
        $mfeMaeSummary = $this->mfeMaeSummary($allPickRows);
        $exitReasonSummary = $this->exitReasonSummary($allPickRows);
        $holdDayReturnSummary = $this->holdDayReturnSummary($allPickRows);
        $gaveBackProfitSummary = $this->flagSummary($allPickRows, 'gave_back_profit_flag', 'gave_back_profit');
        $neverProfitableSummary = $this->flagSummary($allPickRows, 'never_profitable_flag', 'never_profitable');
        $c20G03SegmentSummary = $this->c20G03SegmentSummary($allPickRows);
        $dataAvailability = $this->dataAvailability($selectionArtifact, $allPickRows, $calendarDates);
        $decision = $this->decision($pathSummary, $entryGapSummary, $mfeMaeSummary, $exitReasonSummary, $holdDayReturnSummary, $c20G03SegmentSummary);
        $status = $decision['decision_status'] === 'C21_DIAGNOSTIC_BLOCKED' ? 'BLOCKED' : 'PASS';

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => $status,
            'reason_code' => 'WS_BT_C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC',
            'generated_at' => $executedAt,
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'policy_code' => 'WS',
                'row_count' => count($rows),
                'max_params' => $maxParams,
                'max_picks' => $maxPicks,
                'source_selection' => 'C19_FIXED_PROPOSED_SELECTION_ITEMS',
            ],
            'source_evidence' => [
                'c19_final_status' => 'C19_CATALOG_CANDIDATE_FAILED',
                'c20_final_status' => 'C20_DATE_GATE_NOT_ENOUGH',
                'c20_all_param_7_profile_artifact_hash' => '8f8eec9913c107f22ec1f395eed9386da41756c0',
                'selection_source_artifact_path' => $selectionOutput,
                'selection_source_artifact_hash' => $selectionArtifact['artifact_hash'] ?? ($selectionResult['artifact_hash'] ?? null),
            ],
            'is_window' => [
                'from' => $fromDate,
                'to' => $toDate,
                'trade_date_count' => count($tradeDates),
                'boundary_censoring_rule' => 'EXCLUDE_LAST_HOLDING_DAYS_FROM_ENTRY_GENERATION;KEEP_ALL_PATH_PRICE_READS_WITHIN_IS',
            ],
            'price_evaluation_model' => $this->canonicalEvaluationModel(),
            'data_availability' => $dataAvailability,
            'diagnostic_profiles' => $this->selectedProfileDefinitions($profiles),
            'profile_scope' => $profileScope,
            'profile_summaries' => $profileRows,
            'pick_path_rows' => $allPickRows,
            'path_summary' => $pathSummary,
            'entry_gap_summary' => $entryGapSummary,
            'mfe_mae_summary' => $mfeMaeSummary,
            'exit_reason_summary' => $exitReasonSummary,
            'hold_day_return_summary' => $holdDayReturnSummary,
            'gave_back_profit_summary' => $gaveBackProfitSummary,
            'never_profitable_summary' => $neverProfitableSummary,
            'c20_g03_segment_summary' => $c20G03SegmentSummary,
            'decision' => $decision,
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message']);
        }

        return [
            'status' => $status,
            'reason_code' => 'WS_BT_C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'profile_count' => count($profiles),
            'profile_scope' => $profileScope,
            'evaluated_picks_count' => (int) ($pathSummary['evaluated_picks_count'] ?? 0),
            'path_missing_count' => (int) ($pathSummary['path_missing_count'] ?? 0),
            'avg_entry_gap_pct' => $entryGapSummary['avg_entry_gap_pct'] ?? null,
            'median_entry_gap_pct' => $entryGapSummary['median_entry_gap_pct'] ?? null,
            'never_profitable_rate' => $neverProfitableSummary['never_profitable_rate'] ?? null,
            'gave_back_profit_rate' => $gaveBackProfitSummary['gave_back_profit_rate'] ?? null,
            'gap_open_loss_rate' => $entryGapSummary['gap_open_loss_rate'] ?? null,
            'exit_stop_count' => (int) ($exitReasonSummary['exit_stop_count'] ?? 0),
            'exit_target_count' => (int) ($exitReasonSummary['exit_target_count'] ?? 0),
            'exit_hold_count' => (int) ($exitReasonSummary['exit_hold_count'] ?? 0),
            'median_mfe_5d' => $mfeMaeSummary['median_mfe_5d'] ?? null,
            'median_mae_5d' => $mfeMaeSummary['median_mae_5d'] ?? null,
            'diagnostic_signal_found' => $decision['diagnostic_signal_found'] ? 1 : 0,
            'entry_problem_suspected' => $decision['entry_problem_suspected'] ? 1 : 0,
            'exit_problem_suspected' => $decision['exit_problem_suspected'] ? 1 : 0,
            'stop_problem_suspected' => $decision['stop_problem_suspected'] ? 1 : 0,
            'hold_period_problem_suspected' => $decision['hold_period_problem_suspected'] ? 1 : 0,
            'regime_explains_execution_problem' => $decision['regime_explains_execution_problem'] ? 1 : 0,
            'c21_catalog_implementation_deferred' => 1,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function diagnoseProfileParam(
        string $profileCode,
        array $selectionDiagnostic,
        array $row,
        array $paramset,
        array $tradeDates,
        array $calendarDates,
        string $fromDate,
        string $toDate,
        ?int $maxPicks
    ): array {
        $baseItems = array_values(array_filter($selectionDiagnostic['proposed_path']['selected_items'] ?? [], 'is_array'));
        $holdingDays = max(1, (int) ($paramset['backtest']['holding_days'] ?? 5));
        $strategyTradeDates = count($tradeDates) > $holdingDays ? array_slice($tradeDates, 0, count($tradeDates) - $holdingDays) : [];
        $items = array_values(array_filter($baseItems, function (array $item) use ($strategyTradeDates): bool {
            return in_array((string) ($item['trade_date'] ?? ''), $strategyTradeDates, true);
        }));
        $items = $this->sortItems($items);
        if ($maxPicks !== null) {
            $items = array_slice($items, 0, $maxPicks);
        }

        $trades = $this->proposalItemsToTrades($items, $paramset, $profileCode);
        $requiredMap = $this->requiredPriceTickerMap($trades, $calendarDates, $holdingDays, true);
        $requiredDates = array_keys($requiredMap);
        $priceFromDate = $requiredDates[0] ?? $fromDate;
        $priceToDate = $requiredDates === [] ? $toDate : $requiredDates[count($requiredDates) - 1];
        $priceRead = $requiredMap === []
            ? $this->emptyPriceRead($priceFromDate, $priceToDate)
            : $this->priceSeries->readPublishedSeriesForDateTickerMap($priceFromDate, $priceToDate, $requiredMap);
        $series = is_array($priceRead['series_by_ticker'] ?? null) ? $priceRead['series_by_ticker'] : [];

        $pickRows = [];
        foreach ($trades as $trade) {
            $pickRows[] = $this->pathRow($trade, $row, $paramset, $series, $calendarDates, $holdingDays, $profileCode);
        }
        $summary = array_merge($this->pathSummary($pickRows), [
            'profile_code' => $profileCode,
            'profile_description' => self::diagnosticProfiles()[$profileCode]['description'] ?? '',
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
            'c20_g03_used_as_filter' => false,
        ]);

        return [
            'summary' => $summary,
            'pick_path_rows' => $pickRows,
        ];
    }

    private function pathRow(array $trade, array $row, array $paramset, array $series, array $calendarDates, int $holdingDays, string $profileCode): array
    {
        $ticker = (string) ($trade['ticker'] ?? '');
        $tradeDate = (string) ($trade['trade_date'] ?? '');
        $entryDate = $this->nextTradingDate($tradeDate, $calendarDates);
        $signalBar = $this->publishedBar($series, $ticker, $tradeDate);
        $entryBar = $entryDate !== null ? $this->publishedBar($series, $ticker, $entryDate) : null;
        $pathDates = $entryDate !== null ? $this->tradingWindowFrom($entryDate, $calendarDates, $holdingDays) : [];
        $base = [
            'diagnostic_profile_code' => $profileCode,
            'trade_date' => $tradeDate,
            'ticker_id' => $trade['ticker_id'] ?? null,
            'ticker' => $ticker,
            'param_id' => (int) ($row['param_id'] ?? 0),
            'row_code' => (string) ($row['row_code'] ?? ''),
            'entry_date' => $entryDate,
            'entry_price' => null,
            'signal_close_price' => null,
            'entry_gap_pct' => null,
            'exit_date' => null,
            'exit_price' => null,
            'exit_reason' => null,
            'exit_day_offset' => null,
            'ret_net' => null,
            'd1_close_ret' => null,
            'd2_close_ret' => null,
            'd3_close_ret' => null,
            'd4_close_ret' => null,
            'd5_close_ret' => null,
            'mfe_1d' => null,
            'mfe_2d' => null,
            'mfe_3d' => null,
            'mfe_4d' => null,
            'mfe_5d' => null,
            'mae_1d' => null,
            'mae_2d' => null,
            'mae_3d' => null,
            'mae_4d' => null,
            'mae_5d' => null,
            'max_favorable_excursion_pct' => null,
            'max_adverse_excursion_pct' => null,
            'first_profitable_day' => null,
            'first_stop_touch_day' => null,
            'first_target_touch_day' => null,
            'gave_back_profit_flag' => false,
            'never_profitable_flag' => false,
            'gap_open_loss_flag' => false,
            'stop_before_target_flag' => false,
            'target_before_stop_flag' => false,
            'missing_path_data_flag' => true,
            'missing_path_reason_code' => null,
            'c20_g03_context' => $this->c20G03Context($trade),
        ];

        if ($ticker === '' || $tradeDate === '' || $entryDate === null || $signalBar === null || $entryBar === null || count($pathDates) < $holdingDays) {
            $base['missing_path_reason_code'] = 'WS_BT_C21_REQUIRED_ENTRY_OR_SIGNAL_PATH_MISSING';
            return $base;
        }
        $signalClose = $this->num($signalBar['close'] ?? null);
        $entryPrice = $this->num($entryBar['open'] ?? null);
        if ($signalClose === null || $signalClose <= 0 || $entryPrice === null || $entryPrice <= 0) {
            $base['missing_path_reason_code'] = 'WS_BT_C21_REQUIRED_ENTRY_OR_SIGNAL_OHLC_MISSING';
            return $base;
        }
        foreach ($pathDates as $date) {
            $bar = $this->publishedBar($series, $ticker, $date);
            if ($bar === null || ! $this->tradableBar($bar, $paramset)) {
                $base['missing_path_reason_code'] = 'WS_BT_C21_D1_TO_D5_OHLC_PATH_MISSING';
                return $base;
            }
            foreach (['open', 'high', 'low', 'close'] as $field) {
                $value = $this->num($bar[$field] ?? null);
                if ($value === null || $value <= 0) {
                    $base['missing_path_reason_code'] = 'WS_BT_C21_D1_TO_D5_OHLC_PATH_MISSING';
                    return $base;
                }
            }
        }

        $levels = $this->targetStopLevels($entryPrice, $trade, $paramset);
        $firstProfitable = null;
        $firstStop = null;
        $firstTarget = null;
        $maxHigh = null;
        $minLow = null;
        $exitDate = null;
        $exitPrice = null;
        $exitReason = null;
        $exitOffset = null;
        $dailyCloseReturns = [];
        $mfe = [];
        $mae = [];

        foreach ($pathDates as $index => $date) {
            $offset = $index + 1;
            $bar = $this->publishedBar($series, $ticker, $date);
            $open = (float) $bar['open'];
            $high = (float) $bar['high'];
            $low = (float) $bar['low'];
            $close = (float) $bar['close'];
            $maxHigh = $maxHigh === null ? $high : max($maxHigh, $high);
            $minLow = $minLow === null ? $low : min($minLow, $low);
            $dailyCloseReturns[$offset] = ($close - $entryPrice) / $entryPrice;
            $mfe[$offset] = ($maxHigh - $entryPrice) / $entryPrice;
            $mae[$offset] = ($minLow - $entryPrice) / $entryPrice;
            if ($firstProfitable === null && $close > $entryPrice) {
                $firstProfitable = $offset;
            }
            if (($levels['has_target_stop'] ?? false) === true) {
                $stop = (float) $levels['stop_trigger_price'];
                $target = (float) $levels['target_trigger_price'];
                if ($firstStop === null && ($open <= $stop || $low <= $stop)) {
                    $firstStop = $offset;
                }
                if ($firstTarget === null && ($open >= $target || $high >= $target)) {
                    $firstTarget = $offset;
                }
                if ($exitReason === null) {
                    if ($open <= $stop) {
                        $exitPrice = $open;
                        $exitReason = 'exit_stop';
                        $exitDate = $date;
                        $exitOffset = $offset;
                    } elseif ($open >= $target) {
                        $exitPrice = $open;
                        $exitReason = 'exit_target';
                        $exitDate = $date;
                        $exitOffset = $offset;
                    } elseif ($low <= $stop) {
                        $exitPrice = $stop;
                        $exitReason = 'exit_stop';
                        $exitDate = $date;
                        $exitOffset = $offset;
                    } elseif ($high >= $target) {
                        $exitPrice = $target;
                        $exitReason = 'exit_target';
                        $exitDate = $date;
                        $exitOffset = $offset;
                    }
                }
            }
        }

        if ($exitReason === null) {
            $lastDate = $pathDates[count($pathDates) - 1];
            $lastBar = $this->publishedBar($series, $ticker, $lastDate);
            $exitDate = $lastDate;
            $exitPrice = (float) $lastBar['close'];
            $exitReason = 'exit_hold';
            $exitOffset = count($pathDates);
        }

        $retNet = $this->retNet($entryPrice, (float) $exitPrice, $paramset);
        $maxMfe = $mfe === [] ? null : max($mfe);
        $maxMae = $mae === [] ? null : min($mae);
        $gaveBack = $maxMfe !== null && $maxMfe > 0.005 && ($retNet <= 0 || ($maxMfe - $retNet) >= 0.010);
        $neverProfitable = $maxMfe !== null && $maxMfe <= 0;
        $entryGap = ($entryPrice - $signalClose) / $signalClose;

        return array_merge($base, [
            'entry_price' => $entryPrice,
            'signal_close_price' => $signalClose,
            'entry_gap_pct' => $entryGap,
            'exit_date' => $exitDate,
            'exit_price' => $exitPrice,
            'exit_reason' => $exitReason,
            'exit_day_offset' => $exitOffset,
            'ret_net' => $retNet,
            'd1_close_ret' => $dailyCloseReturns[1] ?? null,
            'd2_close_ret' => $dailyCloseReturns[2] ?? null,
            'd3_close_ret' => $dailyCloseReturns[3] ?? null,
            'd4_close_ret' => $dailyCloseReturns[4] ?? null,
            'd5_close_ret' => $dailyCloseReturns[5] ?? null,
            'mfe_1d' => $mfe[1] ?? null,
            'mfe_2d' => $mfe[2] ?? null,
            'mfe_3d' => $mfe[3] ?? null,
            'mfe_4d' => $mfe[4] ?? null,
            'mfe_5d' => $mfe[5] ?? null,
            'mae_1d' => $mae[1] ?? null,
            'mae_2d' => $mae[2] ?? null,
            'mae_3d' => $mae[3] ?? null,
            'mae_4d' => $mae[4] ?? null,
            'mae_5d' => $mae[5] ?? null,
            'max_favorable_excursion_pct' => $maxMfe,
            'max_adverse_excursion_pct' => $maxMae,
            'first_profitable_day' => $firstProfitable,
            'first_stop_touch_day' => $firstStop,
            'first_target_touch_day' => $firstTarget,
            'gave_back_profit_flag' => $gaveBack,
            'never_profitable_flag' => $neverProfitable,
            'gap_open_loss_flag' => $entryGap < 0,
            'stop_before_target_flag' => $firstStop !== null && ($firstTarget === null || $firstStop <= $firstTarget),
            'target_before_stop_flag' => $firstTarget !== null && ($firstStop === null || $firstTarget < $firstStop),
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
            'stop_price' => $levels['stop_price'] ?? null,
            'target_price' => $levels['target_price'] ?? null,
            'stop_trigger_price' => $levels['stop_trigger_price'] ?? null,
            'target_trigger_price' => $levels['target_trigger_price'] ?? null,
        ]);
    }

    private function proposalItemsToTrades(array $items, array $paramset, string $profileCode): array
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
                'stop_price' => null,
                'target_price' => null,
                'stop_atr_mult' => $this->num($paramset['risk']['stop_atr_mult'] ?? null),
                'min_rr' => $this->num($paramset['risk']['min_rr'] ?? null),
                'reason_codes' => ['WS_C21_FIXED_RECOMMENDATION_BEFORE_PATH_READ', 'WS_REC_SELECTED'],
                'source_reference' => [
                    'diagnostic_source' => 'C21_ENTRY_EXIT_BEHAVIOR_ON_C19_SELECTION_BASE',
                    'proposed_plan_group' => (string) ($item['proposed_plan_group'] ?? ''),
                    'penalty_total' => $this->num($item['penalty_total'] ?? null),
                    'c21_profile_code' => $profileCode,
                    'future_path_price_used_for_selection' => false,
                ],
                'contract_flags' => [
                    'recommendation_frozen_before_price_read' => true,
                    'future_path_price_used_for_measurement_only' => true,
                    'future_path_price_used_for_selection' => false,
                    'c20_g03_used_as_filter' => false,
                    'c21_diagnostic_only' => true,
                ],
            ];
            $rank++;
        }
        return $trades;
    }

    private function dataAvailability(array $selectionArtifact, array $rows, array $calendarDates): array
    {
        $evaluated = array_values(array_filter($rows, function (array $row): bool {
            return ! ($row['missing_path_data_flag'] ?? true);
        }));
        $notes = [];
        if ($rows === []) {
            $notes[] = 'No fixed recommendation rows were available from the C19 selection source for the selected filters.';
        }
        if ($evaluated === []) {
            $notes[] = 'No complete D+1 to D+5 OHLC path could be evaluated; do not infer execution behavior from empty path evidence.';
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
            'stop_target_available' => count(array_filter($evaluated, function (array $row): bool { return $row['stop_trigger_price'] !== null && $row['target_trigger_price'] !== null; })) > 0,
            'exit_reason_available' => $evaluated !== [],
            'c20_regime_segmentation_available' => count(array_filter($rows, function (array $row): bool { return is_array($row['c20_g03_context'] ?? null); })) > 0,
            'fixed_selection_items_available' => $selectionItemsAvailable,
            'calendar_dates_available' => $calendarDates !== [],
            'notes' => $notes,
        ];
    }

    private function pathSummary(array $rows): array
    {
        $evaluated = $this->evaluatedRows($rows);
        $returns = $this->values($evaluated, 'ret_net');
        $neverProfitable = count(array_filter($evaluated, function (array $row): bool { return ($row['never_profitable_flag'] ?? false) === true; }));
        $gaveBackProfit = count(array_filter($evaluated, function (array $row): bool { return ($row['gave_back_profit_flag'] ?? false) === true; }));
        return [
            'evaluated_picks_count' => count($evaluated),
            'price_missing_count' => count($rows) - count($evaluated),
            'path_missing_count' => count($rows) - count($evaluated),
            'never_profitable_count' => $neverProfitable,
            'never_profitable_rate' => count($evaluated) > 0 ? $neverProfitable / count($evaluated) : null,
            'gave_back_profit_count' => $gaveBackProfit,
            'gave_back_profit_rate' => count($evaluated) > 0 ? $gaveBackProfit / count($evaluated) : null,
            'avg_ret_net_top' => $this->avg($returns),
            'median_ret_net_top' => $this->median($returns),
            'p25_ret_net_top' => $this->percentile($returns, 0.25),
            'win_rate_top' => count($returns) > 0 ? count(array_filter($returns, function (float $v): bool { return $v > 0; })) / count($returns) : null,
            'first_profitable_day_distribution' => $this->distribution($evaluated, 'first_profitable_day', 'NONE'),
            'exit_day_offset_distribution' => $this->distribution($evaluated, 'exit_day_offset', 'NONE'),
            'return_by_day_distribution' => $this->returnByDayDistribution($evaluated),
            'exit_reason_distribution' => $this->distribution($evaluated, 'exit_reason', 'NONE'),
        ];
    }

    private function entryGapSummary(array $rows): array
    {
        $evaluated = $this->evaluatedRows($rows);
        $gaps = $this->values($evaluated, 'entry_gap_pct');
        $gapLoss = count(array_filter($evaluated, function (array $row): bool { return ($row['gap_open_loss_flag'] ?? false) === true; }));
        $gapLossRows = array_values(array_filter($evaluated, function (array $row): bool { return ($row['gap_open_loss_flag'] ?? false) === true; }));
        $gapLossReturns = $this->values($gapLossRows, 'ret_net');
        return [
            'avg_entry_gap_pct' => $this->avg($gaps),
            'median_entry_gap_pct' => $this->median($gaps),
            'gap_open_loss_count' => $gapLoss,
            'gap_open_loss_rate' => count($evaluated) > 0 ? $gapLoss / count($evaluated) : null,
            'gap_open_loss_win_rate' => count($gapLossReturns) > 0 ? count(array_filter($gapLossReturns, function (float $v): bool { return $v > 0; })) / count($gapLossReturns) : null,
            'entry_gap_bucket_distribution' => $this->entryGapBuckets($evaluated),
        ];
    }

    private function mfeMaeSummary(array $rows): array
    {
        $evaluated = $this->evaluatedRows($rows);
        $mfe5 = $this->values($evaluated, 'mfe_5d');
        $mae5 = $this->values($evaluated, 'mae_5d');
        $positiveMfe = count(array_filter($evaluated, function (array $row): bool { return ($row['max_favorable_excursion_pct'] ?? 0) > 0; }));
        $maeBreach = count(array_filter($evaluated, function (array $row): bool { return ($row['first_stop_touch_day'] ?? null) !== null; }));
        return [
            'avg_mfe_5d' => $this->avg($mfe5),
            'median_mfe_5d' => $this->median($mfe5),
            'avg_mae_5d' => $this->avg($mae5),
            'median_mae_5d' => $this->median($mae5),
            'mfe_positive_rate' => count($evaluated) > 0 ? $positiveMfe / count($evaluated) : null,
            'mae_breach_rate' => count($evaluated) > 0 ? $maeBreach / count($evaluated) : null,
            'mfe_mae_bucket_distribution' => $this->mfeMaeBuckets($evaluated),
        ];
    }

    private function exitReasonSummary(array $rows): array
    {
        $evaluated = $this->evaluatedRows($rows);
        $stop = count(array_filter($evaluated, function (array $row): bool { return ($row['exit_reason'] ?? '') === 'exit_stop'; }));
        $target = count(array_filter($evaluated, function (array $row): bool { return ($row['exit_reason'] ?? '') === 'exit_target'; }));
        $hold = count(array_filter($evaluated, function (array $row): bool { return ($row['exit_reason'] ?? '') === 'exit_hold'; }));
        $stopBeforeTarget = count(array_filter($evaluated, function (array $row): bool { return ($row['stop_before_target_flag'] ?? false) === true; }));
        $targetBeforeStop = count(array_filter($evaluated, function (array $row): bool { return ($row['target_before_stop_flag'] ?? false) === true; }));
        return [
            'exit_stop_count' => $stop,
            'exit_target_count' => $target,
            'exit_hold_count' => $hold,
            'stop_before_target_count' => $stopBeforeTarget,
            'target_before_stop_count' => $targetBeforeStop,
            'exit_reason_distribution' => $this->distribution($evaluated, 'exit_reason', 'NONE'),
            'stop_first_vs_target_first_distribution' => [
                'stop_before_target' => $stopBeforeTarget,
                'target_before_stop' => $targetBeforeStop,
                'neither' => max(0, count($evaluated) - $stopBeforeTarget - $targetBeforeStop),
            ],
        ];
    }

    private function holdDayReturnSummary(array $rows): array
    {
        $evaluated = $this->evaluatedRows($rows);
        $summary = [];
        for ($day = 1; $day <= 5; $day++) {
            $values = $this->values($evaluated, 'd'.$day.'_close_ret');
            $summary['d'.$day] = [
                'avg_close_ret' => $this->avg($values),
                'median_close_ret' => $this->median($values),
                'positive_rate' => count($values) > 0 ? count(array_filter($values, function (float $v): bool { return $v > 0; })) / count($values) : null,
            ];
        }
        $bestDay = null;
        $bestMedian = null;
        foreach ($summary as $day => $row) {
            if ($row['median_close_ret'] !== null && ($bestMedian === null || $row['median_close_ret'] > $bestMedian)) {
                $bestMedian = $row['median_close_ret'];
                $bestDay = $day;
            }
        }
        $summary['best_median_close_return_day'] = $bestDay;
        $summary['mfe_peak_before_d5_rate'] = $this->mfePeakBeforeD5Rate($evaluated);
        return $summary;
    }

    private function flagSummary(array $rows, string $flagKey, string $baseName): array
    {
        $evaluated = $this->evaluatedRows($rows);
        $count = count(array_filter($evaluated, function (array $row) use ($flagKey): bool { return ($row[$flagKey] ?? false) === true; }));
        return [
            $baseName.'_count' => $count,
            $baseName.'_rate' => count($evaluated) > 0 ? $count / count($evaluated) : null,
            $baseName.'_distribution' => [
                'true' => $count,
                'false' => max(0, count($evaluated) - $count),
            ],
        ];
    }

    private function c20G03SegmentSummary(array $rows): array
    {
        $evaluated = $this->evaluatedRows($rows);
        $segments = [];
        foreach ($evaluated as $row) {
            $context = is_array($row['c20_g03_context'] ?? null) ? $row['c20_g03_context'] : [];
            $key = ($context['risk_off_flag'] ?? false) ? 'C20_G03_RISK_OFF_FAIL_CONTEXT' : 'C20_G03_VOLATILITY_OK_CONTEXT';
            $segments[$key]['rows'][] = $row;
        }
        $out = [
            'c20_g03_used_as_filter' => false,
            'segmentation_only' => true,
            'segments' => [],
        ];
        foreach ($segments as $key => $payload) {
            $segRows = $payload['rows'];
            $out['segments'][$key] = [
                'evaluated_picks_count' => count($segRows),
                'exit_stop_count' => count(array_filter($segRows, function (array $row): bool { return ($row['exit_reason'] ?? '') === 'exit_stop'; })),
                'median_mae_5d' => $this->median($this->values($segRows, 'mae_5d')),
                'median_mfe_5d' => $this->median($this->values($segRows, 'mfe_5d')),
                'avg_ret_net_top' => $this->avg($this->values($segRows, 'ret_net')),
            ];
        }
        return $out;
    }

    private function decision(array $path, array $entryGap, array $mfeMae, array $exitReason, array $hold, array $c20G03): array
    {
        $evaluated = (int) ($path['evaluated_picks_count'] ?? 0);
        if ($evaluated === 0) {
            return [
                'decision_status' => 'C21_DIAGNOSTIC_BLOCKED',
                'catalog_allowed' => false,
                'oos_allowed' => false,
                'next_step' => 'Fix path data availability or price read wiring before drawing execution behavior conclusions.',
                'diagnostic_signal_found' => false,
                'entry_problem_suspected' => false,
                'exit_problem_suspected' => false,
                'stop_problem_suspected' => false,
                'hold_period_problem_suspected' => false,
                'regime_explains_execution_problem' => false,
            ];
        }

        $neverRate = $this->num($path['never_profitable_rate'] ?? null);
        $gaveBackRate = $this->num($path['gave_back_profit_rate'] ?? null);
        $gapLossRate = $this->num($entryGap['gap_open_loss_rate'] ?? null);
        $medianMfe = $this->num($mfeMae['median_mfe_5d'] ?? null);
        $medianMae = $this->num($mfeMae['median_mae_5d'] ?? null);
        $medianRet = $this->num($path['median_ret_net_top'] ?? null);
        $exitStop = (int) ($exitReason['exit_stop_count'] ?? 0);
        $exitTarget = (int) ($exitReason['exit_target_count'] ?? 0);
        $exitHold = (int) ($exitReason['exit_hold_count'] ?? 0);
        $entryProblem = (($entryGap['avg_entry_gap_pct'] ?? null) !== null && $entryGap['avg_entry_gap_pct'] < 0 && $gapLossRate !== null && $gapLossRate >= 0.25)
            || (($entryGap['median_entry_gap_pct'] ?? null) !== null && $entryGap['median_entry_gap_pct'] <= -0.005);
        $exitProblem = ($gaveBackRate !== null && $gaveBackRate >= 0.25)
            || ($medianMfe !== null && $medianRet !== null && $medianMfe > $medianRet + 0.010)
            || ($exitHold >= max(3, (int) ceil($evaluated * 0.4)) && $medianMfe !== null && $medianMfe > 0 && $medianRet !== null && $medianRet < 0.005);
        $stopProblem = $exitStop > $exitTarget && $medianMae !== null && $medianMfe !== null && abs($medianMae) > max(0.005, abs($medianMfe));
        $holdProblem = ($hold['best_median_close_return_day'] ?? 'd5') !== 'd5'
            || (($hold['mfe_peak_before_d5_rate'] ?? null) !== null && $hold['mfe_peak_before_d5_rate'] >= 0.40);
        $regimeExplains = $this->regimeExplanationFlag($c20G03);
        $diagnosticSignal = ($neverRate !== null && $neverRate >= 0.40)
            || ($gaveBackRate !== null && $gaveBackRate >= 0.25)
            || ($gapLossRate !== null && $gapLossRate >= 0.25)
            || ($exitStop > $exitTarget)
            || ($medianMfe !== null && $medianRet !== null && $medianMfe > $medianRet + 0.010)
            || $regimeExplains;

        return [
            'decision_status' => $diagnosticSignal ? 'C21_EXECUTION_SIGNAL_FOUND' : 'C21_EXECUTION_SIGNAL_NOT_FOUND',
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'next_step' => $diagnosticSignal
                ? 'Continue with C21 follow-up diagnostic that isolates the suspected execution component while keeping catalog and OOS forbidden.'
                : 'Stop C21 execution hypothesis unless a richer non-lookahead execution diagnostic is added; do not create catalog/OOS from this result.',
            'diagnostic_signal_found' => $diagnosticSignal,
            'entry_problem_suspected' => $entryProblem,
            'exit_problem_suspected' => $exitProblem,
            'stop_problem_suspected' => $stopProblem,
            'hold_period_problem_suspected' => $holdProblem,
            'regime_explains_execution_problem' => $regimeExplains,
        ];
    }

    private function c20G03Context(array $trade): array
    {
        $metrics = is_array($trade['score_metrics'] ?? null) ? $trade['score_metrics'] : [];
        $medianAtr = $this->num($metrics['atr14_pct'] ?? $trade['atr14_pct'] ?? null);
        $riskOff = $medianAtr === null || $medianAtr > 0.045;
        return [
            'profile_code' => 'C20_G03_VOLATILITY_RISK_OFF_FILTER',
            'segmentation_only' => true,
            'c20_g03_used_as_filter' => false,
            'risk_off_flag' => $riskOff,
            'candidate_atr14_pct' => $medianAtr,
            'reason_code' => $riskOff ? 'C20_VOLATILITY_RISK_OFF_FAIL_CONTEXT_ONLY' : 'C20_VOLATILITY_OK_CONTEXT_ONLY',
        ];
    }

    private function regimeExplanationFlag(array $summary): bool
    {
        $segments = $summary['segments'] ?? [];
        $risk = $segments['C20_G03_RISK_OFF_FAIL_CONTEXT'] ?? null;
        $ok = $segments['C20_G03_VOLATILITY_OK_CONTEXT'] ?? null;
        if (! is_array($risk) || ! is_array($ok)) {
            return false;
        }
        if ((int) ($risk['evaluated_picks_count'] ?? 0) < 2 || (int) ($ok['evaluated_picks_count'] ?? 0) < 2) {
            return false;
        }
        $riskStopRate = ((int) ($risk['exit_stop_count'] ?? 0)) / max(1, (int) ($risk['evaluated_picks_count'] ?? 0));
        $okStopRate = ((int) ($ok['exit_stop_count'] ?? 0)) / max(1, (int) ($ok['evaluated_picks_count'] ?? 0));
        $riskMae = $this->num($risk['median_mae_5d'] ?? null);
        $okMae = $this->num($ok['median_mae_5d'] ?? null);
        return $riskStopRate >= $okStopRate + 0.20
            || ($riskMae !== null && $okMae !== null && abs($riskMae) >= abs($okMae) + 0.010);
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

    private function evaluatedRows(array $rows): array
    {
        return array_values(array_filter($rows, function (array $row): bool {
            return ($row['missing_path_data_flag'] ?? true) === false && is_numeric($row['ret_net'] ?? null);
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

    private function returnByDayDistribution(array $rows): array
    {
        $out = [];
        for ($day = 1; $day <= 5; $day++) {
            $values = $this->values($rows, 'd'.$day.'_close_ret');
            $out['d'.$day] = [
                'avg' => $this->avg($values),
                'median' => $this->median($values),
                'p25' => $this->percentile($values, 0.25),
                'positive_rate' => count($values) > 0 ? count(array_filter($values, function (float $v): bool { return $v > 0; })) / count($values) : null,
            ];
        }
        return $out;
    }

    private function entryGapBuckets(array $rows): array
    {
        $buckets = ['<=-2%' => 0, '-2%..0%' => 0, '0%..2%' => 0, '>2%' => 0];
        foreach ($rows as $row) {
            $gap = $this->num($row['entry_gap_pct'] ?? null);
            if ($gap === null) {
                continue;
            }
            if ($gap <= -0.02) {
                $buckets['<=-2%']++;
            } elseif ($gap < 0) {
                $buckets['-2%..0%']++;
            } elseif ($gap <= 0.02) {
                $buckets['0%..2%']++;
            } else {
                $buckets['>2%']++;
            }
        }
        return $buckets;
    }

    private function mfeMaeBuckets(array $rows): array
    {
        $buckets = ['mfe_positive_mae_small' => 0, 'mfe_positive_mae_large' => 0, 'never_profitable' => 0];
        foreach ($rows as $row) {
            $mfe = $this->num($row['max_favorable_excursion_pct'] ?? null);
            $mae = $this->num($row['max_adverse_excursion_pct'] ?? null);
            if ($mfe === null || $mae === null) {
                continue;
            }
            if ($mfe <= 0) {
                $buckets['never_profitable']++;
            } elseif (abs($mae) >= 0.02) {
                $buckets['mfe_positive_mae_large']++;
            } else {
                $buckets['mfe_positive_mae_small']++;
            }
        }
        return $buckets;
    }

    private function mfePeakBeforeD5Rate(array $rows): ?float
    {
        if ($rows === []) {
            return null;
        }
        $count = 0;
        foreach ($rows as $row) {
            $mfeValues = [];
            for ($day = 1; $day <= 5; $day++) {
                $mfeValues[$day] = $this->num($row['mfe_'.$day.'d'] ?? null);
            }
            $max = max(array_filter($mfeValues, 'is_numeric'));
            $peakDay = null;
            foreach ($mfeValues as $day => $value) {
                if ($value === $max) {
                    $peakDay = $day;
                    break;
                }
            }
            if ($peakDay !== null && $peakDay < 5) {
                $count++;
            }
        }
        return $count / count($rows);
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

    private function selectedProfileDefinitions(array $profiles): array
    {
        $defs = [];
        foreach ($profiles as $code) {
            $defs[] = array_merge(['profile_code' => $code], self::diagnosticProfiles()[$code] ?? []);
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
            'shadow_path_analysis_only' => true,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C21_CATALOG_CODE' => 'NOT_CREATED',
            'C21_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_TICKER_BLACKLIST' => true,
            'NO_MONTH_BLACKLIST' => true,
            'NO_SECTOR_WHITELIST' => true,
            'NO_BEST_OF_FAILED_BINDING' => true,
            'NO_C01_TO_C20_MUTATION' => true,
            'NO_C19_REOPEN' => true,
            'NO_C20_REOPEN' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'canonical_evaluation_model_unchanged' => $this->canonicalEvaluationModel(),
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'future_path_price_used_for_selection' => false,
            'c20_g03_used_as_filter' => false,
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

    private function profileCodes($value)
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
        $known = self::diagnosticProfiles();
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
            'c21_catalog_implementation_deferred' => 1,
            'c21_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }
}
