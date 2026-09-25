<?php

use App\Application\MarketData\Services\AsKnownReplayExecutionService;
use App\Application\MarketData\Services\AsKnownReplaySnapshotService;
use App\Application\MarketData\Services\MarketDataReadProductService;
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
     * `MD-S050-R0016` Gap B2 -- even a fixture that would otherwise match exactly is `BLOCKED`, not
     * `PASS`. `MD-S085` provides no historical reason-registry identity for any as-known replay to
     * bind, so this holds unconditionally; before this fix the same scenario reached `PASS`/`MATCH`
     * on a hash of two hardcoded constant arrays that never varied with anything.
     */
    public function test_an_as_known_replay_is_blocked_even_when_the_fixture_would_otherwise_match(): void
    {
        $this->verifyAsKnown();
        $metric = $this->storedMetric();

        $this->assertSame('BLOCKED', (string) $metric->replay_status,
            'a fully-matching fixture must not make an unavailable required input admissible');
        $this->assertSame('NOT_ADMISSIBLE', (string) $metric->comparison_result);
        $this->assertSame('NOT_ADMISSIBLE', (string) $metric->admission_state);
        $this->assertStringContainsString('REPLAY_REASON_REGISTRY_IDENTITY_UNAVAILABLE', (string) $metric->mismatch_summary,
            'the block must name the input that was unavailable');
    }

    /**
     * `MD-S050-R0016` Gap B1 (`D-MD-B18-A002-008`) -- AS_KNOWN's `read_model_version` binds the
     * versioned replay/read-product contract that renders the artifact. Before this it read a
     * configuration key that never existed and always recorded empty.
     */
    public function test_as_known_read_model_identity_binds_the_canonical_read_product_contract(): void
    {
        $this->verifyAsKnown();
        $metric = $this->storedMetric();

        $this->assertSame(
            MarketDataReadProductService::READ_MODEL_VERSION,
            (string) $metric->read_model_version,
            'AS_KNOWN must bind the canonical read-product contract identity, not resolve empty'
        );
        // Pinned to the literal `D-MD-B18-A002-008` itself names, independent of the production
        // constant: comparing only against `MarketDataReadProductService::READ_MODEL_VERSION`
        // would still pass if that constant's own value were wrong, since both sides of the
        // assertion above would move together.
        $this->assertSame(
            'market_data_read_product_v1',
            (string) $metric->read_model_version,
            'the canonical identity itself must be the one D-MD-B18-A002-008 names'
        );
    }

    /**
     * `D-MD-B18-A002-008` explicitly rejects binding this identity from configuration, including
     * any similarly-named runtime setting. Real runtime configuration is changed here, not a
     * fixture value, so a code path that still reads any of it would move this result. The exact
     * key AS_KNOWN previously read, `market_data.governance.read_model_version`, cannot be set at
     * all -- `PlatformConfigRegistry` refuses an unregistered key, itself confirming that key was
     * never a real configuration identity.
     *
     * Calls `AsKnownReplaySnapshotService::capture()` directly rather than through
     * `verifyAsKnown()`'s full canonicalizer run: that run re-validates the seeded run's already
     * -bound config snapshot against live config (`EodBarsIngestService::assertConsumedConfiguration()`)
     * and would reject this mutation as unrelated drift, not exercise the point being probed here.
     */
    public function test_as_known_read_model_identity_is_independent_of_runtime_configuration(): void
    {
        config([
            'market_data.governance.build_id' => 'PROBE-CONFIG-DERIVED-VALUE',
            'market_data.governance.config_serialization_version' => 'PROBE-CONFIG-DERIVED-VALUE',
        ]);

        $snapshot = (new AsKnownReplaySnapshotService())->capture(self::TRADE_DATE, self::CUTOFF);

        $this->assertSame(
            MarketDataReadProductService::READ_MODEL_VERSION,
            (string) $snapshot['read_model_version'],
            'changing runtime configuration must not change the read-model contract identity'
        );
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
     * `MD-S050-R0005` -- the comparison surface is the as-known artifacts, when a comparison runs
     * at all.
     *
     * Before `MD-S050-R0016` Gap B2, this was proven behaviourally: an as-known replay reached
     * `MATCH`/`PASS`, and `deterministic_fields_checked_json` on the stored row named the fields it
     * actually compared. Gap B2 made every as-known replay `BLOCKED` before comparison -- `MD-S085`
     * defines no historical reason-registry identity an as-known replay could legitimately bind, so
     * the comparison this test proved the surface of can no longer execute at all (see
     * `test_an_as_known_result_with_a_diverging_fixture_is_still_blocked_by_the_unavailable_reason_registry`
     * below). No conforming as-known scenario reaches this code, so this is now a static guard on
     * the field list `ReplayVerificationService::verifyAsKnownAgainstFixture()` names when that code
     * runs, rather than an executed one -- weaker, but honest about what it proves.
     */
    public function test_the_comparison_surface_field_list_names_as_known_artifacts_and_not_the_publication(): void
    {
        $source = file_get_contents(base_path('app/Application/MarketData/Services/ReplayVerificationService.php'));
        $this->assertNotFalse($source);

        $this->assertStringContainsString(
            "'deterministic_fields_checked_json' => json_encode(['as_known_snapshot_hash','canonical_output_hash','canonical_row_count','invalid_row_count','reason_code_counts'], JSON_UNESCAPED_SLASHES),",
            $source,
            'the as-known comparison field list must name the as-known artifacts it actually compares'
        );

        foreach (['bars_batch_hash', 'seal_state', 'publication_version', 'is_current_publication'] as $publicationField) {
            $this->assertStringNotContainsString(
                "'".$publicationField."'",
                $this->asKnownComparisonFieldListLiteral($source),
                'as-known replay must not compare itself against '.$publicationField.', a property of '
                    .'the publication; differing from the publication would then read as a failure'
            );
        }
    }

    private function asKnownComparisonFieldListLiteral(string $source): string
    {
        $start = strpos($source, "'deterministic_fields_checked_json' => json_encode([");
        $this->assertNotFalse($start, 'the as-known deterministic-fields-checked literal moved; re-locate it rather than weakening this guard');
        $end = strpos($source, '],', $start);

        return substr($source, $start, $end - $start);
    }

    /**
     * `MD-S050-R0016` Gap B2 -- an unavailable historical reason-registry identity blocks the
     * replay before any comparison, regardless of what else the fixture would have diverged on. The
     * prior version of this test (`test_an_as_known_result_diverging_from_its_own_fixture_is_a_mismatch`)
     * proved this same perturbed fixture reached `MISMATCH`/`FAIL`; that scenario no longer exists,
     * because `MD-S085` provides no historical reason-registry identity for any as-known replay to
     * bind, so every as-known replay is `BLOCKED` first. Forcing a `MISMATCH`/`FAIL` result here
     * would require fabricating a reason-registry identity Gap B2 exists to refuse -- not done.
     */
    public function test_an_as_known_result_with_a_diverging_fixture_is_still_blocked_by_the_unavailable_reason_registry(): void
    {
        $this->verifyAsKnown(['snapshot_hash' => str_repeat('9', 64)]);
        $metric = $this->storedMetric();

        $this->assertSame('BLOCKED', (string) $metric->replay_status,
            'an unavailable required input is BLOCKED regardless of what else in the fixture diverges');
        $this->assertSame('NOT_ADMISSIBLE', (string) $metric->comparison_result);
        $this->assertStringContainsString('REPLAY_REASON_REGISTRY_IDENTITY_UNAVAILABLE', (string) $metric->mismatch_summary);

        $mismatches = json_decode((string) $metric->mismatches_json, true);
        $this->assertSame([], $mismatches,
            'a comparison that never ran must not report a named mismatch, fabricated or otherwise');
    }

    /**
     * `MD-S050-R0016` Gap B2, the no-fallback half: mutating the *current* `eod_reason_codes`
     * registry -- the real table, not a fixture -- cannot make an as-known replay admissible.
     * `Platform_Config_Registry_LOCKED.md`: "Current registry state must never leak into
     * historical replay." If the block were somehow keyed off today's registry being empty or
     * absent, populating it richly would flip the verdict; it must not.
     */
    public function test_mutating_the_current_reason_registry_does_not_make_as_known_admissible(): void
    {
        DB::table('eod_reason_codes')->insert([
            'code' => 'PROBE_CURRENT_ONLY_CODE', 'category' => 'PROBE', 'description' => 'current-only probe row',
            'severity' => 'INFO', 'is_active' => 1,
        ]);

        $this->verifyAsKnown();
        $metric = $this->storedMetric();

        $this->assertSame('BLOCKED', (string) $metric->replay_status,
            'richer current registry content must not make a historical as-known replay admissible');
        $this->assertSame(
            AsKnownReplaySnapshotService::REASON_REGISTRY_IDENTITY_UNAVAILABLE,
            (string) $metric->reason_registry_hash,
            'no current-table lookup may fill the historical identity in'
        );
    }

    /**
     * `MD-S050-R0016` five-domain cumulative audit -- `temporal_identity_hash`.
     * `readProjectedUniverseAsOf()` never throws on an empty universe; a whole-system-empty state
     * (no listing known to the platform at all as of the cutoff) previously hashed to a real-looking
     * value and was admissible.
     */
    public function test_an_as_known_replay_is_blocked_when_no_listing_is_known_at_all(): void
    {
        DB::table('md_listings')->delete();

        $this->verifyAsKnown();
        $metric = $this->storedMetric();

        $this->assertSame('BLOCKED', (string) $metric->replay_status,
            'a whole-system-empty temporal universe must not be admissible');
        $this->assertSame('NOT_ADMISSIBLE', (string) $metric->comparison_result);
        $this->assertSame(
            AsKnownReplaySnapshotService::TEMPORAL_IDENTITY_UNAVAILABLE,
            (string) $metric->temporal_identity_hash,
            'no fabricated hash of an empty universe may satisfy the requirement'
        );
        $this->assertStringContainsString('REPLAY_TEMPORAL_IDENTITY_UNAVAILABLE', (string) $metric->mismatch_summary);
    }

    /**
     * `MD-S050-R0016` five-domain cumulative audit -- `source_observation_manifest_hash` /
     * `canonical_raw_input_hash`. `observationManifestAsKnown()`/`normalizedRowsManifestAsKnown()`
     * never throw on zero rows; a trading day with nothing recorded at all (not even a recorded
     * outage -- see `SourceObservationAsKnownBoundaryTest::test_zero_row_provider_outage_remains_
     * in_as_known_observation_manifest` for that different, already-legitimate case) previously
     * hashed to a real-looking value and was admissible.
     */
    public function test_an_as_known_replay_is_blocked_when_a_trading_day_has_no_recorded_observation_at_all(): void
    {
        DB::table('md_source_observation_identity_bindings')->delete();
        DB::table('md_source_observation_rows')->delete();
        DB::table('md_source_observations')->delete();

        $this->verifyAsKnown();
        $metric = $this->storedMetric();

        $this->assertSame('BLOCKED', (string) $metric->replay_status,
            'a trading day with no recorded source observation at all must not be admissible');
        $this->assertSame('NOT_ADMISSIBLE', (string) $metric->comparison_result);
        $this->assertSame(
            AsKnownReplaySnapshotService::SOURCE_OBSERVATION_UNAVAILABLE,
            (string) $metric->source_observation_manifest_hash
        );
        $this->assertSame(
            AsKnownReplaySnapshotService::SOURCE_OBSERVATION_UNAVAILABLE,
            (string) $metric->canonical_raw_input_hash
        );
        $this->assertStringContainsString('REPLAY_SOURCE_OBSERVATION_UNAVAILABLE', (string) $metric->mismatch_summary);
    }

    /**
     * `MD-S050-R0016` five-domain cumulative audit -- negative control. A non-trading day with zero
     * source observations is the *correct* value, not a gap (the same distinction
     * `ExpectedBarDecisionService::decide()` already draws via `EXPECTED_BAR_NON_TRADING_DAY`), so
     * `capture()` must not mark it unavailable. Exercised directly against the snapshot service
     * (not the full verifier) because a non-trading-day scenario is otherwise orthogonal to what
     * this class tests, and the claim is entirely about `capture()`'s own return value.
     */
    public function test_a_non_trading_day_with_no_observations_is_not_marked_unavailable(): void
    {
        DB::table('md_market_calendar_revisions')->where('cal_date', self::TRADE_DATE)
            ->update(['is_trading_day' => 0, 'session_state' => 'CLOSED']);
        DB::table('md_source_observation_identity_bindings')->delete();
        DB::table('md_source_observation_rows')->delete();
        DB::table('md_source_observations')->delete();

        $snapshot = (new AsKnownReplaySnapshotService())->capture(self::TRADE_DATE, self::CUTOFF);

        $this->assertNotSame(AsKnownReplaySnapshotService::SOURCE_OBSERVATION_UNAVAILABLE, $snapshot['source_observation_manifest_hash'],
            'zero observations on a non-trading day is the correct value, not a missing input');
        $this->assertNotSame(AsKnownReplaySnapshotService::SOURCE_OBSERVATION_UNAVAILABLE, $snapshot['canonical_raw_input_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $snapshot['source_observation_manifest_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $snapshot['canonical_raw_input_hash']);
    }

    /**
     * `MD-S050-R0016` five-domain cumulative audit -- `calendar_status_hash`, the already-protected
     * half. `MarketCalendarRepository::sessionContext()` already throws `MARKET_CALENDAR_EVIDENCE_
     * MISSING` when no calendar row exists for the date, proven here as an executing boundary rather
     * than assumed from reading the repository.
     */
    public function test_capture_throws_when_no_calendar_evidence_exists_for_the_date(): void
    {
        DB::table('md_market_calendar_revisions')->where('cal_date', self::TRADE_DATE)->delete();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_EVIDENCE_MISSING/');

        (new AsKnownReplaySnapshotService())->capture(self::TRADE_DATE, self::CUTOFF);
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
