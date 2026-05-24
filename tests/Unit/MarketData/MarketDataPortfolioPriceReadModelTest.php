<?php

use App\Application\MarketData\Services\MarketDataPortfolioPriceService;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataPortfolioPriceReadModelTest extends TestCase
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

    public function test_portfolio_price_read_model_returns_official_prices_from_current_readable_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedTicker(2, 'BBRI');
        $this->seedTradingDay('2026-05-18');
        $this->seedTradingDay('2026-05-19');

        $this->seedReadablePublication('2026-05-18', 2, 1);
        $this->seedBar('2026-05-18', 1, 2, 1, 8900, 111111, 8900);
        $this->seedBar('2026-05-18', 2, 2, 1, 4300, 222222, 4300);

        $this->seedReadablePublication('2026-05-19', 3, 2);
        $this->seedBar('2026-05-19', 1, 3, 2, 9000, 123456, 9000);
        $this->seedBar('2026-05-19', 2, 3, 2, 4400, 234567, 4400);

        $result = (new MarketDataPortfolioPriceService())->getOfficialPricesForPortfolio('2026-05-19', ['bbca', 'BBRI']);

        $this->assertTrue($result['is_ready']);
        $this->assertSame(2, $result['publication_id']);
        $this->assertSame([], $result['missing_tickers']);
        $this->assertCount(2, $result['prices']);

        $bbca = $result['prices'][0];
        $this->assertSame('BBCA', $bbca['ticker_code']);
        $this->assertSame(9000.0, $bbca['close_price']);
        $this->assertSame(9000.0, $bbca['adjusted_close']);
        $this->assertSame(8900.0, $bbca['previous_close_price']);
        $this->assertSame(100.0, $bbca['change_amount']);
        $this->assertEqualsWithDelta(1.1235955056, $bbca['change_pct'], 0.0000001);
    }

    public function test_portfolio_price_read_model_returns_missing_tickers(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedReadablePublication('2026-05-19', 3, 2);
        $this->seedBar('2026-05-19', 1, 3, 2, 9000, 123456, 9000);

        $result = (new MarketDataPortfolioPriceService())->getOfficialPricesForPortfolio('2026-05-19', ['BBCA', 'BBRI']);

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['BBRI'], $result['missing_tickers']);
        $this->assertCount(1, $result['prices']);
    }

    public function test_portfolio_price_read_model_does_not_fallback_to_latest_other_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedReadablePublication('2026-05-18', 2, 1);
        $this->seedBar('2026-05-18', 1, 2, 1, 8900, 111111, 8900);

        $result = (new MarketDataPortfolioPriceService())->getOfficialPricesForPortfolio('2026-05-19', ['BBCA']);

        $this->assertFalse($result['is_ready']);
        $this->assertSame('NO_READABLE_PUBLICATION', $result['reason_code']);
        $this->assertSame([], $result['prices']);
        $this->assertSame(['BBCA'], $result['missing_tickers']);
    }

    public function test_portfolio_price_read_model_returns_null_previous_close_with_reason_when_unavailable(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedReadablePublication('2026-05-19', 3, 2);
        $this->seedBar('2026-05-19', 1, 3, 2, 9000, 123456, 9000);

        $result = (new MarketDataPortfolioPriceService())->getOfficialPricesForPortfolio('2026-05-19', ['BBCA']);

        $this->assertNull($result['prices'][0]['previous_close_price']);
        $this->assertSame('NO_READABLE_PUBLICATION', $result['prices'][0]['previous_close_reason_code']);
        $this->assertNull($result['prices'][0]['change_amount']);
        $this->assertNull($result['prices'][0]['change_pct']);
    }
}
