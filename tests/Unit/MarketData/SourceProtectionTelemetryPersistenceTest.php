<?php

use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class SourceProtectionTelemetryPersistenceTest extends TestCase
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

    public function test_circuit_breaker_state_survives_append_only_event_to_evidence_projection(): void
    {
        DB::table('eod_run_events')->insert([
            'run_id' => 8008,
            'trade_date_requested' => '2026-08-21',
            'event_time' => '2026-08-21 17:10:00',
            'stage' => 'IMPORT',
            'event_type' => 'STAGE_COMPLETED',
            'severity' => 'WARN',
            'reason_code' => 'RUN_SOURCE_RATE_LIMIT',
            'message' => 'Source fanout stopped by source protection.',
            'event_payload_json' => json_encode([
                'source_acquisition' => [
                    'provider' => 'YAHOO_FINANCE',
                    'source_name' => 'API_FREE',
                    'source_priority' => 'PRIMARY',
                    'active_source_decision' => 'api_free',
                    'attempt_count' => 5,
                    'retry_attempt_count' => 4,
                    'failure_class_summary' => ['TRANSIENT' => 5],
                    'final_reason_code' => 'RUN_SOURCE_RATE_LIMIT',
                    'circuit_breaker_open' => true,
                    'source_protection_state' => 'CIRCUIT_OPEN',
                    'circuit_breaker_threshold' => 0.5,
                    'circuit_breaker_failure_count' => 5,
                    'circuit_breaker_success_count' => 0,
                    'attempted_acquisition_unit_count' => 5,
                    'unattempted_acquisition_unit_count' => 95,
                    'circuit_breaker_trigger_reason_code' => 'RUN_SOURCE_RATE_LIMIT',
                    'attempts' => [[
                        'attempt_number' => 1,
                        'reason_code' => 'RUN_SOURCE_RATE_LIMIT',
                        'http_status' => 429,
                        'throttle_delay_ms' => 0,
                        'backoff_delay_ms' => 0,
                        'will_retry' => false,
                    ]],
                ],
            ], JSON_UNESCAPED_SLASHES),
            'created_at' => '2026-08-21 17:10:00',
        ]);

        $telemetry = (new EodEvidenceRepository())->exportRunSourceAttemptTelemetry(8008);

        $this->assertSame('STAGE_COMPLETED', $telemetry['event_type']);
        $this->assertSame('PRIMARY', $telemetry['source_priority']);
        $this->assertSame('api_free', $telemetry['active_source_decision']);
        $this->assertSame(4, $telemetry['retry_attempt_count']);
        $this->assertSame(['TRANSIENT' => 5], $telemetry['failure_class_summary']);
        $this->assertTrue($telemetry['circuit_breaker_open']);
        $this->assertSame('CIRCUIT_OPEN', $telemetry['source_protection_state']);
        $this->assertSame(0.5, $telemetry['circuit_breaker_threshold']);
        $this->assertSame(5, $telemetry['circuit_breaker_failure_count']);
        $this->assertSame(0, $telemetry['circuit_breaker_success_count']);
        $this->assertSame(5, $telemetry['attempted_acquisition_unit_count']);
        $this->assertSame(95, $telemetry['unattempted_acquisition_unit_count']);
        $this->assertSame('RUN_SOURCE_RATE_LIMIT', $telemetry['circuit_breaker_trigger_reason_code']);
        $this->assertSame('RUN_SOURCE_RATE_LIMIT', $telemetry['final_reason_code']);
    }

    public function test_source_observation_audit_projects_immutable_hash_schema_and_rejection_reasons(): void
    {
        $common = [
            'run_id' => 8008,
            'attempt_uid' => 'ATT-B08-001',
            'acquisition_batch_id' => 'BATCH-B08-001',
            'acquisition_checkpoint_id' => null,
            'requested_trade_date' => '2026-08-21',
            'requested_start_date' => null,
            'requested_end_date' => null,
            'source_mode' => 'api',
            'source_name' => 'API_FREE',
            'provider' => 'yahoo_finance',
            'provider_symbol' => 'BBCA.JK',
            'provider_mapping_id' => null,
            'mapping_revision' => null,
            'config_snapshot_id' => null,
            'sanitized_request_identity' => 'https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK',
            'response_status' => 200,
            'content_type' => 'application/json',
            'source_timestamp' => null,
            'acquired_at' => '2026-08-21 17:10:00',
            'provider_schema_version' => 'yahoo_chart_schema_v1',
            'schema_fingerprint' => str_repeat('b', 64),
            'adapter_version' => 'yahoo_chart_v2',
            'payload_hash' => str_repeat('a', 64),
            'payload_ref' => 'sha256:'.str_repeat('a', 64),
            'payload_byte_length' => 123,
            'bounded_payload_body' => null,
            'supersedes_observation_id' => null,
            'created_at' => '2026-08-21 17:10:00',
        ];

        $captureId = DB::table('md_source_observations')->insertGetId($common + [
            'observation_uid' => 'OBS-B08-CAPTURE',
            'parent_observation_id' => null,
            'outcome_state' => 'CAPTURED',
            'validation_state' => 'PENDING',
            'reason_code' => null,
        ]);
        $outcomeId = DB::table('md_source_observations')->insertGetId($common + [
            'observation_uid' => 'OBS-B08-OUTCOME',
            'parent_observation_id' => $captureId,
            'outcome_state' => 'ACCEPTED',
            'validation_state' => 'PASSED',
            'reason_code' => null,
        ]);

        DB::table('md_source_observation_rejected_rows')->insert([
            'source_observation_id' => $outcomeId,
            'capture_observation_id' => $captureId,
            'source_row_ref' => 'yahoo:BBCA.JK:2026-08-21:bad',
            'instrument_code' => 'BBCA',
            'provider_symbol' => 'BBCA.JK',
            'trade_date' => '2026-08-21',
            'open_value' => null,
            'high_value' => null,
            'low_value' => null,
            'close_value' => null,
            'volume_value' => null,
            'adj_close_value' => null,
            'reason_code' => 'BAR_MISSING_REQUIRED_FIELD',
            'reason_note' => 'Missing provider field: close',
            'created_at' => '2026-08-21 17:10:00',
        ]);

        $audit = (new EodEvidenceRepository())->exportRunSourceObservationAudit(8008);

        $this->assertSame(2, $audit['source_observation_count']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $audit['source_observation_reference_manifest_hash']);
        $this->assertCount(2, $audit['source_observation_reference_sample']);
        $this->assertSame(['ACCEPTED' => 1, 'CAPTURED' => 1], $audit['source_observation_outcome_state_summary']);
        $this->assertSame(['PASSED' => 1, 'PENDING' => 1], $audit['schema_validation_state_summary']);
        $this->assertSame(1, $audit['source_observation_rejected_row_count']);
        $this->assertSame(['BAR_MISSING_REQUIRED_FIELD' => 1], $audit['source_observation_rejection_reason_summary']);
        $this->assertSame(str_repeat('a', 64), $audit['source_observation_reference_sample'][0]['payload_hash']);
        $this->assertSame(str_repeat('b', 64), $audit['source_observation_reference_sample'][0]['schema_fingerprint']);
    }

}
