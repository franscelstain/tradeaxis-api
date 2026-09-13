<?php

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * `MD-B18-A002` — `MD-S050-R0056`.
 *
 * > Production relock requires executed publication and as-known fixtures, including all
 * > anti-survivorship cases above, on the actual production path.
 *
 * The eight anti-survivorship cases and the publication fixture all execute — but they execute on
 * the SQLite mirror, which is a hand-maintained definition of the intended schema rather than the
 * schema production runs. `B18ScenarioFamiliesOnMariaDbTest` established that the six `MD-S003`
 * scenario families survive the change of engine; this class does the same for the corpus `MD-S050`
 * names, because the sentence above asks for these fixtures specifically and asks for them on the
 * production path.
 *
 * Every one of these cases is decided by a temporal comparison — a `datetime` bound, a `NULL` in a
 * retraction or supersession column, a string compared under a collation — and those are exactly
 * the places a mirror diverges quietly. A survivorship filter that resolves correctly under SQLite
 * affinity and incorrectly under MariaDB is a production defect the mirror cannot see, and
 * survivorship bias is the one class of defect that makes a backtest look *better* rather than
 * broken.
 *
 * **What this class does not establish.** "Production path" here means the engine production runs
 * and the same repositories the pipeline calls, against the migrated schema. It does not mean the
 * production deployment: no fixture here runs against production data, and production relock is a
 * governance act rather than something a test performs. `MD-S050-R0056` is bound on the executable
 * half with that boundary named, not on a claim to have relocked anything.
 */
class B18ProductionPathReplayFixturesTest extends TestCase
{
    use UsesMarketDataMariaDb;

    private const CONTRACT = 'docs/market_data/authority/strategy/book/Replay_Verification_Contract_LOCKED.md';

    private const INTRODUCER = 'Required fixtures include:';

    /** Distinctive ids so a leaked row is identifiable and cannot collide with real data. */
    private const SEED = 990001;

    private const TRADE_DATE = '2026-03-24';

    /** A date with no observations in the shared testing database, so the manifest is only ours. */
    private const OBSERVATION_DATE = '2027-02-15';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();

        config()->set('market_data.tickers.table', 'tickers');
        config()->set('market_data.tickers.id_column', 'ticker_id');
        config()->set('market_data.tickers.code_column', 'ticker_code');
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    /**
     * The contract's own wording => the fixture that executes that case on the production engine.
     *
     * Read from `MD-S050` rather than transcribed, so a case added to the contract with nothing
     * exercising it on the production path fails here rather than leaving "all anti-survivorship
     * cases above" quietly meaning the eight that happened to be written down.
     *
     * @return array<string,string>
     */
    private function productionPathMap(): array
    {
        return [
            'a listing active at historical T but inactive today;' =>
                'test_a_delisting_recorded_later_is_invisible_to_an_earlier_cutoff_on_mariadb',
            'a symbol change and provider-symbol mapping transition;' =>
                'test_a_symbol_change_recorded_after_the_cutoff_is_invisible_on_mariadb',
            'symbol text reused by another listing;' =>
                'test_reused_symbol_text_resolves_to_the_holder_known_at_the_cutoff_on_mariadb',
            'a calendar/status fact corrected after T;' =>
                'test_a_calendar_revision_recorded_after_the_cutoff_is_invisible_on_mariadb',
            'a corporate action learned or verified later;' =>
                'test_a_corporate_action_recorded_after_the_cutoff_is_invisible_on_mariadb',
            'a configuration/formula change after T;' =>
                'test_a_configuration_recorded_after_the_cutoff_is_invisible_on_mariadb',
            'an original and corrected immutable publication; and' =>
                'test_a_correction_sealed_later_is_invisible_to_an_earlier_cutoff_on_mariadb',
            'a provider outage that cannot disappear through dormancy/current-universe filtering.' =>
                'test_a_provider_outage_survives_the_as_known_manifest_on_mariadb',
        ];
    }

    /** @return array<int,string> the eight numbered members, read from the frozen contract */
    private function contractCases(): array
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);
        $lines = preg_split('/\R/', (string) file_get_contents($path));

        $start = null;
        foreach ($lines as $i => $line) {
            if (trim($line) === self::INTRODUCER) {
                $start = $i;
                break;
            }
        }
        $this->assertNotNull($start, 'the required-fixtures introducer is no longer in MD-S050; this '
            .'guard is bound to text that moved and must be re-read, not relaxed');

        $cases = [];
        for ($i = $start + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^\d+\.\s+(.*)$/', $line, $m) !== 1) {
                break;
            }
            $cases[] = trim($m[1]);
        }

        return $cases;
    }

    public function test_the_production_path_corpus_covers_every_anti_survivorship_case(): void
    {
        $cases = $this->contractCases();
        $mapped = array_keys($this->productionPathMap());
        sort($cases);
        sort($mapped);

        $this->assertSame($cases, $mapped,
            'MD-S050-R0056 requires all anti-survivorship cases on the production path; a case with '
                .'no fixture here is one production relock would rest on the mirror for');
        $this->assertCount(8, $cases, 'MD-S050 no longer lists eight required fixtures');

        $source = (string) file_get_contents(__FILE__);
        $missing = [];
        foreach ($this->productionPathMap() as $case => $method) {
            if (strpos($source, 'function '.$method.'(') === false) {
                $missing[] = $case.' -> '.$method;
            }
        }
        $this->assertSame([], $missing, 'these cases have no production-path fixture');
    }

    /**
     * The whole class is worthless if it silently runs on the mirror: a corpus that claims the
     * production path and delivers SQLite would prove the opposite of what `MD-S050-R0056` asks.
     */
    public function test_these_fixtures_really_run_on_the_production_engine(): void
    {
        $this->assertSame('mysql', $this->marketDataMariaDb()->getDriverName(),
            'these fixtures are not running on the production engine');

        $version = $this->marketDataMariaDb()->select('select version() as v')[0]->v;
        $this->assertStringContainsStringIgnoringCase('mariadb', $version,
            'the connection is MySQL-family but not MariaDB, so this is not the production engine');

        $this->assertSame(
            config('database.connections.'.$this->marketDataMariaDbConnection.'.database'),
            $this->marketDataMariaDb()->getDatabaseName()
        );
    }

    // ---- the executed publication fixture ---------------------------------------------------------

    /**
     * `MD-S050-R0056` asks for an executed **publication** fixture as well as the as-known ones. The
     * publication question is the other half of replay: an explicit publication must resolve to
     * itself rather than to whatever the pointer currently names, or a replay of a superseded
     * publication silently verifies the correction that replaced it.
     */
    public function test_an_explicit_publication_resolves_to_itself_on_mariadb(): void
    {
        $this->seedPublicationPair();

        $repository = new EodEvidenceRepository();

        $original = $repository->resolvePublicationForEvidenceAudit([
            'type' => 'publication_id',
            'publication_id' => self::SEED + 1,
            'trade_date' => self::TRADE_DATE,
        ]);

        $this->assertNotNull($original, 'the superseded publication did not resolve on MariaDB');
        $this->assertSame(self::SEED + 1, (int) $original->publication_id,
            'an explicit publication selector resolved to a different publication');
        $this->assertSame(1, (int) $original->publication_version,
            'the pointer names version 2; resolving an explicit id must not follow it');
    }

    // ---- the eight anti-survivorship cases --------------------------------------------------------

    /**
     * **1. A listing active at historical T but inactive today.** The canonical survivorship case,
     * and the one the knowledge-time column was added for: a delisting removes a company from the
     * universe only once it was recorded.
     */
    public function test_a_delisting_recorded_later_is_invisible_to_an_earlier_cutoff_on_mariadb(): void
    {
        $listingId = $this->seedListing(1, 'PPDELIST', [
            'delisted_date' => '2025-06-30',
            'listing_state' => 'DELISTED',
            'delisted_recorded_at' => '2025-07-01 00:00:00',
        ]);
        $this->assertGreaterThan(0, $listingId);

        $repository = new TemporalIdentityRepository();
        $tradeDate = '2025-12-01';

        $before = array_column($repository->readProjectedUniverseAsOf($tradeDate, '2025-01-15 00:00:00'), 'ticker_code');
        $after = array_column($repository->readProjectedUniverseAsOf($tradeDate, '2025-08-01 00:00:00'), 'ticker_code');
        $today = array_column($repository->readProjectedUniverseAsOf($tradeDate), 'ticker_code');

        $this->assertContains('PPDELIST', $before,
            'MariaDB dropped a company from a January universe for a delisting recorded in July');
        $this->assertNotContains('PPDELIST', $after,
            'once the delisting is on record the same trade date must lose it');
        $this->assertNotContains('PPDELIST', $today,
            'and an uncut read agrees with the later cutoff, so the fixture is real');
    }

    /**
     * **2. A symbol change and provider-symbol mapping transition.** The rename takes effect in July
     * and is recorded in September; a read of an August trade date as known on 1 August still
     * resolves the old symbol, because the platform had not yet learned of the change.
     */
    public function test_a_symbol_change_recorded_after_the_cutoff_is_invisible_on_mariadb(): void
    {
        $listingId = $this->seedListing(2, 'PPOLDSYM');

        // The open interval as it stood before anyone knew it would close, retracted when the
        // rename was recorded, plus the closed replacement and the new symbol recorded with it.
        $this->retractSymbol($listingId, 'PPOLDSYM', '2024-09-01 00:00:00');
        $this->seedSymbol($listingId, 'PPOLDSYM', '2023-01-02 00:00:00', '2024-07-01 00:00:00', '2024-09-01 00:00:00');
        $this->seedSymbol($listingId, 'PPNEWSYM', '2024-07-01 00:00:00', null, '2024-09-01 00:00:00');

        // The provider-symbol mapping transitions with the rename, on the same knowledge times.
        $this->seedMapping($listingId, 'PPOLDSYM.JK', '2023-01-02 00:00:00', null, '2023-01-02 00:00:00');
        $this->retractMapping($listingId, 'PPOLDSYM.JK', '2024-09-01 00:00:00');
        $this->seedMapping($listingId, 'PPOLDSYM.JK', '2023-01-02 00:00:00', '2024-07-01 00:00:00', '2024-09-01 00:00:00');
        $this->seedMapping($listingId, 'PPNEWSYM.JK', '2024-07-01 00:00:00', null, '2024-09-01 00:00:00');

        $repository = new TemporalIdentityRepository();

        $asKnown = array_column($repository->readProjectedUniverseAsOf('2024-08-02', '2024-08-01 00:00:00'), 'ticker_code');
        $learned = array_column($repository->readProjectedUniverseAsOf('2024-08-02', '2024-10-01 00:00:00'), 'ticker_code');
        $today = array_column($repository->readProjectedUniverseAsOf('2024-08-02'), 'ticker_code');

        $this->assertContains('PPOLDSYM', $asKnown,
            'MariaDB applied a rename recorded on 1 September to a read as known on 1 August');
        $this->assertNotContains('PPNEWSYM', $asKnown,
            'the new symbol did not exist in the record at the cutoff and cannot resolve');

        // The retraction is what the third read turns on: the first two are decided by recorded_at
        // alone. A NULL in a retraction column compared against a datetime is exactly the shape a
        // mirror handles loosely, so it is the comparison worth resolving on the production engine.
        $this->assertNotContains('PPOLDSYM', $learned,
            'MariaDB still resolved the retracted interval for a cutoff that has learned the rename');
        $this->assertContains('PPNEWSYM', $learned,
            'and it must resolve the revision recorded in its place');

        $this->assertContains('PPNEWSYM', $today,
            'and without a cutoff the rename applies, so the cutoff is load-bearing');
        $this->assertNotContains('PPOLDSYM', $today,
            'an uncut read must not see the retracted interval either');

        // The mapping half of the case. A retracted mapping that keeps answering makes two provider
        // symbols claim one listing on one date, which the resolver refuses as ambiguous rather
        // than picking -- so this is also where the production engine's NULL handling shows.
        $repository = new TemporalIdentityRepository();
        $this->assertSame('PPOLDSYM.JK',
            $repository->resolveProviderContext('PPOLDSYM', 'yahoo_finance', '2024-08-02', '2024-08-01 00:00:00')['provider_symbol'],
            'as known on 1 August the provider still called this listing by its old symbol');
        $this->assertSame('PPNEWSYM.JK',
            $repository->resolveProviderContext('PPNEWSYM', 'yahoo_finance', '2024-08-02', '2024-10-01 00:00:00')['provider_symbol'],
            'MariaDB let the retracted mapping answer beside the one recorded in its place');
    }

    /**
     * **3. Symbol text reused by another listing.** The text moves from one company to another. A
     * universe keyed on symbol text silently swaps one company for another, and a cutoff before the
     * handover was recorded must still resolve the listing that held it.
     */
    public function test_reused_symbol_text_resolves_to_the_holder_known_at_the_cutoff_on_mariadb(): void
    {
        $first = $this->seedListing(3, 'PPREUSED');
        $this->seedMapping($first, 'PPREUSED.JK', '2023-01-02 00:00:00', null, '2023-01-02 00:00:00');
        $this->retractSymbol($first, 'PPREUSED', '2024-09-01 00:00:00');
        $this->seedSymbol($first, 'PPREUSED', '2023-01-02 00:00:00', '2024-07-01 00:00:00', '2024-09-01 00:00:00');
        $this->retractMapping($first, 'PPREUSED.JK', '2024-09-01 00:00:00');
        $this->seedMapping($first, 'PPREUSED.JK', '2023-01-02 00:00:00', '2024-07-01 00:00:00', '2024-09-01 00:00:00');

        $second = $this->seedListing(4, 'PPOTHER');
        $this->seedSymbol($second, 'PPREUSED', '2024-07-01 00:00:00', null, '2024-09-01 00:00:00');
        $this->seedMapping($second, 'PPREUSED.JK', '2024-07-01 00:00:00', null, '2024-09-01 00:00:00');

        $repository = new TemporalIdentityRepository();

        $asKnown = $repository->resolveProviderContext('PPREUSED', 'yahoo_finance', '2024-08-02', '2024-08-01 00:00:00');
        $this->assertNotNull($asKnown, 'the reused symbol resolved to nothing at the earlier cutoff');
        $this->assertSame($first, (int) $asKnown['listing_id'],
            'MariaDB handed the symbol to the new holder for a cutoff that predates the handover');

        $learned = $repository->resolveProviderContext('PPREUSED', 'yahoo_finance', '2024-08-02', '2024-10-01 00:00:00');
        $this->assertSame($second, (int) $learned['listing_id'],
            'MariaDB still resolved the retracted mapping for a cutoff that has learned the handover');

        $today = $repository->resolveProviderContext('PPREUSED', 'yahoo_finance', '2024-08-02');
        $this->assertSame($second, (int) $today['listing_id'],
            'and without a cutoff it resolves to the new holder, so the cutoff decides the answer');
    }

    /**
     * **4. A calendar/status fact corrected after T.** A revision recorded after the cutoff is
     * invisible to it and applies without one — the cutoff is a filter, not a wall.
     */
    public function test_a_calendar_revision_recorded_after_the_cutoff_is_invisible_on_mariadb(): void
    {
        $early = $this->seedCalendarRevision('2026-03-01 00:00:00', 'pp-calendar-early');

        $repository = new MarketCalendarRepository();
        $atCutoff = $repository->sessionContext(self::TRADE_DATE, '2026-04-15 00:00:00');
        $this->assertSame('pp-calendar-early', (string) $atCutoff['revision_uid'],
            'MariaDB did not resolve the calendar revision that was on record at the cutoff');

        $this->seedCalendarRevision('2026-05-01 00:00:00', 'pp-calendar-late', $early);

        $stillEarly = $repository->sessionContext(self::TRADE_DATE, '2026-04-15 00:00:00');
        $late = $repository->sessionContext(self::TRADE_DATE, '2026-06-15 00:00:00');

        $this->assertSame('pp-calendar-early', (string) $stillEarly['revision_uid'],
            'MariaDB let a correction recorded on 1 May supersede for a cutoff of 15 April');
        $this->assertSame('pp-calendar-late', (string) $late['revision_uid'],
            'and the later cutoff must see it, or the cutoff is a wall on the production engine');
    }

    /**
     * **5. A corporate action learned or verified later.** The widest-reaching leak in the real
     * corpus: actions entered months after the dates they apply to.
     */
    public function test_a_corporate_action_recorded_after_the_cutoff_is_invisible_on_mariadb(): void
    {
        $tickerId = self::SEED + 700;
        $this->marketDataMariaDb()->table('market_data_corporate_actions')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => 'PPLATECA',
            'action_date' => self::TRADE_DATE,
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'manual_corporate_action_csv',
            'recorded_at' => '2026-06-01 00:00:00',
            'created_at' => '2026-06-01 00:00:00',
        ]);

        $repository = new EventRiskSourceRepository();
        $asKnown = $repository->resolveEventRiskContextForTickerIds([$tickerId], self::TRADE_DATE, '2026-04-15 00:00:00');
        $today = $repository->resolveEventRiskContextForTickerIds([$tickerId], self::TRADE_DATE);

        $this->assertSame(0, (int) ($asKnown[$tickerId]['corporate_action_flag'] ?? 0),
            'MariaDB showed an action recorded in June to a read as known in April');
        $this->assertSame(1, (int) ($today[$tickerId]['corporate_action_flag'] ?? 0),
            'and it is visible without a cutoff, so the fixture is real');
    }

    /**
     * **6. A configuration/formula change after T.** A snapshot recorded after the cutoff cannot be
     * the configuration a replay resolves, or the replay is computed with a formula that did not
     * exist when the numbers it reproduces were produced.
     */
    public function test_a_configuration_recorded_after_the_cutoff_is_invisible_on_mariadb(): void
    {
        $early = $this->seedConfigSnapshot('2026-01-01 00:00:00', '2026-01-10 00:00:00', 'pp-config-early');

        // The uncut path resolves the governing snapshot and materialises one when nothing governs
        // the date, so the later revision is made by that call rather than seeded behind its back.
        $repository = new MarketDataConfigSnapshotRepository();
        $live = $repository->resolveForRun(self::TRADE_DATE);
        $this->marketDataMariaDb()->table('md_config_snapshots')
            ->where('config_snapshot_id', (int) $live['config_snapshot_id'])
            ->update(['effective_at' => '2026-03-01 00:00:00', 'recorded_at' => '2026-06-01 00:00:00']);

        $asKnown = $repository->resolveForRun(self::TRADE_DATE, '2026-04-15 00:00:00');
        $today = $repository->resolveForRun(self::TRADE_DATE);

        $this->assertSame($early, (int) $asKnown['config_snapshot_id'],
            'MariaDB resolved a configuration recorded on 1 June for a cutoff of 15 April');
        $this->assertSame(hash('sha256', 'pp-config-early'), (string) $asKnown['config_hash'],
            'the as-known read resolved a different configuration than the one on record at the cutoff');
        $this->assertSame((int) $live['config_snapshot_id'], (int) $today['config_snapshot_id'],
            'and the uncut read takes the later revision, so the cutoff is what decides');
    }

    /**
     * **7. An original and corrected immutable publication.** The original was sealed at 18:20 and
     * the correction at 19:20; a read as known at 18:30 must return the original, because at that
     * moment the correction was still a candidate and no reader could resolve it.
     */
    public function test_a_correction_sealed_later_is_invisible_to_an_earlier_cutoff_on_mariadb(): void
    {
        $this->seedPublicationPair();

        $repository = new EodEvidenceRepository();
        $before = $repository->resolvePublicationAsKnownAt(self::TRADE_DATE, self::TRADE_DATE.' 18:30:00');
        $after = $repository->resolvePublicationAsKnownAt(self::TRADE_DATE, self::TRADE_DATE.' 19:30:00');

        $this->assertNotNull($before, 'the original was sealed at 18:20 and must resolve at 18:30');
        $this->assertSame(self::SEED + 1, (int) $before->publication_id,
            'MariaDB returned a correction sealed at 19:20 as the publication a reader had at 18:30');
        $this->assertSame(self::SEED + 2, (int) $after->publication_id,
            'and once the correction is sealed it becomes the answer, so the cutoff decides');
    }

    /**
     * **8. A provider outage that cannot disappear through dormancy/current-universe filtering.** A
     * failed acquisition is a fact about the source, not about the universe: it must stay in the
     * manifest, because an outage that vanishes makes a gap in the data look like a company that
     * was never there.
     */
    public function test_a_provider_outage_survives_the_as_known_manifest_on_mariadb(): void
    {
        $this->seedOutageObservation();

        $manifest = (new SourceObservationRepository())
            ->observationManifestAsKnown(self::OBSERVATION_DATE, self::OBSERVATION_DATE.' 23:00:00');

        $this->assertSame(1, (int) $manifest['observation_count'],
            'the outage observation is missing from the as-known manifest on MariaDB');
        $this->assertSame('SOURCE_TIMEOUT', (string) $manifest['observations'][0]['reason_code'],
            'the outage was recorded without the reason that makes it an outage');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $manifest['manifest_hash']);

        $before = (new SourceObservationRepository())
            ->observationManifestAsKnown(self::OBSERVATION_DATE, self::OBSERVATION_DATE.' 12:00:00');
        $this->assertSame(0, (int) $before['observation_count'],
            'an acquisition attempted at 18:00 cannot be in a manifest as known at noon');
    }

    // ---- fixtures ---------------------------------------------------------------------------------

    /** Issuer, instrument, listing, exchange symbol and board — the identity foundation. */
    private function seedListing(int $n, string $symbol, array $override = []): int
    {
        $db = $this->marketDataMariaDb();
        $id = self::SEED + $n;

        $issuerId = (int) $db->table('md_issuers')->insertGetId([
            'issuer_uid' => 'PP-ISSUER-'.$id, 'legal_name' => 'Production Path Tbk '.$n,
            'source_ref' => 'fixture', 'recorded_at' => '2023-01-01 00:00:00',
            'created_at' => '2023-01-01 00:00:00',
        ]);
        $instrumentId = (int) $db->table('md_instruments')->insertGetId([
            'instrument_uid' => 'PP-INSTRUMENT-'.$id, 'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR', 'source_ref' => 'fixture',
            'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        $listingId = (int) $db->table('md_listings')->insertGetId(array_merge([
            'listing_uid' => 'PP-LISTING-'.$id, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'listed_date' => '2023-01-02', 'listing_state' => 'LISTED', 'source_ref' => 'fixture',
            'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ], $override));

        $this->seedSymbol($listingId, $symbol, '2023-01-02 00:00:00', null, '2023-01-02 00:00:00');
        $db->table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => '2023-01-02 00:00:00', 'source_ref' => 'fixture',
            'change_reason' => 'TEST_FIXTURE',
        ]);

        return $listingId;
    }

    private function seedSymbol(int $listingId, string $symbol, string $from, ?string $to, string $recordedAt): void
    {
        $this->marketDataMariaDb()->table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => $symbol, 'symbol_type' => 'EXCHANGE',
            'effective_from' => $from, 'effective_to' => $to, 'recorded_at' => $recordedAt,
            'source_ref' => 'fixture', 'change_reason' => 'TEST_FIXTURE',
        ]);
    }

    /**
     * The retraction is how the schema records that an interval was believed open and later learned
     * to have closed. Writing `effective_to` on the original row instead would backdate the
     * knowledge — it would make the platform look as though it had always known.
     */
    private function retractSymbol(int $listingId, string $symbol, string $retractedAt): void
    {
        $this->marketDataMariaDb()->table('md_listing_symbols')
            ->where('listing_id', $listingId)->where('symbol', $symbol)->whereNull('effective_to')
            ->update(['retracted_at' => $retractedAt]);
    }

    private function seedMapping(int $listingId, string $providerSymbol, string $from, ?string $to, string $recordedAt): void
    {
        $this->marketDataMariaDb()->table('md_provider_symbol_mappings')->insert([
            'listing_id' => $listingId, 'provider' => 'yahoo_finance',
            'provider_symbol' => $providerSymbol, 'effective_from' => $from, 'effective_to' => $to,
            'recorded_at' => $recordedAt, 'mapping_revision' => 'pp-map-'.$listingId,
            'source_ref' => 'fixture', 'change_reason' => 'TEST_FIXTURE',
        ]);
    }

    private function retractMapping(int $listingId, string $providerSymbol, string $retractedAt): void
    {
        $this->marketDataMariaDb()->table('md_provider_symbol_mappings')
            ->where('listing_id', $listingId)->where('provider_symbol', $providerSymbol)
            ->whereNull('effective_to')
            ->update(['retracted_at' => $retractedAt]);
    }

    /** An original sealed at 18:20 and the correction that supersedes it, sealed at 19:20. */
    private function seedPublicationPair(): void
    {
        $db = $this->marketDataMariaDb();
        $original = self::SEED + 1;
        $corrected = self::SEED + 2;

        $db->table('eod_current_publication_pointer')->where('trade_date', self::TRADE_DATE)->delete();

        foreach ([[$original, 1, null, self::TRADE_DATE.' 18:20:00'],
            [$corrected, 2, $original, self::TRADE_DATE.' 19:20:00']] as [$publicationId, $version, $supersedes, $sealedAt]) {
            $db->table('eod_publications')->insert([
                'publication_id' => $publicationId, 'trade_date' => self::TRADE_DATE,
                'run_id' => self::SEED + 100 + $version, 'publication_version' => $version,
                'is_current' => $version === 2 ? 1 : 0, 'seal_state' => 'SEALED',
                'supersedes_publication_id' => $supersedes,
                'previous_publication_id' => $supersedes,
                'replaced_publication_id' => $supersedes,
                'bars_batch_hash' => hash('sha256', 'pp-bars-'.$publicationId),
                'indicators_batch_hash' => hash('sha256', 'pp-indicators-'.$publicationId),
                'eligibility_batch_hash' => hash('sha256', 'pp-eligibility-'.$publicationId),
                'sealed_at' => $sealedAt,
                'created_at' => self::TRADE_DATE.' 17:00:00', 'updated_at' => $sealedAt,
            ]);

            // The audit resolver joins eod_runs, and rightly so: a publication whose run is absent
            // is not auditable evidence.
            $db->table('eod_runs')->insert([
                'run_id' => self::SEED + 100 + $version,
                'trade_date_requested' => self::TRADE_DATE,
                'trade_date_effective' => self::TRADE_DATE,
                'lifecycle_state' => 'COMPLETED', 'stage' => 'FINALIZE', 'source' => 'manual_file',
                'quality_gate_state' => 'PASS', 'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE', 'coverage_gate_state' => 'PASS',
                // Audit resolution refuses a publication whose coverage telemetry is
                // incomplete, so the fixture carries the whole context rather than the
                // columns the join happens to select.
                'coverage_universe_count' => 2, 'coverage_available_count' => 2,
                'coverage_missing_count' => 0, 'coverage_ratio' => '1.0000',
                'coverage_min_threshold' => '0.9800', 'coverage_threshold_mode' => 'MIN_RATIO',
                'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
                'coverage_contract_version' => 'coverage_gate_v1',
                'publication_id' => $publicationId, 'publication_version' => $version,
                'is_current_publication' => $version === 2 ? 1 : 0,
                'sealed_at' => $sealedAt,
                'started_at' => self::TRADE_DATE.' 17:00:00',
                'created_at' => self::TRADE_DATE.' 17:00:00', 'updated_at' => $sealedAt,
            ]);
        }

        $db->table('eod_current_publication_pointer')->insert([
            'trade_date' => self::TRADE_DATE, 'publication_id' => $corrected,
            'run_id' => self::SEED + 102, 'publication_version' => 2,
            'sealed_at' => self::TRADE_DATE.' 19:20:00',
            'updated_at' => self::TRADE_DATE.' 19:20:00',
        ]);
    }

    private function seedCalendarRevision(string $recordedAt, string $uid, ?int $supersedes = null): int
    {
        return (int) $this->marketDataMariaDb()->table('md_market_calendar_revisions')->insertGetId([
            'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => self::TRADE_DATE,
            'revision_uid' => $uid, 'timezone' => 'Asia/Jakarta',
            'is_trading_day' => 1, 'is_half_day' => 0, 'session_state' => 'COMPLETED',
            'session_open_at' => self::TRADE_DATE.' 09:00:00',
            'session_close_at' => self::TRADE_DATE.' 16:00:00',
            'completed_at' => self::TRADE_DATE.' 16:00:00', 'recorded_at' => $recordedAt,
            'supersedes_revision_id' => $supersedes,
            'source_ref' => 'https://www.idx.co.id/calendar', 'source_version' => 'idx-calendar-2026',
            'provenance_tier' => 'VERIFIED', 'reconciled_at' => self::TRADE_DATE,
            'reconciliation_source_ref' => 'https://www.idx.co.id/calendar',
        ]);
    }

    private function seedConfigSnapshot(string $effectiveAt, string $recordedAt, string $tag): int
    {
        return (int) $this->marketDataMariaDb()->table('md_config_snapshots')->insertGetId([
            'snapshot_uid' => hash('sha256', $tag),
            'snapshot_schema_version' => 'market_data_config_snapshot_v1',
            'serialization_version' => 'canonical_json_v1',
            'resolved_config_json' => '{}',
            'config_hash' => hash('sha256', $tag),
            'registry_revision' => 'platform_config_registry_v2',
            'effective_at' => $effectiveAt, 'recorded_at' => $recordedAt,
            'build_id' => 'production-path-test',
            // The resolver selects on the configured profile; a fixture with a profile of its own
            // would resolve nothing and the case would fail for the wrong reason.
            'environment_profile' => (string) config('market_data.governance.environment_profile', 'local'),
            'resolver_version' => 'test', 'created_at' => $recordedAt,
        ]);
    }

    /** A failed acquisition: no rows returned, and a reason code saying why. */
    private function seedOutageObservation(): void
    {
        $db = $this->marketDataMariaDb();
        $acquiredAt = self::OBSERVATION_DATE.' 18:00:00';

        $observationId = (int) $db->table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'pp-outage-'.self::SEED),
            'attempt_uid' => 'pp-outage', 'requested_trade_date' => self::OBSERVATION_DATE,
            'requested_start_date' => self::OBSERVATION_DATE, 'requested_end_date' => self::OBSERVATION_DATE,
            'source_mode' => 'api', 'source_name' => 'YAHOO_FINANCE', 'provider' => 'yahoo_finance',
            'provider_symbol' => 'PPOUTAGE.JK',
            'sanitized_request_identity' => 'fixture://PPOUTAGE.JK',
            'response_status' => null, 'content_type' => null,
            'acquired_at' => $acquiredAt, 'adapter_version' => 'adapter-v1',
            'payload_hash' => str_repeat('c', 64), 'outcome_state' => 'FAILED',
            'validation_state' => 'FAILED', 'reason_code' => 'SOURCE_TIMEOUT',
            'created_at' => $acquiredAt,
        ]);

        $this->assertGreaterThan(0, $observationId);
    }
}
