<?php

use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class SectorClassificationRepositoryTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_resolves_effective_sector_code_for_trade_date(): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'company_name' => 'Bank Central Asia',
            'is_active' => 1,
        ]);

        DB::table('ticker_sector_memberships')->insert([
            [
                'ticker_id' => 1,
                'sector_code' => 'G',
                'classification_system' => 'IDX-IC',
                'effective_from' => '2021-01-25',
                'effective_to' => '2026-05-31',
                'source_name' => 'fixture',
            ],
            [
                'ticker_id' => 1,
                'sector_code' => 'I',
                'classification_system' => 'IDX-IC',
                'effective_from' => '2026-06-01',
                'effective_to' => null,
                'source_name' => 'fixture',
            ],
        ]);

        $repository = new SectorClassificationRepository();

        $this->assertSame([1 => 'G'], $repository->resolveSectorCodesForTickerIds([1], '2026-05-19'));
        $this->assertSame([1 => 'I'], $repository->resolveSectorCodesForTickerIds([1], '2026-06-03'));
        $this->assertSame([
            1 => [
                'sector_code' => 'G',
                'sector_index_code' => 'IDXFINANCE',
            ],
        ], $repository->resolveSectorContextForTickerIds([1], '2026-05-19'));
        $this->assertSame([], $repository->resolveSectorCodesForTickerIds([1], '2021-01-24'));
    }

    public function test_upsert_membership_replaces_same_effective_start_without_duplicate_rows(): void
    {
        $repository = new SectorClassificationRepository();

        $repository->upsertMembership(1, 'G', '2021-01-25', null, 'fixture', 'first');
        $repository->upsertMembership(1, 'I', '2021-01-25', null, 'fixture', 'replacement');

        $rows = DB::table('ticker_sector_memberships')->where('ticker_id', 1)->get();

        $this->assertCount(1, $rows);
        $this->assertSame('I', $rows[0]->sector_code);
        $this->assertSame('replacement', $rows[0]->source_ref);
    }
}
