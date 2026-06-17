<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketBenchmarkReadService;
use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC20RegimeTradeDateDiagnosticService
{
    public const ARTIFACT_TYPE = 'C20_REGIME_TRADE_DATE_DIAGNOSTIC';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c20-regime-trade-date-diagnostic-run-1.json';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;
    public const CANONICAL_SAMPLE_TARGET = 120;
    public const PROMISING_SAMPLE_TARGET = 100;

    private const C19_BASELINE_AVG = -0.0018;
    private const C19_BASELINE_MEDIAN = -0.0005;
    private const C19_BASELINE_WIN_RATE = 0.4355;
    private const C19_BASELINE_PERIOD_FAIL_COUNT = 13;

    private MarketDataTradingCalendarReadService $calendar;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;
    private WatchlistBacktestC19SelectionModelRedesignAnalysisService $selectionDiagnostic;
    private MarketDataPublishedEodSeriesReadService $priceSeries;
    private WatchlistBacktestRuntimeArtifactService $runtimeArtifacts;
    private MarketBenchmarkReadService $benchmarkRead;
    private array $benchmarkCache = [];

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null,
        WatchlistBacktestC19SelectionModelRedesignAnalysisService $selectionDiagnostic = null,
        MarketDataPublishedEodSeriesReadService $priceSeries = null,
        WatchlistBacktestRuntimeArtifactService $runtimeArtifacts = null,
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
        $this->runtimeArtifacts = $runtimeArtifacts ?: new WatchlistBacktestRuntimeArtifactService();
        $this->benchmarkRead = $benchmarkRead ?: new MarketBenchmarkReadService();
    }

    public static function regimeProfiles(): array
    {
        return [
            'C20_G00_BASELINE_NO_DATE_GATE' => [
                'description' => 'Baseline C20 without trade-date gate; preserves C19 proposed selection base for comparison.',
                'gate_family' => 'baseline',
            ],
            'C20_G01_MARKET_MOMENTUM_SAFE' => [
                'description' => 'Allow dates when IHSG proxy is not weak; fallback uses same-date candidate momentum and quality when benchmark data is unavailable.',
                'gate_family' => 'market_momentum',
            ],
            'C20_G02_BREADTH_HEALTHY' => [
                'description' => 'Allow dates with enough same-date candidate breadth and acceptable aggregate quality/momentum.',
                'gate_family' => 'breadth',
            ],
            'C20_G03_VOLATILITY_RISK_OFF_FILTER' => [
                'description' => 'Block dates where same-date selected pool volatility proxy is too high.',
                'gate_family' => 'volatility',
            ],
            'C20_G04_SECTOR_CONFIRMATION' => [
                'description' => 'Allow dates with aggregate sector confirmation metrics; never whitelists a sector code.',
                'gate_family' => 'sector_aggregate',
            ],
            'C20_G05_COMBINED_REGIME_QUALITY' => [
                'description' => 'Combined regime gate requiring multiple same-date market, breadth, volatility, and sector quality confirmations.',
                'gate_family' => 'combined',
            ],
            'C20_G06_NO_PICK_DAY_ALLOWED_QUALITY_FIRST' => [
                'description' => 'Strict quality-first combined date gate; no-pick days/weeks/months are allowed when regime quality is weak.',
                'gate_family' => 'strict_combined',
            ],
        ];
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode) !== '' ? trim($catalogCode) : self::DEFAULT_SOURCE_CATALOG_CODE;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C20_IS_ONLY_WINDOW_MISMATCH', 'C20 regime trade-date diagnostic requires the frozen IS window only.');
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.');
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C20_PARAM_IDS_INVALID', 'param_ids must be a comma-separated list of positive integers.');
        }
        $profiles = $this->profileCodes($options['profiles'] ?? null);
        if ($profiles === false) {
            return $this->blocked('WS_BT_C20_PROFILE_INVALID', 'profile-codes/profiles must be a comma-separated list of known C20 profile codes.');
        }
        $profileScope = 'EXPLICIT';
        if ($profiles === []) {
            $profiles = [
                'C20_G00_BASELINE_NO_DATE_GATE',
                'C20_G01_MARKET_MOMENTUM_SAFE',
                'C20_G02_BREADTH_HEALTHY',
                'C20_G05_COMBINED_REGIME_QUALITY',
            ];
            $profileScope = 'FAST_DEFAULT';
        }
        $maxProfiles = $this->positiveIntOrNull($options['max_profiles'] ?? null);
        if ($maxProfiles !== null) {
            $profiles = array_slice($profiles, 0, $maxProfiles);
            $profileScope .= '_MAX_'.$maxProfiles;
        }
        if ($profiles === []) {
            return $this->blocked('WS_BT_C20_PROFILE_EMPTY', 'No C20 regime profile is selected for execution.');
        }

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar is unavailable for C20 diagnostic.', [
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
            return $this->blocked('WS_BT_C20_SOURCE_CATALOG_UNAVAILABLE', $e->getMessage());
        }
        if ($paramIds !== []) {
            $allowed = array_fill_keys($paramIds, true);
            $rows = array_values(array_filter($rows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_C20_ROW_FILTER_NO_MATCH', 'No source catalog rows matched the explicit param filter.');
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
            return $this->blocked($selectionResult['reason_code'] ?? 'WS_BT_C20_SELECTION_SOURCE_NOT_READY', 'C19 selection diagnostic source did not produce a PASS artifact.', [
                'selection_result' => $selectionResult,
            ]);
        }
        $selectionArtifact = $this->readJson($selectionOutput);
        if ($selectionArtifact === null) {
            return $this->blocked('WS_BT_C20_SELECTION_ARTIFACT_UNREADABLE', 'C19 selection artifact could not be read.');
        }

        $executedAt = (string) ($options['executed_at'] ?? ($toDate.'T23:59:59+07:00'));
        $progress = $options['progress_callback'] ?? null;
        $profileSummaries = [];
        $sampleQualityTable = [];
        $monthlyDistribution = [];
        $gateSummaries = [];
        $dataAvailability = $this->dataAvailability($selectionArtifact, $tradeDates);
        $profileIndex = 0;

        foreach ($profiles as $profileCode) {
            $profileIndex++;
            if (is_callable($progress)) {
                $progress('[C20] profile '.$profileIndex.'/'.count($profiles).': '.$profileCode);
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
                $summary = $this->diagnoseProfileParam(
                    $profileCode,
                    $selectionDiag,
                    $row,
                    $paramset,
                    $tradeDates,
                    $calendarDates,
                    $calendar,
                    $fromDate,
                    $toDate,
                    $executedAt,
                    $outputPath,
                    $dataAvailability
                );
                $profileSummaries[] = $summary;
                $sampleQualityTable[] = $this->sampleQualityRow($summary);
                $monthlyDistribution = array_merge($monthlyDistribution, $summary['monthly_evaluated_distribution'] ?? []);
                $gateSummaries[] = $summary['trade_date_gate_summary'] ?? [];
            }
        }

        $profileSummaries = $this->rankProfileSummaries($profileSummaries);
        $sampleQualityTable = $this->rankSampleQualityTable($sampleQualityTable);
        $decision = $this->decision($profileSummaries);
        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C20_REGIME_TRADE_DATE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_REGIME_TRADE_DATE_DIAGNOSTIC',
            'generated_at' => $executedAt,
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'policy_code' => 'WS',
                'row_count' => count($rows),
                'max_params' => $maxParams,
            ],
            'source_evidence' => [
                'c19_final_status' => 'C19_CATALOG_CANDIDATE_FAILED',
                'c19_frontier_all_param_artifact_hash' => '18ae8b1f1dcfc5ddecc2279d3c9fd0ce69079e6d',
                'c19_stop_tuning' => true,
                'c19_catalog_code' => 'NOT_CREATED',
                'selection_source_artifact_path' => $selectionOutput,
                'selection_source_artifact_hash' => $selectionArtifact['artifact_hash'] ?? ($selectionResult['artifact_hash'] ?? null),
            ],
            'is_window' => [
                'from' => $fromDate,
                'to' => $toDate,
                'trade_date_count' => count($tradeDates),
            ],
            'price_evaluation_model' => $this->canonicalEvaluationModel(),
            'data_availability' => $dataAvailability,
            'regime_profiles' => $this->selectedProfileDefinitions($profiles),
            'profile_scope' => $profileScope,
            'profile_summaries' => $profileSummaries,
            'sample_quality_table' => $sampleQualityTable,
            'trade_date_gate_summary' => $this->aggregateGateSummary($gateSummaries),
            'monthly_evaluated_distribution' => $this->aggregateMonthlyDistribution($monthlyDistribution),
            'decision' => $decision,
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message']);
        }

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C20_REGIME_TRADE_DATE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_REGIME_TRADE_DATE_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'profile_count' => count($profiles),
            'profile_scope' => $profileScope,
            'best_any_sample_profile_code' => (string) ($decision['best_any_sample_profile']['profile_code'] ?? ''),
            'best_promising_sample_profile_code' => (string) ($decision['best_promising_sample_profile']['profile_code'] ?? ''),
            'best_sample_qualified_profile_code' => (string) ($decision['best_sample_qualified_profile']['profile_code'] ?? ''),
            'best_quality_target_profile_code' => (string) ($decision['best_quality_target_profile']['profile_code'] ?? ''),
            'profiles_with_quality_improvement' => count(array_filter($profileSummaries, function (array $summary): bool {
                return ($summary['quality_improvement']['quality_improvement'] ?? false) === true;
            })),
            'profiles_with_promising_continue' => count(array_filter($profileSummaries, function (array $summary): bool {
                return ($summary['quality_improvement']['promising_continue'] ?? false) === true;
            })),
            'profiles_with_quality_target_reached' => count(array_filter($profileSummaries, function (array $summary): bool {
                return ($summary['quality_improvement']['quality_target_reached'] ?? false) === true;
            })),
            'c20_catalog_implementation_deferred' => 1,
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
        array $calendarManifest,
        string $fromDate,
        string $toDate,
        string $executedAt,
        string $outputPath,
        array $dataAvailability
    ): array {
        $baseItems = array_values(array_filter($selectionDiagnostic['proposed_path']['selected_items'] ?? [], 'is_array'));
        $holdingDays = max(1, (int) ($paramset['backtest']['holding_days'] ?? 5));
        $strategyTradeDates = count($tradeDates) > $holdingDays ? array_slice($tradeDates, 0, count($tradeDates) - $holdingDays) : [];
        $featuresByDate = $this->featuresByDate($baseItems, $strategyTradeDates);
        $gate = $this->applyDateGate($baseItems, $featuresByDate, $strategyTradeDates, $profileCode);
        $selectedItems = $gate['items'];
        $trades = $this->proposalItemsToTrades($selectedItems, $paramset, $profileCode);
        $backtestPayload = $this->backtestPayload($trades, $strategyTradeDates, $paramset, $row, $profileCode, $gate);
        $requiredMap = $this->requiredPriceTickerMap($trades, $calendarDates, $holdingDays);
        $requiredDates = array_keys($requiredMap);
        $priceFromDate = $requiredDates[0] ?? $fromDate;
        $priceToDate = $requiredDates === [] ? $toDate : $requiredDates[count($requiredDates) - 1];
        $priceRead = $requiredMap === []
            ? $this->emptyPriceRead($priceFromDate, $priceToDate)
            : $this->priceSeries->readPublishedSeriesForDateTickerMap($priceFromDate, $priceToDate, $requiredMap);

        $runtimeArtifact = $this->runtimeArtifacts->buildArtifact(
            $backtestPayload,
            is_array($priceRead['series_by_ticker'] ?? null) ? $priceRead['series_by_ticker'] : [],
            $calendarDates,
            [
                'generated_at' => $executedAt,
                'runtime_context' => [
                    'calendar_manifest' => [
                        'requested_from_date' => $fromDate,
                        'requested_to_date' => $toDate,
                        'resolved_trade_dates' => $tradeDates,
                        'calendar_dates' => $calendarDates,
                        'calendar_source' => $calendarManifest['calendar_source'] ?? null,
                        'calendar_sources' => $calendarManifest['calendar_sources'] ?? [],
                        'calendar_hash' => $calendarManifest['calendar_hash'] ?? $this->stableHash($calendarDates),
                        'coverage' => $calendarManifest['coverage'] ?? [],
                    ],
                    'price_series_manifest' => $priceRead['price_series_manifest'] ?? [],
                    'publication_manifest' => $priceRead['publication_manifest'] ?? [],
                    'runtime_execution' => [
                        'executed_at' => $executedAt,
                        'output_path' => $outputPath,
                        'trade_candidates_frozen_before_price_read' => true,
                        'date_gate_features_frozen_before_price_read' => true,
                        'future_price_used_for_evaluation_only' => true,
                        'future_price_used_for_trade_date_gate' => false,
                        'strategy_payload_hash' => $this->stableHash($backtestPayload),
                        'strategy_payload_immutable' => true,
                        'ticker_count' => count($this->tickerCodesFromDateMap($requiredMap)),
                        'required_price_date_count' => count($requiredDates),
                        'requested_ticker_date_pair_count' => array_sum(array_map('count', $requiredMap)),
                        'targeted_date_ticker_read' => true,
                        'trade_candidate_count' => count($trades),
                        'strict_is_boundary' => true,
                        'hard_market_data_to_date' => $toDate,
                        'max_requested_market_data_date' => $priceToDate,
                        'strategy_trade_date_count' => count($strategyTradeDates),
                        'boundary_censoring_rule' => 'EXCLUDE_LAST_HOLDING_DAYS_FROM_ENTRY_GENERATION;KEEP_ALL_PRICE_READS_WITHIN_IS',
                        'c20_regime_trade_date_diagnostic_only' => true,
                    ],
                ],
            ]
        );

        $metrics = is_array($runtimeArtifact['metrics'] ?? null) ? $runtimeArtifact['metrics'] : [];
        $canonical = is_array($metrics['canonical_eval_metrics'] ?? null) ? $metrics['canonical_eval_metrics'] : [];
        $counts = is_array($metrics['counts'] ?? null) ? $metrics['counts'] : [];
        $evaluatedTrades = is_array($metrics['evaluated_trades'] ?? null) ? $metrics['evaluated_trades'] : [];
        $exitCounts = $this->exitCounts($evaluatedTrades);
        $summary = [
            'profile_code' => $profileCode,
            'profile_description' => (string) (self::regimeProfiles()[$profileCode]['description'] ?? ''),
            'param_id' => (int) ($row['param_id'] ?? 0),
            'row_code' => (string) ($row['row_code'] ?? ''),
            'total_trade_dates' => count($strategyTradeDates),
            'allowed_trade_dates_count' => count($gate['allowed_trade_dates']),
            'blocked_trade_dates_count' => count($gate['blocked_trade_dates']),
            'no_pick_days_count' => max(0, count($strategyTradeDates) - count($this->uniqueDatesFromItems($selectedItems))),
            'baseline_proposed_recommended_count' => count($baseItems),
            'proposed_recommended_count' => count($selectedItems),
            'requested_pairs_count' => array_sum(array_map('count', $requiredMap)),
            'evaluated_picks_count' => (int) ($canonical['picks_count'] ?? 0),
            'price_missing_count' => (int) ($counts['rejected_no_data_evaluation_count'] ?? 0),
            'avg_ret_net_top' => $canonical['avg_ret_net_top'] ?? null,
            'median_ret_net_top' => $canonical['median_ret_net_top'] ?? null,
            'p25_ret_net_top' => $canonical['p25_ret_net_top'] ?? null,
            'p75_ret_net_top' => $canonical['p75_ret_net_top'] ?? null,
            'win_rate_top' => $canonical['win_rate_top'] ?? null,
            'month_win_rate_min' => $canonical['month_win_rate_min'] ?? null,
            'month_avg_ret_net_min' => $canonical['month_avg_ret_net_min'] ?? null,
            'period_fail_count' => (int) ($canonical['period_fail_count'] ?? 0),
            'exit_stop_count' => $exitCounts['exit_stop_count'],
            'exit_target_count' => $exitCounts['exit_target_count'],
            'exit_hold_count' => $exitCounts['exit_hold_count'],
            'sample_gate' => (int) ($canonical['picks_count'] ?? 0) >= self::CANONICAL_SAMPLE_TARGET,
            'quality_gate' => $this->qualityTargetReached($canonical),
            'trade_date_gate_notes' => $gate['notes'],
            'trade_date_gate_summary' => [
                'profile_code' => $profileCode,
                'allowed_trade_dates' => $gate['allowed_trade_dates'],
                'blocked_trade_dates' => $gate['blocked_trade_dates'],
                'blocked_reason_counts' => $gate['blocked_reason_counts'],
                'uses_ihsg_proxy' => $gate['uses_ihsg_proxy'],
                'uses_candidate_fallback' => $gate['uses_candidate_fallback'],
                'uses_price_outcome_for_gate' => false,
                'feature_cutoff' => 'trade_date_eod_only',
            ],
            'monthly_evaluated_distribution' => $this->monthlyEvaluatedDistribution($selectedItems, $evaluatedTrades, $profileCode, (int) ($row['param_id'] ?? 0)),
            'reason_code_distribution' => $metrics['reason_code_distribution'] ?? [],
            'runtime_artifact_summary' => [
                'is_ready' => (bool) ($runtimeArtifact['is_ready'] ?? false),
                'reason_code' => $runtimeArtifact['reason_code'] ?? null,
                'artifact_hash' => $runtimeArtifact['validation']['artifact_hash'] ?? null,
                'metrics_ready' => (bool) ($runtimeArtifact['summary']['metrics_ready'] ?? false),
                'metrics_reason_code' => $runtimeArtifact['summary']['metrics_reason_code'] ?? null,
            ],
            'price_readiness' => [
                'is_ready' => (bool) ($priceRead['is_ready'] ?? false),
                'reason_code' => $priceRead['reason_code'] ?? null,
                'missing_price_dates' => $priceRead['price_series_manifest']['missing_price_dates'] ?? [],
                'missing_price_rows_count' => count($priceRead['price_series_manifest']['missing_price_rows'] ?? []),
            ],
            'data_availability' => $dataAvailability,
        ];
        $summary['quality_improvement'] = $this->qualityImprovementFlags($summary);

        return $summary;
    }

    private function dataAvailability(array $selectionArtifact, array $tradeDates): array
    {
        $candidateDistributionAvailable = false;
        $sectorProxyAvailable = false;
        $breadthProxyAvailable = false;
        $selectedTradeDates = [];
        foreach (($selectionArtifact['diagnostics'] ?? []) as $diag) {
            if (! is_array($diag)) {
                continue;
            }
            $items = array_values(array_filter($diag['proposed_path']['selected_items'] ?? [], 'is_array'));
            if ($items !== []) {
                $candidateDistributionAvailable = true;
                $breadthProxyAvailable = true;
            }
            foreach ($items as $item) {
                $date = (string) ($item['trade_date'] ?? '');
                if ($this->validDate($date)) {
                    $selectedTradeDates[$date] = $date;
                }
                $metrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
                if (isset($metrics['sector_roc20']) || isset($metrics['sector_rs_20_vs_ihsg'])) {
                    $sectorProxyAvailable = true;
                }
            }
        }

        $ihsgProxyAvailable = false;
        $notes = [];
        $benchmarkProbeDates = array_values($selectedTradeDates !== [] ? $selectedTradeDates : array_slice($tradeDates, 0, min(50, count($tradeDates))));
        sort($benchmarkProbeDates, SORT_STRING);
        foreach (array_slice($benchmarkProbeDates, 0, min(50, count($benchmarkProbeDates))) as $date) {
            $benchmark = $this->benchmarkContext($date);
            if (is_array($benchmark) && ($benchmark['is_valid'] ?? false) === true) {
                $ihsgProxyAvailable = true;
                break;
            }
        }
        if (! $ihsgProxyAvailable) {
            $notes[] = 'IHSG benchmark proxy was not readable in this environment/sample; C20 market profiles use explicit candidate-metric fallback when needed.';
        }
        if (! $sectorProxyAvailable) {
            $notes[] = 'Sector proxy metrics were not found in selected proposal items; sector profile falls back to aggregate relative-strength candidate metrics.';
        }

        return [
            'ihsg_proxy_available' => $ihsgProxyAvailable,
            'sector_proxy_available' => $sectorProxyAvailable,
            'breadth_proxy_available' => $breadthProxyAvailable,
            'candidate_distribution_available' => $candidateDistributionAvailable,
            'candidate_metric_fallback_supported' => $candidateDistributionAvailable,
            'notes' => $notes,
        ];
    }

    private function featuresByDate(array $items, array $strategyTradeDates): array
    {
        $features = [];
        foreach ($strategyTradeDates as $date) {
            $benchmark = $this->benchmarkContext($date);
            $features[$date] = [
                'trade_date' => $date,
                'candidate_count' => 0,
                'quality_values' => [],
                'score_values' => [],
                'roc20_values' => [],
                'atr14_pct_values' => [],
                'vol_ratio_values' => [],
                'rs_20_vs_ihsg_values' => [],
                'sector_roc20_values' => [],
                'sector_rs_20_vs_ihsg_values' => [],
                'ihsg_proxy' => $benchmark,
            ];
        }
        foreach ($items as $item) {
            $date = (string) ($item['trade_date'] ?? '');
            if (! isset($features[$date])) {
                continue;
            }
            $metrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
            $features[$date]['candidate_count']++;
            $this->appendNumeric($features[$date]['quality_values'], $item['quality_score'] ?? null);
            $this->appendNumeric($features[$date]['score_values'], $item['score_total'] ?? null);
            $this->appendNumeric($features[$date]['roc20_values'], $metrics['roc20'] ?? null);
            $this->appendNumeric($features[$date]['atr14_pct_values'], $metrics['atr14_pct'] ?? null);
            $this->appendNumeric($features[$date]['vol_ratio_values'], $metrics['vol_ratio'] ?? null);
            $this->appendNumeric($features[$date]['rs_20_vs_ihsg_values'], $metrics['rs_20_vs_ihsg'] ?? null);
            $this->appendNumeric($features[$date]['sector_roc20_values'], $metrics['sector_roc20'] ?? null);
            $this->appendNumeric($features[$date]['sector_rs_20_vs_ihsg_values'], $metrics['sector_rs_20_vs_ihsg'] ?? null);
        }

        foreach ($features as $date => $feature) {
            $features[$date]['avg_quality_score'] = $this->avg($feature['quality_values']);
            $features[$date]['median_quality_score'] = $this->median($feature['quality_values']);
            $features[$date]['avg_score_total'] = $this->avg($feature['score_values']);
            $features[$date]['median_roc20'] = $this->median($feature['roc20_values']);
            $features[$date]['avg_roc20'] = $this->avg($feature['roc20_values']);
            $features[$date]['median_atr14_pct'] = $this->median($feature['atr14_pct_values']);
            $features[$date]['avg_atr14_pct'] = $this->avg($feature['atr14_pct_values']);
            $features[$date]['median_vol_ratio'] = $this->median($feature['vol_ratio_values']);
            $features[$date]['avg_vol_ratio'] = $this->avg($feature['vol_ratio_values']);
            $features[$date]['avg_rs_20_vs_ihsg'] = $this->avg($feature['rs_20_vs_ihsg_values']);
            $features[$date]['median_sector_roc20'] = $this->median($feature['sector_roc20_values']);
            $features[$date]['avg_sector_roc20'] = $this->avg($feature['sector_roc20_values']);
            $features[$date]['avg_sector_rs_20_vs_ihsg'] = $this->avg($feature['sector_rs_20_vs_ihsg_values']);
            unset(
                $features[$date]['quality_values'],
                $features[$date]['score_values'],
                $features[$date]['roc20_values'],
                $features[$date]['atr14_pct_values'],
                $features[$date]['vol_ratio_values'],
                $features[$date]['rs_20_vs_ihsg_values'],
                $features[$date]['sector_roc20_values'],
                $features[$date]['sector_rs_20_vs_ihsg_values']
            );
        }

        return $features;
    }

    private function benchmarkContext(string $tradeDate): ?array
    {
        if (isset($this->benchmarkCache[$tradeDate])) {
            return $this->benchmarkCache[$tradeDate];
        }
        try {
            $read = $this->benchmarkRead->getBenchmarkMarketDataForTradeDate($tradeDate, 'IHSG');
            $benchmark = is_array($read['benchmark'] ?? null) ? $read['benchmark'] : null;
            $this->benchmarkCache[$tradeDate] = $benchmark;
            return $benchmark;
        } catch (\Throwable $e) {
            $this->benchmarkCache[$tradeDate] = null;
            return null;
        }
    }

    private function applyDateGate(array $items, array $featuresByDate, array $strategyTradeDates, string $profileCode): array
    {
        $allowed = [];
        $blocked = [];
        $blockedReasonCounts = [];
        $usesIhsg = false;
        $usesFallback = false;
        $notes = [];

        foreach ($strategyTradeDates as $date) {
            $feature = $featuresByDate[$date] ?? ['trade_date' => $date, 'candidate_count' => 0];
            $gate = $this->dateGateDecision($feature, $profileCode);
            if (($gate['uses_ihsg_proxy'] ?? false) === true) {
                $usesIhsg = true;
            }
            if (($gate['uses_candidate_fallback'] ?? false) === true) {
                $usesFallback = true;
            }
            if (($gate['allowed'] ?? false) === true) {
                $allowed[$date] = $date;
            } else {
                $blocked[$date] = $date;
                foreach (($gate['reason_codes'] ?? ['C20_DATE_GATE_BLOCKED']) as $reason) {
                    $blockedReasonCounts[$reason] = ($blockedReasonCounts[$reason] ?? 0) + 1;
                }
            }
        }

        $kept = [];
        foreach ($items as $item) {
            $date = (string) ($item['trade_date'] ?? '');
            if (isset($allowed[$date])) {
                $item['_c20_date_gate_profile'] = $profileCode;
                $item['_c20_date_gate_allowed'] = true;
                $kept[] = $item;
            }
        }

        if ($usesFallback) {
            $notes[] = 'candidate_metric_fallback_used_for_one_or_more_dates';
        }
        if (! $usesIhsg && $profileCode !== 'C20_G00_BASELINE_NO_DATE_GATE') {
            $notes[] = 'ihsg_proxy_not_used_or_not_available_for_this_profile_run';
        }
        arsort($blockedReasonCounts);
        $reasonRows = [];
        foreach ($blockedReasonCounts as $reason => $count) {
            $reasonRows[] = ['reason_code' => (string) $reason, 'count' => (int) $count];
        }

        return [
            'items' => array_values($kept),
            'allowed_trade_dates' => array_values($allowed),
            'blocked_trade_dates' => array_values($blocked),
            'blocked_reason_counts' => $reasonRows,
            'uses_ihsg_proxy' => $usesIhsg,
            'uses_candidate_fallback' => $usesFallback,
            'notes' => $notes,
        ];
    }

    private function dateGateDecision(array $feature, string $profileCode): array
    {
        if ($profileCode === 'C20_G00_BASELINE_NO_DATE_GATE') {
            return ['allowed' => true, 'reason_codes' => [], 'uses_ihsg_proxy' => false, 'uses_candidate_fallback' => false];
        }

        $market = $this->marketMomentumPass($feature);
        $breadth = $this->breadthPass($feature);
        $volatility = $this->volatilityPass($feature);
        $sector = $this->sectorPass($feature);

        if ($profileCode === 'C20_G01_MARKET_MOMENTUM_SAFE') {
            return $market;
        }
        if ($profileCode === 'C20_G02_BREADTH_HEALTHY') {
            return $breadth;
        }
        if ($profileCode === 'C20_G03_VOLATILITY_RISK_OFF_FILTER') {
            return $volatility;
        }
        if ($profileCode === 'C20_G04_SECTOR_CONFIRMATION') {
            return $sector;
        }

        $passes = 0;
        foreach ([$market, $breadth, $volatility, $sector] as $gate) {
            if (($gate['allowed'] ?? false) === true) {
                $passes++;
            }
        }
        $usesIhsg = (bool) (($market['uses_ihsg_proxy'] ?? false) || ($sector['uses_ihsg_proxy'] ?? false));
        $usesFallback = (bool) (($market['uses_candidate_fallback'] ?? false) || ($sector['uses_candidate_fallback'] ?? false));
        $reasons = array_values(array_unique(array_merge(
            $market['reason_codes'] ?? [],
            $breadth['reason_codes'] ?? [],
            $volatility['reason_codes'] ?? [],
            $sector['reason_codes'] ?? []
        )));

        if ($profileCode === 'C20_G05_COMBINED_REGIME_QUALITY') {
            return [
                'allowed' => $passes >= 3,
                'reason_codes' => $passes >= 3 ? [] : array_merge(['C20_G05_COMBINED_REGIME_PASS_COUNT_LT_3'], $reasons),
                'uses_ihsg_proxy' => $usesIhsg,
                'uses_candidate_fallback' => $usesFallback,
            ];
        }

        $quality = $this->num($feature['median_quality_score'] ?? null);
        $roc20 = $this->num($feature['median_roc20'] ?? null);
        $strictQuality = $quality !== null && $quality >= 0.56;
        $strictRoc = $roc20 !== null && $roc20 >= -0.030 && $roc20 <= 0.080;
        $allowed = $passes >= 3 && $strictQuality && $strictRoc;
        $strictReasons = [];
        if (! $strictQuality) {
            $strictReasons[] = 'C20_G06_MEDIAN_QUALITY_LT_0_56';
        }
        if (! $strictRoc) {
            $strictReasons[] = 'C20_G06_MEDIAN_ROC20_OUTSIDE_QUALITY_FIRST_RANGE';
        }

        return [
            'allowed' => $allowed,
            'reason_codes' => $allowed ? [] : array_values(array_unique(array_merge(['C20_G06_QUALITY_FIRST_STRICT_GATE_FAIL'], $strictReasons, $reasons))),
            'uses_ihsg_proxy' => $usesIhsg,
            'uses_candidate_fallback' => $usesFallback,
        ];
    }

    private function marketMomentumPass(array $feature): array
    {
        $benchmark = is_array($feature['ihsg_proxy'] ?? null) ? $feature['ihsg_proxy'] : null;
        if (is_array($benchmark) && ($benchmark['is_valid'] ?? false) === true) {
            $roc = $this->num($benchmark['roc_20'] ?? null);
            $toMa20 = $this->num($benchmark['close_to_ma20_pct'] ?? null);
            $slope = $this->num($benchmark['ma20_slope_pct'] ?? null);
            $allowed = ($roc === null || $roc >= -0.015)
                && ($toMa20 === null || $toMa20 >= -0.025)
                && ($slope === null || $slope >= -0.004);
            return [
                'allowed' => $allowed,
                'reason_codes' => $allowed ? [] : ['C20_MARKET_MOMENTUM_IHSG_WEAK'],
                'uses_ihsg_proxy' => true,
                'uses_candidate_fallback' => false,
            ];
        }

        $avgRoc = $this->num($feature['avg_roc20'] ?? null);
        $quality = $this->num($feature['median_quality_score'] ?? null);
        $allowed = $avgRoc !== null && $avgRoc >= -0.015 && ($quality === null || $quality >= 0.54);
        return [
            'allowed' => $allowed,
            'reason_codes' => $allowed ? [] : ['C20_MARKET_MOMENTUM_FALLBACK_CANDIDATE_WEAK'],
            'uses_ihsg_proxy' => false,
            'uses_candidate_fallback' => true,
        ];
    }

    private function breadthPass(array $feature): array
    {
        $count = (int) ($feature['candidate_count'] ?? 0);
        $quality = $this->num($feature['avg_quality_score'] ?? null);
        $roc = $this->num($feature['median_roc20'] ?? null);
        $allowed = $count >= 2 && ($quality === null || $quality >= 0.54) && ($roc === null || $roc >= -0.020);
        return [
            'allowed' => $allowed,
            'reason_codes' => $allowed ? [] : ['C20_BREADTH_HEALTHY_FAIL'],
            'uses_ihsg_proxy' => false,
            'uses_candidate_fallback' => true,
        ];
    }

    private function volatilityPass(array $feature): array
    {
        $medianAtr = $this->num($feature['median_atr14_pct'] ?? null);
        $avgAtr = $this->num($feature['avg_atr14_pct'] ?? null);
        $allowed = ($medianAtr !== null && $medianAtr <= 0.045) && ($avgAtr === null || $avgAtr <= 0.050);
        return [
            'allowed' => $allowed,
            'reason_codes' => $allowed ? [] : ['C20_VOLATILITY_RISK_OFF_FAIL'],
            'uses_ihsg_proxy' => false,
            'uses_candidate_fallback' => true,
        ];
    }

    private function sectorPass(array $feature): array
    {
        $sectorRoc = $this->num($feature['median_sector_roc20'] ?? null);
        $sectorRs = $this->num($feature['avg_sector_rs_20_vs_ihsg'] ?? null);
        if ($sectorRoc !== null || $sectorRs !== null) {
            $allowed = ($sectorRoc === null || $sectorRoc >= -0.020)
                && ($sectorRs === null || $sectorRs >= -0.030);
            return [
                'allowed' => $allowed,
                'reason_codes' => $allowed ? [] : ['C20_SECTOR_AGGREGATE_CONFIRMATION_FAIL'],
                'uses_ihsg_proxy' => false,
                'uses_candidate_fallback' => false,
            ];
        }
        $rs = $this->num($feature['avg_rs_20_vs_ihsg'] ?? null);
        $allowed = $rs !== null && $rs >= -0.030;
        return [
            'allowed' => $allowed,
            'reason_codes' => $allowed ? [] : ['C20_SECTOR_FALLBACK_RELATIVE_STRENGTH_FAIL'],
            'uses_ihsg_proxy' => false,
            'uses_candidate_fallback' => true,
        ];
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
            $components = $this->normalizeScoreComponents(is_array($item['score_components'] ?? null) ? $item['score_components'] : []);
            $trades[] = [
                'trade_date' => $tradeDate,
                'ticker_id' => (int) ($item['ticker_id'] ?? 0),
                'ticker' => $ticker,
                'bucket_code' => 'TOP_PICKS',
                'plan_rank' => (int) ($item['proposed_rank'] ?? $rank),
                'recommendation_rank' => $rank,
                'recommendation_score' => $this->num($item['quality_score'] ?? null),
                'score_total' => $this->num($item['score_total'] ?? null),
                'score_components' => $components,
                'score_metrics' => $metrics,
                'factor_breakdown' => is_array($item['factor_breakdown'] ?? null) ? $item['factor_breakdown'] : [],
                'score_momentum' => $components['score_momentum'] ?? null,
                'score_breakout' => $components['score_breakout'] ?? null,
                'score_volume' => $components['score_volume'] ?? null,
                'score_risk' => $components['score_risk'] ?? null,
                'dv20_idr' => $this->num($metrics['dv20_idr'] ?? null),
                'vol_ratio' => $this->num($metrics['vol_ratio'] ?? null),
                'roc20' => $this->num($metrics['roc20'] ?? null),
                'atr14_pct' => $this->num($metrics['atr14_pct'] ?? null),
                'stop_price' => null,
                'target_price' => null,
                'stop_atr_mult' => $this->num($paramset['risk']['stop_atr_mult'] ?? null),
                'min_rr' => $this->num($paramset['risk']['min_rr'] ?? null),
                'confirm_state' => 'C20_REGIME_TRADE_DATE_DIAGNOSTIC',
                'trade_state' => 'EVALUATION_CANDIDATE',
                'entry_model' => $paramset['backtest']['entry_model'] ?? 'D_PLUS_1_OPEN_DOCUMENTED',
                'exit_model' => $paramset['backtest']['exit_model'] ?? 'WEEKLY_SWING_MAX_5_TRADING_DAYS_DOCUMENTED',
                'pricing_model' => $paramset['backtest']['pricing_model'] ?? 'PUBLISHED_EOD_OHLCV_REQUIRED_AT_RUNTIME',
                'source_price_mode' => $paramset['backtest']['source_price_mode'] ?? 'RAW_TRADABLE_OHLC_REQUIRED',
                'gap_fill_rule' => $paramset['backtest']['gap_fill_rule'] ?? 'OPEN_IF_GAP_THROUGH_TRIGGER',
                'price_fraction_rule' => $paramset['backtest']['price_fraction_rule'] ?? 'IDX_EQUITY_PRICE_BANDS',
                'price_fraction_reference' => $paramset['backtest']['price_fraction_reference'] ?? 'THEORETICAL_LEVEL',
                'price_normalization_rule' => $paramset['backtest']['price_normalization_rule'] ?? 'CONSERVATIVE_STOP_FLOOR_TARGET_CEIL',
                'reason_codes' => array_values(array_unique(array_merge(
                    ['WS_C20_REGIME_DATE_GATE_ALLOWED', 'WS_REC_SELECTED'],
                    is_array($item['current_extension_failures'] ?? null) ? $item['current_extension_failures'] : []
                ))),
                'source_reference' => [
                    'diagnostic_source' => 'C20_REGIME_TRADE_DATE_GATE_ON_C19_SELECTION_BASE',
                    'proposed_plan_group' => (string) ($item['proposed_plan_group'] ?? ''),
                    'penalty_total' => $this->num($item['penalty_total'] ?? null),
                    'penalties' => is_array($item['penalties'] ?? null) ? $item['penalties'] : [],
                    'c20_date_gate_profile' => $profileCode,
                    'date_gate_feature_cutoff' => 'trade_date_eod_only',
                ],
                'contract_flags' => [
                    'from_recommendation_layer' => true,
                    'confirm_does_not_create_recommendation' => true,
                    'no_lookahead_price_used' => true,
                    'future_price_used_for_date_gate' => false,
                    'not_broker_surface' => true,
                    'c20_diagnostic_only' => true,
                ],
            ];
            $rank++;
        }

        usort($trades, function (array $left, array $right): int {
            foreach (['trade_date', 'recommendation_rank', 'ticker_id', 'ticker'] as $key) {
                $cmp = strcmp((string) ($left[$key] ?? ''), (string) ($right[$key] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            return 0;
        });

        return $trades;
    }

    private function backtestPayload(array $trades, array $strategyTradeDates, array $paramset, array $row, string $profileCode, array $gate): array
    {
        $items = [];
        foreach ($trades as $trade) {
            $items[] = [
                'trade_date' => $trade['trade_date'],
                'ticker_id' => $trade['ticker_id'],
                'ticker' => $trade['ticker'],
                'plan_group' => $trade['bucket_code'],
                'plan_rank' => $trade['plan_rank'],
                'recommendation_rank' => $trade['recommendation_rank'],
                'recommended_flag' => true,
                'confirm_state' => $trade['confirm_state'],
                'active_trade_evaluation' => true,
                'reason_codes' => $trade['reason_codes'],
            ];
        }

        $tradeDatesWithRecommendations = [];
        foreach ($trades as $trade) {
            $tradeDatesWithRecommendations[(string) $trade['trade_date']] = true;
        }
        $blocked = array_fill_keys($gate['blocked_trade_dates'] ?? [], true);
        $diagnostics = [];
        foreach ($strategyTradeDates as $tradeDate) {
            if (isset($tradeDatesWithRecommendations[$tradeDate])) {
                continue;
            }
            $diagnostics[] = [
                'trade_date' => $tradeDate,
                'reason_code' => isset($blocked[$tradeDate]) ? 'WS_C20_DATE_GATE_BLOCKED_NO_PICK_DAY' : 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID',
                'reason_codes' => isset($blocked[$tradeDate]) ? ['WS_C20_DATE_GATE_BLOCKED_NO_PICK_DAY'] : ['WS_REC_EMPTY_SET'],
                'active_trade_evaluation' => false,
                'c20_regime_trade_date_diagnostic_only' => true,
            ];
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_FOUNDATION_READY',
            'backtest_reason_code' => 'WATCHLIST_BACKTEST_FOUNDATION_READY',
            'meta' => [
                'strategy_code' => $paramset['policy_code'] ?? 'WS',
                'policy_code' => $paramset['policy_code'] ?? 'WS',
                'policy_version' => $paramset['policy_version'] ?? 'WS_EOD_RUNTIME',
                'paramset_code' => $paramset['paramset_code'] ?? null,
                'row_code' => $row['row_code'] ?? null,
                'engine' => 'WatchlistBacktestC20RegimeTradeDateDiagnosticService',
                'backtest_reason_code' => 'WATCHLIST_BACKTEST_FOUNDATION_READY',
                'not_production_ready' => true,
            ],
            'source_contract' => [
                'consumer' => 'WatchlistBacktestC20RegimeTradeDateDiagnosticService',
                'upstream' => ['C19 selector simulation', 'C20 trade-date gate', 'WatchlistBacktestRuntimeArtifactService'],
                'recommendation_layer_only' => true,
                'confirm_overlay_diagnostic_only' => true,
                'no_raw_market_data' => true,
                'no_latest_shortcut' => true,
                'no_max_trade_date_shortcut' => true,
                'no_plan_mutation' => true,
                'no_recommendation_mutation' => true,
                'no_confirm_mutation' => true,
                'no_portfolio_state' => true,
                'no_broker_surface' => true,
            ],
            'backtest_contract' => [
                'foundation_only' => false,
                'no_lookahead' => true,
                'date_gate_uses_trade_date_eod_only' => true,
                'date_gate_uses_price_outcome' => false,
                'deterministic_replay' => true,
                'publication_aware_replay' => true,
                'explicit_replay_window_only' => true,
                'same_trade_date_source_alignment' => true,
                'entry_exit_assumptions_documented' => true,
                'eod_only' => true,
                'price_series_consumed' => true,
                'metrics_ready' => false,
                'not_production_ready' => true,
            ],
            'paramset_snapshot' => $paramset,
            'replay_window' => [
                'from_trade_date' => $strategyTradeDates[0] ?? null,
                'to_trade_date' => $strategyTradeDates === [] ? null : $strategyTradeDates[count($strategyTradeDates) - 1],
                'trade_dates' => $strategyTradeDates,
                'days_requested' => count($strategyTradeDates),
                'explicit_window' => true,
            ],
            'items' => $items,
            'trades' => $trades,
            'evaluations' => [],
            'summary' => [
                'days_requested' => count($strategyTradeDates),
                'days_evaluated' => count($strategyTradeDates),
                'items_count' => count($items),
                'picks_count' => count($trades),
                'evaluations_count' => count($trades),
                'empty_recommendation_days' => count($diagnostics),
                'metrics_ready' => false,
                'artifact_runtime_persistence' => false,
                'production_ready' => false,
                'reason_codes' => ['WS_C20_REGIME_TRADE_DATE_DIAGNOSTIC'],
            ],
            'diagnostics' => $diagnostics,
            'artifact_manifest' => [
                'runtime_artifact_created' => false,
                'runtime_persistence_created' => false,
                'reason_codes' => ['WS_C20_REGIME_TRADE_DATE_DIAGNOSTIC'],
            ],
            'c20_date_gate' => [
                'profile_code' => $profileCode,
                'allowed_trade_dates_count' => count($gate['allowed_trade_dates'] ?? []),
                'blocked_trade_dates_count' => count($gate['blocked_trade_dates'] ?? []),
                'uses_price_outcome_for_gate' => false,
            ],
        ];
    }

    private function requiredPriceTickerMap(array $trades, array $calendarDates, int $holdingDays): array
    {
        $map = [];
        foreach ($trades as $trade) {
            $ticker = strtoupper(trim((string) ($trade['ticker'] ?? $trade['ticker_code'] ?? '')));
            $entryDate = $this->nextTradingDate((string) ($trade['trade_date'] ?? ''), $calendarDates);
            if ($ticker === '' || $entryDate === null) {
                continue;
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

    private function sampleQualityRow(array $summary): array
    {
        return [
            'profile_code' => $summary['profile_code'],
            'param_id' => $summary['param_id'],
            'row_code' => $summary['row_code'],
            'total_trade_dates' => $summary['total_trade_dates'],
            'allowed_trade_dates_count' => $summary['allowed_trade_dates_count'],
            'blocked_trade_dates_count' => $summary['blocked_trade_dates_count'],
            'no_pick_days_count' => $summary['no_pick_days_count'],
            'proposed_recommended_count' => $summary['proposed_recommended_count'],
            'evaluated_picks_count' => $summary['evaluated_picks_count'],
            'avg_ret_net_top' => $summary['avg_ret_net_top'],
            'median_ret_net_top' => $summary['median_ret_net_top'],
            'p25_ret_net_top' => $summary['p25_ret_net_top'],
            'win_rate_top' => $summary['win_rate_top'],
            'period_fail_count' => $summary['period_fail_count'],
            'sample_gate' => $summary['sample_gate'],
            'quality_gate' => $summary['quality_gate'],
            'quality_improvement' => $summary['quality_improvement']['quality_improvement'] ?? false,
            'promising_continue' => $summary['quality_improvement']['promising_continue'] ?? false,
            'quality_target_reached' => $summary['quality_improvement']['quality_target_reached'] ?? false,
        ];
    }

    private function qualityImprovementFlags(array $summary): array
    {
        $evaluated = (int) ($summary['evaluated_picks_count'] ?? 0);
        $avg = $this->num($summary['avg_ret_net_top'] ?? null);
        $median = $this->num($summary['median_ret_net_top'] ?? null);
        $win = $this->num($summary['win_rate_top'] ?? null);
        $periodFail = (int) ($summary['period_fail_count'] ?? 0);
        $qualityImprovement = $avg !== null && $median !== null && $win !== null
            && $avg > self::C19_BASELINE_AVG
            && $median >= self::C19_BASELINE_MEDIAN
            && $win > self::C19_BASELINE_WIN_RATE
            && $periodFail < self::C19_BASELINE_PERIOD_FAIL_COUNT;
        $promising = $evaluated >= self::PROMISING_SAMPLE_TARGET
            && $avg !== null && $avg > self::C19_BASELINE_AVG
            && $median !== null && $median >= self::C19_BASELINE_MEDIAN
            && $win !== null && $win >= 0.45
            && $periodFail < self::C19_BASELINE_PERIOD_FAIL_COUNT;
        $qualityTarget = $evaluated >= self::CANONICAL_SAMPLE_TARGET
            && $avg !== null && $avg >= 0
            && $median !== null && $median >= 0
            && $win !== null && $win >= 0.45
            && $periodFail <= 10;

        return [
            'quality_improvement' => $qualityImprovement,
            'promising_continue' => $promising,
            'quality_target_reached' => $qualityTarget,
            'baseline' => [
                'evaluated_picks_count' => 124,
                'avg_ret_net_top' => self::C19_BASELINE_AVG,
                'median_ret_net_top' => self::C19_BASELINE_MEDIAN,
                'p25_ret_net_top' => -0.0182,
                'win_rate_top' => self::C19_BASELINE_WIN_RATE,
                'period_fail_count' => self::C19_BASELINE_PERIOD_FAIL_COUNT,
            ],
        ];
    }

    private function decision(array $summaries): array
    {
        $bestAny = $this->bestByRank($summaries, 1);
        $bestPromising = $this->bestByFilter($summaries, function (array $summary): bool {
            return (int) ($summary['evaluated_picks_count'] ?? 0) >= self::PROMISING_SAMPLE_TARGET;
        });
        $bestQualified = $this->bestByFilter($summaries, function (array $summary): bool {
            return (int) ($summary['evaluated_picks_count'] ?? 0) >= self::CANONICAL_SAMPLE_TARGET;
        });
        $bestTarget = $this->bestByFilter($summaries, function (array $summary): bool {
            return ($summary['quality_improvement']['quality_target_reached'] ?? false) === true;
        });

        $decisionStatus = 'C20_DATE_GATE_NOT_ENOUGH';
        $next = 'Stop C20 as diagnostic failed unless a new non-lookahead regime data source is added.';
        if ($bestTarget !== null) {
            $decisionStatus = 'PROMISING_CONTINUE_TO_C20_TUNING';
            $next = 'Run repeat IS proof for the best quality-target C20 profile before any OOS or catalog discussion.';
        } elseif ($bestPromising !== null && ($bestPromising['quality_improvement']['promising_continue'] ?? false) === true) {
            $decisionStatus = 'PROMISING_CONTINUE_TO_C20_TUNING';
            $next = 'Run focused C20 tuning/repeat IS proof on promising sample profiles only; catalog remains forbidden.';
        }

        return [
            'decision_status' => $decisionStatus,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'next_step' => $next,
            'best_any_sample_profile' => $this->decisionProfile($bestAny),
            'best_promising_sample_profile' => $this->decisionProfile($bestPromising),
            'best_sample_qualified_profile' => $this->decisionProfile($bestQualified),
            'best_quality_target_profile' => $this->decisionProfile($bestTarget),
            'small_sample_cannot_be_main_decision' => true,
        ];
    }

    private function bestByRank(array $summaries, int $minimumEvaluated): ?array
    {
        $filtered = array_values(array_filter($summaries, function (array $summary) use ($minimumEvaluated): bool {
            return (int) ($summary['evaluated_picks_count'] ?? 0) >= $minimumEvaluated;
        }));
        return $filtered[0] ?? null;
    }

    private function bestByFilter(array $summaries, callable $filter): ?array
    {
        $filtered = array_values(array_filter($summaries, $filter));
        $filtered = $this->rankProfileSummaries($filtered);
        return $filtered[0] ?? null;
    }

    private function decisionProfile(?array $summary): ?array
    {
        if ($summary === null) {
            return null;
        }
        return [
            'profile_code' => $summary['profile_code'],
            'param_id' => $summary['param_id'],
            'row_code' => $summary['row_code'],
            'evaluated_picks_count' => $summary['evaluated_picks_count'],
            'avg_ret_net_top' => $summary['avg_ret_net_top'],
            'median_ret_net_top' => $summary['median_ret_net_top'],
            'win_rate_top' => $summary['win_rate_top'],
            'period_fail_count' => $summary['period_fail_count'],
        ];
    }

    private function rankProfileSummaries(array $summaries): array
    {
        usort($summaries, function (array $left, array $right): int {
            $leftTarget = (($left['quality_improvement']['quality_target_reached'] ?? false) === true) ? 1 : 0;
            $rightTarget = (($right['quality_improvement']['quality_target_reached'] ?? false) === true) ? 1 : 0;
            if ($leftTarget !== $rightTarget) {
                return $rightTarget <=> $leftTarget;
            }
            $leftPromising = (($left['quality_improvement']['promising_continue'] ?? false) === true) ? 1 : 0;
            $rightPromising = (($right['quality_improvement']['promising_continue'] ?? false) === true) ? 1 : 0;
            if ($leftPromising !== $rightPromising) {
                return $rightPromising <=> $leftPromising;
            }
            $leftSample = (int) ($left['evaluated_picks_count'] ?? 0);
            $rightSample = (int) ($right['evaluated_picks_count'] ?? 0);
            $leftQualified = $leftSample >= self::CANONICAL_SAMPLE_TARGET ? 1 : 0;
            $rightQualified = $rightSample >= self::CANONICAL_SAMPLE_TARGET ? 1 : 0;
            if ($leftQualified !== $rightQualified) {
                return $rightQualified <=> $leftQualified;
            }
            $cmp = $this->compareNullableFloat($right['avg_ret_net_top'] ?? null, $left['avg_ret_net_top'] ?? null);
            if ($cmp !== 0) {
                return $cmp;
            }
            $cmp = $this->compareNullableFloat($right['median_ret_net_top'] ?? null, $left['median_ret_net_top'] ?? null);
            if ($cmp !== 0) {
                return $cmp;
            }
            $cmp = $this->compareNullableFloat($right['win_rate_top'] ?? null, $left['win_rate_top'] ?? null);
            if ($cmp !== 0) {
                return $cmp;
            }
            return $rightSample <=> $leftSample;
        });
        return array_values($summaries);
    }

    private function rankSampleQualityTable(array $rows): array
    {
        usort($rows, function (array $left, array $right): int {
            $cmp = strcmp((string) ($left['profile_code'] ?? ''), (string) ($right['profile_code'] ?? ''));
            if ($cmp !== 0) {
                return $cmp;
            }
            return ((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0));
        });
        return array_values($rows);
    }

    private function compareNullableFloat($right, $left): int
    {
        $r = $this->num($right);
        $l = $this->num($left);
        if ($r === null && $l === null) {
            return 0;
        }
        if ($r === null) {
            return 1;
        }
        if ($l === null) {
            return -1;
        }
        if ($r == $l) {
            return 0;
        }
        return $r < $l ? -1 : 1;
    }

    private function qualityTargetReached(array $canonical): bool
    {
        $summary = [
            'evaluated_picks_count' => (int) ($canonical['picks_count'] ?? 0),
            'avg_ret_net_top' => $canonical['avg_ret_net_top'] ?? null,
            'median_ret_net_top' => $canonical['median_ret_net_top'] ?? null,
            'win_rate_top' => $canonical['win_rate_top'] ?? null,
            'period_fail_count' => (int) ($canonical['period_fail_count'] ?? 0),
        ];
        return $this->qualityImprovementFlags($summary)['quality_target_reached'];
    }

    private function selectedProfileDefinitions(array $profiles): array
    {
        $defs = [];
        foreach ($profiles as $code) {
            $defs[] = array_merge(['profile_code' => $code], self::regimeProfiles()[$code] ?? []);
        }
        return $defs;
    }

    private function aggregateGateSummary(array $gateSummaries): array
    {
        $totalAllowed = 0;
        $totalBlocked = 0;
        $reasons = [];
        $profiles = [];
        foreach ($gateSummaries as $summary) {
            if (! is_array($summary)) {
                continue;
            }
            $profile = (string) ($summary['profile_code'] ?? '');
            if ($profile !== '') {
                $profiles[$profile] = true;
            }
            $totalAllowed += count($summary['allowed_trade_dates'] ?? []);
            $totalBlocked += count($summary['blocked_trade_dates'] ?? []);
            foreach (($summary['blocked_reason_counts'] ?? []) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $reason = (string) ($row['reason_code'] ?? '');
                if ($reason === '') {
                    continue;
                }
                $reasons[$reason] = ($reasons[$reason] ?? 0) + (int) ($row['count'] ?? 0);
            }
        }
        arsort($reasons);
        $reasonRows = [];
        foreach ($reasons as $reason => $count) {
            $reasonRows[] = ['reason_code' => $reason, 'count' => $count];
        }
        return [
            'profile_count' => count($profiles),
            'total_allowed_trade_dates_across_profiles' => $totalAllowed,
            'total_blocked_trade_dates_across_profiles' => $totalBlocked,
            'blocked_reason_counts' => $reasonRows,
            'uses_price_outcome_for_gate' => false,
        ];
    }

    private function aggregateMonthlyDistribution(array $rows): array
    {
        usort($rows, function (array $left, array $right): int {
            foreach (['profile_code', 'param_id', 'month'] as $key) {
                $cmp = strcmp((string) ($left[$key] ?? ''), (string) ($right[$key] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            return 0;
        });
        return array_values($rows);
    }

    private function monthlyEvaluatedDistribution(array $selectedItems, array $evaluatedTrades, string $profileCode, int $paramId): array
    {
        $monthly = [];
        foreach ($selectedItems as $item) {
            $month = substr((string) ($item['trade_date'] ?? ''), 0, 7);
            if ($month === '') {
                continue;
            }
            $key = $profileCode.'|'.$paramId.'|'.$month;
            $monthly[$key] = $monthly[$key] ?? [
                'profile_code' => $profileCode,
                'param_id' => $paramId,
                'month' => $month,
                'proposed_recommended_count' => 0,
                'evaluated_picks_count' => 0,
                'price_missing_count' => 0,
                'win_rate' => null,
                'avg_ret_net' => null,
            ];
            $monthly[$key]['proposed_recommended_count']++;
        }
        $returns = [];
        foreach ($evaluatedTrades as $trade) {
            if (! is_array($trade)) {
                continue;
            }
            $month = substr((string) ($trade['trade_date'] ?? ''), 0, 7);
            if ($month === '') {
                continue;
            }
            $key = $profileCode.'|'.$paramId.'|'.$month;
            $monthly[$key] = $monthly[$key] ?? [
                'profile_code' => $profileCode,
                'param_id' => $paramId,
                'month' => $month,
                'proposed_recommended_count' => 0,
                'evaluated_picks_count' => 0,
                'price_missing_count' => 0,
                'win_rate' => null,
                'avg_ret_net' => null,
            ];
            if (($trade['metrics_ready'] ?? false) === true && is_numeric($trade['ret_net'] ?? null)) {
                $monthly[$key]['evaluated_picks_count']++;
                $returns[$key][] = (float) $trade['ret_net'];
            } else {
                $monthly[$key]['price_missing_count']++;
            }
        }
        foreach ($returns as $key => $values) {
            $wins = count(array_filter($values, function (float $value): bool { return $value > 0; }));
            $monthly[$key]['win_rate'] = count($values) > 0 ? $wins / count($values) : null;
            $monthly[$key]['avg_ret_net'] = count($values) > 0 ? array_sum($values) / count($values) : null;
        }
        ksort($monthly, SORT_STRING);
        return array_values($monthly);
    }

    private function exitCounts(array $evaluatedTrades): array
    {
        $out = ['exit_stop_count' => 0, 'exit_target_count' => 0, 'exit_hold_count' => 0];
        foreach ($evaluatedTrades as $trade) {
            if (! is_array($trade) || ($trade['metrics_ready'] ?? false) !== true) {
                continue;
            }
            if (($trade['exit_reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EXIT_STOP') {
                $out['exit_stop_count']++;
            } elseif (($trade['exit_reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EXIT_TARGET') {
                $out['exit_target_count']++;
            } elseif (($trade['exit_reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED') {
                $out['exit_hold_count']++;
            }
        }
        return $out;
    }

    private function normalizeScoreComponents(array $components): array
    {
        $out = [];
        foreach (['momentum', 'breakout', 'volume', 'risk'] as $key) {
            $out['score_'.$key] = $this->num($components['score_'.$key] ?? $components[$key] ?? null);
        }
        return $out;
    }

    private function uniqueDatesFromItems(array $items): array
    {
        $dates = [];
        foreach ($items as $item) {
            $date = (string) ($item['trade_date'] ?? '');
            if ($this->validDate($date)) {
                $dates[$date] = $date;
            }
        }
        ksort($dates, SORT_STRING);
        return array_values($dates);
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
            'source_service' => 'WatchlistBacktestRuntimeArtifactService + WatchlistBacktestMetricsService',
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C20_CATALOG_CODE' => 'NOT_CREATED',
            'C20_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_TICKER_BLACKLIST' => true,
            'NO_MONTH_BLACKLIST' => true,
            'NO_SECTOR_WHITELIST' => true,
            'NO_BEST_OF_FAILED_BINDING' => true,
            'NO_C01_TO_C19_MUTATION' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'canonical_evaluation_model_unchanged' => $this->canonicalEvaluationModel(),
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'date_gate_uses_price_outcome' => false,
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

    private function tickerCodesFromDateMap(array $map): array
    {
        $codes = [];
        foreach ($map as $items) {
            foreach ($items as $code) {
                $codes[(string) $code] = (string) $code;
            }
        }
        ksort($codes, SORT_STRING);
        return array_values($codes);
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
        $known = self::regimeProfiles();
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

    private function appendNumeric(array &$values, $value): void
    {
        if (is_numeric($value)) {
            $values[] = (float) $value;
        }
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
            'c20_catalog_implementation_deferred' => 1,
            'c20_catalog_code' => 'NOT_CREATED',
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }
}
