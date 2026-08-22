<?php

use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W05 / MD-B05 — board and market segment are temporal facts, not current columns.
 *
 * Owner contracts:
 *   docs/market_data/authority/strategy/book/Tickers_and_Identity_Dependency_Contract_LOCKED.md
 *   docs/market_data/authority/strategy/book/Symbol_Lifecycle_and_Mapping_Contract.md
 *
 * Three predicates converge here. The identity contract requires point-in-time resolution to return
 * "market segment and board valid on T", and requires board or market-segment movement to be
 * effective-dated and to leave the prior listing context unrewritten. The mapping contract requires
 * Regular-Market observations to retain the listing/board context valid on their trade date.
 *
 * `md_listings.board_code` and `md_listings.market_segment` are single mutable columns. Recording a
 * board move meant overwriting one of them, which silently changed the answer for every historical
 * date, and the universe query filtered on the current segment — so a listing that was Regular on T
 * and moved afterwards fell out of T's universe. That is the same current-state-resolves-history
 * defect the `is_active` boundary forbids, wearing a different column name.
 *
 * `md_listing_boards` carries the intervals. The columns on `md_listings` remain, as the cached
 * current-state projection the contract permits, and nothing resolves history from them.
 */
class ListingBoardAndSegmentTemporalityTest extends TestCase
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

    public function test_the_temporal_board_record_exists_with_an_interval_and_provenance(): void
    {
        $this->assertTrue(Schema::hasTable('md_listing_boards'), 'board movement needs somewhere to live');

        foreach ([
            'listing_id', 'market_segment', 'board_code', 'effective_from', 'effective_to',
            'recorded_at', 'retracted_at', 'source_ref', 'change_reason',
        ] as $column) {
            $this->assertTrue(
                Schema::hasColumn('md_listing_boards', $column),
                'md_listing_boards is missing '.$column
            );
        }
    }

    public function test_a_board_move_resolves_the_board_effective_on_the_trade_date(): void
    {
        $listingId = $this->seedListing(['n' => 1, 'board_code' => 'DEVELOPMENT']);
        $this->seedSymbol($listingId, 'MOVED', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', 'REGULAR', '2023-01-02 00:00:00', '2025-02-03 00:00:00');
        $this->seedBoard($listingId, 'DEVELOPMENT', 'REGULAR', '2025-02-03 00:00:00', null);

        $this->assertSame('MAIN', $this->boardOn('2025-01-30'));
        $this->assertSame('DEVELOPMENT', $this->boardOn('2025-02-04'));
    }

    /**
     * The prior interval must survive the move. A resolver that returned the right answer by editing
     * the earlier row would pass the test above and still destroy the history it read.
     */
    public function test_the_prior_board_interval_is_closed_and_never_rewritten(): void
    {
        $listingId = $this->seedListing(['n' => 2]);
        $this->seedSymbol($listingId, 'KEEP', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', 'REGULAR', '2023-01-02 00:00:00', '2025-02-03 00:00:00');
        $this->seedBoard($listingId, 'DEVELOPMENT', 'REGULAR', '2025-02-03 00:00:00', null);

        $this->boardOn('2025-02-04');

        $rows = DB::table('md_listing_boards')->where('listing_id', $listingId)->orderBy('effective_from')->get();

        $this->assertCount(2, $rows, 'a move appends an interval; it does not replace one');
        $this->assertSame('MAIN', (string) $rows[0]->board_code);
        $this->assertSame('2023-01-02 00:00:00', (string) $rows[0]->effective_from);
        $this->assertSame('2025-02-03 00:00:00', (string) $rows[0]->effective_to);
    }

    /**
     * The survivorship case for segment. A listing that was Regular on `T` belongs in `T`'s universe
     * even if it left the segment afterwards; filtering on the current segment silently deletes it
     * from history.
     */
    public function test_a_segment_move_does_not_remove_the_listing_from_an_earlier_universe(): void
    {
        $listingId = $this->seedListing(['n' => 3, 'market_segment' => 'ACCELERATION']);
        $this->seedSymbol($listingId, 'WASREG', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', 'REGULAR', '2023-01-02 00:00:00', '2026-01-05 00:00:00');
        $this->seedBoard($listingId, 'ACCEL', 'ACCELERATION', '2026-01-05 00:00:00', null);

        $this->assertContains('WASREG', $this->universeCodes('2025-12-30'), 'the listing was Regular on that date');
        $this->assertNotContains('WASREG', $this->universeCodes('2026-01-06'), 'and is out of the Regular universe after the move');
    }

    /**
     * Absence of a temporal record is not permission to use the current column. `is_active` boundary
     * reasoning applies to every current field: resolution must hold rather than answer from today.
     */
    public function test_a_listing_without_a_board_interval_for_the_date_is_not_resolved_from_the_current_column(): void
    {
        $listingId = $this->seedListing(['n' => 4, 'board_code' => 'MAIN', 'market_segment' => 'REGULAR']);
        $this->seedSymbol($listingId, 'NOBRD', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', 'REGULAR', '2025-06-02 00:00:00', null);

        $this->assertNotContains('NOBRD', $this->universeCodes('2025-05-30'), 'no interval covers that date');
        $this->assertContains('NOBRD', $this->universeCodes('2025-06-03'));
    }

    /**
     * Two intervals covering one date is a conflicting temporal record, and the contract makes that
     * a blocking condition rather than a preference between rows.
     */
    public function test_overlapping_board_intervals_fail_closed(): void
    {
        $listingId = $this->seedListing(['n' => 5]);
        $this->seedSymbol($listingId, 'DOUBLE', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', 'REGULAR', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'DEVELOPMENT', 'REGULAR', '2024-01-02 00:00:00', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LISTING_BOARD_CONTEXT_AMBIGUOUS');

        (new TemporalIdentityRepository())->readProjectedUniverseAsOf('2025-01-02');
    }

    /**
     * A retracted interval is not a resolvable one, the same way a retracted symbol is not.
     */
    public function test_a_retracted_board_interval_does_not_resolve(): void
    {
        $listingId = $this->seedListing(['n' => 6]);
        $this->seedSymbol($listingId, 'RETRD', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', 'REGULAR', '2023-01-02 00:00:00', null, ['retracted_at' => '2024-01-01 00:00:00']);

        $this->assertNotContains('RETRD', $this->universeCodes('2025-01-02'));
    }

    /**
     * The legacy projection must open the interval, or every projected listing would be unresolvable
     * the moment the temporal record became required.
     */
    public function test_the_legacy_projection_opens_a_board_interval_from_the_listed_date(): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'company_name' => 'Bank Central Asia',
            'listed_date' => '2023-01-02',
            'board_code' => 'MAIN',
            'is_active' => 1,
        ]);

        (new TemporalIdentityRepository())->ensureLegacyProjection(['BBCA']);

        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        $board = DB::table('md_listing_boards')->where('listing_id', $listingId)->first();

        $this->assertNotNull($board, 'a projected listing with no board interval cannot be resolved at all');
        $this->assertSame('2023-01-02 00:00:00', (string) $board->effective_from);
        $this->assertSame('REGULAR', (string) $board->market_segment);
        $this->assertSame('LEGACY_MASTER_PROJECTION', (string) $board->change_reason);
        $this->assertContains('BBCA', $this->universeCodes('2024-01-02'));
    }

    private function boardOn(string $tradeDate): ?string
    {
        foreach ((new TemporalIdentityRepository())->readProjectedUniverseAsOf($tradeDate) as $row) {
            return $row['board_code'];
        }

        return null;
    }

    private function universeCodes(string $tradeDate): array
    {
        $codes = [];
        foreach ((new TemporalIdentityRepository())->readProjectedUniverseAsOf($tradeDate) as $row) {
            $codes[] = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
        }
        sort($codes);

        return $codes;
    }

    private function seedBoard(int $listingId, string $board, string $segment, string $from, ?string $to, array $override = []): void
    {
        DB::table('md_listing_boards')->insert($override + [
            'listing_id' => $listingId,
            'market_segment' => $segment,
            'board_code' => $board,
            'effective_from' => $from,
            'effective_to' => $to,
            'recorded_at' => $from,
            'retracted_at' => null,
            'source_ref' => 'fixture',
            'change_reason' => 'BOARD_MOVEMENT',
        ]);
    }

    private function seedSymbol(int $listingId, string $symbol, string $from, ?string $to): void
    {
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId,
            'symbol' => $symbol,
            'symbol_type' => 'EXCHANGE',
            'effective_from' => $from,
            'effective_to' => $to,
            'recorded_at' => $from,
        ]);
    }

    private function seedListing(array $override = []): int
    {
        $n = $override['n'] ?? 1;
        unset($override['n']);

        $issuerId = DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'ISSUER-'.$n,
            'legal_name' => 'Issuer '.$n,
            'recorded_at' => '2020-01-01 00:00:00',
            'created_at' => '2020-01-01 00:00:00',
        ]);

        $instrumentId = DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'INSTRUMENT-'.$n,
            'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY',
            'currency_code' => 'IDR',
            'recorded_at' => '2020-01-01 00:00:00',
            'created_at' => '2020-01-01 00:00:00',
        ]);

        return (int) DB::table('md_listings')->insertGetId(array_merge([
            'listing_uid' => 'LISTING-'.$n,
            'legacy_ticker_id' => 900 + $n,
            'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX',
            'market_segment' => 'REGULAR',
            'board_code' => 'MAIN',
            'listed_date' => '2023-01-02',
            'delisted_date' => null,
            'listing_state' => 'LISTED',
            'recorded_at' => '2023-01-02 00:00:00',
            'created_at' => '2023-01-02 00:00:00',
        ], $override));
    }
}
