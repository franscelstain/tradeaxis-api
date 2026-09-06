<?php

use App\Application\MarketData\Services\AsKnownReplayExecutionService;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class AsKnownReplayExecutionServiceTest extends TestCase
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

    public function test_as_known_executes_production_canonicalizer_and_rolls_back_projection_mutation(): void
    {
        $tradeDate = '2025-06-02';
        $cutoff = '2025-06-03 00:00:00';
        DB::table('tickers')->insert([
            'ticker_id' => 501,
            'ticker_code' => 'TEST',
            'company_name' => 'Test Tbk',
            'is_active' => 1,
            'listed_date' => '2023-01-02',
            'created_at' => '2023-01-02 00:00:00',
        ]);
        $identity = (new TemporalIdentityRepository())->resolveProviderContext('TEST', 'yahoo_finance', $tradeDate, $cutoff);

        $config = (new MarketDataConfigSnapshotRepository())->resolveForRun($tradeDate);
        DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $config['config_snapshot_id'])
            ->update(['recorded_at' => '2025-06-01 00:00:00']);

        $captureObservationId = (int) DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'as-known-execution-capture'),
            'run_id' => 9001,
            'attempt_uid' => 'as-known-execution-test',
            'acquisition_batch_id' => 'as-known-execution-test',
            'requested_trade_date' => $tradeDate,
            'requested_start_date' => $tradeDate,
            'requested_end_date' => $tradeDate,
            'source_mode' => 'api',
            'source_name' => 'YAHOO_FINANCE',
            'provider' => 'yahoo_finance',
            'provider_symbol' => 'TEST.JK',
            'provider_mapping_id' => $identity['provider_mapping_id'],
            'mapping_revision' => $identity['mapping_revision'],
            'config_snapshot_id' => $config['config_snapshot_id'],
            'sanitized_request_identity' => 'fixture://TEST.JK/2025-06-02',
            'response_status' => 200,
            'content_type' => 'application/json',
            'acquired_at' => '2025-06-02 18:00:00',
            'provider_schema_version' => 'fixture-schema-v1',
            'schema_fingerprint' => str_repeat('a', 64),
            'adapter_version' => 'fixture-adapter-v1',
            'payload_hash' => str_repeat('b', 64),
            'payload_ref' => 'sha256:'.str_repeat('b', 64),
            'payload_byte_length' => 128,
            'bounded_payload_body' => '{}',
            'outcome_state' => 'CAPTURED',
            'validation_state' => 'PENDING',
            'created_at' => '2025-06-02 18:00:00',
        ]);
        $observationId = (int) DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'as-known-execution-accepted'),
            'parent_observation_id' => $captureObservationId,
            'run_id' => 9001,
            'attempt_uid' => 'as-known-execution-test',
            'acquisition_batch_id' => 'as-known-execution-test',
            'requested_trade_date' => $tradeDate,
            'requested_start_date' => $tradeDate,
            'requested_end_date' => $tradeDate,
            'source_mode' => 'api',
            'source_name' => 'YAHOO_FINANCE',
            'provider' => 'yahoo_finance',
            'provider_symbol' => 'TEST.JK',
            'provider_mapping_id' => $identity['provider_mapping_id'],
            'mapping_revision' => $identity['mapping_revision'],
            'config_snapshot_id' => $config['config_snapshot_id'],
            'sanitized_request_identity' => 'fixture://TEST.JK/2025-06-02',
            'response_status' => 200,
            'content_type' => 'application/json',
            'acquired_at' => '2025-06-02 18:00:00',
            'provider_schema_version' => 'fixture-schema-v1',
            'schema_fingerprint' => str_repeat('a', 64),
            'adapter_version' => 'fixture-adapter-v1',
            'payload_hash' => str_repeat('b', 64),
            'payload_ref' => 'sha256:'.str_repeat('b', 64),
            'payload_byte_length' => 128,
            'outcome_state' => 'ACCEPTED',
            'validation_state' => 'PASSED',
            'created_at' => '2025-06-02 18:00:00',
        ]);
        $rowId = (int) DB::table('md_source_observation_rows')->insertGetId([
            'source_observation_id' => $observationId,
            'capture_observation_id' => $captureObservationId,
            'source_row_ref' => 'row-1',
            'listing_id' => $identity['listing_id'],
            'provider' => 'yahoo_finance',
            'provider_symbol' => 'TEST.JK',
            'provider_mapping_id' => $identity['provider_mapping_id'],
            'mapping_revision' => $identity['mapping_revision'],
            'ticker_code' => 'TEST',
            'trade_date' => $tradeDate,
            'open_value' => '100',
            'high_value' => '110',
            'low_value' => '95',
            'close_value' => '105',
            'volume_value' => '12345',
            'adj_close_value' => null,
            'row_fingerprint' => str_repeat('c', 64),
            'created_at' => '2025-06-02 18:00:00',
        ]);
        DB::table('md_source_observation_identity_bindings')->insert([
            'source_observation_row_id' => $rowId,
            'source_observation_id' => $observationId,
            'listing_id' => $identity['listing_id'],
            'provider_mapping_id' => $identity['provider_mapping_id'],
            'mapping_revision' => $identity['mapping_revision'],
            'effective_trade_date' => $tradeDate,
            'recorded_at' => '2025-06-02 18:00:00',
        ]);

        $observations = new SourceObservationRepository();
        $bound = [
            'source_observation_manifest_hash' => $observations->observationManifestAsKnown($tradeDate, $cutoff)['manifest_hash'],
            'canonical_raw_input_hash' => $observations->normalizedRowsManifestAsKnown($tradeDate, $cutoff)['manifest_hash'],
            'config_snapshot_id' => $config['config_snapshot_id'],
            'config_snapshot_hash' => $config['config_hash'],
            'source_observation_manifest' => $observations->observationManifestAsKnown($tradeDate, $cutoff),
        ];

        $beforeRuns = (int) DB::table('eod_runs')->count();
        $beforePublications = (int) DB::table('eod_publications')->count();
        $beforeBars = (int) DB::table('eod_bars')->count();

        $result = app(AsKnownReplayExecutionService::class)->execute($tradeDate, $cutoff, $bound);

        $this->assertSame('CANONICAL_RAW', $result['execution_scope']);
        $this->assertSame('SUCCESS', $result['execution_state']);
        $this->assertSame(1, $result['canonical_row_count']);
        $this->assertSame(0, $result['invalid_row_count']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result['canonical_output_hash']);
        $this->assertFalse($result['durable_projection_mutation']);
        $this->assertSame($beforeRuns, (int) DB::table('eod_runs')->count());
        $this->assertSame($beforePublications, (int) DB::table('eod_publications')->count());
        $this->assertSame($beforeBars, (int) DB::table('eod_bars')->count());
    }
}
