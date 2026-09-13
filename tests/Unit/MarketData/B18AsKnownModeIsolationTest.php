<?php

use App\Application\MarketData\Services\AsKnownReplayExecutionService;
use App\Application\MarketData\Services\AsKnownReplaySnapshotService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B18-A002` -- `MD-S050-R0005`.
 *
 * > As-known replay ... may differ from a historical publication when the selected cutoff, approved
 * > as-known configuration, or declared scenario differs. It creates new replay artifacts and never
 * > mutates or impersonates the original publication.
 *
 * `AsKnownReplayExecutionServiceTest` covers the *mutates* half: it runs the production
 * canonicalizer and asserts no row is added to `eod_runs`, `eod_publications` or `eod_bars`. What
 * had no guard is the *impersonates* half, which is a different failure. A run that writes nothing
 * can still produce a **result** that reads like a publication replay -- claiming a publication id,
 * carrying no cutoff, or judging itself against the publication's artifacts -- and such a result is
 * citable as point-in-time evidence it did not earn.
 *
 * The subject is therefore the verifier and the row it stores, not the canonicalizer. Every
 * repository and both as-known services are real: `AsKnownReplaySnapshotService` is `final` and
 * cannot be stubbed, and standing in for the persistence layer would mean asserting a fixture, so
 * this runs end to end against the SQLite mirror and reads the stored metric back.
 */
class B18AsKnownModeIsolationTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const RUN_ID = 9101;

    private const TICKER_ID = 501;

    private const TRADE_DATE = '2026-03-24';

    private const CUTOFF = '2026-04-15 00:00:00';

    /** @var array<string,mixed> */
    private $bound = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedWorld();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /**
     * `MD-S050-R0005` -- an as-known result never claims to be the publication.
     *
     * It records its own mode and the cutoff it was taken as of, and its `publication_id` is null.
     * A stored result naming a publication would be indistinguishable from a publication replay of
     * that publication, which is the impersonation the contract forbids.
     */
    public function test_an_as_known_result_claims_no_publication_and_records_its_own_mode(): void
    {
        $this->verifyAsKnown();
        $metric = $this->storedMetric();

        $this->assertSame('AS_KNOWN', $metric->replay_mode,
            'an as-known result must record its mode, or it is unclassified and citable as either');
        $this->assertSame(self::CUTOFF, (string) $metric->knowledge_cutoff_at,
            'and the cutoff it was taken as of, which is what makes it point-in-time evidence');
        $this->assertNull($metric->publication_id,
            'an as-known result claiming a publication id impersonates a publication replay of it');
        $this->assertSame('as_known_replay', (string) $metric->source,
            'the result names as-known execution as its source rather than a publication run');
    }

    /**
     * `MD-S050-R0005` -- new artifacts, and the original publication untouched.
     *
     * A sealed publication for the same trade date exists throughout. The as-known verification
     * must leave it, its pointer and its snapshot rows exactly as they were, while producing a
     * replay row of its own that did not exist before.
     */
    public function test_the_original_publication_is_untouched_and_a_new_replay_row_appears(): void
    {
        $before = $this->publicationFingerprint();
        $this->assertSame(0, (int) DB::table('md_replay_daily_metrics')->count());

        $this->verifyAsKnown();

        $this->assertSame($before, $this->publicationFingerprint(),
            'as-known replay altered the publication it was supposed to leave alone');
        $this->assertSame(1, (int) DB::table('md_replay_daily_metrics')->count(),
            'as-known replay must create its own artifact rather than only reading');
    }

    /**
     * `MD-S050-R0005` -- the comparison surface is the as-known artifacts.
     *
     * That is what "may differ from a historical publication" rests on: as-known is judged against
     * its own declared expectation, so differing from the publication is not a failure and cannot
     * become one.
     */
    public function test_the_comparison_surface_is_the_as_known_artifacts_and_not_the_publication(): void
    {
        $this->verifyAsKnown();

        $checked = json_decode((string) $this->storedMetric()->deterministic_fields_checked_json, true);

        $this->assertContains('as_known_snapshot_hash', $checked);
        $this->assertContains('canonical_output_hash', $checked);

        foreach (['bars_batch_hash', 'seal_state', 'publication_version', 'is_current_publication'] as $publicationField) {
            $this->assertNotContains($publicationField, $checked,
                'as-known replay compared itself against '.$publicationField.', a property of the '
                    .'publication; differing from the publication would then read as a failure');
        }
    }

    /**
     * The control. An as-known verification whose actual artifacts diverge from its own fixture is
     * a MISMATCH, so "may differ from a historical publication" is not "may differ from anything".
     */
    public function test_an_as_known_result_diverging_from_its_own_fixture_is_a_mismatch(): void
    {
        $this->verifyAsKnown(['snapshot_hash' => str_repeat('9', 64)]);
        $metric = $this->storedMetric();

        $this->assertSame('MISMATCH', (string) $metric->comparison_result);
        $this->assertSame('FAIL', (string) $metric->replay_status);

        $mismatches = json_decode((string) $metric->mismatches_json, true);
        $this->assertSame('as_known_snapshot_hash', $mismatches[0]['field'],
            'the divergence must name the as-known artifact that moved');
    }

    /**
     * A `PUBLICATION_EXACT` fixture handed to the as-known verifier is refused. Without this the
     * mode would be whatever the caller ran, and a publication fixture verified as-known would
     * produce an as-known-labelled result about publication artifacts.
     */
    public function test_a_publication_fixture_is_refused_by_the_as_known_verifier(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/REPLAY_MODE_MISMATCH/');

        $this->verifyAsKnown([], ['replay_mode' => 'PUBLICATION_EXACT']);
    }

    // ---- execution -------------------------------------------------------------------------------

    /**
     * @param array<string,mixed> $snapshotOverride perturbs what the fixture expects of the snapshot
     * @param array<string,mixed> $manifestOverride perturbs the fixture manifest
     */
    private function verifyAsKnown(array $snapshotOverride = [], array $manifestOverride = []): void
    {
        // What a real as-known run produces. Taken from the services themselves, because the fixture
        // is the record of an expectation and the proof is what happens when it is compared -- not
        // that these two values were typed correctly into a test.
        $snapshots = new AsKnownReplaySnapshotService();
        $snapshot = $snapshots->capture(self::TRADE_DATE, self::CUTOFF);
        $execution = app(AsKnownReplayExecutionService::class)->execute(self::TRADE_DATE, self::CUTOFF, $snapshot);

        $dir = $this->fixtureDir(
            array_merge([
                'trade_date' => self::TRADE_DATE,
                'knowledge_cutoff' => self::CUTOFF,
                'snapshot_hash' => $snapshot['snapshot_hash'],
            ], $snapshotOverride),
            [
                'execution_scope' => $execution['execution_scope'],
                'execution_state' => $execution['execution_state'],
                'canonical_row_count' => $execution['canonical_row_count'],
                'invalid_row_count' => $execution['invalid_row_count'],
                'canonical_output_hash' => $execution['canonical_output_hash'],
                'reason_code_counts' => $execution['reason_code_counts'] ?? [],
            ],
            $manifestOverride
        );

        (new ReplayVerificationService(
            new EodEvidenceRepository(),
            new EodPublicationRepository(),
            new ReplayResultRepository(),
            $snapshots,
            app(AsKnownReplayExecutionService::class)
        ))->verifyAsKnownAgainstFixture(self::RUN_ID, $dir, self::CUTOFF);
    }

    private function storedMetric()
    {
        $metric = DB::table('md_replay_daily_metrics')->orderByDesc('replay_id')->first();
        $this->assertNotNull($metric, 'the as-known verification persisted no result');

        return $metric;
    }

    /** Everything about the publication that a replay must not move. */
    private function publicationFingerprint(): string
    {
        return json_encode([
            'publications' => DB::table('eod_publications')->orderBy('publication_id')->get()->toArray(),
            'pointer' => DB::table('eod_current_publication_pointer')->orderBy('trade_date')->get()->toArray(),
            'bars_history' => DB::table('eod_bars_history')->orderBy('publication_id')->get()->toArray(),
            'runs' => DB::table('eod_runs')->orderBy('run_id')->get()->toArray(),
            'bars' => DB::table('eod_bars')->count(),
        ]);
    }

    // ---- fixture ---------------------------------------------------------------------------------

    /**
     * @param array<string,mixed> $snapshot
     * @param array<string,mixed> $execution
     * @param array<string,mixed> $manifestOverride
     */
    private function fixtureDir(array $snapshot, array $execution, array $manifestOverride = []): string
    {
        $manifest = array_merge([
            'fixture_id' => 'fixture_as_known_isolation',
            'fixture_family' => 'as_known_replay',
            'fixture_version' => 'v2',
            'fixture_schema_version' => 'replay_fixture_v2',
            'replay_mode' => 'AS_KNOWN',
            'assertion_scope' => AsKnownReplayExecutionService::EXECUTION_SCOPE,
            'knowledge_cutoff' => self::CUTOFF,
            'trade_date' => self::TRADE_DATE,
            'fixture_source' => 'unit_test',
            'fixture_created_at' => '2026-04-01T00:00:00+07:00',
        ], $manifestOverride);

        $dir = sys_get_temp_dir().'/md_b18_as_known_isolation_'.uniqid();
        mkdir($dir.'/expected', 0775, true);
        file_put_contents($dir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($dir.'/expected/as_known_snapshot.json', json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($dir.'/expected/as_known_execution.json', json_encode($execution, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $dir;
    }

    /**
     * A listing with one accepted observation on the trade date, a calendar revision, a config
     * snapshot, a run to verify against, and a sealed publication that must survive untouched.
     */
    private function seedWorld(): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => self::TICKER_ID, 'ticker_code' => 'TEST', 'company_name' => 'Test Tbk',
            'is_active' => 1, 'listed_date' => '2023-01-02', 'created_at' => '2023-01-02 00:00:00',
        ]);

        // Projects the legacy ticker into the temporal identity tables the snapshot reads.
        $identity = (new TemporalIdentityRepository())
            ->resolveProviderContext('TEST', 'yahoo_finance', self::TRADE_DATE, self::CUTOFF);

        DB::table('md_market_calendar_revisions')->insert([
            'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => self::TRADE_DATE,
            'revision_uid' => hash('sha256', 'as-known-isolation-calendar'), 'timezone' => 'Asia/Jakarta',
            'is_trading_day' => 1, 'is_half_day' => 0, 'session_state' => 'COMPLETED',
            'session_open_at' => self::TRADE_DATE.' 09:00:00', 'session_close_at' => self::TRADE_DATE.' 16:00:00',
            'completed_at' => self::TRADE_DATE.' 16:00:00', 'recorded_at' => self::TRADE_DATE.' 17:00:00',
            'source_ref' => 'https://www.idx.co.id/calendar', 'source_version' => 'idx-calendar-2026',
            'provenance_tier' => 'VERIFIED', 'reconciled_at' => self::TRADE_DATE,
            'reconciliation_source_ref' => 'https://www.idx.co.id/calendar',
        ]);

        $config = (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);
        DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $config['config_snapshot_id'])
            ->update(['recorded_at' => '2026-03-01 00:00:00']);

        $this->seedObservation($identity, (int) $config['config_snapshot_id']);
        $this->seedRunAndPublication();
    }

    /** @param array<string,mixed> $identity */
    private function seedObservation(array $identity, int $configSnapshotId): void
    {
        $common = [
            'run_id' => self::RUN_ID,
            'attempt_uid' => 'as-known-isolation',
            'acquisition_batch_id' => 'as-known-isolation',
            'requested_trade_date' => self::TRADE_DATE,
            'requested_start_date' => self::TRADE_DATE,
            'requested_end_date' => self::TRADE_DATE,
            'source_mode' => 'api', 'source_name' => 'YAHOO_FINANCE', 'provider' => 'yahoo_finance',
            'provider_symbol' => 'TEST.JK',
            'provider_mapping_id' => $identity['provider_mapping_id'],
            'mapping_revision' => $identity['mapping_revision'],
            'config_snapshot_id' => $configSnapshotId,
            'sanitized_request_identity' => 'fixture://TEST.JK/'.self::TRADE_DATE,
            'response_status' => 200, 'content_type' => 'application/json',
            'acquired_at' => self::TRADE_DATE.' 18:00:00',
            'provider_schema_version' => 'fixture-schema-v1',
            'schema_fingerprint' => str_repeat('a', 64),
            'adapter_version' => 'fixture-adapter-v1',
            'payload_hash' => str_repeat('b', 64),
            'payload_ref' => 'sha256:'.str_repeat('b', 64),
            'payload_byte_length' => 128,
            'created_at' => self::TRADE_DATE.' 18:00:00',
        ];

        $captureId = (int) DB::table('md_source_observations')->insertGetId($common + [
            'observation_uid' => hash('sha256', 'as-known-isolation-capture'),
            'bounded_payload_body' => '{}',
            'outcome_state' => 'CAPTURED', 'validation_state' => 'PENDING',
        ]);
        $observationId = (int) DB::table('md_source_observations')->insertGetId($common + [
            'observation_uid' => hash('sha256', 'as-known-isolation-accepted'),
            'parent_observation_id' => $captureId,
            'outcome_state' => 'ACCEPTED', 'validation_state' => 'PASSED',
        ]);

        $rowId = (int) DB::table('md_source_observation_rows')->insertGetId([
            'source_observation_id' => $observationId, 'capture_observation_id' => $captureId,
            'source_row_ref' => 'row-1', 'listing_id' => $identity['listing_id'],
            'provider' => 'yahoo_finance', 'provider_symbol' => 'TEST.JK',
            'provider_mapping_id' => $identity['provider_mapping_id'],
            'mapping_revision' => $identity['mapping_revision'], 'ticker_code' => 'TEST',
            'trade_date' => self::TRADE_DATE,
            'open_value' => '100', 'high_value' => '110', 'low_value' => '95',
            'close_value' => '105', 'volume_value' => '12345', 'adj_close_value' => null,
            'row_fingerprint' => str_repeat('c', 64),
            'created_at' => self::TRADE_DATE.' 18:00:00',
        ]);

        DB::table('md_source_observation_identity_bindings')->insert([
            'source_observation_row_id' => $rowId, 'source_observation_id' => $observationId,
            'listing_id' => $identity['listing_id'],
            'provider_mapping_id' => $identity['provider_mapping_id'],
            'mapping_revision' => $identity['mapping_revision'],
            'effective_trade_date' => self::TRADE_DATE,
            'recorded_at' => self::TRADE_DATE.' 18:00:00',
        ]);
    }

    /** The original publication the as-known replay must neither move nor impersonate. */
    private function seedRunAndPublication(): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => self::RUN_ID,
            'trade_date_requested' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
            'lifecycle_state' => 'COMPLETED', 'quality_gate_state' => 'PASS', 'stage' => 'FINALIZE',
            'source' => 'api', 'publication_id' => 77, 'publication_version' => 1,
            'terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS', 'is_current_publication' => 1,
            'sealed_at' => self::TRADE_DATE.' 18:30:00',
            'started_at' => self::TRADE_DATE.' 17:00:00',
            'created_at' => self::TRADE_DATE.' 17:00:00',
            'updated_at' => self::TRADE_DATE.' 18:30:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 77, 'trade_date' => self::TRADE_DATE, 'run_id' => self::RUN_ID,
            'publication_version' => 1, 'is_current' => 1, 'seal_state' => 'SEALED',
            'bars_batch_hash' => 'ORIGINAL-A1', 'sealed_at' => self::TRADE_DATE.' 18:30:00',
            'created_at' => self::TRADE_DATE.' 17:00:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => self::TRADE_DATE, 'publication_id' => 77, 'run_id' => self::RUN_ID,
            'publication_version' => 1, 'sealed_at' => self::TRADE_DATE.' 18:30:00',
        ]);

        DB::table('eod_bars_history')->insert([
            'publication_id' => 77, 'trade_date' => self::TRADE_DATE, 'ticker_id' => self::TICKER_ID,
            'open' => 100, 'high' => 110, 'low' => 95, 'close' => 105, 'volume' => 12345,
            'source' => 'YAHOO_FINANCE', 'run_id' => self::RUN_ID,
            'created_at' => self::TRADE_DATE.' 18:30:00',
        ]);
    }
}
