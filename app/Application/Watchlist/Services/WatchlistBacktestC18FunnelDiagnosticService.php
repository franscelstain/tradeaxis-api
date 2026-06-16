<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use RuntimeException;

class WatchlistBacktestC18FunnelDiagnosticService
{
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c18-funnel-diagnostic.json';
    public const ARTIFACT_TYPE = 'C18_FUNNEL_AND_MONTHLY_COVERAGE_DIAGNOSTIC';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;

    private MarketDataTradingCalendarReadService $calendar;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;
    private WatchlistCandidateUniverseService $candidateUniverse;
    private WatchlistScoringService $scoring;
    private WatchlistPlanGroupingService $planGrouping;
    private WatchlistRecommendationService $recommendation;
    private WatchlistBacktestPublishedPriceRuntimeService $runtime;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null,
        WatchlistCandidateUniverseService $candidateUniverse = null,
        WatchlistScoringService $scoring = null,
        WatchlistPlanGroupingService $planGrouping = null,
        WatchlistRecommendationService $recommendation = null,
        WatchlistBacktestPublishedPriceRuntimeService $runtime = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->paramGrid = $paramGrid ?: new WatchlistBacktestParamGridRepository();
        $this->paramsetFactory = $paramsetFactory ?: new WatchlistBacktestParamGridParamsetFactory();
        $this->candidateUniverse = $candidateUniverse ?: new WatchlistCandidateUniverseService();
        $this->scoring = $scoring ?: new WatchlistScoringService();
        $this->planGrouping = $planGrouping ?: new WatchlistPlanGroupingService();
        $this->recommendation = $recommendation ?: new WatchlistRecommendationService($this->planGrouping);
        $this->runtime = $runtime ?: new WatchlistBacktestPublishedPriceRuntimeService();
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode);
        $fromDate = trim($fromDate);
        $toDate = trim($toDate);
        $outputPath = trim($outputPath);

        if ($catalogCode === '') {
            $catalogCode = self::DEFAULT_SOURCE_CATALOG_CODE;
        }
        if (! $this->validDate($fromDate) || ! $this->validDate($toDate) || strcmp($fromDate, $toDate) > 0) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Explicit from/to date window is invalid.');
        }
        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked(
                strcmp($toDate, WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) > 0
                    ? 'WS_BT_C18_IS_BOUNDARY_VIOLATION'
                    : 'WS_BT_C18_IS_WINDOW_MISMATCH',
                'C18 funnel diagnostic requires the exact frozen IS window.'
            );
        }
        if ($outputPath === '' || is_dir($outputPath)) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Explicit non-directory output path is required.');
        }
        if (is_file($outputPath) && ! ($options['overwrite'] ?? false)) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Output file already exists. Use overwrite explicitly.');
        }

        $policyCode = (string) ($options['policy_code'] ?? 'WS');
        try {
            $rows = $this->paramGrid->allForCatalog($catalogCode, $policyCode);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }
        if ($rows === []) {
            return $this->blocked('WS_BT_C18_SOURCE_CATALOG_MISSING', 'No rows found for explicit source catalog_code.', [
                'catalog_code' => $catalogCode,
                'policy_code' => $policyCode,
            ]);
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? []);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C18_PARAM_IDS_INVALID', 'Optional param_ids must be positive integers.');
        }
        if ($paramIds !== []) {
            $allowed = array_fill_keys($paramIds, true);
            $rows = array_values(array_filter($rows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_C18_ROW_FILTER_NO_MATCH', 'No source catalog rows matched the explicit param filter.');
            }
        }

        usort($rows, function (array $left, array $right): int {
            $comparison = strcmp((string) ($left['row_code'] ?? ''), (string) ($right['row_code'] ?? ''));
            if ($comparison !== 0) {
                return $comparison;
            }

            return ((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0));
        });

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar is unavailable for C18 funnel diagnostic.', [
                'calendar' => $calendar,
            ]);
        }
        $tradeDates = $this->normalizeDateList($calendar['trade_dates'] ?? []);
        if ($tradeDates === []) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar returned no trade dates.');
        }

        $executedAt = (string) ($options['executed_at'] ?? ($toDate.'T23:59:59+07:00'));
        $perParam = [];
        foreach ($rows as $row) {
            try {
                $paramset = $this->paramsetFactory->make($row);
                $perParam[] = $this->diagnoseParam($row, $paramset, $fromDate, $toDate, $tradeDates, $executedAt, $options);
            } catch (\Throwable $e) {
                $perParam[] = $this->blockedParam($row, $this->reasonCode($e), $e->getMessage());
            }
        }

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'scope' => 'IS_ONLY_DIAGNOSTIC',
            'status' => 'C18_FUNNEL_DIAGNOSTIC_READY',
            'reason_code' => 'WS_BT_C18_FUNNEL_DIAGNOSTIC_READY',
            'generated_at' => $executedAt,
            'source_catalog_manifest' => $this->catalogManifest($rows, $catalogCode, $policyCode),
            'is_window_manifest' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'raw_trading_date_count' => count($tradeDates),
                'trade_date_hash' => $this->stableHash($tradeDates),
                'calendar_hash' => (string) ($calendar['calendar_hash'] ?? $this->stableHash($tradeDates)),
                'exact_frozen_is_window' => true,
            ],
            'diagnostic_contract' => [
                'phase' => 'C18_PHASE_A_DIAGNOSTIC_FIRST_FUNNEL_AUDIT',
                'not_catalog_iteration' => true,
                'source_catalog_for_diagnosis' => $catalogCode,
                'does_not_seed_catalog' => true,
                'does_not_promote_paramset' => true,
                'does_not_lower_canonical_gates' => true,
                'does_not_execute_oos' => true,
                'production_ready_remains_false' => true,
                'runtime_first_default' => true,
                'deep_funnel_enabled' => (bool) ($options['deep_funnel'] ?? false),
                'watchlist_scope_only' => true,
                'plan_recommendation_confirm_boundary_unchanged' => true,
            ],
            'per_param_diagnostics' => $perParam,
            'cross_param_summary' => $this->crossParamSummary($perParam),
            'c18_catalog_decision' => $this->catalogDecision($perParam),
            'safety_boundaries' => [
                'c17_unchanged' => true,
                'c01_to_c17_immutable' => true,
                'oos_service_invoked' => false,
                'oos_repository_invoked' => false,
                'oos_executed' => false,
                'production_ready' => false,
                'best_of_failed_binding' => false,
                'ticker_blacklist' => false,
                'month_blacklist' => false,
                'sector_whitelist' => false,
                'canonical_gate_lowered' => false,
            ],
            'operator_validation_required' => [
                'phpunit_filter' => 'WatchlistBacktestC18Funnel',
                'diagnostic_command' => 'php artisan watchlist:backtest-c18-funnel-diagnose --catalog-code='.$catalogCode.' --from='.$fromDate.' --to='.$toDate.' --output='.$outputPath.' --overwrite',
                'full_watchlist_phpunit' => 'vendor\\bin\\phpunit tests\\Unit\\Watchlist',
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['canonical_artifact_hash'] = $artifact['artifact_hash'];

        $write = $this->writeJsonArtifact($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'C18 funnel diagnostic artifact write failed.', [
                'artifact' => $artifact,
                'write' => $write,
            ]);
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C18_FUNNEL_DIAGNOSTIC_READY',
            'catalog_code' => $catalogCode,
            'diagnostic_param_count' => count($perParam),
            'artifact_hash' => $artifact['artifact_hash'],
            'artifact' => $artifact,
            'write' => $write,
            'oos_executed' => false,
            'production_ready' => false,
        ];
    }

    private function diagnoseParam(array $row, array $paramset, string $fromDate, string $toDate, array $tradeDates, string $executedAt, array $options = []): array
    {
        $holdingDays = max(1, (int) ($paramset['backtest']['holding_days'] ?? 5));
        $strategyTradeDates = count($tradeDates) > $holdingDays
            ? array_slice($tradeDates, 0, count($tradeDates) - $holdingDays)
            : [];

        $deepFunnel = (bool) ($options['deep_funnel'] ?? false);
        $progressEvery = max(1, (int) ($options['progress_every'] ?? 25));
        $progressCallback = $options['progress_callback'] ?? null;
        $rowLabel = (string) ($row['row_code'] ?? ('param_'.(string) ($row['param_id'] ?? '')));
        $funnel = $this->emptyFunnel($tradeDates, $strategyTradeDates, $holdingDays);
        $funnel['deep_funnel_enabled'] = $deepFunnel;
        $funnel['deep_funnel_status'] = $deepFunnel
            ? 'RUNNING_PER_DATE_CANDIDATE_SCORING_GROUPING_DIAGNOSTIC'
            : 'SKIPPED_BY_DEFAULT_RUNTIME_FIRST_DIAGNOSTIC';
        $funnel['deep_funnel_required_for_filter_drop_reason_distribution'] = ! $deepFunnel;
        $funnel['progress_every'] = $progressEvery;
        $monthly = $this->monthSkeleton($strategyTradeDates);
        $dropReasons = [];
        $filterDropReasons = [];
        $sourceNotReady = [];

        if ($deepFunnel && is_callable($progressCallback)) {
            $progressCallback('diagnosing_param_id='.(string) ($row['param_id'] ?? 0).' row_code='.$rowLabel.' mode=deep_funnel trade_dates='.count($strategyTradeDates));
        }

        if ($deepFunnel) {
            $tradeDateIndex = 0;
            foreach ($strategyTradeDates as $tradeDate) {
                $tradeDateIndex++;
                if (is_callable($progressCallback) && ($tradeDateIndex === 1 || $tradeDateIndex % $progressEvery === 0 || $tradeDateIndex === count($strategyTradeDates))) {
                    $progressCallback('diagnosing_param_id='.(string) ($row['param_id'] ?? 0).' trade_date_progress='.$tradeDateIndex.'/'.count($strategyTradeDates).' trade_date='.$tradeDate);
                }
            $month = substr($tradeDate, 0, 7);
            if (! isset($monthly[$month])) {
                $monthly[$month] = $this->emptyMonth($month);
            }

            $universe = $this->candidateUniverse->buildCandidateUniverseForTradeDate($tradeDate, $paramset);
            if (! ($universe['is_ready'] ?? false)) {
                $sourceNotReady[] = [
                    'trade_date' => $tradeDate,
                    'stage' => 'candidate_universe',
                    'reason_code' => (string) ($universe['reason_code'] ?? 'WATCHLIST_CANDIDATE_UNIVERSE_NOT_READY'),
                ];
                $this->incrementReason($dropReasons, (string) ($universe['reason_code'] ?? 'WATCHLIST_CANDIDATE_UNIVERSE_NOT_READY'));
                continue;
            }
            $inputCount = (int) ($universe['input_candidate_count'] ?? count($universe['universe_rows'] ?? []));
            $eligibleCount = (int) ($universe['eligible_count'] ?? count($universe['eligible_candidates'] ?? []));
            $rejectedCount = (int) ($universe['rejected_count'] ?? count($universe['rejected_candidates'] ?? []));
            $funnel['raw_ticker_date_candidate_count'] += $inputCount;
            $funnel['after_candidate_universe_filter_count'] += $eligibleCount;
            $funnel['candidate_universe_rejected_count'] += $rejectedCount;
            $monthly[$month]['raw_candidate_count'] += $inputCount;
            $monthly[$month]['candidate_universe_eligible_count'] += $eligibleCount;
            $this->collectReasonCounts($dropReasons, $universe['reason_counts'] ?? []);
            $this->collectRejectedReasons($filterDropReasons, $universe['rejected_candidates'] ?? []);

            $scored = $this->scoring->scoreCandidateUniverse($universe, $paramset, $tradeDate);
            if (! ($scored['is_ready'] ?? $scored['ready'] ?? false)) {
                $sourceNotReady[] = [
                    'trade_date' => $tradeDate,
                    'stage' => 'scoring',
                    'reason_code' => (string) ($scored['reason_code'] ?? 'WATCHLIST_SCORING_NOT_READY'),
                ];
                $this->incrementReason($dropReasons, (string) ($scored['reason_code'] ?? 'WATCHLIST_SCORING_NOT_READY'));
                continue;
            }
            $scoredCount = (int) ($scored['summary']['scored_count'] ?? count($scored['items'] ?? []));
            $scoringExcluded = (int) ($scored['summary']['excluded_count'] ?? count($scored['excluded'] ?? []));
            $funnel['after_score_runtime_guard_count'] += $scoredCount;
            $funnel['scoring_excluded_count'] += $scoringExcluded;
            $monthly[$month]['scored_count'] += $scoredCount;
            $this->collectRejectedReasons($filterDropReasons, $scored['excluded'] ?? []);

            $grouped = $this->planGrouping->groupScoredOutput($scored, $paramset, $tradeDate);
            if (! ($grouped['is_ready'] ?? $grouped['ready'] ?? false)) {
                $sourceNotReady[] = [
                    'trade_date' => $tradeDate,
                    'stage' => 'plan_grouping',
                    'reason_code' => (string) ($grouped['reason_code'] ?? 'WATCHLIST_PLAN_GROUPING_NOT_READY'),
                ];
                $this->incrementReason($dropReasons, (string) ($grouped['reason_code'] ?? 'WATCHLIST_PLAN_GROUPING_NOT_READY'));
                continue;
            }

            $topCount = count($grouped['groups']['TOP_PICKS'] ?? []);
            $secondaryCount = count($grouped['groups']['SECONDARY'] ?? []);
            $watchOnlyCount = count($grouped['groups']['WATCH_ONLY'] ?? []);
            $avoidCount = count($grouped['groups']['AVOID'] ?? []);
            $groupExcluded = count($grouped['excluded'] ?? []);
            $funnel['after_grouping_top_picks_count'] += $topCount;
            $funnel['after_grouping_secondary_count'] += $secondaryCount;
            $funnel['after_grouping_watch_only_count'] += $watchOnlyCount;
            $funnel['after_grouping_active_count'] += $topCount + $secondaryCount;
            $funnel['grouping_avoid_count'] += $avoidCount;
            $funnel['grouping_excluded_count'] += $groupExcluded;
            $monthly[$month]['top_picks_count'] += $topCount;
            $monthly[$month]['secondary_count'] += $secondaryCount;
            $this->collectRejectedReasons($filterDropReasons, $grouped['excluded'] ?? []);

            $recommended = $this->recommendation->recommendFromPlanOutput($grouped, $paramset, []);
            if ($recommended['is_ready'] ?? $recommended['ready'] ?? false) {
                $recommendedCount = (int) ($recommended['summary']['recommended_count'] ?? 0);
                $sourcePlanCount = (int) ($recommended['summary']['source_plan_item_count'] ?? 0);
                $funnel['recommendation_source_plan_item_count'] += $sourcePlanCount;
                $funnel['recommended_count_before_price_evaluation'] += $recommendedCount;
                $monthly[$month]['recommended_count_before_price_evaluation'] += $recommendedCount;
            } else {
                $this->incrementReason($dropReasons, (string) ($recommended['reason_code'] ?? 'WATCHLIST_RECOMMENDATION_NOT_READY'));
            }
        }
        }

        if ($deepFunnel && is_callable($progressCallback)) {
            $progressCallback('diagnosing_param_id='.(string) ($row['param_id'] ?? 0).' row_code='.$rowLabel.' deep_funnel_done=1');
        }

        $runtime = $this->runtime->evaluateWindow($fromDate, $toDate, [
            'paramset' => $paramset,
            'hard_market_data_to_date' => $toDate,
            'executed_at' => $executedAt,
        ]);
        $runtimeSummary = $this->runtimeSummary($runtime, $toDate);
        $monthlyEvaluated = $this->monthlyEvaluatedDistribution($runtime, $monthly, $runtimeSummary['effective_thresholds']);
        $priceDropReasons = $this->runtimeDropReasons($runtime);
        foreach ($priceDropReasons as $reason => $count) {
            $filterDropReasons[$reason] = ($filterDropReasons[$reason] ?? 0) + $count;
        }

        $topDropContributors = $this->topDropContributors($filterDropReasons);
        $funnel['requested_ticker_date_pair_count'] = $runtimeSummary['requested_ticker_date_pair_count'];
        $funnel['evaluated_picks_count'] = $runtimeSummary['evaluated_picks_count'];
        $funnel['boundary_censored_count'] = $runtimeSummary['boundary_censored_count'];
        $funnel['period_count'] = $runtimeSummary['period_count'];
        $funnel['period_fail_count'] = $runtimeSummary['period_fail_count'];

        return [
            'param_id' => (int) ($row['param_id'] ?? 0),
            'row_code' => (string) ($row['row_code'] ?? ''),
            'catalog_code' => (string) ($row['catalog_code'] ?? ''),
            'catalog_version' => (string) ($row['catalog_version'] ?? ''),
            'catalog_hash' => (string) ($row['catalog_hash'] ?? ''),
            'status' => ($runtimeSummary['runtime_ready'] ? 'DIAGNOSTIC_READY' : 'RUNTIME_DIAGNOSTIC_BLOCKED'),
            'reason_code' => ($runtimeSummary['runtime_ready'] ? 'WS_BT_C18_PARAM_FUNNEL_DIAGNOSTIC_READY' : $runtimeSummary['reason_code']),
            'funnel_counts' => $funnel,
            'monthly_pick_distribution' => array_values($monthly),
            'monthly_win_rate_distribution' => $monthlyEvaluated['win_rate_distribution'],
            'monthly_average_return_distribution' => $monthlyEvaluated['average_return_distribution'],
            'monthly_empty_or_failed_periods' => $monthlyEvaluated['empty_or_failed_periods'],
            'filter_drop_reason_distribution' => $this->sortAssocDesc($filterDropReasons),
            'drop_reason_category_distribution' => $this->dropReasonCategories($filterDropReasons),
            'top_drop_contributors' => $topDropContributors,
            'runtime_evaluation_summary' => $runtimeSummary,
            'source_not_ready_samples' => array_slice($sourceNotReady, 0, 25),
            'root_cause_signals' => $this->rootCauseSignals($funnel, $runtimeSummary, $monthlyEvaluated, $paramset),
        ];
    }

    private function emptyFunnel(array $tradeDates, array $strategyTradeDates, int $holdingDays): array
    {
        return [
            'raw_trading_dates_count' => count($tradeDates),
            'strategy_trade_dates_count' => count($strategyTradeDates),
            'holding_days' => $holdingDays,
            'raw_ticker_date_candidate_count' => 0,
            'after_candidate_universe_filter_count' => 0,
            'candidate_universe_rejected_count' => 0,
            'after_score_runtime_guard_count' => 0,
            'scoring_excluded_count' => 0,
            'after_grouping_top_picks_count' => 0,
            'after_grouping_secondary_count' => 0,
            'after_grouping_watch_only_count' => 0,
            'after_grouping_active_count' => 0,
            'grouping_avoid_count' => 0,
            'grouping_excluded_count' => 0,
            'recommendation_source_plan_item_count' => 0,
            'recommended_count_before_price_evaluation' => 0,
            'requested_ticker_date_pair_count' => 0,
            'evaluated_picks_count' => 0,
            'boundary_censored_count' => count($tradeDates) - count($strategyTradeDates),
            'period_count' => 0,
            'period_fail_count' => 0,
        ];
    }

    private function runtimeSummary(array $runtime, string $toDate): array
    {
        $artifact = is_array($runtime['artifact'] ?? null) ? $runtime['artifact'] : [];
        $metrics = is_array($artifact['metrics'] ?? null) ? $artifact['metrics'] : [];
        $canonical = is_array($metrics['canonical_eval_metrics'] ?? null) ? $metrics['canonical_eval_metrics'] : [];
        $sufficiency = is_array($metrics['metric_sufficiency'] ?? null) ? $metrics['metric_sufficiency'] : [];
        $runtimeExecution = is_array($artifact['runtime_execution'] ?? null) ? $artifact['runtime_execution'] : [];
        $counts = is_array($metrics['counts'] ?? null) ? $metrics['counts'] : [];

        return [
            'runtime_ready' => (bool) ($runtime['is_ready'] ?? false),
            'reason_code' => (string) ($runtime['reason_code'] ?? 'WATCHLIST_BACKTEST_RUNTIME_NOT_READY'),
            'artifact_hash' => (string) ($runtime['artifact_hash'] ?? ($artifact['validation']['artifact_hash'] ?? '')),
            'strict_is_boundary' => (bool) ($runtimeExecution['strict_is_boundary'] ?? false),
            'hard_market_data_to_date' => (string) ($runtimeExecution['hard_market_data_to_date'] ?? $toDate),
            'max_requested_market_data_date' => (string) ($runtimeExecution['max_requested_market_data_date'] ?? ''),
            'requested_ticker_date_pair_count' => (int) ($runtimeExecution['requested_ticker_date_pair_count'] ?? 0),
            'trade_candidate_count' => (int) ($runtimeExecution['trade_candidate_count'] ?? 0),
            'required_price_date_count' => (int) ($runtimeExecution['required_price_date_count'] ?? 0),
            'ticker_count' => (int) ($runtimeExecution['ticker_count'] ?? 0),
            'boundary_censored_count' => (int) ($runtimeExecution['boundary_censored_trade_date_count'] ?? 0),
            'evaluated_picks_count' => (int) ($canonical['picks_count'] ?? ($counts['total_evaluated_trades'] ?? 0)),
            'days_covered' => (int) ($canonical['days_covered'] ?? 0),
            'period_count' => (int) ($canonical['periods_count'] ?? 0),
            'period_fail_count' => (int) ($canonical['period_fail_count'] ?? 0),
            'metrics' => $canonical,
            'effective_thresholds' => is_array($sufficiency['effective_thresholds'] ?? null)
                ? $sufficiency['effective_thresholds']
                : (is_array($sufficiency['gating_thresholds'] ?? null) ? $sufficiency['gating_thresholds'] : []),
            'gates' => is_array($sufficiency['gates'] ?? null) ? $sufficiency['gates'] : [],
            'reason_codes' => $this->runtimeReasonCodes($runtime, $sufficiency),
            'oos_executed' => false,
            'production_ready' => false,
        ];
    }

    private function monthlyEvaluatedDistribution(array $runtime, array $monthSkeleton, array $thresholds): array
    {
        $artifact = is_array($runtime['artifact'] ?? null) ? $runtime['artifact'] : [];
        $metrics = is_array($artifact['metrics'] ?? null) ? $artifact['metrics'] : [];
        $evaluated = is_array($metrics['evaluated_trades'] ?? null)
            ? $metrics['evaluated_trades']
            : (is_array($artifact['evaluations'] ?? null) ? $artifact['evaluations'] : []);

        $monthReturns = [];
        foreach ($evaluated as $evaluation) {
            if (! is_array($evaluation) || ! ($evaluation['metrics_ready'] ?? false)) {
                continue;
            }
            $bucket = strtoupper((string) ($evaluation['bucket_code'] ?? 'TOP_PICKS'));
            if (! in_array($bucket, ['TOP', 'TOP_PICKS'], true)) {
                continue;
            }
            if (! isset($evaluation['ret_net']) || ! is_numeric($evaluation['ret_net'])) {
                continue;
            }
            $tradeDate = (string) ($evaluation['trade_date'] ?? '');
            if (strlen($tradeDate) < 7) {
                continue;
            }
            $month = substr($tradeDate, 0, 7);
            if (! isset($monthReturns[$month])) {
                $monthReturns[$month] = [];
            }
            $monthReturns[$month][] = (float) $evaluation['ret_net'];
        }

        $allMonths = array_unique(array_merge(array_keys($monthSkeleton), array_keys($monthReturns)));
        sort($allMonths, SORT_STRING);
        $winRates = [];
        $averages = [];
        $failed = [];
        $minWinRate = $this->floatOrNull($thresholds['min_month_win_rate_min'] ?? null);
        $minAverage = $this->floatOrNull($thresholds['min_month_avg_ret_net_min'] ?? null);

        foreach ($allMonths as $month) {
            $returns = $monthReturns[$month] ?? [];
            $wins = count(array_filter($returns, function (float $value): bool {
                return $value > 0;
            }));
            $count = count($returns);
            $winRate = $count > 0 ? $wins / $count : null;
            $average = $count > 0 ? array_sum($returns) / $count : null;
            $winRates[] = [
                'month' => $month,
                'evaluated_pick_count' => $count,
                'win_rate' => $winRate,
                'status' => $count > 0 ? 'HAS_EVALUATED_PICKS' : 'EMPTY_MONTH_AFTER_EVALUATION',
            ];
            $averages[] = [
                'month' => $month,
                'evaluated_pick_count' => $count,
                'avg_ret_net' => $average,
                'status' => $count > 0 ? 'HAS_EVALUATED_PICKS' : 'EMPTY_MONTH_AFTER_EVALUATION',
            ];
            if ($count === 0
                || ($minWinRate !== null && $winRate !== null && $winRate < $minWinRate)
                || ($minAverage !== null && $average !== null && $average < $minAverage)) {
                $failed[] = [
                    'month' => $month,
                    'evaluated_pick_count' => $count,
                    'win_rate' => $winRate,
                    'avg_ret_net' => $average,
                    'reason_code' => $count === 0 ? 'WS_BT_C18_MONTH_EMPTY_AFTER_EVALUATION' : 'WS_BT_C18_MONTH_STABILITY_FAIL',
                ];
            }
        }

        return [
            'win_rate_distribution' => $winRates,
            'average_return_distribution' => $averages,
            'empty_or_failed_periods' => $failed,
        ];
    }

    private function rootCauseSignals(array $funnel, array $runtimeSummary, array $monthlyEvaluated, array $paramset): array
    {
        $raw = max(0, (int) $funnel['raw_ticker_date_candidate_count']);
        $eligible = max(0, (int) $funnel['after_candidate_universe_filter_count']);
        $scored = max(0, (int) $funnel['after_score_runtime_guard_count']);
        $active = max(0, (int) $funnel['after_grouping_active_count']);
        $recommended = max(0, (int) $funnel['recommended_count_before_price_evaluation']);
        $evaluated = max(0, (int) $runtimeSummary['evaluated_picks_count']);
        $minTrades = (int) ($runtimeSummary['effective_thresholds']['min_trades'] ?? 120);
        $strategyDates = max(1, (int) $funnel['strategy_trade_dates_count']);
        $topCap = $strategyDates * max(1, (int) ($paramset['grouping']['top_picks']['max_items'] ?? 1));
        $recCap = $strategyDates * max(1, (int) ($paramset['recommendation']['max_recommended_items'] ?? 1));
        $emptyMonths = count(array_filter($monthlyEvaluated['empty_or_failed_periods'], function (array $row): bool {
            return ($row['reason_code'] ?? '') === 'WS_BT_C18_MONTH_EMPTY_AFTER_EVALUATION';
        }));

        return [
            'raw_candidate_insufficient_for_min_trades' => $raw < $minTrades,
            'candidate_universe_drop_rate' => $raw > 0 ? ($raw - $eligible) / $raw : null,
            'score_runtime_guard_drop_rate' => $eligible > 0 ? ($eligible - $scored) / $eligible : null,
            'grouping_runtime_guard_drop_rate' => $scored > 0 ? ($scored - $active) / $scored : null,
            'recommendation_selection_drop_rate' => $active > 0 ? ($active - $recommended) / $active : null,
            'price_or_evaluation_drop_rate' => $recommended > 0 ? ($recommended - $evaluated) / $recommended : null,
            'top_picks_capacity_bind_suspected' => $active >= $topCap && $topCap > 0,
            'recommendation_cap_bind_suspected' => $recommended >= $recCap && $recCap > 0,
            'monthly_empty_period_count' => $emptyMonths,
            'monthly_distribution_unhealthy' => count($monthlyEvaluated['empty_or_failed_periods']) > 0,
            'min_trades_threshold' => $minTrades,
            'evaluated_to_min_trades_ratio' => $minTrades > 0 ? $evaluated / $minTrades : null,
        ];
    }

    private function crossParamSummary(array $perParam): array
    {
        $maxEvaluated = null;
        $maxRecommended = null;
        $bestEvaluated = null;
        $dropReasons = [];
        $emptyMonthParamCount = 0;
        foreach ($perParam as $param) {
            if (($param['status'] ?? '') === 'BLOCKED') {
                continue;
            }
            $funnel = is_array($param['funnel_counts'] ?? null) ? $param['funnel_counts'] : [];
            $evaluated = (int) ($funnel['evaluated_picks_count'] ?? 0);
            $recommended = (int) ($funnel['recommended_count_before_price_evaluation'] ?? 0);
            if ($maxEvaluated === null || $evaluated > $maxEvaluated) {
                $maxEvaluated = $evaluated;
                $bestEvaluated = [
                    'param_id' => (int) ($param['param_id'] ?? 0),
                    'row_code' => (string) ($param['row_code'] ?? ''),
                    'evaluated_picks_count' => $evaluated,
                    'recommended_count_before_price_evaluation' => $recommended,
                ];
            }
            if ($maxRecommended === null || $recommended > $maxRecommended) {
                $maxRecommended = $recommended;
            }
            foreach (($param['filter_drop_reason_distribution'] ?? []) as $reason => $count) {
                $dropReasons[$reason] = ($dropReasons[$reason] ?? 0) + (int) $count;
            }
            if ((int) ($param['root_cause_signals']['monthly_empty_period_count'] ?? 0) > 0) {
                $emptyMonthParamCount++;
            }
        }

        return [
            'diagnostic_param_count' => count($perParam),
            'max_evaluated_picks_count' => $maxEvaluated ?? 0,
            'max_recommended_count_before_price_evaluation' => $maxRecommended ?? 0,
            'best_sample_row' => $bestEvaluated,
            'params_with_empty_evaluation_months' => $emptyMonthParamCount,
            'aggregate_filter_drop_reason_distribution' => $this->sortAssocDesc($dropReasons),
            'aggregate_top_drop_contributors' => $this->topDropContributors($dropReasons),
            'c17_baseline_context' => [
                'known_c17_max_picks_count' => 42,
                'known_c17_min_trades_threshold' => 120,
                'known_c17_failed_min_trades_count' => 12,
                'known_c17_failed_stability_count' => 12,
                'known_c17_downside_failure_count' => 0,
                'artifact_source' => 'docs/watchlist/audit/_artifacts/c17-final-evidence-summary.json',
            ],
        ];
    }

    private function catalogDecision(array $perParam): array
    {
        $summary = $this->crossParamSummary($perParam);
        $maxEvaluated = (int) ($summary['max_evaluated_picks_count'] ?? 0);
        $paramsWithEmptyMonths = (int) ($summary['params_with_empty_evaluation_months'] ?? 0);
        $canSupportCatalog = $maxEvaluated >= 120 && $paramsWithEmptyMonths === 0;

        return [
            'diagnostic_first_completed_by_source' => true,
            'catalog_implementation_deferred' => ! $canSupportCatalog,
            'status' => $canSupportCatalog
                ? 'C18_CATALOG_REVIEW_CAN_PROCEED_AFTER_OPERATOR_EVIDENCE_REVIEW'
                : 'C18_CATALOG_IMPLEMENTATION_DEFERRED',
            'reason_code' => $canSupportCatalog
                ? 'WS_BT_C18_DIAGNOSTIC_EVIDENCE_REVIEW_REQUIRED'
                : 'WS_BT_C18_CATALOG_DEFERRED_UNTIL_FUNNEL_ROOT_CAUSE_VALIDATED',
            'rationale' => $canSupportCatalog
                ? 'Diagnostic output may support a controlled catalog only after operator reviews funnel and monthly evidence; no catalog is created by this diagnostic command.'
                : 'Diagnostic output does not yet justify creating a C18 catalog inside this source session; operator must validate raw candidate, runtime guard, grouping, price-read, and monthly root cause first.',
            'oos_allowed' => false,
            'production_ready' => false,
        ];
    }

    private function runtimeDropReasons(array $runtime): array
    {
        $artifact = is_array($runtime['artifact'] ?? null) ? $runtime['artifact'] : [];
        $metrics = is_array($artifact['metrics'] ?? null) ? $artifact['metrics'] : [];
        $distribution = is_array($metrics['reason_code_distribution'] ?? null) ? $metrics['reason_code_distribution'] : [];
        $result = [];
        foreach ($distribution as $reason => $count) {
            if (is_string($reason) && is_numeric($count)) {
                $result[$reason] = (int) $count;
            }
        }
        foreach (array_merge($runtime['diagnostics'] ?? [], $artifact['diagnostics'] ?? []) as $diagnostic) {
            if (is_array($diagnostic) && isset($diagnostic['reason_code'])) {
                $this->incrementReason($result, (string) $diagnostic['reason_code']);
            }
        }

        return $result;
    }

    private function runtimeReasonCodes(array $runtime, array $sufficiency): array
    {
        $codes = [];
        if (isset($runtime['reason_code'])) {
            $codes[] = (string) $runtime['reason_code'];
        }
        foreach (($sufficiency['reason_codes'] ?? []) as $code) {
            if (is_scalar($code)) {
                $codes[] = (string) $code;
            }
        }
        foreach (($sufficiency['failure_reason_codes'] ?? []) as $code) {
            if (is_scalar($code)) {
                $codes[] = (string) $code;
            }
        }

        return array_values(array_unique(array_filter($codes)));
    }

    private function collectRejectedReasons(array &$result, array $items): void
    {
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            foreach (($item['reason_codes'] ?? []) as $reason) {
                if (is_scalar($reason)) {
                    $this->incrementReason($result, (string) $reason);
                }
            }
            if (isset($item['group_reason_code']) && is_scalar($item['group_reason_code'])) {
                $this->incrementReason($result, (string) $item['group_reason_code']);
            }
            if (isset($item['canonical_fail_reason_code']) && is_scalar($item['canonical_fail_reason_code'])) {
                $this->incrementReason($result, (string) $item['canonical_fail_reason_code']);
            }
        }
    }

    private function collectReasonCounts(array &$result, array $reasonCounts): void
    {
        foreach ($reasonCounts as $reason => $count) {
            if (is_string($reason) && is_numeric($count)) {
                $result[$reason] = ($result[$reason] ?? 0) + (int) $count;
            }
        }
    }

    private function topDropContributors(array $reasonDistribution): array
    {
        $categories = $this->dropReasonCategories($reasonDistribution);
        arsort($categories, SORT_NUMERIC);
        $result = [];
        foreach ($categories as $category => $count) {
            $result[] = [
                'contributor' => $category,
                'count' => (int) $count,
            ];
        }

        return array_slice($result, 0, 12);
    }

    private function dropReasonCategories(array $reasonDistribution): array
    {
        $categories = [
            'score_window' => 0,
            'roc5_guard' => 0,
            'roc20_guard' => 0,
            'atr_guard' => 0,
            'dv20_guard' => 0,
            'volume_guard' => 0,
            'close_to_hh20_or_breakout_extension_guard' => 0,
            'low_atr_negative_roc20_dependency' => 0,
            'one_r_dependency' => 0,
            'grouping_cutoff' => 0,
            'price_availability_or_boundary_censoring' => 0,
            'other' => 0,
        ];
        foreach ($reasonDistribution as $reason => $count) {
            $reasonUpper = strtoupper((string) $reason);
            $count = (int) $count;
            if (strpos($reasonUpper, 'LOW_ATR') !== false || strpos($reasonUpper, 'NEG_ROC20') !== false) {
                $categories['low_atr_negative_roc20_dependency'] += $count;
            } elseif (strpos($reasonUpper, 'ONE_R') !== false) {
                $categories['one_r_dependency'] += $count;
            } elseif (strpos($reasonUpper, 'SCORE') !== false) {
                $categories['score_window'] += $count;
            } elseif (strpos($reasonUpper, 'ROC5') !== false) {
                $categories['roc5_guard'] += $count;
            } elseif (strpos($reasonUpper, 'ROC20') !== false || strpos($reasonUpper, 'ROC_REGIME') !== false) {
                $categories['roc20_guard'] += $count;
            } elseif (strpos($reasonUpper, 'ATR') !== false) {
                $categories['atr_guard'] += $count;
            } elseif (strpos($reasonUpper, 'DV20') !== false || strpos($reasonUpper, 'LIQ') !== false) {
                $categories['dv20_guard'] += $count;
            } elseif (strpos($reasonUpper, 'VOL') !== false) {
                $categories['volume_guard'] += $count;
            } elseif (strpos($reasonUpper, 'HH20') !== false || strpos($reasonUpper, 'BREAKOUT') !== false || strpos($reasonUpper, 'BO_') !== false) {
                $categories['close_to_hh20_or_breakout_extension_guard'] += $count;
            } elseif (strpos($reasonUpper, 'PLAN_AVOID') !== false || strpos($reasonUpper, 'GROUP') !== false) {
                $categories['grouping_cutoff'] += $count;
            } elseif (strpos($reasonUpper, 'PRICE') !== false || strpos($reasonUpper, 'OHLC') !== false || strpos($reasonUpper, 'BOUNDARY') !== false || strpos($reasonUpper, 'TRADABLE') !== false) {
                $categories['price_availability_or_boundary_censoring'] += $count;
            } else {
                $categories['other'] += $count;
            }
        }

        return $categories;
    }

    private function monthSkeleton(array $strategyTradeDates): array
    {
        $months = [];
        foreach ($strategyTradeDates as $tradeDate) {
            $month = substr($tradeDate, 0, 7);
            if (! isset($months[$month])) {
                $months[$month] = $this->emptyMonth($month);
            }
            $months[$month]['strategy_trade_date_count']++;
        }

        return $months;
    }

    private function emptyMonth(string $month): array
    {
        return [
            'month' => $month,
            'strategy_trade_date_count' => 0,
            'raw_candidate_count' => 0,
            'candidate_universe_eligible_count' => 0,
            'scored_count' => 0,
            'top_picks_count' => 0,
            'secondary_count' => 0,
            'recommended_count_before_price_evaluation' => 0,
        ];
    }

    private function catalogManifest(array $rows, string $catalogCode, string $policyCode): array
    {
        $versions = [];
        $hashes = [];
        foreach ($rows as $row) {
            $versions[(string) ($row['catalog_version'] ?? '')] = true;
            $hashes[(string) ($row['catalog_hash'] ?? '')] = true;
        }

        return [
            'policy_code' => $policyCode,
            'catalog_code' => $catalogCode,
            'catalog_versions' => array_values(array_filter(array_keys($versions))),
            'catalog_hashes' => array_values(array_filter(array_keys($hashes))),
            'catalog_count' => count($rows),
            'param_ids' => array_values(array_map(function (array $row): int {
                return (int) ($row['param_id'] ?? 0);
            }, $rows)),
            'row_codes' => array_values(array_map(function (array $row): string {
                return (string) ($row['row_code'] ?? '');
            }, $rows)),
        ];
    }

    private function blockedParam(array $row, string $reasonCode, string $message): array
    {
        return [
            'param_id' => (int) ($row['param_id'] ?? 0),
            'row_code' => (string) ($row['row_code'] ?? ''),
            'catalog_code' => (string) ($row['catalog_code'] ?? ''),
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
        ];
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_merge([
            'ready' => false,
            'is_ready' => false,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'oos_executed' => false,
            'production_ready' => false,
        ], $extra);
    }

    private function writeJsonArtifact(array $artifact, string $targetPath, bool $overwrite): array
    {
        if (is_file($targetPath) && ! $overwrite) {
            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID',
                'path' => $targetPath,
            ];
        }
        $directory = dirname($targetPath);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_DIRECTORY_UNAVAILABLE',
                'path' => $targetPath,
            ];
        }
        $encoded = json_encode($this->normalizeForHash($artifact), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
        $temporaryPath = $targetPath.'.tmp';
        if (file_put_contents($temporaryPath, $encoded, LOCK_EX) === false) {
            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'path' => $targetPath,
            ];
        }
        if (! rename($temporaryPath, $targetPath)) {
            @unlink($temporaryPath);

            return [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_RENAME_FAILED',
                'path' => $targetPath,
            ];
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITTEN',
            'path' => $targetPath,
            'bytes' => strlen($encoded),
            'sha1' => sha1($encoded),
        ];
    }

    private function artifactForHash(array $artifact): array
    {
        unset($artifact['artifact_hash'], $artifact['canonical_artifact_hash']);

        return $artifact;
    }

    private function sortAssocDesc(array $values): array
    {
        arsort($values, SORT_NUMERIC);

        return $values;
    }

    private function incrementReason(array &$result, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            return;
        }
        $result[$reason] = ($result[$reason] ?? 0) + 1;
    }

    private function paramIds($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = array_filter(array_map('trim', explode(',', $value)), function (string $item): bool {
                return $item !== '';
            });
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

        return array_values(array_unique($ids));
    }

    private function normalizeDateList(array $values): array
    {
        $dates = [];
        foreach ($values as $value) {
            if (is_scalar($value) && $this->validDate((string) $value)) {
                $dates[] = (string) $value;
            }
        }
        $dates = array_values(array_unique($dates));
        sort($dates, SORT_STRING);

        return $dates;
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        return $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format('Y-m-d') === $value;
    }

    private function floatOrNull($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function stableHash(array $value): string
    {
        return sha1(json_encode($this->normalizeForHash($value), JSON_UNESCAPED_SLASHES));
    }

    private function normalizeForHash($value)
    {
        if (is_array($value)) {
            if ($this->isAssoc($value)) {
                ksort($value, SORT_STRING);
            }
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeForHash($item);
            }
        }

        return $value;
    }

    private function isAssoc(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function reasonCode(\Throwable $e, string $fallback = 'WS_BT_C18_FUNNEL_DIAGNOSTIC_FAILED'): string
    {
        $message = strtoupper($e->getMessage());
        if (preg_match('/\b([A-Z0-9_]{8,})\b/', $message, $matches)) {
            return $matches[1];
        }

        return $fallback;
    }
}
