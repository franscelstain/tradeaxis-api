<?php

use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Application\MarketData\Services\EodBarsIngestService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `Determinism_Invariants_LOCKED.md:122` — "As-known replay resolves only revisions known by the
 * declared knowledge cutoff. Current state must not leak into either mode."
 *
 * Acquisition carried a `known_at` context key that the calendar assertion and the provider symbol
 * resolver already read, and that nothing ever wrote. The universe read that chooses which tickers
 * to acquire did not consult it at all, so a run with a declared cutoff still asked the source for
 * the world as it stands now.
 *
 * A cutoff that excluded every listing would also look stable, so the claim is paired with its
 * counterproof: the same corpus acquired without a cutoff must include the later listing.
 */
class AcquisitionKnowledgeCutoffTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-24';

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

    private function seedListing(int $n, string $recordedAt): void
    {
        $issuerId = DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'ISSUER-'.$n, 'legal_name' => 'Issuer '.$n,
            'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $instrumentId = DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'INSTRUMENT-'.$n, 'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR',
            'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $listingId = (int) DB::table('md_listings')->insertGetId([
            'listing_uid' => 'LISTING-'.$n, 'legacy_ticker_id' => 900 + $n,
            'instrument_id' => $instrumentId, 'exchange_code' => 'IDX',
            'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'listed_date' => '2023-01-02', 'delisted_date' => null, 'listing_state' => 'LISTED',
            'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => 'AAA'.$n, 'symbol_type' => 'EXCHANGE',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => $recordedAt,
        ]);
    }

    /**
     * Acquire once and report which ticker codes the source was asked for.
     *
     * The concrete adapters are stood in for rather than the ports they implement:
     * `LifecycleProofIsNotMockedTest` permits a DB-backed test to replace only
     * `App\Infrastructure\MarketData\Source`, the boundary that reaches a third party.
     */
    private function codesAcquiredAt(?string $knownAt): array
    {
        $captured = [];

        $api = m::mock(PublicApiEodBarsAdapter::class);
        $api->shouldReceive('fetchOrLoadEodBars')
            ->andReturnUsing(function ($tradeDate, $sourceMode, array $codes = [], array $context = []) use (&$captured) {
                $captured = $codes;
                return [];
            });

        $service = new EodBarsIngestService(
            m::mock(LocalFileEodBarsAdapter::class),
            $api,
            new TickerMasterRepository(),
            new EodArtifactRepository(),
            new EodPublicationRepository()
        );

        $service->acquireSourceRows(self::TRADE_DATE, 'api', null, ['known_at' => $knownAt]);
        sort($captured);

        return $captured;
    }

    public function test_acquisition_does_not_ask_for_a_listing_recorded_after_the_cutoff(): void
    {
        foreach ([1, 2, 3] as $n) {
            $this->seedListing($n, '2026-03-01 00:00:00');
        }
        $this->seedListing(4, '2026-03-20 00:00:00');

        $this->assertSame(
            ['AAA1', 'AAA2', 'AAA3'],
            $this->codesAcquiredAt('2026-03-10 00:00:00'),
            'acquisition asked the source for a listing the run could not yet know'
        );
    }

    /**
     * Counterproof. Without a cutoff the same corpus must offer the later listing, otherwise the
     * exclusion above would be a property of the fixture rather than of the cutoff.
     */
    public function test_acquisition_without_a_cutoff_sees_the_later_listing(): void
    {
        foreach ([1, 2, 3] as $n) {
            $this->seedListing($n, '2026-03-01 00:00:00');
        }
        $this->seedListing(4, '2026-03-20 00:00:00');

        $this->assertSame(['AAA1', 'AAA2', 'AAA3', 'AAA4'], $this->codesAcquiredAt(null));
    }

    /**
     * Second counterproof: the cutoff is a coordinate, not a blanket exclusion.
     */
    public function test_a_later_cutoff_admits_the_later_listing(): void
    {
        foreach ([1, 2, 3] as $n) {
            $this->seedListing($n, '2026-03-01 00:00:00');
        }
        $this->seedListing(4, '2026-03-20 00:00:00');

        $this->assertSame(['AAA1', 'AAA2', 'AAA3', 'AAA4'], $this->codesAcquiredAt('2026-03-22 00:00:00'));
    }
}
