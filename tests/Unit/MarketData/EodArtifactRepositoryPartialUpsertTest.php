<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class EodArtifactRepositoryPartialUpsertTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootMarketDataSqlite();
        Carbon::setTestNow('2026-05-27 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();

        parent::tearDown();
    }

    public function test_partial_recovered_upsert_preserves_existing_tickers_on_same_trade_date(): void
    {
        DB::table('eod_bars')->insert([
            $this->bar(1, 'BBCA'),
            $this->bar(2, 'BBRI'),
            $this->bar(3, 'TLKM'),
        ]);

        $summary = (new EodArtifactRepository())->upsertBarsPartial('2026-05-01', 77, 20, [
            [
                'trade_date' => '2026-05-01',
                'ticker_id' => 4,
                'open' => 400,
                'high' => 410,
                'low' => 390,
                'close' => 405,
                'volume' => 4000,
                'adj_close' => 405,
                'source' => 'YAHOO_FINANCE',
                'publication_id' => 77,
                'run_id' => 20,
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
        ]);

        $this->assertSame(4, DB::table('eod_bars')->where('trade_date', '2026-05-01')->count());
        $this->assertSame(1, DB::table('eod_bars')->where('ticker_id', 1)->count());
        $this->assertSame(1, DB::table('eod_bars')->where('ticker_id', 2)->count());
        $this->assertSame(1, DB::table('eod_bars')->where('ticker_id', 3)->count());
        $this->assertSame(1, DB::table('eod_bars')->where('ticker_id', 4)->count());
        $this->assertSame(1, $summary['inserted_bar_count']);
        $this->assertSame(1, $summary['changed_bar_count']);
        $this->assertSame(0, $summary['removed_bar_count']);
    }

    public function test_partial_recovered_upsert_unchanged_row_is_idempotent_noop(): void
    {
        $existing = $this->bar(1, 'BBCA');
        DB::table('eod_bars')->insert([$existing]);

        $summary = (new EodArtifactRepository())->upsertBarsPartial('2026-05-01', 77, 20, [$existing]);

        $this->assertSame(1, DB::table('eod_bars')->where('trade_date', '2026-05-01')->count());
        $this->assertSame(0, $summary['changed_bar_count']);
        $this->assertSame(0, $summary['inserted_bar_count']);
        $this->assertSame(0, $summary['updated_bar_count']);
        $this->assertSame(1, $summary['unchanged_bar_count']);
        $this->assertSame([], $summary['changed_trade_dates']);
    }

    private function bar(int $tickerId, string $source): array
    {
        return [
            'trade_date' => '2026-05-01',
            'ticker_id' => $tickerId,
            'open' => 100 * $tickerId,
            'high' => 110 * $tickerId,
            'low' => 90 * $tickerId,
            'close' => 105 * $tickerId,
            'volume' => 1000 * $tickerId,
            'adj_close' => 105 * $tickerId,
            'source' => $source,
            'run_id' => 10,
            'publication_id' => 77,
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
