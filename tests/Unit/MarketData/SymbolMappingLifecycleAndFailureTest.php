<?php

use App\Infrastructure\MarketData\Source\EquityProviderSymbolResolver;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W05 / MD-B05 — symbol lifecycle, mapping uniqueness, and mapping failure behavior.
 *
 * Owner contract:
 *   docs/market_data/authority/strategy/book/Symbol_Lifecycle_and_Mapping_Contract.md
 *
 * Symbol text is mutable metadata. Every lookup for a trade date and provider resolves through an
 * effective-dated mapping record, and the contract is explicit that appending `.JK` is an adapter
 * rendering rule which "cannot substitute for a mapping record". The failure side is equally
 * explicit: unknown symbol rejects, ambiguity fails closed, a date outside mapping validity is
 * rejected with a reason, and an unavailable mapping dependency must not fabricate an identity.
 *
 * `TemporalIdentityFixturesTest` already pins rename, reuse, retraction, and revision ordering.
 * This suite covers what it does not: the uniqueness rules, the four failure behaviors and the
 * reason codes they surface, lifecycle closure at delisting, relisting identity, and the boundary
 * between rendering a symbol and resolving an identity.
 */
class SymbolMappingLifecycleAndFailureTest extends TestCase
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

    /** A mapping record carries every element the contract lists, not just the symbol and target. */
    public function test_a_mapping_record_carries_identity_namespace_interval_provenance_and_reason(): void
    {
        $listingId = $this->seedListing(1);
        $this->seedSymbol($listingId, 'FULLMP');
        $this->seedMapping($listingId, 'FULLMP.JK', '2023-01-02 00:00:00', null, [
            'change_reason' => 'LISTING',
        ]);

        $row = DB::table('md_provider_symbol_mappings')->where('listing_id', $listingId)->first();

        $this->assertGreaterThan(0, (int) $row->provider_mapping_id, 'immutable mapping identity');
        $this->assertSame($listingId, (int) $row->listing_id, 'stable listing target');
        $this->assertSame('yahoo_finance', (string) $row->provider, 'provider namespace');
        $this->assertSame('FULLMP.JK', (string) $row->provider_symbol, 'symbol text exactly as used there');
        $this->assertSame('2023-01-02 00:00:00', (string) $row->effective_from);
        $this->assertNull($row->effective_to, 'nullable end under the documented convention');
        $this->assertNotSame('', (string) $row->source_ref, 'provenance');
        $this->assertSame('temporal_provider_mapping_v1', (string) $row->mapping_revision, 'mapping revision');
        $this->assertSame('2023-01-02 00:00:00', (string) $row->recorded_at, 'known time');
        $this->assertSame('LISTING', (string) $row->change_reason, 'optional change reason');

        // The listing symbol carries the exchange namespace on its own side of the boundary.
        $symbol = DB::table('md_listing_symbols')->where('listing_id', $listingId)->first();
        $this->assertSame('IDX', (string) $symbol->symbol_namespace);
    }

    /**
     * Suffix rendering is a transport convenience. It resolves no identity, and says so: the
     * preview path returns a null listing and a revision that names itself non-canonical, while the
     * enforced path returns a real mapping identity.
     */
    public function test_suffix_rendering_produces_no_identity_and_the_mapping_record_does(): void
    {
        $listingId = $this->seedListing(2);
        $this->seedSymbol($listingId, 'RENDER');
        $this->seedMapping($listingId, 'RENDER.JK', '2023-01-02 00:00:00', null);

        $resolver = new EquityProviderSymbolResolver(new TemporalIdentityRepository());
        $apiConfig = ['provider' => 'yahoo_finance', 'yahoo' => ['symbol_suffix' => '.JK']];

        $rendered = $resolver->resolveContext('RENDER', $apiConfig, '2024-05-02');
        $this->assertSame('RENDER.JK', $rendered['provider_symbol'], 'the suffix is applied');
        $this->assertNull($rendered['listing_id'], 'and establishes no stable identity');
        $this->assertNull($rendered['provider_mapping_id']);
        $this->assertSame('NON_CANONICAL_PREVIEW_ONLY', $rendered['mapping_revision']);

        $resolved = $resolver->resolveContext('RENDER', $apiConfig, '2024-05-02', ['enforce_temporal_mapping' => true]);
        $this->assertSame($listingId, $resolved['listing_id']);
        $this->assertGreaterThan(0, $resolved['provider_mapping_id']);
        $this->assertSame('temporal_provider_mapping_v1', $resolved['mapping_revision']);
    }

    /** One symbol, one provider, one instant: at most one listing. */
    public function test_two_listings_holding_one_symbol_at_the_same_instant_fail_closed(): void
    {
        $first = $this->seedListing(3);
        $second = $this->seedListing(4);
        $this->seedSymbol($first, 'CLASH');
        $this->seedSymbol($second, 'CLASH');
        $this->seedMapping($first, 'CLASH.JK', '2023-01-02 00:00:00', null);
        $this->seedMapping($second, 'CLASH.JK', '2023-01-02 00:00:00', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('PROVIDER_SYMBOL_MAPPING_AMBIGUOUS');

        (new TemporalIdentityRepository())->resolveProviderContext('CLASH', 'yahoo_finance', '2024-05-02');
    }

    /** One active mapping identity, one stable target: two provider symbols for one listing conflict. */
    public function test_one_listing_with_two_active_provider_symbols_fails_closed(): void
    {
        $listingId = $this->seedListing(5);
        $this->seedSymbol($listingId, 'TWOSYM');
        $this->seedMapping($listingId, 'TWOSYM.JK', '2023-01-02 00:00:00', null);
        $this->seedMapping($listingId, 'TWOSYM2.JK', '2024-01-02 00:00:00', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('PROVIDER_SYMBOL_MAPPING_AMBIGUOUS');

        (new TemporalIdentityRepository())->resolveProviderContext('TWOSYM', 'yahoo_finance', '2025-01-02');
    }

    /**
     * A gap in mapping validity is a failure, not an invitation to use the current mapping. The
     * assertion pins both halves: the requested date fails, and a date the mapping does cover
     * resolves — otherwise a resolver that failed on everything would pass.
     */
    public function test_a_date_outside_mapping_validity_is_rejected_rather_than_served_by_the_current_mapping(): void
    {
        $listingId = $this->seedListing(6);
        $this->seedSymbol($listingId, 'GAPPED');
        $this->seedMapping($listingId, 'GAPPED.JK', '2025-06-02 00:00:00', null);

        $this->assertSame(
            'GAPPED.JK',
            (new TemporalIdentityRepository())->resolveProviderContext('GAPPED', 'yahoo_finance', '2025-06-03')['provider_symbol']
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('PROVIDER_SYMBOL_MAPPING_MISSING');

        (new TemporalIdentityRepository())->resolveProviderContext('GAPPED', 'yahoo_finance', '2025-05-30');
    }

    /** An unknown symbol resolves to nothing and fabricates nothing. */
    public function test_an_unknown_symbol_fabricates_no_identity(): void
    {
        $this->seedSymbol($this->seedListing(7), 'KNOWNS');

        $before = DB::table('md_listings')->count();
        try {
            (new TemporalIdentityRepository())->resolveProviderContext('NOSUCH', 'yahoo_finance', '2024-05-02');
            $this->fail('an unmapped provider symbol must not resolve');
        } catch (\RuntimeException $e) {
            $this->assertStringStartsWith('PROVIDER_SYMBOL_MAPPING_MISSING', $e->getMessage());
        }

        $this->assertSame($before, DB::table('md_listings')->count(), 'no listing was invented');
        $this->assertSame(0, DB::table('md_listing_symbols')->where('symbol', 'NOSUCH')->count());
        $this->assertSame(0, DB::table('md_provider_symbol_mappings')->where('provider_symbol', 'NOSUCH.JK')->count());
    }

    /**
     * Batch resolution skips what it cannot map instead of substituting. A silent substitution and a
     * skip look the same in a count, so the resolved set is compared by key.
     */
    public function test_batch_resolution_omits_the_unmappable_symbol_instead_of_substituting_one(): void
    {
        $mapped = $this->seedListing(8);
        $this->seedSymbol($mapped, 'MAPPED');
        $this->seedMapping($mapped, 'MAPPED.JK', '2023-01-02 00:00:00', null);
        $unmapped = $this->seedListing(9);
        $this->seedSymbol($unmapped, 'UNMAPD');

        $contexts = (new TemporalIdentityRepository())->resolveByTickerCodes(['MAPPED', 'UNMAPD'], '2024-05-02');

        $this->assertSame(['MAPPED'], array_keys($contexts));
        $this->assertSame($mapped, $contexts['MAPPED']['listing_id']);
    }

    /**
     * The acquisition boundary turns a mapping failure into a governed per-instrument outcome rather
     * than a crash or a silent skip: an explicit reason code, ticker scope, and a failed status.
     */
    public function test_the_acquisition_boundary_surfaces_a_mapping_failure_as_an_explicit_ticker_scoped_reason(): void
    {
        $this->assertTrue(
            class_exists(SourceAcquisitionException::class),
            'the acquisition failure type must exist for the mapping reason codes to travel'
        );

        $adapter = file_get_contents(
            dirname(__DIR__, 3).'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php'
        );
        $this->assertNotFalse(
            strpos($adapter, "'PROVIDER_SYMBOL_MAPPING_AMBIGUOUS'"),
            'ambiguity must reach acquisition as its own reason code'
        );
        $this->assertNotFalse(
            strpos($adapter, "'PROVIDER_SYMBOL_MAPPING_MISSING'"),
            'a missing mapping must reach acquisition as its own reason code'
        );
        $this->assertNotFalse(
            strpos($adapter, "'failure_scope' => 'TICKER'"),
            'the failure stays per-instrument rather than failing the run'
        );
    }

    /** Delisting closes symbol validity at the governed boundary; the row is closed, not deleted. */
    public function test_delisting_closes_symbol_validity_without_deleting_the_record(): void
    {
        $listingId = $this->seedListing(10, ['delisted_date' => '2024-06-01', 'listing_state' => 'DELISTED']);
        $this->seedSymbol($listingId, 'CLOSED', '2023-01-02 00:00:00', '2024-06-01 00:00:00');
        $this->seedMapping($listingId, 'CLOSED.JK', '2023-01-02 00:00:00', '2024-06-01 00:00:00');

        $this->assertSame(
            'CLOSED.JK',
            (new TemporalIdentityRepository())->resolveProviderContext('CLOSED', 'yahoo_finance', '2024-05-31')['provider_symbol']
        );
        $this->assertCount(0, (new TemporalIdentityRepository())->readProjectedUniverseAsOf('2024-06-02'));
        $this->assertSame(1, DB::table('md_listing_symbols')->where('symbol', 'CLOSED')->count(), 'history is retained');
    }

    /**
     * A relisting is either the same instrument under a new listing or a different instrument, and
     * the record has to say which. Both shapes are asserted, because a model that could only express
     * one of them would satisfy a single-case test and lose the distinction.
     */
    public function test_relisting_states_whether_the_instrument_continues_or_changes(): void
    {
        $original = $this->seedListing(11, ['delisted_date' => '2024-03-01', 'listing_state' => 'DELISTED']);
        $instrumentId = (int) DB::table('md_listings')->where('listing_id', $original)->value('instrument_id');
        $this->seedSymbol($original, 'RELIST', '2023-01-02 00:00:00', '2024-03-01 00:00:00');

        // Same instrument, new listing identity.
        $continued = (int) DB::table('md_listings')->insertGetId([
            'listing_uid' => 'LISTING-11B', 'legacy_ticker_id' => 9110, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'listed_date' => '2025-01-06', 'delisted_date' => null, 'listing_state' => 'LISTED',
            'source_ref' => 'fixture', 'recorded_at' => '2025-01-06 00:00:00', 'created_at' => '2025-01-06 00:00:00',
        ]);
        $this->seedBoard($continued, '2025-01-06 00:00:00');
        $this->seedSymbol($continued, 'RELIST', '2025-01-06 00:00:00', null);

        $this->assertNotSame($original, $continued, 'a relisting is a new listing identity');
        $this->assertSame(
            $instrumentId,
            (int) DB::table('md_listings')->where('listing_id', $continued)->value('instrument_id'),
            'continuation is expressed by keeping the instrument'
        );

        $different = $this->seedListing(12, ['listed_date' => '2025-01-06']);
        $this->assertNotSame(
            $instrumentId,
            (int) DB::table('md_listings')->where('listing_id', $different)->value('instrument_id'),
            'a different instrument is expressed by a different instrument identity'
        );
    }

    /**
     * A provider correction is a new revision that preserves the prior lineage. Overwriting the row
     * would make the earlier acquisition unexplainable, which is what "preserves prior lineage"
     * rules out.
     */
    public function test_a_provider_correction_appends_a_revision_and_keeps_the_prior_one(): void
    {
        $listingId = $this->seedListing(13);
        $this->seedSymbol($listingId, 'CORREC');
        $this->seedMapping($listingId, 'WRONG.JK', '2023-01-02 00:00:00', '2024-02-01 00:00:00', [
            'mapping_revision' => 'rev-1', 'change_reason' => 'LISTING',
        ]);
        $this->seedMapping($listingId, 'RIGHT.JK', '2024-02-01 00:00:00', null, [
            'mapping_revision' => 'rev-2', 'change_reason' => 'PROVIDER_CORRECTION',
        ]);

        $repository = new TemporalIdentityRepository();
        $before = $repository->resolveProviderContext('CORREC', 'yahoo_finance', '2024-01-31');
        $after = $repository->resolveProviderContext('CORREC', 'yahoo_finance', '2024-02-02');

        $this->assertSame('WRONG.JK', $before['provider_symbol'], 'the earlier run is still explainable');
        $this->assertSame('rev-1', $before['mapping_revision']);
        $this->assertSame('RIGHT.JK', $after['provider_symbol']);
        $this->assertSame('rev-2', $after['mapping_revision']);
        $this->assertSame(2, DB::table('md_provider_symbol_mappings')->where('listing_id', $listingId)->count());
    }

    private function seedListing(int $n, array $override = []): int
    {
        $issuerId = DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'ISSUER-'.$n, 'legal_name' => 'Issuer '.$n, 'source_ref' => 'fixture',
            'recorded_at' => '2020-01-01 00:00:00', 'created_at' => '2020-01-01 00:00:00',
        ]);
        $instrumentId = DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'INSTRUMENT-'.$n, 'issuer_id' => $issuerId, 'instrument_type' => 'EQUITY',
            'currency_code' => 'IDR', 'source_ref' => 'fixture',
            'recorded_at' => '2020-01-01 00:00:00', 'created_at' => '2020-01-01 00:00:00',
        ]);
        $listingId = (int) DB::table('md_listings')->insertGetId(array_merge([
            'listing_uid' => 'LISTING-'.$n, 'legacy_ticker_id' => 900 + $n, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'listed_date' => '2023-01-02', 'delisted_date' => null, 'listing_state' => 'LISTED',
            'source_ref' => 'fixture', 'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ], $override));

        $attributes = array_merge(['listed_date' => '2023-01-02', 'delisted_date' => null], $override);
        $this->seedBoard(
            $listingId,
            $attributes['listed_date'].' 00:00:00',
            $attributes['delisted_date'] ? $attributes['delisted_date'].' 00:00:00' : null
        );

        return $listingId;
    }

    private function seedBoard(int $listingId, string $from, ?string $to = null): void
    {
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $from,
            'source_ref' => 'fixture', 'change_reason' => 'LEGACY_MASTER_PROJECTION',
        ]);
    }

    private function seedSymbol(int $listingId, string $symbol, string $from = '2023-01-02 00:00:00', ?string $to = null): void
    {
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => $symbol, 'symbol_type' => 'EXCHANGE',
            'symbol_namespace' => 'IDX', 'effective_from' => $from, 'effective_to' => $to,
            'recorded_at' => $from, 'source_ref' => 'fixture', 'change_reason' => 'LISTING',
        ]);
    }

    private function seedMapping(int $listingId, string $providerSymbol, string $from, ?string $to, array $override = []): void
    {
        DB::table('md_provider_symbol_mappings')->insert($override + [
            'listing_id' => $listingId, 'provider' => 'yahoo_finance', 'provider_symbol' => $providerSymbol,
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $from,
            'mapping_revision' => 'temporal_provider_mapping_v1', 'source_ref' => 'fixture',
            'change_reason' => 'PROVIDER_MAPPING',
        ]);
    }
}
