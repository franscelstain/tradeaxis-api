<?php

use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class TickerMasterRepositoryTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        config()->set('market_data.tickers.table', 'tickers');
        config()->set('market_data.tickers.id_column', 'ticker_id');
        config()->set('market_data.tickers.code_column', 'ticker_code');
        config()->set('market_data.tickers.active_column', 'is_active');
        config()->set('market_data.tickers.active_value', 1);
        config()->set('market_data.tickers.listed_date_column', 'listed_date');
        config()->set('market_data.tickers.delisted_date_column', 'delisted_date');
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_universe_filter_uses_numeric_active_value_and_rejects_stale_yes_string(): void
    {
        DB::table('tickers')->insert([
            ['ticker_id' => 1, 'ticker_code' => 'BBCA', 'company_name' => 'BBCA', 'is_active' => 1, 'listed_date' => '2020-01-01', 'delisted_date' => null],
            ['ticker_id' => 2, 'ticker_code' => 'BMRI', 'company_name' => 'BMRI', 'is_active' => 0, 'listed_date' => '2020-01-01', 'delisted_date' => null],
            ['ticker_id' => 3, 'ticker_code' => 'TLKM', 'company_name' => 'TLKM', 'is_active' => 'Yes', 'listed_date' => '2020-01-01', 'delisted_date' => null],
            ['ticker_id' => 4, 'ticker_code' => 'ASII', 'company_name' => 'ASII', 'is_active' => 1, 'listed_date' => '2026-06-01', 'delisted_date' => null],
            ['ticker_id' => 5, 'ticker_code' => 'UNVR', 'company_name' => 'UNVR', 'is_active' => 1, 'listed_date' => '2020-01-01', 'delisted_date' => '2026-01-01'],
        ]);

        $universe = (new TickerMasterRepository())->getUniverseForTradeDate('2026-05-17');

        $this->assertSame([
            ['ticker_id' => 1, 'ticker_code' => 'BBCA'],
        ], $universe);
    }
}
