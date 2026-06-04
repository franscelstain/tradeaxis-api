<?php

use App\Application\MarketData\Services\MarketDataWatchlistReadService;
use Illuminate\Support\Facades\DB;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataWatchlistReadModelTest extends TestCase
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

    public function test_watchlist_read_model_returns_indicator_rows_from_current_readable_publication(): void
    {
        $this->seedTicker(1, 'BBCA', 'Bank Central Asia');
        $this->seedTicker(2, 'BBRI', 'Bank Rakyat Indonesia');
        $this->seedReadablePublication('2026-05-19', 3, 2);
        $this->seedBar('2026-05-19', 1, 3, 2, 9000, 123456, 9000);
        $this->seedIndicator('2026-05-19', 1, 3, 2, [
            'corporate_action_flag' => 1,
            'corporate_action_types' => 'DIVIDEND',
            'trading_status_code' => 'UMA',
            'is_suspended' => 0,
            'is_uma' => 1,
            'event_risk_flag' => 1,
            'event_risk_reasons' => 'CORPORATE_ACTION:DIVIDEND,UMA',
        ]);
        $this->seedEligibility('2026-05-19', 1, 3, 2, 1);

        $this->seedBar('2026-05-19', 2, 99, 999, 4000);
        $this->seedIndicator('2026-05-19', 2, 99, 999, ['publication_id' => 999, 'run_id' => 99]);
        $this->seedEligibility('2026-05-19', 2, 99, 999, 1);

        $result = (new MarketDataWatchlistReadService())->getWatchlistMarketDataForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertSame(2, $result['publication_id']);
        $this->assertSame(3, $result['run_id']);
        $this->assertSame('RESOLVED_READABLE_CURRENT', $result['pointer_resolve_status']);
        $this->assertCount(1, $result['rows']);

        $row = $result['rows'][0];
        $this->assertSame('BBCA', $row['ticker_code']);
        $this->assertSame('Bank Central Asia', $row['ticker_name']);
        $this->assertSame(9000.0, $row['close_price']);
        $this->assertSame(123456, $row['volume']);
        $this->assertSame(123456789000.0, $row['dv20idr']);
        $this->assertSame('G', $row['sector_code']);
        $this->assertSame('Financials', $row['sector_name']);
        $this->assertSame('IDXFINANCE', $row['sector_index_code']);
        $this->assertSame(0.018, $row['roc_5']);
        $this->assertSame(0.031, $row['roc_10']);
        $this->assertSame(5.2, $row['roc_20']);
        $this->assertSame(8200.0, $row['ll20']);
        $this->assertSame(9.756097561, $row['close_to_ll20_pct']);
        $this->assertSame(10.9756097561, $row['range_20_pct']);
        $this->assertSame(88.8888888889, $row['range_position_20_pct']);
        $this->assertSame(8750.0, $row['ma20']);
        $this->assertSame(8600.0, $row['ma50']);
        $this->assertSame(3.4, $row['rs_20_vs_ihsg']);
        $this->assertSame(2.7, $row['sector_roc20']);
        $this->assertSame(2.5, $row['rs_20_vs_sector']);
        $this->assertSame(-0.9, $row['sector_rs_20_vs_ihsg']);
        $this->assertSame(1, $row['corporate_action_flag']);
        $this->assertSame('DIVIDEND', $row['corporate_action_types']);
        $this->assertSame('UMA', $row['trading_status_code']);
        $this->assertSame(0, $row['is_suspended']);
        $this->assertSame(1, $row['is_uma']);
        $this->assertSame(1, $row['event_risk_flag']);
        $this->assertSame('CORPORATE_ACTION:DIVIDEND,UMA', $row['event_risk_reasons']);
        $this->assertSame('v1', $row['indicator_set_version']);
        $this->assertSame('API_FREE', $row['source_name']);
    }

    public function test_watchlist_read_model_blocks_when_no_readable_publication_exists(): void
    {
        $this->seedTicker(1, 'BBCA', 'Bank Central Asia');
        $this->seedReadablePublication('2026-05-18', 2, 1);
        $this->seedBar('2026-05-18', 1, 2, 1, 8900);
        $this->seedIndicator('2026-05-18', 1, 2, 1);
        $this->seedEligibility('2026-05-18', 1, 2, 1, 1);

        $result = (new MarketDataWatchlistReadService())->getWatchlistMarketDataForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('NO_READABLE_PUBLICATION', $result['reason_code']);
        $this->assertSame('NOT_RESOLVED_READABLE_CURRENT', $result['pointer_resolve_status']);
        $this->assertSame([], $result['rows']);
    }

    public function test_watchlist_read_model_does_not_leak_non_current_publication_rows(): void
    {
        $this->seedTicker(1, 'BBCA', 'Bank Central Asia');
        $this->seedReadablePublication('2026-05-19', 3, 2);
        $this->seedBar('2026-05-19', 1, 3, 2, 9000);
        $this->seedIndicator('2026-05-19', 1, 3, 2);
        $this->seedEligibility('2026-05-19', 1, 3, 2, 1);

        DB::table('eod_current_publication_pointer')->where('trade_date', '2026-05-19')->delete();

        $result = (new MarketDataWatchlistReadService())->getWatchlistMarketDataForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame([], $result['rows']);
    }
}
