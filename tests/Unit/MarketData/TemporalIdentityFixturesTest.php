<?php

use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W05 — temporal issuer/instrument/listing/symbol/provider mapping, stage 6.
 *
 * Exit gate: "listing/delisting, rename, symbol reuse, provider mapping revision, dan
 * inactive-now-active-then fixtures lulus tanpa survivorship leakage."
 *
 * Owner contracts:
 *   docs/market_data/authority/strategy/book/Tickers_and_Identity_Dependency_Contract_LOCKED.md
 *   docs/market_data/authority/strategy/book/Symbol_Lifecycle_and_Mapping_Contract.md
 *
 * Each fixture seeds the temporal tables directly rather than projecting from the legacy master,
 * because the cases under test — rename, reuse, mapping revision — cannot be expressed by a
 * current-state master at all. That is precisely why the temporal model exists.
 */
class TemporalIdentityFixturesTest extends TestCase
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

        $attributes = array_merge([
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
        ], $override);

        $listingId = (int) DB::table('md_listings')->insertGetId($attributes);

        // Board and market segment became effective-dated at 2026_08_22_000001, so the fixture
        // records the interval the listing dates already imply. Without it the listing is
        // unresolvable by design, which `ListingBoardAndSegmentTemporalityTest` asserts directly.
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId,
            'market_segment' => $attributes['market_segment'],
            'board_code' => $attributes['board_code'],
            'effective_from' => $attributes['listed_date'].' 00:00:00',
            'effective_to' => $attributes['delisted_date'] ? $attributes['delisted_date'].' 00:00:00' : null,
            'recorded_at' => $attributes['recorded_at'],
            'change_reason' => 'LEGACY_MASTER_PROJECTION',
        ]);

        return $listingId;
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

    private function universeCodes(string $tradeDate): array
    {
        $codes = [];
        foreach ((new TemporalIdentityRepository())->universeAsOf($tradeDate) as $row) {
            $codes[] = strtoupper(trim((string) ($row['ticker_code'] ?? '')));
        }
        sort($codes);

        return $codes;
    }

    /**
     * A listing delisted mid-dataset must be present before its delisting date and absent after.
     * Verified against production data during W05: legacy ticker 939, delisted 2023-04-06, is
     * `is_active = 0` today, appears in the 2023-03-01 universe and not in the 2026-07-28 one.
     */
    public function test_a_delisted_listing_is_present_before_and_absent_after_its_delisting(): void
    {
        $listingId = $this->seedListing(['n' => 1, 'delisted_date' => '2024-06-01', 'listing_state' => 'DELISTED']);
        $this->seedSymbol($listingId, 'GONE', '2023-01-02 00:00:00', null);

        $this->assertContains('GONE', $this->universeCodes('2024-05-31'));
        $this->assertNotContains('GONE', $this->universeCodes('2024-06-02'));
    }

    /**
     * A listing is absent before its listed date. The inverse of survivorship: a security that
     * exists today must not appear in a universe that predates its admission.
     */
    public function test_a_listing_is_absent_before_its_listed_date(): void
    {
        $listingId = $this->seedListing(['n' => 2, 'listed_date' => '2025-03-10']);
        $this->seedSymbol($listingId, 'LATE', '2025-03-10 00:00:00', null);

        $this->assertNotContains('LATE', $this->universeCodes('2025-03-09'));
        $this->assertContains('LATE', $this->universeCodes('2025-03-10'));
    }

    /**
     * A rename closes the old symbol interval and opens a new one against the same listing.
     * Resolution on each side must return the symbol effective then, never the current one.
     */
    public function test_a_rename_resolves_to_the_symbol_effective_on_the_trade_date(): void
    {
        $listingId = $this->seedListing(['n' => 3]);
        $this->seedSymbol($listingId, 'OLDNM', '2023-01-02 00:00:00', '2024-04-01 00:00:00');
        $this->seedSymbol($listingId, 'NEWNM', '2024-04-01 00:00:00', null);

        $before = $this->universeCodes('2024-03-31');
        $after = $this->universeCodes('2024-04-02');

        $this->assertContains('OLDNM', $before);
        $this->assertNotContains('NEWNM', $before);
        $this->assertContains('NEWNM', $after);
        $this->assertNotContains('OLDNM', $after);
    }

    /**
     * Symbol text reused by a different instrument must resolve to the listing that held it on
     * the trade date. Attaching the earlier history to the later instrument is the failure this
     * prevents, and it is invisible in a current-state master.
     */
    public function test_symbol_reuse_resolves_to_the_listing_that_held_it_then(): void
    {
        $first = $this->seedListing(['n' => 4, 'delisted_date' => '2024-01-31', 'listing_state' => 'DELISTED']);
        $this->seedSymbol($first, 'REUSE', '2023-01-02 00:00:00', '2024-01-31 00:00:00');

        $second = $this->seedListing(['n' => 5, 'listed_date' => '2024-06-01']);
        $this->seedSymbol($second, 'REUSE', '2024-06-01 00:00:00', null);

        $early = (new TemporalIdentityRepository())->universeAsOf('2023-06-01');
        $late = (new TemporalIdentityRepository())->universeAsOf('2024-09-01');

        $resolve = function (array $universe): ?int {
            foreach ($universe as $row) {
                if (strtoupper(trim((string) ($row['ticker_code'] ?? ''))) === 'REUSE') {
                    return (int) $row['listing_id'];
                }
            }

            return null;
        };

        $this->assertSame($first, $resolve($early), 'the earlier era must resolve to the first listing');
        $this->assertSame($second, $resolve($late), 'the later era must resolve to the second listing');
        $this->assertNotSame($resolve($early), $resolve($late));
    }

    /**
     * A retracted symbol row must not resolve at all. Retraction records that a mapping was
     * asserted and withdrawn, which is a different fact from a closed interval.
     */
    public function test_a_retracted_symbol_does_not_resolve(): void
    {
        $listingId = $this->seedListing(['n' => 6]);
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId,
            'symbol' => 'PULLED',
            'symbol_type' => 'EXCHANGE',
            'effective_from' => '2023-01-02 00:00:00',
            'effective_to' => null,
            'recorded_at' => '2023-01-02 00:00:00',
            'retracted_at' => '2023-02-01 00:00:00',
        ]);

        $this->assertNotContains('PULLED', $this->universeCodes('2024-01-05'));
    }

    /**
     * Provider mapping is resolved per provider and trade date. A later mapping revision must
     * not reach back and change how an earlier date resolves.
     */
    public function test_provider_mapping_revision_does_not_rewrite_earlier_resolution(): void
    {
        $listingId = $this->seedListing(['n' => 7]);
        $this->seedSymbol($listingId, 'MAPD', '2023-01-02 00:00:00', null);

        DB::table('md_provider_symbol_mappings')->insert([
            [
                'listing_id' => $listingId,
                'provider' => 'yahoo_finance',
                'provider_symbol' => 'MAPD.JK',
                'effective_from' => '2023-01-02 00:00:00',
                'effective_to' => '2025-01-01 00:00:00',
                'recorded_at' => '2023-01-02 00:00:00',
                'mapping_revision' => 'rev-1',
            ],
            [
                'listing_id' => $listingId,
                'provider' => 'yahoo_finance',
                'provider_symbol' => 'MAPD2.JK',
                'effective_from' => '2025-01-01 00:00:00',
                'effective_to' => null,
                'recorded_at' => '2025-01-01 00:00:00',
                'mapping_revision' => 'rev-2',
            ],
        ]);

        $repository = new TemporalIdentityRepository();

        $early = $repository->resolveProviderContext('MAPD', 'yahoo_finance', '2024-06-01');
        $late = $repository->resolveProviderContext('MAPD', 'yahoo_finance', '2025-06-01');

        $this->assertSame('MAPD.JK', $early['provider_symbol'] ?? null);
        $this->assertSame('MAPD2.JK', $late['provider_symbol'] ?? null);
    }
}
