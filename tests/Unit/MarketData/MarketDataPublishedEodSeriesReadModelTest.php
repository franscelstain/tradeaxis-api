<?php

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use Illuminate\Support\Facades\DB;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataPublishedEodSeriesReadModelTest extends TestCase
{
    use UsesMarketDataSqlite;
    use SeedsConsumerReadModelFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->configureConsumerReadModelFixture();
        $this->seedTicker(1, 'BBCA', 'Bank Central Asia');
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_published_series_can_resolve_explicit_range_without_requiring_future_calendar_day(): void
    {
        DB::table('market_calendar')->insert([
            'cal_date' => '2026-05-19',
            'is_trading_day' => 1,
            'source' => 'idx_official',
            'created_at' => '2026-05-19 00:00:00',
        ]);
        $this->seedReadablePublication('2026-05-19', 11, 21, 3);
        $this->seedBar('2026-05-19', 1, 11, 21, 9000, 123456, 8995);

        $result = (new MarketDataPublishedEodSeriesReadService())->readPublishedSeries(
            '2026-05-19',
            '2026-05-19',
            ['BBCA']
        );

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['2026-05-19'], $result['requested_trade_dates']);
        $this->assertSame(9000.0, $result['series_by_ticker']['BBCA']['2026-05-19']['close']);
    }

    public function test_published_series_returns_exact_date_ohlcv_and_publication_lineage(): void
    {
        $this->seedReadablePublication('2026-05-19', 11, 21, 3);
        $this->seedBar('2026-05-19', 1, 11, 21, 9000, 123456, 8995);

        $result = (new MarketDataPublishedEodSeriesReadService())->readPublishedSeries(
            '2026-05-19',
            '2026-05-19',
            ['bbca'],
            ['2026-05-19']
        );

        $this->assertTrue($result['is_ready']);
        $bar = $result['series_by_ticker']['BBCA']['2026-05-19'];
        $this->assertSame(8990.0, $bar['open']);
        $this->assertSame(9025.0, $bar['high']);
        $this->assertSame(8975.0, $bar['low']);
        $this->assertSame(9000.0, $bar['close']);
        $this->assertSame(21, $bar['publication_id']);
        $this->assertSame(3, $bar['publication_version']);
        $this->assertSame(11, $bar['run_id']);
        $this->assertTrue($bar['published']);
        $this->assertTrue($bar['readable']);
        $this->assertSame(1, $result['price_series_manifest']['resolved_price_date_count']);
        $this->assertSame([], $result['price_series_manifest']['missing_publication_dates']);
    }

    public function test_published_series_rejects_exact_date_outside_explicit_range(): void
    {
        $result = (new MarketDataPublishedEodSeriesReadService())->readPublishedSeries(
            '2026-05-19',
            '2026-05-19',
            ['BBCA'],
            ['2026-05-20']
        );

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $result['reason_code']);
        $this->assertTrue($result['diagnostics'][0]['fatal']);
    }

    public function test_published_series_fails_closed_on_missing_exact_date_publication_without_latest_fallback(): void
    {
        $this->seedReadablePublication('2026-05-18', 10, 20, 1);
        $this->seedBar('2026-05-18', 1, 10, 20, 8900);

        $result = (new MarketDataPublishedEodSeriesReadService())->readPublishedSeries(
            '2026-05-19',
            '2026-05-19',
            ['BBCA'],
            ['2026-05-19']
        );

        $this->assertFalse($result['is_ready']);
        $this->assertSame('WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', $result['reason_code']);
        $this->assertSame(['2026-05-19'], $result['price_series_manifest']['missing_publication_dates']);
        $this->assertSame([], $result['series_by_ticker']);
    }

    public function test_missing_ticker_row_is_nonfatal_and_reason_coded_for_evaluation_skip(): void
    {
        $this->seedReadablePublication('2026-05-19', 11, 21, 3);

        $result = (new MarketDataPublishedEodSeriesReadService())->readPublishedSeries(
            '2026-05-19',
            '2026-05-19',
            ['BBCA'],
            ['2026-05-19']
        );

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['2026-05-19'], $result['price_series_manifest']['missing_price_dates']);
        $this->assertSame('WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', $result['diagnostics'][0]['reason_code']);
        $this->assertFalse($result['diagnostics'][0]['fatal']);
    }

    public function test_published_series_does_not_leak_rows_from_non_current_publication(): void
    {
        $this->seedReadablePublication('2026-05-19', 99, 999, 1, [
            'is_current' => 0,
            'is_current_publication' => 0,
            'skip_pointer' => true,
        ]);
        DB::table('eod_bars_history')->insert([
            'publication_id' => 999,
            'trade_date' => '2026-05-19',
            'ticker_id' => 1,
            'open' => 9890,
            'high' => 9925,
            'low' => 9875,
            'close' => 9900,
            'volume' => 123456,
            'adj_close' => 9900,
            'source' => 'api',
            'run_id' => 99,
            'created_at' => '2026-05-19 17:10:00',
        ]);

        $this->seedReadablePublication('2026-05-19', 11, 21, 2);
        $this->seedBar('2026-05-19', 1, 11, 21, 9000, 123456, 9000);

        $result = (new MarketDataPublishedEodSeriesReadService())->readPublishedSeries(
            '2026-05-19',
            '2026-05-19',
            ['BBCA'],
            ['2026-05-19']
        );

        $this->assertTrue($result['is_ready']);
        $this->assertSame(9000.0, $result['series_by_ticker']['BBCA']['2026-05-19']['close']);
        $this->assertSame(1, $result['price_series_manifest']['resolved_price_row_count']);
    }
}
