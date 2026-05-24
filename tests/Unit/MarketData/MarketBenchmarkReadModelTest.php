<?php

use App\Application\MarketData\Services\MarketBenchmarkReadService;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

class MarketBenchmarkReadModelTest extends TestCase
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

    public function test_benchmark_read_model_returns_ihsg_context_without_equity_ticker_boundary(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2);
        $this->seedBenchmark('2026-05-19', [
            'roc_20' => '1.5000000000',
            'ma20' => '7100.0000',
            'ma50' => '7000.0000',
            'is_valid' => 1,
            'invalid_reason_code' => null,
        ]);

        $result = (new MarketBenchmarkReadService())->getBenchmarkMarketDataForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertSame(2, $result['publication_id']);
        $this->assertSame('IHSG', $result['benchmark']['benchmark_code']);
        $this->assertSame('^JKSE', $result['benchmark']['provider_symbol']);
        $this->assertSame(7250.0, $result['benchmark']['close_price']);
        $this->assertSame(1.5, $result['benchmark']['roc_20']);
        $this->assertTrue($result['benchmark']['is_valid']);
        $this->assertSame(0, $this->db()->table('tickers')->where('ticker_code', 'IHSG')->count());
    }

    public function test_benchmark_read_model_preserves_insufficient_history_reason(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2);
        $this->seedBenchmark('2026-05-19');

        $result = (new MarketBenchmarkReadService())->getBenchmarkMarketDataForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertFalse($result['benchmark']['is_valid']);
        $this->assertNull($result['benchmark']['roc_20']);
        $this->assertNull($result['benchmark']['ma20']);
        $this->assertNull($result['benchmark']['ma50']);
        $this->assertSame('IND_INSUFFICIENT_HISTORY', $result['benchmark']['invalid_reason_code']);
    }

    public function test_benchmark_read_model_blocks_when_market_data_publication_is_not_ready(): void
    {
        $this->seedBenchmark('2026-05-19');

        $result = (new MarketBenchmarkReadService())->getBenchmarkMarketDataForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('NO_READABLE_PUBLICATION', $result['reason_code']);
        $this->assertNull($result['benchmark']);
    }
}
