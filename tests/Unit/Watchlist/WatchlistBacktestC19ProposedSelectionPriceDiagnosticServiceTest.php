<?php

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC19ProposedSelectionPriceDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestC19SelectionModelRedesignAnalysisService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistBacktestRuntimeArtifactService;
use App\Application\Watchlist\Services\WatchlistCandidateUniverseService;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistRecommendationService;
use App\Application\Watchlist\Services\WatchlistScoringService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC19ProposedSelectionPriceDiagnosticServiceTest extends TestCase
{
    public function test_it_price_evaluates_c19_proposed_selection_without_catalog_or_oos(): void
    {
        $outputPath = sys_get_temp_dir().'/c19-proposed-selection-price-diagnostic-test.json';
        @unlink($outputPath);
        @unlink($outputPath.'.selection-analysis.json');

        $calendar = new WatchlistBacktestC19Phase4FakeCalendar();
        $paramGrid = new WatchlistBacktestC19Phase4FakeParamGridRepository();
        $paramsetFactory = new WatchlistBacktestParamGridParamsetFactory();
        $plan = new WatchlistBacktestC19Phase4FakePlanGroupingService();
        $selection = new WatchlistBacktestC19SelectionModelRedesignAnalysisService(
            $calendar,
            $paramGrid,
            $paramsetFactory,
            new WatchlistBacktestC19Phase4FakeCandidateUniverseService(),
            new WatchlistBacktestC19Phase4FakeScoringService(),
            $plan,
            new WatchlistBacktestC19Phase4FakeRecommendationService($plan)
        );
        $service = new WatchlistBacktestC19ProposedSelectionPriceDiagnosticService(
            $calendar,
            $paramGrid,
            $paramsetFactory,
            $selection,
            new WatchlistBacktestC19Phase4FakePriceSeriesReadService(),
            new WatchlistBacktestRuntimeArtifactService()
        );

        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2025-05-21T23:59:59+07:00']
        );

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C19_PRICE_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_PRICE_DIAGNOSTIC', $result['scope']);
        $this->assertSame(1, $result['diagnostic_param_count']);
        $this->assertGreaterThan(0, $result['max_proposed_recommended_count']);
        $this->assertGreaterThan(0, $result['max_requested_pairs_count']);
        $this->assertGreaterThan(0, $result['max_evaluated_picks_count']);
        $this->assertSame(1, $result['c19_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertFileExists($outputPath);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C19_PROPOSED_SELECTION_PRICE_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('IS_ONLY_PRICE_DIAGNOSTIC', $artifact['scope']);
        $this->assertSame('NEXT_OPEN', $artifact['price_evaluation_model']['ENTRY']);
        $this->assertSame('STOP_TP_OR_TIME', $artifact['price_evaluation_model']['EXIT']);
        $this->assertSame(5, $artifact['price_evaluation_model']['HOLD']);
        $this->assertTrue($artifact['safety_boundaries']['C19_PRICE_EVALUATION_DIAGNOSTIC_IMPLEMENTED']);
        $this->assertTrue($artifact['safety_boundaries']['C19_CATALOG_IMPLEMENTATION_DEFERRED']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C19_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['OOS_NOT_RUN']);
        $this->assertSame(0, $artifact['safety_boundaries']['production_ready']);
        $this->assertGreaterThan(0, $artifact['diagnostics'][0]['price_evaluation_counts']['evaluated_picks_count']);
        $this->assertArrayHasKey('avg_ret_net_top', $artifact['diagnostics'][0]['return_metrics']);
        $this->assertNotSame([], $artifact['diagnostics'][0]['monthly_evaluated_distribution']);

        @unlink($outputPath);
        @unlink($outputPath.'.selection-analysis.json');
    }

    public function test_it_blocks_oos_like_window_for_price_diagnostic(): void
    {
        $service = new WatchlistBacktestC19ProposedSelectionPriceDiagnosticService(
            new WatchlistBacktestC19Phase4FakeCalendar(),
            new WatchlistBacktestC19Phase4FakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            null,
            new WatchlistBacktestC19Phase4FakePriceSeriesReadService(),
            new WatchlistBacktestRuntimeArtifactService()
        );

        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            '2025-05-22',
            '2026-05-29',
            sys_get_temp_dir().'/c19-price-oos-blocked-test.json',
            ['overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C19_PRICE_IS_ONLY_WINDOW_MISMATCH', $result['reason_code']);
        $this->assertSame(1, $result['c19_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
    }
}

class WatchlistBacktestC19Phase4FakeCalendar extends MarketDataTradingCalendarReadService
{
    public function resolveTradingDates(string $fromDate, string $toDate): array
    {
        $dates = [
            '2023-01-02',
            '2023-02-01',
            '2023-02-02',
            '2023-02-03',
            '2023-02-06',
            '2023-02-07',
            '2023-02-08',
        ];

        return [
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_CALENDAR_READY',
            'trade_dates' => $dates,
            'calendar_dates' => $dates,
            'calendar_source' => 'FAKE_UNIT_TEST_CALENDAR',
            'calendar_sources' => ['FAKE_UNIT_TEST_CALENDAR'],
            'calendar_hash' => sha1(json_encode($dates)),
            'coverage' => ['unit_test' => true],
        ];
    }
}

class WatchlistBacktestC19Phase4FakeParamGridRepository extends WatchlistBacktestParamGridRepository
{
    public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array
    {
        $row = WatchlistBacktestC17ParamGridCatalog::rows()[5];
        $row['param_id'] = 150;
        return [$row];
    }
}

class WatchlistBacktestC19Phase4FakeCandidateUniverseService extends WatchlistCandidateUniverseService
{
    public function buildCandidateUniverseForTradeDate(string $tradeDate, array $paramset = []): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_CANDIDATE_UNIVERSE_READY',
            'input_candidate_count' => 5,
            'eligible_count' => 5,
            'rejected_count' => 0,
            'summary' => ['raw_count' => 5, 'eligible_count' => 5, 'rejected_count' => 0],
            'eligible_candidates' => [],
            'rejected_candidates' => [],
        ];
    }
}

class WatchlistBacktestC19Phase4FakeScoringService extends WatchlistScoringService
{
    public function scoreCandidateUniverse(array $universe, array $paramset = [], string $tradeDate = ''): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_SCORING_READY',
            'summary' => ['scored_count' => 5, 'excluded_count' => 0],
            'items' => [
                $this->item(1, 'AAA', 0.76, 8000000000.0, 1.80, 0.024, 0.010, -0.010, [0.25, 0.54, 0.30, 0.68]),
                $this->item(2, 'BBB', 0.70, 9000000000.0, 2.20, 0.030, 0.035, -0.028, [0.22, 0.50, 0.26, 0.64]),
                $this->item(3, 'CCC', 0.64, 6200000000.0, 1.45, 0.021, -0.025, 0.012, [0.40, 0.50, 0.40, 0.60]),
                $this->item(4, 'DDD', 0.60, 5400000000.0, 1.30, 0.026, 0.060, -0.014, [0.18, 0.43, 0.21, 0.52]),
                $this->item(5, 'EEE', 0.91, 5000000000.0, 1.30, 0.026, 0.010, -0.010, [0.30, 0.60, 0.30, 0.70]),
            ],
            'excluded' => [],
        ];
    }

    private function item(int $id, string $ticker, float $score, float $dv20, float $vol, float $atr, float $roc20, float $roc5, array $components): array
    {
        return [
            'ticker_id' => $id,
            'ticker_code' => $ticker,
            'score_total' => $score,
            'score_components' => [
                'momentum' => $components[0],
                'breakout' => $components[1],
                'volume' => $components[2],
                'risk' => $components[3],
            ],
            'score_metrics' => [
                'dv20_idr' => $dv20,
                'vol_ratio' => $vol,
                'atr14_pct' => $atr,
                'roc20' => $roc20,
                'roc5' => $roc5,
                'ma20_slope_pct' => 0.010,
                'rs_20_vs_ihsg' => 0.020,
                'close_to_ma20_pct' => -0.010,
                'close_to_ma50_pct' => -0.020,
            ],
        ];
    }
}

class WatchlistBacktestC19Phase4FakePlanGroupingService extends WatchlistPlanGroupingService
{
    public function groupScoredOutput(array $scoredOutput, array $paramset = [], string $tradeDate = ''): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_PLAN_GROUPING_READY',
            'groups' => [
                'TOP_PICKS' => [array_merge($scoredOutput['items'][0], ['plan_group' => 'TOP_PICKS'])],
                'SECONDARY' => [],
                'WATCH_ONLY' => [],
                'AVOID' => [
                    ['ticker_code' => 'BBB', 'group_reason_code' => 'WATCHLIST_C17_DV20_SAMPLE_RECOVERY_RANGE_FAIL'],
                    ['ticker_code' => 'CCC', 'reason_codes' => ['WATCHLIST_C17_ROC20_SEGMENT_RANGE_FAIL']],
                ],
            ],
            'excluded' => [
                ['ticker_code' => 'BBB', 'reason_code' => 'WATCHLIST_C17_DV20_SAMPLE_RECOVERY_RANGE_FAIL'],
                ['ticker_code' => 'CCC', 'reason_code' => 'WATCHLIST_C17_ROC20_SEGMENT_RANGE_FAIL'],
            ],
        ];
    }
}

class WatchlistBacktestC19Phase4FakeRecommendationService extends WatchlistRecommendationService
{
    public function recommendFromPlanOutput(array $planOutput, array $paramset = [], array $capitalInput = []): array
    {
        return [
            'is_ready' => true,
            'ready' => true,
            'reason_code' => 'WATCHLIST_RECOMMENDATION_READY',
            'summary' => [
                'source_plan_item_count' => count($planOutput['groups']['TOP_PICKS'] ?? []),
                'evaluated_count' => count($planOutput['groups']['TOP_PICKS'] ?? []),
                'recommended_count' => count($planOutput['groups']['TOP_PICKS'] ?? []),
            ],
            'items' => array_map(function (array $item): array {
                $item['recommended_flag'] = true;
                return $item;
            }, $planOutput['groups']['TOP_PICKS'] ?? []),
        ];
    }
}

class WatchlistBacktestC19Phase4FakePriceSeriesReadService extends MarketDataPublishedEodSeriesReadService
{
    public function readPublishedSeriesForDateTickerMap(string $fromDate, string $toDate, array $tickerCodesByTradeDate): array
    {
        $series = [];
        $publicationManifest = [];
        $pairs = 0;
        foreach ($tickerCodesByTradeDate as $tradeDate => $codes) {
            $publicationManifest[] = [
                'trade_date' => $tradeDate,
                'is_readable' => true,
                'reason_code' => 'READABLE_PUBLICATION_RESOLVED',
                'requested_ticker_count' => count($codes),
                'row_count' => count($codes),
            ];
            foreach ($codes as $code) {
                $pairs++;
                $base = 100.0 + ((int) crc32((string) $code) % 20);
                $series[(string) $code][$tradeDate] = [
                    'trade_date' => $tradeDate,
                    'ticker_code' => (string) $code,
                    'open' => $base,
                    'high' => $base + 10.0,
                    'low' => $base - 1.0,
                    'close' => $base + 5.0,
                    'volume' => 100000,
                    'published' => true,
                    'readable' => true,
                ];
            }
        }

        return [
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_PRICE_SERIES_READY',
            'series_by_ticker' => $series,
            'price_series_manifest' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'required_price_date_count' => count($tickerCodesByTradeDate),
                'requested_ticker_date_pair_count' => $pairs,
                'resolved_price_date_count' => count($tickerCodesByTradeDate),
                'missing_price_dates' => [],
                'missing_price_rows' => [],
            ],
            'publication_manifest' => $publicationManifest,
            'diagnostics' => [],
        ];
    }
}
