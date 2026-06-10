<?php

use App\Application\Watchlist\Services\WatchlistBacktestOosSplitService;

class WatchlistBacktestOosSplitServiceTest extends TestCase
{
    public function test_split_uses_floor_seventy_percent_prefix_and_remainder_suffix(): void
    {
        $dates = [
            '2026-01-01', '2026-01-02', '2026-01-03', '2026-01-04', '2026-01-05',
            '2026-01-06', '2026-01-07', '2026-01-08', '2026-01-09', '2026-01-10',
        ];

        $result = (new WatchlistBacktestOosSplitService())->split(array_reverse($dates));

        $this->assertTrue($result['is_ready']);
        $this->assertSame('FLOOR_70_PERCENT_IS_REMAINDER_OOS', $result['split_rule']);
        $this->assertSame(7, $result['split_index']);
        $this->assertSame(array_slice($dates, 0, 7), $result['is_dates']);
        $this->assertSame(array_slice($dates, 7), $result['oos_dates']);
        $this->assertTrue($result['is_prefix']);
        $this->assertTrue($result['oos_suffix']);
        $this->assertTrue($result['no_overlap']);
        $this->assertTrue($result['no_hidden_gap']);
    }

    public function test_split_is_deterministic_for_odd_date_count(): void
    {
        $dates = ['2026-01-01', '2026-01-02', '2026-01-03', '2026-01-04', '2026-01-05'];
        $service = new WatchlistBacktestOosSplitService();

        $first = $service->split($dates);
        $second = $service->split(['2026-01-05', '2026-01-03', '2026-01-01', '2026-01-04', '2026-01-02']);

        $this->assertSame(3, $first['is_trading_date_count']);
        $this->assertSame(2, $first['oos_trading_date_count']);
        $this->assertSame($first['ordered_trading_date_hash'], $second['ordered_trading_date_hash']);
        $this->assertSame($first['is_trading_date_hash'], $second['is_trading_date_hash']);
        $this->assertSame($first['oos_trading_date_hash'], $second['oos_trading_date_hash']);
    }

    public function test_split_fails_closed_when_either_side_would_be_empty(): void
    {
        $result = (new WatchlistBacktestOosSplitService())->split(['2026-01-01']);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WS_BT_OOS_WINDOW_INSUFFICIENT', $result['reason_code']);
        $this->assertTrue($result['diagnostics'][0]['fatal']);
    }
}
