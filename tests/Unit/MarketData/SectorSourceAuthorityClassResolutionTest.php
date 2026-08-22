<?php

use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W05 / MD-B05 — the source authority class is a resolution input, not a write-time formality.
 *
 * Owner contract:
 *   docs/market_data/authority/strategy/book/Sector_Classification_Contract_LOCKED.md
 *
 * The contract grades sources into three classes and attaches a different condition to each:
 * `EXCHANGE_AUTHORITATIVE` may establish membership, `DERIVED_REFERENCE` may never, and
 * `OPERATOR_ENTERED` may only "with an explicit authoritative reference, named operator, and
 * governed reason code". A row that names the class without carrying the condition has not met it.
 *
 * `SectorClassificationRepository::appendMembership` enforced the triple, and nothing else did.
 * `operator_name` and `reason_code` are nullable with no database constraint tying them to the
 * class, so a row inserted by any other writer — a repair statement, an import path, a future
 * service — resolved as authoritative membership. The condition was checked exactly where it was
 * already satisfied and nowhere it could fail.
 *
 * These tests insert directly, deliberately. Going through the write path would prove the write
 * path, which was never the gap.
 */
class SectorSourceAuthorityClassResolutionTest extends TestCase
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

    public function test_an_operator_row_without_its_governance_triple_does_not_establish_membership(): void
    {
        $listingId = $this->seedTickerAndListing();

        foreach ([
            'no operator named' => ['operator_name' => null, 'reason_code' => 'SECTOR_RECLASSIFICATION', 'source_ref' => 'idx-announcement-2026-04'],
            'no governed reason' => ['operator_name' => 'ops.analyst', 'reason_code' => null, 'source_ref' => 'idx-announcement-2026-04'],
            'no authoritative reference' => ['operator_name' => 'ops.analyst', 'reason_code' => 'SECTOR_RECLASSIFICATION', 'source_ref' => null],
        ] as $label => $missing) {
            DB::table('ticker_sector_memberships')->where('listing_id', $listingId)->delete();
            $this->insertMembership($listingId, ['source_authority_class' => 'OPERATOR_ENTERED'] + $missing);

            $context = (new SectorClassificationRepository())
                ->resolveSectorContextForTickerIds([1], '2026-04-10', null, '2026-04-11 00:00:00')[1];

            $this->assertSame('UNKNOWN', $context['sector_code'], $label.': an unmet condition still established a sector');
            $this->assertSame('UNKNOWN', $context['resolution_state'], $label);
            $this->assertSame(
                'SECTOR_OPERATOR_GOVERNANCE_INCOMPLETE',
                $context['resolution_reason_code'],
                $label.': the reason must name why the row was refused, not merely that nothing was found'
            );
        }
    }

    public function test_an_operator_row_carrying_the_full_triple_does_establish_membership(): void
    {
        $listingId = $this->seedTickerAndListing();
        $this->insertMembership($listingId, [
            'source_authority_class' => 'OPERATOR_ENTERED',
            'operator_name' => 'ops.analyst',
            'reason_code' => 'SECTOR_RECLASSIFICATION',
            'source_ref' => 'idx-announcement-2026-04',
        ]);

        $context = (new SectorClassificationRepository())
            ->resolveSectorContextForTickerIds([1], '2026-04-10', null, '2026-04-11 00:00:00')[1];

        $this->assertSame('G', $context['sector_code'], 'the class is conditional, not forbidden');
        $this->assertSame('RESOLVED_AUTHORITATIVE', $context['resolution_state']);
        $this->assertSame('OPERATOR_ENTERED', $context['source_authority_class']);
        $this->assertNull($context['resolution_reason_code']);
    }

    /**
     * An exchange-authoritative row is unaffected. Without this the previous test would also pass on
     * an implementation that simply refused every operator row, which is a different rule.
     */
    public function test_an_exchange_authoritative_row_needs_no_operator_or_reason(): void
    {
        $listingId = $this->seedTickerAndListing();
        $this->insertMembership($listingId, [
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'operator_name' => null,
            'reason_code' => null,
            'source_ref' => 'idx-classification-2026',
        ]);

        $context = (new SectorClassificationRepository())
            ->resolveSectorContextForTickerIds([1], '2026-04-10', null, '2026-04-11 00:00:00')[1];

        $this->assertSame('G', $context['sector_code']);
        $this->assertSame('RESOLVED_AUTHORITATIVE', $context['resolution_state']);
    }

    /**
     * The two refusals are different facts and must not collapse into one reason code. A third-party
     * restatement may never establish membership at all; an operator row may, once governed.
     */
    public function test_a_derived_reference_row_is_refused_under_its_own_reason(): void
    {
        $listingId = $this->seedTickerAndListing();
        $this->insertMembership($listingId, [
            'source_authority_class' => 'DERIVED_REFERENCE',
            'source_name' => 'vendor',
            'source_ref' => 'vendor-restatement-2026',
        ]);

        $context = (new SectorClassificationRepository())
            ->resolveSectorContextForTickerIds([1], '2026-04-10', null, '2026-04-11 00:00:00')[1];

        $this->assertSame('UNKNOWN', $context['sector_code']);
        $this->assertSame('SECTOR_MEMBERSHIP_UNKNOWN', $context['resolution_reason_code']);
    }

    /**
     * A refused operator row must not be treated as absent either: if a governed row covers the same
     * date, that one resolves, and the refusal must not hide it.
     */
    public function test_a_governed_row_still_resolves_when_an_ungoverned_row_covers_the_same_date(): void
    {
        $listingId = $this->seedTickerAndListing();
        $this->insertMembership($listingId, [
            'source_authority_class' => 'OPERATOR_ENTERED',
            'sector_code' => 'I',
            'operator_name' => null,
            'reason_code' => null,
            'recorded_at' => '2026-04-01 09:00:00',
        ]);
        $this->insertMembership($listingId, [
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'sector_code' => 'G',
            'source_ref' => 'idx-classification-2026',
            'recorded_at' => '2026-04-02 09:00:00',
        ]);

        $context = (new SectorClassificationRepository())
            ->resolveSectorContextForTickerIds([1], '2026-04-10', null, '2026-04-11 00:00:00')[1];

        $this->assertSame('G', $context['sector_code'], 'the ungoverned row must be ignored, not counted as a conflict');
        $this->assertSame('RESOLVED_AUTHORITATIVE', $context['resolution_state']);
    }

    private function insertMembership(int $listingId, array $override = []): void
    {
        DB::table('ticker_sector_memberships')->insert($override + [
            'ticker_id' => 1,
            'listing_id' => $listingId,
            'sector_code' => 'G',
            'classification_system' => 'IDX-IC',
            'effective_from' => '2026-01-02',
            'effective_to' => null,
            'source_name' => 'idx',
            'source_ref' => 'idx-classification-2026',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'recorded_at' => '2026-01-02 09:00:00',
            'operator_name' => null,
            'reason_code' => null,
        ]);
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
