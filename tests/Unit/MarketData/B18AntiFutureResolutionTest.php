<?php

use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B18-A002` -- `MD-S050-R0017`, the anti-future list.
 *
 * > Replay must not use today's `is_active`, current symbol, current sector, current
 * > suspension/status, latest calendar correction, later corporate-action revision, later factor,
 * > current config, or latest provider mapping unless that exact revision was frozen/known in the
 * > selected mode.
 *
 * Nine named items. Seven already had executing guards spread across
 * `B18AntiSurvivorshipFixtureCorpusTest`, `AsKnownReplayBoundaryTest` and
 * `B18AsKnownSnapshotIsolationTest`. Two did not: **current sector** and **latest provider
 * mapping** were covered only by `AsKnownReplayBoundaryTest::test_every_temporal_root_accepts_a_knowledge_cutoff`,
 * which reflects over the method signature and asserts a cutoff parameter exists. A parameter that
 * is accepted and ignored passes that check.
 *
 * So this class executes those two, and then binds all nine to their guards through a map checked
 * against the contract sentence itself, so an item added to `MD-S050` with nothing behind it fails
 * rather than leaving the list quietly short.
 */
class B18AntiFutureResolutionTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-24';

    /** Between the two recordings: the later revision is not yet knowable here. */
    private const CUTOFF_BEFORE_LATER_REVISION = '2026-04-01 00:00:00';

    /** When the later revision entered the record. */
    private const LATER_RECORDED_AT = '2026-05-01 00:00:00';

    private const TICKER_ID = 8801;

    /** @var int */
    private $listingId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->listingId = $this->seedListing();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /**
     * The nine items `MD-S050` forbids replay from taking from current state, each bound to a guard
     * that executes the prohibition.
     *
     * @return array<string,string>
     */
    private function antiFutureMap(): array
    {
        return [
            "today's `is_active`" =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_listing_active_at_T_but_delisted_today_stays_in_the_historical_universe',
            'current symbol' =>
                'B18AntiSurvivorshipFixtureCorpusTest::test_a_symbol_change_resolves_to_the_symbol_and_mapping_effective_on_the_trade_date',
            'current sector' =>
                'B18AntiFutureResolutionTest::test_a_sector_reclassification_recorded_later_is_invisible_at_the_earlier_cutoff',
            'current suspension/status' =>
                'AsKnownReplayBoundaryTest::test_a_status_revision_recorded_after_the_cutoff_is_invisible',
            'latest calendar correction' =>
                'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'later corporate-action revision' =>
                'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
            'later factor' =>
                'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
            'current config' =>
                'AsKnownReplayBoundaryTest::test_a_configuration_recorded_after_the_cutoff_is_invisible_and_none_is_created',
            'latest provider mapping' =>
                'B18AntiFutureResolutionTest::test_a_provider_remapping_recorded_later_is_invisible_at_the_earlier_cutoff',
        ];
    }

    /**
     * `MD-S050-R0017` -- the map and the contract must forbid the same things.
     */
    public function test_the_anti_future_map_names_exactly_what_the_contract_names(): void
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/strategy/book/Replay_Verification_Contract_LOCKED.md';
        $this->assertFileExists($path);

        $this->assertSame(1, preg_match(
            '/Replay must not use (.+?) unless that exact revision was frozen\/known in the selected mode\./s',
            (string) file_get_contents($path),
            $match
        ), 'the MD-S050 anti-future sentence moved; re-read it rather than weakening this map');

        $items = preg_split('/,\s*or\s+|,\s*/', trim($match[1]));
        $items = array_values(array_filter(array_map('trim', $items)));
        $mapped = array_keys($this->antiFutureMap());
        sort($items);
        sort($mapped);

        $this->assertSame($items, $mapped,
            'MD-S050 and the anti-future map disagree about what replay may not take from current '
                .'state');
    }

    /**
     * Every guard the map names must exist, or the map is a list of intentions.
     */
    public function test_every_anti_future_guard_exists_and_is_executable(): void
    {
        $missing = [];

        foreach ($this->antiFutureMap() as $item => $ref) {
            [$class, $method] = explode('::', $ref);
            $file = __DIR__.'/'.$class.'.php';
            if (! is_file($file)) {
                $missing[] = $item.' -> '.$class.' (file not found)';

                continue;
            }
            if (strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                $missing[] = $item.' -> '.$ref;
            }
        }

        $this->assertSame([], $missing, 'these anti-future items name a guard that no longer exists');
    }

    // ---- current sector ------------------------------------------------------------------------

    /**
     * `MD-S050-R0017` -- current sector.
     *
     * The listing was classified `A1` from the dataset start. A reclassification to `B2`, effective
     * from the same date, was recorded in May. A replay reading as known in April must still say
     * `A1`: the reclassification had not been learned yet, and a backtest that saw `B2` would be
     * grouping the instrument by a fact from its own future.
     */
    public function test_a_sector_reclassification_recorded_later_is_invisible_at_the_earlier_cutoff(): void
    {
        $this->seedSectorMemberships();

        $repository = new SectorClassificationRepository();

        $asKnown = $repository->resolveSectorCodesForTickerIds([self::TICKER_ID], self::TRADE_DATE, null, self::CUTOFF_BEFORE_LATER_REVISION);
        $today = $repository->resolveSectorCodesForTickerIds([self::TICKER_ID], self::TRADE_DATE);

        $this->assertSame('A1', $asKnown[self::TICKER_ID],
            'a reclassification recorded in May cannot be known to a replay reading as of April');
        $this->assertSame('B2', $today[self::TICKER_ID],
            'and it does resolve without a cutoff, so the fixture is real and the cutoff is a '
                .'filter rather than a wall');
    }

    // ---- latest provider mapping ---------------------------------------------------------------

    /**
     * `MD-S050-R0017` -- latest provider mapping.
     *
     * The provider symbol was `ANTIF.JK` on record from the dataset start. A remapping to
     * `ANTIF.KL`, effective over the same interval, was recorded in May. Acquisition replayed as
     * known in April must request the symbol it would have requested then; using the later mapping
     * would reconstruct a request the platform could not have made.
     */
    public function test_a_provider_remapping_recorded_later_is_invisible_at_the_earlier_cutoff(): void
    {
        $this->seedProviderMappings();

        $repository = new TemporalIdentityRepository();

        $asKnown = $repository->resolveProviderContext('ANTIF', 'yahoo_finance', self::TRADE_DATE, self::CUTOFF_BEFORE_LATER_REVISION);
        $today = $repository->resolveProviderContext('ANTIF', 'yahoo_finance', self::TRADE_DATE);

        $this->assertNotNull($asKnown, 'the mapping on record in April must still resolve');
        $this->assertSame('ANTIF.JK', $asKnown['provider_symbol'],
            'a remapping recorded in May cannot be used by a replay reading as of April');
        $this->assertSame('ANTIF.KL', $today['provider_symbol'],
            'and the later mapping does win without a cutoff, so the cutoff is what excludes it');
        $this->assertSame((int) $asKnown['listing_id'], (int) $today['listing_id'],
            'both resolve the same listing; only the provider symbol moved');
    }

    // ---- fixtures ------------------------------------------------------------------------------

    private function seedListing(): int
    {
        $issuerId = (int) DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'AF-ISSUER', 'legal_name' => 'Anti Future Tbk',
            'source_ref' => 'fixture', 'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ]);
        $instrumentId = (int) DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'AF-INSTRUMENT', 'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR', 'source_ref' => 'fixture',
            'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ]);
        $listingId = (int) DB::table('md_listings')->insertGetId([
            'listing_uid' => 'AF-LISTING', 'legacy_ticker_id' => self::TICKER_ID,
            'instrument_id' => $instrumentId, 'exchange_code' => 'IDX', 'market_segment' => 'REGULAR',
            'board_code' => 'MAIN', 'listed_date' => '2023-01-02', 'delisted_date' => null,
            'listing_state' => 'LISTED', 'source_ref' => 'fixture',
            'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ]);
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => 'ANTIF', 'symbol_type' => 'EXCHANGE',
            'symbol_namespace' => 'IDX', 'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => '2023-01-02 00:00:00', 'source_ref' => 'fixture', 'change_reason' => 'SYMBOL_CHANGE',
        ]);
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => '2023-01-02 00:00:00', 'source_ref' => 'fixture', 'change_reason' => 'BOARD_MOVEMENT',
        ]);

        return $listingId;
    }

    /**
     * Two authoritative memberships over the same effective interval, learned at different times.
     * Effective time cannot separate them; only knowledge time can.
     */
    private function seedSectorMemberships(): void
    {
        $firstId = (int) DB::table('ticker_sector_memberships')->insertGetId([
            'ticker_id' => self::TICKER_ID, 'listing_id' => $this->listingId,
            'sector_code' => 'A1', 'classification_system' => 'IDX-IC',
            'effective_from' => '2023-01-02', 'effective_to' => null,
            'source_name' => 'IDX', 'source_ref' => 'https://www.idx.co.id/sector',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'recorded_at' => '2023-01-02 00:00:00', 'supersedes_membership_id' => null,
        ]);

        DB::table('ticker_sector_memberships')->insert([
            'ticker_id' => self::TICKER_ID, 'listing_id' => $this->listingId,
            'sector_code' => 'B2', 'classification_system' => 'IDX-IC',
            'effective_from' => '2023-01-02', 'effective_to' => null,
            'source_name' => 'IDX', 'source_ref' => 'https://www.idx.co.id/sector',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'recorded_at' => self::LATER_RECORDED_AT, 'supersedes_membership_id' => $firstId,
        ]);
    }

    /**
     * A remapping the way this schema expresses one: the original mapping is retracted at the
     * moment the replacement was learned, and the replacement is recorded then.
     *
     * Two live mappings over one trade date is not a remapping -- the resolver refuses it as
     * PROVIDER_SYMBOL_MAPPING_AMBIGUOUS, which is the fail-closed rule MD-S050 requires and was
     * the first shape this fixture took. Both knowledge-time filters are therefore exercised:
     * `recorded_at` hides the replacement from the earlier cutoff, and `retracted_at` keeps the
     * original visible to it while removing it from an uncut read.
     */
    private function seedProviderMappings(): void
    {
        $rows = [
            ['ANTIF.JK', '2023-01-02 00:00:00', self::LATER_RECORDED_AT],
            ['ANTIF.KL', self::LATER_RECORDED_AT, null],
        ];

        foreach ($rows as [$symbol, $recordedAt, $retractedAt]) {
            DB::table('md_provider_symbol_mappings')->insert([
                'listing_id' => $this->listingId, 'provider' => 'yahoo_finance',
                'provider_symbol' => $symbol, 'effective_from' => '2023-01-02 00:00:00',
                'effective_to' => null, 'recorded_at' => $recordedAt, 'retracted_at' => $retractedAt,
                'mapping_revision' => 'temporal_provider_mapping_v1', 'source_ref' => 'fixture',
            ]);
        }
    }
}
