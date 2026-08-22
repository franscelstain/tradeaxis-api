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

    private function normalizedRow(array $override = []): array
    {
        return array_merge([
            'listing_id' => 9001,
            'provider_symbol' => 'BBCA.JK',
            'provider_mapping_id' => 7001,
            'mapping_revision' => 'YF-BBCA-2026-01',
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 100,
            'high' => 110,
            'low' => 95,
            'close' => 105,
            'volume' => 1000,
            'adj_close' => null,
            'source_row_ref' => 'yahoo:BBCA.JK:2026-03-20',
            'captured_at' => '2026-03-20 17:20:00',
        ], $override);
    }

    private function bindIdentity(SourceObservationRepository $repository, array $accepted, array $row): void
    {
        $repository->bindResolvedIdentity(
            $accepted['source_observation_id'],
            $row['source_row_ref'],
            [
                'listing_id' => $row['listing_id'],
                'ticker_id' => 8001,
                'provider_mapping_id' => $row['provider_mapping_id'],
                'mapping_revision' => $row['mapping_revision'],
                'trade_date' => $row['trade_date'],
            ]
        );
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

        $failureCapture = $rows->firstWhere('content_type', 'application/vnd.tradeaxis.source-transport-failure+json');
        $this->assertNotNull($failureCapture);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $failureCapture->payload_hash);
        $this->assertSame('sha256:'.$failureCapture->payload_hash, $failureCapture->payload_ref);
        $this->assertGreaterThan(0, (int) $failureCapture->payload_byte_length);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $failureCapture->schema_fingerprint);

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
        $row = $this->normalizedRow();
        $accepted = $repository->recordAcceptedRows($capture, [$row]);
        $this->bindIdentity($repository, $accepted, $row);

        $this->assertTrue($repository->existsAccepted($accepted['source_observation_id'], $row['source_row_ref']));
        $this->assertFalse($repository->existsAccepted(999999), 'an unknown observation id must not resolve');
    }

    public function test_capture_without_payload_or_verifiable_external_identity_fails_closed(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SOURCE_OBSERVATION_PAYLOAD_IDENTITY_REQUIRED');

        (new SourceObservationRepository())->capture($this->envelope());
    }

    public function test_identical_refetch_records_confirmation_without_a_finding(): void
    {
        $repository = new SourceObservationRepository();
        $row = $this->normalizedRow();

        $firstCapture = $repository->capture($this->envelope() + ['payload' => '{"close":105,"attempt":1}']);
        $first = $repository->recordAcceptedRows($firstCapture, [$row]);
        $this->bindIdentity($repository, $first, $row);

        $secondCapture = $repository->capture($this->envelope(['acquired_at' => '2026-03-20 18:20:00']) + ['payload' => '{"close":105,"attempt":2}']);
        $second = $repository->recordAcceptedRows($secondCapture, [$row]);
        $this->bindIdentity($repository, $second, $row);

        $comparison = DB::table('md_source_observation_revision_comparisons')->first();
        $this->assertNotNull($comparison);
        $this->assertSame('CONFIRMED_SAME', $comparison->comparison_state);
        $this->assertSame('NOT_APPLICABLE', $comparison->finding_state);
        $this->assertNull($comparison->divergence_finding_uid);
        $this->assertNotSame($first['source_observation_id'], $second['source_observation_id']);
    }

    public function test_changed_refetch_opens_an_explicit_divergence_with_both_values_and_delta(): void
    {
        $repository = new SourceObservationRepository();
        $priorRow = $this->normalizedRow();
        $currentRow = $this->normalizedRow(['close' => 107]);

        $firstCapture = $repository->capture($this->envelope() + ['payload' => '{"close":105,"attempt":1}']);
        $first = $repository->recordAcceptedRows($firstCapture, [$priorRow]);
        $this->bindIdentity($repository, $first, $priorRow);

        $secondCapture = $repository->capture($this->envelope(['acquired_at' => '2026-03-20 18:20:00']) + ['payload' => '{"close":107,"attempt":2}']);
        $second = $repository->recordAcceptedRows($secondCapture, [$currentRow]);
        $this->bindIdentity($repository, $second, $currentRow);

        $comparison = DB::table('md_source_observation_revision_comparisons')->first();
        $this->assertSame('OPEN_DIVERGENCE', $comparison->comparison_state);
        $this->assertSame('OPEN', $comparison->finding_state);
        $this->assertStringStartsWith('MD-SOURCE-DIV-', $comparison->divergence_finding_uid);
        $this->assertSame($first['source_observation_id'], (int) $comparison->prior_source_observation_id);
        $this->assertSame($second['source_observation_id'], (int) $comparison->current_source_observation_id);
        $this->assertSame(['close'], json_decode($comparison->differing_fields_json, true));
        $this->assertSame('105', json_decode($comparison->prior_values_json, true)['close']);
        $this->assertSame('107', json_decode($comparison->current_values_json, true)['close']);
        $this->assertSame('2', json_decode($comparison->value_deltas_json, true)['close']);
    }

    public function test_partially_invalid_response_persists_reason_coded_row_evidence_linked_to_the_capture(): void
    {
        $repository = new SourceObservationRepository();
        $capture = $repository->capture($this->envelope() + ['payload' => '{"valid":1,"invalid":1}']);
        $accepted = $repository->recordAcceptedRows($capture, [$this->normalizedRow()], [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 100,
            'high' => 110,
            'low' => 95,
            'close' => null,
            'volume' => 1000,
            'source_row_ref' => 'yahoo:BBCA.JK:2026-03-20:invalid',
            'invalid_reason_code' => 'BAR_MISSING_REQUIRED_FIELD',
            'invalid_note' => 'Missing provider field: close',
        ]]);

        $rejected = DB::table('md_source_observation_rejected_rows')->first();
        $this->assertNotNull($rejected);
        $this->assertSame($accepted['source_observation_id'], (int) $rejected->source_observation_id);
        $this->assertSame($capture['source_observation_id'], (int) $rejected->capture_observation_id);
        $this->assertSame('BAR_MISSING_REQUIRED_FIELD', $rejected->reason_code);
        $this->assertNull($rejected->close_value);
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
