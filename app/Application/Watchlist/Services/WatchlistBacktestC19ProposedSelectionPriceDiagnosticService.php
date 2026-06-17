<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC19ProposedSelectionPriceDiagnosticService
{
    public const ARTIFACT_TYPE = 'C19_PROPOSED_SELECTION_PRICE_DIAGNOSTIC';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c19-proposed-selection-price-diagnostic-run-1.json';
    public const CANONICAL_SAMPLE_TARGET = 120;
    public const DEFAULT_QUALITY_PROFILE = 'Q00_TAHAP_4_BASELINE';

    public static function qualityProfiles(): array
    {
        return [
            'Q00_TAHAP_4_BASELINE' => [
                'description' => 'Baseline Tahap 4 selection; no additional quality recovery filter.',
                'selector_input_only' => true,
            ],
            'Q01_STRICT_ENTRY_QUALITY' => [
                'description' => 'Reject candidates still carrying C17 entry-quality floor failure before price evaluation.',
                'selector_input_only' => true,
            ],
            'Q02_NO_SCORE_OVEREXTENSION_RECOVERY' => [
                'description' => 'Reject candidates recovered only by allowing C17 score overextension failure.',
                'selector_input_only' => true,
            ],
            'Q03_PULLBACK_ROC_DISCIPLINE' => [
                'description' => 'Reject candidates with ROC5 pullback miss or ROC20 segment miss.',
                'selector_input_only' => true,
            ],
            'Q04_LOW_ATR_NEG_ROC20_PRIORITY' => [
                'description' => 'Prefer lower ATR and non-chasing ROC20 candidates using only pre-entry EOD metrics.',
                'selector_input_only' => true,
            ],
            'Q05_DOWNSIDE_AWARE_SCORE_120' => [
                'description' => 'Global downside-aware rerank to 120-125 candidates using quality score, risk, breakout, and penalty.',
                'selector_input_only' => true,
            ],
            'Q06_MONTHLY_QUALITY_CAP_120' => [
                'description' => 'Limit forced monthly coverage to best candidates first, then fill globally to sample target.',
                'selector_input_only' => true,
            ],
            'Q07_NO_OVEREXTENSION_CORE_WITH_DOWNSIDE_BACKFILL_120' => [
                'description' => 'Use Q02 no-overextension candidates as quality core, then controlled downside-aware backfill to preserve sample.',
                'selector_input_only' => true,
                'hybrid_backfill' => true,
            ],
            'Q08_NO_OVEREXTENSION_CORE_WITH_MONTHLY_FLEX_BACKFILL' => [
                'description' => 'Use Q02 core with monthly-flex backfill; do not force bad months to five picks unless ranked quality supports it.',
                'selector_input_only' => true,
                'hybrid_backfill' => true,
            ],
            'Q09_LOW_ATR_NEG_ROC20_CORE_WITH_NO_OVEREXTENSION_BACKFILL' => [
                'description' => 'Use Q04 low-ATR/ROC20 discipline as core and backfill only from no-overextension quality candidates.',
                'selector_input_only' => true,
                'hybrid_backfill' => true,
            ],
            'Q10_HYBRID_Q02_Q04_Q05_BACKFILL_125' => [
                'description' => 'Hybrid Q02/Q04 quality core with Q05-style backfill around 125 candidates; designed to test quality preservation near sample target.',
                'selector_input_only' => true,
                'hybrid_backfill' => true,
            ],
        ];
    }

    private MarketDataTradingCalendarReadService $calendar;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;
    private WatchlistBacktestC19SelectionModelRedesignAnalysisService $selectionDiagnostic;
    private MarketDataPublishedEodSeriesReadService $priceSeries;
    private WatchlistBacktestRuntimeArtifactService $runtimeArtifacts;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null,
        WatchlistBacktestC19SelectionModelRedesignAnalysisService $selectionDiagnostic = null,
        MarketDataPublishedEodSeriesReadService $priceSeries = null,
        WatchlistBacktestRuntimeArtifactService $runtimeArtifacts = null
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
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode) !== '' ? trim($catalogCode) : self::DEFAULT_SOURCE_CATALOG_CODE;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;
        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C19_PRICE_IS_ONLY_WINDOW_MISMATCH', 'C19 price diagnostic requires the frozen IS window only.');
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.');
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C19_PRICE_PARAM_IDS_INVALID', 'param_ids must be a comma-separated list of positive integers.');
        }
        $qualityProfile = strtoupper(trim((string) ($options['quality_profile'] ?? self::DEFAULT_QUALITY_PROFILE)));
        if ($qualityProfile === '') {
            $qualityProfile = self::DEFAULT_QUALITY_PROFILE;
        }
        if (! isset(self::qualityProfiles()[$qualityProfile])) {
            return $this->blocked('WS_BT_C19_PRICE_QUALITY_PROFILE_INVALID', 'Unknown C19 quality profile: '.$qualityProfile);
        }

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar is unavailable for C19 price diagnostic.', [
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
            return $this->blocked('WS_BT_C19_PRICE_SOURCE_CATALOG_UNAVAILABLE', $e->getMessage());
        }
        if ($paramIds !== []) {
            $allowed = array_fill_keys($paramIds, true);
            $rows = array_values(array_filter($rows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_C19_PRICE_ROW_FILTER_NO_MATCH', 'No source catalog rows matched the explicit param filter.');
            }
        }
        $maxParams = $this->positiveIntOrNull($options['max_params'] ?? null);
        if ($maxParams !== null) {
            $rows = array_slice(array_values($rows), 0, $maxParams);
        }
        $rowsByParamId = [];
        foreach ($rows as $row) {
            $rowsByParamId[(int) ($row['param_id'] ?? 0)] = $row;
        }

        $selectionOutput = (string) ($options['selection_output_path'] ?? ($outputPath.'.selection-analysis.json'));
        $reuseSelection = ! empty($options['reuse_selection_artifact']) && is_file($selectionOutput);
        $selectionResult = [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_SELECTION_DIAGNOSTIC_REUSED',
            'artifact_path' => $selectionOutput,
        ];
        if (! $reuseSelection) {
            $selectionResult = $this->selectionDiagnostic->execute($catalogCode, $fromDate, $toDate, $selectionOutput, [
                'param_ids' => $paramIds,
                'overwrite' => true,
                'executed_at' => $options['executed_at'] ?? ($toDate.'T23:59:59+07:00'),
            ]);
            if (($selectionResult['status'] ?? null) !== 'PASS') {
                return $this->blocked($selectionResult['reason_code'] ?? 'WS_BT_C19_SELECTION_DIAGNOSTIC_NOT_READY', 'C19 selection diagnostic did not produce a PASS artifact.', [
                    'selection_result' => $selectionResult,
                ]);
            }
        }
        $selectionArtifact = $this->readJson($selectionOutput);
        if ($selectionArtifact === null) {
            return $this->blocked('WS_BT_C19_SELECTION_DIAGNOSTIC_ARTIFACT_UNREADABLE', 'C19 selection diagnostic artifact could not be read.');
        }

        $executedAt = (string) ($options['executed_at'] ?? ($toDate.'T23:59:59+07:00'));
        $diagnostics = [];
        foreach (($selectionArtifact['diagnostics'] ?? []) as $selectionDiagnostic) {
            if (! is_array($selectionDiagnostic)) {
                continue;
            }
            $paramId = (int) ($selectionDiagnostic['param_id'] ?? 0);
            if (! isset($rowsByParamId[$paramId])) {
                continue;
            }
            $paramset = $this->paramsetFactory->make($rowsByParamId[$paramId]);
            $diagnostics[] = $this->diagnoseParamPrice(
                $selectionDiagnostic,
                $rowsByParamId[$paramId],
                $paramset,
                $tradeDates,
                $calendarDates,
                $calendar,
                $fromDate,
                $toDate,
                $executedAt,
                $outputPath,
                $qualityProfile
            );
        }

        $summary = $this->crossParamSummary($diagnostics);
        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_PRICE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_PRICE_DIAGNOSTIC',
            'generated_at' => $executedAt,
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'row_count' => count($diagnostics),
                'policy_code' => 'WS',
                'max_params' => $maxParams,
            ],
            'is_window' => [
                'from' => $fromDate,
                'to' => $toDate,
                'trade_date_count' => count($tradeDates),
            ],
            'selection_diagnostic_source' => [
                'artifact_path' => $selectionOutput,
                'artifact_hash' => $selectionArtifact['artifact_hash'] ?? ($selectionResult['artifact_hash'] ?? null),
                'reason_code' => $selectionResult['reason_code'] ?? null,
                'selector_simulation_from_scored_pool' => true,
                'selection_artifact_reused' => $reuseSelection,
            ],
            'price_evaluation_model' => $this->canonicalEvaluationModel(),
            'quality_recovery_profile' => [
                'profile_code' => $qualityProfile,
                'profile_definition' => self::qualityProfiles()[$qualityProfile],
                'uses_price_outcome_for_selection' => false,
                'diagnostic_only' => true,
            ],
            'diagnostics' => $diagnostics,
            'cross_param_summary' => $summary,
            'c19_catalog_decision' => [
                'C19_CATALOG_IMPLEMENTATION_DEFERRED' => true,
                'C19_IMPLEMENTED_SOURCE_LEVEL' => false,
                'C19_CATALOG_CODE' => 'NOT_CREATED',
                'defer_reason' => 'Tahap 4 is an IS-only proposed-selection price diagnostic. Catalog creation still requires repeatable IS calibration evidence and downside/monthly stability review.',
                'next_gate_before_catalog' => [
                    'inspect evaluated_picks_count and price_missing_count',
                    'inspect avg/median/p25 return and monthly stability',
                    'if viable, run IS calibration-style proof twice',
                    'do not run OOS before an immutable IS candidate exists',
                ],
            ],
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message']);
        }

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_PRICE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_PRICE_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'quality_profile' => $qualityProfile,
            'diagnostic_param_count' => count($diagnostics),
            'max_proposed_recommended_count' => (int) ($summary['max_proposed_recommended_count'] ?? 0),
            'max_requested_pairs_count' => (int) ($summary['max_requested_pairs_count'] ?? 0),
            'max_evaluated_picks_count' => (int) ($summary['max_evaluated_picks_count'] ?? 0),
            'max_price_missing_count' => (int) ($summary['max_price_missing_count'] ?? 0),
            'params_with_evaluated_sample_target_reached' => (int) ($summary['params_with_evaluated_sample_target_reached'] ?? 0),
            'c19_catalog_implementation_deferred' => 1,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function diagnoseParamPrice(
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
        string $qualityProfile = self::DEFAULT_QUALITY_PROFILE
    ): array {
        $baseSelectedItems = array_values(array_filter($selectionDiagnostic['proposed_path']['selected_items'] ?? [], 'is_array'));
        $profileSelection = $this->applyQualityProfile($baseSelectedItems, $qualityProfile, $paramset);
        $selectedItems = $profileSelection['items'];
        $holdingDays = max(1, (int) ($paramset['backtest']['holding_days'] ?? 5));
        $strategyTradeDates = count($tradeDates) > $holdingDays
            ? array_slice($tradeDates, 0, count($tradeDates) - $holdingDays)
            : [];
        $trades = $this->proposalItemsToTrades($selectedItems, $paramset);
        $backtestPayload = $this->backtestPayload($trades, $strategyTradeDates, $paramset, $row);
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
                        'future_price_used_for_evaluation_only' => true,
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
                        'c19_price_diagnostic_only' => true,
                    ],
                ],
            ]
        );

        $metrics = is_array($runtimeArtifact['metrics'] ?? null) ? $runtimeArtifact['metrics'] : [];
        $canonical = is_array($metrics['canonical_eval_metrics'] ?? null) ? $metrics['canonical_eval_metrics'] : [];
        $counts = is_array($metrics['counts'] ?? null) ? $metrics['counts'] : [];

        return [
            'param_id' => (int) ($row['param_id'] ?? 0),
            'row_code' => (string) ($row['row_code'] ?? ''),
            'selection_counts' => [
                'current_recommended_count' => (int) ($selectionDiagnostic['current_path']['recommendation_output_count'] ?? 0),
                'proposed_top_buffer_count' => (int) ($selectionDiagnostic['proposed_path']['top_count'] ?? 0),
                'proposed_secondary_buffer_count' => (int) ($selectionDiagnostic['proposed_path']['secondary_count'] ?? 0),
                'baseline_proposed_recommended_count' => count($baseSelectedItems),
                'proposed_recommended_count' => count($selectedItems),
                'quality_profile_code' => $qualityProfile,
                'quality_profile_removed_count' => (int) ($profileSelection['removed_count'] ?? 0),
                'quality_profile_core_selected_count' => (int) ($profileSelection['stage_counts']['core_selected_count'] ?? 0),
                'quality_profile_backfill_selected_count' => (int) ($profileSelection['stage_counts']['backfill_selected_count'] ?? 0),
            ],
            'price_evaluation_counts' => [
                'requested_pairs_count' => array_sum(array_map('count', $requiredMap)),
                'requested_price_date_count' => count($requiredDates),
                'requested_ticker_count' => count($this->tickerCodesFromDateMap($requiredMap)),
                'evaluated_picks_count' => (int) ($canonical['picks_count'] ?? 0),
                'total_evaluated_trades' => (int) ($counts['total_evaluated_trades'] ?? 0),
                'price_missing_count' => (int) ($counts['rejected_no_data_evaluation_count'] ?? 0),
                'diagnostics_count' => (int) ($counts['diagnostics_count'] ?? 0),
            ],
            'return_metrics' => [
                'avg_ret_net_top' => $canonical['avg_ret_net_top'] ?? null,
                'median_ret_net_top' => $canonical['median_ret_net_top'] ?? null,
                'p25_ret_net_top' => $canonical['p25_ret_net_top'] ?? null,
                'p75_ret_net_top' => $canonical['p75_ret_net_top'] ?? null,
                'win_rate_top' => $canonical['win_rate_top'] ?? null,
                'month_win_rate_min' => $canonical['month_win_rate_min'] ?? null,
                'month_avg_ret_net_min' => $canonical['month_avg_ret_net_min'] ?? null,
                'days_covered' => $canonical['days_covered'] ?? null,
                'periods_count' => $canonical['periods_count'] ?? null,
                'period_fail_count' => $canonical['period_fail_count'] ?? null,
            ],
            'monthly_evaluated_distribution' => $this->monthlyEvaluatedDistribution($selectedItems, $metrics['evaluated_trades'] ?? []),
            'reason_code_distribution' => $metrics['reason_code_distribution'] ?? [],
            'quality_profile_diagnostic' => [
                'profile_code' => $qualityProfile,
                'profile_definition' => self::qualityProfiles()[$qualityProfile] ?? [],
                'baseline_selected_count' => count($baseSelectedItems),
                'profile_selected_count' => count($selectedItems),
                'removed_count' => (int) ($profileSelection['removed_count'] ?? 0),
                'removed_reason_counts' => $profileSelection['removed_reason_counts'] ?? [],
                'stage_counts' => $profileSelection['stage_counts'] ?? [],
                'selection_lineage' => $profileSelection['selection_lineage'] ?? [],
                'uses_price_outcome_for_selection' => false,
            ],
            'price_readiness' => [
                'is_ready' => (bool) ($priceRead['is_ready'] ?? false),
                'reason_code' => $priceRead['reason_code'] ?? null,
                'missing_price_dates' => $priceRead['price_series_manifest']['missing_price_dates'] ?? [],
                'missing_price_rows_count' => count($priceRead['price_series_manifest']['missing_price_rows'] ?? []),
            ],
            'runtime_artifact_summary' => [
                'is_ready' => (bool) ($runtimeArtifact['is_ready'] ?? false),
                'reason_code' => $runtimeArtifact['reason_code'] ?? null,
                'artifact_hash' => $runtimeArtifact['validation']['artifact_hash'] ?? null,
                'metrics_ready' => (bool) ($runtimeArtifact['summary']['metrics_ready'] ?? false),
                'metrics_reason_code' => $runtimeArtifact['summary']['metrics_reason_code'] ?? null,
            ],
            'sample_gate_diagnostic' => [
                'canonical_sample_target' => self::CANONICAL_SAMPLE_TARGET,
                'selection_target_reached' => count($selectedItems) >= self::CANONICAL_SAMPLE_TARGET,
                'evaluated_target_reached' => (int) ($canonical['picks_count'] ?? 0) >= self::CANONICAL_SAMPLE_TARGET,
                'price_evaluation_not_oos' => true,
                'catalog_deferred' => true,
            ],
        ];
    }

    private function applyQualityProfile(array $items, string $profile, array $paramset): array
    {
        $profile = strtoupper(trim($profile)) ?: self::DEFAULT_QUALITY_PROFILE;
        $items = array_values(array_filter($items, 'is_array'));
        if ($profile === self::DEFAULT_QUALITY_PROFILE) {
            return [
                'items' => $this->tagQualityStage($items, 'baseline', $profile),
                'removed_count' => 0,
                'removed_reason_counts' => [],
                'stage_counts' => [
                    'baseline_selected_count' => count($items),
                    'core_selected_count' => 0,
                    'backfill_selected_count' => 0,
                    'final_selected_count' => count($items),
                ],
                'selection_lineage' => ['baseline'],
            ];
        }

        if ($this->isHybridBackfillProfile($profile)) {
            return $this->applyHybridBackfillProfile($items, $profile, $paramset);
        }

        $kept = [];
        $removedReasons = [];
        foreach ($items as $item) {
            $reasons = $this->qualityProfileRejectReasons($item, $profile, $paramset);
            if ($reasons === []) {
                $kept[] = $item;
                continue;
            }
            $this->addReasonCounts($removedReasons, $reasons);
        }

        if ($profile === 'Q05_DOWNSIDE_AWARE_SCORE_120') {
            $kept = $this->rankQualityItems($kept);
            $kept = array_slice($kept, 0, min(max(self::CANONICAL_SAMPLE_TARGET, 120) + 5, count($kept)));
        } elseif ($profile === 'Q06_MONTHLY_QUALITY_CAP_120') {
            $kept = $this->monthlyQualityCap($kept, 4, self::CANONICAL_SAMPLE_TARGET);
        } else {
            $kept = $this->rankQualityItems($kept);
        }
        $kept = $this->tagQualityStage($kept, 'filtered', $profile);

        return [
            'items' => array_values($kept),
            'removed_count' => max(0, count($items) - count($kept)),
            'removed_reason_counts' => $this->topCounts($removedReasons, 20),
            'stage_counts' => [
                'baseline_selected_count' => count($items),
                'core_selected_count' => count($kept),
                'backfill_selected_count' => 0,
                'final_selected_count' => count($kept),
            ],
            'selection_lineage' => ['single_filter'],
        ];
    }

    private function isHybridBackfillProfile(string $profile): bool
    {
        return in_array($profile, [
            'Q07_NO_OVEREXTENSION_CORE_WITH_DOWNSIDE_BACKFILL_120',
            'Q08_NO_OVEREXTENSION_CORE_WITH_MONTHLY_FLEX_BACKFILL',
            'Q09_LOW_ATR_NEG_ROC20_CORE_WITH_NO_OVEREXTENSION_BACKFILL',
            'Q10_HYBRID_Q02_Q04_Q05_BACKFILL_125',
        ], true);
    }

    private function applyHybridBackfillProfile(array $items, string $profile, array $paramset): array
    {
        $core = [];
        $backfillPool = [];
        $removedReasons = [];
        foreach ($items as $item) {
            if ($this->hybridCoreAccepts($item, $profile, $paramset)) {
                $core[] = $item;
                continue;
            }
            $reasons = $this->hybridBackfillRejectReasons($item, $profile, $paramset);
            if ($reasons === []) {
                $backfillPool[] = $item;
                continue;
            }
            $this->addReasonCounts($removedReasons, $reasons);
        }

        $core = $this->rankQualityItems($core);
        $backfillPool = $this->rankHybridBackfillItems($backfillPool, $profile);
        $target = $this->hybridTargetCount($profile);
        $selected = [];
        $selectedKeys = [];
        $coreSelected = 0;
        $backfillSelected = 0;

        foreach ($core as $item) {
            if (count($selected) >= $target) {
                break;
            }
            $key = $this->qualityItemKey($item);
            if (isset($selectedKeys[$key])) {
                continue;
            }
            $selected[] = $this->tagQualityStageItem($item, 'core', $profile);
            $selectedKeys[$key] = true;
            $coreSelected++;
        }

        if ($profile === 'Q08_NO_OVEREXTENSION_CORE_WITH_MONTHLY_FLEX_BACKFILL') {
            $backfilled = $this->monthlyFlexBackfill($selected, $selectedKeys, $backfillPool, $target, 5);
            $selected = $backfilled['selected'];
            $selectedKeys = $backfilled['selected_keys'];
            $backfillSelected = $backfilled['backfill_selected_count'];
        } else {
            foreach ($backfillPool as $item) {
                if (count($selected) >= $target) {
                    break;
                }
                $key = $this->qualityItemKey($item);
                if (isset($selectedKeys[$key])) {
                    continue;
                }
                $selected[] = $this->tagQualityStageItem($item, 'backfill', $profile);
                $selectedKeys[$key] = true;
                $backfillSelected++;
            }
        }

        $selected = $this->rankQualityItems($selected);
        return [
            'items' => array_values($selected),
            'removed_count' => max(0, count($items) - count($selected)),
            'removed_reason_counts' => $this->topCounts($removedReasons, 20),
            'stage_counts' => [
                'baseline_selected_count' => count($items),
                'core_pool_count' => count($core),
                'backfill_pool_count' => count($backfillPool),
                'core_selected_count' => $coreSelected,
                'backfill_selected_count' => $backfillSelected,
                'final_selected_count' => count($selected),
                'target_selected_count' => $target,
            ],
            'selection_lineage' => [
                'strict_quality_core',
                'controlled_quality_backfill',
                'price_outcome_not_used_for_selection',
            ],
        ];
    }

    private function hybridCoreAccepts(array $item, string $profile, array $paramset): bool
    {
        if ($profile === 'Q09_LOW_ATR_NEG_ROC20_CORE_WITH_NO_OVEREXTENSION_BACKFILL') {
            return $this->qualityProfileRejectReasons($item, 'Q04_LOW_ATR_NEG_ROC20_PRIORITY', $paramset) === [];
        }
        if ($profile === 'Q10_HYBRID_Q02_Q04_Q05_BACKFILL_125') {
            return $this->qualityProfileRejectReasons($item, 'Q02_NO_SCORE_OVEREXTENSION_RECOVERY', $paramset) === []
                || $this->qualityProfileRejectReasons($item, 'Q04_LOW_ATR_NEG_ROC20_PRIORITY', $paramset) === [];
        }
        return $this->qualityProfileRejectReasons($item, 'Q02_NO_SCORE_OVEREXTENSION_RECOVERY', $paramset) === [];
    }

    private function hybridBackfillRejectReasons(array $item, string $profile, array $paramset): array
    {
        $failures = array_values(array_map('strval', is_array($item['current_extension_failures'] ?? null) ? $item['current_extension_failures'] : []));
        $metrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $score = $this->numericOrNull($item['score_total'] ?? null);
        $quality = $this->numericOrNull($item['quality_score'] ?? null);
        $penalty = $this->numericOrNull($item['penalty_total'] ?? null) ?? 0.0;
        $atr = $this->numericOrNull($metrics['atr14_pct'] ?? null);
        $roc20 = $this->numericOrNull($metrics['roc20'] ?? null);
        $reject = [];

        if ($quality !== null && $quality < 0.535) {
            $reject[] = 'Q5B_REJECT_BACKFILL_LOW_QUALITY_SCORE';
        }
        if ($penalty > 0.125) {
            $reject[] = 'Q5B_REJECT_BACKFILL_HIGH_PENALTY_TOTAL';
        }
        if ($score !== null && $score >= 0.90) {
            $reject[] = 'Q5B_REJECT_BACKFILL_EXTREME_SCORE_CHASE_ZONE';
        }
        if ($this->hasReason($failures, 'WATCHLIST_C17_ENTRY_QUALITY_FLOOR_FAIL') && $quality !== null && $quality < 0.575) {
            $reject[] = 'Q5B_REJECT_BACKFILL_ENTRY_QUALITY_FAIL_WITH_LOW_QUALITY';
        }

        if ($profile === 'Q07_NO_OVEREXTENSION_CORE_WITH_DOWNSIDE_BACKFILL_120') {
            if ($this->hasReason($failures, 'WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL') && $penalty > 0.075) {
                $reject[] = 'Q07_REJECT_HEAVY_SCORE_OVEREXTENSION_BACKFILL';
            }
            if ($atr !== null && $atr > 0.044) {
                $reject[] = 'Q07_REJECT_HIGH_ATR_BACKFILL';
            }
        } elseif ($profile === 'Q08_NO_OVEREXTENSION_CORE_WITH_MONTHLY_FLEX_BACKFILL') {
            if ($this->hasReason($failures, 'WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL') && $penalty > 0.085) {
                $reject[] = 'Q08_REJECT_HEAVY_OVEREXTENSION_MONTHLY_BACKFILL';
            }
        } elseif ($profile === 'Q09_LOW_ATR_NEG_ROC20_CORE_WITH_NO_OVEREXTENSION_BACKFILL') {
            if ($this->hasReason($failures, 'WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL')) {
                $reject[] = 'Q09_REJECT_SCORE_OVEREXTENSION_BACKFILL';
            }
            if ($atr !== null && $atr > 0.040) {
                $reject[] = 'Q09_REJECT_HIGH_ATR_BACKFILL';
            }
            if ($roc20 !== null && $roc20 > 0.050) {
                $reject[] = 'Q09_REJECT_ROC20_CHASE_BACKFILL';
            }
        } elseif ($profile === 'Q10_HYBRID_Q02_Q04_Q05_BACKFILL_125') {
            if ($penalty > 0.105) {
                $reject[] = 'Q10_REJECT_HIGH_PENALTY_BACKFILL';
            }
            if ($this->hasReason($failures, 'WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL') && $score !== null && $score >= 0.875) {
                $reject[] = 'Q10_REJECT_HIGH_SCORE_OVEREXTENSION_BACKFILL';
            }
        }

        return array_values(array_unique($reject));
    }

    private function hybridTargetCount(string $profile): int
    {
        if ($profile === 'Q10_HYBRID_Q02_Q04_Q05_BACKFILL_125') {
            return 125;
        }
        if ($profile === 'Q08_NO_OVEREXTENSION_CORE_WITH_MONTHLY_FLEX_BACKFILL') {
            return 132;
        }
        return 135;
    }

    private function rankHybridBackfillItems(array $items, string $profile): array
    {
        usort($items, function (array $left, array $right) use ($profile): int {
            $leftScore = $this->hybridBackfillRankScore($left, $profile);
            $rightScore = $this->hybridBackfillRankScore($right, $profile);
            if ($leftScore != $rightScore) {
                return $rightScore <=> $leftScore;
            }
            foreach ([
                ['penalty_total', true],
                ['quality_score', false],
                ['score_total', false],
                ['trade_date', true],
                ['ticker_id', true],
                ['ticker_code', true],
            ] as $sort) {
                [$key, $ascending] = $sort;
                $a = $left[$key] ?? '';
                $b = $right[$key] ?? '';
                if ($a == $b) {
                    continue;
                }
                $cmp = $a <=> $b;
                return $ascending ? $cmp : -$cmp;
            }
            return 0;
        });
        return array_values($items);
    }

    private function hybridBackfillRankScore(array $item, string $profile): float
    {
        $metrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $failures = array_values(array_map('strval', is_array($item['current_extension_failures'] ?? null) ? $item['current_extension_failures'] : []));
        $quality = $this->numericOrNull($item['quality_score'] ?? null) ?? 0.0;
        $score = $this->numericOrNull($item['score_total'] ?? null) ?? 0.0;
        $penalty = $this->numericOrNull($item['penalty_total'] ?? null) ?? 0.0;
        $atr = $this->numericOrNull($metrics['atr14_pct'] ?? null);
        $roc20 = $this->numericOrNull($metrics['roc20'] ?? null);
        $rank = ($quality * 2.0) + ($score * 0.35) - ($penalty * 2.5);
        if ($atr !== null) {
            $rank -= max(0.0, $atr - 0.030) * 6.0;
        }
        if ($roc20 !== null && $roc20 > 0.030) {
            $rank -= ($roc20 - 0.030) * 2.0;
        }
        if ($this->hasReason($failures, 'WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL')) {
            $rank -= $profile === 'Q10_HYBRID_Q02_Q04_Q05_BACKFILL_125' ? 0.10 : 0.16;
        }
        if ($this->hasReason($failures, 'WATCHLIST_C17_ENTRY_QUALITY_FLOOR_FAIL')) {
            $rank -= 0.12;
        }
        if ($this->hasReason($failures, 'WATCHLIST_C17_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL')) {
            $rank -= 0.05;
        }
        return $rank;
    }

    private function monthlyFlexBackfill(array $selected, array $selectedKeys, array $backfillPool, int $target, int $monthlyCap): array
    {
        $monthlyCount = [];
        foreach ($selected as $item) {
            $month = substr((string) ($item['trade_date'] ?? ''), 0, 7);
            if ($month !== '') {
                $monthlyCount[$month] = ($monthlyCount[$month] ?? 0) + 1;
            }
        }
        $backfillSelected = 0;
        foreach ($backfillPool as $item) {
            if (count($selected) >= $target) {
                break;
            }
            $month = substr((string) ($item['trade_date'] ?? ''), 0, 7);
            if ($month !== '' && ($monthlyCount[$month] ?? 0) >= $monthlyCap) {
                continue;
            }
            $key = $this->qualityItemKey($item);
            if (isset($selectedKeys[$key])) {
                continue;
            }
            $selected[] = $this->tagQualityStageItem($item, 'backfill', 'Q08_NO_OVEREXTENSION_CORE_WITH_MONTHLY_FLEX_BACKFILL');
            $selectedKeys[$key] = true;
            $monthlyCount[$month] = ($monthlyCount[$month] ?? 0) + 1;
            $backfillSelected++;
        }
        return [
            'selected' => $selected,
            'selected_keys' => $selectedKeys,
            'backfill_selected_count' => $backfillSelected,
        ];
    }

    private function tagQualityStage(array $items, string $stage, string $profile): array
    {
        return array_map(function (array $item) use ($stage, $profile): array {
            return $this->tagQualityStageItem($item, $stage, $profile);
        }, $items);
    }

    private function tagQualityStageItem(array $item, string $stage, string $profile): array
    {
        $item['_c19_quality_stage'] = $stage;
        $item['_c19_quality_profile'] = $profile;
        return $item;
    }

    private function qualityProfileRejectReasons(array $item, string $profile, array $paramset): array
    {
        $failures = array_values(array_map('strval', is_array($item['current_extension_failures'] ?? null) ? $item['current_extension_failures'] : []));
        $metrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $score = $this->numericOrNull($item['score_total'] ?? null);
        $quality = $this->numericOrNull($item['quality_score'] ?? null);
        $penalty = $this->numericOrNull($item['penalty_total'] ?? null) ?? 0.0;
        $reject = [];

        if ($profile === 'Q01_STRICT_ENTRY_QUALITY') {
            if ($this->hasReason($failures, 'WATCHLIST_C17_ENTRY_QUALITY_FLOOR_FAIL')) {
                $reject[] = 'Q01_REJECT_ENTRY_QUALITY_FLOOR_FAIL';
            }
            if ($quality !== null && $quality < 0.60) {
                $reject[] = 'Q01_REJECT_LOW_QUALITY_SCORE';
            }
        } elseif ($profile === 'Q02_NO_SCORE_OVEREXTENSION_RECOVERY') {
            if ($this->hasReason($failures, 'WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL')) {
                $reject[] = 'Q02_REJECT_SCORE_OVEREXTENSION_RECOVERY';
            }
            if ($score !== null && $score >= 0.85) {
                $reject[] = 'Q02_REJECT_HIGH_SCORE_CHASE_ZONE';
            }
        } elseif ($profile === 'Q03_PULLBACK_ROC_DISCIPLINE') {
            if ($this->hasReason($failures, 'WATCHLIST_C17_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL')) {
                $reject[] = 'Q03_REJECT_ROC5_PULLBACK_MISS';
            }
            if ($this->hasReason($failures, 'WATCHLIST_C17_ROC20_SEGMENT_RANGE_FAIL')) {
                $reject[] = 'Q03_REJECT_ROC20_SEGMENT_MISS';
            }
        } elseif ($profile === 'Q04_LOW_ATR_NEG_ROC20_PRIORITY') {
            $atr = $this->numericOrNull($metrics['atr14_pct'] ?? null);
            $roc20 = $this->numericOrNull($metrics['roc20'] ?? null);
            $maxAtr = $this->numericOrNull($paramset['risk']['max_atr14_pct'] ?? null) ?? 0.06;
            if ($atr === null || $atr > min($maxAtr, 0.032)) {
                $reject[] = 'Q04_REJECT_ATR_ABOVE_LOW_ATR_PRIORITY';
            }
            if ($roc20 === null || $roc20 > 0.030) {
                $reject[] = 'Q04_REJECT_ROC20_CHASE_PRIORITY';
            }
            if ($penalty > 0.08) {
                $reject[] = 'Q04_REJECT_TOO_MANY_RECOVERY_PENALTIES';
            }
        } elseif ($profile === 'Q05_DOWNSIDE_AWARE_SCORE_120') {
            if ($quality !== null && $quality < 0.56) {
                $reject[] = 'Q05_REJECT_LOW_QUALITY_SCORE';
            }
            if ($penalty > 0.10) {
                $reject[] = 'Q05_REJECT_HIGH_PENALTY_TOTAL';
            }
        } elseif ($profile === 'Q06_MONTHLY_QUALITY_CAP_120') {
            if ($quality !== null && $quality < 0.54) {
                $reject[] = 'Q06_REJECT_LOW_QUALITY_SCORE';
            }
        }

        return array_values(array_unique($reject));
    }

    private function rankQualityItems(array $items): array
    {
        usort($items, function (array $left, array $right): int {
            foreach ([
                ['quality_score', false],
                ['penalty_total', true],
                ['score_total', false],
                ['trade_date', true],
                ['ticker_id', true],
                ['ticker_code', true],
            ] as $sort) {
                [$key, $ascending] = $sort;
                $a = $left[$key] ?? '';
                $b = $right[$key] ?? '';
                if ($a == $b) {
                    continue;
                }
                $cmp = $a <=> $b;
                return $ascending ? $cmp : -$cmp;
            }
            return 0;
        });
        return array_values($items);
    }

    private function monthlyQualityCap(array $items, int $monthlyCap, int $target): array
    {
        $ranked = $this->rankQualityItems($items);
        $selected = [];
        $selectedKeys = [];
        $monthlyCount = [];
        foreach ($ranked as $item) {
            $month = substr((string) ($item['trade_date'] ?? ''), 0, 7);
            if ($month === '') {
                continue;
            }
            if (($monthlyCount[$month] ?? 0) >= $monthlyCap) {
                continue;
            }
            $key = $this->qualityItemKey($item);
            $selected[] = $item;
            $selectedKeys[$key] = true;
            $monthlyCount[$month] = ($monthlyCount[$month] ?? 0) + 1;
        }
        foreach ($ranked as $item) {
            if (count($selected) >= $target) {
                break;
            }
            $key = $this->qualityItemKey($item);
            if (isset($selectedKeys[$key])) {
                continue;
            }
            $selected[] = $item;
            $selectedKeys[$key] = true;
        }
        return array_values($selected);
    }

    private function qualityItemKey(array $item): string
    {
        return (string) ($item['trade_date'] ?? '').'|'.(string) ($item['ticker_id'] ?? '').'|'.(string) ($item['ticker_code'] ?? '');
    }

    private function addReasonCounts(array &$counts, array $reasons): void
    {
        foreach ($reasons as $reason) {
            $reason = (string) $reason;
            if ($reason === '') {
                continue;
            }
            $counts[$reason] = ($counts[$reason] ?? 0) + 1;
        }
    }

    private function topCounts(array $counts, int $limit): array
    {
        arsort($counts);
        $out = [];
        foreach (array_slice($counts, 0, $limit, true) as $reason => $count) {
            $out[] = ['reason_code' => (string) $reason, 'count' => (int) $count];
        }
        return $out;
    }

    private function hasReason(array $reasons, string $needle): bool
    {
        return in_array($needle, $reasons, true);
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
            $components = $this->normalizeScoreComponents(is_array($item['score_components'] ?? null) ? $item['score_components'] : []);
            $trades[] = [
                'trade_date' => $tradeDate,
                'ticker_id' => (int) ($item['ticker_id'] ?? 0),
                'ticker' => $ticker,
                'bucket_code' => 'TOP_PICKS',
                'plan_rank' => (int) ($item['proposed_rank'] ?? $rank),
                'recommendation_rank' => $rank,
                'recommendation_score' => $this->numericOrNull($item['quality_score'] ?? null),
                'score_total' => $this->numericOrNull($item['score_total'] ?? null),
                'score_components' => $components,
                'score_metrics' => $metrics,
                'factor_breakdown' => is_array($item['factor_breakdown'] ?? null) ? $item['factor_breakdown'] : [],
                'score_momentum' => $components['score_momentum'] ?? null,
                'score_breakout' => $components['score_breakout'] ?? null,
                'score_volume' => $components['score_volume'] ?? null,
                'score_risk' => $components['score_risk'] ?? null,
                'dv20_idr' => $this->numericOrNull($metrics['dv20_idr'] ?? null),
                'vol_ratio' => $this->numericOrNull($metrics['vol_ratio'] ?? null),
                'roc20' => $this->numericOrNull($metrics['roc20'] ?? null),
                'atr14_pct' => $this->numericOrNull($metrics['atr14_pct'] ?? null),
                'stop_price' => null,
                'target_price' => null,
                'stop_atr_mult' => $this->numericOrNull($paramset['risk']['stop_atr_mult'] ?? null),
                'min_rr' => $this->numericOrNull($paramset['risk']['min_rr'] ?? null),
                'confirm_state' => 'C19_PROPOSED_SELECTION_DIAGNOSTIC',
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
                    ['WS_C19_PROPOSED_SELECTION_SELECTED', 'WS_REC_SELECTED'],
                    is_array($item['current_extension_failures'] ?? null) ? $item['current_extension_failures'] : []
                ))),
                'source_reference' => [
                    'diagnostic_source' => 'C19_SELECTION_MODEL_REDESIGN_SELECTOR_SIMULATION',
                    'proposed_plan_group' => (string) ($item['proposed_plan_group'] ?? ''),
                    'penalty_total' => $this->numericOrNull($item['penalty_total'] ?? null),
                    'penalties' => is_array($item['penalties'] ?? null) ? $item['penalties'] : [],
                    'c19_quality_stage' => (string) ($item['_c19_quality_stage'] ?? ''),
                    'c19_quality_profile' => (string) ($item['_c19_quality_profile'] ?? ''),
                ],
                'contract_flags' => [
                    'from_recommendation_layer' => true,
                    'confirm_does_not_create_recommendation' => true,
                    'no_lookahead_price_used' => true,
                    'not_broker_surface' => true,
                    'c19_diagnostic_only' => true,
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

    private function normalizeScoreComponents(array $components): array
    {
        $out = [];
        foreach (['momentum', 'breakout', 'volume', 'risk'] as $key) {
            $out['score_'.$key] = $this->numericOrNull($components['score_'.$key] ?? $components[$key] ?? null);
        }
        return $out;
    }

    private function backtestPayload(array $trades, array $strategyTradeDates, array $paramset, array $row): array
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
        $diagnostics = [];
        foreach ($strategyTradeDates as $tradeDate) {
            if (! isset($tradeDatesWithRecommendations[$tradeDate])) {
                $diagnostics[] = [
                    'trade_date' => $tradeDate,
                    'reason_code' => 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID',
                    'reason_codes' => ['WS_REC_EMPTY_SET'],
                    'active_trade_evaluation' => false,
                    'c19_price_diagnostic_only' => true,
                ];
            }
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
                'engine' => 'WatchlistBacktestC19ProposedSelectionPriceDiagnosticService',
                'backtest_reason_code' => 'WATCHLIST_BACKTEST_FOUNDATION_READY',
                'not_production_ready' => true,
            ],
            'source_contract' => [
                'consumer' => 'WatchlistBacktestC19ProposedSelectionPriceDiagnosticService',
                'upstream' => ['C19 selector simulation', 'WatchlistBacktestRuntimeArtifactService'],
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
                'reason_codes' => ['WS_C19_PROPOSED_SELECTION_PRICE_DIAGNOSTIC'],
            ],
            'diagnostics' => $diagnostics,
            'artifact_manifest' => [
                'official_backtest_tables' => [
                    'watchlist_bt_param_grid',
                    'watchlist_bt_eval',
                    'watchlist_bt_picks_ws',
                    'watchlist_bt_universe_ws',
                    'watchlist_bt_cutoffs_ws',
                    'watchlist_bt_oos_eval_ws',
                ],
                'runtime_artifact_created' => false,
                'runtime_persistence_created' => false,
                'reason_codes' => ['WS_C19_PROPOSED_SELECTION_PRICE_DIAGNOSTIC'],
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

    private function monthlyEvaluatedDistribution(array $selectedItems, array $evaluatedTrades): array
    {
        $monthly = [];
        foreach ($selectedItems as $item) {
            $month = substr((string) ($item['trade_date'] ?? ''), 0, 7);
            if ($month === '') {
                continue;
            }
            $monthly[$month] = $monthly[$month] ?? [
                'month' => $month,
                'proposed_recommended_count' => 0,
                'evaluated_picks_count' => 0,
                'price_missing_count' => 0,
                'win_rate' => null,
                'avg_ret_net' => null,
            ];
            $monthly[$month]['proposed_recommended_count']++;
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
            $monthly[$month] = $monthly[$month] ?? [
                'month' => $month,
                'proposed_recommended_count' => 0,
                'evaluated_picks_count' => 0,
                'price_missing_count' => 0,
                'win_rate' => null,
                'avg_ret_net' => null,
            ];
            if (($trade['metrics_ready'] ?? false) === true && is_numeric($trade['ret_net'] ?? null)) {
                $monthly[$month]['evaluated_picks_count']++;
                $returns[$month][] = (float) $trade['ret_net'];
            } else {
                $monthly[$month]['price_missing_count']++;
            }
        }
        foreach ($returns as $month => $values) {
            $wins = count(array_filter($values, function (float $value): bool { return $value > 0; }));
            $monthly[$month]['win_rate'] = count($values) > 0 ? $wins / count($values) : null;
            $monthly[$month]['avg_ret_net'] = count($values) > 0 ? array_sum($values) / count($values) : null;
        }
        ksort($monthly, SORT_STRING);
        return array_values($monthly);
    }

    private function crossParamSummary(array $diagnostics): array
    {
        $summary = [
            'max_proposed_recommended_count' => 0,
            'max_requested_pairs_count' => 0,
            'max_evaluated_picks_count' => 0,
            'max_price_missing_count' => 0,
            'params_with_evaluated_sample_target_reached' => 0,
            'params_with_price_metrics_ready' => 0,
        ];
        foreach ($diagnostics as $diag) {
            $selection = $diag['selection_counts'] ?? [];
            $counts = $diag['price_evaluation_counts'] ?? [];
            $summary['max_proposed_recommended_count'] = max($summary['max_proposed_recommended_count'], (int) ($selection['proposed_recommended_count'] ?? 0));
            $summary['max_requested_pairs_count'] = max($summary['max_requested_pairs_count'], (int) ($counts['requested_pairs_count'] ?? 0));
            $summary['max_evaluated_picks_count'] = max($summary['max_evaluated_picks_count'], (int) ($counts['evaluated_picks_count'] ?? 0));
            $summary['max_price_missing_count'] = max($summary['max_price_missing_count'], (int) ($counts['price_missing_count'] ?? 0));
            if ((int) ($counts['evaluated_picks_count'] ?? 0) >= self::CANONICAL_SAMPLE_TARGET) {
                $summary['params_with_evaluated_sample_target_reached']++;
            }
            if (($diag['runtime_artifact_summary']['metrics_ready'] ?? false) === true) {
                $summary['params_with_price_metrics_ready']++;
            }
        }
        return $summary;
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
            'C19_STRATEGY_MODEL_REDESIGN' => true,
            'C19_NOT_CATALOG_CHURN' => true,
            'C19_PRICE_EVALUATION_DIAGNOSTIC_IMPLEMENTED' => true,
            'C19_PROPOSED_SELECTION_PRICE_EVALUATED' => true,
            'C19_QUALITY_RECOVERY_PROFILE_SUPPORTED' => true,
            'C19_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'C19_CATALOG_CODE' => 'NOT_CREATED',
            'C18_UNCHANGED' => true,
            'C01_TO_C18_IMMUTABLE' => true,
            'WATCHLIST_SCOPE_ONLY' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'no_best_of_failed_binding' => true,
            'no_ticker_blacklist' => true,
            'no_month_blacklist' => true,
            'no_sector_whitelist' => true,
            'canonical_evaluation_model_unchanged' => $this->canonicalEvaluationModel(),
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

    private function numericOrNull($value): ?float
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
            'c19_catalog_implementation_deferred' => 1,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }
}
