<?php

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC18FunnelDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistBacktestPublishedPriceRuntimeService;
use App\Application\Watchlist\Services\WatchlistCandidateUniverseService;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistRecommendationService;
use App\Application\Watchlist\Services\WatchlistScoringService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC18FunnelDiagnosticServiceTest extends TestCase
{
    public function test_it_builds_is_only_c18_funnel_artifact_and_defers_catalog(): void
    {
        $outputPath = sys_get_temp_dir().'/c18-funnel-diagnostic-service-test.json';
        @unlink($outputPath);

        $service = new WatchlistBacktestC18FunnelDiagnosticService(
            new WatchlistBacktestC18FunnelFakeCalendar(),
            new WatchlistBacktestC18FunnelFakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            new WatchlistBacktestC18FunnelFakeCandidateUniverseService(),
            new WatchlistBacktestC18FunnelFakeScoringService(),
            new WatchlistBacktestC18FunnelFakePlanGroupingService(),
            new WatchlistBacktestC18FunnelFakeRecommendationService(),
            new WatchlistBacktestC18FunnelFakeRuntimeService()
        );

        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2025-05-21T23:59:59+07:00', 'deep_funnel' => true]
        );

        $this->assertTrue($result['is_ready']);
        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C18_FUNNEL_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertFileExists($outputPath);

        $artifact = $result['artifact'];
        $this->assertSame('C18_FUNNEL_AND_MONTHLY_COVERAGE_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('IS_ONLY_DIAGNOSTIC', $artifact['scope']);
        $this->assertSame('C18_PHASE_A_DIAGNOSTIC_FIRST_FUNNEL_AUDIT', $artifact['diagnostic_contract']['phase']);
        $this->assertTrue($artifact['diagnostic_contract']['does_not_execute_oos']);
        $this->assertTrue($artifact['diagnostic_contract']['does_not_lower_canonical_gates']);
        $this->assertTrue($artifact['diagnostic_contract']['plan_recommendation_confirm_boundary_unchanged']);
        $this->assertFalse($artifact['safety_boundaries']['oos_executed']);
        $this->assertFalse($artifact['safety_boundaries']['production_ready']);
        $this->assertTrue($artifact['c18_catalog_decision']['catalog_implementation_deferred']);
        $this->assertSame('C18_CATALOG_IMPLEMENTATION_DEFERRED', $artifact['c18_catalog_decision']['status']);

        $this->assertSame(1, $artifact['source_catalog_manifest']['catalog_count']);
        $this->assertSame(WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE, $artifact['source_catalog_manifest']['catalog_code']);
        $this->assertSame(7, $artifact['is_window_manifest']['raw_trading_date_count']);

        $param = $artifact['per_param_diagnostics'][0];
        $this->assertSame('DIAGNOSTIC_READY', $param['status']);
        $this->assertSame(2, $param['funnel_counts']['strategy_trade_dates_count']);
        $this->assertSame(6, $param['funnel_counts']['raw_ticker_date_candidate_count']);
        $this->assertSame(4, $param['funnel_counts']['after_candidate_universe_filter_count']);
        $this->assertSame(4, $param['funnel_counts']['after_score_runtime_guard_count']);
        $this->assertSame(2, $param['funnel_counts']['after_grouping_top_picks_count']);
        $this->assertSame(2, $param['funnel_counts']['after_grouping_secondary_count']);
        $this->assertSame(2, $param['funnel_counts']['recommended_count_before_price_evaluation']);
        $this->assertSame(12, $param['funnel_counts']['requested_ticker_date_pair_count']);
        $this->assertSame(1, $param['funnel_counts']['evaluated_picks_count']);
        $this->assertSame(5, $param['funnel_counts']['boundary_censored_count']);
        $this->assertSame(2, $param['funnel_counts']['period_count']);
        $this->assertSame(1, $param['funnel_counts']['period_fail_count']);

        $this->assertSame(1, $param['root_cause_signals']['monthly_empty_period_count']);
        $this->assertTrue($param['root_cause_signals']['monthly_distribution_unhealthy']);
        $this->assertSame('WS_BT_C18_MONTH_EMPTY_AFTER_EVALUATION', $param['monthly_empty_or_failed_periods'][0]['reason_code']);
        $this->assertArrayHasKey('WATCHLIST_C17_SCORE_WINDOW_LOW_FAIL', $param['filter_drop_reason_distribution']);
        $this->assertArrayHasKey('WS_BT_SKIP_MISSING_OHLC_EXIT', $param['filter_drop_reason_distribution']);
        $this->assertGreaterThanOrEqual(1, $param['drop_reason_category_distribution']['score_window']);
        $this->assertGreaterThanOrEqual(1, $param['drop_reason_category_distribution']['price_availability_or_boundary_censoring']);

        $firstHash = $artifact['artifact_hash'];
        $second = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2025-05-21T23:59:59+07:00', 'deep_funnel' => true]
        );
        $this->assertSame($firstHash, $second['artifact']['artifact_hash']);

        @unlink($outputPath);
    }


    public function test_it_defaults_to_runtime_first_without_deep_funnel_loop(): void
    {
        $outputPath = sys_get_temp_dir().'/c18-funnel-runtime-first-service-test.json';
        @unlink($outputPath);

        $service = new WatchlistBacktestC18FunnelDiagnosticService(
            new WatchlistBacktestC18FunnelFakeCalendar(),
            new WatchlistBacktestC18FunnelFakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            new WatchlistBacktestC18FunnelFakeCandidateUniverseService(),
            new WatchlistBacktestC18FunnelFakeScoringService(),
            new WatchlistBacktestC18FunnelFakePlanGroupingService(),
            new WatchlistBacktestC18FunnelFakeRecommendationService(),
            new WatchlistBacktestC18FunnelFakeRuntimeService()
        );

        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2025-05-21T23:59:59+07:00']
        );

        $this->assertTrue($result['is_ready']);
        $artifact = $result['artifact'];
        $this->assertTrue($artifact['diagnostic_contract']['runtime_first_default']);
        $this->assertFalse($artifact['diagnostic_contract']['deep_funnel_enabled']);

        $param = $artifact['per_param_diagnostics'][0];
        $this->assertFalse($param['funnel_counts']['deep_funnel_enabled']);
        $this->assertSame('SKIPPED_BY_DEFAULT_RUNTIME_FIRST_DIAGNOSTIC', $param['funnel_counts']['deep_funnel_status']);
        $this->assertTrue($param['funnel_counts']['deep_funnel_required_for_filter_drop_reason_distribution']);
        $this->assertSame(0, $param['funnel_counts']['raw_ticker_date_candidate_count']);
        $this->assertSame(0, $param['funnel_counts']['after_candidate_universe_filter_count']);
        $this->assertSame(12, $param['funnel_counts']['requested_ticker_date_pair_count']);
        $this->assertSame(1, $param['funnel_counts']['evaluated_picks_count']);

        @unlink($outputPath);
    }

    public function test_it_blocks_non_frozen_is_window(): void
    {
        $service = new WatchlistBacktestC18FunnelDiagnosticService(
            new WatchlistBacktestC18FunnelFakeCalendar(),
            new WatchlistBacktestC18FunnelFakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            new WatchlistBacktestC18FunnelFakeCandidateUniverseService(),
            new WatchlistBacktestC18FunnelFakeScoringService(),
            new WatchlistBacktestC18FunnelFakePlanGroupingService(),
            new WatchlistBacktestC18FunnelFakeRecommendationService(),
            new WatchlistBacktestC18FunnelFakeRuntimeService()
        );

        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            '2023-01-03',
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            sys_get_temp_dir().'/c18-funnel-window-blocked-test.json',
            ['overwrite' => true]
        );

        $this->assertFalse($result['is_ready']);
        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C18_IS_WINDOW_MISMATCH', $result['reason_code']);
        $this->assertFalse($result['oos_executed']);
        $this->assertFalse($result['production_ready']);
    }
}

class WatchlistBacktestC18FunnelFakeCalendar extends MarketDataTradingCalendarReadService
{
    public function resolveTradingDates(string $fromDate, string $toDate): array
    {
        return [
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_CALENDAR_READY',
            'calendar_hash' => 'fake-c18-calendar-hash',
            'trade_dates' => [
                '2023-01-02',
                '2023-02-01',
                '2023-02-02',
                '2023-02-03',
                '2023-02-06',
                '2023-02-07',
                '2023-02-08',
            ],
        ];
    }
}

class WatchlistBacktestC18FunnelFakeParamGridRepository extends WatchlistBacktestParamGridRepository
{
    public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array
    {
        $row = WatchlistBacktestC17ParamGridCatalog::rows()[0];
        $row['param_id'] = 145;

        return [$row];
    }
}

class WatchlistBacktestC18FunnelFakeCandidateUniverseService extends WatchlistCandidateUniverseService
{
    public function buildCandidateUniverseForTradeDate(string $tradeDate, array $paramset = []): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_CANDIDATE_UNIVERSE_READY',
            'input_candidate_count' => 3,
            'eligible_count' => 2,
            'rejected_count' => 1,
            'eligible_candidates' => [
                ['ticker_code' => 'AAA', 'ticker_id' => 1],
                ['ticker_code' => 'BBB', 'ticker_id' => 2],
            ],
            'rejected_candidates' => [
                ['ticker_code' => 'CCC', 'reason_codes' => ['WATCHLIST_C17_DV20_RANGE_FAIL']],
            ],
            'reason_counts' => [
                'WATCHLIST_C17_VOLUME_RECOVERY_RANGE_FAIL' => 1,
            ],
        ];
    }
}

class WatchlistBacktestC18FunnelFakeScoringService extends WatchlistScoringService
{
    public function scoreCandidateUniverse(array $universe, array $paramset = [], string $tradeDate = ''): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_SCORING_READY',
            'trade_date' => $tradeDate,
            'items' => [
                ['ticker_code' => 'AAA', 'ticker_id' => 1, 'score_total' => 0.76],
                ['ticker_code' => 'BBB', 'ticker_id' => 2, 'score_total' => 0.74],
            ],
            'excluded' => [
                ['ticker_code' => 'DDD', 'reason_codes' => ['WATCHLIST_C17_SCORE_WINDOW_LOW_FAIL']],
            ],
            'summary' => [
                'scored_count' => 2,
                'excluded_count' => 1,
            ],
        ];
    }
}

class WatchlistBacktestC18FunnelFakePlanGroupingService extends WatchlistPlanGroupingService
{
    public function groupScoredOutput(array $scoredOutput, array $paramset = [], string $tradeDate = ''): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_PLAN_GROUPING_READY',
            'groups' => [
                'TOP_PICKS' => [
                    ['ticker_code' => 'AAA', 'ticker_id' => 1, 'bucket_code' => 'TOP_PICKS'],
                ],
                'SECONDARY' => [
                    ['ticker_code' => 'BBB', 'ticker_id' => 2, 'bucket_code' => 'SECONDARY'],
                ],
                'WATCH_ONLY' => [],
                'AVOID' => [
                    ['ticker_code' => 'EEE', 'group_reason_code' => 'WATCHLIST_PLAN_AVOID_LOW_SCORE'],
                ],
            ],
            'excluded' => [
                ['ticker_code' => 'FFF', 'reason_codes' => ['WATCHLIST_C17_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL']],
            ],
        ];
    }
}

class WatchlistBacktestC18FunnelFakeRecommendationService extends WatchlistRecommendationService
{
    public function __construct()
    {
    }

    public function recommendFromPlanOutput(array $planOutput, array $paramset = [], array $capitalInput = []): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_RECOMMENDATION_READY',
            'summary' => [
                'source_plan_item_count' => 1,
                'recommended_count' => 1,
            ],
            'recommendations' => [
                ['ticker_code' => 'AAA', 'ticker_id' => 1, 'bucket_code' => 'TOP_PICKS'],
            ],
        ];
    }
}

class WatchlistBacktestC18FunnelFakeRuntimeService extends WatchlistBacktestPublishedPriceRuntimeService
{
    public function evaluateWindow(string $fromDate, string $toDate, array $options = []): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY',
            'artifact_hash' => 'fake-c18-runtime-hash',
            'diagnostics' => [
                ['reason_code' => 'WS_BT_SKIP_MISSING_OHLC_EXIT'],
            ],
            'artifact' => [
                'runtime_execution' => [
                    'strict_is_boundary' => true,
                    'hard_market_data_to_date' => $toDate,
                    'max_requested_market_data_date' => $toDate,
                    'requested_ticker_date_pair_count' => 12,
                    'trade_candidate_count' => 2,
                    'required_price_date_count' => 6,
                    'ticker_count' => 2,
                    'boundary_censored_trade_date_count' => 5,
                ],
                'metrics' => [
                    'canonical_eval_metrics' => [
                        'picks_count' => 1,
                        'days_covered' => 1,
                        'periods_count' => 2,
                        'period_fail_count' => 1,
                        'avg_ret_net_top' => 0.02,
                    ],
                    'metric_sufficiency' => [
                        'effective_thresholds' => [
                            'min_trades' => 120,
                            'min_month_win_rate_min' => 0.45,
                            'min_month_avg_ret_net_min' => -0.01,
                            'min_p25_ret_net_top' => -0.03,
                        ],
                        'failure_reason_codes' => [
                            'WS_BT_EVAL_MIN_TRADES_FAIL',
                            'WS_BT_EVAL_STABILITY_FAIL',
                        ],
                    ],
                    'reason_code_distribution' => [
                        'WS_BT_SKIP_BOUNDARY_CENSORED' => 5,
                    ],
                    'evaluated_trades' => [
                        [
                            'metrics_ready' => true,
                            'bucket_code' => 'TOP_PICKS',
                            'trade_date' => '2023-01-02',
                            'ret_net' => 0.02,
                        ],
                    ],
                ],
            ],
        ];
    }
}
