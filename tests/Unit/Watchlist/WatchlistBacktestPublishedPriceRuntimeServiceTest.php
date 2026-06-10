<?php

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Application\Watchlist\Services\WatchlistBacktestPublishedPriceRuntimeService;
use App\Application\Watchlist\Services\WatchlistBacktestRuntimeArtifactService;
use App\Application\Watchlist\Services\WatchlistBacktestStrategyService;

class WatchlistBacktestPublishedPriceRuntimeServiceTest extends TestCase
{
    public function test_runtime_orchestrates_strategy_before_price_read_and_exports_deterministic_artifact(): void
    {
        $firstPath = tempnam(sys_get_temp_dir(), 'wl-bt-runtime-');
        $secondPath = tempnam(sys_get_temp_dir(), 'wl-bt-runtime-');
        @unlink($firstPath);
        @unlink($secondPath);

        $strategy = $this->fakeStrategy($this->backtestPayload());
        $calendar = $this->fakeCalendar($this->calendarPayload());
        $prices = $this->fakePrices($this->pricePayload());
        $service = new WatchlistBacktestPublishedPriceRuntimeService(
            $strategy,
            $calendar,
            $prices,
            new WatchlistBacktestRuntimeArtifactService()
        );

        $first = $service->execute('2026-05-19', '2026-05-19', $firstPath, [
            'executed_at' => '2026-06-09T01:00:00+07:00',
        ]);
        $second = $service->execute('2026-05-19', '2026-05-19', $secondPath, [
            'executed_at' => '2026-06-09T01:05:00+07:00',
        ]);

        $this->assertTrue($first['is_ready']);
        $this->assertTrue($second['is_ready']);
        $this->assertSame($first['artifact_hash'], $second['artifact_hash']);
        $this->assertFileExists($firstPath);
        $this->assertFileExists($secondPath);
        $this->assertTrue($first['artifact']['runtime_execution']['trade_candidates_frozen_before_price_read']);
        $this->assertTrue($first['artifact']['runtime_execution']['future_price_used_for_evaluation_only']);
        $this->assertTrue($first['artifact']['runtime_execution']['strategy_payload_immutable']);
        $this->assertTrue($first['artifact']['backtest_contract']['price_series_consumed']);
        $this->assertTrue($first['artifact']['backtest_contract']['official_trading_calendar_consumed']);
        $this->assertFalse($first['artifact']['backtest_contract']['foundation_only']);
        $this->assertSame(5, $first['artifact']['price_series_manifest']['required_price_date_count']);
        $this->assertTrue($first['artifact']['price_series_manifest']['targeted_date_ticker_read']);
        $this->assertSame(5, $first['artifact']['price_series_manifest']['requested_ticker_date_pair_count']);
        $this->assertSame('PUBLISHED_EOD_OHLCV_CURRENT_READABLE_EXACT_DATE', $first['artifact']['paramset_snapshot']['backtest']['pricing_model']);
        $this->assertSame('TARGETED_DATE_TICKER_MAP', $first['artifact']['paramset_snapshot']['backtest']['price_read_mode']);
        $this->assertSame('RAW_TRADABLE_OHLC_REQUIRED', $first['artifact']['paramset_snapshot']['backtest']['source_price_mode']);
        $this->assertSame('OPEN_IF_GAP_THROUGH_TRIGGER', $first['artifact']['paramset_snapshot']['backtest']['gap_fill_rule']);
        $this->assertSame('IDX_EQUITY_PRICE_BANDS', $first['artifact']['paramset_snapshot']['backtest']['price_fraction_rule']);
        $this->assertSame('THEORETICAL_LEVEL', $first['artifact']['paramset_snapshot']['backtest']['price_fraction_reference']);
        $this->assertSame('CONSERVATIVE_STOP_FLOOR_TARGET_CEIL', $first['artifact']['paramset_snapshot']['backtest']['price_normalization_rule']);
        $this->assertSame(1, $first['artifact']['metrics']['canonical_eval_metrics']['days_covered']);
        $this->assertSame(1, $first['artifact']['metrics']['canonical_eval_metrics']['picks_count']);
        $this->assertTrue($first['metric_thresholds_resolved']);
        $this->assertFalse($first['metric_calibration_valid']);
        $this->assertFalse($first['artifact']['summary']['production_ready']);

        @unlink($firstPath);
        @unlink($secondPath);
    }

    public function test_internal_evaluate_window_keeps_full_evidence_in_memory_without_json_temp_write(): void
    {
        $service = new WatchlistBacktestPublishedPriceRuntimeService(
            $this->fakeStrategy($this->backtestPayload()),
            $this->fakeCalendar($this->calendarPayload()),
            $this->fakePrices($this->pricePayload()),
            new WatchlistBacktestRuntimeArtifactService()
        );

        $result = $service->evaluateWindow('2026-05-19', '2026-05-19', [
            'executed_at' => '2026-06-09T01:00:00+07:00',
        ]);

        $this->assertTrue($result['is_ready']);
        $this->assertSame('SKIPPED_IN_MEMORY_EVALUATION', $result['write']['status']);
        $this->assertNull($result['write']['path']);
        $this->assertNull($result['artifact']['runtime_execution']['output_path']);
        $this->assertSame(1, $result['evaluated_trade_count']);
    }

    public function test_runtime_binds_default_eval_thresholds_and_positive_volume_rule_before_strategy_replay(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-bt-runtime-paramset-'.uniqid('', true).'.json';
        $payload = $this->backtestPayload();
        $strategy = new class($payload) extends WatchlistBacktestStrategyService {
            private array $payload;
            public array $receivedParamset = [];

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function backtestForReplayWindow(
                array $tradeDates,
                array $confirmInputsByTradeDate = [],
                array $paramset = [],
                array $capitalInput = []
            ): array {
                $this->receivedParamset = $paramset;

                return $this->payload;
            }
        };
        $service = new WatchlistBacktestPublishedPriceRuntimeService(
            $strategy,
            $this->fakeCalendar($this->calendarPayload()),
            $this->fakePrices($this->pricePayload()),
            new WatchlistBacktestRuntimeArtifactService()
        );

        $result = $service->execute('2026-05-19', '2026-05-19', $path, [
            'paramset' => [
                'backtest' => [
                    'source_price_mode' => 'CALLER_OVERRIDE_NOT_ALLOWED',
                    'gap_fill_rule' => 'CALLER_OVERRIDE_NOT_ALLOWED',
                    'price_fraction_rule' => 'CALLER_OVERRIDE_NOT_ALLOWED',
                    'price_fraction_reference' => 'CALLER_OVERRIDE_NOT_ALLOWED',
                    'price_normalization_rule' => 'CALLER_OVERRIDE_NOT_ALLOWED',
                ],
            ],
        ]);

        $this->assertTrue($result['is_ready']);
        $this->assertSame('WS_ACTIVE_BOOTSTRAP', $strategy->receivedParamset['paramset_code']);
        $this->assertSame(40, $strategy->receivedParamset['eval']['min_trades_oos']['value']);
        $this->assertSame(120, $strategy->receivedParamset['eval']['min_trades']['value']);
        $this->assertSame(0, $strategy->receivedParamset['eval']['min_days_covered']['value']);
        $this->assertSame(-0.03, $strategy->receivedParamset['eval']['min_p25_ret_net_top']['value']);
        $this->assertSame(0.45, $strategy->receivedParamset['eval']['min_month_win_rate_min']['value']);
        $this->assertSame('POSITIVE_VOLUME_REQUIRED', $strategy->receivedParamset['backtest']['tradable_bar_rule']);
        $this->assertSame(1, $strategy->receivedParamset['backtest']['min_tradable_volume']);
        $this->assertSame('PUBLISHED_EOD_OHLCV_CURRENT_READABLE_EXACT_DATE', $strategy->receivedParamset['backtest']['pricing_model']);
        $this->assertSame('TARGETED_DATE_TICKER_MAP', $strategy->receivedParamset['backtest']['price_read_mode']);
        $this->assertSame('RAW_TRADABLE_OHLC_REQUIRED', $strategy->receivedParamset['backtest']['source_price_mode']);
        $this->assertSame('OPEN_IF_GAP_THROUGH_TRIGGER', $strategy->receivedParamset['backtest']['gap_fill_rule']);
        $this->assertSame('IDX_EQUITY_PRICE_BANDS', $strategy->receivedParamset['backtest']['price_fraction_rule']);
        $this->assertSame('THEORETICAL_LEVEL', $strategy->receivedParamset['backtest']['price_fraction_reference']);
        $this->assertSame('CONSERVATIVE_STOP_FLOOR_TARGET_CEIL', $strategy->receivedParamset['backtest']['price_normalization_rule']);
        $this->assertSame(1.5, $strategy->receivedParamset['risk']['stop_atr_mult']);
        $this->assertSame(1.5, $strategy->receivedParamset['risk']['min_rr']);

        @unlink($path);
    }

    public function test_runtime_blocks_artifact_export_when_eval_thresholds_are_unresolved(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-bt-runtime-thresholds-'.uniqid('', true).'.json';
        $payload = $this->backtestPayload();
        $payload['paramset_snapshot']['eval'] = [];
        $service = new WatchlistBacktestPublishedPriceRuntimeService(
            $this->fakeStrategy($payload),
            $this->fakeCalendar($this->calendarPayload()),
            $this->fakePrices($this->pricePayload()),
            new WatchlistBacktestRuntimeArtifactService()
        );

        $result = $service->execute('2026-05-19', '2026-05-19', $path);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WS_BT_EVAL_METRICS_MISSING', $result['reason_code']);
        $this->assertSame([
            'min_trades',
            'min_days_covered',
            'min_p25_ret_net_top',
            'min_month_win_rate_min',
            'min_month_avg_ret_net_min',
        ], $result['diagnostics'][0]['missing_thresholds']);
        $this->assertFileDoesNotExist($path);
    }

    public function test_runtime_blocks_when_exact_date_publication_is_missing(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-bt-runtime-missing-'.uniqid('', true).'.json';
        $pricePayload = $this->pricePayload();
        $pricePayload['ready'] = false;
        $pricePayload['is_ready'] = false;
        $pricePayload['reason_code'] = 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE';
        $pricePayload['diagnostics'] = [[
            'trade_date' => '2026-05-20',
            'reason_code' => 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE',
            'fatal' => true,
        ]];

        $service = new WatchlistBacktestPublishedPriceRuntimeService(
            $this->fakeStrategy($this->backtestPayload()),
            $this->fakeCalendar($this->calendarPayload()),
            $this->fakePrices($pricePayload),
            new WatchlistBacktestRuntimeArtifactService()
        );

        $result = $service->execute('2026-05-19', '2026-05-19', $path);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $result['reason_code']);
        $this->assertFileDoesNotExist($path);
    }

    public function test_runtime_fails_closed_when_strategy_detects_future_effective_source(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-bt-runtime-lookahead-'.uniqid('', true).'.json';
        $payload = $this->backtestPayload();
        $payload['ready'] = false;
        $payload['is_ready'] = false;
        $payload['reason_code'] = 'WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION';
        $payload['diagnostics'] = [[
            'trade_date' => '2026-05-19',
            'reason_code' => 'WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION',
            'fatal' => true,
        ]];

        $service = new WatchlistBacktestPublishedPriceRuntimeService(
            $this->fakeStrategy($payload),
            $this->fakeCalendar($this->calendarPayload()),
            $this->fakePrices($this->pricePayload()),
            new WatchlistBacktestRuntimeArtifactService()
        );

        $result = $service->execute('2026-05-19', '2026-05-19', $path);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION', $result['reason_code']);
        $this->assertFileDoesNotExist($path);
    }

    public function test_runtime_keeps_missing_price_row_as_reason_coded_skip_instead_of_zero_return(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wl-bt-runtime-skip-'.uniqid('', true).'.json';
        $pricePayload = $this->pricePayload();
        unset($pricePayload['series_by_ticker']['AAA']['2026-05-24']);
        $pricePayload['price_series_manifest']['missing_price_dates'] = ['2026-05-24'];

        $service = new WatchlistBacktestPublishedPriceRuntimeService(
            $this->fakeStrategy($this->backtestPayload()),
            $this->fakeCalendar($this->calendarPayload()),
            $this->fakePrices($pricePayload),
            new WatchlistBacktestRuntimeArtifactService()
        );

        $result = $service->execute('2026-05-19', '2026-05-19', $path);

        $this->assertTrue($result['is_ready']);
        $this->assertFalse($result['metrics_ready']);
        $this->assertSame(0, $result['evaluated_trade_count']);
        $this->assertContains('BT_SKIP_MISSING_OHLC_EXIT', array_column($result['artifact']['diagnostics'], 'reason_code'));
        $this->assertNull($result['artifact']['metrics']['evaluated_trades'][0]['ret_net']);

        @unlink($path);
    }

    private function fakeStrategy(array $payload): WatchlistBacktestStrategyService
    {
        return new class($payload) extends WatchlistBacktestStrategyService {
            private array $payload;

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function backtestForReplayWindow(
                array $tradeDates,
                array $confirmInputsByTradeDate = [],
                array $paramset = [],
                array $capitalInput = []
            ): array {
                return $this->payload;
            }
        };
    }

    private function fakeCalendar(array $payload): MarketDataTradingCalendarReadService
    {
        return new class($payload) extends MarketDataTradingCalendarReadService {
            private array $payload;

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function resolveReplayWindow(string $fromDate, string $toDate, int $forwardTradingDays = 5): array
            {
                return $this->payload;
            }
        };
    }

    private function fakePrices(array $payload): MarketDataPublishedEodSeriesReadService
    {
        return new class($payload) extends MarketDataPublishedEodSeriesReadService {
            private array $payload;

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function readPublishedSeries(
                string $fromDate,
                string $toDate,
                array $tickerCodes,
                array $exactTradeDates = []
            ): array {
                return $this->payload;
            }

            public function readPublishedSeriesForDateTickerMap(
                string $fromDate,
                string $toDate,
                array $tickerCodesByTradeDate
            ): array {
                return $this->payload;
            }
        };
    }

    private function calendarPayload(): array
    {
        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'READABLE_TRADING_CALENDAR_RESOLVED',
            'trade_dates' => ['2026-05-19'],
            'calendar_dates' => ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-23', '2026-05-24'],
            'calendar_source' => 'market_calendar',
            'calendar_sources' => ['idx_official'],
            'calendar_hash' => str_repeat('a', 40),
            'coverage' => [
                'replay_date_count' => 1,
                'calendar_date_count' => 6,
                'forward_date_count' => 5,
                'horizon_complete' => true,
            ],
            'diagnostics' => [],
        ];
    }

    private function pricePayload(): array
    {
        $bars = [];
        foreach (['2026-05-20', '2026-05-21', '2026-05-22', '2026-05-23', '2026-05-24'] as $index => $date) {
            $bars['AAA'][$date] = [
                'trade_date' => $date,
                'ticker_id' => 1,
                'ticker_code' => 'AAA',
                'open' => 100.0,
                'high' => 103.0,
                'low' => 99.0,
                'close' => 101.0 + $index,
                'volume' => 100000,
                'publication_id' => 20 + $index,
                'publication_version' => 1,
                'run_id' => 10 + $index,
                'source_name' => 'API_FREE',
                'published' => true,
                'readable' => true,
            ];
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLISHED_EOD_SERIES_RESOLVED',
            'series_by_ticker' => $bars,
            'publication_manifest' => array_values(array_map(function (string $date, int $index): array {
                return [
                    'trade_date' => $date,
                    'is_readable' => true,
                    'publication_id' => 20 + $index,
                    'publication_version' => 1,
                    'run_id' => 10 + $index,
                    'row_count' => 1,
                ];
            }, array_keys($bars['AAA']), array_keys(array_keys($bars['AAA'])))),
            'price_series_manifest' => [
                'ticker_count' => 1,
                'requested_ticker_date_pair_count' => 5,
                'required_price_date_count' => 5,
                'resolved_price_date_count' => 5,
                'resolved_price_row_count' => 5,
                'missing_publication_dates' => [],
                'missing_price_dates' => [],
                'missing_price_rows' => [],
                'source_payload_hash' => str_repeat('b', 40),
                'targeted_date_ticker_read' => true,
                'exact_date_resolution_only' => true,
                'no_latest_fallback' => true,
                'no_max_trade_date' => true,
            ],
            'diagnostics' => [],
        ];
    }

    private function backtestPayload(): array
    {
        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_FOUNDATION_READY',
            'meta' => [
                'strategy_code' => 'WS',
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            ],
            'source_contract' => [
                'no_raw_market_data' => true,
                'no_latest_shortcut' => true,
                'no_max_trade_date_shortcut' => true,
                'no_plan_mutation' => true,
                'no_recommendation_mutation' => true,
                'no_confirm_mutation' => true,
            ],
            'backtest_contract' => [
                'no_lookahead' => true,
                'deterministic_replay' => true,
                'publication_aware_replay' => true,
                'explicit_replay_window_only' => true,
            ],
            'paramset_snapshot' => [
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
                'eval' => [
                    'min_trades' => 120,
                    'min_days_covered' => 1,
                    'min_p25_ret_net_top' => -0.03,
                    'min_month_win_rate_min' => 0.45,
                    'min_month_avg_ret_net_min' => -0.01,
                ],
                'backtest' => [
                    'pricing_model' => 'FOUNDATION_ONLY_PRICE_SERIES_NOT_CONSUMED',
                    'price_read_mode' => 'UNBOUND',
                    'holding_days' => 5,
                    'fee_buy_idr' => 2500,
                    'fee_sell_idr' => 2500,
                    'notional_idr' => 10000000,
                    'lot_size' => 100,
                    'slippage_entry_pct' => 0,
                    'slippage_exit_pct' => 0,
                ],
            ],
            'replay_window' => [
                'from_trade_date' => '2026-05-19',
                'to_trade_date' => '2026-05-19',
                'trade_dates' => ['2026-05-19'],
                'explicit_window' => true,
            ],
            'items' => [[
                'trade_date' => '2026-05-19',
                'ticker_id' => 1,
                'ticker' => 'AAA',
                'recommended_flag' => true,
                'active_trade_evaluation' => true,
                'reason_codes' => ['WS_REC_SELECTED'],
            ]],
            'trades' => [[
                'trade_date' => '2026-05-19',
                'ticker_id' => 1,
                'ticker' => 'AAA',
                'bucket_code' => 'TOP_PICKS',
                'plan_rank' => 1,
                'recommendation_rank' => 1,
                'trade_state' => 'EVALUATION_CANDIDATE',
                'reason_codes' => ['WS_REC_SELECTED'],
            ]],
            'evaluations' => [],
            'summary' => [
                'days_requested' => 1,
                'days_evaluated' => 1,
                'items_count' => 1,
                'picks_count' => 1,
                'evaluations_count' => 1,
                'empty_recommendation_days' => 0,
                'metrics_ready' => false,
                'production_ready' => false,
                'reason_codes' => [],
            ],
            'diagnostics' => [],
            'artifact_manifest' => [
                'official_backtest_tables' => [
                    'watchlist_bt_param_grid',
                    'watchlist_bt_eval',
                    'watchlist_bt_picks_ws',
                    'watchlist_bt_universe_ws',
                    'watchlist_bt_cutoffs_ws',
                    'watchlist_bt_oos_eval_ws',
                ],
            ],
        ];
    }
}
