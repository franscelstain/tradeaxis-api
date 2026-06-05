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

    public function test_history_replacement_mutation_summary_compares_against_superseded_current_publication(): void
    {
        DB::table('eod_publications')->insert([
            [
                'publication_id' => 10,
                'trade_date' => '2026-05-01',
                'run_id' => 10,
                'publication_version' => 1,
                'is_current' => 1,
                'supersedes_publication_id' => null,
                'previous_publication_id' => null,
                'replaced_publication_id' => null,
                'seal_state' => 'SEALED',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'publication_id' => 77,
                'trade_date' => '2026-05-01',
                'run_id' => 20,
                'publication_version' => 2,
                'is_current' => 0,
                'supersedes_publication_id' => 10,
                'previous_publication_id' => 10,
                'replaced_publication_id' => 10,
                'seal_state' => 'UNSEALED',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ],
        ]);

        DB::table('eod_bars')->insert([
            [
                'trade_date' => '2026-05-01',
                'ticker_id' => 1,
                'open' => 100,
                'high' => 110,
                'low' => 90,
                'close' => 105,
                'volume' => 1000,
                'adj_close' => 105,
                'source' => 'API_FREE',
                'run_id' => 10,
                'publication_id' => 10,
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
        ]);

        $summary = (new EodArtifactRepository())->replaceBars('2026-05-01', 77, 20, [
            [
                'trade_date' => '2026-05-01',
                'ticker_id' => 1,
                'open' => 100,
                'high' => 110,
                'low' => 90,
                'close' => 105,
                'volume' => 1000,
                'adj_close' => 105,
                'source' => 'API_FREE',
                'publication_id' => 77,
                'run_id' => 20,
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'trade_date' => '2026-05-01',
                'ticker_id' => 2,
                'open' => 200,
                'high' => 210,
                'low' => 190,
                'close' => 205,
                'volume' => 2000,
                'adj_close' => 205,
                'source' => 'YAHOO_FINANCE',
                'publication_id' => 77,
                'run_id' => 20,
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
        ], [], true);

        $this->assertSame(1, $summary['changed_bar_count']);
        $this->assertSame(1, $summary['inserted_bar_count']);
        $this->assertSame(0, $summary['updated_bar_count']);
        $this->assertSame(1, $summary['unchanged_bar_count']);
        $this->assertSame([2], $summary['changed_ticker_ids']);
        $this->assertSame(2, DB::table('eod_bars_history')->where('publication_id', 77)->count());
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
