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
            $published[$ticker][$date] = array_merge(['published' => true], $bar);
        }

        return $published;
    }
}
