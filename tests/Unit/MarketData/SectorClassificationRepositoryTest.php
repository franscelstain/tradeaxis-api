<?php

use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
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

    public function test_resolves_authoritative_effective_and_as_known_sector_membership(): void
    {
        $listingId = $this->seedTickerAndListing();
        DB::table('ticker_sector_memberships')->insert([
            [
                'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G',
                'classification_system' => 'IDX-IC', 'effective_from' => '2021-01-25',
                'effective_to' => '2026-05-31', 'source_name' => 'idx',
                'source_ref' => 'idx-classification-2021',
                'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
                'recorded_at' => '2021-01-20 12:00:00',
            ],
            [
                'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'I',
                'classification_system' => 'IDX-IC', 'effective_from' => '2026-06-01',
                'effective_to' => null, 'source_name' => 'idx',
                'source_ref' => 'idx-reclassification-2026',
                'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
                'recorded_at' => '2026-05-20 12:00:00',
            ],
        ]);

        $repository = new SectorClassificationRepository();

        $this->assertSame([1 => 'G'], $repository->resolveSectorCodesForTickerIds([1], '2026-05-19', null, '2026-05-21 00:00:00'));
        $this->assertSame([1 => 'I'], $repository->resolveSectorCodesForTickerIds([1], '2026-06-03', null, '2026-06-03 18:00:00'));
        $this->assertSame('RESOLVED_AUTHORITATIVE', $repository->resolveSectorContextForTickerIds([1], '2026-05-19', null, '2026-05-21 00:00:00')[1]['resolution_state']);
        $this->assertSame([1 => 'UNKNOWN'], $repository->resolveSectorCodesForTickerIds([1], '2021-01-24', null, '2026-06-03 18:00:00'));
    }

    public function test_reclassification_appends_closure_and_new_fact_without_editing_prior_row(): void
    {
        $this->seedTickerAndListing();
        $repository = new SectorClassificationRepository();

        $originalId = $repository->upsertMembership(
            1, 'G', '2021-01-25', null, 'idx', 'idx-initial', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2021-01-20 12:00:00'
        );
        $newId = $repository->upsertMembership(
            1, 'I', '2026-06-01', null, 'idx', 'idx-reclassification', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2026-05-20 12:00:00'
        );

        $rows = DB::table('ticker_sector_memberships')->where('ticker_id', 1)->orderBy('membership_id')->get();
        $original = $rows->first(function ($row) use ($originalId) {
            return (int) $row->membership_id === $originalId;
        });
        $closure = $rows->first(function ($row) use ($originalId) {
            return (int) $row->supersedes_membership_id === $originalId;
        });

        $this->assertCount(3, $rows);
        $this->assertNull($original->effective_to, 'the prior fact is immutable');
        $this->assertSame('2026-05-31', $closure->effective_to);
        $this->assertSame('I', DB::table('ticker_sector_memberships')->where('membership_id', $newId)->value('sector_code'));
        $this->assertSame([1 => 'G'], $repository->resolveSectorCodesForTickerIds([1], '2026-06-03', null, '2026-05-19 23:59:59'));
        $this->assertSame([1 => 'I'], $repository->resolveSectorCodesForTickerIds([1], '2026-06-03', null, '2026-05-20 12:00:00'));
    }

    public function test_overlapping_authoritative_intervals_fail_closed(): void
    {
        $listingId = $this->seedTickerAndListing();
        DB::table('ticker_sector_memberships')->insert([
            [
                'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G',
                'classification_system' => 'IDX-IC', 'effective_from' => '2021-01-25',
                'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'one',
                'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2021-01-20 12:00:00',
            ],
            [
                'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'I',
                'classification_system' => 'IDX-IC', 'effective_from' => '2026-06-01',
                'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'two',
                'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-05-20 12:00:00',
            ],
        ]);

        $context = (new SectorClassificationRepository())
            ->resolveSectorContextForTickerIds([1], '2026-06-03', null, '2026-06-03 18:00:00')[1];

        $this->assertSame('UNKNOWN', $context['sector_code']);
        $this->assertSame('SECTOR_MEMBERSHIP_OVERLAP_INVALID', $context['resolution_reason_code']);
    }

    public function test_derived_reference_cannot_be_appended_as_membership_authority(): void
    {
        $this->seedTickerAndListing();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SECTOR_SOURCE_AUTHORITY_CLASS_INVALID: DERIVED_REFERENCE.');

        (new SectorClassificationRepository())->upsertMembership(
            1, 'G', '2021-01-25', null, 'vendor', 'vendor-reference', 'IDX-IC',
            'DERIVED_REFERENCE', '2021-01-20 12:00:00'
        );
    }

    public function test_recorded_at_is_required_for_as_known_membership(): void
    {
        $this->seedTickerAndListing();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SECTOR_MEMBERSHIP_RECORDED_AT_INVALID');

        (new SectorClassificationRepository())->upsertMembership(
            1, 'G', '2021-01-25', null, 'idx', 'idx-reference', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE'
        );
    }

    private function seedTickerAndListing(): int
    {
        DB::table('tickers')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'BBCA',
            'company_name' => 'Bank Central Asia',
            'listed_date' => '2023-01-02',
            'is_active' => 1,
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection(['BBCA']);

        return (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
    }
}
