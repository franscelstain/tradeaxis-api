<?php

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC19SelectionModelRedesignAnalysisService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistCandidateUniverseService;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistRecommendationService;
use App\Application\Watchlist\Services\WatchlistScoringService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC19SelectionModelRedesignAnalysisServiceTest extends TestCase
{
    public function test_it_builds_c19_selection_redesign_artifact_without_catalog_or_oos(): void
    {
        $outputPath = sys_get_temp_dir().'/c19-selection-redesign-analysis-test.json';
        @unlink($outputPath);

        $plan = new WatchlistBacktestC19FakePlanGroupingService();
        $service = new WatchlistBacktestC19SelectionModelRedesignAnalysisService(
            new WatchlistBacktestC19FakeCalendar(),
            new WatchlistBacktestC19FakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            new WatchlistBacktestC19FakeCandidateUniverseService(),
            new WatchlistBacktestC19FakeScoringService(),
            $plan,
            new WatchlistBacktestC19FakeRecommendationService($plan)
        );

        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2025-05-21T23:59:59+07:00']
        );

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY', $result['reason_code']);
        $this->assertSame(1, $result['diagnostic_param_count']);
        $this->assertSame(0, $result['max_current_secondary_count']);
        $this->assertGreaterThan(0, $result['max_proposed_secondary_count']);
        $this->assertGreaterThan($result['max_current_recommended_count'], $result['max_proposed_recommended_count']);
        $this->assertSame(1, $result['c19_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertFileExists($outputPath);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C19_SELECTION_MODEL_REDESIGN_ANALYSIS', $artifact['artifact_type']);
        $this->assertSame('IS_ONLY_DIAGNOSTIC', $artifact['scope']);
        $this->assertTrue($artifact['safety_boundaries']['C19_STRATEGY_MODEL_REDESIGN']);
        $this->assertTrue($artifact['safety_boundaries']['C19_NOT_CATALOG_CHURN']);
        $this->assertTrue($artifact['safety_boundaries']['C19_CATALOG_IMPLEMENTATION_DEFERRED']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C19_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['OOS_NOT_RUN']);
        $this->assertSame(0, $artifact['safety_boundaries']['production_ready']);
        $this->assertSame('design_cutoff_guard_behavior_not_runtime_bug', $artifact['diagnostics'][0]['root_cause_code_level']['secondary_zero_interpretation']);
        $this->assertTrue($artifact['diagnostics'][0]['root_cause_code_level']['current_path_mapping_fixed_v3']);
        $this->assertTrue($artifact['diagnostics'][0]['root_cause_code_level']['proposal_selector_simulation_from_scored_pool']);
        $this->assertSame('WatchlistPlanGroupingService::candidateSelectionExtensionFailures', $artifact['diagnostics'][0]['root_cause_code_level']['hard_guard_hotspots'][0]);
        $this->assertSame(10, $artifact['diagnostics'][0]['current_path']['candidate_raw_count']);
        $this->assertSame(2, $artifact['diagnostics'][0]['current_path']['plan_top_picks_count']);
        $this->assertSame(2, $artifact['diagnostics'][0]['current_path']['recommendation_output_count']);
        $this->assertContains('TOP_PICKS', $artifact['diagnostics'][0]['debug_output_keys']['plan_grouping_group_keys']);
        $this->assertNotSame([], $artifact['diagnostics'][0]['dominant_current_drop_reasons']);
        $this->assertGreaterThan(0, $artifact['diagnostics'][0]['proposed_path']['secondary_count']);
        $this->assertGreaterThan(0, $artifact['diagnostics'][0]['monthly_distribution'][0]['proposed_recommended_count']);

        $firstHash = $artifact['artifact_hash'];
        $second = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2025-05-21T23:59:59+07:00']
        );
        $this->assertSame($firstHash, $second['artifact_hash']);

        @unlink($outputPath);
    }

    public function test_it_blocks_non_frozen_is_window(): void
    {
        $service = new WatchlistBacktestC19SelectionModelRedesignAnalysisService(
            new WatchlistBacktestC19FakeCalendar(),
            new WatchlistBacktestC19FakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            new WatchlistBacktestC19FakeCandidateUniverseService(),
            new WatchlistBacktestC19FakeScoringService(),
            new WatchlistBacktestC19FakePlanGroupingService(),
            new WatchlistBacktestC19FakeRecommendationService(new WatchlistBacktestC19FakePlanGroupingService())
        );

        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            '2023-01-03',
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            sys_get_temp_dir().'/c19-selection-window-blocked-test.json',
            ['overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C19_IS_ONLY_WINDOW_MISMATCH', $result['reason_code']);
        $this->assertSame(1, $result['c19_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
    }
}

class WatchlistBacktestC19FakeCalendar extends MarketDataTradingCalendarReadService
{
    public function resolveTradingDates(string $fromDate, string $toDate): array
    {
        return [
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_CALENDAR_READY',
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

class WatchlistBacktestC19FakeParamGridRepository extends WatchlistBacktestParamGridRepository
{
    public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array
    {
        $row = WatchlistBacktestC17ParamGridCatalog::rows()[5];
        $row['param_id'] = 150;
        return [$row];
    }
}

class WatchlistBacktestC19FakeCandidateUniverseService extends WatchlistCandidateUniverseService
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

class WatchlistBacktestC19FakeScoringService extends WatchlistScoringService
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

class WatchlistBacktestC19FakePlanGroupingService extends WatchlistPlanGroupingService
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

class WatchlistBacktestC19FakeRecommendationService extends WatchlistRecommendationService
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
