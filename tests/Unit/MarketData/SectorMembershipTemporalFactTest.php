<?php

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W05 / MD-B05 — sector membership as a governed point-in-time fact.
 *
 * Owner contract:
 *   docs/market_data/authority/strategy/book/Sector_Classification_Contract_LOCKED.md
 *
 * The contract places sector classification inside market-data scope as a governed exchange fact of
 * the same kind as listing, board, and trading status; names `IDX-IC` sourced from IDX as the only
 * authoritative system; binds each membership to stable identity, a named system, an interval,
 * a source with an authority class, and a known-time coordinate; and makes reclassification an
 * append that closes the prior interval without editing it.
 *
 * Its dependency order clause is the reason this work sits at `W05` rather than with the indicators
 * that consume it: temporal membership is a prerequisite for every sector-relative measure, and
 * `W16` may consume the governed state but may not be the first point at which membership becomes
 * temporal. That sequencing claim is asserted here rather than assumed from the stage register.
 *
 * `SectorClassificationRepositoryTest` covers reclassification, overlap, and as-known ordering, and
 * `SectorSourceAuthorityClassResolutionTest` covers the conditional operator class. This suite
 * covers the record shape, the system authority, the scope boundary, and the sequencing.
 */
class SectorMembershipTemporalFactTest extends TestCase
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

    /** The membership record binds every element the contract lists, identity first. */
    public function test_a_membership_record_binds_stable_identity_system_interval_source_and_known_time(): void
    {
        foreach ([
            'listing_id',               // stable identity, never ticker text alone
            'sector_code',
            'classification_system',
            'effective_from',
            'effective_to',
            'source_name',
            'source_ref',
            'source_authority_class',
            'recorded_at',              // known time for as-known replay
            'supersedes_membership_id', // reclassification appends rather than edits
        ] as $column) {
            $this->assertTrue(
                Schema::hasColumn('ticker_sector_memberships', $column),
                'ticker_sector_memberships.'.$column.' is missing'
            );
        }

        $listingId = $this->seedListing();
        $membershipId = (new SectorClassificationRepository())->appendMembership(
            $listingId, 1, 'G', '2024-01-02', null, 'idx', 'idx-classification-2024', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2024-01-02 09:00:00'
        );

        $row = DB::table('ticker_sector_memberships')->where('membership_id', $membershipId)->first();
        $this->assertSame($listingId, (int) $row->listing_id, 'membership binds the stable listing, not the code');
        $this->assertSame('IDX-IC', (string) $row->classification_system);
        $this->assertSame('idx-classification-2024', (string) $row->source_ref, 'the source reference is retained');
        $this->assertSame('EXCHANGE_AUTHORITATIVE', (string) $row->source_authority_class);
        $this->assertSame('2024-01-02 09:00:00', (string) $row->recorded_at);
    }

    /** `IDX-IC` is the only system that may be written, and no other may be stored under its name. */
    public function test_no_other_classification_system_may_be_stored(): void
    {
        $listingId = $this->seedListing();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SECTOR_CLASSIFICATION_SYSTEM_UNSUPPORTED: GICS.');

        (new SectorClassificationRepository())->appendMembership(
            $listingId, 1, 'G', '2024-01-02', null, 'idx', 'idx-ref', 'GICS',
            'EXCHANGE_AUTHORITATIVE', '2024-01-02 09:00:00'
        );
    }

    /**
     * A sector code that is not in the governed taxonomy cannot be introduced by a membership write.
     * Storing a foreign taxonomy's code under the `IDX-IC` name is the same defect as naming the
     * system outright, and it is the easier one to do by accident.
     */
    public function test_a_code_outside_the_governed_taxonomy_cannot_enter_under_the_idx_ic_name(): void
    {
        $listingId = $this->seedListing();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SECTOR_CODE_UNKNOWN');

        (new SectorClassificationRepository())->appendMembership(
            $listingId, 1, '40', '2024-01-02', null, 'idx', 'idx-ref', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2024-01-02 09:00:00'
        );
    }

    /**
     * The interval convention for membership is inclusive on both ends, and the reclassification
     * boundary is asserted on each side. The prior row is untouched; the closure is a new revision.
     */
    public function test_the_membership_interval_end_is_inclusive_and_the_reclassification_boundary_holds(): void
    {
        $listingId = $this->seedListing();
        $repository = new SectorClassificationRepository();

        $original = $repository->appendMembership(
            $listingId, 1, 'G', '2023-01-02', null, 'idx', 'idx-initial', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2023-01-02 09:00:00'
        );
        $repository->appendMembership(
            $listingId, 1, 'I', '2025-07-01', null, 'idx', 'idx-reclassification', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2025-06-20 09:00:00'
        );

        $this->assertSame([1 => 'G'], $repository->resolveSectorCodesForTickerIds([1], '2025-06-30', null, '2025-07-02 00:00:00'));
        $this->assertSame([1 => 'I'], $repository->resolveSectorCodesForTickerIds([1], '2025-07-01', null, '2025-07-02 00:00:00'));

        $prior = DB::table('ticker_sector_memberships')->where('membership_id', $original)->first();
        $this->assertNull($prior->effective_to, 'the original fact is never edited');
        $closure = DB::table('ticker_sector_memberships')->where('supersedes_membership_id', $original)->first();
        $this->assertSame('2025-06-30', (string) $closure->effective_to, 'the closing revision ends the day before');
    }

    /**
     * An instrument with no covering interval resolves `UNKNOWN` — not a default sector, and not the
     * sector it holds today. The second half is the one that matters: a later membership must not
     * reach backwards.
     */
    public function test_an_uncovered_date_resolves_unknown_rather_than_the_current_sector(): void
    {
        $listingId = $this->seedListing();
        (new SectorClassificationRepository())->appendMembership(
            $listingId, 1, 'I', '2025-07-01', null, 'idx', 'idx-later', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2025-06-20 09:00:00'
        );

        $context = (new SectorClassificationRepository())
            ->resolveSectorContextForTickerIds([1], '2024-03-04', null, '2026-01-01 00:00:00')[1];

        $this->assertSame('UNKNOWN', $context['sector_code']);
        $this->assertSame('SECTOR_MEMBERSHIP_UNKNOWN', $context['resolution_reason_code']);
        $this->assertNull($context['sector_membership_id'], 'no membership was borrowed to fill the gap');
    }

    /**
     * Scope: from the intentional dataset start onward. A date before the boundary is not a sector
     * the resolver guesses at; the identity layer refuses the date outright and membership stays
     * unresolved.
     */
    public function test_a_date_before_the_dataset_start_resolves_no_membership(): void
    {
        $scope = MarketDataScope::fromConfig();
        $before = date('Y-m-d', strtotime($scope->datasetStart().' -1 day'));

        $listingId = $this->seedListing();
        (new SectorClassificationRepository())->appendMembership(
            $listingId, 1, 'G', $scope->datasetStart(), null, 'idx', 'idx-initial', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', $scope->datasetStart().' 09:00:00'
        );

        $this->assertSame(
            [1 => 'UNKNOWN'],
            (new SectorClassificationRepository())->resolveSectorCodesForTickerIds([1], $before, null, '2026-01-01 00:00:00')
        );

        $this->expectException(\Throwable::class);
        (new TemporalIdentityRepository())->readProjectedUniverseAsOf($before);
    }

    /**
     * Dependency order: membership is already temporal at this stage, which is what makes it a
     * prerequisite rather than a later retrofit. The assertion is on the resolution surface — a
     * resolver that takes a trade date and a knowledge cutoff, and a table that stores intervals —
     * because "temporal at W05" is a property of the mechanism, not of a document saying so.
     */
    public function test_membership_is_temporal_at_this_stage_rather_than_first_at_the_consuming_stage(): void
    {
        $reflection = new ReflectionMethod(SectorClassificationRepository::class, 'resolveSectorContextForTickerIds');
        $parameters = array_map(function (ReflectionParameter $parameter) {
            return $parameter->getName();
        }, $reflection->getParameters());

        $this->assertSame(['tickerIds', 'tradeDate', 'classificationSystem', 'knownAt'], $parameters,
            'resolution takes both time coordinates');

        // And the sector-consuming indicator surface binds the membership revision it used rather
        // than re-deriving a current sector at read time.
        $this->assertTrue(
            Schema::hasColumn('eod_indicators', 'sector_membership_id'),
            'the consumer binds the membership revision, which requires membership to be temporal first'
        );
    }

    /**
     * Owning the classification does not transfer sector policy. Ordering sectors by attractiveness
     * stays downstream, so no market-data surface may name that ordering.
     */
    public function test_no_market_data_surface_ranks_or_weights_sectors(): void
    {
        $offenders = [];
        $scanned = 0;
        $pattern = '/sector[_A-Za-z]{0,12}('.'rotation_rank|attractive|overweight|underweight|preferred_sector|sector_score'.')/i';

        foreach ($this->phpFiles(dirname(__DIR__, 3).'/app') as $file) {
            $scanned++;
            $source = (string) file_get_contents($file);
            if (preg_match($pattern, $source, $match)) {
                $offenders[] = basename($file).' :: '.$match[0];
            }
        }

        $this->assertGreaterThan(50, $scanned, 'the application scan must reach the market-data surface');
        $this->assertSame(1, preg_match($pattern, 'sector_rotation_rank'), 'the pattern must be able to fire');
        $this->assertSame([], $offenders, 'sector ordering by attractiveness is downstream policy');
    }

    /** @return array<int,string> */
    private function phpFiles(string $root): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function seedListing(): int
    {
        DB::table('tickers')->insert([
            'ticker_id' => 1, 'ticker_code' => 'BBCA', 'company_name' => 'Bank Central Asia',
            'listed_date' => '2023-01-02', 'is_active' => 1,
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection(['BBCA']);

        return (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
    }
}
