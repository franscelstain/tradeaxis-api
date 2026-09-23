<?php

use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class SourceObservationAsKnownBoundaryTest extends TestCase
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

    public function test_as_known_rows_require_both_observation_and_identity_binding_to_be_known_by_cutoff(): void
    {
        [$observationId, $rowId] = $this->seedObservationRow('2025-06-02', '2025-06-02 18:00:00');
        DB::table('md_source_observation_identity_bindings')->insert([
            'source_observation_row_id' => $rowId,
            'source_observation_id' => $observationId,
            'listing_id' => 77,
            'provider_mapping_id' => 11,
            'mapping_revision' => 'map-v1',
            'effective_trade_date' => '2025-06-02',
            'recorded_at' => '2025-06-05 10:00:00',
        ]);

        $repo = new SourceObservationRepository();
        $this->assertSame([], $repo->normalizedRowsAsKnown('2025-06-02', '2025-06-03 00:00:00'));
        $rows = $repo->normalizedRowsAsKnown('2025-06-02', '2025-06-06 00:00:00');
        $this->assertCount(1, $rows);
        $this->assertSame(77, (int) $rows[0]['listing_id']);
    }

    public function test_provider_outage_observation_manifest_does_not_depend_on_current_universe_filtering(): void
    {
        [$observationId, $rowId] = $this->seedObservationRow('2025-06-02', '2025-06-02 18:00:00', 'FAILED', 'SOURCE_TIMEOUT');
        DB::table('md_source_observation_identity_bindings')->insert([
            'source_observation_row_id' => $rowId,
            'source_observation_id' => $observationId,
            'listing_id' => 99,
            'provider_mapping_id' => null,
            'mapping_revision' => 'outage-v1',
            'effective_trade_date' => '2025-06-02',
            'recorded_at' => '2025-06-02 18:00:00',
        ]);

        $manifest = (new SourceObservationRepository())->observationManifestAsKnown('2025-06-02', '2025-06-03 00:00:00');
        $this->assertSame(1, $manifest['observation_count']);
        $this->assertSame('SOURCE_TIMEOUT', $manifest['observations'][0]['reason_code']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $manifest['manifest_hash']);
    }

    /**
     * `MD-S019-R0074` clause 2 -- as-known replay resolves only revisions known by the declared
     * cutoff -- for the source-observation root specifically. `Determinism_Invariants_LOCKED.md:113`
     * names "immutable source-observation manifest" as one of the frozen identities; `G05`
     * (`MD-S050-R0017`) proved knowledge-time gating for temporal/calendar/corporate-action/
     * factor/config, but named no source-observation member, and the two existing tests above seed
     * only one `acquired_at`, so neither discriminates `obs.acquired_at <= knownAt` from an
     * unconditional read. Two observations for the same trade date, one acquired before the cutoff
     * and one after, close that gap for both `observationManifestAsKnown` (raw observation gate)
     * and `normalizedRowsAsKnown` (the same gate plus the identity-binding gate already proven by
     * the binding-only test above).
     */
    public function test_an_observation_acquired_after_the_cutoff_is_invisible_to_as_known_replay(): void
    {
        [$earlyObservationId, $earlyRowId] = $this->seedObservationRow('2025-07-02', '2025-07-02 09:00:00');
        DB::table('md_source_observation_identity_bindings')->insert([
            'source_observation_row_id' => $earlyRowId,
            'source_observation_id' => $earlyObservationId,
            'listing_id' => 201,
            'provider_mapping_id' => 11,
            'mapping_revision' => 'map-v1',
            'effective_trade_date' => '2025-07-02',
            'recorded_at' => '2025-07-02 09:00:00',
        ]);

        [$lateObservationId, $lateRowId] = $this->seedObservationRow('2025-07-02', '2025-07-10 09:00:00');
        DB::table('md_source_observation_identity_bindings')->insert([
            'source_observation_row_id' => $lateRowId,
            'source_observation_id' => $lateObservationId,
            'listing_id' => 202,
            'provider_mapping_id' => 12,
            'mapping_revision' => 'map-v1',
            'effective_trade_date' => '2025-07-02',
            'recorded_at' => '2025-07-10 09:00:00',
        ]);

        $repo = new SourceObservationRepository();
        $cutoffBetween = '2025-07-05 00:00:00';

        $manifestAtCutoff = $repo->observationManifestAsKnown('2025-07-02', $cutoffBetween);
        $this->assertSame(
            1,
            $manifestAtCutoff['observation_count'],
            'an observation acquired after the cutoff is visible to a replay that could not yet know it'
        );

        $rowsAtCutoff = $repo->normalizedRowsAsKnown('2025-07-02', $cutoffBetween);
        $this->assertCount(1, $rowsAtCutoff);
        $this->assertSame(201, (int) $rowsAtCutoff[0]['listing_id'],
            'the row visible at the earlier cutoff must be the early observation, not the late one');

        $cutoffAfterBoth = '2025-07-11 00:00:00';
        $manifestAfterBoth = $repo->observationManifestAsKnown('2025-07-02', $cutoffAfterBoth);
        $this->assertSame(2, $manifestAfterBoth['observation_count'],
            'once both are known, the manifest must not remain walled off at the earlier count');

        $rowsAfterBoth = $repo->normalizedRowsAsKnown('2025-07-02', $cutoffAfterBoth);
        $this->assertCount(2, $rowsAfterBoth);
    }

    public function test_zero_row_provider_outage_remains_in_as_known_observation_manifest(): void
    {
        DB::table('md_source_observations')->insert([
            'observation_uid' => 'OBS-OUTAGE-'.uniqid(),
            'run_id' => 777, 'attempt_uid' => 'ATTEMPT-777', 'acquisition_batch_id' => 'BATCH-777',
            'requested_trade_date' => '2025-06-03', 'requested_start_date' => '2025-06-03', 'requested_end_date' => '2025-06-03',
            'source_mode' => 'api', 'source_name' => 'YAHOO_FINANCE', 'provider' => 'yahoo_finance',
            'sanitized_request_identity' => 'fixture://OUTAGE', 'response_status' => null, 'content_type' => 'application/json',
            'acquired_at' => '2025-06-03 18:00:00', 'adapter_version' => 'adapter-v1',
            'payload_hash' => str_repeat('d', 64), 'payload_ref' => 'sha256:'.str_repeat('d', 64), 'payload_byte_length' => 1,
            'schema_fingerprint' => str_repeat('e', 64), 'provider_schema_version' => 'schema-v1',
            'bounded_payload_body' => '{}', 'outcome_state' => 'FAILED', 'validation_state' => 'FAILED',
            'reason_code' => 'SOURCE_TIMEOUT', 'created_at' => '2025-06-03 18:00:00',
        ]);

        $repo = new SourceObservationRepository();
        $this->assertSame([], $repo->normalizedRowsAsKnown('2025-06-03', '2025-06-04 00:00:00'));
        $manifest = $repo->observationManifestAsKnown('2025-06-03', '2025-06-04 00:00:00');
        $this->assertSame(1, $manifest['observation_count']);
        $this->assertSame('SOURCE_TIMEOUT', $manifest['observations'][0]['reason_code']);
    }

    private function seedObservationRow($tradeDate, $knownAt, $outcome = 'ACCEPTED', $reason = null): array
    {
        $observationId = (int) DB::table('md_source_observations')->insertGetId([
            'observation_uid' => 'OBS-'.uniqid(),
            'parent_observation_id' => null,
            'run_id' => 501,
            'attempt_uid' => 'ATTEMPT-501',
            'acquisition_batch_id' => 'BATCH-501',
            'acquisition_checkpoint_id' => null,
            'requested_trade_date' => $tradeDate,
            'requested_start_date' => $tradeDate,
            'requested_end_date' => $tradeDate,
            'source_mode' => 'api',
            'source_name' => 'YAHOO_FINANCE',
            'provider' => 'yahoo_finance',
            'provider_symbol' => 'TEST.JK',
            'provider_mapping_id' => 11,
            'mapping_revision' => 'map-v1',
            'config_snapshot_id' => 1,
            'sanitized_request_identity' => 'fixture://TEST.JK',
            'response_status' => $outcome === 'FAILED' ? null : 200,
            'content_type' => 'application/json',
            'source_timestamp' => null,
            'acquired_at' => $knownAt,
            'provider_schema_version' => 'schema-v1',
            'schema_fingerprint' => str_repeat('a', 64),
            'adapter_version' => 'adapter-v1',
            'payload_hash' => str_repeat('b', 64),
            'payload_ref' => 'sha256:'.str_repeat('b', 64),
            'payload_byte_length' => 12,
            'bounded_payload_body' => '{}',
            'outcome_state' => $outcome,
            'validation_state' => $outcome === 'FAILED' ? 'FAILED' : 'PASSED',
            'reason_code' => $reason,
            'created_at' => $knownAt,
        ]);
        $rowId = (int) DB::table('md_source_observation_rows')->insertGetId([
            'source_observation_id' => $observationId,
            'capture_observation_id' => $observationId,
            'source_row_ref' => 'row-1',
            'listing_id' => null,
            'provider' => 'yahoo_finance',
            'provider_symbol' => 'TEST.JK',
            'provider_mapping_id' => 11,
            'mapping_revision' => 'map-v1',
            'ticker_code' => 'TEST',
            'trade_date' => $tradeDate,
            'source_timestamp' => null,
            'open_value' => '100', 'high_value' => '101', 'low_value' => '99', 'close_value' => '100', 'volume_value' => '0',
            'adj_close_value' => null,
            'row_fingerprint' => str_repeat('c', 64),
            'created_at' => $knownAt,
        ]);
        return [$observationId, $rowId];
    }
}
