<?php

use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W05 / MD-B05 — the five identity layers, their temporal fields, and the interval convention.
 *
 * Owner contract:
 *   docs/market_data/authority/strategy/book/Tickers_and_Identity_Dependency_Contract_LOCKED.md
 *
 * The contract requires issuer, instrument, listing, display symbol, and provider symbol mapping to
 * remain distinct concepts, each with a stable immutable identity; requires records that can change
 * historical membership to carry an effective start, a nullable end under one documented convention,
 * a status or change reason, provenance and revision identity, and a known-time coordinate; and
 * separates effective time from recorded time, permitting current fields only as cached projections.
 *
 * Structure is asserted against the schema because that is where these predicates live, and the
 * interval convention is asserted behaviorally on both sides of a boundary, because "one documented
 * convention" is only meaningful if the code agrees with the document about which one it is.
 */
class TemporalIdentityLayerContractTest extends TestCase
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

    /** The five layers are separate tables with their own immutable identity, not one flattened row. */
    public function test_each_identity_layer_is_a_distinct_record_with_its_own_stable_identity(): void
    {
        foreach ([
            'md_issuers' => ['issuer_id', 'issuer_uid'],
            'md_instruments' => ['instrument_id', 'instrument_uid', 'issuer_id'],
            'md_listings' => ['listing_id', 'listing_uid', 'instrument_id'],
            'md_listing_symbols' => ['listing_symbol_id', 'listing_id', 'symbol'],
            'md_provider_symbol_mappings' => ['provider_mapping_id', 'listing_id', 'provider', 'provider_symbol'],
        ] as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), $table.' is missing');
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), $table.'.'.$column.' is missing');
            }
        }

        // Distinctness is the point of the layering: an instrument belongs to an issuer and a
        // listing to an instrument, so neither may carry the other's identity as its own key.
        $this->assertFalse(Schema::hasColumn('md_issuers', 'instrument_id'), 'issuer must not key on instrument');
        $this->assertFalse(Schema::hasColumn('md_instruments', 'listing_id'), 'instrument must not key on listing');
        $this->assertFalse(Schema::hasColumn('md_listings', 'symbol'), 'a listing must not carry symbol text as identity');
    }

    /** The venue/segment/board that defines a listing is temporal, and the symbol attaches to it. */
    public function test_the_listing_layer_carries_venue_segment_and_board_over_an_effective_interval(): void
    {
        $this->assertTrue(Schema::hasColumn('md_listings', 'exchange_code'));
        $this->assertTrue(Schema::hasColumn('md_listings', 'listed_date'));
        $this->assertTrue(Schema::hasColumn('md_listings', 'delisted_date'));
        $this->assertTrue(Schema::hasTable('md_listing_boards'), 'segment and board must be effective-dated');
        $this->assertTrue(Schema::hasColumn('md_listing_boards', 'market_segment'));
        $this->assertTrue(Schema::hasColumn('md_listing_boards', 'board_code'));
        $this->assertTrue(Schema::hasColumn('md_listing_symbols', 'listing_id'), 'a symbol attaches to a listing');
    }

    /**
     * Every record that can change historical membership carries the same six temporal fields. The
     * assertion is written as one loop over the three tables so a new temporal record type cannot be
     * added with a thinner shape than the ones already governed.
     */
    public function test_every_temporal_record_carries_interval_provenance_and_known_time(): void
    {
        foreach (['md_listing_symbols', 'md_listing_boards', 'md_provider_symbol_mappings'] as $table) {
            foreach ([
                'effective_from',   // effective start
                'effective_to',     // nullable end
                'recorded_at',      // known time for as-known replay
                'source_ref',       // provenance
                'change_reason',    // status/reason for the change
                'retracted_at',     // the record is corrected by revision, not by deletion
            ] as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), $table.'.'.$column.' is missing');
            }
        }

        $this->assertTrue(Schema::hasColumn('md_provider_symbol_mappings', 'mapping_revision'), 'mapping revision identity');
        $this->assertTrue(Schema::hasColumn('md_listings', 'listing_state'), 'listing/delisting state');
        foreach (['md_issuers', 'md_instruments', 'md_listings'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'recorded_at'), $table.' has no known-time coordinate');
            $this->assertTrue(Schema::hasColumn($table, 'source_ref'), $table.' has no provenance');
        }
    }

    /**
     * The nullable end is exclusive, and the three identity record types agree.
     *
     * A convention that differs between two of them would still satisfy a per-table reading and
     * would move observations to the wrong identity by exactly one session at every boundary, which
     * is the failure the mapping contract calls out by name.
     */
    public function test_the_identity_interval_end_is_exclusive_and_the_same_in_every_record_type(): void
    {
        $listingId = $this->seedListing(1);
        $this->seedSymbol($listingId, 'BEFORE', '2023-01-02 00:00:00', '2025-04-01 00:00:00');
        $this->seedSymbol($listingId, 'AFTERR', '2025-04-01 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', '2023-01-02 00:00:00', '2025-04-01 00:00:00');
        $this->seedBoard($listingId, 'DEVELOPMENT', '2025-04-01 00:00:00', null);
        $this->seedMapping($listingId, 'OLD.JK', '2023-01-02 00:00:00', '2025-04-01 00:00:00');
        $this->seedMapping($listingId, 'NEW.JK', '2025-04-01 00:00:00', null);

        $dayBefore = $this->identityOn('2025-03-31');
        $boundary = $this->identityOn('2025-04-01');

        $this->assertSame('BEFORE', $dayBefore['ticker_code'], 'the closing interval owns the day before the boundary');
        $this->assertSame('MAIN', $dayBefore['board_code']);
        $this->assertSame('AFTERR', $boundary['ticker_code'], 'the boundary date itself belongs to the new interval');
        $this->assertSame('DEVELOPMENT', $boundary['board_code']);

        $repository = new TemporalIdentityRepository();
        $this->assertSame('OLD.JK', $repository->resolveProviderContext('BEFORE', 'yahoo_finance', '2025-03-31')['provider_symbol']);
        $this->assertSame('NEW.JK', $repository->resolveProviderContext('AFTERR', 'yahoo_finance', '2025-04-01')['provider_symbol']);
    }

    /**
     * Recorded time answers a different question from effective time, and the resolver must keep
     * them apart: a record recorded after the cutoff is invisible to an as-known read even though its
     * effective interval covers the trade date.
     */
    public function test_recorded_time_and_effective_time_are_separate_coordinates(): void
    {
        $listingId = $this->seedListing(2, ['recorded_at' => '2023-01-02 00:00:00']);
        $this->seedSymbol($listingId, 'LATEKN', '2023-01-02 00:00:00', null, '2026-03-01 00:00:00');
        $this->seedBoard($listingId, 'MAIN', '2023-01-02 00:00:00', null, '2026-03-01 00:00:00');

        $this->assertSame([], (new TemporalIdentityRepository())
            ->readProjectedUniverseAsOf('2025-06-02', '2026-02-28 00:00:00'), 'a record not yet known must not resolve');
        $this->assertCount(1, (new TemporalIdentityRepository())
            ->readProjectedUniverseAsOf('2025-06-02', '2026-03-02 00:00:00'), 'and must resolve once it is known');
    }

    /**
     * The point-in-time output is a complete identity, not a code. Every element the contract lists
     * is named individually so dropping one is a failure rather than a smaller array.
     */
    public function test_point_in_time_resolution_returns_the_full_identity_for_the_trade_date(): void
    {
        $listingId = $this->seedListing(3);
        $this->seedSymbol($listingId, 'FULLID', '2023-01-02 00:00:00', null);
        $this->seedBoard($listingId, 'MAIN', '2023-01-02 00:00:00', null);
        $this->seedMapping($listingId, 'FULLID.JK', '2023-01-02 00:00:00', null);

        $context = (new TemporalIdentityRepository())->resolveProviderContext('FULLID', 'yahoo_finance', '2024-05-02');

        foreach ([
            'issuer_id', 'instrument_id', 'listing_id', 'ticker_code', 'market_segment', 'board_code',
            'listed_date', 'delisted_date', 'listing_symbol_id', 'listing_board_id',
            'identity_recorded_at', 'provider', 'provider_symbol', 'provider_mapping_id',
            'mapping_revision', 'provider_mapping_recorded_at',
        ] as $key) {
            $this->assertArrayHasKey($key, $context, 'point-in-time resolution omits '.$key);
        }
        $this->assertGreaterThan(0, $context['issuer_id']);
        $this->assertGreaterThan(0, $context['instrument_id']);
        $this->assertGreaterThan(0, $context['listing_id']);
        $this->assertSame('REGULAR', $context['market_segment']);
        $this->assertNull($context['delisted_date'], 'listing validity state on the trade date');
        $this->assertNotSame('', $context['mapping_revision'], 'the revision identity used by the run');
    }

    /**
     * The dependency direction: identity is projected from the shared master into `market_data`, and
     * the owned facts are not projected back out. A projection that wrote to the master would be
     * this contract's ownership note broken in the direction it warns about.
     */
    public function test_the_projection_reads_the_shared_master_and_does_not_write_to_it(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 3).'/app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php'
        );
        $tickerTable = "'".'tickers'."'";

        $this->assertNotFalse(strpos($source, 'DB::table($tickerTable)'), 'the master is read');
        foreach (['insert', 'update', 'delete', 'insertGetId', 'truncate'] as $write) {
            $this->assertFalse(
                strpos($source, 'DB::table($tickerTable)->'.$write),
                'the identity projection must not '.$write.' the shared master'
            );
            $this->assertFalse(
                strpos($source, 'DB::table('.$tickerTable.')->'.$write),
                'the identity projection must not '.$write.' the shared master'
            );
        }
    }

    private function identityOn(string $tradeDate): array
    {
        $rows = (new TemporalIdentityRepository())->readProjectedUniverseAsOf($tradeDate);
        $this->assertCount(1, $rows, 'expected exactly one resolved listing on '.$tradeDate);

        return $rows[0];
    }

    private function seedListing(int $n, array $override = []): int
    {
        $issuerId = DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'ISSUER-'.$n, 'legal_name' => 'Issuer '.$n,
            'source_ref' => 'fixture', 'recorded_at' => '2020-01-01 00:00:00', 'created_at' => '2020-01-01 00:00:00',
        ]);
        $instrumentId = DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'INSTRUMENT-'.$n, 'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR', 'source_ref' => 'fixture',
            'recorded_at' => '2020-01-01 00:00:00', 'created_at' => '2020-01-01 00:00:00',
        ]);

        return (int) DB::table('md_listings')->insertGetId(array_merge([
            'listing_uid' => 'LISTING-'.$n, 'legacy_ticker_id' => 900 + $n, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'listed_date' => '2023-01-02', 'delisted_date' => null, 'listing_state' => 'LISTED',
            'source_ref' => 'fixture', 'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ], $override));
    }

    private function seedSymbol(int $listingId, string $symbol, string $from, ?string $to, string $recordedAt = null): void
    {
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => $symbol, 'symbol_type' => 'EXCHANGE',
            'symbol_namespace' => 'IDX', 'effective_from' => $from, 'effective_to' => $to,
            'recorded_at' => $recordedAt ?: $from, 'source_ref' => 'fixture', 'change_reason' => 'SYMBOL_CHANGE',
        ]);
    }

    private function seedBoard(int $listingId, string $board, string $from, ?string $to, string $recordedAt = null): void
    {
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => $board,
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $recordedAt ?: $from,
            'source_ref' => 'fixture', 'change_reason' => 'BOARD_MOVEMENT',
        ]);
    }

    private function seedMapping(int $listingId, string $providerSymbol, string $from, ?string $to): void
    {
        DB::table('md_provider_symbol_mappings')->insert([
            'listing_id' => $listingId, 'provider' => 'yahoo_finance', 'provider_symbol' => $providerSymbol,
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $from,
            'mapping_revision' => 'temporal_provider_mapping_v1', 'source_ref' => 'fixture',
            'change_reason' => 'PROVIDER_MAPPING',
        ]);
    }
}
