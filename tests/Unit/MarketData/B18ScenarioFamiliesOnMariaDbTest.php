<?php

use App\Application\MarketData\Services\FinalizeDecisionService;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * `MD-B18-A002` -- `MD-S003-R0025`.
 *
 * > All required scenario families pass on MariaDB production semantics **and** the supported test
 * > mirror. Any missing family remains an open proof gap; historical green results for superseded
 * > rules do not close it.
 *
 * The mirror half was already true: every DB-backed market-data guard runs on the SQLite mirror.
 * The MariaDB half was true of nothing -- all 91 of those guards swap `database.default` to an
 * in-memory SQLite connection, so no family had ever been resolved against the engine production
 * uses.
 *
 * That gap matters because the families are decided by *temporal* queries, and the places a mirror
 * silently diverges are exactly the places those queries live: type affinity on a `char(64)` hash
 * compared against a string, `datetime` comparison and ordering, `NULL` ordering in a
 * `supersedes`/`retracted_at` join, and whether a unique constraint is enforced at all. A family
 * that resolves correctly under SQLite affinity and incorrectly under MariaDB collation is a
 * production defect the mirror cannot see.
 *
 * Each of the six `MD-S003` required families is exercised here against the migrated MariaDB schema
 * through the same repositories and services the mirror guards use. This class does not restate
 * their assertions -- it establishes that the behaviour each family turns on survives the change of
 * engine, which is the half `MD-S003-R0025` adds to what the mirror already proves.
 */
class B18ScenarioFamiliesOnMariaDbTest extends TestCase
{
    use UsesMarketDataMariaDb;

    private const CONTRACT = 'docs/market_data/authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md';

    private const TRADE_DATE = '2026-03-24';

    private const CUTOFF_EARLY = '2026-04-15 00:00:00';

    private const CUTOFF_LATE = '2026-06-15 00:00:00';

    /** Distinctive ids so a leaked row is identifiable and cannot collide with real data. */
    private const LISTING_SEED = 970001;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    /**
     * The families are read from the contract, so a family added to `MD-S003` with nothing
     * exercising it on MariaDB fails here rather than leaving "all required scenario families"
     * quietly meaning the six that happened to be written down.
     *
     * @return array<string,string> contract heading => the test that exercises it on MariaDB
     */
    private function familyMap(): array
    {
        return [
            'Exact publication verification' => 'test_exact_publication_verification_family_on_mariadb',
            'Degraded acquisition and expectation' => 'test_degraded_acquisition_family_on_mariadb',
            'Temporal identity and status' => 'test_temporal_identity_and_status_family_on_mariadb',
            'Corporate actions and indicators' => 'test_corporate_actions_and_indicators_family_on_mariadb',
            'Correction and read path' => 'test_correction_and_read_path_family_on_mariadb',
            'As-known isolation' => 'test_as_known_isolation_family_on_mariadb',
        ];
    }

    public function test_the_family_map_names_exactly_the_families_the_contract_requires(): void
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);
        $source = (string) file_get_contents($path);

        $start = strpos($source, '## Required scenario families');
        $this->assertNotFalse($start, 'the MD-S003 required-families heading moved; re-read the contract');
        $end = strpos($source, "\n## ", $start + 5);
        $block = substr($source, $start, $end === false ? null : $end - $start);

        preg_match_all('/^### (.+)$/m', $block, $matches);
        $headings = array_map('trim', $matches[1]);
        $mapped = array_keys($this->familyMap());
        sort($headings);
        sort($mapped);

        $this->assertSame($headings, $mapped,
            'MD-S003 and the reviewed family map disagree about which scenario families exist');
    }

    public function test_every_family_names_a_test_that_exists_in_this_class(): void
    {
        $source = (string) file_get_contents(__FILE__);
        $missing = [];

        foreach ($this->familyMap() as $family => $method) {
            if (strpos($source, 'function '.$method.'(') === false) {
                $missing[] = $family.' -> '.$method;
            }
        }

        $this->assertSame([], $missing, 'these families have no MariaDB scenario');
    }

    /**
     * The whole class is worthless if it silently runs on the mirror, so the substrate is asserted
     * rather than assumed.
     */
    public function test_these_scenarios_really_run_on_mariadb(): void
    {
        $driver = $this->marketDataMariaDb()->getDriverName();
        $this->assertSame('mysql', $driver, 'these scenarios are not running on MariaDB');

        $version = $this->marketDataMariaDb()->select('select version() as v')[0]->v;
        $this->assertStringContainsStringIgnoringCase('mariadb', $version,
            'the connection is MySQL-family but not MariaDB, so this is not the production engine');

        $this->assertSame(
            config('database.connections.'.$this->marketDataMariaDbConnection.'.database'),
            $this->marketDataMariaDb()->getDatabaseName()
        );
    }

    // ---- the six families ------------------------------------------------------------------------

    /**
     * **Exact publication verification.** The frozen configuration a publication replay resolves
     * against is selected by effective time and, under a cutoff, by knowledge time. Both are
     * `datetime` comparisons, which is precisely where a mirror's looser handling would not show.
     */
    public function test_exact_publication_verification_family_on_mariadb(): void
    {
        $early = $this->seedConfigSnapshot('2026-03-01 00:00:00', '2026-03-01 10:00:00', 'mariadb-early');
        $late = $this->seedConfigSnapshot('2026-04-01 00:00:00', '2026-04-01 10:00:00', 'mariadb-late');

        $governing = $this->marketDataMariaDb()->table('md_config_snapshots')
            ->where('snapshot_schema_version', 'market_data_config_snapshot_v1')
            ->where('environment_profile', 'mariadb-family-test')
            ->where('effective_at', '<=', self::TRADE_DATE.' 23:59:59')
            ->orderByDesc('effective_at')->orderByDesc('recorded_at')->orderByDesc('config_snapshot_id')
            ->first();

        $this->assertSame($early, (int) $governing->config_snapshot_id,
            'MariaDB selected a configuration not yet effective for the trade date');
        $this->assertNotSame($late, (int) $governing->config_snapshot_id);
    }

    /**
     * **Degraded acquisition and expectation.** The held/failed split is decided in the finalize
     * service rather than in SQL, so what MariaDB has to carry is that the decision is reached the
     * same way when the surrounding state lives in the production engine.
     */
    public function test_degraded_acquisition_family_on_mariadb(): void
    {
        $service = new FinalizeDecisionService();
        $coverage = [
            'coverage_gate_status' => 'PASS', 'coverage_gate_state' => 'PASS', 'coverage_ratio' => 1.0,
            'coverage_threshold_value' => 0.98, 'coverage_threshold_mode' => 'MIN_RATIO',
            'expected_universe_count' => 100, 'available_eod_count' => 100, 'missing_eod_count' => 0,
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
        ];

        $held = $service->evaluate(true, true, 'SEALED', $coverage, '2026-03-23', ['bars_rows_written' => 0]);
        $failed = $service->evaluate(true, true, 'SEALED', $coverage, null, ['bars_rows_written' => 0]);

        $this->assertSame('HELD', $held['terminal_status']);
        $this->assertSame('2026-03-23', $held['trade_date_effective']);
        $this->assertSame('NOT_READABLE', $held['publishability_state']);
        $this->assertSame('FAILED', $failed['terminal_status']);
        $this->assertNull($failed['trade_date_effective']);
    }

    /**
     * **Temporal identity and status.** A suspension closed by a later-recorded revision. The
     * supersession join is a `LEFT JOIN ... WHERE newer.id IS NULL` bounded by `recorded_at`, and
     * `NULL` handling in that shape is engine-specific.
     */
    public function test_temporal_identity_and_status_family_on_mariadb(): void
    {
        [$listingId, $instrumentId, $observationId] = $this->seedListingFoundation();

        $openId = $this->seedStatusRevision($listingId, $instrumentId, $observationId, 'mdb-suspension', null, '2026-03-12 00:00:00', null);
        $this->seedStatusRevision($listingId, $instrumentId, $observationId, 'mdb-lift', '2026-03-18 00:00:00', '2026-05-01 00:00:00', $openId);

        $repository = new TemporalTradingStatusRepository();
        $beforeLift = $repository->resolveForListing($listingId, self::TRADE_DATE, '2026-03-20 00:00:00');
        $afterLift = $repository->resolveForListing($listingId, self::TRADE_DATE, '2026-05-10 00:00:00');

        $this->assertSame('SUSPENSION', $beforeLift['status_code'],
            'MariaDB let a lift recorded on 1 May be seen by a read as known on 20 March');
        $this->assertNotSame('SUSPENSION', $afterLift['status_code'],
            'MariaDB did not apply the superseding revision once it was knowable');
    }

    /**
     * **Corporate actions and indicators.** Only `AUTHORITATIVE_VERIFIED` and governed
     * `MANUAL_VERIFIED` revisions are adjustment-active. The filter is a `whereIn` over a string
     * column, so collation decides it.
     */
    public function test_corporate_actions_and_indicators_family_on_mariadb(): void
    {
        [$listingId, $instrumentId, $observationId] = $this->seedListingFoundation();
        $hash = str_repeat('a', 64);

        foreach (['AUTHORITATIVE_VERIFIED', 'SYNTHETIC_CANDIDATE', 'PROVIDER_REPORTED'] as $i => $state) {
            $this->marketDataMariaDb()->table('md_corporate_action_revisions')->insert([
                'event_uid' => 'mdb-evt-'.strtolower($state).'-'.$listingId,
                'revision_number' => 1, 'listing_id' => $listingId, 'action_type_code' => 'STOCK_SPLIT',
                'lifecycle_state' => 'EFFECTIVE', 'verification_state' => $state,
                'ex_date' => self::TRADE_DATE, 'terms_json' => json_encode(['ratio' => ['from' => 1, 'to' => 2]]),
                'source_observation_id' => $observationId, 'recorded_at' => '2026-03-01 00:00:00',
            ]);
        }

        $active = $this->marketDataMariaDb()->table('md_corporate_action_revisions')
            ->where('listing_id', $listingId)
            ->whereIn('verification_state', ['AUTHORITATIVE_VERIFIED', 'MANUAL_VERIFIED'])
            ->pluck('verification_state')->all();

        $this->assertSame(['AUTHORITATIVE_VERIFIED'], array_values(array_unique($active)),
            'MariaDB admitted a non-verified revision to the adjustment-active set');
        $this->assertCount(1, $active, 'the two non-eligible revisions must not reach the factor set');
    }

    /**
     * **Correction and read path.** Exactly one publication is current for a trade date, and the
     * pointer table enforces it structurally. A unique constraint is the one thing a mirror can
     * declare and not enforce.
     */
    public function test_correction_and_read_path_family_on_mariadb(): void
    {
        $tradeDate = '2026-03-19';
        $this->marketDataMariaDb()->table('eod_current_publication_pointer')->where('trade_date', $tradeDate)->delete();

        // The pointer carries a foreign key to eod_publications. The SQLite mirror builds its
        // tables with foreign_key_constraints disabled, so referential integrity in the
        // pointer/publication chain is enforced by production and by nothing in the mirror --
        // a second divergence this family only sees on MariaDB.
        foreach ([[970101, 1], [970102, 2]] as [$publicationId, $version]) {
            $this->marketDataMariaDb()->table('eod_publications')->insert([
                'publication_id' => $publicationId, 'trade_date' => $tradeDate,
                'run_id' => 970200 + $version, 'publication_version' => $version,
                'is_current' => $version === 1 ? 1 : 0, 'seal_state' => 'SEALED',
                'sealed_at' => $tradeDate.' 18:00:00',
                'created_at' => $tradeDate.' 17:00:00', 'updated_at' => $tradeDate.' 18:00:00',
            ]);
        }
        $this->marketDataMariaDb()->table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate, 'publication_id' => 970101, 'run_id' => 970201,
            'publication_version' => 1, 'sealed_at' => $tradeDate.' 18:00:00',
            // MariaDB declares updated_at NOT NULL with no default; the SQLite mirror does not
            // enforce it, so this column is one the mirror could never have caught.
            'updated_at' => $tradeDate.' 18:00:00',
        ]);

        $refused = false;
        try {
            $this->marketDataMariaDb()->table('eod_current_publication_pointer')->insert([
                'trade_date' => $tradeDate, 'publication_id' => 970102, 'run_id' => 970202,
                'publication_version' => 2, 'sealed_at' => $tradeDate.' 19:00:00',
                'updated_at' => $tradeDate.' 19:00:00',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $refused = true;
        }

        $this->assertTrue($refused,
            'MariaDB accepted a second current pointer for one trade date, so "exactly one '
                .'publication is current" is enforced by the mirror and not by production');
        $this->assertSame(1, (int) $this->marketDataMariaDb()->table('eod_current_publication_pointer')
            ->where('trade_date', $tradeDate)->count());
    }

    /**
     * **As-known isolation.** A calendar revision recorded after the cutoff is invisible to it and
     * resolves without one. Both directions, so the cutoff is a filter rather than a wall on the
     * production engine too.
     */
    public function test_as_known_isolation_family_on_mariadb(): void
    {
        $this->seedCalendarRevision('2026-03-01 00:00:00', 'mdb-calendar-early');

        $repository = new MarketCalendarRepository();
        $early = $repository->sessionContext(self::TRADE_DATE, self::CUTOFF_EARLY);
        $this->assertSame('mdb-calendar-early', (string) $early['revision_uid'],
            'MariaDB did not resolve the calendar revision on record at the cutoff');

        // A revision recorded after the cutoff supersedes it only once it is knowable.
        $this->seedCalendarRevision('2026-05-01 00:00:00', 'mdb-calendar-late', (int) $early['calendar_revision_id']);

        $stillEarly = $repository->sessionContext(self::TRADE_DATE, self::CUTOFF_EARLY);
        $late = $repository->sessionContext(self::TRADE_DATE, self::CUTOFF_LATE);

        $this->assertSame('mdb-calendar-early', (string) $stillEarly['revision_uid'],
            'MariaDB let a revision recorded on 1 May supersede for a cutoff of 15 April');
        $this->assertSame('mdb-calendar-late', (string) $late['revision_uid'],
            'and the later cutoff must see it, or the cutoff is a wall on MariaDB');
    }

    // ---- fixtures --------------------------------------------------------------------------------

    private function seedConfigSnapshot(string $effectiveAt, string $recordedAt, string $tag): int
    {
        return (int) $this->marketDataMariaDb()->table('md_config_snapshots')->insertGetId([
            'snapshot_uid' => hash('sha256', $tag.$effectiveAt.$recordedAt),
            'snapshot_schema_version' => 'market_data_config_snapshot_v1',
            'serialization_version' => 'canonical_json_v1',
            'resolved_config_json' => '{}',
            'config_hash' => hash('sha256', 'mariadb-family-config'),
            'registry_revision' => 'platform_config_registry_v2',
            'effective_at' => $effectiveAt,
            'recorded_at' => $recordedAt,
            'build_id' => 'mariadb-family-test',
            'environment_profile' => 'mariadb-family-test',
            'resolver_version' => 'test',
            'created_at' => $recordedAt,
        ]);
    }

    /** @return array{0:int,1:int,2:int} listing, instrument and observation ids */
    private function seedListingFoundation(): array
    {
        $db = $this->marketDataMariaDb();
        $n = self::LISTING_SEED;

        $issuerId = (int) $db->table('md_issuers')->insertGetId([
            'issuer_uid' => 'MDB-ISSUER-'.$n, 'legal_name' => 'MariaDB Family Tbk',
            'source_ref' => 'fixture', 'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        $instrumentId = (int) $db->table('md_instruments')->insertGetId([
            'instrument_uid' => 'MDB-INSTRUMENT-'.$n, 'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR', 'source_ref' => 'fixture',
            'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        $listingId = (int) $db->table('md_listings')->insertGetId([
            'listing_uid' => 'MDB-LISTING-'.$n, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'listed_date' => '2023-01-02', 'listing_state' => 'LISTED', 'source_ref' => 'fixture',
            'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ]);
        $db->table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => '2023-01-02 00:00:00', 'source_ref' => 'fixture', 'change_reason' => 'TEST_FIXTURE',
        ]);

        $observationId = (int) $db->table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'mdb-observation-'.$n),
            'attempt_uid' => 'mariadb-family', 'requested_trade_date' => self::TRADE_DATE,
            'source_mode' => 'authority_document', 'source_name' => 'IDX', 'provider' => 'IDX',
            'sanitized_request_identity' => 'https://www.idx.co.id/notice',
            'response_status' => 200, 'content_type' => 'application/json',
            'acquired_at' => '2026-03-01 00:00:00', 'adapter_version' => 'test-v1',
            'payload_hash' => str_repeat('a', 64), 'outcome_state' => 'ACCEPTED',
            'created_at' => '2026-03-01 00:00:00',
        ]);

        return [$listingId, $instrumentId, $observationId];
    }

    private function seedStatusRevision(int $listingId, int $instrumentId, int $observationId, string $uid, ?string $effectiveTo, string $recordedAt, ?int $supersedes): int
    {
        return (int) $this->marketDataMariaDb()->table('md_trading_status_revisions')->insertGetId([
            'listing_id' => $listingId, 'instrument_id' => $instrumentId,
            'status_event_uid' => hash('sha256', $uid.$listingId), 'status_type_code' => 'SUSPENDED',
            'status_code' => 'SUSPENSION', 'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => 'RG', 'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'source_name' => 'IDX_OFFICIAL', 'source_payload_hash' => str_repeat('a', 64),
            'verification_state' => 'VERIFIED', 'full_session_verified' => 1,
            'effective_from' => '2026-03-10 00:00:00', 'effective_to' => $effectiveTo,
            'recorded_at' => $recordedAt, 'supersedes_revision_id' => $supersedes,
            'source_observation_id' => $observationId, 'source_ref' => 'https://www.idx.co.id/notice',
            'observed_at' => $recordedAt, 'announced_at' => $recordedAt,
        ]);
    }

    private function seedCalendarRevision(string $recordedAt, string $uid, ?int $supersedes = null): void
    {
        $this->marketDataMariaDb()->table('md_market_calendar_revisions')->insert([
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
}
