<?php

use App\Application\MarketData\Services\MarketBenchmarkReadService;
use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC19SelectionModelRedesignAnalysisService;
use App\Application\Watchlist\Services\WatchlistBacktestC22ExitCaptureShadowDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC22ExitCaptureShadowDiagnosticServiceTest extends TestCase
{
    public function test_it_generates_c22_exit_capture_shadow_artifact_and_preserves_safety_boundaries(): void
    {
        $outputPath = sys_get_temp_dir().'/c22-exit-capture-shadow-diagnostic-test.json';
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
                'shadow_profiles' => 'C22_S00_CANONICAL_BASELINE,C22_S01_EXIT_D1_CLOSE,C22_S02_EXIT_D2_CLOSE,C22_S06_FIRST_PROFITABLE_CLOSE_EXIT,C22_S08_PROFIT_LOCK_AFTER_MFE_1_00PCT,C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT,C22_S11_TRAILING_FROM_MFE_1_50PCT_GIVEBACK_0_75PCT',
                'executed_at' => '2025-05-21T23:59:59+07:00',
            ]
        );

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC', $result['scope']);
        $this->assertSame(1, $result['c22_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertArrayHasKey('exit_capture_signal_found', $result);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC', $artifact['scope']);
        $this->assertSame('C19_CATALOG_CANDIDATE_FAILED', $artifact['source_evidence']['c19_final_status']);
        $this->assertSame('C20_DATE_GATE_NOT_ENOUGH', $artifact['source_evidence']['c20_final_status']);
        $this->assertSame('C21_EXECUTION_SIGNAL_FOUND', $artifact['source_evidence']['c21_final_status']);
        $this->assertSame('d6c6c72d51b40a0c852ce9bbc6a452c55920df13', $artifact['source_evidence']['c21_all_param_artifact_hash']);
        $this->assertArrayHasKey('data_availability', $artifact);
        $this->assertArrayHasKey('shadow_profiles', $artifact);
        $this->assertArrayHasKey('pick_shadow_rows', $artifact);
        $this->assertArrayHasKey('canonical_summary', $artifact);
        $this->assertArrayHasKey('shadow_profile_summary', $artifact);
        $this->assertArrayHasKey('hold_day_comparison_summary', $artifact);
        $this->assertArrayHasKey('profit_lock_summary', $artifact);
        $this->assertArrayHasKey('breakeven_summary', $artifact);
        $this->assertArrayHasKey('trailing_summary', $artifact);
        $this->assertArrayHasKey('target_distance_summary', $artifact);
        $this->assertArrayHasKey('stop_distance_summary', $artifact);
        $this->assertArrayHasKey('decision', $artifact);
        $this->assertGreaterThanOrEqual(4, $artifact['canonical_summary']['evaluated_picks_count']);
        $this->assertGreaterThanOrEqual(7, count($artifact['shadow_profile_summary']));
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);
        $this->assertFalse($artifact['safety_boundaries']['future_path_price_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['shadow_exit_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['shadow_ret_net_used_for_selection']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C22_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['NO_C01_TO_C21_MUTATION']);
        $this->assertTrue($artifact['safety_boundaries']['NO_C21_REOPEN']);
        $this->assertSame('NEXT_OPEN', $artifact['price_evaluation_model']['ENTRY']);
        $this->assertSame('STOP_TP_OR_TIME', $artifact['price_evaluation_model']['EXIT']);
        $this->assertSame(5, $artifact['price_evaluation_model']['HOLD']);

        foreach ($artifact['pick_shadow_rows'] as $row) {
            $this->assertArrayHasKey('canonical_ret_net', $row);
            $this->assertArrayHasKey('shadow_ret_net', $row);
            $this->assertArrayHasKey('shadow_ret_delta_vs_canonical', $row);
            $this->assertArrayHasKey('shadow_profile_code', $row);
            $this->assertFalse($row['missing_path_data_flag']);
            $this->assertFalse($row['future_path_price_used_for_selection']);
            $this->assertFalse($row['shadow_ret_net_used_for_selection']);
        }

        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function test_it_blocks_unknown_shadow_profile_without_claiming_runtime_success(): void
    {
        $result = $this->service()->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            sys_get_temp_dir().'/c22-invalid-profile.json',
            ['shadow_profiles' => 'NOT_A_C22_PROFILE', 'overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C22_SHADOW_PROFILE_INVALID', $result['reason_code']);
        $this->assertSame(1, $result['c22_catalog_implementation_deferred']);
        $this->assertSame('NOT_CREATED', $result['c22_catalog_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
    }

    public function test_missing_path_data_is_reported_not_invented(): void
    {
        $outputPath = sys_get_temp_dir().'/c22-missing-path-test.json';
        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }

        $service = new WatchlistBacktestC22ExitCaptureShadowDiagnosticService(
            new WatchlistBacktestC22FakeCalendarReadService(),
            new WatchlistBacktestC22FakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            new WatchlistBacktestC22FakeSelectionDiagnosticService(),
            new WatchlistBacktestC22MissingPriceSeriesReadService(),
            new WatchlistBacktestC22FakeBenchmarkReadService()
        );
        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            ['overwrite' => true, 'param_ids' => '148', 'shadow_profiles' => 'C22_S00_CANONICAL_BASELINE']
        );

        $this->assertSame('BLOCKED', $result['status']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C22_DIAGNOSTIC_BLOCKED', $artifact['decision']['decision_status']);
        $this->assertSame(0, $artifact['canonical_summary']['evaluated_picks_count']);
        $this->assertGreaterThan(0, $artifact['canonical_summary']['path_missing_count']);
        $this->assertFalse($artifact['data_availability']['d1_to_d5_ohlc_available']);

        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }
    }

    private function service(): WatchlistBacktestC22ExitCaptureShadowDiagnosticService
    {
        return new WatchlistBacktestC22ExitCaptureShadowDiagnosticService(
            new WatchlistBacktestC22FakeCalendarReadService(),
            new WatchlistBacktestC22FakeParamGridRepository(),
            new WatchlistBacktestParamGridParamsetFactory(),
            new WatchlistBacktestC22FakeSelectionDiagnosticService(),
            new WatchlistBacktestC22FakePriceSeriesReadService(),
            new WatchlistBacktestC22FakeBenchmarkReadService()
        );
    }
}

class WatchlistBacktestC22FakeCalendarReadService extends MarketDataTradingCalendarReadService
{
    public function __construct()
    {
    }

    public function resolveTradingDates(string $fromDate, string $toDate): array
    {
        $dates = [
            '2023-01-02', '2023-01-03', '2023-01-04', '2023-01-05', '2023-01-06',
            '2023-01-09', '2023-01-10', '2023-01-11', '2023-01-12', '2023-01-13',
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

class WatchlistBacktestC22FakeParamGridRepository extends WatchlistBacktestParamGridRepository
{
    public function allForCatalog($catalogCode, $policyCode = 'WS'): array
    {
        $row = WatchlistBacktestC17ParamGridCatalog::rows()[0];
        $row['param_id'] = 148;
        return [$row];
    }
}

class WatchlistBacktestC22FakeSelectionDiagnosticService extends WatchlistBacktestC19SelectionModelRedesignAnalysisService
{
    public function __construct()
    {
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $items = [
            $this->item('2023-01-02', 1, 'AAA', 0.60, 0.020, 0.030),
            $this->item('2023-01-03', 2, 'BBB', 0.58, -0.010, 0.050),
            $this->item('2023-01-04', 3, 'CCC', 0.56, -0.020, 0.055),
            $this->item('2023-01-05', 4, 'DDD', 0.57, 0.005, 0.030),
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
                    'secondary_count' => 0,
                    'candidate_buffer_count' => 4,
                    'recommended_count' => 4,
                    'selected_items' => $items,
                ],
            ]],
            'artifact_hash' => sha1('fake-c22-selection'),
        ];
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
        ];
    }

    private function item(string $date, int $id, string $ticker, float $quality, float $roc20, float $atr): array
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
            'current_extension_failures' => [],
            'penalties' => [],
            'score_metrics' => [
                'dv20_idr' => 3000000000,
                'vol_ratio' => 1.50,
                'roc20' => $roc20,
                'atr14_pct' => $atr,
                'rs_20_vs_ihsg' => 0.010,
                'sector_roc20' => 0.010,
                'sector_rs_20_vs_ihsg' => 0.010,
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

class WatchlistBacktestC22FakePriceSeriesReadService extends MarketDataPublishedEodSeriesReadService
{
    public function __construct()
    {
    }

    public function readPublishedSeriesForDateTickerMap(string $fromDate, string $toDate, array $tickerCodesByTradeDate): array
    {
        $all = $this->allBars();
        $series = [];
        foreach ($tickerCodesByTradeDate as $date => $tickers) {
            foreach ($tickers as $ticker) {
                if (isset($all[$ticker][$date])) {
                    $series[$ticker][$date] = $all[$ticker][$date];
                }
            }
        }
        ksort($series, SORT_STRING);
        foreach ($series as &$rows) {
            ksort($rows, SORT_STRING);
        }
        unset($rows);
        return [
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLISHED_EOD_SERIES_RESOLVED',
            'series_by_ticker' => $series,
            'price_series_manifest' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'missing_price_dates' => [],
                'missing_price_rows' => [],
            ],
            'publication_manifest' => [],
        ];
    }

    private function allBars(): array
    {
        return [
            'AAA' => [
                '2023-01-02' => $this->bar('2023-01-02', 100, 101, 99, 100),
                '2023-01-03' => $this->bar('2023-01-03', 98, 100, 94, 96),
                '2023-01-04' => $this->bar('2023-01-04', 96, 97, 92, 93),
                '2023-01-05' => $this->bar('2023-01-05', 93, 95, 90, 92),
                '2023-01-06' => $this->bar('2023-01-06', 92, 94, 91, 93),
                '2023-01-09' => $this->bar('2023-01-09', 93, 94, 92, 93),
            ],
            'BBB' => [
                '2023-01-03' => $this->bar('2023-01-03', 100, 102, 98, 100),
                '2023-01-04' => $this->bar('2023-01-04', 100, 103, 99, 102),
                '2023-01-05' => $this->bar('2023-01-05', 102, 104, 100, 103),
                '2023-01-06' => $this->bar('2023-01-06', 103, 103, 97, 98),
                '2023-01-09' => $this->bar('2023-01-09', 98, 99, 95, 96),
                '2023-01-10' => $this->bar('2023-01-10', 96, 98, 94, 95),
            ],
            'CCC' => [
                '2023-01-04' => $this->bar('2023-01-04', 100, 101, 99, 100),
                '2023-01-05' => $this->bar('2023-01-05', 100, 100, 96, 97),
                '2023-01-06' => $this->bar('2023-01-06', 97, 99, 93, 94),
                '2023-01-09' => $this->bar('2023-01-09', 94, 96, 92, 93),
                '2023-01-10' => $this->bar('2023-01-10', 93, 95, 91, 92),
                '2023-01-11' => $this->bar('2023-01-11', 92, 94, 90, 91),
            ],
            'DDD' => [
                '2023-01-05' => $this->bar('2023-01-05', 100, 101, 99, 100),
                '2023-01-06' => $this->bar('2023-01-06', 100, 105, 99, 104),
                '2023-01-09' => $this->bar('2023-01-09', 104, 106, 103, 105),
                '2023-01-10' => $this->bar('2023-01-10', 105, 106, 104, 105),
                '2023-01-11' => $this->bar('2023-01-11', 105, 106, 104, 105),
                '2023-01-12' => $this->bar('2023-01-12', 105, 106, 104, 105),
            ],
        ];
    }

    private function bar(string $date, float $open, float $high, float $low, float $close): array
    {
        return [
            'trade_date' => $date,
            'open' => $open,
            'high' => $high,
            'low' => $low,
            'close' => $close,
            'volume' => 1000,
            'published' => true,
            'readable' => true,
            'publication_id' => 1,
            'publication_version' => 1,
            'run_id' => 1,
            'source_name' => 'fake',
        ];
    }
}

class WatchlistBacktestC22MissingPriceSeriesReadService extends WatchlistBacktestC22FakePriceSeriesReadService
{
    public function readPublishedSeriesForDateTickerMap(string $fromDate, string $toDate, array $tickerCodesByTradeDate): array
    {
        return [
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLISHED_EOD_SERIES_RESOLVED',
            'series_by_ticker' => [],
            'price_series_manifest' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'missing_price_dates' => array_keys($tickerCodesByTradeDate),
                'missing_price_rows' => [],
            ],
            'publication_manifest' => [],
        ];
    }
}

class WatchlistBacktestC22FakeBenchmarkReadService extends MarketBenchmarkReadService
{
    public function __construct()
    {
    }
}
