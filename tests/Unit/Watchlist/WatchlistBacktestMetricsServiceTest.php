<?php

use App\Application\Watchlist\Services\WatchlistBacktestMetricsService;

class WatchlistBacktestMetricsServiceTest extends TestCase
{
    public function test_metrics_fails_safe_when_published_price_series_and_calendar_are_missing(): void
    {
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics($this->backtestPayload());

        $this->assertFalse($metrics['ready']);
        $this->assertSame('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $metrics['reason_code']);
        $this->assertSame(1, $metrics['counts']['total_replay_dates']);
        $this->assertSame(1, $metrics['counts']['total_recommendations']);
        $this->assertSame(0, $metrics['counts']['total_evaluated_trades']);
        $this->assertSame(1, $metrics['counts']['rejected_no_data_evaluation_count']);
        $this->assertArrayHasKey('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $metrics['reason_code_distribution']);
        $this->assertArrayHasKey('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', $metrics['reason_code_distribution']);
        $this->assertArrayHasKey('WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', $metrics['reason_code_distribution']);
        $this->assertTrue($metrics['metrics_contract']['published_price_series_only']);
        $this->assertTrue($metrics['metrics_contract']['no_raw_market_data']);
        $this->assertTrue($metrics['metrics_contract']['no_latest_shortcut']);
        $this->assertTrue($metrics['metrics_contract']['positive_volume_required_for_execution']);
        $this->assertTrue($metrics['metrics_contract']['zero_volume_bar_is_published_but_non_executable']);
        $this->assertSame('RAW_TRADABLE_OHLC_REQUIRED', $metrics['metrics_contract']['source_price_mode']);
        $this->assertSame('OPEN_IF_GAP_THROUGH_TRIGGER', $metrics['metrics_contract']['gap_fill_rule']);
        $this->assertSame('IDX_EQUITY_PRICE_BANDS', $metrics['metrics_contract']['price_fraction_rule']);
    }

    public function test_metrics_evaluates_time_exit_with_published_price_series_and_explicit_calendar(): void
    {
        $payload = $this->backtestPayload([
            'backtest' => [
                'notional_idr' => 1000000,
                'lot_size' => 100,
                'fee_buy_idr' => 0,
                'fee_sell_idr' => 0,
                'slippage_entry_pct' => 0,
                'slippage_exit_pct' => 0,
                'holding_days' => 5,
            ],
        ]);

        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $payload,
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 100, 'high' => 102, 'low' => 99, 'close' => 101],
                '2026-05-21' => ['open' => 101, 'high' => 102, 'low' => 100, 'close' => 101],
                '2026-05-22' => ['open' => 101, 'high' => 103, 'low' => 100, 'close' => 102],
                '2026-05-25' => ['open' => 102, 'high' => 104, 'low' => 101, 'close' => 103],
                '2026-05-26' => ['open' => 103, 'high' => 106, 'low' => 102, 'close' => 105],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $this->assertTrue($metrics['ready']);
        $this->assertSame('WATCHLIST_BACKTEST_METRICS_READY', $metrics['reason_code']);
        $this->assertSame(1, $metrics['counts']['total_evaluated_trades']);
        $this->assertSame(1.0, $metrics['returns']['win_rate']);
        $this->assertEqualsWithDelta(0.05, $metrics['returns']['average_return'], 0.0000001);
        $this->assertEqualsWithDelta(0.05, $metrics['returns']['median_return'], 0.0000001);
        $this->assertEqualsWithDelta(0.05, $metrics['returns']['max_gain'], 0.0000001);
        $this->assertEqualsWithDelta(0.05, $metrics['returns']['max_loss'], 0.0000001);
        $this->assertSame(1, $metrics['exit_outcomes']['timeout_hold_expired_count']);
        $this->assertSame('WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED', $metrics['evaluated_trades'][0]['exit_reason_code']);
        $this->assertContains('WATCHLIST_BACKTEST_TARGET_STOP_LEVEL_UNAVAILABLE', $metrics['evaluated_trades'][0]['reason_codes']);
    }

    public function test_metrics_counts_target_stop_timeout_and_reason_code_distribution(): void
    {
        $payload = $this->backtestPayload([
            'backtest' => [
                'notional_idr' => 1000000,
                'lot_size' => 100,
                'fee_buy_idr' => 0,
                'fee_sell_idr' => 0,
                'slippage_entry_pct' => 0,
                'slippage_exit_pct' => 0,
                'holding_days' => 5,
                'target_pct' => 0.05,
                'stop_pct' => 0.03,
            ],
        ], [
            $this->trade('2026-05-19', 1, 'AAA'),
            $this->trade('2026-05-19', 2, 'BBB'),
            $this->trade('2026-05-19', 3, 'CCC'),
        ]);

        $priceSeries = array_replace_recursive(
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-21' => ['open' => 100, 'high' => 106, 'low' => 99, 'close' => 105],
                '2026-05-22' => ['open' => 105, 'high' => 106, 'low' => 104, 'close' => 105],
                '2026-05-25' => ['open' => 105, 'high' => 106, 'low' => 104, 'close' => 105],
                '2026-05-26' => ['open' => 105, 'high' => 106, 'low' => 104, 'close' => 105],
            ]),
            $this->priceSeries('BBB', [
                '2026-05-20' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-21' => ['open' => 100, 'high' => 101, 'low' => 96, 'close' => 97],
                '2026-05-22' => ['open' => 97, 'high' => 98, 'low' => 96, 'close' => 97],
                '2026-05-25' => ['open' => 97, 'high' => 98, 'low' => 96, 'close' => 97],
                '2026-05-26' => ['open' => 97, 'high' => 98, 'low' => 96, 'close' => 97],
            ]),
            $this->priceSeries('CCC', [
                '2026-05-20' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-21' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-22' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-25' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-26' => ['open' => 100, 'high' => 102, 'low' => 99, 'close' => 102],
            ])
        );

        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $payload,
            $priceSeries,
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $this->assertTrue($metrics['ready']);
        $this->assertSame(3, $metrics['counts']['total_evaluated_trades']);
        $this->assertSame(1, $metrics['exit_outcomes']['hit_target_count']);
        $this->assertSame(1, $metrics['exit_outcomes']['hit_stop_count']);
        $this->assertSame(1, $metrics['exit_outcomes']['timeout_hold_expired_count']);
        $this->assertSame(2 / 3, $metrics['returns']['win_rate']);
        $this->assertArrayHasKey('WATCHLIST_BACKTEST_EXIT_TARGET', $metrics['reason_code_distribution']);
        $this->assertArrayHasKey('WATCHLIST_BACKTEST_EXIT_STOP', $metrics['reason_code_distribution']);
        $this->assertArrayHasKey('WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED', $metrics['reason_code_distribution']);
    }

    public function test_metrics_skips_published_zero_volume_entry_as_non_tradable(): void
    {
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $this->backtestPayload(),
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 100, 'high' => 100, 'low' => 100, 'close' => 100, 'volume' => 0],
                '2026-05-21' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-22' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-25' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-26' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $this->assertFalse($metrics['ready']);
        $this->assertSame(0, $metrics['counts']['total_evaluated_trades']);
        $this->assertSame(1, $metrics['counts']['rejected_no_data_evaluation_count']);
        $this->assertSame('BT_SKIP_NO_TRADABLE_ENTRY', $metrics['evaluated_trades'][0]['reason_code']);
        $this->assertSame(0, $metrics['evaluated_trades'][0]['entry_volume']);
        $this->assertNull($metrics['evaluated_trades'][0]['ret_net']);
        $this->assertArrayHasKey('BT_SKIP_NO_TRADABLE_ENTRY', $metrics['reason_code_distribution']);
        $this->assertSame('Published entry bar exists but has no executable market volume.', $metrics['diagnostics'][0]['message']);
    }

    public function test_metrics_ignores_zero_volume_exit_bars_and_skips_non_tradable_final_exit(): void
    {
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $this->backtestPayload(),
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100, 'volume' => 100000],
                '2026-05-21' => ['open' => 100, 'high' => 120, 'low' => 80, 'close' => 100, 'volume' => 0],
                '2026-05-22' => ['open' => 100, 'high' => 120, 'low' => 80, 'close' => 100, 'volume' => 0],
                '2026-05-25' => ['open' => 100, 'high' => 120, 'low' => 80, 'close' => 100, 'volume' => 0],
                '2026-05-26' => ['open' => 100, 'high' => 120, 'low' => 80, 'close' => 100, 'volume' => 0],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $this->assertFalse($metrics['ready']);
        $this->assertSame('BT_SKIP_NO_TRADABLE_EXIT', $metrics['evaluated_trades'][0]['reason_code']);
        $this->assertSame('2026-05-26', $metrics['evaluated_trades'][0]['exit_trade_date']);
        $this->assertSame(0, $metrics['evaluated_trades'][0]['exit_volume']);
        $this->assertSame(
            ['2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26'],
            $metrics['evaluated_trades'][0]['ignored_non_tradable_exit_dates']
        );
        $this->assertNull($metrics['evaluated_trades'][0]['ret_net']);
        $this->assertArrayHasKey('BT_SKIP_NO_TRADABLE_EXIT', $metrics['reason_code_distribution']);
    }

    public function test_metric_sufficiency_resolves_zero_coverage_sentinel_to_seventy_percent_window(): void
    {
        $payload = $this->backtestPayload([
            'eval' => [
                'min_trades' => 120,
                'min_days_covered' => 0,
                'min_p25_ret_net_top' => -0.03,
                'min_month_win_rate_min' => 0.45,
                'min_month_avg_ret_net_min' => -0.01,
            ],
        ]);
        $payload['replay_window']['trade_dates'] = [
            '2026-05-11', '2026-05-12', '2026-05-13', '2026-05-14', '2026-05-15',
            '2026-05-18', '2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22',
        ];

        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $payload,
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-21' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-22' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-25' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
                '2026-05-26' => ['open' => 100, 'high' => 101, 'low' => 99, 'close' => 100],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $sufficiency = $metrics['metric_sufficiency'];
        $this->assertSame(1, $metrics['canonical_eval_metrics']['days_covered']);
        $this->assertSame(10, $sufficiency['gating_thresholds']['total_trading_days_in_window']);
        $this->assertTrue($sufficiency['thresholds_resolved']);
        $this->assertSame(0, $sufficiency['configured_thresholds']['min_days_covered']);
        $this->assertSame(7, $sufficiency['effective_thresholds']['min_days_covered']);
        $this->assertSame(7, $sufficiency['gating_thresholds']['min_days_covered']);
        $this->assertSame('CEIL_70_PERCENT_OF_TOTAL_TRADING_DAYS', $sufficiency['coverage_threshold_rule']);
        $this->assertFalse($sufficiency['gates']['minimum_coverage']);
        $this->assertFalse($sufficiency['calibration_valid']);
    }

    public function test_metric_coverage_gate_passes_at_dynamic_seventy_percent_floor(): void
    {
        $trades = [];
        $bars = [];
        $calendar = [
            '2026-05-11', '2026-05-12', '2026-05-13', '2026-05-14', '2026-05-15',
            '2026-05-18', '2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22',
            '2026-05-25', '2026-05-26', '2026-05-27', '2026-05-28', '2026-05-29',
        ];
        $replayDates = array_slice($calendar, 0, 10);

        foreach (array_slice($replayDates, 0, 7) as $index => $tradeDate) {
            $ticker = 'T'.($index + 1);
            $trades[] = $this->trade($tradeDate, $index + 1, $ticker);
            $entryIndex = array_search($tradeDate, $calendar, true) + 1;
            foreach (array_slice($calendar, $entryIndex, 5) as $priceDate) {
                $bars[$ticker][$priceDate] = [
                    'published' => true,
                    'open' => 100,
                    'high' => 101,
                    'low' => 99,
                    'close' => 100,
                    'volume' => 100000,
                ];
            }
        }

        $payload = $this->backtestPayload([
            'eval' => [
                'min_trades' => 120,
                'min_days_covered' => 0,
                'min_p25_ret_net_top' => -0.03,
                'min_month_win_rate_min' => 0.45,
                'min_month_avg_ret_net_min' => -0.01,
            ],
        ], $trades);
        $payload['replay_window']['trade_dates'] = $replayDates;

        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics($payload, $bars, $calendar);
        $sufficiency = $metrics['metric_sufficiency'];

        $this->assertSame(7, $metrics['canonical_eval_metrics']['days_covered']);
        $this->assertSame(7, $sufficiency['effective_thresholds']['min_days_covered']);
        $this->assertTrue($sufficiency['gates']['minimum_coverage']);
        $this->assertFalse($sufficiency['calibration_valid']);
    }

    public function test_metrics_derives_canonical_atr_stop_and_rr_target_when_plan_levels_are_absent(): void
    {
        $trade = $this->trade('2026-05-19', 1, 'AAA');
        $trade['atr14_pct'] = 0.04;
        $payload = $this->backtestPayload([
            'risk' => [
                'stop_atr_mult' => 1.5,
                'min_rr' => 1.5,
            ],
            'backtest' => [
                'notional_idr' => 1000000,
                'lot_size' => 100,
                'fee_buy_idr' => 0,
                'fee_sell_idr' => 0,
                'slippage_entry_pct' => 0,
                'slippage_exit_pct' => 0,
                'holding_days' => 5,
            ],
        ], [$trade]);

        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $payload,
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 100, 'high' => 102, 'low' => 99, 'close' => 101],
                '2026-05-21' => ['open' => 101, 'high' => 102, 'low' => 93, 'close' => 95],
                '2026-05-22' => ['open' => 95, 'high' => 96, 'low' => 94, 'close' => 95],
                '2026-05-25' => ['open' => 95, 'high' => 96, 'low' => 94, 'close' => 95],
                '2026-05-26' => ['open' => 95, 'high' => 96, 'low' => 94, 'close' => 95],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $evaluation = $metrics['evaluated_trades'][0];
        $this->assertTrue($evaluation['metrics_ready']);
        $this->assertSame('WATCHLIST_BACKTEST_EXIT_STOP', $evaluation['exit_reason_code']);
        $this->assertSame('ATR_RR_FALLBACK', $evaluation['target_stop_source']);
        $this->assertEqualsWithDelta(94.0, $evaluation['stop_price'], 0.000001);
        $this->assertEqualsWithDelta(109.0, $evaluation['target_price'], 0.000001);
        $this->assertEqualsWithDelta(-0.06, $evaluation['ret_net'], 0.000001);
        $this->assertContains('WATCHLIST_BACKTEST_TARGET_STOP_ATR_RR_FALLBACK', $evaluation['reason_codes']);
    }

    public function test_gap_through_stop_fills_at_executable_open_not_theoretical_stop(): void
    {
        $trade = $this->trade('2026-05-19', 1, 'AAA');
        $trade['stop_price'] = 48.5037;
        $trade['target_price'] = 53.0;
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $this->backtestPayload([
                'backtest' => [
                    'notional_idr' => 1000000,
                    'lot_size' => 100,
                    'fee_buy_idr' => 0,
                    'fee_sell_idr' => 0,
                    'slippage_entry_pct' => 0,
                    'slippage_exit_pct' => 0,
                    'holding_days' => 5,
                ],
            ], [$trade]),
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 50, 'high' => 51, 'low' => 49, 'close' => 50],
                '2026-05-21' => ['open' => 47, 'high' => 48, 'low' => 46, 'close' => 47],
                '2026-05-22' => ['open' => 47, 'high' => 48, 'low' => 46, 'close' => 47],
                '2026-05-25' => ['open' => 47, 'high' => 48, 'low' => 46, 'close' => 47],
                '2026-05-26' => ['open' => 47, 'high' => 48, 'low' => 46, 'close' => 47],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $evaluation = $metrics['evaluated_trades'][0];
        $this->assertSame('WATCHLIST_BACKTEST_EXIT_STOP', $evaluation['exit_reason_code']);
        $this->assertSame(48.0, $evaluation['stop_trigger_price']);
        $this->assertSame(48.5037, $evaluation['stop_price']);
        $this->assertSame(48.0, $evaluation['trigger_price']);
        $this->assertSame(47.0, $evaluation['executed_price']);
        $this->assertSame('GAP_THROUGH_STOP_AT_OPEN', $evaluation['fill_rule']);
        $this->assertTrue($evaluation['gap_detected']);
        $this->assertEqualsWithDelta(-0.06, $evaluation['ret_net'], 0.000001);
        $this->assertContains('BT_GAP_THROUGH_STOP_AT_OPEN', $evaluation['reason_codes']);
    }

    public function test_gap_through_target_fills_at_executable_open(): void
    {
        $trade = $this->trade('2026-05-19', 1, 'AAA');
        $trade['stop_price'] = 48.0;
        $trade['target_price'] = 51.2;
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $this->backtestPayload([
                'backtest' => [
                    'notional_idr' => 1000000,
                    'lot_size' => 100,
                    'fee_buy_idr' => 0,
                    'fee_sell_idr' => 0,
                    'slippage_entry_pct' => 0,
                    'slippage_exit_pct' => 0,
                    'holding_days' => 5,
                ],
            ], [$trade]),
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 50, 'high' => 51, 'low' => 49, 'close' => 50],
                '2026-05-21' => ['open' => 55, 'high' => 56, 'low' => 54, 'close' => 55],
                '2026-05-22' => ['open' => 55, 'high' => 56, 'low' => 54, 'close' => 55],
                '2026-05-25' => ['open' => 55, 'high' => 56, 'low' => 54, 'close' => 55],
                '2026-05-26' => ['open' => 55, 'high' => 56, 'low' => 54, 'close' => 55],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $evaluation = $metrics['evaluated_trades'][0];
        $this->assertSame('WATCHLIST_BACKTEST_EXIT_TARGET', $evaluation['exit_reason_code']);
        $this->assertSame(52.0, $evaluation['target_trigger_price']);
        $this->assertSame(51.2, $evaluation['target_price']);
        $this->assertSame(52.0, $evaluation['trigger_price']);
        $this->assertSame(55.0, $evaluation['executed_price']);
        $this->assertSame('GAP_THROUGH_TARGET_AT_OPEN', $evaluation['fill_rule']);
        $this->assertTrue($evaluation['gap_detected']);
        $this->assertEqualsWithDelta(0.10, $evaluation['ret_net'], 0.000001);
        $this->assertContains('BT_GAP_THROUGH_TARGET_AT_OPEN', $evaluation['reason_codes']);
    }

    public function test_intraday_levels_use_conservative_idx_price_fraction_normalization(): void
    {
        $trade = $this->trade('2026-05-19', 1, 'AAA');
        $trade['stop_price'] = 226.0809374902;
        $trade['target_price'] = 330.8785937647;
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $this->backtestPayload([
                'backtest' => [
                    'notional_idr' => 1000000,
                    'lot_size' => 100,
                    'fee_buy_idr' => 0,
                    'fee_sell_idr' => 0,
                    'slippage_entry_pct' => 0,
                    'slippage_exit_pct' => 0,
                    'holding_days' => 5,
                ],
            ], [$trade]),
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 268, 'high' => 270, 'low' => 266, 'close' => 268],
                '2026-05-21' => ['open' => 270, 'high' => 330, 'low' => 268, 'close' => 328],
                '2026-05-22' => ['open' => 328, 'high' => 332, 'low' => 326, 'close' => 330],
                '2026-05-25' => ['open' => 330, 'high' => 332, 'low' => 328, 'close' => 330],
                '2026-05-26' => ['open' => 330, 'high' => 332, 'low' => 328, 'close' => 330],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $evaluation = $metrics['evaluated_trades'][0];
        $this->assertSame(226.0, $evaluation['stop_trigger_price']);
        $this->assertSame(332.0, $evaluation['target_trigger_price']);
        $this->assertSame('2026-05-22', $evaluation['exit_trade_date']);
        $this->assertSame(332.0, $evaluation['executed_price']);
        $this->assertSame('INTRADAY_TARGET_AT_NORMALIZED_TRIGGER', $evaluation['fill_rule']);
        $this->assertFalse($evaluation['gap_detected']);
        $this->assertSame('IDX_EQUITY_PRICE_BANDS', $evaluation['price_fraction_rule']);
        $this->assertSame('THEORETICAL_LEVEL', $evaluation['price_fraction_reference']);
        $this->assertSame('CONSERVATIVE_STOP_FLOOR_TARGET_CEIL', $evaluation['price_normalization_rule']);
    }

    public function test_fractional_adjusted_like_entry_ohlc_is_rejected(): void
    {
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $this->backtestPayload(),
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 494.4835, 'high' => 504.0851, 'low' => 474.3201, 'close' => 474.3201],
                '2026-05-21' => ['open' => 474, 'high' => 480, 'low' => 470, 'close' => 474],
                '2026-05-22' => ['open' => 474, 'high' => 480, 'low' => 470, 'close' => 474],
                '2026-05-25' => ['open' => 474, 'high' => 480, 'low' => 470, 'close' => 474],
                '2026-05-26' => ['open' => 474, 'high' => 480, 'low' => 470, 'close' => 474],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $evaluation = $metrics['evaluated_trades'][0];
        $this->assertFalse($evaluation['metrics_ready']);
        $this->assertSame('BT_SKIP_NON_EXECUTABLE_PRICE_ENTRY', $evaluation['reason_code']);
        $this->assertContains('open', $evaluation['invalid_price_fields']);
        $this->assertSame('RAW_TRADABLE_OHLC_REQUIRED', $evaluation['source_price_mode']);
        $this->assertNull($evaluation['ret_net']);
    }

    public function test_fractional_adjusted_like_exit_ohlc_is_rejected(): void
    {
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $this->backtestPayload(),
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
                '2026-05-21' => ['open' => 494.4835, 'high' => 504.0851, 'low' => 474.3201, 'close' => 474.3201],
                '2026-05-22' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
                '2026-05-25' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
                '2026-05-26' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $evaluation = $metrics['evaluated_trades'][0];
        $this->assertFalse($evaluation['metrics_ready']);
        $this->assertSame('BT_SKIP_NON_EXECUTABLE_PRICE_EXIT', $evaluation['reason_code']);
        $this->assertSame('2026-05-21', $evaluation['exit_trade_date']);
        $this->assertContains('open', $evaluation['invalid_price_fields']);
        $this->assertNull($evaluation['ret_net']);
    }

    public function test_integer_raw_ohlc_is_not_rejected_when_bar_crosses_price_bands(): void
    {
        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $this->backtestPayload([
                'backtest' => [
                    'notional_idr' => 1000000,
                    'lot_size' => 100,
                    'fee_buy_idr' => 0,
                    'fee_sell_idr' => 0,
                    'slippage_entry_pct' => 0,
                    'slippage_exit_pct' => 0,
                    'holding_days' => 5,
                ],
            ]),
            $this->priceSeries('AAA', [
                '2026-05-20' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
                '2026-05-21' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
                '2026-05-22' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
                '2026-05-25' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
                '2026-05-26' => ['open' => 500, 'high' => 505, 'low' => 495, 'close' => 500],
            ]),
            ['2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26']
        );

        $this->assertTrue($metrics['evaluated_trades'][0]['metrics_ready']);
        $this->assertSame('WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED', $metrics['evaluated_trades'][0]['exit_reason_code']);
        $this->assertSame(500.0, $metrics['evaluated_trades'][0]['executed_price']);
    }

    public function test_metrics_output_is_deterministic_for_same_inputs(): void
    {
        $service = new WatchlistBacktestMetricsService();
        $payload = $this->backtestPayload();

        $first = $service->buildMetrics($payload);
        $second = $service->buildMetrics($payload);

        $this->assertSame($first, $second);
    }

    private function backtestPayload(array $paramsetOverrides = [], array $trades = null): array
    {
        $paramset = array_replace_recursive([
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
                'entry_model' => 'D_PLUS_1_OPEN_DOCUMENTED',
                'exit_model' => 'WEEKLY_SWING_MAX_5_TRADING_DAYS_DOCUMENTED',
                'pricing_model' => 'FOUNDATION_ONLY_PRICE_SERIES_NOT_CONSUMED',
                'fee_model' => 'IDR_FIXED',
                'fee_buy_idr' => 2500,
                'fee_sell_idr' => 2500,
                'notional_idr' => 10000000,
                'lot_size' => 100,
                'slippage_entry_pct' => 0,
                'slippage_exit_pct' => 0,
                'tradable_bar_rule' => 'POSITIVE_VOLUME_REQUIRED',
                'min_tradable_volume' => 1,
            ],
        ], $paramsetOverrides);

        $trades = $trades ?? [$this->trade('2026-05-19', 1, 'AAA')];

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_FOUNDATION_READY',
            'source_contract' => [
                'consumer' => 'WatchlistBacktestStrategyService',
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
            ],
            'paramset_snapshot' => $paramset,
            'replay_window' => [
                'from_trade_date' => '2026-05-19',
                'to_trade_date' => '2026-05-19',
                'trade_dates' => ['2026-05-19'],
            ],
            'items' => [],
            'trades' => $trades,
            'evaluations' => array_map(function (array $trade): array {
                return [
                    'trade_date' => $trade['trade_date'],
                    'ticker_id' => $trade['ticker_id'],
                    'ticker' => $trade['ticker'],
                    'metrics_ready' => false,
                    'reason_codes' => ['WS_BT_EVAL_METRICS_MISSING'],
                ];
            }, $trades),
            'summary' => [
                'days_requested' => 1,
                'days_evaluated' => 1,
                'picks_count' => count($trades),
                'evaluations_count' => count($trades),
                'empty_recommendation_days' => 0,
                'reason_codes' => ['WS_BT_EVAL_METRICS_MISSING'],
            ],
            'diagnostics' => [],
            'artifact_manifest' => [],
        ];
    }

    private function trade(string $tradeDate, int $tickerId, string $ticker): array
    {
        return [
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'ticker' => $ticker,
            'bucket_code' => 'TOP_PICKS',
            'plan_rank' => $tickerId,
            'recommendation_rank' => $tickerId,
            'trade_state' => 'EVALUATION_CANDIDATE',
            'reason_codes' => ['WS_REC_SELECTED'],
        ];
    }

    private function priceSeries(string $ticker, array $barsByDate): array
    {
        $published = [];
        foreach ($barsByDate as $date => $bar) {
            $published[$ticker][$date] = array_merge([
                'published' => true,
                'volume' => 100000,
            ], $bar);
        }

        return $published;
    }
}
