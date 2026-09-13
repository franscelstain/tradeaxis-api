<?php

use App\Application\MarketData\Services\AsKnownReplaySnapshotService;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * MD-B18-A002 -- as-known snapshots must be made from the revisions selected by the declared
 * cutoff, never from the live config that happens to be loaded while the replay runs.
 *
 * This class previously replaced five persistence repositories with mocks that branched on the
 * cutoff they were handed, which made the mock decide the knowledge-time question the predicate is
 * about: the identity, calendar and status assertions could not have failed however
 * `AsKnownReplaySnapshotService` treated the cutoff, and
 * `LifecycleProofIsNotMockedTest::test_db_backed_tests_only_mock_the_external_source_boundary`
 * failed on exactly that. `F-MD-B18-A002-001` records it. Every repository here is now real and the
 * early/late difference is produced by seeded rows with different `recorded_at` values.
 */
class B18AsKnownSnapshotIsolationTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const CONTRACT = 'docs/market_data/authority/strategy/backtest/Historical_Replay_and_Data_Quality_Backtest.md';

    private const TRADE_DATE = '2026-03-24';

    /** When the later revisions entered the record: between the two cutoffs under test. */
    private const LATE_RECORDED_AT = '2026-05-01 09:00:00';

    private $originalMarketDataConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->originalMarketDataConfig = config('market_data');
    }

    protected function tearDown(): void
    {
        config(['market_data' => $this->originalMarketDataConfig]);
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /** @return array<string,string> contract token => behavioural guard that executes it */
    private function laterRevisionGuardMap(): array
    {
        return [
            'master' => 'AsKnownReplayBoundaryTest::test_identity_recorded_after_the_cutoff_is_invisible',
            'event' => 'AsKnownReplayBoundaryTest::test_a_corporate_action_recorded_after_the_cutoff_is_invisible',
            'status' => 'AsKnownReplayBoundaryTest::test_a_status_revision_recorded_after_the_cutoff_is_invisible',
            'calendar' => 'AsKnownReplayBoundaryTest::test_a_calendar_revision_recorded_after_the_cutoff_is_invisible',
            'config' => 'AsKnownReplayBoundaryTest::test_a_configuration_recorded_after_the_cutoff_is_invisible_and_none_is_created',
            'formula' => 'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
            'factor' => 'B18AsKnownSnapshotIsolationTest::test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot',
        ];
    }

    /**
     * The MD-S003 list is the source of the root set. Adding a later-revision kind without a guard,
     * or retaining a guard after the contract stops naming it, fails instead of drifting silently.
     */
    public function test_every_later_revision_kind_is_bound_to_an_executing_guard(): void
    {
        $path = dirname(__DIR__, 3).'/'.self::CONTRACT;
        $this->assertFileExists($path);
        $source = (string) file_get_contents($path);
        $this->assertSame(1, preg_match(
            '/later ([^;]+) revisions are invisible before their recorded\/known times;/',
            $source,
            $match
        ), 'MD-S003 later-revision scenario line moved; re-read it rather than weakening this map');

        $contractKinds = explode(',', preg_replace('/,?\s+and\s+/', ',', $match[1]));
        $contractKinds = array_values(array_filter(array_map('trim', $contractKinds)));
        $mappedKinds = array_keys($this->laterRevisionGuardMap());
        sort($contractKinds);
        sort($mappedKinds);
        $this->assertSame($contractKinds, $mappedKinds);
        $this->assertCount(7, $mappedKinds);

        $missing = [];
        foreach ($this->laterRevisionGuardMap() as $kind => $guard) {
            [$class, $method] = explode('::', $guard);
            $file = __DIR__.'/'.$class.'.php';
            if (! is_file($file) || strpos((string) file_get_contents($file), 'function '.$method.'(') === false) {
                $missing[] = $kind.' -> '.$guard;
            }
        }
        $this->assertSame([], $missing, 'these later-revision kinds have no executing guard');
    }

    /**
     * A later cutoff exposes later master, event, status, calendar, config, formula, and factor
     * revisions. Re-running the earlier cutoff still returns the identical bound snapshot and
     * performs no writes.
     *
     * Every root below is resolved by its real repository from rows whose only distinguishing
     * property is when they were recorded, so the cutoff comparison happens inside the code under
     * test rather than inside the fixture.
     */
    public function test_a_later_cutoff_exposes_later_revisions_without_rewriting_the_earlier_snapshot(): void
    {
        $earlyCutoff = '2026-04-15 00:00:00';
        $lateCutoff = '2026-06-15 00:00:00';
        $configs = new MarketDataConfigSnapshotRepository();

        config(['market_data.indicators.roc_lookback_days' => 20]);
        Carbon::setTestNow('2026-03-25 10:00:00');
        $earlyConfig = $configs->resolveForRun(self::TRADE_DATE);

        config(['market_data.indicators.roc_lookback_days' => 21]);
        Carbon::setTestNow(self::LATE_RECORDED_AT);
        $lateConfig = $configs->resolveForRun(self::TRADE_DATE);

        $earlyListing = $this->seedIdentity();
        $this->seedCalendar();
        $this->seedStatus($earlyListing);

        $earlyEvent = $this->event('early-event', 1, '2026-03-25 09:00:00');
        $lateEvent = $this->event('late-event', 1, self::LATE_RECORDED_AT);
        $earlyFactorSet = $this->factorSet('early-factor', (int) $earlyConfig['config_snapshot_id'], '2026-03-25 09:00:00');
        $lateFactorSet = $this->factorSet('late-factor', (int) $lateConfig['config_snapshot_id'], self::LATE_RECORDED_AT);
        $this->factor($earlyFactorSet, $earlyEvent);
        $this->factor($lateFactorSet, $lateEvent);

        $service = $this->service($configs);
        $countsBefore = $this->boundRowCounts();

        $earlyFirst = $service->capture(self::TRADE_DATE, $earlyCutoff);
        $late = $service->capture(self::TRADE_DATE, $lateCutoff);
        $earlyAgain = $service->capture(self::TRADE_DATE, $earlyCutoff);

        // Config, formula and factor: the snapshot the cutoff selects, and the indicator config
        // frozen inside it.
        $this->assertSame((int) $earlyConfig['config_snapshot_id'], $earlyFirst['config_snapshot_id']);
        $this->assertSame(20, $earlyFirst['formula_registry_identity']['indicator_config']['roc_lookback_days']);
        $this->assertSame((int) $lateConfig['config_snapshot_id'], $late['config_snapshot_id']);
        $this->assertSame(21, $late['formula_registry_identity']['indicator_config']['roc_lookback_days']);

        // Master identity: the listing recorded after the early cutoff is not in the early
        // universe and is in the late one.
        $earlyCodes = array_column($earlyFirst['temporal_universe'], 'ticker_code');
        $lateCodes = array_column($late['temporal_universe'], 'ticker_code');
        $this->assertSame(['EARLY'], $earlyCodes,
            'a listing recorded in May cannot be in a universe read as known in April');
        $this->assertContains('LATE', $lateCodes,
            'and the later cutoff must expose it, or the cutoff is a wall rather than a filter');
        $this->assertContains('EARLY', $lateCodes);

        // Calendar: two revisions for the date, the later superseding the earlier.
        $this->assertSame('calendar-early', $earlyFirst['calendar_context']['revision_uid']);
        $this->assertSame('calendar-late', $late['calendar_context']['revision_uid']);

        // Status: a suspension on record since 2023, closed before the trade date by a revision
        // recorded in May. The early cutoff still sees it in force; the later one does not.
        $this->assertSame('SUSPENSION', $earlyFirst['trading_status_contexts'][$earlyListing]['status_code']);
        $this->assertNotSame('SUSPENSION', $late['trading_status_contexts'][$earlyListing]['status_code'],
            'the later-recorded revision closed the suspension before this trade date');

        // Event and factor: one of each known at the early cutoff, two at the later one.
        $this->assertCount(1, $earlyFirst['event_factor_context']['corporate_action_revisions']);
        $this->assertCount(1, $earlyFirst['event_factor_context']['factor_sets']);
        $this->assertCount(1, $earlyFirst['event_factor_context']['factors']);
        $this->assertCount(2, $late['event_factor_context']['corporate_action_revisions']);
        $this->assertCount(2, $late['event_factor_context']['factor_sets']);
        $this->assertCount(2, $late['event_factor_context']['factors']);

        $this->assertSame($earlyFirst['snapshot_hash'], $earlyAgain['snapshot_hash'],
            'later-known revisions must not rewrite the artifact produced for the earlier cutoff');
        $this->assertNotSame($earlyFirst['snapshot_hash'], $late['snapshot_hash'],
            'a declared later cutoff that exposes new revisions must produce a distinct snapshot');
        $this->assertSame($countsBefore, $this->boundRowCounts(),
            'as-known capture is a read: neither cutoff may create or rewrite bound-input rows');
    }

    /** A missing recorded payload is BLOCKED; the live config is never an implicit substitute. */
    public function test_an_incomplete_historical_config_snapshot_is_refused_instead_of_using_live_config(): void
    {
        config(['market_data.indicators.roc_lookback_days' => 99]);

        // The unusable payload is written into the real snapshot row rather than returned by a
        // mocked repository, so the refusal is the service reading a row it genuinely cannot use.
        $configs = new MarketDataConfigSnapshotRepository();
        Carbon::setTestNow('2026-03-25 10:00:00');
        $resolved = $configs->resolveForRun(self::TRADE_DATE);
        DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $resolved['config_snapshot_id'])
            ->update(['resolved_config_json' => '{}']);

        $this->seedIdentity();
        $this->seedCalendar();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('REPLAY_CONFIG_SNAPSHOT_PAYLOAD_INVALID');

        $this->service($configs)->capture(self::TRADE_DATE, '2026-04-15 00:00:00');
    }

    /**
     * Real repositories throughout.
     *
     * These were five mocks, and `LifecycleProofIsNotMockedTest` was right to reject them: a mock
     * that branches on the cutoff it is handed makes the knowledge-time decision itself, so the
     * assertions read back the fixture rather than the behaviour of the service.
     */
    private function service(MarketDataConfigSnapshotRepository $configs): AsKnownReplaySnapshotService
    {
        return new AsKnownReplaySnapshotService(
            new TemporalIdentityRepository(),
            new MarketCalendarRepository(),
            new TemporalTradingStatusRepository(),
            $configs,
            new SourceObservationRepository()
        );
    }

    /**
     * Two listings: one on record since the dataset start, one recorded after the early cutoff. The
     * later cutoff is the only thing that can bring the second into the universe.
     *
     * @return int the listing id of the early one
     */
    private function seedIdentity(): int
    {
        $early = $this->listing(1, 'EARLY', '2023-01-02 00:00:00');
        $this->listing(2, 'LATE', self::LATE_RECORDED_AT);

        return $early;
    }

    private function listing(int $n, string $symbol, string $recordedAt): int
    {
        $issuerId = (int) DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'ISO-ISSUER-'.$n, 'legal_name' => 'Issuer '.$n,
            'source_ref' => 'fixture', 'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $instrumentId = (int) DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'ISO-INSTRUMENT-'.$n, 'issuer_id' => $issuerId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR', 'source_ref' => 'fixture',
            'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        $listingId = (int) DB::table('md_listings')->insertGetId([
            'listing_uid' => 'ISO-LISTING-'.$n, 'legacy_ticker_id' => 700 + $n,
            'instrument_id' => $instrumentId, 'exchange_code' => 'IDX', 'market_segment' => 'REGULAR',
            'board_code' => 'MAIN', 'listed_date' => '2023-01-02', 'delisted_date' => null,
            'listing_state' => 'LISTED', 'source_ref' => 'fixture',
            'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
        DB::table('md_listing_symbols')->insert([
            'listing_id' => $listingId, 'symbol' => $symbol, 'symbol_type' => 'EXCHANGE',
            'symbol_namespace' => 'IDX', 'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => $recordedAt, 'source_ref' => 'fixture', 'change_reason' => 'SYMBOL_CHANGE',
        ]);
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'MAIN',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => $recordedAt, 'source_ref' => 'fixture', 'change_reason' => 'BOARD_MOVEMENT',
        ]);

        return $listingId;
    }

    /**
     * Two calendar revisions for the trade date, the later superseding the earlier. Which one the
     * repository returns is decided by the cutoff against `recorded_at`, inside the repository.
     */
    private function seedCalendar(): void
    {
        $earlyId = (int) DB::table('md_market_calendar_revisions')->insertGetId(
            $this->calendarRow('calendar-early', '2023-01-05 00:00:00', null)
        );
        DB::table('md_market_calendar_revisions')->insert(
            $this->calendarRow('calendar-late', self::LATE_RECORDED_AT, $earlyId)
        );
    }

    /** @return array<string,mixed> */
    private function calendarRow(string $uid, string $recordedAt, ?int $supersedes): array
    {
        return [
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
        ];
    }

    /**
     * A suspension on record since 2023, and the later-recorded revision that closes its interval
     * before the trade date. The early cutoff can only see the first.
     */
    private function seedStatus(int $listingId): void
    {
        $observationId = (int) DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'iso-status-observation'),
            'attempt_uid' => 'iso-as-known', 'requested_trade_date' => self::TRADE_DATE,
            'source_mode' => 'authority_document', 'source_name' => 'IDX', 'provider' => 'IDX',
            'sanitized_request_identity' => 'https://www.idx.co.id/notice',
            'response_status' => 200, 'content_type' => 'application/json',
            'acquired_at' => '2023-01-05 00:00:00', 'adapter_version' => 'test-v1',
            'payload_hash' => str_repeat('d', 64), 'outcome_state' => 'ACCEPTED',
            'created_at' => '2023-01-05 00:00:00',
        ]);

        $instrumentId = (int) DB::table('md_listings')->where('listing_id', $listingId)->value('instrument_id');
        $openId = (int) DB::table('md_trading_status_revisions')->insertGetId(
            $this->statusRow($listingId, $instrumentId, $observationId, 'iso-suspension', null, '2023-01-05 00:00:00', null)
        );
        DB::table('md_trading_status_revisions')->insert(
            $this->statusRow($listingId, $instrumentId, $observationId, 'iso-lift', '2026-03-01 00:00:00', self::LATE_RECORDED_AT, $openId)
        );
    }

    /** @return array<string,mixed> */
    private function statusRow(int $listingId, int $instrumentId, int $observationId, string $uid, ?string $effectiveTo, string $recordedAt, ?int $supersedes): array
    {
        return [
            'listing_id' => $listingId, 'instrument_id' => $instrumentId,
            'status_event_uid' => hash('sha256', $uid), 'status_type_code' => 'SUSPENDED',
            'status_code' => 'SUSPENSION', 'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => 'MAIN', 'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'source_name' => 'IDX_OFFICIAL', 'source_payload_hash' => str_repeat('d', 64),
            'verification_state' => 'VERIFIED', 'full_session_verified' => 1,
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => $effectiveTo,
            'recorded_at' => $recordedAt, 'supersedes_revision_id' => $supersedes,
            'source_observation_id' => $observationId, 'source_ref' => 'https://www.idx.co.id/notice',
            'observed_at' => $recordedAt, 'announced_at' => $recordedAt,
        ];
    }

    private function event(string $uid, int $revision, string $recordedAt): int
    {
        return (int) DB::table('md_corporate_action_revisions')->insertGetId([
            'event_uid' => $uid,
            'revision_number' => $revision,
            'listing_id' => 700 + $revision,
            'action_type_code' => 'STOCK_SPLIT',
            'lifecycle_state' => 'EFFECTIVE',
            'verification_state' => 'AUTHORITATIVE_VERIFIED',
            'ex_date' => '2026-03-20',
            'effective_at' => '2026-03-20 00:00:00',
            'recorded_at' => $recordedAt,
        ]);
    }

    private function factorSet(string $uid, int $configSnapshotId, string $recordedAt): int
    {
        return (int) DB::table('md_adjustment_factor_sets')->insertGetId([
            'factor_set_uid' => hash('sha256', $uid),
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'factor_formula_version' => 'structural_factor_product_v2',
            'config_snapshot_id' => $configSnapshotId,
            'state' => 'BOUND',
            'content_hash' => hash('sha256', $uid.'-content'),
            'recorded_at' => $recordedAt,
            'created_at' => $recordedAt,
        ]);
    }

    private function factor(int $factorSetId, int $eventId): void
    {
        DB::table('md_adjustment_factors')->insert([
            'factor_set_id' => $factorSetId,
            'listing_id' => 700 + $eventId,
            'effective_from' => '2026-03-20',
            'effective_to' => null,
            'price_factor' => 0.5,
            'volume_factor' => 2.0,
            'corporate_action_revision_id' => $eventId,
            'created_at' => '2026-03-20 00:00:00',
        ]);
    }

    private function boundRowCounts(): array
    {
        return [
            'config_snapshots' => (int) DB::table('md_config_snapshots')->count(),
            'events' => (int) DB::table('md_corporate_action_revisions')->count(),
            'factor_sets' => (int) DB::table('md_adjustment_factor_sets')->count(),
            'factors' => (int) DB::table('md_adjustment_factors')->count(),
        ];
    }
}
