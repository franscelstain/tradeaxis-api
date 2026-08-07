<?php

use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W07 — immutable source observations and acquisition ports/adapters, stage 4.
 *
 * Exit gate: "rerun tidak menimpa observation; secret tidak bocor; canonical rows dapat
 * ditelusuri ke observation yang tepat."
 *
 * Blueprint outcome: "setiap source outcome, termasuk empty/failure, memiliki immutable
 * provenance."
 *
 * Owner contract: docs/market_data/book/Source_Data_Acquisition_Contract_LOCKED.md
 */
class SourceObservationImmutabilityTest extends TestCase
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

    private function envelope(array $override = []): array
    {
        return array_merge([
            'run_id' => 501,
            'requested_trade_date' => '2026-03-20',
            'source_mode' => 'api',
            'source_name' => 'YAHOO_FINANCE',
            'provider' => 'yahoo_finance',
            'provider_symbol' => 'BBCA.JK',
            'ticker_code' => 'BBCA',
            'adapter_version' => 'yahoo_chart_v2',
            'schema_version' => 'yahoo_chart_schema_v1',
            'sanitized_request_identity' => 'GET /v8/finance/chart/BBCA.JK',
            'acquired_at' => '2026-03-20 17:20:00',
        ], $override);
    }

    /**
     * A rerun is a new observation, never an edit of the previous one. Overwriting would erase
     * the record of what the platform saw the first time, which is the one thing an immutable
     * envelope exists to keep.
     */
    public function test_a_rerun_appends_a_new_observation_and_never_overwrites(): void
    {
        $repository = new SourceObservationRepository();

        $first = $repository->capture($this->envelope() + ['payload' => '{"first":true}']);
        $second = $repository->capture($this->envelope() + ['payload' => '{"second":true}']);

        $this->assertNotSame($first['source_observation_id'], $second['source_observation_id']);
        $this->assertSame(2, DB::table('md_source_observations')->count());

        $stored = DB::table('md_source_observations')->where('source_observation_id', $first['source_observation_id'])->first();
        $this->assertStringContainsString('first', (string) $stored->bounded_payload_body);
    }

    /**
     * An identical payload replayed still produces a distinct observation. Two acquisitions are
     * two events even when their content matches; collapsing them would lose the fact that the
     * platform asked twice.
     */
    public function test_an_identical_payload_still_produces_a_distinct_observation(): void
    {
        $repository = new SourceObservationRepository();
        $payload = '{"identical":true}';

        $first = $repository->capture($this->envelope() + ['payload' => $payload]);
        $second = $repository->capture($this->envelope() + ['payload' => $payload]);

        $this->assertNotSame($first['source_observation_id'], $second['source_observation_id']);
        $this->assertSame($first['payload_hash'], $second['payload_hash'], 'identical content must hash identically');
    }

    /**
     * Every outcome carries provenance, including the ones that returned nothing. An empty
     * response and a transport failure are source facts; leaving them unrecorded makes the
     * absence of a bar indistinguishable from an absence of an attempt.
     */
    public function test_empty_and_failed_outcomes_are_recorded_with_provenance(): void
    {
        $repository = new SourceObservationRepository();

        $capture = $repository->capture($this->envelope() + ['payload' => '{"chart":{"result":[]}}']);
        $repository->recordOutcome($capture, 'EMPTY', 'RUN_SOURCE_EMPTY_SERIES');
        $repository->recordTransportFailure($this->envelope(['ticker_code' => 'TLKM']), 'RUN_SOURCE_TIMEOUT');

        $rows = DB::table('md_source_observations')->orderBy('observation_id')->get();

        $this->assertGreaterThanOrEqual(3, $rows->count());
        $this->assertNotEmpty($rows->firstWhere('reason_code', 'RUN_SOURCE_EMPTY_SERIES'));
        $this->assertNotEmpty($rows->firstWhere('reason_code', 'RUN_SOURCE_TIMEOUT'));

        foreach ($rows as $row) {
            $this->assertNotEmpty($row->requested_trade_date, 'every outcome records its requested scope');
            $this->assertNotEmpty($row->source_mode, 'every outcome records its source mode');
            $this->assertNotEmpty($row->acquired_at, 'every outcome records when it happened');
        }
    }

    /**
     * Secret material must never reach the stored envelope. A token appearing in a payload
     * excerpt or a request identity would be recorded permanently by the very immutability this
     * table provides.
     */
    public function test_secret_material_never_reaches_the_stored_envelope(): void
    {
        $repository = new SourceObservationRepository();

        $capture = $repository->capture($this->envelope([
            'sanitized_request_identity' => 'GET /v8/finance/chart/BBCA.JK?token=super-secret-token',
        ]) + ['payload' => '{"crumb":"super-secret-token","authorization":"Bearer super-secret-token"}']);

        $stored = DB::table('md_source_observations')->where('source_observation_id', $capture['source_observation_id'])->first();
        $serialized = json_encode((array) $stored);

        $this->assertStringNotContainsString('super-secret-token', $serialized);
    }

    /**
     * A canonical bar may only claim an observation that exists and was accepted. Without this
     * the lineage field records an id nobody can resolve, which is worse than no lineage at all
     * because it looks complete.
     */
    public function test_canonical_lineage_only_accepts_a_real_accepted_observation(): void
    {
        $repository = new SourceObservationRepository();

        $capture = $repository->capture($this->envelope() + ['payload' => '{"ok":true}']);
        $accepted = $repository->recordOutcome($capture, 'ACCEPTED');

        $this->assertTrue($repository->existsAccepted($accepted['source_observation_id']));
        $this->assertFalse($repository->existsAccepted(999999), 'an unknown observation id must not resolve');
    }

    /**
     * The run-level manifest hash must change when an observation is added, so a publication
     * bound to it cannot silently describe a different acquisition set.
     */
    public function test_the_run_manifest_hash_moves_when_an_observation_is_added(): void
    {
        $repository = new SourceObservationRepository();

        $repository->capture($this->envelope() + ['payload' => '{"one":true}']);
        $before = $repository->manifestHashForRun(501);

        $repository->capture($this->envelope(['ticker_code' => 'TLKM']) + ['payload' => '{"two":true}']);
        $after = $repository->manifestHashForRun(501);

        $this->assertNotEmpty($before);
        $this->assertNotSame($before, $after);
    }
}
