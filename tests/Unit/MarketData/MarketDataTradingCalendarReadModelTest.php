<?php

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use Illuminate\Support\Facades\DB;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataTradingCalendarReadModelTest extends TestCase
{
    use UsesMarketDataSqlite;
    use SeedsConsumerReadModelFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->configureConsumerReadModelFixture();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_calendar_range_read_does_not_require_future_horizon(): void
    {
        $this->seedTradingDayWithSource('2026-05-19');

        $result = (new MarketDataTradingCalendarReadService())->resolveTradingDates('2026-05-19', '2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['2026-05-19'], $result['trade_dates']);
        $this->assertSame(0, $result['forward_horizon_days']);
        $this->assertSame(0, $result['coverage']['forward_date_count']);
    }

    public function test_calendar_read_surface_returns_explicit_replay_dates_and_complete_forward_horizon(): void
    {
        foreach (['2026-05-18', '2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26'] as $date) {
            $this->seedTradingDayWithSource($date);
        }

        $result = (new MarketDataTradingCalendarReadService())->resolveReplayWindow('2026-05-18', '2026-05-19', 5);

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['2026-05-18', '2026-05-19'], $result['trade_dates']);
        $this->assertSame('market_calendar', $result['calendar_source']);
        $this->assertTrue($result['coverage']['horizon_complete']);
        $this->assertSame(5, $result['coverage']['forward_date_count']);
        $this->assertSame(40, strlen($result['calendar_hash']));
    }

    public function test_calendar_read_surface_fails_closed_when_forward_horizon_is_incomplete(): void
    {
        foreach (['2026-05-18', '2026-05-19', '2026-05-20'] as $date) {
            $this->seedTradingDayWithSource($date);
        }

        $result = (new MarketDataTradingCalendarReadService())->resolveReplayWindow('2026-05-18', '2026-05-19', 5);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', $result['reason_code']);
        $this->assertTrue($result['diagnostics'][0]['fatal']);
    }

    public function test_calendar_read_surface_does_not_infer_weekdays_or_include_holidays(): void
    {
        $this->seedTradingDayWithSource('2026-05-18');
        DB::table('market_calendar')->insert([
            'cal_date' => '2026-05-19',
            'is_trading_day' => 0,
            'holiday_name' => 'Exchange Holiday',
            'source' => 'idx_official',
            'created_at' => '2026-05-19 00:00:00',
        ]);
        foreach (['2026-05-20', '2026-05-21', '2026-05-22', '2026-05-25', '2026-05-26'] as $date) {
            $this->seedTradingDayWithSource($date);
        }

        $result = (new MarketDataTradingCalendarReadService())->resolveReplayWindow('2026-05-18', '2026-05-19', 5);

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['2026-05-18'], $result['trade_dates']);
        $this->assertNotContains('2026-05-19', $result['calendar_dates']);
    }

    private function seedTradingDayWithSource(string $tradeDate): void
    {
        DB::table('market_calendar')->insert([
            'cal_date' => $tradeDate,
            'is_trading_day' => 1,
            'source' => 'idx_official',
            'created_at' => $tradeDate.' 00:00:00',
        ]);
    }
}
