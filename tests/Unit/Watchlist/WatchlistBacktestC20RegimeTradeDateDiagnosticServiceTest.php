<?php

use App\Application\MarketData\Services\MarketBenchmarkReadService;
use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC19SelectionModelRedesignAnalysisService;
use App\Application\Watchlist\Services\WatchlistBacktestC20RegimeTradeDateDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistBacktestRuntimeArtifactService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC20RegimeTradeDateDiagnosticServiceTest extends TestCase
{
    public function test_it_generates_c20_artifact_with_regime_profiles_no_pick_days_and_safety_boundaries(): void
    {
        $outputPath = sys_get_temp_dir().'/c20-regime-diagnostic-test.json';
        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }

        $service = $this->service();
        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            [
                'overwrite' => true,
                'param_ids' => '148',
                'profiles' => 'C20_G00_BASELINE_NO_DATE_GATE,C20_G02_BREADTH_HEALTHY,C20_G06_NO_PICK_DAY_ALLOWED_QUALITY_FIRST',
                'executed_at' => '2025-05-21T23:59:59+07:00',
            ]
        );

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C20_REGIME_TRADE_DATE_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_REGIME_TRADE_DATE_DIAGNOSTIC', $result['scope']);
        $this->assertSame(1, $result['c20_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C20_REGIME_TRADE_DATE_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('IS_ONLY_REGIME_TRADE_DATE_DIAGNOSTIC', $artifact['scope']);
        $this->assertSame('C19_CATALOG_CANDIDATE_FAILED', $artifact['source_evidence']['c19_final_status']);
        $this->assertSame('18ae8b1f1dcfc5ddecc2279d3c9fd0ce69079e6d', $artifact['source_evidence']['c19_frontier_all_param_artifact_hash']);
        $this->assertArrayHasKey('data_availability', $artifact);
        $this->assertArrayHasKey('profile_summaries', $artifact);
        $this->assertArrayHasKey('sample_quality_table', $artifact);
        $this->assertArrayHasKey('decision', $artifact);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);
        $this->assertTrue($artifact['decision']['small_sample_cannot_be_main_decision']);
        $this->assertSame(0, $artifact['safety_boundaries']['production_ready']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C20_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['NO_C01_TO_C19_MUTATION']);
        $this->assertFalse($artifact['safety_boundaries']['date_gate_uses_price_outcome']);

        $g02 = $this->summaryFor($artifact['profile_summaries'], 'C20_G02_BREADTH_HEALTHY');
        $this->assertGreaterThan(0, $g02['blocked_trade_dates_count']);
        $this->assertGreaterThan(0, $g02['no_pick_days_count']);
        $this->assertFalse($g02['trade_date_gate_summary']['uses_price_outcome_for_gate']);

        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function test_small_sample_profile_is_not_promoted_as_decision_best(): void
    {
        $outputPath = sys_get_temp_dir().'/c20-small-sample-decision-test.json';
        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }

        $result = $this->service()->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            [
                'overwrite' => true,
                'param_ids' => '148',
                'profiles' => 'C20_G06_NO_PICK_DAY_ALLOWED_QUALITY_FIRST',
                'executed_at' => '2025-05-21T23:59:59+07:00',
            ]
        );

        $this->assertSame('PASS', $result['status']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C20_DATE_GATE_NOT_ENOUGH', $artifact['decision']['decision_status']);
        $this->assertNull($artifact['decision']['best_promising_sample_profile']);
        $this->assertNull($artifact['decision']['best_sample_qualified_profile']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);

        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function test_it_blocks_unknown_profile_without_claiming_runtime_success(): void
    {
        $result = $this->service()->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            sys_get_temp_dir().'/c20-invalid-profile.json',
            ['profiles' => 'NOT_A_C20_PROFILE', 'overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C20_PROFILE_INVALID', $result['reason_code']);
        $this->assertSame(1, $result['c20_catalog_implementation_deferred']);
        $this->assertSame('NOT_CREATED', $result['c20_catalog_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
    }

    private function service(): WatchlistBacktestC20RegimeTradeDateDiagnosticService
    {
        return new WatchlistBacktestC20RegimeTradeDateDiagnosticService(
            new WatchlistBacktestC20FakeCalendarReadService(),
            new WatchlistBacktestC20FakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            new WatchlistBacktestC20FakeSelectionDiagnosticService(),
            new WatchlistBacktestC20FakePriceSeriesReadService(),
            new WatchlistBacktestC20FakeRuntimeArtifactService(),
            new WatchlistBacktestC20FakeBenchmarkReadService()
        );
    }

    private function summaryFor(array $summaries, string $profileCode): array
    {
        foreach ($summaries as $summary) {
            if (($summary['profile_code'] ?? null) === $profileCode) {
                return $summary;
            }
        }
        $this->fail('Missing summary for '.$profileCode);
    }
}

class WatchlistBacktestC20FakeCalendarReadService extends MarketDataTradingCalendarReadService
{
    public function __construct()
    {
    }

    public function resolveTradingDates(string $fromDate, string $toDate): array
    {
        $dates = [
            '2023-01-02', '2023-01-03', '2023-01-04', '2023-01-05', '2023-01-06',
            '2023-01-09', '2023-01-10', '2023-01-11', '2023-01-12', '2023-01-13', '2023-01-16',
        ];

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'READABLE_TRADING_CALENDAR_RESOLVED',
            'trade_dates' => $dates,
            'calendar_dates' => $dates,
            'calendar_source' => 'fake_market_calendar',
            'calendar_sources' => ['fake_market_calendar'],
            'calendar_hash' => sha1(json_encode($dates)),
            'coverage' => ['calendar_date_count' => count($dates)],
        ];
    }
}

class WatchlistBacktestC20FakeParamGridRepository extends WatchlistBacktestParamGridRepository
{
    public function allForCatalog($catalogCode, $policyCode = 'WS'): array
    {
        $row = WatchlistBacktestC17ParamGridCatalog::rows()[0];
        $row['param_id'] = 148;
        return [$row];
    }
}

class WatchlistBacktestC20FakeSelectionDiagnosticService extends WatchlistBacktestC19SelectionModelRedesignAnalysisService
{
    public function __construct()
    {
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $items = [
            $this->item('2023-01-02', 1, 'AAA', 0.60, 0.020, 0.030, 1.60, 0.010, 0.020),
            $this->item('2023-01-02', 2, 'AAB', 0.58, 0.015, 0.032, 1.50, 0.015, 0.025),
            $this->item('2023-01-03', 3, 'BBB', 0.53, -0.040, 0.060, 1.20, -0.040, -0.060),
            $this->item('2023-01-04', 4, 'CCC', 0.57, 0.000, 0.044, 1.40, -0.010, -0.010),
            $this->item('2023-01-04', 5, 'CCD', 0.56, 0.005, 0.046, 1.35, -0.005, -0.005),
            $this->item('2023-01-05', 6, 'DDD', 0.55, -0.010, 0.041, 1.55, 0.000, 0.000),
        ];

        $artifact = [
            'artifact_type' => 'C19_SELECTION_MODEL_REDESIGN_ANALYSIS',
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY',
            'scope' => 'IS_ONLY_DIAGNOSTIC',
            'diagnostics' => [[
                'param_id' => 148,
                'row_code' => '00_C16_140_SCORE_65_80_MID_DV20_ONE_R',
                'proposed_path' => [
                    'top_count' => 4,
                    'secondary_count' => 2,
                    'candidate_buffer_count' => 6,
                    'recommended_count' => 6,
                    'selected_items' => $items,
                ],
            ]],
            'artifact_hash' => sha1('fake-c20-selection'),
        ];
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
        ];
    }

    private function item(string $date, int $id, string $ticker, float $quality, float $roc20, float $atr, float $vol, float $rs, float $sectorRoc): array
    {
        return [
            'trade_date' => $date,
            'month' => substr($date, 0, 7),
            'ticker_id' => $id,
            'ticker_code' => $ticker,
            'proposed_plan_group' => 'top',
            'proposed_rank' => $id,
            'score_total' => $quality + 0.10,
            'quality_score' => $quality,
            'penalty_total' => 0.02,
            'trend_pass_count' => 2,
            'current_extension_failures' => [],
            'penalties' => [],
            'score_metrics' => [
                'dv20_idr' => 3000000000,
                'vol_ratio' => $vol,
                'roc20' => $roc20,
                'atr14_pct' => $atr,
                'rs_20_vs_ihsg' => $rs,
                'sector_roc20' => $sectorRoc,
                'sector_rs_20_vs_ihsg' => $rs,
            ],
            'score_components' => [
                'score_momentum' => 0.22,
                'score_breakout' => 0.24,
                'score_volume' => 0.22,
                'score_risk' => 0.32,
            ],
            'factor_breakdown' => [],
        ];
    }
}

class WatchlistBacktestC20FakePriceSeriesReadService extends MarketDataPublishedEodSeriesReadService
{
    public function __construct()
    {
    }

    public function readPublishedSeriesForDateTickerMap(string $fromDate, string $toDate, array $tickerCodesByTradeDate): array
    {
        return [
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLISHED_EOD_SERIES_RESOLVED',
            'series_by_ticker' => [],
            'price_series_manifest' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'missing_price_dates' => [],
                'missing_price_rows' => [],
            ],
            'publication_manifest' => [],
        ];
    }
}

class WatchlistBacktestC20FakeRuntimeArtifactService extends WatchlistBacktestRuntimeArtifactService
{
    public function __construct()
    {
    }

    public function buildArtifact(array $backtestPayload, array $publishedPriceSeriesByTicker = [], array $tradingCalendar = [], array $options = []): array
    {
        $trades = array_values(array_filter($backtestPayload['trades'] ?? [], 'is_array'));
        $count = count($trades);
        $profile = (string) ($backtestPayload['c20_date_gate']['profile_code'] ?? '');
        $ret = $profile === 'C20_G06_NO_PICK_DAY_ALLOWED_QUALITY_FIRST' ? 0.010 : -0.002;
        $evaluated = [];
        foreach ($trades as $trade) {
            $evaluated[] = [
                'trade_date' => $trade['trade_date'],
                'ticker' => $trade['ticker'],
                'metrics_ready' => true,
                'ret_net' => $ret,
                'is_win' => $ret > 0,
                'exit_reason_code' => $ret > 0 ? 'WATCHLIST_BACKTEST_EXIT_TARGET' : 'WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED',
            ];
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY',
            'validation' => ['artifact_hash' => sha1(json_encode($backtestPayload))],
            'summary' => ['metrics_ready' => $count > 0, 'metrics_reason_code' => 'WATCHLIST_BACKTEST_METRICS_READY'],
            'metrics' => [
                'canonical_eval_metrics' => [
                    'picks_count' => $count,
                    'avg_ret_net_top' => $count > 0 ? $ret : null,
                    'median_ret_net_top' => $count > 0 ? $ret : null,
                    'p25_ret_net_top' => $count > 0 ? $ret : null,
                    'p75_ret_net_top' => $count > 0 ? $ret : null,
                    'win_rate_top' => $count > 0 ? ($ret > 0 ? 1.0 : 0.0) : null,
                    'month_win_rate_min' => $count > 0 ? ($ret > 0 ? 1.0 : 0.0) : null,
                    'month_avg_ret_net_min' => $count > 0 ? $ret : null,
                    'period_fail_count' => $ret > 0 ? 0 : 13,
                ],
                'counts' => [
                    'total_evaluated_trades' => $count,
                    'rejected_no_data_evaluation_count' => 0,
                    'diagnostics_count' => 0,
                ],
                'evaluated_trades' => $evaluated,
                'reason_code_distribution' => [],
            ],
        ];
    }
}

class WatchlistBacktestC20FakeBenchmarkReadService extends MarketBenchmarkReadService
{
    public function __construct()
    {
    }

    public function getBenchmarkMarketDataForTradeDate(string $tradeDate, string $benchmarkCode = 'IHSG'): array
    {
        return [
            'trade_date' => $tradeDate,
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLICATION_RESOLVED',
            'benchmark' => [
                'benchmark_code' => 'IHSG',
                'trade_date' => $tradeDate,
                'roc_20' => $tradeDate === '2023-01-03' ? -0.050 : 0.010,
                'close_to_ma20_pct' => $tradeDate === '2023-01-03' ? -0.040 : 0.005,
                'ma20_slope_pct' => $tradeDate === '2023-01-03' ? -0.010 : 0.002,
                'is_valid' => true,
            ],
        ];
    }
}
